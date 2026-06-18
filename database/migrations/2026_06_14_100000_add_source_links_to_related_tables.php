<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('installments', function (Blueprint $t) {
            if (!Schema::hasColumn('installments', 'source_type')) $t->string('source_type', 40)->nullable()->index();
            if (!Schema::hasColumn('installments', 'source_id'))   $t->unsignedBigInteger('source_id')->nullable()->index();
        });

        Schema::table('company_debts', function (Blueprint $t) {
            if (!Schema::hasColumn('company_debts', 'source_type')) $t->string('source_type', 40)->nullable()->index();
            if (!Schema::hasColumn('company_debts', 'source_id'))   $t->unsignedBigInteger('source_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('installments', function (Blueprint $t) {
            if (Schema::hasColumn('installments', 'source_type')) $t->dropColumn('source_type');
            if (Schema::hasColumn('installments', 'source_id'))   $t->dropColumn('source_id');
        });
        Schema::table('company_debts', function (Blueprint $t) {
            if (Schema::hasColumn('company_debts', 'source_type')) $t->dropColumn('source_type');
            if (Schema::hasColumn('company_debts', 'source_id'))   $t->dropColumn('source_id');
        });
    }
};
