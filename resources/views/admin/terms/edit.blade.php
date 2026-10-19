@extends('admin.dashboard.dashboard')
@section('admin_title', 'Terms of service · Nebula Mart Admin')
@section('admin_contents')

    @php
        $keys = old('keys', $page->keys ?? []);
        $clauses = old('clauses', $page->clauses ?? []);
        $icons = \App\Support\PolicyText::ICONS;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Terms of service</h1>
            <p class="text-ink/55 mt-1">Edit what customers see on the storefront terms of service page.</p>
        </div>
        <a href="{{ route('terms.show') }}" target="_blank" class="btn btn-ghost">View page</a>
    </div>

    <form method="POST" action="{{ route('admin.terms.update') }}" class="space-y-6">
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
                    <label class="lbl" for="last_updated">Last updated</label>
                    <input class="input" id="last_updated" name="last_updated" type="date" value="{{ old('last_updated', $page->last_updated?->format('Y-m-d')) }}">
                    @error('last_updated')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="lbl" for="hero_subtitle">Sub heading</label>
                    <input class="input" id="hero_subtitle" name="hero_subtitle" value="{{ old('hero_subtitle', $page->hero_subtitle) }}" required>
                    @error('hero_subtitle')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Key points</h3></div>
            <div class="card-b space-y-3" data-repeat="keys">
                @foreach ($keys as $i => $row)
                    <div class="grid sm:grid-cols-[150px_1fr_2fr_auto] gap-3 items-end" data-item>
                        <div><label class="lbl">Icon</label>
                            <select class="select" name="keys[{{ $i }}][icon]">
                                @foreach ($icons as $key => $label)
                                    <option value="{{ $key }}" @selected(($row['icon'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select></div>
                        <div><label class="lbl">Title</label><input class="input" name="keys[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                        <div><label class="lbl">Text</label><input class="input" name="keys[{{ $i }}][text]" value="{{ $row['text'] ?? '' }}"></div>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="keys"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add key point</button>
            </div>
            <template id="tpl-keys">
                <div class="grid sm:grid-cols-[150px_1fr_2fr_auto] gap-3 items-end" data-item>
                    <div><label class="lbl">Icon</label>
                        <select class="select" name="keys[__i__][icon]">
                            @foreach ($icons as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select></div>
                    <div><label class="lbl">Title</label><input class="input" name="keys[__i__][title]"></div>
                    <div><label class="lbl">Text</label><input class="input" name="keys[__i__][text]"></div>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>Sections</h3></div>
            <div class="card-b space-y-4" data-repeat="clauses">
                <p class="text-xs text-ink/50">Leave a blank line between paragraphs. Start a line with "- " for a bullet list. Use [link text](/page) for links.</p>
                @error('clauses')<p class="text-danger text-sm">{{ $message }}</p>@enderror
                @foreach ($clauses as $i => $row)
                    <div class="rounded-2xl border border-line p-4 space-y-3" data-item>
                        <div class="flex gap-3 items-end">
                            <div class="flex-1"><label class="lbl">Section title</label><input class="input" name="clauses[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                            <button type="button" class="icon-btn" data-up title="Move up">↑</button>
                            <button type="button" class="icon-btn" data-down title="Move down">↓</button>
                            <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                        </div>
                        <textarea class="textarea" rows="4" name="clauses[{{ $i }}][body]">{{ $row['body'] ?? '' }}</textarea>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="clauses"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add section</button>
            </div>
            <template id="tpl-clauses">
                <div class="rounded-2xl border border-line p-4 space-y-3" data-item>
                    <div class="flex gap-3 items-end">
                        <div class="flex-1"><label class="lbl">Section title</label><input class="input" name="clauses[__i__][title]"></div>
                        <button type="button" class="icon-btn" data-up title="Move up">↑</button>
                        <button type="button" class="icon-btn" data-down title="Move down">↓</button>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                    <textarea class="textarea" rows="4" name="clauses[__i__][body]"></textarea>
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
            <button type="submit" class="btn btn-primary"><svg class="ic ic-sm"><use href="#i-check" /></svg>Save terms of service</button>
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
