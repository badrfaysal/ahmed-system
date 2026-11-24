<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الجمعيات (تحصيل وصرف) - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/html2canvas/html2canvas.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'teal'])

    <style>
        body {
            background:
                radial-gradient(circle at 8% 6%, rgba(13, 148, 136, 0.24) 0%, transparent 36%),
                radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.24) 0%, transparent 38%),
                radial-gradient(circle at 80% 86%, rgba(139, 92, 246, 0.20) 0%, transparent 40%),
                radial-gradient(circle at 18% 88%, rgba(16, 185, 129, 0.20) 0%, transparent 38%),
                linear-gradient(155deg, #e0f7f4 0%, #e4f0ff 52%, #efe9ff 100%) !important;
            background-attachment: fixed !important;
        }
        .main-content { margin-right: 260px; padding: 26px 28px; min-height: 100vh; }
        @media(max-width: 991px) { .main-content { margin-right: 0 !important; padding: 75px 16px 30px !important; } }

        /* ── Keyframe Animations ── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes floatGlow {
            0%, 100% { transform: translateY(0px) scale(1); }
            50%      { transform: translateY(-6px) scale(1.06); }
        }
        @keyframes pulseRing {
            0%   { box-shadow: 0 0 0 0 rgba(13, 148, 136, 0.42); }
            70%  { box-shadow: 0 0 0 11px rgba(13, 148, 136, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 148, 136, 0); }
        }
        @keyframes pulseDanger {
            0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.45); }
            70%  { box-shadow: 0 0 0 9px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        @keyframes iconPop {
            0%, 100% { transform: scale(1); }
            50%      { transform: scale(1.08); }
        }

        .tab-content-pane {
            animation: fadeSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ── Hero Header ── */
        .flow-hero {
            background: linear-gradient(135deg, #064e3b 0%, #0d9488 42%, #0284c7 82%, #4f46e5 100%);
            color: #fff;
            border-radius: 22px;
            padding: 26px 30px;
            margin-bottom: 22px;
            box-shadow: 0 14px 36px rgba(13, 148, 136, 0.28);
            position: relative; overflow: hidden;
            display: flex; justify-content: space-between; align-items: center;
            gap: 16px; flex-wrap: wrap;
            animation: fadeSlideUp 0.4s ease-out;
        }
        .flow-hero::before {
            content: ""; position: absolute; bottom: -55%; right: -4%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.3), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .flow-hero::after {
            content: ""; position: absolute; top: -50%; left: -8%;
            width: 340px; height: 340px;
            background: radial-gradient(circle, rgba(167, 243, 208, 0.32), transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        .flow-hero .hero-block { position: relative; z-index: 2; display: flex; gap: 16px; align-items: center; }
        .flow-hero .hero-icon {
            background: linear-gradient(135deg, rgba(255,255,255,0.34), rgba(255,255,255,0.14));
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(255,255,255,0.45);
            width: 66px; height: 66px; border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.9rem; color: #fff;
            animation: floatGlow 3.2s ease-in-out infinite;
            box-shadow: 0 10px 24px rgba(0,0,0,0.18);
        }
        .flow-hero h2 { color: #fff; font-weight: 800; font-size: 1.45rem; margin: 0; text-shadow: 0 2px 6px rgba(0,0,0,0.12); }
        .flow-hero .sub { opacity: 0.95; font-size: 0.89rem; margin-top: 5px; }

        .hero-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; position: relative; z-index: 2; }
        .btn-hero {
            background: rgba(255,255,255,0.22);
            backdrop-filter: blur(6px);
            color: #fff; border: 1.5px solid rgba(255,255,255,0.4);
            border-radius: 13px; padding: 10px 18px; font-weight: 700; font-size: 0.9rem;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-hero i { font-size: 1.05rem; }
        .btn-hero:hover { background: rgba(255,255,255,0.36); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.18); }
        .btn-hero-primary {
            background: linear-gradient(135deg, #ffffff 0%, #ccfbf1 100%);
            color: #0f766e !important; border: none; font-weight: 800;
            box-shadow: 0 6px 20px rgba(0,0,0,0.16);
        }
        .btn-hero-primary:hover { background: #ffffff; color: #065f46 !important; transform: translateY(-2px) scale(1.03); }

        /* ── Filter Bar ── */
        .filter-strip {
            background: linear-gradient(135deg, rgba(255,255,255,0.94) 0%, rgba(236, 253, 245, 0.94) 55%, rgba(239, 246, 255, 0.94) 100%);
            backdrop-filter: blur(8px);
            border: 1.5px solid #99f6e4;
            border-radius: 18px; padding: 14px 22px;
            margin-bottom: 20px; box-shadow: 0 8px 24px rgba(13, 148, 136, 0.08);
            display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .month-picker-wrap { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .month-picker-wrap input[type="month"] {
            border: 1.5px solid #5eead4; border-radius: 12px; padding: 8px 14px;
            font-size: 0.95rem; font-weight: 700; color: #0f172a; background: #fff;
            cursor: pointer; outline: none; transition: all 0.2s;
        }
        .month-picker-wrap input[type="month"]:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.18); }

        /* ── Stat Cards (3 complete rows on desktop: 3 + 3 + 2 half-width) ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            grid-column: span 2;
            background: #fff; border-radius: 22px; padding: 18px 20px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06); border: 1.5px solid #e2e8f0;
            position: relative; overflow: hidden;
            display: flex; flex-direction: column; justify-content: space-between;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            animation: fadeSlideUp 0.45s ease-out both;
        }
        .kpi-card.kpi-card-half {
            grid-column: span 3;
        }
        @media (max-width: 991px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .kpi-card, .kpi-card.kpi-card-half { grid-column: span 1; }
        }
        @media (max-width: 576px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .kpi-card, .kpi-card.kpi-card-half { grid-column: span 1; }
        }
        .kpi-card:nth-child(1) { animation-delay: 0.04s; }
        .kpi-card:nth-child(2) { animation-delay: 0.08s; }
        .kpi-card:nth-child(3) { animation-delay: 0.12s; }
        .kpi-card:nth-child(4) { animation-delay: 0.16s; }
        .kpi-card:nth-child(5) { animation-delay: 0.20s; }
        .kpi-card:nth-child(6) { animation-delay: 0.24s; }
        .kpi-card:nth-child(7) { animation-delay: 0.28s; }
        .kpi-card:nth-child(8) { animation-delay: 0.32s; }
        .kpi-card:nth-child(9) { animation-delay: 0.36s; }

        .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12); }
        .kpi-card::before {
            content: ""; position: absolute; top: 0; right: 0; width: 7px; height: 100%;
        }
        .kpi-card .kpi-watermark {
            position: absolute; bottom: -10px; left: 10px;
            font-size: 5.5rem; opacity: 0.15; pointer-events: none;
            transition: transform 0.35s ease, opacity 0.35s ease;
            z-index: 1;
        }
        .kpi-card:hover .kpi-watermark {
            transform: scale(1.14) rotate(-8deg);
            opacity: 0.24;
        }
        .kpi-card.red    { background: linear-gradient(145deg, #ffffff 18%, #ffe4e6 100%); border-color: #fda4af; }
        .kpi-card.red::before    { background: linear-gradient(180deg, #ef4444, #be123c); }
        .kpi-card.green  { background: linear-gradient(145deg, #ffffff 18%, #d1fae5 100%); border-color: #6ee7b7; }
        .kpi-card.green::before  { background: linear-gradient(180deg, #10b981, #047857); }
        .kpi-card.teal   { background: linear-gradient(145deg, #ffffff 18%, #ccfbf1 100%); border-color: #5eead4; }
        .kpi-card.teal::before   { background: linear-gradient(180deg, #14b8a6, #0f766e); }
        .kpi-card.orange { background: linear-gradient(145deg, #ffffff 18%, #ffedd5 100%); border-color: #fdba74; }
        .kpi-card.orange::before { background: linear-gradient(180deg, #f97316, #c2410c); }
        .kpi-card.purple { background: linear-gradient(145deg, #ffffff 18%, #ede9fe 100%); border-color: #c4b5fd; }
        .kpi-card.purple::before { background: linear-gradient(180deg, #8b5cf6, #5b21b6); }
        .kpi-card.blue   { background: linear-gradient(145deg, #ffffff 18%, #dbeafe 100%); border-color: #7dd3fc; }
        .kpi-card.blue::before   { background: linear-gradient(180deg, #0ea5e9, #1d4ed8); }
        .kpi-card.indigo { background: linear-gradient(145deg, #ffffff 18%, #e0e7ff 100%); border-color: #a5b4fc; }
        .kpi-card.indigo::before { background: linear-gradient(180deg, #6366f1, #3730a3); }
        .kpi-card.rose   { background: linear-gradient(145deg, #ffffff 18%, #fce7f3 100%); border-color: #f9a8d4; }
        .kpi-card.rose::before   { background: linear-gradient(180deg, #e11d48, #9f1239); }
        .kpi-card.kpi-card-wide  { grid-column: 1 / -1; }

        .kpi-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 8px; position: relative; z-index: 2; }
        .kpi-title { font-size: 0.9rem; font-weight: 800; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 7px; }
        .kpi-icon {
            width: 44px; height: 44px; border-radius: 13px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.25rem;
            color: #ffffff;
            transition: transform 0.28s ease;
        }
        .kpi-card:hover .kpi-icon { transform: scale(1.12) rotate(-6deg); }
        .kpi-icon.red    { background: linear-gradient(135deg, #ef4444, #be123c); box-shadow: 0 6px 16px rgba(239, 68, 68, 0.34); }
        .kpi-icon.green  { background: linear-gradient(135deg, #10b981, #047857); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.34); }
        .kpi-icon.teal   { background: linear-gradient(135deg, #14b8a6, #0f766e); box-shadow: 0 6px 16px rgba(13, 148, 136, 0.34); }
        .kpi-icon.orange { background: linear-gradient(135deg, #f97316, #c2410c); box-shadow: 0 6px 16px rgba(249, 115, 22, 0.34); }
        .kpi-icon.purple { background: linear-gradient(135deg, #8b5cf6, #5b21b6); box-shadow: 0 6px 16px rgba(139, 92, 246, 0.34); }
        .kpi-icon.blue   { background: linear-gradient(135deg, #0ea5e9, #0369a1); box-shadow: 0 6px 16px rgba(2, 132, 199, 0.34); }
        .kpi-icon.indigo { background: linear-gradient(135deg, #6366f1, #3730a3); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.34); }
        .kpi-icon.rose   { background: linear-gradient(135deg, #e11d48, #9f1239); box-shadow: 0 6px 16px rgba(225, 29, 72, 0.34); }

        /* ── Unified 3-Block Executive Summary (الداخل ← الخارج ← الصافي) ── */
        .flow-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 16px;
        }
        @media (max-width: 1199px) {
            .flow-summary-grid { grid-template-columns: 1fr; }
        }
        .flow-big-card {
            background: #ffffff;
            border-radius: 22px;
            padding: 20px 22px;
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            animation: fadeSlideUp 0.42s ease-out both;
        }
        .flow-big-card:nth-child(2) { animation-delay: 0.06s; }
        .flow-big-card:nth-child(3) { animation-delay: 0.12s; }
        .flow-big-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.11);
        }
        .flow-big-card::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 5px;
        }
        .flow-big-card.is-in {
            background: linear-gradient(155deg, #ffffff 22%, #e6faf5 100%);
            border-color: #6ee7b7;
        }
        .flow-big-card.is-in::before { background: linear-gradient(90deg, #059669, #14b8a6); }

        .flow-big-card.is-out {
            background: linear-gradient(155deg, #ffffff 22%, #fff0f3 100%);
            border-color: #fda4af;
        }
        .flow-big-card.is-out::before { background: linear-gradient(90deg, #be123c, #f43f5e); }

        .flow-big-card.is-net {
            background: linear-gradient(155deg, #ffffff 22%, #e8f1fe 100%);
            border-color: #93c5fd;
        }
        .flow-big-card.is-net::before { background: linear-gradient(90deg, #1d4ed8, #0284c7); }

        .flow-big-card.is-net.is-deficit {
            background: linear-gradient(155deg, #ffffff 22%, #fef2f2 100%);
            border-color: #fca5a5;
        }
        .flow-big-card.is-net.is-deficit::before { background: linear-gradient(90deg, #b91c1c, #ef4444); }

        .flow-big-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .flow-big-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .flow-big-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }
        .flow-big-subtitle {
            font-size: 0.76rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
        .flow-status-pill {
            font-size: 0.74rem;
            font-weight: 800;
            padding: 4px 11px;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .flow-status-pill.is-in  { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .flow-status-pill.is-out { background: #ffe4e6; color: #9f1239; border: 1px solid #fda4af; }
        .flow-status-pill.is-net { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .flow-status-pill.is-bad { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .flow-big-main {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .flow-big-val {
            font-size: 2.05rem;
            font-weight: 900;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }
        .flow-big-val small {
            font-size: 0.92rem;
            font-weight: 700;
            opacity: 0.85;
        }
        .flow-big-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #334155;
        }
        .flow-progress-track {
            height: 7px;
            background: rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 2px;
        }
        .flow-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #059669, #14b8a6);
            transition: width 0.5s ease;
        }

        .flow-breakdown-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .flow-bd-row {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 13px;
            padding: 10px 13px;
            transition: border-color 0.2s ease, background 0.2s ease;
        }
        .flow-bd-row:hover {
            background: #ffffff;
            border-color: rgba(13, 148, 136, 0.35);
        }
        .flow-bd-row.is-ref {
            background: rgba(248, 250, 252, 0.72);
            border-style: dashed;
            border-color: rgba(124, 58, 237, 0.28);
        }
        .flow-bd-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }
        .flow-bd-label {
            font-size: 0.83rem;
            font-weight: 800;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .flow-bd-val {
            font-size: 0.96rem;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .flow-bd-sub {
            margin-top: 4px;
            font-size: 0.75rem;
            color: #475569;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 4px 10px;
        }
        .flow-mini-link {
            font-size: 0.72rem;
            font-weight: 800;
            padding: 2px 9px;
            border-radius: 999px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.18s ease;
        }

        /* ── Thin Overdue Alert Strip (بعد يوم 10) ── */
        .overdue-alert-strip {
            background: linear-gradient(135deg, #fffbeb 0%, #ffedd5 100%);
            border: 1.5px solid #fdba74;
            border-right: 6px solid #ea580c;
            border-radius: 16px;
            padding: 12px 18px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px 16px;
            box-shadow: 0 6px 18px rgba(234, 88, 12, 0.09);
            animation: fadeSlideUp 0.46s ease-out both;
        }
        .overdue-alert-info {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px 14px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #7c2d12;
        }
        .overdue-alert-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f97316, #c2410c);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .kpi-val { font-size: 1.8rem; font-weight: 900; color: #0f172a; margin-bottom: 8px; line-height: 1.15; font-variant-numeric: tabular-nums; position: relative; z-index: 2; }
        .kpi-sub { font-size: 0.8rem; color: #334155; font-weight: 600; display: flex; justify-content: space-between; gap: 6px; flex-wrap: wrap; position: relative; z-index: 2; }
        .kpi-explain { font-size: 0.76rem; color: #475569; line-height: 1.45; font-weight: 600; position: relative; z-index: 2; }
        .kpi-breakdown { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 8px; position: relative; z-index: 2; }
        .kpi-breakdown-item {
            background: rgba(255,255,255,0.88); backdrop-filter: blur(4px);
            border: 1.5px solid rgba(148, 163, 184, 0.35); border-radius: 13px;
            padding: 9px 12px; transition: all 0.2s;
        }
        .kpi-breakdown-item:hover { border-color: #38bdf8; background: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(2,132,199,0.1); }
        .kpi-breakdown-top {
            display: flex; justify-content: space-between; align-items: center;
            gap: 4px; flex-wrap: wrap; font-size: 0.82rem; color: #0f172a; font-weight: 700; margin-bottom: 3px;
        }
        .kpi-wide-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 8px; }
        @media (max-width: 768px) {
            .kpi-wide-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .kpi-breakdown { grid-template-columns: 1fr; }
        }

        /* ── Standalone Big Block: Combined 1–10 Collection ── */
        .combined-collection-block {
            background: linear-gradient(135deg, #ccfbf1 0%, #ffffff 42%, #d1fae5 76%, #e0f2fe 100%);
            border: 2px solid #2dd4bf;
            border-right: 7px solid #0d9488;
            border-radius: 22px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 12px 32px rgba(13, 148, 136, 0.14);
            position: relative;
            overflow: hidden;
            animation: fadeSlideUp 0.5s ease-out both;
        }
        .combined-collection-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .combined-collection-head {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .combined-collection-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, #14b8a6, #0f766e);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.34);
            animation: pulseRing 2.8s infinite;
        }
        .combined-collection-heading {
            font-size: 1.22rem;
            font-weight: 800;
            color: #0f766e;
            margin: 0 0 4px 0;
        }
        .combined-collection-formula {
            font-size: 0.92rem;
            font-weight: 600;
            color: #334155;
            line-height: 1.45;
        }
        .combined-collection-amount-box {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
            border: 2px solid #5eead4;
            border-radius: 18px;
            padding: 14px 28px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.12);
            min-width: 260px;
        }
        .combined-collection-amount-label {
            font-size: 0.82rem;
            font-weight: 800;
            color: #0f766e;
            margin-bottom: 4px;
        }
        .combined-collection-val {
            font-size: 2.6rem;
            font-weight: 900;
            color: #0f766e;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        .combined-collection-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 18px;
        }
        .combined-sub-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1.5px solid #99f6e4;
            border-radius: 15px;
            padding: 13px 16px;
            transition: all 0.22s ease;
        }
        .combined-sub-card:hover {
            transform: translateY(-2px);
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(13, 148, 136, 0.10);
        }
        .combined-sub-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .combined-sub-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 0.82rem;
            color: #475569;
            font-weight: 600;
        }
        .combined-progress {
            height: 8px;
            border-radius: 999px;
            background: #cbd5e1;
            overflow: hidden;
            margin-top: 8px;
        }
        .combined-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0d9488, #10b981, #0ea5e9);
            border-radius: 999px;
        }
        @media (max-width: 991px) {
            .combined-collection-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .combined-collection-block { padding: 18px; }
            .combined-collection-amount-box { width: 100%; min-width: 0; }
            .combined-collection-val { font-size: 2rem; }
        }

        /* ── Charts Grid ── */
        .chart-box {
            background: linear-gradient(155deg, rgba(255,255,255,0.98) 45%, rgba(240, 253, 250, 0.95) 100%);
            border-radius: 22px; padding: 22px 24px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06); border: 1.5px solid #99f6e4;
            margin-bottom: 24px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .chart-box:hover {
            box-shadow: 0 14px 34px rgba(13, 148, 136, 0.12);
        }
        .box-title {
            font-size: 1.06rem; font-weight: 800; color: #0f172a; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px;
        }
        .box-title-icon {
            width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
            background: linear-gradient(135deg, #14b8a6, #0284c7);
            color: #fff; display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.05rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.28);
        }

        /* ── Top Pill Nav Tabs ── */
        .custom-tabs {
            background: linear-gradient(135deg, rgba(255,255,255,0.96) 0%, rgba(240, 249, 255, 0.96) 100%);
            backdrop-filter: blur(8px);
            border: 1.5px solid #bae6fd;
            border-radius: 20px;
            padding: 9px;
            margin-bottom: 24px;
            gap: 10px;
            display: flex;
            flex-wrap: wrap;
            box-shadow: 0 10px 28px rgba(2, 132, 199, 0.08);
        }
        .custom-tab-btn {
            background: transparent;
            border: 1.5px solid transparent;
            border-radius: 14px;
            padding: 10px 20px;
            font-weight: 800;
            color: #334155;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 0.93rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .custom-tab-btn .tab-icon-chip {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            color: #ffffff;
            transition: all 0.25s ease;
        }
        .custom-tab-btn:hover {
            transform: translateY(-2px);
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }
        .custom-tab-btn.tab-overview .tab-icon-chip { background: linear-gradient(135deg, #14b8a6, #0f766e); box-shadow: 0 4px 10px rgba(13, 148, 136, 0.28); }
        .custom-tab-btn.tab-month .tab-icon-chip    { background: linear-gradient(135deg, #10b981, #047857); box-shadow: 0 4px 10px rgba(16, 185, 129, 0.28); }
        .custom-tab-btn.tab-jamias .tab-icon-chip   { background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 4px 10px rgba(139, 92, 246, 0.28); }
        .custom-tab-btn.tab-overdue .tab-icon-chip  { background: linear-gradient(135deg, #ef4444, #b91c1c); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.28); }

        .custom-tab-btn.active {
            color: #ffffff;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.35);
            transform: translateY(-1px);
        }
        .custom-tab-btn.tab-month.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
        }
        .custom-tab-btn.tab-jamias.active {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.35);
        }
        .custom-tab-btn.tab-overdue.active {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35);
        }
        .custom-tab-btn.active .tab-icon-chip {
            background: rgba(255, 255, 255, 0.24) !important;
            color: #ffffff !important;
            box-shadow: none !important;
            transform: scale(1.1);
        }
        .tab-count-badge {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            background: #e2e8f0;
            color: #1e293b;
            transition: all 0.2s;
        }
        .custom-tab-btn.active .tab-count-badge {
            background: rgba(255,255,255,0.26);
            color: #fff;
        }
        .tab-count-badge.pulse-danger {
            background: #fee2e2;
            color: #dc2626;
            animation: pulseDanger 2s infinite;
        }

        /* ── Modern Table ── */
        .flow-table-wrap {
            background: #ffffff;
            border-radius: 16px;
            border: 1.5px solid #cbd5e1;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        }
        .flow-table {
            width: 100%; border-collapse: separate; border-spacing: 0;
        }
        .flow-table th {
            background: linear-gradient(90deg, #e0f2fe 0%, #ccfbf1 50%, #ede9fe 100%);
            color: #0f172a; font-weight: 800; font-size: 0.85rem;
            padding: 13px 14px; border-bottom: 2px solid #99f6e4; white-space: nowrap;
        }
        .flow-table th i { margin-left: 5px; font-size: 0.9rem; }
        .flow-table td {
            padding: 12px 14px; border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem; vertical-align: middle; color: #1e293b;
        }
        .flow-table tbody tr:hover td { background: #f0fdfa; }
        .flow-table tbody tr.row-paid td { background: #ecfdf5; }
        .flow-table tbody tr.row-late td { background: #fff1f2; }
        .flow-table tbody tr.row-company td { background: #f0f9ff; }
        .flow-table tbody tr.row-half td { background: #faf5ff; }

        .badge-status {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; white-space: nowrap;
        }
        .badge-status i { font-size: 0.9rem; }
        .badge-paid { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-pending { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-open { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .badge-collected { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-waived { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-company { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .badge-transferred { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }

        .btn-act {
            border: none; border-radius: 10px; padding: 6px 12px; font-size: 0.82rem;
            font-weight: 700; cursor: pointer; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-act i { font-size: 0.95rem; }
        .btn-act:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.14); }
        .btn-pay { background: linear-gradient(135deg, #14b8a6, #0f766e); color: #fff; }
        .btn-pay:hover { color: #fff; }
        .btn-collect { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
        .btn-collect:hover { color: #fff; }
        .btn-payout { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; }
        .btn-payout:hover { color: #fff; }
        .btn-recover { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; }
        .btn-recover:hover { color: #fff; }
        .btn-transfer { background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; }
        .btn-transfer:hover { color: #fff; }
        .btn-edit { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .btn-edit:hover { background: #e0e7ff; color: #3730a3; }
        .btn-cancel { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .btn-cancel:hover { background: #fecaca; color: #991b1b; }
        .btn-statement { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .btn-statement:hover { background: #bae6fd; color: #075985; }
        .btn-locked-pill {
            background: #f1f5f9; color: #64748b; border: 1.5px dashed #cbd5e1;
            border-radius: 10px; padding: 5px 10px; font-size: 0.78rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 5px; cursor: not-allowed;
        }
        .btn-act:disabled { opacity: .5; cursor: not-allowed; }

        /* ── Statement Card Styling ── */
        .statement-card-container,
        .statement-card-container *,
        #memberStatementCard,
        #memberStatementCard * {
            letter-spacing: normal !important;
            letter-spacing: 0px !important;
            word-spacing: normal !important;
            font-feature-settings: normal !important;
            font-family: 'Cairo', 'IBM Plex Sans Arabic', Tahoma, sans-serif !important;
        }
        .statement-card-container {
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1;
            border-radius: 18px;
            padding: 26px 30px;
            color: #1e293b;
            max-width: 820px;
            margin: 0 auto;
            direction: rtl;
            text-align: right;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .statement-header {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 2.5px solid #0f766e; padding-bottom: 14px; margin-bottom: 18px;
        }
        .statement-brand h3 { margin: 0; color: #0f766e; font-weight: 800; font-size: 1.3rem; }
        .statement-brand .sub { color: #64748b; font-size: 0.8rem; margin-top: 2px; }
        .statement-meta { text-align: left; font-size: 0.78rem; color: #64748b; line-height: 1.5; }

        .statement-member-banner {
            background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%);
            border: 1.5px solid #0d9488; border-radius: 12px;
            padding: 14px 18px; margin-bottom: 18px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;
        }
        .statement-member-name { font-size: 1.25rem; font-weight: 800; color: #0f766e; margin: 0; }
        .statement-member-details { font-size: 0.86rem; color: #334155; margin-top: 3px; }

        .statement-kpi-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 18px;
        }
        @media (max-width: 768px) { .statement-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        .statement-kpi-box {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 10px 12px; text-align: center;
        }
        .statement-kpi-box.kpi-teal   { border-top: 3px solid #0d9488; background: #f0fdfa; }
        .statement-kpi-box.kpi-green  { border-top: 3px solid #10b981; background: #f0fdf4; }
        .statement-kpi-box.kpi-orange { border-top: 3px solid #f59e0b; background: #fffbeb; }
        .statement-kpi-box.kpi-red    { border-top: 3px solid #ef4444; background: #fef2f2; }
        .statement-kpi-label { font-size: 0.76rem; color: #64748b; font-weight: 600; margin-bottom: 3px; display: block; }
        .statement-kpi-val { font-size: 1.08rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .statement-kpi-sub { font-size: 0.7rem; color: #64748b; margin-top: 3px; }

        .statement-sec-title {
            font-size: 0.94rem; font-weight: 800; color: #0f766e;
            margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 1.5px solid #e2e8f0;
            display: flex; align-items: center; gap: 6px;
        }
        .statement-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 0.8rem; }
        .statement-table th {
            background: #f1f5f9; color: #334155; font-weight: 800; padding: 7px 8px;
            border: 1px solid #cbd5e1; text-align: right; white-space: nowrap;
        }
        .statement-table td { padding: 6px 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .statement-table tr:nth-child(even) td { background: #fafafa; }

        .statement-badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 0.74rem; font-weight: 700; }
        .statement-badge.badge-success { background: #dcfce7; color: #166534; }
        .statement-badge.badge-danger  { background: #fee2e2; color: #991b1b; }
        .statement-badge.badge-warning { background: #fef3c7; color: #92400e; }
        .statement-badge.badge-info    { background: #e0f2fe; color: #075985; }
        .statement-badge.badge-gray    { background: #f1f5f9; color: #475569; }

        .statement-footer {
            border-top: 1px dashed #cbd5e1; padding-top: 12px; margin-top: 16px;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 0.75rem; color: #64748b;
        }

        .jamia-card {
            background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
            padding: 20px; margin-bottom: 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .jamia-card:hover { border-color: #0d9488; box-shadow: 0 4px 18px rgba(13,148,136,0.08); }

        .jamia-tile {
            background: linear-gradient(145deg, #ffffff 30%, #f0fdfa 72%, #e0f2fe 100%);
            border-radius: 20px; border: 1.5px solid #99f6e4;
            border-top: 5px solid #0d9488;
            padding: 20px; cursor: pointer;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 8px 22px rgba(13, 148, 136, 0.08);
            display: flex; flex-direction: column; justify-content: space-between; height: 100%;
            position: relative; overflow: hidden;
        }
        .jamia-tile:hover {
            border-color: #0d9488; transform: translateY(-5px);
            box-shadow: 0 16px 34px rgba(13, 148, 136, 0.18);
            background: linear-gradient(145deg, #ffffff 20%, #ccfbf1 100%);
        }
        .dir-jamia-tile-wrap .jamia-tile {
            background: linear-gradient(145deg, #ffffff 30%, #f5f3ff 72%, #e0f2fe 100%);
            border-color: #c4b5fd;
            border-top-color: #7c3aed;
            box-shadow: 0 8px 22px rgba(124, 58, 237, 0.08);
        }
        .dir-jamia-tile-wrap .jamia-tile:hover {
            border-color: #7c3aed;
            box-shadow: 0 16px 34px rgba(124, 58, 237, 0.18);
            background: linear-gradient(145deg, #ffffff 20%, #ede9fe 100%);
        }
        .jamia-tile-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;
            background: rgba(255, 255, 255, 0.85); border: 1.5px solid rgba(148, 163, 184, 0.3);
            border-radius: 14px; padding: 10px 12px; margin: 10px 0;
        }
        .jamia-tile-stat small { display: block; color: #475569; font-size: 0.74rem; font-weight: 700; }
        .jamia-tile-stat strong { font-size: 0.96rem; color: #0f172a; font-weight: 800; }
        .jamia-tile-footer {
            border-top: 1.5px dashed #99f6e4; padding-top: 10px; margin-top: 10px;
            display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem;
        }

        /* ── Rich Colorful Jamia Modals ── */
        .jamia-rich-modal .modal-content {
            border-radius: 22px !important;
            overflow: hidden;
            border: 2px solid #99f6e4 !important;
        }
        .jamia-rich-modal .modal-header-month {
            background: linear-gradient(135deg, #065f46 0%, #0d9488 55%, #0284c7 100%);
            color: #ffffff;
            border-bottom: none;
            padding: 18px 24px;
        }
        .jamia-rich-modal .modal-header-dir {
            background: linear-gradient(135deg, #3730a3 0%, #6d28d9 52%, #0d9488 100%);
            color: #ffffff;
            border-bottom: none;
            padding: 18px 24px;
        }
        .jamia-rich-modal .modal-header-icon {
            width: 46px; height: 46px; border-radius: 14px;
            background: rgba(255,255,255,0.22);
            border: 1.5px solid rgba(255,255,255,0.38);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.35rem; color: #ffffff; flex-shrink: 0;
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
        }
        .jamia-rich-modal .modal-body {
            background: linear-gradient(160deg, #ecfdf5 0%, #f0f9ff 50%, #f5f3ff 100%);
        }
        .modal-stats-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px;
        }
        @media (max-width: 768px) {
            .modal-stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .modal-stat-pill {
            background: #ffffff; border-radius: 16px; padding: 12px 14px;
            display: flex; align-items: center; gap: 11px;
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease;
        }
        .modal-stat-pill:hover { transform: translateY(-2px); }
        .modal-stat-pill.st-teal   { background: linear-gradient(145deg, #fff 30%, #ccfbf1 100%); border-color: #5eead4; }
        .modal-stat-pill.st-indigo { background: linear-gradient(145deg, #fff 30%, #e0e7ff 100%); border-color: #a5b4fc; }
        .modal-stat-pill.st-green  { background: linear-gradient(145deg, #fff 30%, #dcfce7 100%); border-color: #86efac; }
        .modal-stat-pill.st-blue   { background: linear-gradient(145deg, #fff 30%, #e0f2fe 100%); border-color: #7dd3fc; }
        .modal-stat-icon {
            width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff;
        }
        .modal-stat-pill.st-teal   .modal-stat-icon { background: linear-gradient(135deg, #14b8a6, #0f766e); }
        .modal-stat-pill.st-indigo .modal-stat-icon { background: linear-gradient(135deg, #6366f1, #4338ca); }
        .modal-stat-pill.st-green  .modal-stat-icon { background: linear-gradient(135deg, #10b981, #047857); }
        .modal-stat-pill.st-blue   .modal-stat-icon { background: linear-gradient(135deg, #0ea5e9, #0284c7); }

        .window-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 13px; border-radius: 11px; font-size: 0.82rem; font-weight: 700;
        }
        .window-chip.open { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .window-chip.closed { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .window-chip.upcoming { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .payout-box {
            border: 1.5px dashed #fda4af; background: linear-gradient(135deg, #ffffff 30%, #fff1f2 100%); border-radius: 14px;
            padding: 13px 16px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .payout-box.company { border-color: #7dd3fc; background: linear-gradient(135deg, #ffffff 30%, #e0f2fe 100%); }
        .payout-box.done { border-color: #6ee7b7; background: linear-gradient(135deg, #ffffff 30%, #d1fae5 100%); }

        .turn-num {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #14b8a6, #0f766e); color: #ffffff;
            display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .85rem;
            box-shadow: 0 3px 8px rgba(13, 148, 136, 0.28);
        }
        .turn-num.company { background: linear-gradient(135deg, #0ea5e9, #0369a1); box-shadow: 0 3px 8px rgba(2, 132, 199, 0.28); }

        /* ── Teal Utility Classes ── */
        .text-teal { color: #0f766e !important; }
        .btn-outline-teal {
            color: #0f766e;
            background: #f0fdfa;
            border: 1.5px solid #5eead4;
            border-radius: 9px;
            transition: all 0.2s ease;
        }
        .btn-outline-teal:hover {
            background: linear-gradient(135deg, #0d9488, #0f766e);
            border-color: #0f766e;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.28);
            transform: translateY(-1px);
        }

        /* ── New Jamia Modal & Members Grid (Rich Colorful Theme) ── */
        #newJamiaModal .modal-content {
            border-radius: 22px !important;
            overflow: hidden;
            border: 2px solid #99f6e4 !important;
        }
        #newJamiaModal .modal-header-new-jamia {
            background: linear-gradient(135deg, #065f46 0%, #0d9488 48%, #0284c7 100%);
            color: #ffffff;
            border-bottom: none;
            padding: 18px 24px;
        }
        #newJamiaModal .modal-body {
            background: linear-gradient(160deg, #ecfdf5 0%, #f0f9ff 52%, #f5f3ff 100%);
        }
        #newJamiaModal .nj-section-card {
            background: #ffffff;
            border: 1.5px solid #99f6e4;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 6px 20px rgba(13, 148, 136, 0.08);
        }
        #newJamiaModal .nj-section-card.nj-members-card {
            border-color: #bae6fd;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.08);
        }
        #newJamiaModal .nj-section-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.94rem;
            font-weight: 800;
            color: #0f766e;
            background: linear-gradient(135deg, #f0fdfa, #e0f2fe);
            border: 1px solid #99f6e4;
            padding: 5px 14px;
            border-radius: 999px;
            margin-bottom: 14px;
        }
        #newJamiaModal .form-label {
            color: #1e293b;
            font-size: 0.86rem;
            margin-bottom: 5px;
        }
        #newJamiaModal .form-control {
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        #newJamiaModal .form-control:focus {
            background: #ffffff;
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.16);
        }
        #newJamiaModal .input-group-text {
            background: linear-gradient(135deg, #ccfbf1, #e0f2fe);
            color: #0f766e;
            font-weight: 800;
            border: 1.5px solid #99f6e4;
            border-radius: 10px 0 0 10px;
        }
        #newJamiaModal .nj-hint-banner {
            background: linear-gradient(135deg, #f0fdfa 0%, #eff6ff 100%);
            border: 1.5px solid #99f6e4;
            border-right: 4px solid #0d9488;
            border-radius: 12px;
            padding: 9px 14px;
            color: #115e59;
            font-size: 0.83rem;
            font-weight: 600;
        }
        #newJamiaModal .btn-shuffle-rich {
            background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 6px 14px;
            font-weight: 700;
            font-size: 0.83rem;
            box-shadow: 0 4px 12px rgba(109, 40, 217, 0.25);
            transition: all 0.2s ease;
        }
        #newJamiaModal .btn-shuffle-rich:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #6d28d9 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(109, 40, 217, 0.35);
        }
        #newJamiaModal .nj-table-wrap {
            border: 1.5px solid #99f6e4;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }
        .members-table { border-collapse: separate; border-spacing: 0; }
        .members-table thead th {
            font-size: .83rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0f766e 0%, #0284c7 100%) !important;
            color: #ffffff !important;
            border-bottom: none !important;
            padding: 11px 10px !important;
            white-space: nowrap;
        }
        .members-table thead th i { color: #ccfbf1 !important; }
        .members-table tbody tr { transition: background 0.15s ease; }
        .members-table tbody tr:nth-child(4n+1) td { background: #ffffff; }
        .members-table tbody tr:nth-child(4n+3) td { background: #f8fafc; }
        .members-table tbody tr:hover td { background: #f0fdfa !important; }
        .members-table td {
            vertical-align: middle;
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .members-table tr.is-company td {
            background: linear-gradient(90deg, #e0f2fe 0%, #f0f9ff 100%) !important;
            border-bottom-color: #bae6fd;
        }
        .members-table tr.is-company td:first-child {
            border-right: 4px solid #0284c7;
        }
        .members-table tr.is-company .turn-num {
            background: linear-gradient(135deg, #0ea5e9, #0369a1);
            box-shadow: 0 3px 8px rgba(2, 132, 199, 0.32);
        }
        .members-table tr.member-split-row td {
            background: #ecfdf5 !important;
            border-bottom-color: #a7f3d0;
        }
        .members-table tr.member-split-summary-row td {
            background: linear-gradient(90deg, #f0fdfa 0%, #eff6ff 100%) !important;
            border-bottom: 2px solid #5eead4 !important;
            padding: 6px 12px !important;
        }
        .share-ratio-box {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .share-ratio-box .input-group-sm .form-control {
            padding: 3px 6px !important;
            font-size: 0.8rem !important;
            text-align: center;
            font-weight: 700;
        }
        .share-ratio-box .input-group-text {
            padding: 3px 6px !important;
            font-size: 0.72rem !important;
            font-weight: 800;
        }
        .split-partner-card {
            background: #ffffff;
            border: 1.5px solid #bae6fd;
            border-radius: 14px;
            padding: 12px 14px;
            box-shadow: 0 3px 10px rgba(2, 132, 199, 0.06);
            transition: border-color 0.2s;
        }
        .split-partner-card.is-company {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-color: #38bdf8;
        }
        .members-table .m-month {
            color: #0369a1 !important;
            font-weight: 700;
        }
        .members-table input.form-control, .members-table input.form-control-sm {
            font-size: .87rem;
            background: #ffffff;
            border: 1.5px solid #dbeafe;
            border-radius: 9px;
        }
        .members-table input.form-control:focus, .members-table input.form-control-sm:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14);
        }
        .members-table .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            cursor: pointer;
            border-color: #0284c7;
        }
        .members-table .form-check-input:checked {
            background-color: #0284c7;
            border-color: #0284c7;
        }
        #newJamiaModal .modal-footer-new-jamia {
            background: linear-gradient(135deg, #f0fdfa 0%, #eff6ff 100%);
            border-top: 1.5px solid #99f6e4;
            padding: 14px 24px;
        }
        #newJamiaModal .btn-save-jamia {
            background: linear-gradient(135deg, #059669 0%, #0d9488 55%, #0284c7 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 10px 26px;
            font-weight: 800;
            box-shadow: 0 6px 18px rgba(13, 148, 136, 0.32);
            transition: all 0.2s ease;
        }
        #newJamiaModal .btn-save-jamia:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(13, 148, 136, 0.42);
            color: #ffffff;
        }
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
            <button type="button" class="btn-hero" onclick="openMemberStatementModal()">
                <i class="fa-solid fa-file-invoice-dollar"></i> كشف حساب عضو
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

    {{-- ═══ Top Tabs Navigation ═══ --}}
    <div class="custom-tabs">
        <button class="custom-tab-btn tab-overview active" data-tab="tab-overview" onclick="switchTab('tab-overview', this)">
            <span class="tab-icon-chip"><i class="fa-solid fa-chart-pie"></i></span>
            <span>الرئيسية (المؤشرات والشارتات)</span>
        </button>
        <button class="custom-tab-btn tab-month" data-tab="tab-month" onclick="switchTab('tab-month', this)">
            <span class="tab-icon-chip"><i class="fa-solid fa-calendar-check"></i></span>
            <span>تحصيل شهر {{ $monthDate->locale('ar')->isoFormat('MMMM') }}</span>
            <span class="tab-count-badge">{{ $monthJamias->count() }} جمعية</span>
        </button>
        <button class="custom-tab-btn tab-jamias" data-tab="tab-jamias" onclick="switchTab('tab-jamias', this)">
            <span class="tab-icon-chip"><i class="fa-solid fa-users-viewfinder"></i></span>
            <span>دليل الجمعيات والأعضاء</span>
            <span class="tab-count-badge">{{ $jamias->count() }}</span>
        </button>
        <button class="custom-tab-btn tab-overdue" data-tab="tab-overdue" onclick="switchTab('tab-overdue', this)">
            <span class="tab-icon-chip"><i class="fa-solid fa-bell"></i></span>
            <span>المتأخرين</span>
            <span class="tab-count-badge {{ $overdueInstallments->count() > 0 ? 'pulse-danger' : '' }}">{{ $overdueInstallments->count() }}</span>
        </button>
    </div>

    {{-- ═══ Tab 0: الصفحة الرئيسية للبلوكات والشارتات ═══ --}}
    <div id="tab-overview" class="tab-content-pane">
        @php
            $combined1to10Total          = round((float) $expectedCollection + (float) ($instSystem1to10['total'] ?? 0), 2);
            $combined1to10Collected      = round((float) $collectedThisMonth + (float) ($instSystem1to10['collected'] ?? 0), 2);
            $combined1to10Remaining      = round((float) $remainingCollection + (float) ($instSystem1to10['remaining'] ?? 0), 2);
            $combined1to10Pct            = $combined1to10Total > 0
                ? min(100, (int) round(($combined1to10Collected / $combined1to10Total) * 100))
                : 0;
            $partnersMonthlyWithdrawals  = $partnersMonthlyWithdrawals ?? ['total' => 0.0, 'partners_count' => 0, 'partners' => collect()];
            $partnersMonthlyOut          = round((float) ($partnersMonthlyWithdrawals['total'] ?? 0), 2);
            $totalMonthlyOutWithPartners = round((float) $payoutDue + $partnersMonthlyOut, 2);
            $net1to10Overall             = round($combined1to10Total - $totalMonthlyOutWithPartners, 2);
            $totalRemainingOutEstimate   = round((float) $payoutRemaining + $partnersMonthlyOut, 2);
        @endphp

        {{-- ═══ 3 Unified Executive Summary Blocks (اللي داخل ← اللي خارج ← الصافي) ═══ --}}
        <div class="flow-summary-grid">
            {{-- 1. اللي داخل ليك (من يوم 1 ليوم 10) --}}
            <div class="flow-big-card is-in" id="combinedCollection1To10Block">
                <div>
                    <div class="flow-big-head">
                        <div class="flow-big-title-wrap">
                            <div class="kpi-icon teal"><i class="fa-solid fa-circle-arrow-down"></i></div>
                            <div>
                                <h6 class="flow-big-title" style="color:#065f46;">اللي داخل ليك (1–10)</h6>
                                <div class="flow-big-subtitle">إجمالي تحصيل 1–10 (جمعيات + أقساط)</div>
                            </div>
                        </div>
                        <span class="flow-status-pill is-in">اتحصّل {{ $combined1to10Pct }}%</span>
                    </div>

                    <div class="flow-big-main mt-3">
                        <div class="flow-big-val text-success">
                            {{ number_format($combined1to10Total, 2) }} <small>ج</small>
                        </div>
                        <div class="flow-big-meta">
                            <span><i class="fa-solid fa-circle-check text-success me-1"></i> اتحصّل: <b class="text-success">{{ number_format($combined1to10Collected, 0) }} ج</b></span>
                            <span><i class="fa-regular fa-clock text-danger me-1"></i> متبقي: <b class="text-danger">{{ number_format($combined1to10Remaining, 0) }} ج</b></span>
                        </div>
                        <div class="flow-progress-track">
                            <div class="flow-progress-fill" style="width: {{ $combined1to10Pct }}%;"></div>
                        </div>
                    </div>
                </div>

                <div class="flow-breakdown-list">
                    {{-- منظومة الأقساط 1-10 --}}
                    <div class="flow-bd-row">
                        <div class="flow-bd-top">
                            <span class="flow-bd-label">
                                <i class="fa-solid fa-file-invoice-dollar" style="color:#4f46e5;"></i>
                                <span>أقساط منظومة الأقساط (1–10)</span>
                                @if(\Illuminate\Support\Facades\Route::has('installments.index'))
                                    <a href="{{ route('installments.index') }}" class="flow-mini-link bg-primary-subtle text-primary border border-primary-subtle">
                                        شاشة الأقساط <i class="fa-solid fa-arrow-up-right-from-square fa-2xs"></i>
                                    </a>
                                @endif
                            </span>
                            <span class="flow-bd-val" style="color:#4f46e5;">{{ number_format($instSystem1to10['total'], 2) }} ج</span>
                        </div>
                        <div class="flow-bd-sub">
                            <span>اتحصّل: <b class="text-success">{{ number_format($instSystem1to10['collected'], 0) }} ج</b> · متبقي: <b class="text-danger">{{ number_format($instSystem1to10['remaining'], 0) }} ج</b></span>
                            <span><b class="text-dark">{{ $instSystem1to10['contracts_count'] }} عقد</b> ({{ $instSystem1to10['customers_count'] }} عميل)</span>
                        </div>
                    </div>

                    {{-- أقساط الجمعيات من الأعضاء 1-10 --}}
                    <div class="flow-bd-row">
                        <div class="flow-bd-top">
                            <span class="flow-bd-label">
                                <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                                <span>أقساط الجمعيات (الأعضاء 1–10)</span>
                            </span>
                            <span class="flow-bd-val text-success">{{ number_format($expectedCollection, 2) }} ج</span>
                        </div>
                        <div class="flow-bd-sub">
                            <span>اتسدد: <b class="text-success">{{ number_format($collectedThisMonth, 0) }} ج</b> ({{ $paidMembersCount }})</span>
                            <span>متبقي: <b class="text-danger">{{ number_format($remainingCollection, 0) }} ج</b> ({{ $unpaidMembersCount }})</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. اللي خارج منك (من يوم 1 ليوم 10) --}}
            <div class="flow-big-card is-out">
                <div>
                    <div class="flow-big-head">
                        <div class="flow-big-title-wrap">
                            <div class="kpi-icon rose"><i class="fa-solid fa-circle-arrow-up"></i></div>
                            <div>
                                <h6 class="flow-big-title" style="color:#9f1239;">اللي خارج منك (1–10)</h6>
                                <div class="flow-big-subtitle">قبض الجمعيات للأعضاء + مسحوبات الشركاء</div>
                            </div>
                        </div>
                        <span class="flow-status-pill is-out">خارج شهري</span>
                    </div>

                    <div class="flow-big-main mt-3">
                        <div class="flow-big-val" style="color:#be123c;">
                            {{ number_format($totalMonthlyOutWithPartners, 2) }} <small>ج</small>
                        </div>
                        <div class="flow-big-meta">
                            <span><i class="fa-solid fa-check-double text-success me-1"></i> اتصرف للجمعيات: <b class="text-success">{{ number_format($payoutDone, 0) }} ج</b></span>
                            <span><i class="fa-solid fa-hourglass-half text-danger me-1"></i> متبقي صرفه: <b class="text-danger">{{ number_format($totalRemainingOutEstimate, 0) }} ج</b></span>
                        </div>
                    </div>
                </div>

                <div class="flow-breakdown-list">
                    {{-- مسحوبات الشركاء الشهرية --}}
                    <div class="flow-bd-row" id="partnersMonthlyWithdrawalsBlock">
                        <div class="flow-bd-top">
                            <span class="flow-bd-label">
                                <i class="fa-solid fa-money-bill-transfer" style="color:#e11d48;"></i>
                                <span>مسحوبات الشركاء الشهرية (خارج)</span>
                                @if(\Illuminate\Support\Facades\Route::has('partners.index'))
                                    <a href="{{ route('partners.index') }}" class="flow-mini-link bg-danger-subtle text-danger border border-danger-subtle">
                                        الشركاء <i class="fa-solid fa-arrow-up-right-from-square fa-2xs"></i>
                                    </a>
                                @endif
                            </span>
                            <span class="flow-bd-val" style="color:#be123c;">{{ number_format($partnersMonthlyOut, 2) }} ج</span>
                        </div>
                        <div class="flow-bd-sub">
                            <span>
                                عدد الشركاء: <b class="text-dark">{{ $partnersMonthlyWithdrawals['partners_count'] }} شريك</b>
                                @if($partnersMonthlyWithdrawals['partners_count'] > 0)
                                    <button type="button" class="btn btn-link p-0 ms-1 fw-bold text-decoration-none" style="font-size:0.74rem; color:#be123c; vertical-align:baseline;" data-bs-toggle="modal" data-bs-target="#partnersMonthlyWithdrawalsModal">
                                        (عرض التفاصيل)
                                    </button>
                                @endif
                            </span>
                            <span class="text-muted">سحب شهري ثابت</span>
                        </div>
                    </div>

                    {{-- قبض الجمعيات للأعضاء --}}
                    <div class="flow-bd-row">
                        <div class="flow-bd-top">
                            <span class="flow-bd-label">
                                <i class="fa-solid fa-sack-dollar text-danger"></i>
                                <span>قبض الجمعيات للأعضاء</span>
                            </span>
                            <span class="flow-bd-val text-danger">{{ number_format($payoutDue, 2) }} ج</span>
                        </div>
                        <div class="flow-bd-sub">
                            <span>اتصرف: <b class="text-success">{{ number_format($payoutDone, 0) }} ج</b> ({{ $payoutDoneCount }})</span>
                            <span>متبقي: <b class="text-danger">{{ number_format($payoutRemaining, 0) }} ج</b> ({{ $payoutPendingCount }})</span>
                        </div>
                    </div>

                    {{-- التزامات أسهم الشركة (مرجعي داخل الجمعيات) --}}
                    <div class="flow-bd-row is-ref">
                        <div class="flow-bd-top">
                            <span class="flow-bd-label" style="color:#5b21b6;">
                                <i class="fa-solid fa-building-columns" style="color:#7c3aed;"></i>
                                <span>التزامات أسهم الشركة (داخلي)</span>
                            </span>
                            <span class="flow-bd-val" style="color:#7c3aed;">{{ number_format($companyInstallmentsDue, 2) }} ج</span>
                        </div>
                        <div class="flow-bd-sub">
                            <span><b class="text-dark">{{ $companySharesMonthCount }} سهم</b> في {{ $globalStats['active_jamias_count'] }} جمعية · سُدد: <b class="text-success">{{ number_format($companyInstallmentsPaid, 0) }} ج</b></span>
                            <span class="text-muted">غير مضاف للخارج النقدي</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. الصافي المتوقع (من يوم 1 ليوم 10) --}}
            <div class="flow-big-card is-net {{ $net1to10Overall < 0 ? 'is-deficit' : '' }}">
                <div>
                    <div class="flow-big-head">
                        <div class="flow-big-title-wrap">
                            <div class="kpi-icon blue"><i class="fa-solid fa-scale-balanced"></i></div>
                            <div>
                                <h6 class="flow-big-title" style="color:#0369a1;">الصافي المتوقع (1–10)</h6>
                                <div class="flow-big-subtitle">اللي داخل ليك ناقص اللي خارج منك</div>
                            </div>
                        </div>
                        <span class="flow-status-pill {{ $net1to10Overall >= 0 ? 'is-net' : 'is-bad' }}">
                            {{ $net1to10Overall >= 0 ? 'فائض سيولة' : 'عجز سيولة' }}
                        </span>
                    </div>

                    <div class="flow-big-main mt-3">
                        <div class="flow-big-val {{ $net1to10Overall >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $net1to10Overall >= 0 ? '+' : '' }}{{ number_format($net1to10Overall, 2) }} <small>ج</small>
                        </div>
                        <div class="flow-big-meta">
                            <span>داخل: <b class="text-success">{{ number_format($combined1to10Total, 0) }} ج</b></span>
                            <span>− خارج: <b class="text-danger">{{ number_format($totalMonthlyOutWithPartners, 0) }} ج</b></span>
                        </div>
                    </div>
                </div>

                <div class="flow-breakdown-list">
                    {{-- حسبة الجمعيات وحدها --}}
                    <div class="flow-bd-row">
                        <div class="flow-bd-top">
                            <span class="flow-bd-label">
                                <i class="fa-solid fa-handshake text-primary"></i>
                                <span>حسبة الجمعيات الشهر ده</span>
                            </span>
                            <span class="flow-bd-val {{ $netGeneralExpected >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $netGeneralExpected >= 0 ? '+' : '' }}{{ number_format($netGeneralExpected, 2) }} ج
                            </span>
                        </div>
                        <div class="flow-bd-sub">
                            <span>تحصيل الأعضاء: <b class="text-success">{{ number_format($totalInExpected, 0) }} ج</b></span>
                            <span>قبض الأعضاء: <b class="text-danger">{{ number_format($totalOutExpected, 0) }} ج</b></span>
                        </div>
                    </div>

                    {{-- صافي موقف أسهم الشركة --}}
                    <div class="flow-bd-row">
                        <div class="flow-bd-top">
                            <span class="flow-bd-label">
                                <i class="fa-solid fa-wallet text-teal"></i>
                                <span>صافي موقف أسهم الشركة</span>
                            </span>
                            <span class="flow-bd-val {{ $companyNetExpected >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $companyNetExpected >= 0 ? '+' : '' }}{{ number_format($companyNetExpected, 2) }} ج
                            </span>
                        </div>
                        <div class="flow-bd-sub">
                            <span>قبض دور الشركة: <b class="text-success">{{ number_format($companyPayoutDue, 0) }} ج</b> ({{ $companyPayoutCount }})</span>
                            <span>التزاماتها: <b class="text-danger">{{ number_format($companyInstallmentsDue, 0) }} ج</b></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ شريط تنبيه رفيع للمتأخرين عن السداد بعد يوم 10 (يظهر فقط لو في متأخرين) ═══ --}}
        @if($lateCountThisMonth > 0 || ($globalStats['overdue_count'] ?? 0) > 0)
            <div class="overdue-alert-strip">
                <div class="overdue-alert-info">
                    <span class="overdue-alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <span>
                        المتأخرين عن السداد (بعد يوم 10):
                        <strong class="text-danger">{{ $lateCountThisMonth }} قسط هذا الشهر</strong>
                        بمبلغ <strong class="text-danger">{{ number_format($lateAmountThisMonth, 0) }} ج</strong>
                    </span>
                    <span class="badge bg-white text-danger border border-danger-subtle rounded-pill px-3 py-1" style="font-size:0.78rem;">
                        كل المتأخرات: {{ $globalStats['overdue_count'] }} قسط ({{ number_format($globalStats['overdue_amount'], 0) }} ج)
                    </span>
                </div>
                <button type="button" class="btn btn-sm fw-bold rounded-pill px-3 text-white" style="background: linear-gradient(135deg, #ea580c, #c2410c); border:none;" onclick="switchTab('tab-overdue', document.querySelector('.custom-tab-btn.tab-overdue'))">
                    <i class="fa-solid fa-bell me-1"></i> عرض المتأخرين
                </button>
            </div>
        @endif

        {{-- ═══ Annual Charts ═══ --}}
        <div class="row g-3 mb-2">
            <div class="col-lg-8">
                <div class="chart-box h-100 mb-0">
                    <div class="box-title">
                        <span class="box-title-icon"><i class="fa-solid fa-chart-column"></i></span>
                        <span>التحصيل المتوقع vs القبض المصروف لسنة {{ $monthDate->year }}</span>
                    </div>
                    <div style="height: 290px; position: relative;">
                        <canvas id="cashflowYearChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-box h-100 mb-0">
                    <div class="box-title">
                        <span class="box-title-icon"><i class="fa-solid fa-chart-pie"></i></span>
                        <span>موقف الشهر</span>
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
    </div>

    {{-- ═══ Tab 1: تحصيل الشهر لكل جمعية (مربعات + بوبب تفاصيل) ═══ --}}
    <div id="tab-month" class="tab-content-pane" style="display:none;">
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
                                            @if(\App\Models\JamiaShare::isPartial($p->share_fraction ?? 1)) <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0" style="font-size:0.68rem;">{{ \App\Models\JamiaShare::formatPercent($p->share_fraction ?? 1) }}</span> @endif
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
                <div class="modal fade jamia-rich-modal month-jamia-modal" id="{{ $mModalId }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
                            <div class="modal-header modal-header-month">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="modal-header-icon"><i class="fa-solid fa-calendar-check"></i></span>
                                    <div>
                                        <h5 class="modal-title fw-bold mb-1" style="color:#ffffff;">{{ $jamia->name }} — تحصيل {{ $monthDate->locale('ar')->isoFormat('MMMM YYYY') }}</h5>
                                        <small class="d-flex flex-wrap gap-2" style="color:rgba(255,255,255,0.9);">
                                            <span><i class="fa-solid fa-layer-group me-1"></i> الشهر <b>{{ $jamia->month_index }}</b> من <b>{{ $jamia->duration_months }}</b></span>
                                            <span>|</span>
                                            <span><i class="fa-solid fa-coins me-1"></i> قسط العضو: <b>{{ number_format($jamia->monthly_installment, 2) }} ج</b></span>
                                            <span>|</span>
                                            <span><i class="fa-solid fa-hand-holding-dollar me-1"></i> القبض: <b>{{ number_format($jamia->total_amount, 2) }} ج</b></span>
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if(now()->lt($winFrom))
                                        <span class="window-chip upcoming"><i class="fa-regular fa-hourglass"></i> التحصيل من يوم {{ $startDay }} لغاية يوم {{ $endDay }} — لسه ما بدأش</span>
                                    @elseif(now()->lte($winTo))
                                        <span class="window-chip open"><i class="fa-solid fa-hourglass-half"></i> التحصيل (يوم {{ $startDay }} ← {{ $endDay }}) — {{ $daysLeft === 0 ? 'آخر يوم النهارده!' : "باقي {$daysLeft} يوم" }}</span>
                                    @else
                                        <span class="window-chip closed"><i class="fa-solid fa-lock"></i> انتهت فترة التحصيل يوم {{ $endDay }}</span>
                                    @endif
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                            </div>

                            <div class="modal-body p-4">
                                {{-- Progress --}}
                                <div class="mb-3 p-3 rounded-3" style="background: rgba(255,255,255,0.88); border: 1.5px solid #99f6e4;">
                                    <div class="d-flex justify-content-between mb-1" style="font-size:0.84rem;">
                                        <span><i class="fa-solid fa-user-check text-teal me-1"></i> سددوا: <b class="text-success">{{ $paidCnt }}</b> من <b>{{ $allCnt }}</b> عضو
                                            @if($jamia->company_shares_in > 0)
                                                <span class="text-primary fw-bold">(+ {{ $jamia->company_shares_in }} سهم لـ{{ $companyName }})</span>
                                            @endif
                                        </span>
                                        <span><i class="fa-solid fa-coins text-warning me-1"></i> إجمالي الملموم من الأعضاء: <b class="text-success">{{ number_format($insts->where('is_paid', true)->sum('amount'), 2) }} ج</b> من <b>{{ number_format($insts->sum('amount'), 2) }} ج</b></span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 999px; background: #e2e8f0;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%; border-radius: 999px;"></div>
                                    </div>
                                </div>

                                @if($insts->isEmpty())
                                    <div class="text-center text-muted py-3 border rounded-3 bg-white">كل أسهم الجمعية دي ملك {{ $companyName }} — مفيش تحصيل أقساط من أعضاء.</div>
                                @else
                                    {{-- In-modal Filter & Search --}}
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-dark modal-filter-btn active" onclick="filterModalRows({{ $jamia->id }}, 'all', this)"><i class="fa-solid fa-list me-1"></i>الكل ({{ $allCnt }})</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger modal-filter-btn" onclick="filterModalRows({{ $jamia->id }}, 'unpaid', this)"><i class="fa-regular fa-clock me-1"></i>لسه ({{ $unpaid->count() }})</button>
                                            <button type="button" class="btn btn-sm btn-outline-success modal-filter-btn" onclick="filterModalRows({{ $jamia->id }}, 'paid', this)"><i class="fa-solid fa-check me-1"></i>دفعوا ({{ $paidCnt }})</button>
                                            <button type="button" class="btn btn-sm btn-outline-warning modal-filter-btn" onclick="filterModalRows({{ $jamia->id }}, 'late', this)"><i class="fa-solid fa-triangle-exclamation me-1"></i>متأخرين</button>
                                        </div>
                                        <input type="search" class="form-control form-control-sm" style="max-width:240px;"
                                               id="modalSearch-{{ $jamia->id }}" placeholder="بحث داخل الجمعية..." oninput="filterModalRows({{ $jamia->id }}, null, null)">
                                    </div>

                                    <div class="flow-table-wrap mb-2">
                                        <div class="table-responsive">
                                            <table class="flow-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width:36px;">
                                                            <input type="checkbox" class="form-check-input" title="تحديد كل اللي لسه ما دفعوش" onchange="toggleAllUnpaid({{ $jamia->id }}, this.checked)" @disabled($unpaid->isEmpty())>
                                                        </th>
                                                        <th><i class="fa-solid fa-hashtag text-teal"></i> الدور</th>
                                                        <th><i class="fa-solid fa-user" style="color:#4f46e5;"></i> العضو</th>
                                                        <th><i class="fa-solid fa-phone" style="color:#0284c7;"></i> الهاتف</th>
                                                        <th><i class="fa-solid fa-coins text-warning"></i> القسط</th>
                                                        <th><i class="fa-solid fa-circle-info text-success"></i> الحالة</th>
                                                        <th><i class="fa-solid fa-bolt text-danger"></i> الإجراء</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($insts as $inst)
                                                        @php
                                                            $st = $inst->status;
                                                            $isCompInst = !empty($inst->is_company);
                                                            $isHalfInst = \App\Models\JamiaShare::isPartial($inst->share_fraction ?? 1);
                                                            $pctInstLbl = \App\Models\JamiaShare::formatPercent($inst->share_fraction ?? 1);
                                                            $mRowClass = $inst->is_paid
                                                                ? 'row-paid'
                                                                : ($isCompInst
                                                                    ? 'row-company'
                                                                    : ($st['key'] === 'late' ? 'row-late' : ($isHalfInst ? 'row-half' : '')));
                                                        @endphp
                                                        <tr class="month-member-row modal-row-{{ $jamia->id }} {{ $mRowClass }}"
                                                            data-paid="{{ $inst->is_paid ? '1' : '0' }}"
                                                            data-late="{{ $st['key'] === 'late' ? '1' : '0' }}"
                                                            data-search="{{ mb_strtolower($inst->share_label . ' ' . $inst->member_phone) }}">
                                                        <td>
                                                            @if(!$inst->is_paid)
                                                                <input type="checkbox" class="form-check-input sel-inst sel-jamia-{{ $jamia->id }}"
                                                                       value="{{ $inst->id }}" data-amount="{{ $inst->amount }}"
                                                                       data-name="{{ $inst->share_label }}" data-company="{{ $isCompInst ? '1' : '0' }}" onchange="refreshBulkBar({{ $jamia->id }})">
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="turn-num {{ $isCompInst ? 'company' : '' }}">{{ $inst->share_number }}</span>
                                                            @if($isHalfInst)
                                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0 ms-1" style="font-size:0.68rem;">{{ $pctInstLbl }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-bold">
                                                            {{ $inst->share_label }}
                                                            @if($isCompInst)
                                                                <span class="badge-status badge-company ms-1" style="font-size:0.72rem;"><i class="fa-solid fa-building"></i> سهم الشركة</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted" dir="ltr" style="text-align:right;">{{ $inst->member_phone ?: '—' }}</td>
                                                        <td><strong>{{ number_format($inst->amount, 2) }} ج</strong></td>
                                                        <td>
                                                            @if($st['key'] === 'paid' || $st['key'] === 'paid_late')
                                                                <span class="badge-status badge-paid"><i class="fa-solid fa-check"></i> {{ $isCompInst ? 'سُددت حصة الشركة' : 'دفع' }}</span>
                                                                <div style="font-size:0.74rem; color:#64748b;" class="mt-1">
                                                                    {{ \Carbon\Carbon::parse($inst->paid_at)->format('Y/m/d') }} — {{ $isCompInst ? 'من: ' : 'في: ' }}{{ $inst->paid_account_name ?? 'الخزنة' }}
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
                                                            <div class="d-flex align-items-center gap-1">
                                                                @if(!$isCompInst)
                                                                    <button type="button" class="btn-act btn-statement" title="كشف حساب العضو (صورة / واتساب)"
                                                                            onclick="openMemberStatementModal({ shareId: {{ $inst->share_id }}, memberName: @js($inst->share_label), memberPhone: @js($inst->member_phone), parentModalId: '{{ $mModalId }}' })">
                                                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                                                    </button>
                                                                @endif
                                                                @if(!$inst->is_paid)
                                                                    @if($isCompInst)
                                                                        <button type="button" class="btn-act" style="background:#7c3aed; color:#fff;"
                                                                                onclick="openCollectModal([{{ $inst->id }}], {{ $inst->amount }}, @js($jamia->name . ' — سداد حصة ' . $inst->share_label), '{{ $mModalId }}', true)">
                                                                            <i class="fa-solid fa-building-circle-arrow-right"></i> إخراج حصة الشركة
                                                                        </button>
                                                                    @else
                                                                        <button type="button" class="btn-act btn-collect"
                                                                                onclick="openCollectModal([{{ $inst->id }}], {{ $inst->amount }}, @js($jamia->name . ' — ' . $inst->share_label), '{{ $mModalId }}', false)">
                                                                            <i class="fa-solid fa-hand-holding-dollar"></i> تحصيل
                                                                        </button>
                                                                    @endif
                                                                @else
                                                                    <form method="POST" action="{{ route('cashflows.cancel_payment') }}"
                                                                          onsubmit="if(confirm('إلغاء سداد/تحصيل القسط ده وعكس المبلغ مع الخزنة؟')){ rememberReopenModal('{{ $mModalId }}'); return true; } return false;" style="display:inline;">
                                                                        @csrf
                                                                        <input type="hidden" name="installment_id" value="{{ $inst->id }}">
                                                                        <button type="submit" class="btn-act btn-cancel" title="إلغاء">
                                                                            <i class="fa-solid fa-rotate-left"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>

                                    {{-- Bulk Bar --}}
                                    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2 p-2 px-3 rounded-3" style="background: rgba(255,255,255,0.9); border: 1.5px solid #cbd5e1;">
                                        <small class="text-muted fw-bold" id="bulk-info-{{ $jamia->id }}"><i class="fa-solid fa-circle-info text-teal me-1"></i> حدد الأعضاء اللي دفعوا علشان تسجل تحصيلهم مرة واحدة.</small>
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
                                                        @if(\App\Models\JamiaShare::isPartial($p->share_fraction ?? 1))
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill ms-1" style="font-size:0.74rem;"><i class="fa-solid fa-user-group me-1"></i> حصة {{ \App\Models\JamiaShare::formatPercent($p->share_fraction ?? 1) }}</span>
                                                        @endif
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
                                                            <i class="fa-solid fa-building-circle-check"></i> تأكيد استلام الشركة
                                                        </button>
                                                        @if(!$p->is_transferred && $jamia->duration_months >= 2)
                                                            <button type="button" class="btn-act btn-transfer"
                                                                    onclick="openTransferModal({ shareId: {{ $p->id }}, shareNumber: {{ $p->share_number }}, jamiaName: @js($jamia->name), startDate: '{{ \Carbon\Carbon::parse($jamia->start_date)->format('Y-m-d') }}', duration: {{ $jamia->duration_months }}, installment: {{ round((float) $jamia->monthly_installment * (float) ($p->share_fraction ?? 1), 2) }}, payoutAmount: {{ (float) $p->payout_amount }}, parentModalId: '{{ $mModalId }}' })">
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
                                            @if($jamia->duration_months <= 1)
                                                {{ \Carbon\Carbon::parse($jamia->start_date)->locale('ar')->isoFormat('MMMM YYYY') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($jamia->start_date)->locale('ar')->isoFormat('MMM YYYY') }} ← {{ \Carbon\Carbon::parse($jamia->start_date)->addMonths($jamia->duration_months - 1)->locale('ar')->isoFormat('MMM YYYY') }}
                                            @endif
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
                <div class="modal fade jamia-rich-modal dir-jamia-modal" id="{{ $dModalId }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
                            <div class="modal-header modal-header-dir">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="modal-header-icon"><i class="fa-solid fa-users-viewfinder"></i></span>
                                    <div>
                                        <h5 class="modal-title fw-bold mb-1" style="color:#ffffff;">
                                            {{ $jamia->name }}
                                            @if($jamia->status !== 'active')
                                                <span class="badge bg-warning text-dark ms-1" style="font-size:.72rem;">{{ $jamia->status === 'completed' ? 'منتهية' : 'ملغاة' }}</span>
                                            @endif
                                        </h5>
                                        <small style="color:rgba(255,255,255,0.9);">
                                            <i class="fa-regular fa-calendar me-1"></i>
                                            @if($jamia->duration_months <= 1)
                                                شهر {{ \Carbon\Carbon::parse($jamia->start_date)->locale('ar')->isoFormat('MMMM YYYY') }}
                                            @else
                                                من {{ \Carbon\Carbon::parse($jamia->start_date)->locale('ar')->isoFormat('MMMM YYYY') }}
                                                لغاية {{ \Carbon\Carbon::parse($jamia->start_date)->addMonths($jamia->duration_months - 1)->locale('ar')->isoFormat('MMMM YYYY') }}
                                            @endif
                                            | <i class="fa-solid fa-clock me-1"></i> التحصيل من يوم {{ $jamia->collection_start_day ?? 1 }} لغاية يوم {{ $jamia->collection_end_day ?? 10 }}
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-light text-primary fw-bold rounded-3" title="تعديل إعدادات الجمعية"
                                            onclick="openEditJamia(this, '{{ $dModalId }}')"
                                            data-id="{{ $jamia->id }}"
                                            data-name="{{ $jamia->name }}"
                                            data-start-day="{{ $jamia->collection_start_day ?? 1 }}"
                                            data-end-day="{{ $jamia->collection_end_day ?? 10 }}"
                                            data-status="{{ $jamia->status }}"
                                            data-notes="{{ $jamia->notes }}">
                                        <i class="fa-solid fa-sliders"></i> إعدادات
                                    </button>
                                    <a href="{{ route('cashflows.print_jamia', $jamia->id) }}" target="_blank" class="btn btn-sm btn-light text-dark fw-bold rounded-3" title="طباعة كشف الجمعية">
                                        <i class="fa-solid fa-print"></i> طباعة
                                    </a>
                                    @if(!$jamia->calc_has_cash_moves)
                                        <form method="POST" action="{{ route('cashflows.destroy', $jamia->id) }}" onsubmit="return confirm('هل تريد حذف هذه الجمعية نهائياً؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded-3" title="حذف الجمعية">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                            </div>

                            <div class="modal-body p-4">
                                {{-- Stats Bar --}}
                                <div class="modal-stats-grid">
                                    <div class="modal-stat-pill st-teal">
                                        <span class="modal-stat-icon"><i class="fa-solid fa-sack-dollar"></i></span>
                                        <div>
                                            <small class="text-muted d-block fw-bold">قيمة القبض</small>
                                            <strong class="text-dark" style="font-size:1rem;">{{ number_format($jamia->total_amount, 2) }} ج</strong>
                                        </div>
                                    </div>
                                    <div class="modal-stat-pill st-indigo">
                                        <span class="modal-stat-icon"><i class="fa-solid fa-hourglass-half"></i></span>
                                        <div>
                                            <small class="text-muted d-block fw-bold">المدة والقسط</small>
                                            <strong class="text-dark" style="font-size:0.95rem;">{{ $jamia->duration_months }} شهر ({{ number_format($jamia->monthly_installment, 2) }} ج)</strong>
                                        </div>
                                    </div>
                                    <div class="modal-stat-pill st-green">
                                        <span class="modal-stat-icon"><i class="fa-solid fa-circle-check"></i></span>
                                        <div>
                                            <small class="text-muted d-block fw-bold">اتحصل من المطلوب</small>
                                            <strong class="text-success" style="font-size:1rem;">{{ number_format($jamia->calc_total_collected, 0) }} / {{ number_format($jamia->calc_total_due, 0) }} ج</strong>
                                        </div>
                                    </div>
                                    <div class="modal-stat-pill st-blue">
                                        <span class="modal-stat-icon"><i class="fa-solid fa-building"></i></span>
                                        <div>
                                            <small class="text-muted d-block fw-bold">أسهم {{ $companyName }}</small>
                                            <strong class="text-teal" style="font-size:1rem;">{{ $jamia->company_shares_in }} (من {{ $jamia->members_count }})</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Security Notice: Financial actions locked in Directory --}}
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 px-3 py-2 rounded-3"
                                     style="background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fcd34d; color: #92400e; font-size: 0.84rem;">
                                    <div class="d-flex align-items-center gap-2 fw-bold">
                                        <i class="fa-solid fa-shield-halved text-warning" style="font-size:1.05rem;"></i>
                                        <span>الدليل للمراجعة وتعديل البيانات وكشف الحساب فقط — الحركات المالية (الصرف والاستلام والتنازل والإلغاء) مقفولة هنا وتُنفذ من شاشة "تحصيل الشهر".</span>
                                    </div>
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="fa-solid fa-lock me-1"></i> حماية مالية</span>
                                </div>

                                {{-- Members Directory Table --}}
                                <div class="flow-table-wrap">
                                    <div class="table-responsive">
                                        <table class="flow-table">
                                            <thead>
                                                <tr>
                                                    <th><i class="fa-solid fa-hashtag text-teal"></i> الدور</th>
                                                    <th><i class="fa-regular fa-calendar-days" style="color:#4f46e5;"></i> شهر القبض</th>
                                                    <th><i class="fa-solid fa-users" style="color:#7c3aed;"></i> الاسم والحالة</th>
                                                    <th><i class="fa-solid fa-phone" style="color:#0284c7;"></i> الهاتف</th>
                                                    <th><i class="fa-solid fa-coins text-success"></i> المدفوع</th>
                                                    <th><i class="fa-solid fa-triangle-exclamation text-warning"></i> متأخرات</th>
                                                    <th><i class="fa-solid fa-vault text-teal"></i> القبض / الخزنة</th>
                                                    <th><i class="fa-solid fa-gears" style="color:#4f46e5;"></i> إجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($jamia->shares as $sh)
                                                    @php
                                                        $paidCountForShare = $jamia->paid_by_share[$sh->id] ?? 0;
                                                        $isHalfShare = \App\Models\JamiaShare::isPartial($sh->share_fraction ?? 1);
                                                        $pctShareLbl = \App\Models\JamiaShare::formatPercent($sh->share_fraction ?? 1);
                                                        $turnSiblings = $jamia->shares->where('share_number', $sh->share_number)->sortBy('split_part')->values();
                                                        $turnSiblingsCount = $turnSiblings->count();
                                                        $turnIsFirstSibling = $turnSiblings->first() && $turnSiblings->first()->id === $sh->id;
                                                        $turnCanSplitEdit = $turnSiblings->every(function ($sib) use ($jamia) {
                                                            $pCnt = $jamia->paid_by_share[$sib->id] ?? 0;
                                                            return !$sib->is_collected && !$sib->is_transferred && !$sib->is_claim_recovered && $pCnt === 0;
                                                        });
                                                        $turnPartnersPayload = $turnSiblings->map(function ($sib) use ($jamia) {
                                                            $frac = (float) ($sib->share_fraction ?? 1);
                                                            $instAmt = round((float) $jamia->monthly_installment * $frac, 2);
                                                            return [
                                                                'name'       => $sib->share_label,
                                                                'phone'      => $sib->member_phone ?? '',
                                                                'is_company' => (bool) $sib->is_company,
                                                                'percent'    => round($frac * 100, 4),
                                                                'amount'     => $instAmt,
                                                            ];
                                                        })->values()->all();
                                                        $dirRowClass = $sh->is_company ? 'row-company' : ($isHalfShare ? 'row-half' : ($sh->is_collected ? 'row-paid' : ''));
                                                    @endphp
                                                    <tr class="{{ $dirRowClass }}">
                                                    <td>
                                                        <span class="turn-num {{ $sh->is_company ? 'company' : '' }}">{{ $sh->share_number }}</span>
                                                        @if($isHalfShare)
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0 ms-1" style="font-size:0.68rem;">{{ $pctShareLbl }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($sh->payout_month)->locale('ar')->isoFormat('MMMM YYYY') }}</td>
                                                    <td>
                                                        <div class="fw-bold">
                                                            {{ $sh->share_label }}
                                                            @if($isHalfShare)
                                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill ms-1" style="font-size:0.72rem;"><i class="fa-solid fa-user-group me-1"></i> مشترك ({{ $pctShareLbl }} = {{ number_format($sh->payout_amount, 0) }} ج)</span>
                                                            @endif
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
                                                            <span class="badge-status badge-company"><i class="fa-solid fa-building"></i> سهم الشركة</span>
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
                                                        @if($lateN > 0)
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
                                                            <button type="button" class="btn-act btn-statement" title="كشف حساب العضو (صورة / واتساب)"
                                                                    onclick="openMemberStatementModal({ shareId: {{ $sh->id }}, memberName: @js($sh->share_label), memberPhone: @js($sh->member_phone), parentModalId: '{{ $dModalId }}' })">
                                                                <i class="fa-solid fa-file-invoice-dollar"></i> كشف
                                                            </button>
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

                                                            @if($turnIsFirstSibling && $turnCanSplitEdit)
                                                                @php
                                                                    $splitModalData = [
                                                                        'shareId'            => $sh->id,
                                                                        'shareNumber'        => $sh->share_number,
                                                                        'memberName'         => $sh->share_label,
                                                                        'jamiaName'          => $jamia->name,
                                                                        'totalAmount'        => (float) $jamia->total_amount,
                                                                        'monthlyInstallment' => (float) $jamia->monthly_installment,
                                                                        'partners'           => $turnPartnersPayload,
                                                                        'parentModalId'      => $dModalId,
                                                                    ];
                                                                @endphp
                                                                <button type="button" class="btn-act" style="background:#e0f2fe; color:#0369a1;" title="{{ $turnSiblingsCount > 1 ? 'تعديل نسب أو إضافة شركاء لهذا الدور' : 'تقسيم هذا السهم بنسب أو مبالغ بين أكثر من شريك' }}"
                                                                        data-split='@json($splitModalData)'
                                                                        onclick="openSplitShareModal(JSON.parse(this.dataset.split))">
                                                                    <i class="fa-solid fa-user-plus"></i> {{ $turnSiblingsCount > 1 ? 'تعديل الشركاء' : 'تقسيم بنسب' }}
                                                                </button>
                                                            @endif
                                                            @if($turnIsFirstSibling && ($isHalfShare || $turnSiblingsCount > 1) && $turnCanSplitEdit)
                                                                <form method="POST" action="{{ route('cashflows.unsplit_share') }}"
                                                                      onsubmit="if(confirm('إلغاء تقسيم الدور رقم {{ $sh->share_number }} ودمج جميع الشركاء في سهم كامل واحد؟')){ rememberReopenModal('{{ $dModalId }}'); return true; } return false;" style="display:inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="share_id" value="{{ $sh->id }}">
                                                                    <button type="submit" class="btn-act btn-edit text-danger" title="دمج جميع الشركاء في سهم كامل واحد">
                                                                        <i class="fa-solid fa-compress"></i> دمج السهم
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            <span class="btn-locked-pill" title="الحركات المالية (الصرف والاستلام والتنازل والإلغاء) مقفولة هنا وتُنفذ من شاشة تحصيل الشهر فقط">
                                                                <i class="fa-solid fa-lock text-warning"></i> الحركات مقفولة
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    </div>
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

            <div class="flow-table-wrap">
                <div class="table-responsive">
                    <table class="flow-table">
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-layer-group text-teal"></i> الجمعية</th>
                                <th><i class="fa-regular fa-calendar-days" style="color:#4f46e5;"></i> شهر القسط</th>
                                <th><i class="fa-solid fa-hashtag text-teal"></i> الدور</th>
                                <th><i class="fa-solid fa-user" style="color:#7c3aed;"></i> العضو</th>
                                <th><i class="fa-solid fa-phone" style="color:#0284c7;"></i> الهاتف</th>
                                <th><i class="fa-solid fa-coins text-warning"></i> القسط</th>
                                <th><i class="fa-solid fa-clock text-danger"></i> التأخير</th>
                                <th><i class="fa-solid fa-bolt text-danger"></i> الإجراء</th>
                            </tr>
                        </thead>
                    <tbody>
                        @forelse($overdueInstallments as $inst)
                            @php $isCompOver = !empty($inst->is_company); @endphp
                            <tr class="row-late">
                                <td class="fw-bold text-teal">{{ $inst->jamia_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($inst->month_date)->locale('ar')->isoFormat('MMMM YYYY') }}</td>
                                <td>
                                    <span class="turn-num {{ $isCompOver ? 'company' : '' }}">{{ $inst->share_number }}</span>
                                    @if(\App\Models\JamiaShare::isPartial($inst->share_fraction ?? 1))
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0 ms-1" style="font-size:0.68rem;">{{ \App\Models\JamiaShare::formatPercent($inst->share_fraction ?? 1) }}</span>
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    {{ $inst->share_label }}
                                    @if($isCompOver)
                                        <span class="badge-status badge-company ms-1" style="font-size:0.72rem;">سهم الشركة</span>
                                    @endif
                                </td>
                                <td class="text-muted" dir="ltr" style="text-align:right;">{{ $inst->member_phone ?: '—' }}</td>
                                <td><strong>{{ number_format($inst->amount, 2) }} ج</strong></td>
                                <td><span class="badge-status badge-pending">{{ $inst->status['days'] }} يوم</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        @if(!$isCompOver)
                                            <button type="button" class="btn-act btn-statement" title="كشف حساب العضو (صورة / واتساب)"
                                                    onclick="openMemberStatementModal({ shareId: {{ $inst->share_id }}, memberName: @js($inst->share_label), memberPhone: @js($inst->member_phone) })">
                                                <i class="fa-solid fa-file-invoice-dollar"></i> كشف
                                            </button>
                                        @endif
                                        @if($isCompOver)
                                            <button type="button" class="btn-act" style="background:#7c3aed; color:#fff;"
                                                    onclick="openCollectModal([{{ $inst->id }}], {{ $inst->amount }}, @js($inst->jamia_name . ' — حصة ' . $inst->share_label . ' (' . \Carbon\Carbon::parse($inst->month_date)->format('Y/m') . ')'), null, true)">
                                                <i class="fa-solid fa-building-circle-arrow-right"></i> إخراج حصة الشركة
                                            </button>
                                        @else
                                            <button type="button" class="btn-act btn-collect"
                                                    onclick="openCollectModal([{{ $inst->id }}], {{ $inst->amount }}, @js($inst->jamia_name . ' — ' . $inst->share_label . ' (' . \Carbon\Carbon::parse($inst->month_date)->format('Y/m') . ')'), null, false)">
                                                <i class="fa-solid fa-hand-holding-dollar"></i> تحصيل
                                            </button>
                                        @endif
                                    </div>
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

</div>

{{-- ══════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════ --}}

{{-- Modal 1: إضافة جمعية جديدة --}}
<div class="modal fade jamia-rich-modal" id="newJamiaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="POST" action="{{ route('cashflows.store') }}" class="modal-content border-0 shadow-lg" onsubmit="return validateNewJamia();">
            @csrf
            <input type="hidden" name="form_kind" value="new_jamia">
            <div class="modal-header modal-header-new-jamia">
                <div class="d-flex align-items-center gap-3">
                    <span class="modal-header-icon"><i class="fa-solid fa-plus-circle"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="color:#ffffff;">تسجيل جمعية جديدة ({{ $companyName }} هي المنظم)</h5>
                        <small style="color:rgba(255,255,255,0.9);">تحديد بيانات الجمعية وقيمة القبض وترتيب الأدوار وتوليد جدول التحصيل تلقائياً</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="nj-section-card mb-3">
                    <div class="nj-section-title">
                        <i class="fa-solid fa-sliders"></i> البيانات الأساسية للجمعية
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="fa-solid fa-tag text-teal me-1"></i> اسم الجمعية <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="مثلاً: جمعية 100 ألف - يناير" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-solid fa-building-user text-primary me-1"></i> المنظم</label>
                            <input type="text" name="organizer_name" class="form-control" value="{{ old('organizer_name', $companyName) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-regular fa-calendar-days text-teal me-1"></i> تاريخ أول شهر <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="input_start_date" class="form-control" value="{{ old('start_date', now()->format('Y-m-01')) }}" required onchange="generateMemberRows()">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-solid fa-sack-dollar text-success me-1"></i> قيمة القبض <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="total_amount" id="input_total_amount" class="form-control" placeholder="مثلاً: 100000" value="{{ old('total_amount') }}" required oninput="calcMonthlyInstallment()">
                                <span class="input-group-text">ج</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-solid fa-layer-group text-primary me-1"></i> عدد الشهور = عدد الأعضاء <span class="text-danger">*</span></label>
                            <input type="number" name="duration_months" id="input_duration" class="form-control" placeholder="مثلاً: 10" min="1" max="60" value="{{ old('duration_months', 10) }}" required oninput="calcMonthlyInstallment(); generateMemberRows();">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-solid fa-coins text-warning me-1"></i> قسط العضو الشهري</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="display_installment" class="form-control" placeholder="0.00" oninput="calcTotalFromInstallment()">
                                <span class="input-group-text">ج</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-solid fa-chart-pie text-info me-1"></i> عدد أسهم {{ $companyName }}</label>
                            <input type="number" name="company_shares_count" id="input_company_count" class="form-control" value="{{ old('company_shares_count', 1) }}" min="0" max="60" oninput="applyCompanyCount()">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-regular fa-clock text-teal me-1"></i> التحصيل يبدأ يوم</label>
                            <input type="number" name="collection_start_day" id="input_col_start" class="form-control" value="{{ old('collection_start_day', 1) }}" min="1" max="10">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="fa-solid fa-hourglass-end text-danger me-1"></i> آخر يوم للتحصيل (بحد أقصى 10)</label>
                            <input type="number" name="collection_end_day" id="input_col_end" class="form-control" value="{{ old('collection_end_day', 10) }}" min="1" max="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="fa-regular fa-note-sticky text-secondary me-1"></i> ملاحظات</label>
                            <input type="text" name="notes" class="form-control" placeholder="اختياري" value="{{ old('notes') }}">
                        </div>
                    </div>
                </div>

                <div class="nj-section-card nj-members-card">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="nj-section-title mb-0" style="background:linear-gradient(135deg,#eff6ff,#f5f3ff); border-color:#c7d2fe; color:#4338ca;">
                            <i class="fa-solid fa-users"></i> الأعضاء وأماكن دخولهم (ترقيم الدور ثابت بالترتيب)
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-shuffle-rich" onclick="shuffleTurns()" title="توزيع الأسماء عشوائياً على الأدوار الثابتة (قرعة)">
                                <i class="fa-solid fa-shuffle me-1"></i> قرعة على الأسماء
                            </button>
                        </div>
                    </div>
                    <div class="nj-hint-banner mb-3">
                        <i class="fa-solid fa-lock text-teal me-1"></i> ترقيم الدور مقفول وثابت تلقائياً من 1 لنهاية الجمعية. لو أكتر من شخص مشتركين في نفس السهم <b>بنسب أو مبالغ مختلفة</b> (مثلاً السهم 400 ج: حد بـ 50 وحد بـ 100 وحد بـ 200 وحد بـ 50) اضغط <b>"+ إضافة شريك"</b> أمام الدور وحدد مبلغ أو نسبة كل شريك.
                    </div>
                    <div id="turn_dup_warning" class="alert alert-danger py-2 mb-2" style="display:none; font-size:.85rem;"></div>
                    <div class="nj-table-wrap">
                        <div class="table-responsive" style="max-height: 440px; overflow-y:auto;">
                            <table class="table table-sm members-table mb-0">
                                <thead class="sticky-top">
                                    <tr>
                                        <th style="width:95px;">الدور <i class="fa-solid fa-lock ms-1" style="font-size:0.7rem;" title="ترقيم الدور مغلق وثابت"></i></th>
                                        <th style="width:125px;"><i class="fa-regular fa-calendar me-1"></i> شهر القبض</th>
                                        <th><i class="fa-solid fa-user me-1"></i> الاسم</th>
                                        <th style="width:145px;"><i class="fa-solid fa-phone me-1"></i> الهاتف</th>
                                        <th style="width:185px;" class="text-center"><i class="fa-solid fa-percent me-1"></i> الحصة (ج / %)</th>
                                        <th style="width:85px;" class="text-center"><i class="fa-solid fa-building me-1"></i> الشركة</th>
                                        <th style="width:120px;" class="text-center"><i class="fa-solid fa-user-group me-1"></i> شركاء السهم</th>
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
            <div class="modal-footer modal-footer-new-jamia d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary fw-bold px-4 rounded-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-save-jamia"><i class="fa-solid fa-check-circle me-1"></i> حفظ الجمعية وتوليد جدول التحصيل</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal 2: تحصيل قسط/أقساط (أو سداد حصة الشركة) --}}
<div class="modal fade sub-action-modal" id="collectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cashflows.collect') }}" class="modal-content border-0 shadow-lg" style="border-radius:18px;" onsubmit="rememberActiveParentForSubmit();">
            @csrf
            <div id="collect_ids_holder"></div>
            <div class="modal-header bg-success text-white" id="collect_modal_header">
                <h5 class="modal-title fw-bold" id="collect_modal_title"><i class="fa-solid fa-hand-holding-dollar me-1"></i> تسجيل تحصيل من الأعضاء</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="fw-bold text-dark" id="collect_info">—</div>
                    <div class="text-success fw-bold fs-5 mt-1" id="collect_amount_display">0.00 ج</div>
                </div>
                <div class="alert alert-info py-2 mb-3" id="collect_company_note" style="display:none; font-size:0.84rem;">
                    <i class="fa-solid fa-building-circle-arrow-right me-1"></i> إخراج حصة <strong>{{ $companyName }}</strong> من الخزنة المختارة علشان مبلغ قبض الجمعية يكتمل.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold" id="collect_account_label">إيداع المبلغ في محفظة / خزنة: <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select" required>
                        <option value="">-- اختر المحفظة أو الخزنة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                        @endforeach
                    </select>
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
                <button type="submit" class="btn btn-success fw-bold px-4" id="collect_submit_btn">تأكيد التحصيل والإيداع</button>
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
                    <i class="fa-solid fa-building-circle-check me-1"></i> تأكيد استلام <strong>{{ $companyName }}</strong> لدورها — لا يتم إضافة مبلغ جديد للخزنة لأن أقساط الأعضاء دخلت الخزنة بالفعل وقت التحصيل، ويُحدَّث بلوك موقف الجمعيات تلقائياً.
                </div>

                <div class="mb-3" id="payout_account_wrap">
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

{{-- Modal 4B: تقسيم السهم بنسب ومبالغ مخصصة بين عدة شركاء في نفس الدور --}}
<div class="modal fade sub-action-modal jamia-rich-modal" id="splitShareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('cashflows.split_share') }}" class="modal-content border-0 shadow-lg" style="border-radius:20px;" onsubmit="return validateSplitModalSubmit();">
            @csrf
            <input type="hidden" name="share_id" id="split_share_id">
            <div class="modal-header modal-header-month">
                <div class="d-flex align-items-center gap-3">
                    <span class="modal-header-icon"><i class="fa-solid fa-users-between-lines"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="color:#ffffff;">تقسيم السهم بنسب / مبالغ بين شركاء</h5>
                        <small id="split_jamia_info" style="color:rgba(255,255,255,0.92);">—</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 rounded-4 mb-3" style="background:#ffffff; border:1.5px solid #99f6e4; box-shadow:0 4px 14px rgba(13,148,136,0.08);">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div>
                            <small class="text-muted d-block fw-bold">قسط السهم الكامل (شهرياً)</small>
                            <strong class="fs-5 text-teal" id="split_full_inst_display">0.00 ج</strong>
                        </div>
                        <div class="vr"></div>
                        <div>
                            <small class="text-muted d-block fw-bold">قبض السهم الكامل</small>
                            <strong class="fs-5 text-success" id="split_full_payout_display">0.00 ج</strong>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-teal fw-bold px-3" onclick="equalizeSplitModalPartners()">
                            <i class="fa-solid fa-scale-balanced me-1"></i> توزيع متساوي
                        </button>
                        <button type="button" class="btn btn-sm fw-bold px-3 text-white" style="background:linear-gradient(135deg,#0d9488,#0284c7); border:none; border-radius:9px;" onclick="addSplitModalPartner()">
                            <i class="fa-solid fa-user-plus me-1"></i> إضافة شريك
                        </button>
                    </div>
                </div>

                <div class="p-2 px-3 rounded-3 mb-3" style="background:linear-gradient(135deg,#f0fdfa,#eff6ff); border:1px solid #99f6e4; font-size:0.83rem; color:#115e59;">
                    <i class="fa-solid fa-circle-info text-teal me-1"></i> اكتب <b>مبلغ القسط الشهري (ج)</b> أو <b>النسبة (%)</b> لكل شريك — النظام هيحسب التاني وقيمة القبض تلقائياً. لازم المجموع النهائي يساوي <b>100%</b>.
                </div>

                <div id="split_partners_list" class="d-flex flex-column gap-2">
                    {{-- Generated dynamically by JS --}}
                </div>

                <div id="split_modal_summary" class="mt-3 p-3 rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-2 fw-bold" style="font-size:0.9rem;">
                    {{-- Live summary --}}
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between" style="background:linear-gradient(135deg,#f0fdfa,#eff6ff); border-top:1.5px solid #99f6e4;">
                <button type="button" class="btn btn-outline-secondary fw-bold px-4 rounded-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" id="split_modal_submit_btn" class="btn fw-bold px-4 text-white rounded-3" style="background:linear-gradient(135deg,#059669,#0d9488,#0284c7); border:none;">
                    <i class="fa-solid fa-check-circle me-1"></i> حفظ توزيع الشركاء
                </button>
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

{{-- ══════════════════════════════════════════════════════════════
     Modal: كشف حساب عضو الجمعيات (صورة / واتساب / طباعة)
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="memberStatementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div style="background:#0d9488; color:#fff; width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">كشف حساب عضو الجمعيات</h5>
                        <small class="text-muted">متابعة الحصص، الجمعيات، مواعيد القبض، وجدول سداد الأقساط</small>
                    </div>
                </div>

                {{-- Action Toolbar --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm fw-bold text-white shadow-sm" id="btnSendWa" onclick="sendMemberStatementWhatsApp()" style="background:#25d366; border:none; border-radius:10px; padding:7px 14px;" title="إرسال كشف الحساب كصورة عبر واتساب">
                        <i class="fa-brands fa-whatsapp me-1"></i> إرسال واتساب (صورة)
                    </button>
                    <button type="button" class="btn btn-sm fw-bold text-white shadow-sm" id="btnDownloadPng" onclick="downloadMemberStatementImage()" style="background:#0d9488; border:none; border-radius:10px; padding:7px 14px;" title="تنزيل كشف الحساب كصورة PNG">
                        <i class="fa-solid fa-download me-1"></i> تحميل صورة (PNG)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" id="btnPrintStatement" onclick="printMemberStatement()" style="border-radius:10px; padding:7px 12px;" title="طباعة أو تصدير PDF">
                        <i class="fa-solid fa-print me-1"></i> طباعة
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark fw-bold" id="btnCopyText" onclick="copyStatementText()" style="border-radius:10px; padding:7px 12px;" title="نسخ ملخص نصي سريع">
                        <i class="fa-solid fa-copy me-1"></i> نسخ ملخص
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4" style="background:#f8fafc;">
                {{-- Selector & Phone Controls --}}
                <div class="card border-0 shadow-sm p-3 mb-3" style="border-radius:14px; background:#ffffff;">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-secondary mb-1" style="font-size:0.84rem;">
                                <i class="fa-solid fa-user-check text-teal me-1"></i> اختر العضو:
                            </label>
                            <select id="statementMemberSelect" class="form-select" onchange="onStatementMemberChanged(this.value)">
                                <option value="">-- اختر عضواً لعرض كشف حسابه --</option>
                                @foreach($membersList as $m)
                                    <option value="{{ $m['name'] }}" data-phone="{{ $m['phone'] }}" data-share-id="{{ $m['share_id'] }}">
                                        {{ $m['name'] }} {{ $m['phone'] ? '('.$m['phone'].')' : '' }} — {{ $m['shares_count'] }} حصص في {{ $m['jamias_count'] }} جمعية
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-secondary mb-1" style="font-size:0.84rem;">
                                <i class="fa-brands fa-whatsapp text-success me-1"></i> رقم هاتف الواتساب:
                            </label>
                            <input type="text" id="statementPhoneInput" class="form-control" dir="ltr" placeholder="01xxxxxxxxx">
                        </div>
                        <div class="col-12 col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-teal w-100 fw-bold" onclick="reloadCurrentStatement()" style="margin-top:24px; border-radius:10px;">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> تحديث
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Loading Spinner --}}
                <div id="statementLoading" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-teal" style="width: 3rem; height: 3rem;" role="status"></div>
                    <div class="mt-3 text-muted fw-bold">جاري تجهيز كشف الحساب...</div>
                </div>

                {{-- Error Alert --}}
                <div id="statementError" class="alert alert-danger shadow-sm text-center py-4" style="display:none; border-radius:12px;"></div>

                {{-- Empty Selection Prompt --}}
                <div id="statementEmpty" class="text-center py-5" style="display:none;">
                    <div class="text-muted fs-1 mb-2"><i class="fa-solid fa-file-invoice-dollar text-teal"></i></div>
                    <h5 class="fw-bold text-secondary">اختر عضواً من القائمة أعلاه</h5>
                    <p class="text-muted" style="font-size:0.88rem;">سيظهر لك كشف الحساب المجمع بجميع الجمعيات ومواعيد القبض والأقساط، مع إمكانية إرساله واتساب أو تنزيله كصورة.</p>
                </div>

                {{-- The Capturable / Printable Statement Card --}}
                <div id="statementCardWrap" style="display:none;">
                    {{-- Filter pills for installments before capture --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 px-1 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-secondary fw-bold" style="font-size:0.82rem;">عرض أقساط:</span>
                            <button type="button" class="btn btn-sm btn-outline-teal active py-1 px-2 fw-bold stmt-filter-btn" style="border-radius:8px; font-size:0.78rem;" onclick="filterStatementInstallments('all', this)">كل الشهور</button>
                            <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 fw-bold stmt-filter-btn" style="border-radius:8px; font-size:0.78rem;" onclick="filterStatementInstallments('unpaid', this)">المتبقي والمتأخر</button>
                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 fw-bold stmt-filter-btn" style="border-radius:8px; font-size:0.78rem;" onclick="filterStatementInstallments('paid', this)">المسدد فقط</button>
                        </div>
                        <small class="text-muted"><i class="fa-solid fa-camera text-teal me-1"></i> المنطقة أدناه يتم تصويرها بدقة عالية للواتساب والتنزيل</small>
                    </div>

                    <!-- CARD CONTAINER (Captured by html2canvas) -->
                    <div id="memberStatementCard" class="statement-card-container">
                        <!-- Populated dynamically by renderMemberStatement() -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: تفاصيل المسحوبات الشهرية للشركاء --}}
<div class="modal fade" id="partnersMonthlyWithdrawalsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #be123c 0%, #e11d48 100%);">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-money-bill-transfer me-2"></i> تفاصيل المسحوبات الشهرية للشركاء
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                @if(!empty($partnersMonthlyWithdrawals['partners']) && count($partnersMonthlyWithdrawals['partners']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 48px;">#</th>
                                    <th>اسم الشريك</th>
                                    <th class="text-center">المسحوب شهرياً</th>
                                    <th class="text-center">الملف</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($partnersMonthlyWithdrawals['partners'] as $pw)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-dark">{{ $pw->name }}</td>
                                        <td class="text-center fw-bold" style="color:#be123c; font-variant-numeric: tabular-nums;">
                                            {{ number_format((float) $pw->monthly_fixed_withdrawal, 2) }} ج / شهر
                                        </td>
                                        <td class="text-center">
                                            @if(\Illuminate\Support\Facades\Route::has('partners.show'))
                                                <a href="{{ route('partners.show', $pw->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:0.78rem;">
                                                    <i class="fa-solid fa-user-tie me-1"></i> فتح
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:#fff1f2;">
                                <tr>
                                    <td colspan="2" class="fw-bold text-end" style="color:#9f1239;">إجمالي المسحوبات الشهرية للشركاء:</td>
                                    <td colspan="2" class="text-center fw-bolder fs-6" style="color:#be123c; font-variant-numeric: tabular-nums;">
                                        {{ number_format($partnersMonthlyWithdrawals['total'], 2) }} ج / شهر
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        لا يوجد شركاء مسجّل لهم سحب شهري ثابت حالياً.
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 bg-light d-flex justify-content-between">
                @if(\Illuminate\Support\Facades\Route::has('partners.index'))
                    <a href="{{ route('partners.index') }}" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3">
                        <i class="fa-solid fa-users me-1"></i> الذهاب لشاشة الشركاء
                    </a>
                @endif
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
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

    // ── Tab Switching (التاب الافتراضي دائماً: الرئيسية) ──
    function switchTab(tabId, btn) {
        const pane = document.getElementById(tabId);
        if (!pane) return;
        document.querySelectorAll('.tab-content-pane').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.custom-tab-btn').forEach(b => b.classList.remove('active'));
        pane.style.display = 'block';
        if (btn) btn.classList.add('active');
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
    function getNewJamiaFullInstallment() {
        const disp = parseFloat(document.getElementById('display_installment')?.value);
        if (disp > 0) return disp;
        const total = parseFloat(document.getElementById('input_total_amount')?.value) || 0;
        const duration = parseInt(document.getElementById('input_duration')?.value) || 0;
        return (total > 0 && duration > 0) ? (total / duration) : 0;
    }

    function getNewJamiaTotalAmount() {
        return parseFloat(document.getElementById('input_total_amount')?.value) || 0;
    }

    function calcMonthlyInstallment() {
        const total = parseFloat(document.getElementById('input_total_amount').value) || 0;
        const duration = parseInt(document.getElementById('input_duration').value) || 0;
        document.getElementById('display_installment').value = (total > 0 && duration > 0) ? (total / duration).toFixed(2) : '';
        refreshAllNewJamiaRatioDisplays();
    }

    function calcTotalFromInstallment() {
        const inst = parseFloat(document.getElementById('display_installment').value) || 0;
        const duration = parseInt(document.getElementById('input_duration').value) || 0;
        if (inst > 0 && duration > 0) {
            document.getElementById('input_total_amount').value = (inst * duration).toFixed(2);
        }
        refreshAllNewJamiaRatioDisplays();
    }

    function monthLabelForTurn(turn) {
        const startVal = document.getElementById('input_start_date').value;
        const base = startVal ? new Date(startVal + 'T00:00:00') : new Date();
        const d = new Date(base.getFullYear(), base.getMonth() + (turn - 1), 1);
        return `${AR_MONTHS[d.getMonth()]} ${d.getFullYear()}`;
    }

    function cleanNumStr(v, maxDec = 2) {
        if (v === '' || v === null || v === undefined || isNaN(v)) return '';
        return String(parseFloat(Number(v).toFixed(maxDec)));
    }

    // ── Members rows (يدعم اشتراك أي عدد من الشركاء في نفس الدور بنسب أو مبالغ مخصصة) ──
    let newJamiaTurns = [];

    function syncNewJamiaTurnsFromDOM() {
        const mainRows = Array.from(document.querySelectorAll('#members_container tr.member-main-row'));
        if (mainRows.length === 0) return;

        newJamiaTurns = mainRows.map(tr => {
            const idx = tr.dataset.idx;
            const isSplit = tr.querySelector('.m-split-flag')?.value === '1';
            if (!isSplit) {
                return {
                    partners: [{
                        name: tr.querySelector('.m-name')?.value || '',
                        phone: tr.querySelector('.m-phone')?.value || '',
                        company: tr.querySelector('.m-company')?.checked || false,
                        percent: 100,
                        amount: '',
                    }],
                };
            }
            const partnerRows = Array.from(document.querySelectorAll(`#members_container tr.member-partner-row[data-idx="${idx}"]`));
            const partners = partnerRows.map(pr => ({
                name: pr.querySelector('.m-p-name')?.value || '',
                phone: pr.querySelector('.m-p-phone')?.value || '',
                company: pr.querySelector('.m-p-company')?.checked || false,
                percent: pr.querySelector('.m-p-pct')?.value ?? '',
                amount: pr.querySelector('.m-p-amt')?.value ?? '',
            }));
            return { partners: partners.length >= 2 ? partners : [{ name: '', phone: '', company: false, percent: 100, amount: '' }] };
        });
    }

    function generateMemberRows() {
        const n = Math.max(0, Math.min(60, parseInt(document.getElementById('input_duration').value) || 0));
        syncNewJamiaTurnsFromDOM();
        const firstBuild = newJamiaTurns.length === 0;

        if (firstBuild && OLD_MEMBERS.length > 0) {
            newJamiaTurns = OLD_MEMBERS.map(m => {
                if (m.is_split && Array.isArray(m.partners) && m.partners.length >= 2) {
                    return {
                        partners: m.partners.map(p => ({
                            name: p.name ?? '',
                            phone: p.phone ?? '',
                            company: !!p.is_company,
                            percent: p.percent ?? '',
                            amount: p.amount ?? '',
                        })),
                    };
                }
                if (m.is_split) {
                    return {
                        partners: [
                            { name: m.name ?? '', phone: m.phone ?? '', company: !!m.is_company, percent: 50, amount: '' },
                            { name: m.name2 ?? '', phone: m.phone2 ?? '', company: !!m.is_company2, percent: 50, amount: '' },
                        ],
                    };
                }
                return {
                    partners: [{
                        name: m.name ?? '',
                        phone: m.phone ?? '',
                        company: !!m.is_company,
                        percent: 100,
                        amount: '',
                    }],
                };
            });
        }

        const companyCount = parseInt(document.getElementById('input_company_count').value) || 0;
        const resized = [];
        for (let i = 0; i < n; i++) {
            if (newJamiaTurns[i]) {
                resized.push(newJamiaTurns[i]);
            } else {
                const isComp = (firstBuild && OLD_MEMBERS.length === 0) ? (i < companyCount) : false;
                resized.push({
                    partners: [{
                        name: isComp ? COMPANY_NAME : '',
                        phone: '',
                        company: isComp,
                        percent: 100,
                        amount: '',
                    }],
                });
            }
        }
        newJamiaTurns = resized;
        renderNewJamiaRows();
    }

    function renderNewJamiaRows() {
        const container = document.getElementById('members_container');
        if (!container) return;
        container.innerHTML = '';

        const fullInst = getNewJamiaFullInstallment();
        const totalAmt = getNewJamiaTotalAmount();

        newJamiaTurns.forEach((turnObj, i) => {
            const turn = i + 1;
            const parts = turnObj.partners || [];
            const isSplit = parts.length >= 2;

            if (!isSplit) {
                const p = parts[0] || { name: '', phone: '', company: false, percent: 100, amount: '' };
                const tr = document.createElement('tr');
                tr.className = 'member-main-row' + (p.company ? ' is-company' : '');
                tr.dataset.idx = i;
                tr.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <span class="turn-num">${turn}</span>
                        </div>
                        <input type="hidden" class="m-turn" name="members[${i}][turn]" value="${turn}">
                        <input type="hidden" class="m-split-flag" name="members[${i}][is_split]" value="0">
                    </td>
                    <td class="text-muted m-month" style="font-size:.83rem;">${monthLabelForTurn(turn)}</td>
                    <td><input type="text" class="form-control form-control-sm m-name" name="members[${i}][name]" placeholder="اسم العضو" value="${escapeHtml(p.name)}"></td>
                    <td><input type="text" class="form-control form-control-sm m-phone" name="members[${i}][phone]" placeholder="اختياري" dir="ltr" value="${escapeHtml(p.phone || '')}"></td>
                    <td class="text-center">
                        <span class="badge rounded-pill px-2 py-1 m-full-share-badge" style="background:#f0fdfa; color:#0f766e; border:1px solid #99f6e4; font-size:0.76rem;">
                            100%${fullInst > 0 ? ' • ' + fmt(fullInst) + ' ج' : ''}
                        </span>
                    </td>
                    <td class="text-center"><input type="checkbox" class="form-check-input m-company" name="members[${i}][is_company]" value="1" ${p.company ? 'checked' : ''} onchange="onCompanyToggle(this)"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-teal py-0 px-2 fw-bold m-split-btn" style="font-size:0.74rem;" onclick="addTurnPartner(${i})" title="تقسيم هذا الدور بين أكثر من شريك بنسب أو مبالغ">
                            <i class="fa-solid fa-user-plus me-1"></i> إضافة شريك
                        </button>
                    </td>
                `;
                container.appendChild(tr);
            } else {
                parts.forEach((p, pIdx) => {
                    let pctVal = (p.percent !== '' && p.percent !== null && p.percent !== undefined) ? parseFloat(p.percent) : NaN;
                    let amtVal = (p.amount !== '' && p.amount !== null && p.amount !== undefined) ? parseFloat(p.amount) : NaN;
                    if (isNaN(amtVal) && !isNaN(pctVal) && fullInst > 0) {
                        amtVal = fullInst * (pctVal / 100);
                    } else if (isNaN(pctVal) && !isNaN(amtVal) && fullInst > 0) {
                        pctVal = (amtVal / fullInst) * 100;
                    }
                    const amtStr = !isNaN(amtVal) ? cleanNumStr(amtVal, 2) : '';
                    const pctStr = !isNaN(pctVal) ? cleanNumStr(pctVal, 2) : '';
                    const payoutPreview = (!isNaN(pctVal) && totalAmt > 0) ? fmt(totalAmt * (pctVal / 100)) + ' ج' : '';

                    const tr = document.createElement('tr');
                    tr.className = (pIdx === 0 ? 'member-main-row ' : 'member-split-row ') + 'member-partner-row' + (p.company ? ' is-company' : '');
                    tr.dataset.idx = i;
                    tr.dataset.pidx = pIdx;

                    const turnCell = pIdx === 0
                        ? `
                            <div class="d-flex align-items-center gap-1">
                                <span class="turn-num">${turn}</span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0" style="font-size:0.66rem;">شريك 1</span>
                            </div>
                            <input type="hidden" class="m-turn" name="members[${i}][turn]" value="${turn}">
                            <input type="hidden" class="m-split-flag" name="members[${i}][is_split]" value="1">
                        `
                        : `
                            <div class="d-flex align-items-center gap-1 ps-2">
                                <i class="fa-solid fa-turn-up fa-rotate-90 text-teal" style="font-size:0.75rem;"></i>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0" style="font-size:0.66rem;">شريك ${pIdx + 1}</span>
                            </div>
                        `;

                    const monthCell = pIdx === 0
                        ? `<td class="text-muted m-month" style="font-size:.83rem;">${monthLabelForTurn(turn)}</td>`
                        : `<td class="text-muted" style="font-size:.74rem;">نفس الدور (${turn})</td>`;

                    const actionCell = pIdx === 0
                        ? `<button type="button" class="btn btn-sm btn-outline-teal py-0 px-2 fw-bold" style="font-size:0.73rem;" onclick="addTurnPartner(${i})" title="إضافة شريك آخر في نفس الدور"><i class="fa-solid fa-user-plus me-1"></i> شريك</button>`
                        : `<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold" style="font-size:0.73rem;" onclick="removeTurnPartner(${i}, ${pIdx})" title="حذف هذا الشريك"><i class="fa-solid fa-xmark"></i></button>`;

                    tr.innerHTML = `
                        <td>${turnCell}</td>
                        ${monthCell}
                        <td><input type="text" class="form-control form-control-sm m-p-name" name="members[${i}][partners][${pIdx}][name]" placeholder="اسم الشريك ${pIdx + 1}" value="${escapeHtml(p.name)}"></td>
                        <td><input type="text" class="form-control form-control-sm m-p-phone" name="members[${i}][partners][${pIdx}][phone]" placeholder="اختياري" dir="ltr" value="${escapeHtml(p.phone || '')}"></td>
                        <td>
                            <div class="share-ratio-box">
                                <div class="input-group input-group-sm" style="flex:1.15;">
                                    <input type="number" step="any" min="0" class="form-control m-p-amt" name="members[${i}][partners][${pIdx}][amount]" placeholder="المبلغ" value="${amtStr}" oninput="onTurnPartnerAmountInput(${i}, ${pIdx}, this)" title="قسط هذا الشريك الشهري بالجنيه">
                                    <span class="input-group-text">ج</span>
                                </div>
                                <div class="input-group input-group-sm" style="flex:1;">
                                    <input type="number" step="any" min="0" max="100" class="form-control m-p-pct" name="members[${i}][partners][${pIdx}][percent]" placeholder="%" value="${pctStr}" oninput="onTurnPartnerPercentInput(${i}, ${pIdx}, this)" title="نسبة هذا الشريك من السهم">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="text-center text-success fw-bold m-p-payout-hint" style="font-size:0.68rem; margin-top:2px;">${payoutPreview ? 'قبض: ' + payoutPreview : ''}</div>
                        </td>
                        <td class="text-center"><input type="checkbox" class="form-check-input m-p-company" name="members[${i}][partners][${pIdx}][is_company]" value="1" ${p.company ? 'checked' : ''} onchange="onTurnPartnerCompanyToggle(this)"></td>
                        <td class="text-center">${actionCell}</td>
                    `;
                    container.appendChild(tr);
                });

                // صف الملخص المباشر للدور المقسّم
                const sumTr = document.createElement('tr');
                sumTr.className = 'member-split-summary-row';
                sumTr.dataset.idx = i;
                sumTr.innerHTML = `
                    <td colspan="7">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center flex-wrap gap-2 m-turn-sum-text" style="font-size:0.78rem;"></div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" style="font-size:0.72rem;" onclick="equalizeTurnPartners(${i})">
                                    <i class="fa-solid fa-scale-balanced me-1"></i> توزيع متساوي
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-teal py-0 px-2 fw-bold" style="font-size:0.72rem;" onclick="addTurnPartner(${i})">
                                    <i class="fa-solid fa-plus me-1"></i> شريك إضافي
                                </button>
                            </div>
                        </div>
                    </td>
                `;
                container.appendChild(sumTr);
                updateTurnSummaryBar(i);
            }
        });

        syncCompanyCountInput();
    }

    function onTurnPartnerAmountInput(idx, pIdx, inputEl) {
        const fullInst = getNewJamiaFullInstallment();
        const totalAmt = getNewJamiaTotalAmount();
        const row = inputEl.closest('tr');
        const pctInput = row.querySelector('.m-p-pct');
        const hintEl = row.querySelector('.m-p-payout-hint');
        const amt = parseFloat(inputEl.value);

        if (!isNaN(amt) && amt >= 0 && fullInst > 0) {
            const pct = (amt / fullInst) * 100;
            pctInput.value = cleanNumStr(pct, 2);
            if (hintEl) hintEl.textContent = totalAmt > 0 ? 'قبض: ' + fmt(totalAmt * (pct / 100)) + ' ج' : '';
        } else {
            if (hintEl) hintEl.textContent = '';
        }
        updateTurnSummaryBar(idx);
    }

    function onTurnPartnerPercentInput(idx, pIdx, inputEl) {
        const fullInst = getNewJamiaFullInstallment();
        const totalAmt = getNewJamiaTotalAmount();
        const row = inputEl.closest('tr');
        const amtInput = row.querySelector('.m-p-amt');
        const hintEl = row.querySelector('.m-p-payout-hint');
        const pct = parseFloat(inputEl.value);

        if (!isNaN(pct) && pct >= 0) {
            if (fullInst > 0) amtInput.value = cleanNumStr(fullInst * (pct / 100), 2);
            if (hintEl) hintEl.textContent = totalAmt > 0 ? 'قبض: ' + fmt(totalAmt * (pct / 100)) + ' ج' : '';
        } else {
            if (hintEl) hintEl.textContent = '';
        }
        updateTurnSummaryBar(idx);
    }

    function updateTurnSummaryBar(idx) {
        const sumRow = document.querySelector(`#members_container tr.member-split-summary-row[data-idx="${idx}"]`);
        if (!sumRow) return;
        const partnerRows = Array.from(document.querySelectorAll(`#members_container tr.member-partner-row[data-idx="${idx}"]`));
        const fullInst = getNewJamiaFullInstallment();

        let sumAmt = 0;
        let sumPct = 0;
        partnerRows.forEach(pr => {
            const a = parseFloat(pr.querySelector('.m-p-amt')?.value);
            const p = parseFloat(pr.querySelector('.m-p-pct')?.value);
            if (!isNaN(a) && a > 0) sumAmt += a;
            if (!isNaN(p) && p > 0) sumPct += p;
            else if (!isNaN(a) && a > 0 && fullInst > 0) sumPct += (a / fullInst) * 100;
        });

        const diffPct = 100 - sumPct;
        const isOk = Math.abs(diffPct) <= 0.5 && (fullInst <= 0 || Math.abs(fullInst - sumAmt) <= Math.max(0.05, fullInst * 0.005));

        let statusHtml = '';
        if (isOk) {
            statusHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> مكتمل 100%</span>`;
        } else if (diffPct > 0) {
            const remAmtTxt = fullInst > 0 ? `${fmt(Math.max(0, fullInst - sumAmt))} ج (${cleanNumStr(diffPct, 2)}%)` : `${cleanNumStr(diffPct, 2)}%`;
            statusHtml = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> متبقي: ${remAmtTxt}</span>`;
        } else {
            const overAmtTxt = fullInst > 0 ? `${fmt(Math.max(0, sumAmt - fullInst))} ج (+${cleanNumStr(Math.abs(diffPct), 2)}%)` : `+${cleanNumStr(Math.abs(diffPct), 2)}%`;
            statusHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-circle-exclamation me-1"></i> زيادة: ${overAmtTxt}</span>`;
        }

        const totalTxt = fullInst > 0
            ? `<b>${fmt(sumAmt)}</b> / ${fmt(fullInst)} ج (<b>${cleanNumStr(sumPct, 2)}%</b>)`
            : `<b>${cleanNumStr(sumPct, 2)}%</b> / 100%`;

        sumRow.querySelector('.m-turn-sum-text').innerHTML = `
            <span class="text-teal fw-bold"><i class="fa-solid fa-users me-1"></i> ${partnerRows.length} شركاء</span>
            <span class="text-muted">|</span>
            <span>المجموع: ${totalTxt}</span>
            ${statusHtml}
        `;
    }

    function refreshAllNewJamiaRatioDisplays() {
        const fullInst = getNewJamiaFullInstallment();
        const totalAmt = getNewJamiaTotalAmount();

        document.querySelectorAll('#members_container .m-full-share-badge').forEach(b => {
            b.textContent = `100%${fullInst > 0 ? ' • ' + fmt(fullInst) + ' ج' : ''}`;
        });

        const splitIdxs = new Set();
        document.querySelectorAll('#members_container tr.member-partner-row').forEach(pr => {
            splitIdxs.add(parseInt(pr.dataset.idx));
            const amtInp = pr.querySelector('.m-p-amt');
            const pctInp = pr.querySelector('.m-p-pct');
            const hintEl = pr.querySelector('.m-p-payout-hint');
            const pct = parseFloat(pctInp?.value);
            const amt = parseFloat(amtInp?.value);

            if (!isNaN(pct) && pct > 0 && fullInst > 0) {
                amtInp.value = cleanNumStr(fullInst * (pct / 100), 2);
                if (hintEl) hintEl.textContent = totalAmt > 0 ? 'قبض: ' + fmt(totalAmt * (pct / 100)) + ' ج' : '';
            } else if (!isNaN(amt) && amt > 0 && fullInst > 0) {
                const calcPct = (amt / fullInst) * 100;
                pctInp.value = cleanNumStr(calcPct, 2);
                if (hintEl) hintEl.textContent = totalAmt > 0 ? 'قبض: ' + fmt(totalAmt * (calcPct / 100)) + ' ج' : '';
            } else if (hintEl) {
                hintEl.textContent = '';
            }
        });
        splitIdxs.forEach(idx => updateTurnSummaryBar(idx));
    }

    function addTurnPartner(idx) {
        syncNewJamiaTurnsFromDOM();
        const turnObj = newJamiaTurns[idx];
        if (!turnObj) return;
        const fullInst = getNewJamiaFullInstallment();

        if (turnObj.partners.length <= 1) {
            const first = turnObj.partners[0] || { name: '', phone: '', company: false };
            const halfAmt = fullInst > 0 ? cleanNumStr(fullInst / 2, 2) : '';
            turnObj.partners = [
                { name: first.name, phone: first.phone, company: first.company, percent: 50, amount: halfAmt },
                { name: '', phone: '', company: false, percent: 50, amount: halfAmt },
            ];
        } else {
            const pcts = turnObj.partners.map(p => parseFloat(p.percent) || 0);
            const sumPct = pcts.reduce((a, b) => a + b, 0);
            const remPct = Math.round((100 - sumPct) * 100) / 100;
            const allEqual = pcts.length >= 2 && pcts.every(v => Math.abs(v - pcts[0]) < 0.25) && Math.abs(sumPct - 100) < 0.5;

            if (remPct > 0.05) {
                const remAmt = fullInst > 0 ? cleanNumStr(fullInst * (remPct / 100), 2) : '';
                turnObj.partners.push({ name: '', phone: '', company: false, percent: cleanNumStr(remPct, 2), amount: remAmt });
            } else if (allEqual) {
                turnObj.partners.push({ name: '', phone: '', company: false, percent: 0, amount: '' });
                distributeEqualOnList(turnObj.partners, fullInst);
            } else {
                turnObj.partners.push({ name: '', phone: '', company: false, percent: '', amount: '' });
            }
        }

        renderNewJamiaRows();
        const lastPartner = document.querySelector(`#members_container tr.member-partner-row[data-idx="${idx}"][data-pidx="${turnObj.partners.length - 1}"] .m-p-name`);
        if (lastPartner) lastPartner.focus();
    }

    function removeTurnPartner(idx, pIdx) {
        syncNewJamiaTurnsFromDOM();
        const turnObj = newJamiaTurns[idx];
        if (!turnObj || turnObj.partners.length <= 1) return;
        turnObj.partners.splice(pIdx, 1);
        if (turnObj.partners.length === 1) {
            turnObj.partners[0].percent = 100;
            turnObj.partners[0].amount = '';
        }
        renderNewJamiaRows();
    }

    function distributeEqualOnList(list, fullInst) {
        const count = list.length;
        if (count === 0) return;
        const basePct = Math.floor((100 / count) * 100) / 100;
        let accPct = 0;
        let accAmt = 0;
        list.forEach((p, i) => {
            const isLast = (i === count - 1);
            const pct = isLast ? Math.round((100 - accPct) * 100) / 100 : basePct;
            accPct += pct;
            p.percent = cleanNumStr(pct, 2);
            if (fullInst > 0) {
                const amt = isLast ? Math.round((fullInst - accAmt) * 100) / 100 : Math.round((fullInst * pct / 100) * 100) / 100;
                accAmt += amt;
                p.amount = cleanNumStr(amt, 2);
            } else {
                p.amount = '';
            }
        });
    }

    function equalizeTurnPartners(idx) {
        syncNewJamiaTurnsFromDOM();
        const turnObj = newJamiaTurns[idx];
        if (!turnObj || turnObj.partners.length < 2) return;
        distributeEqualOnList(turnObj.partners, getNewJamiaFullInstallment());
        renderNewJamiaRows();
    }

    function onCompanyToggle(cb) {
        const tr = cb.closest('tr');
        const nameInput = tr.querySelector('.m-name');
        tr.classList.toggle('is-company', cb.checked);
        if (cb.checked && nameInput.value.trim() === '') nameInput.value = COMPANY_NAME;
        if (!cb.checked && nameInput.value.trim() === COMPANY_NAME) nameInput.value = '';
        syncCompanyCountInput();
    }

    function onTurnPartnerCompanyToggle(cb) {
        const tr = cb.closest('tr');
        const nameInput = tr.querySelector('.m-p-name');
        tr.classList.toggle('is-company', cb.checked);
        if (cb.checked && nameInput.value.trim() === '') nameInput.value = COMPANY_NAME;
        if (!cb.checked && nameInput.value.trim() === COMPANY_NAME) nameInput.value = '';
        syncCompanyCountInput();
    }

    function syncCompanyCountInput() {
        const c1 = document.querySelectorAll('#members_container .m-company:checked').length;
        const c2 = document.querySelectorAll('#members_container .m-p-company:checked').length;
        document.getElementById('input_company_count').value = c1 + c2;
    }

    // تحديد عدد أسهم الشركة => يزوّد أو ينقص أسهم الشركة مع الحفاظ على التحديدات اليدوية
    function applyCompanyCount() {
        const rows = Array.from(document.querySelectorAll('#members_container tr.member-main-row'));
        const desired = Math.max(0, Math.min(rows.length, parseInt(document.getElementById('input_company_count').value) || 0));
        let checked = document.querySelectorAll('#members_container .m-company:checked, #members_container .m-p-company:checked').length;

        if (checked < desired) {
            for (const tr of rows) {
                if (checked >= desired) break;
                const cb = tr.querySelector('.m-company') || tr.querySelector('.m-p-company');
                if (cb && !cb.checked) {
                    cb.checked = true;
                    tr.classList.add('is-company');
                    const nameInput = tr.querySelector('.m-name') || tr.querySelector('.m-p-name');
                    if (nameInput && nameInput.value.trim() === '') nameInput.value = COMPANY_NAME;
                    checked++;
                }
            }
        } else if (checked > desired) {
            for (let i = rows.length - 1; i >= 0 && checked > desired; i--) {
                const tr = rows[i];
                const cb = tr.querySelector('.m-company') || tr.querySelector('.m-p-company');
                if (cb && cb.checked) {
                    cb.checked = false;
                    tr.classList.remove('is-company');
                    const nameInput = tr.querySelector('.m-name') || tr.querySelector('.m-p-name');
                    if (nameInput && nameInput.value.trim() === COMPANY_NAME) nameInput.value = '';
                    checked--;
                }
            }
        }
    }

    // قرعة على الأسماء مع بقاء ترقيم الأدوار مقفولاً وثابتاً (1..N)
    function shuffleTurns() {
        syncNewJamiaTurnsFromDOM();
        for (let i = newJamiaTurns.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [newJamiaTurns[i], newJamiaTurns[j]] = [newJamiaTurns[j], newJamiaTurns[i]];
        }
        renderNewJamiaRows();
    }

    function validateNewJamia() {
        syncNewJamiaTurnsFromDOM();
        const n = newJamiaTurns.length;
        if (n < 1) {
            Swal.fire({ icon: 'warning', title: 'لازم دور واحد على الأقل' });
            return false;
        }
        const s = parseInt(document.getElementById('input_col_start').value) || 1;
        const e = parseInt(document.getElementById('input_col_end').value) || 10;
        if (e > 10 || e < s) {
            Swal.fire({ icon: 'warning', title: 'فترة التحصيل غير صحيحة', text: 'آخر يوم للتحصيل لازم يكون بين يوم البداية ويوم 10.' });
            return false;
        }

        const fullInst = getNewJamiaFullInstallment();
        for (let i = 0; i < newJamiaTurns.length; i++) {
            const parts = newJamiaTurns[i].partners || [];
            if (parts.length < 2) continue;
            let sumPct = 0;
            let sumAmt = 0;
            for (let pIdx = 0; pIdx < parts.length; pIdx++) {
                const amt = parseFloat(parts[pIdx].amount) || 0;
                const pct = parseFloat(parts[pIdx].percent) || (fullInst > 0 && amt > 0 ? (amt / fullInst) * 100 : 0);
                if (amt <= 0 && pct <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: `حصة ناقصة في الدور رقم ${i + 1}`,
                        text: `يرجى إدخال المبلغ أو النسبة للشريك رقم ${pIdx + 1} في الدور رقم ${i + 1}.`,
                    });
                    return false;
                }
                sumAmt += amt;
                sumPct += pct;
            }
            const pctOk = Math.abs(sumPct - 100) <= 0.5;
            const amtOk = fullInst > 0 && Math.abs(sumAmt - fullInst) <= Math.max(0.05, fullInst * 0.005);
            if (!pctOk && !amtOk) {
                Swal.fire({
                    icon: 'warning',
                    title: `توزيع الدور رقم ${i + 1} غير مكتمل`,
                    text: `مجموع حصص الشركاء في الدور رقم ${i + 1} لازم يساوي 100%${fullInst > 0 ? ' (' + fmt(fullInst) + ' ج)' : ''} — المجموع الحالي: ${cleanNumStr(sumPct, 2)}%${fullInst > 0 ? ' (' + fmt(sumAmt) + ' ج)' : ''}.`,
                });
                return false;
            }
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
            sessionStorage.removeItem('cashflows_active_tab');
            const overviewBtn = document.querySelector('.custom-tab-btn[data-tab="tab-overview"]');
            if (overviewBtn) switchTab('tab-overview', overviewBtn);

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
    function openCollectModal(ids, total, info, parentModalId, isCompanyOnly) {
        const holder = document.getElementById('collect_ids_holder');
        holder.innerHTML = ids.map(id => `<input type="hidden" name="installment_ids[]" value="${parseInt(id)}">`).join('');
        document.getElementById('collect_info').innerText = info;
        document.getElementById('collect_amount_display').innerText = `${fmt(total)} ج`;

        const header  = document.getElementById('collect_modal_header');
        const title   = document.getElementById('collect_modal_title');
        const amtDisp = document.getElementById('collect_amount_display');
        const accLbl  = document.getElementById('collect_account_label');
        const compNt  = document.getElementById('collect_company_note');
        const btn     = document.getElementById('collect_submit_btn');

        if (isCompanyOnly) {
            header.className  = 'modal-header text-white';
            header.style.background = '#7c3aed';
            title.innerHTML   = '<i class="fa-solid fa-building-circle-arrow-right me-1"></i> سداد وإخراج حصة الشركة في الجمعية';
            amtDisp.className = 'fw-bold fs-5 mt-1';
            amtDisp.style.color = '#7c3aed';
            accLbl.innerHTML  = 'إخراج المبلغ من محفظة / خزنة: <span class="text-danger">*</span>';
            compNt.style.display = 'block';
            btn.className     = 'btn text-white fw-bold px-4';
            btn.style.background = '#7c3aed';
            btn.innerText     = 'تأكيد إخراج حصة الشركة';
        } else {
            header.className  = 'modal-header bg-success text-white';
            header.style.background = '';
            title.innerHTML   = '<i class="fa-solid fa-hand-holding-dollar me-1"></i> تسجيل تحصيل / سداد الأقساط';
            amtDisp.className = 'text-success fw-bold fs-5 mt-1';
            amtDisp.style.color = '';
            accLbl.innerHTML  = 'المحفظة / الخزنة: <span class="text-danger">*</span>';
            compNt.style.display = 'none';
            btn.className     = 'btn btn-success fw-bold px-4';
            btn.style.background = '';
            btn.innerText     = 'تأكيد العملية';
        }

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
            ? `محدد ${sel.length} مشترك — الإجمالي ${fmt(total)} ج`
            : 'حدد المشتركين اللي دفعوا علشان تسجل تحصيلهم مرة واحدة.';
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
        const allCompany = sel.every(cb => cb.dataset.company === '1');
        openCollectModal(sel.map(cb => cb.value), total, `${jamiaName} — ${sel.length} مشترك: ${names}`, parentModalId, allCompany);
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

        const header  = document.getElementById('payout_modal_header');
        const title   = document.getElementById('payout_modal_title');
        const amtDisp = document.getElementById('payout_amount_display');
        const accWrap = document.getElementById('payout_account_wrap');
        const accSel  = document.getElementById('payout_account_id');
        const accLbl  = document.getElementById('payout_account_label');
        const amtLbl  = document.getElementById('payout_amount_label');
        const btn     = document.getElementById('payout_submit_btn');
        const compNt  = document.getElementById('payout_company_note');
        const warn    = document.getElementById('payout_warning');

        if (isCompany) {
            header.className = 'modal-header bg-primary text-white';
            title.innerHTML  = `<i class="fa-solid fa-building-circle-check me-1"></i> تأكيد استلام ${escapeHtml(COMPANY_NAME)} للدور`;
            amtDisp.className = 'text-primary fw-bold fs-5 mt-1';
            if (accWrap) accWrap.style.display = 'none';
            if (accSel) { accSel.required = false; accSel.value = ''; }
            amtLbl.innerHTML = 'قيمة دور الشركة <span class="text-danger">*</span>';
            btn.className    = 'btn btn-primary fw-bold px-4';
            btn.innerText    = 'تأكيد استلام الشركة';
            compNt.style.display = 'block';
            warn.style.display   = 'none';
        } else {
            header.className = 'modal-header bg-danger text-white';
            title.innerHTML  = '<i class="fa-solid fa-money-bill-transfer me-1"></i> صرف القبض لصاحب الدور';
            amtDisp.className = 'text-danger fw-bold fs-5 mt-1';
            if (accWrap) accWrap.style.display = 'block';
            if (accSel) accSel.required = true;
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
        const isCompany = document.getElementById('payout_is_company_flag').value === '1';
        const acc = document.getElementById('payout_account_id').value;
        const amt = parseFloat(document.getElementById('payout_amount_input').value) || 0;
        const max = parseFloat(document.getElementById('payout_max_amount').value) || 0;
        if (!isCompany && !acc) {
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

    // ── Split share (تقسيم السهم بنسب ومبالغ مخصصة بين عدة شركاء) ──
    let splitModalState = {
        shareId: null,
        shareNumber: 1,
        jamiaName: '',
        totalAmount: 0,
        monthlyInstallment: 0,
        partners: [],
    };

    function openSplitShareModal(opts) {
        const fullInst = parseFloat(opts.monthlyInstallment) || 0;
        const fullPayout = parseFloat(opts.totalAmount) || 0;
        let partners = Array.isArray(opts.partners) ? opts.partners.map(p => ({
            name: p.name || '',
            phone: p.phone || '',
            company: !!p.is_company,
            percent: cleanNumStr(p.percent, 2),
            amount: cleanNumStr(p.amount, 2),
        })) : [];

        if (partners.length < 2) {
            const first = partners[0] || { name: opts.memberName || '', phone: '', company: false };
            const halfAmt = fullInst > 0 ? cleanNumStr(fullInst / 2, 2) : '';
            partners = [
                { name: first.name, phone: first.phone, company: first.company, percent: '50', amount: halfAmt },
                { name: '', phone: '', company: false, percent: '50', amount: halfAmt },
            ];
        }

        splitModalState = {
            shareId: opts.shareId,
            shareNumber: opts.shareNumber,
            jamiaName: opts.jamiaName,
            totalAmount: fullPayout,
            monthlyInstallment: fullInst,
            partners,
        };

        document.getElementById('split_share_id').value = opts.shareId;
        document.getElementById('split_jamia_info').innerText = `${opts.jamiaName} — الدور رقم ${opts.shareNumber}`;
        document.getElementById('split_full_inst_display').innerText = `${fmt(fullInst)} ج`;
        document.getElementById('split_full_payout_display').innerText = `${fmt(fullPayout)} ج`;

        renderSplitModalPartners();
        openSubModal('splitShareModal', opts.parentModalId);
    }

    function syncSplitModalFromDOM() {
        const cards = Array.from(document.querySelectorAll('#split_partners_list .split-partner-card'));
        if (cards.length === 0) return;
        splitModalState.partners = cards.map(c => ({
            name: c.querySelector('.sm-name')?.value || '',
            phone: c.querySelector('.sm-phone')?.value || '',
            company: c.querySelector('.sm-company')?.checked || false,
            amount: c.querySelector('.sm-amt')?.value ?? '',
            percent: c.querySelector('.sm-pct')?.value ?? '',
        }));
    }

    function renderSplitModalPartners() {
        const container = document.getElementById('split_partners_list');
        if (!container) return;
        container.innerHTML = '';

        const fullInst = splitModalState.monthlyInstallment;
        const fullPayout = splitModalState.totalAmount;

        splitModalState.partners.forEach((p, idx) => {
            const pctNum = parseFloat(p.percent);
            const payoutStr = (!isNaN(pctNum) && fullPayout > 0) ? fmt(fullPayout * (pctNum / 100)) + ' ج' : '0.00 ج';
            const card = document.createElement('div');
            card.className = 'split-partner-card' + (p.company ? ' is-company' : '');
            card.dataset.idx = idx;
            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2 py-1" style="background:linear-gradient(135deg,#0d9488,#0284c7); color:#fff; font-size:0.75rem;">
                        <i class="fa-solid fa-user me-1"></i> الشريك رقم ${idx + 1}
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 sm-payout-badge" style="font-size:0.75rem;">
                            القبض: ${payoutStr}
                        </span>
                        ${splitModalState.partners.length > 2 ? `<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeSplitModalPartner(${idx})" title="حذف الشريك"><i class="fa-solid fa-trash-can"></i></button>` : ''}
                    </div>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1" style="font-size:0.78rem;">اسم الشريك <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm sm-name" name="partners[${idx}][name]" placeholder="اسم الشريك ${idx + 1}" value="${escapeHtml(p.name)}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1" style="font-size:0.78rem;">الهاتف</label>
                        <input type="text" class="form-control form-control-sm sm-phone" name="partners[${idx}][phone]" placeholder="اختياري" dir="ltr" value="${escapeHtml(p.phone || '')}">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label fw-bold mb-1" style="font-size:0.78rem;">القسط الشهري</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="any" min="0.01" class="form-control fw-bold text-center sm-amt" name="partners[${idx}][amount]" placeholder="المبلغ" value="${escapeHtml(p.amount)}" oninput="onSplitModalAmountInput(${idx}, this)">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label fw-bold mb-1" style="font-size:0.78rem;">النسبة</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="any" min="0.01" max="100" class="form-control fw-bold text-center sm-pct" name="partners[${idx}][percent]" placeholder="%" value="${escapeHtml(p.percent)}" oninput="onSplitModalPercentInput(${idx}, this)">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-1 text-center">
                        <label class="form-label fw-bold mb-1 d-block" style="font-size:0.7rem;">الشركة</label>
                        <input type="checkbox" class="form-check-input sm-company" name="partners[${idx}][is_company]" value="1" ${p.company ? 'checked' : ''} onchange="onSplitModalCompanyToggle(this)" title="حصة ملك {{ $companyName }}">
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        updateSplitModalSummary();
    }

    function onSplitModalAmountInput(idx, inputEl) {
        const fullInst = splitModalState.monthlyInstallment;
        const fullPayout = splitModalState.totalAmount;
        const card = inputEl.closest('.split-partner-card');
        const pctInput = card.querySelector('.sm-pct');
        const badge = card.querySelector('.sm-payout-badge');
        const amt = parseFloat(inputEl.value);
        if (!isNaN(amt) && amt >= 0 && fullInst > 0) {
            const pct = (amt / fullInst) * 100;
            pctInput.value = cleanNumStr(pct, 2);
            if (badge) badge.textContent = `القبض: ${fmt(fullPayout * (pct / 100))} ج`;
        }
        updateSplitModalSummary();
    }

    function onSplitModalPercentInput(idx, inputEl) {
        const fullInst = splitModalState.monthlyInstallment;
        const fullPayout = splitModalState.totalAmount;
        const card = inputEl.closest('.split-partner-card');
        const amtInput = card.querySelector('.sm-amt');
        const badge = card.querySelector('.sm-payout-badge');
        const pct = parseFloat(inputEl.value);
        if (!isNaN(pct) && pct >= 0) {
            if (fullInst > 0) amtInput.value = cleanNumStr(fullInst * (pct / 100), 2);
            if (badge) badge.textContent = `القبض: ${fmt(fullPayout * (pct / 100))} ج`;
        }
        updateSplitModalSummary();
    }

    function onSplitModalCompanyToggle(cb) {
        const card = cb.closest('.split-partner-card');
        const nameInput = card.querySelector('.sm-name');
        card.classList.toggle('is-company', cb.checked);
        if (cb.checked && nameInput.value.trim() === '') nameInput.value = COMPANY_NAME;
        if (!cb.checked && nameInput.value.trim() === COMPANY_NAME) nameInput.value = '';
    }

    function addSplitModalPartner() {
        syncSplitModalFromDOM();
        const fullInst = splitModalState.monthlyInstallment;
        const pcts = splitModalState.partners.map(p => parseFloat(p.percent) || 0);
        const sumPct = pcts.reduce((a, b) => a + b, 0);
        const remPct = Math.round((100 - sumPct) * 100) / 100;
        const allEqual = pcts.length >= 2 && pcts.every(v => Math.abs(v - pcts[0]) < 0.25) && Math.abs(sumPct - 100) < 0.5;

        if (remPct > 0.05) {
            const remAmt = fullInst > 0 ? cleanNumStr(fullInst * (remPct / 100), 2) : '';
            splitModalState.partners.push({ name: '', phone: '', company: false, percent: cleanNumStr(remPct, 2), amount: remAmt });
        } else if (allEqual) {
            splitModalState.partners.push({ name: '', phone: '', company: false, percent: '0', amount: '' });
            distributeEqualOnList(splitModalState.partners, fullInst);
        } else {
            splitModalState.partners.push({ name: '', phone: '', company: false, percent: '', amount: '' });
        }

        renderSplitModalPartners();
        const lastInput = document.querySelector(`#split_partners_list .split-partner-card[data-idx="${splitModalState.partners.length - 1}"] .sm-name`);
        if (lastInput) lastInput.focus();
    }

    function removeSplitModalPartner(idx) {
        syncSplitModalFromDOM();
        if (splitModalState.partners.length <= 2) return;
        splitModalState.partners.splice(idx, 1);
        renderSplitModalPartners();
    }

    function equalizeSplitModalPartners() {
        syncSplitModalFromDOM();
        distributeEqualOnList(splitModalState.partners, splitModalState.monthlyInstallment);
        renderSplitModalPartners();
    }

    function updateSplitModalSummary() {
        const cards = Array.from(document.querySelectorAll('#split_partners_list .split-partner-card'));
        const fullInst = splitModalState.monthlyInstallment;
        let sumAmt = 0;
        let sumPct = 0;
        cards.forEach(c => {
            const a = parseFloat(c.querySelector('.sm-amt')?.value);
            const p = parseFloat(c.querySelector('.sm-pct')?.value);
            if (!isNaN(a) && a > 0) sumAmt += a;
            if (!isNaN(p) && p > 0) sumPct += p;
            else if (!isNaN(a) && a > 0 && fullInst > 0) sumPct += (a / fullInst) * 100;
        });

        const diffPct = 100 - sumPct;
        const isOk = Math.abs(diffPct) <= 0.5 || (fullInst > 0 && Math.abs(fullInst - sumAmt) <= Math.max(0.05, fullInst * 0.005));

        const box = document.getElementById('split_modal_summary');
        const submitBtn = document.getElementById('split_modal_submit_btn');
        if (!box) return;

        if (isOk) {
            box.style.background = '#dcfce7';
            box.style.border = '1.5px solid #86efac';
            box.style.color = '#166534';
            box.innerHTML = `
                <span><i class="fa-solid fa-circle-check me-1"></i> التوزيع مكتمل 100% (${cards.length} شركاء)</span>
                <span>الإجمالي: ${fmt(sumAmt)} / ${fmt(fullInst)} ج (${cleanNumStr(sumPct, 2)}%)</span>
            `;
            if (submitBtn) submitBtn.disabled = false;
        } else if (diffPct > 0) {
            box.style.background = '#fef3c7';
            box.style.border = '1.5px solid #fcd34d';
            box.style.color = '#92400e';
            box.innerHTML = `
                <span><i class="fa-solid fa-triangle-exclamation me-1"></i> متبقي للتوزيع: ${fmt(Math.max(0, fullInst - sumAmt))} ج (${cleanNumStr(diffPct, 2)}%)</span>
                <span>الحالي: ${fmt(sumAmt)} / ${fmt(fullInst)} ج (${cleanNumStr(sumPct, 2)}%)</span>
            `;
            if (submitBtn) submitBtn.disabled = false;
        } else {
            box.style.background = '#fee2e2';
            box.style.border = '1.5px solid #fca5a5';
            box.style.color = '#991b1b';
            box.innerHTML = `
                <span><i class="fa-solid fa-circle-exclamation me-1"></i> المجموع أكبر من السهم بـ ${fmt(Math.max(0, sumAmt - fullInst))} ج (+${cleanNumStr(Math.abs(diffPct), 2)}%)</span>
                <span>الحالي: ${fmt(sumAmt)} / ${fmt(fullInst)} ج (${cleanNumStr(sumPct, 2)}%)</span>
            `;
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    function validateSplitModalSubmit() {
        syncSplitModalFromDOM();
        const fullInst = splitModalState.monthlyInstallment;
        const parts = splitModalState.partners;
        if (parts.length < 2) {
            Swal.fire({ icon: 'warning', title: 'لازم شريكين على الأقل لتقسيم السهم' });
            return false;
        }
        let sumAmt = 0;
        let sumPct = 0;
        for (let i = 0; i < parts.length; i++) {
            const amt = parseFloat(parts[i].amount) || 0;
            const pct = parseFloat(parts[i].percent) || (fullInst > 0 && amt > 0 ? (amt / fullInst) * 100 : 0);
            if (amt <= 0 && pct <= 0) {
                Swal.fire({ icon: 'warning', title: `يرجى إدخال قسط أو نسبة للشريك رقم ${i + 1}` });
                return false;
            }
            sumAmt += amt;
            sumPct += pct;
        }
        const pctOk = Math.abs(sumPct - 100) <= 0.5;
        const amtOk = fullInst > 0 && Math.abs(sumAmt - fullInst) <= Math.max(0.05, fullInst * 0.005);
        if (!pctOk && !amtOk) {
            Swal.fire({
                icon: 'warning',
                title: 'مجموع الحصص لازم يساوي 100%',
                text: `الإجمالي الحالي: ${fmt(sumAmt)} ج (${cleanNumStr(sumPct, 2)}%) — المطلوب: ${fmt(fullInst)} ج (100%).`,
            });
            return false;
        }
        rememberActiveParentForSubmit();
        return true;
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

    // ══════════════════════════════════════════════════════════════
    // ── كشف حساب عضو الجمعيات (صورة / واتساب / تحميل / طباعة) ──
    // ══════════════════════════════════════════════════════════════
    let statementParentModalId = null;
    let currentStatementData = null;

    function waNormalizePhone(phone) {
        let raw = (phone || '').replace(/\D/g, '');
        if (!raw) return '';
        if (raw.startsWith('20')) return raw;
        if (raw.startsWith('0'))  return '2' + raw;   // 01xxxxxxxxx -> 201xxxxxxxxx
        if (raw.length === 10 && raw.startsWith('1')) return '20' + raw;
        return raw;
    }

    function openMemberStatementModal(opts) {
        opts = opts || {};
        statementParentModalId = opts.parentModalId || null;

        if (statementParentModalId) {
            const pEl = document.getElementById(statementParentModalId);
            const pInst = pEl ? bootstrap.Modal.getInstance(pEl) : null;
            if (pInst) pInst.hide();
        }

        const sel = document.getElementById('statementMemberSelect');
        const modalEl = document.getElementById('memberStatementModal');
        const modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);

        // إذا تم تمرير عضو محدد
        if (opts.memberName || opts.shareId) {
            let matchedOption = false;
            if (sel) {
                for (let i = 0; i < sel.options.length; i++) {
                    const opt = sel.options[i];
                    if (opts.memberName && opt.value === opts.memberName) {
                        sel.selectedIndex = i;
                        matchedOption = true;
                        break;
                    }
                    if (opts.shareId && opt.dataset.shareId === String(opts.shareId)) {
                        sel.selectedIndex = i;
                        matchedOption = true;
                        break;
                    }
                }
            }

            loadMemberStatement({
                share_id: opts.shareId || '',
                name: opts.memberName || '',
                phone: opts.memberPhone || ''
            });
        } else {
            // فتح عام بدون تحديد عضو
            if (sel && sel.value) {
                onStatementMemberChanged(sel.value);
            } else {
                document.getElementById('statementLoading').style.display = 'none';
                document.getElementById('statementError').style.display = 'none';
                document.getElementById('statementCardWrap').style.display = 'none';
                document.getElementById('statementEmpty').style.display = 'block';
            }
        }

        modalInst.show();
    }

    // إعادة فتح المودال الأب لو كان تم فتحه منه
    document.addEventListener('DOMContentLoaded', function() {
        const stmtEl = document.getElementById('memberStatementModal');
        if (stmtEl) {
            stmtEl.addEventListener('hidden.bs.modal', function() {
                if (statementParentModalId) {
                    const pEl = document.getElementById(statementParentModalId);
                    if (pEl) bootstrap.Modal.getOrCreateInstance(pEl).show();
                    statementParentModalId = null;
                }
            });
        }
    });

    function onStatementMemberChanged(memberName) {
        if (!memberName) {
            document.getElementById('statementLoading').style.display = 'none';
            document.getElementById('statementError').style.display = 'none';
            document.getElementById('statementCardWrap').style.display = 'none';
            document.getElementById('statementEmpty').style.display = 'block';
            return;
        }

        const sel = document.getElementById('statementMemberSelect');
        const opt = sel ? sel.options[sel.selectedIndex] : null;
        const phone = opt ? (opt.dataset.phone || '') : '';
        const shareId = opt ? (opt.dataset.shareId || '') : '';

        loadMemberStatement({
            name: memberName,
            phone: phone,
            share_id: shareId
        });
    }

    function reloadCurrentStatement() {
        const sel = document.getElementById('statementMemberSelect');
        if (sel && sel.value) {
            onStatementMemberChanged(sel.value);
        } else if (currentStatementData && currentStatementData.summary) {
            loadMemberStatement({
                name: currentStatementData.summary.display_name,
                phone: document.getElementById('statementPhoneInput').value.trim()
            });
        }
    }

    function loadMemberStatement(params) {
        document.getElementById('statementEmpty').style.display = 'none';
        document.getElementById('statementError').style.display = 'none';
        document.getElementById('statementCardWrap').style.display = 'none';
        document.getElementById('statementLoading').style.display = 'block';

        const q = new URLSearchParams(params);
        q.set('format', 'json');

        fetch(`{{ route('cashflows.member_statement') }}?${q.toString()}`)
            .then(res => {
                if (!res.ok) {
                    return res.json().then(data => { throw new Error(data.error || 'تعذر تحميل كشف الحساب.'); });
                }
                return res.json();
            })
            .then(data => {
                document.getElementById('statementLoading').style.display = 'none';
                renderMemberStatement(data);
            })
            .catch(err => {
                document.getElementById('statementLoading').style.display = 'none';
                const errBox = document.getElementById('statementError');
                errBox.innerText = err.message || 'حدث خطأ أثناء تحميل البيانات.';
                errBox.style.display = 'block';
            });
    }

    function renderMemberStatement(data) {
        currentStatementData = data;
        const s = data.summary;
        const phoneInput = document.getElementById('statementPhoneInput');
        if (phoneInput) phoneInput.value = s.display_phone || '';

        const card = document.getElementById('memberStatementCard');
        if (!card) return;

        const isLate = (s.late_count || 0) > 0;
        const statusBadge = isLate
            ? `<span class="statement-badge badge-danger"><i class="fa fa-triangle-exclamation me-1"></i> يوجد ${s.late_count} قسط متأخر (${fmt(s.late_amount)} ج)</span>`
            : `<span class="statement-badge badge-success"><i class="fa fa-check-circle me-1"></i> منتظم بالسداد بالكامل</span>`;

        // Shares rows
        const sharesHtml = data.shares.map(sh => {
            const frac = parseFloat(sh.share_fraction || 1);
            const isHalf = (frac < 0.9999);
            const pctLbl = parseFloat((frac * 100).toFixed(2)) + '%';
            const statusHtml = sh.is_collected
                ? `<span class="statement-badge badge-success"><i class="fa fa-check me-1"></i> تم الصرف (${sh.collected_at || ''} ${sh.collected_account ? 'من ' + escapeHtml(sh.collected_account) : ''})</span>`
                : (sh.status_class === 'warning'
                    ? `<span class="statement-badge badge-warning"><i class="fa fa-clock me-1"></i> مستحق الصرف الآن</span>`
                    : `<span class="statement-badge badge-info">بانتظار حلول الشهر</span>`);

            return `
                <tr>
                    <td style="text-align:center;"><span class="turn-num">${sh.share_number}</span>${isHalf ? `<div style="font-size:0.65rem;color:#0284c7;font-weight:700;">${pctLbl}</div>` : ''}</td>
                    <td class="fw-bold">${escapeHtml(sh.jamia_name)}</td>
                    <td>${escapeHtml(sh.payout_month_ar)}</td>
                    <td class="fw-bold text-success">${fmt(sh.payout_amount)} ج</td>
                    <td>${statusHtml}</td>
                    <td class="text-muted" style="font-size:0.75rem;">${sh.is_transferred ? 'بديل متنازل له' : (isHalf ? `حصة (${pctLbl})` : 'سهم كامل')}</td>
                </tr>
            `;
        }).join('');

        // Installments rows
        const instsHtml = data.installments.map(inst => {
            const isPaid = !!inst.is_paid;
            const isLateInst = inst.status_key === 'late';
            const rowClass = isPaid ? 'row-paid' : (isLateInst ? 'row-late' : '');

            let stBadge = '';
            if (isPaid) {
                stBadge = `<span class="statement-badge badge-success"><i class="fa fa-check me-1"></i> مسدد</span>`;
            } else if (isLateInst) {
                stBadge = `<span class="statement-badge badge-danger"><i class="fa fa-exclamation-triangle me-1"></i> متأخر (${inst.days_late} يوم)</span>`;
            } else if (inst.status_key === 'open') {
                stBadge = `<span class="statement-badge badge-warning">جاري التحصيل</span>`;
            } else {
                stBadge = `<span class="statement-badge badge-gray">قادم</span>`;
            }

            const payDetails = isPaid
                ? `<div style="font-size:0.74rem; color:#475569;">${inst.paid_at || ''} ${inst.paid_account ? '(' + escapeHtml(inst.paid_account) + ')' : ''}</div>`
                : `<span class="text-muted">—</span>`;

            return `
                <tr class="stmt-inst-row ${rowClass}" data-paid="${isPaid ? '1' : '0'}" data-late="${isLateInst ? '1' : '0'}">
                    <td class="fw-bold">${escapeHtml(inst.month_date_ar)}</td>
                    <td>${escapeHtml(inst.jamia_name)}</td>
                    <td style="text-align:center;">دور ${inst.share_number}</td>
                    <td class="fw-bold">${fmt(inst.amount)} ج</td>
                    <td>${stBadge}</td>
                    <td>${payDetails}</td>
                </tr>
            `;
        }).join('');

        const todayDate = new Date().toLocaleDateString('ar-EG', { year:'numeric', month:'long', day:'numeric' });
        const todayTime = new Date().toLocaleTimeString('ar-EG', { hour:'2-digit', minute:'2-digit' });

        card.innerHTML = `
            <!-- Top Branding Header -->
            <div class="statement-header">
                <div class="statement-brand">
                    <h3>${escapeHtml(COMPANY_NAME)}</h3>
                    <div class="sub">منظومة إدارة الجمعيات والتدفقات النقدية — كشف حساب اشتراكات رسمي</div>
                </div>
                <div class="statement-meta">
                    <div><strong>تاريخ الإصدار:</strong> ${todayDate}</div>
                    <div><strong>توقيت الإصدار:</strong> ${todayTime}</div>
                </div>
            </div>

            <!-- Member Banner -->
            <div class="statement-member-banner">
                <div>
                    <h4 class="statement-member-name">${escapeHtml(s.display_name)}</h4>
                    <div class="statement-member-details">
                        <i class="fa-solid fa-phone me-1 text-teal"></i> ${escapeHtml(s.display_phone || 'لا يوجد هاتف مسجل')}
                        <span class="mx-2">|</span>
                        مشترك بـ <strong>${s.shares_count} حصص / أسماء</strong> في <strong>${s.jamias_count} جمعية</strong> (${s.jamias_names.map(escapeHtml).join('، ')})
                    </div>
                </div>
                <div>${statusBadge}</div>
            </div>

            <!-- 4 KPI Stat Boxes -->
            <div class="statement-kpi-grid">
                <div class="statement-kpi-box kpi-teal">
                    <span class="statement-kpi-label">الحصص والجمعيات</span>
                    <div class="statement-kpi-val text-teal">${s.shares_count} حصص (${s.jamias_count} جمعية)</div>
                    <div class="statement-kpi-sub">${s.jamias_names.map(escapeHtml).join(' — ')}</div>
                </div>
                <div class="statement-kpi-box kpi-green">
                    <span class="statement-kpi-label">إجمالي القبض المستحق</span>
                    <div class="statement-kpi-val text-success">${fmt(s.total_payout)} ج</div>
                    <div class="statement-kpi-sub">قبض: ${fmt(s.collected_payout)} ج | متبقي: ${fmt(s.remaining_payout)} ج</div>
                </div>
                <div class="statement-kpi-box kpi-orange">
                    <span class="statement-kpi-label">إجمالي الأقساط المطلوبة</span>
                    <div class="statement-kpi-val">${fmt(s.total_installments_due)} ج</div>
                    <div class="statement-kpi-sub">سدد: ${fmt(s.total_paid)} ج | متبقي: ${fmt(s.total_remaining)} ج</div>
                </div>
                <div class="statement-kpi-box ${isLate ? 'kpi-red' : 'kpi-green'}">
                    <span class="statement-kpi-label">الموقف المالي والمتأخرات</span>
                    <div class="statement-kpi-val ${isLate ? 'text-danger' : 'text-success'}">
                        ${isLate ? fmt(s.late_amount) + ' ج متأخر' : 'لا توجد متأخرات'}
                    </div>
                    <div class="statement-kpi-sub">صافي التدفق: ${fmt(s.net_position)} ج</div>
                </div>
            </div>

            <!-- Section 1: Payout Schedule -->
            <div class="statement-sec-title">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i> أولاً: بيان الحصص وأدوار القبض (متى وكم سيقبض)
            </div>
            <table class="statement-table">
                <thead>
                    <tr>
                        <th style="width:8%; text-align:center;">الدور</th>
                        <th style="width:26%;">اسم الجمعية</th>
                        <th style="width:20%;">شهر استحقاق القبض</th>
                        <th style="width:16%;">قيمة القبض</th>
                        <th style="width:20%;">موقف الصرف</th>
                        <th style="width:10%;">الصفة</th>
                    </tr>
                </thead>
                <tbody>
                    ${sharesHtml}
                </tbody>
            </table>

            <!-- Section 2: Installments Schedule -->
            <div class="statement-sec-title">
                <i class="fa-solid fa-calendar-check me-1"></i> ثانياً: جدول الأقساط الشهرية وموقف السداد (ماذا سيدفع وما تم سداده وما تبقى)
            </div>
            <table class="statement-table">
                <thead>
                    <tr>
                        <th style="width:18%;">الشهر</th>
                        <th style="width:22%;">اسم الجمعية</th>
                        <th style="width:10%; text-align:center;">الدور</th>
                        <th style="width:15%;">القسط المطلوب</th>
                        <th style="width:17%;">حالة السداد</th>
                        <th style="width:18%;">تاريخ وطريقة السداد</th>
                    </tr>
                </thead>
                <tbody id="statementInstTbody">
                    ${instsHtml}
                </tbody>
            </table>

            <!-- Footer note & stamp -->
            <div class="statement-footer">
                <div style="font-size:0.72rem;">في حال وجود أي استفسار يرجى مراجعة إدارة الحسابات.</div>
                <div style="text-align:left;">
                    <div>اعتماد الإدارة: <span style="border-bottom:1px solid #94a3b8; display:inline-block; width:90px;"></span></div>
                </div>
            </div>
        `;

        document.getElementById('statementCardWrap').style.display = 'block';
    }

    function filterStatementInstallments(type, btn) {
        document.querySelectorAll('.stmt-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const rows = document.querySelectorAll('.stmt-inst-row');
        rows.forEach(r => {
            const isPaid = r.dataset.paid === '1';
            const isLate = r.dataset.late === '1';
            if (type === 'all') {
                r.style.display = '';
            } else if (type === 'unpaid') {
                r.style.display = (!isPaid) ? '' : 'none';
            } else if (type === 'paid') {
                r.style.display = isPaid ? '' : 'none';
            }
        });
    }

    function getStatementIsoHeadStyles() {
        let html = '';
        document.querySelectorAll('style, link[rel="stylesheet"]').forEach(function(el) {
            html += el.outerHTML;
        });
        return html;
    }

    async function captureStatementCanvas() {
        const card = document.getElementById('memberStatementCard');
        if (!card) throw new Error('عنصر كشف الحساب غير موجود');

        // انتظار اكتمال تحميل الخطوط العربية قبل الرسم
        if (document.fonts && document.fonts.ready) {
            try { await document.fonts.ready; } catch (e) {}
        }

        // المسار الأساسي: تصوير داخل iframe معزول لمنع وراثة letter-spacing السالب من ثيم الصفحة والذي يُفتّت الحروف العربية
        try {
            return await new Promise((resolve, reject) => {
                const iframe = document.createElement('iframe');
                iframe.setAttribute('aria-hidden', 'true');
                iframe.style.cssText = 'position:fixed;left:-99999px;top:0;width:840px;height:10px;border:0;visibility:hidden;z-index:-999;';
                document.body.appendChild(iframe);

                let cleaned = false;
                const cleanup = () => {
                    if (!cleaned) {
                        cleaned = true;
                        if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
                    }
                };

                try {
                    const idoc = iframe.contentDocument || iframe.contentWindow.document;
                    idoc.open();
                    idoc.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8">
                        <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
                        <link href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
                        <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
                        ${getStatementIsoHeadStyles()}
                        <style>
                            *, *::before, *::after {
                                letter-spacing: normal !important;
                                letter-spacing: 0px !important;
                                word-spacing: normal !important;
                                font-feature-settings: normal !important;
                            }
                            body {
                                margin: 0;
                                padding: 15px;
                                background: #ffffff;
                                font-family: 'Cairo', 'IBM Plex Sans Arabic', Tahoma, sans-serif !important;
                                direction: rtl;
                            }
                            .statement-card-container {
                                width: 820px !important;
                                max-width: 820px !important;
                                margin: 0 auto !important;
                                box-shadow: none !important;
                                border: 1.5px solid #cbd5e1 !important;
                            }
                        </style>
                    </head><body></body></html>`);
                    idoc.close();

                    const target = idoc.importNode(card, true);
                    target.style.position = 'static';
                    target.style.left = 'auto';
                    target.style.margin = '0 auto';
                    idoc.body.appendChild(target);

                    const done = () => {
                        html2canvas(target, {
                            scale: 2,
                            backgroundColor: '#ffffff',
                            useCORS: true,
                            logging: false,
                            imageTimeout: 0,
                            onclone: function(doc) {
                                const s = doc.createElement('style');
                                s.textContent = `
                                    *, *::before, *::after {
                                        letter-spacing: normal !important;
                                        letter-spacing: 0px !important;
                                        word-spacing: normal !important;
                                        font-feature-settings: normal !important;
                                    }
                                    body, h1, h2, h3, h4, h5, h6, div, p, span, td, th {
                                        letter-spacing: normal !important;
                                        letter-spacing: 0px !important;
                                        font-family: 'Cairo', 'IBM Plex Sans Arabic', Tahoma, sans-serif !important;
                                    }
                                `;
                                (doc.head || doc.documentElement).appendChild(s);
                                doc.querySelectorAll('.no-capture').forEach(n => n.style.display = 'none');
                            }
                        }).then(canvas => {
                            if (!canvas || canvas.width < 50 || canvas.height < 50) {
                                cleanup();
                                reject(new Error('empty canvas'));
                                return;
                            }
                            cleanup();
                            resolve(canvas);
                        }).catch(e => {
                            cleanup();
                            reject(e);
                        });
                    };

                    let settled = false;
                    const go = () => { if (settled) return; settled = true; setTimeout(done, 60); };
                    const fr = (idoc.fonts && idoc.fonts.ready) ? idoc.fonts.ready : Promise.resolve();
                    fr.then(go);
                    setTimeout(go, 800);
                } catch (e) {
                    cleanup();
                    reject(e);
                }
            });
        } catch (isoErr) {
            console.warn('Isolated capture fallback:', isoErr);
            return await html2canvas(card, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
                onclone: (clonedDoc) => {
                    const s = clonedDoc.createElement('style');
                    s.textContent = `
                        *, *::before, *::after {
                            letter-spacing: normal !important;
                            letter-spacing: 0px !important;
                            word-spacing: normal !important;
                            font-feature-settings: normal !important;
                        }
                        body, h1, h2, h3, h4, h5, h6, div, p, span, td, th {
                            letter-spacing: normal !important;
                            letter-spacing: 0px !important;
                            font-family: 'Cairo', 'IBM Plex Sans Arabic', Tahoma, sans-serif !important;
                        }
                    `;
                    (clonedDoc.head || clonedDoc.documentElement).appendChild(s);

                    const el = clonedDoc.getElementById('memberStatementCard');
                    if (el) {
                        el.style.width = '820px';
                        el.style.maxWidth = '820px';
                        el.style.margin = '0 auto';
                        el.style.background = '#ffffff';
                    }
                    clonedDoc.querySelectorAll('.no-capture').forEach(n => n.style.display = 'none');
                }
            });
        }
    }

    async function downloadMemberStatementImage() {
        if (!currentStatementData || !currentStatementData.summary) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى اختيار عضو وعرض كشف حسابه أولاً.' });
            return;
        }

        const btn = document.getElementById('btnDownloadPng');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> جاري التوليد...'; }

        try {
            const canvas = await captureStatementCanvas();
            const memberName = (currentStatementData.summary.display_name || 'عضو').replace(/[^a-zA-Z0-9\u0600-\u06FF_-]/g, '_');
            const filename = `كشف_حساب_${memberName}_${new Date().toISOString().slice(0, 10)}.png`;

            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            Swal.fire({
                title: 'تم استخراج الصورة بنجاح!',
                text: `تم تنزيل صورة كشف حساب (${currentStatementData.summary.display_name}) بجودة فائقة.`,
                icon: 'success',
                timer: 2500,
                showConfirmButton: false
            });
        } catch (err) {
            console.error(err);
            alert('حدث خطأ أثناء تحميل الصورة: ' + err.message);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = oldHtml; }
        }
    }

    async function sendMemberStatementWhatsApp() {
        if (!currentStatementData || !currentStatementData.summary) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى اختيار عضو وعرض كشف حسابه أولاً.' });
            return;
        }

        const btn = document.getElementById('btnSendWa');
        const oldHtml = btn ? btn.innerHTML : '';
        const phoneInput = document.getElementById('statementPhoneInput');
        let phone = phoneInput ? phoneInput.value.trim() : (currentStatementData.summary.display_phone || '');

        const cleanPhone = waNormalizePhone(phone);
        if (!cleanPhone) {
            Swal.fire({
                icon: 'warning',
                title: 'رقم الهاتف مطلوب',
                text: 'يرجى كتابة رقم هاتف العضو لإرسال كشف الحساب عبر الواتساب.',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#0d9488'
            });
            if (phoneInput) phoneInput.focus();
            return;
        }

        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> تجهيز الصورة...'; }

        try {
            const canvas = await captureStatementCanvas();
            const memberName = currentStatementData.summary.display_name || 'العضو المحترم';
            const s = currentStatementData.summary;

            const greeting = `السلام عليكم ورحمة الله وبركاته،\nأ/ ${memberName} المحترم\n\nتفضل كشف حساب اشتراكات الجمعيات الخاص بحضرتك من *${COMPANY_NAME}*:\n`
                + `📌 عدد الحصص: ${s.shares_count} في ${s.jamias_count} جمعية (${s.jamias_names.join('، ')})\n`
                + `💰 إجمالي مبالغ القبض: ${fmt(s.total_payout)} ج\n`
                + `✅ إجمالي المسدد: ${fmt(s.total_paid)} ج\n`
                + `⏳ المتبقي سداده: ${fmt(s.total_remaining)} ج\n`
                + (s.late_count > 0 ? `⚠️ متأخرات حالية: ${fmt(s.late_amount)} ج في ${s.late_count} قسط\n` : `✨ منتظم بالسداد بالكامل\n`)
                + `\nمرفق صورة كشف الحساب التفصيلي بمواعيد القبض وجدول الأقساط الشهرية.`;

            const waWebUrl = 'https://web.whatsapp.com/send?phone=' + cleanPhone + '&text=' + encodeURIComponent(greeting);

            canvas.toBlob(async (blob) => {
                if (!blob) {
                    alert('تعذر إنشاء صورة الكشف');
                    return;
                }
                const file = new File([blob], `كشف_حساب_${memberName}.png`, { type: 'image/png' });

                // 1) Mobile: Web Share API (يرسل ملف الصورة مباشرة إلى تطبيق واتساب)
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            files: [file],
                            title: `كشف حساب جمعيات - ${memberName}`,
                            text: greeting
                        });
                        return;
                    } catch (shareErr) {
                        console.log('Share dismissed or cancelled:', shareErr);
                    }
                }

                // 2) Desktop: نسخ الصورة للكليبورد + فتح محادثة واتساب ويب مباشرة
                try {
                    const clipItem = new ClipboardItem({ 'image/png': blob });
                    await navigator.clipboard.write([clipItem]);
                    window.open(waWebUrl, '_blank');
                    Swal.fire({
                        icon: 'success',
                        title: 'الصورة جاهزة في الحافظة 📋',
                        html: `تم نسخ صورة كشف الحساب للكليبورد (Clipboard) وفُتحت محادثة الواتساب مع <b>${memberName}</b>.<br><br><b>اضغط داخل المحادثة (Ctrl + V) ثم إرسال.</b>`,
                        confirmButtonText: 'تمام، فهمت',
                        confirmButtonColor: '#25d366',
                        timer: 10000,
                        timerProgressBar: true
                    });
                } catch (clipErr) {
                    // Fallback لو الكليبورد غير متاح
                    window.open(waWebUrl, '_blank');
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `كشف_حساب_${memberName}.png`;
                    a.click();
                    setTimeout(() => URL.revokeObjectURL(url), 1500);

                    Swal.fire({
                        icon: 'info',
                        title: 'تم فتح واتساب وتنزيل الصورة',
                        html: `تم فتح محادثة الواتساب وتنزيل صورة كشف الحساب.<br>يمكنك إرفاق الصورة من زر المرفقات 📎 وإرسالها مباشرة.`,
                        confirmButtonText: 'تمام',
                        confirmButtonColor: '#25d366'
                    });
                }
            }, 'image/png');

        } catch (err) {
            console.error(err);
            alert('حدث خطأ أثناء إرسال الكشف: ' + err.message);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = oldHtml; }
        }
    }

    function printMemberStatement() {
        if (!currentStatementData || !currentStatementData.summary) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى اختيار عضو وعرض كشف حسابه أولاً.' });
            return;
        }
        const name = currentStatementData.summary.display_name;
        const phone = document.getElementById('statementPhoneInput').value.trim() || currentStatementData.summary.display_phone;
        const url = `{{ route('cashflows.print_member') }}?name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`;
        window.open(url, '_blank');
    }

    function copyStatementText() {
        if (!currentStatementData || !currentStatementData.summary) {
            Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى اختيار عضو وعرض كشف حسابه أولاً.' });
            return;
        }
        const s = currentStatementData.summary;
        let text = `*كشف حساب الجمعيات — ${COMPANY_NAME}*\n`
            + `العضو: ${s.display_name} (${s.display_phone || 'بدون هاتف'})\n`
            + `عدد الأسماء: ${s.shares_count} حصص في ${s.jamias_count} جمعية (${s.jamias_names.join('، ')})\n`
            + `--------------------------\n`
            + `💰 إجمالي مبالغ القبض: ${fmt(s.total_payout)} ج\n`
            + `  - تم القبض: ${fmt(s.collected_payout)} ج\n`
            + `  - متبقي للقبض: ${fmt(s.remaining_payout)} ج\n`
            + `💳 إجمالي الأقساط المطلوبة: ${fmt(s.total_installments_due)} ج\n`
            + `  - تم سداده: ${fmt(s.total_paid)} ج\n`
            + `  - متبقي سداده: ${fmt(s.total_remaining)} ج\n`
            + (s.late_count > 0 ? `⚠️ المتأخرات: ${fmt(s.late_amount)} ج (${s.late_count} قسط)\n` : `✨ لا توجد أي متأخرات\n`)
            + `--------------------------\n`
            + `أدوار القبض:\n`;

        currentStatementData.shares.forEach(sh => {
            text += `• دور ${sh.share_number} (${sh.jamia_name}) — شهر ${sh.payout_month_ar}: ${fmt(sh.payout_amount)} ج [${sh.status_label}]\n`;
        });

        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                title: 'تم نسخ الملخص!',
                text: 'تم نسخ ملخص كشف الحساب كنص إلى الحافظة.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(() => {
            alert('تعذر نسخ النص.');
        });
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
