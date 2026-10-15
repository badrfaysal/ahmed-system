<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GasStationController extends SystemController
{
    public function gasStation(Request $request)
    {
        $companies       = DB::table('transport_companies')->get();
        $stations        = DB::table('gas_stations')->get();
        $items           = DB::table('items')->get();
        $payment_methods = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $deductions      = DB::table('fuel_deductions')->get();

        // 💡 المستحقات اللي لينا من كل شركة نقل (ديون البنزينة على الشركة) — مفتاحها اسم الشركة
        $companyDues = DB::table('installments')
            ->where('category', 'بنزينة')
            ->where('remaining_balance', '>', 0)
            ->groupBy('customer_name')
            ->selectRaw('customer_name, SUM(remaining_balance) as total')
            ->pluck('total', 'customer_name');

        // 💡 الديون اللي علينا لكل محطة وقود (ثمن الوقود المستحق للمحطة) — مفتاحها اسم المحطة
        $stationDebts = DB::table('company_debts')
            ->where('category', 'وقود')
            ->where('remaining_balance', '>', 0)
            ->groupBy('creditor_name')
            ->selectRaw('creditor_name, SUM(remaining_balance) as total')
            ->pluck('total', 'creditor_name');

        // ══════════════════════════════════════════════
        // ══════════════════════════════════════════════
        // 📊 بيانات وإحصائيات الشاحنات والأساطيل
        // ══════════════════════════════════════════════
        // 1) قائمة الشاحنات والمركبات وآخر قراءة عداد مسجلة
        $knownVehicles = DB::table('fuel_transactions')
            ->selectRaw("
                COALESCE(NULLIF(car_number, '-'), driver_name) as vehicle_key,
                MAX(car_number) as car_number,
                MAX(truck_name) as truck_name,
                MAX(driver_name) as driver_name,
                MAX(odometer) as last_odometer,
                MAX(CASE WHEN is_full_tank = 1 THEN odometer ELSE NULL END) as last_full_odometer,
                COUNT(*) as total_trips,
                SUM(liters) as total_liters
            ")
            ->whereRaw("((car_number IS NOT NULL AND car_number != '' AND car_number != '-') OR (driver_name IS NOT NULL AND driver_name != '' AND driver_name != '-'))")
            ->groupBy(DB::raw('1'))
            ->orderByDesc('total_trips')
            ->get();

        // 2) استعلام سجل العمليات مع الفلاتر
        $query = DB::table('fuel_transactions as ft')
            ->leftJoin('transport_companies as tc', 'ft.company_id', '=', 'tc.id')
            ->leftJoin('gas_stations as gs', 'ft.station_id', '=', 'gs.id')
            ->select(
                'ft.*',
                DB::raw("COALESCE(tc.company_name, '—') as company_name"),
                DB::raw("COALESCE(gs.station_name, '—') as station_name")
            )
            ->orderByDesc('ft.id');

        // إعداد دالة الفلترة الشاملة لتطبيقها على جميع استعلامات الشاشة والتقارير
        $applyFilters = function($q, $prefix = '') use ($request) {
            $p = $prefix ? rtrim($prefix, '.') . '.' : '';

            if ($request->filled('filter_company')) {
                $q->where($p . 'company_id', $request->filter_company);
            }
            if ($request->filled('filter_station')) {
                $q->where($p . 'station_id', $request->filter_station);
            }
            if ($request->filled('filter_car')) {
                $carVal = trim($request->filter_car);
                $q->where(function($sub) use ($p, $carVal) {
                    $sub->where($p . 'car_number', 'like', '%' . $carVal . '%')
                        ->orWhere($p . 'truck_name', 'like', '%' . $carVal . '%');
                });
            }
            if ($request->filled('filter_driver')) {
                $driverVal = trim($request->filter_driver);
                $q->where(function($sub) use ($p, $driverVal) {
                    $sub->where($p . 'driver_name', 'like', '%' . $driverVal . '%')
                        ->orWhere($p . 'car_number', 'like', '%' . $driverVal . '%')
                        ->orWhere($p . 'truck_name', 'like', '%' . $driverVal . '%');
                });
            }
            if ($request->filled('filter_from')) {
                $q->whereDate($p . 'created_at', '>=', $request->filter_from);
            }
            if ($request->filled('filter_to')) {
                $q->whereDate($p . 'created_at', '<=', $request->filter_to);
            }
            return $q;
        };

        // تطبيق الفلتر على استعلام العمليات
        $applyFilters($query, 'ft');

        // إجمالي الإحصائيات الشاملة (KPIs) مع تطبيق الفلاتر
        $kpiQuery = DB::table('fuel_transactions');
        $applyFilters($kpiQuery);
        $kpi = $kpiQuery
            ->selectRaw('
                COUNT(*) as total_ops,
                COALESCE(SUM(liters), 0) as total_liters,
                COALESCE(SUM(cash_advance), 0) as total_advance,
                COALESCE(SUM(total_on_company), 0) as total_cost,
                COALESCE(SUM(ahmed_profit), 0) as total_profit,
                COALESCE(AVG(CASE WHEN distance_km > 0 AND consumption_rate > 0 THEN consumption_rate END), 0) as avg_consumption,
                COALESCE(SUM(distance_km), 0) as total_distance
            ')
            ->first();

        // أكثر الشاحنات استهلاكاً مع تطبيق الفلاتر
        $topVehiclesQuery = DB::table('fuel_transactions');
        $applyFilters($topVehiclesQuery);
        $topVehicles = $topVehiclesQuery
            ->select(
                DB::raw("COALESCE(NULLIF(car_number, '-'), driver_name) as driver_name"),
                DB::raw('MAX(car_number) as car_number'),
                DB::raw('MAX(truck_name) as truck_name'),
                DB::raw('SUM(liters) as liters'),
                DB::raw('SUM(total_on_company) as total_spent'),
                DB::raw('COUNT(*) as fills_count')
            )
            ->whereRaw("((car_number IS NOT NULL AND car_number != '' AND car_number != '-') OR (driver_name IS NOT NULL AND driver_name != '' AND driver_name != '-'))")
            ->groupBy(DB::raw('1'))
            ->orderByDesc('liters')
            ->limit(6)
            ->get();

        // 📊 تقرير تفصيلي شامل لأداء وكفاءة أسطول الشاحنات مع تطبيق الفلاتر
        $fleetReportQuery = DB::table('fuel_transactions');
        $applyFilters($fleetReportQuery);
        $detailedFleetReport = $fleetReportQuery
            ->select(
                DB::raw("COALESCE(NULLIF(car_number, '-'), driver_name) as driver_name"),
                DB::raw('MAX(car_number) as car_number'),
                DB::raw('MAX(truck_name) as truck_name'),
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(CASE WHEN is_full_tank = 1 THEN 1 ELSE 0 END) as full_fills'),
                DB::raw('SUM(CASE WHEN is_full_tank = 0 THEN 1 ELSE 0 END) as partial_fills'),
                DB::raw('SUM(liters) as total_liters'),
                DB::raw('SUM(distance_km) as total_dist'),
                DB::raw('SUM(total_on_company) as total_cost'),
                DB::raw('AVG(CASE WHEN consumption_rate > 0 THEN consumption_rate END) as avg_efficiency'),
                DB::raw('AVG(CASE WHEN cost_per_km > 0 THEN cost_per_km END) as avg_cost_km'),
                DB::raw('MAX(id) as last_trip_id'),
                DB::raw('MAX(created_at) as last_trip_at')
            )
            ->whereRaw("((car_number IS NOT NULL AND car_number != '' AND car_number != '-') OR (driver_name IS NOT NULL AND driver_name != '' AND driver_name != '-'))")
            ->groupBy(DB::raw('1'))
            ->orderByDesc('last_trip_id')
            ->get();

        // 📊 تقرير تفصيلي منفصل للسيارات والشاحنات (مفلترة بالعربيات)
        $carsReportQuery = DB::table('fuel_transactions');
        $applyFilters($carsReportQuery);
        $detailedCarsReport = $carsReportQuery
            ->select(
                'car_number',
                DB::raw('MAX(truck_name) as truck_name'),
                DB::raw('GROUP_CONCAT(DISTINCT driver_name SEPARATOR "، ") as drivers_list'),
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(CASE WHEN is_full_tank = 1 THEN 1 ELSE 0 END) as full_fills'),
                DB::raw('SUM(CASE WHEN is_full_tank = 0 THEN 1 ELSE 0 END) as partial_fills'),
                DB::raw('SUM(liters) as total_liters'),
                DB::raw('SUM(distance_km) as total_dist'),
                DB::raw('SUM(total_on_company) as total_cost'),
                DB::raw('AVG(CASE WHEN consumption_rate > 0 THEN consumption_rate END) as avg_efficiency'),
                DB::raw('AVG(CASE WHEN cost_per_km > 0 THEN cost_per_km END) as avg_cost_km'),
                DB::raw('MAX(id) as last_trip_id'),
                DB::raw('MAX(created_at) as last_trip_at')
            )
            ->whereNotNull('car_number')
            ->where('car_number', '!=', '')
            ->where('car_number', '!=', '-')
            ->groupBy('car_number')
            ->orderByDesc('last_trip_id')
            ->get();

        // 📊 تقرير تفصيلي منفصل للسائقين (مفلترة بالسواقين)
        $driversReportQuery = DB::table('fuel_transactions');
        $applyFilters($driversReportQuery);
        $detailedDriversReport = $driversReportQuery
            ->select(
                'driver_name',
                DB::raw('GROUP_CONCAT(DISTINCT NULLIF(car_number, "-") SEPARATOR "، ") as cars_list'),
                DB::raw('MAX(truck_name) as truck_name'),
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(CASE WHEN is_full_tank = 1 THEN 1 ELSE 0 END) as full_fills'),
                DB::raw('SUM(CASE WHEN is_full_tank = 0 THEN 1 ELSE 0 END) as partial_fills'),
                DB::raw('SUM(liters) as total_liters'),
                DB::raw('SUM(distance_km) as total_dist'),
                DB::raw('SUM(total_on_company) as total_cost'),
                DB::raw('AVG(CASE WHEN consumption_rate > 0 THEN consumption_rate END) as avg_efficiency'),
                DB::raw('AVG(CASE WHEN cost_per_km > 0 THEN cost_per_km END) as avg_cost_km'),
                DB::raw('MAX(id) as last_trip_id'),
                DB::raw('MAX(created_at) as last_trip_at')
            )
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->where('driver_name', '!=', '-')
            ->groupBy('driver_name')
            ->orderByDesc('last_trip_id')
            ->get();

        // 🏢 تقرير استهلاك ومسحوبات شركات النقل مع تطبيق الفلاتر
        $companyBreakdownQuery = DB::table('fuel_transactions as ft')
            ->leftJoin('transport_companies as tc', 'ft.company_id', '=', 'tc.id');
        $applyFilters($companyBreakdownQuery, 'ft');
        $companyBreakdown = $companyBreakdownQuery
            ->select(
                DB::raw('COALESCE(tc.company_name, "بدون شركة") as company_name'),
                DB::raw('COUNT(*) as total_ops'),
                DB::raw('SUM(ft.liters) as total_liters'),
                DB::raw('SUM(ft.distance_km) as total_distance'),
                DB::raw('SUM(ft.cash_advance) as total_advance'),
                DB::raw('SUM(ft.total_on_company) as total_amount'),
                DB::raw('SUM(ft.ahmed_profit) as total_profit')
            )
            ->groupBy('tc.id', 'tc.company_name')
            ->orderByDesc('total_liters')
            ->limit(10)
            ->get();

        // 🚨 رادار كشف الشبهات والهدر (Suspicious Activity Alerts)
        // جلب المعايير من الإعدادات مع نسبة التفاوت والسماح %
        $radarMinEff = (float) \App\Services\SystemSetting::get('fuel_radar_min_efficiency', 4.0);
        $radarMaxCostKm = (float) \App\Services\SystemSetting::get('fuel_radar_max_cost_km', 7.0);
        $radarTolerancePct = (float) \App\Services\SystemSetting::get('fuel_radar_tolerance_pct', 10.0);

        // الحدود الفعلية مع تطبيق نسبة السماح (مثلاً: 4.0 - 10% = 3.6 كم/لتر ، و 7.0 + 10% = 7.7 ج/كم)
        $effectiveMinEff = round($radarMinEff * (1 - ($radarTolerancePct / 100)), 2);
        $effectiveMaxCostKm = round($radarMaxCostKm * (1 + ($radarTolerancePct / 100)), 2);

        $suspiciousAlerts = [];
        $suspiciousMap = [];

        // أ) كشف استهلاك مفرط (أقل من الحد المعتمد بعد احتساب نسبة السماح) مع تطبيق الفلاتر
        $abnormalQuery = DB::table('fuel_transactions as ft')
            ->leftJoin('transport_companies as tc', 'ft.company_id', '=', 'tc.id')
            ->leftJoin('gas_stations as gs', 'ft.station_id', '=', 'gs.id')
            ->where('ft.is_full_tank', 1)
            ->whereNotNull('ft.consumption_rate')
            ->where('ft.consumption_rate', '>', 0)
            ->where('ft.consumption_rate', '<', $effectiveMinEff);
        $applyFilters($abnormalQuery, 'ft');
        $abnormalConsumption = $abnormalQuery
            ->select('ft.*', 'tc.company_name', 'gs.station_name')
            ->orderByDesc('ft.id')
            ->limit(15)
            ->get();

        foreach ($abnormalConsumption as $ac) {
            $truckTitle = ($ac->car_number && $ac->car_number != '-') ? $ac->car_number . ($ac->truck_name ? " ({$ac->truck_name})" : '') : $ac->driver_name;
            $reason = '⚠️ استهلاك مفرط للوقود (' . number_format($ac->consumption_rate, 2) . ' كم/لتر) أقل من المعيار (' . $effectiveMinEff . ' كم/لتر بسماح ' . $radarTolerancePct . '%) لمسافة ' . number_format($ac->distance_km) . ' كم فقط بـ ' . number_format($ac->liters) . ' لتر.';
            $suspiciousAlerts[] = (object) [
                'id'               => $ac->id,
                'driver_name'      => $ac->driver_name,
                'car_number'       => $ac->car_number,
                'truck_name'       => $ac->truck_name,
                'vehicle_title'    => $truckTitle,
                'company_name'     => $ac->company_name ?? '—',
                'station_name'     => $ac->station_name ?? '—',
                'liters'           => $ac->liters,
                'distance_km'      => $ac->distance_km ?? 0,
                'consumption_rate' => $ac->consumption_rate ?? 0,
                'cost_per_km'      => $ac->cost_per_km ?? 0,
                'cost'             => $ac->total_on_company,
                'created_at'       => $ac->created_at,
                'badge'            => 'استهلاك مفرط (شبهة هدر)',
                'severity'         => 'danger',
                'reason'           => $reason,
            ];
            $suspiciousMap[$ac->id] = $reason;
        }

        // ب) كشف تكلفة الكيلومتر المرتفعة بشكل شاذ (> الحد المعتمد بعد احتساب نسبة السماح) مع تطبيق الفلاتر
        $highCostQuery = DB::table('fuel_transactions as ft')
            ->leftJoin('transport_companies as tc', 'ft.company_id', '=', 'tc.id')
            ->leftJoin('gas_stations as gs', 'ft.station_id', '=', 'gs.id')
            ->whereNotNull('ft.cost_per_km')
            ->where('ft.cost_per_km', '>', $effectiveMaxCostKm)
            ->whereNotIn('ft.id', array_keys($suspiciousMap));
        $applyFilters($highCostQuery, 'ft');
        $highCostTrips = $highCostQuery
            ->select('ft.*', 'tc.company_name', 'gs.station_name')
            ->orderByDesc('ft.id')
            ->limit(10)
            ->get();

        foreach ($highCostTrips as $hc) {
            $truckTitle = ($hc->car_number && $hc->car_number != '-') ? $hc->car_number . ($hc->truck_name ? " ({$hc->truck_name})" : '') : $hc->driver_name;
            $reason = '💸 تكلفة كيلومتر مرتفعة جداً (' . number_format($hc->cost_per_km, 2) . ' ج/كم) أعلى من المعيار (' . $effectiveMaxCostKm . ' ج/كم بسماح ' . $radarTolerancePct . '%) إجمالي التكلفة ' . number_format($hc->total_on_company) . ' ج.';
            $suspiciousAlerts[] = (object) [
                'id'               => $hc->id,
                'driver_name'      => $hc->driver_name,
                'car_number'       => $hc->car_number,
                'truck_name'       => $hc->truck_name,
                'vehicle_title'    => $truckTitle,
                'company_name'     => $hc->company_name ?? '—',
                'station_name'     => $hc->station_name ?? '—',
                'liters'           => $hc->liters,
                'distance_km'      => $hc->distance_km ?? 0,
                'consumption_rate' => $hc->consumption_rate ?? 0,
                'cost_per_km'      => $hc->cost_per_km ?? 0,
                'cost'             => $hc->total_on_company,
                'created_at'       => $hc->created_at,
                'badge'            => 'تكلفة كيلومتر شاذة',
                'severity'         => 'warning',
                'reason'           => $reason,
            ];
            $suspiciousMap[$hc->id] = $reason;
        }

        // توزيع أنواع الوقود مع تطبيق الفلاتر
        $fuelDistQuery = DB::table('fuel_transactions');
        $applyFilters($fuelDistQuery);
        $fuelDistribution = $fuelDistQuery
            ->select('fuel_type', DB::raw('SUM(liters) as total_liters'), DB::raw('COUNT(*) as ops_count'))
            ->groupBy('fuel_type')
            ->orderByDesc('total_liters')
            ->get();

        // السجل الأخير للعمليات
        $transactions = $query->limit(200)->get();

        // قوائم أرقام السيارات وأسماء السائقين الفريدة لاقتراحات الفلترة
        $filterCarOptions = DB::table('fuel_transactions')
            ->whereNotNull('car_number')
            ->where('car_number', '!=', '')
            ->where('car_number', '!=', '-')
            ->distinct()
            ->orderBy('car_number')
            ->pluck('car_number');

        $filterDriverOptions = DB::table('fuel_transactions')
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->where('driver_name', '!=', '-')
            ->distinct()
            ->orderBy('driver_name')
            ->pluck('driver_name');

        return view('gas_station', compact(
            'companies', 'stations', 'items', 'payment_methods', 'deductions',
            'companyDues', 'stationDebts', 'knownVehicles', 'transactions', 'kpi',
            'topVehicles', 'fuelDistribution', 'detailedFleetReport', 'detailedCarsReport', 'detailedDriversReport', 'companyBreakdown',
            'suspiciousAlerts', 'suspiciousMap',
            'radarMinEff', 'radarMaxCostKm', 'radarTolerancePct', 'effectiveMinEff', 'effectiveMaxCostKm',
            'filterCarOptions', 'filterDriverOptions'
        ));
    }

    /**
     * تقرير كفاءة واستهلاك الشاحنة / السائق المفصل (مطابق لتطبيق Drivvo)
     */
    public function getDriverReport(Request $request)
    {
        $driverName = trim($request->query('driver', ''));
        if (!$driverName) {
            return response()->json(['error' => 'اسم السائق أو رقم الشاحنة مطلوب'], 422);
        }

        $type = $request->query('type', 'auto'); // 'driver', 'car', or 'auto'

        // تحديد ما إذا كان المعامل يمثل سيارة أم سائق في حالة auto
        $isCar = false;
        if ($type === 'car') {
            $isCar = true;
        } elseif ($type === 'driver') {
            $isCar = false;
        } else {
            // فحص هل الاسم يتطابق مع رقم سيارة موجود
            $carExists = DB::table('fuel_transactions')
                ->where('car_number', $driverName)
                ->orWhere('truck_name', $driverName)
                ->exists();
            $isCar = $carExists;
        }

        $txQuery = DB::table('fuel_transactions as ft')
            ->leftJoin('transport_companies as tc', 'ft.company_id', '=', 'tc.id')
            ->leftJoin('gas_stations as gs', 'ft.station_id', '=', 'gs.id');

        if ($type === 'driver') {
            $txQuery->where('ft.driver_name', $driverName);
        } elseif ($type === 'car') {
            $txQuery->where(function($q) use ($driverName) {
                $q->where('ft.car_number', $driverName)
                  ->orWhere('ft.truck_name', $driverName);
            });
        } else {
            $txQuery->where(function($q) use ($driverName) {
                $q->where('ft.driver_name', $driverName)
                  ->orWhere('ft.car_number', $driverName)
                  ->orWhere('ft.truck_name', $driverName)
                  ->orWhere(DB::raw('COALESCE(NULLIF(ft.car_number, "-"), ft.driver_name)'), $driverName);
            });
        }

        if ($request->filled('from')) {
            $txQuery->whereDate('ft.created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $txQuery->whereDate('ft.created_at', '<=', $request->to);
        }

        $transactions = (clone $txQuery)
            ->select('ft.*', 'tc.company_name', 'gs.station_name')
            ->orderByDesc('ft.id')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json(['error' => 'لا توجد بيانات مسجلة لهذا السائق / الشاحنة في الفترة المحددة'], 404);
        }

        $totalRefuelings  = $transactions->count();
        $fullTankCount    = $transactions->where('is_full_tank', 1)->count();
        $partialTankCount = $transactions->where('is_full_tank', 0)->count();
        $fullTankPct      = $totalRefuelings > 0 ? round(($fullTankCount / $totalRefuelings) * 100, 1) : 0;

        $totalSpent    = (float) $transactions->sum('total_on_company');
        $totalLiters   = (float) $transactions->sum('liters');
        $totalDistance = (float) $transactions->sum('distance_km');

        $firstDate = \Carbon\Carbon::parse($transactions->min('created_at'));
        $lastDate  = \Carbon\Carbon::parse($transactions->max('created_at'));
        $daysSpan  = max(1, $firstDate->diffInDays($lastDate) ?: 1);

        // بطاقة التكلفة
        $costByDay = round($totalSpent / $daysSpan, 2);
        $costByKm  = $totalDistance > 0 ? round($totalSpent / $totalDistance, 2) : 0;

        // بطاقة حجم الوقود
        $volPerRefueling = $totalRefuelings > 0 ? round($totalLiters / $totalRefuelings, 1) : 0;
        $volByDay        = round($totalLiters / $daysSpan, 1);

        // متوسط وأسعار اللتر
        $prices = $transactions->pluck('fuel_price')->filter(fn($p) => $p > 0);
        $avgPrice = $prices->isNotEmpty() ? round($prices->avg(), 2) : ($totalLiters > 0 ? round($totalSpent / $totalLiters, 2) : 0);
        $minPrice = $prices->isNotEmpty() ? round($prices->min(), 2) : $avgPrice;
        $maxPrice = $prices->isNotEmpty() ? round($prices->max(), 2) : $avgPrice;

        // كفاءة الاستهلاك
        $rates = $transactions->pluck('consumption_rate')->filter(fn($r) => $r > 0);
        $avgRate = $rates->isNotEmpty() ? round($rates->avg(), 2) : 0;
        $minRate = $rates->isNotEmpty() ? round($rates->min(), 2) : 0;
        $maxRate = $rates->isNotEmpty() ? round($rates->max(), 2) : 0;

        // آخر مؤشرات مسجلة
        $lastTx = $transactions->first();
        $lastRate = $lastTx && $lastTx->consumption_rate > 0 ? round($lastTx->consumption_rate, 2) : null;
        $lastCostKm = $lastTx && $lastTx->cost_per_km > 0 ? round($lastTx->cost_per_km, 2) : null;
        $lastOdometer = $lastTx ? $lastTx->odometer : null;

        // نمط ومعدل التفويل
        $avgDistBetweenRefuelings = $fullTankCount > 0 ? round($totalDistance / $fullTankCount, 1) : round($totalDistance / max(1, $totalRefuelings), 1);
        $refuelingFrequencyDays = round($daysSpan / max(1, $totalRefuelings), 2);
        $monthlyProjection = round($costByDay * 30, 2);

        // توزيع محطات الوقود
        $stationBreakdown = $transactions->groupBy('station_name')->map(function ($items, $stName) use ($totalLiters) {
            $stLiters = $items->sum('liters');
            $stCost   = $items->sum('total_on_company');
            $pct      = $totalLiters > 0 ? round(($stLiters / $totalLiters) * 100, 1) : 0;
            return [
                'station_name' => $stName ?: 'محطة غير محددة',
                'count'        => $items->count(),
                'liters'       => round($stLiters, 1),
                'cost'         => round($stCost, 2),
                'avg_price'    => $stLiters > 0 ? round($stCost / $stLiters, 2) : 0,
                'percentage'   => $pct,
            ];
        })->values();

        // رسم بياني للمصروفات الشهرية
        $monthlyExpenses = $transactions->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('Y-m');
        })->map(function ($items, $month) {
            return [
                'month'  => $month,
                'cost'   => round($items->sum('total_on_company'), 2),
                'liters' => round($items->sum('liters'), 1),
                'count'  => $items->count(),
            ];
        })->values();

        $truckName = $transactions->whereNotNull('truck_name')->where('truck_name', '!=', '')->pluck('truck_name')->first() ?? '';
        $carNumber = $transactions->whereNotNull('car_number')->where('car_number', '!=', '-')->pluck('car_number')->first() ?? '';

        // ══════════════════════════════════════════════════════
        // 📊 إعداد نقاط الشارت الزمني (Chronological Timeline Points)
        // ══════════════════════════════════════════════════════
        $radarMinEff = (float) \App\Services\SystemSetting::get('fuel_radar_min_efficiency', 4.0);
        $radarMaxCostKm = (float) \App\Services\SystemSetting::get('fuel_radar_max_cost_km', 7.0);
        $radarTolerancePct = (float) \App\Services\SystemSetting::get('fuel_radar_tolerance_pct', 10.0);
        $effectiveMinEff = round($radarMinEff * (1 - ($radarTolerancePct / 100)), 2);
        $effectiveMaxCostKm = round($radarMaxCostKm * (1 + ($radarTolerancePct / 100)), 2);

        // الترتيب تصاعدياً من الأقدم للأحدث لرسم المسار الزمني بشكل سليم
        $sortedTx = $transactions->sortBy('id')->values();

        $timelinePoints = $sortedTx->map(function ($t) use ($effectiveMinEff, $effectiveMaxCostKm) {
            $liters = floatval($t->liters ?? 0);
            $dist   = floatval($t->distance_km ?? 0);
            $rate   = floatval($t->consumption_rate ?? 0);
            $costKm = floatval($t->cost_per_km ?? 0);

            // لو لم يكن مسجلاً كم/لتر وتم تسجيل المسافة واللترات نحسبها
            if ($rate <= 0 && $dist > 0 && $liters > 0) {
                $rate = round($dist / $liters, 2);
            }

            // فحص الشبهة أو المشكلة (استهلاك مفرط أو تكلفة شاذة)
            $isAbnormalRate = ($rate > 0 && $rate < $effectiveMinEff);
            $isAbnormalCost = ($costKm > 0 && $costKm > $effectiveMaxCostKm);
            $hasProblem     = ($isAbnormalRate || $isAbnormalCost);

            $problemReason = null;
            if ($isAbnormalRate) {
                $problemReason = "⚠️ استهلاك مرتفع للوقود (" . number_format($rate, 2) . " كم/لتر) أقل من الحد القياسي ({$effectiveMinEff} كم/لتر)";
            } elseif ($isAbnormalCost) {
                $problemReason = "⚠️ تكلفة مرتفعة (" . number_format($costKm, 2) . " ج/كم) أعلى من الحد المسموح ({$effectiveMaxCostKm} ج/كم)";
            }

            $dateFormatted = $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('Y-m-d H:i') : '—';
            $dateShort     = $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('m/d H:i') : '—';

            $carTitle = ($t->car_number && $t->car_number !== '-')
                ? $t->car_number . ($t->truck_name ? " ({$t->truck_name})" : '')
                : ($t->truck_name ?: 'شاحنة غير محددة');

            return [
                'id'               => $t->id,
                'date'             => $dateFormatted,
                'date_short'       => $dateShort,
                'raw_date'         => $t->created_at,
                'liters'           => $liters,
                'car_number'       => ($t->car_number && $t->car_number !== '-') ? $t->car_number : '—',
                'truck_name'       => $t->truck_name ?: '—',
                'car_title'        => $carTitle,
                'driver_name'      => $t->driver_name ?: '—',
                'location'         => $t->notes ?: 'المكان/الوجهة غير مسجلة',
                'station_name'     => $t->station_name ?: '—',
                'company_name'     => $t->company_name ?: '—',
                'total_cost'       => floatval($t->total_on_company ?? 0),
                'odometer'         => $t->odometer ? floatval($t->odometer) : null,
                'distance_km'      => $dist,
                'consumption_rate' => $rate,
                'cost_per_km'      => $costKm,
                'is_full_tank'     => (bool) $t->is_full_tank,
                'has_problem'      => $hasProblem,
                'problem_reason'   => $problemReason,
                'color'            => $hasProblem ? '#ef4444' : '#10b981',
            ];
        });

        return response()->json([
            'driver_name'      => $driverName,
            'car_number'       => $carNumber,
            'truck_name'       => $truckName,
            'entity_type'      => $isCar ? 'car' : 'driver',
            'total_distance'   => $totalDistance,
            'last_odometer'    => $lastOdometer,
            'timeline_points'  => $timelinePoints,
            'radar_standards'  => [
                'min_eff'       => $radarMinEff,
                'effective_min' => $effectiveMinEff,
                'max_cost_km'   => $radarMaxCostKm,
                'effective_max' => $effectiveMaxCostKm,
            ],
            'date_range'  => [
                'entries_count' => $totalRefuelings,
                'from'          => $firstDate->format('Y-m-d'),
                'to'            => $lastDate->format('Y-m-d'),
                'days_span'     => $daysSpan,
            ],
            'cost' => [
                'total'  => $totalSpent,
                'by_day' => $costByDay,
                'by_km'  => $costByKm,
            ],
            'volume' => [
                'total'          => $totalLiters,
                'per_refueling'  => $volPerRefueling,
                'by_day'         => $volByDay,
            ],
            'price' => [
                'avg'   => $avgPrice,
                'lower' => $minPrice,
                'larger'=> $maxPrice,
            ],
            'fuel_efficiency' => [
                'general_avg' => $avgRate,
                'lower'       => $minRate,
                'larger'      => $maxRate,
            ],
            'refueling_routine' => [
                'between_refuelings' => $avgDistBetweenRefuelings,
                'frequency_days'     => $refuelingFrequencyDays,
                'monthly_projection' => $monthlyProjection,
                'full_tank_count'    => $fullTankCount,
                'partial_tank_count' => $partialTankCount,
                'full_tank_pct'      => $fullTankPct,
            ],
            'last_metrics' => [
                'rate'    => $lastRate,
                'cost_km' => $lastCostKm,
            ],
            'gas_stations'     => $stationBreakdown,
            'monthly_expenses' => $monthlyExpenses,
            'recent_trips'     => $transactions->values(),
        ]);
    }

    /**
     * تحديث معايير الرادار ونسبة السماح والتفاوت
     */
    public function updateRadarSettings(Request $request)
    {
        $request->validate([
            'min_efficiency' => 'required|numeric|min:0.1',
            'max_cost_km'    => 'required|numeric|min:0.1',
            'tolerance_pct'  => 'required|numeric|min:0|max:100',
        ], [
            'min_efficiency.required' => 'يرجى إدخال الحد الأدنى لكفاءة الاستهلاك',
            'max_cost_km.required'    => 'يرجى إدخال الحد الأقصى لتكلفة الكيلومتر',
            'tolerance_pct.required'  => 'يرجى إدخال نسبة التفاوت والسماح',
        ]);

        \App\Services\SystemSetting::set('fuel_radar_min_efficiency', (float) $request->min_efficiency);
        \App\Services\SystemSetting::set('fuel_radar_max_cost_km', (float) $request->max_cost_km);
        \App\Services\SystemSetting::set('fuel_radar_tolerance_pct', (float) $request->tolerance_pct);

        return back()->with('success', '✅ تم تحديث معايير الرادار ونسبة السماح بنجاح، وتطبيقها فورياً على فحص الشبهات!');
    }

    /**
     * مقارنة شاملة جنباً إلى جنب بين شاحنتين (Vehicle Comparison Engine)
     */
    public function getVehicleComparison(Request $request)
    {
        $vehicleA = trim($request->query('vehicle_a', ''));
        $vehicleB = trim($request->query('vehicle_b', ''));

        if (!$vehicleA || !$vehicleB) {
            return response()->json(['error' => 'يرجى تحديد الشاحنتين للمقارنة بينهما'], 422);
        }

        // معايير الرادار
        $radarMinEff = (float) \App\Services\SystemSetting::get('fuel_radar_min_efficiency', 4.0);
        $radarMaxCostKm = (float) \App\Services\SystemSetting::get('fuel_radar_max_cost_km', 7.0);
        $radarTolerancePct = (float) \App\Services\SystemSetting::get('fuel_radar_tolerance_pct', 10.0);
        $effectiveMinEff = round($radarMinEff * (1 - ($radarTolerancePct / 100)), 2);
        $effectiveMaxCostKm = round($radarMaxCostKm * (1 + ($radarTolerancePct / 100)), 2);

        $getStats = function ($vehicleKey) use ($effectiveMinEff, $effectiveMaxCostKm) {
            $txs = DB::table('fuel_transactions as ft')
                ->leftJoin('transport_companies as tc', 'ft.company_id', '=', 'tc.id')
                ->leftJoin('gas_stations as gs', 'ft.station_id', '=', 'gs.id')
                ->where(function ($q) use ($vehicleKey) {
                    $q->where('ft.car_number', $vehicleKey)
                      ->orWhere('ft.driver_name', $vehicleKey)
                      ->orWhere(DB::raw('COALESCE(NULLIF(ft.car_number, "-"), ft.driver_name)'), $vehicleKey);
                })
                ->select('ft.*', 'tc.company_name', 'gs.station_name')
                ->orderByDesc('ft.id')
                ->get();

            if ($txs->isEmpty()) {
                return null;
            }

            $totalTrips = $txs->count();
            $fullFills = $txs->where('is_full_tank', 1)->count();
            $partialFills = $txs->where('is_full_tank', 0)->count();
            $totalLiters = (float) $txs->sum('liters');
            $totalCost = (float) $txs->sum('total_on_company');
            $totalDist = (float) $txs->sum('distance_km');

            $validEff = $txs->where('consumption_rate', '>', 0);
            $avgEfficiency = $validEff->isNotEmpty() ? round($validEff->avg('consumption_rate'), 2) : 0;

            $validCostKm = $txs->where('cost_per_km', '>', 0);
            $avgCostKm = $validCostKm->isNotEmpty() ? round($validCostKm->avg('cost_per_km'), 2) : 0;

            $lastTx = $txs->first();
            $lastOdometer = $txs->max('odometer') ?? 0;

            $truckName = $txs->whereNotNull('truck_name')->where('truck_name', '!=', '')->pluck('truck_name')->first() ?? '';
            $carNumber = $txs->whereNotNull('car_number')->where('car_number', '!=', '-')->pluck('car_number')->first() ?? $vehicleKey;

            // السائقون المرتبطون
            $drivers = $txs->pluck('driver_name')->filter(function ($d) {
                return $d && $d !== '-';
            })->unique()->values()->all();

            // الشركات المرتبطة
            $companies = $txs->pluck('company_name')->filter(function ($c) {
                return $c && $c !== '—';
            })->unique()->values()->all();

            // حساب تنبيهات الشبهات
            $suspicionCount = $txs->filter(function ($t) use ($effectiveMinEff, $effectiveMaxCostKm) {
                $isAbnormalRate = ($t->is_full_tank == 1 && $t->consumption_rate > 0 && $t->consumption_rate < $effectiveMinEff);
                $isAbnormalCost = ($t->cost_per_km > 0 && $t->cost_per_km > $effectiveMaxCostKm);
                return $isAbnormalRate || $isAbnormalCost;
            })->count();

            // تجميع الوجهات وخطوط السير المسجلة للطرف
            $destGroup = $txs->filter(function($t) {
                return !empty(trim($t->notes ?? ''));
            })->groupBy(function($t) {
                return trim($t->notes);
            });

            $destinations = [];
            foreach ($destGroup as $loc => $group) {
                $vEff = $group->where('consumption_rate', '>', 0);
                $vCostKm = $group->where('cost_per_km', '>', 0);
                $dLit = (float) $group->sum('liters');
                $dCost = (float) $group->sum('total_on_company');
                $destinations[] = [
                    'location'         => $loc,
                    'trips_count'      => $group->count(),
                    'total_liters'     => round($dLit, 1),
                    'total_cost'       => round($dCost, 2),
                    'total_distance'   => round($group->sum('distance_km'), 1),
                    'avg_efficiency'   => $vEff->isNotEmpty() ? round($vEff->avg('consumption_rate'), 2) : 0,
                    'avg_cost_km'      => $vCostKm->isNotEmpty() ? round($vCostKm->avg('cost_per_km'), 2) : 0,
                    'avg_price_liter'  => $dLit > 0 ? round($dCost / $dLit, 2) : 0,
                ];
            }

            $avgPriceLiter = $totalLiters > 0 ? round($totalCost / $totalLiters, 2) : 0;
            $maxEfficiency = $validEff->isNotEmpty() ? round($validEff->max('consumption_rate'), 2) : 0;
            $minEfficiency = $validEff->isNotEmpty() ? round($validEff->min('consumption_rate'), 2) : 0;
            $lastRate      = ($lastTx && $lastTx->consumption_rate > 0) ? round($lastTx->consumption_rate, 2) : 0;

            // آخر 5 عمليات
            $recentTrips = $txs->take(5)->map(function($t) {
                return [
                    'date'             => $t->created_at ? date('Y-m-d', strtotime($t->created_at)) : '—',
                    'location'         => $t->notes ?: 'غير مسجل',
                    'liters'           => $t->liters,
                    'cost'             => $t->total_on_company,
                    'is_full_tank'     => (bool) $t->is_full_tank,
                    'consumption_rate' => $t->consumption_rate,
                    'cost_per_km'      => $t->cost_per_km,
                    'station_name'     => $t->station_name ?? '—',
                ];
            })->values()->all();

            return [
                'vehicle_key'         => $vehicleKey,
                'car_number'          => $carNumber,
                'truck_name'          => $truckName,
                'drivers'             => $drivers,
                'companies'           => $companies,
                'total_trips'         => $totalTrips,
                'full_fills'          => $fullFills,
                'partial_fills'       => $partialFills,
                'total_liters'        => round($totalLiters, 1),
                'total_cost'          => round($totalCost, 2),
                'total_distance'      => round($totalDist, 1),
                'avg_efficiency'      => $avgEfficiency,
                'avg_cost_km'         => $avgCostKm,
                'avg_price_per_liter' => $avgPriceLiter,
                'max_efficiency'      => $maxEfficiency,
                'min_efficiency'      => $minEfficiency,
                'last_rate'           => $lastRate,
                'last_odometer'       => $lastOdometer,
                'last_trip_date'      => $lastTx ? $lastTx->created_at : null,
                'suspicion_count'     => $suspicionCount,
                'destinations'        => $destinations,
                'recent_trips'        => $recentTrips,
            ];
        };

        $dataA = $getStats($vehicleA);
        $dataB = $getStats($vehicleB);

        if (!$dataA || !$dataB) {
            return response()->json(['error' => 'تعذر العثور على بيانات كافية لإحدى الشاحنتين المختارتين'], 404);
        }

        // كفاءة الاستهلاك: الأكبر هو الفائز
        $winnerEfficiency = null;
        if ($dataA['avg_efficiency'] > 0 || $dataB['avg_efficiency'] > 0) {
            if ($dataA['avg_efficiency'] > $dataB['avg_efficiency']) {
                $winnerEfficiency = 'A';
            } elseif ($dataB['avg_efficiency'] > $dataA['avg_efficiency']) {
                $winnerEfficiency = 'B';
            } else {
                $winnerEfficiency = 'tie';
            }
        }

        // تكلفة الكيلومتر: الأقل هو الفائز
        $winnerCost = null;
        if ($dataA['avg_cost_km'] > 0 || $dataB['avg_cost_km'] > 0) {
            if ($dataA['avg_cost_km'] > 0 && ($dataB['avg_cost_km'] <= 0 || $dataA['avg_cost_km'] < $dataB['avg_cost_km'])) {
                $winnerCost = 'A';
            } elseif ($dataB['avg_cost_km'] > 0 && ($dataA['avg_cost_km'] <= 0 || $dataB['avg_cost_km'] < $dataA['avg_cost_km'])) {
                $winnerCost = 'B';
            } else {
                $winnerCost = 'tie';
            }
        }

        // الأمان والنزاهة: الأقل شبهات هو الفائز
        $winnerSafety = null;
        if ($dataA['suspicion_count'] < $dataB['suspicion_count']) {
            $winnerSafety = 'A';
        } elseif ($dataB['suspicion_count'] < $dataA['suspicion_count']) {
            $winnerSafety = 'B';
        } else {
            $winnerSafety = 'tie';
        }

        // نسبة التوفير في الاستهلاك
        $effDiffPct = 0;
        if ($dataA['avg_efficiency'] > 0 && $dataB['avg_efficiency'] > 0) {
            $higher = max($dataA['avg_efficiency'], $dataB['avg_efficiency']);
            $lower  = min($dataA['avg_efficiency'], $dataB['avg_efficiency']);
            $effDiffPct = round((($higher - $lower) / $lower) * 100, 1);
        }

        // مقارنة تفصيلية للوجهات والأماكن المشتركة (إذا كانا يذهبان لنفس المكان)
        $destMapA = collect($dataA['destinations'] ?? [])->keyBy('location');
        $destMapB = collect($dataB['destinations'] ?? [])->keyBy('location');
        $allLocs  = $destMapA->keys()->merge($destMapB->keys())->unique()->values();

        $destinationsComparison = [];
        $commonCount = 0;
        foreach ($allLocs as $loc) {
            $stA = $destMapA->get($loc);
            $stB = $destMapB->get($loc);
            $inBoth = ($stA && $stB);
            if ($inBoth) $commonCount++;

            $costA = $stA ? $stA['avg_cost_km'] : 0;
            $costB = $stB ? $stB['avg_cost_km'] : 0;
            $effA  = $stA ? $stA['avg_efficiency'] : 0;
            $effB  = $stB ? $stB['avg_efficiency'] : 0;

            $hasSuspicionAtDest = false;
            $suspicionDesc = '';
            if ($inBoth) {
                if ($costA > $effectiveMaxCostKm || ($effA > 0 && $effA < $effectiveMinEff)) {
                    $hasSuspicionAtDest = true;
                    $suspicionDesc .= "🚨 شبهة هدر عند (أ) في هذه الوجهة. ";
                }
                if ($costB > $effectiveMaxCostKm || ($effB > 0 && $effB < $effectiveMinEff)) {
                    $hasSuspicionAtDest = true;
                    $suspicionDesc .= "🚨 شبهة هدر عند (ب) في هذه الوجهة. ";
                }
            }

            $destinationsComparison[] = [
                'location'       => $loc,
                'in_both'        => $inBoth,
                'stat_a'         => $stA,
                'stat_b'         => $stB,
                'cost_km_a'      => $costA,
                'cost_km_b'      => $costB,
                'eff_a'          => $effA,
                'eff_b'          => $effB,
                'higher_cost'    => $inBoth ? ($costA > $costB ? 'A' : ($costB > $costA ? 'B' : 'tie')) : null,
                'higher_eff'     => $inBoth ? ($effA > $effB ? 'A' : ($effB > $effA ? 'B' : 'tie')) : null,
                'has_suspicion'  => $hasSuspicionAtDest,
                'suspicion_desc' => $suspicionDesc,
            ];
        }

        // ترتيب الوجهات المشتركة في المقدمة
        usort($destinationsComparison, function($x, $y) {
            if ($x['in_both'] === $y['in_both']) return 0;
            return $x['in_both'] ? -1 : 1;
        });

        return response()->json([
            'vehicle_a'                 => $dataA,
            'vehicle_b'                 => $dataB,
            'destinations_comparison'   => $destinationsComparison,
            'common_destinations_count' => $commonCount,
            'winners'   => [
                'efficiency'   => $winnerEfficiency,
                'cost'         => $winnerCost,
                'safety'       => $winnerSafety,
                'eff_diff_pct' => $effDiffPct,
            ],
            'radar_standards' => [
                'min_eff'       => $radarMinEff,
                'effective_min' => $effectiveMinEff,
                'max_cost'      => $radarMaxCostKm,
                'effective_max' => $effectiveMaxCostKm,
                'tolerance'     => $radarTolerancePct,
            ]
        ]);
    }

    public function storeGasStation(Request $request)
    {
        $request->validate([
            'company_id'      => 'required|exists:transport_companies,id',
            'station_id'      => 'required|exists:gas_stations,id',
            'advance_source'  => 'required|in:none,safe,station',
            'account_id'      => 'required_if:advance_source,safe',
            'notes'           => 'required|string|min:2',
        ], [
            'account_id.required_if' => '⚠️ يجب اختيار الخزنة لصرف العهدة النقدية.',
            'notes.required'         => '⚠️ خط سير الرحلة / المكان والوجهة حقل إجباري لتسجيل العملية.',
            'notes.min'              => '⚠️ يرجى كتابة المكان أو الوجهة بشكل صحيح (حرفين على الأقل).',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $company = \Illuminate\Support\Facades\DB::table('transport_companies')->where('id', $request->company_id)->first();
                $station = \Illuminate\Support\Facades\DB::table('gas_stations')->where('id', $request->station_id)->first();
                if (!$company || !$station) throw new \Exception('الشركة أو المحطة غير موجودة.');

                $companyName = $company->company_name ?? $company->name;
                $stationName = $station->station_name ?? $station->name;

                $carNumber  = trim($request->car_number ?? '');
                $truckName  = trim($request->truck_name ?? '');
                $driverName = trim($request->driver_name ?? '');

                // التوافق العكسي: إذا لم يحدد رقم الشاحنة واستخدم الحقل القديم
                if (!$carNumber && $request->filled('driver_car')) {
                    $legacyVal = trim($request->driver_car);
                    $carNumber  = '-';
                    $driverName = $legacyVal;
                }
                if (!$carNumber) {
                    $carNumber = '-';
                }
                if (!$driverName) {
                    $driverName = ($carNumber !== '-') ? $carNumber : 'غير محدد';
                }

                // الاسم التعريفي المجمع للشاحنة والسائق للديون والفواتير
                $driverCarDisplay = ($carNumber !== '-' ? $carNumber : '') 
                    . ($truckName ? " ({$truckName})" : '')
                    . ($driverName && $driverName !== $carNumber ? " - {$driverName}" : '');
                $driverCarDisplay = trim($driverCarDisplay) ?: 'غير محدد';

                $itemName = 'وقود فقط (بدون عهدة)';
                $dbPrice = 0;

                if ($request->item_id != "0") {
                    $item = \Illuminate\Support\Facades\DB::table('items')->where('id', $request->item_id)->first();
                    if (!$item) throw new \Exception('الصنف غير موجود.');
                    $itemName = $item->item_name ?? $item->name;
                    $dbPrice  = floatval($item->original_price ?? $item->price ?? 0);
                    if ($dbPrice == 0) $dbPrice = (float) \App\Services\SystemSetting::get('default_fuel_price', 20);
                }

                // 💡 إذا حدد المستخدم سعر اللتر يدوياً
                if ($request->filled('fuel_price') && floatval($request->fuel_price) > 0) {
                    $dbPrice = floatval($request->fuel_price);
                }

                $advanceSource = $request->advance_source; // 'none' | 'safe' | 'station'
                $quantity = ($request->item_id == "0") ? 0 : floatval($request->quantity ?? 0);
                // 💡 لو "بدون عهدة" نتجاهل أي قيمة دخلت في خانة المبلغ
                $advance  = ($advanceSource === 'none') ? 0 : floatval($request->cash_advance ?? 0);

                if ($advanceSource !== 'none' && $advance <= 0) {
                    throw new \Exception('⚠️ يرجى إدخال مبلغ العهدة النقدية المطلوبة للسائق (المبلغ إجباري عند اختيار وجود عهدة).');
                }

                $fuelDebt = $quantity * $dbPrice;

                // لازم يكون فيه وقود أو عهدة على الأقل
                if ($fuelDebt <= 0 && $advance <= 0) {
                    throw new \Exception('⚠️ لا يمكن تسجيل عملية فارغة — أدخل كمية وقود أو عهدة نقدية على الأقل.');
                }

                // 💡 استدعاء الخصم اليدوي (مصروفات) لخصمه من إجمالي الأرباح
                $manualDiscount = floatval($request->manual_discount ?? 0);

                $profitRate = (float) \App\Services\SystemSetting::get('fuel_profit_rate', 0.01);

                // 💡 اللوجيك المحاسبي بناءً على جهة الصرف
                if ($advanceSource === 'none') {
                    // لا توجد عهدة — العمولة على الوقود فقط، والمحطة تستحق ثمن الوقود
                    $grossProfit    = $fuelDebt * $profitRate;
                    $totalToStation = $fuelDebt;
                } elseif ($advanceSource === 'station') {
                    // العهدة من المحطة — العمولة على (الوقود + العهدة)، المحطة تستحق الاثنين
                    $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                    $totalToStation = $fuelDebt + $advance;
                } else {
                    // العهدة من خزنة الشركة — العمولة على الإجمالي، المحطة تستحق الوقود فقط
                    $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                    $totalToStation = $fuelDebt;

                    // سحب العهدة من الخزنة
                    if ($advance > 0) {
                        $accId = $request->account_id;
                        $account = \Illuminate\Support\Facades\DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                        if (!$account || $account->balance < $advance) throw new \Exception("رصيد الخزنة لا يكفي لصرف العهدة.");

                        \Illuminate\Support\Facades\DB::table('accounts')->where('id', $accId)->decrement('balance', $advance);
                        \Illuminate\Support\Facades\DB::table('financial_transactions')->insert([
                            'type' => 'expense', 'amount' => $advance, 'from_account_id' => $accId,
                            'notes' => "صرف عهدة نقدية لـ ({$driverCarDisplay}) | شركة {$companyName}", 'status' => 'active', 'created_at' => now(),
                        ]);
                    }
                }

                // 💡 تطبيق الخصم اليدوي على الربح ↘ شركة النقل تستحق التكلفة + الربح بعد الخصم
                $profitAfterDiscount = $grossProfit - $manualDiscount;
                $totalOnCompany      = $fuelDebt + $advance + $profitAfterDiscount;

                // 💡 حساب الاستقطاعات (نحسبها أولاً قبل أي insert)
                $deductions = \Illuminate\Support\Facades\DB::table('fuel_deductions')->get();
                $totalDeductionsAmount = 0;
                $deductionRows = [];
                foreach ($deductions as $ded) {
                    $dedVal = $profitAfterDiscount * ($ded->percentage / 100);
                    $totalDeductionsAmount += $dedVal;
                    if ($dedVal > 0) {
                        $deductionRows[] = ['name' => trim($ded->name), 'amount' => $dedVal];
                    }
                }

                $netProfit = $profitAfterDiscount - $totalDeductionsAmount;

                // 💡 قراءة العداد والتفويلة الكاملة لحساب Drivvo
                $currentOdometer = $request->filled('odometer') ? floatval($request->odometer) : null;
                $isFullTank = $request->has('is_full_tank') && in_array($request->is_full_tank, [1, '1', true, 'true', 'on']);
                $distanceKm = null;
                $consumptionRate = null;
                $costPerKm = null;

                if ($currentOdometer && $currentOdometer > 0) {
                    $prevOdometerVal = null;
                    $prevTx = null;

                    if ($request->filled('prev_odometer') && floatval($request->prev_odometer) > 0) {
                        $prevOdometerVal = floatval($request->prev_odometer);
                    } else {
                        // البحث عن العداد السابق:
                        // الشاحنة (رقم اللوحة) هي الأساس لربط العداد
                        $queryPrev = \Illuminate\Support\Facades\DB::table('fuel_transactions')
                            ->whereNotNull('odometer')
                            ->where('odometer', '>', 0)
                            ->where('odometer', '<', $currentOdometer);

                        if ($carNumber !== '-') {
                            $queryPrev->where('car_number', $carNumber);
                        } else {
                            $queryPrev->where('driver_name', $driverName);
                        }

                        if ($isFullTank) {
                            $prevTx = (clone $queryPrev)->where('is_full_tank', 1)->orderByDesc('id')->first()
                                      ?: $queryPrev->orderByDesc('id')->first();
                        } else {
                            $prevTx = $queryPrev->orderByDesc('id')->first();
                        }

                        if ($prevTx && $prevTx->odometer) {
                            $prevOdometerVal = floatval($prevTx->odometer);
                        }
                    }

                    if ($prevOdometerVal && $currentOdometer > $prevOdometerVal) {
                        $distanceKm = round($currentOdometer - $prevOdometerVal, 2);
                    }

                    // 1) حالة التفويلة الكاملة (Full Tank):
                    // كفاءة الاستهلاك = (قراءة العداد الحالية - العداد عند آخر تفويلة كاملة) ÷ إجمالي اللترات المستهلكة
                    if ($isFullTank) {
                        $totalLitersForTrip = $quantity;
                        // جمع أي لترات تم تزويدها جزئياً بين التفويلتين الكاملتين لنفس الشاحنة
                        if ($prevTx && $prevTx->id && $prevTx->is_full_tank) {
                            $partialQuery = \Illuminate\Support\Facades\DB::table('fuel_transactions')
                                ->where('id', '>', $prevTx->id)
                                ->where('is_full_tank', 0);

                            if ($carNumber !== '-') {
                                $partialQuery->where('car_number', $carNumber);
                            } else {
                                $partialQuery->where('driver_name', $driverName);
                            }

                            $intermediatePartialLiters = $partialQuery->sum('liters');
                            $totalLitersForTrip += floatval($intermediatePartialLiters);
                        }

                        if ($distanceKm > 0 && $totalLitersForTrip > 0) {
                            // كفاءة الاستهلاك بوحدة (كم / لتر)
                            $consumptionRate = round($distanceKm / $totalLitersForTrip, 2);
                            $costPerKm = round($totalOnCompany / $distanceKm, 2);
                        }
                    } else {
                        // 2) حالة التزويد الجزئي (مش فل تانك - Partial Fill):
                        // نترك حقل معدل الاستهلاك فارغاً (Null) لحين التفويلة الكاملة القادمة
                        $consumptionRate = null;
                        $costPerKm = null;
                    }
                }

                // 1) إنشاء fuel_transaction أولاً للحصول على ID المصدر
                $fuelTxId = \Illuminate\Support\Facades\DB::table('fuel_transactions')->insertGetId([
                    'company_id'             => $company->id,
                    'station_id'             => $station->id,
                    'driver_name'            => $driverName,
                    'car_number'             => $carNumber,
                    'truck_name'             => $truckName ?: null,
                    'fuel_type'              => $itemName,
                    'liters'                 => $quantity,
                    'cash_advance'           => $advance,
                    'market_price_at_time'   => $dbPrice,
                    'discount_price_at_time' => $dbPrice,
                    'total_to_station'       => $totalToStation,
                    'total_on_company'       => $totalOnCompany,
                    'ahmed_profit'           => $netProfit,
                    'odometer'               => $currentOdometer,
                    'is_full_tank'           => $isFullTank ? 1 : 0,
                    'distance_km'            => $distanceKm,
                    'consumption_rate'       => $consumptionRate,
                    'cost_per_km'            => $costPerKm,
                    'notes'                  => $request->notes ?? null,
                    'created_at'             => now(),
                ]);

                // 2) ربط أي financial_transaction اتسجلت قبل (سحب العهدة) بالـ fuel_tx
                if ($advanceSource === 'safe' && $advance > 0) {
                    \Illuminate\Support\Facades\DB::table('financial_transactions')
                        ->where('from_account_id', $request->account_id)
                        ->where('amount', $advance)
                        ->where('type', 'expense')
                        ->whereNull('ref_id')
                        ->where('created_at', '>=', now()->subSeconds(5))
                        ->orderByDesc('id')->limit(1)
                        ->update(['ref_type' => 'fuel_transaction', 'ref_id' => $fuelTxId]);
                }

                // 3) إدخال الاستقطاعات في company_debts مع الربط
                foreach ($deductionRows as $row) {
                    \Illuminate\Support\Facades\DB::table('company_debts')->insert([
                        'creditor_name'     => $row['name'],
                        'reason'            => "استقطاع من بنزينة ({$stationName}) | الشاحنة/السائق: {$driverCarDisplay} | شركة النقل: {$companyName}",
                        'total_amount'      => $row['amount'],
                        'paid_amount'       => 0,
                        'remaining_balance' => $row['amount'],
                        'category'          => 'استقطاعات',
                        'source_type'       => 'fuel_transaction',
                        'source_id'         => $fuelTxId,
                        'created_at'        => now(),
                    ]);
                }

                // 4) دين المحطة (الوقود) مربوط بالـ fuel_tx
                if ($totalToStation > 0) {
                    $reasonDetails = "الشاحنة/السائق: {$driverCarDisplay} | " .
                                     "شركة النقل: {$companyName} | " .
                                     "الكمية: {$quantity} لتر | " .
                                     "عهدة نقدية: {$advance} ج | " .
                                     "صافي الربح المُحقق: {$netProfit} ج";

                    \Illuminate\Support\Facades\DB::table('company_debts')->insert([
                        'creditor_name'     => trim($stationName),
                        'reason'            => $reasonDetails,
                        'total_amount'      => $totalToStation,
                        'paid_amount'       => 0,
                        'remaining_balance' => $totalToStation,
                        'category'          => 'وقود',
                        'source_type'       => 'fuel_transaction',
                        'source_id'         => $fuelTxId,
                        'created_at'        => now(),
                    ]);
                }

                // 5) دين شركة النقل في installments
                \Illuminate\Support\Facades\DB::table('installments')->insert([
                    'customer_name' => $companyName, 'customer_phone' => 'بدون', 'product_name' => "بنزينة ({$stationName}) - {$itemName} | {$driverCarDisplay}",
                    'cash_price' => $totalOnCompany, 'discount' => 0, 'down_payment' => 0,
                    'remaining_after_down' => $totalOnCompany, 'installment_months' => 0, 'interest_rate' => 0, 'total_after_interest' => $totalOnCompany,
                    'monthly_installment' => $totalOnCompany, 'due_day' => date('d'), 'remaining_balance' => $totalOnCompany,
                    'profit' => $netProfit, 'category' => 'بنزينة',
                    'sale_type' => 'fuel',
                    'start_date' => now()->toDateString(), 'created_at' => now(),
                    'fuel_liters'  => $quantity,
                    'cash_custody' => $advance,
                    'source_type'  => 'fuel_transaction',
                    'source_id'    => $fuelTxId,
                ]);

                if (method_exists($this, 'logActivity')) {
                    $this->logActivity(
                        'create',
                        'gas_station',
                        "⛽ تم تسجيل عملية بنزينة لمحطة ({$stationName}) | الكمية: {$quantity} لتر | عهدة: {$advance} ج"
                    );
                }

            });
            return back()->with('success', '✅ تم تسجيل عملية البنزينة بنجاح، وربطها بالديون والاستقطاعات!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}