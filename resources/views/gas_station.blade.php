<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محطة العمليات - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        /* ── Tabs & Analytics Styling ── */
        .gas-main-tabs {
            background: #f1f5f9;
            padding: 7px;
            border-radius: 14px;
            border: 1.5px solid #cbd5e1;
            display: inline-flex;
            gap: 8px;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.05);
        }
        .gas-main-tabs .nav-link {
            border-radius: 10px;
            color: #1e293b;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-bottom-width: 3px;
            font-weight: 700;
            font-size: 0.92rem;
            padding: 9px 20px;
            transition: all 0.15s ease;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.06);
            cursor: pointer;
        }
        .gas-main-tabs .nav-link:hover {
            color: #0f172a;
            background: #f8fafc;
            border-color: #94a3b8;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(15, 23, 42, 0.1);
        }
        .gas-main-tabs .nav-link.active {
            background: linear-gradient(135deg, #0d1f35 0%, #1e3a8a 100%);
            color: #fff !important;
            border-color: #0d1f35;
            border-bottom-color: #f59e0b;
            box-shadow: 0 4px 12px rgba(13, 31, 53, 0.25);
            transform: translateY(-1px);
        }

        /* ── أزرار واضحة البارزة في تاب التقارير والمقارنات ── */
        .btn-report-solid {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 8px;
            font-weight: 700;
            padding: 6px 14px;
            border: 1.5px solid #cbd5e1;
            border-bottom-width: 3px;
            background: #ffffff;
            color: #1e293b;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.06);
            transition: all 0.15s ease;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-report-solid:hover {
            background: #f8fafc;
            border-color: #64748b;
            color: #0f172a;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(15, 23, 42, 0.12);
        }
        .btn-report-solid.active-blue,
        .btn-report-solid.primary-btn {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            border-color: #1e40af;
            border-bottom-color: #1e3a8a;
            box-shadow: 0 3px 8px rgba(37, 99, 235, 0.28);
        }
        .btn-report-solid.active-blue:hover,
        .btn-report-solid.primary-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: #ffffff !important;
        }
        .btn-report-solid.active-dark {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff !important;
            border-color: #0f172a;
            border-bottom-color: #020617;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.25);
        }
        .btn-report-solid.active-danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff !important;
            border-color: #991b1b;
            border-bottom-color: #7f1d1d;
            box-shadow: 0 3px 8px rgba(220, 38, 38, 0.28);
        }
        .btn-report-solid.active-danger:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
            color: #ffffff !important;
        }
        .btn-entity-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-bottom-width: 2.5px;
            color: #1e3a8a !important;
            font-weight: 700;
            font-size: 0.82rem;
            text-decoration: none !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-entity-chip:hover {
            background: #eff6ff;
            border-color: #2563eb;
            color: #1d4ed8 !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(37, 99, 235, 0.15);
        }
        /* ── In-Form Trip Box & Metrics ── */
        .drivvo-trip-box {
            background: linear-gradient(135deg, rgba(37,99,235,0.05) 0%, rgba(16,185,129,0.05) 100%);
            border: 1.5px solid rgba(59,130,246,0.3);
            border-radius: var(--r-lg);
            padding: 16px;
            margin-bottom: 18px;
            position: relative;
        }
        .drivvo-trip-metric {
            background: #ffffff;
            border: 1px solid var(--c-border);
            border-radius: var(--r-md);
            padding: 10px 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .drivvo-trip-metric .metric-title {
            font-size: 0.74rem;
            color: var(--c-text-muted);
            font-weight: 700;
            margin-bottom: 4px;
        }
        .drivvo-trip-metric .metric-value {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--c-navy);
            font-variant-numeric: tabular-nums;
        }
        .drivvo-kpi-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--r-lg);
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .drivvo-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .drivvo-kpi-card::before {
            content: ""; position: absolute; top: 0; right: 0; bottom: 0;
            width: 4px; background: var(--c-accent);
        }
        .drivvo-kpi-card.c-blue::before   { background: #3b82f6; }
        .drivvo-kpi-card.c-amber::before  { background: #f59e0b; }
        .drivvo-kpi-card.c-green::before  { background: #10b981; }
        .drivvo-kpi-card.c-purple::before { background: #8b5cf6; }
        .drivvo-kpi-card.c-red::before    { background: #ef4444; }
        .drivvo-kpi-card.c-cyan::before   { background: #06b6d4; }
        .drivvo-kpi-card .kpi-label { font-size: 0.8rem; color: var(--c-text-muted); font-weight: 600; margin-bottom: 6px; }
        .drivvo-kpi-card .kpi-val   { font-size: 1.45rem; font-weight: 800; color: var(--c-text); font-variant-numeric: tabular-nums; }
        .drivvo-kpi-card .kpi-icon  { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); font-size: 2.2rem; opacity: 0.12; }

        .table-drivvo {
            width: 100%; border-collapse: separate; border-spacing: 0;
            font-size: 0.86rem;
        }
        .table-drivvo thead th {
            background: var(--c-navy); color: #fff;
            padding: 10px 12px; font-weight: 600; font-size: 0.82rem;
            white-space: nowrap; text-align: center;
        }
        .table-drivvo tbody td {
            padding: 9px 12px; border-bottom: 1px solid var(--c-border);
            text-align: center; vertical-align: middle;
            color: var(--c-text); background: var(--c-surface);
        }
        .table-drivvo tbody tr:hover td {
            background: var(--c-surface-2);
        }
        .tank-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 999px; font-size: 0.72rem; font-weight: 700;
        }
        .tank-badge.full { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .tank-badge.partial { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .tank-badge.custody { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

        /* ── كفاءة الاستهلاك ومؤشرات المعدل الواضحة عالية التباين ── */
        .rate-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
            letter-spacing: 0.2px;
        }
        .rate-badge.good {
            background: #ecfdf5 !important;
            color: #047857 !important;
            border: 1.5px solid #10b981 !important;
            box-shadow: 0 1px 2px rgba(16,185,129,0.12);
        }
        .rate-badge.danger {
            background: #fef2f2 !important;
            color: #b91c1c !important;
            border: 1.5px solid #ef4444 !important;
            box-shadow: 0 1px 2px rgba(239,68,68,0.15);
        }
        .rate-badge.waiting {
            background: #fffbeb !important;
            color: #92400e !important;
            border: 1.5px solid #f59e0b !important;
            box-shadow: 0 1px 2px rgba(245,158,11,0.12);
        }

        /* ── زر جانبي عائم لرادار الشبهات ── */
        .radar-floating-trigger {
            position: fixed;
            bottom: 24px;
            left: 24px;
            z-index: 1040;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .radar-floating-trigger:hover {
            transform: translateY(-3px) scale(1.03);
        }
        .radar-floating-btn {
            background: #ffffff;
            border: 2px solid #ef4444;
            color: #ef4444;
            padding: 10px 18px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.25);
            font-weight: 700;
            font-size: 0.88rem;
        }
        .radar-floating-btn.has-alerts {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            color: #ffffff;
            border-color: #991b1b;
            box-shadow: 0 6px 24px rgba(239, 68, 68, 0.4);
            animation: radarPulse 2.2s infinite;
        }
        @keyframes radarPulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
            70% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* ── تصميم بطاقات تقرير Drivvo للسائق والشاحنة ── */
        .drivvo-modal-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            position: relative;
            height: 100%;
        }
        .drivvo-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .drivvo-card-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #fff7ed;
            color: #ea580c;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .drivvo-card-title {
            font-weight: 700;
            color: #ea580c;
            font-size: 0.96rem;
            margin: 0;
        }
        .drivvo-card-val-huge {
            font-size: 1.85rem;
            font-weight: 800;
            color: #ea580c;
            line-height: 1.15;
            margin: 6px 0;
            font-variant-numeric: tabular-nums;
        }
        .drivvo-card-sub {
            font-size: 0.8rem;
            color: #64748b;
        }
        .drivvo-gauge-track {
            position: relative;
            height: 8px;
            background: linear-gradient(to right, #fee2e2 0%, #fef3c7 50%, #dcfce7 100%);
            border-radius: 999px;
            margin: 14px 0 6px;
        }
        .drivvo-gauge-track.reverse-colors {
            background: linear-gradient(to right, #dcfce7 0%, #fef3c7 50%, #fee2e2 100%);
        }
        .drivvo-gauge-pip {
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ea580c;
            border: 3px solid #ffffff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.25);
        }
        .drivvo-gauge-avg {
            position: absolute;
            top: -4px;
            bottom: -4px;
            width: 2px;
            background: #475569;
            transform: translateX(-50%);
        }

    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}
    @media print {
        .sidebar, .mobile-header, .radar-floating-trigger, .gas-main-tabs, .no-print { display: none !important; }
        .main-content { margin-right: 0 !important; width: 100% !important; padding: 0 !important; }
        body { background: #fff !important; }
    }
    </style>
</head>
<body>
@include('sidebar')

{{-- الزر الجانبي العائم لرادار كشف الشبهات والهدر --}}
<div class="radar-floating-trigger" onclick="openRadarModal()" title="انقر لفتح رادار كشف الشبهات والهدر">
    <div class="radar-floating-btn {{ count($suspiciousAlerts ?? []) > 0 ? 'has-alerts' : '' }}">
        <i class="fa fa-triangle-exclamation"></i>
        <span>رادار الشبهات والهدر</span>
        @if(count($suspiciousAlerts ?? []) > 0)
            <span class="badge bg-white text-danger fw-bolder px-2">{{ count($suspiciousAlerts) }}</span>
        @else
            <span class="badge bg-success text-white px-2"><i class="fa fa-check"></i></span>
        @endif
    </div>
</div>

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
    <div class="gas-hero mb-3">
        <div class="d-flex justify-content-between align-items-center position-relative" style="z-index:2;">
            <div class="d-flex gap-3 align-items-center">
                <div class="hero-icon"><i class="fa fa-gas-pump"></i></div>
                <div>
                    <h2>محطة عمليات الوقود والشاحنات</h2>
                    <div class="sub">تسجيل السحوبات، العهد النقدية، وتحليلات استهلاك وقود الشاحنات وكفاءتها</div>
                </div>
            </div>
            <i class="fa fa-truck-moving" style="font-size:3rem; opacity:0.2;"></i>
        </div>
    </div>

    {{-- ═══════════ Nav Tabs (التابات الرئيسية) ═══════════ --}}
    @php
        $hasExplicitPeriod = request()->filled('period') && request('period') !== 'month';
        $hasActiveFilters = (
            request()->hasAny(['filter_company','filter_station','filter_car','filter_driver','filter_from','filter_to'])
            && collect(request()->only(['filter_company','filter_station','filter_car','filter_driver','filter_from','filter_to']))->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty()
        ) || $hasExplicitPeriod;
        $activeTab = request('tab', 'entry');
        if (!in_array($activeTab, ['entry', 'analytics', 'comparison'])) $activeTab = 'entry';
        if (($hasActiveFilters || request()->has('period')) && !request()->has('tab')) $activeTab = 'analytics';
    @endphp
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <ul class="nav nav-pills gas-main-tabs" id="gasTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'entry' ? 'active' : '' }}" id="tab-entry-btn" data-bs-toggle="pill" data-bs-target="#tab-entry-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'entry' ? 'true' : 'false' }}">
                    <i class="fa fa-gas-pump me-2 text-warning"></i> تسجيل عملية وقود وعُهدة
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'analytics' ? 'active' : '' }}" id="tab-analytics-btn" data-bs-toggle="pill" data-bs-target="#tab-analytics-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'analytics' ? 'true' : 'false' }}">
                    <i class="fa fa-chart-pie me-2 text-info"></i> الإحصائيات والتقارير العامة
                    @if($hasActiveFilters)
                        <span class="badge bg-warning text-dark ms-1"><i class="fa fa-filter"></i></span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'comparison' ? 'active' : '' }}" id="tab-comparison-btn" data-bs-toggle="pill" data-bs-target="#tab-comparison-pane" type="button" role="tab" aria-selected="{{ $activeTab === 'comparison' ? 'true' : 'false' }}" onclick="initComparisonTab()">
                    <i class="fa fa-scale-balanced me-2 text-success"></i> تابة المقارنات بين الشاحنات ⚖️
                </button>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-2 flex-wrap no-print">
            <a href="{{ route('gas.export_report', ['mode' => 'company_report', 'period' => 'week', 'filter_company' => request('filter_company', $defaultCompany->id ?? '')]) }}"
               target="_blank"
               class="btn btn-primary fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2"
               style="background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 100%); border: none;"
               title="إصدار التقرير الأسبوعي الرسمي لشركة النقل بالشارتات وجاهز للطباعة أو الحفظ PDF">
                <i class="fa fa-file-pdf text-warning"></i>
                <span>تقرير شركة النقل الأسبوعي (PDF)</span>
            </a>

            <button type="button" class="btn btn-outline-secondary fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" onclick="openRadarSettingsModal()" title="انقر لضبط معايير رادار كشف الشبهات والهدر ونسبة السماح">
                <i class="fa fa-sliders text-primary"></i>
                <span>معايير الرادار</span>
                <span class="badge bg-light text-dark border ms-1" style="font-size:0.75rem;">{{ $radarMinEff }} كم/لتر · {{ $radarMaxCostKm }} ج/كم · ±{{ $radarTolerancePct }}%</span>
            </button>

            @if(count($suspiciousAlerts ?? []) > 0)
                <button type="button" class="btn btn-danger fw-bold shadow-sm d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 animate-pulse" onclick="openRadarModal()" title="انقر لفتح رادار كشف الشبهات والهدر">
                    <i class="fa fa-triangle-exclamation"></i>
                    <span>رادار كشف الشبهات والهدر</span>
                    <span class="badge bg-white text-danger fw-bolder">{{ count($suspiciousAlerts) }}</span>
                </button>
            @else
                <button type="button" class="btn btn-outline-success fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="openRadarModal()" title="انقر لعرض فحص الأمان">
                    <i class="fa fa-shield-check text-success"></i>
                    <span>فحص الأمان والنزاهة</span>
                </button>
            @endif
        </div>
    </div>

    <div class="tab-content" id="gasTabsContent">
        {{-- ═══════════ TAB 1: تسجيل عملية وقود وعُهدة ═══════════ --}}
        <div class="tab-pane fade {{ $activeTab === 'entry' ? 'show active' : '' }}" id="tab-entry-pane" role="tabpanel" aria-labelledby="tab-entry-btn">
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
                            @php
                                $defCompanyId   = $defaultCompany->id ?? '';
                                $defCompanyName = $defaultCompany ? ($defaultCompany->company_name ?? $defaultCompany->name) : '';
                            @endphp
                            <input type="hidden" name="company_id"   id="company_id_hidden"   value="{{ old('company_id', $defCompanyId) }}">
                            <input type="hidden" name="company_name" id="company_name_hidden" value="{{ old('company_name', $defCompanyName) }}">

                            <div class="combo-wrap" id="wrap_company">
                                <div class="combo-input-row">
                                    <button type="button" class="combo-toggle-btn" onclick="toggleCombo('company')" tabindex="-1">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                    <input type="text" id="combo_company" class="combo-text"
                                           placeholder="اختر أو اكتب اسماً جديداً..."
                                           autocomplete="off" value="{{ old('company_name', $defCompanyName) }}"
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

                

                    {{-- 🚚 بيانات الشاحنة الأساسية والسائق --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-pro-label mb-1 d-flex align-items-center justify-content-between">
                                <span class="text-truncate fw-bold text-primary"><i class="fa fa-truck-front me-1"></i> رقم الشاحنة / اللوحة</span>
                                <span class="badge bg-primary text-white" style="font-size:0.65rem;">مربوط بالعداد</span>
                            </label>
                            <input type="text" name="car_number" id="car_number_input" class="form-pro-control fw-bold"
                                   placeholder="مثال: د ن ر 5421 أو 12"
                                   list="known_trucks_list"
                                   style="height: 44px; border: 2px solid #2563eb; background-color: #f0f7ff; color: #1e3a8a; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);"
                                   required autocomplete="off" value="{{ old('car_number') }}"
                                   oninput="checkVehicleOdometerByCar(this.value)">
                            <datalist id="known_trucks_list">
                                @foreach($knownVehicles as $v)
                                    @php
                                        $tCarNum = ($v->car_number && $v->car_number != '-') ? $v->car_number : $v->vehicle_key;
                                    @endphp
                                    <option value="{{ $tCarNum }}">
                                        آخر عداد: {{ $v->last_odometer ? number_format($v->last_odometer) . ' كم' : 'جديد' }} (السائق: {{ $v->driver_name }})
                                    </option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="driver_car" id="driver_car_input" value="{{ old('driver_car') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-pro-label mb-1 d-flex align-items-center justify-content-between">
                                <span class="text-truncate"><i class="fa fa-user-tie text-info me-1"></i> اسم السائق القائم بالرحلة</span>
                                <span class="text-muted" style="font-size:0.68rem;">(للمتابعة)</span>
                            </label>
                            <input type="text" name="driver_name" id="driver_name_input" class="form-pro-control"
                                   placeholder="اسم السائق (للمتابعة)"
                                   list="known_drivers_list"
                                   style="height: 44px;"
                                   autocomplete="off" value="{{ old('driver_name') }}">
                            <datalist id="known_drivers_list">
                                @foreach($knownVehicles as $v)
                                    @if($v->driver_name && $v->driver_name != '-' && $v->driver_name != $v->car_number)
                                        <option value="{{ $v->driver_name }}"></option>
                                    @endif
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- ⛽ تفاصيل التفويلة والرحلة --}}
                    <div class="drivvo-trip-box">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <strong class="text-dark" style="font-size: 0.95rem;">⚡ تفاصيل التفويلة وحاسبة الرحلة والكفاءة</strong>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-bold" id="badge_trip_mode">
                                <i class="fa fa-circle-check me-1"></i> تفويلة كاملة (Full Tank)
                            </span>
                        </div>

                        {{-- السطر 1: الوقود + الكمية + سعر اللتر + إجمالي الوقود --}}
                        <div class="row g-2 mb-3 align-items-end" id="fuel_fields_row">
                            <div class="col-md-3">
                                <label class="form-pro-label mb-1 d-block text-truncate"><i class="fa fa-droplet text-primary"></i> نوع الوقود / الصنف</label>
                                @php
                                    $defFuelId = $defaultFuelItem->id ?? '';
                                    $defFuelPrice = $defaultFuelItem ? ($defaultFuelItem->selling_price ?? $defaultFuelItem->original_price ?? 0) : 0;
                                    $selectedItemId = old('item_id', $defFuelId);
                                    $selectedFuelPrice = old('fuel_price', $defFuelPrice > 0 ? $defFuelPrice : '');
                                @endphp
                                <select name="item_id" id="item_id" class="form-pro-control input-calc" style="height: 42px;" required onchange="onFuelItemChange()">
                                    @foreach($items as $item)
                                        @php
                                            $itemPrice = $item->selling_price ?? $item->original_price ?? 0;
                                            $isSelected = (string)$selectedItemId === (string)$item->id;
                                        @endphp
                                        <option value="{{ $item->id }}" data-price="{{ $itemPrice }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $item->item_name ?? 'وقود بدون اسم' }}
                                        </option>
                                    @endforeach
                                    <option value="0" data-price="0" {{ (string)$selectedItemId === '0' ? 'selected' : '' }}>عهدة نقدية فقط (بدون وقود)</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="quantity_box">
                                <label class="form-pro-label mb-1 d-block text-truncate"><i class="fa fa-flask text-warning"></i> كمية الوقود (لتر)</label>
                                <input type="number" step="any" min="0" name="quantity" id="quantity"
                                       class="form-pro-control input-calc fw-bold" placeholder="مثال: 50"
                                       style="height: 42px;"
                                       value="{{ old('quantity') }}" oninput="calculateGasStation(); calculateDrivvoTrip();">
                            </div>
                            <div class="col-md-3" id="price_box">
                                <label class="form-pro-label mb-1 d-block text-truncate"><i class="fa fa-tag text-info"></i> سعر اللتر (ج)</label>
                                <input type="number" step="0.01" min="0" name="fuel_price" id="fuel_price"
                                       class="form-pro-control input-calc fw-bold" placeholder="السعر"
                                       style="height: 42px;"
                                       value="{{ $selectedFuelPrice }}" oninput="calculateGasStation(); calculateDrivvoTrip();">
                            </div>
                            <div class="col-md-3" id="fuel_subtotal_box">
                                <label class="form-pro-label mb-1 d-block text-truncate text-muted"><i class="fa fa-receipt text-success"></i> إجمالي تكلفة الوقود</label>
                                <div class="p-2 rounded-2 border bg-white text-center fw-bold text-dark d-flex align-items-center justify-content-center" style="height: 42px;">
                                    <span id="fuel_subtotal_val" class="fs-6 text-success fw-bold">0</span>
                                    <span class="small ms-1 text-muted">ج</span>
                                </div>
                            </div>
                        </div>

                        {{-- السطر 2: قراءة العداد عند آخر تفويلة كاملة + العداد الحالي + نوع التفويلة --}}
                        <div class="row g-2 mb-3" id="odometer_tank_row">
                            <div class="col-md-4">
                                <label class="form-pro-label mb-1 d-block text-truncate">
                                    <i class="fa fa-history text-secondary"></i> العداد عند آخر تفويلة (كم)
                                </label>
                                <input type="number" step="any" min="0" name="prev_odometer" id="inp_prev_odometer"
                                       class="form-pro-control input-calc" placeholder="مثال: 140000"
                                       style="height: 42px; background-color: #f1f5f9; cursor: not-allowed;"
                                       value="{{ old('prev_odometer') }}" autocomplete="off" readonly tabindex="-1">
                                <small class="text-muted d-block mt-1 text-truncate" style="font-size:0.7rem;">
                                    <i class="fa fa-lock text-secondary me-1"></i> يُجلب تلقائياً ولا يقبل التعديل
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-pro-label mb-1 d-block text-truncate">
                                    <i class="fa fa-gauge-high text-primary"></i> قراءة العداد الحالية (كم)
                                </label>
                                <input type="number" step="any" min="0" name="odometer" id="inp_odometer"
                                       class="form-pro-control input-calc fw-bold" placeholder="مثال: 140500"
                                       style="height: 42px;"
                                       value="{{ old('odometer') }}" autocomplete="off" oninput="calculateDrivvoTrip()">
                                <small class="text-muted d-block mt-1 text-truncate" style="font-size:0.7rem;">
                                    <i class="fa fa-pen text-info me-1"></i> قراءة العداد لحظة التفويل
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-pro-label mb-1 d-block text-truncate"><i class="fa fa-gas-pump text-success"></i> نوع التفويلة</label>
                                <div class="px-3 rounded-2 border bg-white d-flex align-items-center justify-content-between" style="height: 42px;">
                                    <div>
                                        <span class="fw-bold small d-block" id="tank_status_badge">
                                            <i class="fa fa-circle-check text-success me-1"></i> تفويلة كاملة
                                        </span>
                                    </div>
                                    <div class="form-check form-switch m-0 fs-5">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_full_tank" value="1" id="switch_full_tank" checked onchange="toggleTankSwitch(this)">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1 text-truncate" id="tank_status_desc" style="font-size:0.7rem;">
                                    <i class="fa fa-calculator text-success me-1"></i> حساب كفاءة (كم/لتر) دقيقة
                                </small>
                            </div>
                        </div>

                        {{-- مؤشرات الرحلة الحية المباشرة (تُسجل على الرحلة السابقة) --}}
                        <div class="p-2 rounded-2" id="live_trip_metrics_box" style="background: rgba(255,255,255,0.85); border: 1.5px dashed rgba(59,130,246,0.35);">
                            {{-- بانر توضيح ترحيل الاستهلاك للرحلة السابقة (آخر محطة، آخر سائق، آخر وجهة) --}}
                            <div id="prev_trip_attribution_banner" class="alert alert-info py-2 px-3 mb-2 rounded-2 small border-0" style="display:none; background: linear-gradient(90deg, rgba(14,165,233,0.12), rgba(59,130,246,0.06)); color:#0f172a;">
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="drivvo-trip-metric">
                                        <span class="metric-title"><i class="fa fa-road text-primary me-1"></i> المسافة (للرحلة السابقة)</span>
                                        <span class="metric-value text-primary" id="form_live_dist">— كم</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="drivvo-trip-metric" id="box_metric_rate">
                                        <span class="metric-title"><i class="fa fa-gauge text-success me-1"></i> كفاءة الرحلة السابقة</span>
                                        <span class="metric-value text-success" id="form_live_rate">— كم / لتر</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="drivvo-trip-metric">
                                        <span class="metric-title"><i class="fa fa-coins text-dark me-1"></i> تكلفة الكيلو (السابقة)</span>
                                        <span class="metric-value text-dark" id="form_live_cost">— ج/كم</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-center text-muted" id="form_live_status" style="font-size:0.75rem;">
                                <i class="fa fa-info-circle text-primary me-1"></i> أدخل قراءة العداد واللترات لإقفال الرحلة السابقة وحساب كفاءتها تلقائياً.
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"><i class="fa fa-money-bill-wave"></i> تفاصيل العهدة النقدية</div>

                    <div class="row g-2 mb-3 align-items-end" id="custody_row">
                        <div class="col-md-4" id="has_advance_box">
                            <label class="form-pro-label mb-1 d-block text-truncate">
                                <i class="fa fa-circle-question text-primary"></i> هل توجد عهدة نقدية؟
                            </label>
                            <select id="has_advance" class="form-pro-control input-calc" style="height: 42px;">
                                <option value="no"  {{ old('advance_source', 'none') === 'none' || old('advance_source') === 'no' ? 'selected' : '' }}>لا — عملية وقود فقط بدون عهدة</option>
                                <option value="yes" {{ in_array(old('advance_source'), ['station','safe']) ? 'selected' : '' }}>نعم — يوجد عهدة نقدية للسائق</option>
                            </select>
                        </div>

                        <div class="col-md-4" id="advance_amount_col" style="display: none;">
                            <label class="form-pro-label mb-1 d-block text-truncate text-danger fw-bold">
                                <i class="fa fa-money-bill-wave me-1"></i> مبلغ العهدة المطلوب (ج)
                                <span class="badge bg-danger text-white ms-1" style="font-size:0.68rem;"><i class="fa fa-asterisk"></i> إجباري</span>
                            </label>
                            <input type="number" step="0.01" name="cash_advance" min="0.01" id="cash_advance"
                                   class="form-pro-control input-calc fw-bold border-danger" placeholder="أدخل مبلغ العهدة..."
                                   style="height: 42px;"
                                   value="{{ old('cash_advance') }}">
                        </div>

                        <div class="col-md-4" id="advance_source_col" style="display: none;">
                            <label class="form-pro-label mb-1 d-block text-truncate text-primary fw-bold">
                                <i class="fa fa-building-columns me-1"></i> جهة صرف العهدة النقدية
                            </label>
                            <select id="mixed_source" class="form-pro-control input-calc" style="height: 42px;">
                                <option value="station" {{ old('advance_source', 'station') == 'station' ? 'selected' : '' }}>🏪 من المحطة (تضاف لفاتورة البنزينة)</option>
                                <optgroup label="🏦 سحب من خزائن الشركة (العمولة على الإجمالي)">
                                    @foreach($payment_methods as $acc)
                                        <option value="{{ $acc->id }}" {{ old('advance_source') == 'safe' && old('account_id') == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="advance_source" id="hidden_advance_source" value="{{ old('advance_source', 'none') }}">
                    <input type="hidden" name="account_id" id="hidden_account_id" value="{{ old('account_id') }}">

                    {{-- ملاحظات الرحلة / خط السير (إجباري) --}}
                    <div class="mb-3 p-3 rounded-3" style="background: rgba(248,250,252,0.95); border: 1.5px solid var(--c-border);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-pro-label m-0 fw-bold">
                                <i class="fa fa-route text-primary me-1"></i> خط سير الرحلة / المكان والوجهة
                                <span class="badge bg-danger text-white ms-1" style="font-size:0.72rem;"><i class="fa fa-asterisk"></i> إجباري</span>
                            </label>
                            <span class="text-muted small" style="font-size:0.7rem;">(اضغط للاختيار السريع أو ربط محطات متعددة)</span>
                        </div>

                        <div class="position-relative">
                            <input type="text" name="notes" id="trip_route_input" list="common_destinations_list"
                                   class="form-pro-control" style="height: 42px; padding-left: 38px;"
                                   placeholder="اكتب أو اختر الوجهة (مثال: ميناء الإسكندرية ⬅️ العاشر من رمضان)..."
                                   value="{{ old('notes') }}" required autocomplete="off">
                            <button type="button" class="btn btn-sm text-muted position-absolute"
                                    style="left: 6px; top: 50%; transform: translateY(-50%); border: none; background: transparent; padding: 2px 6px;"
                                    onclick="clearTripRoute()" title="مسح خط السير">
                                <i class="fa fa-times-circle fs-5 text-secondary"></i>
                            </button>
                        </div>

                        {{-- قائمة اقتراحات الإكمال التلقائي الذكية من الإعدادات --}}
                        <datalist id="common_destinations_list">
                            @foreach($destinations ?? [] as $dest)
                                <option value="{{ $dest }}"></option>
                            @endforeach
                        </datalist>

                        {{-- أزرار الوجهات السريعة المعتمدة من شاشة الإعدادات --}}
                        <div class="quick-dest-section mt-2 pt-2 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                                <span class="text-dark small fw-bold">
                                    <i class="fa fa-bolt text-warning me-1"></i> وجهات سريعة:
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('settings.index') }}" class="text-primary small text-decoration-none" style="font-size:0.72rem;">
                                        <i class="fa fa-cog me-1"></i> تعديل الأماكن من الإعدادات
                                    </a>
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none" onclick="clearTripRoute()" style="font-size:0.72rem;">
                                        <i class="fa fa-eraser me-1"></i> مسح الخانة
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-1 flex-wrap" id="dest_chips_container" style="max-height: 120px; overflow-y: auto; padding: 2px;">
                                @forelse($destinations ?? [] as $dest)
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill dest-chip" onclick="appendRouteDestination(@js($dest))">{{ $dest }}</button>
                                @empty
                                    <span class="text-muted small py-1" style="font-size:0.75rem;">
                                        ⚠️ لا توجد أماكن مسجلة حتى الآن. يمكنك إضافة الأماكن والوجهات من 
                                        <a href="{{ route('settings.index') }}" class="fw-bold text-decoration-underline">شاشة الإعدادات</a> لتظهر هنا كأزرار سريعة وخيارات إكمال تلقائي.
                                    </span>
                                @endforelse
                            </div>
                        </div>
                    </div>

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
                    <input type="number" step="0.01" id="manual_discount" class="manual-discount-input input-calc"
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

                {{-- كارت مؤشرات الرحلة المباشرة --}}
                <div id="drivvo_calc_card" class="mt-3 p-3 rounded-3" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,200,80,0.3); display:block;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-warning"><i class="fa fa-gauge-high me-1"></i> مؤشرات الرحلة والكفاءة:</span>
                        <span class="badge bg-success" style="font-size:0.68rem;">حساب تلقائي</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1" style="color:rgba(255,255,255,0.85);">
                        <span>المسافة المقطوعة:</span>
                        <strong class="text-white" id="live_dist_val">— كم</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1" style="color:rgba(255,255,255,0.85);">
                        <span>معدل الاستهلاك:</span>
                        <strong class="text-warning" id="live_rate_val">— لتر / 100 كم</strong>
                    </div>
                    <div class="d-flex justify-content-between small" style="color:rgba(255,255,255,0.85);">
                        <span>تكلفة الكيلومتر الواحد:</span>
                        <strong class="text-info" id="live_cost_val">— ج/كم</strong>
                    </div>
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
    </div> <!-- /tab-entry-pane -->

    {{-- ═══════════ TAB 2: الإحصائيات والتقارير العامة ═══════════ --}}
    <div class="tab-pane fade {{ $activeTab === 'analytics' ? 'show active' : '' }}" id="tab-analytics-pane" role="tabpanel" aria-labelledby="tab-analytics-btn">

        {{-- 🚨 رادار كشف الشبهات والهدر (شريط مدمج ينقل للرادار عبر النافذة المنبثقة) --}}
        <div class="mb-4">
            @if(count($suspiciousAlerts ?? []) > 0)
                <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 border-danger shadow-sm mb-0 flex-wrap gap-3" style="border-radius: 12px; background: linear-gradient(135deg, rgba(239,68,68,0.08) 0%, rgba(245,158,11,0.05) 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; font-size: 1.25rem;">
                            <i class="fa fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-danger mb-1">
                                رادار الشبهات والهدر: تم رصد ({{ count($suspiciousAlerts) }}) عمليات استهلاك أو تكلفة شاذة للشاحنات 🚨
                            </h6>
                            <small class="text-muted">يقوم النظام تلقائياً برصد أي تفويلات تتجاوز معدلات الأمان (استهلاك أقل من 4 كم/لتر أو تكلفة أعلى من 7 ج/كم). انقر على الزر لعرض الرادار والتفاصيل.</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-danger fw-bold shadow-sm px-3 py-2 d-inline-flex align-items-center gap-2" onclick="openRadarModal()">
                            <i class="fa fa-radiation"></i>
                            <span>فتح رادار كشف الشبهات ({{ count($suspiciousAlerts) }})</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-success d-flex align-items-center justify-content-between p-3 border-success-subtle shadow-sm mb-0 flex-wrap gap-2" style="border-radius: 12px; background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(59,130,246,0.05) 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.3rem;">
                            <i class="fa fa-shield-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-success mb-1">الشاحنات تعمل بكفاءة وأمان كامل — لا توجد أي علامات تعجب أو شبهات هدر حالياً</h6>
                            <small class="text-muted">يقوم النظام آلياً بفحص ومراقبة استهلاك كل سائق وشاحنة والتنبيه فوراً لأي استهلاك غير مبرر (أقل من 4 كم/لتر) أو تكلفة كيلومتر مفرطة (أعلى من 7 ج/كم).</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-success fw-bold px-3 py-2" onclick="openRadarModal()">
                        <i class="fa fa-shield me-1"></i> فحص الأمان
                    </button>
                </div>
            @endif
        </div>
        
        {{-- كروت إحصائيات KPI --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="drivvo-kpi-card c-amber">
                    <div class="kpi-label"><i class="fa fa-gas-pump me-1"></i> إجمالي الوقود</div>
                    <div class="kpi-val">{{ number_format($kpi->total_liters ?? 0, 1) }} <span style="font-size:0.8rem; font-weight:600;">لتر</span></div>
                    <i class="fa fa-gas-pump kpi-icon text-warning"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="drivvo-kpi-card c-blue">
                    <div class="kpi-label"><i class="fa fa-money-bill-wave me-1"></i> تكلفة الوقود</div>
                    <div class="kpi-val">{{ number_format($kpi->total_cost ?? 0) }} <span style="font-size:0.8rem; font-weight:600;">ج</span></div>
                    <i class="fa fa-wallet kpi-icon text-primary"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="drivvo-kpi-card c-red">
                    <div class="kpi-label"><i class="fa fa-hand-holding-dollar me-1"></i> العُهد النقدية</div>
                    <div class="kpi-val">{{ number_format($kpi->total_advance ?? 0) }} <span style="font-size:0.8rem; font-weight:600;">ج</span></div>
                    <i class="fa fa-hand-holding-dollar kpi-icon text-danger"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="drivvo-kpi-card c-green">
                    <div class="kpi-label"><i class="fa fa-chart-line me-1"></i> صافي الأرباح</div>
                    <div class="kpi-val text-success">{{ number_format($kpi->total_profit ?? 0) }} <span style="font-size:0.8rem; font-weight:600;">ج</span></div>
                    <i class="fa fa-arrow-trend-up kpi-icon text-success"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="drivvo-kpi-card c-purple">
                    <div class="kpi-label"><i class="fa fa-road me-1"></i> المسافة الإجمالية</div>
                    <div class="kpi-val">{{ number_format($kpi->total_distance ?? 0) }} <span style="font-size:0.8rem; font-weight:600;">كم</span></div>
                    <i class="fa fa-tachometer-alt kpi-icon text-purple"></i>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="drivvo-kpi-card c-cyan">
                    <div class="kpi-label"><i class="fa fa-gauge-high me-1"></i> متوسط كفاءة الاستهلاك</div>
                    <div class="kpi-val">{{ number_format($kpi->avg_consumption ?? 0, 1) }} <span style="font-size:0.75rem; font-weight:600;">كم/لتر</span></div>
                    <i class="fa fa-droplet kpi-icon text-info"></i>
                </div>
            </div>
        </div>

        {{-- شريط الفترات السريعة والفلاتر وتصدير PDF --}}
        <div class="panel-pro mb-4 p-3" style="background:var(--c-surface);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 mb-3 border-bottom">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-dark small"><i class="fa fa-calendar-check text-primary me-1"></i> الفترة المعروضة:</span>
                    <span class="badge bg-primary text-white px-3 py-2 fw-bold" style="font-size:0.82rem;">
                        {{ $periodLabel ?? 'آخر شهر (30 يوماً)' }}
                        @if(!empty($resolvedFrom) || !empty($resolvedTo))
                            <span class="fw-normal opacity-75 ms-1" dir="ltr">({{ $resolvedFrom ?? '...' }} ← {{ $resolvedTo ?? '...' }})</span>
                        @endif
                    </span>
                    <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="اختيار سريع للفترة">
                        @php $curPer = $filterPeriod ?? 'month'; @endphp
                        <button type="button" class="btn {{ $curPer === 'week' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}" onclick="setAnalyticsPeriod('week')">آخر أسبوع</button>
                        <button type="button" class="btn {{ $curPer === 'month' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}" onclick="setAnalyticsPeriod('month')">آخر شهر (الافتراضي)</button>
                        <button type="button" class="btn {{ $curPer === 'this_month' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}" onclick="setAnalyticsPeriod('this_month')">هذا الشهر</button>
                        <button type="button" class="btn {{ $curPer === '3months' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}" onclick="setAnalyticsPeriod('3months')">آخر 3 شهور</button>
                        <button type="button" class="btn {{ $curPer === 'all' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}" onclick="setAnalyticsPeriod('all')">كل الوقت</button>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-1" style="background:linear-gradient(135deg,#1d4ed8,#0f172a); border:none;" onclick="openGasPdfExport('company_report')">
                        <i class="fa fa-building text-warning"></i>
                        <span>تقرير شركة النقل PDF (بالشارتات)</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-success fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-1" onclick="openGasPdfExport('analytics')">
                        <i class="fa fa-file-pdf"></i>
                        <span>تصدير الإحصائيات PDF</span>
                    </button>
                    <button type="button" class="btn btn-sm fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-1 text-white" style="background:#ea580c; border:none;" onclick="openGasPdfExport('comparisons')">
                        <i class="fa fa-scale-balanced"></i>
                        <span>تصدير المقارنات PDF</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-dark fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-1" onclick="openGasPdfExport('logs')">
                        <i class="fa fa-table-list text-info"></i>
                        <span>تصدير السجل PDF</span>
                    </button>
                </div>
            </div>

            <form method="GET" action="{{ url('/gas-station') }}" id="drivvoFilterForm" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="analytics">
                <input type="hidden" name="period" id="analytics_period_input" value="{{ $filterPeriod ?? 'month' }}">
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-pro-label small mb-1"><i class="fa fa-building"></i> شركة النقل</label>
                    <select name="filter_company" id="analytics_filter_company" class="form-select form-select-sm">
                        <option value="">-- كل الشركات --</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ request('filter_company') == $c->id ? 'selected' : '' }}>
                                {{ $c->company_name ?? $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-pro-label small mb-1"><i class="fa fa-gas-pump"></i> محطة الوقود</label>
                    <select name="filter_station" id="analytics_filter_station" class="form-select form-select-sm">
                        <option value="">-- كل المحطات --</option>
                        @foreach($stations as $s)
                            <option value="{{ $s->id }}" {{ request('filter_station') == $s->id ? 'selected' : '' }}>
                                {{ $s->station_name ?? $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-pro-label small mb-1"><i class="fa fa-truck text-primary"></i> الشاحنة / السيارة</label>
                    <input type="text" name="filter_car" id="analytics_filter_car" list="filter_cars_list" class="form-control form-control-sm" placeholder="اختر أو اكتب رقم اللوحة..." value="{{ request('filter_car') }}" autocomplete="off">
                    <datalist id="filter_cars_list">
                        @foreach($filterCarOptions ?? [] as $carOpt)
                            <option value="{{ $carOpt }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-pro-label small mb-1"><i class="fa fa-user-tie text-secondary"></i> اسم السائق</label>
                    <input type="text" name="filter_driver" id="analytics_filter_driver" list="filter_drivers_list" class="form-control form-control-sm" placeholder="اختر أو اكتب اسم السائق..." value="{{ request('filter_driver') }}" autocomplete="off">
                    <datalist id="filter_drivers_list">
                        @foreach($filterDriverOptions ?? [] as $drvOpt)
                            <option value="{{ $drvOpt }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-pro-label small mb-1"><i class="fa fa-calendar-alt"></i> من تاريخ</label>
                    <input type="date" name="filter_from" id="analytics_filter_from" class="form-control form-control-sm" value="{{ $resolvedFrom ?? request('filter_from') }}" onchange="onCustomAnalyticsDateChange()">
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-pro-label small mb-1"><i class="fa fa-calendar-alt"></i> إلى تاريخ</label>
                    <input type="date" name="filter_to" id="analytics_filter_to" class="form-control form-control-sm" value="{{ $resolvedTo ?? request('filter_to') }}" onchange="onCustomAnalyticsDateChange()">
                </div>
                <div class="col-12 d-flex gap-2 justify-content-between align-items-center mt-2 pt-2 border-top flex-wrap">
                    <div class="small text-muted">
                        <i class="fa fa-circle-info text-primary me-1"></i>
                        إجمالي العمليات بالفترة: <strong>{{ $kpi->total_ops ?? 0 }}</strong>
                        ({{ $kpi->fuel_ops ?? 0 }} تفويلة وقود · {{ $kpi->custody_ops ?? 0 }} عهدة بدون وقود)
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn-report-solid primary-btn">
                            <i class="fa fa-filter"></i> تصفية النتائج
                        </button>
                        <a href="{{ url('/gas-station?tab=analytics') }}" class="btn-report-solid">
                            <i class="fa fa-rotate-left text-secondary"></i> إعادة ضبط (آخر شهر)
                        </a>
                    </div>
                </div>
            </form>

            @if($hasActiveFilters ?? false)
                @php
                    $fCompany = request('filter_company') ? optional(collect($companies)->firstWhere('id', request('filter_company'))) : null;
                    $fStation = request('filter_station') ? optional(collect($stations)->firstWhere('id', request('filter_station'))) : null;
                @endphp
                <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 mb-0 py-2 px-3" style="border-radius:10px;">
                    <div class="d-flex align-items-center gap-2 flex-wrap fw-bold" style="font-size:0.9rem;">
                        <span><i class="fa fa-filter me-1"></i> الفلاتر النشطة:</span>
                        <span class="badge bg-primary">📅 {{ $periodLabel ?? 'آخر شهر' }}</span>
                        @if($fCompany)<span class="badge bg-dark">🏢 {{ $fCompany->company_name ?? $fCompany->name }}</span>@endif
                        @if($fStation)<span class="badge bg-dark">⛽ {{ $fStation->station_name ?? $fStation->name }}</span>@endif
                        @if(request('filter_car'))<span class="badge bg-primary">🚗 {{ request('filter_car') }}</span>@endif
                        @if(request('filter_driver'))<span class="badge bg-secondary">👤 {{ request('filter_driver') }}</span>@endif
                        <span class="badge bg-light text-dark border">{{ $kpi->total_ops ?? 0 }} عملية</span>
                    </div>
                    <a href="{{ url('/gas-station?tab=analytics') }}" class="btn btn-sm btn-danger fw-bold shadow-sm">
                        <i class="fa fa-xmark me-1"></i> إلغاء الفلاتر
                    </a>
                </div>
            @endif
        </div>

        {{-- لوحتان للمقارنة: أعلى المركبات + توزيع الوقود --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="panel-pro h-100">
                    <div class="panel-pro-head d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold"><i class="fa fa-truck-moving text-warning me-2"></i> أعلى المركبات استهلاكاً للوقود</h6>
                        <span class="badge bg-light text-muted border small">حسب إجمالي اللترات</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size:0.84rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>السائق / المركبة</th>
                                    <th class="text-center">عدد التفويلات</th>
                                    <th class="text-center">إجمالي اللترات</th>
                                    <th class="text-end">إجمالي المصروف (ج)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topVehicles as $idx => $tv)
                                    <tr>
                                        <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                        <td class="fw-bold text-dark">{{ $tv->driver_name }}</td>
                                        <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-dark">{{ $tv->fills_count }}</span></td>
                                        <td class="text-center fw-bold text-warning">{{ number_format($tv->liters, 1) }} لتر</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($tv->total_spent) }} ج</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">لا توجد بيانات مسجلة</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel-pro h-100">
                    <div class="panel-pro-head d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold"><i class="fa fa-chart-pie text-info me-2"></i> توزيع الاستهلاك حسب نوع الوقود</h6>
                        <span class="badge bg-light text-muted border small">{{ count($fuelDistribution) }} أصناف</span>
                    </div>
                    <div class="p-3">
                        @php $totalDistLiters = $fuelDistribution->sum('total_liters') ?: 1; @endphp
                        @forelse($fuelDistribution as $fd)
                            @php $pct = round(($fd->total_liters / $totalDistLiters) * 100, 1); @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                    <span>{{ $fd->fuel_type ?: 'وقود عام' }}</span>
                                    <span>{{ number_format($fd->total_liters, 1) }} لتر ({{ $pct }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 999px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small">لا توجد بيانات وقود</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 📊 تقرير كفاءة واستهلاك الشاحنات والسائقين التفصيلي --}}
        <div class="panel-pro mb-4">
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="m-0 fw-bold"><i class="fa fa-id-card-clip text-primary me-2"></i> تقرير كفاءة واستهلاك الشاحنات والسائقين التفصيلي</h6>
                    <small class="text-muted">تحليل شامل لكل سائق وشاحنة: تصفية منفصلة بالعربيات أو السواقين، المسافات، اللترات، ومتوسط الكفاءة</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="text" id="fleetDriverSearch" class="form-control form-control-sm" placeholder="🔍 ابحث عن سائق أو شاحنة..." style="min-width: 200px;" oninput="filterFleetTable(this.value)">
                    <select id="quickDriverReportSelect" class="form-select form-select-sm fw-bold border-primary" style="min-width: 220px;" onchange="if(this.value){ const opt=this.options[this.selectedIndex]; openDriverReportModal(this.value, opt.getAttribute('data-type') || 'auto'); }">
                        <option value="">-- اختر لفتح التقرير والشارت 📊 --</option>
                        <optgroup label="🚗 السيارات والشاحنات ({{ count($detailedCarsReport ?? []) }})">
                            @foreach($detailedCarsReport ?? [] as $cItem)
                                <option value="{{ $cItem->car_number }}" data-type="car">🚗 {{ $cItem->car_number }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="👤 السائقين ({{ count($detailedDriversReport ?? []) }})">
                            @foreach($detailedDriversReport ?? [] as $dItem)
                                <option value="{{ $dItem->driver_name }}" data-type="driver">👤 {{ $dItem->driver_name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    <span class="badge bg-light text-dark border px-3 py-2 fw-bold" id="fleetDriversCountBadge">إجمالي: {{ count($detailedCarsReport ?? []) }} سيارة</span>
                </div>
            </div>

            {{-- شريط أزرار التصفية والتنقل بين (العربيات / السواقين / الكل) بأزرار بارزة وواضحة --}}
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-light flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold small text-dark"><i class="fa fa-filter text-primary me-1"></i> عرض التقرير حسب:</span>
                    <div class="d-flex flex-wrap gap-2" role="group" id="fleetViewToggleGroup">
                        <button type="button" class="btn-report-solid active-blue" id="btnFleetCars" onclick="switchFleetView('cars')">
                            <i class="fa fa-truck"></i> السيارات والشاحنات ({{ count($detailedCarsReport ?? []) }})
                        </button>
                        <button type="button" class="btn-report-solid" id="btnFleetDrivers" onclick="switchFleetView('drivers')">
                            <i class="fa fa-user-tie"></i> السائقين ({{ count($detailedDriversReport ?? []) }})
                        </button>
                        <button type="button" class="btn-report-solid" id="btnFleetAll" onclick="switchFleetView('all')">
                            <i class="fa fa-layer-group"></i> الكل المدمج ({{ count($detailedFleetReport ?? []) }})
                        </button>
                    </div>
                </div>
                <div class="small text-muted" id="fleetActiveViewNote">
                    <i class="fa fa-circle-info text-info me-1"></i> معروض الآن: قائمة السيارات والشاحنات مع السائقين الذين قادوها
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle" id="fleetEfficiencyTable" style="font-size:0.84rem;">
                    <thead class="table-light">
                        <tr id="fleetTableHeaders">
                            <th>#</th>
                            <th id="thEntityCol">السيارة / الشاحنة</th>
                            <th id="thRelatedCol">السائقين المرتبطين</th>
                            <th class="text-center">إجمالي الرحلات</th>
                            <th class="text-center">التفويل / العُهد</th>
                            <th class="text-center">إجمالي المسافة (كم)</th>
                            <th class="text-center">إجمالي الوقود (لتر)</th>
                            <th class="text-end">إجمالي المصروف (ج)</th>
                            <th class="text-center">متوسط الكفاءة (كم/لتر)</th>
                            <th class="text-center">تكلفة الكيلومتر (ج/كم)</th>
                            <th class="text-center">تقييم الكفاءة والأمان</th>
                            <th class="text-center">التقرير والشارت</th>
                        </tr>
                    </thead>

                    {{-- 🚗 1) جدول السيارات والشاحنات --}}
                    <tbody id="fleetCarsTbody">
                        @forelse($detailedCarsReport ?? [] as $idx => $car)
                            @php
                                $cCarNumber   = $car->car_number ?? '—';
                                $cDriversList = $car->drivers_list ?? '—';
                                $cTrips       = $car->total_trips ?? 0;
                                $cFull        = $car->full_fills ?? 0;
                                $cPart        = $car->partial_fills ?? 0;
                                $cCustody     = $car->custody_fills ?? 0;
                                $cDist        = $car->total_dist ?? 0;
                                $cLiters      = $car->total_liters ?? 0;
                                $cCost        = $car->total_cost ?? 0;
                                $cEff         = $car->avg_efficiency ?? 0;
                                $cCostKm      = $car->avg_cost_km ?? 0;
                                $cSuspicious  = ($cEff > 0 && $cEff < ($effectiveMinEff ?? 4.0)) || ($cCostKm > ($effectiveMaxCostKm ?? 7.0));
                            @endphp
                            <tr class="fleet-row {{ $cSuspicious ? 'table-danger bg-opacity-25' : '' }}" data-search="{{ mb_strtolower($cCarNumber . ' ' . $cDriversList) }}">
                                <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <button type="button" onclick="openDriverReportModal('{{ addslashes($cCarNumber) }}', 'car')" class="btn-entity-chip" title="انقر لفتح شارت وتقرير السيارة">
                                            <i class="fa fa-truck text-primary"></i>
                                            <span>{{ $cCarNumber }}</span>
                                            <i class="fa fa-up-right-from-square text-muted" style="font-size:0.65rem;"></i>
                                        </button>
                                        @if($cSuspicious)
                                            <button type="button" class="btn btn-xs btn-danger text-white fw-bold py-0 px-2 rounded-2 shadow-xs" onclick="openDriverReportModal('{{ addslashes($cCarNumber) }}', 'car')" title="شبهة هدر استهلاك وقود">
                                                <i class="fa fa-triangle-exclamation"></i> هدر 🚨
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark small"><i class="fa fa-user-tie text-secondary me-1"></i>{{ $cDriversList }}</span>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-dark fw-bold">{{ $cTrips }}</span></td>
                                <td class="text-center">
                                    @if($cFull > 0)
                                        <span class="badge bg-success-subtle text-success small">{{ $cFull }} كامل</span>
                                    @endif
                                    @if($cPart > 0)
                                        <span class="badge bg-warning-subtle text-dark small">{{ $cPart }} جزئي</span>
                                    @endif
                                    @if($cCustody > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">{{ $cCustody }} عهدة</span>
                                    @endif
                                    @if($cFull == 0 && $cPart == 0 && $cCustody == 0)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold" dir="ltr">{{ $cDist > 0 ? number_format($cDist) . ' كم' : '—' }}</td>
                                <td class="text-center fw-bold {{ $cLiters > 0 ? 'text-warning' : 'text-muted' }}">{{ $cLiters > 0 ? number_format($cLiters, 1) . ' لتر' : 'بدون وقود' }}</td>
                                <td class="text-end fw-bold text-dark">{{ number_format($cCost) }} ج</td>
                                <td class="text-center">
                                    @if($cEff > 0)
                                        <span class="rate-badge {{ $cEff < ($effectiveMinEff ?? 4.0) ? 'danger' : 'good' }}">{{ number_format($cEff, 2) }} كم/لتر</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($cCostKm > 0)
                                        <span class="fw-bold {{ $cCostKm > ($effectiveMaxCostKm ?? 7.0) ? 'text-danger' : 'text-dark' }}">{{ number_format($cCostKm, 2) }} ج/كم</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($cSuspicious)
                                        <button type="button" class="btn btn-xs btn-danger text-white fw-bold py-1 px-2 rounded-2 shadow-xs" onclick="openDriverReportModal('{{ addslashes($cCarNumber) }}', 'car')"><i class="fa fa-triangle-exclamation me-1"></i> يحتاج مراجعة 🚨</button>
                                    @elseif($cLiters <= 0 && $cCustody > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold"><i class="fa fa-hand-holding-dollar me-1"></i> حساب عهدة</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-20 text-success fw-bold"><i class="fa fa-check me-1"></i> كفاءة طبيعية</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-report-solid primary-btn py-1 px-2" style="font-size:0.76rem;" onclick="openDriverReportModal('{{ addslashes($cCarNumber) }}', 'car')" title="عرض شارت نقاط التفويل وتكلفة اللتر للسيارة">
                                        <i class="fa fa-chart-column"></i>
                                        <span>الشارت والتقرير</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center py-4 text-muted">لا توجد سيارات مسجلة</td></tr>
                        @endforelse
                    </tbody>

                    {{-- 👤 2) جدول السائقين --}}
                    <tbody id="fleetDriversTbody" style="display:none;">
                        @forelse($detailedDriversReport ?? [] as $idx => $driver)
                            @php
                                $dDriverName  = $driver->driver_name ?? '—';
                                $dCarsList    = $driver->cars_list ?? '—';
                                $dTrips       = $driver->total_trips ?? 0;
                                $dFull        = $driver->full_fills ?? 0;
                                $dPart        = $driver->partial_fills ?? 0;
                                $dCustody     = $driver->custody_fills ?? 0;
                                $dDist        = $driver->total_dist ?? 0;
                                $dLiters      = $driver->total_liters ?? 0;
                                $dCost        = $driver->total_cost ?? 0;
                                $dEff         = $driver->avg_efficiency ?? 0;
                                $dCostKm      = $driver->avg_cost_km ?? 0;
                                $dSuspicious  = ($dEff > 0 && $dEff < ($effectiveMinEff ?? 4.0)) || ($dCostKm > ($effectiveMaxCostKm ?? 7.0));
                            @endphp
                            <tr class="fleet-row {{ $dSuspicious ? 'table-danger bg-opacity-25' : '' }}" data-search="{{ mb_strtolower($dDriverName . ' ' . $dCarsList) }}">
                                <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <button type="button" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'driver')" class="btn-entity-chip" title="انقر لفتح شارت وتقرير السائق">
                                            <i class="fa fa-user-tie text-primary"></i>
                                            <span>{{ $dDriverName }}</span>
                                            <i class="fa fa-up-right-from-square text-muted" style="font-size:0.65rem;"></i>
                                        </button>
                                        @if($dSuspicious)
                                            <button type="button" class="btn btn-xs btn-danger text-white fw-bold py-0 px-2 rounded-2 shadow-xs" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'driver')" title="شبهة هدر استهلاك وقود">
                                                <i class="fa fa-triangle-exclamation"></i> هدر 🚨
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark small"><i class="fa fa-truck text-primary me-1"></i>{{ $dCarsList ?: 'غير محدد' }}</span>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-dark fw-bold">{{ $dTrips }}</span></td>
                                <td class="text-center">
                                    @if($dFull > 0)
                                        <span class="badge bg-success-subtle text-success small">{{ $dFull }} كامل</span>
                                    @endif
                                    @if($dPart > 0)
                                        <span class="badge bg-warning-subtle text-dark small">{{ $dPart }} جزئي</span>
                                    @endif
                                    @if($dCustody > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">{{ $dCustody }} عهدة</span>
                                    @endif
                                    @if($dFull == 0 && $dPart == 0 && $dCustody == 0)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold" dir="ltr">{{ $dDist > 0 ? number_format($dDist) . ' كم' : '—' }}</td>
                                <td class="text-center fw-bold {{ $dLiters > 0 ? 'text-warning' : 'text-muted' }}">{{ $dLiters > 0 ? number_format($dLiters, 1) . ' لتر' : 'بدون وقود' }}</td>
                                <td class="text-end fw-bold text-dark">{{ number_format($dCost) }} ج</td>
                                <td class="text-center">
                                    @if($dEff > 0)
                                        <span class="rate-badge {{ $dEff < ($effectiveMinEff ?? 4.0) ? 'danger' : 'good' }}">{{ number_format($dEff, 2) }} كم/لتر</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($dCostKm > 0)
                                        <span class="fw-bold {{ $dCostKm > ($effectiveMaxCostKm ?? 7.0) ? 'text-danger' : 'text-dark' }}">{{ number_format($dCostKm, 2) }} ج/كم</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($dSuspicious)
                                        <button type="button" class="btn btn-xs btn-danger text-white fw-bold py-1 px-2 rounded-2 shadow-xs" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'driver')"><i class="fa fa-triangle-exclamation me-1"></i> يحتاج مراجعة 🚨</button>
                                    @elseif($dLiters <= 0 && $dCustody > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold"><i class="fa fa-hand-holding-dollar me-1"></i> حساب عهدة</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-20 text-success fw-bold"><i class="fa fa-check me-1"></i> كفاءة طبيعية</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-report-solid primary-btn py-1 px-2" style="font-size:0.76rem;" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'driver')" title="عرض شارت نقاط التفويل وتكلفة اللتر للسائق">
                                        <i class="fa fa-chart-column"></i>
                                        <span>الشارت والتقرير</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center py-4 text-muted">لا توجد بيانات سائقين مسجلة</td></tr>
                        @endforelse
                    </tbody>

                    {{-- 🔄 3) جدول الكل المدمج --}}
                    <tbody id="fleetAllTbody" style="display:none;">
                        @forelse($detailedFleetReport ?? [] as $idx => $driver)
                            @php
                                $dDriverName  = is_array($driver) ? ($driver['driver_name'] ?? '—') : ($driver->driver_name ?? '—');
                                $dCarNumber   = is_array($driver) ? ($driver['car_number'] ?? null) : ($driver->car_number ?? null);
                                $dTotalTrips  = is_array($driver) ? ($driver['total_trips'] ?? 0) : ($driver->total_trips ?? 0);
                                $dFullFills   = is_array($driver) ? ($driver['full_fills'] ?? 0) : ($driver->full_fills ?? 0);
                                $dPartFills   = is_array($driver) ? ($driver['partial_fills'] ?? 0) : ($driver->partial_fills ?? 0);
                                $dCustFills   = is_array($driver) ? ($driver['custody_fills'] ?? 0) : ($driver->custody_fills ?? 0);
                                $dTotalDist   = is_array($driver) ? ($driver['total_dist'] ?? $driver['total_distance'] ?? 0) : ($driver->total_dist ?? $driver->total_distance ?? 0);
                                $dTotalLiters = is_array($driver) ? ($driver['total_liters'] ?? 0) : ($driver->total_liters ?? 0);
                                $dTotalCost   = is_array($driver) ? ($driver['total_cost'] ?? 0) : ($driver->total_cost ?? 0);
                                $avgEff       = is_array($driver) ? ($driver['avg_efficiency'] ?? 0) : ($driver->avg_efficiency ?? 0);
                                $costKm       = is_array($driver) ? ($driver['avg_cost_km'] ?? $driver['avg_cost_per_km'] ?? 0) : ($driver->avg_cost_km ?? $driver->avg_cost_per_km ?? 0);
                                $hasSuspicion = ($avgEff > 0 && $avgEff < ($effectiveMinEff ?? 4.0)) || ($costKm > ($effectiveMaxCostKm ?? 7.0));
                            @endphp
                            <tr class="fleet-row {{ $hasSuspicion ? 'table-danger bg-opacity-25' : '' }}" data-search="{{ mb_strtolower($dDriverName . ' ' . ($dCarNumber ?? '')) }}">
                                <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <button type="button" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'auto')" class="btn-entity-chip" title="انقر لفتح التقرير التفصيلي">
                                            @if($dCarNumber && $dCarNumber !== '-')
                                                <span class="badge bg-dark text-white px-2 py-1"><i class="fa fa-truck me-1"></i>{{ $dCarNumber }}</span>
                                            @else
                                                <i class="fa fa-user-tie text-primary"></i>
                                            @endif
                                            <span>{{ $dDriverName }}</span>
                                            <i class="fa fa-up-right-from-square text-muted" style="font-size:0.65rem;"></i>
                                        </button>
                                        @if($hasSuspicion)
                                            <button type="button" class="btn btn-xs btn-danger text-white fw-bold py-0 px-2 rounded-2 shadow-xs" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'auto')" title="تم رصد عمليات استهلاك شاذة أو تكلفة مرتفعة">
                                                <i class="fa fa-triangle-exclamation"></i> هدر 🚨
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">—</span>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-dark fw-bold">{{ $dTotalTrips }}</span></td>
                                <td class="text-center">
                                    @if($dFullFills > 0)
                                        <span class="badge bg-success-subtle text-success small">{{ $dFullFills }} كامل</span>
                                    @endif
                                    @if($dPartFills > 0)
                                        <span class="badge bg-warning-subtle text-dark small">{{ $dPartFills }} جزئي</span>
                                    @endif
                                    @if($dCustFills > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">{{ $dCustFills }} عهدة</span>
                                    @endif
                                    @if($dFullFills == 0 && $dPartFills == 0 && $dCustFills == 0)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold" dir="ltr">{{ $dTotalDist > 0 ? number_format($dTotalDist) . ' كم' : '—' }}</td>
                                <td class="text-center fw-bold {{ $dTotalLiters > 0 ? 'text-warning' : 'text-muted' }}">{{ $dTotalLiters > 0 ? number_format($dTotalLiters, 1) . ' لتر' : 'بدون وقود' }}</td>
                                <td class="text-end fw-bold text-dark">{{ number_format($dTotalCost) }} ج</td>
                                <td class="text-center">
                                    @if($avgEff > 0)
                                        <span class="rate-badge {{ $avgEff < ($effectiveMinEff ?? 4.0) ? 'danger' : 'good' }}">{{ number_format($avgEff, 2) }} كم/لتر</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($costKm > 0)
                                        <span class="fw-bold {{ $costKm > ($effectiveMaxCostKm ?? 7.0) ? 'text-danger' : 'text-dark' }}">{{ number_format($costKm, 2) }} ج/كم</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($hasSuspicion)
                                        <button type="button" class="btn btn-xs btn-danger text-white fw-bold py-1 px-2 rounded-2 shadow-xs" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'auto')"><i class="fa fa-triangle-exclamation me-1"></i> يحتاج مراجعة 🚨</button>
                                    @elseif($dTotalLiters <= 0 && $dCustFills > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold"><i class="fa fa-hand-holding-dollar me-1"></i> حساب عهدة</span>
                                    @else
                                        <span class="badge bg-light text-muted border"><i class="fa fa-shield me-1"></i> طبيعي</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-report-solid primary-btn py-1 px-2" style="font-size:0.76rem;" onclick="openDriverReportModal('{{ addslashes($dDriverName) }}', 'auto')" title="عرض التقرير التفصيلي الكامل">
                                        <i class="fa fa-chart-column"></i>
                                        <span>الشارت والتقرير</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center py-4 text-muted">لا توجد بيانات مسجلة</td></tr>
                        @endforelse
                    </tbody>

                    <tbody id="fleetNoResultsTbody" style="display:none;">
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                <i class="fa fa-magnifying-glass fa-2x mb-2 text-secondary d-block"></i>
                                لا توجد نتائج مطابقة لكلمة البحث
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- 📑 شريط التنقل بين الصفحات لتقرير الشاحنات (30 بالصفحة مع شريط تنقل) --}}
            <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap gap-3" id="fleetPaginationWrapper">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small" id="fleetPaginationInfo">
                        عرض <strong id="fleetPageStart" class="text-dark">1</strong> إلى <strong id="fleetPageEnd" class="text-dark">{{ min(30, count($detailedFleetReport ?? [])) }}</strong> من إجمالي <strong id="fleetTotalRows" class="text-primary">{{ count($detailedFleetReport ?? []) }}</strong> شاحنة
                    </span>
                    <div class="vr mx-2 d-none d-sm-inline-block" style="height: 18px;"></div>
                    <div class="d-flex align-items-center gap-1">
                        <label for="fleetPageSizeSelect" class="small text-muted mb-0">عرض بالصفحة:</label>
                        <select id="fleetPageSizeSelect" class="form-select form-select-sm" style="width: auto; padding: 0.2rem 1.8rem 0.2rem 0.6rem; font-size: 0.8rem;" onchange="changeFleetPageSize(this.value)">
                            <option value="30" selected>30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">الكل</option>
                        </select>
                    </div>
                </div>

                <nav aria-label="تنقل صفحات تقرير الأسطول">
                    <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center" id="fleetPaginationNav">
                        <!-- يتم إنشاؤها عبر JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>

        {{-- 🏢 تقرير مسحوبات واستهلاك شركات النقل --}}
        <div class="panel-pro mb-4">
            <div class="panel-pro-head d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold"><i class="fa fa-building-user text-info me-2"></i> تقرير مسحوبات واستهلاك شركات النقل</h6>
                <span class="badge bg-light text-muted border small">{{ count($companyBreakdown ?? []) }} شركات</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle" style="font-size:0.84rem;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>شركة النقل</th>
                            <th class="text-center">عدد العمليات</th>
                            <th class="text-center">إجمالي اللترات</th>
                            <th class="text-center">إجمالي المسافة (كم)</th>
                            <th class="text-center">إجمالي العهد النقدية (ج)</th>
                            <th class="text-end">إجمالي الحساب على الشركة (ج)</th>
                            <th class="text-end">صافي أرباح النظام (ج)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companyBreakdown ?? [] as $cIdx => $comp)
                            @php
                                $cName   = is_array($comp) ? ($comp['company_name'] ?? '—') : ($comp->company_name ?? '—');
                                $cOps    = is_array($comp) ? ($comp['total_ops'] ?? $comp['trips_count'] ?? 0) : ($comp->total_ops ?? $comp->trips_count ?? 0);
                                $cLiters = is_array($comp) ? ($comp['total_liters'] ?? $comp['liters'] ?? 0) : ($comp->total_liters ?? $comp->liters ?? 0);
                                $cDist   = is_array($comp) ? ($comp['total_distance'] ?? $comp['distance_km'] ?? 0) : ($comp->total_distance ?? $comp->distance_km ?? 0);
                                $cAdv    = is_array($comp) ? ($comp['total_advance'] ?? $comp['cash_advance'] ?? 0) : ($comp->total_advance ?? $comp->cash_advance ?? 0);
                                $cAmount = is_array($comp) ? ($comp['total_amount'] ?? $comp['total_on_company'] ?? 0) : ($comp->total_amount ?? $comp->total_on_company ?? 0);
                                $cProfit = is_array($comp) ? ($comp['total_profit'] ?? $comp['ahmed_profit'] ?? 0) : ($comp->total_profit ?? $comp->ahmed_profit ?? 0);
                            @endphp
                            <tr>
                                <td class="fw-bold text-muted">{{ $cIdx + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $cName }}</td>
                                <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $cOps }}</span></td>
                                <td class="text-center fw-bold text-warning">{{ number_format($cLiters, 1) }} لتر</td>
                                <td class="text-center fw-bold" dir="ltr">{{ $cDist > 0 ? number_format($cDist) . ' كم' : '—' }}</td>
                                <td class="text-center fw-bold {{ $cAdv > 0 ? 'text-danger' : 'text-muted' }}">{{ $cAdv > 0 ? number_format($cAdv) . ' ج' : '—' }}</td>
                                <td class="text-end fw-bold text-dark">{{ number_format($cAmount, 2) }} ج</td>
                                <td class="text-end fw-bold text-success">+{{ number_format($cProfit, 2) }} ج</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-3 text-muted">لا توجد بيانات لشركات النقل</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- جدول سجل عمليات الأسطول التفاعلي --}}
        <div class="panel-pro">
            <div class="panel-pro-head d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="m-0 fw-bold"><i class="fa fa-clock-rotate-left text-primary me-2"></i> سجل عمليات وقود الشاحنات التفصيلي</h6>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="text" id="drivvoTableSearch" class="form-control form-control-sm" placeholder="بحث سريع في السجل..." style="max-width:220px;" oninput="filterDrivvoLog(this.value)">
                    <button type="button" class="btn btn-sm btn-dark fw-bold px-2 py-1 d-inline-flex align-items-center gap-1" onclick="openGasPdfExport('logs')" title="تصدير وطباعة السجل كملف PDF منسق">
                        <i class="fa fa-file-pdf text-warning"></i>
                        <span>تصدير السجل PDF</span>
                    </button>
                    <span class="badge bg-primary px-3 py-2 fw-bold" id="drivvoFilteredCount">{{ count($transactions) }} حركة</span>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 540px; overflow-y: auto;">
                <table class="table-drivvo" id="drivvoLogTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ والوقت</th>
                            <th>شركة النقل</th>
                            <th>محطة الوقود</th>
                            <th>السائق / المركبة</th>
                            <th>العداد (كم)</th>
                            <th>المسافة</th>
                            <th>التفويلة</th>
                            <th>الوقود (لتر)</th>
                            <th>معدل الاستهلاك</th>
                            <th>العهدة</th>
                            <th>الحساب على الشركة</th>
                            <th>صافي الربح</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            @php
                                $isCustodyOnly = floatval($t->liters ?? 0) <= 0;
                                $isFull = !$isCustodyOnly && ($t->is_full_tank ?? true);
                                $isSuspicious = !$isCustodyOnly && isset($suspiciousMap[$t->id]);
                            @endphp
                            <tr class="drivvo-row {{ $isSuspicious ? 'table-danger bg-opacity-10' : '' }}" data-search="{{ strtolower(($t->driver_name ?? '') . ' ' . ($t->company_name ?? '') . ' ' . ($t->station_name ?? '') . ' ' . ($t->fuel_type ?? '') . ' ' . ($t->notes ?? '')) }}">
                                <td class="fw-bold text-muted text-nowrap">
                                    <div class="d-flex align-items-center gap-1">
                                        <span>#{{ $t->id }}</span>
                                        <a href="{{ route('operations.index', ['type' => 'fuel', 'period' => 'all', 'search' => '#' . $t->id]) }}" class="btn-entity-chip py-0 px-2" style="font-size:0.7rem;" title="تعديل شامل للعملية في سجل العمليات">
                                            <i class="fa fa-pen text-warning"></i> تعديل
                                        </a>
                                    </div>
                                </td>
                                <td style="font-size:0.78rem;" dir="ltr">{{ \Carbon\Carbon::parse($t->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="fw-bold">{{ $t->company_name }}</td>
                                <td>{{ $t->station_name }}</td>
                                <td class="fw-bold text-dark">
                                    <button type="button" onclick="openDriverReportModal('{{ addslashes($t->driver_name) }}')" class="btn-entity-chip" title="انقر لفتح التقرير التفصيلي للشاحنة">
                                        <i class="fa fa-chart-line text-primary"></i>
                                        <span>{{ $t->driver_name }}</span>
                                    </button>
                                    @if($isSuspicious)
                                        <button type="button" class="btn btn-xs btn-danger text-white fw-bold py-0 px-2 ms-1 rounded-2" onclick="openDriverReportModal('{{ addslashes($t->driver_name) }}')" title="{{ $suspiciousMap[$t->id] }}" data-bs-toggle="tooltip">
                                            <i class="fa fa-triangle-exclamation"></i> هدر 🚨
                                        </button>
                                    @endif
                                </td>
                                <td class="fw-bold" dir="ltr">{{ $t->odometer ? number_format($t->odometer) . ' كم' : '—' }}</td>
                                <td>
                                    @if($t->distance_km)
                                        <span class="badge bg-info bg-opacity-10 text-primary fw-bold" dir="ltr">+{{ number_format($t->distance_km) }} كم</span>
                                    @elseif($t->odometer > 0 && $isFull)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary small" title="رحلة جارية — ستُسجل المسافة عند التفويلة القادمة" data-bs-toggle="tooltip">رحلة جارية</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isCustodyOnly)
                                        <span class="tank-badge custody"><i class="fa fa-hand-holding-dollar"></i> عهدة</span>
                                    @elseif($isFull)
                                        <span class="tank-badge full"><i class="fa fa-circle-check"></i> كاملة</span>
                                    @else
                                        <span class="tank-badge partial"><i class="fa fa-adjust"></i> جزئية</span>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $isCustodyOnly ? 'text-muted' : 'text-warning' }}">
                                    @if($isCustodyOnly)
                                        <span class="text-muted small">بدون وقود</span>
                                        <small class="text-primary d-block" style="font-size:0.7rem;">عهدة نقدية</small>
                                    @else
                                        <div>{{ number_format($t->liters, 1) }} لتر</div>
                                        @if($t->distance_km > 0 && $t->consumption_rate > 0)
                                            @php $tripBurnedLiters = round($t->distance_km / $t->consumption_rate, 1); @endphp
                                            <small class="text-primary d-block" style="font-size:0.7rem;" title="اللترات التي استهلكتها هذه الرحلة وتم تعويضها في التفويلة التالية">
                                                استهلكت: {{ number_format($tripBurnedLiters, 1) }} لتر
                                            </small>
                                        @else
                                            <small class="text-muted" style="font-size:0.72rem;">{{ $t->fuel_type }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($isCustodyOnly)
                                        <span class="text-muted">—</span>
                                    @elseif($t->consumption_rate)
                                        @php
                                            $rateVal = floatval($t->consumption_rate);
                                            $isLowRate = $rateVal < $effectiveMinEff;
                                        @endphp
                                        @if($isLowRate)
                                            <span class="rate-badge danger" dir="ltr" title="{{ $suspiciousMap[$t->id] ?? 'استهلاك مفرط (أقل من الحد القياسي)' }}" data-bs-toggle="tooltip">
                                                <i class="fa fa-triangle-exclamation"></i>
                                                <span>{{ number_format($rateVal, 2) }} كم/لتر</span>
                                            </span>
                                        @else
                                            <span class="rate-badge good" dir="ltr" title="كفاءة استهلاك طبيعية" data-bs-toggle="tooltip">
                                                <i class="fa fa-gauge-high"></i>
                                                <span>{{ number_format($rateVal, 2) }} كم/لتر</span>
                                            </span>
                                        @endif
                                        @if($t->cost_per_km > 0)
                                            <div class="small text-muted mt-1" style="font-size:0.7rem;" dir="ltr">{{ number_format($t->cost_per_km, 2) }} ج/كم</div>
                                        @endif
                                    @elseif(!$isFull)
                                        <span class="rate-badge waiting" title="تزويد جزئي — في انتظار التفويلة الكاملة لحساب الكفاءة" data-bs-toggle="tooltip">
                                            <i class="fa fa-clock"></i>
                                            <span>في انتظار التفويلة</span>
                                        </span>
                                    @elseif($t->odometer > 0)
                                        <span class="rate-badge waiting" title="رحلة جارية بدأت من عداد {{ number_format($t->odometer) }} كم — سيتم حساب المسافة وكفاءة الاستهلاك وتكلفة الكيلو لها عند التفويلة القادمة" data-bs-toggle="tooltip">
                                            <i class="fa fa-truck-fast"></i>
                                            <span>رحلة جارية (تُقفل بالتفويلة القادمة)</span>
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $t->cash_advance > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $t->cash_advance > 0 ? number_format($t->cash_advance) . ' ج' : '—' }}
                                </td>
                                <td class="fw-bold text-dark">{{ number_format($t->total_on_company, 2) }} ج</td>
                                <td class="fw-bold text-success">+{{ number_format($t->ahmed_profit, 2) }} ج</td>
                                <td class="text-muted small" style="max-width:140px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="{{ $t->notes }}">
                                    {{ $t->notes ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="14" class="text-center py-4 text-muted">لا توجد عمليات مطابقة للفلتر المحدد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div> <!-- /tab-analytics-pane -->

        {{-- ═══════════ TAB 3: تابة المقارنات والرسوم البيانية (كل طرف بلون مميز وشارتات تفاعلية) ⚖️📊 ═══════════ --}}
        <div class="tab-pane fade {{ $activeTab === 'comparison' ? 'show active' : '' }}" id="tab-comparison-pane" role="tabpanel" aria-labelledby="tab-comparison-btn">

            {{-- 1) لوحة ترتيب الأفضل والشارتات للأقسام الأربعة (السائقين · العربيات · الوجهات · المحطات) --}}
            <div class="card border shadow-xs mb-3" style="border-radius: 12px; border-color: #e2e8f0 !important; background: #fff;">
                <div class="card-header bg-white px-3 py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <i class="fa fa-trophy text-warning" id="best_board_icon"></i>
                        <span class="fw-bold text-dark" style="font-size: 0.95rem;" id="best_board_title_text">ترتيب وشارتات الأفضل كفاءة في الأسطول</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">📅 {{ $periodLabel ?? 'آخر شهر (30 يوماً)' }}</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="d-flex flex-wrap gap-2" role="group" id="best_criterion_btn_group">
                            <button type="button" class="btn-report-solid active-blue" data-criterion="best_efficiency" onclick="setBestRankingMode('best_efficiency')">
                                <i class="fa fa-gauge-high"></i> أفضل كفاءة (كم/لتر)
                            </button>
                            <button type="button" class="btn-report-solid" data-criterion="worst_efficiency" onclick="setBestRankingMode('worst_efficiency')">
                                <i class="fa fa-triangle-exclamation text-danger"></i> أسوأ كفاءة (كم/لتر)
                            </button>
                            <button type="button" class="btn-report-solid" data-criterion="cheapest_cost_km" onclick="setBestRankingMode('cheapest_cost_km')">
                                <i class="fa fa-coins text-success"></i> أرخص تكلفة (ج/كم)
                            </button>
                            <button type="button" class="btn-report-solid" data-criterion="highest_cost_km" onclick="setBestRankingMode('highest_cost_km')">
                                <i class="fa fa-fire-flame-curved text-danger"></i> أغلى تكلفة (ج/كم)
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-dark fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-1" onclick="printComparisonReport()" title="تصدير المقارنات والشارتات بدقة عالية Vector PDF">
                            <i class="fa fa-file-pdf text-warning"></i>
                            <span>تصدير المقارنات PDF</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="row g-3" id="best_four_categories_grid">
                        {{-- السائقين --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="border rounded-3 h-100 d-flex flex-column" style="border-color: #cbd5e1 !important; background: #fcfcfd;">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc;">
                                    <span class="fw-bold text-dark small"><i class="fa fa-user-tie text-primary me-1"></i> السائقين</span>
                                    <button type="button" class="btn-entity-chip py-1 px-2" style="font-size: 0.72rem;" onclick="compareTopTwoInCategory('drivers')">
                                        <i class="fa fa-scale-balanced text-primary"></i> مقارنة 1 و 2
                                    </button>
                                </div>
                                <div class="p-2 border-bottom bg-white" style="height: 155px; position: relative;">
                                    <canvas id="bestChart_drivers"></canvas>
                                </div>
                                <div class="p-2 flex-grow-1" id="best_list_drivers" style="max-height: 210px; overflow-y: auto;"></div>
                            </div>
                        </div>

                        {{-- العربيات --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="border rounded-3 h-100 d-flex flex-column" style="border-color: #cbd5e1 !important; background: #fcfcfd;">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc;">
                                    <span class="fw-bold text-dark small"><i class="fa fa-truck text-success me-1"></i> العربيات</span>
                                    <button type="button" class="btn-entity-chip py-1 px-2" style="font-size: 0.72rem;" onclick="compareTopTwoInCategory('cars')">
                                        <i class="fa fa-scale-balanced text-success"></i> مقارنة 1 و 2
                                    </button>
                                </div>
                                <div class="p-2 border-bottom bg-white" style="height: 155px; position: relative;">
                                    <canvas id="bestChart_cars"></canvas>
                                </div>
                                <div class="p-2 flex-grow-1" id="best_list_cars" style="max-height: 210px; overflow-y: auto;"></div>
                            </div>
                        </div>

                        {{-- الوجهات --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="border rounded-3 h-100 d-flex flex-column" style="border-color: #cbd5e1 !important; background: #fcfcfd;">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc;">
                                    <span class="fw-bold text-dark small"><i class="fa fa-route text-warning me-1"></i> الوجهات</span>
                                    <button type="button" class="btn-entity-chip py-1 px-2" style="font-size: 0.72rem;" onclick="compareTopTwoInCategory('destinations')">
                                        <i class="fa fa-scale-balanced text-warning"></i> مقارنة 1 و 2
                                    </button>
                                </div>
                                <div class="p-2 border-bottom bg-white" style="height: 155px; position: relative;">
                                    <canvas id="bestChart_destinations"></canvas>
                                </div>
                                <div class="p-2 flex-grow-1" id="best_list_destinations" style="max-height: 210px; overflow-y: auto;"></div>
                            </div>
                        </div>

                        {{-- المحطات --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="border rounded-3 h-100 d-flex flex-column" style="border-color: #cbd5e1 !important; background: #fcfcfd;">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc;">
                                    <span class="fw-bold text-dark small"><i class="fa fa-gas-pump text-danger me-1"></i> المحطات</span>
                                    <button type="button" class="btn-entity-chip py-1 px-2" style="font-size: 0.72rem;" onclick="compareTopTwoInCategory('stations')">
                                        <i class="fa fa-scale-balanced text-danger"></i> مقارنة 1 و 2
                                    </button>
                                </div>
                                <div class="p-2 border-bottom bg-white" style="height: 155px; position: relative;">
                                    <canvas id="bestChart_stations"></canvas>
                                </div>
                                <div class="p-2 flex-grow-1" id="best_list_stations" style="max-height: 210px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2) لوحة المقارنة المباشرة (الطرف أ باللون الأزرق الملكي ضد الطرف ب باللون البرتقالي المميز) --}}
            <div class="card border shadow-xs mb-4" id="head_to_head_comparison_panel" style="border-radius: 12px; border-color: #e2e8f0 !important; background: #fff;">
                <div class="card-header bg-white px-3 py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                            <i class="fa fa-scale-balanced text-primary me-1"></i> <span id="cmp_header_title">المقارنة المباشرة والشارتات</span>
                        </span>
                        <div class="d-flex flex-wrap gap-2" role="group" id="compare_mode_group">
                            <button type="button" class="btn-report-solid active-blue" data-mode="vehicle" onclick="setCompareMode('vehicle')">
                                <i class="fa fa-truck"></i> الشاحنات / السائقين
                            </button>
                            <button type="button" class="btn-report-solid" data-mode="station" onclick="setCompareMode('station')">
                                <i class="fa fa-gas-pump"></i> المحطات
                            </button>
                            <button type="button" class="btn-report-solid" data-mode="destination" onclick="setCompareMode('destination')">
                                <i class="fa fa-route"></i> الوجهات
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small d-none d-md-inline">
                            الحد القياسي: {{ $radarMinEff }} كم/ل · {{ $radarMaxCostKm }} ج/كم
                        </span>
                        <button type="button" class="btn-report-solid py-1 px-2" onclick="openRadarSettingsModal()">
                            <i class="fa fa-gear text-secondary"></i> المعايير
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-bold py-1 px-3 d-inline-flex align-items-center gap-1" onclick="printComparisonReport()" title="طباعة وحفظ المقارنة والشارتات كملف PDF">
                            <i class="fa fa-print"></i> طباعة المقارنة PDF
                        </button>
                    </div>
                </div>

                <div class="card-body p-3">
                    {{-- شريط اختيار الطرفين أ (أزرق) و ب (برتقالي) --}}
                    <div class="row g-2 align-items-end mb-3 pb-3 border-bottom" style="border-color: #f1f5f9 !important;">
                        <div class="col-lg-5 col-md-5">
                            <div class="p-2 rounded-3" style="background: rgba(37, 99, 235, 0.05); border: 1.5px solid rgba(37, 99, 235, 0.25);">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold mb-0 small" style="color: #1d4ed8;">
                                        <span class="badge rounded-1 me-1" style="background:#2563eb; color:#fff;">أ</span>
                                        <span id="cmp_label_a">الطرف الأول (اللون الأزرق)</span>
                                    </label>
                                    <input type="text" id="search_compare_a" class="form-control form-control-sm py-0 px-2 bg-white"
                                           style="width: 165px; height: 26px; font-size: 0.75rem; border-color: #93c5fd;"
                                           placeholder="🔍 بحث سريع..."
                                           oninput="filterCompareSelect('a', this.value)" autocomplete="off">
                                </div>
                                <select id="compare_select_a" class="form-select form-select-sm fw-bold" style="height: 38px; border-color: #2563eb;" onchange="triggerVehicleComparison()">
                                    <option value="">— اختر الطرف الأول (أ) —</option>
                                    @if(isset($detailedDriversReport) && count($detailedDriversReport))
                                        <optgroup label="👤 السائقين">
                                            @foreach($detailedDriversReport as $idx => $d)
                                                @php
                                                    $dName = $d->driver_name;
                                                    $dCars = $d->cars_list ? ' · ' . $d->cars_list : '';
                                                @endphp
                                                <option value="{{ $dName }}" data-type="driver" data-search="{{ mb_strtolower($dName . ' ' . ($d->cars_list ?? '') . ' سائق') }}" {{ $idx === 0 ? 'selected' : '' }}>
                                                    👤 {{ $dName }} ({{ $d->total_trips }} رحلة{{ $dCars }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if(isset($detailedCarsReport) && count($detailedCarsReport))
                                        <optgroup label="🚗 العربيات">
                                            @foreach($detailedCarsReport as $idx => $c)
                                                @php
                                                    $cPlate = $c->car_number;
                                                    $cDrivers = $c->drivers_list ? ' · ' . $c->drivers_list : '';
                                                @endphp
                                                <option value="{{ $cPlate }}" data-type="car" data-search="{{ mb_strtolower($cPlate . ' ' . ($c->drivers_list ?? '') . ' سيارة شاحنة') }}">
                                                    🚗 {{ $cPlate }} ({{ $c->total_trips }} رحلة{{ $cDrivers }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @elseif(isset($knownVehicles))
                                        <optgroup label="🚗 العربيات">
                                            @foreach($knownVehicles as $idx => $v)
                                                @php
                                                    $vKey = $v->vehicle_key;
                                                    $vPlate = ($v->car_number && $v->car_number != '-') ? $v->car_number : $v->vehicle_key;
                                                    $vTitle = $vPlate . ($v->driver_name ? " - {$v->driver_name}" : '');
                                                @endphp
                                                <option value="{{ $vKey }}" data-search="{{ mb_strtolower($vPlate . ' ' . ($v->driver_name ?? '') . ' ' . $vKey) }}">
                                                    🚗 {{ $vTitle }} ({{ $v->total_trips }} رحلة)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                <div id="compare_quick_matches_a" class="d-flex flex-wrap gap-1 mt-1"></div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 text-center d-flex align-items-center justify-content-center py-2">
                            <button type="button" class="btn-report-solid w-100 justify-content-center" style="height: 42px;" onclick="swapCompareVehicles()" title="تبديل الطرفين">
                                <i class="fa fa-arrow-right-arrow-left text-primary"></i> تبديل الطرفين
                            </button>
                        </div>

                        <div class="col-lg-5 col-md-5">
                            <div class="p-2 rounded-3" style="background: rgba(234, 88, 12, 0.05); border: 1.5px solid rgba(234, 88, 12, 0.25);">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold mb-0 small" style="color: #c2410c;">
                                        <span class="badge rounded-1 me-1" style="background:#ea580c; color:#fff;">ب</span>
                                        <span id="cmp_label_b">الطرف الثاني (اللون البرتقالي)</span>
                                    </label>
                                    <input type="text" id="search_compare_b" class="form-control form-control-sm py-0 px-2 bg-white"
                                           style="width: 165px; height: 26px; font-size: 0.75rem; border-color: #fdba74;"
                                           placeholder="🔍 بحث سريع..."
                                           oninput="filterCompareSelect('b', this.value)" autocomplete="off">
                                </div>
                                <select id="compare_select_b" class="form-select form-select-sm fw-bold" style="height: 38px; border-color: #ea580c;" onchange="triggerVehicleComparison()">
                                    <option value="">— اختر الطرف الثاني (ب) —</option>
                                    @if(isset($detailedDriversReport) && count($detailedDriversReport))
                                        <optgroup label="👤 السائقين">
                                            @foreach($detailedDriversReport as $idx => $d)
                                                @php
                                                    $dName = $d->driver_name;
                                                    $dCars = $d->cars_list ? ' · ' . $d->cars_list : '';
                                                @endphp
                                                <option value="{{ $dName }}" data-type="driver" data-search="{{ mb_strtolower($dName . ' ' . ($d->cars_list ?? '') . ' سائق') }}" {{ $idx === 1 ? 'selected' : '' }}>
                                                    👤 {{ $dName }} ({{ $d->total_trips }} رحلة{{ $dCars }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if(isset($detailedCarsReport) && count($detailedCarsReport))
                                        <optgroup label="🚗 العربيات">
                                            @foreach($detailedCarsReport as $idx => $c)
                                                @php
                                                    $cPlate = $c->car_number;
                                                    $cDrivers = $c->drivers_list ? ' · ' . $c->drivers_list : '';
                                                @endphp
                                                <option value="{{ $cPlate }}" data-type="car" data-search="{{ mb_strtolower($cPlate . ' ' . ($c->drivers_list ?? '') . ' سيارة شاحنة') }}">
                                                    🚗 {{ $cPlate }} ({{ $c->total_trips }} رحلة{{ $cDrivers }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @elseif(isset($knownVehicles))
                                        <optgroup label="🚗 العربيات">
                                            @foreach($knownVehicles as $idx => $v)
                                                @php
                                                    $vKey = $v->vehicle_key;
                                                    $vPlate = ($v->car_number && $v->car_number != '-') ? $v->car_number : $v->vehicle_key;
                                                    $vTitle = $vPlate . ($v->driver_name ? " - {$v->driver_name}" : '');
                                                @endphp
                                                <option value="{{ $vKey }}" data-search="{{ mb_strtolower($vPlate . ' ' . ($v->driver_name ?? '') . ' ' . $vKey) }}">
                                                    🚗 {{ $vTitle }} ({{ $v->total_trips }} رحلة)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                <div id="compare_quick_matches_b" class="d-flex flex-wrap gap-1 mt-1"></div>
                            </div>
                        </div>
                    </div>

                    {{-- مؤشر التحميل --}}
                    <div id="compare_loading_spinner" class="text-center py-4" style="display:none;">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div class="small text-muted">جاري تحميل المقارنة والشارتات...</div>
                    </div>

                    {{-- رسالة تنبيه --}}
                    <div id="compare_error_box" class="alert alert-light border text-secondary py-2 small rounded-2" style="display:none;"></div>

                    {{-- نتائج المقارنة --}}
                    <div id="compare_results_box">

                        {{-- شريط خلاصة المقارنة --}}
                        <div class="p-2 px-3 rounded-2 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" id="compare_winner_banner" style="background: #f8fafc; border: 1px solid #e2e8f0; border-right: 4px solid #10b981;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-circle-check text-success"></i>
                                <span class="fw-bold text-dark small" id="compare_winner_title">الخلاصة:</span>
                                <span class="text-secondary small" id="compare_winner_desc">اختر الطرفين لعرض المقارنة.</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 small fw-bold">
                                <span style="color:#2563eb;"><i class="fa fa-square me-1"></i> الطرف (أ) أزرق</span>
                                <span style="color:#ea580c;"><i class="fa fa-square me-1"></i> الطرف (ب) برتقالي</span>
                            </div>
                        </div>

                        {{-- 📊 الشارتات التفاعلية للمقارنة (في المقدمة وكل طرف بلون مختلف) --}}
                        <div class="row g-3 mb-3" id="compare_charts_row">
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 bg-white h-100 shadow-xs" style="border-color: #cbd5e1 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark small"><i class="fa fa-chart-column text-primary me-1"></i> شارت الكفاءة (كم / لتر)</span>
                                        <small class="fw-bold" id="cmp_eff_verdict">—</small>
                                    </div>
                                    <div style="position: relative; height: 220px;">
                                        <canvas id="compareEfficiencyChartCanvas"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 bg-white h-100 shadow-xs" style="border-color: #cbd5e1 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark small"><i class="fa fa-coins text-warning me-1"></i> شارت تكلفة الكيلو وسعر اللتر</span>
                                        <small class="fw-bold" id="cmp_cost_verdict">—</small>
                                    </div>
                                    <div style="position: relative; height: 220px;">
                                        <canvas id="compareCostChartCanvas"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 bg-white h-100 shadow-xs" style="border-color: #cbd5e1 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark small"><i class="fa fa-gas-pump text-success me-1"></i> شارت اللترات والمسافة والرحلات</span>
                                        <small class="text-muted small">مقارنة حجم التشغيل</small>
                                    </div>
                                    <div style="position: relative; height: 220px;">
                                        <canvas id="compareVolumeChartCanvas"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- بطاقتا المقارنة جنباً إلى جنب (الطرف أ أزرق · الطرف ب برتقالي) --}}
                        <div class="row g-3 mb-3">
                            {{-- الطرف أ (أزرق) --}}
                            <div class="col-lg-6">
                                <div class="border rounded-3 p-3 h-100 bg-white" style="border-color: #bfdbfe !important; border-top: 4px solid #2563eb !important; background: linear-gradient(180deg, rgba(37,99,235,0.03) 0%, #ffffff 60%);">
                                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom" style="border-color: #e2e8f0 !important;">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge rounded-1 px-2 py-1" style="background:#2563eb; color:#fff;">الطرف أ</span>
                                                <h6 class="fw-bold m-0" style="color:#1e40af;" id="cmp_a_title">الطرف (أ)</h6>
                                            </div>
                                            <small id="cmp_a_subtitle" class="text-muted d-block mt-1" style="font-size: 0.75rem;">—</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1" id="cmp_a_badges_container"></div>
                                    </div>

                                    <div class="row g-2 mb-2 text-center">
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#eff6ff; border-color:#bfdbfe !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">كفاءة الاستهلاك</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_a_eff">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#eff6ff; border-color:#bfdbfe !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">تكلفة الكيلو</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_a_cost_km">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">المسافة المقطوعة</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_a_dist">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">إجمالي الوقود</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_a_liters">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">إجمالي التكلفة</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_a_cost">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">الرحلات</div>
                                                <div class="fw-bold text-dark mt-1" style="font-size:0.8rem;" id="cmp_a_trips">—</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top small text-muted flex-wrap gap-2" style="border-color:#e2e8f0 !important; font-size:0.75rem;">
                                        <span class="cmp-odometer-row">آخر عداد: <strong class="text-dark" id="cmp_a_odometer">—</strong></span>
                                        <span id="cmp_a_suspicion">—</span>
                                        <span class="d-none" id="cmp_a_drivers_tags"></span>
                                        <button type="button" class="btn-entity-chip py-1 px-2" id="cmp_a_btn_drivvo" style="font-size:0.72rem;" onclick="">
                                            <i class="fa fa-chart-line text-primary"></i> التقرير المفصل
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- الطرف ب (برتقالي) --}}
                            <div class="col-lg-6">
                                <div class="border rounded-3 p-3 h-100 bg-white" style="border-color: #fed7aa !important; border-top: 4px solid #ea580c !important; background: linear-gradient(180deg, rgba(234,88,12,0.03) 0%, #ffffff 60%);">
                                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom" style="border-color: #e2e8f0 !important;">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge rounded-1 px-2 py-1" style="background:#ea580c; color:#fff;">الطرف ب</span>
                                                <h6 class="fw-bold m-0" style="color:#c2410c;" id="cmp_b_title">الطرف (ب)</h6>
                                            </div>
                                            <small id="cmp_b_subtitle" class="text-muted d-block mt-1" style="font-size: 0.75rem;">—</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1" id="cmp_b_badges_container"></div>
                                    </div>

                                    <div class="row g-2 mb-2 text-center">
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#fff7ed; border-color:#fed7aa !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">كفاءة الاستهلاك</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_b_eff">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#fff7ed; border-color:#fed7aa !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">تكلفة الكيلو</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_b_cost_km">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">المسافة المقطوعة</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_b_dist">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">إجمالي الوقود</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_b_liters">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">إجمالي التكلفة</div>
                                                <div class="fw-bold text-dark mt-1" id="cmp_b_cost">—</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded-2 border" style="background:#f8fafc; border-color:#e2e8f0 !important;">
                                                <div class="text-muted" style="font-size:0.7rem;">الرحلات</div>
                                                <div class="fw-bold text-dark mt-1" style="font-size:0.8rem;" id="cmp_b_trips">—</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top small text-muted flex-wrap gap-2" style="border-color:#e2e8f0 !important; font-size:0.75rem;">
                                        <span class="cmp-odometer-row">آخر عداد: <strong class="text-dark" id="cmp_b_odometer">—</strong></span>
                                        <span id="cmp_b_suspicion">—</span>
                                        <span class="d-none" id="cmp_b_drivers_tags"></span>
                                        <button type="button" class="btn-entity-chip py-1 px-2" id="cmp_b_btn_drivvo" style="font-size:0.72rem;" onclick="">
                                            <i class="fa fa-chart-line" style="color:#ea580c;"></i> التقرير المفصل
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- جدول الوجهات المشتركة --}}
                        <div class="border rounded-3 bg-white overflow-hidden" id="compareDestinationsPanel" style="display:none; border-color: #e2e8f0 !important;">
                            <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="background: #f8fafc;">
                                <span class="fw-bold text-dark small" id="cmp_dest_panel_title">مقارنة خطوط السير والوجهات</span>
                                <span class="badge bg-white text-secondary border fw-normal" id="compareDestinationsBadge">0</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0 text-center" style="font-size: 0.84rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start pe-3" id="cmp_dest_th_loc">الوجهة</th>
                                            <th style="color:#2563eb;">كفاءة (أ)</th>
                                            <th style="color:#2563eb;">تكلفة كم (أ)</th>
                                            <th style="color:#ea580c;">كفاءة (ب)</th>
                                            <th style="color:#ea580c;">تكلفة كم (ب)</th>
                                            <th>الفرق</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody id="compareDestinationsTbody"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div> <!-- /tab-comparison-pane -->
</div> <!-- /gasTabsContent -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function onFuelItemChange() {
        let itemSelect = document.getElementById('item_id');
        let selectedOption = itemSelect.options[itemSelect.selectedIndex];
        let price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        let priceInput = document.getElementById('fuel_price');
        if (priceInput) {
            priceInput.value = price > 0 ? price : '';
        }

        let hasAdvanceSelect = document.getElementById('has_advance');
        if (itemSelect.value === '0') {
            if (hasAdvanceSelect) hasAdvanceSelect.value = 'yes';
        } else {
            if (hasAdvanceSelect && itemSelect.dataset.lastWasCustodyOnly === 'true') {
                hasAdvanceSelect.value = 'no';
            }
        }
        itemSelect.dataset.lastWasCustodyOnly = (itemSelect.value === '0') ? 'true' : 'false';

        calculateGasStation();
        calculateDrivvoTrip();
    }

    function calculateGasStation() {
        let itemSelect = document.getElementById('item_id');
        let selectedOption = itemSelect.options[itemSelect.selectedIndex];
        let defaultItemPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;

        let fuelPriceInput = document.getElementById('fuel_price');
        let price = defaultItemPrice;
        if (fuelPriceInput && fuelPriceInput.value !== '') {
            price = parseFloat(fuelPriceInput.value) || 0;
        } else if (fuelPriceInput && defaultItemPrice > 0) {
            fuelPriceInput.value = defaultItemPrice;
            price = defaultItemPrice;
        }

        // 💡 "عهدة نقدية فقط (بدون وقود)" => العملية لازم تتضمن عهدة، فنخفي السؤال التاني ونفرض "نعم"
        let isCustodyOnly = itemSelect.value === '0';
        let hasAdvanceSelect = document.getElementById('has_advance');
        let hasAdvanceBox = document.getElementById('has_advance_box');
        let quantityBox = document.getElementById('quantity_box');
        let priceBox = document.getElementById('price_box');
        let fuelSubtotalBox = document.getElementById('fuel_subtotal_box');

        let advanceAmountCol = document.getElementById('advance_amount_col');
        let advanceSourceCol = document.getElementById('advance_source_col');
        let cashAdvanceInput = document.getElementById('cash_advance');

        let hasAdvance = hasAdvanceSelect ? (hasAdvanceSelect.value === 'yes') : false;

        let odoTankRow = document.getElementById('odometer_tank_row');
        let liveTripBox = document.getElementById('live_trip_metrics_box');
        let modeBadge = document.getElementById('badge_trip_mode');

        if (isCustodyOnly) {
            if (hasAdvanceSelect) hasAdvanceSelect.value = 'yes';
            hasAdvance = true;
            if (hasAdvanceBox) hasAdvanceBox.style.display = 'none';
            if (quantityBox) quantityBox.style.display = 'none';
            if (priceBox) priceBox.style.display = 'none';
            if (fuelSubtotalBox) fuelSubtotalBox.style.display = 'none';
            if (odoTankRow) odoTankRow.style.display = 'none';
            if (liveTripBox) liveTripBox.style.display = 'none';
            if (modeBadge) {
                modeBadge.innerHTML = '<i class="fa fa-hand-holding-dollar me-1"></i> عهدة نقدية فقط (بدون وقود)';
                modeBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small fw-bold';
            }
            if (advanceAmountCol) {
                advanceAmountCol.style.display = 'block';
                advanceAmountCol.className = 'col-md-6';
            }
            if (advanceSourceCol) {
                advanceSourceCol.style.display = 'block';
                advanceSourceCol.className = 'col-md-6';
            }
        } else {
            if (quantityBox) quantityBox.style.display = '';
            if (priceBox) priceBox.style.display = '';
            if (fuelSubtotalBox) fuelSubtotalBox.style.display = '';
            if (odoTankRow) odoTankRow.style.display = '';
            if (liveTripBox) liveTripBox.style.display = '';
            const tankSwitch = document.getElementById('switch_full_tank');
            if (modeBadge && tankSwitch) {
                if (tankSwitch.checked) {
                    modeBadge.innerHTML = '<i class="fa fa-circle-check me-1"></i> تفويلة كاملة (Full Tank)';
                    modeBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-bold';
                } else {
                    modeBadge.innerHTML = '<i class="fa fa-clock me-1"></i> تزويد جزئي (Partial Fill)';
                    modeBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 small fw-bold';
                }
            }
            if (hasAdvanceBox) {
                hasAdvanceBox.style.display = 'block';
                hasAdvanceBox.className = hasAdvance ? 'col-md-4' : 'col-12';
            }
            if (advanceAmountCol) {
                advanceAmountCol.style.display = hasAdvance ? 'block' : 'none';
                advanceAmountCol.className = 'col-md-4';
            }
            if (advanceSourceCol) {
                advanceSourceCol.style.display = hasAdvance ? 'block' : 'none';
                advanceSourceCol.className = 'col-md-4';
            }
        }

        let quantity = parseFloat(document.getElementById('quantity')?.value) || 0;
        let fuelDebt = isCustodyOnly ? 0 : (quantity * price);

        let fuelSubtotalEl = document.getElementById('fuel_subtotal_val');
        if (fuelSubtotalEl) {
            fuelSubtotalEl.innerText = fmtMoney(fuelDebt);
        }

        let advance = 0;
        let advanceSource = 'none';
        let mixedSource = 'station';

        if (hasAdvance) {
            if (cashAdvanceInput) {
                cashAdvanceInput.required = true;
                cashAdvanceInput.min = "0.01";
            }
            advance = parseFloat(document.getElementById('cash_advance')?.value) || 0;
            mixedSource = document.getElementById('mixed_source')?.value || 'station';
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
            if (cashAdvanceInput) {
                cashAdvanceInput.required = false;
                cashAdvanceInput.value = '';
            }
            advance = 0;
            advanceSource = 'none';
            document.getElementById('hidden_advance_source').value = 'none';
            document.getElementById('hidden_account_id').value = '';
        }

        let manualDiscount = parseFloat(document.getElementById('manual_discount').value) || 0;

        let grossProfit = advanceSource === 'none'
            ? fuelDebt * 0.01
            : (fuelDebt + advance) * 0.01;

        let totalToStation = advanceSource === 'station' ? fuelDebt + advance : fuelDebt;
        let totalOnCompany = fuelDebt + advance + grossProfit - manualDiscount;

        document.getElementById('res_station').innerText = fmtMoney(totalToStation);
        document.getElementById('gross_profit').innerText = fmtMoney(grossProfit);
        document.getElementById('res_company').innerText = fmtMoney(totalOnCompany);

        let totalDeductions = 0;
        let profitAfterDiscount = grossProfit - manualDiscount;

        document.querySelectorAll('.deduction-val-span').forEach(span => {
            let pct = parseFloat(span.getAttribute('data-pct')) || 0;
            let currentDeductionVal = profitAfterDiscount * (pct / 100);
            span.innerText = fmtMoney(currentDeductionVal);
            totalDeductions += currentDeductionVal;
        });

        let netProfit = profitAfterDiscount - totalDeductions;
        document.getElementById('manual_discount_display').innerText = fmtMoney(manualDiscount);
        document.getElementById('net_profit').innerText = fmtMoney(netProfit);
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

    document.addEventListener("DOMContentLoaded", function() {
        let itemSelect = document.getElementById('item_id');
        if (itemSelect) {
            itemSelect.dataset.lastWasCustodyOnly = (itemSelect.value === '0') ? 'true' : 'false';
        }
        calculateGasStation();
    });

    // ── تطبيع النص العربي (احتياطي محلي لو الدالة العامة غير محملة) ──
    function gsNormalize(text) {
        if (typeof window.normalizeArabic === 'function') return window.normalizeArabic(text || '');
        return String(text || '').toLowerCase().trim()
            .replace(/[\u064B-\u0652\u0640]/g, '')
            .replace(/[أإآٱ]/g, 'ا')
            .replace(/ى/g, 'ي')
            .replace(/ة/g, 'ه')
            .replace(/ؤ/g, 'و')
            .replace(/ئ/g, 'ي')
            .replace(/\s+/g, ' ');
    }

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
        const q = gsNormalize(d.inputEl().value);
        let visible = 0;
        d.allItems.forEach(item => {
            const label = gsNormalize(item.dataset.label);
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
    function fmtDues(n) { return fmtMoney(n); }
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
            return;
        }
        const routeVal = (document.getElementById('trip_route_input')?.value || '').trim();
        if (!routeVal) {
            e.preventDefault();
            const inp = document.getElementById('trip_route_input');
            if (inp) {
                inp.focus();
                inp.style.borderColor = '#ef4444';
                inp.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
            }
            alert('⚠️ خط سير الرحلة / المكان والوجهة حقل إجباري لتسجيل حركة البنزينة.');
            return;
        }

        const hasAdvVal = document.getElementById('has_advance')?.value === 'yes';
        const cashAdvInp = document.getElementById('cash_advance');
        const cashAdvVal = parseFloat(cashAdvInp?.value) || 0;
        if (hasAdvVal && cashAdvVal <= 0) {
            e.preventDefault();
            if (cashAdvInp) {
                cashAdvInp.focus();
                cashAdvInp.style.borderColor = '#ef4444';
                cashAdvInp.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
            }
            alert('⚠️ يرجى إدخال مبلغ العهدة النقدية المطلوبة للسائق (المبلغ إجباري عند اختيار وجود عهدة).');
            return;
        }
    });
    ['company','station'].forEach(key => {
        comboData[key].inputEl().addEventListener('input', () => filterCombo(key));
    });

    // ════════════════════════════════════════════════════════════
    // 🚀 VEHICLE & FLEET ENGINE (حسابات الأسطول التفاعلية)
    // ════════════════════════════════════════════════════════════
    const KNOWN_VEHICLES_LIST = @json($knownVehicles);
    const KNOWN_VEHICLES = {};
    let currentSelectedVehicleMeta = null;

    KNOWN_VEHICLES_LIST.forEach(v => {
        const payload = {
            vehicle_key: v.vehicle_key,
            car_number: v.car_number,
            driver_name: v.driver_name,
            last_odometer: v.last_odometer,
            last_full_odometer: v.last_full_odometer,
            prev_trip_driver: v.prev_trip_driver || v.driver_name || '—',
            prev_trip_station: v.prev_trip_station || '—',
            prev_trip_destination: v.prev_trip_destination || '—',
            prev_trip_id: v.prev_trip_id || null,
        };
        if (v.vehicle_key) KNOWN_VEHICLES[v.vehicle_key] = payload;
        if (v.car_number && v.car_number !== '-') KNOWN_VEHICLES[v.car_number] = payload;
        if (v.driver_name && v.driver_name !== '-') {
            if (!KNOWN_VEHICLES[v.driver_name]) KNOWN_VEHICLES[v.driver_name] = payload;
        }
    });

    function checkVehicleOdometerByCar(carNum) {
        carNum = (carNum || '').trim();
        const prevInput = document.getElementById('inp_prev_odometer');
        const driverNameInp = document.getElementById('driver_name_input');
        const driverCarHidden = document.getElementById('driver_car_input');
        const isFull = document.getElementById('switch_full_tank')?.checked ?? true;
        const bannerEl = document.getElementById('prev_trip_attribution_banner');

        if (driverCarHidden) {
            driverCarHidden.value = carNum;
        }

        if (carNum && KNOWN_VEHICLES[carNum]) {
            const vData = KNOWN_VEHICLES[carNum];
            currentSelectedVehicleMeta = vData;
            const chosenOdo = isFull 
                ? (parseFloat(vData.last_full_odometer) || parseFloat(vData.last_odometer) || 0)
                : (parseFloat(vData.last_odometer) || 0);

            if (chosenOdo > 0 && prevInput) {
                prevInput.value = chosenOdo;
            } else if (prevInput) {
                prevInput.value = '';
            }

            // اقتراح اسم السائق إن وجد وكان الحقل فارغاً
            if (driverNameInp && !driverNameInp.value && vData.driver_name && vData.driver_name !== '-') {
                driverNameInp.value = vData.driver_name;
            }

            if (bannerEl) {
                if (chosenOdo > 0) {
                    bannerEl.style.display = 'block';
                    bannerEl.innerHTML = `
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1">
                            <span><i class="fa fa-rotate-left text-primary me-1"></i> <strong>إقفال الرحلة السابقة:</strong> المسافة واللترات والكفاءة ستُسجل على آخر تفويلة</span>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-white text-dark border"><i class="fa fa-user text-primary me-1"></i>السائق السابق: ${vData.prev_trip_driver}</span>
                                <span class="badge bg-white text-dark border"><i class="fa fa-gas-pump text-warning me-1"></i>المحطة السابقة: ${vData.prev_trip_station}</span>
                                <span class="badge bg-white text-dark border"><i class="fa fa-route text-success me-1"></i>الوجهة السابقة: ${vData.prev_trip_destination}</span>
                            </div>
                        </div>
                    `;
                } else {
                    bannerEl.style.display = 'block';
                    bannerEl.innerHTML = `<i class="fa fa-flag-checkered text-primary me-1"></i> <strong>أول قراءة عداد لهذه الشاحنة:</strong> سيتم تسجيل العداد الحالي كبداية للرحلة الجديدة، وتُحسب كفاءتها في التفويلة القادمة.`;
                }
            }
        } else {
            currentSelectedVehicleMeta = null;
            if (prevInput) prevInput.value = '';
            if (bannerEl) {
                if (carNum) {
                    bannerEl.style.display = 'block';
                    bannerEl.innerHTML = `<i class="fa fa-flag-checkered text-primary me-1"></i> <strong>شاحنة جديدة (${carNum}):</strong> سيتم تسجيل العداد الحالي كبداية للرحلة الأولى، وتُحسب كفاءتها عند التفويلة القادمة.`;
                } else {
                    bannerEl.style.display = 'none';
                    bannerEl.innerHTML = '';
                }
            }
        }
        calculateDrivvoTrip();
    }

    function checkVehicleOdometer(driverName) {
        checkVehicleOdometerByCar(driverName);
    }

    function toggleTankSwitch(el) {
        const badge = document.getElementById('tank_status_badge');
        const desc  = document.getElementById('tank_status_desc');
        const modeBadge = document.getElementById('badge_trip_mode');

        if (el.checked) {
            if (badge) badge.innerHTML = '<i class="fa fa-circle-check text-success me-1"></i> تفويلة كاملة';
            if (desc) desc.innerText = 'تُقفل الرحلة السابقة وتحسب كفاءتها (كم/لتر)';
            if (modeBadge) {
                modeBadge.innerHTML = '<i class="fa fa-circle-check me-1"></i> تفويلة كاملة (Full Tank)';
                modeBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-bold';
            }
        } else {
            if (badge) badge.innerHTML = '<i class="fa fa-clock text-warning me-1"></i> تزويد جزئي';
            if (desc) desc.innerText = 'يُضاف للرحلة الجارية لحين التفويلة الكاملة';
            if (modeBadge) {
                modeBadge.innerHTML = '<i class="fa fa-clock me-1"></i> تزويد جزئي (Partial Fill)';
                modeBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 small fw-bold';
            }
        }
        calculateDrivvoTrip();
    }

    function calculateDrivvoTrip() {
        const prevInput = document.getElementById('inp_prev_odometer');
        const curInput  = document.getElementById('inp_odometer');
        const prevOdo   = parseFloat(prevInput?.value) || 0;
        const curOdo    = parseFloat(curInput?.value) || 0;
        const qty       = parseFloat(document.getElementById('quantity')?.value) || 0;
        const priceInp  = document.getElementById('fuel_price');
        const itemSel   = document.getElementById('item_id');
        const unitPrice = parseFloat(priceInp?.value) || (itemSel ? parseFloat(itemSel.options[itemSel.selectedIndex]?.getAttribute('data-price')) : 0) || 0;
        const fuelDebt  = (itemSel && itemSel.value === '0') ? 0 : (qty * unitPrice);
        const fuelCostOnly = fuelDebt > 0 ? (fuelDebt * 1.01) : 0; // ثمن الوقود + عمولة الشركة 1% فقط بدون أي عهدة
        const isFull    = document.getElementById('switch_full_tank')?.checked ?? true;

        // عناصر العرض في نموذج التفويلة
        const formDist   = document.getElementById('form_live_dist');
        const formRate   = document.getElementById('form_live_rate');
        const formCost   = document.getElementById('form_live_cost');
        const formStatus = document.getElementById('form_live_status');

        // عناصر العرض في اللوحة الجانبية
        const sideDist = document.getElementById('live_dist_val');
        const sideRate = document.getElementById('live_rate_val');
        const sideCost = document.getElementById('live_cost_val');

        const prevMetaText = currentSelectedVehicleMeta
            ? ` (السائق: ${currentSelectedVehicleMeta.prev_trip_driver} · المحطة: ${currentSelectedVehicleMeta.prev_trip_station} · الوجهة: ${currentSelectedVehicleMeta.prev_trip_destination})`
            : '';

        if (curOdo > 0 && prevOdo > 0 && curOdo > prevOdo) {
            const dist = curOdo - prevOdo;
            const distStr = dist.toLocaleString('en-US') + ' كم';
            if (formDist) formDist.innerText = distStr;
            if (sideDist) sideDist.innerText = distStr;

            if (isFull) {
                // 1) تفويلة كاملة: إقفال الرحلة السابقة وتسجيل المسافة والكفاءة عليها
                if (qty > 0) {
                    const kmPerLiter = (dist / qty).toFixed(2);
                    const lPer100 = ((qty / dist) * 100).toFixed(1);

                    if (formRate) {
                        formRate.innerHTML = `<span class="text-success fw-bold">${kmPerLiter} كم / لتر</span> <span class="text-muted fw-normal" style="font-size:0.75rem;">(${lPer100} لتر/100كم)</span>`;
                    }
                    if (sideRate) sideRate.innerText = kmPerLiter + ' كم/لتر';

                    if (fuelCostOnly > 0) {
                        const costVal = (fuelCostOnly / dist).toFixed(2);
                        if (formCost) formCost.innerText = costVal + ' ج/كم';
                        if (sideCost) sideCost.innerText = costVal + ' ج/كم';
                    } else {
                        if (formCost) formCost.innerText = '— ج/كم';
                        if (sideCost) sideCost.innerText = '—';
                    }

                    if (formStatus) {
                        formStatus.innerHTML = `<span class="text-success fw-bold"><i class="fa fa-circle-check me-1"></i> سيتم تسجيل (${dist.toLocaleString('en-US')} كم) وكفاءة (${kmPerLiter} كم/لتر بـ ${qty} لتر) على الرحلة السابقة${prevMetaText}، وتبدأ رحلة اليوم من عداد ${curOdo.toLocaleString('en-US')} كم.</span>`;
                    }
                } else {
                    if (formRate) formRate.innerText = '— كم/لتر';
                    if (sideRate) sideRate.innerText = '—';
                    if (formCost) formCost.innerText = '— ج/كم';
                    if (sideCost) sideCost.innerText = '—';
                    if (formStatus) {
                        formStatus.innerHTML = `<i class="fa fa-info-circle text-primary me-1"></i> أدخل عدد اللترات لإظهار كفاءة الاستهلاك وتكلفة الكيلومتر للرحلة السابقة.`;
                    }
                }
            } else {
                // 2) تزويد جزئي (مش فل تانك):
                if (formRate) {
                    formRate.innerHTML = `<span class="badge bg-warning bg-opacity-20 text-dark fw-bold px-2 py-1" style="font-size:0.78rem;"><i class="fa fa-clock me-1"></i> يضاف للرحلة الجارية</span>`;
                }
                if (sideRate) sideRate.innerText = 'في انتظار التفويلة';
                if (formCost) formCost.innerText = '—';
                if (sideCost) sideCost.innerText = '—';

                if (formStatus) {
                    formStatus.innerHTML = `<span class="text-warning-emphasis fw-bold"><i class="fa fa-triangle-exclamation text-warning me-1"></i> تزويد جزئي: سيتم إضافة ${qty} لتر للرحلة الجارية لحين إقفالها بالتفويلة الكاملة القادمة.</span>`;
                }
            }
        } else if (curOdo > 0 && prevOdo === 0) {
            if (formDist) formDist.innerText = 'بداية رحلة';
            if (formRate) formRate.innerText = 'تُحسب بالتفويلة القادمة';
            if (formCost) formCost.innerText = '— ج/كم';
            if (sideDist) sideDist.innerText = 'بداية رحلة';
            if (sideRate) sideRate.innerText = 'رحلة جارية';
            if (sideCost) sideCost.innerText = '—';
            if (formStatus) {
                formStatus.innerHTML = `<span class="text-primary fw-bold"><i class="fa fa-flag-checkered me-1"></i> بداية أول رحلة مسجلة بالعداد (${curOdo.toLocaleString('en-US')} كم) — ستُحسب المسافة والكفاءة لهذه الرحلة في التفويلة القادمة.</span>`;
            }
        } else if (curOdo > 0 && prevOdo > 0 && curOdo <= prevOdo) {
            if (formDist) formDist.innerText = '— كم';
            if (formRate) formRate.innerText = '— كم/لتر';
            if (formCost) formCost.innerText = '— ج/كم';
            if (sideDist) sideDist.innerText = '— كم';
            if (sideRate) sideRate.innerText = '—';
            if (sideCost) sideCost.innerText = '—';
            if (formStatus) {
                formStatus.innerHTML = `<span class="text-danger fw-bold"><i class="fa fa-triangle-exclamation me-1"></i> تنبيه: العداد الحالي (${curOdo}) يجب أن يكون أكبر من العداد السابق (${prevOdo})</span>`;
            }
        } else {
            if (formDist) formDist.innerText = '— كم';
            if (formRate) formRate.innerText = isFull ? '— كم/لتر' : 'في انتظار التفويلة';
            if (formCost) formCost.innerText = '— ج/كم';
            if (sideDist) sideDist.innerText = '— كم';
            if (sideRate) sideRate.innerText = '—';
            if (sideCost) sideCost.innerText = '—';
            if (formStatus) {
                formStatus.innerHTML = `<i class="fa fa-info-circle text-primary me-1"></i> أدخل قراءة العداد الحالية واللترات لإقفال الرحلة السابقة وحساب كفاءتها فورياً.`;
            }
        }
    }

    // ربط حسابات الرحلة بحسابات الشاشة العامة
    const originalCalc = calculateGasStation;
    calculateGasStation = function() {
        originalCalc();
        calculateDrivvoTrip();
    };

    // فلترة سريعة فورية لسجل العمليات في تابة الإحصائيات
    function filterDrivvoLog(query) {
        query = (query || '').trim().toLowerCase();
        const rows = document.querySelectorAll('#drivvoLogTable tbody tr.drivvo-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = (row.dataset.search || '').toLowerCase();
            if (!query || searchData.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('drivvoFilteredCount');
        if (countBadge) countBadge.innerText = visibleCount + ' حركة';
    }

    // تفعيل التابة الصحيحة بناءً على رابط الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        renderBestFourCategories();
        const compTabBtnEl = document.getElementById('tab-comparison-btn');
        if (compTabBtnEl) {
            compTabBtnEl.addEventListener('shown.bs.tab', function () {
                initComparisonTab();
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'comparison') {
            const compTabBtn = document.getElementById('tab-comparison-btn');
            if (compTabBtn) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(compTabBtn);
                tabInstance.show();
                initComparisonTab();
            }
        } else if (urlParams.get('tab') === 'analytics' || ['filter_company','filter_station','filter_car','filter_driver','filter_from','filter_to'].some(k => urlParams.get(k))) {
            const analyticsTabBtn = document.getElementById('tab-analytics-btn');
            if (analyticsTabBtn) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(analyticsTabBtn);
                tabInstance.show();
            }
        }

        // فحص العداد الأولي إذا كان هناك قيمة قديمة للشاحنة
        const initialCar = document.getElementById('car_number_input')?.value || document.getElementById('driver_car_input')?.value;
        if (initialCar) checkVehicleOdometerByCar(initialCar);

        // تفعيل ترقيم وتصفح تقرير كفاءة الشاحنات (30 بالصفحة مع شريط تنقل)
        initFleetPagination();
    });

    // ── أدوات خرائط Google وتحديد الموقع الجغرافي لخط السير ──
    let lastSelectedPlace = 'مصر';

    function openGoogleMapsModal() {
        const currentText = document.getElementById('trip_route_input')?.value.trim();
        if (currentText) {
            document.getElementById('gmaps_search_input').value = currentText;
            searchLocationOnMap();
        }
        const modalEl = document.getElementById('googleMapsModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function searchLocationOnMap() {
        const query = document.getElementById('gmaps_search_input').value.trim();
        if (!query) return;
        lastSelectedPlace = query;
        const iframe = document.getElementById('gmaps_iframe');
        const selectedNameEl = document.getElementById('gmaps_selected_name');
        if (selectedNameEl) selectedNameEl.innerText = query;
        if (iframe) {
            iframe.src = `https://maps.google.com/maps?q=${encodeURIComponent(query)}&t=&z=13&ie=UTF8&iwloc=&output=embed`;
        }
    }

    function appendRouteDestination(name) {
        name = (name || '').trim();
        if (!name) return;
        const input = document.getElementById('trip_route_input');
        if (!input) return;
        let val = input.value.trim();
        if (!val) {
            input.value = name;
        } else {
            if (!val.includes(name)) {
                input.value = val + ' ⬅️ ' + name;
            }
        }
        input.focus();
    }

    function clearTripRoute() {
        const input = document.getElementById('trip_route_input');
        if (input) {
            input.value = '';
            input.focus();
        }
    }

    function filterDestCategory(cat, btnEl) {
        document.querySelectorAll('#dest_chips_container .dest-chip').forEach(btn => {
            if (cat === 'all' || btn.getAttribute('data-category') === cat) {
                btn.style.display = 'inline-block';
            } else {
                btn.style.display = 'none';
            }
        });
        document.querySelectorAll('#dest_category_tabs .btn').forEach(b => {
            b.classList.remove('btn-dark');
            b.classList.add('btn-outline-secondary');
        });
        if (btnEl) {
            btnEl.classList.remove('btn-outline-secondary');
            btnEl.classList.add('btn-dark');
        }
    }

    // ── التحكم السريع في الفترة وتصدير تقارير PDF الاحترافية ──
    const ACTIVE_FILTER_PERIOD  = @json($filterPeriod ?? 'month');
    const ACTIVE_FILTER_FROM    = @json($resolvedFrom ?? '');
    const ACTIVE_FILTER_TO      = @json($resolvedTo ?? '');
    const ACTIVE_FILTER_COMPANY = @json(request('filter_company', ''));
    const ACTIVE_PERIOD_LABEL   = @json($periodLabel ?? 'آخر شهر (30 يوماً)');
    const DEFAULT_COMPANY_ID    = @json($defaultCompany->id ?? '');

    function setAnalyticsPeriod(period) {
        const periodInp = document.getElementById('analytics_period_input');
        const fromInp   = document.getElementById('analytics_filter_from');
        const toInp     = document.getElementById('analytics_filter_to');
        const form      = document.getElementById('drivvoFilterForm');
        if (periodInp) periodInp.value = period;
        if (fromInp) fromInp.value = '';
        if (toInp) toInp.value = '';
        if (form) form.submit();
    }

    function onCustomAnalyticsDateChange() {
        const periodInp = document.getElementById('analytics_period_input');
        if (periodInp) periodInp.value = 'custom';
    }

    function openGasPdfExport(mode, customPeriod) {
        mode = mode || 'analytics';
        const params = new URLSearchParams();
        params.set('mode', mode);

        const periodVal  = customPeriod || document.getElementById('analytics_period_input')?.value || (mode === 'company_report' ? 'week' : 'month');
        const companyVal = document.getElementById('analytics_filter_company')?.value || (mode === 'company_report' ? DEFAULT_COMPANY_ID : '');
        const stationVal = document.getElementById('analytics_filter_station')?.value || '';
        const carVal     = document.getElementById('analytics_filter_car')?.value || '';
        const driverVal  = document.getElementById('analytics_filter_driver')?.value || '';
        const fromVal    = document.getElementById('analytics_filter_from')?.value || '';
        const toVal      = document.getElementById('analytics_filter_to')?.value || '';

        const activeCmpType = (typeof compareMode !== 'undefined' && compareMode) ? compareMode : 'vehicle';

        params.set('period', periodVal);
        if (companyVal) params.set('filter_company', companyVal);
        if (stationVal && !(mode === 'comparisons' && activeCmpType === 'station')) {
            params.set('filter_station', stationVal);
        }
        if (carVal && mode !== 'comparisons')    params.set('filter_car', carVal);
        if (driverVal && mode !== 'comparisons') params.set('filter_driver', driverVal);
        if (periodVal === 'custom') {
            if (fromVal) params.set('filter_from', fromVal);
            if (toVal)   params.set('filter_to', toVal);
        }

        if (mode === 'comparisons') {
            const critVal = (typeof currentBestCriterion !== 'undefined' && currentBestCriterion) ? currentBestCriterion : 'best_efficiency';
            const selAVal = document.getElementById('compare_select_a')?.value || '';
            const selBVal = document.getElementById('compare_select_b')?.value || '';
            params.set('compare_type', activeCmpType);
            params.set('criterion', critVal);
            if (selAVal) params.set('vehicle_a', selAVal);
            if (selBVal) params.set('vehicle_b', selBVal);
        }

        window.open(`{{ route('gas.export_report') }}?` + params.toString(), '_blank');
    }

    function printDrivvoReport() {
        openGasPdfExport('analytics');
    }

    // ── فتح نافذة رادار كشف الشبهات والهدر ──
    function openRadarModal() {
        const modalEl = document.getElementById('suspiciousRadarModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    // ── فلترة وبحث فوري في تنبيهات رادار الشبهات ──
    function filterRadarAlerts(query) {
        query = (query || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.radar-alert-col');
        let visibleCount = 0;
        cards.forEach(card => {
            const searchData = (card.dataset.search || '').toLowerCase();
            const text = (card.innerText || '').toLowerCase();
            if (!query || searchData.includes(query) || text.includes(query)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noMatchesEl = document.getElementById('radarAlertsNoMatches');
        if (noMatchesEl) {
            noMatchesEl.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
        }

        const badge = document.getElementById('radarAlertsCountBadge');
        if (badge) {
            badge.innerText = query ? `${visibleCount} مطابق` : `${cards.length} تنبيه`;
        }
    }

    // ── فتح نافذة ضبط معايير الرادار ونسبة السماح ──
    function openRadarSettingsModal() {
        const modalEl = document.getElementById('radarSettingsModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            updateRadarPreview();
        }
    }

    function updateRadarPreview() {
        const minEffVal = document.getElementById('radar_inp_min_eff')?.value;
        const maxCostVal = document.getElementById('radar_inp_max_cost')?.value;
        const tolVal = document.getElementById('radar_inp_tolerance')?.value;

        const minEff = (minEffVal !== undefined && minEffVal !== '' && !isNaN(parseFloat(minEffVal))) ? parseFloat(minEffVal) : 4.0;
        const maxCost = (maxCostVal !== undefined && maxCostVal !== '' && !isNaN(parseFloat(maxCostVal))) ? parseFloat(maxCostVal) : 7.0;
        const tol = (tolVal !== undefined && tolVal !== '' && !isNaN(parseFloat(tolVal))) ? parseFloat(tolVal) : 0.0;

        const effVal = (minEff * (1 - (tol / 100))).toFixed(2);
        const costVal = (maxCost * (1 + (tol / 100))).toFixed(2);

        const prevEffEl = document.getElementById('preview_eff_val');
        const prevCostEl = document.getElementById('preview_cost_val');
        if (prevEffEl) prevEffEl.innerText = effVal + ' كم/لتر (أقل بـ ' + tol + '%)';
        if (prevCostEl) prevCostEl.innerText = costVal + ' ج/كم (أعلى بـ ' + tol + '%)';
    }

    // ════════════════════════════════════════════════════════════
    // ⚖️ VEHICLE COMPARISON ENGINE (محرك المقارنة والشارتات التفاعلية)
    // ════════════════════════════════════════════════════════════
    let compareEfficiencyChartInstance = null;
    let compareCostChartInstance = null;
    let compareVolumeChartInstance = null;
    let bestCategoryCharts = { drivers: null, cars: null, destinations: null, stations: null };
    let compareOptionsDataA = [];
    let compareOptionsDataB = [];

    // 🎨 باليت ألوان مميزة لكل طرف في الشارتات والقوائم
    const PARTY_COLOR_PALETTE = [
        '#2563eb', // أزرق ملكي
        '#ea580c', // برتقالي ساطع
        '#10b981', // أخضر زمردي
        '#8b5cf6', // بنفسجي
        '#f59e0b', // ذهبي كهرماني
        '#06b6d4', // سماوي
        '#ec4899', // وردي
        '#6366f1'  // نيلي
    ];
    const PARTY_A_COLOR = '#2563eb';
    const PARTY_B_COLOR = '#ea580c';

    // 🔀 نوع المقارنة الحالي: vehicle | station | destination
    let compareMode = 'vehicle';
    let vehicleOptionsCacheA = null;
    let vehicleOptionsCacheB = null;

    const COMPARE_DRIVERS_REPORT      = @json($detailedDriversReport ?? []);
    const COMPARE_CARS_REPORT         = @json($detailedCarsReport ?? []);
    const COMPARE_STATION_OPTIONS     = @json($compareStationOptions ?? []);
    const COMPARE_DESTINATION_OPTIONS = @json($compareDestinationOptions ?? []);

    let currentBestCriterion = 'best_efficiency'; // 'best_efficiency' | 'worst_efficiency' | 'cheapest_cost_km' | 'highest_cost_km'
    const RADAR_EFFECTIVE_MIN_EFF = Number(@json($effectiveMinEff ?? 3.6));
    const RADAR_EFFECTIVE_MAX_COST = Number(@json($effectiveMaxCostKm ?? 7.7));

    function getSortedCategoryItems(category, criterion) {
        criterion = criterion || currentBestCriterion;
        const isEffCriterion = (criterion === 'best_efficiency' || criterion === 'worst_efficiency');
        const isWorstOrExpensive = (criterion === 'worst_efficiency' || criterion === 'highest_cost_km');

        let raw = [];
        if (category === 'drivers') {
            raw = (COMPARE_DRIVERS_REPORT || []).map(d => ({
                key: d.driver_name,
                name: d.driver_name,
                sub: d.cars_list ? `🚗 ${d.cars_list}` : '',
                total_trips: Number(d.total_trips || 0),
                completed_trips: Number(d.completed_trips || 0),
                total_dist: Number(d.total_dist || 0),
                total_liters: Number(d.total_liters || 0),
                total_cost: Number(d.total_cost || 0),
                avg_efficiency: Number(d.avg_efficiency || 0),
                avg_cost_km: Number(d.avg_cost_km || 0),
                compare_mode: 'vehicle'
            }));
        } else if (category === 'cars') {
            raw = (COMPARE_CARS_REPORT || []).map(c => ({
                key: c.car_number,
                name: c.car_number,
                sub: c.drivers_list ? `👤 ${c.drivers_list}` : '',
                total_trips: Number(c.total_trips || 0),
                completed_trips: Number(c.completed_trips || 0),
                total_dist: Number(c.total_dist || 0),
                total_liters: Number(c.total_liters || 0),
                total_cost: Number(c.total_cost || 0),
                avg_efficiency: Number(c.avg_efficiency || 0),
                avg_cost_km: Number(c.avg_cost_km || 0),
                compare_mode: 'vehicle'
            }));
        } else if (category === 'destinations') {
            raw = (COMPARE_DESTINATION_OPTIONS || []).map(o => ({
                key: o.name,
                name: o.name,
                sub: `${Number(o.total_liters || 0).toLocaleString()} لتر`,
                total_trips: Number(o.total_trips || 0),
                completed_trips: Number(o.completed_trips || 0),
                total_dist: Number(o.total_dist || 0),
                total_liters: Number(o.total_liters || 0),
                total_cost: Number(o.total_cost || 0),
                avg_efficiency: Number(o.avg_efficiency || 0),
                avg_cost_km: Number(o.avg_cost_km || 0),
                compare_mode: 'destination'
            }));
        } else if (category === 'stations') {
            raw = (COMPARE_STATION_OPTIONS || []).map(s => ({
                key: s.name,
                name: s.name,
                sub: `${Number(s.total_liters || 0).toLocaleString()} لتر`,
                total_trips: Number(s.total_trips || 0),
                completed_trips: Number(s.completed_trips || 0),
                total_dist: Number(s.total_dist || 0),
                total_liters: Number(s.total_liters || 0),
                total_cost: Number(s.total_cost || 0),
                avg_efficiency: Number(s.avg_efficiency || 0),
                avg_cost_km: Number(s.avg_cost_km || 0),
                compare_mode: 'station'
            }));
        }

        const directMeasured = raw.filter(item => isEffCriterion ? item.avg_efficiency > 0 : item.avg_cost_km > 0);
        const unmeasured = raw.filter(item => isEffCriterion ? item.avg_efficiency <= 0 : item.avg_cost_km <= 0);

        directMeasured.sort((a, b) => {
            if (criterion === 'best_efficiency') {
                return b.avg_efficiency - a.avg_efficiency; // الأعلى كم/لتر أولاً
            } else if (criterion === 'worst_efficiency') {
                return a.avg_efficiency - b.avg_efficiency; // الأقل كم/لتر أولاً
            } else if (criterion === 'cheapest_cost_km') {
                return a.avg_cost_km - b.avg_cost_km; // الأرخص ج/كم أولاً
            } else {
                return b.avg_cost_km - a.avg_cost_km; // الأغلى ج/كم أولاً
            }
        });

        unmeasured.sort((a, b) => isWorstOrExpensive
            ? (b.total_liters - a.total_liters)
            : (a.total_liters - b.total_liters)
        );

        const orderedList = [...directMeasured, ...unmeasured];

        return {
            measured: directMeasured,
            unmeasured: unmeasured,
            all: orderedList
        };
    }

    function setBestRankingMode(criterion) {
        const validModes = ['best_efficiency', 'worst_efficiency', 'cheapest_cost_km', 'highest_cost_km'];
        currentBestCriterion = validModes.includes(criterion) ? criterion : 'best_efficiency';
        const isDangerMode = (currentBestCriterion === 'worst_efficiency' || currentBestCriterion === 'highest_cost_km');

        document.querySelectorAll('#best_criterion_btn_group button').forEach(btn => {
            const isAct = btn.dataset.criterion === currentBestCriterion;
            if (!isAct) {
                btn.className = 'btn-report-solid';
            } else {
                btn.className = isDangerMode ? 'btn-report-solid active-danger' : 'btn-report-solid active-blue';
            }
        });

        const titleEl = document.getElementById('best_board_title_text');
        const iconEl = document.getElementById('best_board_icon');
        const titlesMap = {
            best_efficiency:  'ترتيب وشارتات الأفضل كفاءة في الأسطول (كم/ل)',
            worst_efficiency: 'ترتيب وشارتات الأسوأ كفاءة في الأسطول (كم/ل)',
            cheapest_cost_km: 'ترتيب وشارتات الأرخص تكلفة في الأسطول (ج/كم)',
            highest_cost_km:  'ترتيب وشارتات الأغلى تكلفة في الأسطول (ج/كم)'
        };
        if (titleEl) titleEl.innerText = titlesMap[currentBestCriterion] || titlesMap.best_efficiency;
        if (iconEl) {
            iconEl.className = isDangerMode ? 'fa fa-triangle-exclamation text-danger' : 'fa fa-trophy text-warning';
        }

        renderBestFourCategories();
    }

    function formatItemRankMetric(item, isEff) {
        const metricVal = Number(isEff ? (item.avg_efficiency || 0) : (item.avg_cost_km || 0));
        const unitLabel = isEff ? 'كم/ل' : 'ج/كم';
        const hasReading = metricVal > 0;

        return {
            rawNumber: Number(metricVal.toFixed(2)),
            badgeText: `${metricVal.toFixed(2)} ${unitLabel}`,
            subText: hasReading
                ? `${item.completed_trips} رحلة مقفلة · ${item.total_dist.toLocaleString()} كم`
                : `بانتظار قراءة عداد ثانية · (${item.total_trips} رحلة)`
        };
    }

    function renderBestCategoryMiniChart(catId, canvasId, all, isEff) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        if (bestCategoryCharts[catId]) {
            bestCategoryCharts[catId].destroy();
            bestCategoryCharts[catId] = null;
        }

        // دائماً نعرض أول 3 مراكز في الشارت بوحدة موحدة تماماً (كم/ل أو ج/كم)
        const topThree = all.slice(0, 3);
        if (!topThree.length) return;

        const labels = topThree.map((item, idx) => {
            const clean = String(item.name || '').trim();
            const shortName = clean.length > 10 ? clean.substring(0, 10) + '..' : clean;
            return `(${idx + 1}) ${shortName}`;
        });

        const formattedTop = topThree.map(item => formatItemRankMetric(item, isEff));

        const customLabels = [];
        const tooltipDetails = [];
        const values = formattedTop.map((f, idx) => {
            const item = topThree[idx];
            customLabels.push(f.badgeText);
            tooltipDetails.push(`المركز ${idx + 1} (${item.name}): ${f.badgeText} — ${f.subText}`);
            return f.rawNumber;
        });

        const colors = topThree.map((_, idx) => PARTY_COLOR_PALETTE[idx % PARTY_COLOR_PALETTE.length]);

        bestCategoryCharts[catId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: isEff ? 'كم/ل' : 'ج/كم',
                    data: values,
                    customLabels: customLabels,
                    tooltipDetails: tooltipDetails,
                    backgroundColor: colors,
                    borderRadius: 6,
                    barPercentage: 0.62,
                    categoryPercentage: 0.75,
                    maxBarThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 22, bottom: 0, left: 6, right: 6 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const ds = context.dataset;
                                if (ds.tooltipDetails && ds.tooltipDetails[context.dataIndex]) {
                                    return ' ' + ds.tooltipDetails[context.dataIndex];
                                }
                                return ` ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { family: 'IBM Plex Sans Arabic', size: 9 },
                            maxTicksLimit: 4
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { family: 'IBM Plex Sans Arabic', size: 10, weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            },
            plugins: [simpleBarValuePlugin]
        });
    }

    function renderBestFourCategories() {
        const isEff = (currentBestCriterion === 'best_efficiency' || currentBestCriterion === 'worst_efficiency');
        const categoriesMeta = [
            { id: 'drivers',      containerId: 'best_list_drivers',      canvasId: 'bestChart_drivers' },
            { id: 'cars',         containerId: 'best_list_cars',         canvasId: 'bestChart_cars' },
            { id: 'destinations', containerId: 'best_list_destinations', canvasId: 'bestChart_destinations' },
            { id: 'stations',     containerId: 'best_list_stations',     canvasId: 'bestChart_stations' }
        ];

        categoriesMeta.forEach(cat => {
            const { all } = getSortedCategoryItems(cat.id, currentBestCriterion);
            renderBestCategoryMiniChart(cat.id, cat.canvasId, all, isEff);

            const listEl = document.getElementById(cat.containerId);
            if (!listEl) return;
            if (!all.length) {
                listEl.innerHTML = `<div class="text-center text-muted py-4 small">لا توجد بيانات</div>`;
                return;
            }

            const topThree = all.slice(0, 3);
            const restItems = all.slice(3, 6);
            const maxRaw = Math.max(...topThree.map(i => formatItemRankMetric(i, isEff).rawNumber), 1);

            let html = '';
            topThree.forEach((item, idx) => {
                const rank = idx + 1;
                const partyColor = PARTY_COLOR_PALETTE[idx % PARTY_COLOR_PALETTE.length];
                const info = formatItemRankMetric(item, isEff);
                const barPct = info.rawNumber > 0 ? Math.min(100, Math.max(22, Math.round((info.rawNumber / maxRaw) * 100))) : 0;
                const safeKey = String(item.key).replace(/'/g, "\\'");

                html += `
                    <div class="px-2 py-2 border-bottom" style="border-right: 3px solid ${partyColor}; background:${rank === 1 ? '#f8fafc' : '#fff'};">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2 text-truncate" style="min-width:0;">
                                <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs"
                                      style="width:22px; height:22px; font-size:0.72rem; background:${partyColor}; color:#fff;">
                                    ${rank}
                                </span>
                                <div class="text-truncate">
                                    <div class="fw-bold text-dark text-truncate" style="font-size:0.8rem;" title="${item.name}">${item.name}</div>
                                    <div class="text-muted" style="font-size:0.68rem;">
                                        ${info.subText}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <span class="badge fw-bold" style="background:${partyColor}18; color:${partyColor}; border:1px solid ${partyColor}40; font-size:0.71rem;" dir="ltr">${info.badgeText}</span>
                                <button type="button" class="btn btn-sm fw-bold py-0 px-2 text-white shadow-xs" style="font-size:0.66rem; background:${PARTY_A_COLOR}; border-radius:5px;" onclick="selectRankedItemForCompare('${cat.id}', '${safeKey}', 'a')" title="اختيار كطرف (أ)">أ</button>
                                <button type="button" class="btn btn-sm fw-bold py-0 px-2 text-white shadow-xs" style="font-size:0.66rem; background:${PARTY_B_COLOR}; border-radius:5px;" onclick="selectRankedItemForCompare('${cat.id}', '${safeKey}', 'b')" title="اختيار كطرف (ب)">ب</button>
                            </div>
                        </div>
                        <div class="progress mt-1" style="height: 4px; background: #f1f5f9;">
                            <div class="progress-bar" style="width: ${barPct}%; background: ${partyColor};"></div>
                        </div>
                    </div>
                `;
            });

            if (restItems.length > 0) {
                restItems.forEach((item, rIdx) => {
                    const rank = rIdx + 4;
                    const partyColor = PARTY_COLOR_PALETTE[(rank - 1) % PARTY_COLOR_PALETTE.length];
                    const info = formatItemRankMetric(item, isEff);
                    const safeKey = String(item.key).replace(/'/g, "\\'");
                    html += `
                        <div class="px-2 py-1 border-bottom d-flex align-items-center justify-content-between gap-2" style="border-right: 3px solid ${partyColor};">
                            <div class="text-truncate d-flex align-items-center gap-1">
                                <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center" style="width:18px; height:18px; font-size:0.62rem; background:${partyColor}22; color:${partyColor}; border:1px solid ${partyColor}55;">${rank}</span>
                                <span class="text-dark fw-semibold text-truncate" style="font-size:0.75rem;">${item.name}</span>
                                <small class="text-muted ms-1" style="font-size:0.64rem;">(${info.badgeText})</small>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <button type="button" class="btn btn-sm fw-bold py-0 px-2 text-white" style="font-size:0.63rem; background:${PARTY_A_COLOR}; border-radius:5px;" onclick="selectRankedItemForCompare('${cat.id}', '${safeKey}', 'a')">أ</button>
                                <button type="button" class="btn btn-sm fw-bold py-0 px-2 text-white" style="font-size:0.63rem; background:${PARTY_B_COLOR}; border-radius:5px;" onclick="selectRankedItemForCompare('${cat.id}', '${safeKey}', 'b')">ب</button>
                            </div>
                        </div>
                    `;
                });
            }

            listEl.innerHTML = html;
        });
    }

    function selectRankedItemForCompare(category, itemKey, side) {
        const targetMode = category === 'stations' ? 'station' : (category === 'destinations' ? 'destination' : 'vehicle');
        if (compareMode !== targetMode) {
            setCompareMode(targetMode);
        }
        const sel = document.getElementById(side === 'a' ? 'compare_select_a' : 'compare_select_b');
        if (sel) {
            sel.value = itemKey;
            triggerVehicleComparison();
            document.getElementById('head_to_head_comparison_panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function compareTopTwoInCategory(category) {
        const { all } = getSortedCategoryItems(category, currentBestCriterion);
        if (all.length < 2) {
            alert('يرجى توفر عنصرين على الأقل في هذه الفئة لإجراء المقارنة.');
            return;
        }
        const targetMode = category === 'stations' ? 'station' : (category === 'destinations' ? 'destination' : 'vehicle');
        if (compareMode !== targetMode) {
            setCompareMode(targetMode);
        }
        const selA = document.getElementById('compare_select_a');
        const selB = document.getElementById('compare_select_b');
        if (selA && selB) {
            selA.value = all[0].key;
            selB.value = all[1].key;
            triggerVehicleComparison();
            document.getElementById('head_to_head_comparison_panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function autoPickBestForActiveMode(criterion) {
        setBestRankingMode(criterion);
        const category = compareMode === 'station' ? 'stations' : (compareMode === 'destination' ? 'destinations' : 'drivers');
        compareTopTwoInCategory(category);
    }

    const COMPARE_MODE_META = {
        vehicle: {
            header: 'المقارنة المباشرة والشارتات (الشاحنات والسائقين)',
            labelA: 'الطرف الأول (اللون الأزرق)', labelB: 'الطرف الثاني (اللون البرتقالي)',
            hint: 'بحث بالشاحنة أو السائق',
            placeholder: '🔍 بحث سريع...',
            emptyA: '— اختر الطرف الأول (أ) —', emptyB: '— اختر الطرف الثاني (ب) —',
            icon: 'fa-truck', noun: 'الشاحنتين', sameErr: 'يرجى اختيار سائقين أو شاحنتين مختلفتين للمقارنة.',
            breakdownNoun: 'وجهات', breakdownTitle: 'مقارنة الوجهات وخطوط السير المشتركة',
            breakdownSub: '',
            breakdownTh: 'الوجهة / خط السير', driversLabel: 'السائقون:'
        },
        station: {
            header: 'المقارنة المباشرة والشارتات (المحطات)',
            labelA: 'المحطة الأولى (اللون الأزرق)', labelB: 'المحطة الثانية (اللون البرتقالي)',
            hint: 'بحث باسم المحطة',
            placeholder: '🔍 بحث سريع...',
            emptyA: '— اختر المحطة الأولى (أ) —', emptyB: '— اختر المحطة الثانية (ب) —',
            icon: 'fa-gas-pump', noun: 'المحطتين', sameErr: 'يرجى اختيار محطتين مختلفتين للمقارنة.',
            breakdownNoun: 'وجهات', breakdownTitle: 'مقارنة الوجهات المشتركة بين المحطتين',
            breakdownSub: '',
            breakdownTh: 'الوجهة / خط السير', driversLabel: 'السائقون:'
        },
        destination: {
            header: 'المقارنة المباشرة والشارتات (الوجهات)',
            labelA: 'الوجهة الأولى (اللون الأزرق)', labelB: 'الوجهة الثانية (اللون البرتقالي)',
            hint: 'بحث باسم الوجهة',
            placeholder: '🔍 بحث سريع...',
            emptyA: '— اختر الوجهة الأولى (أ) —', emptyB: '— اختر الوجهة الثانية (ب) —',
            icon: 'fa-route', noun: 'الوجهتين', sameErr: 'يرجى اختيار وجهتين مختلفتين للمقارنة.',
            breakdownNoun: 'محطات', breakdownTitle: 'مقارنة المحطات المشتركة بين الوجهتين',
            breakdownSub: '',
            breakdownTh: 'محطة الوقود', driversLabel: 'السائقون:'
        }
    };

    function buildModeOptions(list, mode, emptyText) {
        const groupLabel = mode === 'station' ? 'المحطات' : 'الوجهات';
        const opts = [{ value: '', text: emptyText, group: '', search: '' }];
        (list || []).forEach(o => {
            if (!o || !o.name) return;
            const effTxt = Number(o.avg_efficiency || 0) > 0 ? ` · ${Number(o.avg_efficiency).toFixed(2)} كم/ل` : '';
            opts.push({
                value: o.name,
                text: `${o.name} (${o.total_trips} عملية${effTxt})`,
                group: groupLabel,
                search: String(o.name).toLowerCase()
            });
        });
        return opts;
    }

    function setCompareMode(mode) {
        if (!COMPARE_MODE_META[mode]) mode = 'vehicle';
        initCompareSelectOptions();

        if (vehicleOptionsCacheA === null) vehicleOptionsCacheA = compareOptionsDataA.slice();
        if (vehicleOptionsCacheB === null) vehicleOptionsCacheB = compareOptionsDataB.slice();

        compareMode = mode;
        const meta = COMPARE_MODE_META[mode];

        document.querySelectorAll('#compare_mode_group button').forEach(btn => {
            const active = btn.dataset.mode === mode;
            btn.className = active
                ? 'btn-report-solid active-blue'
                : 'btn-report-solid';
        });

        const setText = (id, txt) => { const el = document.getElementById(id); if (el) el.innerText = txt; };
        setText('cmp_header_title', meta.header);
        setText('cmp_label_a', meta.labelA);
        setText('cmp_label_b', meta.labelB);
        setText('cmp_dest_panel_title', meta.breakdownTitle);
        setText('cmp_dest_panel_sub', meta.breakdownSub);
        setText('cmp_dest_th_loc', meta.breakdownTh);
        document.querySelectorAll('.cmp-search-hint').forEach(el => el.innerText = meta.hint);
        document.querySelectorAll('.cmp-drivers-label').forEach(el => el.innerText = meta.driversLabel);
        document.querySelectorAll('.cmp-side-icon').forEach(el => {
            el.classList.remove('fa-truck', 'fa-gas-pump', 'fa-route');
            el.classList.add(meta.icon);
        });
        document.querySelectorAll('.cmp-odometer-row').forEach(el => el.style.setProperty('display', mode === 'vehicle' ? '' : 'none', 'important'));
        ['cmp_a_btn_drivvo', 'cmp_b_btn_drivvo'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = mode === 'vehicle' ? '' : 'none';
        });
        ['a', 'b'].forEach(side => {
            const inp = document.getElementById('search_compare_' + side);
            if (inp) { inp.value = ''; inp.placeholder = meta.placeholder; }
            const qb = document.getElementById('compare_quick_matches_' + side);
            if (qb) qb.innerHTML = '';
        });

        if (mode === 'vehicle') {
            compareOptionsDataA = vehicleOptionsCacheA.slice();
            compareOptionsDataB = vehicleOptionsCacheB.slice();
        } else {
            const src = mode === 'station' ? COMPARE_STATION_OPTIONS : COMPARE_DESTINATION_OPTIONS;
            compareOptionsDataA = buildModeOptions(src, mode, meta.emptyA);
            compareOptionsDataB = buildModeOptions(src, mode, meta.emptyB);
        }

        const selA = document.getElementById('compare_select_a');
        const selB = document.getElementById('compare_select_b');
        if (selA) selA.value = '';
        if (selB) selB.value = '';
        filterCompareSelect('a', '');
        filterCompareSelect('b', '');

        const valuesList = compareOptionsDataA.filter(o => o.value).map(o => o.value);
        if (selA && valuesList[0] !== undefined) selA.value = valuesList[0];
        if (selB && valuesList[1] !== undefined) selB.value = valuesList[1];

        triggerVehicleComparison();
    }

    function initCompareSelectOptions() {
        const selectA = document.getElementById('compare_select_a');
        const selectB = document.getElementById('compare_select_b');
        if (selectA && !compareOptionsDataA.length) {
            compareOptionsDataA = Array.from(selectA.options).map(o => ({
                value: o.value,
                text: o.text,
                group: (o.parentElement && o.parentElement.tagName === 'OPTGROUP') ? o.parentElement.label : '',
                search: o.dataset.search || o.text.toLowerCase()
            }));
        }
        if (selectB && !compareOptionsDataB.length) {
            compareOptionsDataB = Array.from(selectB.options).map(o => ({
                value: o.value,
                text: o.text,
                group: (o.parentElement && o.parentElement.tagName === 'OPTGROUP') ? o.parentElement.label : '',
                search: o.dataset.search || o.text.toLowerCase()
            }));
        }
    }

    function filterCompareSelect(side, query) {
        initCompareSelectOptions();
        query = (query || '').trim().toLowerCase();
        const sel = document.getElementById(side === 'a' ? 'compare_select_a' : 'compare_select_b');
        const quickBox = document.getElementById(side === 'a' ? 'compare_quick_matches_a' : 'compare_quick_matches_b');
        const allOptions = side === 'a' ? compareOptionsDataA : compareOptionsDataB;
        if (!sel || !allOptions.length) return;

        const currentVal = sel.value;
        sel.innerHTML = '';

        const defaultOpt = allOptions.find(o => !o.value) || { value: '', text: (side === 'a' ? '— اختر الطرف الأول (أ) —' : '— اختر الطرف الثاني (ب) —') };
        const defEl = document.createElement('option');
        defEl.value = defaultOpt.value;
        defEl.text = defaultOpt.text;
        sel.appendChild(defEl);

        const matches = allOptions.filter(o => o.value && (!query || o.search.includes(query)));

        const groups = {};
        matches.forEach(m => {
            const grp = m.group || 'عام';
            if (!groups[grp]) groups[grp] = [];
            groups[grp].push(m);
        });

        let hasSelected = false;
        Object.keys(groups).forEach(grpName => {
            let container = sel;
            if (grpName !== 'عام') {
                const optGroup = document.createElement('optgroup');
                optGroup.label = grpName;
                sel.appendChild(optGroup);
                container = optGroup;
            }
            groups[grpName].forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.text = o.text;
                if (o.value === currentVal) {
                    opt.selected = true;
                    hasSelected = true;
                }
                container.appendChild(opt);
            });
        });

        if (query && !hasSelected && matches.length > 0) {
            sel.value = matches[0].value;
            triggerVehicleComparison();
        }

        if (quickBox) {
            quickBox.innerHTML = '';
            if (query && matches.length > 0) {
                matches.slice(0, 4).forEach(m => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn-entity-chip py-0 px-2';
                    btn.style.fontSize = '0.72rem';
                    btn.innerText = m.text.split('(')[0].replace('👤','').replace('🚗','').replace('⛽','').replace('📍','').trim();
                    btn.onclick = function() {
                        sel.value = m.value;
                        quickBox.innerHTML = '';
                        triggerVehicleComparison();
                    };
                    quickBox.appendChild(btn);
                });
            }
        }
    }

    function swapCompareVehicles() {
        const selectA = document.getElementById('compare_select_a');
        const selectB = document.getElementById('compare_select_b');
        if (!selectA || !selectB) return;
        const valA = selectA.value;
        const valB = selectB.value;
        selectA.value = valB;
        selectB.value = valA;
        triggerVehicleComparison();
    }

    function initComparisonTab() {
        setTimeout(() => {
            renderBestFourCategories();
            initCompareSelectOptions();
            const selectA = document.getElementById('compare_select_a');
            const selectB = document.getElementById('compare_select_b');
            if (selectA && selectB && selectA.value && selectB.value) {
                triggerVehicleComparison();
            }
        }, 40);
    }

    function triggerVehicleComparison() {
        const selectA = document.getElementById('compare_select_a');
        const selectB = document.getElementById('compare_select_b');
        const vA = selectA ? selectA.value.trim() : '';
        const vB = selectB ? selectB.value.trim() : '';

        const spinner = document.getElementById('compare_loading_spinner');
        const errorBox = document.getElementById('compare_error_box');
        const resultsBox = document.getElementById('compare_results_box');

        if (!vA || !vB) {
            if (errorBox) {
                errorBox.style.display = 'block';
                errorBox.innerText = 'يرجى اختيار الطرفين (أ) و (ب) لعرض المقارنة.';
            }
            if (resultsBox) resultsBox.style.display = 'none';
            return;
        }

        if (vA === vB) {
            if (errorBox) {
                errorBox.style.display = 'block';
                errorBox.innerText = COMPARE_MODE_META[compareMode].sameErr;
            }
            if (resultsBox) resultsBox.style.display = 'none';
            return;
        }

        if (errorBox) errorBox.style.display = 'none';
        if (spinner) spinner.style.display = 'block';
        if (resultsBox) resultsBox.style.display = 'none';

        let cmpUrl = `{{ url('/gas-station/compare-vehicles') }}?type=${encodeURIComponent(compareMode)}&vehicle_a=${encodeURIComponent(vA)}&vehicle_b=${encodeURIComponent(vB)}`;
        if (ACTIVE_FILTER_FROM) cmpUrl += `&from=${encodeURIComponent(ACTIVE_FILTER_FROM)}`;
        if (ACTIVE_FILTER_TO) cmpUrl += `&to=${encodeURIComponent(ACTIVE_FILTER_TO)}`;
        if (ACTIVE_FILTER_COMPANY) cmpUrl += `&company_id=${encodeURIComponent(ACTIVE_FILTER_COMPANY)}`;

        fetch(cmpUrl)
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.style.display = 'none';
                if (data.error) {
                    if (errorBox) {
                        errorBox.style.display = 'block';
                        errorBox.innerText = data.error;
                    }
                    return;
                }

                if (resultsBox) resultsBox.style.display = 'block';
                renderVehicleComparison(data);
            })
            .catch(err => {
                if (spinner) spinner.style.display = 'none';
                if (errorBox) {
                    errorBox.style.display = 'block';
                    errorBox.innerText = 'حدث خطأ أثناء جلب بيانات المقارنة: ' + err.message;
                }
            });
    }

    let lastComparisonResult = null;

    function renderVehicleComparison(data) {
        lastComparisonResult = data;
        const a = data.vehicle_a;
        const b = data.vehicle_b;
        const winners = data.winners;
        const standards = data.radar_standards || { effective_min: 3.6, effective_max: 7.7 };

        const isVehicleMode = (data.compare_type || compareMode) === 'vehicle';
        const titleA = !isVehicleMode ? a.vehicle_key : ((a.drivers && a.drivers.length === 1 && a.drivers[0] === a.vehicle_key)
            ? a.vehicle_key
            : (a.car_number !== '-' ? a.car_number : a.vehicle_key));

        const titleB = !isVehicleMode ? b.vehicle_key : ((b.drivers && b.drivers.length === 1 && b.drivers[0] === b.vehicle_key)
            ? b.vehicle_key
            : (b.car_number !== '-' ? b.car_number : b.vehicle_key));

        document.getElementById('cmp_a_title').innerText = titleA;
        document.getElementById('cmp_a_subtitle').innerText = a.companies.length ? a.companies.join('، ') : '';
        document.getElementById('cmp_b_title').innerText = titleB;
        document.getElementById('cmp_b_subtitle').innerText = b.companies.length ? b.companies.join('، ') : '';

        // تعبئة أرقام الطرف أ
        document.getElementById('cmp_a_eff').innerHTML = a.avg_efficiency > 0
            ? `<span class="${a.avg_efficiency < standards.effective_min ? 'text-danger' : 'text-dark'}">${a.avg_efficiency} كم/لتر</span>`
            : '<span class="text-muted fw-normal small">رحلة جارية</span>';

        document.getElementById('cmp_a_cost_km').innerHTML = a.avg_cost_km > 0
            ? `<span class="${a.avg_cost_km > standards.effective_max ? 'text-danger' : 'text-dark'}">${a.avg_cost_km} ج/كم</span>`
            : '<span class="text-muted fw-normal small">—</span>';

        document.getElementById('cmp_a_liters').innerText = Number(a.total_liters).toLocaleString() + ' لتر';
        document.getElementById('cmp_a_dist').innerText = Number(a.total_distance).toLocaleString() + ' كم';
        document.getElementById('cmp_a_cost').innerText = Number(a.total_cost).toLocaleString() + ' ج.م';
        document.getElementById('cmp_a_trips').innerText = `${a.total_trips} (${a.full_fills} مقفلة)`;
        const odomA = document.getElementById('cmp_a_odometer');
        if (odomA) odomA.innerText = a.last_odometer ? Number(a.last_odometer).toLocaleString() + ' كم' : '—';

        const suspElA = document.getElementById('cmp_a_suspicion');
        if (suspElA) {
            suspElA.innerHTML = a.suspicion_count > 0
                ? `<span class="text-danger fw-bold">${a.suspicion_count} تنبيه</span>`
                : `<span class="text-muted">طبيعي</span>`;
        }

        const driversBoxA = document.getElementById('cmp_a_drivers_tags');
        if (driversBoxA) {
            driversBoxA.innerHTML = a.drivers.length
                ? a.drivers.slice(0, 5).map(d => `<span class="badge bg-light text-secondary border fw-normal">${d}</span>`).join(' ')
                : '<span class="text-muted">—</span>';
        }

        const btnA = document.getElementById('cmp_a_btn_drivvo');
        if (btnA) btnA.setAttribute('onclick', `openDriverReportModal('${titleA}')`);

        // تعبئة أرقام الطرف ب
        document.getElementById('cmp_b_eff').innerHTML = b.avg_efficiency > 0
            ? `<span class="${b.avg_efficiency < standards.effective_min ? 'text-danger' : 'text-dark'}">${b.avg_efficiency} كم/لتر</span>`
            : '<span class="text-muted fw-normal small">رحلة جارية</span>';

        document.getElementById('cmp_b_cost_km').innerHTML = b.avg_cost_km > 0
            ? `<span class="${b.avg_cost_km > standards.effective_max ? 'text-danger' : 'text-dark'}">${b.avg_cost_km} ج/كم</span>`
            : '<span class="text-muted fw-normal small">—</span>';

        document.getElementById('cmp_b_liters').innerText = Number(b.total_liters).toLocaleString() + ' لتر';
        document.getElementById('cmp_b_dist').innerText = Number(b.total_distance).toLocaleString() + ' كم';
        document.getElementById('cmp_b_cost').innerText = Number(b.total_cost).toLocaleString() + ' ج.م';
        document.getElementById('cmp_b_trips').innerText = `${b.total_trips} (${b.full_fills} مقفلة)`;
        const odomB = document.getElementById('cmp_b_odometer');
        if (odomB) odomB.innerText = b.last_odometer ? Number(b.last_odometer).toLocaleString() + ' كم' : '—';

        const suspElB = document.getElementById('cmp_b_suspicion');
        if (suspElB) {
            suspElB.innerHTML = b.suspicion_count > 0
                ? `<span class="text-danger fw-bold">${b.suspicion_count} تنبيه</span>`
                : `<span class="text-muted">طبيعي</span>`;
        }

        const driversBoxB = document.getElementById('cmp_b_drivers_tags');
        if (driversBoxB) {
            driversBoxB.innerHTML = b.drivers.length
                ? b.drivers.slice(0, 5).map(d => `<span class="badge bg-light text-secondary border fw-normal">${d}</span>`).join(' ')
                : '<span class="text-muted">—</span>';
        }

        const btnB = document.getElementById('cmp_b_btn_drivvo');
        if (btnB) btnB.setAttribute('onclick', `openDriverReportModal('${titleB}')`);

        // شارات الفوز المميزة
        const badgesA = document.getElementById('cmp_a_badges_container');
        const badgesB = document.getElementById('cmp_b_badges_container');
        if (badgesA) badgesA.innerHTML = '';
        if (badgesB) badgesB.innerHTML = '';

        if (winners.efficiency === 'A' && badgesA) {
            badgesA.innerHTML += '<span class="badge bg-success text-white fw-bold"><i class="fa fa-trophy me-1"></i>الأفضل كفاءة</span>';
        } else if (winners.efficiency === 'B' && badgesB) {
            badgesB.innerHTML += '<span class="badge bg-success text-white fw-bold"><i class="fa fa-trophy me-1"></i>الأفضل كفاءة</span>';
        }

        if (winners.cost === 'A' && badgesA) {
            badgesA.innerHTML += '<span class="badge bg-primary text-white fw-bold"><i class="fa fa-coins me-1"></i>الأرخص للكيلو</span>';
        } else if (winners.cost === 'B' && badgesB) {
            badgesB.innerHTML += '<span class="badge text-white fw-bold" style="background:#ea580c;"><i class="fa fa-coins me-1"></i>الأرخص للكيلو</span>';
        }

        // سطر خلاصة المقارنة
        const parts = [];
        if (winners.efficiency === 'A') {
            parts.push(`الأفضل كفاءة: ${titleA} (${a.avg_efficiency} كم/لتر، وفر ${winners.eff_diff_pct}%)`);
        } else if (winners.efficiency === 'B') {
            parts.push(`الأفضل كفاءة: ${titleB} (${b.avg_efficiency} كم/لتر، وفر ${winners.eff_diff_pct}%)`);
        }
        if (winners.cost === 'A') {
            parts.push(`الأرخص للكيلو: ${titleA} (${a.avg_cost_km} ج/كم)`);
        } else if (winners.cost === 'B') {
            parts.push(`الأرخص للكيلو: ${titleB} (${b.avg_cost_km} ج/كم)`);
        }
        if (!parts.length) {
            parts.push(`مقارنة التشغيل: ${titleA} (${Number(a.total_liters).toLocaleString()} لتر) مقابل ${titleB} (${Number(b.total_liters).toLocaleString()} لتر)`);
        }

        const descEl = document.getElementById('compare_winner_desc');
        if (descEl) descEl.innerText = parts.join('  ·  ');

        renderCompareDestinationsTable(data);
        renderCompareEfficiencyChart(titleA, titleB, a, b, data.destinations_comparison, standards);
        renderCompareCostChart(titleA, titleB, a, b, data.destinations_comparison, standards);
        renderCompareVolumeChart(titleA, titleB, a, b);
    }

    function renderCompareDestinationsTable(data) {
        const destPanel = document.getElementById('compareDestinationsPanel');
        const destTbody = document.getElementById('compareDestinationsTbody');
        const destBadge = document.getElementById('compareDestinationsBadge');

        if (!destPanel || !destTbody) return;

        if (data.destinations_comparison && data.destinations_comparison.length > 0) {
            destPanel.style.display = 'block';
            if (destBadge) {
                destBadge.innerText = `${data.common_destinations_count} ${COMPARE_MODE_META[compareMode].breakdownNoun} مشتركة`;
            }
            destTbody.innerHTML = '';

            data.destinations_comparison.forEach(item => {
                const tr = document.createElement('tr');
                if (item.in_both) {
                    tr.style.backgroundColor = '#f8fafc';
                }

                const effATxt = item.eff_a > 0 ? `<span class="fw-bold ${item.eff_a < data.radar_standards.effective_min ? 'text-danger' : ''}" style="color:${item.eff_a < data.radar_standards.effective_min ? '' : PARTY_A_COLOR};">${item.eff_a} كم/ل</span>` : '<span class="text-muted">—</span>';
                const costATxt = item.cost_km_a > 0 ? `<span class="fw-bold ${item.cost_km_a > data.radar_standards.effective_max ? 'text-danger' : ''}" style="color:${item.cost_km_a > data.radar_standards.effective_max ? '' : PARTY_A_COLOR};">${item.cost_km_a} ج/كم</span>` : '<span class="text-muted">—</span>';
                const effBTxt = item.eff_b > 0 ? `<span class="fw-bold ${item.eff_b < data.radar_standards.effective_min ? 'text-danger' : ''}" style="color:${item.eff_b < data.radar_standards.effective_min ? '' : PARTY_B_COLOR};">${item.eff_b} كم/ل</span>` : '<span class="text-muted">—</span>';
                const costBTxt = item.cost_km_b > 0 ? `<span class="fw-bold ${item.cost_km_b > data.radar_standards.effective_max ? 'text-danger' : ''}" style="color:${item.cost_km_b > data.radar_standards.effective_max ? '' : PARTY_B_COLOR};">${item.cost_km_b} ج/كم</span>` : '<span class="text-muted">—</span>';

                let diffHtml = '';
                if (item.in_both) {
                    const diff = Math.abs(item.cost_km_a - item.cost_km_b).toFixed(2);
                    if (item.higher_cost === 'A') {
                        diffHtml = `<span class="fw-bold" style="color:${PARTY_B_COLOR};">الطرف (ب) أوفر بـ ${diff} ج/كم</span>`;
                    } else if (item.higher_cost === 'B') {
                        diffHtml = `<span class="fw-bold" style="color:${PARTY_A_COLOR};">الطرف (أ) أوفر بـ ${diff} ج/كم</span>`;
                    } else {
                        diffHtml = `<span class="text-muted">متطابق</span>`;
                    }
                } else {
                    diffHtml = item.stat_a
                        ? `<span style="color:${PARTY_A_COLOR};">الطرف (أ) فقط (${item.stat_a.trips_count})</span>`
                        : `<span style="color:${PARTY_B_COLOR};">الطرف (ب) فقط (${item.stat_b.trips_count})</span>`;
                }

                let suspHtml = '';
                if (item.has_suspicion) {
                    suspHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">تنبيه تكلفة/استهلاك</span>`;
                } else if (item.in_both) {
                    suspHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle">طبيعي</span>`;
                } else {
                    suspHtml = `<span class="text-muted">—</span>`;
                }

                const commonBadge = item.in_both ? '<span class="badge bg-light text-dark border me-1">مشترك</span>' : '';

                tr.innerHTML = `
                    <td class="text-start pe-3 fw-bold text-dark">${commonBadge}${item.location}</td>
                    <td>${effATxt}</td>
                    <td>${costATxt}</td>
                    <td>${effBTxt}</td>
                    <td>${costBTxt}</td>
                    <td>${diffHtml}</td>
                    <td>${suspHtml}</td>
                `;
                destTbody.appendChild(tr);
            });
        } else {
            destPanel.style.display = 'none';
        }
    }

    const simpleBarValuePlugin = {
        id: 'simpleBarValuePlugin',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            ctx.save();
            ctx.font = 'bold 10px "IBM Plex Sans Arabic", sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';

            chart.data.datasets.forEach((dataset, datasetIdx) => {
                const meta = chart.getDatasetMeta(datasetIdx);
                if (meta.hidden) return;
                meta.data.forEach((bar, index) => {
                    const val = dataset.data[index];
                    if (val === null || val === undefined || isNaN(val) || val <= 0) return;
                    const textToDraw = (dataset.customLabels && dataset.customLabels[index])
                        ? dataset.customLabels[index]
                        : val;
                    const barColor = Array.isArray(dataset.backgroundColor)
                        ? dataset.backgroundColor[index]
                        : dataset.backgroundColor;
                    ctx.fillStyle = barColor || '#1e293b';
                    ctx.fillText(textToDraw, bar.x, Math.min(bar.y, bar.base) - 4);
                });
            });
            ctx.restore();
        }
    };

    function renderCompareEfficiencyChart(nameA, nameB, dataA, dataB, destinationsComparison, standards) {
        const ctx = document.getElementById('compareEfficiencyChartCanvas');
        if (!ctx || typeof Chart === 'undefined') return;

        if (compareEfficiencyChartInstance) {
            compareEfficiencyChartInstance.destroy();
        }

        const labels = ['المعدل العام', 'أعلى كفاءة', 'آخر رحلة'];
        const valuesA = [
            Number(dataA.avg_efficiency || 0),
            Number(dataA.max_efficiency || dataA.avg_efficiency || 0),
            Number(dataA.last_rate || dataA.avg_efficiency || 0)
        ];
        const valuesB = [
            Number(dataB.avg_efficiency || 0),
            Number(dataB.max_efficiency || dataB.avg_efficiency || 0),
            Number(dataB.last_rate || dataB.avg_efficiency || 0)
        ];

        if (destinationsComparison && destinationsComparison.length > 0) {
            destinationsComparison.filter(d => d.in_both).slice(0, 2).forEach(cd => {
                labels.push(cd.location.length > 12 ? cd.location.substring(0, 12) + '..' : cd.location);
                valuesA.push(Number(cd.eff_a || 0));
                valuesB.push(Number(cd.eff_b || 0));
            });
        }

        const shortNameA = nameA.replace('👤','').replace('🚗','').trim();
        const shortNameB = nameB.replace('👤','').replace('🚗','').trim();

        compareEfficiencyChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: `(أ) ${shortNameA}`,
                        data: valuesA,
                        backgroundColor: PARTY_A_COLOR,
                        borderRadius: 6,
                        maxBarThickness: 28
                    },
                    {
                        label: `(ب) ${shortNameB}`,
                        data: valuesB,
                        backgroundColor: PARTY_B_COLOR,
                        borderRadius: 6,
                        maxBarThickness: 28
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 20, bottom: 0 } },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'IBM Plex Sans Arabic', size: 11, weight: 'bold' }, boxWidth: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { family: 'IBM Plex Sans Arabic', size: 10 } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { family: 'IBM Plex Sans Arabic', size: 10, weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            },
            plugins: [simpleBarValuePlugin]
        });

        const verdictEl = document.getElementById('cmp_eff_verdict');
        if (verdictEl) {
            if (dataA.avg_efficiency > dataB.avg_efficiency) {
                verdictEl.style.color = PARTY_A_COLOR;
                verdictEl.innerText = `الأفضل: ${shortNameA} (+${(dataA.avg_efficiency - dataB.avg_efficiency).toFixed(2)} كم/ل)`;
            } else if (dataB.avg_efficiency > dataA.avg_efficiency) {
                verdictEl.style.color = PARTY_B_COLOR;
                verdictEl.innerText = `الأفضل: ${shortNameB} (+${(dataB.avg_efficiency - dataA.avg_efficiency).toFixed(2)} كم/ل)`;
            } else {
                verdictEl.style.color = '#64748b';
                verdictEl.innerText = 'متعادلان';
            }
        }
    }

    function renderCompareCostChart(nameA, nameB, dataA, dataB, destinationsComparison, standards) {
        const ctx = document.getElementById('compareCostChartCanvas');
        if (!ctx || typeof Chart === 'undefined') return;

        if (compareCostChartInstance) {
            compareCostChartInstance.destroy();
        }

        const labels = ['تكلفة الكيلو (ج/كم)', 'سعر اللتر (ج/لتر)'];
        const valuesA = [
            Number(dataA.avg_cost_km || 0),
            Number(dataA.avg_price_per_liter || 0)
        ];
        const valuesB = [
            Number(dataB.avg_cost_km || 0),
            Number(dataB.avg_price_per_liter || 0)
        ];

        if (destinationsComparison && destinationsComparison.length > 0) {
            destinationsComparison.filter(d => d.in_both).slice(0, 2).forEach(cd => {
                labels.push(cd.location.length > 12 ? cd.location.substring(0, 12) + '..' : cd.location);
                valuesA.push(Number(cd.cost_km_a || 0));
                valuesB.push(Number(cd.cost_km_b || 0));
            });
        }

        const shortNameA = nameA.replace('👤','').replace('🚗','').trim();
        const shortNameB = nameB.replace('👤','').replace('🚗','').trim();

        compareCostChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: `(أ) ${shortNameA}`,
                        data: valuesA,
                        backgroundColor: PARTY_A_COLOR,
                        borderRadius: 6,
                        maxBarThickness: 28
                    },
                    {
                        label: `(ب) ${shortNameB}`,
                        data: valuesB,
                        backgroundColor: PARTY_B_COLOR,
                        borderRadius: 6,
                        maxBarThickness: 28
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 20, bottom: 0 } },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'IBM Plex Sans Arabic', size: 11, weight: 'bold' }, boxWidth: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { family: 'IBM Plex Sans Arabic', size: 10 } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { family: 'IBM Plex Sans Arabic', size: 10, weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            },
            plugins: [simpleBarValuePlugin]
        });

        const costVerdictEl = document.getElementById('cmp_cost_verdict');
        if (costVerdictEl) {
            if (dataA.avg_cost_km > 0 && dataB.avg_cost_km > 0) {
                if (dataA.avg_cost_km < dataB.avg_cost_km) {
                    costVerdictEl.style.color = PARTY_A_COLOR;
                    costVerdictEl.innerText = `الأوفر: ${shortNameA} (-${(dataB.avg_cost_km - dataA.avg_cost_km).toFixed(2)} ج/كم)`;
                } else if (dataB.avg_cost_km < dataA.avg_cost_km) {
                    costVerdictEl.style.color = PARTY_B_COLOR;
                    costVerdictEl.innerText = `الأوفر: ${shortNameB} (-${(dataA.avg_cost_km - dataB.avg_cost_km).toFixed(2)} ج/كم)`;
                } else {
                    costVerdictEl.style.color = '#64748b';
                    costVerdictEl.innerText = 'متعادلان';
                }
            } else {
                costVerdictEl.innerText = '—';
            }
        }
    }

    function renderCompareVolumeChart(nameA, nameB, dataA, dataB) {
        const ctx = document.getElementById('compareVolumeChartCanvas');
        if (!ctx || typeof Chart === 'undefined') return;

        if (compareVolumeChartInstance) {
            compareVolumeChartInstance.destroy();
        }

        const shortNameA = nameA.replace('👤','').replace('🚗','').trim();
        const shortNameB = nameB.replace('👤','').replace('🚗','').trim();

        const labels = ['الوقود (لتر)', 'المسافة (كم)', 'عدد الرحلات'];
        const valuesA = [
            Number(Number(dataA.total_liters || 0).toFixed(1)),
            Number(Number(dataA.total_distance || 0).toFixed(0)),
            Number(dataA.total_trips || 0)
        ];
        const valuesB = [
            Number(Number(dataB.total_liters || 0).toFixed(1)),
            Number(Number(dataB.total_distance || 0).toFixed(0)),
            Number(dataB.total_trips || 0)
        ];

        compareVolumeChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: `(أ) ${shortNameA}`,
                        data: valuesA,
                        backgroundColor: PARTY_A_COLOR,
                        borderRadius: 6,
                        maxBarThickness: 28
                    },
                    {
                        label: `(ب) ${shortNameB}`,
                        data: valuesB,
                        backgroundColor: PARTY_B_COLOR,
                        borderRadius: 6,
                        maxBarThickness: 28
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 20, bottom: 0 } },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'IBM Plex Sans Arabic', size: 11, weight: 'bold' }, boxWidth: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { family: 'IBM Plex Sans Arabic', size: 10 } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { family: 'IBM Plex Sans Arabic', size: 10, weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            },
            plugins: [simpleBarValuePlugin]
        });
    }

    // ══════════════════════════════════════════════════════════════════════
    // 📑 نظام تقسيم صفحات تقرير كفاءة واستهلاك الشاحنات (30 بالصفحة مع شريط تنقل)
    // ══════════════════════════════════════════════════════════════════════
    let fleetCurrentPage = 1;
    let fleetPageSize = 30; // 30 شاحنة في الصفحة افتراضياً
    let fleetFilteredRows = [];
    let currentFleetView = 'cars';

    function getActiveFleetTbody() {
        if (currentFleetView === 'drivers') return document.getElementById('fleetDriversTbody');
        if (currentFleetView === 'all') return document.getElementById('fleetAllTbody');
        return document.getElementById('fleetCarsTbody');
    }

    function switchFleetView(view) {
        currentFleetView = view;
        const tbCars = document.getElementById('fleetCarsTbody');
        const tbDrivers = document.getElementById('fleetDriversTbody');
        const tbAll = document.getElementById('fleetAllTbody');

        const btnCars = document.getElementById('btnFleetCars');
        const btnDrivers = document.getElementById('btnFleetDrivers');
        const btnAll = document.getElementById('btnFleetAll');

        const thEntity = document.getElementById('thEntityCol');
        const thRelated = document.getElementById('thRelatedCol');
        const noteEl = document.getElementById('fleetActiveViewNote');

        if (tbCars) tbCars.style.display = (view === 'cars') ? '' : 'none';
        if (tbDrivers) tbDrivers.style.display = (view === 'drivers') ? '' : 'none';
        if (tbAll) tbAll.style.display = (view === 'all') ? '' : 'none';

        if (btnCars) {
            btnCars.className = view === 'cars' ? 'btn-report-solid active-blue' : 'btn-report-solid';
        }
        if (btnDrivers) {
            btnDrivers.className = view === 'drivers' ? 'btn-report-solid active-blue' : 'btn-report-solid';
        }
        if (btnAll) {
            btnAll.className = view === 'all' ? 'btn-report-solid active-dark' : 'btn-report-solid';
        }

        if (view === 'cars') {
            if (thEntity) thEntity.innerText = 'السيارة / الشاحنة';
            if (thRelated) thRelated.innerText = 'السائقين المرتبطين';
            if (noteEl) noteEl.innerHTML = '<i class="fa fa-circle-info text-info me-1"></i> معروض الآن: قائمة السيارات والشاحنات مع السائقين الذين قادوها';
        } else if (view === 'drivers') {
            if (thEntity) thEntity.innerText = 'السائق';
            if (thRelated) thRelated.innerText = 'السيارات التي قادها';
            if (noteEl) noteEl.innerHTML = '<i class="fa fa-circle-info text-info me-1"></i> معروض الآن: قائمة السائقين مع السيارات والشاحنات المرتبطة';
        } else {
            if (thEntity) thEntity.innerText = 'السائق / الشاحنة';
            if (thRelated) thRelated.innerText = 'بيانات المركبة';
            if (noteEl) noteEl.innerHTML = '<i class="fa fa-circle-info text-info me-1"></i> معروض الآن: العرض المدمج للسائقين والشاحنات';
        }

        const filterInput = document.getElementById('fleetDriverSearch');
        filterFleetTable(filterInput ? filterInput.value : '');
    }

    function initFleetPagination() {
        const activeTbody = getActiveFleetTbody();
        if (!activeTbody) return;
        const allRows = Array.from(activeTbody.querySelectorAll('tr.fleet-row'));
        fleetFilteredRows = allRows;
        fleetCurrentPage = 1;
        renderFleetPage();
    }

    function renderFleetPage() {
        const activeTbody = getActiveFleetTbody();
        if (!activeTbody) return;
        const allRows = Array.from(activeTbody.querySelectorAll('tr.fleet-row'));
        const noResultsRow = document.getElementById('fleetNoResultsTbody');
        
        const total = fleetFilteredRows.length;
        if (total === 0) {
            allRows.forEach(r => r.style.display = 'none');
            if (noResultsRow) noResultsRow.style.display = '';
            updateFleetPaginationUI(0, 0, 0, 1);
            return;
        } else {
            if (noResultsRow) noResultsRow.style.display = 'none';
        }

        const size = (fleetPageSize === 'all') ? total : parseInt(fleetPageSize, 10);
        const totalPages = Math.max(1, Math.ceil(total / size));
        if (fleetCurrentPage > totalPages) fleetCurrentPage = totalPages;
        if (fleetCurrentPage < 1) fleetCurrentPage = 1;

        const startIndex = (fleetCurrentPage - 1) * size;
        const endIndex = (fleetPageSize === 'all') ? total : Math.min(startIndex + size, total);

        // إخفاء كافة صفوف التاب النشط
        allRows.forEach(r => r.style.display = 'none');

        // إظهار صفوف الصفحة الحالية فقط
        for (let i = startIndex; i < endIndex; i++) {
            if (fleetFilteredRows[i]) {
                fleetFilteredRows[i].style.display = '';
            }
        }

        updateFleetPaginationUI(startIndex + 1, endIndex, total, totalPages);
    }

    function updateFleetPaginationUI(start, end, total, totalPages) {
        const infoStart = document.getElementById('fleetPageStart');
        const infoEnd = document.getElementById('fleetPageEnd');
        const infoTotal = document.getElementById('fleetTotalRows');
        const wrapper = document.getElementById('fleetPaginationWrapper');
        
        if (infoStart) infoStart.innerText = start;
        if (infoEnd) infoEnd.innerText = end;
        if (infoTotal) infoTotal.innerText = total;

        if (wrapper) {
            wrapper.style.display = (total === 0) ? 'none' : 'flex';
        }

        renderFleetNavButtons(totalPages);
    }

    function renderFleetNavButtons(totalPages) {
        const nav = document.getElementById('fleetPaginationNav');
        if (!nav) return;
        nav.innerHTML = '';

        if (totalPages <= 1) {
            const li = document.createElement('li');
            li.className = 'page-item active';
            li.innerHTML = '<span class="page-link shadow-xs rounded-2 px-3 fw-bold">1</span>';
            nav.appendChild(li);
            return;
        }

        // زر الصفحة الأولى
        const firstLi = document.createElement('li');
        firstLi.className = `page-item ${fleetCurrentPage === 1 ? 'disabled' : ''}`;
        firstLi.innerHTML = `<button type="button" class="page-link rounded-2" onclick="goToFleetPage(1)" title="الصفحة الأولى" ${fleetCurrentPage === 1 ? 'disabled' : ''}><i class="fa fa-angles-right"></i></button>`;
        nav.appendChild(firstLi);

        // زر السابق
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${fleetCurrentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<button type="button" class="page-link rounded-2" onclick="goToFleetPage(${fleetCurrentPage - 1})" title="السابق" ${fleetCurrentPage === 1 ? 'disabled' : ''}><i class="fa fa-angle-right me-1"></i> السابق</button>`;
        nav.appendChild(prevLi);

        // حساب ترقيم الصفحات بنمط نافذة منزلقة (Sliding Window)
        let startPage = Math.max(1, fleetCurrentPage - 2);
        let endPage = Math.min(totalPages, fleetCurrentPage + 2);

        if (startPage > 1) {
            const p1 = document.createElement('li');
            p1.className = 'page-item';
            p1.innerHTML = `<button type="button" class="page-link rounded-2" onclick="goToFleetPage(1)">1</button>`;
            nav.appendChild(p1);
            if (startPage > 2) {
                const ell = document.createElement('li');
                ell.className = 'page-item disabled';
                ell.innerHTML = `<span class="page-link rounded-2 border-0">…</span>`;
                nav.appendChild(ell);
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            const pLi = document.createElement('li');
            pLi.className = `page-item ${p === fleetCurrentPage ? 'active' : ''}`;
            pLi.innerHTML = (p === fleetCurrentPage)
                ? `<span class="page-link rounded-2 fw-bold shadow-xs">${p}</span>`
                : `<button type="button" class="page-link rounded-2" onclick="goToFleetPage(${p})">${p}</button>`;
            nav.appendChild(pLi);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ell = document.createElement('li');
                ell.className = 'page-item disabled';
                ell.innerHTML = `<span class="page-link rounded-2 border-0">…</span>`;
                nav.appendChild(ell);
            }
            const pLast = document.createElement('li');
            pLast.className = 'page-item';
            pLast.innerHTML = `<button type="button" class="page-link rounded-2" onclick="goToFleetPage(${totalPages})">${totalPages}</button>`;
            nav.appendChild(pLast);
        }

        // زر التالي
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${fleetCurrentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<button type="button" class="page-link rounded-2" onclick="goToFleetPage(${fleetCurrentPage + 1})" title="التالي" ${fleetCurrentPage === totalPages ? 'disabled' : ''}>التالي <i class="fa fa-angle-left ms-1"></i></button>`;
        nav.appendChild(nextLi);

        // زر الصفحة الأخيرة
        const lastLi = document.createElement('li');
        lastLi.className = `page-item ${fleetCurrentPage === totalPages ? 'disabled' : ''}`;
        lastLi.innerHTML = `<button type="button" class="page-link rounded-2" onclick="goToFleetPage(${totalPages})" title="الصفحة الأخيرة" ${fleetCurrentPage === totalPages ? 'disabled' : ''}><i class="fa fa-angles-left"></i></button>`;
        nav.appendChild(lastLi);
    }

    function goToFleetPage(p) {
        fleetCurrentPage = p;
        renderFleetPage();
        const table = document.getElementById('fleetEfficiencyTable');
        if (table) {
            table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function changeFleetPageSize(val) {
        fleetPageSize = val;
        fleetCurrentPage = 1;
        renderFleetPage();
    }

    // ── فلترة جدول كفاءة الشاحنات والسائقين ──
    function filterFleetTable(query) {
        query = gsNormalize(query || '');
        const activeTbody = getActiveFleetTbody();
        if (!activeTbody) return;
        const allRows = Array.from(activeTbody.querySelectorAll('tr.fleet-row'));
        
        if (!query) {
            fleetFilteredRows = allRows;
        } else {
            fleetFilteredRows = allRows.filter(row => {
                const searchData = gsNormalize(row.dataset.search || row.innerText || '');
                return searchData.includes(query);
            });
        }

        fleetCurrentPage = 1;
        renderFleetPage();

        const badge = document.getElementById('fleetDriversCountBadge');
        if (badge) {
            const label = currentFleetView === 'cars' ? 'سيارة' : (currentFleetView === 'drivers' ? 'سائق' : 'عنصر');
            badge.innerText = query ? `المطابق: ${fleetFilteredRows.length}` : `إجمالي: ${allRows.length} ${label}`;
        }
    }

    // متتبعات كائنات الرسوم البيانية وبيانات التقرير الحالي للطباعة
    let refuelTimelineChart = null;
    let literEfficiencyChart = null;
    let driverMonthlyExpensesChart = null;
    let currentDriverReportData = null;
    let currentDriverModalEntity = null;
    let currentDriverModalType = 'auto';
    let currentDriverModalFilter = 'month'; // 'month' (الافتراضي) | '3months' | 'all' | 'custom'
    let currentDriverModalFrom = null;
    let currentDriverModalTo = null;

    // ── فتح التقرير التحليلي المفصل للشاحنة / السائق (مطابق لـ Drivvo مع شارت النقاط وتكلفة اللتر) ──
    function openDriverReportModal(entityName, entityType = 'auto', filterType = null, customFrom = null, customTo = null) {
        entityName = (entityName || '').trim();
        if (!entityName) return;

        // إذا تم تغيير الكيان، نعيد الفلتر إلى الافتراضي (شهر)
        if (currentDriverModalEntity !== entityName) {
            currentDriverModalFilter = filterType || 'month';
            currentDriverModalFrom = null;
            currentDriverModalTo = null;
        } else if (filterType) {
            currentDriverModalFilter = filterType;
        }

        currentDriverModalEntity = entityName;
        currentDriverModalType = entityType;

        // إغلاق رادار الشبهات إن كان مفتوحاً
        const radarModalEl = document.getElementById('suspiciousRadarModal');
        if (radarModalEl) {
            const radarModal = bootstrap.Modal.getInstance(radarModalEl);
            if (radarModal) radarModal.hide();
        }

        const modalEl = document.getElementById('driverReportModal');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        // تحديث القائمة المنسدلة داخل المودال
        const selectEl = document.getElementById('modalDriverSelect');
        if (selectEl) {
            selectEl.value = entityName;
        }

        // تحديث أزرار الفلترة في الـ UI
        updateDriverModalFilterButtons();

        // تحديد التواريخ بحسب الفلتر
        let fromDate = null;
        let toDate = null;
        const now = new Date();
        const formatDate = (d) => {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        if (currentDriverModalFilter === 'month') {
            const past30 = new Date(now.getTime() - (30 * 24 * 60 * 60 * 1000));
            fromDate = formatDate(past30);
            toDate = formatDate(now);
        } else if (currentDriverModalFilter === '3months') {
            const past90 = new Date(now.getTime() - (90 * 24 * 60 * 60 * 1000));
            fromDate = formatDate(past90);
            toDate = formatDate(now);
        } else if (currentDriverModalFilter === 'custom') {
            fromDate = customFrom || currentDriverModalFrom || formatDate(new Date(now.getTime() - (30 * 24 * 60 * 60 * 1000)));
            toDate = customTo || currentDriverModalTo || formatDate(now);
            currentDriverModalFrom = fromDate;
            currentDriverModalTo = toDate;
            const fromInp = document.getElementById('driverModalFromDate');
            const toInp = document.getElementById('driverModalToDate');
            if (fromInp) fromInp.value = fromDate;
            if (toInp) toInp.value = toDate;
        }

        // إظهار حالة التحميل
        document.getElementById('driverReportLoading').style.display = 'block';
        document.getElementById('driverReportContent').style.display = 'none';
        document.getElementById('driverReportError').style.display = 'none';

        let url = `/gas-station/driver-report?driver=${encodeURIComponent(entityName)}&type=${encodeURIComponent(entityType)}`;
        if (fromDate) url += `&from=${encodeURIComponent(fromDate)}`;
        if (toDate) url += `&to=${encodeURIComponent(toDate)}`;

        fetch(url)
            .then(res => {
                if (!res.ok) {
                    if (res.status === 404) {
                        throw new Error('NOT_FOUND_IN_PERIOD');
                    }
                    throw new Error('فشل جلب بيانات التقرير أو لا توجد حركات مسجلة');
                }
                return res.json();
            })
            .then(data => {
                currentDriverReportData = data;
                renderDriverReport(data);
                document.getElementById('driverReportLoading').style.display = 'none';
                document.getElementById('driverReportContent').style.display = 'block';
                // إعادة ضبط حجم الشارت بعد ظهور المحتوى
                setTimeout(() => {
                    if (refuelTimelineChart) refuelTimelineChart.resize();
                    if (literEfficiencyChart) literEfficiencyChart.resize();
                }, 150);
            })
            .catch(err => {
                document.getElementById('driverReportLoading').style.display = 'none';
                const errEl = document.getElementById('driverReportError');
                errEl.style.display = 'block';
                if (err.message === 'NOT_FOUND_IN_PERIOD') {
                    const filterName = (currentDriverModalFilter === 'month') ? 'آخر شهر' : 'الفترة المحددة';
                    errEl.className = 'alert alert-warning text-center shadow-sm p-4';
                    errEl.innerHTML = `
                        <div class="py-2">
                            <i class="fa fa-calendar-xmark text-warning fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">لا توجد عمليات تفويل مسجلة خلال (${filterName})</h6>
                            <p class="text-muted small mb-3">يمكنك الانتقال لعرض السجل بالكامل أو اختيار فترة مخصصة.</p>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-primary fw-bold px-3 py-1" onclick="setDriverReportDateFilter('all')">
                                    <i class="fa fa-clock-rotate-left me-1"></i> عرض كافة الحركات المسجلة
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-3 py-1" onclick="toggleDriverModalCustomRange(true)">
                                    <i class="fa fa-calendar-days me-1"></i> اختيار فترة مخصصة (رينج)
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    errEl.className = 'alert alert-danger shadow-sm';
                    errEl.innerHTML = `<i class="fa fa-circle-exclamation me-2"></i> ${err.message || 'حدث خطأ أثناء تحميل التقرير'}`;
                }
            });
    }

    function setDriverReportDateFilter(filterType) {
        if (!currentDriverModalEntity) return;
        if (filterType === 'custom') {
            toggleDriverModalCustomRange(true);
            return;
        }
        toggleDriverModalCustomRange(false);
        openDriverReportModal(currentDriverModalEntity, currentDriverModalType, filterType);
    }

    function toggleDriverModalCustomRange(forceShow = null) {
        const customBox = document.getElementById('driverModalCustomRangeInputs');
        if (!customBox) return;
        const isHidden = (customBox.style.display === 'none' || !customBox.style.display);
        const shouldShow = (forceShow !== null) ? forceShow : isHidden;
        customBox.style.display = shouldShow ? 'flex' : 'none';

        if (shouldShow) {
            const now = new Date();
            const formatDate = (d) => {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            const fromInp = document.getElementById('driverModalFromDate');
            const toInp = document.getElementById('driverModalToDate');
            if (fromInp && !fromInp.value) fromInp.value = currentDriverModalFrom || formatDate(new Date(now.getTime() - (30 * 24 * 60 * 60 * 1000)));
            if (toInp && !toInp.value) toInp.value = currentDriverModalTo || formatDate(now);
        }
    }

    function applyDriverModalCustomRange() {
        const fromVal = document.getElementById('driverModalFromDate')?.value;
        const toVal = document.getElementById('driverModalToDate')?.value;
        if (!fromVal && !toVal) {
            alert('يرجى اختيار تاريخ البداية أو النهاية على الأقل');
            return;
        }
        openDriverReportModal(currentDriverModalEntity, currentDriverModalType, 'custom', fromVal, toVal);
    }

    function updateDriverModalFilterButtons() {
        const btnMonth = document.getElementById('driver_filter_btn_month');
        const btn3Months = document.getElementById('driver_filter_btn_3months');
        const btnAll = document.getElementById('driver_filter_btn_all');
        const btnCustom = document.getElementById('driver_filter_btn_custom');

        const resetBtn = (btn) => {
            if (btn) btn.className = 'btn btn-sm btn-outline-secondary fw-semibold px-2 py-1 rounded-2';
        };
        const activeBtn = (btn) => {
            if (btn) btn.className = 'btn btn-sm btn-primary fw-bold px-2 py-1 rounded-2 shadow-xs';
        };

        resetBtn(btnMonth);
        resetBtn(btn3Months);
        resetBtn(btnAll);
        resetBtn(btnCustom);

        if (currentDriverModalFilter === 'month') activeBtn(btnMonth);
        else if (currentDriverModalFilter === '3months') activeBtn(btn3Months);
        else if (currentDriverModalFilter === 'all') activeBtn(btnAll);
        else if (currentDriverModalFilter === 'custom') activeBtn(btnCustom);
    }

    function renderDriverReport(data) {
        currentDriverReportData = data;
        const isCar = data.entity_type === 'car';

        // أيقونة وعنوان المودال الديناميكي
        const iconEl = document.getElementById('modalDriverIcon');
        const iconWrap = document.getElementById('modalDriverIconWrap');
        if (iconEl && iconWrap) {
            if (isCar) {
                iconEl.className = 'fa fa-truck';
                iconWrap.style.color = '#0284c7';
            } else {
                iconEl.className = 'fa fa-user-tie';
                iconWrap.style.color = '#0d7373';
            }
        }

        const nameTitleEl = document.getElementById('modalDriverNameTitle');
        if (nameTitleEl) {
            nameTitleEl.innerText = isCar ? (data.car_number || data.driver_name || '—') : (data.driver_name || '—');
        }

        const subEl = document.getElementById('modalDriverSub');
        if (subEl) {
            if (isCar) {
                subEl.innerText = (data.last_odometer ? `عداد: ${Number(data.last_odometer).toLocaleString()} كم` : 'شاحنة نقل');
            } else {
                subEl.innerText = 'سائق شاحنة' + (data.car_number ? ` · السيارة: ${data.car_number}` : '');
            }
        }

        // شريط عدد الحركات والتاريخ
        const dateBadge = document.getElementById('modalDateRangeBadge');
        if (dateBadge && data.date_range) {
            const filterLabelMap = {
                month: 'آخر شهر',
                '3months': 'آخر 3 شهور',
                all: 'السجل بالكامل',
                custom: 'فترة مخصصة'
            };
            const curFilterLabel = filterLabelMap[currentDriverModalFilter] || 'الفترة';
            const custBadge = Number(data.date_range.custody_count || 0) > 0
                ? ` · <span class="text-primary">+${data.date_range.custody_count} عهدة بدون وقود</span>`
                : '';
            dateBadge.innerHTML = `<i class="fa fa-calendar-days text-primary me-1"></i> <strong class="text-dark">${curFilterLabel}:</strong> ${data.date_range.entries_count} تفويلة${custBadge} (${data.date_range.from} إلى ${data.date_range.to})`;
        }

        // تحديث ترويسة شارت نقاط التفويل
        const headingEl = document.getElementById('timelineChartHeading');
        const subHeadEl = document.getElementById('timelineChartSub');
        if (headingEl) {
            headingEl.innerText = isCar 
                ? `📈 مسار نقاط التفويل وكميات الوقود للسيارة (${data.car_number || ''})` 
                : `📈 مسار نقاط التفويل وكميات الوقود للسائق (${data.driver_name || ''})`;
        }
        if (subHeadEl) {
            if ((data.timeline_points || []).length === 0 && Number(data.date_range?.custody_count || 0) > 0) {
                subHeadEl.innerText = 'جميع العمليات المسجلة في هذه الفترة هي عُهد نقدية بدون وقود — تم استثناؤها تلقائياً من منحنى التفويلات.';
            } else {
                subHeadEl.innerText = isCar
                    ? 'يعتمد المنحنى على التفويلات الفعلية فقط (تم استثناء عمليات العهد بدون وقود) — قف على أي نقطة لعرض التفاصيل'
                    : 'يعتمد المنحنى على التفويلات الفعلية فقط (تم استثناء عمليات العهد بدون وقود) — قف على أي نقطة لعرض التفاصيل';
            }
        }

        const countBadge = document.getElementById('timelinePointsCountBadge');
        if (countBadge) {
            countBadge.innerText = `${(data.timeline_points || []).length} نقاط تفويل`;
        }

        // 📈 تشغيل ورسم شارت نقاط التفويل والشارت السفلي لكفاءة وتكلفة اللتر
        renderRefuelTimelineChart(data.timeline_points || [], data.entity_type);
        renderLiterEfficiencyChart(data.timeline_points || [], data.entity_type);

        // 1) بطاقة التكلفة
        const cost = data.cost || {};
        document.getElementById('drivvo_cost_total').innerText = Number(cost.total || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
        document.getElementById('drivvo_cost_by_day').innerText = Number(cost.by_day || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
        document.getElementById('drivvo_cost_by_km').innerText = Number(cost.by_km || 0).toFixed(2) + ' ج.م';

        // 2) بطاقة كمية الوقود
        const vol = data.volume || {};
        document.getElementById('drivvo_vol_total').innerText = Number(vol.total || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1}) + ' لتر';
        document.getElementById('drivvo_vol_per_refueling').innerText = Number(vol.per_refueling || 0).toFixed(1) + ' لتر';
        document.getElementById('drivvo_vol_by_day').innerText = Number(vol.by_day || 0).toFixed(1) + ' لتر';

        // 3) بطاقة متوسط السعر
        const price = data.price || {};
        document.getElementById('drivvo_price_avg').innerText = Number(price.avg || 0).toFixed(2) + ' ج.م';
        document.getElementById('drivvo_price_lower').innerText = Number(price.lower || 0).toFixed(2) + ' ج.م';
        document.getElementById('drivvo_price_larger').innerText = Number(price.larger || 0).toFixed(2) + ' ج.م';

        // 4) بطاقة كفاءة الاستهلاك
        const eff = data.fuel_efficiency || {};
        const avgEff = Number(eff.general_avg || 0);
        document.getElementById('drivvo_eff_avg').innerText = avgEff > 0 ? avgEff.toFixed(2) + ' كم / لتر' : '—';
        document.getElementById('drivvo_eff_lower').innerText = eff.lower ? Number(eff.lower).toFixed(2) + ' كم / لتر' : '—';
        document.getElementById('drivvo_eff_larger').innerText = eff.larger ? Number(eff.larger).toFixed(2) + ' كم / لتر' : '—';

        // 5) بطاقة نمط ومعدل التفويل
        const routine = data.refueling_routine || {};
        document.getElementById('drivvo_routine_dist').innerText = Number(routine.between_refuelings || 0).toLocaleString() + ' كم';
        document.getElementById('drivvo_routine_freq').innerText = 'كل ' + Number(routine.frequency_days || 0).toFixed(1) + ' يوم';
        document.getElementById('drivvo_routine_monthly').innerText = Number(routine.monthly_projection || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';
        
        const fullPct = Number(routine.full_tank_pct || 0);
        const fullBar = document.getElementById('drivvo_routine_progress_bar');
        if (fullBar) fullBar.style.width = fullPct + '%';
        const routineDesc = document.getElementById('drivvo_routine_desc');
        if (routineDesc) {
            const custNote = Number(data.date_range?.custody_count || 0) > 0
                ? ` (بالإضافة إلى ${data.date_range.custody_count} عملية عهدة نقدية بدون وقود)`
                : '';
            routineDesc.innerText = `${routine.full_tank_count || 0} من ${data.date_range?.entries_count || 0} تفويلات بخزان كامل (${fullPct}%)${custNote} — يقاس معدل الكفاءة بدقة بين كل تفويلتين كاملتين.`;
        }

        // 6) بطاقة مؤشرات آخر رحلة مسجلة والجيج
        const lastMet = data.last_metrics || {};
        const lastRate = Number(lastMet.last_rate || 0);
        document.getElementById('drivvo_last_rate_val').innerText = lastRate > 0 ? lastRate.toFixed(2) + ' كم / لتر' : '—';
        document.getElementById('drivvo_last_rate_lower').innerText = eff.lower ? Number(eff.lower).toFixed(2) + ' كم/لتر' : '—';
        document.getElementById('drivvo_last_rate_larger').innerText = eff.larger ? Number(eff.larger).toFixed(2) + ' كم/لتر' : '—';
        document.getElementById('drivvo_last_rate_period_avg').innerText = avgEff > 0 ? `| متوسط الفترة: ${avgEff.toFixed(2)} كم/لتر` : '';

        // تحريك مؤشر الجيج لكفاءة الاستهلاك
        const rateMin = Number(eff.lower || 0);
        const rateMax = Number(eff.larger || (rateMin + 1));
        let ratePipPct = rateMax > rateMin ? ((lastRate - rateMin) / (rateMax - rateMin)) * 100 : 50;
        ratePipPct = Math.max(5, Math.min(95, ratePipPct));
        const ratePipEl = document.getElementById('drivvo_last_rate_pip');
        if (ratePipEl) ratePipEl.style.left = ratePipPct + '%';

        // تكلفة الكيلومتر الأخيرة
        const lastCostKm = Number(lastMet.last_cost_km || 0);
        const avgCostKm = Number(cost.by_km || 0);
        document.getElementById('drivvo_last_cost_val').innerText = lastCostKm > 0 ? lastCostKm.toFixed(2) + ' ج.م / كم' : '—';
        document.getElementById('drivvo_last_cost_lower').innerText = lastCostKm > 0 ? Math.min(lastCostKm, avgCostKm * 0.7).toFixed(2) + ' ج.م' : '—';
        document.getElementById('drivvo_last_cost_larger').innerText = lastCostKm > 0 ? Math.max(lastCostKm, avgCostKm * 1.3).toFixed(2) + ' ج.م' : '—';
        document.getElementById('drivvo_last_cost_period_avg').innerText = avgCostKm > 0 ? `| متوسط الفترة: ${avgCostKm.toFixed(2)} ج.م / كم` : '';

        let costPipPct = avgCostKm > 0 ? Math.min(95, Math.max(5, (lastCostKm / (avgCostKm * 2)) * 100)) : 50;
        const costPipEl = document.getElementById('drivvo_last_cost_pip');
        if (costPipEl) costPipEl.style.left = costPipPct + '%';

        document.getElementById('drivvo_last_vol_summary').innerText = Number(vol.total || 0).toLocaleString() + ' لتر';
        document.getElementById('drivvo_last_cost_summary').innerText = Number(cost.total || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج.م';

        // 7) جدول محطات الوقود
        const stationsListEl = document.getElementById('drivvo_stations_list');
        if (stationsListEl) {
            stationsListEl.innerHTML = '';
            const stations = data.gas_stations || [];
            if (stations.length === 0) {
                stationsListEl.innerHTML = '<div class="text-muted text-center py-2 small">لا توجد محطات مسجلة</div>';
            } else {
                stations.forEach(st => {
                    stationsListEl.innerHTML += `
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-bold text-dark"><i class="fa fa-gas-pump text-warning me-1"></i> ${st.station_name}</div>
                                <small class="text-muted">${st.count} عمليات · ${Number(st.liters).toLocaleString()} لتر</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark">${Number(st.cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج.م</div>
                                <div class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle small">${st.percentage}% من إجمالي الوقود</div>
                            </div>
                        </div>
                    `;
                });
            }
        }

        // 8) رسم بياني للمصروفات الشهرية Chart.js
        renderDriverMonthlyChart(data.monthly_expenses || []);

        // 9) جدول آخر العمليات
        const tripsTbody = document.getElementById('drivvo_recent_trips_tbody');
        if (tripsTbody) {
            tripsTbody.innerHTML = '';
            const trips = data.recent_trips || [];
            if (trips.length === 0) {
                tripsTbody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">لا توجد عمليات مسجلة</td></tr>';
            } else {
                trips.forEach((tr, i) => {
                    const isCustodyOnly = Number(tr.liters || 0) <= 0;
                    const isFullTank = !isCustodyOnly && (tr.is_full_tank == 1);
                    const effStr = isCustodyOnly
                        ? '—'
                        : (tr.consumption_rate
                            ? Number(tr.consumption_rate).toFixed(2) + ' كم / لتر'
                            : (isFullTank
                                ? (tr.odometer > 0 ? '<span class="badge bg-info-subtle text-primary small">رحلة جارية (تُقفل بالتفويلة القادمة)</span>' : '—')
                                : 'تزويد جزئي'));
                    const typeBadgeHtml = isCustodyOnly
                        ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle small">عهدة</span>'
                        : (isFullTank
                            ? '<span class="badge bg-success-subtle text-success small">كامل</span>'
                            : '<span class="badge bg-warning-subtle text-dark small">جزئي</span>');
                    const litersCellHtml = isCustodyOnly
                        ? '<span class="text-muted small">بدون وقود</span>'
                        : `${Number(tr.liters).toFixed(1)} لتر`;
                    tripsTbody.innerHTML += `
                        <tr>
                            <td>${i + 1}</td>
                            <td dir="ltr" class="small">${tr.created_at ? tr.created_at.substring(0, 16) : '—'}</td>
                            <td>${tr.station_name || '—'}</td>
                            <td class="text-center fw-bold ${isCustodyOnly ? 'text-muted' : 'text-warning'}">${litersCellHtml}</td>
                            <td class="text-end fw-bold text-dark">${Number(tr.total_on_company).toLocaleString()} ج.م</td>
                            <td class="text-center">${typeBadgeHtml}</td>
                            <td class="text-center fw-bold ${!isCustodyOnly && tr.consumption_rate && tr.consumption_rate < 4 && isFullTank ? 'text-danger' : 'text-dark'}">${effStr}</td>
                        </tr>
                    `;
                });
            }
        }
    }

    // ── الشارت 1: رسم بياني لكميات التفويل وتاريخ كل عملية (مسار نقاط التفويل) ──
    function renderRefuelTimelineChart(timelinePoints, entityType) {
        const canvas = document.getElementById('refuelTimelineChartCanvas');
        if (!canvas) return;

        if (refuelTimelineChart) {
            refuelTimelineChart.destroy();
            refuelTimelineChart = null;
        }

        if (!timelinePoints || timelinePoints.length === 0) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            return;
        }

        const labels = timelinePoints.map((p, idx) => {
            return p.short_date || p.date_short || (p.date ? p.date.substring(5, 10).replace('-', '/') : `#${idx + 1}`);
        });
        const litersData = timelinePoints.map(p => p.liters);
        const pointColors = timelinePoints.map(p => p.has_problem ? '#ef4444' : '#0284c7');

        const ctx = canvas.getContext('2d');
        refuelTimelineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'كمية التفويلة (لتر)',
                    data: litersData,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.14)',
                    borderWidth: 3.5,
                    fill: true,
                    tension: 0.25,
                    pointRadius: 9,
                    pointHoverRadius: 14,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: true
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        rtl: true,
                        backgroundColor: 'rgba(15, 23, 42, 0.98)',
                        borderColor: '#38bdf8',
                        borderWidth: 1.5,
                        titleFont: { family: 'IBM Plex Sans Arabic', size: 16, weight: 'bold' },
                        titleSpacing: 10,
                        bodyFont: { family: 'IBM Plex Sans Arabic', size: 14, weight: '600' },
                        bodySpacing: 8,
                        padding: 16,
                        cornerRadius: 12,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                const idx = context[0].dataIndex;
                                const pt = timelinePoints[idx];
                                const fullTime = pt.date_human || pt.date || '';
                                return `⛽ تفويلة رقم (${idx + 1}) — ${fullTime}`;
                            },
                            label: function(context) {
                                const idx = context.dataIndex;
                                const pt = timelinePoints[idx];
                                const lines = [];
                                lines.push(`🔹 كمية الوقود: ${Number(pt.liters).toLocaleString()} لتر`);
                                if (entityType === 'car') {
                                    lines.push(`👤 السائق: ${pt.driver_name || 'غير محدد'}`);
                                } else {
                                    lines.push(`🚗 السيارة: ${pt.car_title || pt.car_number || 'غير محدد'}`);
                                }
                                lines.push(`📍 الوجهة / خط السير: ${pt.location || 'غير محدد'}`);
                                if (pt.station_name) {
                                    lines.push(`⛽ المحطة: ${pt.station_name}`);
                                }
                                if (pt.distance_km > 0) {
                                    lines.push(`📏 المسافة المقطوعة: ${Number(pt.distance_km).toLocaleString()} كم`);
                                }
                                if (pt.consumption_rate > 0) {
                                    lines.push(`⚡ كفاءة اللتر: ${Number(pt.consumption_rate).toFixed(2)} كم / لتر`);
                                }
                                lines.push(`💰 التكلفة: ${Number(pt.total_cost).toLocaleString()} ج.م`);
                                return lines;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.08)' },
                        ticks: {
                            color: '#0f172a',
                            callback: function(val) { return val + ' ل'; },
                            font: { family: 'IBM Plex Sans Arabic', size: 12, weight: 'bold' }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#475569',
                            font: { family: 'IBM Plex Sans Arabic', size: 11, weight: 'bold' },
                            autoSkip: true,
                            maxTicksLimit: 8,
                            maxRotation: 0,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }

    // ── الشارت 2: رسم بياني لكفاءة استهلاك اللتر والمسافة المقطوعة مع تمييز المشاكل بالأحمر ──
    function renderLiterEfficiencyChart(timelinePoints, entityType) {
        const canvas = document.getElementById('literEfficiencyChartCanvas');
        if (!canvas) return;

        if (literEfficiencyChart) {
            literEfficiencyChart.destroy();
            literEfficiencyChart = null;
        }

        if (!timelinePoints || timelinePoints.length === 0) {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            return;
        }

        const labels = timelinePoints.map((p, idx) => {
            return p.short_date || p.date_short || (p.date ? p.date.substring(5, 10).replace('-', '/') : `#${idx + 1}`);
        });
        const effData = timelinePoints.map(p => p.consumption_rate !== null ? p.consumption_rate : 0);
        // الألوان: أحمر عند وجود مشكلة أو هدر (#ef4444)، وأخضر عند كفاءة طبيعية (#10b981)، ورمادي للتزويد الجزئي
        const bgColors = timelinePoints.map(p => {
            if (!p.is_full_tank && !p.consumption_rate) return '#94a3b8';
            return p.has_problem ? '#ef4444' : '#10b981';
        });
        const borderColors = timelinePoints.map(p => {
            if (!p.is_full_tank && !p.consumption_rate) return '#64748b';
            return p.has_problem ? '#b91c1c' : '#047857';
        });

        const ctx = canvas.getContext('2d');
        literEfficiencyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'كفاءة اللتر (كم / لتر)',
                    data: effData,
                    backgroundColor: bgColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    borderRadius: 6,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: true
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        rtl: true,
                        backgroundColor: 'rgba(15, 23, 42, 0.98)',
                        borderColor: '#34d399',
                        borderWidth: 1.5,
                        titleFont: { family: 'IBM Plex Sans Arabic', size: 16, weight: 'bold' },
                        titleSpacing: 10,
                        bodyFont: { family: 'IBM Plex Sans Arabic', size: 14, weight: '600' },
                        bodySpacing: 8,
                        padding: 16,
                        cornerRadius: 12,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                const idx = context[0].dataIndex;
                                const pt = timelinePoints[idx];
                                const fullTime = pt.date_human || pt.date || '';
                                return `⚡ كفاءة التفويلة (${idx + 1}) — ${fullTime}`;
                            },
                            label: function(context) {
                                const idx = context.dataIndex;
                                const pt = timelinePoints[idx];
                                const lines = [];
                                if (pt.consumption_rate > 0) {
                                    lines.push(`⚡ اللتر كفاني ومشاني: ${Number(pt.consumption_rate).toFixed(2)} كم / لتر`);
                                } else if (pt.is_full_tank && pt.odometer > 0) {
                                    lines.push(`⚡ حالة الرحلة: رحلة جارية (تُقفل بالتفويلة القادمة)`);
                                } else {
                                    lines.push(`⚡ كفاءة الاستهلاك: تزويد جزئي (بانتظار تفويلة كاملة)`);
                                }
                                if (entityType === 'car') {
                                    lines.push(`👤 السائق: ${pt.driver_name || 'غير محدد'}`);
                                } else {
                                    lines.push(`🚗 السيارة: ${pt.car_title || pt.car_number || 'غير محدد'}`);
                                }
                                lines.push(`📍 الوجهة: ${pt.location || 'غير محدد'}`);
                                if (pt.distance_km > 0) {
                                    const burned = pt.consumed_liters || pt.liters;
                                    lines.push(`📏 المسافة: ${Number(pt.distance_km).toLocaleString()} كم بـ ${Number(burned).toFixed(1)} لتر مستهلك`);
                                }
                                if (pt.cost_per_km > 0) {
                                    lines.push(`💵 تكلفة الكيلومتر: ${Number(pt.cost_per_km).toFixed(2)} ج.م / كم`);
                                }
                                if (pt.has_problem) {
                                    lines.push(`🚨 تنبيه المشكلة: ${pt.problem_reason}`);
                                } else if (pt.consumption_rate > 0) {
                                    lines.push(`✅ التقييم: كفاءة استهلاك طبيعية وآمنة`);
                                }
                                return lines;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.08)' },
                        ticks: {
                            color: '#0f172a',
                            callback: function(val) { return val + ' كم/ل'; },
                            font: { family: 'IBM Plex Sans Arabic', size: 12, weight: 'bold' }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#475569',
                            font: { family: 'IBM Plex Sans Arabic', size: 11, weight: 'bold' },
                            autoSkip: true,
                            maxTicksLimit: 8,
                            maxRotation: 0,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }

    function renderDriverMonthlyChart(monthlyData) {
        const canvas = document.getElementById('driverMonthlyChartCanvas');
        if (!canvas) return;

        if (driverMonthlyExpensesChart) {
            driverMonthlyExpensesChart.destroy();
        }

        const labels = monthlyData.map(m => m.month);
        const costs  = monthlyData.map(m => m.cost);

        const ctx = canvas.getContext('2d');
        driverMonthlyExpensesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['الشهر الحالي'],
                datasets: [{
                    label: 'إجمالي التكلفة (ج.م)',
                    data: costs.length ? costs : [0],
                    backgroundColor: '#ea580c',
                    borderRadius: 6,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ` التكلفة: ${Number(ctx.raw).toLocaleString()} ج.م`; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return val >= 1000 ? (val/1000) + ' ألف ج' : val + ' ج'; }
                        }
                    }
                }
            }
        });
    }

    // تشغيل ترقيم الصفحات وضبط أحجام الشارتات عند فتح المودال
    document.addEventListener('DOMContentLoaded', function() {
        initFleetPagination();
        const subtabRefuelBtn = document.getElementById('subtab-refueling-btn');
        if (subtabRefuelBtn) {
            subtabRefuelBtn.addEventListener('shown.bs.tab', function() {
                if (refuelTimelineChart) refuelTimelineChart.resize();
                if (literEfficiencyChart) literEfficiencyChart.resize();
            });
        }
    });

    // ══════════════════════════════════════════════════════════════════════
    // 🖨️ محرك الطباعة الرسمية المعتمدة لتقرير كفاءة واستهلاك الشاحنة
    // ══════════════════════════════════════════════════════════════════════
    function printDriverModalReport() {
        const data = currentDriverReportData;
        if (!data) {
            alert('يرجى الانتظار حتى اكتمال تحميل التقرير ثم المحاولة مجدداً');
            return;
        }

        const driverName = data.driver_name || document.getElementById('modalDriverNameTitle')?.innerText || '—';
        const carNumber = data.car_number || '—';
        const vehicleDisplay = (carNumber && carNumber !== '-') 
            ? carNumber 
            : driverName;
        const totalDistance = data.total_distance || 0;
        const lastOdometer = data.last_odometer || 0;
        const dateRange = data.date_range || {};
        const cost = data.cost || {};
        const vol = data.volume || {};
        const price = data.price || {};
        const eff = data.fuel_efficiency || {};
        const routine = data.refueling_routine || {};
        const stations = data.gas_stations || [];
        const trips = data.recent_trips || [];

        const avgEffNum = Number(eff.general_avg || 0);
        let effEvaluationBadge = 'طبيعي معتمد';
        let effEvaluationClass = 'badge-normal';
        if (avgEffNum >= 8) {
            effEvaluationBadge = 'كفاءة ممتازة (اقتصادي جداً)';
            effEvaluationClass = 'badge-success';
        } else if (avgEffNum >= 5) {
            effEvaluationBadge = 'استهلاك جيد ومستقر';
            effEvaluationClass = 'badge-info';
        } else if (avgEffNum > 0 && avgEffNum < 4) {
            effEvaluationBadge = 'استهلاك مفرط (شبهة هدر/تسريب ⚠️)';
            effEvaluationClass = 'badge-danger';
        }

        const now = new Date();
        const printDateFormatted = now.toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        const reportCode = `REP-TRK-${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}-${String(Math.floor(Math.random()*900)+100)}`;

        // جدول المحطات
        let stationsRowsHtml = '';
        if (stations.length === 0) {
            stationsRowsHtml = '<tr><td colspan="6" class="text-center text-muted py-2">لا توجد محطات مسجلة</td></tr>';
        } else {
            stations.forEach((st, idx) => {
                stationsRowsHtml += `
                    <tr>
                        <td class="text-center fw-bold">${idx + 1}</td>
                        <td class="fw-bold">${st.station_name || 'محطة غير محددة'}</td>
                        <td class="text-center">${st.count || 0} عملية</td>
                        <td class="text-center fw-bold">${Number(st.liters || 0).toLocaleString()} لتر</td>
                        <td class="text-end fw-bold">${Number(st.cost || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج.م</td>
                        <td class="text-center fw-bold">${st.percentage || 0}%</td>
                    </tr>
                `;
            });
        }

        // بناء شارتات Vector SVG عالية الدقة للطباعة وملفات PDF (مستثنى منها العهد بدون وقود)
        const tlPoints = (data.timeline_points || []).filter(p => Number(p.liters || 0) > 0);
        let vectorChartsSectionHtml = '';
        if (tlPoints.length > 0) {
            const ptsSlice = tlPoints.slice(-14); // آخر 14 تفويلة فعلية لوضوح مثالي في الطباعة
            const svgW = 420, svgH = 158, padL = 34, padR = 16, padT = 22, padB = 28;
            const plotW = svgW - padL - padR;
            const plotH = svgH - padT - padB;

            // 1) شارت مسار كميات التفويل باللتر (Line + Area SVG)
            const maxLiters = Math.max(...ptsSlice.map(p => Number(p.liters || 0)), 10) * 1.15;
            let lineCoords = [];
            let dotsHtml = '';
            let xLabelsHtml = '';
            const stepX = ptsSlice.length > 1 ? (plotW / (ptsSlice.length - 1)) : 0;

            ptsSlice.forEach((pt, idx) => {
                const x = ptsSlice.length === 1 ? (padL + plotW / 2) : (padL + idx * stepX);
                const val = Number(pt.liters || 0);
                const y = padT + plotH - ((val / maxLiters) * plotH);
                lineCoords.push(`${x.toFixed(1)},${y.toFixed(1)}`);
                const dotColor = pt.has_problem ? '#dc2626' : '#0284c7';
                const lblDate = pt.short_date || pt.date_short || (pt.date ? String(pt.date).substring(5, 10).replace('-', '/') : `#${idx+1}`);
                dotsHtml += `<circle cx="${x.toFixed(1)}" cy="${y.toFixed(1)}" r="4" fill="${dotColor}" stroke="#fff" stroke-width="1.5"/>`;
                if (ptsSlice.length <= 10 || idx % 2 === 0 || idx === ptsSlice.length - 1) {
                    dotsHtml += `<text x="${x.toFixed(1)}" y="${Math.max(12, y - 7).toFixed(1)}" text-anchor="middle" font-size="8.5" font-weight="700" fill="#0f172a">${Math.round(val)}ل</text>`;
                    xLabelsHtml += `<text x="${x.toFixed(1)}" y="${svgH - 8}" text-anchor="middle" font-size="8" font-weight="600" fill="#475569">${lblDate}</text>`;
                }
            });

            let gridLines1 = '';
            for (let g = 0; g <= 3; g++) {
                const gy = padT + (plotH * g / 3);
                const gVal = Math.round(maxLiters * (1 - g / 3));
                gridLines1 += `<line x1="${padL}" y1="${gy.toFixed(1)}" x2="${svgW - padR}" y2="${gy.toFixed(1)}" stroke="#e2e8f0" stroke-dasharray="3,3"/>`;
                gridLines1 += `<text x="${padL - 5}" y="${(gy + 3).toFixed(1)}" text-anchor="end" font-size="8" fill="#64748b">${gVal}</text>`;
            }

            const firstX = ptsSlice.length === 1 ? (padL + plotW / 2) : padL;
            const lastX  = ptsSlice.length === 1 ? (padL + plotW / 2) : (padL + (ptsSlice.length - 1) * stepX);
            const areaPoints = `${firstX.toFixed(1)},${(padT + plotH).toFixed(1)} ${lineCoords.join(' ')} ${lastX.toFixed(1)},${(padT + plotH).toFixed(1)}`;

            const svgTimeline = `
                <svg viewBox="0 0 ${svgW} ${svgH}" width="100%" height="155" xmlns="http://www.w3.org/2000/svg" dir="ltr">
                    <defs>
                        <linearGradient id="fuelGradPrint" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0284c7" stop-opacity="0.24"/>
                            <stop offset="100%" stop-color="#0284c7" stop-opacity="0.02"/>
                        </linearGradient>
                    </defs>
                    ${gridLines1}
                    ${ptsSlice.length > 1 ? `<polygon points="${areaPoints}" fill="url(#fuelGradPrint)"/>` : ''}
                    ${ptsSlice.length > 1 ? `<polyline points="${lineCoords.join(' ')}" fill="none" stroke="#0284c7" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>` : ''}
                    ${dotsHtml}
                    ${xLabelsHtml}
                </svg>
            `;

            // 2) شارت كفاءة الاستهلاك لكل تفويلة (كم / لتر) Bar SVG
            const minEffRef = Number(RADAR_EFFECTIVE_MIN_EFF || 3.6);
            const maxEff = Math.max(...ptsSlice.map(p => Number(p.consumption_rate || 0)), minEffRef + 2, 6) * 1.18;
            const barSlot = plotW / ptsSlice.length;
            const barW = Math.min(24, Math.max(8, barSlot * 0.58));
            let barsHtml = '';
            let gridLines2 = '';

            for (let g = 0; g <= 3; g++) {
                const gy = padT + (plotH * g / 3);
                const gVal = (maxEff * (1 - g / 3)).toFixed(1);
                gridLines2 += `<line x1="${padL}" y1="${gy.toFixed(1)}" x2="${svgW - padR}" y2="${gy.toFixed(1)}" stroke="#e2e8f0" stroke-dasharray="3,3"/>`;
                gridLines2 += `<text x="${padL - 5}" y="${(gy + 3).toFixed(1)}" text-anchor="end" font-size="8" fill="#64748b">${gVal}</text>`;
            }

            const threshY = padT + plotH - ((minEffRef / maxEff) * plotH);
            const threshLine = `
                <line x1="${padL}" y1="${threshY.toFixed(1)}" x2="${svgW - padR}" y2="${threshY.toFixed(1)}" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4,3"/>
                <text x="${svgW - padR}" y="${Math.max(11, threshY - 3).toFixed(1)}" text-anchor="end" font-size="7.5" font-weight="700" fill="#dc2626">حد الأمان ${minEffRef}</text>
            `;

            ptsSlice.forEach((pt, idx) => {
                const rate = Number(pt.consumption_rate || 0);
                const cx = padL + idx * barSlot + barSlot / 2;
                const lblDate = pt.short_date || pt.date_short || (pt.date ? String(pt.date).substring(5, 10).replace('-', '/') : `#${idx+1}`);
                if (rate > 0) {
                    const bh = Math.max(4, (rate / maxEff) * plotH);
                    const by = padT + plotH - bh;
                    const bColor = (pt.has_problem || rate < minEffRef) ? '#dc2626' : '#059669';
                    barsHtml += `<rect x="${(cx - barW / 2).toFixed(1)}" y="${by.toFixed(1)}" width="${barW.toFixed(1)}" height="${bh.toFixed(1)}" rx="3" fill="${bColor}"/>`;
                    if (ptsSlice.length <= 10 || idx % 2 === 0 || idx === ptsSlice.length - 1) {
                        barsHtml += `<text x="${cx.toFixed(1)}" y="${Math.max(12, by - 4).toFixed(1)}" text-anchor="middle" font-size="8" font-weight="700" fill="${bColor}">${rate.toFixed(1)}</text>`;
                    }
                } else {
                    barsHtml += `<rect x="${(cx - barW / 2).toFixed(1)}" y="${(padT + plotH - 5).toFixed(1)}" width="${barW.toFixed(1)}" height="5" rx="2" fill="#cbd5e1"/>`;
                }
                if (ptsSlice.length <= 10 || idx % 2 === 0 || idx === ptsSlice.length - 1) {
                    barsHtml += `<text x="${cx.toFixed(1)}" y="${svgH - 8}" text-anchor="middle" font-size="8" font-weight="600" fill="#475569">${lblDate}</text>`;
                }
            });

            const svgEfficiency = `
                <svg viewBox="0 0 ${svgW} ${svgH}" width="100%" height="155" xmlns="http://www.w3.org/2000/svg" dir="ltr">
                    ${gridLines2}
                    ${threshLine}
                    ${barsHtml}
                </svg>
            `;

            vectorChartsSectionHtml = `
                <div class="section-title">
                    <span>الرسوم البيانية لمسار التفويلات وكفاءة الاستهلاك (Vector عالي الدقة)</span>
                    <span style="font-size:10px; font-weight:600; color:#0369a1;">تم استثناء عمليات العهد بدون وقود واعتماد التفويلات الفعلية فقط (${tlPoints.length} تفويلة)</span>
                </div>
                <div class="charts-print-grid">
                    <div class="chart-print-card">
                        <div class="chart-print-title">مسار كميات الوقود بالتفويلات (لتر)</div>
                        ${svgTimeline}
                    </div>
                    <div class="chart-print-card">
                        <div class="chart-print-title">كفاءة الاستهلاك لكل تفويلة (كم / لتر)</div>
                        ${svgEfficiency}
                    </div>
                </div>
            `;
        }

        // جدول العمليات التفصيلية
        let tripsRowsHtml = '';
        if (trips.length === 0) {
            tripsRowsHtml = '<tr><td colspan="10" class="text-center text-muted py-3">لا توجد حركات مسجلة في هذا التقرير</td></tr>';
        } else {
            trips.forEach((tr, idx) => {
                const isCustodyOnly = Number(tr.liters || 0) <= 0;
                const isFull = !isCustodyOnly && (tr.is_full_tank == 1);
                const effVal = (!isCustodyOnly && tr.consumption_rate) ? Number(tr.consumption_rate).toFixed(2) : null;
                const costKmVal = (!isCustodyOnly && tr.cost_per_km) ? Number(tr.cost_per_km).toFixed(2) : null;
                const hasSuspicion = !isCustodyOnly && ((isFull && effVal && effVal < 4.0) || (costKmVal && costKmVal > 7.0));
                const statusHtml = isCustodyOnly
                    ? '<span class="status-custody">عهدة</span>'
                    : (isFull ? '<span class="status-full">كامل</span>' : '<span class="status-partial">جزئي</span>');

                tripsRowsHtml += `
                    <tr class="${hasSuspicion ? 'suspicious-row' : ''}">
                        <td class="text-center fw-bold text-muted">${idx + 1}</td>
                        <td class="text-center" dir="ltr">${tr.created_at ? tr.created_at.substring(0, 16) : '—'}</td>
                        <td>${tr.station_name || '—'}</td>
                        <td class="text-center fw-bold">${isCustodyOnly ? '<span style="color:#64748b;font-size:9.5px;">بدون وقود</span>' : Number(tr.liters || 0).toFixed(1)}</td>
                        <td class="text-end fw-bold">${Number(tr.total_on_company || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج</td>
                        <td class="text-center" dir="ltr">${tr.odometer ? Number(tr.odometer).toLocaleString() : '—'}</td>
                        <td class="text-center fw-bold" dir="ltr">${tr.distance_km ? '+' + Number(tr.distance_km).toLocaleString() : '—'}</td>
                        <td class="text-center">${statusHtml}</td>
                        <td class="text-center fw-bold ${effVal && effVal < 4 ? 'text-danger' : ''}">${effVal ? effVal + ' كم/ل' : '—'}</td>
                        <td class="text-center ${costKmVal && costKmVal > 7 ? 'text-danger fw-bold' : ''}">${costKmVal ? costKmVal + ' ج' : '—'}</td>
                    </tr>
                `;
            });
        }

        const printWin = window.open('', '_blank', 'width=1150,height=880');
        if (!printWin) {
            alert('يرجى السماح بالنوافذ المنبثقة للمتصفح لفتح شاشة الطباعة');
            return;
        }

        printWin.document.write(`
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير كفاءة واستهلاك الشاحنة - ${vehicleDisplay}</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 15mm 10mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Segoe UI', Tahoma, sans-serif;
            background: #fff;
            color: #0f172a;
            margin: 0;
            padding: 15px;
            font-size: 11.5px;
            line-height: 1.45;
        }

        /* شريط المعاينة للشاشة فقط */
        .no-print-toolbar {
            background: #0f172a;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
        }
        .btn-print:hover { background: #1d4ed8; }
        .btn-close-win {
            background: #475569;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-family: inherit;
        }

        @media print {
            .no-print, .no-print-toolbar { display: none !important; }
            body { padding: 0; }
            .page-break { page-break-before: always; }
        }

        /* ── ترويسة التقرير الرسمية ── */
        .report-header {
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 14px;
            position: relative;
        }
        .header-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 15px;
        }
        .company-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            letter-spacing: -0.3px;
        }
        .company-subtitle {
            font-size: 11.5px;
            color: #475569;
            font-weight: 600;
            margin: 0 0 6px 0;
        }
        .report-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }
        .meta-table {
            border-collapse: collapse;
            font-size: 10.5px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .meta-table td {
            padding: 3px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-table td.lbl {
            font-weight: 700;
            color: #475569;
            background: #f1f5f9;
            width: 85px;
        }

        /* ── بطاقة هوية الشاحنة والسائق ── */
        .section-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            border-right: 4px solid #0284c7;
            padding: 5px 10px;
            margin: 12px 0 8px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .id-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 12px;
        }
        .id-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 10px;
            background: #fff;
        }
        .id-box .lbl {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 2px;
            display: block;
        }
        .id-box .val {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
        }
        .id-box .val-highlight {
            color: #0284c7;
            font-weight: 800;
        }

        /* ── جدول كروت المؤشرات المالية والفنية ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 12px;
        }
        .kpi-cell {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 7px 10px;
            background: #f8fafc;
            text-align: center;
        }
        .kpi-cell .lbl {
            font-size: 10px;
            color: #475569;
            font-weight: 600;
            display: block;
            margin-bottom: 3px;
        }
        .kpi-cell .val {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .kpi-cell .sub-val {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 1px;
            display: block;
        }

        /* ── الجداول الرسمية ── */
        .table-pro {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 12px;
        }
        .table-pro th {
            background: #0f172a;
            color: #fff;
            padding: 6px 8px;
            font-weight: 700;
            text-align: center;
            border: 1px solid #0f172a;
        }
        .table-pro td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            color: #1e293b;
        }
        .table-pro tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .table-pro tbody tr.suspicious-row {
            background: #fef2f2 !important;
        }
        .table-pro tbody tr.suspicious-row td {
            color: #991b1b;
        }
        .status-full {
            background: #dcfce7;
            color: #166534;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 9.5px;
        }
        .status-partial {
            background: #fef3c7;
            color: #92400e;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 9.5px;
        }
        .status-custody {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 9.5px;
        }
        .charts-print-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .chart-print-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px 4px;
            background: #fff;
        }
        .chart-print-title {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 3px;
        }

        /* ── تقييم الكفاءة ── */
        .badge-success { background: #dcfce7; color: #166534; padding: 2px 7px; border-radius: 4px; font-weight: 700; }
        .badge-info    { background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 4px; font-weight: 700; }
        .badge-danger  { background: #fee2e2; color: #991b1b; padding: 2px 7px; border-radius: 4px; font-weight: 700; }
        .badge-normal  { background: #f1f5f9; color: #334155; padding: 2px 7px; border-radius: 4px; font-weight: 700; }

        /* ── قسم التوقيعات والاعتماد الرسمي ── */
        .signatures-box {
            border: 1.5px solid #0f172a;
            border-radius: 8px;
            padding: 14px 18px;
            margin-top: 15px;
            background: #fff;
            page-break-inside: avoid;
        }
        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
        }
        .sig-block .sig-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 11.5px;
            margin-bottom: 28px;
        }
        .sig-block .sig-line {
            border-bottom: 1.5px dashed #94a3b8;
            width: 75%;
            margin: 0 auto 4px auto;
        }
        .sig-block .sig-name {
            font-size: 10px;
            color: #64748b;
        }

        .report-footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- شريط الإجراءات المخصص للشاشة فقط -->
    <div class="no-print-toolbar">
        <div>
            <strong>🖨️ معاينة تقرير الشاحنة الرسمي جاهزة</strong> — مُعد ومُنسق هندسياً لمقاس ورق A4 القياسي
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn-print" onclick="window.print()">
                طباعة التقرير (Print)
            </button>
            <button type="button" class="btn-close-win" onclick="window.close()">
                إغلاق النافذة
            </button>
        </div>
    </div>

    <!-- 1. الترويسة الرسمية الفاخرة -->
    <div class="report-header">
        <div class="header-grid">
            <div>
                <h1 class="company-title">شركة الضبع لنقل البضائع والمقاولات العامة</h1>
                <div class="company-subtitle">إدارة الحركة والتشغيل والصيانة · منظومة إدارة ومحاسبة الوقود والأساطيل</div>
                <div class="report-badge">وثيقة رسمية معتمدة · تقرير كفاءة واستهلاك الوقود للمركبة</div>
            </div>
            <div>
                <table class="meta-table">
                    <tr><td class="lbl">كود التقرير</td><td dir="ltr"><strong>${reportCode}</strong></td></tr>
                    <tr><td class="lbl">تاريخ التقرير</td><td dir="ltr">${printDateFormatted}</td></tr>
                    <tr><td class="lbl">الفترة المشمولة</td><td>من ${dateRange.from || '—'} إلى ${dateRange.to || '—'} (${dateRange.days_span || 1} يوم)</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. بطاقة هوية المركبة والسائق -->
    <div class="section-title">
        <span>بيانات المركبة والسائق</span>
        <span style="font-size:10.5px; font-weight:600;">الحالة الفنية: ${effEvaluationBadge}</span>
    </div>
    <div class="id-grid">
        <div class="id-box">
            <span class="lbl">رقم الشاحنة / اللوحة</span>
            <span class="val val-highlight">${carNumber && carNumber !== '-' ? carNumber : (driverName || '—')}</span>
        </div>
        <div class="id-box">
            <span class="lbl">تصنيف الحساب / المركبة</span>
            <span class="val">${Number(vol.total || 0) <= 0 && Number(dateRange.custody_count || 0) > 0 ? 'حساب عهدة وخدمات تشغيلية' : (data.entity_type === 'car' ? 'شاحنة نقل بالأسطول' : 'سائق تشغيل بالأسطول')}</span>
        </div>
        <div class="id-box">
            <span class="lbl">السائق القائم بالتشغيل</span>
            <span class="val">${driverName || '—'}</span>
        </div>
        <div class="id-box">
            <span class="lbl">آخر قراءة عداد مسجلة</span>
            <span class="val" dir="ltr">${lastOdometer ? Number(lastOdometer).toLocaleString() + ' كم' : '—'}</span>
        </div>
    </div>

    <!-- 3. ملخص المؤشرات الفنية والمالية -->
    <div class="section-title">
        <span>المؤشرات المالية والفنية ومعدلات الاستهلاك</span>
        <span style="font-size:10.5px; font-weight:600;">عدد العمليات: ${dateRange.entries_count || 0} تفويلة</span>
    </div>
    <div class="kpi-grid">
        <div class="kpi-cell">
            <span class="lbl">إجمالي الوقود المستهلك</span>
            <span class="val">${Number(vol.total || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})} لتر</span>
            <span class="sub-val">بمعدل ${Number(vol.by_day || 0).toFixed(1)} لتر/يوم</span>
        </div>
        <div class="kpi-cell">
            <span class="lbl">إجمالي تكلفة الوقود</span>
            <span class="val" style="color:#0284c7;">${Number(cost.total || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج.م</span>
            <span class="sub-val">بمعدل ${Number(cost.by_day || 0).toFixed(1)} ج/يوم</span>
        </div>
        <div class="kpi-cell">
            <span class="lbl">متوسط كفاءة الاستهلاك</span>
            <span class="val ${avgEffNum < 4 && avgEffNum > 0 ? 'text-danger' : ''}">${avgEffNum > 0 ? avgEffNum.toFixed(2) + ' كم / لتر' : '—'}</span>
            <span class="sub-val">${effEvaluationBadge}</span>
        </div>
        <div class="kpi-cell">
            <span class="lbl">متوسط تكلفة الكيلومتر</span>
            <span class="val ${cost.by_km > 7 ? 'text-danger' : ''}">${cost.by_km ? Number(cost.by_km).toFixed(2) + ' ج / كم' : '—'}</span>
            <span class="sub-val">إجمالي المسافة: ${Number(totalDistance).toLocaleString()} كم</span>
        </div>
        <div class="kpi-cell">
            <span class="lbl">متوسط سعر اللتر</span>
            <span class="val">${price.avg ? Number(price.avg).toFixed(2) + ' ج' : '—'}</span>
            <span class="sub-val">بين ${price.lower || '—'} و ${price.larger || '—'} ج</span>
        </div>
        <div class="kpi-cell">
            <span class="lbl">نمط التفويل</span>
            <span class="val">${routine.full_tank_pct || 0}% كامل</span>
            <span class="sub-val">${routine.full_tank_count || 0} كاملة · ${routine.partial_tank_count || 0} جزئية</span>
        </div>
        <div class="kpi-cell">
            <span class="lbl">المسافة بين التفويلات</span>
            <span class="val">${Number(routine.between_refuelings || 0).toLocaleString()} كم</span>
            <span class="sub-val">تفويل كل ${Number(routine.frequency_days || 0).toFixed(1)} يوم</span>
        </div>
        <div class="kpi-cell">
            <span class="lbl">التوقع المالي الشهري</span>
            <span class="val">${Number(routine.monthly_projection || 0).toLocaleString(undefined, {maximumFractionDigits:0})} ج.م</span>
            <span class="sub-val">تقدير على أساس 30 يوم</span>
        </div>
    </div>

    ${vectorChartsSectionHtml}

    <!-- 4. توزيع محطات الوقود -->
    <div class="section-title">
        <span>محطات الوقود التي تم التفويل منها</span>
        <span style="font-size:10.5px; font-weight:600;">${stations.length} محطات</span>
    </div>
    <table class="table-pro">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>اسم محطة الوقود</th>
                <th style="width:100px;">عدد العمليات</th>
                <th style="width:130px;">إجمالي اللترات</th>
                <th style="width:140px;">إجمالي المبلغ (ج.م)</th>
                <th style="width:120px;">النسبة من الوقود</th>
            </tr>
        </thead>
        <tbody>
            ${stationsRowsHtml}
        </tbody>
    </table>

    <!-- 5. السجل التفصيلي للعمليات -->
    <div class="section-title">
        <span>السجل التفصيلي لعمليات التفويل والرحلات</span>
        <span style="font-size:10.5px; font-weight:600;">إجمالي ${trips.length} عملية مسجلة</span>
    </div>
    <table class="table-pro">
        <thead>
            <tr>
                <th style="width:35px;">#</th>
                <th style="width:110px;">التاريخ والوقت</th>
                <th>محطة الوقود</th>
                <th style="width:75px;">اللترات</th>
                <th style="width:95px;">المبلغ (ج)</th>
                <th style="width:85px;">العداد</th>
                <th style="width:75px;">المسافة</th>
                <th style="width:65px;">التفويل</th>
                <th style="width:85px;">الكفاءة</th>
                <th style="width:80px;">تكلفة كم</th>
            </tr>
        </thead>
        <tbody>
            ${tripsRowsHtml}
        </tbody>
    </table>

    <!-- 6. قسم الاعتماد والتوقيعات الرسمية -->
    <div class="signatures-box">
        <div class="signatures-grid">
            <div class="sig-block">
                <div class="sig-title">مسؤول الحركة والتشغيل</div>
                <div class="sig-line"></div>
                <div class="sig-name">الاسم والتوقيع: .................................</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">المراجعة والرقابة المالية</div>
                <div class="sig-line"></div>
                <div class="sig-name">الاسم والتوقيع: .................................</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">اعتماد المدير العام / خاتم الشركة</div>
                <div class="sig-line"></div>
                <div class="sig-name">التوقيع والخاتم: .................................</div>
            </div>
        </div>
    </div>

    <!-- التذييل -->
    <div class="report-footer">
        <div>منظومة إدارة ومحاسبة وقود الشاحنات — شركة الضبع لنقل البضائع والمقاولات العامة</div>
        <div>صفحة معتمدة رسمياً · تم إصدارها بواسطة النظام الآلي</div>
    </div>

</body>
</html>
        `);
        printWin.document.close();
        setTimeout(() => {
            printWin.focus();
            printWin.print();
        }, 500);
    }

    // ══════════════════════════════════════════════════════════════════════
    // ⚖️🖨️ محرك تصدير وطباعة المقارنات والشارتات بدقة عالية Vector SVG PDF
    // ══════════════════════════════════════════════════════════════════════
    function printComparisonReport() {
        openGasPdfExport('comparisons');
    }
