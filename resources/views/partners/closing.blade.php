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
            height: 100%;
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
        .table-responsive {
            overflow-x: visible !important;
        }
        .table-formal {
            margin: 0;
            width: 100% !important;
            table-layout: auto;
            vertical-align: middle;
            font-size: 0.82rem;
        }
        .table-formal thead th {
            background: var(--slate-800);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 10px 6px;
            border: none;
            white-space: normal;
            line-height: 1.3;
        }
        .table-formal tbody td {
            vertical-align: middle;
            padding: 9px 6px;
            border-bottom: 1px solid var(--slate-200);
            font-variant-numeric: tabular-nums;
        }
        .btn-decision {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 6px;
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
                    <i class="fa fa-file-invoice-dollar text-primary"></i> إقفال الدورة الشهرية وتوزيع الأرباح وتحديث النسب
                </h4>
            </div>
            <div class="text-muted small">
                <i class="fa fa-shield-alt text-success me-1"></i> النسب تتحدث وتُحفظ في سجل الهيستوري فور اعتماد الإقفال
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
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-50 flex-wrap gap-2">
                <span class="fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa fa-calculator text-warning"></i> معادلة احتساب الربح القابل للتوزيع (صافي الربح = رأس مال الإقفال الحالي − صافي رأس مال البداية المرجعي)
                </span>
                <span class="badge bg-secondary font-monospace">
                    الفترة: {{ $preview['start_date'] }} إلى {{ $preview['end_date'] }}
                </span>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="formula-box py-3">
                        <div class="label fs-6 mb-1">1. صافي رأس مال البداية المرجعي للدورة</div>
                        <div class="val text-info fs-3">{{ number_format($preview['opening_capital'], 2) }} ج</div>
                        <small class="text-light opacity-75 d-block mt-1" style="font-size:0.74rem;">
                            @if(($preview['last_period_withdrawn'] ?? 0) > 0 || ($preview['partner_deposits_net'] ?? 0) != 0)
                                إقفال سابق ({{ number_format($preview['raw_last_closing_capital'], 2) }} ج)
                                @if(($preview['last_period_withdrawn'] ?? 0) > 0)
                                    − أرباح مصروفة ({{ number_format($preview['last_period_withdrawn'], 2) }} ج)
                                @endif
                                @if(($preview['partner_deposits_net'] ?? 0) != 0)
                                    {{ $preview['partner_deposits_net'] > 0 ? '+' : '−' }} صافي حركات رأس المال ({{ number_format(abs($preview['partner_deposits_net']), 2) }} ج)
                                @endif
                            @else
                                مطابق لإقفال بداية الدورة ({{ $preview['start_date'] }})
                            @endif
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="formula-box py-3">
                        <div class="label fs-6 mb-1">2. رأس مال الشركة الحالي (عند الإقفال)</div>
                        <div class="val text-light fs-3">{{ number_format($preview['closing_capital'], 2) }} ج</div>
                        <small class="text-light opacity-75 d-block mt-1" style="font-size:0.74rem;">
                            إجمالي الحصص المشاركة بالدورة: <strong>{{ number_format($preview['total_opening_shares'], 2) }} ج</strong>
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="formula-box py-3" style="background: rgba(4, 120, 87, 0.25); border-color: rgba(4, 120, 87, 0.6);">
                        <div class="label text-success fw-bold fs-6 mb-1">صافي ربح الدورة القابل للتوزيع (R)</div>
                        <div class="val {{ $preview['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }} fs-3">
                            {{ $preview['gross_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['gross_profit'], 2) }} ج
                        </div>
                        <small class="text-light opacity-75">الأرباح = (2) رأس مال الإقفال الحالي − (1) صافي رأس مال البداية</small>
                    </div>
                </div>
            </div>

            @if($preview['gross_profit'] < 0)
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mt-3 mb-0 py-2 px-3 small rounded">
                <i class="fa fa-exclamation-triangle me-1"></i> تنبيه: سجلت الدورة الحالية عجزاً/خسارة قدرها {{ number_format(abs($preview['gross_profit']), 2) }} ج. يتحمل كل شريك حصته من الخسارة حسب نسبته مع عدم احتساب أي نسب شغل لحساب الإدارة.
            </div>
            @endif
        </div>

        {{-- جدول توزيع الحصص وقرارات الشركاء ومقارنة النسب قبل وبعد التقفيل --}}
        <form action="{{ route('partners.closing.store') }}" method="POST" id="formExecuteClosing">
            @csrf
            <input type="hidden" name="start_date" value="{{ $preview['start_date'] }}">
            <input type="hidden" name="end_date" value="{{ $preview['end_date'] }}">
            <input type="hidden" name="closing_capital" value="{{ $preview['closing_capital'] }}">

            <div class="card-formal mb-4 overflow-hidden">
                <div class="bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 border-bottom">
                    <div>
                        <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa fa-users text-primary"></i> بيان توزيع الأرباح ومقارنة النسب (قبل التقفيل ← بعد التقفيل)
                        </h5>
                        <small class="text-muted">تتحدث «النسبة بعد التقفيل (بقت كام %)» تلقائياً بمجرد تغيير قرار أي شريك أو حساب الإدارة بين التدوير أو السحب.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-success fw-bold" onclick="setAllDecisions('reinvest')">
                                <i class="fa fa-sync-alt me-1"></i> تعيين الكل: تدوير بالأرصدة
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

                <div>
                    <table class="table table-formal table-hover m-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 13%;">الشريك / الحساب</th>
                                <th style="width: 11%;">الرصيد قبل التقفيل</th>
                                <th class="text-center" style="width: 8%; background:#334155;">النسبة كانت كام</th>
                                <th style="width: 10%;">ربح الحصة (g)</th>
                                <th class="text-center" style="width: 11%;">نسبة وأرباح الشغل</th>
                                <th style="width: 10%;">صافي الربح (n)</th>
                                <th style="width: 18%;">قرار التصرف والصرف</th>
                                <th style="width: 11%;">الرصيد بعد التقفيل</th>
                                <th class="text-center" style="width: 8%; background:#1e40af;">النسبة بقت كام</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- سطر حساب الإدارة --}}
                            @php
                                $ahmedOpenCap   = $preview['ahmed']['opening_capital'];
                                $ahmedOpenPct   = $preview['ahmed']['capital_share_pct'];
                                $ahmedNetProfit = $preview['ahmed']['total_profit'];
                                $ahmedCloseCap  = $preview['ahmed']['closing_capital'];
                                $ahmedClosePct  = $preview['ahmed']['closing_share_pct'];
                                $ahmedPctDiff   = $preview['ahmed']['share_pct_diff'];
                            @endphp
                            <tr id="row_partner_ahmed"
                                data-pid="ahmed"
                                data-opencap="{{ $ahmedOpenCap }}"
                                data-openpct="{{ $ahmedOpenPct }}"
                                data-net="{{ $ahmedNetProfit }}"
                                data-newcap="{{ $ahmedCloseCap }}"
                                style="background-color: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                                <td>
                                    <strong class="text-dark d-block">{{ $headName }}</strong>
                                    <span class="badge bg-dark text-white fw-bold" style="font-size:0.65rem;">حساب الإدارة</span>
                                </td>
                                <td class="fw-bold">{{ number_format($ahmedOpenCap, 2) }} ج</td>
                                <td class="text-center">
                                    <span class="badge-formal bg-light text-secondary border">
                                        {{ number_format($ahmedOpenPct, 2) }}%
                                    </span>
                                </td>
                                <td class="fw-bold {{ $preview['ahmed']['own_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $preview['ahmed']['own_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['ahmed']['own_profit'], 2) }} ج
                                    <small class="d-block text-muted" style="font-size:0.68rem;">ربح الرصيد المدور</small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-primary">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</div>
                                    <small class="text-muted d-block" style="font-size:0.68rem;">الأرباح من نسب الشغل</small>
                                </td>
                                <td class="fw-bold {{ $ahmedNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $ahmedNetProfit >= 0 ? '+' : '' }}{{ number_format($ahmedNetProfit, 2) }} ج
                                    <small class="d-block text-muted" style="font-size:0.68rem;">إجمالي ربح الإدارة</small>
                                </td>

                                {{-- عمود القرار والصرف المدمج لحساب الإدارة --}}
                                <td>
                                    @if($ahmedNetProfit > 0)
                                        <div class="btn-group btn-group-sm w-100 mb-1" role="group">
                                            <button type="button" class="btn btn-outline-success btn-decision btn-decision-reinvest btn-decision-ahmed active" id="btn_opt_reinvest_ahmed" onclick="setPartnerDecision('ahmed', 'reinvest', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                تدوير
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-decision btn-decision-withdraw-all btn-decision-ahmed" id="btn_opt_withdraw_all_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_all', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                سحب كامل
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-decision btn-decision-withdraw-partial btn-decision-ahmed" id="btn_opt_withdraw_partial_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_partial', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                سحب جزئي
                                            </button>
                                        </div>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" id="input_action_ahmed" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" id="input_withdrawn_ahmed" value="0">

                                        <div id="box_reinvest_ahmed" class="payout-box-ahmed px-2 py-1 rounded bg-light text-success border text-center fw-bold" style="font-size:0.72rem;">
                                            <i class="fa fa-check-circle me-1"></i> تدوير بالحساب للدورة القادمة
                                        </div>

                                        <div id="box_withdraw_all_ahmed" class="payout-box-ahmed mt-1" style="display:none;">
                                            <select name="decisions[ahmed][account_id]" id="acc_sel_ahmed" class="form-select form-select-sm fw-bold border-danger treasury-select" style="font-size:0.75rem;" data-pid="ahmed" data-name="{{ $headName }}">
                                                <option value="">-- اختر خزينة الصرف ({{ number_format($ahmedNetProfit, 2) }} ج) --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="box_withdraw_partial_ahmed" class="payout-box-ahmed mt-1" style="display:none;">
                                            <div class="input-group input-group-sm mb-1">
                                                <input type="number" step="0.01" min="1" max="{{ $ahmedNetProfit }}" id="inp_partial_val_ahmed" class="form-control fw-bold text-danger" value="{{ round($ahmedNetProfit / 2, 2) }}" placeholder="المبلغ المسحوب" oninput="onPartialInput('ahmed', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                <span class="input-group-text bg-white fw-bold">ج</span>
                                            </div>
                                            <div class="text-muted fw-bold mb-1" style="font-size:0.7rem;" id="partial_rollover_badge_ahmed">
                                                المدور: <strong class="text-success">{{ number_format($ahmedNetProfit - round($ahmedNetProfit / 2, 2), 2) }} ج</strong>
                                            </div>
                                            <select id="acc_partial_sel_ahmed" class="form-select form-select-sm fw-bold border-secondary treasury-select-partial" style="font-size:0.75rem;" data-pid="ahmed" data-name="{{ $headName }}" onchange="syncPartialAccount('ahmed')">
                                                <option value="">-- اختر خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($ahmedNetProfit < 0)
                                        <span class="badge-formal bg-danger-subtle text-danger border border-danger-subtle p-1 text-wrap d-block text-center">
                                            خصم العجز ({{ number_format($ahmedNetProfit, 2) }} ج)
                                        </span>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" value="0">
                                    @else
                                        <span class="badge-formal bg-light text-muted border p-1 d-block text-center">
                                            لا يوجد ربح للتوزيع (0.00 ج)
                                        </span>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- الرصيد الجديد بعد التقفيل لحساب الإدارة --}}
                                <td class="fw-bold" id="cell_cap_ahmed">
                                    <div class="text-dark fw-bold" id="new_cap_display_ahmed">
                                        {{ number_format($ahmedCloseCap, 2) }} ج
                                    </div>
                                    <small id="new_cap_sub_ahmed" class="d-block {{ $ahmedNetProfit > 0 ? 'text-success' : ($ahmedNetProfit < 0 ? 'text-danger' : 'text-muted') }}" style="font-size:0.7rem;">
                                        @if($ahmedNetProfit > 0)
                                            <i class="fa fa-arrow-up"></i> +{{ number_format($ahmedNetProfit, 2) }} ج
                                        @elseif($ahmedNetProfit < 0)
                                            <i class="fa fa-arrow-down"></i> {{ number_format($ahmedNetProfit, 2) }} ج
                                        @else
                                            ثابت (0.00 ج)
                                        @endif
                                    </small>
                                </td>

                                {{-- النسبة الجديدة بعد التقفيل (بقت كام) لحساب الإدارة --}}
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold" style="font-size:0.8rem;" id="new_pct_display_ahmed">
                                        {{ number_format($ahmedClosePct, 2) }}%
                                    </span>
                                    <div id="new_pct_diff_ahmed" class="mt-1" style="font-size:0.68rem;">
                                        @if($ahmedPctDiff > 0.0001)
                                            <span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> +{{ number_format($ahmedPctDiff, 2) }}%</span>
                                        @elseif($ahmedPctDiff < -0.0001)
                                            <span class="text-danger fw-bold"><i class="fa fa-arrow-down"></i> {{ number_format($ahmedPctDiff, 2) }}%</span>
                                        @else
                                            <span class="text-muted">ثابتة</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- أسطر باقي الشركاء --}}
                            @forelse($preview['partner_lines'] as $line)
                            @php $pid = $line['partner_id']; @endphp
                            <tr id="row_partner_{{ $pid }}"
                                data-pid="{{ $pid }}"
                                data-opencap="{{ $line['opening_capital'] }}"
                                data-openpct="{{ $line['capital_share_pct'] }}"
                                data-net="{{ $line['net_share'] }}"
                                data-newcap="{{ $line['closing_capital'] }}">
                                <td>
                                    <strong class="text-dark d-block">{{ $line['name'] }}</strong>
                                    <small class="text-muted" style="font-size:0.7rem;">{{ $line['phone'] ?: '—' }}</small>
                                </td>
                                <td class="fw-bold">{{ number_format($line['opening_capital'], 2) }} ج</td>
                                <td class="text-center">
                                    <span class="badge-formal bg-light text-secondary border">
                                        {{ number_format($line['capital_share_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="fw-bold {{ $line['gross_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['gross_share'] >= 0 ? '+' : '' }}{{ number_format($line['gross_share'], 2) }} ج
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark">+{{ number_format($line['ahmed_commission'], 2) }} ج</div>
                                    <span class="badge bg-light text-secondary border" style="font-size:0.65rem;">نسبة الشغل {{ number_format($line['ahmed_ratio_pct'], 0) }}%</span>
                                </td>
                                <td class="fw-bold {{ $line['net_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['net_share'] >= 0 ? '+' : '' }}{{ number_format($line['net_share'], 2) }} ج
                                </td>

                                {{-- عمود القرار والصرف المدمج للشريك --}}
                                <td>
                                    @if($line['net_share'] > 0)
                                        <div class="btn-group btn-group-sm w-100 mb-1" role="group">
                                            <button type="button" class="btn btn-outline-success btn-decision btn-decision-reinvest btn-decision-{{ $pid }} active" id="btn_opt_reinvest_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'reinvest', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                تدوير
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-decision btn-decision-withdraw-all btn-decision-{{ $pid }}" id="btn_opt_withdraw_all_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_all', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                سحب كامل
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-decision btn-decision-withdraw-partial btn-decision-{{ $pid }}" id="btn_opt_withdraw_partial_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_partial', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                سحب جزئي
                                            </button>
                                        </div>
                                        <input type="hidden" name="decisions[{{ $pid }}][payout_action]" id="input_action_{{ $pid }}" value="reinvest">
                                        <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" id="input_withdrawn_{{ $pid }}" value="0">

                                        <div id="box_reinvest_{{ $pid }}" class="payout-box-{{ $pid }} px-2 py-1 rounded bg-light text-success border text-center fw-bold" style="font-size:0.72rem;">
                                            <i class="fa fa-check-circle me-1"></i> تدوير بالرصيد (+{{ number_format($line['net_share'], 2) }} ج)
                                        </div>

                                        <div id="box_withdraw_all_{{ $pid }}" class="payout-box-{{ $pid }} mt-1" style="display:none;">
                                            <select name="decisions[{{ $pid }}][account_id]" id="acc_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-danger treasury-select" style="font-size:0.75rem;" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}">
                                                <option value="">-- اختر خزينة الصرف ({{ number_format($line['net_share'], 2) }} ج) --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="box_withdraw_partial_{{ $pid }}" class="payout-box-{{ $pid }} mt-1" style="display:none;">
                                            <div class="input-group input-group-sm mb-1">
                                                <input type="number" step="0.01" min="1" max="{{ $line['net_share'] }}" id="inp_partial_val_{{ $pid }}" class="form-control fw-bold text-danger" value="{{ round($line['net_share'] / 2, 2) }}" placeholder="المبلغ المسحوب" oninput="onPartialInput({{ $pid }}, {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                <span class="input-group-text bg-white fw-bold">ج</span>
                                            </div>
                                            <div class="text-muted fw-bold mb-1" style="font-size:0.7rem;" id="partial_rollover_badge_{{ $pid }}">
                                                المدور: <strong class="text-success">{{ number_format($line['net_share'] - round($line['net_share'] / 2, 2), 2) }} ج</strong>
                                            </div>
                                            <select id="acc_partial_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-secondary treasury-select-partial" style="font-size:0.75rem;" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}" onchange="syncPartialAccount({{ $pid }})">
                                                <option value="">-- اختر خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($line['net_share'] < 0)
                                        <span class="badge-formal bg-danger-subtle text-danger border border-danger-subtle p-1 text-wrap d-block text-center">
                                            خصم الخسارة ({{ number_format($line['net_share'], 2) }} ج)
                                        </span>
                                        <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                    @else
                                        <span class="badge-formal bg-light text-muted border p-1 d-block text-center">
                                            لا يوجد ربح للتوزيع (0.00 ج)
                                        </span>
                                        <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- الرصيد الجديد المعتمد --}}
                                <td class="fw-bold" id="cell_cap_{{ $pid }}">
                                    <div class="text-dark fw-bold" id="new_cap_display_{{ $pid }}">
                                        {{ number_format($line['closing_capital'], 2) }} ج
                                    </div>
                                    <small id="new_cap_sub_{{ $pid }}" class="d-block {{ $line['net_share'] > 0 ? 'text-success' : ($line['net_share'] < 0 ? 'text-danger' : 'text-muted') }}" style="font-size:0.7rem;">
                                        @if($line['net_share'] > 0)
                                            <i class="fa fa-arrow-up"></i> +{{ number_format($line['net_share'], 2) }} ج
                                        @elseif($line['net_share'] < 0)
                                            <i class="fa fa-arrow-down"></i> {{ number_format($line['net_share'], 2) }} ج
                                        @else
                                            ثابت (0.00 ج)
                                        @endif
                                    </small>
                                </td>

                                {{-- النسبة الجديدة بعد التقفيل (بقت كام) --}}
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold" style="font-size:0.8rem;" id="new_pct_display_{{ $pid }}">
                                        {{ number_format($line['closing_share_pct'], 2) }}%
                                    </span>
                                    <div id="new_pct_diff_{{ $pid }}" class="mt-1" style="font-size:0.68rem;">
                                        @if($line['share_pct_diff'] > 0.0001)
                                            <span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> +{{ number_format($line['share_pct_diff'], 2) }}%</span>
                                        @elseif($line['share_pct_diff'] < -0.0001)
                                            <span class="text-danger fw-bold"><i class="fa fa-arrow-down"></i> {{ number_format($line['share_pct_diff'], 2) }}%</span>
                                        @else
                                            <span class="text-muted">ثابتة</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">لا يوجد شركاء نشطين لتوزيع الأرباح عليهم في هذه الفترة.</td>
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
                            <input type="text" name="notes" class="form-control form-control-sm mt-1" placeholder="مثال: تم إقفال دورة الشهر وتحديث نسب الشركاء وحساب الإدارة...">
                        </div>
                        <div class="col-md-4 text-start">
                            <button type="submit" class="btn btn-dark fw-bold px-4 py-2 w-100 shadow-sm">
                                <i class="fa fa-check-circle me-1"></i> اعتماد التقفيل وتحديث النسب والهيستوري
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- ملخص أرباح حساب الإدارة للدورة --}}
        <div class="card-formal p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-light text-primary rounded border fs-4">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">ملخص حساب الإدارة ({{ $headName }})</h6>
                            <small class="text-muted">الأرباح من نسب الشغل + ربح الرصيد المدور</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">ربح الرصيد المدور في حساب الإدارة</small>
                    <strong class="fs-5 text-dark font-monospace">{{ number_format($preview['ahmed']['own_profit'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">نسبة الحساب قبل التقفيل: {{ number_format($preview['ahmed']['capital_share_pct'], 2) }}%</div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">الأرباح من نسب الشغل مع الشركاء</small>
                    <strong class="fs-5 text-primary font-monospace">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">مجموع نسب الإدارة من أرباح الشركاء</div>
                </div>

                <div class="col-md-3 text-center border-start bg-light py-3 rounded">
                    <small class="text-dark fw-bold d-block mb-1">إجمالي الدخل المضاف لحساب الإدارة</small>
                    <strong class="fs-4 text-success font-monospace">{{ number_format($preview['ahmed']['total_profit'], 2) }} ج</strong>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /**
     * تغيير قرار الشريك أو حساب الإدارة للدورة الحالية
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
            badge.innerHTML = 'المتبقي للتدوير بالرصيد: <strong class="text-success">' + Number(rem).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج</strong>';
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
        const tr = document.getElementById('row_partner_' + pid);
        if (tr) {
            tr.setAttribute('data-newcap', newCap.toFixed(2));
        }

        const capDisplay = document.getElementById('new_cap_display_' + pid);
        const capSub = document.getElementById('new_cap_sub_' + pid);

        if (capDisplay) {
            capDisplay.innerText = Number(newCap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (capSub) {
            if (action === 'reinvest') {
                capSub.className = 'd-block text-success';
                capSub.innerHTML = '<i class="fa fa-arrow-up me-1"></i> زيادة (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            } else if (action === 'withdraw_all') {
                capSub.className = 'd-block text-muted';
                capSub.innerHTML = '<i class="fa fa-equals me-1"></i> ثبات الرصيد (سحب كامل الربح)';
            } else {
                capSub.className = 'd-block text-primary';
                capSub.innerHTML = '<i class="fa fa-arrow-up me-1"></i> زيادة بالمدور (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            }
        }

        // إعادة حساب جميع النسب بعد التقفيل (بقت كام %) لحظياً بناءً على إجمالي الأرصدة الجديدة
        recalcAllClosingSharePcts();
    }

    function recalcAllClosingSharePcts() {
        let totalNewCap = 0;
        const rows = document.querySelectorAll('tr[data-pid]');
        rows.forEach(tr => {
            totalNewCap += parseFloat(tr.getAttribute('data-newcap')) || 0;
        });

        rows.forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const newCap = parseFloat(tr.getAttribute('data-newcap')) || 0;
            const openPct = parseFloat(tr.getAttribute('data-openpct')) || 0;
            const newPct = totalNewCap > 0 ? (newCap / totalNewCap) * 100 : 0;
            const diff = newPct - openPct;

            const pctEl = document.getElementById('new_pct_display_' + pid);
            const diffEl = document.getElementById('new_pct_diff_' + pid);

            if (pctEl) {
                pctEl.innerText = newPct.toFixed(2) + '%';
            }
            if (diffEl) {
                if (diff > 0.005) {
                    diffEl.innerHTML = '<span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> +' + diff.toFixed(2) + '%</span>';
                } else if (diff < -0.005) {
                    diffEl.innerHTML = '<span class="text-danger fw-bold"><i class="fa fa-arrow-down"></i> ' + diff.toFixed(2) + '%</span>';
                } else {
                    diffEl.innerHTML = '<span class="text-muted">ثابتة</span>';
                }
            }
        });
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

        if (!confirm('تأكيد: هل راجعت كافة قرارات التوزيع وتريد اعتماد إقفال الدورة وتحديث النسب والهيستوري رسمياً؟')) {
            e.preventDefault();
            return false;
        }
    });
</script>

</body>
</html>
