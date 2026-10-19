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
        Schema::create('shipping_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title', 120);
            $table->string('hero_subtitle');
            $table->string('zones_title', 120);
            $table->string('zones_text');
            $table->json('zone_areas')->nullable();
            $table->string('know_title', 120);
            $table->json('know_items')->nullable();
            $table->string('flow_title', 120);
            $table->string('flow_text');
            $table->json('flow_steps')->nullable();
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
        Schema::dropIfExists('shipping_pages');
    }
};
