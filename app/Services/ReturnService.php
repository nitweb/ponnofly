<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Product;
use App\Models\ProductVariant;
use DomainException;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    /**
     * Records a customer's request to cancel or return the given order lines.
     *
     * @param  array<int, int>  $quantities  order item id => quantity
     *
     * @throws DomainException
     */
    public function request(Order $order, ?int $userId, array $quantities, string $reason, ?string $details, bool $notifyAdmins = true): OrderReturn
    {
        $return = DB::transaction(function () use ($order, $userId, $quantities, $reason, $details) {
            $order = Order::query()->lockForUpdate()->with('items')->findOrFail($order->id);

            if ($order->isFinal()) {
                throw new DomainException('This order is already '.strtolower($order->status_label).'.');
            }

            $lines = [];

            foreach ($quantities as $itemId => $quantity) {
                $item = $order->items->firstWhere('id', (int) $itemId);

                if (! $item) {
                    throw new DomainException('One of the selected products is not part of this order.');
                }

                $available = $this->returnableQuantity($item);

                if ($quantity < 1 || $quantity > $available) {
                    throw new DomainException($available > 0
                        ? "You can only request {$available} of {$item->name}."
                        : "{$item->name} already has a request.");
                }

                $lines[$item->id] = $quantity;
            }

            if ($lines === []) {
                throw new DomainException('Select at least one product.');
            }

            $return = OrderReturn::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'type' => $order->status === 'delivered' ? 'return' : 'cancel',
                'status' => 'pending',
                'reason' => $reason,
                'details' => $details,
            ]);

            foreach ($lines as $itemId => $quantity) {
                OrderReturnItem::create(['order_return_id' => $return->id, 'order_item_id' => $itemId, 'quantity' => $quantity]);
            }

            return $return;
        });

        if ($notifyAdmins) {
            AdminNotifier::returnRequested($return);
        }

        return $return;
    }

    public function returnableQuantity(OrderItem $item): int
    {
        $claimed = OrderReturnItem::query()
            ->where('order_item_id', $item->id)
            ->whereHas('orderReturn', fn ($query) => $query->whereIn('status', ['pending', 'approved']))
            ->sum('quantity');

        return max(0, $item->quantity - (int) $claimed);
    }

    /**
     * Approves a request: the items go back into stock and, once every unit of the order is covered,
     * the order itself becomes returned (if it was delivered) or cancelled.
     *
     * @throws DomainException
     */
    public function approve(OrderReturn $return, ?int $adminId): void
    {
        DB::transaction(function () use ($return, $adminId) {
            $return = OrderReturn::query()->lockForUpdate()->with('items.orderItem')->findOrFail($return->id);

            if (! $return->isPending()) {
                throw new DomainException('This request has already been '.$return->status.'.');
            }

            $order = Order::query()->lockForUpdate()->with('items')->findOrFail($return->order_id);

            if ($order->isFinal()) {
                throw new DomainException('This order is already '.strtolower($order->status_label).'.');
            }

            foreach ($return->items as $line) {
                $this->restockLine($line->orderItem, $line->quantity);
            }

            $return->update(['status' => 'approved', 'admin_id' => $adminId, 'resolved_at' => now()]);

            $approved = OrderReturnItem::query()
                ->whereIn('order_item_id', $order->items->pluck('id'))
                ->whereHas('orderReturn', fn ($query) => $query->where('status', 'approved'))
                ->selectRaw('order_item_id, sum(quantity) as total')
                ->groupBy('order_item_id')
                ->pluck('total', 'order_item_id');

            $everythingCovered = $order->items->every(fn (OrderItem $item) => (int) ($approved[$item->id] ?? 0) >= $item->quantity);
            $label = $return->type === 'return' ? 'Return' : 'Cancellation';

            if ($everythingCovered) {
                $status = $order->status === 'delivered' ? 'returned' : 'cancelled';
                $order->update(['status' => $status, 'restocked_at' => now()]);

                OrderEvent::create(['order_id' => $order->id, 'admin_id' => $adminId, 'status' => $status, 'note' => "{$return->number} · {$label} approved"]);

                return;
            }

            OrderEvent::create([
                'order_id' => $order->id,
                'admin_id' => $adminId,
                'status' => $order->status,
                'note' => "{$return->number} · {$label} approved for ".$return->items->sum('quantity').' item(s)',
            ]);
        });
    }

    /** @throws DomainException */
    public function reject(OrderReturn $return, ?int $adminId, ?string $note): void
    {
        DB::transaction(function () use ($return, $adminId, $note) {
            $return = OrderReturn::query()->lockForUpdate()->findOrFail($return->id);

            if (! $return->isPending()) {
                throw new DomainException('This request has already been '.$return->status.'.');
            }

            $return->update(['status' => 'rejected', 'admin_id' => $adminId, 'admin_note' => $note, 'resolved_at' => now()]);

            OrderEvent::create([
                'order_id' => $return->order_id,
                'admin_id' => $adminId,
                'status' => Order::query()->whereKey($return->order_id)->value('status'),
                'note' => "{$return->number} · ".($return->type === 'return' ? 'Return' : 'Cancellation').' request rejected',
            ]);
        });
    }

    private function restockLine(OrderItem $item, int $quantity): void
    {
        $quantity = min($quantity, $item->quantity - $item->restocked_quantity);

        if ($quantity <= 0) {
            return;
        }

        $item->increment('restocked_quantity', $quantity);

        if (! $item->product_id) {
            return;
        }

        $product = Product::query()->lockForUpdate()->find($item->product_id);

        if (! $product) {
            return;
        }

        if ($product->track_inventory) {
            if ($item->variant_id) {
                ProductVariant::query()->where('id', $item->variant_id)->increment('stock', $quantity);
            } else {
                $product->increment('stock', $quantity);
            }
        }

        $product->update(['sold_count' => max(0, $product->sold_count - $quantity)]);
    }
}