</script>

<!-- Modal 🚨: رادار كشف الشبهات والهدر (نافذة الفحص الأمني للشاحنات) -->
<div class="modal fade" id="suspiciousRadarModal" tabindex="-1" aria-labelledby="suspiciousRadarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: 2px solid #ef4444;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-triangle-exclamation fs-4 text-warning"></i>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="suspiciousRadarModalLabel">رادار كشف الشبهات والهدر في وقود الشاحنات</h5>
                        <small style="opacity:0.9;">فحص ذكي آلي لكفاءة الاستهلاك ومعدل تكلفة الكيلومتر ورصد أي تلاعب أو تسريب</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light fw-bold text-dark px-3 py-1 shadow-sm" onclick="openRadarSettingsModal()">
                        <i class="fa fa-sliders text-danger me-1"></i> ضبط المعايير والسماح
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4" style="background: #f8fafc;">
                @if(count($suspiciousAlerts ?? []) > 0)
                    {{-- شريط البحث الفوري داخل رادار الشبهات --}}
                    <div class="mb-3">
                        <div class="input-group input-group-lg shadow-xs" style="border-radius: 10px; overflow: hidden;">
                            <span class="input-group-text bg-white border-end-0 text-danger ps-3">
                                <i class="fa fa-magnifying-glass"></i>
                            </span>
                            <input type="text" id="radarAlertSearchInput" class="form-control border-start-0 ps-2" 
                                   placeholder="🔍 بحث فوري في التنبيهات (اسم السائق، رقم الشاحنة، الشركة، المحطة، أو رقم الحركة #...)" 
                                   oninput="filterRadarAlerts(this.value)" autocomplete="off">
                            <button class="btn btn-light border border-start-0 text-muted px-3" type="button" 
                                    onclick="document.getElementById('radarAlertSearchInput').value=''; filterRadarAlerts('');" 
                                    title="مسح البحث">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 rounded-3 mb-4 border-danger shadow-sm flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-radiation fs-4 text-danger"></i>
                            <div>
                                <strong class="d-block text-danger">تم رصد ({{ count($suspiciousAlerts) }}) عمليات استهلاك أو تكلفة شاذة تحتاج مراجعة فورية!</strong>
                                <small class="text-muted">المعايير المعتمدة حالياً: استهلاك أقل من {{ $effectiveMinEff }} كم/لتر (الأساس {{ $radarMinEff }} بسماح ±{{ $radarTolerancePct }}%) يُعد هدر/تسريب، وتكلفة أعلى من {{ $effectiveMaxCostKm }} ج/كم (الأساس {{ $radarMaxCostKm }} بسماح ±{{ $radarTolerancePct }}%) تُعد تكلفة مرتفعة شاذة.</small>
                            </div>
                        </div>
                        <span class="badge bg-danger fs-6 px-3 py-2 fw-bold" id="radarAlertsCountBadge">{{ count($suspiciousAlerts) }} تنبيه</span>
                    </div>

                    <div class="row g-3" id="radarAlertsCardsContainer">
                        @foreach($suspiciousAlerts as $alert)
                            @php
                                $aId       = is_array($alert) ? ($alert['id'] ?? 0) : ($alert->id ?? 0);
                                $aDriver   = is_array($alert) ? ($alert['driver_name'] ?? '—') : ($alert->driver_name ?? '—');
                                $aCar      = is_array($alert) ? ($alert['car_number'] ?? '') : ($alert->car_number ?? '');
                                $aComp     = is_array($alert) ? ($alert['company_name'] ?? $alert['company'] ?? '—') : ($alert->company_name ?? $alert->company ?? '—');
                                $aStation  = is_array($alert) ? ($alert['station_name'] ?? $alert['station'] ?? '—') : ($alert->station_name ?? $alert->station ?? '—');
                                $aReason   = is_array($alert) ? ($alert['reason'] ?? '') : ($alert->reason ?? '');
                                $aDist     = is_array($alert) ? ($alert['distance_km'] ?? $alert['distance'] ?? 0) : ($alert->distance_km ?? $alert->distance ?? 0);
                                $aLiters   = is_array($alert) ? ($alert['liters'] ?? 0) : ($alert->liters ?? 0);
                                $aRate     = is_array($alert) ? ($alert['consumption_rate'] ?? $alert['rate'] ?? 0) : ($alert->consumption_rate ?? $alert->rate ?? 0);
                                $aCostKm   = is_array($alert) ? ($alert['cost_per_km'] ?? 0) : ($alert->cost_per_km ?? 0);
                                $aDate     = is_array($alert) ? ($alert['created_at'] ?? $alert['date'] ?? null) : ($alert->created_at ?? $alert->date ?? null);
                                $searchTokens = mb_strtolower("{$aDriver} {$aCar} {$aComp} {$aStation} #{$aId} {$aReason}");
                            @endphp
                            <div class="col-lg-6 radar-alert-col" data-search="{{ $searchTokens }}">
                                <div class="card h-100 border-danger shadow-sm" style="border-width: 1.5px; border-radius: 12px; background:#fff;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-1 flex-wrap">
                                                    <i class="fa fa-circle-exclamation text-danger fs-5"></i>
                                                    @if($aCar && $aCar !== '-')
                                                        <span class="badge bg-dark text-white px-2 py-1"><i class="fa fa-truck me-1"></i>{{ $aCar }}</span>
                                                    @endif
                                                    <span>{{ $aDriver }}</span>
                                                </h6>
                                                <small class="text-muted">{{ $aComp }} | {{ $aStation }}</small>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1 small fw-bold">حركة #{{ $aId }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;" onclick="openDriverReportModal('{{ addslashes($aCar && $aCar !== '-' ? $aCar : $aDriver) }}')" title="عرض تقرير الشاحنة/السائق">
                                                    تقرير 📊
                                                </button>
                                            </div>
                                        </div>

                                        <div class="alert alert-danger p-2 mb-3 small fw-bold d-flex align-items-center gap-2" style="border-radius:6px;">
                                            <i class="fa fa-radiation text-danger fs-6"></i>
                                            <div>{{ $aReason }}</div>
                                        </div>

                                        <div class="row g-2 text-center small mb-3">
                                            <div class="col-3 p-1 rounded bg-light border">
                                                <span class="text-muted d-block" style="font-size:0.7rem;">المسافة</span>
                                                <strong class="text-dark">{{ number_format($aDist) }} كم</strong>
                                            </div>
                                            <div class="col-3 p-1 rounded bg-light border">
                                                <span class="text-muted d-block" style="font-size:0.7rem;">اللترات</span>
                                                <strong class="text-warning">{{ number_format($aLiters, 1) }} لتر</strong>
                                            </div>
                                            <div class="col-3 p-1 rounded bg-light border">
                                                <span class="text-muted d-block" style="font-size:0.7rem;">الكفاءة</span>
                                                <strong class="{{ $aRate < ($effectiveMinEff ?? 4.0) ? 'text-danger fw-bolder' : 'text-dark' }}">
                                                    {{ $aRate ? number_format($aRate, 2) . ' كم/ل' : '—' }}
                                                </strong>
                                            </div>
                                            <div class="col-3 p-1 rounded bg-light border">
                                                <span class="text-muted d-block" style="font-size:0.7rem;">تكلفة كم</span>
                                                <strong class="{{ $aCostKm > ($effectiveMaxCostKm ?? 7.0) ? 'text-danger fw-bolder' : 'text-dark' }}">
                                                    {{ $aCostKm ? number_format($aCostKm, 2) . ' ج' : '—' }}
                                                </strong>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center small text-muted pt-2 border-top">
                                            <span dir="ltr"><i class="fa fa-clock me-1"></i> {{ $aDate ? \Carbon\Carbon::parse($aDate)->format('Y-m-d H:i') : '—' }}</span>
                                            <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-bold" onclick="openDriverReportModal('{{ addslashes($aCar && $aCar !== '-' ? $aCar : $aDriver) }}')">
                                                فتح ملف الشاحنة بالكامل <i class="fa fa-arrow-left ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div id="radarAlertsNoMatches" class="col-12 text-center py-4" style="display:none;">
                            <div class="alert alert-light border p-4 rounded-3 text-muted">
                                <i class="fa fa-magnifying-glass fa-2x mb-2 text-secondary d-block"></i>
                                <strong class="d-block text-dark mb-1">لا توجد عمليات شبهات أو هدر مطابقة لكلمة البحث</strong>
                                <small>جرب البحث برقم شاحنة، أو اسم سائق آخر، أو مسح خانة البحث</small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <i class="fa fa-shield-check"></i>
                        </div>
                        <h4 class="fw-bold text-success mb-2">جميع الشاحنات تعمل بكفاءة وأمان كامل</h4>
                        <p class="text-muted" style="max-width: 500px; margin: 0 auto;">
                            لم يتم تسجيل أي عمليات هدر أو معدلات استهلاك شاذة (أقل من {{ $effectiveMinEff }} كم/لتر) أو تكاليف مفرطة للكيلومتر (أعلى من {{ $effectiveMaxCostKm }} ج/كم). الرادار يفحص العمليات بصورة دورية ومستمرة بمعيار سماح ±{{ $radarTolerancePct }}%.
                        </p>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 📊: التقرير التحليلي المفصل للشاحنة والسائق (Drivvo Engine UI) -->
