@php
    $kinds = [
        'order' => ['icon' => 'cart', 'tone' => 'bg-primary/10 text-primary'],
        'low_stock' => ['icon' => 'alert', 'tone' => 'bg-danger/10 text-danger'],
        'message' => ['icon' => 'mail', 'tone' => 'bg-accent/20 text-accent-dark'],
        'customer' => ['icon' => 'users', 'tone' => 'bg-success/10 text-success'],
        'cancelled' => ['icon' => 'alert', 'tone' => 'bg-danger/10 text-danger'],
    ];
@endphp
@forelse ($items as $item)
    @php($kind = $kinds[$item->data['kind'] ?? ''] ?? $kinds['order'])
    <a href="{{ route('admin.notifications.open', $item->id) }}">
        <span class="w-9 h-9 shrink-0 rounded-xl {{ $kind['tone'] }} grid place-items-center">
            <svg class="ic ">
                <use href="#i-{{ $kind['icon'] }}" />
            </svg>
        </span>
        <span class="text-sm min-w-0">
            <b class="{{ $item->read_at ? 'font-semibold text-ink/70' : '' }}">{{ $item->data['title'] ?? 'Notification' }}</b><br>
            <span class="text-ink/50">{{ $item->data['body'] ?? '' }} · {{ $item->created_at->diffForHumans() }}</span>
        </span>
        @unless ($item->read_at)
            <i class="ml-auto w-2 h-2 shrink-0 rounded-full bg-accent"></i>
        @endunless
    </a>
@empty
    <p class="px-3 py-6 text-center text-sm text-ink/50">No notifications yet</p>
@endforelse
