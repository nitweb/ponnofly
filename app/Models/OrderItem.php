<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id', 'product_id', 'variant_id', 'name', 'variant_label', 'sku', 'image',
    'unit_price', 'old_price', 'cost_price', 'quantity', 'restocked_quantity', 'line_total',
])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
            'old_price' => 'float',
            'cost_price' => 'float',
            'quantity' => 'integer',
            'restocked_quantity' => 'integer',
            'line_total' => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    /** How many units of this line can still be put in a new return or cancellation request. */
    public function getReturnableQuantityAttribute(): int
    {
        $claimed = $this->returnItems
            ->filter(fn (OrderReturnItem $line) => in_array($line->orderReturn?->status, ['pending', 'approved'], true))
            ->sum('quantity');

        return max(0, $this->quantity - (int) $claimed);
    }
}
