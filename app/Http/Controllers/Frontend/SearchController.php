<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    private const MIN_LENGTH = 2;

    private const LIMIT = 8;

    private const PER_PAGE = 12;

    /** Full search results page, reached from the header search. */
    public function index(Request $request): View|RedirectResponse
    {
        $term = trim((string) preg_replace('/\s+/u', ' ', (string) $request->query('q', '')));

        if ($term === '') {
            return to_route('shop');
        }

        $products = Product::active()
            ->matching($term)
            ->with(['images', 'brand', 'category', 'variants'])
            ->orderByRaw('case when name like ? then 0 else 1 end', [$term.'%'])
            ->orderByDesc('sold_count')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('frontend.search.index', ['term' => $term, 'products' => $products]);
    }

    /** Live suggestions for the header search box. */
    public function suggest(Request $request): JsonResponse
    {
        $term = trim((string) preg_replace('/\s+/u', ' ', (string) $request->query('q', '')));

        if (mb_strlen($term) < self::MIN_LENGTH) {
            return response()->json(['query' => $term, 'total' => 0, 'products' => []]);
        }

        $query = Product::active()->matching($term);
        $total = (clone $query)->count();

        $products = $query
            ->with(['images', 'brand', 'category'])
            ->orderByRaw('case when name like ? then 0 else 1 end', [$term.'%'])
            ->orderByDesc('sold_count')
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'url' => $product->url,
                'image' => $product->cover_url,
                'price' => $this->money($product->display_price),
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
            ])
            ->all();

        return response()->json(['query' => $term, 'total' => $total, 'products' => $products]);
    }

    private function money(float $amount): string
    {
        return '৳'.number_format($amount, floor($amount) == $amount ? 0 : 2);
    }
}
