<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير تقفيل الدورة: {{ $period->period_name }} - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
        .summary-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            height: 100%;
        }
        .summary-box .title { font-size: 0.78rem; font-weight: 700; color: #64748b; margin-bottom: 4px; }
        .summary-box .val   { font-size: 1.4rem; font-weight: 900; }
        @media print {
            .app-sidebar, .no-print { display: none !important; }
            .main-content { margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة للوحة الشركاء
                </a>
                <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                    <i class="fa fa-file-invoice-dollar text-primary"></i> تقرير تقفيل الدورة: {{ $period->period_name }}
                    <span class="badge bg-success ms-2" style="font-size:0.75rem;">معتمدة ومغلقة</span>
                </h3>
                <small class="text-muted">
                    الفترة من <strong>{{ $period->start_date }}</strong> إلى <strong>{{ $period->end_date }}</strong>
                    · أُغلقت في: {{ date('Y-m-d H:i', strtotime($period->created_at)) }}
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-dark fw-bold px-3 py-2" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> طباعة التقرير
                </button>
            </div>
        </div>

        {{-- 📊 ملخص الأرقام الكلية للدورة --}}
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="summary-box">
                    <div class="title"><i class="fa fa-vault me-1"></i> رأس مال الافتتاح</div>
                    <div class="val text-info">{{ number_format($period->opening_capital, 2) }} ج</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="summary-box">
                    <div class="title"><i class="fa fa-building-columns me-1"></i> رأس مال الإقفال</div>
                    <div class="val text-primary">{{ number_format($period->closing_capital, 2) }} ج</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="summary-box">
                    <div class="title"><i class="fa fa-calculator me-1"></i> الربح التشغيلي (R)</div>
                    <div class="val {{ $period->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $period->gross_profit >= 0 ? '+' : '' }}{{ number_format($period->gross_profit, 2) }} ج
                    </div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="summary-box">
                    <div class="title"><i class="fa fa-users me-1"></i> صافي أرباح الشركاء</div>
                    <div class="val text-dark">{{ number_format($period->total_partners_net_profit, 2) }} ج</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="summary-box">
                    <div class="title"><i class="fa fa-crown me-1 text-warning"></i> عمولة أحمد المحصلة</div>
                    <div class="val text-warning-emphasis">+{{ number_format($period->total_ahmed_commission, 2) }} ج</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="summary-box">
                    <div class="title"><i class="fa fa-arrows-rotate me-1 text-success"></i> المدوّر في رأس المال</div>
                    <div class="val text-success">{{ number_format($period->total_reinvested, 2) }} ج</div>
                    <small class="text-danger d-block mt-1" style="font-size:0.7rem;">مسحوب نقداً: {{ number_format($period->total_withdrawn, 2) }} ج</small>
                </div>
            </div>
        </div>

        @if($period->notes)
        <div class="alert alert-secondary py-2 px-3 small border-0 rounded-3 mb-4">
            <i class="fa fa-note-sticky me-1"></i> <strong>ملاحظات التقفيل:</strong> {{ $period->notes }}
        </div>
        @endif

        {{-- 📋 جدول تفاصيل حصص كل شريك --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold m-0 text-dark">
                    <i class="fa fa-list-check text-primary me-2"></i> بيان توزيع الحصص والأرباح للشركاء
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover m-0" style="vertical-align: middle; font-size: 0.88rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>الشريك</th>
                            <th>رأس مال البداية</th>
                            <th class="text-center">حصته من الشركة (s)</th>
                            <th>نصيبه من الأرباح (g)</th>
                            <th>نسبة أحمد</th>
                            <th>عمولة أحمد (a)</th>
                            <th>صافي ربح الشريك (n)</th>
                            <th>القرار المتخذ</th>
                            <th>المسحوب نقداً</th>
                            <th>الخزنة المصروف منها</th>
                            <th>المدوّر في رأس المال</th>
                            <th>رأس المال الجديد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lines as $l)
                        <tr>
                            <td class="fw-bold">{{ $loop->iteration }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $l->partner_name }}</strong>
                                <small class="text-muted">{{ $l->partner_phone ?: '—' }}</small>
                            </td>
                            <td class="fw-bold">{{ number_format($l->opening_capital, 2) }} ج</td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    {{ number_format($l->capital_share_pct, 2) }}%
                                </span>
                            </td>
                            <td class="fw-bold {{ $l->gross_share >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $l->gross_share >= 0 ? '+' : '' }}{{ number_format($l->gross_share, 2) }} ج
                            </td>
                            <td>
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                    {{ number_format($l->ahmed_ratio_pct, 0) }}%
                                </span>
                            </td>
                            <td class="fw-bold text-warning-emphasis">
                                +{{ number_format($l->ahmed_commission, 2) }} ج
                            </td>
                            <td class="fw-bold fs-6 {{ $l->net_share >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $l->net_share >= 0 ? '+' : '' }}{{ number_format($l->net_share, 2) }} ج
                            </td>
                            <td>
                                @if($l->payout_action === 'reinvest')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">تدوير بالكامل</span>
                                @elseif($l->payout_action === 'withdraw_all')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">سحب بالكامل</span>
                                @else
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">سحب جزئي وتدوير</span>
                                @endif
                            </td>
                            <td class="fw-bold text-danger">
                                {{ $l->withdrawn_amount > 0 ? number_format($l->withdrawn_amount, 2) . ' ج' : '—' }}
                            </td>
                            <td>
                                {{ $l->payout_account_name ?: '—' }}
                            </td>
                            <td class="fw-bold text-success">
                                {{ $l->reinvested_amount != 0 ? number_format($l->reinvested_amount, 2) . ' ج' : '—' }}
                            </td>
                            <td class="fw-bold text-primary fs-6">{{ number_format($l->closing_capital, 2) }} ج</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center py-4 text-muted">لا توجد بيانات تفصيلية لهذه الدورة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
