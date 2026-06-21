<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ديون الموردين والمحطات - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --gold: #c8922a; --gold-light: #e8b84b; --gold-dim: #f5e6c8; --gold-border: #e0c87a;
            --navy: #0f2240; --navy-mid: #1a3a5f; --navy-light: #e8eef5;
            --surface: #ffffff; --bg: #f0f4f8; --text-dark: #0f172a; --text-mid: #475569; --text-light: #94a3b8;
            --danger: #dc2626; --danger-bg: #fef2f2; --success: #059669; --success-bg: #ecfdf5;
            --border: #e2e8f0; --radius: 16px; --shadow: 0 4px 20px rgba(0,0,0,0.07);
        }

        * { box-sizing: border-box; }
        body { font-family: 'Cairo', sans-serif; background: var(--bg); color: var(--text-dark); overflow-x: hidden; }
        .main-content { margin-right: 260px; padding: 30px 28px; min-height: 100vh; }

        /* ══════════════ PAGE HEADER ══════════════ */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 15px;}
        .page-title { font-family: 'Tajawal', sans-serif; font-size: 1.65rem; font-weight: 900; color: var(--navy); margin: 0 0 4px; }
        .page-title span { color: var(--gold); }
        .page-subtitle { color: var(--text-mid); font-size: 0.82rem; margin: 0; font-weight: 500; }

        .alert { border-radius: 12px; border: none; font-weight: 700; font-size: 0.88rem; padding: 12px 18px; margin-bottom: 18px; }

        /* ══════════════ STAT CARDS ══════════════ */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { border-radius: var(--radius); padding: 20px 22px; display: flex; align-items: center; gap: 16px; position: relative; overflow: hidden; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card::after { content: ''; position: absolute; top: -20px; left: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.07); border-radius: 50%; }
        .sc-gold  { background: linear-gradient(135deg, #b45309, var(--gold)); box-shadow: 0 6px 20px rgba(180,83,9,0.3); color:white; }
        .sc-navy  { background: linear-gradient(135deg, var(--navy), var(--navy-mid)); box-shadow: 0 6px 20px rgba(15,34,64,0.3); color:white; }
        .sc-green { background: linear-gradient(135deg, #065f46, #10b981); box-shadow: 0 6px 20px rgba(6,95,70,0.3); color:white; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .stat-info p { margin: 0 0 2px; font-size: 0.72rem; font-weight: 700; opacity: 0.85; text-transform: uppercase; }
        .stat-info h3 { margin: 0; font-size: 1.45rem; font-weight: 900; font-family: 'Tajawal', sans-serif; }

        /* ══════════════ FILTER BAR ══════════════ */
        .filter-section { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 14px 18px; margin-bottom: 22px; box-shadow: var(--shadow); display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .filter-label { font-size: 0.85rem; font-weight: 900; color: var(--navy); white-space: nowrap; }
        .filter-pills { display: flex; gap: 8px; flex-wrap: wrap; flex: 1; }
        .filter-pill { padding: 6px 16px; border-radius: 50px; font-size: 0.78rem; font-weight: 700; border: 1.5px solid var(--border); background: #f8fafc; color: var(--text-mid); cursor: pointer; transition: all 0.18s; white-space: nowrap; }
        .filter-pill:hover { border-color: var(--gold); color: var(--gold); background: var(--gold-dim); }
        .filter-pill.active { background: var(--gold); color: white; border-color: var(--gold); box-shadow: 0 3px 10px rgba(200,146,42,0.35); }

        .filter-search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 300px; }
        .filter-search-wrap i { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 0.82rem; }
        .filter-search-wrap input { width: 100%; padding: 8px 38px 8px 14px; border-radius: 50px; border: 1.5px solid var(--border); font-size: 0.82rem; font-weight: 600; outline: none; background: #f8fafc; color: var(--text-dark); transition: 0.2s; }
        .filter-search-wrap input:focus { border-color: var(--gold); background: white; }

        /* ══════════════ 💡 TIME FILTER CARDS (الجديدة) ══════════════ */
        .time-filter-cards { display: flex; align-items: center; gap: 8px; background: white; padding: 6px 12px; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow); flex-wrap: wrap; }
        .time-card { padding: 6px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 800; color: var(--text-mid); background: #f8fafc; border: 1px solid var(--border); text-decoration: none; transition: 0.2s; }
        .time-card:hover { background: var(--navy-light); color: var(--navy); }
        .time-card.active { background: var(--navy); color: white; border-color: var(--navy); box-shadow: 0 4px 10px rgba(15,34,64,0.2); }
        .time-card-input { border: 1px solid var(--border); background: #f8fafc; color: var(--navy); font-weight: 800; font-size: 0.82rem; padding: 5px 10px; border-radius: 8px; outline: none; cursor: pointer; transition: 0.2s; height: 33px; }
        .time-card-input:focus, .time-card-input:hover { border-color: var(--navy); background: white;}

        /* ══════════════ TABS & TABLE ══════════════ */
        .tabs-header { display: flex; gap: 0; margin-bottom: 0; }
        .tab-btn { padding: 11px 28px; border: 1.5px solid var(--border); border-bottom: none; background: #f1f5f9; color: var(--text-mid); font-weight: 800; font-size: 0.85rem; cursor: pointer; border-radius: 12px 12px 0 0; margin-left: 4px; position: relative; bottom: -1px; }
        .tab-btn.active { background: var(--surface); color: var(--gold); border-color: var(--border); border-bottom-color: var(--surface); z-index: 1; }
        .tab-btn .tab-count { display: inline-block; margin-right: 6px; background: currentColor; opacity: 0.15; border-radius: 20px; padding: 1px 8px; font-size: 0.72rem; }
        .tab-btn.active .tab-count { opacity: 1; background: var(--gold-dim); color: var(--gold); }

        .table-container { background: var(--surface); border: 1.5px solid var(--border); border-radius: 0 16px 16px 16px; box-shadow: var(--shadow); overflow: hidden; padding: 20px; }
        .tab-pane { display: none; }
        .tab-pane.show { display: block; }

        .table { margin: 0; font-size: 0.875rem; width: 100%; }
        .table thead th { background: #f8fafc; color: var(--text-mid); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid var(--border); padding: 14px 16px; white-space: nowrap; text-align: center;}
        .table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: var(--text-dark); text-align: center;}
        
        .clickable-row { cursor: pointer; transition: background 0.15s; }
        .clickable-row:hover { background: #fffbeb !important; }
        
        .inner-clickable-row { cursor: pointer; transition: all 0.2s; }
        .inner-clickable-row:hover { background: #e0f2fe !important; transform: scale(1.005); }

        .row-num { width: 30px; height: 30px; border-radius: 8px; background: var(--navy-light); color: var(--navy); display: inline-flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.75rem; }
        .creditor-name { display: flex; align-items: center; gap: 10px; text-align: right;}
        .creditor-avatar { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, var(--navy), var(--navy-mid)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.85rem; flex-shrink: 0; }
        .creditor-text strong { display: block; font-weight: 800; color: var(--navy); font-size: 0.9rem; }
        
        .badge-status { padding: 5px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; }
        .badge-active   { background: #fef2f2; color: var(--danger); }
        .badge-cleared  { background: var(--success-bg); color: var(--success); }

        .amount-val { font-family: 'Tajawal', sans-serif; font-weight: 900; font-size: 1rem;}
        .amount-remaining { color: var(--danger); font-size: 1.1rem; }
        .amount-paid { color: var(--success); }
        
        .debt-progress { margin-top: 4px; }
        .debt-progress-bar { height: 5px; border-radius: 10px; background: #e2e8f0; overflow: hidden; }
        .debt-progress-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg, var(--success), #34d399); transition: width 0.6s ease; }
        .debt-progress small { font-size: 0.7rem; color: var(--text-mid); font-weight: 800; }

        /* MODALS */
        .modal-content { border-radius: 20px; border: none; overflow: hidden; }
        .modal-hd-navy { background: linear-gradient(135deg, var(--navy), var(--navy-mid)); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .details-header { background: linear-gradient(135deg, #f8fafc, #eff6ff); border-bottom: 1.5px solid var(--border); padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        
        .btn-action-pay { background: var(--navy); color: white; border: none; border-radius: 8px; padding: 6px 14px; font-size: 0.78rem; font-weight: 800; cursor: pointer; transition: 0.2s; white-space: nowrap; position: relative; z-index: 2; }
        .btn-action-pay:hover { background: var(--gold); transform: translateY(-1px); }

        /* ══════════════ شريط التنقل (Pagination) — تصميم شيك ══════════════ */
        .pagination-wrap { display: flex; justify-content: center; padding: 18px 8px 6px; border-top: 1px solid var(--border); margin-top: 4px; }
        .pagination-wrap nav { width: 100%; display: flex; justify-content: center; }
        .pagination-wrap .pagination {
            display: inline-flex; gap: 6px; padding: 0; margin: 0; list-style: none;
            background: var(--surface); border-radius: 50px; box-shadow: var(--shadow); padding: 6px 8px;
            border: 1px solid var(--border);
        }
        .pagination-wrap .page-item { list-style: none; }
        .pagination-wrap .page-item .page-link,
        .pagination-wrap .page-item span {
            min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
            padding: 0 12px; border-radius: 50%; font-weight: 800; font-size: 0.85rem;
            color: var(--text-mid); background: transparent; border: none; transition: 0.18s;
            text-decoration: none; cursor: pointer;
        }
        .pagination-wrap .page-item .page-link:hover { background: var(--gold-dim); color: var(--gold); transform: translateY(-1px); }
        .pagination-wrap .page-item.active .page-link,
        .pagination-wrap .page-item.active span {
            background: linear-gradient(135deg, var(--navy), var(--navy-mid));
            color: white; box-shadow: 0 4px 12px rgba(15,34,64,0.25);
        }
        .pagination-wrap .page-item.disabled .page-link,
        .pagination-wrap .page-item.disabled span { color: var(--text-light); cursor: not-allowed; opacity: 0.5; }

        /* ── Mobile ── */
        @media (max-width: 991px) {
            .main-content { margin-right: 0 !important; width: 100% !important; padding: 70px 16px 30px !important; }
        }
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .page-title { font-size: 1.2rem; }

            /* تابات التنقل: تمرير أفقي */
            .tabs-header { overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; flex-wrap: nowrap; gap: 4px; padding-bottom: 2px; }
            .tab-btn { flex-shrink: 0; padding: 9px 14px; font-size: 0.78rem; }

            /* stat cards */
            .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 14px; gap: 10px; }
            .stat-icon { width: 40px; height: 40px; font-size: 1rem; }
            .stat-info h3 { font-size: 1.1rem; }
            .stat-info p { font-size: 0.68rem; }

            /* filter search full width */
            .filter-search-wrap { min-width: 100%; max-width: 100%; }
            .time-filter-cards { justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; }
            .time-card, .time-card-input { flex-shrink: 0; }

            /* modal header: يتفصل لصفين */
            .details-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .details-header .d-flex.gap-2 { flex-wrap: wrap; gap: 6px !important; }
            .modal-hd-navy { flex-direction: column; align-items: flex-start; gap: 8px; }
            .modal-hd-navy > div:last-child { display: flex; flex-wrap: wrap; gap: 6px; width: 100%; }

            /* table container */
            .table-container { padding: 12px 8px; }
        }
        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr; }
            .table-container { padding: 12px 8px; }
        }
    </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    @if(session('success')) <div class="alert alert-success fw-bold"><i class="fa fa-circle-check me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger fw-bold"><i class="fa fa-triangle-exclamation me-2"></i>{{ session('error') }}</div> @endif

    <div class="page-header">
        <div>
            <h2 class="page-title"><span><i class="fa fa-building-columns me-2"></i>ديون الموردين والمحطات</span></h2>
            <p class="page-subtitle">المبالغ المستحقة للجهات الدائنة والاستقطاعات</p>
        </div>
        
        {{-- 💡 تصميم كروت فلتر التاريخ الجديد (Pills) — الافتراضي: الكل --}}
        @php $tf = request('time_filter', 'all'); @endphp
        <div class="time-filter-cards">
            <span class="fw-bold px-2 text-navy small"><i class="fa fa-calendar-day me-1"></i> عرض السجلات:</span>
            <a href="?time_filter=all"       class="time-card {{ $tf == 'all'       ? 'active' : '' }}">الكل</a>
            <a href="?time_filter=today"     class="time-card {{ $tf == 'today'     ? 'active' : '' }}">اليوم</a>
            <a href="?time_filter=yesterday" class="time-card {{ $tf == 'yesterday' ? 'active' : '' }}">أمس</a>
            <a href="?time_filter=month"     class="time-card {{ $tf == 'month'     ? 'active' : '' }}">الشهر</a>
            <a href="?time_filter=year"      class="time-card {{ $tf == 'year'      ? 'active' : '' }}">السنة</a>

            <form method="GET" class="m-0 d-inline-block position-relative">
                <input type="hidden" name="time_filter" value="custom">
                <input type="date" name="custom_date" value="{{ request('custom_date') }}" class="time-card-input" onchange="this.form.submit()" title="اختر يوماً محدداً">
            </form>
        </div>
    </div>

    @php
        $cleared_count = $cleared_creditors_count ?? 0;
    @endphp

    <div class="stats-row">
        <div class="stat-card sc-gold">
            <div class="stat-icon"><i class="fa fa-scale-unbalanced"></i></div>
            <div class="stat-info">
                <p>إجمالي الديون المتبقية</p>
                <h3>{{ number_format($total_debts_on_us, 0) }} ج</h3>
            </div>
        </div>
        <div class="stat-card sc-navy">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
            <div class="stat-info">
                <p>الجهات الدائنة النشطة</p>
                <h3>{{ $active_creditors_count }} جهة</h3>
            </div>
        </div>
        <div class="stat-card sc-green">
            <div class="stat-icon"><i class="fa fa-circle-check"></i></div>
            <div class="stat-info">
                <p>ديون مسددة بالكامل</p>
                <h3>{{ $cleared_count }} جهة</h3>
            </div>
        </div>
    </div>

    <form method="GET" class="filter-section" id="filterForm">
        {{-- نحافظ على فلتر الوقت ضمن الـ form لأنه يأتي من خارجه --}}
        <input type="hidden" name="time_filter" value="{{ $timeFilter }}">
        @if(!empty($customDate))
            <input type="hidden" name="custom_date" value="{{ $customDate }}">
        @endif

        <span class="filter-label"><i class="fa fa-filter me-1"></i>تصفية:</span>
        @php $cat = request('category', ''); @endphp
        <div class="filter-pills">
            <a href="{{ url('/debts2') }}?time_filter={{ $timeFilter }}{{ $customDate ? '&custom_date='.$customDate : '' }}"
               class="filter-pill {{ $cat === '' ? 'active' : '' }}">الكل</a>
            <a href="{{ url('/debts2') }}?time_filter={{ $timeFilter }}{{ $customDate ? '&custom_date='.$customDate : '' }}&category=وقود"
               class="filter-pill {{ $cat === 'وقود' ? 'active' : '' }}">محطات البنزين</a>
            <a href="{{ url('/debts2') }}?time_filter={{ $timeFilter }}{{ $customDate ? '&custom_date='.$customDate : '' }}&category=مورد"
               class="filter-pill {{ $cat === 'مورد' ? 'active' : '' }}">الموردين</a>
            <a href="{{ url('/debts2') }}?time_filter={{ $timeFilter }}{{ $customDate ? '&custom_date='.$customDate : '' }}&category=استقطاعات"
               class="filter-pill {{ $cat === 'استقطاعات' ? 'active' : '' }}">الاستقطاعات والتبرعات</a>
            <a href="{{ url('/debts2') }}?time_filter={{ $timeFilter }}{{ $customDate ? '&custom_date='.$customDate : '' }}&category=عمولات"
               class="filter-pill {{ $cat === 'عمولات' ? 'active' : '' }}">💰 عمولات البيع</a>
        </div>

        <div class="filter-search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="ابحث باسم المورد... (Enter للبحث)">
            @if(!empty($cat))<input type="hidden" name="category" value="{{ $cat }}">@endif
        </div>
    </form>

    <div class="tabs-header">
        <button class="tab-btn active" data-tab="active-content"><i class="fa fa-clock me-1"></i> ديون واستقطاعات نشطة <span class="tab-count">{{ $active_creditors_count }}</span></button>
        <button class="tab-btn" data-tab="cleared-content"><i class="fa fa-check-double me-1"></i> مسددة بالكامل <span class="tab-count">{{ $cleared_count }}</span></button>
        <button class="tab-btn" data-tab="earned-content"><i class="fa fa-gift me-1"></i> الخصومات المكتسبة <span class="tab-count">{{ ($earnedByCreditor ?? collect())->count() }}</span></button>
    </div>

    <div class="table-container">
        {{-- 1. النشطة --}}
        <div class="tab-pane show" id="active-content">
            <div class="table-responsive">
                <table class="table" id="activeTable">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th class="text-start">اسم الجهة / البند</th>
                            <th>عدد العمليات</th>
                            <th>إجمالي المستحق</th>
                            <th>ما تم سداده</th>
                            <th>المتبقي (الصافي)</th>
                            <th>نسبة السداد</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activePaginated as $row)
                            @php
                                $creditorName   = $row->creditor_name;
                                $totalAmount    = (float) $row->total_amount;
                                $totalPaid      = (float) $row->paid_amount;
                                $totalRemaining = (float) $row->remaining_balance;
                                $paidPct        = $totalAmount > 0 ? round(($totalPaid/$totalAmount)*100) : 0;
                                $firstChar      = mb_substr($creditorName, 0, 1);
                                $categoryStr    = $row->category ?? '';
                                $modalId        = 'mdl_' . md5($creditorName);
                                $rowNum         = ($activePaginated->firstItem() ?? 0) + $loop->index;
                            @endphp
                            <tr class="clickable-row creditor-row" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" data-creditor="{{ $creditorName }}" data-cat="{{ $categoryStr }}">
                                <td><span class="row-num">{{ $rowNum }}</span></td>
                                <td>
                                    <div class="creditor-name">
                                        <div class="creditor-avatar">{{ $firstChar }}</div>
                                        <div class="creditor-text">
                                            <strong>{{ Str::limit($creditorName, 30) }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="fw-bold fs-6 badge bg-light text-dark border">{{ $row->ops_count }} عملية</span></td>
                                <td><span class="amount-val">{{ number_format($totalAmount, 0) }} ج</span></td>
                                <td><span class="amount-val amount-paid">{{ number_format($totalPaid, 0) }} ج</span></td>
                                <td><span class="amount-val amount-remaining">{{ number_format($totalRemaining, 0) }} ج</span></td>
                                <td style="min-width:110px;">
                                    <div class="debt-progress">
                                        <div class="debt-progress-bar"><div class="debt-progress-fill" style="width:{{ $paidPct }}%"></div></div>
                                        <small>{{ $paidPct }}% مسدد</small>
                                    </div>
                                </td>
                                <td><span class="badge-status badge-active">نشط</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="empty-state text-center py-5"><i class="fa fa-folder-open fs-1 text-muted mb-3 d-block"></i><p class="fw-bold">لا توجد ديون مسجلة للفترة المحددة</p></div></td></tr>
                        @endforelse
                    </tbody>
                    @if($activePaginated->total() > 0)
                    <tfoot>
                        <tr style="background:linear-gradient(135deg,var(--navy),var(--navy-mid));color:white;font-weight:900;">
                            <td colspan="2" style="padding:13px 16px;text-align:right;font-size:0.88rem;border-radius:0 0 12px 0;">
                                <i class="fa fa-sigma me-1"></i> إجمالي كل النشطة ({{ $activePaginated->total() }} جهة)
                            </td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.88rem;" colspan="3">صفحة {{ $activePaginated->currentPage() }} من {{ $activePaginated->lastPage() }}</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.9rem;color:#f87171;">{{ number_format($total_debts_on_us, 0) }} ج</td>
                            <td colspan="2" style="padding:13px 10px;text-align:center;border-radius:0 0 0 12px;"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @if($activePaginated->hasPages())
                <div class="pagination-wrap">
                    {{ $activePaginated->links() }}
                </div>
            @endif
        </div>

        {{-- 2. المسددة --}}
        <div class="tab-pane" id="cleared-content">
            <div class="table-responsive">
                <table class="table" id="clearedTable">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th class="text-start">اسم الجهة / البند</th>
                            <th>عدد العمليات</th>
                            <th>إجمالي المبالغ</th>
                            <th>حالة السداد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clearedPaginated as $row)
                            @php
                                $creditorName = $row->creditor_name;
                                $totalAmount  = (float) $row->total_amount;
                                $firstChar    = mb_substr($creditorName, 0, 1);
                                $categoryStr  = $row->category ?? '';
                                $modalId      = 'mdl_' . md5($creditorName);
                                $rowNum       = ($clearedPaginated->firstItem() ?? 0) + $loop->index;
                            @endphp
                            <tr class="clickable-row creditor-row" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" data-creditor="{{ $creditorName }}" data-cat="{{ $categoryStr }}">
                                <td><span class="row-num">{{ $rowNum }}</span></td>
                                <td>
                                    <div class="creditor-name">
                                        <div class="creditor-avatar" style="background:#059669;">{{ $firstChar }}</div>
                                        <div class="creditor-text"><strong>{{ Str::limit($creditorName, 30) }}</strong></div>
                                    </div>
                                </td>
                                <td><span class="fw-bold fs-6 badge bg-light text-dark border">{{ $row->ops_count }} عملية</span></td>
                                <td><span class="amount-val text-dark">{{ number_format($totalAmount, 0) }} ج</span></td>
                                <td><span class="badge-status badge-cleared"><i class="fa fa-check-circle"></i>خالص</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state text-center py-5"><i class="fa fa-folder-open fs-1 text-muted mb-3 d-block"></i><p class="fw-bold">لا توجد جهات مسددة بالكامل</p></div></td></tr>
                        @endforelse
                    </tbody>
                    @if($clearedPaginated->total() > 0)
                    <tfoot>
                        <tr style="background:linear-gradient(135deg,#065f46,#10b981);color:white;font-weight:900;">
                            <td colspan="2" style="padding:13px 16px;text-align:right;font-size:0.88rem;border-radius:0 0 12px 0;">
                                <i class="fa fa-sigma me-1"></i> إجمالي المسدد ({{ $clearedPaginated->total() }} جهة)
                            </td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.88rem;">صفحة {{ $clearedPaginated->currentPage() }} من {{ $clearedPaginated->lastPage() }}</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.9rem;color:#fbbf24;"></td>
                            <td style="padding:13px 10px;text-align:center;border-radius:0 0 0 12px;"><i class="fa fa-check-circle me-1"></i> خالص بالكامل</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @if($clearedPaginated->hasPages())
                <div class="pagination-wrap">
                    {{ $clearedPaginated->links() }}
                </div>
            @endif
        </div>

        {{-- 3. الخصومات المكتسبة --}}
        <div class="tab-pane" id="earned-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="fw-bold mb-1 text-navy"><i class="fa fa-gift me-1 text-success"></i> الخصومات المكتسبة من الموردين والمحطات</h6>
                    <p class="mb-0 text-muted small">المبالغ اللي الجهات سامحتنا بيها عند التقفيل — كل خصم بيزوّد رأس المال تلقائياً</p>
                </div>
                <div class="p-3 rounded-3 text-center" style="background:#ecfdf5; border:2px dashed #10b981; min-width:180px;">
                    <div class="text-muted small fw-bold">إجمالي الخصومات المكتسبة</div>
                    <h3 class="fw-black text-success m-0">{{ number_format($earnedDiscountTotal ?? 0, 0) }} ج</h3>
                </div>
            </div>

            {{-- إجمالي لكل جهة --}}
            <div class="table-responsive mb-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th class="text-start">الجهة</th>
                            <th>عدد المرات</th>
                            <th>إجمالي الخصم المكتسب</th>
                            <th>آخر خصم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($earnedByCreditor ?? collect()) as $i => $row)
                            <tr>
                                <td><span class="row-num">{{ $i + 1 }}</span></td>
                                <td class="text-start fw-bold text-navy">{{ $row->person_name ?: 'غير محدد' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $row->ops_count }}</span></td>
                                <td><span class="amount-val text-success">{{ number_format($row->total_discount, 0) }} ج</span></td>
                                <td class="text-muted small">{{ $row->last_at ? \Carbon\Carbon::parse($row->last_at)->format('Y-m-d') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state text-center py-5"><i class="fa fa-gift fs-1 text-muted mb-3 d-block"></i><p class="fw-bold">لا توجد خصومات مكتسبة مسجلة بعد</p><p class="text-muted small">الخصم المكتسب بيتسجل تلقائياً لما تكتب قيمة خصم عند سداد دين جهة</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- آخر الحركات التفصيلية --}}
            @if(($earnedDiscountRows ?? collect())->isNotEmpty())
                <h6 class="fw-bold mb-2 text-muted small"><i class="fa fa-list me-1"></i> آخر حركات الخصم المكتسب</h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th class="text-start">الجهة</th>
                                <th>قيمة الخصم</th>
                                <th class="text-start">ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($earnedDiscountRows as $r)
                                <tr>
                                    <td class="text-muted small">{{ \Carbon\Carbon::parse($r->created_at)->format('Y-m-d') }}</td>
                                    <td class="text-start fw-bold">{{ $r->person_name ?: '—' }}</td>
                                    <td><span class="text-success fw-bold">{{ number_format($r->amount, 0) }} ج</span></td>
                                    <td class="text-start text-muted small">{{ $r->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════
         نوافذ تفاصيل كل مورد / محطة
         ══════════════════════════════════════ --}}
    @foreach($groupedCompanyDebts as $creditorName => $debts)
        @php 
            $totalRemaining = $debts->sum('remaining_balance'); 
            $modalId        = 'mdl_' . md5($creditorName);
        @endphp
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow-lg">
       <div class="details-header">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="fa fa-file-invoice-dollar me-2 text-warning"></i> كشف العمليات لـ: {{ $creditorName }}</h5>
                            <p class="mb-0 text-muted small">إجمالي المتبقي للجهة: <span class="fw-bold {{ $totalRemaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($totalRemaining, 2) }} ج</span></p>
                        </div>
                        <div class="d-flex gap-2">
                            {{-- 💡 زر الطباعة الجديد --}}
                            <button class="btn btn-outline-dark fw-bold rounded-pill px-3 shadow-sm" onclick="printCreditorStatement('{{ addslashes($creditorName) }}', '{{ $modalId }}')"><i class="fa fa-print me-1"></i> طباعة الكشف</button>
                            
                            @if($totalRemaining > 0)
                                <button class="btn btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#payPartialModal"
                                        data-creditor="{{ $creditorName }}"
                                        data-total="{{ $totalRemaining }}">
                                    <i class="fa fa-hand-holding-dollar me-1"></i> سداد جزئي
                                </button>
                                <button class="btn btn-success fw-bold rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#payBulkModal" data-creditor="{{ $creditorName }}" data-total="{{ $totalRemaining }}"><i class="fa fa-money-bills me-1"></i> سداد كلي للجهة</button>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <div class="alert alert-info fw-bold mb-3 border-info">
                            <i class="fa fa-hand-pointer me-1"></i> اضغط على أي صف لعرض تفاصيل العملية (الكميات، الأصناف، الأسباب) بالكامل.
                        </div>
                        <div class="table-responsive bg-white rounded-3 shadow-sm border">
                            <table class="table table-hover text-center mb-0">
                                <thead style="background:#f1f5f9;">
                                    <tr>
                                        <th>التاريخ</th>
                                        <th class="text-start">البيان المبدئي للعملية</th>
                                        <th>تكلفة العملية</th>
                                        <th>المتبقي للدفع</th>
                                        <th>إجراءات الدفع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($debts as $cd)
                                        @php 
                                            $isRowCleared = $cd->remaining_balance <= 0; 
                                            $shortReason = Str::limit(str_replace('|', ' - ', $cd->reason), 55);
                                            
                                   // 💡 1. بناء الـ HTML الخاص بتفاصيل العملية (بيحلل الـ reason ويوضح الحسبة)
                                            $parts = array_filter(array_map('trim', explode('|', $cd->reason)));
                                            $detailsHtml = "<div class='text-start fw-bold text-dark' style='font-family: Cairo; line-height: 1.8; font-size: 14px;'>";
                                            $detailsHtml .= "<div class='bg-light p-3 rounded-3 mb-3 border'>";
                                            foreach($parts as $text) {
                                                if($text !== '') {
                                                    $icon = 'fa-check text-success';
                                                    if(str_contains($text, 'لتر') || str_contains($text, 'بنزينة') || str_contains($text, 'وقود')) $icon = 'fa-gas-pump text-danger';
                                                    elseif(str_contains($text, 'عهدة') || str_contains($text, 'نقدية')) $icon = 'fa-money-bill-wave text-success';
                                                    elseif(str_contains($text, 'ربح') || str_contains($text, 'مكسب') || str_contains($text, 'صافي')) $icon = 'fa-chart-line text-primary';
                                                    elseif(str_contains($text, 'سائق') || str_contains($text, 'سيارة')) $icon = 'fa-truck text-warning';
                                                    elseif(str_contains($text, 'شركة')) $icon = 'fa-building text-info';
                                                    elseif(str_contains($text, 'استقطاع') || str_contains($text, 'تبرع')) $icon = 'fa-scissors text-danger';
                                                    elseif(str_contains($text, 'أصناف') || str_contains($text, 'بضاعة') || str_contains($text, 'مشتريات')) $icon = 'fa-boxes-packing text-primary';
                                                    
                                                    $detailsHtml .= "<div class='mb-2 pb-2 border-bottom border-secondary-subtle'><i class='fa {$icon} me-2' style='width:20px;text-align:center;'></i> {$text}</div>";
                                                }
                                            }
                                            $detailsHtml .= "</div>";
                                            
                                            // 💡 تفاصيل المبالغ بالحسابات الدقيقة
                                            $detailsHtml .= "<div class='bg-white p-3 rounded-3 border shadow-sm'>";
                                            $detailsHtml .= "<div class='d-flex justify-content-between mb-2'><span class='text-muted'>إجمالي تكلفة العملية:</span><span class='text-dark fw-black fs-5'>" . number_format($cd->total_amount, 0) . " ج</span></div>";
                                            $detailsHtml .= "<div class='d-flex justify-content-between mb-2'><span class='text-muted'>ما تم دفعه وقتها (مقدم):</span><span class='text-success fw-black fs-5'>" . number_format($cd->paid_amount, 0) . " ج</span></div>";
                                            $detailsHtml .= "<div class='d-flex justify-content-between mb-2 pb-2 border-bottom'><span class='text-muted'>المتبقي كمديونية:</span><span class='text-danger fw-black fs-5'>" . number_format($cd->remaining_balance, 0) . " ج</span></div>";
                                            $detailsHtml .= "<div class='d-flex justify-content-between mt-2'><span class='text-muted'>تاريخ وتوقيت العملية:</span><span class='text-primary' dir='ltr'>" . \Carbon\Carbon::parse($cd->created_at)->format('Y-m-d h:i A') . "</span></div>";
                                            $detailsHtml .= "</div></div>";
                                        @endphp
                                        
                                        {{-- 💡 2. إخفاء الـ HTML في ديف لمنع ضرب الكود --}}
                                        <div id="op_details_{{ $cd->id }}" class="d-none">{!! $detailsHtml !!}</div>
                                        
                                        {{-- 💡 3. السطر نفسه بيفتح التفاصيل بمجرد الضغط عليه --}}
                                        <tr class="inner-clickable-row {{ $isRowCleared ? 'opacity-75' : '' }}" 
                                            onclick="showOperationDetails('op_details_{{ $cd->id }}')">
                                            
                                            <td class="text-muted fw-bold">{{ \Carbon\Carbon::parse($cd->created_at)->format('Y/m/d') }}</td>
                                            <td class="text-start fw-bold text-dark"><i class="fa fa-chevron-circle-left text-primary me-2 opacity-50"></i>{{ $shortReason }}</td>
                                            <td class="fw-black text-dark">{{ number_format($cd->total_amount, 0) }} ج</td>
                                            <td class="{{ $isRowCleared ? 'text-success' : 'text-danger' }} fw-black">{{ number_format($cd->remaining_balance, 0) }} ج</td>
                                            
                                            <td>
                                                @if(!$isRowCleared)
                                                    {{-- 💡 حطينا event.stopPropagation() عشان الدوسة هنا متفتحش التفاصيل وتعمل تداخل --}}
                                                    <button class="btn-action-pay" data-bs-toggle="modal" data-bs-target="#payDebtOnUsModal" data-id="{{ $cd->id }}" data-rem="{{ $cd->remaining_balance }}" onclick="event.stopPropagation();"><i class="fa fa-hand-holding-dollar me-1"></i> سداد جزء</button>
                                                @else
                                                    <span class="badge bg-success"><i class="fa fa-check"></i> تم السداد</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>

{{-- ══════════════════════════════
     Modal: سداد دفعة (فردي)
     ══════════════════════════════ --}}
<div class="modal fade" id="payDebtOnUsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-hd-navy">
                <div>
                    <h6 class="mb-1"><i class="fa fa-hand-holding-dollar me-2"></i>سداد دفعة نقدية</h6>
                    <small style="opacity:.75;font-size:.75rem;">المبلغ المتبقي للعملية: <span id="pay_rem_display" class="fw-bold">0</span> ج</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('company_debts.pay') }}" method="POST" id="payDebtForm" novalidate>
                @csrf
                <input type="hidden" name="debt_id" id="modal_on_us_id">

                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">مبلغ الدفعة (ج.م)</label>
                        <input type="number" name="amount" id="modal_on_us_amount" class="form-control form-control-lg text-center fw-bold border-primary text-primary" step="1" min="1" required autocomplete="off" placeholder="0">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark">اختر الخزنة للسداد</label>
                        <select name="account_id" class="form-select border-dark fw-bold" required>
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} | متاح: {{ number_format($acc->balance, 0) }} ج</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 p-3 pt-0 bg-light">
                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill fs-5 shadow-sm">اعتماد ودفع</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════
     💡 Modal: سداد مجمع (كلي) (المبلغ مقفول Built-in)
     ══════════════════════════════ --}}
<div class="modal fade" id="payBulkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content">
            <div class="modal-hd-navy bg-success">
                <div>
                    <h5 class="text-white m-0"><i class="fa fa-money-bills me-2"></i>سداد مجمع للجهة</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('company_debts.pay_bulk') }}" method="POST" id="payBulkForm">
                @csrf
                <input type="hidden" name="creditor_name" id="modal_bulk_creditor">
                <input type="hidden" name="amount" id="bulk_amount_hidden">

                <div class="modal-body p-4 text-center bg-light">
                    <h6 class="fw-bold text-muted mb-2">إجمالي المبلغ المطلوب سداده دفعة واحدة</h6>

                    {{-- 💡 المبلغ ظاهر هنا كنص فقط ولا يمكن تعديله --}}
                    <div class="p-3 mb-3 rounded-3" style="background:#ecfdf5; border: 2px dashed #10b981;">
                        <h2 class="fw-black text-success m-0"><span id="modal_bulk_total_text">0</span> ج</h2>
                    </div>

                    {{-- 💡 خصم مكتسب (اختياري): يقلّل الكاش المسحوب ويقفّل الدين بالكامل --}}
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold text-success">
                            <i class="fa fa-gift me-1"></i> خصم مكتسب (اختياري)
                        </label>
                        <input type="number" name="earned_discount" id="modal_bulk_discount" step="1" min="0" value="0"
                               class="form-control text-center fw-bold border-success text-success"
                               autocomplete="off" placeholder="0" oninput="updateBulkHint()">
                        <div id="bulk_hint" class="mt-2 fw-bold small text-dark" style="display:none;"></div>
                    </div>

                    <label class="form-label fw-bold text-start w-100 text-dark">سحب الأموال من خزنة:</label>
                    <select name="account_id" class="form-select border-success fw-bold" required>
                        <option value="" disabled selected>اختر الخزنة...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} | متاح: {{ number_format($acc->balance, 0) }} ج</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer border-0 p-3 pt-0 bg-light">
                    <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill fs-5 shadow-sm">تأكيد السداد المجمع للجهة</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════
     💡 Modal: سداد جزئي للجهة (مبلغ يتوزع FIFO على الديون)
     ══════════════════════════════ --}}
<div class="modal fade" id="payPartialModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:450px;">
        <div class="modal-content">
            <div class="modal-hd-navy" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                <div>
                    <h5 class="text-white m-0"><i class="fa fa-hand-holding-dollar me-2"></i>سداد جزئي للجهة</h5>
                    <small class="text-white" style="opacity:.85;font-size:.72rem;">يُوزَّع المبلغ على الديون من الأقدم للأحدث</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('company_debts.pay_partial') }}" method="POST" id="payPartialForm">
                @csrf
                <input type="hidden" name="creditor_name" id="modal_partial_creditor">

                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-warning border-warning mb-3 fw-bold small" style="font-size:.78rem;">
                        <i class="fa fa-circle-info me-1"></i>
                        إجمالي المتبقي على الجهة: <span id="modal_partial_total_text" class="fw-black">0</span> ج
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">مبلغ السداد الجزئي (ج.م)</label>
                        <input type="number" name="amount" id="modal_partial_amount" step="1" min="0"
                               class="form-control form-control-lg text-center fw-black border-warning text-warning"
                               required autocomplete="off" placeholder="0" oninput="updatePartialHint()">
                        <small class="text-muted">سيُخصم من الديون بالترتيب: الأقدم أولاً، حتى ينتهي المبلغ</small>
                    </div>

                    {{-- 💡 خصم مكتسب (اختياري): يقفّل جزء من الدين بدون دفع نقدي --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">
                            <i class="fa fa-gift me-1"></i> خصم مكتسب (اختياري)
                        </label>
                        <input type="number" name="earned_discount" id="modal_partial_discount" step="1" min="0" value="0"
                               class="form-control text-center fw-bold border-success text-success"
                               autocomplete="off" placeholder="0" oninput="updatePartialHint()">
                        <small class="text-muted">المبلغ اللي البنزينة سامحانا بيه عند التقفيل — بيقفّل من الدين بدون ما يخرج من الخزنة</small>
                        <div id="partial_hint" class="mt-2 fw-bold small text-dark" style="display:none;"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark">سحب الأموال من خزنة</label>
                        <select name="account_id" class="form-select border-dark fw-bold" required>
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} | متاح: {{ number_format($acc->balance, 0) }} ج</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0 bg-light">
                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold rounded-pill fs-6 shadow-sm">
                        <i class="fa fa-check-circle me-1"></i> اعتماد ودفع السداد الجزئي
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── تشغيل التابات (مع حفظ آخر تاب في sessionStorage عشان يفضل ثابت بعد reload الترقيم) ──
(function() {
    const STORAGE_KEY = 'debts2_active_tab';
    const stored = sessionStorage.getItem(STORAGE_KEY);

    // لو في URL ?cleared_page=X — يعني المستخدم في تاب المسددة
    const urlParams = new URLSearchParams(window.location.search);
    let initialTab = 'active-content';
    if (urlParams.has('cleared_page') && !urlParams.has('active_page')) {
        initialTab = 'cleared-content';
    } else if (stored === 'cleared-content' && !urlParams.has('active_page')) {
        initialTab = 'cleared-content';
    }

    function activate(tabId) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tabId));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.toggle('show', p.id === tabId));
        sessionStorage.setItem(STORAGE_KEY, tabId);
    }

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => activate(btn.dataset.tab));
    });

    activate(initialTab);
})();

