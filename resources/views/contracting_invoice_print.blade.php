<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $doc['page_title'] ?? 'مستند مقاولات رسمي' }}</title>
    <!-- Cairo & JetBrains Mono Fonts & FontAwesome & Bootstrap RTL -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --brand-navy: #0f172a;
            --brand-navy-sub: #1e293b;
            --brand-gold: #b45309;
            --brand-gold-sub: #f59e0b;
            --brand-blue: #0284c7;
            --border-color: #e2e8f0;
            --bg-subtle: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f1f5f9;
            color: var(--text-dark);
            margin: 0;
            padding: 24px 12px;
            -webkit-font-smoothing: antialiased;
        }

        /* Interactive Screen Toolbar */
        .interactive-toolbar {
            max-width: 920px;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 10px 18px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        /* Printable Invoice Sheet */
        .invoice-sheet {
            max-width: 920px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
            border: 1px solid #e2e8f0;
            padding: 34px 38px 28px 38px;
            position: relative;
            box-sizing: border-box;
        }

        /* Top Dual Accent Lines */
        .top-accent-lines {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            flex-direction: column;
        }
        .accent-stripe-primary {
            height: 5px;
            background-color: var(--brand-navy);
        }
        .accent-stripe-gold {
            height: 3px;
            background-color: var(--brand-gold);
        }

        /* Header Layout */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 10px;
            margin-bottom: 24px;
        }

        .doc-title-zone {
            text-align: right;
        }

        .doc-title-main {
            font-size: 32px;
            font-weight: 900;
            color: var(--brand-navy);
            line-height: 1.1;
            margin: 0 0 4px 0;
            letter-spacing: -0.5px;
        }

        .doc-code-ref {
            font-family: 'Cairo', sans-serif;
            font-size: 14.5px;
            font-weight: 800;
            color: var(--brand-navy-sub);
            letter-spacing: 0;
            margin-bottom: 6px;
        }

        .doc-badge {
            display: inline-block;
            font-size: 12.5px;
            font-weight: 800;
            padding: 3px 12px;
            border-radius: 6px;
        }
        .doc-badge-success {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .doc-badge-primary {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .doc-badge-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* Company Identity */
        .company-identity {
            display: flex;
            align-items: center;
            gap: 14px;
            direction: ltr;
        }

        .company-logo-frame {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f59e0b;
            font-size: 26px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12);
        }

        .company-titles-box {
            text-align: right;
            direction: rtl;
        }

        .company-name-ar {
            font-size: 20px;
            font-weight: 900;
            color: var(--brand-navy);
            line-height: 1.2;
            margin: 0;
        }

        .company-tagline-en {
            font-size: 11px;
            font-weight: 700;
            color: var(--brand-gold);
            letter-spacing: 0.8px;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* Dossier & Details Section */
        .doc-meta-split {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            padding-top: 16px;
            margin-bottom: 24px;
            border-top: 1px solid #f1f5f9;
        }

        /* Right Column: Client & QR */
        .client-dossier-wrap {
            flex: 1.3;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .client-info-col {
            flex: 1;
        }

        .meta-subtitle-tiny {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .client-bold-name {
            font-size: 21px;
            font-weight: 900;
            color: var(--brand-navy);
            margin-bottom: 3px;
            line-height: 1.25;
        }

        .client-sub-project {
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 5px;
        }

        .client-phone-wrap {
            font-size: 13px;
            font-weight: 700;
            color: var(--brand-navy);
            font-family: 'JetBrains Mono', 'Cairo', sans-serif;
            direction: ltr;
            display: inline-block;
        }

        /* QR Frame */
        .qr-block-container {
            width: 95px;
            text-align: center;
            flex-shrink: 0;
        }

        .qr-frame {
            width: 86px;
            height: 86px;
            padding: 3px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .qr-frame img, .qr-frame svg {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .qr-caption-text {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.2;
        }

        /* Left Column: Metadata Key-Values */
        .doc-kv-details-col {
            flex: 0.9;
        }

        .meta-kv-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3.5px 0;
            font-size: 13px;
        }

        .meta-kv-key {
            color: var(--text-muted);
            font-weight: 600;
        }

        .meta-kv-val {
            color: var(--brand-navy);
            font-weight: 700;
        }

        .currency-bold-tag {
            font-weight: 800;
            color: var(--brand-navy);
        }

        /* Main Table */
        .official-doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .official-doc-table thead th {
            background-color: var(--brand-navy);
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            padding: 9px 8px;
            text-align: center;
            border: none;
            letter-spacing: 0.3px;
        }

        .official-doc-table thead th.col-desc-head {
            text-align: right;
            padding-right: 14px;
        }

        .official-doc-table thead th.col-total-head {
            text-align: left;
            padding-left: 14px;
        }

        .official-doc-table tbody td {
            padding: 9px 8px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }

        .official-doc-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .official-doc-table tbody td.col-idx {
            text-align: center;
            font-weight: 700;
            color: var(--text-muted);
            width: 40px;
        }

        .official-doc-table tbody td.col-code {
            text-align: center;
            font-family: 'JetBrains Mono', 'Cairo', monospace;
            font-size: 12px;
            font-weight: 700;
            color: #1e3a8a;
            width: 110px;
        }

        .official-doc-table tbody td.col-desc {
            text-align: right;
            font-weight: 700;
            padding-right: 14px;
        }

        .official-doc-table tbody td.col-qty {
            text-align: center;
            font-weight: 800;
            width: 80px;
        }

        .official-doc-table tbody td.col-unit {
            text-align: center;
            color: #475569;
            width: 80px;
        }

        .official-doc-table tbody td.col-unitprice {
            text-align: center;
            font-weight: 700;
            width: 100px;
        }

        .official-doc-table tbody td.col-linetotal {
            text-align: left;
            font-weight: 800;
            font-size: 13.5px;
            color: var(--brand-navy);
            padding-left: 14px;
            width: 120px;
        }

        /* Table Subtotal Summary Row */
        .table-subtotal-summary-row {
            background-color: #f1f5f9 !important;
            font-size: 13px;
            font-weight: 800;
        }

        .table-subtotal-summary-row td {
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 9px 8px !important;
        }

        /* Bottom Split: Terms & Totals */
        .bottom-split-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 28px;
            margin-top: 14px;
            margin-bottom: 20px;
        }

        .terms-conditions-box {
            flex: 1.25;
        }

        .terms-title-heading {
            font-size: 13px;
            font-weight: 800;
            color: var(--brand-navy);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .terms-list-items {
            margin: 0;
            padding-right: 18px;
            color: #475569;
            font-size: 12px;
            line-height: 1.75;
        }

        /* Totals Card */
        .totals-summary-card {
            flex: 1;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .total-kv-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 14px;
            font-size: 13px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .total-kv-row.grand-highlight {
            background-color: var(--brand-navy);
            color: #ffffff;
            font-weight: 900;
            font-size: 15.5px;
            padding: 10px 14px;
            border-bottom: none;
        }

        .total-kv-row.grand-highlight .highlight-amount {
            color: #f59e0b;
            font-size: 17.5px;
        }

        .total-kv-row.paid-row {
            background-color: #f0fdf4;
            color: #166534;
            font-weight: 700;
        }

        .total-kv-row.debt-row {
            background-color: #fef2f2;
            color: #991b1b;
            font-weight: 800;
        }

        /* Barcode Area */
        .barcode-strip {
            text-align: center;
            margin: 18px 0 10px 0;
        }

        .barcode-svg-holder {
            display: inline-block;
        }

        .barcode-text-caption {
            font-family: 'JetBrains Mono', 'Cairo', monospace;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 5px;
            color: var(--brand-navy);
            margin-top: 5px;
        }

        /* Official Footer Bar */
        .official-footer-strip {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 11.5px;
            color: #475569;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .footer-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Bottom Dual Accent Lines */
        .bottom-accent-lines {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            flex-direction: column;
        }

        /* Print Media Queries */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .interactive-toolbar, .no-print {
                display: none !important;
            }
            .invoice-sheet {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 10mm 12mm 10mm 12mm !important;
                margin: 0 !important;
                min-height: auto !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            tr {
                page-break-inside: avoid;
            }
            .bottom-split-wrap, .barcode-strip, .official-footer-strip {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

@php
    $companyName = $settings['invoice_header_text'] ?? 'المكتب الهندسي لإدارة المشروعات والتشطيبات';
    $companyPhone = $settings['company_phone'] ?? '01000000000';
    $statusClass = match($doc['status_color'] ?? 'success') {
        'success' => 'doc-badge-success',
        'danger'  => 'doc-badge-danger',
        default   => 'doc-badge-primary',
    };

    // Clean phone for WhatsApp button
    $rawPhone = preg_replace('/[^0-9]/', '', $doc['client_phone'] ?? '');
    $waPhone = $rawPhone;
    if (str_starts_with($waPhone, '01')) {
        $waPhone = '20' . substr($waPhone, 1);
    }
@endphp

<!-- 1. SCREEN TOOLBAR (NO-PRINT) -->
<div class="interactive-toolbar no-print">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ url('/contracting') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">
            <i class="fa fa-arrow-right me-1"></i> العودة للمقاولات
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold">
            <i class="fa fa-print me-1"></i> طباعة / تصدير PDF
        </button>
        @if($waPhone)
        <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode('مرحباً بك، تجد طيه رابط المستند الرسمي: ' . url()->current()) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
            <i class="fab fa-whatsapp me-1"></i> إرسال عبر واتساب
        </a>
        @endif
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if(isset($doc['view_mode']))
            <span class="small fw-bold text-muted">طريقة العرض:</span>
            <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
                <a href="?view=cost" class="btn {{ ($doc['view_mode'] ?? 'cost') === 'cost' ? 'btn-dark' : 'btn-light' }} fw-bold">بسعر الشراء (التكلفة)</a>
                <a href="?view=selling" class="btn {{ ($doc['view_mode'] ?? 'cost') === 'selling' ? 'btn-dark' : 'btn-light' }} fw-bold">بسعر البيع (للعميل)</a>
            </div>
        @endif
        <button onclick="navigator.clipboard.writeText(window.location.href); alert('تم نسخ رابط الوثيقة بنجاح!');" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
            <i class="fa fa-link me-1"></i> نسخ الرابط
        </button>
    </div>
</div>

<!-- 2. OFFICIAL INVOICE SHEET -->
<div class="invoice-sheet">

    <!-- Top Dual Accent Lines -->
    <div class="top-accent-lines">
        <div class="accent-stripe-primary"></div>
        <div class="accent-stripe-gold"></div>
    </div>

    <!-- MAIN BODY CONTENT -->
    <div>
        <!-- A. HEADER ROW -->
        <div class="doc-header">
            <!-- Left: Document Title, Code, Status -->
            <div class="doc-title-zone">
                <h1 class="doc-title-main">{{ $doc['title'] }}</h1>
                <div class="doc-code-ref">{{ $doc['code'] }}</div>
                <div class="doc-badge {{ $statusClass }}">
                    <i class="fa fa-circle-check me-1"></i>{{ $doc['status'] }}
                </div>
            </div>

            <!-- Right: Office / Company Identity -->
            <div class="company-identity">
                <div class="company-titles-box">
                    <h2 class="company-name-ar">{{ $companyName }}</h2>
                    <div class="company-tagline-en">ENGINEERING & CONTRACTING MANAGEMENT</div>
                </div>
            </div>
        </div>

        <!-- B. METADATA & PARTIES SECTION -->
        <div class="doc-meta-split">
            <!-- Right: Client Dossier & QR Code -->
            <div class="client-dossier-wrap">
                <div class="client-info-col">
                    <div class="meta-subtitle-tiny">
                        {{ $doc['type'] === 'material' ? 'المورد / جهة التوريد' : 'مقدم إلى العميل' }}
                    </div>
                    <div class="client-bold-name">{{ $doc['client_name'] }}</div>
                    @if(!empty($doc['project_title']))
                        <div class="client-sub-project">
                            <i class="fa fa-folder-open text-muted me-1"></i>{{ $doc['project_title'] }}
                        </div>
                    @endif
                    @if(!empty($doc['client_phone']))
                        <div class="client-phone-wrap">
                            <i class="fa fa-phone text-muted me-1"></i>{{ $doc['client_phone'] }}
                        </div>
                    @endif
                </div>

                <!-- QR Code Box -->
                <div class="qr-block-container">
                    <div class="qr-frame">
                        {!! $doc['qr_svg'] ?? \App\Http\Controllers\ContractingController::generateQrSvg($doc['code'] ?? 'DOC') !!}
                    </div>
                    <div class="qr-caption-text">
                        {{ $doc['type'] === 'quotation' ? 'امسح للتحقق من العرض' : 'امسح للتحقق من الفاتورة' }}
                    </div>
                </div>
            </div>

            <!-- Left: Document Details -->
            <div class="doc-kv-details-col">
                <div class="meta-subtitle-tiny">
                    تفاصيل {{ $doc['title'] }}
                </div>
                <div class="meta-kv-line">
                    <span class="meta-kv-key">تاريخ الإصدار:</span>
                    <span class="meta-kv-val">{{ $doc['issue_date'] }}</span>
                </div>
                <div class="meta-kv-line">
                    <span class="meta-kv-key">{{ $doc['type'] === 'quotation' ? 'صالح حتى:' : 'تاريخ الاستحقاق:' }}</span>
                    <span class="meta-kv-val">{{ $doc['valid_until'] }}</span>
                </div>
                <div class="meta-kv-line">
                    <span class="meta-kv-key">العملة:</span>
                    <span class="currency-bold-tag">{{ $doc['currency'] }} (EGP)</span>
                </div>
                @if(!empty($doc['project_code']))
                <div class="meta-kv-line">
                    <span class="meta-kv-key">كود المشروع:</span>
                    <span class="meta-kv-val">#PRJ-{{ $doc['project_code'] }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- C. ITEMS TABLE -->
        <table class="official-doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الكود</th>
                    <th class="col-desc-head">البيان / الوصف</th>
                    <th>الكمية</th>
                    <th>الوحدة</th>
                    <th>سعر الوحدة</th>
                    <th class="col-total-head">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @forelse($doc['items'] as $item)
                    <tr>
                        <td class="col-idx">{{ $item['index'] }}</td>
                        <td class="col-code">{{ $item['code'] }}</td>
                        <td class="col-desc">{{ $item['title'] }}</td>
                        <td class="col-qty">{{ number_format($item['qty'], $item['qty'] == intval($item['qty']) ? 0 : 2) }}</td>
                        <td class="col-unit">{{ in_array(trim($item['unit'] ?? ''), ['وحدة', 'وحده', 'عدد']) ? '-' : $item['unit'] }}</td>
                        <td class="col-unitprice">{{ number_format($item['unit_price'], 2) }}</td>
                        <td class="col-linetotal">{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">لا توجد بنود مدرجة في هذا المستند</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-subtotal-summary-row">
                    <td colspan="3" class="col-desc fw-black text-dark">
                        الإجمالي ({{ count($doc['items']) }} {{ count($doc['items']) == 1 ? 'صنف' : 'أصناف / بنود' }})
                    </td>
                    <td class="col-qty fw-black text-dark">
                        {{ number_format($doc['total_qty'], $doc['total_qty'] == intval($doc['total_qty']) ? 0 : 2) }}
                    </td>
                    <td colspan="2"></td>
                    <td class="col-linetotal fw-black text-primary">
                        {{ number_format($doc['subtotal'], 2) }} <span class="small">{{ $doc['currency'] }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- D. TERMS & FINAL TOTALS (BOTTOM SPLIT) -->
        <div class="bottom-split-wrap">
            @if(($doc['type'] ?? '') !== 'material' && !empty($doc['terms']))
            <!-- Right: Terms and Conditions -->
            <div class="terms-conditions-box">
                <div class="terms-title-heading">
                    <i class="fa fa-file-contract text-muted"></i> الشروط والأحكام
                </div>
                <ul class="terms-list-items">
                    @foreach($doc['terms'] as $term)
                        <li>{{ $term }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Left: Totals Summary Card -->
            <div class="totals-summary-card" style="{{ (($doc['type'] ?? '') === 'material' || empty($doc['terms'])) ? 'margin-right: auto; min-width: 340px; max-width: 440px;' : '' }}">
                <div class="total-kv-row">
                    <span class="text-muted fw-bold">المجموع</span>
                    <span class="fw-bold text-dark">{{ number_format($doc['subtotal'], 2) }} {{ $doc['currency'] }}</span>
                </div>

                @if(!empty($doc['supervision_amount']) && $doc['supervision_amount'] > 0)
                <div class="total-kv-row">
                    <span class="text-muted fw-bold">أتعاب الإشراف ({{ $doc['supervision_pct'] }}%)</span>
                    <span class="fw-bold text-primary">{{ number_format($doc['supervision_amount'], 2) }} {{ $doc['currency'] }}</span>
                </div>
                @endif

                @if(!empty($doc['discount_amount']) && $doc['discount_amount'] > 0)
                <div class="total-kv-row">
                    <span class="text-muted fw-bold">خصم خاص</span>
                    <span class="fw-bold text-danger">-{{ number_format($doc['discount_amount'], 2) }} {{ $doc['currency'] }}</span>
                </div>
                @endif

                @if(!empty($doc['tax_amount']) && $doc['tax_amount'] > 0)
                <div class="total-kv-row">
                    <span class="text-muted fw-bold">ضريبة القيمة المضافة ({{ $doc['tax_rate'] }}%)</span>
                    <span class="fw-bold text-dark">{{ number_format($doc['tax_amount'], 2) }} {{ $doc['currency'] }}</span>
                </div>
                @endif

                <!-- Highlighted Final Grand Total -->
                <div class="total-kv-row grand-highlight">
                    <span>الإجمالي النهائي</span>
                    <span class="highlight-amount">{{ number_format($doc['grand_total'], 2) }} {{ $doc['currency'] }}</span>
                </div>

                @if($doc['paid_amount'] !== null)
                <div class="total-kv-row paid-row">
                    <span>المبلغ المسدد</span>
                    <span>{{ number_format($doc['paid_amount'], 2) }} {{ $doc['currency'] }}</span>
                </div>
                @endif

                @if($doc['remaining_balance'] !== null)
                <div class="total-kv-row {{ $doc['remaining_balance'] > 0 ? 'debt-row' : 'paid-row' }}">
                    <span>{{ $doc['remaining_balance'] > 0 ? 'المتبقي المستحق' : 'حالة الحساب' }}</span>
                    <span>{{ $doc['remaining_balance'] > 0 ? number_format($doc['remaining_balance'], 2) . ' ' . $doc['currency'] : 'خالص السداد' }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- BOTTOM SECTIONS (BARCODE & OFFICIAL FOOTER) -->
    <div>
        <!-- E. BARCODE STRIP -->
        <div class="barcode-strip">
            <div class="barcode-svg-holder">
                {!! $doc['barcode_svg'] !!}
            </div>
            <div class="barcode-text-caption" dir="ltr" style="letter-spacing: 3px;">
                {{ $doc['serial'] ?? (preg_replace('/[^A-Z0-9\-]/', '', strtoupper($doc['code'] ?? '')) ?: 'DOC-2026') }}
            </div>
        </div>

        <!-- F. OFFICIAL FOOTER -->
        <div class="official-footer-strip">
            <div class="footer-item">
                <i class="fa fa-location-dot text-muted"></i>
                <span>المقر الرئيسي: القاهرة الجديدة - التجمع الخامس</span>
            </div>
            <div class="footer-item">
                <i class="fa fa-phone text-muted"></i>
                <span dir="ltr">(+20) {{ $companyPhone }}</span>
            </div>
            <div class="footer-item">
                <i class="fa fa-envelope text-muted"></i>
                <span>info@contracting-eg.com</span>
            </div>
            <div class="footer-item">
                <i class="fa fa-globe text-muted"></i>
                <span>www.contracting-eg.com</span>
            </div>
        </div>
    </div>

    <!-- Bottom Dual Accent Lines -->
    <div class="bottom-accent-lines">
        <div class="accent-stripe-gold"></div>
        <div class="accent-stripe-primary"></div>
    </div>

</div>

</body>
</html>
