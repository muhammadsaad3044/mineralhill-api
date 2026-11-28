<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text', 60)->nullable();
            $table->string('title_line_1', 60);
            $table->string('title_line_2', 60);
            $table->text('description')->nullable();
            $table->string('cta_text', 30)->nullable();
            $table->string('cta_url')->nullable();
            $table->boolean('cta_enabled')->default(true);
            $table->string('background_image_path')->nullable();
            $table->string('background_image_mobile_path')->nullable();
            $table->unsignedTinyInteger('overlay_opacity')->default(0);
            $table->string('text_align', 10)->default('left');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_sections');
    }
};