<div class="modal fade" id="driverReportModal" tabindex="-1" aria-labelledby="driverReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            
            {{-- ترويسة المودال بنمط التطبيق المحمول Drivvo --}}
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d7373 0%, #00838f 60%, #0d1f35 100%); padding: 16px 22px;">
                <div class="d-flex align-items-center gap-3 w-100 flex-wrap justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center fw-bold" id="modalDriverIconWrap" style="width: 44px; height: 44px; font-size: 1.1rem; color: #0d7373 !important;">
                            <i class="fa fa-truck" id="modalDriverIcon"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold m-0" id="modalDriverNameTitle">جارٍ التحميل...</h5>
                                <select id="modalDriverSelect" class="form-select form-select-sm py-0 px-2 text-white bg-dark bg-opacity-25 border-white border-opacity-25" style="width:auto; font-size:0.8rem;" onchange="if(this.value){ const opt=this.options[this.selectedIndex]; openDriverReportModal(this.value, opt.getAttribute('data-type') || 'auto'); }">
                                    <optgroup label="🚗 السيارات والشاحنات" class="text-dark">
                                        @foreach($detailedCarsReport ?? [] as $c)
                                            <option value="{{ $c->car_number }}" data-type="car" class="text-dark">🚗 {{ $c->car_number }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="👤 السائقين" class="text-dark">
                                        @foreach($detailedDriversReport ?? [] as $d)
                                            <option value="{{ $d->driver_name }}" data-type="driver" class="text-dark">👤 {{ $d->driver_name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <small id="modalDriverSub" style="opacity:0.85;">شاحنة نقل · تفاصيل الاستهلاك</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-light py-1 px-2 rounded-pill" onclick="printDriverModalReport()" title="طباعة التقرير">
                            <i class="fa fa-print me-1"></i> طباعة
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
            </div>

            {{-- 🗓️ شريط فلاتر التاريخ للشاحنة (الافتراضي: شهر مع إمكانية الرينج المخصص) --}}
            <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: #f8fafc;">
                <div class="d-flex align-items-center gap-1 flex-wrap" id="driverModalDateFilterPresets">
                    <span class="text-secondary small fw-bold me-1"><i class="fa fa-filter text-primary me-1"></i>الفترة:</span>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-2 py-1 rounded-2 shadow-xs" id="driver_filter_btn_month" onclick="setDriverReportDateFilter('month')">
                        <i class="fa fa-calendar-check me-1"></i>آخر شهر (الافتراضي)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold px-2 py-1 rounded-2" id="driver_filter_btn_3months" onclick="setDriverReportDateFilter('3months')">
                        آخر 3 شهور
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold px-2 py-1 rounded-2" id="driver_filter_btn_all" onclick="setDriverReportDateFilter('all')">
                        كل السجل
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold px-2 py-1 rounded-2" id="driver_filter_btn_custom" onclick="toggleDriverModalCustomRange()">
                        <i class="fa fa-calendar-days me-1"></i>فترة مخصصة (رينج)
                    </button>
                </div>

                {{-- حقول إدخال الرينج المخصص --}}
                <div class="align-items-center gap-2 flex-wrap" id="driverModalCustomRangeInputs" style="display: none;">
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text bg-white text-muted small fw-semibold">من:</span>
                        <input type="date" class="form-control form-control-sm" id="driverModalFromDate">
                    </div>
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text bg-white text-muted small fw-semibold">إلى:</span>
                        <input type="date" class="form-control form-control-sm" id="driverModalToDate">
                    </div>
                    <button type="button" class="btn btn-sm btn-success fw-bold px-3 py-1 shadow-xs" onclick="applyDriverModalCustomRange()">
                        <i class="fa fa-check me-1"></i>تطبيق
                    </button>
                </div>
            </div>

            {{-- شريط التبويبات الفرعية بنمط Drivvo بالعربي --}}
            <div class="bg-white border-bottom px-3 pt-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <ul class="nav nav-tabs border-0" id="driverReportSubTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold text-dark border-0 border-bottom border-warning border-3" id="subtab-refueling-btn" data-bs-toggle="tab" data-bs-target="#subtab-refueling-pane" type="button" style="color: #ea580c !important;">
                            <i class="fa fa-gas-pump me-1"></i> ملخص التفويل واستهلاك الوقود
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold text-muted border-0" id="subtab-trips-btn" data-bs-toggle="tab" data-bs-target="#subtab-trips-pane" type="button">
                            <i class="fa fa-clock-rotate-left me-1"></i> سجل العمليات السابقة
                        </button>
                    </li>
                </ul>
                <div class="badge bg-light text-muted border px-3 py-2 small" id="modalDateRangeBadge">
                    <i class="fa fa-calendar-days text-warning me-1"></i> جارٍ التحميل...
                </div>
            </div>

            <div class="modal-body p-3" style="background: #f1f5f9;">
                
                {{-- مؤشر التحميل --}}
                <div id="driverReportLoading" class="text-center py-5">
                    <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-3 fw-bold text-muted">جاري تحليل بيانات الشاحنة ومعدلات الاستهلاك...</div>
                </div>

                {{-- رسالة الخطأ --}}
                <div id="driverReportError" class="alert alert-danger" style="display:none;"></div>

                {{-- المحتوى الرئيسي للتقرير --}}
                <div id="driverReportContent" style="display:none;">
                    <div class="tab-content" id="driverReportSubTabsContent">
                        
                        {{-- ── التبويب الأول: ملخص التفويل والوقود ── --}}
                        <div class="tab-pane fade show active" id="subtab-refueling-pane" role="tabpanel">

                            {{-- 📈 الشارت 1: رسم بياني لكميات التفويل وتاريخ كل عملية (Refuelings Timeline Points) --}}
                            <div class="drivvo-modal-card mb-3 p-3 shadow-sm" style="border: 1.5px solid #0284c7; background: #ffffff;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px; font-size: 1.15rem; background: #0284c7;">
                                            <i class="fa fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark fs-5 d-block" id="timelineChartHeading">📈 مسار نقاط التفويل وكميات الوقود (باللتر)</span>
                                            <div class="text-secondary fw-medium" style="font-size:0.92rem;" id="timelineChartSub">قف على أي نقطة تفويلة لمعرفة تاريخها، كميتها، وبيانات الشاحنة/السائق والوجهة</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold fs-6 px-3 py-2" id="timelinePointsCountBadge">0 نقاط تفويل</span>
                                </div>
                                <div style="height: 300px; position: relative;" class="mt-2">
                                    <canvas id="refuelTimelineChartCanvas"></canvas>
                                </div>
                            </div>

                            {{-- ⚡ الشارت 2: رسم بياني لكفاءة استهلاك اللتر والمسافة (كم/لتر - اللتر مشاني قد ايه) مع تمييز المشاكل بالأحمر --}}
                            <div class="drivvo-modal-card mb-3 p-3 shadow-sm" style="border: 1.5px solid #0d9488; background: #ffffff;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px; font-size: 1.15rem; background: #0d9488;">
                                            <i class="fa fa-gauge-high"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark fs-5 d-block">⚡ كفاءة استهلاك اللتر (اللتر كفاني ومشاني قد ايه - كم / لتر)</span>
                                            <div class="text-secondary fw-medium" style="font-size:0.92rem;">المسافة المقطوعة لكل لتر وقود — النقاط الخضراء طبيعية والنقاط <strong class="text-danger">🔴 الحمراء</strong> تشير لوجود مشكلة أو هدر</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle fs-6 px-3 py-2 fw-bold"><i class="fa fa-circle text-success me-1"></i> استهلاك طبيعي</span>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle fs-6 px-3 py-2 fw-bold"><i class="fa fa-triangle-exclamation text-danger me-1"></i> مشكلة / هدر</span>
                                    </div>
                                </div>
                                <div style="height: 280px; position: relative;" class="mt-2">
                                    <canvas id="literEfficiencyChartCanvas"></canvas>
                                </div>
                            </div>
                            
                            {{-- شبكة الكروت الأربعة بالعربي (التكلفة، كمية الوقود، متوسط السعر، كفاءة الاستهلاك) --}}
                            <div class="row g-3 mb-3">
                                
                                {{-- 1) كارت التكلفة والمصروفات --}}
                                <div class="col-md-6">
                                    <div class="drivvo-modal-card">
                                        <div class="drivvo-card-header">
                                            <div class="drivvo-card-icon"><i class="fa fa-arrow-trend-down"></i></div>
                                            <span class="drivvo-card-title">إجمالي تكلفة الوقود</span>
                                        </div>
                                        <div class="drivvo-card-sub">الإجمالي الكلي</div>
                                        <div class="drivvo-card-val-huge" id="drivvo_cost_total">—</div>
                                        <div class="d-flex justify-content-between pt-2 border-top mt-2 small text-muted">
                                            <div>
                                                <span>المعدل اليومي</span>
                                                <div class="fw-bold text-dark" id="drivvo_cost_by_day">—</div>
                                            </div>
                                            <div class="text-end">
                                                <span>تكلفة الكيلومتر</span>
                                                <div class="fw-bold text-dark" id="drivvo_cost_by_km">—</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2) كارت كمية الوقود --}}
                                <div class="col-md-6">
                                    <div class="drivvo-modal-card">
                                        <div class="drivvo-card-header">
                                            <div class="drivvo-card-icon"><i class="fa fa-canister"></i></div>
                                            <span class="drivvo-card-title">كمية الوقود المسحوبة</span>
                                        </div>
                                        <div class="drivvo-card-sub">إجمالي اللترات</div>
                                        <div class="drivvo-card-val-huge" id="drivvo_vol_total">—</div>
                                        <div class="d-flex justify-content-between pt-2 border-top mt-2 small text-muted">
                                            <div>
                                                <span>متوسط التفويلة الواحدة</span>
                                                <div class="fw-bold text-dark" id="drivvo_vol_per_refueling">—</div>
                                            </div>
                                            <div class="text-end">
                                                <span>الاستهلاك اليومي</span>
                                                <div class="fw-bold text-dark" id="drivvo_vol_by_day">—</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3) كارت متوسط سعر لتر الوقود --}}
                                <div class="col-md-6">
                                    <div class="drivvo-modal-card">
                                        <div class="drivvo-card-header">
                                            <div class="drivvo-card-icon"><i class="fa fa-dollar-sign"></i></div>
                                            <span class="drivvo-card-title">متوسط سعر لتر الوقود</span>
                                        </div>
                                        <div class="drivvo-card-sub">متوسط السعر / لتر</div>
                                        <div class="drivvo-card-val-huge" id="drivvo_price_avg">—</div>
                                        <div class="d-flex justify-content-between pt-2 border-top mt-2 small text-muted">
                                            <div>
                                                <span class="text-success">أدنى سعر مسجل</span>
                                                <div class="fw-bold text-dark" id="drivvo_price_lower">—</div>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-danger">أعلى سعر مسجل</span>
                                                <div class="fw-bold text-dark" id="drivvo_price_larger">—</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 4) كارت معدل كفاءة استهلاك الوقود --}}
                                <div class="col-md-6">
                                    <div class="drivvo-modal-card">
                                        <div class="drivvo-card-header">
                                            <div class="drivvo-card-icon"><i class="fa fa-chart-line"></i></div>
                                            <span class="drivvo-card-title">معدل كفاءة استهلاك الوقود</span>
                                        </div>
                                        <div class="drivvo-card-sub">المتوسط العام للكفاءة</div>
                                        <div class="drivvo-card-val-huge" id="drivvo_eff_avg">—</div>
                                        <div class="d-flex justify-content-between pt-2 border-top mt-2 small text-muted">
                                            <div>
                                                <span class="text-danger">أدنى كفاءة (استهلاك مرتفع)</span>
                                                <div class="fw-bold text-dark" id="drivvo_eff_lower">—</div>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-success">أعلى كفاءة (أفضل توفير)</span>
                                                <div class="fw-bold text-dark" id="drivvo_eff_larger">—</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 5) كارت نمط ودورية التفويل والمسافات --}}
                            <div class="drivvo-modal-card mb-3">
                                <div class="drivvo-card-header">
                                    <div class="drivvo-card-icon"><i class="fa fa-route"></i></div>
                                    <span class="drivvo-card-title">نمط ودورية التفويل والمسافات</span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="small text-muted">متوسط المسافة بين التفويلات</div>
                                        <div class="fw-bold fs-4 text-dark" id="drivvo_routine_dist">—</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="small text-muted">المعدل الزمني الدوري</div>
                                        <div class="fw-bold fs-4 text-dark" id="drivvo_routine_freq">—</div>
                                    </div>
                                    <div class="col-12 text-end pt-2 border-top">
                                        <span class="small text-muted d-block">المصروف الشهري التقديري للوقود</span>
                                        <strong class="fs-5 text-dark" id="drivvo_routine_monthly">—</strong>
                                    </div>
                                </div>

                                {{-- شريط نسبة التانك الكامل --}}
                                <div class="progress" style="height: 10px; border-radius: 999px; background: #fed7aa;">
                                    <div id="drivvo_routine_progress_bar" class="progress-bar" style="background: #ea580c; width: 0%;"></div>
                                </div>
                                <div class="small text-muted mt-2" id="drivvo_routine_desc">
                                    —
                                </div>
                            </div>

                            {{-- 6) كارت مؤشرات آخر عملية تزويد بالوقود --}}
                            <div class="drivvo-modal-card mb-3">
                                <div class="drivvo-card-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="drivvo-card-icon"><i class="fa fa-gas-pump"></i></div>
                                        <span class="drivvo-card-title">مؤشرات آخر عملية تزويد بالوقود</span>
                                    </div>
                                </div>

                                {{-- كفاءة الاستهلاك الأخيرة مع الجيج --}}
                                <div class="mb-4">
                                    <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                                        <i class="fa fa-circle-dot text-warning"></i>
                                        <span>كفاءة استهلاك الوقود لآخر رحلة</span>
                                    </div>
                                    <div class="fs-4 fw-bold text-warning" id="drivvo_last_rate_val">—</div>
                                    
                                    <div class="drivvo-gauge-track">
                                        <div class="drivvo-gauge-pip" id="drivvo_last_rate_pip" style="left: 50%;"></div>
                                        <div class="drivvo-gauge-avg" style="left: 50%;"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small text-muted">
                                        <div><span class="text-danger">أدنى كفاءة:</span> <strong id="drivvo_last_rate_lower">—</strong></div>
                                        <div class="text-muted" id="drivvo_last_rate_period_avg"></div>
                                        <div><span class="text-success">أعلى كفاءة:</span> <strong id="drivvo_last_rate_larger">—</strong></div>
                                    </div>
                                </div>

                                {{-- تكلفة الكيلومتر الأخيرة مع الجيج --}}
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                                        <i class="fa fa-circle-dot text-warning"></i>
                                        <span>تكلفة الكيلومتر لآخر رحلة</span>
                                    </div>
                                    <div class="fs-4 fw-bold text-dark" id="drivvo_last_cost_val">—</div>
                                    
                                    <div class="drivvo-gauge-track reverse-colors">
                                        <div class="drivvo-gauge-pip" id="drivvo_last_cost_pip" style="left: 50%;"></div>
                                        <div class="drivvo-gauge-avg" style="left: 50%;"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small text-muted">
                                        <div><span class="text-success">أقل تكلفة:</span> <strong id="drivvo_last_cost_lower">—</strong></div>
                                        <div class="text-muted" id="drivvo_last_cost_period_avg"></div>
                                        <div><span class="text-danger">أعلى تكلفة:</span> <strong id="drivvo_last_cost_larger">—</strong></div>
                                    </div>
                                </div>

                                <div class="row g-2 pt-2 border-top text-muted small">
                                    <div class="col-6">كمية الوقود: <strong class="text-dark" id="drivvo_last_vol_summary">—</strong></div>
                                    <div class="col-6 text-end">إجمالي التكلفة: <strong class="text-dark" id="drivvo_last_cost_summary">—</strong></div>
                                </div>
                            </div>

                            {{-- 7) رسم بياني لتكلفة الوقود شهرياً --}}
                            <div class="drivvo-modal-card mb-3">
                                <div class="drivvo-card-header">
                                    <div class="drivvo-card-icon"><i class="fa fa-chart-column"></i></div>
                                    <span class="drivvo-card-title">الرسم البياني لتكلفة الوقود شهرياً</span>
                                </div>
                                <div style="height: 180px; position: relative;">
                                    <canvas id="driverMonthlyChartCanvas"></canvas>
                                </div>
                            </div>

                            {{-- 8) محطات الوقود الأكثر استخداماً --}}
                            <div class="drivvo-modal-card">
                                <div class="drivvo-card-header">
                                    <div class="drivvo-card-icon"><i class="fa fa-gas-pump"></i></div>
                                    <span class="drivvo-card-title">محطات الوقود الأكثر استخداماً</span>
                                </div>
                                <div id="drivvo_stations_list">
                                    <!-- يتم تعبئتها بواسطة JS -->
                                </div>
                            </div>
                        </div>

                        {{-- ── التبويب الثاني: سجل العمليات السابقة ── --}}
                        <div class="tab-pane fade" id="subtab-trips-pane" role="tabpanel">
                            <div class="drivvo-modal-card">
                                <h6 class="fw-bold mb-3"><i class="fa fa-clock-rotate-left text-warning me-2"></i> آخر عمليات التفويل المسجلة لهذه الشاحنة</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.82rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>تاريخ التفويلة</th>
                                                <th>محطة الوقود</th>
                                                <th class="text-center">كمية الوقود</th>
                                                <th class="text-end">إجمالي التكلفة</th>
                                                <th class="text-center">حالة الخزان</th>
                                                <th class="text-center">معدل الكفاءة</th>
                                            </tr>
                                        </thead>
                                        <tbody id="drivvo_recent_trips_tbody">
                                            <!-- يتم تعبئتها بواسطة JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal ⚙️: ضبط معايير رادار كشف الشبهات والهدر ونسبة السماح والتفاوت -->
