<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * يحسب درجة مخاطرة العميل (Risk Score) بناءً على سلوكه التاريخي.
 *
 * المعايير:
 * - عدد العقود المتأخرة / إجمالي العقود
 * - متوسط مدة التأخير بالأيام
 * - نسبة الديون الحالية / إجمالي المسحوبات
 * - وجود ديون مُعدمة (write-off) — مؤشر سلبي قوي
 * - عمر العلاقة (عميل قديم منتظم = أقل خطورة)
 *
 * المخرج: درجة من 0-100 ودرجة حرفية A/B/C/D.
 */
class CustomerRiskService
{
    /**
     * يحسب درجة المخاطرة لعميل واحد.
     */
    public static function calculateForCustomer(string $customerName): array
    {
        $contracts = DB::table('installments')
            ->where('customer_name', $customerName)
            ->where(function($q) {
                $q->where('category', '!=', 'بنزينة')->orWhereNull('category');
            })
            ->get();

        $totalContracts = $contracts->count();
        if ($totalContracts === 0) {
            return self::neutralResult($customerName, 'عميل بدون تاريخ');
        }

        // العقود التقسيطية فقط (اللي ليها أقساط شهرية)
        $instContracts = $contracts->where('installment_months', '>', 0);

        // 1. نسبة العقود المعدمة
        $writtenOff = $contracts->where('close_reason', 'written_off')->count();
        $writeOffRatio = $writtenOff / max(1, $totalContracts);

        // 2. نسبة العقود الحالية ولها رصيد متبقي
        $openContracts = $contracts->where('remaining_balance', '>', 0)->count();

        // 3. حساب التأخيرات من المدفوعات
        $latePayments = 0;
        $totalPayments = 0;
        $totalDelayDays = 0;
        $contractIds = $instContracts->pluck('id')->toArray();

        if (!empty($contractIds)) {
            // ✨ نختار فقط الأعمدة اللي بنحتاجها — created_at مش موجود في الجدول ده
            $payments = DB::table('installment_payments')
                ->whereIn('installment_id', $contractIds)
                ->select('installment_id', 'payment_date')
                ->get();

            $contractsById = $instContracts->keyBy('id');

            foreach ($payments as $p) {
                $totalPayments++;
                $inst = $contractsById->get($p->installment_id);
                if (!$inst) continue;

                $dueDay = (int) ($inst->due_day ?? 0);
                if ($dueDay < 1) continue;

                try {
                    $dateStr = $p->payment_date ?? null;
                    if (!$dateStr) continue;

                    $paid = \Carbon\Carbon::parse($dateStr);
                    // ✨ الاستحقاق في نفس شهر الدفعة (= التاريخ المتوقع للسداد)
                    $dueThis = \Carbon\Carbon::create($paid->year, $paid->month, min($dueDay, 28));
                    // التأخير بالأيام (موجب فقط لو فعلاً متأخر)
                    $delay = (int) $dueThis->diffInDays($paid, false);
                    if ($delay > 3) {
                        $latePayments++;
                        $totalDelayDays += $delay;
                    }
                } catch (\Throwable $e) {
                    // skip parse errors silently
                }
            }
        }

        $lateRatio = $totalPayments > 0 ? ($latePayments / $totalPayments) : 0;
        $avgDelayDays = $latePayments > 0 ? ($totalDelayDays / $latePayments) : 0;

        // 4. نسبة الديون الحالية
        $totalHistorical = (float) $contracts->sum('total_after_interest');
        $totalRemaining  = (float) $contracts->sum('remaining_balance');
        $debtRatio = $totalHistorical > 0 ? ($totalRemaining / $totalHistorical) : 0;

        // 5. عمر العلاقة بالأشهر
        $firstSeen = $contracts->min('created_at');
        $ageMonths = 0;
        if ($firstSeen) {
            try {
                $ageMonths = (int) \Carbon\Carbon::parse($firstSeen)->diffInMonths(now());
            } catch (\Throwable $e) {
                $ageMonths = 0;
            }
        }

        // ════════ حساب الـ Score (نفس الـ formula المستخدمة في الـ bulk) ════════
        $score = self::computeScore(
            $lateRatio, $avgDelayDays, $writeOffRatio, $debtRatio,
            $ageMonths, $totalContracts, $writtenOff
        );
        $grade = self::scoreToGrade($score);

        // ════════ تحويل لـ label + لون ════════
        $labels = [
            'A' => ['ممتاز', 'success'],
            'B' => ['جيد', 'primary'],
            'C' => ['متوسط', 'warning'],
            'D' => ['مرتفع المخاطر', 'orange'],
            'F' => ['خطر شديد', 'danger'],
        ];
        [$label, $color] = $labels[$grade] ?? ['غير محدد', 'secondary'];

        // اقتراح المقدم بناءً على الـ score
        $suggestedDown = self::suggestDownPaymentPercent($score);

        return [
            'customer_name'      => $customerName,
            'score'              => $score,
            'grade'              => $grade,
            'label'              => $label,
            'color'              => $color,
            'total_contracts'    => $totalContracts,
            'open_contracts'     => $openContracts,
            'late_payments'      => $latePayments,
            'total_payments'     => $totalPayments,
            'late_ratio'         => round($lateRatio * 100, 1),
            'avg_delay_days'     => round($avgDelayDays, 1),
            'written_off_count'  => $writtenOff,
            'debt_ratio'         => round($debtRatio * 100, 1),
            'age_months'         => $ageMonths,
            'suggested_down_pct' => $suggestedDown,
            'recommendation'     => self::generateRecommendation($score, $grade, $writtenOff, $avgDelayDays),
        ];
    }

