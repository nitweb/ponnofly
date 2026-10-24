<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));

        $customers = User::query()
            ->with(['orders' => fn ($orders) => $orders->with('items:id,order_id,name,variant_label,quantity')->latest('id')->limit(5)])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhereHas('orders', fn ($orders) => $orders->where('phone', 'like', $like)));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', ['customers' => $customers, 'term' => $term]);
    }

    public function show(User $customer, ReportService $reports): View
    {
        return view('admin.customers.show', [
            'customer' => $customer->load('addresses'),
            'profile' => $reports->customerProfile($customer),
        ]);
    }

    /** Stops (or allows again) this customer from placing orders. They can still sign in and browse. */
    public function restriction(Request $request, User $customer): RedirectResponse
    {
        $restrict = ! $customer->is_restricted;

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $customer->forceFill([
            'is_restricted' => $restrict,
            'restriction_reason' => $restrict ? ($data['reason'] ?? null) : null,
            'restricted_at' => $restrict ? now() : null,
        ])->save();

        return back()->with('success', $restrict ? 'Customer restricted from placing orders' : 'Customer can place orders again');
    }
}
