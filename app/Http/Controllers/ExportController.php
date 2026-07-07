<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends SystemController
{
    /**
     * تصدير الخزنة والأرباح كـ Excel (.xls).
     * يحتوي على: ملخص رأس المال، أرصدة الخزن، تفصيل الأرباح، الاستقطاعات،
     * مستحقات وديون البنزينة.
     */
    public function treasury(Request $request)
    {
        // ─── جلب نفس البيانات اللي بتظهر في شاشة الخزنة ───
        $projectIds = [12, 30];
        $all_liquidity_accounts = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get();
        $liquidity_accounts     = $all_liquidity_accounts->whereNotIn('id', $projectIds);
        $projects               = DB::table('accounts')->where('category', 'project_sector')->orWhereIn('id', $projectIds)->get();

        $summary = \App\Services\InstallmentFinanceService::treasurySummary();

        $gas_receivables = (float) DB::table('installments')
            ->where('category', 'بنزينة')->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        $gas_payables_stations = (float) DB::table('company_debts')
            ->where('category', 'وقود')->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        $gas_payables_deductions = (float) DB::table('company_debts')
            ->where('category', 'استقطاعات')->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        // الفلتر الزمني للأرباح
        $pf     = $request->input('profit_filter', 'all');
        $pfFrom = $request->input('profit_from_date');
        $pfTo   = $request->input('profit_to_date');
        $rangeLabel = 'كل الفترات';
        $startDate = null;
        $endDate   = null;

        if ($pf !== 'all') {
            switch ($pf) {
                case 'today':     $startDate = now()->startOfDay(); $endDate = now()->endOfDay(); $rangeLabel = 'اليوم'; break;
                case 'yesterday': $startDate = now()->subDay()->startOfDay(); $endDate = now()->subDay()->endOfDay(); $rangeLabel = 'أمس'; break;
                case 'week':      $startDate = now()->startOfWeek(Carbon::SATURDAY); $endDate = now()->endOfWeek(Carbon::FRIDAY); $rangeLabel = 'هذا الأسبوع'; break;
                case 'month':     $startDate = now()->startOfMonth(); $endDate = now()->endOfMonth(); $rangeLabel = 'هذا الشهر'; break;
                case 'custom':
                    if ($pfFrom && $pfTo) {
                        $startDate = Carbon::parse($pfFrom)->startOfDay();
                        $endDate   = Carbon::parse($pfTo)->endOfDay();
                        $rangeLabel = "من {$pfFrom} إلى {$pfTo}";
                    }
                    break;
            }
        }

        // حساب الأرباح بناءً على الفلتر
        if ($startDate && $endDate) {
            $applyDate = fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]);

            $profit_installments = (float) $applyDate(DB::table('installments'))
                ->where('installment_months', '>', 0)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) { $q->where('category', '!=', 'بنزينة')->orWhereNull('category'); })
                ->selectRaw('COALESCE(SUM(total_after_interest - cash_price), 0) as total')
                ->value('total');

            $profit_inventory = (float) $applyDate(DB::table('installments'))
                ->where('installment_months', 0)
                ->where(function ($q) { $q->where('category', 'مبيعات مخزن')->orWhere('sale_type', 'inventory'); })
                ->where('status', '!=', 'cancelled')->sum('profit');

            $profit_direct = (float) $applyDate(DB::table('installments'))
                ->where('installment_months', 0)->where('category', 'مبيعات مباشرة')
                ->where('status', '!=', 'cancelled')->sum('profit');

            $profit_services = (float) $applyDate(DB::table('installments'))
                ->where('category', 'خدمات')->where('status', '!=', 'cancelled')->sum('profit');

            $profit_gas = (float) $applyDate(DB::table('fuel_transactions')->whereNull('superseded_by'))
                ->sum('ahmed_profit');

            $fb = \App\Services\InstallmentFinanceService::financialBreakdown($startDate, $endDate);
            $profit_asset_sales = $fb['profitAssetSales'];
            $expenses_general   = $fb['expensesGeneral'];
            $expenses_salaries  = $fb['expensesSalaries'];
            $total_commissions  = $fb['totalCommissions'];
            $losses_depreciation= $fb['lossesDepreciation'];
            $losses_returns     = $fb['lossesReturns'];
            $losses_discounts   = $fb['lossesDiscounts'];
            $losses_bad_debts   = $fb['lossesBadDebts'];
            $losses_asset_sales = $fb['lossesAssetSales'];
            $losses_inventory_shortage = $fb['lossesInventoryShortage'];
        } else {
            $profit_installments = $summary['profitInstallments'];
            $profit_inventory    = $summary['profitInventory'];
            $profit_direct       = $summary['profitDirectProducts'];
            $profit_services     = $summary['profitServices'];
            $profit_gas          = $summary['profitGas'];
            $profit_asset_sales  = $summary['profitAssetSales'];
            $expenses_general    = $summary['expensesGeneral'];
            $expenses_salaries   = $summary['expensesSalaries'];
            $total_commissions   = $summary['totalCommissions'];
            $losses_depreciation = $summary['lossesDepreciation'];
            $losses_returns      = $summary['lossesReturns'];
            $losses_discounts    = $summary['lossesDiscounts'];
            $losses_bad_debts    = $summary['lossesBadDebts'];
            $losses_asset_sales  = $summary['lossesAssetSales'];
            $losses_inventory_shortage = $summary['lossesInventoryShortage'] ?? 0;
        }

        $total_revenue = $profit_installments + $profit_inventory + $profit_direct
                       + $profit_services + $profit_gas + $profit_asset_sales;
        $total_deductions = $expenses_general + $expenses_salaries + $total_commissions
                          + $losses_depreciation + $losses_returns + $losses_discounts
                          + $losses_bad_debts + $losses_asset_sales + $losses_inventory_shortage;
        $net_profit = $total_revenue - $total_deductions;

        $profit_breakdown = [
            ['label' => 'أرباح التقسيط (الآجل)',         'value' => $profit_installments],
            ['label' => 'أرباح مبيعات المخزن',           'value' => $profit_inventory],
            ['label' => 'أرباح البيع المباشر (منتجات)',  'value' => $profit_direct],
            ['label' => 'أرباح الخدمات (صيانة/تركيب)',   'value' => $profit_services],
            ['label' => 'أرباح محطة الوقود',             'value' => $profit_gas],
            ['label' => 'أرباح بيع الأصول',              'value' => $profit_asset_sales],
        ];

        $deductions_breakdown = [
            ['label' => 'مصاريف تشغيلية وعامة',  'value' => $expenses_general],
            ['label' => 'رواتب الموظفين',         'value' => $expenses_salaries],
            ['label' => 'عمولات المحافظ',         'value' => $total_commissions],
            ['label' => 'إهلاكات الأصول',         'value' => $losses_depreciation],
            ['label' => 'خسائر المرتجعات',        'value' => $losses_returns],
            ['label' => 'خصومات للعملاء',         'value' => $losses_discounts],
            ['label' => 'إعدام ديون وخسائر',      'value' => $losses_bad_debts],
            ['label' => 'خسائر بيع أصول',         'value' => $losses_asset_sales],
            ['label' => 'خسائر وعجز جرد المخزن',  'value' => $losses_inventory_shortage],
        ];

        $html = $this->renderTreasuryXls(
            $liquidity_accounts, $projects, $summary,
            $profit_breakdown, $deductions_breakdown,
            $gas_receivables, $gas_payables_stations, $gas_payables_deductions,
            $total_revenue, $total_deductions, $net_profit, $rangeLabel
        );

        return $this->xlsResponse($html, 'الخزنة_والأرباح_' . now()->format('Y-m-d_His'));
    }

    /**
     * تصدير/طباعة شاشة التقارير المتقدمة — بيطبع بس التاب اللي المستخدم فاتحه دلوقتي،
     * وبيستخدم نفس دوال ReportController وبنفس الفلتر بالظبط، عشان الأرقام في الطباعة
     * تطابق اللي على الشاشة تماماً (بدل ما يبقى فيه حساب منفصل بيختلف مع الوقت).
     */
    public function reports(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'month');
        $customFrom = $request->input('custom_from');
        $customTo   = $request->input('custom_to');
        $tab        = $request->input('tab', 'inventory');

        [$range, $rangeLabel] = ReportController::resolveDateRange($dateFilter, $customFrom, $customTo);
        [$startDate, $endDate] = $range;

        $rc = new ReportController();

        $html = match ($tab) {
            'services' => $this->renderServicesXls($rc->servicesReport($range), $rangeLabel, $startDate, $endDate),
            'inst'     => $this->renderInstXls($rc->installmentsReport($range), $rangeLabel, $startDate, $endDate),
            'gas'      => $this->renderGasXls($rc->gasReport($range), $rangeLabel, $startDate, $endDate),
            'fin'      => $this->renderFinXls($rc->financialReport($range), $rangeLabel, $startDate, $endDate),
            default    => $this->renderInventoryXls($rc->inventoryReport($range), $rangeLabel, $startDate, $endDate),
        };

        return $this->xlsResponse($html, 'تقرير_' . $tab . '_' . now()->format('Y-m-d_His'));
    }

    // ───────────────────────────────────────────────────────────────────
    // Helpers — تصدير PDF عبر تشغيل dialog الطباعة في المتصفح
    // (المستخدم يختار "حفظ كـ PDF" من dialog الطباعة)
    // ───────────────────────────────────────────────────────────────────

    private function xlsResponse(string $html, string $filename)
    {
        // الـ filename بيتسم بيه title الصفحة عشان لو حفظ PDF يبقى الاسم منطقي
        $safeTitle = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
        $page = '<!DOCTYPE html><html dir="rtl" lang="ar"><head>'
            . '<meta charset="UTF-8"><title>' . $safeTitle . '</title>'
            . '<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">'
            . '</head><body>' . $html
            . '<script>document.title=' . json_encode($filename) . ';'
            . 'window.addEventListener("load",function(){setTimeout(function(){window.print();},250);});'
            . '</script></body></html>';

        return response($page, 200, [
            'Content-Type'  => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function xlsHeader(string $title): string
    {
        $date = now()->format('Y-m-d h:i A');
        return '<style>'
            . '@page{size:A4;margin:10mm 9mm;}'
            . '*{box-sizing:border-box;}'
            . 'body{font-family:"Cairo","Calibri",Arial,sans-serif;direction:rtl;margin:0;padding:12px;color:#0f172a;background:#fff;font-size:9.5pt;-webkit-print-color-adjust:exact;print-color-adjust:exact;}'
            // 💡 تقسيم الصفحات: الجدول نفسه يُسمح له يكمل على صفحة تانية (طويل)، لكن أي صف
            // أو كارت أو قسم بمفرده ممنوع يتقطع نص في نص — يتنقل بالكامل للصفحة الجاية بدل ما يتقص.
            . 'table{border-collapse:collapse;width:100%;margin-bottom:12px;page-break-inside:auto;}'
            . 'thead{display:table-header-group;}' // يكرر عنوان الأعمدة لو الجدول كمّل صفحة جديدة
            . 'tr{page-break-inside:avoid;break-inside:avoid;page-break-after:auto;}'
            . 'th,td{border:1px solid #e2e8f0;padding:6px 8px;text-align:center;font-size:9.5pt;}'
            . 'th{background:#0f172a;color:#fff;font-weight:700;font-size:9pt;letter-spacing:.2px;}'
            . 'td:first-child,th:first-child{text-align:right;}'
            . 'tbody tr:nth-child(even) td{background:#fafbfd;}'
            . '.section-title{background:#f8fafc;color:#0f172a;font-size:11.5pt;font-weight:900;padding:7px 12px;margin-top:12px;margin-bottom:7px;border-right:4px solid #0f172a;border-radius:4px;page-break-after:avoid;break-after:avoid;page-break-inside:avoid;break-inside:avoid;}'
            . '.section-title.final{background:#0f172a;color:#fff;border-right-color:#fbbf24;}'
            . '.total-row{background:#f1f5f9;font-weight:800;}'
            . '.neg{color:#dc2626;font-weight:800;}'
            . '.pos{color:#059669;font-weight:800;}'
            . '.doc-header{display:flex;justify-content:space-between;align-items:flex-end;padding-bottom:10px;margin-bottom:12px;border-bottom:3px solid #0f172a;page-break-inside:avoid;break-inside:avoid;}'
            . '.doc-header .brand h1{margin:0;font-size:20px;font-weight:900;color:#0f172a;letter-spacing:-.5px;}'
            . '.doc-header .brand p{margin:3px 0 0;color:#64748b;font-size:10px;font-weight:600;}'
            . '.doc-header .meta{text-align:left;font-size:10px;}'
            . '.doc-header .meta .doc-title{display:inline-block;background:#0f172a;color:#fff;padding:5px 14px;border-radius:6px;font-weight:800;font-size:11px;margin-bottom:5px;}'
            . '.doc-header .meta .doc-date{color:#64748b;font-weight:700;}'
            . '.subtitle{font-size:9.5pt;color:#64748b;font-weight:700;margin:-6px 0 12px;}'
            . '.print-bar{background:#0f172a;color:#fff;padding:9px 16px;border-radius:8px;text-align:center;margin-bottom:16px;font-weight:700;font-size:11pt;}'
            . '.print-bar button{background:#2563eb;color:#fff;border:0;padding:6px 16px;border-radius:6px;font-weight:800;cursor:pointer;margin:0 4px;font-family:inherit;}'
            . '.print-bar button.gray{background:#475569;}'
            . '@media print{.print-bar{display:none;}body{padding:0;}}'
            . '.report-footer{margin-top:18px;padding-top:10px;border-top:1px dashed #cbd5e1;page-break-inside:avoid;break-inside:avoid;}'
            . '.footer-sign{display:flex;justify-content:space-between;margin-bottom:14px;}'
            . '.footer-sign .sign-box{text-align:center;min-width:180px;}'
            . '.footer-sign .sign-box .line{border-top:1px solid #0f172a;margin-top:24px;padding-top:5px;font-weight:700;color:#0f172a;font-size:10pt;}'
            . '.footer-stamp{text-align:center;font-size:8.5pt;color:#94a3b8;font-weight:700;}'
            . '</style>'
            . '<div class="print-bar">معاينة قبل الطباعة — اختر "حفظ كـ PDF" من قائمة الطابعة لتصدير الملف'
            . '<button onclick="window.print()">طباعة / حفظ PDF</button>'
            . '<button class="gray" onclick="window.close()">إغلاق</button>'
            . '</div>'
            . '<div class="doc-header">'
            . '<div class="brand"><h1>شركة الضبع</h1><p>للتجارة وأنظمة التقسيط والمقاولات</p></div>'
            . '<div class="meta"><div class="doc-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div><div class="doc-date">' . $date . '</div></div>'
            . '</div>';
    }

    private function xlsFooter(): string
    {
        return '<div class="report-footer">'
            . '<div class="footer-sign">'
            . '<div class="sign-box"><div class="line">إعداد</div></div>'
            . '<div class="sign-box"><div class="line">اعتماد / المدير المالي</div></div>'
            . '</div>'
            . '<div class="footer-stamp">تم إنشاء هذا التقرير آلياً بواسطة نظام إدارة الموارد — شركة الضبع · ' . now()->format('Y/m/d h:i A') . '</div>'
            . '</div>';
    }

    private function renderTreasuryXls(
        $liquidity_accounts, $projects, $summary,
        $profit_breakdown, $deductions_breakdown,
        $gas_receivables, $gas_payables_stations, $gas_payables_deductions,
        $total_revenue, $total_deductions, $net_profit, $rangeLabel
    ): string {
        $h = $this->xlsHeader('تقرير الخزنة والأرباح');
        $h .= '<div class="subtitle">نطاق الأرباح: ' . htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8') . '</div>';

        $f = fn($n) => number_format((float) $n, 2);

        // ─── 1. ملخص رأس المال ───
        $h .= '<div class="section-title">ملخص رأس المال</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة (ج.م)</th></tr>';
        $h .= '<tr><td>السيولة في الخزن والبنوك</td><td>' . $f($summary['liquidity']) . '</td></tr>';
        $h .= '<tr><td>قيمة المشاريع</td><td>' . $f($summary['projectsValue']) . '</td></tr>';
        $h .= '<tr><td>قيمة المخزون</td><td>' . $f($summary['inventoryAssets']) . '</td></tr>';
        $h .= '<tr><td>الأصول الثابتة</td><td>' . $f($summary['fixedAssets']) . '</td></tr>';
        $h .= '<tr><td>المستحقات على العملاء</td><td>' . $f($summary['totalDebtsForUs']) . '</td></tr>';
        $h .= '<tr><td>مستحقات البنزينة (شركات النقل)</td><td>' . $f($summary['gasReceivables'] ?? $gas_receivables) . '</td></tr>';
        $h .= '<tr><td>الديون على الشركة</td><td class="neg">' . $f($summary['totalDebtsOnUs']) . '</td></tr>';
        $h .= '<tr class="total-row"><td>صافي رأس المال</td><td>' . $f($summary['capital']) . '</td></tr>';
        $h .= '</table>';

        // ─── 2. الخزن والبنوك ───
        $h .= '<div class="section-title">أرصدة الخزن والبنوك</div>';
        $h .= '<table><tr><th>الخزنة / البنك</th><th>الفئة</th><th>الرصيد (ج.م)</th></tr>';
        $totalAcc = 0;
        foreach ($liquidity_accounts as $a) {
            $cat = $a->category === 'bank_wallet' ? 'بنك / محفظة' : 'خزنة نقدية';
            $h .= '<tr><td>' . htmlspecialchars($a->account_name, ENT_QUOTES, 'UTF-8') . '</td><td>' . $cat . '</td><td>' . $f($a->balance) . '</td></tr>';
            $totalAcc += (float) $a->balance;
        }
        $h .= '<tr class="total-row"><td colspan="2">إجمالي السيولة</td><td>' . $f($totalAcc) . '</td></tr>';
        $h .= '</table>';

        if ($projects->count() > 0) {
            $h .= '<div class="section-title">المشاريع</div>';
            $h .= '<table><tr><th>المشروع</th><th>القيمة (ج.م)</th></tr>';
            $totalP = 0;
            foreach ($projects as $p) {
                $h .= '<tr><td>' . htmlspecialchars($p->account_name, ENT_QUOTES, 'UTF-8') . '</td><td>' . $f($p->balance) . '</td></tr>';
                $totalP += (float) $p->balance;
            }
            $h .= '<tr class="total-row"><td>الإجمالي</td><td>' . $f($totalP) . '</td></tr>';
            $h .= '</table>';
        }

        // ─── 3. الأرباح ───
        $h .= '<div class="section-title">تفصيل الأرباح</div>';
        $h .= '<table><tr><th>المصدر</th><th>الربح (ج.م)</th></tr>';
        foreach ($profit_breakdown as $row) {
            $h .= '<tr><td>' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '</td><td class="pos">' . $f($row['value']) . '</td></tr>';
        }
        $h .= '<tr class="total-row"><td>إجمالي الأرباح</td><td>' . $f($total_revenue) . '</td></tr>';
        $h .= '</table>';

        // ─── 4. الاستقطاعات ───
        $h .= '<div class="section-title">المصروفات والخسائر</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة (ج.م)</th></tr>';
        foreach ($deductions_breakdown as $row) {
            $h .= '<tr><td>' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '</td><td class="neg">' . $f($row['value']) . '</td></tr>';
        }
        $h .= '<tr class="total-row"><td>إجمالي المصروفات والخسائر</td><td>' . $f($total_deductions) . '</td></tr>';
        $h .= '</table>';

        // ─── 5. صافي الربح ───
        $h .= '<table><tr class="total-row" style="background:#fef3c7;font-size:14pt;"><td>صافي الربح للفترة</td><td>' . $f($net_profit) . '</td></tr></table>';

        // ─── 6. البنزينة ───
        $h .= '<div class="section-title">البنزينة — مستحقات وديون قائمة</div>';
        $h .= '<table><tr><th>البند</th><th>القيمة (ج.م)</th></tr>';
        $h .= '<tr><td>مستحقات على شركات النقل</td><td class="pos">' . $f($gas_receivables) . '</td></tr>';
        $h .= '<tr><td>ديون للمحطات</td><td class="neg">' . $f($gas_payables_stations) . '</td></tr>';
        $h .= '<tr><td>استقطاعات مستحقة</td><td class="neg">' . $f($gas_payables_deductions) . '</td></tr>';
        $h .= '</table>';

        $h .= $this->xlsFooter();
        return $h;
    }

    /**
     * 💡 CSS مشتركة بين كل تقارير الطباعة — أسلوب كشف حساب محاسبي احترافي:
     * بدون مربعات/كروت ملونة، جداول أرقام نظيفة بحدود رفيعة وتايبوجرافي واضح.
     */
    private function xlsCommonCss(): string
    {
        return '<style>'
            // شريط الفترة: سطر نظيف تحت الترويسة بدل الصندوق الملوّن
            . '.period-bar{display:flex;justify-content:space-between;align-items:center;border:1px solid #e2e8f0;border-right:4px solid #0f172a;background:#f8fafc;padding:8px 14px;margin-bottom:14px;font-size:10pt;page-break-inside:avoid;break-inside:avoid;}'
            . '.period-bar .p-label{font-weight:900;color:#0f172a;font-size:11pt;}'
            . '.period-bar .p-range{color:#475569;font-weight:700;direction:ltr;}'

            // جدول "الأرقام الرئيسية" — نظيف بحدود رفيعة، رقمين في الصف. لا مربعات، لا ألوان خلفية صارخة.
            . '.figures{width:100%;border-collapse:collapse;margin-bottom:14px;page-break-inside:avoid;break-inside:avoid;}'
            . '.figures td{border:1px solid #d8dee6;padding:7px 12px;vertical-align:middle;}'
            . '.figures .fg-l{background:#f8fafc;font-weight:700;color:#475569;width:23%;text-align:right;font-size:9.5pt;}'
            . '.figures .fg-v{width:27%;text-align:left;font-weight:900;color:#0f172a;font-size:12pt;font-feature-settings:"tnum";}'
            . '.figures .fg-v small{font-size:8pt;color:#94a3b8;font-weight:700;}'
            . '.figures .fg-v.pos{color:#047857;}'
            . '.figures .fg-v.neg{color:#b91c1c;}'
            . '.figures .fg-v.big{font-size:13.5pt;}'

            // صف "المحصلة" البارز في نهاية جدول الأرقام (مثلاً صافي الربح)
            . '.figures tr.headline td{background:#0f172a;color:#fff;border-color:#0f172a;}'
            . '.figures tr.headline .fg-l{background:#0f172a;color:#cbd5e1;}'
            . '.figures tr.headline .fg-v{color:#fff;font-size:13.5pt;}'

            . '.subhead{font-size:10.5pt;font-weight:900;color:#1e3a8a;margin:10px 0 6px;border-right:4px solid #fbbf24;padding-right:10px;page-break-after:avoid;break-after:avoid;}'
            . '.cat-cell{font-weight:800;color:#1e3a8a;}'
            . '.muted{color:#64748b;font-weight:700;}'
            . '.rank-cell{font-weight:800;color:#64748b;text-align:center;width:44px;}'
            . '.empty-note{padding:10px;text-align:center;color:#94a3b8;font-weight:700;font-size:10pt;background:#f8fafc;border:1px dashed #cbd5e1;margin-bottom:10px;}'
            . '</style>';
    }

    /**
     * شريط الفترة الموحّد أعلى كل تقرير مطبوع (نفس الفلتر المعروض على الشاشة).
     */
    private function xlsCoverBox(string $rangeLabel, $start, $end): string
    {
        $esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        return '<div class="period-bar">'
            . '<span class="p-label">الفترة: ' . $esc($rangeLabel) . '</span>'
            . '<span class="p-range">' . $esc($start->format('Y/m/d')) . ' — ' . $esc($end->format('Y/m/d')) . '</span>'
            . '</div>';
    }

    /**
     * جدول "الأرقام الرئيسية" الاحترافي — كل صف بند/قيمة، ورقمين في كل سطر مطبوع.
     * $rows: عناصر ['l'=>البند, 'v'=>القيمة المُنسّقة, 'cls'=>'pos'|'neg'|'', 'headline'=>bool]
     */
    private function figuresTable(array $rows): string
    {
        $esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        // نفصل صفوف المحصلة (headline) لتظهر بعرض كامل في سطرها الخاص
        $normal = array_values(array_filter($rows, fn($r) => empty($r['headline'])));
        $heads  = array_values(array_filter($rows, fn($r) => !empty($r['headline'])));

        $html = '<table class="figures">';
        foreach (array_chunk($normal, 2) as $pair) {
            $html .= '<tr>';
            foreach ($pair as $r) {
                $cls = $r['cls'] ?? '';
                $html .= '<td class="fg-l">' . $esc($r['l']) . '</td>'
                       . '<td class="fg-v ' . $cls . '">' . $r['v'] . '</td>';
            }
            if (count($pair) === 1) $html .= '<td class="fg-l"></td><td class="fg-v"></td>';
            $html .= '</tr>';
        }
        foreach ($heads as $r) {
            $cls = $r['cls'] ?? '';
            $html .= '<tr class="headline"><td class="fg-l">' . $esc($r['l']) . '</td>'
                   . '<td class="fg-v ' . $cls . '" colspan="3">' . $r['v'] . '</td></tr>';
        }
        $html .= '</table>';
        return $html;
    }

    // ══════════════════════════════════════════════════════════
    // 📦 طباعة تاب المخزن — نفس بيانات ReportController::inventoryReport
    // ══════════════════════════════════════════════════════════
    private function renderInventoryXls(array $inv, string $rangeLabel, $start, $end): string
    {
        $f  = fn($n) => number_format((float) $n, 2);
        $f0 = fn($n) => number_format((float) $n, 0);
        $esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        $m  = fn($n) => $f0($n) . ' <small>ج</small>';

        $h = $this->xlsHeader('تقرير المخزن');
        $h .= $this->xlsCoverBox($rangeLabel, $start, $end);

        $h .= '<div class="subhead">الأرقام الرئيسية</div>';
        $h .= $this->figuresTable([
            ['l' => 'مشتريات الفترة', 'v' => $m($inv['purchasedValue']), 'cls' => 'neg'],
            ['l' => 'عدد عمليات الشراء', 'v' => $f0($inv['purchasesCount']) . ' <small>عملية / ' . $f0($inv['purchasedItems']) . ' قطعة</small>'],
            ['l' => 'قيمة المخزون الحالي (شراء)', 'v' => $m($inv['currentStockCost'])],
            ['l' => 'قيمة المخزون الحالي (بيع)', 'v' => $m($inv['currentStockSell'])],
            ['l' => 'مبيعات المخزن', 'v' => $m($inv['invSalesValue']), 'cls' => 'pos'],
            ['l' => 'عدد عمليات البيع', 'v' => $f0($inv['invSalesCount']) . ' <small>عملية</small>'],
            ['l' => 'مرتجعات الفترة', 'v' => $f0($inv['returnsCount']) . ' <small>عملية / خسائر ' . $f0($inv['returnsLoss']) . ' ج</small>'],
            ['l' => 'ربح متوقع من المتبقي', 'v' => $m($inv['expectedProfit'])],
            ['l' => 'صافي ربح المخزن (بيع − شراء)', 'v' => $m($inv['invSalesProfit']), 'cls' => 'pos', 'headline' => true],
        ]);

        if (count($inv['categories']) > 0) {
            $h .= '<div class="section-title">مبيعات المخزن حسب الفئة</div>';
            $h .= '<table><tr><th>الفئة</th><th>عمليات</th><th>القيمة (ج.م)</th><th>الربح (ج.م)</th></tr>';
            foreach ($inv['categories'] as $c) {
                $h .= '<tr><td class="cat-cell">' . $esc($c['name']) . '</td><td>' . $f0($c['count']) . '</td><td>' . $f($c['value']) . '</td><td class="pos">' . $f($c['profit']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        if (count($inv['topProducts']) > 0) {
            $h .= '<div class="section-title">أكثر المنتجات مبيعاً</div>';
            $h .= '<table><tr><th>#</th><th>المنتج</th><th>الكمية</th><th>القيمة (ج.م)</th><th>الربح (ج.م)</th></tr>';
            $rank = 1;
            foreach ($inv['topProducts'] as $p) {
                $h .= '<tr><td class="rank-cell">' . $rank . '</td><td><b>' . $esc($p['name']) . '</b></td><td>' . $f0($p['qty']) . '</td><td>' . $f($p['revenue']) . '</td><td class="pos">' . $f($p['profit']) . '</td></tr>';
                $rank++;
            }
            $h .= '</table>';
        } else {
            $h .= '<div class="empty-note">— لا توجد مبيعات مخزن في هذه الفترة —</div>';
        }

        if (count($inv['topSuppliers']) > 0) {
            $h .= '<div class="section-title">أكثر الموردين توريداً</div>';
            $h .= '<table><tr><th>المورد</th><th>عمليات التوريد</th><th>القيمة (ج.م)</th></tr>';
            foreach ($inv['topSuppliers'] as $s) {
                $h .= '<tr><td><b>' . $esc($s['name']) . '</b></td><td>' . $f0($s['count']) . '</td><td class="neg">' . $f($s['value']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        return $this->xlsCommonCss() . $h . $this->xlsFooter();
    }

    // ══════════════════════════════════════════════════════════
    // 🔧 طباعة تاب الخدمات — نفس بيانات ReportController::servicesReport
    // ══════════════════════════════════════════════════════════
    private function renderServicesXls(array $s, string $rangeLabel, $start, $end): string
    {
        $f  = fn($n) => number_format((float) $n, 2);
        $f0 = fn($n) => number_format((float) $n, 0);
        $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $m  = fn($n) => $f0($n) . ' <small>ج</small>';

        $h = $this->xlsHeader('تقرير الخدمات');
        $h .= $this->xlsCoverBox($rangeLabel, $start, $end);

        $h .= '<div class="subhead">الأرقام الرئيسية</div>';
        $h .= $this->figuresTable([
            ['l' => 'عدد الخدمات', 'v' => $f0($s['servicesCount']) . ' <small>عملية</small>'],
            ['l' => 'متوسط قيمة الخدمة', 'v' => $m($s['avgPerService'])],
            ['l' => 'إجمالي الإيرادات', 'v' => $m($s['servicesRevenue']), 'cls' => 'pos'],
            ['l' => 'أجور الفنيين (التكلفة)', 'v' => $m($s['servicesCost']), 'cls' => 'neg'],
            ['l' => 'خدمات تم تحصيلها', 'v' => $f0($s['cashValue']) . ' <small>ج / ' . $f0($s['cashCount']) . ' عملية</small>', 'cls' => 'pos'],
            ['l' => 'خدمات لم تُحصّل بعد', 'v' => $f0($s['creditValue']) . ' <small>ج / ' . $f0($s['creditCount']) . ' عملية</small>', 'cls' => 'neg'],
            ['l' => 'صافي الربح (هامش ' . $s['avgProfitPct'] . '%)', 'v' => $m($s['servicesProfit']), 'cls' => 'pos', 'headline' => true],
        ]);

        if (count($s['topServices']) > 0) {
            $h .= '<div class="section-title">أكثر الخدمات تنفيذاً</div>';
            $h .= '<table><tr><th>#</th><th>الخدمة</th><th>عمليات</th><th>القيمة (ج.م)</th><th>الربح (ج.م)</th></tr>';
            $rank = 1;
            foreach ($s['topServices'] as $row) {
                $h .= '<tr><td class="rank-cell">' . $rank . '</td><td><b>' . $esc($row['name']) . '</b></td><td>' . $f0($row['count']) . '</td><td>' . $f($row['revenue']) . '</td><td class="pos">' . $f($row['profit']) . '</td></tr>';
                $rank++;
            }
            $h .= '</table>';
        } else {
            $h .= '<div class="empty-note">— لا توجد خدمات في هذه الفترة —</div>';
        }

        if (count($s['topCustomers']) > 0) {
            $h .= '<div class="section-title">أكثر العملاء طلباً للخدمة</div>';
            $h .= '<table><tr><th>العميل</th><th>عمليات</th><th>الإجمالي (ج.م)</th></tr>';
            foreach ($s['topCustomers'] as $row) {
                $h .= '<tr><td><b>' . $esc($row['name']) . '</b></td><td>' . $f0($row['count']) . '</td><td>' . $f($row['revenue']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        if (count($s['topTechs']) > 0) {
            $h .= '<div class="section-title">أكثر الفنيين شغلاً</div>';
            $h .= '<table><tr><th>الفني</th><th>عمليات</th><th>المدفوع له (ج.م)</th></tr>';
            foreach ($s['topTechs'] as $row) {
                $h .= '<tr><td><b>' . $esc($row['name']) . '</b></td><td>' . $f0($row['count']) . '</td><td class="neg">' . $f($row['paid']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        return $this->xlsCommonCss() . $h . $this->xlsFooter();
    }

    // ══════════════════════════════════════════════════════════
    // 📝 طباعة تاب الأقساط — نفس بيانات ReportController::installmentsReport
    // ══════════════════════════════════════════════════════════
    private function renderInstXls(array $i, string $rangeLabel, $start, $end): string
    {
        $f  = fn($n) => number_format((float) $n, 2);
        $f0 = fn($n) => number_format((float) $n, 0);
        $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $m  = fn($n) => $f0($n) . ' <small>ج</small>';

        $h = $this->xlsHeader('تقرير الأقساط');
        $h .= $this->xlsCoverBox($rangeLabel, $start, $end);

        $h .= '<div class="subhead">عقود ودفعات الفترة المختارة</div>';
        $h .= $this->figuresTable([
            ['l' => 'عقود جديدة', 'v' => $f0($i['contractsCount']) . ' <small>عقد / قيمتها ' . $f0($i['contractsValue']) . ' ج</small>'],
            ['l' => 'المحصّل بالفترة', 'v' => $f0($i['paymentsValue']) . ' <small>ج / ' . $f0($i['paymentsCount']) . ' دفعة</small>', 'cls' => 'pos'],
            ['l' => 'ربح النسبة (الفايدة)', 'v' => $m($i['interestProfit']), 'cls' => 'pos'],
            ['l' => 'ربح المنتجات', 'v' => $m($i['productProfit']), 'cls' => 'pos'],
            ['l' => 'الدفعات المقدمة', 'v' => $m($i['totalDownPayments'])],
            ['l' => 'متوسط مدة العقد', 'v' => $i['avgMonths'] . ' <small>شهر / متوسط قيمة ' . $f0($i['avgContractValue']) . ' ج</small>'],
            ['l' => 'إجمالي ربح عقود الفترة', 'v' => $m($i['totalContractProfit']), 'cls' => 'pos', 'headline' => true],
        ]);

        $h .= '<div class="subhead">الحالة الحالية (بغض النظر عن الفترة)</div>';
        $h .= $this->figuresTable([
            ['l' => 'أقساط نشطة', 'v' => $f0($i['activeContracts']) . ' <small>عقد</small>'],
            ['l' => 'إجمالي المديونيات القائمة', 'v' => $m($i['totalOutstanding']), 'cls' => 'neg'],
            ['l' => 'متأخرات (35+ يوم)', 'v' => $f0($i['overdueCount']) . ' <small>عقد / ' . $f0($i['overdueValue']) . ' ج</small>', 'cls' => 'neg'],
            ['l' => 'عقود مكتملة', 'v' => $f0($i['closedContracts']) . ' <small>عقد</small>', 'cls' => 'pos'],
            ['l' => 'عقود معدومة', 'v' => $f0($i['writtenOffCount']) . ' <small>عقد / ' . $f0($i['writtenOffValue']) . ' ج</small>', 'cls' => 'neg'],
        ]);

        if (count($i['topCustomers']) > 0) {
            $h .= '<div class="section-title">أكثر العملاء تعاقداً (عقود الفترة)</div>';
            $h .= '<table><tr><th>العميل</th><th>عقود</th><th>القيمة (ج.م)</th><th>المتبقي (ج.م)</th></tr>';
            foreach ($i['topCustomers'] as $row) {
                $h .= '<tr><td><b>' . $esc($row['name']) . '</b></td><td>' . $f0($row['count']) . '</td><td>' . $f($row['value']) . '</td><td class="neg">' . $f($row['remaining']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        if ($i['overdue']->count() > 0) {
            $h .= '<div class="section-title">أكبر المتأخرات حالياً</div>';
            $h .= '<table><tr><th>العميل</th><th>الصنف</th><th>المتبقي (ج.م)</th><th>القسط الشهري (ج.م)</th></tr>';
            foreach ($i['overdue']->sortByDesc('remaining_balance')->take(15) as $row) {
                $h .= '<tr><td><b>' . $esc($row->customer_name) . '</b></td><td>' . $esc($row->product_name) . '</td><td class="neg">' . $f($row->remaining_balance) . '</td><td>' . $f($row->monthly_installment) . '</td></tr>';
            }
            $h .= '</table>';
        }

        return $this->xlsCommonCss() . $h . $this->xlsFooter();
    }

    // ══════════════════════════════════════════════════════════
    // ⛽ طباعة تاب البنزينة — نفس بيانات ReportController::gasReport
    // ══════════════════════════════════════════════════════════
    private function renderGasXls(array $g, string $rangeLabel, $start, $end): string
    {
        $f  = fn($n) => number_format((float) $n, 2);
        $f0 = fn($n) => number_format((float) $n, 0);
        $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $m  = fn($n) => $f0($n) . ' <small>ج</small>';

        $h = $this->xlsHeader('تقرير محطة الوقود');
        $h .= $this->xlsCoverBox($rangeLabel, $start, $end);

        $h .= '<div class="subhead">الأرقام الرئيسية</div>';
        $h .= $this->figuresTable([
            ['l' => 'عمليات الفترة', 'v' => $f0($g['opsCount']) . ' <small>عملية / ' . $f0($g['totalLiters']) . ' لتر</small>'],
            ['l' => 'إجمالي مدفوع للمحطات', 'v' => $m($g['totalToStation']), 'cls' => 'neg'],
            ['l' => 'عهد نقدية مصروفة', 'v' => $m($g['totalAdvances'])],
            ['l' => 'مديونية شركات النقل (بالفترة)', 'v' => $m($g['totalOnCompany'])],
            ['l' => 'مستحقات لنا (تراكمي)', 'v' => $m($g['gasReceivables']), 'cls' => 'pos'],
            ['l' => 'مديونيات للمحطات (تراكمي)', 'v' => $m($g['gasPayablesStations']), 'cls' => 'neg'],
            ['l' => 'استقطاعات معلّقة', 'v' => $m($g['gasPayablesDeductions'])],
            ['l' => 'متوسط العمولة/عملية', 'v' => number_format($g['avgProfit'], 1) . ' <small>ج</small>'],
            ['l' => 'صافي العمولة (ربح الوقود)', 'v' => $m($g['netProfit']), 'cls' => 'pos', 'headline' => true],
        ]);

        if ($g['topCompanies']->count() > 0) {
            $h .= '<div class="section-title">أكبر شركات النقل</div>';
            $h .= '<table><tr><th>الشركة</th><th>عمليات</th><th>لترات</th><th>مديونية (ج.م)</th><th>ربحنا (ج.م)</th></tr>';
            foreach ($g['topCompanies'] as $c) {
                $h .= '<tr><td><b>' . $esc($c['name']) . '</b></td><td>' . $f0($c['count']) . '</td><td>' . $f($c['liters']) . '</td><td>' . $f($c['on_them']) . '</td><td class="pos">' . $f($c['profit']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        if ($g['topStations']->count() > 0) {
            $h .= '<div class="section-title">أكبر المحطات</div>';
            $h .= '<table><tr><th>المحطة</th><th>عمليات</th><th>لترات</th><th>المدفوع لها (ج.م)</th></tr>';
            foreach ($g['topStations'] as $s) {
                $h .= '<tr><td><b>' . $esc($s['name']) . '</b></td><td>' . $f0($s['count']) . '</td><td>' . $f($s['liters']) . '</td><td class="neg">' . $f($s['paid']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        if ($g['topDrivers']->count() > 0) {
            $h .= '<div class="section-title">أكثر السائقين تشغيلاً</div>';
            $h .= '<table><tr><th>السائق</th><th>عمليات</th><th>لترات</th></tr>';
            foreach ($g['topDrivers'] as $d) {
                $h .= '<tr><td><b>' . $esc($d['name']) . '</b></td><td>' . $f0($d['count']) . '</td><td>' . $f($d['liters']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        return $this->xlsCommonCss() . $h . $this->xlsFooter();
    }

    // ══════════════════════════════════════════════════════════
    // 💰 طباعة تاب الحركة المالية — نفس بيانات ReportController::financialReport
    // ══════════════════════════════════════════════════════════
    private function renderFinXls(array $fin, string $rangeLabel, $start, $end): string
    {
        $f  = fn($n) => number_format((float) $n, 2);
        $f0 = fn($n) => number_format((float) $n, 0);
        $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $m  = fn($n) => $f0($n) . ' <small>ج</small>';

        $h = $this->xlsHeader('تقرير الحركة المالية');
        $h .= $this->xlsCoverBox($rangeLabel, $start, $end);

        $h .= '<div class="subhead">ملخص الحركة المالية</div>';
        $h .= $this->figuresTable([
            ['l' => 'إجمالي الإيرادات', 'v' => $m($fin['totalIncomes']), 'cls' => 'pos'],
            ['l' => 'إجمالي المصروفات', 'v' => $m($fin['totalExpenses']), 'cls' => 'neg'],
            ['l' => 'التسويات (دخلت كدين علينا)', 'v' => $m($fin['totalSettlements'])],
            ['l' => 'إجمالي التدفقات الخارجة', 'v' => $m($fin['totalExpensesGross']), 'cls' => 'neg'],
            ['l' => 'السيولة الحالية', 'v' => $m($fin['totalLiquidity'])],
            ['l' => 'نمو رأس المال (' . $fin['capitalPct'] . '%)', 'v' => ($fin['capitalDiff'] >= 0 ? '+' : '') . $m($fin['capitalDiff']), 'cls' => ($fin['capitalDiff'] >= 0 ? 'pos' : 'neg')],
            ['l' => 'صافي التدفق النقدي', 'v' => $m($fin['netCashFlow']), 'cls' => ($fin['netCashFlow'] >= 0 ? 'pos' : 'neg'), 'headline' => true],
        ]);

        $h .= '<div class="subhead">تفصيل المصروفات والالتزامات</div>';
        $h .= $this->figuresTable([
            ['l' => 'الرواتب', 'v' => $m($fin['salaries']), 'cls' => 'neg'],
            ['l' => 'العمولات', 'v' => $m($fin['commissions']), 'cls' => 'neg'],
            ['l' => 'خصومات للعملاء', 'v' => $m($fin['discounts']), 'cls' => 'neg'],
            ['l' => 'إعدامات ديون', 'v' => $m($fin['badDebts']), 'cls' => 'neg'],
            ['l' => 'إهلاك أصول ثابتة', 'v' => $m($fin['depreciation']), 'cls' => 'neg'],
            ['l' => 'خسارة فرق سعر', 'v' => $m($fin['priceDiffLoss']), 'cls' => 'neg'],
            ['l' => 'عُهد الموظفين', 'v' => $m($fin['advancesTotal'])],
            ['l' => 'ديون لنا (السوق: أقساط + آجل)', 'v' => $m($fin['debtsForUs']), 'cls' => 'pos'],
            ['l' => 'ديون علينا (موردين والتزامات)', 'v' => $m($fin['debtsOnUs']), 'cls' => 'neg'],
        ]);

        if (count($fin['expensesByCategory']) > 0) {
            $h .= '<div class="section-title">المصروفات حسب التصنيف</div>';
            $h .= '<table><tr><th>التصنيف</th><th>عمليات</th><th>القيمة (ج.م)</th></tr>';
            foreach ($fin['expensesByCategory'] as $c) {
                $h .= '<tr><td class="cat-cell">' . $esc($c['name']) . '</td><td>' . $f0($c['count']) . '</td><td class="neg">' . $f($c['value']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        if (count($fin['byPerson']) > 0) {
            $h .= '<div class="section-title">مصاريف الموظفين (عُهد)</div>';
            $h .= '<table><tr><th>الاسم</th><th>عمليات</th><th>الإجمالي (ج.م)</th></tr>';
            foreach ($fin['byPerson'] as $p) {
                $h .= '<tr><td><b>' . $esc($p['name']) . '</b></td><td>' . $f0($p['count']) . '</td><td class="neg">' . $f($p['total']) . '</td></tr>';
            }
            $h .= '</table>';
        }

        return $this->xlsCommonCss() . $h . $this->xlsFooter();
    }
}
