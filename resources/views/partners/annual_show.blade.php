<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير التقفيل السنوي لسنة {{ $closing->closing_year }} - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    @include('partials.theme', ['accent' => 'teal'])
    @include('partners._calm_theme')

    <style>
        .ann-kpi-val {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--c-navy);
            line-height: 1.25;
            font-variant-numeric: tabular-nums;
        }
        .print-only { display: none; }

        @media print {
            @page {
                size: A4 landscape;
                margin: 9mm 7mm 10mm 7mm;
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
                font-size: 7.6pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop,
            .no-print, th.no-print, td.no-print, button, .btn, .prt-pill-btn, .prt-back-link {
                display: none !important;
            }
            .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; min-width: 100% !important; max-width: 100% !important; }
            .container-fluid { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            .print-only { display: block !important; }
            .prt-panel { border: none !important; border-radius: 0 !important; background: transparent !important; margin-bottom: 8px !important; }
            .prt-panel-head { background: #f8fafc !important; border: 1px solid #94a3b8 !important; padding: 6px 10px !important; margin-bottom: 6px !important; }
            .prt-table-wrap { overflow: visible !important; }
            .prt-table { width: 100% !important; border-collapse: collapse !important; font-size: 7.4pt !important; }
            .prt-table thead { display: table-header-group !important; }
            .prt-table thead th {
                background-color: #0d1f35 !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
                padding: 5px 4px !important;
                font-size: 7pt !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .prt-table tbody td {
                border: 1px solid #94a3b8 !important;
                padding: 4px 4px !important;
                font-size: 7.3pt !important;
                color: #000000 !important;
                background: #ffffff !important;
            }
            .prt-table tfoot td {
                background-color: #f1f5f9 !important;
                border: 1px solid #475569 !important;
                border-top: 2px solid #0d1f35 !important;
                padding: 5px 4px !important;
                font-size: 7.4pt !important;
                color: #000000 !important;
            }
            .prt-table tr { page-break-inside: avoid !important; }
            .prt-chip { border: 1px solid #475569 !important; color: #000000 !important; background: #f1f5f9 !important; padding: 1px 4px !important; font-size: 6.6pt !important; }
            .print-kpi-grid { display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 6px !important; margin-bottom: 10px !important; }
            .print-kpi-box { border: 1px solid #cbd5e1 !important; padding: 6px 8px !important; background: #f8fafc !important; text-align: center !important; border-radius: 4px !important; }
            .print-kpi-box .lbl { font-size: 7pt !important; color: #475569 !important; font-weight: 700 !important; }
            .print-kpi-box .num { font-size: 9.5pt !important; font-weight: 800 !important; color: #0d1f35 !important; }
            .print-sig-col { border: 1px dashed #64748b !important; padding: 10px 8px !important; text-align: center !important; background: #ffffff !important; border-radius: 4px !important; }
        }
    </style>
</head>
<body>

@include('sidebar')

@php
    $yrGainPct  = (float) $closing->total_opening_capital > 0 ? round(((float) $closing->total_annual_gain / (float) $closing->total_opening_capital) * 100, 2) : 0.0;
    $cumGainPct = (float) $closing->total_initial_capital > 0 ? round(((float) $closing->total_cumulative_gain / (float) $closing->total_initial_capital) * 100, 2) : 0.0;
@endphp

<div class="main-content">
    <div class="container-fluid px-0">

        {{-- ترويسة الطباعة الرسمية --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">وحدة الحسابات والرقابة المالية وإدارة حصص الشركاء</small>
                    <span class="badge bg-dark text-white mt-1">كود الوثيقة: ANN-{{ $closing->closing_year }}</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">تقرير الإقفال السنوي المعتمد لسنة {{ $closing->closing_year }}</h4>
                    <small class="text-muted">مقارنة رأس المال التأسيسي الثابت بأرصدة بداية ونهاية السنة المالية {{ $closing->closing_year }}</small>
                </div>
                <div class="text-start">
                    <div class="badge bg-success text-white px-2 py-1 mb-1">إقفال سنوي معتمد</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الاعتماد: {{ $closing->closed_at ? date('Y-m-d H:i', strtotime($closing->closed_at)) : '—' }}</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- ترويسة الشاشة --}}
        <div class="prt-header no-print">
            <div>
                <a href="{{ route('partners.index', ['tab' => 'annual']) }}" class="prt-back-link">
                    <i class="fa fa-arrow-right fa-xs"></i>
                    <span>العودة إلى التقفيلات السنوية</span>
                </a>
                <div class="prt-title-row">
                    <span class="prt-title-badge"><i class="fa fa-calendar-check"></i></span>
                    <h1 class="prt-title">تقرير الإقفال السنوي لسنة {{ $closing->closing_year }}</h1>
                    <span class="prt-chip is-success">معتمد ومحفوظ</span>
                </div>
                <p class="prt-subtitle">
                    عدد الشركاء والحسابات: <strong>{{ $closing->partners_count }}</strong>
                    · تاريخ الإقفال: {{ $closing->closed_at ? date('Y-m-d H:i', strtotime($closing->closed_at)) : '—' }}
                </p>
            </div>
            <div class="prt-actions align-self-end">
                <button type="button" class="prt-pill-btn is-primary" onclick="window.print()">
                    <i class="fa fa-print fa-xs"></i>
                    <span>طباعة التقرير السنوي (A4)</span>
                </button>
            </div>
        </div>

        {{-- كروت المؤشرات للشاشة --}}
        <div class="row g-3 mb-4 no-print">
            <div class="col-xl-3 col-md-6">
                <div class="prt-kpi-card is-blue">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">إجمالي رأس المال التأسيسي (ثابت)</div>
                        <span class="prt-kpi-ico"><i class="fa fa-lock"></i></span>
                    </div>
                    <div class="ann-kpi-val"><span dir="ltr">{{ number_format($closing->total_initial_capital, 2) }}</span> <span class="kpi-cur">ج</span></div>
                    <div class="kpi-sub">بداية السنة: {{ number_format($closing->total_opening_capital, 2) }} ج</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="prt-kpi-card is-teal">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">إجمالي الأرصدة بنهاية {{ $closing->closing_year }}</div>
                        <span class="prt-kpi-ico"><i class="fa fa-vault"></i></span>
                    </div>
                    <div class="ann-kpi-val"><span dir="ltr">{{ number_format($closing->total_closing_capital, 2) }}</span> <span class="kpi-cur">ج</span></div>
                    <div class="kpi-sub">{{ $closing->partners_count }} شريك وحساب إدارة</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="prt-kpi-card {{ $closing->total_annual_gain >= 0 ? 'is-emerald' : 'is-rose' }}">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">صافي النمو خلال سنة {{ $closing->closing_year }}</div>
                        <span class="prt-kpi-ico"><i class="fa fa-arrow-trend-up"></i></span>
                    </div>
                    <div class="ann-kpi-val" style="color: {{ $closing->total_annual_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                        <span dir="ltr">{{ $closing->total_annual_gain >= 0 ? '+' : '' }}{{ number_format($closing->total_annual_gain, 2) }}</span> <span class="kpi-cur">ج</span>
                    </div>
                    <div class="kpi-sub">نسبة التغير السنوي: <strong>{{ $yrGainPct >= 0 ? '+' : '' }}{{ number_format($yrGainPct, 2) }}%</strong></div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="prt-kpi-card is-amber">
                    <div class="prt-kpi-top">
                        <div class="kpi-lbl">النمو التراكمي منذ التأسيس</div>
                        <span class="prt-kpi-ico"><i class="fa fa-coins"></i></span>
                    </div>
                    <div class="ann-kpi-val" style="color: {{ $closing->total_cumulative_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                        <span dir="ltr">{{ $closing->total_cumulative_gain >= 0 ? '+' : '' }}{{ number_format($closing->total_cumulative_gain, 2) }}</span> <span class="kpi-cur">ج</span>
                    </div>
                    <div class="kpi-sub">نسبة تراكمية: <strong>{{ $cumGainPct >= 0 ? '+' : '' }}{{ number_format($cumGainPct, 2) }}%</strong> · أرباح موزعة: {{ number_format($closing->total_year_profits, 2) }} ج</div>
                </div>
            </div>
        </div>

        {{-- كروت الطباعة --}}
        <div class="print-only">
            <div class="print-kpi-grid">
                <div class="print-kpi-box">
                    <div class="lbl">1. إجمالي رأس المال التأسيسي</div>
                    <div class="num">{{ number_format($closing->total_initial_capital, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">2. إجمالي رصيد نهاية {{ $closing->closing_year }}</div>
                    <div class="num">{{ number_format($closing->total_closing_capital, 2) }} ج</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">3. صافي الزيادة في {{ $closing->closing_year }}</div>
                    <div class="num">{{ $closing->total_annual_gain >= 0 ? '+' : '' }}{{ number_format($closing->total_annual_gain, 2) }} ج ({{ $yrGainPct }}%)</div>
                </div>
                <div class="print-kpi-box">
                    <div class="lbl">4. النمو التراكمي منذ التأسيس</div>
                    <div class="num">{{ $closing->total_cumulative_gain >= 0 ? '+' : '' }}{{ number_format($closing->total_cumulative_gain, 2) }} ج ({{ $cumGainPct }}%)</div>
                </div>
            </div>
        </div>

        @if($closing->notes)
            <div class="alert-pro info mb-3" style="font-size: 0.84rem;">
                <i class="fa fa-file-alt"></i>
                <span><strong>ملاحظات الإقفال السنوي:</strong> {{ $closing->notes }}</span>
            </div>
        @endif

        {{-- جدول تفاصيل التقفيل السنوي --}}
        <div class="prt-panel">
            <div class="prt-panel-head">
                <h5 class="prt-panel-title">
                    <span class="prt-panel-title-ico"><i class="fa fa-table-list"></i></span>
                    <span>كشف أرصدة ونمو الشركاء في إقفال سنة {{ $closing->closing_year }}</span>
                </h5>
                <span class="prt-chip is-neutral">المبالغ بالجنيه المصري (EGP)</span>
            </div>

            <div class="prt-table-wrap">
                <table class="prt-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 38px;">#</th>
                            <th>الشريك / الحساب</th>
                            <th class="text-center th-soft-before"><i class="fa fa-lock fa-xs me-1"></i> رأس المال التأسيسي (بدأ بكام)</th>
                            <th class="text-center">رصيد بداية {{ $closing->closing_year }}</th>
                            <th class="text-center th-soft-after">رصيد نهاية {{ $closing->closing_year }}</th>
                            <th class="text-center">صافي الزيادة في {{ $closing->closing_year }}</th>
                            <th class="text-center">أرباح الدورات بالسنة</th>
                            <th class="text-center">المسحوب نقداً</th>
                            <th class="text-center">إجمالي النمو منذ التأسيس</th>
                            <th class="text-center">الحصة بنهاية السنة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $l)
                            <tr class="{{ $l->is_head ? 'prt-row-head' : '' }}">
                                <td class="text-center text-muted">{{ $l->is_head ? '—' : $loop->iteration }}</td>
                                <td>
                                    <strong style="color: var(--c-navy);">{{ $l->partner_name }}</strong>
                                    @if($l->is_head)
                                        <span class="prt-chip is-head ms-1">حساب الإدارة</span>
                                    @endif
                                </td>
                                <td class="text-center prt-num-main">{{ number_format($l->initial_capital, 2) }} ج</td>
                                <td class="text-center text-muted">{{ number_format($l->opening_capital, 2) }} ج</td>
                                <td class="text-center prt-num-main">{{ number_format($l->closing_capital, 2) }} ج</td>
                                <td class="text-center">
                                    <span class="prt-chip {{ $l->annual_gain >= 0 ? 'is-success' : 'is-danger' }}">
                                        {{ $l->annual_gain >= 0 ? '+' : '' }}{{ number_format($l->annual_gain, 2) }} ج
                                    </span>
                                    @if((float) $l->opening_capital > 0)
                                        <div class="prt-num-sub fw-semibold" style="color: {{ $l->annual_gain_pct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                            {{ $l->annual_gain_pct >= 0 ? '+' : '' }}{{ number_format($l->annual_gain_pct, 2) }}%
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold" style="color: var(--c-info);">
                                    {{ (float) $l->year_net_profits != 0 ? (($l->year_net_profits > 0 ? '+' : '') . number_format($l->year_net_profits, 2) . ' ج') : '—' }}
                                </td>
                                <td class="text-center fw-semibold" style="color: {{ $l->year_withdrawn > 0 ? 'var(--c-danger)' : 'var(--c-text-soft)' }};">
                                    {{ $l->year_withdrawn > 0 ? '-' . number_format($l->year_withdrawn, 2) . ' ج' : '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="prt-chip {{ $l->cumulative_gain >= 0 ? 'is-success' : 'is-danger' }}">
                                        {{ $l->cumulative_gain >= 0 ? '+' : '' }}{{ number_format($l->cumulative_gain, 2) }} ج
                                    </span>
                                    @if((float) $l->initial_capital > 0)
                                        <div class="prt-num-sub fw-semibold" style="color: {{ $l->cumulative_gain_pct >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                            {{ $l->cumulative_gain_pct >= 0 ? '+' : '' }}{{ number_format($l->cumulative_gain_pct, 2) }}%
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="prt-chip is-accent">{{ number_format($l->closing_share_pct, 2) }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end">الإجمالي الكلي لسنة {{ $closing->closing_year }}:</td>
                            <td class="text-center">{{ number_format($closing->total_initial_capital, 2) }} ج</td>
                            <td class="text-center">{{ number_format($closing->total_opening_capital, 2) }} ج</td>
                            <td class="text-center">{{ number_format($closing->total_closing_capital, 2) }} ج</td>
                            <td class="text-center" style="color: {{ $closing->total_annual_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                {{ $closing->total_annual_gain >= 0 ? '+' : '' }}{{ number_format($closing->total_annual_gain, 2) }} ج
                            </td>
                            <td class="text-center" style="color: var(--c-info);">+{{ number_format($closing->total_year_profits, 2) }} ج</td>
                            <td class="text-center" style="color: {{ $closing->total_year_withdrawn > 0 ? 'var(--c-danger)' : 'var(--c-text-soft)' }};">
                                {{ $closing->total_year_withdrawn > 0 ? '-' . number_format($closing->total_year_withdrawn, 2) . ' ج' : '—' }}
                            </td>
                            <td class="text-center" style="color: {{ $closing->total_cumulative_gain >= 0 ? 'var(--c-success)' : 'var(--c-danger)' }};">
                                {{ $closing->total_cumulative_gain >= 0 ? '+' : '' }}{{ number_format($closing->total_cumulative_gain, 2) }} ج
                            </td>
                            <td class="text-center">100.00%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- خانات التوقيع الرسمية للطباعة --}}
        <div class="print-only mt-3" style="page-break-inside: avoid;">
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
                وثيقة إقفال سنوي رسمية صادرة عن النظام المالي لشركة الضبع · مستخرجة بتاريخ {{ date('Y-m-d H:i') }}
            </div>
        </div>

    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
