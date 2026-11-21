<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DedupeProducts extends Command
{
    protected $signature = 'products:dedupe
                            {--dry-run : List the duplicates that would be removed without changing anything}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Remove duplicate products (same name). Keeps one per group and merges orders, carts, wishlists, reviews, coupons and flash deals into it.';

    /**
     * Tables that reference products.product_id.
     * Value = other columns that must stay unique together with product_id (so a row is skipped, not duplicated,
     * when the kept product already has an equivalent row). An empty list means there is no unique constraint.
     * 'only' limits which rows are moved; rows left behind are removed by the foreign key cascade.
     */
    private const REFERENCES = [
        'order_items' => ['unique' => []],
        'wishlists' => ['unique' => ['user_id']],
        'reviews' => ['unique' => ['user_id']],
        'flash_deal_products' => ['unique' => ['flash_deal_id']],
        'coupon_product' => ['unique' => ['coupon_id']],
        // variant ids belong to the duplicate's own variants, so only variant-less lines can be carried over
        'cart_items' => ['unique' => ['cart_id', 'variant_id'], 'only' => ['variant_id' => 0]],
    ];

    public function handle(): int
    {
        $groups = $this->duplicateGroups();

        if ($groups->isEmpty()) {
            $this->info('No duplicate products found.');

            return self::SUCCESS;
        }

        $plan = $groups->map(fn (Collection $products) => $this->plan($products))->values();
        $toRemove = $plan->sum(fn (array $p) => count($p['remove']));

        $this->table(
            ['Product', 'Keep (id)', 'Remove (ids)'],
            $plan->map(fn (array $p) => [
                mb_strimwidth($p['name'], 0, 55, '…'),
                $p['keep'],
                implode(', ', $p['remove']),
            ])->all(),
        );
        $this->line(sprintf('%d duplicate group(s), %d product(s) to remove.', $plan->count(), $toRemove));

        if ($this->option('dry-run')) {
            $this->comment('Dry run: nothing was changed.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Remove these duplicates? This cannot be undone. Back up the database first.')) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }

        $removed = 0;
        foreach ($plan as $p) {
            DB::transaction(fn () => $this->merge($p['keep'], $p['remove']));
            $removed += count($p['remove']);
        }

        $this->info("Removed {$removed} duplicate product(s) across {$plan->count()} group(s).");

        return self::SUCCESS;
    }

    /** @return Collection<string, Collection<int, Product>> products sharing a normalized name, 2+ per group */
    private function duplicateGroups(): Collection
    {
        return Product::query()
            ->orderBy('id')
            ->get(['id', 'name', 'sold_count'])
            ->groupBy(fn (Product $p) => $this->normalize($p->name))
            ->filter(fn (Collection $g, string $key) => $key !== '' && $g->count() > 1);
    }

    /** Case, spacing and punctuation differences do not make a product different. */
    private function normalize(?string $name): string
    {
        return trim((string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower((string) $name)));
    }

    /**
     * Keep the product with the most order history (so sales reports stay attached), then the oldest one.
     *
     * @param  Collection<int, Product>  $products
     * @return array{name: string, keep: int, remove: list<int>}
     */
    private function plan(Collection $products): array
    {
        $orders = Schema::hasTable('order_items')
            ? DB::table('order_items')->whereIn('product_id', $products->pluck('id'))
                ->selectRaw('product_id, count(*) as c')->groupBy('product_id')->pluck('c', 'product_id')
            : collect();

        $sorted = $products->sortBy([
            fn ($a, $b) => ($orders[$b->id] ?? 0) <=> ($orders[$a->id] ?? 0),
            fn ($a, $b) => $a->id <=> $b->id,
        ])->values();

        return [
            'name' => $sorted->first()->name,
            'keep' => $sorted->first()->id,
            'remove' => $sorted->slice(1)->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];
    }

    /** @param  list<int>  $dupIds */
    private function merge(int $keeperId, array $dupIds): void
    {
        foreach (self::REFERENCES as $table => $rule) {
            $this->moveRows($table, $rule['unique'], $keeperId, $dupIds, $rule['only'] ?? []);
        }

        // Give the kept product a gallery / variants if it has none, taking them from the first duplicate that does.
        foreach (['product_images', 'product_variants'] as $table) {
            if (! Schema::hasTable($table) || DB::table($table)->where('product_id', $keeperId)->exists()) {
                continue;
            }
            foreach ($dupIds as $dupId) {
                if (DB::table($table)->where('product_id', $dupId)->exists()) {
                    DB::table($table)->where('product_id', $dupId)->update(['product_id' => $keeperId]);
                    break;
                }
            }
        }

        $sold = (int) Product::whereIn('id', $dupIds)->sum('sold_count');
        if ($sold > 0) {
            Product::whereKey($keeperId)->increment('sold_count', $sold);
        }

        // Anything not carried over (conflicting rows, extra images/variants) goes with the product via cascade.
        Product::whereIn('id', $dupIds)->delete();
    }

    /**
     * @param  list<string>  $uniqueBy
     * @param  list<int>  $dupIds
     * @param  array<string, mixed>  $only
     */
    private function moveRows(string $table, array $uniqueBy, int $keeperId, array $dupIds, array $only = []): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if ($uniqueBy === []) {
            DB::table($table)->whereIn('product_id', $dupIds)->where($only)->update(['product_id' => $keeperId]);

            return;
        }

        $rows = DB::table($table)->whereIn('product_id', $dupIds)->where($only)->get();

        foreach ($rows as $row) {
            $key = [];
            foreach ($uniqueBy as $column) {
                $key[$column] = $row->{$column};
            }

            if (DB::table($table)->where('product_id', $keeperId)->where($key)->exists()) {
                continue;
            }

            DB::table($table)->where('product_id', $row->product_id)->where($key)->update(['product_id' => $keeperId]);
        }
    }
}
