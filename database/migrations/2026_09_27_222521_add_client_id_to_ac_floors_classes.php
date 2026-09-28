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
        Schema::table('ac_floors', function (Blueprint $table) {
            $table->unsignedBigInteger('ac_client_id')->nullable()->after('id');
        });
        
        Schema::table('ac_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('ac_client_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ac_floors', function (Blueprint $table) {
            $table->dropColumn('ac_client_id');
        });
        
        Schema::table('ac_classes', function (Blueprint $table) {
            $table->dropColumn('ac_client_id');
        });
    }
};
