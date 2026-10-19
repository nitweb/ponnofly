<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use App\Models\ShippingPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $icons = Rule::in(array_keys(ShippingPage::ICONS));
        $rules = [
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_subtitle' => ['required', 'string', 'max:255'],
            'zones_title' => ['required', 'string', 'max:120'],
            'zones_text' => ['required', 'string', 'max:255'],
            'know_title' => ['required', 'string', 'max:120'],
            'know_items' => ['nullable', 'array', 'max:6'],
            'know_items.*.icon' => ['nullable', $icons],
            'know_items.*.title' => ['nullable', 'string', 'max:80'],
            'know_items.*.text' => ['nullable', 'string', 'max:200'],
            'flow_title' => ['required', 'string', 'max:120'],
            'flow_text' => ['required', 'string', 'max:255'],
            'flow_steps' => ['nullable', 'array', 'max:8'],
            'flow_steps.*.title' => ['nullable', 'string', 'max:80'],
            'flow_steps.*.text' => ['nullable', 'string', 'max:200'],
            'cta_title' => ['required', 'string', 'max:120'],
            'cta_text' => ['required', 'string', 'max:255'],
            'zone_areas' => ['nullable', 'array'],
        ];

        foreach (array_keys(Setting::ZONES) as $zone) {
            $rules["zone_areas.{$zone}.icon"] = ['nullable', $icons];
            $rules["zone_areas.{$zone}.areas"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }
}
