<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب — {{ $project->name }} — {{ $settings['invoice_header_text'] ?? 'شركة المقاولات' }}</title>
    <!-- Bootstrap RTL & Font Awesome & Cairo Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-navy: #0d2350;
            --brand-royal: #1e3a8a;
            --brand-gold: #b45309;
            --border-light: #e2e8f0;
            --bg-zebra: #f8fafc;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 16px 8px;
            -webkit-font-smoothing: antialiased;
        }

        /* Screen Toolbar */
        .interactive-toolbar {
            max-width: 860px;
            margin: 0 auto 12px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 10px 16px;
            border: 1px solid var(--border-light);
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        /* Official Statement Paper (Matches Uploaded Screenshot Exactly) */
        .statement-sheet {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-light);
            padding: 24px 28px;
            position: relative;
        }

        /* Header Elements */
        .company-logo-box {
            width: 44px;
            height: 44px;
            background: var(--brand-gold);
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .company-title {
            font-size: 19px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.2;
        }

        .company-subtitle {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 2px;
        }

        .doc-title {
            font-size: 18px;
            font-weight: 900;
            color: var(--brand-navy);
        }

        .doc-meta {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        /* Meta Information Strip */
        .meta-strip {
            border-top: 1px solid var(--border-light);
            border-bottom: 1px solid var(--border-light);
            padding: 10px 4px;
            margin-bottom: 14px;
        }

        .meta-strip-label {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .meta-strip-val {
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Top 3 KPI Boxes */
        .kpi-row {
            margin-bottom: 16px;
        }

        .kpi-card {
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
            background: #ffffff;
        }

        .kpi-card.due {
            border: 1.5px solid #0f172a;
        }

        .kpi-card.paid {
            border: 1px solid #cbd5e1;
        }

        .kpi-card.remaining {
            border: 1px solid #fecaca;
            background-color: #fef2f2;
        }

        .kpi-card-label {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .kpi-card-val {
            font-size: 17px;
            font-weight: 900;
            direction: ltr;
        }

        /* Section Sub-heading */
        .section-label {
            font-size: 12px;
            font-weight: 800;
            color: #475569;
            margin-bottom: 6px;
        }

        /* Main Statement Table System */
        .statement-table {
            width: 100%;
            table-layout: fixed !important;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .statement-table thead th {
            background-color: var(--brand-navy);
            color: #ffffff;
            font-weight: 800;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #1e3a8a;
            font-size: 12px;
            white-space: nowrap;
        }

        .statement-table th.col-desc,
        .statement-table td.col-desc {
            text-align: right !important;
            padding-right: 14px !important;
        }

        .statement-table th.col-date,
        .statement-table td.col-date,
        .statement-table th.col-qty,
        .statement-table td.col-qty,
        .statement-table th.col-cost,
        .statement-table td.col-cost,
        .statement-table th.col-sup,
        .statement-table td.col-sup,
        .statement-table th.col-tot,
        .statement-table td.col-tot {
            text-align: center !important;
        }

        .statement-table tbody td {
            padding: 6px 8px;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
            color: #0f172a;
        }

        /* Zebra striping for easy line tracking like Excel */
        .statement-table tbody tr.data-row:nth-child(even) td {
            background-color: var(--bg-zebra);
        }

        .statement-table tbody tr.data-row:nth-child(odd) td {
            background-color: #ffffff;
        }

        /* Item name bold as requested */
        .statement-table td.cell-item-name {
            font-weight: 800;
            color: #0f172a;
            font-size: 12.5px;
        }

        /* Scope Header Bar (Solid Blue in Screenshot) */
        .statement-table tr.scope-header-tr td {
            background-color: var(--brand-royal) !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 13.5px !important;
            padding: 7px 12px !important;
            border: 1px solid var(--brand-royal) !important;
        }

        /* Subheader Bar (البنود الفرعية / المصنعيات in Screenshot) */
        .statement-table tr.sub-section-tr td {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            font-weight: 800 !important;
            font-size: 11px !important;
            padding: 4px 12px !important;
            border: 1px solid #cbd5e1 !important;
        }

        /* Scope Total Underlined Row */
        .statement-table tr.scope-total-tr td {
            border-top: 2px solid #0f172a !important;
            border-bottom: 2px solid #0f172a !important;
            font-weight: 900 !important;
            font-size: 13px !important;
            padding: 7px 10px !important;
            background-color: #ffffff !important;
        }

        /* General Expenses Header (Purple in Screenshot) */
        .statement-table tr.general-expenses-tr td {
            color: #6366f1 !important;
            font-weight: 800 !important;
            font-size: 12.5px !important;
            padding: 9px 12px 5px 12px !important;
            background-color: #ffffff !important;
            border-top: 1px dashed #cbd5e1;
        }

        /* Grand Total Row */
        .statement-table tr.grand-total-tr td {
            border-top: 2px solid #0f172a !important;
            border-bottom: 2px solid #0f172a !important;
            font-weight: 900 !important;
            font-size: 13.5px !important;
            padding: 8px 10px !important;
            background-color: #f8fafc !important;
        }

        /* Payment Schedule Table */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 16px;
        }

        .payment-table th {
            color: #64748b;
            font-weight: 700;
            font-size: 11.5px;
            padding: 6px 8px;
            border-bottom: 1px solid #cbd5e1;
            text-align: center;
        }

        .payment-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
        }

        /* Final Settlement Card (Bottom Left Box in Screenshot) */
        .final-settlement-card {
            border: 1.5px solid #0f172a;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
            margin-bottom: 20px;
            max-width: 360px;
        }

        .final-settlement-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 14px;
            font-size: 12.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .final-settlement-row.grand {
            font-size: 14.5px;
            font-weight: 900;
            border-bottom: none;
            padding: 8px 14px;
        }

        /* Footer */
        .doc-footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #64748b;
        }

        /* Print Specific Rules (Zero Gaps, Tight Fitting) */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .interactive-toolbar, .no-print {
                display: none !important;
            }
            .statement-sheet {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 6mm 10mm !important;
                margin: 0 !important;
            }
            .statement-table tr {
                page-break-inside: avoid;
            }
            .kpi-row, .meta-strip, .final-settlement-card, .doc-footer {
                page-break-inside: avoid;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

@php
    $companyName = $settings['invoice_header_text'] ?? 'شركة الضبع للمقاولات';
    $companyPhone = $settings['company_phone'] ?? '01113688108';
    $billableOps = $operations->where('is_billable', 1);

    // Group operations
    $materialsOps = $billableOps->where('op_type', 'material');
    $laborOps     = $billableOps->where('op_type', 'labor');
    $otherOps     = $billableOps->whereNotIn('op_type', ['material', 'labor']);

    /**
     * Compute Material Pricing & Supervision Breakdown
     * User requirement: تظهر سعر الخامة 100، 10 إشراف، الإجمالي 110
     */
    if (!function_exists('calcMatLine')) {
        function calcMatLine($op, $projectSupPct, $showSupervision = true) {
            $qty = floatval($op->quantity ?: 1);
            if ($qty <= 0) $qty = 1;
            $cost = floatval($op->cost_price ?: 0);
            $selling = floatval($op->selling_price ?: 0);
            $opSup = floatval($op->supervision_pct ?: 0);
            $pct = $opSup > 0 ? $opSup : floatval($projectSupPct ?: 0);

            if (!$showSupervision) {
                $unitMat = $selling > 0 ? $selling : $cost;
                $unitSup = 0;
                $unitTot = $unitMat;
            } elseif ($cost > 0 && $selling > $cost) {
                $unitMat = $cost;
                $unitSup = $selling - $cost;
                $unitTot = $selling;
            } elseif ($cost > 0 && ($selling == 0 || abs($selling - $cost) < 0.001)) {
                $unitMat = $cost;
                $unitSup = round($cost * ($pct / 100), 2);
                $unitTot = $unitMat + $unitSup;
            } elseif ($selling > 0 && $cost == 0) {
                if ($pct > 0) {
                    $unitMat = round($selling / (1 + ($pct / 100)), 2);
                    $unitSup = round($selling - $unitMat, 2);
                    $unitTot = $selling;
                } else {
                    $unitMat = $selling;
                    $unitSup = 0;
                    $unitTot = $selling;
                }
            } else {
                $unitMat = $cost > 0 ? $cost : $selling;
                $unitSup = 0;
                $unitTot = $unitMat;
            }

            return (object) [
                'qty'       => $qty,
                'unit_mat'  => $unitMat,
                'unit_sup'  => $unitSup,
                'unit_tot'  => $unitTot,
                'total_mat' => $unitMat * $qty,
                'total_sup' => $unitSup * $qty,
                'total_tot' => $unitTot * $qty,
            ];
        }
    }

    // Cumulative Totals
    $totalMatSum = 0;
    $totalSupSum = 0;
    $totalMatClient = 0;

    foreach ($materialsOps as $m) {
        $c = calcMatLine($m, $project->supervision_pct, $showSupervision);
        $totalMatSum += $c->total_mat;
        $totalSupSum += $c->total_sup;
        $totalMatClient += $c->total_tot;
    }

    $totalLaborClient = 0;
    foreach ($laborOps as $l) {
        $totalLaborClient += floatval($l->total_selling > 0 ? $l->total_selling : ($l->quantity * $l->selling_price));
    }

    $totalOtherClient = 0;
    foreach ($otherOps as $o) {
        $totalOtherClient += floatval($o->total_selling > 0 ? $o->total_selling : ($o->quantity * $o->selling_price));
    }

    $subtotalCustomer = $totalMatClient + $totalLaborClient + $totalOtherClient;
    $netAfterDiscount = max(0, $subtotalCustomer - $totalDiscounts);
    $taxAmount = $isTax ? round($netAfterDiscount * 0.14, 2) : 0;
    $finalGrandTotal = $netAfterDiscount + $taxAmount;
    $remainingOnCustomer = max(0, $finalGrandTotal - $totalPaid);

    // WhatsApp clean phone
    $rawPhone = preg_replace('/[^0-9]/', '', $project->client_phone ?? '');
    $waPhone = $rawPhone;
    if (str_starts_with($waPhone, '01')) {
        $waPhone = '20' . substr($waPhone, 1);
    }
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

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" id="supSwitch" {{ $showSupervision ? 'checked' : '' }} onchange="toggleOption('show_supervision', this.checked)">
            <label class="form-check-label small fw-bold" for="supSwitch">إظهار الإشراف</label>
        </div>
        <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" id="taxSwitch" {{ $isTax ? 'checked' : '' }} onchange="toggleOption('is_tax', this.checked)">
            <label class="form-check-label small fw-bold" for="taxSwitch">ضريبة 14%</label>
        </div>
    </div>
</div>

<!-- OFFICIAL STATEMENT SHEET -->
<div class="statement-sheet">

    <!-- 1. HEADER (Matches Uploaded Screenshot) -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <!-- Right side: Logo & Company Information -->
        <div class="d-flex align-items-center gap-2">
            <div class="company-logo-box">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <div class="company-title">{{ $companyName }}</div>
                <div class="company-subtitle">مقاولات وتشطيبات - القاهرة - هاتف {{ $companyPhone }}</div>
            </div>
        </div>

        <!-- Left side: Document Title & Metadata -->
        <div class="text-start">
            <div class="doc-title">كشف حساب</div>
            <div class="doc-meta">رقم: <span class="fw-bold text-dark font-monospace">INV-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span></div>
            <div class="doc-meta">التاريخ: <span class="fw-bold text-dark">{{ date('d/m/Y') }}</span></div>
        </div>
    </div>

    <!-- 2. CLIENT & PROJECT METADATA STRIP -->
    <div class="meta-strip">
        <div class="row g-2 align-items-center">
            <div class="col-4 text-end">
                <div class="meta-strip-label">العميل</div>
                <div class="meta-strip-val">{{ $project->client_name }}</div>
            </div>
            <div class="col-4 text-center">
                <div class="meta-strip-label">المشروع</div>
                <div class="meta-strip-val">
                    {{ $project->name }}
                    @if($project->area_sqm)
                        <span class="fw-bold small text-muted">- {{ number_format($project->area_sqm, 0) }} م²</span>
                    @endif
                </div>
            </div>
            <div class="col-4 text-start">
                <div class="meta-strip-label">العنوان</div>
                <div class="meta-strip-val">{{ $project->location ?: 'الموقع المعتمد' }}</div>
            </div>
        </div>
    </div>

    <!-- 3. TOP 3 FINANCIAL KPI BOXES (Matches Screenshot) -->
    <div class="row g-2 kpi-row">
        <!-- Box 1 (Right): إجمالي المستحق -->
        <div class="col-4">
            <div class="kpi-card due">
                <div class="kpi-card-label text-dark">إجمالي المستحق</div>
                <div class="kpi-card-val text-dark">
                    {{ number_format($finalGrandTotal, 2) }} ج.م
                </div>
            </div>
        </div>
        <!-- Box 2 (Center): المدفوع -->
        <div class="col-4">
            <div class="kpi-card paid">
                <div class="kpi-card-label text-muted">المدفوع</div>
                <div class="kpi-card-val text-success">
                    {{ number_format($totalPaid, 2) }} ج.م
                </div>
            </div>
        </div>
        <!-- Box 3 (Left): المتبقي -->
        <div class="col-4">
            <div class="kpi-card remaining">
                <div class="kpi-card-label text-danger">المتبقي</div>
                <div class="kpi-card-val text-danger">
                    {{ number_format($remainingOnCustomer, 2) }} ج.م
                </div>
            </div>
        </div>
    </div>

    <!-- 4. SECTION HEADING -->
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="section-label">تفاصيل المصروفات لكل بند</div>
        <div class="text-muted small" style="font-size: 11px;">كود المشروع: #PRJ-{{ $project->id }}</div>
    </div>

    <!-- 5. MAIN STATEMENT TABLE (Matches Screenshot Structure) -->
    <table class="statement-table">
        <colgroup>
            <col style="width: 14%;">
            <col style="width: 36%;">
            <col style="width: 11%;">
            <col style="width: 13%;">
            @if($showSupervision)
            <col style="width: 12%;">
            @endif
            <col style="width: 14%;">
        </colgroup>
        <thead>
            <tr>
                <th class="col-date">التاريخ</th>
                <th class="col-desc">البيان</th>
                <th class="col-qty">الكمية</th>
                <th class="col-cost">سعر الخامة</th>
                @if($showSupervision)
                <th class="col-sup">الإشراف</th>
                @endif
                <th class="col-tot">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @php $hasScopesWithData = false; @endphp

            @foreach($items as $item)
                @php
                    $itemMat = $materialsOps->where('item_id', $item->id);
                    $itemLab = $laborOps->where('item_id', $item->id);
                    $scopeCount = $itemMat->count() + $itemLab->count();

                    $scopeMatTotal = 0;
                    foreach ($itemMat as $m) {
                        $c = calcMatLine($m, $project->supervision_pct, $showSupervision);
                        $scopeMatTotal += $c->total_tot;
                    }

                    $scopeLabTotal = 0;
                    foreach ($itemLab as $l) {
                        $scopeLabTotal += floatval($l->total_selling > 0 ? $l->total_selling : ($l->quantity * $l->selling_price));
                    }

                    $scopeGrandTotal = $scopeMatTotal + $scopeLabTotal;
                @endphp

                @if($scopeCount > 0)
                    @php $hasScopesWithData = true; @endphp

                    <!-- Scope Title Bar (Solid Blue in Screenshot) -->
                    <tr class="scope-header-tr">
                        <td colspan="{{ $showSupervision ? 6 : 5 }}" class="text-end">
                            {{ $item->title }}
                        </td>
                    </tr>

                    <!-- A. Materials Under Scope (البنود الفرعية) -->
                    @if($itemMat->count() > 0)
                        <tr class="sub-section-tr">
                            <td colspan="{{ $showSupervision ? 6 : 5 }}" class="text-end">
                                البنود الفرعية
                            </td>
                        </tr>
                        @foreach($itemMat as $op)
                            @php
                                $c = calcMatLine($op, $project->supervision_pct, $showSupervision);
                            @endphp
                            <tr class="data-row">
                                <td class="col-date text-muted" style="font-size: 11px;">
                                    {{ date('Y-m-d', strtotime($op->created_at)) }}
                                </td>
                                <td class="col-desc cell-item-name">
                                    {{ $op->name_or_title }}
                                    @if($op->notes)
                                        <div class="small text-muted fw-normal" style="font-size: 10.5px;">{{ $op->notes }}</div>
                                    @endif
                                </td>
                                <td class="col-qty fw-bold">
                                    {{ floatval($c->qty) }}
                                </td>
                                <td class="col-cost" dir="ltr">
                                    {{ number_format($c->unit_mat, 2) }}
                                </td>
                                @if($showSupervision)
                                <td class="col-sup text-primary fw-bold" dir="ltr">
                                    {{ number_format($c->unit_sup, 2) }}
                                </td>
                                @endif
                                <td class="col-tot fw-bold" dir="ltr">
                                    {{ number_format($c->total_tot, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <!-- B. Labor Under Scope (المصنعيات) -->
                    @if($itemLab->count() > 0)
                        <tr class="sub-section-tr">
                            <td colspan="{{ $showSupervision ? 6 : 5 }}" class="text-end">
                                المصنعيات
                            </td>
                        </tr>
                        @foreach($itemLab as $op)
                            @php
                                $lPrice = floatval($op->selling_price > 0 ? $op->selling_price : $op->total_selling);
                                $lTotal = floatval($op->total_selling > 0 ? $op->total_selling : ($op->quantity * $lPrice));
                            @endphp
                            <tr class="data-row">
                                <td class="col-date text-muted" style="font-size: 11px;">
                                    {{ date('Y-m-d', strtotime($op->created_at)) }}
                                </td>
                                <td class="col-desc cell-item-name">
                                    {{ $op->name_or_title }}
                                    @if($op->person_name)
                                        <span class="badge bg-light text-secondary border fw-normal py-0 ms-1" style="font-size: 10px;">{{ $op->person_name }}</span>
                                    @endif
                                </td>
                                <td class="col-qty text-muted">{{ floatval($op->quantity ?: 1) }}</td>
                                <td class="col-cost text-muted">-</td>
                                @if($showSupervision)
                                <td class="col-sup text-muted">-</td>
                                @endif
                                <td class="col-tot fw-bold" dir="ltr">
                                    {{ number_format($lTotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <!-- Scope Subtotal Row (Matching Underline in Screenshot) -->
                    <tr class="scope-total-tr">
                        <td colspan="{{ $showSupervision ? 5 : 4 }}" class="text-start pe-3 fw-bold">
                            إجمالي بند {{ $item->title }}
                        </td>
                        <td class="col-tot fw-black text-dark" dir="ltr" style="font-size: 13px;">
                            {{ number_format($scopeGrandTotal, 2) }} ج.م
                        </td>
                    </tr>
                @endif
            @endforeach

            <!-- C. General Expenses / Unassigned Supplies (مصروفات عامة على المشروع in Screenshot) -->
            @php
                $itemIds = $items->pluck('id')->toArray();
                $generalMat = $materialsOps->whereNotIn('item_id', $itemIds);
                $generalLab = $laborOps->whereNotIn('item_id', $itemIds);
                $generalOps = $generalMat->concat($generalLab)->concat($otherOps);

                $generalTotal = 0;
                foreach ($generalMat as $m) {
                    $c = calcMatLine($m, $project->supervision_pct, $showSupervision);
                    $generalTotal += $c->total_tot;
                }
                foreach ($generalLab as $l) {
                    $generalTotal += floatval($l->total_selling > 0 ? $l->total_selling : ($l->quantity * $l->selling_price));
                }
                foreach ($otherOps as $o) {
                    $generalTotal += floatval($o->total_selling > 0 ? $o->total_selling : ($o->quantity * $o->selling_price));
                }
            @endphp

            @if($generalOps->count() > 0)
                <tr class="general-expenses-tr">
                    <td colspan="{{ $showSupervision ? 5 : 4 }}" class="text-start pe-3 fw-bold text-primary">
                        مصروفات عامة على المشروع
                    </td>
                    <td class="col-tot fw-bold text-primary" dir="ltr">
                        الإجمالي: {{ number_format($generalTotal, 2) }} ج.م
                    </td>
                </tr>

                @foreach($generalOps as $op)
                    @php
                        $isMat = ($op->op_type == 'material');
                        if ($isMat) {
                            $c = calcMatLine($op, $project->supervision_pct, $showSupervision);
                            $lineQty = floatval($c->qty);
                            $linePrice = $c->unit_mat;
                            $lineSup = $c->unit_sup;
                            $lineTot = $c->total_tot;
                        } else {
                            $lineQty = floatval($op->quantity ?: 1);
                            $linePrice = floatval($op->selling_price > 0 ? $op->selling_price : $op->total_selling);
                            $lineSup = 0;
                            $lineTot = floatval($op->total_selling > 0 ? $op->total_selling : ($lineQty * $linePrice));
                        }
                    @endphp
                    <tr class="data-row">
                        <td class="col-date text-muted" style="font-size: 11px;">
                            {{ date('Y-m-d', strtotime($op->created_at)) }}
                        </td>
                        <td class="col-desc cell-item-name">
                            {{ $op->name_or_title }}
                        </td>
                        <td class="col-qty">
                            {{ $lineQty }}
                        </td>
                        <td class="col-cost" dir="ltr">
                            {{ number_format($linePrice, 2) }}
                        </td>
                        @if($showSupervision)
                        <td class="col-sup text-primary" dir="ltr">
                            {{ $lineSup > 0 ? number_format($lineSup, 2) : '-' }}
                        </td>
                        @endif
                        <td class="col-tot fw-bold" dir="ltr">
                            {{ number_format($lineTot, 2) }}
                        </td>
                    </tr>
                @endforeach
            @endif

            <!-- D. Grand Total Operations Line (إجمالي المستحق حتى الآن) -->
            <tr class="grand-total-tr">
                <td colspan="{{ $showSupervision ? 5 : 4 }}" class="text-start pe-3 fw-black text-dark fs-6">
                    إجمالي المستحق حتى الآن
                </td>
                <td class="col-tot fw-black text-dark fs-6" dir="ltr">
                    {{ number_format($subtotalCustomer, 2) }} ج.م
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 6. PAYMENT SCHEDULE SECTION (Matches Screenshot: خطة السداد) -->
    <div class="mb-3">
        <div class="section-label">خطة السداد ({{ $customerPayments->count() }} دفعة)</div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th class="text-start pe-3">الدفعة</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customerPayments as $pIdx => $pay)
                    <tr>
                        <td class="text-start pe-3 fw-bold text-dark">
                            {{ $pay->notes ?: ('دفعة نقدية ' . ($loop->iteration)) }}
                        </td>
                        <td>{{ date('Y-m-d', strtotime($pay->created_at)) }}</td>
                        <td class="fw-bold text-success" dir="ltr">{{ number_format($pay->paid_amount, 2) }} ج.م</td>
                        <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2">مسددة</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-2 text-muted">
                            لم تُسجل دفعات سداد من العميل حتى تاريخه
                        </td>
                    </tr>
                @endforelse

                @if($remainingOnCustomer > 0)
                    <tr>
                        <td class="text-start pe-3 fw-bold text-danger">المتبقي المطلوب سداده</td>
                        <td>{{ date('Y-m-d') }}</td>
                        <td class="fw-bold text-danger" dir="ltr">{{ number_format($remainingOnCustomer, 2) }} ج.م</td>
                        <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">مستحقة للسداد</span></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- 7. FINAL RECONCILIATION SUMMARY BOX (Matches Bottom Box in Screenshot) -->
    <div class="final-settlement-card">
        <div class="final-settlement-row">
            <span class="fw-bold text-dark">إجمالي المستحق حتى الآن</span>
            <span class="fw-bold text-dark" dir="ltr">{{ number_format($finalGrandTotal, 2) }} ج.م</span>
        </div>
        <div class="final-settlement-row">
            <span class="fw-bold text-muted">المدفوع</span>
            <span class="fw-bold text-success" dir="ltr">{{ number_format($totalPaid, 2) }} ج.م</span>
        </div>
        <div class="final-settlement-row grand bg-light">
            <span class="fw-black text-dark">المتبقي المطلوب</span>
            <span class="fw-black text-danger" dir="ltr">{{ number_format($remainingOnCustomer, 2) }} ج.م</span>
        </div>
    </div>

    <!-- 8. FOOTER (Matches Screenshot) -->
    <div class="doc-footer">
        <div>توقيع الشركة: ________________________</div>
        <div>كشف تفصيلي معتمد بكل بند ومصروفاته وكمياته وتواريخه.</div>
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
    function toggleOption(param, val) {
        const url = new URL(window.location.href);
        url.searchParams.set(param, val ? '1' : '0');
        window.location.href = url.toString();
    }

    function shareWhatsApp() {
        const phone = '{{ $waPhone }}';
        if (!phone) {
            alert('رقم هاتف العميل غير مسجل في بيانات المشروع.');
            return;
        }

        const clientName = `{{ $project->client_name }}`;
        const projectName = `{{ $project->name }}`;
        const totalGrand = `{{ number_format($finalGrandTotal, 2) }}`;
        const totalPaid = `{{ number_format($totalPaid, 2) }}`;
        const remaining = `{{ number_format($remainingOnCustomer, 2) }}`;
        const company = `{{ $companyName }}`;

        let text = `السلام عليكم ورحمة الله وبركاته،\n`;
        text += `أستاذنا الفاضل/ *${clientName}* المحترم،\n`;
        text += `تحية طيبة وبعد،،\n\n`;
        text += `مرفق كشف الحساب والمستخلص المالي المعتمد لمشروع:\n`;
        text += `🏠 *${projectName}*\n`;
        text += `--------------------------------\n`;
        text += `• إجمالي المستحق حتى الآن: ${totalGrand} ج.م\n`;
        text += `• إجمالي المدفوع: ${totalPaid} ج.م\n`;
        text += `• *المتبقي المطلوب سداده: ${remaining} ج.م*\n`;
        text += `--------------------------------\n`;
        text += `شاكرين لسعادتكم حسن التعاون.\n`;
        text += `*${company}*\n`;

        window.open(`https://wa.me/${phone}?text=${encodeURIComponent(text)}`, '_blank');
    }

    function copySummary() {
        const summaryText = 
            `كشف حساب مشروع: {{ $project->name }}\n` +
            `العميل: {{ $project->client_name }}\n` +
            `إجمالي المستحق حتى الآن: {{ number_format($finalGrandTotal, 2) }} ج.م\n` +
            `المدفوع: {{ number_format($totalPaid, 2) }} ج.م\n` +
            `المتبقي المطلوب: {{ number_format($remainingOnCustomer, 2) }} ج.م`;

        navigator.clipboard.writeText(summaryText).then(() => {
            const toast = document.getElementById('copyToast');
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 2500);
        });
    }
</script>

</body>
</html>
