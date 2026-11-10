<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_guide_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->json('measurement_fields');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_guide_types');
    }
};
