<?php

namespace App\Http\Controllers;

use App\Models\Jamia;
use App\Models\JamiaShare;
use App\Models\JamiaInstallment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * منظومة الجمعيات من منظور "المنظم":
 *  - الشركة بتلم الجمعية من الأعضاء كل شهر (فترة التحصيل من يوم 1 لغاية يوم 10 بحد أقصى).
 *  - تحصيل قسط العضو  = داخل للخزنة  (income  / ref_type = jamia_collection).
 *  - صرف القبض لصاحب الدور = خارج من الخزنة (expense / ref_type = jamia_payout_out).
 *  - أسهم الشركة (شركة الضبع) لا تحتاج تحصيل ولا صرف نقدي، لأن الفلوس أصلاً عند الشركة.
 *
 * ملاحظة على استخدام الأعمدة:
 *  - jamia_installments.is_paid / paid_account_id / tx_id  => حالة تحصيل القسط من العضو.
 *  - jamia_shares.is_collected / collected_*                => حالة صرف (تسليم) القبض لصاحب الدور.
 */
class JamiaController extends SystemController
{
    public const COMPANY_NAME = 'شركة الضبع';
    public const MAX_COLLECTION_DAY = 10;

    /**
     * عرض شاشة التدفقات النقدية للجمعيات
     */
    public function index(Request $request)
    {
        if (function_exists('isDeveloperUser') && !isDeveloperUser()) {
            return view('coming_soon', [
                'screenTitle' => 'منظومة التدفقات النقدية (الجمعيات)',
                'screenIcon'  => 'fa-hand-holding-dollar',
            ]);
        }

        // مزامنة أي حركات تم إلغاؤها من سجل العمليات لضمان مطابقة الحالة 100%
        $this->syncWithCancelledTransactions();

        // الشهر المختار (الافتراضي: الشهر الحالي)
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $monthDate  = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();

        // 1. حسابات المحافظ / الخزن المتاحة
        $accounts = DB::table('accounts')
            ->whereIn('category', ['bank_wallet', 'safe_cash'])
            ->where('status', 'active')
            ->orderBy('account_name')
            ->get();

        // 2. أقساط الأعضاء المطلوب تحصيلها هذا الشهر (بدون أسهم الشركة)
        $monthInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->leftJoin('accounts as a', 'ji.paid_account_id', '=', 'a.id')
            ->whereDate('ji.month_date', $monthStart)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->select(
                'ji.*',
                'j.name as jamia_name',
                'j.collection_start_day',
                'j.collection_end_day',
                'js.share_label',
                'js.share_number',
                'js.member_phone',
                'a.account_name as paid_account_name'
            )
            ->orderBy('js.share_number')
            ->get()
            ->map(function ($inst) {
                $inst->status = self::installmentStatus($inst);
                return $inst;
            });

        // 3. أدوار القبض هذا الشهر (اللي هيستلم الجمعية)
        $monthShares = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->leftJoin('accounts as ca', 'js.collected_account_id', '=', 'ca.id')
            ->leftJoin('accounts as ra', 'js.recovered_account_id', '=', 'ra.id')
            ->whereDate('js.payout_month', $monthStart)
            ->where('j.status', 'active')
            ->select(
                'js.*',
                'j.name as jamia_name',
                'j.collection_end_day',
                'ca.account_name as collected_account_name',
                'ra.account_name as recovered_account_name'
            )
            ->orderBy('js.share_number')
            ->get();

        // 4. مؤشرات الشهر
        $expectedCollection  = (float) $monthInstallments->sum('amount');
        $collectedThisMonth  = (float) $monthInstallments->where('is_paid', true)->sum('amount');
        $remainingCollection = max(0.0, $expectedCollection - $collectedThisMonth);
        $paidMembersCount    = $monthInstallments->where('is_paid', true)->count();
        $unpaidMembersCount  = $monthInstallments->where('is_paid', false)->count();
        $lateThisMonth       = $monthInstallments->filter(fn ($i) => $i->status['key'] === 'late');
        $lateCountThisMonth  = $lateThisMonth->count();
        $lateAmountThisMonth = (float) $lateThisMonth->sum('amount');

        $memberPayouts   = $monthShares->where('is_company', false);
        $payoutDue       = (float) $memberPayouts->sum('payout_amount');
        $payoutDone      = (float) $memberPayouts->where('is_collected', true)->sum('collected_amount');
        $payoutRemaining = (float) $memberPayouts->where('is_collected', false)->sum('payout_amount');

        $netExpectedThisMonth = $expectedCollection - $payoutDue;
        $netActualThisMonth   = $collectedThisMonth - $payoutDone;

        // 5. الجمعيات النشطة في الشهر المختار مجمعة (تحصيل + دور القبض)
        $monthJamias = Jamia::where('status', 'active')
            ->with(['shares' => fn ($q) => $q->orderBy('share_number')])
            ->orderBy('name')
            ->get()
            ->map(function ($jamia) use ($monthDate, $monthInstallments, $monthShares) {
                $start = Carbon::parse($jamia->start_date)->startOfMonth();
                $jamia->month_index = (($monthDate->year - $start->year) * 12) + ($monthDate->month - $start->month) + 1;
                $jamia->month_installments = $monthInstallments->where('jamia_id', $jamia->id)->values();
                $jamia->month_payouts = $monthShares->where('jamia_id', $jamia->id)->values();
                $jamia->company_shares_in = $jamia->shares->where('is_company', true)->count();
                return $jamia;
            })
            ->filter(fn ($j) => $j->month_index >= 1 && $j->month_index <= $j->duration_months)
            ->values();

        // 6. شارت التدفقات السنوي (التحصيل المتوقع من الأعضاء vs القبض المصروف لهم)
        $year = $monthDate->year;
        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        $collectionByMonth = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->whereYear('ji.month_date', $year)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->groupBy('ji.month_date')
            ->selectRaw('ji.month_date as m, SUM(ji.amount) as total')
            ->get()
            ->reduce(function ($carry, $row) {
                $key = substr((string) $row->m, 0, 10);
                $carry[$key] = ($carry[$key] ?? 0) + (float) $row->total;
                return $carry;
            }, []);

        $payoutByMonth = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->whereYear('js.payout_month', $year)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->groupBy('js.payout_month')
            ->selectRaw('js.payout_month as m, SUM(js.payout_amount) as total')
            ->get()
            ->reduce(function ($carry, $row) {
                $key = substr((string) $row->m, 0, 10);
                $carry[$key] = ($carry[$key] ?? 0) + (float) $row->total;
                return $carry;
            }, []);

        $chartLabels = $chartCollection = $chartPayout = $chartNet = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d-01', $year, $m);
            $in  = round((float) ($collectionByMonth[$key] ?? 0), 2);
            $out = round((float) ($payoutByMonth[$key] ?? 0), 2);
            $chartLabels[]     = $arabicMonths[$m];
            $chartCollection[] = $in;
            $chartPayout[]     = $out;
            $chartNet[]        = round($in - $out, 2);
        }

