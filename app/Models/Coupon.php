<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'type', 'value', 'max_discount', 'min_order', 'usage_limit', 'per_customer_limit',
    'used_count', 'applies_to', 'description', 'starts_at', 'expires_at', 'is_active', 'show_on_checkout',
])]
class Coupon extends Model
{
    public const TYPES = [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed amount',
        'free_delivery' => 'Free delivery',
    ];

    public const SCOPES = [
        'all' => 'All products',
        'categories' => 'Selected categories',
        'products' => 'Selected products',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'max_discount' => 'float',
            'min_order' => 'float',
            'usage_limit' => 'integer',
            'per_customer_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'date',
            'expires_at' => 'date',
            'is_active' => 'boolean',
            'show_on_checkout' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public static function normalizeCode(?string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $code));
    }

    public static function findByCode(?string $code): ?self
    {
        $code = static::normalizeCode($code);

        return $code === '' ? null : static::query()->where('code', $code)->first();
    }

    /** @return 'active'|'scheduled'|'expired'|'exhausted'|'inactive' */
    public function phase(): string
    {
        $today = CarbonImmutable::now(Setting::tz())->format('Y-m-d');

        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at && $this->starts_at->format('Y-m-d') > $today) {
            return 'scheduled';
        }

        if ($this->expires_at && $this->expires_at->format('Y-m-d') < $today) {
            return 'expired';
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'exhausted';
        }

        return 'active';
    }

    public function getDiscountLabelAttribute(): string
    {
        $fmt = fn (float $n) => number_format($n, floor($n) == $n ? 0 : 2);

        return match ($this->type) {
            'percentage' => $fmt($this->value).'%',
            'fixed' => '৳'.$fmt($this->value),
            default => 'Free delivery',
        };
    }

    /**
     * Validates the coupon against a cart and works out the discount.
     *
     * @param  list<array<string, mixed>>  $lines  cart lines with product_id, category_id and line_total
     * @return array{discount: float, free_delivery: bool}
     *
     * @throws DomainException with a message that is safe to show to the customer
     */
    public function evaluate(array $lines, float $subtotal, ?int $userId = null): array
    {
        $fmt = fn (float $n) => '৳'.number_format($n, floor($n) == $n ? 0 : 2);

        match ($this->phase()) {
            'inactive', 'expired' => throw new DomainException('This coupon is no longer valid.'),
            'scheduled' => throw new DomainException('This coupon is not active yet.'),
            'exhausted' => throw new DomainException('This coupon has been fully redeemed.'),
            default => null,
        };

        if ($this->min_order && $subtotal < $this->min_order) {
            throw new DomainException('Spend at least '.$fmt($this->min_order).' to use this coupon.');
        }

        if ($userId && $this->per_customer_limit
            && $this->usages()->where('user_id', $userId)->count() >= $this->per_customer_limit) {
            throw new DomainException('You have already used this coupon.');
        }

        $eligible = $this->eligibleSubtotal($lines, $subtotal);

        if ($eligible <= 0) {
            throw new DomainException('This coupon does not apply to the items in your cart.');
        }

        if ($this->type === 'free_delivery') {
            return ['discount' => 0.0, 'free_delivery' => true];
        }

        $discount = $this->type === 'percentage'
            ? round($eligible * $this->value / 100, 2)
            : min($this->value, $eligible);

        if ($this->type === 'percentage' && $this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }

        return ['discount' => round(min($discount, $subtotal), 2), 'free_delivery' => false];
    }

    /** Records one redemption; checkout calls this once the order is placed. */
    public function redeem(?int $userId, float $discount, ?int $orderId = null): void
    {
        $this->usages()->create(['user_id' => $userId, 'order_id' => $orderId, 'discount' => $discount]);
        $this->increment('used_count');
    }

    /** @param  list<array<string, mixed>>  $lines */
    private function eligibleSubtotal(array $lines, float $subtotal): float
    {
        if ($this->applies_to === 'products') {
            $ids = $this->products()->pluck('products.id')->all();

            return (float) collect($lines)->whereIn('product_id', $ids)->sum('line_total');
        }

        if ($this->applies_to === 'categories') {
            $ids = $this->categories()->pluck('categories.id')
                ->flatMap(fn ($id) => [$id, ...Category::descendantIds((int) $id)])
                ->unique()
                ->all();

            return (float) collect($lines)->whereIn('category_id', $ids)->sum('line_total');
        }

        return $subtotal;
    }
}
