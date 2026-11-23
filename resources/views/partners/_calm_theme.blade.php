{{-- ═══════════════════════════════════════════════════════════
     التصميم الملوّن الحيوي المتناسق لشاشات الشركاء وحساب الإدارة
     يُضمَّن بعد: @include('partials.theme', ['accent' => 'teal'])
     ═══════════════════════════════════════════════════════════ --}}
<style>
    :root {
        --p-radius-xl: 22px;
        --p-radius-lg: 18px;
        --p-radius-md: 13px;
        --p-shadow-card: 0 10px 28px rgba(15, 23, 42, 0.07);
        --p-shadow-hover: 0 16px 36px rgba(13, 148, 136, 0.14);
        --p-head-tint: #fef6dc;
        --p-head-tint-hover: #fdebb8;
        --p-hairline: #dcebe9;
        --p-thead-bg: linear-gradient(180deg, #0f2942 0%, #0d5c63 100%);
        --p-thead-text: #ffffff;
        --p-panel-head-bg: linear-gradient(135deg, #d7f5f0 0%, #e0efff 55%, #ede9fe 100%);
    }

    body {
        background:
            radial-gradient(circle at 8% 6%, rgba(13, 148, 136, 0.24) 0%, transparent 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.24) 0%, transparent 38%),
            radial-gradient(circle at 80% 86%, rgba(139, 92, 246, 0.20) 0%, transparent 40%),
            radial-gradient(circle at 18% 88%, rgba(16, 185, 129, 0.20) 0%, transparent 38%),
            linear-gradient(155deg, #d8f5f0 0%, #dfeeff 52%, #ece6ff 100%) !important;
        background-attachment: fixed !important;
        color: var(--c-text);
        overflow-x: hidden;
    }

    body.theme-exited {
        background:
            radial-gradient(circle at 88% 8%, rgba(225, 29, 72, 0.18) 0%, transparent 40%),
            radial-gradient(circle at 12% 18%, rgba(245, 158, 11, 0.14) 0%, transparent 38%),
            linear-gradient(155deg, #ffe4e6 0%, #fef2f2 55%, #fff7ed 100%) !important;
        background-attachment: fixed !important;
    }

    /* ── بانر ترويسة الصفحة الملوّن الحيوي ── */
    .prt-header {
        background: linear-gradient(135deg, #064e3b 0%, #0d9488 42%, #0284c7 82%, #4f46e5 100%);
        color: #ffffff;
        border-radius: var(--p-radius-xl);
        padding: 22px 26px;
        margin-bottom: 22px;
        box-shadow: 0 14px 36px rgba(13, 148, 136, 0.26);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .prt-header::before {
        content: "";
        position: absolute;
        bottom: -55%;
        right: -4%;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.30), transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .prt-header::after {
        content: "";
        position: absolute;
        top: -50%;
        left: -6%;
        width: 310px;
        height: 310px;
        background: radial-gradient(circle, rgba(167, 243, 208, 0.30), transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .prt-header > * {
        position: relative;
        z-index: 2;
    }
    .prt-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.20);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255, 255, 255, 0.38);
        color: #ffffff;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        margin-bottom: 10px;
        transition: all 0.18s ease;
    }
    .prt-back-link:hover {
        background: rgba(255, 255, 255, 0.34);
        color: #ffffff;
        transform: translateX(2px);
    }
    .prt-title-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .prt-title-badge {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.34), rgba(255, 255, 255, 0.14));
        backdrop-filter: blur(8px);
        border: 1.5px solid rgba(255, 255, 255, 0.45);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
        flex-shrink: 0;
    }
    .prt-title {
        margin: 0;
        font-size: 1.48rem;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.015em;
        line-height: 1.3;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.14);
    }
    .prt-subtitle {
        margin: 6px 0 0;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.92);
        max-width: 660px;
        line-height: 1.65;
        font-weight: 500;
    }
    .prt-header .prt-chip {
        border: 1px solid rgba(255, 255, 255, 0.36);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
    }

    /* ── أزرار كبسولية ملوّنة بتصنيف لوني واضح ── */
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
        font-weight: 700;
        border: 1.5px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        line-height: 1.35;
        white-space: nowrap;
    }
    .prt-pill-btn:hover {
        transform: translateY(-2px);
    }
    .prt-pill-btn.is-primary {
        background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
        color: #ffffff;
        box-shadow: 0 5px 15px rgba(13, 148, 136, 0.28);
    }
    .prt-pill-btn.is-primary:hover {
        filter: brightness(1.06);
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(13, 148, 136, 0.36);
    }
    .prt-header .prt-pill-btn.is-primary {
        background: linear-gradient(135deg, #ffffff 0%, #ccfbf1 100%);
        color: #0f766e !important;
        border-color: rgba(255, 255, 255, 0.8);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
    }
    .prt-header .prt-pill-btn.is-primary:hover {
        background: #ffffff;
        color: #065f46 !important;
    }
    .prt-pill-btn.is-soft {
        background: #ccfbf1;
        color: #0f766e;
        border-color: #5eead4;
    }
    .prt-pill-btn.is-soft:hover {
        background: #99f6e4;
        color: #065f46;
    }
    .prt-pill-btn.is-emerald {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.28);
    }
    .prt-pill-btn.is-emerald:hover {
        filter: brightness(1.06);
        color: #ffffff;
    }
    .prt-pill-btn.is-whatsapp {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: #ffffff;
        box-shadow: 0 5px 14px rgba(22, 163, 74, 0.30);
    }
    .prt-pill-btn.is-whatsapp:hover {
        filter: brightness(1.06);
        color: #ffffff;
    }
    .prt-pill-btn.is-blue {
        background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.28);
    }
    .prt-pill-btn.is-blue:hover {
        filter: brightness(1.06);
        color: #ffffff;
    }
    .prt-pill-btn.is-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.30);
    }
    .prt-pill-btn.is-amber:hover {
        filter: brightness(1.06);
        color: #ffffff;
    }
    .prt-pill-btn.is-rose {
        background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(244, 63, 94, 0.28);
    }
    .prt-pill-btn.is-rose:hover {
        filter: brightness(1.06);
        color: #ffffff;
    }
    .prt-pill-btn.is-navy {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
    }
    .prt-pill-btn.is-navy:hover {
        filter: brightness(1.12);
        color: #ffffff;
    }
    .prt-pill-btn.is-ghost {
        background: rgba(255, 255, 255, 0.92);
        color: #0f172a;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    .prt-pill-btn.is-ghost:hover {
        background: #ffffff;
        border-color: #0d9488;
        color: #0f766e;
    }
    .prt-header .prt-pill-btn.is-ghost,
    .prt-header .prt-pill-btn.is-soft {
        background: rgba(255, 255, 255, 0.20);
        backdrop-filter: blur(6px);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.42);
        box-shadow: none;
    }
    .prt-header .prt-pill-btn.is-ghost:hover,
    .prt-header .prt-pill-btn.is-soft:hover {
        background: rgba(255, 255, 255, 0.34);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.7);
    }
    .prt-pill-btn.is-sm {
        padding: 7px 14px;
        font-size: 0.79rem;
    }
    .prt-pill-btn.is-active-tab {
        background: linear-gradient(135deg, #0f2942 0%, #0d7373 100%);
        color: #ffffff;
        border-color: transparent;
    }

    /* ── كروت المؤشرات الملوّنة والغنية ── */
    .prt-kpi-hero {
        background: linear-gradient(135deg, #065f46 0%, #0d9488 45%, #0284c7 85%, #4338ca 100%);
        border: 1.5px solid rgba(255, 255, 255, 0.35);
        border-radius: var(--p-radius-xl);
        padding: 26px 24px;
        box-shadow: 0 14px 34px rgba(13, 148, 136, 0.28);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        color: #ffffff;
        transition: box-shadow 0.22s ease, transform 0.22s ease;
    }
    .prt-kpi-hero::before {
        content: '';
        position: absolute;
        bottom: -45px;
        right: -45px;
        width: 170px;
        height: 170px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(167, 243, 208, 0.30), transparent 70%);
        pointer-events: none;
    }
    .prt-kpi-hero::after {
        content: '';
        position: absolute;
        top: -40px;
        left: -40px;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.32), transparent 70%);
        pointer-events: none;
    }
    .prt-kpi-hero > * {
        position: relative;
        z-index: 2;
    }
    .prt-kpi-hero:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 40px rgba(13, 148, 136, 0.34);
    }
    .prt-kpi-hero .kpi-lbl {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.90);
        font-weight: 700;
        margin-bottom: 10px;
    }
    .prt-kpi-hero .kpi-val {
        font-size: 2.25rem;
        font-weight: 900;
        color: #ffffff;
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.16);
    }
    .prt-kpi-hero .kpi-cur {
        font-size: 1rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.86);
        margin-right: 4px;
    }
    .prt-kpi-hero .kpi-sub {
        font-size: 0.83rem;
        color: rgba(255, 255, 255, 0.88);
        margin-top: 10px;
        font-weight: 500;
    }

    .prt-kpi-card {
        background: linear-gradient(145deg, #ffffff 18%, #eef7f7 100%);
        border: 1.5px solid #cbd5e1;
        border-radius: var(--p-radius-lg);
        padding: 18px 22px 18px 18px;
        box-shadow: var(--p-shadow-card);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        height: 100%;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }
    .prt-kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(180deg, #14b8a6, #0f766e);
    }
    .prt-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--p-shadow-hover);
    }
    .prt-kpi-card.is-teal {
        background: linear-gradient(145deg, #ffffff 12%, #b5f5ec 100%);
        border-color: #2dd4bf;
    }
    .prt-kpi-card.is-teal::before { background: linear-gradient(180deg, #14b8a6, #0f766e); }

    .prt-kpi-card.is-blue {
        background: linear-gradient(145deg, #ffffff 12%, #c7e2ff 100%);
        border-color: #60a5fa;
    }
    .prt-kpi-card.is-blue::before { background: linear-gradient(180deg, #0ea5e9, #1d4ed8); }

    .prt-kpi-card.is-emerald {
        background: linear-gradient(145deg, #ffffff 12%, #bbf7d0 100%);
        border-color: #34d399;
    }
    .prt-kpi-card.is-emerald::before { background: linear-gradient(180deg, #10b981, #047857); }

    .prt-kpi-card.is-amber {
        background: linear-gradient(145deg, #ffffff 12%, #fde68a 100%);
        border-color: #fbbf24;
    }
    .prt-kpi-card.is-amber::before { background: linear-gradient(180deg, #f59e0b, #b45309); }

    .prt-kpi-card.is-rose {
        background: linear-gradient(145deg, #ffffff 12%, #fecdd3 100%);
        border-color: #fb7185;
    }
    .prt-kpi-card.is-rose::before { background: linear-gradient(180deg, #f43f5e, #be123c); }

    .prt-kpi-card.is-indigo {
        background: linear-gradient(145deg, #ffffff 12%, #d4d8ff 100%);
        border-color: #818cf8;
    }
    .prt-kpi-card.is-indigo::before { background: linear-gradient(180deg, #6366f1, #3730a3); }

    .prt-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }
    .prt-kpi-card .kpi-lbl {
        font-size: 0.82rem;
        color: #1e293b;
        font-weight: 800;
        margin: 0;
    }
    .prt-kpi-ico {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #ffffff;
        flex-shrink: 0;
        transition: transform 0.22s ease;
    }
    .prt-kpi-card:hover .prt-kpi-ico {
        transform: scale(1.10) rotate(-5deg);
    }
    .prt-kpi-card.is-teal    .prt-kpi-ico { background: linear-gradient(135deg, #14b8a6, #0f766e); box-shadow: 0 5px 14px rgba(13, 148, 136, 0.34); }
    .prt-kpi-card.is-blue    .prt-kpi-ico { background: linear-gradient(135deg, #0ea5e9, #1d4ed8); box-shadow: 0 5px 14px rgba(14, 165, 233, 0.34); }
    .prt-kpi-card.is-emerald .prt-kpi-ico { background: linear-gradient(135deg, #10b981, #047857); box-shadow: 0 5px 14px rgba(16, 185, 129, 0.34); }
    .prt-kpi-card.is-amber   .prt-kpi-ico { background: linear-gradient(135deg, #f59e0b, #b45309); box-shadow: 0 5px 14px rgba(245, 158, 11, 0.34); }
    .prt-kpi-card.is-rose    .prt-kpi-ico { background: linear-gradient(135deg, #f43f5e, #be123c); box-shadow: 0 5px 14px rgba(244, 63, 94, 0.34); }
    .prt-kpi-card.is-indigo  .prt-kpi-ico { background: linear-gradient(135deg, #6366f1, #3730a3); box-shadow: 0 5px 14px rgba(99, 102, 241, 0.34); }

    .prt-kpi-card .kpi-val {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
    }
    .prt-kpi-card .kpi-val.is-success { color: #047857; }
    .prt-kpi-card .kpi-val.is-danger  { color: #be123c; }
    .prt-kpi-card .kpi-val.is-accent  { color: #0f766e; }
    .prt-kpi-card .kpi-val.is-info    { color: #0369a1; }
    .prt-kpi-card .kpi-cur {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        margin-right: 4px;
    }
    .prt-kpi-card .kpi-sub {
        font-size: 0.79rem;
        color: #334155;
        font-weight: 600;
        margin-top: 7px;
        line-height: 1.5;
        font-variant-numeric: tabular-nums;
    }

    .prt-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .prt-status-pill.is-pos {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }
    .prt-status-pill.is-neg {
        background: #ffe4e6;
        color: #9f1239;
        border: 1px solid #fda4af;
    }
    .prt-kpi-hero .prt-status-pill.is-pos {
        background: rgba(209, 250, 229, 0.95);
        color: #065f46;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.14);
    }
    .prt-kpi-hero .prt-status-pill.is-neg {
        background: rgba(255, 228, 230, 0.96);
        color: #9f1239;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.14);
    }

    .prt-bar-track {
        height: 6px;
        width: 100%;
        background: rgba(15, 23, 42, 0.10);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 12px;
    }
    .prt-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #0d9488, #0284c7);
        transition: width 0.35s ease;
    }
    .prt-bar-fill.is-amber {
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    /* ── شريط التبويبات الكبسولي الملوّن ── */
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
        gap: 6px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.94) 0%, rgba(224, 247, 244, 0.94) 100%);
        backdrop-filter: blur(8px);
        border: 1.5px solid #5eead4;
        border-radius: 999px;
        padding: 6px;
        box-shadow: 0 8px 22px rgba(13, 148, 136, 0.12);
    }
    .prt-tabs-capsule .nav-link {
        border: none;
        background: transparent;
        color: #334155;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 9px 17px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .prt-tabs-capsule .nav-link:hover {
        color: #0f766e;
        background: rgba(204, 251, 241, 0.65);
    }
    .prt-tabs-capsule .nav-link.active {
        background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
        color: #ffffff;
        box-shadow: 0 5px 15px rgba(13, 148, 136, 0.32);
    }
    .prt-tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 0.73rem;
        font-weight: 800;
        background: #d5f3ef;
        color: #0f766e;
        font-variant-numeric: tabular-nums;
    }
    .prt-tabs-capsule .nav-link.active .prt-tab-count {
        background: rgba(255, 255, 255, 0.26);
        color: #ffffff;
    }
    .prt-tab-sim-tag {
        padding: 2px 9px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fcd34d;
    }
    .prt-tabs-capsule .nav-link.active .prt-tab-sim-tag {
        background: rgba(255, 255, 255, 0.26);
        color: #ffffff;
        border-color: transparent;
    }

    /* ── اللوحات والبطاقات الرئيسية ── */
    .prt-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1.5px solid #99f6e4;
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
        border-bottom: 1.5px solid #b5e6df;
    }
    .prt-panel-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f2942;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .prt-panel-title-ico {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem;
        box-shadow: 0 4px 10px rgba(13, 148, 136, 0.26);
    }
    .prt-panel-sub {
        margin: 4px 0 0;
        font-size: 0.81rem;
        color: #475569;
        font-weight: 500;
    }
    .prt-panel-foot {
        padding: 13px 24px;
        background: linear-gradient(135deg, #eefaf8 0%, #eff5ff 100%);
        border-top: 1.5px solid #cdeae5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.81rem;
        color: #334155;
        font-weight: 600;
    }

    /* ── الجداول ذات الترويسة الملوّنة الواضحة ── */
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
        font-weight: 700;
        font-size: 0.78rem;
        padding: 13px 12px;
        border-bottom: 2px solid #083344;
        white-space: nowrap;
        vertical-align: middle;
    }
    .prt-table thead th.th-soft-before {
        background: linear-gradient(180deg, #1e3a8a 0%, #2563eb 100%);
        color: #ffffff;
    }
    .prt-table thead th.th-soft-after {
        background: linear-gradient(180deg, #065f46 0%, #0d9488 100%);
        color: #ffffff;
    }
    .prt-table tbody td {
        padding: 13px 12px;
        border-bottom: 1px solid #e2efed;
        color: #1e293b;
        font-variant-numeric: tabular-nums;
        vertical-align: middle;
    }
    .prt-table tbody tr {
        background-color: #ffffff;
        transition: background-color 0.14s ease;
    }
    .prt-table tbody tr:nth-child(even) {
        background-color: #f3faf9;
    }
    .prt-table tbody tr:hover {
        background-color: #e0f5f2;
    }
    .prt-table tbody tr:last-child td {
        border-bottom: none;
    }
    .prt-table tbody tr.prt-row-head {
        background: linear-gradient(90deg, #fef9e7 0%, #fef0c7 100%);
    }
    .prt-table tbody tr.prt-row-head td:first-child {
        box-shadow: inset -4px 0 0 #d97706;
    }
    .prt-table tbody tr.prt-row-head:hover {
        background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
    }
    .prt-table tbody tr.prt-row-exited td {
        background-color: #fff1f2;
    }
    .prt-table tbody tr.prt-row-exited:hover td {
        background-color: #ffe4e6;
    }
    .prt-table tbody tr.prt-row-exited td:first-child {
        box-shadow: inset -4px 0 0 #e11d48;
    }
    .prt-table tfoot td,
    .prt-table tfoot th {
        background: linear-gradient(135deg, #d7f5f0 0%, #e0efff 100%);
        border-top: 2px solid #0d9488;
        padding: 13px 12px;
        font-weight: 800;
        font-size: 0.85rem;
        color: #0f2942;
        font-variant-numeric: tabular-nums;
    }

    /* ── كبسولات الحالات والنسب الملوّنة ── */
    .prt-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 3px 11px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        line-height: 1.45;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .prt-chip.is-info    { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
    .prt-chip.is-success { background: #d1fae5; color: #047857; border: 1px solid #6ee7b7; }
    .prt-chip.is-warning { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
    .prt-chip.is-danger  { background: #ffe4e6; color: #be123c; border: 1px solid #fda4af; }
    .prt-chip.is-accent  { background: #ccfbf1; color: #0f766e; border: 1px solid #5eead4; }
    .prt-chip.is-neutral {
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        font-weight: 600;
    }
    .prt-chip.is-head {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border: 1px solid #f59e0b;
        font-size: 0.7rem;
        padding: 2px 9px;
    }

    .prt-name-link {
        font-weight: 700;
        font-size: 0.92rem;
        color: #0f2942;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .prt-name-link:hover { color: #0d9488; }
    .prt-meta-line {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 3px;
        line-height: 1.45;
    }
    .prt-num-main {
        font-weight: 700;
        font-size: 0.92rem;
        color: #0f2942;
        white-space: nowrap;
    }
    .prt-num-sub {
        font-size: 0.73rem;
        color: #64748b;
        margin-top: 2px;
    }

    /* ── بطاقات الرسوم البيانية ── */
    .prt-chart-card {
        background: linear-gradient(155deg, #ffffff 30%, #f0fdfa 100%);
        border: 1.5px solid #99f6e4;
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
        border-bottom: 1.5px solid #d5f0ec;
    }
    .prt-chart-head h6 {
        margin: 0;
        font-size: 0.94rem;
        font-weight: 800;
        color: #0f2942;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── المودالات الملوّنة الموحدة ── */
    .prt-modal .modal-content {
        border: 1.5px solid #99f6e4;
        border-radius: var(--p-radius-xl);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }
    .prt-modal .modal-header {
        background: linear-gradient(135deg, #d7f5f0 0%, #e0efff 60%, #ede9fe 100%);
        border-bottom: 1.5px solid #b5e6df;
        padding: 18px 22px;
    }
    .prt-modal .modal-title {
        font-size: 1.02rem;
        font-weight: 800;
        color: #0f2942;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .prt-modal-icon {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.94rem;
        background: linear-gradient(135deg, #0d9488, #0284c7);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(13, 148, 136, 0.28);
    }
    .prt-modal-icon.is-danger  { background: linear-gradient(135deg, #f43f5e, #be123c); color: #ffffff; box-shadow: 0 4px 10px rgba(244, 63, 94, 0.28); }
    .prt-modal-icon.is-warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.28); }
    .prt-modal-icon.is-success { background: linear-gradient(135deg, #10b981, #047857); color: #ffffff; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.28); }
    .prt-modal .modal-body {
        padding: 22px;
        background: #ffffff;
    }
    .prt-modal .modal-footer {
        background: linear-gradient(135deg, #eefaf8 0%, #eff5ff 100%);
        border-top: 1.5px solid #cdeae5;
        padding: 14px 22px;
        gap: 8px;
    }
    .prt-soft-box {
        background: linear-gradient(135deg, #f0fdfa 0%, #eff6ff 100%);
        border: 1px solid #bae6fd;
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
        color: #0f766e;
        font-weight: 700;
        font-size: 0.84rem;
        background: #e6f7f5;
        border-top-left-radius: var(--r-md);
        border-bottom-left-radius: var(--r-md);
    }

    /* ── الترقيم ── */
    .pagination {
        margin: 0;
        gap: 4px;
    }
    .pagination .page-link {
        border-radius: 9px !important;
        border: 1px solid #99f6e4;
        color: #0f766e;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 5px 11px;
        background: #ffffff;
    }
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #0d9488, #0284c7);
        border-color: transparent;
        color: #ffffff;
    }
    .pagination .page-link:hover {
        background-color: #ccfbf1;
        border-color: #2dd4bf;
        color: #065f46;
    }

    @media (max-width: 767px) {
        .prt-header {
            padding: 18px 18px;
        }
        .prt-tabs-capsule {
            border-radius: 16px;
            width: 100%;
        }
        .prt-panel-head {
            padding: 15px 16px;
        }
    }

    @media print {
        .prt-kpi-card::before {
            display: none !important;
        }
        .prt-kpi-card, .prt-kpi-hero {
            background: #ffffff !important;
            color: #0d1f35 !important;
        }
    }
</style>
