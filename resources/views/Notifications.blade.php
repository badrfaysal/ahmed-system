<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإشعارات - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ink:       #0d1117;
            --surface:   #ffffff;
            --muted:     #6b7280;
            --line:      #e5e7eb;
            --accent:    #f59e0b;
            --accent2:   #3b82f6;
            --bg:        #f8fafc;
            --radius:    18px;
            --col-create:  #16a34a;
            --col-update:  #2563eb;
            --col-delete:  #dc2626;
            --col-cancel:  #7c3aed;
            --col-payment: #0891b2;
            --col-transfer:#d97706;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--ink);
            overflow-x: hidden;
        }

        .main-content {
            margin-right: 260px;
            padding: 36px 32px;
            min-height: 100vh;
        }

        /* ─── Ambient BG ─── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 40% at 80% 10%, rgba(245,158,11,.07) 0%, transparent 70%),
                radial-gradient(ellipse 50% 50% at 10% 80%, rgba(59,130,246,.06) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .main-content > * { position: relative; z-index: 1; }

        /* ─── Page Header ─── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 900;
            color: var(--ink);
            line-height: 1.15;
            margin: 0 0 4px;
            letter-spacing: -.02em;
        }

        .page-title .bell-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px; height: 44px;
            background: var(--accent);
            border-radius: 14px;
            margin-left: 10px;
            animation: bellShake 4s ease-in-out infinite;
            transform-origin: 50% 8%;
        }

        @keyframes bellShake {
            0%,100%  { transform: rotate(0deg); }
            5%       { transform: rotate(-12deg); }
            10%      { transform: rotate(12deg); }
            15%      { transform: rotate(-8deg); }
            20%      { transform: rotate(8deg); }
            25%      { transform: rotate(0deg); }
        }

        .page-subtitle {
            color: var(--muted);
            font-size: .85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .live-dot {
            width: 7px; height: 7px;
            background: #10b981;
            border-radius: 50%;
            position: relative;
        }
        .live-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: rgba(16,185,129,.25);
            animation: livePulse 2s ease-in-out infinite;
        }
        @keyframes livePulse {
            0%,100% { transform: scale(1); opacity: 1; }
            50%     { transform: scale(1.8); opacity: 0; }
        }

        .btn-clear {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px;
            border: 2px solid #fee2e2;
            background: #fff5f5;
            color: #b91c1c;
            border-radius: 12px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            font-size: .82rem;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-clear:hover { background: #fca5a5; border-color: #fca5a5; color: #7f1d1d; }

        /* ─── Stats Row ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-tile {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            animation: tileIn .5s both;
            position: relative;
            overflow: hidden;
        }

        @keyframes tileIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stat-tile:nth-child(1) { animation-delay: .05s; }
        .stat-tile:nth-child(2) { animation-delay: .10s; }
        .stat-tile:nth-child(3) { animation-delay: .15s; }
        .stat-tile:nth-child(4) { animation-delay: .20s; }

        .stat-tile::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            border-radius: 0 var(--radius) 0 80px;
            opacity: .06;
        }

        .stat-tile.t-amber::before  { background: #f59e0b; }
        .stat-tile.t-blue::before   { background: #3b82f6; }
        .stat-tile.t-green::before  { background: #10b981; }
        .stat-tile.t-slate::before  { background: #475569; }

        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .t-amber .stat-icon { background: #fef3c7; color: #d97706; }
        .t-blue  .stat-icon { background: #dbeafe; color: #2563eb; }
        .t-green .stat-icon { background: #d1fae5; color: #059669; }
        .t-slate .stat-icon { background: #f1f5f9; color: #475569; }

        .stat-info { flex: 1; }
        .stat-info .num {
            font-size: 1.65rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 2px;
            counter-reset: num var(--n);
        }
        .t-amber .num { color: #d97706; }
        .t-blue  .num { color: #2563eb; }
        .t-green .num { color: #059669; }
        .t-slate .num { color: #334155; }

        .stat-info .lbl { font-size: .75rem; font-weight: 700; color: var(--muted); }

        /* ─── Filter Card ─── */
        .filter-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 24px;
            animation: tileIn .4s .25s both;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            border: 1px solid var(--line);
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            font-size: .82rem;
            background: var(--bg);
            color: var(--ink);
            transition: border-color .2s, box-shadow .2s;
        }
        .filter-card .form-control:focus,
        .filter-card .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(245,158,11,.12);
            outline: none;
        }
        .filter-card label { font-size: .76rem; font-weight: 800; color: var(--muted); margin-bottom: 5px; }

        .btn-filter {
            display: inline-flex; align-items: center; gap: 6px;
            background: var(--ink); color: #fff;
            border: none; border-radius: 10px;
            padding: 9px 18px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700; font-size: .82rem;
            cursor: pointer; transition: all .2s;
        }
        .btn-filter:hover { background: #1f2937; transform: translateY(-1px); }

        .btn-reset {
            display: inline-flex; align-items: center;
            background: #fee2e2; color: #b91c1c;
            border: none; border-radius: 10px;
            padding: 9px 14px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700; font-size: .82rem;
            cursor: pointer; transition: all .2s;
        }
        .btn-reset:hover { background: #fca5a5; }

        /* ─── Feed ─── */
        .feed-wrap {
            animation: tileIn .4s .35s both;
        }

        /* ─── Day Divider ─── */
        .day-sep {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 28px 0 20px;
        }
        .day-sep::before, .day-sep::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--line);
        }
        .day-sep .day-chip {
            background: var(--ink);
            color: #fff;
            font-size: .72rem;
            font-weight: 800;
            padding: 5px 16px;
            border-radius: 20px;
            white-space: nowrap;
            letter-spacing: .02em;
        }
        .day-sep.today-sep .day-chip { background: var(--accent); color: var(--ink); }
        .day-sep.yesterday-sep .day-chip { background: #6b7280; }

        /* ─── Notification Card ─── */
        .notif-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 10px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
            position: relative;
            transition: transform .2s, box-shadow .2s, border-color .2s;
            animation: cardSlide .4s both;
        }

        @keyframes cardSlide {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .notif-card:hover {
            transform: translateX(-3px);
            box-shadow: 0 6px 24px rgba(0,0,0,.07);
            border-color: #d1d5db;
        }

        /* Left accent bar */
        .notif-card::before {
            content: '';
            position: absolute;
            right: 0; top: 20%; bottom: 20%;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: var(--card-color, #e5e7eb);
        }

        .notif-card.unread {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .notif-card.unread::before { background: var(--accent); }

        /* Action Icon */
        .action-bubble {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: .9rem;
            color: #fff;
            background: var(--card-color, #94a3b8);
            position: relative;
        }

        /* User Avatar */
        .user-circle {
            width: 34px; height: 34px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .82rem; font-weight: 900;
            color: #fff;
            flex-shrink: 0;
            position: relative;
        }
        .user-circle .online-ring {
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            border: 2px solid transparent;
        }

        /* Content */
        .notif-body { flex: 1; min-width: 0; }

        .notif-top {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 5px;
        }

        .user-name {
            font-size: .87rem;
            font-weight: 800;
            color: var(--ink);
        }

        .mod-pill {
            font-size: .65rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: .03em;
        }
        .mod-installments { background:#dbeafe; color:#1e40af; }
        .mod-sales        { background:#dcfce7; color:#166534; }
        .mod-gas          { background:#fef9c3; color:#854d0e; }
        .mod-inventory    { background:#ede9fe; color:#6d28d9; }
        .mod-finance      { background:#fee2e2; color:#991b1b; }
        .mod-treasury     { background:#f0fdf4; color:#065f46; }
        .mod-settings     { background:#f1f5f9; color:#334155; }

        .admin-crown {
            font-size: .6rem;
            background: linear-gradient(135deg,#f59e0b,#fbbf24);
            color: #78350f;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 900;
        }

        .notif-desc {
            font-size: .84rem;
            font-weight: 600;
            color: #374151;
            line-height: 1.5;
            margin-bottom: 6px;
        }

        .notif-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .time-chip {
            font-size: .7rem;
            font-weight: 700;
            color: var(--muted);
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 2px 10px;
        }

        .ip-chip {
            font-size: .68rem;
            color: var(--muted);
            font-weight: 600;
            direction: ltr;
        }

        /* Extra data tags */
        .extra-tag {
            font-size: .65rem;
            font-weight: 700;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 2px 8px;
            color: #64748b;
        }

        /* Delete btn */
        .del-btn {
            width: 28px; height: 28px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: transparent;
            color: var(--muted);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all .15s;
            font-size: .7rem;
        }
        .del-btn:hover { background: #fee2e2; border-color: #fca5a5; color: #b91c1c; }

        /* ─── Stagger delays for cards ─── */
        .notif-card:nth-child(1)  { animation-delay: .05s; }
        .notif-card:nth-child(2)  { animation-delay: .09s; }
        .notif-card:nth-child(3)  { animation-delay: .13s; }
        .notif-card:nth-child(4)  { animation-delay: .17s; }
        .notif-card:nth-child(5)  { animation-delay: .21s; }
        .notif-card:nth-child(6)  { animation-delay: .25s; }
        .notif-card:nth-child(7)  { animation-delay: .29s; }
        .notif-card:nth-child(8)  { animation-delay: .33s; }
        .notif-card:nth-child(9)  { animation-delay: .37s; }
        .notif-card:nth-child(10) { animation-delay: .41s; }

        /* ─── Empty State ─── */
        .empty-state {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 72px 32px;
            text-align: center;
        }
        .empty-icon {
            width: 80px; height: 80px;
            background: var(--bg);
            border-radius: 24px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            color: var(--muted);
            margin: 0 auto 20px;
            animation: floatIcon 3s ease-in-out infinite;
        }
        @keyframes floatIcon {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }

        /* ─── Pagination ─── */
        .pagination .page-link {
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            border-radius: 10px !important;
            border: 1px solid var(--line);
            color: var(--ink);
            margin: 0 2px;
            font-size: .82rem;
        }
        .pagination .page-item.active .page-link {
            background: var(--ink);
            border-color: var(--ink);
            color: #fff;
        }
        .pagination .page-link:hover { background: var(--bg); }

        /* ─── Shimmer on new badge ─── */
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        .new-badge {
            font-size: .58rem;
            font-weight: 900;
            padding: 1px 7px;
            border-radius: 6px;
            background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 40%, #f59e0b 100%);
            background-size: 200% auto;
            animation: shimmer 2s linear infinite;
            color: #78350f;
            letter-spacing: .04em;
        }

        @media (max-width: 768px) {
            .main-content { margin-right: 0; padding: 20px 16px; }
            .stats-row { grid-template-columns: 1fr 1fr; }
        }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    {{-- ── Header ── --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <span class="bell-icon"><i class="fa fa-bell fa-xs" style="color:#fff;"></i></span>
                الإشعارات
            </h1>
            <div class="page-subtitle">
                <span class="live-dot"></span>
                مباشر · يتحدث كل دقيقة تلقائياً
            </div>
        </div>
        <form action="{{ route('notifications.clear') }}" method="POST"
              onsubmit="return confirm('سيتم حذف سجلات أكثر من 30 يوم. هل أنت متأكد؟')">
            @csrf
         
        </form>
    </div>

    {{-- ── Stats ── --}}
    <div class="stats-row">
        <div class="stat-tile t-amber">
            <div class="stat-icon"><i class="fa fa-bell"></i></div>
            <div class="stat-info">
                <div class="num" id="num-unread">{{ $stats['unread'] }}</div>
                <div class="lbl">غير مقروءة</div>
            </div>
        </div>
        <div class="stat-tile t-blue">
            <div class="stat-icon"><i class="fa fa-calendar-day"></i></div>
            <div class="stat-info">
                <div class="num" id="num-today">{{ $stats['today'] }}</div>
                <div class="lbl">إشعارات اليوم</div>
            </div>
        </div>
        <div class="stat-tile t-green">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
            <div class="stat-info">
                <div class="num">{{ $stats['unique_users'] }}</div>
                <div class="lbl">مستخدمون نشطون</div>
            </div>
        </div>
        <div class="stat-tile t-slate">
            <div class="stat-icon"><i class="fa fa-database"></i></div>
            <div class="stat-info">
                <div class="num">{{ number_format($stats['total']) }}</div>
                <div class="lbl">إجمالي السجلات</div>
            </div>
        </div>
    </div>

    {{-- ── Filter ── --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('notifications.index') }}" class="row g-2 align-items-end">

            <div class="col-md-3">
                <label>بحث بالمستخدم</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-start-0" style="border:1px solid var(--line); border-radius:10px 0 0 10px;">
                        <i class="fa fa-user text-muted" style="font-size:.8rem;"></i>
                    </span>
                    <input type="text" name="user" class="form-control border-end-0"
                           style="border-radius:0 10px 10px 0;"
                           placeholder="اسم المستخدم..." value="{{ $userFilter }}">
                </div>
            </div>

            <div class="col-md-2">
                <label>القسم</label>
                <select name="module" class="form-select">
                    <option value="">كل الأقسام</option>
                    @foreach($modules as $mod)
                        @php $labels = ['installments'=>'الأقساط','sales'=>'المبيعات','gas'=>'البنزينة','inventory'=>'المخزن','finance'=>'مالي','treasury'=>'الخزنة','settings'=>'الإعدادات']; @endphp
                        @if($mod !== 'auth')
                        <option value="{{ $mod }}" {{ $moduleFilter===$mod?'selected':'' }}>
                            {{ $labels[$mod] ?? $mod }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>النوع</label>
                <select name="action" class="form-select">
                    <option value="">كل الأنواع</option>
                    @php $actionLabels = ['create'=>'إنشاء','update'=>'تعديل','delete'=>'حذف','cancel'=>'إلغاء','payment'=>'تحصيل','transfer'=>'تحويل']; @endphp
                    @foreach($actions as $act)
                        @if(!in_array($act, ['login','logout']))
                        <option value="{{ $act }}" {{ $actionFilter===$act?'selected':'' }}>
                            {{ $actionLabels[$act] ?? $act }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>

            <div class="col-md-2">
                <label>إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>

            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn-filter flex-fill">
                    <i class="fa fa-filter" style="font-size:.75rem;"></i>
                    فلتر
                </button>
                @if($userFilter || $moduleFilter || $actionFilter || $dateFrom || $dateTo)
                    <a href="{{ route('notifications.index') }}" class="btn-reset">
                        <i class="fa fa-times" style="font-size:.75rem;"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- ── Feed ── --}}
    @php
        $actionColorMap = [
            'create'   => '#16a34a',
            'update'   => '#2563eb',
            'delete'   => '#dc2626',
            'cancel'   => '#7c3aed',
            'payment'  => '#0891b2',
            'transfer' => '#d97706',
        ];
        $actionIconMap = [
            'create'   => 'fa-plus',
            'update'   => 'fa-pen',
            'delete'   => 'fa-trash',
            'cancel'   => 'fa-ban',
            'payment'  => 'fa-money-bill',
            'transfer' => 'fa-shuffle',
        ];
        $modLabels = [
            'installments'=>'أقساط','sales'=>'مبيعات','gas'=>'بنزينة',
            'inventory'=>'مخزن','finance'=>'مالي','treasury'=>'خزنة','settings'=>'إعدادات'
        ];
        $userColors = ['#1e40af','#065f46','#7c3aed','#b91c1c','#d97706','#0f766e','#4338ca','#0e7490'];
    @endphp

    @if($logs->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa fa-bell-slash"></i></div>
            <h5 style="font-weight:800; margin-bottom:8px;">لا توجد إشعارات</h5>
            <p style="color:var(--muted); font-size:.85rem; margin:0;">لا توجد أنشطة مطابقة للفلتر المحدد</p>
        </div>
    @else
        <div class="feed-wrap">
            @php $prevDay = null; $cardIdx = 0; @endphp

            @foreach($logs as $log)
                @php
                    /* ── تخطي إشعارات تسجيل الدخول/الخروج ── */
                    if (in_array($log->action, ['login','logout'])) continue;
                    if (($log->module ?? '') === 'auth') continue;

                    $day = \Carbon\Carbon::parse($log->created_at)->format('Y-m-d');
                    $color    = $actionColorMap[$log->action]  ?? '#94a3b8';
                    $icon     = $actionIconMap[$log->action]   ?? 'fa-circle-dot';
                    $modClass = 'mod-'.($log->module ?? 'settings');
                    $modLabel = $modLabels[$log->module] ?? ($log->module ?? '—');
                    $uColor   = $userColors[abs(crc32($log->user_name ?? '')) % count($userColors)];
                    $initial  = mb_substr($log->user_name ?? 'N', 0, 1, 'UTF-8');
                    $timeStr  = \Carbon\Carbon::parse($log->created_at)->format('h:i A');
                    $relTime  = \Carbon\Carbon::parse($log->created_at)->diffForHumans(['locale'=>'ar']);
                    $isNew    = \Carbon\Carbon::parse($log->created_at)->diffInMinutes(now()) < 10;
                    $cardIdx++;
                @endphp

                @if($day !== $prevDay)
                    @php $prevDay = $day; @endphp
                    @php
                        $parsedDay = \Carbon\Carbon::parse($log->created_at);
                        $isToday     = $parsedDay->isSameDay(now());
                        $isYesterday = $parsedDay->isYesterday();
                        $sepClass    = $isToday ? 'today-sep' : ($isYesterday ? 'yesterday-sep' : '');
                        $dayLabel    = $isToday ? 'اليوم' : ($isYesterday ? 'أمس' : $parsedDay->format('l، d M Y'));
                    @endphp
                    <div class="day-sep {{ $sepClass }}">
                        <span class="day-chip">{{ $dayLabel }}</span>
                    </div>
                @endif

                <div class="notif-card {{ $log->is_read ?? false ? '' : 'unread' }}"
                     style="--card-color:{{ $color }}; animation-delay: {{ min($cardIdx * 0.04, 0.5) }}s;">

                    {{-- Action bubble --}}
                    <div class="action-bubble" style="background:{{ $color }}15; color:{{ $color }};">
                        <i class="fa {{ $icon }}"></i>
                    </div>

                    {{-- User circle --}}
                    <div class="user-circle" style="background:{{ $uColor }};">
                        {{ $initial }}
                    </div>

                    {{-- Body --}}
                    <div class="notif-body">
                        <div class="notif-top">
                            <span class="user-name">{{ $log->user_name }}</span>
                            @if($log->user_role === 'admin')
                                <span class="admin-crown">👑 أدمن</span>
                            @endif
                            <span class="mod-pill {{ $modClass }}">{{ $modLabel }}</span>
                            @if($isNew)
                                <span class="new-badge">جديد</span>
                            @endif
                        </div>

                        <p class="notif-desc">{{ $log->description }}</p>

                        <div class="notif-meta">
                            <span class="time-chip">
                                <i class="fa fa-clock me-1" style="font-size:.6rem; opacity:.6;"></i>
                                {{ $timeStr }} · {{ $relTime }}
                            </span>
                            @if($log->ip_address)
                                <span class="ip-chip">
                                    <i class="fa fa-map-marker-alt me-1" style="font-size:.6rem; opacity:.5;"></i>
                                    {{ $log->ip_address }}
                                </span>
                            @endif
                            @if($log->extra_data)
                                @php $extra = json_decode($log->extra_data, true); @endphp
                                @if(is_array($extra))
                                    @foreach($extra as $k => $v)
                                        <span class="extra-tag">{{ $k }}: {{ $v }}</span>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Delete --}}
                    <form action="{{ route('notifications.destroy', $log->id) }}"
                          method="POST" class="d-flex align-items-start">
                        @csrf @method('DELETE')
                        <button type="submit" class="del-btn" title="حذف">
                            <i class="fa fa-times"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
        <p class="text-center mt-2" style="color:var(--muted); font-size:.78rem; font-weight:700;">
            عرض {{ $logs->firstItem() }}–{{ $logs->lastItem() }} من {{ $logs->total() }} إشعار
        </p>
    @endif

</div>

@if(!$userFilter && !$moduleFilter && !$actionFilter && !$dateFrom && !$dateTo)
<script>setTimeout(() => window.location.reload(), 60000);</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /* ── Count-up animation for stat numbers ── */
    function animateCount(el, target) {
        const dur = 900, start = performance.now();
        const from = 0;
        function tick(now) {
            const p = Math.min((now - start) / dur, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(from + (target - from) * ease).toLocaleString('ar-EG');
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    document.querySelectorAll('.stat-info .num').forEach(el => {
        const raw = el.textContent.replace(/,/g, '').trim();
        const n = parseInt(raw);
        if (!isNaN(n)) animateCount(el, n);
    });

    /* ── Hover shimmer on cards ── */
    document.querySelectorAll('.notif-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s, border-color .18s';
        });
    });
</script>
</body>
</html>