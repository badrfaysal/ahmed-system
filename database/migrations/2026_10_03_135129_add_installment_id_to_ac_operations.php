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
            $table->unsignedBigInteger('installment_id')->nullable()->after('ac_client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ac_operations', function (Blueprint $table) {
            $table->dropColumn('installment_id');
        });
    }
};
