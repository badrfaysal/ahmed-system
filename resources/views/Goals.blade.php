<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الأهداف — شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ══ Reset & Base ══ */
        *, *::before, *::after { box-sizing: border-box; }
        * { font-family: 'Cairo', sans-serif; }

        :root {
            --bg:        #f4f6f9;
            --surface:   #ffffff;
            --border:    #e5e9f0;
            --border-md: #d0d7e5;
            --text:      #0f172a;
            --muted:     #64748b;
            --subtle:    #94a3b8;

            --blue:      #2563eb;
            --blue-lt:   #eff6ff;
            --blue-bd:   #bfdbfe;
            --blue-dk:   #1e40af;

            --green:     #059669;
            --green-lt:  #f0fdf4;
            --green-bd:  #a7f3d0;
            --green-dk:  #065f46;

            --red:       #dc2626;
            --red-lt:    #fef2f2;
            --red-bd:    #fecaca;
            --red-dk:    #991b1b;

            --amber:     #d97706;
            --amber-lt:  #fffbeb;
            --amber-bd:  #fde68a;
            --amber-dk:  #92400e;

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,.08);
        }

        [data-theme="dark"] {
            --bg:        #0c1420;
            --surface:   #162032;
            --border:    #1e3a5f;
            --border-md: #2a4a72;
            --text:      #e2e8f0;
            --muted:     #7090b0;
            --subtle:    #4a6080;

            --blue-lt:   #1e3a8a;
            --blue-bd:   #1e40af;
            --green-lt:  #064e3b;
            --green-bd:  #065f46;
            --red-lt:    #450a0a;
            --red-bd:    #7f1d1d;
            --amber-lt:  #451a03;
            --amber-bd:  #78350f;
        }

        body { background: var(--bg); color: var(--text); transition: background .3s, color .3s; }
        .main-content { margin-right: 260px; padding: 24px 28px; min-height: 100vh; }

        /* ══ Page Header ══ */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .page-header-left { display: flex; align-items: center; gap: 12px; }
        .page-header-icon {
            width: 44px; height: 44px;
            background: var(--blue-lt);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--blue-bd);
        }
        .page-header-icon i { color: var(--blue); font-size: 1.15rem; }
        .page-header h2 { font-size: 1.25rem; font-weight: 800; color: var(--text); margin: 0; }
        .page-header p  { font-size: .8rem; color: var(--muted); font-weight: 600; margin: 2px 0 0; }
        .header-actions { display: flex; gap: 8px; align-items: center; }

        /* ══ Stat Cards ══ */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 16px;
            box-shadow: var(--shadow-sm);
        }
        .stat-card .lbl {
            font-size: .75rem; font-weight: 700;
            color: var(--muted);
            display: flex; align-items: center; gap: 5px;
            margin-bottom: 8px;
        }
        .stat-card .val { font-size: 1.7rem; font-weight: 900; line-height: 1; }
        .stat-card.blue  .val { color: var(--blue); }
        .stat-card.green .val { color: var(--green); }
        .stat-card.red   .val { color: var(--red); }
        .stat-card.amber .val { color: var(--amber); }

        /* ══ Tabs ══ */
        .goals-nav {
            display: flex;
            gap: 0;
            border-bottom: 1.5px solid var(--border);
            margin-bottom: 20px;
        }
        .goals-nav-tab {
            padding: 9px 18px;
            font-size: .85rem; font-weight: 700;
            color: var(--muted);
            cursor: pointer;
            border: none; background: transparent;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -1.5px;
            display: flex; align-items: center; gap: 7px;
            transition: color .2s;
        }
        .goals-nav-tab:hover { color: var(--text); }
        .goals-nav-tab.active { color: var(--blue); border-bottom-color: var(--blue); }
        .goals-nav-tab.achieved.active { color: var(--green); border-bottom-color: var(--green); }
        .goals-nav-tab.failed.active   { color: var(--red);   border-bottom-color: var(--red); }
        .nav-badge {
            font-size: .7rem; font-weight: 800;
            padding: 2px 7px; border-radius: 20px;
        }
        .goals-nav-tab.active       .nav-badge { background: var(--blue-lt);  color: var(--blue); }
        .goals-nav-tab.achieved.active .nav-badge { background: var(--green-lt); color: var(--green); }
        .goals-nav-tab.failed.active   .nav-badge { background: var(--red-lt);   color: var(--red); }

        /* ══ Tab Panes ══ */
        .tab-pane { display: none; }
        .tab-pane.show { display: block; }

        /* ══ Goals Grid ══ */
        .goals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 14px;
        }

        /* ══ Goal Card ══ */
        .goal-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            gap: 14px;
            transition: box-shadow .2s, border-color .2s;
        }
        .goal-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--border-md);
        }

        /* Card Top Row */
        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .card-title-wrap { display: flex; align-items: flex-start; gap: 10px; }
        .card-icon {
            width: 38px; height: 38px; flex-shrink: 0;
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }
        .card-icon.income  { background: var(--blue-lt);  color: var(--blue); border: 1px solid var(--blue-bd); }
        .card-icon.expense { background: var(--red-lt);   color: var(--red);  border: 1px solid var(--red-bd); }
        .card-name   { font-size: .9rem; font-weight: 800; color: var(--text); line-height: 1.4; }
        .card-period { font-size: .72rem; color: var(--muted); font-weight: 600; margin-top: 3px; display: flex; align-items: center; gap: 4px; }

        /* Days pill */
        .days-pill {
            font-size: .72rem; font-weight: 800;
            padding: 4px 10px; border-radius: 20px;
            white-space: nowrap; flex-shrink: 0;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .days-pill.urgent { background: var(--red-lt);   color: var(--red);   border: 1px solid var(--red-bd); }
        .days-pill.normal { background: var(--blue-lt);  color: var(--blue);  border: 1px solid var(--blue-bd); }
        .days-pill.done   { background: var(--green-lt); color: var(--green); border: 1px solid var(--green-bd); }

        /* Divider */
        .card-divider { height: 1px; background: var(--border); }

        /* Amounts Row */
        .amounts-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }
        .amt-box { text-align: center; }
        .amt-lbl { font-size: .68rem; font-weight: 700; color: var(--muted); margin-bottom: 4px; }
        .amt-val { font-size: .95rem; font-weight: 900; color: var(--text); }
        .amt-val.blue  { color: var(--blue); }
        .amt-val.green { color: var(--green); }
        .amt-val.red   { color: var(--red); }
        .amt-val.amber { color: var(--amber); }

        /* Progress */
        .progress-section {}
        .progress-head {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 7px;
        }
        .progress-pct { font-size: .8rem; font-weight: 800; color: var(--text); }
        .surplus-pill {
            font-size: .7rem; font-weight: 800;
            padding: 3px 9px; border-radius: 20px;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .surplus-pill.over  { background: var(--green-lt); color: var(--green); }
        .surplus-pill.under { background: var(--red-lt);   color: var(--red); }
        .progress-track {
            height: 10px; background: var(--border);
            border-radius: 20px; overflow: hidden;
            margin-bottom: 5px;
        }
        .progress-fill {
            height: 100%; border-radius: 20px;
            transition: width 1s ease;
        }
        .progress-fill.income  { background: var(--blue); }
        .progress-fill.expense { background: var(--red); }
        .progress-fill.done    { background: var(--green); }
        .progress-ends {
            display: flex; justify-content: space-between;
            font-size: .68rem; color: var(--subtle); font-weight: 600;
        }

        /* Time bar */
        .time-row {
            display: flex; align-items: center; gap: 8px;
        }
        .time-lbl { font-size: .68rem; color: var(--muted); font-weight: 700; white-space: nowrap; }
        .time-track { flex: 1; height: 4px; background: var(--border); border-radius: 20px; overflow: hidden; }
        .time-fill  { height: 100%; border-radius: 20px; background: var(--subtle); }
        .time-val   { font-size: .68rem; color: var(--muted); font-weight: 700; white-space: nowrap; }

        /* Notes */
        .card-notes {
            font-size: .75rem; color: var(--muted); font-weight: 600;
            display: flex; align-items: flex-start; gap: 5px;
            background: var(--bg); border-radius: var(--radius-sm);
            padding: 8px 10px; line-height: 1.5;
        }

        /* Actions */
        .card-actions { display: flex; gap: 7px; margin-top: 2px; }
        .btn-close {
            flex: 1; font-size: .78rem; font-weight: 800;
            padding: 8px 12px; border-radius: var(--radius-sm);
            background: var(--green-lt); color: var(--green);
            border: 1px solid var(--green-bd); cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 5px;
            transition: .2s;
        }
        .btn-close:hover { background: var(--green); color: #fff; }
        .btn-delete {
            font-size: .78rem; font-weight: 800;
            padding: 8px 14px; border-radius: var(--radius-sm);
            background: var(--red-lt); color: var(--red);
            border: 1px solid var(--red-bd); cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 5px;
            transition: .2s;
        }
        .btn-delete:hover { background: var(--red); color: #fff; }

        /* ══ Empty State ══ */
        .empty-state {
            text-align: center; padding: 56px 20px;
            color: var(--muted);
        }
        .empty-state i { font-size: 2.5rem; opacity: .25; margin-bottom: 14px; display: block; }
        .empty-state p { font-weight: 700; font-size: 1rem; margin-bottom: 4px; }
        .empty-state small { font-size: .82rem; color: var(--subtle); }

        /* ══ Closed Goals Section ══ */
        .section-block {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-top: 8px;
        }
        .section-block-header {
            padding: 13px 18px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 8px;
            font-size: .85rem; font-weight: 800; color: var(--muted);
        }
        .closed-table { width: 100%; border-collapse: collapse; }
        .closed-table th {
            padding: 10px 14px;
            font-size: .72rem; font-weight: 800; color: var(--muted);
            text-align: center;
            background: var(--bg);
            border-bottom: 1.5px solid var(--border);
        }
        .closed-table td {
            padding: 11px 14px;
            font-size: .82rem; font-weight: 700; color: var(--text);
            text-align: center;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .closed-table tbody tr:last-child td { border-bottom: none; }
        .closed-table tbody tr:hover td { background: var(--bg); }

        /* ══ Inline badges ══ */
        .type-badge {
            font-size: .7rem; font-weight: 800;
            padding: 3px 10px; border-radius: 8px;
            display: inline-block;
        }
        .type-badge.income  { background: var(--blue-lt);  color: var(--blue); }
        .type-badge.expense { background: var(--red-lt);   color: var(--red); }
        .pct-badge {
            font-size: .75rem; font-weight: 900;
            padding: 3px 10px; border-radius: 8px;
        }
        .pct-badge.green { background: var(--green-lt); color: var(--green); }
        .pct-badge.red   { background: var(--red-lt);   color: var(--red); }

        /* ══ Buttons ══ */
        .btn-primary-custom {
            background: var(--blue); color: #fff;
            border: none; border-radius: var(--radius-sm);
            padding: 9px 20px; font-weight: 800; font-size: .85rem;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
            transition: .2s;
        }
        .btn-primary-custom:hover { background: var(--blue-dk); }
        .btn-icon-round {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
            color: #fff; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: .2s; font-size: .9rem;
        }
        .btn-icon-round:hover { background: rgba(255,255,255,.2); }

        /* ══ Modal ══ */
        .modal-content { border-radius: var(--radius-lg); border: 0; background: var(--surface); box-shadow: 0 20px 60px rgba(0,0,0,.18); }
        .modal-header-custom {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            padding: 18px 22px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-header-icon {
            width: 40px; height: 40px;
            background: rgba(255,255,255,.12); border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
        }
        .nc-label { font-weight: 800; font-size: .78rem; color: var(--muted); margin-bottom: 5px; display: block; letter-spacing: .3px; }
        .form-control, .form-select {
            border-radius: var(--radius-sm); border: 1.5px solid var(--border);
            padding: 9px 13px; font-weight: 700; font-size: .88rem;
            background: var(--surface); color: var(--text);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        /* Source grid */
        .source-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px,1fr)); gap: 8px; }
        .source-opt { cursor: pointer; }
        .source-opt input { display: none; }
        .source-box {
            border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            padding: 10px 12px; display: flex; align-items: center; gap: 9px;
            font-size: .8rem; font-weight: 700; color: var(--muted);
            background: var(--bg); transition: .15s;
        }
        .source-box:hover { border-color: var(--blue-bd); }
        .source-opt input:checked + .source-box { border-color: var(--blue); background: var(--blue-lt); color: var(--blue); }
        .source-opt.expense input:checked + .source-box { border-color: var(--red); background: var(--red-lt); color: var(--red); }
        .source-icon-sm {
            width: 30px; height: 30px; flex-shrink: 0;
            border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: .85rem;
        }

        /* Period buttons */
        .period-btn {
            border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            padding: 7px 14px; font-size: .8rem; font-weight: 800;
            cursor: pointer; background: var(--bg); color: var(--muted); transition: .15s;
        }
        .period-btn.active,
        .period-btn:hover { border-color: var(--blue); background: var(--blue-lt); color: var(--blue); }

        /* Step label */
        .step-label {
            font-weight: 900; font-size: .92rem; color: var(--text);
            margin-bottom: 14px; padding-bottom: 11px; border-bottom: 1.5px solid var(--border);
            display: flex; align-items: center; gap: 8px;
        }
        .step-num {
            width: 28px; height: 28px; flex-shrink: 0;
            background: var(--blue); color: #fff;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: .78rem; font-weight: 900;
        }
        .modal-step {
            background: var(--surface); border: 1.5px solid var(--border);
            border-radius: var(--radius-md); padding: 18px; margin-bottom: 14px;
        }

        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        .pulse { animation: pulse 1.4s infinite; }

        @media (max-width: 1200px) { .stat-row { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 768px)  { .main-content { margin-right: 0; padding: 14px; } .stat-row { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 480px)  { .stat-row { grid-template-columns: 1fr 1fr; } .goals-grid { grid-template-columns: 1fr; } }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success fw-bold rounded-3 mb-3 d-flex align-items-center gap-2">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fw-bold rounded-3 mb-3 d-flex align-items-center gap-2">
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <i class="fa fa-bullseye"></i>
            </div>
            <div>
                <h2>صفحة الأهداف</h2>
                <p>حدد أهدافك وتابع تقدمك لحظة بلحظة</p>
            </div>
        </div>
        <div class="header-actions">
            <button id="themeToggle" class="btn-icon-round" style="background:var(--border);color:var(--muted);border:1px solid var(--border-md);">
                <i class="fa fa-moon"></i>
            </button>
            <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#newGoalModal">
                <i class="fa fa-plus"></i> هدف جديد
            </button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-row">
        <div class="stat-card blue">
            <div class="lbl"><i class="fa fa-fire"></i> أهداف نشطة</div>
            <div class="val">{{ $stats['active_count'] }}</div>
        </div>
        <div class="stat-card green">
            <div class="lbl"><i class="fa fa-trophy"></i> تحققت</div>
            <div class="val">{{ $stats['achieved_count'] }}</div>
        </div>
        <div class="stat-card red">
            <div class="lbl"><i class="fa fa-times-circle"></i> لم تتحقق</div>
            <div class="val">{{ $stats['failed_count'] }}</div>
        </div>
        <div class="stat-card amber">
            <div class="lbl"><i class="fa fa-chart-line"></i> متوسط التقدم</div>
            <div class="val">{{ $stats['avg_pct'] }}%</div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="goals-nav">
        <button class="goals-nav-tab active" onclick="switchTab('active', this)">
            <i class="fa fa-fire"></i> الأهداف النشطة
            <span class="nav-badge">{{ $stats['active_count'] }}</span>
        </button>
        <button class="goals-nav-tab achieved" onclick="switchTab('achieved', this)">
            <i class="fa fa-trophy"></i> المحققة
            <span class="nav-badge">{{ $stats['achieved_count'] }}</span>
        </button>
        <button class="goals-nav-tab failed" onclick="switchTab('failed', this)">
            <i class="fa fa-times-circle"></i> لم تتحقق
            <span class="nav-badge">{{ $stats['failed_count'] }}</span>
        </button>
    </div>

    {{-- ══ TAB: Active Goals ══ --}}
    <div class="tab-pane show" id="pane-active">
        @if($activeGoals->isEmpty())
            <div class="empty-state">
                <i class="fa fa-bullseye"></i>
                <p>لا توجد أهداف نشطة</p>
                <small>اضغط «هدف جديد» لتحديد أول هدف لك</small>
                <div class="mt-3">
                    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#newGoalModal">
                        <i class="fa fa-plus"></i> أضف أول هدف
                    </button>
                </div>
            </div>
        @else
            <div class="goals-grid">
                @foreach($activeGoals as $goal)
                @php
                    $isDone   = $goal->type === 'expense'
                        ? $goal->actual_amount <= $goal->target_amount
                        : $goal->actual_amount >= $goal->target_amount;
                    $fillCls  = $isDone ? 'done' : $goal->type;
                    $pct      = $goal->actual_pct;
                    $barW     = min(100, $pct);
                    $daysCls  = $isDone ? 'done' : ($goal->days_remaining <= 3 ? 'urgent' : 'normal');
                    $amtColor = $goal->type === 'income' ? 'blue' : 'red';
                @endphp
                <div class="goal-card">

                    {{-- Top: icon + name + days --}}
                    <div class="card-top">
                        <div class="card-title-wrap">
                            <div class="card-icon {{ $goal->type }}">
                                <i class="fa {{ $goal->type === 'income' ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                            </div>
                            <div>
                                <div class="card-name">{{ $goal->source_label }}</div>
                                <div class="card-period">
                                    <i class="fa fa-calendar-days" style="font-size:.65rem;"></i>
                                    {{ \Carbon\Carbon::parse($goal->start_date)->format('d/m/Y') }}
                                    &larr;
                                    {{ \Carbon\Carbon::parse($goal->end_date)->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                        @if($isDone)
                            <span class="days-pill done"><i class="fa fa-circle-check pulse"></i> تحقق!</span>
                        @elseif($goal->days_remaining <= 3)
                            <span class="days-pill urgent"><i class="fa fa-clock pulse"></i> {{ $goal->days_remaining }} أيام</span>
                        @else
                            <span class="days-pill normal"><i class="fa fa-calendar-days"></i> {{ $goal->days_remaining }} يوم</span>
                        @endif
                    </div>

                    <div class="card-divider"></div>

                    {{-- Amounts row --}}
                    <div class="amounts-row">
                        <div class="amt-box">
                            <div class="amt-lbl">المستهدف</div>
                            <div class="amt-val">{{ number_format($goal->target_amount, 0) }} ج</div>
                        </div>
                        <div class="amt-box">
                            <div class="amt-lbl">{{ $goal->type === 'expense' ? 'المصروف' : 'المحقق' }}</div>
                            <div class="amt-val {{ $isDone ? 'green' : $amtColor }}">
                                {{ number_format($goal->actual_amount, 0) }} ج
                            </div>
                        </div>
                        <div class="amt-box">
                            @if($isDone && $goal->surplus > 0)
                                <div class="amt-lbl">الزيادة</div>
                                <div class="amt-val green">+{{ number_format($goal->surplus, 0) }} ج</div>
                            @else
                                <div class="amt-lbl">المتبقي</div>
                                <div class="amt-val red">{{ number_format($goal->shortage, 0) }} ج</div>
                            @endif
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="progress-section">
                        <div class="progress-head">
                            <span class="progress-pct">{{ $pct }}% إنجاز</span>
                            @if($isDone && $goal->surplus > 0)
                                <span class="surplus-pill over"><i class="fa fa-arrow-up" style="font-size:.65rem;"></i> زيادة {{ number_format($goal->surplus, 0) }} ج</span>
                            @elseif(!$isDone && $goal->shortage > 0)
                                <span class="surplus-pill under"><i class="fa fa-arrow-down" style="font-size:.65rem;"></i> ناقص {{ number_format($goal->shortage, 0) }} ج</span>
                            @endif
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill {{ $fillCls }}" style="width:{{ $barW }}%;"></div>
                        </div>
                        <div class="progress-ends">
                            <span>0</span>
                            <span>{{ number_format($goal->target_amount / 2, 0) }} ج</span>
                            <span>{{ number_format($goal->target_amount, 0) }} ج</span>
                        </div>
                    </div>

                    {{-- Time elapsed --}}
                    <div class="time-row">
                        <span class="time-lbl">الوقت المنقضي</span>
                        <div class="time-track">
                            <div class="time-fill" style="width:{{ $goal->time_pct }}%;"></div>
                        </div>
                        <span class="time-val">{{ $goal->time_pct }}%</span>
                    </div>

                    {{-- Notes --}}
                    @if($goal->notes)
                        <div class="card-notes">
                            <i class="fa fa-note-sticky" style="flex-shrink:0;margin-top:2px;"></i>
                            {{ $goal->notes }}
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="card-actions">
                        <form method="POST" action="{{ route('goals.close') }}" style="display:contents;" onsubmit="return confirm('إغلاق الهدف وتسجيل النتيجة نهائياً؟')">
                            @csrf
                            <input type="hidden" name="goal_id" value="{{ $goal->id }}">
                            <button type="submit" class="btn-close">
                                <i class="fa fa-check"></i> إغلاق وتسجيل النتيجة
                            </button>
                        </form>
                        <form method="POST" action="{{ route('goals.destroy') }}" style="display:contents;" onsubmit="return confirm('حذف الهدف نهائياً؟')">
                            @csrf
                            <input type="hidden" name="goal_id" value="{{ $goal->id }}">
                            <button type="submit" class="btn-delete"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>

                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ══ TAB: Achieved ══ --}}
    <div class="tab-pane" id="pane-achieved">
        @php $achieved = $closedGoals->where('status', 'achieved'); @endphp
        @if($achieved->isEmpty())
            <div class="empty-state">
                <i class="fa fa-trophy"></i>
                <p>لا توجد أهداف محققة بعد</p>
            </div>
        @else
            <div class="section-block">
                <div class="section-block-header">
                    <i class="fa fa-trophy" style="color:var(--amber);"></i>
                    الأهداف المحققة — {{ $achieved->count() }} هدف
                </div>
                <div class="table-responsive">
                    <table class="closed-table">
                        <thead>
                            <tr>
                                <th style="text-align:right;">المصدر</th>
                                <th>النوع</th>
                                <th>المستهدف</th>
                                <th>المحقق</th>
                                <th>النسبة</th>
                                <th>الزيادة</th>
                                <th>الفترة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($achieved as $g)
                            @php $surplus = max(0, $g->achieved_amount - $g->target_amount); @endphp
                            <tr>
                                <td style="text-align:right; font-weight:800;">
                                    <i class="fa fa-trophy" style="color:var(--amber); font-size:.75rem; margin-left:5px;"></i>
                                    {{ $g->source_label }}
                                </td>
                                <td>
                                    <span class="type-badge {{ $g->type }}">
                                        {{ $g->type === 'income' ? 'إيراد' : 'مصروف' }}
                                    </span>
                                </td>
                                <td>{{ number_format($g->target_amount, 0) }} ج</td>
                                <td style="color:var(--green); font-weight:800;">{{ number_format($g->achieved_amount, 0) }} ج</td>
                                <td><span class="pct-badge green">{{ $g->achieved_pct }}%</span></td>
                                <td style="color:var(--green); font-weight:800;">
                                    @if($surplus > 0) +{{ number_format($surplus, 0) }} ج @else — @endif
                                </td>
                                <td style="color:var(--muted); font-size:.75rem;">
                                    {{ \Carbon\Carbon::parse($g->start_date)->format('d/m/Y') }}
                                    &rarr;
                                    {{ \Carbon\Carbon::parse($g->end_date)->format('d/m/Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- ══ TAB: Failed ══ --}}
    <div class="tab-pane" id="pane-failed">
        @php $failed = $closedGoals->where('status', 'failed'); @endphp
        @if($failed->isEmpty())
            <div class="empty-state">
                <i class="fa fa-circle-check" style="color:var(--green);"></i>
                <p>ما فيش أهداف فاشلة 🎉</p>
            </div>
        @else
            <div class="section-block">
                <div class="section-block-header">
                    <i class="fa fa-times-circle" style="color:var(--red);"></i>
                    لم تتحقق — {{ $failed->count() }} هدف
                </div>
                <div class="table-responsive">
                    <table class="closed-table">
                        <thead>
                            <tr>
                                <th style="text-align:right;">المصدر</th>
                                <th>النوع</th>
                                <th>المستهدف</th>
                                <th>المحقق</th>
                                <th>النسبة</th>
                                <th>النقص</th>
                                <th>الفترة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failed as $g)
                            @php $shortage = max(0, $g->target_amount - $g->achieved_amount); @endphp
                            <tr>
                                <td style="text-align:right; font-weight:800; color:var(--muted);">
                                    <i class="fa fa-times-circle" style="color:var(--red); font-size:.75rem; margin-left:5px;"></i>
                                    {{ $g->source_label }}
                                </td>
                                <td>
                                    <span class="type-badge expense">
                                        {{ $g->type === 'income' ? 'إيراد' : 'مصروف' }}
                                    </span>
                                </td>
                                <td>{{ number_format($g->target_amount, 0) }} ج</td>
                                <td style="color:var(--red); font-weight:800;">{{ number_format($g->achieved_amount, 0) }} ج</td>
                                <td><span class="pct-badge red">{{ $g->achieved_pct }}%</span></td>
                                <td style="color:var(--red); font-weight:800;">
                                    @if($shortage > 0) -{{ number_format($shortage, 0) }} ج @else — @endif
                                </td>
                                <td style="color:var(--muted); font-size:.75rem;">
                                    {{ \Carbon\Carbon::parse($g->start_date)->format('d/m/Y') }}
                                    &rarr;
                                    {{ \Carbon\Carbon::parse($g->end_date)->format('d/m/Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

</div>{{-- end main-content --}}


{{-- ══════════════════════════════════════════════════
     MODAL: إنشاء هدف جديد
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="newGoalModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('goals.store') }}" class="modal-content" onsubmit="return validateGoalForm(event)">
            @csrf
            <input type="hidden" name="source_key"   id="h_source_key">
            <input type="hidden" name="source_label" id="h_source_label">
            <input type="hidden" name="type"         id="h_type">

            {{-- Modal Header --}}
            <div class="modal-header-custom">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-header-icon">
                        <i class="fa fa-bullseye text-white"></i>
                    </div>
                    <div>
                        <h5 class="text-white fw-bold m-0" style="font-size:1rem;">إنشاء هدف جديد</h5>
                        <small style="color:rgba(255,255,255,.55); font-size:.75rem; font-weight:600;">اختر المصدر، حدد المبلغ والفترة</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4" style="background:var(--bg);">

                {{-- Step 1: نوع الهدف --}}
                <div class="modal-step">
                    <div class="step-label">
                        <span class="step-num">١</span>
                        نوع الهدف
                    </div>
                    <div style="display:flex; gap:10px;">
                        <label style="flex:1; cursor:pointer;">
                            <input type="radio" name="_goal_type_ui" value="income" checked onchange="switchGoalType('income')" style="display:none;">
                            <div class="source-box" id="box-income" style="justify-content:center; padding:14px; border-color:var(--blue); background:var(--blue-lt); color:var(--blue);">
                                <i class="fa fa-arrow-trend-up" style="font-size:1rem;"></i>
                                <span style="font-size:.88rem; font-weight:800;">هدف إيراد (ربح)</span>
                            </div>
                        </label>
                        <label style="flex:1; cursor:pointer;">
                            <input type="radio" name="_goal_type_ui" value="expense" onchange="switchGoalType('expense')" style="display:none;">
                            <div class="source-box" id="box-expense" style="justify-content:center; padding:14px;">
                                <i class="fa fa-arrow-trend-down" style="font-size:1rem;"></i>
                                <span style="font-size:.88rem; font-weight:800;">هدف مصروف (ميزانية)</span>
                            </div>
                        </label>
                    </div>
                    <small style="color:var(--muted); font-size:.72rem; font-weight:700; margin-top:8px; display:block;">
                        <i class="fa fa-info-circle me-1"></i>هدف المصروف: لو صرفت أقل من الهدف = حققت الهدف ووفّرت
                    </small>
                </div>

                {{-- Step 2: المصدر --}}
                <div class="modal-step">
                    <div class="step-label">
                        <span class="step-num">٢</span>
                        اختر المصدر
                        <small style="color:var(--muted); font-size:.72rem; font-weight:600;">تُقرأ تلقائياً من السيستم</small>
                    </div>

                    <div id="income-sources">
                        <div class="source-grid">
                            @foreach($sources['income'] as $src)
                            <label class="source-opt">
                                <input type="radio" name="_source_ui" value="{{ $src['key'] }}"
                                    data-label="{{ $src['label'] }}" data-type="income"
                                    onchange="selectSource(this)">
                                <div class="source-box">
                                    <div class="source-icon-sm" style="background:{{ $src['color'] }}20; color:{{ $src['color'] }};">
                                        <i class="fa {{ $src['icon'] }}"></i>
                                    </div>
                                    <span>{{ $src['label'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="expense-sources" style="display:none;">
                        <div class="source-grid">
                            @foreach($sources['expense'] as $src)
                            <label class="source-opt expense">
                                <input type="radio" name="_source_ui" value="{{ $src['key'] }}"
                                    data-label="{{ $src['label'] }}" data-type="expense"
                                    onchange="selectSource(this)">
                                <div class="source-box">
                                    <div class="source-icon-sm" style="background:var(--red-lt); color:var(--red);">
                                        <i class="fa {{ $src['icon'] }}"></i>
                                    </div>
                                    <span>{{ $src['label'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Step 3: المبلغ والفترة --}}
                <div class="modal-step">
                    <div class="step-label">
                        <span class="step-num">٣</span>
                        المبلغ المستهدف والفترة
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="nc-label">المبلغ المستهدف (ج.م) <span style="color:var(--red);">*</span></label>
                            <input type="number" name="target_amount" id="target_amount_inp"
                                   class="form-control fw-bold text-center"
                                   style="font-size:1.3rem;"
                                   placeholder="0" min="1" step="1" required>
                        </div>

                        <div class="col-12">
                            <label class="nc-label">الفترة الزمنية <span style="color:var(--red);">*</span></label>
                            <div style="display:flex; gap:7px; flex-wrap:wrap; margin-bottom:10px;">
                                <button type="button" class="period-btn" onclick="setPeriod('weekly')">أسبوع</button>
                                <button type="button" class="period-btn active" onclick="setPeriod('monthly')">شهر كامل</button>
                                <button type="button" class="period-btn" onclick="setPeriod('custom')">تحديد يدوي</button>
                            </div>
                            <input type="hidden" name="period_type" id="period_type_inp" value="monthly">
                        </div>

                        <div class="col-md-6">
                            <label class="nc-label">من تاريخ <span style="color:var(--red);">*</span></label>
                            <input type="date" name="start_date" id="start_date_inp" class="form-control fw-bold" required>
                        </div>
                        <div class="col-md-6">
                            <label class="nc-label">إلى تاريخ <span style="color:var(--red);">*</span></label>
                            <input type="date" name="end_date" id="end_date_inp" class="form-control fw-bold" required>
                        </div>

                        <div class="col-12">
                            <label class="nc-label">ملاحظة (اختياري)</label>
                            <input type="text" name="notes" class="form-control fw-bold"
                                   placeholder="مثال: هدف مارس لتحقيق نمو التقسيط...">
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-0 p-3" style="background:var(--surface);">
                <button type="button" class="btn btn-light fw-bold px-4 rounded-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn-primary-custom">
                    <i class="fa fa-bullseye"></i> حفظ الهدف
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Theme ──
    const themeBtn   = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    themeBtn.innerHTML = savedTheme === 'dark'
        ? '<i class="fa fa-sun"></i>'
        : '<i class="fa fa-moon"></i>';
    themeBtn.addEventListener('click', () => {
        const t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('theme', t);
        themeBtn.innerHTML = t === 'dark' ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';
    });

    // ── Tab switching ──
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show'));
        document.querySelectorAll('.goals-nav-tab').forEach(b => b.classList.remove('active'));
        document.getElementById('pane-' + tab).classList.add('show');
        btn.classList.add('active');
    }

    // ── Goal type switch ──
    function switchGoalType(type) {
        const isIncome = type === 'income';
        document.getElementById('income-sources').style.display  = isIncome ? 'block' : 'none';
        document.getElementById('expense-sources').style.display = isIncome ? 'none'  : 'block';

        const bi = document.getElementById('box-income');
        const be = document.getElementById('box-expense');
        bi.style.borderColor = isIncome ? 'var(--blue)' : 'var(--border)';
        bi.style.background  = isIncome ? 'var(--blue-lt)' : 'var(--bg)';
        bi.style.color       = isIncome ? 'var(--blue)' : 'var(--muted)';
        be.style.borderColor = !isIncome ? 'var(--red)' : 'var(--border)';
        be.style.background  = !isIncome ? 'var(--red-lt)' : 'var(--bg)';
        be.style.color       = !isIncome ? 'var(--red)' : 'var(--muted)';

        document.getElementById('h_source_key').value   = '';
        document.getElementById('h_source_label').value = '';
        document.getElementById('h_type').value         = type;
        document.querySelectorAll('input[name="_source_ui"]').forEach(r => r.checked = false);
    }

    // ── Source selection ──
    function selectSource(radio) {
        document.getElementById('h_source_key').value   = radio.value;
        document.getElementById('h_source_label').value = radio.dataset.label;
        document.getElementById('h_type').value         = radio.dataset.type;
    }

    // ── Period presets ──
    function setPeriod(type) {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        event.currentTarget.classList.add('active');
        document.getElementById('period_type_inp').value = type;

        const today = new Date();
        const fmt   = d => d.toISOString().split('T')[0];

        if (type === 'weekly') {
            const end = new Date(today);
            end.setDate(today.getDate() + 6);
            document.getElementById('start_date_inp').value = fmt(today);
            document.getElementById('end_date_inp').value   = fmt(end);
        } else if (type === 'monthly') {
            const start = new Date(today.getFullYear(), today.getMonth(), 1);
            const end   = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            document.getElementById('start_date_inp').value = fmt(start);
            document.getElementById('end_date_inp').value   = fmt(end);
        }
    }

    // ── Form validation ──
    function validateGoalForm(e) {
        if (!document.getElementById('h_source_key').value) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'اختر المصدر', text:'الرجاء اختيار مصدر الهدف أولاً', confirmButtonColor:'#2563eb' });
            return false;
        }
        const amt = parseFloat(document.getElementById('target_amount_inp').value) || 0;
        if (amt <= 0) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'أدخل المبلغ', text:'الرجاء إدخال مبلغ مستهدف صحيح', confirmButtonColor:'#2563eb' });
            return false;
        }
        return true;
    }

    // ── Init ──
    document.addEventListener('DOMContentLoaded', () => {
        setPeriod('monthly');
        document.getElementById('h_type').value = 'income';
    });
</script>
</body>
</html>