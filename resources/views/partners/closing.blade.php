<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إقفال الدورة الشهرية وتوزيع الأرباح - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;
            --brand-primary: #1e40af;
            --brand-success: #047857;
            --brand-danger:  #b91c1c;
            --brand-amber:   #b45309;
        }
        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--slate-50);
            color: var(--slate-900);
            font-size: 0.92rem;
        }
        .main-content {
            padding: 24px 20px;
        }
        .card-formal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }
        .formula-card-formal {
            background: var(--slate-900);
            border: 1px solid var(--slate-800);
            border-radius: 8px;
            color: #ffffff;
            padding: 20px;
            margin-bottom: 24px;
        }
        .formula-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .formula-box .label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .formula-box .val {
            font-size: 1.25rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }
        .table-formal {
            margin: 0;
            vertical-align: middle;
            font-size: 0.88rem;
        }
        .table-formal thead th {
            background: var(--slate-800);
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 12px 10px;
            border: none;
            white-space: nowrap;
        }
        .table-formal tbody td {
            vertical-align: middle;
            padding: 12px 10px;
            border-bottom: 1px solid var(--slate-200);
            font-variant-numeric: tabular-nums;
        }
        .btn-decision {
            font-size: 0.78rem;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 4px !important;
            transition: all 0.15s ease-in-out;
        }
        .btn-decision.active {
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .btn-decision-reinvest.active {
            background-color: var(--brand-success) !important;
            border-color: var(--brand-success) !important;
            color: #ffffff !important;
        }
        .btn-decision-withdraw-all.active {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            color: #ffffff !important;
        }
        .btn-decision-withdraw-partial.active {
            background-color: var(--slate-700) !important;
            border-color: var(--slate-700) !important;
            color: #ffffff !important;
        }
        .badge-formal {
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        @media print {
            *, *::before, *::after {
                overflow: visible !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
                box-shadow: none !important;
            }
            ::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, button, .btn {
                display: none !important;
            }
            .table-responsive {
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة الرئيسية --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة إلى سجل الشركاء
                </a>
                <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    <i class="fa fa-file-invoice-dollar text-primary"></i> إقفال الدورة الشهرية وتوزيع الأرباح
                </h4>
            </div>
            <div class="text-muted small">
                <i class="fa fa-shield-alt text-success me-1"></i> وحدة الإدارة المالية المعتمدة · صلاحيات الإدارة فقط
            </div>
        </div>

        {{-- شريط تحديد تواريخ الفترة واحتساب رأس المال --}}
        <div class="card-formal p-3 mb-4">
            <form action="{{ route('partners.closing') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">
                        <i class="fa fa-calendar-alt text-primary me-1"></i> تاريخ بداية الدورة (الإقفال السابق)
                    </label>
                    <input type="date" name="start_date" class="form-control form-control-sm fw-bold" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">
                        <i class="fa fa-calendar-check text-success me-1"></i> تاريخ نهاية الدورة (تاريخ الإقفال)
                    </label>
                    <input type="date" name="end_date" class="form-control form-control-sm fw-bold" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="use_live" value="1" id="switchLive" {{ $useLiveCapital ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label fw-bold small" for="switchLive">
                            احتساب رأس المال المباشر (لحظياً)
                        </label>
                    </div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">مطابقة فورية لأرصدة الخزائن والديون والسيولة النقدية</small>
                </div>
                <div class="col-md-3 text-start">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100 py-2">
                        <i class="fa fa-sync-alt me-1"></i> تحديث حسابات المعاينة
                    </button>
                </div>
            </form>
        </div>

        {{-- بطاقة المعادلة المالية لاحتساب الربح القابل للتوزيع --}}
        <div class="formula-card-formal">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-50">
                <span class="fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa fa-calculator text-warning"></i> معادلة احتساب الربح القابل للتوزيع (الأرباح = رأس مال الإقفال الحالي − رأس مال الإقفال الأخير)
                </span>
                <span class="badge bg-secondary font-monospace">
                    الفترة: {{ $preview['start_date'] }} إلى {{ $preview['end_date'] }}
                </span>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="formula-box py-3">
                        <div class="label fs-6 mb-1">1. رأس مال الإقفال الأخير</div>
                        <div class="val text-info fs-3">{{ number_format($preview['opening_capital'], 2) }} ج</div>
                        <small class="text-muted">تاريخ: {{ $preview['start_date'] }}</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="formula-box py-3">
                        <div class="label fs-6 mb-1">2. رأس مال الإقفال الحالي</div>
                        <div class="val text-light fs-3">{{ number_format($preview['closing_capital'], 2) }} ج</div>
                        <small class="text-muted">تاريخ: {{ $preview['end_date'] }}</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="formula-box py-3" style="background: rgba(4, 120, 87, 0.25); border-color: rgba(4, 120, 87, 0.6);">
                        <div class="label text-success fw-bold fs-6 mb-1">صافي الربح القابل للتوزيع (R)</div>
                        <div class="val {{ $preview['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }} fs-3">
                            {{ $preview['gross_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['gross_profit'], 2) }} ج
                        </div>
                        <small class="text-light opacity-75">الأرباح = (2) رأس مال الإقفال الحالي − (1) رأس مال الإقفال الأخير</small>
                    </div>
                </div>
            </div>

            @if($preview['gross_profit'] < 0)
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mt-3 mb-0 py-2 px-3 small rounded">
                <i class="fa fa-exclamation-triangle me-1"></i> تنبيه: سجلت الدورة الحالية عجزاً/خسارة قدرها {{ number_format(abs($preview['gross_profit']), 2) }} ج. بموجب شروط النظام، يتحمل كل شريك حصته من الخسارة كاملة من رأس ماله مع عدم استحقاق أي عمولات إدارة لرئيس الإدارة.
            </div>
            @endif
        </div>

        {{-- جدول توزيع الحصص وقرارات الشركاء --}}
        <form action="{{ route('partners.closing.store') }}" method="POST" id="formExecuteClosing">
            @csrf
            <input type="hidden" name="start_date" value="{{ $preview['start_date'] }}">
            <input type="hidden" name="end_date" value="{{ $preview['end_date'] }}">
            <input type="hidden" name="closing_capital" value="{{ $preview['closing_capital'] }}">

            <div class="card-formal mb-4 overflow-hidden">
                <div class="bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 border-bottom">
                    <div>
                        <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa fa-users text-primary"></i> بيان توزيع أرباح الشركاء للدورة
                        </h5>
                        <small class="text-muted">حدد خيار كل شريك للدورة الحالية: إعادة استثمار الحصة بالكامل، أو صرفها نقداً، أو سحب جزء وتدوير المتبقي.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-success fw-bold" onclick="setAllDecisions('reinvest')">
                                <i class="fa fa-sync-alt me-1"></i> تعيين الكل: إعادة استثمار
                            </button>
                            <button type="button" class="btn btn-outline-primary fw-bold" onclick="setAllDecisions('withdraw_all')">
                                <i class="fa fa-money-bill-wave me-1"></i> تعيين الكل: سحب كامل
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2 border-start ps-2">
                            <label class="form-label m-0 fw-bold small text-nowrap">اسم الدورة:</label>
                            <input type="text" name="period_name" class="form-control form-control-sm fw-bold" style="width: 250px;" value="إقفال دورة شهر {{ \Carbon\Carbon::parse($preview['end_date'])->translatedFormat('F Y') }}">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-formal table-hover m-0 align-middle">
                        <thead>
                            <tr>
                                <th>الشريك</th>
                                <th>رأس المال السابق</th>
                                <th class="text-center">نسبة المساهمة</th>
                                <th>إجمالي الحصة (g)</th>
                                <th>نسبة الإدارة</th>
                                <th>عمولة الإدارة (a)</th>
                                <th>صافي ربح الشريك (n)</th>
                                <th style="min-width: 260px;">قرار تصرف الأرباح للدورة</th>
                                <th style="min-width: 240px;">تفاصيل الصرف والخزينة</th>
                                <th style="min-width: 170px;">رأس المال الجديد المعتمد</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- سطر رئيس مجلس الإدارة --}}
                            @php
                                $ahmedOpenCap = $preview['ahmed']['opening_capital'];
                                $ahmedNetProfit = $preview['ahmed']['total_profit'];
                            @endphp
                            <tr id="row_partner_ahmed" data-pid="ahmed" data-opencap="{{ $ahmedOpenCap }}" data-net="{{ $ahmedNetProfit }}" style="background-color: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <strong class="text-dark d-block">{{ $headName }}</strong>
                                            <span class="badge bg-dark text-white fw-bold" style="font-size:0.7rem;">رئيس مجلس الإدارة</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold">{{ number_format($ahmedOpenCap, 2) }} ج</td>
                                <td class="text-center">
                                    <span class="badge-formal bg-light text-primary border">
                                        {{ number_format($preview['ahmed']['capital_share_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="fw-bold {{ $preview['ahmed']['own_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $preview['ahmed']['own_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['ahmed']['own_profit'], 2) }} ج
                                    <small class="d-block text-muted" style="font-size:0.7rem;">ربح الحصة الشخصية</small>
                                </td>
                                <td>
                                    <span class="badge-formal bg-light text-secondary border">
                                        الجهة المديرة
                                    </span>
                                </td>
                                <td class="fw-bold text-primary">
                                    +{{ number_format($preview['ahmed']['commissions'], 2) }} ج
                                    <small class="d-block text-muted" style="font-size:0.7rem;">أتعاب إدارة الشركاء</small>
                                </td>
                                <td class="fw-bold fs-6 {{ $ahmedNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $ahmedNetProfit >= 0 ? '+' : '' }}{{ number_format($ahmedNetProfit, 2) }} ج
                                    <small class="d-block text-muted" style="font-size:0.7rem;">إجمالي الدخل المحقق</small>
                                </td>

                                {{-- عمود القرار المباشر لرئيس الإدارة --}}
                                <td>
                                    @if($ahmedNetProfit > 0)
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <button type="button" class="btn btn-outline-success btn-decision btn-decision-reinvest btn-decision-ahmed active" id="btn_opt_reinvest_ahmed" onclick="setPartnerDecision('ahmed', 'reinvest', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                            <i class="fa fa-sync-alt me-1"></i> إعادة استثمار
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-decision btn-decision-withdraw-all btn-decision-ahmed" id="btn_opt_withdraw_all_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_all', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                            <i class="fa fa-money-bill-wave me-1"></i> سحب كامل
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-decision btn-decision-withdraw-partial btn-decision-ahmed" id="btn_opt_withdraw_partial_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_partial', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                            <i class="fa fa-sliders-h me-1"></i> سحب جزئي
                                        </button>
                                    </div>
                                    <input type="hidden" name="decisions[ahmed][payout_action]" id="input_action_ahmed" value="reinvest">
                                    <input type="hidden" name="decisions[ahmed][custom_withdrawn]" id="input_withdrawn_ahmed" value="0">
                                    @else
                                    <span class="badge-formal bg-danger-subtle text-danger border border-danger-subtle p-2 text-wrap d-block">
                                        <i class="fa fa-exclamation-circle me-1"></i> خصم الخسارة من رأس المال (-{{ number_format(abs($ahmedNetProfit), 2) }} ج)
                                    </span>
                                    <input type="hidden" name="decisions[ahmed][payout_action]" value="reinvest">
                                    <input type="hidden" name="decisions[ahmed][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- تفاصيل الصرف والخزينة لرئيس الإدارة --}}
                                <td>
                                    @if($ahmedNetProfit > 0)
                                        {{-- 1. حالة إعادة الاستثمار --}}
                                        <div id="box_reinvest_ahmed" class="payout-box-ahmed p-2 rounded bg-light text-success border text-center small fw-bold">
                                            <i class="fa fa-check-circle me-1"></i> إضافة كامل أرباح رئيس الإدارة (+{{ number_format($ahmedNetProfit, 2) }} ج) إلى رأس ماله
                                        </div>

                                        {{-- 2. حالة السحب الكامل --}}
                                        <div id="box_withdraw_all_ahmed" class="payout-box-ahmed" style="display:none;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="small fw-bold text-muted">مبلغ الصرف النقدي لرئيس الإدارة:</span>
                                                <span class="badge bg-danger fs-6 font-monospace">{{ number_format($ahmedNetProfit, 2) }} ج</span>
                                            </div>
                                            <select name="decisions[ahmed][account_id]" id="acc_sel_ahmed" class="form-select form-select-sm fw-bold border-danger treasury-select" data-pid="ahmed" data-name="{{ $headName }}">
                                                <option value="">-- تحديد خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 3. حالة السحب الجزئي --}}
                                        <div id="box_withdraw_partial_ahmed" class="payout-box-ahmed" style="display:none;">
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold m-0 text-muted">المبلغ المراد سحبه نقداً:</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="1" max="{{ $ahmedNetProfit }}" id="inp_partial_val_ahmed" class="form-control fw-bold text-danger" value="{{ round($ahmedNetProfit / 2, 2) }}" placeholder="المبلغ المسحوب" oninput="onPartialInput('ahmed', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                    <span class="input-group-text bg-white fw-bold">ج</span>
                                                </div>
                                                <div class="small text-muted fw-bold mt-1" id="partial_rollover_badge_ahmed">
                                                    المتبقي للتدوير في رأس المال: <strong class="text-success">{{ number_format($ahmedNetProfit - round($ahmedNetProfit / 2, 2), 2) }} ج</strong>
                                                </div>
                                            </div>
                                            <select id="acc_partial_sel_ahmed" class="form-select form-select-sm fw-bold border-secondary treasury-select-partial" data-pid="ahmed" data-name="{{ $headName }}" onchange="syncPartialAccount('ahmed')">
                                                <option value="">-- تحديد خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="text-center text-muted small py-2">
                                            — لا يوجد صرف نقدي —
                                        </div>
                                    @endif
                                </td>

                                {{-- رأس المال الجديد المعتمد لرئيس الإدارة --}}
                                <td class="fw-bold fs-6" id="cell_cap_ahmed">
                                    <div class="text-dark fw-bold" id="new_cap_display_ahmed">
                                        {{ number_format(max(0, $ahmedOpenCap + $ahmedNetProfit), 2) }} ج
                                    </div>
                                    <small id="new_cap_sub_ahmed" class="d-block {{ $ahmedNetProfit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.72rem;">
                                        @if($ahmedNetProfit >= 0)
                                            <i class="fa fa-arrow-up me-1"></i> زيادة بإضافة الربح (+{{ number_format($ahmedNetProfit, 2) }} ج)
                                        @else
                                            <i class="fa fa-arrow-down me-1"></i> انخفاض بمقدار الخسارة (-{{ number_format(abs($ahmedNetProfit), 2) }} ج)
                                        @endif
                                    </small>
                                </td>
                            </tr>

                            {{-- أسطر باقي الشركاء --}}
                            @forelse($preview['partner_lines'] as $line)
                            @php $pid = $line['partner_id']; @endphp
                            <tr id="row_partner_{{ $pid }}" data-pid="{{ $pid }}" data-opencap="{{ $line['opening_capital'] }}" data-net="{{ $line['net_share'] }}">
                                <td>
                                    <strong class="text-dark d-block">{{ $line['name'] }}</strong>
                                    <small class="text-muted">{{ $line['phone'] ?: '—' }}</small>
                                </td>
                                <td class="fw-bold">{{ number_format($line['opening_capital'], 2) }} ج</td>
                                <td class="text-center">
                                    <span class="badge-formal bg-light text-primary border">
                                        {{ number_format($line['capital_share_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="fw-bold {{ $line['gross_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['gross_share'] >= 0 ? '+' : '' }}{{ number_format($line['gross_share'], 2) }} ج
                                </td>
                                <td>
                                    <span class="badge-formal bg-light text-dark border">
                                        {{ number_format($line['ahmed_ratio_pct'], 1) }}%
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    +{{ number_format($line['ahmed_commission'], 2) }} ج
                                </td>
                                <td class="fw-bold fs-6 {{ $line['net_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['net_share'] >= 0 ? '+' : '' }}{{ number_format($line['net_share'], 2) }} ج
                                </td>

                                {{-- عمود القرار المباشر --}}
                                <td>
                                    @if($line['net_share'] > 0)
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <button type="button" class="btn btn-outline-success btn-decision btn-decision-reinvest btn-decision-{{ $pid }} active" id="btn_opt_reinvest_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'reinvest', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                            <i class="fa fa-sync-alt me-1"></i> إعادة استثمار
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-decision btn-decision-withdraw-all btn-decision-{{ $pid }}" id="btn_opt_withdraw_all_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_all', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                            <i class="fa fa-money-bill-wave me-1"></i> سحب كامل
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-decision btn-decision-withdraw-partial btn-decision-{{ $pid }}" id="btn_opt_withdraw_partial_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_partial', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                            <i class="fa fa-sliders-h me-1"></i> سحب جزئي
                                        </button>
                                    </div>
                                    <input type="hidden" name="decisions[{{ $pid }}][payout_action]" id="input_action_{{ $pid }}" value="reinvest">
                                    <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" id="input_withdrawn_{{ $pid }}" value="0">
                                    @else
                                    <span class="badge-formal bg-danger-subtle text-danger border border-danger-subtle p-2 text-wrap d-block">
                                        <i class="fa fa-exclamation-circle me-1"></i> خصم الخسارة من رأس المال (-{{ number_format(abs($line['net_share']), 2) }} ج)
                                    </span>
                                    <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                    <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- تفاصيل الصرف والخزينة --}}
                                <td>
                                    @if($line['net_share'] > 0)
                                        {{-- 1. حالة إعادة الاستثمار --}}
                                        <div id="box_reinvest_{{ $pid }}" class="payout-box-{{ $pid }} p-2 rounded bg-light text-success border text-center small fw-bold">
                                            <i class="fa fa-check-circle me-1"></i> إضافة كامل الربح (+{{ number_format($line['net_share'], 2) }} ج) إلى رأس المال
                                        </div>

                                        {{-- 2. حالة السحب الكامل --}}
                                        <div id="box_withdraw_all_{{ $pid }}" class="payout-box-{{ $pid }}" style="display:none;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="small fw-bold text-muted">مبلغ الصرف النقدي:</span>
                                                <span class="badge bg-danger fs-6 font-monospace">{{ number_format($line['net_share'], 2) }} ج</span>
                                            </div>
                                            <select name="decisions[{{ $pid }}][account_id]" id="acc_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-danger treasury-select" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}">
                                                <option value="">-- تحديد خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 3. حالة السحب الجزئي --}}
                                        <div id="box_withdraw_partial_{{ $pid }}" class="payout-box-{{ $pid }}" style="display:none;">
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold m-0 text-muted">المبلغ المراد سحبه نقداً:</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="1" max="{{ $line['net_share'] }}" id="inp_partial_val_{{ $pid }}" class="form-control fw-bold text-danger" value="{{ round($line['net_share'] / 2, 2) }}" placeholder="المبلغ المسحوب" oninput="onPartialInput({{ $pid }}, {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                    <span class="input-group-text bg-white fw-bold">ج</span>
                                                </div>
                                                <div class="small text-muted fw-bold mt-1" id="partial_rollover_badge_{{ $pid }}">
                                                    المتبقي للتدوير في رأس المال: <strong class="text-success">{{ number_format($line['net_share'] - round($line['net_share'] / 2, 2), 2) }} ج</strong>
                                                </div>
                                            </div>
                                            <select id="acc_partial_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-secondary treasury-select-partial" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}" onchange="syncPartialAccount({{ $pid }})">
                                                <option value="">-- تحديد خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="text-center text-muted small py-2">
                                            — لا يوجد صرف نقدي —
                                        </div>
                                    @endif
                                </td>

                                {{-- رأس المال الجديد المعتمد --}}
                                <td class="fw-bold fs-6" id="cell_cap_{{ $pid }}">
                                    <div class="text-dark fw-bold" id="new_cap_display_{{ $pid }}">
                                        {{ number_format($line['closing_capital'], 2) }} ج
                                    </div>
                                    <small id="new_cap_sub_{{ $pid }}" class="d-block {{ $line['net_share'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.72rem;">
                                        @if($line['net_share'] >= 0)
                                            <i class="fa fa-arrow-up me-1"></i> زيادة بإضافة الربح الصافي (+{{ number_format($line['net_share'], 2) }} ج)
                                        @else
                                            <i class="fa fa-arrow-down me-1"></i> انخفاض بمقدار الخسارة (-{{ number_format(abs($line['net_share']), 2) }} ج)
                                        @endif
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">لا يوجد شركاء نشطين لتوزيع الأرباح عليهم في هذه الفترة.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light p-3 border-top">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small m-0 me-2 text-muted">
                                <i class="fa fa-file-alt text-secondary me-1"></i> ملاحظات التقفيل والاعتماد:
                            </label>
                            <input type="text" name="notes" class="form-control form-control-sm mt-1" placeholder="مثال: تم إقفال دورة الشهر بعد مراجعة تقارير المبيعات وجرد الخزائن...">
                        </div>
                        <div class="col-md-4 text-start">
                            <button type="submit" class="btn btn-dark fw-bold px-4 py-2 w-100 shadow-sm">
                                <i class="fa fa-check-circle me-1"></i> اعتماد التقفيل وترحيل الحسابات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- ملخص أتعاب وعوائد الإدارة --}}
        <div class="card-formal p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-light text-primary rounded border fs-4">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">عوائد إدارة النظام ({{ $headName }})</h6>
                            <small class="text-muted">أرباح الحصة الشخصية + عمولات أتعاب إدارة الشركاء</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">أرباح حصة رأس المال الشخصي</small>
                    <strong class="fs-5 text-dark font-monospace">{{ number_format($preview['ahmed']['own_profit'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">نسبة رأس المال: {{ number_format($preview['ahmed']['capital_share_pct'], 2) }}%</div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">إجمالي عمولات أتعاب إدارة الشركاء</small>
                    <strong class="fs-5 text-primary font-monospace">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">نظير الإدارة والتشغيل</div>
                </div>

                <div class="col-md-3 text-center border-start bg-light py-3 rounded">
                    <small class="text-dark fw-bold d-block mb-1">إجمالي صافي الدخل المحقق للإدارة</small>
                    <strong class="fs-4 text-success font-monospace">{{ number_format($preview['ahmed']['total_profit'], 2) }} ج</strong>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /**
     * تغيير قرار الشريك للدورة الحالية
     */
    function setPartnerDecision(pid, action, openCap, netShare) {
        document.querySelectorAll('.btn-decision-' + pid).forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('btn_opt_' + action + '_' + pid);
        if (activeBtn) activeBtn.classList.add('active');

        const inputAction = document.getElementById('input_action_' + pid);
        if (inputAction) inputAction.value = action;

        const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
        const accSel = document.getElementById('acc_sel_' + pid);
        const accPartialSel = document.getElementById('acc_partial_sel_' + pid);

        document.querySelectorAll('.payout-box-' + pid).forEach(box => box.style.display = 'none');

        let withdrawn = 0;
        let reinvested = netShare;

        if (action === 'withdraw_all') {
            const box = document.getElementById('box_withdraw_all_' + pid);
            if (box) box.style.display = 'block';
            withdrawn = netShare;
            reinvested = 0;
            if (inputWithdrawn) inputWithdrawn.value = withdrawn.toFixed(2);
            if (accSel) {
                accSel.name = 'decisions[' + pid + '][account_id]';
                accSel.required = true;
            }
            if (accPartialSel) {
                accPartialSel.name = '';
                accPartialSel.required = false;
            }
        } else if (action === 'withdraw_partial') {
            const box = document.getElementById('box_withdraw_partial_' + pid);
            if (box) box.style.display = 'block';
            const inp = document.getElementById('inp_partial_val_' + pid);
            let val = parseFloat(inp ? inp.value : 0) || (netShare / 2);
            if (val > netShare) val = netShare;
            if (val < 0) val = 0;
            withdrawn = val;
            reinvested = netShare - withdrawn;
            if (inputWithdrawn) inputWithdrawn.value = withdrawn.toFixed(2);
            if (accSel) {
                accSel.name = '';
                accSel.required = false;
            }
            if (accPartialSel) {
                accPartialSel.name = 'decisions[' + pid + '][account_id]';
                accPartialSel.required = true;
            }
            if (accSel && accPartialSel && accPartialSel.value) {
                accSel.value = accPartialSel.value;
            }
        } else {
            // reinvest
            const box = document.getElementById('box_reinvest_' + pid);
            if (box) box.style.display = 'block';
            withdrawn = 0;
            reinvested = netShare;
            if (inputWithdrawn) inputWithdrawn.value = '0';
            if (accSel) {
                accSel.name = 'decisions[' + pid + '][account_id]';
                accSel.required = false;
                accSel.value = '';
            }
            if (accPartialSel) {
                accPartialSel.name = '';
                accPartialSel.required = false;
                accPartialSel.value = '';
            }
        }

        updateNewCapDisplay(pid, openCap, reinvested, action);
    }

    /**
     * تحديث فوري عند إدخال قيمة السحب الجزئي
     */
    function onPartialInput(pid, openCap, netShare) {
        const inp = document.getElementById('inp_partial_val_' + pid);
        let val = parseFloat(inp ? inp.value : 0) || 0;
        if (val > netShare) val = netShare;
        if (val < 0) val = 0;

        const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
        if (inputWithdrawn) inputWithdrawn.value = val.toFixed(2);

        const rem = netShare - val;
        const badge = document.getElementById('partial_rollover_badge_' + pid);
        if (badge) {
            badge.innerHTML = 'المتبقي للتدوير في رأس المال: <strong class="text-success">' + Number(rem).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج</strong>';
        }

        updateNewCapDisplay(pid, openCap, rem, 'withdraw_partial');
    }

    function syncPartialAccount(pid) {
        const accPartialSel = document.getElementById('acc_partial_sel_' + pid);
        const accSel = document.getElementById('acc_sel_' + pid);
        if (accSel && accPartialSel) {
            accSel.value = accPartialSel.value;
        }
    }

    function setAllDecisions(action) {
        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const openCap = parseFloat(tr.getAttribute('data-opencap')) || 0;
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            if (netShare > 0) {
                setPartnerDecision(pid, action, openCap, netShare);
            }
        });
    }

    function updateNewCapDisplay(pid, openCap, reinvested, action) {
        const newCap = Math.max(0, openCap + reinvested);
        const capDisplay = document.getElementById('new_cap_display_' + pid);
        const capSub = document.getElementById('new_cap_sub_' + pid);

        if (capDisplay) {
            capDisplay.innerText = Number(newCap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (capSub) {
            if (action === 'reinvest') {
                capSub.className = 'd-block text-success';
                capSub.innerHTML = '<i class="fa fa-arrow-up me-1"></i> زيادة بإضافة كامل الربح (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            } else if (action === 'withdraw_all') {
                capSub.className = 'd-block text-muted';
                capSub.innerHTML = '<i class="fa fa-equals me-1"></i> ثبات رأس المال (سحب كامل الربح نقداً)';
            } else {
                capSub.className = 'd-block text-primary';
                capSub.innerHTML = '<i class="fa fa-arrow-up me-1"></i> زيادة بالمتبقي المدور (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            }
        }
    }

    document.getElementById('formExecuteClosing').addEventListener('submit', function (e) {
        let hasError = false;
        let errorMsg = '';

        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            if (netShare <= 0) return;

            const actionInp = document.getElementById('input_action_' + pid);
            const action = actionInp ? actionInp.value : 'reinvest';

            const partnerName = tr.querySelector('strong') ? tr.querySelector('strong').innerText.trim() : 'المحدد';

            if (action === 'withdraw_all') {
                const accSel = document.getElementById('acc_sel_' + pid);
                if (!accSel || !accSel.value) {
                    errorMsg = 'يرجى تحديد خزينة الصرف لصرف أرباح «' + partnerName + '» (سحب كامل).';
                    hasError = true;
                    if (accSel) accSel.focus();
                }
            } else if (action === 'withdraw_partial') {
                const accPartialSel = document.getElementById('acc_partial_sel_' + pid);
                const accSel = document.getElementById('acc_sel_' + pid);
                const selectedVal = (accPartialSel && accPartialSel.value) || (accSel && accSel.value);
                if (!selectedVal) {
                    errorMsg = 'يرجى تحديد خزينة الصرف لصرف حصة «' + partnerName + '» (سحب جزئي).';
                    hasError = true;
                    if (accPartialSel) accPartialSel.focus();
                }
            }
        });

        if (hasError) {
            e.preventDefault();
            alert(errorMsg);
            return false;
        }

        if (!confirm('تأكيد: هل راجعت كافة قرارات التوزيع وتريد اعتماد إقفال دورة الشهر رسمياً وترحيل الحسابات والخزائن؟')) {
            e.preventDefault();
            return false;
        }
    });
</script>

</body>
</html>
