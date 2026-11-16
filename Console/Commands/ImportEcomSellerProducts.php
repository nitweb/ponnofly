<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportEcomSellerProducts extends Command
{
    protected $signature = 'products:import-ecomseller
                            {--limit=50 : Max products to import (0 = all)}
                            {--page=1 : Start page}
                            {--dry-run : Only show what would be imported}
                            {--force : Update existing products by SKU}';

    protected $description = 'Import products (name, images, wholesale/sale price, description) from ecomsellerbd.com into this store';

    private string $base = 'https://ecomsellerbd.com';
    private string $api = 'https://ecomsellerbd.com/rest/v1';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $page = max(1, (int) $this->option('page'));
        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->info('Fetching categories from ecomsellerbd.com ...');
        $categoryMap = $this->syncCategories($dry);

        $this->info('Fetching products ...');
        $perPage = 50;
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $offset = ($page - 1) * $perPage;

        while (true) {
            if ($limit > 0 && $imported + $updated >= $limit) {
                break;
            }

            $url = $this->api . '/products?select=id,name,slug,sku,product_code,short_description,description,reseller_price,suggested_price,buying_price,category_id,is_active,is_featured,stock,weight_grams,meta_title,meta_description&is_active=eq.true&order=created_at.desc&limit=' . $perPage . '&offset=' . $offset;

            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'PonnoflyImporter/1.0'])
                ->get($url);

            if (! $response->successful()) {
                $this->error('API error: ' . $response->status() . ' ' . $response->body());
                return self::FAILURE;
            }

            $items = $response->json();
            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                if ($limit > 0 && $imported + $updated >= $limit) {
                    break 2;
                }

                $sku = $item['product_code'] ?? $item['sku'] ?? null;
                if (! $sku) {
                    $sku = 'ES-' . Str::upper(Str::random(8));
                }

                $existing = Product::where('sku', $sku)->first();
                if ($existing && ! $force) {
                    $skipped++;
                    continue;
                }

                $name = $item['name'] ?? 'Untitled';
                $slug = $item['slug'] ?? Str::slug($name);
                // Ensure unique slug
                $baseSlug = $slug;
                $i = 1;
                while (Product::where('slug', $slug)->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))->exists()) {
                    $slug = $baseSlug . '-' . $i++;
                }

                $cost = (float) ($item['reseller_price'] ?? $item['buying_price'] ?? 0);
                $price = (float) ($item['suggested_price'] ?? 0);
                if ($price <= 0 && $cost > 0) {
                    $price = round($cost * 2.5); // fallback margin
                }

                $categoryId = null;
                if (! empty($item['category_id']) && isset($categoryMap[$item['category_id']])) {
                    $categoryId = $categoryMap[$item['category_id']];
                }

                $data = [
                    'category_id' => $categoryId,
                    'name' => $name,
                    'slug' => $slug,
                    'short_description' => $item['short_description'] ?? null,
                    'description' => $item['description'] ?? null,
                    'price' => $price,
                    'compare_price' => null,
                    'cost_price' => $cost,
                    'sku' => $sku,
                    'stock' => (int) ($item['stock'] ?? 100),
                    'track_inventory' => true,
                    'status' => 'active',
                    'is_featured' => (bool) ($item['is_featured'] ?? false),
                    'is_new' => true,
                    'is_cod' => true,
                    'is_free_delivery' => false,
                    'is_returnable' => true,
                    'weight' => isset($item['weight_grams']) ? ((float) $item['weight_grams'] / 1000) : null,
                    'meta_title' => $item['meta_title'] ?? $name,
                    'meta_description' => $item['meta_description'] ?? null,
                ];

                if ($dry) {
                    $this->line(sprintf(
                        '[DRY] %s | cost ৳%s → sale ৳%s | SKU %s',
                        Str::limit($name, 50),
                        number_format($cost),
                        number_format($price),
                        $sku
                    ));
                    $imported++;
                    continue;
                }

                if ($existing) {
                    $existing->update($data);
                    $product = $existing;
                    $updated++;
                    // clear old images so we refresh
                    $product->images()->delete();
                } else {
                    $product = Product::create($data);
                    $imported++;
                }

                // Import images
                $this->importImages($product, $item['id']);

                $this->line(sprintf(
                    '✓ %s | ৳%s → ৳%s (profit ৳%s)',
                    Str::limit($name, 45),
                    number_format($cost),
                    number_format($price),
                    number_format($price - $cost)
                ));
            }

            $offset += $perPage;
            if (count($items) < $perPage) {
                break;
            }

            // Be nice to the API
            usleep(300000);
        }

        $this->newLine();
        $this->info("Done. Imported: {$imported}, Updated: {$updated}, Skipped: {$skipped}");
        if ($dry) {
            $this->warn('Dry-run only — no database changes.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, int> remote category UUID => local category id
     */
    private function syncCategories(bool $dry): array
    {
        $map = [];
        $response = Http::timeout(20)
            ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'PonnoflyImporter/1.0'])
            ->get($this->api . '/categories?select=id,name,slug,parent_id&order=name.asc');

        if (! $response->successful()) {
            $this->warn('Could not fetch categories, products will have no category.');
            return $map;
        }

        $remoteCats = $response->json();
        foreach ($remoteCats as $cat) {
            $name = $cat['name'] ?? null;
            if (! $name) {
                continue;
            }

            $slug = $cat['slug'] ?? Str::slug($name);

            if ($dry) {
                $map[$cat['id']] = 0;
                continue;
            }

            $local = Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'is_active' => true,
                    'show_on_home' => false,
                    'sort_order' => 100,
                ]
            );

            $map[$cat['id']] = $local->id;
        }

        $this->info('Categories ready: ' . count($map));
        return $map;
    }

    private function importImages(Product $product, string $remoteProductId): void
    {
        $response = Http::timeout(15)
            ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'PonnoflyImporter/1.0'])
            ->get(
                $this->api . '/product_images?select=url,sort_order,is_primary&product_id=eq.' . $remoteProductId . '&order=sort_order.asc'
            );

        if (! $response->successful()) {
            return;
        }

        $images = $response->json();
        $order = 0;
        foreach ($images as $img) {
            $path = $img['url'] ?? null;
            if (! $path) {
                continue;
            }

            // Store full remote URL so images work without downloading
            if (! str_starts_with($path, 'http')) {
                $path = $this->base . $path;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'sort_order' => $img['sort_order'] ?? $order,
            ]);
            $order++;
        }
    }
}
