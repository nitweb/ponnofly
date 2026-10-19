<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingPageRequest;
use App\Models\Setting;
use App\Models\ShippingPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShippingPageController extends Controller
{
    public function edit(): View
    {
        return view('admin.shipping.edit', ['page' => ShippingPage::instance()]);
    }

    public function update(ShippingPageRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['zone_areas', 'know_items', 'flow_steps']);

        $data['zone_areas'] = collect(array_keys(Setting::ZONES))
            ->mapWithKeys(fn (string $zone) => [$zone => [
                'icon' => $request->input("zone_areas.{$zone}.icon", 'truck'),
                'areas' => (string) $request->input("zone_areas.{$zone}.areas", ''),
            ]])->all();

        $data['know_items'] = collect($request->input('know_items', []))
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->map(fn ($row) => [
                'icon' => $row['icon'] ?? 'check',
                'title' => $row['title'],
                'text' => (string) ($row['text'] ?? ''),
            ])->values()->all();

        $data['flow_steps'] = collect($request->input('flow_steps', []))
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->map(fn ($row) => ['title' => $row['title'], 'text' => (string) ($row['text'] ?? '')])
            ->values()->all();

        ShippingPage::instance()->update($data);

        return to_route('admin.shipping.edit')->with('success', 'Shipping info updated');
    }
}
