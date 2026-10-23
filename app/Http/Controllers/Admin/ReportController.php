<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        $period = $this->reports->period($request);
        $summary = $this->reports->summary($period);
        $previous = $this->reports->previousSummary($period);

        return view('admin.reports.index', [
            'period' => $period,
            'summary' => $summary,
            'previous' => $previous,
            'reports' => $this->reports,
            'timeline' => $this->reports->timeline($period),
            'weekdays' => $this->reports->weekdays($period),
            'categories' => $this->reports->categorySales($period),
            'districts' => $this->reports->districts($period),
            'statuses' => $this->reports->statusBreakdown($period),
        ]);
    }

    public function sales(Request $request): View
    {
        $period = $this->reports->period($request);
        $summary = $this->reports->summary($period);

        return view('admin.reports.sales', [
            'period' => $period,
            'summary' => $summary,
            'previous' => $this->reports->previousSummary($period),
            'reports' => $this->reports,
            'timeline' => $this->reports->timeline($period),
            'statuses' => $this->reports->statusBreakdown($period),
            'payments' => $this->reports->paymentBreakdown($period),
            'products' => $this->reports->topProducts($period, 8),
            'categories' => $this->reports->categorySales($period),
            'districts' => $this->reports->districts($period),
            'coupons' => $this->reports->couponUsage($period),
            'weekdays' => $this->reports->weekdays($period),
            'orderStatuses' => Order::STATUSES,
        ]);
    }

    public function profit(Request $request): View
    {
        $period = $this->reports->period($request);

        return view('admin.reports.profit', [
            'period' => $period,
            'summary' => $this->reports->summary($period),
            'previous' => $this->reports->previousSummary($period),
            'reports' => $this->reports,
            'timeline' => $this->reports->timeline($period),
            'expenses' => $this->reports->expenses($period),
            'products' => $this->reports->topProducts($period, 10),
        ]);
    }

    public function products(Request $request): View
    {
        $period = $this->reports->period($request);

        return view('admin.reports.products', [
            'period' => $period,
            'products' => $this->reports->topProducts($period, 20),
            'categories' => $this->reports->categorySales($period),
            'stock' => $this->reports->stock(),
            'slowMovers' => $this->reports->slowMovers($period),
        ]);
    }

    public function customers(Request $request): View
    {
        $period = $this->reports->period($request);

        return view('admin.reports.customers', [
            'period' => $period,
            'customers' => $this->reports->topCustomers($period, 20),
            'mix' => $this->reports->customerMix($period),
            'districts' => $this->reports->districts($period),
            'coupons' => $this->reports->couponUsage($period),
            'summary' => $this->reports->summary($period),
        ]);
    }

    public function staff(Request $request): View
    {
        $period = $this->reports->period($request);

        return view('admin.reports.staff', [
            'period' => $period,
            'rows' => $this->reports->staffPerformance($period),
        ]);
    }

    public function staffShow(Request $request, Admin $admin): View
    {
        $period = $this->reports->period($request);

        return view('admin.reports.staff-show', [
            'period' => $period,
            'member' => $admin->load('role:id,name,color'),
            'profile' => $this->reports->staffProfile($admin, $period),
        ]);
    }

    public function export(Request $request, string $report): StreamedResponse
    {
        abort_unless(in_array($report, ['sales', 'profit', 'products', 'customers', 'staff'], true), 404);

        $period = $this->reports->period($request);
        [$name, $header, $rows] = $this->reports->exportRows($report, $period);
        $filename = sprintf('%s-%s-to-%s.csv', $name, $period['from']->format('Ymd'), $period['to']->format('Ymd'));

        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
