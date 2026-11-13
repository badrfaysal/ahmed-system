<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف جمعية: {{ $jamia->name }} - {{ $companyName }}</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        @page {
            size: {{ $jamia->duration_months > 10 ? 'A4 landscape' : 'A4 portrait' }};
            margin: 10mm;
        }
        body { font-family: 'Cairo', sans-serif; background: #fff; color: #1e293b; padding: 20px; font-size: 13px; }
        .print-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f766e; padding-bottom: 12px; margin-bottom: 16px; }
        .company-title { font-size: 1.35rem; font-weight: 800; color: #0f766e; margin: 0; }
        .report-title { font-size: 1.08rem; font-weight: 700; color: #334155; margin-top: 4px; }
        .meta-info { font-size: 0.82rem; color: #64748b; text-align: left; }

        .info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
        .info-item { font-size: 0.84rem; }
        .info-label { color: #64748b; display: block; font-weight: 600; margin-bottom: 2px; }
        .info-val { font-weight: 800; color: #0f172a; font-size: 0.98rem; }

        .sec-header { font-size: 0.98rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; }

        .table-print { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-print th, .table-print td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 0.8rem; text-align: right; vertical-align: middle; }
        .table-print th { background-color: #f1f5f9; font-weight: 800; color: #334155; }
        .table-print tr:nth-child(even) { background-color: #fcfcfd; }
        .table-print tr.company-row { background-color: #f0f9ff !important; }

        .matrix-table th, .matrix-table td { text-align: center; padding: 4px 5px; font-size: {{ $jamia->duration_months > 12 ? '0.68rem' : '0.75rem' }}; }
        .matrix-table td.name-col { text-align: right; white-space: nowrap; font-weight: 700; }
        .cell-paid { background: #dcfce7 !important; color: #166534; font-weight: 700; }
        .cell-late { background: #fee2e2 !important; color: #991b1b; font-weight: 700; }
        .cell-open { background: #fef3c7 !important; color: #92400e; }
        .cell-company { background: #e0f2fe !important; color: #075985; font-weight: 700; }

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
        <button onclick="window.print()" class="btn btn-success btn-sm px-4 fw-bold">
            <i class="fa fa-print me-1"></i> طباعة تقرير الجمعية (PDF)
        </button>
    </div>

    {{-- Report Header --}}
    <div class="print-header">
        <div>
            <h1 class="company-title">{{ $companyName }}</h1>
            <div class="report-title">كشف وتفاصيل جمعية: {{ $jamia->name }}</div>
        </div>
        <div class="meta-info">
            <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y/m/d H:i') }}</div>
            <div><strong>الحالة:</strong> {{ $jamia->status === 'active' ? 'نشطة' : ($jamia->status === 'completed' ? 'منتهية' : 'ملغاة') }}</div>
        </div>
    </div>

    {{-- Jamia Info Grid --}}
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">المنظم</span>
            <div class="info-val">{{ $jamia->organizer_name ?: $companyName }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">قيمة القبض</span>
            <div class="info-val text-success">{{ number_format($jamia->total_amount, 2) }} ج</div>
        </div>
        <div class="info-item">
            <span class="info-label">المدة والقسط الشهري</span>
            <div class="info-val">{{ $jamia->duration_months }} شهر ({{ number_format($jamia->monthly_installment, 2) }} ج)</div>
        </div>
        <div class="info-item">
            <span class="info-label">فترة التحصيل الشهرية</span>
            <div class="info-val text-teal">من يوم {{ $jamia->collection_start_day ?? 1 }} إلى يوم {{ $jamia->collection_end_day ?? 10 }}</div>
        </div>
    </div>

    {{-- Section 1: Members & Payout Turns --}}
    <div class="sec-header">
        <i class="fa-solid fa-users me-1 text-teal"></i> الأعضاء وأماكن دخولهم (أدوار القبض)
    </div>
    <table class="table-print">
        <thead>
            <tr>
                <th style="width:7%;">الدور</th>
                <th style="width:18%;">شهر القبض</th>
                <th style="width:27%;">الاسم</th>
                <th style="width:15%;">الهاتف</th>
                <th style="width:13%;">النوع</th>
                <th style="width:20%;">حالة صرف القبض</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jamia->shares as $sh)
                <tr class="{{ $sh->is_company ? 'company-row' : '' }}">
                    <td class="text-center fw-bold">{{ $sh->share_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($sh->payout_month)->locale('ar')->isoFormat('MMMM YYYY') }}</td>
                    <td class="fw-bold">{{ $sh->share_label }}</td>
                    <td dir="ltr" style="text-align:right;">{{ $sh->member_phone ?: '—' }}</td>
                    <td>
                        @if($sh->is_company)
                            <span class="text-primary fw-bold">سهم الشركة</span>
                        @elseif($sh->is_transferred)
                            <span class="fw-bold" style="color:#5b21b6;">بديل الشركة ({{ $sh->paid_months_before_transfer }} اسم)</span>
                            <div style="font-size:0.7rem; color:#0284c7;">
                                حق الشركة: {{ number_format($sh->company_claim_amount, 0) }} ج
                                {{ $sh->is_claim_recovered ? '(تم الاسترداد)' : '(لم يُسترد)' }}
                            </div>
                        @else
                            عضو
                        @endif
                    </td>
                    <td>
                        @if($sh->is_collected)
                            <span class="text-success fw-bold">
                                ✔ {{ $sh->is_company ? 'استلمت الشركة' : 'تم الصرف' }}
                                ({{ $sh->collected_at ? \Carbon\Carbon::parse($sh->collected_at)->format('Y/m/d') : '' }})
                            </span>
                        @else
                            <span class="text-muted">لم يصرف بعد</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Section 2: Collection Matrix (Members x Months) --}}
    <div class="sec-header">
        <i class="fa-solid fa-table-cells me-1 text-teal"></i> جدول متابعة التحصيل الشهري (مين دفع ومين لسه)
    </div>
    <table class="table-print matrix-table">
        <thead>
            <tr>
                <th style="width:36px;">دور</th>
                <th>العضو</th>
                @foreach($months as $m)
                    <th>{{ $m->locale('ar')->isoFormat('MMM YY') }}</th>
                @endforeach
                <th>المدفوع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jamia->shares as $sh)
                @php $paidMonths = 0; @endphp
                <tr class="{{ $sh->is_company ? 'company-row' : '' }}">
                    <td class="fw-bold">{{ $sh->share_number }}</td>
                    <td class="name-col">
                        {{ $sh->share_label }}
                        @if($sh->is_company) <small class="text-primary">(الشركة)</small> @endif
                    </td>
                    @if($sh->is_company)
                        <td colspan="{{ count($months) }}" class="cell-company">سهم {{ $companyName }} — لا يحتاج تحصيل نقدي</td>
                        <td class="cell-company">—</td>
                    @else
                        @php $reqMonths = 0; @endphp
                        @foreach($months as $m)
                            @php
                                $inst = $matrix[$sh->id][$m->toDateString()] ?? null;
                                $st   = $inst ? $inst->status : null;
                                if ($inst && !$inst->is_waived) {
                                    $reqMonths++;
                                    if ($inst->is_paid) $paidMonths++;
                                }
                            @endphp
                            @if(!$inst)
                                <td>—</td>
                            @elseif($inst->is_waived)
                                <td class="cell-company">تمت للشركة</td>
                            @elseif($st['key'] === 'paid' || $st['key'] === 'paid_late')
                                <td class="cell-paid">
                                    ✔ {{ $inst->paid_at ? \Carbon\Carbon::parse($inst->paid_at)->format('d/m') : '' }}
                                    @if($st['key'] === 'paid_late') <div style="font-size:.65rem;color:#991b1b;">متأخر</div> @endif
                                </td>
                            @elseif($st['key'] === 'late')
                                <td class="cell-late">✖ متأخر</td>
                            @elseif($st['key'] === 'open')
                                <td class="cell-open">⏳ لسه</td>
                            @else
                                <td class="text-muted">—</td>
                            @endif
                        @endforeach
                        <td class="fw-bold">{{ $paidMonths }} / {{ $reqMonths }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($jamia->notes)
        <div class="p-2 border rounded bg-light" style="font-size:.82rem;">
            <strong>ملاحظات:</strong> {{ $jamia->notes }}
        </div>
    @endif

</body>
</html>
