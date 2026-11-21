<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FlashDealRequest;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FlashDealController extends Controller
{
    public function index(): View
    {
        return view('admin.flash-deals.index', [
            'deals' => FlashDeal::query()
                ->withCount('items')
                ->withMax('items', 'discount_percent')
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.flash-deals.create', $this->formData());
    }

    public function store(FlashDealRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $deal = FlashDeal::create($this->payload($request));

            $this->syncItems($deal, $request->input('products', []));
        });

        return to_route('admin.flash-deals.index')->with('success', 'Flash deal saved');
    }

    public function edit(FlashDeal $flashDeal): View
    {
        $flashDeal->load('items.product.images');

        return view('admin.flash-deals.edit', $this->formData() + ['deal' => $flashDeal]);
    }

    public function update(FlashDealRequest $request, FlashDeal $flashDeal): RedirectResponse
    {
        DB::transaction(function () use ($request, $flashDeal) {
            $flashDeal->update($this->payload($request));

            $this->syncItems($flashDeal, $request->input('products', []));
        });

        return to_route('admin.flash-deals.index')->with('success', 'Flash deal updated');
    }

    /** Live product suggestions for the deal product picker. */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = trim((string) preg_replace('/\s+/u', ' ', (string) $request->query('q', '')));

        if (mb_strlen($term) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::active()
            ->where(fn ($q) => $q->matching($term)->orWhere('sku', 'like', "%{$term}%"))
            ->with(['images', 'category'])
            ->orderByRaw('case when name like ? then 0 else 1 end', [$term.'%'])
            ->orderByDesc('sold_count')
            ->limit(10)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'img' => $product->cover_url,
                'category' => $product->category?->name,
            ])
            ->all();

        return response()->json(['products' => $products]);
    }

    public function toggle(FlashDeal $flashDeal): RedirectResponse
    {
        $flashDeal->update(['is_active' => ! $flashDeal->is_active]);

        return back()->with('success', $flashDeal->is_active ? 'Flash deal enabled' : 'Flash deal disabled');
    }

    public function destroy(FlashDeal $flashDeal): RedirectResponse
    {
        $flashDeal->delete();

        return to_route('admin.flash-deals.index')->with('success', 'Flash deal deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'catalog' => collect(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(FlashDealRequest $request): array
    {
        $data = $request->safe()->except('products');

        foreach (['starts_at', 'ends_at'] as $field) {
            $data[$field] = Carbon::parse($data[$field])->format('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncItems(FlashDeal $deal, array $rows): void
    {
        $deal->items()->delete();

        foreach (array_values($rows) as $index => $row) {
            $deal->items()->create([
                'product_id' => (int) $row['product_id'],
                'discount_percent' => $row['discount_percent'],
                'deal_stock' => $row['deal_stock'] === null ? null : (int) $row['deal_stock'],
                'sort_order' => $index,
            ]);
        }

        FlashDeal::flushLiveCache();
    }
}
