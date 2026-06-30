<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير والتحليلات - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('partials.theme', ['accent' => 'indigo'])

    <style>
        /* ── شريط الترحيب ── */
        .report-hero {
            background: linear-gradient(135deg, var(--c-navy), var(--c-navy-soft));
            color: #fff;
            border-radius: var(--r-lg);
            padding: 22px 26px;
            margin-bottom: 18px;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 14px;
            position: relative; overflow: hidden;
        }
        .report-hero::after {
            content: ''; position: absolute;
            top: 0; left: 0; bottom: 0; width: 4px;
            background: var(--c-accent);
        }
        .report-hero h1 { color: #fff; margin: 0 0 4px; font-size: 1.35rem; font-weight: 600; }
        .report-hero .meta { color: rgba(255,255,255,0.7); font-size: 0.86rem; }
        .report-hero .right { color: rgba(255,255,255,0.85); font-size: 0.84rem; text-align: end; }

        /* ── فلاتر الفترة ── */
        .period-filters {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            background: var(--c-surface); border: 1px solid var(--c-border);
            border-radius: var(--r-md); padding: 10px 14px; margin-bottom: 18px;
        }
        .period-filters .label { font-size: 0.82rem; color: var(--c-text-muted); font-weight: 500; }
        .period-filters .pill {
            border: 1px solid var(--c-border); background: var(--c-surface-2);
            color: var(--c-text); font-size: 0.82rem; font-weight: 500;
            padding: 6px 14px; border-radius: 999px;
            text-decoration: none; transition: var(--t-fast); cursor: pointer;
        }
        .period-filters .pill:hover { border-color: var(--c-accent); color: var(--c-accent); }
        .period-filters .pill.active {
            background: var(--c-navy); color: #fff; border-color: var(--c-navy);
        }
        .period-filters .custom-range { display: flex; align-items: center; gap: 6px; margin-inline-start: auto; }
        .period-filters .custom-range input {
            border: 1px solid var(--c-border); background: var(--c-surface-2);
            border-radius: var(--r-sm); padding: 5px 10px;
            font-size: 0.82rem; color: var(--c-text); font-family: inherit;
        }
        .period-filters .custom-range button {
            background: var(--c-accent); color: #fff; border: none;
            border-radius: var(--r-sm); padding: 6px 14px;
            font-size: 0.82rem; font-weight: 600; cursor: pointer;
        }

        /* ── التابات ── */
        .tabs-bar {
            display: flex; gap: 4px;
            background: var(--c-surface); border: 1px solid var(--c-border);
            border-radius: var(--r-md); padding: 5px;
            margin-bottom: 20px; flex-wrap: wrap;
        }
        .tabs-bar a {
            flex: 1; min-width: 130px; text-align: center;
            padding: 11px 14px; border-radius: var(--r-sm);
            font-size: 0.9rem; font-weight: 600;
            text-decoration: none; color: var(--c-text-muted);
            transition: var(--t-fast);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .tabs-bar a:hover { background: var(--c-surface-2); color: var(--c-text); }
        .tabs-bar a.active {
            background: var(--c-navy); color: #fff;
            box-shadow: var(--shadow-sm);
        }
        .tabs-bar a i { font-size: 1rem; }

        /* ── KPI cards ── */
        .kpi-grid { display: grid; gap: 14px; margin-bottom: 18px; }
        .kpi-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
        .kpi-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .kpi-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        @media (max-width: 992px) { .kpi-grid.cols-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px) { .kpi-grid { grid-template-columns: 1fr !important; } }

        .kpi-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--r-md);
            padding: 16px 18px;
            position: relative; overflow: hidden;
            transition: var(--t-fast);
        }
        .kpi-card:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .kpi-card::before {
            content: ''; position: absolute; top: 0; right: 0; bottom: 0;
            width: 3px; background: var(--c-text-muted);
        }
        .kpi-card.success::before { background: var(--c-success); }
        .kpi-card.danger::before  { background: var(--c-danger); }
        .kpi-card.warning::before { background: var(--c-warning); }
        .kpi-card.info::before    { background: var(--c-info); }
        .kpi-card.accent::before  { background: var(--c-accent); }
        .kpi-card .kpi-label {
            font-size: 0.78rem; color: var(--c-text-muted);
            font-weight: 500; margin-bottom: 6px;
            display: flex; align-items: center; gap: 6px;
        }
        .kpi-card .kpi-value {
            font-size: 1.5rem; font-weight: 700; color: var(--c-navy);
            font-feature-settings: 'tnum'; line-height: 1.2;
        }
        .kpi-card .kpi-unit { font-size: 0.85rem; color: var(--c-text-muted); font-weight: 400; }
        .kpi-card .kpi-sub {
            font-size: 0.75rem; color: var(--c-text-soft);
            margin-top: 6px; padding-top: 6px;
            border-top: 1px dashed var(--c-border);
        }

        /* ── Tables ── */
        .data-table {
            width: 100%; border-collapse: separate; border-spacing: 0;
            font-size: 0.86rem;
        }
        .data-table th {
            background: var(--c-surface-2); color: var(--c-text-muted);
            font-weight: 600; font-size: 0.78rem;
            padding: 9px 12px; text-align: start;
            border-bottom: 1px solid var(--c-border);
            position: sticky; top: 0;
        }
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--c-border);
            color: var(--c-text);
            font-feature-settings: 'tnum';
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: var(--c-navy-50); }
        .data-table .rank {
            display: inline-flex; width: 22px; height: 22px;
            background: var(--c-surface-2); color: var(--c-text-muted);
            border-radius: 50%; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 700;
        }
        .data-table tr:nth-child(1) .rank { background: var(--c-accent); color: #fff; }
        .data-table tr:nth-child(2) .rank { background: var(--c-info); color: #fff; }
        .data-table tr:nth-child(3) .rank { background: var(--c-success); color: #fff; }

        .table-scroll { max-height: 380px; overflow-y: auto; }

        .empty-mini {
            padding: 30px 16px; text-align: center;
            color: var(--c-text-soft);
        }
        .empty-mini i { font-size: 1.8rem; opacity: 0.4; margin-bottom: 8px; display: block; }

        .chart-box { position: relative; height: 280px; padding-top: 4px; }

        .badge-soft {
            font-size: 0.72rem; padding: 3px 8px; border-radius: 999px;
            background: var(--c-surface-2); color: var(--c-text-muted);
            font-weight: 600;
        }
        .badge-soft.success { background: var(--c-success-bg); color: var(--c-success); }
        .badge-soft.warning { background: var(--c-warning-bg); color: var(--c-warning); }
        .badge-soft.danger  { background: var(--c-danger-bg);  color: var(--c-danger); }
        .badge-soft.info    { background: var(--c-info-bg);    color: var(--c-info); }

        .num-pos { color: var(--c-success); font-weight: 600; }
        .num-neg { color: var(--c-danger); font-weight: 600; }
    </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    {{-- ── الترحيب ── --}}
    <div class="report-hero">
        <div>
            <h1><i class="fa-solid fa-chart-pie me-2"></i> التقارير والتحليلات المتقدمة</h1>
            <div class="meta">رصد كل قسم بكل تفاصيله — الفترة الحالية: <strong>{{ $rangeLabel }}</strong></div>
        </div>
        <div class="right">
            <div><i class="fa-regular fa-calendar me-1"></i> {{ $startDate->format('Y/m/d') }} → {{ $endDate->format('Y/m/d') }}</div>
            <a href="{{ url('/reports/export?date_filter='.$dateFilter.($customFrom?'&custom_from='.$customFrom:'').($customTo?'&custom_to='.$customTo:'')) }}"
               target="_blank" rel="noopener"
               style="display:inline-block; margin-top:8px; background:#dc2626; color:#fff; padding:6px 14px; border-radius:8px; font-weight:600; font-size:0.85rem; text-decoration:none;"
               title="تصدير التقارير كـ PDF">
                <i class="fa fa-file-pdf me-1"></i> تصدير PDF
            </a>
        </div>
    </div>

    {{-- ── فلاتر الفترة ── --}}
    @php
        $filters = [
            'today'     => 'اليوم',
            'yesterday' => 'أمس',
            'week'      => 'هذا الأسبوع',
            'month'     => 'هذا الشهر',
            'year'      => 'هذا العام',
        ];
    @endphp
    <form method="GET" action="{{ url('/reports') }}" class="period-filters">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <span class="label"><i class="fa-regular fa-calendar"></i> الفترة:</span>
        @foreach($filters as $key => $name)
            <a href="{{ url('/reports?tab='.$tab.'&date_filter='.$key) }}"
               class="pill {{ $dateFilter === $key ? 'active' : '' }}">{{ $name }}</a>
        @endforeach

        <div class="custom-range">
            <input type="hidden" name="date_filter" value="custom">
            <input type="date" name="custom_from" value="{{ $customFrom }}" max="{{ now()->format('Y-m-d') }}">
            <span style="color: var(--c-text-muted); font-size: 0.78rem;">إلى</span>
            <input type="date" name="custom_to" value="{{ $customTo }}" max="{{ now()->format('Y-m-d') }}">
            <button type="submit"><i class="fa fa-filter"></i> تطبيق</button>
        </div>
    </form>

    {{-- ── شريط التابات ── --}}
    @php
        $tabs = [
            'inventory' => ['icon' => 'fa-warehouse',          'name' => 'المخزن'],
            'services'  => ['icon' => 'fa-screwdriver-wrench', 'name' => 'الخدمات'],
            'inst'      => ['icon' => 'fa-file-signature',     'name' => 'الأقساط'],
            'gas'       => ['icon' => 'fa-gas-pump',           'name' => 'البنزينة'],
            'fin'       => ['icon' => 'fa-money-bill-trend-up','name' => 'الحركة المالية'],
        ];
    @endphp
    <div class="tabs-bar">
        @foreach($tabs as $key => $t)
            <a href="{{ url('/reports?tab='.$key.'&date_filter='.$dateFilter.($customFrom?'&custom_from='.$customFrom:'').($customTo?'&custom_to='.$customTo:'')) }}"
               class="{{ $tab === $key ? 'active' : '' }}">
                <i class="fa-solid {{ $t['icon'] }}"></i> {{ $t['name'] }}
            </a>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- 📦 تاب المخزن --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @if($tab === 'inventory')
        <div class="kpi-grid cols-4">
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-truck-ramp-box"></i> مشتريات الفترة</div>
                <div class="kpi-value">{{ fmtMoney($inv['purchasedValue']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">{{ $inv['purchasesCount'] }} عملية شراء · {{ fmtMoney($inv['purchasedItems']) }} قطعة</div>
            </div>
            <div class="kpi-card accent">
                <div class="kpi-label"><i class="fa fa-boxes-stacked"></i> المخزون الحالي</div>
                <div class="kpi-value">{{ fmtMoney($inv['currentStockCost']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">{{ $inv['currentStockProducts'] }} منتج · {{ fmtMoney($inv['currentStockItems']) }} قطعة · بيع: {{ fmtMoney($inv['currentStockSell']) }} ج</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-cart-shopping"></i> مبيعات المخزن</div>
                <div class="kpi-value">{{ fmtMoney($inv['invSalesValue']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">{{ $inv['invSalesCount'] }} عملية بيع</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-coins"></i> ربح المخزن (بيع − شراء)</div>
                <div class="kpi-value">{{ fmtMoney($inv['invSalesProfit']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">بدون فايدة التقسيط · ربح متوقع من المتبقي: {{ fmtMoney($inv['expectedProfit']) }} ج</div>
            </div>
        </div>

        {{-- 💡 مصاريف تكييفات إضافية (نقل/تركيب/خامات) لمبيعات المخزن --}}
        <div class="panel-pro" style="border-right:4px solid #f59e0b;">
            <div class="panel-pro-head">
                <h5><i class="fa fa-screwdriver-wrench text-warning"></i> مصاريف التكييفات الإضافية - مبيعات المخزن</h5>
                <small class="text-muted">{{ $inv['acExtras']->operations }} عملية مبيع شملت هذه المصاريف</small>
            </div>
            <div class="kpi-grid cols-4">
                <div class="kpi-card" style="background:#fff7ed; border-color:#fb923c;">
                    <div class="kpi-label"><i class="fa fa-truck text-warning"></i> النقل</div>
                    <div class="kpi-value">{{ fmtMoney($inv['acExtras']->transport) }} <span class="kpi-unit">ج</span></div>
                </div>
                <div class="kpi-card" style="background:#fff7ed; border-color:#fb923c;">
                    <div class="kpi-label"><i class="fa fa-tools text-warning"></i> التركيب</div>
                    <div class="kpi-value">{{ fmtMoney($inv['acExtras']->installation) }} <span class="kpi-unit">ج</span></div>
                </div>
                <div class="kpi-card" style="background:#fff7ed; border-color:#fb923c;">
                    <div class="kpi-label"><i class="fa fa-cubes text-warning"></i> الخامات</div>
                    <div class="kpi-value">{{ fmtMoney($inv['acExtras']->materials) }} <span class="kpi-unit">ج</span></div>
                </div>
                <div class="kpi-card" style="background:#1f2937; color:#fff; border-color:#374151;">
                    <div class="kpi-label" style="color:#fbbf24;"><i class="fa fa-calculator"></i> الإجمالي</div>
                    <div class="kpi-value" style="color:#fff;">{{ fmtMoney($inv['acExtras']->total) }} <span class="kpi-unit" style="color:#fde68a;">ج</span></div>
                </div>
            </div>
        </div>

        {{-- مرتجعات --}}
        <div class="kpi-grid cols-3">
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-undo"></i> مرتجعات الفترة</div>
                <div class="kpi-value">{{ $inv['returnsCount'] }} <span class="kpi-unit">عملية</span></div>
                <div class="kpi-sub">{{ fmtMoney($inv['returnsQty']) }} قطعة · خسائر: {{ fmtMoney($inv['returnsLoss']) }} ج</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-arrow-trend-up"></i> أكثر المنتجات بيعاً</div>
                <div class="kpi-value" style="font-size:1.1rem">{{ $inv['topProduct']['name'] ?? '—' }}</div>
                <div class="kpi-sub">{{ isset($inv['topProduct']['qty']) ? fmtMoney($inv['topProduct']['qty']).' قطعة' : 'لا توجد مبيعات بعد' }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-arrow-trend-down"></i> أقل منتج تحريكاً</div>
                <div class="kpi-value" style="font-size:1.1rem">{{ $inv['leastProduct']['name'] ?? '—' }}</div>
                <div class="kpi-sub">{{ isset($inv['leastProduct']['qty']) ? fmtMoney($inv['leastProduct']['qty']).' قطعة' : '—' }}</div>
            </div>
        </div>

        {{-- شارت يومي --}}
        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-chart-area"></i> حركة مبيعات المخزن اليومية</h5></div>
            <div class="chart-box"><canvas id="invDailyChart"></canvas></div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-trophy"></i> أكثر المنتجات مبيعاً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>المنتج</th><th>كمية</th><th>قيمة</th><th>ربح</th></tr></thead>
                            <tbody>
                                @forelse($inv['topProducts'] as $i => $p)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ fmtMoney($p['qty']) }}</td>
                                    <td>{{ fmtMoney($p['revenue']) }}</td>
                                    <td class="num-pos">{{ fmtMoney($p['profit']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-mini"><i class="fa fa-box-open"></i>لا يوجد بيانات</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-truck"></i> أكثر الموردين توريداً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>المورد</th><th>عمليات</th><th>قيمة الشراء</th></tr></thead>
                            <tbody>
                                @forelse($inv['topSuppliers'] as $i => $s)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $s['name'] }}</td>
                                    <td>{{ $s['count'] }}</td>
                                    <td>{{ fmtMoney($s['value']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4"><div class="empty-mini"><i class="fa fa-truck"></i>لا يوجد موردين بالفترة</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-tags"></i> التصنيفات الأكثر مبيعاً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>التصنيف</th><th>عمليات</th><th>قيمة</th><th>ربح</th></tr></thead>
                            <tbody>
                                @forelse($inv['categories'] as $c)
                                <tr>
                                    <td>{{ $c['name'] }}</td>
                                    <td>{{ $c['count'] }}</td>
                                    <td>{{ fmtMoney($c['value']) }}</td>
                                    <td class="num-pos">{{ fmtMoney($c['profit']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4"><div class="empty-mini"><i class="fa fa-tags"></i>لا يوجد تصنيفات</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-triangle-exclamation"></i> منتجات قاربت على النفاد</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>المنتج</th><th>المورد</th><th>المتبقي</th></tr></thead>
                            <tbody>
                                @forelse($inv['lowStock'] as $p)
                                <tr>
                                    <td>{{ $p->product_name }}</td>
                                    <td>{{ $p->supplier_name ?: '—' }}</td>
                                    <td><span class="badge-soft danger">{{ $p->remaining_quantity }} قطعة</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="3"><div class="empty-mini"><i class="fa fa-check-circle"></i>كل المنتجات بكميات كافية</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($inv['mostReturned'])
        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-rotate-left"></i> أكثر منتج تم إرجاعه</h5></div>
            <div style="padding: 12px 16px; font-size: 0.95rem;">
                <strong style="color: var(--c-navy);">{{ $inv['mostReturned']['name'] }}</strong>
                — {{ fmtMoney($inv['mostReturned']['qty']) }} قطعة مرتجعة
                <span class="num-neg">(خسائر: {{ fmtMoney($inv['mostReturned']['loss']) }} ج)</span>
            </div>
        </div>
        @endif

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- 🔧 تاب الخدمات --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'services')
        <div class="kpi-grid cols-4">
            <div class="kpi-card accent">
                <div class="kpi-label"><i class="fa fa-screwdriver-wrench"></i> عدد الخدمات</div>
                <div class="kpi-value">{{ $services['servicesCount'] }} <span class="kpi-unit">عملية</span></div>
                <div class="kpi-sub">متوسط/عملية: {{ fmtMoney($services['avgPerService']) }} ج</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-money-bill-trend-up"></i> إجمالي الإيرادات</div>
                <div class="kpi-value">{{ fmtMoney($services['servicesRevenue']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">قيمة كل الخدمات المنفذة</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-tools"></i> أجور الفنيين</div>
                <div class="kpi-value">{{ fmtMoney($services['servicesCost']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">إجمالي تكلفة الخدمات</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-coins"></i> صافي الربح</div>
                <div class="kpi-value">{{ fmtMoney($services['servicesProfit']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">هامش الربح: {{ $services['avgProfitPct'] }}%</div>
            </div>
        </div>

        {{-- نقدي vs آجل --}}
        <div class="kpi-grid cols-2">
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-money-bill"></i> خدمات تم تحصيلها</div>
                <div class="kpi-value">{{ fmtMoney($services['cashValue']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">{{ $services['cashCount'] }} عملية مدفوعة كاملاً</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-clock"></i> خدمات لم تحصّل بعد</div>
                <div class="kpi-value">{{ fmtMoney($services['creditValue']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">{{ $services['creditCount'] }} عملية بمتبقي</div>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-chart-area"></i> حركة الخدمات اليومية</h5></div>
            <div class="chart-box"><canvas id="servicesDailyChart"></canvas></div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-trophy"></i> أكثر الخدمات تنفيذاً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>الخدمة</th><th>عمليات</th><th>قيمة</th><th>ربح</th></tr></thead>
                            <tbody>
                                @forelse($services['topServices'] as $i => $s)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $s['name'] }}</td>
                                    <td>{{ $s['count'] }}</td>
                                    <td>{{ fmtMoney($s['revenue']) }}</td>
                                    <td class="num-pos">{{ fmtMoney($s['profit']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-mini"><i class="fa fa-tools"></i>لا توجد خدمات في هذه الفترة</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-user-tie"></i> أكثر العملاء طلباً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>العميل</th><th>عمليات</th><th>إجمالي</th></tr></thead>
                            <tbody>
                                @forelse($services['topCustomers'] as $i => $c)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $c['name'] }}</td>
                                    <td>{{ $c['count'] }}</td>
                                    <td>{{ fmtMoney($c['revenue']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4"><div class="empty-mini"><i class="fa fa-users"></i>لا يوجد عملاء</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-screwdriver-wrench"></i> أكثر الفنيين شغلاً</h5></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>#</th><th>الفني</th><th>عمليات</th><th>أجور مدفوعة</th></tr></thead>
                    <tbody>
                        @forelse($services['topTechs'] as $i => $t)
                        <tr>
                            <td><span class="rank">{{ $i+1 }}</span></td>
                            <td>{{ $t['name'] }}</td>
                            <td>{{ $t['count'] }}</td>
                            <td>{{ fmtMoney($t['paid']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-mini"><i class="fa fa-user"></i>لا يوجد فنيين بالفترة</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- 📝 تاب الأقساط --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'inst')
        <div class="kpi-grid cols-4">
            <div class="kpi-card accent">
                <div class="kpi-label"><i class="fa fa-file-signature"></i> عقود جديدة</div>
                <div class="kpi-value">{{ $inst['contractsCount'] }}</div>
                <div class="kpi-sub">قيمتها: {{ fmtMoney($inst['contractsValue']) }} ج</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-percent"></i> ربح الفوائد</div>
                <div class="kpi-value">{{ fmtMoney($inst['interestProfit']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">متوسط الفائدة: {{ $inst['avgInterestPct'] }}%</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-box"></i> ربح المنتجات</div>
                <div class="kpi-value">{{ fmtMoney($inst['productProfit']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">إجمالي الربح: {{ fmtMoney($inst['totalContractProfit']) }} ج</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-money-bill"></i> المحصّل بالفترة</div>
                <div class="kpi-value">{{ fmtMoney($inst['paymentsValue']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">{{ $inst['paymentsCount'] }} دفعة · خصومات: {{ fmtMoney($inst['discountsGiven']) }} ج</div>
            </div>
        </div>

        <div class="kpi-grid cols-4">
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-calendar"></i> متوسط مدة العقد</div>
                <div class="kpi-value">{{ $inst['avgMonths'] }} <span class="kpi-unit">شهر</span></div>
                <div class="kpi-sub">متوسط قيمة العقد: {{ fmtMoney($inst['avgContractValue']) }} ج</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-hand-holding-dollar"></i> الدفعات المقدمة</div>
                <div class="kpi-value">{{ fmtMoney($inst['totalDownPayments']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">في عقود الفترة</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-check-circle"></i> عقود مكتملة</div>
                <div class="kpi-value">{{ $inst['closedContracts'] }}</div>
                <div class="kpi-sub">من إجمالي الأقساط</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-circle-exclamation"></i> عقود معدومة</div>
                <div class="kpi-value">{{ $inst['writtenOffCount'] }}</div>
                <div class="kpi-sub">بقيمة: {{ fmtMoney($inst['writtenOffValue']) }} ج</div>
            </div>
        </div>

        <div class="kpi-grid cols-2">
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-clock"></i> أقساط نشطة</div>
                <div class="kpi-value">{{ $inst['activeContracts'] }}</div>
                <div class="kpi-sub">مديونيات بقيمة: {{ fmtMoney($inst['totalOutstanding']) }} ج</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-triangle-exclamation"></i> متأخرات (35+ يوم)</div>
                <div class="kpi-value">{{ $inst['overdueCount'] }}</div>
                <div class="kpi-sub">بإجمالي: {{ fmtMoney($inst['overdueValue']) }} ج</div>
            </div>
        </div>

        {{-- 💡 مصاريف تكييفات إضافية (نقل/تركيب/خامات) لعقود التقسيط --}}
        <div class="panel-pro" style="border-right:4px solid #6366f1;">
            <div class="panel-pro-head">
                <h5><i class="fa fa-screwdriver-wrench text-primary"></i> مصاريف التكييفات الإضافية - عقود التقسيط</h5>
                <small class="text-muted">{{ $inst['acExtras']->operations }} عقد شمل هذه المصاريف</small>
            </div>
            <div class="kpi-grid cols-4">
                <div class="kpi-card" style="background:#eef2ff; border-color:#818cf8;">
                    <div class="kpi-label"><i class="fa fa-truck text-primary"></i> النقل</div>
                    <div class="kpi-value">{{ fmtMoney($inst['acExtras']->transport) }} <span class="kpi-unit">ج</span></div>
                </div>
                <div class="kpi-card" style="background:#eef2ff; border-color:#818cf8;">
                    <div class="kpi-label"><i class="fa fa-tools text-primary"></i> التركيب</div>
                    <div class="kpi-value">{{ fmtMoney($inst['acExtras']->installation) }} <span class="kpi-unit">ج</span></div>
                </div>
                <div class="kpi-card" style="background:#eef2ff; border-color:#818cf8;">
                    <div class="kpi-label"><i class="fa fa-cubes text-primary"></i> الخامات</div>
                    <div class="kpi-value">{{ fmtMoney($inst['acExtras']->materials) }} <span class="kpi-unit">ج</span></div>
                </div>
                <div class="kpi-card" style="background:#1e293b; color:#fff; border-color:#334155;">
                    <div class="kpi-label" style="color:#a5b4fc;"><i class="fa fa-calculator"></i> الإجمالي</div>
                    <div class="kpi-value" style="color:#fff;">{{ fmtMoney($inst['acExtras']->total) }} <span class="kpi-unit" style="color:#c7d2fe;">ج</span></div>
                </div>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-chart-area"></i> العقود الجديدة والمحصّل يومياً</h5></div>
            <div class="chart-box"><canvas id="instDailyChart"></canvas></div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-user-tie"></i> أكثر العملاء عقوداً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>العميل</th><th>عقود</th><th>قيمة</th><th>متبقي</th></tr></thead>
                            <tbody>
                                @forelse($inst['topCustomers'] as $i => $c)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $c['name'] }}</td>
                                    <td>{{ $c['count'] }}</td>
                                    <td>{{ fmtMoney($c['value']) }}</td>
                                    <td class="num-neg">{{ fmtMoney($c['remaining']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-mini"><i class="fa fa-users"></i>لا يوجد عملاء</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-cubes"></i> أكثر المنتجات تقسيطاً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>المنتج</th><th>عقود</th><th>قيمة</th><th>ربح</th></tr></thead>
                            <tbody>
                                @forelse($inst['topProducts'] as $i => $p)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ $p['count'] }}</td>
                                    <td>{{ fmtMoney($p['value']) }}</td>
                                    <td class="num-pos">{{ fmtMoney($p['profit']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-mini"><i class="fa fa-cubes"></i>لا توجد منتجات</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-triangle-exclamation"></i> قائمة المتأخرات الحالية</h5></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>العميل</th><th>المنتج</th><th>قسط</th><th>متبقي</th><th>آخر دفعة</th></tr></thead>
                    <tbody>
                        @forelse($inst['overdue']->take(20) as $o)
                        <tr>
                            <td>{{ $o->customer_name }}</td>
                            <td>{{ $o->product_name }}</td>
                            <td>{{ fmtMoney($o->monthly_installment) }}</td>
                            <td class="num-neg">{{ fmtMoney($o->remaining_balance) }}</td>
                            <td>{{ $o->last_payment ? \Carbon\Carbon::parse($o->last_payment)->diffForHumans() : 'لم يدفع بعد' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5"><div class="empty-mini"><i class="fa fa-circle-check"></i>كل العملاء منتظمين 🎉</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- ⛽ تاب البنزينة --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'gas')
        <div class="kpi-grid cols-4">
            <div class="kpi-card accent">
                <div class="kpi-label"><i class="fa fa-gas-pump"></i> عمليات الفترة</div>
                <div class="kpi-value">{{ $gas['opsCount'] }} <span class="kpi-unit">عملية</span></div>
                <div class="kpi-sub">{{ fmtMoney($gas['totalLiters']) }} لتر</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-arrow-up-from-bracket"></i> مبالغ للمحطات</div>
                <div class="kpi-value">{{ fmtMoney($gas['totalToStation']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">عهد نقدية: {{ fmtMoney($gas['totalAdvances']) }} ج</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-arrow-down-to-bracket"></i> مديونية الشركات</div>
                <div class="kpi-value">{{ fmtMoney($gas['totalOnCompany']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">مستحقات بالفترة على شركات النقل</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-coins"></i> صافي العمولة</div>
                <div class="kpi-value">{{ fmtMoney($gas['netProfit']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">متوسط/عملية: {{ number_format($gas['avgProfit'], 1) }} ج</div>
            </div>
        </div>

        <div class="kpi-grid cols-3">
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-hand-holding-dollar"></i> مستحقات لنا (الكل)</div>
                <div class="kpi-value">{{ fmtMoney($gas['gasReceivables']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">على شركات النقل (تراكمي)</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-money-bill-wave"></i> مديونيات للمحطات</div>
                <div class="kpi-value">{{ fmtMoney($gas['gasPayablesStations']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">رصيد وقود للمحطات</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-receipt"></i> استقطاعات</div>
                <div class="kpi-value">{{ fmtMoney($gas['gasPayablesDeductions']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">استقطاعات معلقة</div>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-chart-area"></i> اللترات والربح اليومي</h5></div>
            <div class="chart-box"><canvas id="gasDailyChart"></canvas></div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-building"></i> أكثر شركات النقل</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>الشركة</th><th>عمليات</th><th>لترات</th><th>مديونية</th><th>ربحنا</th></tr></thead>
                            <tbody>
                                @forelse($gas['topCompanies'] as $i => $c)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $c['name'] }}</td>
                                    <td>{{ $c['count'] }}</td>
                                    <td>{{ fmtMoney($c['liters']) }}</td>
                                    <td>{{ fmtMoney($c['on_them']) }}</td>
                                    <td class="num-pos">{{ fmtMoney($c['profit']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6"><div class="empty-mini"><i class="fa fa-building"></i>لا توجد شركات</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-gas-pump"></i> أكثر المحطات استخداماً</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>المحطة</th><th>عمليات</th><th>لترات</th><th>مدفوع</th></tr></thead>
                            <tbody>
                                @forelse($gas['topStations'] as $i => $s)
                                <tr>
                                    <td><span class="rank">{{ $i+1 }}</span></td>
                                    <td>{{ $s['name'] }}</td>
                                    <td>{{ $s['count'] }}</td>
                                    <td>{{ fmtMoney($s['liters']) }}</td>
                                    <td>{{ fmtMoney($s['paid']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5"><div class="empty-mini"><i class="fa fa-gas-pump"></i>لا توجد محطات</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-id-card"></i> أكثر السائقين/العربيات</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>السائق</th><th>عمليات</th><th>لترات</th></tr></thead>
                            <tbody>
                                @forelse($gas['topDrivers'] as $d)
                                <tr>
                                    <td>{{ $d['name'] }}</td>
                                    <td>{{ $d['count'] }}</td>
                                    <td>{{ fmtMoney($d['liters']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3"><div class="empty-mini"><i class="fa fa-user"></i>لا يوجد سائقين</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-droplet"></i> أنواع الوقود</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>النوع</th><th>عمليات</th><th>لترات</th><th>قيمة</th></tr></thead>
                            <tbody>
                                @forelse($gas['topFuelTypes'] as $f)
                                <tr>
                                    <td>{{ $f['name'] }}</td>
                                    <td>{{ $f['count'] }}</td>
                                    <td>{{ fmtMoney($f['liters']) }}</td>
                                    <td>{{ fmtMoney($f['value']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4"><div class="empty-mini"><i class="fa fa-droplet"></i>لا يوجد أنواع</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- 💰 تاب الحركة المالية --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @elseif($tab === 'fin')
        <div class="kpi-grid cols-4">
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-arrow-down"></i> إجمالي الإيرادات</div>
                <div class="kpi-value">{{ fmtMoney($fin['totalIncomes']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">تسويات: {{ fmtMoney($fin['totalSettlements']) }} ج</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-arrow-up"></i> إجمالي المصروفات</div>
                <div class="kpi-value">{{ fmtMoney($fin['totalExpenses']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">رواتب + خصومات + عمليات</div>
            </div>
            <div class="kpi-card {{ $fin['netCashFlow'] >= 0 ? 'success' : 'danger' }}">
                <div class="kpi-label"><i class="fa fa-balance-scale"></i> صافي التدفق النقدي</div>
                <div class="kpi-value">{{ fmtMoney($fin['netCashFlow']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">إيرادات − مصروفات</div>
            </div>
            <div class="kpi-card {{ $fin['capitalDiff'] >= 0 ? 'accent' : 'warning' }}">
                <div class="kpi-label"><i class="fa fa-chart-line"></i> نمو رأس المال</div>
                <div class="kpi-value">{{ $fin['capitalDiff'] >= 0 ? '+' : '' }}{{ fmtMoney($fin['capitalDiff']) }}</div>
                <div class="kpi-sub">{{ $fin['capitalPct'] }}% · من {{ fmtMoney($fin['capitalStart']) }} إلى {{ fmtMoney($fin['capitalEnd']) }}</div>
            </div>
        </div>

        <div class="kpi-grid cols-4">
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-user-tie"></i> الرواتب</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['salaries']) }} ج</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-percentage"></i> العمولات</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['commissions']) }} ج</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-gift"></i> خصومات للعملاء</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['discounts']) }} ج</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-trash"></i> إعدامات ديون</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['badDebts']) }} ج</div>
            </div>
        </div>

        <div class="kpi-grid cols-3">
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-wallet"></i> السيولة الحالية</div>
                <div class="kpi-value">{{ fmtMoney($fin['totalLiquidity']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">{{ $fin['accounts']->count() }} حساب (خزن + محافظ)</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-hand-holding-dollar"></i> ديون لنا (السوق)</div>
                <div class="kpi-value">{{ fmtMoney($fin['debtsForUs']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">أقساط + بيع آجل (بدون بنزينة)</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-handshake"></i> ديون علينا</div>
                <div class="kpi-value">{{ fmtMoney($fin['debtsOnUs']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">للموردين (بدون وقود)</div>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-chart-area"></i> الإيرادات والمصروفات يومياً</h5></div>
            <div class="chart-box"><canvas id="finDailyChart"></canvas></div>
        </div>

        {{-- ── شارت السنابات مع فلتر مستقل ── --}}
        <div class="panel-pro">
            <div class="panel-pro-head" style="flex-wrap:wrap; gap:8px;">
                <h5 style="margin:0;"><i class="fa fa-chart-line"></i> نمو رأس المال (لقطات)</h5>
                <form method="GET" action="{{ request()->url() }}" class="d-flex gap-2 align-items-center flex-wrap ms-auto" style="font-size:.8rem;">
                    <input type="hidden" name="tab" value="fin">
                    <input type="hidden" name="date_filter" value="{{ $dateFilter }}">
                    @if($customFrom) <input type="hidden" name="custom_from" value="{{ $customFrom }}"> @endif
                    @if($customTo)   <input type="hidden" name="custom_to"   value="{{ $customTo }}"> @endif
                    <select name="snap_period" class="form-select form-select-sm fw-bold" style="width:150px;" onchange="this.form.submit()">
                        <option value="1month"  {{ ($snapPeriod??'3months') === '1month'  ? 'selected' : '' }}>آخر شهر</option>
                        <option value="3months" {{ ($snapPeriod??'3months') === '3months' ? 'selected' : '' }}>آخر 3 شهور</option>
                        <option value="6months" {{ ($snapPeriod??'3months') === '6months' ? 'selected' : '' }}>آخر 6 شهور</option>
                        <option value="year"    {{ ($snapPeriod??'3months') === 'year'    ? 'selected' : '' }}>آخر سنة</option>
                        <option value="custom"  {{ ($snapPeriod??'3months') === 'custom'  ? 'selected' : '' }}>نطاق مخصص</option>
                    </select>
                    @if(($snapPeriod??'3months') === 'custom')
                    <input type="date" name="snap_from" value="{{ $snapFrom ?? '' }}" class="form-control form-control-sm" style="width:120px;">
                    <input type="date" name="snap_to"   value="{{ $snapTo   ?? '' }}" class="form-control form-control-sm" style="width:120px;">
                    <button type="submit" class="btn btn-sm btn-dark px-2"><i class="fa fa-search"></i></button>
                    @endif
                    <span class="badge bg-secondary">{{ $capitalTrendFiltered->count() }} لقطة</span>
                </form>
            </div>
            @if($capitalTrendFiltered->count() > 1)
            <div class="chart-box"><canvas id="capitalTrendChartFiltered"></canvas></div>
            @else
            <div style="padding:30px; text-align:center; color:#9ca3af; font-size:.85rem;">
                <i class="fa fa-camera-retro fa-2x mb-2 d-block"></i>
                لا توجد لقطات في هذه الفترة — استخدم زر "حفظ لقطة للتقارير" في صفحة الخزينة
            </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-tags"></i> توزيع المصروفات بالتصنيف</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>التصنيف</th><th>عدد</th><th>قيمة</th></tr></thead>
                            <tbody>
                                @forelse($fin['expensesByCategory'] as $c)
                                <tr>
                                    <td>{{ $c['name'] }}</td>
                                    <td>{{ $c['count'] }}</td>
                                    <td class="num-neg">{{ fmtMoney($c['value']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3"><div class="empty-mini"><i class="fa fa-tags"></i>لا يوجد مصروفات</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel-pro">
                    <div class="panel-pro-head"><h5><i class="fa fa-user-gear"></i> مصاريف الموظفين</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>الموظف</th><th>عمليات</th><th>إجمالي</th></tr></thead>
                            <tbody>
                                @forelse($fin['byPerson'] as $p)
                                <tr>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ $p['count'] }}</td>
                                    <td>{{ fmtMoney($p['total']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3"><div class="empty-mini"><i class="fa fa-user"></i>لا توجد عمليات على موظفين</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-vault"></i> أرصدة الحسابات الحالية</h5></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>الحساب</th><th>التصنيف</th><th>الرصيد</th></tr></thead>
                    <tbody>
                        @foreach($fin['accounts'] as $acc)
                        <tr>
                            <td>{{ $acc->account_name }}</td>
                            <td><span class="badge-soft {{ $acc->category === 'bank_wallet' ? 'info' : 'warning' }}">{{ $acc->category === 'bank_wallet' ? 'محفظة بنكية' : 'خزنة نقدية' }}</span></td>
                            <td class="{{ $acc->balance >= 0 ? 'num-pos' : 'num-neg' }}">{{ fmtMoney($acc->balance) }} ج</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head"><h5><i class="fa fa-list"></i> آخر العمليات المالية</h5></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>التاريخ</th><th>النوع</th><th>الوصف</th><th>المبلغ</th></tr></thead>
                    <tbody>
                        @forelse($fin['recentTx'] as $t)
                        @php
                            $isIncome = in_array($t->type, ['income', 'settlement']);
                            $typeLabel = [
                                'income' => 'إيراد', 'settlement' => 'تسوية',
                                'general_expense' => 'مصروف عام', 'salary_expense' => 'راتب',
                                'discount' => 'خصم', 'transfer' => 'تحويل',
                            ][$t->type] ?? $t->type;
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($t->created_at)->format('Y/m/d') }}</td>
                            <td><span class="badge-soft {{ $isIncome ? 'success' : 'danger' }}">{{ $typeLabel }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($t->notes, 60) }}</td>
                            <td class="{{ $isIncome ? 'num-pos' : 'num-neg' }}">{{ $isIncome ? '+' : '−' }}{{ fmtMoney($t->amount) }} ج</td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-mini"><i class="fa fa-folder-open"></i>لا توجد عمليات</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const chartFont = "'IBM Plex Sans Arabic', 'Cairo', sans-serif";
const fmt = n => fmtMoney(n);

Chart.defaults.font.family = chartFont;
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#5a6478';

const baseOpts = {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { display: true, position: 'top', labels: { boxWidth: 12, boxHeight: 12, padding: 10, font: { size: 11 } } },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleColor: '#fff', bodyColor: '#fff',
            padding: 10, borderWidth: 0, cornerRadius: 8, displayColors: true,
            callbacks: { label: ctx => '  ' + ctx.dataset.label + ': ' + fmt(ctx.parsed.y) }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            border: { display: false },
            ticks: { padding: 8, callback: v => v >= 1000 ? (v/1000).toFixed(1) + 'k' : v },
            grid: { color: '#eef0f4', drawTicks: false }
        },
        x: {
            border: { display: false },
            ticks: { padding: 6 },
            grid: { display: false }
        }
    }
};

function makeGradient(ctx, h, color, alpha) {
    const g = ctx.createLinearGradient(0, 0, 0, h);
    g.addColorStop(0, color + alpha);
    g.addColorStop(1, color + '00');
    return g;
}

@if($tab === 'inventory')
(function(){
    const data = @json($inv['dailyTrend']);
    const ctx = document.getElementById('invDailyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                { label: 'قيمة المبيعات', data: data.map(d => d.value), borderColor:'#3a4fb8', backgroundColor: makeGradient(ctx, 280, '#3a4fb8', '40'), fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 2, pointHoverRadius: 5, yAxisID: 'y' },
                { label: 'عدد العمليات', data: data.map(d => d.count), borderColor:'#b8842d', backgroundColor: 'rgba(184,132,45,0.1)', fill: false, tension: 0.4, borderWidth: 2, pointRadius: 2, pointHoverRadius: 5, yAxisID: 'y1' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });
})();
@endif


@if($tab === 'services')
(function(){
    const data = @json($services['dailyTrend']);
    const ctx = document.getElementById('servicesDailyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                { label: 'إيرادات الخدمات', data: data.map(d => d.revenue), borderColor:'#0d7373', backgroundColor: makeGradient(ctx, 280, '#0d7373', '40'), fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 2, pointHoverRadius: 5, yAxisID: 'y' },
                { label: 'صافي ربح',       data: data.map(d => d.profit),  borderColor:'#2d8659', backgroundColor: 'rgba(45,134,89,0.1)', fill: false, tension: 0.4, borderWidth: 2, pointRadius: 2, pointHoverRadius: 5, yAxisID: 'y' },
                { label: 'عدد العمليات',    data: data.map(d => d.count),   borderColor:'#b67c1f', backgroundColor: 'rgba(182,124,31,0.1)', fill: false, tension: 0.4, borderWidth: 2, pointRadius: 2, pointHoverRadius: 5, yAxisID: 'y1' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });
})();
@endif

@if($tab === 'inst')
(function(){
    const data = @json($inst['dailyTrend']);
    const ctx = document.getElementById('instDailyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                { label: 'عقود جديدة', data: data.map(d => d.new), backgroundColor: '#3a4fb8', borderRadius: 4, maxBarThickness: 20, yAxisID: 'y1' },
                { label: 'محصّل (ج)',  data: data.map(d => d.paid), backgroundColor: '#2d8659', borderRadius: 4, maxBarThickness: 20, yAxisID: 'y' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });
})();
@endif

@if($tab === 'gas')
(function(){
    const data = @json($gas['dailyTrend']);
    const ctx = document.getElementById('gasDailyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                { label: 'لترات', data: data.map(d => d.liters), borderColor:'#1e5fa4', backgroundColor: makeGradient(ctx, 280, '#1e5fa4', '40'), fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 2, pointHoverRadius: 5, yAxisID: 'y' },
                { label: 'ربح (ج)', data: data.map(d => d.profit), borderColor:'#2d8659', backgroundColor: 'rgba(45,134,89,0.1)', fill: false, tension: 0.4, borderWidth: 2, pointRadius: 2, pointHoverRadius: 5, yAxisID: 'y1' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });
})();
@endif

@if($tab === 'fin')
(function(){
    const data = @json($fin['dailyTrend']);
    const ctx = document.getElementById('finDailyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                { label: 'إيرادات', data: data.map(d => d.income), backgroundColor: '#2d8659', borderRadius: 4, maxBarThickness: 20 },
                { label: 'مصروفات', data: data.map(d => d.expense), backgroundColor: '#b91c1c', borderRadius: 4, maxBarThickness: 20 }
            ]
        },
        options: baseOpts
    });
})();

@if($capitalTrendFiltered->count() > 1)
(function(){
    const data = @json($capitalTrendFiltered);
    const ctx = document.getElementById('capitalTrendChartFiltered').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                label: 'رأس المال',
                data: data.map(d => d.value),
                borderColor: '#3a4fb8',
                backgroundColor: makeGradient(ctx, 280, '#3a4fb8', '40'),
                fill: true, tension: 0.3, borderWidth: 3,
                pointBackgroundColor: '#fff', pointBorderColor: '#3a4fb8', pointBorderWidth: 2,
                pointRadius: 5, pointHoverRadius: 7
            }]
        },
        options: {
            ...baseOpts,
            plugins: {
                ...((baseOpts.plugins) || {}),
                tooltip: {
                    callbacks: {
                        afterLabel: function(ctx2) {
                            const note = data[ctx2.dataIndex]?.notes;
                            return note ? '📝 ' + note : '';
                        }
                    }
                }
            }
        }
    });
})();
@endif
@endif
</script>
</body>
</html>
