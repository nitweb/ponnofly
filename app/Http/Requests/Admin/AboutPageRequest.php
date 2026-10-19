<?php

namespace App\Http\Requests\Admin;

use App\Models\AboutPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AboutPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        return [
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['required', 'string', 'max:500'],
            'hero_caption' => ['nullable', 'string', 'max:80'],
            'hero_image' => $image,

            'stats' => ['nullable', 'array', 'max:4'],
            'stats.*.value' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'stats.*.suffix' => ['nullable', 'string', 'max:5'],
            'stats.*.label' => ['nullable', 'string', 'max:60'],

            'story_title' => ['required', 'string', 'max:120'],
            'story_text' => ['required', 'string', 'max:3000'],
            'story_points' => ['nullable', 'array', 'max:8'],
            'story_points.*' => ['nullable', 'string', 'max:160'],
            'story_image' => $image,
            'story_tag_number' => ['nullable', 'string', 'max:20'],
            'story_tag_text' => ['nullable', 'string', 'max:80'],

            'mission_text' => ['required', 'string', 'max:600'],
            'vision_text' => ['required', 'string', 'max:600'],

            'values_title' => ['required', 'string', 'max:120'],
            'values_subtitle' => ['nullable', 'string', 'max:255'],
            'values' => ['nullable', 'array', 'max:9'],
            'values.*.icon' => ['nullable', Rule::in(array_keys(AboutPage::ICONS))],
            'values.*.title' => ['nullable', 'string', 'max:80'],
            'values.*.text' => ['nullable', 'string', 'max:200'],

            'process_title' => ['required', 'string', 'max:120'],
            'process_subtitle' => ['nullable', 'string', 'max:255'],
            'steps' => ['nullable', 'array', 'max:6'],
            'steps.*.title' => ['nullable', 'string', 'max:80'],
            'steps.*.text' => ['nullable', 'string', 'max:200'],

            'gallery_title' => ['required', 'string', 'max:120'],
            'gallery_alt' => ['nullable', 'array', 'max:3'],
            'gallery_alt.*' => ['nullable', 'string', 'max:120'],
            'gallery_images' => ['nullable', 'array', 'max:3'],
            'gallery_images.*' => $image,

            'cta_title' => ['required', 'string', 'max:120'],
            'cta_text' => ['required', 'string', 'max:200'],
        ];
    }
}
