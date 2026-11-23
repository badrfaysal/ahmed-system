<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير إقفال الدورة: {{ $period->period_name }} - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    @include('partials.theme', ['accent' => 'teal'])
    @include('partners._calm_theme')

    <style>
        .prd-kpi-val {
            font-size: 1.22rem;
            font-weight: 700;
            color: var(--c-navy);
            line-height: 1.25;
            font-variant-numeric: tabular-nums;
        }
        .print-only { display: none; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 5mm 10mm 5mm;
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
                font-size: 7.2pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop,
            .no-print, th.no-print, td.no-print, button, .btn, .prt-pill-btn, .prt-back-link,
            .prt-panel-foot, .modal, .pagination {
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
            .print-only { display: block !important; }
            .prt-panel {
                border: none !important;
                border-radius: 0 !important;
                background: transparent !important;
                margin-bottom: 8px !important;
            }
            .prt-panel-head {
                background: #f8fafc !important;
                border: 1px solid #94a3b8 !important;
                padding: 5px 8px !important;
                margin-bottom: 4px !important;
            }
            .prt-table-wrap { overflow: visible !important; }
            .prt-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 6.8pt !important;
                margin-bottom: 6px !important;
            }
            .prt-table thead { display: table-header-group !important; }
            .prt-table thead th {
                background-color: #0d1f35 !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
                padding: 4px 2px !important;
                font-size: 6.3pt !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .prt-table tbody td {
                border: 1px solid #94a3b8 !important;
                padding: 3px 2px !important;
                font-size: 6.8pt !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .prt-table tr { page-break-inside: avoid !important; }
            .prt-chip, .badge {
                border: 1px solid #475569 !important;
                color: #000000 !important;
                background: #f1f5f9 !important;
                padding: 1px 3px !important;
                font-size: 6.2pt !important;
            }
            .print-kpi-grid {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 5px !important;
                margin-bottom: 8px !important;
            }
            .print-kpi-box {
                border: 1px solid #cbd5e1 !important;
                padding: 5px 6px !important;
                background: #f8fafc !important;
                text-align: center !important;
                border-radius: 4px !important;
            }
            .print-kpi-box .lbl { font-size: 6.8pt !important; color: #475569 !important; font-weight: 700 !important; margin-bottom: 2px !important; }
            .print-kpi-box .num { font-size: 9pt !important; font-weight: 800 !important; color: #0f172a !important; }
            .print-signatures-block { margin-top: 14px !important; page-break-inside: avoid !important; }
            .print-sig-col {
                border: 1px dashed #64748b !important;
                padding: 8px !important;
                text-align: center !important;
                background: #ffffff !important;
                border-radius: 4px !important;
            }
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid px-0">

        {{-- الترويسة المطبوعة الرسمية (تظهر فقط عند الطباعة) --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">وحدة الحسابات والرقابة المالية وإدارة الحصص</small>
                    <span class="badge bg-dark text-white mt-1">كود الوثيقة: PRD-{{ str_pad($period->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">تقرير اعتماد إقفال الدورة وتوزيع الأرباح وتغير النسب</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">{{ $period->period_name }}</div>
                    <small class="text-muted">الفترة المحاسبية: من <strong>{{ $period->start_date }}</strong> إلى <strong>{{ $period->end_date }}</strong></small>
                </div>
                <div class="text-start">
                    <div class="badge bg-success text-white px-2 py-1 mb-1">معتمدة ومرحلة بالدفاتر</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الاعتماد: {{ date('Y-m-d H:i', strtotime($period->created_at)) }}</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- الترويسة والشاشة والتحكم --}}
        <div class="prt-header no-print">
            <div>
                <a href="{{ route('partners.index') }}" class="prt-back-link">
                    <i class="fa fa-arrow-right fa-xs"></i>
                    <span>العودة إلى سجل الشركاء</span>
                </a>
                <div class="prt-title-row">
                    <span class="prt-title-badge"><i class="fa fa-file-invoice-dollar"></i></span>
                    <h1 class="prt-title">تقرير إقفال الدورة: {{ $period->period_name }}</h1>
                    <span class="prt-chip is-success">معتمدة ومغلقة</span>
                </div>
                <p class="prt-subtitle">
                    الفترة من <strong>{{ $period->start_date }}</strong> إلى <strong>{{ $period->end_date }}</strong>
                    · تم الاعتماد في: {{ date('Y-m-d H:i', strtotime($period->created_at)) }}
                </p>
            </div>
            <div class="prt-actions align-self-end">
                <button type="button" class="prt-pill-btn is-primary" onclick="window.print()">
                    <i class="fa fa-print fa-xs"></i>
                    <span>طباعة التقرير الرسمي (A4)</span>
                </button>
            </div>
        </div>

        {{-- ملخص المؤشرات للشاشة --}}
        <div class="row g-3 mb-4 no-print">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="prt-kpi-card is-blue">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">رأس مال البداية</div>
                        <span class="prt-kpi-ico"><i class="fa fa-vault"></i></span>
                    </div>
                    <div class="prd-kpi-val"><span dir="ltr">{{ number_format($period->opening_capital, 2) }}</span> <span class="kpi-cur">ج</span></div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="prt-kpi-card is-teal">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">رأس مال الإقفال</div>
                        <span class="prt-kpi-ico"><i class="fa fa-building"></i></span>
                    </div>
                    <div class="prd-kpi-val"><span dir="ltr">{{ number_format($period->closing_capital, 2) }}</span> <span class="kpi-cur">ج</span></div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="prt-kpi-card {{ $period->gross_profit >= 0 ? 'is-emerald' : 'is-rose' }}">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">الربح التشغيلي (R)</div>
                        <span class="prt-kpi-ico"><i class="fa fa-calculator"></i></span>
                    </div>
                    <div class="prd-kpi-val" style="color: {{ $period->gross_profit >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                        <span dir="ltr">{{ $period->gross_profit >= 0 ? '+' : '' }}{{ number_format($period->gross_profit, 2) }}</span> <span class="kpi-cur">ج</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="prt-kpi-card is-indigo">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">صافي ربح الشركاء</div>
                        <span class="prt-kpi-ico"><i class="fa fa-users"></i></span>
                    </div>
                    <div class="prd-kpi-val"><span dir="ltr">{{ number_format($period->total_partners_net_profit, 2) }}</span> <span class="kpi-cur">ج</span></div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="prt-kpi-card is-amber">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">ربح حساب الإدارة</div>
                        <span class="prt-kpi-ico"><i class="fa fa-user-tie"></i></span>
                    </div>
                    <div class="prd-kpi-val" style="color: var(--c-accent);">+<span dir="ltr">{{ number_format($period->ahmed_total_profit ?: $period->total_ahmed_commission, 2) }}</span> <span class="kpi-cur">ج</span></div>
                    <div class="kpi-sub">نسب الشغل: {{ number_format($period->total_ahmed_commission, 2) }} ج</div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="prt-kpi-card is-emerald">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">المدوّر بالحسابات</div>
                        <span class="prt-kpi-ico"><i class="fa fa-sync-alt"></i></span>
                    </div>
                    <div class="prd-kpi-val" style="color: var(--c-success);"><span dir="ltr">{{ number_format($period->total_reinvested, 2) }}</span> <span class="kpi-cur">ج</span></div>
                    <div class="kpi-sub" style="color: var(--c-danger);">مسحوب نقداً: {{ number_format($period->total_withdrawn, 2) }} ج</div>
                </div>
            </div>
        </div>

        {{-- ملخص المؤشرات المطبوع --}}
        <div class="print-only">
            <div class="print-kpi-grid">
                <div class="print-kpi-box">
                    <div class="lbl">1. رأس مال البداية</div>
                    <div class="num">{{ number_format($period->opening_capital, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">2. رأس مال الإقفال</div>
                    <div class="num">{{ number_format($period->closing_capital, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">3. صافي الربح (R)</div>
                    <div class="num">{{ $period->gross_profit >= 0 ? '+' : '' }}{{ number_format($period->gross_profit, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">4. صافي ربح الشركاء</div>
                    <div class="num">{{ number_format($period->total_partners_net_profit, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">5. إجمالي ربح حساب الإدارة</div>
                    <div class="num">+{{ number_format($period->ahmed_total_profit ?: $period->total_ahmed_commission, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">6. المدور / المسحوب نقداً</div>
                    <div class="num" style="font-size:8pt !important;">
                        مدور: {{ number_format($period->total_reinvested, 2) }} ج<br>
                        <span style="color:#b91c1c;">سحب: {{ number_format($period->total_withdrawn, 2) }} ج</span>
                    </div>
                </div>
            </div>
        </div>

        @if($period->notes)
            <div class="alert-pro info mb-3" style="font-size: 0.84rem;">
                <i class="fa fa-file-alt"></i>
                <span><strong>ملاحظات التقفيل والاعتماد:</strong> {{ $period->notes }}</span>
            </div>
        @endif

        {{-- جدول تفاصيل حصص كل شريك ومقارنة النسب قبل وبعد التقفيل --}}
        <div class="prt-panel">
            <div class="prt-panel-head">
                <h5 class="prt-panel-title">
                    <span class="prt-panel-title-ico"><i class="fa fa-list-check"></i></span>
                    <span>بيان توزيع الأرباح وتغير النسب (قبل التقفيل ← بعد التقفيل) للشركاء وحساب الإدارة</span>
                </h5>
                <span class="prt-chip is-neutral">المبالغ بالجنيه المصري (EGP)</span>
            </div>

            <div class="prt-table-wrap">
                <table class="prt-table">
                    <thead>
                        <tr>
                            <th style="width: 36px;" class="text-center">#</th>
                            <th>الشريك / الحساب</th>
                            <th class="text-center">الرصيد قبل</th>
                            <th class="text-center th-soft-before">النسبة كانت كام</th>
                            <th class="text-center">ربح الحصة (g)</th>
                            <th class="text-center">نسبة وأرباح الشغل</th>
                            <th class="text-center">صافي الربح (n)</th>
                            <th class="text-center">القرار والخزينة</th>
                            <th class="text-center">المسحوب نقداً</th>
                            <th class="text-center">المدوّر بالرصيد</th>
                            <th class="text-center">الرصيد بعد</th>
                            <th class="text-center th-soft-after">النسبة بقت كام</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($period->ahmed_opening_capital))
                            @php
                                $ahmedOpenPct  = (float) $period->ahmed_capital_share_pct;
                                $ahmedClosePct = (float) ($period->ahmed_closing_share_pct ?? 0);
                                $ahmedPctDiff  = round($ahmedClosePct - $ahmedOpenPct, 4);
                            @endphp
                            <tr class="prt-row-head">
                                <td class="text-center text-muted">—</td>
                                <td>
                                    <strong class="d-block" style="color: var(--c-navy);">{{ $headName }}</strong>
                                    <span class="prt-chip is-head mt-1">حساب الإدارة</span>
                                </td>
                                <td class="text-center">{{ number_format($period->ahmed_opening_capital, 2) }} ج</td>
                                <td class="text-center"><span class="prt-chip is-neutral">{{ number_format($ahmedOpenPct, 2) }}%</span></td>
                                <td class="text-center fw-semibold" style="color: {{ $period->ahmed_own_profit >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $period->ahmed_own_profit >= 0 ? '+' : '' }}{{ number_format($period->ahmed_own_profit, 2) }} ج
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold" style="color: var(--c-info);">+{{ number_format($period->ahmed_commission, 2) }} ج</div>
                                    <div class="prt-num-sub">حساب الإدارة</div>
                                </td>
                                <td class="text-center fw-bold" style="color: {{ $period->ahmed_total_profit >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $period->ahmed_total_profit >= 0 ? '+' : '' }}{{ number_format($period->ahmed_total_profit, 2) }} ج
                                </td>
                                <td class="text-center">
                                    @if($period->ahmed_payout_action === 'reinvest')
                                        <span class="prt-chip is-success">تدوير بالحساب</span>
                                    @elseif($period->ahmed_payout_action === 'withdraw_all')
                                        <span class="prt-chip is-info">سحب كامل</span>
                                    @else
                                        <span class="prt-chip is-warning">سحب جزئي</span>
                                    @endif
                                    @if(!empty($ahmedPayoutAccountName))
                                        <div class="prt-num-sub">{{ $ahmedPayoutAccountName }}</div>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold" style="color: {{ $period->ahmed_withdrawn_amount > 0 ? 'var(--c-danger)' : 'var(--c-text-soft)' }};">
                                    {{ $period->ahmed_withdrawn_amount > 0 ? number_format($period->ahmed_withdrawn_amount, 2) . ' ج' : '—' }}
                                </td>
                                <td class="text-center fw-semibold" style="color: {{ $period->ahmed_reinvested_amount != 0 ? 'var(--c-success)' : 'var(--c-text-soft)' }};">
                                    {{ $period->ahmed_reinvested_amount != 0 ? '+' . number_format($period->ahmed_reinvested_amount, 2) . ' ج' : '—' }}
                                </td>
                                <td class="text-center prt-num-main">{{ number_format($period->ahmed_closing_capital, 2) }} ج</td>
                                <td class="text-center">
                                    <span class="prt-chip is-accent">{{ number_format($ahmedClosePct, 2) }}%</span>
                                    @if(abs($ahmedPctDiff) > 0.0001)
                                        <div class="prt-num-sub fw-semibold" style="color: {{ $ahmedPctDiff > 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                            {{ $ahmedPctDiff > 0 ? '+' : '' }}{{ number_format($ahmedPctDiff, 2) }}%
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @forelse($lines as $l)
                            @php
                                $openPct  = (float) $l->capital_share_pct;
                                $closePct = (float) ($l->closing_share_pct ?? 0);
                                $pctDiff  = round($closePct - $openPct, 4);
                            @endphp
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <strong class="d-block" style="color: var(--c-navy);">{{ $l->partner_name }}</strong>
                                    <div class="prt-meta-line">{{ $l->partner_phone ?: '—' }}</div>
                                </td>
                                <td class="text-center">{{ number_format($l->opening_capital, 2) }} ج</td>
                                <td class="text-center"><span class="prt-chip is-neutral">{{ number_format($openPct, 2) }}%</span></td>
                                <td class="text-center fw-semibold" style="color: {{ $l->gross_share >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $l->gross_share >= 0 ? '+' : '' }}{{ number_format($l->gross_share, 2) }} ج
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold" style="color: var(--c-info);">+{{ number_format($l->ahmed_commission, 2) }} ج</div>
                                    <span class="prt-chip is-neutral mt-1" style="font-size:0.66rem; padding:1px 6px;">{{ number_format($l->ahmed_ratio_pct, 0) }}%</span>
                                </td>
                                <td class="text-center fw-bold" style="color: {{ $l->net_share >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                    {{ $l->net_share >= 0 ? '+' : '' }}{{ number_format($l->net_share, 2) }} ج
                                </td>
                                <td class="text-center">
                                    @if($l->payout_action === 'reinvest')
                                        <span class="prt-chip is-success">تدوير بالرصيد</span>
                                    @elseif($l->payout_action === 'withdraw_all')
                                        <span class="prt-chip is-info">سحب كامل</span>
                                    @else
                                        <span class="prt-chip is-warning">سحب جزئي</span>
                                    @endif
                                    @if(!empty($l->payout_account_name))
                                        <div class="prt-num-sub">{{ $l->payout_account_name }}</div>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold" style="color: {{ $l->withdrawn_amount > 0 ? 'var(--c-danger)' : 'var(--c-text-soft)' }};">
                                    {{ $l->withdrawn_amount > 0 ? number_format($l->withdrawn_amount, 2) . ' ج' : '—' }}
                                </td>
                                <td class="text-center fw-semibold" style="color: {{ $l->reinvested_amount != 0 ? 'var(--c-success)' : 'var(--c-text-soft)' }};">
                                    {{ $l->reinvested_amount != 0 ? '+' . number_format($l->reinvested_amount, 2) . ' ج' : '—' }}
                                </td>
                                <td class="text-center prt-num-main">{{ number_format($l->closing_capital, 2) }} ج</td>
                                <td class="text-center">
                                    <span class="prt-chip is-accent">{{ number_format($closePct, 2) }}%</span>
                                    @if(abs($pctDiff) > 0.0001)
                                        <div class="prt-num-sub fw-semibold" style="color: {{ $pctDiff > 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                            {{ $pctDiff > 0 ? '+' : '' }}{{ number_format($pctDiff, 2) }}%
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">لا توجد بيانات تفصيلية لهذه الدورة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- خانات التوقيع والاعتماد الرسمية للطباعة --}}
        <div class="print-only print-signatures-block">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">إعداد وتدقيق الحسابات</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">وحدة الإدارة المالية</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">المدير المالي التنفيذي</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">المطابقة وصرف الخزائن</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع والختم: ............................</div>
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
                وثيقة مالية رسمية معتمدة ومرحلة بالدفاتر المحاسبية والخزائن الإلكترونية لشركة الضبع · صالحة للتدقيق والمطابقة
            </div>
        </div>

    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>
