<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'color', 'permissions', 'is_active'])]
class Role extends Model
{
    public const SUPER_ADMIN_SLUG = 'super-admin';

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === self::SUPER_ADMIN_SLUG;
    }

    /**
     * @return list<string>
     */
    public function permissionKeys(): array
    {
        return $this->isSuperAdmin() ? Permissions::all() : array_values($this->permissions ?? []);
    }

    public function permissionCount(): int
    {
        return count($this->permissionKeys());
    }

    public function allows(string $permission): bool
    {
        return $this->is_active && in_array($permission, $this->permissionKeys(), true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
