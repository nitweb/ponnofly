@extends('admin.dashboard.dashboard')
@section('admin_title', 'FAQs · Nebula Mart Admin')
@section('admin_contents')

    @php
        $badge = ['delivery' => 'b-info', 'payments' => 'b-purple', 'returns' => 'b-warn', 'orders' => 'b-success'];
        $editing = old('faq_id');
        $categoryChoice = old('category', 'orders');
        $faqPayload = $faqs->mapWithKeys(fn ($faq) => [$faq->id => [
            'question' => $faq->question,
            'category' => $faq->category,
            'answer' => $faq->answer,
            'active' => $faq->is_active,
            'url' => route('admin.faqs.update', $faq),
        ]]);
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-[28px] tracking-tight">FAQs</h1>
            <p class="text-ink/55 mt-1">Answers shown on the storefront FAQ page. Drag to reorder.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" class="btn btn-primary" id="faq-add"><svg class="ic ic-sm"><use href="#i-plus" /></svg>Add FAQ</button>
        </div>
    </div>

    <ul class="space-y-4" id="faq-list">
        @forelse ($faqs as $faq)
            <li class="card p-5 flex items-start gap-4" data-row data-id="{{ $faq->id }}" draggable="true">
                <span class="mt-1 cursor-grab text-ink/30"><svg class="ic"><use href="#i-grid" /></svg></span>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <b>{{ $faq->question }}</b>
                        <span class="badge {{ $badge[$faq->category] ?? 'b-info' }}">{{ $faq->category_label }}</span>
                        @unless ($faq->is_active)<span class="badge b-muted">Hidden</span>@endunless
                    </div>
                    <p class="text-ink/60 mt-1.5 whitespace-pre-line">{{ $faq->answer }}</p>
                </div>
                <form method="POST" action="{{ route('admin.faqs.toggle', $faq) }}">
                    @csrf
                    @method('PATCH')
                    <label class="switch"><input type="checkbox" @checked($faq->is_active) onchange="this.form.submit()" aria-label="Published"><i></i></label>
                </form>
                <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="flex gap-1">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="icon-btn" data-edit="{{ $faq->id }}" title="Edit"><svg class="ic"><use href="#i-edit" /></svg></button>
                    <button type="button" class="icon-btn danger" data-delete="this FAQ" title="Delete"><svg class="ic"><use href="#i-trash" /></svg></button>
                </form>
            </li>
        @empty
            <li class="card p-8 text-center text-ink/55">No FAQs yet. Add your first one.</li>
        @endforelse
    </ul>

    <div id="faq-modal" class="modal">
        <div class="!max-w-xl !p-0 overflow-hidden">
            <form method="POST" action="{{ route('admin.faqs.store') }}" id="faq-form">
                @csrf
                <input type="hidden" name="_method" value="POST" id="faq-method">
                <input type="hidden" name="faq_id" value="{{ $editing }}" id="faq-id">
                <div class="is-12 relative px-8 pt-8 pb-14 text-white">
                    <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-accent/30 blur-2xl"></div>
                    <button type="button" class="absolute right-5 top-5 w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 grid place-items-center transition" data-close aria-label="Close"><svg class="ic"><use href="#i-x" /></svg></button>
                    <span class="w-14 h-14 rounded-2xl bg-gradient-to-br from-accent to-amber-300 text-[#1b1300] grid place-items-center shadow-lg"><svg class="ic ic-lg"><use href="#i-help" /></svg></span>
                    <h3 class="font-display font-extrabold text-2xl mt-4" id="faq-title">Add a FAQ</h3>
                    <p class="text-white/70 text-sm mt-1">Answer a question your customers ask often.</p>
                </div>
                <div class="px-8 pb-8 -mt-6 relative">
                    <div class="bg-white rounded-2xl border border-line shadow-lg p-6 space-y-5">
                        <div>
                            <label class="lbl" for="q">Question <span class="text-danger">*</span></label>
                            <div class="input-group"><svg class="ic"><use href="#i-help" /></svg><input class="input" id="q" name="question" type="text" value="{{ old('question') }}" placeholder="e.g. How does Cash on Delivery work?" maxlength="255" required></div>
                            @error('question')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="lbl">Category</label>
                            <div class="flex flex-wrap gap-2" id="faq-cats">
                                @foreach (\App\Models\Faq::CATEGORIES as $key => $label)
                                    <label class="cursor-pointer"><input type="radio" name="category" value="{{ $key }}" class="peer sr-only" @checked($categoryChoice === $key)><span class="inline-flex items-center px-4 py-2 rounded-xl border border-line font-semibold text-sm text-ink/70 transition peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary peer-checked:shadow-md hover:border-primary">{{ $label }}</span></label>
                                @endforeach
                            </div>
                            @error('category')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="lbl" for="a">Answer <span class="text-danger">*</span></label>
                            <textarea class="textarea" id="a" name="answer" rows="4" maxlength="2000" placeholder="Write a clear, short answer…" required>{{ old('answer') }}</textarea>
                            @error('answer')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-bg px-4 py-3">
                            <p class="font-semibold">Published</p>
                            <input type="hidden" name="is_active" value="0">
                            <label class="switch"><input type="checkbox" name="is_active" value="1" id="faq-active" @checked(old('is_active', '1') == '1')><i></i></label>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" class="btn btn-ghost flex-1 !h-12" data-close>Cancel</button>
                        <button class="btn btn-primary flex-1 !h-12" type="submit"><svg class="ic ic-sm"><use href="#i-check" /></svg>Save FAQ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var faqs = @json($faqPayload);
            var storeUrl = @json(route('admin.faqs.store'));
            var modal = document.getElementById('faq-modal');
            var form = document.getElementById('faq-form');
            var title = document.getElementById('faq-title');

            function openForm(id) {
                var item = id ? faqs[id] : null;
                form.action = item ? item.url : storeUrl;
                document.getElementById('faq-method').value = item ? 'PUT' : 'POST';
                document.getElementById('faq-id').value = id || '';
                title.textContent = item ? 'Edit FAQ' : 'Add a FAQ';
                document.getElementById('q').value = item ? item.question : '';
                document.getElementById('a').value = item ? item.answer : '';
                document.getElementById('faq-active').checked = item ? item.active : true;
                var cat = item ? item.category : 'orders';
                [].forEach.call(form.querySelectorAll('input[name=category]'), function (r) { r.checked = r.value === cat; });
                modal.classList.add('open');
            }

            document.getElementById('faq-add').addEventListener('click', function () { openForm(null); });
            [].forEach.call(document.querySelectorAll('[data-edit]'), function (b) {
                b.addEventListener('click', function () { openForm(b.getAttribute('data-edit')); });
            });

            @if ($errors->any())
                (function () {
                    var id = @json($editing);
                    if (id && faqs[id]) form.action = faqs[id].url;
                    document.getElementById('faq-method').value = id ? 'PUT' : 'POST';
                    title.textContent = id ? 'Edit FAQ' : 'Add a FAQ';
                    modal.classList.add('open');
                })();
            @endif

            var list = document.getElementById('faq-list');
            var dragged = null;
            list.addEventListener('dragstart', function (e) {
                dragged = e.target.closest('li[data-id]');
                if (dragged) { dragged.style.opacity = '.5'; e.dataTransfer.effectAllowed = 'move'; }
            });
            list.addEventListener('dragover', function (e) {
                e.preventDefault();
                var over = e.target.closest('li[data-id]');
                if (!dragged || !over || over === dragged) return;
                var after = e.clientY > over.getBoundingClientRect().top + over.offsetHeight / 2;
                list.insertBefore(dragged, after ? over.nextSibling : over);
            });
            list.addEventListener('dragend', function () {
                if (!dragged) return;
                dragged.style.opacity = '';
                dragged = null;
                var order = [].map.call(list.querySelectorAll('li[data-id]'), function (li) { return li.getAttribute('data-id'); });
                fetch(@json(route('admin.faqs.reorder')), {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                    body: JSON.stringify({ order: order })
                }).then(function (r) { if (window.toast) window.toast(r.ok ? 'Order saved' : 'Could not save order', r.ok ? 'success' : 'error'); });
            });
        })();
    </script>

@endsection
