<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('color', 7)->default('#0B3A8C');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method', 20)->default('cash');
            $table->string('reference', 80)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('expense_date');
        });

        $now = now();

        DB::table('expense_categories')->insert(array_map(
            fn (array $row) => $row + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            [
                ['name' => 'Rent & Utilities', 'color' => '#0B3A8C'],
                ['name' => 'Salary & Wages', 'color' => '#7C3AED'],
                ['name' => 'Marketing & Ads', 'color' => '#F5A800'],
                ['name' => 'Packaging', 'color' => '#0891B2'],
                ['name' => 'Courier & Delivery', 'color' => '#0A8F6B'],
                ['name' => 'Software & Tools', 'color' => '#DC2626'],
                ['name' => 'Other', 'color' => '#64748B'],
            ],
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
