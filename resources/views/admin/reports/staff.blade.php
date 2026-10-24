@extends('admin.dashboard.dashboard')
@section('admin_title', 'Staff performance · Nebula Mart Admin')
@section('admin_contents')

    @include('admin.reports._filters', ['title' => 'Staff performance', 'subtitle' => 'Order handling by team member', 'exportKey' => 'staff'])

    <section class="card">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Staff</th><th>Role</th><th>Orders handled</th><th>Status updates</th><th>Delivered</th><th>Cancelled / returned</th><th>Last action</th><th class="text-right">Delivered value</th></tr></thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td><a href="{{ route('admin.reports.staff.show', ['admin' => $row->admin] + request()->only(['range', 'from', 'to'])) }}" class="font-bold text-primary hover:underline">{{ $row->admin->name }}</a><p class="text-xs text-ink/50">{{ $row->admin->email }}</p></td>
                            <td>{{ $row->admin->displayTitle() }}</td>
                            <td>{{ $row->orders }}</td>
                            <td>{{ $row->updates }}</td>
                            <td>{{ $row->delivered }}</td>
                            <td>{{ $row->cancelled }}</td>
                            <td class="text-ink/60">{{ $row->last_action?->timezone(\App\Models\Setting::tz())->format('d M, h:i A') ?? '—' }}</td>
                            <td class="text-right font-bold">৳{{ number_format($row->delivered_value) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-ink/55">No staff members.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <p class="text-xs text-ink/50">Counts every order status change a staff member made from the Orders page. Changes made before this report existed are not attributed.</p>

@endsection
