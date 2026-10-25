<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_period_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_period_lines', 'closing_share_pct')) {
                $table->decimal('closing_share_pct', 8, 4)->default(0)->after('closing_capital');
            }
        });

        Schema::table('partner_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_periods', 'ahmed_closing_share_pct')) {
                $table->decimal('ahmed_closing_share_pct', 8, 4)->default(0)->after('ahmed_closing_capital');
            }
        });

        if (!Schema::hasTable('partner_share_histories')) {
            Schema::create('partner_share_histories', function (Blueprint $table) {
                $table->id();
                $table->string('event_type', 40); // period_closing | capital_increase | capital_withdrawal | initial_deposit | partner_exit
                $table->string('title', 255);
                $table->unsignedBigInteger('period_id')->nullable();
                $table->unsignedBigInteger('movement_id')->nullable();
                $table->unsignedBigInteger('actor_partner_id')->nullable();
                $table->decimal('total_capital_before', 15, 2)->default(0);
                $table->decimal('total_capital_after', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('event_type');
                $table->index('period_id');
                $table->index('actor_partner_id');
            });
        }

        if (!Schema::hasTable('partner_share_history_lines')) {
            Schema::create('partner_share_history_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('history_id');
                $table->unsignedBigInteger('partner_id');
                $table->string('partner_name', 255);
                $table->boolean('is_head')->default(false);
                $table->decimal('capital_before', 15, 2)->default(0);
                $table->decimal('share_pct_before', 8, 4)->default(0);
                $table->decimal('capital_change', 15, 2)->default(0);
                $table->decimal('capital_after', 15, 2)->default(0);
                $table->decimal('share_pct_after', 8, 4)->default(0);
                $table->decimal('share_pct_diff', 8, 4)->default(0);
                $table->timestamps();

                $table->index('history_id');
                $table->index('partner_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_share_history_lines');
        Schema::dropIfExists('partner_share_histories');

        Schema::table('partner_period_lines', function (Blueprint $table) {
            if (Schema::hasColumn('partner_period_lines', 'closing_share_pct')) {
                $table->dropColumn('closing_share_pct');
            }
        });

        Schema::table('partner_periods', function (Blueprint $table) {
            if (Schema::hasColumn('partner_periods', 'ahmed_closing_share_pct')) {
                $table->dropColumn('ahmed_closing_share_pct');
            }
        });
    }
};
