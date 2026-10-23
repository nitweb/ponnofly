<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    FlashDeal::flushLiveCache();
});

function cartProduct(array $attributes = []): Product
{
    static $n = 0;
    $n++;

    return Product::create($attributes + [
        'name' => "Cart Item {$n}",
        'slug' => "cart-item-{$n}",
        'price' => 1000,
        'compare_price' => 1500,
        'stock' => 10,
        'status' => 'active',
    ]);
}

/** Money comes back as floats, so compare numbers loosely. */
function near(float|int $expected): Closure
{
    return fn ($actual) => abs($actual - $expected) < 0.001;
}

function addToCart($test, Product $product, int $quantity = 1, ?int $variantId = null)
{
    return $test->postJson(route('cart.items.store'), array_filter([
        'product_id' => $product->id,
        'variant_id' => $variantId,
        'quantity' => $quantity,
    ]));
}

it('adds a product to a guest cart and prices it on the server', function () {
    $product = cartProduct();

    addToCart($this, $product, 2)
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('cart.count', 2)
        ->assertJsonPath('cart.subtotal', near(2000))
        ->assertJsonPath('cart.savings', near(1000))
        ->assertJsonPath('cart.items.0.unit_price', near(1000));

    $this->getJson(route('cart.summary'))->assertJsonPath('cart.count', 2);
});

it('merges the same product into one line and never goes above the stock', function () {
    $product = cartProduct(['stock' => 3]);

    addToCart($this, $product, 2)->assertOk();
    addToCart($this, $product, 2)->assertOk()->assertJsonPath('cart.items.0.quantity', 3);
    addToCart($this, $product, 1)->assertStatus(422)->assertJsonPath('ok', false);
});

it('caps a line at ten pieces and rejects tampered quantities', function () {
    $product = cartProduct(['stock' => 50]);

    addToCart($this, $product, 10)->assertOk();
    addToCart($this, $product, 1)->assertStatus(422);
    $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 999])->assertStatus(422);
});

it('refuses out of stock, inactive and unknown products', function () {
    addToCart($this, cartProduct(['stock' => 0]))->assertStatus(422);
    addToCart($this, cartProduct(['status' => 'draft']))->assertStatus(422);
    $this->postJson(route('cart.items.store'), ['product_id' => 999999])->assertStatus(422);

    $this->getJson(route('cart.summary'))->assertJsonPath('cart.count', 0);
});

it('does not sell when the store is closed', function () {
    Setting::putMany(['store_open' => '0']);

    addToCart($this, cartProduct())->assertStatus(422);
});

it('needs a valid variant and uses the variant price and stock', function () {
    $product = cartProduct();
    $small = $product->variants()->create(['size' => 'S', 'price' => 800, 'stock' => 2]);
    $sold = $product->variants()->create(['size' => 'M', 'price' => 900, 'stock' => 0]);
    $other = cartProduct()->variants()->create(['size' => 'L', 'stock' => 5]);

    addToCart($this, $product)->assertStatus(422);
    addToCart($this, $product, 1, $other->id)->assertStatus(422);
    addToCart($this, $product, 1, $sold->id)->assertStatus(422);

    addToCart($this, $product, 5, $small->id)
        ->assertOk()
        ->assertJsonPath('cart.items.0.quantity', 2)
        ->assertJsonPath('cart.items.0.unit_price', near(800))
        ->assertJsonPath('cart.items.0.variant', 'Size: S');
});

it('charges the live flash deal price and limits the quantity to the deal stock', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-15 12:00:00', FlashDeal::TIMEZONE));

    $product = cartProduct();
    $deal = FlashDeal::create([
        'title' => 'Flash', 'starts_at' => '2026-10-10 00:00:00', 'ends_at' => '2026-10-10 23:59:00', 'repeat_daily' => true,
    ]);
    $deal->items()->create(['product_id' => $product->id, 'discount_percent' => 20, 'deal_stock' => 2, 'sort_order' => 0]);

    addToCart($this, $product, 4)
        ->assertOk()
        ->assertJsonPath('cart.items.0.unit_price', near(800))
        ->assertJsonPath('cart.items.0.quantity', 2)
        ->assertJsonPath('cart.subtotal', near(1600));

    $deal->update(['is_active' => false]);

    $this->getJson(route('cart.summary'))->assertJsonPath('cart.items.0.unit_price', near(1000));
});

