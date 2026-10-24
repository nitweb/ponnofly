<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\ReturnService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReturnRequestController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(OrderReturn::STATUSES))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $returns = OrderReturn::query()
            ->with(['order', 'items.orderItem'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $like = '%'.addcslashes(trim($term), '%_\\').'%';

                $query->whereHas('order', fn ($order) => $order
                    ->where('number', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.returns.index', [
            'returns' => $returns,
            'filters' => $filters,
            'counts' => OrderReturn::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(OrderReturn $orderReturn): View
    {
        $orderReturn->load(['order', 'items.orderItem']);

        return view('admin.returns.show', ['return' => $orderReturn]);
    }

    public function approve(OrderReturn $orderReturn, ReturnService $returns): RedirectResponse
    {
        try {
            $returns->approve($orderReturn, auth('admin')->id());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request approved and stock restored');
    }

    public function reject(Request $request, OrderReturn $orderReturn, ReturnService $returns): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        try {
            $returns->reject($orderReturn, auth('admin')->id(), $data['admin_note'] ?? null);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request rejected');
    }

    /** Staff records a return or cancellation on the customer's behalf (guest orders, handed back at the door) and approves it straight away. */
    public function manual(Request $request, Order $order, ReturnService $returns): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer'],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:1', 'max:999'],
            'reason' => ['required', Rule::in(array_keys(OrderReturn::REASONS))],
            'details' => ['nullable', 'string', 'max:500'],
        ], ['items.required' => 'Select at least one product.']);

        $quantities = [];

        foreach ($data['items'] as $itemId) {
            $quantities[(int) $itemId] = (int) ($data['qty'][$itemId] ?? 1);
        }

        try {
            $return = $returns->request($order, $order->user_id, $quantities, $data['reason'], $data['details'] ?? null, false);
            $returns->approve($return, auth('admin')->id());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $return->type_label.' recorded and stock restored');
    }
}
