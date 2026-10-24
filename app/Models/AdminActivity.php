<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

#[Fillable(['admin_id', 'type', 'description', 'ip_address', 'user_agent'])]
class AdminActivity extends Model
{
    public const RETENTION_DAYS = 30;

    /** type => [icon id, badge classes] */
    public const TYPES = [
        'login' => ['lock', 'bg-ink/10 text-ink'],
        'profile' => ['user', 'bg-primary/10 text-primary'],
        'password' => ['lock', 'bg-success/10 text-success'],
        'avatar' => ['camera', 'bg-accent-dark/10 text-accent-dark'],
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public static function record(Admin $admin, string $type, string $description, ?Request $request = null): self
    {
        $request ??= request();

        return $admin->activities()->create([
            'type' => $type,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    public function iconId(): string
    {
        return (self::TYPES[$this->type] ?? self::TYPES['profile'])[0];
    }

    public function iconClasses(): string
    {
        return (self::TYPES[$this->type] ?? self::TYPES['profile'])[1];
    }

    public function browserLabel(): string
    {
        $agent = (string) $this->user_agent;

        $browser = match (true) {
            str_contains($agent, 'Edg') => 'Edge',
            str_contains($agent, 'OPR') || str_contains($agent, 'Opera') => 'Opera',
            str_contains($agent, 'Chrome') => 'Chrome',
            str_contains($agent, 'Firefox') => 'Firefox',
            str_contains($agent, 'Safari') => 'Safari',
            default => 'Unknown browser',
        };

        $os = match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => null,
        };

        return $os ? "{$browser} · {$os}" : $browser;
    }
}
