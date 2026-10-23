<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_periods', 'ahmed_opening_capital')) {
                $table->decimal('ahmed_opening_capital', 15, 2)->default(0)->after('total_partners_net_profit');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_capital_share_pct')) {
                $table->decimal('ahmed_capital_share_pct', 8, 4)->default(0)->after('ahmed_opening_capital');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_own_profit')) {
                $table->decimal('ahmed_own_profit', 15, 2)->default(0)->after('ahmed_capital_share_pct');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_commission')) {
                $table->decimal('ahmed_commission', 15, 2)->default(0)->after('ahmed_own_profit');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_total_profit')) {
                $table->decimal('ahmed_total_profit', 15, 2)->default(0)->after('ahmed_commission');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_payout_action')) {
                $table->string('ahmed_payout_action', 30)->default('reinvest')->after('ahmed_total_profit');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_withdrawn_amount')) {
                $table->decimal('ahmed_withdrawn_amount', 15, 2)->default(0)->after('ahmed_payout_action');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_payout_account_id')) {
                $table->unsignedBigInteger('ahmed_payout_account_id')->nullable()->after('ahmed_withdrawn_amount');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_reinvested_amount')) {
                $table->decimal('ahmed_reinvested_amount', 15, 2)->default(0)->after('ahmed_payout_account_id');
            }
            if (!Schema::hasColumn('partner_periods', 'ahmed_closing_capital')) {
                $table->decimal('ahmed_closing_capital', 15, 2)->default(0)->after('ahmed_reinvested_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partner_periods', function (Blueprint $table) {
            $table->dropColumn([
                'ahmed_opening_capital',
                'ahmed_capital_share_pct',
                'ahmed_own_profit',
                'ahmed_commission',
                'ahmed_total_profit',
                'ahmed_payout_action',
                'ahmed_withdrawn_amount',
                'ahmed_payout_account_id',
                'ahmed_reinvested_amount',
                'ahmed_closing_capital',
            ]);
        });
    }
};
