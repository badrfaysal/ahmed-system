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
        Schema::table('fuel_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('fuel_transactions', 'odometer')) {
                $table->double('odometer')->nullable()->after('car_number');
            }
            if (!Schema::hasColumn('fuel_transactions', 'is_full_tank')) {
                $table->boolean('is_full_tank')->default(true)->after('odometer');
            }
            if (!Schema::hasColumn('fuel_transactions', 'distance_km')) {
                $table->double('distance_km')->nullable()->after('is_full_tank');
            }
            if (!Schema::hasColumn('fuel_transactions', 'consumption_rate')) {
                $table->double('consumption_rate')->nullable()->after('distance_km');
            }
            if (!Schema::hasColumn('fuel_transactions', 'cost_per_km')) {
                $table->double('cost_per_km')->nullable()->after('consumption_rate');
            }
            if (!Schema::hasColumn('fuel_transactions', 'notes')) {
                $table->text('notes')->nullable()->after('ahmed_profit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'odometer',
                'is_full_tank',
                'distance_km',
                'consumption_rate',
                'cost_per_km',
                'notes',
            ]);
        });
    }
};
