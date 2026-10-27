<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقرير المالي لحساب الإدارة: {{ $headName }} - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        .hero-formal {
            background: var(--slate-900);
            border: 1px solid var(--slate-800);
            border-radius: 8px;
            color: #ffffff;
            padding: 24px;
            margin-bottom: 24px;
        }
        .stat-box-formal {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            padding: 14px 16px;
            height: 100%;
        }
        .stat-box-formal .title {
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .stat-box-formal .val {
            font-size: 1.45rem;
            font-weight: 800;
            color: #ffffff;
            font-variant-numeric: tabular-nums;
        }
        .chart-formal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            margin-bottom: 24px;
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
            background-color: #1e293b;
            color: #93c5fd;
            border-color: #3b82f6 !important;
        }
        .partners-tabs-bar .nav-link.tab-btn-periods.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #93c5fd !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.45);
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
                font-size: 7.8pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, th.no-print, td.no-print, button, .btn, .chart-formal, canvas, .card-footer, .pagination, .partners-tabs-bar {
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
            .hero-formal {
                background: #0f172a !important;
                color: #ffffff !important;
                border: 1px solid #000 !important;
                padding: 10px !important;
                margin-bottom: 10px !important;
            }
            .hero-formal p { display: none !important; }
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

        {{-- الترويسة المطبوعة الرسمية --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">وحدة الحسابات والرقابة المالية وإدارة الحصص</small>
                    <span class="badge bg-dark text-white mt-1">حساب الإدارة وأرباح الشغل</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">التقرير المالي الشامل لحساب الإدارة</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">{{ $headName }}</div>
                    <small class="text-muted">نسبة الحساب في الدورة الحالية: <strong>{{ number_format($ahmedSharePct, 2) }}%</strong></small>
                </div>
                <div class="text-start">
                    <div class="badge bg-dark text-white px-2 py-1 mb-1">تقرير رسمي معتمد</div>
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
                    <i class="fa fa-chart-pie text-primary"></i> التقرير المالي لحساب الإدارة: {{ $headName }}
                </h4>
                <small class="text-muted">متابعة أرباح الإدارة من نسب الشغل مع الشركاء وتدوير الأرباح للمشاركة في الدورات التالية</small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-dark btn-sm fw-bold px-3 py-2 shadow-sm" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> طباعة التقرير المالي
                </button>
                <span class="badge bg-light text-dark border px-3 py-2 small fw-bold">
                    <i class="fa fa-briefcase text-primary me-1"></i> حساب الإدارة (نسب الشغل والأرباح المدورة)
                </span>
            </div>
        </div>

        {{-- الملخص التنفيذي لحساب الإدارة --}}
        <div class="hero-formal">
            <div class="row g-3 align-items-center mb-3">
                <div class="col-md-8">
                    <h5 class="fw-bold mb-1 text-white">آلية عمل حساب الإدارة ({{ $headName }})</h5>
                    <p class="text-light opacity-75 small m-0">
                        يدخل في حساب الإدارة الأرباح الناتجة من نسب الشغل المتفق عليها مع الشركاء عند إقفال كل دورة. وفي حالة عدم سحب هذه الأرباح، تزيد أرقام الحساب وتدخل كحصة معتمدة تأخذ نصيبها من أرباح الدورة التالية إلى جانب نسب الشغل الجديدة.
                    </p>
                </div>
                <div class="col-md-4 text-md-start">
                    <div class="d-inline-flex flex-column align-items-md-end">
                        <span class="small text-muted mb-1 text-light">نسبة حصة حساب الإدارة في الدورة الحالية:</span>
                        <span class="fs-2 fw-bold text-warning font-monospace">{{ number_format($ahmedSharePct, 2) }}%</span>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-3">
                    <div class="stat-box-formal">
                        <div class="title"><i class="fa fa-vault me-1 text-info"></i> رصيد حساب الإدارة الحالي</div>
                        <div class="val text-info">{{ number_format($ahmedCapital, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-muted" style="font-size:0.72rem;">من إجمالي الحصص المعتمدة {{ number_format($totalApprovedCapital, 2) }} ج</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-box-formal">
                        <div class="title"><i class="fa fa-hand-holding-dollar me-1 text-warning"></i> الأرباح من نسب الشغل</div>
                        <div class="val text-warning">+{{ number_format($totalCommissions, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-muted" style="font-size:0.72rem;">مجموع نسب الإدارة من أرباح الشركاء</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-box-formal">
                        <div class="title"><i class="fa fa-coins me-1 text-success"></i> أرباح الرصيد المدور بالحساب</div>
                        <div class="val text-success">{{ number_format($totalOwnProfits, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-muted" style="font-size:0.72rem;">المحققة من تدوير أرباح الإدارة السابقة</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-box-formal" style="background: rgba(4, 120, 87, 0.2); border-color: rgba(4, 120, 87, 0.5);">
                        <div class="title text-light"><i class="fa fa-chart-line me-1 text-success"></i> إجمالي أرباح حساب الإدارة</div>
                        <div class="val text-success">{{ number_format($totalOwnProfits + $totalCommissions, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-muted" style="font-size:0.72rem;">العائد التراكمي الشامل</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- الشارتات التحليلية --}}
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark"><i class="fa fa-chart-line text-primary me-2"></i> تطور رصيد حساب الإدارة عبر الدورات الشهرية</h6>
                        <small class="text-muted">تراكم وتدوير الأرباح</small>
                    </div>
                    <div style="height: 270px;">
                        <canvas id="chartAhmedCapital"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark"><i class="fa fa-chart-pie text-secondary me-2"></i> هيكل الحصص المعتمدة بالدورة</h6>
                    </div>
                    <div style="height: 270px; position: relative;">
                        <canvas id="chartOwnershipDoughnut"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark"><i class="fa fa-chart-bar text-success me-2"></i> تفكيك دخل حساب الإدارة لكل دورة (أرباح نسب الشغل مقابل أرباح الرصيد المدور)</h6>
                    </div>
                    <div style="height: 230px;">
                        <canvas id="chartAhmedIncomeBreakdown"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- تبويبات بيان أرباح حساب الإدارة وسجل تغير النسب --}}
        <div class="card-formal mb-4">
            <div class="partners-tabs-bar no-print">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-btn-periods active" data-bs-toggle="tab" data-bs-target="#tabAhmedPeriods" type="button" role="tab">
                            <i class="fa fa-list-alt"></i>
                            <span>بيان أرباح حساب الإدارة في كل دورة إقفال</span>
                            <span class="badge bg-dark text-white border border-secondary ms-1">{{ $periods->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-btn-history" data-bs-toggle="tab" data-bs-target="#tabAhmedShareHistory" type="button" role="tab">
                            <i class="fa fa-history"></i>
                            <span>سجل تاريخ تغير النسب والحصص (النسب كانت كام وبقت كام)</span>
                            <span class="badge bg-dark text-warning border border-warning ms-1">{{ $shareHistoryLines ? $shareHistoryLines->count() : 0 }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                {{-- التاب 1: جدول بيان دخل حساب الإدارة وتغير نسبته عن الدورات السابقة --}}
                <div class="tab-pane fade show active" id="tabAhmedPeriods" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الدورة الشهرية</th>
                                    <th>الرصيد قبل التقفيل</th>
                                    <th class="text-center" style="background:#334155;">النسبة كانت كام</th>
                                    <th>ربح الرصيد المدور</th>
                                    <th>أرباح نسب الشغل</th>
                                    <th>إجمالي ربح الدورة</th>
                                    <th>القرار والمسحوب</th>
                                    <th>الرصيد بعد التقفيل</th>
                                    <th class="text-center" style="background:#1e40af;">النسبة بقت كام</th>
                                    <th>التقرير</th>
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
                                    <td class="fw-bold">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $per->period_name ?: $per->end_date }}</td>
                                    <td class="fw-bold text-dark">{{ number_format($pAhmedCap, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border fw-bold">
                                            {{ number_format($pAhmedOpenPct, 2) }}%
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">{{ number_format($pOwnProf, 2) }} ج</td>
                                    <td class="fw-bold text-primary">+{{ number_format($pComm, 2) }} ج</td>
                                    <td class="fw-bold text-dark fs-6 bg-light">+{{ number_format($pTotal, 2) }} ج</td>
                                    <td>
                                        @if(!empty($per->ahmed_withdrawn_amount) && $per->ahmed_withdrawn_amount > 0)
                                            <span class="badge bg-light text-danger border">
                                                <i class="fa fa-money-bill-wave me-1"></i> مسحوب: {{ number_format($per->ahmed_withdrawn_amount, 2) }} ج
                                            </span>
                                        @else
                                            <span class="badge bg-light text-success border">
                                                <i class="fa fa-sync-alt me-1"></i> تدوير بالحساب
                                            </span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-dark">{{ number_format($pCloseCap, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                            {{ number_format($pClosePct, 2) }}%
                                        </span>
                                        @if(abs($pPctDiff) > 0.0001)
                                            <small class="d-block {{ $pPctDiff > 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.7rem;">
                                                {{ $pPctDiff > 0 ? '+' : '' }}{{ number_format($pPctDiff, 2) }}%
                                            </small>
                                        @endif
                                    </td>
                                    <td>
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
                        </table>
                    </div>
                    <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                        <div class="small text-muted">
                            عرض <strong>{{ $periods->firstItem() ?? 0 }}</strong> إلى <strong>{{ $periods->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $periods->total() }}</strong> دورة معتمدة
                        </div>
                        <div>
                            {{ $periods->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- التاب 2: سجل تاريخ تغير نسبة حساب الإدارة (كانت كام وبقت كام) --}}
                <div class="tab-pane fade" id="tabAhmedShareHistory" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-formal table-hover m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ والوقت</th>
                                    <th>العملية / السبب</th>
                                    <th class="text-center">الرصيد قبل</th>
                                    <th class="text-center" style="background:#334155;">النسبة كانت كام (%)</th>
                                    <th class="text-center">التغير في الرصيد</th>
                                    <th class="text-center">الرصيد بعد</th>
                                    <th class="text-center" style="background:#1e40af;">النسبة بقت كام (%)</th>
                                    <th class="text-center">فرق النسبة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shareHistoryLines ?? [] as $hl)
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
                </div>
            </div>
        </div>

        {{-- خانات التوقيع والاعتماد الرسمية للطباعة --}}
        <div class="print-only" style="margin-top: 14px; page-break-inside: avoid;">
            <div class="row g-2 text-center">
                <div class="col-6">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">إعداد وتدقيق الحسابات</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">وحدة الحسابات والرقابة المالية</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">اعتماد الإدارة</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">{{ $headName }}</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">الاعتماد: ............................</div>
                    </div>
                </div>
            </div>
            <div class="text-center text-muted mt-2 pt-2 border-top" style="font-size:7pt;">
                تقرير مالي رسمي معتمد ومستخرج من النظام المالي لشركة الضبع · صادر بتاريخ {{ date('Y-m-d H:i') }}
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ctxCap = document.getElementById('chartAhmedCapital').getContext('2d');
    new Chart(ctxCap, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'رصيد حساب الإدارة (ج)',
                data: {!! json_encode($chartAhmedCapital) !!},
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

    const ctxDoughnut = document.getElementById('chartOwnershipDoughnut').getContext('2d');
    const partnerNames = [{!! json_encode($headName) !!}];
    const partnerCaps = [{{ $ahmedCapital }}];
    const partnerColors = ['#0f172a'];

    const colorsPool = ['#1e40af', '#047857', '#b45309', '#475569', '#0284c7', '#0d9488'];
    let cIdx = 0;
    @foreach($partners as $p)
        partnerNames.push('{{ addslashes($p->name) }}');
        partnerCaps.push({{ $p->capital }});
        partnerColors.push(colorsPool[cIdx % colorsPool.length]);
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

    const ctxBreakdown = document.getElementById('chartAhmedIncomeBreakdown').getContext('2d');
    new Chart(ctxBreakdown, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'الأرباح من نسب الشغل مع الشركاء (ج)',
                    data: {!! json_encode($chartAhmedCommissions) !!},
                    backgroundColor: '#1e40af',
                    borderRadius: 4
                },
                {
                    label: 'ربح الرصيد المدور في حساب الإدارة (ج)',
                    data: {!! json_encode($chartAhmedOwnProfit) !!},
                    backgroundColor: '#047857',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
        }
    });
</script>

</body>
</html>
