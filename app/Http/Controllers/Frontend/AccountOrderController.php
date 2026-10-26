<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        $orders = $request->user()->customerOrders()
            ->when(array_key_exists($status, Order::STATUSES), fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(8)
            ->withQueryString();

        return view('frontend.account.orders', ['orders' => $orders, 'status' => $status]);
    }

    public function show(Request $request, string $number): View
    {
        $order = $request->user()->customerOrders()->with(['items.product', 'events'])->where('number', $number)->firstOrFail();

        return view('frontend.account.order', ['order' => $order]);
    }

    /** Puts every item of an earlier order back in the cart, skipping what cannot be sold any more. */
    public function reorder(Request $request, CartService $cart, string $number): RedirectResponse
    {
        $order = $request->user()->customerOrders()->with('items')->where('number', $number)->firstOrFail();

        $added = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                $skipped++;

                continue;
            }

            try {
                $cart->add($item->product_id, $item->variant_id ?: null, $item->quantity);
                $added++;
            } catch (DomainException) {
                $skipped++;
            }
        }

        if ($added === 0) {
            return back()->with('error', 'None of these items can be ordered again right now.');
        }

        return to_route('cart.index')->with('status', $skipped
            ? "{$added} item(s) added to your cart. {$skipped} could not be added because they are no longer available."
            : 'The items from this order are back in your cart.');
    }
}
