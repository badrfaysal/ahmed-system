<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملف الشريك: {{ $partner->name }} - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        :root {
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;
            --brand-primary: #1e40af;
            --brand-success: #047857;
            --brand-danger:  #b91c1c;
            --brand-amber:   #b45309;
        }
        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--slate-50);
            color: var(--slate-900);
            font-size: 0.92rem;
        }
        .main-content {
            padding: 24px 20px;
        }
        .card-formal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .kpi-formal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            height: 100%;
            border-top: 3px solid var(--slate-700);
        }
        .kpi-formal.accent-primary { border-top-color: var(--brand-primary); }
        .kpi-formal.accent-amber   { border-top-color: var(--brand-amber); }
        .kpi-formal.accent-success { border-top-color: var(--brand-success); }
        .kpi-formal.accent-slate   { border-top-color: var(--slate-700); }

        .kpi-formal .title {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--slate-600);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .kpi-formal .val {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--slate-900);
            font-variant-numeric: tabular-nums;
        }
        .kpi-formal .sub {
            font-size: 0.74rem;
            color: var(--slate-600);
            margin-top: 6px;
        }
        .chart-formal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            margin-bottom: 24px;
        }
        .chart-header {
            font-weight: 800;
            font-size: 0.92rem;
            color: var(--slate-900);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--slate-100);
            padding-bottom: 10px;
        }
        .table-responsive {
            overflow-x: visible !important;
        }
        .table-formal {
            margin: 0;
            width: 100% !important;
            vertical-align: middle;
            font-size: 0.84rem;
        }
        .table-formal thead th {
            background: var(--slate-800);
            color: #ffffff;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 10px 8px;
            border: none;
            white-space: normal;
        }
        .table-formal tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--slate-200);
            font-variant-numeric: tabular-nums;
        }
        .partners-tabs-bar {
            background: #0f172a;
            padding: 12px 16px;
            border-radius: 8px 8px 0 0;
            border-bottom: 3px solid #cbd5e1;
        }
        .partners-tabs-bar .nav-tabs {
            border-bottom: none;
            gap: 10px;
        }
        .partners-tabs-bar .nav-link {
            border-radius: 6px !important;
            padding: 10px 16px;
            font-weight: 800;
            font-size: 0.86rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            border: 2px solid transparent !important;
        }
        .partners-tabs-bar .nav-link.tab-btn-periods {
            background-color: #064e3b;
            color: #a7f3d0;
            border-color: #10b981 !important;
        }
        .partners-tabs-bar .nav-link.tab-btn-periods.active {
            background-color: #059669 !important;
            color: #ffffff !important;
            border-color: #a7f3d0 !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.45);
        }
        .partners-tabs-bar .nav-link.tab-btn-history {
            background-color: #451a03;
            color: #fde68a;
            border-color: #f59e0b !important;
        }
        .partners-tabs-bar .nav-link.tab-btn-history.active {
            background-color: #d97706 !important;
            color: #ffffff !important;
            border-color: #fde68a !important;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.45);
        }
        .partners-tabs-bar .nav-link.tab-btn-movements {
            background-color: #1e293b;
            color: #93c5fd;
            border-color: #3b82f6 !important;
        }
        .partners-tabs-bar .nav-link.tab-btn-movements.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #93c5fd !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.45);
        }
        body.theme-exited {
            background-color: #fef2f2 !important;
        }
        body.theme-exited .card-formal,
        body.theme-exited .chart-formal,
        body.theme-exited .kpi-formal {
            background-color: #fffafa;
            border-color: #fecaca;
        }
        body.theme-exited .kpi-formal {
            border-top-color: #dc2626 !important;
        }
        .exited-banner-formal {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }

        .print-only { display: none; }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 7mm 12mm 7mm;
            }
            *, *::before, *::after {
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
                color: #000000 !important;
                font-family: 'Cairo', 'IBM Plex Sans Arabic', sans-serif !important;
                font-size: 7.5pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, th.no-print, td.no-print, button, .btn, .nav-tabs, .partners-tabs-bar, .exited-banner-formal, .card-footer, .pagination, .modal, #btnGroupTimeframe, .export-branding-header {
                display: none !important;
            }
            #partnerProfileExportArea {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 0 10px 0 !important;
                background: transparent !important;
            }
            #partnerProfileExportArea .row.g-3 > .col-md-3 {
                flex: 0 0 25% !important;
                max-width: 25% !important;
                padding: 3px !important;
            }
            #partnerProfileExportArea .kpi-formal {
                padding: 8px 6px !important;
            }
            #partnerProfileExportArea .kpi-formal .title {
                font-size: 6.8pt !important;
            }
            #partnerProfileExportArea .kpi-formal .val {
                font-size: 11pt !important;
            }
            #partnerProfileExportArea .kpi-formal .sub {
                font-size: 6.2pt !important;
            }
            #partnerProfileExportArea .row.g-2 > .col-md-4 {
                flex: 0 0 33.333% !important;
                max-width: 33.333% !important;
                padding: 3px !important;
            }
            .chart-formal {
                display: block !important;
                page-break-inside: avoid !important;
                border: 1px solid #cbd5e1 !important;
                margin-bottom: 12px !important;
                box-shadow: none !important;
                background: #ffffff !important;
                padding: 8px !important;
            }
            .chart-formal canvas {
                max-height: 230px !important;
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
                border: 1px solid #94a3b8 !important;
                border-bottom: 2px solid #0f172a !important;
                padding: 6px 10px !important;
                margin-bottom: 6px !important;
                border-radius: 4px !important;
            }
            .card-header-formal h5 {
                font-size: 9pt !important;
                font-weight: bold !important;
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
                min-width: 100% !important;
                max-width: 100% !important;
                border-collapse: collapse !important;
                font-size: 7pt !important;
                table-layout: auto !important;
                margin-bottom: 8px !important;
            }
            .table-formal th, .table-formal td {
                width: auto !important;
            }
            .table-formal thead {
                display: table-header-group !important;
            }
            .table-formal tbody {
                display: table-row-group !important;
            }
            .table-formal thead th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
                padding: 4px 2px !important;
                font-size: 6.8pt !important;
                font-weight: 700 !important;
                text-align: center !important;
                white-space: normal !important;
                line-height: 1.15 !important;
            }
            .table-formal tbody td {
                border: 1px solid #94a3b8 !important;
                padding: 3px 2px !important;
                font-size: 7pt !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .table-formal tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            .print-sig-col {
                border: 1px dashed #64748b !important;
                padding: 10px 8px !important;
                text-align: center !important;
                background: #ffffff !important;
                border-radius: 4px !important;
            }
        }
    </style>
