<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إقفال الدورة الشهرية وتوزيع الأرباح - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        }
        .formula-card-formal {
            background: var(--slate-900);
            border: 1px solid var(--slate-800);
            border-radius: 8px;
            color: #ffffff;
            padding: 20px;
            margin-bottom: 24px;
        }
        .formula-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            padding: 14px 12px;
            text-align: center;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .formula-box:hover {
            transform: translateY(-2px);
        }
        .box-capital-prev {
            background: linear-gradient(145deg, rgba(14, 165, 233, 0.15), rgba(15, 23, 42, 0.9)) !important;
            border: 2px solid #0284c7 !important;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.18);
        }
        .box-capital-curr {
            background: linear-gradient(145deg, rgba(99, 102, 241, 0.18), rgba(15, 23, 42, 0.9)) !important;
            border: 2px solid #6366f1 !important;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.18);
        }
        .box-capital-diff-pos {
            background: linear-gradient(145deg, rgba(16, 185, 129, 0.22), rgba(15, 23, 42, 0.9)) !important;
            border: 2px solid #10b981 !important;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.18);
        }
        .box-capital-diff-neg {
            background: linear-gradient(145deg, rgba(239, 68, 68, 0.22), rgba(15, 23, 42, 0.9)) !important;
            border: 2px solid #ef4444 !important;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.18);
        }
        .box-capital-ratio-pos {
            background: linear-gradient(145deg, rgba(245, 158, 11, 0.22), rgba(15, 23, 42, 0.9)) !important;
            border: 2px solid #f59e0b !important;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.18);
        }
        .box-capital-ratio-neg {
            background: linear-gradient(145deg, rgba(239, 68, 68, 0.22), rgba(15, 23, 42, 0.9)) !important;
            border: 2px solid #ef4444 !important;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.18);
        }
        .val-num-large {
            font-size: 1.6rem !important;
            font-weight: 900 !important;
            letter-spacing: -0.5px;
            font-variant-numeric: tabular-nums;
            margin: 6px 0;
            line-height: 1.2;
        }
        .formula-box .label {
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .formula-box .val {
            font-size: 1.25rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }
        .table-responsive {
            overflow-x: visible !important;
        }
        .table-formal {
            margin: 0;
            width: 100% !important;
            table-layout: auto;
            vertical-align: middle;
            font-size: 0.82rem;
        }
        .table-formal thead th {
            background: var(--slate-800);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 10px 6px;
            border: none;
            white-space: normal;
            line-height: 1.3;
        }
        .table-formal tbody td {
            vertical-align: middle;
            padding: 9px 6px;
            border-bottom: 1px solid var(--slate-200);
            font-variant-numeric: tabular-nums;
        }
        .btn-decision {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 6px;
            border-radius: 4px !important;
            transition: all 0.15s ease-in-out;
        }
        .btn-decision.active {
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .btn-decision-reinvest.active {
            background-color: var(--brand-success) !important;
            border-color: var(--brand-success) !important;
            color: #ffffff !important;
        }
        .btn-decision-withdraw-all.active {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            color: #ffffff !important;
        }
        .btn-decision-withdraw-partial.active {
            background-color: var(--slate-700) !important;
            border-color: var(--slate-700) !important;
            color: #ffffff !important;
        }
        .badge-formal {
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 6mm 12mm 6mm;
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
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, th.no-print, td.no-print, button, .btn, .card-footer, .modal, button[type="submit"], #liveWithdrawalSummaryCard, .closing-actions-bar {
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
            .formula-card-formal {
                background: #f8fafc !important;
                border: 2px solid #0f172a !important;
                padding: 8px !important;
                margin-bottom: 10px !important;
            }
            .formula-card-formal .row > div {
                flex: 0 0 50% !important;
                max-width: 50% !important;
                padding: 3px !important;
            }
            .formula-card-formal .badge {
                border: 1px solid #475569 !important;
                color: #000000 !important;
            }
            .formula-box {
                background: #ffffff !important;
                border: 1px solid #94a3b8 !important;
                color: #000000 !important;
                padding: 6px !important;
            }
            .formula-box .label {
                color: #334155 !important;
                font-size: 7.5pt !important;
            }
            .formula-box .val {
                color: #0f172a !important;
                font-size: 11pt !important;
            }
            .formula-box small {
                color: #64748b !important;
                font-size: 6.5pt !important;
            }
            .card-formal {
                border: none !important;
                box-shadow: none !important;
                margin-bottom: 8px !important;
                border-radius: 0 !important;
                page-break-inside: auto !important;
                background: transparent !important;
                padding: 0 !important;
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
                font-size: 7.2pt !important;
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
                font-size: 7.2pt !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .table-formal tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            input.form-control, select.form-select {
                border: none !important;
                background: transparent !important;
                box-shadow: none !important;
                font-weight: bold !important;
                padding: 0 !important;
                font-size: 7.2pt !important;
            }
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة الرئيسية --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة إلى سجل الشركاء
                </a>
                <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    <i class="fa fa-file-invoice-dollar text-primary"></i> إقفال الدورة الشهرية وتوزيع الأرباح وتحديث النسب
                </h4>
            </div>
            <div class="text-muted small">
                <i class="fa fa-shield-alt text-success me-1"></i> النسب تتحدث وتُحفظ في سجل الهيستوري فور اعتماد الإقفال
            </div>
        </div>

        {{-- شريط معلومات الفترة المقفلة تلقائياً على آخر إقفال (بدون إمكانية تغيير التاريخ يدوياً) --}}
        <div class="card-formal p-3 mb-4 bg-light">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark p-2"><i class="fa fa-lock"></i></span>
                        <div>
                            <div class="small text-muted fw-bold">بداية الدورة الحالية (مقفول على آخر إقفال معتمد)</div>
                            <div class="fw-bold text-dark">
                                @if(!empty($lastPeriod))
                                    {{ $lastPeriod->period_name }} — بتاريخ: <span class="font-monospace text-primary">{{ $preview['start_date'] }}</span>
                                @else
                                    بداية الدورة التأسيسية: <span class="font-monospace text-primary">{{ $preview['start_date'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 border-start pe-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success p-2"><i class="fa fa-calendar-check"></i></span>
                        <div>
                            <div class="small text-muted fw-bold">تاريخ الإقفال الحالي (رأس المال اللحظي الآن)</div>
                            <div class="fw-bold text-dark font-monospace">{{ $preview['end_date'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-start">
                    <a href="{{ route('partners.closing') }}" class="btn btn-primary btn-sm fw-bold w-100 py-2">
                        <i class="fa fa-sync-alt me-1"></i> تحديث الأرقام اللحظية
                    </a>
                </div>
            </div>
        </div>

        {{-- بطاقة المعادلة المالية لاحتساب الربح القابل للتوزيع ونسبة العائد --}}
        @php
            $profitPct = ($preview['opening_capital'] > 0)
                ? round(($preview['gross_profit'] / $preview['opening_capital']) * 100, 2)
                : 0.0;
        @endphp
        <div class="formula-card-formal">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-50 flex-wrap gap-2">
                <span class="fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa fa-calculator text-warning"></i> معادلة احتساب الربح والنسبة (صافي الربح = رأس المال الحالي − رأس المال السابق)
                </span>
                <span class="badge bg-secondary font-monospace">
                    الفترة: {{ $preview['start_date'] }} إلى {{ $preview['end_date'] }}
                </span>
            </div>

            <div class="row g-3">
                {{-- 1. رأس المال السابق --}}
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="formula-box box-capital-prev py-3">
                        <span class="badge bg-info text-dark fw-black px-2 py-1 mb-2" style="font-size: 0.78rem;">
                            <i class="fa fa-history me-1"></i> 1. رأس المال السابق
                        </span>
                        <div class="label fs-6 mb-1" style="color: #bae6fd !important;">صافي رأس مال البداية المرجعي</div>
                        <div class="val val-num-large" style="color: #38bdf8 !important;">{{ number_format($preview['opening_capital'], 2) }} <span class="fs-6 fw-bold">ج</span></div>
                        <small class="text-light opacity-75 d-block mt-1" style="font-size:0.72rem;">
                            @if(($preview['last_period_withdrawn'] ?? 0) > 0 || ($preview['partner_deposits_net'] ?? 0) != 0)
                                إقفال سابق ({{ number_format($preview['raw_last_closing_capital'], 2) }} ج)
                                @if(($preview['last_period_withdrawn'] ?? 0) > 0)
                                    − أرباح مصروفة ({{ number_format($preview['last_period_withdrawn'], 2) }} ج)
                                @endif
                                @if(($preview['partner_deposits_net'] ?? 0) != 0)
                                    {{ $preview['partner_deposits_net'] > 0 ? '+' : '−' }} صافي حركات رأس المال ({{ number_format(abs($preview['partner_deposits_net']), 2) }} ج)
                                @endif
                            @else
                                مطابق لإقفال بداية الدورة ({{ $preview['start_date'] }})
                            @endif
                        </small>
                    </div>
                </div>

                {{-- 2. رأس المال الحالي --}}
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="formula-box box-capital-curr py-3">
                        <span class="badge bg-primary text-white fw-black px-2 py-1 mb-2" style="font-size: 0.78rem;">
                            <i class="fa fa-building me-1"></i> 2. رأس المال الحالي
                        </span>
                        <div class="label fs-6 mb-1" style="color: #c7d2fe !important;">رأس مال الشركة عند الإقفال</div>
                        <div class="val val-num-large" style="color: #a5b4fc !important;">{{ number_format($preview['closing_capital'], 2) }} <span class="fs-6 fw-bold">ج</span></div>
                        <small class="text-light opacity-75 d-block mt-1" style="font-size:0.72rem;">
                            إجمالي الحصص المشاركة بالدورة: <strong class="text-white">{{ number_format($preview['total_opening_shares'], 2) }} ج</strong>
                        </small>
                    </div>
                </div>

                {{-- 3. الفارق (صافي الربح) --}}
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="formula-box {{ $preview['gross_profit'] >= 0 ? 'box-capital-diff-pos' : 'box-capital-diff-neg' }} py-3">
                        <span class="badge {{ $preview['gross_profit'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white fw-black px-2 py-1 mb-2" style="font-size: 0.78rem;">
                            <i class="fa fa-scale-balanced me-1"></i> 3. الفارق (صافي الربح)
                        </span>
                        <div class="label fs-6 mb-1" style="{{ $preview['gross_profit'] >= 0 ? 'color: #a7f3d0 !important;' : 'color: #fecaca !important;' }}">صافي ربح الدورة القابل للتوزيع</div>
                        <div class="val val-num-large" style="{{ $preview['gross_profit'] >= 0 ? 'color: #34d399 !important;' : 'color: #f87171 !important;' }}">
                            {{ $preview['gross_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['gross_profit'], 2) }} <span class="fs-6 fw-bold">ج</span>
                        </div>
                        <small class="text-light opacity-75 d-block mt-1" style="font-size:0.72rem;">
                            الفارق = الحالي (2) − السابق (1)
                        </small>
                    </div>
                </div>

                {{-- 4. النسبة كام --}}
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="formula-box {{ $profitPct >= 0 ? 'box-capital-ratio-pos' : 'box-capital-ratio-neg' }} py-3">
                        <span class="badge bg-warning text-dark fw-black px-2 py-1 mb-2" style="font-size: 0.78rem;">
                            <i class="fa fa-percent me-1"></i> 4. النسبة كام %
                        </span>
                        <div class="label fs-6 mb-1" style="{{ $profitPct >= 0 ? 'color: #fde68a !important;' : 'color: #fecaca !important;' }}">نسبة الربح / النمو للدورة</div>
                        <div class="val val-num-large" style="{{ $profitPct >= 0 ? 'color: #fbbf24 !important;' : 'color: #f87171 !important;' }}">
                            {{ $profitPct >= 0 ? '+' : '' }}{{ number_format($profitPct, 2) }}%
                        </div>
                        <small class="text-light opacity-75 d-block mt-1" style="font-size:0.72rem;">
                            معدل العائد: (الفارق ÷ رأس المال السابق)
                        </small>
                    </div>
                </div>
            </div>

            @if($preview['gross_profit'] < 0)
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mt-3 mb-0 py-2 px-3 small rounded">
                <i class="fa fa-exclamation-triangle me-1"></i> تنبيه: سجلت الدورة الحالية عجزاً/خسارة قدرها {{ number_format(abs($preview['gross_profit']), 2) }} ج. يتحمل كل شريك حصته من الخسارة حسب نسبته مع عدم احتساب أي نسب شغل لحساب الإدارة.
            </div>
            @endif
        </div>

        {{-- جدول توزيع الحصص وقرارات الشركاء ومقارنة النسب قبل وبعد التقفيل --}}
        <form action="{{ route('partners.closing.store') }}" method="POST" id="formExecuteClosing">
            @csrf
            <input type="hidden" name="start_date" value="{{ $preview['start_date'] }}">
            <input type="hidden" name="end_date" value="{{ $preview['end_date'] }}">
            <input type="hidden" name="closing_capital" value="{{ $preview['closing_capital'] }}">

            <div class="card-formal mb-4 overflow-hidden">
                <div class="bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 border-bottom">
                    <div>
                        <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa fa-users text-primary"></i> بيان توزيع الأرباح ومقارنة النسب (قبل التقفيل ← بعد التقفيل)
                        </h5>
                        <small class="text-muted">تتحدث «النسبة بعد التقفيل (بقت كام %)» تلقائياً بمجرد تغيير قرار أي شريك أو حساب الإدارة بين التدوير أو السحب.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-success fw-bold" onclick="setAllDecisions('reinvest')">
                                <i class="fa fa-sync-alt me-1"></i> تعيين الكل: تدوير بالأرصدة
                            </button>
                            <button type="button" class="btn btn-outline-primary fw-bold" onclick="setAllDecisions('withdraw_all')">
                                <i class="fa fa-money-bill-wave me-1"></i> تعيين الكل: سحب كامل
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2 border-start ps-2">
                            <label class="form-label m-0 fw-bold small text-nowrap">اسم الدورة:</label>
                            <input type="text" name="period_name" class="form-control form-control-sm fw-bold" style="width: 250px;" value="إقفال دورة شهر {{ \Carbon\Carbon::parse($preview['end_date'])->translatedFormat('F Y') }}">
                        </div>
                    </div>
                </div>

                <div>
                    <table class="table table-formal table-hover m-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 13%;">الشريك / الحساب</th>
                                <th style="width: 11%;">الرصيد قبل التقفيل</th>
                                <th class="text-center" style="width: 8%; background:#334155;">النسبة كانت كام</th>
                                <th style="width: 10%;">ربح الحصة (g)</th>
                                <th class="text-center" style="width: 11%;">نسبة وأرباح الشغل</th>
                                <th style="width: 10%;">صافي الربح (n)</th>
                                <th style="width: 18%;">قرار التصرف والصرف</th>
                                <th style="width: 11%;">الرصيد بعد التقفيل</th>
                                <th class="text-center" style="width: 8%; background:#1e40af;">النسبة بقت كام</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- سطر حساب الإدارة --}}
                            @php
                                $ahmedOpenCap   = $preview['ahmed']['opening_capital'];
                                $ahmedOpenPct   = $preview['ahmed']['capital_share_pct'];
                                $ahmedNetProfit = $preview['ahmed']['total_profit'];
                                $ahmedCloseCap  = $preview['ahmed']['closing_capital'];
                                $ahmedClosePct  = $preview['ahmed']['closing_share_pct'];
                                $ahmedPctDiff   = $preview['ahmed']['share_pct_diff'];
                            @endphp
                            <tr id="row_partner_ahmed"
                                data-pid="ahmed"
                                data-opencap="{{ $ahmedOpenCap }}"
                                data-openpct="{{ $ahmedOpenPct }}"
                                data-net="{{ $ahmedNetProfit }}"
                                data-newcap="{{ $ahmedCloseCap }}"
                                style="background-color: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                                <td>
                                    <strong class="text-dark d-block">{{ $headName }}</strong>
                                    <span class="badge bg-dark text-white fw-bold" style="font-size:0.65rem;">حساب الإدارة</span>
                                </td>
                                <td class="fw-bold">{{ number_format($ahmedOpenCap, 2) }} ج</td>
                                <td class="text-center">
                                    <span class="badge-formal bg-light text-secondary border">
                                        {{ number_format($ahmedOpenPct, 2) }}%
                                    </span>
                                </td>
                                <td class="fw-bold {{ $preview['ahmed']['own_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $preview['ahmed']['own_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['ahmed']['own_profit'], 2) }} ج
                                    <small class="d-block text-muted" style="font-size:0.68rem;">ربح الرصيد المدور</small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-primary">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</div>
                                    <small class="text-muted d-block" style="font-size:0.68rem;">الأرباح من نسب الشغل</small>
                                </td>
                                <td class="fw-bold {{ $ahmedNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $ahmedNetProfit >= 0 ? '+' : '' }}{{ number_format($ahmedNetProfit, 2) }} ج
                                    <small class="d-block text-muted" style="font-size:0.68rem;">إجمالي ربح الإدارة</small>
                                </td>

                                {{-- عمود القرار والصرف المدمج لحساب الإدارة --}}
                                <td>
                                    @if($ahmedNetProfit > 0)
                                        <div class="btn-group btn-group-sm w-100 mb-1" role="group">
                                            <button type="button" class="btn btn-outline-success btn-decision btn-decision-reinvest btn-decision-ahmed active" id="btn_opt_reinvest_ahmed" onclick="setPartnerDecision('ahmed', 'reinvest', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                تدوير
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-decision btn-decision-withdraw-all btn-decision-ahmed" id="btn_opt_withdraw_all_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_all', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                سحب كامل
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-decision btn-decision-withdraw-partial btn-decision-ahmed" id="btn_opt_withdraw_partial_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_partial', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                سحب جزئي
                                            </button>
                                        </div>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" id="input_action_ahmed" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" id="input_withdrawn_ahmed" value="0">

                                        <div id="box_reinvest_ahmed" class="payout-box-ahmed px-2 py-1 rounded bg-light text-success border text-center fw-bold" style="font-size:0.72rem;">
                                            <i class="fa fa-check-circle me-1"></i> تدوير بالحساب للدورة القادمة
                                        </div>

                                        <div id="box_withdraw_all_ahmed" class="payout-box-ahmed mt-1" style="display:none;">
                                            <select name="decisions[ahmed][account_id]" id="acc_sel_ahmed" class="form-select form-select-sm fw-bold border-danger treasury-select" style="font-size:0.75rem;" data-pid="ahmed" data-name="{{ $headName }}">
                                                <option value="">-- اختر خزينة الصرف ({{ number_format($ahmedNetProfit, 2) }} ج) --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @php
                                            $ahmedFixedWithdrawal = (float) ($preview['ahmed']['monthly_fixed_withdrawal'] ?? 0);
                                            $ahmedDefaultPartialVal = $ahmedFixedWithdrawal > 0 ? min($ahmedNetProfit, $ahmedFixedWithdrawal) : 0;
                                        @endphp
                                        <div id="box_withdraw_partial_ahmed" class="payout-box-ahmed mt-1" style="display:none;">
                                            <div class="input-group input-group-sm mb-1">
                                                <input type="number" step="0.01" min="0" max="{{ $ahmedNetProfit }}" id="inp_partial_val_ahmed" data-fixed="{{ $ahmedFixedWithdrawal }}" class="form-control fw-bold text-danger" value="{{ $ahmedDefaultPartialVal > 0 ? $ahmedDefaultPartialVal : '' }}" placeholder="0.00" oninput="onPartialInput('ahmed', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                <span class="input-group-text bg-white fw-bold">ج</span>
                                            </div>
                                            <div class="text-muted fw-bold mb-1 d-flex justify-content-between align-items-center" style="font-size:0.7rem;" id="partial_rollover_badge_ahmed">
                                                <span>المدور: <strong class="text-success">{{ number_format($ahmedNetProfit - $ahmedDefaultPartialVal, 2) }} ج</strong></span>
                                                @if($ahmedFixedWithdrawal > 0)
                                                    <span class="badge bg-light text-primary border"><i class="fa fa-lock fa-xs me-1"></i>مثبت: {{ number_format($ahmedFixedWithdrawal) }} ج</span>
                                                @endif
                                            </div>
                                            <select id="acc_partial_sel_ahmed" class="form-select form-select-sm fw-bold border-secondary treasury-select-partial" style="font-size:0.75rem;" data-pid="ahmed" data-name="{{ $headName }}" onchange="syncPartialAccount('ahmed')">
                                                <option value="">-- اختر خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($ahmedNetProfit < 0)
                                        <span class="badge-formal bg-danger-subtle text-danger border border-danger-subtle p-1 text-wrap d-block text-center">
                                            خصم العجز ({{ number_format($ahmedNetProfit, 2) }} ج)
                                        </span>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" value="0">
                                    @else
                                        <span class="badge-formal bg-light text-muted border p-1 d-block text-center">
                                            لا يوجد ربح للتوزيع (0.00 ج)
                                        </span>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- الرصيد الجديد بعد التقفيل لحساب الإدارة --}}
                                <td class="fw-bold" id="cell_cap_ahmed">
                                    <div class="text-dark fw-bold" id="new_cap_display_ahmed">
                                        {{ number_format($ahmedCloseCap, 2) }} ج
                                    </div>
                                    <small id="new_cap_sub_ahmed" class="d-block {{ $ahmedNetProfit > 0 ? 'text-success' : ($ahmedNetProfit < 0 ? 'text-danger' : 'text-muted') }}" style="font-size:0.7rem;">
                                        @if($ahmedNetProfit > 0)
                                            <i class="fa fa-arrow-up"></i> +{{ number_format($ahmedNetProfit, 2) }} ج
                                        @elseif($ahmedNetProfit < 0)
                                            <i class="fa fa-arrow-down"></i> {{ number_format($ahmedNetProfit, 2) }} ج
                                        @else
                                            ثابت (0.00 ج)
                                        @endif
                                    </small>
                                </td>

                                {{-- النسبة الجديدة بعد التقفيل (بقت كام) لحساب الإدارة --}}
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold" style="font-size:0.8rem;" id="new_pct_display_ahmed">
                                        {{ number_format($ahmedClosePct, 2) }}%
                                    </span>
                                    <div id="new_pct_diff_ahmed" class="mt-1" style="font-size:0.68rem;">
                                        @if($ahmedPctDiff > 0.0001)
                                            <span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> +{{ number_format($ahmedPctDiff, 2) }}%</span>
                                        @elseif($ahmedPctDiff < -0.0001)
                                            <span class="text-danger fw-bold"><i class="fa fa-arrow-down"></i> {{ number_format($ahmedPctDiff, 2) }}%</span>
                                        @else
                                            <span class="text-muted">ثابتة</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- أسطر باقي الشركاء --}}
                            @forelse($preview['partner_lines'] as $line)
                            @php $pid = $line['partner_id']; @endphp
                            <tr id="row_partner_{{ $pid }}"
                                data-pid="{{ $pid }}"
                                data-opencap="{{ $line['opening_capital'] }}"
                                data-openpct="{{ $line['capital_share_pct'] }}"
                                data-net="{{ $line['net_share'] }}"
                                data-newcap="{{ $line['closing_capital'] }}">
                                <td>
                                    <strong class="text-dark d-block">{{ $line['name'] }}</strong>
                                    <small class="text-muted" style="font-size:0.7rem;">{{ $line['phone'] ?: '—' }}</small>
                                </td>
                                <td class="fw-bold">{{ number_format($line['opening_capital'], 2) }} ج</td>
                                <td class="text-center">
                                    <span class="badge-formal bg-light text-secondary border">
                                        {{ number_format($line['capital_share_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="fw-bold {{ $line['gross_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['gross_share'] >= 0 ? '+' : '' }}{{ number_format($line['gross_share'], 2) }} ج
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark">+{{ number_format($line['ahmed_commission'], 2) }} ج</div>
                                    <span class="badge bg-light text-secondary border" style="font-size:0.65rem;">نسبة الشغل {{ number_format($line['ahmed_ratio_pct'], 0) }}%</span>
                                </td>
                                <td class="fw-bold {{ $line['net_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['net_share'] >= 0 ? '+' : '' }}{{ number_format($line['net_share'], 2) }} ج
                                </td>

                                {{-- عمود القرار والصرف المدمج للشريك --}}
                                <td>
                                    @if($line['net_share'] > 0)
                                        <div class="btn-group btn-group-sm w-100 mb-1" role="group">
                                            <button type="button" class="btn btn-outline-success btn-decision btn-decision-reinvest btn-decision-{{ $pid }} active" id="btn_opt_reinvest_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'reinvest', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                تدوير
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-decision btn-decision-withdraw-all btn-decision-{{ $pid }}" id="btn_opt_withdraw_all_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_all', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                سحب كامل
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-decision btn-decision-withdraw-partial btn-decision-{{ $pid }}" id="btn_opt_withdraw_partial_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_partial', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                سحب جزئي
                                            </button>
                                        </div>
                                        <input type="hidden" name="decisions[{{ $pid }}][payout_action]" id="input_action_{{ $pid }}" value="reinvest">
                                        <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" id="input_withdrawn_{{ $pid }}" value="0">

                                        <div id="box_reinvest_{{ $pid }}" class="payout-box-{{ $pid }} px-2 py-1 rounded bg-light text-success border text-center fw-bold" style="font-size:0.72rem;">
                                            <i class="fa fa-check-circle me-1"></i> تدوير بالرصيد (+{{ number_format($line['net_share'], 2) }} ج)
                                        </div>

                                        <div id="box_withdraw_all_{{ $pid }}" class="payout-box-{{ $pid }} mt-1" style="display:none;">
                                            <select name="decisions[{{ $pid }}][account_id]" id="acc_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-danger treasury-select" style="font-size:0.75rem;" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}">
                                                <option value="">-- اختر خزينة الصرف ({{ number_format($line['net_share'], 2) }} ج) --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @php
                                            $fixedWithdrawal = (float) ($line['monthly_fixed_withdrawal'] ?? 0);
                                            $defaultPartialVal = $fixedWithdrawal > 0 ? min($line['net_share'], $fixedWithdrawal) : 0;
                                        @endphp
                                        <div id="box_withdraw_partial_{{ $pid }}" class="payout-box-{{ $pid }} mt-1" style="display:none;">
                                            <div class="input-group input-group-sm mb-1">
                                                <input type="number" step="0.01" min="0" max="{{ $line['net_share'] }}" id="inp_partial_val_{{ $pid }}" data-fixed="{{ $fixedWithdrawal }}" class="form-control fw-bold text-danger" value="{{ $defaultPartialVal > 0 ? $defaultPartialVal : '' }}" placeholder="0.00" oninput="onPartialInput({{ $pid }}, {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                <span class="input-group-text bg-white fw-bold">ج</span>
                                            </div>
                                            <div class="text-muted fw-bold mb-1 d-flex justify-content-between align-items-center" style="font-size:0.7rem;" id="partial_rollover_badge_{{ $pid }}">
                                                <span>المدور: <strong class="text-success">{{ number_format($line['net_share'] - $defaultPartialVal, 2) }} ج</strong></span>
                                                @if($fixedWithdrawal > 0)
                                                    <span class="badge bg-light text-primary border" title="مسجل في بروفايل الشريك كمسحوب شهري مثبت"><i class="fa fa-lock fa-xs me-1"></i>مثبت: {{ number_format($fixedWithdrawal) }} ج</span>
                                                @endif
                                            </div>
                                            <select id="acc_partial_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-secondary treasury-select-partial" style="font-size:0.75rem;" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}" onchange="syncPartialAccount({{ $pid }})">
                                                <option value="">-- اختر خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($line['net_share'] < 0)
                                        <span class="badge-formal bg-danger-subtle text-danger border border-danger-subtle p-1 text-wrap d-block text-center">
                                            خصم الخسارة ({{ number_format($line['net_share'], 2) }} ج)
                                        </span>
                                        <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                    @else
                                        <span class="badge-formal bg-light text-muted border p-1 d-block text-center">
                                            لا يوجد ربح للتوزيع (0.00 ج)
                                        </span>
                                        <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- الرصيد الجديد المعتمد --}}
                                <td class="fw-bold" id="cell_cap_{{ $pid }}">
                                    <div class="text-dark fw-bold" id="new_cap_display_{{ $pid }}">
                                        {{ number_format($line['closing_capital'], 2) }} ج
                                    </div>
                                    <small id="new_cap_sub_{{ $pid }}" class="d-block {{ $line['net_share'] > 0 ? 'text-success' : ($line['net_share'] < 0 ? 'text-danger' : 'text-muted') }}" style="font-size:0.7rem;">
                                        @if($line['net_share'] > 0)
                                            <i class="fa fa-arrow-up"></i> +{{ number_format($line['net_share'], 2) }} ج
                                        @elseif($line['net_share'] < 0)
                                            <i class="fa fa-arrow-down"></i> {{ number_format($line['net_share'], 2) }} ج
                                        @else
                                            ثابت (0.00 ج)
                                        @endif
                                    </small>
                                </td>

                                {{-- النسبة الجديدة بعد التقفيل (بقت كام) --}}
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold" style="font-size:0.8rem;" id="new_pct_display_{{ $pid }}">
                                        {{ number_format($line['closing_share_pct'], 2) }}%
                                    </span>
                                    <div id="new_pct_diff_{{ $pid }}" class="mt-1" style="font-size:0.68rem;">
                                        @if($line['share_pct_diff'] > 0.0001)
                                            <span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> +{{ number_format($line['share_pct_diff'], 2) }}%</span>
                                        @elseif($line['share_pct_diff'] < -0.0001)
                                            <span class="text-danger fw-bold"><i class="fa fa-arrow-down"></i> {{ number_format($line['share_pct_diff'], 2) }}%</span>
                                        @else
                                            <span class="text-muted">ثابتة</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">لا يوجد شركاء نشطين لتوزيع الأرباح عليهم في هذه الفترة.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- شريط ملخص المسحوبات والتدوير قبل الحفظ والاعتماد --}}
                <div class="p-3 border-top border-2" style="background: #f8fafc; border-top-color: #cbd5e1 !important;" id="liveWithdrawalSummaryCard">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between shadow-sm" id="boxLiveWithdrawnBadge" style="background-color: #fff1f2; border-color: #fca5a5 !important;">
                                <div>
                                    <span class="d-block text-danger fw-bold small mb-1">
                                        <i class="fa fa-money-bill-wave me-1"></i> إجمالي ما سيتم سحبه وصرفه نقداً الآن:
                                    </span>
                                    <strong class="fs-3 text-danger font-monospace" id="live_total_withdrawn_display">0.00 ج</strong>
                                </div>
                                <span class="badge bg-secondary rounded-pill px-3 py-2 fw-bold" id="live_withdrawing_count_badge">
                                    0 مسحوبات
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between shadow-sm" style="background-color: #f0fdf4; border-color: #86efac !important;">
                                <div>
                                    <span class="d-block text-success fw-bold small mb-1">
                                        <i class="fa fa-sync-alt me-1"></i> إجمالي ما سيتم تدويره في رأس المال:
                                    </span>
                                    <strong class="fs-3 text-success font-monospace" id="live_total_reinvested_display">{{ number_format($preview['gross_profit'], 2) }} ج</strong>
                                </div>
                                <span class="badge bg-success rounded-pill px-3 py-2 fw-bold">مدوّر بالحسابات</span>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12">
                            <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between shadow-sm" style="background-color: #f0f9ff; border-color: #7dd3fc !important;">
                                <div>
                                    <span class="d-block text-primary fw-bold small mb-1">
                                        <i class="fa fa-vault me-1"></i> إجمالي رأس المال الجديد بعد التقفيل والصرف:
                                    </span>
                                    <strong class="fs-3 text-primary font-monospace" id="live_total_closing_capital_display">{{ number_format($preview['total_closing_shares'], 2) }} ج</strong>
                                </div>
                                <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold">الرصيد المعتمد</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light p-3 border-top">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <label class="form-label fw-bold small m-0 me-2 text-muted">
                                <i class="fa fa-file-alt text-secondary me-1"></i> ملاحظات التقفيل والاعتماد:
                            </label>
                            <input type="text" name="notes" class="form-control form-control-sm mt-1" placeholder="مثال: تم إقفال دورة الشهر وتحديث نسب الشركاء وحساب الإدارة...">
                        </div>
                        <div class="col-md-5 text-start d-flex align-items-center justify-content-end gap-2 flex-wrap">
                            <div class="text-end d-none d-sm-block me-2">
                                <small class="text-muted d-block" style="font-size:0.75rem;">المسحوب نقداً قبل الحفظ:</small>
                                <strong class="text-danger fs-6 font-monospace" id="footer_withdrawn_display">0.00 ج</strong>
                            </div>
                            <button type="submit" class="btn btn-dark fw-bold px-4 py-2 shadow-sm" id="btnSubmitClosing">
                                <i class="fa fa-check-circle me-1"></i> اعتماد التقفيل وتحديث النسب والهيستوري
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- ملخص أرباح حساب الإدارة للدورة --}}
        <div class="card-formal p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-light text-primary rounded border fs-4">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">ملخص حساب الإدارة ({{ $headName }})</h6>
                            <small class="text-muted">الأرباح من نسب الشغل + ربح الرصيد المدور</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">ربح الرصيد المدور في حساب الإدارة</small>
                    <strong class="fs-5 text-dark font-monospace">{{ number_format($preview['ahmed']['own_profit'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">نسبة الحساب قبل التقفيل: {{ number_format($preview['ahmed']['capital_share_pct'], 2) }}%</div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">الأرباح من نسب الشغل مع الشركاء</small>
                    <strong class="fs-5 text-primary font-monospace">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">مجموع نسب الإدارة من أرباح الشركاء</div>
                </div>

                <div class="col-md-3 text-center border-start bg-light py-3 rounded">
                    <small class="text-dark fw-bold d-block mb-1">إجمالي الدخل المضاف لحساب الإدارة</small>
                    <strong class="fs-4 text-success font-monospace">{{ number_format($preview['ahmed']['total_profit'], 2) }} ج</strong>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /**
     * تغيير قرار الشريك أو حساب الإدارة للدورة الحالية
     */
    function setPartnerDecision(pid, action, openCap, netShare) {
        document.querySelectorAll('.btn-decision-' + pid).forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('btn_opt_' + action + '_' + pid);
        if (activeBtn) activeBtn.classList.add('active');

        const inputAction = document.getElementById('input_action_' + pid);
        if (inputAction) inputAction.value = action;

        const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
        const accSel = document.getElementById('acc_sel_' + pid);
        const accPartialSel = document.getElementById('acc_partial_sel_' + pid);

        document.querySelectorAll('.payout-box-' + pid).forEach(box => box.style.display = 'none');

        let withdrawn = 0;
        let reinvested = netShare;

        if (action === 'withdraw_all') {
            const box = document.getElementById('box_withdraw_all_' + pid);
            if (box) box.style.display = 'block';
            withdrawn = netShare;
            reinvested = 0;
            if (inputWithdrawn) inputWithdrawn.value = withdrawn.toFixed(2);
            if (accSel) {
                accSel.name = 'decisions[' + pid + '][account_id]';
                accSel.required = true;
            }
            if (accPartialSel) {
                accPartialSel.name = '';
                accPartialSel.required = false;
            }
        } else if (action === 'withdraw_partial') {
            const box = document.getElementById('box_withdraw_partial_' + pid);
            if (box) box.style.display = 'block';
            const inp = document.getElementById('inp_partial_val_' + pid);
            const fixedVal = parseFloat(inp ? inp.getAttribute('data-fixed') : 0) || 0;
            let val = parseFloat(inp && inp.value !== '' ? inp.value : (fixedVal > 0 ? Math.min(netShare, fixedVal) : 0));
            if (isNaN(val) || val < 0) val = 0;
            if (val > netShare) val = netShare;
            if (inp && inp.value === '') {
                inp.value = val > 0 ? val : '';
            }
            withdrawn = val;
            reinvested = netShare - withdrawn;
            if (inputWithdrawn) inputWithdrawn.value = withdrawn.toFixed(2);

            const badge = document.getElementById('partial_rollover_badge_' + pid);
            if (badge) {
                const fixedBadge = fixedVal > 0 ? `<span class="badge bg-light text-primary border"><i class="fa fa-lock fa-xs me-1"></i>مثبت: ${Number(fixedVal).toLocaleString()} ج</span>` : '';
                badge.innerHTML = `<span>المدور: <strong class="text-success">${Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2})} ج</strong></span> ${fixedBadge}`;
            }

            if (accSel) {
                accSel.name = '';
                accSel.required = false;
            }
            if (accPartialSel) {
                accPartialSel.name = 'decisions[' + pid + '][account_id]';
                accPartialSel.required = true;
            }
            if (accSel && accPartialSel && accPartialSel.value) {
                accSel.value = accPartialSel.value;
            }
        } else {
            // reinvest
            const box = document.getElementById('box_reinvest_' + pid);
            if (box) box.style.display = 'block';
            withdrawn = 0;
            reinvested = netShare;
            if (inputWithdrawn) inputWithdrawn.value = '0';
            if (accSel) {
                accSel.name = 'decisions[' + pid + '][account_id]';
                accSel.required = false;
                accSel.value = '';
            }
            if (accPartialSel) {
                accPartialSel.name = '';
                accPartialSel.required = false;
                accPartialSel.value = '';
            }
        }

        updateNewCapDisplay(pid, openCap, reinvested, action);
    }

    /**
     * تحديث فوري عند إدخال قيمة السحب الجزئي
     */
    function onPartialInput(pid, openCap, netShare) {
        const inp = document.getElementById('inp_partial_val_' + pid);
        let val = parseFloat(inp && inp.value !== '' ? inp.value : 0) || 0;
        if (val > netShare) {
            val = netShare;
            if (inp) inp.value = val;
        }
        if (val < 0) {
            val = 0;
            if (inp) inp.value = 0;
        }

        const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
        if (inputWithdrawn) inputWithdrawn.value = val.toFixed(2);

        const rem = netShare - val;
        const badge = document.getElementById('partial_rollover_badge_' + pid);
        const fixedVal = parseFloat(inp ? inp.getAttribute('data-fixed') : 0) || 0;
        const fixedBadge = fixedVal > 0 ? `<span class="badge bg-light text-primary border"><i class="fa fa-lock fa-xs me-1"></i>مثبت: ${Number(fixedVal).toLocaleString()} ج</span>` : '';
        if (badge) {
            badge.innerHTML = `<span>المدور: <strong class="text-success">${Number(rem).toLocaleString('en-US', {minimumFractionDigits: 2})} ج</strong></span> ${fixedBadge}`;
        }

        updateNewCapDisplay(pid, openCap, rem, 'withdraw_partial');
    }

    function syncPartialAccount(pid) {
        const accPartialSel = document.getElementById('acc_partial_sel_' + pid);
        const accSel = document.getElementById('acc_sel_' + pid);
        if (accSel && accPartialSel) {
            accSel.value = accPartialSel.value;
        }
    }

    function setAllDecisions(action) {
        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const openCap = parseFloat(tr.getAttribute('data-opencap')) || 0;
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            if (netShare > 0) {
                setPartnerDecision(pid, action, openCap, netShare);
            }
        });
    }

    function updateNewCapDisplay(pid, openCap, reinvested, action) {
        const newCap = Math.max(0, openCap + reinvested);
        const tr = document.getElementById('row_partner_' + pid);
        if (tr) {
            tr.setAttribute('data-newcap', newCap.toFixed(2));
        }

        const capDisplay = document.getElementById('new_cap_display_' + pid);
        const capSub = document.getElementById('new_cap_sub_' + pid);

        if (capDisplay) {
            capDisplay.innerText = Number(newCap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (capSub) {
            if (action === 'reinvest') {
                capSub.className = 'd-block text-success';
                capSub.innerHTML = '<i class="fa fa-arrow-up me-1"></i> زيادة (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            } else if (action === 'withdraw_all') {
                capSub.className = 'd-block text-muted';
                capSub.innerHTML = '<i class="fa fa-equals me-1"></i> ثبات الرصيد (سحب كامل الربح)';
            } else {
                capSub.className = 'd-block text-primary';
                capSub.innerHTML = '<i class="fa fa-arrow-up me-1"></i> زيادة بالمدور (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            }
        }

        // إعادة حساب جميع النسب بعد التقفيل (بقت كام %) لحظياً بناءً على إجمالي الأرصدة الجديدة
        recalcAllClosingSharePcts();

        // تحديث شريط ملخص المسحوبات والتدوير اللحظي قبل الاعتماد
        updateWithdrawalSummary();
    }

    function recalcAllClosingSharePcts() {
        let totalNewCap = 0;
        const rows = document.querySelectorAll('tr[data-pid]');
        rows.forEach(tr => {
            totalNewCap += parseFloat(tr.getAttribute('data-newcap')) || 0;
        });

        rows.forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const newCap = parseFloat(tr.getAttribute('data-newcap')) || 0;
            const openPct = parseFloat(tr.getAttribute('data-openpct')) || 0;
            const newPct = totalNewCap > 0 ? (newCap / totalNewCap) * 100 : 0;
            const diff = newPct - openPct;

            const pctEl = document.getElementById('new_pct_display_' + pid);
            const diffEl = document.getElementById('new_pct_diff_' + pid);

            if (pctEl) {
                pctEl.innerText = newPct.toFixed(2) + '%';
            }
            if (diffEl) {
                if (diff > 0.005) {
                    diffEl.innerHTML = '<span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> +' + diff.toFixed(2) + '%</span>';
                } else if (diff < -0.005) {
                    diffEl.innerHTML = '<span class="text-danger fw-bold"><i class="fa fa-arrow-down"></i> ' + diff.toFixed(2) + '%</span>';
                } else {
                    diffEl.innerHTML = '<span class="text-muted">ثابتة</span>';
                }
            }
        });
    }

    /**
     * تحديث شريط ملخص المسحوبات والتدوير اللحظي
     */
    function updateWithdrawalSummary() {
        let totalWithdrawn = 0;
        let totalNetProfit = 0;
        let withdrawingCount = 0;
        let totalNewCap = 0;

        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            const newCap = parseFloat(tr.getAttribute('data-newcap')) || 0;
            const actionInp = document.getElementById('input_action_' + pid);
            const action = actionInp ? actionInp.value : 'reinvest';
            const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
            const withdrawnVal = parseFloat(inputWithdrawn ? inputWithdrawn.value : 0) || 0;

            if (netShare > 0) {
                totalNetProfit += netShare;
                if (action === 'withdraw_all' || action === 'withdraw_partial') {
                    if (withdrawnVal > 0) {
                        totalWithdrawn += withdrawnVal;
                        withdrawingCount++;
                    }
                }
            }
            totalNewCap += newCap;
        });

        const totalReinvested = Math.max(0, totalNetProfit - totalWithdrawn);

        const elWithdrawn = document.getElementById('live_total_withdrawn_display');
        const elReinvested = document.getElementById('live_total_reinvested_display');
        const elNewCap = document.getElementById('live_total_closing_capital_display');
        const elBadge = document.getElementById('live_withdrawing_count_badge');
        const elFooterWithdrawn = document.getElementById('footer_withdrawn_display');
        const boxBadge = document.getElementById('boxLiveWithdrawnBadge');

        if (elWithdrawn) {
            elWithdrawn.innerText = Number(totalWithdrawn).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (elFooterWithdrawn) {
            elFooterWithdrawn.innerText = Number(totalWithdrawn).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (elReinvested) {
            elReinvested.innerText = Number(totalReinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (elNewCap) {
            elNewCap.innerText = Number(totalNewCap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (elBadge) {
            elBadge.innerText = withdrawingCount + (withdrawingCount === 1 ? ' عملية سحب' : ' عمليات سحب');
            if (totalWithdrawn > 0) {
                elBadge.className = 'badge bg-danger rounded-pill px-3 py-2 fw-bold';
            } else {
                elBadge.className = 'badge bg-secondary rounded-pill px-3 py-2 fw-bold';
            }
        }
        if (boxBadge) {
            if (totalWithdrawn > 0) {
                boxBadge.style.backgroundColor = '#fff1f2';
                boxBadge.style.borderColor = '#f43f5e';
            } else {
                boxBadge.style.backgroundColor = '#f8fafc';
                boxBadge.style.borderColor = '#e2e8f0';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateWithdrawalSummary();
    });

    document.getElementById('formExecuteClosing').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = this;

        let hasError = false;
        let errorMsg = '';
        let totalWithdrawn = 0;
        let totalNetProfit = 0;
        let totalNewCap = 0;
        const withdrawnItems = [];

        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            const newCap = parseFloat(tr.getAttribute('data-newcap')) || 0;
            totalNewCap += newCap;

            if (netShare <= 0) return;
            totalNetProfit += netShare;

            const actionInp = document.getElementById('input_action_' + pid);
            const action = actionInp ? actionInp.value : 'reinvest';
            const partnerName = tr.querySelector('strong') ? tr.querySelector('strong').innerText.trim() : 'المحدد';

            if (action === 'withdraw_all') {
                const accSel = document.getElementById('acc_sel_' + pid);
                if (!accSel || !accSel.value) {
                    errorMsg = 'يرجى تحديد خزينة الصرف لصرف أرباح «' + partnerName + '» (سحب كامل).';
                    hasError = true;
                    if (accSel) accSel.focus();
                } else {
                    const accName = accSel.options[accSel.selectedIndex] ? accSel.options[accSel.selectedIndex].text : '';
                    totalWithdrawn += netShare;
                    withdrawnItems.push({ name: partnerName, amount: netShare, accountName: accName, type: 'سحب كامل' });
                }
            } else if (action === 'withdraw_partial') {
                const accPartialSel = document.getElementById('acc_partial_sel_' + pid);
                const accSel = document.getElementById('acc_sel_' + pid);
                const selectedVal = (accPartialSel && accPartialSel.value) || (accSel && accSel.value);
                const inp = document.getElementById('inp_partial_val_' + pid);
                const partialVal = parseFloat(inp ? inp.value : 0) || 0;

                if (!selectedVal) {
                    errorMsg = 'يرجى تحديد خزينة الصرف لصرف حصة «' + partnerName + '» (سحب جزئي).';
                    hasError = true;
                    if (accPartialSel) accPartialSel.focus();
                } else {
                    const targetSel = (accPartialSel && accPartialSel.value) ? accPartialSel : accSel;
                    const accName = targetSel.options[targetSel.selectedIndex] ? targetSel.options[targetSel.selectedIndex].text : '';
                    totalWithdrawn += partialVal;
                    withdrawnItems.push({ name: partnerName, amount: partialVal, accountName: accName, type: 'سحب جزئي' });
                }
            }
        });

        if (hasError) {
            alert(errorMsg);
            return false;
        }

        const totalReinvested = Math.max(0, totalNetProfit - totalWithdrawn);

        let breakdownHtml = '';
        if (withdrawnItems.length > 0) {
            breakdownHtml = `
            <div class="mt-3 text-start p-2 bg-light rounded border text-end" style="max-height: 190px; overflow-y: auto; font-size: 0.82rem;">
                <div class="fw-bold text-danger mb-2 border-bottom pb-1 d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-list me-1"></i> تفاصيل الحسابات التي سيتم الصرف منها:</span>
                    <span class="badge bg-danger">${withdrawnItems.length} عمليات</span>
                </div>
                ${withdrawnItems.map(item => `
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                        <div>
                            <strong class="text-dark">${item.name}</strong>
                            <small class="text-muted d-block" style="font-size:0.72rem;">${item.accountName} (${item.type})</small>
                        </div>
                        <span class="text-danger fw-black font-monospace fs-6">-${item.amount.toLocaleString('en-US', {minimumFractionDigits: 2})} ج</span>
                    </div>
                `).join('')}
            </div>`;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'تأكيد إجمالي الصرف والاعتماد',
                html: `
                    <div class="text-center">
                        <div class="p-3 mb-3 rounded border ${totalWithdrawn > 0 ? 'bg-danger-subtle border-danger-subtle' : 'bg-success-subtle border-success-subtle'}">
                            <span class="d-block small text-muted fw-bold mb-1">
                                <i class="fa fa-money-bill-wave me-1"></i> إجمالي المبلغ الذي سيتم سحبه وصرفه نقداً الآن:
                            </span>
                            <h2 class="fw-black ${totalWithdrawn > 0 ? 'text-danger' : 'text-success'} m-0 font-monospace">
                                ${totalWithdrawn.toLocaleString('en-US', {minimumFractionDigits: 2})} ج
                            </h2>
                            ${totalWithdrawn === 0 ? '<small class="text-success fw-bold d-block mt-1">كامل الأرباح ستُدوّر وتُضاف لرؤوس الأموال</small>' : ''}
                        </div>

                        <div class="row g-2 text-muted small fw-bold text-center">
                            <div class="col-6 p-2 bg-light rounded border">
                                <span>إجمالي المدوّر بالحسابات:</span>
                                <div class="text-success fs-6 fw-black font-monospace mt-1">+${totalReinvested.toLocaleString('en-US', {minimumFractionDigits: 2})} ج</div>
                            </div>
                            <div class="col-6 p-2 bg-light rounded border">
                                <span>رأس المال الجديد بعد التقفيل:</span>
                                <div class="text-primary fs-6 fw-black font-monospace mt-1">${totalNewCap.toLocaleString('en-US', {minimumFractionDigits: 2})} ج</div>
                            </div>
                        </div>

                        ${breakdownHtml}

                        <div class="mt-3 text-muted small">
                            <i class="fa fa-shield-alt text-success me-1"></i> سيتم خصم المسحوبات من الخزائن المحددة وترحيل الحصص الجديدة في الهيستوري فوراً.
                        </div>
                    </div>
                `,
                icon: totalWithdrawn > 0 ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa fa-check me-1"></i> تأكيد وصرف المبالغ واعتماد الإقفال',
                cancelButtonText: 'مراجعة وتعديل',
                reverseButtons: true,
                width: 580
            }).then((res) => {
                if (res.isConfirmed) {
                    form.submit();
                }
            });
        } else {
            const confirmMsg = `تأكيد الإقفال والصرف:\n\n` +
                               `• إجمالي ما سيتم سحبه وصرفه نقداً من الخزائن: ${totalWithdrawn.toLocaleString('en-US', {minimumFractionDigits: 2})} ج\n` +
                               `• إجمالي ما سيتم تدويره في رأس المال: ${totalReinvested.toLocaleString('en-US', {minimumFractionDigits: 2})} ج\n\n` +
                               `هل تريد تأكيد الاعتماد النهائي؟`;
            if (confirm(confirmMsg)) {
                form.submit();
            }
        }
    });
</script>

</body>
</html>
