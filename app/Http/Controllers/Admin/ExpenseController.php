<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $period = $reports->period($request);
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:20'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = $this->filtered($period, $filters);

        return view('admin.expenses.index', [
            'period' => $period,
            'filters' => $filters,
            'expenses' => (clone $query)->with(['category:id,name,color', 'admin:id,name'])->latest('expense_date')->latest('id')->paginate(15)->withQueryString(),
            'total' => (float) (clone $query)->sum('amount'),
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'summary' => $reports->expenses($period),
        ]);
    }

    public function create(): View
    {
        return view('admin.expenses.create', $this->formData());
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        Expense::create($request->validated() + ['admin_id' => $request->user('admin')->id]);

        return to_route('admin.expenses.index')->with('success', 'Expense saved');
    }

    public function edit(Expense $expense): View
    {
        return view('admin.expenses.edit', $this->formData() + ['expense' => $expense]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return to_route('admin.expenses.index')->with('success', 'Expense updated');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted');
    }

    public function export(Request $request, ReportService $reports): StreamedResponse
    {
        $period = $reports->period($request);
        $filters = $request->validate(['category' => ['nullable', 'string', 'max:20'], 'q' => ['nullable', 'string', 'max:100']]);
        $rows = $this->filtered($period, $filters)->with(['category:id,name', 'admin:id,name'])->orderBy('expense_date')->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Title', 'Category', 'Amount', 'Payment method', 'Reference', 'Note', 'Added by']);

            foreach ($rows as $expense) {
                fputcsv($out, [
                    $expense->expense_date->format('Y-m-d'), $expense->title, $expense->category?->name,
                    number_format($expense->amount, 2, '.', ''), $expense->payment_label, $expense->reference, $expense->note, $expense->admin?->name,
                ]);
            }

            fclose($out);
        }, sprintf('expenses-%s-to-%s.csv', $period['from']->format('Ymd'), $period['to']->format('Ymd')), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $period
     * @param  array<string, mixed>  $filters
     */
    private function filtered(array $period, array $filters)
    {
        return Expense::query()
            ->whereDate('expense_date', '>=', $period['from']->toDateString())
            ->whereDate('expense_date', '<=', $period['to']->toDateString())
            ->when($filters['category'] ?? null, fn ($query, $category) => $category === 'none'
                ? $query->whereNull('expense_category_id')
                : $query->where('expense_category_id', (int) $category))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $like = '%'.addcslashes(trim($term), '%_\\').'%';

                $query->where(fn ($inner) => $inner->where('title', 'like', $like)->orWhere('reference', 'like', $like)->orWhere('note', 'like', $like));
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'methods' => Expense::PAYMENT_METHODS,
        ];
    }
}
