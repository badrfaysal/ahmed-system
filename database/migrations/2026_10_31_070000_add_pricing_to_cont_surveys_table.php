<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cont_surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('cont_surveys', 'pricing_data')) {
                $table->longText('pricing_data')->nullable()->after('rooms_data');
            }
            if (!Schema::hasColumn('cont_surveys', 'total_labor_cost')) {
                $table->decimal('total_labor_cost', 15, 2)->default(0)->after('pricing_data');
            }
            if (!Schema::hasColumn('cont_surveys', 'total_material_cost')) {
                $table->decimal('total_material_cost', 15, 2)->default(0)->after('total_labor_cost');
            }
            if (!Schema::hasColumn('cont_surveys', 'estimated_total')) {
                $table->decimal('estimated_total', 15, 2)->default(0)->after('total_material_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cont_surveys', function (Blueprint $table) {
            $table->dropColumn(['pricing_data', 'total_labor_cost', 'total_material_cost', 'estimated_total']);
        });
    }
};
