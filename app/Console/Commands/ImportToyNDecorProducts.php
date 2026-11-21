<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Import ALL products from toyndecor.com via public API (~216).
 *
 * php artisan products:import-toyndecor --limit=0 --force
 */
class ImportToyNDecorProducts extends Command
{
    protected $signature = 'products:import-toyndecor
                            {--limit=0 : Max products (0 = ALL)}
                            {--dry-run : Preview only}
                            {--force : Update existing by SKU}';

    protected $description = 'Import all products from toyndecor.com API (name, image, price, old price, category, brand)';

    private string $api = 'https://api.toyndecor.com';
    private string $listApi = 'https://toyndecor.com/api/products';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $supplierId = null;
        if (! $dry) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'ToyNDecor'],
                [
                    'address' => 'toyndecor.com',
                    'notes' => 'Products imported from toyndecor.com',
                    'is_active' => true,
                ]
            );
            $supplierId = $supplier->id;
            $this->info("Supplier: ToyNDecor (ID {$supplierId})");
        }

        $this->info('Fetching product list from toyndecor.com API ...');
        try {
            $items = $this->fetchAllProducts();
        } catch (\Throwable $e) {
            $this->error('API failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Found '.count($items).' products');
        if ($items === []) {
            $this->error('No products returned from API.');

            return self::FAILURE;
        }

        if ($limit > 0) {
            $items = array_slice($items, 0, $limit);
            $this->warn("Limited to {$limit} products");
        }

        $imported = $updated = $skipped = $failed = 0;

        foreach ($items as $i => $row) {
            $name = $this->localized($row['name'] ?? null) ?: 'Product';
            $sku = (string) ($row['sku'] ?? '');
            if ($sku === '') {
                $sku = 'TND-'.strtoupper(substr((string) ($row['id'] ?? $row['_id'] ?? md5($name)), 0, 12));
            }
            $sku = 'TND-'.$sku;

            $this->line(sprintf('[%d/%d] %s (%s)', $i + 1, count($items), Str::limit($name, 50), $sku));

            $existing = Product::where('sku', $sku)->first();
            if ($existing && ! $force) {
                $skipped++;
                $this->line('  skip (exists)');

                continue;
            }

            $basePrice = (float) ($row['basePrice'] ?? 0);
            $salePrice = (float) ($row['baseDiscountedPrice'] ?? 0);
            // Selling price = discounted if available, else base
            $price = $salePrice > 0 ? $salePrice : $basePrice;
            $compare = ($salePrice > 0 && $basePrice > $salePrice) ? $basePrice : null;
            if ($price <= 0) {
                $this->warn('  skip (no price)');
                $failed++;

                continue;
            }

            $slug = (string) ($row['slug'] ?? Str::slug($name));
            $catName = null;
            $catSlug = null;
            $cat = $row['categoryId'] ?? null;
            if (is_array($cat)) {
                $catName = $this->localized($cat['name'] ?? null);
                $catSlug = $cat['slug'] ?? null;
            }

            $images = [];
            if (! empty($row['thumbnailUrl'])) {
                $images[] = $row['thumbnailUrl'];
            } elseif (! empty($row['thumbnail'])) {
                $images[] = $this->absUrl($row['thumbnail']);
            }
            foreach ($row['galleryUrls'] ?? [] as $g) {
                if ($g) {
                    $images[] = $g;
                }
            }

            // Optional detail for description + extra gallery
            $description = null;
            $short = null;
            $brandName = null;
            $detail = $this->fetchDetail((string) ($row['id'] ?? $row['_id'] ?? ''));
            if ($detail) {
                $description = $this->localized($detail['description'] ?? null);
                if ($description && ! str_contains($description, '<')) {
                    $description = '<p>'.e($description).'</p>';
                }
                $short = $this->localized($detail['metaDescription'] ?? null)
                    ?: ($description ? Str::limit(trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 240) : null);

                foreach ($detail['gallery'] ?? [] as $g) {
                    $url = is_array($g) ? ($g['url'] ?? $g['path'] ?? null) : $g;
                    if ($url) {
                        $images[] = $this->absUrl((string) $url);
                    }
                }
                if (! empty($detail['galleryUrls']) && is_array($detail['galleryUrls'])) {
                    foreach ($detail['galleryUrls'] as $g) {
                        if ($g) {
                            $images[] = $this->absUrl((string) $g);
                        }
                    }
                }

                // Brand if API provides
                if (! empty($detail['brand']) && is_array($detail['brand'])) {
                    $brandName = $this->localized($detail['brand']['name'] ?? null) ?: ($detail['brand']['name'] ?? null);
                } elseif (! empty($detail['brandName'])) {
                    $brandName = is_array($detail['brandName']) ? $this->localized($detail['brandName']) : (string) $detail['brandName'];
                }
            }

            $images = array_values(array_unique(array_filter($images)));

            if ($dry) {
                $this->line(sprintf(
                    '  [DRY] ৳%s%s | cat: %s | imgs: %d',
                    number_format($price),
                    $compare ? ' / was ৳'.number_format($compare) : '',
                    $catName ?? '—',
                    count($images)
                ));
                $imported++;

                continue;
            }

            $categoryId = $this->resolveCategory($catName, $catSlug);
            $brandId = $this->resolveBrand($brandName);

            $uniqueSlug = $slug;
            $baseSlug = $uniqueSlug;
            $n = 1;
            while (Product::where('slug', $uniqueSlug)->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))->exists()) {
                $uniqueSlug = $baseSlug.'-'.$n++;
            }

            $payload = [
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'supplier_id' => $supplierId,
                'name' => Str::limit($name, 240, ''),
                'slug' => $uniqueSlug,
                'short_description' => $short ? Str::limit($short, 500, '') : null,
                'description' => $description,
                'price' => $price,
                'compare_price' => $compare,
                'sku' => $sku,
                'stock' => (int) ($row['totalStock'] ?? 50),
                'track_inventory' => true,
                'status' => (($row['status'] ?? 'ACTIVE') === 'ACTIVE') ? 'active' : 'draft',
                'is_featured' => (bool) ($row['isFeatured'] ?? false),
                'is_new' => (bool) ($row['isNewArrival'] ?? true),
                'is_cod' => true,
                'is_free_delivery' => false,
                'is_returnable' => true,
                'meta_title' => Str::limit($name, 240, ''),
            ];

            try {
                if ($existing) {
                    $existing->update($payload);
                    $product = $existing;
                    $product->images()->delete();
                    $updated++;
                } else {
                    $product = Product::create($payload);
                    $imported++;
                }

                foreach ($images as $order => $imgUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $imgUrl,
                        'sort_order' => $order,
                    ]);
                }

                $this->info(sprintf(
                    '  ✓ ৳%s%s | %d images',
                    number_format($price),
                    $compare ? ' / was ৳'.number_format($compare) : '',
                    count($images)
                ));
            } catch (\Throwable $e) {
                $this->error('  save failed: '.$e->getMessage());
                $failed++;
            }

            usleep(100000);
        }

        $this->newLine();
        $this->info("Done. Imported: {$imported}, Updated: {$updated}, Skipped: {$skipped}, Failed: {$failed}");

        return self::SUCCESS;
    }

    /** @return list<array<string, mixed>> */
    private function fetchAllProducts(): array
    {
        $all = [];
        $page = 1;
        $totalPages = 1;

        do {
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'application/json',
                ])
                ->get($this->listApi, [
                    'page' => $page,
                    'limit' => 48,
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status().' on page '.$page);
            }

            $json = $response->json();
            $batch = $json['data'] ?? [];
            if (! is_array($batch)) {
                break;
            }

            foreach ($batch as $item) {
                if (is_array($item)) {
                    $all[] = $item;
                }
            }

            $pag = $json['pagination'] ?? [];
            $totalPages = (int) ($pag['totalPages'] ?? 1);
            $this->line("  page {$page}/{$totalPages}: ".count($batch).' products (total '.count($all).')');
            $page++;
        } while ($page <= $totalPages);

        return $all;
    }

    /** @return array<string, mixed>|null */
    private function fetchDetail(string $id): ?array
    {
        if ($id === '') {
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0', 'Accept' => 'application/json'])
                ->get($this->api.'/api/products/'.$id);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json('data');

            return is_array($data) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function localized(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            return trim($value) !== '' ? trim($value) : null;
        }
        if (is_array($value)) {
            foreach (['en', 'bn', 'name'] as $k) {
                if (! empty($value[$k]) && is_string($value[$k])) {
                    return trim($value[$k]);
                }
            }
        }

        return null;
    }

    private function absUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim($this->api, '/').'/'.ltrim($path, '/');
    }

    private function resolveCategory(?string $name, ?string $slug): ?int
    {
        if (! $name && ! $slug) {
            return Category::firstOrCreate(
                ['slug' => 'toys'],
                ['name' => 'Toys', 'is_active' => true, 'show_on_home' => true, 'sort_order' => 25]
            )->id;
        }

        $slug = $slug ?: Str::slug((string) $name);

        $existing = Category::query()
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower((string) $name)])
            ->orWhereRaw('LOWER(slug) = ?', [mb_strtolower($slug)])
            ->first();
        if ($existing) {
            return $existing->id;
        }

        $compact = str_replace('-', '', mb_strtolower($slug));
        $existing = Category::query()->get()->first(function (Category $c) use ($compact, $name) {
            return str_replace('-', '', mb_strtolower($c->slug)) === $compact
                || ($name && strcasecmp($c->name, $name) === 0);
        });
        if ($existing) {
            return $existing->id;
        }

        return Category::create([
            'name' => Str::limit($name ?: Str::title(str_replace('-', ' ', $slug)), 120, ''),
            'slug' => Str::limit($slug, 120, ''),
            'is_active' => true,
            'show_on_home' => false,
            'sort_order' => 50,
        ])->id;
    }

    private function resolveBrand(?string $name): ?int
    {
        if (! $name) {
            return null;
        }
        $name = Str::limit(trim($name), 100, '');
        $slug = Str::slug($name);
        if ($slug === '') {
            return null;
        }

        $brand = Brand::query()
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
        if ($brand) {
            return $brand->id;
        }

        return Brand::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ])->id;
    }
}