        // 7. دليل الجمعيات بالأعضاء والإحصائيات الكلية
        $jamias = Jamia::with([
                'shares' => fn ($q) => $q->with(['collectedAccount', 'recoveredAccount'])->orderBy('share_number'),
                'installments',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($jamia) {
                $memberIds = $jamia->shares->where('is_company', false)->pluck('id')->all();
                $memberInsts = $jamia->installments
                    ->where('is_waived', false)
                    ->whereIn('share_id', $memberIds);

                $jamia->calc_total_due       = (float) $memberInsts->sum('amount');
                $jamia->calc_total_collected = (float) $memberInsts->where('is_paid', true)->sum('amount');
                $jamia->calc_remaining       = $jamia->calc_total_due - $jamia->calc_total_collected;
                $jamia->calc_progress        = $jamia->calc_total_due > 0
                    ? round(($jamia->calc_total_collected / $jamia->calc_total_due) * 100)
                    : 0;
                $jamia->calc_has_cash_moves  = $jamia->installments->whereNotNull('tx_id')->isNotEmpty()
                    || $jamia->shares->where('is_collected', true)->isNotEmpty()
                    || $jamia->shares->where('is_transferred', true)->isNotEmpty()
                    || $jamia->shares->where('is_claim_recovered', true)->isNotEmpty();
                $jamia->members_count        = $jamia->shares->count();
                $jamia->company_shares_in    = $jamia->shares->where('is_company', true)->count();
                $jamia->transferred_count    = $jamia->shares->where('is_transferred', true)->count();
                $jamia->collected_count      = $jamia->shares->where('is_collected', true)->count();

                // عدد الشهور المدفوعة والمتأخرة لكل عضو (لعرضها في الدليل)
                $lateByShare = [];
                $paidByShare = [];
                foreach ($memberInsts->where('is_paid', true) as $inst) {
                    $paidByShare[$inst->share_id] = ($paidByShare[$inst->share_id] ?? 0) + 1;
                }
                foreach ($memberInsts->where('is_paid', false) as $inst) {
                    $inst->collection_start_day = $jamia->collection_start_day;
                    $inst->collection_end_day   = $jamia->collection_end_day;
                    if (self::installmentStatus($inst)['key'] === 'late') {
                        $lateByShare[$inst->share_id] = ($lateByShare[$inst->share_id] ?? 0) + 1;
                    }
                }
                $jamia->late_by_share    = $lateByShare;
                $jamia->paid_by_share    = $paidByShare;
                $jamia->late_total_count = array_sum($lateByShare);

                return $jamia;
            });

        // 8. متابعة المتأخرين في السداد (كل الشهور اللي فترة تحصيلها خلصت)
        $overdueInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->where('ji.is_paid', false)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->whereDate('ji.month_date', '<=', now()->startOfMonth()->toDateString())
            ->select(
                'ji.*',
                'j.name as jamia_name',
                'j.collection_start_day',
                'j.collection_end_day',
                'js.share_label',
                'js.share_number',
                'js.member_phone'
            )
            ->orderBy('ji.month_date')
            ->orderBy('j.name')
            ->orderBy('js.share_number')
            ->get()
            ->map(function ($inst) {
                $inst->status = self::installmentStatus($inst);
                return $inst;
            })
            ->filter(fn ($i) => $i->status['key'] === 'late')
            ->values();

        // 9. إحصائيات عامة
        $globalStats = [
            'active_jamias_count' => $jamias->where('status', 'active')->count(),
            'members_count'       => JamiaShare::where('is_company', false)->count(),
            'company_shares'      => JamiaShare::where('is_company', true)->count(),
            'overdue_count'       => $overdueInstallments->count(),
            'overdue_amount'      => (float) $overdueInstallments->sum('amount'),
        ];

        $companyName = self::COMPANY_NAME;

        return view('cashflows', compact(
            'selectedMonth',
            'monthDate',
            'accounts',
            'monthInstallments',
            'monthShares',
            'monthJamias',
            'expectedCollection',
            'collectedThisMonth',
            'remainingCollection',
            'paidMembersCount',
            'unpaidMembersCount',
            'lateCountThisMonth',
            'lateAmountThisMonth',
            'payoutDue',
            'payoutDone',
            'payoutRemaining',
            'netExpectedThisMonth',
            'netActualThisMonth',
            'chartLabels',
            'chartCollection',
            'chartPayout',
            'chartNet',
            'jamias',
            'overdueInstallments',
            'globalStats',
            'companyName'
        ));
    }

    /**
     * حالة قسط العضو بالنسبة لفترة التحصيل (من يوم البداية لغاية يوم النهاية بحد أقصى 10)
     * المفاتيح: paid | paid_late | upcoming | open | late
     */
    public static function installmentStatus($inst): array
    {
        $monthStart = Carbon::parse($inst->month_date)->startOfMonth();
        $startDay   = max(1, (int) ($inst->collection_start_day ?? 1));
        $endDay     = min(self::MAX_COLLECTION_DAY, max($startDay, (int) ($inst->collection_end_day ?? self::MAX_COLLECTION_DAY)));
        $windowFrom = $monthStart->copy()->day($startDay)->startOfDay();
        $windowTo   = $monthStart->copy()->day($endDay)->endOfDay();
        $today      = now();

        if ($inst->is_paid) {
            if ($inst->paid_at && Carbon::parse($inst->paid_at)->gt($windowTo)) {
                return ['key' => 'paid_late', 'label' => 'تم التحصيل (متأخر)', 'days' => 0, 'window_to' => $windowTo];
            }
            return ['key' => 'paid', 'label' => 'تم التحصيل', 'days' => 0, 'window_to' => $windowTo];
        }

        if ($today->lt($windowFrom)) {
            return ['key' => 'upcoming', 'label' => 'لم تبدأ فترة التحصيل', 'days' => 0, 'window_to' => $windowTo];
        }

        if ($today->lte($windowTo)) {
            $daysLeft = (int) $today->copy()->startOfDay()->diffInDays($windowTo->copy()->startOfDay());
            return ['key' => 'open', 'label' => 'جاري التحصيل', 'days' => $daysLeft, 'window_to' => $windowTo];
        }

        $daysLate = (int) $windowTo->copy()->startOfDay()->diffInDays($today->copy()->startOfDay());
        return ['key' => 'late', 'label' => 'متأخر', 'days' => $daysLate, 'window_to' => $windowTo];
    }

