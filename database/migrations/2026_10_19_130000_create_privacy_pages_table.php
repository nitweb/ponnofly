<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('privacy_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title', 120);
            $table->string('hero_subtitle');
            $table->date('last_updated')->nullable();
            $table->json('keys')->nullable();
            $table->json('clauses')->nullable();
            $table->string('cta_title', 120);
            $table->string('cta_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_pages');
    }
};
