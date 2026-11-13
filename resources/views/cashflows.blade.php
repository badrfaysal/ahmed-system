<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الجمعيات (تحصيل وصرف) - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

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
        .kpi-card.orange::before { background: #f97316; }

        .kpi-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .kpi-title { font-size: 0.85rem; font-weight: 600; color: #64748b; margin: 0; }
        .kpi-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 1rem;
        }
        .kpi-icon.red    { background: #fee2e2; color: #ef4444; }
        .kpi-icon.green  { background: #d1fae5; color: #10b981; }
        .kpi-icon.teal   { background: #ccfbf1; color: #0d9488; }
        .kpi-icon.orange { background: #ffedd5; color: #f97316; }

        .kpi-val { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .kpi-sub { font-size: 0.8rem; color: #64748b; font-weight: 500; display: flex; justify-content: space-between; gap: 6px; flex-wrap: wrap; }

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
            border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; gap: 10px; display: flex; flex-wrap: wrap;
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
            padding: 12px 14px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;
        }
        .flow-table td {
            padding: 11px 14px; border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem; vertical-align: middle; color: #1e293b;
        }
        .flow-table tr:hover td { background: #fdfefe; }
        .flow-table tr.row-paid td { background: #f7fef9; }
        .flow-table tr.row-late td { background: #fff7f7; }

        .badge-status {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; white-space: nowrap;
        }
        .badge-paid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-pending { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-open { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-collected { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-waived { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-company { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
        .badge-transferred { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }

        .btn-act {
            border: none; border-radius: 8px; padding: 6px 12px; font-size: 0.82rem;
            font-weight: 600; cursor: pointer; transition: 0.2s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-pay { background: #0d9488; color: #fff; }
        .btn-pay:hover { background: #0f766e; color: #fff; }
        .btn-collect { background: #10b981; color: #fff; }
        .btn-collect:hover { background: #059669; color: #fff; }
        .btn-payout { background: #ef4444; color: #fff; }
        .btn-payout:hover { background: #dc2626; color: #fff; }
        .btn-recover { background: #0284c7; color: #fff; }
        .btn-recover:hover { background: #0369a1; color: #fff; }
        .btn-transfer { background: #7c3aed; color: #fff; }
        .btn-transfer:hover { background: #6d28d9; color: #fff; }
        .btn-edit { background: #f1f5f9; color: #334155; }
        .btn-edit:hover { background: #e2e8f0; }
        .btn-cancel { background: #fee2e2; color: #b91c1c; }
        .btn-cancel:hover { background: #fecaca; color: #991b1b; }
        .btn-act:disabled { opacity: .5; cursor: not-allowed; }

        .jamia-card {
            background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
            padding: 20px; margin-bottom: 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .jamia-card:hover { border-color: #0d9488; box-shadow: 0 4px 18px rgba(13,148,136,0.08); }

        .jamia-tile {
            background: #fff; border-radius: 16px; border: 1.5px solid #e2e8f0;
            padding: 18px; cursor: pointer; transition: all 0.2s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            display: flex; flex-direction: column; justify-content: space-between; height: 100%;
        }
        .jamia-tile:hover {
            border-color: #0d9488; transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(13,148,136,0.12);
        }
        .jamia-tile-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;
            background: #f8fafc; border-radius: 12px; padding: 10px 12px; margin: 10px 0;
        }
        .jamia-tile-stat small { display: block; color: #64748b; font-size: 0.74rem; }
        .jamia-tile-stat strong { font-size: 0.92rem; color: #0f172a; }
        .jamia-tile-footer {
            border-top: 1px dashed #e2e8f0; padding-top: 10px; margin-top: 10px;
            display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem;
        }

        .window-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 10px; font-size: 0.82rem; font-weight: 700;
        }
        .window-chip.open { background: #fef3c7; color: #92400e; }
        .window-chip.closed { background: #fee2e2; color: #991b1b; }
        .window-chip.upcoming { background: #f1f5f9; color: #475569; }

        .payout-box {
            border: 1px dashed #fca5a5; background: #fffbfb; border-radius: 12px;
            padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .payout-box.company { border-color: #7dd3fc; background: #f8fcff; }
        .payout-box.done { border-color: #86efac; background: #f7fef9; }

        .turn-num {
            width: 30px; height: 30px; border-radius: 50%; background: #ccfbf1; color: #0f766e;
            display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .85rem;
        }
        .turn-num.company { background: #e0f2fe; color: #075985; }

        /* ── New Jamia members grid ── */
        .members-table th { font-size: .82rem; background: #f8fafc; white-space: nowrap; }
        .members-table td { vertical-align: middle; padding: 6px 8px; }
        .members-table tr.is-company td { background: #f0f9ff; }
        .members-table input.form-control, .members-table input.form-control-sm { font-size: .88rem; }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">

    {{-- Alerts --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'تمت العملية بنجاح', text: @json(session('success')), timer: 3000, showConfirmButton: false });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'error', title: 'خطأ', text: @json(session('error')) });
            });
        </script>
    @endif
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'error', title: 'راجع البيانات', html: @json(implode('<br>', array_map('e', $errors->all()))) });
            });
        </script>
    @endif

    {{-- ═══ Hero Header ═══ --}}
    <div class="flow-hero">
        <div class="hero-block">
            <div class="hero-icon"><i class="fa-solid fa-handshake"></i></div>
            <div>
                <h2>الجمعيات — التحصيل والصرف</h2>
                <div class="sub">{{ $companyName }} بتلم الجمعية: تسجيل الأعضاء وأدوار القبض، التحصيل من يوم 1 لغاية يوم 10، ومتابعة مين دفع ومين لسه</div>
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
                <i class="fa-solid fa-chart-pie text-teal me-1"></i> شهر العرض: <strong>{{ $monthDate->locale('ar')->isoFormat('MMMM YYYY') }}</strong>
            </span>
        </div>
    </div>

    {{-- ═══ Monthly KPI Cards ═══ --}}
    <div class="kpi-grid">
        {{-- المطلوب تحصيله من الأعضاء (الداخل) --}}
        <div class="kpi-card green">
            <div class="kpi-head">
                <h6 class="kpi-title">المطلوب تحصيله من الأعضاء (الداخل)</h6>
                <div class="kpi-icon green"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            </div>
            <div class="kpi-val text-success">{{ number_format($expectedCollection, 2) }} <small style="font-size:0.9rem; font-weight:600;">ج</small></div>
            <div class="kpi-sub">
                <span>اتحصل: <b class="text-success">{{ number_format($collectedThisMonth, 0) }} ج</b></span>
                <span>متبقي: <b class="text-danger">{{ number_format($remainingCollection, 0) }} ج</b></span>
            </div>
            <div class="kpi-sub mt-1">
                <span>دفعوا: <b class="text-success">{{ $paidMembersCount }}</b></span>
                <span>لسه: <b class="text-danger">{{ $unpaidMembersCount }}</b></span>
            </div>
        </div>

        {{-- القبض المستحق صرفه (الخارج) --}}
        <div class="kpi-card red">
            <div class="kpi-head">
                <h6 class="kpi-title">القبض المستحق صرفه للأعضاء (الخارج)</h6>
                <div class="kpi-icon red"><i class="fa-solid fa-sack-dollar"></i></div>
            </div>
            <div class="kpi-val text-danger">{{ number_format($payoutDue, 2) }} <small style="font-size:0.9rem; font-weight:600;">ج</small></div>
            <div class="kpi-sub">
                <span>اتصرف: <b class="text-success">{{ number_format($payoutDone, 0) }} ج</b></span>
                <span>متبقي: <b class="text-danger">{{ number_format($payoutRemaining, 0) }} ج</b></span>
            </div>
        </div>

        {{-- المتأخرين --}}
        <div class="kpi-card orange">
            <div class="kpi-head">
                <h6 class="kpi-title">المتأخرين عن السداد</h6>
                <div class="kpi-icon orange"><i class="fa-solid fa-user-clock"></i></div>
            </div>
            <div class="kpi-val" style="color:#ea580c;">{{ $lateCountThisMonth }} <small style="font-size:0.9rem; font-weight:600;">عضو هذا الشهر</small></div>
            <div class="kpi-sub">
                <span>بمبلغ: <b class="text-danger">{{ number_format($lateAmountThisMonth, 0) }} ج</b></span>
                <span>كل المتأخرات: <b class="text-danger">{{ $globalStats['overdue_count'] }}</b> ({{ number_format($globalStats['overdue_amount'], 0) }} ج)</span>
            </div>
        </div>

        {{-- صافي الشهر --}}
        <div class="kpi-card teal">
            <div class="kpi-head">
                <h6 class="kpi-title">صافي الشهر (التحصيل - الصرف)</h6>
                <div class="kpi-icon teal"><i class="fa-solid fa-scale-balanced"></i></div>
            </div>
            <div class="kpi-val {{ $netExpectedThisMonth >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $netExpectedThisMonth >= 0 ? '+' : '' }}{{ number_format($netExpectedThisMonth, 2) }} <small style="font-size:0.9rem; font-weight:600;">ج</small>
            </div>
            <div class="kpi-sub">
                <span>الفعلي: <b class="{{ $netActualThisMonth >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netActualThisMonth, 0) }} ج</b></span>
                <span>جمعيات نشطة: <b>{{ $globalStats['active_jamias_count'] }}</b></span>
            </div>
        </div>
    </div>

    {{-- ═══ Annual Charts ═══ --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="chart-box h-100 mb-0">
                <div class="box-title">
                    <i class="fa-solid fa-chart-column text-teal"></i> التحصيل المتوقع vs القبض المصروف لسنة {{ $monthDate->year }}
                </div>
                <div style="height: 290px; position: relative;">
                    <canvas id="cashflowYearChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-box h-100 mb-0">
                <div class="box-title">
                    <i class="fa-solid fa-chart-pie text-teal"></i> موقف الشهر
                </div>
                <div style="height: 230px; position: relative;">
                    <canvas id="monthRatioChart"></canvas>
                </div>
                <div class="text-center mt-2 text-muted" style="font-size:0.82rem;">
                    التحصيل والصرف لشهر {{ $monthDate->locale('ar')->isoFormat('MMMM YYYY') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Tabs Navigation ═══ --}}
    <div class="custom-tabs">
        <button class="custom-tab-btn active" data-tab="tab-month" onclick="switchTab('tab-month', this)">
            <i class="fa-solid fa-calendar-check"></i> تحصيل شهر {{ $monthDate->locale('ar')->isoFormat('MMMM') }} ({{ $monthJamias->count() }} جمعية)
        </button>
        <button class="custom-tab-btn" data-tab="tab-jamias" onclick="switchTab('tab-jamias', this)">
            <i class="fa-solid fa-users"></i> دليل الجمعيات والأعضاء ({{ $jamias->count() }})
        </button>
        <button class="custom-tab-btn" data-tab="tab-overdue" onclick="switchTab('tab-overdue', this)">
            <i class="fa-solid fa-user-clock"></i> المتأخرين ({{ $overdueInstallments->count() }})
        </button>
    </div>

    {{-- ═══ Tab 1: تحصيل الشهر لكل جمعية (مربعات + بوبب تفاصيل) ═══ --}}
    <div id="tab-month" class="tab-content-pane">
        @if($monthJamias->isNotEmpty())
            <div class="filter-strip py-2 px-3 mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-secondary" style="font-size:.86rem;"><i class="fa-solid fa-filter text-teal me-1"></i> تصفية:</span>
                    <button type="button" class="btn btn-sm btn-teal fw-bold filter-pill active" style="background:#0d9488;color:#fff;" onclick="setMonthFilter('all', this)">الكل ({{ $monthJamias->count() }})</button>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold filter-pill" onclick="setMonthFilter('unpaid', this)">فيها ألسه ما دفعوش ({{ $unpaidMembersCount }})</button>
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold filter-pill" onclick="setMonthFilter('paid', this)">اللي دفعوا ({{ $paidMembersCount }})</button>
                    <button type="button" class="btn btn-sm btn-outline-warning fw-bold filter-pill" onclick="setMonthFilter('late', this)">فيها متأخرين ({{ $lateCountThisMonth }})</button>
                </div>
                <div style="min-width:250px;">
                    <input type="search" id="monthMemberSearch" class="form-control form-control-sm" placeholder="ابحث باسم الجمعية أو العضو أو الهاتف..." oninput="applyMonthFilters()">
                </div>
            </div>
        @endif

        @if($monthJamias->isEmpty())
            <div class="chart-box text-center py-5 text-muted">
                <i class="fa-regular fa-calendar-xmark fa-2x mb-2 text-secondary opacity-50"></i>
                <p class="mb-0">مفيش جمعيات شغالة في الشهر ده. اضغط "إضافة جمعية جديدة" للبدء.</p>
            </div>
        @else
            <div class="row g-3" id="monthJamiasGrid">
                @foreach($monthJamias as $jamia)
                    @php
                        $startDay   = max(1, (int) ($jamia->collection_start_day ?? 1));
                        $endDay     = min(10, max($startDay, (int) ($jamia->collection_end_day ?? 10)));
                        $winFrom    = $monthDate->copy()->day($startDay)->startOfDay();
                        $winTo      = $monthDate->copy()->day($endDay)->endOfDay();
                        $insts      = $jamia->month_installments;
                        $paidCnt    = $insts->where('is_paid', true)->count();
                        $allCnt     = $insts->count();
                        $pct        = $allCnt > 0 ? round($paidCnt / $allCnt * 100) : 100;
                        $unpaid     = $insts->where('is_paid', false);
                        $lateCnt    = $insts->filter(fn ($i) => $i->status['key'] === 'late')->count();
                        $daysLeft   = (int) now()->startOfDay()->diffInDays($winTo->copy()->startOfDay());
                        $tileSearch = mb_strtolower($jamia->name . ' ' . $insts->pluck('share_label')->implode(' ') . ' ' . $insts->pluck('member_phone')->implode(' ') . ' ' . $jamia->month_payouts->pluck('share_label')->implode(' '));
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4 month-jamia-tile-wrap"
                         data-has-unpaid="{{ $unpaid->isNotEmpty() ? '1' : '0' }}"
                         data-has-paid="{{ $paidCnt > 0 ? '1' : '0' }}"
                         data-has-late="{{ $lateCnt > 0 ? '1' : '0' }}"
                         data-search="{{ $tileSearch }}">
                        <div class="jamia-tile" onclick="openJamiaPopup('monthJamiaModal-{{ $jamia->id }}')">
                            <div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1 text-teal">{{ $jamia->name }}</h5>
                                        <small class="text-muted">الشهر <b class="text-dark">{{ $jamia->month_index }}</b> من <b class="text-dark">{{ $jamia->duration_months }}</b></small>
                                    </div>
                                    <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                                        يوم {{ $startDay }} ← {{ $endDay }}
                                    </span>
                                </div>

                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @if(now()->lt($winFrom))
                                        <span class="badge-status badge-waived" style="font-size:0.72rem;"><i class="fa-regular fa-hourglass"></i> لم يبدأ التحصيل</span>
                                    @elseif(now()->lte($winTo))
                                        <span class="badge-status badge-open" style="font-size:0.72rem;"><i class="fa-solid fa-hourglass-half"></i> {{ $daysLeft === 0 ? 'آخر يوم النهارده!' : "باقي {$daysLeft} يوم" }}</span>
                                    @else
                                        <span class="badge-status badge-pending" style="font-size:0.72rem;"><i class="fa-solid fa-lock"></i> انتهت الفترة</span>
                                    @endif

                                    @if($lateCnt > 0)
                                        <span class="badge-status badge-pending" style="font-size:0.72rem;"><i class="fa-solid fa-triangle-exclamation"></i> {{ $lateCnt }} متأخر</span>
                                    @elseif($unpaid->isEmpty())
                                        <span class="badge-status badge-paid" style="font-size:0.72rem;"><i class="fa-solid fa-check-double"></i> مكتمل التحصيل</span>
                                    @endif
                                </div>

                                <div class="jamia-tile-grid">
                                    <div class="jamia-tile-stat">
                                        <small>دفع / إجمالي الأعضاء</small>
                                        <strong><span class="text-success">{{ $paidCnt }}</span> / {{ $allCnt }}</strong>
                                    </div>
                                    <div class="jamia-tile-stat">
                                        <small>اتحصل / المطلوب</small>
                                        <strong>{{ number_format($insts->where('is_paid', true)->sum('amount'), 0) }} <small class="d-inline text-muted">/ {{ number_format($insts->sum('amount'), 0) }} ج</small></strong>
                                    </div>
                                    <div class="jamia-tile-stat">
                                        <small>قيمة القبض</small>
                                        <strong>{{ number_format($jamia->total_amount, 0) }} ج</strong>
                                    </div>
                                    <div class="jamia-tile-stat">
                                        <small>القسط الشهري</small>
                                        <strong>{{ number_format($jamia->monthly_installment, 0) }} ج</strong>
                                    </div>
                                </div>

                                <div class="progress mb-2" style="height:6px; border-radius:4px;">
                                    <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                </div>

                                @foreach($jamia->month_payouts as $p)
                                    <div class="d-flex justify-content-between align-items-center bg-light rounded-3 px-2 py-1 mt-1" style="font-size:0.78rem;">
                                        <span class="fw-bold text-truncate">
                                            <i class="fa-solid fa-sack-dollar text-danger me-1"></i>
                                            دور {{ $p->share_number }}: {{ $p->share_label }}
                                            @if($p->is_company) <span class="text-primary">(الشركة)</span> @endif
                                        </span>
                                        @if($p->is_collected)
                                            <span class="badge bg-success">{{ $p->is_company ? 'استلمت' : 'اتصرف' }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">لسه</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="jamia-tile-footer">
                                <span class="text-teal fw-bold"><i class="fa-solid fa-up-right-from-square me-1"></i> اضغط لفتح كافة البيانات</span>
                                <span class="text-muted">{{ $jamia->shares->count() }} أسهم</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- بوبب التفاصيل لكل جمعية في الشهر المختار --}}
            @foreach($monthJamias as $jamia)
                @php
                    $startDay = max(1, (int) ($jamia->collection_start_day ?? 1));
                    $endDay   = min(10, max($startDay, (int) ($jamia->collection_end_day ?? 10)));
                    $winFrom  = $monthDate->copy()->day($startDay)->startOfDay();
                    $winTo    = $monthDate->copy()->day($endDay)->endOfDay();
                    $insts    = $jamia->month_installments;
                    $paidCnt  = $insts->where('is_paid', true)->count();
                    $allCnt   = $insts->count();
                    $pct      = $allCnt > 0 ? round($paidCnt / $allCnt * 100) : 100;
                    $unpaid   = $insts->where('is_paid', false);
                    $daysLeft = (int) now()->startOfDay()->diffInDays($winTo->copy()->startOfDay());
                    $mModalId = 'monthJamiaModal-' . $jamia->id;
                @endphp
                <div class="modal fade month-jamia-modal" id="{{ $mModalId }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
                            <div class="modal-header bg-light">
                                <div>
                                    <h5 class="modal-title fw-bold text-teal mb-0">{{ $jamia->name }} — تحصيل {{ $monthDate->locale('ar')->isoFormat('MMMM YYYY') }}</h5>
                                    <small class="text-muted">
                                        الشهر <b class="text-dark">{{ $jamia->month_index }}</b> من <b class="text-dark">{{ $jamia->duration_months }}</b>
                                        | قسط العضو: <b class="text-dark">{{ number_format($jamia->monthly_installment, 2) }} ج</b>
                                        | القبض: <b class="text-dark">{{ number_format($jamia->total_amount, 2) }} ج</b>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if(now()->lt($winFrom))
                                        <span class="window-chip upcoming"><i class="fa-regular fa-hourglass"></i> التحصيل من يوم {{ $startDay }} لغاية يوم {{ $endDay }} — لسه ما بدأش</span>
                                    @elseif(now()->lte($winTo))
                                        <span class="window-chip open"><i class="fa-solid fa-hourglass-half"></i> التحصيل (يوم {{ $startDay }} ← {{ $endDay }}) — {{ $daysLeft === 0 ? 'آخر يوم النهارده!' : "باقي {$daysLeft} يوم" }}</span>
                                    @else
                                        <span class="window-chip closed"><i class="fa-solid fa-lock"></i> انتهت فترة التحصيل يوم {{ $endDay }}</span>
                                    @endif
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                            </div>

                            <div class="modal-body p-4">
                                {{-- Progress --}}
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1" style="font-size:0.84rem;">
                                        <span>دفعوا: <b class="text-success">{{ $paidCnt }}</b> من <b>{{ $allCnt }}</b> عضو
                                            @if($jamia->company_shares_in > 0)
                                                <span class="text-muted">(+ {{ $jamia->company_shares_in }} سهم لـ{{ $companyName }} لا يحتاج تحصيل)</span>
                                            @endif
                                        </span>
                                        <span>اتحصل: <b class="text-success">{{ number_format($insts->where('is_paid', true)->sum('amount'), 2) }} ج</b> من <b>{{ number_format($insts->sum('amount'), 2) }} ج</b></span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>

                                @if($insts->isEmpty())
                                    <div class="text-center text-muted py-3 border rounded-3 bg-light">كل أسهم الجمعية دي ملك {{ $companyName }} — مفيش تحصيل أقساط من أعضاء.</div>
                                @else
                                    {{-- In-modal Filter & Search --}}
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-dark modal-filter-btn active" onclick="filterModalRows({{ $jamia->id }}, 'all', this)">الكل ({{ $allCnt }})</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger modal-filter-btn" onclick="filterModalRows({{ $jamia->id }}, 'unpaid', this)">لسه ({{ $unpaid->count() }})</button>
                                            <button type="button" class="btn btn-sm btn-outline-success modal-filter-btn" onclick="filterModalRows({{ $jamia->id }}, 'paid', this)">دفعوا ({{ $paidCnt }})</button>
                                            <button type="button" class="btn btn-sm btn-outline-warning modal-filter-btn" onclick="filterModalRows({{ $jamia->id }}, 'late', this)">متأخرين</button>
                                        </div>
                                        <input type="search" class="form-control form-control-sm" style="max-width:240px;"
                                               id="modalSearch-{{ $jamia->id }}" placeholder="بحث داخل الجمعية..." oninput="filterModalRows({{ $jamia->id }}, null, null)">
                                    </div>

                                    <div class="table-responsive">
                                        <table class="flow-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:36px;">
                                                        <input type="checkbox" class="form-check-input" title="تحديد كل اللي لسه ما دفعوش" onchange="toggleAllUnpaid({{ $jamia->id }}, this.checked)" @disabled($unpaid->isEmpty())>
                                                    </th>
                                                    <th>الدور</th>
                                                    <th>العضو</th>
                                                    <th>الهاتف</th>
                                                    <th>القسط</th>
                                                    <th>الحالة</th>
                                                    <th>الإجراء</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($insts as $inst)
                                                    @php $st = $inst->status; @endphp
                                                    <tr class="month-member-row modal-row-{{ $jamia->id }} {{ $inst->is_paid ? 'row-paid' : ($st['key'] === 'late' ? 'row-late' : '') }}"
                                                        data-paid="{{ $inst->is_paid ? '1' : '0' }}"
                                                        data-late="{{ $st['key'] === 'late' ? '1' : '0' }}"
                                                        data-search="{{ mb_strtolower($inst->share_label . ' ' . $inst->member_phone) }}">
                                                        <td>
                                                            @if(!$inst->is_paid)
                                                                <input type="checkbox" class="form-check-input sel-inst sel-jamia-{{ $jamia->id }}"
                                                                       value="{{ $inst->id }}" data-amount="{{ $inst->amount }}"
                                                                       data-name="{{ $inst->share_label }}" onchange="refreshBulkBar({{ $jamia->id }})">
                                                            @endif
                                                        </td>
                                                        <td><span class="turn-num">{{ $inst->share_number }}</span></td>
                                                        <td class="fw-bold">{{ $inst->share_label }}</td>
                                                        <td class="text-muted" dir="ltr" style="text-align:right;">{{ $inst->member_phone ?: '—' }}</td>
                                                        <td><strong>{{ number_format($inst->amount, 2) }} ج</strong></td>
                                                        <td>
                                                            @if($st['key'] === 'paid' || $st['key'] === 'paid_late')
                                                                <span class="badge-status badge-paid"><i class="fa-solid fa-check"></i> دفع</span>
                                                                <div style="font-size:0.74rem; color:#64748b;" class="mt-1">
                                                                    {{ \Carbon\Carbon::parse($inst->paid_at)->format('Y/m/d') }} — {{ $inst->paid_account_name ?? 'الخزنة' }}
                                                                    @if($st['key'] === 'paid_late') <span class="text-danger fw-bold">(بعد الميعاد)</span> @endif
                                                                </div>
                                                            @elseif($st['key'] === 'late')
                                                                <span class="badge-status badge-pending"><i class="fa-solid fa-triangle-exclamation"></i> متأخر {{ $st['days'] }} يوم</span>
                                                            @elseif($st['key'] === 'open')
                                                                <span class="badge-status badge-open"><i class="fa-regular fa-clock"></i> {{ $st['days'] === 0 ? 'لسه — آخر يوم النهارده' : "لسه — باقي {$st['days']} يوم" }}</span>
                                                            @else
                                                                <span class="badge-status badge-waived"><i class="fa-regular fa-hourglass"></i> لم يبدأ التحصيل</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(!$inst->is_paid)
                                                                <button type="button" class="btn-act btn-collect"
                                                                        onclick="openCollectModal([{{ $inst->id }}], {{ $inst->amount }}, @js($jamia->name . ' — ' . $inst->share_label), '{{ $mModalId }}')">
                                                                    <i class="fa-solid fa-hand-holding-dollar"></i> تحصيل
                                                                </button>
                                                            @else
                                                                <form method="POST" action="{{ route('cashflows.cancel_payment') }}"
                                                                      onsubmit="if(confirm('إلغاء تحصيل القسط ده وخصم المبلغ من الخزنة؟')){ rememberReopenModal('{{ $mModalId }}'); return true; } return false;" style="display:inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="installment_id" value="{{ $inst->id }}">
                                                                    <button type="submit" class="btn-act btn-cancel" title="إلغاء التحصيل">
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

                                    {{-- Bulk Bar --}}
                                    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2 bg-light p-2 rounded-3">
                                        <small class="text-muted" id="bulk-info-{{ $jamia->id }}">حدد الأعضاء اللي دفعوا علشان تسجل تحصيلهم مرة واحدة.</small>
                                        <button type="button" class="btn-act btn-pay" id="bulk-btn-{{ $jamia->id }}" disabled
                                                onclick="openBulkCollect({{ $jamia->id }}, @js($jamia->name), '{{ $mModalId }}')">
                                            <i class="fa-solid fa-list-check"></i> تحصيل المحدد
                                        </button>
                                    </div>
                                @endif

                                {{-- Payout of the month --}}
                                <div class="mt-4">
                                    <h6 class="fw-bold mb-2" style="font-size:0.9rem;"><i class="fa-solid fa-sack-dollar text-danger me-1"></i> دور القبض الشهر ده:</h6>
                                    @forelse($jamia->month_payouts as $p)
                                        <div class="payout-box {{ $p->is_collected ? 'done' : ($p->is_company ? 'company' : '') }} mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="turn-num {{ $p->is_company ? 'company' : '' }}">{{ $p->share_number }}</span>
                                                <div>
                                                    <div class="fw-bold">
                                                        {{ $p->share_label }}
                                                        @if($p->is_company)
                                                            <span class="badge-status badge-company ms-1"><i class="fa-solid fa-building"></i> سهم الشركة</span>
                                                        @elseif($p->is_transferred)
                                                            <span class="badge-status badge-transferred ms-1"><i class="fa-solid fa-people-arrows"></i> داخل مكان الشركة</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">
                                                        القبض: <b class="text-dark">{{ number_format($p->payout_amount, 2) }} ج</b>
                                                        @if($p->member_phone) | <span dir="ltr">{{ $p->member_phone }}</span> @endif
                                                        @if($p->is_transferred)
                                                            | حق الشركة المسترد: <b class="text-primary">{{ number_format($p->company_claim_amount, 2) }} ج</b> ({{ $p->paid_months_before_transfer }} اسم)
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                @if($p->is_collected)
                                                    <span class="badge-status badge-collected">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                        {{ $p->is_company ? 'استلمت الشركة' : 'اتصرف القبض' }}
                                                        {{ $p->collected_at ? '— ' . \Carbon\Carbon::parse($p->collected_at)->format('Y/m/d') : '' }}
                                                    </span>
                                                    @if($p->collected_account_name)
                                                        <small class="text-muted">{{ $p->is_company ? 'في:' : 'من:' }} <b>{{ $p->collected_account_name }}</b></small>
                                                    @endif
                                                    <form method="POST" action="{{ route('cashflows.cancel_payout') }}"
                                                          onsubmit="if(confirm('إلغاء تسجيل القبض وعكس المبلغ من الخزنة؟')){ rememberReopenModal('{{ $mModalId }}'); return true; } return false;" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="share_id" value="{{ $p->id }}">
                                                        <button type="submit" class="btn-act btn-cancel" title="إلغاء"><i class="fa-solid fa-rotate-left"></i></button>
                                                    </form>
                                                @else
                                                    @if($unpaid->count() > 0 && !$p->is_company)
                                                        <small class="text-danger fw-bold"><i class="fa-solid fa-circle-exclamation"></i> لسه {{ $unpaid->count() }} عضو ما دفعش</small>
                                                    @endif
                                                    @if($p->is_company)
                                                        <button type="button" class="btn-act btn-pay"
                                                                onclick="openPayoutModal({ shareId: {{ $p->id }}, amount: {{ (float) $p->payout_amount }}, info: @js($jamia->name . ' — ' . $p->share_label . ' (دور ' . $p->share_number . ')'), unpaidCount: {{ $unpaid->count() }}, isCompany: true, isTransferred: false, claimAmount: 0, isClaimRecovered: false, parentModalId: '{{ $mModalId }}' })">
                                                            <i class="fa-solid fa-building-circle-check"></i> تسجيل استلام الشركة (واختيار خزنة)
                                                        </button>
                                                        @if(!$p->is_transferred)
                                                            <button type="button" class="btn-act btn-transfer"
                                                                    onclick="openTransferModal({ shareId: {{ $p->id }}, shareNumber: {{ $p->share_number }}, jamiaName: @js($jamia->name), startDate: '{{ \Carbon\Carbon::parse($jamia->start_date)->format('Y-m-d') }}', duration: {{ $jamia->duration_months }}, installment: {{ (float) $jamia->monthly_installment }}, payoutAmount: {{ (float) $p->payout_amount }}, parentModalId: '{{ $mModalId }}' })">
                                                                <i class="fa-solid fa-people-arrows"></i> دخول بديل مكاني (تنازل)
                                                            </button>
                                                        @endif
                                                    @else
                                                        <button type="button" class="btn-act btn-payout"
                                                                onclick="openPayoutModal({ shareId: {{ $p->id }}, amount: {{ (float) $p->payout_amount }}, info: @js($jamia->name . ' — ' . $p->share_label . ' (دور ' . $p->share_number . ')'), unpaidCount: {{ $unpaid->count() }}, isCompany: false, isTransferred: {{ $p->is_transferred ? 'true' : 'false' }}, claimAmount: {{ (float) ($p->company_claim_amount ?? 0) }}, isClaimRecovered: {{ $p->is_claim_recovered ? 'true' : 'false' }}, parentModalId: '{{ $mModalId }}' })">
                                                            <i class="fa-solid fa-money-bill-transfer"></i> صرف القبض
                                                        </button>
                                                    @endif
                                                @endif

                                                {{-- زر أو حالة استرداد حق الشركة للسهم المتنازل عنه --}}
                                                @if($p->is_transferred && (float) ($p->company_claim_amount ?? 0) > 0)
                                                    @if(!$p->is_claim_recovered)
                                                        <button type="button" class="btn-act btn-recover"
                                                                onclick="openRecoverModal({ shareId: {{ $p->id }}, jamiaName: @js($jamia->name), memberName: @js($p->share_label), shareNumber: {{ $p->share_number }}, paidMonths: {{ (int) $p->paid_months_before_transfer }}, claimAmount: {{ (float) $p->company_claim_amount }}, isCollected: {{ $p->is_collected ? 'true' : 'false' }}, parentModalId: '{{ $mModalId }}' })">
                                                            <i class="fa-solid fa-vault"></i> تحصيل حق الشركة ({{ number_format($p->company_claim_amount, 0) }} ج)
                                                        </button>
                                                    @else
                                                        <span class="badge-status badge-collected">
                                                            <i class="fa-solid fa-vault"></i> تم استرداد {{ number_format($p->recovered_amount, 0) }} ج
                                                            @if($p->recovered_account_name) — في: {{ $p->recovered_account_name }} @endif
                                                        </span>
                                                        <form method="POST" action="{{ route('cashflows.cancel_recovery') }}"
                                                              onsubmit="if(confirm('إلغاء تحصيل حق الشركة وخصم المبلغ من الخزنة؟')){ rememberReopenModal('{{ $mModalId }}'); return true; } return false;" style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="share_id" value="{{ $p->id }}">
                                                            <button type="submit" class="btn-act btn-cancel" title="إلغاء الاسترداد"><i class="fa-solid fa-rotate-left"></i></button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted" style="font-size:.85rem;">مفيش دور قبض مسجل للشهر ده.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ═══ Tab 2: دليل الجمعيات والأعضاء (مربعات + بوبب كافة البيانات) ═══ --}}
    <div id="tab-jamias" class="tab-content-pane" style="display:none;">
        @if($jamias->isNotEmpty())
            <div class="filter-strip py-2 px-3 mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-secondary" style="font-size:.86rem;"><i class="fa-solid fa-layer-group text-teal me-1"></i> الدليل:</span>
                    <button type="button" class="btn btn-sm btn-teal fw-bold dir-filter-pill active" style="background:#0d9488;color:#fff;" onclick="setDirFilter('all', this)">الكل ({{ $jamias->count() }})</button>
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold dir-filter-pill" onclick="setDirFilter('active', this)">النشطة فقط</button>
                    <button type="button" class="btn btn-sm btn-outline-warning fw-bold dir-filter-pill" onclick="setDirFilter('late', this)">فيها متأخرات</button>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold dir-filter-pill" onclick="setDirFilter('transferred', this)">فيها تنازل بديل</button>
                </div>
                <div style="min-width:250px;">
                    <input type="search" id="dirJamiaSearch" class="form-control form-control-sm" placeholder="ابحث باسم الجمعية أو أي عضو أو الهاتف..." oninput="applyDirFilters()">
                </div>
            </div>
        @endif

        @if($jamias->isEmpty())
            <div class="chart-box text-center py-5">
                <p class="text-muted mb-0">لم يتم تسجيل أي جمعيات حتى الآن. اضغط على "إضافة جمعية جديدة" للبدء.</p>
            </div>
        @else
            <div class="row g-3" id="dirJamiasGrid">
                @foreach($jamias as $jamia)
                    @php
                        $dirSearch = mb_strtolower($jamia->name . ' ' . $jamia->shares->pluck('share_label')->implode(' ') . ' ' . $jamia->shares->pluck('member_phone')->implode(' '));
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4 dir-jamia-tile-wrap"
                         data-status="{{ $jamia->status }}"
                         data-has-late="{{ ($jamia->late_total_count ?? 0) > 0 ? '1' : '0' }}"
                         data-has-transferred="{{ ($jamia->transferred_count ?? 0) > 0 ? '1' : '0' }}"
                         data-search="{{ $dirSearch }}">
                        <div class="jamia-tile" onclick="openJamiaPopup('dirJamiaModal-{{ $jamia->id }}')">
                            <div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1 text-teal">{{ $jamia->name }}</h5>
                                        <div class="text-muted" style="font-size:0.78rem;">
                                            <i class="fa-regular fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($jamia->start_date)->locale('ar')->isoFormat('MMM YYYY') }} ← {{ \Carbon\Carbon::parse($jamia->start_date)->addMonths($jamia->duration_months - 1)->locale('ar')->isoFormat('MMM YYYY') }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $jamia->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $jamia->status === 'active' ? 'نشطة' : ($jamia->status === 'completed' ? 'منتهية' : 'ملغاة') }}
                                    </span>
                                </div>

                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="badge-status badge-waived" style="font-size:0.72rem;">تحصيل يوم {{ $jamia->collection_start_day ?? 1 }} ← {{ $jamia->collection_end_day ?? 10 }}</span>
                                    @if(($jamia->company_shares_in ?? 0) > 0)
                                        <span class="badge-status badge-company" style="font-size:0.72rem;">{{ $jamia->company_shares_in }} سهم لـ{{ $companyName }}</span>
                                    @endif
                                    @if(($jamia->transferred_count ?? 0) > 0)
                                        <span class="badge-status badge-transferred" style="font-size:0.72rem;">{{ $jamia->transferred_count }} متنازل عنه</span>
                                    @endif
                                    @if(($jamia->late_total_count ?? 0) > 0)
                                        <span class="badge-status badge-pending" style="font-size:0.72rem;">{{ $jamia->late_total_count }} قسط متأخر</span>
                                    @endif
                                </div>

                                <div class="jamia-tile-grid">
                                    <div class="jamia-tile-stat">
                                        <small>قيمة القبض</small>
                                        <strong>{{ number_format($jamia->total_amount, 0) }} ج</strong>
                                    </div>
                                    <div class="jamia-tile-stat">
                                        <small>المدة والقسط</small>
                                        <strong>{{ $jamia->duration_months }} شهر × {{ number_format($jamia->monthly_installment, 0) }} ج</strong>
                                    </div>
                                    <div class="jamia-tile-stat">
                                        <small>اتحصل من الأعضاء</small>
                                        <strong class="text-success">{{ number_format($jamia->calc_total_collected, 0) }} ج</strong>
                                    </div>
                                    <div class="jamia-tile-stat">
                                        <small>أدوار اتصرفت/اتقبضت</small>
                                        <strong>{{ $jamia->collected_count ?? 0 }} / {{ $jamia->members_count }}</strong>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mb-1" style="font-size:0.76rem;">
                                    <span>نسبة التحصيل الكلية</span>
                                    <strong>{{ $jamia->calc_progress }}%</strong>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 4px;">
                                    <div class="progress-bar bg-teal" role="progressbar" style="width: {{ $jamia->calc_progress }}%"></div>
                                </div>
                            </div>

                            <div class="jamia-tile-footer">
                                <span class="text-teal fw-bold"><i class="fa-solid fa-up-right-from-square me-1"></i> اضغط لعرض كل الأعضاء والإجراءات</span>
                                <a href="{{ route('cashflows.print_jamia', $jamia->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="event.stopPropagation();" title="طباعة الكشف">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- بوبب كافة البيانات لكل جمعية في الدليل --}}
            @foreach($jamias as $jamia)
                @php $dModalId = 'dirJamiaModal-' . $jamia->id; @endphp
                <div class="modal fade dir-jamia-modal" id="{{ $dModalId }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
                            <div class="modal-header bg-light">
                                <div>
                                    <h5 class="modal-title fw-bold text-teal mb-0">
                                        {{ $jamia->name }}
                                        @if($jamia->status !== 'active')
                                            <span class="badge bg-secondary ms-1" style="font-size:.7rem;">{{ $jamia->status === 'completed' ? 'منتهية' : 'ملغاة' }}</span>
                                        @endif
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fa-regular fa-calendar me-1"></i>
                                        من {{ \Carbon\Carbon::parse($jamia->start_date)->locale('ar')->isoFormat('MMMM YYYY') }}
                                        لغاية {{ \Carbon\Carbon::parse($jamia->start_date)->addMonths($jamia->duration_months - 1)->locale('ar')->isoFormat('MMMM YYYY') }}
                                        | التحصيل من يوم {{ $jamia->collection_start_day ?? 1 }} لغاية يوم {{ $jamia->collection_end_day ?? 10 }}
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3" title="تعديل إعدادات الجمعية"
                                            onclick="openEditJamia(this, '{{ $dModalId }}')"
                                            data-id="{{ $jamia->id }}"
                                            data-name="{{ $jamia->name }}"
                                            data-start-day="{{ $jamia->collection_start_day ?? 1 }}"
                                            data-end-day="{{ $jamia->collection_end_day ?? 10 }}"
                                            data-status="{{ $jamia->status }}"
                                            data-notes="{{ $jamia->notes }}">
                                        <i class="fa-solid fa-sliders"></i> إعدادات
                                    </button>
                                    <a href="{{ route('cashflows.print_jamia', $jamia->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3" title="طباعة كشف الجمعية">
                                        <i class="fa-solid fa-print"></i> طباعة
                                    </a>
                                    @if(!$jamia->calc_has_cash_moves)
                                        <form method="POST" action="{{ route('cashflows.destroy', $jamia->id) }}" onsubmit="return confirm('هل تريد حذف هذه الجمعية نهائياً؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="حذف الجمعية">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                            </div>

                            <div class="modal-body p-4">
                                {{-- Stats Bar --}}
                                <div class="row g-2 mb-3 bg-light p-3 rounded-3 text-center" style="font-size:0.85rem;">
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted d-block">قيمة القبض</span>
                                        <strong class="text-dark">{{ number_format($jamia->total_amount, 2) }} ج</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted d-block">المدة والقسط</span>
                                        <strong class="text-dark">{{ $jamia->duration_months }} شهر ({{ number_format($jamia->monthly_installment, 2) }} ج)</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted d-block">اتحصل من المطلوب</span>
                                        <strong class="text-success">{{ number_format($jamia->calc_total_collected, 0) }} / {{ number_format($jamia->calc_total_due, 0) }} ج</strong>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted d-block">أسهم {{ $companyName }}</span>
                                        <strong class="text-teal">{{ $jamia->company_shares_in }} (من {{ $jamia->members_count }})</strong>
                                    </div>
                                </div>

                                {{-- Members Directory Table --}}
                                <div class="table-responsive">
                                    <table class="flow-table">
                                        <thead>
                                            <tr>
                                                <th>الدور</th>
                                                <th>شهر القبض</th>
                                                <th>الاسم والحالة</th>
                                                <th>الهاتف</th>
                                                <th>المدفوع</th>
                                                <th>متأخرات</th>
                                                <th>القبض / الخزنة</th>
                                                <th>إجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($jamia->shares as $sh)
                                                @php $paidCountForShare = $jamia->paid_by_share[$sh->id] ?? 0; @endphp
                                                <tr>
                                                    <td><span class="turn-num {{ $sh->is_company ? 'company' : '' }}">{{ $sh->share_number }}</span></td>
                                                    <td>{{ \Carbon\Carbon::parse($sh->payout_month)->locale('ar')->isoFormat('MMMM YYYY') }}</td>
                                                    <td>
                                                        <div class="fw-bold">
                                                            {{ $sh->share_label }}
                                                            @if($sh->is_company)
                                                                <span class="badge-status badge-company ms-1"><i class="fa-solid fa-building"></i> سهم الشركة</span>
                                                            @elseif($sh->is_transferred)
                                                                <span class="badge-status badge-transferred ms-1"><i class="fa-solid fa-people-arrows"></i> بديل الشركة</span>
                                                            @endif
                                                        </div>
                                                        @if($sh->is_transferred)
                                                            <div class="mt-1" style="font-size:0.75rem; color:#4338ca;">
                                                                من شهر: {{ $sh->transferred_from_month ? \Carbon\Carbon::parse($sh->transferred_from_month)->locale('ar')->isoFormat('MMM YYYY') : '—' }}
                                                                | حق الشركة: <b>{{ $sh->paid_months_before_transfer }} اسم ({{ number_format($sh->company_claim_amount, 0) }} ج)</b>
                                                                @if($sh->is_claim_recovered)
                                                                    <span class="text-success fw-bold"> — مسترد في {{ $sh->recoveredAccount->account_name ?? 'الخزنة' }}</span>
                                                                @else
                                                                    <span class="text-danger fw-bold"> — لم يُسترد بعد</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted" dir="ltr" style="text-align:right;">{{ $sh->member_phone ?: '—' }}</td>
                                                    <td>
                                                        @if($sh->is_company)
                                                            <span class="text-muted">—</span>
                                                        @else
                                                            @php
                                                                $reqMonths = $sh->is_transferred
                                                                    ? max(0, $jamia->duration_months - (int) $jamia->installments->where('share_id', $sh->id)->where('is_waived', true)->count())
                                                                    : $jamia->duration_months;
                                                            @endphp
                                                            <span class="fw-bold text-success">{{ $paidCountForShare }}</span> / {{ $reqMonths }} شهر
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $lateN = $jamia->late_by_share[$sh->id] ?? 0; @endphp
                                                        @if($sh->is_company)
                                                            <span class="text-muted">—</span>
                                                        @elseif($lateN > 0)
                                                            <span class="badge-status badge-pending">{{ $lateN }} شهر</span>
                                                        @else
                                                            <span class="badge-status badge-paid"><i class="fa-solid fa-check"></i> منتظم</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($sh->is_collected)
                                                            <span class="badge-status badge-collected">
                                                                <i class="fa-solid fa-circle-check"></i> {{ $sh->is_company ? 'استلمت الشركة' : 'اتصرف' }}
                                                            </span>
                                                            @if($sh->collectedAccount)
                                                                <div style="font-size:0.72rem;" class="text-muted mt-1">
                                                                    {{ $sh->is_company ? 'في:' : 'من:' }} {{ $sh->collectedAccount->account_name }}
                                                                </div>
                                                            @endif
                                                        @else
                                                            <span class="badge-status badge-waived">لسه</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-1">
                                                            <button type="button" class="btn-act btn-edit" title="تعديل بيانات العضو / تبديل الدور"
                                                                    onclick="openEditMember(this, '{{ $dModalId }}')"
                                                                    data-id="{{ $sh->id }}"
                                                                    data-name="{{ $sh->share_label }}"
                                                                    data-phone="{{ $sh->member_phone }}"
                                                                    data-turn="{{ $sh->share_number }}"
                                                                    data-company="{{ $sh->is_company ? 1 : 0 }}"
                                                                    data-duration="{{ $jamia->duration_months }}"
                                                                    data-start="{{ \Carbon\Carbon::parse($jamia->start_date)->format('Y-m') }}"
                                                                    data-jamia="{{ $jamia->name }}">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </button>

                                                            @if($sh->is_company && !$sh->is_collected && !$sh->is_transferred)
                                                                <button type="button" class="btn-act btn-pay" title="تسجيل استلام الشركة وإيداع في الخزنة"
                                                                        onclick="openPayoutModal({ shareId: {{ $sh->id }}, amount: {{ (float) $sh->payout_amount }}, info: @js($jamia->name . ' — ' . $sh->share_label . ' (دور ' . $sh->share_number . ')'), unpaidCount: 0, isCompany: true, isTransferred: false, claimAmount: 0, isClaimRecovered: false, parentModalId: '{{ $dModalId }}' })">
                                                                    <i class="fa-solid fa-building-circle-check"></i> استلام
                                                                </button>
                                                                <button type="button" class="btn-act btn-transfer" title="دخول بديل مكان الشركة (تنازل)"
                                                                        onclick="openTransferModal({ shareId: {{ $sh->id }}, shareNumber: {{ $sh->share_number }}, jamiaName: @js($jamia->name), startDate: '{{ \Carbon\Carbon::parse($jamia->start_date)->format('Y-m-d') }}', duration: {{ $jamia->duration_months }}, installment: {{ (float) $jamia->monthly_installment }}, payoutAmount: {{ (float) $sh->payout_amount }}, parentModalId: '{{ $dModalId }}' })">
                                                                    <i class="fa-solid fa-people-arrows"></i> بديل مكاني
                                                                </button>
                                                            @elseif(!$sh->is_company && !$sh->is_collected)
                                                                <button type="button" class="btn-act btn-payout" title="صرف القبض"
                                                                        onclick="openPayoutModal({ shareId: {{ $sh->id }}, amount: {{ (float) $sh->payout_amount }}, info: @js($jamia->name . ' — ' . $sh->share_label . ' (دور ' . $sh->share_number . ')'), unpaidCount: 0, isCompany: false, isTransferred: {{ $sh->is_transferred ? 'true' : 'false' }}, claimAmount: {{ (float) ($sh->company_claim_amount ?? 0) }}, isClaimRecovered: {{ $sh->is_claim_recovered ? 'true' : 'false' }}, parentModalId: '{{ $dModalId }}' })">
                                                                    <i class="fa-solid fa-money-bill-transfer"></i> صرف
                                                                </button>
                                                            @elseif($sh->is_collected)
                                                                <form method="POST" action="{{ route('cashflows.cancel_payout') }}"
                                                                      onsubmit="if(confirm('إلغاء تسجيل القبض وعكس المبلغ من الخزنة؟')){ rememberReopenModal('{{ $dModalId }}'); return true; } return false;" style="display:inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="share_id" value="{{ $sh->id }}">
                                                                    <button type="submit" class="btn-act btn-cancel" title="إلغاء القبض">
                                                                        <i class="fa-solid fa-rotate-left"></i>
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            @if($sh->is_transferred)
                                                                @if(!$sh->is_claim_recovered && (float) $sh->company_claim_amount > 0)
                                                                    <button type="button" class="btn-act btn-recover" title="تحصيل حق الشركة من العضو البديل"
                                                                            onclick="openRecoverModal({ shareId: {{ $sh->id }}, jamiaName: @js($jamia->name), memberName: @js($sh->share_label), shareNumber: {{ $sh->share_number }}, paidMonths: {{ (int) $sh->paid_months_before_transfer }}, claimAmount: {{ (float) $sh->company_claim_amount }}, isCollected: {{ $sh->is_collected ? 'true' : 'false' }}, parentModalId: '{{ $dModalId }}' })">
                                                                        <i class="fa-solid fa-vault"></i> حق الشركة
                                                                    </button>
                                                                @elseif($sh->is_claim_recovered)
                                                                    <form method="POST" action="{{ route('cashflows.cancel_recovery') }}"
                                                                          onsubmit="if(confirm('إلغاء تحصيل حق الشركة وخصم المبلغ من الخزنة؟')){ rememberReopenModal('{{ $dModalId }}'); return true; } return false;" style="display:inline;">
                                                                        @csrf
                                                                        <input type="hidden" name="share_id" value="{{ $sh->id }}">
                                                                        <button type="submit" class="btn-act btn-cancel" title="إلغاء الاسترداد">
                                                                            <i class="fa-solid fa-xmark"></i> إلغاء الاسترداد
                                                                        </button>
                                                                    </form>
                                                                @endif

                                                                @if(!$sh->is_collected && !$sh->is_claim_recovered && $paidCountForShare === 0)
                                                                    <form method="POST" action="{{ route('cashflows.undo_transfer') }}"
                                                                          onsubmit="if(confirm('فك التنازل وإرجاع الدور لـ{{ $companyName }}؟')){ rememberReopenModal('{{ $dModalId }}'); return true; } return false;" style="display:inline;">
                                                                        @csrf
                                                                        <input type="hidden" name="share_id" value="{{ $sh->id }}">
                                                                        <button type="submit" class="btn-act btn-edit text-danger" title="فك التنازل">
                                                                            <i class="fa-solid fa-undo"></i> فك
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if($jamia->notes)
                                    <div class="alert alert-light border mt-3 mb-0 py-2" style="font-size:0.84rem;">
                                        <strong>ملاحظات:</strong> {{ $jamia->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ═══ Tab 3: المتأخرين ═══ --}}
    <div id="tab-overdue" class="tab-content-pane" style="display:none;">
        <div class="chart-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="box-title mb-0">
                    <i class="fa-solid fa-user-clock text-danger"></i> الأعضاء المتأخرين بعد انتهاء فترة التحصيل
                </div>
                <div class="text-muted" style="font-size:0.85rem;">
                    العدد: <strong class="text-danger">{{ $overdueInstallments->count() }}</strong> | الإجمالي: <strong class="text-danger">{{ number_format($overdueInstallments->sum('amount'), 2) }} ج</strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="flow-table">
                    <thead>
                        <tr>
                            <th>الجمعية</th>
                            <th>شهر القسط</th>
                            <th>الدور</th>
                            <th>العضو</th>
                            <th>الهاتف</th>
                            <th>القسط</th>
                            <th>التأخير</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueInstallments as $inst)
                            <tr class="row-late">
                                <td class="fw-bold text-teal">{{ $inst->jamia_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($inst->month_date)->locale('ar')->isoFormat('MMMM YYYY') }}</td>
                                <td><span class="turn-num">{{ $inst->share_number }}</span></td>
                                <td class="fw-bold">{{ $inst->share_label }}</td>
                                <td class="text-muted" dir="ltr" style="text-align:right;">{{ $inst->member_phone ?: '—' }}</td>
                                <td><strong>{{ number_format($inst->amount, 2) }} ج</strong></td>
                                <td><span class="badge-status badge-pending">{{ $inst->status['days'] }} يوم</span></td>
                                <td>
                                    <button type="button" class="btn-act btn-collect"
                                            onclick="openCollectModal([{{ $inst->id }}], {{ $inst->amount }}, @js($inst->jamia_name . ' — ' . $inst->share_label . ' (' . \Carbon\Carbon::parse($inst->month_date)->format('Y/m') . ')'))">
                                        <i class="fa-solid fa-hand-holding-dollar"></i> تحصيل
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> مفيش أي متأخرات — كل الأعضاء منتظمين.</td>
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
    <div class="modal-dialog modal-xl">
        <form method="POST" action="{{ route('cashflows.store') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="return validateNewJamia();">
            @csrf
            <input type="hidden" name="form_kind" value="new_jamia">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-teal"><i class="fa-solid fa-plus-circle me-1"></i> تسجيل جمعية جديدة ({{ $companyName }} هي المنظم)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم الجمعية <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثلاً: جمعية 100 ألف - يناير" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">المنظم</label>
                        <input type="text" name="organizer_name" class="form-control" value="{{ old('organizer_name', $companyName) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">تاريخ أول شهر <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="input_start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m-01')) }}" required onchange="generateMemberRows()">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">قيمة القبض <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="total_amount" id="input_total_amount" class="form-control" placeholder="مثلاً: 100000" value="{{ old('total_amount') }}" required oninput="calcMonthlyInstallment()">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">عدد الشهور = عدد الأعضاء <span class="text-danger">*</span></label>
                        <input type="number" name="duration_months" id="input_duration" class="form-control" placeholder="مثلاً: 10" min="2" max="60" value="{{ old('duration_months', 10) }}" required oninput="calcMonthlyInstallment(); generateMemberRows();">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">قسط العضو الشهري</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="display_installment" class="form-control" placeholder="0.00" oninput="calcTotalFromInstallment()">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">عدد أسهم {{ $companyName }}</label>
                        <input type="number" name="company_shares_count" id="input_company_count" class="form-control" value="{{ old('company_shares_count', 1) }}" min="0" max="60" oninput="applyCompanyCount()">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">التحصيل يبدأ يوم</label>
                        <input type="number" name="collection_start_day" id="input_col_start" class="form-control" value="{{ old('collection_start_day', 1) }}" min="1" max="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">آخر يوم للتحصيل (بحد أقصى 10)</label>
                        <input type="number" name="collection_end_day" id="input_col_end" class="form-control" value="{{ old('collection_end_day', 10) }}" min="1" max="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <input type="text" name="notes" class="form-control" placeholder="اختياري" value="{{ old('notes') }}">
                    </div>

                    <div class="col-12">
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <h6 class="fw-bold text-teal mb-0"><i class="fa-solid fa-users me-1"></i> الأعضاء وأماكن دخولهم (دور القبض):</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="shuffleTurns()" title="توزيع الأدوار عشوائياً (قرعة)">
                                    <i class="fa-solid fa-shuffle me-1"></i> قرعة على الأدوار
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetTurns()">
                                    <i class="fa-solid fa-arrow-down-1-9 me-1"></i> ترتيب الأدوار
                                </button>
                            </div>
                        </div>
                        <small class="text-muted d-block mb-2">كل عضو له دور = الشهر اللي هيقبض فيه. علّم "سهم الشركة" للأسهم الخاصة بـ{{ $companyName }} (مش هيتطلب منها تحصيل).</small>
                        <div id="turn_dup_warning" class="alert alert-danger py-2 mb-2" style="display:none; font-size:.85rem;"></div>
                        <div class="table-responsive" style="max-height: 380px; overflow-y:auto;">
                            <table class="table table-sm members-table mb-0">
                                <thead class="sticky-top">
                                    <tr>
                                        <th style="width:90px;">الدور</th>
                                        <th style="width:140px;">شهر القبض</th>
                                        <th>الاسم</th>
                                        <th style="width:170px;">الهاتف</th>
                                        <th style="width:110px;" class="text-center">سهم الشركة</th>
                                    </tr>
                                </thead>
                                <tbody id="members_container">
                                    {{-- Generated by JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn fw-bold px-4" style="background:#0d9488; color:#fff;">حفظ الجمعية وتوليد جدول التحصيل</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 2: تحصيل قسط/أقساط --}}
<div class="modal fade sub-action-modal" id="collectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.collect') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="rememberActiveParentForSubmit();">
            @csrf
            <div id="collect_ids_holder"></div>
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-hand-holding-dollar me-1"></i> تسجيل تحصيل من الأعضاء</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="collect_info">—</div>
                    <div class="text-success fw-bold fs-5 mt-1" id="collect_amount_display">0.00 ج</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">إيداع المبلغ في محفظة / خزنة: <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select" required>
                        <option value="">-- اختر المحفظة أو الخزنة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">تاريخ التحصيل</label>
                    <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" placeholder="اختياري">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-success fw-bold px-4">تأكيد التحصيل والإيداع</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 3: صرف القبض للعضو أو تسجيل استلام الشركة في خزنة (إجباري) --}}
<div class="modal fade sub-action-modal" id="payoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.payout') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="return validatePayoutForm();">
            @csrf
            <input type="hidden" name="share_id" id="payout_share_id">
            <input type="hidden" id="payout_max_amount" value="0">
            <input type="hidden" id="payout_is_company_flag" value="0">
            <div class="modal-header bg-danger text-white" id="payout_modal_header">
                <h5 class="modal-title fw-bold" id="payout_modal_title"><i class="fa-solid fa-money-bill-transfer me-1"></i> صرف القبض لصاحب الدور</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="payout_info">—</div>
                    <div class="text-danger fw-bold fs-5 mt-1" id="payout_amount_display">0.00 ج</div>
                </div>
                <div class="alert alert-warning py-2 mb-3" id="payout_warning" style="display:none; font-size:.86rem;"></div>
                <div class="alert alert-info py-2 mb-3" id="payout_company_note" style="display:none; font-size:.86rem;">
                    <i class="fa-solid fa-building-circle-check me-1"></i> تسجيل استلام <strong>{{ $companyName }}</strong> لدورها — اختيار الخزنة إجباري لإيداع المبلغ وتوثيقه في سجل العمليات.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold" id="payout_account_label">الصرف من محفظة / خزنة: <span class="text-danger">*</span></label>
                    <select name="account_id" id="payout_account_id" class="form-select" required>
                        <option value="">-- اختر المحفظة أو الخزنة (إجباري) --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold" id="payout_amount_label">المبلغ المصروف <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="1" name="amount" id="payout_amount_input" class="form-control" required>
                        <span class="input-group-text">ج</span>
                    </div>
                </div>

                {{-- خيار استرداد حق الشركة مباشرة أثناء صرف قبض السهم المتنازل عنه --}}
                <div class="border border-primary rounded-3 p-3 mb-3 bg-light" id="payout_recover_section" style="display:none;">
                    <div class="fw-bold text-primary mb-2" style="font-size:0.88rem;">
                        <i class="fa-solid fa-people-arrows me-1"></i> هذا السهم متنازل عنه من {{ $companyName }}
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="also_recover_claim" value="1" id="payout_also_recover" onchange="toggleRecoverWithPayout(this.checked)">
                        <label class="form-check-label fw-bold" for="payout_also_recover">تحصيل حق الشركة من القبض الآن وإيداعه في خزنة</label>
                    </div>
                    <div id="payout_recover_fields" style="display:none;">
                        <div class="mb-2">
                            <label class="form-label fw-bold small">خزنة إيداع حق الشركة <span class="text-danger">*</span></label>
                            <select name="recover_account_id" id="payout_recover_account_id" class="form-select form-select-sm">
                                <option value="">-- اختر الخزنة المستلمة --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-bold small">قيمة حق الشركة المسترد</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" min="1" name="recover_amount" id="payout_recover_amount" class="form-control">
                                <span class="input-group-text">ج</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">التاريخ</label>
                    <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" placeholder="اختياري">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-danger fw-bold px-4" id="payout_submit_btn">تأكيد الصرف</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 3B: دخول بديل مكان الشركة (التنازل وتثبيت حق الشركة) --}}
<div class="modal fade sub-action-modal" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('cashflows.transfer') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="return validateTransferForm();">
            @csrf
            <input type="hidden" name="share_id" id="tr_share_id">
            <input type="hidden" id="tr_duration" value="0">
            <input type="hidden" id="tr_installment" value="0">
            <input type="hidden" id="tr_payout_amount" value="0">
            <div class="modal-header text-white" style="background:#7c3aed;">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-people-arrows me-1"></i> دخول عضو بديل مكان {{ $companyName }} (تنازل)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="tr_info">—</div>
                    <small class="text-muted" id="tr_sub_info">—</small>
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-bold">اسم العضو الجديد اللي هيدخل مكان الشركة <span class="text-danger">*</span></label>
                        <input type="text" name="transferred_to_name" id="tr_new_name" class="form-control" placeholder="مثال: الحاج سيد إبراهيم" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">رقم الهاتف</label>
                        <input type="text" name="member_phone" id="tr_phone" class="form-control" dir="ltr" placeholder="اختياري">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">يبدأ البديل دفع أقساطه من شهر <span class="text-danger">*</span></label>
                        <select name="transferred_from_month" id="tr_from_month" class="form-select" required onchange="onTransferMonthChange()"></select>
                        <small class="text-muted">الشهور اللي قبل الشهر ده هتتقفل كحق للشركة، والشهور من أول الشهر ده هتنزل عليه كأقساط.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">عدد الأسماء (الشهور) اللي الشركة دفعتها قبله <span class="text-danger">*</span></label>
                        <input type="number" name="paid_months_before_transfer" id="tr_paid_months" class="form-control" min="1" required oninput="updateTransferSummary()">
                        <small class="text-muted" id="tr_max_months_hint">بحد أقصى عدد الشهور قبل بداية العضو الجديد.</small>
                    </div>
                </div>

                <div class="alert border mt-3 mb-3" style="background:#f5f3ff; border-color:#ddd6fe !important;" id="tr_summary_box">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <span>حق الشركة المسترد ساعة القبض: <strong class="text-primary fs-6" id="tr_claim_display">0.00 ج</strong></span>
                        <span>الأقساط المتبقية على العضو الجديد: <strong class="text-dark" id="tr_rem_display">0 شهر</strong></span>
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold">ملاحظات الاتفاق</label>
                    <input type="text" name="transferred_notes" class="form-control" placeholder="مثال: اتفقنا ياخد القبض ساعة دوره ويديني الـ 13 اسم كاش">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn text-white fw-bold px-4" style="background:#7c3aed;">تأكيد التنازل وتثبيت حق الشركة</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 3C: تحصيل حق الشركة من العضو البديل وإيداعه في الخزنة --}}
<div class="modal fade sub-action-modal" id="recoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.recover') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="return validateRecoverForm();">
            @csrf
            <input type="hidden" name="share_id" id="rec_share_id">
            <input type="hidden" id="rec_max_claim" value="0">
            <div class="modal-header text-white" style="background:#0284c7;">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-vault me-1"></i> تحصيل حق {{ $companyName }} من العضو البديل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="rec_info">—</div>
                    <div class="text-primary fw-bold fs-5 mt-1" id="rec_claim_display">0.00 ج</div>
                    <small class="text-muted" id="rec_months_info">—</small>
                </div>

                <div class="alert alert-warning py-2 mb-3" id="rec_unpaid_payout_warning" style="display:none; font-size:0.84rem;">
                    <i class="fa-solid fa-circle-info me-1"></i> ملاحظة: لسه لم يُسجّل صرف قبض الجمعية للعضو البديل حتى الآن — تأكد إنك تستلم المبلغ منه فعلياً لإيداعه في الخزنة.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">إيداع حق الشركة في محفظة / خزنة: <span class="text-danger">*</span></label>
                    <select name="account_id" id="rec_account_id" class="form-select" required>
                        <option value="">-- اختر المحفظة أو الخزنة (إجباري) --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ المسترد <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="1" name="amount" id="rec_amount_input" class="form-control" required>
                        <span class="input-group-text">ج</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">تاريخ الاستلام</label>
                    <input type="date" name="recovered_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>

                <div>
                    <label class="form-label fw-bold">ملاحظات</label>
                    <input type="text" name="notes" class="form-control" placeholder="اختياري">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn text-white fw-bold px-4" style="background:#0284c7;">تأكيد التحصيل والإيداع في الخزنة</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 4: تعديل عضو --}}
<div class="modal fade sub-action-modal" id="editMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.update_member') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="rememberActiveParentForSubmit();">
            @csrf
            <input type="hidden" name="share_id" id="edit_share_id">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-teal"><i class="fa-solid fa-user-pen me-1"></i> تعديل بيانات العضو</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3 fw-bold" id="edit_jamia_info">—</div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الاسم</label>
                    <input type="text" name="name" id="edit_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الهاتف</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control" dir="ltr">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الدور (شهر القبض)</label>
                    <select name="turn" id="edit_turn" class="form-select"></select>
                    <small class="text-muted">لو اخترت دور عضو تاني، الدورين هيتبدلوا مع بعض.</small>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_company" value="1" id="edit_is_company" onchange="onEditCompanyToggle(this)">
                    <label class="form-check-label fw-bold" for="edit_is_company">سهم {{ $companyName }}</label>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn fw-bold px-4" style="background:#0d9488; color:#fff;">حفظ التعديل</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 5: تعديل إعدادات الجمعية --}}
<div class="modal fade sub-action-modal" id="editJamiaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.update_jamia') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="rememberActiveParentForSubmit();">
            @csrf
            <input type="hidden" name="jamia_id" id="ej_id">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-teal"><i class="fa-solid fa-sliders me-1"></i> تعديل إعدادات الجمعية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">اسم الجمعية <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="ej_name" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">التحصيل يبدأ يوم</label>
                        <input type="number" name="collection_start_day" id="ej_start_day" class="form-control" min="1" max="10" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">آخر يوم للتحصيل (حد أقصى 10)</label>
                        <input type="number" name="collection_end_day" id="ej_end_day" class="form-control" min="1" max="10" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">حالة الجمعية</label>
                    <select name="status" id="ej_status" class="form-select">
                        <option value="active">نشطة</option>
                        <option value="completed">منتهية</option>
                        <option value="cancelled">ملغاة</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <textarea name="notes" id="ej_notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn fw-bold px-4" style="background:#0d9488; color:#fff;">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script>
    const COMPANY_NAME = @json($companyName);
    const OLD_MEMBERS  = @json(array_values((array) old('members', [])));
    const AR_MONTHS = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    const fmt = n => Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    let activeMonthFilter = 'all';
    let activeDirFilter   = 'all';
    let modalFilterMode   = {};
    let returnToModalId   = null;
    let submittingForm    = false;

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function openJamiaPopup(modalId) {
        const el = document.getElementById(modalId);
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    }

    function openSubModal(subModalId, parentModalId) {
        submittingForm = false;
        returnToModalId = parentModalId || null;
        if (parentModalId) {
            const pEl = document.getElementById(parentModalId);
            const pInst = pEl ? bootstrap.Modal.getInstance(pEl) : null;
            if (pInst) pInst.hide();
        }
        const sEl = document.getElementById(subModalId);
        if (sEl) bootstrap.Modal.getOrCreateInstance(sEl).show();
    }

    function rememberReopenModal(modalId) {
        submittingForm = true;
        try {
            if (modalId) sessionStorage.setItem('cashflows_reopen_modal', modalId);
        } catch (e) {}
    }

    function rememberActiveParentForSubmit() {
        rememberReopenModal(returnToModalId);
        return true;
    }

    // ── Tab Switching (مع حفظ التاب النشط) ──
    function switchTab(tabId, btn) {
        const pane = document.getElementById(tabId);
        if (!pane) return;
        document.querySelectorAll('.tab-content-pane').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.custom-tab-btn').forEach(b => b.classList.remove('active'));
        pane.style.display = 'block';
        if (btn) btn.classList.add('active');
        try { sessionStorage.setItem('cashflows_active_tab', tabId); } catch (e) {}
    }

    // ── Month Tab Filter (على المربعات من برة) ──
    function setMonthFilter(mode, btn) {
        activeMonthFilter = mode;
        document.querySelectorAll('.filter-pill').forEach(b => {
            b.classList.remove('active');
            b.style.background = '';
            b.style.color = '';
        });
        if (btn) {
            btn.classList.add('active');
            btn.style.background = '#0d9488';
            btn.style.color = '#fff';
        }
        applyMonthFilters();
    }

    function applyMonthFilters() {
        const q = (document.getElementById('monthMemberSearch')?.value || '').trim().toLowerCase();
        document.querySelectorAll('.month-jamia-tile-wrap').forEach(tile => {
            const hasUnpaid = tile.dataset.hasUnpaid === '1';
            const hasPaid   = tile.dataset.hasPaid === '1';
            const hasLate   = tile.dataset.hasLate === '1';
            const txt       = tile.dataset.search || '';
            let ok = true;
            if (activeMonthFilter === 'unpaid') ok = hasUnpaid;
            else if (activeMonthFilter === 'paid') ok = hasPaid;
            else if (activeMonthFilter === 'late') ok = hasLate;
            if (ok && q) ok = txt.includes(q);
            tile.style.display = ok ? '' : 'none';
        });
    }

    // ── فلترة جدول الأعضاء داخل بوبب الجمعية ──
    function filterModalRows(jamiaId, mode, btn) {
        if (mode) {
            modalFilterMode[jamiaId] = mode;
            const modalEl = document.getElementById(`monthJamiaModal-${jamiaId}`);
            if (modalEl) {
                modalEl.querySelectorAll('.modal-filter-btn').forEach(b => {
                    b.classList.remove('btn-dark', 'active');
                });
                if (btn) btn.classList.add('btn-dark', 'active');
            }
        }
        const curMode = modalFilterMode[jamiaId] || 'all';
        const q = (document.getElementById(`modalSearch-${jamiaId}`)?.value || '').trim().toLowerCase();
        document.querySelectorAll(`.modal-row-${jamiaId}`).forEach(row => {
            const paid = row.dataset.paid === '1';
            const late = row.dataset.late === '1';
            const txt  = row.dataset.search || '';
            let ok = true;
            if (curMode === 'unpaid') ok = !paid;
            else if (curMode === 'paid') ok = paid;
            else if (curMode === 'late') ok = late;
            if (ok && q) ok = txt.includes(q);
            row.style.display = ok ? '' : 'none';
        });
    }

    // ── Directory Tab Filter (مربعات دليل الجمعيات) ──
    function setDirFilter(mode, btn) {
        activeDirFilter = mode;
        document.querySelectorAll('.dir-filter-pill').forEach(b => {
            b.classList.remove('active');
            b.style.background = '';
            b.style.color = '';
        });
        if (btn) {
            btn.classList.add('active');
            btn.style.background = '#0d9488';
            btn.style.color = '#fff';
        }
        applyDirFilters();
    }

    function applyDirFilters() {
        const q = (document.getElementById('dirJamiaSearch')?.value || '').trim().toLowerCase();
        document.querySelectorAll('.dir-jamia-tile-wrap').forEach(tile => {
            const st = tile.dataset.status;
            const hasLate = tile.dataset.hasLate === '1';
            const hasTransferred = tile.dataset.hasTransferred === '1';
            const txt = tile.dataset.search || '';
            let ok = true;
            if (activeDirFilter === 'active') ok = (st === 'active');
            else if (activeDirFilter === 'late') ok = hasLate;
            else if (activeDirFilter === 'transferred') ok = hasTransferred;
            if (ok && q) ok = txt.includes(q);
            tile.style.display = ok ? '' : 'none';
        });
    }

    // ── Monthly installment two-way calc ──
    function calcMonthlyInstallment() {
        const total = parseFloat(document.getElementById('input_total_amount').value) || 0;
        const duration = parseInt(document.getElementById('input_duration').value) || 0;
        document.getElementById('display_installment').value = (total > 0 && duration > 0) ? (total / duration).toFixed(2) : '';
    }

    function calcTotalFromInstallment() {
        const inst = parseFloat(document.getElementById('display_installment').value) || 0;
        const duration = parseInt(document.getElementById('input_duration').value) || 0;
        if (inst > 0 && duration > 0) {
            document.getElementById('input_total_amount').value = (inst * duration).toFixed(2);
        }
    }

    function monthLabelForTurn(turn) {
        const startVal = document.getElementById('input_start_date').value;
        const base = startVal ? new Date(startVal + 'T00:00:00') : new Date();
        const d = new Date(base.getFullYear(), base.getMonth() + (turn - 1), 1);
        return `${AR_MONTHS[d.getMonth()]} ${d.getFullYear()}`;
    }

    // ── Members rows (يحافظ على البيانات المكتوبة عند تغيير العدد) ──
    function collectCurrentRows() {
        return Array.from(document.querySelectorAll('#members_container tr')).map(tr => ({
            name: tr.querySelector('.m-name').value,
            phone: tr.querySelector('.m-phone').value,
            turn: tr.querySelector('.m-turn').value,
            company: tr.querySelector('.m-company').checked,
        }));
    }

    function generateMemberRows() {
        const n = Math.max(0, Math.min(60, parseInt(document.getElementById('input_duration').value) || 0));
        let prev = collectCurrentRows();
        const firstBuild = prev.length === 0;
        if (firstBuild && OLD_MEMBERS.length > 0) {
            prev = OLD_MEMBERS.map(m => ({
                name: m.name ?? '',
                phone: m.phone ?? '',
                turn: m.turn ?? '',
                company: !!m.is_company,
            }));
        }
        const companyCount = parseInt(document.getElementById('input_company_count').value) || 0;
        const container = document.getElementById('members_container');
        container.innerHTML = '';

        for (let i = 0; i < n; i++) {
            const p = prev[i] || {};
            const isCompany = (firstBuild && OLD_MEMBERS.length === 0) ? (i < companyCount) : !!p.company;
            const turn = (p.turn && parseInt(p.turn) <= n) ? p.turn : (i + 1);
            const name = p.name !== undefined ? p.name : (isCompany ? COMPANY_NAME : '');
            const tr = document.createElement('tr');
            if (isCompany) tr.classList.add('is-company');
            tr.innerHTML = `
                <td><input type="number" class="form-control form-control-sm m-turn" name="members[${i}][turn]" min="1" max="${n}" value="${turn}" required oninput="refreshTurnLabel(this)"></td>
                <td class="text-muted m-month" style="font-size:.85rem;">${monthLabelForTurn(turn)}</td>
                <td><input type="text" class="form-control form-control-sm m-name" name="members[${i}][name]" placeholder="اسم العضو" value="${escapeHtml(name)}"></td>
                <td><input type="text" class="form-control form-control-sm m-phone" name="members[${i}][phone]" placeholder="اختياري" dir="ltr" value="${escapeHtml(p.phone || '')}"></td>
                <td class="text-center"><input type="checkbox" class="form-check-input m-company" name="members[${i}][is_company]" value="1" ${isCompany ? 'checked' : ''} onchange="onCompanyToggle(this)"></td>
            `;
            container.appendChild(tr);
        }
        syncCompanyCountInput();
        highlightDuplicateTurns();
    }

    function refreshTurnLabel(input) {
        const t = parseInt(input.value) || 1;
        input.closest('tr').querySelector('.m-month').textContent = monthLabelForTurn(t);
        highlightDuplicateTurns();
    }

    function highlightDuplicateTurns() {
        const inputs = Array.from(document.querySelectorAll('#members_container .m-turn'));
        const n = inputs.length;
        const counts = {};
        inputs.forEach(inp => {
            const v = parseInt(inp.value);
            if (v >= 1 && v <= n) counts[v] = (counts[v] || 0) + 1;
        });
        let bad = false;
        inputs.forEach(inp => {
            const v = parseInt(inp.value);
            const invalid = !v || v < 1 || v > n || counts[v] > 1;
            inp.classList.toggle('is-invalid', invalid);
            if (invalid) bad = true;
        });
        const warn = document.getElementById('turn_dup_warning');
        if (warn) {
            warn.style.display = bad ? 'block' : 'none';
            if (bad) warn.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> في أدوار مكررة أو خارج النطاق (1 إلى ${n}) — راجع الخانات المحددة بالأحمر.`;
        }
    }

    function onCompanyToggle(cb) {
        const tr = cb.closest('tr');
        const nameInput = tr.querySelector('.m-name');
        tr.classList.toggle('is-company', cb.checked);
        if (cb.checked && nameInput.value.trim() === '') nameInput.value = COMPANY_NAME;
        if (!cb.checked && nameInput.value.trim() === COMPANY_NAME) nameInput.value = '';
        syncCompanyCountInput();
    }

    function syncCompanyCountInput() {
        document.getElementById('input_company_count').value = document.querySelectorAll('#members_container .m-company:checked').length;
    }

    // تحديد عدد أسهم الشركة => يزوّد أو ينقص أسهم الشركة مع الحفاظ على التحديدات اليدوية
    function applyCompanyCount() {
        const rows = Array.from(document.querySelectorAll('#members_container tr'));
        const desired = Math.max(0, Math.min(rows.length, parseInt(document.getElementById('input_company_count').value) || 0));
        let checked = rows.filter(r => r.querySelector('.m-company').checked).length;

        if (checked < desired) {
            for (const tr of rows) {
                if (checked >= desired) break;
                const cb = tr.querySelector('.m-company');
                if (!cb.checked) {
                    cb.checked = true;
                    tr.classList.add('is-company');
                    const nameInput = tr.querySelector('.m-name');
                    if (nameInput.value.trim() === '') nameInput.value = COMPANY_NAME;
                    checked++;
                }
            }
        } else if (checked > desired) {
            for (let i = rows.length - 1; i >= 0 && checked > desired; i--) {
                const tr = rows[i];
                const cb = tr.querySelector('.m-company');
                if (cb.checked) {
                    cb.checked = false;
                    tr.classList.remove('is-company');
                    const nameInput = tr.querySelector('.m-name');
                    if (nameInput.value.trim() === COMPANY_NAME) nameInput.value = '';
                    checked--;
                }
            }
        }
    }

    function shuffleTurns() {
        const inputs = Array.from(document.querySelectorAll('#members_container .m-turn'));
        const turns = inputs.map((_, i) => i + 1);
        for (let i = turns.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [turns[i], turns[j]] = [turns[j], turns[i]];
        }
        inputs.forEach((inp, i) => { inp.value = turns[i]; refreshTurnLabel(inp); });
    }

    function resetTurns() {
        document.querySelectorAll('#members_container .m-turn').forEach((inp, i) => { inp.value = i + 1; refreshTurnLabel(inp); });
    }

    function validateNewJamia() {
        const turns = Array.from(document.querySelectorAll('#members_container .m-turn')).map(i => parseInt(i.value));
        const n = turns.length;
        const unique = new Set(turns);
        if (n < 2) {
            Swal.fire({ icon: 'warning', title: 'لازم عضوين على الأقل' });
            return false;
        }
        if (unique.size !== n || turns.some(t => !t || t < 1 || t > n)) {
            Swal.fire({ icon: 'warning', title: 'الأدوار مكررة', text: `كل عضو لازم يكون له دور مختلف من 1 لغاية ${n}.` });
            return false;
        }
        const s = parseInt(document.getElementById('input_col_start').value) || 1;
        const e = parseInt(document.getElementById('input_col_end').value) || 10;
        if (e > 10 || e < s) {
            Swal.fire({ icon: 'warning', title: 'فترة التحصيل غير صحيحة', text: 'آخر يوم للتحصيل لازم يكون بين يوم البداية ويوم 10.' });
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        calcMonthlyInstallment();
        generateMemberRows();

        document.querySelectorAll('.sub-action-modal').forEach(el => {
            el.addEventListener('hidden.bs.modal', function() {
                if (!submittingForm && returnToModalId) {
                    const targetId = returnToModalId;
                    returnToModalId = null;
                    openJamiaPopup(targetId);
                }
            });
        });

        try {
            const savedTab = sessionStorage.getItem('cashflows_active_tab');
            if (savedTab) {
                const btn = document.querySelector(`.custom-tab-btn[data-tab="${savedTab}"]`);
                if (btn) switchTab(savedTab, btn);
            }
            const reopenId = sessionStorage.getItem('cashflows_reopen_modal');
            if (reopenId) {
                sessionStorage.removeItem('cashflows_reopen_modal');
                openJamiaPopup(reopenId);
            }
        } catch (e) {}
        @if($errors->any() && old('form_kind') === 'new_jamia')
            bootstrap.Modal.getOrCreateInstance(document.getElementById('newJamiaModal')).show();
        @endif
    });

    // ── Collection (single / bulk) ──
    function openCollectModal(ids, total, info, parentModalId) {
        const holder = document.getElementById('collect_ids_holder');
        holder.innerHTML = ids.map(id => `<input type="hidden" name="installment_ids[]" value="${parseInt(id)}">`).join('');
        document.getElementById('collect_info').innerText = info;
        document.getElementById('collect_amount_display').innerText = `${fmt(total)} ج`;
        openSubModal('collectModal', parentModalId);
    }

    function selectedFor(jamiaId) {
        return Array.from(document.querySelectorAll(`.sel-jamia-${jamiaId}:checked`));
    }

    function refreshBulkBar(jamiaId) {
        const sel = selectedFor(jamiaId);
        const total = sel.reduce((s, cb) => s + parseFloat(cb.dataset.amount), 0);
        document.getElementById(`bulk-btn-${jamiaId}`).disabled = sel.length === 0;
        document.getElementById(`bulk-info-${jamiaId}`).innerText = sel.length
            ? `محدد ${sel.length} عضو — الإجمالي ${fmt(total)} ج`
            : 'حدد الأعضاء اللي دفعوا علشان تسجل تحصيلهم مرة واحدة.';
    }

    function toggleAllUnpaid(jamiaId, checked) {
        document.querySelectorAll(`.sel-jamia-${jamiaId}`).forEach(cb => {
            const row = cb.closest('tr');
            if (!checked || !row || row.style.display !== 'none') {
                cb.checked = checked;
            }
        });
        refreshBulkBar(jamiaId);
    }

    function openBulkCollect(jamiaId, jamiaName, parentModalId) {
        const sel = selectedFor(jamiaId);
        if (!sel.length) return;
        const total = sel.reduce((s, cb) => s + parseFloat(cb.dataset.amount), 0);
        const names = sel.map(cb => cb.dataset.name).join('، ');
        openCollectModal(sel.map(cb => cb.value), total, `${jamiaName} — ${sel.length} عضو: ${names}`, parentModalId);
    }

    // ── Payout & Company Receipt (إجباري اختيار الخزنة) ──
    let currentTransferClaimMax = 0;

    function openPayoutModal(opts) {
        const isCompany = !!opts.isCompany;
        document.getElementById('payout_share_id').value = opts.shareId;
        document.getElementById('payout_max_amount').value = opts.amount;
        document.getElementById('payout_is_company_flag').value = isCompany ? '1' : '0';
        document.getElementById('payout_info').innerText = opts.info;
        document.getElementById('payout_amount_display').innerText = `${fmt(opts.amount)} ج`;
        document.getElementById('payout_amount_input').value = opts.amount;
        document.getElementById('payout_amount_input').max = opts.amount;
        document.getElementById('payout_account_id').value = '';

        const header = document.getElementById('payout_modal_header');
        const title  = document.getElementById('payout_modal_title');
        const amtDisp= document.getElementById('payout_amount_display');
        const accLbl = document.getElementById('payout_account_label');
        const amtLbl = document.getElementById('payout_amount_label');
        const btn    = document.getElementById('payout_submit_btn');
        const compNt = document.getElementById('payout_company_note');
        const warn   = document.getElementById('payout_warning');

        if (isCompany) {
            header.className = 'modal-header bg-primary text-white';
            title.innerHTML  = `<i class="fa-solid fa-building-circle-check me-1"></i> تسجيل استلام ${escapeHtml(COMPANY_NAME)} وإيداع في خزنة`;
            amtDisp.className = 'text-primary fw-bold fs-5 mt-1';
            accLbl.innerHTML = 'إيداع المبلغ في محفظة / خزنة: <span class="text-danger">*</span>';
            amtLbl.innerHTML = 'المبلغ المستلم للشركة <span class="text-danger">*</span>';
            btn.className    = 'btn btn-primary fw-bold px-4';
            btn.innerText    = 'تأكيد استلام الشركة والإيداع';
            compNt.style.display = 'block';
            warn.style.display   = 'none';
        } else {
            header.className = 'modal-header bg-danger text-white';
            title.innerHTML  = '<i class="fa-solid fa-money-bill-transfer me-1"></i> صرف القبض لصاحب الدور';
            amtDisp.className = 'text-danger fw-bold fs-5 mt-1';
            accLbl.innerHTML = 'الصرف من محفظة / خزنة: <span class="text-danger">*</span>';
            amtLbl.innerHTML = 'المبلغ المصروف <span class="text-danger">*</span>';
            btn.className    = 'btn btn-danger fw-bold px-4';
            btn.innerText    = 'تأكيد الصرف';
            compNt.style.display = 'none';
            if ((opts.unpaidCount || 0) > 0) {
                warn.style.display = 'block';
                warn.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> لسه في <b>${opts.unpaidCount}</b> عضو ما دفعش قسط الشهر ده.`;
            } else {
                warn.style.display = 'none';
            }
        }

        const recSec = document.getElementById('payout_recover_section');
        const alsoCb = document.getElementById('payout_also_recover');
        currentTransferClaimMax = parseFloat(opts.claimAmount || 0);
        if (!isCompany && opts.isTransferred && !opts.isClaimRecovered && currentTransferClaimMax > 0) {
            recSec.style.display = 'block';
            alsoCb.checked = false;
            document.getElementById('payout_recover_amount').value = currentTransferClaimMax;
            document.getElementById('payout_recover_amount').max   = currentTransferClaimMax;
            document.getElementById('payout_recover_account_id').value = '';
            toggleRecoverWithPayout(false);
        } else {
            recSec.style.display = 'none';
            alsoCb.checked = false;
            toggleRecoverWithPayout(false);
        }

        openSubModal('payoutModal', opts.parentModalId);
    }

    function toggleRecoverWithPayout(checked) {
        const box = document.getElementById('payout_recover_fields');
        const sel = document.getElementById('payout_recover_account_id');
        const inp = document.getElementById('payout_recover_amount');
        box.style.display = checked ? 'block' : 'none';
        sel.required = !!checked;
        inp.required = !!checked;
    }

    function validatePayoutForm() {
        const acc = document.getElementById('payout_account_id').value;
        const amt = parseFloat(document.getElementById('payout_amount_input').value) || 0;
        const max = parseFloat(document.getElementById('payout_max_amount').value) || 0;
        if (!acc) {
            Swal.fire({ icon: 'warning', title: 'اختيار الخزنة إجباري', text: 'لازم تختار المحفظة أو الخزنة أولاً.' });
            return false;
        }
        if (amt <= 0 || (max > 0 && amt > max)) {
            Swal.fire({ icon: 'warning', title: 'مبلغ غير صالح', text: `المبلغ لازم يكون أكبر من صفر وألا يتجاوز ${fmt(max)} ج.` });
            return false;
        }
        if (document.getElementById('payout_also_recover').checked) {
            const recAcc = document.getElementById('payout_recover_account_id').value;
            const recAmt = parseFloat(document.getElementById('payout_recover_amount').value) || 0;
            if (!recAcc) {
                Swal.fire({ icon: 'warning', title: 'خزنة حق الشركة مطلوبة', text: 'اختر الخزنة التي سينزل فيها حق الشركة المسترد.' });
                return false;
            }
            if (recAmt <= 0 || recAmt > currentTransferClaimMax) {
                Swal.fire({ icon: 'warning', title: 'مبلغ الاسترداد غير صالح', text: `المبلغ المسترد لازم يكون بين 1 و ${fmt(currentTransferClaimMax)} ج.` });
                return false;
            }
        }
        return rememberActiveParentForSubmit();
    }

    // ── Transfer (دخول بديل مكان الشركة) ──
    function openTransferModal(opts) {
        document.getElementById('tr_share_id').value = opts.shareId;
        document.getElementById('tr_duration').value = opts.duration;
        document.getElementById('tr_installment').value = opts.installment;
        document.getElementById('tr_payout_amount').value = opts.payoutAmount;
        document.getElementById('tr_info').innerText = `${opts.jamiaName} — دور ${opts.shareNumber}`;
        document.getElementById('tr_sub_info').innerText = `المدة: ${opts.duration} شهر | قسط الشهر: ${fmt(opts.installment)} ج | القبض: ${fmt(opts.payoutAmount)} ج`;
        document.getElementById('tr_new_name').value = '';
        document.getElementById('tr_phone').value = '';

        const [sy, sm] = (opts.startDate || '').split('-').map(Number);
        const sel = document.getElementById('tr_from_month');
        sel.innerHTML = '';
        for (let m = 2; m <= opts.duration; m++) {
            const dt = new Date(sy, (sm - 1) + (m - 1), 1);
            const yStr = dt.getFullYear();
            const mStr = String(dt.getMonth() + 1).padStart(2, '0');
            const val  = `${yStr}-${mStr}-01`;
            const rem  = opts.duration - m + 1;
            const opt  = document.createElement('option');
            opt.value = val;
            opt.dataset.idx = m;
            opt.textContent = `الشهر ${m} (${AR_MONTHS[dt.getMonth()]} ${yStr}) — قبله ${m - 1} اسم، وباقي ${rem} قسط`;
            sel.appendChild(opt);
        }
        const defaultIdx = Math.min(opts.duration, Math.max(2, opts.shareNumber));
        const matchOpt = Array.from(sel.options).find(o => parseInt(o.dataset.idx) === defaultIdx);
        if (matchOpt) matchOpt.selected = true;
        onTransferMonthChange();
        openSubModal('transferModal', opts.parentModalId);
    }

    function onTransferMonthChange() {
        const sel = document.getElementById('tr_from_month');
        const opt = sel.options[sel.selectedIndex];
        const idx = opt ? parseInt(opt.dataset.idx) : 2;
        const paidInp = document.getElementById('tr_paid_months');
        paidInp.value = Math.max(1, idx - 1);
        paidInp.max = Math.max(1, idx - 1);
        updateTransferSummary();
    }

    function updateTransferSummary() {
        const duration = parseInt(document.getElementById('tr_duration').value) || 0;
        const instAmt  = parseFloat(document.getElementById('tr_installment').value) || 0;
        const sel      = document.getElementById('tr_from_month');
        const opt      = sel.options[sel.selectedIndex];
        const fromIdx  = opt ? parseInt(opt.dataset.idx) : 2;
        const rem      = Math.max(0, duration - fromIdx + 1);
        const paid     = parseInt(document.getElementById('tr_paid_months').value) || 0;
        const claim    = paid * instAmt;
        document.getElementById('tr_claim_display').innerText = `${fmt(claim)} ج (${paid} اسم)`;
        document.getElementById('tr_rem_display').innerText   = `${rem} شهر (${fmt(rem * instAmt)} ج)`;
        document.getElementById('tr_max_months_hint').innerText = `الحد الأقصى المتاح قبل شهر البدء: ${fromIdx - 1} اسم (حتى لا يزيد الإجمالي عن ${duration} شهر).`;
    }

    function validateTransferForm() {
        const name = (document.getElementById('tr_new_name').value || '').trim();
        const duration = parseInt(document.getElementById('tr_duration').value) || 0;
        const sel = document.getElementById('tr_from_month');
        const opt = sel.options[sel.selectedIndex];
        const fromIdx = opt ? parseInt(opt.dataset.idx) : 2;
        const rem = Math.max(0, duration - fromIdx + 1);
        const paid = parseInt(document.getElementById('tr_paid_months').value) || 0;

        if (!name || name === COMPANY_NAME) {
            Swal.fire({ icon: 'warning', title: 'اسم غير صالح', text: 'اكتب اسم الشخص الجديد الذي سيدخل مكان الشركة.' });
            return false;
        }
        if (paid < 1 || paid + rem > duration) {
            Swal.fire({
                icon: 'warning',
                title: 'عدد الأسماء غير متوافق',
                text: `عدد الأسماء المدفوعة (${paid}) + الأقساط المتبقية (${rem}) لازم ما يتجاوزش مدة الجمعية (${duration} شهر).`
            });
            return false;
        }
        return rememberActiveParentForSubmit();
    }

    // ── Recover Claim (تحصيل حق الشركة من العضو البديل وإيداع في خزنة إجباري) ──
    function openRecoverModal(opts) {
        document.getElementById('rec_share_id').value = opts.shareId;
        document.getElementById('rec_max_claim').value = opts.claimAmount;
        document.getElementById('rec_info').innerText = `${opts.jamiaName} — ${opts.memberName} (دور ${opts.shareNumber})`;
        document.getElementById('rec_claim_display').innerText = `${fmt(opts.claimAmount)} ج`;
        document.getElementById('rec_months_info').innerText = `قيمة ${opts.paidMonths} اسم تحملتها ${COMPANY_NAME} قبل دخول البديل`;
        document.getElementById('rec_amount_input').value = opts.claimAmount;
        document.getElementById('rec_amount_input').max = opts.claimAmount;
        document.getElementById('rec_account_id').value = '';
        document.getElementById('rec_unpaid_payout_warning').style.display = opts.isCollected ? 'none' : 'block';
        openSubModal('recoverModal', opts.parentModalId);
    }

    function validateRecoverForm() {
        const acc = document.getElementById('rec_account_id').value;
        const amt = parseFloat(document.getElementById('rec_amount_input').value) || 0;
        const max = parseFloat(document.getElementById('rec_max_claim').value) || 0;
        if (!acc) {
            Swal.fire({ icon: 'warning', title: 'اختيار الخزنة إجباري', text: 'لازم تختار المحفظة أو الخزنة اللي هتنزل فيها الفلوس.' });
            return false;
        }
        if (amt <= 0 || amt > max) {
            Swal.fire({ icon: 'warning', title: 'مبلغ غير صالح', text: `المبلغ المسترد لازم يكون بين 1 و ${fmt(max)} ج.` });
            return false;
        }
        return rememberActiveParentForSubmit();
    }

    // ── Edit member ──
    function openEditMember(btn, parentModalId) {
        const d = btn.dataset;
        document.getElementById('edit_share_id').value = d.id;
        document.getElementById('edit_name').value = d.name || '';
        document.getElementById('edit_phone').value = d.phone || '';
        document.getElementById('edit_is_company').checked = d.company === '1';
        document.getElementById('edit_jamia_info').innerText = d.jamia;

        const [sy, sm] = (d.start || '').split('-').map(Number);
        const sel = document.getElementById('edit_turn');
        sel.innerHTML = '';
        for (let t = 1; t <= parseInt(d.duration); t++) {
            const dt = new Date(sy, (sm - 1) + (t - 1), 1);
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = `دور ${t} — ${AR_MONTHS[dt.getMonth()]} ${dt.getFullYear()}`;
            if (String(t) === d.turn) opt.selected = true;
            sel.appendChild(opt);
        }
        openSubModal('editMemberModal', parentModalId);
    }

    function onEditCompanyToggle(cb) {
        const nameInput = document.getElementById('edit_name');
        if (cb.checked && nameInput.value.trim() === '') nameInput.value = COMPANY_NAME;
        if (!cb.checked && nameInput.value.trim() === COMPANY_NAME) nameInput.value = '';
    }

    // ── Edit jamia ──
    function openEditJamia(btn, parentModalId) {
        const d = btn.dataset;
        document.getElementById('ej_id').value = d.id;
        document.getElementById('ej_name').value = d.name || '';
        document.getElementById('ej_start_day').value = d.startDay || 1;
        document.getElementById('ej_end_day').value = d.endDay || 10;
        document.getElementById('ej_status').value = d.status || 'active';
        document.getElementById('ej_notes').value = d.notes || '';
        openSubModal('editJamiaModal', parentModalId);
    }

    // ── Charts ──
    document.addEventListener('DOMContentLoaded', function() {
        const labels     = @json($chartLabels);
        const collection = @json($chartCollection);
        const payout     = @json($chartPayout);
        const netFlow    = @json($chartNet);

        new Chart(document.getElementById('cashflowYearChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'التحصيل المتوقع من الأعضاء (الداخل)', data: collection, backgroundColor: 'rgba(16, 185, 129, 0.75)', borderColor: '#10b981', borderWidth: 1, borderRadius: 6, order: 2 },
                    { label: 'القبض المصروف للأعضاء (الخارج)', data: payout, backgroundColor: 'rgba(239, 68, 68, 0.75)', borderColor: '#ef4444', borderWidth: 1, borderRadius: 6, order: 3 },
                    { label: 'صافي الشهر', data: netFlow, type: 'line', borderColor: '#0d9488', backgroundColor: 'rgba(13, 148, 136, 0.15)', borderWidth: 2.5, fill: false, tension: 0.3, pointRadius: 4, pointHoverRadius: 6, order: 1 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', rtl: true, labels: { font: { family: 'Cairo, sans-serif', weight: 'bold' } } },
                    tooltip: { rtl: true, callbacks: { label: ctx => ` ${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString()} ج` } }
                },
                scales: {
                    y: { ticks: { callback: v => Number(v).toLocaleString() + ' ج', font: { family: 'Cairo, sans-serif' } } },
                    x: { ticks: { font: { family: 'Cairo, sans-serif', weight: 'bold' } } }
                }
            }
        });

        new Chart(document.getElementById('monthRatioChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['اتحصل', 'لسه ما اتحصلش', 'اتصرف قبض', 'قبض لسه ما اتصرفش'],
                datasets: [{
                    data: [{{ $collectedThisMonth }}, {{ $remainingCollection }}, {{ $payoutDone }}, {{ $payoutRemaining }}],
                    backgroundColor: ['#10b981', '#fef3c7', '#ef4444', '#fee2e2'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', rtl: true, labels: { boxWidth: 12, font: { family: 'Cairo, sans-serif', size: 11 } } },
                    tooltip: { rtl: true, callbacks: { label: ctx => ` ${ctx.label}: ${Number(ctx.raw).toLocaleString()} ج` } }
                },
                cutout: '68%'
            }
        });
    });
</script>

</body>
</html>
