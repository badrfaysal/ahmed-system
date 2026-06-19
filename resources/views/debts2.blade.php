<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
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
        
        {{-- 💡 تصميم كروت فلتر التاريخ الجديد (Pills) --}}
        <div class="time-filter-cards">
            <span class="fw-bold px-2 text-navy small"><i class="fa fa-calendar-day me-1"></i> عرض السجلات:</span>
            <a href="?time_filter=today" class="time-card {{ request('time_filter', 'today') == 'today' ? 'active' : '' }}">اليوم</a>
            <a href="?time_filter=yesterday" class="time-card {{ request('time_filter') == 'yesterday' ? 'active' : '' }}">أمس</a>
            <a href="?time_filter=month" class="time-card {{ request('time_filter') == 'month' ? 'active' : '' }}">الشهر</a>
            <a href="?time_filter=year" class="time-card {{ request('time_filter') == 'year' ? 'active' : '' }}">السنة</a>
            <a href="?time_filter=all" class="time-card {{ request('time_filter') == 'all' ? 'active' : '' }}">الكل</a>
            
            <form method="GET" class="m-0 d-inline-block position-relative">
                <input type="hidden" name="time_filter" value="custom">
                <input type="date" name="custom_date" value="{{ request('custom_date') }}" class="time-card-input" onchange="this.form.submit()" title="اختر يوماً محدداً">
            </form>
        </div>
    </div>

    @php
        $groupedCompanyDebts = $groupedCompanyDebts ?? collect();
        
        $activeCreditors  = $groupedCompanyDebts->filter(fn($g) => $g->sum('remaining_balance') > 0);
        $clearedCreditors = $groupedCompanyDebts->filter(fn($g) => $g->sum('remaining_balance') <= 0);
        
        $active_creditors_count = $activeCreditors->count();
        $cleared_count          = $clearedCreditors->count();
        $total_debts_on_us      = $activeCreditors->sum(fn($g) => $g->sum('remaining_balance'));
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

    <div class="filter-section">
        <span class="filter-label"><i class="fa fa-filter me-1"></i>تصفية:</span>
        <div class="filter-pills">
            <button class="filter-pill active" data-cat="">الكل</button>
            <button class="filter-pill" data-cat="وقود">محطات البنزين</button>
            <button class="filter-pill" data-cat="مورد">الموردين</button>
            <button class="filter-pill" data-cat="استقطاعات">الاستقطاعات والتبرعات</button>
            <button class="filter-pill" data-cat="عمولات">💰 عمولات البيع</button>
            {{-- <button class="filter-pill" data-cat="تركيب">🔧 تركيب</button> --}}
        </div>

        <div class="filter-search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="liveSearch" placeholder="ابحث باسم المورد أو المحطة...">
        </div>
    </div>

    <div class="tabs-header">
        <button class="tab-btn active" data-tab="active-content"><i class="fa fa-clock me-1"></i> ديون واستقطاعات نشطة <span class="tab-count">{{ $active_creditors_count }}</span></button>
        <button class="tab-btn" data-tab="cleared-content"><i class="fa fa-check-double me-1"></i> مسددة بالكامل <span class="tab-count">{{ $cleared_count }}</span></button>
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
                        @forelse($activeCreditors as $creditorName => $debts)
                            @php
                                $totalAmount    = $debts->sum('total_amount');
                                $totalPaid      = $debts->sum('paid_amount');
                                $totalRemaining = $debts->sum('remaining_balance');
                                $paidPct        = $totalAmount > 0 ? round(($totalPaid/$totalAmount)*100) : 0;
                                $firstChar      = mb_substr($creditorName, 0, 1);
                                $categoryStr    = $debts->first()->category ?? '';
                                $modalId        = 'mdl_' . md5($creditorName);
                            @endphp
                            <tr class="clickable-row creditor-row" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" data-creditor="{{ $creditorName }}" data-cat="{{ $categoryStr }}">
                                <td><span class="row-num">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div class="creditor-name">
                                        <div class="creditor-avatar">{{ $firstChar }}</div>
                                        <div class="creditor-text">
                                            <strong>{{ Str::limit($creditorName, 30) }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="fw-bold fs-6 badge bg-light text-dark border">{{ $debts->count() }} عملية</span></td>
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
                    @if($activeCreditors->count() > 0)
                    <tfoot>
                        <tr style="background:linear-gradient(135deg,var(--navy),var(--navy-mid));color:white;font-weight:900;">
                            <td colspan="2" style="padding:13px 16px;text-align:right;font-size:0.88rem;border-radius:0 0 12px 0;"><i class="fa fa-sigma me-1"></i> الإجمالي الكلي</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.88rem;">{{ $activeCreditors->sum(fn($g) => $g->count()) }} عملية</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.9rem;color:#fbbf24;">{{ number_format($activeCreditors->sum(fn($g) => $g->sum('total_amount')), 0) }} ج</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.9rem;color:#34d399;">{{ number_format($activeCreditors->sum(fn($g) => $g->sum('paid_amount')), 0) }} ج</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.9rem;color:#f87171;">{{ number_format($total_debts_on_us, 0) }} ج</td>
                            <td colspan="2" style="padding:13px 10px;text-align:center;border-radius:0 0 0 12px;"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
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
                        @forelse($clearedCreditors as $creditorName => $debts)
                            @php
                                $totalAmount    = $debts->sum('total_amount');
                                $firstChar      = mb_substr($creditorName, 0, 1);
                                $categoryStr    = $debts->first()->category ?? '';
                                $modalId        = 'mdl_' . md5($creditorName);
                            @endphp
                            <tr class="clickable-row creditor-row" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" data-creditor="{{ $creditorName }}" data-cat="{{ $categoryStr }}">
                                <td><span class="row-num">{{ $loop->iteration }}</span></td>
                                <td>
                                    <div class="creditor-name">
                                        <div class="creditor-avatar" style="background:#059669;">{{ $firstChar }}</div>
                                        <div class="creditor-text"><strong>{{ Str::limit($creditorName, 30) }}</strong></div>
                                    </div>
                                </td>
                                <td><span class="fw-bold fs-6 badge bg-light text-dark border">{{ $debts->count() }} عملية</span></td>
                                <td><span class="amount-val text-dark">{{ number_format($totalAmount, 0) }} ج</span></td>
                                <td><span class="badge-status badge-cleared"><i class="fa fa-check-circle"></i>خالص</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state text-center py-5"><i class="fa fa-folder-open fs-1 text-muted mb-3 d-block"></i><p class="fw-bold">لا توجد جهات مسددة بالكامل</p></div></td></tr>
                        @endforelse
                    </tbody>
                    @if($clearedCreditors->count() > 0)
                    <tfoot>
                        <tr style="background:linear-gradient(135deg,#065f46,#10b981);color:white;font-weight:900;">
                            <td colspan="2" style="padding:13px 16px;text-align:right;font-size:0.88rem;border-radius:0 0 12px 0;"><i class="fa fa-sigma me-1"></i> إجمالي المسدد</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.88rem;">{{ $clearedCreditors->sum(fn($g) => $g->count()) }} عملية</td>
                            <td style="padding:13px 10px;text-align:center;font-size:0.9rem;color:#fbbf24;">{{ number_format($clearedCreditors->sum(fn($g) => $g->sum('total_amount')), 0) }} ج</td>
                            <td style="padding:13px 10px;text-align:center;border-radius:0 0 0 12px;"><i class="fa fa-check-circle me-1"></i> خالص بالكامل</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
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
                    <div class="p-3 mb-4 rounded-3" style="background:#ecfdf5; border: 2px dashed #10b981;">
                        <h2 class="fw-black text-success m-0"><span id="modal_bulk_total_text">0</span> ج</h2>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// 💡 الفلتر السحري المطور (بيصطاد الموردين القدام والجداد)
let activeCat = '';
let searchVal = '';

document.querySelectorAll('.filter-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        activeCat = this.dataset.cat.toLowerCase();
        applyFilters();
    });
});

