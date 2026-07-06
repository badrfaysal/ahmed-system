<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>نظام المخازن والباتشات - شركة الضبع</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ══════════════════════════════════════════════
       INVENTORY — World-class Design System (Light + Dark)
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
        --input-bg:     #ffffff;

        --accent:       #4f46e5;
        --accent-2:     #6366f1;
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

        /* legacy aliases preserved for JS-targeted classes */
        --blue:         #4f46e5;
        --blue-light:   #eef2ff;
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
        --input-bg:     #0f1828;
        --accent-bg:    rgba(99,102,241,0.13);
        --success-bg:   rgba(5,150,105,0.13);
        --danger-bg:    rgba(220,38,38,0.13);
        --warning-bg:   rgba(217,119,6,0.13);
        --info-bg:      rgba(2,132,199,0.13);
        --violet-bg:    rgba(124,58,237,0.13);
        --blue-light:   rgba(99,102,241,0.13);
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.4);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.35);
        --shadow-lg: 0 12px 32px rgba(0,0,0,0.5);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        background: var(--bg);
        color: var(--text-main);
        font-family: 'IBM Plex Sans Arabic', 'Cairo', sans-serif;
        font-feature-settings: 'tnum' 1;
        overflow-x: hidden;
        letter-spacing: -0.01em;
        transition: background var(--t), color var(--t);
    }
    .main-content {
        margin-right: 260px;
        padding: 28px 32px 40px;
        min-height: 100vh;
        max-width: 1700px;
    }
    @media (max-width: 1200px) { .main-content { padding: 22px 18px 30px; } }

    /* ── الهيدر ── */
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        gap: 18px;
        flex-wrap: wrap;
    }
    .title h1 {
        font-size: 1.6rem;
        font-weight: 600;
        margin: 0 0 4px;
        color: var(--text-main);
        letter-spacing: -0.02em;
    }
    .title p {
        color: var(--text-muted);
        font-size: 0.88rem;
        margin: 0;
        font-weight: 400;
    }
    .actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

    /* ── الأزرار الأساسية ── */
    .btn-custom {
        border: none;
        padding: 10px 18px;
        border-radius: var(--r-sm);
        cursor: pointer;
        font-size: 0.88rem;
        font-weight: 600;
        font-family: inherit;
        transition: var(--t);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: var(--shadow-xs);
        letter-spacing: -0.01em;
        text-decoration: none;
    }
    .btn-custom:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
    .btn-custom:active { transform: translateY(0); }

    .btn-primary-custom {
        background: var(--accent);
        color: #fff;
    }
    .btn-primary-custom:hover { background: var(--accent-2); color: #fff; }

    .btn-success-custom {
        background: var(--success);
        color: #fff;
    }
    .btn-success-custom:hover { background: #047857; color: #fff; }

    .btn-danger-outline {
        border: 1px solid var(--border-2);
        color: var(--danger);
        background: var(--surface);
    }
    .btn-danger-outline:hover { border-color: var(--danger); background: var(--danger-bg); }

    .theme-toggle {
        background: var(--surface);
        border: 1px solid var(--border);
        color: var(--text-muted);
        width: 42px;
        height: 42px;
        border-radius: var(--r-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--t);
        font-size: 1.05rem;
    }
    .theme-toggle:hover { color: var(--accent); border-color: var(--accent); }

    /* ── الفلاتر ── */
    .filter-bar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        padding: 12px 14px;
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: var(--shadow-xs);
    }
    .filter-bar .search-field {
        flex: 1 1 280px;
        min-width: 220px;
        position: relative;
    }
    .filter-bar .search-field i {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        color: var(--text-soft);
        font-size: 0.85rem;
        pointer-events: none;
    }
    .filter-bar input.form-control,
    .filter-bar .filter-field input {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--r-sm);
        padding: 9px 12px 9px 36px;
        font-size: 0.88rem;
        font-weight: 500;
        color: var(--text-main);
        transition: var(--t);
        font-family: inherit;
        width: 100%;
    }
    .filter-bar input.form-control::placeholder { color: var(--text-soft); }
    .filter-bar input.form-control:focus,
    .filter-bar .filter-field input:focus {
        outline: none;
        border-color: var(--accent);
        background: var(--surface);
        box-shadow: 0 0 0 3px var(--accent-bg);
    }
    .filter-bar .search-field input { padding-right: 36px; padding-left: 12px; }
    .filter-bar .filter-field {
        flex: 0 0 180px;
        min-width: 160px;
        position: relative;
    }
    .filter-bar .filter-field i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: var(--text-soft);
        font-size: 0.75rem;
        pointer-events: none;
    }
    .filter-bar button {
        background: var(--text-main);
        color: var(--surface);
        border: none;
        border-radius: var(--r-sm);
        padding: 9px 22px;
        font-size: 0.86rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--t);
        font-family: inherit;
    }
    .filter-bar button:hover { background: var(--accent); }

    /* ── KPI Cards ── */
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }
    .stat-card {
        background: var(--surface);
        padding: 18px 20px;
        border-radius: var(--r-md);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-xs);
        transition: var(--t);
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        width: 3px;
        background: var(--text-soft);
    }
    .stat-card.blue::before   { background: var(--accent); }
    .stat-card.red::before    { background: var(--danger); }
    .stat-card.green::before  { background: var(--success); }
    .stat-card.orange::before { background: var(--warning); }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--border-2);
    }
    .stat-card h3 {
        color: var(--text-muted);
        font-size: 0.76rem;
        margin-bottom: 8px;
        font-weight: 500;
        letter-spacing: 0.02em;
        text-transform: none;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .stat-card .value {
        font-size: 1.55rem;
        font-weight: 600;
        color: var(--text-main);
        line-height: 1.15;
        letter-spacing: -0.02em;
    }
    .stat-card .value .fs-6 {
        font-size: 0.78rem !important;
        font-weight: 400;
        color: var(--text-soft);
    }

    /* ── Tabs ── */
    .nav-pills {
        background: var(--surface);
        padding: 5px;
        border-radius: var(--r-md);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-xs);
        margin-bottom: 18px;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 3px;
    }
    .nav-pills .nav-link {
        background: transparent;
        color: var(--text-muted);
        border-radius: var(--r-sm);
        padding: 9px 16px;
        font-size: 0.86rem;
        font-weight: 500;
        transition: var(--t);
        border: none;
        cursor: pointer;
        font-family: inherit;
    }
    .nav-pills .nav-link:hover {
        background: var(--hover-bg);
        color: var(--text-main);
    }
    .nav-pills .nav-link.active {
        background: var(--text-main);
        color: var(--surface);
        box-shadow: var(--shadow-sm);
    }

    /* ── Tables ── */
    .table-box {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        padding: 0;
        box-shadow: var(--shadow-xs);
        overflow: hidden;
    }
    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .custom-table thead {
        background: var(--surface-2);
    }
    .custom-table th {
        padding: 12px 14px;
        text-align: center;
        font-size: 0.74rem;
        color: var(--text-muted);
        font-weight: 600;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
        letter-spacing: 0.02em;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .custom-table td {
        padding: 14px;
        border-bottom: 1px solid var(--border);
        font-size: 0.88rem;
        font-weight: 500;
        vertical-align: middle;
        text-align: center;
        color: var(--text-main);
        cursor: pointer;
        transition: background var(--t);
    }
    .custom-table tbody tr:last-child td { border-bottom: none; }
    .custom-table tbody tr:hover td { background: var(--hover-bg); }
    .custom-table .text-start { text-align: start !important; }

    .custom-table .fw-bold { font-weight: 600 !important; }
    .custom-table .fw-black { font-weight: 700 !important; }

    .batch-id {
        background: var(--accent-bg);
        color: var(--accent);
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        font-family: 'JetBrains Mono', 'Menlo', monospace;
        letter-spacing: -0.02em;
    }

    /* 🏷️ بادج "مرتجع عميل" — يميّز البضاعة الراجعة من فسخ العقود */
    .badge-return {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fcd34d;
        padding: 1px 8px;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 700;
        margin-inline-start: 6px;
        vertical-align: middle;
    }

    /* ── Action Buttons ── */
    .btn-action-sm {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1px solid transparent;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: var(--t);
        cursor: pointer;
        font-size: 0.78rem;
        background: var(--danger);
    }
    .btn-action-sm:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); filter: brightness(1.08); }
    .btn-sell  { background: var(--success); }
    .btn-ret   { background: var(--violet); }
    .btn-edit  { background: var(--text-soft); }
    .btn-trans { background: var(--info); }
    .btn-inv   { background: var(--warning); }
    .btn-del   { background: var(--danger); }

    /* ── Modals ── */
    .modal-content {
        border-radius: var(--r-lg);
        border: 1px solid var(--border);
        background: var(--surface);
        box-shadow: var(--shadow-lg);
    }
    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 16px 22px;
    }
    .modal-title {
        font-weight: 600;
        color: var(--text-main);
        font-size: 1.02rem;
        letter-spacing: -0.01em;
    }
    .modal-body { color: var(--text-main); padding: 22px; }
    .modal-footer { border-top: 1px solid var(--border); padding: 14px 22px; }

    .form-control, .form-select {
        background: var(--input-bg);
        color: var(--text-main);
        border: 1px solid var(--border);
        border-radius: var(--r-sm);
        font-weight: 500;
        padding: 10px 14px;
        font-size: 0.9rem;
        font-family: inherit;
        transition: var(--t);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--accent);
        background: var(--input-bg);
        color: var(--text-main);
        box-shadow: 0 0 0 3px var(--accent-bg);
        outline: none;
    }
    .form-label { font-weight: 500; color: var(--text-muted); font-size: 0.82rem; }

    .pay-type-card {
        border: 1.5px solid var(--border);
        border-radius: var(--r-sm);
        padding: 12px;
        cursor: pointer;
        text-align: center;
        transition: var(--t);
        background: var(--surface);
    }
    .pay-type-card:has(input:checked) {
        border-color: var(--accent);
        background: var(--accent-bg);
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-lbl {
        font-size: 0.82rem;
        color: var(--text-muted);
        font-weight: 500;
    }
    .detail-val {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-main);
        text-align: left;
    }

    /* ── Supplier Cards ── */
    .sup-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        padding: 18px;
        cursor: pointer;
        transition: var(--t);
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-xs);
    }
    .sup-card:hover {
        border-color: var(--accent);
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    .sup-card .sup-icon {
        width: 44px;
        height: 44px;
        background: var(--accent-bg);
        color: var(--accent);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 12px;
    }
    .sup-card h5 {
        font-size: 0.98rem;
        font-weight: 600;
        margin: 0 0 6px;
        color: var(--text-main);
        letter-spacing: -0.01em;
    }

    /* ── Autocomplete ── */
    .ac-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-sm);
        box-shadow: var(--shadow-lg);
        z-index: 9999;
        max-height: 220px;
        overflow-y: auto;
        display: none;
        padding: 4px;
        text-align: right;
    }
    .ac-item {
        padding: 9px 12px;
        cursor: pointer;
        font-size: 0.86rem;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-main);
        transition: var(--t);
    }
    .ac-item:hover { background: var(--accent-bg); color: var(--accent); }

    /* ── Section headers inside tabs ── */
    .tab-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }
    .tab-section-head h4 {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        letter-spacing: -0.01em;
    }
    .tab-section-head h4 i { color: var(--accent); }

    /* ── Empty states ── */
    .empty-row {
        padding: 60px 20px;
        text-align: center;
        color: var(--text-soft);
        font-weight: 500;
    }
    .empty-row i { display: block; font-size: 2.2rem; margin-bottom: 10px; opacity: 0.4; }

    /* ── Badges (inside tables) ── */
    .badge.bg-info,
    .badge.bg-success,
    .badge.bg-warning,
    .badge.bg-danger {
        font-weight: 500;
        font-size: 0.72rem;
        padding: 4px 10px;
        border-radius: 999px;
    }

    /* ── Scrollbar ── */
    .table-responsive::-webkit-scrollbar,
    .ac-dropdown::-webkit-scrollbar { height: 6px; width: 6px; }
    .table-responsive::-webkit-scrollbar-thumb,
    .ac-dropdown::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 999px; }

    /* ── Alerts ── */
    .alert {
        border-radius: var(--r-md);
        border: 1px solid var(--border);
        font-size: 0.88rem;
        padding: 12px 16px;
        box-shadow: var(--shadow-xs);
    }
    .alert-success { background: var(--success-bg); color: var(--success); border-color: rgba(5,150,105,0.2); }
    .alert-danger  { background: var(--danger-bg);  color: var(--danger);  border-color: rgba(220,38,38,0.2); }

    /* ══════════════════════════════════════════════
       MODAL POLISH — Buy/Sell forms
       ══════════════════════════════════════════════ */
    #buyStockModal .modal-header,
    #sellModal .modal-header {
        background: var(--surface) !important;
        border-bottom: 1px solid var(--border) !important;
        padding: 18px 24px;
    }
    #buyStockModal .modal-title,
    #sellModal .modal-title {
        color: var(--text-main) !important;
        font-weight: 600 !important;
        font-size: 1rem !important;
        letter-spacing: -0.01em;
    }
    #buyStockModal .modal-title i { color: var(--accent) !important; }
    #sellModal   .modal-title i { color: var(--success) !important; }

    #buyStockModal .modal-body,
    #sellModal .modal-body {
        background: var(--surface) !important;
        padding: 22px 24px !important;
    }
    #buyStockModal .modal-footer,
    #sellModal .modal-footer {
        background: var(--surface) !important;
        border-top: 1px solid var(--border) !important;
        padding: 14px 24px !important;
    }

    /* Section blocks inside modal */
    #buyStockModal .modal-body > .row.mb-4,
    #buyStockModal .modal-body > .p-3,
    #sellModal .modal-body > .row.mb-4,
    #sellModal .modal-body > .p-3 {
        background: var(--surface-2) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--r-md) !important;
        box-shadow: none !important;
    }
    #buyStockModal .modal-body label,
    #sellModal .modal-body label {
        font-weight: 500 !important;
        color: var(--text-muted) !important;
        font-size: 0.8rem !important;
        margin-bottom: 6px !important;
    }

    /* Tables inside modals */
    #buyStockModal #purchaseTable,
    #sellModal #sellTable {
        margin-bottom: 0;
    }
    #buyStockModal #purchaseTable thead,
    #sellModal #sellTable thead {
        background: var(--surface-2) !important;
        border-bottom: 1px solid var(--border) !important;
    }
    #buyStockModal #purchaseTable thead th,
    #sellModal #sellTable thead th {
        color: var(--text-muted) !important;
        font-weight: 600 !important;
        font-size: 0.74rem !important;
        text-transform: none;
        padding: 11px 8px !important;
        border-color: var(--border) !important;
    }
    #buyStockModal #purchaseTable td,
    #sellModal #sellTable td {
        border-color: var(--border) !important;
        padding: 6px !important;
    }
    #buyStockModal #purchaseTable input,
    #sellModal #sellTable input {
        border: 1px solid var(--border) !important;
        background: var(--surface) !important;
        font-weight: 500 !important;
        font-size: 0.86rem !important;
        border-radius: 6px !important;
        padding: 8px 10px !important;
    }
    #buyStockModal #purchaseTable input:focus,
    #sellModal #sellTable input:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2px var(--accent-bg) !important;
    }

    /* Remove harsh "border-primary/danger/success" inside modal inputs */
    #buyStockModal .modal-body input.border-primary,
    #buyStockModal .modal-body input.border-danger,
    #buyStockModal .modal-body input.border-success,
    #buyStockModal .modal-body select.border-primary,
    #buyStockModal .modal-body select.border-danger,
    #sellModal .modal-body input.border-primary,
    #sellModal .modal-body input.border-danger,
    #sellModal .modal-body input.border-success,
    #sellModal .modal-body input.border-warning,
    #sellModal .modal-body select.border-primary,
    #sellModal .modal-body select.border-danger,
    #sellModal .modal-body select.border-success {
        border: 1px solid var(--border) !important;
        background: var(--surface) !important;
        color: var(--text-main) !important;
        border-radius: var(--r-sm) !important;
    }

    /* Pay-type cards refined */
    #buyStockModal .pay-type-card,
    #sellModal .pay-type-card {
        background: var(--surface) !important;
        border: 1.5px solid var(--border) !important;
        padding: 14px 10px !important;
        border-radius: var(--r-sm) !important;
        transition: var(--t);
    }
    #buyStockModal .pay-type-card:has(input:checked),
    #sellModal .pay-type-card:has(input:checked) {
        border-color: var(--accent) !important;
        background: var(--accent-bg) !important;
    }
    #buyStockModal .pay-type-card label i,
    #sellModal .pay-type-card label i {
        font-size: 1.2rem !important;
        margin-bottom: 4px !important;
    }
    #buyStockModal .pay-type-card label,
    #sellModal .pay-type-card label {
        font-size: 0.84rem !important;
        font-weight: 500 !important;
        color: var(--text-main) !important;
        cursor: pointer;
    }

    /* Modal totals box */
    #total_purchase_cost, #sell_total {
        font-feature-settings: 'tnum';
        letter-spacing: -0.01em;
    }

    /* Submit buttons */
    #buyStockModal .modal-footer .btn-primary[type="submit"] {
        background: var(--accent) !important;
        border: none !important;
        color: #fff !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        padding: 12px 24px !important;
        border-radius: var(--r-sm) !important;
    }
    #buyStockModal .modal-footer .btn-primary[type="submit"]:hover {
        background: var(--accent-2) !important;
    }
    #sellModal .modal-footer .btn-success[type="submit"] {
        background: var(--success) !important;
        border: none !important;
        color: #fff !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        padding: 12px 24px !important;
        border-radius: var(--r-sm) !important;
    }
    #sellModal .modal-footer .btn-success[type="submit"]:hover {
        background: #047857 !important;
    }

    /* Add-row buttons inside modals */
    #buyStockModal .btn.rounded-pill,
    #sellModal .btn.rounded-pill {
        border-radius: var(--r-sm) !important;
        font-weight: 500 !important;
        font-size: 0.86rem !important;
        padding: 8px 16px !important;
    }

    /* Delete-row buttons inside modal tables */
    #buyStockModal #purchaseTable .btn-danger,
    #sellModal #sellTable .btn-danger {
        background: transparent !important;
        color: var(--danger) !important;
        border: 1px solid var(--border) !important;
        padding: 8px 10px !important;
        border-radius: 6px !important;
    }
    #buyStockModal #purchaseTable .btn-danger:hover,
    #sellModal #sellTable .btn-danger:hover {
        background: var(--danger-bg) !important;
        border-color: var(--danger) !important;
    }

    /* Commission strip in sell modal */
    #sellModal .modal-body > div.mt-3.p-2 {
        background: var(--surface-2) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--r-sm) !important;
        padding: 12px !important;
    }

    /* ══════════════════════════════════════════════
       PRINT BUTTONS
       ══════════════════════════════════════════════ */
    .btn-print {
        background: var(--surface);
        border: 1px solid var(--border);
        color: var(--text-muted);
        padding: 7px 14px;
        border-radius: var(--r-sm);
        font-size: 0.82rem;
        font-weight: 500;
        font-family: inherit;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: var(--t);
    }
    .btn-print:hover {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    /* ════════════════════════════════════════
       MOBILE RESPONSIVE
       ════════════════════════════════════════ */
    @media (max-width: 991px) {
        .main-content { margin-right: 0 !important; width: 100% !important; padding: 70px 16px 30px !important; }

        /* header */
        .topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
        .title h1 { font-size: 1.2rem; }
        .actions { flex-wrap: wrap; gap: 6px; }
        .btn-custom, .btn-print { padding: 8px 12px; font-size: 0.82rem; }

        /* filter bar: wrap to rows */
        .filter-bar { flex-direction: column; align-items: stretch; gap: 8px; }
        .filter-bar .search-field { min-width: 100%; flex: 1 1 100%; }
        .filter-bar .filter-field { flex: 1 1 calc(50% - 4px); min-width: 0; }
        .filter-bar button { width: 100%; justify-content: center; }

        /* stat cards: 2 per row */
        .cards { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-card { padding: 14px; }
        .stat-card .value { font-size: 1.25rem; }

        /* nav tabs: horizontal scroll */
        .nav-pills {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            padding-bottom: 6px;
        }
        .nav-pills .nav-item { flex-shrink: 0; }
        .nav-pills .nav-link { padding: 8px 12px; font-size: 0.8rem; white-space: nowrap; }

        /* action buttons in table rows */
        .btn-action-sm { width: 26px; height: 26px; font-size: 0.72rem; }

        /* modals */
        .modal-dialog { margin: 8px !important; max-width: calc(100vw - 16px) !important; }
    }

    @media (max-width: 576px) {
        .cards { grid-template-columns: 1fr 1fr; gap: 8px; }
        .stat-card { padding: 10px 12px; }
        .stat-card h3 { font-size: 0.7rem; }
        .stat-card .value { font-size: 1.1rem; }

        .filter-bar .filter-field { flex: 1 1 100%; }

        .custom-table th, .custom-table td { padding: 9px 8px; font-size: 0.78rem; }
    }
  </style>
</head>
<body>

@include('sidebar')

@php
    // 💡 helper آمن لـ old() يمنع خطأ htmlspecialchars لو رجع array
    if (!function_exists('safeOld')) {
        function safeOld($key, $default = '') {
            $val = old($key, $default);
            return is_array($val) ? $default : $val;
        }
    }
    $safeItems = $inventoryItems instanceof \Illuminate\Database\Query\Builder ? $inventoryItems->get() : $inventoryItems;
    $mappedItems = [];
    foreach($safeItems as $i) { $mappedItems[] = ['id'=>$i->id, 'name'=>$i->product_name??'', 'cat'=>$i->category??'عام', 'sup'=>$i->supplier_name??'عام', 'pp'=>$i->purchase_price??0, 'sp'=>$i->selling_price??0, 'qty'=>$i->remaining_quantity??0]; }
    
    $jsCategories = array_values($categories);
    $jsSuppliers  = array_values($allSuppliersList);

    $supplierDebts = \Illuminate\Support\Facades\DB::table('company_debts')
        ->where('remaining_balance', '>', 0)
        ->selectRaw('creditor_name, SUM(remaining_balance) as total_debt')
        ->groupBy('creditor_name')
        ->pluck('total_debt', 'creditor_name')
        ->toArray();

    // ── بيانات الطباعة (JSON للجافاسكريبت) ──
    $printInventoryData = [
        'stats' => [
            'total_items'      => $total_items,
            'total_cost_value' => $total_cost_value,
            'total_sell_value' => $total_sell_value,
            'potential_profit' => $potential_profit,
        ],
        'main' => collect($main_store_items)->map(fn($i) => [
            'id' => $i->id, 'name' => $i->product_name, 'category' => $i->category,
            'supplier' => $i->supplier_name, 'qty' => (float)$i->remaining_quantity,
            'pp' => (float)$i->purchase_price, 'sp' => (float)$i->selling_price,
        ])->values(),
        'sub' => collect($sub_store_items)->map(fn($i) => [
            'id' => $i->id, 'name' => $i->product_name, 'category' => $i->category,
            'supplier' => $i->supplier_name, 'qty' => (float)$i->remaining_quantity,
            'pp' => (float)$i->purchase_price, 'sp' => (float)$i->selling_price,
        ])->values(),
    ];

    $printSalesData = collect($sales_log)->map(fn($s) => [
        'id'        => $s->id,
        'date'      => \Carbon\Carbon::parse($s->created_at)->format('Y-m-d'),
        'customer'  => $s->customer_name,
        'product'   => $s->product_name,
        'months'    => (int)($s->installment_months ?? 0),
        'total'     => (float)($s->total_after_interest ?? $s->cash_price ?? 0),
        'profit'    => (float)($s->profit ?? 0),
    ])->values();
@endphp

<div class="main-content">
    
    @if($errors->any())
        <div class="alert alert-danger fw-bold rounded-4 p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-black mb-2"><i class="fa fa-exclamation-triangle me-2"></i>خطأ في البيانات:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif
    @if(session('success')) <div class="alert alert-success fw-bold rounded-3"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error')) 
        <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0">
            <i class="fa fa-exclamation-triangle me-2"></i>
            {{ is_array(session('error')) ? implode(' - ', session('error')) : session('error') }}
        </div> 
    @endif
    <div class="topbar">
        <div class="title">
            <h1>إدارة المخزون والمشتريات</h1>
            <p>مراقبة الأصناف والموردين وحركة الصرف والتوريد</p>
        </div>
        <div class="actions">
            <button id="theme-toggle" class="theme-toggle" title="تبديل الوضع"><i class="fa fa-moon"></i></button>
            <button class="btn-print" onclick="printInventoryState()" title="طباعة حالة المخزن كاملة"><i class="fa fa-print"></i> طباعة المخزن</button>
            <a href="?low_stock=1" class="btn-custom btn-danger-outline"><i class="fa fa-triangle-exclamation"></i> النواقص <strong style="margin-inline-start:4px;">{{ $low_stock_count ?? 0 }}</strong></a>
            <button class="btn-custom btn-success-custom" data-bs-toggle="modal" data-bs-target="#sellModal"><i class="fa fa-cart-arrow-down"></i> إذن صرف</button>
            <button class="btn-custom btn-primary-custom" data-bs-toggle="modal" data-bs-target="#buyStockModal"><i class="fa fa-plus"></i> توريد بضاعة</button>
            @if($search || $category || $supplier || $lowStock) <a href="{{ route('inventory.index') }}" class="btn-custom" style="background: var(--hover-bg); color: var(--text-muted);"><i class="fa fa-times"></i> مسح الفلتر</a> @endif
        </div>
    </div>

    <form class="filter-bar" method="GET">
        <div class="search-field">
            <i class="fa fa-search"></i>
            <input type="text" name="search" class="form-control" placeholder="ابحث باسم الصنف، الباتش، أو العميل..." value="{{ $search }}">
        </div>
        <div class="filter-field">
            <i class="fa fa-chevron-down"></i>
            <input type="text" name="category" id="filter_cat" placeholder="كل الفئات" value="{{ $category }}" autocomplete="off">
            <div class="ac-dropdown"></div>
        </div>
        <div class="filter-field">
            <i class="fa fa-chevron-down"></i>
            <input type="text" name="supplier" id="filter_sup" placeholder="كل الموردين" value="{{ $supplier }}" autocomplete="off">
            <div class="ac-dropdown"></div>
        </div>
        <button type="submit"><i class="fa fa-filter"></i> تصفية</button>
    </form>

    <div class="cards">
        <div class="stat-card blue">
            <h3><i class="fa fa-layer-group"></i> إجمالي الدفعات</h3>
            <div class="value">{{ fmtMoney($total_items) }} <span class="fs-6">دفعة</span></div>
        </div>
        <div class="stat-card red">
            <h3><i class="fa fa-coins"></i> قيمة المخزن (التكلفة)</h3>
            <div class="value">{{ fmtMoney($total_cost_value) }} <span class="fs-6">ج.م</span></div>
        </div>
        <div class="stat-card green">
            <h3><i class="fa fa-money-bill-trend-up"></i> القيمة البيعية المتوقعة</h3>
            <div class="value">{{ fmtMoney($total_sell_value) }} <span class="fs-6">ج.م</span></div>
        </div>
        <div class="stat-card orange">
            <h3><i class="fa fa-bullseye"></i> الربح المستهدف</h3>
            <div class="value">{{ fmtMoney($potential_profit) }} <span class="fs-6">ج.م</span></div>
        </div>
    </div>

    <ul class="nav nav-pills" id="invTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-main"><i class="fa fa-warehouse me-1"></i> المخزن الرئيسي</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-sub"><i class="fa fa-boxes-stacked me-1"></i> المخزن الفرعي</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-purchases"><i class="fa fa-truck-loading me-1"></i> الموردين</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-sales"><i class="fa fa-receipt me-1"></i> سجل المبيعات</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-returns-cust"><i class="fa fa-undo me-1"></i> مرتجعات العملاء</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-returns-sup"><i class="fa fa-reply-all me-1"></i> مرتجعات الموردين</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-inv-reports" id="btn-inv-reports"><i class="fa fa-chart-bar me-1"></i> تقارير</button></li>
    </ul>

    <div class="tab-content">
        {{-- 1. المخزن الرئيسي --}}
        <div class="tab-pane fade show active" id="tab-main">
            <div class="table-box">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead><tr><th class="text-start">الصنف</th><th>الفئة</th><th>المورد</th><th>الكمية الإجمالية</th><th>سعر الشراء</th><th>سعر البيع</th><th>إجراءات</th></tr></thead>
                        <tbody>
                            @forelse($main_store_groups as $group)
                            <tr style="cursor:pointer;" onclick="openBatchesModal({{ json_encode($group->batches) }}, 1, 2)">
                                <td class="text-start"><div class="fw-bold">{{ Str::limit($group->product_name, 35) }}
                                    @if($group->has_return)<span class="badge-return"><i class="fa fa-rotate-left"></i> مرتجع عميل</span>@endif</div>
                                    @if($group->batch_count > 1)<div class="batch-id mt-1 w-auto d-inline-block">{{ $group->batch_count }} دفعات</div>@endif
                                </td>
                                <td><span style="color: var(--accent); font-weight: 600;">{{ $group->category }}</span></td>
                                <td><span style="color: var(--text-muted);">{{ Str::limit($group->supplier_name, 20) }}</span></td>
                                <td><span class="fw-black fs-6 {{ $group->total_qty < 5 ? 'text-danger' : 'text-success' }}">{{ fmtMoney($group->total_qty) }}</span></td>
                                <td class="text-danger fw-bold">{{ $group->min_purchase == $group->max_purchase ? fmtMoney($group->min_purchase) : fmtMoney($group->min_purchase).' - '.fmtMoney($group->max_purchase) }} ج</td>
                                <td class="text-success fw-bold">{{ $group->min_selling == $group->max_selling ? fmtMoney($group->min_selling) : fmtMoney($group->min_selling).' - '.fmtMoney($group->max_selling) }} ج</td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn-action-sm" style="background:#0f172a; color:#fff;" onclick="event.stopPropagation(); openBatchesModal({{ json_encode($group->batches) }}, 1, 2)" title="عرض الدفعات والإجراءات"><i class="fa fa-layer-group"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @empty <tr><td colspan="7"><div class="empty-row"><i class="fa fa-box-open"></i>المخزن الرئيسي فارغ — لم يتم توريد أي بضاعة بعد</div></td></tr> @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 2. مخزن 2 --}}
        <div class="tab-pane fade" id="tab-sub">
            <div class="table-box">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead><tr><th class="text-start">الصنف</th><th>الفئة</th><th>المورد</th><th>الكمية الإجمالية</th><th>سعر الشراء</th><th>سعر البيع</th><th>إجراءات</th></tr></thead>
                        <tbody>
                            @forelse($sub_store_groups as $group)
                            <tr style="cursor:pointer;" onclick="openBatchesModal({{ json_encode($group->batches) }}, 2, 1)">
                                <td class="text-start"><div class="fw-bold">{{ Str::limit($group->product_name, 35) }}
                                    @if($group->has_return)<span class="badge-return"><i class="fa fa-rotate-left"></i> مرتجع عميل</span>@endif</div>
                                    @if($group->batch_count > 1)<div class="batch-id mt-1 w-auto d-inline-block">{{ $group->batch_count }} دفعات</div>@endif
                                </td>
                                <td><span style="color: var(--accent); font-weight: 600;">{{ $group->category }}</span></td>
                                <td><span style="color: var(--text-muted);">{{ Str::limit($group->supplier_name, 20) }}</span></td>
                                <td><span class="fw-black fs-6 text-warning">{{ fmtMoney($group->total_qty) }}</span></td>
                                <td class="text-danger fw-bold">{{ $group->min_purchase == $group->max_purchase ? fmtMoney($group->min_purchase) : fmtMoney($group->min_purchase).' - '.fmtMoney($group->max_purchase) }} ج</td>
                                <td class="text-success fw-bold">{{ $group->min_selling == $group->max_selling ? fmtMoney($group->min_selling) : fmtMoney($group->min_selling).' - '.fmtMoney($group->max_selling) }} ج</td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn-action-sm" style="background:#0f172a; color:#fff;" onclick="event.stopPropagation(); openBatchesModal({{ json_encode($group->batches) }}, 2, 1)" title="عرض الدفعات والإجراءات"><i class="fa fa-layer-group"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @empty <tr><td colspan="7"><div class="empty-row"><i class="fa fa-box-open"></i>مخزن 2 فارغ</div></td></tr> @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 3. سجل المشتريات مقسم بالموردين (Cards) --}}
        <div class="tab-pane fade" id="tab-purchases">
            <div style="padding: 4px 0;">
                <div class="tab-section-head">
                    <h4><i class="fa fa-address-book"></i> سجل الموردين والمشتريات</h4>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">{{ count($supplierPurchases) }} مورد</span>
                </div>
                <div class="row g-3">
                    @forelse($supplierPurchases as $sup)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="sup-card" onclick="openSupplierLogModal('{{ addslashes($sup['supplier']) }}', {{ json_encode($sup['details']) }}, {{ $sup['total_cost'] }})">
                            <div class="sup-icon"><i class="fa fa-truck-fast"></i></div>
                            <h5>{{ Str::limit($sup['supplier'], 20) }}</h5>
                            <div style="display:flex; justify-content:space-between; font-size:0.78rem; color: var(--text-muted); margin-bottom: 10px;">
                                <span>{{ $sup['total_batches'] }} فاتورة</span>
                                <span>{{ $sup['total_items'] }} قطعة</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-end; padding-top:10px; border-top: 1px solid var(--border);">
                                <span style="font-size:0.74rem; color: var(--text-soft);">إجمالي الشراء</span>
                                <span style="font-size:1.15rem; font-weight:600; color: var(--danger);">{{ fmtMoney($sup['total_cost']) }} <small style="font-size:0.7rem; font-weight:400;">ج</small></span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="empty-row"><i class="fa fa-truck"></i>لا يوجد سجل مشتريات أو موردين</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 4. سجل المبيعات --}}
        <div class="tab-pane fade" id="tab-sales">
            {{-- 🔎 فلاتر سجل المبيعات (تعمل لحظياً على الجدول + الطباعة تحترمها بالظبط) --}}
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-md); padding: 14px 16px; margin-bottom: 14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="text" id="sl-search" class="form-control" style="width:190px; padding:7px 10px; font-size:0.86rem;" placeholder="بحث باسم العميل..." oninput="applySalesFilter()">
                <select id="sl-type" class="form-control" style="width:150px; padding:7px 10px; font-size:0.86rem;" onchange="applySalesFilter()">
                    <option value="all">الكل (كاش+تقسيط)</option>
                    <option value="cash">كاش فقط</option>
                    <option value="inst">تقسيط فقط</option>
                </select>
                <span style="font-size:0.82rem; color:var(--text-muted);">من</span>
                <input type="date" id="sl-from" class="form-control" style="width:150px; padding:7px 10px; font-size:0.86rem;" onchange="applySalesFilter()">
                <span style="font-size:0.82rem; color:var(--text-muted);">إلى</span>
                <input type="date" id="sl-to" class="form-control" style="width:150px; padding:7px 10px; font-size:0.86rem;" onchange="applySalesFilter()">
                <button type="button" class="rpt-filter-btn" onclick="resetSalesFilter()" title="إعادة تعيين"><i class="fa fa-rotate"></i></button>
                <button class="btn-print" style="margin-right:auto;" onclick="printSalesLog()"><i class="fa fa-print"></i> طباعة (حسب الفلتر)</button>
            </div>
            <div style="display:flex; gap:16px; align-items:center; margin-bottom:12px; flex-wrap:wrap; font-size:0.85rem;">
                <span style="color: var(--text-muted);"><b id="sl-count">{{ count($sales_log) }}</b> عملية بيع</span>
                <span style="color: var(--success); font-weight:700;">الإيرادات: <span id="sl-revenue">0</span> ج</span>
                <span style="color: var(--accent); font-weight:700;">الأرباح: <span id="sl-profit">0</span> ج</span>
            </div>
            <div class="table-box">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead><tr><th>التاريخ</th><th class="text-start">اسم العميل</th><th class="text-start">البيان (الفاتورة)</th><th>الإجمالي</th><th>الربح</th></tr></thead>
                        <tbody id="sales-log-body">
                            @foreach($sales_log as $sale)
                            <tr onclick="openSaleDetails({{ json_encode($sale) }})"
                                data-date="{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}"
                                data-customer="{{ $sale->customer_name ?? '' }}"
                                data-months="{{ (int)($sale->installment_months ?? 0) }}"
                                data-total="{{ (float)($sale->total_after_interest ?? $sale->cash_price ?? 0) }}"
                                data-profit="{{ (float)($sale->profit ?? 0) }}">
                                <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}</td>
                                <td class="text-start text-primary fw-bold">{{ $sale->customer_name }}</td>
