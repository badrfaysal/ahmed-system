<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب جمعية: {{ $jamia->name }} - شركة الضبع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #fff; color: #1e293b; padding: 25px; }
        .print-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f766e; padding-bottom: 16px; margin-bottom: 24px; }
        .company-title { font-size: 1.5rem; font-weight: 800; color: #0f766e; margin: 0; }
        .report-title { font-size: 1.15rem; font-weight: 700; color: #334155; margin-top: 4px; }
        .meta-info { font-size: 0.85rem; color: #64748b; text-align: left; }

        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 25px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
        .info-item { font-size: 0.88rem; }
        .info-label { color: #64748b; display: block; font-weight: 600; margin-bottom: 3px; }
        .info-val { font-weight: 800; color: #0f172a; font-size: 1.05rem; }

        .sec-header { font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }

        .table-print { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .table-print th, .table-print td { border: 1px solid #cbd5e1; padding: 9px 12px; font-size: 0.86rem; text-align: right; }
        .table-print th { background-color: #f8fafc; font-weight: 700; color: #334155; }
        .table-print tr:nth-child(even) { background-color: #fcfcfd; }

        @media print {
            body { padding: 1cm !important; }
            .no-print { display: none !important; }
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ route('cashflows.index') }}" class="btn btn-outline-secondary px-3">
            <i class="fa fa-arrow-right me-1"></i> رجوع للشاشة
        </a>
        <button onclick="window.print()" class="btn btn-success px-4 fw-bold">
            <i class="fa fa-print me-1"></i> طباعة تقرير الجمعية (PDF)
        </button>
    </div>

    {{-- Report Header --}}
    <div class="print-header">
        <div>
            <h1 class="company-title">مجموعة الضبع التجارية</h1>
            <div class="report-title">كشف حساب وتفاصيل جمعية: {{ $jamia->name }}</div>
        </div>
        <div class="meta-info">
            <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y/m/d H:i') }}</div>
            <div><strong>الحالة:</strong> {{ $jamia->status === 'active' ? 'نشطة' : 'منتهية' }}</div>
        </div>
    </div>

    {{-- Jamia Info Grid --}}
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">المسؤول / المنظم</span>
            <div class="info-val">{{ $jamia->organizer_name ?: 'غير محدد' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">قيمة قبض السهم الواحد</span>
            <div class="info-val text-success">{{ number_format($jamia->total_amount, 2) }} ج</div>
        </div>
        <div class="info-item">
            <span class="info-label">المدة والأقساط</span>
            <div class="info-val">{{ $jamia->duration_months }} شهور ({{ number_format($jamia->monthly_installment, 2) }} ج/شهر)</div>
        </div>
        <div class="info-item">
            <span class="info-label">عدد أسهم الشركة</span>
            <div class="info-val text-teal">{{ $jamia->company_shares_count }} أسهم</div>
        </div>
    </div>

    {{-- Shares Table --}}
    <div class="sec-header">
        <i class="fa-solid fa-users me-1 text-teal"></i> تفاصيل أسهم الشركة ومواعيد القبض والتنازلات
    </div>
    <table class="table-print">
        <thead>
            <tr>
                <th>السهم</th>
                <th>موعد القبض</th>
                <th>قيمة القبض المقرر</th>
                <th>حالة التنازل (المقاصة)</th>
                <th>حق الشركة المسدد</th>
                <th>حالة استلام القبض</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jamia->shares as $sh)
                <tr>
                    <td><strong>{{ $sh->share_label }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($sh->payout_month)->locale('ar')->isoFormat('MMMM YYYY') }}</td>
                    <td><strong class="text-dark">{{ number_format($sh->payout_amount, 2) }} ج</strong></td>
                    <td>
                        @if($sh->is_transferred)
                            <span class="text-warning fw-bold">متنازل عنه ({{ $sh->transferred_to_name ?: 'طرف آخر' }})</span>
                        @else
                            <span class="text-success fw-bold">ملك الشركة</span>
                        @endif
                    </td>
                    <td>
                        @if($sh->is_transferred)
                            <strong class="text-primary">{{ number_format($sh->company_claim_amount, 2) }} ج (حق {{ $sh->paid_months_before_transfer }} شهور)</strong>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($sh->is_transferred)
                            {{ $sh->is_claim_recovered ? 'تم استرداد حق الشركة' : 'في الانتظار' }}
                        @else
                            {{ $sh->is_collected ? 'تم القبض والإيداع' : 'لم يقبض بعد' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Installments Table --}}
    <div class="sec-header">
        <i class="fa-solid fa-list-ol me-1 text-teal"></i> جدول أقساط الجمعية المستحقة والمسددة
    </div>
    <table class="table-print">
        <thead>
            <tr>
                <th>#</th>
                <th>السهم</th>
                <th>الشهر</th>
                <th>تاريخ الاستحقاق</th>
                <th>قيمة القسط</th>
                <th>الحالة</th>
                <th>تاريخ السداد</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jamia->installments as $idx => $inst)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $inst->share->share_label ?? '—' }}</td>
                    <td>شهر {{ $inst->month_index }}</td>
                    <td>{{ \Carbon\Carbon::parse($inst->month_date)->format('Y/m/d') }}</td>
                    <td>{{ number_format($inst->amount, 2) }} ج</td>
                    <td>
                        @if($inst->is_waived)
                            <span class="text-muted">معفي (تنازل)</span>
                        @elseif($inst->is_paid)
                            <span class="text-success fw-bold">مسدد</span>
                        @else
                            <span class="text-danger fw-bold">مستحق</span>
                        @endif
                    </td>
                    <td>{{ $inst->paid_at ? \Carbon\Carbon::parse($inst->paid_at)->format('Y/m/d') : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