    /**
     * يحسب الـ scores لكل العملاء دفعة واحدة (للـ archive).
     * يستخدم نفس الـ formula بتاعة calculateForCustomer للحفاظ على التطابق.
     * Optimized: 2 queries فقط بدل 2N.
     */
    public static function calculateForAll(): array
    {
        // 1) إحصائيات أساسية للعقود
        $stats = DB::table('installments')
            ->where(function($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
            ->groupBy('customer_name')
            ->selectRaw('
                customer_name,
                COUNT(*) AS total_contracts,
                SUM(CASE WHEN close_reason = "written_off" THEN 1 ELSE 0 END) AS written_off,
                SUM(CASE WHEN remaining_balance > 0 THEN 1 ELSE 0 END) AS open_contracts,
                SUM(total_after_interest) AS total_historical,
                SUM(remaining_balance) AS total_remaining,
                MIN(created_at) AS first_seen
            ')
            ->get()
            ->keyBy('customer_name');

        // 2) إحصائيات التأخير — query واحد لكل العملاء
        // ✨ الحساب الصحيح: نقارن payment_date مع تاريخ الاستحقاق الفعلي لنفس الشهر
        // (نبني تاريخ صناعي: السنة والشهر من payment_date + الـ due_day)
        $lateStats = DB::table('installment_payments as p')
            ->join('installments as i', 'i.id', '=', 'p.installment_id')
            ->whereNotNull('p.payment_date')
            ->whereNotNull('i.due_day')
            ->where('i.due_day', '>', 0)
            ->where(function($q) {
                $q->where('i.category', '!=', 'بنزينة')->orWhereNull('i.category');
            })
            ->selectRaw("
                i.customer_name,
                COUNT(*) AS total_payments,
                SUM(CASE
                    WHEN DATEDIFF(
                        p.payment_date,
                        STR_TO_DATE(CONCAT(YEAR(p.payment_date),'-',MONTH(p.payment_date),'-',LEAST(i.due_day,28)),'%Y-%m-%d')
                    ) > 3
                    THEN 1 ELSE 0
                END) AS late_payments,
                SUM(CASE
                    WHEN DATEDIFF(
                        p.payment_date,
                        STR_TO_DATE(CONCAT(YEAR(p.payment_date),'-',MONTH(p.payment_date),'-',LEAST(i.due_day,28)),'%Y-%m-%d')
                    ) > 3
                    THEN DATEDIFF(
                        p.payment_date,
                        STR_TO_DATE(CONCAT(YEAR(p.payment_date),'-',MONTH(p.payment_date),'-',LEAST(i.due_day,28)),'%Y-%m-%d')
                    )
                    ELSE 0
                END) AS total_delay_days
            ")
            ->groupBy('i.customer_name')
            ->get()
            ->keyBy('customer_name');

        $results = [];
        foreach ($stats as $name => $row) {
            $late          = $lateStats->get($name);
            $totalPayments = $late ? (int) $late->total_payments : 0;
            $latePayments  = $late ? (int) $late->late_payments  : 0;
            $totalDelay    = $late ? (int) $late->total_delay_days : 0;

            $lateRatio = $totalPayments > 0 ? $latePayments / $totalPayments : 0;
            $avgDelay  = $latePayments > 0 ? $totalDelay / $latePayments : 0;
            $writeOffRatio = $row->total_contracts > 0 ? $row->written_off / $row->total_contracts : 0;
            $debtRatio = $row->total_historical > 0 ? $row->total_remaining / $row->total_historical : 0;

            $ageMonths = 0;
            if ($row->first_seen) {
                try { $ageMonths = (int) \Carbon\Carbon::parse($row->first_seen)->diffInMonths(now()); } catch (\Throwable $e) {}
            }

            $score = self::computeScore(
                $lateRatio, $avgDelay, $writeOffRatio, $debtRatio,
                $ageMonths, (int) $row->total_contracts, (int) $row->written_off
            );
            $grade = self::scoreToGrade($score);

            $results[$name] = [
                'score'    => $score,
                'grade'    => $grade,
                'contracts'=> (int) $row->total_contracts,
                'open'     => (int) $row->open_contracts,
                'writeoff' => (int) $row->written_off,
            ];
        }
        return $results;
    }

    /**
     * الـ formula الموحدة لحساب الـ score — مستخدمة في الاتنين.
     */
    private static function computeScore(
        float $lateRatio, float $avgDelay, float $writeOffRatio, float $debtRatio,
        int $ageMonths, int $totalContracts, int $writtenOff
    ): int {
        $score = 50;
        $score -= $lateRatio * 40;
        $score -= min(30, $avgDelay);
        $score -= $writeOffRatio * 50;
        if ($debtRatio > 0.7) $score -= 15;
        elseif ($debtRatio > 0.4) $score -= 5;
        if ($ageMonths >= 12 && $lateRatio < 0.1) $score += 15;
        if ($totalContracts >= 3 && $writtenOff === 0) $score += 10;
        return (int) max(0, min(100, round($score)));
    }

    private static function scoreToGrade(int $score): string
    {
        if ($score >= 80) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 40) return 'C';
        if ($score >= 20) return 'D';
        return 'F';
    }

    private static function suggestDownPaymentPercent(int $score): int
    {
        if ($score >= 80) return 10;
        if ($score >= 60) return 20;
        if ($score >= 40) return 30;
        if ($score >= 20) return 50;
        return 70;
    }

    private static function generateRecommendation(int $score, string $grade, int $writeOff, float $avgDelay): string
    {
        if ($writeOff > 0) {
            return "⚠️ تحذير: العميل عنده {$writeOff} عقد معدم سابقاً. لا يُنصح بفتح عقد تقسيط جديد.";
        }
        if ($score >= 80) {
            return "✅ عميل ممتاز ومنتظم. يمكن فتح عقد بشروط ميسرة (مقدم 10% أو حتى أقل).";
        }
        if ($score >= 60) {
            return "👍 عميل جيد. يفضل مقدم 20% وضمان رقم اتصال إضافي.";
        }
        if ($score >= 40) {
            return "⚠️ عميل متوسط - متوسط تأخير {$avgDelay} يوم. يفضل مقدم 30% ومدة قصيرة (لا تزيد عن 6 شهور).";
        }
        if ($score >= 20) {
            return "🔴 عميل مرتفع المخاطر. لا تفتح عقد تقسيط جديد إلا بمقدم 50%+ وضامن.";
        }
        return "❌ خطر شديد. لا يُنصح بأي تعامل تقسيطي مع هذا العميل.";
    }

    private static function neutralResult(string $name, string $msg): array
    {
        return [
            'customer_name'      => $name,
            'score'              => 50,
            'grade'              => 'N',
            'label'              => 'بدون تقييم',
            'color'              => 'secondary',
            'total_contracts'    => 0,
            'recommendation'     => $msg,
            'suggested_down_pct' => 30,
        ];
    }
}