<td class="text-start fw-bold">
    {{ Str::limit($sale->product_name, 60) }}
    
    @if(($sale->installment_months ?? 0) > 0)
        <div class="mt-2">
            <span class="badge bg-info text-dark rounded-pill px-3 shadow-sm border border-info">
                <i class="fa fa-handshake me-1"></i> عقد تقسيط ({{ $sale->installment_months }} شهور)
            </span>
        </div>
    @else
        <div class="mt-2">
            <span class="badge bg-success rounded-pill px-3 shadow-sm" style="background-color: #16a34a !important;">
                <i class="fa fa-money-bill-wave me-1"></i> بيع كاش
            </span>
        </div>
    @endif
</td>                                <td class="text-success fw-bold fs-6">{{ fmtMoney($sale->total_after_interest ?? $sale->cash_price) }} ج</td>
                                <td class="text-primary fw-black fs-6">+{{ fmtMoney($sale->profit ?? 0) }} ج</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 5. مرتجعات المبيعات (عملاء) --}}
        <div class="tab-pane fade" id="tab-returns-cust">
            <div class="table-box">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead><tr><th>التاريخ</th><th class="text-start">الصنف المسترد</th><th>الكمية</th><th>المبلغ المردود للعميل</th><th>الخسارة الدفترية</th></tr></thead>
                        <tbody>
                            @foreach($returns_log as $ret)
                            <tr onclick="openCustReturnDetails({{ json_encode($ret) }})">
                                <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($ret->created_at)->format('Y-m-d') }}</td>
                                <td class="text-start fw-bold">{{ $ret->product_name }}</td>
                                <td><span class="badge bg-warning text-dark px-3 py-1 fs-6">{{ fmtMoney($ret->quantity_returned) }}</span></td>
                                <td class="text-success fw-bold">{{ fmtMoney($ret->return_price) }} ج</td>
                                <td class="text-danger fw-black">-{{ fmtMoney($ret->loss_amount) }} ج</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 6. مرتجعات المشتريات (موردين) --}}
        <div class="tab-pane fade" id="tab-returns-sup">
            @php
                $supRetTotalQty    = $supplier_returns->sum('quantity');
                $supRetTotalRefund = $supplier_returns->sum('total_refunded');
                $supRetTotalLoss   = $supplier_returns->sum('loss_amount');
            @endphp
            <div class="cards" style="margin-bottom: 18px;">
                <div class="stat-card blue">
                    <h3><i class="fa fa-reply-all"></i> عدد المرتجعات</h3>
                    <div class="value">{{ fmtMoney($supplier_returns->count()) }}</div>
                </div>
                <div class="stat-card red">
                    <h3><i class="fa fa-boxes-stacked"></i> إجمالي الكمية المرتجعة</h3>
                    <div class="value">{{ fmtMoney($supRetTotalQty) }}</div>
                </div>
                <div class="stat-card green">
                    <h3><i class="fa fa-hand-holding-dollar"></i> إجمالي المسترد</h3>
                    <div class="value">{{ fmtMoney($supRetTotalRefund) }} <span class="fs-6">ج.م</span></div>
                </div>
                <div class="stat-card red">
                    <h3><i class="fa fa-arrow-trend-down"></i> إجمالي الخسائر</h3>
                    <div class="value">{{ fmtMoney($supRetTotalLoss) }} <span class="fs-6">ج.م</span></div>
                </div>
            </div>
            <div class="table-box">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th class="text-start">الصنف</th>
                                <th class="text-start">المورد</th>
                                <th>الكمية</th>
                                <th>سعر الشراء</th>
                                <th>قيمة الاسترداد</th>
                                <th>الخسارة</th>
                                <th>خزنة الاسترداد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplier_returns as $supRet)
                            <tr onclick="openSupReturnDetails({{ json_encode($supRet) }})" style="cursor:pointer;">
                                <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($supRet->created_at)->format('Y-m-d') }}</td>
                                <td class="text-start fw-bold">{{ $supRet->product_name }}</td>
                                <td class="text-start" style="color: var(--text-muted);">{{ $supRet->supplier_name }}</td>
                                <td><span class="badge bg-danger px-3 py-1 fs-6">{{ fmtMoney($supRet->quantity) }}</span></td>
                                <td>{{ fmtMoney($supRet->purchase_price) }} ج</td>
                                <td style="color: var(--success); font-weight:700;">{{ fmtMoney($supRet->total_refunded) }} ج</td>
                                <td style="color: var(--danger); font-weight:700;">{{ $supRet->loss_amount > 0 ? fmtMoney($supRet->loss_amount) . ' ج' : '—' }}</td>
                                <td style="color: var(--text-muted);">{{ $supRet->refund_account }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8"><div class="empty-row"><i class="fa fa-reply-all"></i>لا توجد مرتجعات موردين</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 7. تقارير المخزن --}}
        <div class="tab-pane fade" id="tab-inv-reports">
            {{-- فلاتر الفترة الزمنية --}}
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-md); padding: 16px 18px; margin-bottom: 18px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">الفترة:</span>
                <button class="rpt-filter-btn active" data-filter="month" onclick="setReportFilter('month', this)">هذا الشهر</button>
                <button class="rpt-filter-btn" data-filter="yesterday" onclick="setReportFilter('yesterday', this)">أمس</button>
                <button class="rpt-filter-btn" data-filter="year" onclick="setReportFilter('year', this)">هذه السنة</button>
                <button class="rpt-filter-btn" data-filter="custom_month" onclick="setReportFilter('custom_month', this)">شهر محدد</button>
                <button class="rpt-filter-btn" data-filter="range" onclick="setReportFilter('range', this)">نطاق تاريخ</button>

                <div id="rpt-custom-month-wrap" style="display:none; gap:8px; align-items:center; flex-wrap:wrap;" class="d-flex">
                    <input type="month" id="rpt-month-picker" class="form-control" style="width:160px; padding: 7px 10px; font-size:0.86rem;" onchange="buildReports()">
                </div>
                <div id="rpt-range-wrap" style="display:none; gap:8px; align-items:center; flex-wrap:wrap;" class="d-flex">
                    <input type="date" id="rpt-from" class="form-control" style="width:145px; padding: 7px 10px; font-size:0.86rem;" onchange="buildReports()">
                    <span style="color: var(--text-muted); font-size:0.82rem;">إلى</span>
                    <input type="date" id="rpt-to" class="form-control" style="width:145px; padding: 7px 10px; font-size:0.86rem;" onchange="buildReports()">
                </div>

                <button type="button" class="btn btn-dark fw-bold" style="margin-right:auto; padding: 7px 18px; font-size:0.86rem; border-radius: var(--r-sm);" onclick="printInventoryReports()">
                    <i class="fa fa-print me-1"></i> طباعة التقرير
                </button>
            </div>

            {{-- كاردات الإجمالي --}}
            <div class="cards" id="rpt-summary-cards" style="margin-bottom: 20px;">
                <div class="stat-card green">
                    <h3><i class="fa fa-money-bill-wave"></i> إجمالي المبيعات</h3>
                    <div class="value" id="rpt-total-sales">0 <span class="fs-6">ج.م</span></div>
                </div>
                <div class="stat-card red">
                    <h3><i class="fa fa-shopping-cart"></i> إجمالي المشتريات</h3>
                    <div class="value" id="rpt-total-purchases">0 <span class="fs-6">ج.م</span></div>
                </div>
                <div class="stat-card blue">
                    <h3><i class="fa fa-chart-line"></i> إجمالي الأرباح</h3>
                    <div class="value" id="rpt-total-profit">0 <span class="fs-6">ج.م</span></div>
                </div>
            </div>

            {{-- جدول المبيعات بالفئة --}}
            <div class="tab-section-head" style="margin-top: 8px;">
                <h4><i class="fa fa-arrow-trend-up"></i> المبيعات والأرباح بالفئة</h4>
            </div>
            <div class="table-box" style="margin-bottom: 22px;">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="text-start">الفئة</th>
                                <th>الكمية المباعة</th>
                                <th>إجمالي المبيعات</th>
                                <th>تكلفة المباع</th>
                                <th>الربح</th>
                                <th>هامش الربح %</th>
                            </tr>
                        </thead>
                        <tbody id="rpt-sales-body">
                            <tr><td colspan="6"><div class="empty-row"><i class="fa fa-chart-bar"></i>اختر فترة زمنية لعرض التقارير</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- جدول المشتريات بالفئة --}}
            <div class="tab-section-head">
                <h4><i class="fa fa-truck-loading"></i> المشتريات بالفئة والمورد</h4>
            </div>
            <div class="table-box">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="text-start">الفئة</th>
                                <th class="text-start">المورد</th>
                                <th>الكمية</th>
                                <th>إجمالي التكلفة</th>
                            </tr>
                        </thead>
                        <tbody id="rpt-purchases-body">
                            <tr><td colspan="4"><div class="empty-row"><i class="fa fa-truck"></i>اختر فترة زمنية لعرض التقارير</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rpt-filter-btn {
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text-muted);
    border-radius: var(--r-sm);
    padding: 7px 14px;
    font-size: 0.83rem;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: var(--t);
}
.rpt-filter-btn:hover { background: var(--hover-bg); color: var(--text-main); }
.rpt-filter-btn.active { background: var(--text-main); color: var(--surface); border-color: var(--text-main); }
</style>

