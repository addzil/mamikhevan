<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_guide_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_guide_type_id')->constrained()->cascadeOnDelete();
            $table->string('label', 20);
            $table->string('age_range', 40)->nullable();
            $table->string('weight_kg', 20)->nullable();
            $table->string('height_cm', 20)->nullable();
            $table->string('chest_cm', 20)->nullable();
            $table->string('waist_cm', 20)->nullable();
            $table->string('hip_cm', 20)->nullable();
            $table->string('thigh_cm', 20)->nullable();
            $table->string('length_cm', 20)->nullable();
            $table->string('sleeve_cm', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_guide_entries');
    }
};