<div class="modal fade" id="radarSettingsModal" tabindex="-1" aria-labelledby="radarSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-sliders text-warning fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="radarSettingsModalLabel">معايير رادار كشف الشبهات والهدر</h5>
                        <small style="opacity:0.85;">تحديد الحدود القياسية للاستهلاك والتكلفة ونسبة السماح والتفاوت</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('gas.update_radar_settings') }}" method="POST" id="radarSettingsForm">
                @csrf
                <div class="modal-body p-4" style="background: #f8fafc;">
                    
                    {{-- 1. الحد الأدنى الأساسي لكفاءة الاستهلاك --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">
                            <i class="fa fa-gauge-high text-danger me-1"></i> الحد الأساسي لكفاءة الاستهلاك (كم / لتر)
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" name="min_efficiency" id="radar_inp_min_eff"
                                   class="form-control fw-bold fs-6" value="{{ old('min_efficiency', $radarMinEff) }}"
                                   required oninput="updateRadarPreview()">
                            <span class="input-group-text bg-white fw-bold text-muted">كم / لتر</span>
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">
                            الاستهلاك الطبيعي للشاحنة (المعتمد: 4.0 كم/لتر). أي قيمة تقل عن هذا الحد يُعد استهلاكاً مفرطاً.
                        </small>
                    </div>

                    {{-- 2. الحد الأقصى الأساسي لتكلفة الكيلومتر --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">
                            <i class="fa fa-tag text-warning me-1"></i> الحد الأساسي لتكلفة الكيلومتر (ج / كم)
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" name="max_cost_km" id="radar_inp_max_cost"
                                   class="form-control fw-bold fs-6" value="{{ old('max_cost_km', $radarMaxCostKm) }}"
                                   required oninput="updateRadarPreview()">
                            <span class="input-group-text bg-white fw-bold text-muted">جنيه / كم</span>
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">
                            التكلفة المعتادة للكيلومتر (المعتمد: 7.0 ج/كم). أي رحلة تتجاوز هذا الحد تُعد تكلفة شاذة.
                        </small>
                    </div>

                    {{-- 3. نسبة التفاوت والسماح المعتمدة % --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">
                            <i class="fa fa-percent text-primary me-1"></i> نسبة التفاوت والسماح المسموحة (%)
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" name="tolerance_pct" id="radar_inp_tolerance"
                                   class="form-control fw-bold fs-6 text-primary" value="{{ old('tolerance_pct', $radarTolerancePct) }}"
                                   required oninput="updateRadarPreview()">
                            <span class="input-group-text bg-white fw-bold text-primary">%</span>
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">
                            نسبة الهامش المسموح بها (المعتمد: 10%). يتم احتساب التنبيه الفعلي بعد تطبيق هذا الهامش.
                        </small>
                    </div>

                    {{-- بطاقة المعاينة الحية للحدود الفعلية --}}
                    <div class="card border-0 bg-white shadow-sm p-3 rounded-3 mt-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa fa-bullseye text-danger fs-5"></i>
                            <strong class="text-dark small">المعاينة الحية لنقاط إطلاق صافرة الرادار:</strong>
                        </div>
                        
                        <div class="p-2 rounded bg-danger bg-opacity-10 text-danger border border-danger-subtle small mb-2 d-flex align-items-center justify-content-between">
                            <span>🚨 تنبيه هدر الوقود عند استهلاك أقل من:</span>
                            <strong class="fs-6" id="preview_eff_val">{{ $effectiveMinEff }} كم/لتر</strong>
                        </div>

                        <div class="p-2 rounded bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle small d-flex align-items-center justify-content-between">
                            <span>💸 تنبيه التكلفة الشاذة عند تكلفة أعلى من:</span>
                            <strong class="fs-6" id="preview_cost_val">{{ $effectiveMaxCostKm }} ج/كم</strong>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fa fa-check me-1"></i> حفظ المعايير وتحديث الرادار فوراً
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
