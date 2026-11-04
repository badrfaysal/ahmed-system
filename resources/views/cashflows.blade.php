<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التدفقات النقدية (الجمعيات) - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('partials.theme', ['accent' => 'teal'])

    <style>
        .main-content { margin-right: 260px; padding: 26px 28px; }
        @media(max-width: 991px) { .main-content { margin-right: 0 !important; padding: 75px 16px 30px !important; } }

        /* ── Hero Header ── */
        .flow-hero {
            background: linear-gradient(135deg, #0d3b36 0%, #115e59 50%, #0f766e 100%);
            color: #fff;
            border-radius: 20px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(15, 118, 110, 0.15);
            position: relative; overflow: hidden;
            display: flex; justify-content: space-between; align-items: center;
            gap: 16px; flex-wrap: wrap;
        }
        .flow-hero::after {
            content: ""; position: absolute; top: -50%; left: -10%;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(94, 234, 212, 0.2), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .flow-hero .hero-block { position: relative; z-index: 2; display: flex; gap: 14px; align-items: center; }
        .flow-hero .hero-icon {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(6px);
            width: 58px; height: 58px; border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: #fff;
        }
        .flow-hero h2 { color: #fff; font-weight: 700; font-size: 1.35rem; margin: 0; }
        .flow-hero .sub { opacity: 0.9; font-size: 0.88rem; margin-top: 4px; }

        .hero-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; position: relative; z-index: 2; }
        .btn-hero {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(6px);
            color: #fff; border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px; padding: 9px 18px; font-weight: 600; font-size: 0.9rem;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-hero:hover { background: rgba(255,255,255,0.3); color: #fff; transform: translateY(-1px); }
        .btn-hero-primary {
            background: #fff; color: #0f766e !important; border: none; font-weight: 700;
        }
        .btn-hero-primary:hover { background: #ccfbf1; color: #0d5f57 !important; }

        /* ── Filter Bar ── */
        .filter-strip {
            background: #fff; border-radius: 16px; padding: 14px 20px;
            margin-bottom: 22px; box-shadow: 0 4px 18px rgba(0,0,0,0.03);
            display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .month-picker-wrap { display: flex; align-items: center; gap: 10px; }
        .month-picker-wrap input[type="month"] {
            border: 1px solid #d1d5db; border-radius: 10px; padding: 8px 14px;
            font-size: 0.95rem; font-weight: 600; color: #1f2937; background: #f9fafb;
            cursor: pointer; outline: none;
        }
        .month-picker-wrap input[type="month"]:focus { border-color: #0d9488; background: #fff; }

        /* ── Stat Cards ── */
        .kpi-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }
        .kpi-card {
            background: #fff; border-radius: 18px; padding: 20px 22px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;
            position: relative; overflow: hidden; transition: transform 0.2s;
        }
        .kpi-card:hover { transform: translateY(-2px); }
        .kpi-card::before {
            content: ""; position: absolute; top: 0; right: 0; width: 5px; height: 100%;
        }
        .kpi-card.red::before    { background: #ef4444; }
        .kpi-card.green::before  { background: #10b981; }
        .kpi-card.teal::before   { background: #0d9488; }
        .kpi-card.purple::before { background: #8b5cf6; }

        .kpi-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .kpi-title { font-size: 0.85rem; font-weight: 600; color: #64748b; margin: 0; }
        .kpi-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1rem;
        }
        .kpi-icon.red    { background: #fee2e2; color: #ef4444; }
        .kpi-icon.green  { background: #d1fae5; color: #10b981; }
        .kpi-icon.teal   { background: #ccfbf1; color: #0d9488; }
        .kpi-icon.purple { background: #ede9fe; color: #8b5cf6; }

        .kpi-val { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .kpi-sub { font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; justify-content: space-between; }

        /* ── Charts Grid ── */
        .chart-box {
            background: #fff; border-radius: 18px; padding: 22px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;
            margin-bottom: 24px;
        }
        .box-title {
            font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }

        /* ── Nav Tabs ── */
        .custom-tabs {
            border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; gap: 10px; display: flex;
        }
        .custom-tab-btn {
            background: none; border: none; padding: 12px 20px; font-weight: 700;
            color: #64748b; border-bottom: 3px solid transparent; margin-bottom: -2px;
            transition: all 0.2s; font-size: 0.95rem; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .custom-tab-btn:hover { color: #0d9488; }
        .custom-tab-btn.active {
            color: #0d9488; border-bottom-color: #0d9488;
        }

        /* ── Modern Table ── */
        .flow-table {
            width: 100%; border-collapse: separate; border-spacing: 0;
        }
        .flow-table th {
            background: #f8fafc; color: #475569; font-weight: 700; font-size: 0.85rem;
            padding: 14px 16px; border-bottom: 2px solid #e2e8f0;
        }
        .flow-table td {
            padding: 14px 16px; border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem; vertical-align: middle; color: #1e293b;
        }
        .flow-table tr:hover td { background: #fdfefe; }

        .badge-status {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 700;
        }
        .badge-paid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-pending { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-collected { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-waived { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-transferred { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }

        .btn-act {
            border: none; border-radius: 8px; padding: 6px 12px; font-size: 0.82rem;
            font-weight: 600; cursor: pointer; transition: 0.2s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-pay { background: #0d9488; color: #fff; }
        .btn-pay:hover { background: #0f766e; color: #fff; }
        .btn-collect { background: #10b981; color: #fff; }
        .btn-collect:hover { background: #059669; color: #fff; }
        .btn-transfer { background: #f97316; color: #fff; }
        .btn-transfer:hover { background: #ea580c; color: #fff; }
        .btn-recover { background: #3b82f6; color: #fff; }
        .btn-recover:hover { background: #2563eb; color: #fff; }
        .btn-cancel { background: #ef4444; color: #fff; }
        .btn-cancel:hover { background: #dc2626; color: #fff; }

        .jamia-card {
            background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
            padding: 20px; margin-bottom: 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .jamia-card:hover { border-color: #0d9488; box-shadow: 0 4px 18px rgba(13,148,136,0.08); }
        .share-pill {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 8px 14px; display: inline-flex; align-items: center; gap: 8px;
            margin: 4px; font-size: 0.85rem; font-weight: 600;
        }
        .share-pill.collected { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .share-pill.transferred { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">

    {{-- Alerts --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'تمت العملية بنجاح', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'error', title: 'خطأ', text: "{{ session('error') }}" });
            });
        </script>
    @endif

    {{-- ═══ Hero Header ═══ --}}
    <div class="flow-hero">
        <div class="hero-block">
            <div class="hero-icon"><i class="fa-solid fa-handshake"></i></div>
            <div>
                <h2>التدفقات النقدية (الجمعيات)</h2>
                <div class="sub">متابعة المداخل والمخارج الشهرية، مواعيد القبض وسداد الأقساط، التنازلات والمقاصات</div>
            </div>
        </div>
        <div class="hero-actions">
            <button class="btn-hero btn-hero-primary" data-bs-toggle="modal" data-bs-target="#newJamiaModal">
                <i class="fa-solid fa-plus-circle"></i> إضافة جمعية جديدة
            </button>
            <a href="{{ route('cashflows.print_month', ['month' => $selectedMonth]) }}" target="_blank" class="btn-hero">
                <i class="fa-solid fa-print"></i> طباعة كشف الشهر
            </a>
        </div>
    </div>

    {{-- ═══ Filter & Month Navigation Bar ═══ --}}
    <div class="filter-strip">
        <form method="GET" action="{{ route('cashflows.index') }}" class="month-picker-wrap" id="monthForm">
            <label class="fw-bold text-secondary" style="font-size:0.9rem;"><i class="fa-regular fa-calendar-days text-teal me-1"></i> اختر شهر العرض:</label>
            <input type="month" name="month" id="monthInput" value="{{ $selectedMonth }}" onchange="document.getElementById('monthForm').submit();">
            
            <a href="{{ route('cashflows.index', ['month' => $monthDate->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary rounded-3" title="الشهر السابق">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <a href="{{ route('cashflows.index', ['month' => now()->format('Y-m')]) }}" class="btn btn-sm btn-outline-teal rounded-3 fw-bold">
                الشهر الحالي
            </a>
            <a href="{{ route('cashflows.index', ['month' => $monthDate->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-sm btn-outline-secondary rounded-3" title="الشهر القادم">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        </form>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border p-2 px-3 rounded-pill" style="font-size:0.9rem;">
                <i class="fa-solid fa-chart-pie text-teal me-1"></i> عرض استحقاقات شهر: <strong>{{ $monthDate->locale('ar')->isoFormat('MMMM YYYY') }}</strong>
            </span>
        </div>
    </div>

    {{-- ═══ Monthly KPI Cards ═══ --}}
    <div class="kpi-grid">
        {{-- المطلوب دفعه هذا الشهر (الخارج) --}}
        <div class="kpi-card red">
            <div class="kpi-head">
                <h6 class="kpi-title">المطالب به (الخارج للجمعيات)</h6>
                <div class="kpi-icon red"><i class="fa-solid fa-arrow-trend-down"></i></div>
            </div>
            <div class="kpi-val text-danger">{{ number_format($totalDueThisMonth, 2) }} <small style="font-size:0.9rem; font-weight:600;">ج</small></div>
            <div class="kpi-sub">
                <span>مسدد: <b class="text-success">{{ number_format($totalPaidThisMonth, 0) }} ج</b></span>
                <span>متبقي: <b class="text-danger">{{ number_format($remainingDueThisMonth, 0) }} ج</b></span>
            </div>
        </div>

        {{-- المقبوض المتوقع هذا الشهر (الداخل) --}}
        <div class="kpi-card green">
            <div class="kpi-head">
                <h6 class="kpi-title">المقبوض المتوقع (الداخل للشركة)</h6>
                <div class="kpi-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div>
            </div>
            <div class="kpi-val text-success">{{ number_format($expectedInflowThisMonth, 2) }} <small style="font-size:0.9rem; font-weight:600;">ج</small></div>
            <div class="kpi-sub">
                <span>محصل: <b class="text-success">{{ number_format($collectedInflowThisMonth, 0) }} ج</b></span>
                <span>معلق: <b class="text-warning">{{ number_format($remainingInflowThisMonth, 0) }} ج</b></span>
            </div>
        </div>

        {{-- صافي التدفق لهذا الشهر (Net Flow) --}}
        <div class="kpi-card teal">
            <div class="kpi-head">
                <h6 class="kpi-title">صافي تدفق الشهر (الداخل - الخارج)</h6>
                <div class="kpi-icon teal"><i class="fa-solid fa-scale-balanced"></i></div>
            </div>
            <div class="kpi-val {{ $netExpectedThisMonth >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $netExpectedThisMonth >= 0 ? '+' : '' }}{{ number_format($netExpectedThisMonth, 2) }} <small style="font-size:0.9rem; font-weight:600;">ج</small>
            </div>
            <div class="kpi-sub">
                <span>الصافي الفعلي المحقق: <b class="{{ $netActualThisMonth >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netActualThisMonth, 0) }} ج</b></span>
            </div>
        </div>

        {{-- إجمالي الاستثمار بالجمعيات --}}
        <div class="kpi-card purple">
            <div class="kpi-head">
                <h6 class="kpi-title">الجمعيات النشطة والأسهم</h6>
                <div class="kpi-icon purple"><i class="fa-solid fa-layer-group"></i></div>
            </div>
            <div class="kpi-val text-purple">{{ $globalStats['active_jamias_count'] }} <small style="font-size:0.9rem; font-weight:600;">جمعية</small></div>
            <div class="kpi-sub">
                <span>إجمالي الأسهم: <b>{{ $globalStats['total_company_shares'] }}</b></span>
                <span>متنازل عنها: <b class="text-warning">{{ $globalStats['transferred_shares'] }}</b></span>
            </div>
        </div>
    </div>

    {{-- ═══ Annual Charts ═══ --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="chart-box h-100 mb-0">
                <div class="box-title">
                    <i class="fa-solid fa-chart-column text-teal"></i> رسم بياني للتدفقات الشهرية لسنة {{ $monthDate->year }} (الداخل vs الخارج)
                </div>
                <div style="height: 290px; position: relative;">
                    <canvas id="cashflowYearChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-box h-100 mb-0">
                <div class="box-title">
                    <i class="fa-solid fa-chart-pie text-teal"></i> توزيع الشهر الحالي
                </div>
                <div style="height: 230px; position: relative;">
                    <canvas id="monthRatioChart"></canvas>
                </div>
                <div class="text-center mt-2 text-muted" style="font-size:0.82rem;">
                    مقارنة المبالغ المسددة والمقبوضة لشهر {{ $monthDate->locale('ar')->isoFormat('MMMM YYYY') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Tabs Navigation ═══ --}}
    <div class="custom-tabs">
        <button class="custom-tab-btn active" onclick="switchTab('tab-month', this)">
            <i class="fa-solid fa-calendar-check"></i> استحقاقات شهر {{ $monthDate->locale('ar')->isoFormat('MMMM') }} ({{ $monthInstallments->count() + $monthShares->count() }})
        </button>
        <button class="custom-tab-btn" onclick="switchTab('tab-jamias', this)">
            <i class="fa-solid fa-list-check"></i> دليل الجمعيات المشتركة ({{ $jamias->count() }})
        </button>
        <button class="custom-tab-btn" onclick="switchTab('tab-shares', this)">
            <i class="fa-solid fa-users-viewfinder"></i> جدول جميع الأسهم والتنازلات ({{ $allShares->count() }})
        </button>
    </div>

    {{-- ═══ Tab 1: Month Inflow & Outflow Details ═══ --}}
    <div id="tab-month" class="tab-content-pane">
        <div class="row g-4">
            {{-- 1. جدول الأقساط المطالب بها هذا الشهر (الخارج) --}}
            <div class="col-lg-6">
                <div class="chart-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="box-title mb-0">
                            <i class="fa-solid fa-arrow-down-long text-danger"></i> الأقساط المطالب بدفعها (الخارج)
                        </div>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold" style="font-size:0.85rem;">
                            الإجمالي: {{ number_format($totalDueThisMonth, 2) }} ج
                        </span>
                    </div>

                    @if($monthInstallments->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-regular fa-calendar-xmark fa-2x mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0">لا توجد أقساط مستحقة على الشركة في هذا الشهر.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="flow-table">
                                <thead>
                                    <tr>
                                        <th>الجمعية</th>
                                        <th>السهم</th>
                                        <th>القسط</th>
                                        <th>الحالة</th>
                                        <th>الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthInstallments as $inst)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $inst->jamia_name }}</div>
                                                <small class="text-muted">يوم الاستحقاق: {{ $inst->due_day }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $inst->share_label }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-danger">{{ number_format($inst->amount, 2) }} ج</strong>
                                            </td>
                                            <td>
                                                @if($inst->is_paid)
                                                    <span class="badge-status badge-paid">
                                                        <i class="fa-solid fa-check"></i> مسدد
                                                    </span>
                                                    <div style="font-size:0.75rem; color:#64748b;" class="mt-1">
                                                        {{ $inst->paid_account_name ?? 'الخزنة' }}
                                                    </div>
                                                @else
                                                    <span class="badge-status badge-pending">
                                                        <i class="fa-regular fa-clock"></i> مستحق
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$inst->is_paid)
                                                    <button class="btn-act btn-pay" onclick="openPayModal({{ $inst->id }}, '{{ addslashes($inst->jamia_name) }}', '{{ addslashes($inst->share_label) }}', {{ $inst->amount }})">
                                                        <i class="fa-solid fa-money-bill-wave"></i> سداد
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('cashflows.cancel_payment') }}" onsubmit="return confirm('هل تريد إلغاء سداد هذا القسط واسترجاع المبلغ للمحفظة؟');" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="installment_id" value="{{ $inst->id }}">
                                                        <button type="submit" class="btn-act btn-cancel" title="إلغاء السداد">
                                                            <i class="fa-solid fa-rotate-left"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. جدول المقبوضات المتوقعة هذا الشهر (الداخل) --}}
            <div class="col-lg-6">
                <div class="chart-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="box-title mb-0">
                            <i class="fa-solid fa-arrow-up-long text-success"></i> المقبوضات والتحصيلات (الداخل)
                        </div>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold" style="font-size:0.85rem;">
                            الإجمالي: {{ number_format($expectedInflowThisMonth, 2) }} ج
                        </span>
                    </div>

                    @if($monthShares->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-regular fa-calendar-xmark fa-2x mb-2 text-secondary opacity-50"></i>
                            <p class="mb-0">لا توجد مقبوضات جمعيات مستحقة للشركة في هذا الشهر.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="flow-table">
                                <thead>
                                    <tr>
                                        <th>الجمعية</th>
                                        <th>السهم</th>
                                        <th>النوع والمبلغ</th>
                                        <th>الحالة</th>
                                        <th>الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthShares as $s)
                                        @php
                                            $isClaim = $s->is_transferred;
                                            $amt = $isClaim ? $s->company_claim_amount : $s->payout_amount;
                                            $done = $isClaim ? $s->is_claim_recovered : $s->is_collected;
                                            $accName = $isClaim ? $s->recovered_account_name : $s->collected_account_name;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $s->jamia_name }}</div>
                                                <small class="text-muted">المدة: {{ $s->duration_months }} شهور</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $s->share_label }}</span>
                                                @if($isClaim)
                                                    <span class="badge bg-warning-subtle text-warning border border-warning" style="font-size:0.75rem;">
                                                        <i class="fa-solid fa-user-plus"></i> متنازل عنه
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-success">{{ number_format($amt, 2) }} ج</strong>
                                                <div style="font-size:0.75rem; color:#64748b;">
                                                    {{ $isClaim ? "حق ({$s->paid_months_before_transfer} شهور مقاصة)" : "قبض سهم كامل" }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($done)
                                                    <span class="badge-status badge-collected">
                                                        <i class="fa-solid fa-circle-check"></i> تم التحصيل
                                                    </span>
                                                    <div style="font-size:0.75rem; color:#64748b;" class="mt-1">
                                                        {{ $accName ?? 'الخزنة' }}
                                                    </div>
                                                @else
                                                    <span class="badge-status badge-pending">
                                                        <i class="fa-regular fa-clock"></i> في الانتظار
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$done)
                                                    @if(!$isClaim)
                                                        <button class="btn-act btn-collect" onclick="openCollectModal({{ $s->id }}, '{{ addslashes($s->jamia_name) }}', '{{ addslashes($s->share_label) }}', {{ $amt }})">
                                                            <i class="fa-solid fa-sack-dollar"></i> قبض
                                                        </button>
                                                    @else
                                                        <button class="btn-act btn-recover" onclick="openRecoverModal({{ $s->id }}, '{{ addslashes($s->jamia_name) }}', '{{ addslashes($s->share_label) }}', {{ $amt }})">
                                                            <i class="fa-solid fa-rotate-left"></i> تحصيل الحق
                                                        </button>
                                                    @endif
                                                @else
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="text-success fw-bold" style="font-size:0.82rem;"><i class="fa-solid fa-check-double"></i> تم الإيداع</span>
                                                        <form method="POST" action="{{ route('cashflows.cancel_payout') }}" onsubmit="return confirm('هل تريد إلغاء قبض هذا السهم وخصم المبلغ من المحفظة؟');" style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="share_id" value="{{ $s->id }}">
                                                            <button type="submit" class="btn-act btn-cancel" title="إلغاء القبض">
                                                                <i class="fa-solid fa-rotate-left"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Tab 2: Jamias List ═══ --}}
    <div id="tab-jamias" class="tab-content-pane" style="display:none;">
        <div class="row g-3">
            @forelse($jamias as $jamia)
                <div class="col-xl-6">
                    <div class="jamia-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-teal">{{ $jamia->name }}</h5>
                                <div class="text-muted" style="font-size:0.85rem;">
                                    <i class="fa-regular fa-user text-secondary me-1"></i> المسؤول: <strong>{{ $jamia->organizer_name ?: 'غير محدد' }}</strong>
                                    @if($jamia->organizer_phone)
                                        | <i class="fa-solid fa-phone text-secondary me-1"></i> {{ $jamia->organizer_phone }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('cashflows.print_jamia', $jamia->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3" title="طباعة تقرير الجمعية">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                @if($jamia->calc_total_paid == 0 && $jamia->calc_collected_payout == 0)
                                    <form method="POST" action="{{ route('cashflows.destroy', $jamia->id) }}" onsubmit="return confirm('هل تريد حذف هذه الجمعية نهائياً؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="حذف الجمعية">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Stats Bar --}}
                        <div class="row g-2 mb-3 bg-light p-3 rounded-3 text-center" style="font-size:0.85rem;">
                            <div class="col-4">
                                <span class="text-muted d-block">قيمة القبض للسهم</span>
                                <strong class="text-dark">{{ number_format($jamia->total_amount, 0) }} ج</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block">المدة والقسط</span>
                                <strong class="text-dark">{{ $jamia->duration_months }} شهر ({{ number_format($jamia->monthly_installment, 0) }} ج)</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block">أسهم الشركة</span>
                                <strong class="text-teal">{{ $jamia->company_shares_count }} أسهم</strong>
                            </div>
                        </div>

                        {{-- Progress --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1" style="font-size:0.8rem;">
                                <span>نسبة السداد: <strong>{{ $jamia->paid_progress }}%</strong></span>
                                <span>مسدد: <b class="text-success">{{ number_format($jamia->calc_total_paid, 0) }} ج</b> من <b class="text-dark">{{ number_format($jamia->calc_total_due, 0) }} ج</b></span>
                            </div>
                            <div class="progress" style="height: 7px; border-radius: 4px;">
                                <div class="progress-bar bg-teal" role="progressbar" style="width: {{ $jamia->paid_progress }}%"></div>
                            </div>
                        </div>

                        {{-- Shares List Inside Jamia --}}
                        <h6 class="fw-bold mb-2" style="font-size:0.85rem;"><i class="fa-solid fa-users text-teal me-1"></i> أسهم الشركة ومواعيد القبض:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($jamia->shares as $sh)
                                @php
                                    $pDate = \Carbon\Carbon::parse($sh->payout_month)->locale('ar')->isoFormat('MMMM YYYY');
                                @endphp
                                <div class="share-pill {{ $sh->is_collected ? 'collected' : '' }} {{ $sh->is_transferred ? 'transferred' : '' }}">
                                    <span>{{ $sh->share_label }}</span>
                                    <small class="text-muted">({{ $pDate }})</small>
                                    @if($sh->is_transferred)
                                        <span class="badge bg-warning-subtle text-warning">متنازل عنه</span>
                                    @elseif($sh->is_collected)
                                        <span class="badge bg-success-subtle text-success">مقبوض</span>
                                    @else
                                        <button class="btn btn-sm text-secondary p-0 ms-1" onclick="openTransferModal({{ $sh->id }}, '{{ addslashes($jamia->name) }}', '{{ addslashes($sh->share_label) }}')" title="تنازل عن هذا السهم لطرف آخر">
                                            <i class="fa-solid fa-user-pen text-warning"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">لم يتم تسجيل أي جمعيات حتى الآن. اضغط على "إضافة جمعية جديدة" للبدء.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═══ Tab 3: All Shares & Transfers (المقاصات) ═══ --}}
    <div id="tab-shares" class="tab-content-pane" style="display:none;">
        <div class="chart-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="box-title mb-0">
                    <i class="fa-solid fa-users-viewfinder text-teal"></i> جميع أسهم وحصص الشركة بالجمعيات
                </div>
                <div class="text-muted" style="font-size:0.85rem;">
                    إجمالي الأسهم: <strong>{{ $allShares->count() }}</strong> | متنازل عنها: <strong class="text-warning">{{ $allShares->where('is_transferred', true)->count() }}</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="flow-table">
                    <thead>
                        <tr>
                            <th>الجمعية</th>
                            <th>السهم</th>
                            <th>شهر القبض</th>
                            <th>قيمة القبض</th>
                            <th>حالة التنازل (المقاصة)</th>
                            <th>حق الشركة المسترد</th>
                            <th>حالة القبض / التحصيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allShares as $sh)
                            <tr>
                                <td>
                                    <strong class="text-teal">{{ $sh->jamia->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $sh->share_label }}</span>
                                </td>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($sh->payout_month)->locale('ar')->isoFormat('MMMM YYYY') }}</strong>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ number_format($sh->payout_amount, 2) }} ج</span>
                                </td>
                                <td>
                                    @if($sh->is_transferred)
                                        <span class="badge-status badge-transferred">
                                            <i class="fa-solid fa-user-tag"></i> متنازل عنه
                                        </span>
                                        <div style="font-size:0.75rem; color:#64748b;" class="mt-1">
                                            سددت الشركة: {{ $sh->paid_months_before_transfer }} شهور
                                            @if($sh->transferred_to_name)
                                                ({{ $sh->transferred_to_name }})
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge-status badge-paid">
                                            <i class="fa-solid fa-shield-halved"></i> ملك الشركة
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($sh->is_transferred)
                                        <strong class="text-primary">{{ number_format($sh->company_claim_amount, 2) }} ج</strong>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sh->is_transferred)
                                        @if($sh->is_claim_recovered)
                                            <span class="badge-status badge-collected">
                                                <i class="fa-solid fa-check-double"></i> تم استرداد الحق
                                            </span>
                                            <div style="font-size:0.75rem; color:#64748b;" class="mt-1">
                                                {{ $sh->recoveredAccount->account_name ?? 'الخزنة' }}
                                            </div>
                                        @else
                                            <span class="badge-status badge-pending">
                                                <i class="fa-regular fa-clock"></i> في انتظار موعد القبض
                                            </span>
                                        @endif
                                    @else
                                        @if($sh->is_collected)
                                            <span class="badge-status badge-collected">
                                                <i class="fa-solid fa-circle-check"></i> تم القبض
                                            </span>
                                            <div style="font-size:0.75rem; color:#64748b;" class="mt-1">
                                                {{ $sh->collectedAccount->account_name ?? 'الخزنة' }}
                                            </div>
                                        @else
                                            <span class="badge-status badge-pending">
                                                <i class="fa-regular fa-clock"></i> لم يقبض بعد
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if(!$sh->is_transferred && !$sh->is_collected)
                                        <button class="btn-act btn-transfer" onclick="openTransferModal({{ $sh->id }}, '{{ addslashes($sh->jamia->name) }}', '{{ addslashes($sh->share_label) }}')">
                                            <i class="fa-solid fa-user-plus"></i> تنازل
                                        </button>
                                    @elseif($sh->is_transferred && !$sh->is_claim_recovered)
                                        <button class="btn-act btn-recover" onclick="openRecoverModal({{ $sh->id }}, '{{ addslashes($sh->jamia->name) }}', '{{ addslashes($sh->share_label) }}', {{ $sh->company_claim_amount }})">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> تحصيل الحق
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('cashflows.cancel_payout') }}" onsubmit="return confirm('هل تريد إلغاء هذه الحركة؟');" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="share_id" value="{{ $sh->id }}">
                                            <button type="submit" class="btn-act btn-cancel" title="إلغاء التحصيل">
                                                <i class="fa-solid fa-rotate-left"></i> إلغاء
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">لا توجد أسهم مسجلة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════ --}}

{{-- Modal 1: إضافة جمعية جديدة --}}
<div class="modal fade" id="newJamiaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('cashflows.store') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            @csrf
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-teal"><i class="fa-solid fa-plus-circle me-1"></i> تسجيل جمعية جديدة في النظام</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم الجمعية <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثلاً: جمعية 100 ألف - الحاج محمود" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">مسؤول/منظم الجمعية</label>
                        <input type="text" name="organizer_name" class="form-control" placeholder="اختياري">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">رقم هاتف المنظم</label>
                        <input type="text" name="organizer_phone" class="form-control" placeholder="اختياري">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">قيمة قبض السهم الواحد <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="total_amount" id="input_total_amount" class="form-control" placeholder="مثلاً: 100000" required oninput="calcMonthlyInstallment()">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">مدة الجمعية (بالأشهر) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_months" id="input_duration" class="form-control" placeholder="مثلاً: 10 أو 12" required oninput="calcMonthlyInstallment()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">القسط الشهري للسهم (تلقائي)</label>
                        <div class="input-group">
                            <input type="text" id="display_installment" class="form-control bg-light" readonly placeholder="0.00">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">عدد أسهم الشركة في الجمعية <span class="text-danger">*</span></label>
                        <input type="number" name="company_shares_count" id="input_shares_count" class="form-control" value="1" min="1" max="20" required onchange="generateShareRows()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">تاريخ أول شهر للجمعية <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="input_start_date" class="form-control" value="{{ now()->format('Y-m-01') }}" required onchange="generateShareRows()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">يوم الاستحقاق الشهري</label>
                        <input type="number" name="due_day" class="form-control" value="1" min="1" max="31">
                    </div>

                    <div class="col-12">
                        <hr class="my-2">
                        <h6 class="fw-bold text-teal mb-2"><i class="fa-solid fa-calendar-alt me-1"></i> تحديد مواعيد قبض أسهم الشركة:</h6>
                        <div id="shares_container" class="row g-2">
                            {{-- Generated by JS --}}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">ملاحظات إضافية</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="أي تفاصيل أو ملاحظات خاصة بالجمعية..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-teal fw-bold px-4" style="background:#0d9488; color:#fff;">حفظ وتوليد الأقساط</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 2: سداد قسط جمعية --}}
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.pay') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            @csrf
            <input type="hidden" name="installment_id" id="pay_installment_id">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-money-bill-wave me-1"></i> سداد قسط جمعية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="pay_jamia_info">—</div>
                    <div class="text-danger fw-bold fs-5 mt-1" id="pay_amount_display">0.00 ج</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">خصم المبلغ من محفظة / خزنة: <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select" required>
                        <option value="">-- اختر المحفظة أو الخزنة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">تاريخ السداد</label>
                    <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات السداد</label>
                    <input type="text" name="notes" class="form-control" placeholder="اختياري">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-danger fw-bold px-4">تأكيد السداد والخصم</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 3: قبض سهم الجمعية --}}
<div class="modal fade" id="collectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.collect') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            @csrf
            <input type="hidden" name="share_id" id="collect_share_id">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-sack-dollar me-1"></i> قبض سهم جمعية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="collect_jamia_info">—</div>
                    <div class="text-success fw-bold fs-5 mt-1" id="collect_amount_display">0.00 ج</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">إيداع المبلغ في محفظة / خزنة: <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select" required>
                        <option value="">-- اختر المحفظة أو الخزنة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ الفعلي المقبوض <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" id="collect_amount_input" class="form-control" required>
                        <span class="input-group-text">ج</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">تاريخ استلام القبض</label>
                    <input type="date" name="collected_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات الإيداع</label>
                    <input type="text" name="notes" class="form-control" placeholder="اختياري">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-success fw-bold px-4">تأكيد القبض والإيداع</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 4: التنازل عن سهم لطرف آخر (في أي وقت) --}}
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.transfer') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            @csrf
            <input type="hidden" name="share_id" id="transfer_share_id">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-1"></i> تسجيل تنازل عن سهم لطرف آخر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border mb-3" style="font-size:0.88rem;">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    <strong>آلية التنازل والمقاصة:</strong>
                    الشركة ستتوقف عن سداد الأقساط القادمة لهذا السهم، وعند حلول موعد قبض الجمعية، ستسترد الشركة من مبلغ القبض حق الشهور التي سددتها بالفعل فقط.
                </div>

                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="transfer_jamia_info">—</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">التنازل يبدأ من شهر: (اختياري)</label>
                    <input type="month" name="transferred_from_month" class="form-control">
                    <small class="text-muted">إذا ترك فارغاً، سيتم إسقاط جميع الأقساط التي لم تسددها الشركة بعد.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">اسم الشخص أو الطرف الذي دخل السهم: (اختياري)</label>
                    <input type="text" name="transferred_to_name" class="form-control" placeholder="اختياري تماماً (للتوثيق فقط)">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات التنازل</label>
                    <textarea name="transferred_notes" class="form-control" rows="2" placeholder="أي تفاصيل أو ملاحظات حول الاتفاق..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-warning fw-bold px-4">تأكيد التنازل وإسقاط الأقساط</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 5: استرداد حق الشركة من سهم متنازل عنه (مقاصة القبض) --}}
<div class="modal fade" id="recoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.recover') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            @csrf
            <input type="hidden" name="share_id" id="recover_share_id">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrow-rotate-left me-1"></i> استرداد حق الشركة من قبض الجمعية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="recover_jamia_info">—</div>
                    <div class="text-primary fw-bold fs-5 mt-1" id="recover_amount_display">0.00 ج</div>
                    <small class="text-muted">هذا هو إجمالي المبالغ التي دفعتها الشركة مسبقاً لهذا السهم قبل التنازل عنه.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">إيداع المبلغ المسترد في محفظة / خزنة: <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select" required>
                        <option value="">-- اختر المحفظة أو الخزنة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ المسترد الفعلي <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" id="recover_amount_input" class="form-control" required>
                        <span class="input-group-text">ج</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">تاريخ الاسترداد</label>
                    <input type="date" name="recovered_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" placeholder="اختياري">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary fw-bold px-4">تأكيد استرداد الحق والإيداع</button>
            </div>
        </form>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ── Tab Switching ──
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content-pane').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.custom-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).style.display = 'block';
        btn.classList.add('active');
    }

    // ── Dynamic Installment Calculation ──
    function calcMonthlyInstallment() {
        const total = parseFloat(document.getElementById('input_total_amount').value) || 0;
        const duration = parseInt(document.getElementById('input_duration').value) || 0;
        if (total > 0 && duration > 0) {
            const installment = (total / duration).toFixed(2);
            document.getElementById('display_installment').value = installment;
        } else {
            document.getElementById('display_installment').value = '0.00';
        }
    }

    // ── Generate Share Input Rows ──
    function generateShareRows() {
        const count = parseInt(document.getElementById('input_shares_count').value) || 1;
        const startDateVal = document.getElementById('input_start_date').value;
        const container = document.getElementById('shares_container');
        container.innerHTML = '';

        const baseDate = startDateVal ? new Date(startDateVal) : new Date();

        for (let i = 1; i <= count; i++) {
            const defaultDate = new Date(baseDate);
            defaultDate.setMonth(defaultDate.getMonth() + (i - 1));
            const y = defaultDate.getFullYear();
            const m = String(defaultDate.getMonth() + 1).padStart(2, '0');
            const defaultMonthStr = `${y}-${m}`;

            const col = document.createElement('div');
            col.className = 'col-md-6';
            col.innerHTML = `
                <div class="p-3 bg-light border rounded-3">
                    <label class="form-label fw-bold mb-1" style="font-size:0.85rem;">اسم/وصف السهم ${i}</label>
                    <input type="text" name="shares[${i-1}][label]" class="form-control mb-2" value="الاسم ${i}" required>
                    <label class="form-label fw-bold mb-1" style="font-size:0.85rem;">شهر استحقاق القبض</label>
                    <input type="month" name="shares[${i-1}][payout_month]" class="form-control" value="${defaultMonthStr}" required>
                </div>
            `;
            container.appendChild(col);
        }
    }

    // Initialize share rows on load
    document.addEventListener('DOMContentLoaded', function() {
        generateShareRows();
    });

    // ── Modals Helpers ──
    function openPayModal(instId, jamiaName, shareLabel, amount) {
        document.getElementById('pay_installment_id').value = instId;
        document.getElementById('pay_jamia_info').innerText = `${jamiaName} — (${shareLabel})`;
        document.getElementById('pay_amount_display').innerText = `${parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2})} ج`;
        new bootstrap.Modal(document.getElementById('payModal')).show();
    }

    function openCollectModal(shareId, jamiaName, shareLabel, amount) {
        document.getElementById('collect_share_id').value = shareId;
        document.getElementById('collect_jamia_info').innerText = `${jamiaName} — (${shareLabel})`;
        document.getElementById('collect_amount_display').innerText = `${parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2})} ج`;
        document.getElementById('collect_amount_input').value = amount;
        new bootstrap.Modal(document.getElementById('collectModal')).show();
    }

    function openTransferModal(shareId, jamiaName, shareLabel) {
        document.getElementById('transfer_share_id').value = shareId;
        document.getElementById('transfer_jamia_info').innerText = `${jamiaName} — (${shareLabel})`;
        new bootstrap.Modal(document.getElementById('transferModal')).show();
    }

    function openRecoverModal(shareId, jamiaName, shareLabel, claimAmount) {
        document.getElementById('recover_share_id').value = shareId;
        document.getElementById('recover_jamia_info').innerText = `${jamiaName} — (${shareLabel})`;
        document.getElementById('recover_amount_display').innerText = `${parseFloat(claimAmount).toLocaleString('en-US', {minimumFractionDigits: 2})} ج`;
        document.getElementById('recover_amount_input').value = claimAmount;
        new bootstrap.Modal(document.getElementById('recoverModal')).show();
    }

    // ── Charts Initialization ──
    document.addEventListener('DOMContentLoaded', function() {
        const labels = {!! json_encode($chartLabels) !!};
        const payable = {!! json_encode($chartPayable) !!};
        const receivable = {!! json_encode($chartReceivable) !!};
        const netFlow = {!! json_encode($chartNet) !!};

        // 1. Annual Cashflow Chart
        const ctxYear = document.getElementById('cashflowYearChart').getContext('2d');
        new Chart(ctxYear, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'المقبوض المتوقع (الداخل)',
                        data: receivable,
                        backgroundColor: 'rgba(16, 185, 129, 0.75)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 6,
                        order: 2
                    },
                    {
                        label: 'المطالب به (الخارج)',
                        data: payable,
                        backgroundColor: 'rgba(239, 68, 68, 0.75)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 6,
                        order: 3
                    },
                    {
                        label: 'صافي التدفق الشهري',
                        data: netFlow,
                        type: 'line',
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.15)',
                        borderWidth: 2.5,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', rtl: true, labels: { font: { family: 'Cairo, sans-serif', weight: 'bold' } } },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(ctx) {
                                return ` ${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString()} ج`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return Number(v).toLocaleString() + ' ج'; },
                            font: { family: 'Cairo, sans-serif' }
                        }
                    },
                    x: {
                        ticks: { font: { family: 'Cairo, sans-serif', weight: 'bold' } }
                    }
                }
            }
        });

        // 2. Month Ratio Doughnut Chart
        const ctxRatio = document.getElementById('monthRatioChart').getContext('2d');
        new Chart(ctxRatio, {
            type: 'doughnut',
            data: {
                labels: ['المسدد فعلياً', 'المتبقي دفعه', 'المحصل فعلياً', 'المتبقي قبضه'],
                datasets: [{
                    data: [
                        {{ $totalPaidThisMonth }},
                        {{ $remainingDueThisMonth }},
                        {{ $collectedInflowThisMonth }},
                        {{ $remainingInflowThisMonth }}
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#fee2e2',
                        '#0d9488',
                        '#fef3c7'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: { boxWidth: 12, font: { family: 'Cairo, sans-serif', size: 11 } }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(ctx) {
                                return ` ${ctx.label}: ${Number(ctx.raw).toLocaleString()} ج`;
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    });
</script>

</body>
</html>
