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
        .table-formal {
            margin: 0;
            vertical-align: middle;
            font-size: 0.88rem;
        }
        .table-formal thead th {
            background: var(--slate-800);
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 12px 14px;
            border: none;
            white-space: nowrap;
        }
        .table-formal tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--slate-200);
            font-variant-numeric: tabular-nums;
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

        /* ── ترويسة وخانات الطباعة الرسمية ── */
        .print-only { display: none; }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 8mm 12mm 8mm;
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
                font-family: 'Cairo', sans-serif !important;
                font-size: 8.5pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
                width: 100% !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, button, .btn, .chart-formal, .nav-tabs, .exited-banner-formal, .card-footer {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                right: 0 !important;
            }
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .card-formal {
                border: 1px solid #94a3b8 !important;
                box-shadow: none !important;
                margin-bottom: 12px !important;
                border-radius: 4px !important;
            }
            .table-responsive {
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
            }
            .table-formal {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 8pt !important;
                table-layout: auto !important;
            }
            .table-formal thead th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
                padding: 6px 4px !important;
                font-size: 7.5pt !important;
                font-weight: 700 !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .table-formal tbody td {
                border: 1px solid #cbd5e1 !important;
                padding: 5px 4px !important;
                font-size: 8pt !important;
                color: #000000 !important;
            }
            .table-formal tr {
                page-break-inside: avoid !important;
            }
            .tab-pane {
                display: block !important;
                opacity: 1 !important;
                margin-bottom: 12px !important;
            }
            .print-sig-col {
                border: 1px dashed #94a3b8 !important;
                padding: 10px !important;
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

        {{-- الترويسة المطبوعة الرسمية (تظهر فقط عند الطباعة) --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">سجل الشركاء والمساهمات الرأسمالية</small>
                    <span class="badge bg-dark text-white mt-1">كود الشريك: PRT-{{ str_pad($partner->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">كشف الحساب الاستثماري وحركة الأرباح</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">المساهم: {{ $partner->name }}</div>
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
                    <i class="fa fa-user-tie text-primary"></i> ملف الشريك الاستثماري: {{ $partner->name }}
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

        {{-- كروت المؤشرات الفردية --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="kpi-formal accent-primary">
                    <div class="title"><i class="fa fa-coins text-primary"></i> رأس المال الحالي</div>
                    <div class="val text-dark">{{ number_format($partner->capital, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                    <div class="sub">
                        رأس المال التأسيسي: {{ number_format($partner->initial_capital, 2) }} ج
                        @if($partner->capital > $partner->initial_capital)
                            <span class="text-success fw-bold ms-1">(زيادة: +{{ number_format($partner->capital - $partner->initial_capital) }} ج)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-formal accent-amber">
                    <div class="title"><i class="fa fa-percent text-warning"></i> نسبة المساهمة بالشركة</div>
                    <div class="val text-dark">{{ number_format($capitalSharePct, 2) }}%</div>
                    <div class="sub">
                        نسبة أتعاب الإدارة: <strong>{{ number_format($partner->ahmed_ratio, 1) }}%</strong>
                        (صافي الحصة في الربح: <strong>{{ number_format($effectiveProfitPct, 2) }}%</strong>)
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-formal accent-success">
                    <div class="title"><i class="fa fa-chart-line text-success"></i> إجمالي صافي الأرباح المحققة</div>
                    <div class="val text-success">{{ number_format($totalNetEarned, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                    <div class="sub">
                        عن {{ $lines->count() }} دورة إقفال شهرية معتمدة
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

        {{-- شارتات نمو رأس المال وتطور الحصة --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="chart-formal">
                    <div class="chart-header">
                        <span><i class="fa fa-chart-area text-primary me-2"></i> تطور رأس مال الشريك عبر الدورات الشهرية</span>
                        <small class="text-muted">الرسملة التلقائية</small>
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
                        <span><i class="fa fa-arrow-trend-up text-warning me-2"></i> تطور نسبة مساهمة الشريك في الشركة (تأثير تدوير الأرباح)</span>
                        <span class="badge bg-light text-dark border">النسبة الحالية: {{ number_format($capitalSharePct, 2) }}%</span>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="chartShareEvolution"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- الجداول التفصيلية --}}
        <div class="card-formal mb-4">
            <div class="card-header bg-white p-3 border-bottom">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tabPeriods" type="button">
                            <i class="fa fa-calendar-check text-success me-1"></i> سجل المشاركة في الدورات الشهرية ({{ $lines->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tabMovements" type="button">
                            <i class="fa fa-exchange-alt text-primary me-1"></i> الحركات المباشرة وتعديلات رأس المال ({{ $movements->count() }})
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
                                    <th>رأس مال البداية</th>
                                    <th class="text-center">نسبة المساهمة</th>
                                    <th>إجمالي الحصة (g)</th>
                                    <th>عمولة الإدارة (a)</th>
                                    <th>صافي ربح الشريك (n)</th>
                                    <th>المسحوب نقداً</th>
                                    <th>المدوّر في رأس المال</th>
                                    <th>رأس المال الجديد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lines as $l)
                                <tr>
                                    <td class="fw-bold">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $l->period_name ?: $l->end_date }}</td>
                                    <td>{{ number_format($l->opening_capital, 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border">
                                            {{ number_format($l->capital_share_pct, 2) }}%
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
                                        {{ $l->reinvested_amount != 0 ? number_format($l->reinvested_amount, 2) . ' ج' : '—' }}
                                    </td>
                                    <td class="fw-bold text-dark fs-6">{{ number_format($l->closing_capital, 2) }} ج</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">لم يشارك هذا الشريك في أي دورة شهرية حتى الآن. ستظهر بياناته هنا فور إغلاق أول دورة.</td>
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

                {{-- التاب 2: الحركات المباشرة --}}
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
                                            <span class="badge bg-light text-info text-dark border">تدوير أرباح ورسملة</span>
                                        @else
                                            <span class="badge bg-light text-secondary border">{{ $m->type }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold fs-6 {{ in_array($m->type, ['initial_deposit', 'capital_increase']) ? 'text-success' : 'text-danger' }}">
                                        {{ in_array($m->type, ['initial_deposit', 'capital_increase']) ? '+' : '-' }}{{ number_format($m->amount, 2) }} ج
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
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">اعتماد رئيس مجلس الإدارة</div>
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
                <button type="submit" class="btn btn-dark btn-sm fw-bold px-4">تأكيد وإيداع بالخزينة</button>
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
                        ده سحب من <u>رأس مال الشريك</u> (وليس أرباحاً)، وده هيأثر على نسبته في الشركة ونسب باقي الشركاء.
                    </div>
                </div>
                <div class="alert alert-secondary py-2 px-3 small rounded mb-3">
                    رأس المال الحالي المتاح: <strong>{{ number_format($partner->capital, 2) }} ج</strong>. لا يمكن سحب مبلغ يتجاوز هذا الرصيد.
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
                <button type="submit" class="btn btn-danger btn-sm fw-bold px-4" onclick="return confirm('تأكيد نهائي: ده سحب من رأس مال الشريك وده هيأثر على نسبته في الشركة ونسب باقي الشركاء. هل تريد المتابعة وصرف المبلغ؟')">
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
                    <label class="form-label fw-bold small">نسبة أتعاب رئيس الإدارة من الربح (%) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" class="form-control fw-bold" value="{{ $partner->ahmed_ratio }}" required>
                        <span class="input-group-text bg-white fw-bold">%</span>
                    </div>
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
                        <label class="form-check-label fw-bold" for="showIsHead">تعيين هذا المساهم رئيساً لمجلس الإدارة</label>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:0.72rem;">يُلغى تعيين الرئيس السابق تلقائياً، ويصبح رأس ماله هو المتبقي من رأس مال الشركة بعد حصص الشركاء.</small>
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
                      '<span class="fw-bold text-danger">وده هيأثر على نسبته في الشركة ونسب باقي الشركاء.</span><br><br>' +
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
            if (confirm('تنبيه هام:\nده سحب من رأس مال الشريك ({{ addslashes($partner->name) }}) وده هيأثر على نسبته في الشركة ونسب باقي الشركاء.\n\nهل تريد المتابعة؟')) {
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
                label: 'رأس المال (ج)',
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
                label: 'نسبة الشريك في الشركة (%)',
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
</script>

</body>
</html>
