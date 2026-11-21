<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'parent_id', 'name', 'slug', 'description', 'image', 'icon',
    'meta_title', 'meta_description', 'is_active', 'show_on_home', 'show_in_menu', 'sort_order', 'promos',
])]
class Category extends Model
{
    /** How many root categories the header navigation shows (after "All Products"). */
    public const NAV_LIMIT = 7;

    private static ?Collection $menuTreeMemo = null;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_home' => 'boolean',
            'show_in_menu' => 'boolean',
            'promos' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushMenuCache());
        static::deleted(fn () => static::flushMenuCache());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function getUrlAttribute(): string
    {
        return url('/category/'.$this->slug);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon ? asset($this->icon) : null;
    }

    public static function flushMenuCache(): void
    {
        static::$menuTreeMemo = null;
    }

    /**
     * Active root categories with their active descendants attached as the `children` relation (any depth).
     * An inactive category hides its whole subtree, and categories with no active products (own or in any descendant) are left out. Built once per request (no cache: Eloquent models cannot be unserialized from cache).
     *
     * @return Collection<int, self>
     */
    public static function menuTree(): Collection
    {
        return static::$menuTreeMemo ??= (function () {
            $byParent = [];
            $productCounts = Product::query()->active()->whereNotNull('category_id')->selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total', 'category_id');

            foreach (static::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get() as $category) {
                $byParent[$category->parent_id ?? 0][] = $category;
            }

            $build = function (int $parentId, array $visited) use (&$build, $byParent, $productCounts): Collection {
                $nodes = new Collection;

                foreach ($byParent[$parentId] ?? [] as $category) {
                    if (in_array($category->id, $visited, true)) {
                        continue;
                    }

                    $children = $build($category->id, [...$visited, $category->id]);

                    if (($productCounts[$category->id] ?? 0) === 0 && $children->isEmpty()) {
                        continue;
                    }

                    $category->setRelation('children', $children);
                    $nodes->push($category);
                }

                return $nodes;
            };

            return $build(0, []);
        })();
    }

    /**
     * Visible root categories the admin ticked "Show in header menu" (up to NAV_LIMIT, by sort order).
     *
     * @return Collection<int, self>
     */
    public static function navTree(): Collection
    {
        return static::menuTree()->filter(fn (self $category) => $category->show_in_menu)->take(self::NAV_LIMIT)->values();
    }

    /**
     * Root categories not pinned to the header, shown in "Shop by category".
     *
     * @return Collection<int, self>
     */
    public static function shopByCategoryList(): Collection
    {
        return static::menuTree()->reject(fn (self $category) => $category->show_in_menu)->values();
    }

    /**
     * Visible categories (any level) flagged for the homepage "Shop by category" slider.
     *
     * @return Collection<int, self>
     */
    public static function homeList(): Collection
    {
        $list = new Collection;

        $walk = function (Collection $nodes) use (&$walk, $list): void {
            foreach ($nodes as $node) {
                if ($node->show_on_home) {
                    $list->push($node);
                }

                $walk($node->children);
            }
        };

        $walk(static::menuTree());

        return $list;
    }

    /**
     * Every category in tree order (parents first), each with a `depth` and `children_count` attribute.
     *
     * @return Collection<int, self>
     */
    public static function flatTree(): Collection
    {
        $byParent = [];

        foreach (static::query()->orderBy('sort_order')->orderBy('name')->get() as $category) {
            $byParent[$category->parent_id ?? 0][] = $category;
        }

        $flat = new Collection;

        $walk = function (int $parentId, int $depth) use (&$walk, $byParent, $flat): void {
            foreach ($byParent[$parentId] ?? [] as $category) {
                $category->depth = $depth;
                $category->children_count = count($byParent[$category->id] ?? []);
                $flat->push($category);
                $walk($category->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $flat;
    }

    /**
     * @return array<int, int>
     */
    public static function descendantIds(int $id): array
    {
        $byParent = [];

        foreach (static::query()->get(['id', 'parent_id']) as $category) {
            $byParent[$category->parent_id ?? 0][] = $category->id;
        }

        $ids = [];
        $stack = [$id];

        while ($stack) {
            $current = array_pop($stack);

            foreach ($byParent[$current] ?? [] as $childId) {
                $ids[] = $childId;
                $stack[] = $childId;
            }
        }

        return $ids;
    }
}
