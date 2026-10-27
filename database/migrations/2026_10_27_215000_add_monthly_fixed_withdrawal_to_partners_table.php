<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (!Schema::hasColumn('partners', 'monthly_fixed_withdrawal')) {
                $table->decimal('monthly_fixed_withdrawal', 15, 2)->nullable()->default(null)->after('ahmed_ratio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'monthly_fixed_withdrawal')) {
                $table->dropColumn('monthly_fixed_withdrawal');
            }
        });
    }
};
