<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug', 100)->unique();
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#0B3A8C');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('bio')->constrained('roles')->restrictOnDelete();
            $table->boolean('is_active')->default(true)->after('role_id');
        });

        // Existing admin accounts keep full access through the locked Super Admin role.
        $superAdminId = DB::table('roles')->insertGetId([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access to everything including roles and settings.',
            'color' => '#DC2626',
            'permissions' => null,
            'is_active' => true,
            'is_locked' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admins')->update(['role_id' => $superAdminId]);
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('is_active');
        });

        Schema::dropIfExists('roles');
    }
};
