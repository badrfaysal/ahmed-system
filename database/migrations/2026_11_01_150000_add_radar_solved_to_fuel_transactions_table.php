<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fuel_transactions') && !Schema::hasColumn('fuel_transactions', 'radar_solved')) {
            Schema::table('fuel_transactions', function (Blueprint $table) {
                $table->boolean('radar_solved')->default(false)->after('cost_per_km');
                $table->timestamp('radar_solved_at')->nullable()->after('radar_solved');
                $table->string('radar_solved_note', 500)->nullable()->after('radar_solved_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fuel_transactions') && Schema::hasColumn('fuel_transactions', 'radar_solved')) {
            Schema::table('fuel_transactions', function (Blueprint $table) {
                $table->dropColumn(['radar_solved', 'radar_solved_at', 'radar_solved_note']);
            });
        }
    }
};
