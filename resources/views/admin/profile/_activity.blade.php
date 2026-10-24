@if ($items->isEmpty())
    <p class="text-sm text-ink/50">No activity recorded yet.</p>
@else
    <ul class="space-y-5">
        @foreach ($items as $activity)
            <li class="flex gap-4 items-start">
                <span class="w-10 h-10 rounded-xl {{ $activity->iconClasses() }} grid place-items-center shrink-0">
                    <svg class="ic"><use href="#i-{{ $activity->iconId() }}" /></svg>
                </span>
                <div>
                    <p class="font-semibold">{{ $activity->description }}</p>
                    <p class="text-xs text-ink/50">
                        {{ $activity->created_at->timezone(\App\Models\Setting::tz())->format('d M Y, g:i A') }}
                        @if ($activity->ip_address) · {{ $activity->ip_address }} @endif
                        · {{ $activity->browserLabel() }}
                    </p>
                </div>
            </li>
        @endforeach
    </ul>
@endif
