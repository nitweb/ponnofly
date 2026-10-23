@extends('admin.dashboard.dashboard')
@section('admin_title', 'Notifications · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Notifications</h1>
            <p class="text-ink/55 mt-1">{{ $unreadCount ? $unreadCount.' unread' : 'You are all caught up.' }}</p>
        </div>
        @if ($unreadCount)
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-ghost">Mark all as read</button>
            </form>
        @endif
    </div>

    <section class="card">
        <div class="card-b">
            <div class="dropdown-menu !static !block !w-full !border-0 !shadow-none !p-0">
                @include('admin.notifications._list', ['items' => $notifications])
            </div>
        </div>
        @if ($notifications->hasPages())
            <div class="px-6 pb-6">{{ $notifications->links() }}</div>
        @endif
    </section>

@endsection
