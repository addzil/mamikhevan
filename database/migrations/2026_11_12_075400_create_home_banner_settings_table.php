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
        Schema::create('home_banner_settings', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 80)->default('Koleksi baru');
            $table->string('headline', 180)->default('Baju anak lucu, nyaman, siap dipakai.');
            $table->text('description')->nullable();
            $table->string('design', 30)->default('collage');
            $table->string('background', 30)->default('warm');
            $table->json('cards')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_banner_settings');
    }
};
