<?php

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Support\Facades\File;

function superRole(): Role
{
    return Role::firstOrCreate(['slug' => 'super-admin'], [
        'name' => 'Super Admin', 'color' => '#DC2626', 'is_locked' => true, 'is_active' => true,
    ]);
}

function roleWith(array $permissions, string $name = 'Custom'): Role
{
    return Role::create(['name' => $name, 'slug' => str($name)->slug(), 'color' => '#0B3A8C', 'permissions' => $permissions, 'is_active' => true]);
}

function staffUser(?Role $role = null, array $attributes = []): Admin
{
    static $n = 0;
    $n++;

    return Admin::forceCreate($attributes + [
        'name' => "Staff {$n}",
        'email' => "staff{$n}@example.com",
        'password' => bcrypt('secret-pass-1'),
        'role_id' => ($role ?? superRole())->id,
        'is_active' => true,
    ]);
}

afterEach(function () {
    File::deleteDirectory(public_path('uploads/avatars'));
});

it('lets a role with the right permission in and keeps everything else out', function () {
    $editor = staffUser(roleWith(['coupons:view', 'coupons:create']));

    $this->actingAs($editor, 'admin')->get(route('admin.coupons.index'))->assertOk();
    $this->actingAs($editor, 'admin')->get(route('admin.coupons.create'))->assertOk();
    $this->actingAs($editor, 'admin')->get(route('admin.products.index'))->assertForbidden();
    $this->actingAs($editor, 'admin')->get(route('admin.staff.index'))->assertForbidden();
    $this->actingAs($editor, 'admin')->get(route('admin.settings.edit'))->assertForbidden();
});

it('maps the action to the matching permission', function () {
    $viewer = staffUser(roleWith(['coupons:view']));
    $coupon = \App\Models\Coupon::create(['code' => 'TEST10', 'type' => 'percentage', 'value' => 10, 'applies_to' => 'all', 'is_active' => true]);

    $this->actingAs($viewer, 'admin')->get(route('admin.coupons.edit', $coupon))->assertForbidden();
    $this->actingAs($viewer, 'admin')->delete(route('admin.coupons.destroy', $coupon))->assertForbidden();
    expect(\App\Models\Coupon::count())->toBe(1);
});

it('lets accounts without a role keep full access', function () {
    $legacy = Admin::forceCreate(['name' => 'Legacy', 'email' => 'legacy@example.com', 'password' => bcrypt('secret-pass-1')]);

    $this->actingAs($legacy, 'admin')->get(route('admin.roles.index'))->assertOk();
});

it('ignores permissions of an inactive role', function () {
    $role = roleWith(['coupons:view']);
    $role->update(['is_active' => false]);

    $this->actingAs(staffUser($role), 'admin')->get(route('admin.coupons.index'))->assertForbidden();
});

it('sends an admin without dashboard access to the first page they may open', function () {
    $agent = staffUser(roleWith(['orders:view']));

    $this->actingAs($agent, 'admin')->get(route('admin.dashboard'))->assertRedirect(route('admin.orders.index'));
});

it('hides sidebar links the role cannot open', function () {
    $agent = staffUser(roleWith(['dashboard:view', 'orders:view']));

    $this->actingAs($agent, 'admin')->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.orders.index'), false)
        ->assertDontSee(route('admin.staff.index'), false)
        ->assertDontSee(route('admin.roles.index'), false);
});

it('creates, updates and deletes a role', function () {
    $admin = staffUser();

    $this->actingAs($admin, 'admin')
        ->post(route('admin.roles.store'), [
            'name' => 'Warehouse', 'slug' => '', 'description' => 'Packs orders', 'color' => '#0A8F6B',
            'is_active' => '1', 'perm' => ['orders:view', 'orders:edit', 'bogus:view'],
        ])
        ->assertSessionHasErrors('permissions.2');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.roles.store'), [
            'name' => 'Warehouse', 'slug' => '', 'description' => 'Packs orders', 'color' => '#0A8F6B',
            'is_active' => '1', 'perm' => ['orders:view', 'orders:edit'],
        ])
        ->assertRedirect(route('admin.roles.index'));

    $role = Role::firstWhere('slug', 'warehouse');
    expect($role->permissions)->toBe(['orders:view', 'orders:edit']);

    $this->actingAs($admin, 'admin')
        ->put(route('admin.roles.update', $role), ['name' => 'Warehouse', 'slug' => 'warehouse', 'color' => '#0A8F6B', 'is_active' => '1', 'perm' => ['orders:view']])
        ->assertRedirect(route('admin.roles.index'));

    expect($role->refresh()->permissions)->toBe(['orders:view']);

    $this->actingAs($admin, 'admin')->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
    expect(Role::where('slug', 'warehouse')->exists())->toBeFalse();
});

it('locks the Super Admin role', function () {
    $admin = staffUser();

    $this->actingAs($admin, 'admin')->put(route('admin.roles.update', superRole()), ['name' => 'Hacked', 'slug' => 'super-admin', 'color' => '#000000', 'perm' => []])
        ->assertSessionHas('error');
    $this->actingAs($admin, 'admin')->delete(route('admin.roles.destroy', superRole()))->assertSessionHas('error');

    expect(superRole()->refresh()->name)->toBe('Super Admin');
});

it('refuses to delete a role that still has staff', function () {
    $role = roleWith(['orders:view'], 'Busy');
    staffUser($role);

    $this->actingAs(staffUser(), 'admin')->delete(route('admin.roles.destroy', $role))->assertSessionHas('error');

    expect(Role::where('slug', 'busy')->exists())->toBeTrue();
});

