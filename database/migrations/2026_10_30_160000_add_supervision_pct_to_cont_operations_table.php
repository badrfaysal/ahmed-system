<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cont_operations', 'supervision_pct')) {
            Schema::table('cont_operations', function (Blueprint $table) {
                $table->decimal('supervision_pct', 5, 2)->default(0)->after('is_billable');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cont_operations', 'supervision_pct')) {
            Schema::table('cont_operations', function (Blueprint $table) {
                $table->dropColumn('supervision_pct');
            });
        }
    }
};
