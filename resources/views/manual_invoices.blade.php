<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفواتير اليدوية - شركة الضبع</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/html2canvas/html2canvas.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'blue'])

    <style>
        .mi-nav-tabs {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 14px;
            padding: 6px;
            display: inline-flex;
            gap: 6px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        .mi-nav-tabs .nav-link {
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.92rem;
            color: var(--c-text-soft);
            padding: 9px 20px;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.18s ease;
        }
        .mi-nav-tabs .nav-link:hover {
            color: var(--c-text);
            background: rgba(59, 130, 246, 0.06);
        }
        .mi-nav-tabs .nav-link.active {
            background: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
        }

        .mi-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            padding: 22px;
            margin-bottom: 22px;
        }
        .mi-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding-bottom: 14px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--c-border);
        }
        .mi-section-title {
            font-size: 1.08rem;
            font-weight: 800;
            color: var(--c-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mi-items-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 800;
            font-size: 0.84rem;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .mi-items-table td {
            vertical-align: middle;
            padding: 8px 6px;
        }
        .mi-summary-box {
            background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            padding: 16px 18px;
        }
        .mi-tax-toggle-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 12px;
            transition: all 0.2s ease;
        }
        .mi-tax-toggle-box.tax-active {
            background: #eff6ff;
            border-color: #3b82f6;
        }
        .mi-row-deleted {
            background-color: rgba(239, 68, 68, 0.06) !important;
        }
        .mi-row-deleted td {
            border-bottom-color: rgba(239, 68, 68, 0.18) !important;
        }
        .mi-row-edited {
            background-color: rgba(245, 158, 11, 0.06) !important;
        }
        .mi-badge-deleted {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .mi-badge-edited {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fcd34d;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .mi-badge-active {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #6ee7b7;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .mi-filter-pill {
            border: 1px solid var(--c-border);
            background: var(--c-surface);
            color: var(--c-text-soft);
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .mi-filter-pill.active {
            background: #1e40af;
            color: #fff;
            border-color: #1e40af;
        }

        /* ══════ Official Printable / Capture Invoice Sheet ══════ */
        .official-invoice-canvas {
            width: 820px;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 30px 34px 26px 34px;
            position: relative;
            font-family: 'Cairo', 'IBM Plex Sans Arabic', sans-serif;
            box-sizing: border-box;
            direction: rtl;
        }
        .inv-top-stripes {
            position: absolute;
            top: 0; left: 0; right: 0;
            border-radius: 8px 8px 0 0;
            overflow: hidden;
        }
        .inv-stripe-navy { height: 6px; background: #0f172a; }
        .inv-stripe-gold { height: 3px; background: #d97706; }

        .inv-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-top: 6px;
            padding-bottom: 16px;
            border-bottom: 2px solid #0f172a;
        }
        .inv-doc-title {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .inv-doc-num {
            font-size: 14px;
            font-weight: 800;
            color: #334155;
            margin-bottom: 6px;
        }
        .inv-company-box {
            text-align: left;
        }
        .inv-company-name {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
        }
        .inv-company-sub {
            font-size: 11px;
            font-weight: 700;
            color: #b45309;
        }
        .inv-client-meta-grid {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 16px;
            margin: 16px 0 18px 0;
        }
        .inv-client-card {
            flex: 1.35;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-right: 4px solid #0f172a;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .inv-meta-card {
            flex: 0.95;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .inv-table thead th {
            background: #0f172a !important;
            color: #ffffff !important;
            font-weight: 800;
            font-size: 13px;
            padding: 9px 10px;
            border: 1px solid #0f172a;
            text-align: center;
        }
        .inv-table thead th.text-right { text-align: right; }
        .inv-table tbody td {
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
        }
        .inv-table tbody td.text-right {
            text-align: right;
            font-weight: 800;
            color: #0f172a;
        }
        .inv-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .inv-bottom-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-top: 10px;
        }
        .inv-notes-terms-col {
            flex: 1.25;
        }
        .inv-totals-col {
            flex: 0.95;
            min-width: 280px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .inv-total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13.5px;
            font-weight: 700;
            border-bottom: 1px dashed #cbd5e1;
        }
        .inv-total-line.grand {
            background: #0f172a !important;
            color: #ffffff !important;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 15.5px;
            font-weight: 900;
            margin-top: 8px;
            border-bottom: none;
        }
        .inv-watermark-deleted {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-18deg);
            border: 5px solid rgba(220, 38, 38, 0.32);
            color: rgba(220, 38, 38, 0.32);
            font-size: 44px;
            font-weight: 900;
            padding: 10px 32px;
            border-radius: 14px;
            pointer-events: none;
            z-index: 10;
            white-space: nowrap;
        }

        /* ══════ Dedicated Print Container ══════ */
        #printOnlyContainer {
            display: none;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }
            .app-sidebar,
            .sb-mobile-launcher,
            .sb-mobile-backdrop,
            .main-content,
            .modal,
            .modal-backdrop,
            .swal2-container,
            .no-print {
                display: none !important;
            }
            #printOnlyContainer {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            #printOnlyContainer .official-invoice-canvas {
                width: 100% !important;
                max-width: 100% !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 20px 24px !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            tr, .inv-bottom-grid, .inv-totals-col {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@include('sidebar')

{{-- حاوية الطباعة النظيفة المستقلة (تظهر فقط عند الطباعة لتخرج صفحة A4 مثالية) --}}
<div id="printOnlyContainer"></div>

<div class="main-content">

    {{-- Header --}}
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 no-print">
        <div>
            <h2 class="mb-1"><i class="fa-solid fa-file-invoice text-primary me-2"></i>الفواتير اليدوية</h2>
            <div class="subtitle">إنشاء فواتير مبيعات أو فواتير ضريبية للعملاء، طباعتها أو حفظها كصورة وإرسالها عبر واتساب</div>
        </div>
        {{-- Tabs Navigation --}}
        <ul class="nav mi-nav-tabs mb-0" id="miMainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="btn-tab-create" data-bs-toggle="tab" data-bs-target="#pane-create" type="button" role="tab">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <span id="tabCreateTitle">إنشاء فاتورة جديدة</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="btn-tab-log" data-bs-toggle="tab" data-bs-target="#pane-log" type="button" role="tab">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>سجل الفواتير</span>
                    <span class="badge bg-white text-dark rounded-pill ms-1">{{ $stats['total'] }}</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content no-print" id="miTabsContent">

        {{-- ════════════════════════════════════════════════════════════════
             TAB 1: إنشاء / تعديل فاتورة يدوية
             ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="pane-create" role="tabpanel">
            <div class="mi-card" id="invoiceFormCard">
                <div class="mi-section-head">
                    <h5 class="mi-section-title" id="formCardTitle">
                        <i class="fa-solid fa-file-circle-plus text-primary"></i>
                        <span>بيانات الفاتورة والعميل</span>
                    </h5>
                    <div id="editModeBanner" class="d-none align-items-center gap-2">
                        <span class="mi-badge-edited">
                            <i class="fa fa-pen-to-square"></i> وضع تعديل الفاتورة: <strong id="editModeInvNum"></strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold" onclick="resetInvoiceForm()">
                            <i class="fa fa-xmark me-1"></i> إلغاء التعديل وإنشاء جديد
                        </button>
                    </div>
                </div>

                <form id="manualInvoiceForm" onsubmit="submitManualInvoice(event)">
                    <input type="hidden" id="mi_id" value="">

                    {{-- أ. بيانات العميل والفاتورة --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">مسمى المستند / الفاتورة</label>
                            <input type="text" id="mi_title" class="form-control fw-bold" value="فاتورة مبيعات" list="docTitlesList" placeholder="مثال: فاتورة مبيعات / بيان أسعار">
                            <datalist id="docTitlesList">
                                <option value="فاتورة مبيعات">
                                <option value="فاتورة ضريبية">
                                <option value="بيان أسعار وتوريد">
                                <option value="عرض سعر">
                                <option value="فاتورة أعمال وتشطيبات">
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">اسم العميل <span class="text-danger">*</span></label>
                            <input type="text" id="mi_client_name" class="form-control fw-bold" list="clientsDatalist" placeholder="اكتب اسم العميل..." required oninput="onClientNameInput(this.value)">
                            <datalist id="clientsDatalist">
                                @foreach($clientsList as $c)
                                    <option value="{{ $c->name }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address }}">{{ $c->phone }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">رقم الهاتف (للواتساب)</label>
                            <input type="text" id="mi_client_phone" class="form-control fw-bold" placeholder="01xxxxxxxxx" dir="ltr">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">عنوان العميل</label>
                            <input type="text" id="mi_client_address" class="form-control" placeholder="المدينة / المنطقة / الشارع">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">تاريخ الفاتورة <span class="text-danger">*</span></label>
                            <input type="date" id="mi_date" class="form-control fw-bold" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    {{-- ب. جدول الأصناف والبنود --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold m-0 text-primary">
                            <i class="fa fa-list-check me-1"></i> الأصناف والبنود المسجلة بالفاتورة
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3" onclick="addInvoiceItemRow()">
                            <i class="fa fa-plus me-1"></i> إضافة صنف / بند
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered mi-items-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 45px;" class="text-center">#</th>
                                    <th>الصنف / البند / البيان <span class="text-danger">*</span></th>
                                    <th style="width: 130px;" class="text-center">الكمية</th>
                                    <th style="width: 140px;" class="text-center">الوحدة</th>
                                    <th style="width: 160px;" class="text-center">سعر الوحدة (ج.م)</th>
                                    <th style="width: 160px;" class="text-center">الإجمالي (ج.م)</th>
                                    <th style="width: 55px;" class="text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="mi_items_tbody">
                                {{-- Populated by JS --}}
                            </tbody>
                        </table>
                    </div>

                    <datalist id="unitsDatalist">
                        @foreach($units as $u)
                            <option value="{{ $u }}">
                        @endforeach
                    </datalist>

                    {{-- ج. الملاحظات والشروط والأحكام + الضريبة وملخص الإجماليات --}}
                    <div class="row g-3 align-items-start">
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold"><i class="fa fa-comment-dots text-secondary me-1"></i> ملاحظات عامة على الفاتورة (تظهر أسفل الأصناف)</label>
                                    <textarea id="mi_notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية خاصة بالعميل أو التسليم..."></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                        <label class="form-label small fw-bold m-0"><i class="fa fa-file-contract text-warning me-1"></i> الشروط والأحكام (كل سطر يظهر كبند مستقل)</label>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size:0.72rem;" onclick="appendTermPreset('البضاعة المباعة ترد أو تستبدل خلال 14 يوماً بحالتها الأصلية وبأصل الفاتورة.')">+ سياسة الاستبدال</button>
                                            <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size:0.72rem;" onclick="appendTermPreset('تم استلام الأصناف الموضحة أعلاه بحالة جيدة ومطابقة للمواصفات.')">+ إقرار استلام</button>
                                            <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size:0.72rem;" onclick="appendTermPreset('الأسعار تشمل التوريد فقط ولا تشمل مصاريف النقل أو التركيب ما لم يذكر خلاف ذلك.')">+ شرط التوريد</button>
                                        </div>
                                    </div>
                                    <textarea id="mi_terms" class="form-control" rows="3" placeholder="اكتب الشروط والأحكام (سطر لكل شرط)..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="mi-summary-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-muted">إجمالي الأصناف:</span>
                                    <span class="fw-bold fs-6" id="mi_sum_subtotal">0 ج.م</span>
                                </div>

                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-0">خصم خاص (ج.م):</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="any" min="0" id="mi_discount" class="form-control form-control-sm text-center fw-bold text-danger" value="0" oninput="recalcManualInvoice()">
                                    </div>
                                </div>

                                {{-- خيار فاتورة ضريبية --}}
                                <div class="mi-tax-toggle-box mb-2" id="mi_tax_box">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label fw-bold small text-dark" for="mi_is_tax" style="cursor:pointer;">
                                            <i class="fa fa-percent text-primary me-1"></i> فاتورة ضريبية
                                        </label>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="mi_is_tax" onchange="toggleTaxInvoiceOption(this.checked)">
                                        </div>
                                    </div>
                                    <div id="wrap_mi_tax_rate" style="display:none;" class="mt-2 pt-2 border-top">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-6">
                                                <label class="small fw-bold text-primary mb-0">نسبة الضريبة (%):</label>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" max="100" id="mi_tax_rate" class="form-control text-center fw-bold text-primary" value="0" oninput="recalcManualInvoice()">
                                                    <span class="input-group-text fw-bold">%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 small">
                                            <span class="fw-bold text-muted">قيمة الضريبة المضافة:</span>
                                            <span class="fw-bold text-primary" id="mi_sum_tax">0 ج.م</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-0">المبلغ المدفوع (اختياري):</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="any" min="0" id="mi_paid" class="form-control form-control-sm text-center fw-bold text-success" placeholder="اختياري" oninput="recalcManualInvoice()">
                                    </div>
                                </div>

                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-black fs-5 text-dark">الإجمالي النهائي:</span>
                                    <span class="fw-black fs-4 text-primary" id="mi_sum_grand">0 ج.م</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1" id="mi_remaining_row" style="display:none !important;">
                                    <span class="small fw-bold text-danger">المتبقي على العميل:</span>
                                    <span class="fw-bold text-danger" id="mi_sum_remaining">0 ج.م</span>
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" id="mi_submit_btn" class="btn btn-primary fw-bold flex-grow-1 py-2">
                                        <i class="fa fa-floppy-disk me-1"></i> <span id="mi_submit_lbl">حفظ الفاتورة وعرضها للطباعة / واتساب</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             TAB 2: سجل الفواتير اليدوية
             ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="pane-log" role="tabpanel">
            {{-- Stats --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-pro">
                        <div class="label">إجمالي الفواتير بالسجل</div>
                        <div class="value">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-pro success">
                        <div class="label">الفواتير السارية</div>
                        <div class="value">{{ $stats['active'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-pro warning">
                        <div class="label">فواتير تم تعديلها</div>
                        <div class="value">{{ $stats['edited'] }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card-pro danger">
                        <div class="label">فواتير محذوفة</div>
                        <div class="value">{{ $stats['deleted'] }}</div>
                    </div>
                </div>
            </div>

            <div class="mi-card">
                <div class="mi-section-head">
                    <h5 class="mi-section-title">
                        <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                        <span>سجل الفواتير اليدوية</span>
                    </h5>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="d-flex gap-1 flex-wrap" id="miStatusFilters">
                            <button type="button" class="mi-filter-pill active" data-filter="all" onclick="setMiFilter('all', this)">الكل</button>
                            <button type="button" class="mi-filter-pill" data-filter="active" onclick="setMiFilter('active', this)">سارية</button>
                            <button type="button" class="mi-filter-pill" data-filter="edited" onclick="setMiFilter('edited', this)">تم تعديلها</button>
                            <button type="button" class="mi-filter-pill" data-filter="deleted" onclick="setMiFilter('deleted', this)">محذوفة</button>
                        </div>
                        <div style="min-width: 260px;">
                            <input type="text" id="miSearchInput" class="form-control form-control-sm" placeholder="🔍 ابحث باسم العميل، الهاتف، الصنف، رقم الفاتورة..." oninput="filterInvoicesLog()">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="miLogTable">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>التاريخ</th>
                                <th>بيانات العميل</th>
                                <th>الأصناف والبنود</th>
                                <th>الإجمالي</th>
                                <th>حالة الفاتورة</th>
                                <th class="text-center">إجراءات (عرض / واتساب / تعديل / حذف)</th>
                            </tr>
                        </thead>
                        <tbody id="miLogTbody">
                            @forelse($invoices as $inv)
                                @php
                                    $rowStatus = $inv->is_deleted ? 'deleted' : ($inv->is_edited ? 'edited' : 'active');
                                    $rowClass = $inv->is_deleted ? 'mi-row-deleted' : ($inv->is_edited ? 'mi-row-edited' : '');
                                @endphp
                                <tr class="{{ $rowClass }}"
                                    data-id="{{ $inv->id }}"
                                    data-status="{{ $rowStatus }}"
                                    data-search="{{ $inv->invoice_number }} {{ $inv->invoice_title }} {{ $inv->client_name }} {{ $inv->client_phone }} {{ $inv->client_address }} {{ $inv->items_summary }} {{ $inv->notes }}">
                                    <td>
                                        <div class="fw-black text-dark">{{ $inv->invoice_number }}</div>
                                        <div class="small text-muted">
                                            {{ $inv->invoice_title }}
                                            @if(floatval($inv->tax_rate) > 0)
                                                <span class="badge bg-primary-subtle text-primary-emphasis ms-1">ضريبية {{ floatval($inv->tax_rate) }}%</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="fw-bold">{{ $inv->invoice_date }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $inv->client_name }}</div>
                                        @if($inv->client_phone)
                                            <div class="small text-muted" dir="ltr"><i class="fa fa-phone me-1"></i>{{ $inv->client_phone }}</div>
                                        @endif
                                        @if($inv->client_address)
                                            <div class="small text-muted"><i class="fa fa-location-dot me-1"></i>{{ $inv->client_address }}</div>
                                        @endif
                                    </td>
                                    <td style="max-width: 260px;">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis mb-1">{{ count($inv->items) }} بنود</span>
                                        <div class="small text-muted text-truncate" title="{{ $inv->items_summary }}">{{ $inv->items_summary }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-black fs-6 {{ $inv->is_deleted ? 'text-decoration-line-through text-muted' : 'text-primary' }}">
                                            {{ number_format($inv->grand_total, 2) }} ج.م
                                        </div>
                                        @if($inv->discount_amount > 0)
                                            <div class="small text-danger">خصم: {{ number_format($inv->discount_amount, 2) }} ج</div>
                                        @endif
                                        @if($inv->tax_amount > 0)
                                            <div class="small text-primary">ضريبة ({{ floatval($inv->tax_rate) }}%): {{ number_format($inv->tax_amount, 2) }} ج</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->is_deleted)
                                            <span class="mi-badge-deleted">
                                                <i class="fa fa-trash-can"></i> فاتورة محذوفة
                                            </span>
                                            <div class="small text-danger mt-1" style="font-size: 0.72rem;">
                                                حُذفت في: {{ $inv->deleted_at ? \Carbon\Carbon::parse($inv->deleted_at)->format('Y-m-d h:i A') : '-' }}
                                                @if($inv->deleted_by) ({{ $inv->deleted_by }}) @endif
                                            </div>
                                            @if($inv->delete_reason)
                                                <div class="small text-muted" style="font-size: 0.72rem;">السبب: {{ $inv->delete_reason }}</div>
                                            @endif
                                        @endif

                                        @if($inv->is_edited)
                                            <div class="{{ $inv->is_deleted ? 'mt-1' : '' }}">
                                                <span class="mi-badge-edited">
                                                    <i class="fa fa-pen-to-square"></i> تم تعديلها ({{ $inv->edit_count }})
                                                </span>
                                                <div class="small text-warning-emphasis mt-1" style="font-size: 0.72rem;">
                                                    آخر تعديل: {{ $inv->last_edited_at ? \Carbon\Carbon::parse($inv->last_edited_at)->format('Y-m-d h:i A') : '-' }}
                                                    @if($inv->last_edited_by) ({{ $inv->last_edited_by }}) @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if(!$inv->is_deleted && !$inv->is_edited)
                                            <span class="mi-badge-active">
                                                <i class="fa fa-circle-check"></i> أصلية / سارية
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="openInvoicePreview({{ $inv->id }})" title="عرض وطباعة الفاتورة">
                                                <i class="fa fa-eye me-1"></i>عرض / طباعة
                                            </button>
                                            <button type="button" class="btn btn-sm btn-dark fw-bold" onclick="openInvoicePreview({{ $inv->id }}, 'image')" title="حفظ الفاتورة كصورة">
                                                <i class="fa fa-image me-1"></i>صورة
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success fw-bold" onclick="openInvoicePreview({{ $inv->id }}, 'whatsapp')" title="إرسال كصورة للواتساب">
                                                <i class="fab fa-whatsapp me-1"></i>واتساب
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning fw-bold" onclick="loadInvoiceForEdit({{ $inv->id }})" title="تعديل الفاتورة">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                            @if(!$inv->is_deleted)
                                                <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="deleteManualInvoice({{ $inv->id }}, '{{ $inv->invoice_number }}')" title="حذف الفاتورة">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="miEmptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa fa-file-invoice fs-1 mb-2 d-block opacity-50"></i>
                                        لا توجد فواتير يدوية مسجلة حتى الآن.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══════ Modal عرض وطباعة وتصدير الفاتورة كصورة ══════ --}}
<div class="modal fade" id="invoicePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #f1f5f9;">
            <div class="modal-header bg-white border-bottom no-print d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="modal-title fw-black text-dark m-0">
                        <i class="fa fa-file-invoice text-primary me-1"></i> معاينة الفاتورة الرسمية
                    </h5>
                    <span id="pv_status_badges"></span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold" onclick="printCurrentInvoice()">
                        <i class="fa fa-print me-1"></i> طباعة / PDF
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" onclick="downloadCurrentInvoiceImage()">
                        <i class="fa fa-download me-1"></i> حفظ كصورة (JPG)
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="sendCurrentInvoiceWhatsApp()">
                        <i class="fab fa-whatsapp me-1"></i> إرسال كصورة للواتساب
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4">
                @php
                    $companyName = $settings['invoice_header_text'] ?? 'شركة الضبع للتجارة والتوريدات والمقاولات';
                    $companyPhone = $settings['company_phone'] ?? '01000000000';
                @endphp

                {{-- لوحة سجل التعديلات والحذف (تظهر في الشاشة للمراجعة) --}}
                <div id="pv_audit_alert" class="alert alert-warning small mb-3 no-print" style="display:none;"></div>

                {{-- الورقة الرسمية القابلة للطباعة والتصوير (بدون QR) --}}
                <div id="printableInvoiceArea" class="official-invoice-canvas">
                    <div class="inv-top-stripes">
                        <div class="inv-stripe-navy"></div>
                        <div class="inv-stripe-gold"></div>
                    </div>

                    <div id="pv_deleted_watermark" class="inv-watermark-deleted" style="display:none;">فاتورة ملغاة / محذوفة</div>

                    {{-- رأس الفاتورة --}}
                    <div class="inv-header-row">
                        <div>
                            <h1 class="inv-doc-title" id="pv_doc_title">فاتورة مبيعات</h1>
                            <div class="inv-doc-num" id="pv_doc_num">INV-0000</div>
                            <div id="pv_doc_state_badge"></div>
                        </div>
                        <div class="inv-company-box">
                            <h2 class="inv-company-name">{{ $companyName }}</h2>
                            <div class="inv-company-sub">AL-DABAA TRADING & CONTRACTING</div>
                            <div class="small text-muted fw-bold mt-1" dir="ltr"><i class="fa fa-phone me-1"></i>{{ $companyPhone }}</div>
                        </div>
                    </div>

                    {{-- بيانات العميل + بيانات المستند (بدون QR) --}}
                    <div class="inv-client-meta-grid">
                        <div class="inv-client-card">
                            <div class="small text-muted fw-bold mb-1">مقدم إلى العميل:</div>
                            <div class="fs-5 fw-black text-dark" id="pv_client_name">—</div>
                            <div class="small fw-bold text-secondary mt-1" id="pv_client_phone_wrap" style="display:none;">
                                <i class="fa fa-phone me-1"></i><span id="pv_client_phone" dir="ltr"></span>
                            </div>
                            <div class="small fw-bold text-secondary mt-1" id="pv_client_address_wrap" style="display:none;">
                                <i class="fa fa-location-dot me-1"></i><span id="pv_client_address"></span>
                            </div>
                        </div>

                        <div class="inv-meta-card">
                            <div class="small text-muted fw-bold mb-2">تفاصيل الفاتورة:</div>
                            <div class="d-flex justify-content-between small fw-bold py-1 border-bottom">
                                <span class="text-muted">رقم الفاتورة:</span>
                                <span class="text-dark" id="pv_meta_num" dir="ltr">—</span>
                            </div>
                            <div class="d-flex justify-content-between small fw-bold py-1 border-bottom">
                                <span class="text-muted">تاريخ الإصدار:</span>
                                <span class="text-dark" id="pv_date">—</span>
                            </div>
                            <div class="d-flex justify-content-between small fw-bold py-1">
                                <span class="text-muted">عدد البنود:</span>
                                <span class="text-dark" id="pv_items_count">0</span>
                            </div>
                        </div>
                    </div>

                    {{-- جدول الأصناف والبنود --}}
                    <table class="inv-table">
                        <thead>
                            <tr>
                                <th style="width: 48px;">م</th>
                                <th class="text-right">الصنف / البند / البيان</th>
                                <th style="width: 100px;">الكمية</th>
                                <th style="width: 100px;">الوحدة</th>
                                <th style="width: 130px;">سعر الوحدة</th>
                                <th style="width: 140px;">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody id="pv_items_tbody"></tbody>
                    </table>

                    {{-- التفقيط --}}
                    <div class="p-2 px-3 mb-3 rounded border bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="small fw-bold text-muted">المبلغ بالحروف:</span>
                        <span class="small fw-black text-dark" id="pv_tafqeet">—</span>
                    </div>

                    {{-- الملاحظات والشروط + ملخص الحساب --}}
                    <div class="inv-bottom-grid">
                        <div class="inv-notes-terms-col">
                            <div id="pv_notes_box" class="mb-3" style="display:none;">
                                <div class="fw-black small text-dark mb-1"><i class="fa fa-comment-dots text-primary me-1"></i> ملاحظات:</div>
                                <div class="small fw-bold text-secondary p-2 rounded border bg-light" id="pv_notes_text" style="white-space: pre-line;"></div>
                            </div>

                            <div id="pv_terms_box" style="display:none;">
                                <div class="fw-black small text-dark mb-1"><i class="fa fa-file-contract text-warning me-1"></i> الشروط والأحكام:</div>
                                <ul class="small fw-bold text-secondary ps-3 mb-0" id="pv_terms_list" style="line-height: 1.7;"></ul>
                            </div>
                        </div>

                        <div class="inv-totals-col">
                            <div class="inv-total-line">
                                <span class="text-muted">إجمالي الأصناف:</span>
                                <span id="pv_subtotal">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line text-danger" id="pv_discount_row" style="display:none;">
                                <span>الخصم:</span>
                                <span id="pv_discount">-0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line text-primary" id="pv_tax_row" style="display:none;">
                                <span id="pv_tax_label">ضريبة القيمة المضافة (14%):</span>
                                <span id="pv_tax_amount">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line grand">
                                <span>الإجمالي النهائي:</span>
                                <span id="pv_grand_total">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line text-success mt-1" id="pv_paid_row" style="display:none;">
                                <span>المبلغ المسدد:</span>
                                <span id="pv_paid">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line text-danger" id="pv_rem_row" style="display:none;">
                                <span>المتبقي المستحق:</span>
                                <span id="pv_remaining">0.00 ج.م</span>
                            </div>
                        </div>
                    </div>

                    {{-- التوقيعات --}}
                    <div class="d-flex justify-content-between align-items-end mt-4 pt-3 border-top">
                        <div class="text-center" style="min-width: 180px;">
                            <div class="small fw-bold text-muted mb-4">توقيع المستلم / العميل</div>
                            <div style="border-bottom: 1px dashed #94a3b8; width: 160px; margin: 0 auto;"></div>
                        </div>
                        <div class="text-center" style="min-width: 180px;">
                            <div class="small fw-bold text-muted mb-4">إدارة المبيعات / الختم</div>
                            <div style="border-bottom: 1px dashed #94a3b8; width: 160px; margin: 0 auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const KNOWN_CLIENTS = @json($clientsList);
let currentPreviewInvoice = null;
let currentStatusFilter = 'all';

// ── تهيئة سطور الأصناف عند فتح الصفحة ──
document.addEventListener('DOMContentLoaded', () => {
    addInvoiceItemRow();
});

function switchMiTab(tabId) {
    const btn = document.getElementById(tabId);
    if (btn) {
        bootstrap.Tab.getOrCreateInstance(btn).show();
    }
}

function toggleTaxInvoiceOption(checked) {
    const wrap = document.getElementById('wrap_mi_tax_rate');
    const box  = document.getElementById('mi_tax_box');
    const rateInput = document.getElementById('mi_tax_rate');
    const titleInput = document.getElementById('mi_title');

    if (checked) {
        wrap.style.display = 'block';
        box.classList.add('tax-active');
        if (!parseFloat(rateInput.value) || parseFloat(rateInput.value) <= 0) {
            rateInput.value = 14;
        }
        if (titleInput.value.trim() === 'فاتورة مبيعات') {
            titleInput.value = 'فاتورة ضريبية';
        }
    } else {
        wrap.style.display = 'none';
        box.classList.remove('tax-active');
        rateInput.value = 0;
        if (titleInput.value.trim() === 'فاتورة ضريبية') {
            titleInput.value = 'فاتورة مبيعات';
        }
    }
    recalcManualInvoice();
}

function onClientNameInput(val) {
    const clean = (val || '').trim();
    const found = KNOWN_CLIENTS.find(c => (c.name || '').trim() === clean);
    if (found) {
        const phoneEl = document.getElementById('mi_client_phone');
        const addrEl  = document.getElementById('mi_client_address');
        if (found.phone && !phoneEl.value) phoneEl.value = found.phone;
        if (found.address && !addrEl.value) addrEl.value = found.address;
    }
}

function addInvoiceItemRow(data = {}) {
    const tbody = document.getElementById('mi_items_tbody');
    const tr = document.createElement('tr');
    tr.className = 'mi-item-row';

    const title = data.title || '';
    const qty   = data.qty !== undefined ? data.qty : 1;
    const unit  = data.unit || 'عدد';
    const price = data.price !== undefined ? data.price : '';

    tr.innerHTML = `
        <td class="text-center fw-bold text-muted row-idx">1</td>
        <td>
            <input type="text" class="form-control form-control-sm fw-bold item-title" placeholder="اكتب اسم الصنف أو البند..." value="${escapeAttr(title)}" required>
        </td>
        <td>
            <input type="number" step="any" min="0.01" class="form-control form-control-sm text-center fw-bold item-qty" value="${qty}" oninput="recalcManualInvoice()" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm text-center item-unit" list="unitsDatalist" value="${escapeAttr(unit)}" placeholder="عدد / متر">
        </td>
        <td>
            <input type="number" step="any" min="0" class="form-control form-control-sm text-center fw-bold item-price" placeholder="0.00" value="${price}" oninput="recalcManualInvoice()" required>
        </td>
        <td class="text-center fw-black text-primary item-line-total">0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeInvoiceItemRow(this)" title="حذف السطر">
                <i class="fa fa-xmark"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    renumberItemRows();
    recalcManualInvoice();
}

function removeInvoiceItemRow(btn) {
    const tbody = document.getElementById('mi_items_tbody');
    if (tbody.querySelectorAll('.mi-item-row').length <= 1) {
        const row = btn.closest('tr');
        row.querySelector('.item-title').value = '';
        row.querySelector('.item-qty').value = 1;
        row.querySelector('.item-unit').value = 'عدد';
        row.querySelector('.item-price').value = '';
        recalcManualInvoice();
        return;
    }
    btn.closest('tr').remove();
    renumberItemRows();
    recalcManualInvoice();
}

function renumberItemRows() {
    document.querySelectorAll('#mi_items_tbody .mi-item-row').forEach((tr, idx) => {
        tr.querySelector('.row-idx').textContent = idx + 1;
    });
}

function recalcManualInvoice() {
    let subtotal = 0;
    document.querySelectorAll('#mi_items_tbody .mi-item-row').forEach(tr => {
        const qty   = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(tr.querySelector('.item-price')?.value) || 0;
        const line  = Math.round(qty * price * 100) / 100;
        subtotal += line;
        tr.querySelector('.item-line-total').textContent = fmtMoney(line);
    });

    const discount = Math.max(0, parseFloat(document.getElementById('mi_discount')?.value) || 0);
    const afterDiscount = Math.max(0, subtotal - discount);

    const isTax = document.getElementById('mi_is_tax')?.checked;
    const taxRate = isTax ? Math.max(0, parseFloat(document.getElementById('mi_tax_rate')?.value) || 0) : 0;
    const taxAmount = Math.round(afterDiscount * (taxRate / 100) * 100) / 100;
    const grand = Math.max(0, Math.round((afterDiscount + taxAmount) * 100) / 100);

    document.getElementById('mi_sum_subtotal').textContent = fmtMoney(subtotal) + ' ج.م';
    document.getElementById('mi_sum_tax').textContent      = fmtMoney(taxAmount) + ' ج.م';
    document.getElementById('mi_sum_grand').textContent    = fmtMoney(grand) + ' ج.م';

    const paidInput = document.getElementById('mi_paid').value;
    const remRow = document.getElementById('mi_remaining_row');
    if (paidInput !== '' && paidInput !== null) {
        const paid = Math.max(0, parseFloat(paidInput) || 0);
        const rem  = Math.max(0, Math.round((grand - paid) * 100) / 100);
        remRow.style.setProperty('display', 'flex', 'important');
        document.getElementById('mi_sum_remaining').textContent = fmtMoney(rem) + ' ج.م';
    } else {
        remRow.style.setProperty('display', 'none', 'important');
    }
}

function appendTermPreset(text) {
    const el = document.getElementById('mi_terms');
    const cur = el.value.trim();
    el.value = cur ? (cur + '\n' + text) : text;
}

function resetInvoiceForm() {
    document.getElementById('mi_id').value = '';
    document.getElementById('mi_title').value = 'فاتورة مبيعات';
    document.getElementById('mi_client_name').value = '';
    document.getElementById('mi_client_phone').value = '';
    document.getElementById('mi_client_address').value = '';
    document.getElementById('mi_date').value = '{{ date('Y-m-d') }}';
    document.getElementById('mi_discount').value = 0;
    document.getElementById('mi_is_tax').checked = false;
    document.getElementById('mi_tax_rate').value = 0;
    toggleTaxInvoiceOption(false);
    document.getElementById('mi_paid').value = '';
    document.getElementById('mi_notes').value = '';
    document.getElementById('mi_terms').value = '';

    const editBanner = document.getElementById('editModeBanner');
    editBanner.classList.add('d-none');
    editBanner.classList.remove('d-flex');
    document.getElementById('tabCreateTitle').textContent = 'إنشاء فاتورة جديدة';
    document.getElementById('formCardTitle').innerHTML = '<i class="fa-solid fa-file-circle-plus text-primary"></i><span>بيانات الفاتورة والعميل</span>';
    document.getElementById('mi_submit_lbl').textContent = 'حفظ الفاتورة وعرضها للطباعة / واتساب';

    const tbody = document.getElementById('mi_items_tbody');
    tbody.innerHTML = '';
    addInvoiceItemRow();
    switchMiTab('btn-tab-create');
}

async function submitManualInvoice(e) {
    e.preventDefault();

    const id = document.getElementById('mi_id').value;
    const items = [];
    document.querySelectorAll('#mi_items_tbody .mi-item-row').forEach(tr => {
        const title = tr.querySelector('.item-title').value.trim();
        const qty   = parseFloat(tr.querySelector('.item-qty').value) || 0;
        const unit  = tr.querySelector('.item-unit').value.trim() || 'عدد';
        const price = parseFloat(tr.querySelector('.item-price').value) || 0;
        if (title !== '') {
            items.push({ title, qty, unit, price });
        }
    });

    if (items.length === 0) {
        Swal.fire('تنبيه', 'يرجى إدخال صنف أو بند واحد على الأقل.', 'warning');
        return;
    }

    const isTax = document.getElementById('mi_is_tax').checked;
    const taxRate = isTax ? (parseFloat(document.getElementById('mi_tax_rate').value) || 0) : 0;

    const payload = {
        invoice_title:   document.getElementById('mi_title').value.trim(),
        client_name:     document.getElementById('mi_client_name').value.trim(),
        client_phone:    document.getElementById('mi_client_phone').value.trim(),
        client_address:  document.getElementById('mi_client_address').value.trim(),
        invoice_date:    document.getElementById('mi_date').value,
        discount_amount: parseFloat(document.getElementById('mi_discount').value) || 0,
        tax_rate:        taxRate,
        paid_amount:     document.getElementById('mi_paid').value !== '' ? parseFloat(document.getElementById('mi_paid').value) : null,
        notes:           document.getElementById('mi_notes').value.trim(),
        terms:           document.getElementById('mi_terms').value.trim(),
        items:           items,
    };

    const url = id ? `/manual-invoices/${id}/update` : `/manual-invoices/store`;
    const btn = document.getElementById('mi_submit_btn');
    btn.disabled = true;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        btn.disabled = false;

        if (!res.ok || !data.success) {
            Swal.fire('خطأ', data.message || 'تعذر حفظ الفاتورة', 'error');
            return;
        }

        await Swal.fire({
            icon: 'success',
            title: 'تم الحفظ بنجاح',
            text: data.message,
            timer: 1300,
            showConfirmButton: false
        });

        renderInvoiceIntoPreview(data.invoice);
        const modalEl = document.getElementById('invoicePreviewModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalEl.addEventListener('hidden.bs.modal', () => location.reload(), { once: true });
        modal.show();
    } catch (err) {
        btn.disabled = false;
        Swal.fire('خطأ', err.message || 'فشل الاتصال بالسيرفر', 'error');
    }
}

async function openInvoicePreview(id, autoAction = null) {
    try {
        const res = await fetch(`/manual-invoices/${id}`);
        const data = await res.json();
        if (!data.success || !data.invoice) {
            Swal.fire('خطأ', data.message || 'تعذر تحميل الفاتورة', 'error');
            return;
        }
        renderInvoiceIntoPreview(data.invoice);
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('invoicePreviewModal'));
        modal.show();

        if (autoAction === 'image') {
            setTimeout(() => downloadCurrentInvoiceImage(), 350);
        } else if (autoAction === 'whatsapp') {
            setTimeout(() => sendCurrentInvoiceWhatsApp(), 350);
        }
    } catch (err) {
        Swal.fire('خطأ', 'تعذر جلب بيانات الفاتورة', 'error');
    }
}

function renderInvoiceIntoPreview(inv) {
    currentPreviewInvoice = inv;

    document.getElementById('pv_doc_title').textContent = inv.invoice_title || 'فاتورة مبيعات';
    document.getElementById('pv_doc_num').textContent   = inv.invoice_number;
    document.getElementById('pv_meta_num').textContent  = inv.invoice_number;
    document.getElementById('pv_client_name').textContent = inv.client_name;

    const phoneWrap = document.getElementById('pv_client_phone_wrap');
    if (inv.client_phone) {
        phoneWrap.style.display = 'block';
        document.getElementById('pv_client_phone').textContent = inv.client_phone;
    } else {
        phoneWrap.style.display = 'none';
    }

    const addrWrap = document.getElementById('pv_client_address_wrap');
    if (inv.client_address) {
        addrWrap.style.display = 'block';
        document.getElementById('pv_client_address').textContent = inv.client_address;
    } else {
        addrWrap.style.display = 'none';
    }

    document.getElementById('pv_date').textContent = inv.invoice_date;
    document.getElementById('pv_items_count').textContent = (inv.items || []).length + ' صنف / بند';
    document.getElementById('pv_tafqeet').textContent = inv.tafqeet || '';

    // حالة الفاتورة (محذوفة / معدلة / سارية)
    const wm = document.getElementById('pv_deleted_watermark');
    const stateBadge = document.getElementById('pv_doc_state_badge');
    const topBadges  = document.getElementById('pv_status_badges');
    const auditAlert = document.getElementById('pv_audit_alert');

    let badgesHtml = '';
    let auditHtml  = '';

    if (Number(inv.is_deleted) === 1) {
        wm.style.display = 'block';
        badgesHtml += `<span class="mi-badge-deleted me-1"><i class="fa fa-ban"></i> فاتورة محذوفة</span>`;
        auditHtml += `<div><i class="fa fa-trash-can text-danger me-1"></i> <b>هذه الفاتورة محذوفة:</b> تم حذفها بتاريخ (${inv.deleted_at || '-'}) بواسطة (${inv.deleted_by || 'الإدارة'}) ${inv.delete_reason ? '— السبب: ' + escapeHtml(inv.delete_reason) : ''}.</div>`;
    } else {
        wm.style.display = 'none';
    }

    if (Number(inv.is_edited) === 1) {
        badgesHtml += `<span class="mi-badge-edited me-1"><i class="fa fa-pen-to-square"></i> تم تعديلها (${inv.edit_count} مرة)</span>`;
        auditHtml += `<div class="mt-1"><i class="fa fa-clock-rotate-left text-warning me-1"></i> <b>تم تعديل هذه الفاتورة (${inv.edit_count} مرة):</b> آخر تعديل في (${inv.last_edited_at || '-'}) بواسطة (${inv.last_edited_by || 'الإدارة'}).</div>`;
    }

    if (Number(inv.is_deleted) === 0 && Number(inv.is_edited) === 0) {
        badgesHtml = `<span class="mi-badge-active"><i class="fa fa-circle-check"></i> فاتورة معتمدة</span>`;
    }

    stateBadge.innerHTML = badgesHtml;
    topBadges.innerHTML  = badgesHtml;

    if (auditHtml !== '') {
        auditAlert.innerHTML = auditHtml;
        auditAlert.style.display = 'block';
    } else {
        auditAlert.style.display = 'none';
    }

    // جدول الأصناف
    const tbody = document.getElementById('pv_items_tbody');
    tbody.innerHTML = '';
    (inv.items || []).forEach((it, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td class="text-right">${escapeHtml(it.title)}</td>
            <td>${fmtMoney(it.qty)}</td>
            <td>${escapeHtml(it.unit || 'عدد')}</td>
            <td>${fmtMoney(it.price)}</td>
            <td class="fw-black">${fmtMoney(it.total)} ج.م</td>
        `;
        tbody.appendChild(tr);
    });

    // الإجماليات
    document.getElementById('pv_subtotal').textContent = fmtMoney(inv.subtotal) + ' ج.م';
    const discRow = document.getElementById('pv_discount_row');
    if (parseFloat(inv.discount_amount) > 0) {
        discRow.style.display = 'flex';
        document.getElementById('pv_discount').textContent = '-' + fmtMoney(inv.discount_amount) + ' ج.م';
    } else {
        discRow.style.display = 'none';
    }

    const taxRow = document.getElementById('pv_tax_row');
    if (parseFloat(inv.tax_amount) > 0 || parseFloat(inv.tax_rate) > 0) {
        taxRow.style.display = 'flex';
        document.getElementById('pv_tax_label').textContent = `ضريبة القيمة المضافة (${fmtMoney(inv.tax_rate)}%):`;
        document.getElementById('pv_tax_amount').textContent = fmtMoney(inv.tax_amount) + ' ج.م';
    } else {
        taxRow.style.display = 'none';
    }

    document.getElementById('pv_grand_total').textContent = fmtMoney(inv.grand_total) + ' ج.م';

    const paidRow = document.getElementById('pv_paid_row');
    const remRow  = document.getElementById('pv_rem_row');
    if (inv.paid_amount !== null && inv.paid_amount !== undefined && inv.paid_amount !== '') {
        paidRow.style.display = 'flex';
        remRow.style.display  = 'flex';
        document.getElementById('pv_paid').textContent      = fmtMoney(inv.paid_amount) + ' ج.م';
        document.getElementById('pv_remaining').textContent = fmtMoney(inv.remaining_amount) + ' ج.م';
    } else {
        paidRow.style.display = 'none';
        remRow.style.display  = 'none';
    }

    // الملاحظات والشروط
    const notesBox = document.getElementById('pv_notes_box');
    if (inv.notes && inv.notes.trim() !== '') {
        notesBox.style.display = 'block';
        document.getElementById('pv_notes_text').textContent = inv.notes;
    } else {
        notesBox.style.display = 'none';
    }

    const termsBox  = document.getElementById('pv_terms_box');
    const termsList = document.getElementById('pv_terms_list');
    termsList.innerHTML = '';
    if (inv.terms && inv.terms.trim() !== '') {
        const lines = inv.terms.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
        lines.forEach(line => {
            const li = document.createElement('li');
            li.textContent = line;
            termsList.appendChild(li);
        });
        termsBox.style.display = lines.length ? 'block' : 'none';
    } else {
        termsBox.style.display = 'none';
    }

    // تحديث حاوية الطباعة المستقلة لضمان طباعة ممتازة
    const printContainer = document.getElementById('printOnlyContainer');
    printContainer.innerHTML = '';
    const clone = document.getElementById('printableInvoiceArea').cloneNode(true);
    clone.removeAttribute('id');
    printContainer.appendChild(clone);
}

function printCurrentInvoice() {
    const printContainer = document.getElementById('printOnlyContainer');
    if (!printContainer.innerHTML.trim()) {
        const clone = document.getElementById('printableInvoiceArea').cloneNode(true);
        clone.removeAttribute('id');
        printContainer.appendChild(clone);
    }
    window.print();
}

// ── تحميل فاتورة للتعديل ──
async function loadInvoiceForEdit(id) {
    try {
        const res = await fetch(`/manual-invoices/${id}`);
        const data = await res.json();
        if (!data.success || !data.invoice) {
            Swal.fire('خطأ', 'تعذر تحميل بيانات الفاتورة للتعديل', 'error');
            return;
        }
        const inv = data.invoice;

        document.getElementById('mi_id').value             = inv.id;
        document.getElementById('mi_title').value          = inv.invoice_title || 'فاتورة مبيعات';
        document.getElementById('mi_client_name').value    = inv.client_name || '';
        document.getElementById('mi_client_phone').value   = inv.client_phone || '';
        document.getElementById('mi_client_address').value = inv.client_address || '';
        document.getElementById('mi_date').value           = inv.invoice_date || '{{ date('Y-m-d') }}';
        document.getElementById('mi_discount').value       = parseFloat(inv.discount_amount) || 0;

        const hasTax = parseFloat(inv.tax_rate) > 0;
        document.getElementById('mi_is_tax').checked = hasTax;
        document.getElementById('mi_tax_rate').value = hasTax ? parseFloat(inv.tax_rate) : 0;
        toggleTaxInvoiceOption(hasTax);

        document.getElementById('mi_paid').value           = (inv.paid_amount !== null && inv.paid_amount !== undefined) ? parseFloat(inv.paid_amount) : '';
        document.getElementById('mi_notes').value          = inv.notes || '';
        document.getElementById('mi_terms').value          = inv.terms || '';

        const tbody = document.getElementById('mi_items_tbody');
        tbody.innerHTML = '';
        if (Array.isArray(inv.items) && inv.items.length > 0) {
            inv.items.forEach(it => addInvoiceItemRow(it));
        } else {
            addInvoiceItemRow();
        }

        const editBanner = document.getElementById('editModeBanner');
        editBanner.classList.remove('d-none');
        editBanner.classList.add('d-flex');
        document.getElementById('editModeInvNum').textContent = inv.invoice_number;
        document.getElementById('tabCreateTitle').textContent = `تعديل (${inv.invoice_number})`;
        document.getElementById('formCardTitle').innerHTML = `<i class="fa-solid fa-pen-to-square text-warning"></i><span>تعديل الفاتورة رقم (${escapeHtml(inv.invoice_number)})</span>`;
        document.getElementById('mi_submit_lbl').textContent = 'حفظ التعديلات على الفاتورة';

        switchMiTab('btn-tab-create');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (err) {
        Swal.fire('خطأ', 'تعذر تحميل الفاتورة', 'error');
    }
}

// ── حذف فاتورة (حذف ناعم مع بقائها في السجل كفاتورة محذوفة) ──
async function deleteManualInvoice(id, invNum) {
    const { value: reason, isConfirmed } = await Swal.fire({
        icon: 'warning',
        title: `حذف الفاتورة (${invNum})`,
        html: 'ستبقى الفاتورة محفوظة في السجل ولكن سيظهر عليها بوضوح أنها <b>فاتورة محذوفة</b>.',
        input: 'text',
        inputPlaceholder: 'سبب الحذف (اختياري)...',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'تأكيد الحذف',
        cancelButtonText: 'تراجع'
    });

    if (!isConfirmed) return;

    try {
        const res = await fetch(`/manual-invoices/${id}/delete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason || '' })
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            Swal.fire('خطأ', data.message || 'تعذر حذف الفاتورة', 'error');
            return;
        }
        await Swal.fire({ icon: 'success', title: 'تم الحذف', text: data.message, timer: 1300, showConfirmButton: false });
        location.reload();
    } catch (err) {
        Swal.fire('خطأ', 'فشل الاتصال بالسيرفر', 'error');
    }
}

// ── التقاط الفاتورة كصورة عالية الجودة (html2canvas) ──
function captureInvoiceCanvas() {
    const node = document.getElementById('printableInvoiceArea');
    return html2canvas(node, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false,
        onclone: function(doc) {
            const s = doc.createElement('style');
            s.textContent = '*{letter-spacing:normal !important;}';
            (doc.head || doc.documentElement).appendChild(s);
        }
    });
}

async function downloadCurrentInvoiceImage() {
    if (!currentPreviewInvoice) return;
    Swal.fire({ title: 'جاري تجهيز صورة الفاتورة...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const canvas = await captureInvoiceCanvas();
        const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
        Swal.close();

        const link = document.createElement('a');
        const safeClient = (currentPreviewInvoice.client_name || 'عميل').replace(/[\\/:*?"<>|]/g, '_');
        link.download = `فاتورة_${currentPreviewInvoice.invoice_number}_${safeClient}.jpg`;
        link.href = dataUrl;
        link.click();
    } catch (err) {
        Swal.close();
        Swal.fire('خطأ', 'تعذر حفظ الفاتورة كصورة', 'error');
    }
}

function normalizeWaPhone(phone) {
    let raw = String(phone || '').replace(/\D/g, '');
    if (!raw) return '';
    if (raw.startsWith('20')) return raw;
    if (raw.startsWith('0')) return '2' + raw;
    return '20' + raw;
}

function dataUrlToFile(dataUrl, filename) {
    const arr = dataUrl.split(',');
    const mime = (arr[0].match(/:(.*?);/) || [])[1] || 'image/png';
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8 = new Uint8Array(n);
    while (n--) u8[n] = bstr.charCodeAt(n);
    return new File([u8], filename, { type: mime });
}

async function sendCurrentInvoiceWhatsApp() {
    if (!currentPreviewInvoice) return;
    const inv = currentPreviewInvoice;
    let phone = normalizeWaPhone(inv.client_phone);

    if (!phone) {
        const { value: enteredPhone } = await Swal.fire({
            title: 'رقم واتساب العميل',
            text: 'أدخل رقم هاتف العميل لإرسال صورة الفاتورة إليه:',
            input: 'text',
            inputPlaceholder: '01xxxxxxxxx',
            showCancelButton: true,
            confirmButtonText: 'إرسال للواتساب',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#25d366'
        });
        if (!enteredPhone) return;
        phone = normalizeWaPhone(enteredPhone);
    }

    const greeting = `مرحباً أ/ ${inv.client_name}،\nمرفق لحضرتك صورة ${inv.invoice_title || 'الفاتورة'} رقم (${inv.invoice_number}) بإجمالي ${fmtMoney(inv.grand_total)} ج.م.`;
    const waWebUrl = `https://web.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(greeting)}`;

    Swal.fire({ title: 'جاري تجهيز صورة الفاتورة للواتساب...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const canvas = await captureInvoiceCanvas();
        const jpgUrl = canvas.toDataURL('image/jpeg', 0.95);
        const file   = dataUrlToFile(jpgUrl, `فاتورة_${inv.invoice_number}.jpg`);

        // 1) على الجوال: مشاركة ملف الصورة مباشرة لتطبيق واتساب
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            Swal.close();
            navigator.share({
                files: [file],
                title: `فاتورة ${inv.invoice_number}`,
                text: greeting
            }).catch(() => {});
            return;
        }

        // 2) على الكمبيوتر: نسخ الصورة للكليبورد + فتح واتساب ويب على محادثة العميل مباشرة
        canvas.toBlob(async function(pngBlob) {
            try {
                if (navigator.clipboard && window.ClipboardItem && pngBlob) {
                    await navigator.clipboard.write([new ClipboardItem({ 'image/png': pngBlob })]);
                    Swal.close();
                    window.open(waWebUrl, '_blank');
                    Swal.fire({
                        icon: 'success',
                        title: 'تم نسخ صورة الفاتورة 📋',
                        html: 'تم فتح محادثة العميل على واتساب.<br>اضغط <b>Ctrl + V</b> داخل المحادثة للصق صورة الفاتورة وإرسالها فوراً.',
                        confirmButtonText: 'تمام',
                        confirmButtonColor: '#25d366'
                    });
                    return;
                }
            } catch (clipErr) {
                // fallback below
            }

            Swal.close();
            const a = document.createElement('a');
            a.href = jpgUrl;
            a.download = `فاتورة_${inv.invoice_number}.jpg`;
            a.click();
            window.open(waWebUrl, '_blank');
            Swal.fire({
                icon: 'info',
                title: 'تم تحميل صورة الفاتورة وفتح واتساب',
                html: 'أرفق الصورة التي تم تنزيلها داخل محادثة العميل في واتساب.',
                confirmButtonText: 'تمام',
                confirmButtonColor: '#25d366'
            });
        }, 'image/png');
    } catch (err) {
        Swal.close();
        Swal.fire('خطأ', 'تعذر تجهيز صورة الفاتورة للواتساب', 'error');
    }
}

// ── فلترة وبحث سجل الفواتير ──
function setMiFilter(status, btn) {
    currentStatusFilter = status;
    document.querySelectorAll('#miStatusFilters .mi-filter-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterInvoicesLog();
}

function filterInvoicesLog() {
    const q = window.normalizeArabic ? window.normalizeArabic(document.getElementById('miSearchInput').value) : document.getElementById('miSearchInput').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#miLogTbody tr[data-id]');

    rows.forEach(tr => {
        const st  = tr.dataset.status || 'active';
        const raw = tr.dataset.search || '';
        const hay = window.normalizeArabic ? window.normalizeArabic(raw) : raw.toLowerCase();

        const statusMatch = (currentStatusFilter === 'all') || (st === currentStatusFilter);
        const searchMatch = !q || hay.includes(q);

        tr.style.display = (statusMatch && searchMatch) ? '' : 'none';
    });
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function escapeAttr(str) {
    return escapeHtml(str);
}
</script>
</body>
</html>
