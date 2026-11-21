<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'show_on_home' => $this->boolean('show_on_home'),
            'show_in_menu' => $this->boolean('show_in_menu') && blank($this->input('parent_id')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        $parentRules = ['nullable', 'integer', Rule::exists('categories', 'id')];

        if ($category) {
            $parentRules[] = Rule::notIn([$category->id, ...Category::descendantIds($category->id)]);
        }

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable', 'string', 'max:140', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'parent_id' => $parentRules,
            'description' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'icon' => ['nullable', 'file', 'mimes:png,svg,webp', 'max:512'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'show_on_home' => ['boolean'],
            'show_in_menu' => ['boolean', function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                if (! $value) {
                    return;
                }

                $others = Category::query()
                    ->whereNull('parent_id')
                    ->where('show_in_menu', true)
                    ->when($category, fn ($q) => $q->where('id', '!=', $category->id))
                    ->count();

                if ($others >= Category::NAV_LIMIT) {
                    $fail('The header menu can show up to '.Category::NAV_LIMIT.' categories. Turn one off first.');
                }
            }],
            'promos' => ['nullable', 'array', 'max:2'],
            'promos.*.badge' => ['nullable', 'string', 'max:40'],
            'promos.*.title' => ['nullable', 'string', 'max:100'],
            'promos.*.cta' => ['nullable', 'string', 'max:40'],
            'promos.*.link' => ['nullable', 'string', 'max:255'],
            'promos.*.color' => ['nullable', Rule::in(['navy', 'yellow'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parent_id.not_in' => 'A category cannot be placed under itself or one of its own sub-categories.',
        ];
    }
}
