<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    private const DIRECTORY = 'uploads/avatars';

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $roleId = $request->integer('role');

        $staff = Admin::query()
            ->with('role')
            ->addSelect([
                'last_login_at' => AdminActivity::query()->select('created_at')
                    ->whereColumn('admin_id', 'admins.id')->where('type', 'login')->latest()->limit(1),
            ])
            ->withCasts(['last_login_at' => 'datetime'])
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->when($roleId, fn ($query) => $query->where('role_id', $roleId))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.staff.index', [
            'staff' => $staff,
            'roles' => Role::orderBy('id')->get(['id', 'name']),
            'search' => $search,
            'roleId' => $roleId,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.staff.create', $this->formData($request) + ['member' => null]);
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $this->guardRoleAssignment($request, $request->integer('role_id'));

        $admin = new Admin($request->safe()->except(['avatar', 'remove_avatar']));

        if ($request->hasFile('avatar')) {
            $admin->avatar = $this->storeAvatar($request->file('avatar'));
        }

        $admin->save();

        return to_route('admin.staff.index')->with('success', 'Staff member saved');
    }

    public function edit(Request $request, Admin $staff): View
    {
        $this->guardSuperAdminTarget($request, $staff);

        return view('admin.staff.edit', $this->formData($request) + ['member' => $staff]);
    }

    public function update(StaffRequest $request, Admin $staff): RedirectResponse
    {
        $this->guardSuperAdminTarget($request, $staff);
        $isSelf = $request->user('admin')->is($staff);

        if (! $isSelf) {
            $this->guardRoleAssignment($request, $request->integer('role_id'));
        }

        $data = $request->safe()->except(['avatar', 'remove_avatar', 'password']);

        // Nobody can change their own role or status from here.
        if ($isSelf) {
            unset($data['role_id'], $data['is_active']);
        }

        if (! $isSelf && $this->isLastSuperAdmin($staff) && ($request->integer('role_id') !== (int) $staff->role_id || ! $request->boolean('is_active'))) {
            return back()->withInput()->with('error', 'At least one active Super Admin must remain.');
        }

        $staff->fill($data);

        if ($request->filled('password')) {
            $staff->password = $request->input('password');
        }

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar((string) $staff->avatar);
            $staff->avatar = $this->storeAvatar($request->file('avatar'));
        } elseif ($request->boolean('remove_avatar') && $staff->avatar) {
            $this->deleteAvatar((string) $staff->avatar);
            $staff->avatar = null;
        }

        $staff->save();

        return to_route('admin.staff.index')->with('success', 'Staff member updated');
    }

    public function toggle(Request $request, Admin $staff): RedirectResponse
    {
        $this->guardSuperAdminTarget($request, $staff);

        if ($request->user('admin')->is($staff)) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($staff->is_active && $this->isLastSuperAdmin($staff)) {
            return back()->with('error', 'At least one active Super Admin must remain.');
        }

        $staff->update(['is_active' => ! $staff->is_active]);

        return back()->with('success', $staff->is_active ? 'Staff member activated' : 'Staff member deactivated');
    }

    public function destroy(Request $request, Admin $staff): RedirectResponse
    {
        $this->guardSuperAdminTarget($request, $staff);

        if ($request->user('admin')->is($staff)) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($this->isLastSuperAdmin($staff, countInactive: true)) {
            return back()->with('error', 'The last Super Admin cannot be deleted.');
        }

        $this->deleteAvatar((string) $staff->avatar);
        $staff->delete();

        return to_route('admin.staff.index')->with('success', 'Staff member deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        $actor = $request->user('admin');

        $roles = Role::active()->orderBy('id')->get()
            ->reject(fn (Role $role) => $role->isSuperAdmin() && ! $actor->isSuperAdmin())
            ->values();

        $preview = $roles->mapWithKeys(function (Role $role) {
            $keys = $role->permissionKeys();

            return [$role->id => collect(Permissions::modules())
                ->map(fn (array $module, string $key) => ['label' => $module['label'], 'access' => Permissions::summary($key, $keys)])
                ->values()->all()];
        })->all();

        return ['roles' => $roles, 'preview' => $preview];
    }

    private function guardRoleAssignment(Request $request, int $roleId): void
    {
        $role = Role::find($roleId);

        abort_if($role?->isSuperAdmin() && ! $request->user('admin')->isSuperAdmin(), 403, 'Only a Super Admin can assign the Super Admin role.');
    }

    private function guardSuperAdminTarget(Request $request, Admin $target): void
    {
        abort_if($target->isSuperAdmin() && ! $request->user('admin')->isSuperAdmin(), 403, 'Only a Super Admin can manage another Super Admin.');
    }

    private function isLastSuperAdmin(Admin $admin, bool $countInactive = false): bool
    {
        if (! $admin->isSuperAdmin()) {
            return false;
        }

        $others = Admin::query()
            ->whereKeyNot($admin->id)
            ->when(! $countInactive, fn ($query) => $query->where('is_active', true))
            ->where(fn ($query) => $query->whereNull('role_id')->orWhereHas('role', fn ($role) => $role->where('slug', Role::SUPER_ADMIN_SLUG)))
            ->exists();

        return ! $others;
    }

    private function storeAvatar(UploadedFile $file): string
    {
        File::ensureDirectoryExists(public_path(self::DIRECTORY));

        $name = 'avatar-' . Str::uuid() . '.' . ($file->guessExtension() ?: 'png');

        $file->move(public_path(self::DIRECTORY), $name);

        return self::DIRECTORY . '/' . $name;
    }

    private function deleteAvatar(string $path): void
    {
        if (str_starts_with($path, self::DIRECTORY . '/')) {
            File::delete(public_path($path));
        }
    }
}
