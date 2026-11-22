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
    $companyName = $settings['invoice_header_text'] ?? $settings['company_name'] ?? 'شركة الحمد للمقاولات والتشطيبات';
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

    <!-- 3. TOP FINANCIAL KPI BOXES -->
    <div class="row g-2 kpi-row">
        <div class="{{ ($personCreditBalance ?? 0) > 0 ? 'col-3' : 'col-4' }}">
            <div class="kpi-card due">
                <div class="kpi-card-label text-dark">{{ $opsTitle }}</div>
                <div class="kpi-card-val text-dark">
                    {{ number_format($totalOpsAmount, 2) }} ج.م
                </div>
            </div>
        </div>
        <div class="{{ ($personCreditBalance ?? 0) > 0 ? 'col-3' : 'col-4' }}">
            <div class="kpi-card paid">
                <div class="kpi-card-label text-muted">إجمالي المسدد</div>
                <div class="kpi-card-val text-success">
                    {{ number_format($totalPaidOnOps, 2) }} ج.م
                </div>
            </div>
        </div>
        <div class="{{ ($personCreditBalance ?? 0) > 0 ? 'col-3' : 'col-4' }}">
            <div class="kpi-card remaining">
                <div class="kpi-card-label text-danger">المتبقي المستحق له</div>
                <div class="kpi-card-val text-danger">
                    {{ number_format($totalRemainingOnOps, 2) }} ج.م
                </div>
            </div>
        </div>
        @if(($personCreditBalance ?? 0) > 0)
        <div class="col-3">
            <div class="kpi-card" style="background:#ecfdf5; border-color:#10b981;">
                <div class="kpi-card-label" style="color:#065f46; font-weight:800;">رصيد دائن لنا عنده</div>
                <div class="kpi-card-val" style="color:#047857;">
                    {{ number_format($personCreditBalance, 2) }} ج.م
                </div>
            </div>
        </div>
        @endif
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
                                    @if(floatval($op->returned_quantity ?? 0) > 0)
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 9.5px;">
                                            <i class="fa-solid fa-rotate-left me-1"></i>مرتجع: {{ floatval($op->returned_quantity) }} {{ in_array(trim($op->unit ?? ''), ['عدد', 'وحدة', 'وحده']) ? '' : $op->unit }}
                                        </span>
                                    @endif
                                    @if(!empty($op->notes))
                                        <div class="small text-muted fw-normal mt-1" style="font-size: 10.5px;">
                                            <i class="fa-regular fa-comment-dots text-primary me-1"></i>{!! nl2br(e($op->notes)) !!}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">
                                    {{ floatval($op->quantity) }} {{ in_array(trim($op->unit ?? ''), ['عدد', 'وحدة', 'وحده']) ? '' : $op->unit }}
                                    @if(floatval($op->returned_quantity ?? 0) > 0)
                                        <div class="text-danger fw-normal" style="font-size: 9.5px;">
                                            (أصل: {{ floatval($op->original_quantity ?: ($op->quantity + $op->returned_quantity)) }} - مرتجع: {{ floatval($op->returned_quantity) }})
                                        </div>
                                    @endif
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
                         @if($invItems->count() > 1)
                        <tr class="invoice-total-tr">
                            <td colspan="4" class="text-start pe-3">
                                إجمالي فاتورة <span class="font-monospace">#{{ $invRef }}</span> ({{ $invItems->count() }} صنف خامات)
                            </td>
                            <td class="text-center fw-black text-dark" dir="ltr">{{ number_format($invTotal, 2) }} ج.م</td>
                            <td class="text-center fw-black text-success" dir="ltr">{{ number_format($invPaid, 2) }} ج.م</td>
                            <td class="text-center fw-black {{ $invRem > 0 ? 'text-danger' : 'text-muted' }}" dir="ltr">{{ number_format($invRem, 2) }} ج.م</td>
                        </tr>
                         @endif
                    @endforeach
                @else
                    @foreach($projOps as $op)
                        <tr>
                            <td class="text-center text-muted" style="font-size: 11px;">
                                {{ date('Y-m-d', strtotime($op->created_at)) }}
                            </td>
                            <td class="text-start fw-bold">
                                {{ $op->name_or_title }}
                                @if(!empty($op->invoice_ref))
                                    <span class="badge bg-info-subtle text-dark border ms-1 font-monospace" style="font-size: 9.5px;">#{{ $op->invoice_ref }}</span>
                                @endif
                                @if(!empty($op->notes))
                                    <div class="small text-muted fw-normal mt-1" style="font-size: 10.5px;">
                                        <i class="fa-regular fa-comment-dots text-warning me-1"></i>{!! nl2br(e($op->notes)) !!}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center fw-bold">
                                {{ floatval($op->quantity) }} {{ in_array(trim($op->unit ?? ''), ['عدد', 'وحدة', 'وحده']) ? '' : $op->unit }}
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

                @if($projOps->count() > 1 && !($isSupplier && $invoicesInProject->count() === 1 && $invoicesInProject->first()->count() > 1))
                <tr class="scope-total-tr">
                    <td colspan="4" class="text-start pe-3 fw-bold">
                        إجمالي {{ $projTitle }} ({{ $projOps->count() }} حركة)
                    </td>
                    <td class="text-center fw-black text-dark" dir="ltr">{{ number_format($projTotal, 2) }}</td>
                    <td class="text-center fw-black text-success" dir="ltr">{{ number_format($projPaid, 2) }}</td>
                    <td class="text-center fw-black {{ $projRem > 0 ? 'text-danger' : 'text-muted' }}" dir="ltr">{{ number_format($projRem, 2) }}</td>
                </tr>
                @endif
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

            @if($groupedByProject->count() > 1 || $standaloneDebts->count() > 0 || $groupedByProject->first()?->count() > 1)
            <tr class="grand-total-tr">
                <td colspan="4" class="text-start pe-3 fw-black text-dark fs-6">
                    الإجمالي العام
                </td>
                <td class="text-center fw-black text-dark" dir="ltr">{{ number_format($totalOpsAmount, 2) }} ج.م</td>
                <td class="text-center fw-black text-success" dir="ltr">{{ number_format($totalPaidOnOps, 2) }} ج.م</td>
                <td class="text-center fw-black text-danger" dir="ltr">{{ number_format($totalRemainingOnOps, 2) }} ج.م</td>
            </tr>
            @endif
        </tbody>
    </table>

    @if($isSupplier && isset($supplierReturns) && $supplierReturns->count() > 0)
    <!-- 4.B SUPPLIER RETURNS HISTORY TABLE -->
    <div class="mb-3">
        <div class="section-label">
            <i class="fa-solid fa-rotate-left text-warning"></i>
            سجل المرتجعات والتسويات ({{ $supplierReturns->count() }} حركة مرتجع)
        </div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 12%;">التاريخ</th>
                    <th style="width: 14%;">رقم المرتجع</th>
                    <th style="width: 22%;" class="text-start">الخامة / الفاتورة</th>
                    <th style="width: 14%;">الكمية المرتجعة</th>
                    <th style="width: 14%;">قيمة المرتجع</th>
                    <th style="width: 24%;">طريقة التسوية / السبب</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplierReturns as $ret)
                    <tr>
                        <td>{{ $ret->created_at ? date('Y-m-d', strtotime($ret->created_at)) : '-' }}</td>
                        <td class="font-monospace fw-bold">{{ $ret->return_number ?: '-' }}</td>
                        <td class="text-start fw-bold">
                            {{ $ret->material_name }}
                            @if(!empty($ret->invoice_ref))
                                <div class="text-muted fw-normal" style="font-size: 10px;">فاتورة: #{{ $ret->invoice_ref }}</div>
                            @endif
                        </td>
                        <td class="fw-bold text-danger">
                            {{ floatval($ret->returned_qty) }} {{ in_array(trim($ret->unit ?? ''), ['عدد', 'وحدة', 'وحده']) ? '' : $ret->unit }}
                            <div class="text-muted fw-normal" style="font-size: 9.5px;">(من أصل {{ floatval($ret->original_qty) }} ← صافي {{ floatval($ret->remaining_qty) }})</div>
                        </td>
                        <td class="fw-black text-danger" dir="ltr">{{ number_format($ret->total_cost_returned, 2) }} ج.م</td>
                        <td class="small">
                            @if($ret->settlement_method === 'cash_refund')
                                <span class="badge bg-success text-white">استرداد نقدي ({{ number_format($ret->cash_refunded, 2) }} ج)</span>
                            @elseif($ret->settlement_method === 'supplier_credit')
                                <span class="badge bg-info text-dark">رصيد دائن عند المورد ({{ number_format($ret->supplier_credit, 2) }} ج)</span>
                            @elseif($ret->settlement_method === 'mixed')
                                <span class="badge bg-warning text-dark">تسوية مركبة (دين: {{ number_format($ret->debt_deducted, 0) }} + نقدي/رصيد: {{ number_format($ret->cash_refunded + $ret->supplier_credit, 0) }})</span>
                            @else
                                <span class="badge bg-secondary text-white">خصم من المديونية ({{ number_format($ret->debt_deducted, 2) }} ج)</span>
                            @endif
                            @if(!empty($ret->reason))
                                <div class="text-muted mt-1" style="font-size: 10px;">{{ $ret->reason }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr class="grand-total-tr">
                    <td colspan="4" class="text-start pe-3 fw-black">إجمالي قيمة الخامات المرتجعة للمورد</td>
                    <td class="text-center fw-black text-danger" dir="ltr">{{ number_format($supplierReturns->sum('total_cost_returned'), 2) }} ج.م</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- 5. PAYMENTS HISTORY TABLE -->
    <div class="mb-3">
        <div class="section-label">
            <i class="fa-solid fa-money-bill-wave text-success"></i>
            سجل الدفعات المسددة ({{ $paymentRecords->count() }} دفعة)
        </div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 7%;">#</th>
                    <th style="width: 15%;">التاريخ</th>
                    <th style="width: 28%;" class="text-start">المشروع / البند</th>
                    <th style="width: 30%;" class="text-start">البيان / الملاحظات</th>
                    <th style="width: 20%;">المبلغ المسدد</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentRecords as $pay)
                    <tr>
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td class="fw-bold">{{ $pay->date ? date('Y-m-d', strtotime($pay->date)) : '-' }}</td>
                        <td class="text-start fw-bold">
                            {{ $pay->project_name ?: '-' }}
                            @if(!empty($pay->item_title))
                                <span class="text-muted fw-normal small"> — {{ $pay->item_title }}</span>
                            @endif
                        </td>
                        <td class="text-start small">
                            {{ $pay->notes ?: '-' }}
                            @if(floatval($pay->discount ?? 0) > 0)
                                <div class="text-warning-emphasis fw-bold">
                                    خصم: {{ number_format($pay->discount, 2) }} ج.م
                                    @if(!empty($pay->discount_reason)) ({{ $pay->discount_reason }}) @endif
                                </div>
                            @endif
                        </td>
                        <td class="fw-bold text-success" dir="ltr">
                            @if(floatval($pay->amount) > 0)
                                {{ number_format($pay->amount, 2) }} ج.م
                            @elseif(floatval($pay->discount ?? 0) > 0)
                                <span class="text-warning-emphasis">{{ number_format($pay->discount, 2) }} ج.م (خصم)</span>
                            @else
                                0.00 ج.م
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-2 text-muted">
                            لا توجد دفعات سداد مسجلة حتى تاريخه
                        </td>
                    </tr>
                @endforelse
                @if($paymentRecords->count() > 0)
                    @php
                        $sumCashPaid = $paymentRecords->sum(fn($p) => floatval($p->amount ?? 0));
                        $sumDiscPaid = $paymentRecords->sum(fn($p) => floatval($p->discount ?? 0));
                        $sumCashRefundedFromReturns = ($isSupplier && isset($supplierReturns)) ? floatval($supplierReturns->sum('cash_refunded')) : 0.0;
                    @endphp
                    <tr class="grand-total-tr">
                        <td colspan="4" class="text-start pe-3 fw-black">
                            إجمالي الدفعات المسددة ({{ $paymentRecords->count() }} دفعة)
                            @if($sumDiscPaid > 0)
                                <span class="small fw-bold text-muted ms-2">(نقدي: {{ number_format($sumCashPaid, 2) }} ج + خصومات: {{ number_format($sumDiscPaid, 2) }} ج)</span>
                            @endif
                        </td>
                        <td class="text-center fw-black text-success" dir="ltr">{{ number_format($sumCashPaid + $sumDiscPaid, 2) }} ج.م</td>
                    </tr>
                    @if($sumCashRefundedFromReturns > 0)
                    <tr class="scope-total-tr">
                        <td colspan="4" class="text-start pe-3 fw-bold text-danger">
                            (-) مسترد نقداً من مرتجعات الخامات
                        </td>
                        <td class="text-center fw-black text-danger" dir="ltr">-{{ number_format($sumCashRefundedFromReturns, 2) }} ج.م</td>
                    </tr>
                    <tr class="grand-total-tr">
                        <td colspan="4" class="text-start pe-3 fw-black text-dark">
                            (=) صافي المسدد على التوريدات بعد استرداد المرتجع
                        </td>
                        <td class="text-center fw-black text-success" dir="ltr">{{ number_format(max(0, ($sumCashPaid + $sumDiscPaid) - $sumCashRefundedFromReturns), 2) }} ج.م</td>
                    </tr>
                    @endif
                @endif
            </tbody>
        </table>
    </div>

    @if(isset($creditLogs) && $creditLogs->count() > 0)
    <!-- 5.B SUPPLIER CREDIT BALANCE LOGS (دفعات زائدة وخصم تلقائي واسترداد) -->
    <div class="mb-3">
        <div class="section-label" style="color: #047857;">
            <i class="fa-solid fa-scale-balanced text-success"></i>
            سجل الرصيد الدائن لنا والدفعات الزائدة ({{ $creditLogs->count() }} حركة)
        </div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 18%;">التاريخ</th>
                    <th style="width: 22%;">نوع الحركة</th>
                    <th style="width: 18%;">المبلغ</th>
                    <th style="width: 18%;">الرصيد لنا بعدها</th>
                    <th class="text-start">البيان / التفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creditLogs as $cLog)
                    @php
                        $isCreditAdd = in_array($cLog->tx_type, ['overpayment', 'return_credit']);
                        $txLabel = match($cLog->tx_type) {
                            'overpayment'   => 'دفعة زائدة (رصيد لنا)',
                            'return_credit' => 'رصيد مرتجع خامات',
                            'auto_deduct'   => 'خصم تلقائي لفاتورة جديدة',
                            'cash_refund'   => 'استرداد نقدي للخزنة',
                            default         => 'حركة رصيد',
                        };
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $cLog->created_at ? substr($cLog->created_at, 0, 10) : '-' }}</td>
                        <td class="fw-bold {{ $isCreditAdd ? 'text-success' : 'text-primary' }}">{{ $txLabel }}</td>
                        <td class="fw-black {{ $isCreditAdd ? 'text-success' : 'text-danger' }}" dir="ltr">
                            {{ $isCreditAdd ? '+' : '-' }}{{ number_format($cLog->amount, 2) }} ج.م
                        </td>
                        <td class="fw-bold text-dark" dir="ltr">{{ number_format($cLog->balance_after, 2) }} ج.م</td>
                        <td class="text-start small">{{ $cLog->notes ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

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
        @if(($personCreditBalance ?? 0) > 0)
        <div class="final-settlement-row grand" style="background:#ecfdf5; border-top:1.5px solid #10b981;">
            <span class="fw-black" style="color:#065f46;"><i class="fa fa-hand-holding-dollar me-1"></i> رصيد دائن لنا عند ({{ $personName }})</span>
            <span class="fw-black" style="color:#047857;" dir="ltr">{{ number_format($personCreditBalance, 2) }} ج.م</span>
        </div>
        @endif
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
