<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'store_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', Rule::in(['BDT', 'USD'])],
            'timezone' => ['required', Rule::in(array_keys(Setting::TIMEZONES))],
            'announcement' => ['nullable', 'string', 'max:500'],

            'support_email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'opening_hours' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],

            'free_delivery_above' => ['nullable', 'numeric', 'min:0'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
            'return_days' => ['required', 'integer', 'min:1', 'max:90'],
            'refund_time' => ['required', 'string', 'max:60'],

            'chat_call' => ['nullable', 'string', 'max:40'],
            'chat_whatsapp' => ['nullable', 'string', 'max:40'],
            'chat_messenger' => ['nullable', 'string', 'max:80'],

            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_domain' => ['nullable', 'url', 'max:255'],
            'ga_id' => ['nullable', 'string', 'max:40'],
            'pixel_id' => ['nullable', 'string', 'max:40'],
            'gsc_code' => ['nullable', 'string', 'max:160'],
            'custom_header_script' => ['nullable', 'string', 'max:20000'],
            'custom_footer_script' => ['nullable', 'string', 'max:20000'],

            'color_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_accent' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_text' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_heading' => ['required', Rule::in(['Plus Jakarta Sans', 'Inter'])],
            'font_body' => ['required', Rule::in(['Plus Jakarta Sans', 'Inter'])],

            'logo_header' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'logo_footer' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg,webp', 'max:1024'],
            'og_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:width=1200,height=630'],
        ];

        foreach (array_keys(Setting::ZONES) as $zone) {
            $rules["{$zone}_charge"] = ['nullable', 'numeric', 'min:0'];
            $rules["{$zone}_time"] = ['nullable', 'string', 'max:60'];
        }

        foreach (array_keys(Setting::COURIERS) as $courier) {
            $rules["{$courier}_client_id"] = ['nullable', 'string', 'max:255'];
            $rules["{$courier}_client_secret"] = ['nullable', 'string', 'max:255'];
            $rules["{$courier}_store_id"] = ['nullable', 'string', 'max:60'];
            $rules["{$courier}_mode"] = ['required', Rule::in(['Live', 'Sandbox'])];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'og_image.dimensions' => 'The social share image must be exactly 1200 × 630 px.',
        ];
    }
}
