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
        Schema::create('featured_products', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow_text', 60)->nullable();
            $table->string('title_line_1', 60);
            $table->string('title_line_2', 60);
            $table->text('description')->nullable();
            $table->string('currency_symbol', 10)->default('$');
            $table->enum('rating_mode', ['auto', 'manual'])->default('auto');
            $table->decimal('rating_value', 2, 1)->nullable();
            $table->unsignedInteger('rating_count')->nullable();
            $table->string('cta_text', 30)->nullable();
            $table->text('whatsapp_message_template');
            $table->boolean('show_quantity_selector')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_products');
    }
};
