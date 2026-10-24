<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_id', 'user_id', 'admin_id', 'type', 'status', 'reason', 'details', 'admin_note', 'resolved_at'])]
class OrderReturn extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public const REASONS = [
        'changed_mind' => 'Changed my mind',
        'ordered_by_mistake' => 'Ordered by mistake',
        'wrong_item' => 'Wrong item received',
        'damaged' => 'Item arrived damaged',
        'defective' => 'Item is defective',
        'late_delivery' => 'Delivery is too late',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class)->orderBy('id');
    }

    public function getNumberAttribute(): string
    {
        return 'RT-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst($this->reason);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'return' ? 'Return' : 'Cancellation';
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getAmountAttribute(): float
    {
        return (float) $this->items->sum(fn (OrderReturnItem $line) => ($line->orderItem?->unit_price ?? 0) * $line->quantity);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
