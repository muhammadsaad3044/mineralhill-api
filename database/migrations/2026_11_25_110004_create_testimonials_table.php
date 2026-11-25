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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location_or_role');
            $table->string('avatar_path')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('product');
            $table->text('review');
            $table->date('reviewed_at')->nullable();
            $table->string('status')->default('published'); // published, pending, hidden
            $table->boolean('featured')->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index('featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
