<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * The shopping cart. Guests keep it in the session, signed-in customers in the database.
 * Only product, variant and quantity are stored; every price, stock limit, flash deal,
 * coupon and delivery fee is recalculated from the database each time the cart is read.
 */
class CartService
{
    /** Technical ceiling only; the real limit for a line is its stock (and flash deal allowance). */
    public const MAX_QUANTITY = 100000;

    private const SESSION_KEY = 'cart';

    /** @var list<string> */
    private array $notices = [];

    public static function makeKey(int $productId, int $variantId = 0): string
    {
        return $productId.'-'.$variantId;
    }

    /** @return array{0: int, 1: int}|null */
    public static function parseKey(string $key): ?array
    {
        return preg_match('/^(\d+)-(\d+)$/', $key, $m) ? [(int) $m[1], (int) $m[2]] : null;
    }

    // ───────────────────────────── actions ─────────────────────────────

    /** @throws DomainException */
    public function add(int $productId, ?int $variantId, int $quantity = 1): string
    {
        if (Setting::get('store_open') !== '1') {
            throw new DomainException('The store is closed right now. Please try again later.');
        }

        $product = Product::active()->with('variants')->find($productId);

        if (! $product) {
            throw new DomainException('This product is not available.');
        }

        $variant = $this->resolveVariant($product, $variantId);
        $max = $this->maxQuantity($product, $variant);

        if ($max < 1) {
            throw new DomainException('Sorry, this item is out of stock.');
        }

        $key = self::makeKey($product->id, $variant?->id ?? 0);
        $current = $this->items()[$key] ?? 0;
        $wanted = $current + max(1, $quantity);

        if ($current >= $max) {
            throw new DomainException("You already have the maximum quantity ({$max}) of this item in your cart.");
        }

        $this->writeQuantity($key, min($wanted, $max));
        $this->forgetMemo();

        return $wanted > $max
            ? "Only {$max} available, so your cart was updated to {$max}."
            : 'Added to cart';
    }

    /** @throws DomainException */
    public function setQuantity(string $key, int $quantity): void
    {
        if (! isset($this->items()[$key])) {
            throw new DomainException('This item is not in your cart.');
        }

        $this->writeQuantity($key, max(1, min($quantity, self::MAX_QUANTITY)));
        $this->forgetMemo();
    }

    public function remove(string $key): void
    {
        $this->removeKey($key);
        $this->forgetMemo();
    }

    public function clear(): void
    {
        if ($cart = $this->databaseCart()) {
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);
            $this->databaseCart(fresh: true);
        } else {
            session()->forget(self::SESSION_KEY);
        }

