<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\AuditService;

class OperationsLogController extends SystemController
{

    /**
     * شاشة سجل العمليات الموحدة (قراءة فقط).
     * تجمع العمليات من 4 مصادر:
     *  - fuel_transactions       (بنزينة)
     *  - sales                   (توريد مخزن)
     *  - inventory_movements     (إهلاك/هالك/نقل/مرتجعات)
     *  - installments (cash)     (بيع مباشر/خدمات/مبيعات مخزن — استثناء التقسيط والبنزينة)
     */
    public function index(Request $request)
    {
        $type     = $request->input('type', 'all');
        $period   = $request->input('period', 'today');
        $search   = trim((string) $request->input('search', ''));
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        [$start, $end] = $this->resolvePeriod($period, $fromDate, $toDate);

        // معرّفات الديون ودفعات الأقساط المرتبطة بالمقاولات لمنع تعديلها أو حذفها من سجل العمليات
        $contDirNames = Schema::hasTable('cont_directory')
            ? DB::table('cont_directory')->pluck('name')->filter()->unique()->values()->all()
            : [];
        $contDebtIds = DB::table('company_debts')
            ->where(function ($q) use ($contDirNames) {
                $q->where('source_type', 'like', 'cont_%')
                  ->orWhere('reason', 'like', '%مشروع:%')
                  ->orWhere('reason', 'like', '%فاتورة خامات%')
                  ->orWhere('reason', 'like', '%متبقي مصنعية%')
                  ->orWhere('reason', 'like', '%عمولة مسوق%')
                  ->orWhere('reason', 'like', '%مقاول%');
                if (!empty($contDirNames)) {
                    $q->orWhereIn('creditor_name', $contDirNames);
                }
            })
            ->pluck('id')
            ->all();

        $contInstIds = DB::table('installments')
            ->where(function ($q) {
                $q->where('sale_type', 'contracting')
                  ->orWhere('source_type', 'like', 'cont_%')
                  ->orWhere('category', 'like', '%مقاول%')
                  ->orWhere('product_name', 'like', '%مقاول%');
            })
            ->pluck('id')
            ->all();

        $contPaymentIds = !empty($contInstIds)
            ? DB::table('installment_payments')->whereIn('installment_id', $contInstIds)->pluck('id')->all()
            : [];

        $rows = collect();

        // 1) البنزينة
        if (in_array($type, ['all', 'fuel'])) {
            $editedFuelIds = DB::table('operation_revisions')
                ->where('source_type', 'fuel_transaction')
                ->where('action', 'edit')
                ->pluck('source_id')
                ->unique()
                ->all();

            $q = DB::table('fuel_transactions as f')
                ->leftJoin('transport_companies as tc', 'f.company_id', '=', 'tc.id')
                ->leftJoin('gas_stations as gs', 'f.station_id', '=', 'gs.id')
                ->whereNull('f.superseded_by')
                ->select(
                    'f.id', 'f.liters', 'f.cash_advance', 'f.fuel_type',
                    'f.market_price_at_time', 'f.discount_price_at_time',
                    'f.total_on_company', 'f.total_to_station', 'f.ahmed_profit',
                    'f.driver_name', 'f.car_number', 'f.odometer', 'f.is_full_tank', 'f.notes',
                    'f.created_at',
                    'f.supersedes', 'f.superseded_by', 'f.superseded_at',
                    DB::raw('tc.company_name as company_name'),
                    DB::raw('gs.station_name as station_name')
                );
            if ($start && $end) $q->whereBetween('f.created_at', [$start, $end]);
            if ($search !== '') {
                $cleanIdSearch = ltrim($search, '#');
                if (ctype_digit($cleanIdSearch)) {
                    $q->where(function ($sub) use ($search, $cleanIdSearch) {
                        $sub->where('f.id', (int) $cleanIdSearch);
                        $this->applyArabicSearch($sub, ['tc.company_name', 'gs.station_name', 'f.driver_name', 'f.car_number', 'f.fuel_type', 'f.notes'], $search);
                    });
                } else {
                    $this->applyArabicSearch($q, ['tc.company_name', 'gs.station_name', 'f.driver_name', 'f.car_number', 'f.fuel_type', 'f.notes'], $search);
                }
            }
            foreach ($q->orderByDesc('f.created_at')->limit(500)->get() as $r) {
                $carTxt    = (!empty($r->car_number) && $r->car_number !== '-') ? "الشاحنة: {$r->car_number} | " : '';
                $odoTxt    = (!empty($r->odometer) && $r->odometer > 0) ? " | العداد: " . number_format((float)$r->odometer, 0) . " كم (" . (($r->is_full_tank ?? 1) ? 'تفويلة كاملة' : 'جزئية') . ")" : '';
                $destTxt   = (!empty($r->notes) && trim($r->notes) !== '') ? " | الوجهة: {$r->notes}" : '';
                $litersFmt = rtrim(rtrim(number_format((float)$r->liters, 2, '.', ''), '0'), '.');

                $rows->push([
                    'type'          => 'fuel',
                    'type_label'    => 'بنزينة',
                    'icon'          => 'fa-gas-pump',
                    'color'         => 'warning',
                    'id'            => $r->id,
                    'date'          => $r->created_at,
                    'title'         => "محطة {$r->station_name} — شركة {$r->company_name}",
                    'description'   => "{$carTxt}السائق: {$r->driver_name}{$destTxt}{$odoTxt} | {$r->fuel_type} | لتر: {$litersFmt} | عهدة: " . number_format((float)$r->cash_advance, 0) . " ج",
                    'amount'        => $r->total_on_company,
                    'profit'        => $r->ahmed_profit,
                    'editable'      => empty($r->superseded_by),
                    'editor'        => 'fuel',
                    'is_edit'       => !empty($r->supersedes) || in_array($r->id, $editedFuelIds),
                    'supersedes'    => $r->supersedes ?: (in_array($r->id, $editedFuelIds) ? $r->id : null),
                    'superseded_by' => $r->superseded_by,
                    'superseded_at' => $r->superseded_at,
                    'is_cancelled'  => false,
                ]);
            }
        }

        // 2) توريد المخزن (sales) — فقط ما أضيف للمخزن فعلاً، وليس البيع المباشر
        if (in_array($type, ['all', 'inventory_purchase'])) {
            $q = DB::table('sales')->where('inventory_status', 'to_inventory');
            if ($start && $end) $q->whereBetween('created_at', [$start, $end]);
            if ($search !== '') {
                $this->applyArabicSearch($q, ['product_name', 'supplier_name', 'category'], $search);
            }
            foreach ($q->orderByDesc('created_at')->limit(500)->get() as $r) {
                $rows->push([
                    'type'        => 'inventory_purchase',
                    'type_label'  => 'توريد مخزن',
                    'icon'        => 'fa-truck-ramp-box',
                    'color'       => 'primary',
                    'id'          => $r->id,
                    'date'        => $r->created_at,
                    'title'       => "{$r->product_name} — مورد: {$r->supplier_name}",
                    'description' => "الفئة: {$r->category} | الكمية: {$r->quantity} | شراء: " . number_format((float)$r->purchase_price,0) . " | بيع: " . number_format((float)$r->selling_price,0),
                    'amount'      => (float)$r->purchase_price * (float)$r->quantity,
                    'profit'      => null,
                    'editable'    => empty($r->last_edited_at),
                    'editor'      => 'inventory_purchase',
                    'is_edit'     => false,
                    'supersedes'  => null,
                    'superseded_by'=> null,
                    'superseded_at'=> $r->last_edited_at ?? null,
                    'is_cancelled'=> false,
                ]);
            }
        }

        // 3) حركات المخزن (إهلاك/هالك/نقل/مرتجعات)
        if (in_array($type, ['all', 'inventory_movement'])) {
            $q = DB::table('inventory_movements as m')
                ->leftJoin('sales as s', 'm.sale_id', '=', 's.id')
                ->select('m.*', DB::raw('s.product_name as product_name'), DB::raw('s.supplier_name as supplier_name'));
            if ($start && $end) $q->whereBetween('m.created_at', [$start, $end]);
            if ($search !== '') {
                $this->applyArabicSearch($q, ['s.product_name', 'm.notes', 'm.type'], $search);
            }
            foreach ($q->orderByDesc('m.created_at')->limit(500)->get() as $r) {
                $typeLabel = match($r->type) {
                    'damage', 'damaged', 'هالك' => 'هالك/إهلاك',
                    'supplier_return' => 'مرتجع لمورد',
                    'customer_return' => 'مرتجع من عميل',
                    'transfer' => 'نقل بين المخازن',
                    'adjustment' => 'تسوية مخزن',
                    default => $r->type,
                };
                $rows->push([
                    'type'        => 'inventory_movement',
                    'type_label'  => $typeLabel,
                    'icon'        => 'fa-arrows-up-down',
                    'color'       => 'danger',
                    'id'          => $r->id,
                    'date'        => $r->created_at,
                    'title'       => $r->product_name ?? '—',
                    'description' => "النوع: {$typeLabel} | الكمية: {$r->quantity} | " . ($r->notes ?? ''),
                    'amount'      => null,
                    'profit'      => null,
                    'editable'    => true,
                    'editor'      => 'inventory_movement',
                    'is_edit'     => false,
                    'supersedes'  => null,
                    'superseded_by'=> null,
                    'superseded_at'=> null,
                    'is_cancelled'=> false,
                ]);
            }
        }

        // 4) عمليات البيع (cash من installments — استثناء التقسيط والبنزينة، يشمل الملغاة عشان نظهرها)
        if (in_array($type, ['all', 'sale_cash', 'ac_ops'])) {
            $q = DB::table('installments')
                ->where('installment_months', 0)
                ->where(function ($x) {
                    $x->where('category', '!=', 'بنزينة')->orWhereNull('category');
                })
                ->where(function ($x) {
                    $x->where('sale_type', '!=', 'fuel')->orWhereNull('sale_type');
                });
            
            if ($type === 'ac_ops') {
                $q->where('category', 'مبيعات/صيانة تكييفات');
            }
            if ($start && $end) $q->whereBetween('created_at', [$start, $end]);
            if ($search !== '') {
                $this->applyArabicSearch($q, ['customer_name', 'product_name', 'category'], $search);
            }
            foreach ($q->orderByDesc('created_at')->limit(500)->get() as $r) {
                $editor = match(true) {
                    $r->category === 'مبيعات/صيانة تكييفات' => 'ac_invoice',
                    $r->category === 'خدمات'              => 'service',
                    $r->category === 'مبيعات مخزن' || $r->sale_type === 'inventory' => 'sale_inventory',
                    default                               => 'sale_direct',
                };
                $typeLabel = match($editor) {
                    'service'        => 'خدمة',
                    'sale_inventory' => 'بيع من المخزن',
                    default          => 'بيع مباشر',
                };
                $color = match($editor) {
                    'service'        => 'info',
                    'sale_inventory' => 'success',
                    default          => 'success',
                };
                $isCancelled = ($r->status === 'cancelled') || !empty($r->cancelled_via_oplog_at);
                $isContSale  = $this->isContractingInstallment($r);
                $rows->push([
                    'type'           => 'sale_cash',
                    'type_label'     => $isContSale ? 'مستحقات مقاولات' : $typeLabel,
                    'icon'           => $isContSale ? 'fa-helmet-safety' : 'fa-cash-register',
                    'color'          => $color,
                    'id'             => $r->id,
                    'date'           => $r->created_at,
                    'title'          => "{$r->product_name} — عميل: {$r->customer_name}",
                    'description'    => "السعر: " . number_format((float)$r->cash_price,0) . " | الربح: " . number_format((float)$r->profit,0),
                    'amount'         => $r->cash_price,
                    'profit'         => $r->profit,
                    'editable'       => !$isCancelled && !$isContSale,
                    'is_contracting' => $isContSale,
                    'editor'         => $editor,
                    'is_edit'        => false,
                    'supersedes'     => null,
                    'superseded_by'  => null,
                    'superseded_at'  => $r->cancelled_via_oplog_at ?? null,
                    'is_cancelled'   => $isCancelled,
                ]);
            }
        }

        // 5) المصروفات (financial_transactions type=general_expense / salary_expense / expense — مع استثناء عهد الوقود و الإيرادات المرتبطة)
        if (in_array($type, ['all', 'expense'])) {
            $q = DB::table('financial_transactions as ft')
                ->leftJoin('accounts as a', 'ft.from_account_id', '=', 'a.id')
                ->whereIn('ft.type', ['general_expense', 'salary_expense', 'expense'])
                ->where('ft.status', 'active')
                ->where(function ($x) {
                    $x->whereNull('ft.ref_type')->orWhere('ft.ref_type', '!=', 'fuel_transaction');
                })
                ->select('ft.*', DB::raw('a.account_name as account_name'));
            if ($start && $end) $q->whereBetween('ft.created_at', [$start, $end]);
            if ($search !== '') {
                $this->applyArabicSearch($q, ['ft.notes', 'a.account_name'], $search);
            }
            foreach ($q->orderByDesc('ft.created_at')->limit(500)->get() as $r) {
                $category = 'مصروفات تشغيلية';
                $actualNote = $r->notes ?? '';
                $expenseIcon = 'fa-receipt';
                $expenseLabel = 'مصروف';
                if ($r->type === 'salary_expense') $category = 'رواتب وأجور';
                elseif ($r->type === 'discount')   $category = 'خصومات للعملاء';
                elseif (($r->subtype ?? '') === 'jamia_installment' || ($r->ref_type ?? '') === 'jamia_installment') {
                    $category = 'قسط جمعية';
                    $expenseIcon = 'fa-handshake';
                    $expenseLabel = 'قسط جمعية';
                }
                elseif (($r->subtype ?? '') === 'jamia_payout_out' || ($r->ref_type ?? '') === 'jamia_payout_out') {
                    $category = 'صرف قبض جمعية';
                    $expenseIcon = 'fa-sack-dollar';
                    $expenseLabel = 'صرف قبض جمعية';
                }
                elseif (preg_match('/^\[(.*?)\]\s*(.*)/u', $actualNote, $m)) {
                    $category = $m[1];
                    $actualNote = $m[2];
                }
                $isContExp = $this->isContractingFinancialTx($r, $contDebtIds, $contPaymentIds);
                $rows->push([
                    'type'           => 'expense',
                    'type_label'     => $expenseLabel,
                    'icon'           => $expenseIcon,
                    'color'          => 'danger',
                    'id'             => $r->id,
                    'date'           => $r->created_at,
                    'title'          => "{$category} — " . ($actualNote ?: 'بدون بيان'),
                    'description'    => "خزنة: " . ($r->account_name ?? 'غير محدد'),
                    'amount'         => $r->amount,
                    'profit'         => null,
                    'editable'       => !$isContExp,
                    'is_contracting' => $isContExp,
                    'editor'         => 'expense',
                    'is_edit'        => false,
                    'supersedes'     => null,
                    'superseded_by'  => null,
                    'superseded_at'  => null,
                    'is_cancelled'   => false,
                ]);
            }
        }

        // 6) الحركات المالية (إيداع/تسوية/تحويل/تحصيل قسط/سداد دين) — كل FT المتبقية
        if (in_array($type, ['all', 'financial'])) {
            $q = DB::table('financial_transactions as ft')
                ->leftJoin('accounts as a_from', 'ft.from_account_id', '=', 'a_from.id')
                ->leftJoin('accounts as a_to',   'ft.to_account_id',   '=', 'a_to.id')
                ->whereIn('ft.type', ['income', 'settlement', 'transfer'])
                ->whereIn('ft.status', ['active', 'cancelled'])
                ->where(function ($x) {
                    $x->whereNull('ft.ref_type')->orWhere('ft.ref_type', '!=', 'fuel_transaction');
                })
                ->select('ft.*',
                    DB::raw('a_from.account_name as from_name'),
                    DB::raw('a_to.account_name as to_name'));
            if ($start && $end) $q->whereBetween('ft.created_at', [$start, $end]);
            if ($search !== '') {
                $this->applyArabicSearch($q, ['ft.notes', 'a_from.account_name', 'a_to.account_name'], $search);
            }
            foreach ($q->orderByDesc('ft.created_at')->limit(500)->get() as $r) {
                $refType = $r->ref_type ?? '';
                if ($r->type === 'transfer') {
                    $label = 'تحويل بين خزن';
                    $icon  = 'fa-right-left';
                    $color = 'info';
                    $desc  = "من: " . ($r->from_name ?? '—') . " ← إلى: " . ($r->to_name ?? '—');
                } elseif ($refType === 'installment_payment') {
                    $label = 'تحصيل قسط';
                    $icon  = 'fa-hand-holding-dollar';
                    $color = 'success';
                    $desc  = "للخزنة: " . ($r->to_name ?? '—');
                } elseif ($refType === 'company_debt') {
                    $label = 'تسوية (دين على الشركة)';
                    $icon  = 'fa-piggy-bank';
                    $color = 'warning';
                    $desc  = "للخزنة: " . ($r->to_name ?? '—');
                } elseif ($refType === 'asset_sale') {
                    $label = 'بيع أصل ثابت';
                    $icon  = 'fa-building-circle-arrow-right';
                    $color = ($r->total_after !== null && (float) $r->total_after < 0) ? 'danger' : 'success';
                    $desc  = "للخزنة: " . ($r->to_name ?? '—');
                    if ($r->total_before !== null) {
                        $desc .= " | القيمة الدفترية: " . number_format((float) $r->total_before, 0) . " ج";
                    }
                } elseif ($refType === 'jamia_collection' || ($r->subtype ?? '') === 'jamia_collection') {
                    $label = 'تحصيل قسط جمعية';
                    $icon  = 'fa-hand-holding-dollar';
                    $color = 'success';
                    $desc  = "إيداع بالخزنة: " . ($r->to_name ?? '—');
                } elseif ($refType === 'jamia_share' || ($r->subtype ?? '') === 'jamia_payout') {
                    $label = 'قبض جمعية (تدفقات)';
                    $icon  = 'fa-sack-dollar';
                    $color = 'success';
                    $desc  = "إيداع بالخزنة: " . ($r->to_name ?? '—');
                } elseif ($refType === 'jamia_share_claim' || ($r->subtype ?? '') === 'jamia_recovery') {
                    $label = 'استرداد حق جمعية (مقاصة)';
                    $icon  = 'fa-arrow-rotate-left';
                    $color = 'info';
                    $desc  = "إيداع بالخزنة: " . ($r->to_name ?? '—');
                } else {
                    $label = ($r->type === 'income') ? 'إيداع/إيراد' : 'تسوية مالية';
                    $icon  = 'fa-money-bill-trend-up';
                    $color = 'success';
                    $desc  = "للخزنة: " . ($r->to_name ?? '—');
                }
                
                $title = $r->notes ?: 'حركة مالية بدون بيان';
                if (!empty($r->person_name)) {
                    $title .= " (" . $r->person_name . ")";
                }

                $isCancelled = ($r->status === 'cancelled');
                $isContFin   = $this->isContractingFinancialTx($r, $contDebtIds, $contPaymentIds);
                $rows->push([
                    'type'           => 'financial',
                    'type_label'     => $label,
                    'icon'           => $icon,
                    'color'          => $color,
                    'id'             => $r->id,
                    'date'           => $r->created_at,
                    'title'          => $title,
                    'description'    => $desc,
                    'amount'         => $r->amount,
                    'profit'         => ($refType === 'asset_sale' && $r->total_after !== null) ? (float) $r->total_after : null,
                    'editable'       => !$isCancelled && !$isContFin,
                    'is_contracting' => $isContFin,
                    'editor'         => 'financial',
                    'is_edit'        => false,
                    'supersedes'     => null,
                    'superseded_by'  => null,
                    'superseded_at'  => $r->cancelled_at ?? null,
                    'is_cancelled'   => $isCancelled,
                ]);
            }
        }

        // 6) عمليات المقاولات والتشطيبات (cont_operations)
        if (in_array($type, ['all', 'contracting'])) {
            $q = DB::table('cont_operations as co')
                ->leftJoin('cont_projects as cp', 'co.project_id', '=', 'cp.id')
                ->leftJoin('cont_project_items as cpi', 'co.item_id', '=', 'cpi.id')
                ->select(
                    'co.*',
                    'cp.name as project_name',
                    'cp.client_name',
                    'cpi.title as item_title'
                );
            if ($start && $end) $q->whereBetween('co.created_at', [$start, $end]);
            if ($search !== '') {
                $this->applyArabicSearch($q, ['co.name_or_title', 'cp.name', 'cp.client_name', 'co.person_name', 'co.invoice_ref', 'co.notes'], $search);
            }
            foreach ($q->orderByDesc('co.created_at')->limit(500)->get() as $r) {
                $typeLabel = match($r->op_type) {
                    'material'   => 'خامات مقاولات',
                    'technician' => 'مصنعية صنايعي/فني',
                    'petty'      => 'مصروف نثري للعميل',
                    'overhead'   => 'مصروف موقع وتشغيل',
                    'commission' => 'عمولة تسويق مقاولات',
                    default      => 'حركة مقاولات'
                };
                $icon = match($r->op_type) {
                    'material'   => 'fa-cubes-stacked',
                    'technician' => 'fa-helmet-safety',
                    'petty'      => 'fa-receipt',
                    'commission' => 'fa-percent',
                    default      => 'fa-building-shield'
                };
                $color = match($r->op_type) {
                    'material'   => 'warning',
                    'technician' => 'info',
                    'petty'      => 'teal',
                    'commission' => 'secondary',
                    default      => 'primary'
                };

                $supTxt = floatval($r->supervision_pct) > 0 ? " (إشراف {$r->supervision_pct}%)" : "";
                $desc = "مشروع: {$r->project_name} | عميل: {$r->client_name} | بند: " . ($r->item_title ?: 'عام');
                if (!empty($r->person_name)) $desc .= " | طرف: {$r->person_name}";
                $desc .= " | كمية: {$r->quantity} {$r->unit} | تكلفة: " . number_format((float)$r->total_cost, 2) . " ج";
                if ($r->is_billable) {
                    $desc .= " | بيع: " . number_format((float)$r->total_selling, 2) . " ج{$supTxt}";
                }
                if (!empty($r->invoice_ref)) $desc .= " | فاتورة: {$r->invoice_ref}";

                $rows->push([
                    'type'           => 'contracting',
                    'type_label'     => $typeLabel,
                    'icon'           => $icon,
                    'color'          => $color,
                    'id'             => $r->id,
                    'date'           => $r->created_at,
                    'title'          => "{$r->name_or_title} — {$r->project_name}",
                    'description'    => $desc,
                    'amount'         => (float)$r->total_selling > 0 ? (float)$r->total_selling : (float)$r->total_cost,
                    'profit'         => (float)$r->profit_amount,
                    'editable'       => false,
                    'is_contracting' => true,
                    'editor'         => 'contracting',
                    'is_edit'        => false,
                    'supersedes'     => null,
                    'superseded_by'  => null,
                    'superseded_at'  => null,
                    'is_cancelled'   => false,
                ]);
            }
        }

        // ترتيب موحد بالتاريخ
        $operations = $rows->sortByDesc(function($op) {
            return \Carbon\Carbon::parse($op['date'])->timestamp;
        })->values();

        // إحصائيات سريعة
        $stats = [
            'total'       => $operations->count(),
            'fuel'        => $operations->where('type', 'fuel')->count(),
            'inv'         => $operations->whereIn('type', ['inventory_purchase', 'inventory_movement'])->count(),
            'sales'       => $operations->where('type', 'sale_cash')->count(),
            'financial'   => $operations->whereIn('type', ['expense', 'financial'])->count(),
            'contracting' => $operations->where('type', 'contracting')->count(),
        ];

        return view('operations_log', compact(
            'operations', 'stats', 'type', 'period', 'search', 'fromDate', 'toDate'
        ));
    }

