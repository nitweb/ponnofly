<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    private const ACTIONS = [
        'index' => 'view', 'show' => 'view',
        'create' => 'create', 'store' => 'create',
        'edit' => 'edit', 'update' => 'edit', 'toggle' => 'edit', 'reorder' => 'edit',
        'reply' => 'edit', 'note' => 'edit', 'readAll' => 'edit',
        'destroy' => 'delete',
        'export' => 'export',
        'sales' => 'view', 'profit' => 'view', 'products' => 'view', 'customers' => 'view',
        'staff' => 'view', 'staffShow' => 'view',
    ];

    /**
     * @param  string  $module  permission module key
     * @param  string|null  $mode  "singleton" for edit/update pages where "edit" only opens the form (view)
     */
    public function handle(Request $request, Closure $next, string $module, ?string $mode = null): Response
    {
        $method = $request->route()?->getActionMethod();
        $action = self::ACTIONS[$method] ?? 'edit';

        if ($mode === 'singleton' && $method === 'edit') {
            $action = 'view';
        }

        abort_unless($request->user('admin')?->hasPermission("{$module}:{$action}"), 403, 'You do not have permission to do that.');

        return $next($request);
    }
}