        $this->forgetMemo();
    }

    /** @throws DomainException */
    public function applyCoupon(string $code): void
    {
        $coupon = Coupon::findByCode($code);

        if (! $coupon) {
            throw new DomainException('This code is not valid.');
        }

        $built = $this->build();

        if (! $built['lines']) {
            throw new DomainException('Add something to your cart first.');
        }

        $coupon->evaluate($built['lines'], $built['subtotal'], auth()->id());

        $this->writeMeta('coupon', $coupon->code);
        $this->forgetMemo();
    }

    public function removeCoupon(): void
    {
        $this->writeMeta('coupon', null);
        $this->forgetMemo();
    }

    /** @throws DomainException */
    public function setZone(string $zone): void
    {
        if (! array_key_exists($zone, $this->zones())) {
            throw new DomainException('This delivery area is not available.');
        }

        $this->writeMeta('zone', $zone);
        $this->forgetMemo();
    }

    /** Moves the guest (session) cart into a customer's database cart when they sign in. */
    public function mergeGuestCart(int $userId): void
    {
        $guest = session(self::SESSION_KEY);

        if (! is_array($guest) || (! ($guest['items'] ?? []) && ! ($guest['coupon'] ?? null))) {
            return;
        }

        DB::transaction(function () use ($guest, $userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);
            $existing = $cart->items()->get()->keyBy(fn (CartItem $item) => self::makeKey($item->product_id, $item->variant_id));

            foreach ($guest['items'] ?? [] as $key => $quantity) {
                if (! $parsed = self::parseKey((string) $key)) {
                    continue;
                }

                $total = min(self::MAX_QUANTITY, (int) $quantity + (int) ($existing[$key]->quantity ?? 0));

                CartItem::updateOrCreate(
                    ['cart_id' => $cart->id, 'product_id' => $parsed[0], 'variant_id' => $parsed[1]],
                    ['quantity' => $total],
                );
            }

            $cart->update([
                'coupon_code' => $cart->coupon_code ?: ($guest['coupon'] ?? null),
                'zone' => $cart->zone ?: ($guest['zone'] ?? null),
            ]);
        });

        session()->forget(self::SESSION_KEY);
        $this->forgetMemo();
    }

    // ───────────────────────────── reading ─────────────────────────────

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $attributes = request()->attributes;

        if (! $attributes->has('cart.summary')) {
            $attributes->set('cart.summary', $this->calculate());
        }

        return $attributes->get('cart.summary');
    }

    public function count(): int
    {
        return array_sum($this->items());
    }

    /** @return array<string, array{label: string, fee: float, time: string}> */
    public function zones(): array
    {
        $all = [];

        foreach (Setting::ZONES as $key => $label) {
            $all[$key] = [
                'label' => $label,
                'fee' => (float) Setting::get("{$key}_charge", 0),
                'time' => (string) Setting::get("{$key}_time", ''),
                'enabled' => Setting::get("{$key}_on") === '1',
            ];
        }

        $enabled = array_filter($all, fn (array $zone) => $zone['enabled']);

        return $enabled ?: $all;
    }

    // ───────────────────────────── calculation ─────────────────────────────

    /** @return array<string, mixed> */
    private function calculate(): array
    {
        $this->notices = [];
        $built = $this->build();
        $lines = $built['lines'];
        $subtotal = $built['subtotal'];

        $discount = 0.0;
        $freeByCoupon = false;
        $couponInfo = null;

        if ($code = $this->readMeta('coupon')) {
            try {
                $coupon = Coupon::findByCode($code) ?? throw new DomainException('Coupon '.$code.' is no longer available.');
                $result = $coupon->evaluate($lines, $subtotal, auth()->id());
                $discount = $result['discount'];
                $freeByCoupon = $result['free_delivery'];
                $couponInfo = ['code' => $coupon->code, 'label' => $coupon->discount_label, 'free_delivery' => $freeByCoupon];
            } catch (DomainException $e) {
                $this->writeMeta('coupon', null);
                $this->notices[] = "Coupon {$code} was removed: ".$e->getMessage();
            }
        }

        $zones = $this->zones();
        $zoneKey = $this->readMeta('zone');
        $zoneKey = array_key_exists($zoneKey ?? '', $zones) ? $zoneKey : array_key_first($zones);
        $threshold = (float) Setting::get('free_delivery_above', 0);

        $freeReason = match (true) {
            ! $lines => null,
            $freeByCoupon => 'coupon',
            $threshold > 0 && $subtotal >= $threshold => 'threshold',
            collect($lines)->every(fn (array $line) => $line['is_free_delivery']) => 'products',
            default => null,
        };

        $fee = $lines && ! $freeReason ? $zones[$zoneKey]['fee'] : 0.0;
        $minOrder = (float) Setting::get('min_order', 0);
        $guestBlocked = ! auth()->check() && Setting::get('guest_checkout') !== '1';

        return [
            'count' => array_sum(array_column($lines, 'quantity')),
            'items' => $lines,
            'subtotal' => $subtotal,
            'savings' => round(array_sum(array_map(
                fn (array $line) => $line['old_price'] ? ($line['old_price'] - $line['unit_price']) * $line['quantity'] : 0,
                $lines,
            )), 2),
            'discount' => $discount,
            'coupon' => $couponInfo,
            'shipping' => [
                'zone' => $zoneKey,
                'label' => $zones[$zoneKey]['label'],
                'time' => $zones[$zoneKey]['time'],
                'fee' => $fee,
                'free' => $lines && $freeReason !== null,
                'free_reason' => $freeReason,
            ],
            'zones' => collect($zones)->map(fn (array $zone, string $key) => [
                'key' => $key, 'label' => $zone['label'], 'fee' => $zone['fee'], 'time' => $zone['time'],
            ])->values()->all(),
            'total' => round(max(0, $subtotal - $discount) + $fee, 2),
            'min_order' => $minOrder,
            'meets_min_order' => $subtotal >= $minOrder,
            'free_delivery_remaining' => $lines && $threshold > 0 && $subtotal < $threshold ? round($threshold - $subtotal, 2) : 0,
            'can_checkout' => (bool) $lines && $subtotal >= $minOrder,
            'checkout_url' => $guestBlocked ? route('login') : url('/checkout'),
            'notices' => $this->notices,
        ];
    }

    /**
     * Rebuilds every line from the database. Items that can no longer be sold are dropped
     * and quantities above the current stock are lowered, each with a notice for the customer.
     *
     * @return array{lines: list<array<string, mixed>>, subtotal: float}
     */
    private function build(): array
    {
        $stored = $this->items();

        if (! $stored) {
            return ['lines' => [], 'subtotal' => 0.0];
        }

        $productIds = collect(array_keys($stored))
            ->map(fn (string $key) => self::parseKey($key)[0] ?? 0)
            ->unique()
            ->all();

        $products = Product::active()->whereIn('id', $productIds)->with(['images', 'variants'])->get()->keyBy('id');
        $lines = [];

        foreach ($stored as $key => $quantity) {
            [$productId, $variantId] = self::parseKey($key) ?? [0, 0];
            $product = $products->get($productId);

            if (! $product) {
                $this->removeKey($key);
                $this->notices[] = 'An item in your cart is no longer available and was removed.';

                continue;
            }

            $variant = $product->variants->isEmpty() ? null : $product->variants->firstWhere('id', $variantId);

            if ($product->variants->isNotEmpty() && ! $variant) {
                $this->removeKey($key);
                $this->notices[] = "The selected option of {$product->name} is no longer available and was removed.";

                continue;
            }

            $max = $this->maxQuantity($product, $variant);

            if ($max < 1) {
                $this->removeKey($key);
                $this->notices[] = "{$product->name} is out of stock and was removed.";

                continue;
            }

            if ($quantity > $max) {
                $quantity = $max;
                $this->writeQuantity($key, $max);
                $this->notices[] = "Only {$max} of {$product->name} available, so the quantity was updated.";
            }

            $flash = $product->flash_item;
            $base = (float) ($variant?->price ?? $product->price);
            $unit = $product->flashPrice($base);
            $reference = max((float) ($product->compare_price ?? 0), $flash ? $base : 0.0);
            $old = $reference > $unit ? $reference : null;

            $lines[] = [
                'key' => $key,
                'product_id' => $product->id,
                'variant_id' => $variant?->id ?? 0,
                'category_id' => $product->category_id,
                'name' => $product->name,
                'url' => $product->url,
                'image' => $product->cover_url,
                'variant' => $variant ? $this->variantLabel($variant) : null,
                'quantity' => $quantity,
                'max_quantity' => $max,
                'unit_price' => $unit,
                'old_price' => $old,
                'line_total' => round($unit * $quantity, 2),
                'is_flash' => $flash !== null,
                'is_free_delivery' => (bool) $product->is_free_delivery,
            ];
        }

        return ['lines' => $lines, 'subtotal' => round(array_sum(array_column($lines, 'line_total')), 2)];
    }

    /** @throws DomainException */
    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if ($product->variants->isEmpty()) {
            return null;
        }

        if (! $variantId) {
            throw new DomainException('Please choose the options for this product first.');
        }

        return $product->variants->firstWhere('id', $variantId)
            ?? throw new DomainException('This option is not available.');
    }

    /** The most a customer can hold of one line: stock, the flash deal allowance and the per-line cap. */
    private function maxQuantity(Product $product, ?ProductVariant $variant): int
    {
        $max = self::MAX_QUANTITY;

        if ($product->track_inventory) {
            $max = min($max, (int) ($variant?->stock ?? $product->stock));
        }

        if ($dealStock = $product->flash_item?->deal_stock) {
            $max = min($max, (int) $dealStock);
        }

        return max(0, $max);
    }

    private function variantLabel(ProductVariant $variant): string
    {
        return collect([
            $variant->size ? 'Size: '.$variant->size : null,
            $variant->color ? 'Color: '.$variant->color : null,
        ])->filter()->implode(' / ');
    }

    // ───────────────────────────── storage ─────────────────────────────

    /** @return array<string, int> */
    private function items(): array
    {
        if (auth()->check()) {
            return $this->databaseCart()?->items
                ->mapWithKeys(fn (CartItem $item) => [self::makeKey($item->product_id, $item->variant_id) => $item->quantity])
                ->all() ?? [];
        }

        return array_map('intval', session(self::SESSION_KEY.'.items', []));
    }

    private function writeQuantity(string $key, int $quantity): void
    {
        if (auth()->check()) {
            [$productId, $variantId] = self::parseKey($key);
            $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

            CartItem::updateOrCreate(
                ['cart_id' => $cart->id, 'product_id' => $productId, 'variant_id' => $variantId],
                ['quantity' => $quantity],
            );
            $this->databaseCart(fresh: true);

            return;
        }

        session()->put(self::SESSION_KEY.'.items.'.$key, $quantity);
    }

    private function removeKey(string $key): void
    {
        if (auth()->check()) {
            if ($cart = $this->databaseCart()) {
                [$productId, $variantId] = self::parseKey($key) ?? [0, 0];
                $cart->items()->where('product_id', $productId)->where('variant_id', $variantId)->delete();
                $this->databaseCart(fresh: true);
            }

            return;
        }

        session()->forget(self::SESSION_KEY.'.items.'.$key);
    }

    private function readMeta(string $field): ?string
    {
        if (auth()->check()) {
            return $this->databaseCart()?->{$field === 'coupon' ? 'coupon_code' : 'zone'};
        }

        return session(self::SESSION_KEY.'.'.$field);
    }

    private function writeMeta(string $field, ?string $value): void
    {
        if (auth()->check()) {
            $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
            $cart->update([$field === 'coupon' ? 'coupon_code' : 'zone' => $value]);
            $this->databaseCart(fresh: true);

            return;
        }

        session()->put(self::SESSION_KEY.'.'.$field, $value);
    }

    /** The signed-in customer's cart row, loaded once per request (the request carries the memo). */
    private function databaseCart(bool $fresh = false): ?Cart
    {
        $userId = auth()->id();
        $attributes = request()->attributes;
        $loaded = $attributes->get('cart.row');

        if ($fresh || ! $loaded || $loaded['user'] !== $userId) {
            $loaded = [
                'user' => $userId,
                'cart' => $userId ? Cart::with('items')->where('user_id', $userId)->first() : null,
            ];
            $attributes->set('cart.row', $loaded);
        }

        return $loaded['cart'];
    }

    private function forgetMemo(): void
    {
        request()->attributes->remove('cart.summary');
    }
}
