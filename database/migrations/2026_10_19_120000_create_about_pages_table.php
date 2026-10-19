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
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title');
            $table->text('hero_subtitle');
            $table->string('hero_image')->nullable();
            $table->string('hero_caption', 80)->nullable();
            $table->json('stats')->nullable();
            $table->string('story_title', 120);
            $table->text('story_text');
            $table->json('story_points')->nullable();
            $table->string('story_image')->nullable();
            $table->string('story_tag_number', 20)->nullable();
            $table->string('story_tag_text', 80)->nullable();
            $table->text('mission_text');
            $table->text('vision_text');
            $table->string('values_title', 120);
            $table->string('values_subtitle')->nullable();
            $table->json('values')->nullable();
            $table->string('process_title', 120);
            $table->string('process_subtitle')->nullable();
            $table->json('steps')->nullable();
            $table->string('gallery_title', 120);
            $table->json('gallery')->nullable();
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
        Schema::dropIfExists('about_pages');
    }
};