<script>
const RPT_SALES     = @json($salesRawReport);
const RPT_PURCHASES = @json($purchasesRawReport);
let currentRptFilter = 'month';

function setReportFilter(filter, btn) {
    currentRptFilter = filter;
    document.querySelectorAll('.rpt-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('rpt-custom-month-wrap').style.display = (filter === 'custom_month') ? 'flex' : 'none';
    document.getElementById('rpt-range-wrap').style.display         = (filter === 'range') ? 'flex' : 'none';
    buildReports();
}

function getDateRange() {
    const today = new Date();
    const fmt = d => d.toISOString().slice(0, 10);
    if (currentRptFilter === 'yesterday') {
        const y = new Date(today); y.setDate(y.getDate() - 1);
        return [fmt(y), fmt(y)];
    }
    if (currentRptFilter === 'month') {
        const from = new Date(today.getFullYear(), today.getMonth(), 1);
        const to   = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        return [fmt(from), fmt(to)];
    }
    if (currentRptFilter === 'year') {
        return [today.getFullYear() + '-01-01', today.getFullYear() + '-12-31'];
    }
    if (currentRptFilter === 'custom_month') {
        const val = document.getElementById('rpt-month-picker').value;
        if (!val) return [null, null];
        const [y, m] = val.split('-').map(Number);
        const last = new Date(y, m, 0).getDate();
        return [`${y}-${String(m).padStart(2,'0')}-01`, `${y}-${String(m).padStart(2,'0')}-${last}`];
    }
    if (currentRptFilter === 'range') {
        const from = document.getElementById('rpt-from').value;
        const to   = document.getElementById('rpt-to').value;
        return [from || null, to || null];
    }
    return [null, null];
}

function inRange(dateStr, from, to) {
    if (!from && !to) return true;
    if (!dateStr) return false;
    if (from && dateStr < from) return false;
    if (to   && dateStr > to)   return false;
    return true;
}

// 💡 اسم فريد لتفادي التعارض مع دالة fmt العامة الخاصة بالطباعة (كان يكسر كل وظائف الطباعة)
function rptFmt(n) { return fmtMoney(n); }

// آخر نتيجة محسوبة — تُستخدم في الطباعة
let lastReportData = null;

function rptFilterLabel() {
    switch (currentRptFilter) {
        case 'yesterday':    return 'أمس';
        case 'month':        return 'هذا الشهر';
        case 'year':         return 'هذه السنة';
        case 'custom_month': return 'شهر: ' + (document.getElementById('rpt-month-picker').value || '—');
        case 'range':        return 'من ' + (document.getElementById('rpt-from').value || '—') + ' إلى ' + (document.getElementById('rpt-to').value || '—');
        default:             return '';
    }
}

function buildReports() {
    const [from, to] = getDateRange();

    // ── مبيعات مفلترة ──
    const filteredSales = RPT_SALES.filter(r => inRange(r.date, from, to));
    const salesByCategory = {};
    filteredSales.forEach(r => {
        const cat = r.category || 'عام';
        if (!salesByCategory[cat]) salesByCategory[cat] = { qty: 0, sell: 0, cost: 0, profit: 0 };
        salesByCategory[cat].qty    += r.qty;
        salesByCategory[cat].sell   += r.sell_value;
        salesByCategory[cat].cost   += r.cost_value;
        salesByCategory[cat].profit += r.profit;
    });

    const totalSales     = Object.values(salesByCategory).reduce((s, v) => s + v.sell,   0);
    const totalProfit    = Object.values(salesByCategory).reduce((s, v) => s + v.profit, 0);
    const totalSalesCost = Object.values(salesByCategory).reduce((s, v) => s + v.cost,   0);

    // ── مشتريات مفلترة ──
    const filteredPurchases = RPT_PURCHASES.filter(r => inRange(r.date, from, to));
    const purchasesByKey = {};
    filteredPurchases.forEach(r => {
        const key = (r.category || 'عام') + '|' + (r.supplier || 'غير محدد');
        if (!purchasesByKey[key]) purchasesByKey[key] = { category: r.category || 'عام', supplier: r.supplier || 'غير محدد', qty: 0, cost: 0 };
        purchasesByKey[key].qty  += r.qty;
        purchasesByKey[key].cost += r.total_cost;
    });

    const totalPurchases = Object.values(purchasesByKey).reduce((s, v) => s + v.cost, 0);

    const salesEntries = Object.entries(salesByCategory).sort((a, b) => b[1].sell - a[1].sell);
    const purchEntries = Object.values(purchasesByKey).sort((a, b) => b.cost - a.cost);

    // خزّن النتيجة للطباعة
    lastReportData = { salesEntries, purchEntries, totalSales, totalProfit, totalSalesCost, totalPurchases, label: rptFilterLabel() };

    // ── تحديث الكاردات ──
    document.getElementById('rpt-total-sales').innerHTML     = rptFmt(totalSales) + ' <span class="fs-6">ج.م</span>';
    document.getElementById('rpt-total-purchases').innerHTML = rptFmt(totalPurchases) + ' <span class="fs-6">ج.م</span>';
    document.getElementById('rpt-total-profit').innerHTML    = rptFmt(totalProfit) + ' <span class="fs-6">ج.م</span>';

    // ── جدول المبيعات ──
    const salesBody = document.getElementById('rpt-sales-body');
    if (salesEntries.length === 0) {
        salesBody.innerHTML = '<tr><td colspan="6"><div class="empty-row"><i class="fa fa-chart-bar"></i>لا توجد مبيعات في هذه الفترة</div></td></tr>';
    } else {
        salesBody.innerHTML = salesEntries.map(([cat, d]) => {
            const margin = d.sell > 0 ? ((d.profit / d.sell) * 100).toFixed(1) : 0;
            const profitColor = d.profit >= 0 ? 'var(--success)' : 'var(--danger)';
            return `<tr>
                <td class="text-start fw-bold" style="color: var(--accent);">${cat}</td>
                <td><span style="font-weight:700; color: var(--text-main);">${rptFmt(d.qty)}</span></td>
                <td style="color: var(--success); font-weight:700;">${rptFmt(d.sell)} ج</td>
                <td style="color: var(--danger); font-weight:600;">${rptFmt(d.cost)} ج</td>
                <td style="color: ${profitColor}; font-weight:800; font-size:1rem;">+${rptFmt(d.profit)} ج</td>
                <td><span style="background: var(--success-bg); color: var(--success); padding: 3px 10px; border-radius: 999px; font-size:0.78rem; font-weight:700;">${margin}%</span></td>
            </tr>`;
        }).join('') + `<tr style="background: var(--surface-2); font-weight:700;">
            <td class="text-start" style="color: var(--text-muted); font-size:0.8rem;">الإجمالي</td>
            <td></td>
            <td style="color: var(--success); font-weight:800;">${rptFmt(totalSales)} ج</td>
            <td style="color: var(--danger); font-weight:800;">${rptFmt(totalSalesCost)} ج</td>
            <td style="color: var(--accent); font-weight:900; font-size:1.05rem;">+${rptFmt(totalProfit)} ج</td>
            <td></td>
        </tr>`;
    }

    // ── جدول المشتريات ──
    const purchBody = document.getElementById('rpt-purchases-body');
    if (purchEntries.length === 0) {
        purchBody.innerHTML = '<tr><td colspan="4"><div class="empty-row"><i class="fa fa-truck"></i>لا توجد مشتريات في هذه الفترة</div></td></tr>';
    } else {
        purchBody.innerHTML = purchEntries.map(d => `<tr>
            <td class="text-start fw-bold" style="color: var(--accent);">${d.category}</td>
            <td class="text-start" style="color: var(--text-muted);">${d.supplier}</td>
            <td style="font-weight:700;">${rptFmt(d.qty)}</td>
            <td style="color: var(--danger); font-weight:700;">${rptFmt(d.cost)} ج</td>
        </tr>`).join('') + `<tr style="background: var(--surface-2); font-weight:700;">
            <td class="text-start" colspan="3" style="color: var(--text-muted); font-size:0.8rem;">إجمالي المشتريات</td>
            <td style="color: var(--danger); font-weight:900; font-size:1.05rem;">${rptFmt(totalPurchases)} ج</td>
        </tr>`;
    }
}

// 🖨️ طباعة تقرير المخزن للفترة المختارة (يعتمد على دوال الطباعة العامة)
window.printInventoryReports = function() {
    if (!lastReportData) buildReports();
    const d = lastReportData;
    const f = (typeof fmt === 'function') ? fmt : rptFmt;

    const salesRows = d.salesEntries.length === 0
        ? '<tr><td colspan="6" style="text-align:center; color:#94a3b8;">لا توجد مبيعات في هذه الفترة</td></tr>'
        : d.salesEntries.map(([cat, s]) => {
            const margin = s.sell > 0 ? ((s.profit / s.sell) * 100).toFixed(1) : 0;
            return `<tr>
                <td class="text-start">${cat}</td>
                <td>${f(s.qty)}</td>
                <td class="num-pos">${f(s.sell)} ج</td>
                <td class="num-neg">${f(s.cost)} ج</td>
                <td class="num-pos">${f(s.profit)} ج</td>
                <td>${margin}%</td>
            </tr>`;
        }).join('');

    const purchRows = d.purchEntries.length === 0
        ? '<tr><td colspan="4" style="text-align:center; color:#94a3b8;">لا توجد مشتريات في هذه الفترة</td></tr>'
        : d.purchEntries.map(p => `<tr>
            <td class="text-start">${p.category}</td>
            <td class="text-start">${p.supplier}</td>
            <td>${f(p.qty)}</td>
            <td class="num-neg">${f(p.cost)} ج</td>
        </tr>`).join('');

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>تقرير المخزن - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
            <style>${getPrintStyles()}</style>
        </head>
        <body>
            <div class="page">
                ${getHeaderHTML('تقرير المخزن — ' + d.label)}

                <div class="summary">
                    <div class="box success"><div class="label">إجمالي المبيعات</div><div class="val">${f(d.totalSales)} ج</div></div>
                    <div class="box danger"><div class="label">إجمالي المشتريات</div><div class="val">${f(d.totalPurchases)} ج</div></div>
                    <div class="box warning"><div class="label">إجمالي الأرباح</div><div class="val">${f(d.totalProfit)} ج</div></div>
                </div>

                <div class="section-title">المبيعات والأرباح بالفئة <small>${d.salesEntries.length} فئة</small></div>
                <table class="data">
                    <thead>
                        <tr>
                            <th class="text-start">الفئة</th><th>الكمية المباعة</th>
                            <th>إجمالي المبيعات</th><th>تكلفة المباع</th><th>الربح</th><th>هامش %</th>
                        </tr>
                    </thead>
                    <tbody>${salesRows}</tbody>
                    <tfoot>
                        <tr>
                            <td class="text-start" style="text-align:right;">الإجمالي</td>
                            <td></td>
                            <td class="num-pos">${f(d.totalSales)} ج</td>
                            <td class="num-neg">${f(d.totalSalesCost)} ج</td>
                            <td class="num-pos">${f(d.totalProfit)} ج</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="section-title">المشتريات بالفئة والمورد <small>${d.purchEntries.length} سجل</small></div>
                <table class="data">
                    <thead>
                        <tr><th class="text-start">الفئة</th><th class="text-start">المورد</th><th>الكمية</th><th>إجمالي التكلفة</th></tr>
                    </thead>
                    <tbody>${purchRows}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-start" style="text-align:right;">إجمالي المشتريات</td>
                            <td class="num-neg">${f(d.totalPurchases)} ج</td>
                        </tr>
                    </tfoot>
                </table>

                ${getFooterHTML('أمين المخزن', 'المدير المالي')}
            </div>
        </body></html>
    `;
    openPrintWin(html);
};

// تشغيل عند الضغط على التاب
document.getElementById('btn-inv-reports').addEventListener('click', function() {
    setTimeout(buildReports, 50);
});

// تهيئة إجماليات سجل المبيعات أول ما الصفحة تحمّل
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('sales-log-body')) applySalesFilter();
});
</script>

{{-- ══════════════════════════════════════════════════════════
     المودالات المنظمة (Popups) - تعمل لكل الصفوف
     ══════════════════════════════════════════════════════════ --}}

{{-- مودال عرض تفاصيل الباتش (المخزن) --}}
<div class="modal fade" id="itemDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title text-white m-0"><i class="fa fa-box-open me-2 text-primary"></i>تفاصيل الصنف (الباتش)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light text-dark">
                <h4 id="det_name" class="fw-black text-primary mb-3 border-bottom pb-2"></h4>
                <div class="row g-3">
                    <div class="col-md-6"><div class="detail-row" style="border-color:#e2e8f0;"><span class="detail-lbl text-muted">المورد:</span><span class="detail-val text-dark" id="det_sup"></span></div></div>
                    <div class="col-md-6"><div class="detail-row" style="border-color:#e2e8f0;"><span class="detail-lbl text-muted">الفئة:</span><span class="detail-val text-dark" id="det_cat"></span></div></div>
                    <div class="col-md-6"><div class="detail-row" style="border-color:#e2e8f0;"><span class="detail-lbl text-muted">المخزن:</span><span class="detail-val text-dark" id="det_store"></span></div></div>
                    <div class="col-md-6"><div class="detail-row" style="border-color:#e2e8f0;"><span class="detail-lbl text-muted">تاريخ الشراء:</span><span class="detail-val text-dark font-monospace" id="det_date" dir="ltr"></span></div></div>
                    <div class="col-md-4 mt-4 text-center">
                        <small class="text-muted fw-bold d-block mb-1">الكمية الأساسية</small>
                        <span id="det_qty_total" class="badge bg-primary fs-5 px-4 py-2"></span>
                    </div>
                    <div class="col-md-4 mt-4 text-center">
                        <small class="text-muted fw-bold d-block mb-1">المُباع</small>
                        <span id="det_qty_sold" class="badge bg-success fs-5 px-4 py-2"></span>
                    </div>
                    <div class="col-md-4 mt-4 text-center">
                        <small class="text-muted fw-bold d-block mb-1">المتاح حالياً</small>
                        <span id="det_qty_rem" class="badge bg-danger fs-5 px-4 py-2"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-2 border-0 bg-white">
                <button type="button" class="btn btn-dark fw-bold px-5 rounded-pill w-100" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- مودال عرض دفعات الصنف المجمّع --}}
<div class="modal fade" id="batchesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title text-white m-0"><i class="fa fa-layer-group me-2 text-primary"></i>دفعات الصنف: <span id="batches_name" class="text-primary"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="background: var(--bg);">
                <div class="table-responsive p-3">
                    <table class="custom-table m-0">
                        <thead><tr><th>تاريخ الشراء</th><th>المورد</th><th>الكمية المتاحة</th><th>سعر الشراء</th><th>سعر البيع</th><th>إجراءات</th></tr></thead>
                        <tbody id="batches_body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer p-2 border-0 bg-white">
                <button type="button" class="btn btn-dark fw-bold px-5 rounded-pill w-100" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- مودال سجل المورد الشامل --}}
<div class="modal fade" id="supplierLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title text-white fw-bold m-0"><i class="fa fa-folder-open me-2 text-warning"></i>سجل تعاملات المورد: <span id="sup_log_name" class="text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="background: var(--bg);">
                <div class="p-3 bg-danger bg-opacity-10 border-bottom border-danger text-center">
                    <span class="fw-bold text-danger d-block mb-1">إجمالي الفلوس المدفوعة له دفترياً</span>
                    <h3 class="fw-black text-danger m-0" id="sup_log_total"></h3>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-bordered text-center align-middle m-0" style="background: var(--surface); color: var(--text-main); border-color: var(--border);">
                        <thead style="background: var(--hover-bg);">
                            <tr>
                                <th>التاريخ</th>
                                <th class="text-start">الصنف المُشترى</th>
                                <th>الكمية</th>
                                <th>سعر التوريد</th>
                                <th>إجمالي الفاتورة</th>
                            </tr>
                        </thead>
                        <tbody id="sup_log_body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 d-flex gap-2" style="background: var(--surface);">
                <button type="button" class="btn btn-primary fw-bold px-4 rounded-pill flex-fill" onclick="printCurrentSupplier()"><i class="fa fa-print me-2"></i>طباعة كشف الحساب</button>
                <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- مودال عرض تفاصيل المبيعات الشامل + الطباعة --}}
<div class="modal fade" id="saleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3" style="background: var(--hover-bg);"><h5 class="modal-title fw-bold"><i class="fa fa-receipt me-2 text-success"></i>تفاصيل المبيعات</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0">
                <div class="p-4 bg-success bg-opacity-10 text-center border-bottom" style="border-color: var(--border) !important;">
                    <h3 class="fw-black text-success m-0" id="sd_total"></h3>
                    <small class="text-success fw-bold mt-1 d-block">الإجمالي النهائي المطلوب للقطعة</small>
                </div>
                <div class="p-3">
                    <div class="detail-row"><span class="detail-lbl">العميل</span><span class="detail-val text-primary" id="sd_cust"></span></div>
                    <div class="detail-row"><span class="detail-lbl">الفاتورة الأساسية</span><span class="detail-val text-wrap lh-base" id="sd_prod"></span></div>
                    
                    {{-- 💡 بلوك تفاصيل التكييفات (مخفي افتراضياً) --}}
                    <div id="sd_extras_box" style="" class="bg-light p-3 rounded mb-2 border">
                        <h6 class="fw-bold text-primary mb-2"><i class="fa fa-screwdriver-wrench me-1"></i>مصروفات التجهيز الإضافية:</h6>
                        <div class="d-flex justify-content-between mb-1"><span class="small fw-bold text-muted">النقل:</span><span class="fw-bold text-danger" id="sd_trans"></span></div>
                        <div class="d-flex justify-content-between mb-1"><span class="small fw-bold text-muted">التركيب والخامات:</span><span class="fw-bold text-success" id="sd_inst"></span></div>
                        <div class="d-flex justify-content-between"><span class="small fw-bold text-muted">أخرى:</span><span class="fw-bold text-secondary" id="sd_oth"></span></div>
                    </div>

                    <div class="detail-row"><span class="detail-lbl">الخصم (التسوية)</span><span class="detail-val text-warning" id="sd_disc"></span></div>
                    <div class="detail-row"><span class="detail-lbl">المدفوع مقدماً (كاش)</span><span class="detail-val text-success" id="sd_down"></span></div>
                    <div class="detail-row"><span class="detail-lbl">المتبقي (آجل)</span><span class="detail-val text-danger" id="sd_rem"></span></div>
                    <div class="detail-row"><span class="detail-lbl">الربح المحقق لشركتنا</span><span class="detail-val text-primary fs-5" id="sd_profit"></span></div>
                </div>
            </div>
            <div class="modal-footer border-0 p-2 d-flex flex-column">
                <button type="button" class="btn btn-primary fw-bold w-100 mb-1 rounded-pill" onclick="printInvoice()"><i class="fa fa-print me-2"></i> طباعة الفاتورة للعميل</button>
                <button type="button" class="btn btn-light fw-bold w-100 rounded-pill" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- مودال عرض تفاصيل مرتجعات العملاء --}}
<div class="modal fade" id="custReturnDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3" style="background: var(--hover-bg);"><h5 class="modal-title fw-bold"><i class="fa fa-undo me-2 text-warning"></i>تفاصيل مرتجع عميل</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-3">
                <div class="detail-row"><span class="detail-lbl">الصنف</span><span class="detail-val text-wrap lh-base" id="crd_prod"></span></div>
                <div class="detail-row"><span class="detail-lbl">الكمية</span><span class="detail-val text-warning fs-5" id="crd_qty"></span></div>
                <div class="detail-row"><span class="detail-lbl">المبلغ المردود</span><span class="detail-val text-success fs-5" id="crd_refund"></span></div>
                <div class="detail-row"><span class="detail-lbl">الخسارة الدفترية</span><span class="detail-val text-danger" id="crd_loss"></span></div>
                <div class="detail-row"><span class="detail-lbl">السبب / الملاحظات</span><span class="detail-val" id="crd_notes" style="color: var(--text-muted);"></span></div>
            </div>
            <div class="modal-footer border-0 p-2"><button type="button" class="btn btn-light fw-bold w-100" data-bs-dismiss="modal">إغلاق</button></div>
        </div>
    </div>
</div>

{{-- مودال عرض تفاصيل مرتجعات الموردين --}}
<div class="modal fade" id="supReturnDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3" style="background: var(--hover-bg);"><h5 class="modal-title fw-bold"><i class="fa fa-reply-all me-2 text-danger"></i>تفاصيل مرتجع مورد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-3">
                <div class="detail-row"><span class="detail-lbl">الصنف</span><span class="detail-val fw-bold" id="srd_product"></span></div>
                <div class="detail-row"><span class="detail-lbl">المورد</span><span class="detail-val" id="srd_supplier"></span></div>
                <div class="detail-row"><span class="detail-lbl">الكمية المُرجعة</span><span class="detail-val fs-4" id="srd_qty"></span></div>
                <div class="detail-row"><span class="detail-lbl">سعر الشراء (للوحدة)</span><span class="detail-val" id="srd_pp"></span></div>
                <div class="detail-row"><span class="detail-lbl">سعر الاسترداد (للوحدة)</span><span class="detail-val" id="srd_rp"></span></div>
                <div class="detail-row"><span class="detail-lbl">إجمالي المسترد</span><span class="detail-val text-success fw-bold" id="srd_refund"></span></div>
                <div class="detail-row"><span class="detail-lbl">الخسارة</span><span class="detail-val text-danger fw-bold" id="srd_loss"></span></div>
                <div class="detail-row"><span class="detail-lbl">خزنة الاسترداد</span><span class="detail-val" id="srd_account"></span></div>
                <div class="detail-row"><span class="detail-lbl">تفاصيل الارتجاع</span><span class="detail-val text-danger text-wrap lh-base" id="srd_notes"></span></div>
                <div class="detail-row"><span class="detail-lbl">تاريخ الارتجاع</span><span class="detail-val font-monospace" id="srd_date" dir="ltr" style="color: var(--text-muted);"></span></div>
            </div>
            <div class="modal-footer border-0 p-2"><button type="button" class="btn btn-light fw-bold w-100" data-bs-dismiss="modal">إغلاق</button></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     فورمات الإضافة والتعديل الأساسية
     ══════════════════════════════════════════════════════════ --}}

{{-- مودال التوريد للمخزن --}}
<div class="modal fade" id="buyStockModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form action="{{ route('inventory.store') }}" method="POST" class="modal-content" onsubmit="return validateBuyForm(event, this)">
            @csrf
            <div class="modal-header py-3" style="background: var(--hover-bg);">
                <h5 class="modal-title fw-bold text-primary"><i class="fa fa-truck-loading me-2"></i>توريد بضاعة للمخزن</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <div class="row g-3 mb-4 p-3 rounded border shadow-sm" style="background: var(--surface); border-color: var(--blue) !important;">
                    <div class="col-md-8">
                        <label class="fw-bold small text-primary mb-1"><i class="fa fa-building me-1"></i> المورد الرئيسي للفاتورة (اختر أو اكتب جديد)</label>
                        <div class="position-relative">
                            <input type="text" id="invoice_supplier" class="form-control form-control-lg fw-black text-primary border-primary" placeholder="اكتب للبحث أو لإضافة مورد..." required autocomplete="off" oninput="updateHiddenSuppliers()" value="{{ old('supplier_name.0') }}" style="padding-left: 35px;">
                            <i class="fa fa-chevron-down position-absolute text-primary" style="left: 15px; top: 50%; transform: translateY(-50%); cursor:pointer;"></i>
                            <div class="ac-dropdown" id="invoice_supplier_dropdown"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small text-primary mb-1"><i class="fa fa-store me-1"></i> المخزن المستلم</label>
                        <select name="store_id" class="form-select form-select-lg fw-bold border-primary">
                            <option value="1" {{ old('store_id') == 1 ? 'selected' : '' }}>المخزن الرئيسي (1)</option>
                            <option value="2" {{ old('store_id') == 2 ? 'selected' : '' }}>المخزن الفرعي (2)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold text-dark m-0" style="color: var(--text-main);">أصناف الفاتورة:</h5>
                    <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm rounded-pill" onclick="addPurchaseRow()"><i class="fa fa-plus me-1"></i> إضافة صنف للفاتورة</button>
                </div>
                
                <div class="table-responsive rounded border p-0 mb-4 shadow-sm" style="min-height: 200px; overflow:visible; background: var(--surface);">
                    <table class="table text-center align-middle mb-0" id="purchaseTable" style="color: var(--text-main);">
                        <thead style="background: var(--hover-bg); border-bottom: 1px solid var(--border);">
                            <tr>
                                <th width="35%" style="color: var(--text-muted);">اسم الصنف (اختر/اكتب)</th>
                                <th width="20%" style="color: var(--text-muted);">الفئة (اختر/اكتب)</th>
                                <th width="15%" style="color: var(--text-muted);">الكمية</th>
                                <th width="15%" style="color: var(--text-muted);">سعر الشراء</th>
                                <th width="10%" style="color: var(--text-muted);">سعر البيع</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="purchaseTableBody">
                            @if(old('product_name'))
                                @for($i = 0; $i < count(old('product_name')); $i++)
                                <tr>
                                    <td class="p-1 position-relative">
                                        <input type="hidden" name="supplier_name[]" class="sup-hidden-inp" value="{{ safeOld('supplier_name.'.$i) }}">
                                        <input type="text" name="product_name[]" class="form-control fw-bold item-combo" required placeholder="اسم الصنف" autocomplete="off" value="{{ safeOld('product_name.'.$i) }}" style="padding-left: 35px;">
                                        <i class="fa fa-chevron-down position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); cursor:pointer;"></i>
                                        <div class="ac-dropdown"></div>
                                    </td>
                                    <td class="p-1 position-relative">
                                        <input type="text" name="category[]" class="form-control fw-bold text-primary cat-combo" placeholder="الفئة" autocomplete="off" required value="{{ safeOld('category.'.$i) }}" style="padding-left: 35px;">
                                        <i class="fa fa-chevron-down position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); cursor:pointer;"></i>
                                        <div class="ac-dropdown"></div>
                                    </td>
                                    <td class="p-1"><input type="number" name="quantity[]" class="form-control text-center fw-bold border-primary qinp" step="1" required oninput="calcBulkTotal()" value="{{ safeOld('quantity.'.$i) }}"></td>
                                    <td class="p-1"><input type="number" name="purchase_price[]" class="form-control text-center fw-bold border-danger ppinp" step="0.01" required oninput="calcBulkTotal()" value="{{ safeOld('purchase_price.'.$i) }}"></td>
                                    <td class="p-1"><input type="number" name="selling_price[]" class="form-control text-center fw-bold border-success spinp" step="0.01" required value="{{ safeOld('selling_price.'.$i) }}"></td>
                                    <td class="p-1"><button type="button" class="btn btn-danger py-2 px-3 mt-1 rounded" onclick="this.closest('tr').remove(); calcBulkTotal();"><i class="fa fa-times"></i></button></td>
                                </tr>
                                @endfor
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <div class="p-3 rounded border" style="background: var(--hover-bg);">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2" style="border-color: var(--border) !important;">
                        <h5 class="fw-bold m-0" style="color: var(--text-main);">إجمالي الفاتورة:</h5>
                        <h3 class="m-0 text-danger fw-black"><span id="total_purchase_cost">0</span> ج.م</h3>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <div class="pay-type-card flex-fill"><input type="radio" class="d-none" name="payment_type" id="buy_cash" value="cash" {{ safeOld('payment_type')=='cash'?'checked':'' }} onchange="toggleBuyPayment()"><label for="buy_cash" class="d-block w-100 fw-bold"><i class="fa fa-money-bill-wave fs-4 text-success d-block mb-1"></i>كاش فوري</label></div>
                        <div class="pay-type-card flex-fill"><input type="radio" class="d-none" name="payment_type" id="buy_partial" value="partial" {{ safeOld('payment_type')=='partial'?'checked':'' }} onchange="toggleBuyPayment()"><label for="buy_partial" class="d-block w-100 fw-bold"><i class="fa fa-coins fs-4 text-warning d-block mb-1"></i>دفع جزء وآجل</label></div>
                        <div class="pay-type-card flex-fill"><input type="radio" class="d-none" name="payment_type" id="buy_ajel" value="ajel" {{ safeOld('payment_type')=='ajel'?'checked':'' }} onchange="toggleBuyPayment()"><label for="buy_ajel" class="d-block w-100 fw-bold"><i class="fa fa-file-invoice-dollar fs-4 text-danger d-block mb-1"></i>آجل بالكامل</label></div>
                    </div>
                    
                    <div id="buy_account_div" style="{{ in_array(safeOld('payment_type'), ['cash', 'partial']) ? 'display:flex;' : 'display:none;' }}" class="row g-2 align-items-end">
                        <div class="col-md-6" id="buy_paid_div" style="{{ safeOld('payment_type') == 'partial' ? 'display:block;' : 'display:none;' }}">
                            <label class="fw-bold small text-success mb-1">المدفوع للمورد (ج.م)</label>
                            <input type="number" name="paid_amount" id="buy_paid_input" value="{{ safeOld('paid_amount') }}" class="form-control border-success fw-bold text-center" placeholder="0" step="0.01" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small text-danger mb-1">سحب من خزنة:</label>
                            <select class="form-select border-danger fw-bold" name="withdrawal_account" id="buy_acc_select">
                                <option value="" disabled selected>اختر الخزنة...</option>
                                @foreach($accounts as $acc) 
                                    <option value="{{ $acc->id }}" {{ safeOld('withdrawal_account') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->account_name }} | متاح: {{ fmtMoney($acc->balance) }} ج.م
                                    </option> 
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-3 border-top" style="background: var(--surface); border-color: var(--border) !important;">
                <button type="submit" class="btn btn-primary fw-bold px-5 fs-5 rounded-pill w-100"><i class="fa fa-check me-2"></i> اعتماد الفاتورة</button>
            </div>
        </form>
    </div>
</div>

{{-- 🛒 مودال إذن الصرف والمبيعات (المعدل بالخانات الخاصة) --}}
<div class="modal fade" id="sellModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('inventory.sell') }}" method="POST" class="modal-content" onsubmit="return validateSellForm(event, this)">
            @csrf
            <div class="modal-header py-3" style="background: var(--hover-bg);">
                <h5 class="modal-title fw-bold text-success"><i class="fa fa-cart-arrow-down me-2"></i>إذن صرف فاتورة مبيعات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 text-start">
                    <button type="button" class="btn btn-success fw-bold px-4 shadow-sm rounded-pill" onclick="addSellRow()"><i class="fa fa-plus me-1"></i> إضافة منتج للفاتورة</button>
                </div>
                <div class="table-responsive rounded border p-0 shadow-sm mb-4" style="min-height:150px; overflow:visible; background: var(--surface);">
                    <table class="table text-center align-middle mb-0" id="sellTable" style="color: var(--text-main);">
                        <thead style="background: var(--hover-bg); border-bottom: 1px solid var(--border);">
                            <tr>
                                <th width="45%" style="color: var(--text-muted);">اسم الصنف والتفاصيل</th>
                                <th width="20%" style="color: var(--text-muted);">الكمية</th>
                                <th width="25%" style="color: var(--text-muted);">سعر البيع للوحدة</th>
                                <th width="10%"></th>
                            </tr>
                        </thead>
                        <tbody id="sellTableBody">
                            </tbody>
                    </table>
                </div>

                <div class="row g-3 mb-4 p-3 rounded border shadow-sm" style="background: var(--surface);">
                    <div class="col-md-6 position-relative">
                        <label class="fw-bold small text-primary mb-1">الهاتف</label>
                        <input type="text" name="customer_phone" id="cust_phone_input" value="{{ safeOld('customer_phone') }}" class="form-control fw-bold border-primary" placeholder="01xxxxxxxxx" autocomplete="off">
                        <div class="ac-dropdown" id="cust_phone_dropdown"></div>
                    </div>
                    <div class="col-md-6 position-relative">
                        <label class="fw-bold small text-primary mb-1">اسم العميل</label>
                        <input type="text" name="customer_name" id="cust_name_input" value="{{ safeOld('customer_name') }}" class="form-control fw-bold border-primary" placeholder="اسم العميل" autocomplete="off">
                        <div class="ac-dropdown" id="cust_ac_dropdown"></div>
                    </div>
                </div>
                
                <div class="p-3 rounded border" style="background: var(--hover-bg);">
                    <div class="row align-items-center mb-3 border-bottom pb-3" style="border-color: var(--border) !important;">
                        <div class="col-7">
                            <h5 class="fw-bold m-0" style="color: var(--text-main);">المطلوب سداده الشامل:</h5>
                            <h3 class="m-0 text-primary fw-black mt-2"><span id="sell_total">0</span> ج.م</h3>
                        </div>
                        <div class="col-5">
                            <label class="fw-bold small text-warning mb-1">خصم (تسوية)</label>
                            <input type="number" name="discount_amount" id="sell_discount" value="{{ safeOld('discount_amount') }}" class="form-control fw-bold border-warning text-center fs-5" placeholder="0" step="0.01" min="0" oninput="calcSellTotalTotal()">
                        </div>
                    </div>
                    
                    {{-- ══ طريقة الدفع: 3 خيارات ══ --}}
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <div class="pay-type-card flex-fill">
                            <input type="radio" class="d-none" name="payment_type" id="sell_cash" value="cash" {{ safeOld('payment_type')=='cash'?'checked':'' }} onchange="toggleSellPayment()">
                            <label for="sell_cash" class="d-block w-100 fw-bold"><i class="fa fa-money-bill-wave fs-4 text-success d-block mb-1"></i>كاش بالكامل</label>
                        </div>
                        <div class="pay-type-card flex-fill">
                            <input type="radio" class="d-none" name="payment_type" id="sell_partial" value="partial" {{ safeOld('payment_type')=='partial'?'checked':'' }} onchange="toggleSellPayment()">
                            <label for="sell_partial" class="d-block w-100 fw-bold"><i class="fa fa-coins fs-4 text-warning d-block mb-1"></i>جزء كاش + آجل</label>
                        </div>
                        <div class="pay-type-card flex-fill">
                            <input type="radio" class="d-none" name="payment_type" id="sell_ajel" value="ajel" {{ safeOld('payment_type')=='ajel'?'checked':'' }} onchange="toggleSellPayment()">
                            <label for="sell_ajel" class="d-block w-100 fw-bold"><i class="fa fa-file-invoice-dollar fs-4 text-danger d-block mb-1"></i>آجل بالكامل</label>
                        </div>
                    </div>

                    {{-- ══ حقول الخزنة والمبلغ المدفوع (تظهر حسب الاختيار) ══ --}}
                    <div id="sell_account_div" style="{{ in_array(safeOld('payment_type'), ['cash','partial']) ? 'display:flex;' : 'display:none;' }}" class="row g-2 align-items-end mb-2">
                        <div class="col-md-6" id="sell_paid_div" style="{{ safeOld('payment_type') == 'partial' ? 'display:block;' : 'display:none;' }}">
                            <label class="fw-bold small text-warning mb-1">المبلغ المدفوع مقدماً (ج.م)</label>
                            <input type="number" name="paid_amount" id="sell_paid_input" value="{{ safeOld('paid_amount') }}" class="form-control border-warning fw-bold text-center" placeholder="0" step="0.01" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small text-success mb-1">إيداع الكاش في خزنة:</label>
                            <select name="deposit_account" id="sell_acc_select" class="form-select border-success fw-bold">
                                <option value="" disabled selected>اختر الخزنة...</option>
                                @foreach($accounts as $acc) 
                                    <option value="{{ $acc->id }}" {{ safeOld('deposit_account') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->account_name }} | متاح: {{ fmtMoney($acc->balance) }} ج.م
                                    </option> 
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- ══ خزنة صرف مصاريف التكييف (تظهر تلقائياً لو في تكييف) ══ --}}
                    <div id="sell_expense_acc_div" style="{{ safeOld('expense_account') ? 'display:block;' : 'display:none;' }}" class="mt-2">
                        <label class="fw-bold small text-danger mb-1"><i class="fa fa-snowflake me-1 text-info"></i>خزنة صرف مصاريف التكييف (نقل + تركيب + خامات):</label>
                        <select name="expense_account" id="sell_expense_acc_select" class="form-select border-danger fw-bold">
                            <option value="" {{ safeOld('expense_account') ? '' : 'selected' }}>اختر الخزنة...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" data-balance="{{ $acc->balance }}" {{ safeOld('expense_account') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} | متاح: {{ fmtMoney($acc->balance) }} ج.م
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ══ عمولات المبيعات: حقل واحد ينزل دين على الشركة ══ --}}
                <div class="mt-3 p-2 rounded border d-flex align-items-center gap-3" style="background: var(--surface); border-color: #8b5cf6 !important;">
                    <label class="fw-bold small m-0 text-nowrap" style="color:#8b5cf6; min-width:fit-content;">
                        <i class="fa fa-user-tie me-1"></i> عمولات المبيعات (دين علينا):
                    </label>
                    <input type="number" name="commission_amount" id="comm_amount"
                           value="{{ safeOld('commission_amount', 0) }}"
                           class="form-control text-center fw-bold"
                           style="border-color:#8b5cf6; max-width:160px;"
                           step="0.01" min="0" placeholder="0 ج.م">
                    <span class="fw-bold small" style="color:var(--text-muted);">يُسجَّل باسم «عمولة مبيعات»</span>
                </div>

            </div>
            <div class="modal-footer p-3 border-top" style="background: var(--surface); border-color: var(--border) !important;">
                <button type="submit" class="btn btn-success fw-bold px-5 fs-5 rounded-pill w-100"><i class="fa fa-check me-2"></i> اعتماد فاتورة المبيعات</button>
            </div>
        </form>
    </div>
</div>

{{-- مودال تعديل الدفعة --}}
<div class="modal fade" id="editStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('inventory.update') }}" method="POST" class="modal-content" onsubmit="return disableSubmitBtn(this)">
            @csrf 
            <input type="hidden" name="sale_id" id="edit_id" value="{{ safeOld('sale_id') }}">
            <div class="modal-header py-3 bg-dark text-white"><h5 class="modal-title fw-bold"><i class="fa fa-pen me-2 text-primary"></i>تعديل بيانات الدفعة والصنف</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4" style="background: var(--bg);">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="small fw-bold mb-1" style="color: var(--text-main);">اسم الصنف</label>
                        <input type="text" name="product_name" id="edit_name" value="{{ safeOld('product_name') }}" class="form-control fw-bold fs-5" style="border-color: var(--border);" required>
                    </div>
                    <div class="col-md-6 position-relative">
                        <label class="small fw-bold text-primary mb-1">الفئة (اختر أو اكتب)</label>
                        <input type="text" name="category" id="edit_cat" class="form-control fw-bold border-primary cat-combo" placeholder="الفئة" required autocomplete="off" value="{{ safeOld('category') }}" style="padding-left: 35px;">
                        <i class="fa fa-chevron-down position-absolute text-muted" style="left: 15px; top: 40px; cursor:pointer;"></i>
                        <div class="ac-dropdown text-start" dir="rtl"></div>
                    </div>
                    <div class="col-md-6 position-relative">
                        <label class="small fw-bold text-primary mb-1">المورد (اختر أو اكتب)</label>
                        <input type="text" name="supplier_name" id="edit_sup" class="form-control fw-bold border-primary sup-combo" placeholder="المورد" required autocomplete="off" value="{{ safeOld('supplier_name') }}" style="padding-left: 35px;">
                        <i class="fa fa-chevron-down position-absolute text-muted" style="left: 15px; top: 40px; cursor:pointer;"></i>
                        <div class="ac-dropdown text-start" dir="rtl"></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="small fw-bold text-danger mb-1">سعر الشراء (التكلفة) <i class="fa fa-lock ms-1"></i></label>
                        <input type="number" id="edit_pp" value="{{ safeOld('purchase_price') }}" class="form-control text-center fw-bold fs-5" step="0.01" readonly disabled style="background: var(--hover-bg); color: var(--text-muted); cursor: not-allowed;">
                        <div class="small text-muted mt-1" style="line-height:1.5;">
                            <i class="fa fa-circle-info me-1"></i>
                            تعديل سعر الشراء بقى من <b>سجل العمليات</b> فقط (عشان بيترتب عليه تسوية ديون/خزنة تلقائية).
                        </div>
                    </div>
                    <div class="col-6"><label class="small fw-bold text-success mb-1">سعر البيع</label><input type="number" name="selling_price" id="edit_sp" value="{{ safeOld('selling_price') }}" class="form-control border-success text-center fw-bold text-success fs-5" step="0.01" required></div>
                </div>
            </div>
            <div class="modal-footer p-3 border-0" style="background: var(--surface);"><button type="submit" class="btn btn-dark w-100 fw-bold fs-5 rounded-pill">حفظ التعديلات</button></div>
        </form>
    </div>
</div>

{{-- مودال المرتجع الشامل الجديد --}}
<div class="modal fade" id="supplierReturnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('inventory.return_supplier') }}" method="POST" class="modal-content" onsubmit="return validateSupRetForm(event, this)">
            @csrf 
            <input type="hidden" name="sale_id" id="sup_ret_sale_id" value="{{ safeOld('sale_id') }}">
            <div class="modal-header py-3" style="background: var(--hover-bg); border-color: var(--border) !important;">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i class="fa fa-reply-all me-2 text-primary"></i>مرتجع لمورد (إخراج من المخزن)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center" style="background: var(--bg);">
                <h5 class="fw-bold mb-4 border-bottom pb-3" id="sup_ret_product_name" style="color: var(--text-main); border-color: var(--border) !important;"></h5>
                
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="fw-bold text-danger mb-1">الكمية المستردة</label>
                        <input type="number" step="1" name="quantity_returned" id="sup_ret_qty" value="{{ safeOld('quantity_returned', 1) }}" class="form-control text-center fw-bold fs-4 border-danger" required oninput="calcSupRetTotal()">
                    </div>
                    <div class="col-6">
                        <label class="fw-bold text-success mb-1">السعر المسترد للقطعة</label>
                        <input type="number" step="0.01" name="refund_price" id="sup_ret_price" value="{{ safeOld('refund_price') }}" class="form-control text-center fw-bold fs-4 border-success text-success" required oninput="calcSupRetTotal()">
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 border mb-4 d-flex justify-content-between align-items-center shadow-sm" style="border-color: var(--border) !important;">
                    <span class="fw-bold text-dark fs-5">إجمالي المرتجع:</span>
                    <span class="fw-black text-danger fs-4" id="sup_ret_total_disp">0 ج.م</span>
                </div>
                
                <div id="sup_ret_payment_section">
                <label class="fw-bold text-dark d-block text-start mb-2">طريقة تسوية المرتجع مع المورد:</label>
                <div class="d-flex gap-2 mb-3">
                    <div class="pay-type-card flex-fill"><input type="radio" class="d-none" name="payment_type" id="sup_ret_cash" value="cash" {{ safeOld('payment_type')=='cash'?'checked':'' }} onchange="toggleSupRetPayment()"><label for="sup_ret_cash" class="d-block w-100 fw-bold text-success">استرداد كامل كاش</label></div>
                    <div class="pay-type-card flex-fill"><input type="radio" class="d-none" name="payment_type" id="sup_ret_partial" value="partial" {{ safeOld('payment_type')=='partial'?'checked':'' }} onchange="toggleSupRetPayment()"><label for="sup_ret_partial" class="d-block w-100 fw-bold text-warning">جزء كاش والباقي آجل</label></div>
                    <div class="pay-type-card flex-fill"><input type="radio" class="d-none" name="payment_type" id="sup_ret_ajel" value="ajel" {{ safeOld('payment_type')=='ajel'?'checked':'' }} onchange="toggleSupRetPayment()"><label for="sup_ret_ajel" class="d-block w-100 fw-bold text-primary">آجل بالكامل (رصيد)</label></div>
                </div>
                
                <div id="sup_ret_paid_div" style="{{ safeOld('payment_type')=='partial' ? 'display:block;' : 'display:none;' }}" class="text-start mt-3 mb-3">
                    <label class="fw-bold text-warning mb-1">المبلغ المسترد نقداً الآن (ج.م):</label>
                    <input type="number" step="0.01" name="paid_amount" id="sup_ret_paid_input" value="{{ safeOld('paid_amount') }}" class="form-control fw-bold border-warning text-center fs-4 text-warning" placeholder="اكتب المبلغ الكاش...">
                </div>

                <div id="sup_ret_acc_div" style="{{ in_array(safeOld('payment_type'), ['cash','partial']) ? 'display:block;' : 'display:none;' }}" class="text-start mt-3">
                    <label class="fw-bold text-success mb-1">إيداع الكاش في خزنة:</label>
                    <select name="refund_account" id="sup_ret_acc_select" class="form-select fw-bold border-success fs-5">
                        <option value="" disabled selected>اختر الخزنة...</option>
                        @foreach($accounts as $acc) 
                            <option value="{{ $acc->id }}" {{ safeOld('refund_account') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->account_name }} | متاح: {{ fmtMoney($acc->balance) }} ج.م
                            </option> 
                        @endforeach
                    </select>
                </div>
                </div>
            </div>
            <div class="modal-footer p-3 border-0" style="background: var(--surface);"><button type="submit" class="btn btn-primary w-100 fw-bold fs-5 rounded-pill">تنفيذ المرتجع وتسوية الحساب</button></div>
        </form>
    </div>
</div>

{{-- مودال الحذف الذكي (خطأ إدخال / هالك) --}}
<div class="modal fade" id="deleteStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('inventory.delete') }}" method="POST" class="modal-content border-0 shadow-lg" onsubmit="return validateDeleteForm(event, this)">
            @csrf <input type="hidden" name="sale_id" id="del_id" value="{{ safeOld('sale_id') }}">
            
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h5 class="modal-title fw-bold m-0"><i class="fa fa-trash me-2"></i>حذف دفعة من المخزن</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                <div class="alert alert-warning fw-bold small mb-4 border-warning">
                    <i class="fa fa-info-circle me-1"></i> اختر سبب الحذف لتوجيه القيود المحاسبية بشكل صحيح.
                </div>

                <div class="mb-4">
                    <div class="form-check border p-3 rounded bg-white mb-2 shadow-sm" style="border-color: #3b82f6 !important;">
                        <input class="form-check-input ms-2" type="radio" name="delete_reason" id="dr_mistake" value="mistake" checked onchange="toggleDeleteReason()">
                        <label class="form-check-label fw-bold text-primary" for="dr_mistake">
                            <i class="fa fa-pen-to-square me-1"></i> سجلت الفاتورة بالخطأ (استرداد وتعديل ديون)
                        </label>
                    </div>
                    <div class="form-check border p-3 rounded bg-white shadow-sm" style="border-color: #ef4444 !important;">
                        <input class="form-check-input ms-2" type="radio" name="delete_reason" id="dr_damage" value="damage" onchange="toggleDeleteReason()">
                        <label class="form-check-label fw-bold text-danger" for="dr_damage">
                            <i class="fa fa-fire me-1"></i> بضاعة تالفة / هالك (تسجيل كخسارة)
                        </label>
                    </div>
                </div>

                <div id="mistake_div" class="p-3 bg-white border rounded-3 shadow-sm">
                    <p class="text-muted small fw-bold mb-3 border-bottom pb-2">بما أنك تحذف لخطأ في التسجيل، حدد المبالغ التي تريد إلغاءها:</p>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-success mb-1 small">هل دفعت فلوس كاش في هذه الدفعة؟ (استرداد)</label>
                        <input type="number" step="0.01" min="0" name="refund_amount" id="del_refund_amt" class="form-control text-center fw-bold border-success text-success fs-5" placeholder="المبلغ المسترد (إن وجد)" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-success mb-1 small">الخزنة التي سيعود إليها المبلغ المسترد:</label>
                        <select name="refund_account" id="del_refund_acc" class="form-select border-success fw-bold">
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc) <option value="{{ $acc->id }}">{{ $acc->account_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="mb-2 border-top pt-2">
                        <label class="fw-bold text-primary mb-1 small">هل تم تسجيل جزء كدين للمورد؟ (إلغاء دين)</label>
                        <input type="number" step="0.01" min="0" name="cancel_debt_amount" id="del_cancel_debt_amt" class="form-control text-center fw-bold border-primary text-primary fs-5" placeholder="المبلغ المراد إسقاطه من علينا" value="0">
                        <small class="text-muted" style="font-size: 0.7rem;">سيتم خصم هذا المبلغ من ديوننا لدى هذا المورد.</small>
                    </div>
                </div>

                <div id="damage_div" class="p-3 bg-danger bg-opacity-10 border border-danger rounded-3 shadow-sm" style="display: none;">
                    <h6 class="fw-bold text-danger mb-2"><i class="fa fa-triangle-exclamation me-1"></i> تنبيه محاسبي هام!</h6>
                    <p class="text-dark small fw-bold mb-0 lh-lg">
                        بما أن المنتج تالف في مخزنك، فإنه يعتبر خسارة عليك.<br>
                        - لن يتم استرداد أي أموال للخزنة.<br>
                        - لن يتم إسقاط مديونيتك للمورد (أنت مُلزم بسداد ثمنها لاحقاً).<br>
                        - سيتم تسجيل تكلفة هذه البضاعة كخسارة صريحة في دفتر المصروفات.
                    </p>
                </div>

            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="submit" class="btn btn-danger w-100 fw-bold rounded-pill fs-5">تأكيد تنفيذ الحذف</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let currentSaleForPrint = null;

    const dbSupplierDebts = @json($supplierDebts);
    const dbItems = {!! json_encode($mappedItems) !!};
    const dbItemNames = [...new Set(dbItems.map(i => i.name))];
    const dbCategories = {!! json_encode(array_values($categories)) !!};
    const dbSuppliers  = {!! json_encode(array_values($allSuppliersList)) !!};
    
    const dbCusts = {!! json_encode(
        \Illuminate\Support\Facades\DB::table('customers')
            ->select('name as customer_name', 'phone as customer_phone')
            ->union(
                \Illuminate\Support\Facades\DB::table('installments')
                ->select('customer_name', 'customer_phone')
                ->whereNotNull('customer_phone')
                ->where('customer_phone', '!=', '-')
            )
            ->get()
            ->filter(function($c) { return !empty($c->customer_phone); })
            ->unique('customer_phone')
            ->values()
    ) !!};

    const themeToggleBtn = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    themeToggleBtn.innerHTML = currentTheme === 'dark' ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';

    themeToggleBtn.addEventListener('click', () => {
        let theme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        themeToggleBtn.innerHTML = theme === 'dark' ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';
    });

    function disableSubmitBtn(form) {
        if(form.classList.contains('submitting')) return false;
        form.classList.add('submitting');
        let btn = form.querySelector('button[type="submit"]');
        if(btn) { btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> جاري التنفيذ...'; btn.disabled = true; }
        return true;
    }

    document.addEventListener("DOMContentLoaded", function() {
        @if(session('open_modal'))
            var errorModal = new bootstrap.Modal(document.getElementById("{{ session('open_modal') }}"));
            errorModal.show();
            if("{{ session('open_modal') }}" === "buyStockModal") calcBulkTotal();
            if("{{ session('open_modal') }}" === "sellModal") { restoreSellRows(); calcSellTotalTotal(); }
            if("{{ session('open_modal') }}" === "supplierReturnModal") calcSupRetTotal();
        @endif

        document.querySelectorAll('.cat-combo').forEach(el => attachCombo(el, dbCategories, 'fa-tags'));
        document.querySelectorAll('.sup-combo').forEach(el => attachCombo(el, dbSuppliers, 'fa-truck'));
        document.querySelectorAll('.item-combo').forEach(el => attachCombo(el, dbItemNames, 'fa-box'));
        
        setupCustAutocomplete();
    });

    function normAr(t) { 
        if(!t) return '';
        return t.replace(/[أإآا]/g,'ا').replace(/[يىئ]/g,'ي').replace(/[ةه]/g,'ه').replace(/\s+/g, ' ').trim().toLowerCase(); 
    }

    function attachCombo(inp, dataList, icon) {
        let wrapper = inp.parentElement;
        let drop = wrapper.querySelector('.ac-dropdown');
        let chevron = wrapper.querySelector('.fa-chevron-down');
        if(!drop) return;
        
        function render(list) {
            drop.innerHTML = '';
            if(list.length === 0) { drop.style.display = 'none'; return; }
            drop.style.display = 'block';
            list.forEach(item => {
                let d = document.createElement('div'); d.className = 'ac-item';
                d.innerHTML = `<i class="fa ${icon} text-primary me-2"></i> ${item}`;
                d.onclick = () => { 
                    inp.value = item; 
                    drop.style.display = 'none'; 
                    if(inp.oninput) inp.oninput();
                    if(inp.onchange) inp.onchange();
                };
                drop.appendChild(d);
            });
        }

        inp.addEventListener('focus', () => render(dataList));
        inp.addEventListener('input', () => {
            let val = normAr(inp.value);
            let filtered = dataList.filter(x => normAr(x).includes(val));
            render(filtered);
        });
        if(chevron) {
            chevron.addEventListener('click', (e) => {
                e.stopPropagation();
                if(drop.style.display === 'block') { drop.style.display = 'none'; }
                else { inp.focus(); render(dataList); }
            });
        }
        document.addEventListener('click', e => { if(e.target !== inp && e.target !== chevron) drop.style.display = 'none'; });
    }

    attachCombo(document.getElementById('filter_cat'), dbCategories, 'fa-tags');
    attachCombo(document.getElementById('filter_sup'), dbSuppliers, 'fa-truck');
    attachCombo(document.getElementById('invoice_supplier'), dbSuppliers, 'fa-building');

    function setupCustAutocomplete() {
        const custPhoneInput = document.getElementById('cust_phone_input');
        const custNameInput  = document.getElementById('cust_name_input');
        const custPhoneDrop  = document.getElementById('cust_phone_dropdown');
        const custNameDrop   = document.getElementById('cust_ac_dropdown');

        if(!custPhoneInput || !custNameInput) return;

        custPhoneInput.addEventListener('input', function() {
            let val = this.value.trim();
            custPhoneDrop.innerHTML = '';
            if(!val) { custPhoneDrop.style.display = 'none'; return; }
            
            let matches = dbCusts.filter(c => c.customer_phone && c.customer_phone.includes(val));
            if(matches.length > 0) {
                custPhoneDrop.style.display = 'block';
                matches.slice(0, 7).forEach(c => {
                    let d = document.createElement('div');
                    d.className = 'ac-item';
                    d.innerHTML = `<i class="fa fa-phone text-primary me-2"></i> ${c.customer_phone} <small class="text-muted ms-auto">${c.customer_name}</small>`;
                    d.onclick = () => {
                        custPhoneInput.value = c.customer_phone;
                        custNameInput.value  = c.customer_name;
                        custPhoneDrop.style.display = 'none';
                        // قفل الاسم فوراً بعد الاختيار من القائمة
                        lockNameField(c.customer_phone, c.customer_name);
                    };
                    custPhoneDrop.appendChild(d);
                });
            } else {
                custPhoneDrop.style.display = 'none';
            }
        });

        custNameInput.addEventListener('input', function() {
            let val = normAr(this.value);
            custNameDrop.innerHTML = '';
            if(!val) { custNameDrop.style.display = 'none'; return; }
            
            let matches = dbCusts.filter(c => c.customer_name && normAr(c.customer_name).includes(val));
            if(matches.length > 0) {
                custNameDrop.style.display = 'block';
                matches.slice(0, 7).forEach(c => {
                    let d = document.createElement('div');
                    d.className = 'ac-item';
                    d.innerHTML = `<i class="fa fa-user text-primary me-2"></i> ${c.customer_name} <small class="text-muted ms-auto" dir="ltr">${c.customer_phone}</small>`;
                    d.onclick = () => {
                        custNameInput.value  = c.customer_name;
                        custPhoneInput.value = c.customer_phone;
                        custNameDrop.style.display = 'none';
                        // قفل الاسم فوراً بعد الاختيار من القائمة
                        lockNameField(c.customer_phone, c.customer_name);
                    };
                    custNameDrop.appendChild(d);
                });
            } else {
                custNameDrop.style.display = 'none';
            }
        });

        document.addEventListener('click', e => {
            if(e.target !== custPhoneInput) custPhoneDrop.style.display = 'none';
            if(e.target !== custNameInput)  custNameDrop.style.display  = 'none';
        });
    }

    window.openDetailsModal = function(item) {
        document.getElementById('det_name').innerText = item.product_name;
        document.getElementById('det_date').innerText = item.purchase_date || item.created_at.substring(0, 10);
        document.getElementById('det_cat').innerText  = item.category || 'عام';
        document.getElementById('det_sup').innerText  = item.supplier_name || 'عام';
        document.getElementById('det_store').innerText= item.store_id == 1 ? 'المخزن الرئيسي' : 'المخزن الفرعي';
        let initialQty = parseFloat(item.quantity) || 0;
        let remQty     = parseFloat(item.remaining_quantity) || 0;
        let soldQty    = initialQty - remQty;
        document.getElementById('det_qty_total').innerText = initialQty.toLocaleString();
        document.getElementById('det_qty_sold').innerText  = soldQty.toLocaleString();
        document.getElementById('det_qty_rem').innerText   = remQty.toLocaleString();
        new bootstrap.Modal(document.getElementById('itemDetailsModal')).show();
    };

    // 🧺 عرض كل دفعات الصنف المجمّع — كل دفعة بسعرها وتاريخها وأزرار إجراءاتها الخاصة
    window.openBatchesModal = function(batches, fromStore, toStore) {
        if (!batches || !batches.length) return;
        document.getElementById('batches_name').innerText = batches[0].product_name;
        const body = document.getElementById('batches_body');
        body.innerHTML = batches.map(b => {
            const date = (b.purchase_date || b.created_at || '').toString().substring(0, 10);
            const name = (b.product_name || '').replace(/'/g, "\\'");
            const supplier = (b.supplier_name || '').replace(/'/g, "\\'");
            const returnBadge = (parseInt(b.is_return) === 1 || b.category === 'مرتجعات عملاء') ? ` <span class="badge-return"><i class="fa fa-rotate-left"></i> مرتجع</span>` : '';
            return `<tr>
                <td style="color: var(--text-muted);" dir="ltr">${date}${returnBadge}</td>
                <td class="text-start" style="color: var(--text-muted);">${b.supplier_name || '—'}</td>
                <td><span class="fw-black fs-6 ${parseFloat(b.remaining_quantity) < 5 ? 'text-danger' : 'text-success'}">${Number(b.remaining_quantity).toLocaleString()}</span></td>
                <td class="text-danger fw-bold">${Number(b.purchase_price).toLocaleString()} ج</td>
                <td class="text-success fw-bold">${Number(b.selling_price).toLocaleString()} ج</td>
                <td>
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn-action-sm btn-sell" onclick="bootstrap.Modal.getInstance(document.getElementById('batchesModal'))?.hide(); addSellRowPreFilled(${b.id}, '${name}', ${b.remaining_quantity}, ${b.selling_price});"><i class="fa fa-cart-arrow-down"></i></button>
                        <button class="btn-action-sm btn-ret" onclick="bootstrap.Modal.getInstance(document.getElementById('batchesModal'))?.hide(); openSupReturnModal(${b.id}, '${name}', ${b.remaining_quantity}, ${b.purchase_price});"><i class="fa fa-truck-loading"></i></button>
                        <button class="btn-action-sm btn-trans" onclick="bootstrap.Modal.getInstance(document.getElementById('batchesModal'))?.hide(); openTransferModal(${b.id}, '${name}', ${b.remaining_quantity}, ${fromStore}, ${toStore});"><i class="fa fa-exchange-alt"></i></button>
                        <button class="btn-action-sm btn-edit" onclick="bootstrap.Modal.getInstance(document.getElementById('batchesModal'))?.hide(); openEditModal(${b.id}, '${name}', '${(b.category||'').replace(/'/g, "\\'")}', '${supplier}', ${b.purchase_price}, ${b.selling_price});"><i class="fa fa-pen"></i></button>
                        <button class="btn-action-sm btn-del" onclick="bootstrap.Modal.getInstance(document.getElementById('batchesModal'))?.hide(); confirmProtectedDelete(${b.id}, '${supplier}', ${b.remaining_quantity}, ${b.purchase_price});"><i class="fa fa-trash"></i></button>
                        <button class="btn-action-sm btn-inv" onclick="bootstrap.Modal.getInstance(document.getElementById('batchesModal'))?.hide(); openInventoryAdjustModal(${b.id}, '${name}', ${b.remaining_quantity});" title="جرد وتسوية الكمية"><i class="fa fa-clipboard-list"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');
        new bootstrap.Modal(document.getElementById('batchesModal')).show();
    };

    window.openSaleDetails = function(sale) {
        currentSaleForPrint = sale; // حفظ للطباعة
        document.getElementById('sd_cust').innerText = sale.customer_name || 'عميل نقدي';
        document.getElementById('sd_prod').innerText = sale.product_name;
        document.getElementById('sd_disc').innerText = (sale.discount || 0).toLocaleString() + ' ج';
        document.getElementById('sd_total').innerText = (sale.total_after_interest || sale.cash_price).toLocaleString() + ' ج';
        document.getElementById('sd_down').innerText = (sale.down_payment || 0).toLocaleString() + ' ج';
        document.getElementById('sd_rem').innerText = (sale.remaining_balance || 0).toLocaleString() + ' ج';
        document.getElementById('sd_profit').innerText = '+' + (sale.profit || 0).toLocaleString() + ' ج';

        // إظهار المصاريف الإضافية لو فيه تكييفات
        let trans = parseFloat(sale.transport_cost) || 0;
        let inst = parseFloat(sale.installation_cost) || 0;
        let oth = parseFloat(sale.materials_cost) || 0;
        let extrasBox = document.getElementById('sd_extras_box');
        
        if(trans + inst + oth > 0) {
            extrasBox.style.display = 'block';
            document.getElementById('sd_trans').innerText = trans.toLocaleString() + ' ج';
            document.getElementById('sd_inst').innerText = inst.toLocaleString() + ' ج';
            document.getElementById('sd_oth').innerText = oth.toLocaleString() + ' ج';
        } else {
            extrasBox.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('saleDetailsModal')).show();
    };

    // 🖨️ دالة طباعة الفاتورة الشيك (المعدلة بالتفاصيل الكاملة)
    window.printInvoice = function() {
        if(!currentSaleForPrint) return;
        let printWin = window.open('', '', 'width=900,height=700');
        
        // تجهيز الأرقام والكميات
        let qty = parseFloat(currentSaleForPrint.quantity) || 1;
        let baseTotal = parseFloat(currentSaleForPrint.cash_price) || 0;
        let unitPrice = baseTotal / qty;

        let trans = parseFloat(currentSaleForPrint.transport_cost) || 0;
        let inst = parseFloat(currentSaleForPrint.installation_cost) || 0;
        let oth = parseFloat(currentSaleForPrint.materials_cost) || 0;
        let discount = parseFloat(currentSaleForPrint.discount) || 0;
        
        let paid = parseFloat(currentSaleForPrint.down_payment) || 0;
        let remaining = parseFloat(currentSaleForPrint.remaining_balance) || 0;
        let finalTotal = parseFloat(currentSaleForPrint.total_after_interest) || baseTotal;

        // تجهيز كود المصروفات الإضافية (يظهر فقط إذا كان هناك قيم)
        let extrasHtml = '';
        if(trans + inst + oth > 0) {
            extrasHtml = `
                <tr style="background:#eff6ff;"><td colspan="4" style="text-align:right; font-weight:900; color:#2563eb; padding-right:20px;">⚙️ مصروفات التجهيز الإضافية:</td></tr>
                ${trans > 0 ? `<tr><td colspan="3" style="text-align:right; padding-right:30px;">تكلفة النقل</td><td style="font-weight:900;">${trans.toLocaleString()} ج.م</td></tr>` : ''}
                ${inst > 0 ? `<tr><td colspan="3" style="text-align:right; padding-right:30px;">التركيب والخامات</td><td style="font-weight:900;">${inst.toLocaleString()} ج.م</td></tr>` : ''}
                ${oth > 0 ? `<tr><td colspan="3" style="text-align:right; padding-right:30px;">مصروفات أخرى</td><td style="font-weight:900;">${oth.toLocaleString()} ج.م</td></tr>` : ''}
            `;
        }

        // تجهيز كود الخصم (يظهر فقط إذا كان هناك خصم)
        let discountHtml = '';
        if(discount > 0) {
            discountHtml = `
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:900;">الخصم الممنوح:</td>
                    <td style="color:#ea580c; font-weight:900;" dir="ltr">- ${discount.toLocaleString()} ج.م</td>
                </tr>
            `;
        }

        printWin.document.write(`
            <html dir="rtl" lang="ar">
            <head>
                <title>فاتورة مبيعات - الضبع</title>
                <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
                <style>
                    body { font-family: 'Cairo', sans-serif; background: #fff; padding: 40px; color: #0f172a; }
                    .invoice-box { max-width: 800px; margin: auto; border: 2px solid #e2e8f0; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
                    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1e293b; padding-bottom: 20px; margin-bottom: 30px; }
                    .header h1 { margin: 0; color: #1e293b; font-weight: 900; font-size: 34px; }
                    .header p { margin: 5px 0 0; color: #64748b; font-weight: 700; font-size: 16px; }
                    .info-box { display: flex; justify-content: space-between; background: #f8fafc; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
                    .info-box div { font-size: 16px; font-weight: 700; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                    th { background: #1e293b; color: #fff; padding: 12px; font-size: 15px; border: 1px solid #1e293b; text-align: center; }
                    td { padding: 12px; border: 1px solid #e2e8f0; font-size: 15px; font-weight: 700; text-align: center; }
                    .totals-wrapper { width: 60%; margin-right: auto; border: 2px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
                    .totals-row { display: flex; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #e2e8f0; font-size: 16px; font-weight: 700; }
                    .totals-row:last-child { border-bottom: none; }
                    .totals-row.final { background: #10b981; color: white; font-size: 20px; font-weight: 900; }
                    .totals-row.paid { background: #eff6ff; color: #2563eb; }
                    .totals-row.rem { background: #fef2f2; color: #dc2626; }
                    .footer { text-align: center; margin-top: 40px; font-weight: 700; color: #94a3b8; font-size: 15px; border-top: 1px dashed #e2e8f0; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class="invoice-box">
                    <div class="header">
                        <div>
                            <h1>شركة الضبع</h1>
                            <p>للتجارة وأنظمة التقسيط والمقاولات</p>
                        </div>
                        <div style="text-align:left;">
                            <h2 style="margin:0; color:#0f172a;">فاتورة مبيعات</h2>
                            <p>التاريخ: ${currentSaleForPrint.created_at ? currentSaleForPrint.created_at.substring(0, 10) : new Date().toLocaleDateString('en-CA')}</p>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <div>العميل: <span style="color:#2563eb; font-weight:900;">${currentSaleForPrint.customer_name || 'عميل نقدي'}</span></div>
                        <div>رقم الفاتورة: <span style="color:#2563eb; font-weight:900;">#${currentSaleForPrint.id || '-'}</span></div>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th style="width:40%; text-align:right;">اسم المنتج / البيان</th>
                                <th>الكمية</th>
                                <th>سعر الوحدة</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:right; font-weight:900;">${currentSaleForPrint.product_name || '---'}</td>
                                <td>${qty}</td>
                                <td>${unitPrice.toLocaleString()} ج.م</td>
                                <td style="font-weight:900;">${baseTotal.toLocaleString()} ج.م</td>
                            </tr>
                            ${extrasHtml}
                            ${discountHtml}
                        </tbody>
                    </table>

                    <div class="totals-wrapper">
                        <div class="totals-row final">
                            <span>الصافي المطلوب سداده:</span>
                            <span>${finalTotal.toLocaleString()} ج.م</span>
                        </div>
                        <div class="totals-row paid">
                            <span>المدفوع (مقدم / كاش):</span>
                            <span>${paid.toLocaleString()} ج.م</span>
                        </div>
                        <div class="totals-row rem">
                            <span>المتبقي (آجل):</span>
                            <span>${remaining.toLocaleString()} ج.م</span>
                        </div>
                    </div>

                    <div class="footer">شكراً لثقتكم واختياركم شركة الضبع</div>
                </div>
            </body>
            </html>
        `);
        printWin.document.close();
        setTimeout(() => { printWin.print(); printWin.close(); }, 500);
    }

    window.openSupplierLogModal = function(supplierName, detailsArray, totalCost) {
        document.getElementById('sup_log_name').innerText = supplierName;
        document.getElementById('sup_log_total').innerText = parseFloat(totalCost).toLocaleString('en-US') + ' ج.م';
        let tbody = document.getElementById('sup_log_body');
        tbody.innerHTML = '';
        detailsArray.forEach(item => {
            let rowTotal = parseFloat(item.quantity) * parseFloat(item.purchase_price);
            let tr = `<tr>
                <td dir="ltr" class="text-muted small fw-bold">${item.created_at ? item.created_at.substring(0, 10) : '—'}</td>
                <td class="text-start fw-bold text-primary">${item.product_name}</td>
                <td class="fw-black">${item.quantity}</td>
                <td class="text-danger fw-bold">${parseFloat(item.purchase_price).toLocaleString()} ج</td>
                <td class="text-danger fw-black fs-6">${rowTotal.toLocaleString()} ج</td>
            </tr>`;
            tbody.innerHTML += tr;
        });
        new bootstrap.Modal(document.getElementById('supplierLogModal')).show();
    }

    window.openCustReturnDetails = function(ret) {
        document.getElementById('crd_prod').innerText = ret.product_name;
        document.getElementById('crd_qty').innerText = ret.quantity_returned;
        document.getElementById('crd_refund').innerText = parseFloat(ret.return_price).toLocaleString() + ' ج';
        document.getElementById('crd_loss').innerText = '-' + parseFloat(ret.loss_amount).toLocaleString() + ' ج';
        document.getElementById('crd_notes').innerText = ret.notes || '—';
        new bootstrap.Modal(document.getElementById('custReturnDetailsModal')).show();
    };

    window.openSupReturnDetails = function(supRet) {
        const money = v => fmtMoney(v) + ' ج';
        document.getElementById('srd_product').innerText  = supRet.product_name || '—';
        document.getElementById('srd_supplier').innerText = supRet.supplier_name || 'غير محدد';
        document.getElementById('srd_qty').innerText      = supRet.quantity;
        document.getElementById('srd_pp').innerText       = money(supRet.purchase_price);
        document.getElementById('srd_rp').innerText       = money(supRet.return_price);
        document.getElementById('srd_refund').innerText   = money(supRet.total_refunded);
        document.getElementById('srd_loss').innerText     = (parseFloat(supRet.loss_amount) > 0) ? money(supRet.loss_amount) : 'لا يوجد';
        document.getElementById('srd_account').innerText  = supRet.refund_account || '—';
        document.getElementById('srd_notes').innerText    = supRet.notes || '—';
        document.getElementById('srd_date').innerText     = supRet.created_at ? supRet.created_at.substring(0, 16) : '—';
        new bootstrap.Modal(document.getElementById('supReturnDetailsModal')).show();
    };

    window.openEditModal = function(id, name, cat, sup, pp, sp) { 
        if(!document.getElementById('edit_id').value || document.getElementById('edit_id').value != id) {
            document.getElementById('edit_id').value = id; 
            document.getElementById('edit_name').value = name; 
            document.getElementById('edit_cat').value = cat; 
            document.getElementById('edit_sup').value = sup; 
            document.getElementById('edit_pp').value = pp; 
            document.getElementById('edit_sp').value = sp; 
        }
        new bootstrap.Modal(document.getElementById('editStockModal')).show(); 
    }

    window.toggleDeleteReason = function() {
        let isMistake = document.getElementById('dr_mistake').checked;
        document.getElementById('mistake_div').style.display = isMistake ? 'block' : 'none';
        document.getElementById('damage_div').style.display = isMistake ? 'none' : 'block';
    }
    
    window.validateDeleteForm = function(e, form) {
        let isMistake = document.getElementById('dr_mistake').checked;
        if(isMistake) {
            let refAmt = parseFloat(document.getElementById('del_refund_amt').value) || 0;
            let refAcc = document.getElementById('del_refund_acc').value;
            if(refAmt > 0 && !refAcc) {
                Swal.fire('خطأ', 'لقد أدخلت مبلغ للاسترداد، يجب اختيار الخزنة التي سيتم إرجاع المبلغ إليها!', 'error');
                e.preventDefault(); return false;
            }
        }
        return disableSubmitBtn(form);
    }

    window.confirmProtectedDelete = async function(id, supName, qty, pp) {
        const { value: p } = await Swal.fire({ title:'مطلوب الرمز السري', text:'أدخل الرمز السري للحذف:', input:'password', showCancelButton:true, confirmButtonColor:'#dc2626' });
        if(p === '233') { 
            document.getElementById('del_id').value = id; 
            document.getElementById('dr_mistake').checked = true;
            toggleDeleteReason();

            let totalItemCost = parseFloat(qty) * parseFloat(pp);
            let supDebt = parseFloat(dbSupplierDebts[supName] || 0);

            let cancelDebt = 0; let refundCash = 0;

            if (supDebt >= totalItemCost) { cancelDebt = totalItemCost; refundCash = 0; } 
            else if (supDebt > 0) { cancelDebt = supDebt; refundCash = totalItemCost - supDebt; } 
            else { cancelDebt = 0; refundCash = totalItemCost; }

            document.getElementById('del_refund_amt').value = refundCash;
            document.getElementById('del_cancel_debt_amt').value = cancelDebt;

            new bootstrap.Modal(document.getElementById('deleteStockModal')).show(); 
        }
        else if(p) { Swal.fire('خطأ!', 'الرمز السري غير صحيح.', 'error'); }
    }

    window.openSupReturnModal = function(id, name, qty, pp) { 
        if(!document.getElementById('sup_ret_sale_id').value || document.getElementById('sup_ret_sale_id').value != id) {
            document.getElementById('sup_ret_sale_id').value = id; 
            document.getElementById('sup_ret_product_name').innerText = name; 
            document.getElementById('sup_ret_qty').max = qty; document.getElementById('sup_ret_qty').value = 1; 
            document.getElementById('sup_ret_price').value = pp; 
            calcSupRetTotal();
        }
        new bootstrap.Modal(document.getElementById('supplierReturnModal')).show(); 
    }

    window.calcSupRetTotal = function() {
        let qty = parseFloat(document.getElementById('sup_ret_qty').value) || 0;
        let price = parseFloat(document.getElementById('sup_ret_price').value) || 0;
        let total = qty * price;
        let disp = document.getElementById('sup_ret_total_disp');
        if(disp) disp.innerText = total.toLocaleString('en-US') + ' ج.م';

        const paymentSection = document.getElementById('sup_ret_payment_section');
        const accSelect = document.getElementById('sup_ret_acc_select');
        const paidInput = document.getElementById('sup_ret_paid_input');

        if(total <= 0) {
            if(paymentSection) paymentSection.style.display = 'none';
            accSelect.removeAttribute('required');
            paidInput.removeAttribute('required');
            document.querySelectorAll('#supplierReturnModal input[name="payment_type"]').forEach(r => r.checked = false);
        } else {
            if(paymentSection) paymentSection.style.display = 'block';
        }
    }

    window.toggleSupRetPayment = function() {
        const cash = document.getElementById('sup_ret_cash').checked;
        const partial = document.getElementById('sup_ret_partial').checked;
        
        const accDiv = document.getElementById('sup_ret_acc_div');
        const paidDiv = document.getElementById('sup_ret_paid_div');
        const accSelect = document.getElementById('sup_ret_acc_select');
        const paidInput = document.getElementById('sup_ret_paid_input');
        
        if(cash || partial) {
            accDiv.style.display = 'block';
            accSelect.setAttribute('required', 'required');
            if(partial) {
                paidDiv.style.display = 'block';
                paidInput.setAttribute('required', 'required');
            } else {
                paidDiv.style.display = 'none';
                paidInput.removeAttribute('required');
            }
        } else {
            accDiv.style.display = 'none';
            paidDiv.style.display = 'none';
            accSelect.removeAttribute('required');
            paidInput.removeAttribute('required');
        }
    }

    window.validateSupRetForm = function(e, form) {
        if(document.getElementById('sup_ret_partial').checked) {
            let qty = parseFloat(document.getElementById('sup_ret_qty').value) || 0;
            let price = parseFloat(document.getElementById('sup_ret_price').value) || 0;
            let total = qty * price;
            let paid = parseFloat(document.getElementById('sup_ret_paid_input').value) || 0;
            if(paid > total) {
                Swal.fire('خطأ!', 'المبلغ المسترد نقداً لا يمكن أن يكون أكبر من إجمالي المرتجع.', 'error');
                e.preventDefault(); return false;
            }
        }
        return disableSubmitBtn(form);
    }

    window.updateHiddenSuppliers = function() {
        let supVal = document.getElementById('invoice_supplier').value || '';
        document.querySelectorAll('.sup-hidden-inp').forEach(inp => inp.value = supVal);
    }

    window.toggleBuyPayment = function() {
        const cash = document.getElementById('buy_cash').checked;
        const partial = document.getElementById('buy_partial').checked;
        const div = document.getElementById('buy_account_div');
        const accSelect = document.getElementById('buy_acc_select');
        const paidDiv = document.getElementById('buy_paid_div');
        const paidInput = document.getElementById('buy_paid_input');
        
        if(cash || partial) {
            div.style.display = 'flex'; accSelect.setAttribute('required', 'required');
            if(partial) { paidDiv.style.display = 'block'; paidInput.setAttribute('required', 'required'); calcBulkTotal(); } 
            else { paidDiv.style.display = 'none'; paidInput.removeAttribute('required'); }
        } else { div.style.display = 'none'; accSelect.removeAttribute('required'); paidInput.removeAttribute('required'); }
    }

    window.validateBuyForm = function(e, form) {
        let total = parseInt(document.getElementById('total_purchase_cost').innerText.replace(/,/g, '')) || 0;
        let paid = total;
        
        if(document.getElementById('buy_partial').checked) {
            paid = parseInt(document.getElementById('buy_paid_input').value) || 0;
            if(paid > total) { Swal.fire('خطأ!', 'المبلغ المدفوع للمورد أكبر من إجمالي الفاتورة.', 'error'); e.preventDefault(); return false; }
        }

        if(document.getElementById('buy_cash').checked || document.getElementById('buy_partial').checked) {
            let accSelect = document.getElementById('buy_acc_select');
            let accId = accSelect.value;
            if(!accId) {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'يجب اختيار الخزنة أولاً!' });
                e.preventDefault(); return false;
            }
            let balance = parseFloat(dbAccountBalances[accId] || 0);
            if(balance < paid) {
                let accName = accSelect.options[accSelect.selectedIndex].text.split('|')[0].trim();
                Swal.fire({
                    icon: 'error',
                    title: 'رصيد الخزنة غير كافٍ!',
                    html: `خزنة <b>${accName}</b> متاح بها <b style="color:#ef4444">${balance.toLocaleString('en-US')} ج.م</b> فقط،<br>
                           والمطلوب سداده للمورد <b style="color:#2563eb">${paid.toLocaleString('en-US')} ج.م</b>.<br><br>
                           يرجى اختيار خزنة أخرى أو تقليل المبلغ المدفوع.`,
                    confirmButtonColor: '#dc2626'
                });
                e.preventDefault(); return false;
            }
        }
        return disableSubmitBtn(form);
    }

    window.addPurchaseRow = function() {
        const tb = document.getElementById('purchaseTableBody');
        const tr = document.createElement('tr');
        let currentSup = document.getElementById('invoice_supplier') ? document.getElementById('invoice_supplier').value : '';
        tr.innerHTML = `
            <td class="p-1 position-relative">
                <input type="hidden" name="supplier_name[]" class="sup-hidden-inp" value="${currentSup}">
                <input type="text" name="product_name[]" class="form-control fw-bold item-combo" required placeholder="اسم الصنف" autocomplete="off" style="padding-left: 35px;">
                <i class="fa fa-chevron-down position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); cursor:pointer;"></i>
                <div class="ac-dropdown text-start" dir="rtl"></div>
            </td>
            <td class="p-1 position-relative">
                <input type="text" name="category[]" class="form-control fw-bold text-primary cat-combo" placeholder="الفئة" required autocomplete="off" style="padding-left: 35px;">
                <i class="fa fa-chevron-down position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); cursor:pointer;"></i>
                <div class="ac-dropdown text-start" dir="rtl"></div>
            </td>
            <td class="p-1"><input type="number" name="quantity[]" class="form-control text-center fw-bold border-primary qinp" value="1" step="1" required oninput="calcBulkTotal()"></td>
            <td class="p-1"><input type="number" name="purchase_price[]" class="form-control text-center fw-bold border-danger ppinp" step="0.01" required oninput="calcBulkTotal()"></td>
            <td class="p-1"><input type="number" name="selling_price[]" class="form-control text-center fw-bold border-success spinp" step="0.01" required></td>
            <td class="p-1"><button type="button" class="btn btn-danger py-2 px-3 mt-1 rounded" onclick="this.closest('tr').remove(); calcBulkTotal();"><i class="fa fa-times"></i></button></td>
        `;
        tb.appendChild(tr);
        attachCombo(tr.querySelector('.item-combo'), dbItemNames, 'fa-box');
        attachCombo(tr.querySelector('.cat-combo'), dbCategories, 'fa-tags');
    }

    window.calcBulkTotal = function() {
        let t = 0; 
        document.querySelectorAll('#purchaseTableBody tr').forEach(r => t += (parseFloat(r.querySelector('.qinp').value)||0)*(parseFloat(r.querySelector('.ppinp').value)||0));
        document.getElementById('total_purchase_cost').innerText = t.toLocaleString('en-US');
    }

    const dbAccountBalances = {!! json_encode(collect($accounts)->mapWithKeys(fn($a) => [$a->id => $a->balance])) !!};

    window.validateSellForm = function(e, form) {
        const cashChecked    = document.getElementById('sell_cash').checked;
        const partialChecked = document.getElementById('sell_partial').checked;
        const ajelChecked    = document.getElementById('sell_ajel').checked;

        if (!cashChecked && !partialChecked && !ajelChecked) {
            Swal.fire({ icon: 'error', title: 'خطأ', text: 'يرجى اختيار طريقة الدفع!' });
            e.preventDefault(); return false;
        }

        // التحقق من خزنة الإيداع في حالة كاش أو جزئي
        if (cashChecked || partialChecked) {
            const accId = document.getElementById('sell_acc_select').value;
            if (!accId) {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'يجب اختيار خزنة إيداع الكاش!' });
                e.preventDefault(); return false;
            }
        }

        // التحقق من المبلغ المدفوع في حالة جزئي
        if (partialChecked) {
            let total = parseFloat(document.getElementById('sell_total').innerText.replace(/,/g, '')) || 0;
            let paid  = parseFloat(document.getElementById('sell_paid_input').value) || 0;
            if (paid <= 0) {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'يرجى إدخال المبلغ المدفوع مقدماً!' });
                e.preventDefault(); return false;
            }
            if (paid >= total) {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'المبلغ المدفوع يجب أن يكون أقل من الإجمالي في حالة الدفع الجزئي!' });
                e.preventDefault(); return false;
            }
        }

        // التحقق من وجود اسم العميل في حالة الآجل
        if (ajelChecked) {
            const custName = document.getElementById('cust_name_input').value.trim();
            if (!custName) {
                Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'يرجى إدخال اسم العميل للبيع الآجل حتى يُسجَّل الدين عليه!' });
                e.preventDefault(); return false;
            }
        }

        // ❄️ التحقق من خزنة صرف مصاريف التكييف (نقل + تركيب + خامات) ومن كفاية الرصيد
        let acExpenses = 0;
        document.querySelectorAll('#sellTableBody tr').forEach(r => {
            let trans = parseFloat(r.querySelector('.strans-inp')?.value) || 0;
            let inst  = parseFloat(r.querySelector('.sinst-inp')?.value) || 0;
            let oth   = parseFloat(r.querySelector('.soth-inp')?.value) || 0;
            acExpenses += trans + inst + oth;
        });
        if (acExpenses > 0) {
            const expSelect = document.getElementById('sell_expense_acc_select');
            const expAccId  = expSelect ? expSelect.value : '';
            if (!expAccId) {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'يجب اختيار خزنة لصرف مصاريف التكييف (نقل + تركيب + خامات)!' });
                e.preventDefault(); return false;
            }
            const opt = expSelect.options[expSelect.selectedIndex];
            const bal = parseFloat(opt.getAttribute('data-balance')) || 0;
            if (bal < acExpenses) {
                Swal.fire({
                    icon: 'error',
                    title: 'رصيد غير كافٍ',
                    html: `رصيد الخزنة المختارة لا يكفي لصرف مصاريف التكييف.<br>المطلوب: <b>${acExpenses.toLocaleString('en-US')}</b> ج.م<br>المتاح: <b>${bal.toLocaleString('en-US')}</b> ج.م`
                });
                e.preventDefault(); return false;
            }
        }

        return disableSubmitBtn(form);
    }

    window.toggleSellPayment = function() {
        const cash    = document.getElementById('sell_cash').checked;
        const partial = document.getElementById('sell_partial').checked;
        const ajel    = document.getElementById('sell_ajel').checked;

        const accountDiv  = document.getElementById('sell_account_div');
        const accSelect   = document.getElementById('sell_acc_select');
        const paidDiv     = document.getElementById('sell_paid_div');
        const paidInput   = document.getElementById('sell_paid_input');

        // إظهار/إخفاء خزنة الإيداع والمبلغ
        if (cash) {
            accountDiv.style.display = 'flex';
            paidDiv.style.display = 'none';
            paidInput.removeAttribute('required');
            accSelect.setAttribute('required', 'required');
        } else if (partial) {
            accountDiv.style.display = 'flex';
            paidDiv.style.display = 'block';
            paidInput.setAttribute('required', 'required');
            accSelect.setAttribute('required', 'required');
        } else if (ajel) {
            // آجل بالكامل: لا خزنة إيداع ولا مبلغ مقدم
            accountDiv.style.display = 'none';
            paidDiv.style.display = 'none';
            paidInput.removeAttribute('required');
            accSelect.removeAttribute('required');
        } else {
            accountDiv.style.display = 'none';
            accSelect.removeAttribute('required');
            paidInput.removeAttribute('required');
        }
        calcSellTotalTotal();
    }

    // 💡 إضافة المبيعات بتظهر مصاريف التكييف لو الصنف تكييفات
   // 💡 إضافة المبيعات بتظهر مصاريف التكييف لو الصنف تكييفات
    window.addSellRow = function() {
        const tb = document.getElementById('sellTableBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="position-relative p-1">
                <input type="hidden" name="sale_id[]" class="sid-inp">
                <input type="hidden" class="scat-inp">
                <input type="text" name="old_product_name[]" class="form-control fw-bold sinp" required autocomplete="off" placeholder="اكتب للبحث عن الصنف...">
                <div class="ac-dropdown text-start" dir="rtl"></div>
                
                <div class="sell-extra-fields mt-2 p-2 bg-light border rounded" style="display:none;">
                    <div class="d-flex gap-1 mb-1">
                        <span class="badge bg-danger d-flex align-items-center justify-content-start px-2 gap-2" style="min-width: 95px; font-size: 0.8rem;">
                            <i class="fa fa-truck-fast"></i> النقل
                        </span>
                        <input type="number" name="transport_cost[]" class="form-control form-control-sm text-center text-danger fw-bold strans-inp" placeholder="0" step="any" value="0" oninput="calcSellTotalTotal()">
                    </div>
                    <div class="d-flex gap-1 mb-1">
                        <span class="badge bg-success d-flex align-items-center justify-content-start px-2 gap-2" style="min-width: 95px; font-size: 0.8rem;">
                            <i class="fa fa-screwdriver-wrench"></i> التركيب
                        </span>
                        <input type="number" name="installation_cost[]" class="form-control form-control-sm text-center text-success fw-bold sinst-inp" placeholder="0" step="any" value="0" oninput="calcSellTotalTotal()">
                    </div>
                    <div class="d-flex gap-1">
                        <span class="badge bg-secondary d-flex align-items-center justify-content-start px-2 gap-2" style="min-width: 95px; font-size: 0.8rem;">
                            <i class="fa fa-toolbox"></i> الخامات
                        </span>
                        <input type="number" name="materials_cost[]" class="form-control form-control-sm text-center text-secondary fw-bold soth-inp" placeholder="0" step="any" value="0" oninput="calcSellTotalTotal()">
                    </div>
                </div>
            </td>
            <td class="p-1"><input type="number" name="sell_quantity[]" class="form-control text-center fw-bold border-primary sqinp" step="1" required oninput="calcSellTotalTotal()"></td>
            <td class="p-1"><input type="number" name="selling_price[]" class="form-control text-center fw-bold border-success ssp-inp text-success" step="0.01" required oninput="calcSellTotalTotal()"></td>
            <td class="p-1"><button type="button" class="btn btn-danger py-2 px-3 mt-1 rounded" onclick="this.closest('tr').remove(); calcSellTotalTotal();"><i class="fa fa-trash"></i></button></td>
        `;
        tb.appendChild(tr);
        setupSellAutocomplete(tr);
    }

    // 💾 إعادة بناء صفوف فاتورة البيع من البيانات المحفوظة بعد فشل التحقق في السيرفر
    window.restoreSellRows = function() {
        const oldSaleIds = @json(old('sale_id', []));
        const oldNames   = @json(old('old_product_name', []));
        const oldQtys    = @json(old('sell_quantity', []));
        const oldPrices  = @json(old('selling_price', []));
        const oldTrans   = @json(old('transport_cost', []));
        const oldInst    = @json(old('installation_cost', []));
        const oldOth     = @json(old('materials_cost', []));
        if (!Array.isArray(oldSaleIds) || oldSaleIds.length === 0) return;

        const tb = document.getElementById('sellTableBody');
        tb.innerHTML = '';
        for (let i = 0; i < oldSaleIds.length; i++) {
            addSellRow();
            const tr = tb.lastElementChild;
            const tCost = parseFloat(oldTrans[i]) || 0;
            const iCost = parseFloat(oldInst[i]) || 0;
            const oCost = parseFloat(oldOth[i]) || 0;

            tr.querySelector('.sid-inp').value    = oldSaleIds[i] ?? '';
            tr.querySelector('.sinp').value       = oldNames[i] ?? '';
            tr.querySelector('.sqinp').value      = oldQtys[i] ?? '';
            tr.querySelector('.ssp-inp').value    = oldPrices[i] ?? '';
            tr.querySelector('.strans-inp').value = tCost;
            tr.querySelector('.sinst-inp').value  = iCost;
            tr.querySelector('.soth-inp').value   = oCost;

            let itemData = (typeof dbItems !== 'undefined') ? dbItems.find(it => it.id == oldSaleIds[i]) : null;
            let cat = itemData ? itemData.cat : '';
            tr.querySelector('.scat-inp').value = cat;
            if (itemData) tr.querySelector('.sqinp').max = itemData.qty;
            if (cat === 'تكييفات' || (tCost + iCost + oCost) > 0) {
                tr.querySelector('.sell-extra-fields').style.display = 'block';
            }
        }
    }

    function setupSellAutocomplete(tr) {
        const inp = tr.querySelector('.sinp'), div = inp.nextElementSibling, sid = tr.querySelector('.sid-inp');
        const qinp = tr.querySelector('.sqinp'), spinp = tr.querySelector('.ssp-inp');
        
        inp.addEventListener('input', () => {
            const v = normAr(inp.value); div.innerHTML = '';
            if(!v) { div.style.display = 'none'; return; }

            const m = dbItems.filter(i => i.qty > 0 && normAr(i.name).includes(v));
            if(m.length) {
                div.style.display = 'block';
                m.slice(0,7).forEach(i => {
                    const d = document.createElement('div'); d.className = 'ac-item'; 
                    d.innerHTML = `<span class="fw-bold">${i.name}</span> <span class="badge bg-danger ms-auto px-2 py-1">متاح: ${i.qty}</span>`;
                    d.onclick = () => { 
                        inp.value = i.name; sid.value = i.id; qinp.max = i.qty; qinp.value = 1; spinp.value = i.sp; 
                        
                        tr.querySelector('.scat-inp').value = i.cat;
                        if(i.cat === 'تكييفات') {
                            tr.querySelector('.sell-extra-fields').style.display = 'block';
                        } else {
                            tr.querySelector('.sell-extra-fields').style.display = 'none';
                            tr.querySelector('.strans-inp').value = 0;
                            tr.querySelector('.sinst-inp').value = 0;
                            tr.querySelector('.soth-inp').value = 0;
                        }

                        div.style.display = 'none'; calcSellTotalTotal(); 
                    };
                    div.appendChild(d);
                });
            } else div.style.display = 'none';
        });
        document.addEventListener('click', e => { if(e.target !== inp) div.style.display = 'none'; });
    }

    window.calcSellTotalTotal = function() {
        let rawSellTotal = 0;
        let acExpensesTotal = 0;
        document.querySelectorAll('#sellTableBody tr').forEach(r => {
            let qty   = parseFloat(r.querySelector('.sqinp').value) || 0;
            let price = parseFloat(r.querySelector('.ssp-inp').value) || 0;
            let trans = parseFloat(r.querySelector('.strans-inp')?.value) || 0;
            let inst  = parseFloat(r.querySelector('.sinst-inp')?.value) || 0;
            let oth   = parseFloat(r.querySelector('.soth-inp')?.value) || 0;
            rawSellTotal += (qty * price) + trans + inst + oth;
            acExpensesTotal += (trans + inst + oth);
        });
        let discount   = parseFloat(document.getElementById('sell_discount').value) || 0;
        let finalTotal = Math.max(rawSellTotal - discount, 0);
        document.getElementById('sell_total').innerText = finalTotal.toLocaleString('en-US');

        // إظهار خزنة مصاريف التكييف تلقائياً وجعلها إجبارية طالما فيه بنود (نقل/تركيب/خامات)
        const needExpenseAcc = acExpensesTotal > 0;
        const expAccDiv = document.getElementById('sell_expense_acc_div');
        const expSelect = document.getElementById('sell_expense_acc_select');
        if (expAccDiv) expAccDiv.style.display = needExpenseAcc ? 'block' : 'none';
        if (expSelect) {
            const emptyOpt = expSelect.querySelector('option[value=""]');
            if (needExpenseAcc) {
                expSelect.setAttribute('required', 'required');
                if (emptyOpt) { emptyOpt.disabled = true; emptyOpt.hidden = true; emptyOpt.textContent = 'اختر الخزنة... (إجباري)'; }
            } else {
                expSelect.removeAttribute('required');
                if (emptyOpt) { emptyOpt.disabled = false; emptyOpt.hidden = false; emptyOpt.textContent = 'اختر الخزنة...'; }
            }
        }
        window._acExpensesTotal = acExpensesTotal;
    }

    // عمولات المبيعات: حقل واحد — لا دوال إضافية مطلوبة

    window.addSellRowPreFilled = function(id, name, maxQty, price) {
        document.getElementById('sellTableBody').innerHTML='';
        addSellRow();
        const tr = document.querySelector('#sellTableBody tr');
        
        let itemData = dbItems.find(i => i.id == id);
        
        tr.querySelector('.sid-inp').value = id; 
        tr.querySelector('.sinp').value = name;
        tr.querySelector('.sqinp').max = maxQty; 
        tr.querySelector('.sqinp').value = 1; 
        tr.querySelector('.ssp-inp').value = price;
        
        if(itemData) {
            tr.querySelector('.scat-inp').value = itemData.cat;
            if(itemData.cat === 'تكييفات') {
                tr.querySelector('.sell-extra-fields').style.display = 'block';
            }
        }

        calcSellTotalTotal();
        new bootstrap.Modal(document.getElementById('sellModal')).show();
    }

    document.getElementById('buyStockModal').addEventListener('show.bs.modal', function () { if(document.getElementById('purchaseTableBody').children.length === 0) addPurchaseRow(); });
    document.getElementById('sellModal').addEventListener('show.bs.modal', function () { if(document.getElementById('sellTableBody').children.length === 0) addSellRow(); });


</script>


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
    // ── قفل اسم العميل المسجل بالكامل ──
    const registeredCustomers = {!! json_encode($uniqueCustomers ?? []) !!};

    const custPhoneField = document.getElementById('cust_phone_input');
    const custNameField  = document.getElementById('cust_name_input');

    // hidden input يُرسل الاسم مع الفورم لما الحقل يكون disabled
    let hiddenNameInput = document.getElementById('cust_name_hidden');
    if (!hiddenNameInput) {
        hiddenNameInput = document.createElement('input');
        hiddenNameInput.type = 'hidden';
        hiddenNameInput.id   = 'cust_name_hidden';
        hiddenNameInput.name = '';
        custNameField?.parentElement?.appendChild(hiddenNameInput);
    }

    // دالة قفل موحدة — تُستدعى من كل الأماكن
    function lockNameField(phone, name) {
        if (!custNameField) return;
        custNameField.value    = name;
        custNameField.disabled = true;
        custNameField.style.backgroundColor = '#e9ecef';
        custNameField.style.cursor = 'not-allowed';
        custNameField.removeAttribute('name');

        hiddenNameInput.name  = 'customer_name';
        hiddenNameInput.value = name;

        // منع الكتابة حتى لو حد أزال disabled من DevTools
        custNameField.onkeydown = (e) => { e.preventDefault(); showLockedAlert(name); };
        custNameField.onpaste   = (e) => { e.preventDefault(); showLockedAlert(name); };
        custNameField.ondrop    = (e) => { e.preventDefault(); };

        if (custPhoneField && !custPhoneField.dataset.alerted) {
            Swal.fire({
                title: 'رقم مسجل!',
                html: `هذا الرقم مسجل في قاعدة البيانات باسم:<br><br>
                       <strong class="text-danger fs-4">${name}</strong><br><br>
                       لا يمكن تعديل اسم عميل مسجل.`,
                icon: 'warning',
                confirmButtonText: 'موافق',
                confirmButtonColor: '#dc2626'
            });
            custPhoneField.dataset.alerted = 'true';
        }
    }

    function showLockedAlert(name) {
        Swal.fire({
            title: 'ممنوع!',
            html: `الاسم مقفول — هذا العميل مسجل باسم:<br><strong class="text-danger">${name}</strong>`,
            icon: 'error',
            confirmButtonText: 'موافق',
            confirmButtonColor: '#dc2626',
            timer: 2500,
            timerProgressBar: true
        });
    }

    function unlockNameField() {
        if (!custNameField) return;
        custNameField.name     = 'customer_name';
        custNameField.disabled = false;
        custNameField.style.backgroundColor = '';
        custNameField.style.cursor = '';
        custNameField.onkeydown = null;
        custNameField.onpaste   = null;
        custNameField.ondrop    = null;

        hiddenNameInput.name  = '';
        hiddenNameInput.value = '';

        if (custPhoneField) delete custPhoneField.dataset.alerted;
    }

    // عند الكتابة المباشرة في حقل الرقم
    custPhoneField?.addEventListener('input', function () {
        const typedPhone = this.value.trim();
        if (typedPhone.length >= 10) {
            const matched = registeredCustomers.find(
                c => c.customer_phone && c.customer_phone.trim() === typedPhone
            );
            matched ? lockNameField(typedPhone, matched.customer_name) : unlockNameField();
        } else {
            unlockNameField();
        }
    });


    // ⚖️ سكربت تسوية الجرد السريع (زيادة أو نقصان)
    function adjustInventoryQty(id, name, type) {
        let titleText = type === 'increase' ? 'إضافة رصيد (تسوية جرد)' : 'تسوية عجز / هالك';
        let mainText  = type === 'increase' ? `كم قطعة إضافية وجدتها من: ${name}؟` : `كم قطعة تالفة أو ناقصة من: ${name}؟`;
        let btnColor  = type === 'increase' ? '#16a34a' : '#dc2626';

        Swal.fire({
            title: titleText,
            text: mainText,
            input: 'number',
            inputAttributes: { min: 1, step: 1 },
            icon: type === 'increase' ? 'info' : 'warning',
            showCancelButton: true,
            confirmButtonText: 'تأكيد التسوية',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: btnColor,
            inputValidator: (value) => {
                if (!value || value <= 0) return 'يرجى إدخال رقم صحيح أكبر من الصفر!';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // إنشاء فورم مخفي وإرساله فوراً
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("inventory.adjust") }}';
                
                form.innerHTML = `
                    @csrf
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="type" value="${type}">
                    <input type="hidden" name="qty" value="${result.value}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // 🔁 مودال التحويل بين المخازن
    function openTransferModal(id, name, currentQty, fromStore, toStore) {
        const fromName = fromStore == 1 ? 'الرئيسي' : 'الفرعي';
        const toName   = toStore == 1 ? 'الرئيسي' : 'الفرعي';
        Swal.fire({
            title: `<i class="fa fa-exchange-alt text-info me-2"></i>تحويل بين المخازن`,
            html: `
                <div class="text-end mb-3">
                    <strong class="text-primary fs-5">${name}</strong><br>
                    <span class="text-muted small">المتاح في المخزن ${fromName}: <strong class="text-dark">${currentQty}</strong> قطعة</span><br>
                    <span class="text-muted small">سيتم التحويل من مخزن <strong>${fromName}</strong> إلى مخزن <strong>${toName}</strong></span>
                </div>
                <input type="number" id="trans_qty" class="swal2-input" min="0.01" max="${currentQty}" step="0.01" placeholder="الكمية المراد تحويلها...">
            `,
            showCancelButton: true,
            confirmButtonText: 'تأكيد التحويل',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#0ea5e9',
            preConfirm: () => {
                const qty = parseFloat(document.getElementById('trans_qty').value);
                if (!qty || qty <= 0) { Swal.showValidationMessage('أدخل كمية صحيحة أكبر من صفر!'); return false; }
                if (qty > currentQty) { Swal.showValidationMessage(`الكمية أكبر من المتاح (${currentQty})!`); return false; }
                return qty;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("inventory.transfer") }}';
                form.innerHTML = `
                    @csrf
                    <input type="hidden" name="sale_id" value="${id}">
                    <input type="hidden" name="qty" value="${result.value}">
                    <input type="hidden" name="from_store" value="${fromStore}">
                    <input type="hidden" name="to_store" value="${toStore}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // 📋 مودال الجرد الموحد (زيادة + نقصان في خطوة واحدة)
    function openInventoryAdjustModal(id, name, currentQty) {
        Swal.fire({
            title: `<i class="fa fa-clipboard-list text-warning me-2"></i>جرد الصنف`,
            html: `
                <div class="text-end mb-3">
                    <strong class="text-primary fs-5">${name}</strong><br>
                    <span class="text-muted small">الكمية الحالية في المخزن: <strong class="text-dark">${currentQty}</strong> قطعة</span>
                </div>
                <div class="d-flex gap-3 justify-content-center mb-3">
                    <label style="cursor:pointer; flex:1; border:2px solid #e2e8f0; border-radius:10px; padding:10px; text-align:center; transition:0.2s;" id="lbl-increase">
                        <input type="radio" name="adj_type" value="increase" style="display:none;" onchange="highlightAdjType()">
                        <i class="fa fa-plus-circle fa-2x text-success mb-1 d-block"></i>
                        <span class="fw-bold text-success">زيادة رصيد</span>
                    </label>
                    <label style="cursor:pointer; flex:1; border:2px solid #e2e8f0; border-radius:10px; padding:10px; text-align:center; transition:0.2s;" id="lbl-decrease">
                        <input type="radio" name="adj_type" value="decrease" style="display:none;" onchange="highlightAdjType()">
                        <i class="fa fa-minus-circle fa-2x text-danger mb-1 d-block"></i>
                        <span class="fw-bold text-danger">تسوية عجز</span>
                    </label>
                </div>
                <input type="number" id="adj_qty" class="swal2-input" min="1" step="0.01" placeholder="أدخل الكمية...">
            `,
            showCancelButton: true,
            confirmButtonText: 'تأكيد التسوية',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#ea580c',
            didOpen: () => {
                window.highlightAdjType = function() {
                    const val = document.querySelector('input[name="adj_type"]:checked')?.value;
                    document.getElementById('lbl-increase').style.borderColor = val === 'increase' ? '#16a34a' : '#e2e8f0';
                    document.getElementById('lbl-increase').style.background  = val === 'increase' ? '#f0fdf4' : '';
                    document.getElementById('lbl-decrease').style.borderColor = val === 'decrease' ? '#dc2626' : '#e2e8f0';
                    document.getElementById('lbl-decrease').style.background  = val === 'decrease' ? '#fef2f2' : '';
                };
            },
            preConfirm: () => {
                const type = document.querySelector('input[name="adj_type"]:checked')?.value;
                const qty  = parseInt(document.getElementById('adj_qty').value);
                if (!type) { Swal.showValidationMessage('اختر نوع التسوية أولاً!'); return false; }
                if (!qty || qty <= 0) { Swal.showValidationMessage('أدخل كمية صحيحة أكبر من صفر!'); return false; }
                return { type, qty };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("inventory.adjust") }}';
                form.innerHTML = `
                    @csrf
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="type" value="${result.value.type}">
                    <input type="hidden" name="qty" value="${result.value.qty}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

{{-- ══════════════════════════════════════════════
     PRINT FUNCTIONS — تقارير رسمية للطباعة
     ══════════════════════════════════════════════ --}}
<script>
const INV_PRINT_DATA = @json($printInventoryData);
const SALES_PRINT_DATA = @json($printSalesData);
let currentSupplierPrint = null;

// تنسيق رقم بفاصلة
const fmt = n => fmtMoney(n);
const today = new Date().toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' });

// CSS مشترك لكل تقارير الطباعة (شركة الضبع)
function getPrintStyles() {
    return `
        @page { size: A4; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Cairo', 'Tahoma', sans-serif;
            background: #fff; color: #0f172a;
            margin: 0; padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page { max-width: 1100px; margin: 0 auto; padding: 8px; }

        .doc-header {
            display: flex; justify-content: space-between; align-items: flex-end;
            padding-bottom: 18px; margin-bottom: 22px;
            border-bottom: 3px solid #0f172a;
        }
        .doc-header .brand h1 {
            margin: 0; font-size: 28px; font-weight: 900; color: #0f172a;
            letter-spacing: -0.5px;
        }
        .doc-header .brand p { margin: 4px 0 0; color: #64748b; font-size: 13px; font-weight: 600; }
        .doc-header .meta { text-align: left; font-size: 13px; }
        .doc-header .meta .doc-title {
            display: inline-block;
            background: #0f172a; color: #fff;
            padding: 6px 16px; border-radius: 6px;
            font-weight: 800; font-size: 14px; margin-bottom: 8px;
        }
        .doc-header .meta .doc-date {
            color: #64748b; font-weight: 700;
        }

        .summary {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;
            margin-bottom: 22px;
        }
        .summary .box {
            border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 12px 14px; background: #f8fafc;
        }
        .summary .box .label {
            font-size: 11px; color: #64748b; font-weight: 700;
            margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .summary .box .val {
            font-size: 18px; font-weight: 900; color: #0f172a;
        }
        .summary .box.success .val { color: #059669; }
        .summary .box.danger  .val { color: #dc2626; }
        .summary .box.warning .val { color: #d97706; }

        .section-title {
            margin: 18px 0 10px; padding-bottom: 8px;
            font-size: 15px; font-weight: 900; color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            display: flex; justify-content: space-between; align-items: center;
        }
        .section-title small { font-size: 11px; color: #64748b; font-weight: 700; }

        table.data {
            width: 100%; border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        table.data thead { background: #0f172a; color: #fff; }
        table.data th {
            padding: 10px 8px; text-align: center; font-weight: 700;
            font-size: 11px; letter-spacing: 0.3px;
        }
        table.data td {
            padding: 9px 8px; border-bottom: 1px solid #e2e8f0;
            text-align: center; vertical-align: middle;
            font-weight: 600; color: #0f172a;
        }
        table.data tr:nth-child(even) td { background: #fafbfd; }
        table.data tr:last-child td { border-bottom: 1px solid #cbd5e1; }
        table.data tfoot tr { background: #f1f5f9; }
        table.data tfoot td {
            padding: 11px 8px; font-weight: 900; font-size: 13px;
            border-top: 2px solid #0f172a;
        }
        table.data .num-pos { color: #059669; font-weight: 800; }
        table.data .num-neg { color: #dc2626; font-weight: 800; }
        table.data .text-start { text-align: right !important; }
        table.data .badge-pill {
            display: inline-block; padding: 2px 8px; border-radius: 999px;
            font-size: 10px; font-weight: 700;
        }
        .badge-cash { background: #dcfce7; color: #166534; }
        .badge-inst { background: #dbeafe; color: #1e40af; }

        .footer {
            display: flex; justify-content: space-between;
            margin-top: 35px; padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
            font-size: 12px; color: #64748b;
        }
        .footer .sign-box {
            text-align: center; min-width: 180px;
        }
        .footer .sign-box .line {
            border-top: 1px solid #0f172a; margin-top: 35px; padding-top: 6px;
            font-weight: 700; color: #0f172a;
        }
        .footer .stamp {
            text-align: center; color: #94a3b8; font-weight: 700;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    `;
}

function getHeaderHTML(title) {
    return `
        <div class="doc-header">
            <div class="brand">
                <h1>شركة الضبع</h1>
                <p>للتجارة وأنظمة التقسيط والمقاولات</p>
            </div>
            <div class="meta">
                <div class="doc-title">${title}</div>
                <div class="doc-date">${today}</div>
            </div>
        </div>
    `;
}

function getFooterHTML(signLeft, signRight) {
    return `
        <div class="footer">
            <div class="sign-box">
                <div class="line">${signLeft || 'توقيع المسؤول'}</div>
            </div>
            <div class="stamp">طُبع آلياً من نظام الضبع — ${new Date().toLocaleString('ar-EG')}</div>
            <div class="sign-box">
                <div class="line">${signRight || 'توقيع المراجع'}</div>
            </div>
        </div>
    `;
}

function openPrintWin(html) {
    const win = window.open('', '', 'width=1000,height=800');
    win.document.write(html);
    win.document.close();
    setTimeout(() => { win.print(); }, 400);
}

// ══════════════════════════════════════════════
// 1. طباعة حالة المخزن كاملة
// ══════════════════════════════════════════════
window.printInventoryState = function() {
    const data = INV_PRINT_DATA;

    const renderTable = (items, title) => {
        if (!items || items.length === 0) {
            return `<div class="section-title">${title} <small>0 صنف</small></div>
                    <div style="text-align:center; padding:20px; color:#94a3b8; border:1px dashed #e2e8f0; border-radius:8px; margin-bottom:20px;">لا توجد أصناف</div>`;
        }
        let totalCost = 0, totalSell = 0;
        const rows = items.map((it, idx) => {
            const lineCost = it.qty * it.pp;
            const lineSell = it.qty * it.sp;
            totalCost += lineCost;
            totalSell += lineSell;
            return `<tr>
                <td>${idx + 1}</td>
                <td>#${it.id}</td>
                <td class="text-start">${it.name || '—'}</td>
                <td>${it.category || 'عام'}</td>
                <td>${it.supplier || '—'}</td>
                <td><strong>${fmt(it.qty)}</strong></td>
                <td>${fmt(it.pp)} ج</td>
                <td>${fmt(it.sp)} ج</td>
                <td class="num-neg">${fmt(lineCost)} ج</td>
                <td class="num-pos">${fmt(lineSell)} ج</td>
            </tr>`;
        }).join('');

        return `
            <div class="section-title">${title} <small>${items.length} صنف</small></div>
            <table class="data">
                <thead>
                    <tr>
                        <th>#</th><th>كود</th><th class="text-start">اسم الصنف</th>
                        <th>الفئة</th><th>المورد</th>
                        <th>الكمية</th><th>سعر شراء</th><th>سعر بيع</th>
                        <th>قيمة شراء</th><th>قيمة بيع</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-start" style="text-align:right; padding-right:14px;">إجمالي ${title}:</td>
                        <td class="num-neg">${fmt(totalCost)} ج</td>
                        <td class="num-pos">${fmt(totalSell)} ج</td>
                    </tr>
                </tfoot>
            </table>
        `;
    };

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>تقرير حالة المخزن - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
            <style>${getPrintStyles()}</style>
        </head>
        <body>
            <div class="page">
                ${getHeaderHTML('تقرير حالة المخزن الكامل')}

                <div class="summary">
                    <div class="box"><div class="label">إجمالي الدفعات</div><div class="val">${fmt(data.stats.total_items)}</div></div>
                    <div class="box danger"><div class="label">قيمة المخزن (تكلفة)</div><div class="val">${fmt(data.stats.total_cost_value)} ج</div></div>
                    <div class="box success"><div class="label">القيمة البيعية</div><div class="val">${fmt(data.stats.total_sell_value)} ج</div></div>
                    <div class="box warning"><div class="label">الربح المستهدف</div><div class="val">${fmt(data.stats.potential_profit)} ج</div></div>
                </div>

                ${renderTable(data.main, 'المخزن الرئيسي')}
                ${renderTable(data.sub, 'المخزن الفرعي')}

                ${getFooterHTML('أمين المخزن', 'المدير المالي')}
            </div>
        </body></html>
    `;
    openPrintWin(html);
};

// ══════════════════════════════════════════════
// 2. طباعة كشف حساب مورد
// ══════════════════════════════════════════════
// نلتقط بيانات المورد من openSupplierLogModal الأصلية
const _origOpenSupplierLogModal = window.openSupplierLogModal;
window.openSupplierLogModal = function(supplierName, detailsArray, totalCost) {
    currentSupplierPrint = { name: supplierName, details: detailsArray, total: totalCost };
    if (typeof _origOpenSupplierLogModal === 'function') {
        _origOpenSupplierLogModal(supplierName, detailsArray, totalCost);
    }
};

window.printCurrentSupplier = function() {
    if (!currentSupplierPrint) return;
    const { name, details, total } = currentSupplierPrint;

    let totalQty = 0;
    const rows = details.map((it, i) => {
        const q = parseFloat(it.quantity) || 0;
        const pp = parseFloat(it.purchase_price) || 0;
        const lineTotal = q * pp;
        totalQty += q;
        const date = it.created_at ? String(it.created_at).substring(0, 10) : '—';
        return `<tr>
            <td>${i + 1}</td>
            <td dir="ltr">${date}</td>
            <td class="text-start">${it.product_name || '—'}</td>
            <td><strong>${fmt(q)}</strong></td>
            <td>${fmt(pp)} ج</td>
            <td class="num-neg"><strong>${fmt(lineTotal)} ج</strong></td>
        </tr>`;
    }).join('');

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>كشف حساب: ${name} - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
            <style>${getPrintStyles()}</style>
        </head>
        <body>
            <div class="page">
                ${getHeaderHTML('كشف حساب مورد')}

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px 20px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-size:12px; color:#64748b; font-weight:700; margin-bottom:4px;">اسم المورد</div>
                        <div style="font-size:20px; font-weight:900; color:#0f172a;">${name}</div>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:12px; color:#64748b; font-weight:700; margin-bottom:4px;">عدد العمليات</div>
                        <div style="font-size:20px; font-weight:900; color:#2563eb;">${details.length} فاتورة</div>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:12px; color:#64748b; font-weight:700; margin-bottom:4px;">إجمالي المدفوع دفترياً</div>
                        <div style="font-size:22px; font-weight:900; color:#dc2626;">${fmt(total)} ج</div>
                    </div>
                </div>

                <div class="section-title">تفاصيل التعاملات <small>${details.length} عملية · ${fmt(totalQty)} قطعة</small></div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th>التاريخ</th><th class="text-start">الصنف المُشترى</th>
                            <th>الكمية</th><th>سعر الوحدة</th><th>إجمالي السطر</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-start" style="text-align:right; padding-right:14px;">الإجمالي العام:</td>
                            <td class="num-neg">${fmt(total)} ج</td>
                        </tr>
                    </tfoot>
                </table>

                ${getFooterHTML('توقيع المورد', 'المدير المالي')}
            </div>
        </body></html>
    `;
    openPrintWin(html);
};

// ══════════════════════════════════════════════
// 3. طباعة سجل المبيعات
// ══════════════════════════════════════════════
// ── فلترة سجل المبيعات (بحث/نوع/نطاق تاريخ) — مصدر واحد للجدول والطباعة ──
function getSalesFilter() {
    return {
        from:     (document.getElementById('sl-from')?.value)   || null,
        to:       (document.getElementById('sl-to')?.value)     || null,
        customer: ((document.getElementById('sl-search')?.value) || '').trim().toLowerCase(),
        type:     (document.getElementById('sl-type')?.value)   || 'all',
    };
}
function salesRowMatches(s, f) {
    const months = Number(s.months) || 0;
    if (f.from && s.date < f.from) return false;
    if (f.to   && s.date > f.to)   return false;
    if (f.type === 'cash' && months > 0)  return false;
    if (f.type === 'inst' && months <= 0) return false;
    if (f.customer && !String(s.customer || '').toLowerCase().includes(f.customer)) return false;
    return true;
}
window.applySalesFilter = function() {
    const f = getSalesFilter();
    const rows = document.querySelectorAll('#sales-log-body tr');
    let shown = 0, revenue = 0, profit = 0;
    rows.forEach(row => {
        const s = { date: row.dataset.date, customer: row.dataset.customer, months: row.dataset.months };
        const match = salesRowMatches(s, f);
        row.style.display = match ? '' : 'none';
        if (match) {
            shown++;
            revenue += parseFloat(row.dataset.total)  || 0;
            profit  += parseFloat(row.dataset.profit) || 0;
        }
    });
    const cEl = document.getElementById('sl-count');   if (cEl) cEl.textContent = shown;
    const rEl = document.getElementById('sl-revenue'); if (rEl) rEl.textContent = fmtMoney(revenue);
    const pEl = document.getElementById('sl-profit');  if (pEl) pEl.textContent = fmtMoney(profit);
};
window.resetSalesFilter = function() {
    ['sl-from','sl-to','sl-search'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    const t = document.getElementById('sl-type'); if (t) t.value = 'all';
    applySalesFilter();
};
function salesFilterLabel() {
    const f = getSalesFilter();
    const parts = [];
    if (f.type === 'cash') parts.push('كاش فقط');
    else if (f.type === 'inst') parts.push('تقسيط فقط');
    if (f.customer) parts.push('عميل: ' + (document.getElementById('sl-search')?.value || ''));
    if (f.from || f.to) parts.push('من ' + (f.from || '—') + ' إلى ' + (f.to || '—'));
    return parts.length ? parts.join(' | ') : 'كل العمليات';
}

window.printSalesLog = function() {
    const f = getSalesFilter();
    const data = SALES_PRINT_DATA.filter(s => salesRowMatches(s, f));
    if (!data || data.length === 0) {
        alert('لا يوجد بيانات مطابقة للفلتر الحالي للطباعة');
        return;
    }

    let totalRevenue = 0, totalProfit = 0, cashCount = 0, instCount = 0;
    const rows = data.map((s, i) => {
        totalRevenue += s.total;
        totalProfit  += s.profit;
        const isInst = s.months > 0;
        if (isInst) instCount++; else cashCount++;
        return `<tr>
            <td>${i + 1}</td>
            <td>#${s.id}</td>
            <td dir="ltr">${s.date}</td>
            <td class="text-start"><strong>${s.customer || 'عميل نقدي'}</strong></td>
            <td class="text-start">${s.product || '—'}</td>
            <td><span class="badge-pill ${isInst ? 'badge-inst' : 'badge-cash'}">${isInst ? 'تقسيط ' + s.months + ' شهر' : 'كاش'}</span></td>
            <td class="num-pos"><strong>${fmt(s.total)} ج</strong></td>
            <td class="num-pos">${fmt(s.profit)} ج</td>
        </tr>`;
    }).join('');

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>سجل المبيعات - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
            <style>${getPrintStyles()}</style>
        </head>
        <body>
            <div class="page">
                ${getHeaderHTML('سجل المبيعات — ' + salesFilterLabel())}

                <div class="summary">
                    <div class="box"><div class="label">عدد العمليات</div><div class="val">${data.length}</div></div>
                    <div class="box"><div class="label">كاش / تقسيط</div><div class="val">${cashCount} / ${instCount}</div></div>
                    <div class="box success"><div class="label">إجمالي الإيرادات</div><div class="val">${fmt(totalRevenue)} ج</div></div>
                    <div class="box warning"><div class="label">إجمالي الربح</div><div class="val">${fmt(totalProfit)} ج</div></div>
                </div>

                <div class="section-title">تفاصيل العمليات <small>${data.length} عملية بيع</small></div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th><th>كود</th><th>التاريخ</th>
                            <th class="text-start">العميل</th><th class="text-start">البيان</th>
                            <th>نوع</th><th>الإجمالي</th><th>الربح</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-start" style="text-align:right; padding-right:14px;">الإجماليات:</td>
                            <td class="num-pos">${fmt(totalRevenue)} ج</td>
                            <td class="num-pos">${fmt(totalProfit)} ج</td>
                        </tr>
                    </tfoot>
                </table>

                ${getFooterHTML('المحاسب', 'المدير المالي')}
            </div>
        </body></html>
    `;
    openPrintWin(html);
};
</script>
</body>
</html>