    /**
     * يعرض بيانات عملية للتعديل (JSON).
     */
    public function show(Request $request, string $editor, int $id)
    {
        if (!$this->verifyAdminPin($request)) {
            return response()->json(['error' => 'الرمز السري غير صحيح'], 403);
        }

        switch ($editor) {
            case 'contracting':
                return response()->json(['error' => '⛔ عمليات المقاولات لا يمكن تعديلها أو حذفها من شاشة سجل العمليات.'], 403);
            case 'fuel':                return $this->showFuel($id);
            case 'inventory_purchase':  return $this->showInventoryPurchase($id);
            case 'sale_inventory':
            case 'sale_direct':
            case 'service':             return $this->showSaleCash($id, $editor);
            case 'sale_return':         return $this->showSaleReturn($id);
            case 'expense':             return $this->showExpense($id);
            default: return response()->json(['error' => "نوع عملية غير مدعوم بعد للتعديل: {$editor}."], 400);
        }
    }

    /**
     * يحذف عملية بالكامل (للبنزينة والمصروفات).
     */
    public function destroy(Request $request, string $editor, int $id)
    {
        if (!$this->verifyAdminPin($request)) {
            return back()->with('error', 'الرمز السري غير صحيح.');
        }

        try {
            switch ($editor) {
                case 'contracting':
                    return back()->with('error', '⛔ عمليات المقاولات لا يمكن تعديلها أو حذفها من شاشة سجل العمليات.');
                case 'service':
                    return back()->with('error', '⛔ عمليات الخدمات لا يمكن حذفها — لأنها مرتبطة بمعادلة رأس المال. لو في خطأ في العملية تواصل مع المسؤول.');
                case 'fuel':      return $this->destroyFuel($id);
                case 'expense':   return $this->destroyExpense($id);
                case 'financial': return $this->destroyFinancial($id);
                default:
                    return back()->with('error', "حذف عمليات [{$editor}] غير متاح.");
            }
        } catch (\Throwable $e) {
            return back()->with('error', '❌ فشل الحذف: ' . $e->getMessage());
        }
    }

    /**
     * يحدّث عملية بإعادة بناء الأثر بالكامل (reverse القديم + redo الجديد).
     */
    public function update(Request $request, string $editor, int $id)
    {
        $wantsJson = $request->expectsJson() || $request->ajax() || $editor === 'sale_return';

        if (!$this->verifyAdminPin($request)) {
            if ($wantsJson) {
                return response()->json(['error' => 'الرمز السري غير صحيح.'], 403);
            }
            return back()->with('error', 'الرمز السري غير صحيح.');
        }

        try {
            switch ($editor) {
                case 'contracting':
                    if ($wantsJson) {
                        return response()->json(['error' => '⛔ عمليات المقاولات لا يمكن تعديلها أو حذفها من شاشة سجل العمليات.'], 403);
                    }
                    return back()->with('error', '⛔ عمليات المقاولات لا يمكن تعديلها أو حذفها من شاشة سجل العمليات.');
                case 'fuel':
                    return $this->updateFuel($request, $id);
                case 'inventory_purchase':
                    return $this->updateInventoryPurchase($request, $id);
                case 'service':
                case 'sale_inventory':
                case 'sale_direct':
                    return $this->cancelSaleCash($request, $id);
                case 'sale_return':
                    return $this->returnSaleCash($request, $id);
                case 'expense':
                    return $this->updateExpense($request, $id);
                default:
                    if ($wantsJson) {
                        return response()->json(['error' => "تعديل عمليات [{$editor}] لسه مش متاح."], 400);
                    }
                    return back()->with('error', "تعديل عمليات [{$editor}] لسه مش متاح.");
            }
        } catch (\Throwable $e) {
            if ($wantsJson) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', '❌ فشل التعديل: ' . $e->getMessage());
        }
    }

    // ───────────────────────────────────────────────────────────────────────────
    // عمليات المقاولات والتشطيبات
    // ───────────────────────────────────────────────────────────────────────────

    private function showContracting(int $id)
    {
        $op = DB::table('cont_operations as co')
            ->leftJoin('cont_projects as cp', 'co.project_id', '=', 'cp.id')
            ->leftJoin('cont_project_items as cpi', 'co.item_id', '=', 'cpi.id')
            ->where('co.id', $id)
            ->select('co.*', 'cp.name as project_name', 'cp.client_name', 'cpi.title as item_title')
            ->first();

        if (!$op) {
            return response()->json(['error' => 'العملية غير موجودة.'], 404);
        }

        return response()->json([
            'id'              => $op->id,
            'name_or_title'   => $op->name_or_title,
            'project_name'    => $op->project_name,
            'client_name'     => $op->client_name,
            'item_title'      => $op->item_title ?: 'عام',
            'op_type'         => $op->op_type,
            'quantity'        => floatval($op->quantity),
            'unit'            => $op->unit,
            'cost_price'      => floatval($op->cost_price),
            'supervision_pct' => floatval($op->supervision_pct),
            'selling_price'   => floatval($op->selling_price),
            'person_name'     => $op->person_name,
            'notes'           => $op->notes,
            'invoice_ref'     => $op->invoice_ref,
            'is_billable'     => (bool) $op->is_billable,
        ]);
    }