</head>
<body class="{{ $partner->status === 'exited' ? 'theme-exited' : '' }}">

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة المطبوعة الرسمية --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">سجل الشركاء والمساهمات الرأسمالية</small>
                    <span class="badge bg-dark text-white mt-1">كود الشريك: PRT-{{ str_pad($partner->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">كشف الحساب الاستثماري وحركة النسب والأرباح</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">الشريك: {{ $partner->name }}</div>
                    <small class="text-muted">الهاتف: {{ $partner->phone ?: '—' }} · تاريخ الانضمام: {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '—' }}</small>
                </div>
                <div class="text-start">
                    <div class="badge {{ $partner->status === 'active' ? 'bg-success' : 'bg-danger' }} text-white px-2 py-1 mb-1">
                        {{ $partner->status === 'active' ? 'شريك نشط ومستمر' : 'حساب مصفى ومتخارج' }}
                    </div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- الترويسة والتحكم للشاشة --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة إلى سجل الشركاء
                </a>
                <h4 class="fw-bold m-0 d-flex align-items-center gap-2 text-dark">
                    <i class="fa fa-user-tie text-primary"></i> ملف الشريك: {{ $partner->name }}
                    @if($partner->status === 'exited')
                        <span class="badge bg-danger ms-2" style="font-size:0.75rem;">متخارج</span>
                    @else
                        <span class="badge bg-success ms-2" style="font-size:0.75rem;">نشط ومستمر</span>
                    @endif
                </h4>
                <small class="text-muted">
                    <i class="fa fa-phone me-1"></i>{{ $partner->phone ?: 'بدون هاتف مسجل' }} · تاريخ الانضمام: {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '—' }}
                </small>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-success btn-sm fw-bold px-3 py-2 shadow-sm" onclick="openWhatsAppModal()">
                    <i class="fa-brands fa-whatsapp fs-6 me-1"></i> إرسال عبر واتساب (صورة / PDF)
                </button>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2 shadow-sm" onclick="exportProfileAsImage()" id="btnExportImage" title="تحميل التقرير والشارتات كصورة PNG">
                        <i class="fa fa-image me-1"></i> حفظ كصورة
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3 py-2 shadow-sm" onclick="exportProfileAsPDF()" id="btnExportPDF" title="تحميل التقرير والشارتات كملف PDF">
                        <i class="fa fa-file-pdf me-1"></i> حفظ PDF
                    </button>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm fw-bold px-3 py-2 shadow-sm" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> طباعة كشف الحساب
                </button>
                @if($partner->status === 'active')
                <button type="button" class="btn btn-success btn-sm fw-bold px-3 py-2 shadow-sm" onclick="openAddCapitalModal()">
                    <i class="fa fa-plus me-1"></i> زيادة رأس المال
                </button>
                <button type="button" class="btn btn-warning btn-sm fw-bold px-3 py-2 shadow-sm text-dark" onclick="openWithdrawCapitalModal()">
                    <i class="fa fa-minus me-1"></i> سحب من رأس المال
                </button>
                @endif
                <button type="button" class="btn btn-dark btn-sm fw-bold px-3 py-2 shadow-sm" onclick="openEditPartnerModal()">
                    <i class="fa fa-pen me-1"></i> تعديل النسبة والبيانات
                </button>
            </div>
        </div>

        @if($partner->status === 'exited')
        <div class="exited-banner-formal d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <i class="fa fa-exclamation-triangle me-2 text-danger"></i>
                <strong>حساب شريك متخارج رسمياً:</strong> تمت تصفية كامل رأس المال وصرف المستحقات من الخزينة. تم إيقاف هذا الحساب عن الدورات الشهرية مع الاحتفاظ بالأرشيف التاريخي.
            </div>
            <span class="badge bg-danger px-3 py-2">متخارج / مصفى</span>
        </div>
        @endif

        {{-- منطقة كشف الحساب والشارتات القابلة للمشاركة والتصدير كصورة أو PDF --}}
        <div id="partnerProfileExportArea" class="bg-white p-3 rounded-3 border mb-4">
            <div class="p-3 mb-3 bg-light rounded-3 border d-flex justify-content-between align-items-center flex-wrap gap-2 export-branding-header">
                <div>
                    <h5 class="fw-black text-dark m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-building text-primary"></i> شركة الضبع للتجارة والتوزيع
                    </h5>
                    <div class="fw-bold fs-6 text-primary mt-1">كشف حساب الشريك: {{ $partner->name }}</div>
                    <small class="text-muted">
                        <i class="fa fa-phone me-1"></i>{{ $partner->phone ?: 'بدون هاتف مسجل' }} · تاريخ الانضمام: {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '—' }}
                    </small>
                </div>
                <div class="text-start">
                    <span class="badge {{ $partner->status === 'active' ? 'bg-success' : 'bg-danger' }} fs-6 px-3 py-2 mb-1">
                        {{ $partner->status === 'active' ? 'شريك نشط ومستمر' : 'حساب مصفى / متخارج' }}
                    </span>
                    <div class="small text-muted font-monospace" style="font-size:0.75rem;">تاريخ التقرير: {{ date('Y-m-d H:i') }}</div>
                </div>
            </div>

            {{-- كروت المؤشرات الفردية --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="kpi-formal accent-primary">
                        <div class="title"><i class="fa fa-coins text-primary"></i> الرصيد المعتمد بالدورة</div>
                        <div class="val text-dark">{{ number_format($partner->capital, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                        <div class="sub">
                            رأس المال التأسيسي: {{ number_format($partner->initial_capital, 2) }} ج
                            @if($partner->capital > $partner->initial_capital)
                                <span class="text-success fw-bold ms-1">(+{{ number_format($partner->capital - $partner->initial_capital) }} ج)</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi-formal accent-amber">
                        <div class="title"><i class="fa fa-percent text-warning"></i> نسبة المساهمة المعتمدة</div>
                        <div class="val text-dark">{{ number_format($capitalSharePct, 2) }}%</div>
                        <div class="sub">
                            نسبة الإدارة من الربح: <strong>{{ number_format($partner->ahmed_ratio, 1) }}%</strong>
                            (صافي حصته: <strong>{{ number_format($effectiveProfitPct, 2) }}%</strong>)
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi-formal accent-success">
                        <div class="title"><i class="fa fa-chart-line text-success"></i> إجمالي صافي الأرباح المحققة</div>
                        <div class="val text-success">{{ number_format($totalNetEarned, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                        <div class="sub">
                            عن {{ $lines->total() }} دورة إقفال شهرية معتمدة
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="kpi-formal accent-slate">
                        <div class="title"><i class="fa fa-arrows-split-up-and-left text-secondary"></i> تدوير الأرباح مقابل السحب</div>
                        <div class="val text-dark">{{ number_format($totalReinvested, 2) }} <span class="fs-6 fw-normal text-muted">ج مدوّر</span></div>
                        <div class="sub">
                            إجمالي المسحوب نقداً: <strong class="text-danger">{{ number_format($totalWithdrawn, 2) }} ج</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- شارت محاكي توقعات الأرباح المستقبلية للشريك --}}
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="chart-formal" style="border: 2px solid #e2e8f0; border-radius: 10px;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 border-bottom mb-3">
                            <div>
                                <h5 class="fw-black text-dark m-0 d-flex align-items-center gap-2">
                                    <i class="fa fa-chart-line text-success"></i> توقعات أرباح الشريك ونمو المحفظة (مقارنة سيناريوهات التدوير والسحب)
                                </h5>
                                <small class="text-muted">محاكاة مبنية على توزيع الأرباح الموسمية السنوية للشركة (25% ~ 30%+ سنوياً)</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="small fw-bold text-muted me-1"><i class="fa fa-calendar me-1"></i> فترة التوقع:</span>
                                <div class="btn-group btn-group-sm" role="group" id="btnGroupTimeframe">
                                    <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-1" onclick="switchProjectionPeriod('6M', this)">6M (6 شهور)</button>
                                    <button type="button" class="btn btn-dark active fw-bold px-3 py-1" onclick="switchProjectionPeriod('1Y', this)">1Y (سنة كاملة)</button>
                                    <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-1" onclick="switchProjectionPeriod('2Y', this)">2Y (سنتين)</button>
                                </div>
                            </div>
                        </div>

                        {{-- ملخص المؤشرات للسيناريوهات الثلاثة --}}
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <div class="p-2 rounded border" style="background-color: #f0fdf4; border-color: #86efac !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success text-white fw-bold" style="font-size:0.75rem;"><i class="fa fa-sync-alt me-1"></i> سيناريو 1: تدوير كامل الأرباح (100%)</span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-black font-monospace" id="proj_pct_reinvest">+44.8%</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <small class="text-muted fw-bold">الرصيد المتوقع نهاية الفترة:</small>
                                        <strong class="fs-5 text-success font-monospace" id="proj_stat_reinvest">0.00 ج</strong>
                                    </div>
                                    <small class="text-muted d-block" style="font-size:0.7rem;">أعلى نمو بفضل العائد التراكمي المركب</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-2 rounded border" style="background-color: #eff6ff; border-color: #93c5fd !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary text-white fw-bold" style="font-size:0.75rem;"><i class="fa fa-money-bill-wave me-1"></i> سيناريو 2: سحب كامل الأرباح شهرياً</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-black font-monospace">رأس مال ثابت</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <small class="text-muted fw-bold">إجمالي الكاش المسحوب:</small>
                                        <strong class="fs-5 text-primary font-monospace" id="proj_cash_withdraw_all">0.00 ج</strong>
                                    </div>
                                    <small class="text-muted d-block" style="font-size:0.7rem;">يبقى رأس المال ثابت ({{ number_format($partner->capital) }} ج) ويقبض ربحه كاش</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-2 rounded border" style="background-color: #faf5ff; border-color: #d8b4fe !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-warning text-dark fw-bold" style="font-size:0.75rem;">
                                            <i class="fa fa-hand-holding-dollar me-1"></i> سيناريو 3: سحب مثبت (<span id="proj_fixed_amt_lbl">{{ number_format($partner->monthly_fixed_withdrawal ?: 1000) }}</span> ج/شهر)
                                        </span>
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle fw-black font-monospace" id="proj_pct_fixed">+0%</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <small class="text-muted fw-bold">الرصيد النامي + كاش مسحوب:</small>
                                        <strong class="fs-5 text-dark font-monospace" id="proj_stat_fixed">0.00 ج</strong>
                                    </div>
                                    <small class="text-muted d-block" style="font-size:0.7rem;">
                                        @if(($partner->monthly_fixed_withdrawal ?? 0) > 0)
                                            سحب {{ number_format($partner->monthly_fixed_withdrawal) }} ج شهرياً وتدوير ما زاد عنه
                                        @else
                                            (سحب المبلغ الشهري المحدد في البروفايل وتدوير ما زاد عنه)
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- منطقة رسم اللاين شارت --}}
                        <div style="height: 310px; position: relative;">
                            <canvas id="chartProfitProjections"></canvas>
                        </div>

                        {{-- شريط نسب مواسم السنة التي حددها المستخدم --}}
                        <div class="mt-3 pt-2 border-top">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <small class="fw-bold text-dark"><i class="fa fa-calendar-alt text-primary me-1"></i> توزيع نسب الأرباح المتوقعة على شهور السنة (مواسم التجارة):</small>
                                <span class="badge bg-dark text-warning">مجموع نسب السنة: 38.0%</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 justify-content-between text-center" style="font-size:0.73rem;">
                                <div class="p-1 px-2 rounded bg-light border flex-fill">يناير<br><strong class="text-primary font-monospace">1.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-light border flex-fill">فبراير<br><strong class="text-primary font-monospace">1.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-light border flex-fill">مارس<br><strong class="text-primary font-monospace">2.0%</strong></div>
                                <div class="p-1 px-2 rounded bg-light border flex-fill">أبريل<br><strong class="text-primary font-monospace">3.0%</strong></div>
                                <div class="p-1 px-2 rounded bg-success-subtle border border-success-subtle flex-fill">مايو<br><strong class="text-success font-monospace">5.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-success-subtle border border-success-subtle flex-fill">يونيو<br><strong class="text-success font-monospace">6.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-success-subtle border border-success-subtle flex-fill">يوليو<br><strong class="text-success font-monospace">6.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-success-subtle border border-success-subtle flex-fill">أغسطس<br><strong class="text-success font-monospace">4.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-light border flex-fill">سبتمبر<br><strong class="text-primary font-monospace">2.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-light border flex-fill">أكتوبر<br><strong class="text-primary font-monospace">1.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-light border flex-fill">نوفمبر<br><strong class="text-primary font-monospace">1.5%</strong></div>
                                <div class="p-1 px-2 rounded bg-light border flex-fill">ديسمبر<br><strong class="text-primary font-monospace">1.5%</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- شارتات نمو رأس المال وتطور الحصة --}}
            <div class="row g-3 mb-2">
                <div class="col-md-6">
                    <div class="chart-formal">
                        <div class="chart-header">
                            <span><i class="fa fa-chart-area text-primary me-2"></i> تطور رصيد الشريك عبر الدورات الشهرية</span>
                            <small class="text-muted">تدوير الأرباح</small>
                        </div>
                        <div style="height: 260px;">
                            <canvas id="chartCapitalGrowth"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="chart-formal">
                        <div class="chart-header">
                            <span><i class="fa fa-chart-bar text-success me-2"></i> صافي الأرباح المحققة مقابل المسحوبات النقدية</span>
                            <small class="text-muted">لكل دورة شهرية</small>
                        </div>
                        <div style="height: 260px;">
                            <canvas id="chartProfitsVsWithdrawn"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="chart-formal">
                        <div class="chart-header">
                            <span><i class="fa fa-arrow-trend-up text-warning me-2"></i> تطور نسبة مساهمة الشريك بعد كل إقفال</span>
                            <span class="badge bg-light text-dark border">النسبة المعتمدة الحالية: {{ number_format($capitalSharePct, 2) }}%</span>
                        </div>
                        <div style="height: 200px;">
                            <canvas id="chartShareEvolution"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center pt-2 pb-1 border-top border-light text-muted small">
                <small><i class="fa fa-shield-alt text-success me-1"></i> تقرير مالي معتمد مستخرج إلكترونياً من نظام شركة الضبع لإدارة الشركاء والحصص</small>
            </div>
        </div>

        {{-- الجداول التفصيلية --}}
        <div class="card-formal mb-4">
            <div class="partners-tabs-bar no-print">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link tab-btn-periods active" data-bs-toggle="tab" data-bs-target="#tabPeriods" type="button">
                            <i class="fa fa-calendar-check"></i>
                            <span>سجل الدورات الشهرية</span>
                            <span class="badge bg-dark text-white border border-secondary ms-1">{{ $lines->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link tab-btn-history" data-bs-toggle="tab" data-bs-target="#tabShareHistory" type="button">
                            <i class="fa fa-history"></i>
                            <span>سجل تغير النسب: كانت كام وبقت كام</span>
                            <span class="badge bg-dark text-warning border border-warning ms-1">{{ $shareHistoryLines->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link tab-btn-movements" data-bs-toggle="tab" data-bs-target="#tabMovements" type="button">
                            <i class="fa fa-exchange-alt"></i>
                            <span>الحركات المالية المباشرة</span>
                            <span class="badge bg-dark text-info border border-info ms-1">{{ $movements->total() }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content p-0">
                {{-- التاب 1: التقفيلات الشهرية --}}
                <div class="tab-pane fade show active" id="tabPeriods">
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الدورة الشهرية</th>
                                    <th>الرصيد قبل التقفيل</th>
                                    <th class="text-center" style="background:#334155;">النسبة كانت كام</th>
                                    <th>ربح الحصة (g)</th>
                                    <th>نسبة الإدارة (a)</th>
                                    <th>صافي ربح الشريك (n)</th>
                                    <th>المسحوب نقداً</th>
                                    <th>المدوّر بالرصيد</th>
                                    <th>الرصيد بعد التقفيل</th>
                                    <th class="text-center" style="background:#1e40af;">النسبة بقت كام</th>
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
                                    <td class="fw-bold">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $l->period_name ?: $l->end_date }}</td>
                                    <td>{{ number_format($l->opening_capital, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border fw-bold">
                                            {{ number_format($openPct, 2) }}%
                                        </span>
                                    </td>
                                    <td class="fw-bold {{ $l->gross_share >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $l->gross_share >= 0 ? '+' : '' }}{{ number_format($l->gross_share, 2) }} ج
                                    </td>
                                    <td class="fw-bold text-dark">
                                        +{{ number_format($l->ahmed_commission, 2) }} ج ({{ number_format($l->ahmed_ratio_pct, 0) }}%)
                                    </td>
                                    <td class="fw-bold fs-6 {{ $l->net_share >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $l->net_share >= 0 ? '+' : '' }}{{ number_format($l->net_share, 2) }} ج
                                    </td>
                                    <td class="text-danger fw-bold">
                                        {{ $l->withdrawn_amount > 0 ? number_format($l->withdrawn_amount, 2) . ' ج' : '—' }}
                                    </td>
                                    <td class="text-success fw-bold">
                                        {{ $l->reinvested_amount != 0 ? '+' . number_format($l->reinvested_amount, 2) . ' ج' : '—' }}
                                    </td>
                                    <td class="fw-bold text-dark fs-6">{{ number_format($l->closing_capital, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                            {{ number_format($closePct, 2) }}%
                                        </span>
                                        @if(abs($pctDiff) > 0.0001)
                                            <small class="d-block {{ $pctDiff > 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.7rem;">
                                                {{ $pctDiff > 0 ? '+' : '' }}{{ number_format($pctDiff, 2) }}%
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">لم يشارك هذا الشريك في أي دورة شهرية حتى الآن.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                        <div class="small text-muted">
                            عرض <strong>{{ $lines->firstItem() ?? 0 }}</strong> إلى <strong>{{ $lines->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $lines->total() }}</strong> دورة
                        </div>
                        <div>
                            {{ $lines->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- التاب 2: سجل تغير النسب (كانت كام وبقت كام) --}}
                <div class="tab-pane fade" id="tabShareHistory">
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ والوقت</th>
                                    <th>الحدث / السبب</th>
                                    <th class="text-center">رصيد الشريك قبل</th>
                                    <th class="text-center" style="background:#334155;">النسبة كانت كام (%)</th>
                                    <th class="text-center">التغير في الرصيد</th>
                                    <th class="text-center">رصيد الشريك بعد</th>
                                    <th class="text-center" style="background:#1e40af;">النسبة بقت كام (%)</th>
                                    <th class="text-center">فرق النسبة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shareHistoryLines as $hl)
                                <tr>
                                    <td class="fw-bold">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ date('Y-m-d H:i', strtotime($hl->event_date)) }}</td>
                                    <td class="fw-bold text-dark">{{ $hl->event_title }}</td>
                                    <td class="text-center">{{ number_format($hl->capital_before, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border fw-bold fs-6">
                                            {{ number_format($hl->share_pct_before, 2) }}%
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold {{ $hl->capital_change > 0 ? 'text-success' : ($hl->capital_change < 0 ? 'text-danger' : 'text-muted') }}">
                                        {{ abs($hl->capital_change) >= 0.01 ? (($hl->capital_change > 0 ? '+' : '') . number_format($hl->capital_change, 2) . ' ج') : '—' }}
                                    </td>
                                    <td class="text-center fw-bold text-dark">{{ number_format($hl->capital_after, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold fs-6">
                                            {{ number_format($hl->share_pct_after, 2) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($hl->share_pct_diff > 0.0001)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                                                <i class="fa fa-arrow-up me-1"></i>+{{ number_format($hl->share_pct_diff, 2) }}%
                                            </span>
                                        @elseif($hl->share_pct_diff < -0.0001)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">
                                                <i class="fa fa-arrow-down me-1"></i>{{ number_format($hl->share_pct_diff, 2) }}%
                                            </span>
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
                    <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                        <div class="small text-muted">
                            عرض <strong>{{ $shareHistoryLines->firstItem() ?? 0 }}</strong> إلى <strong>{{ $shareHistoryLines->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $shareHistoryLines->total() }}</strong> سجل
                        </div>
                        <div>
                            {{ $shareHistoryLines->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- التاب 3: الحركات المباشرة --}}
                <div class="tab-pane fade" id="tabMovements">
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ والوقت</th>
                                    <th>نوع الحركة</th>
                                    <th>المبلغ</th>
                                    <th>الخزينة المستلمة/المصروف منها</th>
                                    <th>البيان والملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $m)
                                <tr>
                                    <td class="fw-bold">{{ $loop->iteration }}</td>
                                    <td>{{ date('Y-m-d H:i', strtotime($m->created_at)) }}</td>
                                    <td>
                                        @if($m->type === 'initial_deposit')
                                            <span class="badge bg-light text-success border">إيداع رأس مال تأسيسي</span>
                                        @elseif($m->type === 'capital_increase')
                                            <span class="badge bg-light text-primary border">زيادة رأس مال</span>
                                        @elseif($m->type === 'capital_withdrawal')
                                            <span class="badge bg-light text-danger border">سحب من رأس المال</span>
                                        @elseif($m->type === 'profit_withdrawal')
                                            <span class="badge bg-light text-warning text-dark border">صرف أرباح دورية</span>
                                        @elseif($m->type === 'profit_reinvest')
                                            <span class="badge bg-light text-info text-dark border">تدوير أرباح بالرصيد</span>
                                        @else
                                            <span class="badge bg-light text-secondary border">{{ $m->type }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold fs-6 {{ in_array($m->type, ['initial_deposit', 'capital_increase', 'profit_reinvest']) ? 'text-success' : 'text-danger' }}">
                                        {{ in_array($m->type, ['initial_deposit', 'capital_increase', 'profit_reinvest']) ? '+' : '-' }}{{ number_format($m->amount, 2) }} ج
                                    </td>
                                    <td>{{ $m->account_name ?: '—' }}</td>
                                    <td class="text-muted">{{ $m->notes ?: '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">لا توجد حركات مالية مسجلة لهذا الشريك.</td>
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
                            {{ $movements->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- خانات التوقيع والاعتماد الرسمية للطباعة --}}
        <div class="print-only" style="margin-top: 14px; page-break-inside: avoid;">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">إعداد وتدقيق الحسابات</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">وحدة الحسابات والرقابة المالية</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">اعتماد الإدارة</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">{{ $headName }}</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">الاعتماد: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">توقيع واستلام الشريك</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">{{ $partner->name }}</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
            </div>
            <div class="text-center text-muted mt-2 pt-2 border-top" style="font-size:7pt;">
                كشف حساب استثماري رسمي صادر من النظام المالي لشركة الضبع · صادر بتاريخ {{ date('Y-m-d H:i') }}
            </div>
        </div>

    </div>
</div>

{{-- 1. زيادة رأس المال --}}
<div class="modal fade" id="modalAddCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.addCapital', $partner->id) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-plus me-2"></i> زيادة رأس مال الشريك ({{ $partner->name }})</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">المبلغ المودع (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control form-control-sm fw-bold text-success" placeholder="المبلغ..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">الخزينة المستلمة للمبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-select-sm fw-bold" required>
                        <option value="">-- تحديد الخزينة المستلمة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">ملاحظات</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="سبب زيادة الحصة...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark btn-sm fw-bold px-4">تأكيد وإيداع وتحديث النسب</button>
            </div>
        </form>
    </div>
</div>

{{-- 2. سحب من رأس المال --}}
<div class="modal fade" id="modalWithdrawCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.withdrawCapital', $partner->id) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-minus me-2"></i> سحب من رأس مال الشريك ({{ $partner->name }})</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger border-2 border-danger d-flex align-items-start gap-2 py-2 px-3 mb-3">
                    <i class="fa fa-triangle-exclamation text-danger fs-4 mt-1 flex-shrink-0"></i>
                    <div class="small">
                        <strong class="text-danger d-block mb-1">تحذير هام جداً:</strong>
                        ده سحب من <u>رأس مال الشريك</u>، وده هيحدث نسب المساهمة ويتسجل في الهيستوري (النسب كانت كام وبقت كام).
                    </div>
                </div>
                <div class="alert alert-secondary py-2 px-3 small rounded mb-3">
                    رأس المال الحالي المتاح: <strong>{{ number_format($partner->capital, 2) }} ج</strong>.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">المبلغ المراد سحبه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" max="{{ $partner->capital }}" name="amount" class="form-control form-control-sm fw-bold text-danger" placeholder="المبلغ..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">الخزينة المصروف منها المبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-select-sm fw-bold" required>
                        <option value="">-- تحديد الخزينة للصرف --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">ملاحظات / سبب السحب</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="سبب استرداد جزء من رأس المال...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-danger btn-sm fw-bold px-4" onclick="return confirm('تأكيد نهائي: سيتم سحب المبلغ وتحديث النسب وتسجيلها في الهيستوري. هل تريد المتابعة؟')">
                    <i class="fa fa-check me-1"></i> تأكيد وصرف من الخزينة
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 3. تعديل بيانات الشريك --}}
<div class="modal fade" id="modalEditPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.update', $partner->id) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-pen me-2"></i> تعديل بيانات الشريك والنسبة</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">اسم الشريك</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="{{ $partner->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control form-control-sm" value="{{ $partner->phone }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">نسبة الإدارة من الربح (%) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" class="form-control fw-bold" value="{{ $partner->ahmed_ratio }}" required>
                        <span class="input-group-text bg-white fw-bold">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">
                        <i class="fa fa-hand-holding-dollar text-danger me-1"></i> المسحوب شهرياً (اختياري)
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="100" min="0" name="monthly_fixed_withdrawal" class="form-control fw-bold text-danger" value="{{ $partner->monthly_fixed_withdrawal }}" placeholder="مثال: 1000 أو 3000">
                        <span class="input-group-text bg-white fw-bold">ج / شهرياً</span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:0.72rem;">إذا كان للشريك مبلغ مثبت يسحبه شهرياً، يُدرج تلقائياً عند التقفيل الجزئي. اتركه فارغاً إذا كان السحب متغيراً.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">ملاحظات</label>
                    <input type="text" name="notes" class="form-control form-control-sm" value="{{ $partner->notes }}">
                </div>
                @if($partner->status === 'active')
                <div class="mb-1 border rounded p-3 bg-light">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_head" value="1" id="showIsHead"
                               onchange="const r=this.form.querySelector('[name=ahmed_ratio]'); r.required=!this.checked; r.closest('.mb-3').style.display=this.checked?'none':'';">
                        <label class="form-check-label fw-bold" for="showIsHead">تعيين هذا الحساب كحساب الإدارة الرئيسي</label>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:0.72rem;">حساب الإدارة تدخل فيه الأرباح من نسب الشغل مع الشركاء، وما لا يُسحب يُضاف لرصيده ويأخذ حصته من الدورة القادمة.</small>
                </div>
                @endif
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark btn-sm fw-bold px-4">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

{{-- 4. مودال المشاركة عبر واتساب --}}
<div class="modal fade" id="modalWhatsAppShare" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title fw-bold">
                    <i class="fa-brands fa-whatsapp fs-5 me-2"></i> مشاركة ملف الشريك والشارتات عبر واتساب
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 rounded-3 small fw-bold mb-3">
                    <i class="fa fa-info-circle me-1"></i> يمكنك إرسال التقرير المالي المصور كاملاً بالشارتات البيانية مباشرة إلى واتساب الشريك، أو تحميله كصورة أو ملف PDF لإرفاقه.
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">اسم الشريك:</label>
                        <input type="text" class="form-control form-control-sm fw-bold bg-light" value="{{ $partner->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-success">رقم الواتساب <span class="text-danger">*</span>:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-success fw-bold"><i class="fa-brands fa-whatsapp"></i></span>
                            <input type="text" id="waPartnerPhone" class="form-control form-control-sm fw-bold text-success font-monospace" value="{{ $partner->phone }}" placeholder="01xxxxxxxxx أو 201xxxxxxxxx">
                        </div>
                        <small class="text-muted" style="font-size:0.7rem;">يتم تنسيق كود الدولة تلقائياً (مثال: 010... ← 2010...).</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">نص الرسالة المرفقة مع التقرير:</label>
                    <textarea id="waMessageText" rows="6" class="form-control form-control-sm font-monospace" style="font-size:0.84rem; line-height: 1.6;">السلام عليكم أستاذ {{ $partner->name }}،
تحية طيبة وبعد، مرفق لحضرتكم كشف حسابكم والتقرير المالي المعتمد من شركة الضبع للتجارة والتوزيع:

📊 المؤشرات المالية لحسابكم:
• الرصيد المعتمد بالدورة: {{ number_format($partner->capital, 2) }} ج
• نسبة المساهمة المعتمدة: {{ number_format($capitalSharePct, 2) }}%
• إجمالي صافي الأرباح المحققة: {{ number_format($totalNetEarned, 2) }} ج
• الأرباح المدورة بالرصيد: {{ number_format($totalReinvested, 2) }} ج
• إجمالي المسحوبات النقدية: {{ number_format($totalWithdrawn, 2) }} ج
• عدد الدورات المحاسبية: {{ $lines->total() }} دورة معتمدة
• تاريخ استخراج التقرير: {{ date('Y-m-d') }}

(مرفق صورة التقرير الرسمي والشارتات البيانية)</textarea>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <div class="fw-bold small text-dark mb-2"><i class="fa fa-paperclip text-primary me-1"></i> خيارات إرفاق وإرسال التقرير:</div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-bold py-2" onclick="exportProfileAsImage()">
                                <i class="fa fa-image me-1"></i> تحميل صورة التقرير (PNG)
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold py-2" onclick="exportProfileAsPDF()">
                                <i class="fa fa-file-pdf me-1"></i> تحميل ملف PDF بالشارتات
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-success btn-sm fw-bold px-4 py-2 shadow-sm" onclick="sendToWhatsAppWithImage()" id="btnSendWhatsApp">
                    <i class="fa-brands fa-whatsapp fs-5 me-1"></i> فتح محادثة الواتساب ونسخ الصورة
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openAddCapitalModal() { new bootstrap.Modal(document.getElementById('modalAddCapital')).show(); }

    function openWithdrawCapitalModal() {
        const doOpen = () => {
            new bootstrap.Modal(document.getElementById('modalWithdrawCapital')).show();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'تحذير هام جداً!',
                html: '<div style="font-size:1.05rem;line-height:1.7;color:#1e293b;" class="text-center">' +
                      'ده سحب من <strong class="text-danger">رأس مال الشريك ({{ addslashes($partner->name) }})</strong><br>' +
                      '<span class="fw-bold text-danger">هيتم تحديث نسب المساهمة وتسجيل النسب قبل وبعد السحب في الهيستوري.</span><br><br>' +
                      '<small class="text-muted">هل تريد المتابعة وفتح نموذج السحب؟</small>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa fa-arrow-left me-1"></i> متابعة السحب',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    doOpen();
                }
            });
        } else {
            if (confirm('تنبيه هام:\nده سحب من رأس مال الشريك ({{ addslashes($partner->name) }}) وهيحدث النسب ويتسجل في الهيستوري.\n\nهل تريد المتابعة؟')) {
                doOpen();
            }
        }
    }

    function openEditPartnerModal() { new bootstrap.Modal(document.getElementById('modalEditPartner')).show(); }

    const ctxCap = document.getElementById('chartCapitalGrowth').getContext('2d');
    new Chart(ctxCap, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'الرصيد (ج)',
                data: {!! json_encode($chartCapitalGrowth) !!},
                borderColor: '#1e40af',
                backgroundColor: 'rgba(30, 64, 175, 0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.25,
                pointRadius: 4,
                pointBackgroundColor: '#1e40af'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false } }
        }
    });

    const ctxProf = document.getElementById('chartProfitsVsWithdrawn').getContext('2d');
    new Chart(ctxProf, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'صافي الربح المحقق (ج)',
                    data: {!! json_encode($chartProfits) !!},
                    backgroundColor: '#047857',
                    borderRadius: 4
                },
                {
                    label: 'المسحوب نقداً (ج)',
                    data: {!! json_encode($chartWithdrawn) !!},
                    backgroundColor: '#b91c1c',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    const ctxShare = document.getElementById('chartShareEvolution').getContext('2d');
    new Chart(ctxShare, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'نسبة الشريك في الدورة (%)',
                data: {!! json_encode($chartShares) !!},
                borderColor: '#b45309',
                backgroundColor: 'rgba(180, 83, 9, 0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.25,
                pointRadius: 4,
                pointBackgroundColor: '#b45309'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // ════════════════════════════════════════════════════════════
    // شارت محاكي توقعات الأرباح المستقبلية للشريك
    // ════════════════════════════════════════════════════════════
    const seasonalMonthlyRates = [
        { month: 'يناير', rate: 1.5 },
        { month: 'فبراير', rate: 1.5 },
        { month: 'مارس', rate: 2.0 },
        { month: 'أبريل', rate: 3.0 },
        { month: 'مايو', rate: 5.5 },
        { month: 'يونيو', rate: 6.5 },
        { month: 'يوليو', rate: 6.5 },
        { month: 'أغسطس', rate: 4.5 },
        { month: 'سبتمبر', rate: 2.5 },
        { month: 'أكتوبر', rate: 1.5 },
        { month: 'نوفمبر', rate: 1.5 },
        { month: 'ديسمبر', rate: 1.5 },
    ];

    const basePartnerCap = {{ (float) $partner->capital }};
    const baseFixedWithdrawal = {{ (float) ($partner->monthly_fixed_withdrawal ?: 1000) }};

    let chartProjectionsInstance = null;
    let currentProjectionPeriod = '1Y';

    function buildProjectionData(periodCode) {
        let monthCount = 12;
        if (periodCode === '6M') monthCount = 6;
        else if (periodCode === '2Y') monthCount = 24;

        const labels = ['البداية'];
        const dataReinvest = [basePartnerCap];
        const dataWithdrawAll = [basePartnerCap];
        const dataFixed = [basePartnerCap];

        let capReinvest = basePartnerCap;
        let capWithdrawAll = basePartnerCap;
        let totalCashWithdrawAll = 0;
        let capFixed = basePartnerCap;
        let totalCashFixed = 0;

        for (let i = 0; i < monthCount; i++) {
            const seasonIdx = i % 12;
            const rateObj = seasonalMonthlyRates[seasonIdx];
            const yearSuffix = monthCount > 12 ? (i < 12 ? ' (سنة 1)' : ' (سنة 2)') : '';
            labels.push(`${rateObj.month} (${rateObj.rate}%)${yearSuffix}`);

            const r = rateObj.rate / 100;

            // السيناريو 1: تدوير كامل الأرباح (Compound Growth)
            const profitReinvest = capReinvest * r;
            capReinvest += profitReinvest;
            dataReinvest.push(Math.round(capReinvest));

            // السيناريو 2: سحب كامل الأرباح (رأس المال ثابت)
            const profitWithdrawAll = capWithdrawAll * r;
            totalCashWithdrawAll += profitWithdrawAll;
            dataWithdrawAll.push(Math.round(capWithdrawAll));

            // السيناريو 3: سحب المسحوب المثبت وتدوير ما زاد عنه
            const profitFixed = capFixed * r;
            const withdrawAmt = Math.min(profitFixed, baseFixedWithdrawal);
            totalCashFixed += withdrawAmt;
            const reinvestedPart = profitFixed - withdrawAmt;
            capFixed += reinvestedPart;
            dataFixed.push(Math.round(capFixed));
        }

        // تحديث إحصائيات الكروت أعلى الشارت
        const pctReinvest = basePartnerCap > 0 ? (((capReinvest - basePartnerCap) / basePartnerCap) * 100).toFixed(1) : '0.0';
        const pctFixed = basePartnerCap > 0 ? (((capFixed - basePartnerCap) / basePartnerCap) * 100).toFixed(1) : '0.0';

        const elReinvest = document.getElementById('proj_stat_reinvest');
        const elPctReinvest = document.getElementById('proj_pct_reinvest');
        const elCashWithdrawAll = document.getElementById('proj_cash_withdraw_all');
        const elFixed = document.getElementById('proj_stat_fixed');
        const elPctFixed = document.getElementById('proj_pct_fixed');
        const elFixedAmtLbl = document.getElementById('proj_fixed_amt_lbl');

        if (elReinvest) elReinvest.innerText = Number(Math.round(capReinvest)).toLocaleString('en-US') + ' ج';
        if (elPctReinvest) elPctReinvest.innerText = '+' + pctReinvest + '% نمو';
        if (elCashWithdrawAll) elCashWithdrawAll.innerText = '+' + Number(Math.round(totalCashWithdrawAll)).toLocaleString('en-US') + ' ج';
        if (elFixed) elFixed.innerText = Number(Math.round(capFixed)).toLocaleString('en-US') + ' ج (كاش: ' + Number(Math.round(totalCashFixed)).toLocaleString('en-US') + ' ج)';
        if (elPctFixed) elPctFixed.innerText = '+' + pctFixed + '% نمو رصيد';
        if (elFixedAmtLbl) elFixedAmtLbl.innerText = Number(baseFixedWithdrawal).toLocaleString('en-US');

        return { labels, dataReinvest, dataWithdrawAll, dataFixed };
    }

    function initProjectionChart(periodCode) {
        currentProjectionPeriod = periodCode;
        const proj = buildProjectionData(periodCode);

        const ctx = document.getElementById('chartProfitProjections').getContext('2d');
        if (chartProjectionsInstance) {
            chartProjectionsInstance.destroy();
        }

        chartProjectionsInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: proj.labels,
                datasets: [
                    {
                        label: 'سيناريو 1: تدوير كامل الأرباح (أعلى نمو مركب)',
                        data: proj.dataReinvest,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.08)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#059669',
                        tension: 0.35,
                        fill: false
                    },
                    {
                        label: 'سيناريو 2: سحب كامل الأرباح شهرياً (رأس مال ثابت)',
                        data: proj.dataWithdrawAll,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#2563eb',
                        tension: 0.1,
                        fill: false
                    },
                    {
                        label: `سيناريو 3: سحب المسحوب المثبت (${Number(baseFixedWithdrawal).toLocaleString()} ج/شهر) وتدوير الباقي`,
                        data: proj.dataFixed,
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.08)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#d97706',
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Cairo', weight: '700', size: 11 },
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        rtl: true,
                        titleFont: { family: 'Cairo', weight: 'bold' },
                        bodyFont: { family: 'Cairo' },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.parsed.y || 0;
                                return label + ': ' + Number(value).toLocaleString('en-US') + ' ج';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return Number(value).toLocaleString('en-US') + ' ج';
                            },
                            font: { family: 'Cairo', size: 10 }
                        },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    },
                    x: {
                        ticks: {
                            font: { family: 'Cairo', size: 10, weight: '600' }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    function switchProjectionPeriod(periodCode, btn) {
        document.querySelectorAll('#btnGroupTimeframe button').forEach(b => {
            b.classList.remove('btn-dark', 'active');
            b.classList.add('btn-outline-secondary');
        });
        if (btn) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-dark', 'active');
        }
        initProjectionChart(periodCode);
    }

    // تشغيل الشارت فوراً
    initProjectionChart('1Y');

    function openWhatsAppModal() {
        new bootstrap.Modal(document.getElementById('modalWhatsAppShare')).show();
    }

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

        return await html2canvas(target, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            onclone: (clonedDoc) => {
                const el = clonedDoc.getElementById('partnerProfileExportArea');
                if (el) {
                    el.style.width = '1050px';
                    el.style.maxWidth = '1050px';
                    el.style.padding = '20px';
                    el.style.backgroundColor = '#ffffff';
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
                Swal.fire({
                    title: 'تم استخراج الصورة بنجاح!',
                    text: 'تم تحميل صورة كشف حساب الشريك مع الشارتات البيانية بجودة فائقة.',
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
                });
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

            const margin = 8;
            const maxContentWidth = pageWidth - (margin * 2);
            const maxContentHeight = pageHeight - (margin * 2);

            const imgWidth = maxContentWidth;
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
            const filename = `تقرير_الشريك_${partnerName}_${new Date().toISOString().slice(0, 10)}.pdf`;
            pdf.save(filename);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'تم تجهيز ملف PDF بنجاح!',
                    text: 'تم تحميل كشف الحساب والشارتات كملف PDF رسمي.',
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
                });
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
        const rawPhone = phoneInput ? phoneInput.value.trim() : '';
        const formattedPhone = formatPhoneForWhatsApp(rawPhone);

        if (!formattedPhone) {
            alert('يرجى إدخال رقم هاتف صحيح للشريك للمتابعة عبر واتساب.');
            if (phoneInput) phoneInput.focus();
            return;
        }

        const msgInput = document.getElementById('waMessageText');
        const msgText = msgInput ? msgInput.value.trim() : '';

        const btn = document.getElementById('btnSendWhatsApp');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> جاري تجهيز التقرير...'; }

        try {
            // 1. استخراج الصورة
            const canvas = await captureProfileCanvas();

            // 2. تحميل الصورة احتياطياً للجهاز
            const partnerName = {!! json_encode($partner->name) !!}.replace(/[^a-zA-Z0-9\u0600-\u06FF_-]/g, '_');
            const filename = `كشف_حساب_${partnerName}_${new Date().toISOString().slice(0, 10)}.png`;
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // 3. نسخ الصورة للحافظة إذا كان المتصفح يدعم ذلك
            let copiedToClipboard = false;
            if (navigator.clipboard && window.ClipboardItem && canvas.toBlob) {
                try {
                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                    if (blob) {
                        await navigator.clipboard.write([
                            new ClipboardItem({ 'image/png': blob })
                        ]);
                        copiedToClipboard = true;
                    }
                } catch (clipErr) {
                    console.warn('Clipboard write image not permitted:', clipErr);
                }
            }

            // 4. فتح واتساب
            const waUrl = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(msgText)}`;
            window.open(waUrl, '_blank');

            // إغلاق المودال
            const modalEl = document.getElementById('modalWhatsAppShare');
            const modalInst = bootstrap.Modal.getInstance(modalEl);
            if (modalInst) modalInst.hide();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'تم فتح محادثة الواتساب!',
                    html: `
                        <div class="text-center">
                            <p class="mb-2">تم تجهيز كشف الحساب المصور وتنزيله على جهازك.</p>
                            ${copiedToClipboard
                                ? '<div class="alert alert-success py-2 px-3 fw-bold small"><i class="fa fa-check-circle me-1"></i> تم نسخ صورة التقرير والشارتات للحافظة! يمكنك الآن الضغط على <strong>(Ctrl + V)</strong> داخل شات الواتساب للصق الصورة فوراً مع الرسالة.</div>'
                                : '<div class="alert alert-info py-2 px-3 fw-bold small"><i class="fa fa-paperclip me-1"></i> تم تحميل صورة التقرير، يمكنك إرفاقها أو سحبها مباشرة في شات الواتساب المفتوح.</div>'
                            }
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#059669'
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
