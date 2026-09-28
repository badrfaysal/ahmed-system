@if(!isset($tab))
<style>
    /* Styling for AC Reports Partial (used in ac.blade.php) */
    .kpi-grid { display: grid; gap: 14px; margin-bottom: 18px; }
    .kpi-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
    .kpi-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    @media (max-width: 992px) { .kpi-grid.cols-4 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .kpi-grid { grid-template-columns: 1fr !important; } }
    .kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    .kpi-card::before { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 4px; background: #64748b; }
    .kpi-card.success::before { background: #10b981; }
    .kpi-card.danger::before  { background: #ef4444; }
    .kpi-card.warning::before { background: #f59e0b; }
    .kpi-card.info::before    { background: #3b82f6; }
    .kpi-card.accent::before  { background: #8b5cf6; }
    .kpi-card .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
    .kpi-card .kpi-value { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .kpi-card .kpi-unit { font-size: 0.9rem; color: #94a3b8; font-weight: 500; }
    .kpi-card .kpi-sub { font-size: 0.8rem; color: #64748b; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; }
    
    .panel-pro { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; }
    .panel-pro-head { padding: 14px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
    .panel-pro-head h5 { margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.9rem; }
    .data-table th { background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.85rem; padding: 10px 14px; text-align: start; border-bottom: 1px solid #e2e8f0; }
    .data-table td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
    .data-table tr:hover td { background: #f8fafc; }
    .data-table .rank { display: inline-flex; width: 24px; height: 24px; background: #e2e8f0; color: #475569; border-radius: 50%; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
    .data-table tr:nth-child(1) .rank { background: #8b5cf6; color: #fff; }
    .data-table tr:nth-child(2) .rank { background: #3b82f6; color: #fff; }
    .data-table tr:nth-child(3) .rank { background: #10b981; color: #fff; }
    .table-scroll { max-height: 400px; overflow-y: auto; }
    .empty-mini { padding: 30px 16px; text-align: center; color: #94a3b8; }
    .empty-mini i { font-size: 2rem; opacity: 0.5; margin-bottom: 10px; display: block; }
    .num-pos { color: #10b981; font-weight: 700; }
    .num-neg { color: #ef4444; font-weight: 700; }
    .badge-soft { font-size: 0.75rem; padding: 4px 10px; border-radius: 999px; background: #f1f5f9; color: #475569; font-weight: 600; }
    .badge-soft.primary { background: #dbeafe; color: #2563eb; }
    .badge-soft.info { background: #cffafe; color: #0891b2; }
    .chart-box { position: relative; height: 300px; padding: 15px; }
</style>
@endif

<div class="kpi-grid cols-4">
    <div class="kpi-card accent">
        <div class="kpi-label"><i class="fa fa-fan"></i> إجمالي مبيعات وتركيب</div>
        <div class="kpi-value">{{ fmtMoney($globalStats['sales_total'] ?? 0) }} <span class="kpi-unit">ج</span></div>
        <div class="kpi-sub">{{ $globalStats['sales_count'] ?? 0 }} عملية بيع · أرباح: {{ fmtMoney($globalStats['sales_profit'] ?? 0) }} ج</div>
    </div>
    <div class="kpi-card info">
        <div class="kpi-label"><i class="fa fa-tools"></i> إجمالي الصيانات</div>
        <div class="kpi-value">{{ fmtMoney($globalStats['maint_total'] ?? 0) }} <span class="kpi-unit">ج</span></div>
        <div class="kpi-sub">{{ $globalStats['maint_count'] ?? 0 }} صيانة · أرباح: {{ fmtMoney($globalStats['maint_profit'] ?? 0) }} ج</div>
    </div>
    <div class="kpi-card warning">
        <div class="kpi-label"><i class="fa fa-tags"></i> إجمالي الخصومات</div>
        <div class="kpi-value">{{ fmtMoney($globalStats['discounts'] ?? 0) }} <span class="kpi-unit">ج</span></div>
        <div class="kpi-sub">خصومات منحت للعملاء بالفترة المحددة</div>
    </div>
    <div class="kpi-card success">
        <div class="kpi-label"><i class="fa fa-wallet"></i> إجمالي صافي الربح</div>
        <div class="kpi-value">{{ fmtMoney($globalStats['net_profit'] ?? 0) }} <span class="kpi-unit">ج</span></div>
        <div class="kpi-sub">المبيعات + الصيانات - (الخصومات + المصروفات)</div>
    </div>
</div>

<div class="kpi-grid cols-2" style="grid-template-columns: repeat(2, 1fr);">
    <div class="kpi-card danger">
        <div class="kpi-label"><i class="fa fa-money-bill-wave"></i> إجمالي المصروفات على المدارس</div>
        <div class="kpi-value">{{ fmtMoney($globalStats['total_expenses'] ?? 0) }} <span class="kpi-unit">ج</span></div>
        <div class="kpi-sub">لا تُحسب على العميل ولكن تُخصم من الأرباح</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label"><i class="fa fa-building"></i> أعلى مدرسة تكلفة (مصروفات)</div>
        <div class="kpi-value">
            @php 
                $filteredTopExpense = array_filter($topExpenseClients ?? [], function($k) { return !empty($k); }, ARRAY_FILTER_USE_KEY);
                $highestExpenseClient = !empty($filteredTopExpense) ? array_key_first($filteredTopExpense) : 'لا يوجد';
                $highestExpenseAmount = !empty($filteredTopExpense) ? current($filteredTopExpense) : 0;
            @endphp
            <span style="font-size: 1.2rem;">{{ $highestExpenseClient }}</span>
        </div>
        <div class="kpi-sub">إجمالي مصروفاتها: {{ fmtMoney($highestExpenseAmount) }} ج</div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-star text-warning"></i> أفضل العملاء تعاملاً</h5></div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th class="text-center">#</th><th>العميل</th><th class="text-center">عمليات</th><th class="text-center">المبيعات</th><th class="text-center">التكلفة</th><th class="text-center">الخصم</th><th class="text-center">المصروفات</th><th class="text-center">الربح</th></tr></thead>
                    <tbody>
                        @forelse($topClients ?? [] as $i => $c)
                        <tr>
                            <td class="text-center"><span class="rank">{{ $i+1 }}</span></td>
                            <td class="fw-bold">{{ $c['name'] }}</td>
                            <td class="text-center">{{ $c['count'] }}</td>
                            <td class="text-center fw-bold text-dark">{{ fmtMoney($c['revenue']) }}</td>
                            <td class="text-center text-muted">{{ fmtMoney($c['cost']) }}</td>
                            <td class="text-center num-neg">{{ $c['discount'] > 0 ? fmtMoney($c['discount']) : '-' }}</td>
                            <td class="text-center num-neg">{{ isset($c['expenses']) && $c['expenses'] > 0 ? fmtMoney($c['expenses']) : '-' }}</td>
                            <td class="text-center num-pos">{{ fmtMoney($c['profit']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8"><div class="empty-mini"><i class="fa fa-users"></i> لا يوجد عملاء</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
    (function() {

        if ($.fn.DataTable) {
            $('.data-table').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        "pageLength": 10,
                        "lengthChange": false,
                        "language": {
                            "search": "بحث:",
                            "paginate": {
                                "first": "الأول",
                                "last": "الأخير",
                                "next": "التالي",
                                "previous": "السابق"
                            },
                            "info": "عرض _START_ إلى _END_ من أصل _TOTAL_ سجل",
                            "emptyTable": "لا توجد بيانات متاحة في الجدول"
                        }
                    });
                }
            });
        }
    })();
</script>
