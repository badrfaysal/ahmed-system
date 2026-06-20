<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>محطة العمليات - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('partials.theme', ['accent' => 'amber'])

    <style>
        .main-content { margin-right: 260px; padding: 28px 30px; }

        /* ── Hero مخصص للبنزينة ── */
        .gas-hero {
            background: linear-gradient(135deg, var(--c-navy) 0%, #1f3556 50%, var(--c-accent) 100%);
            color: #fff;
            border-radius: var(--r-xl);
            padding: 26px 32px;
            margin-bottom: 22px;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }
        .gas-hero::after {
            content: ""; position: absolute; top: -40%; left: -10%;
            width: 360px; height: 360px;
            background: radial-gradient(circle, rgba(255,200,80,0.18), transparent 65%);
            border-radius: 50%;
        }
        .gas-hero .hero-icon {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(6px);
            width: 64px; height: 64px; border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.9rem; color: #ffd980;
        }
        .gas-hero h2 { color: #fff; font-weight: 600; font-size: 1.45rem; margin: 0 0 4px; }
        .gas-hero .sub { opacity: 0.85; font-size: 0.9rem; }

        /* ── Form ── */
        .form-grid label.form-pro-label i { color: var(--c-accent); margin-inline-end: 4px; }

        /* ── Combo ── */
        .combo-wrap { position: relative; }
        .combo-input-row {
            display: flex; gap: 0;
            border-radius: var(--r-md); overflow: hidden;
            border: 1px solid var(--c-border-2);
            background: var(--c-surface);
            transition: var(--t-fast);
        }
        .combo-input-row:focus-within {
            border-color: var(--c-accent);
            box-shadow: 0 0 0 3px var(--c-accent-bg);
        }
        .combo-input-row input.combo-text {
            flex: 1; border: none; outline: none; background: transparent;
            padding: 9px 12px; font-family: inherit;
            font-weight: 400; font-size: 0.92rem; color: var(--c-text);
            min-width: 0;
        }
        .combo-input-row input.combo-text::placeholder { color: var(--c-text-soft); }
        .combo-toggle-btn {
            background: none; border: none;
            border-right: 1px solid var(--c-border-2);
            padding: 0 12px; color: var(--c-text-soft);
            cursor: pointer; transition: var(--t-fast);
            font-size: 0.85rem; flex-shrink: 0;
        }
        .combo-toggle-btn:hover { background: var(--c-accent-bg); color: var(--c-accent); }
        .combo-dropdown {
            position: absolute; top: calc(100% + 4px); right: 0; left: 0;
            background: var(--c-surface);
            border: 1px solid var(--c-border-2);
            border-radius: var(--r-md);
            box-shadow: var(--shadow-lg);
            z-index: 9999; max-height: 240px; overflow-y: auto;
            display: none; padding: 6px;
        }
        .combo-dropdown.open { display: block; }
        .combo-item {
            padding: 8px 12px; border-radius: var(--r-sm);
            cursor: pointer; font-weight: 500;
            font-size: 0.88rem; color: var(--c-text);
            display: flex; align-items: center; gap: 8px;
            transition: var(--t-fast);
        }
        .combo-item:hover, .combo-item.highlighted {
            background: var(--c-accent-bg); color: var(--c-accent);
        }
        .combo-item .combo-item-icon { color: var(--c-text-soft); font-size: 0.78rem; flex-shrink: 0; }
        .combo-item .combo-item-id { font-size: 0.72rem; color: var(--c-text-soft); margin-right: auto; }
        .combo-empty { padding: 14px; text-align: center; color: var(--c-text-soft); font-size: 0.82rem; font-weight: 500; }
        .combo-new-hint {
            padding: 8px 14px 4px; font-size: 0.72rem;
            color: var(--c-text-soft); font-weight: 500;
            border-top: 1px dashed var(--c-border); margin-top: 4px;
        }
        .combo-new-hint i { color: var(--c-accent); }

        /* ── Calculator Panel ── */
        .calc-panel {
            background: var(--c-navy);
            border-radius: var(--r-xl);
            padding: 24px;
            color: #fff;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 18px;
        }
        .calc-panel h5 {
            color: #ffd980;
            font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            padding-bottom: 12px;
            margin-bottom: 18px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            font-size: 1rem;
        }
        .calc-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 12px; border-radius: var(--r-sm);
            font-size: 0.88rem;
            margin-bottom: 6px;
        }
        .calc-row .lbl { color: rgba(255,255,255,0.7); font-weight: 500; font-size: 0.82rem; }
        .calc-row .val { font-weight: 600; font-size: 1.05rem; color: #fff; font-variant-numeric: tabular-nums; }
        .calc-row.info { background: rgba(30,95,164,0.18); }
        .calc-row.info .val { color: #7eb6ef; }
        .calc-row.danger {
            background: rgba(185,28,28,0.18);
            border: 1px dashed rgba(185,28,28,0.4);
        }
        .calc-row.danger .val { color: #ffb3b3; }
        .calc-row.danger .lbl { color: rgba(255,255,255,0.85); }
        .deduction-item {
            background: rgba(255,255,255,0.04);
            padding: 8px 12px;
            border-radius: var(--r-sm);
            border-inline-start: 3px solid #ef5a5a;
            font-size: 0.82rem;
            display: flex; justify-content: space-between;
            margin-bottom: 5px;
        }
        .deduction-item .val { color: #ff9c9c; font-weight: 600; font-feature-settings: 'tnum'; }
        .calc-final {
            background: linear-gradient(135deg, rgba(184,124,29,0.18), rgba(184,124,29,0.05));
            border: 1px solid rgba(255,200,80,0.35);
            border-radius: var(--r-md);
            padding: 14px 16px;
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 12px;
        }
        .calc-final .lbl { color: #ffd980; font-weight: 600; font-size: 0.95rem; }
        .calc-final .val { color: #fff; font-weight: 700; font-size: 1.3rem; font-variant-numeric: tabular-nums; }
        .calc-summary-bottom {
            background: var(--c-surface);
            color: var(--c-text);
            border-radius: var(--r-md);
            padding: 14px;
            text-align: center;
            margin-top: 14px;
        }
        .calc-summary-bottom .lbl { color: var(--c-text-muted); font-size: 0.78rem; font-weight: 500; margin-bottom: 4px; }
        .calc-summary-bottom .val { color: var(--c-success); font-weight: 700; font-size: 1.45rem; }

        .manual-discount-input {
            background: rgba(0,0,0,0.25);
            border: 1.5px dashed rgba(255,140,140,0.4);
            color: #fff;
            border-radius: var(--r-sm);
            padding: 8px 12px;
            font-weight: 600;
            width: 100%;
            transition: var(--t-fast);
        }
        .manual-discount-input:focus {
            outline: none;
            border-color: #ff9c9c;
            box-shadow: 0 0 0 3px rgba(255,156,156,0.15);
            background: rgba(0,0,0,0.35);
        }

        .section-divider {
            display: flex; align-items: center; gap: 10px;
            margin: 20px 0 14px;
            color: var(--c-text-muted);
            font-size: 0.85rem; font-weight: 500;
        }
        .section-divider::before, .section-divider::after {
            content: ""; flex: 1; height: 1px; background: var(--c-border);
        }
        .section-divider i { color: var(--c-accent); }
    </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    {{-- ═══════════ رسائل النظام (نجاح / خطأ / تحقق) ═══════════ --}}
    @if(session('success'))
        <div class="alert alert-success fw-bold rounded-3 mb-3 d-flex align-items-center gap-2 shadow-sm">
            <i class="fa fa-circle-check"></i><span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fw-bold rounded-3 mb-3 d-flex align-items-center gap-2 shadow-sm">
            <i class="fa fa-triangle-exclamation"></i><span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger fw-bold rounded-3 mb-3 shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-1"><i class="fa fa-triangle-exclamation"></i><span>برجاء مراجعة البيانات:</span></div>
            <ul class="mb-0" style="padding-right:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══════════ Hero ═══════════ --}}
    <div class="gas-hero">
        <div class="d-flex justify-content-between align-items-center position-relative" style="z-index:2;">
            <div class="d-flex gap-3 align-items-center">
                <div class="hero-icon"><i class="fa fa-gas-pump"></i></div>
                <div>
                    <h2>محطة عمليات الوقود</h2>
                    <div class="sub">تسجيل السحوبات، العهد النقدية، وتوزيع العمولات أوتوماتيكياً</div>
                </div>
            </div>
            <i class="fa fa-truck-moving" style="font-size:3rem; opacity:0.2;"></i>
        </div>
    </div>

    <div class="row g-4">
        {{-- ═══════════ Form ═══════════ --}}
        <div class="col-lg-7">
            <div class="panel-pro">
                <div class="panel-pro-head">
                    <h5><i class="fa fa-file-pen"></i> بيانات العملية</h5>
                </div>

                <form action="{{ url('/gas-station') }}" method="POST" id="gasStationForm" class="form-grid">
                    @csrf
                    <input type="hidden" name="manual_discount" id="hidden_manual_discount" value="{{ old('manual_discount', 0) }}">

                    {{-- شركة + محطة --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-pro-label"><i class="fa fa-building"></i> شركة النقل / المقاول</label>
                            <input type="hidden" name="company_id"   id="company_id_hidden"   value="{{ old('company_id') }}">
                            <input type="hidden" name="company_name" id="company_name_hidden" value="{{ old('company_name') }}">

                            <div class="combo-wrap" id="wrap_company">
                                <div class="combo-input-row">
                                    <button type="button" class="combo-toggle-btn" onclick="toggleCombo('company')" tabindex="-1">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                    <input type="text" id="combo_company" class="combo-text"
                                           placeholder="اختر أو اكتب اسماً جديداً..."
                                           autocomplete="off" value="{{ old('company_name') }}"
                                           oninput="filterCombo('company')" onfocus="openCombo('company')" required>
                                </div>
                                <div class="combo-dropdown" id="drop_company">
                                    @foreach($companies as $company)
                                        <div class="combo-item"
                                             data-id="{{ $company->id }}"
                                             data-label="{{ $company->company_name ?? $company->name }}"
                                             onclick="selectCombo('company', {{ $company->id }}, '{{ addslashes($company->company_name ?? $company->name) }}')">
                                            <i class="fa fa-building combo-item-icon"></i>
                                            <span>{{ $company->company_name ?? $company->name }}</span>
                                            <span class="combo-item-id">#{{ $company->id }}</span>
                                        </div>
                                    @endforeach
                                    <div class="combo-empty" id="empty_company" style="display:none;">لا توجد نتائج</div>
                                    <div class="combo-new-hint"><i class="fa fa-pencil"></i> اكتب للبحث أو أدخل اسماً جديداً</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-pro-label"><i class="fa fa-location-dot"></i> محطة الوقود (البنزينة)</label>
                            <input type="hidden" name="station_id"   id="station_id_hidden"   value="{{ old('station_id') }}">
                            <input type="hidden" name="station_name" id="station_name_hidden" value="{{ old('station_name') }}">

                            <div class="combo-wrap" id="wrap_station">
                                <div class="combo-input-row">
                                    <button type="button" class="combo-toggle-btn" onclick="toggleCombo('station')" tabindex="-1">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                    <input type="text" id="combo_station" class="combo-text"
                                           placeholder="اختر أو اكتب اسماً جديداً..."
                                           autocomplete="off" value="{{ old('station_name') }}"
                                           oninput="filterCombo('station')" onfocus="openCombo('station')" required>
                                </div>
                                <div class="combo-dropdown" id="drop_station">
                                    @foreach($stations as $station)
                                        <div class="combo-item"
                                             data-id="{{ $station->id }}"
                                             data-label="{{ $station->station_name ?? $station->name }}"
                                             onclick="selectCombo('station', {{ $station->id }}, '{{ addslashes($station->station_name ?? $station->name) }}')">
                                            <i class="fa fa-gas-pump combo-item-icon"></i>
                                            <span>{{ $station->station_name ?? $station->name }}</span>
                                            <span class="combo-item-id">#{{ $station->id }}</span>
                                        </div>
                                    @endforeach
                                    <div class="combo-empty" id="empty_station" style="display:none;">لا توجد نتائج</div>
                                    <div class="combo-new-hint"><i class="fa fa-pencil"></i> اكتب للبحث أو أدخل اسماً جديداً</div>
                                </div>
                            </div>
                        </div>
                    </div>

                

                    {{-- السائق --}}
                    <div class="mb-3">
                        <label class="form-pro-label"><i class="fa fa-id-card"></i> اسم السائق أو رقم السيارة</label>
                        <input type="text" name="driver_car" class="form-pro-control"
                               placeholder="مثال: محمد أحمد - أ ب ج 123"
                               required autocomplete="off" value="{{ old('driver_car') }}">
                    </div>

                    {{-- وقود + كمية --}}
                    <div class="row g-3 mb-3">
                       <div class="col-md-6">
                            <label class="form-pro-label"><i class="fa fa-droplet"></i> نوع الوقود / المنتج</label>
                            <select name="item_id" id="item_id" class="form-pro-control input-calc" required>
                                <option value="0" data-price="0" {{ old('item_id') == '' ? 'selected' : '' }}>عهدة نقدية فقط (بدون وقود)</option>
                                
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" data-price="{{ $item->selling_price ?? $item->cash_price ??  }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->item_name ?? 'وقود بدون اسم' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="quantity_box">
                            <label class="form-pro-label"><i class="fa fa-flask"></i> الكمية (باللتر)</label>
                            <input type="number" step="1" name="quantity" id="quantity"
                                   class="form-pro-control input-calc" value="{{ old('quantity', ) }}">
                        </div>
                    </div>

                    <div class="section-divider"><i class="fa fa-money-bill-wave"></i> تفاصيل العهدة النقدية</div>

                    <div class="mb-3" id="has_advance_box">
                        <label class="form-pro-label"><i class="fa fa-circle-question"></i> هل تتضمن العملية عهدة نقدية؟</label>
                        <select id="has_advance" class="form-pro-control input-calc">
                            <option value="no"  {{ old('advance_source', 'no') == 'none' || old('advance_source') == 'no' ? 'selected' : '' }}>لا — عملية وقود فقط</option>
                            <option value="yes" {{ in_array(old('advance_source'), ['station','safe']) ? 'selected' : '' }}>نعم — يوجد عهدة نقدية للسائق</option>
                        </select>
                    </div>

                    <div id="advance_details_box" class="row g-3 mb-3" style="display: none;">
                        <div class="col-md-5">
                            <label class="form-pro-label" style="color: var(--c-danger);">المبلغ المطلوب (ج)</label>
                            <input type="number" step="1" name="cash_advance" min="0" id="cash_advance"
                                   class="form-pro-control input-calc" value="{{ old('cash_advance', ) }}"
                                   style="border-color: var(--c-danger);">
                        </div>
                        <div class="col-md-7">
                            <label class="form-pro-label" style="color: var(--c-accent);">جهة صرف العهدة النقدية</label>
                            <select id="mixed_source" class="form-pro-control input-calc">
                                <option value="station" {{ old('advance_source', 'station') == 'station' ? 'selected' : '' }}>🏪 من المحطة</option>
                                <optgroup label="🏦 سحب من خزائن الشركة (العمولة على الإجمالي)">
                                    @foreach($payment_methods as $acc)
                                        <option value="{{ $acc->id }}" {{ old('advance_source') == 'safe' && old('account_id') == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->account_name }} (رصيد: {{ number_format($acc->balance, 0) }} ج)
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="advance_source" id="hidden_advance_source" value="{{ old('advance_source', 'none') }}">
                    <input type="hidden" name="account_id" id="hidden_account_id" value="{{ old('account_id') }}">

                    <button type="submit" id="btn_submit" class="btn-pro btn-pro-accent btn-pro-lg w-100 mt-2">
                        <i class="fa fa-check-circle"></i> اعتماد وتسجيل الحركة المحاسبية
                    </button>
                </form>
            </div>
        </div>

        {{-- ═══════════ Calculator ═══════════ --}}
        <div class="col-lg-5">
            <div class="calc-panel">
                <h5><i class="fa fa-calculator"></i> شاشة الحسابات المباشرة</h5>

                <div class="calc-row">
                    <span class="lbl">حساب البنزينة الصافي</span>
                    <span class="val"><span id="res_station">0</span> ج</span>
                </div>
                <div class="calc-row info">
                    <span class="lbl">إجمالي عمولة النظام (1%)</span>
                    <span class="val">+ <span id="gross_profit">0</span> ج</span>
                </div>
                <div class="calc-row danger">
                    <span class="lbl"><i class="fa fa-scissors me-1"></i> خصم يدوي من العمولة</span>
                    <span class="val">- <span id="manual_discount_display"></span> ج</span>
                </div>

                <div class="my-3">
                    <label class="form-pro-label" style="color: #ffd980; font-size: 0.82rem;">
                        <i class="fa fa-scissors me-1"></i> خصم يدوي من العمولة (ج)
                    </label>
                    <input type="number" step="1" id="manual_discount" class="manual-discount-input input-calc"
                           value="{{ old('manual_discount', ) }}" placeholder="">
                    <small style="color: rgba(255,156,156,0.7); font-size: 0.72rem;">سيتم خصمه قبل الاستقطاعات</small>
                </div>

                <h6 style="color: #ffd980; font-size: 0.85rem; font-weight: 600; margin-top: 16px;">
                    <i class="fa fa-chart-pie me-1"></i> توزيع الاستقطاعات
                </h6>
                <div class="mb-2">
                    @forelse($deductions as $ded)
                        <div class="deduction-item">
                            <span>{{ $ded->name }} ({{ $ded->percentage }}%)</span>
                            <span class="val">- <span class="deduction-val-span" data-pct="{{ $ded->percentage }}">0</span> ج</span>
                        </div>
                    @empty
                        <div style="text-align:center; color: rgba(255,255,255,0.5); font-size: 0.8rem; padding: 6px;">
                            لا توجد استقطاعات نشطة
                        </div>
                    @endforelse
                </div>

                <div class="calc-final">
                    <span class="lbl"><i class="fa fa-wallet me-2"></i>صافي الربح الفعلي</span>
                    <span class="val"><span id="net_profit">0</span> ج</span>
                </div>

                <div class="calc-summary-bottom">
                    <div class="lbl">إجمالي الحساب على الشركة</div>
                    <div class="val"><span id="res_company">0</span> ج</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ كروت المستحقات والديون للشركة/المحطة المختارة ═══════════ --}}
    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="panel-pro" style="border-inline-start: 4px solid var(--c-success); height:100%;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div style="color: var(--c-text-muted); font-size: 0.82rem; font-weight: 600;">
                            <i class="fa fa-hand-holding-dollar me-1" style="color: var(--c-success);"></i>
                            مستحقات لينا (على الشركة)
                        </div>
                        <div id="dues_company_name" style="color: var(--c-text-soft); font-size: 0.78rem; margin-top: 4px;">— اختر شركة النقل —</div>
                    </div>
                    <div style="text-align: end;">
                        <div style="color: var(--c-success); font-weight: 700; font-size: 1.6rem;"><span id="dues_company_val">0</span> <span style="font-size:0.9rem;">ج</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel-pro" style="border-inline-start: 4px solid var(--c-danger); height:100%;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div style="color: var(--c-text-muted); font-size: 0.82rem; font-weight: 600;">
                            <i class="fa fa-money-bill-transfer me-1" style="color: var(--c-danger);"></i>
                            ديون علينا (للمحطة)
                        </div>
                        <div id="debts_station_name" style="color: var(--c-text-soft); font-size: 0.78rem; margin-top: 4px;">— اختر محطة الوقود —</div>
                    </div>
                    <div style="text-align: end;">
                        <div style="color: var(--c-danger); font-weight: 700; font-size: 1.6rem;"><span id="debts_station_val">0</span> <span style="font-size:0.9rem;">ج</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function calculateGasStation() {
        let itemSelect = document.getElementById('item_id');
        let selectedOption = itemSelect.options[itemSelect.selectedIndex];
        let price = parseFloat(selectedOption.getAttribute('data-price')) || 0;

        // 💡 "عهدة نقدية فقط (بدون وقود)" => العملية لازم تتضمن عهدة، فنخفي السؤال التاني ونفرض "نعم"
        let isCustodyOnly = itemSelect.value === '0';
        let hasAdvanceSelect = document.getElementById('has_advance');
        let hasAdvanceBox = document.getElementById('has_advance_box');
        let quantityBox = document.getElementById('quantity_box');
        if (isCustodyOnly) {
            hasAdvanceSelect.value = 'yes';
            if (hasAdvanceBox) hasAdvanceBox.style.display = 'none';
            if (quantityBox) quantityBox.style.display = 'none';
        } else {
            if (hasAdvanceBox) hasAdvanceBox.style.display = '';
            if (quantityBox) quantityBox.style.display = '';
        }

        let quantity = parseInt(document.getElementById('quantity').value) || 0;
        let hasAdvance = document.getElementById('has_advance').value === 'yes';
        let advanceBox = document.getElementById('advance_details_box');
        advanceBox.style.display = hasAdvance ? 'flex' : 'none';

        let advance = 0;
        let advanceSource = 'none';
        let mixedSource = 'station';

        if (hasAdvance) {
            advance = parseInt(document.getElementById('cash_advance').value) || 0;
            mixedSource = document.getElementById('mixed_source').value;
            if (mixedSource === 'station') {
                advanceSource = 'station';
                document.getElementById('hidden_advance_source').value = 'station';
                document.getElementById('hidden_account_id').value = '';
            } else {
                advanceSource = 'safe';
                document.getElementById('hidden_advance_source').value = 'safe';
                document.getElementById('hidden_account_id').value = mixedSource;
            }
        } else {
            advance = 0;
            advanceSource = 'none';
            document.getElementById('hidden_advance_source').value = 'none';
            document.getElementById('hidden_account_id').value = '';
            let ca = document.getElementById('cash_advance');
            if (ca) ca.value = 0;
        }

        let fuelDebt = quantity * price;
        let manualDiscount = parseInt(document.getElementById('manual_discount').value) || 0;

        let grossProfit = advanceSource === 'none'
            ? fuelDebt * 0.01
            : (fuelDebt + advance) * 0.01;

        let totalToStation = advanceSource === 'station' ? fuelDebt + advance : fuelDebt;
        let totalOnCompany = fuelDebt + advance + grossProfit;

        document.getElementById('res_station').innerText = Math.round(totalToStation).toLocaleString();
        document.getElementById('gross_profit').innerText = Math.round(grossProfit).toLocaleString();
        document.getElementById('res_company').innerText = Math.round(totalOnCompany).toLocaleString();

        let totalDeductions = 0;
        let profitAfterDiscount = grossProfit - manualDiscount;

        document.querySelectorAll('.deduction-val-span').forEach(span => {
            let pct = parseFloat(span.getAttribute('data-pct')) || 0;
            let currentDeductionVal = profitAfterDiscount * (pct / 100);
            span.innerText = Math.round(currentDeductionVal).toLocaleString();
            totalDeductions += currentDeductionVal;
        });

        let netProfit = profitAfterDiscount - totalDeductions;
        document.getElementById('manual_discount_display').innerText = Math.round(manualDiscount).toLocaleString();
        document.getElementById('net_profit').innerText = Math.round(netProfit).toLocaleString();
    }

    document.querySelectorAll('.input-calc').forEach(input => {
        input.addEventListener('input', calculateGasStation);
        input.addEventListener('change', calculateGasStation);
    });

    let manualDiscountEl = document.getElementById('manual_discount');
    manualDiscountEl.addEventListener('input', function() {
        document.getElementById('hidden_manual_discount').value = this.value;
        calculateGasStation();
    });
    manualDiscountEl.addEventListener('change', calculateGasStation);

    document.addEventListener("DOMContentLoaded", function() { calculateGasStation(); });

    // ── COMBOBOX ──
    const comboData = {
        company: {
            allItems: Array.from(document.querySelectorAll('#drop_company .combo-item')),
            inputEl:  () => document.getElementById('combo_company'),
            dropEl:   () => document.getElementById('drop_company'),
            emptyEl:  () => document.getElementById('empty_company'),
            idHidden: () => document.getElementById('company_id_hidden'),
            nameHidden:() => document.getElementById('company_name_hidden'),
        },
        station: {
            allItems: Array.from(document.querySelectorAll('#drop_station .combo-item')),
            inputEl:  () => document.getElementById('combo_station'),
            dropEl:   () => document.getElementById('drop_station'),
            emptyEl:  () => document.getElementById('empty_station'),
            idHidden: () => document.getElementById('station_id_hidden'),
            nameHidden:() => document.getElementById('station_name_hidden'),
        },
    };

    function openCombo(key) { comboData[key].dropEl().classList.add('open'); filterCombo(key); }
    function closeCombo(key) { comboData[key].dropEl().classList.remove('open'); }
    function toggleCombo(key) {
        const drop = comboData[key].dropEl();
        if (drop.classList.contains('open')) closeCombo(key);
        else { comboData[key].inputEl().focus(); openCombo(key); }
    }
    function filterCombo(key) {
        const d = comboData[key];
        const q = d.inputEl().value.trim().toLowerCase();
        let visible = 0;
        d.allItems.forEach(item => {
            const label = item.dataset.label.toLowerCase();
            const match = !q || label.includes(q);
            item.style.display = match ? 'flex' : 'none';
            if (match) visible++;
        });
        d.emptyEl().style.display = visible === 0 ? 'block' : 'none';
        syncHiddenFromText(key);
    }
    function selectCombo(key, id, label) {
        const d = comboData[key];
        d.inputEl().value = label;
        d.idHidden().value = id;
        d.nameHidden().value = label;
        closeCombo(key);
        updateDuesCards();
    }
    function syncHiddenFromText(key) {
        const d = comboData[key];
        const typed = d.inputEl().value.trim();
        const exact = d.allItems.find(i => i.dataset.label.toLowerCase() === typed.toLowerCase());
        if (exact) { d.idHidden().value = exact.dataset.id; d.nameHidden().value = exact.dataset.label; }
        else { d.idHidden().value = ''; d.nameHidden().value = typed; }
        updateDuesCards();
    }

    // ── كروت المستحقات (على الشركة) والديون (للمحطة) حسب الاختيار ──
    const COMPANY_DUES   = @json($companyDues);
    const STATION_DEBTS  = @json($stationDebts);
    function fmtDues(n) { return Math.round(parseFloat(n) || 0).toLocaleString('en-US'); }
    function updateDuesCards() {
        const companyName = (document.getElementById('company_name_hidden').value || document.getElementById('combo_company').value || '').trim();
        const stationName = (document.getElementById('station_name_hidden').value || document.getElementById('combo_station').value || '').trim();

        const compNameEl = document.getElementById('dues_company_name');
        const compValEl  = document.getElementById('dues_company_val');
        if (companyName) {
            compNameEl.innerText = companyName;
            compValEl.innerText  = fmtDues(COMPANY_DUES[companyName] || 0);
        } else {
            compNameEl.innerText = '— اختر شركة النقل —';
            compValEl.innerText  = '0';
        }

        const statNameEl = document.getElementById('debts_station_name');
        const statValEl  = document.getElementById('debts_station_val');
        if (stationName) {
            statNameEl.innerText = stationName;
            statValEl.innerText  = fmtDues(STATION_DEBTS[stationName] || 0);
        } else {
            statNameEl.innerText = '— اختر محطة الوقود —';
            statValEl.innerText  = '0';
        }
    }
    document.addEventListener('DOMContentLoaded', updateDuesCards);
    document.addEventListener('click', function(e) {
        ['company', 'station'].forEach(key => {
            const wrap = document.getElementById('wrap_' + key);
            if (wrap && !wrap.contains(e.target)) { closeCombo(key); syncHiddenFromText(key); }
        });
    });
    document.getElementById('gasStationForm').addEventListener('submit', function(e) {
        ['company', 'station'].forEach(key => syncHiddenFromText(key));
        const companyVal = document.getElementById('combo_company').value.trim();
        const stationVal = document.getElementById('combo_station').value.trim();
        if (!companyVal) {
            e.preventDefault();
            document.getElementById('combo_company').focus();
            document.getElementById('combo_company').closest('.combo-input-row').style.borderColor = 'var(--c-danger)';
            return;
        }
        if (!stationVal) {
            e.preventDefault();
            document.getElementById('combo_station').focus();
            document.getElementById('combo_station').closest('.combo-input-row').style.borderColor = 'var(--c-danger)';
        }
    });
    ['company','station'].forEach(key => {
        comboData[key].inputEl().addEventListener('input', () => filterCombo(key));
    });
</script>
</body>
</html>
