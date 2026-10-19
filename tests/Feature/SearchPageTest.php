<?php

use App\Models\Brand;
use App\Models\Product;

function pageProduct(string $name, array $attributes = []): Product
{
    return Product::create($attributes + [
        'name' => $name,
        'slug' => str($name)->slug()->toString(),
        'price' => 500,
        'stock' => 10,
        'status' => 'active',
    ]);
}

it('shows the search results page without a second search bar', function () {
    pageProduct('Aloe Vera Gel');
    pageProduct('Cotton Panjabi');

    $this->get(route('search', ['q' => 'aloe']))
        ->assertOk()
        ->assertSee('Aloe Vera Gel')
        ->assertDontSee('Cotton Panjabi')
        ->assertDontSee('id="shop-q"', false);
});

it('sends an empty search to the shop', function () {
    $this->get(route('search'))->assertRedirect(route('shop'));
});

it('matches the shop search on product name only', function () {
    $brand = Brand::create(['name' => 'Omron', 'slug' => 'omron', 'is_active' => true]);
    pageProduct('Digital Thermometer', ['brand_id' => $brand->id]);
    pageProduct('Omron Scale');

    $html = $this->getJson(route('shop', ['q' => 'omron']))->assertOk()->json('html');

    expect($html)->toContain('Omron Scale')->not->toContain('Digital Thermometer');
});

it('returns the shop results fragment for ajax requests', function () {
    pageProduct('Aloe Vera Gel');

    $this->getJson(route('shop', ['q' => 'aloe']))
        ->assertOk()
        ->assertJsonStructure(['html'])
        ->assertJsonMissingPath('data');

    $this->get(route('shop', ['q' => 'aloe']))->assertOk()->assertSee('id="shop-results"', false);
});
