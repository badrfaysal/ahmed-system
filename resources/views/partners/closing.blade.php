<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إقفال الدورة الشهرية وتوزيع الأرباح - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'teal'])
    @include('partners._calm_theme')

    <style>
        .cls-period-bar {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(13, 31, 53, 0.08);
            border-radius: var(--p-radius-lg);
            padding: 16px 20px;
            box-shadow: var(--p-shadow-card);
            margin-bottom: 20px;
        }
        .cls-meta-ico {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.92rem;
            flex-shrink: 0;
        }
        .cls-meta-ico.is-lock { background: #e5ecf4; color: var(--c-navy); }
        .cls-meta-ico.is-date { background: #daf2e5; color: #1f6f47; }

        .cls-formula-val {
            font-size: 1.52rem;
            font-weight: 700;
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
            margin: 4px 0;
        }
        .cls-formula-step {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .cls-dec-group {
            display: flex;
            gap: 4px;
            background: #f1f5f8;
            padding: 3px;
            border-radius: 999px;
            border: 1px solid #e0e8f0;
            margin-bottom: 6px;
        }
        .btn-decision {
            flex: 1;
            border: none !important;
            background: transparent;
            color: var(--c-text-muted);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 6px;
            border-radius: 999px !important;
            transition: all 0.16s ease;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-decision:hover {
            color: var(--c-navy);
            background: rgba(255, 255, 255, 0.7);
        }
        .btn-decision.active {
            box-shadow: 0 2px 6px rgba(13, 31, 53, 0.14);
        }
        .btn-decision-reinvest.active {
            background: linear-gradient(135deg, #257a4e, #319461) !important;
            color: #ffffff !important;
        }
        .btn-decision-withdraw-all.active {
            background: linear-gradient(135deg, #1a5494, #256eb8) !important;
            color: #ffffff !important;
        }
        .btn-decision-withdraw-partial.active {
            background: linear-gradient(135deg, #9a6512, #bd7f1c) !important;
            color: #ffffff !important;
        }
        .cls-reinvest-pill {
            background: #eef8f2;
            color: #1f6f47;
            border: 1px solid rgba(45, 134, 89, 0.18);
            border-radius: 10px;
            padding: 5px 8px;
            font-size: 0.72rem;
            font-weight: 600;
            text-align: center;
        }
        .cls-summary-strip {
            padding: 18px 24px;
            background: linear-gradient(180deg, #f5f9fb 0%, #f1f6f9 100%);
            border-top: 1px solid var(--p-hairline);
        }
        .cls-summary-tile {
            border-radius: var(--p-radius-md);
            padding: 15px 18px;
            border: 1px solid #dfe8f0;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            height: 100%;
            transition: all 0.2s ease;
        }
        .cls-summary-tile.is-emerald {
            background: linear-gradient(145deg, #ecf8f1 0%, #ffffff 78%);
            border-color: rgba(45, 134, 89, 0.22);
        }
        .cls-summary-tile.is-teal {
            background: linear-gradient(145deg, #eaf6f6 0%, #ffffff 78%);
            border-color: rgba(13, 115, 115, 0.22);
        }
        .cls-summary-tile.is-rose {
            background: linear-gradient(145deg, #fdf0f0 0%, #ffffff 78%);
            border-color: rgba(185, 28, 28, 0.24);
        }
        .cls-annual-box {
            border-radius: var(--p-radius-md);
            padding: 14px 18px;
            border: 1px solid #d9e6f0;
            background: #ffffff;
            margin-bottom: 16px;
        }
        .cls-annual-box.is-dec {
            background: #fdf7e7;
            border-color: #ecd194;
        }
        .cls-mgmt-tile {
            background: #f5f9fb;
            border: 1px solid #e2ebf2;
            border-radius: var(--p-radius-md);
            padding: 14px 16px;
            text-align: center;
            height: 100%;
        }
        .cls-mgmt-tile.is-total {
            background: linear-gradient(145deg, #eaf7f0 0%, #f4fbf7 100%);
            border-color: rgba(45, 134, 89, 0.24);
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 6mm 12mm 6mm;
            }
            *, *::before, *::after {
                overflow: visible !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
                box-shadow: none !important;
            }
            ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'IBM Plex Sans Arabic', 'Cairo', sans-serif !important;
                font-size: 7.5pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop,
            .no-print, th.no-print, td.no-print, button, .btn, .prt-pill-btn, .prt-back-link,
            .prt-panel-foot, .modal, button[type="submit"], #liveWithdrawalSummaryCard, .closing-actions-bar {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
            }
            .container-fluid { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            .prt-panel {
                border: 1px solid #cbd5e1 !important;
                border-radius: 4px !important;
                background: #ffffff !important;
                margin-bottom: 8px !important;
            }
            .prt-table-wrap { overflow: visible !important; }
            .prt-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 7.2pt !important;
            }
            .prt-table thead { display: table-header-group !important; }
            .prt-table thead th {
                background-color: #0d1f35 !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
                padding: 4px 2px !important;
                font-size: 6.8pt !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .prt-table tbody td {
                border: 1px solid #94a3b8 !important;
                padding: 3px 2px !important;
                font-size: 7.2pt !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .prt-table tr { page-break-inside: avoid !important; }
            input.form-control, select.form-select {
                border: none !important;
                background: transparent !important;
                box-shadow: none !important;
                font-weight: bold !important;
                padding: 0 !important;
                font-size: 7.2pt !important;
            }
        }
    </style>
</head>
<body>

@include('sidebar')

@php
    $profitPct = ($preview['opening_capital'] > 0)
        ? round(($preview['gross_profit'] / $preview['opening_capital']) * 100, 2)
        : 0.0;
    $isProfit = $preview['gross_profit'] >= 0;
@endphp

<div class="main-content">
    <div class="container-fluid px-0">

        {{-- الترويسة الرئيسية --}}
        <div class="prt-header no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="prt-back-link">
                    <i class="fa fa-arrow-right fa-xs"></i>
                    <span>العودة إلى سجل الشركاء</span>
                </a>
                <div class="prt-title-row">
                    <span class="prt-title-badge"><i class="fa fa-file-invoice-dollar"></i></span>
                    <h1 class="prt-title">إقفال الدورة الشهرية وتوزيع الأرباح وتحديث النسب</h1>
                </div>
            </div>
            <div class="prt-actions align-self-end">
                <span class="prt-chip is-success py-2 px-3">
                    <i class="fa fa-shield-alt fa-xs"></i>
                    <span>النسب تتحدث وتُحفظ في السجل فور اعتماد الإقفال</span>
                </span>
            </div>
        </div>

        {{-- شريط معلومات الفترة المقفلة تلقائياً --}}
        <div class="cls-period-bar no-print">
            <div class="row g-3 align-items-center">
                <div class="col-lg-5 col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <span class="cls-meta-ico is-lock"><i class="fa fa-lock"></i></span>
                        <div>
                            <div class="prt-meta-line m-0">بداية الدورة الحالية (مقفول على آخر إقفال معتمد)</div>
                            <div class="fw-semibold mt-1" style="color: var(--c-navy); font-size: 0.9rem;">
                                @if(!empty($lastPeriod))
                                    {{ $lastPeriod->period_name }} — بتاريخ:
                                    <span class="prt-chip is-info">{{ $preview['start_date'] }}</span>
                                @else
                                    بداية الدورة التأسيسية:
                                    <span class="prt-chip is-info">{{ $preview['start_date'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-3">
                        <span class="cls-meta-ico is-date"><i class="fa fa-calendar-check"></i></span>
                        <div>
                            <div class="prt-meta-line m-0">تاريخ الإقفال الحالي (رأس المال اللحظي الآن)</div>
                            <div class="fw-semibold mt-1" style="color: var(--c-navy); font-variant-numeric: tabular-nums;">
                                {{ $preview['end_date'] }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12 text-lg-start">
                    <a href="{{ route('partners.closing') }}" class="prt-pill-btn is-primary w-100">
                        <i class="fa fa-sync-alt fa-xs"></i>
                        <span>تحديث الأرقام اللحظية</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- لوحة معادلة احتساب الربح والنسبة (هادئة وملونة بتناغم) --}}
        <div class="prt-panel">
            <div class="prt-panel-head">
                <h5 class="prt-panel-title">
                    <span class="prt-panel-title-ico"><i class="fa fa-calculator"></i></span>
                    <span>معادلة احتساب الربح والنسبة (صافي الربح = رأس المال الحالي − رأس المال السابق)</span>
                </h5>
                <span class="prt-chip is-accent">
                    <i class="fa fa-calendar-alt fa-xs"></i>
                    <span>الفترة: {{ $preview['start_date'] }} إلى {{ $preview['end_date'] }}</span>
                </span>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    {{-- 1. رأس المال السابق --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card is-teal">
                            <div>
                                <div class="prt-kpi-top">
                                    <span class="cls-formula-step prt-chip is-accent"><i class="fa fa-history fa-xs"></i> 1. رأس المال السابق</span>
                                    <span class="prt-kpi-ico"><i class="fa fa-vault"></i></span>
                                </div>
                                <div class="kpi-lbl mt-2">صافي رأس مال البداية المرجعي</div>
                                <div class="cls-formula-val" style="color: var(--c-accent);">
                                    <span dir="ltr">{{ number_format($preview['opening_capital'], 2) }}</span><span class="kpi-cur">ج</span>
                                </div>
                            </div>
                            <div class="kpi-sub">
                                @if(($preview['last_period_withdrawn'] ?? 0) > 0 || ($preview['partner_deposits_net'] ?? 0) != 0)
                                    إقفال سابق ({{ number_format($preview['raw_last_closing_capital'], 2) }} ج)
                                    @if(($preview['last_period_withdrawn'] ?? 0) > 0)
                                        − أرباح مصروفة ({{ number_format($preview['last_period_withdrawn'], 2) }} ج)
                                    @endif
                                    @if(($preview['partner_deposits_net'] ?? 0) != 0)
                                        {{ $preview['partner_deposits_net'] > 0 ? '+' : '−' }} حركات رأس المال ({{ number_format(abs($preview['partner_deposits_net']), 2) }} ج)
                                    @endif
                                @else
                                    مطابق لإقفال بداية الدورة ({{ $preview['start_date'] }})
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 2. رأس المال الحالي --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card is-blue">
                            <div>
                                <div class="prt-kpi-top">
                                    <span class="cls-formula-step prt-chip is-info"><i class="fa fa-building fa-xs"></i> 2. رأس المال الحالي</span>
                                    <span class="prt-kpi-ico"><i class="fa fa-coins"></i></span>
                                </div>
                                <div class="kpi-lbl mt-2">رأس مال الشركة عند الإقفال</div>
                                <div class="cls-formula-val" style="color: var(--c-info);">
                                    <span dir="ltr">{{ number_format($preview['closing_capital'], 2) }}</span><span class="kpi-cur">ج</span>
                                </div>
                            </div>
                            <div class="kpi-sub">
                                إجمالي الحصص المشاركة بالدورة: <strong style="color: var(--c-navy);">{{ number_format($preview['total_opening_shares'], 2) }} ج</strong>
                            </div>
                        </div>
                    </div>

                    {{-- 3. الفارق (صافي الربح) --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card {{ $isProfit ? 'is-emerald' : 'is-rose' }}">
                            <div>
                                <div class="prt-kpi-top">
                                    <span class="cls-formula-step prt-chip {{ $isProfit ? 'is-success' : 'is-danger' }}">
                                        <i class="fa fa-scale-balanced fa-xs"></i> 3. الفارق (صافي الربح)
                                    </span>
                                    <span class="prt-kpi-ico"><i class="fa {{ $isProfit ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i></span>
                                </div>
                                <div class="kpi-lbl mt-2">صافي ربح الدورة القابل للتوزيع</div>
                                <div class="cls-formula-val" style="color: {{ $isProfit ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    <span dir="ltr">{{ $isProfit ? '+' : '' }}{{ number_format($preview['gross_profit'], 2) }}</span><span class="kpi-cur">ج</span>
                                </div>
                            </div>
                            <div class="kpi-sub">الفارق = الحالي (2) − السابق (1)</div>
                        </div>
                    </div>

                    {{-- 4. النسبة كام --}}
                    <div class="col-xl-3 col-md-6">
                        <div class="prt-kpi-card {{ $profitPct >= 0 ? 'is-amber' : 'is-rose' }}">
                            <div>
                                <div class="prt-kpi-top">
                                    <span class="cls-formula-step prt-chip {{ $profitPct >= 0 ? 'is-warning' : 'is-danger' }}">
                                        <i class="fa fa-percent fa-xs"></i> 4. النسبة كام %
                                    </span>
                                    <span class="prt-kpi-ico"><i class="fa fa-chart-line"></i></span>
                                </div>
                                <div class="kpi-lbl mt-2">نسبة الربح / النمو للدورة</div>
                                <div class="cls-formula-val" style="color: {{ $profitPct >= 0 ? 'var(--c-warning)' : 'var(--c-danger)' }};">
                                    <span dir="ltr">{{ $profitPct >= 0 ? '+' : '' }}{{ number_format($profitPct, 2) }}%</span>
                                </div>
                            </div>
                            <div class="kpi-sub">معدل العائد: (الفارق ÷ رأس المال السابق)</div>
                        </div>
                    </div>
                </div>

                @if($preview['gross_profit'] < 0)
                    <div class="alert-pro danger mt-3 mb-0" style="font-size: 0.83rem;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span>تنبيه: سجلت الدورة الحالية عجزاً/خسارة قدرها <strong>{{ number_format(abs($preview['gross_profit']), 2) }} ج</strong>. يتحمل كل شريك حصته من الخسارة حسب نسبته مع عدم احتساب أي نسب شغل لحساب الإدارة.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- جدول توزيع الحصص وقرارات الشركاء ومقارنة النسب قبل وبعد التقفيل --}}
        <form action="{{ route('partners.closing.store') }}" method="POST" id="formExecuteClosing">
            @csrf
            <input type="hidden" name="start_date" value="{{ $preview['start_date'] }}">
            <input type="hidden" name="end_date" value="{{ $preview['end_date'] }}">
            <input type="hidden" name="closing_capital" value="{{ $preview['closing_capital'] }}">

            <div class="prt-panel">
                <div class="prt-panel-head">
                    <div>
                        <h5 class="prt-panel-title">
                            <span class="prt-panel-title-ico"><i class="fa fa-users"></i></span>
                            <span>بيان توزيع الأرباح ومقارنة النسب (قبل التقفيل ← بعد التقفيل)</span>
                        </h5>
                        <p class="prt-panel-sub">تتحدث «النسبة بعد التقفيل» تلقائياً بمجرد تغيير قرار أي شريك أو حساب الإدارة بين التدوير أو السحب.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap no-print">
                        <button type="button" class="prt-pill-btn is-emerald is-sm" onclick="setAllDecisions('reinvest')">
                            <i class="fa fa-sync-alt fa-xs"></i> تعيين الكل: تدوير
                        </button>
                        <button type="button" class="prt-pill-btn is-blue is-sm" onclick="setAllDecisions('withdraw_all')">
                            <i class="fa fa-money-bill-wave fa-xs"></i> تعيين الكل: سحب كامل
                        </button>
                        <div class="d-flex align-items-center gap-2">
                            <label class="small fw-semibold text-muted m-0 text-nowrap">اسم الدورة:</label>
                            <input type="text" name="period_name" class="form-pro-control py-1 px-3" style="width: 235px; font-size: 0.83rem; font-weight: 600;" value="إقفال دورة شهر {{ \Carbon\Carbon::parse($preview['end_date'])->translatedFormat('F Y') }}">
                        </div>
                    </div>
                </div>

                <div class="prt-table-wrap">
                    <table class="prt-table">
                        <thead>
                            <tr>
                                <th style="min-width: 145px;">الشريك / الحساب</th>
                                <th>الرصيد قبل التقفيل</th>
                                <th class="text-center th-soft-before">النسبة كانت كام</th>
                                <th>ربح الحصة (g)</th>
                                <th class="text-center">نسبة وأرباح الشغل</th>
                                <th>صافي الربح (n)</th>
                                <th style="min-width: 215px;">قرار التصرف والصرف</th>
                                <th>الرصيد بعد التقفيل</th>
                                <th class="text-center th-soft-after">النسبة بقت كام</th>
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
                                class="prt-row-head"
                                data-pid="ahmed"
                                data-opencap="{{ $ahmedOpenCap }}"
                                data-openpct="{{ $ahmedOpenPct }}"
                                data-net="{{ $ahmedNetProfit }}"
                                data-newcap="{{ $ahmedCloseCap }}">
                                <td>
                                    <strong class="d-block" style="color: var(--c-navy);">{{ $headName }}</strong>
                                    <span class="prt-chip is-head mt-1">حساب الإدارة</span>
                                </td>
                                <td class="prt-num-main">{{ number_format($ahmedOpenCap, 2) }} ج</td>
                                <td class="text-center">
                                    <span class="prt-chip is-neutral">{{ number_format($ahmedOpenPct, 2) }}%</span>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="color: {{ $preview['ahmed']['own_profit'] >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        <span dir="ltr">{{ $preview['ahmed']['own_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['ahmed']['own_profit'], 2) }}</span> ج
                                    </div>
                                    <div class="prt-num-sub">ربح الرصيد المدور</div>
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold" style="color: var(--c-info);">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</div>
                                    <div class="prt-num-sub">الأرباح من نسب الشغل</div>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="color: {{ $ahmedNetProfit >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        <span dir="ltr">{{ $ahmedNetProfit >= 0 ? '+' : '' }}{{ number_format($ahmedNetProfit, 2) }}</span> ج
                                    </div>
                                    <div class="prt-num-sub">إجمالي ربح الإدارة</div>
                                </td>

                                {{-- عمود القرار والصرف المدمج لحساب الإدارة --}}
                                <td>
                                    @if($ahmedNetProfit > 0)
                                        <div class="cls-dec-group" role="group">
                                            <button type="button" class="btn-decision btn-decision-reinvest btn-decision-ahmed active" id="btn_opt_reinvest_ahmed" onclick="setPartnerDecision('ahmed', 'reinvest', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                تدوير
                                            </button>
                                            <button type="button" class="btn-decision btn-decision-withdraw-all btn-decision-ahmed" id="btn_opt_withdraw_all_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_all', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                سحب كامل
                                            </button>
                                            <button type="button" class="btn-decision btn-decision-withdraw-partial btn-decision-ahmed" id="btn_opt_withdraw_partial_ahmed" onclick="setPartnerDecision('ahmed', 'withdraw_partial', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                سحب جزئي
                                            </button>
                                        </div>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" id="input_action_ahmed" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" id="input_withdrawn_ahmed" value="0">

                                        <div id="box_reinvest_ahmed" class="payout-box-ahmed cls-reinvest-pill">
                                            <i class="fa fa-check-circle me-1"></i> تدوير بالحساب للدورة القادمة
                                        </div>

                                        <div id="box_withdraw_all_ahmed" class="payout-box-ahmed mt-1" style="display:none;">
                                            <select name="decisions[ahmed][account_id]" id="acc_sel_ahmed" class="form-select form-select-sm fw-semibold treasury-select" style="font-size:0.76rem; border-color: rgba(185,28,28,0.35);" data-pid="ahmed" data-name="{{ $headName }}">
                                                <option value="">-- اختر خزينة الصرف ({{ number_format($ahmedNetProfit, 2) }} ج) --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @php
                                            $ahmedFixedWithdrawal = (float) ($preview['ahmed']['monthly_fixed_withdrawal'] ?? 0);
                                            $ahmedDefaultPartialVal = $ahmedFixedWithdrawal > 0 ? min($ahmedNetProfit, $ahmedFixedWithdrawal) : 0;
                                        @endphp
                                        <div id="box_withdraw_partial_ahmed" class="payout-box-ahmed mt-1" style="display:none;">
                                            <div class="input-group input-group-sm mb-1">
                                                <input type="number" step="0.01" min="0" max="{{ $ahmedNetProfit }}" id="inp_partial_val_ahmed" data-fixed="{{ $ahmedFixedWithdrawal }}" class="form-control fw-semibold text-danger" value="{{ $ahmedDefaultPartialVal > 0 ? $ahmedDefaultPartialVal : '' }}" placeholder="0.00" oninput="onPartialInput('ahmed', {{ $ahmedOpenCap }}, {{ $ahmedNetProfit }})">
                                                <span class="input-group-text bg-white fw-semibold">ج</span>
                                            </div>
                                            <div class="text-muted fw-semibold mb-1 d-flex justify-content-between align-items-center" style="font-size:0.7rem;" id="partial_rollover_badge_ahmed">
                                                <span>المدور: <strong style="color: var(--c-success);">{{ number_format($ahmedNetProfit - $ahmedDefaultPartialVal, 2) }} ج</strong></span>
                                                @if($ahmedFixedWithdrawal > 0)
                                                    <span class="prt-chip is-info" style="font-size:0.65rem; padding:1px 6px;"><i class="fa fa-lock fa-xs"></i> مثبت: {{ number_format($ahmedFixedWithdrawal) }} ج</span>
                                                @endif
                                            </div>
                                            <select id="acc_partial_sel_ahmed" class="form-select form-select-sm fw-semibold treasury-select-partial" style="font-size:0.76rem;" data-pid="ahmed" data-name="{{ $headName }}" onchange="syncPartialAccount('ahmed')">
                                                <option value="">-- اختر خزينة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($ahmedNetProfit < 0)
                                        <span class="prt-chip is-danger w-100 py-2">
                                            خصم العجز ({{ number_format($ahmedNetProfit, 2) }} ج)
                                        </span>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" value="0">
                                    @else
                                        <span class="prt-chip is-neutral w-100 py-2">
                                            لا يوجد ربح للتوزيع (0.00 ج)
                                        </span>
                                        <input type="hidden" name="decisions[ahmed][payout_action]" value="reinvest">
                                        <input type="hidden" name="decisions[ahmed][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- الرصيد الجديد بعد التقفيل --}}
                                <td id="cell_cap_ahmed">
                                    <div class="prt-num-main" id="new_cap_display_ahmed">{{ number_format($ahmedCloseCap, 2) }} ج</div>
                                    <small id="new_cap_sub_ahmed" class="d-block mt-1" style="font-size:0.72rem; color: {{ $ahmedNetProfit > 0 ? 'var(--c-success)' : ($ahmedNetProfit < 0 ? 'var(--c-danger)' : 'var(--c-text-soft)') }};">
                                        @if($ahmedNetProfit > 0)
                                            ↑ +{{ number_format($ahmedNetProfit, 2) }} ج
                                        @elseif($ahmedNetProfit < 0)
                                            ↓ {{ number_format($ahmedNetProfit, 2) }} ج
                                        @else
                                            ثابت (0.00 ج)
                                        @endif
                                    </small>
                                </td>

                                {{-- النسبة الجديدة --}}
                                <td class="text-center">
                                    <span class="prt-chip is-accent" id="new_pct_display_ahmed">{{ number_format($ahmedClosePct, 2) }}%</span>
                                    <div id="new_pct_diff_ahmed" class="mt-1" style="font-size:0.7rem;">
                                        @if($ahmedPctDiff > 0.0001)
                                            <span class="fw-semibold" style="color: var(--c-success);">↑ +{{ number_format($ahmedPctDiff, 2) }}%</span>
                                        @elseif($ahmedPctDiff < -0.0001)
                                            <span class="fw-semibold" style="color: var(--c-danger);">↓ {{ number_format($ahmedPctDiff, 2) }}%</span>
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
                                        <strong class="d-block" style="color: var(--c-navy);">{{ $line['name'] }}</strong>
                                        <div class="prt-meta-line">{{ $line['phone'] ?: '—' }}</div>
                                    </td>
                                    <td class="prt-num-main">{{ number_format($line['opening_capital'], 2) }} ج</td>
                                    <td class="text-center">
                                        <span class="prt-chip is-neutral">{{ number_format($line['capital_share_pct'], 2) }}%</span>
                                    </td>
                                    <td class="fw-semibold" style="color: {{ $line['gross_share'] >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        <span dir="ltr">{{ $line['gross_share'] >= 0 ? '+' : '' }}{{ number_format($line['gross_share'], 2) }}</span> ج
                                    </td>
                                    <td class="text-center">
                                        <div class="fw-semibold" style="color: var(--c-navy);">+{{ number_format($line['ahmed_commission'], 2) }} ج</div>
                                        <span class="prt-chip is-neutral mt-1" style="font-size:0.67rem; padding:1px 7px;">نسبة الشغل {{ number_format($line['ahmed_ratio_pct'], 0) }}%</span>
                                    </td>
                                    <td class="fw-semibold" style="color: {{ $line['net_share'] >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        <span dir="ltr">{{ $line['net_share'] >= 0 ? '+' : '' }}{{ number_format($line['net_share'], 2) }}</span> ج
                                    </td>

                                    {{-- عمود القرار والصرف المدمج للشريك --}}
                                    <td>
                                        @if($line['net_share'] > 0)
                                            <div class="cls-dec-group" role="group">
                                                <button type="button" class="btn-decision btn-decision-reinvest btn-decision-{{ $pid }} active" id="btn_opt_reinvest_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'reinvest', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                    تدوير
                                                </button>
                                                <button type="button" class="btn-decision btn-decision-withdraw-all btn-decision-{{ $pid }}" id="btn_opt_withdraw_all_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_all', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                    سحب كامل
                                                </button>
                                                <button type="button" class="btn-decision btn-decision-withdraw-partial btn-decision-{{ $pid }}" id="btn_opt_withdraw_partial_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_partial', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                    سحب جزئي
                                                </button>
                                            </div>
                                            <input type="hidden" name="decisions[{{ $pid }}][payout_action]" id="input_action_{{ $pid }}" value="reinvest">
                                            <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" id="input_withdrawn_{{ $pid }}" value="0">

                                            <div id="box_reinvest_{{ $pid }}" class="payout-box-{{ $pid }} cls-reinvest-pill">
                                                <i class="fa fa-check-circle me-1"></i> تدوير بالرصيد (+{{ number_format($line['net_share'], 2) }} ج)
                                            </div>

                                            <div id="box_withdraw_all_{{ $pid }}" class="payout-box-{{ $pid }} mt-1" style="display:none;">
                                                <select name="decisions[{{ $pid }}][account_id]" id="acc_sel_{{ $pid }}" class="form-select form-select-sm fw-semibold treasury-select" style="font-size:0.76rem; border-color: rgba(185,28,28,0.35);" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}">
                                                    <option value="">-- اختر خزينة الصرف ({{ number_format($line['net_share'], 2) }} ج) --</option>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @php
                                                $fixedWithdrawal = (float) ($line['monthly_fixed_withdrawal'] ?? 0);
                                                $defaultPartialVal = $fixedWithdrawal > 0 ? min($line['net_share'], $fixedWithdrawal) : 0;
                                            @endphp
                                            <div id="box_withdraw_partial_{{ $pid }}" class="payout-box-{{ $pid }} mt-1" style="display:none;">
                                                <div class="input-group input-group-sm mb-1">
                                                    <input type="number" step="0.01" min="0" max="{{ $line['net_share'] }}" id="inp_partial_val_{{ $pid }}" data-fixed="{{ $fixedWithdrawal }}" class="form-control fw-semibold text-danger" value="{{ $defaultPartialVal > 0 ? $defaultPartialVal : '' }}" placeholder="0.00" oninput="onPartialInput({{ $pid }}, {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                    <span class="input-group-text bg-white fw-semibold">ج</span>
                                                </div>
                                                <div class="text-muted fw-semibold mb-1 d-flex justify-content-between align-items-center" style="font-size:0.7rem;" id="partial_rollover_badge_{{ $pid }}">
                                                    <span>المدور: <strong style="color: var(--c-success);">{{ number_format($line['net_share'] - $defaultPartialVal, 2) }} ج</strong></span>
                                                    @if($fixedWithdrawal > 0)
                                                        <span class="prt-chip is-info" style="font-size:0.65rem; padding:1px 6px;" title="مسجل في بروفايل الشريك كمسحوب شهري مثبت"><i class="fa fa-lock fa-xs"></i> مثبت: {{ number_format($fixedWithdrawal) }} ج</span>
                                                    @endif
                                                </div>
                                                <select id="acc_partial_sel_{{ $pid }}" class="form-select form-select-sm fw-semibold treasury-select-partial" style="font-size:0.76rem;" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}" onchange="syncPartialAccount({{ $pid }})">
                                                    <option value="">-- اختر خزينة الصرف --</option>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @elseif($line['net_share'] < 0)
                                            <span class="prt-chip is-danger w-100 py-2">
                                                خصم الخسارة ({{ number_format($line['net_share'], 2) }} ج)
                                            </span>
                                            <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                            <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                        @else
                                            <span class="prt-chip is-neutral w-100 py-2">
                                                لا يوجد ربح للتوزيع (0.00 ج)
                                            </span>
                                            <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                            <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                        @endif
                                    </td>

                                    {{-- الرصيد الجديد المعتمد --}}
                                    <td id="cell_cap_{{ $pid }}">
                                        <div class="prt-num-main" id="new_cap_display_{{ $pid }}">{{ number_format($line['closing_capital'], 2) }} ج</div>
                                        <small id="new_cap_sub_{{ $pid }}" class="d-block mt-1" style="font-size:0.72rem; color: {{ $line['net_share'] > 0 ? 'var(--c-success)' : ($line['net_share'] < 0 ? 'var(--c-danger)' : 'var(--c-text-soft)') }};">
                                            @if($line['net_share'] > 0)
                                                ↑ +{{ number_format($line['net_share'], 2) }} ج
                                            @elseif($line['net_share'] < 0)
                                                ↓ {{ number_format($line['net_share'], 2) }} ج
                                            @else
                                                ثابت (0.00 ج)
                                            @endif
                                        </small>
                                    </td>

                                    {{-- النسبة الجديدة --}}
                                    <td class="text-center">
                                        <span class="prt-chip is-accent" id="new_pct_display_{{ $pid }}">{{ number_format($line['closing_share_pct'], 2) }}%</span>
                                        <div id="new_pct_diff_{{ $pid }}" class="mt-1" style="font-size:0.7rem;">
                                            @if($line['share_pct_diff'] > 0.0001)
                                                <span class="fw-semibold" style="color: var(--c-success);">↑ +{{ number_format($line['share_pct_diff'], 2) }}%</span>
                                            @elseif($line['share_pct_diff'] < -0.0001)
                                                <span class="fw-semibold" style="color: var(--c-danger);">↓ {{ number_format($line['share_pct_diff'], 2) }}%</span>
                                            @else
                                                <span class="text-muted">ثابتة</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">لا يوجد شركاء نشطين لتوزيع الأرباح عليهم في هذه الفترة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- شريط ملخص المسحوبات والتدوير قبل الحفظ والاعتماد --}}
                <div class="cls-summary-strip no-print" id="liveWithdrawalSummaryCard">
                    <div class="row g-3 align-items-stretch">
                        <div class="col-lg-4 col-md-6">
                            <div class="cls-summary-tile" id="boxLiveWithdrawnBadge">
                                <div>
                                    <span class="d-block small fw-semibold mb-1" style="color: var(--c-danger);">
                                        <i class="fa fa-money-bill-wave me-1"></i> إجمالي المسحوب نقداً الآن
                                    </span>
                                    <strong class="fs-4" style="color: var(--c-danger); font-variant-numeric: tabular-nums;" id="live_total_withdrawn_display">0.00 ج</strong>
                                </div>
                                <span class="prt-chip is-neutral" id="live_withdrawing_count_badge">0 مسحوبات</span>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="cls-summary-tile is-emerald">
                                <div>
                                    <span class="d-block small fw-semibold mb-1" style="color: var(--c-success);">
                                        <i class="fa fa-sync-alt me-1"></i> إجمالي المدوّر في رأس المال
                                    </span>
                                    <strong class="fs-4" style="color: var(--c-success); font-variant-numeric: tabular-nums;" id="live_total_reinvested_display">{{ number_format($preview['gross_profit'], 2) }} ج</strong>
                                </div>
                                <span class="prt-chip is-success">مدوّر بالحسابات</span>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12">
                            <div class="cls-summary-tile is-teal">
                                <div>
                                    <span class="d-block small fw-semibold mb-1" style="color: var(--c-accent);">
                                        <i class="fa fa-vault me-1"></i> رأس المال الجديد بعد التقفيل
                                    </span>
                                    <strong class="fs-4" style="color: var(--c-navy); font-variant-numeric: tabular-nums;" id="live_total_closing_capital_display">{{ number_format($preview['total_closing_shares'], 2) }} ج</strong>
                                </div>
                                <span class="prt-chip is-accent">الرصيد المعتمد</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ذيل الاعتماد وخيار الإقفال السنوي --}}
                <div class="p-4 border-top no-print" style="background: #f6f9fb;">
                    @php
                        $closingDt  = \Carbon\Carbon::parse($preview['end_date']);
                        $closingYr  = $closingDt->year;
                        $nextYr     = $closingYr + 1;
                        $isDecMonth = ($closingDt->month === 12);
                    @endphp
                    <div class="cls-annual-box {{ $isDecMonth ? 'is-dec' : '' }}">
                        <div class="form-check d-flex align-items-center gap-2 m-0">
                            <input class="form-check-input mt-0" type="checkbox" name="is_annual_closing" value="1" id="chkIsAnnualClosing" style="width: 1.15rem; height: 1.15rem; cursor: pointer;" {{ $isDecMonth ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="chkIsAnnualClosing" style="cursor: pointer; color: var(--c-navy);">
                                <i class="fa fa-calendar-check me-1" style="color: var(--c-accent);"></i>
                                إقفال السنة المالية ({{ $closingYr }}): حفظ الأرصدة الختامية في سجل «التقفيلات السنوية» مع الحفاظ على رأس المال التأسيسي ثابتاً لا يتغير
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1 ms-4">
                            عند تفعيل هذا الخيار، سيتم بعد توزيع الأرباح حفظ أرصدة نهاية سنة {{ $closingYr }} ونسبة النمو السنوي لكل شريك في تبويب «التقفيلات السنوية» دون تعديل رأس المال التأسيسي.
                        </small>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-7">
                            <label class="form-pro-label">ملاحظات التقفيل والاعتماد</label>
                            <input type="text" name="notes" class="form-pro-control" placeholder="مثال: تم إقفال دورة الشهر وتحديث نسب الشركاء وحساب الإدارة...">
                        </div>
                        <div class="col-md-5 d-flex align-items-center justify-content-md-end gap-3 flex-wrap">
                            <div class="text-end d-none d-sm-block">
                                <span class="prt-meta-line d-block m-0">المسحوب نقداً قبل الحفظ:</span>
                                <strong style="color: var(--c-danger); font-variant-numeric: tabular-nums;" id="footer_withdrawn_display">0.00 ج</strong>
                            </div>
                            <button type="submit" class="prt-pill-btn is-primary px-4 py-2" id="btnSubmitClosing">
                                <i class="fa fa-check-circle"></i>
                                <span>اعتماد التقفيل وتحديث النسب والهيستوري</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- ملخص أرباح حساب الإدارة للدورة --}}
        <div class="prt-panel">
            <div class="p-4">
                <div class="row g-3 align-items-stretch">
                    <div class="col-lg-3 col-md-6 d-flex align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <span class="prt-title-badge" style="width: 46px; height: 46px;"><i class="fa fa-briefcase"></i></span>
                            <div>
                                <h6 class="fw-bold m-0" style="color: var(--c-navy);">ملخص حساب الإدارة ({{ $headName }})</h6>
                                <div class="prt-meta-line">نسب الشغل + ربح الرصيد المدور</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="cls-mgmt-tile">
                            <div class="prt-meta-line m-0 mb-1">ربح الرصيد المدور في حساب الإدارة</div>
                            <strong class="fs-5" style="color: var(--c-navy); font-variant-numeric: tabular-nums;">{{ number_format($preview['ahmed']['own_profit'], 2) }} ج</strong>
                            <div class="prt-num-sub">نسبة الحساب قبل التقفيل: {{ number_format($preview['ahmed']['capital_share_pct'], 2) }}%</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="cls-mgmt-tile">
                            <div class="prt-meta-line m-0 mb-1">الأرباح من نسب الشغل مع الشركاء</div>
                            <strong class="fs-5" style="color: var(--c-info); font-variant-numeric: tabular-nums;">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</strong>
                            <div class="prt-num-sub">مجموع نسب الإدارة من أرباح الشركاء</div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="cls-mgmt-tile is-total">
                            <div class="small fw-semibold mb-1" style="color: var(--c-success);">إجمالي الدخل المضاف لحساب الإدارة</div>
                            <strong class="fs-4" style="color: var(--c-success); font-variant-numeric: tabular-nums;">{{ number_format($preview['ahmed']['total_profit'], 2) }} ج</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
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
            const fixedVal = parseFloat(inp ? inp.getAttribute('data-fixed') : 0) || 0;
            let val = parseFloat(inp && inp.value !== '' ? inp.value : (fixedVal > 0 ? Math.min(netShare, fixedVal) : 0));
            if (isNaN(val) || val < 0) val = 0;
            if (val > netShare) val = netShare;
            if (inp && inp.value === '') {
                inp.value = val > 0 ? val : '';
            }
            withdrawn = val;
            reinvested = netShare - withdrawn;
            if (inputWithdrawn) inputWithdrawn.value = withdrawn.toFixed(2);

            const badge = document.getElementById('partial_rollover_badge_' + pid);
            if (badge) {
                const fixedBadge = fixedVal > 0 ? `<span class="prt-chip is-info" style="font-size:0.65rem; padding:1px 6px;"><i class="fa fa-lock fa-xs"></i> مثبت: ${Number(fixedVal).toLocaleString()} ج</span>` : '';
                badge.innerHTML = `<span>المدور: <strong style="color:var(--c-success);">${Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2})} ج</strong></span> ${fixedBadge}`;
            }

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

    function onPartialInput(pid, openCap, netShare) {
        const inp = document.getElementById('inp_partial_val_' + pid);
        let val = parseFloat(inp && inp.value !== '' ? inp.value : 0) || 0;
        if (val > netShare) {
            val = netShare;
            if (inp) inp.value = val;
        }
        if (val < 0) {
            val = 0;
            if (inp) inp.value = 0;
        }

        const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
        if (inputWithdrawn) inputWithdrawn.value = val.toFixed(2);

        const rem = netShare - val;
        const badge = document.getElementById('partial_rollover_badge_' + pid);
        const fixedVal = parseFloat(inp ? inp.getAttribute('data-fixed') : 0) || 0;
        const fixedBadge = fixedVal > 0 ? `<span class="prt-chip is-info" style="font-size:0.65rem; padding:1px 6px;"><i class="fa fa-lock fa-xs"></i> مثبت: ${Number(fixedVal).toLocaleString()} ج</span>` : '';
        if (badge) {
            badge.innerHTML = `<span>المدور: <strong style="color:var(--c-success);">${Number(rem).toLocaleString('en-US', {minimumFractionDigits: 2})} ج</strong></span> ${fixedBadge}`;
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
        if (tr) tr.setAttribute('data-newcap', newCap.toFixed(2));

        const capDisplay = document.getElementById('new_cap_display_' + pid);
        const capSub = document.getElementById('new_cap_sub_' + pid);

        if (capDisplay) {
            capDisplay.innerText = Number(newCap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (capSub) {
            if (action === 'reinvest') {
                capSub.style.color = 'var(--c-success)';
                capSub.innerHTML = '↑ زيادة (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            } else if (action === 'withdraw_all') {
                capSub.style.color = 'var(--c-text-soft)';
                capSub.innerHTML = '= ثبات الرصيد (سحب كامل الربح)';
            } else {
                capSub.style.color = 'var(--c-info)';
                capSub.innerHTML = '↑ زيادة بالمدور (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            }
        }

        recalcAllClosingSharePcts();
        updateWithdrawalSummary();
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

            if (pctEl) pctEl.innerText = newPct.toFixed(2) + '%';
            if (diffEl) {
                if (diff > 0.005) {
                    diffEl.innerHTML = '<span class="fw-semibold" style="color:var(--c-success);">↑ +' + diff.toFixed(2) + '%</span>';
                } else if (diff < -0.005) {
                    diffEl.innerHTML = '<span class="fw-semibold" style="color:var(--c-danger);">↓ ' + diff.toFixed(2) + '%</span>';
                } else {
                    diffEl.innerHTML = '<span class="text-muted">ثابتة</span>';
                }
            }
        });
    }

    function updateWithdrawalSummary() {
        let totalWithdrawn = 0;
        let totalNetProfit = 0;
        let withdrawingCount = 0;
        let totalNewCap = 0;

        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            const newCap = parseFloat(tr.getAttribute('data-newcap')) || 0;
            const actionInp = document.getElementById('input_action_' + pid);
            const action = actionInp ? actionInp.value : 'reinvest';
            const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
            const withdrawnVal = parseFloat(inputWithdrawn ? inputWithdrawn.value : 0) || 0;

            if (netShare > 0) {
                totalNetProfit += netShare;
                if (action === 'withdraw_all' || action === 'withdraw_partial') {
                    if (withdrawnVal > 0) {
                        totalWithdrawn += withdrawnVal;
                        withdrawingCount++;
                    }
                }
            }
            totalNewCap += newCap;
        });

        const totalReinvested = Math.max(0, totalNetProfit - totalWithdrawn);

        const elWithdrawn = document.getElementById('live_total_withdrawn_display');
        const elReinvested = document.getElementById('live_total_reinvested_display');
        const elNewCap = document.getElementById('live_total_closing_capital_display');
        const elBadge = document.getElementById('live_withdrawing_count_badge');
        const elFooterWithdrawn = document.getElementById('footer_withdrawn_display');
        const boxBadge = document.getElementById('boxLiveWithdrawnBadge');

        if (elWithdrawn) elWithdrawn.innerText = Number(totalWithdrawn).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        if (elFooterWithdrawn) elFooterWithdrawn.innerText = Number(totalWithdrawn).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        if (elReinvested) elReinvested.innerText = Number(totalReinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        if (elNewCap) elNewCap.innerText = Number(totalNewCap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';

        if (elBadge) {
            elBadge.innerText = withdrawingCount + (withdrawingCount === 1 ? ' عملية سحب' : ' عمليات سحب');
            elBadge.className = totalWithdrawn > 0 ? 'prt-chip is-danger' : 'prt-chip is-neutral';
        }
        if (boxBadge) {
            boxBadge.classList.toggle('is-rose', totalWithdrawn > 0);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateWithdrawalSummary();
    });

    document.getElementById('formExecuteClosing').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = this;

        let hasError = false;
        let errorMsg = '';
        let totalWithdrawn = 0;
        let totalNetProfit = 0;
        let totalNewCap = 0;
        const withdrawnItems = [];

        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            const newCap = parseFloat(tr.getAttribute('data-newcap')) || 0;
            totalNewCap += newCap;

            if (netShare <= 0) return;
            totalNetProfit += netShare;

            const actionInp = document.getElementById('input_action_' + pid);
            const action = actionInp ? actionInp.value : 'reinvest';
            const partnerName = tr.querySelector('strong') ? tr.querySelector('strong').innerText.trim() : 'المحدد';

            if (action === 'withdraw_all') {
                const accSel = document.getElementById('acc_sel_' + pid);
                if (!accSel || !accSel.value) {
                    errorMsg = 'يرجى تحديد خزينة الصرف لصرف أرباح «' + partnerName + '» (سحب كامل).';
                    hasError = true;
                    if (accSel) accSel.focus();
                } else {
                    const accName = accSel.options[accSel.selectedIndex] ? accSel.options[accSel.selectedIndex].text : '';
                    totalWithdrawn += netShare;
                    withdrawnItems.push({ name: partnerName, amount: netShare, accountName: accName, type: 'سحب كامل' });
                }
            } else if (action === 'withdraw_partial') {
                const accPartialSel = document.getElementById('acc_partial_sel_' + pid);
                const accSel = document.getElementById('acc_sel_' + pid);
                const selectedVal = (accPartialSel && accPartialSel.value) || (accSel && accSel.value);
                const inp = document.getElementById('inp_partial_val_' + pid);
                const partialVal = parseFloat(inp ? inp.value : 0) || 0;

                if (!selectedVal) {
                    errorMsg = 'يرجى تحديد خزينة الصرف لصرف حصة «' + partnerName + '» (سحب جزئي).';
                    hasError = true;
                    if (accPartialSel) accPartialSel.focus();
                } else {
                    const targetSel = (accPartialSel && accPartialSel.value) ? accPartialSel : accSel;
                    const accName = targetSel.options[targetSel.selectedIndex] ? targetSel.options[targetSel.selectedIndex].text : '';
                    totalWithdrawn += partialVal;
                    withdrawnItems.push({ name: partnerName, amount: partialVal, accountName: accName, type: 'سحب جزئي' });
                }
            }
        });

        if (hasError) {
            alert(errorMsg);
            return false;
        }

        const totalReinvested = Math.max(0, totalNetProfit - totalWithdrawn);

        let breakdownHtml = '';
        if (withdrawnItems.length > 0) {
            breakdownHtml = `
            <div class="mt-3 p-3 rounded-3 border text-end" style="background:#f7fafc; max-height: 190px; overflow-y: auto; font-size: 0.82rem;">
                <div class="fw-bold mb-2 border-bottom pb-1 d-flex justify-content-between align-items-center" style="color:var(--c-danger);">
                    <span><i class="fa fa-list me-1"></i> تفاصيل الحسابات التي سيتم الصرف منها:</span>
                    <span class="prt-chip is-danger">${withdrawnItems.length} عمليات</span>
                </div>
                ${withdrawnItems.map(item => `
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                        <div>
                            <strong style="color:var(--c-navy);">${item.name}</strong>
                            <small class="text-muted d-block" style="font-size:0.72rem;">${item.accountName} (${item.type})</small>
                        </div>
                        <span class="fw-bold" style="color:var(--c-danger); font-variant-numeric:tabular-nums;">-${item.amount.toLocaleString('en-US', {minimumFractionDigits: 2})} ج</span>
                    </div>
                `).join('')}
            </div>`;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'تأكيد إجمالي الصرف والاعتماد',
                html: `
                    <div class="text-center">
                        <div class="p-3 mb-3 rounded-3 border" style="background:${totalWithdrawn > 0 ? '#fdf0f0' : '#ecf8f1'}; border-color:${totalWithdrawn > 0 ? 'rgba(185,28,28,0.2)' : 'rgba(45,134,89,0.2)'};">
                            <span class="d-block small text-muted fw-semibold mb-1">
                                إجمالي المبلغ الذي سيتم سحبه وصرفه نقداً الآن
                            </span>
                            <h2 class="fw-bold m-0" style="color:${totalWithdrawn > 0 ? '#b91c1c' : '#2d8659'}; font-variant-numeric:tabular-nums;">
                                ${totalWithdrawn.toLocaleString('en-US', {minimumFractionDigits: 2})} ج
                            </h2>
                            ${totalWithdrawn === 0 ? '<small style="color:#2d8659;" class="fw-semibold d-block mt-1">كامل الأرباح ستُدوّر وتُضاف لرؤوس الأموال</small>' : ''}
                        </div>

                        <div class="row g-2 text-muted small fw-semibold text-center">
                            <div class="col-6 p-2 rounded-3 border" style="background:#f5f9fb;">
                                <span>إجمالي المدوّر بالحسابات</span>
                                <div class="fs-6 fw-bold mt-1" style="color:#2d8659; font-variant-numeric:tabular-nums;">+${totalReinvested.toLocaleString('en-US', {minimumFractionDigits: 2})} ج</div>
                            </div>
                            <div class="col-6 p-2 rounded-3 border" style="background:#f5f9fb;">
                                <span>رأس المال الجديد بعد التقفيل</span>
                                <div class="fs-6 fw-bold mt-1" style="color:#0d7373; font-variant-numeric:tabular-nums;">${totalNewCap.toLocaleString('en-US', {minimumFractionDigits: 2})} ج</div>
                            </div>
                        </div>

                        ${breakdownHtml}

                        <div class="mt-3 text-muted small">
                            <i class="fa fa-shield-alt me-1" style="color:#2d8659;"></i> سيتم خصم المسحوبات من الخزائن المحددة وترحيل الحصص الجديدة في السجل فوراً.
                        </div>
                    </div>
                `,
                icon: totalWithdrawn > 0 ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d7373',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'تأكيد وصرف المبالغ واعتماد الإقفال',
                cancelButtonText: 'مراجعة وتعديل',
                reverseButtons: true,
                width: 580
            }).then((res) => {
                if (res.isConfirmed) form.submit();
            });
        } else {
            const confirmMsg = `تأكيد الإقفال والصرف:\n\n` +
                               `• إجمالي ما سيتم سحبه وصرفه نقداً من الخزائن: ${totalWithdrawn.toLocaleString('en-US', {minimumFractionDigits: 2})} ج\n` +
                               `• إجمالي ما سيتم تدويره في رأس المال: ${totalReinvested.toLocaleString('en-US', {minimumFractionDigits: 2})} ج\n\n` +
                               `هل تريد تأكيد الاعتماد النهائي؟`;
            if (confirm(confirmMsg)) form.submit();
        }
    });
</script>

</body>
</html>
