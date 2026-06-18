{{-- ════════════════════════════════════════════════
     Theme موحد لشركة الضبع - Design System
     استخدامه: @include('partials.theme', ['accent' => 'gold'])
     الـ accents المتاحة: gold, navy, emerald, teal, indigo,
                           amber, rose, slate, purple, cyan
     ════════════════════════════════════════════════ --}}

@php
    // اللون موحّد لكل السيستم بناءً على إعداد theme_color — تجاوز أي accent اتبعت من الصفحة.
    try {
        $systemAccent = \App\Services\SystemSetting::get('theme_color');
    } catch (\Throwable $e) {
        $systemAccent = null;
    }
    $accent = $systemAccent ?: ($accent ?? 'navy');
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    /* ── ألوان أساسية موحدة ── */
    --c-bg:           #f5f7fa;
    --c-surface:      #ffffff;
    --c-surface-2:    #fafbfc;
    --c-border:       #e5e9f0;
    --c-border-2:     #d8dee9;

    --c-text:         #1a1f2e;
    --c-text-muted:   #5a6478;
    --c-text-soft:    #8b95a9;

    --c-navy:         #0d1f35;
    --c-navy-soft:    #1a3a5f;
    --c-navy-50:      #f0f4fa;

    /* ── ألوان الحالات ── */
    --c-success:      #2d8659;
    --c-success-bg:   #e8f5ed;
    --c-warning:      #b67c1f;
    --c-warning-bg:   #fbf3e2;
    --c-danger:       #b91c1c;
    --c-danger-bg:    #fceeee;
    --c-info:         #1e5fa4;
    --c-info-bg:      #e8f0fa;

    /* ── الـ accent بيتغير حسب الصفحة ── */
    @switch($accent)
        @case('gold')     --c-accent: #b8842d; --c-accent-2: #d4a23c; --c-accent-bg: #fbf3e2; @break
        @case('navy')     --c-accent: #1a3a5f; --c-accent-2: #2c5282; --c-accent-bg: #e8f0fa; @break
        @case('emerald')  --c-accent: #2d8659; --c-accent-2: #3c9c6b; --c-accent-bg: #e8f5ed; @break
        @case('teal')     --c-accent: #0d7373; --c-accent-2: #138a8a; --c-accent-bg: #e0f2f2; @break
        @case('indigo')   --c-accent: #3a4fb8; --c-accent-2: #4e63cc; --c-accent-bg: #ebeeff; @break
        @case('amber')    --c-accent: #b67c1f; --c-accent-2: #c8902c; --c-accent-bg: #fbf3e2; @break
        @case('rose')     --c-accent: #a13347; --c-accent-2: #b94155; --c-accent-bg: #fbeaed; @break
        @case('slate')    --c-accent: #475569; --c-accent-2: #64748b; --c-accent-bg: #f1f5f9; @break
        @case('purple')   --c-accent: #6b46a8; --c-accent-2: #7e58bb; --c-accent-bg: #f1e8fa; @break
        @case('cyan')     --c-accent: #0e7490; --c-accent-2: #1591ae; --c-accent-bg: #e0f2f7; @break
        @default          --c-accent: #1a3a5f; --c-accent-2: #2c5282; --c-accent-bg: #e8f0fa;
    @endswitch

    --r-sm: 6px;
    --r-md: 10px;
    --r-lg: 14px;
    --r-xl: 18px;

    --shadow-xs: 0 1px 2px rgba(13, 31, 53, 0.04);
    --shadow-sm: 0 2px 6px rgba(13, 31, 53, 0.05);
    --shadow-md: 0 4px 14px rgba(13, 31, 53, 0.07);
    --shadow-lg: 0 10px 30px rgba(13, 31, 53, 0.10);

    --t-fast: 0.15s ease;
    --t-base: 0.25s ease;
}

* { box-sizing: border-box; }

body {
    font-family: 'IBM Plex Sans Arabic', 'Tajawal', 'Segoe UI', sans-serif !important;
    font-weight: 400;
    color: var(--c-text);
    background: var(--c-bg);
    font-size: 14.5px;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'IBM Plex Sans Arabic', sans-serif !important;
    font-weight: 600;
    color: var(--c-navy);
    letter-spacing: -0.01em;
}

