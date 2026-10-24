<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\FlashDealProduct;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns the cart into an order. Prices, coupon, delivery fee and stock are all worked out again
 * on the server inside one transaction, so nothing the browser sends can change what is charged.
 */
class CheckoutService
{
    public function __construct(private CartService $cart) {}

    /**
     * Guests get an account from their checkout details. The mobile number is the customer's identity: an
     * account with the same number is reused (never logged in), so every order placed with that number ends
     * up under one account. The starting password is the store name followed by the last 4 digits of the
     * mobile number, which the customer can change later.
     *
     * @param  array{name: string, phone: string, email: string}  $data
     */
    private function accountFor(array $data): User
    {
        $email = Str::lower($data['email']);

        if ($user = User::query()->where('phone', $data['phone'])->first()) {
            return $user;
        }

        // An older account with this email that never had a number yet becomes the account for this number.
        $byEmail = User::query()->where('email', $email)->first();

        if ($byEmail && ! $byEmail->phone) {
            $byEmail->update(['phone' => $data['phone']]);

            return $byEmail;
        }

        session()->flash('new_account', true);

        return User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            // The email can already belong to a different customer; the order still keeps it.
            'email' => $byEmail ? null : $email,
            'password' => self::startingPassword($data['phone']),
        ]);
    }

    /** The password every checkout-created account starts with. */
    public static function startingPassword(string $phone): string
    {
        return Str::slug((string) Setting::get('store_name'), '').substr($phone, -4);
    }

    /** Keeps the delivery address in the customer's address book (the first one becomes the default). */
    private function rememberAddress(int $userId, array $data): void
    {
        $book = Address::query()->where('user_id', $userId);

        $known = (clone $book)
            ->where('district', $data['district'])
            ->where('area', $data['area'])
            ->where('address', $data['address'])
            ->exists();

        if ($known || (clone $book)->count() >= Address::LIMIT) {
            return;
        }

        Address::create([
            'user_id' => $userId,
            'label' => 'Home',
            'name' => $data['name'],
            'phone' => $data['phone'],
            'district' => $data['district'],
            'area' => $data['area'],
            'address' => $data['address'],
            'is_default' => ! (clone $book)->exists(),
        ]);
    }

    /** @return list<string> every district a customer can pick */
    public static function districts(): array
    {
        return array_merge(...array_values(config('locations.divisions')));
    }

    public static function divisionOf(string $district): ?string
    {
        foreach (config('locations.divisions') as $division => $districts) {
            if (in_array($district, $districts, true)) {
                return $division;
            }
        }

        return null;
    }

    public static function zoneOf(string $district): string
    {
        return match (true) {
            in_array($district, config('locations.inside_dhaka'), true) => 'inside_dhaka',
            in_array($district, config('locations.nearby_dhaka'), true) => 'nearby_dhaka',
            default => 'outside_dhaka',
        };
    }

    /**
     * Why checkout is not possible right now, or null when it is.
     */
    public function blocker(): ?string
    {
        $summary = $this->cart->summary();

        return match (true) {
            auth()->user()?->is_restricted => User::restrictedMessage(),
            Setting::get('store_open') !== '1' => 'The store is closed right now. Please try again later.',
            ! $summary['items'] => 'Your cart is empty.',
            ! $summary['meets_min_order'] => 'The minimum order is ৳'.number_format($summary['min_order']).'.',
            Setting::get('cod') !== '1' => 'Cash on delivery is not available right now.',
            $this->codBlockedBy($summary['items']) !== null => $this->codBlockedBy($summary['items']).' is not available for cash on delivery. Please remove it from your cart.',
            default => null,
        };
    }

    /**
     * @param  array{name: string, phone: string, email: string, district: string, area: string, address: string, note?: string|null}  $data
     *
     * @throws DomainException with a message that is safe to show to the customer
     */
    public function place(array $data): Order
    {
        if ($blocker = $this->blocker()) {
            throw new DomainException($blocker);
        }

        $email = Str::lower(trim((string) ($data['email'] ?? '')));

        $blocked = User::query()
            ->where('is_restricted', true)
            ->where(function ($query) use ($data, $email) {
                $query->where('phone', $data['phone']);

                if ($email !== '') {
                    $query->orWhere('email', $email);
                }
            })
            ->exists();

        if ($blocked) {
            throw new DomainException(User::restrictedMessage());
        }

        // Reading the cart already corrects quantities and drops items that cannot be sold. If it had to,
        // stop here so the customer sees the corrected order before paying for it.
        if ($notices = $this->cart->summary()['notices']) {
            throw new DomainException(implode(' ', $notices).' Please review your order.');
        }

        $zone = self::zoneOf($data['district']);

        // The cart's zone follows the district, so the delivery fee below is the one for this address.
        $this->cart->setZone($zone);
        $summary = $this->cart->summary();

        if (! $summary['items']) {
            throw new DomainException('Your cart is empty.');
        }

        $userId = auth()->id() ?? $this->accountFor($data)->id;

        $order = DB::transaction(function () use ($data, $summary, $zone, $userId) {
            $this->takeStock($summary['items']);

            $discount = 0.0;
            $couponCode = null;
            $coupon = null;

            if ($summary['coupon']) {
                $coupon = Coupon::query()->lockForUpdate()->where('code', $summary['coupon']['code'])->first();

                if ($coupon) {
                    $result = $coupon->evaluate($summary['items'], $summary['subtotal'], $userId);
                    $discount = $result['discount'];
                    $couponCode = $coupon->code;
                }
            }

            $order = Order::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'division' => self::divisionOf($data['district']) ?? '',
                'district' => $data['district'],
                'area' => $data['area'],
                'address' => $data['address'],
                'note' => $data['note'] ?? null,
                'zone' => $zone,
                'zone_label' => $summary['shipping']['label'],
                'delivery_time' => $summary['shipping']['time'] ?: null,
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'subtotal' => $summary['subtotal'],
                'discount' => $discount,
                'coupon_code' => $couponCode,
                'delivery_fee' => $summary['shipping']['fee'],
                'total' => $summary['total'],
            ]);

            $order->update(['number' => 'NM-'.(10000 + $order->id)]);

            $skus = $this->skus($summary['items']);
            $costs = Product::query()->whereIn('id', array_column($summary['items'], 'product_id'))->pluck('cost_price', 'id');

            foreach ($summary['items'] as $line) {
                $order->items()->create([
                    'product_id' => $line['product_id'],
                    'variant_id' => $line['variant_id'],
                    'name' => $line['name'],
                    'variant_label' => $line['variant'],
                    'sku' => $skus[$line['key']] ?? null,
                    'image' => $line['image'],
                    'unit_price' => $line['unit_price'],
                    'old_price' => $line['old_price'],
                    'cost_price' => $costs[$line['product_id']] ?? null,
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);
            }

            OrderEvent::create(['order_id' => $order->id, 'status' => 'pending', 'note' => 'Order placed']);

            $this->rememberAddress($userId, $data);

            if ($coupon && $couponCode) {
                $coupon->redeem($userId, $discount, $order->id);
            }

            return $order;
        });

        $this->cart->clear();

        AdminNotifier::newOrder($order);

        return $order;
    }

    /**
     * Puts the stock of a cancelled or returned order back on the shelf (once).
     */
    public function restock(Order $order): void
    {
        if ($order->restocked_at) {
            return;
        }

        foreach ($order->items as $item) {
            $quantity = $item->quantity - $item->restocked_quantity;

            if ($quantity <= 0) {
                continue;
            }

            $item->update(['restocked_quantity' => $item->quantity]);

            if (! $item->product_id) {
                continue;
            }

            $product = Product::query()->lockForUpdate()->find($item->product_id);

            if (! $product) {
                continue;
            }

            if ($product->track_inventory) {
                if ($item->variant_id) {
                    ProductVariant::query()->where('id', $item->variant_id)->increment('stock', $quantity);
                } else {
                    $product->increment('stock', $quantity);
                }
            }

            $product->update(['sold_count' => max(0, $product->sold_count - $quantity)]);
        }

        $order->update(['restocked_at' => now()]);
    }

    /**
     * Locks the rows being sold, checks the stock one last time and takes it off.
     *
     * @param  list<array<string, mixed>>  $lines
     *
     * @throws DomainException
     */
    private function takeStock(array $lines): void
    {
        foreach ($lines as $line) {
            $product = Product::query()->lockForUpdate()->find($line['product_id']);

            if (! $product || $product->status !== 'active') {
                throw new DomainException("{$line['name']} is no longer available. Please review your cart.");
            }

            $variant = $line['variant_id']
                ? ProductVariant::query()->lockForUpdate()->where('product_id', $product->id)->find($line['variant_id'])
                : null;

            if ($line['variant_id'] && ! $variant) {
                throw new DomainException("The selected option of {$line['name']} is no longer available. Please review your cart.");
            }

            if ($product->track_inventory) {
                $available = (int) ($variant?->stock ?? $product->stock);

                if ($available < $line['quantity']) {
                    throw new DomainException("Only {$available} of {$line['name']} left in stock. Please review your cart.");
                }

                if ($variant) {
                    $variant->decrement('stock', $line['quantity']);
                } else {
                    $product->decrement('stock', $line['quantity']);
                }
            }

            $this->takeFlashStock($product, $line);

            $product->increment('sold_count', $line['quantity']);

            AdminNotifier::lowStock($product);
        }
    }

    /** @param  array<string, mixed>  $line */
    private function takeFlashStock(Product $product, array $line): void
    {
        if (! $line['is_flash']) {
            return;
        }

        $deal = $product->flash_item;

        if (! $deal || $deal->deal_stock === null) {
            return;
        }

        $row = FlashDealProduct::query()->lockForUpdate()->find($deal->id);

        if (! $row || $row->deal_stock === null) {
            return;
        }

        if ($row->deal_stock < $line['quantity']) {
            throw new DomainException("The flash deal for {$line['name']} just sold out. Please review your cart.");
        }

        $row->decrement('deal_stock', $line['quantity']);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return array<string, string|null> SKU per cart line key
     */
    private function skus(array $lines): array
    {
        $products = Product::query()->whereIn('id', array_column($lines, 'product_id'))->get(['id', 'sku'])->keyBy('id');
        $variants = ProductVariant::query()->whereIn('id', array_filter(array_column($lines, 'variant_id')))->get(['id', 'sku'])->keyBy('id');
        $skus = [];

        foreach ($lines as $line) {
            $skus[$line['key']] = ($line['variant_id'] ? $variants->get($line['variant_id'])?->sku : null)
                ?: $products->get($line['product_id'])?->sku;
        }

        return $skus;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return string|null name of the first product that cannot be paid for in cash
     */
    private function codBlockedBy(array $lines): ?string
    {
        $blocked = Product::query()
            ->whereIn('id', array_column($lines, 'product_id'))
            ->where('is_cod', false)
            ->first(['id', 'name']);

        return $blocked?->name;
    }
}
