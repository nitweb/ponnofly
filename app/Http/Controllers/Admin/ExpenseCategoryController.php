<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.expenses.categories', [
            'categories' => ExpenseCategory::query()->withCount('expenses')->withSum('expenses', 'amount')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ExpenseCategory::create($this->validated($request) + ['is_active' => true]);

        return back()->with('success', 'Category added');
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Category updated');
    }

    public function destroy(ExpenseCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Category deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?ExpenseCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('expense_categories', 'name')->ignore($category?->id)],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
    }
}
