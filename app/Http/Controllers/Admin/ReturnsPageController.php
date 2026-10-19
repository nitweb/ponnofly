<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReturnsPageRequest;
use App\Models\ReturnsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReturnsPageController extends Controller
{
    public function edit(): View
    {
        return view('admin.returns.edit', ['page' => ReturnsPage::instance()]);
    }

    public function update(ReturnsPageRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['win_needs', 'steps', 'nope_items']);

        $data['win_needs'] = collect($request->input('win_needs', []))->filter()->values()->all();
        $data['nope_items'] = collect($request->input('nope_items', []))->filter()->values()->all();
        $data['steps'] = collect($request->input('steps', []))
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->map(fn ($row) => ['title' => $row['title'], 'text' => (string) ($row['text'] ?? '')])
            ->values()->all();

        ReturnsPage::instance()->update($data);

        return to_route('admin.returns.edit')->with('success', 'Returns & refunds updated');
    }
}
