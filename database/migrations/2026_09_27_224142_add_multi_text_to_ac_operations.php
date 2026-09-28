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
        Schema::table('ac_operations', function (Blueprint $table) {
            $table->text('multi_floors_text')->nullable()->after('ac_floor_id');
            $table->text('multi_classes_text')->nullable()->after('ac_class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ac_operations', function (Blueprint $table) {
            $table->dropColumn('multi_floors_text');
            $table->dropColumn('multi_classes_text');
        });
    }
};
