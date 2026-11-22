<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب عضو: {{ $summary['display_name'] }} - {{ $companyName }}</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body { font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif; background: #fff; color: #1e293b; padding: 20px; font-size: 13px; }
        .print-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f766e; padding-bottom: 12px; margin-bottom: 16px; }
        .company-title { font-size: 1.35rem; font-weight: 800; color: #0f766e; margin: 0; }
        .report-title { font-size: 1.08rem; font-weight: 700; color: #334155; margin-top: 4px; }
        .meta-info { font-size: 0.82rem; color: #64748b; text-align: left; }

        .member-banner {
            background: #f0fdfa; border: 1.5px solid #0d9488; border-radius: 10px;
            padding: 12px 16px; margin-bottom: 16px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;
        }
        .member-name { font-size: 1.18rem; font-weight: 800; color: #0f766e; margin: 0; }
        .member-sub { font-size: 0.85rem; color: #334155; margin-top: 2px; }

        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
        .info-item { font-size: 0.84rem; text-align: center; }
        .info-label { color: #64748b; display: block; font-weight: 600; margin-bottom: 2px; }
        .info-val { font-weight: 800; color: #0f172a; font-size: 0.98rem; }

        .sec-header { font-size: 0.98rem; font-weight: 800; color: #0f766e; margin: 16px 0 8px; padding-bottom: 5px; border-bottom: 1.5px solid #e2e8f0; }

        .table-print { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .table-print th, .table-print td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 0.8rem; text-align: right; vertical-align: middle; }
        .table-print th { background-color: #f1f5f9; font-weight: 800; color: #334155; }
        .table-print tr:nth-child(even) { background-color: #fcfcfd; }

        .badge-status { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.74rem; font-weight: 700; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info    { background: #e0f2fe; color: #075985; }

        .footer-print {
            border-top: 1px dashed #cbd5e1; padding-top: 12px; margin-top: 24px;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 0.78rem; color: #64748b;
        }

        @media print {
            body { padding: 0 !important; }
            .no-print { display: none !important; }
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('cashflows.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fa fa-arrow-right me-1"></i> رجوع للشاشة
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-success btn-sm px-4 fw-bold">
                <i class="fa fa-print me-1"></i> طباعة كشف الحساب (PDF)
            </button>
        </div>
    </div>

    {{-- Report Header --}}
    <div class="print-header">
        <div>
            <h1 class="company-title">{{ $companyName }}</h1>
            <div class="report-title">كشف حساب واشتراكات عضو الجمعيات</div>
        </div>
        <div class="meta-info">
            <div><strong>تاريخ التقرير:</strong> {{ now()->format('Y/m/d H:i') }}</div>
            <div><strong>منظومة إدارة الجمعيات والتدفقات النقدية</strong></div>
        </div>
    </div>

    {{-- Member Banner --}}
    <div class="member-banner">
        <div>
            <div class="member-name">{{ $summary['display_name'] }}</div>
            <div class="member-sub">
                <i class="fa-solid fa-phone me-1 text-teal"></i> {{ $summary['display_phone'] ?: 'غير مسجل' }}
                <span class="mx-2">|</span>
                مشترك بـ <strong>{{ $summary['shares_count'] }} حصص / أسماء</strong> في <strong>{{ $summary['jamias_count'] }} جمعية</strong> ({{ implode('، ', $summary['jamias_names']) }})
            </div>
        </div>
        <div>
            @if($summary['late_count'] > 0)
                <span class="badge-status badge-danger">يوجد {{ $summary['late_count'] }} قسط متأخر ({{ number_format($summary['late_amount'], 2) }} ج)</span>
            @else
                <span class="badge-status badge-success"><i class="fa fa-check me-1"></i> منتظم بالسداد بالكامل</span>
            @endif
        </div>
    </div>

    {{-- KPI Stat Grid --}}
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">الحصص والجمعيات</span>
            <div class="info-val text-teal">{{ $summary['shares_count'] }} حصص ({{ $summary['jamias_count'] }} جمعية)</div>
            <small class="text-muted" style="font-size:0.7rem;">{{ implode(' — ', $summary['jamias_names']) }}</small>
        </div>
        <div class="info-item">
            <span class="info-label">إجمالي القبض المستحق</span>
            <div class="info-val text-success">{{ number_format($summary['total_payout'], 2) }} ج</div>
            <small class="text-muted" style="font-size:0.7rem;">قبض: {{ number_format($summary['collected_payout'], 2) }} | باقي: {{ number_format($summary['remaining_payout'], 2) }} ج</small>
        </div>
        <div class="info-item">
            <span class="info-label">إجمالي المطلوب سداده</span>
            <div class="info-val">{{ number_format($summary['total_installments_due'], 2) }} ج</div>
            <small class="text-muted" style="font-size:0.7rem;">سدد: {{ number_format($summary['total_paid'], 2) }} | باقي: {{ number_format($summary['total_remaining'], 2) }} ج</small>
        </div>
        <div class="info-item">
            <span class="info-label">الموقف المالي والمتأخرات</span>
            <div class="info-val {{ $summary['late_count'] > 0 ? 'text-danger' : 'text-success' }}">
                {{ $summary['late_count'] > 0 ? number_format($summary['late_amount'], 2) . ' ج متأخر' : 'لا متأخرات' }}
            </div>
            <small class="text-muted" style="font-size:0.7rem;">صافي التدفق: {{ number_format($summary['net_position'], 2) }} ج</small>
        </div>
    </div>

    {{-- Section 1: Payout Turns --}}
    <div class="sec-header">
        <i class="fa-solid fa-hand-holding-dollar me-1 text-teal"></i> أولاً: بيان الحصص وأدوار القبض (متى وكم سيقبض)
    </div>
    <table class="table-print">
        <thead>
            <tr>
                <th style="width:7%; text-align:center;">الدور</th>
                <th style="width:25%;">اسم الجمعية</th>
                <th style="width:18%;">شهر استحقاق القبض</th>
                <th style="width:16%;">قيمة القبض</th>
                <th style="width:20%;">حالة الصرف</th>
                <th style="width:14%;">ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sharesData as $sh)
                @php
                    $isPartialSh = \App\Models\JamiaShare::isPartial($sh['share_fraction'] ?? 1);
                    $pctLabelSh  = \App\Models\JamiaShare::formatPercent($sh['share_fraction'] ?? 1);
                @endphp
                <tr>
                    <td style="text-align:center; font-weight:bold;">{{ $sh['share_number'] }} @if($isPartialSh) <small class="text-primary">({{ $pctLabelSh }})</small> @endif</td>
                    <td class="fw-bold">{{ $sh['jamia_name'] }}</td>
                    <td>{{ $sh['payout_month_ar'] }}</td>
                    <td class="fw-bold text-success">{{ number_format($sh['payout_amount'], 2) }} ج</td>
                    <td>
                        @if($sh['is_collected'])
                            <span class="badge-status badge-success">تم الصرف ({{ $sh['collected_at'] ?: '—' }} {{ $sh['collected_account'] ? 'من ' . $sh['collected_account'] : '' }})</span>
                        @elseif($sh['status_class'] === 'warning')
                            <span class="badge-status badge-warning">مستحق الصرف الآن</span>
                        @else
                            <span class="badge-status badge-info">بانتظار حلول الشهر</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:0.76rem;">{{ $sh['is_transferred'] ? 'بديل متنازل له' : ($isPartialSh ? "حصة ({$pctLabelSh})" : 'سهم كامل') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Section 2: Monthly Installments --}}
    <div class="sec-header">
        <i class="fa-solid fa-calendar-check me-1 text-teal"></i> ثانياً: جدول الأقساط الشهرية وموقف السداد (ماذا سيدفع وما تم سداده وما تبقى)
    </div>
    <table class="table-print">
        <thead>
            <tr>
                <th style="width:16%;">الشهر</th>
                <th style="width:22%;">اسم الجمعية</th>
                <th style="width:8%; text-align:center;">الدور</th>
                <th style="width:14%;">القسط المطلوب</th>
                <th style="width:20%;">حالة السداد</th>
                <th style="width:20%;">تاريخ وطريقة السداد</th>
            </tr>
        </thead>
        <tbody>
            @foreach($installmentsData as $inst)
                <tr style="{{ $inst['is_paid'] ? 'background:#f0fdf4;' : ($inst['status_key'] === 'late' ? 'background:#fef2f2;' : '') }}">
                    <td class="fw-bold">{{ $inst['month_date_ar'] }}</td>
                    <td>{{ $inst['jamia_name'] }}</td>
                    <td style="text-align:center;">دور {{ $inst['share_number'] }}</td>
                    <td class="fw-bold">{{ number_format($inst['amount'], 2) }} ج</td>
                    <td>
                        @if($inst['is_paid'])
                            <span class="badge-status badge-success"><i class="fa fa-check me-1"></i> مسدد</span>
                        @elseif($inst['status_key'] === 'late')
                            <span class="badge-status badge-danger"><i class="fa fa-exclamation-triangle me-1"></i> متأخر ({{ $inst['days_late'] }} يوم)</span>
                        @elseif($inst['status_key'] === 'open')
                            <span class="badge-status badge-warning">جاري التحصيل</span>
                        @else
                            <span class="badge-status badge-info">قادم</span>
                        @endif
                    </td>
                    <td style="font-size:0.76rem;">
                        @if($inst['is_paid'])
                            {{ $inst['paid_at'] ?: '—' }} {{ $inst['paid_account'] ? '(' . $inst['paid_account'] . ')' : '' }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer Stamp --}}
    <div class="footer-print">
        <div style="font-size:0.72rem;">في حال وجود أي استفسار يرجى مراجعة إدارة الحسابات.</div>
        <div style="text-align:left;">
            <div>توقيع المسؤول / الاعتماد: _________________</div>
        </div>
    </div>

</body>
</html>
