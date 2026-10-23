<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Hash;

function accountProduct(): Product
{
    static $n = 0;
    $n++;

    return Product::create([
        'name' => "Account Item {$n}",
        'slug' => "account-item-{$n}",
        'price' => 500,
        'stock' => 10,
        'status' => 'active',
    ]);
}

function accountOrder(array $attributes = [], ?Product $product = null): Order
{
    static $n = 0;
    $n++;

    $order = Order::create($attributes + [
        'name' => 'Rahim Uddin',
        'phone' => '01711223344',
        'email' => 'rahim@example.com',
        'division' => 'Dhaka',
        'district' => 'Dhaka',
        'area' => 'Dhanmondi',
        'address' => 'House 12',
        'zone' => 'inside_dhaka',
        'zone_label' => 'Inside Dhaka',
        'subtotal' => 500,
        'delivery_fee' => 70,
        'total' => 570,
    ]);
    $order->update(['number' => 'NM-'.(20000 + $order->id)]);

    if ($product) {
        $order->items()->create([
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => 500,
            'quantity' => 1,
            'line_total' => 500,
        ]);
    }

    return $order->fresh();
}

it('keeps the customer pages behind the login', function () {
    foreach (['index', 'orders', 'profile', 'addresses', 'wishlist'] as $page) {
        $this->get(route('account.'.$page))->assertRedirect(route('login'));
    }
});

it('lists every order placed with the customer mobile number, signed in or not', function () {
    $user = User::factory()->create(['phone' => '01711223344']);
    $guestOrder = accountOrder();
    $linked = accountOrder(['user_id' => $user->id, 'phone' => '01955555555']);
    $other = accountOrder(['phone' => '01600000000']);

    $this->actingAs($user)
        ->get(route('account.orders'))
        ->assertOk()
        ->assertSee($guestOrder->number)
        ->assertSee($linked->number)
        ->assertDontSee($other->number);

    $this->get(route('account.orders.show', $other->number))->assertNotFound();
    $this->get(route('account.orders.show', $guestOrder->number))->assertOk()->assertSee('Rahim Uddin');
});

it('puts the items of an earlier order back in the cart', function () {
    $user = User::factory()->create(['phone' => '01711223344']);
    $product = accountProduct();
    $order = accountOrder([], $product);

    $this->actingAs($user)
        ->post(route('account.orders.reorder', $order->number))
        ->assertRedirect(route('cart.index'));

    $this->getJson(route('cart.summary'))->assertJsonPath('cart.count', 1);
});

it('updates the customer details and refuses a mobile number of another account', function () {
    $user = User::factory()->create(['phone' => '01711223344']);
    User::factory()->create(['phone' => '01811111111']);

    $this->actingAs($user)
        ->patch(route('account.profile.update'), ['name' => 'New Name', 'phone' => '01811111111', 'email' => 'new@example.com'])
        ->assertSessionHasErrors('phone');

    $this->patch(route('account.profile.update'), ['name' => 'New Name', 'phone' => '+8801922222222', 'email' => 'new@example.com'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('New Name')
        ->and($user->fresh()->phone)->toBe('01922222222')
        ->and($user->fresh()->email)->toBe('new@example.com');
});

it('changes the password only when the current one is right', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('account.password.update'), ['current_password' => 'nope', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
        ->assertSessionHasErrors('current_password', errorBag: 'password');

    $this->put(route('account.password.update'), ['current_password' => 'password', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
        ->assertSessionHasNoErrors();

    expect(Hash::check('new-password-1', $user->fresh()->password))->toBeTrue();
});

it('manages the address book and always keeps one default', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $details = ['label' => 'Home', 'name' => 'Rahim', 'phone' => '01711223344', 'district' => 'Dhaka', 'area' => 'Dhanmondi', 'address' => 'House 12'];

    $this->post(route('account.addresses.store'), $details)->assertRedirect(route('account.addresses'));
    $this->post(route('account.addresses.store'), ['label' => 'Office', 'address' => 'Level 4'] + $details)->assertSessionHasNoErrors();

    [$home, $office] = $user->addresses()->reorder('id')->get()->all();

    expect($home->is_default)->toBeTrue()->and($office->is_default)->toBeFalse();

    $this->patch(route('account.addresses.default', $office))->assertRedirect();
    expect($office->fresh()->is_default)->toBeTrue()->and($home->fresh()->is_default)->toBeFalse();

    $this->delete(route('account.addresses.destroy', $office))->assertRedirect();
    expect($home->fresh()->is_default)->toBeTrue();

    $this->post(route('account.addresses.store'), ['district' => 'Atlantis'] + $details)->assertSessionHasErrors('district');

    $stranger = Address::create($details + ['user_id' => User::factory()->create()->id]);
    $this->delete(route('account.addresses.destroy', $stranger))->assertNotFound();
});

it('saves and removes wishlist items for the signed-in customer only', function () {
    $user = User::factory()->create();
    $product = accountProduct();

    $this->postJson(route('account.wishlist.toggle'), ['product_id' => $product->id])->assertUnauthorized();

    $this->actingAs($user)
        ->postJson(route('account.wishlist.toggle'), ['product_id' => $product->id])
        ->assertOk()->assertJsonPath('wished', true)->assertJsonPath('count', 1);

    $this->get(route('account.wishlist'))->assertOk()->assertSee($product->name);
    $this->get($product->url)->assertOk()->assertSee('aria-pressed="true"', false);

    $this->postJson(route('account.wishlist.toggle'), ['product_id' => $product->id])
        ->assertOk()->assertJsonPath('wished', false)->assertJsonPath('count', 0);

    expect(Wishlist::count())->toBe(0);
    $this->postJson(route('account.wishlist.toggle'), ['product_id' => 99999])->assertUnprocessable();
});
