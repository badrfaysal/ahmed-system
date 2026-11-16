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
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

    <style>
        :root {
            --ink-900: #0f172a;
            --ink-800: #1e293b;
            --ink-700: #334155;
            --ink-600: #475569;
            --ink-500: #64748b;
            --line-300: #cbd5e1;
            --line-200: #e2e8f0;
            --surface-100: #f1f5f9;
            --surface-50: #f8fafc;
            --debit-ink: #991b1b;
        }

        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--surface-50);
            color: var(--ink-900);
            font-size: 0.9rem;
        }

        .main-content {
            padding: 24px 20px;
        }

        .num-tabular {
            font-variant-numeric: tabular-nums;
        }

        .text-debit {
            color: var(--debit-ink) !important;
        }

        /* بطاقة الملخص التنفيذي النظيفة (أبيض وأسود/رمادي احترافي) */
        .exec-summary-card {
            background: #ffffff;
            border: 1px solid var(--line-300);
            border-top: 3px solid var(--ink-900);
            border-radius: 8px;
            padding: 20px 22px 16px;
            margin-bottom: 22px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .exec-share-pill {
            background: var(--surface-50);
            border: 1px solid var(--line-300);
            border-radius: 6px;
            padding: 10px 16px;
            text-align: center;
            min-width: 190px;
        }

        .exec-share-pill .lbl {
            display: block;
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--ink-600);
            margin-bottom: 2px;
        }

        .exec-share-pill .pct {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--ink-900);
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
        }

        .kpi-tile {
            background: var(--surface-50);
            border: 1px solid var(--line-200);
            border-radius: 6px;
            padding: 14px 16px;
            height: 100%;
        }

        .kpi-tile.is-primary {
            background: #ffffff;
            border-color: var(--line-300);
            border-right: 3px solid var(--ink-900);
        }

        .kpi-tile .title {
            font-size: 0.77rem;
            font-weight: 700;
            color: var(--ink-600);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kpi-tile .val {
            font-size: 1.38rem;
            font-weight: 800;
            color: var(--ink-900);
            line-height: 1.25;
            font-variant-numeric: tabular-nums;
        }

        .kpi-tile .sub {
            font-size: 0.73rem;
            color: var(--ink-500);
            margin-top: 6px;
            line-height: 1.4;
        }

        .reconcile-strip {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid var(--line-200);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px 18px;
            font-size: 0.77rem;
            color: var(--ink-600);
        }

        .reconcile-strip .rec-item strong {
            color: var(--ink-900);
            font-variant-numeric: tabular-nums;
        }

        .card-formal {
            background: #ffffff;
            border: 1px solid var(--line-300);
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            margin-bottom: 22px;
            overflow: hidden;
        }

        .chart-formal {
            background: #ffffff;
            border: 1px solid var(--line-300);
            border-radius: 8px;
            padding: 18px 20px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            height: 100%;
        }

        .table-responsive {
            overflow-x: visible !important;
        }

        .table-formal {
            margin: 0;
            width: 100% !important;
            vertical-align: middle;
            font-size: 0.83rem;
        }

        .table-formal thead th {
            background: var(--ink-800);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 10px 8px;
            border: 1px solid var(--ink-800);
            white-space: normal;
            text-align: center;
            vertical-align: middle;
            line-height: 1.3;
        }

        .table-formal tbody td {
            padding: 9px 8px;
            border-bottom: 1px solid var(--line-200);
            font-variant-numeric: tabular-nums;
            color: var(--ink-900);
        }

        .table-formal tbody tr:nth-child(even) {
            background-color: #fcfcfd;
        }

        .table-formal tfoot th,
        .table-formal tfoot td {
            background-color: var(--surface-100);
            border-top: 2px solid var(--ink-900);
            border-bottom: none;
            padding: 10px 8px;
            font-weight: 800;
            font-size: 0.81rem;
            color: var(--ink-900);
            font-variant-numeric: tabular-nums;
        }

        .pct-tag {
            display: inline-block;
            background: var(--surface-50);
            border: 1px solid var(--line-300);
            color: var(--ink-900);
            border-radius: 4px;
            padding: 2px 7px;
            font-size: 0.76rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .status-tag {
            display: inline-block;
            background: var(--surface-50);
            border: 1px solid var(--line-300);
            color: var(--ink-700);
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 0.74rem;
            font-weight: 700;
        }

        /* شريط التبويبات الهادئ والمحايد */
        .statement-tabs-bar {
            background: var(--surface-50);
            padding: 10px 16px 0;
            border-bottom: 1px solid var(--line-300);
        }

        .statement-tabs-bar .nav-tabs {
            border-bottom: none;
            gap: 6px;
        }

        .statement-tabs-bar .nav-link {
            border: 1px solid transparent !important;
            border-bottom: 2px solid transparent !important;
            border-radius: 6px 6px 0 0 !important;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--ink-600);
            background: transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .statement-tabs-bar .nav-link:hover {
            color: var(--ink-900);
            background: var(--surface-100);
        }

        .statement-tabs-bar .nav-link.active {
            color: var(--ink-900) !important;
            background: #ffffff !important;
            border-color: var(--line-300) var(--line-300) #ffffff !important;
            border-bottom: 2px solid var(--ink-900) !important;
            font-weight: 800;
            margin-bottom: -1px;
        }

        .tab-count-pill {
            background: var(--surface-100);
            color: var(--ink-700);
            border: 1px solid var(--line-300);
            border-radius: 4px;
            padding: 1px 6px;
            font-size: 0.72rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .statement-tabs-bar .nav-link.active .tab-count-pill {
            background: var(--ink-900);
            color: #ffffff;
            border-color: var(--ink-900);
        }

        .print-only { display: none; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 9mm 7mm 10mm 7mm;
            }
            *, *::before, *::after {
                box-sizing: border-box !important;
                overflow: visible !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
                box-shadow: none !important;
            }
            ::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }
            html, body {
                background: #ffffff !important;
                color: #0f172a !important;
                font-family: 'Cairo', 'IBM Plex Sans Arabic', sans-serif !important;
                font-size: 7.4pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop,
            .no-print, th.no-print, td.no-print, button, .btn,
            .chart-formal, canvas, .card-footer, .pagination, .statement-tabs-bar {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                margin-right: 0 !important;
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
                right: 0 !important;
                left: 0 !important;
            }
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .text-nowrap {
                white-space: normal !important;
            }

            /* ترويسة الطباعة الرسمية */
            .print-doc-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 8px !important;
                border-bottom: 2px solid #0f172a !important;
                padding-bottom: 8px !important;
                margin-bottom: 10px !important;
                width: 100% !important;
            }
            .print-doc-header .hdr-col {
                flex: 1 1 0 !important;
                min-width: 0 !important;
            }
            .print-doc-header .hdr-center {
                flex: 1.35 1 0 !important;
                text-align: center !important;
                min-width: 0 !important;
            }
            .print-doc-header .hdr-meta-box {
                border: 1px solid #cbd5e1 !important;
                background: #f8fafc !important;
                border-radius: 4px !important;
                padding: 5px 8px !important;
                font-size: 6.8pt !important;
                text-align: right !important;
            }

            /* ملخص المؤشرات المطبوع (نظيف بدون خلفية سوداء) */
            .exec-summary-card {
                border: 1px solid #cbd5e1 !important;
                border-top: 2px solid #0f172a !important;
                border-radius: 4px !important;
                padding: 8px 10px !important;
                margin-bottom: 10px !important;
                background: #ffffff !important;
                page-break-inside: avoid !important;
            }
            .exec-summary-top {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 6px !important;
                padding-bottom: 5px !important;
                border-bottom: 1px solid #e2e8f0 !important;
            }
            .exec-summary-top h5 {
                font-size: 9pt !important;
                margin: 0 !important;
            }
            .exec-summary-top .exec-desc {
                display: none !important;
            }
            .exec-share-pill {
                padding: 3px 10px !important;
                min-width: auto !important;
            }
            .exec-share-pill .lbl {
                font-size: 6.3pt !important;
                display: inline !important;
                margin-left: 4px !important;
            }
            .exec-share-pill .pct {
                font-size: 9.5pt !important;
                display: inline !important;
            }
            .exec-kpi-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 6px !important;
                margin: 0 !important;
            }
            .exec-kpi-grid > [class*="col-"] {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .kpi-tile {
                padding: 6px 7px !important;
                border: 1px solid #cbd5e1 !important;
                background: #f8fafc !important;
            }
            .kpi-tile.is-primary {
                background: #ffffff !important;
                border-right: 2.5px solid #0f172a !important;
            }
            .kpi-tile .title {
                font-size: 6.6pt !important;
                margin-bottom: 2px !important;
            }
            .kpi-tile .val {
                font-size: 9.5pt !important;
            }
            .kpi-tile .sub {
                font-size: 6pt !important;
                margin-top: 2px !important;
            }
            .reconcile-strip {
                margin-top: 6px !important;
                padding-top: 5px !important;
                font-size: 6.5pt !important;
                gap: 6px 12px !important;
            }

            .card-formal {
                border: none !important;
                box-shadow: none !important;
                margin-bottom: 10px !important;
                border-radius: 0 !important;
                page-break-inside: auto !important;
                background: transparent !important;
                padding: 0 !important;
            }
            .card-header-formal {
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                border-right: 3px solid #0f172a !important;
                padding: 5px 8px !important;
                margin-bottom: 6px !important;
                border-radius: 3px !important;
            }
            .card-header-formal h6 {
                font-size: 8.5pt !important;
                font-weight: 800 !important;
                margin: 0 !important;
                color: #0f172a !important;
            }
            .tab-content {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .tab-content > .tab-pane:not(.active) {
                display: none !important;
            }
            .tab-content > .tab-pane.active {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            .table-responsive {
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .table-formal {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                border-collapse: collapse !important;
                font-size: 6.8pt !important;
                table-layout: auto !important;
                margin-bottom: 8px !important;
            }
            .table-formal thead {
                display: table-header-group !important;
            }
            .table-formal tbody {
                display: table-row-group !important;
            }
            .table-formal thead th {
                background-color: #1e293b !important;
                color: #ffffff !important;
                border: 1px solid #1e293b !important;
                padding: 4px 2.5px !important;
                font-size: 6.4pt !important;
                font-weight: 700 !important;
                text-align: center !important;
                white-space: normal !important;
                line-height: 1.2 !important;
            }
            .table-formal tbody td {
                border: 1px solid #cbd5e1 !important;
                padding: 3.5px 2.5px !important;
                font-size: 6.8pt !important;
                color: #0f172a !important;
                background: #ffffff !important;
                white-space: normal !important;
            }
            .table-formal tfoot th,
            .table-formal tfoot td {
                background-color: #f1f5f9 !important;
                border: 1px solid #94a3b8 !important;
                border-top: 2px solid #0f172a !important;
                padding: 4px 2.5px !important;
                font-size: 6.9pt !important;
                color: #0f172a !important;
            }
            .table-formal tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            .pct-tag, .status-tag, .badge {
                white-space: normal !important;
                display: inline-block !important;
                font-size: 6.2pt !important;
                padding: 1px 4px !important;
                border: 1px solid #cbd5e1 !important;
                background: #f8fafc !important;
                color: #0f172a !important;
            }
            .print-sig-col {
                border: 1px solid #cbd5e1 !important;
                padding: 8px !important;
                text-align: center !important;
                background: #ffffff !important;
                border-radius: 4px !important;
            }
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
    <div class="container-fluid">

        {{-- الترويسة المطبوعة الرسمية --}}
        <div class="print-only print-doc-header">
            <div class="hdr-col text-end">
                <div class="fw-bold" style="font-size:10pt; color:#0f172a; white-space: pre-line;">{{ $companyName }}</div>
                <div class="text-muted" style="font-size:7pt;">وحدة الحسابات والرقابة المالية وإدارة الحصص</div>
            </div>
            <div class="hdr-center">
                <div class="fw-bold" style="font-size:12pt; color:#0f172a;">كشف حساب الإدارة التنفيذية</div>
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
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة إلى سجل الشركاء
                </a>
                <h4 class="fw-bold m-0 d-flex align-items-center gap-2 text-dark">
                    <i class="fa fa-file-invoice-dollar text-secondary"></i> كشف حساب الإدارة: {{ $headName }}
                </h4>
                <small class="text-muted">بيان المركز المالي المعتمد وأرباح نسب الشغل والحركات المالية وتطور الحصة</small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="status-tag py-2 px-3">
                    حساب الإدارة الرئيسي · الحصة: {{ number_format($ahmedSharePct, 2) }}%
                </span>
                <button type="button" class="btn btn-dark btn-sm fw-bold px-3 py-2 shadow-sm" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> طباعة كشف الحساب (A4)
                </button>
            </div>
        </div>

        {{-- الملخص المالي الموحد (شاشة + طباعة بدون ألوان مبالغ فيها) --}}
        <div class="exec-summary-card">
            <div class="exec-summary-top d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold m-0 text-dark">ملخص المركز المالي لحساب الإدارة ({{ $headName }})</h5>
                    <div class="exec-desc text-muted small mt-1">
                        يشمل رصيد الإدارة المعتمد، أرباح نسب الشغل المتفق عليها مع الشركاء، عائد تدوير الرصيد، وكافة المسحوبات والإيداعات المباشرة.
                    </div>
                </div>
                <div class="exec-share-pill">
                    <span class="lbl">نسبة الحصة المعتمدة بالدورة</span>
                    <span class="pct">{{ number_format($ahmedSharePct, 2) }}%</span>
                </div>
            </div>

            <div class="row g-3 exec-kpi-grid">
                <div class="col-xl-3 col-md-6">
                    <div class="kpi-tile is-primary">
                        <div class="title"><i class="fa fa-vault"></i> الرصيد الحالي المعتمد</div>
                        <div class="val">{{ number_format($ahmedCapital, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                        <div class="sub">من إجمالي الحصص المعتمدة: {{ number_format($totalApprovedCapital, 2) }} ج</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="kpi-tile">
                        <div class="title"><i class="fa fa-briefcase"></i> أرباح نسب الشغل (الإدارة)</div>
                        <div class="val">{{ number_format($totalCommissions, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                        <div class="sub">حصة الإدارة من أرباح الشركاء ({{ $periods->total() }} دورة)</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="kpi-tile">
                        <div class="title"><i class="fa fa-coins"></i> عائد الرصيد المدور</div>
                        <div class="val">{{ number_format($totalOwnProfits, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                        <div class="sub">الأرباح المحققة من رصيد الإدارة بالدورة</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="kpi-tile">
                        <div class="title"><i class="fa fa-calculator"></i> إجمالي الأرباح المحققة</div>
                        <div class="val">{{ number_format($totalEarnedAll, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                        <div class="sub">مدوّر: {{ number_format($totalPeriodReinvested, 2) }} ج · مسحوب: {{ number_format($totalCashWithdrawn, 2) }} ج</div>
                    </div>
                </div>
            </div>

            <div class="reconcile-strip">
                <div class="rec-item">حصص الشركاء الحالية: <strong>{{ number_format($totalPartnersCapital, 2) }} ج</strong> ({{ number_format(max(0, 100 - $ahmedSharePct), 2) }}%)</div>
                <div class="rec-item">إيداعات مباشرة بالرصيد: <strong>{{ number_format($directDeposits, 2) }} ج</strong></div>
                <div class="rec-item">مسحوبات مباشرة من الرصيد: <strong class="{{ $directWithdrawals > 0 ? 'text-debit' : '' }}">{{ number_format($directWithdrawals, 2) }} ج</strong></div>
                <div class="rec-item">مصروف من أرباح الدورات: <strong class="{{ $totalPeriodWithdrawn > 0 ? 'text-debit' : '' }}">{{ number_format($totalPeriodWithdrawn, 2) }} ج</strong></div>
                <div class="rec-item">إجمالي المسحوبات النقدية: <strong class="{{ $totalCashWithdrawn > 0 ? 'text-debit' : '' }}">{{ number_format($totalCashWithdrawn, 2) }} ج</strong></div>
            </div>
        </div>

        {{-- الشارتات التحليلية (للشاشة فقط بألوان هادئة متجانسة) --}}
        <div class="row g-3 mb-4 no-print">
            <div class="col-md-8">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark">تطور رصيد حساب الإدارة عبر الدورات الشهرية</h6>
                        <small class="text-muted">تراكم الرصيد وتدوير الأرباح</small>
                    </div>
                    <div style="height: 255px;">
                        <canvas id="chartAhmedCapital"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark">هيكل الحصص المعتمدة بالدورة</h6>
                    </div>
                    <div style="height: 255px; position: relative;">
                        <canvas id="chartOwnershipDoughnut"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark">تحليل دخل حساب الإدارة لكل دورة (نسب الشغل مقابل عائد الرصيد المدور)</h6>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="chartAhmedIncomeBreakdown"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- الجداول المحاسبية التفصيلية --}}
        <div class="card-formal mb-4">
            <div class="statement-tabs-bar no-print">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'periods' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAhmedPeriods" type="button" role="tab">
                            <i class="fa fa-list-alt"></i>
                            <span>بيان الدورات الشهرية والأرباح</span>
                            <span class="tab-count-pill">{{ $periods->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'movements' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAhmedMovements" type="button" role="tab">
                            <i class="fa fa-exchange-alt"></i>
                            <span>الحركات المالية المباشرة</span>
                            <span class="tab-count-pill">{{ $movements->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'history' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAhmedShareHistory" type="button" role="tab">
                            <i class="fa fa-history"></i>
                            <span>سجل تغير النسب والحصص</span>
                            <span class="tab-count-pill">{{ $historyCount }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                {{-- التاب 1: جدول بيان الدورات الشهرية والأرباح --}}
                <div class="tab-pane fade {{ $activeTab === 'periods' ? 'show active' : '' }}" id="tabAhmedPeriods" role="tabpanel">
                    <div class="card-header-formal print-only">
                        <h6>أولاً: بيان أرباح حساب الإدارة عبر الدورات الشهرية المعتمدة</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الدورة الشهرية</th>
                                    <th>الرصيد قبل التقفيل</th>
                                    <th>النسبة قبل</th>
                                    <th>ربح الرصيد المدور</th>
                                    <th>أرباح نسب الشغل</th>
                                    <th>إجمالي ربح الدورة</th>
                                    <th>القرار / المسحوب</th>
                                    <th>الرصيد بعد التقفيل</th>
                                    <th>النسبة بعد</th>
                                    <th class="no-print">التقرير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periods as $per)
                                @php
                                    $pAhmedCap      = (float) ($per->ahmed_opening_capital ?? 0.0);
                                    $pAhmedOpenPct  = (float) ($per->ahmed_capital_share_pct ?? 0.0);
                                    $pOwnProf       = (float) ($per->ahmed_own_profit ?? 0.0);
                                    $pComm          = (float) ($per->total_ahmed_commission ?? 0.0);
                                    $pTotal         = (float) ($per->ahmed_total_profit ?: ($pOwnProf + $pComm));
                                    $pCloseCap      = (float) ($per->ahmed_closing_capital ?? 0.0);
                                    $pClosePct      = (float) ($per->ahmed_closing_share_pct ?? 0.0);
                                    $pPctDiff       = round($pClosePct - $pAhmedOpenPct, 4);
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $per->period_name ?: $per->end_date }}</td>
                                    <td class="text-center">{{ number_format($pAhmedCap, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="pct-tag">{{ number_format($pAhmedOpenPct, 2) }}%</span>
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($pOwnProf, 2) }} ج</td>
                                    <td class="text-center fw-bold">+{{ number_format($pComm, 2) }} ج</td>
                                    <td class="text-center fw-bold">+{{ number_format($pTotal, 2) }} ج</td>
                                    <td class="text-center">
                                        @if(!empty($per->ahmed_withdrawn_amount) && $per->ahmed_withdrawn_amount > 0)
                                            <span class="status-tag text-debit">مسحوب: {{ number_format($per->ahmed_withdrawn_amount, 2) }} ج</span>
                                        @else
                                            <span class="status-tag">تدوير بالحساب</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($pCloseCap, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="pct-tag">{{ number_format($pClosePct, 2) }}%</span>
                                        @if(abs($pPctDiff) > 0.0001)
                                            <small class="d-block {{ $pPctDiff < 0 ? 'text-debit' : 'text-muted' }}" style="font-size:0.68rem;">
                                                {{ $pPctDiff > 0 ? '+' : '' }}{{ number_format($pPctDiff, 2) }}%
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('partners.period.show', $per->id) }}" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:0.75rem;">
                                            <i class="fa fa-file-invoice me-1"></i> التقرير
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">لم يتم إقفال أي دورات سابقة حتى الآن.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($periods->total() > 0)
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end">الإجمالي التراكمي لكافة الدورات ({{ $periods->total() }}):</td>
                                    <td class="text-center">{{ number_format($totalOwnProfits, 2) }} ج</td>
                                    <td class="text-center">+{{ number_format($totalCommissions, 2) }} ج</td>
                                    <td class="text-center">+{{ number_format($totalEarnedAll, 2) }} ج</td>
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

                    {{-- في حالة الطباعة من تاب الدورات، عرض ملخص الحركات المباشرة لإيضاح الإيداعات والمسحوبات --}}
                    @if($allMovements->isNotEmpty())
                    <div class="print-only mt-3">
                        <div class="card-header-formal">
                            <h6>ثانياً: سجل الحركات المالية على حساب الإدارة (الإيداعات والمسحوبات المباشرة والتدوير)</h6>
                        </div>
                        <table class="table table-formal m-0">
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
                                @php
                                    $isDebitMove = in_array($pm->type, ['capital_withdrawal', 'profit_withdrawal'], true);
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ date('Y-m-d H:i', strtotime($pm->created_at)) }}</td>
                                    <td class="text-center">
                                        @if($pm->type === 'initial_deposit')
                                            إيداع رأس مال تأسيسي
                                        @elseif($pm->type === 'capital_increase')
                                            إيداع / زيادة رصيد
                                        @elseif($pm->type === 'capital_withdrawal')
                                            سحب مباشر من الرصيد
                                        @elseif($pm->type === 'profit_withdrawal')
                                            صرف أرباح دورية
                                        @elseif($pm->type === 'profit_reinvest')
                                            تدوير أرباح بالرصيد
                                        @elseif($pm->type === 'annual_rollover')
                                            إقفال سنوي وترحيل رصيد
                                        @else
                                            {{ $pm->type }}
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

                    <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                        <div class="small text-muted">
                            عرض <strong>{{ $periods->firstItem() ?? 0 }}</strong> إلى <strong>{{ $periods->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $periods->total() }}</strong> دورة معتمدة
                        </div>
                        <div>
                            {{ $periods->appends(array_merge(request()->query(), ['tab' => 'periods']))->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- التاب 2: الحركات المالية المباشرة --}}
                <div class="tab-pane fade {{ $activeTab === 'movements' ? 'show active' : '' }}" id="tabAhmedMovements" role="tabpanel">
                    <div class="card-header-formal print-only">
                        <h6>سجل الحركات المالية المباشرة لحساب الإدارة ({{ $headName }})</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ والوقت</th>
                                    <th>نوع الحركة</th>
                                    <th>المبلغ</th>
                                    <th>الخزينة المستلمة / المصروف منها</th>
                                    <th>البيان والملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $m)
                                @php
                                    $isDebit = in_array($m->type, ['capital_withdrawal', 'profit_withdrawal'], true);
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ date('Y-m-d H:i', strtotime($m->created_at)) }}</td>
                                    <td class="text-center">
                                        @if($m->type === 'initial_deposit')
                                            <span class="status-tag">إيداع رأس مال تأسيسي</span>
                                        @elseif($m->type === 'capital_increase')
                                            <span class="status-tag">إيداع / زيادة رصيد</span>
                                        @elseif($m->type === 'capital_withdrawal')
                                            <span class="status-tag text-debit">سحب مباشر من الرصيد</span>
                                        @elseif($m->type === 'profit_withdrawal')
                                            <span class="status-tag text-debit">صرف أرباح دورية</span>
                                        @elseif($m->type === 'profit_reinvest')
                                            <span class="status-tag">تدوير أرباح بالرصيد</span>
                                        @elseif($m->type === 'annual_rollover')
                                            <span class="status-tag">إقفال سنوي وترحيل رصيد</span>
                                        @else
                                            <span class="status-tag">{{ $m->type }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold {{ $isDebit ? 'text-debit' : '' }}">
                                        {{ $m->type === 'annual_rollover' ? '' : ($isDebit ? '-' : '+') }}{{ number_format($m->amount, 2) }} ج
                                    </td>
                                    <td class="text-center">{{ $m->account_name ?: '—' }}</td>
                                    <td class="text-muted">{{ $m->notes ?: '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">لا توجد حركات مالية مسجلة لحساب الإدارة حتى الآن.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                        <div class="small text-muted">
                            عرض <strong>{{ $movements->firstItem() ?? 0 }}</strong> إلى <strong>{{ $movements->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $movements->total() }}</strong> حركة
                        </div>
                        <div>
                            {{ $movements->appends(array_merge(request()->query(), ['tab' => 'movements']))->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- التاب 3: سجل تغير النسب والحصص --}}
                <div class="tab-pane fade {{ $activeTab === 'history' ? 'show active' : '' }}" id="tabAhmedShareHistory" role="tabpanel">
                    <div class="card-header-formal print-only">
                        <h6>سجل تاريخ تغير نسب وحصص حساب الإدارة ({{ $headName }})</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ والوقت</th>
                                    <th>العملية / السبب</th>
                                    <th>الرصيد قبل</th>
                                    <th>النسبة قبل (%)</th>
                                    <th>التغير في الرصيد</th>
                                    <th>الرصيد بعد</th>
                                    <th>النسبة بعد (%)</th>
                                    <th>فرق النسبة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shareHistoryLines ?? [] as $hl)
                                <tr>
                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                    <td class="text-center text-nowrap">{{ date('Y-m-d H:i', strtotime($hl->event_date)) }}</td>
                                    <td class="fw-bold">{{ $hl->event_title }}</td>
                                    <td class="text-center">{{ number_format($hl->capital_before, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="pct-tag">{{ number_format($hl->share_pct_before, 2) }}%</span>
                                    </td>
                                    <td class="text-center fw-bold {{ $hl->capital_change < 0 ? 'text-debit' : '' }}">
                                        {{ abs($hl->capital_change) >= 0.01 ? (($hl->capital_change > 0 ? '+' : '') . number_format($hl->capital_change, 2) . ' ج') : '—' }}
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($hl->capital_after, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="pct-tag">{{ number_format($hl->share_pct_after, 2) }}%</span>
                                    </td>
                                    <td class="text-center">
                                        @if($hl->share_pct_diff > 0.0001)
                                            <span class="status-tag">+{{ number_format($hl->share_pct_diff, 2) }}%</span>
                                        @elseif($hl->share_pct_diff < -0.0001)
                                            <span class="status-tag text-debit">{{ number_format($hl->share_pct_diff, 2) }}%</span>
                                        @else
                                            <span class="text-muted small">ثابتة</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">لا توجد سجلات تغير نسب مسجلة حتى الآن.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(is_object($shareHistoryLines) && method_exists($shareHistoryLines, 'links'))
                    <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                        <div class="small text-muted">
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
    const ctxCap = document.getElementById('chartAhmedCapital')?.getContext('2d');
    if (ctxCap) {
        new Chart(ctxCap, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'رصيد حساب الإدارة (ج)',
                    data: {!! json_encode($chartAhmedCapital) !!},
                    borderColor: '#0f172a',
                    backgroundColor: 'rgba(15, 23, 42, 0.06)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.25,
                    pointRadius: 4,
                    pointBackgroundColor: '#0f172a'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: false, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const ctxDoughnut = document.getElementById('chartOwnershipDoughnut')?.getContext('2d');
    if (ctxDoughnut) {
        const partnerNames = [{!! json_encode($headName) !!}];
        const partnerCaps = [{{ $ahmedCapital }}];
        const partnerColors = ['#0f172a'];

        const slatePalette = ['#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#1e293b'];
        let cIdx = 0;
        @foreach($partners as $p)
            partnerNames.push('{{ addslashes($p->name) }}');
            partnerCaps.push({{ $p->capital }});
            partnerColors.push(slatePalette[cIdx % slatePalette.length]);
            cIdx++;
        @endforeach

        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: partnerNames,
                datasets: [{
                    data: partnerCaps,
                    backgroundColor: partnerColors,
                    borderWidth: 1,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { family: 'Cairo', size: 11 } } }
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
                        backgroundColor: '#1e293b',
                        borderRadius: 3
                    },
                    {
                        label: 'عائد الرصيد المدور في حساب الإدارة (ج)',
                        data: {!! json_encode($chartAhmedOwnProfit) !!},
                        backgroundColor: '#94a3b8',
                        borderRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: { family: 'Cairo', size: 11 } } } },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, grid: { color: '#f1f5f9' } }
                }
            }
        });
    }
</script>

</body>
</html>
