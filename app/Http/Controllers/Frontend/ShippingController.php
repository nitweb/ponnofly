<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ShippingPage;
use App\Services\CartService;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function show(CartService $cart): View
    {
        $page = ShippingPage::instance();

        $zones = collect($cart->zones())
            ->map(fn (array $zone, string $key) => $zone + $page->zoneArea($key))
            ->values();

        return view('frontend.shipping.index', ['page' => $page, 'zones' => $zones]);
    }
}
