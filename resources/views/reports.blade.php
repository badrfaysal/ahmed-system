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
        .data-table tbody tr:hover td { background: var(--c-navy-50); }
        .data-table tfoot tr,
        .data-table tfoot td,
        .data-table tfoot tr:hover td {
            background-color: var(--c-navy, #0f172a) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-bottom: none !important;
        }
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
            <a href="{{ url('/reports/export?tab='.$tab.'&date_filter='.$dateFilter.($customFrom?'&custom_from='.$customFrom:'').($customTo?'&custom_to='.$customTo:'')) }}"
               target="_blank" rel="noopener"
               style="display:inline-block; margin-top:8px; background:#dc2626; color:#fff; padding:6px 14px; border-radius:8px; font-weight:600; font-size:0.85rem; text-decoration:none;"
               title="طباعة التاب الحالي فقط، بنفس الفلتر المطبّق">
                <i class="fa fa-file-pdf me-1"></i> طباعة هذا التاب (PDF)
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
            'all'       => 'كل الفترات',
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
            'inventory'   => ['icon' => 'fa-warehouse',          'name' => 'المخزن'],
            'services'    => ['icon' => 'fa-screwdriver-wrench', 'name' => 'الخدمات'],
            'inst'        => ['icon' => 'fa-file-signature',     'name' => 'الأقساط'],
            'gas'         => ['icon' => 'fa-gas-pump',           'name' => 'البنزينة'],
            'fin'         => ['icon' => 'fa-money-bill-trend-up','name' => 'الحركة المالية'],
            'ac'          => ['icon' => 'fa-snowflake',          'name' => 'صيانة التكييفات'],
            'contracting' => ['icon' => 'fa-trowel-bricks',      'name' => 'المقاولات والتشطيبات'],
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

        {{-- شارت المبيعات والشراء مع فلتر التجميع --}}
        <div class="panel-pro">
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5><i class="fa fa-chart-area text-primary"></i> حركة مبيعات المخزن</h5>
                <div class="btn-group btn-group-sm" role="group" id="granularity-btns-inv">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('inv', 'day', this)">باليوم</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('inv', 'week', this)">بالأسبوع</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('inv', 'month', this)">بالشهر</button>
                </div>
            </div>
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
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5><i class="fa fa-chart-area text-primary"></i> حركة الخدمات والإيرادات</h5>
                <div class="btn-group btn-group-sm" role="group" id="granularity-btns-services">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('services', 'day', this)">باليوم</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('services', 'week', this)">بالأسبوع</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('services', 'month', this)">بالشهر</button>
                </div>
            </div>
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
                <div class="kpi-sub">عدد عقود التقسيط اللي اتوقّعت في الفترة المختارة، بقيمة إجمالية {{ fmtMoney($inst['contractsValue']) }} ج</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-percent"></i> ربح النسبة</div>
                <div class="kpi-value">{{ fmtMoney($inst['interestProfit']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">ربح فايدة التقسيط بس (السعر بعد الفايدة − السعر كاش) لعقود الفترة، متوسط نسبة الفايدة {{ $inst['avgInterestPct'] }}%</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-box"></i> ربح المنتجات</div>
                <div class="kpi-value">{{ fmtMoney($inst['productProfit']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">ربح بيع الصنف نفسه (سعر البيع − سعر الشراء) لعقود الفترة. مع ربح النسبة فوق = إجمالي ربح عقود الفترة {{ fmtMoney($inst['totalContractProfit']) }} ج</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-money-bill"></i> المحصّل بالفترة</div>
                <div class="kpi-value">{{ fmtMoney($inst['paymentsValue']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">كل دفعات الأقساط اللي اتحصّلت في الفترة (من أي عقد قديم أو جديد) — {{ $inst['paymentsCount'] }} دفعة · خصومات مُنحت: {{ fmtMoney($inst['discountsGiven']) }} ج</div>
            </div>
        </div>

        <div class="kpi-grid cols-4">
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-calendar"></i> متوسط مدة العقد</div>
                <div class="kpi-value">{{ $inst['avgMonths'] }} <span class="kpi-unit">شهر</span></div>
                <div class="kpi-sub">متوسط عدد شهور تقسيط عقود الفترة، بمتوسط قيمة عقد {{ fmtMoney($inst['avgContractValue']) }} ج</div>
            </div>
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-hand-holding-dollar"></i> الدفعات المقدمة</div>
                <div class="kpi-value">{{ fmtMoney($inst['totalDownPayments']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">إجمالي المقدّم اللي اتدفع وقت توقيع عقود الفترة</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa fa-check-circle"></i> عقود مكتملة</div>
                <div class="kpi-value">{{ $inst['closedContracts'] }}</div>
                <div class="kpi-sub">عدد العقود اللي خلصت سداد بالكامل لحد النهاردة — رقم حالة حالية، مش مرتبط بفلتر الفترة فوق</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-circle-exclamation"></i> عقود معدومة</div>
                <div class="kpi-value">{{ $inst['writtenOffCount'] }}</div>
                <div class="kpi-sub">عقود اتشطبت كديون مش قابلة للتحصيل، بقيمة متبقية {{ fmtMoney($inst['writtenOffValue']) }} ج — رقم حالة حالية، مش مرتبط بفلتر الفترة</div>
            </div>
        </div>

        <div class="kpi-grid cols-2">
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-clock"></i> أقساط نشطة</div>
                <div class="kpi-value">{{ $inst['activeContracts'] }}</div>
                <div class="kpi-sub">كل العقود اللي لسه عليها مبالغ متبقية دلوقتي (بغض النظر عن الفترة المختارة)، بإجمالي مديونيات {{ fmtMoney($inst['totalOutstanding']) }} ج</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-triangle-exclamation"></i> متأخرات (35+ يوم)</div>
                <div class="kpi-value">{{ $inst['overdueCount'] }}</div>
                <div class="kpi-sub">عقود نشطة عدّى على آخر دفعة فيها (أو تاريخ التعاقد لو مفيش دفعات) أكتر من 35 يوم من النهاردة، بإجمالي متبقي {{ fmtMoney($inst['overdueValue']) }} ج — رقم حالة حالية، مش مرتبط بفلتر الفترة</div>
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
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5><i class="fa fa-chart-area text-primary"></i> العقود الجديدة والتحصيل</h5>
                <div class="btn-group btn-group-sm" role="group" id="granularity-btns-inst">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('inst', 'day', this)">باليوم</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('inst', 'week', this)">بالأسبوع</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('inst', 'month', this)">بالشهر</button>
                </div>
            </div>
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
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5><i class="fa fa-chart-area text-primary"></i> اللترات وأرباح الوقود</h5>
                <div class="btn-group btn-group-sm" role="group" id="granularity-btns-gas">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('gas', 'day', this)">باليوم</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('gas', 'week', this)">بالأسبوع</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('gas', 'month', this)">بالشهر</button>
                </div>
            </div>
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
                <div class="kpi-sub">رواتب + خصومات عملاء + مصروفات تشغيلية وعامة</div>
            </div>
            <div class="kpi-card {{ $fin['netCashFlow'] >= 0 ? 'success' : 'danger' }}">
                <div class="kpi-label"><i class="fa fa-balance-scale"></i> صافي التدفق النقدي</div>
                <div class="kpi-value">{{ fmtMoney($fin['netCashFlow']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">الفلوس اللي فضلت معاك بعد ما تدفع كل حاجة: لو موجب يبقى دخلك أكتر من مصروفك، ولو سالب يبقى صرفت أكتر مما دخل</div>
            </div>
            <div class="kpi-card {{ $fin['capitalDiff'] >= 0 ? 'accent' : 'warning' }}">
                <div class="kpi-label"><i class="fa fa-chart-line"></i> نمو رأس المال</div>
                <div class="kpi-value">{{ $fin['capitalDiff'] >= 0 ? '+' : '' }}{{ fmtMoney($fin['capitalDiff']) }}</div>
                <div class="kpi-sub">{{ $fin['capitalPct'] }}% · من {{ fmtMoney($fin['capitalStart']) }} إلى {{ fmtMoney($fin['capitalEnd']) }}</div>
            </div>
        </div>

        <div class="kpi-grid cols-2">
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-hand-holding-dollar"></i> التسويات</div>
                <div class="kpi-value">{{ fmtMoney($fin['totalSettlements']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">فلوس دخلت واتسجلت كدين علينا (زي إيداع مالك أو شريك) — مش إيراد فعلي من بيع أو خدمة، عشان كده مش داخلة في "إجمالي الإيرادات"</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-arrow-up-from-bracket"></i> إجمالي التدفقات الخارجة</div>
                <div class="kpi-value">{{ fmtMoney($fin['totalExpensesGross']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">كل فلوس خرجت من السيستم فعلياً، شاملة عُهد الموظفين وإعدامات الديون وإهلاك الأصول وخسارة فرق السعر (نفس رقم "حجم التدفقات الخارجة" في شاشة العمليات المالية) — أكبر من "إجمالي المصروفات" فوق لإنه مش بيستبعد حاجة</div>
            </div>
        </div>

        <div class="kpi-grid cols-4">
            <div class="kpi-card info">
                <div class="kpi-label"><i class="fa fa-user-tie"></i> الرواتب</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['salaries']) }} ج</div>
                <div class="kpi-sub">كل حركات نوع "راتب" في الفترة، داخلة أصلاً ضمن إجمالي المصروفات</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label"><i class="fa fa-percentage"></i> العمولات</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['commissions']) }} ج</div>
                <div class="kpi-sub">عمولات المحافظ (تلقائية أو مسجّلة يدوياً)، داخلة أصلاً ضمن إجمالي المصروفات</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-gift"></i> خصومات للعملاء</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['discounts']) }} ج</div>
                <div class="kpi-sub">داخلة أصلاً ضمن إجمالي المصروفات</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-trash"></i> إعدامات ديون</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['badDebts']) }} ج</div>
                <div class="kpi-sub">ديون اتشطبت بالكامل — مستبعدة من إجمالي المصروفات (بند مستقل)</div>
            </div>
        </div>

        <div class="kpi-grid cols-3">
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-clock-rotate-left"></i> إهلاك أصول ثابتة</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['depreciation']) }} ج</div>
                <div class="kpi-sub">مستبعد من إجمالي المصروفات (بند مستقل)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-arrow-trend-down"></i> خسارة فرق سعر</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['priceDiffLoss']) }} ج</div>
                <div class="kpi-sub">مستبعدة من إجمالي المصروفات (بند مستقل)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label"><i class="fa fa-people-carry-box"></i> عُهد الموظفين</div>
                <div class="kpi-value" style="font-size:1.2rem">{{ fmtMoney($fin['advancesTotal']) }} ج</div>
                <div class="kpi-sub">سُلف/عُهد لسه ملهاش تسوية — مستبعدة من إجمالي المصروفات، تفاصيلها في جدول "مصاريف الموظفين" تحت</div>
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
                <div class="kpi-sub">رقم مجمّع = أقساط + بيع آجل (بدون بنزينة). شاشة الخزينة بتعرض نفس الرقم مقسّم على كارتين منفصلين "منظومة الأقساط" و"مستحقات لنا"</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa fa-handshake"></i> ديون علينا</div>
                <div class="kpi-value">{{ fmtMoney($fin['debtsOnUs']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">للموردين وكل الالتزامات (بدون وقود)</div>
            </div>
        </div>

        {{-- 💰 تفصيل الأرباح حسب المصدر (نفس أرقام كل تاب بالظبط) --}}
        <div class="panel-pro">
            <div class="panel-pro-head">
                <h5><i class="fa fa-coins"></i> تفصيل الأرباح حسب المصدر</h5>
                <small class="text-muted">أرباح الفترة المختارة موزّعة على مصادرها</small>
            </div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>المصدر</th><th>الربح (ج.م)</th><th>النسبة من الإجمالي</th></tr></thead>
                    <tbody>
                        @php
                            $pb = $profitBreakdown;
                            $pbTotal = $pb['total'] ?: 1;
                            $pbRows = [
                                ['ربح النسبة  الأقساط)',        $pb['installmentInterest'], 'fa-percent'],
                                ['ربح منتجات الأقساط',          $pb['installmentProduct'],  'fa-box'],
                                ['ربح المخزن (بيع − شراء)',      $pb['inventory'],           'fa-warehouse'],
                                ['ربح الخدمات (صيانة/تركيب)',   $pb['services'],            'fa-screwdriver-wrench'],
                                ['ربح البنزينة (صافي العمولة)', $pb['gas'],                 'fa-gas-pump'],
                                ['ربح صيانة التكييفات',          $pb['ac'] ?? 0,             'fa-snowflake'],
                                ['إجمالي ربح المقاولات',         $pb['contracting'] ?? 0,    'fa-helmet-safety'],
                            ];
                        @endphp
                        @foreach($pbRows as $r)
                        <tr>
                            <td><i class="fa {{ $r[2] }} text-muted me-1"></i> {{ $r[0] }}</td>
                            <td class="num-pos">{{ fmtMoney($r[1]) }}</td>
                            <td>{{ number_format($pb['total'] != 0 ? ($r[1] / $pbTotal) * 100 : 0, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: var(--c-navy, #0f172a) !important; color: #ffffff !important;">
                            <td class="fw-bold" style="background-color: var(--c-navy, #0f172a) !important; color: #ffffff !important;">إجمالي الأرباح</td>
                            <td class="fw-bold" style="background-color: var(--c-navy, #0f172a) !important; color: #ffffff !important;">{{ fmtMoney($pb['total']) }} ج</td>
                            <td class="fw-bold" style="background-color: var(--c-navy, #0f172a) !important; color: #ffffff !important;">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="panel-pro">
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5><i class="fa fa-chart-area text-primary"></i> حركة الإيرادات والمصروفات</h5>
                <div class="btn-group btn-group-sm" role="group" id="granularity-btns-fin">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('fin', 'day', this)">باليوم</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('fin', 'week', this)">بالأسبوع</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('fin', 'month', this)">بالشهر</button>
                </div>
            </div>
            <div class="chart-box"><canvas id="finDailyChart"></canvas></div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             محاكي نمو رأس مال الشركة وتوقعات الأرباح (وفرض قيود وحظر السحب)
        ════════════════════════════════════════════════════════════ --}}
        <div class="panel-pro mb-4" style="border: 2px solid #cbd5e1; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 border-bottom mb-3">
                <div>
                    <h5 class="fw-black text-dark m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-chart-line text-success"></i> محاكي نمو الشركة وتوقعات رأس المال (سياسات تقييد وحظر سحب الشركاء)
                    </h5>
                    <small class="text-muted">
                        أداة استراتيجية لاتخاذ القرار: قياس النمو المركب لرأس المال وسيولة الشركة عند فرض حظر أو تقييد السحب على المساهمين
                    </small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="small fw-bold text-muted"><i class="fa fa-calendar me-1"></i>فترة التوقع:</span>
                    <div class="btn-group btn-group-sm" id="btnGroupCompanyTimeframe">
                        <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-1" onclick="switchCompanyProjectionPeriod('6M', this)">6 شهور</button>
                        <button type="button" class="btn btn-dark active fw-bold px-3 py-1" onclick="switchCompanyProjectionPeriod('1Y', this)">سنة كاملة</button>
                        <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-1" onclick="switchCompanyProjectionPeriod('2Y', this)">سنتين</button>
                        <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-1" onclick="switchCompanyProjectionPeriod('3Y', this)">3 سنوات</button>
                    </div>
                    <button type="button" class="btn btn-outline-dark btn-sm fw-bold px-2 py-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCompanyRates" title="تعديل نسب شهور السنة ديناميكياً">
                        <i class="fa fa-sliders text-warning me-1"></i> ضبط نسب الشهور
                    </button>
                </div>
            </div>

            {{-- شريط قرارات الإدارة والتحكم في حظر السحب --}}
            <div class="bg-light p-3 rounded-3 mb-3 border">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark mb-1"><i class="fa fa-ban text-danger me-1"></i>قرار حظر سحب الأرباح على المساهمين:</label>
                        <div class="btn-group btn-group-sm w-100" id="btnGroupFreezeMonths">
                            <button type="button" class="btn btn-outline-secondary fw-bold" onclick="setFreezeMonths(0, this)">بدون حظر (0)</button>
                            <button type="button" class="btn btn-success active fw-bold" onclick="setFreezeMonths(3, this)">حظر 3 شهور</button>
                            <button type="button" class="btn btn-outline-secondary fw-bold" onclick="setFreezeMonths(6, this)">حظر 6 شهور</button>
                            <button type="button" class="btn btn-outline-secondary fw-bold" onclick="setFreezeMonths(12, this)">سنة كاملة</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark mb-1"><i class="fa fa-rotate text-primary me-1"></i>تدوير الأرباح بعد انتهاء الحظر:</label>
                        <select class="form-select form-select-sm fw-bold" id="postFreezeReinvestSelect" onchange="updateCompanySimulation()">
                            <option value="1.0">تدوير كامل 100% (أعلى نمو تراكمي)</option>
                            <option value="0.7" selected>تدوير 70% وتوزيع 30% أرباح (متوازن)</option>
                            <option value="0.5">تدوير 50% وتوزيع 50%</option>
                            <option value="0.0">توزيع كامل الأرباح (رأس مال ثابت)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-dark mb-1"><i class="fa fa-vault text-warning me-1"></i>رأس المال الأساسي للمحاكاة (ج.م):</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="simCompanyBaseCap" class="form-control form-control-sm fw-bold" value="{{ (int)$totalCompanyCapital }}" onchange="updateCompanySimulation()">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="resetBaseCapital({{ (int)$totalCompanyCapital }})" title="استعادة رأس المال الفعلي">
                                <i class="fa fa-rotate-left"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- كروت النتائج والمقارنة للقرار الإداري --}}
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="p-3 rounded border h-100 d-flex flex-column justify-content-between" style="background-color: #f0fdf4; border-color: #86efac !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-success text-white fw-bold" style="font-size:0.75rem;"><i class="fa fa-shield-halved me-1"></i> سيناريو قرار الحظر المقترح</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-black font-monospace" id="comp_sim_pct_freeze">+0%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline py-1">
                            <span class="text-muted small fw-bold">رأس المال المتوقع:</span>
                            <strong class="text-success font-monospace" id="comp_sim_stat_freeze" style="font-size: 1.15rem;">0 ج</strong>
                        </div>
                        <div class="text-muted small" style="font-size:0.72rem;">
                            <i class="fa fa-check-circle me-1 text-success"></i>حظر سحب <span id="comp_sim_freeze_lbl" class="fw-bold text-dark">3 شهور</span> ثم تدوير بنسبة <span id="comp_sim_reinvest_lbl" class="fw-bold text-dark">70%</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded border h-100 d-flex flex-column justify-content-between" style="background-color: #f8fafc; border-color: #cbd5e1 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary text-white fw-bold" style="font-size:0.75rem;"><i class="fa fa-arrow-trend-flat me-1"></i> سيناريو الوضع الراهن (بدون حظر)</span>
                            <span class="badge bg-light text-secondary border fw-bold font-monospace" id="comp_sim_pct_nofreeze">+0%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline py-1">
                            <span class="text-muted small fw-bold">رأس المال بدون حظر:</span>
                            <strong class="text-secondary font-monospace" id="comp_sim_stat_nofreeze" style="font-size: 1.15rem;">0 ج</strong>
                        </div>
                        <div class="text-muted small" style="font-size:0.72rem;">
                            <i class="fa fa-info-circle me-1 text-muted"></i>استمرار السحب الشهري المفتوح للمساهمين
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded border h-100 d-flex flex-column justify-content-between" style="background-color: #fefce8; border-color: #fde047 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-warning text-dark fw-bold" style="font-size:0.75rem;"><i class="fa fa-coins me-1"></i> عائد قرار الحظر للشركة</span>
                            <span class="badge bg-success text-white fw-bold" style="font-size:0.75rem;">سيولة إضافية محمية</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline py-1">
                            <span class="text-muted small fw-bold">سيولة إضافية محققة:</span>
                            <strong class="text-primary font-monospace" id="comp_sim_gain_diff" style="font-size: 1.15rem;">+0 ج</strong>
                        </div>
                        <div class="text-muted small" style="font-size:0.72rem;">
                            <i class="fa fa-bolt me-1 text-warning"></i>فائض كاش يمكن توظيفه في مشاريع واستثمارات
                        </div>
                    </div>
                </div>
            </div>

            {{-- شارت المحاكاة والسيناريوهات --}}
            <div style="height: 330px; position: relative;">
                <canvas id="companyProjectionChart"></canvas>
            </div>
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
                        <option value="1month"  {{ ($snapPeriod??'6months') === '1month'  ? 'selected' : '' }}>آخر شهر</option>
                        <option value="3months" {{ ($snapPeriod??'6months') === '3months' ? 'selected' : '' }}>آخر 3 شهور</option>
                        <option value="6months" {{ ($snapPeriod??'6months') === '6months' ? 'selected' : '' }}>آخر 6 شهور (افتراضي)</option>
                        <option value="year"    {{ ($snapPeriod??'6months') === 'year'    ? 'selected' : '' }}>آخر سنة</option>
                        <option value="custom"  {{ ($snapPeriod??'6months') === 'custom'  ? 'selected' : '' }}>نطاق مخصص</option>
                    </select>
                    @if(($snapPeriod??'6months') === 'custom')
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
                    <div class="panel-pro-head"><h5><i class="fa fa-receipt"></i> مصروفات حسب الجهة <small class="text-muted fw-normal" style="font-size:.75rem;">(أعلى 10 أطراف خارجية)</small></h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>الجهة / الطرف</th><th>عمليات</th><th>إجمالي المصروف</th></tr></thead>
                            <tbody>
                                @forelse($fin['byPerson'] as $p)
                                <tr>
                                    <td>{{ $p['name'] }}</td>
                                    <td>{{ $p['count'] }}</td>
                                    <td>{{ fmtMoney($p['total']) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3"><div class="empty-mini"><i class="fa fa-receipt"></i>لا توجد مصروفات مسجلة بأطراف خارجية</div></td></tr>
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
    @elseif($tab === 'ac')
        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- ❄️ تاب التكييفات --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        @include('ac_reports_partial', [
            'reports' => $ac['reports'],
            'globalStats' => $ac['globalStats'],
            'topClients' => $ac['topClients'],
            'dailyTrend' => $ac['dailyTrend']
        ])
    @elseif($tab === 'contracting')
        {{-- ════════════════════════════════════════════════════════════ --}}
        {{-- 🏗️ تاب المقاولات والتشطيبات --}}
        {{-- ════════════════════════════════════════════════════════════ --}}
        <!-- 1. الكروت المالية الرئيسية للمقاولات -->
        <div class="kpi-grid cols-4">
            <div class="kpi-card success">
                <div class="kpi-label"><i class="fa-solid fa-sack-dollar text-success"></i> إجمالي ربح المقاولات</div>
                <div class="kpi-value {{ $contracting['grandProfit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ fmtMoney($contracting['grandProfit']) }} <span class="kpi-unit">ج</span>
                </div>
                <div class="kpi-sub">متوسط هامش الربح: <strong class="text-dark">{{ $contracting['avgMargin'] }}%</strong> · بعد خصم التكاليف والخصومات</div>
            </div>
            <div class="kpi-card accent">
                <div class="kpi-label"><i class="fa-solid fa-file-invoice-dollar text-primary"></i> صافي الإيرادات والمستخلصات</div>
                <div class="kpi-value text-primary">{{ fmtMoney($contracting['grandSelling']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">إجمالي المبيعات المفوترة للعملاء بعد الخصومات</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-label"><i class="fa-solid fa-money-bill-transfer text-danger"></i> إجمالي تكاليف ومصروفات المشاريع</div>
                <div class="kpi-value text-danger">{{ fmtMoney($contracting['grandCost']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">شاملة خامات، مصنعيات، فنيين، عمولات ونثريات</div>
            </div>
            <div class="kpi-card {{ $contracting['collectionRate'] >= 70 ? 'info' : 'warning' }}">
                <div class="kpi-label"><i class="fa-solid fa-hand-holding-dollar text-warning"></i> المحصل ومعدل التحصيل</div>
                <div class="kpi-value">{{ fmtMoney($contracting['grandPaid']) }} <span class="kpi-unit">ج</span></div>
                <div class="kpi-sub">نسبة التحصيل: <strong class="text-success">{{ $contracting['collectionRate'] }}%</strong> · المتبقي: {{ fmtMoney($contracting['grandRemaining']) }} ج</div>
            </div>
        </div>

        <!-- 2. بلوك مؤشرات ذكاء الأعمال وصنع القرار (المقاولات) -->
        <div class="panel-pro mb-4" style="border: 2px solid #e2e8f0; border-radius: 12px;">
            <div class="panel-pro-head bg-light">
                <h5><i class="fa fa-brain text-warning"></i> مؤشرات ذكاء الأعمال وصنع القرار (المقاولات)</h5>
                <small class="text-muted">تحليل استراتيجي لأهم الأنشطة والأطراف الأكثر تأثيراً على الربحية والتكاليف</small>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    <!-- 1. أكثر مشروع كسبت منه -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #10b981 !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-trophy text-success me-1"></i>أكثر مشروع كسبت منه:</div>
                            @if($contracting['topProfitProj'])
                                <div class="fw-black text-dark fs-6 text-truncate" title="{{ $contracting['topProfitProj']['project_name'] }}">{{ $contracting['topProfitProj']['project_name'] }}</div>
                                <div class="small text-muted mb-2">العميل: {{ $contracting['topProfitProj']['client_name'] }}</div>
                                <div class="badge bg-success-subtle text-success border border-success-subtle fw-bold font-monospace">
                                    ربح: +{{ fmtMoney($contracting['topProfitProj']['net_profit']) }} ج ({{ $contracting['topProfitProj']['margin_pct'] }}%)
                                </div>
                            @else
                                <div class="text-muted small">لا توجد بيانات مسجلة</div>
                            @endif
                        </div>
                    </div>

                    <!-- 2. أكثر مشروع صرفت فيه -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #ef4444 !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-fire-flame-curved text-danger me-1"></i>أكثر مشروع صرفت فيه:</div>
                            @if($contracting['topSpendingProj'])
                                <div class="fw-black text-dark fs-6 text-truncate" title="{{ $contracting['topSpendingProj']['project_name'] }}">{{ $contracting['topSpendingProj']['project_name'] }}</div>
                                <div class="small text-muted mb-2">العميل: {{ $contracting['topSpendingProj']['client_name'] }}</div>
                                <div class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold font-monospace">
                                    تكلفة: {{ fmtMoney($contracting['topSpendingProj']['total_cost']) }} ج
                                </div>
                            @else
                                <div class="text-muted small">لا توجد بيانات مسجلة</div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. أكثر مشروع أخد خصم -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #f59e0b !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-tags text-warning me-1"></i>أكثر مشروع أخد خصم:</div>
                            @if($contracting['topDiscountProj'])
                                <div class="fw-black text-dark fs-6 text-truncate" title="{{ $contracting['topDiscountProj']['project_name'] }}">{{ $contracting['topDiscountProj']['project_name'] }}</div>
                                <div class="small text-muted mb-2">العميل: {{ $contracting['topDiscountProj']['client_name'] }}</div>
                                <div class="badge bg-warning-subtle text-dark border border-warning-subtle fw-bold font-monospace">
                                    خصم: {{ fmtMoney($contracting['topDiscountProj']['discounts']) }} ج
                                </div>
                            @else
                                <div class="text-muted small">لا توجد خصومات بالفترة</div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. أكثر مسوق أخد عمولات -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #8b5cf6 !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-bullhorn text-purple me-1"></i>أكثر مسوق أخد عمولات:</div>
                            @if($contracting['topMarketer'])
                                <div class="fw-black text-dark fs-6 text-truncate">{{ $contracting['topMarketer']['name'] }}</div>
                                <div class="small text-muted mb-2">{{ $contracting['topMarketer']['ops_count'] }} عمليات وساطة</div>
                                <div class="badge bg-info-subtle text-dark border fw-bold font-monospace">
                                    عمولات: {{ fmtMoney($contracting['topMarketer']['total_commission']) }} ج
                                </div>
                            @else
                                <div class="text-muted small">لا توجد عمولات تسويق مسجلة</div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. أكثر بند صرفت فيه وكان تبع مشروع إيه -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #b91c1c !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-receipt text-danger me-1"></i>أكثر بند صرفت فيه:</div>
                            @if($contracting['topCostOp'])
                                <div class="fw-black text-dark fs-6 text-truncate" title="{{ $contracting['topCostOp']['title'] }}">{{ $contracting['topCostOp']['title'] }}</div>
                                <div class="small text-muted mb-2">المشروع: <strong class="text-primary">{{ $contracting['topCostOp']['project_name'] }}</strong></div>
                                <div class="badge bg-danger text-white fw-bold font-monospace">
                                    {{ fmtMoney($contracting['topCostOp']['cost']) }} ج
                                </div>
                            @else
                                <div class="text-muted small">لا توجد عمليات تكلفة</div>
                            @endif
                        </div>
                    </div>

                    <!-- 6. أكثر بند كسبت منه وكان تبع مشروع إيه -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #059669 !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-chart-line-up text-success me-1"></i>أكثر بند كسبت منه:</div>
                            @if($contracting['topProfitOp'])
                                <div class="fw-black text-dark fs-6 text-truncate" title="{{ $contracting['topProfitOp']['title'] }}">{{ $contracting['topProfitOp']['title'] }}</div>
                                <div class="small text-muted mb-2">المشروع: <strong class="text-primary">{{ $contracting['topProfitOp']['project_name'] }}</strong></div>
                                <div class="badge bg-success text-white fw-bold font-monospace">
                                    ربح: +{{ fmtMoney($contracting['topProfitOp']['profit']) }} ج
                                </div>
                            @else
                                <div class="text-muted small">لا توجد أرباح بنود مسجلة</div>
                            @endif
                        </div>
                    </div>

                    <!-- 7. أكثر فني اتعاملت معاه -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #3b82f6 !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-tools text-primary me-1"></i>أكثر فني اتعاملت معاه:</div>
                            @if($contracting['topTech'])
                                <div class="fw-black text-dark fs-6 text-truncate">{{ $contracting['topTech']['name'] }}</div>
                                <div class="small text-muted mb-2">{{ $contracting['topTech']['jobs_count'] }} مصنعيات منفذة</div>
                                <div class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold font-monospace">
                                    مستحقات: {{ fmtMoney($contracting['topTech']['total_cost']) }} ج
                                </div>
                            @else
                                <div class="text-muted small">لا توجد مصنعيات فنيين</div>
                            @endif
                        </div>
                    </div>

                    <!-- 8. أكثر خامة جبتها -->
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border bg-white h-100 shadow-sm" style="border-right: 4px solid #d97706 !important;">
                            <div class="small text-muted fw-bold mb-1"><i class="fa fa-cubes text-warning me-1"></i>أكثر خامة جبتها:</div>
                            @if($contracting['topMaterial'])
                                <div class="fw-black text-dark fs-6 text-truncate" title="{{ $contracting['topMaterial']['name'] }}">{{ $contracting['topMaterial']['name'] }}</div>
                                <div class="small text-muted mb-2">الكمية: {{ $contracting['topMaterial']['total_qty'] }} {{ $contracting['topMaterial']['unit'] }} ({{ $contracting['topMaterial']['count'] }} فواتير)</div>
                                <div class="badge bg-warning-subtle text-dark border border-warning-subtle fw-bold font-monospace">
                                    شراء: {{ fmtMoney($contracting['topMaterial']['total_cost']) }} ج
                                </div>
                            @else
                                <div class="text-muted small">لا توجد خامات مشتراة</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. شارتات المقاولات (مع فلتر التجميع باليوم / بالأسبوع / بالشهر) -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="panel-pro h-100">
                    <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5><i class="fa fa-chart-area text-primary"></i> حركة الإيرادات والمصروفات والأرباح بالمقاولات</h5>
                        <div class="btn-group btn-group-sm" role="group" id="granularity-btns-contracting">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('contracting', 'day', this)">باليوم</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('contracting', 'week', this)">بالأسبوع</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 fw-bold" onclick="switchChartGranularity('contracting', 'month', this)">بالشهر</button>
                        </div>
                    </div>
                    <div class="chart-box"><canvas id="contractingChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panel-pro h-100">
                    <div class="panel-pro-head">
                        <h5><i class="fa fa-chart-pie text-warning"></i> توزيع مصروفات المشاريع</h5>
                    </div>
                    <div class="chart-box"><canvas id="contractingExpensesChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- 4. جدول أداء مشاريع المقاولات المالي والتشغيلي -->
        <div class="panel-pro mb-4">
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5><i class="fa fa-building text-primary"></i> أداء مشاريع المقاولات المالية والتشغيلية</h5>
                <div style="width: 260px;">
                    <input type="text" class="form-control form-control-sm" placeholder="بحث سريع في المشاريع والعملاء..." oninput="filterContractingProjectsTable(this.value)">
                </div>
            </div>
            <div class="table-scroll">
                <table class="data-table" id="table-contracting-projects">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المشروع</th>
                            <th>العميل</th>
                            <th>نسبة الإشراف</th>
                            <th>إجمالي التكلفة</th>
                            <th>صافي الإيراد</th>
                            <th>ربح الإشراف</th>
                            <th>صافي الربح</th>
                            <th>هامش الربح</th>
                            <th>المحصل نقداً</th>
                            <th>المتبقي (مستحق)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracting['reportProjectMetrics'] as $pIdx => $p)
                            <tr>
                                <td><span class="rank">{{ $loop->iteration }}</span></td>
                                <td class="fw-bold text-dark">
                                    <a href="{{ url('/contracting') }}" class="text-decoration-none text-dark">
                                        <i class="fa fa-folder-open text-primary me-1"></i>{{ $p['project_name'] }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ $p['client_name'] }}</div>
                                    @if($p['client_phone'])
                                        <small class="text-muted font-monospace" style="font-size:0.75rem;">{{ $p['client_phone'] }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $p['supervision_pct'] }}%</span></td>
                                <td class="num-neg">{{ fmtMoney($p['total_cost']) }} ج</td>
                                <td class="fw-bold">{{ fmtMoney($p['net_selling']) }} ج</td>
                                <td class="text-primary font-monospace">{{ fmtMoney($p['supervision_profit']) }} ج</td>
                                <td class="{{ $p['net_profit'] >= 0 ? 'num-pos' : 'num-neg' }} fw-black font-monospace">
                                    {{ $p['net_profit'] >= 0 ? '+' : '' }}{{ fmtMoney($p['net_profit']) }} ج
                                </td>
                                <td>
                                    <span class="badge {{ $p['margin_pct'] >= 15 ? 'bg-success' : ($p['margin_pct'] > 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $p['margin_pct'] }}%
                                    </span>
                                </td>
                                <td class="num-pos">{{ fmtMoney($p['client_paid']) }} ج</td>
                                <td class="{{ $p['client_remaining'] > 0 ? 'num-neg fw-bold' : 'text-muted' }}">
                                    {{ fmtMoney($p['client_remaining']) }} ج
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11"><div class="empty-mini"><i class="fa fa-folder-open"></i>لا توجد مشاريع مسجلة في هذا النطاق الزمني</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. جدولان جانبيان: توزيع المصروفات + آخر العمليات المسجلة -->
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="panel-pro h-100">
                    <div class="panel-pro-head"><h5><i class="fa fa-layer-group text-primary"></i> توزيع المصروفات حسب التصنيف</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>التصنيف</th><th>عمليات</th><th>إجمالي التكلفة</th><th>النسبة</th></tr></thead>
                            <tbody>
                                @forelse($contracting['expensesByCategory'] as $exp)
                                    <tr>
                                        <td><b>{{ $exp['label'] }}</b></td>
                                        <td>{{ $exp['count'] }}</td>
                                        <td class="num-neg">{{ fmtMoney($exp['cost']) }} ج</td>
                                        <td><span class="badge bg-light text-dark border">{{ $exp['pct'] }}%</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><div class="empty-mini"><i class="fa fa-receipt"></i>لا توجد مصروفات مسجلة</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="panel-pro h-100">
                    <div class="panel-pro-head"><h5><i class="fa fa-list-check text-success"></i> آخر العمليات والبنود المسجلة بالمشاريع</h5></div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>التاريخ</th><th>المشروع</th><th>البند / العملية</th><th>التكلفة</th><th>البيع</th><th>الربح</th></tr></thead>
                            <tbody>
                                @forelse($contracting['recentOperations'] as $op)
                                    <tr>
                                        <td class="text-muted small font-monospace">{{ $op['date'] }}</td>
                                        <td class="fw-bold">{{ $op['project_name'] }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($op['title'], 35) }}</td>
                                        <td class="num-neg">{{ fmtMoney($op['cost']) }}</td>
                                        <td>{{ fmtMoney($op['selling']) }}</td>
                                        <td class="{{ $op['profit'] >= 0 ? 'num-pos' : 'num-neg' }} font-monospace fw-bold">
                                            {{ $op['profit'] >= 0 ? '+' : '' }}{{ fmtMoney($op['profit']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><div class="empty-mini"><i class="fa fa-folder-open"></i>لا توجد عمليات بالفترة</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- مودال ضبط وتعديل نسب الشهور لنمو ومحاكاة الشركة --}}
    <div class="modal fade" id="modalCompanyRates" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('partners.updateProjectionRates') }}" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="fa fa-sliders text-warning me-2"></i> ضبط وتعديل نسب الأرباح المتوقعة لشهور السنة</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="small text-muted mb-3">تُستخدم هذه النسب في حساب النمو المتوقع ومحاكاة حظر السحب لكافة أنشطة الشركة ومحافظ الشركاء.</p>
                    <div class="row g-2">
                        @foreach($seasonalMonthlyRates as $idx => $r)
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1">{{ $r['month'] }} (%):</label>
                                <input type="number" step="0.1" name="rates[{{ $idx }}]" class="form-control form-control-sm fw-bold text-center proj-rate-input" value="{{ $r['rate'] }}" required oninput="updateLiveModalRatesSum()">
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small fw-bold">
                        <span>مجموع النسب السنوية:</span>
                        <span class="badge bg-primary fs-6 font-monospace" id="modalLiveRatesSum">0%</span>
                    </div>
                </div>
                <div class="modal-footer p-2 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold"><i class="fa fa-save me-1"></i> حفظ وتحديث التوقعات</button>
                </div>
            </form>
        </div>
    </div>
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
        legend: { display: true, position: 'top', labels: { boxWidth: 12, boxHeight: 12, padding: 10, font: { size: 11, weight: 'bold' } } },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleColor: '#fff', bodyColor: '#fff',
            padding: 10, borderWidth: 0, cornerRadius: 8, displayColors: true,
            callbacks: { label: ctx => '  ' + ctx.dataset.label + ': ' + fmt(ctx.parsed.y) + ' ج' }
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
            ticks: { padding: 6, font: { weight: 'bold' } },
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

// ════════════════════════════════════════════════════════════
// ⏱️ Universal Time Granularity Aggregator (Day / Week / Month)
// ════════════════════════════════════════════════════════════
function aggregateTrendData(rawDailyData, granularity, keys) {
    if (!rawDailyData || rawDailyData.length === 0) return { labels: [], datasetsData: {} };

    if (granularity === 'day') {
        const labels = rawDailyData.map(d => {
            if (d.date && d.date.length >= 10) return d.date.substring(5);
            return d.label || '';
        });
        const datasetsData = {};
        keys.forEach(k => {
            datasetsData[k] = rawDailyData.map(d => Number(d[k] || 0));
        });
        return { labels, datasetsData };
    }

    if (granularity === 'month') {
        const monthNames = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        const groups = {};
        rawDailyData.forEach(d => {
            const dateStr = d.date || '';
            const key = dateStr.length >= 7 ? dateStr.substring(0, 7) : (d.label || 'عام');
            if (!groups[key]) {
                const parts = key.split('-');
                const mNum = parts[1] ? parseInt(parts[1], 10) : 0;
                const mName = monthNames[mNum] || key;
                const yr = parts[0] ? ` ${parts[0]}` : '';
                groups[key] = { label: `${mName}${yr}` };
                keys.forEach(k => groups[key][k] = 0);
            }
            keys.forEach(k => {
                groups[key][k] += Number(d[k] || 0);
            });
        });

        const labels = Object.values(groups).map(g => g.label);
        const datasetsData = {};
        keys.forEach(k => {
            datasetsData[k] = Object.values(groups).map(g => Math.round(g[k] * 100) / 100);
        });
        return { labels, datasetsData };
    }

    if (granularity === 'week') {
        const groups = {};
        rawDailyData.forEach((d, idx) => {
            const wNum = Math.floor(idx / 7) + 1;
            const key = `w_${wNum}`;
            if (!groups[key]) {
                const startLabel = d.date && d.date.length >= 10 ? d.date.substring(5) : (d.label || '');
                groups[key] = { label: `أسبوع ${wNum} (${startLabel})` };
                keys.forEach(k => groups[key][k] = 0);
            }
            keys.forEach(k => {
                groups[key][k] += Number(d[k] || 0);
            });
        });

        const labels = Object.values(groups).map(g => g.label);
        const datasetsData = {};
        keys.forEach(k => {
            datasetsData[k] = Object.values(groups).map(g => Math.round(g[k] * 100) / 100);
        });
        return { labels, datasetsData };
    }
}

// Global registry of chart instances and raw data
const activeTrendCharts = {};
const chartRawDataMap = {};
const chartKeysMap = {};

function switchChartGranularity(chartKey, granularity, btnEl) {
    if (btnEl) {
        const container = btnEl.closest('.btn-group');
        if (container) {
            container.querySelectorAll('button').forEach(b => {
                b.classList.remove('btn-dark', 'active');
                b.classList.add('btn-outline-secondary');
            });
            btnEl.classList.remove('btn-outline-secondary');
            btnEl.classList.add('btn-dark', 'active');
        }
    }

    const chart = activeTrendCharts[chartKey];
    const rawData = chartRawDataMap[chartKey];
    const keys = chartKeysMap[chartKey];
    if (!chart || !rawData || !keys) return;

    const aggregated = aggregateTrendData(rawData, granularity, keys);
    chart.data.labels = aggregated.labels;
    keys.forEach((k, idx) => {
        if (chart.data.datasets[idx]) {
            chart.data.datasets[idx].data = aggregated.datasetsData[k];
        }
    });
    chart.update();
}

// Smart default granularity based on report date filter
@php
    $isLongPeriod = in_array($dateFilter, ['year', 'all']) || ($startDate->diffInDays($endDate) > 60);
    $isMediumPeriod = ($startDate->diffInDays($endDate) > 31 && !$isLongPeriod);
    $initialGranularity = $isLongPeriod ? 'month' : ($isMediumPeriod ? 'week' : 'day');
@endphp
const defaultGranularity = '{{ $initialGranularity }}';

function highlightInitialGranularityButtons(chartKey, gran) {
    const btnGroup = document.getElementById(`granularity-btns-${chartKey}`);
    if (!btnGroup) return;
    btnGroup.querySelectorAll('button').forEach(btn => {
        btn.classList.remove('btn-dark', 'active');
        btn.classList.add('btn-outline-secondary');
        const txt = btn.textContent.trim();
        if (gran === 'month' && txt.includes('الشهر')) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-dark', 'active');
        } else if (gran === 'week' && txt.includes('الأسبوع')) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-dark', 'active');
        } else if (gran === 'day' && txt.includes('اليوم')) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-dark', 'active');
        }
    });
}

// ────────────────────────────────────────────────────────────
// 📦 1. Inventory Chart
// ────────────────────────────────────────────────────────────
@if($tab === 'inventory')
(function(){
    const rawData = @json($inv['dailyTrend']);
    const keys = ['value', 'count'];
    chartRawDataMap['inv'] = rawData;
    chartKeysMap['inv'] = keys;

    const ctx = document.getElementById('invDailyChart').getContext('2d');
    const agg = aggregateTrendData(rawData, defaultGranularity, keys);

    activeTrendCharts['inv'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: agg.labels,
            datasets: [
                { label: 'قيمة المبيعات (ج)', data: agg.datasetsData['value'], borderColor:'#3a4fb8', backgroundColor: makeGradient(ctx, 280, '#3a4fb8', '40'), fill: true, tension: 0.35, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 6, yAxisID: 'y' },
                { label: 'عدد العمليات', data: agg.datasetsData['count'], borderColor:'#b8842d', backgroundColor: 'rgba(184,132,45,0.1)', fill: false, tension: 0.35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, yAxisID: 'y1' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });

    highlightInitialGranularityButtons('inv', defaultGranularity);
})();
@endif

// ────────────────────────────────────────────────────────────
// 🔧 2. Services Chart
// ────────────────────────────────────────────────────────────
@if($tab === 'services')
(function(){
    const rawData = @json($services['dailyTrend']);
    const keys = ['revenue', 'profit', 'count'];
    chartRawDataMap['services'] = rawData;
    chartKeysMap['services'] = keys;

    const ctx = document.getElementById('servicesDailyChart').getContext('2d');
    const agg = aggregateTrendData(rawData, defaultGranularity, keys);

    activeTrendCharts['services'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: agg.labels,
            datasets: [
                { label: 'إيرادات الخدمات (ج)', data: agg.datasetsData['revenue'], borderColor:'#0d7373', backgroundColor: makeGradient(ctx, 280, '#0d7373', '40'), fill: true, tension: 0.35, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 6, yAxisID: 'y' },
                { label: 'صافي ربح (ج)', data: agg.datasetsData['profit'], borderColor:'#2d8659', backgroundColor: 'rgba(45,134,89,0.1)', fill: false, tension: 0.35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, yAxisID: 'y' },
                { label: 'عدد العمليات', data: agg.datasetsData['count'], borderColor:'#b67c1f', backgroundColor: 'rgba(182,124,31,0.1)', fill: false, tension: 0.35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, yAxisID: 'y1' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });

    highlightInitialGranularityButtons('services', defaultGranularity);
})();
@endif

// ────────────────────────────────────────────────────────────
// 📝 3. Installments Chart
// ────────────────────────────────────────────────────────────
@if($tab === 'inst')
(function(){
    const rawData = @json($inst['dailyTrend']);
    const keys = ['new', 'paid'];
    chartRawDataMap['inst'] = rawData;
    chartKeysMap['inst'] = keys;

    const ctx = document.getElementById('instDailyChart').getContext('2d');
    const agg = aggregateTrendData(rawData, defaultGranularity, keys);

    activeTrendCharts['inst'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: agg.labels,
            datasets: [
                { label: 'عقود جديدة (عدد)', data: agg.datasetsData['new'], backgroundColor: '#3a4fb8', borderRadius: 4, maxBarThickness: 28, yAxisID: 'y1' },
                { label: 'محصّل نقدياً (ج)',  data: agg.datasetsData['paid'], backgroundColor: '#2d8659', borderRadius: 4, maxBarThickness: 28, yAxisID: 'y' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });

    highlightInitialGranularityButtons('inst', defaultGranularity);
})();
@endif

// ────────────────────────────────────────────────────────────
// ⛽ 4. Gas Station Chart
// ────────────────────────────────────────────────────────────
@if($tab === 'gas')
(function(){
    const rawData = @json($gas['dailyTrend']);
    const keys = ['liters', 'profit'];
    chartRawDataMap['gas'] = rawData;
    chartKeysMap['gas'] = keys;

    const ctx = document.getElementById('gasDailyChart').getContext('2d');
    const agg = aggregateTrendData(rawData, defaultGranularity, keys);

    activeTrendCharts['gas'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: agg.labels,
            datasets: [
                { label: 'لترات الوقود', data: agg.datasetsData['liters'], borderColor:'#1e5fa4', backgroundColor: makeGradient(ctx, 280, '#1e5fa4', '40'), fill: true, tension: 0.35, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 6, yAxisID: 'y' },
                { label: 'صافي العمولة (ج)', data: agg.datasetsData['profit'], borderColor:'#2d8659', backgroundColor: 'rgba(45,134,89,0.1)', fill: false, tension: 0.35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, yAxisID: 'y1' }
            ]
        },
        options: {...baseOpts, scales: {
            y:  { ...baseOpts.scales.y, position: 'right' },
            y1: { ...baseOpts.scales.y, position: 'left', grid: { display: false } },
            x:  baseOpts.scales.x
        }}
    });

    highlightInitialGranularityButtons('gas', defaultGranularity);
})();
@endif

// ────────────────────────────────────────────────────────────
// 💰 5. Financial Tab Charts & Company Growth Simulator
// ────────────────────────────────────────────────────────────
@if($tab === 'fin')
(function(){
    const rawData = @json($fin['dailyTrend']);
    const keys = ['income', 'expense'];
    chartRawDataMap['fin'] = rawData;
    chartKeysMap['fin'] = keys;

    const ctx = document.getElementById('finDailyChart').getContext('2d');
    const agg = aggregateTrendData(rawData, defaultGranularity, keys);

    activeTrendCharts['fin'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: agg.labels,
            datasets: [
                { label: 'إيرادات (ج)', data: agg.datasetsData['income'], backgroundColor: '#2d8659', borderRadius: 4, maxBarThickness: 28 },
                { label: 'مصروفات (ج)', data: agg.datasetsData['expense'], backgroundColor: '#b91c1c', borderRadius: 4, maxBarThickness: 28 }
            ]
        },
        options: baseOpts
    });

    highlightInitialGranularityButtons('fin', defaultGranularity);
})();

// Capital Snapshots
@if($capitalTrendFiltered->count() > 1)
(function(){
    const data = @json($capitalTrendFiltered);
    const canvasEl = document.getElementById('capitalTrendChartFiltered');
    if (!canvasEl) return;
    const ctx = canvasEl.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                label: 'رأس المال (ج)',
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

// ════════════════════════════════════════════════════════════
// 🚀 Company-Wide Growth & Shareholder Restrictions Simulator
// ════════════════════════════════════════════════════════════
const seasonalMonthlyRates = {!! json_encode($seasonalMonthlyRates, JSON_UNESCAPED_UNICODE) !!};
let compSimulationChart = null;
let currentCompPeriod = '1Y';
let currentFreezeMonths = 3;

function getSimulationMonthCount(periodCode) {
    if (periodCode === '6M') return 6;
    if (periodCode === '2Y') return 24;
    if (periodCode === '3Y') return 36;
    return 12; // 1Y
}

function calculateCompanySimulation() {
    const baseCap = Math.max(10000, parseFloat(document.getElementById('simCompanyBaseCap')?.value) || {{ (float)$totalCompanyCapital }});
    const postFreezeReinvestRate = parseFloat(document.getElementById('postFreezeReinvestSelect')?.value) || 0.7;
    const monthCount = getSimulationMonthCount(currentCompPeriod);

    const labels = ['البداية'];
    const dataFreeze = [baseCap];
    const dataNoFreeze = [baseCap];
    const dataFullCompounding = [baseCap];

    let capFreeze = baseCap;
    let capNoFreeze = baseCap;
    let capFull = baseCap;

    const nowDate = new Date();
    const startMonthIdx = nowDate.getMonth(); // يبدأ من الشهر الحالي
    const startYear = nowDate.getFullYear();

    for (let i = 0; i < monthCount; i++) {
        const seasonIdx = (startMonthIdx + i) % 12;
        const yearVal = startYear + Math.floor((startMonthIdx + i) / 12);
        const rateObj = seasonalMonthlyRates[seasonIdx] || { month: '', rate: 2.0 };
        const grossRate = (parseFloat(rateObj.rate) || 2.0) / 100;
        
        labels.push(`${rateObj.month} ${yearVal}`);

        // 1. Full compounding benchmark (100% reinvestment all the time)
        const profitFull = capFull * grossRate;
        capFull += profitFull;
        dataFullCompounding.push(Math.round(capFull));

        // 2. Proposed Freeze Decision (months <= freezeMonths: 100% reinvested, afterwards: postFreezeReinvestRate)
        const profitFreeze = capFreeze * grossRate;
        const isFrozenMonth = (i < currentFreezeMonths);
        const reinvestRatio = isFrozenMonth ? 1.0 : postFreezeReinvestRate;
        capFreeze += (profitFreeze * reinvestRatio);
        dataFreeze.push(Math.round(capFreeze));

        // 3. Status Quo (Zero freeze from day 1, partners withdraw monthly profits)
        // Assume baseline retention is only 30% without restriction (70% withdrawn)
        const profitNoFreeze = capNoFreeze * grossRate;
        capNoFreeze += (profitNoFreeze * 0.30);
        dataNoFreeze.push(Math.round(capNoFreeze));
    }

    // Metrics calculations
    const pctFreeze = (((capFreeze - baseCap) / baseCap) * 100).toFixed(1);
    const pctNoFreeze = (((capNoFreeze - baseCap) / baseCap) * 100).toFixed(1);
    const addedLiquidity = Math.max(0, capFreeze - capNoFreeze);

    // Update KPI UI
    const elStatFreeze = document.getElementById('comp_sim_stat_freeze');
    const elPctFreeze  = document.getElementById('comp_sim_pct_freeze');
    const elStatNoFreeze = document.getElementById('comp_sim_stat_nofreeze');
    const elPctNoFreeze  = document.getElementById('comp_sim_pct_nofreeze');
    const elGainDiff   = document.getElementById('comp_sim_gain_diff');
    const elFreezeLbl  = document.getElementById('comp_sim_freeze_lbl');
    const elReinvestLbl= document.getElementById('comp_sim_reinvest_lbl');

    if (elStatFreeze)   elStatFreeze.innerText = Number(Math.round(capFreeze)).toLocaleString('en-US') + ' ج';
    if (elPctFreeze)    elPctFreeze.innerText  = '+' + pctFreeze + '% نمو';
    if (elStatNoFreeze) elStatNoFreeze.innerText = Number(Math.round(capNoFreeze)).toLocaleString('en-US') + ' ج';
    if (elPctNoFreeze)  elPctNoFreeze.innerText  = '+' + pctNoFreeze + '% نمو';
    if (elGainDiff)     elGainDiff.innerText   = '+' + Number(Math.round(addedLiquidity)).toLocaleString('en-US') + ' ج';

    if (elFreezeLbl)    elFreezeLbl.innerText  = currentFreezeMonths === 0 ? 'بدون حظر' : `${currentFreezeMonths} شهور`;
    if (elReinvestLbl)  elReinvestLbl.innerText = Math.round(postFreezeReinvestRate * 100) + '%';

    return { labels, dataFreeze, dataNoFreeze, dataFullCompounding };
}

function updateCompanySimulation() {
    const sim = calculateCompanySimulation();
    const canvasEl = document.getElementById('companyProjectionChart');
    if (!canvasEl) return;
    const ctx = canvasEl.getContext('2d');

    if (compSimulationChart) {
        compSimulationChart.data.labels = sim.labels;
        compSimulationChart.data.datasets[0].data = sim.dataFreeze;
        compSimulationChart.data.datasets[1].data = sim.dataNoFreeze;
        compSimulationChart.data.datasets[2].data = sim.dataFullCompounding;
        compSimulationChart.update();
        return;
    }

    compSimulationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: sim.labels,
            datasets: [
                {
                    label: 'سيناريو قرار الحظر المقترح (نمو محمي مركب)',
                    data: sim.dataFreeze,
                    borderColor: '#059669',
                    backgroundColor: makeGradient(ctx, 300, '#059669', '30'),
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#059669'
                },
                {
                    label: 'سيناريو استمرار السحب المفتوح (الوضع الراهن بدون حظر)',
                    data: sim.dataNoFreeze,
                    borderColor: '#64748b',
                    backgroundColor: 'rgba(100, 116, 139, 0.05)',
                    borderDash: [6, 6],
                    fill: false,
                    tension: 0.2,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#64748b'
                },
                {
                    label: 'الحد الأقصى النظري: تدوير 100% مستمر دون سحب',
                    data: sim.dataFullCompounding,
                    borderColor: '#d97706',
                    backgroundColor: 'transparent',
                    borderDash: [3, 3],
                    fill: false,
                    tension: 0.35,
                    borderWidth: 1.8,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#d97706'
                }
            ]
        },
        options: {
            ...baseOpts,
            plugins: {
                ...baseOpts.plugins,
                tooltip: {
                    ...baseOpts.plugins.tooltip,
                    callbacks: {
                        label: ctx => `  ${ctx.dataset.label}: ${Number(ctx.parsed.y).toLocaleString('en-US')} ج.م`
                    }
                }
            }
        }
    });
}

function switchCompanyProjectionPeriod(periodCode, btn) {
    currentCompPeriod = periodCode;
    if (btn) {
        document.querySelectorAll('#btnGroupCompanyTimeframe button').forEach(b => {
            b.classList.remove('btn-dark', 'active');
            b.classList.add('btn-outline-secondary');
        });
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-dark', 'active');
    }
    updateCompanySimulation();
}

function setFreezeMonths(months, btn) {
    currentFreezeMonths = Number(months);
    if (btn) {
        document.querySelectorAll('#btnGroupFreezeMonths button').forEach(b => {
            b.classList.remove('btn-success', 'active');
            b.classList.add('btn-outline-secondary');
        });
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success', 'active');
    }
    updateCompanySimulation();
}

function resetBaseCapital(defaultVal) {
    const inp = document.getElementById('simCompanyBaseCap');
    if (inp) {
        inp.value = defaultVal;
        updateCompanySimulation();
    }
}

// Initialize simulator when on fin tab
setTimeout(updateCompanySimulation, 60);

@endif

// ────────────────────────────────────────────────────────────
// 🏗️ 6. Contracting Tab Charts & Logic
// ────────────────────────────────────────────────────────────
@if($tab === 'contracting')
(function(){
    // 1. Contracting Trend Chart
    const rawData = @json($contracting['dailyTrend']);
    const keys = ['revenue', 'cost', 'profit'];
    chartRawDataMap['contracting'] = rawData;
    chartKeysMap['contracting'] = keys;

    const ctx = document.getElementById('contractingChart').getContext('2d');
    const agg = aggregateTrendData(rawData, defaultGranularity, keys);

    activeTrendCharts['contracting'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: agg.labels,
            datasets: [
                { label: 'إيرادات المشاريع (ج)', data: agg.datasetsData['revenue'], borderColor:'#2563eb', backgroundColor: makeGradient(ctx, 280, '#2563eb', '35'), fill: true, tension: 0.35, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 6 },
                { label: 'التكاليف والمصروفات (ج)', data: agg.datasetsData['cost'], borderColor:'#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.08)', fill: false, tension: 0.35, borderWidth: 2, pointRadius: 3, pointHoverRadius: 6 },
                { label: 'صافي الأرباح (ج)', data: agg.datasetsData['profit'], borderColor:'#059669', backgroundColor: 'rgba(5, 150, 105, 0.1)', fill: false, tension: 0.35, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 6 }
            ]
        },
        options: baseOpts
    });

    highlightInitialGranularityButtons('contracting', defaultGranularity);

    // 2. Contracting Expenses Doughnut Chart
    const expData = @json($contracting['expensesByCategory']);
    const ctxExp = document.getElementById('contractingExpensesChart').getContext('2d');
    new Chart(ctxExp, {
        type: 'doughnut',
        data: {
            labels: expData.map(e => e.label),
            datasets: [{
                data: expData.map(e => e.cost),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6',
                    '#ef4444', '#06b6d4', '#64748b', '#ec4899'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } },
                tooltip: {
                    callbacks: {
                        label: ctx => `  ${ctx.label}: ${Number(ctx.raw).toLocaleString('en-US')} ج.م`
                    }
                }
            }
        }
    });

    // 3. Filter Table
    window.filterContractingProjectsTable = function(searchTerm) {
        const q = (searchTerm || '').toLowerCase().trim();
        document.querySelectorAll('#table-contracting-projects tbody tr').forEach(tr => {
            if (tr.querySelector('td[colspan]')) return;
            const text = tr.textContent.toLowerCase();
            tr.style.display = text.includes(q) ? '' : 'none';
        });
    };
})();
@endif

// Live Modal Rate Sum Calculator
function updateLiveModalRatesSum() {
    let sum = 0;
    document.querySelectorAll('.proj-rate-input').forEach(input => {
        sum += parseFloat(input.value) || 0;
    });
    const elSum = document.getElementById('modalLiveRatesSum');
    if (elSum) elSum.innerText = sum.toFixed(1) + '%';
}
document.getElementById('modalCompanyRates')?.addEventListener('show.bs.modal', updateLiveModalRatesSum);

</script>
</body>
</html>
