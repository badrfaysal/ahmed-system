<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('partner_annual_closings')) {
            Schema::create('partner_annual_closings', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('closing_year')->unique();
                $table->unsignedBigInteger('period_id')->nullable()->index();
                $table->decimal('total_initial_capital', 15, 2)->default(0);
                $table->decimal('total_opening_capital', 15, 2)->default(0);
                $table->decimal('total_closing_capital', 15, 2)->default(0);
                $table->decimal('total_annual_gain', 15, 2)->default(0);
                $table->decimal('total_cumulative_gain', 15, 2)->default(0);
                $table->decimal('total_year_profits', 15, 2)->default(0);
                $table->decimal('total_year_withdrawn', 15, 2)->default(0);
                $table->unsignedInteger('partners_count')->default(0);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('partner_annual_closing_lines')) {
            Schema::create('partner_annual_closing_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('annual_closing_id')->index();
                $table->unsignedSmallInteger('closing_year')->index();
                $table->unsignedBigInteger('partner_id')->index();
                $table->string('partner_name');
                $table->boolean('is_head')->default(false);
                $table->decimal('initial_capital', 15, 2)->default(0);
                $table->decimal('opening_capital', 15, 2)->default(0);
                $table->decimal('closing_capital', 15, 2)->default(0);
                $table->decimal('annual_gain', 15, 2)->default(0);
                $table->decimal('annual_gain_pct', 10, 2)->default(0);
                $table->decimal('cumulative_gain', 15, 2)->default(0);
                $table->decimal('cumulative_gain_pct', 10, 2)->default(0);
                $table->decimal('year_net_profits', 15, 2)->default(0);
                $table->decimal('year_withdrawn', 15, 2)->default(0);
                $table->decimal('closing_share_pct', 8, 4)->default(0);
                $table->timestamps();

                $table->unique(['closing_year', 'partner_id'], 'pacl_year_partner_unique');
            });
        }

        // استعادة رأس المال التأسيسي الأصلي للشركاء الذين تم ترحيلهم سابقاً وبناء سجل التقفيلات السنوية التاريخي
        if (
            Schema::hasTable('partner_movements')
            && Schema::hasTable('partners')
            && DB::table('partner_annual_closings')->count() === 0
        ) {
            $rollovers = DB::table('partner_movements')
                ->where('type', 'annual_rollover')
                ->orderBy('id', 'asc')
                ->get();

            if ($rollovers->isNotEmpty()) {
                $byYear = [];
                $earliestInitialByPartner = [];

                foreach ($rollovers as $ro) {
                    $year = (int) date('Y', strtotime($ro->created_at));
                    if (preg_match('/31 ديسمبر\s+(\d{4})/u', (string) $ro->notes, $ym)) {
                        $year = (int) $ym[1];
                    }
                    $oldInitial = null;
                    if (preg_match('/أصل رأس المال السابق:\s*([0-9,]+\.?\d*)/u', (string) $ro->notes, $im)) {
                        $oldInitial = round((float) str_replace(',', '', $im[1]), 2);
                    }

                    if ($oldInitial !== null && !array_key_exists($ro->partner_id, $earliestInitialByPartner)) {
                        $earliestInitialByPartner[$ro->partner_id] = $oldInitial;
                    }

                    $byYear[$year][$ro->partner_id] = [
                        'closing_capital' => round((float) $ro->amount, 2),
                        'old_initial'     => $oldInitial,
                        'period_id'       => $ro->period_id,
                        'closed_by'       => $ro->created_by,
                        'closed_at'       => $ro->created_at,
                    ];
                }

                // استعادة أصل رأس المال التأسيسي المحفوظ لكل شريك في جدول partners
                foreach ($earliestInitialByPartner as $partnerId => $foundingCap) {
                    DB::table('partners')
                        ->where('id', $partnerId)
                        ->update([
                            'initial_capital' => $foundingCap,
                            'updated_at'      => now(),
                        ]);
                }

                ksort($byYear);
                $partnersById = DB::table('partners')->get()->keyBy('id');
                $prevClosingByPartner = [];

                foreach ($byYear as $year => $entries) {
                    $totalClosing = 0.0;
                    foreach ($entries as $e) {
                        $totalClosing += $e['closing_capital'];
                    }

                    $yearStart = "{$year}-01-01";
                    $yearEnd   = "{$year}-12-31";

                    $periodIds = Schema::hasTable('partner_periods')
                        ? DB::table('partner_periods')
                            ->where('status', 'closed')
                            ->whereBetween('end_date', [$yearStart, $yearEnd])
                            ->pluck('id')
                            ->all()
                        : [];

                    $firstMeta = reset($entries);
                    $annualClosingId = DB::table('partner_annual_closings')->insertGetId([
                        'closing_year'          => $year,
                        'period_id'             => $firstMeta['period_id'] ?? null,
                        'total_initial_capital' => 0,
                        'total_opening_capital' => 0,
                        'total_closing_capital' => round($totalClosing, 2),
                        'total_annual_gain'     => 0,
                        'total_cumulative_gain' => 0,
                        'total_year_profits'    => 0,
                        'total_year_withdrawn'  => 0,
                        'partners_count'        => count($entries),
                        'notes'                 => "إقفال السنة المالية {$year} (مرحل من السجل التاريخي مع استعادة رأس المال التأسيسي الثابت)",
                        'closed_by'             => $firstMeta['closed_by'] ?? 1,
                        'closed_at'             => $firstMeta['closed_at'] ?? now(),
                        'created_at'            => $firstMeta['closed_at'] ?? now(),
                        'updated_at'            => now(),
                    ]);

                    $sumInit = 0.0;
                    $sumOpen = 0.0;
                    $sumAnnGain = 0.0;
                    $sumCumGain = 0.0;
                    $sumProfits = 0.0;
                    $sumWithdrawn = 0.0;

                    foreach ($entries as $partnerId => $e) {
                        $p = $partnersById->get($partnerId);
                        if (!$p) {
                            continue;
                        }
                        $isHead = !empty($p->is_head);
                        $foundingCap = $earliestInitialByPartner[$partnerId] ?? round((float) $p->initial_capital, 2);
                        $openingCap  = array_key_exists($partnerId, $prevClosingByPartner)
                            ? $prevClosingByPartner[$partnerId]
                            : ($e['old_initial'] ?? $foundingCap);
                        $closingCap  = $e['closing_capital'];

                        $annualGain    = round($closingCap - $openingCap, 2);
                        $annualGainPct = $openingCap > 0 ? round(($annualGain / $openingCap) * 100, 2) : 0.0;
                        $cumGain       = round($closingCap - $foundingCap, 2);
                        $cumGainPct    = $foundingCap > 0 ? round(($cumGain / $foundingCap) * 100, 2) : 0.0;

                        $yearProfits = 0.0;
                        $yearWithdrawn = 0.0;
                        if (!empty($periodIds)) {
                            if ($isHead) {
                                $yearProfits = round((float) DB::table('partner_periods')
                                    ->whereIn('id', $periodIds)
                                    ->selectRaw('COALESCE(SUM(CASE WHEN ahmed_total_profit != 0 THEN ahmed_total_profit ELSE total_ahmed_commission END), 0) as s')
                                    ->value('s'), 2);
                                $yearWithdrawn = round((float) DB::table('partner_periods')
                                    ->whereIn('id', $periodIds)
                                    ->sum('ahmed_withdrawn_amount'), 2);
                            } elseif (Schema::hasTable('partner_period_lines')) {
                                $yearProfits = round((float) DB::table('partner_period_lines')
                                    ->where('partner_id', $partnerId)
                                    ->whereIn('period_id', $periodIds)
                                    ->sum('net_share'), 2);
                                $yearWithdrawn = round((float) DB::table('partner_period_lines')
                                    ->where('partner_id', $partnerId)
                                    ->whereIn('period_id', $periodIds)
                                    ->sum('withdrawn_amount'), 2);
                            }
                        }

                        $sharePct = $totalClosing > 0 ? round(($closingCap / $totalClosing) * 100, 4) : 0.0;

                        DB::table('partner_annual_closing_lines')->insert([
                            'annual_closing_id'   => $annualClosingId,
                            'closing_year'        => $year,
                            'partner_id'          => $partnerId,
                            'partner_name'        => $p->name,
                            'is_head'             => $isHead ? 1 : 0,
                            'initial_capital'     => $foundingCap,
                            'opening_capital'     => $openingCap,
                            'closing_capital'     => $closingCap,
                            'annual_gain'         => $annualGain,
                            'annual_gain_pct'     => $annualGainPct,
                            'cumulative_gain'     => $cumGain,
                            'cumulative_gain_pct' => $cumGainPct,
                            'year_net_profits'    => $yearProfits,
                            'year_withdrawn'      => $yearWithdrawn,
                            'closing_share_pct'   => $sharePct,
                            'created_at'          => $e['closed_at'] ?? now(),
                            'updated_at'          => now(),
                        ]);

                        $prevClosingByPartner[$partnerId] = $closingCap;
                        $sumInit      += $foundingCap;
                        $sumOpen      += $openingCap;
                        $sumAnnGain   += $annualGain;
                        $sumCumGain   += $cumGain;
                        $sumProfits   += $yearProfits;
                        $sumWithdrawn += $yearWithdrawn;
                    }

                    DB::table('partner_annual_closings')->where('id', $annualClosingId)->update([
                        'total_initial_capital' => round($sumInit, 2),
                        'total_opening_capital' => round($sumOpen, 2),
                        'total_closing_capital' => round($totalClosing, 2),
                        'total_annual_gain'     => round($sumAnnGain, 2),
                        'total_cumulative_gain' => round($sumCumGain, 2),
                        'total_year_profits'    => round($sumProfits, 2),
                        'total_year_withdrawn'  => round($sumWithdrawn, 2),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_annual_closing_lines');
        Schema::dropIfExists('partner_annual_closings');
    }
};
