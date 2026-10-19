<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge(['phone' => User::normalizePhone($this->input('phone'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^01\d{9}$/'],
            'email' => ['required', 'email', 'max:150'],
            'topic' => ['required', Rule::in(ContactMessage::TOPICS)],
            'message' => ['required', 'string', 'min:10', 'max:500'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'phone' => 'mobile number',
            'email' => 'email address',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid 11 digit mobile number, like 01XXXXXXXXX.',
        ];
    }
}
