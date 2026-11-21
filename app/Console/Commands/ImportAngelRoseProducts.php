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

class ImportAngelRoseProducts extends Command
{
    protected $signature = 'products:import-angelrose
                            {--limit=0 : Max products to import (0 = all)}
                            {--dry-run : Only show what would be imported}
                            {--force : Update existing products by SKU}';

    protected $description = 'Import all products from angelrosebeautycarebd.com (category, brand, price, old price, images)';

    private string $base = 'https://angelrosebeautycarebd.com';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $supplierId = null;
        if (! $dry) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'AngelRose'],
                [
                    'address' => 'angelrosebeautycarebd.com',
                    'notes' => 'Products imported from Angel Rose Beauty Care BD.',
                    'is_active' => true,
                ]
            );
            $supplierId = $supplier->id;
            $this->info("Supplier: AngelRose (ID {$supplierId})");
        }

        $this->info('Collecting products from shop pages ...');
        $listed = $this->collectFromShop();
        $this->info('Found '.count($listed).' products');

        if ($limit > 0) {
            $listed = array_slice($listed, 0, $limit);
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($listed as $i => $row) {
            $url = $row['url'];
            $this->line(sprintf('[%d/%d] %s', $i + 1, count($listed), $url));

            try {
                $detail = $this->scrapeProduct($url);
            } catch (\Throwable $e) {
                $this->error('  scrape failed: '.$e->getMessage());
                $failed++;
                usleep(200000);

                continue;
            }

            // Listing data is more reliable for name/price; detail fills images/desc/category
            $name = $this->cleanName($detail['name'] ?? null, $row['name'] ?? null, $row['slug'] ?? null);
            $price = ($detail['price'] ?? 0) > 0 ? $detail['price'] : (float) ($row['price'] ?? 0);
            $compare = $detail['compare_price'] ?? $row['compare_price'] ?? null;
            if ($compare !== null && $price > 0 && $compare <= $price) {
                $compare = null;
            }

            if ($name === '' || $price <= 0) {
                $this->warn('  no usable name/price');
                $failed++;

                continue;
            }

            $slug = $row['slug'] ?: ($detail['slug'] ?? Str::slug($name));
            $sku = 'AR-'.strtoupper(Str::slug($slug, ''));
            if (strlen($sku) > 60) {
                $sku = 'AR-'.substr(md5($slug), 0, 12);
            }

            $existing = Product::where('sku', $sku)->first();
            if ($existing && ! $force) {
                $skipped++;
                $this->line('  skip (exists)');

                continue;
            }

            $brandName = $this->cleanBrand($detail['brand_name'] ?? null, $name);
            $categoryName = $detail['category_name'] ?? null;
            $categorySlug = $detail['category_slug'] ?? null;
            $images = $detail['images'] ?? [];
            $short = $detail['short_description'] ?? null;
            $description = $detail['description'] ?? null;

            if ($dry) {
                $this->line(sprintf(
                    '  [DRY] %s | ৳%s (was ৳%s) | cat: %s | brand: %s | imgs: %d',
                    Str::limit($name, 45),
                    number_format($price),
                    $compare ? number_format($compare) : '—',
                    $categoryName ?? '—',
                    $brandName ?? '—',
                    count($images)
                ));
                $imported++;

                continue;
            }

            $categoryId = $this->resolveCategory($categoryName, $categorySlug);
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
                'stock' => 50,
                'track_inventory' => true,
                'status' => 'active',
                'is_featured' => false,
                'is_new' => true,
                'is_cod' => true,
                'is_free_delivery' => false,
                'is_returnable' => true,
                'meta_title' => Str::limit($name, 240, ''),
            ];

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
                '  ✓ %s — ৳%s%s',
                Str::limit($name, 50),
                number_format($price),
                $compare ? ' / was ৳'.number_format($compare) : ''
            ));

            usleep(250000);
        }

        $this->newLine();
        $this->info("Done. Imported: {$imported}, Updated: {$updated}, Skipped: {$skipped}, Failed: {$failed}");
        if ($dry) {
            $this->warn('Dry-run only — no database changes.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array{url:string,slug:string,name:?string,price:?float,compare_price:?float}>
     */
    private function collectFromShop(): array
    {
        $byUrl = [];

        for ($page = 1; $page <= 20; $page++) {
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                ])
                ->get($this->base.'/shop', [
                    'more' => 1,
                    'page' => $page,
                ]);

            if (! $response->successful()) {
                $this->warn("Shop page {$page} HTTP ".$response->status());
                break;
            }

            $json = $response->json();
            $html = is_array($json) ? (string) ($json['html'] ?? '') : '';
            if ($html === '') {
                break;
            }

            // Card: <a class="nm" href=".../product/slug">Name</a> ... <div class="pr">৳1,200<s>৳1,800</s></div>
            preg_match_all(
                '#<a class="nm" href="(https?://angelrosebeautycarebd\.com/product/([a-zA-Z0-9\-]+))"[^>]*>(.*?)</a>[\s\S]*?<div class="pr">(.*?)</div>#i',
                $html,
                $matches,
                PREG_SET_ORDER
            );

            $count = 0;
            foreach ($matches as $m) {
                $url = $m[1];
                $slug = $m[2];
                $name = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $priceBlock = $m[4];
                $price = null;
                $compare = null;
                if (preg_match_all('/([\d,]+(?:\.\d+)?)/', html_entity_decode(strip_tags($priceBlock), ENT_QUOTES | ENT_HTML5, 'UTF-8'), $pm)) {
                    $nums = array_map(fn ($x) => (float) str_replace(',', '', $x), $pm[1]);
                    if ($nums !== []) {
                        $price = $nums[0];
                        if (isset($nums[1]) && $nums[1] > $price) {
                            $compare = $nums[1];
                        }
                    }
                }
                $byUrl[$url] = [
                    'url' => $url,
                    'slug' => $slug,
                    'name' => $name !== '' ? $name : null,
                    'price' => $price,
                    'compare_price' => $compare,
                ];
                $count++;
            }

            // Fallback: at least collect URLs
            if ($count === 0) {
                preg_match_all('#https?://angelrosebeautycarebd\.com/product/([a-zA-Z0-9\-]+)#i', $html, $um);
                foreach (array_unique($um[0] ?? []) as $i => $url) {
                    $slug = $um[1][$i] ?? basename(parse_url($url, PHP_URL_PATH) ?: '');
                    $byUrl[$url] ??= [
                        'url' => $url,
                        'slug' => $slug,
                        'name' => null,
                        'price' => null,
                        'compare_price' => null,
                    ];
                }
                $count = count($um[0] ?? []);
            }

            $this->line("  page {$page}: {$count} items");
            if ($count === 0) {
                break;
            }
        }

        return array_values($byUrl);
    }

    /** @return array<string, mixed> */
    private function scrapeProduct(string $url): array
    {
        $response = Http::timeout(45)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml',
            ])
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status());
        }

        $html = $response->body();
        $slug = basename(parse_url($url, PHP_URL_PATH) ?: '');

        $name = null;
        // Prefer visible product heading patterns used by the theme
        if (preg_match('/<h1[^>]*class="[^"]*"[^>]*>(.*?)<\/h1>/is', $html, $m)
            || preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            $candidate = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($this->looksLikeProductName($candidate)) {
                $name = $candidate;
            }
        }
        if (! $name && preg_match('/property="og:title"\s+content="([^"]+)"/i', $html, $m)) {
            $candidate = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            // "Product Name || Angel Rose..." or "Product Name | Store"
            foreach ([' || ', ' | '] as $sep) {
                if (str_contains($candidate, $sep)) {
                    $parts = array_map('trim', explode($sep, $candidate));
                    // product name is usually the shorter / non-site part
                    foreach ($parts as $part) {
                        if ($this->looksLikeProductName($part)) {
                            $name = $part;
                            break 2;
                        }
                    }
                }
            }
            if (! $name && $this->looksLikeProductName($candidate)) {
                $name = $candidate;
            }
        }

        $price = 0.0;
        $compare = null;
        if (preg_match('/class="pr"[^>]*>(.*?)<\/div>/is', $html, $m)) {
            if (preg_match_all('/([\d,]+(?:\.\d+)?)/', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'), $pm)) {
                $nums = array_map(fn ($x) => (float) str_replace(',', '', $x), $pm[1]);
                if ($nums !== []) {
                    $price = $nums[0];
                    if (isset($nums[1]) && $nums[1] > $price) {
                        $compare = $nums[1];
                    }
                }
            }
        }

        $categoryName = null;
        $categorySlug = null;
        if (preg_match_all('#href="https?://angelrosebeautycarebd\.com/(subcategory|category)/([a-z0-9\-]+)"[^>]*>([^<]{2,40})</a>#i', $html, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $row) {
                $label = trim(html_entity_decode(strip_tags($row[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if (in_array(mb_strtolower($label), ['home', 'shop', 'all products'], true)) {
                    continue;
                }
                if ($row[1] === 'subcategory' || $categoryName === null) {
                    $categorySlug = $row[2];
                    $categoryName = $label;
                    if ($row[1] === 'subcategory') {
                        break;
                    }
                }
            }
        }

        $brandName = null;
        if (preg_match('/\bBrand\s*[:\-]\s*([A-Za-z0-9][A-Za-z0-9 .&\-]{1,60})/i', strip_tags($html), $m)) {
            $brandName = trim($m[1]);
        }

        $description = null;
        $short = null;
        if (preg_match('/property="og:description"\s+content="([^"]*)"/i', $html, $m)) {
            $short = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/class="[^"]*(?:product-description|description|ar-desc)[^"]*"[^>]*>(.*?)<\/div>/is', $html, $m)) {
            $description = trim($m[1]);
        }
        if (! $description && $short) {
            $description = '<p>'.e($short).'</p>';
        }
        if (! $short && $description) {
            $short = Str::limit(trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 240);
        }

        $images = [];
        if (preg_match_all('#https?://angelrosebeautycarebd\.com/upload/(?:product_images|gallery_images)/[^"\'\s>]+\.(?:jpg|jpeg|png|webp)#i', $html, $m)) {
            foreach ($m[0] as $img) {
                $images[$img] = true;
            }
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'price' => $price,
            'compare_price' => $compare,
            'category_name' => $categoryName,
            'category_slug' => $categorySlug,
            'brand_name' => $brandName,
            'short_description' => $short,
            'description' => $description,
            'images' => array_keys($images),
        ];
    }

    private function looksLikeProductName(?string $name): bool
    {
        if ($name === null || $name === '') {
            return false;
        }
        if (mb_strlen($name) > 180) {
            return false;
        }
        $lower = mb_strtolower($name);
        if (str_contains($lower, 'authentic cosmetics')
            || str_contains($lower, 'beauty care bd')
            || str_contains($lower, 'angel rose beauty')
            || str_contains($lower, 'product name:')) {
            return false;
        }

        return true;
    }

    private function cleanName(?string $detailName, ?string $listName, ?string $slug): string
    {
        foreach ([$listName, $detailName] as $candidate) {
            if ($candidate && $this->looksLikeProductName($candidate)) {
                return trim($candidate);
            }
        }

        return Str::title(str_replace('-', ' ', (string) $slug));
    }

    private function cleanBrand(?string $brand, string $productName): ?string
    {
        if ($brand) {
            $brand = trim(preg_replace('/\s+/', ' ', $brand) ?? '');
            if (mb_strlen($brand) > 80 || str_contains(mb_strtolower($brand), 'product name')) {
                $brand = null;
            }
        }

        if ($brand) {
            return $brand;
        }

        $hints = [
            'Dr. Althea', 'Dr. Alvin', 'DR. ALVIN', 'Dabo', 'VSC', 'Eliza Helena', 'Beauty Ever',
            'Pro Max', 'COSRX', 'Axis-Y', 'Cathy Doll', 'Baby Bright', 'Jamsai', 'Genive',
            'Nagano', 'Sasi', 'Q-Nic', '3W Clinic', 'Kim',
        ];
        foreach ($hints as $hint) {
            if (stripos($productName, $hint) !== false) {
                return $hint;
            }
        }

        return null;
    }

    private function resolveCategory(?string $name, ?string $slug): ?int
    {
        if (! $name && ! $slug) {
            $cat = Category::firstOrCreate(
                ['slug' => 'beauty'],
                ['name' => 'Beauty', 'is_active' => true, 'show_on_home' => true, 'sort_order' => 20]
            );

            return $cat->id;
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

        $compact = str_replace('-', '', $slug);
        $existing = Category::query()->get()->first(function (Category $c) use ($compact, $name) {
            return str_replace('-', '', $c->slug) === $compact
                || ($name && strcasecmp($c->name, $name) === 0);
        });
        if ($existing) {
            return $existing->id;
        }

        $cat = Category::create([
            'name' => Str::limit($name ?: Str::title(str_replace('-', ' ', $slug)), 120, ''),
            'slug' => Str::limit($slug, 120, ''),
            'is_active' => true,
            'show_on_home' => false,
            'sort_order' => 50,
        ]);

        return $cat->id;
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

        $brand = Brand::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        return $brand->id;
    }
}
