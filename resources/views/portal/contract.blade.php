@extends('portal.layout')

@section('title', 'تفاصيل العقد')

@section('extra-head')
<style>
    .breadcrumb-portal {
        background: transparent; padding: 0; margin-bottom: 18px;
        font-weight: 500; font-size: 0.88rem;
    }
    .breadcrumb-portal a { color: var(--c-accent); text-decoration: none; }
    .breadcrumb-portal a:hover { text-decoration: underline; }
    .breadcrumb-portal .sep { color: var(--c-text-soft); margin: 0 6px; }

    .invoice-head {
        background: linear-gradient(135deg, var(--c-navy), var(--c-navy-soft));
        color: #fff; border-radius: var(--r-lg) var(--r-lg) 0 0;
        padding: 24px 28px;
    }
    .invoice-head h3 { margin: 0 0 6px; font-weight: 600; color: #fff; }
    .invoice-head .sub { color: rgba(255,255,255,0.75); font-weight: 400; font-size: 0.9rem; }
    .invoice-body {
        background: var(--c-surface);
        border-radius: 0 0 var(--r-lg) var(--r-lg);
        padding: 24px 28px; border: 1px solid var(--c-border); border-top: none;
    }

    /* تنسيق عمودي ضيّق — صف واحد لكل بيان */
    .portal-narrow { max-width: 720px; margin: 0 auto; }

    .info-grid { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
    .info-item {
        background: var(--c-bg); border: 1px solid var(--c-border); border-radius: var(--r-md);
        padding: 12px 16px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .info-item .lbl { font-size: 0.85rem; font-weight: 500; color: var(--c-text-soft); }
    .info-item .val { font-size: 1rem; font-weight: 700; color: var(--c-navy); font-feature-settings: 'tnum'; text-align: left; }

    .pay-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    .pay-table th { background: var(--c-navy); color: #fff; padding: 12px; text-align: center; font-weight: 600; font-size: 0.84rem; }
    .pay-table td { padding: 10px 12px; border-bottom: 1px solid var(--c-border); text-align: center; font-weight: 500; color: var(--c-text); font-size: 0.9rem; }
    .pay-table tbody tr:hover { background: var(--c-navy-50); }
    .pay-table .amount { color: var(--c-success); font-weight: 600; font-feature-settings: 'tnum'; }

    @media print {
        .portal-header, .portal-footer, .breadcrumb-portal, .no-print { display: none !important; }
        body { background: #fff; }
        .invoice-head { background: #fff !important; color: #000 !important; border: 1px solid #000; }
        .invoice-head h3, .invoice-head .sub { color: #000 !important; }
    }
</style>
@endsection

@section('content')

@php
    // المدفوع = الإجمالي - المتبقي
    $totalContract = (float) $contract->total_after_interest;
    $totalPaid     = max(0, $totalContract - (float) $contract->remaining_balance);
    $isPaid        = $contract->remaining_balance <= 0;
@endphp

<div class="portal-narrow">
<div class="breadcrumb-portal">
    <a href="{{ route('portal.dashboard') }}"><i class="fa fa-chevron-right ms-1"></i>صفحتي</a>
    <span class="sep">/</span>
    <span>عقد #{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}</span>
</div>

<div class="invoice-head">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h3>{{ $contract->product_name }}</h3>
            <div class="sub">عقد رقم: #{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }} · بتاريخ {{ \Carbon\Carbon::parse($contract->created_at)->format('Y/m/d') }}</div>
        </div>
        <button onclick="window.print()" class="btn-pro btn-pro-success no-print">
            <i class="fa fa-print"></i> طباعة
        </button>
    </div>
</div>

<div class="invoice-body">

    {{-- معلومات أساسية --}}
    <div class="info-grid">
        <div class="info-item">
            <div class="lbl">العميل</div>
            <div class="val">{{ $contract->customer_name }}</div>
        </div>
        <div class="info-item">
            <div class="lbl">المنتج / الخدمة</div>
            <div class="val">{{ $contract->product_name }}</div>
        </div>
        <div class="info-item">
            <div class="lbl">تاريخ البدء</div>
            <div class="val">{{ \Carbon\Carbon::parse($contract->start_date ?? $contract->created_at)->format('Y/m/d') }}</div>
        </div>
    </div>

    {{-- ملخص مالي --}}
    <h6 class="fw-bold mt-3 mb-3" style="color: var(--c-navy);">
        <i class="fa fa-receipt me-1" style="color: var(--c-accent);"></i> الملخص المالي
    </h6>

    <div class="info-grid">
        <div class="info-item">
            <div class="lbl">السعر كاش</div>
            <div class="val">{{ number_format($contract->cash_price, 0) }} ج</div>
        </div>
        @if($contract->installment_months > 0)
            @php
                $laterPaymentsForInfo = (float) $payments->sum('amount_paid');
                $actualDownForInfo    = max(0, $totalPaid - $laterPaymentsForInfo);
            @endphp
            <div class="info-item">
                <div class="lbl">إجمالي العقد (بعد الفائدة)</div>
                <div class="val">{{ number_format($contract->total_after_interest, 0) }} ج</div>
            </div>
            <div class="info-item">
                <div class="lbl">المقدم المدفوع</div>
                <div class="val" style="color: var(--c-success);">{{ number_format($actualDownForInfo, 0) }} ج</div>
            </div>
            <div class="info-item">
                <div class="lbl">مدة التقسيط</div>
                <div class="val">{{ $contract->installment_months }} شهر</div>
            </div>
            <div class="info-item">
                <div class="lbl">القسط الشهري</div>
                <div class="val" style="color: var(--c-accent);">{{ number_format($contract->monthly_installment, 0) }} ج</div>
            </div>
            <div class="info-item">
                <div class="lbl">يوم الاستحقاق</div>
                <div class="val">يوم {{ (int) $contract->due_day }} من كل شهر</div>
            </div>
        @endif
        <div class="info-item" style="background: var(--c-success-bg); border-color: rgba(45,134,89,0.2);">
            <div class="lbl">إجمالي المدفوع</div>
            <div class="val" style="color: var(--c-success);">{{ number_format($totalPaid, 0) }} ج</div>
        </div>
        <div class="info-item" style="background: {{ $isPaid ? 'var(--c-success-bg)' : 'var(--c-danger-bg)' }};">
            <div class="lbl">المتبقي</div>
            <div class="val" style="color: {{ $isPaid ? 'var(--c-success)' : 'var(--c-danger)' }};">
                @if($isPaid)
                    <i class="fa fa-check-circle me-1"></i> مسدد بالكامل
                @else
                    {{ number_format($contract->remaining_balance, 0) }} ج
                @endif
            </div>
        </div>
    </div>

    {{-- سجل المدفوعات --}}
    <h6 class="fw-bold mt-4 mb-3" style="color: var(--c-navy);">
        <i class="fa fa-clock-rotate-left me-1" style="color: var(--c-info);"></i> سجل المدفوعات
    </h6>

    @php
        $laterPayments    = (float) $payments->sum('amount_paid');
        $actualDownPaid   = max(0, $totalPaid - $laterPayments);
        $hasAnyPayment    = $actualDownPaid > 0 || $payments->isNotEmpty();
    @endphp

    @if($hasAnyPayment)
        <table class="pay-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>تاريخ الدفع</th>
                    <th>نوع</th>
                    <th>المبلغ</th>
                    <th>طريقة الدفع</th>
                </tr>
            </thead>
            <tbody>
                @if($actualDownPaid > 0)
                    <tr>
                        <td>1</td>
                        <td>{{ \Carbon\Carbon::parse($contract->created_at)->format('Y/m/d') }}</td>
                        <td>
                            @if($contract->installment_months > 0)
                                <span class="pill-pro pill-warning">مقدم</span>
                            @else
                                <span class="pill-pro pill-success">دفعة نقدية</span>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($actualDownPaid, 0) }} ج</td>
                        <td>—</td>
                    </tr>
                @endif
                @foreach($payments as $i => $p)
                    <tr>
                        <td>{{ $actualDownPaid > 0 ? $i + 2 : $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('Y/m/d') }}</td>
                        <td><span class="pill-pro pill-info">قسط</span></td>
                        <td class="amount">{{ number_format($p->amount_paid, 0) }} ج</td>
                        <td>{{ $p->account_name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: var(--c-navy-50); font-weight: 600;">
                    <td colspan="3" class="text-end">إجمالي المدفوع:</td>
                    <td class="amount" style="font-size: 1.02rem;">{{ number_format($totalPaid, 0) }} ج</td>
                    <td>—</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="empty-pro">
            <i class="fa-regular fa-folder-open"></i>
            <p>مفيش مدفوعات مسجلة لحد دلوقتي</p>
        </div>
    @endif

    @if(!$isPaid && $contract->installment_months > 0)
        <div class="alert-pro warning mt-4">
            <i class="fa fa-info-circle"></i>
            <div><strong>للسداد:</strong> توجه لمقر الشركة أو اتصل بنا في أوقات العمل الرسمية.</div>
        </div>
    @endif

</div>
</div>{{-- /portal-narrow --}}

@endsection
