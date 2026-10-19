@extends('admin.dashboard.dashboard')
@section('admin_title', 'Shipping info · Nebula Mart Admin')
@section('admin_contents')

    @php
        $know = old('know_items', $page->know_items ?? []);
        $steps = old('flow_steps', $page->flow_steps ?? []);
        $icons = \App\Models\ShippingPage::ICONS;
        $zoneRates = app(\App\Services\CartService::class)->zones();
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Shipping info</h1>
            <p class="text-ink/55 mt-1">Edit what customers see on the storefront shipping page.</p>
        </div>
        <a href="{{ route('shipping.show') }}" target="_blank" class="btn btn-ghost">View page</a>
    </div>

    <form method="POST" action="{{ route('admin.shipping.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="card">
            <div class="card-h"><h3>Header</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="hero_title">Heading</label>
                    <input class="input" id="hero_title" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}" required>
                    @error('hero_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="hero_subtitle">Sub heading</label>
                    <input class="input" id="hero_subtitle" name="hero_subtitle" value="{{ old('hero_subtitle', $page->hero_subtitle) }}" required>
                    @error('hero_subtitle')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Delivery zones</h3></div>
            <div class="card-b space-y-5">
                <p class="text-xs text-ink/50">Charges, delivery times and on/off are managed in <a href="{{ route('admin.settings.edit') }}" class="text-primary font-semibold">Settings → Delivery zones</a>, so the page, cart and checkout always agree.</p>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="lbl" for="zones_title">Section heading</label>
                        <input class="input" id="zones_title" name="zones_title" value="{{ old('zones_title', $page->zones_title) }}" required>
                        @error('zones_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="zones_text">Section text</label>
                        <input class="input" id="zones_text" name="zones_text" value="{{ old('zones_text', $page->zones_text) }}" required>
                        @error('zones_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                @foreach (\App\Models\Setting::ZONES as $zone => $label)
                    @php $area = $page->zoneArea($zone); @endphp
                    <div class="grid sm:grid-cols-[150px_minmax(0,1.2fr)_minmax(0,2fr)] gap-3 items-end rounded-2xl border border-line p-4">
                        <div><label class="lbl">Icon</label>
                            <select class="select" name="zone_areas[{{ $zone }}][icon]">
                                @foreach ($icons as $key => $iconLabel)
                                    <option value="{{ $key }}" @selected(old("zone_areas.{$zone}.icon", $area['icon']) === $key)>{{ $iconLabel }}</option>
                                @endforeach
                            </select></div>
                        <div>
                            <label class="lbl">Zone</label>
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-line bg-primary-soft/40 px-4 h-[46px]">
                                <b class="truncate">{{ $label }}</b>
                                @if (isset($zoneRates[$zone]))
                                    <span class="shrink-0 text-xs font-semibold text-primary whitespace-nowrap">৳{{ number_format($zoneRates[$zone]['fee']) }} · {{ $zoneRates[$zone]['time'] }}</span>
                                @else
                                    <span class="shrink-0 text-xs text-ink/50">Turned off</span>
                                @endif
                            </div>
                        </div>
                        <div><label class="lbl">Areas covered</label><input class="input" name="zone_areas[{{ $zone }}][areas]" value="{{ old("zone_areas.{$zone}.areas", $area['areas']) }}"></div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Good to know</h3></div>
            <div class="card-b space-y-3" data-repeat="know_items">
                <div class="max-w-md">
                    <label class="lbl" for="know_title">Section heading</label>
                    <input class="input" id="know_title" name="know_title" value="{{ old('know_title', $page->know_title) }}" required>
                    @error('know_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @foreach ($know as $i => $row)
                    <div class="grid sm:grid-cols-[150px_1fr_2fr_auto] gap-3 items-end" data-item>
                        <div><label class="lbl">Icon</label>
                            <select class="select" name="know_items[{{ $i }}][icon]">
                                @foreach ($icons as $key => $label)
                                    <option value="{{ $key }}" @selected(($row['icon'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select></div>
                        <div><label class="lbl">Title</label><input class="input" name="know_items[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                        <div><label class="lbl">Text</label><input class="input" name="know_items[{{ $i }}][text]" value="{{ $row['text'] ?? '' }}"></div>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="know_items"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add card</button>
            </div>
            <template id="tpl-know_items">
                <div class="grid sm:grid-cols-[150px_1fr_2fr_auto] gap-3 items-end" data-item>
                    <div><label class="lbl">Icon</label>
                        <select class="select" name="know_items[__i__][icon]">
                            @foreach ($icons as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select></div>
                    <div><label class="lbl">Title</label><input class="input" name="know_items[__i__][title]"></div>
                    <div><label class="lbl">Text</label><input class="input" name="know_items[__i__][text]"></div>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>From checkout to your door</h3></div>
            <div class="card-b space-y-3" data-repeat="flow_steps">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="lbl" for="flow_title">Section heading</label>
                        <input class="input" id="flow_title" name="flow_title" value="{{ old('flow_title', $page->flow_title) }}" required>
                        @error('flow_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="flow_text">Section text</label>
                        <input class="input" id="flow_text" name="flow_text" value="{{ old('flow_text', $page->flow_text) }}" required>
                        @error('flow_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                @foreach ($steps as $i => $row)
                    <div class="grid sm:grid-cols-[1fr_2fr_auto_auto_auto] gap-3 items-end" data-item>
                        <div><label class="lbl">Step title</label><input class="input" name="flow_steps[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                        <div><label class="lbl">Text</label><input class="input" name="flow_steps[{{ $i }}][text]" value="{{ $row['text'] ?? '' }}"></div>
                        <button type="button" class="icon-btn" data-up title="Move up">↑</button>
                        <button type="button" class="icon-btn" data-down title="Move down">↓</button>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="flow_steps"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add step</button>
            </div>
            <template id="tpl-flow_steps">
                <div class="grid sm:grid-cols-[1fr_2fr_auto_auto_auto] gap-3 items-end" data-item>
                    <div><label class="lbl">Step title</label><input class="input" name="flow_steps[__i__][title]"></div>
                    <div><label class="lbl">Text</label><input class="input" name="flow_steps[__i__][text]"></div>
                    <button type="button" class="icon-btn" data-up title="Move up">↑</button>
                    <button type="button" class="icon-btn" data-down title="Move down">↓</button>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>Bottom banner</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="cta_title">Heading</label>
                    <input class="input" id="cta_title" name="cta_title" value="{{ old('cta_title', $page->cta_title) }}" required>
                    @error('cta_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="cta_text">Text</label>
                    <input class="input" id="cta_text" name="cta_text" value="{{ old('cta_text', $page->cta_text) }}" required>
                    @error('cta_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="savebar">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><svg class="ic ic-sm"><use href="#i-check" /></svg>Save shipping info</button>
        </div>
    </form>

    <script>
        (function () {
            [].forEach.call(document.querySelectorAll('[data-add]'), function (btn) {
                var box = btn.closest('[data-repeat]');
                var tpl = document.getElementById('tpl-' + btn.getAttribute('data-add'));
                var next = box.querySelectorAll('[data-item]').length + 100;
                btn.addEventListener('click', function () {
                    var wrap = document.createElement('div');
                    wrap.innerHTML = tpl.innerHTML.replace(/__i__/g, next++).trim();
                    box.insertBefore(wrap.firstElementChild, btn);
                });
            });
            document.addEventListener('click', function (e) {
                var item = e.target.closest('[data-item]');
                if (!item) return;
                if (e.target.closest('[data-remove]')) item.remove();
                else if (e.target.closest('[data-up]') && item.previousElementSibling && item.previousElementSibling.hasAttribute('data-item')) item.parentNode.insertBefore(item, item.previousElementSibling);
                else if (e.target.closest('[data-down]') && item.nextElementSibling && item.nextElementSibling.hasAttribute('data-item')) item.parentNode.insertBefore(item.nextElementSibling, item);
            });
        })();
    </script>

@endsection
