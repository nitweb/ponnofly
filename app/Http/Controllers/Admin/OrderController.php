<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderReturn;
use App\Models\Setting;
use App\Services\AdminNotifier;
use App\Services\CheckoutService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(Order::STATUSES))],
            'payment' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'courier' => ['nullable', Rule::in([...array_keys(Setting::COURIERS), 'none'])],
            'date' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = Order::query()
            ->with('items:id,order_id,quantity')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment'] ?? null, fn ($query, $payment) => $query->where('payment_status', $payment))
            ->when($filters['courier'] ?? null, fn ($query, $courier) => $courier === 'none'
                ? $query->whereNull('courier')
                : $query->where('courier', $courier))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', $date))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $like = '%'.addcslashes(trim($term), '%_\\').'%';

                $query->where(fn ($inner) => $inner
                    ->where('number', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'counts' => Order::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.returnItems.orderReturn', 'events', 'returns.items.orderItem']);

        return view('admin.orders.show', [
            'order' => $order,
            'customerOrders' => Order::query()->where('phone', $order->phone)->count(),
        ]);
    }

    public function invoice(Order $order): View
    {
        $order->load('items');

        return view('admin.orders.invoice', ['order' => $order]);
    }

    public function invoicePdf(Order $order): Response
    {
        $order->load('items');

        $fontDirectory = storage_path('app/dompdf');
        File::ensureDirectoryExists($fontDirectory);

        $options = new Options;
        $options->set('defaultFont', 'Inter');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('fontDir', $fontDirectory);
        $options->set('fontCache', $fontDirectory);
        $options->set('chroot', base_path());

        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('admin.orders.invoice_pdf', [
            'order' => $order,
            'fontPath' => resource_path('fonts/pdf'),
            'heroImage' => $this->invoiceGradientImage(Setting::get('color_primary'), Setting::get('color_accent'), 1100, 270, true),
            'barImage' => $this->invoiceGradientImage(Setting::get('color_primary'), Setting::get('color_accent'), 600, 8, false),
        ])->render());
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-'.$order->number.'.pdf"',
        ]);
    }

    /**
     * Builds the invoice header gradient (or the thin footer bar) as a PNG data URI, since dompdf cannot paint CSS gradients.
     */
    private function invoiceGradientImage(?string $primary, ?string $accent, int $width, int $height, bool $hero): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $isHex = fn (?string $value): bool => (bool) preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value);
        $primary = $isHex($primary) ? $primary : '#0B3A8C';
        $accent = $isHex($accent) ? $accent : '#F5A800';
        $toRgb = fn (string $hex): array => [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
        $mix = fn (array $from, array $to, float $amount): array => array_map(fn (int $a, int $b): int => (int) round($a + ($b - $a) * $amount), $from, $to);

        $base = $toRgb($primary);
        $gold = $toRgb($accent);

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);

        if ($hero) {
            $stops = [[0.0, $mix($base, [0, 0, 0], 0.22)], [0.55, $base], [1.0, $mix($base, [255, 255, 255], 0.30)]];
            $angle = deg2rad(125);
            $dx = sin($angle);
            $dy = -cos($angle);
            $length = abs($width * $dx) + abs($height * $dy);
        } else {
            $stops = [[0.0, $base], [1.0, $gold]];
        }

        $colorAt = function (float $t) use ($stops): array {
            $t = max(0.0, min(1.0, $t));
            for ($i = 1; $i < count($stops); $i++) {
                if ($t <= $stops[$i][0]) {
                    $span = $stops[$i][0] - $stops[$i - 1][0];

                    return $this->mixRgb($stops[$i - 1][1], $stops[$i][1], $span > 0 ? ($t - $stops[$i - 1][0]) / $span : 0);
                }
            }

            return $stops[count($stops) - 1][1];
        };

        if ($hero) {
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $t = (($x - $width / 2) * $dx + ($y - $height / 2) * $dy) / $length + 0.5;
                    [$r, $g, $b] = $colorAt($t);
                    imagesetpixel($image, $x, $y, ($r << 16) | ($g << 8) | $b);
                }
            }

            $scale = $width / 734;
            $white = imagecolorallocatealpha($image, 255, 255, 255, 115);
            imagefilledellipse($image, (int) (($width / $scale - 70) * $scale), (int) (10 * $scale), (int) (320 * $scale), (int) (320 * $scale), $white);
            $goldFill = imagecolorallocatealpha($image, $gold[0], $gold[1], $gold[2], 92);
            imagefilledellipse($image, (int) ((0.38 * 734 + 100) * $scale), (int) (($height / $scale + 30) * $scale), (int) (200 * $scale), (int) (200 * $scale), $goldFill);
        } else {
            for ($x = 0; $x < $width; $x++) {
                [$r, $g, $b] = $colorAt($x / max(1, $width - 1));
                imageline($image, $x, 0, $x, $height, ($r << 16) | ($g << 8) | $b);
            }
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     * @return array{0: int, 1: int, 2: int}
     */
    private function mixRgb(array $from, array $to, float $amount): array
    {
        return [
            (int) round($from[0] + ($to[0] - $from[0]) * $amount),
            (int) round($from[1] + ($to[1] - $from[1]) * $amount),
            (int) round($from[2] + ($to[2] - $from[2]) * $amount),
        ];
    }

    public function update(Request $request, Order $order, CheckoutService $checkout): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_diff(array_keys(Order::STATUSES), ['returned']))],
            'courier' => ['nullable', Rule::in(array_keys(Setting::COURIERS))],
            'tracking_id' => ['nullable', 'string', 'max:80'],
        ]);

        if ($order->isFinal()) {
            return back()->with('error', 'This order is '.strtolower($order->status_label).' and can no longer be changed.');
        }

        $previousStatus = $order->status;

        DB::transaction(function () use ($order, $data, $checkout) {
            $changed = $order->status !== $data['status'];

            $order->fill([
                'status' => $data['status'],
                'courier' => $data['courier'] ?? null,
                'tracking_id' => $data['tracking_id'] ?? null,
            ]);

            if ($data['status'] === 'delivered') {
                $order->payment_status = 'paid';
            }

            $order->save();

            if ($changed) {
                OrderEvent::create(['order_id' => $order->id, 'admin_id' => auth('admin')->id(), 'status' => $data['status'], 'note' => null]);
            }

            if ($order->isFinal()) {
                $checkout->restock($order->load('items'));

                OrderReturn::query()->where('order_id', $order->id)->where('status', 'pending')->update([
                    'status' => 'rejected',
                    'admin_id' => auth('admin')->id(),
                    'admin_note' => 'The order was '.strtolower($order->status_label).' by the store.',
                    'resolved_at' => now(),
                ]);
            }
        });

        if ($previousStatus !== $order->status && $order->isFinal()) {
            AdminNotifier::orderClosed($order, auth('admin')->id());
        }

        return back()->with('success', 'Order updated');
    }

    public function note(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1000']]);

        $order->update(['admin_note' => $data['admin_note'] ?? null]);

        return back()->with('success', 'Note saved');
    }
}
