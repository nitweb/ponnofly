<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Products scraped from xaxisdigitalllc.com
 * Run: php artisan db:seed --class=XaxisProductsSeeder
 */
class XaxisProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'UGREEN 30W Robot USB-C Fast Charger',
                'sku' => 'XAXIS0260',
                'brand' => 'Ugreen',
                'category' => 'Electronics',
                'price' => 2990,
                'compare_price' => 3250,
                'short_description' => '30W USB-C fast charger with cute robot design. Compact, safe and perfect for desk or nightstand.',
                'description' => '<ul>
<li>30W output for fast charging</li>
<li>USB-C reversible connector</li>
<li>Robot-shaped compact design</li>
<li>Built-in over-current protection</li>
<li>Fits neatly on desk or nightstand</li>
</ul>
<p>UGREEN 30W Robot USB-C Fast Charger combines powerful charging with a fun robot look. Ideal for phones, earbuds and small gadgets.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1792147442/ecommerch/products/p-ce9bb93a3630b0f03977326979cf92d4.png',
                ],
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'name' => 'LDNIO 65W GaN Universal Travel Charger',
                'sku' => 'XAXIS0183',
                'brand' => 'LDNIO',
                'category' => 'Electronics',
                'price' => 2990,
                'compare_price' => 3550,
                'short_description' => '65W GaN travel charger with foldable plug, USB-C + USB-A ports. Powers laptop and phone together.',
                'description' => '<ul>
<li>Compact size fits in any travel bag</li>
<li>GaN tech keeps charger cool during fast charging</li>
<li>65W output powers most laptops and phones</li>
<li>Two ports allow simultaneous charging of devices</li>
<li>Foldable plug prevents damage and saves space</li>
<li>Includes USB-C and USB-A ports</li>
<li>Works with Power Delivery devices</li>
</ul>
<p>Perfect travel companion – charge your laptop and phone at the same time with this powerful yet pocket-sized GaN charger.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790620127/ecommerch/products/pywsrhto46xgryafbf66.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790620126/ecommerch/products/ov5guz6banru9ie37snh.jpg',
                ],
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'name' => 'Mini Handheld Fan A.Life',
                'sku' => 'XAXIS0081',
                'brand' => 'A.Life',
                'category' => 'Home Essentials',
                'price' => 1650,
                'compare_price' => 1990,
                'short_description' => 'Compact rechargeable handheld fan. Lightweight, quiet and perfect for travel, office or outdoor use.',
                'description' => '<ul>
<li>Compact handheld design fits easily in small bags or pockets</li>
<li>Immediate cooling during hot weather and outdoor activities</li>
<li>Lightweight build – comfortable for long use</li>
<li>Simple operation suitable for all ages</li>
<li>Targeted airflow for face, neck or head</li>
</ul>
<p>Stay cool anywhere with this mini handheld fan from A.Life. Ideal for commuting, studying or outdoor adventures.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790177015/ecommerch/products/xbrmqxygopek5qtqv3h0.png',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1789820648/ecommerch/products/e3jdy6n2ai2nqyutedmd.jpg',
                ],
                'is_featured' => false,
                'is_new' => true,
            ],
            [
                'name' => 'S8 4K Digital Camera 48MP | 16X Zoom Vlogging Camera',
                'sku' => 'XAXIS0147',
                'brand' => null,
                'category' => 'Electronics',
                'price' => 5500,
                'compare_price' => 6500,
                'short_description' => '48MP sensor, 16X optical zoom, 4K video recording. Perfect beginner-friendly vlogging camera.',
                'description' => '<ul>
<li>48MP sensor delivers detailed still images</li>
<li>16X optical zoom for close-up shots</li>
<li>Records smooth 4K video for vlogs</li>
<li>User-friendly controls for beginners</li>
<li>Compact size fits in a bag for travel</li>
</ul>
<p>Capture sharp photos and cinematic 4K videos with the S8 digital camera. Great for travel, content creation and everyday memories.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790586061/ecommerch/products/r1iynrasi1gevlakbzgi.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790586064/ecommerch/products/swolzfx9bj2dtabfsyhv.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790586065/ecommerch/products/utwemhmrsask0jpirwbp.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790586084/ecommerch/products/n76rvgfammkisufqdmjp.jpg',
                ],
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'name' => 'M26 50MP Digital Camera 16X Zoom | 2.4" Screen',
                'sku' => 'XAXIS0153',
                'brand' => null,
                'category' => 'Electronics',
                'price' => 2650,
                'compare_price' => 3550,
                'short_description' => '50MP digital camera with 16X optical zoom and 2.4" LCD screen. Compact and travel-friendly.',
                'description' => '<ul>
