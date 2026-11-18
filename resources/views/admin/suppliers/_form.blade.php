@php
    $isActive = session()->hasOldInput() ? (bool) old('is_active') : ($supplier?->is_active ?? true);
@endphp

<div class="grid lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">

        <section class="card">
            <div class="card-h"><h3>Supplier details</h3></div>
            <div class="card-b">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="lbl" for="name">Supplier / company name <span class="text-danger">*</span></label>
                        <input class="input" id="name" name="name" type="text" value="{{ old('name', $supplier?->name) }}" required>
                        @error('name')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="contact_person">Contact person</label>
                        <input class="input" id="contact_person" name="contact_person" type="text" value="{{ old('contact_person', $supplier?->contact_person) }}">
                        @error('contact_person')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="lbl" for="phone">Phone</label>
                        <input class="input" id="phone" name="phone" type="text" value="{{ old('phone', $supplier?->phone) }}">
                        @error('phone')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="lbl" for="email">Email</label>
                        <input class="input" id="email" name="email" type="email" value="{{ old('email', $supplier?->email) }}">
                        @error('email')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="lbl" for="address">Address</label>
                        <textarea class="textarea" id="address" name="address" rows="2">{{ old('address', $supplier?->address) }}</textarea>
                        @error('address')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="lbl" for="notes">Internal notes</label>
                        <textarea class="textarea" id="notes" name="notes" rows="3">{{ old('notes', $supplier?->notes) }}</textarea>
                        @error('notes')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

    </div>

    <div class="space-y-6">

        <section class="card">
            <div class="card-h"><h3>Status</h3></div>
            <div class="card-b">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold">Active</p>
                        <p class="text-xs text-ink/50">Inactive suppliers stay on old products</p>
                    </div>
                    <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($isActive)><i></i></label>
                </div>
            </div>
        </section>

        @if ($supplier)
            <section class="card">
                <div class="card-h"><h3>Products from this supplier</h3></div>
                <div class="card-b">
                    @forelse ($products as $item)
                        <p class="py-1.5 border-b border-line last:border-0">
                            <a href="{{ route('admin.products.edit', $item) }}" class="font-semibold hover:text-primary">{{ $item->name }}</a>
                            @if ($item->sku)<span class="text-xs text-ink/50"> · {{ $item->sku }}</span>@endif
                        </p>
                    @empty
                        <p class="text-ink/55">No products linked yet.</p>
                    @endforelse
                    @if ($productCount > $products->count())
                        <a href="{{ route('admin.products.index', ['supplier' => $supplier->id]) }}" class="inline-block mt-3 text-sm font-bold text-primary">View all {{ $productCount }} products →</a>
                    @endif
                </div>
            </section>
        @endif

    </div>
</div>

<div class="savebar mt-6">
    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-ghost">Cancel</a>
    <button type="submit" class="btn btn-primary">
        <svg class="ic ic-sm"><use href="#i-check" /></svg>Save supplier
    </button>
</div>
