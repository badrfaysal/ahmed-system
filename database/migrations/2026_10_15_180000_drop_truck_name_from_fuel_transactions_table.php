<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('fuel_transactions', 'truck_name')) {
                $table->dropColumn('truck_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fuel_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('fuel_transactions', 'truck_name')) {
                $table->string('truck_name')->nullable()->after('car_number');
            }
        });
    }
};
