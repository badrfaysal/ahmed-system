<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الشركاء وحساب الإدارة - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'teal'])
    @include('partners._calm_theme')

    <style>
        /* ── شبكة المؤشرات (Bento Grid) ── */
        .prt-bento {
            display: grid;
            grid-template-columns: 1.32fr 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 16px;
            margin-bottom: 22px;
        }
        .prt-kpi-hero {
            grid-row: span 2;
        }

        /* ── حقل البحث الكبسولي ── */
        .prt-search-box {
            position: relative;
            width: 260px;
            max-width: 100%;
        }
        .prt-search-box input {
            width: 100%;
            padding: 8px 36px 8px 32px;
            border-radius: 999px;
            border: 1px solid #d7e3ec;
            background: rgba(255, 255, 255, 0.92);
            color: var(--c-text);
            font-size: 0.84rem;
            font-family: inherit;
            transition: all 0.18s ease;
        }
        .prt-search-box input::placeholder {
            color: var(--c-text-soft);
        }
        .prt-search-box input:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--c-accent);
            box-shadow: 0 0 0 3px var(--c-accent-bg);
        }
        .prt-search-icon {
            position: absolute;
            top: 50%;
            right: 13px;
            transform: translateY(-50%);
            color: var(--c-text-soft);
            font-size: 0.8rem;
            pointer-events: none;
        }
        .prt-search-clear {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--c-text-soft);
            font-size: 0.76rem;
            padding: 2px 4px;
            cursor: pointer;
            display: none;
        }
        .prt-search-clear:hover {
            color: var(--c-navy);
        }

        /* شريط المساهمة المصغر تحت النسبة */
        .prt-mini-bar {
            width: 56px;
            height: 4px;
            background: rgba(13, 31, 53, 0.09);
            border-radius: 999px;
            overflow: hidden;
            margin: 5px auto 0;
        }
        .prt-mini-bar > span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #3b82b8, #5ca0d3);
            border-radius: 999px;
        }

        /* أزرار الإجراءات الملوّنة */
        .prt-icon-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .prt-icon-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12); }
        .prt-icon-btn.is-view { background: #e0f2fe; border-color: #7dd3fc; color: #0369a1; }
        .prt-icon-btn.is-view:hover { background: #0284c7; border-color: #0284c7; color: #ffffff; }
        .prt-icon-btn.is-add  { background: #d1fae5; border-color: #6ee7b7; color: #047857; }
        .prt-icon-btn.is-add:hover  { background: #059669; border-color: #059669; color: #ffffff; }
        .prt-icon-btn.is-sub  { background: #fef3c7; border-color: #fcd34d; color: #b45309; }
        .prt-icon-btn.is-sub:hover  { background: #d97706; border-color: #d97706; color: #ffffff; }
        .prt-icon-btn.is-edit { background: #e0e7ff; border-color: #a5b4fc; color: #3730a3; }
        .prt-icon-btn.is-edit:hover { background: #4f46e5; border-color: #4f46e5; color: #ffffff; }
        .prt-icon-btn.is-exit { background: #ffe4e6; border-color: #fda4af; color: #be123c; }
        .prt-icon-btn.is-exit:hover { background: #e11d48; border-color: #e11d48; color: #ffffff; }

        /* ── أكورديون سجل تاريخ تغير النسب ── */
        .prt-hist-list {
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #f8fbfc;
        }
        .prt-hist-item {
            background: #ffffff;
            border: 1px solid #e1eaf2;
            border-radius: var(--p-radius-md);
            overflow: hidden;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .prt-hist-item:hover {
            border-color: #cbdbe8;
            box-shadow: var(--shadow-xs);
        }
        .prt-hist-head {
            cursor: pointer;
            padding: 14px 18px;
            background: #ffffff;
            border-right: 4px solid var(--c-accent);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            transition: background-color 0.15s ease;
        }
        .prt-hist-head:hover { background: #f4f9fb; }
        .prt-hist-head.ev-closing    { border-right-color: var(--c-info); }
        .prt-hist-head.ev-withdrawal { border-right-color: var(--c-danger); }
        .prt-hist-head.ev-deposit    { border-right-color: var(--c-success); }
        .prt-hist-head.ev-rollover   { border-right-color: var(--c-warning); }
        .prt-hist-head .hist-chevron { transition: transform 0.2s ease; }
        .prt-hist-head[aria-expanded="true"] .hist-chevron { transform: rotate(180deg); }

        .prt-subtable thead th {
            font-size: 0.75rem;
            padding: 10px 12px;
        }
        .prt-subtable tbody td {
            padding: 10px 12px;
            font-size: 0.83rem;
        }

        /* ── محاكي دخول شريك جديد ── */
        .prt-sim-body { padding: 22px 24px 26px; }
        .prt-sim-notice {
            background: #fdf8eb;
            border: 1px solid #f0dfb5;
            border-right: 4px solid var(--c-warning);
            border-radius: var(--p-radius-md);
            padding: 12px 16px;
            font-size: 0.84rem;
            color: #6b5323;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .prt-sim-notice i { color: var(--c-warning); font-size: 1.05rem; flex-shrink: 0; }
        .prt-sim-controls {
            background: linear-gradient(145deg, #f1f7f8 0%, #f6f8fc 100%);
            border: 1px solid #dce8ee;
            border-radius: var(--p-radius-lg);
            padding: 20px;
            margin-bottom: 20px;
        }
        .prt-sim-quick {
            border: 1px solid #d5e2ec;
            background: #ffffff;
            color: var(--c-text-muted);
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .prt-sim-quick:hover {
            border-color: var(--c-accent);
            background: var(--c-accent-bg);
            color: var(--c-accent);
        }
        .prt-sim-kpi {
            background: linear-gradient(155deg, #f3f7fb 0%, #ffffff 72%);
            border: 1px solid #dfe8f0;
            border-radius: var(--p-radius-md);
            padding: 16px;
            text-align: center;
            height: 100%;
        }
        .prt-sim-kpi.is-highlight {
            background: linear-gradient(155deg, #e2f3f2 0%, #f2faf9 100%);
            border-color: rgba(13, 115, 115, 0.24);
        }
        .prt-sim-chart-box {
            background: #ffffff;
            border: 1px solid #e1eaf2;
            border-radius: var(--p-radius-lg);
            padding: 18px 20px;
            text-align: center;
            height: 100%;
        }
        .prt-sim-row-new td {
            background: #e7f5f4 !important;
        }

        /* ── الاستجابة للمقاسات ── */
        @media (max-width: 1199px) {
            .prt-bento {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto;
            }
            .prt-kpi-hero {
                grid-row: auto;
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 767px) {
            .prt-bento { grid-template-columns: 1fr; }
            .prt-kpi-hero .kpi-val { font-size: 1.75rem; }
            .prt-search-box { width: 100%; }
            .prt-hist-list, .prt-sim-body { padding: 14px; }
        }

        /* ── تنسيق الطباعة الرسمية A4 ── */
        .print-only { display: none; }
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 7mm 12mm 7mm;
            }
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
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'IBM Plex Sans Arabic', 'Cairo', sans-serif !important;
                font-size: 7.8pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop,
            .no-print, th.no-print, td.no-print,
            button, .btn, .prt-icon-btn, .prt-pill-btn,
            .prt-panel-foot, .modal, .pagination, nav[role="navigation"],
            .prt-tabs-row, .prt-search-box, .prt-mini-bar {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
            }
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .prt-panel {
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                background: transparent !important;
            }
            .prt-panel-head {
                background: #f8fafc !important;
                border: 1px solid #94a3b8 !important;
                border-bottom: 2px solid #0d1f35 !important;
                padding: 6px 10px !important;
                margin-bottom: 6px !important;
                border-radius: 4px !important;
            }
            .prt-panel-title {
                font-size: 9pt !important;
                color: #0d1f35 !important;
            }
            .tab-content > .tab-pane:not(.active) {
                display: none !important;
            }
            .tab-content > .tab-pane.active {
                display: block !important;
                opacity: 1 !important;
            }
            .prt-table-wrap {
                overflow: visible !important;
            }
            .prt-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 7.5pt !important;
                margin-bottom: 8px !important;
            }
            .prt-table thead { display: table-header-group !important; }
            .prt-table tbody { display: table-row-group !important; }
            .prt-table thead th {
                background-color: #0d1f35 !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
                padding: 5px 4px !important;
                font-size: 7.2pt !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .prt-table tbody td {
                border: 1px solid #94a3b8 !important;
                padding: 4px 4px !important;
                font-size: 7.5pt !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .prt-table tfoot td {
                border: 1px solid #0d1f35 !important;
                border-top: 2px solid #0d1f35 !important;
                padding: 5px 4px !important;
                font-size: 7.6pt !important;
                font-weight: 700 !important;
                color: #000000 !important;
                background: #eef4f7 !important;
            }
            .prt-table tr {
                page-break-inside: avoid !important;
            }
            .prt-chip, .badge {
                border: 1px solid #475569 !important;
                color: #000000 !important;
                background: #f1f5f9 !important;
                padding: 1px 4px !important;
                font-size: 7pt !important;
            }
            .print-sig-col {
                border: 1px dashed #64748b !important;
                padding: 10px 8px !important;
                text-align: center !important;
                background: #ffffff !important;
                border-radius: 4px !important;
            }
        }
    </style>
</head>
<body>

@include('sidebar')

@php
    $activeMainTab = request('tab', '');
    if (!in_array($activeMainTab, ['partners', 'history', 'periods', 'annual', 'simulator'], true)) {
        $activeMainTab = 'partners';
        if (request()->has('history_page') || request()->has('hist_page')) {
            $activeMainTab = 'history';
        } elseif (request()->has('periods_page')) {
            $activeMainTab = 'periods';
        }
    }

    $maxSharePct = max(
        (float) $ahmedSharePct,
        (float) ($partners->max('capital_share_pct') ?? 0),
        25.0
    );

    $annualClosings          = $annualSummary['closings'] ?? collect();
    $annualClosingsDesc      = $annualSummary['closings_desc'] ?? collect();
    $annualYears             = $annualSummary['years'] ?? [];
    $annualLinesByYear       = $annualSummary['lines_by_year'] ?? collect();
    $annualLinesByPartnerYr  = $annualSummary['lines_by_partner_year'] ?? [];
    $prevClosingByPartner    = $annualSummary['prev_closing_by_partner'] ?? [];
    $nextSuggestedAnnualYear = $annualSummary['next_suggested_year'] ?? (int) date('Y');
@endphp

<div class="main-content">
    <div class="container-fluid px-0">

        {{-- الترويسة المطبوعة الرسمية (تظهر فقط عند الطباعة) --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">سجل الشركاء وحساب الإدارة والدورات الشهرية</small>
                    <span class="badge bg-dark text-white mt-1">كود الوثيقة: PRT-REG-{{ date('Ymd') }}</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark" id="printDocTitle">سجل الشركاء والحصص المعتمدة</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">حساب الإدارة: {{ $headName }}</div>
                    <small class="text-muted">إجمالي الحصص المعتمدة: <strong>{{ number_format($totalApprovedCapital, 2) }} ج</strong> · رأس مال الشركة الحالي: <strong>{{ number_format($totalCompanyCapital, 2) }} ج</strong></small>
                </div>
                <div class="text-start">
                    <div class="badge bg-dark text-white px-2 py-1 mb-1">وثيقة مالية رسمية</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- تنبيهات النظام --}}
        @if(session('success'))
            <div class="alert-pro success no-print">
                <i class="fa fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert-pro danger no-print">
                <i class="fa fa-exclamation-triangle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert-pro danger no-print">
                <i class="fa fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- ترويسة الصفحة وأزرار التحكم --}}
        <div class="prt-header no-print">
            <div>
                <div class="prt-title-row">
                    <span class="prt-title-badge"><i class="fa fa-briefcase"></i></span>
                    <h1 class="prt-title">إدارة الشركاء وحساب الإدارة</h1>
                </div>
                <p class="prt-subtitle">
                    نسب الشركاء وحساب الإدارة ثابتة ومعتمدة من إجمالي الحصص، ولا تتغير إلا عند إقفال الدورة الشهرية أو الإيداع / السحب المباشر.
                </p>
            </div>
            <div class="prt-actions">
                <a href="{{ route('partners.closing') }}" class="prt-pill-btn is-primary">
                    <i class="fa fa-calendar-check"></i>
                    <span>إقفال الدورة الشهرية</span>
                </a>
                <button type="button" class="prt-pill-btn is-amber" data-bs-toggle="modal" data-bs-target="#modalAnnualRollover" title="إقفال السنة المالية وحفظ أرصدة نهاية العام في سجل التقفيلات السنوية مع الحفاظ على رأس المال التأسيسي ثابتاً">
                    <i class="fa fa-calendar-alt fa-xs"></i>
                    <span>إقفال السنة</span>
                </button>
                <button type="button" class="prt-pill-btn is-emerald" data-bs-toggle="modal" data-bs-target="#modalAddPartner">
                    <i class="fa fa-plus fa-xs"></i>
                    <span>إضافة شريك</span>
                </button>
                <a href="{{ route('partners.ahmed') }}" class="prt-pill-btn is-blue">
                    <i class="fa fa-chart-pie fa-xs"></i>
                    <span>حساب الإدارة</span>
                </a>
                <button type="button" class="prt-pill-btn is-ghost" onclick="window.print()">
                    <i class="fa fa-print fa-xs"></i>
                    <span>طباعة السجل</span>
                </button>
            </div>
        </div>

        {{-- كروت المؤشرات المالية (Bento Grid) --}}
        <div class="prt-bento no-print">
            {{-- البطاقة الرئيسية الكبرى --}}
            <div class="prt-kpi-hero">
                <div class="kpi-lbl">رأس المال الحالي للشركة (اللحظي)</div>
                <div class="kpi-val">
                    <span dir="ltr">{{ number_format($totalCompanyCapital, 2) }}</span><span class="kpi-cur">ج</span>
                </div>
                <div class="kpi-sub">صافي السيولة + الأقساط القائمة</div>
                @if(abs($unclosedPeriodProfit) >= 0.01)
                    <div class="prt-status-pill {{ $unclosedPeriodProfit >= 0 ? 'is-pos' : 'is-neg' }}">
                        @if($unclosedPeriodProfit >= 0)
                            ربح تحت التوزيع: +{{ number_format($unclosedPeriodProfit, 2) }} ج
                        @else
                            عجز عن الحصص المعتمدة: {{ number_format(abs($unclosedPeriodProfit), 2) }} ج
                        @endif
                    </div>
                @endif
            </div>

            {{-- رأس المال السابق --}}
            <div class="prt-kpi-card is-blue">
                <div>
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">رأس المال السابق (الحصص المعتمدة)</div>
                        <span class="prt-kpi-ico"><i class="fa fa-history"></i></span>
                    </div>
                    <div class="kpi-val">
                        <span dir="ltr">{{ number_format($totalApprovedCapital, 2) }}</span><span class="kpi-cur">ج</span>
                    </div>
                    <div class="kpi-sub">إجمالي حصص الإدارة + الشركاء</div>
                </div>
            </div>

            {{-- رؤوس أموال الشركاء --}}
            <div class="prt-kpi-card is-teal">
                <div>
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">رؤوس أموال الشركاء</div>
                        <span class="prt-kpi-ico"><i class="fa fa-users"></i></span>
                    </div>
                    <div class="kpi-val">
                        <span dir="ltr">{{ number_format($totalPartnersCapital, 2) }}</span><span class="kpi-cur">ج</span>
                    </div>
                    <div class="kpi-sub">نسبتهم {{ number_format($partnersSharePct, 2) }}%</div>
                </div>
                <div class="prt-bar-track">
                    <div class="prt-bar-fill" style="width: {{ min(100, max(0, (float) $partnersSharePct)) }}%;"></div>
                </div>
            </div>

            {{-- حساب الإدارة --}}
            <div class="prt-kpi-card is-amber">
                <div>
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">حساب الإدارة ({{ $headName }})</div>
                        <span class="prt-kpi-ico"><i class="fa fa-user-tie"></i></span>
                    </div>
                    <div class="kpi-val">
                        <span dir="ltr">{{ number_format($ahmedCapital, 2) }}</span><span class="kpi-cur">ج</span>
                    </div>
                    <div class="kpi-sub">نسبته {{ number_format($ahmedSharePct, 2) }}%</div>
                </div>
                <div class="prt-bar-track">
                    <div class="prt-bar-fill is-amber" style="width: {{ (float) $ahmedSharePct > 0 ? max(2, min(100, (float) $ahmedSharePct)) : 0 }}%;"></div>
                </div>
            </div>

            {{-- أرباح حساب الإدارة --}}
            <div class="prt-kpi-card is-emerald">
                <div>
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">أرباح حساب الإدارة</div>
                        <span class="prt-kpi-ico"><i class="fa fa-hand-holding-dollar"></i></span>
                    </div>
                    <div class="kpi-val is-success">
                        <span dir="ltr">+{{ number_format($totalAhmedTotalEarned, 2) }}</span><span class="kpi-cur">ج</span>
                    </div>
                    <div class="kpi-sub">نسب الشغل: {{ number_format($totalAhmedCommissionsEarned, 2) }} ج</div>
                </div>
            </div>
        </div>

        {{-- شريط التبويبات الكبسولي --}}
        <div class="prt-tabs-row no-print">
            <ul class="nav prt-tabs-capsule" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeMainTab === 'partners' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMainPartners" type="button" role="tab">
                        <i class="fa fa-users fa-xs"></i>
                        <span>سجل الشركاء</span>
                        <span class="prt-tab-count">{{ $partners->count() + 1 }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeMainTab === 'history' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMainShareHistory" type="button" role="tab">
                        <i class="fa fa-history fa-xs"></i>
                        <span>تاريخ تغير النسب</span>
                        <span class="prt-tab-count">{{ $shareHistories->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeMainTab === 'periods' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMainPeriods" type="button" role="tab">
                        <i class="fa fa-calendar-check fa-xs"></i>
                        <span>التقفيلات الشهرية</span>
                        <span class="prt-tab-count">{{ $periods->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeMainTab === 'annual' ? 'active' : '' }}" id="tabBtnAnnualClosings" data-bs-toggle="tab" data-bs-target="#tabMainAnnual" type="button" role="tab">
                        <i class="fa fa-calendar-alt fa-xs"></i>
                        <span>التقفيلات السنوية</span>
                        <span class="prt-tab-count">{{ $annualClosings->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab-btn-simulator {{ $activeMainTab === 'simulator' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMainSimulator" type="button" role="tab">
                        <i class="fa fa-wand-magic-sparkles fa-xs"></i>
                        <span>محاكي دخول شريك</span>
                        <span class="prt-tab-sim-tag">توقعي</span>
                    </button>
                </li>
            </ul>
        </div>

        {{-- محتوى التبويبات --}}
        <div class="prt-panel">
            <div class="tab-content">

                {{-- ══ التاب 1: سجل الشركاء وحساب الإدارة ══ --}}
                <div class="tab-pane fade {{ $activeMainTab === 'partners' ? 'show active' : '' }}" id="tabMainPartners" role="tabpanel">
                    <div class="prt-panel-head">
                        <div>
                            <h5 class="prt-panel-title">سجل الشركاء وحساب الإدارة</h5>
                            <p class="prt-panel-sub">النسب المعتمدة للدورة الحالية - ثابتة طوال الشهر ولا تتأثر بأي ربح جديد إلا عند اعتماد الإقفال</p>
                        </div>
                        <div class="prt-search-box no-print">
                            <i class="fa fa-search prt-search-icon"></i>
                            <input type="text" id="partnerSearchInput" placeholder="ابحث عن شريك..." autocomplete="off" oninput="filterPartnersTable()">
                            <button type="button" id="partnerSearchClear" class="prt-search-clear" onclick="clearPartnerSearch()" title="مسح البحث">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="prt-table-wrap">
                        <table class="prt-table" id="partnersRegisterTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px;">#</th>
                                    <th style="min-width: 195px;">الشريك / الحساب</th>
                                    <th>أصل رأس المال</th>
                                    <th>الرصيد المعتمد</th>
                                    <th class="text-center">المساهمة</th>
                                    <th class="text-center">نسبة الإدارة</th>
                                    <th class="text-center">صافي الحصة</th>
                                    <th>إجمالي الأرباح</th>
                                    <th>المسحوبات</th>
                                    <th class="text-center no-print" style="width: 185px;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- صف حساب الإدارة --}}
                                <tr class="prt-row-head partner-searchable-row" data-search="{{ mb_strtolower($headName . ' حساب الإدارة') }}">
                                    <td class="text-center text-muted">—</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <a href="{{ route('partners.ahmed') }}" class="prt-name-link">{{ $headName }}</a>
                                            <span class="prt-chip is-head">حساب الإدارة</span>
                                        </div>
                                        <div class="prt-meta-line">
                                            يأخذ نسبة الشغل من أرباح الإدارة · يُدوّر ما لا يُسحب للدورة القادمة
                                        </div>
                                    </td>
                                    <td>
                                        @if($headPartner && (float)$headPartner->initial_capital > 0)
                                            <div class="prt-num-main">{{ number_format($headPartner->initial_capital, 2) }}</div>
                                            <div class="prt-num-sub">رأس مال تأسيسي</div>
                                        @else
                                            <div class="prt-num-sub">تراكمي من النسب</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="prt-num-main">{{ number_format($ahmedCapital, 2) }}</div>
                                        <div class="prt-num-sub">رصيد أرباح الإدارة</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="prt-chip is-info">{{ number_format($ahmedSharePct, 2) }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="prt-chip is-neutral">نسبة الشغل</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="prt-chip is-success" title="{{ number_format($ahmedSharePct, 2) }}% + نسب الشغل">
                                            {{ number_format($ahmedSharePct, 2) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="prt-num-main" style="color: var(--c-success);">+{{ number_format($totalAhmedTotalEarned, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($totalAhmedWithdrawn > 0)
                                            <span class="prt-num-main" style="color: var(--c-danger);">-{{ number_format($totalAhmedWithdrawn, 2) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <a href="{{ route('partners.ahmed') }}" class="prt-icon-btn is-view" title="تقرير حساب الإدارة">
                                                <i class="fa fa-chart-line"></i>
                                            </a>
                                            @if($headPartner)
                                                <button type="button" class="prt-icon-btn is-add" title="إيداع في حساب الإدارة" onclick="openAddCapitalModal({{ $headPartner->id }}, '{{ addslashes($headPartner->name) }}')">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                                <button type="button" class="prt-icon-btn is-sub" title="سحب نقدي من رصيد حساب الإدارة" onclick="openWithdrawCapitalModal({{ $headPartner->id }}, '{{ addslashes($headPartner->name) }}', {{ (float) $headPartner->capital }})">
                                                    <i class="fa fa-minus"></i>
                                                </button>
                                                <button type="button" class="prt-icon-btn is-edit" title="تعديل اسم وبيانات الحساب" onclick='openEditPartnerModal(@json($headPartner, JSON_HEX_APOS | JSON_HEX_QUOT))'>
                                                    <i class="fa fa-pen"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- قائمة الشركاء --}}
                                @forelse($partners as $p)
                                    @php
                                        $barWidth = $maxSharePct > 0 ? min(100, round(((float) $p->capital_share_pct / $maxSharePct) * 100, 1)) : 0;
                                    @endphp
                                    <tr class="partner-searchable-row {{ $p->status === 'exited' ? 'prt-row-exited' : '' }}" data-search="{{ mb_strtolower($p->name . ' ' . ($p->phone ?? '')) }}">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <a href="{{ route('partners.show', $p->id) }}" class="prt-name-link">{{ $p->name }}</a>
                                                @if($p->status === 'exited')
                                                    <span class="prt-chip is-danger" style="font-size: 0.68rem; padding: 1px 8px;">متخارج</span>
                                                @endif
                                            </div>
                                            <div class="prt-meta-line">
                                                انضمام: {{ $p->joined_at ? date('Y-m-d', strtotime($p->joined_at)) : '—' }} · {{ $p->phone ?: 'بدون هاتف' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="prt-num-main">{{ number_format((float)($p->initial_capital > 0 ? $p->initial_capital : $p->capital), 2) }}</div>
                                            <div class="prt-num-sub">رأس المال التأسيسي</div>
                                        </td>
                                        <td>
                                            <div class="prt-num-main">{{ number_format($p->capital, 2) }}</div>
                                            @if($p->status === 'active' && (float)$p->capital == 0 && (float)$p->initial_capital > 0)
                                                <div class="mt-1 no-print">
                                                    <span class="prt-chip is-warning mb-1" style="font-size: 0.68rem;">
                                                        <i class="fa fa-exclamation-triangle"></i> لم يتم تحديد المحفظة
                                                    </span>
                                                    <div>
                                                        <button type="button" class="prt-pill-btn is-soft is-sm py-1 px-2" style="font-size: 0.72rem;" onclick="openAddCapitalModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ (float)$p->initial_capital }})">
                                                            <i class="fa fa-wallet"></i> اختيار المحفظة وإيداع ({{ number_format($p->initial_capital) }} ج)
                                                        </button>
                                                    </div>
                                                </div>
                                            @elseif($p->initial_capital > 0 && $p->capital != $p->initial_capital)
                                                <div class="prt-num-sub" style="color: {{ $p->capital > $p->initial_capital ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                    @if($p->capital > $p->initial_capital)
                                                        ↑ +{{ number_format($p->capital - $p->initial_capital, 2) }} ج
                                                    @else
                                                        ↓ -{{ number_format($p->initial_capital - $p->capital, 2) }} ج
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="prt-chip is-info">{{ number_format($p->capital_share_pct, 2) }}%</span>
                                            @if($p->status === 'active' && (float) $p->capital_share_pct > 0)
                                                <div class="prt-mini-bar">
                                                    <span style="width: {{ max(4, $barWidth) }}%;"></span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="prt-chip is-neutral" title="نسبة الإدارة من ربح الشريك {{ number_format($p->ahmed_ratio, 1) }}%">
                                                {{ number_format($p->ahmed_ratio, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="prt-chip is-success">{{ number_format($p->effective_profit_pct, 2) }}%</span>
                                        </td>
                                        <td>
                                            <span class="prt-num-main" style="color: var(--c-success);">+{{ number_format($p->total_earned, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($p->total_withdrawn_profits > 0)
                                                <span class="prt-num-main" style="color: var(--c-danger);">-{{ number_format($p->total_withdrawn_profits, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center no-print">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <a href="{{ route('partners.show', $p->id) }}" class="prt-icon-btn is-view" title="عرض السجل والتحليلات">
                                                    <i class="fa fa-chart-line"></i>
                                                </a>
                                                @if($p->status === 'active')
                                                    <button type="button" class="prt-icon-btn is-add" title="إيداع / زيادة رأس مال" onclick="openAddCapitalModal({{ $p->id }}, '{{ addslashes($p->name) }}')">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                    <button type="button" class="prt-icon-btn is-sub" title="سحب من رأس المال" onclick="openWithdrawCapitalModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ (float) $p->capital }})">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                    <button type="button" class="prt-icon-btn is-edit" title="تعديل الإعدادات والنسبة" onclick='openEditPartnerModal(@json($p, JSON_HEX_APOS | JSON_HEX_QUOT))'>
                                                        <i class="fa fa-pen"></i>
                                                    </button>
                                                    <button type="button" class="prt-icon-btn is-exit" title="تصفية وتخارج نهائي" onclick="openExitPartnerModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ (float) $p->capital }})">
                                                        <i class="fa fa-right-from-bracket"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            لا يوجد شركاء مسجلين حتى الآن. ابدأ بإضافة شريك جديد عبر زر «إضافة شريك» بالأعلى.
                                        </td>
                                    </tr>
                                @endforelse

                                {{-- صف عدم وجود نتائج للبحث --}}
                                <tr id="partnerSearchEmptyRow" class="d-none no-print">
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        لا توجد نتائج مطابقة لبحثك.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="prt-panel-foot no-print">
                        <div>
                            إجمالي الحصص المعتمدة (شركاء + حساب الإدارة):
                            <strong style="color: var(--c-navy);">{{ number_format($totalApprovedCapital, 2) }} ج</strong>
                            · مجموع النسب = 100%
                        </div>
                        <div id="partnerSearchCount" class="text-muted"></div>
                    </div>
                </div>

                {{-- ══ التاب 2: تاريخ تغير النسب والحصص ══ --}}
                <div class="tab-pane fade {{ $activeMainTab === 'history' ? 'show active' : '' }} no-print" id="tabMainShareHistory" role="tabpanel">
                    <div class="prt-panel-head">
                        <div>
                            <h5 class="prt-panel-title">
                                <span>تاريخ تغير النسب والحصص</span>
                                <span class="prt-chip is-neutral">{{ $shareHistories->total() }} عملية</span>
                            </h5>
                            <p class="prt-panel-sub">اضغط على أي عملية لعرض تفاصيل النسب قبل وبعد التغيير</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="prt-pill-btn is-ghost is-sm" onclick="toggleAllHistoryCollapses(true)">
                                <i class="fa fa-expand-alt fa-xs"></i> فتح الكل
                            </button>
                            <button type="button" class="prt-pill-btn is-ghost is-sm" onclick="toggleAllHistoryCollapses(false)">
                                <i class="fa fa-compress-alt fa-xs"></i> إغلاق الكل
                            </button>
                        </div>
                    </div>

                    <div class="prt-hist-list">
                        @forelse($shareHistories as $hist)
                            @php
                                $hLines = $historyLinesGrouped->get($hist->id, collect());
                                $evClass = 'ev-closing';
                                if ($hist->event_type === 'capital_withdrawal' || $hist->event_type === 'partner_exit') {
                                    $evClass = 'ev-withdrawal';
                                } elseif ($hist->event_type === 'capital_increase' || $hist->event_type === 'initial_deposit') {
                                    $evClass = 'ev-deposit';
                                } elseif ($hist->event_type === 'annual_rollover') {
                                    $evClass = 'ev-rollover';
                                }
                            @endphp
                            <div class="prt-hist-item">
                                <div class="prt-hist-head {{ $evClass }}"
                                     data-bs-toggle="collapse"
                                     data-bs-target="#collapseHist_{{ $hist->id }}"
                                     aria-expanded="false">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        @if($hist->event_type === 'period_closing')
                                            <span class="prt-chip is-info"><i class="fa fa-calendar-check fa-xs"></i> إقفال دورة شهرية</span>
                                        @elseif($hist->event_type === 'capital_withdrawal')
                                            <span class="prt-chip is-danger"><i class="fa fa-arrow-down fa-xs"></i> سحب من الرصيد</span>
                                        @elseif($hist->event_type === 'capital_increase' || $hist->event_type === 'initial_deposit')
                                            <span class="prt-chip is-success"><i class="fa fa-arrow-up fa-xs"></i> إيداع / زيادة رأس مال</span>
                                        @elseif($hist->event_type === 'partner_exit')
                                            <span class="prt-chip is-neutral"><i class="fa fa-right-from-bracket fa-xs"></i> تخارج شريك</span>
                                        @elseif($hist->event_type === 'annual_rollover')
                                            <span class="prt-chip is-warning"><i class="fa fa-calendar-alt fa-xs"></i> إقفال سنوي</span>
                                        @else
                                            <span class="prt-chip is-neutral">{{ $hist->event_type }}</span>
                                        @endif
                                        <strong style="color: var(--c-navy); font-size: 0.9rem;">{{ $hist->title }}</strong>
                                        <span class="prt-meta-line m-0">· {{ date('Y-m-d H:i', strtotime($hist->created_at)) }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="small text-muted" style="font-variant-numeric: tabular-nums;">
                                            قبل: <strong>{{ number_format($hist->total_capital_before, 2) }} ج</strong>
                                            ← بعد: <strong style="color: var(--c-navy);">{{ number_format($hist->total_capital_after, 2) }} ج</strong>
                                        </span>
                                        <span class="prt-pill-btn is-ghost is-sm py-1 px-3">
                                            <span>عرض النسب</span>
                                            <i class="fa fa-chevron-down fa-xs hist-chevron"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="collapse hist-collapse-box border-top" id="collapseHist_{{ $hist->id }}">
                                    <div class="prt-table-wrap">
                                        <table class="prt-table prt-subtable">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="width: 40px;">#</th>
                                                    <th>الشريك / الحساب</th>
                                                    <th class="text-center">الرصيد قبل</th>
                                                    <th class="text-center">النسبة قبل (%)</th>
                                                    <th class="text-center">التغير في الرصيد</th>
                                                    <th class="text-center">الرصيد بعد</th>
                                                    <th class="text-center">النسبة بعد (%)</th>
                                                    <th class="text-center">فرق النسبة</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($hLines as $hl)
                                                    <tr class="{{ $hl->is_head ? 'prt-row-head' : '' }}">
                                                        <td class="text-center text-muted">{{ $hl->is_head ? '—' : $loop->iteration }}</td>
                                                        <td>
                                                            <span class="fw-semibold" style="color: var(--c-navy);">{{ $hl->partner_name }}</span>
                                                            @if($hl->is_head)
                                                                <span class="prt-chip is-head ms-1">حساب الإدارة</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center text-muted">{{ number_format($hl->capital_before, 2) }} ج</td>
                                                        <td class="text-center">
                                                            <span class="prt-chip is-neutral">{{ number_format($hl->share_pct_before, 2) }}%</span>
                                                        </td>
                                                        <td class="text-center fw-semibold" style="color: {{ $hl->capital_change > 0 ? 'var(--c-success)' : ($hl->capital_change < 0 ? 'var(--c-danger)' : 'var(--c-text-soft)') }};">
                                                            @if(abs($hl->capital_change) >= 0.01)
                                                                {{ $hl->capital_change > 0 ? '+' : '' }}{{ number_format($hl->capital_change, 2) }} ج
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td class="text-center fw-semibold" style="color: var(--c-navy);">{{ number_format($hl->capital_after, 2) }} ج</td>
                                                        <td class="text-center">
                                                            <span class="prt-chip is-info">{{ number_format($hl->share_pct_after, 2) }}%</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($hl->share_pct_diff > 0.0001)
                                                                <span class="prt-chip is-success">+{{ number_format($hl->share_pct_diff, 2) }}%</span>
                                                            @elseif($hl->share_pct_diff < -0.0001)
                                                                <span class="prt-chip is-danger">{{ number_format($hl->share_pct_diff, 2) }}%</span>
                                                            @else
                                                                <span class="text-muted small">ثابتة</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-pro">
                                <i class="fa fa-history"></i>
                                <h5>لا توجد سجلات تغير نسب حتى الآن</h5>
                                <p>سيتم تسجيل النسب تلقائياً عند كل إقفال دورة أو إيداع أو سحب رأس مال.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($shareHistories->hasPages())
                        <div class="prt-panel-foot">
                            <div>
                                عرض <strong>{{ $shareHistories->firstItem() ?? 0 }}</strong> إلى <strong>{{ $shareHistories->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $shareHistories->total() }}</strong> سجل
                            </div>
                            <div>
                                {{ $shareHistories->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ══ التاب 3: التقفيلات الشهرية المعتمدة ══ --}}
                <div class="tab-pane fade {{ $activeMainTab === 'periods' ? 'show active' : '' }} no-print" id="tabMainPeriods" role="tabpanel">
                    <div class="prt-panel-head">
                        <div>
                            <h5 class="prt-panel-title">
                                <span>سجل التقفيلات الشهرية المعتمدة</span>
                                <span class="prt-chip is-neutral">{{ $periods->total() }} دورة</span>
                            </h5>
                            <p class="prt-panel-sub">أرشيف الدورات الشهرية المقفلة وتوزيع الأرباح والمسحوبات</p>
                        </div>
                        <a href="{{ route('partners.closing') }}" class="prt-pill-btn is-soft is-sm">
                            <i class="fa fa-file-invoice-dollar fa-xs"></i>
                            <span>فتح إقفال دورة جديدة</span>
                        </a>
                    </div>

                    <div class="prt-table-wrap">
                        <table class="prt-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px;">#</th>
                                    <th>اسم الدورة</th>
                                    <th>الفترة المحاسبية</th>
                                    <th>رأس مال البداية</th>
                                    <th>رأس مال الإقفال</th>
                                    <th>الربح التشغيلي</th>
                                    <th>صافي ربح الشركاء</th>
                                    <th>ربح حساب الإدارة</th>
                                    <th>المسحوب نقداً</th>
                                    <th>المدوّر في الحصص</th>
                                    <th class="text-center no-print">التقرير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periods as $per)
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="fw-semibold" style="color: var(--c-navy);">{{ $per->period_name }}</td>
                                        <td class="text-muted small text-nowrap">{{ $per->start_date }} → {{ $per->end_date }}</td>
                                        <td class="text-nowrap">{{ number_format($per->opening_capital, 2) }} ج</td>
                                        <td class="fw-semibold text-nowrap" style="color: var(--c-navy);">{{ number_format($per->closing_capital, 2) }} ج</td>
                                        <td class="fw-semibold text-nowrap" style="color: {{ $per->gross_profit >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                            {{ $per->gross_profit >= 0 ? '+' : '' }}{{ number_format($per->gross_profit, 2) }} ج
                                        </td>
                                        <td class="fw-semibold text-nowrap" style="color: var(--c-navy);">{{ number_format($per->total_partners_net_profit, 2) }} ج</td>
                                        <td class="text-nowrap">
                                            <span class="fw-semibold" style="color: var(--c-accent);">+{{ number_format($per->ahmed_total_profit ?: $per->total_ahmed_commission, 2) }} ج</span>
                                            @if(!empty($per->ahmed_opening_capital) && $per->ahmed_opening_capital > 0)
                                                <div class="prt-num-sub">نسبته: {{ number_format($per->ahmed_capital_share_pct, 2) }}% ← {{ number_format($per->ahmed_closing_share_pct, 2) }}%</div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-nowrap" style="color: {{ $per->total_withdrawn > 0 ? 'var(--c-danger)' : 'var(--c-text-soft)' }};">
                                            {{ $per->total_withdrawn > 0 ? '-' . number_format($per->total_withdrawn, 2) . ' ج' : '—' }}
                                        </td>
                                        <td class="fw-semibold text-nowrap" style="color: {{ $per->total_reinvested > 0 ? 'var(--c-success)' : 'var(--c-text-soft)' }};">
                                            {{ $per->total_reinvested > 0 ? '+' . number_format($per->total_reinvested, 2) . ' ج' : '—' }}
                                        </td>
                                        <td class="text-center no-print">
                                            <a href="{{ route('partners.period.show', $per->id) }}" class="prt-pill-btn is-ghost is-sm py-1 px-3">
                                                <i class="fa fa-eye fa-xs"></i>
                                                <span>التقرير والنسب</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            لم يتم إقفال أي دورة شهرية حتى الآن. اضغط على «إقفال الدورة الشهرية» لحساب أرباح الدورة الأولى.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="prt-panel-foot no-print">
                        <div>
                            عرض <strong>{{ $periods->firstItem() ?? 0 }}</strong> إلى <strong>{{ $periods->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $periods->total() }}</strong> دورة معتمدة
                        </div>
                        <div>
                            {{ $periods->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- ══ التاب 4: التقفيلات السنوية ومقارنة نمو رؤوس الأموال عبر السنوات ══ --}}
                @php
                    $annualTotalFounding = ($headPartner ? (float) ($headPartner->initial_capital ?? 0) : 0.0)
                        + (float) $partners->sum(fn ($x) => (float) ($x->initial_capital ?? 0));
                    $annualTotalCurrent = (float) $totalApprovedCapital;
                    $annualTotalCumGain = round($annualTotalCurrent - $annualTotalFounding, 2);
                    $annualTotalCumPct  = $annualTotalFounding > 0 ? round(($annualTotalCumGain / $annualTotalFounding) * 100, 2) : 0.0;
                    $latestAnnualClosing = $annualClosingsDesc->first();
                @endphp
                <div class="tab-pane fade {{ $activeMainTab === 'annual' ? 'show active' : '' }}" id="tabMainAnnual" role="tabpanel">
                    <div class="prt-panel-head">
                        <div>
                            <h5 class="prt-panel-title">
                                <span class="prt-panel-title-ico"><i class="fa fa-calendar-alt"></i></span>
                                <span>التقفيلات السنوية ومقارنة نمو رؤوس الأموال عبر السنوات</span>
                                <span class="prt-chip is-accent">{{ $annualClosings->count() }} سنة مقفلة</span>
                            </h5>
                            <p class="prt-panel-sub">
                                رأس المال التأسيسي ثابت ومحفوظ لكل شريك لا يُعدّل أبداً، وتوضح هذه المصفوفة رصيد كل شريك ومقدار زيادته في نهاية كل سنة مالية.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap no-print">
                            <div class="prt-search-box" style="width: 215px;">
                                <i class="fa fa-search prt-search-icon"></i>
                                <input type="text" id="annualSearchInput" placeholder="ابحث عن شريك..." autocomplete="off" oninput="filterAnnualTable()">
                                <button type="button" id="annualSearchClear" class="prt-search-clear" onclick="clearAnnualSearch()" title="مسح البحث">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                            <button type="button" class="prt-pill-btn is-amber is-sm" data-bs-toggle="modal" data-bs-target="#modalAnnualRollover">
                                <i class="fa fa-lock fa-xs"></i>
                                <span>إقفال سنة مالية جديدة</span>
                            </button>
                            <button type="button" class="prt-pill-btn is-primary is-sm" onclick="window.print()">
                                <i class="fa fa-print fa-xs"></i>
                                <span>طباعة التقفيلات السنوية</span>
                            </button>
                        </div>
                    </div>

                    {{-- كروت ملخص التقفيلات السنوية --}}
                    <div class="p-4 pb-3 no-print" style="background: #f8fbfc; border-bottom: 1px solid var(--p-hairline);">
                        <div class="row g-3">
                            <div class="col-xl-3 col-md-6">
                                <div class="prt-kpi-card is-blue">
                                    <div class="prt-kpi-top">
                                        <div class="kpi-lbl">إجمالي رأس المال التأسيسي (ثابت)</div>
                                        <span class="prt-kpi-ico"><i class="fa fa-lock"></i></span>
                                    </div>
                                    <div class="kpi-val"><span dir="ltr">{{ number_format($annualTotalFounding, 2) }}</span><span class="kpi-cur">ج</span></div>
                                    <div class="kpi-sub">محفوظ من بداية الشراكة ولا يتغير</div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="prt-kpi-card is-teal">
                                    <div class="prt-kpi-top">
                                        <div class="kpi-lbl">الرصيد المعتمد حالياً</div>
                                        <span class="prt-kpi-ico"><i class="fa fa-vault"></i></span>
                                    </div>
                                    <div class="kpi-val"><span dir="ltr">{{ number_format($annualTotalCurrent, 2) }}</span><span class="kpi-cur">ج</span></div>
                                    <div class="kpi-sub">إجمالي حصص الشركاء + الإدارة الآن</div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="prt-kpi-card {{ $annualTotalCumGain >= 0 ? 'is-emerald' : 'is-rose' }}">
                                    <div class="prt-kpi-top">
                                        <div class="kpi-lbl">إجمالي النمو منذ التأسيس</div>
                                        <span class="prt-kpi-ico"><i class="fa fa-arrow-trend-up"></i></span>
                                    </div>
                                    <div class="kpi-val {{ $annualTotalCumGain >= 0 ? 'is-success' : 'is-danger' }}">
                                        <span dir="ltr">{{ $annualTotalCumGain >= 0 ? '+' : '' }}{{ number_format($annualTotalCumGain, 2) }}</span><span class="kpi-cur">ج</span>
                                    </div>
                                    <div class="kpi-sub">نسبة النمو التراكمي: <strong>{{ $annualTotalCumPct >= 0 ? '+' : '' }}{{ number_format($annualTotalCumPct, 2) }}%</strong></div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="prt-kpi-card is-amber">
                                    <div class="prt-kpi-top">
                                        <div class="kpi-lbl">آخر سنة مالية مقفلة</div>
                                        <span class="prt-kpi-ico"><i class="fa fa-calendar-check"></i></span>
                                    </div>
                                    <div class="kpi-val">
                                        @if($latestAnnualClosing)
                                            سنة {{ $latestAnnualClosing->closing_year }}
                                        @else
                                            لا يوجد بعد
                                        @endif
                                    </div>
                                    <div class="kpi-sub">
                                        @if($latestAnnualClosing)
                                            إجمالي الإقفال: {{ number_format($latestAnnualClosing->total_closing_capital, 2) }} ج
                                        @else
                                            اضغط «إقفال سنة مالية جديدة» للبدء
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- المصفوفة المقارنة عبر السنوات (بدأ بكام ← 2026 ← 2027 ← 2028 ... ← الآن) --}}
                    <div class="prt-table-wrap">
                        <table class="prt-table" id="annualComparisonTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th style="min-width: 175px;">الشريك / الحساب</th>
                                    <th class="text-center th-soft-before">
                                        <i class="fa fa-lock fa-xs me-1"></i> رأس المال التأسيسي
                                        <div style="font-size: 0.67rem; font-weight: 500; opacity: 0.8;">(بدأ بكام - ثابت)</div>
                                    </th>
                                    @foreach($annualYears as $yr)
                                        <th class="text-center" style="min-width: 145px;">
                                            <span>إقفال سنة {{ $yr }}</span>
                                            <div style="font-size: 0.67rem; font-weight: 500; opacity: 0.8;">الرصيد · الزيادة السنوية</div>
                                        </th>
                                    @endforeach
                                    <th class="text-center th-soft-after">الرصيد الحالي الآن</th>
                                    <th class="text-center">إجمالي النمو منذ التأسيس</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- صف حساب الإدارة --}}
                                @if($headPartner)
                                    @php
                                        $headFounding = round((float) ($headPartner->initial_capital ?? 0), 2);
                                        $headCurrent  = round((float) $ahmedCapital, 2);
                                        $headCumGain  = round($headCurrent - $headFounding, 2);
                                        $headCumPct   = $headFounding > 0 ? round(($headCumGain / $headFounding) * 100, 2) : null;
                                    @endphp
                                    <tr class="prt-row-head annual-searchable-row" data-search="{{ mb_strtolower($headName . ' حساب الإدارة') }}">
                                        <td class="text-center text-muted">—</td>
                                        <td>
                                            <a href="{{ route('partners.ahmed') }}" class="prt-name-link">{{ $headName }}</a>
                                            <span class="prt-chip is-head ms-1">حساب الإدارة</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="prt-num-main">{{ number_format($headFounding, 2) }}</span> <span class="text-muted small">ج</span>
                                            @if($headFounding == 0)
                                                <div class="prt-num-sub">تراكمي من النسب</div>
                                            @endif
                                        </td>
                                        @foreach($annualYears as $yr)
                                            @php $yl = $annualLinesByPartnerYr[$headPartner->id][$yr] ?? null; @endphp
                                            <td class="text-center">
                                                @if($yl)
                                                    <div class="prt-num-main">{{ number_format($yl->closing_capital, 2) }} <span class="text-muted small">ج</span></div>
                                                    <div class="mt-1">
                                                        <span class="prt-chip {{ $yl->annual_gain >= 0 ? 'is-success' : 'is-danger' }}" style="font-size: 0.69rem; padding: 1px 7px;">
                                                            {{ $yl->annual_gain >= 0 ? '+' : '' }}{{ number_format($yl->annual_gain, 2) }} ج
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center prt-num-main">{{ number_format($headCurrent, 2) }} <span class="text-muted small">ج</span></td>
                                        <td class="text-center">
                                            <span class="prt-chip {{ $headCumGain >= 0 ? 'is-success' : 'is-danger' }}">
                                                {{ $headCumGain >= 0 ? '+' : '' }}{{ number_format($headCumGain, 2) }} ج
                                            </span>
                                            @if($headCumPct !== null)
                                                <div class="prt-num-sub fw-semibold" style="color: {{ $headCumPct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                    {{ $headCumPct >= 0 ? '+' : '' }}{{ number_format($headCumPct, 2) }}%
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif

                                {{-- صفوف الشركاء --}}
                                @forelse($partners as $p)
                                    @php
                                        $pFounding = round((float) ($p->initial_capital ?? 0), 2);
                                        $pCurrent  = round((float) $p->capital, 2);
                                        $pCumGain  = round($pCurrent - $pFounding, 2);
                                        $pCumPct   = $pFounding > 0 ? round(($pCumGain / $pFounding) * 100, 2) : null;
                                    @endphp
                                    <tr class="annual-searchable-row {{ $p->status === 'exited' ? 'prt-row-exited' : '' }}" data-search="{{ mb_strtolower($p->name . ' ' . ($p->phone ?? '')) }}">
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('partners.show', $p->id) }}" class="prt-name-link">{{ $p->name }}</a>
                                            @if($p->status === 'exited')
                                                <span class="prt-chip is-danger ms-1" style="font-size: 0.66rem; padding: 1px 6px;">متخارج</span>
                                            @endif
                                            <div class="prt-meta-line">انضمام: {{ $p->joined_at ? date('Y-m-d', strtotime($p->joined_at)) : '—' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="prt-num-main">{{ number_format($pFounding, 2) }}</span> <span class="text-muted small">ج</span>
                                        </td>
                                        @foreach($annualYears as $yr)
                                            @php $yl = $annualLinesByPartnerYr[$p->id][$yr] ?? null; @endphp
                                            <td class="text-center">
                                                @if($yl)
                                                    <div class="prt-num-main">{{ number_format($yl->closing_capital, 2) }} <span class="text-muted small">ج</span></div>
                                                    <div class="mt-1 d-inline-flex align-items-center gap-1 flex-wrap justify-content-center">
                                                        <span class="prt-chip {{ $yl->annual_gain >= 0 ? 'is-success' : 'is-danger' }}" style="font-size: 0.69rem; padding: 1px 7px;">
                                                            {{ $yl->annual_gain >= 0 ? '+' : '' }}{{ number_format($yl->annual_gain, 2) }} ج
                                                        </span>
                                                        @if((float) $yl->opening_capital > 0)
                                                            <span class="prt-num-sub m-0" style="color: {{ $yl->annual_gain_pct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                                ({{ $yl->annual_gain_pct >= 0 ? '+' : '' }}{{ number_format($yl->annual_gain_pct, 1) }}%)
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted small">لم ينضم بعد</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center prt-num-main">{{ number_format($pCurrent, 2) }} <span class="text-muted small">ج</span></td>
                                        <td class="text-center">
                                            <span class="prt-chip {{ $pCumGain >= 0 ? 'is-success' : 'is-danger' }}">
                                                {{ $pCumGain >= 0 ? '+' : '' }}{{ number_format($pCumGain, 2) }} ج
                                            </span>
                                            @if($pCumPct !== null)
                                                <div class="prt-num-sub fw-semibold" style="color: {{ $pCumPct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                    {{ $pCumPct >= 0 ? '+' : '' }}{{ number_format($pCumPct, 2) }}%
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 5 + count($annualYears) }}" class="text-center py-5 text-muted">لا يوجد شركاء مسجلين بعد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end">الإجمالي الكلي:</td>
                                    <td class="text-center">{{ number_format($annualTotalFounding, 2) }} ج</td>
                                    @foreach($annualYears as $yr)
                                        @php $acObj = $annualClosings->firstWhere('closing_year', $yr); @endphp
                                        <td class="text-center">
                                            @if($acObj)
                                                <div>{{ number_format($acObj->total_closing_capital, 2) }} ج</div>
                                                <small style="color: {{ $acObj->total_annual_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                    {{ $acObj->total_annual_gain >= 0 ? '+' : '' }}{{ number_format($acObj->total_annual_gain, 2) }} ج
                                                </small>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center">{{ number_format($annualTotalCurrent, 2) }} ج</td>
                                    <td class="text-center" style="color: {{ $annualTotalCumGain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        {{ $annualTotalCumGain >= 0 ? '+' : '' }}{{ number_format($annualTotalCumGain, 2) }} ج
                                        <small class="d-block">({{ $annualTotalCumPct >= 0 ? '+' : '' }}{{ number_format($annualTotalCumPct, 2) }}%)</small>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- بطاقات تقارير السنوات المقفلة الفردية (للفتح والطباعة لكل سنة منفردة) --}}
                    @if($annualClosingsDesc->isNotEmpty())
                        <div class="p-4 border-top no-print" style="background: #f8fbfc;">
                            <h6 class="fw-bold mb-3" style="color: var(--c-navy);">
                                <i class="fa fa-folder-open me-1" style="color: var(--c-accent);"></i>
                                أرشيف تقارير التقفيلات السنوية (طباعة كل سنة منفردة)
                            </h6>
                            <div class="row g-3">
                                @foreach($annualClosingsDesc as $ac)
                                    @php
                                        $yrGainPct = (float) $ac->total_opening_capital > 0
                                            ? round(((float) $ac->total_annual_gain / (float) $ac->total_opening_capital) * 100, 2)
                                            : 0.0;
                                    @endphp
                                    <div class="col-xl-4 col-md-6">
                                        <div class="prt-kpi-card is-teal">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="prt-chip is-accent" style="font-size: 0.82rem;">
                                                        <i class="fa fa-calendar-check fa-xs"></i> إقفال سنة {{ $ac->closing_year }}
                                                    </span>
                                                    <span class="prt-meta-line m-0">{{ $ac->partners_count }} شريك وحساب</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                    <span class="small text-muted">رصيد نهاية {{ $ac->closing_year }}:</span>
                                                    <strong class="fs-5" style="color: var(--c-navy); font-variant-numeric: tabular-nums;">{{ number_format($ac->total_closing_capital, 2) }} ج</strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1 small">
                                                    <span class="text-muted">صافي النمو خلال السنة:</span>
                                                    <span class="fw-semibold" style="color: {{ $ac->total_annual_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                                        {{ $ac->total_annual_gain >= 0 ? '+' : '' }}{{ number_format($ac->total_annual_gain, 2) }} ج ({{ $yrGainPct >= 0 ? '+' : '' }}{{ number_format($yrGainPct, 1) }}%)
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1 small">
                                                    <span class="text-muted">أرباح الدورات الموزعة:</span>
                                                    <span class="fw-semibold" style="color: var(--c-info);">+{{ number_format($ac->total_year_profits, 2) }} ج</span>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                                <span class="prt-meta-line m-0">{{ $ac->closed_at ? date('Y-m-d', strtotime($ac->closed_at)) : '—' }}</span>
                                                <a href="{{ route('partners.annual.show', $ac->closing_year) }}" class="prt-pill-btn is-soft is-sm py-1 px-3">
                                                    <i class="fa fa-print fa-xs"></i>
                                                    <span>عرض وطباعة {{ $ac->closing_year }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ══ التاب 5: محاكي دخول شريك جديد ══ --}}
                @php
                    $simPartnersData = [];
                    $simPartnersData[] = [
                        'id' => 0,
                        'name' => $headName,
                        'is_head' => true,
                        'capital' => (float) $ahmedCapital,
                        'ahmed_ratio' => 0,
                        'capital_share_pct' => (float) $ahmedSharePct,
                    ];
                    foreach ($partners->where('status', 'active') as $ap) {
                        $capVal = (float) $ap->capital;
                        $pctVal = (float) ($ap->capital_share_pct ?? ($totalApprovedCapital > 0 ? round(($capVal / $totalApprovedCapital) * 100, 4) : 0));
                        $simPartnersData[] = [
                            'id' => $ap->id,
                            'name' => $ap->name,
                            'is_head' => false,
                            'capital' => $capVal,
                            'ahmed_ratio' => (float) ($ap->ahmed_ratio ?? 30.00),
                            'capital_share_pct' => $pctVal,
                        ];
                    }
                @endphp
                <div class="tab-pane fade {{ $activeMainTab === 'simulator' ? 'show active' : '' }} no-print" id="tabMainSimulator" role="tabpanel">
                    <div class="prt-panel-head">
                        <div>
                            <h5 class="prt-panel-title">
                                <span>محاكي دخول شريك جديد</span>
                                <span class="prt-chip is-warning">دراسة توقعية فقط</span>
                            </h5>
                            <p class="prt-panel-sub">جرب إدخال أي مبلغ مساهمة لشريك جديد وشاهد توزيع النسب تلقائياً دون أي تأثير على البيانات الحقيقية</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="prt-pill-btn is-ghost is-sm" onclick="resetSimulator()">
                                <i class="fa fa-rotate-left fa-xs"></i> إعادة ضبط
                            </button>
                            <button type="button" class="prt-pill-btn is-soft is-sm" onclick="printSimulationReport()">
                                <i class="fa fa-print fa-xs"></i> طباعة تقرير المحاكاة
                            </button>
                        </div>
                    </div>

                    <div class="prt-sim-body">
                        <div class="prt-sim-notice">
                            <i class="fa fa-info-circle"></i>
                            <div>
                                <strong>ملاحظة:</strong> هذه الشاشة للمحاكاة الافتراضية فقط لمساعدتك في حساب النسب قبل الاتفاق مع أي شريك جديد، ولن يتم حفظ أو تعديل أي بيانات فعلية.
                            </div>
                        </div>

                        {{-- إعدادات المحاكاة --}}
                        <div class="prt-sim-controls">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-pro-label">اسم الشريك المقترح</label>
                                    <input type="text" id="sim_partner_name" class="form-pro-control" value="شريك جديد (توقعي)" oninput="runPartnerSimulation()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-pro-label">مبلغ المساهمة برأس المال (ج)</label>
                                    <input type="number" step="1000" id="sim_capital" class="form-pro-control fw-semibold" value="100000" oninput="onSimCapitalInput()">
                                    <div class="d-flex gap-1 mt-2 flex-wrap">
                                        <button type="button" class="prt-sim-quick" onclick="setSimCapital(50000)">50 ألف</button>
                                        <button type="button" class="prt-sim-quick" onclick="setSimCapital(100000)">100 ألف</button>
                                        <button type="button" class="prt-sim-quick" onclick="setSimCapital(200000)">200 ألف</button>
                                        <button type="button" class="prt-sim-quick" onclick="setSimCapital(500000)">500 ألف</button>
                                        <button type="button" class="prt-sim-quick" onclick="setSimCapital(1000000)">مليون</button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-pro-label">أو حدد النسبة المستهدفة (%)</label>
                                    <input type="number" step="0.5" id="sim_target_pct" class="form-pro-control fw-semibold" placeholder="مثلاً: 10" oninput="onSimTargetPctInput()">
                                    <div class="d-flex gap-1 mt-2 flex-wrap">
                                        <button type="button" class="prt-sim-quick" onclick="setSimTargetPct(5)">5%</button>
                                        <button type="button" class="prt-sim-quick" onclick="setSimTargetPct(10)">10%</button>
                                        <button type="button" class="prt-sim-quick" onclick="setSimTargetPct(15)">15%</button>
                                        <button type="button" class="prt-sim-quick" onclick="setSimTargetPct(20)">20%</button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-pro-label">نسبة الإدارة (%)</label>
                                            <input type="number" step="1" id="sim_ahmed_ratio" class="form-pro-control" value="30" oninput="runPartnerSimulation()">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-pro-label">أرباح تجريبية (ج)</label>
                                            <input type="number" step="1000" id="sim_sample_profit" class="form-pro-control" value="100000" oninput="runPartnerSimulation()">
                                        </div>
                                    </div>
                                    <div class="prt-meta-line mt-2">لحساب توزيع الأرباح الافتراضية قبل وبعد</div>
                                </div>
                            </div>
                        </div>

                        {{-- كروت مقارنة سريعة --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="prt-sim-kpi">
                                    <div class="prt-meta-line m-0 mb-1">إجمالي رأس المال الجديد</div>
                                    <div class="fs-5 fw-bold" style="color: var(--c-navy); font-variant-numeric: tabular-nums;" id="sim_kpi_total_capital">0.00 ج</div>
                                    <div class="small mt-1 fw-semibold" style="color: var(--c-success);" id="sim_kpi_capital_increase">+0.00 ج زيادة</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="prt-sim-kpi is-highlight">
                                    <div class="small fw-semibold mb-1" style="color: var(--c-accent);">نسبة الشريك الجديد المقترحة</div>
                                    <div class="fs-4 fw-bold" style="color: var(--c-accent); font-variant-numeric: tabular-nums;" id="sim_kpi_new_partner_pct">0.00%</div>
                                    <div class="prt-meta-line m-0 mt-1">من إجمالي رأس مال الشركة</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="prt-sim-kpi">
                                    <div class="prt-meta-line m-0 mb-1">نسبة حساب الإدارة</div>
                                    <div class="fs-5 fw-bold" style="color: var(--c-navy); font-variant-numeric: tabular-nums;" id="sim_kpi_ahmed_pct">0.00%</div>
                                    <div class="small mt-1 text-muted" id="sim_kpi_ahmed_diff">كانت {{ number_format($ahmedSharePct, 2) }}%</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="prt-sim-kpi">
                                    <div class="prt-meta-line m-0 mb-1">نسبة باقي الشركاء مجتمعين</div>
                                    <div class="fs-5 fw-bold" style="color: var(--c-navy); font-variant-numeric: tabular-nums;" id="sim_kpi_partners_pct">0.00%</div>
                                    <div class="small mt-1 text-muted" id="sim_kpi_partners_diff">كانت {{ number_format($partnersSharePct, 2) }}%</div>
                                </div>
                            </div>
                        </div>

                        {{-- جدول المقارنة التفصيلي --}}
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3" style="color: var(--c-navy);">مقارنة حصص ونسب الشركاء (الوضع الحالي مقابل التوقعي)</h6>
                            <div class="prt-table-wrap border rounded-3">
                                <table class="prt-table" id="simComparisonTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 40px;">#</th>
                                            <th>الشريك / الحساب</th>
                                            <th class="text-center">رأس المال الحالي</th>
                                            <th class="text-center">رأس المال بعد الدخول</th>
                                            <th class="text-center">النسبة الحالية</th>
                                            <th class="text-center">النسبة التوقعية</th>
                                            <th class="text-center">التغير</th>
                                            <th class="text-center">ربح افتراضي (قبل)</th>
                                            <th class="text-center">ربح افتراضي (بعد)</th>
                                            <th class="text-center">فرق الربح</th>
                                        </tr>
                                    </thead>
                                    <tbody id="simTableBody"></tbody>
                                    <tfoot style="background: var(--c-surface-2); font-weight: 600; border-top: 1px solid var(--c-border);" id="simTableFoot"></tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- الرسوم البيانية --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="prt-sim-chart-box">
                                    <h6 class="fw-semibold mb-3" style="color: var(--c-text-muted); font-size: 0.88rem;">1. توزيع الحصص الحالي (قبل الدخول)</h6>
                                    <div style="height: 250px; position: relative;">
                                        <canvas id="simChartBefore"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="prt-sim-chart-box">
                                    <h6 class="fw-semibold mb-3" style="color: var(--c-accent); font-size: 0.88rem;">2. توزيع الحصص التوقعي (بعد دخول الشريك الجديد)</h6>
                                    <div style="height: 250px; position: relative;">
                                        <canvas id="simChartAfter"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- خانات التوقيع والاعتماد الرسمية للطباعة --}}
        <div class="print-only" style="margin-top: 20px; page-break-inside: avoid;">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">إعداد وتدقيق الحسابات</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">وحدة الحسابات والرقابة المالية</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">المدير المالي التنفيذي</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">الشؤون المالية والاستثمار</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">اعتماد الإدارة</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">{{ $headName }}</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">الاعتماد: ............................</div>
                    </div>
                </div>
            </div>
            <div class="text-center text-muted mt-2 pt-2 border-top" style="font-size:7pt;">
                سجل استثماري رسمي صادر عن النظام المالي لشركة الضبع · مستخرج بتاريخ {{ date('Y-m-d H:i') }}
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════
     1. MODAL: إضافة شريك جديد
     ══════════════════════════════════════════════ --}}
<div class="modal fade prt-modal" id="modalAddPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('partners.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon"><i class="fa fa-user-plus"></i></span>
                    <span>إضافة شريك جديد</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-pro info mb-3" style="font-size: 0.82rem;">
                    <i class="fa fa-info-circle"></i>
                    <span>عند إضافة شريك برأس مال، يتم إيداعه في الخزينة المحددة وتحديث نسب المساهمة وحفظ التغيير تلقائياً في سجل التاريخ.</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="prt-soft-box">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_head" value="1" id="addIsHead" onchange="toggleHeadFields('add', this.checked)">
                                <label class="form-check-label fw-semibold" for="addIsHead">تعيين هذا الحساب كحساب الإدارة (أرباح نسب الشغل)</label>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.76rem;">
                                تدخل فيه أرباح نسب الشغل مع الشركاء، وما لا يُسحب يُضاف لرصيده ليشارك في الدورة القادمة.
                                @if($headPartner)
                                    <br><strong style="color: var(--c-navy);">حساب الإدارة الحالي: {{ $headPartner->name }}</strong>
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-pro-label">اسم الشريك / الحساب <span class="text-danger">*</span></label>
                        <input type="text" name="name" data-unique-scope="partner" class="form-pro-control" placeholder="الاسم الكامل..." required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-pro-label">رقم الهاتف للتواصل</label>
                        <input type="text" name="phone" class="form-pro-control" placeholder="مثال: 010xxxxxxxx">
                    </div>

                    <div class="col-md-6">
                        <label class="form-pro-label">مبلغ رأس المال المودع (ج) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" name="capital" id="addCapitalInput" class="form-pro-control fw-semibold" placeholder="100000" required oninput="syncAddAccountRequired()">
                        <small class="text-muted d-none mt-1" id="addCapitalHeadHint" style="font-size: 0.74rem;">اختياري لحساب الإدارة: يبدأ رصيده من الأرباح المحصلة أو أي رصيد افتتاحي.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-pro-label">المحفظة / الخزينة المستلمة <span class="text-danger">*</span></label>
                        <select name="account_id" id="addAccountSelect" class="form-select form-pro-control" required>
                            <option value="">-- اختر المحفظة / الخزينة المستلمة --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6" id="addRatioWrap">
                        <label class="form-pro-label">نسبة الإدارة من أرباح الشريك (%) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" id="addRatioInput" class="form-control form-pro-control fw-semibold" value="30.00" required>
                            <span class="input-group-text bg-white">%</span>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">مثال: عند 30% يحصل الشريك على 70% من ربح حصته وتدخل 30% في حساب الإدارة.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-pro-label">المسحوب شهرياً (اختياري)</label>
                        <div class="input-group">
                            <input type="number" step="100" min="0" name="monthly_fixed_withdrawal" id="addMonthlyWithdrawal" class="form-control form-pro-control" placeholder="مثال: 1000 أو 3000">
                            <span class="input-group-text bg-white small">ج / شهر</span>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">يُدرج تلقائياً عند التقفيل إن وُجد مبلغ ثابت، أو اتركه فارغاً للسحب المتغير.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-pro-label">تاريخ الانضمام وبداية الشراكة</label>
                        <input type="date" name="joined_at" class="form-pro-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-pro-label">ملاحظات أو بنود الاتفاق</label>
                        <input type="text" name="notes" class="form-pro-control" placeholder="أي تفاصيل أو شروط خاصة بالاتفاق...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="prt-pill-btn is-primary is-sm px-4">
                    <i class="fa fa-check fa-xs"></i> حفظ وتوريد رأس المال
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     2. MODAL: زيادة رأس مال شريك حالي
     ══════════════════════════════════════════════ --}}
<div class="modal fade prt-modal" id="modalAddCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formAddCapital" action="" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon"><i class="fa fa-plus"></i></span>
                    <span>زيادة الرصيد / رأس المال</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="prt-soft-box mb-3">
                    <span class="small text-muted">إضافة مبلغ لحساب:</span>
                    <strong class="d-block mt-1" style="color: var(--c-navy); font-size: 1rem;" id="addCapPartnerName"></strong>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">المبلغ الإضافي المراد إيداعه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" id="addCapAmountInput" class="form-pro-control fw-semibold" placeholder="المبلغ..." required>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">المحفظة / الخزينة المستلمة <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-pro-control" required>
                        <option value="">-- اختر المحفظة / الخزينة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-pro-label">ملاحظات الإيداع</label>
                    <input type="text" name="notes" class="form-pro-control" placeholder="مثال: زيادة استثمارية...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="prt-pill-btn is-primary is-sm px-4">
                    <i class="fa fa-check fa-xs"></i> تأكيد الإيداع وتحديث النسب
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     3. MODAL: سحب من رأس مال الشريك
     ══════════════════════════════════════════════ --}}
<div class="modal fade prt-modal" id="modalWithdrawCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formWithdrawCapital" action="" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon is-warning"><i class="fa fa-minus"></i></span>
                    <span>سحب من الرصيد / رأس المال</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-pro danger mb-3" style="font-size: 0.82rem;">
                    <i class="fa fa-triangle-exclamation"></i>
                    <span>يُخصم هذا السحب مباشرة من الرصيد المعتمد في الحصص مع تحديث النسب فوراً وتسجيلها في سجل التاريخ.</span>
                </div>

                <div class="prt-soft-box mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="small text-muted d-block">الحساب / الشريك</span>
                        <strong style="color: var(--c-navy);" id="withCapPartnerName"></strong>
                    </div>
                    <div class="text-start">
                        <span class="small text-muted d-block">الرصيد المتاح</span>
                        <strong style="color: var(--c-navy); font-variant-numeric: tabular-nums;"><span id="withCapCurrentVal">0.00</span> ج</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">المبلغ المراد سحبه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" id="withCapAmountInput" class="form-pro-control fw-semibold" placeholder="المبلغ..." required>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">الخزينة المصروف منها المبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-pro-control" required>
                        <option value="">-- تحديد الخزينة للصرف --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-pro-label">ملاحظات السحب</label>
                    <input type="text" name="notes" class="form-pro-control" placeholder="سبب السحب...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn-pro btn-pro-danger rounded-pill px-4" style="font-size: 0.82rem;" onclick="return confirm('تأكيد نهائي: سيتم خصم المبلغ وتحديث نسب المساهمة وتسجيلها في الهيستوري. هل تريد المتابعة؟')">
                    <i class="fa fa-check fa-xs"></i> تأكيد وصرف المبلغ
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     4. MODAL: تعديل بيانات الشريك ونسبته
     ══════════════════════════════════════════════ --}}
<div class="modal fade prt-modal" id="modalEditPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditPartner" action="" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon"><i class="fa fa-pen"></i></span>
                    <span>تعديل البيانات والنسبة</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-pro-label">الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editPartnerName" class="form-pro-control" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-pro-label">رقم الهاتف</label>
                        <input type="text" name="phone" id="editPartnerPhone" class="form-pro-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-pro-label">تاريخ الانضمام</label>
                        <input type="date" name="joined_at" id="editPartnerJoinedAt" class="form-pro-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">
                        أصل رأس المال (رأس المال التأسيسي - ج)
                        <span class="prt-chip is-warning ms-1" style="font-size: 0.68rem; padding: 1px 8px;">
                            <i class="fa fa-lock fa-xs"></i> محفوظ ولا يُعدّل
                        </span>
                    </label>
                    <div class="prt-soft-box d-flex align-items-center justify-content-between py-2 px-3">
                        <strong id="editPartnerInitialCapitalDisplay" style="color: var(--c-navy); font-size: 1rem; font-variant-numeric: tabular-nums;">0.00 ج</strong>
                        <i class="fa fa-lock text-muted"></i>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">رأس المال التأسيسي ثابت ومحفوظ للمقارنة الدائمة مع التقفيلات السنوية ولا يمكن تغييره أو تعديله.</small>
                </div>

                <div class="mb-3" id="editRatioWrap">
                    <label class="form-pro-label">نسبة الإدارة من الربح (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" id="editPartnerAhmedRatio" class="form-control form-pro-control fw-semibold" required>
                        <span class="input-group-text bg-white">%</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">المسحوب شهرياً (اختياري)</label>
                    <div class="input-group">
                        <input type="number" step="100" min="0" name="monthly_fixed_withdrawal" id="editPartnerMonthlyWithdrawal" class="form-control form-pro-control" placeholder="مثال: 1000 أو 3000">
                        <span class="input-group-text bg-white small">ج / شهر</span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">يُدرج تلقائياً عند التقفيل الجزئي، أو اتركه فارغاً للسحب المتغير.</small>
                </div>

                <div class="prt-soft-box mb-3">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_head" value="1" id="editIsHead" onchange="toggleHeadFields('edit', this.checked)">
                        <label class="form-check-label fw-semibold" for="editIsHead">تعيين هذا الحساب كحساب الإدارة الرئيسي</label>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                        تدخل فيه أرباح نسب الشغل مع الشركاء، وما لا يُسحب يُضاف لرصيده ويأخذ حصته من الدورة القادمة.
                    </small>
                </div>

                <div>
                    <label class="form-pro-label">ملاحظات</label>
                    <input type="text" name="notes" id="editPartnerNotes" class="form-pro-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="prt-pill-btn is-primary is-sm px-4">
                    <i class="fa fa-check fa-xs"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     5. MODAL: تصفية وتخارج نهائي للشريك
     ══════════════════════════════════════════════ --}}
<div class="modal fade prt-modal" id="modalExitPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formExitPartner" action="" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon is-danger"><i class="fa fa-right-from-bracket"></i></span>
                    <span>تصفية وتخارج الشريك من النظام</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-pro danger mb-3" style="font-size: 0.82rem;">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span>يترتب على التخارج تصفية كامل رأس مال الشريك وصرفه من الخزينة المختارة وتحويل حالته إلى «متخارج» وتحديث نسب باقي الشركاء.</span>
                </div>

                <div class="prt-soft-box mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="small text-muted d-block">الشريك</span>
                        <strong style="color: var(--c-danger);" id="exitPartnerName"></strong>
                    </div>
                    <div class="text-start">
                        <span class="small text-muted d-block">الرصيد المستحق</span>
                        <strong style="color: var(--c-navy); font-variant-numeric: tabular-nums;"><span id="exitCapVal">0.00</span> ج</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-pro-label">الخزينة المصروف منها مستحقات التصفية <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-pro-control" required>
                        <option value="">-- تحديد الخزينة لصرف التخارج --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-pro-label">ملاحظات التصفية</label>
                    <input type="text" name="notes" class="form-pro-control" placeholder="تفاصيل اتفاق التخارج النهائي...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">تراجع</button>
                <button type="submit" class="btn-pro btn-pro-danger rounded-pill px-4" style="font-size: 0.82rem;" onclick="return confirm('تأكيد: هل أنت متأكد من تصفية وتخارج هذا الشريك وصرف مستحقاته من الخزينة؟')">
                    <i class="fa fa-check fa-xs"></i> اعتماد التصفية والتخارج
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     6. MODAL: إقفال السنة المالية وحفظ التقفيلة السنوية
     ══════════════════════════════════════════════ --}}
<div class="modal fade prt-modal" id="modalAnnualRollover" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('partners.annual.rollover') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">
                    <span class="prt-modal-icon is-warning"><i class="fa fa-calendar-check"></i></span>
                    <span>إقفال السنة المالية وحفظ التقفيلة السنوية</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert-pro info mb-3" style="font-size: 0.82rem;">
                    <i class="fa fa-lock"></i>
                    <span>عند اعتماد إقفال السنة، يتم تسجيل <strong>الرصيد الختامي لكل شريك</strong> ومقدار زيادته في تبويب <strong>«التقفيلات السنوية»</strong> للمقارنة عبر السنوات، مع بقاء <strong>رأس المال التأسيسي ثابتاً ومحفوظاً لا يتغير أبداً</strong>.</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-pro-label">السنة المالية المراد إقفالها <span class="text-danger">*</span></label>
                        <input type="number" name="closing_year" id="inpAnnualClosingYear" class="form-pro-control fw-semibold" value="{{ $nextSuggestedAnnualYear }}" min="2020" max="2099" required>
                        @if(!empty($annualYears))
                            <small class="text-muted d-block mt-1" style="font-size: 0.73rem;">السنوات المقفلة سابقاً: {{ implode('، ', $annualYears) }}</small>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <label class="form-pro-label">ملاحظات الإقفال السنوي (اختياري)</label>
                        <input type="text" name="notes" class="form-pro-control" placeholder="مثال: إقفال السنة المالية واعتماد أرصدة نهاية العام...">
                    </div>
                </div>

                <div class="prt-table-wrap border rounded-3" style="max-height: 310px; overflow-y: auto;">
                    <table class="prt-table prt-subtable">
                        <thead>
                            <tr>
                                <th>الشريك / الحساب</th>
                                <th class="text-center"><i class="fa fa-lock fa-xs me-1"></i> التأسيسي (ثابت)</th>
                                <th class="text-center">بداية السنة</th>
                                <th class="text-center">رصيد نهاية السنة</th>
                                <th class="text-center">التغير السنوي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($headPartner)
                                @php
                                    $hInit = round((float) ($headPartner->initial_capital ?? 0), 2);
                                    $hOpen = $prevClosingByPartner[$headPartner->id] ?? $hInit;
                                    $hEnd  = round((float) $headPartner->capital, 2);
                                    $hDiff = round($hEnd - $hOpen, 2);
                                @endphp
                                <tr class="prt-row-head">
                                    <td class="fw-semibold" style="color: var(--c-navy);">
                                        {{ $headName }}
                                        <span class="prt-chip is-head ms-1">حساب الإدارة</span>
                                    </td>
                                    <td class="text-center text-muted">{{ number_format($hInit, 2) }} ج</td>
                                    <td class="text-center text-muted">{{ number_format($hOpen, 2) }} ج</td>
                                    <td class="text-center fw-semibold" style="color: var(--c-navy);">{{ number_format($hEnd, 2) }} ج</td>
                                    <td class="text-center fw-semibold" style="color: {{ $hDiff >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        {{ $hDiff >= 0 ? '+' : '' }}{{ number_format($hDiff, 2) }} ج
                                    </td>
                                </tr>
                            @endif
                            @foreach($activePartners as $ap)
                                @php
                                    $aInit = round((float) ($ap->initial_capital ?? 0), 2);
                                    $aOpen = $prevClosingByPartner[$ap->id] ?? $aInit;
                                    $aEnd  = round((float) $ap->capital, 2);
                                    $aDiff = round($aEnd - $aOpen, 2);
                                @endphp
                                <tr>
                                    <td class="fw-semibold" style="color: var(--c-navy);">{{ $ap->name }}</td>
                                    <td class="text-center text-muted">{{ number_format($aInit, 2) }} ج</td>
                                    <td class="text-center text-muted">{{ number_format($aOpen, 2) }} ج</td>
                                    <td class="text-center fw-semibold" style="color: var(--c-navy);">{{ number_format($aEnd, 2) }} ج</td>
                                    <td class="text-center fw-semibold" style="color: {{ $aDiff >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                        {{ $aDiff >= 0 ? '+' : '' }}{{ number_format($aDiff, 2) }} ج
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prt-pill-btn is-ghost is-sm" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="prt-pill-btn is-primary is-sm px-4">
                    <i class="fa fa-check-double fa-xs"></i> اعتماد وحفظ إقفال السنة المالية
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
    // ── البحث الفوري في جدول الشركاء ──
    function filterPartnersTable() {
        const input = document.getElementById('partnerSearchInput');
        const clearBtn = document.getElementById('partnerSearchClear');
        const emptyRow = document.getElementById('partnerSearchEmptyRow');
        const countEl = document.getElementById('partnerSearchCount');
        if (!input) return;

        const q = input.value.trim().toLowerCase();
        if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';

        const rows = document.querySelectorAll('#partnersRegisterTable tbody tr.partner-searchable-row');
        let visible = 0;
        rows.forEach(row => {
            const hay = (row.getAttribute('data-search') || row.textContent || '').toLowerCase();
            const match = !q || hay.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (emptyRow) emptyRow.classList.toggle('d-none', visible > 0 || !q);
        if (countEl) countEl.textContent = q ? ('النتائج المطابقة: ' + visible) : '';
    }

    function clearPartnerSearch() {
        const input = document.getElementById('partnerSearchInput');
        if (input) {
            input.value = '';
            input.focus();
            filterPartnersTable();
        }
    }

    function filterAnnualTable() {
        const input = document.getElementById('annualSearchInput');
        const clearBtn = document.getElementById('annualSearchClear');
        if (!input) return;
        const q = input.value.trim().toLowerCase();
        if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';
        document.querySelectorAll('#annualComparisonTable tbody tr.annual-searchable-row').forEach(row => {
            const hay = (row.getAttribute('data-search') || row.textContent || '').toLowerCase();
            row.style.display = (!q || hay.includes(q)) ? '' : 'none';
        });
    }

    function clearAnnualSearch() {
        const input = document.getElementById('annualSearchInput');
        if (input) {
            input.value = '';
            input.focus();
            filterAnnualTable();
        }
    }

    function openAddCapitalModal(id, name, defaultAmount = '') {
        document.getElementById('formAddCapital').action = '/partners/' + id + '/add-capital';
        document.getElementById('addCapPartnerName').innerText = name;
        const amtInput = document.getElementById('addCapAmountInput');
        if (amtInput) {
            amtInput.value = defaultAmount !== '' ? defaultAmount : '';
        }
        new bootstrap.Modal(document.getElementById('modalAddCapital')).show();
    }

    function openWithdrawCapitalModal(id, name, capital) {
        const doOpen = () => {
            document.getElementById('formWithdrawCapital').action = '/partners/' + id + '/withdraw-capital';
            document.getElementById('withCapPartnerName').innerText = name;
            document.getElementById('withCapCurrentVal').innerText = Number(capital).toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('withCapAmountInput').max = capital;
            new bootstrap.Modal(document.getElementById('modalWithdrawCapital')).show();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'تنبيه سحب من الرصيد',
                html: '<div style="font-size:0.95rem;line-height:1.75;color:#1a1f2e;" class="text-center">' +
                      'هذا سحب من رصيد <strong style="color:#b91c1c;">(' + name + ')</strong><br>' +
                      'سيتم تحديث نسب المساهمة وتسجيل النسب قبل وبعد السحب في سجل التاريخ.<br><br>' +
                      '<small style="color:#5a6478;">هل تريد المتابعة وفتح نموذج السحب؟</small>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'متابعة السحب',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    doOpen();
                }
            });
        } else {
            if (confirm('تنبيه هام:\nهذا سحب من الرصيد (' + name + ') وسيؤثر على النسب ويُسجل في التاريخ.\n\nهل تريد المتابعة؟')) {
                doOpen();
            }
        }
    }

    function openEditPartnerModal(p) {
        document.getElementById('formEditPartner').action = '/partners/' + p.id + '/update';
        document.getElementById('editPartnerName').value = p.name || '';
        document.getElementById('editPartnerPhone').value = p.phone || '';
        const joinedDate = p.joined_at ? String(p.joined_at).substring(0, 10) : '';
        const joinedEl = document.getElementById('editPartnerJoinedAt');
        if (joinedEl) joinedEl.value = joinedDate;
        const initCapDisp = document.getElementById('editPartnerInitialCapitalDisplay');
        if (initCapDisp) {
            const initVal = (p.initial_capital !== null && p.initial_capital !== undefined) ? Number(p.initial_capital) : 0;
            initCapDisp.innerText = initVal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';
        }
        document.getElementById('editPartnerAhmedRatio').value = p.ahmed_ratio || 30.00;
        document.getElementById('editPartnerMonthlyWithdrawal').value = p.monthly_fixed_withdrawal || '';
        document.getElementById('editPartnerNotes').value = p.notes || '';
        document.getElementById('editIsHead').checked = !!Number(p.is_head);
        toggleHeadFields('edit', !!Number(p.is_head));
        new bootstrap.Modal(document.getElementById('modalEditPartner')).show();
    }

    function syncAddAccountRequired() {
        const isHead = document.getElementById('addIsHead')?.checked;
        const capInput = document.getElementById('addCapitalInput');
        const accSelect = document.getElementById('addAccountSelect');
        if (!accSelect) return;
        const capVal = parseFloat(capInput?.value) || 0;
        accSelect.required = !isHead || capVal > 0;
    }

    function toggleHeadFields(prefix, isHead) {
        const ratioWrap = document.getElementById(prefix + 'RatioWrap');
        const ratioInput = prefix === 'add' ? document.getElementById('addRatioInput') : document.getElementById('editPartnerAhmedRatio');
        if (ratioWrap) ratioWrap.style.display = isHead ? 'none' : '';
        if (ratioInput) ratioInput.required = !isHead;

        if (prefix === 'add') {
            const capInput = document.getElementById('addCapitalInput');
            const hint = document.getElementById('addCapitalHeadHint');
            if (capInput) {
                capInput.required = !isHead;
                capInput.min = isHead ? '0' : '1';
            }
            if (hint) hint.classList.toggle('d-none', !isHead);
            syncAddAccountRequired();
        }
    }

    function openExitPartnerModal(id, name, capital) {
        document.getElementById('formExitPartner').action = '/partners/' + id + '/exit';
        document.getElementById('exitPartnerName').innerText = name;
        document.getElementById('exitCapVal').innerText = Number(capital).toLocaleString('en-US', {minimumFractionDigits: 2});
        new bootstrap.Modal(document.getElementById('modalExitPartner')).show();
    }

    function toggleAllHistoryCollapses(shouldOpen) {
        document.querySelectorAll('.hist-collapse-box').forEach(el => {
            const instance = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
            if (shouldOpen) {
                instance.show();
            } else {
                instance.hide();
            }
        });
    }

    // ══════════════════════════════════════════════════════════════
    // محاكي دخول شريك جديد (تجريبي وتوقعي) - Partner Simulator
    // ══════════════════════════════════════════════════════════════
    const simBasePartners = {!! json_encode($simPartnersData) !!};
    const simOldTotalCapital = {{ (float) $totalApprovedCapital }};
    let simChartBeforeInst = null;
    let simChartAfterInst = null;

    function setSimCapital(amount) {
        document.getElementById('sim_capital').value = amount;
        document.getElementById('sim_target_pct').value = '';
        runPartnerSimulation();
    }

    function onSimCapitalInput() {
        document.getElementById('sim_target_pct').value = '';
        runPartnerSimulation();
    }

    function setSimTargetPct(pct) {
        document.getElementById('sim_target_pct').value = pct;
        onSimTargetPctInput();
    }

    function onSimTargetPctInput() {
        const targetPct = parseFloat(document.getElementById('sim_target_pct').value) || 0;
        if (targetPct > 0 && targetPct < 100 && simOldTotalCapital > 0) {
            const calculatedCap = Math.round(simOldTotalCapital * (targetPct / (100 - targetPct)));
            document.getElementById('sim_capital').value = calculatedCap;
        }
        runPartnerSimulation();
    }

    function resetSimulator() {
        document.getElementById('sim_partner_name').value = 'شريك جديد (توقعي)';
        document.getElementById('sim_capital').value = '100000';
        document.getElementById('sim_target_pct').value = '';
        document.getElementById('sim_ahmed_ratio').value = '30';
        document.getElementById('sim_sample_profit').value = '100000';
        runPartnerSimulation();
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function runPartnerSimulation() {
        const partnerName = document.getElementById('sim_partner_name').value.trim() || 'شريك جديد';
        const newCap = Math.max(0, parseFloat(document.getElementById('sim_capital').value) || 0);
        const newAhmedRatio = Math.max(0, Math.min(100, parseFloat(document.getElementById('sim_ahmed_ratio').value) || 30));
        const sampleProfit = Math.max(0, parseFloat(document.getElementById('sim_sample_profit').value) || 0);

        const newTotalCapital = simOldTotalCapital + newCap;
        const newPartnerSharePct = newTotalCapital > 0 ? (newCap / newTotalCapital) * 100 : 0;

        document.getElementById('sim_kpi_total_capital').innerText = newTotalCapital.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';
        document.getElementById('sim_kpi_capital_increase').innerText = '+' + newCap.toLocaleString('en-US') + ' ج مساهمة جديدة';
        document.getElementById('sim_kpi_new_partner_pct').innerText = newPartnerSharePct.toFixed(2) + '%';

        let newPartnersList = [];
        let ahmedNewSharePct = 0;
        let partnersNewTotalSharePct = 0;

        simBasePartners.forEach(p => {
            const cap = p.capital;
            const oldSharePct = simOldTotalCapital > 0 ? (cap / simOldTotalCapital) * 100 : 0;
            const newSharePct = newTotalCapital > 0 ? (cap / newTotalCapital) * 100 : 0;
            const shareDiff = newSharePct - oldSharePct;

            if (p.is_head) {
                ahmedNewSharePct = newSharePct;
            } else {
                partnersNewTotalSharePct += newSharePct;
            }

            newPartnersList.push({
                ...p,
                old_cap: cap,
                new_cap: cap,
                old_share_pct: oldSharePct,
                new_share_pct: newSharePct,
                share_diff: shareDiff,
                ahmed_ratio: p.ahmed_ratio,
            });
        });

        newPartnersList.push({
            id: -1,
            name: partnerName,
            is_head: false,
            is_new: true,
            old_cap: 0,
            new_cap: newCap,
            old_share_pct: 0,
            new_share_pct: newPartnerSharePct,
            share_diff: newPartnerSharePct,
            ahmed_ratio: newAhmedRatio,
        });

        const ahmedOldPct = {{ (float) $ahmedSharePct }};
        const ahmedDiff = ahmedNewSharePct - ahmedOldPct;
        document.getElementById('sim_kpi_ahmed_pct').innerText = ahmedNewSharePct.toFixed(2) + '%';
        document.getElementById('sim_kpi_ahmed_diff').innerHTML = `كانت ${ahmedOldPct.toFixed(2)}% (<span style="color:${ahmedDiff >= 0 ? 'var(--c-success)' : 'var(--c-danger)'}">${ahmedDiff >= 0 ? '+' : ''}${ahmedDiff.toFixed(2)}%</span>)`;

        const partnersOldPct = {{ (float) $partnersSharePct }};
        const partnersDiff = partnersNewTotalSharePct - partnersOldPct;
        document.getElementById('sim_kpi_partners_pct').innerText = partnersNewTotalSharePct.toFixed(2) + '%';
        document.getElementById('sim_kpi_partners_diff').innerHTML = `كانت ${partnersOldPct.toFixed(2)}% (<span style="color:${partnersDiff >= 0 ? 'var(--c-success)' : 'var(--c-danger)'}">${partnersDiff >= 0 ? '+' : ''}${partnersDiff.toFixed(2)}%</span>)`;

        let totalCommissionsNew = 0;
        let totalCommissionsOld = 0;

        newPartnersList.forEach(p => {
            if (!p.is_head) {
                const commNew = (p.new_share_pct * (p.ahmed_ratio / 100));
                const commOld = (p.old_share_pct * (p.ahmed_ratio / 100));
                totalCommissionsNew += commNew;
                totalCommissionsOld += commOld;

                p.net_profit_pct_new = p.new_share_pct - commNew;
                p.net_profit_pct_old = p.old_share_pct - commOld;
            }
        });

        const ahmedPartner = newPartnersList.find(p => p.is_head);
        if (ahmedPartner) {
            ahmedPartner.net_profit_pct_new = ahmedPartner.new_share_pct + totalCommissionsNew;
            ahmedPartner.net_profit_pct_old = ahmedPartner.old_share_pct + totalCommissionsOld;
        }

        const tbody = document.getElementById('simTableBody');
        tbody.innerHTML = '';

        newPartnersList.forEach((p, idx) => {
            const tr = document.createElement('tr');
            if (p.is_new) {
                tr.className = 'prt-sim-row-new';
            } else if (p.is_head) {
                tr.className = 'prt-row-head';
            }

            const diffColor = p.share_diff > 0.001 ? 'var(--c-success)' : (p.share_diff < -0.001 ? 'var(--c-danger)' : 'var(--c-text-soft)');
            const diffSign = p.share_diff > 0.001 ? '+' : '';

            const profitOld = (p.net_profit_pct_old * sampleProfit) / 100;
            const profitNew = (p.net_profit_pct_new * sampleProfit) / 100;
            const profitDiff = profitNew - profitOld;
            const pDiffColor = profitDiff > 0.001 ? 'var(--c-success)' : (profitDiff < -0.001 ? 'var(--c-danger)' : 'var(--c-text-soft)');
            const pDiffSign = profitDiff > 0.001 ? '+' : '';

            tr.innerHTML = `
                <td class="text-center text-muted">${idx + 1}</td>
                <td>
                    <span class="fw-semibold" style="color:${p.is_new ? 'var(--c-accent)' : 'var(--c-navy)'};">${escapeHtml(p.name)}</span>
                    ${p.is_head ? '<span class="prt-chip is-head ms-1">حساب الإدارة</span>' : ''}
                    ${p.is_new ? '<span class="prt-chip is-accent ms-1">الشريك الجديد</span>' : ''}
                    ${!p.is_head ? `<div class="prt-num-sub">نسبة الإدارة: ${p.ahmed_ratio}%</div>` : ''}
                </td>
                <td class="text-center text-muted">${p.is_new ? '—' : Number(p.old_cap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج'}</td>
                <td class="text-center fw-semibold" style="color:${p.is_new ? 'var(--c-accent)' : 'var(--c-navy)'};">${Number(p.new_cap).toLocaleString('en-US', {minimumFractionDigits: 2})} ج</td>
                <td class="text-center text-muted">${p.is_new ? '—' : p.old_share_pct.toFixed(2) + '%'}</td>
                <td class="text-center"><span class="prt-chip ${p.is_new ? 'is-accent' : 'is-info'}">${p.new_share_pct.toFixed(2)}%</span></td>
                <td class="text-center fw-semibold" style="color:${diffColor};">${diffSign}${p.share_diff.toFixed(2)}%</td>
                <td class="text-center text-muted">${p.is_new ? '—' : Number(profitOld).toLocaleString('en-US', {maximumFractionDigits: 0}) + ' ج'}</td>
                <td class="text-center fw-semibold" style="color:var(--c-success);">${Number(profitNew).toLocaleString('en-US', {maximumFractionDigits: 0})} ج</td>
                <td class="text-center fw-semibold" style="color:${pDiffColor};">${pDiffSign}${Number(profitDiff).toLocaleString('en-US', {maximumFractionDigits: 0})} ج</td>
            `;
            tbody.appendChild(tr);
        });

        const tfoot = document.getElementById('simTableFoot');
        tfoot.innerHTML = `
            <tr>
                <td colspan="2" class="text-end py-3 px-3">الإجمالي:</td>
                <td class="text-center text-muted">${Number(simOldTotalCapital).toLocaleString('en-US', {minimumFractionDigits: 2})} ج</td>
                <td class="text-center" style="color:var(--c-navy);">${Number(newTotalCapital).toLocaleString('en-US', {minimumFractionDigits: 2})} ج</td>
                <td class="text-center text-muted">100.00%</td>
                <td class="text-center" style="color:var(--c-navy);">100.00%</td>
                <td class="text-center text-muted">0.00%</td>
                <td class="text-center text-muted">${Number(sampleProfit).toLocaleString('en-US')} ج</td>
                <td class="text-center" style="color:var(--c-success);">${Number(sampleProfit).toLocaleString('en-US')} ج</td>
                <td class="text-center text-muted">0 ج</td>
            </tr>
        `;

        updateSimCharts(newPartnersList);
    }

    function updateSimCharts(partnersList) {
        const labelsBefore = [];
        const dataBefore = [];
        // باليت ألوان هادئة متناسقة مع النظام
        const calmPalette = [
            '#1a3a5f', '#2d8659', '#3b7ea1', '#b67c1f', '#5a6b8c',
            '#4f8a8b', '#7c6f9f', '#8c7a5b', '#4d7c6f', '#64748b'
        ];

        simBasePartners.forEach(p => {
            labelsBefore.push(p.name);
            dataBefore.push(Number(p.capital_share_pct.toFixed(2)));
        });

        const labelsAfter = [];
        const dataAfter = [];
        const colorsAfter = [];

        const themeAccent = getComputedStyle(document.documentElement).getPropertyValue('--c-accent').trim() || '#0d7373';
        partnersList.forEach((p, idx) => {
            labelsAfter.push(p.name);
            dataAfter.push(Number(p.new_share_pct.toFixed(2)));
            if (p.is_new) {
                colorsAfter.push(themeAccent);
            } else if (p.is_head) {
                colorsAfter.push('#1a3a5f');
            } else {
                colorsAfter.push(calmPalette[(idx % (calmPalette.length - 1)) + 1]);
            }
        });

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    rtl: true,
                    labels: {
                        font: { family: 'IBM Plex Sans Arabic', size: 11 },
                        boxWidth: 10,
                        usePointStyle: true,
                        color: '#5a6478'
                    }
                },
                tooltip: {
                    rtl: true,
                    callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}%` }
                }
            },
            cutout: '64%'
        };

        const ctxBefore = document.getElementById('simChartBefore');
        if (ctxBefore) {
            if (simChartBeforeInst) simChartBeforeInst.destroy();
            simChartBeforeInst = new Chart(ctxBefore.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labelsBefore,
                    datasets: [{
                        data: dataBefore,
                        backgroundColor: calmPalette.slice(0, labelsBefore.length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: chartOptions
            });
        }

        const ctxAfter = document.getElementById('simChartAfter');
        if (ctxAfter) {
            if (simChartAfterInst) simChartAfterInst.destroy();
            simChartAfterInst = new Chart(ctxAfter.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labelsAfter,
                    datasets: [{
                        data: dataAfter,
                        backgroundColor: colorsAfter,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: chartOptions
            });
        }
    }

    function syncPrintDocTitle() {
        const titleEl = document.getElementById('printDocTitle');
        if (!titleEl) return;
        const annualPane = document.getElementById('tabMainAnnual');
        const historyPane = document.getElementById('tabMainHistory');
        const periodsPane = document.getElementById('tabMainPeriods');
        if (annualPane && annualPane.classList.contains('active')) {
            titleEl.textContent = 'سجل التقفيلات السنوية ونمو رؤوس أموال الشركاء';
        } else if (historyPane && historyPane.classList.contains('active')) {
            titleEl.textContent = 'سجل تاريخ تغيّر نسب وحصص الشركاء';
        } else if (periodsPane && periodsPane.classList.contains('active')) {
            titleEl.textContent = 'أرشيف التقفيلات الشهرية وتوزيع الأرباح';
        } else {
            titleEl.textContent = 'سجل الشركاء والحصص المعتمدة';
        }
    }

    window.addEventListener('beforeprint', syncPrintDocTitle);

    function printSimulationReport() {
        syncPrintDocTitle();
        window.print();
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('sim_capital')) {
            runPartnerSimulation();
        }

        document.querySelectorAll('.tab-btn-simulator').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function () {
                if (simChartBeforeInst) simChartBeforeInst.resize();
                if (simChartAfterInst) simChartAfterInst.resize();
            });
        });
    });
</script>
</body>
</html>
