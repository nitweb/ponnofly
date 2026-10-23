@php($change = $reports->growth($current, $previous))
@if ($change !== null)
    @php($good = $invert ?? false ? $change <= 0 : $change >= 0)
    <span class="badge {{ $good ? 'b-success' : 'b-danger' }} !py-0" title="vs previous period">
        <svg class="ic ic-sm"><use href="#i-{{ $change >= 0 ? 'up' : 'down' }}" /></svg>{{ abs($change) }}%
    </span>
@endif
