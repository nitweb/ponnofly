<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('serves an xml sitemap with the main store pages', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/xml');
    $response->assertSee('<urlset', false);
    $response->assertSee(url('/shop'), false);
});

it('lists only active products, categories and brands', function () {
    Product::create(['name' => 'Live Serum', 'slug' => 'live-serum', 'price' => 1000, 'stock' => 5, 'status' => 'active']);
    Product::create(['name' => 'Hidden Serum', 'slug' => 'hidden-serum', 'price' => 1000, 'stock' => 5, 'status' => 'draft']);
    Category::create(['name' => 'Beauty', 'slug' => 'beauty', 'is_active' => true]);
    Category::create(['name' => 'Secret', 'slug' => 'secret', 'is_active' => false]);
    Brand::create(['name' => 'Acme', 'slug' => 'acme', 'is_active' => true]);
    Brand::create(['name' => 'Gone', 'slug' => 'gone', 'is_active' => false]);

    $this->get('/sitemap.xml')
        ->assertSee('/product/live-serum', false)
        ->assertDontSee('/product/hidden-serum', false)
        ->assertSee('/category/beauty', false)
        ->assertDontSee('/category/secret', false)
        ->assertSee('/brand/acme', false)
        ->assertDontSee('/brand/gone', false);
});

it('builds sitemap urls from the canonical domain setting', function () {
    Setting::putMany(['canonical_domain' => 'https://ponnofly.com']);

    $this->get('/sitemap.xml')->assertSee('<loc>https://ponnofly.com/shop</loc>', false);
});
