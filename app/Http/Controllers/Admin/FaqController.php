<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        Faq::create([
            ...$request->validated(),
            'sort_order' => (int) Faq::max('sort_order') + 1,
        ]);

        return to_route('admin.faqs.index')->with('success', 'FAQ added.');
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());

        return to_route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    public function toggle(Faq $faq): RedirectResponse
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        return back()->with('success', $faq->is_active ? 'FAQ published.' : 'FAQ hidden.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:faqs,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach (array_values($data['order']) as $position => $id) {
                Faq::whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return to_route('admin.faqs.index')->with('success', 'FAQ deleted.');
    }
}
