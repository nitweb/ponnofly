<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'bio', 'avatar', 'password', 'role_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AdminActivity::class)->latest()->latest('id');
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim($this->name), 0, 1)) ?: 'A';
    }

    public function displayTitle(): string
    {
        return $this->role?->name ?: ($this->job_title ?: 'Administrator');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Accounts created before roles existed have no role and keep full access.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_id === null || (bool) $this->role?->isSuperAdmin();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->isSuperAdmin() || (bool) $this->role?->allows($permission);
    }

    /**
     * Route name of the first page this admin may open (used when the dashboard is off limits).
     */
    public function homeRoute(): string
    {
        $candidates = [
            'products' => 'admin.products.index', 'orders' => 'admin.orders.index', 'customers' => 'admin.customers.index',
            'categories' => 'admin.categories.index', 'brands' => 'admin.brands.index', 'coupons' => 'admin.coupons.index',
            'flash' => 'admin.flash-deals.index', 'banners' => 'admin.banners.index', 'pages' => 'admin.about.edit',
            'faqs' => 'admin.faqs.index', 'messages' => 'admin.messages.index', 'settings' => 'admin.settings.edit',
            'reports' => 'admin.reports.index', 'expenses' => 'admin.expenses.index', 'roles' => 'admin.roles.index', 'staff' => 'admin.staff.index',
        ];

        foreach ($candidates as $module => $route) {
            if ($this->hasPermission("{$module}:view")) {
                return $route;
            }
        }

        return 'admin.profile.edit';
    }
}
