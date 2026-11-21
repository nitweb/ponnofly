<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the categories that used to be hard-coded in the storefront menu.
     * Run with: php artisan db:seed --class=CategorySeeder
     */
    public function run(): void
    {
        $tree = [
            ['name' => 'Medical', 'home' => true],
            [
                'name' => 'Fashion',
                'home' => true,
                'promos' => [
                    ['badge' => 'New season', 'title' => 'Eid-ready outfits, up to 40% off', 'cta' => 'Shop fashion', 'link' => '', 'color' => 'navy'],
                    ['badge' => 'Trending', 'title' => 'Sneakers under ৳2,500', 'cta' => 'See the picks', 'link' => '', 'color' => 'yellow'],
                ],
                'children' => [
                    ['name' => 'Women', 'children' => [
                        ['name' => 'Sarees & Salwar', 'children' => [['name' => 'Silk Sarees'], ['name' => 'Cotton Sarees']]],
                        ['name' => 'Dresses & Tops'], ['name' => 'Kurtis'], ['name' => 'Handbags'], ['name' => 'Jewellery'],
                    ]],
                    ['name' => 'Men', 'children' => [
                        ['name' => 'Panjabi'], ['name' => 'Shirts & T-shirts'], ['name' => 'Jeans & Pants'], ['name' => 'Watches'], ['name' => 'Wallets'],
                    ]],
                    ['name' => 'Footwear', 'children' => [
                        ['name' => 'Sneakers'], ['name' => 'Sandals'], ['name' => 'Formal shoes'], ['name' => "Kids' shoes"],
                    ]],
                ],
            ],
            [
                'name' => 'Electronics',
                'home' => true,
                'promos' => [
                    ['badge' => 'Official warranty', 'title' => 'New phones, 0% EMI up to 12 months', 'cta' => 'Browse phones', 'link' => '', 'color' => 'navy'],
                    ['badge' => 'Hot deal', 'title' => 'Earbuds from ৳990', 'cta' => 'Grab a pair', 'link' => '', 'color' => 'yellow'],
                ],
                'children' => [
                    ['name' => 'Phones', 'children' => [['name' => 'Smartphones'], ['name' => 'Tablets'], ['name' => 'Cases & covers'], ['name' => 'Power banks']]],
                    ['name' => 'Computing', 'children' => [['name' => 'Laptops'], ['name' => 'Monitors'], ['name' => 'Keyboards & mice'], ['name' => 'Routers']]],
                    ['name' => 'Audio', 'children' => [['name' => 'Earbuds'], ['name' => 'Headphones'], ['name' => 'Bluetooth speakers'], ['name' => 'Smartwatches']]],
                ],
            ],
            [
                'name' => 'Home and Living',
                'home' => true,
                'promos' => [
                    ['badge' => 'Free assembly', 'title' => 'Refresh your room for less', 'cta' => 'Shop home', 'link' => '', 'color' => 'yellow'],
                ],
                'children' => [
                    ['name' => 'Furniture', 'children' => [['name' => 'Beds & mattresses'], ['name' => 'Sofas & chairs'], ['name' => 'Desks & storage'], ['name' => 'Shelves']]],
                    ['name' => 'Kitchen', 'children' => [['name' => 'Cookware'], ['name' => 'Appliances'], ['name' => 'Dinnerware'], ['name' => 'Storage boxes']]],
                    ['name' => 'Decor', 'children' => [['name' => 'Wall art'], ['name' => 'Lighting'], ['name' => 'Curtains & rugs'], ['name' => 'Plants & planters']]],
                ],
            ],
            [
                'name' => 'Beauty',
                'home' => true,
                'promos' => [
                    ['badge' => '100% authentic', 'title' => 'Skincare sets, buy 2 get 1', 'cta' => 'Shop beauty', 'link' => '', 'color' => 'navy'],
                ],
                'children' => [
                    ['name' => 'Skincare', 'children' => [['name' => 'Cleansers'], ['name' => 'Moisturizers'], ['name' => 'Sunscreen'], ['name' => 'Serums']]],
                    ['name' => 'Makeup', 'children' => [['name' => 'Lips'], ['name' => 'Eyes'], ['name' => 'Face'], ['name' => 'Nails']]],
                    ['name' => 'Hair & body', 'children' => [['name' => 'Shampoo & oil'], ['name' => 'Perfume & attar'], ['name' => 'Grooming for men'], ['name' => 'Body care']]],
                ],
            ],
            [
                'name' => 'Grocery',
                'promos' => [
                    ['badge' => 'Same-day', 'title' => 'Order by 2 PM, get it today', 'cta' => 'Start shopping', 'link' => '', 'color' => 'yellow'],
                ],
                'children' => [
                    ['name' => 'Fresh', 'children' => [['name' => 'Fruits'], ['name' => 'Vegetables'], ['name' => 'Fish & meat'], ['name' => 'Eggs & dairy']]],
                    ['name' => 'Pantry', 'children' => [['name' => 'Rice, dal & oil'], ['name' => 'Spices'], ['name' => 'Snacks'], ['name' => 'Tea & coffee']]],
                    ['name' => 'Household', 'children' => [['name' => 'Cleaning'], ['name' => 'Tissue & paper'], ['name' => 'Baby care'], ['name' => 'Pet supplies']]],
                ],
            ],
            ['name' => 'Sports'],
        ];

        $this->createLevel($tree, null);
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     */
    private function createLevel(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $index => $node) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($node['name'])],
                [
                    'parent_id' => $parentId,
                    'name' => $node['name'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'show_on_home' => $node['home'] ?? false,
                    'show_in_menu' => $parentId === null && $index < Category::NAV_LIMIT,
                    'promos' => $node['promos'] ?? null,
                ],
            );

            if (isset($node['promos'])) {
                $category->update(['promos' => $node['promos']]);
            }

            $this->createLevel($node['children'] ?? [], $category->id);
        }
    }
}