it('stops a non-super admin from granting permissions they do not hold', function () {
    $manager = staffUser(roleWith(['roles:view', 'roles:create', 'orders:view'], 'Role Manager'));

    $this->actingAs($manager, 'admin')->post(route('admin.roles.store'), [
        'name' => 'Sneaky', 'slug' => '', 'color' => '#111111', 'is_active' => '1', 'perm' => ['orders:view', 'staff:delete', 'settings:edit'],
    ])->assertRedirect(route('admin.roles.index'));

    expect(Role::firstWhere('slug', 'sneaky')->permissions)->toBe(['orders:view']);
});

it('adds a staff member with a role and password', function () {
    $role = roleWith(['orders:view'], 'Agent');

    $this->actingAs(staffUser(), 'admin')
        ->post(route('admin.staff.store'), [
            'name' => 'New Agent', 'email' => 'agent@example.com', 'phone' => '01711-445566',
            'role_id' => $role->id, 'password' => 'agent-pass-1', 'password_confirmation' => 'agent-pass-1', 'is_active' => '1',
        ])
        ->assertRedirect(route('admin.staff.index'));

    $agent = Admin::firstWhere('email', 'agent@example.com');

    expect($agent->role_id)->toBe($role->id)
        ->and($agent->is_active)->toBeTrue()
        ->and(Hash::check('agent-pass-1', $agent->password))->toBeTrue();
});

it('requires a password and a valid role for new staff', function () {
    $this->actingAs(staffUser(), 'admin')
        ->post(route('admin.staff.store'), ['name' => 'X', 'email' => 'x@example.com', 'role_id' => 9999])
        ->assertSessionHasErrors(['password', 'role_id']);
});

it('keeps the password when editing without a new one', function () {
    $agent = staffUser(roleWith(['orders:view'], 'Agent'));
    $hash = $agent->password;

    $this->actingAs(staffUser(), 'admin')
        ->put(route('admin.staff.update', $agent), ['name' => 'Renamed', 'email' => $agent->email, 'role_id' => $agent->role_id, 'is_active' => '1'])
        ->assertRedirect(route('admin.staff.index'));

    expect($agent->refresh()->name)->toBe('Renamed')->and($agent->password)->toBe($hash);
});

it('toggles and deletes staff', function () {
    $agent = staffUser(roleWith(['orders:view'], 'Agent'));
    $admin = staffUser();

    $this->actingAs($admin, 'admin')->patch(route('admin.staff.toggle', $agent))->assertSessionHas('success');
    expect($agent->refresh()->is_active)->toBeFalse();

    $this->actingAs($admin, 'admin')->delete(route('admin.staff.destroy', $agent))->assertRedirect(route('admin.staff.index'));
    expect(Admin::find($agent->id))->toBeNull();
});

it('does not let anyone delete or deactivate themselves', function () {
    $admin = staffUser();
    staffUser(); // a second Super Admin so the last-admin rule is not what blocks this

    $this->actingAs($admin, 'admin')->delete(route('admin.staff.destroy', $admin))->assertSessionHas('error');
    $this->actingAs($admin, 'admin')->patch(route('admin.staff.toggle', $admin))->assertSessionHas('error');

    expect(Admin::find($admin->id)?->is_active)->toBeTrue();
});

it('keeps at least one active Super Admin', function () {
    $actor = staffUser(roleWith(['staff:view', 'staff:edit', 'staff:delete'], 'Staff Manager'));
    $only = staffUser();

    $this->actingAs($actor, 'admin')->patch(route('admin.staff.toggle', $only))->assertForbidden();
});

it('only lets a Super Admin assign or manage the Super Admin role', function () {
    $manager = staffUser(roleWith(['staff:view', 'staff:create', 'staff:edit'], 'Staff Manager'));
    $boss = staffUser();

    $this->actingAs($manager, 'admin')
        ->post(route('admin.staff.store'), ['name' => 'Y', 'email' => 'y@example.com', 'role_id' => superRole()->id, 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'])
        ->assertForbidden();

    $this->actingAs($manager, 'admin')->get(route('admin.staff.edit', $boss))->assertForbidden();
});

it('ignores role and status changes a staff member makes on themselves', function () {
    $role = roleWith(['staff:view', 'staff:edit'], 'Staff Manager');
    $self = staffUser($role);

    $this->actingAs($self, 'admin')
        ->put(route('admin.staff.update', $self), ['name' => 'Me', 'email' => $self->email, 'role_id' => superRole()->id])
        ->assertRedirect(route('admin.staff.index'));

    expect($self->refresh()->role_id)->toBe($role->id)->and($self->is_active)->toBeTrue();
});

it('filters the staff list by role and search', function () {
    $agentRole = roleWith(['orders:view'], 'Agent');
    staffUser($agentRole, ['name' => 'Rakib Hasan', 'email' => 'rakib@example.com']);
    staffUser(null, ['name' => 'Tasnim Rahman', 'email' => 'tasnim@example.com']);

    $this->actingAs(staffUser(), 'admin')->get(route('admin.staff.index', ['role' => $agentRole->id]))
        ->assertOk()->assertSee('Rakib Hasan')->assertDontSee('Tasnim Rahman');

    $this->actingAs(staffUser(), 'admin')->get(route('admin.staff.index', ['q' => 'tasnim']))
        ->assertOk()->assertSee('Tasnim Rahman')->assertDontSee('Rakib Hasan');
});
