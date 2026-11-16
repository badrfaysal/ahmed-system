<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفواتير اليدوية - شركة الضبع</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/html2canvas/html2canvas.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'indigo'])

    <style>
        /* ══════════════════════════════════════════════════════════════
           الخلفية العامة والجو اللوني الحيوي لشاشة الفواتير اليدوية
           ══════════════════════════════════════════════════════════════ */
        body {
            background:
                radial-gradient(circle at 12% 10%, rgba(59, 130, 246, 0.08), transparent 40%),
                radial-gradient(circle at 88% 15%, rgba(16, 185, 129, 0.07), transparent 38%),
                radial-gradient(circle at 50% 92%, rgba(245, 158, 11, 0.06), transparent 44%),
                var(--c-bg);
        }

        /* ── Hero Header الملون الحيوي ── */
        .mi-hero {
            margin-top: 22px; /* مسافة آمنة أسفل زر الجرس العلوي الثابت */
            background: linear-gradient(135deg, #1e1b4b 0%, #1d4ed8 50%, #0d9488 100%);
            color: #ffffff;
            border-radius: 22px;
            padding: 24px 28px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 14px 34px rgba(29, 78, 216, 0.22);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }
        .mi-hero::before {
            content: '';
            position: absolute;
            top: -70px;
            left: -40px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.28) 0%, transparent 70%);
            pointer-events: none;
        }
        .mi-hero::after {
            content: '';
            position: absolute;
            bottom: -90px;
            right: 22%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(45, 212, 191, 0.24) 0%, transparent 70%);
            pointer-events: none;
        }
        .mi-hero-brand {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .mi-hero-icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(255,255,255,0.22), rgba(255,255,255,0.08));
            border: 1.5px solid rgba(255, 255, 255, 0.32);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            color: #fde047;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.22);
            flex-shrink: 0;
        }
        .mi-hero-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff !important;
            letter-spacing: -0.01em;
        }
        .mi-hero-sub {
            margin-top: 4px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.88);
            font-weight: 500;
        }
        .mi-hero-pills {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .mi-hero-pill {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.26);
            backdrop-filter: blur(8px);
            border-radius: 14px;
            padding: 9px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
        }
        .mi-hero-pill-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .mi-hero-pill-icon.emerald { background: rgba(16, 185, 129, 0.28); color: #6ee7b7; }
        .mi-hero-pill-icon.amber   { background: rgba(245, 158, 11, 0.28); color: #fde047; }
        .mi-hero-pill-lbl { font-size: 0.72rem; opacity: 0.85; line-height: 1.2; }
        .mi-hero-pill-val { font-size: 1.02rem; font-weight: 800; line-height: 1.25; }

        /* ── شريط التبويبات المعبرة الملونة ── */
        .mi-tabs-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }
        .mi-nav-tabs {
            background: transparent;
            border: none;
            padding: 0;
            display: inline-flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .mi-nav-tabs .nav-link {
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.94rem;
            padding: 10px 22px;
            border: 1.5px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            transition: all 0.22s ease;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
        }
        .mi-nav-tabs .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(15, 23, 42, 0.10);
        }
        .mi-nav-tabs .nav-link.tab-create-btn {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
        }
        .mi-nav-tabs .nav-link.tab-create-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            border-color: #4338ca;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.32);
        }
        .mi-nav-tabs .nav-link.tab-create-btn.is-editing.active {
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            border-color: #d97706;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(234, 88, 12, 0.34);
        }
        .mi-nav-tabs .nav-link.tab-log-btn {
            background: #ecfdf5;
            border-color: #6ee7b7;
            color: #047857;
        }
        .mi-nav-tabs .nav-link.tab-log-btn.active {
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
            border-color: #0f766e;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.32);
        }
        .mi-tab-count-badge {
            background: rgba(5, 150, 105, 0.14);
            color: #047857;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            transition: all 0.2s ease;
        }
        .mi-nav-tabs .nav-link.active .mi-tab-count-badge {
            background: rgba(255, 255, 255, 0.24);
            color: #ffffff;
        }

        .mi-quick-new-btn {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            color: #475569;
            border-radius: 50px;
            padding: 8px 16px;
            font-size: 0.84rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }
        .mi-quick-new-btn:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        /* ── الكروت الرئيسية والأقسام الملونة داخل النموذج ── */
        .mi-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 20px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.05);
            padding: 24px;
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
        }
        .mi-card-accent-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2563eb 0%, #7c3aed 35%, #0d9488 70%, #f59e0b 100%);
        }
        .mi-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding-bottom: 14px;
            margin-bottom: 20px;
            border-bottom: 1.5px dashed var(--c-border);
        }
        .mi-section-title {
            font-size: 1.12rem;
            font-weight: 800;
            color: var(--c-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mi-title-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            flex-shrink: 0;
        }
        .mi-title-icon.blue   { background: linear-gradient(135deg, #2563eb, #4f46e5); }
        .mi-title-icon.amber  { background: linear-gradient(135deg, #f59e0b, #ea580c); box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25); }
        .mi-title-icon.teal   { background: linear-gradient(135deg, #059669, #0d9488); box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25); }

        /* ── منطقة 1: هوية الفاتورة وبيانات العميل ── */
        .mi-zone-client {
            background: linear-gradient(135deg, #f8fbff 0%, #f5f3ff 100%);
            border: 1.5px solid #dbeafe;
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .mi-zone-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }
        .mi-zone-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 0.94rem;
            color: #1e3a8a;
        }
        .mi-zone-dot {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            color: #ffffff;
        }
        .mi-zone-dot.indigo { background: linear-gradient(135deg, #3b82f6, #6366f1); }
        .mi-zone-dot.emerald { background: linear-gradient(135deg, #10b981, #0d9488); }
        .mi-zone-dot.sky { background: linear-gradient(135deg, #0ea5e9, #2563eb); }
        .mi-zone-dot.amber { background: linear-gradient(135deg, #f59e0b, #d97706); }

        /* شرائح اختيار نوع المستند السريعة */
        .mi-doc-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-bottom: 14px;
        }
        .mi-doc-chip {
            border-radius: 999px;
            padding: 5px 13px;
            font-size: 0.79rem;
            font-weight: 700;
            border: 1.5px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.18s ease;
            background: #ffffff;
        }
        .mi-doc-chip:hover { transform: translateY(-1px); }
        .mi-doc-chip[data-color="blue"]    { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }
        .mi-doc-chip[data-color="purple"]  { color: #6d28d9; border-color: #ddd6fe; background: #f5f3ff; }
        .mi-doc-chip[data-color="amber"]   { color: #b45309; border-color: #fde68a; background: #fffbeb; }
        .mi-doc-chip[data-color="emerald"] { color: #047857; border-color: #a7f3d0; background: #ecfdf5; }
        .mi-doc-chip[data-color="rose"]    { color: #be123c; border-color: #fecdd3; background: #fff1f2; }

        .mi-doc-chip.active { color: #ffffff !important; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.16); }
        .mi-doc-chip[data-color="blue"].active    { background: linear-gradient(135deg, #2563eb, #1d4ed8); border-color: #1d4ed8; }
        .mi-doc-chip[data-color="purple"].active  { background: linear-gradient(135deg, #7c3aed, #6d28d9); border-color: #6d28d9; }
        .mi-doc-chip[data-color="amber"].active   { background: linear-gradient(135deg, #f59e0b, #d97706); border-color: #b45309; }
        .mi-doc-chip[data-color="emerald"].active { background: linear-gradient(135deg, #10b981, #059669); border-color: #047857; }
        .mi-doc-chip[data-color="rose"].active    { background: linear-gradient(135deg, #f43f5e, #e11d48); border-color: #be123c; }

        .mi-input-group .input-group-text {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #3b82f6;
            font-size: 0.85rem;
        }
        .mi-input-group .form-control {
            border-color: #cbd5e1;
        }
        .mi-input-group .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
        }

        .mi-client-match-pill {
            display: none;
            align-items: center;
            gap: 4px;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 0.7rem;
            font-weight: 800;
            margin-right: 4px;
            animation: miPopIn 0.25s ease;
        }

        /* ── منطقة 2: جدول الأصناف والبنود ── */
        .mi-zone-items {
            background: #ffffff;
            border: 1.5px solid #ccfbf1;
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(13, 148, 136, 0.05);
        }
        .mi-live-pill {
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.78rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .mi-live-pill.indigo { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .mi-live-pill.teal   { background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4; }

        .btn-mi-add-row {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 999px;
            padding: 7px 18px;
            font-weight: 800;
            font-size: 0.85rem;
            box-shadow: 0 5px 14px rgba(16, 185, 129, 0.28);
            transition: all 0.2s ease;
        }
        .btn-mi-add-row:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 18px rgba(16, 185, 129, 0.38);
        }

        .mi-items-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .mi-items-table thead th {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #0d9488 100%) !important;
            color: #ffffff !important;
            font-weight: 800;
            font-size: 0.84rem;
            border: none !important;
            padding: 11px 10px;
            white-space: nowrap;
        }
        .mi-items-table tbody tr {
            transition: background-color 0.15s ease;
        }
        .mi-items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .mi-items-table tbody tr:hover {
            background-color: #eff6ff;
        }
        .mi-items-table td {
            vertical-align: middle;
            padding: 9px 7px;
            border-color: #e2e8f0;
        }
        .mi-row-new-anim {
            animation: miRowSlideIn 0.26s ease-out;
        }
        .mi-row-idx-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: linear-gradient(135deg, #e0e7ff, #dbeafe);
            color: #1e40af;
            font-weight: 800;
            font-size: 0.82rem;
            border: 1px solid #bfdbfe;
        }
        .mi-line-total-pill {
            display: inline-block;
            min-width: 84px;
            padding: 4px 10px;
            border-radius: 9px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            font-weight: 800;
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }
        .mi-line-total-pill.has-val {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            color: #047857;
            border-color: #6ee7b7;
        }
        .btn-mi-del-row {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.16s ease;
        }
        .btn-mi-del-row:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            transform: scale(1.06);
        }

        /* ── منطقة 3: الملاحظات والشروط الملونة + ملخص الحساب ── */
        .mi-zone-notes {
            background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
            border: 1.5px solid #bae6fd;
            border-right: 4px solid #0284c7;
            border-radius: 14px;
            padding: 15px 16px;
        }
        .mi-zone-terms {
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
            border: 1.5px solid #fde68a;
            border-right: 4px solid #d97706;
            border-radius: 14px;
            padding: 15px 16px;
        }
        .mi-term-chip {
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            padding: 3px 10px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.16s ease;
        }
        .mi-term-chip:hover { transform: translateY(-1px); }
        .mi-term-chip.amber   { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
        .mi-term-chip.amber:hover   { background: #f59e0b; color: #ffffff; }
        .mi-term-chip.emerald { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .mi-term-chip.emerald:hover { background: #10b981; color: #ffffff; }
        .mi-term-chip.indigo  { background: #e0e7ff; color: #3730a3; border-color: #a5b4fc; }
        .mi-term-chip.indigo:hover  { background: #4f46e5; color: #ffffff; }

        .mi-summary-box {
            background: linear-gradient(160deg, #ffffff 0%, #f0f9ff 55%, #ecfdf5 100%);
            border: 1.5px solid #bae6fd;
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: 0 8px 24px rgba(14, 116, 144, 0.08);
        }
        .mi-summary-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 1.5px dashed #cbd5e1;
        }
        .mi-sum-row-pill {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .mi-sum-row-pill.discount {
            background: #fff5f5;
            border-color: #fecaca;
        }
        .mi-sum-row-pill.paid {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        .mi-tax-toggle-box {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 10px 13px;
            transition: all 0.22s ease;
        }
        .mi-tax-toggle-box.tax-active {
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            border-color: #8b5cf6;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.14);
        }
        .mi-grand-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #0d9488 100%);
            color: #ffffff;
            border-radius: 14px;
            padding: 14px 16px;
            margin-top: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .mi-grand-banner .lbl {
            font-weight: 800;
            font-size: 0.98rem;
            color: rgba(255, 255, 255, 0.92);
        }
        .mi-grand-banner .val {
            font-weight: 900;
            font-size: 1.5rem;
            color: #fde047;
            letter-spacing: 0.01em;
        }
        .mi-remaining-alert {
            background: linear-gradient(135deg, #fef2f2, #fff1f2);
            border: 1.5px solid #fca5a5;
            border-radius: 11px;
            padding: 8px 12px;
            margin-top: 9px;
        }
        .btn-mi-submit {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 52%, #0d9488 100%);
            border: none;
            color: #ffffff !important;
            border-radius: 13px;
            padding: 12px 18px;
            font-weight: 800;
            font-size: 0.98rem;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.30);
            transition: all 0.22s ease;
        }
        .btn-mi-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 11px 24px rgba(37, 99, 235, 0.38);
        }
        .btn-mi-submit.is-edit-submit {
            background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
            box-shadow: 0 8px 20px rgba(234, 88, 12, 0.32);
        }

        /* ── بنر وضع التعديل ── */
        .mi-edit-banner-box {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1.5px dashed #f59e0b;
            border-radius: 14px;
            padding: 10px 16px;
        }

        /* ── كروت الإحصائيات الملونة في سجل الفواتير ── */
        .mi-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .mi-kpi-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 18px;
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .mi-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
        }
        .mi-kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
        }
        .mi-kpi-card.indigo::before  { background: linear-gradient(180deg, #3b82f6, #4f46e5); }
        .mi-kpi-card.emerald::before { background: linear-gradient(180deg, #10b981, #059669); }
        .mi-kpi-card.amber::before   { background: linear-gradient(180deg, #f59e0b, #ea580c); }
        .mi-kpi-card.rose::before    { background: linear-gradient(180deg, #f43f5e, #dc2626); }
        .mi-kpi-card.violet::before  { background: linear-gradient(180deg, #8b5cf6, #0d9488); }

        .mi-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }
        .mi-kpi-lbl {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--c-text-muted);
        }
        .mi-kpi-ico {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .mi-kpi-ico.indigo  { background: #e0e7ff; color: #4338ca; }
        .mi-kpi-ico.emerald { background: #d1fae5; color: #059669; }
        .mi-kpi-ico.amber   { background: #fef3c7; color: #d97706; }
        .mi-kpi-ico.rose    { background: #ffe4e6; color: #e11d48; }
        .mi-kpi-ico.violet  { background: #ede9fe; color: #7c3aed; }

        .mi-kpi-val {
            font-size: 1.55rem;
            font-weight: 900;
            line-height: 1.15;
            font-feature-settings: 'tnum';
        }
        .mi-kpi-card.indigo .mi-kpi-val  { color: #1e40af; }
        .mi-kpi-card.emerald .mi-kpi-val { color: #047857; }
        .mi-kpi-card.amber .mi-kpi-val   { color: #b45309; }
        .mi-kpi-card.rose .mi-kpi-val    { color: #be123c; }
        .mi-kpi-card.violet .mi-kpi-val  { color: #6d28d9; font-size: 1.25rem; }

        /* ── فلاتر السجل الملونة ── */
        .mi-filter-pill {
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            border-radius: 999px;
            padding: 6px 15px;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.18s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .mi-filter-pill:hover { transform: translateY(-1px); }
        .mi-filter-pill[data-filter="all"].active     { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; border-color: #1e40af; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); }
        .mi-filter-pill[data-filter="active"].active  { background: linear-gradient(135deg, #059669, #10b981); color: #fff; border-color: #059669; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); }
        .mi-filter-pill[data-filter="edited"].active  { background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; border-color: #d97706; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25); }
        .mi-filter-pill[data-filter="deleted"].active { background: linear-gradient(135deg, #dc2626, #f43f5e); color: #fff; border-color: #dc2626; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); }

        /* ── جدول سجل الفواتير ── */
        .mi-log-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .mi-log-table thead th {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 58%, #0d9488 100%) !important;
            color: #ffffff !important;
            font-weight: 800;
            font-size: 0.84rem;
            padding: 13px 12px;
            border: none !important;
            white-space: nowrap;
        }
        .mi-log-table tbody td {
            padding: 12px 11px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
        }
        .mi-inv-num-pill {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-right: 4px solid #2563eb;
            border-radius: 8px;
            padding: 3px 9px;
            font-weight: 900;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }
        .mi-client-avatar {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 0.92rem;
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.12);
        }
        .mi-av-0 { background: linear-gradient(135deg, #2563eb, #4f46e5); }
        .mi-av-1 { background: linear-gradient(135deg, #059669, #0d9488); }
        .mi-av-2 { background: linear-gradient(135deg, #d97706, #ea580c); }
        .mi-av-3 { background: linear-gradient(135deg, #7c3aed, #db2777); }
        .mi-av-4 { background: linear-gradient(135deg, #0284c7, #2563eb); }
        .mi-av-5 { background: linear-gradient(135deg, #e11d48, #f97316); }

        .mi-row-deleted {
            background-color: rgba(239, 68, 68, 0.05) !important;
        }
        .mi-row-deleted td {
            border-bottom-color: rgba(239, 68, 68, 0.16) !important;
        }
        .mi-row-edited {
            background-color: rgba(245, 158, 11, 0.05) !important;
        }
        .mi-badge-deleted {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .mi-badge-edited {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fcd34d;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .mi-badge-active {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #6ee7b7;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* أزرار الإجراءات الملونة في السجل */
        .btn-mi-act {
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.78rem;
            padding: 5px 11px;
            border: none;
            transition: all 0.16s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-mi-act:hover { transform: translateY(-1px); }
        .btn-mi-act.view { background: linear-gradient(135deg, #2563eb, #4f46e5); color: #fff; box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25); }
        .btn-mi-act.img  { background: linear-gradient(135deg, #334155, #0f172a); color: #fff; box-shadow: 0 3px 8px rgba(15, 23, 42, 0.22); }
        .btn-mi-act.wa   { background: linear-gradient(135deg, #16a34a, #22c55e); color: #fff; box-shadow: 0 3px 8px rgba(22, 163, 74, 0.28); }
        .btn-mi-act.edit { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .btn-mi-act.edit:hover { background: #f59e0b; color: #fff; }
        .btn-mi-act.del  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-mi-act.del:hover  { background: #dc2626; color: #fff; }

        /* ── منتقي طابع ألوان الفاتورة في المعاينة ── */
        .inv-palette-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 14px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }
        .inv-swatch-btn {
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 800;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            transition: all 0.16s ease;
        }
        .inv-swatch-btn:hover { transform: translateY(-1px); }
        .inv-swatch-btn.active {
            border-color: #0f172a;
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.18);
        }
        .inv-swatch-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            border: 1.5px solid rgba(255,255,255,0.85);
            box-shadow: 0 0 0 1px rgba(0,0,0,0.12);
        }

        /* ══════════════════════════════════════════════════════════════
           الورقة الرسمية للفاتورة (طباعة + تصدير JPG لواتساب)
           مُحصنة بالكامل ضد أي تأثيرات للوضع الليلي، وبألوان زاهية ومتناسقة
           ══════════════════════════════════════════════════════════════ */
        .official-invoice-canvas {
            width: 820px;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 32px 34px 26px 34px;
            position: relative;
            overflow: hidden;
            font-family: 'Cairo', 'IBM Plex Sans Arabic', sans-serif;
            box-sizing: border-box;
            direction: rtl;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .official-invoice-canvas .inv-decor-orb {
            position: absolute;
            top: -55px;
            left: -55px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            opacity: 0.12;
            pointer-events: none;
        }
        .inv-top-stripes {
            position: absolute;
            top: 0; left: 0; right: 0;
            overflow: hidden;
        }
        .inv-stripe-primary { height: 7px; }
        .inv-stripe-gold    { height: 3.5px; background: linear-gradient(90deg, #f59e0b, #d97706, #fbbf24); }

        .inv-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-top: 8px;
            padding: 16px 18px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            position: relative;
            z-index: 2;
        }
        .inv-doc-kind-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            margin-bottom: 7px;
        }
        .inv-doc-title {
            font-size: 27px;
            font-weight: 900;
            margin: 0 0 6px 0;
            line-height: 1.15;
        }
        .inv-doc-num {
            display: inline-block;
            font-size: 13.5px;
            font-weight: 800;
            padding: 3px 11px;
            border-radius: 7px;
            margin-bottom: 6px;
        }
        .inv-company-wrap {
            display: flex;
            align-items: center;
            gap: 13px;
            direction: ltr;
        }
        .inv-logo-badge {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fde047;
            flex-shrink: 0;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.16);
        }
        .inv-company-box {
            text-align: right;
            direction: rtl;
        }
        .inv-company-name {
            font-size: 18.5px;
            font-weight: 900;
            margin: 0;
            line-height: 1.25;
        }
        .inv-company-sub {
            font-size: 10.5px;
            font-weight: 800;
            color: #b45309 !important;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }
        .inv-company-phone {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            padding: 2px 9px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            font-size: 12px;
            font-weight: 800;
            color: #334155 !important;
        }

        .inv-client-meta-grid {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 16px;
            margin: 16px 0 18px 0;
            position: relative;
            z-index: 2;
        }
        .inv-client-card {
            flex: 1.35;
            border-radius: 11px;
            padding: 13px 16px;
        }
        .inv-card-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        .inv-client-name-val {
            font-size: 19px;
            font-weight: 900;
            color: #0f172a !important;
            margin-bottom: 4px;
        }
        .inv-client-detail {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569 !important;
            margin-top: 3px;
        }
        .inv-meta-card {
            flex: 0.95;
            background: #fffdf7 !important;
            border: 1px solid #fde68a !important;
            border-top: 3.5px solid #d97706 !important;
            border-radius: 11px;
            padding: 12px 16px;
        }
        .inv-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            font-weight: 700;
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
            color: #334155 !important;
        }
        .inv-meta-row:last-child { border-bottom: none; }

        .inv-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 16px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            position: relative;
            z-index: 2;
        }
        .inv-table thead th {
            color: #ffffff !important;
            font-weight: 800;
            font-size: 13px;
            padding: 10px 10px;
            border: none;
            text-align: center;
        }
        .inv-table thead th.text-right { text-align: right; }
        .inv-table tbody td {
            padding: 9px 10px;
            border-top: 1px solid #e2e8f0;
            border-left: 1px solid #e2e8f0;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a !important;
            text-align: center;
            background: #ffffff;
        }
        .inv-table tbody td:last-child { border-left: none; }
        .inv-table tbody td.text-right {
            text-align: right;
            font-weight: 800;
            color: #0f172a !important;
        }
        .inv-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .inv-row-num-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 800;
        }
        .inv-cell-total {
            font-weight: 900 !important;
        }

        .inv-tafqeet-bar {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%) !important;
            border: 1px solid #fcd34d !important;
            border-right: 4px solid #d97706 !important;
            border-radius: 10px;
            padding: 9px 14px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            position: relative;
            z-index: 2;
        }
        .inv-tafqeet-lbl {
            font-size: 12.5px;
            font-weight: 800;
            color: #92400e !important;
        }
        .inv-tafqeet-val {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a !important;
        }

        .inv-bottom-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-top: 8px;
            position: relative;
            z-index: 2;
        }
        .inv-notes-terms-col { flex: 1.25; }
        .inv-notes-card {
            background: #f0f9ff !important;
            border: 1px solid #bae6fd !important;
            border-right: 4px solid #0284c7 !important;
            border-radius: 10px;
            padding: 10px 13px;
            margin-bottom: 10px;
        }
        .inv-terms-card {
            background: #fffdf7 !important;
            border: 1px solid #fde68a !important;
            border-right: 4px solid #d97706 !important;
            border-radius: 10px;
            padding: 10px 13px;
        }
        .inv-totals-col {
            flex: 0.95;
            min-width: 285px;
            background: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 12px;
            padding: 13px 16px;
        }
        .inv-total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13.5px;
            font-weight: 800;
            border-bottom: 1px dashed #cbd5e1;
            color: #1e293b !important;
        }
        .inv-total-line.is-discount { color: #dc2626 !important; }
        .inv-total-line.is-tax      { color: #6d28d9 !important; }
        .inv-total-line.is-paid     { color: #059669 !important; }
        .inv-total-line.is-rem      { color: #dc2626 !important; border-bottom: none; }
        .inv-total-line.grand {
            color: #ffffff !important;
            padding: 10px 13px;
            border-radius: 9px;
            font-size: 15.5px;
            font-weight: 900;
            margin-top: 8px;
            border-bottom: none;
        }

        .inv-footer-signs {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1.5px dashed #cbd5e1;
            position: relative;
            z-index: 2;
        }
        .inv-seal-badge {
            border: 2px dashed #cbd5e1;
            border-radius: 999px;
            padding: 6px 18px;
            font-size: 11px;
            font-weight: 800;
            color: #64748b !important;
            background: #f8fafc !important;
        }

        .inv-watermark-deleted {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-18deg);
            border: 5px solid rgba(220, 38, 38, 0.32);
            color: rgba(220, 38, 38, 0.32) !important;
            font-size: 44px;
            font-weight: 900;
            padding: 10px 32px;
            border-radius: 14px;
            pointer-events: none;
            z-index: 10;
            white-space: nowrap;
        }

        /* ══════ القوالب اللونية الخمسة للورقة الرسمية (ثابتة ومتوافقة 100% مع html2canvas والطباعة) ══════ */
        /* 1) الكلاسيكي الملكي (Royal Navy & Sapphire + Gold) */
        .official-invoice-canvas.inv-theme-royal .inv-decor-orb     { background: #2563eb; }
        .official-invoice-canvas.inv-theme-royal .inv-stripe-primary { background: linear-gradient(90deg, #0f172a, #1d4ed8); }
        .official-invoice-canvas.inv-theme-royal .inv-header-row     { background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-color: #bfdbfe; }
        .official-invoice-canvas.inv-theme-royal .inv-doc-kind-pill  { background: #dbeafe; color: #1e40af !important; }
        .official-invoice-canvas.inv-theme-royal .inv-doc-title      { color: #0f172a !important; }
        .official-invoice-canvas.inv-theme-royal .inv-doc-num        { background: #0f172a; color: #fde047 !important; }
        .official-invoice-canvas.inv-theme-royal .inv-logo-badge     { background: linear-gradient(135deg, #0f172a, #1d4ed8); }
        .official-invoice-canvas.inv-theme-royal .inv-company-name   { color: #0f172a !important; }
        .official-invoice-canvas.inv-theme-royal .inv-client-card    { background: #f0f7ff !important; border: 1px solid #bfdbfe !important; border-right: 4px solid #1d4ed8 !important; }
        .official-invoice-canvas.inv-theme-royal .inv-card-tag       { color: #1d4ed8 !important; }
        .official-invoice-canvas.inv-theme-royal .inv-table thead th { background: linear-gradient(135deg, #0f172a, #1d4ed8) !important; }
        .official-invoice-canvas.inv-theme-royal .inv-row-num-chip   { background: #dbeafe; color: #1e40af !important; }
        .official-invoice-canvas.inv-theme-royal .inv-cell-total     { color: #1d4ed8 !important; }
        .official-invoice-canvas.inv-theme-royal .inv-total-line.grand { background: linear-gradient(135deg, #0f172a, #1d4ed8) !important; }

        /* 2) الزمردي الحيوي (Emerald & Forest) */
        .official-invoice-canvas.inv-theme-emerald .inv-decor-orb     { background: #10b981; }
        .official-invoice-canvas.inv-theme-emerald .inv-stripe-primary { background: linear-gradient(90deg, #064e3b, #059669); }
        .official-invoice-canvas.inv-theme-emerald .inv-header-row     { background: linear-gradient(135deg, #f8fafc 0%, #ecfdf5 100%); border-color: #a7f3d0; }
        .official-invoice-canvas.inv-theme-emerald .inv-doc-kind-pill  { background: #d1fae5; color: #065f46 !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-doc-title      { color: #064e3b !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-doc-num        { background: #064e3b; color: #fde047 !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-logo-badge     { background: linear-gradient(135deg, #064e3b, #059669); }
        .official-invoice-canvas.inv-theme-emerald .inv-company-name   { color: #064e3b !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-client-card    { background: #ecfdf5 !important; border: 1px solid #a7f3d0 !important; border-right: 4px solid #059669 !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-card-tag       { color: #059669 !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-table thead th { background: linear-gradient(135deg, #064e3b, #059669) !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-row-num-chip   { background: #d1fae5; color: #065f46 !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-cell-total     { color: #047857 !important; }
        .official-invoice-canvas.inv-theme-emerald .inv-total-line.grand { background: linear-gradient(135deg, #064e3b, #059669) !important; }

        /* 3) البنفسجي العصري (Royal Violet & Indigo) */
        .official-invoice-canvas.inv-theme-violet .inv-decor-orb     { background: #8b5cf6; }
        .official-invoice-canvas.inv-theme-violet .inv-stripe-primary { background: linear-gradient(90deg, #3b0764, #7c3aed); }
        .official-invoice-canvas.inv-theme-violet .inv-header-row     { background: linear-gradient(135deg, #faf5ff 0%, #eef2ff 100%); border-color: #ddd6fe; }
        .official-invoice-canvas.inv-theme-violet .inv-doc-kind-pill  { background: #ede9fe; color: #5b21b6 !important; }
        .official-invoice-canvas.inv-theme-violet .inv-doc-title      { color: #3b0764 !important; }
        .official-invoice-canvas.inv-theme-violet .inv-doc-num        { background: #4c1d95; color: #fde047 !important; }
        .official-invoice-canvas.inv-theme-violet .inv-logo-badge     { background: linear-gradient(135deg, #4c1d95, #7c3aed); }
        .official-invoice-canvas.inv-theme-violet .inv-company-name   { color: #3b0764 !important; }
        .official-invoice-canvas.inv-theme-violet .inv-client-card    { background: #f5f3ff !important; border: 1px solid #ddd6fe !important; border-right: 4px solid #7c3aed !important; }
        .official-invoice-canvas.inv-theme-violet .inv-card-tag       { color: #6d28d9 !important; }
        .official-invoice-canvas.inv-theme-violet .inv-table thead th { background: linear-gradient(135deg, #4c1d95, #7c3aed) !important; }
        .official-invoice-canvas.inv-theme-violet .inv-row-num-chip   { background: #ede9fe; color: #5b21b6 !important; }
        .official-invoice-canvas.inv-theme-violet .inv-cell-total     { color: #6d28d9 !important; }
        .official-invoice-canvas.inv-theme-violet .inv-total-line.grand { background: linear-gradient(135deg, #4c1d95, #7c3aed) !important; }

        /* 4) التركواز السماوي (Ocean Teal & Sky) */
        .official-invoice-canvas.inv-theme-teal .inv-decor-orb     { background: #06b6d4; }
        .official-invoice-canvas.inv-theme-teal .inv-stripe-primary { background: linear-gradient(90deg, #0c4a6e, #0d9488); }
        .official-invoice-canvas.inv-theme-teal .inv-header-row     { background: linear-gradient(135deg, #f0f9ff 0%, #f0fdfa 100%); border-color: #99f6e4; }
        .official-invoice-canvas.inv-theme-teal .inv-doc-kind-pill  { background: #ccfbf1; color: #0f766e !important; }
        .official-invoice-canvas.inv-theme-teal .inv-doc-title      { color: #0c4a6e !important; }
        .official-invoice-canvas.inv-theme-teal .inv-doc-num        { background: #0c4a6e; color: #fde047 !important; }
        .official-invoice-canvas.inv-theme-teal .inv-logo-badge     { background: linear-gradient(135deg, #0c4a6e, #0d9488); }
        .official-invoice-canvas.inv-theme-teal .inv-company-name   { color: #0c4a6e !important; }
        .official-invoice-canvas.inv-theme-teal .inv-client-card    { background: #f0fdfa !important; border: 1px solid #99f6e4 !important; border-right: 4px solid #0d9488 !important; }
        .official-invoice-canvas.inv-theme-teal .inv-card-tag       { color: #0f766e !important; }
        .official-invoice-canvas.inv-theme-teal .inv-table thead th { background: linear-gradient(135deg, #0c4a6e, #0d9488) !important; }
        .official-invoice-canvas.inv-theme-teal .inv-row-num-chip   { background: #ccfbf1; color: #0f766e !important; }
        .official-invoice-canvas.inv-theme-teal .inv-cell-total     { color: #0f766e !important; }
        .official-invoice-canvas.inv-theme-teal .inv-total-line.grand { background: linear-gradient(135deg, #0c4a6e, #0d9488) !important; }

        /* 5) العنابي الدافئ (Warm Crimson & Amber) */
        .official-invoice-canvas.inv-theme-crimson .inv-decor-orb     { background: #f43f5e; }
        .official-invoice-canvas.inv-theme-crimson .inv-stripe-primary { background: linear-gradient(90deg, #7f1d1d, #dc2626); }
        .official-invoice-canvas.inv-theme-crimson .inv-header-row     { background: linear-gradient(135deg, #fff1f2 0%, #fff7ed 100%); border-color: #fecdd3; }
        .official-invoice-canvas.inv-theme-crimson .inv-doc-kind-pill  { background: #ffe4e6; color: #9f1239 !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-doc-title      { color: #7f1d1d !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-doc-num        { background: #881337; color: #fde047 !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-logo-badge     { background: linear-gradient(135deg, #881337, #e11d48); }
        .official-invoice-canvas.inv-theme-crimson .inv-company-name   { color: #7f1d1d !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-client-card    { background: #fff1f2 !important; border: 1px solid #fecdd3 !important; border-right: 4px solid #e11d48 !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-card-tag       { color: #be123c !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-table thead th { background: linear-gradient(135deg, #881337, #e11d48) !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-row-num-chip   { background: #ffe4e6; color: #9f1239 !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-cell-total     { color: #be123c !important; }
        .official-invoice-canvas.inv-theme-crimson .inv-total-line.grand { background: linear-gradient(135deg, #881337, #e11d48) !important; }

        /* حركات لطيفة */
        @keyframes miRowSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes miPopIn {
            from { opacity: 0; transform: scale(0.92); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ══════ توافق الوضع الليلي للواجهة مع الحفاظ على سطوع الورقة الرسمية ══════ */
        html.dark-mode body, body.dark-mode {
            background: #0b1329 !important;
        }
        html.dark-mode .mi-zone-client, body.dark-mode .mi-zone-client {
            background: #111c36 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-zone-items, body.dark-mode .mi-zone-items {
            background: #151f38 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-zone-notes, body.dark-mode .mi-zone-notes,
        html.dark-mode .mi-zone-terms, body.dark-mode .mi-zone-terms {
            background: #111c36 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-summary-box, body.dark-mode .mi-summary-box {
            background: #111c36 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-sum-row-pill, body.dark-mode .mi-sum-row-pill {
            background: #151f38 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-tax-toggle-box, body.dark-mode .mi-tax-toggle-box {
            background: #151f38 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-tax-toggle-box.tax-active, body.dark-mode .mi-tax-toggle-box.tax-active {
            background: rgba(124, 58, 237, 0.16) !important;
            border-color: #7c3aed !important;
        }
        html.dark-mode .mi-kpi-card, body.dark-mode .mi-kpi-card {
            background: #151f38 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-kpi-card .mi-kpi-val, body.dark-mode .mi-kpi-card .mi-kpi-val {
            color: #e2e8f0 !important;
        }
        html.dark-mode .mi-zone-label, body.dark-mode .mi-zone-label {
            color: #93c5fd !important;
        }
        html.dark-mode .inv-palette-bar, body.dark-mode .inv-palette-bar {
            background: #151f38 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-quick-new-btn, body.dark-mode .mi-quick-new-btn {
            background: #151f38 !important;
            border-color: #263554 !important;
            color: #cbd5e1 !important;
        }
        html.dark-mode .mi-items-table tbody tr:nth-child(even), body.dark-mode .mi-items-table tbody tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.025) !important;
        }
        html.dark-mode .mi-items-table tbody tr:hover, body.dark-mode .mi-items-table tbody tr:hover {
            background-color: #1c2846 !important;
        }
        html.dark-mode .mi-line-total-pill, body.dark-mode .mi-line-total-pill {
            background: #0d1527 !important;
            color: #94a3b8 !important;
            border-color: #263554 !important;
        }
        html.dark-mode .mi-line-total-pill.has-val, body.dark-mode .mi-line-total-pill.has-val {
            background: rgba(16, 185, 129, 0.18) !important;
            color: #34d399 !important;
            border-color: #059669 !important;
        }
        html.dark-mode .mi-filter-pill:not(.active), body.dark-mode .mi-filter-pill:not(.active) {
            background: #151f38 !important;
            border-color: #263554 !important;
            color: #cbd5e1 !important;
        }
        html.dark-mode .inv-swatch-btn:not(.active), body.dark-mode .inv-swatch-btn:not(.active) {
            background: #0d1527 !important;
            border-color: #263554 !important;
            color: #cbd5e1 !important;
        }
        /* حماية ورقة الفاتورة الرسمية داخل المودال في الوضع الليلي */
        html.dark-mode #printableInvoiceArea, body.dark-mode #printableInvoiceArea {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        html.dark-mode #printableInvoiceArea .inv-table, body.dark-mode #printableInvoiceArea .inv-table {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        html.dark-mode #printableInvoiceArea .inv-table tbody td,
        body.dark-mode #printableInvoiceArea .inv-table tbody td {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #e2e8f0 !important;
        }
        html.dark-mode #printableInvoiceArea .inv-table tbody tr:nth-child(even) td,
        body.dark-mode #printableInvoiceArea .inv-table tbody tr:nth-child(even) td {
            background: #f8fafc !important;
        }
        html.dark-mode #printableInvoiceArea .inv-table tbody tr:hover td,
        body.dark-mode #printableInvoiceArea .inv-table tbody tr:hover td {
            background: #eff6ff !important;
        }
        html.dark-mode #printableInvoiceArea .inv-company-phone,
        body.dark-mode #printableInvoiceArea .inv-company-phone {
            background: #ffffff !important;
            color: #334155 !important;
            border-color: #cbd5e1 !important;
        }

        /* ══════ حاوية الطباعة المستقلة ══════ */
        #printOnlyContainer { display: none; }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }
            .app-sidebar,
            .sb-mobile-launcher,
            .sb-mobile-backdrop,
            .main-content,
            .modal,
            .modal-backdrop,
            .swal2-container,
            .no-print {
                display: none !important;
            }
            #printOnlyContainer {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            #printOnlyContainer .official-invoice-canvas {
                width: 100% !important;
                max-width: 100% !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 20px 24px !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            tr, .inv-bottom-grid, .inv-totals-col {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .mi-hero { margin-top: 4px; padding: 18px 18px; }
            .mi-hero-title { font-size: 1.25rem; }
            .mi-card { padding: 15px; }
            .mi-zone-client, .mi-zone-items { padding: 14px; }
        }
    </style>
</head>
<body>
@include('sidebar')

{{-- حاوية الطباعة النظيفة المستقلة (تظهر فقط عند الطباعة لتخرج صفحة A4 مثالية) --}}
<div id="printOnlyContainer"></div>

<div class="main-content">

    {{-- ══════════ Hero Header الملون ══════════ --}}
    <div class="mi-hero no-print">
        <div class="mi-hero-brand">
            <div class="mi-hero-icon">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h2 class="mi-hero-title">الفواتير اليدوية</h2>
                <div class="mi-hero-sub">أنشئ فواتير مبيعات أو ضريبية بتصميم ملون أنيق، واطبعها أو أرسلها كصورة للواتساب فوراً</div>
            </div>
        </div>

        <div class="mi-hero-pills">
            <div class="mi-hero-pill">
                <div class="mi-hero-pill-icon emerald">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="mi-hero-pill-lbl">الفواتير السارية</div>
                    <div class="mi-hero-pill-val">{{ $stats['active'] }} فاتورة</div>
                </div>
            </div>
            <div class="mi-hero-pill">
                <div class="mi-hero-pill-icon amber">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <div class="mi-hero-pill-lbl">إجمالي الساري</div>
                    <div class="mi-hero-pill-val">{!! finMask(fmtMoney($stats['amount'] ?? 0) . ' ج.م') !!}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════ شريط التبويبات ══════════ --}}
    <div class="mi-tabs-bar no-print">
        <ul class="nav mi-nav-tabs mb-0" id="miMainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-create-btn active" id="btn-tab-create" data-bs-toggle="tab" data-bs-target="#pane-create" type="button" role="tab">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <span id="tabCreateTitle">إنشاء فاتورة جديدة</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-log-btn" id="btn-tab-log" data-bs-toggle="tab" data-bs-target="#pane-log" type="button" role="tab">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>سجل الفواتير</span>
                    <span class="mi-tab-count-badge">{{ $stats['total'] }}</span>
                </button>
            </li>
        </ul>

        <button type="button" class="mi-quick-new-btn" onclick="resetInvoiceForm()" title="تفريغ الحقول وبدء فاتورة جديدة">
            <i class="fa-solid fa-wand-magic-sparkles text-primary"></i>
            <span>فاتورة جديدة / تفريغ</span>
        </button>
    </div>

    <div class="tab-content no-print" id="miTabsContent">

        {{-- ════════════════════════════════════════════════════════════════
             TAB 1: إنشاء / تعديل فاتورة يدوية
             ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="pane-create" role="tabpanel">
            <div class="mi-card" id="invoiceFormCard">
                <div class="mi-card-accent-bar"></div>

                <div class="mi-section-head">
                    <h5 class="mi-section-title" id="formCardTitle">
                        <span class="mi-title-icon blue"><i class="fa-solid fa-file-circle-plus"></i></span>
                        <span>بيانات الفاتورة والعميل</span>
                    </h5>
                    <div id="editModeBanner" class="d-none align-items-center gap-2 mi-edit-banner-box">
                        <span class="mi-badge-edited">
                            <i class="fa fa-pen-to-square"></i> وضع تعديل الفاتورة: <strong id="editModeInvNum"></strong>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-bold" onclick="resetInvoiceForm()">
                            <i class="fa fa-xmark me-1"></i> إلغاء التعديل وإنشاء جديد
                        </button>
                    </div>
                </div>

                <form id="manualInvoiceForm" onsubmit="submitManualInvoice(event)">
                    <input type="hidden" id="mi_id" value="">

                    {{-- أ. بيانات العميل والفاتورة --}}
                    <div class="mi-zone-client">
                        <div class="mi-zone-header">
                            <span class="mi-zone-label">
                                <span class="mi-zone-dot indigo"><i class="fa-solid fa-user-tag"></i></span>
                                <span>1. نوع المستند وبيانات العميل</span>
                            </span>
                            <span class="small text-muted fw-bold">اختر نوع المستند أو اكتب المسمى يدوياً</span>
                        </div>

                        {{-- شرائح اختيار سريع لمسمى الفاتورة --}}
                        <div class="mi-doc-chips" id="miDocChips">
                            <button type="button" class="mi-doc-chip active" data-color="blue" data-doc="فاتورة مبيعات" onclick="selectDocTypeChip('فاتورة مبيعات', this)">
                                <i class="fa-solid fa-receipt"></i> فاتورة مبيعات
                            </button>
                            <button type="button" class="mi-doc-chip" data-color="purple" data-doc="فاتورة ضريبية" onclick="selectDocTypeChip('فاتورة ضريبية', this)">
                                <i class="fa-solid fa-percent"></i> فاتورة ضريبية
                            </button>
                            <button type="button" class="mi-doc-chip" data-color="amber" data-doc="بيان أسعار وتوريد" onclick="selectDocTypeChip('بيان أسعار وتوريد', this)">
                                <i class="fa-solid fa-clipboard-list"></i> بيان أسعار وتوريد
                            </button>
                            <button type="button" class="mi-doc-chip" data-color="emerald" data-doc="عرض سعر" onclick="selectDocTypeChip('عرض سعر', this)">
                                <i class="fa-solid fa-tag"></i> عرض سعر
                            </button>
                            <button type="button" class="mi-doc-chip" data-color="rose" data-doc="فاتورة أعمال وتشطيبات" onclick="selectDocTypeChip('فاتورة أعمال وتشطيبات', this)">
                                <i class="fa-solid fa-helmet-safety"></i> أعمال وتشطيبات
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">مسمى المستند / الفاتورة</label>
                                <div class="input-group mi-input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
                                    <input type="text" id="mi_title" class="form-control fw-bold" value="فاتورة مبيعات" list="docTitlesList" placeholder="مثال: فاتورة مبيعات / بيان أسعار" oninput="syncDocTypeChips(this.value)">
                                </div>
                                <datalist id="docTitlesList">
                                    <option value="فاتورة مبيعات">
                                    <option value="فاتورة ضريبية">
                                    <option value="بيان أسعار وتوريد">
                                    <option value="عرض سعر">
                                    <option value="فاتورة أعمال وتشطيبات">
                                </datalist>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">
                                    اسم العميل <span class="text-danger">*</span>
                                    <span id="clientAutoMatchBadge" class="mi-client-match-pill"><i class="fa-solid fa-check"></i> عميل مسجل</span>
                                </label>
                                <div class="input-group mi-input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" id="mi_client_name" class="form-control fw-bold" list="clientsDatalist" placeholder="اكتب اسم العميل..." required oninput="onClientNameInput(this.value)">
                                </div>
                                <datalist id="clientsDatalist">
                                    @foreach($clientsList as $c)
                                        <option value="{{ $c->name }}" data-phone="{{ $c->phone }}" data-address="{{ $c->address }}">{{ $c->phone }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">رقم الهاتف (للواتساب)</label>
                                <div class="input-group mi-input-group">
                                    <span class="input-group-text text-success"><i class="fa-brands fa-whatsapp"></i></span>
                                    <input type="text" id="mi_client_phone" class="form-control fw-bold" placeholder="01xxxxxxxxx" dir="ltr">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">عنوان العميل</label>
                                <div class="input-group mi-input-group">
                                    <span class="input-group-text text-warning"><i class="fa-solid fa-location-dot"></i></span>
                                    <input type="text" id="mi_client_address" class="form-control" placeholder="المدينة / المنطقة / الشارع">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">تاريخ الفاتورة <span class="text-danger">*</span></label>
                                <input type="date" id="mi_date" class="form-control fw-bold" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- ب. جدول الأصناف والبنود --}}
                    <div class="mi-zone-items">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="mi-zone-label text-success">
                                    <span class="mi-zone-dot emerald"><i class="fa-solid fa-list-check"></i></span>
                                    <span>2. الأصناف والبنود المسجلة بالفاتورة</span>
                                </span>
                                <span class="mi-live-pill indigo" id="mi_live_items_badge">
                                    <i class="fa-solid fa-cubes"></i> <span>1 بند</span>
                                </span>
                                <span class="mi-live-pill teal" id="mi_live_qty_badge">
                                    <i class="fa-solid fa-scale-balanced"></i> <span>إجمالي الكميات: 1</span>
                                </span>
                            </div>

                            <button type="button" class="btn btn-sm btn-mi-add-row" onclick="addInvoiceItemRow({}, true)">
                                <i class="fa fa-plus me-1"></i> إضافة صنف / بند
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered mi-items-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th>الصنف / البند / البيان <span class="text-warning">*</span></th>
                                        <th style="width: 130px;" class="text-center">الكمية</th>
                                        <th style="width: 140px;" class="text-center">الوحدة</th>
                                        <th style="width: 160px;" class="text-center">سعر الوحدة (ج.م)</th>
                                        <th style="width: 160px;" class="text-center">الإجمالي (ج.م)</th>
                                        <th style="width: 55px;" class="text-center">حذف</th>
                                    </tr>
                                </thead>
                                <tbody id="mi_items_tbody">
                                    {{-- Populated by JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <datalist id="unitsDatalist">
                        @foreach($units as $u)
                            <option value="{{ $u }}">
                        @endforeach
                    </datalist>

                    {{-- ج. الملاحظات والشروط والأحكام + الضريبة وملخص الإجماليات --}}
                    <div class="row g-3 align-items-start">
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="mi-zone-notes">
                                        <label class="form-label small fw-bold text-primary d-flex align-items-center gap-2 mb-2">
                                            <span class="mi-zone-dot sky"><i class="fa fa-comment-dots"></i></span>
                                            <span>ملاحظات عامة على الفاتورة (تظهر أسفل الأصناف)</span>
                                        </label>
                                        <textarea id="mi_notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية خاصة بالعميل أو التسليم..."></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mi-zone-terms">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <label class="form-label small fw-bold m-0 d-flex align-items-center gap-2" style="color:#b45309;">
                                                <span class="mi-zone-dot amber"><i class="fa fa-file-contract"></i></span>
                                                <span>الشروط والأحكام (كل سطر يظهر كبند مستقل)</span>
                                            </label>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <button type="button" class="mi-term-chip amber" onclick="appendTermPreset('البضاعة المباعة ترد أو تستبدل خلال 14 يوماً بحالتها الأصلية وبأصل الفاتورة.')">+ سياسة الاستبدال</button>
                                                <button type="button" class="mi-term-chip emerald" onclick="appendTermPreset('تم استلام الأصناف الموضحة أعلاه بحالة جيدة ومطابقة للمواصفات.')">+ إقرار استلام</button>
                                                <button type="button" class="mi-term-chip indigo" onclick="appendTermPreset('الأسعار تشمل التوريد فقط ولا تشمل مصاريف النقل أو التركيب ما لم يذكر خلاف ذلك.')">+ شرط التوريد</button>
                                            </div>
                                        </div>
                                        <textarea id="mi_terms" class="form-control" rows="3" placeholder="اكتب الشروط والأحكام (سطر لكل شرط)..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="mi-summary-box">
                                <div class="mi-summary-head">
                                    <span class="fw-black text-dark d-flex align-items-center gap-2">
                                        <span class="mi-zone-dot indigo"><i class="fa-solid fa-calculator"></i></span>
                                        <span>ملخص الحساب النهائي</span>
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-2 py-1" style="font-size: 0.72rem;">حساب فوري</span>
                                </div>

                                <div class="mi-sum-row-pill d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-muted small"><i class="fa-solid fa-layer-group text-primary me-1"></i> إجمالي الأصناف:</span>
                                    <span class="fw-black fs-6 text-primary" id="mi_sum_subtotal">0 ج.م</span>
                                </div>

                                <div class="mi-sum-row-pill discount row g-2 mx-0 align-items-center">
                                    <div class="col-6 px-0">
                                        <label class="small fw-bold text-danger mb-0"><i class="fa-solid fa-tags me-1"></i> خصم خاص (ج.م):</label>
                                    </div>
                                    <div class="col-6 px-0">
                                        <input type="number" step="any" min="0" id="mi_discount" class="form-control form-control-sm text-center fw-bold text-danger" value="0" oninput="recalcManualInvoice()">
                                    </div>
                                </div>

                                {{-- خيار فاتورة ضريبية --}}
                                <div class="mi-tax-toggle-box mb-2" id="mi_tax_box">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-check-label fw-bold small text-dark" for="mi_is_tax" style="cursor:pointer;">
                                            <i class="fa fa-percent text-primary me-1"></i> تفعيل فاتورة ضريبية
                                        </label>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="mi_is_tax" onchange="toggleTaxInvoiceOption(this.checked)">
                                        </div>
                                    </div>
                                    <div id="wrap_mi_tax_rate" style="display:none;" class="mt-2 pt-2 border-top">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-6">
                                                <label class="small fw-bold mb-0" style="color:#6d28d9;">نسبة الضريبة (%):</label>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" max="100" id="mi_tax_rate" class="form-control text-center fw-bold" style="color:#6d28d9;" value="0" oninput="recalcManualInvoice()">
                                                    <span class="input-group-text fw-bold">%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 small">
                                            <span class="fw-bold text-muted">قيمة الضريبة المضافة:</span>
                                            <span class="fw-black" style="color:#6d28d9;" id="mi_sum_tax">0 ج.م</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mi-sum-row-pill paid row g-2 mx-0 align-items-center">
                                    <div class="col-6 px-0">
                                        <label class="small fw-bold text-success mb-0"><i class="fa-solid fa-money-bill-wave me-1"></i> المدفوع (اختياري):</label>
                                    </div>
                                    <div class="col-6 px-0">
                                        <input type="number" step="any" min="0" id="mi_paid" class="form-control form-control-sm text-center fw-bold text-success" placeholder="اختياري" oninput="recalcManualInvoice()">
                                    </div>
                                </div>

                                <div class="mi-grand-banner">
                                    <span class="lbl"><i class="fa-solid fa-wallet me-1 text-warning"></i> الإجمالي النهائي:</span>
                                    <span class="val" id="mi_sum_grand">0 ج.م</span>
                                </div>

                                <div class="mi-remaining-alert d-flex justify-content-between align-items-center" id="mi_remaining_row" style="display:none !important;">
                                    <span class="small fw-bold text-danger"><i class="fa-solid fa-hourglass-half me-1"></i> المتبقي على العميل:</span>
                                    <span class="fw-black text-danger" id="mi_sum_remaining">0 ج.م</span>
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" id="mi_submit_btn" class="btn btn-mi-submit flex-grow-1">
                                        <i class="fa fa-floppy-disk me-1"></i> <span id="mi_submit_lbl">حفظ الفاتورة وعرضها للطباعة / واتساب</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
             TAB 2: سجل الفواتير اليدوية
             ════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="pane-log" role="tabpanel">
            {{-- Stats الملونة --}}
            <div class="mi-kpi-grid">
                <div class="mi-kpi-card indigo">
                    <div class="mi-kpi-top">
                        <span class="mi-kpi-lbl">إجمالي الفواتير بالسجل</span>
                        <span class="mi-kpi-ico indigo"><i class="fa-solid fa-layer-group"></i></span>
                    </div>
                    <div class="mi-kpi-val">{{ $stats['total'] }}</div>
                </div>

                <div class="mi-kpi-card emerald">
                    <div class="mi-kpi-top">
                        <span class="mi-kpi-lbl">الفواتير السارية</span>
                        <span class="mi-kpi-ico emerald"><i class="fa-solid fa-circle-check"></i></span>
                    </div>
                    <div class="mi-kpi-val">{{ $stats['active'] }}</div>
                </div>

                <div class="mi-kpi-card amber">
                    <div class="mi-kpi-top">
                        <span class="mi-kpi-lbl">فواتير تم تعديلها</span>
                        <span class="mi-kpi-ico amber"><i class="fa-solid fa-pen-to-square"></i></span>
                    </div>
                    <div class="mi-kpi-val">{{ $stats['edited'] }}</div>
                </div>

                <div class="mi-kpi-card rose">
                    <div class="mi-kpi-top">
                        <span class="mi-kpi-lbl">فواتير محذوفة</span>
                        <span class="mi-kpi-ico rose"><i class="fa-solid fa-trash-can"></i></span>
                    </div>
                    <div class="mi-kpi-val">{{ $stats['deleted'] }}</div>
                </div>

                <div class="mi-kpi-card violet">
                    <div class="mi-kpi-top">
                        <span class="mi-kpi-lbl">إجمالي قيمة الساري</span>
                        <span class="mi-kpi-ico violet"><i class="fa-solid fa-sack-dollar"></i></span>
                    </div>
                    <div class="mi-kpi-val">{!! finMask(fmtMoney($stats['amount'] ?? 0) . ' ج.م') !!}</div>
                </div>
            </div>

            <div class="mi-card">
                <div class="mi-card-accent-bar"></div>
                <div class="mi-section-head">
                    <h5 class="mi-section-title">
                        <span class="mi-title-icon teal"><i class="fa-solid fa-clock-rotate-left"></i></span>
                        <span>سجل الفواتير اليدوية</span>
                    </h5>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="d-flex gap-1 flex-wrap" id="miStatusFilters">
                            <button type="button" class="mi-filter-pill active" data-filter="all" onclick="setMiFilter('all', this)">
                                <i class="fa-solid fa-border-all"></i> الكل
                            </button>
                            <button type="button" class="mi-filter-pill" data-filter="active" onclick="setMiFilter('active', this)">
                                <i class="fa-solid fa-circle-check"></i> سارية
                            </button>
                            <button type="button" class="mi-filter-pill" data-filter="edited" onclick="setMiFilter('edited', this)">
                                <i class="fa-solid fa-pen"></i> تم تعديلها
                            </button>
                            <button type="button" class="mi-filter-pill" data-filter="deleted" onclick="setMiFilter('deleted', this)">
                                <i class="fa-solid fa-trash"></i> محذوفة
                            </button>
                        </div>
                        <div style="min-width: 265px;">
                            <div class="input-group input-group-sm mi-input-group">
                                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" id="miSearchInput" class="form-control" placeholder="ابحث باسم العميل، الهاتف، الصنف، رقم الفاتورة..." oninput="filterInvoicesLog()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 mi-log-table" id="miLogTable">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>التاريخ</th>
                                <th>بيانات العميل</th>
                                <th>الأصناف والبنود</th>
                                <th>الإجمالي</th>
                                <th>حالة الفاتورة</th>
                                <th class="text-center">إجراءات (عرض / واتساب / تعديل / حذف)</th>
                            </tr>
                        </thead>
                        <tbody id="miLogTbody">
                            @forelse($invoices as $inv)
                                @php
                                    $rowStatus = $inv->is_deleted ? 'deleted' : ($inv->is_edited ? 'edited' : 'active');
                                    $rowClass = $inv->is_deleted ? 'mi-row-deleted' : ($inv->is_edited ? 'mi-row-edited' : '');
                                    $avatarIdx = $loop->index % 6;
                                    $clientInitial = mb_substr(trim((string) $inv->client_name), 0, 1) ?: 'ع';
                                @endphp
                                <tr class="{{ $rowClass }}"
                                    data-id="{{ $inv->id }}"
                                    data-status="{{ $rowStatus }}"
                                    data-search="{{ $inv->invoice_number }} {{ $inv->invoice_title }} {{ $inv->client_name }} {{ $inv->client_phone }} {{ $inv->client_address }} {{ $inv->items_summary }} {{ $inv->notes }}">
                                    <td>
                                        <div class="mi-inv-num-pill">{{ $inv->invoice_number }}</div>
                                        <div class="small text-muted fw-bold">
                                            {{ $inv->invoice_title }}
                                            @if(floatval($inv->tax_rate) > 0)
                                                <span class="badge rounded-pill ms-1" style="background:#f3e8ff; color:#7e22ce; border:1px solid #d8b4fe;">ضريبية {{ floatval($inv->tax_rate) }}%</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                            <i class="fa-regular fa-calendar me-1 text-primary"></i>{{ $inv->invoice_date }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="mi-client-avatar mi-av-{{ $avatarIdx }}">{{ $clientInitial }}</span>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $inv->client_name }}</div>
                                                @if($inv->client_phone)
                                                    <div class="small text-muted" dir="ltr"><i class="fa fa-phone text-success me-1"></i>{{ $inv->client_phone }}</div>
                                                @endif
                                                @if($inv->client_address)
                                                    <div class="small text-muted"><i class="fa fa-location-dot text-warning me-1"></i>{{ $inv->client_address }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 260px;">
                                        <span class="badge rounded-pill mb-1" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd;">
                                            <i class="fa-solid fa-box-open me-1"></i>{{ count($inv->items) }} بنود
                                        </span>
                                        <div class="small text-muted text-truncate" title="{{ $inv->items_summary }}">{{ $inv->items_summary }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-black fs-6 {{ $inv->is_deleted ? 'text-decoration-line-through text-muted' : 'text-primary' }}">
                                            {{ number_format($inv->grand_total, 2) }} ج.م
                                        </div>
                                        @if($inv->discount_amount > 0)
                                            <div class="small text-danger fw-bold">خصم: {{ number_format($inv->discount_amount, 2) }} ج</div>
                                        @endif
                                        @if($inv->tax_amount > 0)
                                            <div class="small fw-bold" style="color:#7c3aed;">ضريبة ({{ floatval($inv->tax_rate) }}%): {{ number_format($inv->tax_amount, 2) }} ج</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->is_deleted)
                                            <span class="mi-badge-deleted">
                                                <i class="fa fa-trash-can"></i> فاتورة محذوفة
                                            </span>
                                            <div class="small text-danger mt-1" style="font-size: 0.72rem;">
                                                حُذفت في: {{ $inv->deleted_at ? \Carbon\Carbon::parse($inv->deleted_at)->format('Y-m-d h:i A') : '-' }}
                                                @if($inv->deleted_by) ({{ $inv->deleted_by }}) @endif
                                            </div>
                                            @if($inv->delete_reason)
                                                <div class="small text-muted" style="font-size: 0.72rem;">السبب: {{ $inv->delete_reason }}</div>
                                            @endif
                                        @endif

                                        @if($inv->is_edited)
                                            <div class="{{ $inv->is_deleted ? 'mt-1' : '' }}">
                                                <span class="mi-badge-edited">
                                                    <i class="fa fa-pen-to-square"></i> تم تعديلها ({{ $inv->edit_count }})
                                                </span>
                                                <div class="small text-warning-emphasis mt-1" style="font-size: 0.72rem;">
                                                    آخر تعديل: {{ $inv->last_edited_at ? \Carbon\Carbon::parse($inv->last_edited_at)->format('Y-m-d h:i A') : '-' }}
                                                    @if($inv->last_edited_by) ({{ $inv->last_edited_by }}) @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if(!$inv->is_deleted && !$inv->is_edited)
                                            <span class="mi-badge-active">
                                                <i class="fa fa-circle-check"></i> أصلية / سارية
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <button type="button" class="btn-mi-act view" onclick="openInvoicePreview({{ $inv->id }})" title="عرض وطباعة الفاتورة">
                                                <i class="fa fa-eye"></i> عرض / طباعة
                                            </button>
                                            <button type="button" class="btn-mi-act img" onclick="openInvoicePreview({{ $inv->id }}, 'image')" title="حفظ الفاتورة كصورة">
                                                <i class="fa fa-image"></i> صورة
                                            </button>
                                            <button type="button" class="btn-mi-act wa" onclick="openInvoicePreview({{ $inv->id }}, 'whatsapp')" title="إرسال كصورة للواتساب">
                                                <i class="fab fa-whatsapp"></i> واتساب
                                            </button>
                                            <button type="button" class="btn-mi-act edit" onclick="loadInvoiceForEdit({{ $inv->id }})" title="تعديل الفاتورة">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                            @if(!$inv->is_deleted)
                                                <button type="button" class="btn-mi-act del" onclick="deleteManualInvoice({{ $inv->id }}, '{{ $inv->invoice_number }}')" title="حذف الفاتورة">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="miEmptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa fa-file-invoice fs-1 mb-2 d-block text-primary opacity-50"></i>
                                        لا توجد فواتير يدوية مسجلة حتى الآن.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══════ Modal عرض وطباعة وتصدير الفاتورة كصورة ══════ --}}
<div class="modal fade" id="invoicePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #f1f5f9;">
            <div class="modal-header bg-white border-bottom no-print d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="mi-title-icon blue" style="width:34px;height:34px;border-radius:10px;"><i class="fa fa-file-invoice"></i></span>
                    <h5 class="modal-title fw-black text-dark m-0">معاينة الفاتورة الرسمية</h5>
                    <span id="pv_status_badges"></span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold" onclick="printCurrentInvoice()">
                        <i class="fa fa-print me-1"></i> طباعة / PDF
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" onclick="downloadCurrentInvoiceImage()">
                        <i class="fa fa-download me-1"></i> حفظ كصورة (JPG)
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="sendCurrentInvoiceWhatsApp()">
                        <i class="fab fa-whatsapp me-1"></i> إرسال كصورة للواتساب
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4">
                @php
                    $companyName = $settings['invoice_header_text'] ?? 'شركة الضبع للتجارة والتوريدات والمقاولات';
                    $companyPhone = $settings['company_phone'] ?? '01000000000';
                @endphp

                {{-- شريط اختيار لون/طابع الفاتورة قبل الطباعة أو الإرسال --}}
                <div class="inv-palette-bar no-print">
                    <span class="small fw-black text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-palette text-primary"></i>
                        <span>طابع ألوان الفاتورة (للطباعة والصورة):</span>
                    </span>
                    <div class="d-flex align-items-center gap-2 flex-wrap" id="invPaletteSwatches">
                        <button type="button" class="inv-swatch-btn active" data-theme="royal" onclick="setInvoiceTheme('royal', this)">
                            <span class="inv-swatch-dot" style="background:linear-gradient(135deg,#0f172a,#1d4ed8);"></span> أزرق ملكي
                        </button>
                        <button type="button" class="inv-swatch-btn" data-theme="emerald" onclick="setInvoiceTheme('emerald', this)">
                            <span class="inv-swatch-dot" style="background:linear-gradient(135deg,#064e3b,#10b981);"></span> زمردي
                        </button>
                        <button type="button" class="inv-swatch-btn" data-theme="violet" onclick="setInvoiceTheme('violet', this)">
                            <span class="inv-swatch-dot" style="background:linear-gradient(135deg,#4c1d95,#7c3aed);"></span> بنفسجي
                        </button>
                        <button type="button" class="inv-swatch-btn" data-theme="teal" onclick="setInvoiceTheme('teal', this)">
                            <span class="inv-swatch-dot" style="background:linear-gradient(135deg,#0c4a6e,#0d9488);"></span> تركواز سماوي
                        </button>
                        <button type="button" class="inv-swatch-btn" data-theme="crimson" onclick="setInvoiceTheme('crimson', this)">
                            <span class="inv-swatch-dot" style="background:linear-gradient(135deg,#881337,#e11d48);"></span> عنابي دافئ
                        </button>
                    </div>
                </div>

                {{-- لوحة سجل التعديلات والحذف (تظهر في الشاشة للمراجعة) --}}
                <div id="pv_audit_alert" class="alert alert-warning small mb-3 no-print" style="display:none;"></div>

                {{-- الورقة الرسمية القابلة للطباعة والتصوير (بدون QR) --}}
                <div id="printableInvoiceArea" class="official-invoice-canvas inv-theme-royal">
                    <div class="inv-decor-orb"></div>

                    <div class="inv-top-stripes">
                        <div class="inv-stripe-primary"></div>
                        <div class="inv-stripe-gold"></div>
                    </div>

                    <div id="pv_deleted_watermark" class="inv-watermark-deleted" style="display:none;">فاتورة ملغاة / محذوفة</div>

                    {{-- رأس الفاتورة الملون --}}
                    <div class="inv-header-row">
                        <div>
                            <div class="inv-doc-kind-pill" id="pv_doc_kind_tag">
                                <i class="fa-solid fa-file-invoice"></i>
                                <span>مستند مالي رسمي</span>
                            </div>
                            <h1 class="inv-doc-title" id="pv_doc_title">فاتورة مبيعات</h1>
                            <div class="inv-doc-num" id="pv_doc_num">INV-0000</div>
                            <div id="pv_doc_state_badge"></div>
                        </div>

                        <div class="inv-company-wrap">
                            <div class="inv-logo-badge">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <div class="inv-company-box">
                                <h2 class="inv-company-name">{{ $companyName }}</h2>
                                <div class="inv-company-sub">AL-DABAA TRADING & CONTRACTING</div>
                                <div class="inv-company-phone" dir="ltr">
                                    <i class="fa fa-phone text-success"></i>
                                    <span>{{ $companyPhone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- بيانات العميل + بيانات المستند (بدون QR) --}}
                    <div class="inv-client-meta-grid">
                        <div class="inv-client-card">
                            <div class="inv-card-tag"><i class="fa-solid fa-user-check"></i> مقدم إلى العميل:</div>
                            <div class="inv-client-name-val" id="pv_client_name">—</div>
                            <div class="inv-client-detail" id="pv_client_phone_wrap" style="display:none;">
                                <i class="fa fa-phone text-success me-1"></i><span id="pv_client_phone" dir="ltr"></span>
                            </div>
                            <div class="inv-client-detail" id="pv_client_address_wrap" style="display:none;">
                                <i class="fa fa-location-dot text-danger me-1"></i><span id="pv_client_address"></span>
                            </div>
                        </div>

                        <div class="inv-meta-card">
                            <div class="inv-card-tag" style="color:#b45309 !important;"><i class="fa-solid fa-circle-info"></i> تفاصيل المستند:</div>
                            <div class="inv-meta-row">
                                <span style="color:#64748b !important;">رقم الفاتورة:</span>
                                <span style="color:#0f172a !important; font-weight:900;" id="pv_meta_num" dir="ltr">—</span>
                            </div>
                            <div class="inv-meta-row">
                                <span style="color:#64748b !important;">تاريخ الإصدار:</span>
                                <span style="color:#0f172a !important; font-weight:900;" id="pv_date">—</span>
                            </div>
                            <div class="inv-meta-row">
                                <span style="color:#64748b !important;">عدد البنود:</span>
                                <span style="color:#0f172a !important; font-weight:900;" id="pv_items_count">0</span>
                            </div>
                        </div>
                    </div>

                    {{-- جدول الأصناف والبنود --}}
                    <table class="inv-table">
                        <thead>
                            <tr>
                                <th style="width: 48px;">م</th>
                                <th class="text-right">الصنف / البند / البيان</th>
                                <th style="width: 100px;">الكمية</th>
                                <th style="width: 100px;">الوحدة</th>
                                <th style="width: 130px;">سعر الوحدة</th>
                                <th style="width: 140px;">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody id="pv_items_tbody"></tbody>
                    </table>

                    {{-- التفقيط --}}
                    <div class="inv-tafqeet-bar">
                        <span class="inv-tafqeet-lbl"><i class="fa-solid fa-coins me-1"></i> المبلغ بالحروف:</span>
                        <span class="inv-tafqeet-val" id="pv_tafqeet">—</span>
                    </div>

                    {{-- الملاحظات والشروط + ملخص الحساب --}}
                    <div class="inv-bottom-grid">
                        <div class="inv-notes-terms-col">
                            <div id="pv_notes_box" class="inv-notes-card" style="display:none;">
                                <div class="fw-black small mb-1" style="color:#0369a1 !important;"><i class="fa fa-comment-dots me-1"></i> ملاحظات:</div>
                                <div class="small fw-bold" id="pv_notes_text" style="white-space: pre-line; color:#334155 !important;"></div>
                            </div>

                            <div id="pv_terms_box" class="inv-terms-card" style="display:none;">
                                <div class="fw-black small mb-1" style="color:#b45309 !important;"><i class="fa fa-file-contract me-1"></i> الشروط والأحكام:</div>
                                <ul class="small fw-bold ps-3 mb-0" id="pv_terms_list" style="line-height: 1.75; color:#475569 !important;"></ul>
                            </div>
                        </div>

                        <div class="inv-totals-col">
                            <div class="inv-total-line">
                                <span style="color:#64748b !important;">إجمالي الأصناف:</span>
                                <span id="pv_subtotal">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line is-discount" id="pv_discount_row" style="display:none;">
                                <span>الخصم:</span>
                                <span id="pv_discount">-0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line is-tax" id="pv_tax_row" style="display:none;">
                                <span id="pv_tax_label">ضريبة القيمة المضافة (14%):</span>
                                <span id="pv_tax_amount">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line grand">
                                <span>الإجمالي النهائي:</span>
                                <span id="pv_grand_total">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line is-paid mt-1" id="pv_paid_row" style="display:none;">
                                <span>المبلغ المسدد:</span>
                                <span id="pv_paid">0.00 ج.م</span>
                            </div>
                            <div class="inv-total-line is-rem" id="pv_rem_row" style="display:none;">
                                <span>المتبقي المستحق:</span>
                                <span id="pv_remaining">0.00 ج.م</span>
                            </div>
                        </div>
                    </div>

                    {{-- التوقيعات والختم --}}
                    <div class="inv-footer-signs">
                        <div class="text-center" style="min-width: 170px;">
                            <div class="small fw-bold mb-4" style="color:#64748b !important;">توقيع المستلم / العميل</div>
                            <div style="border-bottom: 1.5px dashed #94a3b8; width: 155px; margin: 0 auto;"></div>
                        </div>
                        <div class="inv-seal-badge">
                            <i class="fa-solid fa-certificate text-warning me-1"></i> مستند معتمد • شركة الضبع
                        </div>
                        <div class="text-center" style="min-width: 170px;">
                            <div class="small fw-bold mb-4" style="color:#64748b !important;">إدارة المبيعات / الختم</div>
                            <div style="border-bottom: 1.5px dashed #94a3b8; width: 155px; margin: 0 auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const KNOWN_CLIENTS = @json($clientsList);
let currentPreviewInvoice = null;
let currentStatusFilter = 'all';
const INV_THEMES = ['royal', 'emerald', 'violet', 'teal', 'crimson'];

// ── تهيئة سطور الأصناف واسترجاع لون الفاتورة المفضل عند فتح الصفحة ──
document.addEventListener('DOMContentLoaded', () => {
    addInvoiceItemRow();
    try {
        const savedTheme = localStorage.getItem('mi_invoice_palette');
        if (savedTheme && INV_THEMES.includes(savedTheme)) {
            const btn = document.querySelector(`#invPaletteSwatches .inv-swatch-btn[data-theme="${savedTheme}"]`);
            setInvoiceTheme(savedTheme, btn);
        }
    } catch (e) {}
});

function setInvoiceTheme(theme, btn) {
    if (!INV_THEMES.includes(theme)) theme = 'royal';
    const canvas = document.getElementById('printableInvoiceArea');
    if (canvas) {
        INV_THEMES.forEach(t => canvas.classList.remove('inv-theme-' + t));
        canvas.classList.add('inv-theme-' + theme);
    }
    document.querySelectorAll('#invPaletteSwatches .inv-swatch-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    try { localStorage.setItem('mi_invoice_palette', theme); } catch (e) {}
    syncPrintClone();
}

function syncPrintClone() {
    const printContainer = document.getElementById('printOnlyContainer');
    const area = document.getElementById('printableInvoiceArea');
    if (!printContainer || !area) return;
    printContainer.innerHTML = '';
    const clone = area.cloneNode(true);
    clone.removeAttribute('id');
    printContainer.appendChild(clone);
}

function switchMiTab(tabId) {
    const btn = document.getElementById(tabId);
    if (btn) {
        bootstrap.Tab.getOrCreateInstance(btn).show();
    }
}

function selectDocTypeChip(title, btn) {
    const input = document.getElementById('mi_title');
    input.value = title;
    syncDocTypeChips(title);

    const taxToggle = document.getElementById('mi_is_tax');
    if (title === 'فاتورة ضريبية' && taxToggle && !taxToggle.checked) {
        taxToggle.checked = true;
        toggleTaxInvoiceOption(true);
    }
}

function syncDocTypeChips(val) {
    const clean = (val || '').trim();
    document.querySelectorAll('#miDocChips .mi-doc-chip').forEach(ch => {
        ch.classList.toggle('active', ch.dataset.doc === clean);
    });
}

function toggleTaxInvoiceOption(checked) {
    const wrap = document.getElementById('wrap_mi_tax_rate');
    const box  = document.getElementById('mi_tax_box');
    const rateInput = document.getElementById('mi_tax_rate');
    const titleInput = document.getElementById('mi_title');

    if (checked) {
        wrap.style.display = 'block';
        box.classList.add('tax-active');
        if (!parseFloat(rateInput.value) || parseFloat(rateInput.value) <= 0) {
            rateInput.value = 14;
        }
        if (titleInput.value.trim() === 'فاتورة مبيعات') {
            titleInput.value = 'فاتورة ضريبية';
        }
    } else {
        wrap.style.display = 'none';
        box.classList.remove('tax-active');
        rateInput.value = 0;
        if (titleInput.value.trim() === 'فاتورة ضريبية') {
            titleInput.value = 'فاتورة مبيعات';
        }
    }
    syncDocTypeChips(titleInput.value);
    recalcManualInvoice();
}

function onClientNameInput(val) {
    const clean = (val || '').trim();
    const found = KNOWN_CLIENTS.find(c => (c.name || '').trim() === clean);
    const badge = document.getElementById('clientAutoMatchBadge');
    if (found) {
        const phoneEl = document.getElementById('mi_client_phone');
        const addrEl  = document.getElementById('mi_client_address');
        if (found.phone && !phoneEl.value) phoneEl.value = found.phone;
        if (found.address && !addrEl.value) addrEl.value = found.address;
        if (badge) badge.style.display = 'inline-flex';
    } else {
        if (badge) badge.style.display = 'none';
    }
}

function addInvoiceItemRow(data = {}, focusNew = false) {
    const tbody = document.getElementById('mi_items_tbody');
    const tr = document.createElement('tr');
    tr.className = 'mi-item-row mi-row-new-anim';

    const title = data.title || '';
    const qty   = data.qty !== undefined ? data.qty : 1;
    const unit  = data.unit || 'عدد';
    const price = data.price !== undefined ? data.price : '';

    tr.innerHTML = `
        <td class="text-center"><span class="mi-row-idx-badge row-idx">1</span></td>
        <td>
            <input type="text" class="form-control form-control-sm fw-bold item-title" placeholder="اكتب اسم الصنف أو البند..." value="${escapeAttr(title)}" required>
        </td>
        <td>
            <input type="number" step="any" min="0.01" class="form-control form-control-sm text-center fw-bold item-qty" value="${qty}" oninput="recalcManualInvoice()" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm text-center item-unit" list="unitsDatalist" value="${escapeAttr(unit)}" placeholder="عدد / متر">
        </td>
        <td>
            <input type="number" step="any" min="0" class="form-control form-control-sm text-center fw-bold item-price" placeholder="0.00" value="${price}" oninput="recalcManualInvoice()" required>
        </td>
        <td class="text-center">
            <span class="mi-line-total-pill item-line-total">0</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn-mi-del-row" onclick="removeInvoiceItemRow(this)" title="حذف السطر">
                <i class="fa fa-xmark"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    renumberItemRows();
    recalcManualInvoice();

    if (focusNew) {
        const titleInput = tr.querySelector('.item-title');
        if (titleInput) titleInput.focus();
    }
}

function removeInvoiceItemRow(btn) {
    const tbody = document.getElementById('mi_items_tbody');
    if (tbody.querySelectorAll('.mi-item-row').length <= 1) {
        const row = btn.closest('tr');
        row.querySelector('.item-title').value = '';
        row.querySelector('.item-qty').value = 1;
        row.querySelector('.item-unit').value = 'عدد';
        row.querySelector('.item-price').value = '';
        recalcManualInvoice();
        return;
    }
    btn.closest('tr').remove();
    renumberItemRows();
    recalcManualInvoice();
}

function renumberItemRows() {
    document.querySelectorAll('#mi_items_tbody .mi-item-row').forEach((tr, idx) => {
        tr.querySelector('.row-idx').textContent = idx + 1;
    });
}

function recalcManualInvoice() {
    let subtotal = 0;
    let totalQty = 0;
    const rows = document.querySelectorAll('#mi_items_tbody .mi-item-row');
    rows.forEach(tr => {
        const qty   = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(tr.querySelector('.item-price')?.value) || 0;
        const line  = Math.round(qty * price * 100) / 100;
        subtotal += line;
        totalQty += qty;

        const lineEl = tr.querySelector('.item-line-total');
        if (lineEl) {
            lineEl.textContent = fmtMoney(line);
            lineEl.classList.toggle('has-val', line > 0);
        }
    });

    const itemsBadge = document.querySelector('#mi_live_items_badge span');
    const qtyBadge   = document.querySelector('#mi_live_qty_badge span');
    if (itemsBadge) itemsBadge.textContent = `${rows.length} ${rows.length === 1 ? 'بند' : 'بنود'}`;
    if (qtyBadge)   qtyBadge.textContent   = `إجمالي الكميات: ${fmtMoney(totalQty)}`;

    const discount = Math.max(0, parseFloat(document.getElementById('mi_discount')?.value) || 0);
    const afterDiscount = Math.max(0, subtotal - discount);

    const isTax = document.getElementById('mi_is_tax')?.checked;
    const taxRate = isTax ? Math.max(0, parseFloat(document.getElementById('mi_tax_rate')?.value) || 0) : 0;
    const taxAmount = Math.round(afterDiscount * (taxRate / 100) * 100) / 100;
    const grand = Math.max(0, Math.round((afterDiscount + taxAmount) * 100) / 100);

    document.getElementById('mi_sum_subtotal').textContent = fmtMoney(subtotal) + ' ج.م';
    document.getElementById('mi_sum_tax').textContent      = fmtMoney(taxAmount) + ' ج.م';
    document.getElementById('mi_sum_grand').textContent    = fmtMoney(grand) + ' ج.م';

    const paidInput = document.getElementById('mi_paid').value;
    const remRow = document.getElementById('mi_remaining_row');
    if (paidInput !== '' && paidInput !== null) {
        const paid = Math.max(0, parseFloat(paidInput) || 0);
        const rem  = Math.max(0, Math.round((grand - paid) * 100) / 100);
        remRow.style.setProperty('display', 'flex', 'important');
        document.getElementById('mi_sum_remaining').textContent = fmtMoney(rem) + ' ج.م';
    } else {
        remRow.style.setProperty('display', 'none', 'important');
    }
}

function appendTermPreset(text) {
    const el = document.getElementById('mi_terms');
    const cur = el.value.trim();
    el.value = cur ? (cur + '\n' + text) : text;
}

function resetInvoiceForm() {
    document.getElementById('mi_id').value = '';
    document.getElementById('mi_title').value = 'فاتورة مبيعات';
    syncDocTypeChips('فاتورة مبيعات');
    document.getElementById('mi_client_name').value = '';
    document.getElementById('mi_client_phone').value = '';
    document.getElementById('mi_client_address').value = '';
    const matchBadge = document.getElementById('clientAutoMatchBadge');
    if (matchBadge) matchBadge.style.display = 'none';
    document.getElementById('mi_date').value = '{{ date('Y-m-d') }}';
    document.getElementById('mi_discount').value = 0;
    document.getElementById('mi_is_tax').checked = false;
    document.getElementById('mi_tax_rate').value = 0;
    toggleTaxInvoiceOption(false);
    document.getElementById('mi_paid').value = '';
    document.getElementById('mi_notes').value = '';
    document.getElementById('mi_terms').value = '';

    const editBanner = document.getElementById('editModeBanner');
    editBanner.classList.add('d-none');
    editBanner.classList.remove('d-flex');
    document.getElementById('tabCreateTitle').textContent = 'إنشاء فاتورة جديدة';
    document.getElementById('btn-tab-create').classList.remove('is-editing');
    document.getElementById('mi_submit_btn').classList.remove('is-edit-submit');
    document.getElementById('formCardTitle').innerHTML = '<span class="mi-title-icon blue"><i class="fa-solid fa-file-circle-plus"></i></span><span>بيانات الفاتورة والعميل</span>';
    document.getElementById('mi_submit_lbl').textContent = 'حفظ الفاتورة وعرضها للطباعة / واتساب';

    const tbody = document.getElementById('mi_items_tbody');
    tbody.innerHTML = '';
    addInvoiceItemRow();
    switchMiTab('btn-tab-create');
}

async function submitManualInvoice(e) {
    e.preventDefault();

    const id = document.getElementById('mi_id').value;
    const items = [];
    document.querySelectorAll('#mi_items_tbody .mi-item-row').forEach(tr => {
        const title = tr.querySelector('.item-title').value.trim();
        const qty   = parseFloat(tr.querySelector('.item-qty').value) || 0;
        const unit  = tr.querySelector('.item-unit').value.trim() || 'عدد';
        const price = parseFloat(tr.querySelector('.item-price').value) || 0;
        if (title !== '') {
            items.push({ title, qty, unit, price });
        }
    });

    if (items.length === 0) {
        Swal.fire('تنبيه', 'يرجى إدخال صنف أو بند واحد على الأقل.', 'warning');
        return;
    }

    const isTax = document.getElementById('mi_is_tax').checked;
    const taxRate = isTax ? (parseFloat(document.getElementById('mi_tax_rate').value) || 0) : 0;

    const payload = {
        invoice_title:   document.getElementById('mi_title').value.trim(),
        client_name:     document.getElementById('mi_client_name').value.trim(),
        client_phone:    document.getElementById('mi_client_phone').value.trim(),
        client_address:  document.getElementById('mi_client_address').value.trim(),
        invoice_date:    document.getElementById('mi_date').value,
        discount_amount: parseFloat(document.getElementById('mi_discount').value) || 0,
        tax_rate:        taxRate,
        paid_amount:     document.getElementById('mi_paid').value !== '' ? parseFloat(document.getElementById('mi_paid').value) : null,
        notes:           document.getElementById('mi_notes').value.trim(),
        terms:           document.getElementById('mi_terms').value.trim(),
        items:           items,
    };

    const url = id ? `/manual-invoices/${id}/update` : `/manual-invoices/store`;
    const btn = document.getElementById('mi_submit_btn');
    btn.disabled = true;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        btn.disabled = false;

        if (!res.ok || !data.success) {
            Swal.fire('خطأ', data.message || 'تعذر حفظ الفاتورة', 'error');
            return;
        }

        await Swal.fire({
            icon: 'success',
            title: 'تم الحفظ بنجاح',
            text: data.message,
            timer: 1300,
            showConfirmButton: false
        });

        renderInvoiceIntoPreview(data.invoice);
        const modalEl = document.getElementById('invoicePreviewModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalEl.addEventListener('hidden.bs.modal', () => location.reload(), { once: true });
        modal.show();
    } catch (err) {
        btn.disabled = false;
        Swal.fire('خطأ', err.message || 'فشل الاتصال بالسيرفر', 'error');
    }
}

async function openInvoicePreview(id, autoAction = null) {
    try {
        const res = await fetch(`/manual-invoices/${id}`);
        const data = await res.json();
        if (!data.success || !data.invoice) {
            Swal.fire('خطأ', data.message || 'تعذر تحميل الفاتورة', 'error');
            return;
        }
        renderInvoiceIntoPreview(data.invoice);
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('invoicePreviewModal'));
        modal.show();

        if (autoAction === 'image') {
            setTimeout(() => downloadCurrentInvoiceImage(), 350);
        } else if (autoAction === 'whatsapp') {
            setTimeout(() => sendCurrentInvoiceWhatsApp(), 350);
        }
    } catch (err) {
        Swal.fire('خطأ', 'تعذر جلب بيانات الفاتورة', 'error');
    }
}

function renderInvoiceIntoPreview(inv) {
    currentPreviewInvoice = inv;

    document.getElementById('pv_doc_title').textContent = inv.invoice_title || 'فاتورة مبيعات';
    document.getElementById('pv_doc_num').textContent   = inv.invoice_number;
    document.getElementById('pv_meta_num').textContent  = inv.invoice_number;
    document.getElementById('pv_client_name').textContent = inv.client_name;

    const kindTag = document.getElementById('pv_doc_kind_tag');
    if (kindTag) {
        const isTaxInv = parseFloat(inv.tax_rate) > 0;
        kindTag.innerHTML = isTaxInv
            ? '<i class="fa-solid fa-percent"></i><span>مستند ضريبي معتمد</span>'
            : '<i class="fa-solid fa-file-invoice"></i><span>مستند مالي رسمي</span>';
    }

    const phoneWrap = document.getElementById('pv_client_phone_wrap');
    if (inv.client_phone) {
        phoneWrap.style.display = 'block';
        document.getElementById('pv_client_phone').textContent = inv.client_phone;
    } else {
        phoneWrap.style.display = 'none';
    }

    const addrWrap = document.getElementById('pv_client_address_wrap');
    if (inv.client_address) {
        addrWrap.style.display = 'block';
        document.getElementById('pv_client_address').textContent = inv.client_address;
    } else {
        addrWrap.style.display = 'none';
    }

    document.getElementById('pv_date').textContent = inv.invoice_date;
    document.getElementById('pv_items_count').textContent = (inv.items || []).length + ' صنف / بند';
    document.getElementById('pv_tafqeet').textContent = inv.tafqeet || '';

    // حالة الفاتورة (محذوفة / معدلة / سارية)
    const wm = document.getElementById('pv_deleted_watermark');
    const stateBadge = document.getElementById('pv_doc_state_badge');
    const topBadges  = document.getElementById('pv_status_badges');
    const auditAlert = document.getElementById('pv_audit_alert');

    let badgesHtml = '';
    let auditHtml  = '';

    if (Number(inv.is_deleted) === 1) {
        wm.style.display = 'block';
        badgesHtml += `<span class="mi-badge-deleted me-1"><i class="fa fa-ban"></i> فاتورة محذوفة</span>`;
        auditHtml += `<div><i class="fa fa-trash-can text-danger me-1"></i> <b>هذه الفاتورة محذوفة:</b> تم حذفها بتاريخ (${inv.deleted_at || '-'}) بواسطة (${inv.deleted_by || 'الإدارة'}) ${inv.delete_reason ? '— السبب: ' + escapeHtml(inv.delete_reason) : ''}.</div>`;
    } else {
        wm.style.display = 'none';
    }

    if (Number(inv.is_edited) === 1) {
        badgesHtml += `<span class="mi-badge-edited me-1"><i class="fa fa-pen-to-square"></i> تم تعديلها (${inv.edit_count} مرة)</span>`;
        auditHtml += `<div class="mt-1"><i class="fa fa-clock-rotate-left text-warning me-1"></i> <b>تم تعديل هذه الفاتورة (${inv.edit_count} مرة):</b> آخر تعديل في (${inv.last_edited_at || '-'}) بواسطة (${inv.last_edited_by || 'الإدارة'}).</div>`;
    }

    if (Number(inv.is_deleted) === 0 && Number(inv.is_edited) === 0) {
        badgesHtml = `<span class="mi-badge-active"><i class="fa fa-circle-check"></i> فاتورة معتمدة</span>`;
    }

    stateBadge.innerHTML = badgesHtml;
    topBadges.innerHTML  = badgesHtml;

    if (auditHtml !== '') {
        auditAlert.innerHTML = auditHtml;
        auditAlert.style.display = 'block';
    } else {
        auditAlert.style.display = 'none';
    }

    // جدول الأصناف
    const tbody = document.getElementById('pv_items_tbody');
    tbody.innerHTML = '';
    (inv.items || []).forEach((it, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="inv-row-num-chip">${idx + 1}</span></td>
            <td class="text-right">${escapeHtml(it.title)}</td>
            <td>${fmtMoney(it.qty)}</td>
            <td>${escapeHtml(it.unit || 'عدد')}</td>
            <td>${fmtMoney(it.price)}</td>
            <td class="inv-cell-total">${fmtMoney(it.total)} ج.م</td>
        `;
        tbody.appendChild(tr);
    });

    // الإجماليات
    document.getElementById('pv_subtotal').textContent = fmtMoney(inv.subtotal) + ' ج.م';
    const discRow = document.getElementById('pv_discount_row');
    if (parseFloat(inv.discount_amount) > 0) {
        discRow.style.display = 'flex';
        document.getElementById('pv_discount').textContent = '-' + fmtMoney(inv.discount_amount) + ' ج.م';
    } else {
        discRow.style.display = 'none';
    }

    const taxRow = document.getElementById('pv_tax_row');
    if (parseFloat(inv.tax_amount) > 0 || parseFloat(inv.tax_rate) > 0) {
        taxRow.style.display = 'flex';
        document.getElementById('pv_tax_label').textContent = `ضريبة القيمة المضافة (${fmtMoney(inv.tax_rate)}%):`;
        document.getElementById('pv_tax_amount').textContent = fmtMoney(inv.tax_amount) + ' ج.م';
    } else {
        taxRow.style.display = 'none';
    }

    document.getElementById('pv_grand_total').textContent = fmtMoney(inv.grand_total) + ' ج.م';

    const paidRow = document.getElementById('pv_paid_row');
    const remRow  = document.getElementById('pv_rem_row');
    if (inv.paid_amount !== null && inv.paid_amount !== undefined && inv.paid_amount !== '') {
        paidRow.style.display = 'flex';
        remRow.style.display  = 'flex';
        document.getElementById('pv_paid').textContent      = fmtMoney(inv.paid_amount) + ' ج.م';
        document.getElementById('pv_remaining').textContent = fmtMoney(inv.remaining_amount) + ' ج.م';
    } else {
        paidRow.style.display = 'none';
        remRow.style.display  = 'none';
    }

    // الملاحظات والشروط
    const notesBox = document.getElementById('pv_notes_box');
    if (inv.notes && inv.notes.trim() !== '') {
        notesBox.style.display = 'block';
        document.getElementById('pv_notes_text').textContent = inv.notes;
    } else {
        notesBox.style.display = 'none';
    }

    const termsBox  = document.getElementById('pv_terms_box');
    const termsList = document.getElementById('pv_terms_list');
    termsList.innerHTML = '';
    if (inv.terms && inv.terms.trim() !== '') {
        const lines = inv.terms.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
        lines.forEach(line => {
            const li = document.createElement('li');
            li.textContent = line;
            termsList.appendChild(li);
        });
        termsBox.style.display = lines.length ? 'block' : 'none';
    } else {
        termsBox.style.display = 'none';
    }

    // تحديث حاوية الطباعة المستقلة لضمان طباعة ممتازة
    syncPrintClone();
}

function printCurrentInvoice() {
    syncPrintClone();
    window.print();
}

// ── تحميل فاتورة للتعديل ──
async function loadInvoiceForEdit(id) {
    try {
        const res = await fetch(`/manual-invoices/${id}`);
        const data = await res.json();
        if (!data.success || !data.invoice) {
            Swal.fire('خطأ', 'تعذر تحميل بيانات الفاتورة للتعديل', 'error');
            return;
        }
        const inv = data.invoice;

        document.getElementById('mi_id').value             = inv.id;
        document.getElementById('mi_title').value          = inv.invoice_title || 'فاتورة مبيعات';
        syncDocTypeChips(document.getElementById('mi_title').value);
        document.getElementById('mi_client_name').value    = inv.client_name || '';
        document.getElementById('mi_client_phone').value   = inv.client_phone || '';
        document.getElementById('mi_client_address').value = inv.client_address || '';
        document.getElementById('mi_date').value           = inv.invoice_date || '{{ date('Y-m-d') }}';
        document.getElementById('mi_discount').value       = parseFloat(inv.discount_amount) || 0;

        const hasTax = parseFloat(inv.tax_rate) > 0;
        document.getElementById('mi_is_tax').checked = hasTax;
        document.getElementById('mi_tax_rate').value = hasTax ? parseFloat(inv.tax_rate) : 0;
        toggleTaxInvoiceOption(hasTax);

        document.getElementById('mi_paid').value           = (inv.paid_amount !== null && inv.paid_amount !== undefined) ? parseFloat(inv.paid_amount) : '';
        document.getElementById('mi_notes').value          = inv.notes || '';
        document.getElementById('mi_terms').value          = inv.terms || '';

        const tbody = document.getElementById('mi_items_tbody');
        tbody.innerHTML = '';
        if (Array.isArray(inv.items) && inv.items.length > 0) {
            inv.items.forEach(it => addInvoiceItemRow(it));
        } else {
            addInvoiceItemRow();
        }

        const editBanner = document.getElementById('editModeBanner');
        editBanner.classList.remove('d-none');
        editBanner.classList.add('d-flex');
        document.getElementById('editModeInvNum').textContent = inv.invoice_number;
        document.getElementById('tabCreateTitle').textContent = `تعديل (${inv.invoice_number})`;
        document.getElementById('btn-tab-create').classList.add('is-editing');
        document.getElementById('mi_submit_btn').classList.add('is-edit-submit');
        document.getElementById('formCardTitle').innerHTML = `<span class="mi-title-icon amber"><i class="fa-solid fa-pen-to-square"></i></span><span>تعديل الفاتورة رقم (${escapeHtml(inv.invoice_number)})</span>`;
        document.getElementById('mi_submit_lbl').textContent = 'حفظ التعديلات على الفاتورة';

        switchMiTab('btn-tab-create');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (err) {
        Swal.fire('خطأ', 'تعذر تحميل الفاتورة', 'error');
    }
}

// ── حذف فاتورة (حذف ناعم مع بقائها في السجل كفاتورة محذوفة) ──
async function deleteManualInvoice(id, invNum) {
    const { value: reason, isConfirmed } = await Swal.fire({
        icon: 'warning',
        title: `حذف الفاتورة (${invNum})`,
        html: 'ستبقى الفاتورة محفوظة في السجل ولكن سيظهر عليها بوضوح أنها <b>فاتورة محذوفة</b>.',
        input: 'text',
        inputPlaceholder: 'سبب الحذف (اختياري)...',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'تأكيد الحذف',
        cancelButtonText: 'تراجع'
    });

    if (!isConfirmed) return;

    try {
        const res = await fetch(`/manual-invoices/${id}/delete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason || '' })
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            Swal.fire('خطأ', data.message || 'تعذر حذف الفاتورة', 'error');
            return;
        }
        await Swal.fire({ icon: 'success', title: 'تم الحذف', text: data.message, timer: 1300, showConfirmButton: false });
        location.reload();
    } catch (err) {
        Swal.fire('خطأ', 'فشل الاتصال بالسيرفر', 'error');
    }
}

// ── التقاط الفاتورة كصورة عالية الجودة (html2canvas) ──
async function captureInvoiceCanvas() {
    if (document.fonts && document.fonts.ready) {
        try { await document.fonts.ready; } catch (e) {}
    }
    const node = document.getElementById('printableInvoiceArea');
    return html2canvas(node, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false,
        onclone: function(doc) {
            doc.documentElement.classList.remove('dark-mode');
            if (doc.body) doc.body.classList.remove('dark-mode');
            const s = doc.createElement('style');
            s.textContent = '*{letter-spacing:0 !important;word-spacing:normal !important;}';
            (doc.head || doc.documentElement).appendChild(s);
        }
    });
}

async function downloadCurrentInvoiceImage() {
    if (!currentPreviewInvoice) return;
    Swal.fire({ title: 'جاري تجهيز صورة الفاتورة...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const canvas = await captureInvoiceCanvas();
        const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
        Swal.close();

        const link = document.createElement('a');
        const safeClient = (currentPreviewInvoice.client_name || 'عميل').replace(/[\\/:*?"<>|]/g, '_');
        link.download = `فاتورة_${currentPreviewInvoice.invoice_number}_${safeClient}.jpg`;
        link.href = dataUrl;
        link.click();
    } catch (err) {
        Swal.close();
        Swal.fire('خطأ', 'تعذر حفظ الفاتورة كصورة', 'error');
    }
}

function normalizeWaPhone(phone) {
    let raw = String(phone || '').replace(/\D/g, '');
    if (!raw) return '';
    if (raw.startsWith('20')) return raw;
    if (raw.startsWith('0')) return '2' + raw;
    return '20' + raw;
}

function dataUrlToFile(dataUrl, filename) {
    const arr = dataUrl.split(',');
    const mime = (arr[0].match(/:(.*?);/) || [])[1] || 'image/png';
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8 = new Uint8Array(n);
    while (n--) u8[n] = bstr.charCodeAt(n);
    return new File([u8], filename, { type: mime });
}

async function sendCurrentInvoiceWhatsApp() {
    if (!currentPreviewInvoice) return;
    const inv = currentPreviewInvoice;
    let phone = normalizeWaPhone(inv.client_phone);

    if (!phone) {
        const { value: enteredPhone } = await Swal.fire({
            title: 'رقم واتساب العميل',
            text: 'أدخل رقم هاتف العميل لإرسال صورة الفاتورة إليه:',
            input: 'text',
            inputPlaceholder: '01xxxxxxxxx',
            showCancelButton: true,
            confirmButtonText: 'إرسال للواتساب',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#25d366'
        });
        if (!enteredPhone) return;
        phone = normalizeWaPhone(enteredPhone);
    }

    const greeting = `مرحباً أ/ ${inv.client_name}،\nمرفق لحضرتك صورة ${inv.invoice_title || 'الفاتورة'} رقم (${inv.invoice_number}) بإجمالي ${fmtMoney(inv.grand_total)} ج.م.`;
    const waWebUrl = `https://web.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(greeting)}`;

    Swal.fire({ title: 'جاري تجهيز صورة الفاتورة للواتساب...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const canvas = await captureInvoiceCanvas();
        const jpgUrl = canvas.toDataURL('image/jpeg', 0.95);
        const file   = dataUrlToFile(jpgUrl, `فاتورة_${inv.invoice_number}.jpg`);

        // 1) على الجوال: مشاركة ملف الصورة مباشرة لتطبيق واتساب
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            Swal.close();
            navigator.share({
                files: [file],
                title: `فاتورة ${inv.invoice_number}`,
                text: greeting
            }).catch(() => {});
            return;
        }

        // 2) على الكمبيوتر: نسخ الصورة للكليبورد + فتح واتساب ويب على محادثة العميل مباشرة
        canvas.toBlob(async function(pngBlob) {
            try {
                if (navigator.clipboard && window.ClipboardItem && pngBlob) {
                    await navigator.clipboard.write([new ClipboardItem({ 'image/png': pngBlob })]);
                    Swal.close();
                    window.open(waWebUrl, '_blank');
                    Swal.fire({
                        icon: 'success',
                        title: 'تم نسخ صورة الفاتورة 📋',
                        html: 'تم فتح محادثة العميل على واتساب.<br>اضغط <b>Ctrl + V</b> داخل المحادثة للصق صورة الفاتورة وإرسالها فوراً.',
                        confirmButtonText: 'تمام',
                        confirmButtonColor: '#25d366'
                    });
                    return;
                }
            } catch (clipErr) {
                // fallback below
            }

            Swal.close();
            const a = document.createElement('a');
            a.href = jpgUrl;
            a.download = `فاتورة_${inv.invoice_number}.jpg`;
            a.click();
            window.open(waWebUrl, '_blank');
            Swal.fire({
                icon: 'info',
                title: 'تم تحميل صورة الفاتورة وفتح واتساب',
                html: 'أرفق الصورة التي تم تنزيلها داخل محادثة العميل في واتساب.',
                confirmButtonText: 'تمام',
                confirmButtonColor: '#25d366'
            });
        }, 'image/png');
    } catch (err) {
        Swal.close();
        Swal.fire('خطأ', 'تعذر تجهيز صورة الفاتورة للواتساب', 'error');
    }
}

// ── فلترة وبحث سجل الفواتير ──
function setMiFilter(status, btn) {
    currentStatusFilter = status;
    document.querySelectorAll('#miStatusFilters .mi-filter-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterInvoicesLog();
}

function filterInvoicesLog() {
    const q = window.normalizeArabic ? window.normalizeArabic(document.getElementById('miSearchInput').value) : document.getElementById('miSearchInput').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#miLogTbody tr[data-id]');

    rows.forEach(tr => {
        const st  = tr.dataset.status || 'active';
        const raw = tr.dataset.search || '';
        const hay = window.normalizeArabic ? window.normalizeArabic(raw) : raw.toLowerCase();

        const statusMatch = (currentStatusFilter === 'all') || (st === currentStatusFilter);
        const searchMatch = !q || hay.includes(q);

        tr.style.display = (statusMatch && searchMatch) ? '' : 'none';
    });
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function escapeAttr(str) {
    return escapeHtml(str);
}
</script>
</body>
</html>
