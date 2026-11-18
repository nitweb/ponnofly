<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Removes all products and related rows (images, variants, reviews, cart/wishlist lines, flash deal links).
 * Run: php artisan db:seed --class=ClearProductsSeeder
 */
class ClearProductsSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('flash_deal_products')) {
            FlashDealProduct::query()->delete();
        }
        if (Schema::hasTable('cart_items')) {
            CartItem::query()->delete();
        }
        if (Schema::hasTable('wishlists')) {
            Wishlist::query()->delete();
        }
        if (Schema::hasTable('reviews')) {
            Review::query()->delete();
        }
        if (Schema::hasTable('product_variants')) {
            ProductVariant::query()->delete();
        }
        if (Schema::hasTable('product_images')) {
            ProductImage::query()->delete();
        }

        // Order items keep historical product data via their own columns; do not cascade delete orders.
        Product::query()->delete();

        Schema::enableForeignKeyConstraints();

        $this->command?->info('All products and related catalog data removed.');
    }
}
