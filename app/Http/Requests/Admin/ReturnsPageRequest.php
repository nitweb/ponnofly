<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReturnsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_subtitle' => ['required', 'string', 'max:255'],
            'win_caption' => ['required', 'string', 'max:120'],
            'win_title' => ['required', 'string', 'max:120'],
            'win_text' => ['required', 'string', 'max:255'],
            'win_needs' => ['nullable', 'array', 'max:8'],
            'win_needs.*' => ['nullable', 'string', 'max:80'],
            'steps_title' => ['required', 'string', 'max:120'],
            'steps_text' => ['required', 'string', 'max:255'],
            'steps' => ['nullable', 'array', 'max:6'],
            'steps.*.title' => ['nullable', 'string', 'max:80'],
            'steps.*.text' => ['nullable', 'string', 'max:200'],
            'nope_title' => ['required', 'string', 'max:120'],
            'nope_text' => ['required', 'string', 'max:255'],
            'nope_items' => ['nullable', 'array', 'max:12'],
            'nope_items.*' => ['nullable', 'string', 'max:120'],
            'help_title' => ['required', 'string', 'max:120'],
            'help_text' => ['required', 'string', 'max:255'],
        ];
    }
}
