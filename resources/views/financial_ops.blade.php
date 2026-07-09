<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>العمليات المالية - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --blue: #0f172a; --blue-light: #1e293b; --brand-accent: #3b82f6; --bg: #f4f7fb; }
        
        body { 
            font-family: 'Cairo', sans-serif; background-color: var(--bg); 
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px), radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 40px 40px; background-position: 0 0, 20px 20px; overflow-x: hidden; 
        }
        
        .main-content { margin-right: 260px; padding: 35px 28px; position: relative; z-index: 1; }

        .op-card { 
            border-radius: 24px; cursor: pointer; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            position: relative; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .op-card::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.8), transparent);
            transform: rotate(45deg); transition: 0.6s; z-index: -1; opacity: 0;
        }
        .op-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important; }
        .op-card:hover::before { opacity: 1; left: 100%; top: 100%; }

        .sc { border-radius: 18px; padding: 20px; color: white; position: relative; overflow: hidden; border: none; }
        .sc::after { content: '\f200'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; left: -10px; bottom: -20px; font-size: 6rem; opacity: 0.1; }
        .sc-red    { background: linear-gradient(135deg, #dc2626, #991b1b); }
        .sc-green  { background: linear-gradient(135deg, #16a34a, #14532d); }
        .sc-blue   { background: linear-gradient(135deg, #2563eb, #1e3a8a); }
        .sc-gray   { background: linear-gradient(135deg, #475569, #1e293b); }
        .sc h6 { font-size: .8rem; font-weight: 700; opacity: .9; margin-bottom: 8px; letter-spacing: 0.5px; }
        .sc h4 { font-weight: 900; margin: 0; font-size: 1.4rem; }
        .sc-sub { font-size: .7rem; opacity: .85; margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(255,255,255,.35); line-height: 1.4; }

        .nav-pills { background: rgba(255,255,255,0.8); backdrop-filter: blur(10px); padding: 8px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 25px; border: 1px solid #e2e8f0; display: inline-flex; }
        .nav-pills .nav-link { font-weight: 800; color: #475569; border-radius: 12px; padding: 12px 30px; font-size: 15px; transition: 0.3s; }
        .nav-pills .nav-link.active { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }
        .nav-pills .nav-link.active-radar { background: #ef4444; color: #fff; animation: pulseRadar 2s infinite; }

        @keyframes pulseRadar { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

        .table-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 24px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 40px rgba(0,0,0,0.03); position: relative; }
        
        .table { position: relative; z-index: 1; }
        .table thead th { background: transparent; color: var(--blue); font-weight: 800; text-transform: uppercase; font-size: .75rem; padding: 18px 12px; border-bottom: 2px solid #e2e8f0; text-align: center; }
        .table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .2s; cursor: pointer; }
        .table tbody tr:hover { background: #f8fafc; }
        .table tbody tr.cancelled { background: #fafafa; opacity: .5; }
        .table tbody td { padding: 16px 12px; vertical-align: middle; font-size: .88rem; font-weight: 600; text-align: center; }

        /* شريط التنقل بين الصفحات (Pagination) */
        .pagination-wrap { display: flex; justify-content: center; padding: 18px 8px 6px; border-top: 1px solid #f1f5f9; margin-top: 4px; }
        .pagination-wrap nav { width: 100%; display: flex; justify-content: center; }
        .pagination-wrap .pagination {
            display: inline-flex; gap: 6px; padding: 6px 8px; margin: 0; list-style: none;
            background: #fff; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;
        }
        .pagination-wrap .page-item { list-style: none; }
        .pagination-wrap .page-item .page-link,
        .pagination-wrap .page-item span {
            min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
            padding: 0 12px; border-radius: 50%; font-weight: 800; font-size: .85rem;
            border: none; color: var(--blue-light); text-decoration: none; cursor: pointer;
        }
        .pagination-wrap .page-item .page-link:hover { background: #f1f5f9; }
        .pagination-wrap .page-item.active .page-link,
        .pagination-wrap .page-item.active span { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(15,23,42,.25); }
        .pagination-wrap .page-item.disabled .page-link,
        .pagination-wrap .page-item.disabled span { color: #cbd5e1; cursor: not-allowed; }

        .tx-type-cell { display: inline-flex; align-items: center; gap: 10px; border-radius: 50px; padding: 6px 16px 6px 6px; font-weight: 800; font-size: .8rem; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; }
        .tx-arrow { display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; color: white; position: relative; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .tx-income .tx-arrow { background: linear-gradient(135deg, #059669, #10b981); color: #d1fae5; }
        .tx-expense .tx-arrow { background: linear-gradient(135deg, #b91c1c, #ef4444); color: #fee2e2; }
        .tx-transfer .tx-arrow { background: linear-gradient(135deg, #1d4ed8, #60a5fa); color: #dbeafe; }
        .tx-cancelled .tx-arrow { background: linear-gradient(135deg, #475569, #94a3b8); color: #f1f5f9; box-shadow: none; }
        
        .tx-label .tx-main { display: block; font-weight: 800; font-size: .8rem; color: #1e293b; text-align: right; }
        .tx-label .tx-sub { display: block; font-weight: 700; font-size: .65rem; color: #64748b; margin-top: 2px; text-align: right; }

        .flow-path { display: flex; align-items: center; gap: 8px; font-family: monospace; font-size: .85rem; justify-content: center;}
        .flow-node { background: #f1f5f9; padding: 4px 10px; border-radius: 8px; font-weight: 700; color: #334155; border: 1px solid #e2e8f0; }
        .flow-line { flex-grow: 1; height: 2px; background-image: linear-gradient(to right, #cbd5e1 50%, transparent 50%); background-size: 6px 100%; min-width: 30px; position: relative; display: flex; align-items: center; justify-content: center; }
        .flow-line i { color: #94a3b8; font-size: .7rem; background: white; padding: 0 2px; }
        .flow-node.highlight-out { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
        .flow-node.highlight-in { background: #dcfce7; color: #15803d; border-color: #86efac; }

        .amt-income { color: #16a34a; font-weight: 900; font-size: 1.05rem; }
        .amt-expense { color: #dc2626; font-weight: 900; font-size: 1.05rem; }
        .amt-transfer { color: #2563eb; font-weight: 900; font-size: 1.05rem; }
        .amt-cancelled { color: #94a3b8; font-weight: 700; text-decoration: line-through; }

        /* ── 📡 تصميم الرادار ── */
        .radar-container { position: relative; padding: 20px 0; z-index: 1; }
        .radar-container::before { content: ''; position: absolute; top: 0; bottom: 0; right: 40px; width: 4px; background: #e2e8f0; border-radius: 5px; }
        .radar-item { position: relative; margin-bottom: 25px; padding-right: 80px; cursor: pointer; }
        .radar-icon { position: absolute; right: 22px; top: 0; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; font-weight: 900; z-index: 2; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .radar-content { background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border: 1px solid #e2e8f0; border-radius: 16px; padding: 15px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); position: relative; transition: 0.3s; }
        .radar-content:hover { transform: translateX(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); border-color: var(--brand-accent); }
        .radar-content::before { content: ''; position: absolute; right: -8px; top: 12px; width: 15px; height: 15px; background: #fff; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; transform: rotate(45deg); }
        .radar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 8px; }
        .radar-user { font-weight: 900; color: var(--blue); font-size: 15px; }
        .radar-time { font-size: 12px; color: #94a3b8; font-weight: 800; direction: ltr; }
        .radar-text { font-size: 14.5px; color: #334155; font-weight: 700; line-height: 1.6; }

        .icon-create { background-color: #10b981; } .icon-update { background-color: #3b82f6; } 
        .icon-delete { background-color: #ef4444; } .icon-cancel { background-color: #f59e0b; } 
        .icon-payment { background-color: #059669; } .icon-system { background-color: #64748b; } 

        .filter-btn { border: 1px solid #cbd5e1; background: #fff; color: #475569; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 800; text-decoration: none; transition: 0.2s; }
        .filter-btn:hover, .filter-btn.active { background: var(--blue); color: #fff; border-color: var(--blue); }

        /* Detail Rows for Modals */
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f1f5f9; }
        .detail-row:last-child { border-bottom: none; }
        .detail-lbl { font-size: 13px; color: #64748b; font-weight: 700; }
        .detail-val { font-size: 15px; font-weight: 900; color: #1e293b; text-align: left; }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>

@include('sidebar')

<div class="main-content">

    @if(session('success')) <div class="alert alert-success fw-bold rounded-4 p-3 mb-3"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger fw-bold rounded-4 p-3 mb-3"><i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}</div> @endif

    {{-- ── الهيدر والطباعة ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="header-title">العمليات المالية ومراقبة النظام</h1>
            <p class="header-desc">لوحة تحكم بنكية لمراقبة تدفقات الأموال، ورادار تسجيل كافة الأنشطة</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark fw-bold rounded-pill px-4 shadow-sm" onclick="printDailySummary()">
                <i class="fa fa-print me-1"></i> طباعة ملخص اليوم
            </button>
        </div>
    </div>

    {{-- 💰 كارت إجراء حركة يدوية (إيداع/صرف/تحويل) --}}
    <div class="row mb-4 position-relative z-1">
        <div class="col-12">
            <div class="op-card d-flex align-items-center justify-content-between p-4" data-bs-toggle="modal" data-bs-target="#addOpModal" style="background: linear-gradient(135deg, #0f172a, #1e293b); border: none; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2); border-bottom: 4px solid #3b82f6;">
                <div class="d-flex align-items-center gap-4">
                    <div class="icon-lg m-0 d-flex align-items-center justify-content-center bg-white bg-opacity-10 text-white shadow-none border border-light border-opacity-25" style="width: 70px; height: 70px;">
                        <i class="fa fa-bolt fs-2"></i>
                    </div>
                    <div class="text-start">
                        <h4 class="fw-bold text-white mb-1">تنفيذ حركة يدوية</h4>
                        <p class="text-white-50 fw-bold m-0 fs-6">
                            <span class="badge bg-success me-1">إيداع</span> يتسجّل كـ دين عليك للمودع &nbsp;|&nbsp;
                            <span class="badge bg-danger me-1">صرف</span> يتسجّل كـ مستحق على المستلم &nbsp;|&nbsp;
                            <span class="badge bg-primary me-1">تحويل</span> بين المحافظ
                        </p>
                    </div>
                </div>
                <div>
                    <span class="btn btn-primary rounded-pill fw-bold px-5 py-2 shadow-sm fs-5"><i class="fa fa-plus me-2"></i>بدء العملية</span>
                </div>
            </div>
        </div>
    </div>

    {{-- الإحصائيات --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="sc sc-red shadow-sm"><h6>حجم التدفقات الداخلة</h6><h4>{{ fmtMoney($total_income) }} ج</h4>
            <div class="sc-sub">كل فلوس دخلت الحسابات: إيرادات فعلية + تسويات (إيداعات مسجّلة كدين على الشركة). أشمل من "إجمالي الإيرادات" في شاشة التقارير لإنها بتفصل التسويات</div>
        </div></div>
        <div class="col-md-3 col-6"><div class="sc sc-green shadow-sm"><h6>حجم التدفقات الخارجة</h6><h4>{{ fmtMoney($total_expense) }} ج</h4>
            <div class="sc-sub">كل فلوس خرجت من الحسابات: مصروفات + رواتب + خصومات + عُهد موظفين + إعدامات ديون وأي صرف تاني. أشمل من "إجمالي المصروفات" في شاشة التقارير لإنها بتستبعد بعض البنود دي كأرقام منفصلة</div>
        </div></div>
        <div class="col-md-3 col-6"><div class="sc sc-blue shadow-sm"><h6>إجمالي التحويلات</h6><h4>{{ fmtMoney($total_transfer) }} ج</h4>
            <div class="sc-sub">فلوس اتنقلت بين حسابات الشركة نفسها (من خزنة لمحفظة مثلاً) — مش دخلت ولا خرجت فعلياً</div>
        </div></div>
        <div class="col-md-3 col-6"><div class="sc sc-gray shadow-sm"><h6>عمليات ملغاة</h6><h4>{{ $cancelled_count }}</h4>
            <div class="sc-sub">عدد الحركات اليدوية اللي اتعمل لها إلغاء في نفس الفترة</div>
        </div></div>
    </div>

    {{-- التابات السحرية --}}
    <ul class="nav nav-pills mb-4" id="systemTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-finance"><i class="fa fa-vault me-1"></i> العمليات المالية (الخزائن)</button>
        </li>
        <li class="nav-item ms-2">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-capital"><i class="fa fa-scale-balanced me-1"></i> تعديلات رأس المال</button>
        </li>
        <li class="nav-item ms-2">
            <button class="nav-link active-radar shadow-sm" data-bs-toggle="pill" data-bs-target="#tab-radar"><i class="fa fa-satellite-dish me-1"></i> رادار النظام (سجل الحركات)</button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- 💰 ── تابة العمليات المالية ── 💰 --}}
        <div class="tab-pane fade show active" id="tab-finance">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center p-4 pb-0 position-relative z-1">
                    <h5 class="fw-bold text-dark m-0"><i class="fa fa-list-check me-2 text-primary"></i>سجل الحركات التفصيلي</h5>
                </div>

                <div class="px-4 py-3 position-relative z-1">
                    <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                        <form method="GET" action="{{ route('financial.index') }}" class="row g-3 align-items-end">
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2 mb-1">
                                    <a href="?quick=all" class="filter-btn {{ request('quick')==='all' ? 'active' : '' }}">الكل</a>
                                    <a href="?quick=today" class="filter-btn {{ (request('quick')==='today' || (!request('quick') && !request('custom_from'))) ? 'active' : '' }}">اليوم</a>
                                    <a href="?quick=yesterday" class="filter-btn {{ request('quick')==='yesterday' ? 'active' : '' }}">أمس</a>
                                    <a href="?quick=month" class="filter-btn {{ request('quick')==='month' ? 'active' : '' }}">الشهر ده</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small text-muted mb-1">من تاريخ</label>
                                <input type="date" name="custom_from" class="form-control border-secondary fw-bold" value="{{ request('custom_from') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold small text-muted mb-1">إلى تاريخ</label>
                                <input type="date" name="custom_to" class="form-control border-secondary fw-bold" value="{{ request('custom_to') }}">
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-dark rounded-pill fw-bold flex-fill shadow-sm"><i class="fa fa-filter me-1"></i>تصفية</button>
                                @if(request()->anyFilled(['quick','custom_from'])) <a href="{{ route('financial.index') }}" class="btn btn-outline-danger rounded-pill px-3"><i class="fa fa-rotate-left"></i></a> @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="px-3 pb-4">
                    @if($transactions->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0 align-middle" id="financeTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th class="text-start" style="width: 200px;">نوع الحركة</th>
                                    <th>المبلغ</th>
                                    <th>الطرف الخارجي</th>
                                    <th style="width: 250px;" class="no-print">مسار الحركة (Flow)</th>
                                    <th class="text-start">البيان</th>
                                    <th>تاريخ التنفيذ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                        @php
                                    $isCancelled = $tx->status === 'cancelled';
                                    if ($isCancelled) {
                                        $cellClass = 'tx-cancelled'; $amtClass = 'amt-cancelled';
                                        $arrowIcon = 'fa-ban'; $mainLabel = 'حركة ملغاة'; $subLabel = 'Reversed'; $amtPrefix = '';
                                    } elseif ($tx->type === 'settlement' || $tx->type === 'income') {
                                        $cellClass = 'tx-income'; $amtClass = 'amt-income';
                                        $arrowIcon = 'fa-arrow-down'; $mainLabel = 'إيداع / تسوية'; $subLabel = 'تدفق داخل'; $amtPrefix = '+';
                                    } elseif (in_array($tx->type, ['expense', 'general_expense', 'salary_expense', 'discount'])) {
                                        $cellClass = 'tx-expense'; $amtClass = 'amt-expense';
                                        $arrowIcon = 'fa-arrow-up'; $mainLabel = 'صرف / مصروف'; $subLabel = 'تدفق خارج'; $amtPrefix = '−';
                                    } else {
                                        $cellClass = 'tx-transfer'; $amtClass = 'amt-transfer';
                                        $arrowIcon = 'fa-right-left'; $mainLabel = 'تحويل مالي'; $subLabel = 'حركة داخلية'; $amtPrefix = '⇄';
                                    }

                                    // 💡 فحص الكلمات المفتاحية للنظام
                                    $isSystemOp = str_contains($tx->notes, 'قسط') || str_contains($tx->notes, 'مخزن') || str_contains($tx->notes, 'راتب') || str_contains($tx->notes, 'بيع') || str_contains($tx->notes, 'مورد') || str_contains($tx->notes, 'مرتجع') || str_contains($tx->notes, 'إهلاك') || str_contains($tx->notes, 'دين') || str_contains($tx->notes, 'عهدة');
                                @endphp
                                <tr class="{{ $isCancelled ? 'cancelled' : '' }}" 
                                    data-id="{{ $tx->id }}" 
                                    data-type-lbl="{{ $mainLabel }}" 
                                    data-amt="{{ number_format($tx->amount, 2) }}" 
                                    data-person="{{ $tx->person_name ?: '—' }}" 
                                    data-notes="{{ htmlspecialchars($tx->notes) }}" 
                                    data-date="{{ \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d h:i A') }}" 
                                    data-status="{{ $tx->status }}" 
                                    data-from="{{ $tx->from_name ?: '—' }}"
                                    data-to="{{ $tx->to_name ?: '—' }}"
                                    data-is-system="{{ $isSystemOp ? '1' : '0' }}"
                                    data-can-cancel="{{ ($tx->can_cancel ?? false) ? '1' : '0' }}"
                                    onclick="openTxDetailsModal(this)">
                                    
                                    <td class="text-muted small fw-bold font-monospace">{{ $tx->id }}</td>
                                    <td class="text-start">
                                        <div class="tx-type-cell {{ $cellClass }}">
                                            <div class="tx-arrow"><i class="fa {{ $arrowIcon }} arrow-icon"></i></div>
                                            <div class="tx-label"><span class="tx-main">{{ $mainLabel }}</span><span class="tx-sub">{{ $subLabel }}</span></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="{{ $amtClass }} font-monospace d-flex justify-content-center align-items-center gap-1">
                                            <span class="text-muted fs-6">{{ $amtPrefix }}</span>{{ number_format($tx->amount, 2) }}<small class="text-muted fs-6 fw-bold">EGP</small>
                                        </span>
                                    </td>
                                    <td class="fw-bold" style="color:var(--blue-light);">
                                        @if($tx->person_name) <i class="fa fa-user-circle text-muted me-1"></i>{{ $tx->person_name }} @else <span class="text-muted fw-normal">—</span> @endif
                                    </td>
                                    <td class="no-print">
                                        <div class="flow-path">
                                            @if(in_array($tx->type, ['settlement', 'income']))
                                                <div class="flow-node text-muted"><i class="fa fa-globe"></i> خارجي</div><div class="flow-line"><i class="fa fa-chevron-left"></i></div><div class="flow-node highlight-in">{{ $tx->to_name ?? '—' }}</div>
                                            @elseif(in_array($tx->type, ['expense', 'general_expense', 'salary_expense', 'discount']))
                                                <div class="flow-node highlight-out">{{ $tx->from_name ?? '—' }}</div><div class="flow-line"><i class="fa fa-chevron-left"></i></div><div class="flow-node text-muted"><i class="fa fa-globe"></i> خارجي</div>
                                            @else
                                                <div class="flow-node border-primary text-primary">{{ $tx->from_name ?? '—' }}</div><div class="flow-line"><i class="fa fa-chevron-left text-primary"></i></div><div class="flow-node border-primary text-primary">{{ $tx->to_name ?? '—' }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-start"><div class="text-truncate fw-bold" style="max-width:180px; color:#475569;" title="{{ $tx->notes }}">{{ $tx->notes ?: '—' }}</div></td>
                                    <td>
                                        <div class="fw-bold" style="color:#334155;">{{ \Carbon\Carbon::parse($tx->created_at)->format('Y/m/d') }}</div>
                                        <div class="text-muted small font-monospace">{{ \Carbon\Carbon::parse($tx->created_at)->format('H:i') }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($transactions->hasPages())
                        <div class="pagination-wrap">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                    @else
                    <div class="text-center py-5 position-relative z-1"><i class="fa fa-vault fa-4x text-muted opacity-25 mb-3 d-block"></i><h5 class="text-muted fw-bold">لا توجد حركات مالية.</h5></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ⚖️ ── تابة تعديلات رأس المال ── ⚖️ --}}
        <div class="tab-pane fade" id="tab-capital">
            <div class="row g-4">

                {{-- فورم التعديل --}}
                <div class="col-lg-4">
                    <div class="table-card p-4 h-100">
                        <h5 class="fw-bold text-dark mb-1"><i class="fa fa-scale-balanced me-2 text-primary"></i>تعديل رأس المال</h5>
                        <p class="text-muted small mb-4">إضافة أو صرف مبلغ يؤثر على رأس المال (السيولة) فقط — لا يُسجَّل في المصروفات أو الديون أو المستحقات.</p>

                        @if(session('auth_user') && session('auth_user')->role === 'admin')
                        <form action="{{ route('financial.capitalAdjust') }}" method="POST" onsubmit="return disableSubmitBtn(this)">
                            @csrf
                            <div class="mb-3">
                                <label class="fw-bold small text-muted mb-1">نوع العملية</label>
                                <div class="d-flex gap-2">
                                    <label class="flex-fill m-0">
                                        <input type="radio" name="type" value="increase" class="btn-check" id="cap_inc" checked>
                                        <span class="btn btn-outline-success fw-bold w-100 rounded-pill"><i class="fa fa-plus me-1"></i>إضافة لرأس المال</span>
                                    </label>
                                    <label class="flex-fill m-0">
                                        <input type="radio" name="type" value="decrease" class="btn-check" id="cap_dec">
                                        <span class="btn btn-outline-danger fw-bold w-100 rounded-pill"><i class="fa fa-minus me-1"></i>صرف من رأس المال</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small text-muted mb-1">الخزنة / المحفظة</label>
                                <select name="account_id" class="form-select fw-bold border-secondary" required>
                                    <option value="">— اختر الخزنة —</option>
                                    @foreach($accounts->filter(fn($a) => in_array($a->category, ['bank_wallet','safe_cash']) || ($a->category === 'project_sector' && str_contains($a->account_name, 'خزنة'))) as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ fmtMoney($acc->balance) }} ج)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small text-muted mb-1">المبلغ (ج)</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold border-secondary text-center fs-5" placeholder="0.00" required>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold small text-muted mb-1">السبب</label>
                                <textarea name="reason" rows="2" class="form-control fw-bold border-secondary" placeholder="مثال: ضخ رأس مال من الشريك / سحب شخصي..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 fw-bold rounded-pill py-2 shadow-sm"><i class="fa fa-check me-1"></i>تنفيذ التعديل</button>
                        </form>
                        @else
                        <div class="text-center py-5">
                            <i class="fa fa-lock fa-3x text-muted opacity-25 mb-3 d-block"></i>
                            <h6 class="text-muted fw-bold">هذا الإجراء متاح للأدمن فقط.</h6>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- السجل --}}
                <div class="col-lg-8">
                    <div class="table-card h-100">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-4 pb-0 position-relative z-1">
                            <h5 class="fw-bold text-dark m-0"><i class="fa fa-clock-rotate-left me-2 text-primary"></i>سجل تعديلات رأس المال</h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2">+ {{ fmtMoney($cap_total_increase ?? 0) }} ج</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold px-3 py-2">− {{ fmtMoney($cap_total_decrease ?? 0) }} ج</span>
                            </div>
                        </div>

                        {{-- فلاتر --}}
                        <div class="px-4 py-3 position-relative z-1">
                            <div style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                                <form method="GET" action="{{ route('financial.index') }}" class="row g-2 align-items-end">
                                    <input type="hidden" name="tab" value="capital">
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap gap-2 mb-1">
                                            <a href="?tab=capital" class="filter-btn {{ (!($capQuick ?? '') && !($capFrom ?? '')) ? 'active' : '' }}">الكل</a>
                                            <a href="?tab=capital&cap_quick=today" class="filter-btn {{ ($capQuick ?? '')==='today' ? 'active' : '' }}">اليوم</a>
                                            <a href="?tab=capital&cap_quick=yesterday" class="filter-btn {{ ($capQuick ?? '')==='yesterday' ? 'active' : '' }}">أمس</a>
                                            <a href="?tab=capital&cap_quick=month" class="filter-btn {{ ($capQuick ?? '')==='month' ? 'active' : '' }}">الشهر ده</a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold small text-muted mb-1">من تاريخ</label>
                                        <input type="date" name="cap_from" class="form-control border-secondary fw-bold" value="{{ $capFrom ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold small text-muted mb-1">إلى تاريخ</label>
                                        <input type="date" name="cap_to" class="form-control border-secondary fw-bold" value="{{ $capTo ?? '' }}">
                                    </div>
                                    <div class="col-md-4 d-flex gap-2">
                                        <button type="submit" class="btn btn-dark rounded-pill fw-bold flex-fill shadow-sm"><i class="fa fa-filter me-1"></i>تصفية</button>
                                        @if(($capQuick ?? '') || ($capFrom ?? '')) <a href="{{ route('financial.index') }}?tab=capital" class="btn btn-outline-danger rounded-pill px-3"><i class="fa fa-rotate-left"></i></a> @endif
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="px-3 pb-4">
                            @if(($capital_adjustments ?? collect())->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>النوع</th>
                                            <th>المبلغ</th>
                                            <th>الخزنة</th>
                                            <th class="text-start">السبب</th>
                                            <th>التاريخ</th>
                                            <th>بواسطة</th>
                                            <th class="no-print"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($capital_adjustments as $adj)
                                        @php $isInc = $adj->type === 'increase'; $isCanc = $adj->status === 'cancelled'; @endphp
                                        <tr class="{{ $isCanc ? 'cancelled' : '' }}" style="cursor:default;">
                                            <td class="text-muted small fw-bold font-monospace">{{ $adj->id }}</td>
                                            <td>
                                                @if($isCanc)
                                                    <span class="badge bg-secondary"><i class="fa fa-ban me-1"></i>ملغاة</span>
                                                @elseif($isInc)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa fa-plus me-1"></i>إضافة</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa fa-minus me-1"></i>صرف</span>
                                                @endif
                                            </td>
                                            <td class="font-monospace fw-bold {{ $isCanc ? 'text-muted text-decoration-line-through' : ($isInc ? 'text-success' : 'text-danger') }}">
                                                {{ $isInc ? '+' : '−' }}{{ number_format($adj->amount, 2) }} <small class="text-muted">ج</small>
                                            </td>
                                            <td class="fw-bold" style="color:var(--blue-light);">{{ $adj->account_name ?? '—' }}</td>
                                            <td class="text-start"><div class="fw-bold" style="color:#475569; max-width:220px;">{{ $adj->reason ?: '—' }}</div></td>
                                            <td>
                                                <div class="fw-bold" style="color:#334155;">{{ \Carbon\Carbon::parse($adj->created_at)->format('Y/m/d') }}</div>
                                                <div class="text-muted small font-monospace">{{ \Carbon\Carbon::parse($adj->created_at)->format('h:i A') }}</div>
                                            </td>
                                            <td class="text-muted small fw-bold">{{ $adj->created_by_name ?? '—' }}</td>
                                            <td class="no-print">
                                                @if(!$isCanc && session('auth_user') && session('auth_user')->role === 'admin')
                                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="cancelCapitalAdj({{ $adj->id }})" title="إلغاء العملية"><i class="fa fa-rotate-left"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5 position-relative z-1"><i class="fa fa-scale-balanced fa-4x text-muted opacity-25 mb-3 d-block"></i><h5 class="text-muted fw-bold">لا توجد تعديلات رأس مال في هذه الفترة.</h5></div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- 📡 ── تابة رادار النظام ── 📡 --}}
        <div class="tab-pane fade" id="tab-radar">
            <div class="table-box" style="background-color: #fafafa; border: 2px solid #e2e8f0;">
                <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-4">
                    <div>
                        <h4 class="fw-black text-danger m-0"><i class="fa fa-satellite-dish me-2 fa-beat"></i> رادار النظام المباشر</h4>
                        <span class="text-muted fw-bold small">يسجل كل حركة تتم داخل السيستم تلقائياً (آخر 200 حركة)</span>
                    </div>
                    <button class="btn btn-sm btn-dark fw-bold px-3" onclick="location.reload()"><i class="fa fa-sync me-1"></i> تحديث الرادار</button>
                </div>

                <div class="radar-container px-3" id="radarContainer">
                    @forelse($radar_logs ?? [] as $log)
                    @php
                        $icon = 'fa-info'; $colorClass = 'icon-system';
                        if ($log->action == 'create') { $icon = 'fa-plus'; $colorClass = 'icon-create'; }
                        elseif ($log->action == 'update') { $icon = 'fa-pen'; $colorClass = 'icon-update'; }
                        elseif ($log->action == 'delete') { $icon = 'fa-trash'; $colorClass = 'icon-delete'; }
                        elseif ($log->action == 'cancel') { $icon = 'fa-times'; $colorClass = 'icon-cancel'; }
                        elseif ($log->action == 'payment') { $icon = 'fa-money-bill-wave'; $colorClass = 'icon-payment'; }
                        if ($log->module == 'inventory') { $icon = 'fa-box'; }
                        elseif ($log->module == 'installments') { $icon = 'fa-file-signature'; }
                    @endphp
                    <div class="radar-item" 
                         data-id="{{ $log->id }}" 
                         data-user="{{ $log->user_name ?? 'النظام' }}" 
                         data-module="{{ strtoupper($log->module) }}" 
                         data-date="{{ date('Y-m-d h:i A', strtotime($log->created_at)) }}" 
                         data-desc="{{ htmlspecialchars($log->description) }}" 
                         onclick="openLogDetailsModal(this)">
                        <div class="radar-icon {{ $colorClass }}"><i class="fa {{ $icon }}"></i></div>
                        <div class="radar-content">
                            <div class="radar-header">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="radar-user"><i class="fa fa-user-circle me-1 text-muted"></i>{{ $log->user_name ?? 'النظام' }}</span>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark border">{{ strtoupper($log->module) }}</span>
                                </div>
                                <span class="radar-time"><i class="fa fa-clock me-1"></i>{{ date('h:i A - d/m', strtotime($log->created_at)) }}</span>
                            </div>
                            <div class="radar-text">{!! nl2br(e($log->description)) !!}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fa fa-satellite fa-4x text-muted opacity-25 mb-3 d-block"></i>
                        <h5 class="fw-bold text-muted">الرادار هادئ.. لا توجد حركات مسجلة مؤخراً.</h5>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     MODALS (إضافة عملية + التفاصيل والإلغاء)
══════════════════════════════════════════════════════════════ --}}

{{-- 1. مودال إضافة حركة يدوية (إيداع/صرف/تحويل) --}}
<div class="modal fade" id="addOpModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('financial.store') }}" method="POST" class="modal-content border-0 shadow-lg" onsubmit="return disableSubmitBtn(this)">
            @csrf
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="fw-bold m-0"><i class="fa fa-plus-circle me-2 text-success"></i>تنفيذ حركة يدوية (إيداع/صرف/تحويل)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">

                <label class="fw-bold mb-2 text-dark">نوع الحركة المطلوبة:</label>
                <div class="d-flex gap-2 mb-2">
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="op_type" id="op_exp" value="expense" checked onchange="toggleOpFields()">
                        <label class="btn btn-outline-danger w-100 fw-bold border-2" for="op_exp"><i class="fa fa-arrow-down me-1"></i> صرف</label>
                    </div>
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="op_type" id="op_inc" value="settlement" onchange="toggleOpFields()">
                        <label class="btn btn-outline-success w-100 fw-bold border-2" for="op_inc"><i class="fa fa-arrow-up me-1"></i> إيداع</label>
                    </div>
                    <div class="flex-fill">
                        <input type="radio" class="btn-check" name="op_type" id="op_trans" value="transfer" onchange="toggleOpFields()">
                        <label class="btn btn-outline-primary w-100 fw-bold border-2" for="op_trans"><i class="fa fa-exchange-alt me-1"></i> تحويل</label>
                    </div>
                </div>

                {{-- شرح تأثير الحركة على الحسابات --}}
                <div class="alert mb-3 small fw-bold py-2 px-3" id="op_type_hint" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a8a;border-radius:10px;">
                    <i class="fa fa-circle-info me-1"></i> <span id="op_hint_text">بيتخصم من الخزنة + بيتسجل دين/مستحق على المستلم.</span>
                </div>

                <div class="mb-3">
                    <label class="fw-bold mb-1 text-dark">المبلغ (ج.م) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control text-center fs-4 fw-bold border-dark" required autocomplete="off">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6" id="div_from_acc">
                        <label class="fw-bold mb-1 text-danger">سحب من خزنة <span class="text-danger">*</span></label>
                        <select name="from_account_id" id="from_account_id" class="form-select fw-bold border-danger">
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} | متاح: {{ fmtMoney($acc->balance) }} ج</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6" id="div_to_acc" style="display:none;">
                        <label class="fw-bold mb-1 text-success">إيداع إلى خزنة <span class="text-danger">*</span></label>
                        <select name="to_account_id" id="to_account_id" class="form-select fw-bold border-success">
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} | متاح: {{ fmtMoney($acc->balance) }} ج</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3" id="div_person_details">
                    <div class="col-6">
                        <label class="fw-bold mb-1 text-primary" id="lbl_person_name">اسم المستلم (هيتسجل عليه مستحق) <span class="text-danger">*</span></label>
                        <input type="text" name="person_name" id="person_name" required class="form-control fw-bold border-primary" placeholder="الاسم ..." autocomplete="off">
                    </div>
                    <div class="col-6">
                        <label class="fw-bold mb-1 text-primary">رقم الهاتف (اختياري)</label>
                        <input type="text" name="person_phone" id="person_phone" class="form-control fw-bold border-primary" placeholder="الرقم (اختياري)..." autocomplete="off">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="fw-bold mb-1 text-dark">البيان / الملاحظات <span class="text-muted fw-normal" style="font-size:.8rem;">(اختياري)</span></label>
                    <input type="text" name="notes" class="form-control fw-bold" placeholder="سبب الحركة (اختياري)..." autocomplete="off">
                </div>

            </div>
            <div class="modal-footer border-0 p-3 bg-light">
                <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> إلغاء
                </button>
                <button type="submit" class="btn btn-success fw-bold flex-grow-1 rounded-pill py-2 shadow-sm fs-6">
                    <i class="fa fa-check-circle me-2"></i> تنفيذ وإتمام العملية
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 2. مودال تفاصيل الحركة المالية (يظهر عند الضغط على الصف) --}}
<div class="modal fade" id="txDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="fw-bold m-0"><i class="fa fa-file-invoice-dollar me-2 text-primary"></i>تفاصيل الحركة المالية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4 bg-light border-bottom text-center">
                    <h2 class="fw-black text-dark m-0" id="txD_amt"></h2>
                    <span class="badge bg-secondary mt-2 fs-6" id="txD_type"></span>
                </div>
                <div class="p-4">
                    <div class="detail-row"><span class="detail-lbl">رقم القيد (ID)</span><span class="detail-val font-monospace" id="txD_id"></span></div>
                    <div class="detail-row"><span class="detail-lbl">تاريخ ووقت التنفيذ</span><span class="detail-val font-monospace" id="txD_date" dir="ltr"></span></div>
                    <div class="detail-row"><span class="detail-lbl">الطرف الخارجي</span><span class="detail-val text-primary" id="txD_person"></span></div>
                    <div class="detail-row"><span class="detail-lbl">سحب من (الخزنة)</span><span class="detail-val text-danger" id="txD_from"></span></div>
                    <div class="detail-row"><span class="detail-lbl">إيداع إلى (الخزنة)</span><span class="detail-val text-success" id="txD_to"></span></div>
                    <div class="detail-row"><span class="detail-lbl">البيان والملاحظات</span><span class="detail-val text-wrap text-break" id="txD_notes"></span></div>
                    
                    <div id="txD_cancel_alert" class="alert alert-danger mt-3 mb-0 fw-bold text-center" style="display:none;">
                        <i class="fa fa-ban me-1"></i> هذه الحركة ملغاة ومعكوسة.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-dark fw-bold rounded-pill px-4" onclick="printTxDetails()">
                    <i class="fa fa-print me-1"></i> طباعة التفاصيل
                </button>
                {{-- <button type="button" id="txD_cancel_btn" class="btn btn-danger fw-bold rounded-pill px-4" style="display:none;" onclick="cancelFinancialOp()">
                    <i class="fa fa-ban me-1"></i> إلغاء الحركة
                </button> --}}
                <button type="button" class="btn btn-light fw-bold rounded-pill px-4 flex-grow-1" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- 3. مودال تفاصيل نشاط الرادار --}}
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="fw-bold m-0"><i class="fa fa-satellite-dish me-2"></i>تفاصيل نشاط النظام</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="detail-row"><span class="detail-lbl">رقم السجل</span><span class="detail-val font-monospace" id="logD_id"></span></div>
                <div class="detail-row"><span class="detail-lbl">بواسطة المستخدم</span><span class="detail-val text-primary" id="logD_user"></span></div>
                <div class="detail-row"><span class="detail-lbl">وقت النشاط</span><span class="detail-val font-monospace" id="logD_date" dir="ltr"></span></div>
                <div class="detail-row"><span class="detail-lbl">قسم النظام</span><span class="detail-val"><span class="badge bg-dark" id="logD_module"></span></span></div>
                <div class="mt-3 p-3 bg-light border rounded">
                    <span class="detail-lbl d-block mb-2">الوصف الكامل للنشاط:</span>
                    <span class="detail-val text-wrap text-break lh-lg" id="logD_desc"></span>
                </div>
            </div>
            <div class="modal-footer border-0 p-2 d-flex gap-2">
                <button type="button" class="btn btn-outline-dark fw-bold rounded-pill px-4" onclick="printLogDetails()">
                    <i class="fa fa-print me-1"></i> طباعة التفاصيل
                </button>
                <button type="button" class="btn btn-light fw-bold rounded-pill flex-grow-1" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    function disableSubmitBtn(form) {
        if(form.classList.contains('submitting')) return false;
        form.classList.add('submitting');
        let btn = form.querySelector('button[type="submit"]');
        if(btn) { btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> جاري...'; btn.disabled = true; }
        return true;
    }

    function toggleOpFields() {
        const type = document.querySelector('input[name="op_type"]:checked').value;
        const divFrom = document.getElementById('div_from_acc');
        const divTo = document.getElementById('div_to_acc');
        const selFrom = document.getElementById('from_account_id');
        const selTo = document.getElementById('to_account_id');
        const divPerson = document.getElementById('div_person_details');
        const lblPerson = document.getElementById('lbl_person_name');
        const inpPerson = document.getElementById('person_name');
        const hint = document.getElementById('op_hint_text');

        if (type === 'expense') {
            divFrom.style.display = 'block'; divFrom.classList.replace('col-6', 'col-12');
            divTo.style.display = 'none';
            selFrom.required = true; selTo.required = false;
            divPerson.style.display = 'flex';
            lblPerson.innerHTML = 'اسم المستلم (هيتسجل عليه مستحق) <span class="text-danger">*</span>';
            inpPerson.name = 'person_name';
            inpPerson.required = true;
            hint.innerText = 'بيتخصم المبلغ من الخزنة + بيتسجل دين على المستلم في المستحقات.';
        }
        else if (type === 'settlement') {
            divFrom.style.display = 'none';
            divTo.style.display = 'block'; divTo.classList.replace('col-6', 'col-12');
            selFrom.required = false; selTo.required = true;
            divPerson.style.display = 'flex';
            lblPerson.innerHTML = 'اسم المودع/الدائن (هيتسجل دين عليك) <span class="text-danger">*</span>';
            inpPerson.name = 'creditor_name';
            inpPerson.required = true;
            hint.innerText = 'بيتضاف المبلغ للخزنة + بيتسجل دين عليك للمودع/الدائن في ديون الشركة.';
        }
        else if (type === 'transfer') {
            divFrom.style.display = 'block'; divFrom.classList.replace('col-12', 'col-6');
            divTo.style.display = 'block'; divTo.classList.replace('col-12', 'col-6');
            selFrom.required = true; selTo.required = true;
            divPerson.style.display = 'none';
            inpPerson.name = 'ignore_person';
            inpPerson.required = false;
            hint.innerText = 'تحويل بين خزنتين — مفيش طرف خارجي ومفيش دين/مستحق.';
        }
    }

    // تشغيل أولي لما الصفحة تفتح (الصرف checked افتراضياً)
    document.addEventListener('DOMContentLoaded', toggleOpFields);

    // ── فتح تفاصيل الحركة المالية ──
    let _currentTxId = null;
    let _currentTxData = {};
    function openTxDetailsModal(row) {
        _currentTxId = row.getAttribute('data-id');
        const status = row.getAttribute('data-status');
        const canCancel = row.getAttribute('data-can-cancel') === '1';

        _currentTxData = {
            id: _currentTxId,
            amt: row.getAttribute('data-amt'),
            type: row.getAttribute('data-type-lbl'),
            date: row.getAttribute('data-date'),
            person: row.getAttribute('data-person'),
            from: row.getAttribute('data-from'),
            to: row.getAttribute('data-to'),
            notes: row.getAttribute('data-notes'),
            status: status,
        };

        document.getElementById('txD_id').innerText = '#' + _currentTxId;
        document.getElementById('txD_amt').innerText = _currentTxData.amt + ' ج.م';
        document.getElementById('txD_type').innerText = _currentTxData.type;
        document.getElementById('txD_date').innerText = _currentTxData.date;
        document.getElementById('txD_person').innerText = _currentTxData.person;
        document.getElementById('txD_from').innerText = _currentTxData.from;
        document.getElementById('txD_to').innerText = _currentTxData.to;
        document.getElementById('txD_notes').innerText = _currentTxData.notes;

        document.getElementById('txD_cancel_alert').style.display = (status === 'cancelled') ? 'block' : 'none';
        document.getElementById('txD_cancel_btn').style.display = (canCancel && status !== 'cancelled') ? 'inline-block' : 'none';

        new bootstrap.Modal(document.getElementById('txDetailsModal')).show();
    }

    // ── إلغاء حركة مالية (إيداع/صرف يدوي فقط) ──
    async function cancelFinancialOp() {
        if (!_currentTxId) return;

        const { value: pin } = await Swal.fire({
            title: '🔒 إلغاء حركة مالية',
            text: 'أدخل كلمة مرور حسابك (أدمن) للتأكيد:',
            input: 'password',
            showCancelButton: true,
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'تراجع',
            confirmButtonColor: '#dc2626',
            inputAttributes: { autocomplete: 'current-password' },
        });
        if (!pin) return;

        const confirm = await Swal.fire({
            icon: 'warning',
            title: 'تأكيد الإلغاء',
            html: `<p>سيتم تعليم الحركة <b>كملغاة</b> وعكس أثرها على رصيد الخزنة تلقائياً.</p>
                   <p class="small text-muted">إيداع → خصم من الخزنة | صرف → إرجاع للخزنة</p>`,
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'نعم، ألغِ الحركة',
            cancelButtonText: 'تراجع',
        });
        if (!confirm.isConfirmed) return;

        Swal.fire({ title: 'جاري الإلغاء...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const fd = new FormData();
            fd.append('_token', CSRF);
            fd.append('tx_id', _currentTxId);
            fd.append('admin_pin', pin);

            const res = await fetch('{{ route('financial.cancel') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            });
            const data = await res.json().catch(() => ({ error: 'رد السيرفر غير صالح.' }));

            if (!res.ok || data.error) {
                Swal.fire('خطأ', data.error || 'فشل تنفيذ الإلغاء.', 'error');
                return;
            }

            await Swal.fire({ icon: 'success', title: 'تم بنجاح', text: data.message || 'تم الإلغاء.', timer: 2500, showConfirmButton: false });
            location.reload();
        } catch (e) {
            Swal.fire('خطأ', 'فشل الاتصال بالسيرفر: ' + (e.message || ''), 'error');
        }
    }

    // ── إلغاء تعديل رأس مال ──
    async function cancelCapitalAdj(adjId) {
        const { value: pin } = await Swal.fire({
            title: '🔒 إلغاء تعديل رأس المال',
            text: 'أدخل كلمة مرور حسابك (أدمن) للتأكيد:',
            input: 'password',
            showCancelButton: true,
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'تراجع',
            confirmButtonColor: '#dc2626',
            inputAttributes: { autocomplete: 'current-password' },
        });
        if (!pin) return;

        const confirm = await Swal.fire({
            icon: 'warning',
            title: 'تأكيد الإلغاء',
            html: `<p>سيتم تعليم العملية <b>كملغاة</b> وعكس أثرها على رصيد الخزنة.</p>
                   <p class="small text-muted">إضافة → خصم من الخزنة | صرف → إرجاع للخزنة</p>`,
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'نعم، ألغِ العملية',
            cancelButtonText: 'تراجع',
        });
        if (!confirm.isConfirmed) return;

        Swal.fire({ title: 'جاري الإلغاء...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const fd = new FormData();
            fd.append('_token', CSRF);
            fd.append('adj_id', adjId);
            fd.append('admin_pin', pin);

            const res = await fetch('{{ route('financial.capitalAdjustCancel') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            });
            const data = await res.json().catch(() => ({ error: 'رد السيرفر غير صالح.' }));

            if (!res.ok || data.error) {
                Swal.fire('خطأ', data.error || 'فشل تنفيذ الإلغاء.', 'error');
                return;
            }

            await Swal.fire({ icon: 'success', title: 'تم بنجاح', text: 'تم إلغاء العملية وعكس أثرها.', timer: 2200, showConfirmButton: false });
            location.href = '{{ route('financial.index') }}?tab=capital';
        } catch (e) {
            Swal.fire('خطأ', 'فشل الاتصال بالسيرفر: ' + (e.message || ''), 'error');
        }
    }

    // ── تفعيل تابة معينة حسب ?tab= في الرابط (للحفاظ على التابة بعد التصفية) ──
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab === 'capital') {
            const btn = document.querySelector('[data-bs-target="#tab-capital"]');
            if (btn && window.bootstrap) new bootstrap.Tab(btn).show();
        }
    });

    // ── طباعة تفاصيل حركة مالية واحدة ──
    function printTxDetails() {
        const d = _currentTxData;
        if (!d || !d.id) return;
        const cancelledBadge = d.status === 'cancelled'
            ? '<div style="margin-top:15px;padding:10px;background:#fef2f2;border:2px solid #ef4444;border-radius:8px;color:#991b1b;font-weight:900;text-align:center;">⚠️ حركة ملغاة ومعكوسة</div>'
            : '';
        const html = `
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>تفاصيل الحركة #${d.id}</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Cairo', sans-serif; padding: 30px; color: #1e293b; background: #fff; max-width: 700px; margin: 0 auto; }
                .header { text-align: center; border-bottom: 3px solid #1e293b; padding-bottom: 16px; margin-bottom: 24px; }
                .header h1 { margin: 0; font-size: 22px; color: #0f172a; font-weight: 900; }
                .header p { margin: 6px 0 0 0; font-size: 13px; color: #64748b; font-weight: 700; }
                .amt-box { text-align:center; background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; padding:20px; margin-bottom:20px; }
                .amt-box .amt { font-size: 36px; font-weight: 900; color: #0f172a; margin-bottom: 8px; }
                .amt-box .type { font-size: 14px; color: #64748b; font-weight: 800; background:#e2e8f0; padding:4px 12px; border-radius:50px; display:inline-block; }
                table { width: 100%; border-collapse: collapse; font-size: 14px; }
                td { padding: 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
                td.lbl { color: #64748b; font-weight: 700; width: 35%; }
                td.val { color: #0f172a; font-weight: 800; text-align: left; }
                .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; font-weight: 700; border-top: 1px dashed #cbd5e1; padding-top: 12px; }
            @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
        </head>
        <body>
            <div class="header">
                <h1>شركة الضبع - تفاصيل حركة مالية</h1>
                <p>تاريخ الطباعة: ${new Date().toLocaleString('ar-EG')}</p>
            </div>
            <div class="amt-box">
                <div class="amt">${d.amt} ج.م</div>
                <div class="type">${d.type}</div>
            </div>
            <table>
                <tr><td class="lbl">رقم القيد</td><td class="val" dir="ltr">#${d.id}</td></tr>
                <tr><td class="lbl">تاريخ التنفيذ</td><td class="val" dir="ltr">${d.date}</td></tr>
                <tr><td class="lbl">الطرف الخارجي</td><td class="val">${d.person}</td></tr>
                <tr><td class="lbl">سحب من (الخزنة)</td><td class="val">${d.from}</td></tr>
                <tr><td class="lbl">إيداع إلى (الخزنة)</td><td class="val">${d.to}</td></tr>
                <tr><td class="lbl">البيان</td><td class="val">${d.notes}</td></tr>
            </table>
            ${cancelledBadge}
            <div class="footer">تم إنشاء هذا التقرير آلياً بواسطة نظام تخطيط موارد المؤسسات (ERP) - شركة الضبع</div>
        </body>
        </html>`;
        const w = window.open('', '', 'width=800,height=900');
        w.document.write(html);
        w.document.close();
        setTimeout(() => { w.print(); w.close(); }, 500);
    }

    // ── طباعة تفاصيل نشاط رادار ──
    let _currentLogData = {};
    function printLogDetails() {
        const d = _currentLogData;
        if (!d || !d.id) return;
        const html = `
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>تفاصيل نشاط #${d.id}</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Cairo', sans-serif; padding: 30px; color: #1e293b; background: #fff; max-width: 700px; margin: 0 auto; }
                .header { text-align: center; border-bottom: 3px solid #dc2626; padding-bottom: 16px; margin-bottom: 24px; }
                .header h1 { margin: 0; font-size: 22px; color: #0f172a; font-weight: 900; }
                .header p { margin: 6px 0 0 0; font-size: 13px; color: #64748b; font-weight: 700; }
                table { width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px; }
                td { padding: 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
                td.lbl { color: #64748b; font-weight: 700; width: 35%; }
                td.val { color: #0f172a; font-weight: 800; text-align: left; }
                .desc-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px; font-size:15px; font-weight:700; line-height:1.8; color:#1e293b; white-space:pre-wrap; }
                .desc-lbl { font-size: 13px; color: #64748b; font-weight: 800; margin-bottom: 10px; display: block; }
                .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; font-weight: 700; border-top: 1px dashed #cbd5e1; padding-top: 12px; }
            @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
        </head>
        <body>
            <div class="header">
                <h1>شركة الضبع - تفاصيل نشاط النظام (الرادار)</h1>
                <p>تاريخ الطباعة: ${new Date().toLocaleString('ar-EG')}</p>
            </div>
            <table>
                <tr><td class="lbl">رقم السجل</td><td class="val" dir="ltr">#${d.id}</td></tr>
                <tr><td class="lbl">المستخدم</td><td class="val">${d.user}</td></tr>
                <tr><td class="lbl">قسم النظام</td><td class="val">${d.module}</td></tr>
                <tr><td class="lbl">وقت النشاط</td><td class="val" dir="ltr">${d.date}</td></tr>
            </table>
            <span class="desc-lbl">الوصف الكامل للنشاط:</span>
            <div class="desc-box">${d.desc}</div>
            <div class="footer">تم إنشاء هذا التقرير آلياً بواسطة نظام تخطيط موارد المؤسسات (ERP) - شركة الضبع</div>
        </body>
        </html>`;
        const w = window.open('', '', 'width=800,height=900');
        w.document.write(html);
        w.document.close();
        setTimeout(() => { w.print(); w.close(); }, 500);
    }

    // ── فتح تفاصيل نشاط الرادار ──
    function openLogDetailsModal(item) {
        _currentLogData = {
            id:     item.getAttribute('data-id'),
            user:   item.getAttribute('data-user'),
            module: item.getAttribute('data-module'),
            date:   item.getAttribute('data-date'),
            desc:   item.getAttribute('data-desc'),
        };
        document.getElementById('logD_id').innerText = '#' + _currentLogData.id;
        document.getElementById('logD_user').innerText = _currentLogData.user;
        document.getElementById('logD_date').innerText = _currentLogData.date;
        document.getElementById('logD_module').innerText = _currentLogData.module;
        document.getElementById('logD_desc').innerText = _currentLogData.desc;
        new bootstrap.Modal(document.getElementById('logDetailsModal')).show();
    }

    // 🖨️ سكربت الطباعة الشيك جداً لملخص اليوم
    function printDailySummary() {
        let printContents = `
        <html dir="rtl" lang="ar">
        <head>
            <title>تقرير عمليات اليوم - شركة الضبع</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Cairo', sans-serif; padding: 40px; color: #1e293b; background: #fff; }
                .header { text-align: center; border-bottom: 3px solid #1e293b; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 28px; color: #0f172a; font-weight: 900; }
                .header p { margin: 5px 0 0 0; font-size: 16px; color: #64748b; font-weight: 700; }
                
                .stats-grid { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 30px; }
                .stat-box { flex: 1; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; }
                .stat-box.green { border-top: 4px solid #10b981; background: #f0fdf4; }
                .stat-box.red { border-top: 4px solid #ef4444; background: #fef2f2; }
                .stat-box.blue { border-top: 4px solid #3b82f6; background: #eff6ff; }
                .stat-box h4 { margin: 0 0 8px 0; font-size: 14px; color: #475569; font-weight: 800; }
                .stat-box h2 { margin: 0; font-size: 22px; color: #0f172a; font-weight: 900; }
                
                .section-title { font-size: 18px; font-weight: 800; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; color: #1e293b; }
                
                table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 13px; }
                th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: center; font-weight: 700; }
                th { background-color: #f1f5f9; color: #334155; }
                
                .radar-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .radar-item { padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; background:#f8fafc; }
                .radar-time { float: left; color: #64748b; direction: ltr; font-size: 11px; }
            @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
        </head>
        <body>
            <div class="header">
                <h1>شركة الضبع - التقرير المالي والإداري الشامل</h1>
                <p>تاريخ استخراج التقرير: ${new Date().toLocaleString('ar-EG')}</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box green"><h4>إجمالي الإيداعات</h4><h2>{{ fmtMoney($total_income) }} ج.م</h2></div>
                <div class="stat-box red"><h4>إجمالي المصروفات</h4><h2>{{ fmtMoney($total_expense) }} ج.م</h2></div>
                <div class="stat-box blue"><h4>التحويلات الداخلية</h4><h2>{{ fmtMoney($total_transfer) }} ج.م</h2></div>
            </div>

            <div class="section-title">تفاصيل الحركات المالية للخزائن</div>
            <table>
                <thead>
                    <tr>
                        <th>م</th>
                        <th>نوع الحركة</th>
                        <th>المبلغ</th>
                        <th>الطرف الخارجي</th>
                        <th>البيان</th>
                        <th>الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    ${document.querySelector('#financeTable tbody').innerHTML.replace(/<td class="no-print">.*?<\/td>/g, '')}
                </tbody>
            </table>

            <div class="section-title">رادار النظام (أبرز النشاطات الإدارية المباشرة)</div>
            <div class="radar-grid">
                ${Array.from(document.querySelectorAll('.radar-item')).slice(0, 16).map(el => {
                    let user = el.querySelector('.radar-user')?.innerText || '';
                    let time = el.querySelector('.radar-time')?.innerText || '';
                    let text = el.querySelector('.radar-text')?.innerText || '';
                    return `<div class="radar-item">
                                <span class="radar-time">${time}</span>
                                <b style="color:#0f172a;">${user}:</b> ${text}
                            </div>`;
                }).join('')}
            </div>
            
            <div style="margin-top: 40px; text-align: center; color: #94a3b8; font-size: 12px; font-weight:700;">
                تم إنشاء وإصدار هذا التقرير آلياً بواسطة نظام تخطيط موارد المؤسسات (ERP) - شركة الضبع
            </div>
        </body>
        </html>`;

        let printWin = window.open('', '', 'width=900,height=700');
        printWin.document.write(printContents);
        printWin.document.close();
        setTimeout(() => { printWin.print(); printWin.close(); }, 800);
    }
</script>
</body>
</html>