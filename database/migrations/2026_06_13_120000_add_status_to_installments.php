<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('installments') && ! Schema::hasColumn('installments', 'status')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->string('status', 32)->default('active')->after('remaining_balance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('installments', 'status')) {
            Schema::table('installments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
