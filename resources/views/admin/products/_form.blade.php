@php
    $val = fn (string $key, $default = null) => old($key, $product?->{$key} ?? $default);
    $flag = fn (string $key, bool $default) => session()->hasOldInput() ? (bool) old($key) : ($product?->{$key} ?? $default);
    $variantRows = session()->hasOldInput()
        ? collect(old('variants', []))->values()->all()
        : ($product ? $product->variants->map(fn ($v) => ['id' => $v->id, 'size' => $v->size, 'color' => $v->color, 'sku' => $v->sku, 'price' => $v->price, 'stock' => $v->stock])->all() : []);
    $removed = array_map('intval', old('remove_images', []));
@endphp

<div class="grid xl:grid-cols-3 gap-6 items-start">
    <div class="space-y-6 xl:col-span-2">

        <section class="card">
            <div class="card-h"><h3>Basic information</h3></div>
            <div class="card-b">
                <div class="grid gap-5">
                    <div>
                        <label class="lbl" for="name">Product name <span class="text-danger">*</span></label>
                        <input class="input" id="name" name="name" type="text" value="{{ $val('name') }}" placeholder="e.g. Premium Cotton Panjabi" required>
                        @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="lbl" for="slug">Slug (URL)</label>
                            <input class="input" id="slug" name="slug" type="text" value="{{ $val('slug') }}" placeholder="auto-generated" @if (! $product) data-slug-from="#name" @endif>
                            @error('slug')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="lbl" for="model">Model</label>
                            <input class="input" id="model" name="model" type="text" value="{{ $val('model') }}" placeholder="e.g. HEM-7156T">
                            @error('model')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="lbl" for="short_description">Short description</label>
                        <textarea class="textarea" id="short_description" name="short_description" rows="2">{{ $val('short_description') }}</textarea>
                        <p class="hint">Shown on product cards and search results.</p>
                        @error('short_description')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="description-editor">Full description</label>
                        @php
                            $rawDescription = (string) $val('description');
                            $editorHtml = \App\Support\HtmlSanitizer::looksLikeHtml($rawDescription)
                                ? \App\Support\HtmlSanitizer::clean($rawDescription)
                                : collect(preg_split('/\R{2,}/', trim($rawDescription)))->filter(fn ($p) => trim($p) !== '')->map(fn ($p) => '<p>'.nl2br(e(trim($p))).'</p>')->implode('');
                        @endphp
                        <textarea id="description" name="description" hidden>{{ $editorHtml }}</textarea>
                        <div id="description-editor" class="bg-white">{!! $editorHtml !!}</div>
                        <p class="hint">Format the description your own way: headings, bold, colors, lists, alignment, links and images (by URL).</p>
                        @error('description')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Product media</h3></div>
            <div class="card-b">
                @if ($product && $product->images->isNotEmpty())
                    <p class="font-semibold mb-3">Current images</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(7.5rem,1fr));gap:.75rem" class="mb-6">
                        @foreach ($product->images as $image)
                            <div class="border border-line rounded-2xl p-2 bg-white" data-existing>
                                <img src="{{ $image->url }}" alt="" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:.75rem">
                                <label class="flex items-center gap-2 text-xs mt-2 cursor-pointer">
                                    <input type="radio" name="cover_image" value="{{ $image->id }}" @checked((string) old('cover_image', $product->images->first()->id) === (string) $image->id)> Cover
                                </label>
                                <label class="flex items-center gap-2 text-xs mt-1 cursor-pointer text-danger">
                                    <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" @checked(in_array($image->id, $removed, true))> Remove
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif

                <label class="drop block" for="images">
                    <input type="file" class="hidden" id="images" name="images[]" multiple accept="image/*">
                    <span class="w-14 h-14 rounded-2xl bg-white text-primary grid place-items-center mx-auto shadow"><svg class="ic ic-lg"><use href="#i-upload" /></svg></span>
                    <p class="font-bold mt-3">Click to upload or drag & drop</p>
                    <p class="text-xs text-ink/50 mt-1">WEBP, PNG or JPG · up to 5 MB each · saved as 800×800 webp · first image is the cover</p>
                </label>
                <div id="new-gallery" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(7.5rem,1fr));gap:.75rem" class="mt-4"></div>
                @error('images')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                @error('images.*')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Pricing</h3></div>
            <div class="card-b">
                <div class="grid sm:grid-cols-3 gap-5">
                    <div>
                        <label class="lbl" for="price">Selling price <span class="text-danger">*</span></label>
                        <div class="addon"><span>৳</span><input class="input" id="price" name="price" type="number" step="0.01" min="0" value="{{ $val('price') }}" placeholder="0" required></div>
                        @error('price')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="compare_price">Compare-at price</label>
                        <div class="addon"><span>৳</span><input class="input" id="compare_price" name="compare_price" type="number" step="0.01" min="0" value="{{ $val('compare_price') }}" placeholder="0"></div>
                        <p class="hint">Shown as strikethrough</p>
                        @error('compare_price')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="cost_price">Cost price</label>
                        <div class="addon"><span>৳</span><input class="input" id="cost_price" name="cost_price" type="number" step="0.01" min="0" value="{{ $val('cost_price') }}" placeholder="0"></div>
                        <p class="hint">Hidden from customers</p>
                        @error('cost_price')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Inventory</h3></div>
            <div class="card-b">
                <div class="grid sm:grid-cols-3 gap-5">
                    <div>
                        <label class="lbl" for="sku">SKU</label>
                        <input class="input" id="sku" name="sku" type="text" value="{{ $val('sku') }}" placeholder="NM-0000">
                        @error('sku')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="barcode">Barcode</label>
                        <input class="input" id="barcode" name="barcode" type="text" value="{{ $val('barcode') }}">
                        @error('barcode')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="stock">Stock quantity</label>
                        <input class="input" id="stock" name="stock" type="number" min="0" value="{{ $val('stock', 0) }}">
                        <p class="hint">Ignored when variants are added below.</p>
                        @error('stock')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-5 mt-5">
                    <div>
                        <label class="lbl" for="low_stock">Low-stock alert at</label>
                        <input class="input" id="low_stock" name="low_stock" type="number" min="0" value="{{ $val('low_stock', 10) }}">
                        <p class="hint">Marked “Low stock” when stock falls to this number</p>
                        @error('low_stock')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="weight">Weight (kg)</label>
                        <input class="input" id="weight" name="weight" type="number" step="0.001" min="0" value="{{ $val('weight') }}">
                        @error('weight')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="mt-5 pt-5 border-t border-line">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold">Track inventory</p>
                            <p class="text-xs text-ink/50">Reduce stock automatically when orders are placed</p>
                        </div>
                        <label class="switch"><input type="checkbox" name="track_inventory" value="1" @checked($flag('track_inventory', true))><i></i></label>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Variants</h3></div>
            <div class="card-b">
                <p class="hint mb-4">Optional. Each row is one size/colour combination with its own stock, and optionally its own SKU and price. Leave empty for a simple product.</p>
                <div class="overflow-x-auto">
                    <table class="tbl" id="variant-table">
                        <thead>
                            <tr><th>Size</th><th>Color</th><th>SKU</th><th>Price (৳)</th><th>Stock</th><th></th></tr>
                        </thead>
                        <tbody id="variant-rows">
                            @foreach ($variantRows as $i => $row)
                                @include('admin.products._variant_row', ['i' => $i, 'row' => $row])
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-ghost btn-sm mt-4" id="add-variant">
                    <svg class="ic ic-sm"><use href="#i-plus" /></svg>Add variant
                </button>
                @error('variants.*')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                <template id="variant-template">
                    @include('admin.products._variant_row', ['i' => '__INDEX__', 'row' => []])
                </template>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Search engine listing</h3></div>
            <div class="card-b">
                <div class="grid gap-5">
                    <div>
                        <label class="lbl" for="meta_title">Meta title</label>
                        <input class="input" id="meta_title" name="meta_title" type="text" value="{{ $val('meta_title') }}">
                        @error('meta_title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="meta_description">Meta description</label>
                        <textarea class="textarea" id="meta_description" name="meta_description" rows="2">{{ $val('meta_description') }}</textarea>
                        @error('meta_description')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="meta_keywords">Meta keywords</label>
                        <input class="input" id="meta_keywords" name="meta_keywords" type="text" value="{{ $val('meta_keywords') }}" placeholder="panjabi, cotton panjabi, eid collection">
                        <p class="hint">Separate keywords with commas.</p>
                        @error('meta_keywords')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="space-y-6">

        <section class="card">
            <div class="card-h"><h3>Publish</h3></div>
            <div class="card-b">
                <div class="space-y-5">
                    <div>
                        <label class="lbl" for="status">Status</label>
                        <select class="select" id="status" name="status">
                            <option value="active" @selected($val('status', 'active') === 'active')>Active</option>
                            <option value="draft" @selected($val('status', 'active') === 'draft')>Draft</option>
                        </select>
                        @error('status')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Featured product</p><p class="text-xs text-ink/50">Show on homepage</p></div>
                        <label class="switch"><input type="checkbox" name="is_featured" value="1" @checked($flag('is_featured', false))><i></i></label>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">New arrival</p></div>
                        <label class="switch"><input type="checkbox" name="is_new" value="1" @checked($flag('is_new', true))><i></i></label>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Organization</h3></div>
            <div class="card-b">
                <div class="space-y-5">
                    <div>
                        <label class="lbl" for="category_id">Category</label>
                        <select class="select" id="category_id" name="category_id">
                            <option value="">— None —</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) $val('category_id') === (string) $category->id)>{{ str_repeat('— ', $category->depth) }}{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="brand_id">Brand</label>
                        <select class="select" id="brand_id" name="brand_id">
                            <option value="">— None —</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected((string) $val('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="tags">Tags</label>
                        <input class="input" id="tags" name="tags" type="text" value="{{ $val('tags') }}" placeholder="eid, cotton, premium">
                        <p class="hint">Comma separated</p>
                        @error('tags')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-h"><h3>Delivery</h3></div>
            <div class="card-b">
                <div class="space-y-5">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Cash on delivery</p></div>
                        <label class="switch"><input type="checkbox" name="is_cod" value="1" @checked($flag('is_cod', true))><i></i></label>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Free delivery</p><p class="text-xs text-ink/50">Over ৳5,000 handled in settings</p></div>
                        <label class="switch"><input type="checkbox" name="is_free_delivery" value="1" @checked($flag('is_free_delivery', false))><i></i></label>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="font-semibold">Returnable</p><p class="text-xs text-ink/50">7-day return window</p></div>
                        <label class="switch"><input type="checkbox" name="is_returnable" value="1" @checked($flag('is_returnable', true))><i></i></label>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="savebar mt-6">
    <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <svg class="ic ic-sm"><use href="#i-check" /></svg>{{ $product ? 'Update product' : 'Publish product' }}
    </button>
</div>

<script>
    (function () {
        var rows = document.getElementById('variant-rows');
        var tpl = document.getElementById('variant-template');
        var next = Date.now();

        document.getElementById('add-variant').addEventListener('click', function () {
            rows.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, next++));
        });

        rows.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove-variant]');
            if (btn) btn.closest('tr').remove();
        });

        var input = document.getElementById('images');
        var gallery = document.getElementById('new-gallery');
        var files = [];

        function sync() {
            var dt = new DataTransfer();
            files.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
            gallery.innerHTML = '';
            files.forEach(function (f, i) {
                var wrap = document.createElement('div');
                wrap.className = 'border border-line rounded-2xl p-2 bg-white';
                var img = document.createElement('img');
                img.style.cssText = 'width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:.75rem';
                img.src = URL.createObjectURL(f);
                var rm = document.createElement('button');
                rm.type = 'button';
                rm.className = 'text-xs text-danger mt-2';
                rm.textContent = 'Remove';
                rm.onclick = function () { files.splice(i, 1); sync(); };
                wrap.appendChild(img);
                wrap.appendChild(rm);
                gallery.appendChild(wrap);
            });
        }

        input.addEventListener('change', function () {
            Array.prototype.forEach.call(input.files, function (f) { files.push(f); });
            sync();
        });
    })();
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<style>
    #description-editor { font-size: 15px; }
    .ql-container.ql-snow { height: auto; }
    .ql-editor { min-height: 260px; max-height: 520px; overflow-y: auto; }
    .ql-toolbar.ql-snow { border-top-left-radius: .75rem; border-top-right-radius: .75rem; }
    .ql-container.ql-snow { border-bottom-left-radius: .75rem; border-bottom-right-radius: .75rem; }
</style>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    (function () {
        var holder = document.getElementById('description-editor');
        var field = document.getElementById('description');
        if (!holder || !field || typeof Quill === 'undefined') { if (field) field.hidden = false; return; }

        var quill = new Quill(holder, {
            theme: 'snow',
            placeholder: 'Write the product description…',
            modules: {
                toolbar: {
                    container: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ color: [] }, { background: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ align: [] }],
                        ['blockquote', 'link', 'image'],
                        ['clean']
                    ],
                    handlers: {
                        image: function () {
                            var url = window.prompt('Image URL (https://…)');
                            if (!url) return;
                            var range = this.quill.getSelection(true);
                            this.quill.insertEmbed(range.index, 'image', url, 'user');
                        }
                    }
                }
            }
        });

        function sync() {
            var empty = quill.getText().trim() === '' && !quill.root.querySelector('img');
            field.value = empty ? '' : quill.getSemanticHTML();
        }

        quill.on('text-change', sync);
        field.form.addEventListener('submit', sync);
    })();
</script>