.num, .stat-value, .kpi-value {
    font-feature-settings: 'tnum';
    font-variant-numeric: tabular-nums;
}

/* ── Page Header موحد ── */
.page-header {
    background: var(--c-surface);
    border-bottom: 3px solid var(--c-accent);
    padding: 20px 24px;
    margin-bottom: 22px;
    border-radius: var(--r-lg);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: var(--shadow-sm);
}
.page-header h1, .page-header h2 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--c-navy);
    display: flex;
    align-items: center;
    gap: 10px;
}
.page-header h1 i, .page-header h2 i { color: var(--c-accent); }
.page-header .subtitle {
    color: var(--c-text-muted);
    font-size: 0.88rem;
    font-weight: 400;
    margin-top: 4px;
}

/* ── Stat Cards ── */
.stat-card-pro {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-lg);
    padding: 18px 20px;
    transition: var(--t-base);
    position: relative;
    overflow: hidden;
}
.stat-card-pro::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 3px; height: 100%;
    background: var(--c-accent);
    opacity: 0;
    transition: opacity var(--t-base);
}
.stat-card-pro:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-card-pro:hover::before { opacity: 1; }

.stat-card-pro .label {
    font-size: 0.76rem;
    font-weight: 500;
    color: var(--c-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 6px;
}
.stat-card-pro .value {
    font-size: 1.7rem;
    font-weight: 600;
    color: var(--c-navy);
    line-height: 1.15;
    font-feature-settings: 'tnum';
}
.stat-card-pro .unit {
    font-size: 0.78rem;
    font-weight: 400;
    color: var(--c-text-soft);
    margin-top: 3px;
}
.stat-card-pro.danger  { border-right: 3px solid var(--c-danger); }
.stat-card-pro.warning { border-right: 3px solid var(--c-warning); }
.stat-card-pro.success { border-right: 3px solid var(--c-success); }
.stat-card-pro.info    { border-right: 3px solid var(--c-info); }

.stat-card-pro.danger .value  { color: var(--c-danger); }
.stat-card-pro.warning .value { color: var(--c-warning); }
.stat-card-pro.success .value { color: var(--c-success); }
.stat-card-pro.info .value    { color: var(--c-info); }

/* ── Panel ── */
.panel-pro {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-lg);
    padding: 22px;
    margin-bottom: 18px;
    box-shadow: var(--shadow-xs);
}
.panel-pro-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 14px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--c-border);
}
.panel-pro-head h5 {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 600;
    color: var(--c-navy);
    display: flex; align-items: center; gap: 8px;
}
.panel-pro-head h5 i { color: var(--c-accent); font-size: 0.95rem; }
.panel-pro-head a {
    color: var(--c-accent);
    font-weight: 500;
    font-size: 0.84rem;
    text-decoration: none;
}
.panel-pro-head a:hover { color: var(--c-accent-2); }

