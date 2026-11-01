<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cont_projects') && !Schema::hasColumn('cont_projects', 'area_sqm')) {
            Schema::table('cont_projects', function (Blueprint $table) {
                $table->decimal('area_sqm', 10, 2)->nullable()->after('location');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cont_projects') && Schema::hasColumn('cont_projects', 'area_sqm')) {
            Schema::table('cont_projects', function (Blueprint $table) {
                $table->dropColumn('area_sqm');
            });
        }
    }
};
