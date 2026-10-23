<?php

use App\Models\Admin;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Order;
use App\Models\Role;

function reportAdmin(array $permissions = []): Admin
{
    static $n = 0;
    $n++;

    $role = $permissions === []
        ? Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin', 'color' => '#DC2626', 'is_locked' => true, 'is_active' => true])
        : Role::create(['name' => "Reporter {$n}", 'slug' => "reporter-{$n}", 'color' => '#0B3A8C', 'permissions' => $permissions, 'is_active' => true]);

    return Admin::forceCreate([
        'name' => "Reporter {$n}", 'email' => "reporter{$n}@example.com", 'password' => bcrypt('secret-pass-1'),
        'role_id' => $role->id, 'is_active' => true,
    ]);
}

function reportOrder(array $attributes = []): Order
{
    static $n = 0;
    $n++;

    $order = Order::create($attributes + [
        'name' => 'Rahim', 'phone' => '01700000000', 'email' => 'rahim@example.com',
        'division' => 'Dhaka', 'district' => 'Dhaka', 'area' => 'Mirpur', 'address' => 'House 1',
        'zone' => 'inside', 'zone_label' => 'Inside Dhaka', 'status' => 'delivered', 'payment_status' => 'paid',
        'subtotal' => 1000, 'discount' => 0, 'delivery_fee' => 60, 'total' => 1060,
    ]);
    $order->update(['number' => 'NM-'.(10000 + $order->id)]);
    $order->items()->create([
        'name' => 'Widget', 'unit_price' => 500, 'cost_price' => 300, 'quantity' => 2, 'line_total' => 1000,
    ]);

    return $order;
}

it('computes revenue, cost of goods, expenses and profit', function () {
    reportOrder();
    reportOrder(['status' => 'cancelled', 'total' => 5000, 'subtotal' => 5000]);
    Expense::create(['title' => 'Ads', 'amount' => 200, 'expense_date' => today(), 'payment_method' => 'cash']);

    $summary = app(\App\Services\ReportService::class)->summary(
        app(\App\Services\ReportService::class)->period(request()->merge(['range' => '7d']))
    );

    expect($summary['orders'])->toBe(1)
        ->and($summary['net_revenue'])->toBe(1060.0)
        ->and($summary['cogs'])->toBe(600.0)
        ->and($summary['expenses'])->toBe(200.0)
        ->and($summary['net_profit'])->toBe(260.0)
        ->and($summary['lost_orders'])->toBe(1);
});

it('renders every report page for an admin with report access', function () {
    $admin = reportAdmin();
    reportOrder();

    foreach (['index', 'sales', 'profit', 'products', 'customers', 'staff'] as $page) {
        $this->actingAs($admin, 'admin')->get(route("admin.reports.{$page}"))->assertOk();
    }

    $this->actingAs($admin, 'admin')->get(route('admin.reports.staff.show', $admin))->assertOk();
});

it('exports a report as csv only with the export permission', function () {
    reportOrder();

    $viewer = reportAdmin(['reports:view']);
    $exporter = reportAdmin(['reports:view', 'reports:export']);

    $this->actingAs($viewer, 'admin')->get(route('admin.reports.sales'))->assertOk();
    $this->actingAs($viewer, 'admin')->get(route('admin.reports.export', 'sales'))->assertForbidden();

    $response = $this->actingAs($exporter, 'admin')->get(route('admin.reports.export', 'sales'));
    $response->assertOk()->assertDownload();
});

it('keeps reports away from staff without the permission', function () {
    $staff = reportAdmin(['orders:view']);

    $this->actingAs($staff, 'admin')->get(route('admin.reports.index'))->assertForbidden();
    $this->actingAs($staff, 'admin')->get(route('admin.expenses.index'))->assertForbidden();
});

it('creates, updates and deletes expenses', function () {
    $admin = reportAdmin();
    $category = ExpenseCategory::first();

    $this->actingAs($admin, 'admin')->post(route('admin.expenses.store'), [
        'title' => 'Packaging tape', 'amount' => 450, 'expense_date' => today()->toDateString(),
        'payment_method' => 'cash', 'expense_category_id' => $category->id,
    ])->assertRedirect(route('admin.expenses.index'));

    $expense = Expense::firstWhere('title', 'Packaging tape');
    expect($expense->admin_id)->toBe($admin->id);

    $this->actingAs($admin, 'admin')->put(route('admin.expenses.update', $expense), [
        'title' => 'Packaging tape', 'amount' => 500, 'expense_date' => today()->toDateString(), 'payment_method' => 'bank',
    ])->assertRedirect(route('admin.expenses.index'));
    expect($expense->fresh()->amount)->toBe(500.0);

    $this->actingAs($admin, 'admin')->get(route('admin.expenses.index'))->assertOk()->assertSee('Packaging tape');
    $this->actingAs($admin, 'admin')->delete(route('admin.expenses.destroy', $expense))->assertRedirect();
    expect(Expense::count())->toBe(0);
});

it('rejects an expense with no amount', function () {
    $this->actingAs(reportAdmin(), 'admin')
        ->post(route('admin.expenses.store'), ['title' => 'Bad', 'expense_date' => today()->toDateString(), 'payment_method' => 'cash'])
        ->assertSessionHasErrors('amount');
});

it('shows a customer sales profile', function () {
    $user = \App\Models\User::forceCreate(['name' => 'Karim', 'email' => 'karim@example.com', 'phone' => '01711111111', 'password' => bcrypt('secret-pass-1')]);
    reportOrder(['user_id' => $user->id, 'phone' => '01711111111']);

    $this->actingAs(reportAdmin(), 'admin')
        ->get(route('admin.customers.show', $user))
        ->assertOk()
        ->assertSee('Karim')
        ->assertSee('1,060');
});