    /**
     * حفظ جمعية جديدة بأعضائها وأدوارهم وتوليد جدول التحصيل الشهري
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'total_amount'         => 'required|numeric|min:1',
            'duration_months'      => 'required|integer|min:2|max:60',
            'start_date'           => 'required|date',
            'collection_start_day' => 'nullable|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'collection_end_day'   => 'nullable|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'members'              => 'required|array|min:2',
            'members.*.name'       => 'nullable|string|max:150',
            'members.*.phone'      => 'nullable|string|max:50',
            'members.*.turn'       => 'required|integer|min:1',
            'members.*.is_company' => 'nullable',
        ], [
            'collection_end_day.max' => 'آخر يوم للتحصيل لا يمكن أن يتعدى يوم ' . self::MAX_COLLECTION_DAY . ' من الشهر.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $totalAmount = (float) $request->total_amount;
                $duration    = (int) $request->duration_months;
                $members     = array_values($request->members);
                $monthlyInstallment = round($totalAmount / $duration, 2);
                $startDate   = Carbon::parse($request->start_date)->startOfMonth();
                $startDay    = (int) ($request->collection_start_day ?: 1);
                $endDay      = (int) ($request->collection_end_day ?: self::MAX_COLLECTION_DAY);

                if ($endDay < $startDay) {
                    throw new \Exception('آخر يوم للتحصيل لازم يكون بعد أو يساوي يوم بداية التحصيل.');
                }
                if (count($members) !== $duration) {
                    throw new \Exception("عدد الأعضاء (" . count($members) . ") لازم يساوي عدد شهور الجمعية ({$duration}) — كل عضو له دور قبض في شهر.");
                }

                $turns = array_map(fn ($m) => (int) $m['turn'], $members);
                if (count(array_unique($turns)) !== count($turns) || max($turns) > $duration) {
                    throw new \Exception('أدوار القبض لازم تكون مختلفة ومن 1 لغاية ' . $duration . '.');
                }

                $companyCount = count(array_filter($members, fn ($m) => !empty($m['is_company'])));

                $jamia = Jamia::create([
                    'name'                 => $request->name,
                    'organizer_name'       => $request->organizer_name ?: self::COMPANY_NAME,
                    'organizer_phone'      => $request->organizer_phone,
                    'total_amount'         => $totalAmount,
                    'duration_months'      => $duration,
                    'monthly_installment'  => $monthlyInstallment,
                    'company_shares_count' => $companyCount,
                    'start_date'           => $startDate->toDateString(),
                    'due_day'              => $endDay,
                    'collection_start_day' => $startDay,
                    'collection_end_day'   => $endDay,
                    'notes'                => $request->notes,
                    'status'               => 'active',
                ]);

                foreach ($members as $m) {
                    $turn      = (int) $m['turn'];
                    $isCompany = !empty($m['is_company']);
                    $name      = trim((string) ($m['name'] ?? ''));
                    if ($name === '') {
                        $name = $isCompany ? self::COMPANY_NAME : "عضو {$turn}";
                    }

                    $share = JamiaShare::create([
                        'jamia_id'      => $jamia->id,
                        'share_number'  => $turn,
                        'share_label'   => $name,
                        'member_phone'  => !empty($m['phone']) ? trim($m['phone']) : null,
                        'is_company'    => $isCompany,
                        'payout_month'  => $startDate->copy()->addMonths($turn - 1)->toDateString(),
                        'payout_amount' => $totalAmount,
                        'is_collected'  => false,
                    ]);

                    // جدول تحصيل قسط العضو على مدار شهور الجمعية
                    for ($i = 0; $i < $duration; $i++) {
                        JamiaInstallment::create([
                            'jamia_id'    => $jamia->id,
                            'share_id'    => $share->id,
                            'month_date'  => $startDate->copy()->addMonths($i)->toDateString(),
                            'month_index' => $i + 1,
                            'amount'      => $monthlyInstallment,
                            'is_paid'     => false,
                            'is_waived'   => false,
                        ]);
                    }
                }

                $this->logActivity('create', 'jamia', "تم إنشاء جمعية جديدة: {$jamia->name} — قبض {$totalAmount} ج على {$duration} شهور ({$duration} عضو، منهم {$companyCount} سهم للشركة).");
            });

            return back()->with('success', 'تمت إضافة الجمعية وتسجيل الأعضاء وأدوار القبض وجدول التحصيل بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حفظ الجمعية: ' . $e->getMessage());
        }
    }

    /**
     * تعديل بيانات عضو (الاسم / الهاتف / الدور / سهم شركة)
     * تغيير الدور بيبدل مع العضو اللي واخد الدور ده.
     */
    public function updateMember(Request $request)
    {
        $request->validate([
            'share_id'   => 'required|integer',
            'name'       => 'nullable|string|max:150',
            'phone'      => 'nullable|string|max:50',
            'turn'       => 'required|integer|min:1',
            'is_company' => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');
                $jamia = Jamia::findOrFail($share->jamia_id);

                $newTurn   = (int) $request->turn;
                $isCompany = (bool) $request->boolean('is_company');
                if ($newTurn > $jamia->duration_months) {
                    throw new \Exception("الدور لازم يكون من 1 لغاية {$jamia->duration_months}.");
                }

                if ($isCompany !== (bool) $share->is_company) {
                    if ($share->is_transferred) {
                        throw new \Exception('هذا السهم متنازل عنه لعضو بديل؛ لإعادته للشركة استخدم خيار "فك التنازل" أولاً.');
                    }
                    $hasCollected = JamiaInstallment::where('share_id', $share->id)->where('is_paid', true)->exists();
                    if ($hasCollected || $share->is_collected || $share->is_claim_recovered) {
                        throw new \Exception('لا يمكن تغيير نوع السهم (عضو/شركة) بعد وجود تحصيلات أو صرف قبض أو استرداد عليه. الغِ العمليات أولاً.');
                    }
                }

                // تبديل الدور مع العضو الآخر
                if ($newTurn !== (int) $share->share_number) {
                    $other = JamiaShare::where('jamia_id', $share->jamia_id)
                        ->where('share_number', $newTurn)
                        ->lockForUpdate()
                        ->first();

                    if ($share->is_collected || ($other && $other->is_collected)) {
                        throw new \Exception('لا يمكن تبديل دور تم صرف القبض فيه بالفعل.');
                    }

                    $oldTurn  = (int) $share->share_number;
                    $oldMonth = $share->payout_month;
                    $newMonth = Carbon::parse($jamia->start_date)->startOfMonth()->addMonths($newTurn - 1)->toDateString();

                    if ($other) {
                        $other->update(['share_number' => $oldTurn, 'payout_month' => $oldMonth]);
                    }
                    $share->share_number = $newTurn;
                    $share->payout_month = $newMonth;
                }

                $name = trim((string) $request->name);
                $share->share_label  = $name !== '' ? $name : ($isCompany ? self::COMPANY_NAME : "عضو {$share->share_number}");
                $share->member_phone = $request->phone ? trim($request->phone) : null;
                $share->is_company   = $isCompany;
                $share->save();

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                $this->logActivity('update', 'jamia', "تعديل بيانات عضو [{$share->share_label}] (دور {$share->share_number}) في جمعية {$jamia->name}.");
            });

            return back()->with('success', 'تم تعديل بيانات العضو بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * تعديل بيانات الجمعية الأساسية وفترة التحصيل (1 إلى 10 بحد أقصى) وحالتها
     */
    public function updateJamia(Request $request)
    {
        $request->validate([
            'jamia_id'             => 'required|integer',
            'name'                 => 'required|string|max:255',
            'collection_start_day' => 'required|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'collection_end_day'   => 'required|integer|min:1|max:' . self::MAX_COLLECTION_DAY,
            'status'               => 'required|in:active,completed,cancelled',
            'notes'                => 'nullable|string',
        ], [
            'collection_end_day.max' => 'آخر يوم للتحصيل لا يمكن أن يتعدى يوم ' . self::MAX_COLLECTION_DAY . ' من الشهر.',
        ]);

        try {
            $startDay = (int) $request->collection_start_day;
            $endDay   = (int) $request->collection_end_day;
            if ($endDay < $startDay) {
                throw new \Exception('آخر يوم للتحصيل لازم يكون بعد أو يساوي يوم بداية التحصيل.');
            }

            $jamia = Jamia::findOrFail($request->jamia_id);
            $jamia->update([
                'name'                 => trim($request->name),
                'collection_start_day' => $startDay,
                'collection_end_day'   => $endDay,
                'due_day'              => $endDay,
                'status'               => $request->status,
                'notes'                => $request->notes ? trim($request->notes) : null,
            ]);

            $this->logActivity('update', 'jamia', "تعديل بيانات جمعية: {$jamia->name} (التحصيل من يوم {$startDay} إلى {$endDay}).");
            return back()->with('success', 'تم تحديث بيانات الجمعية بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * تحصيل قسط/أقساط من الأعضاء وإيداعها في محفظة/خزنة (يدعم التحصيل الجماعي)
     */
    public function collectInstallments(Request $request)
    {
        $request->validate([
            'installment_ids'   => 'required|array|min:1',
            'installment_ids.*' => 'integer|exists:jamia_installments,id',
            'account_id'        => 'required|integer|exists:accounts,id',
            'paid_at'           => 'nullable|date',
            'notes'             => 'nullable|string|max:500',
        ], [
            'installment_ids.required' => 'اختر قسطاً واحداً على الأقل للتحصيل.',
            'account_id.required'      => 'اختيار الخزنة أو المحفظة إجباري.',
            'account_id.exists'        => 'المحفظة / الخزنة المختارة غير موجودة.',
        ]);

        try {
            $count = 0;
            DB::transaction(function () use ($request, &$count) {
                $account = $this->lockActiveAccount((int) $request->account_id);

                $paidAt = $request->paid_at ? Carbon::parse($request->paid_at)->setTimeFrom(now()) : now();
                $total  = 0;

                foreach (array_unique($request->installment_ids) as $instId) {
                    $inst = JamiaInstallment::where('id', $instId)->lockForUpdate()->first();
                    if (!$inst) throw new \Exception('القسط غير موجود.');
                    if ($inst->is_paid) continue; // متحصل بالفعل
                    if ($inst->is_waived) continue;

                    $share = DB::table('jamia_shares')->where('id', $inst->share_id)->first();
                    if (!$share || $share->is_company) continue; // أسهم الشركة لا تُحصّل

                    $jamia    = DB::table('jamias')->where('id', $inst->jamia_id)->first();
                    $monthStr = Carbon::parse($inst->month_date)->format('Y/m');
                    $noteText = "تحصيل قسط شهر ({$monthStr}) - جمعية: {$jamia->name} - العضو: {$share->share_label} (دور {$share->share_number})";
                    if ($request->notes) {
                        $noteText .= ' | ' . trim($request->notes);
                    }

                    DB::table('accounts')->where('id', $account->id)->increment('balance', $inst->amount);

                    $txId = DB::table('financial_transactions')->insertGetId([
                        'type'            => 'income',
                        'subtype'         => 'jamia_collection',
                        'amount'          => $inst->amount,
                        'from_account_id' => null,
                        'to_account_id'   => $account->id,
                        'ref_type'        => 'jamia_collection',
                        'ref_id'          => $inst->id,
                        'notes'           => $noteText,
                        'status'          => 'active',
                        'created_at'      => $paidAt,
                        'updated_at'      => now(),
                    ]);

                    $inst->update([
                        'is_paid'         => true,
                        'paid_at'         => $paidAt,
                        'paid_account_id' => $account->id,
                        'tx_id'           => $txId,
                        'notes'           => $request->notes ? trim($request->notes) : $inst->notes,
                    ]);

                    $total += (float) $inst->amount;
                    $count++;
                }

                if ($count === 0) {
                    throw new \Exception('لا توجد أقساط صالحة للتحصيل (ممكن تكون متحصلة بالفعل).');
                }

                $this->logActivity('collect', 'jamia', "تحصيل {$count} قسط جمعية بإجمالي " . number_format($total, 2) . " ج وإيداعها في {$account->account_name}.");
            });

            return back()->with('success', "تم تحصيل {$count} قسط بنجاح وإيداع المبلغ في الخزنة وقيده في سجل العمليات.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء تحصيل قسط (خصم المبلغ من الخزنة وإلغاء القيد)
     */
    public function cancelInstallmentPayment(Request $request)
    {
        $request->validate(['installment_id' => 'required|integer']);

        try {
            DB::transaction(function () use ($request) {
                $inst = JamiaInstallment::where('id', $request->installment_id)->lockForUpdate()->first();
                if (!$inst || !$inst->is_paid) throw new \Exception('القسط غير محصل.');

                $tx = $inst->tx_id ? DB::table('financial_transactions')->where('id', $inst->tx_id)->first() : null;

                if ($inst->paid_account_id) {
                    // حركات قديمة (expense) كانت خصم من الخزنة => نرجعها. التحصيل (income) => نخصمه.
                    if ($tx && $tx->type === 'expense') {
                        DB::table('accounts')->where('id', $inst->paid_account_id)->increment('balance', $inst->amount);
                    } else {
                        $acc = DB::table('accounts')->where('id', $inst->paid_account_id)->lockForUpdate()->first();
                        if ($acc && $acc->balance < $inst->amount) {
                            throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من مبلغ القسط المطلوب عكسه.");
                        }
                        DB::table('accounts')->where('id', $inst->paid_account_id)->decrement('balance', $inst->amount);
                    }
                }

                if ($inst->tx_id) {
                    DB::table('financial_transactions')->where('id', $inst->tx_id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء تحصيل قسط جمعية',
                        'updated_at'    => now(),
                    ]);
                }

                $inst->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                ]);

                $this->logActivity('cancel', 'jamia', 'إلغاء تحصيل قسط جمعية بقيمة ' . number_format($inst->amount, 2) . ' ج وعكس أثره على الخزنة.');
            });

            return back()->with('success', 'تم إلغاء التحصيل بنجاح وعكس المبلغ من الخزنة.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * قفل والتحقق من أن الخزنة / المحفظة موجودة ونشطة
     */
    private function lockActiveAccount(int $accountId): object
    {
        $account = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->first();
        if (!$account || ($account->status ?? 'active') !== 'active') {
            throw new \Exception('المحفظة / الخزنة المختارة غير موجودة أو غير نشطة.');
        }
        return $account;
    }

    /**
     * تنفيذ استرداد حق الشركة من قبض السهم المتنازل عنه وإيداعه في الخزنة
     */
    private function executeClaimRecovery(JamiaShare $share, object $jamia, int $accountId, float $amount, Carbon $recoveredAt, ?string $notes = null): void
    {
        if (!$share->is_transferred) {
            throw new \Exception('هذا السهم غير متنازل عنه.');
        }
        if ($share->is_claim_recovered) {
            throw new \Exception('تم استرداد حق الشركة من هذا السهم بالفعل.');
        }
        $claimMax = (float) $share->company_claim_amount;
        if ($claimMax <= 0) {
            throw new \Exception('لا يوجد حق مالي مسجل للشركة على هذا السهم.');
        }
        if ($amount <= 0 || $amount > $claimMax || $amount > (float) $share->payout_amount) {
            throw new \Exception('المبلغ المسترد يجب أن يكون أكبر من صفر ولا يتجاوز حق الشركة المستحق (' . number_format($claimMax, 2) . ' ج).');
        }

        $account = $this->lockActiveAccount($accountId);

        DB::table('accounts')->where('id', $account->id)->increment('balance', $amount);

        $noteText = "استرداد حق " . self::COMPANY_NAME . " من جمعية: {$jamia->name} (سهم متنازل عنه إلى: {$share->share_label} - دور {$share->share_number} - حق {$share->paid_months_before_transfer} اسم)";
        if ($notes && trim($notes) !== '') {
            $noteText .= ' | ' . trim($notes);
        }

        $txId = DB::table('financial_transactions')->insertGetId([
            'type'            => 'income',
            'subtype'         => 'jamia_recovery',
            'amount'          => $amount,
            'from_account_id' => null,
            'to_account_id'   => $account->id,
            'ref_type'        => 'jamia_share_claim',
            'ref_id'          => $share->id,
            'notes'           => $noteText,
            'status'          => 'active',
            'created_at'      => $recoveredAt,
            'updated_at'      => now(),
        ]);

        $share->update([
            'is_claim_recovered'   => true,
            'recovered_at'         => $recoveredAt,
            'recovered_account_id' => $account->id,
            'recovered_tx_id'      => $txId,
            'recovered_amount'     => $amount,
        ]);

        $this->logActivity('recover', 'jamia', "استرداد حق الشركة من جمعية {$jamia->name} (عضو: {$share->share_label}) بمبلغ " . number_format($amount, 2) . " ج وإيداعه في {$account->account_name}.");
    }

    /**
     * صرف القبض لصاحب الدور (خارج من الخزنة) أو تسجيل استلام الشركة (داخل للخزنة إجبارياً)
     */
    public function payoutShare(Request $request)
    {
        $request->validate([
            'share_id'           => 'required|integer|exists:jamia_shares,id',
            'account_id'         => 'required|integer|exists:accounts,id',
            'amount'             => 'required|numeric|min:1',
            'paid_at'            => 'nullable|date',
            'notes'              => 'nullable|string|max:500',
            'also_recover_claim' => 'nullable|boolean',
            'recover_account_id' => 'nullable|integer|exists:accounts,id',
            'recover_amount'     => 'nullable|numeric|min:1',
        ], [
            'share_id.required'   => 'يجب تحديد الدور المراد تسجيله.',
            'share_id.exists'     => 'الدور المحدد غير موجود.',
            'account_id.required' => 'اختيار المحفظة / الخزنة إجباري.',
            'account_id.exists'   => 'المحفظة / الخزنة المختارة غير موجودة.',
            'amount.required'     => 'يجب إدخال المبلغ.',
            'amount.min'          => 'المبلغ يجب أن يكون أكبر من صفر.',
            'recover_account_id.exists' => 'خزنة استرداد حق الشركة المختارة غير موجودة.',
        ]);

        $isCompanyShare = false;

        try {
            DB::transaction(function () use ($request, &$isCompanyShare) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');
                if ($share->is_collected) throw new \Exception('تم تسجيل قبض هذا الدور بالفعل.');

                $isCompanyShare = (bool) $share->is_company;
                $jamia  = DB::table('jamias')->where('id', $share->jamia_id)->first();
                $paidAt = $request->paid_at ? Carbon::parse($request->paid_at)->setTimeFrom(now()) : now();
                $amount = round((float) $request->amount, 2);

                if ($amount <= 0 || $amount > (float) $share->payout_amount) {
                    throw new \Exception('المبلغ المدخل (' . number_format($amount, 2) . ' ج) يجب أن يكون بين 1 وقيمة قبض الدور (' . number_format((float) $share->payout_amount, 2) . ' ج).');
                }

                $account = $this->lockActiveAccount((int) $request->account_id);

                if ($share->is_company) {
                    if ($share->is_transferred) {
                        throw new \Exception('تم التنازل عن هذا السهم لعضو بديل.');
                    }

                    DB::table('accounts')->where('id', $account->id)->increment('balance', $amount);

                    $noteText = "قبض جمعية (" . self::COMPANY_NAME . "): {$jamia->name} (دور {$share->share_number} - {$share->share_label})";
                    if ($request->notes) {
                        $noteText .= ' | ' . trim($request->notes);
                    }

                    $txId = DB::table('financial_transactions')->insertGetId([
                        'type'            => 'income',
                        'subtype'         => 'jamia_payout',
                        'amount'          => $amount,
                        'from_account_id' => null,
                        'to_account_id'   => $account->id,
                        'ref_type'        => 'jamia_share',
                        'ref_id'          => $share->id,
                        'notes'           => $noteText,
                        'status'          => 'active',
                        'created_at'      => $paidAt,
                        'updated_at'      => now(),
                    ]);

                    $share->update([
                        'is_collected'         => true,
                        'collected_at'         => $paidAt,
                        'collected_account_id' => $account->id,
                        'collected_tx_id'      => $txId,
                        'collected_amount'     => $amount,
                        'notes'                => $request->notes ? trim($request->notes) : $share->notes,
                    ]);

                    $this->logActivity('payout', 'jamia', "استلام الشركة ({$share->share_label}) قبض جمعية {$jamia->name} (دور {$share->share_number}) بمبلغ " . number_format($amount, 2) . " ج وإيداعه في {$account->account_name}.");
                    return;
                }

                if ($account->balance < $amount) {
                    throw new \Exception("رصيد الخزنة ({$account->account_name}) غير كافٍ. الرصيد الحالي: " . number_format($account->balance, 2) . ' ج.');
                }

                DB::table('accounts')->where('id', $account->id)->decrement('balance', $amount);

                $noteText = "صرف قبض جمعية: {$jamia->name} - للعضو: {$share->share_label} (دور {$share->share_number})";
                if ($request->notes) {
                    $noteText .= ' | ' . trim($request->notes);
                }

                $txId = DB::table('financial_transactions')->insertGetId([
                    'type'            => 'expense',
                    'subtype'         => 'jamia_payout_out',
                    'amount'          => $amount,
                    'from_account_id' => $account->id,
                    'to_account_id'   => null,
                    'ref_type'        => 'jamia_payout_out',
                    'ref_id'          => $share->id,
                    'notes'           => $noteText,
                    'status'          => 'active',
                    'created_at'      => $paidAt,
                    'updated_at'      => now(),
                ]);

                $share->update([
                    'is_collected'         => true,
                    'collected_at'         => $paidAt,
                    'collected_account_id' => $account->id,
                    'collected_tx_id'      => $txId,
                    'collected_amount'     => $amount,
                    'notes'                => $request->notes ? trim($request->notes) : $share->notes,
                ]);

                $this->logActivity('payout', 'jamia', "صرف قبض جمعية {$jamia->name} للعضو {$share->share_label} بمبلغ " . number_format($amount, 2) . " ج من {$account->account_name}.");

                if ($share->is_transferred && $request->boolean('also_recover_claim')) {
                    if (!$request->filled('recover_account_id')) {
                        throw new \Exception('اختيار خزنة إيداع حق الشركة إجباري عند تفعيل الاسترداد مع الصرف.');
                    }
                    $recoverAmount = round((float) ($request->recover_amount ?: $share->company_claim_amount), 2);
                    $this->executeClaimRecovery($share->fresh(), $jamia, (int) $request->recover_account_id, $recoverAmount, $paidAt, $request->notes);
                }
            });

            return back()->with('success', $isCompanyShare ? 'تم تسجيل استلام الشركة وإيداع المبلغ في الخزنة بنجاح.' : 'تم تسجيل صرف القبض بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * التنازل عن سهم الشركة لعضو بديل يدخل مكانها ويتحمل الأقساط القادمة، وتثبيت حق الشركة عن الشهور السابقة
     */
    public function transferShare(Request $request)
    {
        $request->validate([
            'share_id'                    => 'required|integer|exists:jamia_shares,id',
            'transferred_to_name'         => 'required|string|max:150',
            'member_phone'                => 'nullable|string|max:50',
            'transferred_from_month'      => 'required|date',
            'paid_months_before_transfer' => 'required|integer|min:1',
            'transferred_notes'           => 'nullable|string|max:500',
        ], [
            'share_id.required'                    => 'يجب تحديد السهم المراد التنازل عنه.',
            'share_id.exists'                      => 'السهم المحدد غير موجود.',
            'transferred_to_name.required'         => 'يجب إدخال اسم العضو الجديد الذي سيدخل مكان الشركة.',
            'transferred_from_month.required'      => 'يجب تحديد الشهر الذي يبدأ العضو الجديد السداد منه.',
            'paid_months_before_transfer.required' => 'يجب تحديد عدد الأسماء (الشهور) التي دفعتها الشركة قبل التنازل.',
            'paid_months_before_transfer.min'      => 'عدد الأسماء المدفوعة من الشركة يجب أن يكون 1 على الأقل.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');
                if (!$share->is_company) throw new \Exception('التنازل عن الدور وإثبات حق الشركة متاح فقط لأسهم الشركة.');
                if ($share->is_transferred) throw new \Exception('تم التنازل عن هذا السهم مسبقاً.');
                if ($share->is_collected) throw new \Exception('لا يمكن التنازل عن سهم تم قبضه بالفعل.');

                $newName = trim((string) $request->transferred_to_name);
                if ($newName === '' || $newName === self::COMPANY_NAME) {
                    throw new \Exception('اسم العضو البديل غير صالح — اكتب اسم الشخص الذي سيدخل مكان الشركة.');
                }

                $jamia      = Jamia::findOrFail($share->jamia_id);
                $jamiaStart = Carbon::parse($jamia->start_date)->startOfMonth();
                $jamiaEnd   = $jamiaStart->copy()->addMonths($jamia->duration_months - 1)->startOfMonth();
                $fromMonth  = Carbon::parse($request->transferred_from_month)->startOfMonth();

                if ($fromMonth->lt($jamiaStart) || $fromMonth->gt($jamiaEnd)) {
                    throw new \Exception('الشهر المختار لدخول العضو البديل لازم يكون داخل شهور الجمعية.');
                }

                $paidMonths = (int) $request->paid_months_before_transfer;
                if ($paidMonths < 1 || $paidMonths > (int) $jamia->duration_months) {
                    throw new \Exception("عدد الأسماء اللي دفعتها الشركة لازم يكون من 1 لغاية {$jamia->duration_months}.");
                }

                $fromMonthStr   = $fromMonth->toDateString();
                $remainingCount = JamiaInstallment::where('share_id', $share->id)
                    ->whereDate('month_date', '>=', $fromMonthStr)
                    ->count();

                if ($paidMonths + $remainingCount > (int) $jamia->duration_months) {
                    throw new \Exception("عدد الأسماء المدفوعة من الشركة ({$paidMonths}) + الأقساط المتبقية على العضو الجديد ({$remainingCount}) يتجاوز مدة الجمعية ({$jamia->duration_months} شهر). اختَر شهر بداية لاحقاً أو راجع عدد الأسماء.");
                }

                $hasPaid = JamiaInstallment::where('share_id', $share->id)->where('is_paid', true)->exists();
                if ($hasPaid) {
                    throw new \Exception('يوجد أقساط مدفوعة مسجلة على هذا السهم بالفعل.');
                }

                $claimAmount = round($paidMonths * (float) $jamia->monthly_installment, 2);
                if ($claimAmount > (float) $share->payout_amount) {
                    throw new \Exception('حق الشركة المحسوب يتجاوز قيمة قبض الجمعية.');
                }

                $origLabel = $share->share_label;

                // الشهور قبل دخول البديل: معفية عن التحصيل لأن الشركة شالتها ومثبتة كحق مسترد
                JamiaInstallment::where('share_id', $share->id)
                    ->whereDate('month_date', '<', $fromMonthStr)
                    ->update([
                        'is_waived'     => true,
                        'waived_reason' => "تحملتها {$origLabel} قبل دخول {$newName}",
                    ]);

                // الشهور من بداية دخول البديل: مفتوحة للتحصيل الشهري من العضو الجديد
                JamiaInstallment::where('share_id', $share->id)
                    ->whereDate('month_date', '>=', $fromMonthStr)
                    ->update([
                        'is_waived'     => false,
                        'waived_reason' => null,
                    ]);

                $share->update([
                    'share_label'                 => $newName,
                    'member_phone'                => $request->member_phone ? trim($request->member_phone) : $share->member_phone,
                    'is_company'                  => false,
                    'is_transferred'              => true,
                    'transferred_at'              => now(),
                    'transferred_from_month'      => $fromMonthStr,
                    'transferred_to_name'         => $newName,
                    'transferred_notes'           => $request->transferred_notes ? trim($request->transferred_notes) : null,
                    'paid_months_before_transfer' => $paidMonths,
                    'company_claim_amount'        => $claimAmount,
                ]);

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                $this->logActivity('transfer', 'jamia', "تنازل {$origLabel} عن دور {$share->share_number} في جمعية {$jamia->name} لصالح [{$newName}] من شهر {$fromMonthStr} — حق الشركة: {$paidMonths} اسم (" . number_format($claimAmount, 2) . " ج).");
            });

            return back()->with('success', 'تم تسجيل دخول العضو البديل مكان الشركة وتثبيت حق الشركة بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * فك التنازل وإرجاع الدور لسهم الشركة (ممنوع لو اتسدد أقساط أو اتصرف قبض أو اتسترد حق الشركة)
     */
    public function undoTransfer(Request $request)
    {
        $request->validate(['share_id' => 'required|integer|exists:jamia_shares,id']);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share || !$share->is_transferred) {
                    throw new \Exception('هذا السهم غير متنازل عنه.');
                }
                if ($share->is_claim_recovered) {
                    throw new \Exception('لا يمكن فك التنازل بعد استرداد حق الشركة — الغِ الاسترداد أولاً.');
                }
                if ($share->is_collected) {
                    throw new \Exception('لا يمكن فك التنازل بعد صرف القبض — الغِ الصرف أولاً.');
                }
                if (JamiaInstallment::where('share_id', $share->id)->where('is_paid', true)->exists()) {
                    throw new \Exception('لا يمكن فك التنازل لوجود أقساط محصّلة من العضو البديل — الغِ التحصيل أولاً.');
                }

                $jamia = Jamia::findOrFail($share->jamia_id);
                $oldMember = $share->share_label;

                JamiaInstallment::where('share_id', $share->id)->update([
                    'is_waived'     => false,
                    'waived_reason' => null,
                ]);

                $share->update([
                    'share_label'                 => self::COMPANY_NAME,
                    'member_phone'                => null,
                    'is_company'                  => true,
                    'is_transferred'              => false,
                    'transferred_at'              => null,
                    'transferred_from_month'      => null,
                    'transferred_to_name'         => null,
                    'transferred_notes'           => null,
                    'paid_months_before_transfer' => 0,
                    'company_claim_amount'        => 0,
                ]);

                $jamia->update([
                    'company_shares_count' => JamiaShare::where('jamia_id', $jamia->id)->where('is_company', true)->count(),
                ]);

                $this->logActivity('undo_transfer', 'jamia', "فك التنازل عن دور {$share->share_number} ({$oldMember}) وإعادته لـ" . self::COMPANY_NAME . " في جمعية {$jamia->name}.");
            });

            return back()->with('success', 'تم فك التنازل وإرجاع الدور لـ' . self::COMPANY_NAME . '.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * استرداد حق الشركة من العضو البديل وإيداع الفلوس في الخزنة المختارة (إجباري)
     */
    public function recoverClaim(Request $request)
    {
        $request->validate([
            'share_id'     => 'required|integer|exists:jamia_shares,id',
            'account_id'   => 'required|integer|exists:accounts,id',
            'amount'       => 'required|numeric|min:1',
            'recovered_at' => 'nullable|date',
            'notes'        => 'nullable|string|max:500',
        ], [
            'share_id.required'   => 'يجب تحديد السهم المتنازل عنه.',
            'share_id.exists'     => 'السهم المحدد غير موجود.',
            'account_id.required' => 'اختيار المحفظة / الخزنة لإيداع حق الشركة إجباري.',
            'account_id.exists'   => 'المحفظة / الخزنة المختارة غير موجودة.',
            'amount.required'     => 'يجب إدخال المبلغ المسترد.',
            'amount.min'          => 'المبلغ المسترد يجب أن يكون أكبر من صفر.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('السهم غير موجود.');

                $jamia = DB::table('jamias')->where('id', $share->jamia_id)->first();
                $recoveredAt = $request->recovered_at ? Carbon::parse($request->recovered_at)->setTimeFrom(now()) : now();

                $this->executeClaimRecovery($share, $jamia, (int) $request->account_id, round((float) $request->amount, 2), $recoveredAt, $request->notes);
            });

            return back()->with('success', 'تم تحصيل حق الشركة وإيداع المبلغ في الخزنة وقيده في سجل العمليات بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء تحصيل حق الشركة من الخزنة وإرجاع الحالة لغير مسترد
     */
    public function cancelRecovery(Request $request)
    {
        $request->validate(['share_id' => 'required|integer|exists:jamia_shares,id']);

        try {
            DB::transaction(function () use ($request) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share || !$share->is_claim_recovered) {
                    throw new \Exception('لم يتم تسجيل استرداد حق الشركة لهذا السهم.');
                }

                if ($share->recovered_account_id && $share->recovered_amount > 0) {
                    $acc = DB::table('accounts')->where('id', $share->recovered_account_id)->lockForUpdate()->first();
                    if ($acc && $acc->balance < $share->recovered_amount) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب عكسه.");
                    }
                    DB::table('accounts')->where('id', $share->recovered_account_id)->decrement('balance', $share->recovered_amount);
                }

                if ($share->recovered_tx_id) {
                    DB::table('financial_transactions')->where('id', $share->recovered_tx_id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء استرداد حق الشركة من جمعية',
                        'updated_at'    => now(),
                    ]);
                }

                $share->update([
                    'is_claim_recovered'   => false,
                    'recovered_at'         => null,
                    'recovered_account_id' => null,
                    'recovered_tx_id'      => null,
                    'recovered_amount'     => 0,
                ]);

                $this->logActivity('cancel_recovery', 'jamia', "إلغاء استرداد حق الشركة عن سهم [{$share->share_label}] وعكس الأثر المالي.");
            });

            return back()->with('success', 'تم إلغاء تحصيل حق الشركة وخصم المبلغ من الخزنة.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إلغاء صرف القبض أو استلام الشركة وإرجاع الأثر المالي للخزنة
     */
    public function cancelPayout(Request $request)
    {
        $request->validate(['share_id' => 'required|integer']);

        $isCompanyShare = false;

        try {
            DB::transaction(function () use ($request, &$isCompanyShare) {
                $share = JamiaShare::where('id', $request->share_id)->lockForUpdate()->first();
                if (!$share) throw new \Exception('العضو غير موجود.');
                if (!$share->is_collected) throw new \Exception('لم يتم صرف القبض لهذا الدور.');

                $isCompanyShare = (bool) $share->is_company;

                if ($share->collected_tx_id) {
                    $tx = DB::table('financial_transactions')->where('id', $share->collected_tx_id)->first();
                    if ($share->collected_account_id && $share->collected_amount > 0) {
                        if ($tx && $tx->type === 'income') {
                            // قبض الشركة كان إيداع => نخصمه من الخزنة
                            $acc = DB::table('accounts')->where('id', $share->collected_account_id)->lockForUpdate()->first();
                            if ($acc && $acc->balance < $share->collected_amount) {
                                throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب عكسه.");
                            }
                            DB::table('accounts')->where('id', $share->collected_account_id)->decrement('balance', $share->collected_amount);
                        } else {
                            DB::table('accounts')->where('id', $share->collected_account_id)->increment('balance', $share->collected_amount);
                        }
                    }

                    DB::table('financial_transactions')->where('id', $share->collected_tx_id)->update([
                        'status'        => 'cancelled',
                        'cancelled_at'  => now(),
                        'cancel_reason' => 'إلغاء صرف/استلام قبض جمعية',
                        'updated_at'    => now(),
                    ]);
                }

                $share->update([
                    'is_collected'         => false,
                    'collected_at'         => null,
                    'collected_account_id' => null,
                    'collected_tx_id'      => null,
                    'collected_amount'     => 0,
                ]);

                $this->logActivity('cancel_payout', 'jamia', "إلغاء قبض [{$share->share_label}] وعكس الأثر المالي.");
            });

            return back()->with('success', $isCompanyShare ? 'تم إلغاء تسجيل استلام الشركة وخصم المبلغ من الخزنة.' : 'تم إلغاء صرف القبض بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * مزامنة أي حركات تم إلغاؤها من سجل العمليات لضمان مطابقة الحالة
     */
    private function syncWithCancelledTransactions()
    {
        // 1. أدوار القبض المصروفة التي أُلغيت حركتها
        $collectedShares = DB::table('jamia_shares')->where('is_collected', true)->whereNotNull('collected_tx_id')->get();
        foreach ($collectedShares as $cs) {
            $tx = DB::table('financial_transactions')->where('id', $cs->collected_tx_id)->first();
            if (!$tx || $tx->status === 'cancelled') {
                DB::table('jamia_shares')->where('id', $cs->id)->update([
                    'is_collected'         => false,
                    'collected_at'         => null,
                    'collected_account_id' => null,
                    'collected_tx_id'      => null,
                    'collected_amount'     => 0,
                    'updated_at'           => now(),
                ]);
            }
        }

        // 2. (قديم) الأسهم المسترد حقها التي أُلغيت حركتها
        $recoveredShares = DB::table('jamia_shares')->where('is_claim_recovered', true)->whereNotNull('recovered_tx_id')->get();
        foreach ($recoveredShares as $rs) {
            $tx = DB::table('financial_transactions')->where('id', $rs->recovered_tx_id)->first();
            if (!$tx || $tx->status === 'cancelled') {
                DB::table('jamia_shares')->where('id', $rs->id)->update([
                    'is_claim_recovered'   => false,
                    'recovered_at'         => null,
                    'recovered_account_id' => null,
                    'recovered_tx_id'      => null,
                    'recovered_amount'     => 0,
                    'updated_at'           => now(),
                ]);
            }
        }

        // 3. الأقساط المحصلة التي أُلغيت حركتها
        $paidInsts = DB::table('jamia_installments')->where('is_paid', true)->whereNotNull('tx_id')->get();
        foreach ($paidInsts as $pi) {
            $tx = DB::table('financial_transactions')->where('id', $pi->tx_id)->first();
            if (!$tx || $tx->status === 'cancelled') {
                DB::table('jamia_installments')->where('id', $pi->id)->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    /**
     * طباعة كشف التحصيل والصرف لشهر محدد
     */
    public function printMonth(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $monthDate  = Carbon::parse($selectedMonth . '-01');
        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();

        $monthInstallments = DB::table('jamia_installments as ji')
            ->join('jamias as j', 'ji.jamia_id', '=', 'j.id')
            ->join('jamia_shares as js', 'ji.share_id', '=', 'js.id')
            ->leftJoin('accounts as a', 'ji.paid_account_id', '=', 'a.id')
            ->whereDate('ji.month_date', $monthStart)
            ->where('ji.is_waived', false)
            ->where('js.is_company', false)
            ->where('j.status', 'active')
            ->select(
                'ji.*',
                'j.name as jamia_name',
                'j.collection_start_day',
                'j.collection_end_day',
                'js.share_label',
                'js.share_number',
                'js.member_phone',
                'a.account_name as paid_account_name'
            )
            ->orderBy('j.name')
            ->orderBy('js.share_number')
            ->get()
            ->map(function ($inst) {
                $inst->status = self::installmentStatus($inst);
                return $inst;
            });

        $monthShares = DB::table('jamia_shares as js')
            ->join('jamias as j', 'js.jamia_id', '=', 'j.id')
            ->leftJoin('accounts as ca', 'js.collected_account_id', '=', 'ca.id')
            ->leftJoin('accounts as ra', 'js.recovered_account_id', '=', 'ra.id')
            ->whereDate('js.payout_month', $monthStart)
            ->where('j.status', 'active')
            ->select(
                'js.*',
                'j.name as jamia_name',
                'ca.account_name as collected_account_name',
                'ra.account_name as recovered_account_name'
            )
            ->orderBy('j.name')
            ->get();

        $totalDue       = (float) $monthInstallments->sum('amount');
        $totalCollected = (float) $monthInstallments->where('is_paid', true)->sum('amount');
        $memberPayouts  = $monthShares->where('is_company', false);
        $totalPayout    = (float) $memberPayouts->sum('payout_amount');
        $totalPaidOut   = (float) $memberPayouts->where('is_collected', true)->sum('collected_amount');
        $companyName    = self::COMPANY_NAME;

        return view('cashflows_print', compact(
            'selectedMonth',
            'monthDate',
            'monthInstallments',
            'monthShares',
            'totalDue',
            'totalCollected',
            'totalPayout',
            'totalPaidOut',
            'companyName'
        ));
    }

    /**
     * طباعة كشف تفصيلي لجمعية واحدة (الأعضاء + مصفوفة التحصيل)
     */
    public function printJamia($id)
    {
        $jamia = Jamia::with([
            'shares' => fn ($q) => $q->orderBy('share_number'),
            'installments',
        ])->findOrFail($id);

        $start  = Carbon::parse($jamia->start_date)->startOfMonth();
        $months = [];
        for ($i = 0; $i < $jamia->duration_months; $i++) {
            $months[] = $start->copy()->addMonths($i);
        }

        // مصفوفة: [share_id][Y-m-d] => installment
        $matrix = [];
        foreach ($jamia->installments as $inst) {
            $inst->collection_start_day = $jamia->collection_start_day;
            $inst->collection_end_day   = $jamia->collection_end_day;
            $inst->status = self::installmentStatus($inst);
            $matrix[$inst->share_id][Carbon::parse($inst->month_date)->toDateString()] = $inst;
        }

        $companyName = self::COMPANY_NAME;

        return view('cashflows_jamia_print', compact('jamia', 'months', 'matrix', 'companyName'));
    }

    /**
     * حذف جمعية بالكامل
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $jamia = Jamia::findOrFail($id);
                $hasPaid = JamiaInstallment::where('jamia_id', $id)->where('is_paid', true)->exists();
                $hasCollected = JamiaShare::where('jamia_id', $id)->where(function ($q) {
                    $q->where('is_collected', true)->orWhere('is_claim_recovered', true);
                })->exists();

                if ($hasPaid || $hasCollected) {
                    throw new \Exception('لا يمكن حذف جمعية فيها تحصيلات أو صرف قبض. الغِ العمليات أولاً إذا رغبت.');
                }

                $jamia->delete();
                $this->logActivity('delete', 'jamia', "تم حذف جمعية: {$jamia->name}.");
            });

            return back()->with('success', 'تم حذف الجمعية بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
