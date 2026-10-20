<?php

namespace App\Support;

class Permissions
{
    /**
     * @return array<string, array{label: string, icon: string, actions: list<string>}>
     */
    public static function modules(): array
    {
        return config('permissions.modules');
    }

    /**
     * @return array<string, string>
     */
    public static function actions(): array
    {
        return config('permissions.actions');
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $keys = [];

        foreach (self::modules() as $module => $definition) {
            foreach ($definition['actions'] as $action) {
                $keys[] = "{$module}:{$action}";
            }
        }

        return $keys;
    }

    public static function total(): int
    {
        return count(self::all());
    }

    /**
     * Short label for what a permission list allows inside one module.
     *
     * @param  list<string>  $granted
     */
    public static function summary(string $module, array $granted): string
    {
        $actions = self::modules()[$module]['actions'];
        $has = array_values(array_filter($actions, fn (string $action) => in_array("{$module}:{$action}", $granted, true)));

        return match (true) {
            $has === [] => 'None',
            count($has) === count($actions) => count($actions) === 1 ? 'View' : 'Full',
            $has === ['view'] => 'View',
            default => implode(' · ', array_map(fn (string $action) => self::actions()[$action], $has)),
        };
    }
}
