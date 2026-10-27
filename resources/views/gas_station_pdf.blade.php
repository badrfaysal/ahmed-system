<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $modeTitles = [
            'company_report' => 'التقرير الرسمي لمسحوبات وكفاءة وقود الشاحنات — ' . $companyTitle,
            'analytics'      => 'تقرير الإحصائيات والتحليلات العامة لمحطة الوقود والشاحنات',
            'comparisons'    => 'تقرير مقارنات كفاءة الشاحنات والشارتات المعتمد',
            'logs'           => 'كشف سجل عمليات الوقود والعُهد النقدية التفصيلي',
        ];
        $docTitle = $modeTitles[$mode] ?? $modeTitles['company_report'];
        $codePrefix = match ($mode) {
            'company_report' => 'TRK-REP',
            'analytics'      => 'GAS-ANL',
            'comparisons'    => 'GAS-CMP',
            default          => 'GAS-LOG',
        };
        $reportCode = $codePrefix . '-' . now()->format('Ymd-Hi');
        $defaultLandscape = ($mode === 'logs');
    @endphp
    <title>{{ $docTitle }} ({{ $periodLabel }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- margin: 0mm يلغي تماماً طباعة رابط المتصفح (127.0.0.1:8000/...) والتاريخ وأرقام الصفحات التلقائية من Chrome/Edge --}}
    <style id="pageOrientationStyle">
        @page {
            size: A4 {{ $defaultLandscape ? 'landscape' : 'portrait' }};
            margin: 0mm !important;
        }
    </style>

    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0;
            padding: 0;
            background: #e2e8f0;
            color: #0f172a;
            font-family: 'IBM Plex Sans Arabic', 'Cairo', Tahoma, sans-serif;
            font-size: 10.8px;
            line-height: 1.42;
        }

        /* ── شريط التحكم العلوي (يختفي تماماً عند الطباعة و PDF) ── */
        .control-bar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 12px 20px;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.28);
            border-bottom: 3px solid #f59e0b;
        }
        .control-inner {
            max-width: 1180px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ctrl-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .mode-tabs {
            display: inline-flex;
            background: rgba(255,255,255,0.08);
            padding: 4px;
            border-radius: 8px;
            gap: 4px;
            flex-wrap: wrap;
        }
        .mode-tab {
            color: #cbd5e1;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            border: 1px solid transparent;
            cursor: pointer;
            background: transparent;
            font-family: inherit;
            transition: all 0.15s ease;
        }
        .mode-tab:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .mode-tab.active {
            background: #2563eb;
            color: #fff;
            border-color: #60a5fa;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
        }
        .ctrl-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-ctrl {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 7px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.12s ease;
        }
        .btn-ctrl:hover { transform: translateY(-1px); }
        .btn-pdf {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.35);
            font-size: 13px;
            padding: 8px 18px;
        }
        .btn-secondary-ctrl {
            background: rgba(255,255,255,0.12);
            color: #f8fafc;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .filter-strip {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            background: rgba(15, 23, 42, 0.55);
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.12);
        }
        .filter-strip label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
        }
        .filter-strip select,
        .filter-strip input[type="date"] {
            background: #fff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            padding: 4px 8px;
            font-family: inherit;
            font-size: 11.5px;
            font-weight: 600;
            height: 30px;
        }
        .period-pill {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.06);
            color: #e2e8f0;
            cursor: pointer;
            font-family: inherit;
        }
        .period-pill.active {
            background: #f59e0b;
            color: #0f172a;
            border-color: #fbbf24;
        }

        /* ── ورقة التقرير الرئيسية وإطار هوامش الطباعة المتكرر ── */
        .report-sheet {
            max-width: {{ $defaultLandscape ? '1320px' : '1060px' }};
            margin: 18px auto 36px auto;
            background: #ffffff;
            padding: 20px 22px;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.12);
        }
        .print-margin-table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            border: none;
            table-layout: fixed;
        }
        .print-margin-spacer {
            height: 0;
            padding: 0;
            border: none;
        }
        .print-margin-body {
            padding: 0;
            border: none;
            vertical-align: top;
            width: 100%;
            overflow: hidden;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                font-size: 9.6px;
            }
            .no-print, .control-bar {
                display: none !important;
            }
            .report-sheet {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .print-margin-table > thead {
                display: table-header-group;
            }
            .print-margin-table > tfoot {
                display: table-footer-group;
            }
            .print-margin-spacer {
                height: 7.5mm !important;
            }
            .print-margin-body {
                padding: 0 7mm !important;
            }
        }

        /* ── الترويسة الرسمية ── */
        .doc-header {
            border-bottom: 3px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }
        .brand-block h1 {
            margin: 0 0 3px 0;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Cairo', sans-serif;
        }
        .brand-block .sub {
            font-size: 10.5px;
            color: #475569;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .doc-type-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: #1e40af;
            border: 1.5px solid #93c5fd;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 11px;
        }
        .meta-box {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            min-width: 240px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .meta-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .meta-box td {
            padding: 3.5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-box tr:last-child td { border-bottom: none; }
        .meta-box td.k {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            width: 88px;
        }
        .meta-box td.v {
            font-weight: 700;
            color: #0f172a;
        }

        /* ── شريط توجيه شركة النقل ── */
        .client-banner {
            background: linear-gradient(90deg, #f8fafc 0%, #eff6ff 100%);
            border: 1.5px solid #bfdbfe;
            border-right: 5px solid #2563eb;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            break-inside: avoid;
        }
        .client-banner .to-label {
            font-size: 10px;
            color: #475569;
            font-weight: 700;
        }
        .client-banner .to-name {
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a;
        }

        /* ── كروت الملخص التنفيذي (KPIs) ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            margin-bottom: 12px;
            break-inside: avoid;
        }
        .kpi-card {
            border: 1px solid #cbd5e1;
            border-top: 3.5px solid #0284c7;
            border-radius: 7px;
            padding: 7px 8px;
            background: #ffffff;
            break-inside: avoid;
            min-width: 0;
        }
        .kpi-card.amber  { border-top-color: #f59e0b; }
        .kpi-card.orange { border-top-color: #ea580c; }
        .kpi-card.navy   { border-top-color: #0f172a; background: #f8fafc; }
        .kpi-card.green  { border-top-color: #10b981; }
        .kpi-card.purple { border-top-color: #7c3aed; }

        .kpi-card .lbl {
            font-size: 9.2px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2px;
            display: block;
        }
        .kpi-card .val {
            font-size: 12.8px;
            font-weight: 800;
            color: #0f172a;
            display: block;
            font-variant-numeric: tabular-nums;
        }
        .kpi-card .sub {
            font-size: 8.8px;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }

        /* ── عناوين الأقسام ── */
        .sec-head {
            background: #f1f5f9;
            border-right: 4px solid #0f172a;
            padding: 5px 10px;
            margin: 12px 0 7px 0;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            break-after: avoid;
            page-break-after: avoid;
        }
        .sec-head h3 {
            margin: 0;
            font-size: 11.8px;
            font-weight: 800;
            color: #0f172a;
        }
        .sec-head span {
            font-size: 9.5px;
            color: #475569;
            font-weight: 600;
        }

        /* ── شبكة الشارتات الفيكتور (Vector SVG + HTML RTL Labels) ── */
        .charts-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
            margin-bottom: 11px;
        }
        .chart-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 9px 11px;
            background: #ffffff;
            break-inside: avoid;
            page-break-inside: avoid;
            min-width: 0;
        }
        .chart-card.full-span {
            grid-column: 1 / -1;
        }
        .chart-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 5px;
            margin-bottom: 7px;
            gap: 6px;
        }
        .chart-card-title {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
        }
        .chart-card-sub {
            font-size: 9px;
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
        }
        .svg-wrap {
            width: 100%;
            overflow: visible;
        }
        .svg-wrap svg {
            width: 100%;
            height: auto;
            display: block;
            direction: ltr !important;
            unicode-bidi: isolate;
            font-family: 'IBM Plex Sans Arabic', 'Cairo', Tahoma, sans-serif;
        }

        /* ── صفوف الشارتات الأفقية بأسماء عربية كاملة وواضحة 100% ── */
        .hbar-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 2px 0;
        }
        .hbar-row {
            display: grid;
            grid-template-columns: 128px 1fr 84px;
            align-items: center;
            gap: 7px;
            min-width: 0;
        }
        .hbar-name {
            font-size: 9.8px;
            font-weight: 800;
            color: #0f172a;
            text-align: right;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            direction: rtl;
        }
        .hbar-bar-cell {
            width: 100%;
            min-width: 0;
        }
        .hbar-bar-cell svg {
            width: 100%;
            height: 14px;
            display: block;
            direction: ltr !important;
        }
        .hbar-metric {
            font-size: 9.2px;
            font-weight: 800;
            text-align: left;
            white-space: nowrap;
            direction: ltr;
            font-variant-numeric: tabular-nums;
        }

        .chart-legend-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 4px;
            font-size: 9.8px;
            font-weight: 800;
            flex-wrap: wrap;
        }
        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            vertical-align: middle;
            margin-left: 4px;
        }

        /* ── الجداول المحاسبية والفنية (مضبوطة 100% داخل عرض الورقة بدون أي قص) ── */
        .tbl {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 11px;
        }
        .tbl thead {
            display: table-header-group;
        }
        .tbl tfoot {
            display: table-footer-group;
        }
        .tbl tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .tbl > thead > tr > th {
            background: #0f172a;
            color: #ffffff;
            padding: 5px 3px;
            font-weight: 700;
            font-size: 9px;
            text-align: center;
            border: 1px solid #1e293b;
            line-height: 1.25;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .tbl > tbody > tr > td {
            padding: 4px 3px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
            color: #1e293b;
            line-height: 1.3;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .tbl > tbody > tr:nth-child(even) > td {
            background: #f8fafc;
        }
        .tbl > tbody > tr.row-alert > td {
            background: #fef2f2 !important;
            color: #991b1b;
        }
        .tbl > tfoot > tr > td {
            background: #f1f5f9 !important;
            font-weight: 800;
            font-size: 9px;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-top: 2px solid #0f172a;
            padding: 5px 3px;
            text-align: center;
            overflow-wrap: break-word;
        }

        /* جداول السجل والبيان الكثيفة للطباعة الكاملة داخل الصفحة */
        .tbl-dense > thead > tr > th {
            font-size: 8.5px;
            padding: 4px 2.5px;
        }
        .tbl-dense > tbody > tr > td,
        .tbl-dense > tfoot > tr > td {
            font-size: 8.5px;
            padding: 3.5px 2.5px;
        }

        @media print {
            .tbl > thead > tr > th { font-size: 8.4px; padding: 4px 2px; }
            .tbl > tbody > tr > td { font-size: 8.5px; padding: 3.5px 2px; }
            .tbl > tfoot > tr > td { font-size: 8.4px; padding: 4px 2px; }
            .tbl-dense > thead > tr > th { font-size: 8px; padding: 3.5px 2px; }
            .tbl-dense > tbody > tr > td,
            .tbl-dense > tfoot > tr > td { font-size: 8px; padding: 3px 2px; }
        }

        /* ── شارات التصنيف (كامل / جزئي / عهدة / كفاءة) ── */
        .b-full, .b-partial, .b-custody, .b-alert, .b-ok {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 8.2px;
            line-height: 1.25;
            white-space: nowrap;
        }
        .b-full    { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .b-partial { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .b-custody { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .b-alert   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .b-ok      { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

        .text-right { text-align: right !important; }
        .text-left  { text-align: left !important; }
        .fw-bold    { font-weight: 700 !important; }
        .fw-800     { font-weight: 800 !important; }
        .c-blue     { color: #1d4ed8 !important; }
        .c-amber    { color: #b45309 !important; }
        .c-orange   { color: #c2410c !important; }
        .c-green    { color: #047857 !important; }
        .c-red      { color: #b91c1c !important; }
        .c-muted    { color: #64748b !important; }

        /* ── التوقيعات والتذييل ── */
        .signatures-box {
            border: 1.5px solid #0f172a;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 14px;
            background: #ffffff;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .sig-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            text-align: center;
        }
        .sig-item .t {
            font-weight: 800;
            font-size: 10.5px;
            color: #0f172a;
            margin-bottom: 24px;
        }
        .sig-item .l {
            border-bottom: 1.5px dashed #94a3b8;
            width: 78%;
            margin: 0 auto 4px auto;
        }
        .sig-item .s {
            font-size: 9px;
            color: #64748b;
        }
        .doc-footer {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #64748b;
        }

        @media (max-width: 900px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    {{-- ═══════════ شريط المعاينة والفلترة قبل الطباعة / التصدير PDF ═══════════ --}}
    <div class="control-bar no-print">
        <div class="control-inner">
            <div class="ctrl-row">
                <div class="mode-tabs">
                    <button type="button" class="mode-tab {{ $mode === 'company_report' ? 'active' : '' }}" onclick="switchReportMode('company_report')">
                        <i class="fa fa-building me-1"></i> تقرير شركة النقل الدوري (بالشارتات)
                    </button>
                    <button type="button" class="mode-tab {{ $mode === 'analytics' ? 'active' : '' }}" onclick="switchReportMode('analytics')">
                        <i class="fa fa-chart-pie me-1"></i> تقرير الإحصائيات العامة PDF
                    </button>
                    <button type="button" class="mode-tab {{ $mode === 'comparisons' ? 'active' : '' }}" onclick="switchReportMode('comparisons')">
                        <i class="fa fa-scale-balanced me-1"></i> تقرير المقارنات والشارتات PDF
                    </button>
                    <button type="button" class="mode-tab {{ $mode === 'logs' ? 'active' : '' }}" onclick="switchReportMode('logs')">
                        <i class="fa fa-table-list me-1"></i> سجل العمليات التفصيلي PDF
                    </button>
                </div>

                <div class="ctrl-actions">
                    <button type="button" class="btn-ctrl btn-secondary-ctrl" onclick="togglePageOrientation()" id="btnOrientation">
                        <i class="fa fa-file-lines"></i>
                        <span>{{ $defaultLandscape ? 'اتجاه الورقة: عرضي (A4 Landscape)' : 'اتجاه الورقة: طولي (A4)' }}</span>
                    </button>
                    @if($mode !== 'logs')
                        <button type="button" class="btn-ctrl btn-secondary-ctrl" onclick="toggleLedgerSection()" id="btnToggleLedger">
                            <i class="fa fa-list-check"></i> <span>{{ $mode === 'comparisons' ? 'إظهار جدول السجل المرفق' : 'إخفاء جدول السجل المرفق' }}</span>
                        </button>
                    @endif
                    <button type="button" class="btn-ctrl btn-pdf" onclick="window.print()">
                        <i class="fa fa-file-pdf"></i> طباعة / حفظ كملف PDF
                    </button>
                    <a href="{{ route('gas.index', ['tab' => $mode === 'comparisons' ? 'comparison' : 'analytics']) }}" class="btn-ctrl btn-secondary-ctrl">
                        <i class="fa fa-arrow-right"></i> العودة للبنزينة
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('gas.export_report') }}" id="pdfFilterForm" class="filter-strip">
                <input type="hidden" name="mode" id="inp_mode" value="{{ $mode }}">
                <input type="hidden" name="period" id="inp_period" value="{{ $filterPeriod }}">
                @if($selectedCar)<input type="hidden" name="filter_car" value="{{ $selectedCar }}">@endif
                @if($selectedDriver)<input type="hidden" name="filter_driver" value="{{ $selectedDriver }}">@endif
                @if($mode !== 'comparisons')
                    @if(!empty($compareType))<input type="hidden" name="compare_type" value="{{ $compareType }}">@endif
                    @if(!empty($criterion))<input type="hidden" name="criterion" value="{{ $criterion }}">@endif
                    @if(!empty($compareA))<input type="hidden" name="vehicle_a" value="{{ $compareA }}">@endif
                    @if(!empty($compareB))<input type="hidden" name="vehicle_b" value="{{ $compareB }}">@endif
                @endif

                <label><i class="fa fa-building"></i> شركة النقل:</label>
                <select name="filter_company" onchange="this.form.submit()">
                    <option value="all" {{ $selectedCompanyId === '' ? 'selected' : '' }}>-- كل شركات النقل --</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ (string)$selectedCompanyId === (string)$c->id ? 'selected' : '' }}>
                            {{ $c->company_name ?? $c->name }}
                        </option>
                    @endforeach
                </select>

                @if($mode !== 'comparisons' || ($compareType ?? 'vehicle') !== 'station')
                    <label><i class="fa fa-gas-pump"></i> المحطة:</label>
                    <select name="filter_station" onchange="this.form.submit()">
                        <option value="">-- كل المحطات --</option>
                        @foreach($stations as $st)
                            <option value="{{ $st->id }}" {{ (string)$selectedStationId === (string)$st->id ? 'selected' : '' }}>
                                {{ $st->station_name ?? $st->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <span style="color:#475569;">|</span>
                <label><i class="fa fa-calendar-week"></i> الفترة:</label>
                <button type="button" class="period-pill {{ $filterPeriod === 'week' ? 'active' : '' }}" onclick="setPdfPeriod('week')">آخر أسبوع</button>
                <button type="button" class="period-pill {{ $filterPeriod === 'month' ? 'active' : '' }}" onclick="setPdfPeriod('month')">آخر شهر</button>
                <button type="button" class="period-pill {{ $filterPeriod === 'this_month' ? 'active' : '' }}" onclick="setPdfPeriod('this_month')">هذا الشهر</button>
                <button type="button" class="period-pill {{ $filterPeriod === '3months' ? 'active' : '' }}" onclick="setPdfPeriod('3months')">3 شهور</button>
                <button type="button" class="period-pill {{ $filterPeriod === 'all' ? 'active' : '' }}" onclick="setPdfPeriod('all')">كل الوقت</button>

                <label>من:</label>
                <input type="date" name="filter_from" id="inp_from" value="{{ $resolvedFrom }}" onchange="document.getElementById('inp_period').value='custom'">
                <label>إلى:</label>
                <input type="date" name="filter_to" id="inp_to" value="{{ $resolvedTo }}" onchange="document.getElementById('inp_period').value='custom'">
                <button type="submit" class="period-pill" style="background:#2563eb; color:#fff; border-color:#3b82f6;">تطبيق</button>

                <label style="margin-right:auto; display:inline-flex; align-items:center; gap:5px; cursor:pointer; color:#e2e8f0;">
                    <input type="hidden" name="show_profit" value="0">
                    <input type="checkbox" name="show_profit" value="1" {{ $showProfit ? 'checked' : '' }} onchange="this.form.submit()">
                    <span>إظهار ربح النظام الداخلي</span>
                </label>

                @if($mode === 'comparisons')
                    <div style="width:100%; border-top:1px solid rgba(255,255,255,0.12); padding-top:8px; margin-top:2px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <label><i class="fa fa-scale-balanced" style="color:#60a5fa;"></i> نوع المقارنة:</label>
                        <select name="compare_type" id="inp_compare_type" onchange="onPdfCompareTypeChange()">
                            <option value="vehicle" {{ ($compareType ?? 'vehicle') === 'vehicle' ? 'selected' : '' }}>🚛 الشاحنات والسائقين</option>
                            <option value="station" {{ ($compareType ?? 'vehicle') === 'station' ? 'selected' : '' }}>⛽ محطات الوقود</option>
                            <option value="destination" {{ ($compareType ?? 'vehicle') === 'destination' ? 'selected' : '' }}>🛣️ الوجهات وخطوط السير</option>
                        </select>

                        <label><i class="fa fa-trophy" style="color:#fbbf24;"></i> معيار الترتيب:</label>
                        <select name="criterion" onchange="this.form.submit()">
                            <option value="best_efficiency" {{ ($criterion ?? 'best_efficiency') === 'best_efficiency' ? 'selected' : '' }}>أفضل كفاءة (الأعلى كم/لتر)</option>
                            <option value="worst_efficiency" {{ ($criterion ?? '') === 'worst_efficiency' ? 'selected' : '' }}>أسوأ كفاءة (الأقل كم/لتر)</option>
                            <option value="cheapest_cost_km" {{ ($criterion ?? '') === 'cheapest_cost_km' ? 'selected' : '' }}>أرخص تكلفة (الأقل ج/كم)</option>
                            <option value="highest_cost_km" {{ ($criterion ?? '') === 'highest_cost_km' ? 'selected' : '' }}>أغلى تكلفة (الأعلى ج/كم)</option>
                        </select>

                        <span style="color:#475569;">|</span>
                        @php
                            $groupedOpts = collect($comparePartiesOptions ?? [])->groupBy('group');
                        @endphp
                        <label style="color:#93c5fd; font-weight:800;">■ الطرف (أ):</label>
                        <select name="vehicle_a" id="inp_vehicle_a" style="border:1.5px solid #2563eb; max-width:210px;" onchange="this.form.submit()">
                            @foreach($groupedOpts as $grpName => $grpItems)
                                <optgroup label="{{ $grpName }}">
                                    @foreach($grpItems as $opt)
                                        <option value="{{ $opt['value'] }}" {{ (string)($compareA ?? '') === (string)$opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <label style="color:#fdba74; font-weight:800;">■ الطرف (ب):</label>
                        <select name="vehicle_b" id="inp_vehicle_b" style="border:1.5px solid #ea580c; max-width:210px;" onchange="this.form.submit()">
                            @foreach($groupedOpts as $grpName => $grpItems)
                                <optgroup label="{{ $grpName }}">
                                    @foreach($grpItems as $opt)
                                        <option value="{{ $opt['value'] }}" {{ (string)($compareB ?? '') === (string)$opt['value'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ═══════════ محتوى التقرير المطبوع (A4 Sheet مع هوامش طباعة تلقائية لكل الصفحات بدون URL المتصفح) ═══════════ --}}
    <div class="report-sheet">
        <table class="print-margin-table">
            <thead><tr><td class="print-margin-spacer"></td></tr></thead>
            <tbody><tr><td class="print-margin-body">

                {{-- 1. الترويسة الرسمية --}}
                <div class="doc-header">
                    <div class="brand-block">
                        <h1>شركة الضبع — منظومة إدارة ومحاسبة وقود الشاحنات</h1>
                        <div class="sub">إدارة التشغيل ومتابعة استهلاك الوقود والعُهد النقدية وكفاءة شاحنات النقل</div>
                        <div class="doc-type-pill">
                            @if($mode === 'company_report')
                                <i class="fa fa-file-shield"></i> التقرير الدوري المعتمد لمسحوبات وكفاءة شاحنات النقل
                            @elseif($mode === 'analytics')
                                <i class="fa fa-chart-line"></i> التقرير التحليلي والإحصائي الشامل لمحطة الوقود والشاحنات
                            @elseif($mode === 'comparisons')
                                <i class="fa fa-scale-balanced"></i> تقرير مقارنات كفاءة الشاحنات والمقارنة المباشرة بالشارتات
                            @else
                                <i class="fa fa-receipt"></i> كشف حساب وسجل عمليات الوقود والعُهد النقدية التفصيلي
                            @endif
                        </div>
                    </div>

                    <div class="meta-box">
                        <table>
                            <tr>
                                <td class="k">كود الوثيقة</td>
                                <td class="v" dir="ltr">{{ $reportCode }}</td>
                            </tr>
                            <tr>
                                <td class="k">الفترة المشمولة</td>
                                <td class="v">{{ $periodLabel }}</td>
                            </tr>
                            <tr>
                                <td class="k">نطاق التاريخ</td>
                                <td class="v" dir="ltr">
                                    {{ $resolvedFrom ?: 'البداية' }} &larr; {{ $resolvedTo ?: now()->toDateString() }}
                                </td>
                            </tr>
                            <tr>
                                <td class="k">تاريخ الإصدار</td>
                                <td class="v" dir="ltr">{{ now()->format('Y-m-d H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- 2. شريط الجهة الموجه إليها التقرير --}}
                <div class="client-banner">
                    <div>
                        <div class="to-label">الجهة الصادر لها التقرير / نطاق الفلترة:</div>
                        <div class="to-name">
                            🏢 {{ $companyTitle }}
                            @if($selectedStation)
                                <span style="font-size:12px; color:#475569; font-weight:700;"> — محطة: {{ $selectedStation->station_name ?? $selectedStation->name }}</span>
                            @endif
                            @if($selectedCar)
                                <span style="font-size:12px; color:#1d4ed8;"> — شاحنة: {{ $selectedCar }}</span>
                            @endif
                            @if($selectedDriver)
                                <span style="font-size:12px; color:#0f766e;"> — سائق: {{ $selectedDriver }}</span>
                            @endif
                        </div>
                    </div>
                    <div style="text-align:left;">
                        <div class="to-label">إجمالي الشاحنات العاملة بالفترة</div>
                        <div style="font-size:13px; font-weight:800; color:#0f172a;">
                            {{ $summary->active_trucks_count }} شاحنة · ({{ $summary->fuel_ops }} تفويلة وقود + {{ $summary->custody_ops }} عهدة مستقلة)
                        </div>
                    </div>
                </div>

                @if($reportTransactions->isEmpty())
                    <div style="border: 2px dashed #cbd5e1; border-radius: 10px; padding: 36px; text-align: center; margin: 20px 0;">
                        <div style="font-size: 16px; font-weight: 800; color: #334155; margin-bottom: 6px;">لا توجد عمليات مسجلة ضمن الفترة أو الفلاتر المحددة</div>
                        <div style="color: #64748b; font-size: 12px;">يمكنك تغيير الفترة الزمنية من الشريط العلوي (مثل: آخر شهر أو كل الوقت) لإعادة عرض التقرير.</div>
                    </div>
                @else

                    {{-- 3. الملخص التنفيذي والمالي (KPIs) --}}
                    <div class="kpi-grid">
                        <div class="kpi-card amber">
                            <span class="lbl">إجمالي الوقود المسحوب</span>
                            <span class="val c-amber">{{ number_format($summary->total_liters, 1) }} لتر</span>
                            <span class="sub">{{ $summary->fuel_ops }} تفويلة ({{ $summary->full_ops }} كاملة{{ $summary->partial_ops > 0 ? ' · ' . $summary->partial_ops . ' جزئية' : '' }})</span>
                        </div>
                        <div class="kpi-card">
                            <span class="lbl">صافي حساب مسحوبات الوقود</span>
                            <span class="val c-blue">{{ number_format($summary->fuel_billing, 2) }} ج</span>
                            <span class="sub">متوسط اللتر: {{ $summary->avg_price_liter > 0 ? number_format($summary->avg_price_liter, 2) . ' ج' : '—' }}</span>
                        </div>
                        <div class="kpi-card orange">
                            <span class="lbl">إجمالي العُهد النقدية والمصروفات</span>
                            <span class="val c-orange">{{ number_format($summary->total_advance, 2) }} ج</span>
                            <span class="sub">{{ $summary->custody_ops }} عملية عهدة بدون وقود</span>
                        </div>
                        <div class="kpi-card navy">
                            <span class="lbl">إجمالي المستحق عن الفترة</span>
                            <span class="val">{{ number_format($summary->total_on_company, 2) }} ج</span>
                            <span class="sub">إجمالي {{ $summary->total_ops }} حركة مسجلة</span>
                        </div>
                        <div class="kpi-card purple">
                            <span class="lbl">المسافات المقطوعة بالعداد</span>
                            <span class="val" style="color:#6d28d9;">{{ number_format($summary->total_distance) }} كم</span>
                            <span class="sub">{{ $summary->closed_trips }} رحلة مقفلة بالعداد</span>
                        </div>
                        <div class="kpi-card green">
                            <span class="lbl">متوسط كفاءة الشاحنات</span>
                            <span class="val {{ $summary->avg_efficiency > 0 && $summary->avg_efficiency < $effectiveMinEff ? 'c-red' : 'c-green' }}">
                                {{ $summary->avg_efficiency > 0 ? number_format($summary->avg_efficiency, 2) . ' كم/لتر' : '—' }}
                            </span>
                            <span class="sub">تكلفة الكيلو: {{ $summary->avg_cost_km > 0 ? number_format($summary->avg_cost_km, 2) . ' ج/كم' : '—' }}</span>
                        </div>
                    </div>

                    {{-- ═══════════ 4-أ. وضع تقرير المقارنات والشارتات (mode = comparisons) ═══════════ --}}
                    @if($mode === 'comparisons')
                        @php
                            $criterionLabels = [
                                'best_efficiency'  => 'الأفضل كفاءة في استهلاك الوقود (الأعلى كم/لتر)',
                                'worst_efficiency' => 'الأسوأ كفاءة والأعلى استهلاكاً (الأقل كم/لتر)',
                                'cheapest_cost_km' => 'الأرخص تكلفة تشغيل للكيلومتر (الأقل ج/كم)',
                                'highest_cost_km'  => 'الأغلى تكلفة تشغيل للكيلومتر (الأعلى ج/كم)',
                            ];
                            $activeCritTitle = $criterionLabels[$criterion ?? 'best_efficiency'] ?? $criterionLabels['best_efficiency'];
                            $isEffRankMode   = in_array($criterion ?? 'best_efficiency', ['best_efficiency', 'worst_efficiency'], true);
                            $rankPalette     = ['#2563eb', '#ea580c', '#10b981', '#7c3aed', '#d97706', '#0891b2'];
                            $rankCatsMeta    = [
                                ['key' => 'drivers',      'title' => '👤 ترتيب السائقين',               'empty' => 'لا توجد بيانات سائقين بالفترة'],
                                ['key' => 'cars',         'title' => '🚛 ترتيب العربيات والشاحنات',      'empty' => 'لا توجد بيانات شاحنات بالفترة'],
                                ['key' => 'destinations', 'title' => '🛣️ ترتيب الوجهات وخطوط السير',     'empty' => 'لا توجد وجهات مسجلة بالفترة'],
                                ['key' => 'stations',     'title' => '⛽ ترتيب محطات الوقود',            'empty' => 'لا توجد محطات مسجلة بالفترة'],
                            ];
                        @endphp

                        <div class="sec-head">
                            <h3>🏆 أولاً: لوحة ترتيب وشارتات الشاحنات للأقسام الأربعة — ({{ $activeCritTitle }})</h3>
                            <span>مبنية حصرياً على التفويلات الفعلية · دقة طباعة Vector SVG</span>
                        </div>

                        <div class="charts-grid-2">
                            @foreach($rankCatsMeta as $catMeta)
                                @php
                                    $catItems = array_slice($comparisonRankings[$catMeta['key']] ?? [], 0, 6);
                                    $maxMetric = 1.0;
                                    foreach ($catItems as $ci) {
                                        $mv = $isEffRankMode ? (float) $ci->avg_efficiency : (float) $ci->avg_cost_km;
                                        if ($mv > $maxMetric) {
                                            $maxMetric = $mv;
                                        }
                                    }
                                    $maxFallbackLiters = 1.0;
                                    foreach ($catItems as $ci) {
                                        if ((float) $ci->total_liters > $maxFallbackLiters) {
                                            $maxFallbackLiters = (float) $ci->total_liters;
                                        }
                                    }
                                @endphp
                                <div class="chart-card">
                                    <div class="chart-card-head">
                                        <span class="chart-card-title">{{ $catMeta['title'] }}</span>
                                        <span class="chart-card-sub">أعلى {{ count($catItems) }} عناصر</span>
                                    </div>
                                    @if(empty($catItems))
                                        <div style="text-align:center; padding:18px; color:#64748b;">{{ $catMeta['empty'] }}</div>
                                    @else
                                        <div class="hbar-list">
                                            @foreach($catItems as $rIdx => $rItem)
                                                @php
                                                    $mVal = $isEffRankMode ? (float) $rItem->avg_efficiency : (float) $rItem->avg_cost_km;
                                                    if ($mVal > 0) {
                                                        $barW200 = max(10, round(($mVal / $maxMetric) * 200, 1));
                                                        $mBadge  = number_format($mVal, 2) . ($isEffRankMode ? ' كم/ل' : ' ج/كم');
                                                    } else {
                                                        $barW200 = max(10, round(((float) $rItem->total_liters / $maxFallbackLiters) * 140, 1));
                                                        $mBadge  = number_format((float) $rItem->total_liters, 0) . ' لتر';
                                                    }
                                                    $rCol = $rankPalette[$rIdx % count($rankPalette)];
                                                @endphp
                                                <div class="hbar-row">
                                                    <div class="hbar-name" title="{{ $rItem->name }}">{{ $rIdx + 1 }}. {{ $rItem->name }}</div>
                                                    <div class="hbar-bar-cell">
                                                        <svg viewBox="0 0 200 14" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                                            <rect x="0" y="0" width="200" height="14" rx="4" fill="#f1f5f9"/>
                                                            <rect x="{{ round(200 - $barW200, 1) }}" y="0" width="{{ $barW200 }}" height="14" rx="4" fill="{{ $rCol }}"/>
                                                        </svg>
                                                    </div>
                                                    <div class="hbar-metric" style="color:{{ $rCol }};">{{ $mBadge }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- القسم الثاني: المقارنة المباشرة (الطرف أ ضد الطرف ب) --}}
                        @if(!empty($comparisonHeadToHead) && !empty($comparisonHeadToHead['vehicle_a']) && !empty($comparisonHeadToHead['vehicle_b']))
                            @php
                                $cmpA       = $comparisonHeadToHead['vehicle_a'];
                                $cmpB       = $comparisonHeadToHead['vehicle_b'];
                                $cmpWinners = $comparisonHeadToHead['winners'] ?? [];
                                $cmpStd     = $comparisonHeadToHead['radar_standards'] ?? ['effective_min' => $effectiveMinEff, 'effective_max' => $effectiveMaxCostKm];
                                $cmpDests   = $comparisonHeadToHead['destinations_comparison'] ?? [];
                                $isVehMode  = ($comparisonHeadToHead['compare_type'] ?? 'vehicle') === 'vehicle';

                                $titleA = !$isVehMode
                                    ? ($cmpA['vehicle_key'] ?? 'الطرف أ')
                                    : ((!empty($cmpA['drivers']) && count($cmpA['drivers']) === 1 && $cmpA['drivers'][0] === $cmpA['vehicle_key'])
                                        ? $cmpA['vehicle_key']
                                        : (($cmpA['car_number'] ?? '-') !== '-' ? $cmpA['car_number'] : $cmpA['vehicle_key']));

                                $titleB = !$isVehMode
                                    ? ($cmpB['vehicle_key'] ?? 'الطرف ب')
                                    : ((!empty($cmpB['drivers']) && count($cmpB['drivers']) === 1 && $cmpB['drivers'][0] === $cmpB['vehicle_key'])
                                        ? $cmpB['vehicle_key']
                                        : (($cmpB['car_number'] ?? '-') !== '-' ? $cmpB['car_number'] : $cmpB['vehicle_key']));

                                $shortA = mb_strlen($titleA) > 22 ? mb_substr($titleA, 0, 22) . '..' : $titleA;
                                $shortB = mb_strlen($titleB) > 22 ? mb_substr($titleB, 0, 22) . '..' : $titleB;

                                $effBars = [
                                    ['label' => 'المعدل العام', 'a' => (float) ($cmpA['avg_efficiency'] ?? 0), 'b' => (float) ($cmpB['avg_efficiency'] ?? 0)],
                                    ['label' => 'أعلى كفاءة',   'a' => (float) ($cmpA['max_efficiency'] ?: ($cmpA['avg_efficiency'] ?? 0)), 'b' => (float) ($cmpB['max_efficiency'] ?: ($cmpB['avg_efficiency'] ?? 0))],
                                    ['label' => 'آخر رحلة',     'a' => (float) ($cmpA['last_rate'] ?: ($cmpA['avg_efficiency'] ?? 0)), 'b' => (float) ($cmpB['last_rate'] ?: ($cmpB['avg_efficiency'] ?? 0))],
                                ];
                                $costBars = [
                                    ['label' => 'تكلفة الكيلو (ج/كم)', 'a' => (float) ($cmpA['avg_cost_km'] ?? 0), 'b' => (float) ($cmpB['avg_cost_km'] ?? 0)],
                                    ['label' => 'متوسط اللتر (ج)',     'a' => (float) ($cmpA['avg_price_per_liter'] ?? 0), 'b' => (float) ($cmpB['avg_price_per_liter'] ?? 0)],
                                ];
                                $commonRoutesForCharts = array_slice(array_values(array_filter($cmpDests, fn($d) => !empty($d['in_both']))), 0, 1);
                                foreach ($commonRoutesForCharts as $cr) {
                                    $crLoc = mb_strlen($cr['location']) > 12 ? mb_substr($cr['location'], 0, 12) . '..' : $cr['location'];
                                    if (($cr['eff_a'] ?? 0) > 0 || ($cr['eff_b'] ?? 0) > 0) {
                                        $effBars[] = ['label' => $crLoc, 'a' => (float) ($cr['eff_a'] ?? 0), 'b' => (float) ($cr['eff_b'] ?? 0)];
                                    }
                                    if (($cr['cost_km_a'] ?? 0) > 0 || ($cr['cost_km_b'] ?? 0) > 0) {
                                        $costBars[] = ['label' => $crLoc, 'a' => (float) ($cr['cost_km_a'] ?? 0), 'b' => (float) ($cr['cost_km_b'] ?? 0)];
                                    }
                                }

                                $verdictParts = [];
                                if (($cmpWinners['efficiency'] ?? null) === 'A') {
                                    $verdictParts[] = "الأفضل كفاءة استهلاك: (أ) {$titleA} بمعدل {$cmpA['avg_efficiency']} كم/لتر" . (!empty($cmpWinners['eff_diff_pct']) ? " (وفر {$cmpWinners['eff_diff_pct']}%)" : '');
                                } elseif (($cmpWinners['efficiency'] ?? null) === 'B') {
                                    $verdictParts[] = "الأفضل كفاءة استهلاك: (ب) {$titleB} بمعدل {$cmpB['avg_efficiency']} كم/لتر" . (!empty($cmpWinners['eff_diff_pct']) ? " (وفر {$cmpWinners['eff_diff_pct']}%)" : '');
                                }
                                if (($cmpWinners['cost'] ?? null) === 'A') {
                                    $verdictParts[] = "الأرخص تكلفة للكيلو: (أ) {$titleA} ({$cmpA['avg_cost_km']} ج/كم)";
                                } elseif (($cmpWinners['cost'] ?? null) === 'B') {
                                    $verdictParts[] = "الأرخص تكلفة للكيلو: (ب) {$titleB} ({$cmpB['avg_cost_km']} ج/كم)";
                                }
                                if (empty($verdictParts)) {
                                    $verdictParts[] = "مقارنة حجم التشغيل: (أ) {$titleA} (" . number_format($cmpA['total_liters'] ?? 0, 1) . " لتر) مقابل (ب) {$titleB} (" . number_format($cmpB['total_liters'] ?? 0, 1) . " لتر)";
                                }
                            @endphp

                            <div class="sec-head" style="border-right-color:#2563eb;">
                                <h3>⚖️ ثانياً: المقارنة المباشرة والشارتات — <span style="color:#1d4ed8;">(أ) {{ $titleA }}</span> 🆚 <span style="color:#c2410c;">(ب) {{ $titleB }}</span></h3>
                                <span>
                                    <strong style="color:#2563eb;">■ الطرف (أ): {{ $shortA }}</strong>
                                    &nbsp;|&nbsp;
                                    <strong style="color:#ea580c;">■ الطرف (ب): {{ $shortB }}</strong>
                                </span>
                            </div>

                            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-right:4px solid #16a34a; border-radius:8px; padding:7px 11px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                <div style="font-weight:800; color:#14532d; font-size:10.5px;">
                                    ✓ الخلاصة الفنية للمقارنة: <span style="font-weight:700; color:#1e293b;">{{ implode('  ·  ', $verdictParts) }}</span>
                                </div>
                                <div style="font-size:9.5px; color:#475569; font-weight:700;">
                                    معيار الأمان المعتمد: ≥ {{ $cmpStd['effective_min'] }} كم/لتر · ≤ {{ $cmpStd['effective_max'] }} ج/كم
                                </div>
                            </div>

                            <div class="charts-grid-2">
                                {{-- شارت 1: مقارنة الكفاءة (كم / لتر) --}}
                                <div class="chart-card">
                                    <div class="chart-card-head">
                                        <span class="chart-card-title">⚡ شارت مقارنة كفاءة الاستهلاك (كم / لتر)</span>
                                        <span class="chart-card-sub">الأعلى هو الأفضل اقتصادياً</span>
                                    </div>
                                    <div class="chart-legend-strip">
                                        <span style="color:#1d4ed8;"><span class="legend-dot" style="background:#2563eb;"></span>(أ) {{ $shortA }}</span>
                                        <span style="color:#c2410c;"><span class="legend-dot" style="background:#ea580c;"></span>(ب) {{ $shortB }}</span>
                                    </div>
                                    <div class="svg-wrap">
                                        @php
                                            $cSvgW = 440; $cSvgH = 172; $cPadL = 40; $cPadR = 16; $cPadT = 18; $cPadB = 28;
                                            $cPlotW = $cSvgW - $cPadL - $cPadR;
                                            $cPlotH = $cSvgH - $cPadT - $cPadB;
                                            $cBaseY = $cPadT + $cPlotH;
                                            $maxEffCmp = max(1.0, (float) $cmpStd['effective_min']);
                                            foreach ($effBars as $eb) {
                                                $maxEffCmp = max($maxEffCmp, $eb['a'], $eb['b']);
                                            }
                                            $maxEffCmp = round($maxEffCmp * 1.24, 2);
                                            $refEffVal = (float) $cmpStd['effective_min'];
                                            $refEffY   = round($cPadT + $cPlotH - (($refEffVal / $maxEffCmp) * $cPlotH), 1);
                                            $eSlot     = $cPlotW / max(1, count($effBars));
                                            $eBarW     = min(26, round($eSlot * 0.28, 1));
                                        @endphp
                                        <svg viewBox="0 0 {{ $cSvgW }} {{ $cSvgH }}" xmlns="http://www.w3.org/2000/svg" dir="ltr">
                                            @for($g = 0; $g <= 3; $g++)
                                                @php
                                                    $gy = round($cPadT + (($g / 3) * $cPlotH), 1);
                                                    $gv = number_format($maxEffCmp * (1 - ($g / 3)), 1);
                                                @endphp
                                                <line x1="{{ $cPadL }}" y1="{{ $gy }}" x2="{{ $cSvgW - $cPadR }}" y2="{{ $gy }}" stroke="#e2e8f0" stroke-dasharray="3,3"/>
                                                <text x="{{ $cPadL - 5 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="8.5" font-weight="700" fill="#64748b">{{ $gv }}</text>
                                            @endfor

                                            @if($refEffVal > 0 && $refEffVal < $maxEffCmp)
                                                <line x1="{{ $cPadL }}" y1="{{ $refEffY }}" x2="{{ $cSvgW - $cPadR }}" y2="{{ $refEffY }}" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4,3"/>
                                                <text x="{{ $cSvgW - $cPadR }}" y="{{ max(12, $refEffY - 3) }}" text-anchor="end" font-size="8" font-weight="800" fill="#dc2626">{{ $refEffVal }} (حد الأمان)</text>
                                            @endif

                                            @foreach($effBars as $bIdx => $eb)
                                                @php
                                                    $cx = round($cPadL + ($bIdx * $eSlot) + ($eSlot / 2), 1);
                                                    $hA = $eb['a'] > 0 ? max(4, round(($eb['a'] / $maxEffCmp) * $cPlotH, 1)) : 2;
                                                    $hB = $eb['b'] > 0 ? max(4, round(($eb['b'] / $maxEffCmp) * $cPlotH, 1)) : 2;
                                                    $yA = round($cBaseY - $hA, 1);
                                                    $yB = round($cBaseY - $hB, 1);
                                                @endphp
                                                <rect x="{{ round($cx - $eBarW - 2, 1) }}" y="{{ $yA }}" width="{{ $eBarW }}" height="{{ $hA }}" rx="3" fill="#2563eb"/>
                                                <rect x="{{ round($cx + 2, 1) }}" y="{{ $yB }}" width="{{ $eBarW }}" height="{{ $hB }}" rx="3" fill="#ea580c"/>
                                                @if($eb['a'] > 0)
                                                    <text x="{{ round($cx - ($eBarW / 2) - 2, 1) }}" y="{{ max(12, $yA - 4) }}" text-anchor="middle" font-size="8.5" font-weight="800" fill="#1d4ed8">{{ number_format($eb['a'], 2) }}</text>
                                                @endif
                                                @if($eb['b'] > 0)
                                                    <text x="{{ round($cx + ($eBarW / 2) + 2, 1) }}" y="{{ max(12, $yB - 4) }}" text-anchor="middle" font-size="8.5" font-weight="800" fill="#c2410c">{{ number_format($eb['b'], 2) }}</text>
                                                @endif
                                                <text x="{{ $cx }}" y="{{ $cSvgH - 8 }}" text-anchor="middle" font-size="9" font-weight="700" fill="#334155">{{ $eb['label'] }}</text>
                                            @endforeach
                                        </svg>
                                    </div>
                                </div>

                                {{-- شارت 2: مقارنة تكلفة الكيلومتر وسعر اللتر (ج.م) --}}
                                <div class="chart-card">
                                    <div class="chart-card-head">
                                        <span class="chart-card-title">💰 شارت تكلفة الكيلومتر وسعر اللتر (ج.م)</span>
                                        <span class="chart-card-sub">الأقل هو الأوفر مالياً</span>
                                    </div>
                                    <div class="chart-legend-strip">
                                        <span style="color:#1d4ed8;"><span class="legend-dot" style="background:#2563eb;"></span>(أ) {{ $shortA }}</span>
                                        <span style="color:#c2410c;"><span class="legend-dot" style="background:#ea580c;"></span>(ب) {{ $shortB }}</span>
                                    </div>
                                    <div class="svg-wrap">
                                        @php
                                            $maxCostCmp = max(1.0, (float) $cmpStd['effective_max']);
                                            foreach ($costBars as $cb) {
                                                $maxCostCmp = max($maxCostCmp, $cb['a'], $cb['b']);
                                            }
                                            $maxCostCmp = round($maxCostCmp * 1.22, 2);
                                            $refCostVal = (float) $cmpStd['effective_max'];
                                            $refCostY   = round($cPadT + $cPlotH - (($refCostVal / $maxCostCmp) * $cPlotH), 1);
                                            $kSlot      = $cPlotW / max(1, count($costBars));
                                            $kBarW      = min(28, round($kSlot * 0.28, 1));
                                        @endphp
                                        <svg viewBox="0 0 {{ $cSvgW }} {{ $cSvgH }}" xmlns="http://www.w3.org/2000/svg" dir="ltr">
                                            @for($g = 0; $g <= 3; $g++)
                                                @php
                                                    $gy = round($cPadT + (($g / 3) * $cPlotH), 1);
                                                    $gv = number_format($maxCostCmp * (1 - ($g / 3)), 1);
                                                @endphp
                                                <line x1="{{ $cPadL }}" y1="{{ $gy }}" x2="{{ $cSvgW - $cPadR }}" y2="{{ $gy }}" stroke="#e2e8f0" stroke-dasharray="3,3"/>
                                                <text x="{{ $cPadL - 5 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="8.5" font-weight="700" fill="#64748b">{{ $gv }}</text>
                                            @endfor

                                            @if($refCostVal > 0 && $refCostVal < $maxCostCmp)
                                                <line x1="{{ $cPadL }}" y1="{{ $refCostY }}" x2="{{ $cSvgW - $cPadR }}" y2="{{ $refCostY }}" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4,3"/>
                                                <text x="{{ $cSvgW - $cPadR }}" y="{{ max(12, $refCostY - 3) }}" text-anchor="end" font-size="8" font-weight="800" fill="#dc2626">{{ $refCostVal }} (سقف ج/كم)</text>
                                            @endif

                                            @foreach($costBars as $bIdx => $cb)
                                                @php
                                                    $cx = round($cPadL + ($bIdx * $kSlot) + ($kSlot / 2), 1);
                                                    $hA = $cb['a'] > 0 ? max(4, round(($cb['a'] / $maxCostCmp) * $cPlotH, 1)) : 2;
                                                    $hB = $cb['b'] > 0 ? max(4, round(($cb['b'] / $maxCostCmp) * $cPlotH, 1)) : 2;
                                                    $yA = round($cBaseY - $hA, 1);
                                                    $yB = round($cBaseY - $hB, 1);
                                                @endphp
                                                <rect x="{{ round($cx - $kBarW - 2, 1) }}" y="{{ $yA }}" width="{{ $kBarW }}" height="{{ $hA }}" rx="3" fill="#2563eb"/>
                                                <rect x="{{ round($cx + 2, 1) }}" y="{{ $yB }}" width="{{ $kBarW }}" height="{{ $hB }}" rx="3" fill="#ea580c"/>
                                                @if($cb['a'] > 0)
                                                    <text x="{{ round($cx - ($kBarW / 2) - 2, 1) }}" y="{{ max(12, $yA - 4) }}" text-anchor="middle" font-size="8.5" font-weight="800" fill="#1d4ed8">{{ number_format($cb['a'], 2) }}</text>
                                                @endif
                                                @if($cb['b'] > 0)
                                                    <text x="{{ round($cx + ($kBarW / 2) + 2, 1) }}" y="{{ max(12, $yB - 4) }}" text-anchor="middle" font-size="8.5" font-weight="800" fill="#c2410c">{{ number_format($cb['b'], 2) }}</text>
                                                @endif
                                                <text x="{{ $cx }}" y="{{ $cSvgH - 8 }}" text-anchor="middle" font-size="9" font-weight="700" fill="#334155">{{ $cb['label'] }}</text>
                                            @endforeach
                                        </svg>
                                    </div>
                                </div>

                                {{-- شارت 3: مقارنة حجم التشغيل والحصص النسبية --}}
                                <div class="chart-card full-span">
                                    <div class="chart-card-head">
                                        <span class="chart-card-title">📊 شارت مقارنة حجم التشغيل (الوقود · المسافة · التفويلات · التكلفة)</span>
                                        <span class="chart-card-sub">مقارنة تناسبية مباشرة بين الطرف (أ) الأزرق والطرف (ب) البرتقالي</span>
                                    </div>
                                    @php
                                        $volSplits = [
                                            ['title' => 'إجمالي الوقود المسحوب',    'a' => (float) ($cmpA['total_liters'] ?? 0),   'b' => (float) ($cmpB['total_liters'] ?? 0),   'unit' => 'لتر',  'dec' => 1],
                                            ['title' => 'المسافة المقطوعة بالعداد', 'a' => (float) ($cmpA['total_distance'] ?? 0), 'b' => (float) ($cmpB['total_distance'] ?? 0), 'unit' => 'كم',   'dec' => 0],
                                            ['title' => 'عدد التفويلات الفعلية',    'a' => (float) ($cmpA['total_trips'] ?? 0),    'b' => (float) ($cmpB['total_trips'] ?? 0),    'unit' => 'رحلة', 'dec' => 0],
                                            ['title' => 'إجمالي التكلفة المالية',   'a' => (float) ($cmpA['total_cost'] ?? 0),     'b' => (float) ($cmpB['total_cost'] ?? 0),     'unit' => 'ج.م',  'dec' => 0],
                                        ];
                                    @endphp
                                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px 18px; padding-top:4px;">
                                        @foreach($volSplits as $vs)
                                            @php
                                                $sumAB = max(0.01, $vs['a'] + $vs['b']);
                                                $pctA  = ($vs['a'] + $vs['b']) > 0 ? round(($vs['a'] / $sumAB) * 100, 1) : 50;
                                                $pctB  = round(100 - $pctA, 1);
                                            @endphp
                                            <div>
                                                <div style="display:flex; justify-content:space-between; font-size:9.5px; font-weight:800; margin-bottom:3px; gap:4px;">
                                                    <span style="color:#1d4ed8;">(أ) {{ number_format($vs['a'], $vs['dec']) }} {{ $vs['unit'] }} ({{ $pctA }}%)</span>
                                                    <span style="color:#0f172a;">{{ $vs['title'] }}</span>
                                                    <span style="color:#c2410c;">(ب) {{ number_format($vs['b'], $vs['dec']) }} {{ $vs['unit'] }} ({{ $pctB }}%)</span>
                                                </div>
                                                <svg viewBox="0 0 400 12" width="100%" height="12" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="direction:ltr;">
                                                    <rect x="0" y="0" width="400" height="12" rx="6" fill="#ea580c"/>
                                                    <rect x="{{ round(400 - ($pctA * 4), 1) }}" y="0" width="{{ round($pctA * 4, 1) }}" height="12" rx="6" fill="#2563eb"/>
                                                </svg>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- جدول المقارنة التفصيلي للمؤشرات الفنية والمالية --}}
                            <table class="tbl">
                                <colgroup>
                                    <col style="width:26%;">
                                    <col style="width:27%;">
                                    <col style="width:27%;">
                                    <col style="width:20%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="text-right">مؤشر المقارنة الفني والمالي</th>
                                        <th style="background:#1d4ed8;">(أ) {{ $titleA }}</th>
                                        <th style="background:#c2410c;">(ب) {{ $titleB }}</th>
                                        <th>الأفضلية / الفارق</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-right fw-800">متوسط كفاءة الاستهلاك (كم / لتر)</td>
                                        <td dir="ltr" class="fw-800 c-blue">{{ ($cmpA['avg_efficiency'] ?? 0) > 0 ? number_format($cmpA['avg_efficiency'], 2) . ' كم/لتر' : 'رحلة جارية' }}</td>
                                        <td dir="ltr" class="fw-800 c-orange">{{ ($cmpB['avg_efficiency'] ?? 0) > 0 ? number_format($cmpB['avg_efficiency'], 2) . ' كم/لتر' : 'رحلة جارية' }}</td>
                                        <td class="fw-bold">
                                            @if(($cmpWinners['efficiency'] ?? null) === 'A')
                                                <span class="b-ok">تفوق (أ) +{{ $cmpWinners['eff_diff_pct'] ?? 0 }}%</span>
                                            @elseif(($cmpWinners['efficiency'] ?? null) === 'B')
                                                <span class="b-ok">تفوق (ب) +{{ $cmpWinners['eff_diff_pct'] ?? 0 }}%</span>
                                            @else
                                                <span class="c-muted">متقارب / قيد القياس</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-right fw-800">متوسط تكلفة الكيلومتر (ج.م / كم)</td>
                                        <td dir="ltr" class="fw-800 c-blue">{{ ($cmpA['avg_cost_km'] ?? 0) > 0 ? number_format($cmpA['avg_cost_km'], 2) . ' ج/كم' : '—' }}</td>
                                        <td dir="ltr" class="fw-800 c-orange">{{ ($cmpB['avg_cost_km'] ?? 0) > 0 ? number_format($cmpB['avg_cost_km'], 2) . ' ج/كم' : '—' }}</td>
                                        <td class="fw-bold">
                                            @if(($cmpWinners['cost'] ?? null) === 'A')
                                                <span class="b-ok">الأوفر: (أ) {{ $shortA }}</span>
                                            @elseif(($cmpWinners['cost'] ?? null) === 'B')
                                                <span class="b-ok">الأوفر: (ب) {{ $shortB }}</span>
                                            @else
                                                <span class="c-muted">متقارب</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-right fw-800">تصنيف العمليات (وقود / عُهد)</td>
                                        <td>
                                            @if(($cmpA['full_fills'] ?? 0) > 0)<span class="b-full">{{ $cmpA['full_fills'] }} كامل</span>@endif
                                            @if(($cmpA['partial_fills'] ?? 0) > 0)<span class="b-partial">{{ $cmpA['partial_fills'] }} جزئي</span>@endif
                                            @if(($cmpA['custody_fills'] ?? 0) > 0)<span class="b-custody">{{ $cmpA['custody_fills'] }} عهدة</span>@endif
                                        </td>
                                        <td>
                                            @if(($cmpB['full_fills'] ?? 0) > 0)<span class="b-full">{{ $cmpB['full_fills'] }} كامل</span>@endif
                                            @if(($cmpB['partial_fills'] ?? 0) > 0)<span class="b-partial">{{ $cmpB['partial_fills'] }} جزئي</span>@endif
                                            @if(($cmpB['custody_fills'] ?? 0) > 0)<span class="b-custody">{{ $cmpB['custody_fills'] }} عهدة</span>@endif
                                        </td>
                                        <td class="c-muted">{{ ($cmpA['total_trips'] ?? 0) }} مقابل {{ ($cmpB['total_trips'] ?? 0) }} تفويلة</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right fw-800">إجمالي المسافة المقطوعة بالعداد</td>
                                        <td dir="ltr" class="fw-bold">{{ number_format($cmpA['total_distance'] ?? 0) }} كم</td>
                                        <td dir="ltr" class="fw-bold">{{ number_format($cmpB['total_distance'] ?? 0) }} كم</td>
                                        <td class="fw-bold">{{ ($cmpA['total_distance'] ?? 0) >= ($cmpB['total_distance'] ?? 0) ? '(أ) ' . $shortA : '(ب) ' . $shortB }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right fw-800">إجمالي الوقود المسحوب (لتر)</td>
                                        <td class="fw-bold c-amber">{{ number_format($cmpA['total_liters'] ?? 0, 1) }} لتر</td>
                                        <td class="fw-bold c-amber">{{ number_format($cmpB['total_liters'] ?? 0, 1) }} لتر</td>
                                        <td class="c-muted">فارق {{ number_format(abs(($cmpA['total_liters'] ?? 0) - ($cmpB['total_liters'] ?? 0)), 1) }} لتر</td>
                                    </tr>
                                    <tr>
                                        <td class="text-right fw-800">العُهد النقدية / الإجمالي المالي المستحق</td>
                                        <td class="fw-800">{{ number_format($cmpA['total_cost'] ?? 0, 2) }} ج <span class="c-muted">({{ number_format($cmpA['total_advance'] ?? 0, 0) }} ج عهدة)</span></td>
                                        <td class="fw-800">{{ number_format($cmpB['total_cost'] ?? 0, 2) }} ج <span class="c-muted">({{ number_format($cmpB['total_advance'] ?? 0, 0) }} ج عهدة)</span></td>
                                        <td class="fw-bold">
                                            @if(($cmpA['suspicion_count'] ?? 0) === 0 && ($cmpB['suspicion_count'] ?? 0) === 0)
                                                <span class="b-ok">سجل آمن للطرفين ✓</span>
                                            @else
                                                <span class="b-alert">تنبيهات: (أ) {{ $cmpA['suspicion_count'] ?? 0 }} | (ب) {{ $cmpB['suspicion_count'] ?? 0 }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- جدول مقارنة الوجهات وخطوط السير المشتركة --}}
                            @if(!empty($cmpDests))
                                <div class="sec-head">
                                    <h3>🛣️ ثالثاً: مقارنة الأداء على الوجهات وخطوط السير ({{ count(array_filter($cmpDests, fn($x) => !empty($x['in_both']))) }} خط مشترك من إجمالي {{ count($cmpDests) }})</h3>
                                    <span>تُعرض الخطوط المشتركة في المقدمة لقياس عدالة الاستهلاك على نفس الطريق</span>
                                </div>
                                <table class="tbl">
                                    <colgroup>
                                        <col style="width:28%;">
                                        <col style="width:12%;">
                                        <col style="width:12%;">
                                        <col style="width:12%;">
                                        <col style="width:12%;">
                                        <col style="width:24%;">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th class="text-right">{{ ($compareType ?? 'vehicle') === 'destination' ? 'محطة الوقود' : 'الوجهة / خط السير' }}</th>
                                            <th style="background:#1d4ed8;">كفاءة (أ)</th>
                                            <th style="background:#1d4ed8;">تكلفة كم (أ)</th>
                                            <th style="background:#c2410c;">كفاءة (ب)</th>
                                            <th style="background:#c2410c;">تكلفة كم (ب)</th>
                                            <th>نتيجة المقارنة على الخط</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice($cmpDests, 0, 14) as $dRow)
                                            <tr class="{{ !empty($dRow['has_suspicion']) ? 'row-alert' : '' }}">
                                                <td class="text-right fw-800">
                                                    @if(!empty($dRow['in_both']))
                                                        <span class="b-custody" style="margin-left:4px;">مشترك</span>
                                                    @endif
                                                    {{ $dRow['location'] }}
                                                </td>
                                                <td dir="ltr" class="fw-bold c-blue">{{ ($dRow['eff_a'] ?? 0) > 0 ? number_format($dRow['eff_a'], 2) . ' كم/ل' : '—' }}</td>
                                                <td dir="ltr" class="fw-bold">{{ ($dRow['cost_km_a'] ?? 0) > 0 ? number_format($dRow['cost_km_a'], 2) . ' ج/كم' : '—' }}</td>
                                                <td dir="ltr" class="fw-bold c-orange">{{ ($dRow['eff_b'] ?? 0) > 0 ? number_format($dRow['eff_b'], 2) . ' كم/ل' : '—' }}</td>
                                                <td dir="ltr" class="fw-bold">{{ ($dRow['cost_km_b'] ?? 0) > 0 ? number_format($dRow['cost_km_b'], 2) . ' ج/كم' : '—' }}</td>
                                                <td class="fw-bold">
                                                    @if(!empty($dRow['in_both']))
                                                        @if(($dRow['higher_cost'] ?? null) === 'A')
                                                            <span class="c-orange">الطرف (ب) أوفر بـ {{ number_format(abs(($dRow['cost_km_a'] ?? 0) - ($dRow['cost_km_b'] ?? 0)), 2) }} ج/كم</span>
                                                        @elseif(($dRow['higher_cost'] ?? null) === 'B')
                                                            <span class="c-blue">الطرف (أ) أوفر بـ {{ number_format(abs(($dRow['cost_km_a'] ?? 0) - ($dRow['cost_km_b'] ?? 0)), 2) }} ج/كم</span>
                                                        @else
                                                            <span class="c-muted">متعادل على هذا الخط</span>
                                                        @endif
                                                    @else
                                                        <span class="c-muted">{{ !empty($dRow['stat_a']) ? 'تشغيل الطرف (أ) فقط (' . ($dRow['stat_a']['trips_count'] ?? 1) . ' رحلة)' : 'تشغيل الطرف (ب) فقط (' . ($dRow['stat_b']['trips_count'] ?? 1) . ' رحلة)' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        @else
                            <div style="border:1.5px dashed #cbd5e1; border-radius:8px; padding:18px; text-align:center; color:#475569; margin-bottom:12px;">
                                اختر طرفين مختلفين من الشريط العلوي (الطرف أ والطرف ب) لعرض شارتات وجداول المقارنة المباشرة.
                            </div>
                        @endif
                    @endif

                    {{-- ═══════════ 4. لوحة الرسوم البيانية التحليلية (company_report & analytics) ═══════════ --}}
                    @if(in_array($mode, ['company_report', 'analytics'], true))
                        <div class="sec-head">
                            <h3>📊 المؤشرات والرسوم البيانية التحليلية للفترة</h3>
                            <span>مبنية على عمليات التفويل الفعلية فقط (مستثنى منها نقاط العُهد بدون وقود)</span>
                        </div>

                        <div class="charts-grid-2">

                            {{-- الشارت 1: منحنى الاستهلاك اليومي باللتر (SVG Area/Line Chart) --}}
                            <div class="chart-card full-span">
                                <div class="chart-card-head">
                                    <span class="chart-card-title">📈 المسار الزمني لاستهلاك الوقود الفعلي (باللتر يومياً)</span>
                                    <span class="chart-card-sub">عدد أيام التفويل الفعلي: {{ $dailyFuelSeries->count() }} يوم · إجمالي: {{ number_format($summary->total_liters, 1) }} لتر</span>
                                </div>
                                <div class="svg-wrap">
                                    @if($dailyFuelSeries->isEmpty())
                                        <div style="text-align:center; padding:18px; color:#64748b;">لا توجد تفويلات وقود فعلية خلال هذه الفترة لرسم المنحنى (العمليات المسجلة عُهد نقدية فقط).</div>
                                    @else
                                        @php
                                            $svgW = 920;
                                            $svgH = 200;
                                            $padL = 52;
                                            $padR = 26;
                                            $padT = 24;
                                            $padB = 32;
                                            $plotW = $svgW - $padL - $padR;
                                            $plotH = $svgH - $padT - $padB;

                                            $ptsCount = $dailyFuelSeries->count();
                                            $maxLit   = max(10, (float) $dailyFuelSeries->max('liters') * 1.15);
                                            $avgDaily = round((float) $dailyFuelSeries->avg('liters'), 1);
                                            $avgY     = $padT + $plotH - (($avgDaily / $maxLit) * $plotH);

                                            $coords = [];
                                            foreach ($dailyFuelSeries as $idx => $dPt) {
                                                $x = $ptsCount === 1
                                                    ? ($padL + ($plotW / 2))
                                                    : ($padL + (($idx / ($ptsCount - 1)) * $plotW));
                                                $y = $padT + $plotH - (($dPt->liters / $maxLit) * $plotH);
                                                $coords[] = [
                                                    'x'      => round($x, 1),
                                                    'y'      => round($y, 1),
                                                    'liters' => $dPt->liters,
                                                    'label'  => $dPt->date_short,
                                                ];
                                            }

                                            $polyPoints = collect($coords)->map(fn($c) => "{$c['x']},{$c['y']}")->implode(' ');
                                            $firstX = $coords[0]['x'];
                                            $lastX  = $coords[count($coords) - 1]['x'];
                                            $baseY  = $padT + $plotH;
                                            $areaPath = "M {$firstX},{$baseY} L " . collect($coords)->map(fn($c) => "{$c['x']},{$c['y']}")->implode(' L ') . " L {$lastX},{$baseY} Z";

                                            $labelStep = max(1, (int) ceil($ptsCount / 14));
                                        @endphp
                                        <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" xmlns="http://www.w3.org/2000/svg" dir="ltr">
                                            <defs>
                                                <linearGradient id="fuelAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#0284c7" stop-opacity="0.24"/>
                                                    <stop offset="100%" stop-color="#0284c7" stop-opacity="0.02"/>
                                                </linearGradient>
                                            </defs>

                                            @for($g = 0; $g <= 4; $g++)
                                                @php
                                                    $gy = round($padT + (($g / 4) * $plotH), 1);
                                                    $gVal = round($maxLit * (1 - ($g / 4)));
                                                @endphp
                                                <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $svgW - $padR }}" y2="{{ $gy }}" stroke="#e2e8f0" stroke-width="1"/>
                                                <text x="{{ $padL - 6 }}" y="{{ $gy + 3.5 }}" text-anchor="end" font-size="9" font-weight="700" fill="#64748b">{{ number_format($gVal) }}</text>
                                            @endfor

                                            @if($ptsCount > 1)
                                                <line x1="{{ $padL }}" y1="{{ round($avgY, 1) }}" x2="{{ $svgW - $padR }}" y2="{{ round($avgY, 1) }}" stroke="#f59e0b" stroke-width="1.3" stroke-dasharray="5,4"/>
                                                <text x="{{ $svgW - $padR - 4 }}" y="{{ max(12, round($avgY - 4, 1)) }}" text-anchor="end" font-size="8.8" font-weight="800" fill="#b45309">متوسط اليوم: {{ number_format($avgDaily, 0) }} لتر</text>
                                                <path d="{{ $areaPath }}" fill="url(#fuelAreaGrad)"/>
                                                <polyline fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $polyPoints }}"/>
                                            @endif

                                            @foreach($coords as $idx => $pt)
                                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#0284c7" stroke="#ffffff" stroke-width="1.8"/>
                                                @if($idx % $labelStep === 0 || $idx === $ptsCount - 1)
                                                    <text x="{{ $pt['x'] }}" y="{{ max(12, $pt['y'] - 7) }}" text-anchor="middle" font-size="9" font-weight="800" fill="#0f172a">{{ number_format($pt['liters'], 0) }}</text>
                                                    <text x="{{ $pt['x'] }}" y="{{ $baseY + 14 }}" text-anchor="middle" font-size="9" font-weight="700" fill="#475569">{{ $pt['label'] }}</text>
                                                @endif
                                            @endforeach
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            {{-- الشارت 2: أعلى الشاحنات استهلاكاً للوقود (بأسماء كاملة واضحة + شريط Vector SVG) --}}
                            <div class="chart-card">
                                <div class="chart-card-head">
                                    <span class="chart-card-title">🚛 أعلى الشاحنات سحباً للسولار (لتر)</span>
                                    <span class="chart-card-sub">أعلى {{ $topVehiclesChart->count() }} شاحنات</span>
                                </div>
                                @if($topVehiclesChart->isEmpty())
                                    <div style="text-align:center; padding:18px; color:#64748b;">لا توجد مسحوبات وقود للشاحنات في هذه الفترة</div>
                                @else
                                    @php
                                        $barColors = ['#2563eb', '#0284c7', '#0d9488', '#ea580c', '#7c3aed', '#d97706', '#4f46e5', '#059669'];
                                        $maxVehLit = max(1, (float) $topVehiclesChart->max('total_liters'));
                                    @endphp
                                    <div class="hbar-list">
                                        @foreach($topVehiclesChart as $vIdx => $vRow)
                                            @php
                                                $bw200    = max(8, round(($vRow->total_liters / $maxVehLit) * 200, 1));
                                                $col      = $barColors[$vIdx % count($barColors)];
                                                $sharePct = $summary->total_liters > 0 ? round(($vRow->total_liters / $summary->total_liters) * 100, 1) : 0;
                                            @endphp
                                            <div class="hbar-row">
                                                <div class="hbar-name" title="{{ $vRow->vehicle_key }}">{{ $vIdx + 1 }}. {{ $vRow->vehicle_key }}</div>
                                                <div class="hbar-bar-cell">
                                                    <svg viewBox="0 0 200 14" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect x="0" y="0" width="200" height="14" rx="4" fill="#f1f5f9"/>
                                                        <rect x="{{ round(200 - $bw200, 1) }}" y="0" width="{{ $bw200 }}" height="14" rx="4" fill="{{ $col }}"/>
                                                    </svg>
                                                </div>
                                                <div class="hbar-metric" style="color:#1e293b;">{{ number_format($vRow->total_liters, 0) }} ل ({{ $sharePct }}%)</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- الشارت 3: مؤشر كفاءة استهلاك الشاحنات مقابل حد الرادار (بأسماء كاملة واضحة + شريط Vector SVG) --}}
                            <div class="chart-card">
                                <div class="chart-card-head">
                                    <span class="chart-card-title">⚡ كفاءة استهلاك الشاحنات (كم / لتر)</span>
                                    <span class="chart-card-sub">حد الأمان: {{ $effectiveMinEff }} كم/لتر</span>
                                </div>
                                @if($efficiencyChart->isEmpty())
                                    <div style="text-align:center; padding:18px; color:#64748b;">لا توجد رحلات مقفلة بقراءتي عداد خلال هذه الفترة لحساب الكفاءة</div>
                                @else
                                    @php
                                        $maxEffVal = max((float) $effectiveMinEff * 1.4, (float) $efficiencyChart->max('avg_efficiency') * 1.15, 6);
                                        $threshX200 = round(200 - (($effectiveMinEff / $maxEffVal) * 200), 1);
                                    @endphp
                                    <div class="hbar-list">
                                        @foreach($efficiencyChart as $eIdx => $eRow)
                                            @php
                                                $ebw200 = max(8, round(($eRow->avg_efficiency / $maxEffVal) * 200, 1));
                                                $isLow  = $eRow->avg_efficiency < $effectiveMinEff;
                                                $ecol   = $isLow ? '#ef4444' : '#10b981';
                                            @endphp
                                            <div class="hbar-row">
                                                <div class="hbar-name" style="color:{{ $isLow ? '#991b1b' : '#0f172a' }};" title="{{ $eRow->vehicle_key }}">{{ $eIdx + 1 }}. {{ $eRow->vehicle_key }}</div>
                                                <div class="hbar-bar-cell">
                                                    <svg viewBox="0 0 200 14" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect x="0" y="0" width="200" height="14" rx="4" fill="#f1f5f9"/>
                                                        <rect x="{{ round(200 - $ebw200, 1) }}" y="0" width="{{ $ebw200 }}" height="14" rx="4" fill="{{ $ecol }}"/>
                                                        <line x1="{{ $threshX200 }}" y1="0" x2="{{ $threshX200 }}" y2="14" stroke="#dc2626" stroke-width="2"/>
                                                    </svg>
                                                </div>
                                                <div class="hbar-metric" style="color:{{ $isLow ? '#dc2626' : '#047857' }};">{{ number_format($eRow->avg_efficiency, 2) }} كم/ل {{ $isLow ? '⚠️' : '' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- الشارت 4: هيكل المسحوبات المالية (وقود مقابل عهد) + أعلى المحطات --}}
                            <div class="chart-card full-span">
                                <div class="chart-card-head">
                                    <span class="chart-card-title">💰 هيكل المسحوبات المالية (وقود فعلي مقابل عُهد نقدية) وتوزيع المحطات</span>
                                    <span class="chart-card-sub">يفصل بدقة بين قيمة السولار والعُهد النقدية المنصرفة للسائقين</span>
                                </div>
                                @php
                                    $totFin = max(0.01, $summary->fuel_billing + $summary->total_advance);
                                    $fuelPct = round(($summary->fuel_billing / $totFin) * 100, 1);
                                    $advPct  = round(100 - $fuelPct, 1);
                                    $circ = 2 * M_PI * 38;
                                    $fuelDash = round(($fuelPct / 100) * $circ, 2);
                                @endphp
                                <div style="display:grid; grid-template-columns: 270px 1fr; gap:14px; align-items:center;">
                                    <div style="display:flex; align-items:center; gap:10px; border-left:1px solid #e2e8f0; padding-left:10px;">
                                        <svg width="96" height="96" viewBox="0 0 100 100" style="flex-shrink:0; direction:ltr;">
                                            <circle cx="50" cy="50" r="38" fill="none" stroke="#fed7aa" stroke-width="14"/>
                                            <circle cx="50" cy="50" r="38" fill="none" stroke="#ea580c" stroke-width="14"
                                                    stroke-dasharray="{{ $circ }}" stroke-dashoffset="0" transform="rotate(-90 50 50)"/>
                                            <circle cx="50" cy="50" r="38" fill="none" stroke="#0284c7" stroke-width="14"
                                                    stroke-dasharray="{{ $fuelDash }} {{ $circ }}" stroke-dashoffset="0" transform="rotate(-90 50 50)"/>
                                            <text x="50" y="48" text-anchor="middle" font-size="11" font-weight="800" fill="#0f172a">{{ $fuelPct }}%</text>
                                            <text x="50" y="60" text-anchor="middle" font-size="8.5" font-weight="700" fill="#64748b">وقود</text>
                                        </svg>
                                        <div style="font-size:9.8px;">
                                            <div style="margin-bottom:6px;">
                                                <span style="display:inline-block; width:9px; height:9px; border-radius:2px; background:#0284c7; margin-left:4px;"></span>
                                                <strong>مسحوبات الوقود ({{ $fuelPct }}%):</strong>
                                                <div class="fw-800 c-blue">{{ number_format($summary->fuel_billing, 2) }} ج</div>
                                            </div>
                                            <div>
                                                <span style="display:inline-block; width:9px; height:9px; border-radius:2px; background:#ea580c; margin-left:4px;"></span>
                                                <strong>العُهد النقدية ({{ $advPct }}%):</strong>
                                                <div class="fw-800 c-orange">{{ number_format($summary->total_advance, 2) }} ج</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div style="font-weight:800; font-size:10px; margin-bottom:5px; color:#334155;">⛽ توزيع السحب حسب محطات الوقود المعتمدة:</div>
                                        @foreach($stationSummary->take(4) as $stItem)
                                            @php
                                                $stFinPct = $summary->total_on_company > 0 ? round(($stItem->total_cost / $summary->total_on_company) * 100, 1) : 0;
                                            @endphp
                                            <div style="margin-bottom:5px;">
                                                <div style="display:flex; justify-content:space-between; font-size:9.5px; font-weight:700; margin-bottom:2px;">
                                                    <span>{{ $stItem->station_name }} ({{ $stItem->fuel_ops }} تفويلة{{ $stItem->custody_ops > 0 ? ' + ' . $stItem->custody_ops . ' عهدة' : '' }})</span>
                                                    <span>{{ number_format($stItem->liters, 1) }} لتر · {{ number_format($stItem->total_cost, 0) }} ج ({{ $stFinPct }}%)</span>
                                                </div>
                                                <div style="height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden;">
                                                    <div style="width:{{ max(3, $stFinPct) }}%; height:100%; background:#0284c7; border-radius:99px;"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ═══════════ 5. جدول أداء وكفاءة الشاحنات التفصيلي (مضبوط 100% داخل عرض الورقة) ═══════════ --}}
                        <div class="sec-head">
                            <h3>🚛 بيان مسحوبات وكفاءة الشاحنات والحسابات خلال الفترة ({{ $fleetTable->count() }} بيان)</h3>
                            <span>التصنيف يفصل بدقة بين التفويل (كامل / جزئي) وعمليات (العهدة) المستقلة بدون وقود</span>
                        </div>

                        <table class="tbl tbl-dense">
                            <colgroup>
                                <col style="width:3.5%;">
                                <col style="width:10.5%;">
                                <col style="width:{{ $showProfit ? '12%' : '15%' }};">
                                <col style="width:11%;">
                                <col style="width:7.5%;">
                                <col style="width:7%;">
                                <col style="width:8.5%;">
                                <col style="width:8%;">
                                <col style="width:9%;">
                                @if($showProfit)
                                    <col style="width:6.5%;">
                                @endif
                                <col style="width:6.5%;">
                                <col style="width:5.5%;">
                                <col style="width:{{ $showProfit ? '4.5%' : '8%' }};">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الشاحنة / الحساب</th>
                                    <th>السائقون</th>
                                    <th>تصنيف العمليات</th>
                                    <th>المسافة</th>
                                    <th>الوقود (لتر)</th>
                                    <th>حساب الوقود</th>
                                    <th>العُهد (ج)</th>
                                    <th>الإجمالي المستحق</th>
                                    @if($showProfit)
                                        <th>الربح</th>
                                    @endif
                                    <th>الكفاءة</th>
                                    <th>ج/كم</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fleetTable as $idx => $fRow)
                                    <tr class="{{ $fRow->is_suspicious ? 'row-alert' : '' }}">
                                        <td class="fw-bold c-muted">{{ $idx + 1 }}</td>
                                        <td class="text-right fw-800">{{ $fRow->vehicle_key }}</td>
                                        <td class="text-right">{{ $fRow->drivers_list }}</td>
                                        <td>
                                            @if($fRow->full_fills > 0)
                                                <span class="b-full">{{ $fRow->full_fills }} كامل</span>
                                            @endif
                                            @if($fRow->partial_fills > 0)
                                                <span class="b-partial">{{ $fRow->partial_fills }} جزئي</span>
                                            @endif
                                            @if($fRow->custody_fills > 0)
                                                <span class="b-custody">{{ $fRow->custody_fills }} عهدة</span>
                                            @endif
                                            @if($fRow->full_fills == 0 && $fRow->partial_fills == 0 && $fRow->custody_fills == 0)
                                                <span class="c-muted">{{ $fRow->total_ops }} حركة</span>
                                            @endif
                                        </td>
                                        <td dir="ltr" class="fw-bold">{{ $fRow->total_distance > 0 ? number_format($fRow->total_distance) : '—' }}</td>
                                        <td class="fw-bold c-amber">{{ $fRow->total_liters > 0 ? number_format($fRow->total_liters, 1) : '—' }}</td>
                                        <td class="fw-bold">{{ $fRow->fuel_billing > 0 ? number_format($fRow->fuel_billing, 2) : '—' }}</td>
                                        <td class="fw-bold {{ $fRow->total_advance > 0 ? 'c-orange' : 'c-muted' }}">{{ $fRow->total_advance > 0 ? number_format($fRow->total_advance, 0) : '—' }}</td>
                                        <td class="fw-800">{{ number_format($fRow->total_cost, 2) }}</td>
                                        @if($showProfit)
                                            <td class="fw-bold c-green">+{{ number_format($fRow->total_profit, 2) }}</td>
                                        @endif
                                        <td dir="ltr" class="fw-bold {{ $fRow->avg_efficiency > 0 && $fRow->avg_efficiency < $effectiveMinEff ? 'c-red' : 'c-green' }}">
                                            {{ $fRow->avg_efficiency > 0 ? number_format($fRow->avg_efficiency, 2) : '—' }}
                                        </td>
                                        <td dir="ltr" class="fw-bold {{ $fRow->avg_cost_km > $effectiveMaxCostKm ? 'c-red' : '' }}">
                                            {{ $fRow->avg_cost_km > 0 ? number_format($fRow->avg_cost_km, 2) : '—' }}
                                        </td>
                                        <td>
                                            @if($fRow->total_liters <= 0)
                                                <span class="b-custody">عهدة</span>
                                            @elseif($fRow->is_suspicious)
                                                <span class="b-alert">مراجعة ⚠️</span>
                                            @else
                                                <span class="b-ok">طبيعي ✓</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right">الإجمالي العام للشاحنات خلال الفترة</td>
                                    <td dir="ltr">{{ number_format($summary->total_distance) }} كم</td>
                                    <td class="c-amber">{{ number_format($summary->total_liters, 1) }}</td>
                                    <td>{{ number_format($summary->fuel_billing, 2) }}</td>
                                    <td class="c-orange">{{ number_format($summary->total_advance, 0) }}</td>
                                    <td>{{ number_format($summary->total_on_company, 2) }}</td>
                                    @if($showProfit)
                                        <td class="c-green">+{{ number_format($summary->total_profit, 2) }}</td>
                                    @endif
                                    <td dir="ltr">{{ $summary->avg_efficiency > 0 ? number_format($summary->avg_efficiency, 2) : '—' }}</td>
                                    <td dir="ltr">{{ $summary->avg_cost_km > 0 ? number_format($summary->avg_cost_km, 2) : '—' }}</td>
                                    <td>—</td>
                                </tr>
                            </tfoot>
                        </table>

                        {{-- ═══════════ 6. جدول ملخص شركات النقل ═══════════ --}}
                        @if($selectedCompanyId === '' && $companySummary->count() > 1)
                            <div class="sec-head">
                                <h3>🏢 ملخص مسحوبات شركات النقل المتعاقدة</h3>
                                <span>{{ $companySummary->count() }} شركات</span>
                            </div>
                            <table class="tbl">
                                <thead>
                                    <tr>
                                        <th style="width:5%;">#</th>
                                        <th style="width:23%;">شركة النقل</th>
                                        <th style="width:11%;">تفويلات الوقود</th>
                                        <th style="width:11%;">عمليات العُهد</th>
                                        <th style="width:13%;">إجمالي اللترات</th>
                                        <th style="width:12%;">المسافات (كم)</th>
                                        <th style="width:12%;">العُهد النقدية (ج)</th>
                                        <th style="width:13%;">إجمالي المستحق (ج)</th>
                                        @if($showProfit)<th style="width:10%;">صافي الربح</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companySummary as $cIdx => $cRow)
                                        <tr>
                                            <td>{{ $cIdx + 1 }}</td>
                                            <td class="text-right fw-800">{{ $cRow->company_name }}</td>
                                            <td>{{ $cRow->fuel_ops }}</td>
                                            <td>{{ $cRow->custody_ops }}</td>
                                            <td class="fw-bold c-amber">{{ number_format($cRow->total_liters, 1) }} لتر</td>
                                            <td dir="ltr">{{ $cRow->total_distance > 0 ? number_format($cRow->total_distance) . ' كم' : '—' }}</td>
                                            <td class="c-orange fw-bold">{{ $cRow->total_advance > 0 ? number_format($cRow->total_advance, 2) : '—' }}</td>
                                            <td class="fw-800">{{ number_format($cRow->total_cost, 2) }} ج</td>
                                            @if($showProfit)<td class="c-green fw-bold">+{{ number_format($cRow->total_profit, 2) }} ج</td>@endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        {{-- ═══════════ 7. ملاحظات رادار الكفاءة ومراجعة الاستهلاك ═══════════ --}}
                        @if(count($radarAlerts) > 0)
                            <div class="sec-head" style="border-right-color:#dc2626; background:#fef2f2;">
                                <h3 style="color:#991b1b;">🚨 ملاحظات مراجعة الكفاءة والاستهلاك خلال الفترة ({{ count($radarAlerts) }} رحلة)</h3>
                                <span style="color:#b91c1c;">رحلات سجلت كفاءة أقل من ({{ $effectiveMinEff }} كم/لتر) أو تكلفة أعلى من ({{ $effectiveMaxCostKm }} ج/كم)</span>
                            </div>
                            <table class="tbl tbl-dense">
                                <colgroup>
                                    <col style="width:6%;">
                                    <col style="width:10%;">
                                    <col style="width:8%;">
                                    <col style="width:12%;">
                                    <col style="width:8%;">
                                    <col style="width:14%;">
                                    <col style="width:8%;">
                                    <col style="width:8%;">
                                    <col style="width:8%;">
                                    <col style="width:18%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>الحركة</th>
                                        <th>التاريخ</th>
                                        <th>الشاحنة</th>
                                        <th>السائق</th>
                                        <th>المحطة</th>
                                        <th>خط السير</th>
                                        <th>المسافة</th>
                                        <th>الوقود</th>
                                        <th>الكفاءة</th>
                                        <th>سبب الملاحظة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($radarAlerts, 0, 15) as $al)
                                        <tr class="row-alert">
                                            <td class="fw-bold">#{{ $al->id }}</td>
                                            <td dir="ltr">{{ $al->date }}</td>
                                            <td class="fw-800">{{ $al->vehicle_title }}</td>
                                            <td>{{ $al->driver_name }}</td>
                                            <td>{{ $al->station_name }}</td>
                                            <td class="text-right">{{ $al->route }}</td>
                                            <td dir="ltr">{{ $al->distance_km > 0 ? number_format($al->distance_km) . ' كم' : '—' }}</td>
                                            <td>{{ number_format($al->liters, 1) }} ل</td>
                                            <td dir="ltr" class="fw-800">{{ $al->consumption_rate > 0 ? number_format($al->consumption_rate, 2) : '—' }}</td>
                                            <td class="text-right fw-bold">{{ $al->issue_label }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endif

                    {{-- ═══════════ 8. جدول سجل العمليات التفصيلي (مضبوط 100% بعرض الصفحة بالكامل بدون أي قص للأعمدة) ═══════════ --}}
                    @php
                        $hasCompCol = ($selectedCompanyId === '');
                        // توزيع دقيق بنسب مئوية مجموعها 100% تماماً لضمان ظهور جميع الأعمدة كاملة في الطباعة الطولية والعرضية
                        $notesColPct = 100
                            - 4.5  // #
                            - 7.5  // التاريخ
                            - ($hasCompCol ? 7.5 : 0) // الشركة
                            - 5.5  // المحطة
                            - 6.0  // الشاحنة
                            - 10.5 // السائق / الحساب
                            - 6.0  // النوع
                            - 5.5  // اللترات
                            - 6.5  // العداد
                            - 5.5  // المسافة
                            - 5.5  // الكفاءة
                            - 6.5  // العهدة
                            - 7.5  // الإجمالي
                            - ($showProfit ? 5.0 : 0); // الربح
                    @endphp
                    <div id="detailedLedgerSection" style="{{ $mode === 'comparisons' ? 'display:none;' : '' }}">
                        <div class="sec-head">
                            <h3>📋 كشف سجل العمليات التفصيلي ({{ $reportTransactions->count() }} حركة)</h3>
                            <span>يشمل كافة عمليات التفويل والعُهد النقدية خلال الفترة المحددة</span>
                        </div>

                        <table class="tbl tbl-dense">
                            <colgroup>
                                <col style="width:4.5%;">
                                <col style="width:7.5%;">
                                @if($hasCompCol)
                                    <col style="width:7.5%;">
                                @endif
                                <col style="width:5.5%;">
                                <col style="width:6%;">
                                <col style="width:10.5%;">
                                <col style="width:6%;">
                                <col style="width:5.5%;">
                                <col style="width:6.5%;">
                                <col style="width:5.5%;">
                                <col style="width:5.5%;">
                                <col style="width:6.5%;">
                                <col style="width:7.5%;">
                                @if($showProfit)
                                    <col style="width:5%;">
                                @endif
                                <col style="width:{{ $notesColPct }}%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ</th>
                                    @if($hasCompCol)
                                        <th>الشركة</th>
                                    @endif
                                    <th>المحطة</th>
                                    <th>الشاحنة</th>
                                    <th>السائق / الحساب</th>
                                    <th>النوع</th>
                                    <th>اللترات</th>
                                    <th>العداد</th>
                                    <th>المسافة</th>
                                    <th>الكفاءة</th>
                                    <th>العهدة (ج)</th>
                                    <th>الإجمالي (ج)</th>
                                    @if($showProfit)
                                        <th>الربح</th>
                                    @endif
                                    <th>خط السير / البيان</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportTransactions as $rIdx => $tx)
                                    @php
                                        $isCustodyOnly = floatval($tx->liters ?? 0) <= 0;
                                        $isFullTank    = !$isCustodyOnly && (int) ($tx->is_full_tank ?? 1) === 1;
                                        $isAlertRow    = isset($radarAlertIds[$tx->id]);
                                        $carLabel      = ($tx->car_number && $tx->car_number !== '-') ? $tx->car_number : '—';
                                        $dtParsed      = \Carbon\Carbon::parse($tx->created_at);
                                    @endphp
                                    <tr class="{{ $isAlertRow ? 'row-alert' : '' }}">
                                        <td class="fw-bold">#{{ $tx->id }}</td>
                                        <td dir="ltr">
                                            <div class="fw-bold">{{ $dtParsed->format('Y-m-d') }}</div>
                                            <div class="c-muted" style="font-size:7.6px;">{{ $dtParsed->format('H:i') }}</div>
                                        </td>
                                        @if($hasCompCol)
                                            <td class="fw-bold">{{ $tx->company_name }}</td>
                                        @endif
                                        <td>{{ $tx->station_name }}</td>
                                        <td class="fw-800">{{ $carLabel }}</td>
                                        <td class="text-right fw-bold">{{ $tx->driver_name ?: '—' }}</td>
                                        <td>
                                            @if($isCustodyOnly)
                                                <span class="b-custody">عهدة</span>
                                            @elseif($isFullTank)
                                                <span class="b-full">كامل</span>
                                            @else
                                                <span class="b-partial">جزئي</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold {{ $isCustodyOnly ? 'c-muted' : 'c-amber' }}">
                                            {{ $isCustodyOnly ? '—' : number_format($tx->liters, 1) }}
                                        </td>
                                        <td dir="ltr">{{ (!$isCustodyOnly && $tx->odometer) ? number_format($tx->odometer) : '—' }}</td>
                                        <td dir="ltr" class="fw-bold">
                                            @if(!$isCustodyOnly && $tx->distance_km > 0)
                                                +{{ number_format($tx->distance_km) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td dir="ltr" class="fw-bold {{ $tx->consumption_rate > 0 && $tx->consumption_rate < $effectiveMinEff ? 'c-red' : 'c-green' }}">
                                            @if(!$isCustodyOnly && $tx->consumption_rate > 0)
                                                {{ number_format($tx->consumption_rate, 2) }}
                                            @else
                                                <span class="c-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold {{ $tx->cash_advance > 0 ? 'c-orange' : 'c-muted' }}">
                                            {{ $tx->cash_advance > 0 ? number_format($tx->cash_advance, 2) : '—' }}
                                        </td>
                                        <td class="fw-800">{{ number_format($tx->total_on_company, 2) }}</td>
                                        @if($showProfit)
                                            <td class="fw-bold c-green">+{{ number_format($tx->ahmed_profit, 2) }}</td>
                                        @endif
                                        <td class="text-right">{{ $tx->notes ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $hasCompCol ? 7 : 6 }}" class="text-right">إجمالي كشف السجل ({{ $reportTransactions->count() }} عملية)</td>
                                    <td class="c-amber">{{ number_format($summary->total_liters, 1) }} ل</td>
                                    <td>—</td>
                                    <td dir="ltr">{{ number_format($summary->total_distance) }} كم</td>
                                    <td dir="ltr">{{ $summary->avg_efficiency > 0 ? number_format($summary->avg_efficiency, 2) : '—' }}</td>
                                    <td class="c-orange">{{ number_format($summary->total_advance, 2) }}</td>
                                    <td>{{ number_format($summary->total_on_company, 2) }}</td>
                                    @if($showProfit)
                                        <td class="c-green">+{{ number_format($summary->total_profit, 2) }}</td>
                                    @endif
                                    <td>—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                @endif

                {{-- 9. التوقيعات والاعتماد الرسمي --}}
                <div class="signatures-box">
                    <div class="sig-grid">
                        <div class="sig-item">
                            <div class="t">إعداد ومراجعة حسابات الوقود (شركة الضبع)</div>
                            <div class="l"></div>
                            <div class="s">الاسم والتوقيع: .....................................</div>
                        </div>
                        <div class="sig-item">
                            <div class="t">مراجعة إدارة الحركة والتشغيل</div>
                            <div class="l"></div>
                            <div class="s">الاسم والتوقيع: .....................................</div>
                        </div>
                        <div class="sig-item">
                            <div class="t">استلام ومصادقة ({{ $selectedCompany ? $companyTitle : 'شركة النقل' }})</div>
                            <div class="l"></div>
                            <div class="s">التوقيع والخاتم: .....................................</div>
                        </div>
                    </div>
                </div>

                <div class="doc-footer">
                    <span>صادر آلياً عن منظومة شركة الضبع لإدارة ومحاسبة وقود الشاحنات — كود: {{ $reportCode }}</span>
                    <span>الفترة: {{ $periodLabel }} | تاريخ الطباعة: {{ now()->format('Y-m-d h:i A') }}</span>
                </div>

            </td></tr></tbody>
            <tfoot><tr><td class="print-margin-spacer"></td></tr></tfoot>
        </table>
    </div>

    <script>
        let isLandscape = {{ $defaultLandscape ? 'true' : 'false' }};
        let isLedgerHidden = {{ $mode === 'comparisons' ? 'true' : 'false' }};

        function switchReportMode(newMode) {
            document.getElementById('inp_mode').value = newMode;
            document.getElementById('pdfFilterForm').submit();
        }

        function onPdfCompareTypeChange() {
            const selA = document.getElementById('inp_vehicle_a');
            const selB = document.getElementById('inp_vehicle_b');
            if (selA) selA.value = '';
            if (selB) selB.value = '';
            document.getElementById('pdfFilterForm').submit();
        }

        function setPdfPeriod(p) {
            document.getElementById('inp_period').value = p;
            if (p !== 'custom') {
                document.getElementById('inp_from').value = '';
                document.getElementById('inp_to').value = '';
            }
            document.getElementById('pdfFilterForm').submit();
        }

        function togglePageOrientation() {
            isLandscape = !isLandscape;
            const styleEl = document.getElementById('pageOrientationStyle');
            const btn = document.getElementById('btnOrientation');
            if (isLandscape) {
                styleEl.innerHTML = '@page { size: A4 landscape; margin: 0mm !important; }';
                document.querySelector('.report-sheet').style.maxWidth = '1320px';
                if (btn) btn.innerHTML = '<i class="fa fa-file-lines"></i> <span>اتجاه الورقة: عرضي (A4 Landscape)</span>';
            } else {
                styleEl.innerHTML = '@page { size: A4 portrait; margin: 0mm !important; }';
                document.querySelector('.report-sheet').style.maxWidth = '1060px';
                if (btn) btn.innerHTML = '<i class="fa fa-file-lines"></i> <span>اتجاه الورقة: طولي (A4)</span>';
            }
        }

        function toggleLedgerSection() {
            const sec = document.getElementById('detailedLedgerSection');
            const btn = document.getElementById('btnToggleLedger');
            if (!sec) return;
            isLedgerHidden = !isLedgerHidden;
            sec.style.display = isLedgerHidden ? 'none' : 'block';
            if (btn) {
                btn.innerHTML = isLedgerHidden
                    ? '<i class="fa fa-list-check"></i> <span>إظهار جدول السجل المرفق</span>'
                    : '<i class="fa fa-list-check"></i> <span>إخفاء جدول السجل المرفق</span>';
            }
        }

        @if(request()->boolean('autoprint'))
            window.addEventListener('load', function() {
                setTimeout(function() { window.print(); }, 350);
            });
        @endif
    </script>
</body>
</html>
