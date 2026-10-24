<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['flash_deal_id', 'product_id', 'discount_percent', 'deal_stock', 'sort_order'])]
class FlashDealProduct extends Model
{
    protected function casts(): array
    {
        return [
            'discount_percent' => 'float',
            'deal_stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => FlashDeal::flushLiveCache());
        static::deleted(fn () => FlashDeal::flushLiveCache());
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(FlashDeal::class, 'flash_deal_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceFor(float $price): float
    {
        return (float) round($price * (1 - $this->discount_percent / 100));
    }
}
