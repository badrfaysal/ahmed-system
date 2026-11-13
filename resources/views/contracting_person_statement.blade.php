<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $personType === 'supplier' ? 'كشف حساب مورد' : 'كشف حساب فني' }} - {{ $personName }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --ink-dark: #0f172a;
            --ink-muted: #64748b;
            --border-color: #cbd5e1;
            --bg-subtle: #f8fafc;
            --green-paid: #16a34a;
            --red-due: #dc2626;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #e2e8f0;
            color: var(--ink-dark);
            margin: 0;
            padding: 24px 12px 60px;
            font-size: 13px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .interactive-toolbar {
            max-width: 210mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border-radius: 14px;
            padding: 12px 18px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid #e2e8f0;
        }

        .statement-sheet {
            max-width: 210mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 14mm 14mm;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            border-radius: 6px;
            position: relative;
        }

        .company-logo-box {
            width: 44px;
            height: 44px;
            border: 1.5px solid var(--ink-dark);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 900;
            color: var(--ink-dark);
            background: #fff;
        }

        .company-title {
            font-size: 18px;
            font-weight: 900;
            color: var(--ink-dark);
            line-height: 1.2;
        }

        .company-subtitle {
            font-size: 11px;
            color: var(--ink-muted);
            font-weight: 600;
        }

        .doc-title {
            font-size: 21px;
            font-weight: 900;
            color: var(--ink-dark);
            line-height: 1.1;
        }

        .doc-meta {
            font-size: 11.5px;
            color: var(--ink-muted);
            font-weight: 600;
        }

        .meta-strip {
            border-top: 2px solid var(--ink-dark);
            border-bottom: 1.5px solid var(--border-color);
            padding: 10px 4px;
            margin-top: 12px;
            margin-bottom: 16px;
        }

        .meta-strip-label {
            font-size: 10.5px;
            color: var(--ink-muted);
            font-weight: 700;
            margin-bottom: 2px;
        }

        .meta-strip-val {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--ink-dark);
        }

        .kpi-row {
            margin-bottom: 18px;
        }

        .kpi-card {
            border-radius: 8px;
            padding: 10px 14px;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .kpi-card.due {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
        }

        .kpi-card.paid {
            background-color: #f0fdf4;
            border: 1.5px solid #86efac;
        }

        .kpi-card.remaining {
            background-color: #fef2f2;
            border: 1.5px solid #f87171;
        }

        .kpi-card-label {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .kpi-card-val {
            font-size: 17px;
            font-weight: 900;
            direction: ltr;
            display: inline-block;
        }

        .section-label {
            font-size: 13px;
            font-weight: 800;
            color: var(--ink-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 12px;
            border: 1.5px solid #94a3b8;
        }

        .statement-table thead th {
            background-color: #e2e8f0 !important;
            color: var(--ink-dark);
            font-weight: 900;
            padding: 8px 8px;
            border: 1px solid #94a3b8;
            font-size: 11.5px;
            white-space: nowrap;
        }

        .statement-table tbody td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        .statement-table tbody tr.scope-header-tr td {
            background-color: #1e293b !important;
            color: #ffffff !important;
            font-weight: 900;
            font-size: 13.5px;
            padding: 8px 12px;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }

        .statement-table tbody tr.invoice-header-tr td {
            background-color: #eff6ff !important;
            color: #1e3a8a !important;
            font-weight: 800;
            font-size: 12px;
            padding: 7px 12px;
            border-top: 1.5px solid #93c5fd;
            border-bottom: 1.5px solid #93c5fd;
        }

        .statement-table tbody tr.invoice-total-tr td {
            background-color: #f8fafc !important;
            font-weight: 800;
            font-size: 11.5px;
            border-top: 1px dashed #94a3b8;
            border-bottom: 1.5px solid #64748b;
            padding: 6px 10px;
        }

        .statement-table tbody tr.scope-total-tr td {
            background-color: #f1f5f9 !important;
            font-weight: 800;
            border-top: 1.5px solid #64748b;
            border-bottom: 2px solid #475569;
            padding: 7px 10px;
        }

        .statement-table tbody tr.grand-total-tr td {
            background-color: #e2e8f0 !important;
            font-weight: 900;
            font-size: 13px;
            border-top: 2px solid var(--ink-dark);
            border-bottom: 2px solid var(--ink-dark);
            padding: 9px 10px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 12px;
            border: 1px solid #cbd5e1;
        }

        .payment-table thead th {
            background-color: #f1f5f9 !important;
            color: var(--ink-dark);
            font-weight: 800;
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .payment-table tbody td {
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
        }

        .final-settlement-card {
            border: 1.5px solid #94a3b8;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .final-settlement-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12.5px;
        }

        .final-settlement-row:last-child {
            border-bottom: none;
        }

        .final-settlement-row.grand {
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 900;
        }

        .doc-footer {
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11.5px;
            color: var(--ink-muted);
            font-weight: 700;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .statement-sheet {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 4mm !important;
                box-shadow: none !important;
                border: none !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

@php
    $companyName = $settings['company_name'] ?? 'شركة الحمد للمقاولات والتشطيبات';
    $companyPhone = $settings['company_phone'] ?? '01000000000';
    $isSupplier = ($personType === 'supplier');
    $viewMode = request('view', 'detailed'); // detailed (default: every invoice with materials in detail) or summary
    $roleLabel = $isSupplier ? 'المورد' : 'الفني / الصنايعي';
    $docTitle = $isSupplier ? 'كشف حساب مورد مفصل' : 'كشف حساب فني';
    $opsTitle = $isSupplier ? 'إجمالي التوريدات والخامات' : 'إجمالي الأعمال والمصنعيات';

    $rawPhone = preg_replace('/[^0-9]/', '', $person->phone ?? '');
    $waPhone = $rawPhone;
    if (str_starts_with($waPhone, '01')) {
        $waPhone = '20' . substr($waPhone, 1);
    }

    $groupedByProject = $operations->groupBy(function ($op) {
        return $op->project_id ?: 0;
    });
@endphp

<!-- SCREEN TOOLBAR (NO-PRINT) -->
<div class="interactive-toolbar no-print">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ url('/contracting') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">
            <i class="fa fa-arrow-right me-1"></i> العودة للمقاولات
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">
            <i class="fa fa-print me-1"></i> طباعة / تصدير PDF
        </button>
        @if($waPhone)
        <button onclick="shareWhatsApp()" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
            <i class="fab fa-whatsapp me-1"></i> إرسال واتساب
        </button>
        @endif
        <button onclick="copySummary()" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
            <i class="fa fa-copy me-1"></i> نسخ ملخص الحساب
        </button>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <label class="small fw-bold text-nowrap mb-0"><i class="fa fa-filter text-primary me-1"></i>نطاق المشاريع:</label>
        <select class="form-select form-select-sm fw-bold rounded-pill px-3" style="min-width: 220px;" onchange="filterByProject(this.value)">
            <option value="">كل المشاريع (كشف حساب شامل)</option>
            @foreach($personProjects as $projOpt)
                <option value="{{ $projOpt->id }}" {{ (!empty($selectedProject) && $selectedProject->id == $projOpt->id) ? 'selected' : '' }}>
                    مشروع: {{ $projOpt->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<!-- OFFICIAL STATEMENT SHEET -->
<div class="statement-sheet">

    <!-- 1. HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="company-logo-box">
                <i class="fa-solid {{ $isSupplier ? 'fa-truck-field' : 'fa-user-gear' }}"></i>
            </div>
            <div>
                <div class="company-title">{{ $companyName }}</div>
                <div class="company-subtitle">مقاولات وتشطيبات - القاهرة - هاتف {{ $companyPhone }}</div>
            </div>
        </div>

        <div class="text-start">
            <div class="doc-title">{{ $docTitle }}</div>
            <div class="doc-meta">النطاق: <span class="fw-bold text-dark">{{ !empty($selectedProject) ? ('مشروع ' . $selectedProject->name) : 'شامل جميع المشاريع' }}</span></div>
            <div class="doc-meta">التاريخ: <span class="fw-bold text-dark">{{ date('d/m/Y') }}</span></div>
        </div>
    </div>

    <!-- 2. PERSON METADATA STRIP -->
    <div class="meta-strip">
        <div class="row g-2 align-items-center">
            <div class="col-4 text-end">
                <div class="meta-strip-label">{{ $roleLabel }}</div>
                <div class="meta-strip-val">{{ $personName }}</div>
            </div>
            <div class="col-4 text-center">
                <div class="meta-strip-label">التخصص / البيان</div>
                <div class="meta-strip-val">{{ $person->specialty ?: ($isSupplier ? 'توريد خامات ومواد بناء' : 'أعمال ومصنعيات تشطيب') }}</div>
            </div>
            <div class="col-4 text-start">
                <div class="meta-strip-label">رقم الهاتف</div>
                <div class="meta-strip-val" dir="ltr">{{ $person->phone ?: 'غير مسجل' }}</div>
            </div>
        </div>
    </div>

    <!-- 3. TOP 3 FINANCIAL KPI BOXES -->
    <div class="row g-2 kpi-row">
        <div class="col-4">
            <div class="kpi-card due">
                <div class="kpi-card-label text-dark">{{ $opsTitle }}</div>
                <div class="kpi-card-val text-dark">
                    {{ number_format($totalOpsAmount, 2) }} ج.م
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="kpi-card paid">
                <div class="kpi-card-label text-muted">إجمالي المسدد</div>
                <div class="kpi-card-val text-success">
                    {{ number_format($totalPaidOnOps, 2) }} ج.م
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="kpi-card remaining">
                <div class="kpi-card-label text-danger">المتبقي المستحق له</div>
                <div class="kpi-card-val text-danger">
                    {{ number_format($totalRemainingOnOps, 2) }} ج.م
                </div>
            </div>
        </div>
    </div>

    <!-- 4. OPERATIONS BREAKDOWN TABLE -->
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="section-label">
            <i class="fa-solid fa-list-check text-primary"></i>
            {{ $isSupplier ? 'تفاصيل الفواتير والخامات الموردة بالتفصيل' : 'تفاصيل الأعمال والمصنعيات المنفذة' }}
        </div>
        <div class="text-muted small" style="font-size: 11px;">
            @if($isSupplier)
                عدد الفواتير: {{ $operations->groupBy(fn($o) => ($o->project_id ?: 0) . '_' . ($o->invoice_ref ?: ('OP_' . $o->id)))->count() }} | إجمالي بنود الخامات: {{ $operations->count() }}
            @else
                عدد الحركات: {{ $operations->count() }}
            @endif
        </div>
    </div>

    <table class="statement-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 12%;">التاريخ</th>
                <th class="text-start" style="width: 28%;">{{ $isSupplier ? 'الخامة / الصنف بالتفصيل' : 'البيان / البند' }}</th>
                <th class="text-center" style="width: 12%;">الكمية</th>
                <th class="text-center" style="width: 12%;">سعر الوحدة</th>
                <th class="text-center" style="width: 12%;">الإجمالي</th>
                <th class="text-center" style="width: 12%;">المسدد</th>
                <th class="text-center" style="width: 12%;">المتبقي</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groupedByProject as $projId => $projOps)
                @php
                    $firstOp = $projOps->first();
                    $projTitle = $firstOp->project_name ?: 'أعمال عامة غير مرتبطة بمشروع';
                    $projTotal = $projOps->sum('total_cost');
                    $projPaid  = $projOps->sum('live_paid');
                    $projRem   = $projOps->sum('live_remaining');
                @endphp
                <tr class="scope-header-tr">
                    <td colspan="7" class="text-start">
                        <i class="fa-solid fa-building me-2"></i>مشروع: {{ $projTitle }}
                        @if(!empty($firstOp->client_name))
                            <span class="fw-normal small ms-2 opacity-75">(العميل: {{ $firstOp->client_name }})</span>
                        @endif
                    </td>
                </tr>

                @if($isSupplier)
                    @php
                        // Group project material operations by invoice_ref so each invoice shows all its materials in detail
                        $invoicesInProject = $projOps->groupBy(function ($op) {
                            return !empty($op->invoice_ref) ? ('INV_' . $op->invoice_ref) : ('SINGLE_' . $op->id);
                        });
                    @endphp

                    @foreach($invoicesInProject as $invKey => $invItems)
                        @php
                            $invFirst = $invItems->first();
                            $invRef   = $invFirst->invoice_ref ?: ('حركة #' . $invFirst->id);
                            $invDate  = date('Y-m-d', strtotime($invFirst->created_at));
                            $invTotal = $invItems->sum('total_cost');
                            $invPaid  = $invItems->sum('live_paid');
                            $invRem   = $invItems->sum('live_remaining');
                        @endphp

                        <!-- Invoice Header Row -->
                        <tr class="invoice-header-tr">
                            <td colspan="7" class="text-start">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <i class="fa-solid fa-file-invoice ms-1"></i>
                                        <span>فاتورة توريد خامات رقم: <span class="font-monospace fw-black">#{{ $invRef }}</span></span>
                                        <span class="mx-2 text-muted">|</span>
                                        <span>التاريخ: <span class="fw-black">{{ $invDate }}</span></span>
                                        <span class="mx-2 text-muted">|</span>
                                        <span>عدد الأصناف: <span class="fw-black">{{ $invItems->count() }} صنف</span></span>
                                    </div>
                                    <div>
                                        @if($invRem <= 0.01)
                                            <span class="badge bg-success text-white px-2">مسددة بالكامل</span>
                                        @elseif($invPaid > 0)
                                            <span class="badge bg-warning text-dark px-2">مسددة جزئياً</span>
                                        @else
                                            <span class="badge bg-danger text-white px-2">آجل (غير مسددة)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Detailed Materials Inside This Invoice -->
                        @foreach($invItems as $idx => $op)
                            <tr>
                                <td class="text-center text-muted" style="font-size: 11px;">
                                    {{ date('Y-m-d', strtotime($op->created_at)) }}
                                </td>
                                <td class="text-start fw-bold">
                                    <span class="text-muted small me-1">{{ $loop->iteration }}.</span>
                                    {{ $op->name_or_title }}
                                    @if(!empty($op->item_title))
                                        <span class="badge bg-light text-secondary border ms-1" style="font-size: 9.5px;">{{ $op->item_title }}</span>
                                    @endif
                                    @if(!empty($op->notes))
                                        <div class="small text-muted fw-normal" style="font-size: 10.5px;">{{ $op->notes }}</div>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">
                                    {{ floatval($op->quantity) }} {{ $op->unit }}
                                </td>
                                <td class="text-center" dir="ltr">
                                    {{ number_format($op->cost_price, 2) }}
                                </td>
                                <td class="text-center fw-bold" dir="ltr">
                                    {{ number_format($op->total_cost, 2) }}
                                </td>
                                <td class="text-center text-success fw-bold" dir="ltr">
                                    {{ number_format($op->live_paid, 2) }}
                                </td>
                                <td class="text-center {{ $op->live_remaining > 0 ? 'text-danger fw-bold' : 'text-muted' }}" dir="ltr">
                                    {{ number_format($op->live_remaining, 2) }}
                                </td>
                            </tr>
                        @endforeach

                        <!-- Invoice Subtotal Row -->
                        <tr class="invoice-total-tr">
                            <td colspan="4" class="text-start pe-3">
                                إجمالي فاتورة <span class="font-monospace">#{{ $invRef }}</span> ({{ $invItems->count() }} صنف خامات)
                            </td>
                            <td class="text-center fw-black text-dark" dir="ltr">{{ number_format($invTotal, 2) }} ج.م</td>
                            <td class="text-center fw-black text-success" dir="ltr">{{ number_format($invPaid, 2) }} ج.م</td>
                            <td class="text-center fw-black {{ $invRem > 0 ? 'text-danger' : 'text-muted' }}" dir="ltr">{{ number_format($invRem, 2) }} ج.م</td>
                        </tr>
                    @endforeach
                @else
                    @foreach($projOps as $op)
                        <tr>
                            <td class="text-center text-muted" style="font-size: 11px;">
                                {{ date('Y-m-d', strtotime($op->created_at)) }}
                            </td>
                            <td class="text-start fw-bold">
                                {{ $op->name_or_title }}
                                @if(!empty($op->item_title))
                                    <span class="badge bg-light text-secondary border ms-1" style="font-size: 9.5px;">{{ $op->item_title }}</span>
                                @endif
                                @if(!empty($op->invoice_ref))
                                    <span class="badge bg-info-subtle text-dark border ms-1 font-monospace" style="font-size: 9.5px;">#{{ $op->invoice_ref }}</span>
                                @endif
                                @if(!empty($op->notes))
                                    <div class="small text-muted fw-normal" style="font-size: 10.5px;">{{ $op->notes }}</div>
                                @endif
                            </td>
                            <td class="text-center fw-bold">
                                {{ floatval($op->quantity) }} {{ $op->unit }}
                            </td>
                            <td class="text-center" dir="ltr">
                                {{ number_format($op->cost_price, 2) }}
                            </td>
                            <td class="text-center fw-bold" dir="ltr">
                                {{ number_format($op->total_cost, 2) }}
                            </td>
                            <td class="text-center text-success fw-bold" dir="ltr">
                                {{ number_format($op->live_paid, 2) }}
                            </td>
                            <td class="text-center {{ $op->live_remaining > 0 ? 'text-danger fw-bold' : 'text-muted' }}" dir="ltr">
                                {{ number_format($op->live_remaining, 2) }}
                            </td>
                        </tr>
                    @endforeach
                @endif

                <tr class="scope-total-tr">
                    <td colspan="4" class="text-start pe-3 fw-bold">
                        إجمالي {{ $projTitle }} ({{ $projOps->count() }} حركة)
                    </td>
                    <td class="text-center fw-black text-dark" dir="ltr">{{ number_format($projTotal, 2) }}</td>
                    <td class="text-center fw-black text-success" dir="ltr">{{ number_format($projPaid, 2) }}</td>
                    <td class="text-center fw-black {{ $projRem > 0 ? 'text-danger' : 'text-muted' }}" dir="ltr">{{ number_format($projRem, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        لا توجد حركات مسجلة لهذا {{ $roleLabel }} حتى الآن
                    </td>
                </tr>
            @endforelse

            @if($standaloneDebts->count() > 0)
                <tr class="scope-header-tr">
                    <td colspan="7" class="text-start">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>مديونيات وأرصدة أخرى مسجلة في الحسابات العامة
                    </td>
                </tr>
                @foreach($standaloneDebts as $sd)
                    <tr>
                        <td class="text-center text-muted" style="font-size: 11px;">
                            {{ $sd->created_at ? date('Y-m-d', strtotime($sd->created_at)) : '-' }}
                        </td>
                        <td class="text-start fw-bold" colspan="3">
                            {{ $sd->reason ?: 'رصيد مديونية مسجل' }}
                        </td>
                        <td class="text-center fw-bold" dir="ltr">{{ number_format($sd->total_amount, 2) }}</td>
                        <td class="text-center text-success fw-bold" dir="ltr">{{ number_format($sd->paid_amount, 2) }}</td>
                        <td class="text-center {{ $sd->remaining_balance > 0 ? 'text-danger fw-bold' : 'text-muted' }}" dir="ltr">{{ number_format($sd->remaining_balance, 2) }}</td>
                    </tr>
                @endforeach
            @endif

            <tr class="grand-total-tr">
                <td colspan="4" class="text-start pe-3 fw-black text-dark fs-6">
                    الإجمالي العام
                </td>
                <td class="text-center fw-black text-dark" dir="ltr">{{ number_format($totalOpsAmount, 2) }} ج.م</td>
                <td class="text-center fw-black text-success" dir="ltr">{{ number_format($totalPaidOnOps, 2) }} ج.م</td>
                <td class="text-center fw-black text-danger" dir="ltr">{{ number_format($totalRemainingOnOps, 2) }} ج.م</td>
            </tr>
        </tbody>
    </table>

    <!-- 5. PAYMENTS HISTORY TABLE (التاريخ والمبلغ فقط) -->
    <div class="mb-3">
        <div class="section-label">
            <i class="fa-solid fa-money-bill-wave text-success"></i>
            سجل الدفعات ({{ $paymentRecords->count() }} دفعة)
        </div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 50%;">التاريخ</th>
                    <th style="width: 50%;">المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentRecords as $pay)
                    <tr>
                        <td class="fw-bold">{{ $pay->date ? date('Y-m-d', strtotime($pay->date)) : '-' }}</td>
                        <td class="fw-bold text-success" dir="ltr">{{ number_format($pay->amount, 2) }} ج.م</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center py-2 text-muted">
                            لا توجد دفعات سداد مسجلة حتى تاريخه
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 6. FINAL RECONCILIATION SUMMARY BOX -->
    <div class="final-settlement-card">
        <div class="final-settlement-row">
            <span class="fw-bold text-dark">{{ $opsTitle }}</span>
            <span class="fw-bold text-dark" dir="ltr">{{ number_format($totalOpsAmount, 2) }} ج.م</span>
        </div>
        <div class="final-settlement-row">
            <span class="fw-bold text-muted">إجمالي المسدد</span>
            <span class="fw-bold text-success" dir="ltr">{{ number_format($totalPaidOnOps, 2) }} ج.م</span>
        </div>
        <div class="final-settlement-row grand bg-light">
            <span class="fw-black text-dark">صافي المتبقي المستحق لـ ({{ $personName }})</span>
            <span class="fw-black {{ $totalRemainingOnOps > 0 ? 'text-danger' : 'text-success' }}" dir="ltr">{{ number_format($totalRemainingOnOps, 2) }} ج.م</span>
        </div>
    </div>

    <!-- 7. FOOTER -->
    <div class="doc-footer">
        <div>توقيع المحاسب / الشركة: ________________________</div>
        <div>توقيع المستلم ({{ $personName }}): ________________________</div>
    </div>

</div>

<!-- TOAST NOTIFICATION -->
<div id="copyToast" class="position-fixed bottom-0 start-50 translate-middle-x p-3 no-print" style="z-index: 9999; display: none;">
    <div class="toast show align-items-center text-white bg-dark border-0 shadow rounded-pill px-3" role="alert">
        <div class="toast-body fw-bold py-2">
            <i class="fa fa-check-circle text-success me-2"></i> تم نسخ ملخص كشف الحساب بنجاح!
        </div>
    </div>
</div>

<script>
    function filterByProject(projectId) {
        const url = new URL(window.location.href);
        if (projectId) {
            url.searchParams.set('project_id', projectId);
        } else {
            url.searchParams.delete('project_id');
        }
        window.location.href = url.toString();
    }

    function buildSummaryText() {
        const lines = [
            `📄 *{{ $docTitle }} - {{ $companyName }}*`,
            `👤 *{{ $roleLabel }}:* {{ $personName }}`,
            `🏗️ *النطاق:* {{ !empty($selectedProject) ? ('مشروع ' . $selectedProject->name) : 'شامل جميع المشاريع' }}`,
            `📅 *التاريخ:* {{ date('Y-m-d') }}`,
            `──────────────────`,
            `💰 *{{ $opsTitle }}:* {{ number_format($totalOpsAmount, 2) }} ج.م`,
            `✅ *إجمالي المسدد:* {{ number_format($totalPaidOnOps, 2) }} ج.م`,
            `📌 *المتبقي المستحق:* {{ number_format($totalRemainingOnOps, 2) }} ج.م`,
        ];
        return lines.join('\n');
    }

    function copySummary() {
        navigator.clipboard.writeText(buildSummaryText()).then(() => {
            const t = document.getElementById('copyToast');
            t.style.display = 'block';
            setTimeout(() => { t.style.display = 'none'; }, 2800);
        });
    }

    function shareWhatsApp() {
        const phone = '{{ $waPhone }}';
        const text = encodeURIComponent(buildSummaryText());
        window.open(`https://wa.me/${phone}?text=${text}`, '_blank');
    }
</script>

</body>
</html>
