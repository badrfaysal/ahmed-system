<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بروفايل المؤسس: أحمد - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #0f172a;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }
        .main-content {
            padding: 24px 20px;
        }
        .founder-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #1e3a8a 100%);
            border-radius: 18px;
            color: #ffffff;
            padding: 28px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .founder-hero i.hero-watermark {
            position: absolute;
            left: -20px;
            bottom: -30px;
            font-size: 8rem;
            opacity: 0.08;
            transform: rotate(-15deg);
        }
        .founder-stat-box {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 16px 20px;
            height: 100%;
        }
        .founder-stat-box .title {
            font-size: 0.82rem;
            color: #cbd5e1;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .founder-stat-box .val {
            font-size: 1.65rem;
            font-weight: 900;
            color: #ffffff;
        }
        .chart-box {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة للوحة الشركاء
                </a>
                <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                    <i class="fa fa-crown text-warning"></i> بروفايل المؤسس والمدير العام: أحمد
                </h3>
            </div>
            <div>
                <span class="badge bg-dark px-3 py-2 fs-6">
                    <i class="fa fa-shield-halved text-warning me-1"></i> حساب المؤسس الرئيسي (حصة الأغلبية)
                </span>
            </div>
        </div>

        {{-- 👑 البانر الإحصائي الرئيسي لأحمد --}}
        <div class="founder-hero">
            <i class="fa fa-crown hero-watermark"></i>
            <div class="row g-3 align-items-center mb-3">
                <div class="col-md-7">
                    <h4 class="fw-bold mb-1 text-warning">البروفايل الأكثر تسارعاً في النمو 🚀</h4>
                    <p class="text-light opacity-75 small m-0">
                        رأس مال أحمد هو حجر الأساس في الشركة (الرصيد المتبقي بعد حصص الشركاء). ينمو رأس مال أحمد بمعدل مضاعف لأنه يجمع بين عوائد استثمار رأس ماله الخاص + العمولات الإدارية المحصلة من أرباح جميع الشركاء.
                    </p>
                </div>
                <div class="col-md-5 text-md-start">
                    <div class="d-inline-flex flex-column align-items-md-end">
                        <span class="small text-muted mb-1 text-light">نسبة أحمد الإجمالية الحالية من الشركة:</span>
                        <span class="display-6 fw-bold text-warning">{{ number_format($ahmedSharePct, 2) }}%</span>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="founder-stat-box">
                        <div class="title"><i class="fa fa-vault me-1 text-info"></i> رأس مال أحمد في الشركة</div>
                        <div class="val text-info">{{ number_format($ahmedCapital, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-light opacity-75" style="font-size:0.72rem;">من إجمالي {{ number_format($totalCompanyCapital, 2) }} ج للشركة</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="founder-stat-box">
                        <div class="title"><i class="fa fa-coins me-1 text-success"></i> أرباح رأس مال أحمد الشخصي</div>
                        <div class="val text-success">{{ number_format($totalOwnProfits, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-light opacity-75" style="font-size:0.72rem;">المحققة من حصته في رأس المال</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="founder-stat-box">
                        <div class="title"><i class="fa fa-hand-holding-dollar me-1 text-warning"></i> عمولات إدارة الشركاء المحصلة</div>
                        <div class="val text-warning">+{{ number_format($totalCommissions, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-light opacity-75" style="font-size:0.72rem;">مجموع نسب أحمد من أرباح الشركاء</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="founder-stat-box" style="background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4);">
                        <div class="title text-light"><i class="fa fa-chart-line-up me-1 text-success"></i> إجمالي دخل وعوائد أحمد</div>
                        <div class="val text-success">{{ number_format($totalOwnProfits + $totalCommissions, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                        <small class="text-light opacity-75" style="font-size:0.72rem;">العائد التراكمي الشامل</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📊 الشارتات التحليلية --}}
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="chart-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa fa-chart-line text-warning me-2"></i> منحنى تسارع نمو رأس مال أحمد عبر التقفيلات الدورية</h6>
                        <small class="text-muted">الرسملة المركبة</small>
                    </div>
                    <div style="height: 270px;">
                        <canvas id="chartAhmedCapital"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="chart-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa fa-chart-pie text-primary me-2"></i> هيكل ملكية الشركة الآن</h6>
                    </div>
                    <div style="height: 270px; position: relative;">
                        <canvas id="chartOwnershipDoughnut"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="chart-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa fa-chart-column text-success me-2"></i> تفكيك دخل أحمد في كل دورة شهرية (أرباح رأس ماله vs عمولات إدارة الشركاء)</h6>
                    </div>
                    <div style="height: 230px;">
                        <canvas id="chartAhmedIncomeBreakdown"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📋 جدول عوائد ودخل أحمد في كل تقفيلة سابقة --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold m-0 text-dark">
                    <i class="fa fa-table-list text-primary me-1"></i> بيان دخل وعمولات أحمد عن كل دورة إقفال شهرية
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover m-0" style="vertical-align: middle; font-size: 0.88rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>الدورة الشهرية</th>
                            <th>رأس مال الشركة</th>
                            <th>رأس مال أحمد في الدورة</th>
                            <th>نسبة أحمد</th>
                            <th>ربح رأس مال أحمد</th>
                            <th>عمولات الشركاء المحصلة</th>
                            <th>إجمالي دخل أحمد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $per)
                        @php
                            $lines = \Illuminate\Support\Facades\DB::table('partner_period_lines')->where('period_id', $per->id)->get();
                            $pPartnersCap = (float) $lines->sum('opening_capital');
                            $pAhmedCap = max(0.0, (float) $per->opening_capital - $pPartnersCap);
                            $pAhmedShare = (float) $per->opening_capital > 0 ? ($pAhmedCap / (float) $per->opening_capital) : 1.0;
                            $pOwnProf = round((float) $per->gross_profit * $pAhmedShare, 2);
                            $pComm = (float) $per->total_ahmed_commission;
                            $pTotal = $pOwnProf + $pComm;
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $loop->iteration }}</td>
                            <td class="fw-bold text-dark">{{ $per->period_name ?: $per->end_date }}</td>
                            <td>{{ number_format($per->opening_capital, 2) }} ج</td>
                            <td class="fw-bold text-primary">{{ number_format($pAhmedCap, 2) }} ج</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ number_format($pAhmedShare * 100, 2) }}%
                                </span>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($pOwnProf, 2) }} ج</td>
                            <td class="fw-bold text-warning-emphasis">+{{ number_format($pComm, 2) }} ج</td>
                            <td class="fw-bold text-dark fs-6 bg-success bg-opacity-10">{{ number_format($pTotal, 2) }} ج</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">لم يتم إقفال أي دورات سابقة حتى الآن.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // شارت منحنى رأس مال أحمد
    const ctxCap = document.getElementById('chartAhmedCapital').getContext('2d');
    new Chart(ctxCap, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'رأس مال أحمد (ج)',
                data: {!! json_encode($chartAhmedCapital) !!},
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.12)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 6,
                pointBackgroundColor: '#b45309'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false } }
        }
    });

    // شارت هيكل الملكية (Doughnut)
    const ctxDoughnut = document.getElementById('chartOwnershipDoughnut').getContext('2d');
    const partnerNames = ['أحمد (المؤسس)'];
    const partnerCaps = [{{ $ahmedCapital }}];
    const partnerColors = ['#f59e0b'];

    const colorsPool = ['#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#06b6d4', '#14b8a6'];
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
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Cairo' } } }
            }
        }
    });

    // شارت تفكيك دخل أحمد
    const ctxBreakdown = document.getElementById('chartAhmedIncomeBreakdown').getContext('2d');
    new Chart(ctxBreakdown, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'أرباح رأس مال أحمد الخاص (ج)',
                    data: {!! json_encode($chartAhmedOwnProfit) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 6
                },
                {
                    label: 'عمولات إدارة الشركاء (ج)',
                    data: {!! json_encode($chartAhmedCommissions) !!},
                    backgroundColor: '#f59e0b',
                    borderRadius: 6
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
