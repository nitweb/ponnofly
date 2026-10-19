<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'phone', 'email', 'topic', 'message', 'status', 'reply', 'replied_at', 'ip_address'])]
class ContactMessage extends Model
{
    public const TOPICS = [
        'Order or delivery',
        'Returns and refunds',
        'Payment issue',
        'Something else',
    ];

    public const STATUSES = [
        'unread' => 'Unread',
        'read' => 'Read',
        'replied' => 'Replied',
    ];

    public const BADGES = [
        'unread' => 'b-warn',
        'read' => 'b-muted',
        'replied' => 'b-success',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->where('status', 'unread');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr(trim($this->name), 0, 1));
    }
}
