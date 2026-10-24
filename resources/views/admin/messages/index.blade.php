@extends('admin.dashboard.dashboard')
@section('admin_title', 'Messages · Nebula Mart Admin')
@section('admin_contents')

    @php
        $current = $filters['status'] ?? '';
        $actor = auth('admin')->user();
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Messages</h1>
            <p class="text-ink/55 mt-1">Customer messages from the contact form.</p>
        </div>
        @if ($unreadCount && $actor->hasPermission('messages:edit'))
            <form method="POST" action="{{ route('admin.messages.read-all') }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-ghost" type="submit"><svg class="ic ic-sm"><use href="#i-check" /></svg>Mark all read ({{ $unreadCount }})</button>
            </form>
        @endif
    </div>

    <section class="card">
        <form method="GET" action="{{ route('admin.messages.index') }}" class="flex flex-wrap items-center gap-3 p-5 border-b border-line">
            <div class="input-group flex-1 min-w-[220px] max-w-sm">
                <svg class="ic"><use href="#i-search" /></svg>
                <input class="input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, email, phone, message…">
            </div>
            <div class="w-48">
                <select class="select" name="status" onchange="this.form.submit()" aria-label="Status">
                    <option value="">All messages</option>
                    @foreach (\Illuminate\Support\Arr::except(\App\Models\ContactMessage::STATUSES, 'replied') as $key => $label)
                        <option value="{{ $key }}" @selected($current === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Topic</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $item)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-primary text-white grid place-items-center font-display font-extrabold shrink-0">{{ $item->initials }}</span>
                                    <div class="min-w-0">
                                        <b class="{{ $item->status === 'unread' ? '' : 'font-semibold' }}">{{ $item->name }}</b>
                                        <p class="text-xs text-ink/50">{{ $item->email }}@if ($item->phone) · {{ $item->phone }}@endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="{{ $item->status === 'unread' ? 'font-bold' : '' }}">{{ $item->topic }}</td>
                            <td class="max-w-xs"><span class="block truncate text-ink/60">{{ $item->message }}</span></td>
                            <td><span class="badge {{ \App\Models\ContactMessage::BADGES[$item->status] ?? 'b-muted' }}">{{ $item->status_label }}</span></td>
                            <td class="text-ink/60 whitespace-nowrap">{{ $item->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.messages.show', $item) }}" class="icon-btn" title="View details"><svg class="ic"><use href="#i-eye" /></svg></a>
                                    @if ($actor->hasPermission('messages:delete'))
                                        <form method="POST" action="{{ route('admin.messages.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="icon-btn danger" data-delete="message from {{ $item->name }}" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-ink/55">No messages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-line">
                <p class="text-sm text-ink/55">Showing <b class="text-ink">{{ $messages->firstItem() }}–{{ $messages->lastItem() }}</b> of <b class="text-ink">{{ $messages->total() }}</b> results</p>
                <div class="pg flex items-center gap-1">
                    @if ($messages->onFirstPage())
                        <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                    @else
                        <a href="{{ $messages->previousPageUrl() }}" aria-label="Previous">‹</a>
                    @endif
                    @foreach ($messages->getUrlRange(1, $messages->lastPage()) as $page => $url)
                        <a href="{{ $url }}" @class(['active' => $page === $messages->currentPage()])>{{ $page }}</a>
                    @endforeach
                    @if ($messages->hasMorePages())
                        <a href="{{ $messages->nextPageUrl() }}" aria-label="Next">›</a>
                    @else
                        <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                    @endif
                </div>
            </div>
        @endif
    </section>

@endsection