<li>50MP sensor for high-detail photos</li>
<li>16× optical zoom for distant subjects</li>
<li>2.4" LCD screen for clear framing</li>
<li>Compact size fits travel bags</li>
<li>Rechargeable battery lasts all day</li>
</ul>
<p>Affordable yet powerful digital camera ideal for beginners, travel photography and everyday use.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790590303/ecommerch/products/lfqaybmsetgyxlc1x0nu.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790590263/ecommerch/products/dnf7onb8rdxthyasekz4.png',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790590088/ecommerch/products/gfcozizbhf2169orixac.jpg',
                ],
                'is_featured' => false,
                'is_new' => true,
            ],
            [
                'name' => 'LK-007 4K Digital Camera with Flip Screen & 48MP',
                'sku' => 'XAXIS0142',
                'brand' => null,
                'category' => 'Electronics',
                'price' => 4500,
                'compare_price' => 5500,
                'short_description' => '48MP + 4K video camera with flip screen for easy selfies and vlogging.',
                'description' => '<ul>
<li>48 MP sensor delivers detailed photos</li>
<li>4K video captures smooth footage</li>
<li>Flip screen enables easy selfie framing</li>
<li>Compact design fits in small bags</li>
<li>Fast autofocus locks subjects quickly</li>
</ul>
<p>Great for vloggers and travelers who want both high-quality stills and 4K video with a convenient flip screen.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790580765/ecommerch/products/ehejhu5kr8ggxsf9n5di.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790583128/ecommerch/products/j5gekxkcav38nwuqlilj.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790583138/ecommerch/products/hcgx896dobkl0ehtpkxd.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790583145/ecommerch/products/qlc8m82w2tinppdlpsz7.jpg',
                ],
                'is_featured' => true,
                'is_new' => true,
            ],
            [
                'name' => '360 Rechargeable Lamp',
                'sku' => 'XAXIS0080',
                'brand' => null,
                'category' => 'Home Essentials',
                'price' => 1350,
                'compare_price' => 1550,
                'short_description' => 'Portable rechargeable LED lamp with flexible lighting angles. Perfect for power cuts and study.',
                'description' => '<ul>
<li>Portable design – carry light to any room during power cuts</li>
<li>Rechargeable battery – no constant mains connection needed</li>
<li>Flexible illumination from any angle</li>
<li>Simple operation for all family members</li>
<li>Stable base prevents accidental tipping</li>
</ul>
<p>Reliable cordless lamp for study, work or emergency lighting at home.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1789819632/ecommerch/products/s81hju4xcpak7odlrdmv.jpg',
                ],
                'is_featured' => false,
                'is_new' => true,
            ],
            [
                'name' => 'PU Leather Magnetic Card Holder Wallet Case for iPhone',
                'sku' => 'XAXIS0208',
                'brand' => null,
                'category' => 'Mobile Accessories',
                'price' => 750,
                'compare_price' => 1250,
                'short_description' => 'Slim PU leather magnetic card holder wallet case. Holds up to 3 cards securely.',
                'description' => '<ul>
<li>Holds up to three cards securely</li>
<li>Magnetic closure keeps cards in place</li>
<li>Slim profile fits easily in pockets</li>
<li>PU leather provides soft, durable feel</li>
<li>Protects back and sides while allowing port access</li>
</ul>
<p>Stylish and practical magnetic wallet case for iPhone – carry your essential cards without bulk.</p>',
                'images' => [
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790711186/ecommerch/products/wtg40lgu4tdr2f9gcyhf.jpg',
                    'https://res.cloudinary.com/ywvdasn7/image/upload/f_auto,q_auto:eco,c_limit,w_1200/v1790711188/ecommerch/products/vy1qwpvbghrnokhspgf2.jpg',
                ],
                'is_featured' => false,
                'is_new' => true,
            ],
        ];

        foreach ($products as $item) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($item['category'])],
                [
                    'name' => $item['category'],
                    'is_active' => true,
                    'show_on_home' => true,
                    'sort_order' => 10,
                ]
            );

            $brandId = null;
            if (! empty($item['brand'])) {
                $brand = Brand::firstOrCreate(
                    ['slug' => Str::slug($item['brand'])],
                    [
                        'name' => $item['brand'],
                        'is_active' => true,
                    ]
                );
                $brandId = $brand->id;
            }

            $slug = Str::slug($item['name']);
            $baseSlug = $slug;
            $i = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            $product = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brandId,
                    'name' => $item['name'],
                    'slug' => $slug,
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'compare_price' => $item['compare_price'],
                    'cost_price' => null,
                    'stock' => 50,
                    'track_inventory' => true,
                    'status' => 'active',
                    'is_featured' => $item['is_featured'] ?? false,
                    'is_new' => $item['is_new'] ?? true,
                    'is_cod' => true,
                    'is_free_delivery' => false,
                    'is_returnable' => true,
                    'meta_title' => $item['name'],
                ]
            );

            // Refresh images
            $product->images()->delete();
            foreach ($item['images'] as $order => $url) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $url,
                    'sort_order' => $order,
                ]);
            }

            $this->command?->info("✓ {$item['name']} (SKU: {$item['sku']}) — ৳{$item['price']}");
        }

        $this->command?->info('Done. ' . count($products) . ' products from xaxisdigitalllc.com imported.');
    }
}
