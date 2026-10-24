<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'number', 'user_id', 'name', 'phone', 'email', 'division', 'district', 'area', 'address', 'note',
    'zone', 'zone_label', 'delivery_time', 'payment_method', 'payment_status', 'status',
    'subtotal', 'discount', 'coupon_code', 'delivery_fee', 'total',
    'courier', 'tracking_id', 'admin_note', 'restocked_at',
])]
class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];

    /** Statuses an order cannot leave; stock is put back when it enters one of them. */
    public const FINAL_STATUSES = ['cancelled', 'returned'];

    public const PAYMENT_METHODS = [
        'cod' => 'Cash on Delivery',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'float',
            'discount' => 'float',
            'delivery_fee' => 'float',
            'total' => 'float',
            'restocked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class)->latest('id');
    }

    /** Needs items.returnItems.orderReturn loaded to avoid extra queries. */
    public function canRequestReturn(): bool
    {
        return ! $this->isFinal() && $this->items->contains(fn (OrderItem $item) => $item->returnable_quantity > 0);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? strtoupper($this->payment_method);
    }

    public function getCourierLabelAttribute(): ?string
    {
        return $this->courier ? (Setting::COURIERS[$this->courier] ?? $this->courier) : null;
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getFullAddressAttribute(): string
    {
        return $this->address.', '.$this->area.', '.$this->district;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }
}
