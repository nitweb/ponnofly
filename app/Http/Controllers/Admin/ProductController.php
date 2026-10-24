<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    private const IMAGE_SIZE = 800;

    private const DIRECTORY = 'uploads/products';

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));
        $categoryId = (int) $request->query('category');
        $sort = $request->query('sort', 'new');

        $query = Product::query()->with(['category', 'brand', 'images', 'variants']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->inCategory($categoryId);
        }

        match ($status) {
            'active' => $query->where('status', 'active'),
            'draft' => $query->where('status', 'draft'),
            'out' => $query->where('track_inventory', true)->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereDoesntHave('variants')->where('stock', 0);
                })->orWhere(function ($q2) {
                    $q2->whereHas('variants')->whereDoesntHave('variants', fn ($v) => $v->where('stock', '>', 0));
                });
            }),
            default => null,
        };

        match ($sort) {
            'lo' => $query->orderBy('price'),
            'hi' => $query->orderByDesc('price'),
            'stock' => $query->orderBy('stock'),
            default => $query->latest('id'),
        };

        return view('admin.products.index', [
            'products' => $query->paginate(10)->withQueryString(),
            'categories' => Category::flatTree(),
            'counts' => [
                'all' => Product::count(),
                'active' => Product::where('status', 'active')->count(),
                'draft' => Product::where('status', 'draft')->count(),
            ],
            'filters' => compact('status', 'search', 'categoryId', 'sort'),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', $this->formData());
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $product = Product::create($this->payload($request));

            $this->syncVariants($product, $request->input('variants', []));
            $this->storeImages($product, $request->file('images', []));
            $this->reorderImages($product, null);
        });

        return to_route('admin.products.index')->with('success', 'Product saved');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'variants']);

        return view('admin.products.edit', $this->formData() + ['product' => $product]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $product->update($this->payload($request, $product->id));

            $this->syncVariants($product, $request->input('variants', []));

            $remove = array_map('intval', $request->input('remove_images', []));

            if ($remove) {
                foreach ($product->images()->whereIn('id', $remove)->get() as $image) {
                    $this->deleteFile($image->path);
                    $image->delete();
                }
            }

            $this->storeImages($product, $request->file('images', []));
            $this->reorderImages($product, $request->filled('cover_image') ? (int) $request->input('cover_image') : null);
        });

        return to_route('admin.products.index')->with('success', 'Product updated');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['status' => $product->status === 'active' ? 'draft' : 'active']);

        return back()->with('success', $product->status === 'active' ? 'Product activated' : 'Product moved to draft');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            $this->deleteFile($image->path);
        }

        $product->delete();

        return to_route('admin.products.index')->with('success', 'Product deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'categories' => Category::flatTree(),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ProductRequest $request, ?int $ignoreId = null): array
    {
        $data = $request->safe()->except(['images', 'remove_images', 'cover_image', 'variants']);
        $data['description'] = \App\Support\HtmlSanitizer::clean($data['description'] ?? null) ?: null;
        $data['slug'] = $this->resolveSlug($request->input('slug'), $data['name'], $ignoreId);

        foreach (['category_id', 'brand_id', 'compare_price', 'cost_price', 'weight', 'sku'] as $nullable) {
            if (($data[$nullable] ?? null) === '') {
                $data[$nullable] = null;
            }
        }

        return $data;
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'product';
        $candidate = $base;
        $suffix = 2;

        while (Product::where('slug', $candidate)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncVariants(Product $product, array $rows): void
    {
        $keep = [];

        foreach (array_values($rows) as $index => $row) {
            $size = trim((string) ($row['size'] ?? ''));
            $color = trim((string) ($row['color'] ?? ''));

            if ($size === '' && $color === '') {
                continue;
            }

            $attributes = [
                'size' => $size !== '' ? $size : null,
                'color' => $color !== '' ? $color : null,
                'sku' => trim((string) ($row['sku'] ?? '')) ?: null,
                'price' => ($row['price'] ?? '') !== '' ? $row['price'] : null,
                'stock' => (int) ($row['stock'] ?? 0),
                'sort_order' => $index,
            ];

            $variant = ! empty($row['id']) ? $product->variants()->whereKey($row['id'])->first() : null;

            if ($variant) {
                $variant->update($attributes);
            } else {
                $variant = $product->variants()->create($attributes);
            }

            $keep[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $keep)->delete();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeImages(Product $product, array $files): void
    {
        if (! $files) {
            return;
        }

        File::ensureDirectoryExists(public_path(self::DIRECTORY));

        $order = (int) $product->images()->max('sort_order');
        $manager = new ImageManager(new Driver);

        foreach ($files as $file) {
            $path = self::DIRECTORY.'/'.Str::uuid().'.webp';

            $manager->read($file)->cover(self::IMAGE_SIZE, self::IMAGE_SIZE)->toWebp(85)->save(public_path($path));

            $product->images()->create(['path' => $path, 'sort_order' => ++$order]);
        }
    }

    private function reorderImages(Product $product, ?int $coverId): void
    {
        $images = $product->images()->get();

        if ($coverId && $images->contains('id', $coverId)) {
            $images = $images->sortBy(fn ($image) => $image->id === $coverId ? 0 : 1)->values();
        }

        foreach ($images->values() as $index => $image) {
            if ($image->sort_order !== $index) {
                $image->update(['sort_order' => $index]);
            }
        }
    }

    private function deleteFile(?string $path): void
    {
        if ($path && str_starts_with($path, self::DIRECTORY.'/')) {
            File::delete(public_path($path));
        }
    }
}