document.getElementById('liveSearch').addEventListener('input', function() {
    searchVal = this.value.trim().toLowerCase();
    applyFilters();
});

function applyFilters() {
    const activePanes = document.querySelectorAll('.tab-pane.show table tbody tr.creditor-row');
    
    activePanes.forEach(row => {
        const creditor = (row.dataset.creditor || '').toLowerCase();
        const cat      = (row.dataset.cat || '').toLowerCase();

        let matchCat = false;
        if (activeCat === '') {
            matchCat = true;
        } else if (activeCat === 'وقود') {
            matchCat = cat.includes('وقود') || cat.includes('محطة') || creditor.includes('بنزين');
        } else if (activeCat === 'استقطاعات') {
            matchCat = cat.includes('استقطاع') || cat.includes('تبرع') || creditor.includes('صندوق');
        } else if (activeCat === 'مورد') {
            matchCat = cat.includes('مورد') || cat.includes('عام') || (!cat.includes('وقود') && !cat.includes('محطة') && !cat.includes('استقطاع') && !cat.includes('تبرع') && !cat.includes('عمولات') && !cat.includes('تركيب') && !creditor.includes('بنزين'));
        } else {
            matchCat = cat.includes(activeCat);
        }

        const matchSearch = searchVal === '' || creditor.includes(searchVal);

        if (matchCat && matchSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// تشغيل التابات العلوية
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('show');
        applyFilters(); // تطبيق الفلتر على التاب الجديد
    });
});

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

