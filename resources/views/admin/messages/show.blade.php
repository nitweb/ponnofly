@extends('admin.dashboard.dashboard')
@section('admin_title', 'Message details · Nebula Mart Admin')
@section('admin_contents')

    @php $actor = auth('admin')->user(); @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Message details</h1>
            <p class="text-ink/55 mt-1">{{ $message->topic }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.messages.index') }}" class="btn btn-ghost">← Back to messages</a>
            @if ($actor->hasPermission('messages:delete'))
                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-ghost !text-danger" data-delete="message from {{ $message->name }}"><svg class="ic ic-sm"><use href="#i-trash" /></svg>Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2 space-y-6">
            <section class="card">
                <div class="card-h"><h3>Message</h3><span class="badge {{ \App\Models\ContactMessage::BADGES[$message->status] ?? 'b-muted' }}">{{ $message->status_label }}</span></div>
                <div class="card-b space-y-4">
                    <div class="bg-bg rounded-2xl p-5">
                        <p class="whitespace-pre-line">{{ $message->message }}</p>
                        <p class="text-xs text-ink/45 mt-3">{{ $message->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, h:i A') }} · {{ $message->created_at->diffForHumans() }} · via Contact form</p>
                    </div>
                </div>
            </section>
        </div>

        <section class="card">
            <div class="card-h"><h3>Customer</h3></div>
            <div class="card-b space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-primary text-white grid place-items-center font-display font-extrabold text-lg">{{ $message->initials }}</span>
                    <b class="text-lg">{{ $message->name }}</b>
                </div>
                <div>
                    <p class="text-xs text-ink/50">Email</p>
                    <a class="font-semibold text-primary" href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                </div>
                <div>
                    <p class="text-xs text-ink/50">Phone</p>
                    <p class="font-semibold">{{ $message->phone ?: '—' }}</p>
                </div>
            </div>
        </section>
    </div>

@endsection