// 💡 دالة بوب-أب التفاصيل السحرية (بتقرأ من الديف المخفي)
function showOperationDetails(containerId) {
    const htmlContent = document.getElementById(containerId).innerHTML;
    Swal.fire({
        title: '<i class="fa fa-file-invoice text-navy me-2"></i> تفاصيل العملية كاملة',
        html: htmlContent,
        confirmButtonText: 'إغلاق نافذة التفاصيل',
        confirmButtonColor: '#0f2240',
        width: '500px'
    });
}

// تمرير الداتا لمودال السداد الجزئي (للجهة الكاملة بمبلغ مخصص)
document.getElementById('payPartialModal').addEventListener('show.bs.modal', function(event) {
    let button = event.relatedTarget;
    let total  = parseFloat(button.getAttribute('data-total')) || 0;
    document.getElementById('modal_partial_creditor').value = button.getAttribute('data-creditor');
    document.getElementById('modal_partial_total_text').innerText = Math.round(total).toLocaleString('en-US');
    let amountEl = document.getElementById('modal_partial_amount');
    amountEl.value = '';
    amountEl.setAttribute('max', total);
    document.getElementById('modal_partial_discount').value = 0;
    updatePartialHint();
});

// تلميح حي: الكاش + الخصم مقابل المتبقي
function updatePartialHint() {
    let cash  = parseFloat(document.getElementById('modal_partial_amount').value) || 0;
    let disc  = parseFloat(document.getElementById('modal_partial_discount').value) || 0;
    let total = parseFloat(document.getElementById('modal_partial_amount').getAttribute('max')) || 0;
    let sum   = cash + disc;
    let box   = document.getElementById('partial_hint');
    if (disc <= 0 && cash <= 0) { box.style.display = 'none'; return; }
    box.style.display = 'block';
    let f = n => Math.round(n).toLocaleString('en-US');
    if (sum > total + 0.01) {
        box.innerHTML = '<span class="text-danger">⚠️ المدفوع + الخصم (' + f(sum) + ' ج) أكبر من المتبقي (' + f(total) + ' ج)</span>';
    } else {
        let after = total - sum;
        box.innerHTML = '💵 كاش: <b>' + f(cash) + '</b> + 🎁 خصم: <b class="text-success">' + f(disc) + '</b> → سيتبقى على الجهة: <b>' + f(after) + ' ج</b>';
    }
}

