<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بروفايل الشريك: {{ $partner->name }} - شركة الضبع</title>
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
        .kpi-box {
            background: #ffffff;
            border-radius: 14px;
            padding: 18px 20px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .kpi-box::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
        }
        .kpi-box.c-blue::before   { background: #3b82f6; }
        .kpi-box.c-gold::before   { background: #f59e0b; }
        .kpi-box.c-green::before  { background: #10b981; }
        .kpi-box.c-purple::before { background: #8b5cf6; }

        .kpi-box .title { font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px; }
        .kpi-box .val   { font-size: 1.55rem; font-weight: 900; color: #0f172a; }
        .kpi-box .sub   { font-size: 0.74rem; color: #94a3b8; margin-top: 4px; }

        .chart-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 24px;
        }
        .chart-header {
            font-weight: 800;
            font-size: 0.95rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة والتحكم --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة للوحة الشركاء
                </a>
                <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                    <i class="fa fa-user-tie text-primary"></i> بروفايل الشريك: {{ $partner->name }}
                    @if($partner->status === 'exited')
                        <span class="badge bg-danger ms-2" style="font-size:0.75rem;">متخارج</span>
                    @else
                        <span class="badge bg-success ms-2" style="font-size:0.75rem;">نشط ومستمر</span>
                    @endif
                </h3>
                <small class="text-muted">
                    <i class="fa fa-phone me-1"></i>{{ $partner->phone ?: 'بدون هاتف' }} · انضم في: {{ $partner->joined_at ? date('Y-m-d', strtotime($partner->joined_at)) : '—' }}
                </small>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-success fw-bold px-3 py-2 shadow-sm" onclick="openAddCapitalModal()">
                    <i class="fa fa-plus me-1"></i> زيادة رأس المال
                </button>
                <button type="button" class="btn btn-warning fw-bold px-3 py-2 shadow-sm text-dark" onclick="openWithdrawCapitalModal()">
                    <i class="fa fa-minus me-1"></i> سحب من رأس المال
                </button>
                <button type="button" class="btn btn-dark fw-bold px-3 py-2 shadow-sm" onclick="openEditPartnerModal()">
                    <i class="fa fa-pen me-1"></i> تعديل النسبة والبيانات
                </button>
            </div>
        </div>

        {{-- 📊 كروت المؤشرات الفردية --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="kpi-box c-blue">
                    <div class="title"><i class="fa fa-coins me-1"></i> رأس المال الحالي</div>
                    <div class="val text-primary">{{ number_format($partner->capital, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                    <div class="sub">
                        التأسيسي: {{ number_format($partner->initial_capital, 2) }} ج
                        @if($partner->capital > $partner->initial_capital)
                            <span class="text-success fw-bold ms-1">(▲ نما بمقدار {{ number_format($partner->capital - $partner->initial_capital) }} ج)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-box c-gold">
                    <div class="title"><i class="fa fa-percent me-1"></i> النسبة الحالية من الشركة (s)</div>
                    <div class="val text-warning-emphasis">{{ number_format($capitalSharePct, 2) }}%</div>
                    <div class="sub">
                        نسبة أحمد المتفق عليها: <strong>{{ number_format($partner->ahmed_ratio, 1) }}%</strong>
                        (صافي حصته في الأرباح: <strong>{{ number_format($effectiveProfitPct, 2) }}%</strong>)
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-box c-green">
                    <div class="title"><i class="fa fa-chart-line me-1"></i> إجمالي صافي الأرباح المحققة</div>
                    <div class="val text-success">{{ number_format($totalNetEarned, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                    <div class="sub">
                        من {{ $lines->count() }} تقفيلة دورية معتمدة
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-box c-purple">
                    <div class="title"><i class="fa fa-arrows-split-up-and-left me-1"></i> المسحوب مقابل المدوّر</div>
                    <div class="val text-dark">{{ number_format($totalReinvested, 2) }} <span class="fs-6 fw-normal text-muted">ج مدوّر</span></div>
                    <div class="sub">
                        مسحوب نقداً: <strong class="text-danger">{{ number_format($totalWithdrawn, 2) }} ج</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📈 شارتات نمو رأس المال وتطور الحصة --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <span><i class="fa fa-chart-area text-primary me-2"></i> منحنى نمو وتطور رأس المال شهر بشهر</span>
                        <small class="text-muted">الرسملة التلقائية</small>
                    </div>
                    <div style="height: 260px;">
                        <canvas id="chartCapitalGrowth"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="chart-card">
                    <div class="chart-header">
                        <span><i class="fa fa-chart-column text-success me-2"></i> الأرباح المحققة مقابل المسحوبات النقدية</span>
                        <small class="text-muted">لكل دورة شهرية</small>
                    </div>
                    <div style="height: 260px;">
                        <canvas id="chartProfitsVsWithdrawn"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="chart-card">
                    <div class="chart-header">
                        <span><i class="fa fa-arrow-trend-up text-warning me-2"></i> تطور نسبة الشريك في الشركة مع الوقت (تأثير تدوير الأرباح)</span>
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">النسبة الحالية: {{ number_format($capitalSharePct, 2) }}%</span>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="chartShareEvolution"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📋 الجداول التفصيلية --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white p-3 border-bottom">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tabPeriods" type="button">
                            <i class="fa fa-calendar-check text-success me-1"></i> سجل مشاركات التقفيل الشهري ({{ $lines->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tabMovements" type="button">
                            <i class="fa fa-money-bill-transfer text-primary me-1"></i> الحركات المباشرة والإيداعات ({{ $movements->count() }})
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content p-0">
                {{-- التاب 1: التقفيلات الشهرية --}}
                <div class="tab-pane fade show active" id="tabPeriods">
                    <div class="table-responsive">
                        <table class="table table-hover m-0" style="vertical-align: middle; font-size: 0.88rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>الدورة الشهرية</th>
                                    <th>رأس مال البداية</th>
                                    <th class="text-center">حصته في الشركة</th>
                                    <th>نصيبه من الأرباح (g)</th>
                                    <th>عمولة أحمد (a)</th>
                                    <th>صافي ربحه (n)</th>
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
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            {{ number_format($l->capital_share_pct, 2) }}%
                                        </span>
                                    </td>
                                    <td class="fw-bold {{ $l->gross_share >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $l->gross_share >= 0 ? '+' : '' }}{{ number_format($l->gross_share, 2) }} ج
                                    </td>
                                    <td class="text-warning-emphasis fw-bold">
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
                                    <td class="fw-bold text-primary fs-6">{{ number_format($l->closing_capital, 2) }} ج</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">لم يشارك هذا الشريك في أي تقفيلة شهرية حتى الآن. ستظهر بياناته هنا فور إغلاق أول دورة.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- التاب 2: الحركات المباشرة --}}
                <div class="tab-pane fade" id="tabMovements">
                    <div class="table-responsive">
                        <table class="table table-hover m-0" style="vertical-align: middle; font-size: 0.88rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>التاريخ والوقت</th>
                                    <th>نوع الحركة</th>
                                    <th>المبلغ</th>
                                    <th>الخزنة / المحفظة</th>
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
                                            <span class="badge bg-success">إيداع رأس مال تأسيسي</span>
                                        @elseif($m->type === 'capital_increase')
                                            <span class="badge bg-primary">زيادة رأس مال</span>
                                        @elseif($m->type === 'capital_withdrawal')
                                            <span class="badge bg-danger">سحب من رأس المال</span>
                                        @elseif($m->type === 'profit_withdrawal')
                                            <span class="badge bg-warning text-dark">صرف أرباح دورية</span>
                                        @elseif($m->type === 'profit_reinvest')
                                            <span class="badge bg-info text-dark">تدوير أرباح ورسملة</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $m->type }}</span>
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
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODALS --}}
{{-- 1. زيادة رأس المال --}}
<div class="modal fade" id="modalAddCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.addCapital', $partner->id) }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-plus me-2"></i> زيادة رأس مال الشريك ({{ $partner->name }})</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ المودع (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control fw-bold fs-5 text-success" placeholder="المبلغ..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الخزنة المستلمة للمبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select fw-bold" required>
                        <option value="">-- اختر الخزنة لتوريد المبلغ --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيدها: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" placeholder="سبب زيادة الحصة...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-success fw-bold px-4">تأكيد وإيداع بالخزنة</button>
            </div>
        </form>
    </div>
</div>

{{-- 2. سحب من رأس المال --}}
<div class="modal fade" id="modalWithdrawCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.withdrawCapital', $partner->id) }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fa fa-minus me-2"></i> سحب من رأس مال الشريك ({{ $partner->name }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning py-2 px-3 small border-0 rounded-3 mb-3">
                    رأس ماله الحالي: <strong>{{ number_format($partner->capital, 2) }} ج</strong>. لا يمكن سحب مبلغ يتجاوز هذا الرصيد.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ المراد سحبه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" max="{{ $partner->capital }}" name="amount" class="form-control fw-bold fs-5 text-danger" placeholder="المبلغ..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الخزنة المصروف منها المبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select fw-bold" required>
                        <option value="">-- اختر الخزنة للصرف --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيدها: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات / سبب السحب</label>
                    <input type="text" name="notes" class="form-control" placeholder="سبب استرداد جزء من رأس المال...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-warning fw-bold px-4 text-dark">تأكيد وصرف من الخزنة</button>
            </div>
        </form>
    </div>
</div>

{{-- 3. تعديل بيانات الشريك --}}
<div class="modal fade" id="modalEditPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('partners.update', $partner->id) }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-pen me-2"></i> تعديل إعدادات الشريك والنسبة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">اسم الشريك</label>
                    <input type="text" name="name" class="form-control" value="{{ $partner->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control" value="{{ $partner->phone }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-warning-emphasis">نسبة أحمد من أرباح الشريك (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" class="form-control fw-bold" value="{{ $partner->ahmed_ratio }}" required>
                        <span class="input-group-text bg-white fw-bold">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" value="{{ $partner->notes }}">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark fw-bold px-4">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openAddCapitalModal() { new bootstrap.Modal(document.getElementById('modalAddCapital')).show(); }
    function openWithdrawCapitalModal() { new bootstrap.Modal(document.getElementById('modalWithdrawCapital')).show(); }
    function openEditPartnerModal() { new bootstrap.Modal(document.getElementById('modalEditPartner')).show(); }

    // شارت نمو رأس المال
    const ctxCap = document.getElementById('chartCapitalGrowth').getContext('2d');
    new Chart(ctxCap, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'رأس المال (ج)',
                data: {!! json_encode($chartCapitalGrowth) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 5,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false } }
        }
    });

    // شارت الأرباح مقابل المسحوبات
    const ctxProf = document.getElementById('chartProfitsVsWithdrawn').getContext('2d');
    new Chart(ctxProf, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'صافي الربح المحقق (ج)',
                    data: {!! json_encode($chartProfits) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 6
                },
                {
                    label: 'المسحوب نقداً (ج)',
                    data: {!! json_encode($chartWithdrawn) !!},
                    backgroundColor: '#ef4444',
                    borderRadius: 6
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

    // شارت تطور النسبة المئوية
    const ctxShare = document.getElementById('chartShareEvolution').getContext('2d');
    new Chart(ctxShare, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'نسبة الشريك في الشركة (%)',
                data: {!! json_encode($chartShares) !!},
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.12)',
                borderWidth: 3,
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointBackgroundColor: '#f59e0b'
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
