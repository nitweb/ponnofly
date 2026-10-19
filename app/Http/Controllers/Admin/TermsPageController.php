<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TermsPageRequest;
use App\Models\TermsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TermsPageController extends Controller
{
    public function edit(): View
    {
        return view('admin.terms.edit', ['page' => TermsPage::instance()]);
    }

    public function update(TermsPageRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['keys', 'clauses']);

        $data['keys'] = collect($request->input('keys', []))
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->map(fn ($row) => [
                'icon' => $row['icon'] ?? 'check',
                'title' => $row['title'],
                'text' => (string) ($row['text'] ?? ''),
            ])->values()->all();

        $data['clauses'] = collect($request->input('clauses', []))
            ->filter(fn ($row) => filled($row['title'] ?? null) && filled($row['body'] ?? null))
            ->map(fn ($row) => ['title' => $row['title'], 'body' => $row['body']])
            ->values()->all();

        if ($data['clauses'] === []) {
            return back()->withInput()->withErrors(['clauses' => 'Add at least one section with a title and text.']);
        }

        TermsPage::instance()->update($data);

        return to_route('admin.terms.edit')->with('success', 'Terms of service updated');
    }
}
