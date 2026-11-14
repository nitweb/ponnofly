@php
    $large = $large ?? false;
    $brandLogo = $setting('admin_logo') ?: $setting('logo_footer') ?: $setting('logo_header');
    $brandName = $setting('store_name');
    $brandInitials = \Illuminate\Support\Str::of($brandName)->explode(' ')->filter()->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('');
@endphp

@if ($brandLogo)
    <img src="{{ asset($brandLogo) }}" alt="{{ $brandName }}" class="{{ $large ? 'h-14 max-w-[15rem]' : 'h-11 max-w-[11rem]' }} w-auto object-contain object-left">
@else
    <span class="{{ $large ? 'w-12 h-12 rounded-2xl text-xl' : 'w-10 h-10 rounded-xl text-base shadow-lg' }} bg-gradient-to-br from-accent to-amber-300 grid place-items-center text-[#1b1300] font-display font-extrabold tracking-tighter uppercase">{{ $brandInitials }}</span>

    <div class="leading-tight">
        <p class="font-display font-extrabold {{ $large ? 'text-xl' : 'text-lg tracking-tight' }}">{{ $brandName }}</p>
        <p class="{{ $large ? 'text-xs tracking-[.2em] text-white/50' : 'text-[11px] tracking-[.18em] text-white/45' }} uppercase font-bold">Admin Console</p>
    </div>
@endif
