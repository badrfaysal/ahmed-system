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
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, button, .btn, .chart-formal, canvas, .card-footer {
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
            .hero-formal {
                background: #0f172a !important;
                color: #ffffff !important;
                border: 1px solid #000 !important;
                padding: 12px !important;
                margin-bottom: 12px !important;
            }
            .hero-formal p { display: none !important; }
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
                    <span class="badge bg-dark text-white mt-1">حساب الأغلبية والإدارة</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">التقرير المالي الشامل لحساب رئيس مجلس الإدارة</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">{{ $headName }}</div>
                    <small class="text-muted">نسبة المساهمة في الشركة: <strong>{{ number_format($ahmedSharePct, 2) }}%</strong></small>
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
                <small class="text-muted">متابعة نمو الحصة الرأسمالية الأساسية وتراكم أتعاب وعمولات إدارة الشركاء</small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-dark btn-sm fw-bold px-3 py-2 shadow-sm" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> طباعة التقرير المالي
                </button>
                <span class="badge bg-light text-dark border px-3 py-2 small fw-bold">
                    <i class="fa fa-shield-alt text-primary me-1"></i> حساب الإدارة الرئيسي (حصة الأغلبية)
                </span>
            </div>
        </div>

        {{-- الملخص التنفيذي لرأس مال ودخل الإدارة --}}
        <div class="hero-formal">
            <div class="row g-3 align-items-center mb-3">
                <div class="col-md-8">
                    <h5 class="fw-bold mb-1 text-white">المركز المالي الشامل لحساب الإدارة</h5>
                    <p class="text-light opacity-75 small m-0">
                        رأس مال رئيس الإدارة هو حجر الأساس للشركة (الرصيد المتبقي بعد حصص الشركاء). ينمو رأس مال الإدارة بمعدل مضاعف نتيجة تجميع عوائد استثمار رأس المال الشخصي بالإضافة إلى عمولات الإدارة المحصلة من أرباح جميع الشركاء.
                    </p>
                </div>
                <div class="col-md-4 text-md-start">
                    <div class="d-inline-flex flex-column align-items-md-end">
                        <span class="small text-muted mb-1 text-light">نسبة مساهمة رئيس الإدارة الإجمالية بالشركة:</span>
                        <span class="fs-2 fw-bold text-warning font-monospace">{{ number_format($ahmedSharePct, 2) }}%</span>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-md-3">
                    <div class="stat-box-formal">
                        <div class="title"><i class="fa fa-vault me-1 text-info"></i> رأس مال رئيس الإدارة في الشركة</div>
                        <div class="val text-info">{{ number_format($ahmedCapital, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-muted" style="font-size:0.72rem;">من إجمالي {{ number_format($totalCompanyCapital, 2) }} ج للشركة</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-box-formal">
                        <div class="title"><i class="fa fa-coins me-1 text-success"></i> أرباح رأس المال الشخصي</div>
                        <div class="val text-success">{{ number_format($totalOwnProfits, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-muted" style="font-size:0.72rem;">المحققة من حصة رأس المال</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-box-formal">
                        <div class="title"><i class="fa fa-hand-holding-dollar me-1 text-warning"></i> عمولات أتعاب إدارة الشركاء</div>
                        <div class="val text-warning">+{{ number_format($totalCommissions, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-muted" style="font-size:0.72rem;">مجموع نسب الإدارة المحصلة</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-box-formal" style="background: rgba(4, 120, 87, 0.2); border-color: rgba(4, 120, 87, 0.5);">
                        <div class="title text-light"><i class="fa fa-chart-line me-1 text-success"></i> إجمالي دخل وعوائد الإدارة</div>
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
                        <h6 class="fw-bold m-0 text-dark"><i class="fa fa-chart-line text-primary me-2"></i> تطور رأس مال الإدارة عبر الدورات الشهرية</h6>
                        <small class="text-muted">الرسملة المركبة</small>
                    </div>
                    <div style="height: 270px;">
                        <canvas id="chartAhmedCapital"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark"><i class="fa fa-chart-pie text-secondary me-2"></i> هيكل مساهمة رأس المال بالشركة</h6>
                    </div>
                    <div style="height: 270px; position: relative;">
                        <canvas id="chartOwnershipDoughnut"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="chart-formal">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark"><i class="fa fa-chart-bar text-success me-2"></i> تفكيك دخل الإدارة لكل دورة شهرية (أرباح رأس المال مقابل أتعاب إدارة الشركاء)</h6>
                    </div>
                    <div style="height: 230px;">
                        <canvas id="chartAhmedIncomeBreakdown"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- جدول بيان دخل وعمولات رئيس الإدارة عن الدورات السابقة --}}
        <div class="card-formal mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    <i class="fa fa-list-alt text-primary"></i> بيان دخل وعمولات الإدارة عن كل دورة إقفال شهرية
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-formal table-hover m-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الدورة الشهرية</th>
                            <th>رأس مال الشركة</th>
                            <th>رأس مال رئيس الإدارة بالدورة</th>
                            <th>نسبة المساهمة</th>
                            <th>أرباح رأس مال رئيس الإدارة</th>
                            <th>عمولات أتعاب الشركاء</th>
                            <th>إجمالي دخل الدورة</th>
                            <th>القرار والمسحوب</th>
                            <th>تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $per)
                        @php
                            $pAhmedCap = $per->ahmed_opening_capital > 0 ? (float) $per->ahmed_opening_capital : 0.0;
                            if ($pAhmedCap == 0) {
                                $lines = \Illuminate\Support\Facades\DB::table('partner_period_lines')->where('period_id', $per->id)->get();
                                $pPartnersCap = (float) $lines->sum('opening_capital');
                                $pAhmedCap = max(0.0, (float) $per->opening_capital - $pPartnersCap);
                            }
                            $pAhmedShare = (float) $per->opening_capital > 0 ? ($pAhmedCap / (float) $per->opening_capital) : 1.0;
                            $pOwnProf = $per->ahmed_own_profit != 0 ? (float) $per->ahmed_own_profit : round((float) $per->gross_profit * $pAhmedShare, 2);
                            $pComm = (float) $per->total_ahmed_commission;
                            $pTotal = $per->ahmed_total_profit != 0 ? (float) $per->ahmed_total_profit : ($pOwnProf + $pComm);
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $loop->iteration }}</td>
                            <td class="fw-bold text-dark">{{ $per->period_name ?: $per->end_date }}</td>
                            <td>{{ number_format($per->opening_capital, 2) }} ج</td>
                            <td class="fw-bold text-dark">{{ number_format($pAhmedCap, 2) }} ج</td>
                            <td>
                                <span class="badge bg-light text-primary border">
                                    {{ number_format($pAhmedShare * 100, 2) }}%
                                </span>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($pOwnProf, 2) }} ج</td>
                            <td class="fw-bold text-primary">+{{ number_format($pComm, 2) }} ج</td>
                            <td class="fw-bold text-dark fs-6 bg-light">{{ number_format($pTotal, 2) }} ج</td>
                            <td>
                                @if(!empty($per->ahmed_withdrawn_amount) && $per->ahmed_withdrawn_amount > 0)
                                    <span class="badge bg-light text-danger border">
                                        <i class="fa fa-money-bill-wave me-1"></i> مسحوب: {{ number_format($per->ahmed_withdrawn_amount, 2) }} ج
                                    </span>
                                @else
                                    <span class="badge bg-light text-success border">
                                        <i class="fa fa-sync-alt me-1"></i> تدوير بالكامل
                                    </span>
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
                            <td colspan="10" class="text-center py-4 text-muted">لم يتم إقفال أي دورات سابقة حتى الآن.</td>
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
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">اعتماد رئيس مجلس الإدارة</div>
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
                label: 'رأس مال رئيس الإدارة (ج)',
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
                    label: 'أرباح رأس مال رئيس الإدارة (ج)',
                    data: {!! json_encode($chartAhmedOwnProfit) !!},
                    backgroundColor: '#047857',
                    borderRadius: 4
                },
                {
                    label: 'عمولات أتعاب إدارة الشركاء (ج)',
                    data: {!! json_encode($chartAhmedCommissions) !!},
                    backgroundColor: '#1e40af',
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
