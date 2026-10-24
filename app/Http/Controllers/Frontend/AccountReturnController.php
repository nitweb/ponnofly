<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Services\ReturnService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountReturnController extends Controller
{
    public function index(Request $request): View
    {
        $returns = OrderReturn::query()
            ->whereIn('order_id', $request->user()->customerOrders()->select('id'))
            ->with(['order', 'items.orderItem'])
            ->latest('id')
            ->paginate(8);

        return view('frontend.account.returns', ['returns' => $returns]);
    }

    public function create(Request $request, string $number): View|RedirectResponse
    {
        $order = $request->user()->customerOrders()->with('items.returnItems.orderReturn')->where('number', $number)->firstOrFail();

        if (! $order->canRequestReturn()) {
            return to_route('account.orders.show', $order->number)->with('error', 'Nothing in this order can be cancelled or returned right now.');
        }

        return view('frontend.account.return', ['order' => $order]);
    }

    public function store(Request $request, ReturnService $returns, string $number): RedirectResponse
    {
        $order = $request->user()->customerOrders()->where('number', $number)->firstOrFail();

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer'],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:1', 'max:999'],
            'reason' => ['required', Rule::in(array_keys(OrderReturn::REASONS))],
            'details' => ['nullable', 'string', 'max:500'],
        ], [
            'items.required' => 'Select at least one product.',
            'reason.required' => 'Please tell us why.',
        ]);

        $quantities = [];

        foreach ($data['items'] as $itemId) {
            $quantities[(int) $itemId] = (int) ($data['qty'][$itemId] ?? 1);
        }

        try {
            $return = $returns->request($order, $request->user()->id, $quantities, $data['reason'], $data['details'] ?? null);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return to_route('account.returns')->with('status', "Your {$return->type_label} request {$return->number} was sent. We will review it shortly.");
    }
}