// تحقق فوري إن (المدفوع + الخصم) مش أكبر من المتبقي
document.getElementById('payPartialForm').addEventListener('submit', function(e) {
    let amount = parseFloat(document.getElementById('modal_partial_amount').value) || 0;
    let disc   = parseFloat(document.getElementById('modal_partial_discount').value) || 0;
    let max    = parseFloat(document.getElementById('modal_partial_amount').getAttribute('max')) || 0;
    let sum    = amount + disc;
    if (sum <= 0) { e.preventDefault(); alert('من فضلك أدخل مبلغ سداد أو خصم مكتسب.'); return false; }
    if (sum > max + 0.01) {
        e.preventDefault();
        alert('المدفوع + الخصم (' + Math.round(sum).toLocaleString('en-US') + ' ج) أكبر من إجمالي المتبقي على الجهة (' + Math.round(max).toLocaleString('en-US') + ' ج)');
        return false;
    }
});

// تمرير الداتا لمودال السداد الفردي والمجمع
document.getElementById('payDebtOnUsModal').addEventListener('show.bs.modal', function(event) {
    let button = event.relatedTarget;
    document.getElementById('modal_on_us_id').value = button.getAttribute('data-id');
    document.getElementById('pay_rem_display').innerText = button.getAttribute('data-rem');
    document.getElementById('modal_on_us_amount').value = button.getAttribute('data-rem');
});

