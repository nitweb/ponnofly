<?php

namespace App\Http\Requests\Admin;

use App\Support\PolicyText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TermsPageRequest extends FormRequest
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
            'last_updated' => ['nullable', 'date'],
            'keys' => ['nullable', 'array', 'max:4'],
            'keys.*.icon' => ['nullable', Rule::in(array_keys(PolicyText::ICONS))],
            'keys.*.title' => ['nullable', 'string', 'max:80'],
            'keys.*.text' => ['nullable', 'string', 'max:160'],
            'clauses' => ['required', 'array', 'min:1', 'max:20'],
            'clauses.*.title' => ['nullable', 'string', 'max:120'],
            'clauses.*.body' => ['nullable', 'string', 'max:3000'],
            'cta_title' => ['required', 'string', 'max:120'],
            'cta_text' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clauses.required' => 'Add at least one section.',
        ];
    }
}
