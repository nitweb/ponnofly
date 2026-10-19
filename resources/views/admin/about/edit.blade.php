@extends('admin.dashboard.dashboard')
@section('admin_title', 'About page · Nebula Mart Admin')
@section('admin_contents')

    @php
        $stats = old('stats', $about->stats ?? []);
        $points = old('story_points', $about->story_points ?? []);
        $values = old('values', $about->values ?? []);
        $steps = old('steps', $about->steps ?? []);
        $gallery = $about->gallery ?? [];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">About page</h1>
            <p class="text-ink/55 mt-1">Edit what customers see on the storefront About us page.</p>
        </div>
        <a href="{{ route('about.show') }}" target="_blank" class="btn btn-ghost">View page</a>
    </div>

    <form method="POST" action="{{ route('admin.about.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="card">
            <div class="card-h"><h3>Hero</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="lbl" for="hero_title">Heading</label>
                    <input class="input" id="hero_title" name="hero_title" value="{{ old('hero_title', $about->hero_title) }}" required>
                    @error('hero_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="lbl" for="hero_subtitle">Sub heading</label>
                    <textarea class="textarea" id="hero_subtitle" name="hero_subtitle" rows="2" required>{{ old('hero_subtitle', $about->hero_subtitle) }}</textarea>
                    @error('hero_subtitle')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="hero_caption">Photo caption</label>
                    <input class="input" id="hero_caption" name="hero_caption" value="{{ old('hero_caption', $about->hero_caption) }}">
                    @error('hero_caption')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl">Photo (1000×800)</label>
                    <label class="drop block">
                        <input type="file" name="hero_image" class="hidden" accept="image/*" data-preview="#img-hero">
                        <img id="img-hero" src="{{ $about->imageUrl($about->hero_image) }}" class="w-full max-h-40 object-cover rounded-2xl mb-2" alt="">
                        <p class="text-xs text-ink/50">Click to replace · auto converted to WEBP</p>
                    </label>
                    @error('hero_image')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Numbers</h3></div>
            <div class="card-b space-y-3" data-repeat="stats">
                @foreach ($stats as $i => $row)
                    <div class="grid sm:grid-cols-[120px_100px_1fr_auto] gap-3 items-end" data-item>
                        <div><label class="lbl">Number</label><input class="input" type="number" min="0" name="stats[{{ $i }}][value]" value="{{ $row['value'] ?? '' }}"></div>
                        <div><label class="lbl">Suffix</label><input class="input" maxlength="5" name="stats[{{ $i }}][suffix]" value="{{ $row['suffix'] ?? '' }}" placeholder="K+"></div>
                        <div><label class="lbl">Label</label><input class="input" name="stats[{{ $i }}][label]" value="{{ $row['label'] ?? '' }}"></div>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="stats"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add number</button>
                @if ($errors->has('stats*'))<p class="text-danger text-xs">Check the numbers above.</p>@endif
            </div>
            <template id="tpl-stats">
                <div class="grid sm:grid-cols-[120px_100px_1fr_auto] gap-3 items-end" data-item>
                    <div><label class="lbl">Number</label><input class="input" type="number" min="0" name="stats[__i__][value]"></div>
                    <div><label class="lbl">Suffix</label><input class="input" maxlength="5" name="stats[__i__][suffix]" placeholder="K+"></div>
                    <div><label class="lbl">Label</label><input class="input" name="stats[__i__][label]"></div>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>Who we are</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="lbl" for="story_title">Heading</label>
                    <input class="input" id="story_title" name="story_title" value="{{ old('story_title', $about->story_title) }}" required>
                    @error('story_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="lbl" for="story_text">Text <span class="text-ink/45 font-normal">(leave a blank line between paragraphs)</span></label>
                    <textarea class="textarea" id="story_text" name="story_text" rows="6" required>{{ old('story_text', $about->story_text) }}</textarea>
                    @error('story_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2 space-y-3" data-repeat="story_points">
                    <label class="lbl">Check list</label>
                    @foreach ($points as $i => $point)
                        <div class="flex gap-3" data-item>
                            <input class="input" name="story_points[{{ $i }}]" value="{{ $point }}">
                            <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                        </div>
                    @endforeach
                    <button type="button" class="btn btn-ghost" data-add="story_points"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add point</button>
                    <template id="tpl-story_points">
                        <div class="flex gap-3" data-item>
                            <input class="input" name="story_points[__i__]">
                            <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                        </div>
                    </template>
                </div>
                <div>
                    <label class="lbl" for="story_tag_number">Badge number</label>
                    <input class="input" id="story_tag_number" name="story_tag_number" value="{{ old('story_tag_number', $about->story_tag_number) }}">
                </div>
                <div>
                    <label class="lbl" for="story_tag_text">Badge text</label>
                    <input class="input" id="story_tag_text" name="story_tag_text" value="{{ old('story_tag_text', $about->story_tag_text) }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="lbl">Photo (900×1100)</label>
                    <label class="drop block">
                        <input type="file" name="story_image" class="hidden" accept="image/*" data-preview="#img-story">
                        <img id="img-story" src="{{ $about->imageUrl($about->story_image) }}" class="w-full max-h-48 object-cover rounded-2xl mb-2" alt="">
                        <p class="text-xs text-ink/50">Click to replace · auto converted to WEBP</p>
                    </label>
                    @error('story_image')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Mission and vision</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="mission_text">Our mission</label>
                    <textarea class="textarea" id="mission_text" name="mission_text" rows="4" required>{{ old('mission_text', $about->mission_text) }}</textarea>
                    @error('mission_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="vision_text">Our vision</label>
                    <textarea class="textarea" id="vision_text" name="vision_text" rows="4" required>{{ old('vision_text', $about->vision_text) }}</textarea>
                    @error('vision_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Values</h3></div>
            <div class="card-b space-y-4" data-repeat="values">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="lbl" for="values_title">Heading</label>
                        <input class="input" id="values_title" name="values_title" value="{{ old('values_title', $about->values_title) }}" required>
                        @error('values_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="values_subtitle">Sub heading</label>
                        <input class="input" id="values_subtitle" name="values_subtitle" value="{{ old('values_subtitle', $about->values_subtitle) }}">
                    </div>
                </div>
                @foreach ($values as $i => $row)
                    <div class="grid sm:grid-cols-[150px_1fr_2fr_auto] gap-3 items-end" data-item>
                        <div><label class="lbl">Icon</label>
                            <select class="select" name="values[{{ $i }}][icon]">
                                @foreach (\App\Models\AboutPage::ICONS as $key => $label)
                                    <option value="{{ $key }}" @selected(($row['icon'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select></div>
                        <div><label class="lbl">Title</label><input class="input" name="values[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                        <div><label class="lbl">Text</label><input class="input" name="values[{{ $i }}][text]" value="{{ $row['text'] ?? '' }}"></div>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="values"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add value</button>
            </div>
            <template id="tpl-values">
                <div class="grid sm:grid-cols-[150px_1fr_2fr_auto] gap-3 items-end" data-item>
                    <div><label class="lbl">Icon</label>
                        <select class="select" name="values[__i__][icon]">
                            @foreach (\App\Models\AboutPage::ICONS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select></div>
                    <div><label class="lbl">Title</label><input class="input" name="values[__i__][title]"></div>
                    <div><label class="lbl">Text</label><input class="input" name="values[__i__][text]"></div>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>How an order reaches you</h3></div>
            <div class="card-b space-y-4" data-repeat="steps">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="lbl" for="process_title">Heading</label>
                        <input class="input" id="process_title" name="process_title" value="{{ old('process_title', $about->process_title) }}" required>
                        @error('process_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="process_subtitle">Sub heading</label>
                        <input class="input" id="process_subtitle" name="process_subtitle" value="{{ old('process_subtitle', $about->process_subtitle) }}">
                    </div>
                </div>
                @foreach ($steps as $i => $row)
                    <div class="grid sm:grid-cols-[1fr_2fr_auto] gap-3 items-end" data-item>
                        <div><label class="lbl">Step title</label><input class="input" name="steps[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                        <div><label class="lbl">Text</label><input class="input" name="steps[{{ $i }}][text]" value="{{ $row['text'] ?? '' }}"></div>
                        <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                    </div>
                @endforeach
                <button type="button" class="btn btn-ghost" data-add="steps"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add step</button>
            </div>
            <template id="tpl-steps">
                <div class="grid sm:grid-cols-[1fr_2fr_auto] gap-3 items-end" data-item>
                    <div><label class="lbl">Step title</label><input class="input" name="steps[__i__][title]"></div>
                    <div><label class="lbl">Text</label><input class="input" name="steps[__i__][text]"></div>
                    <button type="button" class="icon-btn danger" data-remove title="Remove"><svg class="ic"><use href="#i-trash" /></svg></button>
                </div>
            </template>
        </section>

        <section class="card">
            <div class="card-h"><h3>Gallery</h3></div>
            <div class="card-b space-y-5">
                <div>
                    <label class="lbl" for="gallery_title">Heading</label>
                    <input class="input" id="gallery_title" name="gallery_title" value="{{ old('gallery_title', $about->gallery_title) }}" required>
                    @error('gallery_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid sm:grid-cols-3 gap-5">
                    @foreach ([0, 1, 2] as $i)
                        <div>
                            <label class="drop block">
                                <input type="file" name="gallery_images[{{ $i }}]" class="hidden" accept="image/*" data-preview="#img-gal-{{ $i }}">
                                <img id="img-gal-{{ $i }}" src="{{ $about->imageUrl($gallery[$i]['image'] ?? null) }}" class="w-full h-32 object-cover rounded-2xl mb-2" alt="">
                                <p class="text-xs text-ink/50">{{ $i === 0 ? '900×600' : '600×600' }} · click to replace</p>
                            </label>
                            <input class="input mt-2" name="gallery_alt[{{ $i }}]" value="{{ old('gallery_alt.'.$i, $gallery[$i]['alt'] ?? '') }}" placeholder="Image description">
                            @error('gallery_images.'.$i)<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Bottom banner</h3></div>
            <div class="card-b grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="lbl" for="cta_title">Heading</label>
                    <input class="input" id="cta_title" name="cta_title" value="{{ old('cta_title', $about->cta_title) }}" required>
                    @error('cta_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="lbl" for="cta_text">Text</label>
                    <input class="input" id="cta_text" name="cta_text" value="{{ old('cta_text', $about->cta_text) }}" required>
                    @error('cta_text')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <div class="savebar">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary"><svg class="ic ic-sm"><use href="#i-check" /></svg>Save about page</button>
        </div>
    </form>

    <script>
        (function () {
            [].forEach.call(document.querySelectorAll('[data-add]'), function (btn) {
                var key = btn.getAttribute('data-add');
                var box = btn.closest('[data-repeat]');
                var tpl = document.getElementById('tpl-' + key);
                var next = box.querySelectorAll('[data-item]').length + 100;
                btn.addEventListener('click', function () {
                    var wrap = document.createElement('div');
                    wrap.innerHTML = tpl.innerHTML.replace(/__i__/g, next++).trim();
                    box.insertBefore(wrap.firstElementChild, btn);
                });
            });
            document.addEventListener('click', function (e) {
                var rm = e.target.closest('[data-remove]');
                if (rm) rm.closest('[data-item]').remove();
            });
        })();
    </script>

@endsection
