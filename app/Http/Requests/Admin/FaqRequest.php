<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(Faq::CATEGORIES))],
            'answer' => ['required', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
