<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('partials.theme', ['accent' => 'navy'])

    <style>
        .welcome-strip {
            background: linear-gradient(135deg, var(--c-navy), var(--c-navy-soft));
            color: #fff;
            border-radius: var(--r-lg);
            padding: 22px 26px;
            margin-bottom: 22px;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 14px;
            position: relative; overflow: hidden;
        }
        .welcome-strip::after {
            content: ''; position: absolute;
            top: 0; left: 0; bottom: 0; width: 4px;
            background: var(--c-accent);
        }
        .welcome-strip h1 {
            color: #fff; margin: 0 0 4px;
            font-size: 1.35rem; font-weight: 600;
            letter-spacing: -0.01em;
        }
        .welcome-strip .meta { color: rgba(255,255,255,0.7); font-size: 0.86rem; font-weight: 400; }
        .welcome-strip .right-info {
            text-align: end;
            color: rgba(255,255,255,0.85);
            font-size: 0.84rem;
            font-weight: 400;
        }
        .welcome-strip .right-info strong { color: var(--c-accent); font-weight: 600; }

        .quick-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(155px, 1fr)); gap: 8px; }
        .quick-link-pro {
            background: var(--c-surface); border: 1px solid var(--c-border);
            border-radius: var(--r-md); padding: 14px 12px;
            text-align: center; text-decoration: none;
            color: var(--c-text); transition: var(--t-fast);
            font-size: 0.82rem; font-weight: 500;
        }
        .quick-link-pro:hover {
            border-color: var(--c-accent); color: var(--c-accent);
            transform: translateY(-1px); box-shadow: var(--shadow-sm);
        }
        .quick-link-pro i {
            display: block; font-size: 1.3rem; margin-bottom: 5px;
            color: var(--c-text-muted);
        }
        .quick-link-pro:hover i { color: var(--c-accent); }

        .item-row-pro {
            display: flex; justify-content: space-between; align-items: center;
            padding: 11px 13px; border-radius: var(--r-md);
            margin-bottom: 6px; background: var(--c-surface-2);
            border: 1px solid var(--c-border);
            transition: var(--t-fast); text-decoration: none; color: var(--c-text);
        }
        .item-row-pro:hover {
            background: var(--c-navy-50); border-color: var(--c-accent);
            color: var(--c-text);
        }
        .item-row-pro .ir-title { font-weight: 500; font-size: 0.9rem; color: var(--c-navy); }
        .item-row-pro .ir-sub { font-size: 0.78rem; color: var(--c-text-muted); margin-top: 2px; }
        .item-row-pro .ir-value { font-weight: 600; font-size: 0.92rem; font-feature-settings: 'tnum'; white-space: nowrap; }
        .item-row-pro.danger  { border-right: 3px solid var(--c-danger); }
        .item-row-pro.warning { border-right: 3px solid var(--c-warning); }
        .item-row-pro.success { border-right: 3px solid var(--c-success); }

        .chart-container { position: relative; height: 280px; padding-top: 4px; }

        /* ── فلاتر الفترة ── */
        .period-filters {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            background: var(--c-surface); border: 1px solid var(--c-border);
            border-radius: var(--r-md); padding: 10px 14px; margin-bottom: 18px;
        }
        .period-filters .label {
            font-size: 0.82rem; color: var(--c-text-muted); font-weight: 500;
            margin-inline-end: 4px;
        }
        .period-filters .pill {
            border: 1px solid var(--c-border); background: var(--c-surface-2);
            color: var(--c-text); font-size: 0.82rem; font-weight: 500;
            padding: 6px 14px; border-radius: 999px;
            text-decoration: none; transition: var(--t-fast);
            cursor: pointer;
        }
        .period-filters .pill:hover {
            border-color: var(--c-accent); color: var(--c-accent);
        }
        .period-filters .pill.active {
            background: var(--c-navy); color: #fff; border-color: var(--c-navy);
        }
        .period-filters .custom-range {
            display: flex; align-items: center; gap: 6px; margin-inline-start: auto;
        }
        .period-filters .custom-range input {
            border: 1px solid var(--c-border); background: var(--c-surface-2);
            border-radius: var(--r-sm); padding: 5px 10px;
            font-size: 0.82rem; color: var(--c-text); font-family: inherit;
        }
        .period-filters .custom-range button {
            background: var(--c-accent); color: #fff; border: none;
            border-radius: var(--r-sm); padding: 6px 14px;
            font-size: 0.82rem; font-weight: 600; cursor: pointer;
            transition: var(--t-fast);
        }
        .period-filters .custom-range button:hover { filter: brightness(0.92); }
    </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    @php
        $hour = (int) now()->format('H');
        $greet = $hour < 12 ? 'صباح الخير' : ($hour < 17 ? 'يوم سعيد' : 'مساء الخير');
    @endphp
    <div class="welcome-strip">
        <div>
            <h1>{{ $greet }}، {{ $user->name ?? 'موظف' }}</h1>
            <div class="meta">ملخص النظام في لمحة سريعة</div>
        </div>
        <div class="right-info">
            <div><i class="fa-regular fa-calendar me-1"></i> {{ now()->locale('ar')->translatedFormat('l، j F Y') }}</div>
            <div><i class="fa-regular fa-clock me-1"></i> الساعة <strong>{{ now()->format('h:i A') }}</strong></div>
        </div>
    </div>

    {{-- فلاتر الفترة الزمنية --}}
    <form method="GET" action="{{ url('/dashboard') }}" id="periodForm" class="period-filters">
        <span class="label"><i class="fa-regular fa-calendar"></i> الفترة:</span>
        @php
            $filters = [
                'today'     => 'اليوم',
                'yesterday' => 'أمس',
                'week'      => 'هذا الأسبوع',
                'month'     => 'هذا الشهر',
            ];
        @endphp
        @foreach($filters as $key => $name)
            <a href="{{ url('/dashboard?filter='.$key) }}"
               class="pill {{ $filter === $key ? 'active' : '' }}">{{ $name }}</a>
        @endforeach

        <div class="custom-range">
            <input type="hidden" name="filter" value="custom">
            <input type="date" name="from_date" value="{{ $fromDate }}" max="{{ now()->format('Y-m-d') }}">
            <span style="color: var(--c-text-muted); font-size: 0.78rem;">إلى</span>
            <input type="date" name="to_date" value="{{ $toDate }}" max="{{ now()->format('Y-m-d') }}">
            <button type="submit"><i class="fa fa-filter"></i> تطبيق</button>
        </div>
    </form>

    {{-- KPIs الصف الأول --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro">
                <div class="label">رأس المال الحالي</div>
                <div class="value">{!! finMask(fmtMoney($summary['capital'])) !!}</div>
                <div class="unit">جنيه مصري</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro info">
                <div class="label">السيولة النقدية</div>
                <div class="value">{!! finMask(fmtMoney($summary['liquidity'])) !!}</div>
                <div class="unit">في الخزائن والمحافظ</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro">
                <div class="label">مبيعات {{ $filterLabel }}</div>
                <div class="value">{{ fmtMoney($periodSalesValue) }}</div>
                <div class="unit">{{ $periodSalesCount }} عملية</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro success">
                <div class="label">صافي ربح {{ $filterLabel }}</div>
                <div class="value">{!! finMask(fmtMoney($periodProfit)) !!}</div>
                <div class="unit">جنيه (مطابق للماليات)</div>
            </div>
        </div>
    </div>

    {{-- KPIs الصف الثاني (تنبيهات) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro danger">
                <div class="label">أقساط متأخرة</div>
                <div class="value">{{ $overdueCount }}</div>
                <div class="unit">{{ fmtMoney($overdueTotal) }} ج متأخر</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro warning">
                <div class="label">مستحق اليوم</div>
                <div class="value">{{ $dueToday }}</div>
                <div class="unit">قسط مستحق الدفع</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro info">
                <div class="label">استفسارات معلقة</div>
                <div class="value">{{ $pendingInquiries }}</div>
                <div class="unit">عميل في انتظار الرد</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro warning">
                <div class="label">مخزون قارب على النفاد</div>
                <div class="value">{{ count($lowStock) }}</div>
                <div class="unit">منتج (3 قطع أو أقل)</div>
            </div>
        </div>
    </div>

    {{-- 💡 مصاريف التكييفات الإضافية ({{ $filterLabel }}) — مجمّع من المخزن + التقسيط --}}
    @if($acExtras->total > 0)
    <div class="panel-pro" style="border-right:4px solid #f59e0b;">
        <div class="panel-pro-head">
            <h5><i class="fa fa-screwdriver-wrench text-warning"></i> مصاريف التكييفات الإضافية - {{ $filterLabel }}</h5>
            <small class="text-muted">مجمَّع من مبيعات المخزن + عقود التقسيط</small>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card-pro" style="background:#fff7ed; border-color:#fb923c;">
                    <div class="label"><i class="fa fa-truck text-warning me-1"></i> النقل</div>
                    <div class="value">{{ fmtMoney($acExtras->transport) }}</div>
                    <div class="unit">جنيه</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card-pro" style="background:#fff7ed; border-color:#fb923c;">
                    <div class="label"><i class="fa fa-tools text-warning me-1"></i> التركيب</div>
                    <div class="value">{{ fmtMoney($acExtras->installation) }}</div>
                    <div class="unit">جنيه</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card-pro" style="background:#fff7ed; border-color:#fb923c;">
                    <div class="label"><i class="fa fa-cubes text-warning me-1"></i> الخامات</div>
                    <div class="value">{{ fmtMoney($acExtras->materials) }}</div>
                    <div class="unit">جنيه</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card-pro" style="background:#1f2937; color:#fff; border-color:#374151;">
                    <div class="label" style="color:#fbbf24;"><i class="fa fa-calculator me-1"></i> الإجمالي</div>
                    <div class="value" style="color:#fff;">{{ fmtMoney($acExtras->total) }}</div>
                    <div class="unit" style="color:#fde68a;">جنيه</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- روابط سريعة --}}
    <div class="panel-pro">
        <div class="panel-pro-head">
            <h5><i class="fa-solid fa-bolt"></i> روابط سريعة</h5>
        </div>
        <div class="quick-row">
            <a class="quick-link-pro" href="{{ url('/sales') }}"><i class="fa fa-cash-register"></i>بيع جديد</a>
            <a class="quick-link-pro" href="{{ url('/installments') }}"><i class="fa fa-calendar-check"></i>عقد تقسيط</a>
            <a class="quick-link-pro" href="{{ url('/inquiries') }}"><i class="fa fa-phone-volume"></i>استفسار</a>
            <a class="quick-link-pro" href="{{ url('/financial-ops') }}"><i class="fa fa-file-invoice"></i>عملية مالية</a>
            <a class="quick-link-pro" href="{{ url('/gas-station') }}"><i class="fa fa-gas-pump"></i>بنزينة</a>
            <a class="quick-link-pro" href="{{ url('/expenses') }}"><i class="fa fa-receipt"></i>مصروف</a>
            <a class="quick-link-pro" href="{{ url('/reports') }}"><i class="fa fa-chart-column"></i>التقارير</a>
            <a class="quick-link-pro" href="{{ url('/treasury') }}"><i class="fa fa-vault"></i>الخزينة</a>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="panel-pro">
                <div class="panel-pro-head">
                    <h5><i class="fa-solid fa-chart-area"></i> مبيعات آخر 14 يوم</h5>
                </div>
                <div class="chart-container"><canvas id="salesChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-pro">
                <div class="panel-pro-head">
                    <h5><i class="fa-solid fa-chart-line"></i> صافي الأرباح آخر 14 يوم</h5>
                </div>
                <div class="chart-container"><canvas id="profitsChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Lists --}}
    <div class="row g-3">

        <div class="col-lg-6">
            <div class="panel-pro">
                <div class="panel-pro-head">
                    <h5><i class="fa-solid fa-triangle-exclamation"></i> أقساط تحتاج متابعة</h5>
                    <a href="{{ url('/installments') }}">عرض الكل ←</a>
                </div>
                @forelse($overdueInstallments->take(5) as $inst)
                    <a href="{{ url('/installments') }}" class="item-row-pro danger">
                        <div>
                            <div class="ir-title">{{ $inst->customer_name }}</div>
                            <div class="ir-sub">{{ $inst->product_name }}</div>
                        </div>
                        <div class="ir-value" style="color: var(--c-danger);">{{ fmtMoney($inst->remaining_balance) }} ج</div>
                    </a>
                @empty
                    <div class="empty-pro">
                        <i class="fa-regular fa-circle-check"></i>
                        <h5>كل العملاء منتظمين</h5>
                        <p>لا توجد أقساط متأخرة حالياً</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-pro">
                <div class="panel-pro-head">
                    <h5><i class="fa-solid fa-phone-volume"></i> استفسارات معلقة</h5>
                    <a href="{{ url('/inquiries') }}">عرض الكل ←</a>
                </div>
                @forelse($pendingInquiriesList as $inq)
                    <a href="{{ url('/inquiries') }}" class="item-row-pro warning">
                        <div>
                            <div class="ir-title">{{ $inq->customer_name }} <span class="muted-pro" style="font-weight:400; font-size:0.8rem;">({{ $inq->product_type ?? 'استفسار' }})</span></div>
                            <div class="ir-sub">{{ \Illuminate\Support\Str::limit($inq->inquiry, 70) }}</div>
                        </div>
                        <div class="ir-value muted-pro" style="font-size: 0.78rem;">
                            {{ \Carbon\Carbon::parse($inq->created_at)->diffForHumans() }}
                        </div>
                    </a>
                @empty
                    <div class="empty-pro">
                        <i class="fa-regular fa-circle-check"></i>
                        <h5>لا توجد استفسارات معلقة</h5>
                        <p>تم الرد على جميع الاستفسارات</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-pro">
                <div class="panel-pro-head">
                    <h5><i class="fa-solid fa-warehouse"></i> مخزون قارب على النفاد</h5>
                    <a href="{{ url('/inventory') }}">عرض الكل ←</a>
                </div>
                @forelse($lowStock as $item)
                    <a href="{{ url('/inventory') }}" class="item-row-pro warning">
                        <div>
                            <div class="ir-title">{{ $item->item_name ?? $item->product_name ?? 'منتج' }}</div>
                            <div class="ir-sub">المتبقي في المخزن</div>
                        </div>
                        <div class="ir-value" style="color: var(--c-warning);">{{ $item->remaining_quantity }} قطعة</div>
                    </a>
                @empty
                    <div class="empty-pro">
                        <i class="fa-regular fa-circle-check"></i>
                        <h5>المخزون بحالة جيدة</h5>
                        <p>كل المنتجات بكميات كافية</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel-pro">
                <div class="panel-pro-head">
                    <h5><i class="fa-solid fa-receipt"></i> آخر العمليات المالية</h5>
                    <a href="{{ url('/financial-ops') }}">عرض الكل ←</a>
                </div>
                @forelse($recentTransactions as $tx)
                    @php
                        $isIncome = in_array($tx->type, ['income', 'settlement']);
                        $color = $isIncome ? 'var(--c-success)' : 'var(--c-danger)';
                        $icon  = $isIncome ? 'fa-arrow-down' : 'fa-arrow-up';
                    @endphp
                    <div class="item-row-pro">
                        <div>
                            <div class="ir-title">{{ \Illuminate\Support\Str::limit($tx->notes ?? 'عملية بدون وصف', 65) }}</div>
                            <div class="ir-sub">{{ \Carbon\Carbon::parse($tx->created_at)->format('Y/m/d h:i A') }}</div>
                        </div>
                        <div class="ir-value" style="color: {{ $color }};">
                            <i class="fa {{ $icon }} me-1"></i>{{ fmtMoney($tx->amount) }} ج
                        </div>
                    </div>
                @empty
                    <div class="empty-pro">
                        <i class="fa-regular fa-folder-open"></i>
                        <h5>لا توجد عمليات حديثة</h5>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const salesData   = @json($salesChart);
const profitsData = @json($profitsChart);
const chartFont   = "'IBM Plex Sans Arabic', 'Cairo', sans-serif";

// تنسيق رقم بالعربي مع فاصلة
const fmt = n => fmtMoney(n);

// إعدادات عامة لكل الشارتات
Chart.defaults.font.family = chartFont;
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#5a6478';

const baseOpts = {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleColor: '#fff', bodyColor: '#fff',
            titleFont: { family: chartFont, size: 12, weight: '600' },
            bodyFont:  { family: chartFont, size: 12 },
            padding: 10, borderWidth: 0, cornerRadius: 8,
            displayColors: false,
            callbacks: {
                label: ctx => '  ' + fmt(ctx.parsed.y) + ' ج'
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            border: { display: false },
            ticks: {
                padding: 8, color: '#7a8294',
                callback: v => v >= 1000 ? (v/1000) + 'k' : v
            },
            grid: { color: '#eef0f4', drawTicks: false }
        },
        x: {
            border: { display: false },
            ticks: { padding: 6, color: '#7a8294' },
            grid: { display: false }
        }
    }
};

// ── شارت المبيعات (line مع gradient) ──
(function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.35)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(d => d.label),
            datasets: [{
                label: 'مبيعات (ج)',
                data: salesData.map(d => d.value),
                borderColor: '#3b82f6',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointHoverBorderWidth: 3,
            }]
        },
        options: baseOpts
    });
})();

// ── شارت الأرباح (bar ألوان حسب القيمة) ──
(function() {
    const ctx = document.getElementById('profitsChart').getContext('2d');
    const gradPos = ctx.createLinearGradient(0, 0, 0, 280);
    gradPos.addColorStop(0, '#34a874');
    gradPos.addColorStop(1, '#2d8659');

    const gradNeg = ctx.createLinearGradient(0, 0, 0, 280);
    gradNeg.addColorStop(0, '#dc2626');
    gradNeg.addColorStop(1, '#991b1b');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: profitsData.map(d => d.label),
            datasets: [{
                label: 'ربح (ج)',
                data: profitsData.map(d => d.value),
                backgroundColor: profitsData.map(d => d.value >= 0 ? gradPos : gradNeg),
                hoverBackgroundColor: profitsData.map(d => d.value >= 0 ? '#3eb87f' : '#ef4444'),
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 28,
            }]
        },
        options: baseOpts
    });
})();
</script>
</body>
</html>
