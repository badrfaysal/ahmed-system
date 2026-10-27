<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير إقفال الدورة: {{ $period->period_name }} - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            height: 100%;
            border-top: 3px solid var(--slate-700);
        }
        .kpi-formal.accent-primary { border-top-color: var(--brand-primary); }
        .kpi-formal.accent-amber   { border-top-color: var(--brand-amber); }
        .kpi-formal.accent-success { border-top-color: var(--brand-success); }
        .kpi-formal.accent-slate   { border-top-color: var(--slate-700); }

        .kpi-formal .title { font-size: 0.78rem; font-weight: 700; color: var(--slate-600); margin-bottom: 6px; }
        .kpi-formal .val   { font-size: 1.25rem; font-weight: 800; font-variant-numeric: tabular-nums; line-height: 1.3; }
        .table-responsive {
            overflow-x: visible !important;
        }
        .table-formal {
            margin: 0;
            width: 100% !important;
            table-layout: auto;
            vertical-align: middle;
            font-size: 0.81rem;
        }
        .table-formal thead th {
            background: var(--slate-800);
            color: #ffffff;
            font-size: 0.74rem;
            font-weight: 700;
            padding: 9px 5px;
            border: none;
            text-align: center;
            vertical-align: middle;
            white-space: normal;
            line-height: 1.3;
        }
        .table-formal tbody td {
            padding: 8px 5px;
            border-bottom: 1px solid var(--slate-200);
            font-variant-numeric: tabular-nums;
            vertical-align: middle;
        }

        .print-only { display: none; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 5mm 10mm 5mm;
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
                font-size: 7.2pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, th.no-print, td.no-print, button, .btn, .card-footer, .modal, .pagination {
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
                font-size: 6.8pt !important;
                table-layout: auto !important;
                margin-bottom: 6px !important;
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
                padding: 4px 1.5px !important;
                font-size: 6.3pt !important;
                font-weight: 700 !important;
                text-align: center !important;
                white-space: normal !important;
                line-height: 1.15 !important;
            }
            .table-formal tbody td {
                border: 1px solid #94a3b8 !important;
                padding: 3px 1.5px !important;
                font-size: 6.8pt !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .table-formal tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            .badge {
                border: 1px solid #475569 !important;
                color: #000000 !important;
                background: #f1f5f9 !important;
                font-weight: 700 !important;
                padding: 1px 2px !important;
                font-size: 6.2pt !important;
            }
            .print-kpi-grid {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 5px !important;
                margin-bottom: 8px !important;
            }
            .print-kpi-box {
                border: 1px solid #cbd5e1 !important;
                padding: 5px 6px !important;
                background: #f8fafc !important;
                text-align: center !important;
                border-radius: 4px !important;
            }
            .print-kpi-box .lbl {
                font-size: 6.8pt !important;
                color: #475569 !important;
                font-weight: 700 !important;
                margin-bottom: 2px !important;
            }
            .print-kpi-box .num {
                font-size: 9pt !important;
                font-weight: 800 !important;
                color: #0f172a !important;
            }
            .print-signatures-block {
                margin-top: 14px !important;
                page-break-inside: avoid !important;
            }
            .print-sig-col {
                border: 1px dashed #64748b !important;
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

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة المطبوعة الرسمية (تظهر فقط عند الطباعة) --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">وحدة الحسابات والرقابة المالية وإدارة الحصص</small>
                    <span class="badge bg-dark text-white mt-1">كود الوثيقة: PRD-{{ str_pad($period->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">تقرير اعتماد إقفال الدورة وتوزيع الأرباح وتغير النسب</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">{{ $period->period_name }}</div>
                    <small class="text-muted">الفترة المحاسبية: من <strong>{{ $period->start_date }}</strong> إلى <strong>{{ $period->end_date }}</strong></small>
                </div>
                <div class="text-start">
                    <div class="badge bg-success text-white px-2 py-1 mb-1">معتمدة ومرحلة بالدفاتر</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الاعتماد: {{ date('Y-m-d H:i', strtotime($period->created_at)) }}</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- الترويسة والشاشة والتحكم (تختفي في الطباعة) --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة إلى سجل الشركاء
                </a>
                <h4 class="fw-bold m-0 d-flex align-items-center gap-2 text-dark">
                    <i class="fa fa-file-invoice-dollar text-primary"></i> تقرير إقفال الدورة: {{ $period->period_name }}
                    <span class="badge bg-success ms-2" style="font-size:0.75rem;">معتمدة ومغلقة</span>
                </h4>
                <small class="text-muted">
                    الفترة من <strong>{{ $period->start_date }}</strong> إلى <strong>{{ $period->end_date }}</strong>
                    · تم الاعتماد في: {{ date('Y-m-d H:i', strtotime($period->created_at)) }}
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-dark btn-sm fw-bold px-3 py-2 shadow-sm" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> طباعة التقرير المالي الرسمي (A4)
                </button>
            </div>
        </div>

        {{-- ملخص المؤشرات للشاشة --}}
        <div class="no-print">
            <div class="row g-2 mb-4">
                <div class="col-md-2">
                    <div class="kpi-formal accent-primary">
                        <div class="title"><i class="fa fa-vault text-primary me-1"></i> رأس مال البداية</div>
                        <div class="val text-dark">{{ number_format($period->opening_capital, 2) }} ج</div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="kpi-formal accent-slate">
                        <div class="title"><i class="fa fa-building text-secondary me-1"></i> رأس مال الإقفال</div>
                        <div class="val text-dark">{{ number_format($period->closing_capital, 2) }} ج</div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="kpi-formal accent-success">
                        <div class="title"><i class="fa fa-calculator text-success me-1"></i> الربح التشغيلي (R)</div>
                        <div class="val {{ $period->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $period->gross_profit >= 0 ? '+' : '' }}{{ number_format($period->gross_profit, 2) }} ج
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="kpi-formal accent-primary">
                        <div class="title"><i class="fa fa-users text-primary me-1"></i> صافي ربح الشركاء</div>
                        <div class="val text-dark">{{ number_format($period->total_partners_net_profit, 2) }} ج</div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="kpi-formal accent-amber">
                        <div class="title"><i class="fa fa-user-tie text-warning me-1"></i> إجمالي ربح حساب الإدارة</div>
                        <div class="val text-primary">+{{ number_format($period->ahmed_total_profit ?: $period->total_ahmed_commission, 2) }} ج</div>
                        <small class="text-muted d-block mt-1" style="font-size:0.7rem;">نسب الشغل: {{ number_format($period->total_ahmed_commission, 2) }} ج</small>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="kpi-formal accent-success">
                        <div class="title"><i class="fa fa-sync-alt text-success me-1"></i> المدوّر بالحسابات</div>
                        <div class="val text-success">{{ number_format($period->total_reinvested, 2) }} ج</div>
                        <small class="text-danger d-block mt-1" style="font-size:0.7rem;">مسحوب نقداً: {{ number_format($period->total_withdrawn, 2) }} ج</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ملخص المؤشرات المطبوع (A4 Landscape Strip) --}}
        <div class="print-only">
            <div class="print-kpi-grid">
                <div class="print-kpi-box">
                    <div class="lbl">1. رأس مال البداية</div>
                    <div class="num">{{ number_format($period->opening_capital, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">2. رأس مال الإقفال</div>
                    <div class="num">{{ number_format($period->closing_capital, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">3. صافي الربح (R)</div>
                    <div class="num">{{ $period->gross_profit >= 0 ? '+' : '' }}{{ number_format($period->gross_profit, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">4. صافي ربح الشركاء</div>
                    <div class="num">{{ number_format($period->total_partners_net_profit, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">5. إجمالي ربح حساب الإدارة</div>
                    <div class="num">+{{ number_format($period->ahmed_total_profit ?: $period->total_ahmed_commission, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">6. المدور / المسحوب نقداً</div>
                    <div class="num" style="font-size:8pt !important;">
                        مدور: {{ number_format($period->total_reinvested, 2) }} ج<br>
                        <span style="color:#b91c1c;">سحب: {{ number_format($period->total_withdrawn, 2) }} ج</span>
                    </div>
                </div>
            </div>
        </div>

        @if($period->notes)
        <div class="alert alert-secondary py-2 px-3 small rounded mb-3" style="font-size:0.8rem;">
            <i class="fa fa-file-alt me-1"></i> <strong>ملاحظات التقفيل والاعتماد:</strong> {{ $period->notes }}
        </div>
        @endif

        {{-- جدول تفاصيل حصص كل شريك ومقارنة النسب قبل وبعد التقفيل --}}
        <div class="card-formal mb-3">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2" style="font-size:0.88rem;">
                    <i class="fa fa-list-check text-primary"></i> بيان توزيع الأرباح وتغير النسب (قبل التقفيل ← بعد التقفيل) للشركاء وحساب الإدارة
                </h6>
                <small class="text-muted">المبالغ بالجنيه المصري (EGP)</small>
            </div>

            <div>
                <table class="table table-formal table-hover m-0">
                    <thead>
                        <tr>
                            <th style="width: 28px;" class="text-center">#</th>
                            <th style="text-align: right;">الشريك / الحساب</th>
                            <th class="text-center">الرصيد قبل التقفيل</th>
                            <th class="text-center" style="background:#334155;">النسبة كانت كام</th>
                            <th class="text-center">ربح الحصة (g)</th>
                            <th class="text-center">نسبة وأرباح الشغل</th>
                            <th class="text-center">صافي الربح (n)</th>
                            <th class="text-center">القرار والخزينة</th>
                            <th class="text-center">المسحوب نقداً</th>
                            <th class="text-center">المدوّر بالرصيد</th>
                            <th class="text-center">الرصيد بعد التقفيل</th>
                            <th class="text-center" style="background:#1e40af;">النسبة بقت كام</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($period->ahmed_opening_capital))
                        @php
                            $ahmedOpenPct  = (float) $period->ahmed_capital_share_pct;
                            $ahmedClosePct = (float) ($period->ahmed_closing_share_pct ?? 0);
                            $ahmedPctDiff  = round($ahmedClosePct - $ahmedOpenPct, 4);
                        @endphp
                        <tr style="background-color: #f8fafc; font-weight: 600; border-bottom: 2px solid #cbd5e1;">
                            <td class="text-center fw-bold">—</td>
                            <td>
                                <strong class="text-dark d-block">{{ $headName }}</strong>
                                <span class="badge bg-dark text-white" style="font-size:0.65rem;">حساب الإدارة</span>
                            </td>
                            <td class="text-center">{{ number_format($period->ahmed_opening_capital, 2) }} ج</td>
                            <td class="text-center">
                                <span class="badge bg-light text-secondary border fw-bold">
                                    {{ number_format($ahmedOpenPct, 2) }}%
                                </span>
                            </td>
                            <td class="text-center {{ $period->ahmed_own_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $period->ahmed_own_profit >= 0 ? '+' : '' }}{{ number_format($period->ahmed_own_profit, 2) }} ج
                            </td>
                            <td class="text-center">
                                <div class="text-primary fw-bold">+{{ number_format($period->ahmed_commission, 2) }} ج</div>
                                <small class="text-muted d-block" style="font-size:0.68rem;">حساب الإدارة</small>
                            </td>
                            <td class="text-center fw-bold {{ $period->ahmed_total_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $period->ahmed_total_profit >= 0 ? '+' : '' }}{{ number_format($period->ahmed_total_profit, 2) }} ج
                            </td>
                            <td class="text-center">
                                @if($period->ahmed_payout_action === 'reinvest')
                                    <span class="badge bg-light text-success border">تدوير بالحساب</span>
                                @elseif($period->ahmed_payout_action === 'withdraw_all')
                                    <span class="badge bg-light text-primary border">سحب كامل</span>
                                @else
                                    <span class="badge bg-light text-dark border">سحب جزئي</span>
                                @endif
                                @if(!empty($ahmedPayoutAccountName))
                                    <small class="d-block text-muted mt-1" style="font-size:0.68rem;">{{ $ahmedPayoutAccountName }}</small>
                                @endif
                            </td>
                            <td class="text-center text-danger">
                                {{ $period->ahmed_withdrawn_amount > 0 ? number_format($period->ahmed_withdrawn_amount, 2) . ' ج' : '—' }}
                            </td>
                            <td class="text-center text-success">
                                {{ $period->ahmed_reinvested_amount != 0 ? '+' . number_format($period->ahmed_reinvested_amount, 2) . ' ج' : '—' }}
                            </td>
                            <td class="text-center text-dark fw-bold">{{ number_format($period->ahmed_closing_capital, 2) }} ج</td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                    {{ number_format($ahmedClosePct, 2) }}%
                                </span>
                                @if(abs($ahmedPctDiff) > 0.0001)
                                    <small class="d-block {{ $ahmedPctDiff > 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.68rem;">
                                        {{ $ahmedPctDiff > 0 ? '+' : '' }}{{ number_format($ahmedPctDiff, 2) }}%
                                    </small>
                                @endif
                            </td>
                        </tr>
                        @endif

                        @forelse($lines as $l)
                        @php
                            $openPct  = (float) $l->capital_share_pct;
                            $closePct = (float) ($l->closing_share_pct ?? 0);
                            $pctDiff  = round($closePct - $openPct, 4);
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $l->partner_name }}</strong>
                                <small class="text-muted" style="font-size:0.7rem;">{{ $l->partner_phone ?: '—' }}</small>
                            </td>
                            <td class="text-center">{{ number_format($l->opening_capital, 2) }} ج</td>
                            <td class="text-center">
                                <span class="badge bg-light text-secondary border fw-bold">
                                    {{ number_format($openPct, 2) }}%
                                </span>
                            </td>
                            <td class="text-center {{ $l->gross_share >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $l->gross_share >= 0 ? '+' : '' }}{{ number_format($l->gross_share, 2) }} ج
                            </td>
                            <td class="text-center">
                                <div class="text-primary fw-bold">+{{ number_format($l->ahmed_commission, 2) }} ج</div>
                                <span class="badge bg-light text-dark border" style="font-size:0.65rem;">{{ number_format($l->ahmed_ratio_pct, 0) }}%</span>
                            </td>
                            <td class="text-center fw-bold {{ $l->net_share >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $l->net_share >= 0 ? '+' : '' }}{{ number_format($l->net_share, 2) }} ج
                            </td>
                            <td class="text-center">
                                @if($l->payout_action === 'reinvest')
                                    <span class="badge bg-light text-success border">تدوير بالرصيد</span>
                                @elseif($l->payout_action === 'withdraw_all')
                                    <span class="badge bg-light text-primary border">سحب كامل</span>
                                @else
                                    <span class="badge bg-light text-dark border">سحب جزئي</span>
                                @endif
                                @if(!empty($l->payout_account_name))
                                    <small class="d-block text-muted mt-1" style="font-size:0.68rem;">{{ $l->payout_account_name }}</small>
                                @endif
                            </td>
                            <td class="text-center text-danger">
                                {{ $l->withdrawn_amount > 0 ? number_format($l->withdrawn_amount, 2) . ' ج' : '—' }}
                            </td>
                            <td class="text-center text-success">
                                {{ $l->reinvested_amount != 0 ? '+' . number_format($l->reinvested_amount, 2) . ' ج' : '—' }}
                            </td>
                            <td class="text-center text-dark fw-bold">{{ number_format($l->closing_capital, 2) }} ج</td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                    {{ number_format($closePct, 2) }}%
                                </span>
                                @if(abs($pctDiff) > 0.0001)
                                    <small class="d-block {{ $pctDiff > 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.68rem;">
                                        {{ $pctDiff > 0 ? '+' : '' }}{{ number_format($pctDiff, 2) }}%
                                    </small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">لا توجد بيانات تفصيلية لهذه الدورة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- خانات التوقيع والاعتماد الرسمية للطباعة --}}
        <div class="print-only print-signatures-block">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">إعداد وتدقيق الحسابات</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">وحدة الإدارة المالية</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">المدير المالي التنفيذي</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">المطابقة وصرف الخزائن</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع والختم: ............................</div>
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
            </div>
            <div class="text-center text-muted mt-2 pt-2 border-top" style="font-size:7pt;">
                وثيقة مالية رسمية معتمدة ومرحلة بالدفاتر المحاسبية والخزائن الإلكترونية لشركة الضبع · صالحة للتدقيق والمطابقة
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
