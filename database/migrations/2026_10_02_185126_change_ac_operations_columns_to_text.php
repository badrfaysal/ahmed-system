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
            $table->text('multi_floors_text')->nullable()->change();
            $table->text('multi_classes_text')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ac_operations', function (Blueprint $table) {
            $table->string('multi_floors_text', 255)->nullable()->change();
            $table->string('multi_classes_text', 255)->nullable()->change();
        });
    }
};
