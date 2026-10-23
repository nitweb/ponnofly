<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Setting;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(Order::STATUSES))],
            'payment' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'courier' => ['nullable', Rule::in([...array_keys(Setting::COURIERS), 'none'])],
            'date' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = Order::query()
            ->with('items:id,order_id,quantity')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment'] ?? null, fn ($query, $payment) => $query->where('payment_status', $payment))
            ->when($filters['courier'] ?? null, fn ($query, $courier) => $courier === 'none'
                ? $query->whereNull('courier')
                : $query->where('courier', $courier))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', $date))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $like = '%'.addcslashes(trim($term), '%_\\').'%';

                $query->where(fn ($inner) => $inner
                    ->where('number', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'counts' => Order::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'events']);

        return view('admin.orders.show', [
            'order' => $order,
            'customerOrders' => Order::query()->where('phone', $order->phone)->count(),
        ]);
    }

    public function update(Request $request, Order $order, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'courier' => ['nullable', Rule::in(array_keys(Setting::COURIERS))],
            'tracking_id' => ['nullable', 'string', 'max:80'],
        ]);

        if ($order->isFinal()) {
            return back()->with('error', 'This order is '.strtolower($order->status_label).' and can no longer be changed.');
        }

        DB::transaction(function () use ($order, $data, $checkout) {
            $changed = $order->status !== $data['status'];

            $order->fill([
                'status' => $data['status'],
                'courier' => $data['courier'] ?? null,
                'tracking_id' => $data['tracking_id'] ?? null,
            ]);

            if ($data['status'] === 'delivered') {
                $order->payment_status = 'paid';
            }

            $order->save();

            if ($changed) {
                OrderEvent::create(['order_id' => $order->id, 'admin_id' => auth('admin')->id(), 'status' => $data['status'], 'note' => null]);
            }

            if ($order->isFinal()) {
                $checkout->restock($order->load('items'));
            }
        });

        return back()->with('success', 'Order updated');
    }

    public function note(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1000']]);

        $order->update(['admin_note' => $data['admin_note'] ?? null]);

        return back()->with('success', 'Note saved');
    }
}
