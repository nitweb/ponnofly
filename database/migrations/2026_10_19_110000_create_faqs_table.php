<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('category', 30)->default('orders')->index();
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        $rows = [
            ['How long does delivery take?', 'delivery', 'Inside Dhaka 1-2 days, Sub-Dhaka 2-3 days and outside Dhaka 3-5 days.'],
            ['What are the delivery charges?', 'delivery', 'It is ৳70 inside Dhaka, ৳100 for Sub-Dhaka (Gazipur, Narayanganj, Savar) and ৳150 outside Dhaka.'],
            ['When will my order be dispatched?', 'delivery', 'Orders placed before 2 PM are dispatched the same day. You will get an SMS when your package ships.'],
            ['Which payment methods do you accept?', 'payments', 'Cash on delivery only, for now.'],
            ['Is cash on delivery available?', 'payments', 'Yes. Cash on delivery is available in all 64 districts.'],
            ['Can I return a product?', 'returns', 'Yes. You can return most items within 7 days if they are unused and in original packaging.'],
            ['How long do refunds take?', 'returns', 'Your refund is processed within 3-5 working days after we receive the item.'],
            ['Which items cannot be returned?', 'returns', 'Opened beauty and personal care products, innerwear and socks, and digital gift cards cannot be returned.'],
            ['How do I track my order?', 'orders', 'Open the Track order page and enter your order number and the mobile number you used at checkout.'],
            ['How can I reach customer support?', 'orders', 'Use the Contact us page, or call us. We reply to every message within one working day.'],
        ];

        DB::table('faqs')->insert(array_map(fn (array $row, int $index) => [
            'question' => $row[0],
            'category' => $row[1],
            'answer' => $row[2],
            'sort_order' => $index + 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows, array_keys($rows)));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
