<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart, private CheckoutService $checkout) {}

    public function show(): View|RedirectResponse
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $user = auth()->user();
        $addresses = $user ? $user->addresses()->get() : collect();

        return view('frontend.checkout.index', [
            'user' => $user,
            'addresses' => $addresses,
            'prefill' => $addresses->first(),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        try {
            $order = $this->checkout->place($request->safe()->only(['name', 'phone', 'email', 'district', 'area', 'address', 'note']));
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['cart' => $e->getMessage()]);
        }

        session()->push('placed_orders', $order->number);

        return to_route('checkout.success', $order->number);
    }

    public function success(string $number): View
    {
        return view('frontend.checkout.success', ['order' => $this->ownedOrder($number)]);
    }

    public function invoice(string $number): View
    {
        $order = $this->ownedOrder($number);

        return view('admin.orders.invoice', [
            'order' => $order,
            'backUrl' => route('checkout.success', $order->number),
            'backLabel' => 'Back to order',
        ]);
    }

    /** Loads an order only for the signed-in owner or the visitor who just placed it. */
    private function ownedOrder(string $number): Order
    {
        $order = Order::query()->with('items')->where('number', $number)->firstOrFail();

        $owned = auth()->check() && $order->user_id === auth()->id();
        $justPlaced = in_array($order->number, (array) session('placed_orders', []), true);

        abort_unless($owned || $justPlaced, 404);

        return $order;
    }

    /** Sends the customer back where they should be when checkout is not possible. */
    private function guard(): ?RedirectResponse
    {
        if (! auth()->check() && Setting::get('guest_checkout') !== '1') {
            return redirect()->guest(route('login'));
        }

        if ($blocker = $this->checkout->blocker()) {
            return to_route('cart.index')->with('error', $blocker);
        }

        return null;
    }
}
