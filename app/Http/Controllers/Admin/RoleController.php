<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->withCount('admins')->orderByDesc('is_locked')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', ['role' => null, 'presets' => $this->presets()]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        Role::create($this->payload($request));

        return to_route('admin.roles.index')->with('success', 'Role saved');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', ['role' => $role, 'presets' => $this->presets()]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->is_locked) {
            return to_route('admin.roles.index')->with('error', 'The Super Admin role is locked and cannot be changed.');
        }

        $role->update($this->payload($request));

        return to_route('admin.roles.index')->with('success', 'Role updated');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_locked) {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        if ($role->admins()->exists()) {
            return back()->with('error', 'Reassign its staff members before deleting this role.');
        }

        $role->delete();

        return to_route('admin.roles.index')->with('success', 'Role deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(RoleRequest $request): array
    {
        $data = $request->safe()->except(['permissions']);
        $permissions = $request->validated('permissions', []);

        // An admin can only hand out permissions they hold themselves.
        $actor = $request->user('admin');

        if (! $actor->isSuperAdmin()) {
            $permissions = array_values(array_filter($permissions, fn (string $permission) => $actor->hasPermission($permission)));
        }

        $data['permissions'] = array_values(array_unique($permissions));

        return $data;
    }

    /**
     * Permission lists (for the "copy permissions from" helper), keyed by role name.
     *
     * @return array<string, list<string>|string>
     */
    private function presets(): array
    {
        return Role::query()->orderBy('id')->get()->mapWithKeys(
            fn (Role $role) => [$role->name => $role->isSuperAdmin() ? 'all' : $role->permissionKeys()]
        )->all();
    }
}
