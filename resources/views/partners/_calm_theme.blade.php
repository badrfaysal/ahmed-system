{{-- ═══════════════════════════════════════════════════════════
     التصميم الهادئ المتناسق لشاشات الشركاء وحساب الإدارة
     يُضمَّن بعد: @include('partials.theme', ['accent' => 'teal'])
     ═══════════════════════════════════════════════════════════ --}}
<style>
    :root {
        --p-radius-xl: 20px;
        --p-radius-lg: 16px;
        --p-radius-md: 12px;
        --p-shadow-card: 0 4px 20px rgba(13, 31, 53, 0.048);
        --p-shadow-hover: 0 10px 28px rgba(13, 31, 53, 0.085);
        --p-head-tint: #fcf8ee;
        --p-head-tint-hover: #f7f0dd;
        --p-hairline: #e8eff5;
        --p-thead-bg: #eef4f8;
        --p-thead-text: #35506b;
        --p-panel-head-bg: linear-gradient(135deg, #f3f9f9 0%, #f5f8fc 55%, #ffffff 100%);
    }

    body {
        background:
            radial-gradient(circle at 90% 6%, rgba(13, 148, 136, 0.13) 0%, transparent 40%),
            radial-gradient(circle at 10% 14%, rgba(37, 99, 235, 0.10) 0%, transparent 38%),
            radial-gradient(circle at 84% 88%, rgba(217, 119, 6, 0.09) 0%, transparent 42%),
            radial-gradient(circle at 16% 84%, rgba(16, 185, 129, 0.09) 0%, transparent 38%),
            linear-gradient(160deg, #e7f3f3 0%, #ebf2f9 50%, #f3efe6 100%) !important;
        background-attachment: fixed !important;
        color: var(--c-text);
        overflow-x: hidden;
    }

    body.theme-exited {
        background:
            radial-gradient(circle at 88% 8%, rgba(185, 28, 28, 0.10) 0%, transparent 40%),
            radial-gradient(circle at 12% 18%, rgba(182, 124, 31, 0.08) 0%, transparent 38%),
            linear-gradient(160deg, #f9efef 0%, #f7f2f2 55%, #f5f0ea 100%) !important;
        background-attachment: fixed !important;
    }

    /* ── ترويسة الصفحة الهادئة ── */
    .prt-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
    }
    .prt-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 13px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(13, 31, 53, 0.10);
        color: var(--c-text-muted);
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 10px;
        transition: all 0.16s ease;
    }
    .prt-back-link:hover {
        background: #ffffff;
        color: var(--c-accent);
        border-color: rgba(13, 115, 115, 0.28);
        transform: translateX(2px);
    }
    .prt-title-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .prt-title-badge {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        background: linear-gradient(135deg, var(--c-accent), var(--c-accent-2));
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        box-shadow: 0 5px 14px rgba(13, 115, 115, 0.20);
        flex-shrink: 0;
    }
    .prt-title {
        margin: 0;
        font-size: 1.48rem;
        font-weight: 700;
        color: var(--c-navy);
        letter-spacing: -0.015em;
        line-height: 1.3;
    }
    .prt-subtitle {
        margin: 6px 0 0;
        font-size: 0.84rem;
        color: var(--c-text-muted);
        max-width: 620px;
        line-height: 1.65;
        font-weight: 400;
    }

    /* ── أزرار كبسولية هادئة بتصنيف لوني واضح ── */
    .prt-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .prt-pill-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 17px;
        border-radius: 999px;
        font-size: 0.83rem;
        font-weight: 600;
        border: 1px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.18s ease;
        line-height: 1.35;
        white-space: nowrap;
    }
    .prt-pill-btn:hover {
        transform: translateY(-1px);
    }
    .prt-pill-btn.is-primary {
        background: linear-gradient(135deg, var(--c-accent), var(--c-accent-2));
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(13, 115, 115, 0.22);
    }
    .prt-pill-btn.is-primary:hover {
        filter: brightness(1.04);
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(13, 115, 115, 0.28);
    }
    .prt-pill-btn.is-soft {
        background: var(--c-accent-bg);
        color: var(--c-accent);
        border-color: rgba(13, 115, 115, 0.14);
    }
    .prt-pill-btn.is-soft:hover {
        filter: brightness(0.95);
        color: var(--c-accent);
    }
    .prt-pill-btn.is-emerald {
        background: #dff4e8;
        color: #1f6f47;
        border-color: rgba(45, 134, 89, 0.20);
    }
    .prt-pill-btn.is-emerald:hover {
        background: #d0ecd9;
        color: #165636;
    }
    .prt-pill-btn.is-whatsapp {
        background: linear-gradient(135deg, #218852, #2d9c62);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(33, 136, 82, 0.22);
    }
    .prt-pill-btn.is-whatsapp:hover {
        filter: brightness(1.05);
        color: #ffffff;
    }
    .prt-pill-btn.is-blue {
        background: #e1eefb;
        color: #1a5494;
        border-color: rgba(30, 95, 164, 0.18);
    }
    .prt-pill-btn.is-blue:hover {
        background: #d2e4f8;
        color: #15447a;
    }
    .prt-pill-btn.is-amber {
        background: #faefd5;
        color: #9a6512;
        border-color: rgba(182, 124, 31, 0.22);
    }
    .prt-pill-btn.is-amber:hover {
        background: #f5e4be;
        color: #7d510c;
    }
    .prt-pill-btn.is-rose {
        background: #fbe6e6;
        color: #a82020;
        border-color: rgba(185, 28, 28, 0.18);
    }
    .prt-pill-btn.is-rose:hover {
        background: #f7d6d6;
        color: #8b1818;
    }
    .prt-pill-btn.is-navy {
        background: #e5ecf4;
        color: var(--c-navy);
        border-color: rgba(13, 31, 53, 0.14);
    }
    .prt-pill-btn.is-navy:hover {
        background: #d7e2ef;
        color: var(--c-navy);
    }
    .prt-pill-btn.is-ghost {
        background: rgba(255, 255, 255, 0.90);
        color: var(--c-text);
        border-color: rgba(13, 31, 53, 0.10);
        box-shadow: var(--shadow-xs);
    }
    .prt-pill-btn.is-ghost:hover {
        background: #ffffff;
        border-color: rgba(13, 31, 53, 0.20);
        color: var(--c-navy);
    }
    .prt-pill-btn.is-sm {
        padding: 6px 14px;
        font-size: 0.78rem;
    }
    .prt-pill-btn.is-active-tab {
        background: var(--c-navy);
        color: #ffffff;
        border-color: var(--c-navy);
    }

    /* ── كروت المؤشرات الملوّنة بهدوء ── */
    .prt-kpi-hero {
        background: linear-gradient(145deg, #e1f2f2 0%, #ebf3fb 52%, #ffffff 100%);
        border: 1px solid rgba(13, 115, 115, 0.20);
        border-radius: var(--p-radius-xl);
        padding: 26px 24px;
        box-shadow: var(--p-shadow-card);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.22s ease, transform 0.22s ease;
    }
    .prt-kpi-hero::after {
        content: '';
        position: absolute;
        top: -36px;
        left: -36px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(13, 115, 115, 0.12), transparent 70%);
        pointer-events: none;
    }
    .prt-kpi-hero:hover {
        box-shadow: var(--p-shadow-hover);
    }
    .prt-kpi-hero .kpi-lbl {
        font-size: 0.83rem;
        color: #3d5a6c;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .prt-kpi-hero .kpi-val {
        font-size: 2.15rem;
        font-weight: 700;
        color: var(--c-navy);
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
    }
    .prt-kpi-hero .kpi-cur {
        font-size: 1rem;
        font-weight: 500;
        color: var(--c-text-muted);
        margin-right: 4px;
    }
    .prt-kpi-hero .kpi-sub {
        font-size: 0.82rem;
        color: var(--c-text-muted);
        margin-top: 10px;
    }

    .prt-kpi-card {
        background: #ffffff;
        border: 1px solid rgba(13, 31, 53, 0.08);
        border-radius: var(--p-radius-lg);
        padding: 19px 20px;
        box-shadow: var(--p-shadow-card);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .prt-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--p-shadow-hover);
    }
    .prt-kpi-card.is-teal {
        background: linear-gradient(155deg, #ecf8f7 0%, #ffffff 72%);
        border-color: rgba(13, 115, 115, 0.20);
    }
    .prt-kpi-card.is-blue {
        background: linear-gradient(155deg, #edf4fc 0%, #ffffff 72%);
        border-color: rgba(30, 95, 164, 0.20);
    }
    .prt-kpi-card.is-emerald {
        background: linear-gradient(155deg, #ecf8f1 0%, #ffffff 72%);
        border-color: rgba(45, 134, 89, 0.20);
    }
    .prt-kpi-card.is-amber {
        background: linear-gradient(155deg, #fcf6e6 0%, #ffffff 72%);
        border-color: rgba(182, 124, 31, 0.22);
    }
    .prt-kpi-card.is-rose {
        background: linear-gradient(155deg, #fcf0f0 0%, #ffffff 72%);
        border-color: rgba(185, 28, 28, 0.20);
    }
    .prt-kpi-card.is-indigo {
        background: linear-gradient(155deg, #eff2fd 0%, #ffffff 72%);
        border-color: rgba(58, 79, 184, 0.20);
    }

    .prt-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }
    .prt-kpi-card .kpi-lbl {
        font-size: 0.79rem;
        color: var(--c-text-muted);
        font-weight: 600;
        margin: 0;
    }
    .prt-kpi-ico {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .prt-kpi-card.is-teal    .prt-kpi-ico { background: #d6f0ee; color: #0d7373; }
    .prt-kpi-card.is-blue    .prt-kpi-ico { background: #d8e8fa; color: #1e5fa4; }
    .prt-kpi-card.is-emerald .prt-kpi-ico { background: #d5f0e1; color: #257a4e; }
    .prt-kpi-card.is-amber   .prt-kpi-ico { background: #f8e8c0; color: #a86f13; }
    .prt-kpi-card.is-rose    .prt-kpi-ico { background: #f8dcdc; color: #b91c1c; }
    .prt-kpi-card.is-indigo  .prt-kpi-ico { background: #dde2fb; color: #3a4fb8; }

    .prt-kpi-card .kpi-val {
        font-size: 1.38rem;
        font-weight: 700;
        color: var(--c-navy);
        line-height: 1.25;
        font-variant-numeric: tabular-nums;
    }
    .prt-kpi-card .kpi-val.is-success { color: var(--c-success); }
    .prt-kpi-card .kpi-val.is-danger  { color: var(--c-danger); }
    .prt-kpi-card .kpi-val.is-accent  { color: var(--c-accent); }
    .prt-kpi-card .kpi-val.is-info    { color: var(--c-info); }
    .prt-kpi-card .kpi-cur {
        font-size: 0.82rem;
        font-weight: 500;
        color: var(--c-text-soft);
        margin-right: 3px;
    }
    .prt-kpi-card .kpi-sub {
        font-size: 0.78rem;
        color: var(--c-text-muted);
        margin-top: 6px;
        line-height: 1.45;
        font-variant-numeric: tabular-nums;
    }

    .prt-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
        padding: 5px 14px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }
    .prt-status-pill.is-pos {
        background: #daf2e5;
        color: #1f6f47;
    }
    .prt-status-pill.is-neg {
        background: #fae2e2;
        color: #a82020;
    }

    .prt-bar-track {
        height: 5px;
        width: 100%;
        background: rgba(13, 31, 53, 0.08);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 13px;
    }
    .prt-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--c-accent), var(--c-accent-2));
        transition: width 0.35s ease;
    }
    .prt-bar-fill.is-amber {
        background: linear-gradient(90deg, #c78b24, #dfa642);
    }

    /* ── شريط التبويبات الكبسولي ── */
    .prt-tabs-row {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-bottom: 18px;
    }
    .prt-tabs-capsule {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        background: rgba(255, 255, 255, 0.90);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(13, 31, 53, 0.08);
        border-radius: 999px;
        padding: 5px;
        box-shadow: var(--p-shadow-card);
    }
    .prt-tabs-capsule .nav-link {
        border: none;
        background: transparent;
        color: var(--c-text-muted);
        font-size: 0.84rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.18s ease;
        cursor: pointer;
    }
    .prt-tabs-capsule .nav-link:hover {
        color: var(--c-navy);
        background: #eff5f9;
    }
    .prt-tabs-capsule .nav-link.active {
        background: linear-gradient(135deg, var(--c-accent), var(--c-accent-2));
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(13, 115, 115, 0.22);
    }
    .prt-tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 20px;
        padding: 0 7px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        background: #edf3f8;
        color: var(--c-text-muted);
        font-variant-numeric: tabular-nums;
    }
    .prt-tabs-capsule .nav-link.active .prt-tab-count {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }
    .prt-tab-sim-tag {
        padding: 2px 9px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        background: var(--c-warning-bg);
        color: var(--c-warning);
    }
    .prt-tabs-capsule .nav-link.active .prt-tab-sim-tag {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }

    /* ── اللوحات والبطاقات الرئيسية ── */
    .prt-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(13, 31, 53, 0.08);
        border-radius: var(--p-radius-xl);
        box-shadow: var(--p-shadow-card);
        overflow: hidden;
        margin-bottom: 22px;
    }
    .prt-panel-head {
        background: var(--p-panel-head-bg);
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        border-bottom: 1px solid var(--p-hairline);
    }
    .prt-panel-title {
        margin: 0;
        font-size: 1.03rem;
        font-weight: 700;
        color: var(--c-navy);
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .prt-panel-title-ico {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: var(--c-accent-bg);
        color: var(--c-accent);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
    }
    .prt-panel-sub {
        margin: 4px 0 0;
        font-size: 0.8rem;
        color: var(--c-text-muted);
    }
    .prt-panel-foot {
        padding: 13px 24px;
        background: #f3f7fa;
        border-top: 1px solid var(--p-hairline);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.8rem;
        color: var(--c-text-muted);
    }

    /* ── الجداول الهادئة ذات الترويسة الملونة برقة ── */
    .prt-table-wrap {
        width: 100%;
        overflow-x: auto;
    }
    .prt-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        vertical-align: middle;
        font-size: 0.86rem;
    }
    .prt-table thead th {
        background: var(--p-thead-bg);
        color: var(--p-thead-text);
        font-weight: 600;
        font-size: 0.77rem;
        padding: 12px 12px;
        border-bottom: 1.5px solid #d7e3ec;
        white-space: nowrap;
        vertical-align: middle;
    }
    .prt-table thead th.th-soft-before {
        background: #e3ecf4;
        color: #2f4860;
    }
    .prt-table thead th.th-soft-after {
        background: #d8efee;
        color: #0b5d5d;
    }
    .prt-table tbody td {
        padding: 13px 12px;
        border-bottom: 1px solid var(--p-hairline);
        color: var(--c-text);
        font-variant-numeric: tabular-nums;
        vertical-align: middle;
    }
    .prt-table tbody tr {
        transition: background-color 0.14s ease;
    }
    .prt-table tbody tr:hover {
        background-color: #f6fafc;
    }
    .prt-table tbody tr:last-child td {
        border-bottom: none;
    }
    .prt-table tbody tr.prt-row-head {
        background-color: var(--p-head-tint);
    }
    .prt-table tbody tr.prt-row-head:hover {
        background-color: var(--p-head-tint-hover);
    }
    .prt-table tbody tr.prt-row-exited td {
        background-color: #fdf4f4;
    }
    .prt-table tbody tr.prt-row-exited:hover td {
        background-color: #fbeaea;
    }
    .prt-table tbody tr.prt-row-exited td:first-child {
        box-shadow: inset -3px 0 0 #d98282;
    }
    .prt-table tfoot td,
    .prt-table tfoot th {
        background: #eef4f8;
        border-top: 1.5px solid #d5e2ec;
        padding: 12px;
        font-weight: 700;
        font-size: 0.83rem;
        color: var(--c-navy);
        font-variant-numeric: tabular-nums;
    }

    /* ── كبسولات الحالات والنسب ── */
    .prt-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 600;
        line-height: 1.45;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .prt-chip.is-info    { background: #e1eefb; color: #1a5494; }
    .prt-chip.is-success { background: #ddf3e7; color: #1f6f47; }
    .prt-chip.is-warning { background: #faefd5; color: #9a6512; }
    .prt-chip.is-danger  { background: #fbe4e4; color: #a82020; }
    .prt-chip.is-accent  { background: var(--c-accent-bg); color: var(--c-accent); }
    .prt-chip.is-neutral {
        background: #f1f5f8;
        color: var(--c-text-muted);
        border: 1px solid #dfe7ef;
        font-weight: 500;
    }
    .prt-chip.is-head {
        background: #f3e8d0;
        color: #7c5b1e;
        font-size: 0.69rem;
        padding: 2px 8px;
    }

    .prt-name-link {
        font-weight: 600;
        font-size: 0.91rem;
        color: var(--c-navy);
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .prt-name-link:hover { color: var(--c-accent); }
    .prt-meta-line {
        font-size: 0.75rem;
        color: var(--c-text-soft);
        margin-top: 3px;
        line-height: 1.45;
    }
    .prt-num-main {
        font-weight: 600;
        font-size: 0.91rem;
        color: var(--c-navy);
        white-space: nowrap;
    }
    .prt-num-sub {
        font-size: 0.72rem;
        color: var(--c-text-soft);
        margin-top: 2px;
    }

    /* ── بطاقات الرسوم البيانية ── */
    .prt-chart-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(13, 31, 53, 0.08);
        border-radius: var(--p-radius-lg);
        padding: 20px 22px;
        box-shadow: var(--p-shadow-card);
        height: 100%;
    }
    .prt-chart-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        padding-bottom: 12px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--p-hairline);
    }
    .prt-chart-head h6 {
        margin: 0;
        font-size: 0.93rem;
        font-weight: 700;
        color: var(--c-navy);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── المودالات الهادئة الموحدة ── */
    .prt-modal .modal-content {
        border: 1px solid var(--c-border);
        border-radius: var(--p-radius-xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }
    .prt-modal .modal-header {
        background: var(--p-panel-head-bg);
        border-bottom: 1px solid var(--p-hairline);
        padding: 18px 22px;
    }
    .prt-modal .modal-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--c-navy);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .prt-modal-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        background: var(--c-accent-bg);
        color: var(--c-accent);
    }
    .prt-modal-icon.is-danger  { background: #fbe4e4; color: #a82020; }
    .prt-modal-icon.is-warning { background: #faefd5; color: #9a6512; }
    .prt-modal-icon.is-success { background: #ddf3e7; color: #1f6f47; }
    .prt-modal .modal-body {
        padding: 22px;
        background: #ffffff;
    }
    .prt-modal .modal-footer {
        background: #f3f7fa;
        border-top: 1px solid var(--p-hairline);
        padding: 14px 22px;
        gap: 8px;
    }
    .prt-soft-box {
        background: #f4f8fb;
        border: 1px solid #e1eaf2;
        border-radius: var(--p-radius-md);
        padding: 13px 16px;
    }
    .prt-modal .input-group > .form-pro-control {
        width: 1%;
        flex: 1 1 auto;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .prt-modal .input-group-text {
        border-color: var(--c-border-2);
        color: var(--c-text-muted);
        font-size: 0.84rem;
        background: #f4f8fb;
        border-top-left-radius: var(--r-md);
        border-bottom-left-radius: var(--r-md);
    }

    /* ── الترقيم ── */
    .pagination {
        margin: 0;
        gap: 4px;
    }
    .pagination .page-link {
        border-radius: 8px !important;
        border: 1px solid #dce6ef;
        color: var(--c-text-muted);
        font-size: 0.8rem;
        font-weight: 500;
        padding: 5px 11px;
        background: #ffffff;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--c-accent);
        border-color: var(--c-accent);
        color: #ffffff;
    }
    .pagination .page-link:hover {
        background-color: var(--c-accent-bg);
        border-color: transparent;
        color: var(--c-accent);
    }

    @media (max-width: 767px) {
        .prt-tabs-capsule {
            border-radius: 16px;
            width: 100%;
        }
        .prt-panel-head {
            padding: 15px 16px;
        }
    }
</style>