it('updates and removes lines', function () {
    $product = cartProduct();
    addToCart($this, $product)->assertOk();
    $key = $product->id.'-0';

    $this->patchJson(route('cart.items.update', $key), ['quantity' => 4])->assertOk()->assertJsonPath('cart.count', 4);
    $this->patchJson(route('cart.items.update', $key), ['quantity' => 0])->assertStatus(422);
    $this->deleteJson(route('cart.items.destroy', $key))->assertOk()->assertJsonPath('cart.count', 0);
    $this->patchJson(route('cart.items.update', $key), ['quantity' => 1])->assertStatus(422);
});

it('drops items that became unavailable and lowers quantities to the stock', function () {
    $gone = cartProduct();
    $low = cartProduct();
    addToCart($this, $gone)->assertOk();
    addToCart($this, $low, 5)->assertOk();

    $gone->update(['status' => 'draft']);
    $low->update(['stock' => 2]);

    $this->getJson(route('cart.summary'))
        ->assertJsonCount(1, 'cart.items')
        ->assertJsonPath('cart.items.0.quantity', 2)
        ->assertJsonCount(2, 'cart.notices');

    $this->getJson(route('cart.summary'))->assertJsonCount(0, 'cart.notices');
});

it('applies a percentage coupon with its cap and minimum order', function () {
    Coupon::create(['code' => 'TEN', 'type' => 'percentage', 'value' => 10, 'max_discount' => 150, 'min_order' => 1500]);
    $product = cartProduct();

    addToCart($this, $product)->assertOk();
    $this->postJson(route('cart.coupon.apply'), ['code' => 'ten'])->assertStatus(422)->assertJsonPath('ok', false);

    addToCart($this, $product, 2)->assertOk();
    $this->postJson(route('cart.coupon.apply'), ['code' => 'ten'])
        ->assertOk()
        ->assertJsonPath('cart.coupon.code', 'TEN')
        ->assertJsonPath('cart.discount', near(150))
        ->assertJsonPath('cart.total', near(3000 - 150 + 70));

    $this->deleteJson(route('cart.coupon.remove'))->assertOk()->assertJsonPath('cart.discount', near(0));
    $this->postJson(route('cart.coupon.apply'), ['code' => 'NOPE'])->assertStatus(422);
});

it('removes a coupon that stops being valid', function () {
    $coupon = Coupon::create(['code' => 'SAVE', 'type' => 'fixed', 'value' => 100]);
    addToCart($this, cartProduct())->assertOk();
    $this->postJson(route('cart.coupon.apply'), ['code' => 'SAVE'])->assertOk()->assertJsonPath('cart.discount', near(100));

    $coupon->update(['expires_at' => '2020-01-01']);

    $this->getJson(route('cart.summary'))
        ->assertJsonPath('cart.coupon', null)
        ->assertJsonPath('cart.discount', near(0))
        ->assertJsonCount(1, 'cart.notices');
});

it('limits a coupon to its categories and products', function () {
    $shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
    $inScope = cartProduct(['category_id' => $shoes->id]);
    $outOfScope = cartProduct();

    $coupon = Coupon::create(['code' => 'SHOES', 'type' => 'percentage', 'value' => 50, 'applies_to' => 'categories']);
    $coupon->categories()->attach($shoes->id);

    addToCart($this, $outOfScope)->assertOk();
    $this->postJson(route('cart.coupon.apply'), ['code' => 'SHOES'])->assertStatus(422);

    addToCart($this, $inScope)->assertOk();
    $this->postJson(route('cart.coupon.apply'), ['code' => 'SHOES'])->assertOk()->assertJsonPath('cart.discount', near(500));
});

