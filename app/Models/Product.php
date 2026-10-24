<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id', 'brand_id', 'name', 'slug', 'model', 'short_description', 'description',
    'price', 'compare_price', 'cost_price', 'sku', 'barcode', 'stock', 'low_stock', 'weight',
    'track_inventory', 'status', 'is_featured', 'is_new', 'is_cod',
    'is_free_delivery', 'is_returnable', 'tags', 'meta_title', 'meta_description', 'meta_keywords', 'sold_count',
])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'compare_price' => 'float',
            'cost_price' => 'float',
            'weight' => 'float',
            'stock' => 'integer',
            'low_stock' => 'integer',
            'track_inventory' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_cod' => 'boolean',
            'is_free_delivery' => 'boolean',
            'is_returnable' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest('id');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', 'active');
    }

    #[Scope]
    protected function inCategory(Builder $query, int $categoryId): void
    {
        $query->whereIn('category_id', [$categoryId, ...Category::descendantIds($categoryId)]);
    }

    /** Every word of the term must appear in the product name. */
    #[Scope]
    protected function matchingName(Builder $query, string $term): void
    {
        foreach (static::searchWords($term) as $word) {
            $query->where('name', 'like', "%{$word}%");
        }
    }

    /** Every word of the term must appear in the name, description, tags, brand or category. */
    #[Scope]
    protected function matching(Builder $query, string $term): void
    {
        foreach (static::searchWords($term) as $word) {
            $query->where(function (Builder $w) use ($word) {
                $w->where('name', 'like', "%{$word}%")
                    ->orWhere('short_description', 'like', "%{$word}%")
                    ->orWhere('tags', 'like', "%{$word}%")
                    ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$word}%"))
                    ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$word}%"));
            });
        }
    }

    /** @return list<string> */
    protected static function searchWords(string $term): array
    {
        return array_slice(array_values(array_unique(preg_split('/\s+/u', mb_strtolower(trim($term)), -1, PREG_SPLIT_NO_EMPTY))), 0, 5);
    }

    public function getUrlAttribute(): string
    {
        return url('/product/'.$this->slug);
    }

    public function getCoverUrlAttribute(): string
    {
        $image = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $image ? $image->url : asset('assets/frontend/images/placeholder.svg');
    }

    /** Total sellable stock: sum of variants if there are any, else the product stock. */
    public function getStockTotalAttribute(): int
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();

        return $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) $this->stock;
    }

    public function getInStockAttribute(): bool
    {
        return ! $this->track_inventory || $this->stock_total > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->track_inventory && $this->stock_total > 0 && $this->stock_total <= (int) ($this->attributes['low_stock'] ?? 10);
    }

    public function getDiscountPercentAttribute(): int
    {
        if (! $this->compare_price || $this->compare_price <= $this->price) {
            return 0;
        }

        return (int) round((1 - $this->price / $this->compare_price) * 100);
    }

    /** The live flash deal line for this product, if it is in a deal running right now. */
    public function getFlashItemAttribute(): ?FlashDealProduct
    {
        return FlashDeal::liveItemMap()->get($this->id);
    }

    public function getIsOnFlashAttribute(): bool
    {
        return $this->flash_item !== null;
    }

    /** Applies the live flash discount (if any) to a price, e.g. a variant price. */
    public function flashPrice(float $price): float
    {
        return $this->flash_item?->priceFor($price) ?? $price;
    }

    public function getDisplayPriceAttribute(): float
    {
        return $this->flashPrice((float) $this->price);
    }

    /** The crossed-out price: compare price (or the normal price during a flash deal) when it is higher than what is charged. */
    public function getDisplayOldPriceAttribute(): ?float
    {
        $reference = max((float) ($this->compare_price ?? 0), (float) $this->price);

        return $reference > $this->display_price ? $reference : null;
    }

    public function getDisplayDiscountPercentAttribute(): int
    {
        $old = $this->display_old_price;

        return $old ? (int) round((1 - $this->display_price / $old) * 100) : 0;
    }

    public function getHasVariantsAttribute(): bool
    {
        return ($this->relationLoaded('variants') ? $this->variants : $this->variants()->get())->isNotEmpty();
    }

    /** @return list<string> */
    public function getTagListAttribute(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->tags))));
    }
}
