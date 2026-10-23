<div class="card p-5">
    <div class="flex items-start justify-between">
        <span class="{{ $tone }} stat-ic"><svg class="ic ic-lg"><use href="#i-{{ $icon }}" /></svg></span>
        @isset($current)
            @include('admin.reports._delta', ['current' => $current, 'previous' => $previous, 'invert' => $invert ?? false])
        @endisset
    </div>
    <p class="text-ink/55 font-semibold mt-4">{{ $label }}</p>
    <p class="font-display font-extrabold text-[28px] tracking-tight leading-tight">{{ $value }}</p>
    @isset($hint)<p class="text-xs text-ink/50 mt-1">{{ $hint }}</p>@endisset
</div>
