<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('size_guide_type_id')->nullable()->after('sizes');
            $table->foreign('size_guide_type_id')
                ->references('id')
                ->on('size_guide_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['size_guide_type_id']);
            $table->dropColumn('size_guide_type_id');
        });
    }
};