/* ── Buttons ── */
.btn-pro {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 8px 18px;
    font-family: inherit;
    font-weight: 500;
    font-size: 0.88rem;
    border: 1px solid transparent;
    border-radius: var(--r-md);
    cursor: pointer;
    transition: var(--t-fast);
    text-decoration: none;
    line-height: 1.4;
}
.btn-pro:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-pro-primary { background: var(--c-navy); color: #fff; }
.btn-pro-primary:hover { background: var(--c-navy-soft); color: #fff; }
.btn-pro-accent  { background: var(--c-accent); color: #fff; }
.btn-pro-accent:hover  { background: var(--c-accent-2); color: #fff; }
.btn-pro-outline { background: transparent; border-color: var(--c-border-2); color: var(--c-text); }
.btn-pro-outline:hover { background: var(--c-navy-50); border-color: var(--c-accent); color: var(--c-accent); }
.btn-pro-danger  { background: var(--c-danger); color: #fff; }
.btn-pro-danger:hover  { background: #991b1b; color: #fff; }
.btn-pro-success { background: var(--c-success); color: #fff; }
.btn-pro-success:hover { background: #1f6940; color: #fff; }
.btn-pro-sm { padding: 5px 12px; font-size: 0.8rem; }
.btn-pro-lg { padding: 11px 24px; font-size: 0.95rem; }

/* ── Tables ── */
.table-pro {
    width: 100%;
    background: var(--c-surface);
    border-collapse: collapse;
}
.table-pro thead th {
    background: var(--c-navy-50);
    color: var(--c-navy);
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 12px 14px;
    text-align: start;
    border-bottom: 2px solid var(--c-border-2);
}
.table-pro tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--c-border);
    color: var(--c-text);
    font-weight: 400;
}
.table-pro tbody tr:hover { background: var(--c-navy-50); }
.table-pro tbody tr:last-child td { border-bottom: none; }

/* ── Pills ── */
.pill-pro {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    font-size: 0.74rem;
    font-weight: 500;
    border-radius: 999px;
    line-height: 1.5;
    white-space: nowrap;
}
.pill-success { background: var(--c-success-bg); color: var(--c-success); }
.pill-warning { background: var(--c-warning-bg); color: var(--c-warning); }
.pill-danger  { background: var(--c-danger-bg);  color: var(--c-danger); }
.pill-info    { background: var(--c-info-bg);    color: var(--c-info); }
.pill-accent  { background: var(--c-accent-bg);  color: var(--c-accent); }
.pill-neutral { background: #eef0f4; color: var(--c-text-muted); }

/* ── Forms ── */
.form-pro-label {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--c-text);
    margin-bottom: 5px;
    display: block;
}
.form-pro-control {
    width: 100%;
    padding: 9px 12px;
    font-family: inherit;
    font-size: 0.92rem;
    font-weight: 400;
    color: var(--c-text);
    background: var(--c-surface);
    border: 1px solid var(--c-border-2);
    border-radius: var(--r-md);
    transition: var(--t-fast);
}
.form-pro-control:focus {
    outline: none;
    border-color: var(--c-accent);
    box-shadow: 0 0 0 3px var(--c-accent-bg);
}

/* ── Alerts ── */
.alert-pro {
    padding: 12px 16px;
    border-radius: var(--r-md);
    font-weight: 500;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid transparent;
    margin-bottom: 14px;
}
.alert-pro i { font-size: 1.05rem; }
.alert-pro.success { background: var(--c-success-bg); color: var(--c-success); border-color: rgba(45,134,89,0.2); }
.alert-pro.warning { background: var(--c-warning-bg); color: var(--c-warning); border-color: rgba(182,124,31,0.2); }
.alert-pro.danger  { background: var(--c-danger-bg);  color: var(--c-danger);  border-color: rgba(185,28,28,0.2); }
.alert-pro.info    { background: var(--c-info-bg);    color: var(--c-info);    border-color: rgba(30,95,164,0.2); }

/* ── Empty state ── */
.empty-pro {
    text-align: center;
    padding: 40px 20px;
    color: var(--c-text-soft);
}
.empty-pro i { font-size: 2.5rem; margin-bottom: 10px; color: var(--c-border-2); }
.empty-pro h5 { color: var(--c-text-muted); font-weight: 500; font-size: 0.95rem; margin: 6px 0; }
.empty-pro p { margin: 0; font-size: 0.82rem; }

/* ── Bootstrap overrides ── */
.btn { font-family: inherit; }
.form-control, .form-select { font-family: inherit; font-weight: 400; }
.fw-bold { font-weight: 600 !important; }
.fw-black, .fw-900 { font-weight: 700 !important; }
.text-warning { color: var(--c-warning) !important; }
.text-danger  { color: var(--c-danger) !important; }
.text-success { color: var(--c-success) !important; }
.text-info    { color: var(--c-info) !important; }
.text-primary { color: var(--c-accent) !important; }

.divider-pro { height: 1px; background: var(--c-border); margin: 18px 0; }
.muted-pro { color: var(--c-text-muted); font-weight: 400; }
</style>
