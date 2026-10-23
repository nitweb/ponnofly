@php
    $chart = [
        'a' => [
            'labels' => array_column($timeline, 'label'),
            'series' => $series,
            'colors' => $colors,
        ],
    ];
@endphp
<div id="{{ $chartId }}" data-area='@json($chart)'></div>
<div class="flex flex-wrap gap-4 mt-3 text-xs text-ink/60">
    @foreach ($names as $i => $name)
        <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full" style="background: {{ $colors[$i] }}"></i>{{ $name }}</span>
    @endforeach
</div>
