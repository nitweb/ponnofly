<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function dedupeProduct(string $name, array $attributes = []): Product
{
    static $n = 0;
    $n++;

    return Product::create($attributes + [
        'name' => $name,
        'slug' => 'dedupe-item-'.$n,
        'sku' => 'DD-'.$n,
        'price' => 1000,
        'stock' => 5,
        'status' => 'active',
    ]);
}

function dedupeUser(): User
{
    static $n = 0;
    $n++;

    return User::factory()->create(['email' => "dedupe{$n}@example.com"]);
}

it('does nothing when there are no duplicates', function () {
    dedupeProduct('Rose Serum');
    dedupeProduct('Teddy Bear');

    $this->artisan('products:dedupe', ['--force' => true])->assertSuccessful();

    expect(Product::count())->toBe(2);
});

it('leaves everything untouched on a dry run', function () {
    dedupeProduct('Rose Serum');
    dedupeProduct('rose  serum!');

    $this->artisan('products:dedupe', ['--dry-run' => true])->assertSuccessful();

    expect(Product::count())->toBe(2);
});

it('removes duplicates by normalized name and keeps the oldest', function () {
    $first = dedupeProduct('Rose Serum');
    dedupeProduct('ROSE   serum');
    dedupeProduct('Rose-Serum');
    $other = dedupeProduct('Teddy Bear');

    $this->artisan('products:dedupe', ['--force' => true])->assertSuccessful();

    expect(Product::pluck('id')->all())->toBe([$first->id, $other->id]);
});

it('keeps the duplicate that has order history', function () {
    $older = dedupeProduct('Rose Serum');
    $sold = dedupeProduct('Rose Serum');

    $orderId = DB::table('orders')->insertGetId(orderRow());
    DB::table('order_items')->insert([
        'order_id' => $orderId, 'product_id' => $sold->id, 'name' => 'Rose Serum',
        'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->artisan('products:dedupe', ['--force' => true])->assertSuccessful();

    expect(Product::pluck('id')->all())->toBe([$sold->id])
        ->and(DB::table('order_items')->value('product_id'))->toBe($sold->id)
        ->and(Product::whereKey($older->id)->exists())->toBeFalse();
});

it('moves orders, wishlists, reviews and images onto the kept product without conflicts', function () {
    $keep = dedupeProduct('Rose Serum', ['sold_count' => 2]);
    $dup = dedupeProduct('Rose Serum', ['sold_count' => 3]);

    $a = dedupeUser();
    $b = dedupeUser();

    // $a wishlisted both (conflict -> one row survives); $b only the duplicate (row moves over)
    foreach ([[$a, $keep], [$a, $dup], [$b, $dup]] as [$user, $product]) {
        DB::table('wishlists')->insert(['user_id' => $user->id, 'product_id' => $product->id, 'created_at' => now(), 'updated_at' => now()]);
    }

    DB::table('reviews')->insert(['user_id' => $b->id, 'product_id' => $dup->id, 'rating' => 5, 'created_at' => now(), 'updated_at' => now()]);

    // only the duplicate has a gallery
    ProductImage::create(['product_id' => $dup->id, 'path' => 'products/rose.jpg', 'sort_order' => 0]);

    $this->artisan('products:dedupe', ['--force' => true])->assertSuccessful();

    expect(Product::pluck('id')->all())->toBe([$keep->id])
        ->and(DB::table('wishlists')->where('product_id', $keep->id)->count())->toBe(2)
        ->and(DB::table('wishlists')->count())->toBe(2)
        ->and(DB::table('reviews')->where('product_id', $keep->id)->count())->toBe(1)
        ->and(ProductImage::where('product_id', $keep->id)->count())->toBe(1)
        ->and($keep->fresh()->sold_count)->toBe(5);
});

function orderRow(): array
{
    static $n = 0;
    $n++;

    return [
        'number' => 'DD-'.$n,
        'name' => 'Buyer',
        'phone' => '01700000000',
        'email' => "buyer{$n}@example.com",
        'division' => 'Dhaka',
        'district' => 'Dhaka',
        'area' => 'Mirpur',
        'address' => 'Somewhere 1',
        'zone' => 'inside',
        'zone_label' => 'Inside Dhaka',
        'status' => 'pending',
        'subtotal' => 1000,
        'total' => 1000,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}
