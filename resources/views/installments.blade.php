<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منظومة العقود والأقساط - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ══════════════════════════════════════════════
           INSTALLMENTS — World-class Design System
           ══════════════════════════════════════════════ */
        :root {
            --bg:           #f6f8fb;
            --surface:      #ffffff;
            --surface-2:    #fafbfd;
            --text-main:    #0f172a;
            --text-muted:   #5a6478;
            --text-soft:    #8b95a9;
            --border:       #e6ebf3;
            --border-2:     #d4dbe6;
            --hover-bg:     #f1f4f9;

            --accent:       #4f46e5;
            --accent-2:     #393984;
            --accent-bg:    #eef2ff;
            --success:      #059669;
            --success-bg:   #ecfdf5;
            --danger:       #dc2626;
            --danger-bg:    #fef2f2;
            --warning:      #d97706;
            --warning-bg:   #fffbeb;
            --info:         #0284c7;
            --info-bg:      #f0f9ff;
            --violet:       #7c3aed;
            --violet-bg:    #f5f3ff;

            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 16px;
            --shadow-xs: 0 1px 2px rgba(15,23,42,0.04);
            --shadow-sm: 0 1px 3px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
            --shadow-md: 0 4px 12px rgba(15,23,42,0.05), 0 2px 4px rgba(15,23,42,0.03);
            --shadow-lg: 0 12px 32px rgba(15,23,42,0.08), 0 4px 12px rgba(15,23,42,0.04);
            --t: 200ms cubic-bezier(.4, 0, .2, 1);

            /* legacy aliases preserved */
            --bg-page:       #f6f8fb;
            --bg-card:       #ffffff;
            --bg-table-head: #fafbfd;
            --bg-row-alt:    #f1f4f9;
            --bg-input:      #ffffff;
            --text-dark:     #0f172a;
            --border-color:  #e6ebf3;
            --main-color:    #4f46e5;
            --main-light:    #eef2ff;
            --today-color:   #7c3aed;
            --today-light:   #f5f3ff;
            --modal-body:    #fafbfd;
        }
        [data-theme="dark"] {
            --bg:           #0b1220;
            --surface:      #131c2d;
            --surface-2:    #0f1828;
            --text-main:    #e5eaf2;
            --text-muted:   #94a3b8;
            --text-soft:    #64748b;
            --border:       #233149;
            --border-2:     #2c3a55;
            --hover-bg:     #1a2438;
            --accent-bg:    rgba(99,102,241,0.13);
            --success-bg:   rgba(5,150,105,0.13);
            --danger-bg:    rgba(220,38,38,0.13);
            --warning-bg:   rgba(217,119,6,0.13);
            --info-bg:      rgba(2,132,199,0.13);
            --violet-bg:    rgba(124,58,237,0.13);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.4);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.35);
            --shadow-lg: 0 12px 32px rgba(0,0,0,0.5);

            --bg-page:       #0b1220;
            --bg-card:       #131c2d;
            --bg-table-head: #0f1828;
            --bg-row-alt:    #1a2438;
            --bg-input:      #0f1828;
            --text-dark:     #e5eaf2;
            --border-color:  #233149;
            --main-light:    rgba(99,102,241,0.13);
            --modal-body:    #0f1828;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            font-feature-settings: 'tnum' 1;
            overflow-x: hidden;
            letter-spacing: -0.01em;
            transition: background var(--t), color var(--t);
        }
        .main-content { margin-right: 260px; padding: 28px 32px 40px; min-height: 100vh; max-width: 1700px; }
        @media (max-width: 1200px) { .main-content { padding: 22px 18px 30px; } }
        @media (max-width: 991px) { .main-content { margin-right: 0 !important; width: 100% !important; max-width: 100% !important; padding: 70px 14px 30px !important; } }

        /* ── Page Header (refined, no gradient drama) ── */
        .page-header {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 22px 26px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            position: relative;
            box-shadow: var(--shadow-xs);
            overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 4px; background: var(--accent);
        }
        .page-header h2 {
            color: var(--text-main);
            font-weight: 600; font-size: 1.4rem; margin: 0;
            letter-spacing: -0.02em;
        }
        .page-header h2 i { color: var(--accent); margin-inline-end: 10px; }
        .page-header p {
            color: var(--text-muted);
            font-weight: 400; margin: 4px 0 0; font-size: 0.86rem;
        }

        /* ── Action Buttons in Header ── */
        .btn-header-primary {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: var(--r-sm);
            padding: 10px 18px;
            font-size: 0.88rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: var(--t);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-xs);
        }
        .btn-header-primary:hover { background: var(--accent-2); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .btn-print-soft {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 8px 14px;
            border-radius: var(--r-sm);
            font-size: 0.84rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--t);
        }
        .btn-print-soft:hover { background: var(--accent); color: #fff; border-color: var(--accent); }

        .theme-toggle {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
            width: 40px; height: 40px;
            border-radius: var(--r-sm);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: var(--t);
        }
        .theme-toggle:hover { color: var(--accent); border-color: var(--accent); }

        /* ── Stat Cards (refined: side accent bar, subtle) ── */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 18px 20px;
            box-shadow: var(--shadow-xs);
            position: relative;
            overflow: hidden;
            transition: var(--t);
        }
        .stat-card::before {
            content: ''; position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 3px; background: var(--text-soft);
        }
        .stat-card.blue::before    { background: var(--accent); }
        .stat-card.green::before   { background: var(--success); }
        .stat-card.orange::before  { background: var(--warning); }
        .stat-card.red::before     { background: var(--danger); }
        .stat-card.purple::before  { background: var(--violet); }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--border-2); }
        .stat-card .sc-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .stat-card.blue .sc-icon   { background: var(--accent-bg); color: var(--accent) !important; }
        .stat-card.green .sc-icon  { background: var(--success-bg); color: var(--success) !important; }
        .stat-card.orange .sc-icon { background: var(--warning-bg); color: var(--warning) !important; }
        .stat-card.red .sc-icon    { background: var(--danger-bg); color: var(--danger) !important; }
        .stat-card.purple .sc-icon { background: var(--violet-bg); color: var(--violet) !important; }
        .stat-card .sc-icon i { color: inherit !important; }
        .stat-card p {
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--text-muted);
            margin: 0 0 4px;
            letter-spacing: 0;
            text-transform: none;
        }
        .stat-card h3 {
            font-weight: 600;
            margin: 0;
            font-size: 1.55rem;
            color: var(--text-main) !important;
            letter-spacing: -0.02em;
            line-height: 1.15;
        }
        .stat-card h3 small {
            font-size: 0.78rem !important;
            font-weight: 400;
            color: var(--text-soft);
        }

        /* ── Table Box ── */
        .table-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 20px;
            box-shadow: var(--shadow-xs);
        }

        /* ── Tabs ── */
        .nav-pills {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 4px;
        }
        .nav-pills .nav-link {
            font-weight: 500;
            border-radius: var(--r-sm);
            padding: 8px 16px;
            color: var(--text-muted);
            font-size: 0.86rem;
            transition: var(--t);
            background: transparent;
            border: none;
        }
        .nav-pills .nav-link:hover { background: var(--hover-bg); color: var(--text-main); }
        .nav-pills .nav-link.active {
            background: var(--text-main);
            color: var(--surface);
            box-shadow: var(--shadow-sm);
        }
        .nav-pills .nav-link.today-tab { color: var(--violet); }
        .nav-pills .nav-link.today-tab.active { background: var(--violet); color: #fff; }

        /* ── Professional Filter Card ── */
        .filters-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .filters-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filter-search {
            position: relative;
            flex: 1 1 260px;
            min-width: 220px;
        }
        .filter-search i {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-soft);
            font-size: .85rem;
            pointer-events: none;
        }
        .filter-search input {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--hover-bg);
            font-weight: 600;
            font-size: .88rem;
            color: var(--text-main);
            outline: none;
            transition: .15s;
        }
        .filter-search input:focus {
            border-color: var(--main-color);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        /* Status pills (segmented) */
        .status-pills {
            display: inline-flex;
            background: var(--hover-bg);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 3px;
            gap: 2px;
        }
        .status-pill {
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 700;
            font-size: .82rem;
            padding: 7px 14px;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            transition: .15s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-pill:hover { color: var(--text-main); }
        .status-pill.active { background: var(--surface); box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .status-pill.active[data-status="all"]     { color: #0369a1; }
        .status-pill.active.p-full   { color: #15803d; background: #f0fdf4; }
        .status-pill.active.p-part   { color: #1d4ed8; background: #eff6ff; }
        .status-pill.active.p-none   { color: #dc2626; background: #fef2f2; }
        /* Filter groups */
        .filter-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-group > label {
            font-size: .8rem;
            font-weight: 700;
            color: var(--text-muted);
            margin: 0;
            white-space: nowrap;
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--surface);
            font-weight: 600;
            font-size: .82rem;
            color: var(--text-main);
            outline: none;
            cursor: pointer;
        }
        .filter-select:focus { border-color: var(--main-color); }
        .filter-chip {
            padding: 8px 14px;
            border: 1px solid var(--main-color);
            border-radius: 9px;
            background: transparent;
            color: var(--main-color);
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            white-space: nowrap;
            transition: .15s;
        }
        .filter-chip:hover { background: var(--main-color); color: #fff; }
        .filter-actions { display: inline-flex; gap: 8px; margin-right: auto; }
        .btn-filter-print, .btn-filter-reset {
            padding: 8px 16px;
            border-radius: 9px;
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            white-space: nowrap;
            transition: .15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-filter-print { border: 1px solid #16a34a; background: #16a34a; color: #fff; }
        .btn-filter-print:hover { background: #15803d; }
        .btn-filter-reset { border: 1px solid var(--border); background: var(--surface); color: var(--text-muted); }
        .btn-filter-reset:hover { border-color: var(--danger); color: var(--danger); }
        /* KPI strip (read-only) */
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .kpi-item {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
        }
        .kpi-label { font-size: .72rem; font-weight: 700; color: var(--text-soft); margin-bottom: 3px; }
        .kpi-val { font-size: 1.25rem; font-weight: 900; line-height: 1.1; }
        @media (max-width: 992px) {
            .kpi-strip { grid-template-columns: repeat(4, 1fr); }
            .filter-actions { margin-right: 0; width: 100%; }
        }
        @media (max-width: 576px) {
            .kpi-strip { grid-template-columns: repeat(2, 1fr); }
            .status-pills { width: 100%; justify-content: space-between; }
        }

        /* ── Custom Table ── */
        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--surface);
            border-radius: var(--r-md);
            overflow: hidden;
            border: 1px solid var(--border);
        }
        .custom-table thead { background: var(--surface-2); }
        .custom-table th {
            padding: 12px 14px;
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            text-align: center;
            letter-spacing: 0.02em;
            text-transform: none;
        }
        .custom-table td {
            padding: 14px;
            font-size: 0.88rem;
            font-weight: 500;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            text-align: center;
            color: var(--text-main);
        }
        .custom-table tbody tr { transition: background var(--t); }
        .custom-table tbody tr:hover { background: var(--hover-bg); }
        .custom-table tbody tr:last-child td { border-bottom: none; }
        .clickable-row { cursor: pointer; }
        .custom-table .fw-bold { font-weight: 600 !important; }
        .custom-table .fw-black { font-weight: 700 !important; }

        /* Bootstrap text-* color overrides for clean palette */
        .custom-table .text-primary { color: var(--accent) !important; }
        .custom-table .text-success { color: var(--success) !important; }
        .custom-table .text-danger  { color: var(--danger) !important; }
        .custom-table .text-warning { color: var(--warning) !important; }

        /* ── Today Tab Badge ── */
        .today-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid rgba(220,38,38,0.25);
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.76rem;
            font-weight: 600;
        }
        .today-badge.paid {
            background: var(--success-bg);
            color: var(--success);
            border-color: rgba(5,150,105,0.25);
        }
        .pulse-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--danger);
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }

        /* ── Client Avatar ── */
        .client-avatar {
            width: 36px; height: 36px;
            border-radius: 9px;
            background: var(--accent-bg);
            color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 600;
            font-size: 0.95rem;
            flex-shrink: 0;
            border: 1px solid rgba(79,70,229,0.2);
        }

        /* ── Progress Bar ── */
        .progress-bar-bg {
            width: 100%;
            height: 4px;
            background: var(--border);
            border-radius: 999px;
            margin-top: 6px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: var(--success);
            border-radius: 999px;
            transition: width 0.5s;
        }

        /* ── WhatsApp ── */
        .whatsapp-link { color: #25d366; font-size: 1.1rem; transition: var(--t); }
        .whatsapp-link:hover { color: #128c7e; transform: scale(1.1); }

        /* ── Forms / Inputs ── */
        .form-control, .form-select {
            border-radius: var(--r-sm);
            border: 1px solid var(--border);
            padding: 9px 13px;
            font-weight: 500;
            background: var(--surface);
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.86rem;
            transition: var(--t);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-bg);
            outline: none;
        }

        /* ── Modal ── */
        .modal-content {
            border-radius: var(--r-lg);
            border: 1px solid var(--border);
            background: var(--surface);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        /* ══════════════════════════════════════════════
           PAPER EXCEL SHEET — Refined (slate/indigo neutral)
           ══════════════════════════════════════════════ */
        .paper-xls {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            font-feature-settings: 'tnum';
        }
        .paper-xls td {
            border: 1px solid var(--border);
            padding: 10px 14px;
            font-size: 0.86rem;
            font-weight: 500;
            color: var(--text-main);
        }
        .pxls-label {
            background: var(--surface-2);
            color: var(--text-muted) !important;
            text-align: right;
            width: 52%;
            font-weight: 600 !important;
        }
        .pxls-value {
            background: var(--surface);
            text-align: center;
            font-weight: 600;
            font-size: 0.92rem;
            color: var(--text-main) !important;
        }
        .pxls-title-row .pxls-label {
            background: var(--accent);
            color: #fff !important;
            font-size: 0.92rem;
            border-color: var(--accent);
        }
        .pxls-title-row .pxls-value.name-val {
            background: var(--accent-bg);
            color: var(--accent) !important;
            font-size: 1rem;
            font-weight: 700;
        }

        /* قسم بيانات العقد العلوي (من نوع المنتج لغاية رقم الموبايل) — لون مميز يفصله عن باقي الداتا */
        /* تظليل أزرق شفاف يشتغل على الوضع الفاتح والداكن من غير ما يأثر على وضوح النص */
        .paper-xls tr:not([class]) td {
            background: rgba(59, 130, 246, 0.13) !important;
        }

        /* Payment rows */
        .pxls-pay-row td { background: var(--surface); }
        .pxls-pay-row:nth-child(even) td { background: var(--surface-2); }
        .pxls-pay-date {
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted) !important;
            font-weight: 500 !important;
            width: 52%;
        }
        .pxls-pay-num {
            text-align: center;
            position: relative;
            width: 48%;
        }
        .pxls-pay-amount {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--success) !important;
        }
        .pxls-row-badge {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--accent);
            color: #fff !important;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pxls-empty-row td {
            background: var(--surface-2) !important;
            color: var(--text-soft) !important;
        }
        .pxls-empty-row .pxls-row-badge {
            background: var(--border-2);
            color: var(--text-soft) !important;
        }
        .pxls-summary-row td { border-top: 2px solid var(--border-2); }
        .pxls-sum-label {
            background: var(--text-main);
            color: var(--surface) !important;
            text-align: right;
            padding: 12px 14px;
            font-weight: 600;
            font-size: 0.88rem;
        }
        .pxls-sum-value {
            text-align: center;
            font-size: 1rem;
            font-weight: 700;
            padding: 12px 14px;
        }
        .profit-label    { background: var(--success-bg) !important; color: var(--success) !important; }
        .profit-val      { background: var(--success-bg) !important; color: var(--success) !important; }
        .remaining-label { background: var(--danger-bg)  !important; color: var(--danger)  !important; }
        .remaining-val   { background: var(--danger-bg)  !important; color: var(--danger)  !important; }
        .paid-summary .pxls-sum-label { background: var(--accent) !important; color: #fff !important; }
        .paid-val { background: var(--accent-bg) !important; color: var(--accent) !important; }

        /* ── Customer Statement Modal ── */
        .cst-tab {
            transition: var(--t);
            border-left: 1px solid var(--border) !important;
        }
        .cst-tab:hover {
            background: var(--accent-bg) !important;
            color: var(--accent) !important;
        }
        .cst-pane { display: none; }

        /* شريط تبويبات العقود: يلتف على أكتر من سطر (كل العقود ظاهرة) + سكرول بار أكبر وأوضح */
        .cst-tabs-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 10px 12px;
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .cst-tab {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            border: 1.5px solid var(--border-2);
            background: var(--surface);
            color: var(--text-muted);
            transition: all 0.15s;
            white-space: nowrap;
            user-select: none;
        }
        .cst-tab:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-bg); }
        .cst-tab.active-tab { background: var(--accent); color: #fff; border-color: var(--accent); }
        .cst-tab-summary { background: var(--surface); border-color: var(--accent); color: var(--accent); font-size: 11px; }
        .cst-tab-summary.active-tab { background: var(--accent); color: #fff; }
        .cst-num { background: rgba(0,0,0,0.12); border-radius: 10px; padding: 0px 5px; font-size: 10px; font-weight: 800; }
        .cst-tab.active-tab .cst-num { background: rgba(255,255,255,0.25); }

        /* ═══ NEW CONTRACT MODAL - Wizard Style ═══ */
        .nc-step {
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 16px;
            transition: border-color .2s;
        }
        .nc-step:hover { border-color: #93c5fd; }
        .nc-step-header {
            display: flex; align-items: center; gap: 12px;
            font-size: 1.05rem; font-weight: 900; color: var(--text-dark);
            margin-bottom: 18px; padding-bottom: 14px;
            border-bottom: 2px solid var(--border-color);
        }
        .nc-step-num {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, var(--main-color), #60a5fa);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 1rem; flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(26,86,219,.3);
        }
        .nc-label { font-weight: 800; font-size: .82rem; color: var(--text-muted); margin-bottom: 6px; display: block; letter-spacing: .3px; }
        .req { color: #ef4444; }
        .nc-input { font-weight: 700 !important; border-radius: 10px !important; }
        .nc-readonly-val {
            background: var(--bg-row-alt); border: 1.5px solid var(--border-color);
            border-radius: 10px; padding: 11px 16px; font-size: 1.3rem;
            font-weight: 900; text-align: center;
        }
        .nc-result-box {
            border: 1.5px solid; border-radius: 14px; padding: 16px 20px;
            text-align: center;
        }
        .nc-result-label { font-size: .8rem; font-weight: 800; color: var(--text-muted); margin-bottom: 4px; }
        .nc-result-val { font-size: 2rem; font-weight: 900; line-height: 1.1; }

        /* Sale Type Toggle */
        .nc-toggle-row { display: flex; gap: 12px; }
        .nc-toggle-opt { flex: 1; cursor: pointer; }
        .nc-toggle-opt input { display: none; }
        .nc-opt-box {
            border: 2px solid var(--border-color); border-radius: 14px;
            padding: 16px 12px; text-align: center; font-size: .88rem;
            font-weight: 800; color: var(--text-muted);
            transition: .2s; background: var(--bg-page);
        }
        .nc-opt-box:hover { border-color: #93c5fd; }
        .nc-toggle-opt input:checked + .nc-opt-box {
            border-color: var(--main-color); background: var(--main-light);
            color: var(--main-color); box-shadow: 0 4px 12px rgba(26,86,219,.15);
        }

        /* Pay Radio Group */
        .pay-radio-group {
            display: flex; gap: 6px; background: var(--bg-row-alt);
            border: 1.5px solid var(--border-color); border-radius: 12px;
            padding: 6px; overflow: hidden;
        }
        .pay-radio-group label {
            flex: 1; text-align: center; padding: 9px 6px; border-radius: 8px;
            font-size: .8rem; font-weight: 800; cursor: pointer; transition: .15s; color: var(--text-muted);
        }
        .pay-radio-group input[type="radio"] { display: none; }
        .pay-radio-group input[type="radio"]:checked + label,
        .pay-radio-group label:has(input:checked) { background: var(--bg-card); box-shadow: var(--shadow-sm); }

        /* Swal */
        .swal-rtl-popup { direction:rtl; font-family:'Cairo',sans-serif !important; }
        .swal-rtl-popup .swal2-title { font-family:'Cairo',sans-serif !important; font-size:18px !important; }
        .swal-rtl-popup .swal2-input { direction:ltr; }

        /* ═══ Today's Installments Summary Bar ═══ */
        .today-summary-bar {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 0;
            margin-bottom: 20px;
            overflow: hidden;
        }
        @media (max-width: 768px) { .today-summary-bar { grid-template-columns: repeat(2, 1fr); } }
        .tsb-item {
            background: var(--surface);
            padding: 14px 16px;
        }
        .tsb-label {
            font-size: 0.74rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .tsb-label i { color: var(--violet); }
        .tsb-val {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }
        .tsb-val.success { color: var(--success); }
        .tsb-val.danger  { color: var(--danger); }

        /* Writeoff badge */
        .writeoff-badge {
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid rgba(220,38,38,0.25);
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.74rem;
            font-weight: 600;
        }

        /* Bootstrap badge overrides (cleaner) */
        .badge.bg-primary   { background: var(--accent) !important; }
        .badge.bg-secondary { background: var(--text-soft) !important; }
        .badge.bg-success   { background: var(--success) !important; }
        .badge.bg-warning   { background: var(--warning) !important; }
        .badge.bg-danger    { background: var(--danger) !important; }
        .badge.bg-info      { background: var(--info) !important; }

        /* Bootstrap outline button overrides */
        .btn-outline-primary {
            color: var(--accent) !important;
            border-color: var(--border-2) !important;
            border-radius: var(--r-sm) !important;
            font-weight: 500 !important;
            font-size: 0.82rem !important;
        }
        .btn-outline-primary:hover {
            background: var(--accent) !important;
            color: #fff !important;
            border-color: var(--accent) !important;
        }
        .btn-outline-success {
            color: var(--success) !important;
            border-color: var(--border-2) !important;
            border-radius: var(--r-sm) !important;
            font-weight: 500 !important;
            font-size: 0.82rem !important;
        }
        .btn-outline-success:hover {
            background: var(--success) !important;
            color: #fff !important;
            border-color: var(--success) !important;
        }
        .btn-outline-danger { border-radius: var(--r-sm) !important; }
        .btn-dark {
            background: var(--text-main) !important;
            border: none !important;
            border-radius: var(--r-sm) !important;
            font-weight: 500 !important;
            font-size: 0.84rem !important;
            padding: 9px 18px !important;
        }
        .btn-dark:hover { background: var(--accent) !important; }

        @media (max-width: 768px) {
            .main-content { margin-right: 0; padding: 16px; }
            .page-header { padding: 14px 16px; }
            .nc-toggle-row { flex-direction: column; }
            .page-header h1 { font-size: 1.2rem !important; }

            /* شريط إحصائيات اليوم */
            .today-summary-bar { grid-template-columns: repeat(2, 1fr); }

            /* تابات الرينج في المستحق يوم كذا */
            #dueStatsBar { flex-direction: column; }
            #dueStatsBar > div { min-width: 45%; }

            /* inline grids داخل modal كشف الحساب */
            div[style*="grid-template-columns:repeat(4"] { grid-template-columns: repeat(2, 1fr) !important; }
            .summary.cols-4, .summary.cols-5 { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 480px) {
            .today-summary-bar { grid-template-columns: 1fr 1fr; }
            .summary.cols-4, .summary.cols-5 { grid-template-columns: repeat(2, 1fr) !important; }
        }
    </style>
</head>
<body>

{{-- زر الدارك مود موجود في الهيدر --}}

@include('sidebar')

@php
    // fmtMoney معرّفة عامةً في app/helpers.php — ده fallback لو مش متاحة
    if (!function_exists('fmtMoney')) {
        function fmtMoney($val) {
            $v = round((float) $val, 2);
            return fmod($v, 1.0) === 0.0
                ? number_format($v, 0, '.', ',')
                : number_format($v, 2, '.', ',');
        }
    }
    $activeInstallments = collect($installments)->filter(fn($i) => (float) $i->remaining_balance > 0);
    $completedInstallments = collect($installments)->filter(fn($i) => \App\Services\InstallmentFinanceService::isPaidInFull($i));
    $writtenOffInstallments = collect($installments)->filter(fn($i) => \App\Services\InstallmentFinanceService::isWrittenOff($i));

    $total_debts_out = $activeInstallments->sum('remaining_balance');
    $total_collected = $total_collected ?? \App\Services\InstallmentFinanceService::totalCollectedAmount(collect($installments)->pluck('id'));
@endphp

<div class="main-content">
    
    @if(session('success')) <div class="alert alert-success fw-bold rounded-3 shadow-sm"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    
    <div class="page-header">
        <div>
            <h2><i class="fa fa-file-signature"></i>منظومة العقود والأقساط</h2>
            <p>إدارة مبيعات التقسيط — تحصيل الدفعات — إعدام الديون — تسوية العقود</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button id="theme-toggle" class="theme-toggle" title="تبديل الوضع الليلي"><i class="fa fa-moon"></i></button>
            <button class="btn-print-soft" onclick="printActiveInstallments()" title="طباعة العقود النشطة"><i class="fa fa-print"></i> طباعة</button>
            <button class="btn-header-primary" data-bs-toggle="modal" data-bs-target="#newContractModal">
                <i class="fa fa-plus"></i> عقد جديد
            </button>
        </div>
    </div>

    @php
        $todayDay = (int) date('d');
        $todayInstallments = collect($installments)->where('remaining_balance', '>', 0)->where('due_day', $todayDay);
        $todayTotal = $todayInstallments->sum('monthly_installment');
        $todayPaidCount = $todayInstallments->filter(fn($i) => 
            collect($i->payments)->where('payment_date', '>=', date('Y-m-d 00:00:00'))->where('amount_paid', '>', 0)->count() > 0
        )->count();
        $todayUnpaidCount = $todayInstallments->count() - $todayPaidCount;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md col-6">
            <div class="stat-card blue">
                <div class="sc-icon"><i class="fa fa-money-bill-wave"></i></div>
                <p>إجمالي المتبقي بالخارج</p>
                <h3>{{ fmtMoney($total_debts_out) }} <small>ج</small></h3>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="stat-card green">
                <div class="sc-icon"><i class="fa fa-circle-check"></i></div>
                <p>المبالغ المحصلة كلياً</p>
                <h3>{{ fmtMoney($total_collected) }} <small>ج</small></h3>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="stat-card orange">
                <div class="sc-icon"><i class="fa fa-file-contract"></i></div>
                <p>العقود النشطة</p>
                <h3>{{ $activeInstallments->count() }} <small>عقد</small></h3>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="stat-card red">
                <div class="sc-icon"><i class="fa fa-flag-checkered"></i></div>
                <p>العقود المنتهية</p>
                <h3>{{ $completedInstallments->count() }} <small>عقد</small></h3>
            </div>
        </div>
        <div class="col-md col-6">
            <div class="stat-card purple">
                <div class="sc-icon"><i class="fa fa-calendar-day"></i></div>
                <p>أقساط اليوم ({{ date('d') }})</p>
                <h3>{{ $todayInstallments->count() }} <small>قسط</small></h3>
            </div>
        </div>
    </div>

    <div class="table-box">
        <ul class="nav nav-pills mb-3" id="tabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#activeTab"><i class="fa fa-list-check me-1"></i> نشطة ({{ $activeInstallments->count() }})</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#completedTab"><i class="fa fa-check-double me-1"></i> منتهية ({{ $completedInstallments->count() }})</button></li>
        </ul>

        <div class="tab-content">
            {{-- تابة النشط --}}
            <div class="tab-pane fade show active" id="activeTab">

                {{-- ═══════════ بطاقة الفلاتر الموحّدة ═══════════ --}}
                <div class="filters-card mb-3">
                    {{-- الصف الأول: بحث + حالة السداد --}}
                    <div class="filters-row">
                        <div class="filter-search">
                            <i class="fa fa-search"></i>
                            <input type="text" id="activeSearch" placeholder="ابحث باسم العميل أو رقم الهاتف..." oninput="applyActiveFilters()" autocomplete="off">
                        </div>
                        <div class="status-pills" id="statusPills">
                            <button type="button" class="status-pill active" data-status="all"     onclick="setStatusFilter('all', this)"><i class="fa fa-layer-group"></i> الكل</button>
                            <button type="button" class="status-pill p-full"   data-status="full"    onclick="setStatusFilter('full', this)"><i class="fa fa-check-circle"></i> دفع كامل</button>
                            <button type="button" class="status-pill p-part"   data-status="partial" onclick="setStatusFilter('partial', this)"><i class="fa fa-adjust"></i> جزئي</button>
                            <button type="button" class="status-pill p-none"   data-status="unpaid"  onclick="setStatusFilter('unpaid', this)"><i class="fa fa-circle-xmark"></i> لم يسدد</button>
                        </div>
                    </div>

                    {{-- الصف الثاني: يوم الاستحقاق + تاريخ التعاقد + أزرار --}}
                    <div class="filters-row">
                        <div class="filter-group">
                            <label><i class="fa fa-calendar-day"></i> يوم الاستحقاق</label>
                            <select id="dueRangeFrom" class="filter-select" onchange="applyActiveFilters()">
                                <option value="0">من: الكل</option>
                                @for($dy=1; $dy<=30; $dy++) <option value="{{ $dy }}">من يوم {{ $dy }}</option> @endfor
                            </select>
                            <select id="dueRangeTo" class="filter-select" onchange="applyActiveFilters()">
                                <option value="0">إلى: الكل</option>
                                @for($dy=1; $dy<=30; $dy++) <option value="{{ $dy }}">إلى يوم {{ $dy }}</option> @endfor
                            </select>
                            <button type="button" class="filter-chip" onclick="setTodayDueFilter()" title="عرض المستحق اليوم"><i class="fa fa-bolt"></i> اليوم</button>
                        </div>

                        <form method="GET" action="{{ route('installments.index') }}" class="filter-group m-0" id="installmentsFilterForm" onsubmit="return validateCollectionFilter(event)">
                            <label><i class="fa fa-file-signature"></i> تاريخ التعاقد</label>
                            <select name="time_filter" class="filter-select" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                <option value="today"     {{ request('time_filter') == 'today'     ? 'selected' : '' }}>اليوم</option>
                                <option value="yesterday" {{ request('time_filter') == 'yesterday' ? 'selected' : '' }}>الأمس</option>
                                <option value="month"     {{ request('time_filter') == 'month'     ? 'selected' : '' }}>هذا الشهر</option>
                            </select>
                        </form>

                        <div class="filter-actions">
                            <button type="button" class="btn-filter-print" onclick="printActiveInstallments()" title="طباعة حسب الفلتر الحالي"><i class="fa fa-print"></i> طباعة</button>
                            <button type="button" class="btn-filter-reset" onclick="resetActiveFilters()" title="مسح كل الفلاتر"><i class="fa fa-rotate-left"></i> مسح</button>
                        </div>
                    </div>
                </div>

                {{-- ═══════════ شريط الإحصائيات (للعرض فقط) ═══════════ --}}
                <div id="dueStatsBar" class="kpi-strip mb-3">
                    <div class="kpi-item"><div class="kpi-label">العملاء</div><div class="kpi-val" style="color:#0369a1;" id="statTotal">0</div></div>
                    <div class="kpi-item"><div class="kpi-label">إجمالي المطلوب</div><div class="kpi-val" style="color:#b45309;" id="statDue">0 ج</div></div>
                    <div class="kpi-item"><div class="kpi-label">دفع كامل</div><div class="kpi-val" style="color:#15803d;" id="statFullPaid">0</div></div>
                    <div class="kpi-item"><div class="kpi-label">جزئي</div><div class="kpi-val" style="color:#1d4ed8;" id="statPartialPaid">0</div></div>
                    <div class="kpi-item"><div class="kpi-label">لم يسدد</div><div class="kpi-val" style="color:#dc2626;" id="statUnpaid">0</div></div>
                    <div class="kpi-item"><div class="kpi-label">تم تحصيل</div><div class="kpi-val" style="color:#16a34a;" id="statCollected">0 ج</div></div>
                    <div class="kpi-item"><div class="kpi-label">باقي لم يُحصَّل</div><div class="kpi-val" style="color:#dc2626;" id="statRemaining">0 ج</div></div>
                </div>

                <div class="table-responsive">
                    <table class="custom-table" id="dueByDayTable">
                        <thead>
                            <tr>
                                <th class="text-start">العميل</th>
                                <th>عدد العقود</th>
                                <th>إجمالي القسط الشهري</th>
                                <th>إجمالي المتبقي</th>
                                <th>حالة السداد</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody id="dueByDayBody"></tbody>
                    </table>
                </div>
                {{-- شريط التنقل بين الصفحات (15 صف للصفحة) --}}
                <div id="duePager" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3" style="display:none;">
                    <small class="text-muted fw-bold" id="duePagerInfo"></small>
                    <div class="d-flex gap-1" id="duePagerBtns"></div>
                </div>

                {{-- بيانات الأقساط كـ JSON للـ JavaScript --}}
                <script id="allInstallmentsData" type="application/json">
                {!! json_encode(collect($installments)->where('remaining_balance', '>', 0)->map(function($i) {
                    $payments = collect($i->payments);
                    $currentMonth = date('Y-m');
                    $paidThisMonth = $payments->filter(function($p) use ($currentMonth) {
                        return str_starts_with($p->payment_date, $currentMonth) && $p->amount_paid > 0;
                    });
                    $totalPaidThisMonth = $paidThisMonth->sum('amount_paid');
                    $paymentCount = $payments->where('amount_paid', '>', 0)->count();
                    $latestPayment = $payments->sortByDesc('payment_date')->first();

                    return [
                        'id' => $i->id,
                        'customer_name' => $i->customer_name,
                        'customer_phone' => $i->customer_phone ?? '',
                        'product_name' => $i->product_name,
                        'due_day' => (int)$i->due_day,
                        'monthly_installment' => (float)$i->monthly_installment,
                        'remaining_balance' => (float)$i->remaining_balance,
                        'paid_this_month' => $totalPaidThisMonth >= ((float)$i->monthly_installment * 0.99),
                        'paid_this_month_amount' => $totalPaidThisMonth,
                        'payment_count' => $paymentCount,
                        'notes' => $i->notes ?? '',
                        'latest_payment_notes' => $latestPayment ? ($latestPayment->notes ?? '') : '',
                        // 💡 تفاصيل كل دفعة (تاريخ/مبلغ/ملاحظة) — تُستخدم لحساب حالة السداد لأي فترة يختارها المستخدم
                        // (مش بس الشهر الحالي) من غير الحاجة لإعادة تحميل الصفحة
                        'payments_list' => $payments->where('amount_paid', '>', 0)->map(fn($p) => [
                            'date'   => $p->payment_date,
                            'amount' => (float) $p->amount_paid,
                            'notes'  => $p->notes ?? '',
                        ])->values(),
                    ];
                })->values()) !!}
                </script>
            </div>

            {{-- تابة المكتمل --}}
            <div class="tab-pane fade" id="completedTab">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <span style="font-size:0.85rem; color:var(--text-muted);">{{ $completedInstallments->count() }} عقد منتهي</span>
                    <button class="btn-print-soft" onclick="printCompletedInstallments()"><i class="fa fa-print"></i> طباعة العقود المنتهية</button>
                </div>
                <div class="table-responsive">
                    <table class="custom-table opacity-75">
                        <thead>
                            <tr>
                                <th class="text-start">العميل</th>
                                <th>المنتج</th>
                                <th>إجمالي العقد</th>
                                <th>المقدم</th>
                                <th>الربح المحقق</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الحالة</th>
                                <th>التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedInstallments as $cInst)
                            @php
                                $cStmtArg = addslashes($cInst->customer_phone ?? '') . '|' . addslashes($cInst->customer_name ?? '');
                            @endphp
                            <tr class="clickable-row" onclick="openCustomerModal('{{ $cStmtArg }}')">
                                <td class="text-start fw-bold" style="color: var(--text-dark);">
                                    <strong class="d-block">{{ $cInst->customer_name }}</strong>
                                    <small class="text-muted" dir="ltr">{{ $cInst->customer_phone ?? '—' }}</small>
                                </td>
                                <td><span class="badge" style="background:#eff6ff;color:#1a56db;font-size:.8rem;font-weight:800;padding:5px 10px;border-radius:8px;">{{ Str::limit($cInst->product_name, 22) }}</span></td>
                                <td class="fw-bold" style="color: var(--text-dark);">{{ fmtMoney($cInst->total_after_interest) }} ج</td>
                                <td class="text-success fw-bold">{{ fmtMoney($cInst->down_payment) }} ج</td>
                                <td class="text-success fw-bold">+{{ fmtMoney($cInst->profit) }} ج</td>
                                <td class="text-muted fw-bold" style="font-size:.85rem;">{{ \Carbon\Carbon::parse($cInst->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    @if(\App\Services\InstallmentFinanceService::isWrittenOff($cInst))
                                        <span class="badge bg-secondary fs-6">مُعدَم</span>
                                    @elseif(($cInst->installment_months ?? 0) == 0)
                                        <span class="badge bg-info fs-6">سداد فوري</span>
                                    @else
                                        <span class="badge bg-success fs-6">مسدد بالكامل ✓</span>
                                    @endif
                                </td>
                                <td><button class="btn btn-sm btn-outline-success fw-bold px-3" onclick="event.stopPropagation(); openCustomerModal('{{ $cStmtArg }}')"><i class="fa fa-table me-1"></i> كشف</button></td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted fw-bold">لا توجد عقود مكتملة حالياً.</td></tr>
                            @endforelse
                        </tbody>
                        @if($completedInstallments->count() > 0)
                        <tfoot>
                            <tr style="background: rgba(16, 185, 129, 0.1); border-top:2px solid #6ee7b7;">
                                <td colspan="2" class="text-start fw-bold text-success fs-6" style="padding:15px;">
                                    <i class="fa fa-sigma me-2"></i> المجموع (العقود المنتهية)
                                </td>
                                <td class="fw-black text-center" style="color:#047857; font-size:16px;">
                                    {{ fmtMoney($completedInstallments->sum('total_after_interest')) }} ج
                                </td>
                                <td class="fw-black text-center" style="color:#047857; font-size:16px;">
                                    {{ fmtMoney($completedInstallments->sum('down_payment')) }} ج
                                </td>
                                <td class="fw-black text-center" style="color:#047857; font-size:16px;">
                                    +{{ fmtMoney($completedInstallments->sum('profit')) }} ج
                                </td>
                                <td colspan="3" class="fw-black text-center" style="color:#047857; font-size:16px;">
                                    {{ $completedInstallments->count() }} عقد مكتمل
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     CUSTOMER GROUP MODALS - كشف حساب شامل لكل عميل (Excel Style)
══════════════════════════════════════════════════════════════ --}}
{{-- 🚀 كشف الحساب يُحمَّل عند الطلب (lazy) لتخفيف وزن الصفحة --}}
<div id="statementModalHost"></div>

{{-- ══════════════════════════════════════════════════════════════
     MODALS ACTIONS (Pay, Edit, Writeoff, Delete)
══════════════════════════════════════════════════════════════ --}}
{{-- 🚀 مودالات الإجراءات (سداد/تعديل/فسخ/إعدام) تُحمَّل عند الطلب (lazy) --}}
<div id="actionModalHost"></div>

{{-- ══════════════════════════════════════════════════════════════
     💡 MODAL: إنشاء عقد جديد (ديزاين احترافي Wizard UI) 💡
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="newContractModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('installments.store') }}" class="modal-content border-0 shadow-lg" novalidate onsubmit="return validateContractForm(event, this)">
            @csrf
            
            <input type="hidden" name="discount_amount" id="h_discount_amount">
            <input type="hidden" name="total_after_interest" id="h_total">
            <input type="hidden" name="monthly_installment" id="h_monthly">
            <input type="hidden" name="cash_price" id="h_cash_price"> 
            
            <div class="modal-header border-0 p-0">
                <div class="w-100 px-4 py-3 d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#0f172a 0%,#1a56db 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 shadow" style="width:46px;height:46px;background:rgba(255,255,255,.15);">
                            <i class="fa fa-file-signature text-white fs-5"></i>
                        </div>
                        <div>
                            <h5 class="text-white fw-bold m-0" style="font-size:1.05rem;">إنشاء عقد تقسيط جديد</h5>
                            <span class="small fw-bold" style="color:rgba(255,255,255,.6);">أكمل الخطوات الثلاثة بالترتيب</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body p-4" style="background-color: var(--modal-body);">
                
                {{-- ══ STEP 1: بيانات المنتج ══ --}}
                <div class="nc-step">
                    <div class="nc-step-header">
                        <div class="nc-step-num">١</div>
                        <span>تفاصيل عملية البيع والمنتج</span>
                    </div>

                    <input type="hidden" name="sale_type" value="inventory">

                    {{-- قالب مخفي لقائمة أصناف المخزن — يُستنسخ JS منه لكل صف جديد --}}
                    <select id="ci_options_template" style="display:none;">
                        @foreach($inventoryItems as $inv)
                            <option value="{{ $inv->id }}" data-name="{{ addslashes($inv->product_name) }}" data-price="{{ $inv->selling_price }}" data-qty="{{ $inv->remaining_quantity }}" data-category="{{ addslashes($inv->category) }}">
                                {{ Str::limit($inv->product_name, 35) }} (متاح: {{ fmtMoney($inv->remaining_quantity) }} | {{ fmtMoney($inv->selling_price) }} ج)
                            </option>
                        @endforeach
                    </select>

                    <div id="div_inv">
                        <label class="nc-label">الأصناف المباعة للعميل <span class="req">*</span></label>
                        <div class="table-responsive mb-2">
                            <table class="table align-middle mb-0" id="contractItemsTable">
                                <thead>
                                    <tr style="font-size:.78rem;color:var(--text-muted);">
                                        <th style="min-width:220px;">الصنف</th>
                                        <th style="width:100px;">الكمية</th>
                                        <th style="width:130px;">سعر البيع</th>
                                        <th style="width:110px;">الإجمالي</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="contractItemsBody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold mb-3" onclick="addContractItemRow()">
                            <i class="fa fa-plus me-1"></i> إضافة صنف آخر
                        </button>

                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-4" style="background:#f0fdf4;border:1.5px solid #86efac;">
                            <span class="fw-bold text-success"><i class="fa fa-calculator me-1"></i> إجمالي سعر الأصناف (كاش)</span>
                            <span class="fw-black fs-4 text-success" id="contractItemsSubtotal">0 ج</span>
                        </div>
                        {{-- قيمة وسيطة داخلية: إجمالي الأصناف + بنود التكييف — calcMain() بيقرأها ويحطها في h_cash_price الفعلي --}}
                        <input type="hidden" id="inv_price_disp" value="0">

                        {{-- ══ بنود التكييف (تظهر تلقائياً لو اتختار تكييف) ══ --}}
                        <div id="ac_extras_box" style="display:none; margin-top:18px;" class="p-3 rounded-3 border-2" style="border-color:#f59e0b !important; background:linear-gradient(135deg,#fffbeb,#fef3c7);">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span style="background:#f59e0b;color:#fff;border-radius:8px;padding:5px 12px;font-size:13px;font-weight:900;"><i class="fa fa-snowflake me-1"></i> بنود التكييف الإضافية</span>
                                <small class="text-muted fw-bold">تُضاف على فاتورة العميل (سعر الجهاز + بنود التركيب) ولا تُحتسب ضمن الربح</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="nc-label" style="color:#b45309;"><i class="fa fa-truck me-1"></i> نقل</label>
                                    <input type="number" step="0.01" min="0" name="transport_cost" id="ac_transport"
                                           class="form-control nc-input text-center border-warning text-warning fw-bold"
                                           placeholder="0" value="0" oninput="calcAcExtras()">
                                </div>
                                <div class="col-md-4">
                                    <label class="nc-label" style="color:#b45309;"><i class="fa fa-tools me-1"></i> تركيب</label>
                                    <input type="number" step="0.01" min="0" name="installation_cost" id="ac_installation"
                                           class="form-control nc-input text-center border-warning text-warning fw-bold"
                                           placeholder="0" value="0" oninput="calcAcExtras()">
                                </div>
                                <div class="col-md-4">
                                    <label class="nc-label" style="color:#b45309;"><i class="fa fa-boxes me-1"></i> خامات</label>
                                    <input type="number" step="0.01" min="0" name="materials_cost" id="ac_materials"
                                           class="form-control nc-input text-center border-warning text-warning fw-bold"
                                           placeholder="0" value="0" oninput="calcAcExtras()">
                                </div>
                            </div>
                            <div class="mt-3 p-2 rounded-2 d-flex align-items-center justify-content-between" style="background:#fffde7;border:1.5px dashed #f59e0b;">
                                <span class="fw-bold" style="color:#b45309;font-size:.85rem;"><i class="fa fa-calculator me-1"></i> إجمالي البنود الإضافية:</span>
                                <span class="fw-black" style="color:#d97706;font-size:1.3rem;" id="ac_extras_total">0 ج.م</span>
                            </div>
                            
                            {{-- 💡 الخزنة الخاصة بدفع مصاريف التكييف (تظهر فقط إذا كان الإجمالي > 0) 💡 --}}
                            <div id="ac_expense_acc_div" style="display:none; margin-top:15px; padding-top:15px; border-top:1px dashed #d97706;">
                                <label class="nc-label text-danger mb-2"><i class="fa fa-wallet me-1"></i> سحب مصاريف (النقل، التركيب، الخامات) من خزنة <span class="req">*</span></label>
                                <select name="ac_expense_account" id="ac_expense_acc" class="form-select nc-input border-danger text-danger fw-bold" onchange="showVaultBalance('ac_expense_acc', 'ac_expense_bal')">
                                    <option value="" disabled selected>اختر الخزنة لخصم المصاريف...</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}" data-balance="{{ $acc->balance }}">{{ $acc->account_name }} — {{ fmtMoney($acc->balance) }} ج</option>
                                    @endforeach
                                </select>
                                <div id="ac_expense_bal" class="vault-balance-display mt-2"></div>
                                <small class="text-muted fw-bold d-block mt-2"><i class="fa fa-info-circle me-1"></i> سيتم سحب إجمالي المصاريف المذكورة أعلاه من هذه الخزنة كـ (مصروف)، وإضافتها تلقائياً على إجمالي الفاتورة ليتحملها العميل.</small>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ══ STEP 2: العميل ══ --}}
                <div class="nc-step">
                    <div class="nc-step-header">
                        <div class="nc-step-num">٢</div>
                        <span>بيانات العميل المستفيد</span>
                        <span class="ms-auto badge fw-bold" style="background:#eff6ff;color:#1a56db;font-size:.75rem;border-radius:8px;padding:4px 10px;">بيانات التواصل</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="nc-label"><i class="fa fa-phone me-1"></i> رقم الموبايل (واتساب) <span class="req">*</span></label>
                            <input type="text" name="customer_phone" class="form-control nc-input text-center border-primary" required autocomplete="on" oninput="checkCustomer(this.value)" placeholder="01xxxxxxxxx" value="{{ old('customer_phone') }}" style="font-size:1.1rem;letter-spacing:.5px;">
                        </div>
                        <div class="col-md-7">
                            <label class="nc-label"><i class="fa fa-user me-1"></i> اسم العميل (ثلاثي) <span class="req">*</span></label>
                            <input type="text" name="customer_name" id="cust_name_input" class="form-control nc-input border-primary" required autocomplete="on" placeholder="اكتب اسم العميل كاملاً..." value="{{ old('customer_name') }}">
                        </div>
                    </div>
                    <div class="mt-2 py-2 px-3 rounded-2" style="background:#f0fdf4;border:1px dashed #86efac;font-size:.8rem;color:#15803d;font-weight:700;display:none;" id="cust_found_note">
                        <i class="fa fa-circle-check me-1"></i> تم التعرف على العميل من السجلات السابقة ✓
                    </div>
                    {{-- ملاحظات العميل (اختياري) --}}
                    <div class="mt-3">
                        <label class="nc-label" style="color:#7c3aed;">
                            <i class="fa fa-sticky-note me-1"></i> تعليق / ملاحظة على العميل
                            <span class="badge ms-1 fw-bold" style="background:#f5f3ff;color:#7c3aed;font-size:.68rem;border-radius:6px;padding:2px 7px;">اختياري</span>
                        </label>
                        <textarea name="notes" id="customer_notes_input" rows="2"
                                  class="form-control nc-input"
                                  style="border-color:#c4b5fd;resize:none;font-size:.9rem;"
                                  placeholder="مثال: يسكن في المعادي، بيدفع كل أول الشهر، عنده 2 عقود سابقة...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- ══ STEP 3: الحسابات ══ --}}
                <div class="nc-step mb-0 border-0 shadow-sm">
                    <div class="nc-step-header">
                        <div class="nc-step-num">٣</div>
                        <span>التسويات والتفاصيل المالية</span>
                    </div>
                    
                    {{-- حساب مرئي سريع --}}
                    <div class="p-3 rounded-2 mb-4 d-flex gap-3 align-items-center flex-wrap" style="background:#f8fafc;border:1.5px solid var(--border-color);">
                        <div class="text-center flex-fill">
                            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;">سعر الكاش</div>
                            <div class="fw-black" style="color:#0f172a;font-size:1rem;" id="calc_cash_show">0 ج</div>
                        </div>
                        <i class="fa fa-minus" style="color:#94a3b8;"></i>
                        <div class="text-center flex-fill">
                            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;">مقدم + خصم</div>
                            <div class="fw-black" style="color:#059669;font-size:1rem;" id="calc_deduct_show">0 ج</div>
                        </div>
                        <i class="fa fa-plus" style="color:#94a3b8;"></i>
                        <div class="text-center flex-fill">
                            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;">فائدة</div>
                            <div class="fw-black" style="color:#d97706;font-size:1rem;" id="calc_int_show">0 ج</div>
                        </div>
                        <i class="fa fa-equals" style="color:#94a3b8;"></i>
                        <div class="text-center flex-fill" style="background:#fef2f2;border-radius:8px;padding:6px 10px;">
                            <div style="font-size:.72rem;color:#dc2626;font-weight:700;">إجمالي المطلوب</div>
                            <div class="fw-black text-danger" style="font-size:1.1rem;" id="calc_total_show">0 ج</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="nc-label text-success">المقدم المدفوع الآن</label>
                            <input type="number" step="0.01" min="0" name="down_payment" id="inp_down" class="form-control nc-input text-center fs-5 border-success text-success" value="{{ old('down_payment', 0) }}" oninput="calcMain()" autocomplete="on">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="nc-label" style="color:#0ea5e9;">مبلغ الخصم (إن وجد)</label>
                            <input type="number" step="0.01" min="0" name="discount" id="inp_disc" class="form-control nc-input text-center fs-5" style="border-color:#0ea5e9; color:#0ea5e9;" value="{{ old('discount', 0) }}" oninput="calcMain()" autocomplete="on">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="nc-label text-secondary">نسبة الفائدة %</label>
                            <input type="number" step="0.1" min="0" name="interest_rate" id="inp_rate" class="form-control nc-input text-center fs-5 border-secondary" value="{{ old('interest_rate', 0) }}" oninput="calcMain()" autocomplete="on">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="nc-label text-warning">فترة السداد (بالشهور) <span class="req">*</span></label>
                            <input type="number" step="1" min="1" name="installment_months" id="inp_mos" class="form-control nc-input text-center fs-5 border-warning text-warning" required oninput="calcMain()" value="{{ old('installment_months') }}" autocomplete="on">
                        </div>
                    </div>

                    <div class="row justify-content-center mb-4">
                        <div class="col-md-6">
                            <label class="nc-label text-center text-primary fs-6">يوم السداد الشهري (1 - 31) <span class="req">*</span></label>
                            <select name="due_day" class="form-select nc-input border-primary text-primary shadow-sm fw-bold text-center" required style="font-size:1.1rem;">
                                <option value="">— اختر يوم السداد —</option>
                                @for($dy=1; $dy<=30; $dy++)
                                    <option value="{{ $dy }}" {{ old('due_day', date('d')) == $dy ? 'selected' : '' }}>يوم {{ $dy }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Live calc result --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="nc-result-box bg-warning bg-opacity-10 border-warning">
                                <div class="nc-result-label text-warning">إجمالي المديونية (المتبقي بالخارج)</div>
                                <div class="nc-result-val text-dark" id="txt_total">0</div>
                                <span class="fw-bold text-muted small">ج.م</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="nc-result-box bg-danger bg-opacity-10 border-danger">
                                <div class="nc-result-label text-danger">القيمة الثابتة للقسط الشهري</div>
                                <div class="nc-result-val text-danger" id="txt_monthly">0</div>
                                <span class="fw-bold text-muted small">ج.م / شهر</span>
                            </div>
                        </div>
                    </div>

                    {{-- ══ عمولة المبيعات ══ --}}
                    <div class="p-3 rounded-3 border border-2 mb-3" style="border-color:#f59e0b !important; background:linear-gradient(135deg,#fffbeb,#fef3c7);">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span style="background:#f59e0b;color:#fff;border-radius:8px;padding:5px 12px;font-size:13px;font-weight:900;"><i class="fa fa-hand-holding-usd me-1"></i> عمولة المبيعات</span>
                            <small class="text-muted fw-bold">اختياري — ستُسجَّل تلقائياً كدين على الشركة (عمولات مستحقة)</small>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="nc-label" style="color:#b45309;"><i class="fa fa-coins me-1"></i> مبلغ العمولة (ج.م)</label>
                                <input type="number" step="0.01" min="0" name="commission_amount" id="inp_commission"
                                       class="form-control nc-input text-center fs-4 border-warning text-warning fw-bold"
                                       placeholder="0" value="{{ old('commission_amount', 0) }}">
                            </div>
                            <div class="col-md-7">
                                <div class="p-2 rounded-2 mt-3" style="background:#fef9c3;border:1.5px dashed #fcd34d;">
                                    <small class="fw-bold" style="color:#92400e;font-size:12px;">
                                        <i class="fa fa-info-circle me-1"></i>
                                        عند إدخال قيمة ستُسجَّل تحت بند <strong>"عمولات مستحقة"</strong> في ديون الشركة
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-success bg-opacity-10 border border-success border-2 p-3 rounded-3">
                        <label class="nc-label text-success mb-2 fs-6"><i class="fa fa-piggy-bank me-2"></i>إيداع المقدم في خزنة (إجباري في حالة وجود مقدم)</label>
                        <select name="deposit_account" id="inp_dep_acc" class="form-select nc-input border-success fw-bold text-dark fs-6" onchange="showVaultBalance('inp_dep_acc', 'dep_acc_bal')">
                            <option value="">-- يتم تحصيل المقدم بدون إيداع (مقدم صفر) --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" data-balance="{{ $acc->balance }}" {{ old('deposit_account') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} — {{ fmtMoney($acc->balance) }} ج
                                </option>
                            @endforeach
                        </select>
                        <div id="dep_acc_bal" class="vault-balance-display"></div>
                    </div>

                </div>

            </div>
            <div class="modal-footer border-0 p-4 bg-white">
                <button type="button" class="btn btn-light fw-bold px-5 py-3 border rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary fw-bold px-5 py-3 rounded-pill shadow-sm fs-5 flex-grow-1"><i class="fa fa-check-circle me-2"></i>اعتماد وحفظ العقد</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Dark Mode ──
    (function() {
        if(localStorage.getItem('darkMode') === '1') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
    
    document.addEventListener('DOMContentLoaded', function() {
        const themeBtn = document.getElementById('theme-toggle');
        if(themeBtn) {
            themeBtn.addEventListener('click', function() {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                if(isDark) {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.setItem('darkMode', '0');
                    themeBtn.innerHTML = '<i class="fa fa-moon"></i>';
                } else {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('darkMode', '1');
                    themeBtn.innerHTML = '<i class="fa fa-sun"></i>';
                }
            });
            if(document.documentElement.getAttribute('data-theme') === 'dark') {
                themeBtn.innerHTML = '<i class="fa fa-sun"></i>';
            }
        }

        calcMain();
    });

    document.getElementById('newContractModal')?.addEventListener('show.bs.modal', function() {
        const tbody = document.getElementById('contractItemsBody');
        if (tbody && tbody.children.length === 0) addContractItemRow();
    });

    function showVaultBalance(selectId, displayId) {
        const sel = document.getElementById(selectId);
        const disp = document.getElementById(displayId);
        if(!sel || !disp) return;
        const opt = sel.options[sel.selectedIndex];
        if(!opt || opt.value === '') { disp.innerHTML = ''; return; }
        const bal = parseFloat(opt.dataset.balance) || 0;
        const isLow = bal < 1000;
        const color = isLow ? '#ef4444' : '#16a34a';
        const icon  = isLow ? 'fa-triangle-exclamation' : 'fa-wallet';
        disp.innerHTML = `<span style="display:inline-flex;align-items:center;gap:6px;background:${isLow?'#fff1f2':'#f0fdf4'};border:1.5px solid ${isLow?'#fca5a5':'#86efac'};border-radius:20px;padding:4px 12px;font-size:13px;font-weight:900;color:${color};margin-top:4px;">
            <i class="fa ${icon}"></i> الرصيد المتاح: ${bal.toLocaleString('en-US')} ج.م
        </span>`;
    }

    // ════════════════════════════════════════════════════════════
    // 🚀 تحميل المودالات عند الطلب (Lazy) — بدل ترسيم آلاف المودالات مع كل تحميل
    // ════════════════════════════════════════════════════════════
    function _injectAndShow(html, hostId, modalSelector) {
        const host = document.getElementById(hostId);
        host.innerHTML = html;
        const modalEl = host.querySelector(modalSelector);
        if (!modalEl) return null;
        const m = bootstrap.Modal.getOrCreateInstance(modalEl);
        // نظّف الـ host بعد الإغلاق عشان مايتراكمش
        modalEl.addEventListener('hidden.bs.modal', () => { host.innerHTML = ''; }, { once: true });
        m.show();
        return modalEl;
    }

    // فتح مودال إجراء (سداد/تعديل/فسخ/إعدام) لعقد معيّن
    async function openActionModal(kind, id) {
        try {
            const res = await fetch(`/installments/${id}/action-modals`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('فشل التحميل');
            const html = await res.text();
            const modalEl = _injectAndShow(html, 'actionModalHost', '#' + kind + '_' + id);
            // تهيئة معاينة الفسخ بعد الفتح
            if (kind === 'terminateModal' && modalEl) {
                const refundInp = modalEl.querySelector('[name="refund_amount"]');
                if (refundInp) updateDiffPreview(id, parseFloat(refundInp.getAttribute('max')) || 0);
            }
        } catch (e) { alert('تعذّر فتح النافذة، حاول تاني.'); }
    }

    // فتح كشف حساب عميل (lazy) عبر الهاتف أو الاسم
    async function openStatement(phone, name) {
        try {
            const qs = new URLSearchParams({ phone: phone || '', name: name || '' });
            const res = await fetch(`/installments/customer-statement?${qs.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('فشل التحميل');
            const html = await res.text();
            _injectAndShow(html, 'statementModalHost', '.modal');
        } catch (e) { alert('تعذّر فتح كشف الحساب، حاول تاني.'); }
    }

    // متوافقة مع الأزرار القديمة في تابة المنتهية (بتمرّر phone|name مفصولين بـ |)
    function openCustomerModal(phoneOrPipe, name) {
        if (name !== undefined) { openStatement(phoneOrPipe, name); return; }
        const parts = String(phoneOrPipe).split('|');
        openStatement(parts[0] || '', parts[1] || '');
    }

    // دوال الفسخ (منقولة من المودال للسكريبت الرئيسي عشان تشتغل مع التحميل الكسول)
    function updateDiffPreview(instId, totalPaid) {
        const refundEl = document.getElementById('term_refund_' + instId);
        const el = document.getElementById('diff_preview_' + instId);
        if (!refundEl || !el) return;
        const refund = parseFloat(refundEl.value) || 0;
        const diff = totalPaid - refund;
        if (diff > 0.01)      el.innerHTML = `💰 خصم للشركة: <span class="text-success">${diff.toLocaleString('en-US')} ج</span>`;
        else if (diff < -0.01) el.innerHTML = `⚠️ <span class="text-danger">المبلغ أكبر من المدفوع!</span>`;
        else                  el.innerHTML = `<span class="text-muted">رد كامل بدون خصم</span>`;
    }
    function validateTerminate(e, form, instId, totalPaid) {
        const refund = parseFloat(form.refund_amount.value) || 0;
        const reason = (form.reason.value || '').trim();
        if (refund < 0) { e.preventDefault(); alert('المبلغ مش صحيح'); return false; }
        if (refund > totalPaid + 0.01) { e.preventDefault(); alert('المبلغ أكبر من المدفوع فعلاً'); return false; }
        if (!reason) { e.preventDefault(); alert('اكتب سبب الفسخ'); return false; }
        return confirm('متأكد إنك عايز تفسخ العقد؟ ده غير قابل للتراجع.');
    }

    // بعد سداد قسط: نعيد فتح كشف حساب نفس العميل تلقائياً (lazy)
    @if(session('reopen_phone') !== null || session('reopen_name') !== null)
    (function () {
        var __rPhone = @json(session('reopen_phone', ''));
        var __rName  = @json(session('reopen_name', ''));
        function __doReopen() { try { openStatement(__rPhone, __rName); } catch (e) {} }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { setTimeout(__doReopen, 350); });
        } else {
            setTimeout(__doReopen, 350);
        }
    })();
    @endif
    function switchTab(groupKey, tabName) {
        document.querySelectorAll('[id^="pane_' + groupKey + '_"]').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.cst-tab[data-group="' + groupKey + '"]').forEach(t => {
            t.classList.remove('active-tab');
            t.style.background = '';
            t.style.color = '';
        });
        var target = document.getElementById('pane_' + groupKey + '_' + tabName);
        if (target) target.style.display = 'block';
        var activeTab = document.querySelector('.cst-tab[data-group="' + groupKey + '"][data-pane="' + tabName + '"]');
        if (activeTab) activeTab.classList.add('active-tab');
        const cap = document.getElementById('captureCustomer_' + groupKey);
        if (cap) cap.dataset.activePane = tabName;
    }

    // إصلاح انعكاس النص العربي في html2canvas:
    // html2canvas يعكس ترتيب الحروف العربية عند وجود letter-spacing (وفي الصفحة letter-spacing سالب على body وعناصر كثيرة).
    // الحل: نُلغي letter-spacing على نسخة العنصر المُصوَّرة فقط (عبر onclone) فيخرج النص سليماً غير معكوس.
    function captureNodeToPng(node, options) {
        options = options || {};
        return html2canvas(node, {
            scale: options.scale || 2,
            backgroundColor: options.backgroundColor || '#ffffff',
            useCORS: true,
            scrollY: 0,
            windowHeight: node.scrollHeight + 100,
            logging: false,
            imageTimeout: 0,
            onclone: function(doc) {
                // إلغاء letter-spacing عبر ستايل واحد (أسرع بكتير من المرور على كل عنصر)
                var s = doc.createElement('style');
                s.textContent = '*{letter-spacing:normal !important;}';
                (doc.head || doc.documentElement).appendChild(s);
            }
        }).then(function(canvas) {
            // JPEG أسرع في الترميز وأخف من PNG (الكشف خلفيته صلبة فلا حاجة للشفافية)
            return canvas.toDataURL(options.mime || 'image/jpeg', options.quality || 0.95);
        });
    }

    function getActiveCustomerPane(groupKey) {
        const wrap = document.getElementById('captureCustomer_' + groupKey);
        if (!wrap) return null;
        const paneName = wrap.dataset.activePane;
        if (paneName) {
            const byData = document.getElementById('pane_' + groupKey + '_' + paneName);
            if (byData) return byData;
        }
        return [...wrap.querySelectorAll('.cst-pane')].find(p => {
            const st = window.getComputedStyle(p);
            return st.display !== 'none' && st.visibility !== 'hidden';
        }) || wrap.querySelector('.cst-pane[id*="contract_"]') || wrap.querySelector('.cst-pane');
    }

    // علم يدل إن الخطوط حُمّلت مرة واحدة (نتجنب انتظارها كل مرة → أسرع)
    let _fontsWarmed = false;
    function warmFonts() {
        if (_fontsWarmed) return Promise.resolve();
        const fr = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
        return fr.then(function(){ _fontsWarmed = true; });
    }

    // نسخة من ستايلات الصفحة (تُحقن في الـ iframe المعزول) — تُحسب مرة واحدة وتُخزَّن
    let _isoHeadStyles = null;
    function getIsoHeadStyles() {
        if (_isoHeadStyles !== null) return _isoHeadStyles;
        let html = '';
        document.querySelectorAll('style, link[rel="stylesheet"]').forEach(function(el) { html += el.outerHTML; });
        _isoHeadStyles = html;
        return html;
    }

    // التصوير داخل iframe معزول يحتوي الكشف فقط → html2canvas يستنسخ DOM ضئيل بدل الصفحة كلها (أسرع بمراحل)
    function renderBoxIsolated(box, width, scale) {
        return new Promise(function(resolve, reject) {
            const iframe = document.createElement('iframe');
            iframe.setAttribute('aria-hidden', 'true');
            iframe.style.cssText = 'position:fixed;left:-99999px;top:0;width:' + width + 'px;height:10px;border:0;visibility:hidden;';
            document.body.appendChild(iframe);

            let cleaned = false;
            const cleanup = function() { if (!cleaned) { cleaned = true; if (iframe.parentNode) iframe.parentNode.removeChild(iframe); } };

            try {
                const idoc = iframe.contentDocument || iframe.contentWindow.document;
                idoc.open();
                idoc.write('<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="utf-8">' + getIsoHeadStyles() + '</head><body style="margin:0;background:#fffde7;"></body></html>');
                idoc.close();

                const target = idoc.importNode(box, true);
                target.style.position = 'static';
                target.style.left = 'auto';
                idoc.body.appendChild(target);

                const done = function() {
                    html2canvas(target, {
                        scale: scale || 2,
                        backgroundColor: '#fffde7',
                        useCORS: true,
                        logging: false,
                        imageTimeout: 0,
                        onclone: function(doc) {
                            const s = doc.createElement('style');
                            s.textContent = '*{letter-spacing:normal !important;}';
                            (doc.head || doc.documentElement).appendChild(s);
                        }
                    }).then(function(canvas) {
                        // فحص بسيط: لو الكانفاس فاضي اعتبره فشل عشان يشتغل الـ fallback
                        if (!canvas || canvas.width < 50 || canvas.height < 50) { cleanup(); reject(new Error('empty canvas')); return; }
                        const url = canvas.toDataURL('image/jpeg', 0.95);
                        cleanup();
                        resolve(url);
                    }).catch(function(e) { cleanup(); reject(e); });
                };

                // انتظر جاهزية الخطوط داخل الـ iframe (مع مهلة أمان)
                let settled = false;
                const go = function() { if (settled) return; settled = true; setTimeout(done, 30); };
                const fr = (idoc.fonts && idoc.fonts.ready) ? idoc.fonts.ready : Promise.resolve();
                fr.then(go);
                setTimeout(go, 800);
            } catch (e) {
                cleanup();
                reject(e);
            }
        });
    }

    // يبني صورة لعقد/كشف مُعيّن (pane) ويرجّع Promise بـ {dataUrl, product}
    function capturePaneToPng(wrap, activePane, scale) {
        return new Promise(function(resolve, reject) {
            if (!wrap || !activePane) { reject(new Error('no pane')); return; }

            const box = document.createElement('div');
            box.setAttribute('dir', 'rtl');
            box.style.cssText = "background:#fffde7;width:680px;direction:rtl;unicode-bidi:embed;font-family:'IBM Plex Sans Arabic','Cairo',sans-serif;";

            const printHeader = wrap.querySelector('.print-header-' + (wrap.id.replace('captureCustomer_','')));
            if (printHeader) {
                const hClone = printHeader.cloneNode(true);
                hClone.style.display = 'block';
                const sub = hClone.querySelector('div:last-child');
                const product = activePane.dataset.product;
                const customerName = wrap.dataset.customerName || '';
                if (sub) {
                    let line = 'العميل: ' + customerName + ' | تاريخ الطباعة: ' + new Date().toISOString().slice(0, 10);
                    if (product) line = 'العميل: ' + customerName + ' | المنتج: ' + product + ' | تاريخ الطباعة: ' + new Date().toISOString().slice(0, 10);
                    sub.textContent = line;
                }
                box.appendChild(hClone);
            }

            const paneClone = activePane.cloneNode(true);
            paneClone.style.display = 'block';
            paneClone.querySelectorAll('.sheet-no-export, button').forEach(el => el.remove());
            box.appendChild(paneClone);

            const product = (activePane.dataset.product || 'عقد').replace(/[\\/:*?"<>|]/g, '_').slice(0, 40);

            // المسار السريع: تصوير معزول داخل iframe
            renderBoxIsolated(box, 680, scale || 2)
                .then(function(dataUrl) { resolve({ dataUrl: dataUrl, product: product }); })
                .catch(function() {
                    // fallback: الطريقة القديمة (تصوير من الصفحة مباشرة) لو فشل المسار السريع
                    box.style.cssText = "position:fixed;left:-9999px;top:0;z-index:-1;background:#fffde7;width:680px;direction:rtl;unicode-bidi:embed;font-family:'IBM Plex Sans Arabic','Cairo',sans-serif;";
                    document.body.appendChild(box);
                    warmFonts().then(function() {
                        setTimeout(function() {
                            captureNodeToPng(box, { scale: scale || 2, backgroundColor: '#fffde7' })
                                .then(function(dataUrl) { document.body.removeChild(box); resolve({ dataUrl: dataUrl, product: product }); })
                                .catch(function(err) { if (box.parentNode) document.body.removeChild(box); reject(err); });
                        }, _fontsWarmed ? 30 : 200);
                    });
                });
        });
    }

    // درجة جودة الصورة (أقل = أسرع وأخف على المتصفح)
    const WA_CAPTURE_SCALE = 1.5;

    // يبني صورة الكشف/العقد النشط ويرجّع Promise بـ {dataUrl, product}
    function captureCustomerSheet(groupKey) {
        const wrap = document.getElementById('captureCustomer_' + groupKey);
        const activePane = getActiveCustomerPane(groupKey);
        return capturePaneToPng(wrap, activePane, WA_CAPTURE_SCALE);
    }

    function downloadCustomerSheet(groupKey) {
        captureCustomerSheet(groupKey).then(function(res) {
            const link = document.createElement('a');
            link.download = 'كشف_' + res.product + '.jpg';
            link.href = res.dataUrl;
            link.click();
        }).catch(function(err) {
            console.error(err);
            alert('تعذّر إنشاء الصورة، حاول مرة أخرى.');
        });
    }

    // تحويل dataURL لملف صورة (عشان المشاركة المباشرة)
    function dataUrlToFile(dataUrl, filename) {
        const arr = dataUrl.split(',');
        const mime = (arr[0].match(/:(.*?);/) || [])[1] || 'image/png';
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8 = new Uint8Array(n);
        while (n--) u8[n] = bstr.charCodeAt(n);
        return new File([u8], filename, { type: mime });
    }

    // تطبيع رقم الموبايل المصري لصيغة واتساب الدولية (20...)
    function waNormalizePhone(phone) {
        let raw = (phone || '').replace(/\D/g, '');
        if (!raw) return '';
        if (raw.startsWith('20')) return raw;
        if (raw.startsWith('0'))  return '2' + raw;   // 01xxxx -> 201xxxx
        return '2' + raw;
    }

    function waLoading(msg) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            title: msg || 'جاري تجهيز صورة العقد...',
            html: '<div style="font-size:13px;color:#64748b;">لحظة واحدة من فضلك</div>',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });
    }

    // إرسال صورة العقد الحالي للعميل على الواتساب
    window.sendCustomerSheetWhatsApp = function(groupKey, phone) {
        const p = waNormalizePhone(phone);
        if (!p) { alert('لا يوجد رقم موبايل صالح لهذا العميل.'); return; }
        const greeting = 'السلام عليكم ورحمة الله وبركاته،\nتفضل/ي صورة العقد الخاص بحضرتك من شركة الضبع.';
        const waWebUrl = 'https://web.whatsapp.com/send?phone=' + p + '&text=' + encodeURIComponent(greeting);

        waLoading('جاري تجهيز صورة العقد...');
        captureCustomerSheet(groupKey).then(function(res) {
            const file = dataUrlToFile(res.dataUrl, 'عقد_' + res.product + '.jpg');

            // 1) موبايل — Web Share API (يرسل الصورة مباشرة)
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                if (typeof Swal !== 'undefined') Swal.close();
                navigator.share({ files: [file], text: greeting, title: 'عقد ' + res.product })
                    .catch(function() {});
                return;
            }

            // 2) ديسكتوب — نسخ الصورة للكليبورد + فتح واتساب ويب على المحادثة مباشرة
            fetch(res.dataUrl)
                .then(function(r) { return r.blob(); })
                .then(function(blob) {
                    var pngBlob = blob.type === 'image/png' ? blob : null;
                    // نحوّل لـ PNG لو مش PNG
                    if (!pngBlob) {
                        var canvas = document.createElement('canvas');
                        var img = new Image();
                        img.onload = function() {
                            canvas.width = img.width; canvas.height = img.height;
                            canvas.getContext('2d').drawImage(img, 0, 0);
                            canvas.toBlob(function(b) { doCopyAndOpen(b, waWebUrl); }, 'image/png');
                        };
                        img.src = res.dataUrl;
                    } else {
                        doCopyAndOpen(pngBlob, waWebUrl);
                    }
                });
        }).catch(function(err) {
            if (typeof Swal !== 'undefined') Swal.close();
            console.error(err);
            alert('تعذّر إنشاء صورة العقد، حاول مرة أخرى.');
        });
    };

    function doCopyAndOpen(blob, waUrl) {
        var clipItem = new ClipboardItem({ 'image/png': blob });
        navigator.clipboard.write([clipItem]).then(function() {
            if (typeof Swal !== 'undefined') Swal.close();
            window.open(waUrl, '_blank');
            setTimeout(function() {
                Swal.fire({
                    icon: 'info',
                    title: 'الصورة جاهزة في الكليبورد 📋',
                    html: 'واتساب ويب فُتح على المحادثة.<br><b>اضغط Ctrl+V</b> داخل المحادثة ثم ارسل.',
                    confirmButtonText: 'تمام',
                    confirmButtonColor: '#25d366',
                    timer: 8000,
                    timerProgressBar: true
                });
            }, 1200);
        }).catch(function() {
            // لو الكليبورد مش مسموح — نفتح واتساب وننزل الصورة
            if (typeof Swal !== 'undefined') Swal.close();
            window.open(waUrl, '_blank');
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a'); a.href = url; a.download = 'عقد.png'; a.click();
            setTimeout(function() { URL.revokeObjectURL(url); }, 1000);
            setTimeout(function() {
                Swal.fire({
                    icon: 'info',
                    title: 'واتساب فُتح',
                    html: 'الصورة اتنزلت — ارفقها من 📎 وابعت.',
                    confirmButtonText: 'تمام',
                    confirmButtonColor: '#25d366'
                });
            }, 800);
        });
    }

    // إرسال كل عقود العميل دفعة واحدة على الواتساب
    window.sendAllContractsWhatsApp = function(groupKey, phone) {
        const p = waNormalizePhone(phone);
        if (!p) { alert('لا يوجد رقم موبايل صالح لهذا العميل.'); return; }
        const wrap = document.getElementById('captureCustomer_' + groupKey);
        if (!wrap) { alert('تعذّر العثور على بيانات العميل.'); return; }

        // كل عقود العميل (نتجاهل بطاقة الملخص)
        const panes = [...wrap.querySelectorAll('.cst-pane[id*="contract_"]')];
        if (panes.length === 0) { alert('لا توجد عقود لإرسالها.'); return; }
        if (panes.length === 1) { window.sendCustomerSheetWhatsApp(groupKey, phone); return; }

        const greeting = 'السلام عليكم ورحمة الله وبركاته،\nتفضل/ي صور العقود الخاصة بحضرتك من شركة الضبع.';
        const waUrl = 'https://wa.me/' + p + '?text=' + encodeURIComponent(greeting);

        waLoading('جاري تجهيز ' + panes.length + ' عقود...');

        // نصوّر العقود واحداً تلو الآخر (تسلسلياً) لتجنّب تعليق المتصفح
        const files = [];
        let chain = Promise.resolve();
        panes.forEach(function(pane, idx) {
            chain = chain.then(function() {
                if (typeof Swal !== 'undefined') {
                    Swal.update({ title: 'جاري تجهيز العقود... (' + (idx + 1) + '/' + panes.length + ')' });
                    Swal.showLoading();
                }
                return capturePaneToPng(wrap, pane, WA_CAPTURE_SCALE).then(function(res) {
                    files.push(dataUrlToFile(res.dataUrl, 'عقد_' + (idx + 1) + '_' + res.product + '.jpg'));
                    // فاصل بسيط يخلّي المتصفح ياخد نفسه بين العقود (يمنع رسالة عدم الاستجابة)
                    return new Promise(function(r){ setTimeout(r, 200); });
                });
            });
        });

        chain.then(function() {
            // 1) مشاركة كل الصور مرة واحدة لو الجهاز يدعمها
            if (navigator.canShare && navigator.canShare({ files: files })) {
                if (typeof Swal !== 'undefined') Swal.close();
                navigator.share({ files: files, text: greeting, title: 'عقود العميل' })
                    .catch(function(err) { /* المستخدم لغى */ });
                return;
            }

                // 2) البديل: تنزيل كل الصور ثم فتح واتساب
                files.forEach(function(f, i) {
                    setTimeout(function() {
                        const url = URL.createObjectURL(f);
                        const link = document.createElement('a');
                        link.download = f.name;
                        link.href = url;
                        link.click();
                        setTimeout(function(){ URL.revokeObjectURL(url); }, 1000);
                    }, i * 400);   // فاصل بسيط عشان المتصفح ما يمنعش التنزيلات المتعددة
                });

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم تنزيل ' + files.length + ' عقود ✅',
                        html: 'دوس "افتح واتساب" → هيفتح على رقم العميل جاهز.<br>بعدين ارفق الصور اللي اتنزلت وابعتها.',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fab fa-whatsapp"></i> افتح واتساب',
                        cancelButtonText: 'إغلاق',
                        confirmButtonColor: '#25d366'
                    }).then(function(r) {
                        if (r.isConfirmed) window.open(waUrl, '_blank');
                    });
                } else {
                    window.open(waUrl, '_blank');
                }
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') Swal.close();
                console.error(err);
                alert('تعذّر إنشاء صور العقود، حاول مرة أخرى.');
            });
        };

        function disableBtn(e, form) {
            if(form.classList.contains('is-submitting')) { e?.preventDefault(); return false; }
            form.classList.add('is-submitting');
            let btn = form.querySelector('button[type="submit"]');
            if(btn) { btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> جاري التنفيذ...'; btn.classList.add('disabled'); btn.style.pointerEvents = 'none'; }
            return true;
        }

        const dbCustomers = {!! json_encode(
            \Illuminate\Support\Facades\DB::table('installments')
                ->select('customer_name', 'customer_phone')
                ->whereNotNull('customer_phone')
                ->where('customer_phone', '!=', '-')
                ->orderByDesc('created_at')
                ->get()
                ->merge(
                    \Illuminate\Support\Facades\DB::table('customers')->select('name as customer_name', 'phone as customer_phone')->whereNotNull('phone')->get()
                )
                ->unique('customer_phone')
                ->values()
        ) !!};

        function checkCustomer(phone) {
            if(dbCustomers && dbCustomers.length > 0) {
                const found = dbCustomers.find(c => c.customer_phone === phone);
                const note = document.getElementById('cust_found_note');
                const nameInput = document.getElementById('cust_name_input');
                if (found) { 
                    nameInput.value = found.customer_name;
                    nameInput.dataset.originalName = found.customer_name;
                    nameInput.classList.add('border-success');
                    if(note) note.style.display = 'block';
                } else {
                    delete nameInput.dataset.originalName;
                    nameInput.classList.remove('border-success');
                    if(note) note.style.display = 'none';
                }
            }
        }

    document.getElementById('cust_name_input')?.addEventListener('input', function() {
        const phone = document.querySelector('input[name="customer_phone"]')?.value?.trim();
        if (!phone) return;
        const found = dbCustomers?.find(c => c.customer_phone === phone);
        if (!found) return;
        const originalName = this.dataset.originalName;
        const newName = this.value.trim();
        if (originalName && newName && newName !== originalName) {
            let existingWarn = document.getElementById('name_change_warning');
            if (!existingWarn) {
                existingWarn = document.createElement('div');
                existingWarn.id = 'name_change_warning';
                existingWarn.className = 'alert alert-warning fw-bold mt-2 py-2 px-3';
                existingWarn.style.cssText = 'font-size:.82rem;border-radius:8px;border:1.5px solid #fbbf24;background:#fffbeb;';
                existingWarn.innerHTML = `<i class="fa fa-exclamation-triangle me-1 text-warning"></i>
                    <strong>تنبيه:</strong> هذا الرقم مسجل باسم <span id="warn_old_name" class="text-danger fw-bold"></span>.
                    إذا عدّلت الاسم الآن، ستتأثر <strong>جميع عقوده السابقة</strong> بالاسم الجديد.`;
                this.parentNode.appendChild(existingWarn);
            }
            document.getElementById('warn_old_name').textContent = '"' + originalName + '"';
            existingWarn.style.display = 'block';
        } else {
            const warn = document.getElementById('name_change_warning');
            if (warn) warn.style.display = 'none';
        }
    });

    function calcMain() {
        let cPrice = parseFloat(document.getElementById('inv_price_disp').value)||0;
        
        let down = parseFloat(document.getElementById('inp_down').value)||0;
        let disc = parseFloat(document.getElementById('inp_disc').value)||0;
        let rate = parseFloat(document.getElementById('inp_rate').value)||0;
        let mos  = parseInt(document.getElementById('inp_mos').value)||0;
        
        let afterDisc = Math.max(0, cPrice - disc);
        let baseForInterest = Math.max(0, afterDisc - down);
        let interestVal = baseForInterest * (rate / 100);
        
        let totalAfterInt = afterDisc + interestVal; 
        let rem = Math.max(0, totalAfterInt - down);
        let mon = mos > 0 ? (rem / mos) : 0;
        
        let fmt = (v) => v % 1 === 0 ? v.toLocaleString('en-US') : v.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        
        document.getElementById('txt_total').innerText = fmt(rem);
        document.getElementById('txt_monthly').innerText = fmt(mon);
        
        if(document.getElementById('calc_cash_show'))   document.getElementById('calc_cash_show').innerText   = fmt(cPrice) + ' ج';
        if(document.getElementById('calc_deduct_show')) document.getElementById('calc_deduct_show').innerText = fmt(down + disc) + ' ج';
        if(document.getElementById('calc_int_show'))    document.getElementById('calc_int_show').innerText    = fmt(interestVal) + ' ج';
        if(document.getElementById('calc_total_show'))  document.getElementById('calc_total_show').innerText  = fmt(rem) + ' ج';
        
        document.getElementById('h_total').value = totalAfterInt;
        document.getElementById('h_monthly').value = mon;
        document.getElementById('h_discount_amount').value = disc;
        document.getElementById('h_cash_price').value = cPrice;
    }

    // 💡 الفاليديشن المعدل ليتحقق من الأصناف المتعددة وخزنة مصاريف التكييف
    function validateContractForm(e, form) {
        e.preventDefault();
        if(form.classList.contains('submitting')) return false;

        let cName = form.querySelector('input[name="customer_name"]').value.trim();
        let down = parseFloat(document.getElementById('inp_down').value) || 0;
        let mos = parseInt(document.getElementById('inp_mos').value) || 0;

        // لازم صف واحد على الأقل عليه صنف مختار بكمية وسعر صحيحين
        const itemRows = Array.from(document.querySelectorAll('#contractItemsBody tr'));
        const validRows = itemRows.filter(row => {
            const sel = row.querySelector('.ci-select');
            const qty = parseFloat(row.querySelector('.ci-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.ci-price')?.value) || 0;
            return sel && sel.value && qty > 0 && price >= 0;
        });

        if(!cName) return Swal.fire('بيانات ناقصة', 'الرجاء إدخال اسم العميل', 'warning');
        if(validRows.length === 0) return Swal.fire('بيانات ناقصة', 'الرجاء اختيار صنف واحد على الأقل من المخزن', 'warning');
        if(mos <= 0) return Swal.fire('بيانات ناقصة', 'الرجاء إدخال عدد الشهور الصحيح', 'warning');

        // التحقق من الخزنة المخصصة لدفع النقل والتركيب والخامات
        {
            let t = parseFloat(document.getElementById('ac_transport')?.value) || 0;
            let i_cost = parseFloat(document.getElementById('ac_installation')?.value) || 0;
            let m = parseFloat(document.getElementById('ac_materials')?.value) || 0;
            if ((t + i_cost + m) > 0) {
                let acAcc = document.getElementById('ac_expense_acc')?.value;
                if (!acAcc) return Swal.fire('بيانات ناقصة', 'الرجاء اختيار خزنة لسحب مصاريف (النقل، التركيب، الخامات)', 'warning');
            }
        }

        if(down > 0) {
            let depAcc = document.getElementById('inp_dep_acc').value;
            if(!depAcc) return Swal.fire('بيانات ناقصة', 'الرجاء اختيار خزنة لإيداع المقدم', 'warning');
        }

        recalcContractItems();

        form.classList.add('submitting');
        let btn = form.querySelector('button[type="submit"]');
        if(btn) { 
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> جاري اعتماد العقد...'; 
            btn.classList.add('disabled'); 
            btn.style.pointerEvents = 'none';
        }
        form.submit();
    }

    function toggleRefund(id) {
        let mode = document.querySelector(`input[name="delete_mode"][id^="dm_"][id$="_${id}"]:checked`).value;
        let box = document.getElementById('refundBox_'+id);
        if(mode === 'refund') {
            box.style.display = 'block';
            let sel = document.getElementById('refAcc_'+id);
            if(sel) sel.setAttribute('required', 'required');
        } else {
            box.style.display = 'none';
            let sel = document.getElementById('refAcc_'+id);
            if(sel) sel.removeAttribute('required');
        }
    }

    function checkTotals(id, origRem, isSettle = false) {
        if(isSettle) {
            let disc = parseFloat(document.getElementById('settle_disc_'+id).value) || 0;
            let amt = parseFloat(document.getElementById('settle_amt_'+id).value) || 0;
            let sel = document.getElementById('settle_method_'+id);
            if (amt > 0) sel.setAttribute('required', 'required'); else sel.removeAttribute('required');
        } else {
            let amt = parseFloat(document.getElementById('amt_'+id).value) || 0;
            if(amt > origRem) {
                document.getElementById('amt_'+id).value = origRem;
            }
        }
    }

    function checkPayForm(e, form, id, origRem) {
        let type = document.querySelector('input[name="pay_type_'+id+'"]:checked').value;
        
        if(type === 'defaulted') {
            let amtInput = document.getElementById('amt_'+id);
            amtInput.value = 0;
            if(!form.querySelector('input[name="notes"]')) {
                let noteInput = document.createElement('input');
                noteInput.type = 'hidden'; noteInput.name = 'notes'; noteInput.value = 'تعثر';
                form.appendChild(noteInput);
            }
            return disableBtn(e, form);
        }
        
        let disc = parseFloat(document.getElementById('disc_'+id).value) || 0;
        let amtEl = document.getElementById('amt_'+id);
        let amt  = parseFloat(amtEl.value) || 0;
        let vault = document.getElementById('vault_sel_'+id);
        let payDate = document.getElementById('pay_date_'+id);
        
        if(type === 'partial') {
            amtEl.removeAttribute('readonly');
            amtEl.classList.remove('input-locked');
            amt = parseFloat(amtEl.value) || 0;
        }
        
        if(!payDate || !payDate.value) { Swal.fire('خطأ', 'اختر تاريخ السداد!', 'error'); e.preventDefault(); return false; }
        if(amt > 0 && (!vault || vault.value === '')) { Swal.fire('خطأ', 'يجب اختيار الخزنة!', 'error'); e.preventDefault(); return false; }
        if(disc + amt > origRem) { Swal.fire('خطأ', 'المجموع أكبر من المتبقي الأصلي!', 'error'); e.preventDefault(); return false; }
        if(disc === 0 && amt === 0) {
            if(type === 'partial') { Swal.fire('تنبيه', 'الرجاء إدخال المبلغ المخصص أولاً.', 'warning'); e.preventDefault(); return false; }
            Swal.fire('تنبيه', 'أدخل قيمة للخصم أو التحصيل.', 'warning'); e.preventDefault(); return false;
        }
        
        return disableBtn(e, form);
    }

    function checkRefundSafe(e, form, id) {
        let mode = form.querySelector('input[name="delete_mode"]:checked').value;
        if(mode === 'refund') {
            let reqAmt = parseFloat(document.getElementById('refundAmt_'+id).value) || 0;
            if(reqAmt > 0) {
                let sel = document.getElementById('refAcc_'+id);
                if(sel.value === "") { Swal.fire('خطأ', 'اختر الخزنة لرد المبلغ!', 'error'); e.preventDefault(); return false; }
                let bal = parseFloat(sel.options[sel.selectedIndex].dataset.balance) || 0;
                if(bal < reqAmt) { Swal.fire('الرصيد لا يكفي', `المبلغ المطلوب رده (${reqAmt} ج) أكبر من رصيد الخزنة (${bal} ج)!`, 'error'); e.preventDefault(); return false; }
            }
        }
        return disableBtn(e, form);
    }

    function downloadImage(elementId, fileName) {
        const el = document.getElementById(elementId);
        el.setAttribute('dir', 'rtl');
        const fontsReady = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
        fontsReady.then(() => {
            captureNodeToPng(el, { scale:2, backgroundColor:'#ffffff' }).then(dataUrl => {
                const link = document.createElement('a'); link.download = fileName + '.jpg'; link.href = dataUrl; link.click();
            }).catch(err => { console.error(err); alert('تعذّر إنشاء الصورة، حاول مرة أخرى.'); });
        });
    }

    function showSupDropdown() {
        const d = document.getElementById('sup_dropdown');
        if (d) d.style.display = 'block';
    }
    function filterSuppliers(val) {
        const opts = document.querySelectorAll('.sup-opt');
        const noResults = document.getElementById('sup_no_results');
        let anyVisible = false;
        opts.forEach(opt => {
            const name = opt.dataset.name.toLowerCase();
            if (!val || name.includes(val.toLowerCase())) {
                opt.style.display = 'block';
                anyVisible = true;
            } else {
                opt.style.display = 'none';
            }
        });
        if (noResults) noResults.style.display = (anyVisible || !val) ? 'none' : 'block';
        showSupDropdown();
    }
    function selectSupplier(name) {
        const inp = document.getElementById('sup_name_input');
        if (inp) inp.value = name;
        const d = document.getElementById('sup_dropdown');
        if (d) d.style.display = 'none';
    }
    document.addEventListener('click', function(e) {
        const inp = document.getElementById('sup_name_input');
        const d   = document.getElementById('sup_dropdown');
        if (inp && d && !inp.contains(e.target) && !d.contains(e.target)) {
            d.style.display = 'none';
        }
    });

    function formatMoneyDisplay(n) {
        const v = parseFloat(n) || 0;
        return (v % 1 === 0)
            ? v.toLocaleString('en-US')
            : v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function formatMoneyInput(n) {
        return Number((parseFloat(n) || 0).toFixed(2));
    }

    function updatePay(id, monthly, remaining) {
        const type    = document.querySelector('input[name="pay_type_'+id+'"]:checked')?.value;
        const disc    = parseFloat(document.getElementById('disc_'+id)?.value) || 0;
        const netRem  = Math.max(0, remaining - disc);
        const amtInp  = document.getElementById('amt_'+id);
        const dispRem = document.getElementById('disp_rem_'+id);
        const defAlert= document.getElementById('defaulted_alert_'+id);
        const vaultSec= document.getElementById('vault_section_'+id);

        if (!amtInp) return;

        const prevType = amtInp.dataset.payType || 'monthly';
        if (dispRem) dispRem.textContent = formatMoneyDisplay(netRem);

        if (type === 'defaulted') {
            amtInp.value = '0';
            amtInp.setAttribute('readonly', 'readonly');
            amtInp.classList.add('input-locked');
            if (defAlert)  defAlert.classList.remove('d-none');
            if (vaultSec)  vaultSec.style.display = 'none';
            amtInp.dataset.payType = type;
            return;
        }

        if (defAlert)  defAlert.classList.add('d-none');
        if (vaultSec)  vaultSec.style.display = '';

        if (type === 'monthly') {
            amtInp.value = formatMoneyInput(Math.min(monthly, netRem));
            amtInp.setAttribute('readonly', 'readonly');
            amtInp.classList.add('input-locked');
        } else if (type === 'full') {
            amtInp.value = formatMoneyInput(netRem);
            amtInp.setAttribute('readonly', 'readonly');
            amtInp.classList.add('input-locked');
        } else if (type === 'partial') {
            amtInp.removeAttribute('readonly');
            amtInp.classList.remove('input-locked');
            if (prevType !== 'partial') {
                amtInp.value = '';
                amtInp.focus();
            }
        }
        amtInp.dataset.payType = type;
    }

    function clampPayAmount(id, monthly, remaining) {
        const type = document.querySelector('input[name="pay_type_'+id+'"]:checked')?.value;
        if (type !== 'partial') return;

        const disc = parseFloat(document.getElementById('disc_'+id)?.value) || 0;
        const netRem = Math.max(0, remaining - disc);
        const amtInp = document.getElementById('amt_'+id);
        const dispRem = document.getElementById('disp_rem_'+id);
        if (!amtInp) return;

        if (dispRem) dispRem.textContent = formatMoneyDisplay(netRem);

        const raw = amtInp.value.trim();
        if (raw === '' || raw === '.') return;

        const val = parseFloat(raw);
        if (!isNaN(val) && val > netRem + 0.001) {
            amtInp.value = formatMoneyInput(netRem);
        }
    }

    function confirmDeletePayment(paymentId, customerName, amountPaid) {
        let msg = amountPaid > 0 
            ? `سيتم إلغاء الدفعة وإرجاع مبلغ <strong>(${amountPaid} ج)</strong> لنفس الخزنة، وسيتم إعادة المديونية على العميل.` 
            : `سيتم مسح هذا السجل نهائياً.`;

        Swal.fire({
            title: '⚠️ تأكيد إلغاء الدفعة',
            html: `<div style="text-align:right;font-family:Cairo,sans-serif;">
                        <p style="font-size:14px;font-weight:700;color:#64748b;margin-bottom:12px;">
                            العميل: <strong style="color:#0f172a;">${customerName}</strong>
                        </p>
                        <p style="font-size:13px;color:#ef4444;font-weight:700;margin-bottom:4px;">
                            <i class="fa fa-info-circle me-1"></i> ${msg}
                        </p>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fa fa-trash me-1"></i> تأكيد وحذف',
            cancelButtonText: 'إلغاء',
            customClass: { popup: 'swal-rtl-popup' },
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('del_payment_form_' + paymentId);
                if (form) form.submit();
            }
        });
    }
    
    // فلتر تاريخ التعاقد بقى اختيارات بسيطة (اليوم/أمس/الشهر/الكل) — مفيش نطاق محتاج تحقق
    function validateCollectionFilter(e) { return true; }

    // 💡 تعديل برمجي لإظهار صندوق الخزنة عند وجود مصاريف إضافية
    function calcAcExtras() {
        const t = parseFloat(document.getElementById('ac_transport')?.value) || 0;
        const i = parseFloat(document.getElementById('ac_installation')?.value) || 0;
        const m = parseFloat(document.getElementById('ac_materials')?.value) || 0;
        const total = t + i + m;

        const el = document.getElementById('ac_extras_total');
        if (el) el.innerText = total.toLocaleString('en-US') + ' ج.م';

        // إظهار أو إخفاء صندوق سحب المصاريف
        const accDiv = document.getElementById('ac_expense_acc_div');
        if (accDiv) {
            accDiv.style.display = total > 0 ? 'block' : 'none';
        }

        recalcContractItems();
    }

    // 🧺 إدارة صفوف الأصناف المتعددة بعقد التقسيط الواحد
    function addContractItemRow() {
        const tbody = document.getElementById('contractItemsBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-1">
                <select name="sale_id[]" class="form-select form-select-sm ci-select" required onchange="onContractItemSelect(this)">
                    <option value="" disabled selected>— اختر الصنف —</option>
                </select>
            </td>
            <td class="p-1"><input type="number" name="quantity[]" class="form-control form-control-sm text-center fw-bold border-warning text-warning ci-qty" step="1" min="1" value="1" required oninput="recalcContractItems()"></td>
            <td class="p-1"><input type="number" name="unit_price[]" class="form-control form-control-sm text-center fw-bold border-success text-success ci-price" step="0.01" min="0" value="0" required oninput="this.dataset.touched='1'; recalcContractItems();"></td>
            <td class="p-1 text-center fw-bold ci-line-total">0 ج</td>
            <td class="p-1 text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalcContractItems();"><i class="fa fa-trash"></i></button></td>
        `;
        tbody.appendChild(tr);

        // استنساخ خيارات المخزن من القالب المخفي
        const sel = tr.querySelector('.ci-select');
        const tpl = document.getElementById('ci_options_template');
        if (tpl) sel.innerHTML = sel.innerHTML + tpl.innerHTML;

        return tr;
    }

    function onContractItemSelect(selectEl) {
        const row = selectEl.closest('tr');
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !selectEl.value) return;

        const priceInp = row.querySelector('.ci-price');
        // أول اختيار للصنف نملأ السعر بسعر البيع الافتراضي (يفضل قابل للتعديل بعدها)
        priceInp.value = opt.dataset.price || 0;
        priceInp.dataset.touched = '';

        const qtyInp = row.querySelector('.ci-qty');
        const maxQty = parseFloat(opt.dataset.qty) || 0;
        if ((parseFloat(qtyInp.value) || 0) > maxQty) qtyInp.value = maxQty || 1;
        qtyInp.max = maxQty;

        recalcContractItems();
    }

    // يعيد حساب إجمالي كل الصفوف + يحدد لو فيه صنف تكييف (لإظهار بنود التركيب) + يغذي calcMain()
    function recalcContractItems() {
        const rows = document.querySelectorAll('#contractItemsBody tr');
        let subtotal = 0;
        let anyAC = false;
        const fmt = (v) => v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        rows.forEach(row => {
            const sel = row.querySelector('.ci-select');
            const opt = sel.options[sel.selectedIndex];
            const qtyInp = row.querySelector('.ci-qty');
            const priceInp = row.querySelector('.ci-price');
            const lineTotalEl = row.querySelector('.ci-line-total');

            if (!opt || !sel.value) { lineTotalEl.innerText = '0 ج'; return; }

            const maxQty = parseFloat(opt.dataset.qty) || 0;
            let qty = parseFloat(qtyInp.value) || 0;
            if (qty > maxQty) { qty = maxQty; qtyInp.value = maxQty; }

            const price = parseFloat(priceInp.value) || 0;
            const lineTotal = qty * price;
            subtotal += lineTotal;
            lineTotalEl.innerText = fmt(lineTotal) + ' ج';

            const productName = opt.dataset.name || '';
            const productNameLower = productName.toLowerCase();
            const productCategory = opt.dataset.category || '';
            const isAC = productCategory === 'تكييفات' || productName.includes('تكييف') || productName.includes('تكيف') || productName.includes('مكيف') || productName.includes('مكيفة') || productNameLower.includes('ac ') || productNameLower.startsWith('ac') || productNameLower.includes('a/c');
            if (isAC) anyAC = true;
        });

        document.getElementById('contractItemsSubtotal').innerText = fmt(subtotal) + ' ج';

        const acBox = document.getElementById('ac_extras_box');
        if (acBox) acBox.style.display = anyAC ? 'block' : 'none';

        const acTransport = anyAC ? (parseFloat(document.getElementById('ac_transport')?.value) || 0) : 0;
        const acInstall   = anyAC ? (parseFloat(document.getElementById('ac_installation')?.value) || 0) : 0;
        const acMaterials = anyAC ? (parseFloat(document.getElementById('ac_materials')?.value) || 0) : 0;
        const acExtras    = acTransport + acInstall + acMaterials;

        const totalCash = subtotal + acExtras;
        const priceInput = document.getElementById('inv_price_disp');
        if (priceInput) priceInput.value = totalCash;

        calcMain();
    }

    let allInstData = [];
    let currentStatusFilter = 'all'; // all | full | partial | unpaid

    function loadInstData() {
        const el = document.getElementById('allInstallmentsData');
        if (el) {
            try { allInstData = JSON.parse(el.textContent); } catch(e) { allInstData = []; }
        }
    }

    // حالة السداد بتُحسب على الشهر الحالي دايمًا (قيمة جاهزة من السيرفر)
    function getPeriodSelection() {
        return { mode: 'month' };
    }

    // حساب المبلغ المُحصَّل لعقد معيّن خلال الفترة المختارة (شهر حالي/تاريخ محدد/نطاق)
    function computePeriodStatus(inst, period) {
        if (period.mode === 'date' && period.date) {
            const paid = (inst.payments_list || [])
                .filter(p => p.date.substring(0, 10) === period.date)
                .reduce((s, p) => s + p.amount, 0);
            return { paid, isFull: paid >= (inst.monthly_installment * 0.99) };
        }
        if (period.mode === 'range' && period.from && period.to) {
            const paid = (inst.payments_list || [])
                .filter(p => { const d = p.date.substring(0, 10); return d >= period.from && d <= period.to; })
                .reduce((s, p) => s + p.amount, 0);
            return { paid, isFull: paid >= (inst.monthly_installment * 0.99) };
        }
        // الافتراضي: الشهر الحالي (محسوب جاهز من السيرفر)
        return { paid: inst.paid_this_month_amount, isFull: inst.paid_this_month };
    }

    // اختصار: اضبط رينج يوم الاستحقاق على يوم النهاردة بالظبط
    function setTodayDueFilter() {
        const today = new Date().getDate();
        document.getElementById('dueRangeFrom').value = String(today);
        document.getElementById('dueRangeTo').value   = String(today);
        applyActiveFilters();
    }

    // اختيار حالة السداد من الـ pills (الكل/كامل/جزئي/لم يسدد)
    function setStatusFilter(status, btnEl) {
        currentStatusFilter = status;
        document.querySelectorAll('#statusPills .status-pill').forEach(p => p.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');
        applyActiveFilters();
    }

    // مسح كل الفلاتر والرجوع للوضع الافتراضي
    function resetActiveFilters() {
        document.getElementById('activeSearch').value = '';
        document.getElementById('dueRangeFrom').value = '0';
        document.getElementById('dueRangeTo').value   = '0';
        currentStatusFilter = 'all';
        document.querySelectorAll('#statusPills .status-pill').forEach(p => p.classList.toggle('active', p.dataset.status === 'all'));
        applyActiveFilters();
    }

    // 💡 الإحصائيات بتُحسب دايمًا على الكل (مش متأثرة بفلتر الحالة) — عشان الأرقام تفضل مرجع ثابت
    function updateDueStats(allInRange) {
        const totalAmt     = allInRange.reduce((s, i) => s + i.monthly_installment, 0);
        const fullPaid     = allInRange.filter(i => i._isPaid).length;
        const partialPaid  = allInRange.filter(i => !i._isPaid && i._collected > 0).length;
        const unpaid       = allInRange.filter(i => i._collected === 0).length;
        const collected    = allInRange.reduce((s, i) => s + i._collected, 0);
        const remaining    = totalAmt - collected;

        document.getElementById('statTotal').innerText       = allInRange.length;
        document.getElementById('statDue').innerText         = totalAmt.toLocaleString('en-US') + ' ج';
        document.getElementById('statFullPaid').innerText    = fullPaid;
        document.getElementById('statPartialPaid').innerText = partialPaid;
        document.getElementById('statUnpaid').innerText      = unpaid;
        document.getElementById('statCollected').innerText   = collected.toLocaleString('en-US') + ' ج';
        document.getElementById('statRemaining').innerText   = remaining.toLocaleString('en-US') + ' ج';
    }

    // ── ترقيم الصفحات: 15 صف للصفحة + شريط تنقل ──
    const DUE_PAGE_SIZE = 15;
    let _dueSorted = [];
    let _duePage = 1;

    // مفتاح تجميع العميل: الهاتف لو موجود، وإلا الاسم — نفس منطق التجميع المستخدم في باقي الشاشة
    function custKey(inst) {
        const phone = String(inst.customer_phone || '').trim();
        return (phone && phone !== '—') ? phone : ('n:' + (inst.customer_name || ''));
    }

    // يجمّع مجموعة عقود (بعد حساب _isPaid/_collected عليها) في صفوف عملاء — كل عميل صف واحد بإجمالياته
    function groupContractsByCustomer(contracts) {
        const groups = {};
        contracts.forEach(inst => {
            const key = custKey(inst);
            if (!groups[key]) groups[key] = { name: inst.customer_name, phone: inst.customer_phone, contracts: [] };
            groups[key].contracts.push(inst);
        });
        return Object.values(groups).map(g => {
            const fullCount    = g.contracts.filter(c => c._isPaid).length;
            const partialCount = g.contracts.filter(c => !c._isPaid && c._collected > 0).length;
            const unpaidCount  = g.contracts.filter(c => c._collected === 0).length;
            return {
                name: g.name,
                phone: g.phone,
                count: g.contracts.length,
                totalMonthly: g.contracts.reduce((s, c) => s + c.monthly_installment, 0),
                totalRemaining: g.contracts.reduce((s, c) => s + c.remaining_balance, 0),
                fullCount, partialCount, unpaidCount,
            };
        });
    }

    // يبني صفوف الجدول من قائمة صفوف عملاء (مُجمّعة بالفعل) — يخزّنها ويعرض أول صفحة
    function renderDueRows(customerRows) {
        _dueSorted = [...customerRows].sort((a, b) => b.totalRemaining - a.totalRemaining);
        _duePage = 1;
        renderDuePage();
    }

    function gotoDuePage(p) { _duePage = p; renderDuePage(); document.getElementById('dueByDayTable')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); }

    function renderDuePage() {
        const tbody = document.getElementById('dueByDayBody');
        tbody.innerHTML = '';

        const total = _dueSorted.length;
        const pages = Math.max(1, Math.ceil(total / DUE_PAGE_SIZE));
        if (_duePage > pages) _duePage = pages;
        const start = (_duePage - 1) * DUE_PAGE_SIZE;
        const pageRows = _dueSorted.slice(start, start + DUE_PAGE_SIZE);

        pageRows.forEach(row => {
            const initials = row.name ? row.name.charAt(0) : '?';
            const waLink = row.phone ? `<a href="https://wa.me/2${row.phone}?text=${encodeURIComponent('السلام عليكم، تذكير بموعد سداد القسط الشهري.')}" target="_blank" onclick="event.stopPropagation();" style="color:#25d366;font-size:1.1rem;" title="واتساب"><i class="fab fa-whatsapp"></i></a>` : '';

            // حالة السداد المجمّعة: الكل مدفوع / الكل لم يسدد / مختلط
            let statusBadge = '';
            if (row.unpaidCount === 0 && row.partialCount === 0) {
                statusBadge = `<span style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#15803d;border:1px solid #86efac;border-radius:20px;padding:4px 12px;font-size:.82rem;font-weight:800;"><i class="fa fa-check-circle"></i> الكل مدفوع</span>`;
            } else if (row.fullCount === 0 && row.partialCount === 0) {
                statusBadge = `<span style="display:inline-flex;align-items:center;gap:5px;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:20px;padding:4px 12px;font-size:.82rem;font-weight:800;"><span style='width:8px;height:8px;border-radius:50%;background:#dc2626;display:inline-block;animation:pulse 1.5s infinite;'></span> الكل لم يسدد</span>`;
            } else {
                const parts = [];
                if (row.fullCount > 0) parts.push(`${row.fullCount} كامل`);
                if (row.partialCount > 0) parts.push(`${row.partialCount} جزئي`);
                if (row.unpaidCount > 0) parts.push(`${row.unpaidCount} لسه`);
                statusBadge = `<span style="display:inline-flex;align-items:center;gap:5px;background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd;border-radius:20px;padding:4px 12px;font-size:.82rem;font-weight:800;"><i class="fa fa-chart-pie"></i> مختلط (${parts.join(' / ')})</span>`;
            }

            const phoneArg = String(row.phone || '').replace(/'/g, "\\'");
            const nameArg  = String(row.name  || '').replace(/'/g, "\\'");
            const isAllPaid = row.unpaidCount === 0 && row.partialCount === 0;

            tbody.innerHTML += `
            <tr class="clickable-row" style="${isAllPaid ? 'background:rgba(16,185,129,0.04);' : ''}" onclick="openStatement('${phoneArg}', '${nameArg}')">
                <td class="text-start">
                    <div class="d-flex align-items-center gap-2">
                        <div class="client-avatar" style="width:38px;height:38px;font-size:.9rem;background:${isAllPaid ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,var(--main-color),#60a5fa)'};">${initials}</div>
                        <div>
                            <strong class="d-block" style="font-size:.9rem;">${row.name}</strong>
                            <small class="text-muted" dir="ltr">${row.phone || '—'}</small>
                        </div>
                        ${waLink}
                    </div>
                </td>
                <td><span class="badge bg-secondary fw-bold">${row.count} عقد</span></td>
                <td class="fw-bold text-danger fs-6">${row.totalMonthly.toLocaleString('en-US')} ج</td>
                <td class="fw-bold" style="color:#7c3aed;">${row.totalRemaining.toLocaleString('en-US')} ج</td>
                <td>${statusBadge}</td>
                <td><button class="btn btn-sm btn-outline-dark fw-bold px-3" onclick="event.stopPropagation(); openStatement('${phoneArg}', '${nameArg}')"><i class="fa fa-table me-1"></i> كشف حساب</button></td>
            </tr>`;
        });

        // ── بناء شريط التنقل ──
        const pager = document.getElementById('duePager');
        if (total <= DUE_PAGE_SIZE) {
            pager.style.display = 'none';
        } else {
            pager.style.display = 'flex';
            const from = start + 1, to = Math.min(start + DUE_PAGE_SIZE, total);
            document.getElementById('duePagerInfo').innerText = `عرض ${from}–${to} من ${total} عميل`;

            const btns = document.getElementById('duePagerBtns');
            const mk = (label, page, opts = {}) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'btn btn-sm ' + (opts.active ? 'btn-dark' : 'btn-outline-secondary') + ' fw-bold';
                b.innerHTML = label;
                if (opts.disabled) b.disabled = true;
                else b.onclick = () => gotoDuePage(page);
                return b;
            };
            btns.innerHTML = '';
            btns.appendChild(mk('<i class="fa fa-angle-right"></i>', _duePage - 1, { disabled: _duePage === 1 }));
            // أرقام الصفحات (نافذة حول الصفحة الحالية)
            let s = Math.max(1, _duePage - 2), e = Math.min(pages, _duePage + 2);
            if (s > 1) { btns.appendChild(mk('1', 1)); if (s > 2) btns.appendChild(mk('…', 0, { disabled: true })); }
            for (let p = s; p <= e; p++) btns.appendChild(mk(String(p), p, { active: p === _duePage }));
            if (e < pages) { if (e < pages - 1) btns.appendChild(mk('…', 0, { disabled: true })); btns.appendChild(mk(String(pages), pages)); }
            btns.appendChild(mk('<i class="fa fa-angle-left"></i>', _duePage + 1, { disabled: _duePage === pages }));
        }
    }

    // يحسب حالة السداد لكل عقد (لازم قبل أي فلتر بالحالة) ثم يطبّق فلتر الحالة الحالي
    function applyStatusFilter(list) {
        const period = getPeriodSelection();
        list.forEach(inst => {
            const st = computePeriodStatus(inst, period);
            inst._collected = st.paid;
            inst._isPaid    = st.isFull;
        });
        if (currentStatusFilter === 'full')    return list.filter(i => i._isPaid);
        if (currentStatusFilter === 'partial') return list.filter(i => !i._isPaid && i._collected > 0);
        if (currentStatusFilter === 'unpaid')  return list.filter(i => i._collected === 0);
        return list;
    }

    // متوافقة للخلف: تُستخدم في الطباعة (تحسب الحالة + تطبّق فلتر الحالة)
    function prepareAndFilter(list) { return applyStatusFilter(list); }

    // يطبّق: بحث (اسم/هاتف) + نطاق يوم الاستحقاق + حالة السداد، ويعيد رسم الجدول والإحصائيات
    // 💡 الفلاتر بتحدد "مين العميل اللي هيظهر" (عنده عقد واحد مطابق على الأقل)، لكن صف العميل بيوري إجمالي كل عقوده
    function applyActiveFilters() {
        loadInstData();
        const fromDay = parseInt(document.getElementById('dueRangeFrom').value) || 1;
        const toDay   = parseInt(document.getElementById('dueRangeTo').value) || 31;
        const term    = (document.getElementById('activeSearch').value || '').trim().toLowerCase();

        // احسب حالة السداد لكل عقود كل العملاء (مش بس اللي في النطاق) — محتاجينها كاملة عشان تجميع صف العميل
        const period = getPeriodSelection();
        allInstData.forEach(inst => {
            const st = computePeriodStatus(inst, period);
            inst._collected = st.paid;
            inst._isPaid    = st.isFull;
        });

        // 1) فلتر نطاق يوم الاستحقاق + البحث
        let inRange = allInstData.filter(i => i.due_day >= fromDay && i.due_day <= toDay);
        if (term) {
            inRange = inRange.filter(i =>
                String(i.customer_name || '').toLowerCase().includes(term) ||
                String(i.customer_phone || '').toLowerCase().includes(term)
            );
        }
        updateDueStats(inRange);

        // 2) طبّق فلتر الحالة على مستوى العقد — بيحدد مين العملاء "المطابقين"
        let matchingContracts = inRange;
        if (currentStatusFilter === 'full')    matchingContracts = inRange.filter(i => i._isPaid);
        else if (currentStatusFilter === 'partial') matchingContracts = inRange.filter(i => !i._isPaid && i._collected > 0);
        else if (currentStatusFilter === 'unpaid')  matchingContracts = inRange.filter(i => i._collected === 0);

        const tbody = document.getElementById('dueByDayBody');
        if (matchingContracts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted fw-bold"><i class="fa fa-calendar-check fa-2x d-block mb-2" style="opacity:.4;"></i>لا يوجد عملاء لديهم عقود مطابقة للفلتر الحالي</td></tr>';
            const pager = document.getElementById('duePager');
            if (pager) pager.style.display = 'none';
            return;
        }

        // 3) اجمع كل عقود العملاء المطابقين (كل عقودهم، مش بس اللي طابقت الفلتر)
        const matchingKeys = new Set(matchingContracts.map(custKey));
        const allContractsOfMatchingCustomers = allInstData.filter(i => matchingKeys.has(custKey(i)));

        renderDueRows(groupContractsByCustomer(allContractsOfMatchingCustomers));
    }

    document.addEventListener('DOMContentLoaded', applyActiveFilters);
</script>

@if(session('error'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'خطأ!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'فهمت',
            confirmButtonColor: '#dc2626'
        });
        @if(session('open_modal'))
        var modal = document.getElementById('newContractModal');
        if(modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        @endif
    });
</script>
@endif
@php
    // 1. جلب العملاء من جدول العملاء الأساسي (نقدي)
    $list1 = \Illuminate\Support\Facades\DB::table('customers')
        ->select('name as customer_name', 'phone as customer_phone')
        ->whereNotNull('phone')->where('phone', '!=', '-')
        ->get();
        
    // 2. جلب العملاء من جدول العمليات والأقساط (كاحتياطي لضمان عدم ضياع أي عميل)
    $list2 = \Illuminate\Support\Facades\DB::table('installments')
        ->select('customer_name', 'customer_phone')
        ->whereNotNull('customer_phone')->where('customer_phone', '!=', '-')
        ->distinct()
        ->get();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    // دمج القائمتين معاً لضمان وجود كل الأرقام في شاشة واحدة
    const list1 = {!! json_encode($list1) !!};
    const list2 = {!! json_encode($list2) !!};
    const registeredCustomers = [...list1, ...list2];

    document.body.addEventListener('input', function (e) {
        // التحقق من أن الحقل هو حقل الهاتف بأي مسمى محتمل موجود في تصميمك
        if (e.target && (e.target.name === 'customer_phone' || e.target.name === 'phone' || e.target.id === 'customer_phone' || e.target.id === 'phone')) {
            const phoneInput = e.target;
            const typedPhone = phoneInput.value.trim();

            // العثور على النافذة الحالية (سواء كانت فورم صريح أو مودال منبثق)
            const currentContainer = phoneInput.closest('.modal') || phoneInput.closest('form') || document;
            
            // العثور على حقل الاسم بأي مسمى محتمل داخل النافذة
            const nameInput = currentContainer.querySelector('input[name="customer_name"], input[name="name"], input[id="customer_name"], input[id="name"]');
            
            if (!nameInput) return;

            // تفعيل الفحص إذا وصل الرقم لـ 11
            if (typedPhone.length >= 11) {
                // البحث في القائمة المدمجة عن العميل
                const matchedCustomer = registeredCustomers.find(c => c.customer_phone && c.customer_phone.trim() === typedPhone);
                
                if (matchedCustomer) {
                    // ✅ العميل مسجل: تثبيت الاسم، قفل الحقل وإظهار التنبيه
                    nameInput.value = matchedCustomer.customer_name;
                    nameInput.readOnly = true;
                    nameInput.style.backgroundColor = "#e9ecef"; // لون رمادي للقفل
                    nameInput.style.pointerEvents = "none"; // منع الماوس من التفاعل
                    
                    if (!phoneInput.dataset.alerted) {
                        alert(`⚠️ تنبيه:\nهذا الرقم مسجل مسبقاً باسم (${matchedCustomer.customer_name}) ولا يمكنك تعديل الاسم.`);
                        phoneInput.dataset.alerted = "true";
                    }
                } else {
                    // 🔓 العميل جديد: فتح الحقل
                    unlockField(nameInput, phoneInput);
                }
            } else {
                // 🔓 الرقم غير مكتمل: فتح الحقل
                unlockField(nameInput, phoneInput);
            }
        }
    });

    // دالة فتح الحقل (لو مسح الرقم أو كتب رقم جديد مش متسجل)
    function unlockField(nameInput, phoneInput) {
        nameInput.readOnly = false;
        nameInput.style.backgroundColor = "";
        nameInput.style.pointerEvents = "auto";
        delete phoneInput.dataset.alerted; // تصفير التنبيه ليظهر مجدداً لو أخطأ
    }
});
</script>

{{-- ═════════════════════════════════════════════════
     PRINT REPORTS — تقارير الأقساط الرسمية للطباعة
     ═════════════════════════════════════════════════ --}}
@php
    // بيانات العقود المنتهية
    $printCompletedData = collect($completedInstallments)->map(fn($i) => [
        'name'    => $i->customer_name,
        'phone'   => $i->customer_phone ?? '—',
        'product' => $i->product_name,
        'total'   => (float) $i->total_after_interest,
        'down'    => (float) $i->down_payment,
        'profit'  => (float) ($i->profit ?? 0),
        'date'    => \Carbon\Carbon::parse($i->created_at)->format('Y-m-d'),
    ])->values();

    // بيانات كل عميل (لطباعة كشف الحساب من المودال)
    $printCustomerData = [];
    foreach (collect($installments)->groupBy(fn($i) => filled($i->customer_phone) ? $i->customer_phone : 'n:'.$i->customer_name) as $phone => $custInsts) {
        $first = $custInsts->first();
        $key = 'grp_' . md5($phone ?? $first->customer_name);
        $printCustomerData[$key] = [
            'name'      => $first->customer_name,
            'phone'     => $first->customer_phone ?? '—',
            'contracts' => $custInsts->map(function($i) {
                return [
                    'id'             => $i->id,
                    'product'        => $i->product_name,
                    'cash_price'     => (float) ($i->cash_price ?? 0),
                    'device_price'   => (float) ($i->device_price ?? max(0, (float)($i->cash_price ?? 0) - (float)($i->extras_total ?? 0))),
                    'extras_total'   => (float) ($i->extras_total ?? 0),
                    'transport_cost' => (float) ($i->transport_cost ?? 0),
                    'installation_cost' => (float) ($i->installation_cost ?? 0),
                    'materials_cost' => (float) ($i->materials_cost ?? 0),
                    'down'           => (float) $i->down_payment,
                    'months'         => (int) $i->installment_months,
                    'interest'       => (float) ($i->interest_rate ?? 0),
                    'total'          => (float) $i->total_after_interest,
                    'monthly'        => (float) $i->monthly_installment,
                    'due_day'        => $i->due_day,
                    'remaining'      => (float) $i->remaining_balance,
                    'profit'         => (float) ($i->profit ?? 0),
                    'paid_total'     => (float) collect($i->payments)->sum('amount_paid'),
                    'payments'       => collect($i->payments)->sortBy('payment_date')->values()->map(fn($p) => [
    'date'   => \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d'),
    'amount' => (float) $p->amount_paid,
])->values(),
                ];
            })->values(),
        ];
    }
@endphp

<script>
const PRINT_COMPLETED = @json($printCompletedData);
const PRINT_CUSTOMERS = @json($printCustomerData);

const fmtN = n => fmtMoney(n);
const todayStr = new Date().toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' });

function getInstPrintStyles(landscape) {
    const pageSize = landscape ? 'A4 landscape' : 'A4';
    return `
        @page { size: ${pageSize}; margin: 8mm 7mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'IBM Plex Sans Arabic', 'Cairo', 'Tahoma', sans-serif;
            background: #fff; color: #0f172a;
            margin: 0; padding: 0;
            font-feature-settings: 'tnum';
            letter-spacing: -0.01em;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page { max-width: 100%; margin: 0; padding: 0; }

        .doc-header {
            display: flex; justify-content: space-between; align-items: center;
            padding-bottom: 8px; margin-bottom: 10px;
            border-bottom: 2px solid #0f172a;
        }
        .doc-header .brand h1 { margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1.2; }
        .doc-header .brand p { margin: 1px 0 0; color: #5a6478; font-size: 10px; font-weight: 500; }
        .doc-header .meta { text-align: left; font-size: 10px; }
        .doc-header .meta .doc-title {
            display: inline-block;
            background: #0f172a; color: #fff;
            padding: 4px 12px; border-radius: 4px;
            font-weight: 600; font-size: 11px; margin-bottom: 3px;
        }
        .doc-header .meta .doc-date { color: #5a6478; font-weight: 500; font-size: 10px; }

        .summary {
            display: grid; gap: 5px;
            margin-bottom: 10px;
        }
        .summary.cols-4 { grid-template-columns: repeat(4, 1fr); }
        .summary.cols-5 { grid-template-columns: repeat(5, 1fr); }
        .summary .box {
            border: 1px solid #e6ebf3; border-radius: 5px;
            padding: 5px 9px; background: #fafbfd;
            position: relative;
        }
        .summary .box::before {
            content: ''; position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 2px; background: #5a6478;
        }
        .summary .box.accent::before  { background: #4f46e5; }
        .summary .box.success::before { background: #059669; }
        .summary .box.danger::before  { background: #dc2626; }
        .summary .box.warning::before { background: #d97706; }
        .summary .box.violet::before  { background: #7c3aed; }
        .summary .box .label { font-size: 9px; color: #5a6478; font-weight: 500; margin-bottom: 1px; }
        .summary .box .val   { font-size: 12px; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; }

        .section-title {
            margin: 8px 0 4px; padding-bottom: 3px;
            font-size: 11px; font-weight: 700; color: #0f172a;
            border-bottom: 1.5px solid #e6ebf3;
            display: flex; justify-content: space-between; align-items: center;
        }
        .section-title small { font-size: 9px; color: #5a6478; font-weight: 500; }

        table.data {
            width: 100%; border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 9.5px;
            page-break-inside: auto;
        }
        table.data thead { background: #0f172a; color: #fff; display: table-header-group; }
        table.data tfoot { display: table-row-group; }
        table.data th { padding: 4px 4px; text-align: center; font-weight: 600; font-size: 9px; }
        table.data td {
            padding: 3px 4px; border-bottom: 1px solid #e6ebf3;
            text-align: center; vertical-align: middle;
            font-weight: 500; color: #0f172a;
            line-height: 1.3;
        }
        table.data tr { page-break-inside: avoid; }
        table.data tr:nth-child(even) td { background: #fafbfd; }
        table.data tfoot tr { background: #f1f4f9; }
        table.data tfoot td { padding: 5px 4px; font-weight: 700; font-size: 9.5px; border-top: 1.5px solid #0f172a; }
        table.data .text-start { text-align: right !important; }
        table.data .num-pos { color: #059669; font-weight: 700; }
        table.data .num-neg { color: #dc2626; font-weight: 700; }
        table.data .badge-pill {
            display: inline-block; padding: 1px 6px; border-radius: 999px;
            font-size: 8.5px; font-weight: 600;
        }
        .badge-paid   { background: #ecfdf5; color: #059669; }
        .badge-unpaid { background: #fef2f2; color: #dc2626; }
        .badge-active { background: #eef2ff; color: #4f46e5; }

        /* Customer Statement Table */
        table.statement {
            width: 100%; border-collapse: collapse;
            font-size: 10px; margin-bottom: 6px;
            page-break-inside: avoid;
        }
        table.statement td {
            border: 1px solid #e6ebf3;
            padding: 3.5px 9px;
            font-weight: 500;
            color: #0f172a;
            line-height: 1.3;
        }
        table.statement .lbl { background: #fafbfd; color: #5a6478; font-weight: 600; width: 50%; text-align: right; }
        table.statement .val { text-align: center; font-weight: 600; }
        /* قسم بيانات العقد العلوي — لون مميز عن باقي الداتا */
        table.statement tr:not([class]) td { background: #eef4ff !important; }
        table.statement tr:not([class]) .lbl { color: #1e40af; }
        table.statement .title-row td { background: #0f172a; color: #fff !important; font-weight: 700; font-size: 11px; }
        table.statement .summary-row .lbl { background: #4f46e5; color: #fff; font-weight: 700; }
        table.statement .summary-row .val { background: #eef2ff; color: #4f46e5; font-weight: 700; }
        table.statement .remaining-row .lbl { background: #dc2626; color: #fff; }
        table.statement .remaining-row .val { background: #fef2f2; color: #dc2626; }
        table.statement .pay-row .lbl { background: #fafbfd; color: #5a6478; font-weight: 500; font-family: monospace; font-size: 9.5px; }
        table.statement .pay-row .val { color: #059669; font-weight: 700; }

        /* جدول الأقساط المدمج (Compact payment grid) */
        table.pay-grid { width: 100%; border-collapse: collapse; font-size: 9px; margin: 4px 0 8px; }
        table.pay-grid th { background: #0f172a; color: #fff; padding: 3px 4px; font-size: 8.5px; font-weight: 600; border: 1px solid #1e293b; }
        table.pay-grid td { padding: 3px 4px; border: 1px solid #e6ebf3; text-align: center; font-weight: 500; line-height: 1.2; }
        table.pay-grid td.pay-paid    { background: #ecfdf5; color: #059669; font-weight: 700; }
        table.pay-grid td.pay-pending { background: #fef9f3; color: #92400e; }
        table.pay-grid td.pay-empty   { background: #f8fafc; color: #c5cbd6; }

        .footer {
            display: flex; justify-content: space-between;
            margin-top: 14px; padding-top: 6px;
            border-top: 1px dashed #d4dbe6;
            font-size: 9px; color: #5a6478;
        }
        .footer .sign-box { text-align: center; min-width: 130px; }
        .footer .sign-box .line {
            border-top: 1px solid #0f172a; margin-top: 18px; padding-top: 3px;
            font-weight: 600; color: #0f172a; font-size: 9.5px;
        }
        .footer .stamp { text-align: center; color: #8b95a9; font-weight: 500; }

        @media print { body { background: #fff; } .page-break { page-break-after: always; } }
    `;
}

function getInstHeader(title) {
    return `
        <div class="doc-header">
            <div class="brand">
                <h1>شركة الضبع</h1>
                <p>للتجارة وأنظمة التقسيط والمقاولات</p>
            </div>
            <div class="meta">
                <div class="doc-title">${title}</div>
                <div class="doc-date">${todayStr}</div>
            </div>
        </div>
    `;
}

function getInstFooter(left, right) {
    return `
        <div class="footer">
            <div class="sign-box"><div class="line">${left || 'موظف الحسابات'}</div></div>
            <div class="stamp">طُبع آلياً من نظام الضبع — ${new Date().toLocaleString('ar-EG')}</div>
            <div class="sign-box"><div class="line">${right || 'المدير المالي'}</div></div>
        </div>
    `;
}

function openInstPrint(html) {
    const blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const win  = window.open(url, '_blank', 'width=1100,height=850');
    if (win) {
        win.addEventListener('load', function () {
            setTimeout(function () {
                win.print();
                URL.revokeObjectURL(url);
            }, 500);
        });
    } else {
        URL.revokeObjectURL(url);
        alert('السماح بالنوافذ المنبثقة مطلوب لإتمام الطباعة.');
    }
}

// ──────────────────────────────────────────────
// 1. طباعة العقود النشطة
// ──────────────────────────────────────────────
window.printActiveInstallments = function() {
    loadInstData();
    const fromDay  = parseInt(document.getElementById('dueRangeFrom')?.value) || 0;
    const toDay    = parseInt(document.getElementById('dueRangeTo')?.value) || 0;
    const searchVal = (document.getElementById('activeSearch')?.value || '').trim();
    const term     = searchVal.toLowerCase();

    let byDueDay = (fromDay || toDay)
        ? allInstData.filter(i => i.due_day >= (fromDay || 1) && i.due_day <= (toDay || 31))
        : allInstData.slice();
    if (term) {
        byDueDay = byDueDay.filter(i =>
            String(i.customer_name || '').toLowerCase().includes(term) ||
            String(i.customer_phone || '').toLowerCase().includes(term)
        );
    }

    const list = prepareAndFilter(byDueDay);
    if (!list.length) { alert('لا توجد عقود مطابقة للفلتر الحالي للطباعة'); return; }

    // ── عنوان يوضّح الفلاتر المطبّقة ──
    let filterLabel = '';
    if (fromDay || toDay) filterLabel += ` — يوم الاستحقاق من ${fromDay || 1} إلى ${toDay || 31}`;
    const statusLabels = { full: 'دفعوا بالكامل', partial: 'سداد جزئي', unpaid: 'لم يسددوا' };
    if (currentStatusFilter !== 'all') filterLabel += ' — ' + statusLabels[currentStatusFilter];
    if (searchVal) filterLabel += ' — بحث: ' + searchVal;
    const reportTitle = 'سجل العقود النشطة' + filterLabel;

    let totalMonthly = 0, totalRemaining = 0, totalCollected = 0, paidCount = 0;
    const rows = [...list].sort((a, b) => a.due_day - b.due_day).map((inst, i) => {
        totalMonthly   += inst.monthly_installment;
        totalRemaining += inst.remaining_balance;
        totalCollected += inst._collected;
        if (inst._isPaid) paidCount++;
        const statusBadge = inst._isPaid
            ? `<span class="badge-pill badge-paid">دفع (${fmtN(inst._collected)} ج)</span>`
            : (inst._collected > 0
                ? `<span class="badge-pill" style="background:#eff6ff;color:#1d4ed8;">جزئي (${fmtN(inst._collected)} ج)</span>`
                : `<span class="badge-pill badge-unpaid">لم يسدد</span>`);
        return `<tr>
            <td>${i + 1}</td>
            <td class="text-start"><strong>${inst.customer_name}</strong></td>
            <td dir="ltr">${inst.customer_phone || '—'}</td>
            <td>يوم ${inst.due_day}</td>
            <td>${fmtN(inst.monthly_installment)} ج</td>
            <td class="num-neg">${fmtN(inst.remaining_balance)} ج</td>
            <td>${statusBadge}</td>
        </tr>`;
    }).join('');

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>العقود النشطة - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>${getInstPrintStyles(true)}</style>
        </head>
        <body>
            <div class="page">
                ${getInstHeader(reportTitle)}
                <div class="summary cols-5">
                    <div class="box accent"><div class="label">عدد العقود</div><div class="val">${list.length}</div></div>
                    <div class="box success"><div class="label">دفعوا بالكامل</div><div class="val">${paidCount}</div></div>
                    <div class="box"><div class="label">إجمالي الأقساط الشهرية</div><div class="val">${fmtN(totalMonthly)} ج</div></div>
                    <div class="box success"><div class="label">تم تحصيل</div><div class="val">${fmtN(totalCollected)} ج</div></div>
                    <div class="box danger"><div class="label">المتبقي بالخارج</div><div class="val">${fmtN(totalRemaining)} ج</div></div>
                </div>
                <div class="section-title">تفاصيل العقود <small>${list.length} عقد</small></div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th class="text-start">العميل</th><th>الهاتف</th>
                            <th>يوم الاستحقاق</th><th>القسط الشهري</th><th>المتبقي</th><th>حالة السداد</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-start" style="text-align:right; padding-right:14px;">الإجماليات:</td>
                            <td>${fmtN(totalMonthly)} ج</td>
                            <td class="num-neg">${fmtN(totalRemaining)} ج</td>
                            <td class="num-pos">${fmtN(totalCollected)} ج</td>
                        </tr>
                    </tfoot>
                </table>
                ${getInstFooter('موظف التحصيل', 'المدير المالي')}
            </div>
        </body></html>
    `;
    openInstPrint(html);
};

// ──────────────────────────────────────────────
// 2. طباعة العقود المنتهية
// ──────────────────────────────────────────────
window.printCompletedInstallments = function() {
    const data = PRINT_COMPLETED;
    if (!data.length) { alert('لا توجد عقود منتهية للطباعة'); return; }

    let totalValue = 0, totalProfit = 0;
    const rows = data.map((c, i) => {
        totalValue  += c.total;
        totalProfit += c.profit;
        return `<tr>
            <td>${i + 1}</td>
            <td class="text-start"><strong>${c.name}</strong></td>
            <td dir="ltr">${c.phone}</td>
            <td class="text-start">${c.product}</td>
            <td>${fmtN(c.total)} ج</td>
            <td>${fmtN(c.down)} ج</td>
            <td class="num-pos"><strong>${fmtN(c.profit)} ج</strong></td>
            <td dir="ltr">${c.date}</td>
        </tr>`;
    }).join('');

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>العقود المنتهية - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>${getInstPrintStyles(true)}</style>
        </head>
        <body>
            <div class="page">
                ${getInstHeader('سجل العقود المنتهية')}
                <div class="summary cols-4">
                    <div class="box accent"><div class="label">عدد العقود</div><div class="val">${data.length}</div></div>
                    <div class="box"><div class="label">إجمالي قيمة العقود</div><div class="val">${fmtN(totalValue)} ج</div></div>
                    <div class="box success"><div class="label">إجمالي الأرباح</div><div class="val">${fmtN(totalProfit)} ج</div></div>
                    <div class="box warning"><div class="label">متوسط ربح/عقد</div><div class="val">${fmtN(totalProfit / Math.max(1, data.length))} ج</div></div>
                </div>
                <div class="section-title">تفاصيل العقود المنتهية</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th class="text-start">العميل</th><th>الهاتف</th>
                            <th class="text-start">المنتج</th><th>إجمالي العقد</th>
                            <th>المقدم</th><th>الربح</th><th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-start" style="text-align:right; padding-right:14px;">الإجماليات:</td>
                            <td>${fmtN(totalValue)} ج</td>
                            <td>—</td>
                            <td class="num-pos">${fmtN(totalProfit)} ج</td>
                            <td>—</td>
                        </tr>
                    </tfoot>
                </table>
                ${getInstFooter('المحاسب', 'المدير المالي')}
            </div>
        </body></html>
    `;
    openInstPrint(html);
};

// ──────────────────────────────────────────────
// 4. طباعة كشف حساب عميل
// ──────────────────────────────────────────────
window.printCustomerStatement = function(groupKey) {
    const cust = PRINT_CUSTOMERS[groupKey];
    if (!cust) { alert('بيانات العميل غير متاحة'); return; }

    const contractsHtml = cust.contracts.map((c, idx) => {
        // ─── جدول الأقساط المدمج (grid) — يوفر صفحات بدلاً من صف لكل قسط ───
        // كل خلية = قسط واحد: مدفوع (أخضر) / قيد الانتظار (أصفر) / فارغ
        let payGrid = '';
        if (c.months > 0) {
            const cellsPerRow = 6;
            let cellsHtml = '';
            for (let i = 1; i <= c.months; i++) {
                const pay = c.payments[i - 1];
                if (pay) {
                    cellsHtml += `<td class="pay-paid"><div style="font-size:8px; opacity:0.7;">${i}</div><div style="font-weight:700;">${fmtN(pay.amount)}</div><div style="font-size:7.5px; opacity:0.65;" dir="ltr">${pay.date}</div></td>`;
                } else if (i === c.payments.length + 1) {
                    cellsHtml += `<td class="pay-pending"><div style="font-size:8px; opacity:0.7;">${i}</div><div style="font-weight:700;">${fmtN(c.monthly)}</div><div style="font-size:7.5px;">⏳ التالي</div></td>`;
                } else {
                    cellsHtml += `<td class="pay-empty"><div style="font-size:8px;">${i}</div><div style="font-weight:600;">${fmtN(c.monthly)}</div></td>`;
                }
                // كسر السطر بعد كل cellsPerRow خلية
                if (i % cellsPerRow === 0 && i < c.months) {
                    cellsHtml += '</tr><tr>';
                }
            }
            // ملء الخلايا المتبقية في الصف الأخير
            const remainder = c.months % cellsPerRow;
            if (remainder > 0) {
                for (let f = 0; f < cellsPerRow - remainder; f++) {
                    cellsHtml += '<td style="background:#fff; border:none;"></td>';
                }
            }
            payGrid = `
                <div style="font-size:9.5px; font-weight:600; color:#5a6478; margin: 4px 0 2px; display:flex; justify-content:space-between;">
                    <span>جدول السداد (${c.payments.length}/${c.months} قسط)</span>
                    <span style="font-size:8.5px;">
                        <span style="display:inline-block; width:10px; height:10px; background:#ecfdf5; border:1px solid #059669; vertical-align:middle; margin-left:3px;"></span>مدفوع
                        <span style="display:inline-block; width:10px; height:10px; background:#fef9f3; border:1px solid #92400e; vertical-align:middle; margin-right:8px; margin-left:3px;"></span>التالي
                        <span style="display:inline-block; width:10px; height:10px; background:#f8fafc; border:1px solid #c5cbd6; vertical-align:middle; margin-right:8px; margin-left:3px;"></span>منتظر
                    </span>
                </div>
                <table class="pay-grid"><tr>${cellsHtml}</tr></table>
            `;
        }

        return `
            ${cust.contracts.length > 1 ? `<div style="margin-top:10px; padding:5px 10px; background:#0f172a; color:#fff; font-weight:600; font-size:11px; border-radius:4px;">عقد رقم ${idx + 1}: ${c.product}</div>` : ''}
            <table class="statement">
                <tr class="title-row"><td colspan="2" style="text-align:center;">${c.product}</td></tr>
                <tr><td class="lbl">سعر الجهاز كاش</td><td class="val">${fmtN(c.device_price)} ج</td></tr>
                ${c.extras_total > 0 ? `
                    ${c.transport_cost > 0 ? `<tr><td class="lbl">— نقل</td><td class="val">${fmtN(c.transport_cost)} ج</td></tr>` : ''}
                    ${c.installation_cost > 0 ? `<tr><td class="lbl">— تركيب</td><td class="val">${fmtN(c.installation_cost)} ج</td></tr>` : ''}
                    ${c.materials_cost > 0 ? `<tr><td class="lbl">— خامات</td><td class="val">${fmtN(c.materials_cost)} ج</td></tr>` : ''}
                    <tr><td class="lbl">إجمالي بنود التركيب</td><td class="val">${fmtN(c.extras_total)} ج</td></tr>
                    <tr><td class="lbl">الإجمالي (جهاز + تركيب)</td><td class="val">${fmtN(c.cash_price)} ج</td></tr>
                ` : ''}
                <tr><td class="lbl">المقدم</td><td class="val">${fmtN(c.down)} ج</td></tr>
                <tr><td class="lbl">عدد الأشهر</td><td class="val">${c.months} شهر</td></tr>
                ${c.interest > 0 ? `<tr><td class="lbl">نسبة الفائدة</td><td class="val">${c.interest}%</td></tr>` : ''}
                <tr><td class="lbl">إجمالي بعد الفوائد</td><td class="val">${fmtN(c.total)} ج</td></tr>
                <tr><td class="lbl">القسط الشهري</td><td class="val">${fmtN(c.monthly)} ج</td></tr>
                <tr><td class="lbl">يوم السداد الشهري</td><td class="val">يوم ${c.due_day}</td></tr>
                <tr class="summary-row"><td class="lbl">إجمالي المدفوع</td><td class="val">${fmtN(c.paid_total)} ج</td></tr>
                <tr class="summary-row remaining-row"><td class="lbl">إجمالي المتبقي</td><td class="val">${fmtN(c.remaining)} ج</td></tr>
            </table>
            ${payGrid}
        `;
    }).join('');

    const totalContractValue = cust.contracts.reduce((s, c) => s + c.total, 0);
    const totalPaid          = cust.contracts.reduce((s, c) => s + c.paid_total, 0);
    const totalRemaining     = cust.contracts.reduce((s, c) => s + c.remaining, 0);

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>كشف حساب ${cust.name} - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>${getInstPrintStyles(false)}</style>
        </head>
        <body>
            <div class="page">
                ${getInstHeader('كشف حساب عميل')}

                <div style="background:#fafbfd; border:1px solid #e6ebf3; border-radius:6px; padding:7px 12px; margin-bottom:8px; display:grid; grid-template-columns:repeat(4, 1fr); gap:8px;">
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">اسم العميل</div>
                        <div style="font-size:12px; font-weight:700; color:#0f172a;">${cust.name}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">رقم الهاتف</div>
                        <div style="font-size:11px; font-weight:600; color:#0f172a;" dir="ltr">${cust.phone}</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">عدد العقود</div>
                        <div style="font-size:12px; font-weight:700; color:#4f46e5;">${cust.contracts.length} عقد</div>
                    </div>
                    <div>
                        <div style="font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px;">المتبقي الإجمالي</div>
                        <div style="font-size:13px; font-weight:700; color:#dc2626;">${fmtN(totalRemaining)} ج</div>
                    </div>
                </div>

                ${contractsHtml}

                ${cust.contracts.length > 1 ? `
                <div class="section-title">الإجمالي العام للعميل</div>
                <table class="statement">
                    <tr><td class="lbl">إجمالي قيمة العقود</td><td class="val">${fmtN(totalContractValue)} ج</td></tr>
                    <tr class="summary-row"><td class="lbl">إجمالي المسدد</td><td class="val">${fmtN(totalPaid)} ج</td></tr>
                    <tr class="summary-row remaining-row"><td class="lbl">المتبقي بالخارج</td><td class="val">${fmtN(totalRemaining)} ج</td></tr>
                </table>
                ` : ''}

                ${getInstFooter('توقيع العميل', 'موظف التحصيل')}
            </div>
        </body></html>
    `;
    openInstPrint(html);
};
</script>
</body>
</html>