    private function updateContracting(Request $request, int $id)
    {
        $wantsJson = $request->expectsJson() || $request->ajax();
        $controller = new ContractingController();
        $res = $controller->updateOperation($request, $id);
        $data = $res->getData(true);

        if ($data['success']) {
            if ($wantsJson) {
                return response()->json(['success' => true, 'message' => $data['message']]);
            }
            return back()->with('success', $data['message']);
        } else {
            if ($wantsJson) {
                return response()->json(['error' => $data['message']], 422);
            }
            return back()->with('error', $data['message']);
        }
    }

    private function destroyContracting(Request $request, int $id)
    {
        $wantsJson = $request->expectsJson() || $request->ajax();
        $controller = new ContractingController();
        $res = $controller->destroyOperation($id);
        $data = $res->getData(true);

        if ($data['success']) {
            if ($wantsJson) {
                return response()->json(['success' => true, 'message' => $data['message']]);
            }
            return back()->with('success', $data['message']);
        } else {
            if ($wantsJson) {
                return response()->json(['error' => $data['message']], 422);
            }
            return back()->with('error', $data['message']);
        }
    }

    // ───────────────────────────────────────────────────────────────────────────
    // البنزينة
    // ───────────────────────────────────────────────────────────────────────────

    private function showFuel(int $id)
    {
        $tx = DB::table('fuel_transactions')->where('id', $id)->first();
        if (!$tx) return response()->json(['error' => 'عملية غير موجودة'], 404);

        // العثور على الـ financial_transaction المرتبطة بسحب العهدة (لو موجود)
        $advanceFt = DB::table('financial_transactions')
            ->where('ref_type', 'fuel_transaction')
            ->where('ref_id', $id)
            ->where('type', 'expense')
            ->first();

        $advanceSource = 'none';
        $accountId = null;
        if ($advanceFt) {
            $advanceSource = 'safe';
            $accountId = $advanceFt->from_account_id;
        } elseif ($tx->cash_advance > 0) {
            // عهدة من المحطة
            $advanceSource = 'station';
        }

        // استخراج قيمة الخصم اليدوي المسجل سابقاً
        $profitRate = (float) \App\Services\SystemSetting::get('fuel_profit_rate', 0.01);
        $unitPrice  = (float) ($tx->discount_price_at_time ?? $tx->market_price_at_time ?? 0);
        $fuelDebt   = (float) $tx->liters * $unitPrice;
        $advance    = (float) $tx->cash_advance;
        $grossProfit = ($advanceSource === 'none') ? ($fuelDebt * $profitRate) : (($fuelDebt + $advance) * $profitRate);
        $profitAfterDiscount = (float) $tx->total_on_company - ($fuelDebt + $advance);
        $manualDiscount = max(0, round($grossProfit - $profitAfterDiscount, 2));

        // قراءة العداد للعملية السابقة لنفس الشاحنة/السائق (للتوضيح للمستخدم أثناء التعديل)
        $vehicleKeyCar = (!empty($tx->car_number) && $tx->car_number !== '-') ? $tx->car_number : null;
        $prevOdometer = DB::table('fuel_transactions')
            ->where('id', '<', $id)
            ->whereNull('superseded_by')
            ->where(function ($q) use ($vehicleKeyCar, $tx) {
                if ($vehicleKeyCar) {
                    $q->where('car_number', $vehicleKeyCar);
                } else {
                    $q->where('driver_name', $tx->driver_name);
                }
            })
            ->whereNotNull('odometer')
            ->where('odometer', '>', 0)
            ->orderByDesc('id')
            ->value('odometer');

        $companies = DB::table('transport_companies')->get();
        $stations  = DB::table('gas_stations')->get();
        $items     = DB::table('items')->get();
        $accounts  = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $deductions = DB::table('fuel_deductions')->get();

        // قوائم الإكمال التلقائي (أرقام الشاحنات، السائقين، والوجهات)
        $knownCars = DB::table('fuel_transactions')
            ->whereNotNull('car_number')->where('car_number', '!=', '')->where('car_number', '!=', '-')
            ->distinct()->orderBy('car_number')->pluck('car_number')->values();

        $knownDrivers = DB::table('fuel_transactions')
            ->whereNotNull('driver_name')->where('driver_name', '!=', '')->where('driver_name', '!=', '-')
            ->distinct()->orderBy('driver_name')->pluck('driver_name')->values();

        $destinationsSetting = trim((string) \App\Services\SystemSetting::get('fuel_destinations', ''));
        $destinations = [];
        if ($destinationsSetting !== '') {
            foreach (preg_split('/[\r\n,]+/', $destinationsSetting) as $line) {
                $t = trim($line);
                if ($t !== '') $destinations[] = $t;
            }
        }
        $dbDestinations = DB::table('fuel_transactions')
            ->whereNotNull('notes')->whereRaw("TRIM(notes) != ''")
            ->distinct()->limit(40)->pluck('notes')->map(fn($n) => trim($n))->filter()->all();
        $destinations = array_values(array_unique(array_merge($destinations, $dbDestinations)));

        $hasPaidDebts = DB::table('company_debts')
            ->where('source_type', 'fuel_transaction')
            ->where('source_id', $id)
            ->where('paid_amount', '>', 0)
            ->exists();

        if (!$hasPaidDebts) {
            $oldInst = DB::table('installments')
                ->where('source_type', 'fuel_transaction')
                ->where('source_id', $id)
                ->first();
            if ($oldInst) {
                $hasPaidDebts = DB::table('installment_payments')
                    ->where('installment_id', $oldInst->id)
                    ->exists();
            }
        }

        return response()->json([
            'tx'              => $tx,
            'advance_source'  => $advanceSource,
            'account_id'      => $accountId,
            'manual_discount' => $manualDiscount,
            'prev_odometer'   => $prevOdometer,
            'profit_rate'     => $profitRate,
            'deductions'      => $deductions,
            'companies'       => $companies,
            'stations'        => $stations,
            'items'           => $items,
            'accounts'        => $accounts,
            'known_cars'      => $knownCars,
            'known_drivers'   => $knownDrivers,
            'destinations'    => $destinations,
            'has_paid_debts'  => $hasPaidDebts,
        ]);
    }

    private function updateFuel(Request $request, int $id)
    {
        $request->validate([
            'company_id'      => 'required|integer',
            'station_id'      => 'required|integer',
            'car_number'      => 'nullable|string|max:120',
            'driver_name'     => 'nullable|string|max:120',
            'driver_car'      => 'nullable|string|max:120',
            'item_id'         => 'required',                 // 0 = بدون وقود
            'quantity'        => 'nullable|numeric|min:0',
            'fuel_price'      => 'nullable|numeric|min:0',
            'odometer'        => 'nullable|numeric|min:0',
            'is_full_tank'    => 'nullable',
            'notes'           => 'nullable|string|max:500',
            'advance_source'  => 'required|in:none,safe,station',
            'cash_advance'    => 'nullable|numeric|min:0',
            'account_id'      => 'required_if:advance_source,safe',
            'manual_discount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {
            $old = DB::table('fuel_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$old) throw new \Exception('العملية غير موجودة.');

            if (!empty($old->superseded_by)) {
                throw new \Exception("هذه العملية اتعدلت قبل كده — راجع التعديل في عملية #{$old->superseded_by}.");
            }

            // 📝 حفظ snapshot قبل التعديل
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'     => 'fuel_transaction',
                'source_id'       => $id,
                'new_source_id'   => $id,
                'action'          => 'edit',
                'snapshot_before' => json_encode((array)$old, JSON_UNESCAPED_UNICODE),
                'edited_by'       => $user->id ?? null,
                'edited_by_name'  => $user->name ?? null,
                'notes'           => 'تعديل عملية بنزينة من سجل العمليات',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // ═══════════════════════════════════════════════════
            // (1) عكس الأثر المالي القديم بالكامل
            // ═══════════════════════════════════════════════════

            // أ) التحقق إن المستحقات لسه مش مدفوعة (لو فيها سداد، نمنع التعديل)
            $hasPaidDebts = DB::table('company_debts')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)
                ->where('paid_amount', '>', 0)->exists();
            if ($hasPaidDebts) {
                throw new \Exception('لا يمكن التعديل: في مستحقات مرتبطة بهذه العملية تم سدادها جزئياً أو كلياً. الغِ السداد أولاً.');
            }

            $oldInst = DB::table('installments')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)->first();
            if ($oldInst) {
                $hasPayments = DB::table('installment_payments')->where('installment_id', $oldInst->id)->exists();
                if ($hasPayments) {
                    throw new \Exception('لا يمكن التعديل: شركة النقل سددت جزء من المستحقات. الغِ السداد أولاً.');
                }
                DB::table('installments')->where('id', $oldInst->id)->delete();
            }

            // ب) إرجاع العهدة المسحوبة من الخزنة (لو كان عندنا financial_transaction expense مربوطة)
            $oldExpense = DB::table('financial_transactions')
                ->where('ref_type', 'fuel_transaction')->where('ref_id', $id)
                ->where('type', 'expense')->first();
            if ($oldExpense) {
                if ($oldExpense->from_account_id) {
                    DB::table('accounts')->where('id', $oldExpense->from_account_id)->increment('balance', $oldExpense->amount);
                }
                DB::table('financial_transactions')->where('id', $oldExpense->id)->delete();
            }

            // ج) حذف الديون المرتبطة (الوقود + الاستقطاعات)
            DB::table('company_debts')->where('source_type', 'fuel_transaction')->where('source_id', $id)->delete();

            // ═══════════════════════════════════════════════════
            // (2) تجهيز البيانات الجديدة وتحديث العملية
            // ═══════════════════════════════════════════════════

            $company = DB::table('transport_companies')->where('id', $request->company_id)->first();
            $station = DB::table('gas_stations')->where('id', $request->station_id)->first();
            if (!$company || !$station) throw new \Exception('الشركة أو المحطة غير موجودة.');

            $companyName = $company->company_name ?? $company->name;
            $stationName = $station->station_name ?? $station->name;

            $carNumber  = trim($request->car_number ?? '');
            $driverName = trim($request->driver_name ?? '');

            if (!$carNumber && !$driverName && $request->filled('driver_car')) {
                $driverName = trim($request->driver_car);
            }
            if (!$carNumber) {
                $carNumber = (!empty($old->car_number) && $old->car_number !== '-') ? $old->car_number : '-';
            }
            if (!$driverName) {
                $driverName = ($carNumber !== '-') ? $carNumber : 'غير محدد';
            }

            $driverCarDisplay = ($carNumber !== '-' ? $carNumber : '')
                . ($driverName && $driverName !== $carNumber ? " - {$driverName}" : '');
            $driverCarDisplay = trim($driverCarDisplay) ?: 'غير محدد';

            $itemName = 'وقود فقط (بدون عهدة)';
            $dbPrice  = 0;

            if ($request->item_id != "0") {
                $item = DB::table('items')->where('id', $request->item_id)->first();
                if (!$item) throw new \Exception('الصنف غير موجود.');
                $itemName = $item->item_name ?? $item->name;
                $dbPrice  = floatval($item->selling_price ?? $item->cash_price ?? $item->original_price ?? $item->price ?? 0);
                if ($dbPrice == 0) $dbPrice = (float) \App\Services\SystemSetting::get('default_fuel_price', 20);
            }

            if ($request->filled('fuel_price') && floatval($request->fuel_price) > 0) {
                $dbPrice = floatval($request->fuel_price);
            }

            $advanceSource  = $request->advance_source;
            $quantity       = ($request->item_id == "0") ? 0 : floatval($request->quantity ?? 0);
            $advance        = ($advanceSource === 'none') ? 0 : floatval($request->cash_advance ?? 0);
            $fuelDebt       = $quantity * $dbPrice;
            $manualDiscount = floatval($request->manual_discount ?? 0);

            if ($advanceSource !== 'none' && $advance <= 0) {
                throw new \Exception('⚠️ يرجى إدخال مبلغ العهدة النقدية المطلوبة للسائق.');
            }
            if ($fuelDebt <= 0 && $advance <= 0) {
                throw new \Exception('⚠️ لا يمكن حفظ عملية فارغة — أدخل كمية وقود أو عهدة على الأقل.');
            }

            $profitRate = (float) \App\Services\SystemSetting::get('fuel_profit_rate', 0.01);

            if ($advanceSource === 'none') {
                $grossProfit    = $fuelDebt * $profitRate;
                $totalToStation = $fuelDebt;
            } elseif ($advanceSource === 'station') {
                $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                $totalToStation = $fuelDebt + $advance;
            } else { // safe
                $grossProfit    = ($fuelDebt + $advance) * $profitRate;
                $totalToStation = $fuelDebt;
            }

            $profitAfterDiscount = $grossProfit - $manualDiscount;
            $totalOnCompany      = $fuelDebt + $advance + $profitAfterDiscount;

            $deductions = DB::table('fuel_deductions')->get();
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

            $currentOdometer = ($request->filled('odometer') && floatval($request->odometer) > 0)
                ? floatval($request->odometer)
                : null;
            $isFullTank = $request->has('is_full_tank') && in_array($request->is_full_tank, [1, '1', true, 'true', 'on'], true) ? 1 : 0;
            $notes      = $request->filled('notes') ? trim($request->notes) : null;

            // تحديث السجل الأصلي في مكانه للحفاظ على الترتيب الزمني للرحلات وقراءات العداد
            DB::table('fuel_transactions')->where('id', $id)->update([
                'company_id'             => $company->id,
                'station_id'             => $station->id,
                'driver_name'            => $driverName,
                'car_number'             => $carNumber,
                'fuel_type'              => $itemName,
                'liters'                 => $quantity,
                'cash_advance'           => $advance,
                'market_price_at_time'   => $dbPrice,
                'discount_price_at_time' => $dbPrice,
                'total_to_station'       => $totalToStation,
                'total_on_company'       => $totalOnCompany,
                'ahmed_profit'           => $netProfit,
                'odometer'               => $currentOdometer,
                'is_full_tank'           => $isFullTank,
                'notes'                  => $notes,
            ]);

            // إعادة حساب المسافات وكفاءة الاستهلاك وتكلفة الكيلو لجميع رحلات الشاحنات
            (new GasStationController())->syncAndRecalculateFuelTrips();

            // سحب العهدة من الخزنة (لو safe)
            if ($advanceSource === 'safe' && $advance > 0) {
                $accId = $request->account_id;
                $account = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                if (!$account || $account->balance < $advance) throw new \Exception('رصيد الخزنة لا يكفي لصرف العهدة.');

                DB::table('accounts')->where('id', $accId)->decrement('balance', $advance);
                DB::table('financial_transactions')->insert([
                    'type' => 'expense', 'amount' => $advance, 'from_account_id' => $accId,
                    'notes' => "صرف عهدة نقدية لـ ({$driverCarDisplay}) | شركة {$companyName}",
                    'status' => 'active', 'ref_type' => 'fuel_transaction', 'ref_id' => $id,
                    'created_at' => now(),
                ]);
            }

            // الاستقطاعات
            foreach ($deductionRows as $row) {
                DB::table('company_debts')->insert([
                    'creditor_name'     => $row['name'],
                    'reason'            => "استقطاع من بنزينة ({$stationName}) | الشاحنة/السائق: {$driverCarDisplay} | شركة النقل: {$companyName}",
                    'total_amount'      => $row['amount'], 'paid_amount' => 0, 'remaining_balance' => $row['amount'],
                    'category'          => 'استقطاعات',
                    'source_type'       => 'fuel_transaction', 'source_id' => $id,
                    'created_at'        => now(),
                ]);
            }

            // دين المحطة
            if ($totalToStation > 0) {
                $reasonDetails = "الشاحنة/السائق: {$driverCarDisplay} | " .
                                 "شركة النقل: {$companyName} | " .
                                 "الكمية: {$quantity} لتر | " .
                                 "عهدة نقدية: {$advance} ج | " .
                                 "صافي الربح المُحقق: {$netProfit} ج";

                DB::table('company_debts')->insert([
                    'creditor_name'     => trim($stationName),
                    'reason'            => $reasonDetails,
                    'total_amount'      => $totalToStation, 'paid_amount' => 0, 'remaining_balance' => $totalToStation,
                    'category'          => 'وقود',
                    'source_type'       => 'fuel_transaction', 'source_id' => $id,
                    'created_at'        => now(),
                ]);
            }

            // دين شركة النقل
            DB::table('installments')->insert([
                'customer_name' => $companyName, 'customer_phone' => 'بدون',
                'product_name'  => "بنزينة ({$stationName}) - {$itemName} | {$driverCarDisplay}",
                'cash_price' => $totalOnCompany, 'discount' => 0, 'down_payment' => 0,
                'remaining_after_down' => $totalOnCompany, 'installment_months' => 0, 'interest_rate' => 0,
                'total_after_interest' => $totalOnCompany, 'monthly_installment' => $totalOnCompany, 'due_day' => date('d'),
                'remaining_balance' => $totalOnCompany, 'profit' => $netProfit,
                'category' => 'بنزينة', 'sale_type' => 'fuel',
                'start_date' => now()->toDateString(), 'created_at' => now(),
                'fuel_liters' => $quantity, 'cash_custody' => $advance,
                'source_type' => 'fuel_transaction', 'source_id' => $id,
            ]);

            // Audit
            AuditService::warning(
                'update', 'gas_station',
                "تم تعديل عملية بنزينة #{$id} — الشاحنة: {$carNumber} | السائق: {$driverName} | الوجهة: " . ($notes ?: '—') . " | العداد: " . ($currentOdometer ?: '—') . " | لتر: {$quantity} | عهدة: {$advance} | محطة: {$stationName}",
                (array) $old,
                [
                    'fuel_tx_id'   => $id,
                    'car_number'   => $carNumber,
                    'driver_name'  => $driverName,
                    'liters'       => $quantity,
                    'fuel_price'   => $dbPrice,
                    'advance'      => $advance,
                    'odometer'     => $currentOdometer,
                    'is_full_tank' => $isFullTank,
                    'notes'        => $notes,
                ],
                'fuel_transaction', $id
            );
        });

