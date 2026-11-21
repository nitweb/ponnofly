@php
    $selectedParent = old('parent_id', $category?->parent_id ?? request('parent'));
    $isActive = session()->hasOldInput() ? (bool) old('is_active') : ($category?->is_active ?? true);
    $showOnHome = session()->hasOldInput() ? (bool) old('show_on_home') : ($category?->show_on_home ?? false);
    $showInMenu = session()->hasOldInput() ? (bool) old('show_in_menu') : ($category?->show_in_menu ?? false);
    $promo = fn (int $index, string $key) => old("promos.{$index}.{$key}", data_get($category?->promos, "{$index}.{$key}", ''));
@endphp

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">

        <section class="card">
            <div class="card-h"><h3>Category details</h3></div>
            <div class="card-b">
                <div class="grid gap-5">
                    <div>
                        <label class="lbl" for="name">Category name <span class="text-danger">*</span></label>
                        <input class="input" id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" placeholder="e.g. Fashion" required>
                        @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="slug">Slug</label>
                        <input class="input" id="slug" name="slug" type="text" value="{{ old('slug', $category?->slug) }}" placeholder="auto-generated from the name" @if (! $category) data-slug-from="#name" @endif>
                        @error('slug')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="parent_id">Parent category</label>
                        <select class="select" id="parent_id" name="parent_id">
                            <option value="">None (top level)</option>
                            @foreach ($parentOptions as $option)
                                <option value="{{ $option->id }}" @selected((string) $selectedParent === (string) $option->id)>{{ str_repeat('— ', $option->depth) }}{{ $option->name }}</option>
                            @endforeach
                        </select>
                        <p class="hint mt-1">Top-level category with sub-categories shows a mega menu. Without sub-categories it is a plain link.</p>
                        @error('parent_id')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="description">Description</label>
                        <textarea class="textarea" id="description" name="description" rows="3">{{ old('description', $category?->description) }}</textarea>
                        @error('description')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>SEO</h3></div>
            <div class="card-b">
                <div class="grid gap-5">
                    <div>
                        <label class="lbl" for="meta_title">Meta title</label>
                        <input class="input" id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $category?->meta_title) }}">
                        @error('meta_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="meta_description">Meta description</label>
                        <textarea class="textarea" id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $category?->meta_description) }}</textarea>
                        @error('meta_description')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Mega menu promo cards</h3></div>
            <div class="card-b">
                <p class="hint mb-5">Optional. Shown on the right side of the mega menu when this is a top-level category with sub-categories. A card without a title is ignored.</p>
                <div class="grid sm:grid-cols-2 gap-6">
                    @foreach ([0 => 'Card 1', 1 => 'Card 2'] as $index => $label)
                        <div class="space-y-4">
                            <p class="font-bold">{{ $label }}</p>
                            <div>
                                <label class="lbl" for="promo_{{ $index }}_badge">Badge</label>
                                <input class="input" id="promo_{{ $index }}_badge" name="promos[{{ $index }}][badge]" type="text" value="{{ $promo($index, 'badge') }}" placeholder="New season">
                            </div>
                            <div>
                                <label class="lbl" for="promo_{{ $index }}_title">Title</label>
                                <input class="input" id="promo_{{ $index }}_title" name="promos[{{ $index }}][title]" type="text" value="{{ $promo($index, 'title') }}" placeholder="Eid-ready outfits, up to 40% off">
                            </div>
                            <div>
                                <label class="lbl" for="promo_{{ $index }}_cta">Button text</label>
                                <input class="input" id="promo_{{ $index }}_cta" name="promos[{{ $index }}][cta]" type="text" value="{{ $promo($index, 'cta') }}" placeholder="Shop now">
                            </div>
                            <div>
                                <label class="lbl" for="promo_{{ $index }}_color">Card color</label>
                                <select class="select" id="promo_{{ $index }}_color" name="promos[{{ $index }}][color]">
                                    <option value="navy" @selected($promo($index, 'color') === 'navy' || ($promo($index, 'color') === '' && $index === 0))>Navy</option>
                                    <option value="yellow" @selected($promo($index, 'color') === 'yellow' || ($promo($index, 'color') === '' && $index === 1))>Yellow</option>
                                </select>
                            </div>
                            <div>
                                <label class="lbl" for="promo_{{ $index }}_link">Link</label>
                                <input class="input" id="promo_{{ $index }}_link" name="promos[{{ $index }}][link]" type="text" value="{{ $promo($index, 'link') }}" placeholder="Leave empty to open this category">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

    </div>

    <div class="space-y-6">

        <section class="card">
            <div class="card-h"><h3>Category image</h3></div>
            <div class="card-b">
                <div class="space-y-5">
                    <div>
                        <label class="lbl">Category image</label>
                        <div class="drop !p-5 flex flex-col sm:flex-row items-center gap-5 text-left">
                            <img id="cimg" src="{{ $category?->image_url ?? asset('assets/admin/img/no-image.svg') }}" class="w-28 h-28 rounded-2xl object-cover bg-white border border-line shrink-0" alt="">
                            <div class="text-center sm:text-left">
                                <p class="font-bold">Category image</p>
                                <p class="text-xs text-ink/50 mt-1 mb-3">Auto cropped to 400×400 · WEBP</p>
                                <div class="flex gap-2 justify-center sm:justify-start">
                                    <label class="btn btn-primary btn-sm cursor-pointer">
                                        <svg class="ic ic-sm"><use href="#i-upload" /></svg>Upload
                                        <input type="file" name="image" class="hidden" accept="image/*" data-preview="#cimg">
                                    </label>
                                    <button type="button" class="btn btn-ghost btn-sm" data-reset-img="#cimg">Remove</button>
                                </div>
                                <input type="hidden" name="remove_image" value="0" data-remove>
                            </div>
                        </div>
                        @error('image')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl">Icon (optional)</label>
                        <div class="drop !p-5 flex flex-col sm:flex-row items-center gap-5 text-left">
                            <img id="cicon" src="{{ $category?->icon_url ?? asset('assets/admin/img/no-image.svg') }}" class="w-16 h-16 rounded-xl object-contain bg-white border border-line shrink-0" alt="">
                            <div class="text-center sm:text-left">
                                <p class="font-bold">Icon (optional)</p>
                                <p class="text-xs text-ink/50 mt-1 mb-3">Small SVG, PNG or WEBP</p>
                                <div class="flex gap-2 justify-center sm:justify-start">
                                    <label class="btn btn-primary btn-sm cursor-pointer">
                                        <svg class="ic ic-sm"><use href="#i-upload" /></svg>Upload
                                        <input type="file" name="icon" class="hidden" accept="image/*" data-preview="#cicon">
                                    </label>
                                    <button type="button" class="btn btn-ghost btn-sm" data-reset-img="#cicon">Remove</button>
                                </div>
                                <input type="hidden" name="remove_icon" value="0" data-remove>
                            </div>
                        </div>
                        @error('icon')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Visibility</h3></div>
            <div class="card-b">
                <div class="space-y-5">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Active</p></div>
                        <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($isActive)><i></i></label>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold">Show on homepage slider</p>
                            <p class="text-xs text-ink/50">“Shop by category” section</p>
                        </div>
                        <label class="switch"><input type="checkbox" name="show_on_home" value="1" @checked($showOnHome)><i></i></label>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold">Show in header menu</p>
                            <p class="text-xs text-ink/50">Top-level categories only, up to {{ \App\Models\Category::NAV_LIMIT }}. Its sub-categories open in the mega menu.</p>
                        </div>
                        <label class="switch"><input type="checkbox" name="show_in_menu" value="1" @checked($showInMenu)><i></i></label>
                    </div>
                    @error('show_in_menu')<p class="text-danger text-xs -mt-3">{{ $message }}</p>@enderror
                    <div>
                        <label class="lbl" for="sort_order">Display order</label>
                        <input class="input" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category?->sort_order ?? 1) }}">
                        @error('sort_order')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<div class="savebar mt-6">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <svg class="ic ic-sm"><use href="#i-check" /></svg>Save category
    </button>
</div>

<script>
    document.addEventListener('click', function (e) {
        var button = e.target.closest('[data-reset-img]');
        if (!button) return;
        var flag = button.closest('.drop').querySelector('input[data-remove]');
        if (flag) flag.value = 1;
    });
    document.addEventListener('change', function (e) {
        if (!e.target.matches('.drop input[type=file]')) return;
        var flag = e.target.closest('.drop').querySelector('input[data-remove]');
        if (flag) flag.value = 0;
    });
</script>