// تمرير الداتا لمودال السداد الفردي والمجمع
document.getElementById('payDebtOnUsModal').addEventListener('show.bs.modal', function(event) {
    let button = event.relatedTarget;
    document.getElementById('modal_on_us_id').value = button.getAttribute('data-id');
    document.getElementById('pay_rem_display').innerText = button.getAttribute('data-rem');
    document.getElementById('modal_on_us_amount').value = button.getAttribute('data-rem');
});

// المودال الكلي مقفول ولا يقبل التعديل من المستخدم
document.getElementById('payBulkModal').addEventListener('show.bs.modal', function(event) {
    let button = event.relatedTarget;
    document.getElementById('modal_bulk_creditor').value = button.getAttribute('data-creditor');
    document.getElementById('bulk_amount_hidden').value = button.getAttribute('data-total');
    document.getElementById('modal_bulk_total_text').innerText = button.getAttribute('data-total');
});

// 💡 دالة طباعة كشف حساب المورد/الجهة
function printCreditorStatement(creditorName, modalId) {
    let modal = document.getElementById(modalId);
    let tableHtml = modal.querySelector('.table-responsive').innerHTML;
    
    let tempDiv = document.createElement('div');
    tempDiv.innerHTML = tableHtml;
    
    // إزالة عمود "إجراءات الدفع" من الطباعة عشان تظهر كشف حساب رسمي بس
    tempDiv.querySelectorAll('th:last-child, td:last-child').forEach(el => el.remove());
    tempDiv.querySelectorAll('table').forEach(tbl => {
        tbl.classList.add('table-bordered');
        tbl.classList.remove('table-hover');
    });

    let printDate = new Date().toLocaleString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });

    let printWin = window.open('', '', 'width=900,height=700');
    printWin.document.write(`
        <html dir="rtl" lang="ar">
        <head>
            <title>كشف حساب مديونية - ${creditorName}</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Cairo', sans-serif; padding: 40px; color: #0f172a; }
                .invoice-container { max-width: 800px; margin: auto; border: 2px solid #e2e8f0; border-radius: 15px; padding: 40px; }
                .header { text-align: center; border-bottom: 3px solid #0f2240; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { margin: 0; color: #0f2240; font-weight: 900; font-size: 32px; }
                .header p { margin: 5px 0 0; color: #475569; font-weight: 700; font-size: 18px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 30px; text-align: center; }
                th { background: #f8fafc; padding: 12px; font-size: 15px; border: 1px solid #cbd5e1; color: #0f2240; }
                td { padding: 12px; border: 1px solid #cbd5e1; font-size: 14px; font-weight: 700; }
                .footer { text-align: center; margin-top: 40px; font-weight: 700; color: #94a3b8; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="invoice-container">
                <div class="header">
                    <h1>شركة الضبع</h1>
                    <p>كشف حساب مديونية لجهة: <span style="color:#dc2626;">${creditorName}</span></p>
                    <p style="font-size:14px; color:#64748b;">تاريخ الطباعة: ${printDate}</p>
                </div>
                ${tempDiv.innerHTML}
                <div class="footer">تم إصدار هذا الكشف آلياً من نظام إدارة موارد شركة الضبع (ERP)</div>
            </div>
        </body>
        </html>
    `);
    printWin.document.close();
    setTimeout(() => { printWin.print(); printWin.close(); }, 500);
}
</script>
</body>
</html>