@extends('admin.dashboard.dashboard')
@section('admin_title', 'Messages · Nebula Mart Admin')
@section('admin_contents')

    @php
        $current = $filters['status'] ?? 'all';
        $keep = fn (array $merge) => array_filter(array_merge(request()->except('page'), $merge), fn ($value) => $value !== null && $value !== '');
        $query = array_filter($filters);
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Messages</h1>
            <p class="text-ink/55 mt-1">Customer messages from the contact form.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if ($unreadCount)
                <form method="POST" action="{{ route('admin.messages.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-ghost" type="submit"><svg class="ic ic-sm"><use href="#i-check" /></svg>Mark all read</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card overflow-hidden grid lg:grid-cols-[380px_1fr] min-h-[560px]">
        <div class="border-r border-line">
            <form method="GET" action="{{ route('admin.messages.index') }}" class="p-4 border-b border-line space-y-3">
                <div class="input-group">
                    <svg class="ic"><use href="#i-search" /></svg>
                    <input class="input" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search messages">
                </div>
                <select class="select" name="status" onchange="this.form.submit()" aria-label="Status">
                    <option value="">All messages</option>
                    @foreach (\App\Models\ContactMessage::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($current === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>

            @forelse ($messages as $item)
                @php($isActive = $selected?->is($item))
                <a href="{{ route('admin.messages.show', ['message' => $item] + $query) }}" class="w-full text-left p-4 border-b border-line hover:bg-primary-soft transition flex gap-3 {{ $isActive ? 'bg-primary-soft' : '' }}">
                    <span class="w-11 h-11 rounded-xl bg-primary text-white grid place-items-center font-display font-extrabold shrink-0">{{ $item->initials }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="flex justify-between gap-2">
                            <b class="truncate {{ $item->status === 'unread' ? '' : 'font-semibold' }}">{{ $item->name }}</b>
                            <small class="text-ink/45 shrink-0">{{ $item->created_at->diffForHumans(short: true) }}</small>
                        </span>
                        <span class="block truncate text-sm {{ $item->status === 'unread' ? 'font-bold' : 'text-ink/60' }}">{{ $item->topic }}</span>
                        <span class="block truncate text-xs text-ink/50">{{ $item->message }}</span>
                        <span class="block mt-1"><span class="badge {{ \App\Models\ContactMessage::BADGES[$item->status] ?? 'b-muted' }}">{{ $item->status_label }}</span></span>
                    </span>
                </a>
            @empty
                <p class="p-6 text-ink/55">No messages found.</p>
            @endforelse

            @if ($messages->hasPages())
                <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm text-ink/55">
                    <span>{{ $messages->firstItem() }}–{{ $messages->lastItem() }} of {{ $messages->total() }}</span>
                    <div class="pg flex items-center gap-1">
                        @if ($messages->onFirstPage())
                            <span class="px-1 text-ink/30" aria-hidden="true">‹</span>
                        @else
                            <a href="{{ $messages->previousPageUrl() }}" aria-label="Previous">‹</a>
                        @endif
                        @if ($messages->hasMorePages())
                            <a href="{{ $messages->nextPageUrl() }}" aria-label="Next">›</a>
                        @else
                            <span class="px-1 text-ink/30" aria-hidden="true">›</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="flex flex-col">
            @if ($selected)
                <div class="p-6 border-b border-line flex flex-wrap items-center gap-4">
                    <span class="w-12 h-12 rounded-2xl bg-primary text-white grid place-items-center font-display font-extrabold text-lg">{{ $selected->initials }}</span>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-display font-extrabold text-lg">{{ $selected->topic }}</h3>
                        <p class="text-sm text-ink/55">{{ $selected->name }} · {{ $selected->email }} · {{ $selected->phone }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.messages.destroy', $selected) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="icon-btn danger" data-delete="message from {{ $selected->name }}" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </form>
                </div>

                <div class="p-6 flex-1 space-y-4">
                    <div class="bg-bg rounded-2xl rounded-tl-sm p-5 max-w-2xl">
                        <p class="whitespace-pre-line">{{ $selected->message }}</p>
                        <p class="text-xs text-ink/45 mt-3">{{ $selected->created_at->diffForHumans() }} · {{ $selected->created_at->format('d M Y, h:i A') }} · via Contact form</p>
                    </div>

                    @if ($selected->reply)
                        <div class="bg-primary-soft rounded-2xl rounded-tr-sm p-5 max-w-2xl ml-auto">
                            <p class="whitespace-pre-line">{{ $selected->reply }}</p>
                            <p class="text-xs text-ink/45 mt-3">Replied {{ $selected->replied_at?->format('d M Y, h:i A') }}</p>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.messages.reply', $selected) }}" class="p-5 border-t border-line">
                    @csrf
                    <textarea class="textarea" name="reply" rows="3" placeholder="Write your reply…" required>{{ old('reply') }}</textarea>
                    @error('reply')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
                    <div class="flex justify-end mt-3">
                        <button class="btn btn-primary" type="submit"><svg class="ic ic-sm"><use href="#i-reply" /></svg>{{ $selected->reply ? 'Send another reply' : 'Send reply' }}</button>
                    </div>
                </form>
            @else
                <div class="flex-1 grid place-items-center p-10 text-center text-ink/55">
                    <div>
                        <span class="w-14 h-14 rounded-2xl bg-primary-soft text-primary grid place-items-center mx-auto mb-3"><svg class="ic ic-lg"><use href="#i-mail" /></svg></span>
                        <p class="font-semibold">Select a message to read it</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
