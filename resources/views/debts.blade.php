<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مستحقات العملاء - شركة الضبع</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
    body { background: #f4f7fb; color: #1e293b; overflow-x: hidden; }
    .main-content { margin-right: 260px; padding: 25px; min-height: 100vh; }
    
    .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .title h1 { font-size: 32px; font-weight: 800; margin-bottom: 5px; color: #0f172a; }
    .title p { color: #64748b; font-size: 15px; margin: 0; font-weight: 600; }
    
    .actions { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
    .btn-custom { border: none; padding: 10px 20px; border-radius: 12px; cursor: pointer; font-size: 14px; font-weight: 700; transition: 0.3s ease; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;}
    .btn-primary-custom { background: #2563eb; color: white; }
    .btn-primary-custom:hover { background: #1d4ed8; color: white; }

    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 25px; }
    .stat-card { background: white; padding: 22px; border-radius: 20px; box-shadow: 0 8px 25px rgba(15, 23, 42, 0.04); position: relative; overflow: hidden; transition: transform 0.2s;}
    .stat-card:hover { transform: translateY(-3px); }
    .stat-card::before { content: ""; position: absolute; top: 0; right: 0; width: 6px; height: 100%; }
    .stat-card.blue::before { background: #2563eb; }
    .stat-card.green::before { background: #16a34a; }
    .stat-card.orange::before { background: #ea580c; }
    .stat-card.red::before { background: #dc2626; }
    .stat-card h3 { color: #64748b; font-size: 15px; margin-bottom: 12px; font-weight: 700; }
    .stat-card .value { font-size: 28px; font-weight: 800; margin-bottom: 8px; color: #0f172a; }
    .stat-card span { color: #94a3b8; font-size: 13px; font-weight: 600; }

    .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 22px; }
    @media(max-width: 1200px) { .content-grid { grid-template-columns: 1fr; } }
    @media(max-width: 991px) {
        .main-content { margin-right: 0 !important; width: 100% !important; padding: 70px 16px 30px !important; }
    }
    @media(max-width: 768px) {
        /* header */
        .topbar { flex-direction: column; align-items: flex-start; gap: 10px; }
        .title h1 { font-size: 1.3rem !important; }
        .title p { font-size: 0.82rem; }
        .actions { width: 100%; }
        .actions form { width: 100%; justify-content: flex-start; }
        .btn-custom { padding: 8px 12px !important; font-size: 0.8rem !important; }

        /* stat cards: 2 per row */
        .cards { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-card { padding: 14px !important; }
        .stat-card .value { font-size: 1.3rem !important; }
        .stat-card h3 { font-size: 0.75rem; }
        .stat-card span { font-size: 0.68rem; }

        /* table content */
        .search-wrapper { min-width: 100%; }
        .section-header { flex-direction: column; align-items: flex-start; }
        .section-header h2 { font-size: 1rem; }
        .table-box, .side-box { padding: 14px; }
        .custom-table th, .custom-table td { padding: 10px 8px; font-size: 0.8rem; }
        .client-avatar { width: 32px; height: 32px; font-size: 0.82rem; }
    }
    @media(max-width: 480px) {
        .cards { grid-template-columns: 1fr; }
        .custom-table th, .custom-table td { padding: 8px 6px; font-size: 0.75rem; }
        /* نطاق التاريخ المخصص: تغليف على شاشتين */
        #custom_range_container { flex-wrap: wrap !important; gap: 4px !important; }
        #custom_range_container input[type="date"] { max-width: 48% !important; flex: 1 1 40%; }
        #custom_range_container label { font-size: 0.75rem; }
    }
    
    .table-box, .side-box { background: white; border-radius: 20px; padding: 22px; box-shadow: 0 8px 25px rgba(15, 23, 42, 0.04); }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px; }
    .section-header h2 { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; }

    .search-wrapper { position: relative; min-width: 280px; }
    .search-box { display: flex; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 16px; border-radius: 12px; transition: 0.2s; }
    .search-box:focus-within { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .search-box i { color: #94a3b8; font-size: 16px; margin-left: 10px; }
    .search-box input { border: none; outline: none; background: transparent; width: 100%; font-size: 14px; font-weight: 600; color: #1e293b; }

    .custom-table { width: 100%; border-collapse: collapse; }
    .custom-table thead { background: #f8fafc; border-radius: 12px; }
    .custom-table th { padding: 16px; text-align: right; font-size: 13px; color: #64748b; font-weight: 800; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .custom-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; font-weight: 600; vertical-align: middle; cursor: pointer; transition: 0.2s; }
    .custom-table tbody tr:hover td { background: #f8fafc; }
    
    .client-wrap { display: flex; align-items: center; gap: 12px; }
    .client-avatar { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 16px; flex-shrink: 0; }
    .client-info strong { display: block; color: #0f172a; font-size: 15px; margin-bottom: 2px; }
    .client-info small { color: #64748b; font-size: 12px; }

    .status-badge { padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 800; display: inline-block; white-space: nowrap; }
    .status-paid { background: #dcfce7; color: #16a34a; }
    .status-pending { background: #fef3c7; color: #d97706; }

    .progress-item { margin-bottom: 22px; }
    .progress-top { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; font-weight: 700; color: #334155; }
    .progress-bar-bg { width: 100%; height: 8px; background: #f1f5f9; border-radius: 20px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 20px; }
    .fill-green { background: #16a34a; } .fill-orange { background: #ea580c; }

    .activity-list { margin-top: 30px; }
    .activity-item { display: flex; gap: 14px; margin-bottom: 20px; align-items: flex-start; }
    .activity-icon { width: 40px; height: 40px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .activity-content h4 { font-size: 14px; margin-bottom: 4px; font-weight: 800; color: #0f172a; }
    .activity-content p { color: #64748b; font-size: 13px; margin: 0 0 4px 0; font-weight: 600; line-height: 1.4; }
    .activity-content span { color: #94a3b8; font-size: 11px; font-weight: 700; }

    .v-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
    .v-table th, .v-table td { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
    .v-table th { background: #f1f5f9; width: 35%; text-align: right; color: #64748b; font-weight: 800; }
    .v-table td { background: #fff; color: #1e293b; font-weight: 700; }

    .btn-action { font-size: 13px; padding: 8px 16px; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; transition: 0.2s;}
    .btn-action.pay { background: #eff6ff; color: #2563eb; } .btn-action.pay:hover { background: #2563eb; color: white; }
    .btn-action.disc { background: #fef3c7; color: #d97706; } .btn-action.disc:hover { background: #d97706; color: white; }
    .btn-action.ret { background: #fee2e2; color: #dc2626; } .btn-action.ret:hover { background: #dc2626; color: white; }

    .inner-clickable-row { cursor: pointer; transition: all 0.2s; }
    .inner-clickable-row:hover { background: #f8fafc !important; transform: scale(1.005); }
    .sweet-details-box p { margin-bottom: 8px; font-size: 15px; }
    .sweet-details-box ul { padding-right: 20px; font-size: 14px; }

    .print-footer { display: none; }
    @media print {
        .no-print { display: none !important; }
        .print-footer { display: block; margin-top: 40px; text-align: center; }
        .accordion-collapse { display: block !important; } /* لفتح كل التفاصيل وقت الطباعة */
        .accordion-button::after { display: none !important; }
        body { background: #fff !important; }
    }
  </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    @php
        $allDebts = collect($debts);
        $groupedDebts = $allDebts->groupBy('customer_name');
        $personsFormatted = collect();
        $recentActivities = collect();

        foreach($groupedDebts as $name => $contracts) {
            foreach($contracts as $contract) {
                $contract->payments = \Illuminate\Support\Facades\DB::table('installment_payments')
                    ->leftJoin('accounts', 'installment_payments.payment_method_id', '=', 'accounts.id')
                    ->where('installment_id', $contract->id)
                    ->select('installment_payments.*', 'accounts.account_name')
                    ->orderBy('payment_date', 'desc')
                    ->get();
                
                $recentActivities->push((object)[
                    'type' => 'contract', 'customer' => $name, 'amount' => $contract->total_after_interest, 'date' => $contract->created_at, 'desc' => "تم فتح عملية بيع بقيمة " . number_format($contract->total_after_interest, 2) . " ج"
                ]);
                
                foreach($contract->payments as $pay) {
                    $recentActivities->push((object)[
                        'type' => 'payment', 'customer' => $name, 'amount' => $pay->amount_paid, 'date' => $pay->payment_date, 'desc' => "سدد دفعة نقدية بقيمة " . number_format($pay->amount_paid, 2) . " ج"
                    ]);
                }
            }

            $first = $contracts->first();
            $personsFormatted->push((object)[
                'customer_name'   => $name,
                'customer_phone'  => $first->customer_phone,
                'contracts_count' => $contracts->count(),
                'active_count'    => $contracts->where('remaining_balance', '>', 0)->count(),
                'total_amount'    => $contracts->sum('cash_price'),
                'total_paid'      => $contracts->sum('total_after_interest') - $contracts->sum('remaining_balance'),
                'total_remaining' => $contracts->sum('remaining_balance'),
                'contracts'       => $contracts,
            ]);
        }
        
        // الترتيب ليظهر أصحاب الديون النشطة أولاً
        $persons = $personsFormatted->sortByDesc('total_remaining')->values();
        
        if (isset($status) && $status === 'paid') { $persons = $persons->filter(fn($p) => $p->total_remaining <= 0); }
        elseif (!isset($status) || $status === 'active') { $persons = $persons->filter(fn($p) => $p->total_remaining > 0); }
        $persons = $persons->values();

        $recentDiscounts = \Illuminate\Support\Facades\DB::table('financial_transactions')->where('type', 'discount')->orderBy('created_at', 'desc')->limit(10)->get();
        foreach($recentDiscounts as $disc) {
            $recentActivities->push((object)[
                'type' => 'discount', 'customer' => 'خصم تسوية', 'amount' => $disc->amount, 'date' => $disc->created_at, 'desc' => $disc->notes
            ]);
        }
        
        $total_debts_out = $allDebts->sum('remaining_balance');
        $total_collected = $allDebts->sum('total_after_interest') - $allDebts->sum('remaining_balance');
        $total_money = $total_debts_out + $total_collected;
        $colPct      = $total_money > 0 ? ($total_collected / $total_money) * 100 : 0;
        $activePct   = $total_money > 0 ? ($total_debts_out / $total_money) * 100 : 0;
        
        $active_debtors_count = $personsFormatted->filter(fn($p) => $p->total_remaining > 0)->count();
        $cleared_count        = $personsFormatted->filter(fn($p) => $p->total_remaining <= 0)->count();

        $latestActivities = $recentActivities->sortByDesc('date')->take(5);
    @endphp

    @if(session('success')) <div class="alert alert-success fw-bold rounded-3 mb-4"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger fw-bold rounded-3 mb-4"><i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}</div> @endif

    <div class="topbar">
        <div class="title">
            <h1>مستحقات العملاء</h1>
            <p>إدارة الفواتير والمدفوعات الخاصة بالعملاء</p>
        </div>
        <div class="actions">
            <form id="time_filter_form" method="GET" class="d-flex align-items-center gap-2 bg-white p-2 rounded-3 shadow-sm border flex-wrap">
                <input type="hidden" name="status" value="{{ request('status', 'active') }}">
           @php $tf = request('time_filter', 'all'); @endphp
           <select name="time_filter" id="mainTimeFilter" class="form-select fw-bold border-0 bg-transparent text-dark" onchange="toggleMainDateInputs(this.value)" style="outline:none; box-shadow:none; cursor:pointer; min-width: 130px;">
                    <option value="all"       {{ $tf == 'all'       ? 'selected' : '' }}>كل السجلات</option>
                    <option value="today"     {{ $tf == 'today'     ? 'selected' : '' }}>اليوم فقط</option>
                    <option value="yesterday" {{ $tf == 'yesterday' ? 'selected' : '' }}>أمس</option>
                    <option value="week"      {{ $tf == 'week'      ? 'selected' : '' }}>هذا الأسبوع</option>
                    <option value="month"     {{ $tf == 'month'     ? 'selected' : '' }}>هذا الشهر</option>
                    <option value="year"      {{ $tf == 'year'      ? 'selected' : '' }}>هذا العام</option>
                    <option value="custom"    {{ $tf == 'custom'    ? 'selected' : '' }}>نطاق مخصص...</option>
                </select>

                {{-- نطاق مخصص: من - إلى --}}
                <div id="custom_range_container" class="d-flex align-items-center gap-1" style="display: {{ request('time_filter') == 'custom' ? 'flex' : 'none' }} !important;">
                    <label class="fw-bold text-muted small mb-0">من:</label>
                    <input type="date" name="custom_from" id="customFromDate"
                           class="form-control form-control-sm rounded"
                           value="{{ request('custom_from') }}"
                           style="max-width:145px;"
                           onchange="autoSubmitIfBothDates()">
                    <label class="fw-bold text-muted small mb-0">إلى:</label>
                    <input type="date" name="custom_to" id="customToDate"
                           class="form-control form-control-sm rounded"
                           value="{{ request('custom_to') }}"
                           style="max-width:145px;"
                           onchange="autoSubmitIfBothDates()">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-pill px-3">
                        <i class="fa fa-search me-1"></i> بحث
                    </button>
                </div>
            </form>
            <a href="?status=all&time_filter={{ request('time_filter','all') }}&custom_from={{ request('custom_from') }}&custom_to={{ request('custom_to') }}"
               class="btn-custom"
               style="{{ request('status') === 'all' ? 'background:#2563eb; color:white;' : 'background:#e2e8f0; color:#475569;' }}">
               <i class="fa fa-list"></i> الكل
            </a>
            <a href="?status=active&time_filter={{ request('time_filter','all') }}&custom_from={{ request('custom_from') }}&custom_to={{ request('custom_to') }}"
               class="btn-custom"
               style="{{ !request('status') || request('status') === 'active' ? 'background:#ea580c; color:white;' : 'background:#e2e8f0; color:#475569;' }}">
               <i class="fa fa-fire"></i> الديون النشطة
            </a>
            <a href="?status=paid&time_filter={{ request('time_filter','all') }}&custom_from={{ request('custom_from') }}&custom_to={{ request('custom_to') }}"
               class="btn-custom"
               style="{{ request('status') === 'paid' ? 'background:#16a34a; color:white;' : 'background:#e2e8f0; color:#475569;' }}">
               <i class="fa fa-check-circle"></i> المسدد
            </a>
        </div>
    </div>

    <div class="cards">
        <div class="stat-card blue"><h3>إجمالي المستحقات (المتبقي لنا)</h3><div class="value">{{ number_format($total_debts_out, 2) }} ج.م</div><span>المبالغ المتبقية بالسوق للفترة المحددة</span></div>
        <div class="stat-card green"><h3>المبالغ المحصلة</h3><div class="value">{{ number_format($total_collected, 2) }} ج.م</div><span>{{ number_format($colPct, 1) }}% من إجمالي الحسابات</span></div>
        <div class="stat-card orange"><h3>حسابات نشطة</h3><div class="value">{{ $active_debtors_count }} عميل</div><span>لديهم مستحقات معلقة</span></div>
        <div class="stat-card red"><h3>حسابات مكتملة السداد</h3><div class="value">{{ $cleared_count }} عميل</div><span>أنهوا كافة ديونهم</span></div>
    </div>

    <div class="content-grid">
        <div class="table-box">
            <div class="section-header">
                <h2>قائمة العملاء</h2>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-wrapper">
                        <div class="search-box"><i class="fa fa-search"></i><input type="text" id="searchInput" placeholder="ابحث باسم العميل أو التليفون..." autocomplete="off"></div>
                    </div>
                    <button class="btn-custom" style="background:#0f172a; color:#fff;" onclick="printDebtsClientsList()">
                        <i class="fa fa-print"></i> طباعة القائمة
                    </button>
                </div>
            </div>

            @php $currentStatus = request('status', 'active'); @endphp

            <div style="overflow-x: auto;">
                <table class="custom-table">
                    <thead><tr><th>العميل</th><th>عدد العمليات</th><th>إجمالي الحساب</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th></tr></thead>
                    <tbody id="clientsTable">
                        @php
                            if ($currentStatus === 'paid') {
                                $filteredPersons = $persons->filter(fn($p) => $p->total_remaining <= 0);
                            } elseif ($currentStatus === 'active') {
                                $filteredPersons = $persons->filter(fn($p) => $p->total_remaining > 0);
                            } else {
                                // 'all' (الافتراضي): عرض كل السجلات بدون استبعاد أي عميل
                                $filteredPersons = $persons;
                            }
                            $filteredPersons = $filteredPersons->values();
                        @endphp

                        @forelse($filteredPersons as $idx => $person)
                            @php
                                $personKey     = 'p'.$idx;
                                $personModalKey = 'c'.md5($person->customer_name);
                                $colors = ['linear-gradient(135deg, #2563eb, #3b82f6)', 'linear-gradient(135deg, #ea580c, #f97316)', 'linear-gradient(135deg, #7c3aed, #8b5cf6)'];
                                $isPaid  = $person->total_remaining <= 0;
                                $bg      = $isPaid ? 'linear-gradient(135deg, #059669, #10b981)' : $colors[$idx % 3];
                            @endphp
                            <tr class="client-row"
                                data-name="{{ $person->customer_name }}"
                                data-phone="{{ $person->customer_phone ?? '—' }}"
                                data-contracts="{{ $person->contracts_count }}"
                                data-total="{{ $person->total_amount }}"
                                data-paid="{{ $person->total_paid }}"
                                data-remaining="{{ $isPaid ? 0 : $person->total_remaining }}"
                                data-status="{{ $isPaid ? 'مكتمل' : 'قيد الانتظار' }}"
                                onclick="new bootstrap.Modal(document.getElementById('detailsModal{{ $personModalKey }}')).show();">
                                <td>
                                    <div class="client-wrap">
                                        <div class="client-avatar" style="background: {{ $bg }};">{{ mb_substr($person->customer_name, 0, 1, 'UTF-8') }}</div>
                                        <div class="client-info"><strong>{{ $person->customer_name }}</strong><small dir="ltr">{{ $person->customer_phone ?? 'بدون رقم' }}</small></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border px-2 py-1 fs-6">{{ $person->contracts_count }}</span></td>
                                <td>{{ number_format($person->total_amount, 2) }} ج</td>
                                <td style="color:#16a34a;">{{ number_format($person->total_paid, 2) }} ج</td>
                                <td style="color:{{ $isPaid ? '#16a34a' : '#dc2626' }}; font-weight:800;">
                                    {{ $isPaid ? '0.00' : number_format($person->total_remaining, 2) }} ج
                                </td>
                                <td>
                                    <span class="status-badge {{ $isPaid ? 'status-paid' : 'status-pending' }}">
                                        {{ $isPaid ? 'مكتمل' : 'قيد الانتظار' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted fw-bold">
                                <i class="fa fa-{{ $currentStatus === 'paid' ? 'check-circle' : ($currentStatus === 'active' ? 'clock' : 'inbox') }} fa-2x mb-2 d-block opacity-25"></i>
                                {{ $currentStatus === 'paid' ? 'لا يوجد ديون مسددة للفترة المحددة' : ($currentStatus === 'active' ? 'لا يوجد ديون نشطة للفترة المحددة' : 'لا يوجد أي سجلات للفترة المحددة') }}
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="side-box mb-4">
                <div class="section-header"><h2>تحليل التحصيل العام</h2></div>
                <div class="progress-item">
                    <div class="progress-top"><span>المدفوعات المكتملة</span><span>{{ number_format($colPct, 1) }}%</span></div>
                    <div class="progress-bar-bg"><div class="progress-fill fill-green" style="width:{{ $colPct }}%"></div></div>
                </div>
                <div class="progress-item">
                    <div class="progress-top"><span>ديون قيد الانتظار السوقية</span><span>{{ number_format($activePct, 1) }}%</span></div>
                    <div class="progress-bar-bg"><div class="progress-fill fill-orange" style="width:{{ $activePct }}%"></div></div>
                </div>
            </div>

            <div class="side-box">
                <div class="section-header"><h2>آخر النشاطات والتسويات</h2></div>
                <div class="activity-list">
                    @forelse($latestActivities as $act)
                    <div class="activity-item">
                        <div class="activity-icon" style="{{ $act->type=='payment' ? 'color:#16a34a; background:#dcfce7;' : ($act->type=='discount' ? 'color:#d97706; background:#fef3c7;' : '') }}"><i class="fa {{ $act->type=='payment' ? 'fa-check' : ($act->type=='discount' ? 'fa-percent' : 'fa-plus') }}"></i></div>
                        <div class="activity-content">
                            <h4>{{ $act->type=='payment' ? 'تحصيل نقدية' : ($act->type=='discount' ? 'خصم ممنوح' : 'عملية جديدة') }}</h4>
                            <p>{{ $act->type=='discount' ? $act->desc : ($act->customer . ' - ' . $act->desc) }}</p>
                            <span>{{ \Carbon\Carbon::parse($act->date)->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty <p class="text-muted text-center fw-bold small">لا توجد نشاطات</p> @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($persons as $idx => $person)
@php
    $personKey = 'p'.$idx;
    $personModalKey = 'c'.md5($person->customer_name);
    $isActive = $person->total_remaining > 0;

    $waCompany  = \App\Services\SystemSetting::get('company_name', 'شركة الضبع');
    $waCurrency = \App\Services\SystemSetting::get('currency_symbol', 'ج');
    $waPrefix   = \App\Services\SystemSetting::get('whatsapp_country_prefix', '2');
    $waTemplate = \App\Services\SystemSetting::get('whatsapp_reminder_template',
        "مرحباً أستاذ {customer}،\nتذكير من {company} بخصوص الحساب المتبقي.\nالمبلغ المتبقي: {amount} {currency}.\nشكراً لتعاملكم معنا.");

    $whatsappMsg = strtr($waTemplate, [
        '{customer}' => $person->customer_name,
        '{company}'  => $waCompany,
        '{amount}'   => number_format($person->total_remaining, 2),
        '{currency}' => $waCurrency,
    ]);
    $whatsappUrl = "https://wa.me/" . $waPrefix . ltrim($person->customer_phone ?? '', '0') . "?text=" . urlencode($whatsappMsg);
@endphp

<div class="modal fade" id="detailsModal{{ $personModalKey }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-0 pb-3 d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title fw-bold fs-4 mb-2 text-dark"><i class="fa fa-user-circle text-primary me-2"></i>{{ $person->customer_name }}</h5>
                    <div class="d-flex gap-3 align-items-center">
                        <span class="badge bg-danger fs-6 fw-bold shadow-sm">المتبقي: {{ number_format($person->total_remaining, 2) }} ج</span>
                        <span class="badge bg-success fs-6 fw-bold shadow-sm">المدفوع: {{ number_format($person->total_paid, 2) }} ج</span>
                    </div>
                </div>
                
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-dark fw-bold rounded-pill px-3 shadow-sm" onclick="printCustomerDetails('{{ $personModalKey }}', '{{ $person->customer_name }}', '{{ number_format($person->total_remaining, 2) }}')">
                        <i class="fa fa-print me-1"></i> طباعة التفاصيل
                    </button>
                    @if($isActive)
                    <button class="btn btn-warning fw-bold rounded-pill px-3 shadow-sm text-dark" onclick="openPartialPayModal('{{ $person->customer_name }}', {{ $person->total_remaining }})">
                        <i class="fa fa-money-bill-wave me-1"></i> سداد جزئي
                    </button>
                    <button class="btn btn-success fw-bold rounded-pill px-4 shadow-sm" onclick="openGlobalBulkInstModal('{{ $person->customer_name }}', {{ $person->total_remaining }})">
                        <i class="fa fa-money-bills me-1"></i> سداد كلي للعميل
                    </button>
                    @endif
                    <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-outline-success rounded-pill px-3 fw-bold" title="واتساب"><i class="fa-brands fa-whatsapp fs-5"></i></a>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body p-4 bg-white" style="max-height:75vh; overflow-y:auto;">
                
                {{-- فلاتر جوه المودال --}}
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    {{-- تابتين --}}
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary fw-bold btn-sm modal-tab-btn active" 
                                onclick="setModalTab('{{ $personModalKey }}', 'active', this)">
                            <i class="fa fa-fire me-1"></i> النشط
                        </button>
                        <button type="button" class="btn btn-outline-success fw-bold btn-sm modal-tab-btn" 
                                onclick="setModalTab('{{ $personModalKey }}', 'paid', this)">
                            <i class="fa fa-check-circle me-1"></i> المسدد
                        </button>
                        <button type="button" class="btn btn-outline-secondary fw-bold btn-sm modal-tab-btn" 
                                onclick="setModalTab('{{ $personModalKey }}', 'all', this)">
                            <i class="fa fa-list me-1"></i> الكل
                        </button>
                    </div>

                  {{-- فلتر التاريخ السريع --}}
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-dark fw-bold btn-sm modal-date-btn"
                                onclick="setModalDate('{{ $personModalKey }}', 'today', this)">اليوم</button>
                        <button type="button" class="btn btn-outline-dark fw-bold btn-sm modal-date-btn"
                                onclick="setModalDate('{{ $personModalKey }}', 'yesterday', this)">أمس</button>
                        <button type="button" class="btn btn-outline-dark fw-bold btn-sm modal-date-btn"
                                onclick="setModalDate('{{ $personModalKey }}', 'week', this)">أسبوع</button>
                        <button type="button" class="btn btn-outline-dark fw-bold btn-sm modal-date-btn"
                                onclick="setModalDate('{{ $personModalKey }}', 'month', this)">شهر</button>
                        <button type="button" class="btn btn-dark fw-bold btn-sm modal-date-btn active"
                                onclick="setModalDate('{{ $personModalKey }}', 'all', this)">الكل</button>
                    </div>

                    {{-- نطاق تاريخ مخصص --}}
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <small class="fw-bold text-muted">من:</small>
                        <input type="date" class="form-control form-control-sm fw-bold border-dark rounded"
                               style="max-width:140px;"
                               id="modalDateFrom_{{ $personModalKey }}"
                               onchange="setModalDateRange('{{ $personModalKey }}')">
                        <small class="fw-bold text-muted">إلى:</small>
                        <input type="date" class="form-control form-control-sm fw-bold border-dark rounded"
                               style="max-width:140px;"
                               id="modalDateTo_{{ $personModalKey }}"
                               onchange="setModalDateRange('{{ $personModalKey }}')">
                    </div>
                </div>

                <div class="mb-3 px-1">
                    <div class="search-box bg-light border border-secondary-subtle rounded-pill px-3 py-2 shadow-sm d-flex align-items-center">
                        <i class="fa fa-search text-primary"></i>
                        <input type="text" class="border-0 bg-transparent w-100 ms-2 fw-bold text-dark" placeholder="ابحث في عمليات هذا العميل (اسم العملية، تاريخ)..." oninput="filterModalOperations('{{ $personModalKey }}', this.value)">
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                        <span class="badge bg-primary fs-6 fw-bold">إجمالي المتبقي: <span id="sumDisplay{{ $personModalKey }}">{{ number_format($person->total_remaining, 2) }}</span> ج</span>
                        @if($person->contracts->where('category', 'بنزينة')->count() > 0)
                        <span class="badge bg-warning text-dark fs-6 fw-bold">
                            <i class="fa fa-gas-pump me-1"></i> إجمالي الليترات: <span id="litersDisplay{{ $personModalKey }}">{{ fmtMoney($person->contracts->sum('fuel_liters')) }}</span> لتر
                        </span>
                        <span class="badge bg-info text-dark fs-6 fw-bold">
                            <i class="fa fa-wallet me-1"></i> إجمالي العهد: <span id="custodyDisplay{{ $personModalKey }}">{{ number_format($person->contracts->sum('cash_custody'), 2) }}</span> ج
                        </span>
                        @endif
                    </div>
                </div>

                @php
                    $isFuelOnlyClient = $person->contracts->count() > 0
                                     && $person->contracts->every(fn($c) => ($c->category ?? '') === 'بنزينة');
                @endphp

                <div class="table-responsive bg-white rounded-3 shadow-sm border">
                    <table class="table table-hover text-center mb-0" id="opsTable{{ $personModalKey }}">
                        <thead style="background:#f1f5f9;">
                        @if($isFuelOnlyClient)
                            <tr>
                                <th class="text-start">السائق / السيارة</th>
                                <th><i class="fa fa-gas-pump me-1 text-warning"></i> الليترات</th>
                                <th><i class="fa fa-wallet me-1 text-info"></i> العهدة</th>
                                <th>التاريخ</th>
                                <th>إجمالي العملية</th>
                                <th>المتبقي</th>
                                <th>اسم البنزينة</th>
                                <th class="no-print-col">إجراءات</th>
                            </tr>
                            @else
                            <tr><th>التاريخ</th><th class="text-start">العملية / المنتج</th><th>إجمالي الفاتورة</th><th>المتبقي للدفع</th><th class="no-print-col">إجراءات الدفع</th></tr>
                            @endif
                        </thead>
                        <tbody>
                           @foreach($person->contracts as $ci => $contract)
                                @php
                                    $refundAmt = ($contract->calculated_paid ?? 0) + ($contract->down_payment ?? 0);
                                    $isContractActive = $contract->remaining_balance > 0;
                                    $acItemId = $personModalKey.'_c'.$ci;
                                    $isFuelOp = ($contract->category ?? '') === 'بنزينة';

                                    // 💡 فصل الاسم وعكسه (اسم السائق/السيارة أولاً ثم البنزينة)
                                    $prodParts = explode('-', $contract->product_name, 2);
                                    $displayName = count($prodParts) == 2 ? trim($prodParts[1]) . ' - ' . trim($prodParts[0]) : $contract->product_name;

                                    $detailsHtml = "<div class='sweet-details-box text-start fw-bold text-dark'>";
                                    if ($isFuelOp) {
                                        $detailsHtml .= "<p><i class='fa fa-truck text-warning me-2'></i><b>السائق/السيارة:</b> <span class='text-dark ms-1'>" . e($displayName) . "</span></p>";
                                        $detailsHtml .= "<p><i class='fa fa-gas-pump text-warning me-2'></i><b>الكمية:</b> <span class='text-warning ms-1'>" . fmtMoney($contract->fuel_liters ?? 0) . " لتر</span></p>";
                                        $detailsHtml .= "<p><i class='fa fa-wallet text-info me-2'></i><b>عهدة نقدية:</b> <span class='text-info ms-1'>" . number_format($contract->cash_custody ?? 0, 2) . " ج</span></p>";
                                        $detailsHtml .= "<p><i class='fa fa-hand-holding-dollar text-success me-2'></i><b>المقدم المدفوع:</b> <span class='text-success ms-1'>" . number_format($contract->down_payment, 2) . " ج</span></p>";
                                        if ($contract->profit > 0) $detailsHtml .= "<p><i class='fa fa-chart-line text-primary me-2'></i><b>صافي الربح المحقق:</b> <span class='text-primary ms-1'>" . number_format($contract->profit, 2) . " ج</span></p>";
                                        $detailsHtml .= "<p class='border-top pt-2 mt-2'><i class='fa fa-file-invoice-dollar text-dark me-2'></i><b>إجمالي تكلفة العملية:</b> <span class='text-dark ms-1'>" . number_format($contract->total_after_interest, 2) . " ج</span></p>";
                                    } else {
                                        $detailsHtml .= "<p><i class='fa fa-money-bill-wave text-primary me-2'></i><b>السعر الأساسي:</b> <span class='text-primary ms-1'>" . number_format($contract->cash_price, 2) . " ج</span></p>";
                                        if($contract->discount > 0) $detailsHtml .= "<p><i class='fa fa-percent text-warning me-2'></i><b>الخصم الممنوح:</b> <span class='text-warning ms-1'>" . number_format($contract->discount, 2) . " ج</span></p>";
                                        $detailsHtml .= "<p><i class='fa fa-hand-holding-dollar text-success me-2'></i><b>المقدم المدفوع:</b> <span class='text-success ms-1'>" . number_format($contract->down_payment, 2) . " ج</span></p>";
                                        $detailsHtml .= "<p class='border-top pt-2 mt-2'><i class='fa fa-file-invoice-dollar text-dark me-2'></i><b>إجمالي الفاتورة:</b> <span class='text-dark ms-1'>" . number_format($contract->total_after_interest, 2) . " ج</span></p>";
                                    }
                                    if($contract->payments->count() > 0) {
                                        $detailsHtml .= "<div class='mt-3 p-3 bg-light rounded border'><p class='text-primary mb-2 border-bottom pb-1'><i class='fa fa-history me-1'></i> <b>سجل سدادات هذه العملية:</b></p><ul class='mb-0'>";
                                        foreach($contract->payments as $pay) {
                                            $detailsHtml .= "<li class='mb-1'>" . \Carbon\Carbon::parse($pay->payment_date)->format('Y-m-d') . " : <span class='text-success'>+" . number_format($pay->amount_paid, 2) . " ج</span> <small class='text-muted'>(" . $pay->account_name . ")</small></li>";
                                        }
                                        $detailsHtml .= "</ul></div>";
                                    }
                                    $detailsHtml .= "</div>";
                                @endphp

                                <div id="details_html_{{ $acItemId }}" class="d-none">{!! $detailsHtml !!}</div>

                                @if($isFuelOnlyClient)
                                <tr class="inner-clickable-row op-item {{ $isContractActive ? '' : 'opacity-75' }}"
                                    data-status="{{ $isContractActive ? 'active' : 'paid' }}"
                                    data-date="{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}"
                                    data-liters="{{ $contract->fuel_liters ?? 0 }}"
                                    data-custody="{{ $contract->cash_custody ?? 0 }}"
                                    onclick="showContractDetails(`{{ addslashes($displayName) }} — تفاصيل`, 'details_html_{{ $acItemId }}')">
                                    <td class="text-start fw-bold text-dark op-title">
                                        <i class="fa fa-circle text-{{ $isContractActive ? 'danger' : 'success' }} me-2" style="font-size:10px;"></i>
                                        <i class="fa fa-truck text-warning me-1"></i>{{ Str::limit($displayName, 45) }}
                                    </td>
                                    <td class="fw-bold text-warning op-liters-val" data-liters="{{ $contract->fuel_liters ?? 0 }}">
                                        {{ fmtMoney($contract->fuel_liters ?? 0) }} لتر
                                    </td>
                                    <td class="fw-bold text-info op-custody-val" data-custody="{{ $contract->cash_custody ?? 0 }}">
                                        {{ number_format($contract->cash_custody ?? 0, 2) }} ج
                                    </td>
                                    <td class="text-muted fw-bold">{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}</td>
                                    <td class="fw-black text-dark">{{ number_format($contract->total_after_interest, 2) }} ج</td>
                                    <td class="fw-black text-{{ $isContractActive ? 'danger' : 'success' }} op-rem-val" data-val="{{ $contract->remaining_balance }}">{{ number_format($contract->remaining_balance, 2) }} ج</td>
                                    <td class="fw-bold" style="color:#0f172a;">{{ $person->customer_name }}</td>
                                    <td class="no-print-col">
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        @if($isContractActive)
                                            <button class="btn-action pay" onclick="event.stopPropagation(); openPayContractModal({{ $contract->id }}, '{{ addslashes($displayName) }}', {{ $contract->remaining_balance }})"><i class="fa fa-cash-register"></i> تحصيل</button>
                                            <button class="btn-action disc" onclick="event.stopPropagation(); openDiscountContractModal({{ $contract->id }}, '{{ addslashes($displayName) }}', {{ $contract->remaining_balance }})"><i class="fa fa-percent"></i> خصم</button>
                                        @else
                                            <span class="badge bg-success mt-1 px-2 py-1 rounded-pill"><i class="fa fa-check me-1"></i> مسدد</span>
                                        @endif
                                        </div>
                                    </td>
                                </tr>
                                @else
                                <tr class="inner-clickable-row op-item {{ $isContractActive ? '' : 'opacity-75' }}"
                                    data-status="{{ $isContractActive ? 'active' : 'paid' }}"
                                    data-date="{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}"
                                    onclick="showContractDetails(`{{ addslashes($contract->product_name) }}`, 'details_html_{{ $acItemId }}')">
                                    <td class="text-muted fw-bold">{{ \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') }}</td>
                                    <td class="text-start fw-bold text-dark op-title"><i class="fa fa-circle text-{{ $isContractActive ? 'danger' : 'success' }} me-2" style="font-size: 10px;"></i>{{ Str::limit($contract->product_name, 50) }}</td>
                                    <td class="fw-black text-dark">{{ number_format($contract->total_after_interest, 2) }} ج</td>
                                    <td class="fw-black text-{{ $isContractActive ? 'danger' : 'success' }} op-rem-val" data-val="{{ $contract->remaining_balance }}">{{ number_format($contract->remaining_balance, 2) }} ج</td>
                                    <td class="no-print-col">
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        @if($isContractActive)
                                            <button class="btn-action pay" onclick="event.stopPropagation(); openPayContractModal({{ $contract->id }}, '{{ addslashes($contract->product_name) }}', {{ $contract->remaining_balance }})"><i class="fa fa-cash-register"></i> تحصيل</button>
                                            <button class="btn-action disc" onclick="event.stopPropagation(); openDiscountContractModal({{ $contract->id }}, '{{ addslashes($contract->product_name) }}', {{ $contract->remaining_balance }})"><i class="fa fa-percent"></i> خصم</button>
                                        @else
                                            <span class="badge bg-success mt-1 px-3 py-2 rounded-pill"><i class="fa fa-check me-1"></i> مسدد بالكامل</span>
                                        @endif
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="globalPayBulkInstModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fa fa-money-bills me-2"></i>تحصيل مجمع للعميل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('installments.pay_bulk') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_name" id="modal_bulk_inst_customer">
                
                <div class="modal-body p-4 text-center bg-light">
                    <h6 class="fw-bold text-muted mb-2">إجمالي المبلغ المطلوب تحصيله دفعة واحدة</h6>
                    <div class="p-3 mb-4 rounded-4 bg-white shadow-sm border border-success" style="border-width: 2px !important; border-style: dashed !important;">
                        <h2 class="fw-black text-success m-0"><span id="modal_bulk_inst_total_text">0</span> ج</h2>
                    </div>
                    
                    <label class="form-label fw-bold text-start w-100 text-dark">إيداع الأموال في خزنة:</label>
                    <select name="account_id" class="form-select border-success fw-bold" required onchange="showSelectedBalance(this, 'bulk_balance_display')">
                        <option value="" disabled selected>اختر الخزنة...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" data-balance="{{ number_format($acc->balance, 2) }}">{{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                    <div id="bulk_balance_display" class="mt-2 text-start fw-bold text-success" style="display:none; font-size:13px;"></div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 bg-light">
                    <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill fs-5 shadow-sm">تأكيد التحصيل المجمع للعميل</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- مودال السداد الجزئي للعميل --}}
<div class="modal fade" id="globalPayPartialModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fa fa-money-bill-wave me-2"></i>سداد جزئي للعميل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('installments.pay_partial') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_name" id="partial_customer_name">
                <div class="modal-body p-4 bg-light">
                    <h6 class="fw-bold text-muted mb-2 text-center">إجمالي المتبقي على العميل</h6>
                    <div class="p-3 mb-4 rounded-4 bg-white shadow-sm border border-warning text-center" style="border-width:2px !important; border-style:dashed !important;">
                        <h2 class="fw-black text-danger m-0"><span id="partial_total_text">0</span> ج</h2>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">المبلغ المراد تحصيله:</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="partial_amount"
                               class="form-control form-control-lg text-center fw-bold border-warning text-dark"
                               required placeholder="أدخل المبلغ...">
                        <div class="form-text text-muted fw-bold mt-1">يُوزَّع على الديون من الأقدم للأحدث تلقائياً</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">إيداع الأموال في خزنة:</label>
                        <select name="account_id" class="form-select border-warning fw-bold" required onchange="showSelectedBalance(this, 'partial_balance_display')">
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" data-balance="{{ number_format($acc->balance, 2) }}">{{ $acc->account_name }}</option>
                            @endforeach
                        </select>
                        <div id="partial_balance_display" class="mt-2 text-start fw-bold text-warning" style="display:none; font-size:13px;"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 bg-light">
                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold rounded-pill fs-5 shadow-sm">تأكيد السداد الجزئي</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="globalPayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('installments.pay') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf <input type="hidden" name="inst_id" id="globalPayInstId">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="fa fa-cash-register me-2"></i>تحصيل جزء من العملية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <p class="fw-bold text-dark text-center mb-3 fs-5" id="globalPayProductName"></p>
                <div class="text-center mb-4">
                    <span class="d-block text-muted small fw-bold">المبلغ المتبقي للعملية</span>
                    <strong class="fs-3 text-danger fw-black" id="globalPayRemaining">0 ج</strong>
                </div>

                <div class="mb-3">
                    <label class="fw-bold text-dark mb-2">المبلغ المحصل:</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="globalPayAmount" class="form-control form-control-lg text-center fw-bold border-primary text-primary" required placeholder="أدخل المبلغ...">
                </div>

                <div class="mb-4">
                    <label class="fw-bold text-dark mb-2">إيداع في خزنة:</label>
                    <select name="method_id" class="form-select border-primary fw-bold" required onchange="showSelectedBalance(this, 'pay_balance_display')">
                        <option value="" disabled selected>اختر الخزنة...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" data-balance="{{ number_format($acc->balance, 2) }}">{{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                    <div id="pay_balance_display" class="mt-2 text-start fw-bold text-primary" style="display:none; font-size:13px;"></div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold fs-5 rounded-pill shadow-sm">تأكيد التحصيل</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="globalDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('debts.discount') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf <input type="hidden" name="inst_id" id="globalDiscInstId">
            <div class="modal-header border-0 bg-warning py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fa fa-percent me-2"></i>تطبيق خصم للتسوية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <p class="fw-bold text-dark text-center mb-4" id="globalDiscProductName"></p>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="fw-bold text-dark mb-1">نوع الخصم</label>
                        <select name="discount_type" class="form-select border-warning fw-bold">
                            <option value="amount">مبلغ ثابت (ج)</option>
                            <option value="percent">نسبة مئوية (%)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="fw-bold text-dark mb-1">قيمة الخصم</label>
                        <input type="number" step="0.01" name="discount_value" class="form-control text-center fw-bold border-warning text-warning fs-5" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="fw-bold text-dark mb-1">سبب الخصم (ملاحظة)</label>
                    <input type="text" name="discount_reason" class="form-control fw-bold border-secondary-subtle" placeholder="مثال: خصم تسوية حساب...">
                </div>
                <button type="submit" class="btn btn-warning text-dark w-100 fw-bold fs-5 rounded-pill shadow-sm">اعتماد الخصم</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleMainDateInputs(val) {
        const container = document.getElementById('custom_range_container');
        if (val === 'custom') {
            container.style.display = 'flex';
        } else {
            container.style.display = 'none';
            document.getElementById('time_filter_form').submit();
        }
    }

    function autoSubmitIfBothDates() {
        const from = document.getElementById('customFromDate').value;
        const to   = document.getElementById('customToDate').value;
        if (from && to) {
            document.getElementById('time_filter_form').submit();
        } else if (from && !to) {
            // يكفي تاريخ واحد → يُعامَل كيوم واحد
            document.getElementById('customToDate').value = from;
            document.getElementById('time_filter_form').submit();
        }
    }

function printCustomerDetails(personKey, customerName, remaining) {
        const tableEl = document.getElementById('opsTable' + personKey);

        // حساب إجمالي الليترات والعهد من الصفوف الظاهرة
        let totalLiters = 0, totalCustody = 0, totalRemaining = 0;
        const visibleRows = tableEl.querySelectorAll('tbody tr.op-item:not([style*="display: none"])');
        visibleRows.forEach(row => {
            totalLiters   += parseFloat(row.dataset.liters  || 0);
            totalCustody  += parseFloat(row.dataset.custody || 0);
            const remEl = row.querySelector('.op-rem-val');
            if (remEl) totalRemaining += parseFloat(remEl.dataset.val || 0);
        });

        const hasFuel = totalLiters > 0 || totalCustody > 0;
        
        const fuelSummaryHtml = hasFuel ? `
            <div class="fuel-summary">
                <table style="width:100%; border:none; margin:0;">
                    <tr>
                        <td style="border:none; text-align:right; width:30%; padding:0; background:transparent !important;">
                            <h4 style="margin:0; color:#b45309; font-weight:900; font-size:14px;">ملخص الوقود والعهد:</h4>
                        </td>
                        <td style="border:1px solid #fcd34d; background:#fef3c7 !important; text-align:center; padding:5px; border-radius:5px;">
                            <span class="lbl">إجمالي الليترات:</span>
                            <span class="val liters">${fmtMoney(totalLiters)} لتر</span>
                        </td>
                        <td style="border:1px solid #fcd34d; background:#fef3c7 !important; text-align:center; padding:5px; border-radius:5px;">
                            <span class="lbl">إجمالي العهد النقدية:</span>
                            <span class="val custody">${totalCustody.toLocaleString('en-US', {minimumFractionDigits:2})} ج.م</span>
                        </td>
                    </tr>
                </table>
            </div>` : '';

        const tableHtml = tableEl.outerHTML;
        const todayStr = new Date().toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' });

        // صغرت العرض هنا لـ 800 بدل 1000 عشان الشاشة ماتبقاش عريضة أوي
        const win = window.open('', '_blank', 'width=800,height=800');
        
        win.document.write(`
            <html dir="rtl">
            <head>
                <title>كشف حساب - ${customerName}</title>
                <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
                <style>
                    /* تم التعديل إلى portrait (بالطول) بدل landscape (بالعرض) */
                    @page { size: A4 portrait; margin: 10mm; }
                    body { 
                        font-family: 'Cairo', sans-serif; 
                        color: #0f172a; 
                        background: #fff; 
                        margin: 0; 
                        padding: 0;
                        font-size: 11px; 
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .print-container { max-width: 100%; margin: 0 auto; }
                    
                    /* الهيدر مضغوط */
                    .doc-header {
                        display: flex; 
                        justify-content: space-between; 
                        align-items: flex-end;
                        border-bottom: 2px solid #0f172a;
                        padding-bottom: 6px;
                        margin-bottom: 10px;
                    }
                    .doc-header .brand h1 { margin: 0; font-size: 20px; font-weight: 900; color: #0f172a; line-height: 1.2; }
                    .doc-header .brand p { margin: 2px 0 0; font-size: 11px; font-weight: 700; color: #64748b; }
                    .doc-header .meta { text-align: left; }
                    .doc-header .meta .doc-title {
                        display: inline-block;
                        background: #0f172a;
                        color: #fff;
                        padding: 4px 10px;
                        border-radius: 4px;
                        font-weight: 800;
                        font-size: 12px;
                        margin-bottom: 4px;
                    }
                    .doc-header .meta .doc-date { font-size: 10px; color: #64748b; font-weight: 700; }

                    /* بيانات العميل مضغوطة */
                    .info-box {
                        display: flex;
                        justify-content: space-between;
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        border-radius: 6px;
                        padding: 6px 15px;
                        margin-bottom: 10px;
                    }
                    .info-box .info-item { text-align: right; }
                    .info-box .info-item .lbl { font-size: 10px; color: #64748b; font-weight: 700; margin-bottom: 0px; }
                    .info-box .info-item .val { font-size: 15px; font-weight: 900; color: #0f172a; }
                    .info-box .info-item .val.danger { color: #dc2626; }

                    /* الجدول مضغوط */
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-bottom: 10px; 
                        font-size: 10.5px; 
                    }
                    table th { 
                        background-color: #0f172a !important; 
                        color: #ffffff !important; 
                        padding: 5px 6px; 
                        font-weight: 800; 
                        border: 1px solid #0f172a;
                        text-align: right;
                        white-space: nowrap;
                    }
                    table td { 
                        padding: 4px 6px; 
                        border: 1px solid #cbd5e1; 
                        font-weight: 700; 
                        color: #1e293b;
                        vertical-align: middle;
                    }
                    table tr:nth-child(even) td { background-color: #f8fafc !important; }
                    table i.fa, table i.fas { display: none !important; } 

                    /* ملخص الوقود مصغر */
                    .fuel-summary {
                        border: 1px dashed #f59e0b;
                        border-radius: 6px;
                        padding: 6px;
                        background: #fffbeb !important;
                        margin-bottom: 10px;
                        page-break-inside: avoid; 
                    }
                    .fuel-summary .lbl { font-size: 11px; color: #92400e; font-weight: 800; margin-left: 5px; }
                    .fuel-summary .val { font-size: 13px; font-weight: 900; }
                    .fuel-summary .val.liters { color: #d97706; }
                    .fuel-summary .val.custody { color: #0369a1; }

                    /* التوقيعات مضغوطة */
                    .print-footer {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 10px;
                        padding-top: 8px;
                        border-top: 1px dashed #cbd5e1;
                        page-break-inside: avoid;
                    }
                    .print-footer .sign-box { text-align: center; width: 160px; }
                    .print-footer .sign-line {
                        border-top: 1px solid #0f172a;
                        padding-top: 4px;
                        font-weight: 800;
                        font-size: 11px;
                        margin-top: 25px; 
                    }

                    .no-print-col { display: none !important; }
                </style>
            </head>
            <body>
                <div class="print-container">
                    
                    <div class="doc-header">
                        <div class="brand">
                            <h1>شركة الضبع</h1>
                            <p>للتجارة وأنظمة التقسيط والمواد البترولية والمقاولات</p>
                        </div>
                        <div class="meta">
                            <div class="doc-title">كشف حساب عميل</div>
                            <div class="doc-date">تاريخ الطباعة: ${todayStr}</div>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-item">
                            <div class="lbl">اسم العميل (الجهة)</div>
                            <div class="val">${customerName}</div>
                        </div>
                        <div class="info-item text-start">
                            <div class="lbl">إجمالي المديونية الحالية</div>
                            <div class="val danger">${totalRemaining.toLocaleString('en-US', {minimumFractionDigits:2})} ج.م</div>
                        </div>
                    </div>

                    ${tableHtml}

                    ${fuelSummaryHtml}

                    <div class="print-footer">
                        <div class="sign-box">
                            <div class="sign-line">توقيع المستلم</div>
                        </div>
                        <div class="sign-box">
                            <div class="sign-line">توقيع الإدارة الماليـة</div>
                        </div>
                    </div>

                </div>
            </body>
            </html>
        `);
        
        const doc = win.document;
        // مسح أي أعمدة أو أزرار مش عايزينها تطلع في الورقة
        doc.querySelectorAll('th.no-print-col, td.no-print-col').forEach(el => el.remove());
        
        win.document.close();
        win.setTimeout(() => {
            win.print();
            win.close();
        }, 800);
    }

    // 🖨️ طباعة قائمة العملاء بنفس الفلاتر المطبقة على الشاشة (الحالة + الفترة + البحث الفوري)
    function printDebtsClientsList() {
        const fmt = n => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const visibleRows = Array.from(document.querySelectorAll('#clientsTable tr.client-row'))
            .filter(row => row.style.display !== 'none');

        if (!visibleRows.length) { alert('لا يوجد عملاء مطابقين للفلتر الحالي للطباعة.'); return; }

        let totalAmount = 0, totalPaid = 0, totalRemaining = 0;
        const rowsHtml = visibleRows.map((row, idx) => {
            const amount    = parseFloat(row.dataset.total || 0);
            const paid      = parseFloat(row.dataset.paid || 0);
            const remaining = parseFloat(row.dataset.remaining || 0);
            totalAmount    += amount;
            totalPaid      += paid;
            totalRemaining += remaining;
            return `<tr>
                <td>${idx + 1}</td>
                <td class="text-start"><strong>${row.dataset.name}</strong></td>
                <td dir="ltr">${row.dataset.phone}</td>
                <td>${row.dataset.contracts}</td>
                <td>${fmt(amount)} ج</td>
                <td style="color:#16a34a;">${fmt(paid)} ج</td>
                <td style="color:#dc2626; font-weight:800;">${fmt(remaining)} ج</td>
                <td>${row.dataset.status}</td>
            </tr>`;
        }).join('');

        // ── عنوان يوضّح الفلاتر المطبّقة ──
        const statusLabels = { all: 'كل الحالات', active: 'الديون النشطة', paid: 'المسدد' };
        const status = '{{ request("status", "active") }}';
        const timeFilterLabels = { all: 'كل السجلات', today: 'اليوم فقط', yesterday: 'أمس', week: 'هذا الأسبوع', month: 'هذا الشهر', year: 'هذا العام', custom: 'نطاق مخصص' };
        const timeFilter = '{{ request("time_filter", "all") }}';
        let filterLabel = (statusLabels[status] || 'الكل') + ' — ' + (timeFilterLabels[timeFilter] || 'كل السجلات');
        @if(request('time_filter') === 'custom' && request('custom_from'))
            filterLabel += ' ({{ request("custom_from") }} إلى {{ request("custom_to") ?: request("custom_from") }})';
        @endif
        const searchVal = (document.getElementById('searchInput')?.value || '').trim();
        if (searchVal) filterLabel += ' — بحث: ' + searchVal;

        const todayStr = new Date().toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' });

        const win = window.open('', '_blank', 'width=900,height=800');
        win.document.write(`
            <html dir="rtl">
            <head>
                <title>قائمة مستحقات العملاء</title>
                <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
                <style>
                    @page { size: A4 portrait; margin: 10mm; }
                    body { font-family: 'Cairo', sans-serif; color: #0f172a; background: #fff; margin: 0; padding: 0; font-size: 11px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .print-container { max-width: 100%; margin: 0 auto; }
                    .doc-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #0f172a; padding-bottom: 6px; margin-bottom: 10px; }
                    .doc-header .brand h1 { margin: 0; font-size: 20px; font-weight: 900; color: #0f172a; line-height: 1.2; }
                    .doc-header .brand p { margin: 2px 0 0; font-size: 11px; font-weight: 700; color: #64748b; }
                    .doc-header .meta { text-align: left; }
                    .doc-header .meta .doc-title { display: inline-block; background: #0f172a; color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 12px; margin-bottom: 4px; }
                    .doc-header .meta .doc-date { font-size: 10px; color: #64748b; font-weight: 700; }
                    .info-box { display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 15px; margin-bottom: 10px; }
                    .info-box .info-item .lbl { font-size: 10px; color: #64748b; font-weight: 700; margin-bottom: 0px; }
                    .info-box .info-item .val { font-size: 13px; font-weight: 900; color: #0f172a; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10.5px; }
                    table th { background-color: #0f172a !important; color: #ffffff !important; padding: 5px 6px; font-weight: 800; border: 1px solid #0f172a; text-align: right; white-space: nowrap; }
                    table td { padding: 4px 6px; border: 1px solid #cbd5e1; font-weight: 700; color: #1e293b; vertical-align: middle; }
                    table tr:nth-child(even) td { background-color: #f8fafc !important; }
                    table tfoot td { background-color: #f1f5f9 !important; font-weight: 900; }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <div class="doc-header">
                        <div class="brand">
                            <h1>شركة الضبع</h1>
                            <p>للتجارة وأنظمة التقسيط والمواد البترولية والمقاولات</p>
                        </div>
                        <div class="meta">
                            <div class="doc-title">قائمة مستحقات العملاء</div>
                            <div class="doc-date">تاريخ الطباعة: ${todayStr}</div>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-item"><div class="lbl">الفلتر المطبق</div><div class="val">${filterLabel}</div></div>
                        <div class="info-item"><div class="lbl">عدد العملاء</div><div class="val">${visibleRows.length}</div></div>
                    </div>

                    <table>
                        <thead><tr><th>م</th><th class="text-start">العميل</th><th>الهاتف</th><th>عدد العمليات</th><th>إجمالي الحساب</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th></tr></thead>
                        <tbody>${rowsHtml}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-start" style="text-align:right;">الإجمالي</td>
                                <td>${fmt(totalAmount)} ج</td>
                                <td style="color:#16a34a;">${fmt(totalPaid)} ج</td>
                                <td style="color:#dc2626;">${fmt(totalRemaining)} ج</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </body>
            </html>
        `);
        win.document.close();
        win.setTimeout(() => { win.print(); win.close(); }, 800);
    }

    function showSelectedBalance(selectElement, displayId) {
        const displayDiv = document.getElementById(displayId);
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const balance = selectedOption.getAttribute('data-balance');
            displayDiv.innerHTML = `<i class="fa fa-wallet me-1"></i> الرصيد المتاح: ${balance} ج.م`;
            displayDiv.style.display = 'block';
        } else {
            displayDiv.style.display = 'none';
        }
    }

    function resetBalanceDisplays() {
        document.querySelectorAll('[id$="_balance_display"]').forEach(el => {
            el.style.display = 'none';
        });
    }

    function closeAllModals() {
        document.querySelectorAll('.modal.show').forEach(m => {
            const bsm = bootstrap.Modal.getInstance(m);
            if (bsm) bsm.hide();
        });
        resetBalanceDisplays();
    }

    // ═══ فلاتر التابات والتاريخ جوه المودال ═══
    // state لكل مودال
    const modalState = {};

   function getModalState(key) {
        if (!modalState[key]) modalState[key] = { tab: 'active', date: 'all', customDate: null, rangeFrom: null, rangeTo: null };
        return modalState[key];
    }

    function setModalTab(key, tab, btn) {
        getModalState(key).tab = tab;
        // تحديث شكل الأزرار
        if (btn) {
            const group = btn.closest('.btn-group');
            group.querySelectorAll('.modal-tab-btn').forEach(b => {
                b.classList.remove('btn-primary','btn-success','btn-secondary','active');
                b.classList.add(b.dataset.outline || 'btn-outline-' + (b.textContent.includes('نشط') ? 'primary' : b.textContent.includes('مسدد') ? 'success' : 'secondary'));
            });
            btn.classList.remove('btn-outline-primary','btn-outline-success','btn-outline-secondary');
            btn.classList.add(tab === 'active' ? 'btn-primary' : tab === 'paid' ? 'btn-success' : 'btn-secondary', 'active');
        }
        applyModalFilters(key);
    }

    function setModalDate(key, dateType, btn, customVal) {
        const state = getModalState(key);
        state.date = dateType;
        state.customDate = customVal || null;
        state.rangeFrom  = null;
        state.rangeTo    = null;

        if (btn) {
            const group = btn.closest('.btn-group');
            group.querySelectorAll('.modal-date-btn').forEach(b => {
                b.classList.remove('btn-dark','active');
                b.classList.add('btn-outline-dark');
            });
            btn.classList.remove('btn-outline-dark');
            btn.classList.add('btn-dark','active');
        }
        applyModalFilters(key);
    }

    // فلتر نطاق مخصص من / إلى جوه المودال
    function setModalDateRange(key) {
        const fromEl = document.getElementById('modalDateFrom_' + key);
        const toEl   = document.getElementById('modalDateTo_'   + key);
        if (!fromEl || !toEl) return;
        const from = fromEl.value;
        const to   = toEl.value || from;
        if (!from) return;

        const state  = getModalState(key);
        state.date      = 'range';
        state.rangeFrom = from;
        state.rangeTo   = to;
        state.customDate = null;

        // إزالة تحديد أزرار التاريخ السريع لأننا في نطاق مخصص
        const modal = fromEl.closest('.modal-body');
        if (modal) {
            modal.querySelectorAll('.modal-date-btn').forEach(b => {
                b.classList.remove('btn-dark','active');
                b.classList.add('btn-outline-dark');
            });
        }
        applyModalFilters(key);
    }

    function applyModalFilters(key) {
        const state  = getModalState(key);
        const today  = new Date().toISOString().slice(0,10);
        const yest   = new Date(Date.now() - 86400000).toISOString().slice(0,10);
        const rows   = document.querySelectorAll(`#opsTable${key} tbody tr.op-item`);
        let sum = 0, totalLiters = 0, totalCustody = 0;

        // حساب حدود الأسبوع (السبت - الجمعة) والشهر
        const now = new Date();
        // أسبوع: السبت الماضي حتى الجمعة القادم
        const dayOfWeek = now.getDay(); // 0=أحد ... 6=سبت
        const daysSinceSat = (dayOfWeek + 1) % 7; // السبت هو اليوم 6
        const weekStart = new Date(now);
        weekStart.setDate(now.getDate() - ((dayOfWeek >= 6 ? 0 : dayOfWeek + 1)));
        const weekStartStr = weekStart.toISOString().slice(0,10);
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        const weekEndStr = weekEnd.toISOString().slice(0,10);
        // الشهر الحالي
        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
        const monthEnd   = new Date(now.getFullYear(), now.getMonth()+1, 0).toISOString().slice(0,10);

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowDate   = row.dataset.date;
            const remEl     = row.querySelector('.op-rem-val');
            const remVal    = remEl ? parseFloat(remEl.dataset.val) : 0;
            const rowLiters  = parseFloat(row.dataset.liters  || 0);
            const rowCustody = parseFloat(row.dataset.custody || 0);

            const tabOk = state.tab === 'all' || state.tab === rowStatus;

            let dateOk = true;
            if (state.date === 'today')          dateOk = rowDate === today;
            else if (state.date === 'yesterday') dateOk = rowDate === yest;
            else if (state.date === 'week')      dateOk = rowDate >= weekStartStr && rowDate <= weekEndStr;
            else if (state.date === 'month')     dateOk = rowDate >= monthStart && rowDate <= monthEnd;
            else if (state.date === 'range' && state.rangeFrom && state.rangeTo) {
                dateOk = rowDate >= state.rangeFrom && rowDate <= state.rangeTo;
            } else if (state.date === 'custom' && state.customDate) {
                dateOk = rowDate === state.customDate;
            }

            const show = tabOk && dateOk;
            row.style.display = show ? '' : 'none';
            if (show) {
                sum += remVal;
                totalLiters  += rowLiters;
                totalCustody += rowCustody;
            }
        });

        const sumEl = document.getElementById('sumDisplay' + key);
        if (sumEl) sumEl.innerText = sum.toLocaleString('en-US', {minimumFractionDigits:2});

        const litersEl = document.getElementById('litersDisplay' + key);
        if (litersEl) litersEl.innerText = fmtMoney(totalLiters);

        const custodyEl = document.getElementById('custodyDisplay' + key);
        if (custodyEl) custodyEl.innerText = totalCustody.toLocaleString('en-US', {minimumFractionDigits:2});
    }

    function filterModalOperations(personKey, val) {
        val = val.toLowerCase().trim();
        const state  = getModalState(personKey);
        const today  = new Date().toISOString().slice(0,10);
        const yest   = new Date(Date.now() - 86400000).toISOString().slice(0,10);
        const items  = document.querySelectorAll(`#opsTable${personKey} tbody tr.op-item`);
        let visibleSum = 0, totalLiters = 0, totalCustody = 0;

        const now = new Date();
        const dayOfWeek = now.getDay();
        const weekStart = new Date(now);
        weekStart.setDate(now.getDate() - (dayOfWeek >= 6 ? 0 : dayOfWeek + 1));
        const weekStartStr = weekStart.toISOString().slice(0,10);
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        const weekEndStr = weekEnd.toISOString().slice(0,10);
        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
        const monthEnd   = new Date(now.getFullYear(), now.getMonth()+1, 0).toISOString().slice(0,10);

        items.forEach(item => {
            const rowStatus  = item.dataset.status;
            const rowDate    = item.dataset.date;
            const remElem    = item.querySelector('.op-rem-val');
            const remAmt     = remElem ? parseFloat(remElem.dataset.val) : 0;
            const rowLiters  = parseFloat(item.dataset.liters  || 0);
            const rowCustody = parseFloat(item.dataset.custody || 0);

            const tabOk = state.tab === 'all' || state.tab === rowStatus;
            let dateOk  = true;
            if (state.date === 'today')          dateOk = rowDate === today;
            else if (state.date === 'yesterday') dateOk = rowDate === yest;
            else if (state.date === 'week')      dateOk = rowDate >= weekStartStr && rowDate <= weekEndStr;
            else if (state.date === 'month')     dateOk = rowDate >= monthStart && rowDate <= monthEnd;
            else if (state.date === 'range' && state.rangeFrom && state.rangeTo) {
                dateOk = rowDate >= state.rangeFrom && rowDate <= state.rangeTo;
            } else if (state.date === 'custom' && state.customDate) {
                dateOk = rowDate === state.customDate;
            }

            const textOk = val === '' || item.textContent.toLowerCase().includes(val);
            const show   = tabOk && dateOk && textOk;

            item.style.display = show ? '' : 'none';
            if (show) {
                visibleSum   += remAmt;
                totalLiters  += rowLiters;
                totalCustody += rowCustody;
            }
        });

        const sumDisplay = document.getElementById(`sumDisplay${personKey}`);
        if (sumDisplay) sumDisplay.innerText = visibleSum.toLocaleString('en-US', {minimumFractionDigits:2});

        const litersEl = document.getElementById('litersDisplay' + personKey);
        if (litersEl) litersEl.innerText = fmtMoney(totalLiters);
        const custodyEl = document.getElementById('custodyDisplay' + personKey);
        if (custodyEl) custodyEl.innerText = totalCustody.toLocaleString('en-US', {minimumFractionDigits:2});
    }

    function showContractDetails(title, containerId) {
        const htmlContent = document.getElementById(containerId).innerHTML;
        Swal.fire({
            title: '<i class="fa fa-file-invoice-dollar text-primary me-2"></i> ' + title,
            html: htmlContent,
            confirmButtonText: 'إغلاق نافذة التفاصيل',
            confirmButtonColor: '#2563eb',
            width: '500px'
        });
    }

    function openGlobalBulkInstModal(customerName, totalAmount) {
        closeAllModals();
        document.getElementById('modal_bulk_inst_customer').value = customerName;
        document.getElementById('modal_bulk_inst_total_text').innerText = parseFloat(totalAmount).toLocaleString('en-US');
        
        const selectBox = document.querySelector('#globalPayBulkInstModal select[name="account_id"]');
        if(selectBox) selectBox.value = "";
        
        new bootstrap.Modal(document.getElementById('globalPayBulkInstModal')).show();
    }

    function openPartialPayModal(customerName, totalRemaining) {
        closeAllModals();
        document.getElementById('partial_customer_name').value = customerName;
        document.getElementById('partial_total_text').innerText = parseFloat(totalRemaining).toLocaleString('en-US');
        document.getElementById('partial_amount').value = '';
        document.getElementById('partial_amount').max = totalRemaining;

        const selectBox = document.querySelector('#globalPayPartialModal select[name="account_id"]');
        if (selectBox) selectBox.value = '';
        const balDisplay = document.getElementById('partial_balance_display');
        if (balDisplay) balDisplay.style.display = 'none';

        new bootstrap.Modal(document.getElementById('globalPayPartialModal')).show();
    }

    function openPayContractModal(instId, productName, remaining) {
        closeAllModals();
        document.getElementById('globalPayInstId').value = instId;
        document.getElementById('globalPayProductName').textContent = productName;
        document.getElementById('globalPayRemaining').textContent = parseFloat(remaining).toLocaleString('en-US') + ' ج';
        document.getElementById('globalPayAmount').value = '';
        
        const selectBox = document.querySelector('#globalPayModal select[name="method_id"]');
        if(selectBox) selectBox.value = "";

        new bootstrap.Modal(document.getElementById('globalPayModal')).show();
    }

    function openDiscountContractModal(instId, productName, remaining) {
        closeAllModals();
        document.getElementById('globalDiscInstId').value = instId;
        document.getElementById('globalDiscProductName').textContent = productName + ' (المتبقي: ' + parseFloat(remaining).toLocaleString('en-US') + ' ج)';
        new bootstrap.Modal(document.getElementById('globalDiscountModal')).show();
    }

    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('.client-row');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const val = this.value.toLowerCase().trim();
            tableRows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }
</script>
</body>
</html>