// المودال الكلي: المبلغ مقفول، لكن يقبل خصم مكتسب اختياري
document.getElementById('payBulkModal').addEventListener('show.bs.modal', function(event) {
    let button = event.relatedTarget;
    let total  = parseFloat(button.getAttribute('data-total')) || 0;
    document.getElementById('modal_bulk_creditor').value = button.getAttribute('data-creditor');
    document.getElementById('bulk_amount_hidden').value = total;
    document.getElementById('modal_bulk_total_text').innerText = Math.round(total).toLocaleString('en-US');
    let discEl = document.getElementById('modal_bulk_discount');
    discEl.value = 0;
    discEl.setAttribute('max', total);
    updateBulkHint();
});

// تلميح حي: الكاش المطلوب = المتبقي − الخصم
function updateBulkHint() {
    let total = parseFloat(document.getElementById('bulk_amount_hidden').value) || 0;
    let disc  = parseFloat(document.getElementById('modal_bulk_discount').value) || 0;
    let box   = document.getElementById('bulk_hint');
    let f = n => Math.round(n).toLocaleString('en-US');
    if (disc <= 0) { box.style.display = 'none'; return; }
    box.style.display = 'block';
    if (disc > total + 0.01) {
        box.innerHTML = '<span class="text-danger">⚠️ الخصم (' + f(disc) + ' ج) أكبر من المتبقي (' + f(total) + ' ج)</span>';
    } else {
        box.innerHTML = '💵 الكاش المسحوب من الخزنة = <b>' + f(total - disc) + ' ج</b> (بعد خصم ' + f(disc) + ' ج)';
    }
}

