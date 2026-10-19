<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PrivacyPageRequest;
use App\Models\PrivacyPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrivacyPageController extends Controller
{
    public function edit(): View
    {
        return view('admin.privacy.edit', ['page' => PrivacyPage::instance()]);
    }

    public function update(PrivacyPageRequest $request): RedirectResponse
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

        PrivacyPage::instance()->update($data);

        return to_route('admin.privacy.edit')->with('success', 'Privacy policy updated');
    }
}
