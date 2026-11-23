<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملف الشريك: {{ $partner->name }} - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script src="{{ asset('vendor/html2canvas/html2canvas.min.js') }}"></script>
    <script src="{{ asset('vendor/jspdf/jspdf.umd.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'teal'])
    @include('partners._calm_theme')

    <style>
        .prt-statement-shell {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(13, 31, 53, 0.10);
            border-top: 4px solid var(--c-navy);
            border-radius: var(--p-radius-xl);
            padding: 26px 32px;
            box-shadow: var(--p-shadow-card);
            margin-bottom: 24px;
            position: relative;
        }
        body.theme-exited .prt-statement-shell {
            background: rgba(255, 252, 252, 0.97);
            border-color: rgba(185, 28, 28, 0.22);
            border-top-color: var(--c-danger);
        }

        .exited-banner-calm {
            background: linear-gradient(135deg, #fdf0f0 0%, #fae5e5 100%);
            border: 1px solid rgba(185, 28, 28, 0.22);
            border-right: 4px solid var(--c-danger);
            color: #8f1d1d;
            border-radius: var(--p-radius-md);
            padding: 14px 18px;
            margin-bottom: 18px;
        }

        /* الترويسة الرسمية الفخمة لكشف الحساب */
        .formal-statement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            background: linear-gradient(135deg, #e7f4f4 0%, #f1f6fc 52%, #fbf7eb 100%);
            border: 1px solid rgba(13, 115, 115, 0.18);
            border-bottom: 2px solid rgba(13, 31, 53, 0.14);
            border-radius: var(--p-radius-lg);
            padding: 20px 24px;
            margin-bottom: 20px;
        }
        .formal-statement-header .hdr-col { flex: 1 1 0; min-width: 0; }
        .formal-statement-header .hdr-center { flex: 1.35 1 0; text-align: center; min-width: 0; }
        .formal-statement-header .company-title { font-size: 1.12rem; font-weight: 700; color: var(--c-navy); margin: 0 0 3px; white-space: pre-line; }
        .formal-statement-header .company-sub { font-size: 0.77rem; color: var(--c-text-muted); margin-bottom: 8px; }
        .formal-statement-header .partner-code-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, 0.94);
            color: var(--c-accent);
            border: 1px solid rgba(13, 115, 115, 0.24);
            border-radius: 999px;
            padding: 3px 12px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .formal-statement-header .doc-official-kicker {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--c-navy);
            color: #ffffff;
            border-radius: 999px;
            padding: 2px 12px;
            font-size: 0.68rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .formal-statement-header .doc-main-title { font-size: 1.24rem; font-weight: 700; color: var(--c-navy); margin: 0 0 4px; }
        .formal-statement-header .doc-partner-name { font-size: 1.04rem; font-weight: 700; color: var(--c-accent); margin-bottom: 3px; }
        .formal-statement-header .doc-partner-meta { font-size: 0.78rem; color: var(--c-text-muted); }
        .formal-statement-header .hdr-meta-box {
            border: 1px solid rgba(13, 31, 53, 0.09);
            background: rgba(255, 255, 255, 0.88);
            border-radius: var(--p-radius-md);
            padding: 11px 14px;
            font-size: 0.77rem;
            text-align: right;
            line-height: 1.75;
            color: var(--c-text);
        }

        /* شريط مطابقة رحلة رأس المال */
        .stmt-reconcile-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px 18px;
            background: linear-gradient(90deg, #f3f8fb 0%, #f7fbfa 50%, #fbf9f2 100%);
            border: 1px solid rgba(13, 115, 115, 0.14);
            border-radius: var(--p-radius-md);
            padding: 11px 18px;
            margin-bottom: 22px;
            font-size: 0.79rem;
            color: var(--c-text-muted);
        }
        .stmt-reconcile-bar .rec-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .stmt-reconcile-bar .rec-chip strong {
            color: var(--c-navy);
            font-variant-numeric: tabular-nums;
        }

        /* قسم التقفيلات السنوية الرسمي البارز داخل كشف الحساب */
        .stmt-annual-section {
            background: #ffffff;
            border: 1px solid rgba(13, 115, 115, 0.18);
            border-radius: var(--p-radius-lg);
            overflow: hidden;
            margin-bottom: 22px;
            box-shadow: 0 3px 14px rgba(13, 31, 53, 0.04);
        }
        .stmt-annual-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #ecf6f6 0%, #f2f7fc 65%, #fbf8ee 100%);
            border-bottom: 1px solid rgba(13, 115, 115, 0.14);
        }
        .stmt-annual-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--c-navy);
        }
        .stmt-annual-sub {
            margin: 4px 0 0;
            font-size: 0.78rem;
            color: var(--c-text-muted);
        }

        /* شريط المراحل الزمنية (دخل بكام ← تقفيل كل سنة ← الآن) */
        .stmt-timeline-wrap {
            padding: 16px 20px;
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
        .stmt-milestone-card.is-now.is-loss {
            background: linear-gradient(155deg, #fdf0f0 0%, #ffffff 82%);
            border-color: rgba(185, 28, 28, 0.24);
            border-top: 3px solid var(--c-danger);
        }
        .stmt-ms-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--c-text-muted);
        }
        .stmt-ms-val {
            font-size: 1.08rem;
            font-weight: 700;
            color: var(--c-navy);
            font-variant-numeric: tabular-nums;
            line-height: 1.25;
        }
        .stmt-ms-foot {
            font-size: 0.72rem;
            color: var(--c-text-muted);
            font-variant-numeric: tabular-nums;
        }
        .stmt-timeline-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--c-accent);
            font-size: 0.85rem;
            opacity: 0.65;
            padding: 0 2px;
        }

        .proj-panel-body {
            padding: 22px 26px;
        }
        .proj-scenarios-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        .proj-scenario-card {
            border-radius: var(--p-radius-md);
            padding: 14px 20px;
            border: 1px solid rgba(13, 31, 53, 0.08);
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
        }
        .proj-scenario-card.is-emerald { background: linear-gradient(150deg, #ebf8f1 0%, #ffffff 78%); border-color: rgba(45, 134, 89, 0.22); border-right: 4px solid var(--c-success); }
        .proj-scenario-card.is-blue    { background: linear-gradient(150deg, #edf4fc 0%, #ffffff 78%); border-color: rgba(30, 95, 164, 0.22); border-right: 4px solid var(--c-info); }
        .proj-scenario-card.is-amber   { background: linear-gradient(150deg, #fcf6e6 0%, #ffffff 78%); border-color: rgba(182, 124, 31, 0.24); border-right: 4px solid var(--c-warning); }
        .proj-chart-wrap {
            height: 300px;
            position: relative;
            width: 100%;
            max-width: 100%;
            padding: 8px 6px 0;
        }

        .proj-toggle-group {
            display: inline-flex;
            gap: 3px;
            background: #eef4f8;
            border: 1px solid #d9e4ee;
            border-radius: 999px;
            padding: 3px;
        }
        .proj-toggle-group button {
            border: none;
            background: transparent;
            color: var(--c-text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.16s ease;
        }
        .proj-toggle-group button.active {
            background: var(--c-accent);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(13, 115, 115, 0.22);
        }

        .formal-sig-box {
            border: 1px solid #cbd5e1;
            background: #fcfcfd;
            border-radius: 8px;
            padding: 12px 10px;
            text-align: center;
            height: 100%;
        }

        .print-only { display: none; }
        .export-print-only { display: none; }

        /* تنسيق التصدير كصورة / PDF (يضمن سلامة اتصال الحروف العربية في html2canvas) */
        #partnerProfileExportArea.is-exporting,
        #partnerProfileExportArea.is-exporting *:not(i):not(.fa):not(.fas):not(.far):not(.fab):not(.fa-solid):not(.fa-regular):not(.fa-brands) {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif !important;
            letter-spacing: 0px !important;
            word-spacing: normal !important;
            font-feature-settings: normal !important;
            font-variant-ligatures: normal !important;
        }
        #partnerProfileExportArea.is-exporting {
            background: #ffffff !important;
            border: 2px solid #0d1f35 !important;
            border-top: 5px solid #0d1f35 !important;
            border-radius: 8px !important;
            padding: 28px 36px !important;
            box-shadow: none !important;
            color: #0d1f35 !important;
        }
        #partnerProfileExportArea.is-exporting .proj-panel-body {
            padding: 18px 22px !important;
        }
        #partnerProfileExportArea.is-exporting .proj-scenarios-stack {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
        }
        #partnerProfileExportArea.is-exporting .export-print-only { display: block !important; }
        #partnerProfileExportArea.is-exporting #annualStatementSection {
            display: block !important;
            border: 1px solid #94a3b8 !important;
            box-shadow: none !important;
        }
        #partnerProfileExportArea.is-exporting #tabMovements,
        #partnerProfileExportArea.is-exporting .tab-content > .tab-pane.active {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        #partnerProfileExportArea.is-exporting .tab-content > .tab-pane:not(.active):not(#tabMovements) {
            display: none !important;
        }
        #partnerProfileExportArea.is-exporting .prt-table thead th {
            background: #0d1f35 !important;
            color: #ffffff !important;
            border: 1px solid #0d1f35 !important;
            padding: 8px 9px !important;
            font-size: 0.78rem !important;
            text-align: center !important;
        }
        #partnerProfileExportArea.is-exporting .prt-table tbody td {
            border: 1px solid #cbd5e1 !important;
            padding: 8px 9px !important;
            font-size: 0.8rem !important;
            color: #0d1f35 !important;
            background: #ffffff !important;
        }
        #partnerProfileExportArea.is-exporting .prt-table tfoot td {
            border: 1px solid #0d1f35 !important;
            border-top: 2px solid #0d1f35 !important;
            padding: 8px 9px !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            color: #0d1f35 !important;
            background: #eef4f7 !important;
        }

        @media (max-width: 767px) {
            .formal-statement-header { flex-direction: column; text-align: center; }
            .formal-statement-header .hdr-col { width: 100%; text-align: center !important; }
            .prt-statement-shell { padding: 14px; }
            .stmt-timeline-arrow { display: none; }
        }

        @media print {
            @page { size: A4 portrait; margin: 10mm 11mm 11mm 11mm; }
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
            .prt-tabs-row, .exited-banner-calm, .prt-panel-foot, .pagination, .modal,
            #btnGroupTimeframe, #btnGroupRateMode {
                display: none !important;
            }
            .print-only, .export-print-only { display: block !important; }
            .main-content { margin: 0 !important; padding: 0 3mm !important; width: 100% !important; min-width: 100% !important; max-width: 100% !important; }
            .container-fluid { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            .prt-statement-shell {
                border: none !important;
                padding: 2mm 2mm !important;
                margin-bottom: 8px !important;
                background: #ffffff !important;
                border-radius: 0 !important;
            }
            .prt-panel {
                border: 1px solid #cbd5e1 !important;
                padding: 0 !important;
                margin-bottom: 10px !important;
                background: #ffffff !important;
                border-radius: 4px !important;
            }
            #projectionStatementPanel {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .proj-panel-body {
                padding: 10px 14px !important;
            }
            .proj-scenarios-stack {
                display: flex !important;
                flex-direction: column !important;
                gap: 7px !important;
                margin-bottom: 12px !important;
            }
            .proj-scenario-card {
                width: 100% !important;
                padding: 8px 12px !important;
                border-radius: 5px !important;
                gap: 4px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .proj-chart-wrap {
                width: 100% !important;
                max-width: 100% !important;
                height: 215px !important;
                padding: 4px 2px 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            #chartProfitProjections {
                width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                display: block !important;
            }
            .formal-statement-header {
                background: #f8fbfc !important;
                border: 1px solid #94a3b8 !important;
                border-top: 3px solid #0d1f35 !important;
                border-bottom: 2px solid #0d1f35 !important;
                border-radius: 4px !important;
                padding: 8px 12px !important;
                margin-bottom: 10px !important;
            }
            .formal-statement-header .doc-main-title { font-size: 11pt !important; }
            .formal-statement-header .doc-partner-name { font-size: 9.5pt !important; }
            .formal-statement-header .company-title { font-size: 9.5pt !important; }
            .formal-statement-header .hdr-meta-box { font-size: 6.8pt !important; padding: 5px 8px !important; }

            #partnerProfileExportArea .prt-statement-kpis {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 6px !important;
                margin: 0 0 8px 0 !important;
            }
            #partnerProfileExportArea .prt-statement-kpis > [class*="col-"] {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
            #partnerProfileExportArea .prt-kpi-card {
                padding: 7px 8px !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 4px !important;
                background: #ffffff !important;
            }
            #partnerProfileExportArea .prt-kpi-card .kpi-val { font-size: 9.8pt !important; }
            #partnerProfileExportArea .prt-kpi-card .kpi-lbl { font-size: 6.6pt !important; }
            #partnerProfileExportArea .prt-kpi-card .kpi-sub { font-size: 6.1pt !important; }

            .stmt-reconcile-bar {
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                padding: 5px 10px !important;
                margin-bottom: 10px !important;
                font-size: 6.8pt !important;
                gap: 6px 12px !important;
            }

            .stmt-annual-section {
                border: 1px solid #94a3b8 !important;
                border-radius: 4px !important;
                margin-bottom: 10px !important;
                page-break-inside: avoid !important;
            }
            .stmt-annual-head {
                background: #f1f5f9 !important;
                padding: 6px 10px !important;
                border-bottom: 1.5px solid #0d1f35 !important;
            }
            .stmt-annual-title { font-size: 8.6pt !important; }
            .stmt-annual-sub { font-size: 6.6pt !important; }
            .stmt-timeline-wrap { padding: 7px 10px !important; background: #ffffff !important; }
            .stmt-timeline-ribbon { gap: 6px !important; }
            .stmt-milestone-card {
                min-width: 110px !important;
                padding: 5px 8px !important;
                border: 1px solid #cbd5e1 !important;
                background: #f8fafc !important;
                border-radius: 4px !important;
            }
            .stmt-ms-top { font-size: 6.2pt !important; }
            .stmt-ms-val { font-size: 8.6pt !important; }
            .stmt-ms-foot { font-size: 6.2pt !important; }

            .prt-chart-card.no-print { display: none !important; }
            .tab-content > .tab-pane:not(.active):not(#tabMovements) { display: none !important; }
            #tabMovements, .tab-content > .tab-pane.active { display: block !important; opacity: 1 !important; }
            .prt-panel-head { background: #f1f5f9 !important; padding: 6px 10px !important; }
            .prt-panel-title { font-size: 8.4pt !important; }
            .prt-table-wrap { overflow: visible !important; }
            .prt-table { width: 100% !important; border-collapse: collapse !important; font-size: 6.9pt !important; }
            .prt-table thead { display: table-header-group !important; }
            .prt-table thead th {
                background-color: #0d1f35 !important;
                color: #ffffff !important;
                border: 1px solid #0d1f35 !important;
                padding: 4px 3px !important;
                font-size: 6.5pt !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .prt-table tbody td {
                border: 1px solid #cbd5e1 !important;
                padding: 3.5px 3px !important;
                font-size: 6.9pt !important;
                color: #0d1f35 !important;
                background: #ffffff !important;
            }
            .prt-table tfoot td {
                border: 1px solid #0d1f35 !important;
                border-top: 2px solid #0d1f35 !important;
                padding: 4px 3px !important;
                font-size: 7pt !important;
                font-weight: 700 !important;
                color: #0d1f35 !important;
                background: #eef4f7 !important;
            }
            .prt-table tr { page-break-inside: avoid !important; }
            .prt-chip { border: 1px solid #94a3b8 !important; background: #f8fafc !important; color: #000000 !important; font-size: 6.3pt !important; padding: 1px 4px !important; }
            .formal-sig-box { border: 1px solid #94a3b8 !important; padding: 8px 6px !important; }
        }
    </style>
</head>
<body class="{{ $partner->status === 'exited' ? 'theme-exited' : '' }}">

@include('sidebar')

@php
    $companyName = trim((string) \App\Services\SystemSetting::get('invoice_header_text', ''));
    if ($companyName === '') {
        $companyName = 'شركة الضبع للتجارة والتوزيع';
    }

    $activeTab = request('tab');
    if (!$activeTab) {
        $activeTab = (request()->has('lines_page') && !request()->has('movements_page'))
            ? 'periods'
            : 'movements';
    }
    if (!in_array($activeTab, ['movements', 'periods'], true)) {
        $activeTab = 'movements';
    }

    $annualLines              = $annualLines ?? collect();
    $foundingCap              = round((float) ($partner->initial_capital ?? 0), 2);
    $currentCap               = round((float) $partner->capital, 2);
    $totalCumGrowth           = round($currentCap - $foundingCap, 2);
    $totalCumGrowthPct        = $foundingCap > 0 ? round(($totalCumGrowth / $foundingCap) * 100, 2) : null;
    $directCapitalAdditions   = round((float) ($directCapitalAdditions ?? 0), 2);
    $directCapitalWithdrawals = round((float) ($directCapitalWithdrawals ?? 0), 2);
    $totalAnnualProfits       = round((float) $annualLines->sum('year_net_profits'), 2);
    $totalAnnualWithdrawn     = round((float) $annualLines->sum('year_withdrawn'), 2);
    $latestAnnual             = $annualLines->last();
@endphp

<div class="main-content">
    <div class="container-fluid px-0">

        {{-- ترويسة الصفحة وأزرار التحكم --}}
        <div class="prt-header no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="prt-back-link">
                    <i class="fa fa-arrow-right fa-xs"></i>
                    <span>العودة إلى سجل الشركاء</span>
                </a>
                <div class="prt-title-row">
                    <span class="prt-title-badge"><i class="fa fa-user-tie"></i></span>
                    <h1 class="prt-title">ملف الشريك: {{ $partner->name }}</h1>
                    @if($partner->status === 'exited')
                        <span class="prt-chip is-danger">متخارج</span>
                    @else
                        <span class="prt-chip is-success">نشط ومستمر</span>
                    @endif
                </div>
                <p class="prt-subtitle">
                    <i class="fa fa-phone fa-xs me-1"></i>{{ $partner->phone ?: 'بدون هاتف مسجل' }}
                    · تاريخ الانضمام: {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '—' }}
                </p>
            </div>

            <div class="prt-actions align-self-end">
                <button type="button" class="prt-pill-btn is-whatsapp is-sm" onclick="openWhatsAppModal()">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>إرسال عبر واتساب</span>
                </button>
                <button type="button" class="prt-pill-btn is-blue is-sm" onclick="exportProfileAsImage()" id="btnExportImage" title="تحميل كشف الحساب كصورة PNG">
                    <i class="fa fa-image fa-xs"></i>
                    <span>حفظ كصورة</span>
                </button>
                <button type="button" class="prt-pill-btn is-rose is-sm" onclick="exportProfileAsPDF()" id="btnExportPDF" title="تحميل كشف الحساب كملف PDF">
                    <i class="fa fa-file-pdf fa-xs"></i>
                    <span>حفظ PDF</span>
                </button>
                <button type="button" class="prt-pill-btn is-ghost is-sm" onclick="window.print()">
                    <i class="fa fa-print fa-xs"></i>
                    <span>طباعة كشف الحساب</span>
                </button>
                @if($partner->status === 'active')
                    <button type="button" class="prt-pill-btn is-emerald is-sm" onclick="openAddCapitalModal()">
                        <i class="fa fa-plus fa-xs"></i>
                        <span>زيادة رأس المال</span>
                    </button>
                    <button type="button" class="prt-pill-btn is-amber is-sm" onclick="openWithdrawCapitalModal()">
                        <i class="fa fa-minus fa-xs"></i>
                        <span>سحب من رأس المال</span>
                    </button>
                @endif
                <button type="button" class="prt-pill-btn is-navy is-sm" onclick="openEditPartnerModal()">
                    <i class="fa fa-pen fa-xs"></i>
                    <span>تعديل النسبة والبيانات</span>
                </button>
            </div>
        </div>

        {{-- التنبيهات --}}
        @if(session('success'))
            <div class="alert-pro success no-print"><i class="fa fa-check-circle"></i><span>{{ session('success') }}</span></div>
        @endif
        @if(session('error'))
            <div class="alert-pro danger no-print"><i class="fa fa-exclamation-triangle"></i><span>{{ session('error') }}</span></div>
        @endif
        @if($errors->any())
            <div class="alert-pro danger no-print"><i class="fa fa-exclamation-circle"></i><span>{{ $errors->first() }}</span></div>
        @endif

        @if($partner->status === 'active' && (float)$partner->capital == 0 && (float)$partner->initial_capital > 0)
            <div class="alert-pro warning justify-content-between flex-wrap gap-3 no-print">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-wallet"></i>
                    <div>
                        <strong>تنبيه: لم يتم تحديد المحفظة لإيداع رأس المال التأسيسي ({{ number_format($partner->initial_capital, 2) }} ج) بعد!</strong>
                        <div class="small text-muted">يرجى اختيار المحفظة لتفعيل نسبة الشريك في الدورة وتسجيلها في السجل.</div>
                    </div>
                </div>
                <button type="button" class="prt-pill-btn is-primary is-sm" onclick="openAddCapitalModal({{ (float)$partner->initial_capital }})">
                    <i class="fa fa-check-circle fa-xs"></i> اختيار المحفظة وإيداع ({{ number_format($partner->initial_capital, 2) }} ج)
                </button>
            </div>
        @endif

        @if($partner->status === 'exited')
            <div class="exited-banner-calm d-flex align-items-center justify-content-between flex-wrap gap-2 no-print">
                <div>
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <strong>حساب شريك متخارج رسمياً:</strong> تمت تصفية كامل رأس المال وصرف المستحقات من الخزينة، وتم إيقاف هذا الحساب عن الدورات الشهرية مع الاحتفاظ بالأرشيف التاريخي.
                </div>
                <span class="prt-chip is-danger">متخارج / مصفى</span>
            </div>
        @endif

        {{-- منطقة كشف الحساب الرسمي القابلة للطباعة والحفظ كصورة أو PDF --}}
        <div id="partnerProfileExportArea" class="prt-statement-shell">
            {{-- الترويسة الرسمية الفخمة لكشف الحساب --}}
            <div class="formal-statement-header">
                <div class="hdr-col text-end">
                    <div class="company-title">{{ $companyName }}</div>
                    <div class="company-sub">وحدة الحسابات والرقابة المالية وإدارة حصص الشركاء</div>
                    <span class="partner-code-pill">
                        <i class="fa fa-id-badge fa-xs"></i>
                        <span>كود الشريك: PRT-{{ str_pad($partner->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </span>
                </div>
                <div class="hdr-center">
                    <span class="doc-official-kicker">
                        <i class="fa fa-shield-alt fa-xs"></i>
                        <span>وثيقة مالية رسمية معتمدة</span>
                    </span>
                    <div class="doc-main-title">كشف حساب شريك شامل</div>
                    <div class="doc-partner-name">الشريك: {{ $partner->name }}</div>
                    <div class="doc-partner-meta">
                        الهاتف: {{ $partner->phone ?: '—' }} · تاريخ الانضمام: {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '—' }}
                    </div>
                </div>
                <div class="hdr-col">
                    <div class="hdr-meta-box">
                        <div>
                            <strong>حالة الحساب:</strong>
                            <span class="fw-semibold" style="color: {{ $partner->status === 'active' ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                {{ $partner->status === 'active' ? 'شريك نشط ومستمر' : 'حساب مصفى / متخارج' }}
                            </span>
                        </div>
                        <div><strong>تاريخ الإصدار:</strong> <span dir="ltr">{{ date('Y-m-d H:i') }}</span></div>
                        <div><strong>التقفيلات السنوية:</strong> {{ $annualLines->count() }} سنة مقفلة</div>
                        <div><strong>ملخص السجل:</strong> {{ $lines->total() }} دورة · {{ $movements->total() }} حركة</div>
                    </div>
                </div>
            </div>

            {{-- كروت رحلة رأس المال الفردية (دخل بكام ← بقا معاه كام ← كسب/زاد كام) --}}
            <div class="row g-3 mb-3 prt-statement-kpis">
                <div class="col-md-3 col-sm-6">
                    <div class="prt-kpi-card is-blue">
                        <div>
                            <div class="prt-kpi-top">
                                <div class="kpi-lbl">رأس المال التأسيسي (دخل بكام)</div>
                                <span class="prt-kpi-ico"><i class="fa fa-lock"></i></span>
                            </div>
                            <div class="kpi-val"><span dir="ltr">{{ number_format($foundingCap, 2) }}</span><span class="kpi-cur">ج</span></div>
                        </div>
                        <div class="kpi-sub">
                            ثابت ومحفوظ منذ {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : 'بداية الشراكة' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="prt-kpi-card is-teal">
                        <div>
                            <div class="prt-kpi-top">
                                <div class="kpi-lbl">الرصيد الحالي المعتمد (بقا كام)</div>
                                <span class="prt-kpi-ico"><i class="fa fa-vault"></i></span>
                            </div>
                            <div class="kpi-val"><span dir="ltr">{{ number_format($currentCap, 2) }}</span><span class="kpi-cur">ج</span></div>
                        </div>
                        <div class="kpi-sub">
                            نسبة المساهمة: <strong>{{ number_format($capitalSharePct, 2) }}%</strong>
                            · صافي حصته: <strong style="color: var(--c-success);">{{ number_format($effectiveProfitPct, 2) }}%</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="prt-kpi-card {{ $totalCumGrowth >= 0 ? 'is-emerald' : 'is-rose' }}">
                        <div>
                            <div class="prt-kpi-top">
                                <div class="kpi-lbl">إجمالي النمو منذ البداية (زاد / كسب كام)</div>
                                <span class="prt-kpi-ico"><i class="fa fa-arrow-trend-up"></i></span>
                            </div>
                            <div class="kpi-val {{ $totalCumGrowth >= 0 ? 'is-success' : 'is-danger' }}">
                                <span dir="ltr">{{ $totalCumGrowth >= 0 ? '+' : '' }}{{ number_format($totalCumGrowth, 2) }}</span><span class="kpi-cur">ج</span>
                            </div>
                        </div>
                        <div class="kpi-sub">
                            @if($totalCumGrowthPct !== null)
                                نسبة النمو: <strong style="color: {{ $totalCumGrowthPct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">{{ $totalCumGrowthPct >= 0 ? '+' : '' }}{{ number_format($totalCumGrowthPct, 2) }}%</strong> ·
                            @endif
                            أرباح الدورات: <strong style="color: var(--c-success);">+{{ number_format($totalNetEarned, 2) }} ج</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="prt-kpi-card is-amber">
                        <div>
                            <div class="prt-kpi-top">
                                <div class="kpi-lbl">تدوير الأرباح مقابل السحب النقدي</div>
                                <span class="prt-kpi-ico"><i class="fa fa-arrows-split-up-and-left"></i></span>
                            </div>
                            <div class="kpi-val"><span dir="ltr">{{ number_format($totalReinvested, 2) }}</span><span class="kpi-cur">ج مدوّر</span></div>
                        </div>
                        <div class="kpi-sub">
                            مسحوب نقداً: <strong style="color: var(--c-danger);">{{ number_format($totalWithdrawn, 2) }} ج</strong>
                            · الإدارة: <strong>{{ number_format($partner->ahmed_ratio, 1) }}%</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- شريط مطابقة مختصر يوضح حركة رأس المال الكاملة --}}
            <div class="stmt-reconcile-bar">
                <span class="rec-chip">
                    <i class="fa fa-lock fa-xs" style="color: var(--c-info);"></i>
                    التأسيسي: <strong>{{ number_format($foundingCap, 2) }} ج</strong>
                </span>
                @if($directCapitalAdditions > 0)
                    <span class="rec-chip">
                        <i class="fa fa-plus-circle fa-xs" style="color: var(--c-success);"></i>
                        إيداعات زيادة رأس مال: <strong style="color: var(--c-success);">+{{ number_format($directCapitalAdditions, 2) }} ج</strong>
                    </span>
                @endif
                @if($directCapitalWithdrawals > 0)
                    <span class="rec-chip">
                        <i class="fa fa-minus-circle fa-xs" style="color: var(--c-danger);"></i>
                        مسحوبات من الأصل: <strong style="color: var(--c-danger);">-{{ number_format($directCapitalWithdrawals, 2) }} ج</strong>
                    </span>
                @endif
                <span class="rec-chip">
                    <i class="fa fa-chart-line fa-xs" style="color: var(--c-success);"></i>
                    صافي أرباح الدورات: <strong style="color: var(--c-success);">+{{ number_format($totalNetEarned, 2) }} ج</strong>
                </span>
                <span class="rec-chip">
                    <i class="fa fa-wallet fa-xs" style="color: var(--c-accent);"></i>
                    الرصيد المعتمد حالياً: <strong style="color: var(--c-accent);">{{ number_format($currentCap, 2) }} ج</strong>
                </span>
            </div>

            {{-- ══ أولاً: قسم التقفيلات السنوية الرسمي (يظهر دائماً على الشاشة وفي الطباعة والـ PDF والصورة) ══ --}}
            <div id="annualStatementSection" class="stmt-annual-section">
                <div class="stmt-annual-head">
                    <div>
                        <h5 class="stmt-annual-title">
                            <span class="prt-panel-title-ico"><i class="fa fa-calendar-check"></i></span>
                            <span>أولاً: بيان التقفيلات السنوية ورحلة نمو رأس المال</span>
                        </h5>
                        <p class="stmt-annual-sub">
                            يوضح المبلغ التأسيسي الذي بدأ به الشريك ورصيده المعتمد ومقدار الزيادة والمكسب في نهاية كل سنة مالية.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="prt-chip is-info">
                            <i class="fa fa-lock fa-xs"></i> التأسيسي الثابت: {{ number_format($foundingCap, 2) }} ج
                        </span>
                        <span class="prt-chip is-accent">
                            {{ $annualLines->count() }} {{ $annualLines->count() === 1 ? 'سنة مقفلة' : 'سنوات مقفلة' }}
                        </span>
                    </div>
                </div>

                {{-- شريط المراحل الزمني: دخل بكام ← تقفيل كل سنة ← الوضع الحالي الآن --}}
                <div class="stmt-timeline-wrap">
                    <div class="stmt-timeline-ribbon">
                        {{-- محطة البداية: التأسيس --}}
                        <div class="stmt-milestone-card is-start">
                            <div class="stmt-ms-top">
                                <span><i class="fa fa-lock fa-xs me-1" style="color: var(--c-info);"></i> بداية الشراكة (التأسيسي)</span>
                                <span style="color: var(--c-info);">ثابت</span>
                            </div>
                            <div class="stmt-ms-val">{{ number_format($foundingCap, 2) }} <span class="small fw-normal text-muted">ج</span></div>
                            <div class="stmt-ms-foot">تاريخ الانضمام: {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '—' }}</div>
                        </div>

                        @foreach($annualLines as $al)
                            <div class="stmt-timeline-arrow" aria-hidden="true">
                                <i class="fa fa-arrow-left"></i>
                            </div>
                            <div class="stmt-milestone-card is-year">
                                <div class="stmt-ms-top">
                                    <span><i class="fa fa-calendar-alt fa-xs me-1" style="color: var(--c-accent);"></i> إقفال سنة {{ $al->closing_year }}</span>
                                    <span style="color: var(--c-accent);">{{ number_format($al->closing_share_pct, 2) }}%</span>
                                </div>
                                <div class="stmt-ms-val">{{ number_format($al->closing_capital, 2) }} <span class="small fw-normal text-muted">ج</span></div>
                                <div class="stmt-ms-foot fw-semibold" style="color: {{ $al->annual_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $al->annual_gain >= 0 ? '+' : '' }}{{ number_format($al->annual_gain, 2) }} ج خلال السنة
                                    @if((float) $al->opening_capital > 0)
                                        ({{ $al->annual_gain_pct >= 0 ? '+' : '' }}{{ number_format($al->annual_gain_pct, 1) }}%)
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="stmt-timeline-arrow" aria-hidden="true">
                            <i class="fa fa-arrow-left"></i>
                        </div>

                        {{-- محطة الوضع الحالي الآن --}}
                        <div class="stmt-milestone-card is-now {{ $totalCumGrowth < 0 ? 'is-loss' : '' }}">
                            <div class="stmt-ms-top">
                                <span><i class="fa fa-star fa-xs me-1" style="color: {{ $totalCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};"></i> الرصيد المعتمد الآن</span>
                                <span style="color: {{ $totalCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $totalCumGrowthPct !== null ? (($totalCumGrowthPct >= 0 ? '+' : '') . number_format($totalCumGrowthPct, 2) . '%') : 'حالي' }}
                                </span>
                            </div>
                            <div class="stmt-ms-val">{{ number_format($currentCap, 2) }} <span class="small fw-normal text-muted">ج</span></div>
                            <div class="stmt-ms-foot fw-semibold" style="color: {{ $totalCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                إجمالي النمو منذ التأسيس: {{ $totalCumGrowth >= 0 ? '+' : '' }}{{ number_format($totalCumGrowth, 2) }} ج
                            </div>
                        </div>
                    </div>
                </div>

                {{-- جدول التقفيلات السنوية التفصيلي --}}
                <div class="prt-table-wrap">
                    <table class="prt-table">
                        <thead>
                            <tr>
                                <th class="text-center">السنة المالية</th>
                                <th class="text-center th-soft-before"><i class="fa fa-lock fa-xs me-1"></i> التأسيسي (دخل بكام)</th>
                                <th class="text-center">رصيد بداية السنة</th>
                                <th class="text-center th-soft-after">رصيد نهاية السنة (بقا كام)</th>
                                <th class="text-center">الزيادة / المكسب في السنة</th>
                                <th class="text-center">أرباح الدورات بالسنة</th>
                                <th class="text-center">المسحوب نقداً</th>
                                <th class="text-center">إجمالي النمو منذ التأسيس</th>
                                <th class="text-center">الحصة بنهاية السنة</th>
                                <th class="text-center no-print no-export">تقرير السنة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($annualLines as $al)
                                <tr>
                                    <td class="text-center"><span class="prt-chip is-accent">سنة {{ $al->closing_year }}</span></td>
                                    <td class="text-center prt-num-main">{{ number_format($al->initial_capital, 2) }} ج</td>
                                    <td class="text-center text-muted">{{ number_format($al->opening_capital, 2) }} ج</td>
                                    <td class="text-center prt-num-main">{{ number_format($al->closing_capital, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="prt-chip {{ $al->annual_gain >= 0 ? 'is-success' : 'is-danger' }}">
                                            {{ $al->annual_gain >= 0 ? '+' : '' }}{{ number_format($al->annual_gain, 2) }} ج
                                        </span>
                                        @if((float) $al->opening_capital > 0)
                                            <div class="prt-num-sub fw-semibold" style="color: {{ $al->annual_gain_pct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                {{ $al->annual_gain_pct >= 0 ? '+' : '' }}{{ number_format($al->annual_gain_pct, 2) }}%
                                            </div>
                                        @endif
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
                                        @if((float) $al->initial_capital > 0)
                                            <div class="prt-num-sub fw-semibold" style="color: {{ $al->cumulative_gain_pct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                {{ $al->cumulative_gain_pct >= 0 ? '+' : '' }}{{ number_format($al->cumulative_gain_pct, 2) }}%
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="prt-chip is-info">{{ number_format($al->closing_share_pct, 2) }}%</span></td>
                                    <td class="text-center no-print no-export">
                                        <a href="{{ route('partners.annual.show', $al->closing_year) }}" class="prt-pill-btn is-ghost is-sm py-1 px-3">
                                            <i class="fa fa-print fa-xs"></i> التقرير
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        لا توجد تقفيلات سنوية مقفلة لهذا الشريك حتى الآن — يظهر ملخص كل سنة هنا تلقائياً فور اعتماد الإقفال السنوي.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($annualLines->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td class="text-center">الوضع الحالي</td>
                                    <td class="text-center">{{ number_format($foundingCap, 2) }} ج</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center">{{ number_format($currentCap, 2) }} ج</td>
                                    <td class="text-center" style="color: {{ $totalCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        {{ $totalCumGrowth >= 0 ? '+' : '' }}{{ number_format($totalCumGrowth, 2) }} ج
                                    </td>
                                    <td class="text-center" style="color: var(--c-info);">
                                        {{ $totalAnnualProfits != 0 ? (($totalAnnualProfits > 0 ? '+' : '') . number_format($totalAnnualProfits, 2) . ' ج') : '—' }}
                                    </td>
                                    <td class="text-center" style="color: {{ $totalAnnualWithdrawn > 0 ? 'var(--c-danger)' : 'var(--c-text-muted)' }};">
                                        {{ $totalAnnualWithdrawn > 0 ? '-' . number_format($totalAnnualWithdrawn, 2) . ' ج' : '—' }}
                                    </td>
                                    <td class="text-center" style="color: {{ $totalCumGrowth >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        {{ $totalCumGrowth >= 0 ? '+' : '' }}{{ number_format($totalCumGrowth, 2) }} ج
                                        @if($totalCumGrowthPct !== null)
                                            <small class="d-block">({{ $totalCumGrowthPct >= 0 ? '+' : '' }}{{ number_format($totalCumGrowthPct, 2) }}%)</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($capitalSharePct, 2) }}%</td>
                                    <td class="no-print no-export"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- شريط التبويبات الكبسولي للجداول التفصيلية --}}
            <div class="prt-tabs-row no-print no-export">
                <ul class="nav prt-tabs-capsule" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab === 'movements' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMovements" type="button">
                            <i class="fa fa-exchange-alt fa-xs"></i>
                            <span>الحركات المالية المباشرة</span>
                            <span class="prt-tab-count">{{ $movements->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab === 'periods' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabPeriods" type="button">
                            <i class="fa fa-calendar-check fa-xs"></i>
                            <span>سجل الدورات الشهرية</span>
                            <span class="prt-tab-count">{{ $lines->total() }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- الجداول التفصيلية --}}
            <div class="prt-panel mb-4">
                <div class="tab-content p-0">
                    {{-- التاب 1: الحركات المالية المباشرة --}}
                    <div class="tab-pane fade {{ $activeTab === 'movements' ? 'show active' : '' }}" id="tabMovements">
                        <div class="prt-panel-head">
                            <h5 class="prt-panel-title">
                                <span class="prt-panel-title-ico"><i class="fa fa-exchange-alt"></i></span>
                                <span>ثانياً: بيان سجل الحركات المالية المباشرة (الإيداعات والمسحوبات وتدوير الأرباح)</span>
                            </h5>
                            <span class="prt-chip is-neutral">إجمالي الحركات: {{ $movements->total() }} حركة</span>
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
                                        <tr>
                                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                            <td class="text-center text-muted small" dir="ltr">{{ date('Y-m-d H:i', strtotime($m->created_at)) }}</td>
                                            <td class="text-center">
                                                @if($m->type === 'initial_deposit')
                                                    <span class="prt-chip is-success">إيداع رأس مال تأسيسي</span>
                                                @elseif($m->type === 'capital_increase')
                                                    <span class="prt-chip is-info">زيادة رأس مال</span>
                                                @elseif($m->type === 'capital_withdrawal')
                                                    <span class="prt-chip is-danger">سحب من رأس المال</span>
                                                @elseif($m->type === 'profit_withdrawal')
                                                    <span class="prt-chip is-warning">صرف أرباح دورية</span>
                                                @elseif($m->type === 'profit_reinvest')
                                                    <span class="prt-chip is-accent">تدوير أرباح بالرصيد</span>
                                                @elseif($m->type === 'annual_rollover')
                                                    <span class="prt-chip is-warning">إقفال سنوي (حفظ رصيد نهاية العام)</span>
                                                @else
                                                    <span class="prt-chip is-neutral">{{ $m->type }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold" style="color: {{ $m->type === 'annual_rollover' ? 'var(--c-info)' : (in_array($m->type, ['initial_deposit', 'capital_increase', 'profit_reinvest']) ? 'var(--c-success)' : 'var(--c-danger)') }};">
                                                <span dir="ltr">{{ $m->type === 'annual_rollover' ? '' : (in_array($m->type, ['initial_deposit', 'capital_increase', 'profit_reinvest']) ? '+' : '-') }}{{ number_format($m->amount, 2) }}</span> ج
                                            </td>
                                            <td class="text-center fw-semibold" style="color: var(--c-navy);">{{ $m->account_name ?: '—' }}</td>
                                            <td class="text-muted">{{ $m->notes ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">لا توجد حركات مالية مسجلة لهذا الشريك.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="prt-panel-foot no-print no-export">
                            <div>
                                عرض <strong>{{ $movements->firstItem() ?? 0 }}</strong> إلى <strong>{{ $movements->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $movements->total() }}</strong> حركة
                            </div>
                            <div>
                                {{ $movements->appends(array_merge(request()->query(), ['tab' => 'movements']))->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>

                    {{-- التاب 2: التقفيلات الشهرية --}}
                    <div class="tab-pane fade {{ $activeTab === 'periods' ? 'show active' : '' }}" id="tabPeriods">
                        <div class="prt-panel-head">
                            <h5 class="prt-panel-title">
                                <span class="prt-panel-title-ico"><i class="fa fa-calendar-check"></i></span>
                                <span>بيان سجل الدورات الشهرية المعتمدة وتوزيعات الأرباح</span>
                            </h5>
                            <span class="prt-chip is-neutral">إجمالي الدورات: {{ $lines->total() }} دورة</span>
                        </div>
                        <div class="prt-table-wrap">
                            <table class="prt-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 44px;">#</th>
                                        <th>الدورة الشهرية</th>
                                        <th>الرصيد قبل</th>
                                        <th class="text-center th-soft-before">النسبة كانت كام</th>
                                        <th>ربح الحصة (g)</th>
                                        <th>نسبة الإدارة (a)</th>
                                        <th>صافي ربح الشريك (n)</th>
                                        <th>المسحوب نقداً</th>
                                        <th>المدوّر بالرصيد</th>
                                        <th>الرصيد بعد</th>
                                        <th class="text-center th-soft-after">النسبة بقت كام</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lines as $l)
                                        @php
                                            $openPct  = (float) $l->capital_share_pct;
                                            $closePct = (float) ($l->closing_share_pct ?? 0);
                                            $pctDiff  = round($closePct - $openPct, 4);
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                            <td class="fw-semibold" style="color: var(--c-navy);">{{ $l->period_name ?: $l->end_date }}</td>
                                            <td>{{ number_format($l->opening_capital, 2) }} ج</td>
                                            <td class="text-center"><span class="prt-chip is-neutral">{{ number_format($openPct, 2) }}%</span></td>
                                            <td class="fw-semibold" style="color: {{ $l->gross_share >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                {{ $l->gross_share >= 0 ? '+' : '' }}{{ number_format($l->gross_share, 2) }} ج
                                            </td>
                                            <td class="fw-semibold" style="color: var(--c-navy);">
                                                +{{ number_format($l->ahmed_commission, 2) }} ج
                                                <span class="prt-chip is-neutral ms-1" style="font-size:0.66rem; padding:1px 6px;">{{ number_format($l->ahmed_ratio_pct, 0) }}%</span>
                                            </td>
                                            <td class="fw-bold" style="color: {{ $l->net_share >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                {{ $l->net_share >= 0 ? '+' : '' }}{{ number_format($l->net_share, 2) }} ج
                                            </td>
                                            <td class="fw-semibold" style="color: {{ $l->withdrawn_amount > 0 ? 'var(--c-danger)' : 'var(--c-text-soft)' }};">
                                                {{ $l->withdrawn_amount > 0 ? number_format($l->withdrawn_amount, 2) . ' ج' : '—' }}
                                            </td>
                                            <td class="fw-semibold" style="color: {{ $l->reinvested_amount != 0 ? 'var(--c-success)' : 'var(--c-text-soft)' }};">
                                                {{ $l->reinvested_amount != 0 ? '+' . number_format($l->reinvested_amount, 2) . ' ج' : '—' }}
                                            </td>
                                            <td class="prt-num-main">{{ number_format($l->closing_capital, 2) }} ج</td>
                                            <td class="text-center">
                                                <span class="prt-chip is-accent">{{ number_format($closePct, 2) }}%</span>
                                                @if(abs($pctDiff) > 0.0001)
                                                    <div class="prt-num-sub fw-semibold" style="color: {{ $pctDiff > 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                        {{ $pctDiff > 0 ? '+' : '' }}{{ number_format($pctDiff, 2) }}%
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5 text-muted">لم يشارك هذا الشريك في أي دورة شهرية حتى الآن.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="prt-panel-foot no-print no-export">
                            <div>
                                عرض <strong>{{ $lines->firstItem() ?? 0 }}</strong> إلى <strong>{{ $lines->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $lines->total() }}</strong> دورة
                            </div>
                            <div>
                                {{ $lines->appends(array_merge(request()->query(), ['tab' => 'periods']))->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- شارتات نمو رأس المال وتطور الحصة --}}
            <div class="row g-3 mb-4 no-print no-export">
                <div class="col-md-6">
                    <div class="prt-chart-card">
                        <div class="prt-chart-head">
                            <h6><i class="fa fa-chart-area" style="color: var(--c-accent);"></i> تطور رصيد الشريك عبر الدورات الشهرية</h6>
                            <span class="prt-chip is-accent">تدوير الأرباح</span>
                        </div>
                        <div style="height: 255px;">
                            <canvas id="chartCapitalGrowth"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="prt-chart-card">
                        <div class="prt-chart-head">
                            <h6><i class="fa fa-chart-bar" style="color: var(--c-success);"></i> صافي الأرباح المحققة مقابل المسحوبات</h6>
                            <span class="prt-chip is-neutral">لكل دورة شهرية</span>
                        </div>
                        <div style="height: 255px;">
                            <canvas id="chartProfitsVsWithdrawn"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="prt-chart-card">
                        <div class="prt-chart-head">
                            <h6><i class="fa fa-arrow-trend-up" style="color: var(--c-warning);"></i> تطور نسبة مساهمة الشريك بعد كل إقفال</h6>
                            <span class="prt-chip is-warning">النسبة المعتمدة الحالية: {{ number_format($capitalSharePct, 2) }}%</span>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="chartShareEvolution"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- شارت محاكي توقعات الأرباح المستقبلية --}}
            <div class="prt-panel mb-2" id="projectionStatementPanel">
                <div class="prt-panel-head">
                    <div>
                        <h5 class="prt-panel-title">
                            <span class="prt-panel-title-ico"><i class="fa fa-chart-line"></i></span>
                            <span>توقعات أرباح الشريك ونمو المحفظة (مقارنة سيناريوهات التدوير والسحب)</span>
                        </h5>
                        <p class="prt-panel-sub" id="projSubtitleInfo">
                            محاكاة نمو رأس مال الشريك ({{ number_format($partner->capital) }} ج) بناءً على نسب الشهور المحددة
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if((float)$partner->ahmed_ratio > 0)
                            <div class="proj-toggle-group no-print no-export" role="group" id="btnGroupRateMode" title="تحديد هل النسب المدخلة صافي للشريك مباشرة أم يُخصم منها نسبة الإدارة">
                                <button type="button" class="active" onclick="switchProjRateMode('net', this)">النسبة صافي للشريك</button>
                                <button type="button" onclick="switchProjRateMode('gross', this)">خصم الإدارة ({{ number_format($partner->ahmed_ratio, 0) }}%)</button>
                            </div>
                        @endif
                        <span class="prt-chip is-accent" id="exportPeriodBadge" style="display:none;">فترة التوقع: سنة كاملة</span>
                        <div class="proj-toggle-group no-export" role="group" id="btnGroupTimeframe">
                            <button type="button" onclick="switchProjectionPeriod('6M', this)">6 شهور</button>
                            <button type="button" class="active" onclick="switchProjectionPeriod('1Y', this)">سنة كاملة</button>
                            <button type="button" onclick="switchProjectionPeriod('2Y', this)">سنتين</button>
                        </div>
                        <button type="button" class="prt-pill-btn is-amber is-sm no-print no-export" data-bs-toggle="modal" data-bs-target="#modalProjectionRates" title="تعديل نسب شهور السنة ديناميكياً">
                            <i class="fa fa-sliders fa-xs"></i>
                            <span>ضبط نسب الشهور</span>
                        </button>
                    </div>
                </div>

                <div class="proj-panel-body">
                    {{-- ملخص السيناريوهات الثلاثة (مرتبة تحت بعض بشكل رسمي متناسق) --}}
                    <div class="proj-scenarios-stack">
                        <div class="proj-scenario-card is-emerald">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="prt-chip is-success"><i class="fa fa-sync-alt fa-xs"></i> سيناريو 1: تدوير كامل الأرباح</span>
                                <span class="prt-chip is-success" id="proj_pct_reinvest">+0%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 py-1">
                                <span class="small text-muted fw-semibold">الرصيد المتوقع بنهاية الفترة:</span>
                                <strong style="color: var(--c-success); font-size: 1.15rem; font-variant-numeric: tabular-nums;" id="proj_stat_reinvest">0.00 ج</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted" style="font-size: 0.76rem;">
                                <span>صافي ربح تراكمي: <strong style="color: var(--c-success);" id="proj_profit_reinvest">+0 ج</strong></span>
                                <span>أعلى نمو مركب لرأس المال</span>
                            </div>
                        </div>

                        <div class="proj-scenario-card is-blue">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="prt-chip is-info"><i class="fa fa-money-bill-wave fa-xs"></i> سيناريو 2: سحب الأرباح شهرياً</span>
                                <span class="prt-chip is-info" id="proj_pct_withdraw_all">+0% عائد بسيط</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 py-1">
                                <span class="small text-muted fw-semibold">إجمالي الكاش المسحوب خلال الفترة:</span>
                                <strong style="color: var(--c-info); font-size: 1.15rem; font-variant-numeric: tabular-nums;" id="proj_cash_withdraw_all">0.00 ج</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted" style="font-size: 0.76rem;">
                                <span>الرصيد يبقى ثابتاً: <strong>{{ number_format($partner->capital) }} ج</strong></span>
                                <strong style="color: var(--c-info);" id="proj_avg_monthly_profit">0 ج/ش</strong>
                            </div>
                        </div>

                        <div class="proj-scenario-card is-amber">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="prt-chip is-warning">
                                    <i class="fa fa-hand-holding-dollar fa-xs"></i> سيناريو 3: سحب مثبت (<span id="proj_fixed_amt_lbl">{{ number_format($partner->monthly_fixed_withdrawal ?: 1000) }}</span> ج/ش)
                                </span>
                                <span class="prt-chip is-warning" id="proj_pct_fixed">+0%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 py-1">
                                <span class="small text-muted fw-semibold">الرصيد النامي المتوقع بنهاية الفترة:</span>
                                <strong style="color: var(--c-navy); font-size: 1.15rem; font-variant-numeric: tabular-nums;" id="proj_stat_fixed">0.00 ج</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted" style="font-size: 0.76rem;">
                                <span>إجمالي الكاش المسحوب: <strong style="color: var(--c-danger);" id="proj_cash_fixed">0 ج</strong></span>
                                <span id="proj_fixed_note">(تدوير ما زاد)</span>
                            </div>
                        </div>
                    </div>

                    <div class="proj-chart-wrap">
                        <canvas id="chartProfitProjections"></canvas>
                    </div>
                </div>
            </div>

            {{-- خانات التوقيع الرسمية (تظهر في الطباعة وفي الصورة والـ PDF) --}}
            <div class="export-print-only mt-3 pt-3 border-top border-2 border-dark" style="page-break-inside: avoid;">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="formal-sig-box">
                            <div class="fw-bold text-dark mb-1" style="font-size:0.85rem;">إعداد وتدقيق الحسابات</div>
                            <div class="text-muted small mb-4" style="font-size:0.74rem;">وحدة الحسابات والرقابة المالية</div>
                            <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                            <div class="small text-muted" style="font-size:0.72rem;">التوقيع: ............................</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="formal-sig-box">
                            <div class="fw-bold text-dark mb-1" style="font-size:0.85rem;">اعتماد الإدارة التنفيذية</div>
                            <div class="text-muted small mb-4" style="font-size:0.74rem;">{{ $headName }}</div>
                            <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                            <div class="small text-muted" style="font-size:0.72rem;">الاعتماد: ............................</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="formal-sig-box">
                            <div class="fw-bold text-dark mb-1" style="font-size:0.85rem;">توقيع واستلام الشريك</div>
                            <div class="text-muted small mb-4" style="font-size:0.74rem;">{{ $partner->name }}</div>
                            <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                            <div class="small text-muted" style="font-size:0.72rem;">التوقيع: ............................</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center pt-3 pb-1 mt-3 border-top text-muted small" style="border-color: var(--p-hairline) !important;">
                <i class="fa fa-shield-alt me-1" style="color: var(--c-success);"></i>
                كشف حساب استثماري رسمي معتمد صادر إلكترونياً من النظام المالي — {{ $companyName }} · تاريخ الإصدار: {{ date('Y-m-d H:i') }}
            </div>
        </div>

    </div>
</div>

{{-- 1. MODAL: زيادة رأس المال --}}
<div class="modal fade prt-modal" id="modalAddCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.addCapital', $partner->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon is-success"><i class="fa fa-plus"></i></span>
                    <span>زيادة رأس مال الشريك ({{ $partner->name }})</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-pro-label">المبلغ المودع (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" id="showAddCapAmountInput" value="{{ (float)$partner->capital == 0 && (float)$partner->initial_capital > 0 ? (float)$partner->initial_capital : '' }}" class="form-pro-control fw-semibold" placeholder="المبلغ..." required>
                </div>
                <div class="mb-3">
                    <label class="form-pro-label">المحفظة / الخزينة المستلمة <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-pro-control" required>
                        <option value="">-- اختر المحفظة / الخزينة المستلمة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-pro-label">ملاحظات</label>
                    <input type="text" name="notes" class="form-pro-control" placeholder="سبب زيادة الحصة...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="prt-pill-btn is-primary is-sm px-4">تأكيد وإيداع وتحديث النسب</button>
            </div>
        </form>
    </div>
</div>

{{-- 2. MODAL: سحب من رأس المال --}}
<div class="modal fade prt-modal" id="modalWithdrawCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.withdrawCapital', $partner->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon is-warning"><i class="fa fa-minus"></i></span>
                    <span>سحب من رأس مال الشريك ({{ $partner->name }})</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-pro danger mb-3" style="font-size: 0.82rem;">
                    <i class="fa fa-triangle-exclamation"></i>
                    <span>يُخصم هذا السحب من رأس مال الشريك مباشرة، مع تحديث نسب المساهمة وتسجيلها في السجل.</span>
                </div>
                <div class="prt-soft-box mb-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted">رأس المال الحالي المتاح:</span>
                    <strong style="color: var(--c-navy);">{{ number_format($partner->capital, 2) }} ج</strong>
                </div>
                <div class="mb-3">
                    <label class="form-pro-label">المبلغ المراد سحبه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" max="{{ $partner->capital }}" name="amount" class="form-pro-control fw-semibold" placeholder="المبلغ..." required>
                </div>
                <div class="mb-3">
                    <label class="form-pro-label">الخزينة المصروف منها المبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-pro-control" required>
                        <option value="">-- تحديد الخزينة للصرف --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-pro-label">ملاحظات / سبب السحب</label>
                    <input type="text" name="notes" class="form-pro-control" placeholder="سبب استرداد جزء من رأس المال...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn-pro btn-pro-danger rounded-pill px-4" style="font-size: 0.82rem;" onclick="return confirm('تأكيد نهائي: سيتم سحب المبلغ وتحديث النسب وتسجيلها في السجل. هل تريد المتابعة؟')">
                    <i class="fa fa-check fa-xs"></i> تأكيد وصرف من الخزينة
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 3. MODAL: تعديل بيانات الشريك --}}
<div class="modal fade prt-modal" id="modalEditPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.update', $partner->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon"><i class="fa fa-pen"></i></span>
                    <span>تعديل بيانات الشريك والنسبة</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-pro-label">اسم الشريك <span class="text-danger">*</span></label>
                    <input type="text" name="name" data-unique-scope="partner" data-unique-exclude-id="{{ $partner->id }}" data-unique-exclude-name="{{ $partner->name }}" class="form-pro-control" value="{{ $partner->name }}" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-pro-label">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-pro-control" value="{{ $partner->phone }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-pro-label">تاريخ انضمام الشريك</label>
                        <input type="date" name="joined_at" class="form-pro-control" value="{{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '' }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-pro-label">
                        أصل رأس المال (رأس المال التأسيسي - ج)
                        <span class="prt-chip is-warning ms-1" style="font-size: 0.68rem; padding: 1px 8px;">
                            <i class="fa fa-lock fa-xs"></i> محفوظ ولا يُعدّل
                        </span>
                    </label>
                    <div class="prt-soft-box d-flex align-items-center justify-content-between py-2 px-3">
                        <strong style="color: var(--c-navy); font-size: 1rem; font-variant-numeric: tabular-nums;">{{ number_format((float) $partner->initial_capital, 2) }} ج</strong>
                        <i class="fa fa-lock text-muted"></i>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">رأس المال التأسيسي ثابت ومحفوظ للمقارنة الدائمة مع التقفيلات السنوية ولا يمكن تغييره أو تعديله.</small>
                </div>
                <div class="mb-3">
                    <label class="form-pro-label">نسبة الإدارة من الربح (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" class="form-control form-pro-control fw-semibold" value="{{ $partner->ahmed_ratio }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-pro-label">المسحوب شهرياً (اختياري)</label>
                    <div class="input-group">
                        <input type="number" step="100" min="0" name="monthly_fixed_withdrawal" class="form-control form-pro-control" value="{{ $partner->monthly_fixed_withdrawal }}" placeholder="مثال: 1000 أو 3000">
                        <span class="input-group-text">ج / شهر</span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">يُدرج تلقائياً عند التقفيل الجزئي، أو اتركه فارغاً للسحب المتغير.</small>
                </div>
                <div class="mb-3">
                    <label class="form-pro-label">ملاحظات</label>
                    <input type="text" name="notes" class="form-pro-control" value="{{ $partner->notes }}">
                </div>
                @if($partner->status === 'active')
                    <div class="prt-soft-box">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_head" value="1" id="showIsHead"
                                   onchange="const r=this.form.querySelector('[name=ahmed_ratio]'); r.required=!this.checked; r.closest('.mb-3').style.display=this.checked?'none':'';">
                            <label class="form-check-label fw-semibold" for="showIsHead">تعيين هذا الحساب كحساب الإدارة الرئيسي</label>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">حساب الإدارة تدخل فيه الأرباح من نسب الشغل مع الشركاء، وما لا يُسحب يُضاف لرصيده.</small>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="prt-pill-btn is-primary is-sm px-4">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

{{-- 4. MODAL: المشاركة عبر واتساب --}}
<div class="modal fade prt-modal" id="modalWhatsAppShare" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon is-success"><i class="fa-brands fa-whatsapp"></i></span>
                    <span>مشاركة ملف الشريك والشارتات عبر واتساب</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-pro info mb-3" style="font-size: 0.82rem;">
                    <i class="fa fa-info-circle"></i>
                    <span>يمكنك إرسال التقرير المالي المصور كاملاً إلى واتساب الشريك، أو تحميله كصورة أو ملف PDF لإرفاقه.</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-pro-label">اسم الشريك</label>
                        <input type="text" class="form-pro-control" style="background: #f4f8fb;" value="{{ $partner->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-pro-label">رقم الواتساب <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-brands fa-whatsapp" style="color: var(--c-success);"></i></span>
                            <input type="text" id="waPartnerPhone" class="form-control form-pro-control fw-semibold" dir="ltr" value="{{ $partner->phone }}" placeholder="01xxxxxxxxx">
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">يتم تنسيق كود الدولة تلقائياً (010... ← 2010...).</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">نص الرسالة المرفقة مع التقرير</label>
                    <textarea id="waMessageText" rows="9" class="form-pro-control" style="font-size: 0.84rem; line-height: 1.65;">السلام عليكم أستاذ {{ $partner->name }}،
تحية طيبة وبعد، مرفق لحضرتكم كشف حسابكم والتقرير المالي الرسمي المعتمد من {{ $companyName }}:

📊 ملخص رحلة رأس المال:
• رأس المال التأسيسي (بداية الشراكة): {{ number_format($foundingCap, 2) }} ج
• الرصيد الحالي المعتمد: {{ number_format($currentCap, 2) }} ج
• إجمالي النمو منذ التأسيس: {{ $totalCumGrowth >= 0 ? '+' : '' }}{{ number_format($totalCumGrowth, 2) }} ج{{ $totalCumGrowthPct !== null ? ' (' . ($totalCumGrowthPct >= 0 ? '+' : '') . number_format($totalCumGrowthPct, 2) . '%)' : '' }}
• نسبة المساهمة المعتمدة: {{ number_format($capitalSharePct, 2) }}%
• صافي أرباح الدورات المحققة: {{ number_format($totalNetEarned, 2) }} ج (مدوّر: {{ number_format($totalReinvested, 2) }} ج · مسحوب: {{ number_format($totalWithdrawn, 2) }} ج)
@if($annualLines->isNotEmpty())

📅 سجل التقفيلات السنوية:
@foreach($annualLines as $al)
• نهاية سنة {{ $al->closing_year }}: الرصيد {{ number_format($al->closing_capital, 2) }} ج | الزيادة السنوية: {{ $al->annual_gain >= 0 ? '+' : '' }}{{ number_format($al->annual_gain, 2) }} ج{{ (float) $al->opening_capital > 0 ? ' (' . ($al->annual_gain_pct >= 0 ? '+' : '') . number_format($al->annual_gain_pct, 1) . '%)' : '' }}
@endforeach
@endif

• تاريخ استخراج الكشف: {{ date('Y-m-d') }}
(مرفق صورة كشف الحساب الرسمي المعتمد)</textarea>
                </div>

                <div class="prt-soft-box">
                    <div class="fw-semibold small mb-2" style="color: var(--c-navy);"><i class="fa fa-paperclip me-1" style="color: var(--c-accent);"></i> خيارات تحميل التقرير</div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button type="button" class="prt-pill-btn is-blue is-sm w-100" onclick="exportProfileAsImage()">
                                <i class="fa fa-image fa-xs"></i> تحميل صورة التقرير (PNG)
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="prt-pill-btn is-rose is-sm w-100" onclick="exportProfileAsPDF()">
                                <i class="fa fa-file-pdf fa-xs"></i> تحميل ملف PDF بالشارتات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="prt-pill-btn is-whatsapp is-sm px-4" onclick="sendToWhatsAppWithImage()" id="btnSendWhatsApp">
                    <i class="fa-brands fa-whatsapp"></i> فتح محادثة الواتساب ونسخ الصورة
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 5. MODAL: ضبط نسب أرباح شهور السنة --}}
<div class="modal fade prt-modal" id="modalProjectionRates" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('partners.updateProjectionRates') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon is-warning"><i class="fa fa-sliders"></i></span>
                    <span>إعدادات نسب أرباح شهور السنة (محاكي التوقعات)</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="prt-soft-box mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">تُستخدم هذه النسب لتغذية محاكي نمو المحفظة لكافة الشركاء.</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-semibold">تثبيت نسبة موحدة:</span>
                        <input type="number" step="0.1" min="0" max="100" id="flatRateQuickInput" class="form-pro-control py-1 text-center fw-semibold" style="width: 72px;" value="2" placeholder="2%">
                        <button type="button" class="prt-pill-btn is-soft is-sm py-1 px-3" onclick="applyFlatRateToAllMonths()">تطبيق</button>
                    </div>
                </div>

                <div class="row g-3">
                    @php
                        $monthsList = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                    @endphp
                    @foreach($monthsList as $idx => $mName)
                        @php
                            $currRate = isset($seasonalMonthlyRates[$idx]['rate']) ? (float)$seasonalMonthlyRates[$idx]['rate'] : 1.5;
                        @endphp
                        <div class="col-md-3 col-sm-4 col-6">
                            <div class="prt-soft-box p-2 text-center">
                                <label class="form-pro-label mb-1">{{ $mName }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.1" min="0" max="100" name="rates[{{ $idx }}]"
                                           class="form-control form-pro-control py-1 text-center fw-semibold proj-rate-input"
                                           value="{{ $currRate }}" oninput="updateLiveModalRatesSum()">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 prt-soft-box d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small fw-semibold" style="color: var(--c-navy);">
                        <i class="fa fa-calculator me-1" style="color: var(--c-accent);"></i> إجمالي نسب السنة الحالية:
                    </span>
                    <span class="prt-chip is-accent px-3 py-2" style="font-size: 0.85rem;" id="modalLiveRatesSum">30.0%</span>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="prt-pill-btn is-primary is-sm px-4">
                    <i class="fa fa-save fa-xs"></i> حفظ النسب وتطبيقها على المحاكي
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
    function openAddCapitalModal(defaultAmount = '') {
        const amtInput = document.getElementById('showAddCapAmountInput');
        if (amtInput && defaultAmount !== '') amtInput.value = defaultAmount;
        new bootstrap.Modal(document.getElementById('modalAddCapital')).show();
    }

    function openWithdrawCapitalModal() {
        const doOpen = () => {
            new bootstrap.Modal(document.getElementById('modalWithdrawCapital')).show();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'تنبيه سحب من رأس المال',
                html: '<div style="font-size:0.95rem;line-height:1.75;color:#1a1f2e;" class="text-center">' +
                      'هذا سحب من <strong style="color:#b91c1c;">رأس مال الشريك ({{ addslashes($partner->name) }})</strong><br>' +
                      'سيتم تحديث نسب المساهمة وتسجيل النسب قبل وبعد السحب في السجل.<br><br>' +
                      '<small style="color:#5a6478;">هل تريد المتابعة وفتح نموذج السحب؟</small>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'متابعة السحب',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) doOpen();
            });
        } else {
            if (confirm('تنبيه هام:\nهذا سحب من رأس مال الشريك ({{ addslashes($partner->name) }}) وسيُحدث النسب ويُسجل في السجل.\n\nهل تريد المتابعة؟')) {
                doOpen();
            }
        }
    }

    function openEditPartnerModal() { new bootstrap.Modal(document.getElementById('modalEditPartner')).show(); }

    const chartFont = { family: 'IBM Plex Sans Arabic', size: 11 };

    const elChartCap = document.getElementById('chartCapitalGrowth');
    if (elChartCap) {
        new Chart(elChartCap.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'الرصيد (ج)',
                    data: {!! json_encode($chartCapitalGrowth) !!},
                    borderColor: '#0d7373',
                    backgroundColor: 'rgba(13, 115, 115, 0.10)',
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

    const elChartProf = document.getElementById('chartProfitsVsWithdrawn');
    if (elChartProf) {
        new Chart(elChartProf.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'صافي الربح المحقق (ج)',
                        data: {!! json_encode($chartProfits) !!},
                        backgroundColor: '#2d8659',
                        borderRadius: 6
                    },
                    {
                        label: 'المسحوب نقداً (ج)',
                        data: {!! json_encode($chartWithdrawn) !!},
                        backgroundColor: '#c95656',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: chartFont, usePointStyle: true } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#edf2f7' }, ticks: { font: chartFont } },
                    x: { grid: { display: false }, ticks: { font: chartFont } }
                }
            }
        });
    }

    const elChartShare = document.getElementById('chartShareEvolution');
    if (elChartShare) {
        new Chart(elChartShare.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'نسبة الشريك في الدورة (%)',
                    data: {!! json_encode($chartShares) !!},
                    borderColor: '#b67c1f',
                    backgroundColor: 'rgba(182, 124, 31, 0.10)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#b67c1f'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#edf2f7' }, ticks: { font: chartFont } },
                    x: { grid: { display: false }, ticks: { font: chartFont } }
                }
            }
        });
    }

    // ════════════════════════════════════════════════════════════
    // محاكي توقعات الأرباح المستقبلية
    // ════════════════════════════════════════════════════════════
    const seasonalMonthlyRates = {!! json_encode($seasonalMonthlyRates, JSON_UNESCAPED_UNICODE) !!};
    const basePartnerCap = {{ (float) $partner->capital }};
    const configuredFixedWithdrawal = {{ (float) ($partner->monthly_fixed_withdrawal ?: 0) }};
    const baseAhmedRatio = Math.max(0, Math.min(100, {{ (float) ($partner->ahmed_ratio ?? 0) }}));
    const partnerNetFactor = 1 - (baseAhmedRatio / 100);

    let chartProjectionsInstance = null;
    let currentProjectionPeriod = '1Y';
    let currentProjRateMode = 'net';

    function switchProjRateMode(mode, btn) {
        currentProjRateMode = mode;
        document.querySelectorAll('#btnGroupRateMode button').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        initProjectionChart(currentProjectionPeriod);
    }

    function applyFlatRateToAllMonths() {
        const val = parseFloat(document.getElementById('flatRateQuickInput')?.value);
        if (isNaN(val) || val < 0) return;
        document.querySelectorAll('.proj-rate-input').forEach(input => { input.value = val; });
        updateLiveModalRatesSum();
    }

    function buildProjectionData(periodCode) {
        let monthCount = 12;
        if (periodCode === '6M') monthCount = 6;
        else if (periodCode === '2Y') monthCount = 24;

        const rateFactor = (currentProjRateMode === 'gross' && baseAhmedRatio > 0) ? partnerNetFactor : 1.0;

        let firstYearRatesSum = 0;
        for (let m = 0; m < 12; m++) {
            firstYearRatesSum += (parseFloat(seasonalMonthlyRates[m]?.rate) || 0);
        }
        const avgMonthlyNetRate = ((firstYearRatesSum / 12) / 100) * rateFactor;
        const avgFirstMonthProfit = basePartnerCap * avgMonthlyNetRate;

        const effectiveFixedWithdrawal = configuredFixedWithdrawal > 0
            ? configuredFixedWithdrawal
            : Math.max(100, Math.round((avgFirstMonthProfit * 0.5) / 50) * 50 || 500);

        const labels = ['البداية'];
        const dataReinvest = [basePartnerCap];
        const dataWithdrawAll = [basePartnerCap];
        const dataFixed = [basePartnerCap];

        let capReinvest = basePartnerCap;
        let capWithdrawAll = basePartnerCap;
        let totalCashWithdrawAll = 0;
        let capFixed = basePartnerCap;
        let totalCashFixed = 0;
        let simpleRatesSumPct = 0;

        const nowDate = new Date();
        const startMonthIdx = nowDate.getMonth();
        const startYear = nowDate.getFullYear();

        for (let i = 0; i < monthCount; i++) {
            const seasonIdx = (startMonthIdx + i) % 12;
            const yearVal = startYear + Math.floor((startMonthIdx + i) / 12);
            const rateObj = seasonalMonthlyRates[seasonIdx] || { month: '', rate: 0 };
            labels.push(`${rateObj.month} ${yearVal}`);

            const rawRatePct = parseFloat(rateObj.rate) || 0;
            const netRatePct = rawRatePct * rateFactor;
            simpleRatesSumPct += netRatePct;
            const r = netRatePct / 100;

            capReinvest += capReinvest * r;
            dataReinvest.push(Math.round(capReinvest));

            totalCashWithdrawAll += capWithdrawAll * r;
            dataWithdrawAll.push(Math.round(capWithdrawAll));

            const profitFixed = capFixed * r;
            const withdrawAmt = Math.min(profitFixed, effectiveFixedWithdrawal);
            totalCashFixed += withdrawAmt;
            capFixed += Math.max(0, profitFixed - withdrawAmt);
            dataFixed.push(Math.round(capFixed));
        }

        const totalProfitReinvest = Math.max(0, capReinvest - basePartnerCap);
        const pctReinvest = basePartnerCap > 0 ? ((totalProfitReinvest / basePartnerCap) * 100).toFixed(1) : '0.0';
        const pctWithdrawSimple = simpleRatesSumPct.toFixed(1);
        const avgMonthlyCash = monthCount > 0 ? (totalCashWithdrawAll / monthCount) : 0;
        const pctFixed = basePartnerCap > 0 ? (((capFixed - basePartnerCap) / basePartnerCap) * 100).toFixed(1) : '0.0';

        const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.innerText = v; };
        setTxt('proj_stat_reinvest', Number(Math.round(capReinvest)).toLocaleString('en-US') + ' ج');
        setTxt('proj_profit_reinvest', '+' + Number(Math.round(totalProfitReinvest)).toLocaleString('en-US') + ' ج');
        setTxt('proj_pct_reinvest', '+' + pctReinvest + '% مركب');
        setTxt('proj_cash_withdraw_all', '+' + Number(Math.round(totalCashWithdrawAll)).toLocaleString('en-US') + ' ج');
        setTxt('proj_pct_withdraw_all', '+' + pctWithdrawSimple + '% عائد بسيط');
        setTxt('proj_avg_monthly_profit', 'متوسط ' + Number(Math.round(avgMonthlyCash)).toLocaleString('en-US') + ' ج/ش');
        setTxt('proj_stat_fixed', Number(Math.round(capFixed)).toLocaleString('en-US') + ' ج');
        setTxt('proj_cash_fixed', '+' + Number(Math.round(totalCashFixed)).toLocaleString('en-US') + ' ج');
        setTxt('proj_pct_fixed', '+' + pctFixed + '% نمو رصيد');
        setTxt('proj_fixed_amt_lbl', Number(effectiveFixedWithdrawal).toLocaleString('en-US'));

        const elFixedNote = document.getElementById('proj_fixed_note');
        if (elFixedNote) {
            elFixedNote.innerText = (Math.round(capFixed) <= Math.round(basePartnerCap) && totalCashFixed > 0)
                ? '(المسحوب يستوعب كامل الربح)'
                : '(تدوير ما زاد: +' + Number(Math.round(capFixed - basePartnerCap)).toLocaleString('en-US') + ' ج)';
        }

        const elSubtitle = document.getElementById('projSubtitleInfo');
        if (elSubtitle) {
            const avgM = monthCount > 0 ? (simpleRatesSumPct / monthCount).toFixed(2) : '0';
            const modeTxt = (currentProjRateMode === 'gross' && baseAhmedRatio > 0)
                ? `بعد خصم نسبة الإدارة (${baseAhmedRatio}%)`
                : `بنسبة صافي مباشرة للشريك`;
            elSubtitle.innerHTML = `محاكاة رأس مال الشريك (<strong>${Number(basePartnerCap).toLocaleString('en-US')} ج</strong>) · متوسط النسبة: <strong style="color:var(--c-success);">${avgM}% / شهر</strong> (إجمالي الفترة: <strong>${pctWithdrawSimple}%</strong> ${modeTxt})`;
        }

        return { labels, dataReinvest, dataWithdrawAll, dataFixed, effectiveFixedWithdrawal };
    }

    function updateLiveModalRatesSum() {
        let sum = 0;
        let compound = 1.0;
        document.querySelectorAll('.proj-rate-input').forEach(input => {
            const v = parseFloat(input.value) || 0;
            sum += v;
            compound *= (1 + (v / 100));
        });
        const compoundPct = ((compound - 1) * 100).toFixed(1);
        const elSum = document.getElementById('modalLiveRatesSum');
        if (elSum) elSum.innerText = `${sum.toFixed(1)}% بسيط (${compoundPct}% بالتدوير المركب)`;
    }
    document.getElementById('modalProjectionRates')?.addEventListener('show.bs.modal', updateLiveModalRatesSum);

    function initProjectionChart(periodCode) {
        currentProjectionPeriod = periodCode;
        const proj = buildProjectionData(periodCode);

        const canvasEl = document.getElementById('chartProfitProjections');
        if (!canvasEl) return;
        const ctx = canvasEl.getContext('2d');
        if (chartProjectionsInstance) chartProjectionsInstance.destroy();

        chartProjectionsInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: proj.labels,
                datasets: [
                    {
                        label: 'سيناريو 1: تدوير كامل الأرباح (أعلى نمو مركب)',
                        data: proj.dataReinvest,
                        borderColor: '#2d8659',
                        backgroundColor: 'rgba(45, 134, 89, 0.08)',
                        borderWidth: 2.8,
                        pointRadius: 3.5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2d8659',
                        tension: 0.35,
                        fill: false
                    },
                    {
                        label: 'سيناريو 2: سحب كامل الأرباح شهرياً (رأس مال ثابت)',
                        data: proj.dataWithdrawAll,
                        borderColor: '#1e5fa4',
                        backgroundColor: 'rgba(30, 95, 164, 0.08)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#1e5fa4',
                        tension: 0.1,
                        fill: false
                    },
                    {
                        label: `سيناريو 3: سحب مثبت (${Number(proj.effectiveFixedWithdrawal).toLocaleString()} ج/شهر) وتدوير الباقي`,
                        data: proj.dataFixed,
                        borderColor: '#b67c1f',
                        backgroundColor: 'rgba(182, 124, 31, 0.08)',
                        borderWidth: 2.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#b67c1f',
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'IBM Plex Sans Arabic', weight: '600', size: 11 }, usePointStyle: true, padding: 14 }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: (context) => (context.dataset.label || '') + ': ' + Number(context.parsed.y || 0).toLocaleString('en-US') + ' ج'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { callback: v => Number(v).toLocaleString('en-US') + ' ج', font: chartFont },
                        grid: { color: '#edf2f7' }
                    },
                    x: { ticks: { font: chartFont }, grid: { display: false } }
                }
            }
        });
    }

    function switchProjectionPeriod(periodCode, btn) {
        document.querySelectorAll('#btnGroupTimeframe button').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        initProjectionChart(periodCode);
    }

    initProjectionChart('1Y');

    // إعادة ضبط أبعاد الشارت لتناسب عرض ورقة A4 تماماً قبل الطباعة ثم إرجاعه لحجمه الطبيعي بعدها
    window.addEventListener('beforeprint', function () {
        if (chartProjectionsInstance) {
            chartProjectionsInstance.resize(670, 215);
        }
    });
    window.addEventListener('afterprint', function () {
        if (chartProjectionsInstance) {
            setTimeout(() => chartProjectionsInstance.resize(), 120);
        }
    });

    if (new URLSearchParams(window.location.search).get('tab') === 'annual') {
        document.getElementById('annualStatementSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function openWhatsAppModal() { new bootstrap.Modal(document.getElementById('modalWhatsAppShare')).show(); }

    function formatPhoneForWhatsApp(phone) {
        if (!phone) return '';
        let cleaned = phone.replace(/[^0-9]/g, '');
        if (cleaned.startsWith('00')) cleaned = cleaned.substring(2);
        if (cleaned.startsWith('01') && cleaned.length === 11) cleaned = '2' + cleaned;
        return cleaned;
    }

    async function captureProfileCanvas() {
        const target = document.getElementById('partnerProfileExportArea');
        if (!target) throw new Error('منطقة التقرير غير موجودة');

        if (document.fonts && document.fonts.ready) await document.fonts.ready;

        return await html2canvas(target, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            scrollX: 0,
            scrollY: 0,
            onclone: (clonedDoc) => {
                const toHide = clonedDoc.querySelectorAll('.no-export, .no-print, button, .btn');
                toHide.forEach(el => el.style.setProperty('display', 'none', 'important'));

                const periodBadge = clonedDoc.getElementById('exportPeriodBadge');
                if (periodBadge) {
                    periodBadge.style.setProperty('display', 'inline-block', 'important');
                    const periodNames = {
                        '6M': 'فترة التوقع: 6 شهور قادمة',
                        '1Y': 'فترة التوقع: سنة كاملة قادمة',
                        '2Y': 'فترة التوقع: سنتين قادمتين'
                    };
                    periodBadge.innerHTML = '<i class="fa fa-calendar-alt me-1"></i> ' + (periodNames[currentProjectionPeriod] || 'فترة التوقع: سنة كاملة');
                }

                const el = clonedDoc.getElementById('partnerProfileExportArea');
                if (el) {
                    el.classList.add('is-exporting');
                    el.style.backgroundColor = '#ffffff';
                    el.style.width = 'auto';
                    el.style.maxWidth = 'none';
                    el.style.overflow = 'visible';
                }

                clonedDoc.querySelectorAll('.export-print-only').forEach(item => item.style.setProperty('display', 'block', 'important'));

                const tabMovements = clonedDoc.getElementById('tabMovements');
                if (tabMovements) {
                    tabMovements.classList.add('show', 'active');
                    tabMovements.style.setProperty('display', 'block', 'important');
                    tabMovements.style.setProperty('opacity', '1', 'important');
                    tabMovements.style.setProperty('visibility', 'visible', 'important');
                }
            }
        });
    }

    async function exportProfileAsImage() {
        const btn = document.getElementById('btnExportImage');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> تجهيز...'; }

        try {
            const canvas = await captureProfileCanvas();
            const partnerName = {!! json_encode($partner->name) !!}.replace(/[^a-zA-Z0-9\u0600-\u06FF_-]/g, '_');
            const filename = `كشف_حساب_${partnerName}_${new Date().toISOString().slice(0, 10)}.png`;

            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'تم استخراج الصورة بنجاح!', text: 'تم تحميل صورة كشف حساب الشريك بجودة عالية.', icon: 'success', timer: 2500, showConfirmButton: false });
            }
        } catch (err) {
            console.error(err);
            alert('حدث خطأ أثناء استخراج الصورة: ' + err.message);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = oldHtml; }
        }
    }

    async function exportProfileAsPDF() {
        const btn = document.getElementById('btnExportPDF');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> تجهيز...'; }

        try {
            const canvas = await captureProfileCanvas();
            const imgData = canvas.toDataURL('image/jpeg', 0.95);
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 10;
            const imgWidth = pageWidth - (margin * 2);
            const maxContentHeight = pageHeight - (margin * 2);
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            if (imgHeight <= maxContentHeight) {
                pdf.addImage(imgData, 'JPEG', margin, margin, imgWidth, imgHeight);
            } else {
                let heightLeft = imgHeight;
                let position = margin;
                pdf.addImage(imgData, 'JPEG', margin, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'JPEG', margin, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }
            }

            const partnerName = {!! json_encode($partner->name) !!}.replace(/[^a-zA-Z0-9\u0600-\u06FF_-]/g, '_');
            pdf.save(`تقرير_الشريك_${partnerName}_${new Date().toISOString().slice(0, 10)}.pdf`);

            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'تم تجهيز ملف PDF بنجاح!', text: 'تم تحميل كشف الحساب والشارتات كملف PDF رسمي.', icon: 'success', timer: 2500, showConfirmButton: false });
            }
        } catch (err) {
            console.error(err);
            alert('حدث خطأ أثناء استخراج PDF: ' + err.message);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = oldHtml; }
        }
    }

    async function sendToWhatsAppWithImage() {
        const phoneInput = document.getElementById('waPartnerPhone');
        const formattedPhone = formatPhoneForWhatsApp(phoneInput ? phoneInput.value.trim() : '');
        if (!formattedPhone) {
            alert('يرجى إدخال رقم هاتف صحيح للشريك للمتابعة عبر واتساب.');
            if (phoneInput) phoneInput.focus();
            return;
        }

        const msgText = (document.getElementById('waMessageText')?.value || '').trim();
        const btn = document.getElementById('btnSendWhatsApp');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> جاري تجهيز التقرير...'; }

        try {
            const canvas = await captureProfileCanvas();
            const partnerName = {!! json_encode($partner->name) !!}.replace(/[^a-zA-Z0-9\u0600-\u06FF_-]/g, '_');
            const link = document.createElement('a');
            link.download = `كشف_حساب_${partnerName}_${new Date().toISOString().slice(0, 10)}.png`;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            let copiedToClipboard = false;
            if (navigator.clipboard && window.ClipboardItem && canvas.toBlob) {
                try {
                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                    if (blob) {
                        await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
                        copiedToClipboard = true;
                    }
                } catch (clipErr) { console.warn('Clipboard write image not permitted:', clipErr); }
            }

            window.open(`https://wa.me/${formattedPhone}?text=${encodeURIComponent(msgText)}`, '_blank');
            const modalInst = bootstrap.Modal.getInstance(document.getElementById('modalWhatsAppShare'));
            if (modalInst) modalInst.hide();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'تم فتح محادثة الواتساب!',
                    html: `<div class="text-center"><p class="mb-2">تم تجهيز كشف الحساب المصور وتنزيله على جهازك.</p>${
                        copiedToClipboard
                            ? '<div class="alert alert-success py-2 px-3 fw-semibold small">تم نسخ الصورة للحافظة! اضغط <strong>Ctrl + V</strong> داخل شات الواتساب للصقها فوراً.</div>'
                            : '<div class="alert alert-info py-2 px-3 fw-semibold small">تم تحميل صورة التقرير، يمكنك إرفاقها أو سحبها مباشرة في شات الواتساب.</div>'
                    }</div>`,
                    icon: 'success',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#2d8659'
                });
            }
        } catch (err) {
            console.error(err);
            alert('حدث خطأ أثناء تجهيز المشاركة: ' + err.message);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = oldHtml; }
        }
    }
</script>

</body>
</html>