        return redirect()->route('operations.index')->with('success', '✅ تم تعديل عملية البنزينة بالكامل، وتحديث العداد والوجهة، وإعادة حساب مسافات وكفاءة الرحلات.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // توريد المخزن (sales row)
    // ───────────────────────────────────────────────────────────────────────────

    private function showInventoryPurchase(int $id)
    {
        $row = DB::table('sales')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'الصنف غير موجود'], 404);

        $soldQty = (float)$row->quantity - (float)$row->remaining_quantity;
        $suppliers = DB::table('suppliers')->get();
        $accounts  = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        return response()->json([
            'row'        => $row,
            'sold_qty'   => $soldQty,
            'suppliers'  => $suppliers,
            'accounts'   => $accounts,
        ]);
    }

    private function updateInventoryPurchase(Request $request, int $id)
    {
        $request->validate([
            'product_name'    => 'required|string|max:255',
            'category'        => 'required|string|max:255',
            'supplier_name'   => 'required|string|max:255',
            'purchase_price'  => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'quantity'        => 'required|numeric|min:1',
            'settle_method'   => 'required|in:supplier_debt,account',  // كيف نسوّي فرق التكلفة
            'settle_account'  => 'required_if:settle_method,account',
        ]);

        DB::transaction(function () use ($request, $id) {
            $row = DB::table('sales')->where('id', $id)->lockForUpdate()->first();
            if (!$row) throw new \Exception('الصنف غير موجود.');

            // منع التعديل المتكرر
            if (!empty($row->last_edited_at)) {
                throw new \Exception('هذا الصنف اتعدل قبل كده — مينفعش يتعدل تاني.');
            }

            // 📝 snapshot قبل التعديل
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'    => 'sales',
                'source_id'      => $id,
                'action'         => 'edit',
                'snapshot_before'=> json_encode((array)$row, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => 'تعديل توريد مخزن من سجل العمليات',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $oldQty    = (float)$row->quantity;
            $oldPrice  = (float)$row->purchase_price;
            $oldSold   = $oldQty - (float)$row->remaining_quantity;
            $oldCost   = $oldQty * $oldPrice;

            $newQty    = floatval($request->quantity);
            $newPrice  = floatval($request->purchase_price);
            $newSPrice = floatval($request->selling_price);
            $newSup    = trim($request->supplier_name);
            $newName   = trim($request->product_name);
            $newCat    = trim($request->category);
            $newCost   = $newQty * $newPrice;

            if ($newQty < $oldSold) {
                throw new \Exception("الكمية الجديدة ({$newQty}) أقل من المباع منها فعلاً ({$oldSold}). ارجع المبيعات أولاً.");
            }

            $newRemaining = $newQty - $oldSold;
            $diff = $newCost - $oldCost; // موجب: نحتاج زيادة، سالب: نخفّض

            // (1) تعديل سطر المخزن
            DB::table('sales')->where('id', $id)->update([
                'product_name'       => $newName,
                'category'           => $newCat,
                'supplier_name'      => $newSup,
                'purchase_price'     => $newPrice,
                'selling_price'      => $newSPrice,
                'quantity'           => $newQty,
                'remaining_quantity' => $newRemaining,
                'last_edited_at'     => now(),
                'updated_at'         => now(),
            ]);

            // (2) تسوية الفرق المالي
            if (abs($diff) > 0.01) {
                if ($request->settle_method === 'supplier_debt') {
                    // نعدل دين المورد. لو زاد الكوست → نضيف دين، لو نقص → نخصم من دين قائم
                    if ($diff > 0) {
                        DB::table('company_debts')->insert([
                            'creditor_name'     => $newSup,
                            'reason'            => "تعديل توريد: {$newName} — زيادة تكلفة بـ " . number_format($diff, 0) . " ج",
                            'total_amount'      => $diff,
                            'paid_amount'       => 0,
                            'remaining_balance' => $diff,
                            'category'          => 'مورد',
                            'created_at'        => now(),
                        ]);
                    } else {
                        $reduce = abs($diff);
                        $debts = DB::table('company_debts')
                            ->where('creditor_name', $newSup)
                            ->where('remaining_balance', '>', 0)
                            ->orderByDesc('created_at')->get();
                        foreach ($debts as $d) {
                            if ($reduce <= 0) break;
                            $rem = (float)$d->remaining_balance;
                            $take = min($rem, $reduce);
                            DB::table('company_debts')->where('id', $d->id)->update([
                                'total_amount'      => DB::raw("total_amount - {$take}"),
                                'remaining_balance' => DB::raw("remaining_balance - {$take}"),
                                'updated_at'        => now(),
                            ]);
                            $reduce -= $take;
                        }
                        if ($reduce > 0) {
                            throw new \Exception("الدين المتاح للمورد ({$newSup}) أقل من قيمة التخفيض المطلوبة. اختر تسوية بالخزنة أو راجع الديون.");
                        }
                    }
                } else { // account
                    $accId = $request->settle_account;
                    $acc = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                    if (!$acc) throw new \Exception('الخزنة غير موجودة.');

                    if ($diff > 0) {
                        if ($acc->balance < $diff) throw new \Exception('رصيد الخزنة لا يكفي للزيادة.');
                        DB::table('accounts')->where('id', $accId)->decrement('balance', $diff);
                        DB::table('financial_transactions')->insert([
                            'type' => 'expense', 'amount' => $diff, 'from_account_id' => $accId,
                            'notes' => "تسوية تعديل توريد: {$newName} — فرق زيادة", 'status' => 'active', 'created_at' => now(),
                        ]);
                    } else {
                        $back = abs($diff);
                        DB::table('accounts')->where('id', $accId)->increment('balance', $back);
                        DB::table('financial_transactions')->insert([
                            'type' => 'income', 'amount' => $back, 'to_account_id' => $accId,
                            'notes' => "تسوية تعديل توريد: {$newName} — استرداد فرق", 'status' => 'active', 'created_at' => now(),
                        ]);
                    }
                }
            }

            AuditService::warning(
                'update', 'inventory',
                "تعديل توريد مخزن #{$id} — كمية: {$oldQty}→{$newQty} | شراء: {$oldPrice}→{$newPrice} | فرق: " . number_format($diff,0),
                (array) $row,
                $request->only(['product_name','category','supplier_name','purchase_price','selling_price','quantity','settle_method','settle_account']),
                'sales', $id
            );
        });

        return redirect()->route('operations.index')->with('success', '✅ تم تعديل التوريد وتسوية الفرق المالي.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // إلغاء بيعة نقدية (sale_inventory / sale_direct / service)
    // ───────────────────────────────────────────────────────────────────────────

    private function showSaleCash(int $id, string $editor)
    {
        $row = DB::table('installments')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'البيعة غير موجودة'], 404);
        if ($this->isContractingInstallment($row)) {
            return response()->json(['error' => '⛔ هذه العملية تخص المقاولات ولا يمكن تعديلها أو حذفها من سجل العمليات.'], 403);
        }

        // البحث عن الـ income FT المرتبطة (paid_amount = down_payment) في نافذة ±5 ثوان
        $relatedIncomeFt = null;
        if ((float)$row->down_payment > 0) {
            $createdAt = Carbon::parse($row->created_at);
            $relatedIncomeFt = DB::table('financial_transactions')
                ->where('type', 'income')
                ->where('amount', $row->down_payment)
                ->where('status', 'active')
                ->whereBetween('created_at', [$createdAt->copy()->subSeconds(5), $createdAt->copy()->addSeconds(5)])
                ->whereNotNull('to_account_id')
                ->orderByDesc('id')->first();
        }

        return response()->json([
            'row'          => $row,
            'editor'       => $editor,
            'income_ft'    => $relatedIncomeFt,
            'has_payments' => DB::table('installment_payments')->where('installment_id', $id)->exists(),
        ]);
    }

    private function cancelSaleCash(Request $request, int $id)
    {
        $returnedItems = [];
        $unmatchedItems = false;

        DB::transaction(function () use ($id, &$returnedItems, &$unmatchedItems) {
            $inst = DB::table('installments')->where('id', $id)->lockForUpdate()->first();
            if (!$inst) throw new \Exception('البيعة غير موجودة.');
            if ($this->isContractingInstallment($inst)) {
                throw new \Exception('⛔ هذه العملية تخص المقاولات ولا يمكن تعديلها أو حذفها من سجل العمليات.');
            }

            if (Schema::hasTable('operation_revisions') && DB::table('operation_revisions')
                ->where('source_type', 'installments')
                ->where('source_id', $id)
                ->where('action', 'partial_return')
                ->exists()) {
                throw new \Exception('تم تنفيذ مرتجع جزئي على هذه الفاتورة مسبقاً — لإرجاع باقي الأصناف استخدم زر (مرتجع) بدلاً من حذف العملية.');
            }

            $createdAt = Carbon::parse($inst->created_at);
            $windowStart = $createdAt->copy()->subSeconds(5);
            $windowEnd   = $createdAt->copy()->addSeconds(5);
            $paid        = (float)$inst->down_payment;

            // ══════════════════════════════════════════════════════════
            // الحالة الوحيدة للمنع: في سداد فعلي على المستحقات/المديونيات
            // ══════════════════════════════════════════════════════════

            // (أ) دفعات إضافية بعد المقدم: إجمالي installment_payments > المقدم
            $totalPaid = (float) DB::table('installment_payments')
                ->where('installment_id', $id)->sum('amount_paid');
            if ($totalPaid > $paid + 0.01) {
                throw new \Exception(
                    'في دفعات إضافية مسجلة على البيعة دي (إجمالي مدفوع: '
                    . number_format($totalPaid, 0)
                    . ' ج، المقدم الأصلي: '
                    . number_format($paid, 0)
                    . ' ج). الغِ الدفعات الإضافية أولاً من شاشة الأقساط/المبيعات.'
                );
            }

            // (ب) سداد على ديون المورد/العمولات المرتبطة بالبيعة
            $supplierDebtsPaid = DB::table('company_debts')
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->where(function ($q) {
                    $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                })
                ->where('paid_amount', '>', 0)
                ->exists();
            if ($supplierDebtsPaid) {
                throw new \Exception(
                    'في تسديد فعلي على ديون مورد أو عمولات مرتبطة بالبيعة دي. الغِ التسديد أولاً من شاشة الديون/المستحقات.'
                );
            }

            // 📝 snapshot قبل الحذف (للأرشيف)
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'    => 'installments',
                'source_id'      => $id,
                'action'         => 'delete',
                'snapshot_before'=> json_encode((array)$inst, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => 'حذف بيعة كامل من سجل العمليات (كأنها لم تحدث)',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // (1) إرجاع البضاعة للمخزن من snapshot الأصناف (لو مخزون)
            if (($inst->sale_type === 'inventory' || $inst->category === 'مبيعات مخزن') && !empty($inst->inventory_items)) {
                $items = json_decode($inst->inventory_items, true);
                if (is_array($items)) {
                    foreach ($items as $it) {
                        $saleId = (int)($it['sale_id'] ?? 0);
                        $qty    = (float)($it['qty'] ?? 0);
                        if ($saleId <= 0 || $qty <= 0) continue;
                        $exists = DB::table('sales')->where('id', $saleId)->first();
                        if (!$exists) {
                            $unmatchedItems = true;
                            continue;
                        }
                        DB::table('sales')->where('id', $saleId)->update([
                            'remaining_quantity' => DB::raw("remaining_quantity + {$qty}"),
                            'inventory_status'   => 'to_inventory',
                            'updated_at'         => now(),
                        ]);
                        $returnedItems[] = "{$qty}× {$it['product_name']}";
                    }
                }
            } elseif ($inst->sale_type === 'inventory' || $inst->category === 'مبيعات مخزن') {
                // بيعة قديمة قبل ربط الـ items - مش هنقدر نطابق
                $unmatchedItems = true;
            }

            // (2) إرجاع المقدم من الخزنة وحذف الـ income FT (مش بس تعليمها cancelled)
            if ($paid > 0) {
                $ft = DB::table('financial_transactions')
                    ->where('type', 'income')
                    ->where('amount', $paid)
                    ->where('status', 'active')
                    ->whereNotNull('to_account_id')
                    ->whereBetween('created_at', [$windowStart, $windowEnd])
                    ->orderByDesc('id')->first();

                if ($ft) {
                    $acc = DB::table('accounts')->where('id', $ft->to_account_id)->lockForUpdate()->first();
                    if ($acc && $acc->balance < $paid) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المقدم المطلوب إرجاعه. أودِع المبلغ أولاً أو ألغِ السحوبات اللاحقة.");
                    }
                    DB::table('accounts')->where('id', $ft->to_account_id)->decrement('balance', $paid);
                    DB::table('financial_transactions')->where('id', $ft->id)->delete();
                }
            }

            // (3) حذف الـ FT المرتبطة (خصومات + مصروفات + عمولات) + إرجاع الأرصدة لو expense
            $ftWindow = DB::table('financial_transactions')
                ->whereIn('type', ['discount', 'general_expense', 'commission', 'expense'])
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->get();
            foreach ($ftWindow as $ft) {
                if (in_array($ft->type, ['general_expense', 'expense']) && $ft->from_account_id) {
                    DB::table('accounts')->where('id', $ft->from_account_id)->increment('balance', $ft->amount);
                }
                DB::table('financial_transactions')->where('id', $ft->id)->delete();
            }

            // (4) حذف ديون المورد/العمولات المرتبطة (مش بنزينة، ومافيش paid_amount لأننا منعنا قبل كده)
            DB::table('company_debts')
                ->where(function ($q) {
                    $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                })
                ->whereBetween('created_at', [$windowStart, $windowEnd])
                ->delete();

            // (5) حذف installment_expenses لو موجود
            if (Schema::hasTable('installment_expenses')) {
                DB::table('installment_expenses')->where('installment_id', $id)->delete();
            }

            // (6) حذف الـ installment_payments (المقدم نفسه)
            DB::table('installment_payments')->where('installment_id', $id)->delete();

            // (7) حذف سطر sales اللي اتعمل وقت البيع المباشر/الخدمات (inventory_status='sold')
            if ($inst->sale_type === 'direct' || $inst->category === 'خدمات' || $inst->category === 'مبيعات مباشرة') {
                DB::table('sales')
                    ->where('inventory_status', 'sold')
                    ->where('product_name', $inst->product_name)
                    ->whereBetween('created_at', [$windowStart, $windowEnd])
                    ->delete();
            }

            // (8) حذف البيعة نفسها — كأنها لم تحدث
            // (8) Delete installment
            if (str_contains($inst->category ?? '', 'تكييفات') || str_contains($inst->product_name ?? '', 'تكييفات') || str_contains($inst->category ?? '', 'خدمة')) {
                $op = DB::table('ac_operations')->whereBetween('created_at', [$windowStart, $windowEnd])->first();
                if ($op) {
                    DB::table('ac_operations')->where('id', $op->id)->update(['status' => 'cancelled']);
                }
            }
            DB::table('installments')->where('id', $id)->delete();

            AuditService::warning(
                'delete', 'sales',
                "حذف بيعة #{$id} ({$inst->category}) — العميل: {$inst->customer_name} | المبلغ: " . number_format((float)$inst->cash_price, 0),
                (array) $inst, null, 'installments', $id
            );
        });

        $msg = '✅ تم حذف البيعة بالكامل وإرجاع كل القيود المرتبطة (كأنها لم تحدث).';
        if (!empty($returnedItems)) {
            $msg .= ' البضاعة رجعت للمخزن: ' . implode(' + ', $returnedItems);
        }
        if ($unmatchedItems) {
            $msg .= ' ⚠️ في أصناف ما اتربطتش بالمخزن (بيعة قديمة) — راجع وأضف الكميات يدوياً.';
        }
        return redirect()->route('operations.index')->with('success', $msg);
    }

    // ───────────────────────────────────────────────────────────────────────────
    // مرتجع بيعة (يدعم المرتجع الجزئي والكامل)
    // ───────────────────────────────────────────────────────────────────────────

    private function getInstallmentExtrasTotal(object $inst): float
    {
        if (Schema::hasTable('installment_expenses')) {
            $exp = DB::table('installment_expenses')->where('installment_id', $inst->id)->first();
            if ($exp) {
                return round(
                    (float) ($exp->transport_cost ?? 0)
                    + (float) ($exp->installation_cost ?? 0)
                    + (float) ($exp->materials_cost ?? 0),
                    2
                );
            }
        }
        return round(
            (float) ($inst->transport_cost ?? 0)
            + (float) ($inst->installation_cost ?? 0)
            + (float) ($inst->materials_cost ?? 0),
            2
        );
    }

    private function computeInstallmentPaidInfo(object $inst): array
    {
        $createdAt   = Carbon::parse($inst->created_at);
        $windowStart = $createdAt->copy()->subSeconds(5);
        $windowEnd   = $createdAt->copy()->addSeconds(5);

        $paymentsSum = (float) DB::table('installment_payments')
            ->where('installment_id', $inst->id)
            ->sum('amount_paid');
        $downPayment = (float) ($inst->down_payment ?? 0);
        $hasDownPaymentRow = $downPayment > 0
            ? DB::table('installment_payments')
                ->where('installment_id', $inst->id)
                ->where('amount_paid', $downPayment)
                ->whereBetween('payment_date', [$windowStart, $windowEnd])
                ->exists()
            : false;
        $totalPaid = round($hasDownPaymentRow ? $paymentsSum : ($paymentsSum + $downPayment), 2);

        return [
            'payments_sum'  => round($paymentsSum, 2),
            'down_payment'  => round($downPayment, 2),
            'has_down_row'  => $hasDownPaymentRow,
            'total_paid'    => $totalPaid,
            'window_start'  => $windowStart,
            'window_end'    => $windowEnd,
        ];
    }

    private function buildReturnableInvoiceData(object $inst): array
    {
        $extrasTotal        = $this->getInstallmentExtrasTotal($inst);
        $rawDiscount        = max(0.0, (float) ($inst->discount ?? 0));
        $totalAfterInterest = max(0.0, (float) ($inst->total_after_interest ?? $inst->cash_price ?? 0));
        $itemsNetTotal      = max(0.0, round($totalAfterInterest - $extrasTotal, 2));

        $hasInventoryItems = ($inst->sale_type === 'inventory' || $inst->category === 'مبيعات مخزن')
            && !empty($inst->inventory_items);
        $decoded = $hasInventoryItems ? json_decode($inst->inventory_items, true) : null;
        if (!is_array($decoded) || empty($decoded)) {
            $hasInventoryItems = false;
            $decoded = [];
        }

        $rawItems = [];
        if ($hasInventoryItems) {
            $saleIds = collect($decoded)->pluck('sale_id')->map(fn($v) => (int) $v)->filter()->unique()->values()->all();
            $salesMap = !empty($saleIds)
                ? DB::table('sales')->whereIn('id', $saleIds)->get()->keyBy('id')
                : collect();

            foreach ($decoded as $idx => $it) {
                $qty = (float) ($it['qty'] ?? 0);
                if ($qty <= 0) continue;
                $saleId = (int) ($it['sale_id'] ?? 0);
                $saleRow = $saleId > 0 ? $salesMap->get($saleId) : null;
                $sellPrice = (float) ($it['selling_price'] ?? 0);
                if ($sellPrice <= 0 && $saleRow) {
                    $sellPrice = (float) ($saleRow->selling_price ?? 0);
                }
                $purchasePrice = $saleRow ? (float) ($saleRow->purchase_price ?? 0) : 0.0;

                $rawItems[] = [
                    'index'          => count($rawItems),
                    'sale_id'        => $saleId,
                    'product_name'   => $it['product_name'] ?? ($saleRow->product_name ?? $inst->product_name),
                    'qty'            => $qty,
                    'selling_price'  => $sellPrice,
                    'purchase_price' => $purchasePrice,
                ];
            }
        }

        if (empty($rawItems)) {
            $hasInventoryItems = false;
            $qty = max(1.0, (float) ($inst->quantity ?? 1));
            $grossPool = max(0.0, (float) ($inst->cash_price ?? $totalAfterInterest) - $extrasTotal);
            $rawItems[] = [
                'index'          => 0,
                'sale_id'        => 0,
                'product_name'   => $inst->product_name ?? 'صنف',
                'qty'            => $qty,
                'selling_price'  => round($grossPool / $qty, 4),
                'purchase_price' => round((float) ($inst->purchase_cost ?? 0) / $qty, 4),
            ];
        }

        $totalQty = array_sum(array_column($rawItems, 'qty'));
        $grossSubtotal = array_sum(array_map(fn($it) => $it['qty'] * $it['selling_price'], $rawItems));

        // fallback لو فاتورة قديمة فيها selling_price = 0 لبعض الأصناف
        if ($grossSubtotal <= 0 && $totalQty > 0) {
            $fallbackUnitGross = round(max(0.0, (float) ($inst->cash_price ?? $totalAfterInterest) - $extrasTotal) / $totalQty, 4);
            foreach ($rawItems as &$it) {
                $it['selling_price'] = $fallbackUnitGross;
            }
            unset($it);
            $grossSubtotal = array_sum(array_map(fn($it) => $it['qty'] * $it['selling_price'], $rawItems));
        }

        // fallback لتكلفة الشراء لو الباتش اتحذف
        if ((float) ($inst->purchase_cost ?? 0) > 0 && $totalQty > 0) {
            $knownPurchase = array_sum(array_map(fn($it) => $it['qty'] * $it['purchase_price'], $rawItems));
            if ($knownPurchase <= 0) {
                $fallbackUnitCost = round((float) $inst->purchase_cost / $totalQty, 4);
                foreach ($rawItems as &$it) {
                    $it['purchase_price'] = $fallbackUnitCost;
                }
                unset($it);
            }
        }

        $netFactor = $grossSubtotal > 0 ? ($itemsNetTotal / $grossSubtotal) : 1.0;
        $items = [];
        foreach ($rawItems as $it) {
            $unitNet = round($it['selling_price'] * $netFactor, 4);
            $items[] = [
                'index'               => $it['index'],
                'sale_id'             => $it['sale_id'],
                'product_name'        => $it['product_name'],
                'qty'                 => $it['qty'],
                'unit_gross_price'    => round($it['selling_price'], 2),
                'unit_net_price'      => round($unitNet, 2),
                'unit_net_exact'      => $unitNet,
                'unit_purchase_price' => round($it['purchase_price'], 2),
                'line_net_total'      => round($it['qty'] * $unitNet, 2),
            ];
        }

        $hasPriorPartialReturn = Schema::hasTable('operation_revisions')
            && DB::table('operation_revisions')
                ->where('source_type', 'installments')
                ->where('source_id', $inst->id)
                ->where('action', 'partial_return')
                ->exists();

        return [
            'has_inventory_items'      => $hasInventoryItems,
            'items'                    => $items,
            'extras_total'             => $extrasTotal,
            'discount'                 => $rawDiscount,
            'gross_subtotal'           => round($grossSubtotal, 2),
            'items_net_total'          => $itemsNetTotal,
            'total_after_interest'     => round($totalAfterInterest, 2),
            'has_prior_partial_return' => $hasPriorPartialReturn,
        ];
    }

    private function reduceInstallmentPaidAmounts(int $installmentId, float $cashRefund, object $inst, bool $hasDownRow): float
    {
        $downPayment = round((float) ($inst->down_payment ?? 0), 2);
        if ($cashRefund <= 0.001) {
            return $downPayment;
        }

        $reduceDown = min($downPayment, $cashRefund);
        $newDown = max(0.0, round($downPayment - $reduceDown, 2));
        $remToReduceFromPayments = $hasDownRow ? $cashRefund : round($cashRefund - $reduceDown, 2);

        if ($remToReduceFromPayments > 0.001) {
            $payments = DB::table('installment_payments')
                ->where('installment_id', $installmentId)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get();

            foreach ($payments as $pay) {
                if ($remToReduceFromPayments <= 0.001) break;
                $amt = round((float) $pay->amount_paid, 2);
                if ($amt <= $remToReduceFromPayments + 0.001) {
                    DB::table('installment_payments')->where('id', $pay->id)->delete();
                    $remToReduceFromPayments = round($remToReduceFromPayments - $amt, 2);
                } else {
                    DB::table('installment_payments')->where('id', $pay->id)->update([
                        'amount_paid' => round($amt - $remToReduceFromPayments, 2),
                    ]);
                    $remToReduceFromPayments = 0.0;
                }
            }
        }

        return $newDown;
    }

    private function showSaleReturn(int $id)
    {
        $row = DB::table('installments')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'البيعة غير موجودة'], 404);
        if ($this->isContractingInstallment($row)) {
            return response()->json(['error' => '⛔ هذه العملية تخص المقاولات ولا يمكن تعديلها أو حذفها من سجل العمليات.'], 403);
        }

        $paidInfo    = $this->computeInstallmentPaidInfo($row);
        $invoiceData = $this->buildReturnableInvoiceData($row);
        $accounts    = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        // منع المرتجع الكامل فقط لو في عمولة متسددة مرتبطة بالبيعة (استثناء ديون الموردين والوقود)
        $hasPaidSupplierDebts = !$invoiceData['has_prior_partial_return'] && DB::table('company_debts')
            ->whereBetween('created_at', [$paidInfo['window_start'], $paidInfo['window_end']])
            ->where(function ($q) {
                $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
            })
            ->where(function ($q) {
                $q->whereNull('category')->orWhereNotIn('category', ['مورد', 'موردين']);
            })
            ->where('paid_amount', '>', 0)
            ->exists();

        return response()->json([
            'row'                      => $row,
            'items'                    => $invoiceData['items'],
            'extras_total'             => $invoiceData['extras_total'],
            'discount'                 => $invoiceData['discount'],
            'gross_subtotal'           => $invoiceData['gross_subtotal'],
            'items_net_total'          => $invoiceData['items_net_total'],
            'has_prior_partial_return' => $invoiceData['has_prior_partial_return'],
            'total_paid'               => $paidInfo['total_paid'],
            'remaining_balance'        => round(max(0.0, (float) ($row->remaining_balance ?? 0)), 2),
            'accounts'                 => $accounts,
            'has_paid_supplier_debts'  => $hasPaidSupplierDebts,
        ]);
    }

    private function returnSaleCash(Request $request, int $id)
    {
        $returnedItems = [];
        $unmatchedItems = false;
        $cashRefunded = 0.0;
        $debtDeducted = 0.0;
        $isFullReturn = true;

        DB::transaction(function () use ($request, $id, &$returnedItems, &$unmatchedItems, &$cashRefunded, &$debtDeducted, &$isFullReturn) {
            $inst = DB::table('installments')->where('id', $id)->lockForUpdate()->first();
            if (!$inst) throw new \Exception('البيعة غير موجودة.');
            if ($this->isContractingInstallment($inst)) {
                throw new \Exception('⛔ هذه العملية تخص المقاولات ولا يمكن تعديلها أو حذفها من سجل العمليات.');
            }

            $paidInfo    = $this->computeInstallmentPaidInfo($inst);
            $invoiceData = $this->buildReturnableInvoiceData($inst);
            $items       = $invoiceData['items'];
            $windowStart = $paidInfo['window_start'];
            $windowEnd   = $paidInfo['window_end'];
            $totalPaid   = $paidInfo['total_paid'];

            // تحديد الكميات المرتجعة لكل سطر (لو لم يُرسل return_qtys يعتبر مرتجع كامل للخلفية القديمة)
            $inputQtys = $request->input('return_qtys');
            $returnQtysByIndex = [];
            $totalRequestedQty = 0.0;
            $remainingItems = [];

            if (is_array($inputQtys)) {
                foreach ($items as $it) {
                    $idx = $it['index'];
                    $reqQty = round(floatval($inputQtys[$idx] ?? 0), 4);
                    if ($reqQty < 0) {
                        throw new \Exception('كمية المرتجع لا يمكن أن تكون سالبة.');
                    }
                    if ($reqQty > $it['qty'] + 0.0001) {
                        throw new \Exception("الكمية المرتجعة للصنف ({$it['product_name']}) أكبر من المتاح بالفاتورة ({$it['qty']}).");
                    }
                    $reqQty = min($reqQty, $it['qty']);
                    $returnQtysByIndex[$idx] = $reqQty;
                    $totalRequestedQty += $reqQty;

                    $leftQty = round($it['qty'] - $reqQty, 4);
                    if ($leftQty > 0.0001) {
                        $remainingItems[] = [
                            'item'     => $it,
                            'left_qty' => $leftQty,
                        ];
                    }
                }
                if ($totalRequestedQty <= 0.0001) {
                    throw new \Exception('يرجى تحديد كمية أكبر من صفر لصنف واحد على الأقل.');
                }
            } else {
                foreach ($items as $it) {
                    $returnQtysByIndex[$it['index']] = $it['qty'];
                    $totalRequestedQty += $it['qty'];
                }
            }

            $isFullReturn = empty($remainingItems);

            // حساب القيم الإجمالية للمرتجع (صافي بعد الخصم)
            $returnedGrossTotal    = 0.0;
            $returnedPurchaseTotal = 0.0;
            $returnedLines         = [];

            foreach ($items as $it) {
                $retQty = $returnQtysByIndex[$it['index']] ?? 0.0;
                if ($retQty <= 0.0001) continue;

                $lineGross    = round($retQty * $it['unit_gross_price'], 2);
                $linePurchase = round($retQty * $it['unit_purchase_price'], 2);
                $returnedGrossTotal    = round($returnedGrossTotal + $lineGross, 2);
                $returnedPurchaseTotal = round($returnedPurchaseTotal + $linePurchase, 2);

                $returnedLines[] = [
                    'item'          => $it,
                    'ret_qty'       => $retQty,
                    'line_gross'    => $lineGross,
                    'line_purchase' => $linePurchase,
                ];
            }

            $oldGrossSubtotal = $invoiceData['gross_subtotal'];
            $oldDiscount      = $invoiceData['discount'];
            $oldExtras        = $invoiceData['extras_total'];
            $oldItemsNet      = $invoiceData['items_net_total'];
            $oldTotalAfterInt = $invoiceData['total_after_interest'];
            $ratio            = $oldGrossSubtotal > 0 ? ($returnedGrossTotal / $oldGrossSubtotal) : 1.0;

            $newDiscount = $isFullReturn ? 0 : (int) round($oldDiscount * max(0.0, 1.0 - $ratio));
            $returnedDiscountShare = max(0.0, round($oldDiscount - $newDiscount, 2));

            if ($isFullReturn) {
                $returnedNetTotal      = $oldItemsNet;
                $newCashPrice          = 0.0;
                $newTotalAfterInterest = 0.0;
            } elseif (abs(($oldGrossSubtotal - $oldDiscount) - $oldItemsNet) <= 0.05) {
                $newItemsGross         = max(0.0, round($oldGrossSubtotal - $returnedGrossTotal, 2));
                $returnedNetTotal      = max(0.0, round($returnedGrossTotal - $returnedDiscountShare, 2));
                $newCashPrice          = round($newItemsGross + $oldExtras, 2);
                $newTotalAfterInterest = max(0.0, round($newCashPrice - $newDiscount, 2));
            } else {
                $returnedNetTotal      = round($oldItemsNet * $ratio, 2);
                $newTotalAfterInterest = max(0.0, round($oldTotalAfterInt - $returnedNetTotal, 2));
                $newCashPrice          = round($newTotalAfterInterest + $newDiscount, 2);
            }

            $user = session('auth_user');
            $accId = $request->refund_account_id ? (int) $request->refund_account_id : null;

            if ($isFullReturn) {
                // ══════ المرتجع الكامل للفاتورة ══════
                $hasPriorPartial = $invoiceData['has_prior_partial_return'];
                if (!$hasPriorPartial) {
                    $paidOtherDebts = DB::table('company_debts')
                        ->whereBetween('created_at', [$windowStart, $windowEnd])
                        ->where(function ($q) {
                            $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                        })
                        ->where(function ($q) {
                            $q->whereNull('category')->orWhereNotIn('category', ['مورد', 'موردين']);
                        })
                        ->where('paid_amount', '>', 0)
                        ->exists();
                    if ($paidOtherDebts) {
                        throw new \Exception('في تسديد فعلي على عمولات أو التزامات مرتبطة بالبيعة دي. الغِ التسديد أولاً ثم اعمل المرتجع الكامل.');
                    }
                }

                $cashRefunded = $totalPaid;
                $debtDeducted = round(max(0.0, (float) ($inst->remaining_balance ?? 0)), 2);

                DB::table('operation_revisions')->insert([
                    'source_type'     => 'installments',
                    'source_id'       => $id,
                    'action'          => 'return',
                    'snapshot_before' => json_encode((array) $inst, JSON_UNESCAPED_UNICODE),
                    'edited_by'       => $user->id ?? null,
                    'edited_by_name'  => $user->name ?? null,
                    'notes'           => "مرتجع بيعة كامل من سجل العمليات — رد " . number_format($cashRefunded, 2) . " ج للعميل",
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // (1) رد المدفوع للعميل من الخزنة مرة واحدة (بدون حذف إيراد المقدم الأصلي لتفادي الخصم المزدوج)
                $refundFtId = null;
                if ($cashRefunded > 0.001) {
                    if (!$accId) throw new \Exception('لازم تختار خزنة الصرف لرد المبلغ للعميل.');
                    $acc = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                    if (!$acc) throw new \Exception('الخزنة المختارة غير موجودة.');
                    if ((float) $acc->balance < $cashRefunded) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب رده (" . number_format($cashRefunded, 2) . " ج).");
                    }
                    DB::table('accounts')->where('id', $accId)->decrement('balance', $cashRefunded);
                    $refundFtId = DB::table('financial_transactions')->insertGetId([
                        'type'            => 'expense',
                        'amount'          => $cashRefunded,
                        'from_account_id' => $accId,
                        'notes'           => "مرتجع بيعة #{$id} — رد فلوس للعميل: {$inst->customer_name}",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);
                }

                // (2) إرجاع كل الأصناف للمخزن وتسجيل sale_returns
                $this->restoreReturnedItemsToInventory($returnedLines, $invoiceData['has_inventory_items'], $inst, $id, $returnedNetTotal, $returnedGrossTotal, $accId, false, $returnedItems, $unmatchedItems);

                // (3) في حالة الفاتورة التي لم يحدث عليها مرتجع جزئي مسبقاً: تنظيف خصومات/مصاريف/عمولات الإنشاء
                if (!$hasPriorPartial) {
                    $ftWindowQuery = DB::table('financial_transactions')
                        ->whereIn('type', ['discount', 'general_expense', 'commission', 'expense'])
                        ->whereBetween('created_at', [$windowStart, $windowEnd]);
                    if ($refundFtId) {
                        $ftWindowQuery->where('id', '!=', $refundFtId);
                    }
                    $ftWindow = $ftWindowQuery->get();
                    foreach ($ftWindow as $ft) {
                        if (in_array($ft->type, ['general_expense', 'expense']) && $ft->from_account_id) {
                            DB::table('accounts')->where('id', $ft->from_account_id)->increment('balance', $ft->amount);
                        }
                        DB::table('financial_transactions')->where('id', $ft->id)->delete();
                    }

                    DB::table('company_debts')
                        ->where(function ($q) {
                            $q->whereNull('source_type')->orWhere('source_type', '!=', 'fuel_transaction');
                        })
                        ->where(function ($q) {
                            $q->whereNull('category')->orWhereNotIn('category', ['مورد', 'موردين']);
                        })
                        ->whereBetween('created_at', [$windowStart, $windowEnd])
                        ->delete();
                }

                if (Schema::hasTable('installment_expenses')) {
                    DB::table('installment_expenses')->where('installment_id', $id)->delete();
                }
                DB::table('installment_payments')->where('installment_id', $id)->delete();

                if ($inst->sale_type === 'direct' || $inst->category === 'خدمات' || $inst->category === 'مبيعات مباشرة') {
                    DB::table('sales')
                        ->where('inventory_status', 'sold')
                        ->where('product_name', $inst->product_name)
                        ->whereBetween('created_at', [$windowStart, $windowEnd])
                        ->delete();
                }

                if (str_contains($inst->category ?? '', 'تكييفات') || str_contains($inst->product_name ?? '', 'تكييفات') || str_contains($inst->category ?? '', 'خدمة')) {
                    $op = DB::table('ac_operations')->whereBetween('created_at', [$windowStart, $windowEnd])->first();
                    if ($op) {
                        DB::table('ac_operations')->where('id', $op->id)->update(['status' => 'returned']);
                    }
                }

                DB::table('installments')->where('id', $id)->delete();

                AuditService::warning(
                    'return', 'sales',
                    "مرتجع بيعة كامل #{$id} ({$inst->category}) — العميل: {$inst->customer_name} | المردود: " . number_format($cashRefunded, 2),
                    (array) $inst,
                    ['refund_amount' => $cashRefunded, 'debt_deducted' => $debtDeducted, 'refund_account_id' => $accId],
                    'installments', $id
                );
            } else {
                // ══════ المرتجع الجزئي للفاتورة (الفاتورة تبقى مفتوحة بالكميات المتبقية) ══════
                $oldRemaining = round(max(0.0, (float) ($inst->remaining_balance ?? 0)), 2);
                $debtDeducted = min($returnedNetTotal, $oldRemaining);
                $newRemaining = max(0.0, round($oldRemaining - $debtDeducted, 2));
                $cashRefunded = min($totalPaid, max(0.0, round($returnedNetTotal - $debtDeducted, 2)));

                $returnedDesc = array_map(
                    fn($rl) => (fmod($rl['ret_qty'], 1.0) === 0.0 ? (int) $rl['ret_qty'] : $rl['ret_qty']) . '× ' . $rl['item']['product_name'],
                    $returnedLines
                );

                DB::table('operation_revisions')->insert([
                    'source_type'     => 'installments',
                    'source_id'       => $id,
                    'action'          => 'partial_return',
                    'snapshot_before' => json_encode((array) $inst, JSON_UNESCAPED_UNICODE),
                    'edited_by'       => $user->id ?? null,
                    'edited_by_name'  => $user->name ?? null,
                    'notes'           => "مرتجع جزئي لفاتورة #{$id}: " . implode(' + ', $returnedDesc) . " | صافي المرتجع: " . fmtMoney($returnedNetTotal) . " ج (خصم دين: " . fmtMoney($debtDeducted) . " ج، رد كاش: " . fmtMoney($cashRefunded) . " ج)",
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // (1) رد الفائض النقدي من الخزنة (إن وجد) + تحديث المدفوع
                $newDownPayment = $paidInfo['down_payment'];
                if ($cashRefunded > 0.001) {
                    if (!$accId) throw new \Exception('لازم تختار خزنة الصرف لرد المبلغ النقدي للعميل.');
                    $acc = DB::table('accounts')->where('id', $accId)->lockForUpdate()->first();
                    if (!$acc) throw new \Exception('الخزنة المختارة غير موجودة.');
                    if ((float) $acc->balance < $cashRefunded) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من المبلغ المطلوب رده (" . number_format($cashRefunded, 2) . " ج).");
                    }
                    DB::table('accounts')->where('id', $accId)->decrement('balance', $cashRefunded);
                    DB::table('financial_transactions')->insert([
                        'type'            => 'expense',
                        'amount'          => $cashRefunded,
                        'from_account_id' => $accId,
                        'notes'           => "مرتجع جزئي بيعة #{$id} — رد فلوس للعميل: {$inst->customer_name}",
                        'status'          => 'active',
                        'created_at'      => now(),
                    ]);

                    $newDownPayment = $this->reduceInstallmentPaidAmounts($id, $cashRefunded, $inst, $paidInfo['has_down_row']);
                }

                // (2) إرجاع الكميات المرتجعة للمخزن + تسجيل sale_returns
                $this->restoreReturnedItemsToInventory($returnedLines, $invoiceData['has_inventory_items'], $inst, $id, $returnedNetTotal, $returnedGrossTotal, $cashRefunded > 0.001 ? $accId : null, true, $returnedItems, $unmatchedItems);

                // (3) تحديث قيد الخصم إن وُجد متطابقاً في نافذة الإنشاء
                if ($returnedDiscountShare > 0.001) {
                    $discFt = DB::table('financial_transactions')
                        ->where('type', 'discount')
                        ->where('amount', $oldDiscount)
                        ->where('status', 'active')
                        ->whereBetween('created_at', [$windowStart, $windowEnd])
                        ->orderByDesc('id')
                        ->first();
                    if ($discFt) {
                        if ($newDiscount <= 0) {
                            DB::table('financial_transactions')->where('id', $discFt->id)->delete();
                        } else {
                            DB::table('financial_transactions')->where('id', $discFt->id)->update([
                                'amount' => $newDiscount,
                            ]);
                        }
                    }
                }

                // (4) تحديث سطر الفاتورة (installments) بالكميات والقيم المتبقية
                $newPurchaseCost = max(0.0, round((float) ($inst->purchase_cost ?? 0) - $returnedPurchaseTotal, 2));
                $newProfit       = round((float) ($inst->profit ?? 0) - ($returnedNetTotal - $returnedPurchaseTotal), 2);

                $updateData = [
                    'cash_price'           => $newCashPrice,
                    'discount'             => $newDiscount,
                    'down_payment'         => $newDownPayment,
                    'remaining_after_down' => $newRemaining,
                    'total_after_interest' => $newTotalAfterInterest,
                    'monthly_installment'  => $newRemaining,
                    'remaining_balance'    => $newRemaining,
                    'purchase_cost'        => $newPurchaseCost,
                    'profit'               => $newProfit,
                    'status'               => $newRemaining <= 0.001 ? 'paid' : 'active',
                    'updated_at'           => now(),
                ];

                if ($invoiceData['has_inventory_items']) {
                    $newInvJson = [];
                    $newNamesList = [];
                    foreach ($remainingItems as $rem) {
                        $remIt = $rem['item'];
                        $remQty = $rem['left_qty'];
                        $newInvJson[] = [
                            'sale_id'       => $remIt['sale_id'],
                            'qty'           => $remQty,
                            'product_name'  => $remIt['product_name'],
                            'selling_price' => $remIt['unit_gross_price'],
                        ];
                        $qFmt = fmod($remQty, 1.0) === 0.0 ? (int) $remQty : $remQty;
                        $newNamesList[] = "{$remIt['product_name']} (x{$qFmt})";
                    }
                    $updateData['inventory_items'] = json_encode($newInvJson, JSON_UNESCAPED_UNICODE);
                    if (str_starts_with((string) ($inst->product_name ?? ''), 'فاتورة مبيعات:')) {
                        $updateData['product_name'] = 'فاتورة مبيعات: ' . Str::limit(implode(' + ', $newNamesList), 200);
                    }
                } else {
                    $remQty = $remainingItems[0]['left_qty'] ?? 1;
                    $updateData['quantity'] = $remQty;
                }

                DB::table('installments')->where('id', $id)->update($updateData);

                AuditService::warning(
                    'return', 'sales',
                    "مرتجع جزئي بيعة #{$id} — العميل: {$inst->customer_name} | صافي المرتجع: " . fmtMoney($returnedNetTotal) . " ج | خصم دين: " . fmtMoney($debtDeducted) . " ج | رد كاش: " . fmtMoney($cashRefunded) . " ج",
                    (array) $inst,
                    [
                        'returned_items'    => $returnedDesc,
                        'returned_net'      => $returnedNetTotal,
                        'debt_deducted'     => $debtDeducted,
                        'refund_amount'     => $cashRefunded,
                        'refund_account_id' => $cashRefunded > 0.001 ? $accId : null,
                    ],
                    'installments', $id
                );
            }
        });

        $parts = [];
        if ($debtDeducted > 0.001) {
            $parts[] = 'تم خصم ' . fmtMoney($debtDeducted) . ' ج من مديونية العميل';
        }
        if ($cashRefunded > 0.001) {
            $parts[] = 'تم رد ' . fmtMoney($cashRefunded) . ' ج كاش من الخزنة';
        }
        $prefix = $isFullReturn ? '✅ تم تنفيذ المرتجع الكامل' : '✅ تم تنفيذ المرتجع الجزئي بنجاح';
        $msg = $prefix . (!empty($parts) ? ' (' . implode(' + ', $parts) . ')' : '') . '.';
        if (!empty($returnedItems)) {
            $msg .= ' البضاعة رجعت للمخزن: ' . implode(' + ', $returnedItems);
        }
        if ($unmatchedItems) {
            $msg .= ' ⚠️ في أصناف ما اتربطتش بالمخزن (بيعة قديمة) — راجع وأضف الكميات يدوياً.';
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    private function restoreReturnedItemsToInventory(
        array $returnedLines,
        bool $hasInventoryItems,
        object $inst,
        int $instId,
        float $returnedNetTotal,
        float $returnedGrossTotal,
        ?int $refundAccountId,
        bool $isPartial,
        array &$returnedItems,
        bool &$unmatchedItems
    ): void {
        $count = count($returnedLines);
        $allocatedNet = 0.0;
        $notePrefix = $isPartial ? 'مرتجع جزئي' : 'مرتجع';

        foreach ($returnedLines as $i => $rl) {
            $it     = $rl['item'];
            $retQty = $rl['ret_qty'];
            $saleId = (int) $it['sale_id'];

            if ($i === $count - 1) {
                $itemNetRefund = max(0.0, round($returnedNetTotal - $allocatedNet, 2));
            } else {
                $itemNetRefund = $returnedGrossTotal > 0
                    ? round($returnedNetTotal * ($rl['line_gross'] / $returnedGrossTotal), 2)
                    : 0.0;
                $allocatedNet = round($allocatedNet + $itemNetRefund, 2);
            }

            $qFmt = fmod($retQty, 1.0) === 0.0 ? (int) $retQty : $retQty;
            $recordedSaleId = $saleId;

            if ($hasInventoryItems && $saleId > 0) {
                $exists = DB::table('sales')->where('id', $saleId)->lockForUpdate()->first();
                if (!$exists) {
                    $unmatchedItems = true;
                } else {
                    DB::table('sales')->where('id', $saleId)->update([
                        'remaining_quantity' => round((float) $exists->remaining_quantity + $retQty, 4),
                        'inventory_status'   => 'to_inventory',
                        'updated_at'         => now(),
                    ]);
                    DB::table('inventory_movements')->insert([
                        'sale_id'    => $saleId,
                        'type'       => 'customer_return',
                        'quantity'   => $retQty,
                        'notes'      => Str::limit("{$notePrefix} من العميل: {$inst->customer_name} — بيعة #{$instId}", 240),
                        'created_at' => now(),
                    ]);
                    $returnedItems[] = "{$qFmt}× {$it['product_name']}";
                }
            } else {
                // بيعة مباشر / خدمة / بيعة قديمة من المخزن من غير ربط — ننشئ batch جديد بالكمية المرتجعة
                $newSaleId = DB::table('sales')->insertGetId([
                    'store_id'           => 1,
                    'product_name'       => $it['product_name'],
                    'category'           => 'مرتجعات عملاء',
                    'supplier_name'      => 'مرتجع من العميل: ' . $inst->customer_name,
                    'purchase_price'     => $it['unit_purchase_price'],
                    'selling_price'      => $it['unit_gross_price'],
                    'quantity'           => $retQty,
                    'remaining_quantity' => $retQty,
                    'inventory_status'   => 'to_inventory',
                    'is_return'          => 1,
                    'purchase_date'      => now()->toDateString(),
                    'created_at'         => now(),
                ]);
                DB::table('inventory_movements')->insert([
                    'sale_id'    => $newSaleId,
                    'type'       => 'customer_return',
                    'quantity'   => $retQty,
                    'notes'      => Str::limit("{$notePrefix} من العميل: {$inst->customer_name} — بيعة #{$instId}", 240),
                    'created_at' => now(),
                ]);
                $recordedSaleId = $newSaleId;
                $returnedItems[] = "{$qFmt}× {$it['product_name']}";
            }

            if (Schema::hasTable('sale_returns')) {
                DB::table('sale_returns')->insert([
                    'sale_id'           => $recordedSaleId,
                    'product_name'      => $it['product_name'],
                    'category'          => 'مرتجعات عملاء',
                    'quantity_returned' => $retQty,
                    'purchase_price'    => $it['unit_purchase_price'],
                    'return_price'      => $itemNetRefund,
                    'total_refunded'    => $itemNetRefund,
                    'loss_amount'       => 0,
                    'refund_account_id' => $refundAccountId,
                    'notes'             => "{$notePrefix} بيعة العميل: {$inst->customer_name} (فاتورة #{$instId})",
                    'created_at'        => now(),
                ]);
            }
        }
    }

    // ───────────────────────────────────────────────────────────────────────────
    // أدوات مساعدة
    // ───────────────────────────────────────────────────────────────────────────

    /**
     * يتحقق من باسورد الـ admin الحالي (المستخدم المسجّل دخوله) — لا يوجد رمز ثابت.
     */
    private function verifyAdminPin(Request $request): bool
    {
        $sessionUser = session('auth_user');
        if (!$sessionUser || ($sessionUser->role ?? '') !== 'admin') return false;

        $password = (string) $request->input('admin_pin', '');
        if ($password === '') return false;

        $dbUser = DB::table('users')->where('id', $sessionUser->id)->first();
        if (!$dbUser) return false;

        return Hash::check($password, $dbUser->password);
    }

    /**
     * التحقق مما إذا كان سجل الأقساط/المبيعات يتبع المقاولات.
     */
    private function isContractingInstallment($r): bool
    {
        if (!$r) return false;
        $saleType   = (string) ($r->sale_type ?? '');
        $category   = (string) ($r->category ?? '');
        $sourceType = (string) ($r->source_type ?? '');
        $prodName   = (string) ($r->product_name ?? '');

        return $saleType === 'contracting'
            || str_starts_with($sourceType, 'cont_')
            || str_contains($category, 'مقاول')
            || str_contains($prodName, 'مقاول')
            || str_contains($prodName, 'مشروع مقاولة')
            || str_contains($prodName, 'مشروع مقاولات');
    }

    /**
     * التحقق مما إذا كانت الحركة المالية (مصروف أو إيراد/تسوية) تتبع المقاولات.
     */
    private function isContractingFinancialTx($r, ?array $contDebtIds = null, ?array $contPaymentIds = null): bool
    {
        if (!$r) return false;
        $refType = (string) ($r->ref_type ?? '');
        $refId   = (int) ($r->ref_id ?? 0);
        $notes   = (string) ($r->notes ?? '');

        if (str_starts_with($refType, 'cont_')) {
            return true;
        }

        if (
            str_contains($notes, 'مقاول') ||
            str_contains($notes, 'مشروع ') ||
            str_contains($notes, 'مشروع:') ||
            str_contains($notes, 'فاتورة خامات') ||
            str_contains($notes, 'متبقي مصنعية') ||
            str_contains($notes, 'نثريات مقاولات')
        ) {
            return true;
        }

        if (in_array($refType, ['company_debt', 'company_debt_payment', 'company_debt_payment_discount', 'earned_discount'], true) && $refId > 0) {
            if ($contDebtIds !== null) {
                if (in_array($refId, $contDebtIds, true)) return true;
            } else {
                $debt = DB::table('company_debts')->where('id', $refId)->first();
                if ($debt) {
                    $src = (string) ($debt->source_type ?? '');
                    $rsn = (string) ($debt->reason ?? '');
                    if (
                        str_starts_with($src, 'cont_') ||
                        str_contains($rsn, 'مشروع:') ||
                        str_contains($rsn, 'فاتورة خامات') ||
                        str_contains($rsn, 'متبقي مصنعية') ||
                        str_contains($rsn, 'مقاول') ||
                        (Schema::hasTable('cont_directory') && DB::table('cont_directory')->where('name', $debt->creditor_name)->exists())
                    ) {
                        return true;
                    }
                }
            }
        }

        if ($refType === 'installment_payment' && $refId > 0) {
            if ($contPaymentIds !== null) {
                if (in_array($refId, $contPaymentIds, true)) return true;
            } else {
                $pay = DB::table('installment_payments')->where('id', $refId)->first();
                if ($pay) {
                    $inst = DB::table('installments')->where('id', $pay->installment_id)->first();
                    if ($this->isContractingInstallment($inst)) return true;
                }
            }
        }

        return false;
    }

    // ───────────────────────────────────────────────────────────────────────────
    // المصروفات (financial_transactions)
    // ───────────────────────────────────────────────────────────────────────────

    private function showExpense(int $id)
    {
        $row = DB::table('financial_transactions')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'المصروف غير موجود'], 404);
        if ($this->isContractingFinancialTx($row)) {
            return response()->json(['error' => '⛔ هذا المصروف يخص المقاولات ولا يمكن تعديله أو حذفه من سجل العمليات.'], 403);
        }

        $accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();

        $manualCategories = DB::table('expense_categories')->orderBy('name')->pluck('name')->toArray();
        $dynamicCategories = DB::table('financial_transactions')
            ->where('type', 'general_expense')
            ->where('notes', 'like', '[%')
            ->where('notes', 'like', '%]%')
            ->selectRaw('DISTINCT SUBSTRING(notes, 2, LOCATE(\']\', notes) - 2) as cat')
            ->pluck('cat')->filter()->all();

        $allCategories = array_values(array_unique(array_merge(
            ['رواتب وأجور', 'خصومات للعملاء', 'عمولات المحافظ'],
            $manualCategories,
            $dynamicCategories
        )));

        $category = 'مصروفات تشغيلية';
        $actualNote = $row->notes ?? '';
        if ($row->type === 'salary_expense') $category = 'رواتب وأجور';
        elseif ($row->type === 'discount')   $category = 'خصومات للعملاء';
        elseif (preg_match('/^\[(.*?)\]\s*(.*)/u', $actualNote, $m)) {
            $category = $m[1];
            $actualNote = $m[2];
        }

        return response()->json([
            'row'           => $row,
            'category'      => $category,
            'actual_note'   => $actualNote,
            'accounts'      => $accounts,
            'categories'    => $allCategories,
        ]);
    }

    private function updateExpense(Request $request, int $id)
    {
        $request->validate([
            'category'   => 'required|string|max:100',
            'notes'      => 'nullable|string|max:500',
            'amount'     => 'required|numeric|min:0.01',
            'account_id' => 'required|integer',
        ]);

        DB::transaction(function () use ($request, $id) {
            $ft = DB::table('financial_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$ft) throw new \Exception('المصروف غير موجود.');
            if ($this->isContractingFinancialTx($ft)) {
                throw new \Exception('⛔ هذا المصروف يخص المقاولات ولا يمكن تعديله أو حذفه من سجل العمليات.');
            }
            if ($ft->status !== 'active') throw new \Exception('المصروف ملغي بالفعل — مينفعش يتعدل.');
            if (!in_array($ft->type, ['general_expense', 'salary_expense', 'expense'])) {
                throw new \Exception('نوع الحركة غير مدعوم للتعديل من هنا.');
            }

            $oldAmount  = (float) $ft->amount;
            $oldAccount = $ft->from_account_id;
            $newAmount  = (float) $request->amount;
            $newAccount = (int) $request->account_id;
            $newCategory= trim($request->category);
            $newNotes   = trim((string) $request->notes);

            // 1) إرجاع المبلغ القديم للخزنة القديمة
            if ($oldAccount) {
                DB::table('accounts')->where('id', $oldAccount)->increment('balance', $oldAmount);
            }

            // 2) خصم المبلغ الجديد من الخزنة الجديدة
            $acc = DB::table('accounts')->where('id', $newAccount)->lockForUpdate()->first();
            if (!$acc) throw new \Exception('الخزنة الجديدة غير موجودة.');
            if ($acc->balance < $newAmount) {
                throw new \Exception("رصيد الخزنة ({$acc->account_name}) لا يكفي بعد الخصم. الرصيد الحالي بعد الإرجاع: " . number_format($acc->balance, 2) . " ج");
            }
            DB::table('accounts')->where('id', $newAccount)->decrement('balance', $newAmount);

            // 3) تحديد الـ type والـ notes الجديدة بناءً على التصنيف
            $newType = 'general_expense';
            $finalNotes = "[{$newCategory}] {$newNotes}";
            if ($newCategory === 'رواتب وأجور') {
                $newType = 'salary_expense';
                $finalNotes = $newNotes ?: 'راتب';
            } elseif ($newCategory === 'خصومات للعملاء') {
                $newType = 'discount';
                $finalNotes = $newNotes ?: 'خصم للعميل';
            }

            DB::table('financial_transactions')->where('id', $id)->update([
                'type'            => $newType,
                'amount'          => $newAmount,
                'from_account_id' => $newAccount,
                'notes'           => $finalNotes,
                'updated_at'      => now(),
            ]);
        });

        return redirect()->route('operations.index')->with('success', '✅ تم تعديل المصروف بنجاح.');
    }

    private function destroyExpense(int $id)
    {
        DB::transaction(function () use ($id) {
            $ft = DB::table('financial_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$ft) throw new \Exception('المصروف غير موجود.');
            if ($this->isContractingFinancialTx($ft)) {
                throw new \Exception('⛔ هذا المصروف يخص المقاولات ولا يمكن تعديله أو حذفه من سجل العمليات.');
            }
            if ($ft->status !== 'active') throw new \Exception('المصروف ملغي بالفعل.');
            if (!in_array($ft->type, ['general_expense', 'salary_expense', 'expense', 'discount'])) {
                throw new \Exception('نوع الحركة غير مدعوم للحذف من هنا.');
            }

            // إرجاع المبلغ للخزنة
            if ($ft->from_account_id) {
                DB::table('accounts')->where('id', $ft->from_account_id)->increment('balance', $ft->amount);
            }

            // عكس سداد دين الشركة إذا كانت العملية مرتبطة بسداد دين
            if (($ft->ref_type ?? '') === 'company_debt_payment' && $ft->ref_id) {
                $debt = DB::table('company_debts')->where('id', $ft->ref_id)->lockForUpdate()->first();
                if ($debt) {
                    // الخصم المكتسب المربوط بنفس سطر السداد يرجع كمان للدين
                    $discLines = DB::table('financial_transactions')
                        ->where('ref_type', 'company_debt_payment_discount')
                        ->where('ref_id', $id)
                        ->where('status', 'active')
                        ->get();
                    $discTotal = (float) $discLines->sum('amount');
                    $restore   = (float) $ft->amount + $discTotal;   // كاش + خصم = الدين كامل يرجع

                    DB::table('company_debts')->where('id', $ft->ref_id)->update([
                        'paid_amount'       => max(0, $debt->paid_amount - $restore),
                        'remaining_balance' => $debt->remaining_balance + $restore,
                        'updated_at'        => now(),
                    ]);

                    // احذف سطور الخصم المكتسب المربوطة (تختفي من تقرير الخصومات المكتسبة)
                    if ($discLines->isNotEmpty()) {
                        DB::table('financial_transactions')
                            ->where('ref_type', 'company_debt_payment_discount')
                            ->where('ref_id', $id)
                            ->delete();
                    }
                }
            }

            if (($ft->ref_type ?? '') === 'ac_expense' && $ft->ref_id) {
                DB::table('ac_expenses')->where('id', $ft->ref_id)->delete();
            }

            if (($ft->ref_type ?? '') === 'jamia_installment' && $ft->ref_id) {
                DB::table('jamia_installments')->where('id', $ft->ref_id)->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                    'updated_at'      => now(),
                ]);
            }

            if (($ft->ref_type ?? '') === 'jamia_payout_out' && $ft->ref_id) {
                \App\Http\Controllers\JamiaController::recalculateSharePayoutFromTransactions((int) $ft->ref_id, (int) $id);
                \App\Http\Controllers\JamiaController::syncJamiaCompanyDebtByShareId((int) $ft->ref_id);
            }

            // عكس أثر بيع أو إهلاك الأصل الثابت إن وجد
            \App\Http\Controllers\AssetController::reverseAssetTransactionIfApplicable($ft, 'إلغاء يدوي من شاشة سجل العمليات');

            // حذف نهائي للسطر
            DB::table('financial_transactions')->where('id', $id)->delete();
        });

        return redirect()->route('operations.index')->with('success', '✅ تم حذف المصروف وإرجاع المبلغ للخزنة.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // إلغاء حركة مالية (إيداع/تسوية/تحويل/تحصيل قسط) من سجل العمليات
    // ───────────────────────────────────────────────────────────────────────────
    private function destroyFinancial(int $id)
    {
        DB::transaction(function () use ($id) {
            $tx = DB::table('financial_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$tx) throw new \Exception('الحركة غير موجودة.');
            if ($this->isContractingFinancialTx($tx)) {
                throw new \Exception('⛔ هذه الحركة المالية تخص المقاولات ولا يمكن تعديلها أو حذفها من سجل العمليات.');
            }
            if ($tx->status !== 'active') throw new \Exception('الحركة ملغاة بالفعل.');
            if (!in_array($tx->type, ['income', 'settlement', 'transfer'])) {
                throw new \Exception('نوع الحركة غير مدعوم للحذف من هنا.');
            }

            $refType = $tx->ref_type ?? null;

            // فحص الـ ref المرتبط قبل العكس
            if ($refType === 'company_debt') {
                $debt = DB::table('company_debts')->where('id', $tx->ref_id)->first();
                if ($debt && (float) $debt->paid_amount > 0) {
                    throw new \Exception('في سداد على الدين المرتبط بهذه التسوية — الغِ السداد أولاً.');
                }
            }

            // 💰 عكس الأثر على الخزن
            if ($tx->type === 'transfer') {
                if ($tx->to_account_id) {
                    $accTo = DB::table('accounts')->where('id', $tx->to_account_id)->lockForUpdate()->first();
                    if ($accTo && $accTo->balance < $tx->amount) {
                        throw new \Exception("رصيد الخزنة ({$accTo->account_name}) أقل من مبلغ التحويل المطلوب عكسه.");
                    }
                    DB::table('accounts')->where('id', $tx->to_account_id)->decrement('balance', $tx->amount);
                }
                if ($tx->from_account_id) {
                    DB::table('accounts')->where('id', $tx->from_account_id)->increment('balance', $tx->amount);
                }
            } else {
                // إيراد/تسوية → اخصم من الخزنة المستلمة
                if ($tx->to_account_id) {
                    $acc = DB::table('accounts')->where('id', $tx->to_account_id)->lockForUpdate()->first();
                    if ($acc && $acc->balance < $tx->amount) {
                        throw new \Exception("رصيد الخزنة ({$acc->account_name}) أقل من مبلغ العملية المطلوب عكسها. أودِع المبلغ أولاً.");
                    }
                    DB::table('accounts')->where('id', $tx->to_account_id)->decrement('balance', $tx->amount);
                }
            }

            // عكس الـ ref المرتبط
            if ($refType === 'installment_payment') {
                $payment = DB::table('installment_payments')->where('id', $tx->ref_id)->lockForUpdate()->first();
                if ($payment) {
                    \App\Services\InstallmentFinanceService::restoreInstallmentAfterPaymentReversal($payment->installment_id, $payment);
                    DB::table('installment_payments')->where('id', $tx->ref_id)->delete();
                }
            } elseif ($refType === 'company_debt') {
                DB::table('financial_transactions')
                    ->where('ref_type', 'earned_discount')
                    ->where('ref_id', $tx->ref_id)
                    ->delete();
                DB::table('company_debts')->where('id', $tx->ref_id)->delete();
            } elseif ($refType === 'jamia_collection') {
                DB::table('jamia_installments')->where('id', $tx->ref_id)->update([
                    'is_paid'         => false,
                    'paid_at'         => null,
                    'paid_account_id' => null,
                    'tx_id'           => null,
                    'updated_at'      => now(),
                ]);
            } elseif ($refType === 'jamia_share') {
                \App\Http\Controllers\JamiaController::recalculateSharePayoutFromTransactions((int) $tx->ref_id, (int) $id);
                \App\Http\Controllers\JamiaController::syncJamiaCompanyDebtByShareId((int) $tx->ref_id);
            } elseif ($refType === 'jamia_share_claim') {
                DB::table('jamia_shares')->where('id', $tx->ref_id)->update([
                    'is_claim_recovered'   => false,
                    'recovered_at'         => null,
                    'recovered_account_id' => null,
                    'recovered_tx_id'      => null,
                    'recovered_amount'     => 0,
                    'updated_at'           => now(),
                ]);
            }

            // عكس أثر بيع الأصل الثابت واستعادة قيمته الدفترية وحالته النشطة إن وجد
            \App\Http\Controllers\AssetController::reverseAssetTransactionIfApplicable($tx, 'إلغاء يدوي من شاشة سجل العمليات');

            // علّم الحركة كملغاة (تفضل في السجل)
            DB::table('financial_transactions')->where('id', $id)->update([
                'status'        => 'cancelled',
                'cancelled_at'  => now(),
                'cancel_reason' => 'إلغاء يدوي من شاشة سجل العمليات',
                'updated_at'    => now(),
            ]);
        });

        return redirect()->route('operations.index')->with('success', '✅ تم إلغاء الحركة المالية وعكس الأثر بنجاح.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // حذف عملية بنزينة بالكامل
    // ───────────────────────────────────────────────────────────────────────────

    private function destroyFuel(int $id)
    {
        DB::transaction(function () use ($id) {
            $tx = DB::table('fuel_transactions')->where('id', $id)->lockForUpdate()->first();
            if (!$tx) throw new \Exception('عملية البنزينة غير موجودة.');
            if (!empty($tx->superseded_by)) {
                throw new \Exception("هذه العملية اتعدلت قبل كده — احذف العملية المعدّلة #{$tx->superseded_by} بدلاً منها.");
            }

            // امنع لو فيها مدفوعات/سداد
            $hasPaidDebts = DB::table('company_debts')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)
                ->where('paid_amount', '>', 0)->exists();
            if ($hasPaidDebts) {
                throw new \Exception('لا يمكن الحذف: في مستحقات مرتبطة بهذه العملية تم سدادها جزئياً أو كلياً. الغِ السداد أولاً.');
            }

            $oldInst = DB::table('installments')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)->first();
            if ($oldInst) {
                $hasPayments = DB::table('installment_payments')->where('installment_id', $oldInst->id)->exists();
                if ($hasPayments) {
                    throw new \Exception('لا يمكن الحذف: شركة النقل سددت جزء من المستحقات. الغِ السداد أولاً.');
                }
            }

            // 1) إرجاع العهدة للخزنة (لو كان عندنا financial_transaction expense مربوطة)
            $oldExpense = DB::table('financial_transactions')
                ->where('ref_type', 'fuel_transaction')->where('ref_id', $id)
                ->where('type', 'expense')->first();
            if ($oldExpense) {
                if ($oldExpense->from_account_id) {
                    DB::table('accounts')->where('id', $oldExpense->from_account_id)->increment('balance', $oldExpense->amount);
                }
                DB::table('financial_transactions')->where('id', $oldExpense->id)->delete();
            }

            // 2) حذف الديون المرتبطة (وقود + استقطاعات)
            DB::table('company_debts')
                ->where('source_type', 'fuel_transaction')->where('source_id', $id)
                ->delete();

            // 3) حذف installment شركة النقل
            if ($oldInst) {
                DB::table('installments')->where('id', $oldInst->id)->delete();
            }

            // 4) حذف الـ fuel_transaction نفسه وإعادة حساب مسافات وكفاءة الرحلات
            DB::table('fuel_transactions')->where('id', $id)->delete();
            (new GasStationController())->syncAndRecalculateFuelTrips();

            // Audit
            $user = session('auth_user');
            DB::table('operation_revisions')->insert([
                'source_type'    => 'fuel_transaction',
                'source_id'      => $id,
                'action'         => 'delete',
                'snapshot_before'=> json_encode((array)$tx, JSON_UNESCAPED_UNICODE),
                'edited_by'      => $user->id ?? null,
                'edited_by_name' => $user->name ?? null,
                'notes'          => 'حذف عملية بنزينة كاملة من سجل العمليات',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            AuditService::warning(
                'delete', 'gas_station',
                "تم حذف عملية بنزينة #{$id} بالكامل (مع كل القيود المرتبطة)",
                (array) $tx,
                null,
                'fuel_transaction', $id
            );
        });

        return redirect()->route('operations.index')->with('success', '✅ تم حذف عملية البنزينة بالكامل مع كل القيود المرتبطة.');
    }

    private function resolvePeriod(string $period, ?string $from, ?string $to): array
    {
        switch ($period) {
            case 'today':     return [now()->startOfDay(), now()->endOfDay()];
            case 'yesterday': return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];
            case 'week':      return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
            case 'month':     return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
            case 'custom':
                if ($from && $to) {
                    return [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()];
                }
                return [null, null];
            case 'all':
            default:
                return [null, null];
        }
    }
}
