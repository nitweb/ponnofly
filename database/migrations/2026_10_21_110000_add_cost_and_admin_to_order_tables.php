<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable()->after('old_price');
        });

        Schema::table('order_events', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('order_id')->constrained('admins')->nullOnDelete();
        });

        // Orders placed before this migration get the product's current cost as a best-effort snapshot.
        DB::table('order_items')
            ->whereNull('cost_price')
            ->whereNotNull('product_id')
            ->orderBy('id')
            ->chunkById(500, function ($items) {
                $costs = DB::table('products')->whereIn('id', $items->pluck('product_id')->unique())->pluck('cost_price', 'id');

                foreach ($items as $item) {
                    if ($costs[$item->product_id] ?? null) {
                        DB::table('order_items')->where('id', $item->id)->update(['cost_price' => $costs[$item->product_id]]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('order_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