it('stops a coupon that is used up or already used by the customer', function () {
    $user = User::factory()->create();
    $coupon = Coupon::create(['code' => 'ONCE', 'type' => 'fixed', 'value' => 50, 'per_customer_limit' => 1]);
    $product = cartProduct();

    $this->actingAs($user);
    addToCart($this, $product)->assertOk();
    $this->postJson(route('cart.coupon.apply'), ['code' => 'ONCE'])->assertOk();

    $coupon->redeem($user->id, 50);

    $this->getJson(route('cart.summary'))->assertJsonPath('cart.coupon', null);
    $this->postJson(route('cart.coupon.apply'), ['code' => 'ONCE'])->assertStatus(422);

    $coupon->update(['per_customer_limit' => null, 'usage_limit' => 1]);
    $this->postJson(route('cart.coupon.apply'), ['code' => 'ONCE'])->assertStatus(422);
});

it('prices delivery from the settings and the chosen zone', function () {
    Setting::putMany(['inside_dhaka_charge' => '60', 'outside_dhaka_charge' => '120', 'free_delivery_above' => '5000']);
    $product = cartProduct();

    addToCart($this, $product)->assertOk()->assertJsonPath('cart.shipping.fee', near(60));
    $this->putJson(route('cart.zone'), ['zone' => 'outside_dhaka'])->assertOk()->assertJsonPath('cart.shipping.fee', near(120));
    $this->putJson(route('cart.zone'), ['zone' => 'mars'])->assertStatus(422);

    addToCart($this, $product, 5)->assertOk()
        ->assertJsonPath('cart.shipping.free', true)
        ->assertJsonPath('cart.total', near(6000));
});

it('gives free delivery for a free-delivery coupon and for free-delivery products', function () {
    Coupon::create(['code' => 'FREE', 'type' => 'free_delivery']);
    $product = cartProduct();

    addToCart($this, $product)->assertOk()->assertJsonPath('cart.shipping.free', false);
    $this->postJson(route('cart.coupon.apply'), ['code' => 'FREE'])
        ->assertOk()
        ->assertJsonPath('cart.shipping.free', true)
        ->assertJsonPath('cart.total', near(1000));

    $this->deleteJson(route('cart.clear'))->assertOk();
    addToCart($this, cartProduct(['is_free_delivery' => true]))->assertOk()->assertJsonPath('cart.shipping.fee', near(0));
});

it('flags a cart below the minimum order', function () {
    Setting::putMany(['min_order' => '2000']);

    addToCart($this, cartProduct())->assertOk()
        ->assertJsonPath('cart.meets_min_order', false)
        ->assertJsonPath('cart.can_checkout', false);
});

it('keeps a signed-in customer cart in the database', function () {
    $user = User::factory()->create();
    $product = cartProduct();

    $this->actingAs($user);
    addToCart($this, $product, 2)->assertOk();

    $cart = Cart::where('user_id', $user->id)->first();
    expect($cart->items)->toHaveCount(1)
        ->and($cart->items->first()->quantity)->toBe(2);

    $this->getJson(route('cart.summary'))->assertJsonPath('cart.count', 2);
});

it('merges the guest cart into the customer cart on login', function () {
    $user = User::factory()->create();
    $shared = cartProduct();
    $guestOnly = cartProduct();

    $cart = Cart::create(['user_id' => $user->id]);
    $cart->items()->create(['product_id' => $shared->id, 'variant_id' => 0, 'quantity' => 2]);

    addToCart($this, $shared, 3)->assertOk();
    addToCart($this, $guestOnly)->assertOk();

    $this->post('/login', ['phone' => $user->phone, 'password' => 'password']);

    $this->assertAuthenticated();
    expect($cart->items()->where('product_id', $shared->id)->first()->quantity)->toBe(5)
        ->and($cart->items()->where('product_id', $guestOnly->id)->exists())->toBeTrue()
        ->and(session('cart'))->toBeNull();

    $this->getJson(route('cart.summary'))->assertJsonPath('cart.count', 6);
});

it('renders the cart page and the drawer with the live cart', function () {
    $product = cartProduct();
    addToCart($this, $product)->assertOk();

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Order summary')
        ->assertSee('Inside Dhaka');

    $this->get('/')->assertOk()->assertSee('data-cart-badge', false);
});
