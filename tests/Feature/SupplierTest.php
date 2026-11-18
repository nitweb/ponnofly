<?php

use App\Models\Admin;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;

function supplierAdmin(array $permissions = []): Admin
{
    static $n = 0;
    $n++;

    $role = $permissions === []
        ? Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin', 'color' => '#DC2626', 'is_locked' => true, 'is_active' => true])
        : Role::create(['name' => "Supplier Role {$n}", 'slug' => "supplier-role-{$n}", 'color' => '#0B3A8C', 'permissions' => $permissions, 'is_active' => true]);

    return Admin::forceCreate([
        'name' => "Supplier Admin {$n}",
        'email' => "supplier-admin{$n}@example.com",
        'password' => bcrypt('secret-pass-1'),
        'role_id' => $role->id,
        'is_active' => true,
    ]);
}

function supplierProduct(array $attributes = []): Product
{
    static $n = 0;
    $n++;

    return Product::create($attributes + [
        'name' => "Supplied Item {$n}",
        'slug' => "supplied-item-{$n}",
        'price' => 1000,
        'stock' => 10,
        'status' => 'active',
    ]);
}

it('lets an admin create, update and delete a supplier', function () {
    $admin = supplierAdmin();

    $this->actingAs($admin, 'admin')->post(route('admin.suppliers.store'), [
        'name' => 'Dhaka Wholesale', 'phone' => '01711-000000', 'is_active' => '1',
    ])->assertRedirect(route('admin.suppliers.index'));

    $supplier = Supplier::firstWhere('name', 'Dhaka Wholesale');
    expect($supplier)->not->toBeNull()->and($supplier->is_active)->toBeTrue();

    $this->actingAs($admin, 'admin')->put(route('admin.suppliers.update', $supplier), ['name' => 'Dhaka Wholesale Ltd'])
        ->assertRedirect(route('admin.suppliers.index'));
    expect($supplier->refresh()->name)->toBe('Dhaka Wholesale Ltd')->and($supplier->is_active)->toBeFalse();

    $this->actingAs($admin, 'admin')->delete(route('admin.suppliers.destroy', $supplier))->assertRedirect();
    expect(Supplier::count())->toBe(0);
});

it('shows which supplier a product came from in the admin panel', function () {
    $admin = supplierAdmin();
    $supplier = Supplier::create(['name' => 'Gulshan Imports']);
    $product = supplierProduct(['supplier_id' => $supplier->id]);

    $this->actingAs($admin, 'admin')->get(route('admin.products.index'))->assertOk()->assertSee('Gulshan Imports');
    $this->actingAs($admin, 'admin')->get(route('admin.products.index', ['supplier' => $supplier->id]))->assertOk()->assertSee($product->name);
    $this->actingAs($admin, 'admin')->get(route('admin.products.edit', $product))->assertOk()->assertSee('Gulshan Imports');
});

it('never exposes the supplier on the storefront', function () {
    $supplier = Supplier::create(['name' => 'Secret Source Co']);
    $product = supplierProduct(['supplier_id' => $supplier->id]);

    $this->get(route('product.show', $product))->assertOk()->assertDontSee('Secret Source Co');
});

it('keeps the product when its supplier is deleted', function () {
    $supplier = Supplier::create(['name' => 'Temporary Supplier']);
    $product = supplierProduct(['supplier_id' => $supplier->id]);

    $supplier->delete();

    expect($product->refresh()->supplier_id)->toBeNull();
});

it('requires the suppliers permission', function () {
    $viewer = supplierAdmin(['products:view']);

    $this->actingAs($viewer, 'admin')->get(route('admin.suppliers.index'))->assertForbidden();

    $allowed = supplierAdmin(['suppliers:view']);
    $this->actingAs($allowed, 'admin')->get(route('admin.suppliers.index'))->assertOk();
});
