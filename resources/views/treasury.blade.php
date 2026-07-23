<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الخزانة - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root { --main-blue: #1a3a5f; --main-green: #00ab67; --bg-light: #f4f7f6; --text-dark: #1e293b; --danger: #e63946; --warning: #f59e0b; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--bg-light); color: var(--text-dark); overflow-x: hidden; }
        
        /* margin-right و width بيتحكم فيهم السايد بار عبر --sb-width */
        .main-content { padding: 24px 20px; }

        /* ── ضبط كارتين الربح الكبيرين مع السايد بار ── */
        .profit-cards-row .col-profit {
            width: 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }
        @media (max-width: 1200px) {
            .profit-cards-row .col-profit {
                width: 100%;
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .stat-card { border-radius: 12px; padding: 15px 18px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.05); position: relative; overflow: hidden; height: 100%; transition: 0.3s; }
        .stat-card i.watermark { position: absolute; left: -10px; bottom: -15px; font-size: 5rem; opacity: 0.08; transform: rotate(-15deg); }
        .stat-card h6 { font-size: 0.95rem; font-weight: 800; opacity: 0.95; margin-bottom: 6px; }
        .stat-card p { font-size: 0.7rem; opacity: 0.75; margin-bottom: 8px; line-height: 1.4; }
        .stat-card h3 { font-size: 1.6rem; font-weight: 900; margin: 0; }
        
        .bg-capital { background: linear-gradient(135deg, #0f172a, #334155); }
        .bg-balance { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        .bg-assets { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .bg-installments { background: linear-gradient(135deg, #0284c7, #38bdf8); }
        .bg-debts-for { background: linear-gradient(135deg, #059669, #10b981); }
        .bg-debts-on { background: linear-gradient(135deg, #b91c1c, #ef4444); }

        a.card-link { text-decoration: none; color: inherit; display: block; height: 100%; border-radius: 12px;}
        a.card-link .stat-card { transition: transform 0.3s ease, box-shadow 0.3s ease, filter 0.3s ease; }
        a.card-link:hover .stat-card { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); filter: brightness(1.05); cursor: pointer; }

        .btn-info-pill { position: absolute; top: 10px; right: 10px; width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.35); color: #fff; font-size: .75rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .2s; }
        .btn-info-pill:hover { background: rgba(255,255,255,0.35); }

        .category-header { display: flex; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; }
        .category-header .icon-box { width: 38px; height: 38px; border-radius: 10px; background: var(--main-blue); color: white; display: flex; align-items: center; justify-content: center; font-size: 1rem; margin-left: 12px; box-shadow: 0 4px 10px rgba(26,58,95,0.2); }
        .category-title { font-size: 1.2rem; font-weight: 900; color: var(--main-blue); margin: 0; }

        .account-card { background: white; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; text-align: center; position: relative; overflow: hidden; transition: 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .account-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-color: var(--main-blue); }
        .account-card i.watermark { position: absolute; right: -15px; bottom: -15px; font-size: 5rem; opacity: 0.03; color: var(--main-blue); transform: rotate(15deg); }
        .acc-name { font-size: 0.95rem; font-weight: 800; color: #475569; margin-bottom: 8px; }
        .balance-amount { font-size: 1.4rem; font-weight: 900; }
        .balance-positive { color: var(--main-green); }
        .balance-negative { color: var(--danger); }
        .balance-zero { color: #94a3b8; }

        /* ── Dark Mode ── */
        body.dark-mode {
            --bg-light: #0f172a;
            --text-dark: #e2e8f0;
            background-color: #0f172a;
            color: #e2e8f0;
        }
        body.dark-mode .account-card {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        body.dark-mode .account-card:hover { border-color: #60a5fa; }
        body.dark-mode .acc-name { color: #94a3b8; }
        body.dark-mode .balance-zero { color: #64748b; }
        body.dark-mode .category-header { border-bottom-color: #334155; }
        body.dark-mode .category-title { color: #93c5fd; }
        body.dark-mode .icon-box { background: #1d4ed8; }
        body.dark-mode .text-muted { color: #94a3b8 !important; }
        body.dark-mode .text-primary { color: #60a5fa !important; }
        body.dark-mode .border-bottom { border-color: #334155 !important; }
        body.dark-mode [style*="background:#fff"],
        body.dark-mode [style*="background: #fff"] {
            background: #1e293b !important;
        }
        body.dark-mode [style*="background:#f8fafc"] { background: #0f172a !important; }
        body.dark-mode [style*="background:#f1f5f9"] { background: #1e293b !important; color: #94a3b8 !important; }
        body.dark-mode .btn-outline-secondary {
            color: #94a3b8;
            border-color: #475569;
        }
        body.dark-mode .btn-outline-secondary:hover { background: #334155; color: #e2e8f0; }
        body.dark-mode .form-control {
            background: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }
        body.dark-mode .form-control:focus { background: #1e293b; color: #e2e8f0; border-color: #3b82f6; }
        body.dark-mode .modal-content { background: #1e293b; color: #e2e8f0; }
        body.dark-mode .modal-header.bg-light,
        body.dark-mode .modal-footer.bg-light,
        body.dark-mode .modal-footer.bg-white,
        body.dark-mode .modal-body.bg-light { background: #0f172a !important; }
        body.dark-mode .btn-close { filter: invert(1); }
        body.dark-mode .alert-info { background: #1e3a5f; border-color: #3b82f6; color: #bfdbfe; }
        body.dark-mode .badge[style*="background:#f1f5f9"] { background: #1e293b !important; color: #94a3b8 !important; }

        #darkToggleBtn {
            width: 38px; height: 38px; border-radius: 50%; border: 2px solid #e2e8f0;
            background: transparent; cursor: pointer; display: flex; align-items: center;
            justify-content: center; font-size: 1rem; transition: 0.2s; color: #475569;
        }
        body.dark-mode #darkToggleBtn { border-color: #475569; color: #e2e8f0; }
        #darkToggleBtn:hover { background: rgba(0,0,0,0.08); }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
 
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
        <div>
            <h2 class="fw-bold text-primary mb-1 fs-3">المركز المالي الشامل</h2>
            <p class="text-muted small m-0 fw-bold">حساب رأس المال الفعلي بناءً على الأصول والسيولة والديون</p>
        </div>
    </div>
<div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--main-blue);"><i class="fa fa-vault me-2 text-primary"></i>الخزانة والمركز المالي</h2>
            <p class="text-muted small mb-0">نظرة شاملة على السيولة، الأصول، الديون، وصافي الأرباح</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button id="darkToggleBtn" title="تبديل الوضع المظلم">
                <i class="fa fa-moon"></i>
            </button>
            <button class="btn btn-dark fw-bold rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#snapshotModal">
                <i class="fa fa-camera-retro me-2"></i> حفظ لقطة للتقارير
            </button>
        </div>
    </div>
    @php
        $inventory_cost   = $inventory_assets ?? 0; 
        $inventory_sell   = \Illuminate\Support\Facades\DB::table('sales')->where('inventory_status', 'to_inventory')->selectRaw('SUM(selling_price * remaining_quantity) as total_val')->value('total_val') ?? 0;
        $inventory_profit = $inventory_sell - $inventory_cost;
        
        // رأس المال من نفس مصدر الحقيقة (InstallmentFinanceService) — يشمل مستحقات البنزينة فوراً
        // فيظهر ربح أي عملية بنزينة في رأس المال على طول بدل ما يستنى تحصيل المستحقات وتسديد الديون.
        $adjusted_capital = $capital ?? 0;
    @endphp

<div class="row row-cols-2 row-cols-md-4 row-cols-xl-6 g-3 mb-4 animate__animated animate__fadeInUp">

    <div class="col">
        <div class="stat-card bg-capital h-100">
            <i class="fa-solid fa-crown watermark"></i>
            <h6><i class="fa-solid fa-scale-balanced me-2"></i>رأس المال الفعلي</h6>
            <p class="mb-2">سيولة + أصول + <b class="text-warning">أقساط مستحقة ومقاولات</b> + ديون بالسوق − ديون علينا</p>
            <h3>{!! finMask(fmtMoney($adjusted_capital)) !!} <span class="fs-6">ج</span></h3>
        </div>
    </div>

    <div class="col">
        <div class="stat-card bg-balance h-100">
            <i class="fa-solid fa-vault watermark"></i>
            <h6><i class="fa-solid fa-wallet me-2"></i>السيولة النقدية (الخزن)</h6>
            <p class="mb-2">مجموع الخزن والمحافظ البنكية الحالية</p>
            <h3 class="mt-2">{!! finMask(fmtMoney($liquidity ?? 0)) !!} <span class="fs-6">ج</span></h3>
        </div>
    </div>

    <div class="col">
        <a href="{{ url('/inventory') }}" class="card-link">
            <div class="stat-card bg-assets h-100 d-flex flex-column">
                <i class="fa-solid fa-boxes-stacked watermark"></i>
                <h6><i class="fa-solid fa-warehouse me-2"></i>قيمة المخزن تفصيلياً</h6>
                <div class="mt-auto position-relative z-1">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-light border-opacity-25 pb-1 mb-1">
                        <span style="font-size: 0.75rem; opacity: 0.9;">التكلفة الشرائية:</span>
                        <span class="fw-bold" style="font-size: 0.85rem;">{{ fmtMoney($inventory_cost) }} ج</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom border-light border-opacity-25 pb-1 mb-1">
                        <span style="font-size: 0.75rem; opacity: 0.9;">القيمة بسعر البيع:</span>
                        <span class="fw-bold text-white" style="font-size: 0.85rem;">{{ fmtMoney($inventory_sell) }} ج</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <span style="font-size: 0.75rem; opacity: 0.9;">الربح المتوقع:</span>
                        <span class="fw-black text-dark bg-warning px-2 rounded" style="font-size: 0.85rem;">+{{ fmtMoney($inventory_profit) }} ج</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ url('/assets') }}" class="card-link">
            <div class="stat-card h-100" style="background: linear-gradient(135deg, #4c1d95, #7c3aed);">
                <i class="fa-solid fa-building watermark"></i>
                <h6><i class="fa-solid fa-building me-2"></i>قيمة الأصول الثابتة</h6>
                <p class="mb-2">القيمة الدفترية الحالية للأصول النشطة</p>
                <h3 class="mt-2">{!! finMask(fmtMoney($total_assets_value ?? 0)) !!} <span class="fs-6">ج</span></h3>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ url('/installments') }}" class="card-link">
            <div class="stat-card bg-installments h-100">
                <i class="fa-solid fa-file-signature watermark"></i>
                <h6><i class="fa-solid fa-handshake me-2"></i>منظومة الأقساط (مستحقة)</h6>
                <p class="mb-2">إجمالي المبالغ بالخارج التي لم تُحصّل بعد</p>
                <h3 class="mt-2">{!! finMask(fmtMoney($installments_system_debts ?? 0)) !!} <span class="fs-6">ج</span></h3>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="#" class="card-link">
            <div class="stat-card h-100" style="background: linear-gradient(135deg, #9a3412, #ea580c);">
                <i class="fa-solid fa-helmet-safety watermark"></i>
                <h6><i class="fa-solid fa-person-digging me-2"></i>اجمالي مستحق منظومه اقساط المقاولات</h6>
                <p class="mb-2">مستحقات عقود المقاولات غير المحصلة</p>
                <h3 class="mt-2">{!! finMask(fmtMoney($contracting_installments_debts ?? 0)) !!} <span class="fs-6">ج</span></h3>
            </div>
        </a>
    </div>

</div> <!-- End first row -->

<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">

    <div class="col">
        <a href="{{ url('/debts') }}" class="card-link">
            <div class="stat-card bg-debts-for h-100">
                <i class="fa-solid fa-arrow-trend-up watermark"></i>
                <h6><i class="fa-solid fa-users me-2"></i>مستحقات لنا</h6>
                <p class="mb-2">مبيعات آجلة ومباشرة لم تُحصل (بدون أقساط أو بنزينة)</p>
                <h3 class="mt-2">{!! finMask(fmtMoney($other_debts_for_us ?? 0)) !!} <span class="fs-6">ج</span></h3>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="{{ url('/debts2') }}" class="card-link">
            <div class="stat-card bg-debts-on h-100">
                <i class="fa-solid fa-arrow-trend-down watermark"></i>
                <h6><i class="fa-solid fa-truck me-2"></i> ديون على الشركه</h6>
                <p class="mb-2">إجمالي الالتزامات والفواتير التي لم تُسدَّد</p>
                <h3 class="mt-2">{{ fmtMoney($total_debts_on_us ?? 0) }} <span class="fs-6">ج</span></h3>
            </div>
        </a>
    </div>

    {{-- ⛽ كارت مستحقات البنزينة (ما يدين به العملاء لنا من عمليات الوقود) --}}
    <div class="col">
        <a href="{{ url('/debts?category=بنزينة') }}" class="card-link">
            <div class="stat-card h-100" style="background: linear-gradient(135deg, #92400e, #f59e0b);">
                <i class="fa-solid fa-gas-pump watermark"></i>
                <h6><i class="fa-solid fa-gas-pump me-2"></i> العالميه</h6>
                <p class="mb-2">مبالغ وقود وعهد مستحقة على شركات النقل</p>
                <h3 class="mt-2">{{ fmtMoney($gas_receivables ?? 0) }} <span class="fs-6">ج</span></h3>
                @if(isset($gas_receivables_count) && $gas_receivables_count > 0)
                    <small style="opacity:.8; font-size:.7rem;">{{ $gas_receivables_count }} عملية معلقة</small>
                @endif
            </div>
        </a>
    </div>

    {{-- ⛽ كارت ديون البنزينة على الشركة (ما ندين به للمحطات والاستقطاعات) --}}
    <div class="col">
        <a href="{{ url('/debts2?category=وقود') }}" class="card-link">
            <div class="stat-card h-100" style="background: linear-gradient(135deg, #1c1917, #57534e);">
                <i class="fa-solid fa-fire-flame-curved watermark"></i>
                <h6><i class="fa-solid fa-fire-flame-curved me-2"></i>ديون البنزينة على الشركة</h6>
                <p class="mb-2">مستحقات المحطات   </p>
                <h3 class="mt-2">{{ fmtMoney($gas_payables ?? 0) }} <span class="fs-6">ج</span></h3>
                @if(isset($gas_payables_stations) && $gas_payables_stations > 0)
                    <div style="font-size:.72rem; opacity:.85; margin-top:6px;">
                        <span>محطات: {{ fmtMoney($gas_payables_stations) }} ج</span>
                        @if(isset($gas_payables_deductions) && $gas_payables_deductions > 0)
                            &nbsp;|&nbsp;<span>استقطاعات: {{ fmtMoney($gas_payables_deductions) }} ج</span>
                        @endif
                    </div>
                @endif
            </div>
        </a>
    </div>
 {{-- 💸 كارت إجمالي المصروفات والخصومات (بفلتر مستقل) --}}
    <div class="col">
        <div class="stat-card h-100" style="background: linear-gradient(135deg, #831843, #e11d48);">
            <i class="fa-solid fa-file-invoice-dollar watermark"></i>
            <h6>
                <a href="{{url('/expenses')}}" style="color:inherit;text-decoration:none;">
                    <i class="fa-solid fa-receipt me-2"></i>إجمالي المصروفات والخصومات
                </a>
            </h6>
            <p class="mb-1" style="font-size:.78rem;opacity:.85;">مصاريف، رواتب، عمولات، وإهلاكات/خسائر</p>
            <h3 class="mt-1 mb-2">{{ fmtMoney($total_deductions ?? 0) }} <span class="fs-6">ج</span></h3>
            {{-- فلتر مستقل داخل الكارت --}}
            <div class="d-flex flex-wrap gap-1">
                @php
                    $ef      = request('exp_filter', 'month');
                    $expBase = url('/treasury') . '?' . http_build_query(array_filter([
                        'profit_filter'    => request('profit_filter', 'all'),
                        'profit_from_date' => request('profit_from_date', ''),
                        'profit_to_date'   => request('profit_to_date', ''),
                    ]));
                @endphp
                @foreach(['today'=>'اليوم','week'=>'الأسبوع','month'=>'الشهر','3months'=>'3 أشهر','all'=>'الكل'] as $val => $lbl)
                    <a href="{{ $expBase }}&exp_filter={{ $val }}"
                       style="font-size:.68rem; border-radius:6px; padding:2px 8px; text-decoration:none;
                              {{ $ef===$val ? 'background:rgba(255,255,255,0.95);color:#831843;font-weight:800;' : 'background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.4);' }}">
                       {{ $lbl }}
                    </a>
                @endforeach
            </div>
            <div class="mt-1" style="font-size:.68rem;opacity:.8;">
                <i class="fa fa-filter me-1"></i> {{ $expFilterLabel ?? 'هذا الشهر' }}
            </div>
        </div>
    </div>

</div>


    {{-- السيولة والمحافظ --}}
    <div class="liquidity-theme mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
        <div class="category-header">
            <div class="icon-box"><i class="fa-solid fa-vault"></i></div>
            <h3 class="category-title fs-5">السيولة النقدية والمحافظ البنكية</h3>
        </div>
        <div class="row g-3">
            @if(isset($liquidity_accounts))
                @foreach($liquidity_accounts as $acc)
                    @php
                        $val = $acc->balance;
                        $colorClass = ($val > 0) ? 'balance-positive' : (($val < 0) ? 'balance-negative' : 'balance-zero');
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="account-card">
                            <i class="fa-solid fa-money-check-dollar watermark"></i>
                            <div class="acc-name">{{ $acc->account_name }}</div>
                            <span class="balance-amount {{ $colorClass }}">{{ number_format($val, 2) }} <span class="fs-6 text-muted">ج</span></span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

{{-- قسم المشاريع --}}
<div class="projects-theme mb-5 animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
    <div class="category-header">
        <div class="icon-box"><i class="fa-solid fa-diagram-project"></i></div>
        <h3 class="category-title fs-5">قسم المشاريع</h3>
    </div>
    <div class="row g-3">
        @if(isset($projects))
            @foreach($projects as $project)
                @php
                    $val = $project->balance;
                    $colorClass = ($val > 0) ? 'balance-positive' : (($val < 0) ? 'balance-negative' : 'balance-zero');
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="account-card position-relative">
                        <i class="fa-solid fa-diagram-project watermark"></i>
                        <div class="acc-name">{{ $project->account_name }}</div>
                        <span class="balance-amount {{ $colorClass }}">{{ number_format($val, 2) }} <span class="fs-6 text-muted">ج</span></span>
                        
                        {{-- زر التعديل --}}
                        <button type="button" class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 shadow-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editBalanceModal"
                                onclick="setEditData('{{ $project->id }}', '{{ $project->account_name }}', '{{ $val }}')">
                            <i class="fa-solid fa-pen text-primary" style="font-size:.7rem;"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- ════════════════════════════════════════════════
     شريط فلاتر الأرباح والمصروفات
════════════════════════════════════════════════ --}}
<div class="animate__animated animate__fadeInUp mb-3" style="animation-delay:.08s;">
    <div style="background:#fff; border-radius:14px; padding:14px 18px; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,0.04);">

        @php
            $pf = request('profit_filter', 'all');
            // 💡 التعديل: استقبال تاريخ البداية والنهاية
            $pfFrom = request('profit_from_date', '');
            $pfTo   = request('profit_to_date', '');

            $activeClass   = 'btn btn-sm btn-primary fw-bold';
            $inactiveClass = 'btn btn-sm btn-outline-secondary fw-bold';

            // 💡 التعديل: ضبط الليبل عشان يعرض النطاق الزمني
            $filterLabel = match($pf) {
                'today'     => 'اليوم — ' . now()->format('d/m/Y'),
                'yesterday' => 'أمس — ' . now()->subDay()->format('d/m/Y'),
                'week'      => 'الأسبوع الحالي',
                'month'     => now()->format('m/Y'),
                '3months'   => 'آخر 3 أشهر',
                'custom'    => ($pfFrom && $pfTo) ? \Carbon\Carbon::parse($pfFrom)->format('d/m/Y') . ' إلى ' . \Carbon\Carbon::parse($pfTo)->format('d/m/Y') : 'فترة مخصصة',
                default     => 'إجمالي كل الفترات',
            };
        @endphp

        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-bold text-muted" style="font-size:.82rem; white-space:nowrap;">
                <i class="fa fa-filter me-1 text-primary"></i>فلتر الأرباح والمصروفات:
            </span>

            @php $ef = request('exp_filter', '3months'); @endphp

            <a href="{{ url('/treasury') }}?profit_filter=today&exp_filter={{ $ef }}"
               class="{{ $pf==='today' ? $activeClass : $inactiveClass }}"
               style="font-size:.78rem; border-radius:8px; padding:4px 12px;">اليوم</a>

            <a href="{{ url('/treasury') }}?profit_filter=yesterday&exp_filter={{ $ef }}"
               class="{{ $pf==='yesterday' ? $activeClass : $inactiveClass }}"
               style="font-size:.78rem; border-radius:8px; padding:4px 12px;">أمس</a>

            <a href="{{ url('/treasury') }}?profit_filter=week&exp_filter={{ $ef }}"
               class="{{ $pf==='week' ? $activeClass : $inactiveClass }}"
               style="font-size:.78rem; border-radius:8px; padding:4px 12px;">الأسبوع</a>

            <a href="{{ url('/treasury') }}?profit_filter=month&exp_filter={{ $ef }}"
               class="{{ $pf==='month' ? $activeClass : $inactiveClass }}"
               style="font-size:.78rem; border-radius:8px; padding:4px 12px;">الشهر</a>

            <a href="{{ url('/treasury') }}?profit_filter=all&exp_filter={{ $ef }}"
               class="{{ $pf==='all' ? $activeClass : $inactiveClass }}"
               style="font-size:.78rem; border-radius:8px; padding:4px 12px;">الكل</a>

            {{-- 💡 التعديل: نطاق زمني مخصص (من - إلى) --}}
            <form method="GET" action="{{ url('/treasury') }}" class="d-flex align-items-center gap-2 m-0 ms-2 p-1 px-2 rounded-3" style="background:#f8fafc; border: 1px solid #cbd5e1;">
                <input type="hidden" name="profit_filter" value="custom">
                <input type="hidden" name="exp_filter" value="{{ request('exp_filter', '3months') }}">
                
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted fw-bold" style="font-size: 0.75rem;">من</span>
                    <input type="date" name="profit_from_date" value="{{ $pfFrom }}" class="form-control form-control-sm shadow-none {{ $pf==='custom' ? 'border-primary' : '' }}" style="font-size:.75rem; border-radius:6px; padding: 3px 6px; max-width: 120px;" required>
                </div>

                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted fw-bold" style="font-size: 0.75rem;">إلى</span>
                    <input type="date" name="profit_to_date" value="{{ $pfTo }}" class="form-control form-control-sm shadow-none {{ $pf==='custom' ? 'border-primary' : '' }}" style="font-size:.75rem; border-radius:6px; padding: 3px 6px; max-width: 120px;" required>
                </div>
                
                <button type="submit" class="btn btn-sm btn-dark" style="border-radius: 6px; padding: 3px 10px;" title="تطبيق الفلتر">
                    <i class="fa fa-search"></i>
                </button>
            </form>

            @if($pf !== 'all')
            <a href="{{ url('/treasury') }}" class="btn btn-sm btn-light text-danger fw-bold"
               style="font-size:.75rem; border-radius:8px; padding:4px 10px;" title="مسح فلتر الأرباح">
               <i class="fa fa-times"></i>
            </a>
            @endif

            <span class="badge ms-auto" style="background:#f1f5f9; color:#475569; font-size:.72rem; font-weight:700; border-radius:8px; padding:5px 10px;">
                <i class="fa fa-calendar-alt me-1"></i>{{ $filterLabel }}
            </span>

            @php
                $exportQs = 'profit_filter=' . $pf
                    . ($pfFrom ? '&profit_from_date=' . $pfFrom : '')
                    . ($pfTo   ? '&profit_to_date='   . $pfTo   : '');
            @endphp
            <a href="{{ url('/treasury/export') }}?{{ $exportQs }}" target="_blank" rel="noopener"
               class="btn btn-sm fw-bold text-white"
               style="background:#dc2626; font-size:.78rem; border-radius:8px; padding:4px 12px;"
               title="تصدير الخزنة والأرباح كـ PDF">
                <i class="fa fa-file-pdf me-1"></i> تصدير PDF
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4 animate__animated animate__fadeInUp profit-cards-row" style="animation-delay:.1s;">

    {{-- ── الربح الدفتري ── --}}
    <div class="col-profit">
        <div class="stat-card h-100 position-relative" style="background:linear-gradient(135deg,#0c4a6e,#0284c7);">
            <i class="fa-solid fa-file-invoice watermark"></i>

            <button type="button" class="btn-info-pill"
                    data-bs-toggle="popover" data-bs-placement="bottom" data-bs-html="true" data-bs-trigger="click"
                    data-bs-content='<div style="font-family:Cairo,sans-serif;font-size:.82rem;min-width:280px;direction:rtl">
                        <p class="fw-bold mb-2 text-primary">📒 الربح الدفتري — كيف يُحسب؟</p>
                        <p class="mb-1 text-muted">هو الربح "على الورق" بغض النظر عن التحصيل النقدي.</p>
                        <hr class="my-2">
                        <b>المعادلة:</b><br>
                        <code>(أرباح التقسيط + المبيعات النقدية + مبيعات المخزن + بيع أصول)</code><br>
                        <code>− (مصاريف تشغيلية + إهلاكات + مرتجعات + خصومات + ديون معدومة)</code>
                        <hr class="my-2">
                        <small class="text-danger">⚠️ قد يكون مرتفعاً حتى لو السيولة منخفضة — لأن أرباح الأقساط الغير محصولة محسوبة هنا.</small>
                    </div>'
                    title="<b>الربح الدفتري</b>">
                <i class="fa fa-info"></i>
            </button>

            <h6 class="fw-bold mb-1"><i class="fa-solid fa-book me-2"></i>الربح الدفتري (على الورق)</h6>
            <p class="mb-2" style="font-size:.7rem;opacity:.75;">إجمالي الإيرادات − إجمالي الخصومات</p>
            <h3 class="fw-bold mb-3">{{ fmtMoney($net_book_profit ?? 0) }} <span class="fs-6">ج</span></h3>

            {{-- تفصيل الإيرادات (تم الفصل بين المنتجات والخدمات) --}}
            <div style="font-size:.75rem;opacity:.9;">
                <div class="mb-1 fw-bold" style="border-bottom:1px solid rgba(255,255,255,.3);padding-bottom:4px;">
                    <i class="fa fa-plus-circle me-1"></i>مصادر الإيرادات
                </div>
                @if(isset($profit_breakdown))
                    @foreach($profit_breakdown as $item)
                        <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid rgba(255,255,255,.15);">
                            <span>{{ $item['label'] }}</span>
                            <span class="fw-bold">{{ fmtMoney($item['value']) }} ج</span>
                        </div>
                    @endforeach
                @endif
                <div class="d-flex justify-content-between py-1 mt-1 fw-bold">
                    <span>إجمالي الإيرادات</span>
                    <span class="text-warning">{{ fmtMoney($total_gross_revenue ?? 0) }} ج</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── الربح الحقيقي المحصل ── --}}
    <div class="col-profit">
        <div class="stat-card h-100 position-relative" style="background:linear-gradient(135deg,#065f46,#059669);">
            <i class="fa-solid fa-sack-dollar watermark"></i>

            <button type="button" class="btn-info-pill"
                    data-bs-toggle="popover" data-bs-placement="bottom" data-bs-html="true" data-bs-trigger="click"
                    data-bs-content='<div style="font-family:Cairo,sans-serif;font-size:.82rem;min-width:280px;direction:rtl">
                        <p class="fw-bold mb-2 text-success">💰 الربح الحقيقي — كيف يُحسب؟</p>
                        <p class="mb-1 text-muted">هو الربح المحصَّل نقداً فعلاً — الآمن للتوزيع على الشركاء.</p>
                        <hr class="my-2">
                        <b>المعادلة:</b><br>
                        <code>ما تحصَّل من العملاء (أقساط + مقدمات)</code><br>
                        <code>− تكلفة البضاعة الأصلية</code><br>
                        <code>+ أرباح المبيعات النقدية + بيع أصول</code><br>
                        <code>− المصاريف النقدية المدفوعة</code>
                        <hr class="my-2">
                        <small class="text-warning">⚠️ الإهلاكات لا تُطرح هنا لأنها مصروف دفتري فقط وليس خروجاً نقدياً.</small>
                    </div>'
                    title="<b>الربح الحقيقي</b>">
                <i class="fa fa-info"></i>
            </button>

            <h6 class="fw-bold mb-1"><i class="fa-solid fa-hand-holding-dollar me-2"></i>الربح الحقيقي (المحصل)</h6>
            <p class="mb-2" style="font-size:.7rem;opacity:.75;">المبلغ الآمن للتوزيع على الشركاء</p>
            <h3 class="fw-bold mb-3">{{ fmtMoney($real_collected_profit ?? 0) }} <span class="fs-6">ج</span></h3>

            @php
                $diff = ($net_book_profit ?? 0) - ($real_collected_profit ?? 0);
            @endphp
            <div style="font-size:.75rem;opacity:.9;">
                <div class="mb-1 fw-bold" style="border-bottom:1px solid rgba(255,255,255,.3);padding-bottom:4px;">
                    <i class="fa fa-equals me-1"></i>مقارنة مع الدفتري
                </div>
                <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid rgba(255,255,255,.15);">
                    <span>الربح الدفتري</span>
                    <span class="fw-bold">{{ fmtMoney($net_book_profit ?? 0) }} ج</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="border-bottom:1px solid rgba(255,255,255,.15);">
                    <span>الربح الحقيقي</span>
                    <span class="fw-bold text-warning">{{ fmtMoney($real_collected_profit ?? 0) }} ج</span>
                </div>
                <div class="d-flex justify-content-between py-1 mt-1 fw-bold">
                    <span>الفرق (أرباح لم تُحصَّل بعد)</span>
                    <span class="text-warning">{{ fmtMoney($diff) }} ج</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     الصف الثالث: كارت الخصومات والخسائر التفصيلي
════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4 animate__animated animate__fadeInUp" style="animation-delay:.2s;">

    <div class="col-md-6">
        <div class="account-card h-100" style="border-right:4px solid #10b981;text-align:right;">
            <h6 class="fw-bold mb-3" style="color:#065f46;">
                <i class="fa-solid fa-arrow-up-right-dots me-2"></i>تفصيل مصادر الإيرادات (منتجات وخدمات)
            </h6>
            @if(isset($profit_breakdown))
                @foreach($profit_breakdown as $item)
                <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                    <div>
                        <div class="fw-bold" style="font-size:.8rem;">{{ $item['label'] }}</div>
                        <div class="text-muted" style="font-size:.65rem;">{{ $item['source'] }}</div>
                    </div>
                    <span class="fw-bold text-success" style="font-size:.85rem;">{{ fmtMoney($item['value']) }} ج</span>
                </div>
                @endforeach
            @endif
            <div class="d-flex justify-content-between align-items-center pt-2 mt-1">
                <span class="fw-bold fs-6">إجمالي الإيرادات</span>
                <span class="fw-bold text-success fs-6">{{ fmtMoney($total_gross_revenue ?? 0) }} ج</span>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="account-card h-100" style="border-right:4px solid #ef4444;text-align:right;">
            <h6 class="fw-bold mb-3" style="color:#7f1d1d;">
                <i class="fa-solid fa-arrow-down-right-dots me-2"></i>تفصيل الخصومات والخسائر
            </h6>
            @if(isset($deductions_breakdown))
                @foreach($deductions_breakdown as $item)
                <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                    <div>
                        <div class="fw-bold" style="font-size:.8rem;">
                            {{ $item['label'] }}
                            @if(!$item['cash'])
                                <span class="badge ms-1" style="font-size:.6rem;background:#f59e0b;color:#fff;">دفتري فقط</span>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.65rem;">{{ $item['note'] }}</div>
                    </div>
                    <span class="fw-bold text-danger" style="font-size:.85rem;">{{ fmtMoney($item['value']) }} ج</span>
                </div>
                @endforeach
            @endif
            <div class="d-flex justify-content-between align-items-center pt-2 mt-1">
                <span class="fw-bold fs-6">إجمالي الخصومات</span>
                <span class="fw-bold text-danger fs-6">{{ fmtMoney($total_deductions ?? 0) }} ج</span>
            </div>
        </div>
    </div>
</div>
{{-- ════════════ شارت نمو رأس المال ════════════ --}}
<div class="animate__animated animate__fadeInUp mb-4" style="animation-delay:.35s;">
    <div style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,0.04); overflow:hidden;">
        <div style="padding:14px 18px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <h6 style="margin:0; font-weight:700; color:#0f172a;"><i class="fa fa-chart-line text-primary me-2"></i>نمو رأس المال (لقطات)</h6>
            <form method="GET" action="{{ url('/treasury') }}" class="d-flex gap-2 align-items-center flex-wrap ms-auto" style="font-size:.8rem;">
                @foreach(request()->except(['cap_period','cap_from','cap_to']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <select name="cap_period" class="form-select form-select-sm fw-bold" style="width:160px;" onchange="this.form.submit()">
                    <option value="1month"  {{ ($capitalChartPeriod??'3months') === '1month'  ? 'selected' : '' }}>آخر شهر</option>
                    <option value="3months" {{ ($capitalChartPeriod??'3months') === '3months' ? 'selected' : '' }}>آخر 3 شهور (افتراضي)</option>
                    <option value="6months" {{ ($capitalChartPeriod??'3months') === '6months' ? 'selected' : '' }}>آخر 6 شهور</option>
                    <option value="year"    {{ ($capitalChartPeriod??'3months') === 'year'    ? 'selected' : '' }}>آخر سنة</option>
                    <option value="custom"  {{ ($capitalChartPeriod??'3months') === 'custom'  ? 'selected' : '' }}>نطاق مخصص</option>
                </select>
                @if(($capitalChartPeriod??'3months') === 'custom')
                <input type="date" name="cap_from" value="{{ $capitalChartFrom ?? '' }}" class="form-control form-control-sm" style="width:125px;">
                <input type="date" name="cap_to"   value="{{ $capitalChartTo   ?? '' }}" class="form-control form-control-sm" style="width:125px;">
                <button type="submit" class="btn btn-sm btn-dark px-2"><i class="fa fa-search"></i></button>
                @endif
                <span class="badge bg-primary" style="font-size:.72rem;">{{ isset($capitalChartData) ? $capitalChartData->count() : 0 }} لقطة</span>
            </form>
        </div>
        @if(isset($capitalChartData) && $capitalChartData->count() > 1)
        <div style="padding:10px 14px;">
            <canvas id="treasuryCapChart" style="max-height:260px;"></canvas>
        </div>
        @else
        <div style="padding:28px; text-align:center; color:#9ca3af; font-size:.85rem;">
            <i class="fa fa-camera-retro fa-2x mb-2 d-block text-muted"></i>
            لا توجد لقطات في هذه الفترة — استخدم زر <strong>"حفظ لقطة للتقارير"</strong> لبدء تسجيل نمو رأس المال
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
            new bootstrap.Popover(el, { sanitize: false });
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-bs-toggle="popover"]')) {
                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
                    bootstrap.Popover.getInstance(el)?.hide();
                });
            }
        });

        @if(isset($capitalChartData) && $capitalChartData->count() > 1)
        (function () {
            const snapData = @json($capitalChartData);
            const ctx = document.getElementById('treasuryCapChart').getContext('2d');
            const grad = ctx.createLinearGradient(0, 0, 0, 260);
            grad.addColorStop(0, 'rgba(37,99,235,0.35)');
            grad.addColorStop(1, 'rgba(37,99,235,0.02)');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: snapData.map(d => d.label),
                    datasets: [{
                        label: 'رأس المال',
                        data: snapData.map(d => d.value),
                        borderColor: '#2563eb',
                        backgroundColor: grad,
                        fill: true, tension: 0.35, borderWidth: 2.5,
                        pointBackgroundColor: '#fff', pointBorderColor: '#2563eb',
                        pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(c) { return ' ' + c.raw.toLocaleString('ar-EG') + ' ج'; },
                                afterLabel: function(c) {
                                    const note = snapData[c.dataIndex]?.notes;
                                    return note ? '📝 ' + note : '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { ticks: { callback: v => v.toLocaleString('ar-EG') + ' ج', font: { family: 'Cairo', size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { ticks: { font: { family: 'Cairo', size: 10 }, maxTicksLimit: 12 }, grid: { display: false } }
                    }
                }
            });
        })();
        @endif
    });
</script>

<div class="modal fade" id="editBalanceModal" tabindex="-1" aria-labelledby="editBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('treasury.updateManualBalance') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold" id="editBalanceModalLabel">تعديل رصيد: <span id="modalAccountName" class="text-primary"></span></h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="account_id" id="modalAccountId">
                    <div class="mb-3">
                        <label for="modalAccountBalance" class="form-label fw-bold small text-muted">الرصيد الجديد (ج.م)</label>
                        <input type="number" step="0.01" name="new_balance" id="modalAccountBalance" class="form-control form-control-lg fw-bold text-center" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">حفظ التعديل</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="snapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('treasury.snapshot') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold m-0"><i class="fa fa-camera-retro me-2"></i> حفظ لقطة للتقارير والتحليل</h5>
                <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="alert alert-info fw-bold small border-info shadow-sm">
                    <i class="fa fa-info-circle me-1"></i> سيقوم النظام بالتقاط قيمة <strong>(رأس المال الإجمالي الحالي)</strong> و<strong>(أرصدة كل الخزائن والمحافظ بالقرش)</strong> وتجميدها في الأرشيف لكي تتمكن من العودة إليها لاحقاً واستخراج تقارير دقيقة (مثال: تقفيل شهر، تقفيل سنة).
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">ملاحظات على هذه اللقطة (اختياري)</label>
                    <input type="text" name="notes" class="form-control fw-bold border-secondary" placeholder="مثال: لقطة تقفيل شهر مايو، أو لقطة ما قبل جرد المخزن...">
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-white">
                <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark fw-bold px-4 rounded-pill"><i class="fa fa-save me-2"></i> حفظ اللقطة الآن بالداتابيز</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setEditData(id, name, balance) {
        document.getElementById('modalAccountId').value = id;
        document.getElementById('modalAccountName').innerText = name;
        document.getElementById('modalAccountBalance').value = balance;
    }
</script>
<script>
    (function () {
        const btn = document.getElementById('darkToggleBtn');
        const icon = btn.querySelector('i');

        function applyTheme(dark) {
            document.body.classList.toggle('dark-mode', dark);
            icon.className = dark ? 'fa fa-sun' : 'fa fa-moon';
        }

        applyTheme(localStorage.getItem('treasury-dark') === '1');

        btn.addEventListener('click', function () {
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('treasury-dark', isDark ? '0' : '1');
            applyTheme(!isDark);
        });
    })();
</script>
</body>
</html>