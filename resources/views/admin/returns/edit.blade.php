@extends('admin.dashboard.dashboard')
@section('admin_title', 'Returns & refunds · Nebula Mart Admin')
@section('admin_contents')

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">Returns &amp; refunds</h1>
            <p class="text-ink/55 mt-1">Edit what customers see on the storefront returns page.</p>
        </div>
        <a href="{{ route('returns.show') }}" target="_blank" class="btn btn-ghost">View page</a>
    </div>

    <form method="POST" action="{{ route('admin.returns.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card p-4 text-sm text-ink/70">
            The return window and refund time come from <a href="{{ route('admin.settings.edit') }}" class="text-primary font-semibold">Settings → Rules &amp; payment</a>
            (now <b>{{ $setting('return_days') }} days</b> and <b>{{ $setting('refund_time') }}</b>). Write <code>{days}</code> or <code>{refund}</code> in any text below to insert them.
        </div>

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
            <div class="card-h"><h3>Return window</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="win_caption">Caption under the number</label>
                    <input class="input" id="win_caption" name="win_caption" value="{{ old('win_caption', $page->win_caption) }}" required>
                    @error('win_caption')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="win_title">Heading</label>
                    <input class="input" id="win_title" name="win_title" value="{{ old('win_title', $page->win_title) }}" required>
                    @error('win_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="win_text">Intro text</label>
                    <input class="input" id="win_text" name="win_text" value="{{ old('win_text', $page->win_text) }}" required>
                    @error('win_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Item conditions</h3></div>
            <div class="card-b space-y-3" data-repeat="win_needs">
                @foreach (old('win_needs', $page->win_needs ?? []) as $i => $row)
                    <div class="flex gap-3 items-end" data-item>
                        <div class="flex-1"><input class="input" name="win_needs[{{ $i }}]" value="{{ $row }}"></div>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="win_needs"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add condition</button>
            </div>
            <template id="tpl-win_needs">
                <div class="flex gap-3 items-end" data-item>
                    <div class="flex-1"><input class="input" name="win_needs[__i__]"></div>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>How to return an item</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="steps_title">Section heading</label>
                    <input class="input" id="steps_title" name="steps_title" value="{{ old('steps_title', $page->steps_title) }}" required>
                    @error('steps_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="steps_text">Section text</label>
                    <input class="input" id="steps_text" name="steps_text" value="{{ old('steps_text', $page->steps_text) }}" required>
                    @error('steps_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Return steps</h3></div>
            <div class="card-b space-y-3" data-repeat="steps">
                @foreach (old('steps', $page->steps ?? []) as $i => $row)
                    <div class="grid sm:grid-cols-[1fr_2fr_auto_auto_auto] gap-3 items-end" data-item>
                        <div><label class="lbl">Step title</label><input class="input" name="steps[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                        <div><label class="lbl">Text</label><input class="input" name="steps[{{ $i }}][text]" value="{{ $row['text'] ?? '' }}"></div>
                        <button type="button" class="icon-btn" data-up title="Move up">↑</button>
                        <button type="button" class="icon-btn" data-down title="Move down">↓</button>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="steps"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add step</button>
            </div>
            <template id="tpl-steps">
                <div class="grid sm:grid-cols-[1fr_2fr_auto_auto_auto] gap-3 items-end" data-item>
                    <div><label class="lbl">Step title</label><input class="input" name="steps[__i__][title]"></div>
                    <div><label class="lbl">Text</label><input class="input" name="steps[__i__][text]"></div>
                    <button type="button" class="icon-btn" data-up title="Move up">↑</button>
                    <button type="button" class="icon-btn" data-down title="Move down">↓</button>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>Not returnable</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="nope_title">Heading</label>
                    <input class="input" id="nope_title" name="nope_title" value="{{ old('nope_title', $page->nope_title) }}" required>
                    @error('nope_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="nope_text">Text</label>
                    <input class="input" id="nope_text" name="nope_text" value="{{ old('nope_text', $page->nope_text) }}" required>
                    @error('nope_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Not returnable items</h3></div>
            <div class="card-b space-y-3" data-repeat="nope_items">
                @foreach (old('nope_items', $page->nope_items ?? []) as $i => $row)
                    <div class="flex gap-3 items-end" data-item>
                        <div class="flex-1"><input class="input" name="nope_items[{{ $i }}]" value="{{ $row }}"></div>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="nope_items"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add item</button>
            </div>
            <template id="tpl-nope_items">
                <div class="flex gap-3 items-end" data-item>
                    <div class="flex-1"><input class="input" name="nope_items[__i__]"></div>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>Help box</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="help_title">Heading</label>
                    <input class="input" id="help_title" name="help_title" value="{{ old('help_title', $page->help_title) }}" required>
                    @error('help_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="help_text">Text</label>
                    <input class="input" id="help_text" name="help_text" value="{{ old('help_text', $page->help_text) }}" required>
                    @error('help_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="savebar">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><svg class="ic ic-sm"><use href="#i-check" /></svg>Save returns &amp; refunds</button>
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
