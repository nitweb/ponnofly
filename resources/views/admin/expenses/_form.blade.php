@php
    $value = fn (string $key, $default = null) => old($key, $expense?->{$key} ?? $default);
@endphp

<section class="card">
    <div class="card-h"><h3>Expense details</h3></div>
    <div class="card-b">
        <div class="grid sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="lbl" for="title">Title <span class="text-danger">*</span></label>
                <input class="input" id="title" name="title" type="text" value="{{ $value('title') }}" maxlength="255" required>
                @error('title')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="amount">Amount <span class="text-danger">*</span></label>
                <div class="addon"><span>৳</span><input class="input" id="amount" name="amount" type="number" step="0.01" min="0" value="{{ $value('amount') }}" required></div>
                @error('amount')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="expense_date">Date <span class="text-danger">*</span></label>
                <input class="input" id="expense_date" name="expense_date" type="date" value="{{ old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                @error('expense_date')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="expense_category_id">Category</label>
                <select class="select" id="expense_category_id" name="expense_category_id">
                    <option value="">Uncategorized</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $value('expense_category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('expense_category_id')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lbl" for="payment_method">Paid with</label>
                <select class="select" id="payment_method" name="payment_method">
                    @foreach ($methods as $key => $label)
                        <option value="{{ $key }}" @selected($value('payment_method', 'cash') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="lbl" for="reference">Reference / invoice no.</label>
                <input class="input" id="reference" name="reference" type="text" value="{{ $value('reference') }}" maxlength="80">
                @error('reference')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="lbl" for="note">Note</label>
                <textarea class="input" id="note" name="note" rows="3" maxlength="1000">{{ $value('note') }}</textarea>
                @error('note')<p class="text-danger text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</section>

<div class="flex gap-3 mt-6">
    <button class="btn btn-primary" type="submit">Save expense</button>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-ghost">Cancel</a>
</div>
