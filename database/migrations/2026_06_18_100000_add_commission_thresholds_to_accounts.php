<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('min_commission', 10, 2)->default(0)->after('max_commission');
            $table->decimal('flat_fee_below', 12, 2)->default(0)->after('min_commission');
            $table->decimal('flat_fee_amount', 10, 2)->default(0)->after('flat_fee_below');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['min_commission', 'flat_fee_below', 'flat_fee_amount']);
        });
    }
};