// تحقق: الخصم مش أكبر من المتبقي
document.getElementById('payBulkForm').addEventListener('submit', function(e) {
    let total = parseFloat(document.getElementById('bulk_amount_hidden').value) || 0;
    let disc  = parseFloat(document.getElementById('modal_bulk_discount').value) || 0;
    if (disc > total + 0.01) {
        e.preventDefault();
        alert('الخصم المكتسب (' + Math.round(disc).toLocaleString('en-US') + ' ج) أكبر من إجمالي المتبقي على الجهة (' + Math.round(total).toLocaleString('en-US') + ' ج)');
        return false;
    }
});

// 💡 دالة طباعة كشف حساب المورد/الجهة
function printCreditorStatement(creditorName, modalId) {
    const modal = document.getElementById(modalId);
    const dataRows = modal.querySelectorAll('table tbody tr.inner-clickable-row');

    if (!dataRows.length) { alert('لا توجد عمليات للطباعة'); return; }

    // ─── جمع البيانات من DOM ───
    const ops = [];
    let totalAmount = 0, totalPaid = 0, totalRem = 0;
    dataRows.forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length < 4) return;
        const date   = (cells[0].textContent || '').trim();
        const reason = (cells[1].textContent || '').trim();
        const totRaw = (cells[2].textContent || '').replace(/[^\d.-]/g, '');
        const remRaw = (cells[3].textContent || '').replace(/[^\d.-]/g, '');
        const tot = parseFloat(totRaw) || 0;
        const rem = parseFloat(remRaw) || 0;
        const paid = tot - rem;
        totalAmount += tot;
        totalPaid   += paid;
        totalRem    += rem;
        ops.push({ date, reason, tot, paid, rem });
    });

    const fmt = n => Math.round(n || 0).toLocaleString('en-US');
    const printDate = new Date().toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' });
    const printTime = new Date().toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
    const paidPct = totalAmount > 0 ? Math.round((totalPaid / totalAmount) * 100) : 0;

    const rowsHtml = ops.map((o, i) => `
        <tr>
            <td>${i + 1}</td>
            <td dir="ltr">${o.date}</td>
            <td class="text-start">${o.reason}</td>
            <td>${fmt(o.tot)} ج</td>
            <td class="num-pos">${fmt(o.paid)} ج</td>
            <td class="${o.rem > 0 ? 'num-neg' : 'num-pos'}">
                ${o.rem > 0 ? fmt(o.rem) + ' ج' : '<span class="badge-pill badge-paid">خالص</span>'}
            </td>
        </tr>
    `).join('');

    const html = `
        <!DOCTYPE html><html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>كشف مديونية — ${creditorName}</title>
            <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>
                @page { size: A4; margin: 8mm 7mm; }
                * { box-sizing: border-box; }
                body {
                    font-family: 'IBM Plex Sans Arabic', 'Cairo', 'Tahoma', sans-serif;
                    background: #fff; color: #0f172a; margin: 0; padding: 0;
                    font-feature-settings: 'tnum'; letter-spacing: -0.01em;
                    -webkit-print-color-adjust: exact; print-color-adjust: exact;
                }
                .page { max-width: 100%; margin: 0; padding: 0; }
                .doc-header {
                    display: flex; justify-content: space-between; align-items: center;
                    padding-bottom: 8px; margin-bottom: 10px; border-bottom: 2px solid #0f172a;
                }
                .doc-header .brand h1 { margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1.2; }
                .doc-header .brand p { margin: 1px 0 0; color: #5a6478; font-size: 10px; font-weight: 500; }
                .doc-header .meta { text-align: left; font-size: 10px; }
                .doc-header .meta .doc-title {
                    display: inline-block; background: #0f172a; color: #fff;
                    padding: 4px 12px; border-radius: 4px;
                    font-weight: 600; font-size: 11px; margin-bottom: 3px;
                }
                .doc-header .meta .doc-date { color: #5a6478; font-weight: 500; font-size: 10px; }

                .creditor-card {
                    background:#fafbfd; border:1px solid #e6ebf3; border-radius:6px;
                    padding:7px 12px; margin-bottom:8px;
                    display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:8px;
                }
                .creditor-card .field-label { font-size:9px; color:#5a6478; font-weight:500; margin-bottom:1px; }
                .creditor-card .field-value { font-size:12px; font-weight:700; color:#0f172a; }
                .creditor-card .v-paid { color:#059669; }
                .creditor-card .v-rem  { color:#dc2626; }

                .summary { display:grid; grid-template-columns:repeat(4, 1fr); gap:5px; margin-bottom:10px; }
                .summary .box {
                    border: 1px solid #e6ebf3; border-radius: 5px;
                    padding: 5px 9px; background: #fafbfd; position: relative;
                }
                .summary .box::before {
                    content: ''; position: absolute; top: 0; right: 0; bottom: 0;
                    width: 2px; background: #5a6478;
                }
                .summary .box.accent::before  { background: #4f46e5; }
                .summary .box.success::before { background: #059669; }
                .summary .box.danger::before  { background: #dc2626; }
                .summary .box.warning::before { background: #d97706; }
                .summary .box .label { font-size: 9px; color: #5a6478; font-weight: 500; margin-bottom: 1px; }
                .summary .box .val   { font-size: 12px; font-weight: 700; color: #0f172a; }

                .progress-wrap { margin: 4px 0 10px; }
                .progress-wrap .ptop { display:flex; justify-content:space-between; font-size:9.5px; font-weight:600; color:#5a6478; margin-bottom:2px; }
                .progress-wrap .pbar { height: 6px; background: #e6ebf3; border-radius: 999px; overflow: hidden; }
                .progress-wrap .pfill { height: 100%; background: linear-gradient(90deg, #059669, #10b981); }

                .section-title {
                    margin: 8px 0 4px; padding-bottom: 3px;
                    font-size: 11px; font-weight: 700; color: #0f172a;
                    border-bottom: 1.5px solid #e6ebf3;
                    display: flex; justify-content: space-between; align-items: center;
                }
                .section-title small { font-size: 9px; color: #5a6478; font-weight: 500; }

                table.data {
                    width: 100%; border-collapse: collapse;
                    margin-bottom: 6px; font-size: 9.5px;
                }
                table.data thead { background: #0f172a; color: #fff; display: table-header-group; }
                table.data tfoot { display: table-row-group; }
                table.data th { padding: 4px 4px; text-align: center; font-weight: 600; font-size: 9px; }
                table.data td {
                    padding: 3.5px 5px; border-bottom: 1px solid #e6ebf3;
                    text-align: center; vertical-align: middle;
                    font-weight: 500; color: #0f172a; line-height: 1.3;
                }
                table.data tr { page-break-inside: avoid; }
                table.data tr:nth-child(even) td { background: #fafbfd; }
                table.data tfoot tr { background: #f1f4f9; }
                table.data tfoot td { padding: 5px 4px; font-weight: 700; font-size: 9.5px; border-top: 1.5px solid #0f172a; }
                table.data .text-start { text-align: right !important; }
                table.data .num-pos { color: #059669; font-weight: 700; }
                table.data .num-neg { color: #dc2626; font-weight: 700; }
                .badge-pill { display:inline-block; padding:1px 6px; border-radius:999px; font-size:8.5px; font-weight:600; }
                .badge-paid { background:#ecfdf5; color:#059669; }

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

                @media print { body { background:#fff; } }
            </style>
        </head>
        <body>
            <div class="page">
                <div class="doc-header">
                    <div class="brand">
                        <h1>شركة الضبع</h1>
                        <p>للتجارة وأنظمة التقسيط والمقاولات</p>
                    </div>
                    <div class="meta">
                        <div class="doc-title">كشف مديونية</div>
                        <div class="doc-date">${printDate} — ${printTime}</div>
                    </div>
                </div>

                <div class="creditor-card">
                    <div>
                        <div class="field-label">اسم الجهة / الدائن</div>
                        <div class="field-value">${creditorName}</div>
                    </div>
                    <div>
                        <div class="field-label">عدد العمليات</div>
                        <div class="field-value" style="color:#4f46e5;">${ops.length}</div>
                    </div>
                    <div>
                        <div class="field-label">المسدد</div>
                        <div class="field-value v-paid">${fmt(totalPaid)} ج</div>
                    </div>
                    <div>
                        <div class="field-label">المتبقي</div>
                        <div class="field-value v-rem">${fmt(totalRem)} ج</div>
                    </div>
                </div>

                <div class="summary">
                    <div class="box accent">
                        <div class="label">إجمالي قيمة العمليات</div>
                        <div class="val">${fmt(totalAmount)} ج</div>
                    </div>
                    <div class="box success">
                        <div class="label">ما تم سداده</div>
                        <div class="val" style="color:#059669;">${fmt(totalPaid)} ج</div>
                    </div>
                    <div class="box danger">
                        <div class="label">المتبقي (مديونية)</div>
                        <div class="val" style="color:#dc2626;">${fmt(totalRem)} ج</div>
                    </div>
                    <div class="box warning">
                        <div class="label">نسبة السداد</div>
                        <div class="val" style="color:#d97706;">${paidPct}%</div>
                    </div>
                </div>

                <div class="progress-wrap">
                    <div class="ptop">
                        <span>تقدّم السداد</span>
                        <span>${paidPct}% مسدد · ${fmt(totalRem)} ج متبقي</span>
                    </div>
                    <div class="pbar"><div class="pfill" style="width:${paidPct}%"></div></div>
                </div>

                <div class="section-title">
                    تفاصيل العمليات <small>${ops.length} عملية</small>
                </div>
                <table class="data">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th style="width:80px;">التاريخ</th>
                            <th class="text-start">البيان</th>
                            <th style="width:90px;">القيمة</th>
                            <th style="width:90px;">المسدد</th>
                            <th style="width:90px;">المتبقي</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-start" style="text-align:right; padding-right:10px;">الإجماليات:</td>
                            <td>${fmt(totalAmount)} ج</td>
                            <td class="num-pos">${fmt(totalPaid)} ج</td>
                            <td class="num-neg">${fmt(totalRem)} ج</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="footer">
                    <div class="sign-box"><div class="line">موظف الحسابات</div></div>
                    <div class="stamp">طُبع آلياً من نظام الضبع — ${new Date().toLocaleString('ar-EG')}</div>
                    <div class="sign-box"><div class="line">المدير المالي</div></div>
                </div>
            </div>
        </body></html>
    `;

    const blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const win  = window.open(url, '_blank', 'width=1000,height=800');
    if (win) {
        win.addEventListener('load', () => setTimeout(() => { win.print(); URL.revokeObjectURL(url); }, 500));
    } else {
        URL.revokeObjectURL(url);
        alert('السماح بالنوافذ المنبثقة مطلوب لإتمام الطباعة.');
    }
}
</script>
</body>
</html>