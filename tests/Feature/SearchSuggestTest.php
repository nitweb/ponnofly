<?php

use App\Models\Brand;
use App\Models\Product;

function searchProduct(string $name, array $attributes = []): Product
{
    return Product::create($attributes + [
        'name' => $name,
        'slug' => str($name)->slug()->toString(),
        'price' => 1000,
        'stock' => 10,
        'status' => 'active',
    ]);
}

it('returns nothing for terms shorter than two characters', function () {
    searchProduct('Test Serum');

    $this->getJson(route('search.suggest', ['q' => 'a']))
        ->assertOk()
        ->assertJson(['total' => 0, 'products' => []]);
});

it('suggests active products matching name or brand', function () {
    $brand = Brand::create(['name' => 'Omron', 'slug' => 'omron', 'is_active' => true]);
    searchProduct('Digital Thermometer', ['brand_id' => $brand->id]);
    searchProduct('Face Gel');

    $this->getJson(route('search.suggest', ['q' => 'omron']))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('products.0.name', 'Digital Thermometer')
        ->assertJsonPath('products.0.price', '৳1,000')
        ->assertJsonPath('products.0.brand', 'Omron');
});

it('requires every word to match and hides inactive products', function () {
    searchProduct('Vitamin C Serum');
    searchProduct('Vitamin C Sunscreen', ['status' => 'draft']);

    $this->getJson(route('search.suggest', ['q' => 'vitamin serum']))->assertJsonPath('total', 1);
    $this->getJson(route('search.suggest', ['q' => 'vitamin sunscreen']))->assertJsonPath('total', 0);
});

it('caps suggestions but reports the full match count', function () {
    foreach (range(1, 10) as $i) {
        searchProduct("Cotton Shirt {$i}");
    }

    $this->getJson(route('search.suggest', ['q' => 'cotton']))
        ->assertJsonPath('total', 10)
        ->assertJsonCount(8, 'products');
});
