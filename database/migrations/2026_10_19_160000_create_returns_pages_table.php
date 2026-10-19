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
        Schema::create('returns_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title', 120);
            $table->string('hero_subtitle');
            $table->string('win_caption', 120);
            $table->string('win_title', 120);
            $table->string('win_text');
            $table->json('win_needs')->nullable();
            $table->string('steps_title', 120);
            $table->string('steps_text');
            $table->json('steps')->nullable();
            $table->string('nope_title', 120);
            $table->string('nope_text');
            $table->json('nope_items')->nullable();
            $table->string('help_title', 120);
            $table->string('help_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns_pages');
    }
};
