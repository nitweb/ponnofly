<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AdminAlert;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Sends admin notifications to every active staff member whose role can open the matching module.
 * A notification problem must never break checkout, contact or order handling, so errors are only reported.
 */
class AdminNotifier
{
    public static function newOrder(Order $order): void
    {
        self::send('order', 'orders', 'New order #'.$order->number, '৳'.number_format($order->total).' · '.$order->name, route('admin.orders.show', $order, false));
    }

    public static function orderClosed(Order $order, ?int $exceptAdminId = null): void
    {
        $verb = strtolower($order->status_label);

        self::send('cancelled', 'orders', "Order #{$order->number} {$verb}", '৳'.number_format($order->total).' · '.$order->name, route('admin.orders.show', $order, false), null, $exceptAdminId);
    }

    public static function returnRequested(OrderReturn $return): void
    {
        $return->loadMissing('order');

        self::send('cancelled', 'orders', $return->type_label.' request '.$return->number, 'Order #'.$return->order->number.' · '.$return->order->name, route('admin.return-requests.show', $return, false));
    }

    public static function newMessage(ContactMessage $message): void
    {
        self::send('message', 'messages', 'New contact message', $message->name.' · '.$message->topic, route('admin.messages.show', $message, false));
    }

    public static function newCustomer(User $user): void
    {
        self::send('customer', 'customers', 'New customer registered', trim($user->name.' · '.$user->phone, ' ·'), route('admin.customers.show', $user, false));
    }

    public static function lowStock(Product $product): void
    {
        if (! $product->track_inventory) {
            return;
        }

        $product->unsetRelation('variants');
        $stock = $product->stock_total;

        if ($stock > (int) $product->low_stock) {
            return;
        }

        $state = $stock <= 0 ? 'out' : 'low';

        self::send(
            'low_stock',
            'products',
            ($state === 'out' ? 'Out of stock: ' : 'Low stock: ').$product->name,
            $state === 'out' ? 'No stock left' : "Only {$stock} left",
            route('admin.products.edit', $product, false),
            "stock:{$product->id}:{$state}",
        );
    }

    private static function send(string $kind, string $module, string $title, string $body, string $url, ?string $key = null, ?int $exceptAdminId = null): void
    {
        try {
            if ($key && DatabaseNotification::query()->whereNull('read_at')->where('data', 'like', '%"key":"'.$key.'"%')->exists()) {
                return;
            }

            $admins = Admin::query()->with('role')->where('is_active', true)->get()
                ->filter(fn (Admin $admin) => $admin->id !== $exceptAdminId && $admin->hasPermission("{$module}:view"));

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new AdminAlert($kind, $title, $body, $url, $key));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
