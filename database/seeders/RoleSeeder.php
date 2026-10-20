<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->updateOrCreate(['slug' => Role::SUPER_ADMIN_SLUG], [
            'name' => 'Super Admin',
            'description' => 'Full access to everything including roles and settings.',
            'color' => '#DC2626',
            'permissions' => null,
            'is_active' => true,
            'is_locked' => true,
        ]);

        foreach (config('permissions.presets') as $name => $preset) {
            $permissions = $preset['permissions'] ?? $this->derive($preset);

            Role::query()->firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => $preset['description'],
                'color' => $preset['color'],
                'permissions' => $permissions,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $preset
     * @return list<string>
     */
    private function derive(array $preset): array
    {
        $permissions = [];

        foreach (Permissions::modules() as $module => $definition) {
            if (in_array($module, $preset['except'] ?? [], true)) {
                continue;
            }

            $actions = in_array($module, $preset['only_view'] ?? [], true) ? ['view'] : $definition['actions'];

            foreach ($actions as $action) {
                $permissions[] = "{$module}:{$action}";
            }
        }

        return $permissions;
    }
}
