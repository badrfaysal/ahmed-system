@php
    $companyName = trim((string) \App\Services\SystemSetting::get('invoice_header_text', ''));
    if ($companyName === '') {
        $companyName = 'شركة الضبع';
    }
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب الإدارة: {{ $headName }} - {{ $companyName }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'teal'])
    @include('partners._calm_theme')

    <style>
        .exec-share-pill {
            background: linear-gradient(145deg, #e2f3f2 0%, #eef6fa 100%);
            border: 1px solid rgba(13, 115, 115, 0.22);
            border-radius: var(--p-radius-md);
            padding: 10px 18px;
            text-align: center;
            min-width: 195px;
        }
        .exec-share-pill .lbl {
            display: block;
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--c-text-muted);
            margin-bottom: 2px;
        }
        .exec-share-pill .pct {
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--c-accent);
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
        }
        .reconcile-strip {
            margin-top: 18px;
            padding: 12px 16px;
            background: #f4f8fb;
            border: 1px solid #e1ebf3;
            border-radius: var(--p-radius-md);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px 18px;
            font-size: 0.79rem;
            color: var(--c-text-muted);
        }
        .reconcile-strip .rec-item strong {
            color: var(--c-navy);
            font-variant-numeric: tabular-nums;
        }
        .text-debit { color: var(--c-danger) !important; }

        /* شريط المراحل الزمني للتقفيلات السنوية */
        .stmt-timeline-wrap {
            padding: 15px 20px;
            background: #fafcfd;
            border-bottom: 1px solid var(--p-hairline);
        }
        .stmt-timeline-ribbon {
            display: flex;
            align-items: stretch;
            flex-wrap: wrap;
            gap: 10px;
        }
        .stmt-milestone-card {
            flex: 1 1 165px;
            min-width: 155px;
            border-radius: var(--p-radius-md);
            padding: 12px 14px;
            border: 1px solid rgba(13, 31, 53, 0.09);
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 5px;
        }
        .stmt-milestone-card.is-start {
            background: linear-gradient(155deg, #eef4fc 0%, #ffffff 82%);
            border-color: rgba(30, 95, 164, 0.24);
            border-top: 3px solid var(--c-info);
        }
        .stmt-milestone-card.is-year {
            background: linear-gradient(155deg, #ebf6f5 0%, #ffffff 82%);
            border-color: rgba(13, 115, 115, 0.24);
            border-top: 3px solid var(--c-accent);
        }
        .stmt-milestone-card.is-now {
            background: linear-gradient(155deg, #eaf7f0 0%, #ffffff 82%);
            border-color: rgba(45, 134, 89, 0.28);
            border-top: 3px solid var(--c-success);
        }
        .stmt-ms-top { display: flex; align-items: center; justify-content: space-between; gap: 6px; font-size: 0.72rem; font-weight: 700; color: var(--c-text-muted); }
        .stmt-ms-val { font-size: 1.05rem; font-weight: 700; color: var(--c-navy); font-variant-numeric: tabular-nums; line-height: 1.25; }
        .stmt-ms-foot { font-size: 0.72rem; color: var(--c-text-muted); font-variant-numeric: tabular-nums; }
        .stmt-timeline-arrow { display: flex; align-items: center; justify-content: center; color: var(--c-accent); font-size: 0.85rem; opacity: 0.65; padding: 0 2px; }
        @media (max-width: 767px) { .stmt-timeline-arrow { display: none; } }

        .print-only { display: none; }

        @media print {
            @page { size: A4 portrait; margin: 9mm 7mm 10mm 7mm; }
            *, *::before, *::after {
                box-sizing: border-box !important;
                overflow: visible !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
                box-shadow: none !important;
            }
            ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
            html, body {
                background: #ffffff !important;
                color: #0d1f35 !important;
                font-family: 'IBM Plex Sans Arabic', 'Cairo', sans-serif !important;
                font-size: 7.4pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop,
            .no-print, th.no-print, td.no-print, button, .btn, .prt-pill-btn, .prt-back-link,
            .prt-chart-card, canvas, .prt-panel-foot, .pagination, .prt-tabs-row {
                display: none !important;
            }
            .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; min-width: 100% !important; max-width: 100% !important; }
            .container-fluid { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            .print-only { display: block !important; }
            .print-doc-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 8px !important;
                border-bottom: 2px solid #0d1f35 !important;
                padding-bottom: 8px !important;
                margin-bottom: 10px !important;
            }
            .print-doc-header .hdr-col { flex: 1 1 0 !important; min-width: 0 !important; }
            .print-doc-header .hdr-center { flex: 1.35 1 0 !important; text-align: center !important; min-width: 0 !important; }
            .print-doc-header .hdr-meta-box {
                border: 1px solid #cbd5e1 !important;
                background: #f8fafc !important;
                border-radius: 4px !important;
                padding: 5px 8px !important;
                font-size: 6.8pt !important;
                text-align: right !important;
            }
            .prt-panel {
                border: 1px solid #cbd5e1 !important;
                border-radius: 4px !important;
                background: #ffffff !important;
                margin-bottom: 10px !important;
            }
            .prt-panel-head { background: #f8fafc !important; padding: 6px 10px !important; }
            .exec-kpi-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 6px !important;
                margin: 0 !important;
            }
            .exec-kpi-grid > [class*="col-"] { width: 100% !important; max-width: 100% !important; padding: 0 !important; }
            .prt-kpi-card { padding: 6px 7px !important; border: 1px solid #cbd5e1 !important; background: #ffffff !important; border-radius: 4px !important; }
            .prt-kpi-card .kpi-val { font-size: 9.5pt !important; }
            .prt-kpi-card .kpi-lbl { font-size: 6.6pt !important; }
            .prt-kpi-card .kpi-sub { font-size: 6pt !important; }
            .tab-content > .tab-pane:not(.active) { display: none !important; }
            .tab-content > .tab-pane.active { display: block !important; opacity: 1 !important; }
            .prt-table-wrap { overflow: visible !important; }
            .prt-table { width: 100% !important; border-collapse: collapse !important; font-size: 6.8pt !important; margin-bottom: 8px !important; }
            .prt-table thead { display: table-header-group !important; }
            .prt-table thead th {
                background-color: #0d1f35 !important;
                color: #ffffff !important;
                border: 1px solid #0d1f35 !important;
                padding: 4px 2.5px !important;
                font-size: 6.4pt !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .prt-table tbody td {
                border: 1px solid #cbd5e1 !important;
                padding: 3.5px 2.5px !important;
                font-size: 6.8pt !important;
                color: #0d1f35 !important;
                background: #ffffff !important;
            }
            .prt-table tfoot td {
                background-color: #f1f5f9 !important;
                border: 1px solid #94a3b8 !important;
                border-top: 2px solid #0d1f35 !important;
                padding: 4px 2.5px !important;
                font-size: 6.9pt !important;
            }
            .prt-table tr { page-break-inside: avoid !important; }
            .prt-chip { border: 1px solid #cbd5e1 !important; background: #f8fafc !important; color: #0d1f35 !important; font-size: 6.2pt !important; padding: 1px 4px !important; }
            .stmt-timeline-wrap { padding: 6px 10px !important; background: #ffffff !important; }
            .stmt-milestone-card { min-width: 110px !important; padding: 5px 8px !important; border: 1px solid #cbd5e1 !important; background: #f8fafc !important; border-radius: 4px !important; }
            .stmt-ms-top, .stmt-ms-foot { font-size: 6.2pt !important; }
            .stmt-ms-val { font-size: 8.6pt !important; }
            .print-sig-col { border: 1px solid #cbd5e1 !important; padding: 8px !important; text-align: center !important; background: #ffffff !important; border-radius: 4px !important; }
        }
    </style>
</head>
<body>

@include('sidebar')

@php
    $totalEarnedAll = $totalEarnedAll ?? round(($totalCommissions ?? 0) + ($totalOwnProfits ?? 0), 2);
    $totalPeriodWithdrawn = $totalPeriodWithdrawn ?? 0.0;
    $totalPeriodReinvested = $totalPeriodReinvested ?? 0.0;
    $directDeposits = $directDeposits ?? 0.0;
    $directWithdrawals = $directWithdrawals ?? 0.0;
    $totalCashWithdrawn = $totalCashWithdrawn ?? round($totalPeriodWithdrawn + $directWithdrawals, 2);
    $movements = $movements ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['pageName' => 'movements_page']);
    $allMovements = $allMovements ?? collect();
    $annualLines = $annualLines ?? collect();
    $headFoundingCap = round((float) ($headPartner->initial_capital ?? 0), 2);
    $headCumGrowth   = round((float) $ahmedCapital - $headFoundingCap, 2);
    $headCumPct      = $headFoundingCap > 0 ? round(($headCumGrowth / $headFoundingCap) * 100, 2) : null;
    $headAnnualProfits   = round((float) $annualLines->sum('year_net_profits'), 2);
    $headAnnualWithdrawn = round((float) $annualLines->sum('year_withdrawn'), 2);
    $historyCount = is_object($shareHistoryLines) && method_exists($shareHistoryLines, 'total')
        ? $shareHistoryLines->total()
        : (is_countable($shareHistoryLines ?? []) ? count($shareHistoryLines) : 0);

    $activeTab = request('tab');
    if (!$activeTab) {
        if (request()->has('movements_page') && !request()->has('periods_page') && !request()->has('share_history_page')) {
            $activeTab = 'movements';
        } elseif (request()->has('share_history_page') && !request()->has('periods_page') && !request()->has('movements_page')) {
            $activeTab = 'history';
        } else {
            $activeTab = 'periods';
        }
    }
    if (!in_array($activeTab, ['periods', 'movements', 'history'], true)) {
        $activeTab = 'periods';
    }
@endphp

<div class="main-content">
    <div class="container-fluid px-0">

        {{-- الترويسة المطبوعة الرسمية --}}
        <div class="print-only print-doc-header">
            <div class="hdr-col text-end">
                <div class="fw-bold" style="font-size:10pt; color:#0d1f35; white-space: pre-line;">{{ $companyName }}</div>
                <div class="text-muted" style="font-size:7pt;">وحدة الحسابات والرقابة المالية وإدارة الحصص</div>
            </div>
            <div class="hdr-center">
                <div class="fw-bold" style="font-size:12pt; color:#0d1f35;">كشف حساب الإدارة التنفيذية</div>
                <div class="fw-bold mt-1" style="font-size:9.5pt; color:#334155;">{{ $headName }}</div>
                <div style="font-size:7.2pt; color:#475569;">نسبة الحصة المعتمدة بالدورة الحالية: <strong>{{ number_format($ahmedSharePct, 2) }}%</strong></div>
            </div>
            <div class="hdr-col">
                <div class="hdr-meta-box">
                    <div><strong>الحالة:</strong> معتمد ومستمر</div>
                    <div><strong>تاريخ الطباعة:</strong> {{ date('Y-m-d H:i') }}</div>
                    <div><strong>عدد الدورات:</strong> {{ $periods->total() }} دورة</div>
                </div>
            </div>
        </div>

        {{-- شريط التحكم للشاشة --}}
        <div class="prt-header no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="prt-back-link">
                    <i class="fa fa-arrow-right fa-xs"></i>
                    <span>العودة إلى سجل الشركاء</span>
                </a>
                <div class="prt-title-row">
                    <span class="prt-title-badge"><i class="fa fa-briefcase"></i></span>
                    <h1 class="prt-title">كشف حساب الإدارة: {{ $headName }}</h1>
                    <span class="prt-chip is-head">حساب الإدارة الرئيسي</span>
                </div>
                <p class="prt-subtitle">بيان المركز المالي المعتمد وأرباح نسب الشغل والحركات المالية وتطور الحصة</p>
            </div>
            <div class="prt-actions align-self-end">
                <span class="prt-chip is-accent py-2 px-3">
                    الحصة المعتمدة: {{ number_format($ahmedSharePct, 2) }}%
                </span>
                <button type="button" class="prt-pill-btn is-primary" onclick="window.print()">
                    <i class="fa fa-print fa-xs"></i>
                    <span>طباعة كشف الحساب (A4)</span>
                </button>
            </div>
        </div>

        {{-- الملخص المالي الموحد --}}
        <div class="prt-panel">
            <div class="prt-panel-head">
                <div>
                    <h5 class="prt-panel-title">
                        <span class="prt-panel-title-ico"><i class="fa fa-chart-pie"></i></span>
                        <span>ملخص المركز المالي لحساب الإدارة ({{ $headName }})</span>
                    </h5>
                    <p class="prt-panel-sub">يشمل رصيد الإدارة المعتمد، أرباح نسب الشغل مع الشركاء، عائد تدوير الرصيد، وكافة المسحوبات والإيداعات.</p>
                </div>
                <div class="exec-share-pill">
                    <span class="lbl">نسبة الحصة المعتمدة بالدورة</span>
                    <span class="pct">{{ number_format($ahmedSharePct, 2) }}%</span>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3 exec-kpi-grid">
                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card is-teal">
                            <div>
                                <div class="prt-kpi-top">
                                    <div class="kpi-lbl">الرصيد الحالي المعتمد</div>
                                    <span class="prt-kpi-ico"><i class="fa fa-vault"></i></span>
                                </div>
                                <div class="kpi-val"><span dir="ltr">{{ number_format($ahmedCapital, 2) }}</span><span class="kpi-cur">ج</span></div>
                            </div>
                            <div class="kpi-sub">من إجمالي الحصص المعتمدة: {{ number_format($totalApprovedCapital, 2) }} ج</div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card is-blue">
                            <div>
                                <div class="prt-kpi-top">
                                    <div class="kpi-lbl">أرباح نسب الشغل (الإدارة)</div>
                                    <span class="prt-kpi-ico"><i class="fa fa-briefcase"></i></span>
                                </div>
                                <div class="kpi-val is-info"><span dir="ltr">{{ number_format($totalCommissions, 2) }}</span><span class="kpi-cur">ج</span></div>
                            </div>
                            <div class="kpi-sub">حصة الإدارة من أرباح الشركاء ({{ $periods->total() }} دورة)</div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card is-amber">
                            <div>
                                <div class="prt-kpi-top">
                                    <div class="kpi-lbl">عائد الرصيد المدور</div>
                                    <span class="prt-kpi-ico"><i class="fa fa-coins"></i></span>
                                </div>
                                <div class="kpi-val"><span dir="ltr">{{ number_format($totalOwnProfits, 2) }}</span><span class="kpi-cur">ج</span></div>
                            </div>
                            <div class="kpi-sub">الأرباح المحققة من رصيد الإدارة بالدورة</div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card is-emerald">
                            <div>
                                <div class="prt-kpi-top">
                                    <div class="kpi-lbl">إجمالي الأرباح المحققة</div>
                                    <span class="prt-kpi-ico"><i class="fa fa-calculator"></i></span>
                                </div>
                                <div class="kpi-val is-success"><span dir="ltr">{{ number_format($totalEarnedAll, 2) }}</span><span class="kpi-cur">ج</span></div>
                            </div>
                            <div class="kpi-sub">مدوّر: {{ number_format($totalPeriodReinvested, 2) }} ج · مسحوب: {{ number_format($totalCashWithdrawn, 2) }} ج</div>
                        </div>
                    </div>
                </div>

                <div class="reconcile-strip">
                    <div class="rec-item">رأس المال التأسيسي (ثابت): <strong>{{ number_format($headFoundingCap, 2) }} ج</strong></div>
                    <div class="rec-item">حصص الشركاء الحالية: <strong>{{ number_format($totalPartnersCapital, 2) }} ج</strong> ({{ number_format(max(0, 100 - $ahmedSharePct), 2) }}%)</div>
                    <div class="rec-item">إيداعات مباشرة بالرصيد: <strong style="color: var(--c-success);">{{ number_format($directDeposits, 2) }} ج</strong></div>
                    <div class="rec-item">مسحوبات مباشرة: <strong class="{{ $directWithdrawals > 0 ? 'text-debit' : '' }}">{{ number_format($directWithdrawals, 2) }} ج</strong></div>
                    <div class="rec-item">مصروف من أرباح الدورات: <strong class="{{ $totalPeriodWithdrawn > 0 ? 'text-debit' : '' }}">{{ number_format($totalPeriodWithdrawn, 2) }} ج</strong></div>
                    <div class="rec-item">إجمالي المسحوبات النقدية: <strong class="{{ $totalCashWithdrawn > 0 ? 'text-debit' : '' }}">{{ number_format($totalCashWithdrawn, 2) }} ج</strong></div>
                </div>
            </div>
        </div>

        {{-- قسم التقفيلات السنوية الرسمي لحساب الإدارة (يظهر دائماً على الشاشة وفي الطباعة) --}}
        @if($annualLines->isNotEmpty())
            <div class="prt-panel mb-4" id="ahmedAnnualSection">
                <div class="prt-panel-head">
                    <div>
                        <h5 class="prt-panel-title">
                            <span class="prt-panel-title-ico"><i class="fa fa-calendar-check"></i></span>
                            <span>بيان التقفيلات السنوية ونمو رصيد حساب الإدارة</span>
                        </h5>
                        <p class="prt-panel-sub">رصيد حساب الإدارة التأسيسي المحفوظ ({{ number_format($headFoundingCap, 2) }} ج) وتطور الرصيد في نهاية كل سنة مالية.</p>
                    </div>
                    <span class="prt-chip is-accent">{{ $annualLines->count() }} {{ $annualLines->count() === 1 ? 'سنة مقفلة' : 'سنوات مقفلة' }}</span>
                </div>

                <div class="stmt-timeline-wrap">
                    <div class="stmt-timeline-ribbon">
                        <div class="stmt-milestone-card is-start">
                            <div class="stmt-ms-top">
                                <span><i class="fa fa-lock fa-xs me-1" style="color: var(--c-info);"></i> التأسيسي (البداية)</span>
                                <span style="color: var(--c-info);">ثابت</span>
                            </div>
                            <div class="stmt-ms-val">{{ number_format($headFoundingCap, 2) }} <span class="small fw-normal text-muted">ج</span></div>
                            <div class="stmt-ms-foot">{{ $headFoundingCap == 0 ? 'تراكمي من نسب الشغل' : 'محفوظ منذ البداية' }}</div>
                        </div>

                        @foreach($annualLines as $al)
                            <div class="stmt-timeline-arrow" aria-hidden="true"><i class="fa fa-arrow-left"></i></div>
                            <div class="stmt-milestone-card is-year">
                                <div class="stmt-ms-top">
                                    <span><i class="fa fa-calendar-alt fa-xs me-1" style="color: var(--c-accent);"></i> إقفال سنة {{ $al->closing_year }}</span>
                                    <span style="color: var(--c-accent);">{{ number_format($al->closing_share_pct, 2) }}%</span>
                                </div>
                                <div class="stmt-ms-val">{{ number_format($al->closing_capital, 2) }} <span class="small fw-normal text-muted">ج</span></div>
                                <div class="stmt-ms-foot fw-semibold" style="color: {{ $al->annual_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $al->annual_gain >= 0 ? '+' : '' }}{{ number_format($al->annual_gain, 2) }} ج خلال السنة
                                </div>
                            </div>
                        @endforeach

                        <div class="stmt-timeline-arrow" aria-hidden="true"><i class="fa fa-arrow-left"></i></div>
                        <div class="stmt-milestone-card is-now">
                            <div class="stmt-ms-top">
                                <span><i class="fa fa-star fa-xs me-1" style="color: var(--c-success);"></i> الرصيد المعتمد الآن</span>
                                <span style="color: var(--c-success);">{{ number_format($ahmedSharePct, 2) }}%</span>
                            </div>
                            <div class="stmt-ms-val">{{ number_format($ahmedCapital, 2) }} <span class="small fw-normal text-muted">ج</span></div>
                            <div class="stmt-ms-foot fw-semibold" style="color: {{ $headCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                إجمالي النمو: {{ $headCumGrowth >= 0 ? '+' : '' }}{{ number_format($headCumGrowth, 2) }} ج
                            </div>
                        </div>
                    </div>
                </div>

                <div class="prt-table-wrap">
                    <table class="prt-table">
                        <thead>
                            <tr>
                                <th class="text-center">السنة المالية</th>
                                <th class="text-center th-soft-before"><i class="fa fa-lock fa-xs me-1"></i> التأسيسي</th>
                                <th class="text-center">بداية السنة</th>
                                <th class="text-center th-soft-after">نهاية السنة (بقا كام)</th>
                                <th class="text-center">الزيادة في السنة</th>
                                <th class="text-center">أرباح الدورات بالسنة</th>
                                <th class="text-center">المسحوب نقداً</th>
                                <th class="text-center">إجمالي النمو منذ البداية</th>
                                <th class="text-center">الحصة بنهاية السنة</th>
                                <th class="text-center no-print">تقرير السنة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($annualLines as $al)
                                <tr>
                                    <td class="text-center"><span class="prt-chip is-accent">سنة {{ $al->closing_year }}</span></td>
                                    <td class="text-center prt-num-main">{{ number_format($al->initial_capital, 2) }} ج</td>
                                    <td class="text-center text-muted">{{ number_format($al->opening_capital, 2) }} ج</td>
                                    <td class="text-center prt-num-main">{{ number_format($al->closing_capital, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="prt-chip {{ $al->annual_gain >= 0 ? 'is-success' : 'is-danger' }}">
                                            {{ $al->annual_gain >= 0 ? '+' : '' }}{{ number_format($al->annual_gain, 2) }} ج
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold" style="color: var(--c-info);">
                                        {{ (float) $al->year_net_profits != 0 ? (($al->year_net_profits > 0 ? '+' : '') . number_format($al->year_net_profits, 2) . ' ج') : '—' }}
                                    </td>
                                    <td class="text-center fw-semibold" style="color: {{ $al->year_withdrawn > 0 ? 'var(--c-danger)' : 'var(--c-text-soft)' }};">
                                        {{ $al->year_withdrawn > 0 ? '-' . number_format($al->year_withdrawn, 2) . ' ج' : '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="prt-chip {{ $al->cumulative_gain >= 0 ? 'is-success' : 'is-danger' }}">
                                            {{ $al->cumulative_gain >= 0 ? '+' : '' }}{{ number_format($al->cumulative_gain, 2) }} ج
                                        </span>
                                    </td>
                                    <td class="text-center"><span class="prt-chip is-info">{{ number_format($al->closing_share_pct, 2) }}%</span></td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('partners.annual.show', $al->closing_year) }}" class="prt-pill-btn is-ghost is-sm py-1 px-3">
                                            <i class="fa fa-print fa-xs"></i> التقرير
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-center">الوضع الحالي</td>
                                <td class="text-center">{{ number_format($headFoundingCap, 2) }} ج</td>
                                <td class="text-center text-muted">—</td>
                                <td class="text-center">{{ number_format($ahmedCapital, 2) }} ج</td>
                                <td class="text-center" style="color: {{ $headCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $headCumGrowth >= 0 ? '+' : '' }}{{ number_format($headCumGrowth, 2) }} ج
                                </td>
                                <td class="text-center" style="color: var(--c-info);">
                                    {{ $headAnnualProfits != 0 ? (($headAnnualProfits > 0 ? '+' : '') . number_format($headAnnualProfits, 2) . ' ج') : '—' }}
                                </td>
                                <td class="text-center" style="color: {{ $headAnnualWithdrawn > 0 ? 'var(--c-danger)' : 'var(--c-text-muted)' }};">
                                    {{ $headAnnualWithdrawn > 0 ? '-' . number_format($headAnnualWithdrawn, 2) . ' ج' : '—' }}
                                </td>
                                <td class="text-center" style="color: {{ $headCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $headCumGrowth >= 0 ? '+' : '' }}{{ number_format($headCumGrowth, 2) }} ج
                                </td>
                                <td class="text-center">{{ number_format($ahmedSharePct, 2) }}%</td>
                                <td class="no-print"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- الشارتات التحليلية --}}
        <div class="row g-3 mb-4 no-print">
            <div class="col-md-8">
                <div class="prt-chart-card">
                    <div class="prt-chart-head">
                        <h6><i class="fa fa-chart-area" style="color: var(--c-accent);"></i> تطور رصيد حساب الإدارة عبر الدورات الشهرية</h6>
                        <span class="prt-chip is-accent">تراكم الرصيد وتدوير الأرباح</span>
                    </div>
                    <div style="height: 255px;">
                        <canvas id="chartAhmedCapital"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="prt-chart-card">
                    <div class="prt-chart-head">
                        <h6><i class="fa fa-chart-pie" style="color: var(--c-info);"></i> هيكل الحصص المعتمدة بالدورة</h6>
                    </div>
                    <div style="height: 255px; position: relative;">
                        <canvas id="chartOwnershipDoughnut"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="prt-chart-card">
                    <div class="prt-chart-head">
                        <h6><i class="fa fa-chart-column" style="color: var(--c-success);"></i> تحليل دخل حساب الإدارة لكل دورة (نسب الشغل مقابل عائد الرصيد المدور)</h6>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="chartAhmedIncomeBreakdown"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- شريط التبويبات الكبسولي --}}
        <div class="prt-tabs-row no-print">
            <ul class="nav prt-tabs-capsule" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'periods' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAhmedPeriods" type="button" role="tab">
                        <i class="fa fa-list-alt fa-xs"></i>
                        <span>بيان الدورات الشهرية والأرباح</span>
                        <span class="prt-tab-count">{{ $periods->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'movements' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAhmedMovements" type="button" role="tab">
                        <i class="fa fa-exchange-alt fa-xs"></i>
                        <span>الحركات المالية المباشرة</span>
                        <span class="prt-tab-count">{{ $movements->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'history' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAhmedShareHistory" type="button" role="tab">
                        <i class="fa fa-history fa-xs"></i>
                        <span>سجل تغير النسب والحصص</span>
                        <span class="prt-tab-count">{{ $historyCount }}</span>
                    </button>
                </li>
            </ul>
        </div>

        {{-- الجداول المحاسبية التفصيلية --}}
        <div class="prt-panel">
            <div class="tab-content">
                {{-- التاب 1: بيان الدورات الشهرية والأرباح --}}
                <div class="tab-pane fade {{ $activeTab === 'periods' ? 'show active' : '' }}" id="tabAhmedPeriods" role="tabpanel">
                    <div class="prt-panel-head">
                        <h5 class="prt-panel-title">
                            <span class="prt-panel-title-ico"><i class="fa fa-list-alt"></i></span>
                            <span>بيان أرباح حساب الإدارة عبر الدورات الشهرية المعتمدة</span>
                        </h5>
                        <span class="prt-chip is-neutral">{{ $periods->total() }} دورة</span>
                    </div>
                    <div class="prt-table-wrap">
                        <table class="prt-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th>الدورة الشهرية</th>
                                    <th class="text-center">الرصيد قبل</th>
                                    <th class="text-center th-soft-before">النسبة قبل</th>
                                    <th class="text-center">ربح الرصيد المدور</th>
                                    <th class="text-center">أرباح نسب الشغل</th>
                                    <th class="text-center">إجمالي ربح الدورة</th>
                                    <th class="text-center">القرار / المسحوب</th>
                                    <th class="text-center">الرصيد بعد</th>
                                    <th class="text-center th-soft-after">النسبة بعد</th>
                                    <th class="text-center no-print">التقرير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periods as $per)
                                    @php
                                        $pAhmedCap     = (float) ($per->ahmed_opening_capital ?? 0.0);
                                        $pAhmedOpenPct = (float) ($per->ahmed_capital_share_pct ?? 0.0);
                                        $pOwnProf      = (float) ($per->ahmed_own_profit ?? 0.0);
                                        $pComm         = (float) ($per->total_ahmed_commission ?? 0.0);
                                        $pTotal        = (float) ($per->ahmed_total_profit ?: ($pOwnProf + $pComm));
                                        $pCloseCap     = (float) ($per->ahmed_closing_capital ?? 0.0);
                                        $pClosePct     = (float) ($per->ahmed_closing_share_pct ?? 0.0);
                                        $pPctDiff      = round($pClosePct - $pAhmedOpenPct, 4);
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="fw-semibold" style="color: var(--c-navy);">{{ $per->period_name ?: $per->end_date }}</td>
                                        <td class="text-center">{{ number_format($pAhmedCap, 2) }} ج</td>
                                        <td class="text-center"><span class="prt-chip is-neutral">{{ number_format($pAhmedOpenPct, 2) }}%</span></td>
                                        <td class="text-center fw-semibold" style="color: {{ $pOwnProf >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">{{ number_format($pOwnProf, 2) }} ج</td>
                                        <td class="text-center fw-semibold" style="color: var(--c-info);">+{{ number_format($pComm, 2) }} ج</td>
                                        <td class="text-center fw-bold" style="color: var(--c-success);">+{{ number_format($pTotal, 2) }} ج</td>
                                        <td class="text-center">
                                            @if(!empty($per->ahmed_withdrawn_amount) && $per->ahmed_withdrawn_amount > 0)
                                                <span class="prt-chip is-danger">مسحوب: {{ number_format($per->ahmed_withdrawn_amount, 2) }} ج</span>
                                            @else
                                                <span class="prt-chip is-success">تدوير بالحساب</span>
                                            @endif
                                        </td>
                                        <td class="text-center prt-num-main">{{ number_format($pCloseCap, 2) }} ج</td>
                                        <td class="text-center">
                                            <span class="prt-chip is-accent">{{ number_format($pClosePct, 2) }}%</span>
                                            @if(abs($pPctDiff) > 0.0001)
                                                <div class="prt-num-sub fw-semibold" style="color: {{ $pPctDiff > 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                    {{ $pPctDiff > 0 ? '+' : '' }}{{ number_format($pPctDiff, 2) }}%
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center no-print">
                                            <a href="{{ route('partners.period.show', $per->id) }}" class="prt-pill-btn is-ghost is-sm py-1 px-3">
                                                <i class="fa fa-file-invoice fa-xs"></i> التقرير
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">لم يتم إقفال أي دورات سابقة حتى الآن.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($periods->total() > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end">الإجمالي التراكمي لكافة الدورات ({{ $periods->total() }}):</td>
                                        <td class="text-center">{{ number_format($totalOwnProfits, 2) }} ج</td>
                                        <td class="text-center" style="color: var(--c-info);">+{{ number_format($totalCommissions, 2) }} ج</td>
                                        <td class="text-center" style="color: var(--c-success);">+{{ number_format($totalEarnedAll, 2) }} ج</td>
                                        <td class="text-center">
                                            <span class="{{ $totalPeriodWithdrawn > 0 ? 'text-debit' : '' }}">مسحوب: {{ number_format($totalPeriodWithdrawn, 2) }} ج</span>
                                            <small class="d-block fw-normal text-muted" style="font-size:0.7rem;">مدوّر: {{ number_format($totalPeriodReinvested, 2) }} ج</small>
                                        </td>
                                        <td class="text-center">{{ number_format($ahmedCapital, 2) }} ج</td>
                                        <td class="text-center">{{ number_format($ahmedSharePct, 2) }}%</td>
                                        <td class="no-print"></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    {{-- ملخص الحركات في الطباعة --}}
                    @if($allMovements->isNotEmpty())
                        <div class="print-only mt-3">
                            <div class="prt-panel-head">
                                <h6 class="m-0 fw-bold">ثانياً: سجل الحركات المالية على حساب الإدارة (الإيداعات والمسحوبات المباشرة والتدوير)</h6>
                            </div>
                            <table class="prt-table">
                                <thead>
                                    <tr>
                                        <th style="width: 32px;">#</th>
                                        <th>التاريخ والوقت</th>
                                        <th>نوع الحركة</th>
                                        <th>المبلغ</th>
                                        <th>الخزينة / المحفظة</th>
                                        <th>البيان والملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allMovements->take(25) as $pm)
                                        @php $isDebitMove = in_array($pm->type, ['capital_withdrawal', 'profit_withdrawal'], true); @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ date('Y-m-d H:i', strtotime($pm->created_at)) }}</td>
                                            <td class="text-center">
                                                @if($pm->type === 'initial_deposit') إيداع رأس مال تأسيسي
                                                @elseif($pm->type === 'capital_increase') إيداع / زيادة رصيد
                                                @elseif($pm->type === 'capital_withdrawal') سحب مباشر من الرصيد
                                                @elseif($pm->type === 'profit_withdrawal') صرف أرباح دورية
                                                @elseif($pm->type === 'profit_reinvest') تدوير أرباح بالرصيد
                                                @elseif($pm->type === 'annual_rollover') إقفال سنوي وترحيل رصيد
                                                @else {{ $pm->type }}
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold {{ $isDebitMove ? 'text-debit' : '' }}">
                                                {{ $pm->type === 'annual_rollover' ? '' : ($isDebitMove ? '-' : '+') }}{{ number_format($pm->amount, 2) }} ج
                                            </td>
                                            <td class="text-center">{{ $pm->account_name ?: '—' }}</td>
                                            <td>{{ $pm->notes ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($allMovements->count() > 25)
                                <div class="text-muted mt-1" style="font-size:6.5pt;">* يعرض آخر 25 حركة مالية في الطباعة من إجمالي {{ $allMovements->count() }} حركة مسجلة.</div>
                            @endif
                        </div>
                    @endif

                    <div class="prt-panel-foot no-print">
                        <div>
                            عرض <strong>{{ $periods->firstItem() ?? 0 }}</strong> إلى <strong>{{ $periods->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $periods->total() }}</strong> دورة معتمدة
                        </div>
                        <div>
                            {{ $periods->appends(array_merge(request()->query(), ['tab' => 'periods']))->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- التاب 2: الحركات المالية المباشرة --}}
                <div class="tab-pane fade {{ $activeTab === 'movements' ? 'show active' : '' }}" id="tabAhmedMovements" role="tabpanel">
                    <div class="prt-panel-head">
                        <h5 class="prt-panel-title">
                            <span class="prt-panel-title-ico"><i class="fa fa-exchange-alt"></i></span>
                            <span>سجل الحركات المالية المباشرة لحساب الإدارة ({{ $headName }})</span>
                        </h5>
                        <span class="prt-chip is-neutral">{{ $movements->total() }} حركة</span>
                    </div>
                    <div class="prt-table-wrap">
                        <table class="prt-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px;">#</th>
                                    <th class="text-center">التاريخ والوقت</th>
                                    <th class="text-center">نوع الحركة</th>
                                    <th class="text-center">المبلغ</th>
                                    <th class="text-center">الخزينة المستلمة / المصروف منها</th>
                                    <th>البيان والملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $m)
                                    @php $isDebit = in_array($m->type, ['capital_withdrawal', 'profit_withdrawal'], true); @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="text-center text-muted small" dir="ltr">{{ date('Y-m-d H:i', strtotime($m->created_at)) }}</td>
                                        <td class="text-center">
                                            @if($m->type === 'initial_deposit')
                                                <span class="prt-chip is-success">إيداع رأس مال تأسيسي</span>
                                            @elseif($m->type === 'capital_increase')
                                                <span class="prt-chip is-info">إيداع / زيادة رصيد</span>
                                            @elseif($m->type === 'capital_withdrawal')
                                                <span class="prt-chip is-danger">سحب مباشر من الرصيد</span>
                                            @elseif($m->type === 'profit_withdrawal')
                                                <span class="prt-chip is-warning">صرف أرباح دورية</span>
                                            @elseif($m->type === 'profit_reinvest')
                                                <span class="prt-chip is-accent">تدوير أرباح بالرصيد</span>
                                            @elseif($m->type === 'annual_rollover')
                                                <span class="prt-chip is-warning">إقفال سنوي وترحيل رصيد</span>
                                            @else
                                                <span class="prt-chip is-neutral">{{ $m->type }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold" style="color: {{ $m->type === 'annual_rollover' ? 'var(--c-info)' : ($isDebit ? 'var(--c-danger)' : 'var(--c-success)') }};">
                                            <span dir="ltr">{{ $m->type === 'annual_rollover' ? '' : ($isDebit ? '-' : '+') }}{{ number_format($m->amount, 2) }}</span> ج
                                        </td>
                                        <td class="text-center fw-semibold" style="color: var(--c-navy);">{{ $m->account_name ?: '—' }}</td>
                                        <td class="text-muted">{{ $m->notes ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">لا توجد حركات مالية مسجلة لحساب الإدارة حتى الآن.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="prt-panel-foot no-print">
                        <div>
                            عرض <strong>{{ $movements->firstItem() ?? 0 }}</strong> إلى <strong>{{ $movements->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $movements->total() }}</strong> حركة
                        </div>
                        <div>
                            {{ $movements->appends(array_merge(request()->query(), ['tab' => 'movements']))->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- التاب 3: سجل تغير النسب والحصص --}}
                <div class="tab-pane fade {{ $activeTab === 'history' ? 'show active' : '' }}" id="tabAhmedShareHistory" role="tabpanel">
                    <div class="prt-panel-head">
                        <h5 class="prt-panel-title">
                            <span class="prt-panel-title-ico"><i class="fa fa-history"></i></span>
                            <span>سجل تاريخ تغير نسب وحصص حساب الإدارة ({{ $headName }})</span>
                        </h5>
                        <span class="prt-chip is-neutral">{{ $historyCount }} سجل</span>
                    </div>
                    <div class="prt-table-wrap">
                        <table class="prt-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px;">#</th>
                                    <th class="text-center">التاريخ والوقت</th>
                                    <th>العملية / السبب</th>
                                    <th class="text-center">الرصيد قبل</th>
                                    <th class="text-center th-soft-before">النسبة قبل (%)</th>
                                    <th class="text-center">التغير في الرصيد</th>
                                    <th class="text-center">الرصيد بعد</th>
                                    <th class="text-center th-soft-after">النسبة بعد (%)</th>
                                    <th class="text-center">فرق النسبة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shareHistoryLines ?? [] as $hl)
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="text-center text-muted small text-nowrap" dir="ltr">{{ date('Y-m-d H:i', strtotime($hl->event_date)) }}</td>
                                        <td class="fw-semibold" style="color: var(--c-navy);">{{ $hl->event_title }}</td>
                                        <td class="text-center text-muted">{{ number_format($hl->capital_before, 2) }} ج</td>
                                        <td class="text-center"><span class="prt-chip is-neutral">{{ number_format($hl->share_pct_before, 2) }}%</span></td>
                                        <td class="text-center fw-semibold" style="color: {{ $hl->capital_change > 0 ? 'var(--c-success)' : ($hl->capital_change < 0 ? 'var(--c-danger)' : 'var(--c-text-soft)') }};">
                                            {{ abs($hl->capital_change) >= 0.01 ? (($hl->capital_change > 0 ? '+' : '') . number_format($hl->capital_change, 2) . ' ج') : '—' }}
                                        </td>
                                        <td class="text-center prt-num-main">{{ number_format($hl->capital_after, 2) }} ج</td>
                                        <td class="text-center"><span class="prt-chip is-accent">{{ number_format($hl->share_pct_after, 2) }}%</span></td>
                                        <td class="text-center">
                                            @if($hl->share_pct_diff > 0.0001)
                                                <span class="prt-chip is-success">+{{ number_format($hl->share_pct_diff, 2) }}%</span>
                                            @elseif($hl->share_pct_diff < -0.0001)
                                                <span class="prt-chip is-danger">{{ number_format($hl->share_pct_diff, 2) }}%</span>
                                            @else
                                                <span class="text-muted small">ثابتة</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">لا توجد سجلات تغير نسب مسجلة حتى الآن.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(is_object($shareHistoryLines) && method_exists($shareHistoryLines, 'links'))
                        <div class="prt-panel-foot no-print">
                            <div>
                                عرض <strong>{{ $shareHistoryLines->firstItem() ?? 0 }}</strong> إلى <strong>{{ $shareHistoryLines->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $shareHistoryLines->total() }}</strong> سجل
                            </div>
                            <div>
                                {{ $shareHistoryLines->appends(array_merge(request()->query(), ['tab' => 'history']))->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- خانات التوقيع والاعتماد الرسمية للطباعة --}}
        <div class="print-only" style="margin-top: 14px; page-break-inside: avoid;">
            <div class="row g-2 text-center m-0">
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8pt;">إعداد وتدقيق الحسابات</div>
                        <div class="text-muted small mb-4" style="font-size:6.8pt;">وحدة الحسابات والرقابة المالية</div>
                        <div class="border-bottom border-dark border-1 mx-3 mb-2"></div>
                        <div class="small text-muted" style="font-size:6.8pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8pt;">المراجعة المالية</div>
                        <div class="text-muted small mb-4" style="font-size:6.8pt;">مطابقة الأرصدة والخزائن</div>
                        <div class="border-bottom border-dark border-1 mx-3 mb-2"></div>
                        <div class="small text-muted" style="font-size:6.8pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8pt;">اعتماد الإدارة</div>
                        <div class="text-muted small mb-4" style="font-size:6.8pt;">{{ $headName }}</div>
                        <div class="border-bottom border-dark border-1 mx-3 mb-2"></div>
                        <div class="small text-muted" style="font-size:6.8pt;">الاعتماد: ............................</div>
                    </div>
                </div>
            </div>
            <div class="text-center text-muted mt-2 pt-2 border-top" style="font-size:6.8pt;">
                كشف حساب مالي رسمي معتمد ومستخرج من النظام المالي ({{ $companyName }}) · صادر بتاريخ {{ date('Y-m-d H:i') }}
            </div>
        </div>

    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
    const chartFont = { family: 'IBM Plex Sans Arabic', size: 11 };

    const ctxCap = document.getElementById('chartAhmedCapital')?.getContext('2d');
    if (ctxCap) {
        new Chart(ctxCap, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'رصيد حساب الإدارة (ج)',
                    data: {!! json_encode($chartAhmedCapital) !!},
                    borderColor: '#0d7373',
                    backgroundColor: 'rgba(13, 115, 115, 0.11)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d7373'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: false, grid: { color: '#edf2f7' }, ticks: { font: chartFont } },
                    x: { grid: { display: false }, ticks: { font: chartFont } }
                }
            }
        });
    }

    const ctxDoughnut = document.getElementById('chartOwnershipDoughnut')?.getContext('2d');
    if (ctxDoughnut) {
        const partnerNames = [{!! json_encode($headName) !!}];
        const partnerCaps = [{{ $ahmedCapital }}];
        const partnerColors = ['#0d7373'];

        const calmPalette = ['#1e5fa4', '#2d8659', '#b67c1f', '#4f8a8b', '#5a6b8c', '#7c6f9f', '#4d7c6f', '#8c7a5b'];
        let cIdx = 0;
        @foreach($partners as $p)
            partnerNames.push({!! json_encode($p->name) !!});
            partnerCaps.push({{ (float) $p->capital }});
            partnerColors.push(calmPalette[cIdx % calmPalette.length]);
            cIdx++;
        @endforeach

        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: partnerNames,
                datasets: [{
                    data: partnerCaps,
                    backgroundColor: partnerColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: { boxWidth: 10, usePointStyle: true, font: chartFont, color: '#5a6478' }
                    }
                }
            }
        });
    }

    const ctxBreakdown = document.getElementById('chartAhmedIncomeBreakdown')?.getContext('2d');
    if (ctxBreakdown) {
        new Chart(ctxBreakdown, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'الأرباح من نسب الشغل مع الشركاء (ج)',
                        data: {!! json_encode($chartAhmedCommissions) !!},
                        backgroundColor: '#1e5fa4',
                        borderRadius: 5
                    },
                    {
                        label: 'عائد الرصيد المدور في حساب الإدارة (ج)',
                        data: {!! json_encode($chartAhmedOwnProfit) !!},
                        backgroundColor: '#2d8659',
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: chartFont, usePointStyle: true } } },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { font: chartFont } },
                    y: { stacked: true, beginAtZero: true, grid: { color: '#edf2f7' }, ticks: { font: chartFont } }
                }
            }
        });
    }
</script>

</body>
</html>
