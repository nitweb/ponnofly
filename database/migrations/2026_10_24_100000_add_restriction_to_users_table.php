<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_restricted')->default(false)->after('password');
            $table->string('restriction_reason', 255)->nullable()->after('is_restricted');
            $table->timestamp('restricted_at')->nullable()->after('restriction_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_restricted', 'restriction_reason', 'restricted_at']);
        });
    }
};
