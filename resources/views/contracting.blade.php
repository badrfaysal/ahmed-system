<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المقاولات والتشطيبات | لوحة التحكم المتكاملة</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <style>
        :root {
            --main-blue: #0f172a;
            --main-dark: #1e293b;
            --bg-light: #f8fafc;
            --accent-blue: #2563eb;
            --accent-green: #059669;
            --accent-red: #dc2626;
            --border-color: #e2e8f0;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
        }
        .main-content {
            margin-right: 260px;
            padding: 30px 25px;
            min-height: 100vh;
        }
        @media (max-width: 991px) {
            .main-content { margin-right: 0 !important; width: 100% !important; padding: 70px 15px 30px !important; }
        }

        /* Nav Pills Styling - Professional Modern SaaS */
        .nav-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            background: transparent;
            padding: 0;
            margin-bottom: 22px;
        }
        .nav-pills .nav-link {
            color: #475569;
            font-weight: 700;
            border-radius: 50px;
            padding: 9px 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.92rem;
        }
        .nav-pills .nav-link:hover {
            color: #0f172a;
            border-color: #cbd5e1;
            background: #f1f5f9;
            transform: translateY(-1px);
        }
        .nav-pills .nav-link.active {
            background-color: #1e293b !important;
            color: #ffffff !important;
            border-color: #1e293b !important;
            box-shadow: 0 4px 10px rgba(15,23,42,0.18);
        }
        .nav-pills .nav-link.active i {
            color: #ffffff !important;
        }

        .section-title {
            font-weight: 800;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--accent-blue);
            display: inline-block;
            padding-bottom: 4px;
            color: #0f172a;
        }

        /* Project Autocomplete Search Dropdown */
        .project-search-item {
            transition: background 0.15s ease;
        }
        .project-search-item:hover {
            background-color: #f1f5f9;
        }

        /* POS Grid & Selection Cards */
        .pos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 15px;
        }
        .pos-btn {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 95px;
            height: auto;
            position: relative;
        }
        .pos-btn:hover {
            transform: translateY(-3px);
            border-color: var(--main-amber);
            box-shadow: 0 8px 18px rgba(217,119,6,0.15);
        }
        .pos-btn.active {
            background: var(--main-dark);
            color: white;
            border-color: var(--main-dark);
            box-shadow: 0 6px 20px rgba(15,23,42,0.3);
        }
        .pos-btn.active .text-muted {
            color: #cbd5e1 !important;
        }
        .pos-btn.active span {
            color: #ffffff !important;
        }
        .pos-btn i {
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: var(--main-amber);
        }
        .pos-btn.active i {
            color: #fbbf24;
        }
        .pos-btn .badge-sub {
            font-size: 0.75rem;
            position: absolute;
            top: 8px;
            left: 8px;
        }

        /* Operation Types Grid & Cards (المربعات - صف واحد متناسق) */
        .op-types-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr) !important;
            gap: 10px;
        }
        .op-type-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 10px 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100px;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            user-select: none;
        }
        .op-type-card:hover {
            transform: translateY(-2px);
            border-color: #2563eb;
            box-shadow: 0 6px 16px rgba(37,99,235,0.12);
        }
        .op-type-card.active {
            background: var(--main-dark) !important;
            border-color: var(--main-dark) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px rgba(15,23,42,0.3) !important;
            transform: translateY(-2px);
        }
        .op-type-card.active .op-type-title {
            color: #ffffff !important;
        }
        .op-type-card.active .op-type-subtitle {
            color: #94a3b8 !important;
        }
        .op-type-card .op-type-badge-active {
            display: none;
            position: absolute;
            top: 5px;
            left: 5px;
            font-size: 0.65rem;
            background: #10b981;
            color: white;
            padding: 1px 6px;
            border-radius: 20px;
            font-weight: 700;
        }
        .op-type-card.active .op-type-badge-active {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .op-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 6px;
            transition: all 0.25s ease;
        }
        .op-type-card.active .op-icon-box {
            background: rgba(255,255,255,0.15) !important;
            color: #ffffff !important;
            transform: scale(1.06);
        }
        .op-type-title {
            font-size: 0.86rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .op-type-subtitle {
            font-size: 0.7rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        @media (max-width: 768px) {
            .op-types-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        /* Cart Area */
        .pos-layout { display: flex; gap: 20px; }
        .pos-items-area { flex: 1; }
        .pos-cart-area {
            width: 370px;
            background: white;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            align-self: flex-start;
            position: sticky;
            top: 20px;
            border: 1px solid #e2e8f0;
        }
        @media(max-width: 991px) {
            .pos-layout { flex-direction: column; }
            .pos-cart-area { width: 100%; position: static; }
        }

        .cart-item {
            padding: 12px;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            transition: all 0.2s ease;
        }
        .cart-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }

        .kpi-card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            transition: transform 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
        }

        .section-title {
            font-weight: 900;
            margin-bottom: 15px;
            border-bottom: 3px solid var(--main-amber);
            display: inline-block;
            padding-bottom: 4px;
        }

        /* Limiting display tables to roughly 5 rows with vertical scrolling */
        .limit-5-rows,
        .table-scrollable-5rows {
            max-height: 290px !important;
            overflow-y: auto !important;
            overflow-x: auto !important;
            scrollbar-width: thin;
            position: relative;
        }
        .limit-5-rows thead th,
        .table-scrollable-5rows thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 5 !important;
            background-color: #f8fafc !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }
        .limit-5-rows thead.table-dark th,
        .table-scrollable-5rows thead.table-dark th {
            background-color: #1e293b !important;
            color: #fff !important;
        }

        .btn-xs {
            padding: 2px 7px !important;
            font-size: 11px !important;
            line-height: 1.35 !important;
        }

        /* Date & Range Filter Bar Styling */
        .date-filter-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 8px 14px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .date-filter-bar .btn-filter-preset {
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            transition: all 0.2s;
        }
        .date-filter-bar .btn-filter-preset:hover,
        .date-filter-bar .btn-filter-preset.active {
            background: var(--main-dark);
            color: #fff;
            border-color: var(--main-dark);
        }
    </style>
</head>
<body>

@php
    $defaultSafeId = null;
    $mainSafe = $accounts->first(fn($a) => str_contains($a->account_name ?? '', 'خزنة الشركة'));
    if (!$mainSafe) {
        $mainSafe = $accounts->first(fn($a) => str_contains($a->account_name ?? '', 'خزنة'));
    }
    $defaultSafeId = $mainSafe ? $mainSafe->id : ($accounts->first()->id ?? null);
@endphp

@include('sidebar')

<!-- DATALISTS FOR AUTOCOMPLETE (Instant Suggestion on All Inputs) -->
<datalist id="list-materials">
    @foreach($autocompleteMaterials as $m) <option value="{{ $m }}"></option> @endforeach
</datalist>

<datalist id="list-units">
    @foreach($autocompleteUnits as $u) <option value="{{ $u }}"></option> @endforeach
</datalist>

<datalist id="list-petty-titles">
    @foreach($pettyTitlesList as $pt) <option value="{{ $pt->title }}"></option> @endforeach
</datalist>

<datalist id="list-expense-titles">
    @foreach($expenseTitlesList as $et) <option value="{{ $et->title }}"></option> @endforeach
</datalist>

<datalist id="list-items">
    @foreach($autocompleteItemTitles as $it) <option value="{{ $it }}"></option> @endforeach
</datalist>

<datalist id="list-suppliers">
    @foreach($autocompleteSuppliers as $sup) <option value="{{ $sup }}"></option> @endforeach
</datalist>

<datalist id="list-techs">
    @foreach($autocompleteTechs as $tch) <option value="{{ $tch }}"></option> @endforeach
</datalist>

<datalist id="list-marketers">
    @foreach($autocompleteMarketers as $mkt) <option value="{{ $mkt }}"></option> @endforeach
</datalist>

<datalist id="list-specialties">
    <option value="سباك"></option>
    <option value="كهربائي"></option>
    <option value="نقاش"></option>
    <option value="مبلط سيراميك"></option>
    <option value="فني جبس بورد"></option>
    <option value="نجار باب وشباك"></option>
    <option value="فني عزل"></option>
    <option value="فني ألوميتال"></option>
    <option value="عامل تكسير ونظافة"></option>
</datalist>

<datalist id="list-projects">
    @foreach($projects as $p) <option value="{{ $p->name }}">{{ $p->client_name }}</option> @endforeach
</datalist>

<datalist id="list-clients">
    @foreach($clientsList as $cli) <option value="{{ is_object($cli) ? $cli->name : $cli }}"></option> @endforeach
</datalist>

<div class="main-content">
    <!-- Top Bar with Notification Alerts -->
    @if(session('success'))
        <div class="alert alert-success fw-bold rounded-4 shadow-sm mb-3">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fw-bold rounded-4 shadow-sm mb-3">
            <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Main Navigation Tabs Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-black text-dark mb-1"><i class="fa fa-building-user text-primary me-2"></i>إدارة المقاولات والتشطيبات</h3>
            <p class="text-muted small mb-0">لوحة التحكم المتكاملة لإدارة المشاريع، التكاليف، المقايسات، والتقارير المالية</p>
        </div>
        <div>
            <button class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNewProject">
                <i class="fa fa-plus-circle me-2"></i>مشروع جديد
            </button>
        </div>
    </div>

    <!-- Navigation Tabs (Unified Modern SaaS Nav Pills) -->
    <ul class="nav nav-pills mb-4" id="contractingTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-pos" type="button">
                <i class="fa fa-layer-group me-1"></i>تسجيل عملية
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-projects" type="button">
                <i class="fa fa-city me-1"></i>سجل المشاريع
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-survey" type="button">
                <i class="fa fa-ruler-combined me-1"></i>حاسبة المقايسة
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-estimation" type="button">
                <i class="fa fa-calculator me-1"></i>تقدير التكلفة
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-invoices" type="button">
                <i class="fa fa-file-invoice-dollar me-1"></i>الفواتير والمستحقات
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-prices" type="button">
                <i class="fa fa-boxes-stacked me-1"></i>أسعار الخامات
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-suppliers" type="button">
                <i class="fa fa-truck me-1"></i>مقارنة الموردين
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-directory" type="button">
                <i class="fa fa-address-book me-1"></i>سجل المتعاملين
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-reports" type="button">
                <i class="fa fa-chart-line me-1"></i>التقارير والأرباح
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button">
                <i class="fa fa-sliders me-1"></i>الإعدادات
            </button>
        </li>
    </ul>

    <!-- Tabs Content Container -->
    <div class="tab-content" id="contractingTabContent">
        
        <!-- ════════════════════════════════════════════════════════════════
             TAB 1: تسجيل عملية
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="tab-pos">
            <div class="pos-layout">
                <!-- Main Entry Area -->
                <div class="pos-items-area">
                    
                    <!-- 1. Select Project -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="section-title mb-0">1. اختر المشروع</h5>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" id="pos-project-search" class="form-control form-control-sm rounded-pill px-3" style="width: 220px;" placeholder="🔍 بحث في المشاريع..." oninput="filterProjectCards(this.value)">
                                <button class="btn btn-sm btn-outline-primary rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modalNewProject">
                                    <i class="fa fa-plus me-1"></i>مشروع جديد
                                </button>
                            </div>
                        </div>

                        <div class="pos-grid" id="projects-grid">
                            @forelse($projects as $p)
                                <div class="pos-btn" onclick="selectProject({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ addslashes($p->client_name) }}', {{ $p->supervision_pct }}, this)" data-name="{{ strtolower($p->name . ' ' . $p->client_name) }}" data-client="{{ $p->client_name }}">
                                    <i class="fa fa-building mb-2"></i>
                                    <span class="fw-bold fs-6 w-100 px-2" style="line-height: 1.35; word-break: break-word;">{{ $p->name }}</span>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4 text-muted" style="grid-column: 1 / -1;">
                                    لا توجد مشاريع مضافة حتى الآن. اضغط على "مشروع جديد" أو تابة الإعدادات للبدء.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- 2. Select Item (البند) -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 bg-white" id="step-items" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="section-title mb-0">2. اختر البند</h5>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" class="form-control form-control-sm rounded-pill px-3" style="width: 180px;" placeholder="🔍 بحث في البنود..." onkeyup="filterItemCards(this.value)">
                                <button class="btn btn-sm btn-outline-success rounded-pill fw-bold" onclick="showQuickAddItem()">
                                    <i class="fa fa-plus me-1"></i>إضافة بند للمشروع
                                </button>
                            </div>
                        </div>
                        <div class="pos-grid" id="items-grid">
                            <!-- Populated dynamically via JS when project is clicked -->
                        </div>
                    </div>

                    <!-- 3. Operation Form (مربعات اختيار نوع الحركة / تفاصيل الصرف) -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 bg-white" id="step-op-form" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="section-title mb-0">3. تفاصيل حركة الصرف</h5>
                                <span class="badge bg-primary text-white fs-6 rounded-pill px-3 py-2 fw-bold shadow-sm" id="selected-item-badge">بند غير محدد</span>
                            </div>
                            <button type="button" class="btn btn-outline-primary rounded-pill fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2" onclick="resetOpFormForNewEntry()">
                                <i class="fa fa-plus-circle fs-5"></i>
                                <span>إضافة عملية جديدة على هذا البند</span>
                            </button>
                        </div>

                        <!-- Operation Types Selection (مربعات) -->
                        <div class="op-types-grid mb-4" id="op-types-grid">
                            <!-- 1. Material -->
                            <div class="op-type-card" data-op="material" onclick="setOpType('material', this)">
                                <span class="op-type-badge-active"><i class="fa fa-check"></i> مختار</span>
                                <div class="op-icon-box bg-primary bg-opacity-10 text-primary">
                                    <i class="fa fa-boxes-stacked"></i>
                                </div>
                                <span class="fw-bold fs-6 op-type-title text-dark">خامات</span>
                                <small class="text-muted op-type-subtitle">فاتورة وتوريدات</small>
                            </div>

                            <!-- 2. Technician -->
                            <div class="op-type-card" data-op="technician" onclick="setOpType('technician', this)">
                                <span class="op-type-badge-active"><i class="fa fa-check"></i> مختار</span>
                                <div class="op-icon-box bg-warning bg-opacity-10 text-warning">
                                    <i class="fa fa-user-gear"></i>
                                </div>
                                <span class="fw-bold fs-6 op-type-title text-dark">مصنعية صنايعي / فني</span>
                                <small class="text-muted op-type-subtitle">أجور ومقاولات باطن</small>
                            </div>

                            <!-- 3. Petty Cash (Client) -->
                            <div class="op-type-card" data-op="petty" onclick="setOpType('petty', this)">
                                <span class="op-type-badge-active"><i class="fa fa-check"></i> مختار</span>
                                <div class="op-icon-box bg-info bg-opacity-10 text-info">
                                    <i class="fa fa-receipt"></i>
                                </div>
                                <span class="fw-bold fs-6 op-type-title text-dark">مصروف نثري</span>
                                <small class="text-muted op-type-subtitle">يتحمله العميل (نقل/عتالة)</small>
                            </div>

                            <!-- 4. Overhead (Internal) -->
                            <div class="op-type-card" data-op="overhead" onclick="setOpType('overhead', this)">
                                <span class="op-type-badge-active"><i class="fa fa-check"></i> مختار</span>
                                <div class="op-icon-box bg-danger bg-opacity-10 text-danger">
                                    <i class="fa fa-hand-holding-dollar"></i>
                                </div>
                                <span class="fw-bold fs-6 op-type-title text-dark">مصروف داخلي</span>
                                <small class="text-muted op-type-subtitle">تتحمله الشركة (إداري)</small>
                            </div>

                            <!-- 5. Marketer Commission -->
                            <div class="op-type-card" data-op="commission" onclick="setOpType('commission', this)">
                                <span class="op-type-badge-active"><i class="fa fa-check"></i> مختار</span>
                                <div class="op-icon-box bg-success bg-opacity-10 text-success">
                                    <i class="fa fa-bullhorn"></i>
                                </div>
                                <span class="fw-bold fs-6 op-type-title text-dark">عمولة مسوق</span>
                                <small class="text-muted op-type-subtitle">عمولة على المشروع ككل</small>
                            </div>
                        </div>

                        <!-- Prompt placeholder before selecting an operation square -->
                        <div id="op-prompt-select" class="text-center py-4 px-3 bg-light rounded-4 border border-dashed mb-3">
                            <div class="mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                                    <i class="fa fa-hand-pointer me-1"></i> الخطوة التالية: اختر نوع العملية
                                </span>
                            </div>
                            <p class="text-muted mb-0 fw-semibold">اضغط على أي من المربعات أعلاه (خامات، مصنعية، مصروف...) لفتح النموذج والتسجيل مباشرة</p>
                        </div>

                        <!-- ══ SECTION A: خامات كفاتورة مجمعة (يمكن إدخال 100 خامة معاً) ══ -->
                        <div id="section-material-invoice" style="display: none;">
                            <!-- Invoice Header Details -->
                            <div class="p-3 bg-light rounded-4 border mb-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-dark mb-0">اسم المورد <span class="text-danger">*</span></label>
                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill fw-bold" onclick="openQuickAddPersonModal('supplier')">
                                                <i class="fa fa-plus me-1"></i>تسجيل مورد جديد
                                            </button>
                                        </div>
                                        <select id="inv-supplier" class="form-select rounded-3 fw-bold" onchange="onSupplierChange(this.value)">
                                            <option value="">-- اختر المورد من القائمة --</option>
                                            @foreach($suppliers as $s)
                                                <option value="{{ $s->name }}">{{ $s->name }}{{ $s->phone ? ' (' . $s->phone . ')' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-dark mb-1">
                                            اسم / رقم الفاتورة 
                                            <small class="text-primary fw-normal">(مقترح تلقائياً ويمكن تعديله)</small>
                                        </label>
                                        <input type="text" id="inv-ref" class="form-control rounded-3 fw-bold bg-white" placeholder="فاتورة خامات..." oninput="this.dataset.manualEdited='true'">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-light border rounded-3 p-2 mb-0 small text-center text-muted shadow-sm">
                                            <i class="fa fa-clock text-warning me-1"></i> تُسجل الفاتورة تلقائياً <b>آجل (دين للمورد)</b> وتُسدد لاحقاً من شاشة الديون
                                        </div>
                                        <input type="hidden" id="inv-payment-method" value="later">
                                        <input type="hidden" id="inv-paid" value="0">
                                        <input type="hidden" id="inv-debt" value="0">
                                        <input type="hidden" id="inv-account" value="">
                                    </div>
                                </div>
                            </div>

                            <!-- Materials Table (Holds up to 100+ items) -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-dark"><i class="fa fa-boxes-stacked text-primary me-2"></i>سطور الخامات في الفاتورة (يمكنك إضافة أي عدد من الخامات):</h6>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-bold" onclick="addMaterialRow()">
                                        <i class="fa fa-plus me-1"></i>+ إضافة خامة أخرى
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="addMultipleMaterialRows(5)">
                                        + إضافة 5 خامات
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive mb-3 border rounded-3 bg-white" style="max-height: 420px; overflow-y: auto;">
                                <table class="table table-sm table-bordered text-center align-middle mb-0" id="table-material-invoice">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width: 38px;">#</th>
                                            <th class="text-start" style="min-width: 240px; width: 32%;">اسم الخامة <span class="text-danger">*</span></th>
                                            <th style="width: 90px; min-width: 80px;">الكمية</th>
                                            <th style="width: 120px; min-width: 110px;">سعر الشراء (عليك)</th>
                                            <th style="width: 95px; min-width: 85px;">نسبة الإشراف %</th>
                                            <th style="width: 120px; min-width: 110px;">سعر البيع (للعميل)</th>
                                            <th style="width: 110px; min-width: 100px;">إجمالي الشراء</th>
                                            <th style="width: 110px; min-width: 100px;">إجمالي البيع</th>
                                            <th style="width: 40px;">حذف</th>
                                        </tr>
                                    </thead>
                                    <tbody id="material-invoice-tbody">
                                        <!-- Dynamically created rows via addMaterialRow() -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Invoice Live Summary Footer -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="row g-2 text-center align-items-center mb-3">
                                    <div class="col-md-2 col-6">
                                        <span class="text-muted small d-block">إجمالي التكلفة (عليك):</span>
                                        <span class="fs-5 fw-black text-danger" id="inv-total-cost">0.00 ج</span>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <span class="text-muted small d-block">سعر البيع الأساسي:</span>
                                        <span class="fs-5 fw-bold text-secondary" id="inv-base-selling">0.00 ج</span>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <span class="text-muted small d-block">نسبة الإشراف:</span>
                                        <span class="fs-5 fw-bold text-warning" id="inv-supervision-val">+ 0.00 ج</span>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <span class="text-muted small d-block text-primary fw-bold">إجمالي المطالبة (على العميل):</span>
                                        <span class="fs-5 fw-black text-primary" id="inv-total-selling">0.00 ج</span>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <span class="text-muted small d-block text-success fw-bold">صافي المكسب المتوقع:</span>
                                        <span class="fs-5 fw-black text-success" id="inv-total-profit">0.00 ج</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 pt-2 border-top flex-wrap">
                                    <button type="button" class="btn btn-primary rounded-pill fw-bold px-4 py-2 shadow-sm" onclick="addInvoiceToCart()">
                                        <i class="fa fa-box-archive me-2"></i>+ إضافة الفاتورة إلى صندوق العمليات
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ══ SECTION B: مصنعية صنايعي / فني ══ -->
                        <div id="section-technician" style="display: none;">
                            <div class="p-3 bg-light rounded-4 border mb-3">
                                <!-- Row 1: Basic Info (صف واحد متناسق) -->
                                <div class="row g-3 align-items-end mb-3">
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small text-dark mb-0">اسم الفني / الصنايعي <span class="text-danger">*</span></label>
                                            <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill fw-bold" onclick="openQuickAddPersonModal('technician')">
                                                <i class="fa fa-plus me-1"></i>تسجيل صنايعي جديد
                                            </button>
                                        </div>
                                        <select id="tech-person" class="form-select rounded-3 fw-bold">
                                            <option value="">-- اختر الفني من القائمة --</option>
                                            @foreach($technicians as $t)
                                                <option value="{{ $t->name }}">{{ $t->name }}{{ $t->specialty ? ' - ' . $t->specialty : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold small text-dark mb-1">بيان العمل المنجز <small class="text-muted fw-normal">(اختياري - يُكتب تلقائياً من البند)</small></label>
                                        <input type="text" id="tech-title" class="form-control rounded-3" placeholder="مثال: تأسيس مواسير السباكة (أو اتركه ليُسمى باسم البند)...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-dark mb-1"><i class="fa fa-calculator text-primary me-1"></i>نظام المحاسبة</label>
                                        <select id="tech-work-type" class="form-select rounded-3 fw-bold" onchange="onTechWorkTypeChange(this.value)">
                                            <option value="lump_sum" selected>مقاولة مقطوعة (مبلغ ثابت)</option>
                                            <option value="meter">بالمتر (سعر المتر × الأمتار)</option>
                                            <option value="day">باليومية (أجر اليوم × الأيام)</option>
                                            <option value="piece">بالقطعة (سعر القطعة × القطع)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Row 2: Calculation & Pricing (كل الحسابات على صف واحد متناسق) -->
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-2 col-6">
                                        <label class="form-label fw-bold small text-dark mb-1" id="lbl-tech-qty">الكمية</label>
                                        <input type="number" id="tech-qty" class="form-control rounded-3 text-center fw-bold" value="1" step="any" oninput="calcTechLive()">
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label fw-bold small text-danger mb-1" id="lbl-tech-cost">أجر الفني (تكلفة عليك) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" id="tech-cost" class="form-control rounded-start-3 fw-bold text-danger" placeholder="0.00" step="any" oninput="onTechCostChange(this.value)">
                                            <span class="input-group-text">ج</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label fw-bold small text-warning mb-1">نسبة الإشراف %</label>
                                        <input type="number" id="tech-sup" class="form-control rounded-3 text-center fw-bold text-warning" placeholder="%" step="any" oninput="onTechSupChange(this.value)">
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <label class="form-label fw-bold small text-primary mb-1" id="lbl-tech-sell">سعر بيع المصنعية للعميل</label>
                                        <div class="input-group">
                                            <input type="number" id="tech-sell" class="form-control rounded-start-3 fw-bold text-primary" placeholder="0.00" step="any" oninput="onTechSellChange(this.value)">
                                            <span class="input-group-text">ج</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="alert alert-light border rounded-3 py-2 px-3 mb-0 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <span class="text-muted">
                                            <i class="fa fa-clock text-warning me-1"></i>أجر وحساب الفني يُسجل تلقائياً <strong>آجل (دين ومستحقات على الشركة)</strong>، ويتم السداد الفعلي لاحقاً عبر أذونات الصرف والديون.
                                        </span>
                                        <span class="badge bg-danger rounded-pill px-3 py-1">آجل بالكامل (مستحق للفني)</span>
                                    </div>
                                </div>
                                
                                <!-- Tech Live Summary (مظبوط: الأرقام في صف واحد والزرار في شريط سفلي منفصل) -->
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-4 border shadow-sm">
                                        <!-- Row 1: The 5 Live Numbers on One Grid Row -->
                                        <div class="row g-2 text-center align-items-center mb-3">
                                            <div class="col-md col-6">
                                                <span class="text-muted small d-block mb-1">إجمالي التكلفة عليك:</span>
                                                <span class="fs-5 fw-black text-danger" id="tech-live-cost">0.00 ج</span>
                                            </div>
                                            <div class="col-md col-6">
                                                <span class="text-muted small d-block mb-1">سعر البيع الأساسي:</span>
                                                <span class="fs-5 fw-bold text-secondary" id="tech-live-sell">0.00 ج</span>
                                            </div>
                                            <div class="col-md col-6">
                                                <span class="text-muted small d-block mb-1">نسبة الإشراف:</span>
                                                <span class="fs-5 fw-bold text-warning" id="tech-live-sup">+ 0.00 ج</span>
                                            </div>
                                            <div class="col-md col-6">
                                                <span class="text-muted small d-block mb-1 text-primary fw-bold">المطالبة على العميل:</span>
                                                <span class="fs-5 fw-black text-primary" id="tech-live-customer">0.00 ج</span>
                                            </div>
                                            <div class="col-md col-12">
                                                <span class="text-muted small d-block mb-1 text-success fw-bold">صافي المكسب:</span>
                                                <span class="fs-5 fw-black text-success" id="tech-live-profit">0.00 ج</span>
                                            </div>
                                        </div>

                                        <!-- Row 2: Action Button in dedicated bottom bar with no overlapping -->
                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top flex-wrap gap-2">
                                            <small class="text-muted"><i class="fa fa-info-circle text-primary me-1"></i>تُحفظ المصنعية كحركة في صندوق العمليات وتُعتمد مع الفاتورة</small>
                                            <button type="button" class="btn btn-warning rounded-pill fw-bold px-4 py-2 text-dark shadow-sm" onclick="addTechToCart()">
                                                <i class="fa fa-box-archive me-2"></i>+ إضافة المصنعية إلى صندوق العمليات
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ══ SECTION C: مصروف داخلي للشركة ══ -->
                        <div id="section-overhead" style="display: none;">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">بيان المصروف الداخلي <span class="text-danger">*</span></label>
                                    <input type="text" id="ovh-title" class="form-control rounded-3" list="list-expense-titles" placeholder="مثال: هالك مواسير، غرامة موقع، تصليح عيب تشغيل...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-danger">قيمة المصروف (عليك) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" id="ovh-amount" class="form-control rounded-start-3 fw-bold text-danger" placeholder="0.00" step="any">
                                        <span class="input-group-text">ج</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark"><i class="fa fa-vault text-danger me-1"></i>الخزينة / الحساب للصرف منه <span class="text-danger">*</span></label>
                                    <select id="ovh-account" class="form-select rounded-3 fw-bold border-danger">
                                        <option value="">-- اختر الخزينة أو الحساب المالي --</option>
                                        @foreach($accounts as $idx => $acc)
                                            <option value="{{ $acc->id }}" {{ ($acc->id == ($defaultSafeId ?? null) || $idx === 0) ? 'selected' : '' }}>{{ $acc->account_name ?? $acc->name }} (الرصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="ovh-payment-method" value="cash">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-light border rounded-3 py-2 px-3 mb-0 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <span class="text-muted">
                                            <i class="fa fa-vault text-danger me-1"></i>يُخصم المصروف الداخلي مباشرة من <strong>الخزينة المختارة</strong> عند اعتماد صندوق العمليات (ولا يتحمله العميل).
                                        </span>
                                        <span class="badge bg-dark rounded-pill px-3 py-1">صرف مباشر من الخزينة</span>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-3 flex-wrap">
                                    <button type="button" class="btn btn-danger rounded-pill fw-bold px-4 py-2 shadow-sm" onclick="addOverheadToCart()">
                                        <i class="fa fa-box-archive me-2"></i>+ إضافة المصروف إلى صندوق العمليات
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ══ SECTION E: مصروف نثري (يتحمله العميل) ══ -->
                        <div id="section-petty" style="display: none;">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">بيان المصروف النثري <span class="text-danger">*</span></label>
                                    <input type="text" id="petty-title" class="form-control rounded-3 fw-bold" list="list-petty-titles" placeholder="مثال: ونش رفع، إكراميات، مشال...">
                                </div>
                                <div class="col-md-2 col-6">
                                    <label class="form-label fw-bold text-danger">التكلفة (عليك) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" id="petty-cost" class="form-control rounded-start-3 fw-bold text-danger" placeholder="0.00" step="any" oninput="onPettyCostChange(this.value)">
                                        <span class="input-group-text">ج</span>
                                    </div>
                                </div>
                                <div class="col-md-1 col-6">
                                    <label class="form-label fw-bold text-warning">الإشراف%</label>
                                    <input type="number" id="petty-sup" class="form-control rounded-3 text-center fw-bold text-warning px-1" placeholder="%" step="any" oninput="onPettySupChange(this.value)">
                                </div>
                                <div class="col-md-2 col-6">
                                    <label class="form-label fw-bold text-primary">على العميل <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" id="petty-sell" class="form-control rounded-start-3 fw-bold text-primary" placeholder="0.00" step="any" oninput="onPettySellChange(this.value)">
                                        <span class="input-group-text">ج</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label fw-bold text-dark"><i class="fa fa-vault text-info me-1"></i>الخزينة للصرف منها <span class="text-danger">*</span></label>
                                    <select id="petty-account" class="form-select rounded-3 fw-bold border-info">
                                        <option value="">-- اختر الخزينة --</option>
                                        @foreach($accounts as $idx => $acc)
                                            <option value="{{ $acc->id }}" {{ ($acc->id == ($defaultSafeId ?? null) || $idx === 0) ? 'selected' : '' }}>{{ $acc->account_name ?? $acc->name }} ({{ number_format($acc->balance, 2) }} ج)</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="petty-payment-method" value="cash">
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-light border rounded-3 py-2 px-3 mb-0 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <span class="text-muted">
                                            <i class="fa fa-vault text-info me-1"></i>يُصرف المبلغ من <strong>الخزينة المختارة</strong> ويتحمله العميل في مستحقات المشروع.
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-info text-dark rounded-pill px-3 py-1">صرف مباشر من الخزينة</span>
                                            <span class="text-muted ms-2">صافي الربح المتوقع:</span>
                                            <span class="fw-bold text-success fs-6" id="petty-live-profit">0.00 ج</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-info rounded-pill fw-bold px-4 py-2 text-white shadow-sm" onclick="addPettyToCart()">
                                        <i class="fa fa-box-archive me-2"></i>+ إضافة المصروف النثري إلى صندوق العمليات
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ══ SECTION D: عمولة مسوق (على المشروع ككل) ══ -->
                        <div id="section-commission" style="display: none;">
                            <div class="alert alert-success d-flex align-items-center rounded-3 mb-3">
                                <i class="fa fa-bullhorn fs-3 me-3 text-success"></i>
                                <div>
                                    <div class="fw-bold">عمولة المسوق تطبق على إجمالي المشروع ككل</div>
                                    <div class="small">يتم خصم هذه العمولة مباشرة من صافي أرباح المشروع الإجمالية، ولا ترتبط بأي بند مفرد.</div>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-bold mb-0">اسم المسوق / الوسيط <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill fw-bold" onclick="openQuickAddPersonModal('marketer')">
                                            <i class="fa fa-plus me-1"></i>تسجيل مسوق جديد
                                        </button>
                                    </div>
                                    <input type="text" id="comm-name" class="form-control rounded-3" list="list-marketers" placeholder="اسم المسوق..." required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-success mb-1">مبلغ العمولة المستحقة <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" id="comm-val" class="form-control rounded-start-3 fs-5 fw-bold" placeholder="0.00" step="any" required>
                                        <span class="input-group-text">ج</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold mb-1">ملاحظات / اتفاق العمولة</label>
                                    <input type="text" id="comm-note" class="form-control rounded-3" placeholder="اختياري...">
                                </div>
                                <input type="hidden" id="comm-method" value="later">
                                <input type="hidden" id="comm-acc" value="">
                                <div class="col-12">
                                    <div class="alert alert-light border rounded-3 py-2 px-3 mb-0 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <span class="text-muted">
                                            <i class="fa fa-clock text-warning me-1"></i>تُسجل عمولة المسوق تلقائياً <strong>آجل (دين للمسوق في شاشة الديون)</strong>.
                                        </span>
                                        <span class="badge bg-danger rounded-pill px-3 py-1">آجل بالكامل (دين مسوق)</span>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-3 flex-wrap">
                                    <button type="button" class="btn btn-success rounded-pill fw-bold px-4 py-2 shadow-sm" onclick="addCommissionToCart()">
                                        <i class="fa fa-box-archive me-2"></i>+ إضافة العمولة إلى صندوق العمليات
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Sticky Left Cart Area (صندوق العمليات) -->
                <div class="pos-cart-area">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-black mb-0 text-primary"><i class="fa fa-box-archive text-warning me-2"></i>صندوق العمليات</h6>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-primary rounded-pill px-2 py-1" id="cart-count-badge">0 عمليات</span>
                            <button class="btn btn-sm btn-outline-danger rounded-pill border-0" onclick="clearCart()" title="تفريغ صندوق العمليات">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="text-muted small mb-2" id="cart-project-info">المشروع: لم يتم الاختيار</div>

                    <!-- Cart Items Container -->
                    <div id="cart-items-container" style="max-height: 380px; overflow-y: auto;">
                        <div class="text-center py-4 text-muted">
                            <i class="fa fa-box-open fs-2 mb-2 text-secondary"></i>
                            <div class="fw-bold">صندوق العمليات فارغ حالياً</div>
                            <small class="text-muted">أضف خامات أو مصنعيات أو نثريات لتتجمع هنا وتراجعها معاً قبل الحفظ</small>
                        </div>
                    </div>

                    <!-- Cart Summary Totals -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">إجمالي التكلفة (عليك):</span>
                            <span class="fw-bold text-danger" id="cart-total-cost">0.00 ج</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">إجمالي البيع الأساسي:</span>
                            <span class="fw-bold text-secondary" id="cart-base-selling">0.00 ج</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">نسبة الإشراف:</span>
                            <span class="fw-bold text-warning" id="cart-supervision-val">+ 0.00 ج</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted fw-bold text-primary">المطالبة شاملة الإشراف (للعميل):</span>
                            <span class="fw-bold text-primary" id="cart-total-selling">0.00 ج</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted fw-bold text-success">صافي المكسب المتوقع:</span>
                            <span class="fw-bold text-success" id="cart-total-profit">0.00 ج</span>
                        </div>

                        <button class="btn btn-success w-100 rounded-pill fw-black py-2 mt-2 shadow fs-6" onclick="openReviewModal()">
                            <i class="fa fa-clipboard-check me-2"></i>مراجعة واعتماد صندوق العمليات
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 2: سجل المشاريع
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-projects">
            <!-- Project Selector Bar -->
            @php
                $uniqueClients = $projects->pluck('client_name')->filter()->unique()->values();
            @endphp
            <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
                <div class="row align-items-end g-3">
                    <!-- 1. Select or Search Client -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-dark mb-1"><i class="fa fa-users text-primary me-1"></i>1. تصفية حسب العميل:</label>
                        <div class="position-relative" id="client-search-wrapper-360">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted"><i class="fa fa-search"></i></span>
                                <input type="text" id="input-client-search-360" class="form-control border-start-0 border-end-0 fw-bold" value="-- جميع العملاء ({{ $uniqueClients->count() }}) --" placeholder="🔍 ابحث عن اسم العميل..." autocomplete="off" oninput="filterClientSearchDropdown360(this.value)" onfocus="this.select(); showClientSearchDropdown360(true);">
                                <button class="btn btn-light border-top border-bottom text-muted px-2" type="button" title="مسح وإظهار الكل" onclick="clearClientSearch360()"><i class="fa fa-times"></i></button>
                                <button class="btn btn-outline-secondary border-start-0 rounded-end-pill dropdown-toggle" type="button" onclick="toggleClientSearchDropdown360()"></button>
                            </div>
                            <select id="select-360-client-filter" class="d-none" onchange="filterProjectsByClient(this.value)">
                                <option value="all">-- جميع العملاء ({{ $uniqueClients->count() }}) --</option>
                                @foreach($uniqueClients as $clientName)
                                    <option value="{{ $clientName }}">👤 {{ $clientName }}</option>
                                @endforeach
                            </select>
                            <!-- Client Autocomplete Dropdown List -->
                            <div id="client-search-dropdown-360" class="position-absolute w-100 bg-white border rounded-4 shadow-lg p-2 mt-1 z-3" style="display: none; max-height: 260px; overflow-y: auto;">
                                <div class="client-search-item-360 p-2 rounded-3 cursor-pointer d-flex justify-content-between align-items-center mb-1 bg-light"
                                     data-client="all"
                                     onclick="chooseClientFromSearch360('all', '-- جميع العملاء ({{ $uniqueClients->count() }}) --')">
                                    <strong class="text-primary"><i class="fa fa-users me-2"></i>جميع العملاء ({{ $uniqueClients->count() }})</strong>
                                </div>
                                @foreach($uniqueClients as $clientName)
                                    @php
                                        $clientProjCount = $projects->where('client_name', $clientName)->count();
                                    @endphp
                                    <div class="client-search-item-360 p-2 rounded-3 cursor-pointer d-flex justify-content-between align-items-center mb-1"
                                         data-client="{{ $clientName }}"
                                         onclick="chooseClientFromSearch360('{{ addslashes($clientName) }}', '👤 {{ addslashes($clientName) }}')">
                                        <div>
                                            <strong class="text-dark"><i class="fa fa-user text-primary me-2"></i>{{ $clientName }}</strong>
                                        </div>
                                        <span class="badge bg-light text-dark border">{{ $clientProjCount }} مشروع</span>
                                    </div>
                                @endforeach
                                <div id="client-search-no-results-360" class="text-center py-3 text-muted small" style="display: none;">
                                    <i class="fa fa-circle-exclamation me-1 text-danger"></i>لا يوجد عميل يطابق بحثك
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Select or Search Project -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-dark mb-1"><i class="fa fa-folder-open text-warning me-1"></i>2. اختر أو ابحث عن المشروع:</label>
                        <div class="position-relative" id="project-search-wrapper">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted"><i class="fa fa-search"></i></span>
                                <input type="text" id="input-project-search" class="form-control border-start-0 border-end-0 fw-bold" placeholder="🔍 ابحث أو اضغط لاختيار المشروع..." autocomplete="off" oninput="filterProjectSearchDropdown(this.value)" onfocus="this.select(); showProjectSearchDropdown(true);">
                                <button class="btn btn-light border-top border-bottom text-muted px-2" type="button" title="مسح البحث" onclick="clearProjectSearch360()"><i class="fa fa-times"></i></button>
                                <button class="btn btn-outline-secondary border-start-0 rounded-end-pill dropdown-toggle" type="button" onclick="toggleProjectSearchDropdown()"></button>
                            </div>
                            <select class="d-none" id="select-project-360" onchange="select360Project(this.value)">
                                <option value="">-- اضغط لاختيار المشروع --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-client="{{ $p->client_name }}">{{ $p->name }} - (العميل: {{ $p->client_name }})</option>
                                @endforeach
                            </select>
                            <!-- Autocomplete Dropdown List (Hidden by default) -->
                            <div id="project-search-dropdown" class="position-absolute w-100 bg-white border rounded-4 shadow-lg p-2 mt-1 z-3" style="display: none; max-height: 260px; overflow-y: auto;">
                                @foreach($projects as $p)
                                    <div class="project-search-item p-2 rounded-3 cursor-pointer d-flex justify-content-between align-items-center mb-1" 
                                         data-id="{{ $p->id }}" 
                                         data-name="{{ $p->name }}" 
                                         data-client="{{ $p->client_name }}"
                                         onclick="chooseProjectFromSearch({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ addslashes($p->client_name) }}')">
                                        <div>
                                            <strong class="text-dark"><i class="fa fa-building text-warning me-2"></i>{{ $p->name }}</strong>
                                            <div class="small text-muted"><i class="fa fa-user me-1"></i>العميل: {{ $p->client_name }}</div>
                                        </div>
                                        <span class="badge bg-light text-primary border">#PRJ-{{ $p->id }}</span>
                                    </div>
                                @endforeach
                                <div id="project-search-no-results" class="text-center py-3 text-muted small" style="display: none;">
                                    <i class="fa fa-circle-exclamation me-1 text-danger"></i>لا يوجد مشروع يطابق بحثك
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Quick Actions -->
                    <div class="col-md-5 d-flex justify-content-end align-items-end gap-2 flex-wrap" id="project-quick-actions" style="display: none;">
                        <button class="btn btn-sm btn-outline-danger rounded-pill fw-bold" onclick="openDiscountModal()">
                            <i class="fa fa-tags me-1"></i>إضافة خصم
                        </button>
                        <button class="btn btn-sm btn-outline-primary rounded-pill fw-bold" onclick="openInstallmentModal()">
                            <i class="fa fa-credit-card me-1"></i>تحويل لأقساط
                        </button>
                        <button class="btn btn-sm btn-outline-info rounded-pill fw-bold" onclick="openCommissionModal()">
                            <i class="fa fa-bullhorn me-1"></i>عمولة مسوق
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-dark rounded-pill fw-bold dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fa fa-print me-1"></i>طباعة كشف الحساب
                            </button>
                            <ul class="dropdown-menu" style="min-width: 240px;">
                                <li><a class="dropdown-item fw-bold" href="#" id="link-print-summary" target="_blank"><i class="fa fa-list me-2"></i>كشف مختصر (بنود)</a></li>
                                <li><a class="dropdown-item fw-bold" href="#" id="link-print-detailed" target="_blank"><i class="fa fa-receipt me-2"></i>كشف مفصل للعميل (كل المشروع)</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header fw-bold text-primary"><i class="fa fa-layer-group me-1"></i>كشف حساب لبند معين:</h6></li>
                                <div id="dropdown-print-items-list">
                                    <li><span class="dropdown-item-text small text-muted">اختر المشروع لعرض البنود</span></li>
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item fw-bold text-danger" href="#" id="link-print-internal" target="_blank"><i class="fa fa-shield-halved me-2"></i>كشف تحليلي داخلي (للشركة فقط)</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Project Chips for Selected Client Only (Hidden by default so design stays clean) -->
                    <div class="col-12 mt-2 pt-2 border-top" id="chips-projects-wrapper" style="display: none;">
                        <div class="d-flex flex-wrap gap-2 align-items-center" id="chips-projects-container">
                            <span class="small fw-bold text-muted"><i class="fa fa-building me-1 text-warning"></i>مشاريع العميل المختار:</span>
                            @foreach($projects as $p)
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill fw-bold btn-360-chip" id="chip-360-{{ $p->id }}" data-client="{{ $p->client_name }}" onclick="select360Project({{ $p->id }})" style="display: none;">
                                    <i class="fa fa-building text-warning me-1"></i>{{ $p->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project 360 Content Area -->
            <div id="project-360-container" style="display: none;">
                <!-- Project Info Header Bar (بيانات المشروع والمساحة) -->
                <div class="card border-0 rounded-4 shadow-sm p-3 mb-3 bg-white border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 fs-3 d-none d-md-flex">
                                <i class="fa fa-building"></i>
                            </div>
                            <div>
                                <h4 class="fw-black mb-1 text-dark" id="p360-name">-</h4>
                                <div class="text-muted small">
                                    <span class="me-3"><i class="fa fa-user me-1 text-secondary"></i>العميل: <strong id="p360-client" class="text-dark">-</strong></span>
                                    <span class="me-3"><i class="fa fa-map-marker-alt me-1 text-danger"></i>الموقع: <span id="p360-location">-</span></span>
                                    <span class="me-3"><i class="fa fa-percent me-1 text-warning"></i>نسبة الإشراف: <span id="p360-sup">-</span>%</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="p-2 px-3 bg-light rounded-3 text-center border border-success shadow-sm">
                                <small class="text-muted d-block fw-bold"><i class="fa fa-ruler-combined text-success me-1"></i>مساحة المشروع:</small>
                                <span class="fw-black text-success fs-5" id="p360-area">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Statistics Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <div class="kpi-card text-center border-start border-4 border-danger">
                            <span class="text-muted small fw-bold">إجمالي التكلفة عليك</span>
                            <h4 class="fw-black text-danger mt-1 mb-0" id="kpi-cost">0 ج</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center border-start border-4 border-primary">
                            <span class="text-muted small fw-bold">إجمالي بيع الأعمال</span>
                            <h4 class="fw-black text-primary mt-1 mb-0" id="kpi-selling">0 ج</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center border-start border-4 border-secondary">
                            <span class="text-muted small fw-bold">الخصم الممنوح</span>
                            <h4 class="fw-black text-secondary mt-1 mb-0" id="kpi-discount">0 ج</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center border-start border-4 border-success">
                            <span class="text-muted small fw-bold">المحصل من العميل</span>
                            <h4 class="fw-black text-success mt-1 mb-0" id="kpi-paid">0 ج</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center border-start border-4 border-warning">
                            <span class="text-muted small fw-bold">المتبقي على العميل</span>
                            <h4 class="fw-black text-warning mt-1 mb-0" id="kpi-remaining">0 ج</h4>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center border-start border-4 border-dark">
                            <span class="text-muted small fw-bold">صافي ربح المشروع</span>
                            <h4 class="fw-black text-dark mt-1 mb-0" id="kpi-profit">0 ج</h4>
                        </div>
                    </div>
                </div>

                <!-- Analysis Tables (Materials, Workers, Suppliers) -->
                <div class="row g-4">
                    <!-- Materials Breakdown -->
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                            <h5 class="fw-black mb-3"><i class="fa fa-boxes-stacked text-primary me-2"></i>ملخص استهلاك الخامات في هذا المشروع</h5>
                            <div class="table-responsive limit-5-rows">
                                <table class="table table-sm table-bordered text-center align-middle" id="table-project-materials">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">اسم الخامة</th>
                                            <th>إجمالي الكمية</th>
                                            <th>متوسط السعر</th>
                                            <th>إجمالي التكلفة</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Technicians Breakdown -->
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100">
                            <h5 class="fw-black mb-3"><i class="fa fa-users-gear text-warning me-2"></i>العمالة والصنايعية في المشروع</h5>
                            <div class="table-responsive limit-5-rows">
                                <table class="table table-sm table-bordered text-center align-middle" id="table-project-techs">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">اسم الفني</th>
                                            <th>عدد الحركات</th>
                                            <th>إجمالي أتعابه</th>
                                            <th>المسدد</th>
                                            <th>المتبقي له (دين)</th>
                                            <th>كشف حساب</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Top Suppliers for this Project -->
                    <div class="col-md-12">
                        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                            <h5 class="fw-black mb-3"><i class="fa fa-truck-field text-secondary me-2"></i>أكثر الموردين تعاملاً في هذا المشروع</h5>
                            <div class="table-responsive limit-5-rows">
                                <table class="table table-sm table-bordered text-center align-middle" id="table-project-suppliers">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">اسم المورد</th>
                                            <th>عدد التوريدات</th>
                                            <th>إجمالي قيمة التوريدات</th>
                                            <th>المسدد</th>
                                            <th>المتبقي له آجل</th>
                                            <th>كشف حساب</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. Project Stages / Items Table -->
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-black mb-0"><i class="fa fa-layer-group text-primary me-2"></i>بنود ومراحل هذا المشروع (نسب وتكاليف البنود)</h5>
                        <button class="btn btn-sm btn-outline-success rounded-pill fw-bold" onclick="showQuickAddItem()">
                            <i class="fa fa-plus me-1"></i>+ إضافة بند جديد
                        </button>
                    </div>


                    <div class="table-responsive limit-5-rows">
                        <table class="table table-sm table-bordered text-center align-middle mb-0" id="table-project-items">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th class="text-start">اسم البند / المرحلة</th>
                                    <th>عدد الحركات</th>
                                    <th>إجمالي التكلفة (عليك)</th>
                                    <th>إجمالي البيع (للعميل)</th>
                                    <th>صافي ربح البند</th>
                                    <th style="width: 150px;">كشف حساب البند</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Project Full Operations & Invoices Log -->
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-black mb-0"><i class="fa fa-receipt text-warning me-2"></i>سجل العمليات وفواتير الصرف للمشروع</h5>
                            <small class="text-muted">كافة فواتير الخامات ومصنعيات الفنيين والمصروفات المسجلة لهذا المشروع</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="input-search-project-ops" class="form-control form-control-sm rounded-pill fw-bold" style="width: 220px;" placeholder="🔍 بحث في عمليات المشروع..." oninput="filterProjectOpsTable(this.value)">
                            <span class="badge bg-secondary fs-6" id="project-ops-badge">0 حركة</span>
                        </div>
                    </div>

                    <!-- Date & Range Filter Bar for Project Operations -->
                    <div class="date-filter-bar" id="filter-bar-project-ops">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="small fw-bold text-muted"><i class="fa fa-filter text-primary me-1"></i>فلتر التاريخ:</span>
                            <button type="button" class="btn btn-filter-preset active" onclick="setQuickDateFilter('project-ops', 'all', 'table-project-operations', 1)">الكل</button>
                            <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('project-ops', 'today', 'table-project-operations', 1)">اليوم</button>
                            <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('project-ops', 'week', 'table-project-operations', 1)">هذا الأسبوع</button>
                            <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('project-ops', 'month', 'table-project-operations', 1)">هذا الشهر</button>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="width: 145px;">
                                <span class="input-group-text bg-light text-muted small">من</span>
                                <input type="date" id="df-from-project-ops" class="form-control form-control-sm" onchange="runCustomDateFilter('project-ops', 'table-project-operations', 1)">
                            </div>
                            <div class="input-group input-group-sm" style="width: 145px;">
                                <span class="input-group-text bg-light text-muted small">إلى</span>
                                <input type="date" id="df-to-project-ops" class="form-control form-control-sm" onchange="runCustomDateFilter('project-ops', 'table-project-operations', 1)">
                            </div>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2" onclick="clearDateFilter('project-ops', 'table-project-operations', 1)" title="إعادة ضبط">
                                <i class="fa fa-rotate-left text-muted"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive limit-5-rows">
                        <table class="table table-sm table-bordered table-hover text-center align-middle mb-0" id="table-project-operations">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 45px;">#</th>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th class="text-start">البيان / اسم الخامة</th>
                                    <th>البند التابع له</th>
                                    <th>الطرف المستفيد</th>
                                    <th>الكمية</th>
                                    <th>تكلفة الشراء</th>
                                    <th>سعر البيع</th>
                                    <th>طريقة السداد</th>
                                    <th>المسدد</th>
                                    <th>المتبقي (دين)</th>
                                    <th>الفاتورة</th>
                                    <th style="width: 50px;">حذف</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Empty State for Project 360 -->
            <div id="project-360-empty" class="text-center py-5 bg-white rounded-4 shadow-sm border">
                <i class="fa fa-hand-pointer text-muted fs-1 mb-3"></i>
                <h5 class="text-muted fw-bold">اختر مشروعاً من القائمة أعلاه لعرض لوحة المعلومات والتحليلات بالكامل</h5>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 3: المقايسة (حاسبة المساحات والدهانات والسيراميك)
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-survey">
            <!-- Top Hero Banner & Navigation Pills -->
            <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden bg-white">
                <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; font-size: 22px; background: #eff6ff; color: #2563eb;">
                            <i class="fa-solid fa-ruler-combined"></i>
                        </div>
                        <div>
                            <h4 class="fw-black text-dark m-0">حاسبة المقايسة</h4>
                            <p class="text-muted m-0 small fw-bold">حساب المساحات + تسعير تقريبي (مصنعية وخامات للمتر) مع حفظ السجلات وتعديلها</p>
                        </div>
                    </div>
                    <div class="btn-group bg-white rounded-pill p-1 shadow-sm border">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold survey-pill-btn active" id="survey-pill-calc" onclick="switchSurveySubtab('calc')">
                            <i class="fa fa-calculator me-1"></i> الحاسبة
                        </button>
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-4 fw-bold text-dark survey-pill-btn" id="survey-pill-log" onclick="switchSurveySubtab('log')">
                            <i class="fa fa-history me-1"></i> السجل (<span id="survey-log-count">{{ $surveys->count() }}</span>)
                        </button>
                    </div>
                </div>
            </div>

            <!-- SUBTAB 1: الحاسبة (Calculator) -->
            <div id="survey-view-calc">
                <!-- Top Inputs: Title & Ceiling Height -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fa fa-tag text-primary me-1"></i> عنوان الحسبة (اختياري)
                            </label>
                            <input type="text" id="survey-title" class="form-control rounded-3 bg-light border-0 py-2 fs-6 fw-bold" placeholder="مثال: مقايسه الفيلا / الشقة">
                            <div id="survey-edit-badge" class="small fw-bold mt-2" style="display:none; color:#1d4ed8;">
                                <i class="fa fa-pen-to-square me-1"></i>وضع التعديل: أي حفظ هيحدّث نفس السجل
                                <a href="javascript:void(0)" class="ms-2 text-danger text-decoration-none" onclick="resetSurveyCalc()">إلغاء التعديل</a>
                            </div>
                            <input type="hidden" id="survey-project-id" value="">
                            <input type="hidden" id="survey-edit-id" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="fa fa-ruler-vertical text-primary me-1"></i> ارتفاع السقف (متر)
                            </label>
                            <input type="number" id="survey-height" class="form-control rounded-3 bg-light border-0 py-2 text-center fs-5 fw-black text-primary" value="2.8" step="0.05" oninput="recalcSurveyAll()">
                        </div>
                    </div>
                </div>

                <!-- Rooms & Areas Table -->
                <div class="card border-0 rounded-4 shadow-sm bg-white mb-4">
                    <div class="card-header bg-white border-0 p-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="fw-black text-dark m-0 d-flex align-items-center gap-2">
                            <i class="fa fa-table-cells-large text-primary"></i> بيانات المساحات والغرف
                        </h5>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="addSurveyRoom()">
                            <i class="fa fa-plus me-1"></i> إضافة مساحة
                        </button>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-center mb-0" id="table-survey-rooms">
                                <thead class="table-light text-muted small fw-bold">
                                    <tr>
                                        <th style="min-width: 170px;" class="text-start">اسم المساحة</th>
                                        <th style="min-width: 160px;">النوع</th>
                                        <th style="min-width: 100px;">الطول (م)</th>
                                        <th style="min-width: 100px;">العرض (م)</th>
                                        <th style="min-width: 110px;">الفتحات (م²)</th>
                                        <th style="min-width: 320px;" class="text-center">النتائج (م²)</th>
                                        <th style="width: 70px;">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody id="survey-rooms-body">
                                    <!-- Dynamic rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Total Results & Summary Cards -->
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
                    <h5 class="fw-black text-dark mb-3">النتائج الإجمالية للمقايسة</h5>
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 rounded-4 bg-light text-center border h-100">
                                <div class="text-muted fw-bold small mb-1">حوائط الغرف (دهانات)</div>
                                <div class="fs-3 fw-black text-warning" id="survey-tot-room-walls">0.00 <span class="fs-6 text-muted">م²</span></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 rounded-4 bg-light text-center border h-100">
                                <div class="text-muted fw-bold small mb-1">الارضيات = الاسقف</div>
                                <div class="fs-3 fw-black text-primary" id="survey-tot-floors">0.00 <span class="fs-6 text-muted">م²</span></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 rounded-4 bg-light text-center border h-100">
                                <div class="text-muted fw-bold small mb-1">حوائط (حمامات ومطابخ)</div>
                                <div class="fs-3 fw-black text-success" id="survey-tot-ceramic-walls">0.00 <span class="fs-6 text-muted">م²</span></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 rounded-4 bg-light text-center border h-100">
                                <div class="text-muted fw-bold small mb-1">إجمالي الخصومات</div>
                                <div class="fs-3 fw-black text-danger" id="survey-tot-deductions">0.00 <span class="fs-6 text-muted">م²</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ التسعير التقريبي: سعر المتر مصنعية + تكلفة المتر خامات ═══ -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h6 class="fw-black text-dark m-0"><i class="fa fa-calculator text-primary me-2"></i>التسعير التقريبي (بكام تقريباً؟)</h6>
                            <span class="small text-muted">اكتب سعر المتر مصنعية وتكلفة المتر خامات لكل بند — اللي تسيبه فاضي مش هيتحسب</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center mb-0" id="table-survey-pricing">
                                <thead class="table-light small fw-bold text-muted">
                                    <tr>
                                        <th class="text-start" style="min-width: 170px;">البند</th>
                                        <th style="min-width: 110px;">المساحة (م²)</th>
                                        <th style="min-width: 140px;">سعر المتر مصنعية</th>
                                        <th style="min-width: 140px;">تكلفة المتر خامات</th>
                                        <th style="min-width: 120px;">إجمالي المصنعية</th>
                                        <th style="min-width: 120px;">إجمالي الخامات</th>
                                        <th style="min-width: 130px;">الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([
                                        'room_walls'    => 'دهانات حوائط الغرف',
                                        'ceilings'      => 'دهانات الأسقف',
                                        'floors'        => 'أرضيات (سيراميك / بورسلين)',
                                        'ceramic_walls' => 'سيراميك حوائط الحمامات والمطابخ',
                                    ] as $pKey => $pLabel)
                                        <tr class="survey-price-row" data-key="{{ $pKey }}" data-label="{{ $pLabel }}">
                                            <td class="text-start fw-bold text-dark">{{ $pLabel }}</td>
                                            <td class="fw-bold font-monospace price-area" dir="ltr">0.00</td>
                                            <td><input type="number" class="form-control form-control-sm text-center fw-bold price-labor" placeholder="0.00" min="0" step="0.5" oninput="recalcSurveyPricing()"></td>
                                            <td><input type="number" class="form-control form-control-sm text-center fw-bold price-material" placeholder="0.00" min="0" step="0.5" oninput="recalcSurveyPricing()"></td>
                                            <td class="text-secondary fw-bold price-labor-total" dir="ltr">0.00</td>
                                            <td class="text-secondary fw-bold price-material-total" dir="ltr">0.00</td>
                                            <td class="fw-black text-dark price-row-total" dir="ltr">0.00</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border bg-light text-center">
                                    <div class="small text-muted fw-bold">إجمالي المصنعيات</div>
                                    <div class="fs-4 fw-black text-dark" id="survey-total-labor">0.00 <span class="fs-6 text-muted">ج</span></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border bg-light text-center">
                                    <div class="small text-muted fw-bold">إجمالي الخامات</div>
                                    <div class="fs-4 fw-black text-dark" id="survey-total-material">0.00 <span class="fs-6 text-muted">ج</span></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border text-center" style="background: #eff6ff; border-color: #bfdbfe !important;">
                                    <div class="small fw-bold" style="color: #1d4ed8;">السعر التقريبي للمقايسة</div>
                                    <div class="fs-3 fw-black" style="color: #1d4ed8;" id="survey-estimated-total">0.00 <span class="fs-6">ج</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 fw-black shadow-sm" onclick="saveSurveyToLog()">
                            <i class="fa fa-floppy-disk me-2"></i> حفظ الحسبة في السجل
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-bold" onclick="resetSurveyCalc()">
                            <i class="fa fa-rotate-right me-1"></i> تفريغ / حسبة جديدة
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-bold" onclick="printCurrentSurvey()">
                            <i class="fa fa-print me-1"></i> طباعة هذه المقايسة
                        </button>
                    </div>
                </div>
            </div>

            <!-- SUBTAB 2: السجل (Saved Calculations Log) -->
            <div id="survey-view-log" style="display: none;">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="fw-black text-dark m-0"><i class="fa fa-history text-primary me-2"></i>سجل الحسابات السابقة</h5>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="input-search-survey-log" class="form-control form-control-sm rounded-pill fw-bold" style="width: 220px;" placeholder="🔍 بحث في المقايسات..." oninput="filterSurveyLogTable(this.value)">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="switchSurveySubtab('calc')">
                                <i class="fa fa-plus me-1"></i> مقايسة جديدة
                            </button>
                        </div>
                    </div>

                    <!-- Date & Range Filter Bar for Survey Log -->
                    <div class="date-filter-bar mb-3" id="filter-bar-survey-log">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="small fw-bold text-muted"><i class="fa fa-filter text-primary me-1"></i>فلتر التاريخ:</span>
                            <button type="button" class="btn btn-filter-preset active" onclick="setQuickDateFilter('survey-log', 'all', 'table-survey-log', 6)">الكل</button>
                            <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('survey-log', 'today', 'table-survey-log', 6)">اليوم</button>
                            <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('survey-log', 'week', 'table-survey-log', 6)">هذا الأسبوع</button>
                            <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('survey-log', 'month', 'table-survey-log', 6)">هذا الشهر</button>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <span class="input-group-text bg-light text-muted small">من</span>
                                <input type="date" id="df-from-survey-log" class="form-control form-control-sm" onchange="runCustomDateFilter('survey-log', 'table-survey-log', 6)">
                            </div>
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <span class="input-group-text bg-light text-muted small">إلى</span>
                                <input type="date" id="df-to-survey-log" class="form-control form-control-sm" onchange="runCustomDateFilter('survey-log', 'table-survey-log', 6)">
                            </div>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2" onclick="clearDateFilter('survey-log', 'table-survey-log', 6)" title="إعادة ضبط">
                                <i class="fa fa-rotate-left text-muted"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive limit-5-rows">
                        <table class="table table-hover align-middle text-center mb-0" id="table-survey-log">
                            <thead class="table-light text-muted small fw-bold">
                                <tr>
                                    <th class="text-start" style="min-width: 220px;">العنوان</th>
                                    <th style="min-width: 120px;">حوائط الغرف</th>
                                    <th style="min-width: 130px;">الارضيات = الاسقف</th>
                                    <th style="min-width: 130px;">حوائط سيراميك</th>
                                    <th style="min-width: 110px;">إجمالي الخصم</th>
                                    <th style="min-width: 130px;">السعر التقريبي</th>
                                    <th style="min-width: 140px;">تاريخ الحفظ</th>
                                    <th style="min-width: 160px;">إجراء</th>
                                </tr>
                            </thead>
                            <tbody id="survey-log-body">
                                @forelse($surveys as $s)
                                    <tr id="survey-row-{{ $s->id }}">
                                        <td class="text-start fw-bold text-dark fs-6">
                                            {{ $s->title }}
                                        </td>
                                        <td class="fw-bold text-warning-emphasis" dir="ltr">{{ number_format($s->total_room_walls, 2) }} م²</td>
                                        <td class="fw-bold text-primary" dir="ltr">{{ number_format($s->total_floors, 2) }} م²</td>
                                        <td class="fw-bold text-success" dir="ltr">{{ number_format($s->total_ceramic_walls, 2) }} م²</td>
                                        <td class="text-muted" dir="ltr">{{ number_format($s->total_deductions, 2) }} م²</td>
                                        <td class="fw-black text-dark">{{ ($s->estimated_total ?? 0) > 0 ? number_format($s->estimated_total, 2) . ' ج' : '—' }}</td>
                                        <td class="small text-muted">{{ date('Y-m-d H:i', strtotime($s->created_at)) }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 fw-bold" onclick="loadSurveyInCalc({{ $s->id }})" title="استرجاع وتعديل في الحاسبة">
                                                    <i class="fa fa-pen-to-square"></i> تعديل / عرض
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2" onclick="printSavedSurvey({{ $s->id }})" title="طباعة المقايسة">
                                                    <i class="fa fa-print"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger rounded-pill px-2" onclick="deleteSurvey({{ $s->id }})" title="حذف">
                                                    حذف
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="survey-empty-log-row">
                                        <td colspan="8" class="py-4 text-center text-muted">
                                            <i class="fa fa-inbox fs-3 d-block mb-2 opacity-50"></i>
                                            لا توجد مقايسات مسجلة في السجل حتى الآن. ابدأ بإدخال مساحات جديدة في الحاسبة واحفظها!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 4: تقدير تكلفة المشروع (التقدير التناسبي الذكي للمتر المسطح)
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-estimation">
            <!-- Top Bar (زر الطباعة فقط) -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-black text-dark m-0">
                    <i class="fa-solid fa-calculator text-primary me-2"></i>تقدير تكلفة المشروع
                </h5>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="printEstimationReport()">
                    <i class="fa fa-print me-1"></i> طباعة كشف التقدير
                </button>
            </div>

            <!-- Inputs & Benchmark Card -->
            <div class="card border-0 rounded-4 shadow-sm bg-white p-4 mb-4">
                <div class="row g-3 align-items-end">
                    <!-- New Project Area -->
                    <div class="col-md-3">
                        <label class="form-label fw-black text-dark fs-6 mb-1">
                            <i class="fa fa-maximize text-primary me-1"></i> مساحة المشروع الجديد (م²) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="est-new-area" class="form-control form-control-lg rounded-start-3 bg-light border-0 text-center fw-black fs-4 text-primary" value="150" step="any" min="1" oninput="calculateEstimationLive()">
                            <span class="input-group-text bg-primary text-white fw-bold rounded-end-3">م²</span>
                        </div>
                    </div>

                    <!-- Benchmark Project Selector -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="fa fa-landmark text-primary me-1"></i> المشروع الاسترشادي / المرجعي
                        </label>
                        <select id="est-benchmark-project" class="form-select form-select-lg rounded-3 bg-light border-0 fw-bold fs-6" onchange="onBenchmarkProjectChange()">
                            <option value="average" selected>⭐ متوسط المشاريع المسجل لها مساحة</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" data-area="{{ $p->area_sqm > 0 ? $p->area_sqm : '' }}" data-sup="{{ $p->supervision_pct ?: 15 }}">
                                    {{ $p->name }} (العميل: {{ $p->client_name }} | المساحة: {{ $p->area_sqm > 0 ? number_format($p->area_sqm, 0) . ' م²' : 'غير مسجلة' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Benchmark Area (read from DB) -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted small mb-1">
                            مساحة المرجع (م²) <span class="badge bg-success-subtle text-success border" id="est-area-src-badge" title="مقروءة من قاعدة البيانات"><i class="fa fa-database"></i></span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="est-benchmark-area" class="form-control rounded-start-3 bg-light border-0 text-center fw-bold text-secondary" value="" step="any" readonly oninput="calculateEstimationLive()">
                            <span class="input-group-text bg-light border-0 text-muted small rounded-end-3">م²</span>
                        </div>
                        <div id="est-area-missing" class="small mt-1" style="display:none;">
                            <span class="text-danger fw-bold"><i class="fa fa-triangle-exclamation me-1"></i>المساحة غير مسجلة للمشروع</span>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill py-0 px-2 ms-1" onclick="saveBenchmarkAreaToProject()">
                                <i class="fa fa-floppy-disk me-1"></i>حفظ للمشروع
                            </button>
                        </div>
                    </div>

                    <!-- Market Inflation Factor (%) -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="fa fa-arrow-trend-up text-danger me-1"></i> معامل تغير الأسعار / التضخم
                        </label>
                        <div class="input-group">
                            <input type="number" id="est-inflation-pct" class="form-control rounded-start-3 bg-light border-0 text-center fw-bold" value="0" step="any" placeholder="0" oninput="calculateEstimationLive()">
                            <span class="input-group-text bg-light border-0 fw-bold rounded-end-3">%</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2 pt-3 border-top">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted mb-1">اسم المشروع المقترح (اختياري)</label>
                        <input type="text" id="est-project-name" class="form-control rounded-3 bg-light border-0 py-2" placeholder="مثال: تشطيب شقة العاصمة الإدارية">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted mb-1">اسم العميل المقترح (اختياري)</label>
                        <input type="text" id="est-client-name" class="form-control rounded-3 bg-light border-0 py-2" placeholder="اسم العميل...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted mb-1">نسبة الإشراف المقترحة للعميل (%)</label>
                        <div class="input-group">
                            <input type="number" id="est-supervision-pct" class="form-control rounded-start-3 bg-light border-0 text-center fw-bold text-primary" value="15" step="any" oninput="calculateEstimationLive()">
                            <span class="input-group-text bg-light border-0 fw-bold rounded-end-3">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Tiles (بطاقات النتائج الكبرى) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100 text-center border-top border-4 border-primary">
                        <div class="text-muted fw-bold small mb-1">إجمالي التكلفة التقديرية المتوقعة</div>
                        <div class="fs-2 fw-black text-primary" id="est-tot-cost">0.00 <span class="fs-6 text-muted">ج</span></div>
                        <div class="small text-muted mt-1" id="est-tot-cost-note">تكلفة الخامات والمصنعيات والمصاريف</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100 text-center border-top border-4 border-warning">
                        <div class="text-muted fw-bold small mb-1">متوسط تكلفة المتر المربع (م²)</div>
                        <div class="fs-2 fw-black text-warning-emphasis" id="est-sqm-rate">0.00 <span class="fs-6 text-muted">ج/م²</span></div>
                        <div class="small text-muted mt-1" id="est-sqm-note">معدل التكلفة لكل متر مسطح</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100 text-center border-top border-4 border-success">
                        <div class="text-muted fw-bold small mb-1">سعر البيع المقترح للعميل (+ الإشراف)</div>
                        <div class="fs-2 fw-black text-success" id="est-selling-price">0.00 <span class="fs-6 text-muted">ج</span></div>
                        <div class="small text-muted mt-1" id="est-selling-note">شامل أتعاب الإشراف الهندسية</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white h-100 text-center border-top border-4 border-dark">
                        <div class="text-muted fw-bold small mb-1">صافي أرباح الشركة المتوقعة</div>
                        <div class="fs-2 fw-black text-dark" id="est-net-profit">0.00 <span class="fs-6 text-muted">ج</span></div>
                        <div class="small text-muted mt-1" id="est-profit-note">هامش ربح المكتب الهندسي</div>
                    </div>
                </div>
            </div>

            <!-- Component Share Cards (خامات، مصنعيات، مصروفات) -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-info h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark"><i class="fa fa-boxes-stacked text-info me-1"></i> خامات ومواد التوريد</span>
                            <span class="badge bg-info-subtle text-info-emphasis fw-bold" id="est-mat-pct">0%</span>
                        </div>
                        <div class="fs-3 fw-black text-dark" id="est-mat-cost">0.00 <span class="fs-6 text-muted">ج</span></div>
                        <div class="small text-muted d-flex justify-content-between mt-2 pt-2 border-top">
                            <span>معدل المتر المسطح:</span>
                            <strong id="est-mat-rate">0.00 ج/م²</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-warning h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark"><i class="fa fa-hammer text-warning me-1"></i> مصنعيات وأجور فنيين</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold" id="est-labor-pct">0%</span>
                        </div>
                        <div class="fs-3 fw-black text-dark" id="est-labor-cost">0.00 <span class="fs-6 text-muted">ج</span></div>
                        <div class="small text-muted d-flex justify-content-between mt-2 pt-2 border-top">
                            <span>معدل المتر المسطح:</span>
                            <strong id="est-labor-rate">0.00 ج/م²</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-secondary h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark"><i class="fa fa-coins text-secondary me-1"></i> مصروفات ونثريات وإداريات</span>
                            <span class="badge bg-secondary-subtle text-secondary fw-bold" id="est-overhead-pct">0%</span>
                        </div>
                        <div class="fs-3 fw-black text-dark" id="est-overhead-cost">0.00 <span class="fs-6 text-muted">ج</span></div>
                        <div class="small text-muted d-flex justify-content-between mt-2 pt-2 border-top">
                            <span>معدل المتر المسطح:</span>
                            <strong id="est-overhead-rate">0.00 ج/م²</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Breakdown Tables: Work Packages & Expected Materials -->
            <div class="row g-4 mb-4">
                <!-- 1. Work Packages Table -->
                <div class="col-lg-6">
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-black text-dark m-0">
                                <i class="fa fa-layer-group text-primary me-2"></i>توزيع بنود الأعمال المتوقعة
                            </h5>
                            <span class="badge bg-light text-dark border" id="est-items-count">0 بند</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-center mb-0" id="table-est-items">
                                <thead class="table-light text-muted small fw-bold">
                                    <tr>
                                        <th style="width: 40px;">م</th>
                                        <th class="text-start">بند الأعمال</th>
                                        <th>معدل المتر</th>
                                        <th>التكلفة المتوقعة</th>
                                        <th>النسبة</th>
                                    </tr>
                                </thead>
                                <tbody id="est-items-body">
                                    <tr><td colspan="5" class="py-3 text-muted">جارٍ تحميل بيانات البنود...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2. Expected Materials & Quantities Table (User requested feature!) -->
                <div class="col-lg-6">
                    <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-black text-dark m-0">
                                <i class="fa fa-boxes-stacked text-success me-2"></i>جدول الخامات والمواد المتوقعة
                            </h5>
                            <span class="badge bg-success-subtle text-success-emphasis fw-bold" id="est-materials-count">0 صنف</span>
                        </div>
                        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                            <table class="table table-hover align-middle text-center mb-0" id="table-est-materials">
                                <thead class="table-light text-muted small fw-bold sticky-top">
                                    <tr>
                                        <th style="width: 35px;">م</th>
                                        <th class="text-start">اسم الخامة</th>
                                        <th>الوحدة</th>
                                        <th>الكمية المتوقعة</th>
                                        <th>متوسط السعر</th>
                                        <th>إجمالي التكلفة</th>
                                    </tr>
                                </thead>
                                <tbody id="est-materials-body">
                                    <tr><td colspan="6" class="py-3 text-muted">جارٍ تحميل وتوقع الخامات المطلوبة...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 4: الفواتير والمستخلصات والأقساط
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-invoices">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-title mb-0">سجل فواتير المشتريات ومستخلصات العملاء</h5>
                    <!-- Sub Tabs Navigation -->
                    <ul class="nav nav-pills flex-wrap gap-1" id="invoicesSubTabs">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill fw-bold py-1 px-3" id="btn-subtab-materials" onclick="switchInvoicesSubTab('materials', this)">
                                <i class="fa fa-boxes-stacked me-1 text-primary"></i>فواتير خامات ومشتريات الموردين
                                <span class="badge bg-primary ms-1">{{ $materialInvoices->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill fw-bold py-1 px-3" onclick="switchInvoicesSubTab('clients', this)">
                                <i class="fa fa-receipt me-1 text-success"></i>سندات ومستخلصات وأقساط العملاء
                                <span class="badge bg-success ms-1">{{ $invoices->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill fw-bold py-1 px-3" onclick="switchInvoicesSubTab('cancelled', this)">
                                <i class="fa fa-ban me-1 text-danger"></i>الفواتير الملغاة
                                <span class="badge bg-danger ms-1">{{ $cancelledInvoices->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Live Search & Autocomplete Bar for Invoices -->
                <div class="row g-2 mb-3 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted"><i class="fa fa-search"></i></span>
                            <input type="text" id="input-search-invoices" class="form-control border-start-0 rounded-end-pill fw-bold" placeholder="🔍 ابحث في الفواتير (برقم الفاتورة، المشروع، العميل، المورد، التاريخ)..." oninput="filterInvoicesTable(this.value)">
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold" id="invoices-search-count">
                            <i class="fa fa-layer-group text-primary me-1"></i>إجمالي الفواتير: {{ $materialInvoices->count() + $invoices->count() }}
                        </span>
                    </div>
                </div>

                <!-- Date & Range Filter Bar for Invoices -->
                <div class="date-filter-bar" id="filter-bar-invoices">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small fw-bold text-muted"><i class="fa fa-filter text-primary me-1"></i>فلتر التاريخ:</span>
                        <button type="button" class="btn btn-filter-preset active" onclick="setQuickDateFilter('invoices', 'all', 'table-invoices-materials', 11); setQuickDateFilter('invoices', 'all', 'table-invoices-payments', 5);">الكل</button>
                        <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('invoices', 'today', 'table-invoices-materials', 11); setQuickDateFilter('invoices', 'today', 'table-invoices-payments', 5);">اليوم</button>
                        <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('invoices', 'week', 'table-invoices-materials', 11); setQuickDateFilter('invoices', 'week', 'table-invoices-payments', 5);">هذا الأسبوع</button>
                        <button type="button" class="btn btn-filter-preset" onclick="setQuickDateFilter('invoices', 'month', 'table-invoices-materials', 11); setQuickDateFilter('invoices', 'month', 'table-invoices-payments', 5);">هذا الشهر</button>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group input-group-sm" style="width: 145px;">
                            <span class="input-group-text bg-light text-muted small">من</span>
                            <input type="date" id="df-from-invoices" class="form-control form-control-sm" onchange="runCustomDateFilter('invoices', 'table-invoices-materials', 11); runCustomDateFilter('invoices', 'table-invoices-payments', 5);">
                        </div>
                        <div class="input-group input-group-sm" style="width: 145px;">
                            <span class="input-group-text bg-light text-muted small">إلى</span>
                            <input type="date" id="df-to-invoices" class="form-control form-control-sm" onchange="runCustomDateFilter('invoices', 'table-invoices-materials', 11); runCustomDateFilter('invoices', 'table-invoices-payments', 5);">
                        </div>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2" onclick="clearDateFilter('invoices', 'table-invoices-materials', 11); clearDateFilter('invoices', 'table-invoices-payments', 5);" title="إعادة ضبط">
                            <i class="fa fa-rotate-left text-muted"></i>
                        </button>
                    </div>
                </div>

                <!-- Sub-Section 1: فواتير خامات ومشتريات الموردين -->
                <div id="subtab-invoices-materials">
                    <div class="table-responsive border rounded-3 bg-white">
                        <table class="table table-sm table-bordered table-striped text-center align-middle mb-0" id="table-invoices-materials" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35px;">#</th>
                                    <th class="text-start">رقم / اسم الفاتورة</th>
                                    <th>المشروع</th>
                                    <th>العميل</th>
                                    <th>المورد</th>
                                    <th style="width: 75px;">الخامات</th>
                                    <th>إجمالي الشراء</th>
                                    <th>إجمالي البيع</th>
                                    <th style="width: 105px;">طريقة السداد</th>
                                    <th>المسدد</th>
                                    <th>المتبقي (دين)</th>
                                    <th style="width: 85px;">التاريخ</th>
                                    <th style="width: 155px; min-width: 155px; white-space: nowrap;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($materialInvoices as $idx => $minv)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="text-start fw-bold text-dark font-monospace">{{ $minv->invoice_ref }}</td>
                                        <td class="fw-bold">
                                            <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $minv->project_id }})" class="text-primary text-decoration-none" title="عرض في سجل المشاريع">
                                                <i class="fa fa-folder-open me-1"></i>{{ $minv->project_name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $minv->project_id }})" class="text-dark text-decoration-none" title="عرض في سجل المشاريع">
                                                {{ $minv->client_name }}
                                            </a>
                                        </td>
                                        <td class="text-primary fw-bold">{{ $minv->supplier_name }}</td>
                                        <td><span class="badge bg-secondary">{{ $minv->items_count }} صنف</span></td>
                                        <td class="fw-bold text-danger">{{ number_format($minv->total_cost, 2) }} ج</td>
                                        <td class="fw-bold text-primary">{{ number_format($minv->total_selling, 2) }} ج</td>
                                        <td>
                                            @if($minv->payment_method == 'later')
                                                <span class="badge bg-danger">آجل بالكامل (دين)</span>
                                            @elseif($minv->payment_method == 'partial')
                                                <span class="badge bg-warning text-dark">سداد جزئي</span>
                                            @else
                                                <span class="badge bg-success">نقداً بالكامل</span>
                                            @endif
                                        </td>
                                        <td class="text-success fw-bold">{{ number_format($minv->total_paid, 2) }} ج</td>
                                        <td class="fw-black {{ $minv->total_debt > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ number_format($minv->total_debt, 2) }} ج
                                        </td>
                                        <td>{{ substr($minv->created_at, 0, 10) }}</td>
                                        <td style="white-space: nowrap; width: 155px;">
                                            <div class="d-inline-flex align-items-center justify-content-center gap-1 flex-nowrap text-nowrap">
                                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1" onclick="viewInvoiceDetails('{{ addslashes($minv->invoice_ref) }}', {{ $minv->project_id }})" title="عرض البنود">
                                                    <i class="fa fa-eye me-1"></i>البنود
                                                </button>
                                                <a href="{{ route('contracting.material_invoice.print', urlencode($minv->invoice_ref)) }}?project_id={{ $minv->project_id }}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1" title="طباعة الفاتورة">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-2 py-1" onclick="openEditMaterialInvoice('{{ addslashes($minv->invoice_ref) }}', {{ $minv->project_id }})" title="تعديل الفاتورة">
                                                    <i class="fa fa-pen"></i>
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1" onclick="deleteMaterialInvoice('{{ addslashes($minv->invoice_ref) }}', {{ $minv->project_id }})" title="حذف الفاتورة">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="13" class="py-4 text-muted">لا توجد فواتير خامات مسجلة حتى الآن</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Bar for 7 Invoices per page -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2" id="pagination-wrapper-materials">
                        <div class="small text-muted fw-bold" id="page-info-materials">عرض 1 - 7 من إجمالي {{ $materialInvoices->count() }} فاتورة</div>
                        <ul class="pagination pagination-sm m-0" id="pagination-nav-materials"></ul>
                    </div>
                </div>

                <!-- Sub-Section 2: سندات ومستخلصات وأقساط العملاء -->
                <div id="subtab-invoices-clients" style="display: none;">
                    <div class="table-responsive border rounded-3 bg-white">
                        <table class="table table-sm table-bordered table-striped text-center align-middle mb-0" id="table-invoices-payments" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الإشعار / المستخلص</th>
                                    <th>المشروع</th>
                                    <th>العميل</th>
                                    <th>نوع الحركة</th>
                                    <th>المبلغ المسدد</th>
                                    <th>التاريخ</th>
                                    <th>ملاحظات</th>
                                    <th style="width: 100px; white-space: nowrap;">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $inv)
                                    <tr>
                                        <td class="fw-bold text-dark font-monospace">{{ $inv->invoice_number }}</td>
                                        <td>
                                            <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $inv->project_id }})" class="text-primary text-decoration-none fw-bold" title="عرض في سجل المشاريع">
                                                <i class="fa fa-folder-open me-1"></i>{{ $inv->project_name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $inv->project_id }})" class="text-dark text-decoration-none" title="عرض في سجل المشاريع">
                                                {{ $inv->client_name }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($inv->installment_id)
                                                <span class="badge bg-info text-dark">عقد تقسيط #{{ $inv->installment_id }}</span>
                                            @else
                                                <span class="badge bg-success">دفعة نقدية</span>
                                            @endif
                                        </td>
                                        <td class="fw-black text-success">{{ number_format($inv->paid_amount, 2) }} ج</td>
                                        <td>{{ substr($inv->created_at, 0, 10) }}</td>
                                        <td class="small text-muted">{{ $inv->notes ?: '-' }}</td>
                                        <td style="white-space: nowrap;">
                                            <a href="{{ route('contracting.invoice.print', $inv->id) }}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill fw-bold px-3 py-1" title="طباعة المستخلص / السند الرسمي">
                                                <i class="fa fa-print me-1"></i>طباعة
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="py-4 text-muted">لا توجد دفعات أو مستخلصات عملاء مسجلة حتى الآن</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Bar for Client Invoices -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2" id="pagination-wrapper-clients">
                        <div class="small text-muted fw-bold" id="page-info-clients">عرض 1 - 7 من إجمالي {{ $invoices->count() }} سند</div>
                        <ul class="pagination pagination-sm m-0" id="pagination-nav-clients"></ul>
                    </div>
                </div>

                <!-- Sub-Section 3: الفواتير الملغاة -->
                <div id="subtab-invoices-cancelled" style="display: none;">
                    <div class="table-responsive border rounded-3 bg-white">
                        <table class="table table-sm table-bordered table-striped text-center align-middle mb-0" id="table-invoices-cancelled" style="font-size: 12px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35px;">#</th>
                                    <th class="text-start">رقم / اسم الفاتورة الملغاة</th>
                                    <th>المشروع</th>
                                    <th>العميل</th>
                                    <th>المورد</th>
                                    <th>الأصناف</th>
                                    <th>إجمالي الشراء (الملغى)</th>
                                    <th>إجمالي البيع (الملغى)</th>
                                    <th>تاريخ الفاتورة</th>
                                    <th>تاريخ الإلغاء</th>
                                    <th>بواسطة</th>
                                    <th style="width: 110px; white-space: nowrap;">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cancelledInvoices as $cIdx => $cinv)
                                    <tr>
                                        <td>{{ $cIdx + 1 }}</td>
                                        <td class="text-start fw-bold text-danger font-monospace">
                                            <i class="fa fa-ban me-1"></i>{{ $cinv->invoice_ref }}
                                        </td>
                                        <td class="fw-bold">{{ $cinv->project_name ?: '-' }}</td>
                                        <td>{{ $cinv->client_name ?: '-' }}</td>
                                        <td class="text-primary fw-bold">{{ $cinv->supplier_name ?: '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $cinv->items_count }} صنف</span></td>
                                        <td class="fw-bold text-danger text-decoration-line-through">{{ number_format($cinv->total_cost, 2) }} ج</td>
                                        <td class="fw-bold text-muted text-decoration-line-through">{{ number_format($cinv->total_selling, 2) }} ج</td>
                                        <td>{{ $cinv->original_date ?: '-' }}</td>
                                        <td class="small text-danger fw-bold">{{ $cinv->cancelled_at ? substr($cinv->cancelled_at, 0, 16) : '-' }}</td>
                                        <td class="small text-muted">{{ $cinv->cancelled_by ?: 'الإدارة' }}</td>
                                        <td style="white-space: nowrap;">
                                            @if(!empty($cinv->items_json))
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 fw-bold" onclick="viewCancelledInvoiceDetails({{ $cIdx }})" title="عرض بنود الفاتورة الملغاة">
                                                    <i class="fa fa-eye me-1"></i>عرض البنود
                                                </button>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">ملغاة بالكامل</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="12" class="py-4 text-muted">لا توجد فواتير ملغاة مسجلة حتى الآن</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Bar for Cancelled Invoices -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2" id="pagination-wrapper-cancelled">
                        <div class="small text-muted fw-bold" id="page-info-cancelled">عرض 1 - 7 من إجمالي {{ $cancelledInvoices->count() }} فاتورة ملغاة</div>
                        <ul class="pagination pagination-sm m-0" id="pagination-nav-cancelled"></ul>
                    </div>
                </div>

            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 6: متابعة أسعار الخامات (Price Tracker)
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-prices">
            <!-- Header bar matching user's design -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm" style="width: 44px; height: 44px; font-size: 22px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h4 class="fw-black text-dark m-0">متابعة أسعار الخامات</h4>
                </div>
                <div style="min-width: 300px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted pe-1"><i class="fa fa-search"></i></span>
                        <input type="text" id="filter-materials-input" class="form-control border-start-0 rounded-end-pill py-2 bg-white" placeholder="بحث باسم الصنف أو الخامة..." oninput="filterMaterialsTable(this.value)">
                    </div>
                </div>
            </div>

            <!-- Material Prices Table (Matching reference screenshot) -->
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3">
                <div class="table-responsive limit-5-rows">
                    <table class="table table-hover align-middle mb-0" id="table-material-prices">
                        <thead class="text-muted small fw-bold" style="border-bottom: 2px solid #f1f5f9;">
                            <tr>
                                <th class="text-start py-3" style="min-width: 220px;">الصنف</th>
                                <th class="text-center py-3" style="min-width: 120px;">عدد مرات الشراء</th>
                                <th class="text-center py-3" style="min-width: 110px;">أقل سعر</th>
                                <th class="text-center py-3" style="min-width: 110px;">متوسط السعر</th>
                                <th class="text-center py-3" style="min-width: 110px;">أعلى سعر</th>
                                <th class="text-center py-3" style="min-width: 110px;">آخر سعر</th>
                                <th class="text-center py-3" style="min-width: 110px;">نسبة التغير</th>
                                <th class="text-center py-3" style="min-width: 130px;">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody id="materials-prices-tbody">
                            @forelse($materialPricesSummary as $mat)
                                <tr class="mat-price-row" data-name="{{ strtolower($mat->name) }}">
                                    <td class="text-start fw-bold text-dark fs-6 py-3">
                                        {{ $mat->name }}
                                    </td>
                                    <td class="text-center fw-bold text-secondary">{{ $mat->purchases_count }}</td>
                                    <td class="text-center fw-bold" dir="ltr">{{ number_format($mat->min_price, 2) }}</td>
                                    <td class="text-center fw-bold" dir="ltr">{{ number_format($mat->avg_price, 2) }}</td>
                                    <td class="text-center fw-bold" dir="ltr">{{ number_format($mat->max_price, 2) }}</td>
                                    <td class="text-center fw-black text-dark" dir="ltr">{{ number_format($mat->latest_price, 2) }}</td>
                                    <td class="text-center">
                                        @if(is_null($mat->change_pct) || $mat->purchases_count <= 1)
                                            <span class="text-muted">—</span>
                                        @elseif($mat->change_pct > 0)
                                            <span class="fw-black text-danger" dir="ltr"><i class="fa fa-caret-up me-1"></i>{{ $mat->change_pct }}%</span>
                                        @elseif($mat->change_pct < 0)
                                            <span class="fw-black text-success" dir="ltr"><i class="fa fa-caret-down me-1"></i>{{ abs($mat->change_pct) }}%</span>
                                        @else
                                            <span class="text-muted fw-bold">0%</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-bold" onclick="showMaterialFullHistory('{{ addslashes($mat->name) }}')">
                                            السجل الكامل
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="empty-materials-row">
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa fa-boxes-stacked fs-2 d-block mb-2 opacity-50"></i>
                                        لا توجد خامات مسجلة في فواتير الشراء حتى الآن
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 7: مقارنة الموردين (Supplier Comparison Matrix)
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-suppliers">
            <!-- Search bar with orange accent matching user's reference image -->
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-4" style="border-top: 4px solid #f97316 !important;">
                <form id="form-supplier-search" onsubmit="event.preventDefault(); filterSupplierCompareCards();" class="d-flex align-items-center gap-2">
                    <input type="text" id="supplier-compare-search" class="form-control form-control-lg border-0 bg-light rounded-3 py-2 px-3 fw-bold fs-6" placeholder="ابحث عن خامة معينة..." oninput="filterSupplierCompareCards()">
                    <button type="submit" class="btn btn-lg rounded-3 px-4 fw-black text-white shadow-sm" style="background-color: #f97316; min-width: 110px;">
                        بحث
                    </button>
                </form>
            </div>

            <!-- Cards Grid (2 Columns, matching reference screenshot) -->
            <div class="row row-cols-1 row-cols-md-2 g-4" id="supplier-cards-grid">
                @forelse($supplierComparisonCards as $card)
                    <div class="col supplier-comp-col" data-name="{{ strtolower($card->material_name) }}" data-suppliers="{{ strtolower(collect($card->suppliers)->pluck('name')->join(' ')) }}">
                        <div class="card border-0 rounded-4 shadow-sm bg-white p-4 h-100">
                            <!-- Card Header: Orange dot + Material Title -->
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                                <h6 class="fw-black m-0 d-flex align-items-center gap-2" style="color: #ea580c; font-size: 15px;">
                                    <span style="font-size: 12px;">●</span>
                                    <span>{{ $card->material_name }}</span>
                                </h6>
                            </div>

                            <!-- Suppliers list in this material card -->
                            <div class="d-flex flex-column gap-2">
                                @foreach($card->suppliers as $sIdx => $sup)
                                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-secondary fw-bold fs-6">{{ $sup['name'] }}</span>
                                            @if(!empty($sup['is_cheapest']))
                                                <span class="badge" style="background-color: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                                    الأرخص
                                                </span>
                                            @elseif(!empty($sup['is_highest']))
                                                <span class="badge" style="background-color: #fee2e2; color: #b91c1c; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                                    الأغلى
                                                </span>
                                            @endif
                                        </div>
                                        <div class="fw-bold text-dark fs-6" dir="ltr">
                                            <span>{{ number_format($sup['price'], 2) }}</span>
                                            <span class="text-muted small ms-1">ج.م / {{ $card->unit ?: 'وحدة' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12" id="empty-supplier-cards">
                        <div class="card border-0 rounded-4 shadow-sm bg-white p-5 text-center text-muted">
                            <i class="fa fa-scale-balanced fs-1 d-block mb-2 text-warning opacity-50"></i>
                            <h5 class="fw-bold">لا توجد خامات بموردين مسجلين حتى الآن</h5>
                            <p class="small text-muted mb-0">عند تسجيل فواتير خامات من موردين مختلفين، ستظهر مقارنات الأسعار والبطاقات هنا تلقائياً وتحدد لك المورد الأرخص دائماً.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Empty Search Results Placeholder -->
            <div id="no-search-results" class="card border-0 rounded-4 shadow-sm bg-white p-5 text-center text-muted mt-3" style="display: none;">
                <i class="fa fa-search fs-2 d-block mb-2 opacity-50"></i>
                <h5 class="fw-bold text-dark">لا توجد خامات تطابق بحثك</h5>
                <p class="small text-muted mb-0">جرب البحث بكلمة أخرى أو اسم مورد مختلف.</p>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 7: سجل المتعاملين (Directory)
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-directory">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="section-title mb-0">سجل المتعاملين (الموردين، الصنايعية، المسوقين، العملاء)</h5>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="input-search-directory" class="form-control form-control-sm rounded-pill fw-bold" style="width: 240px;" placeholder="🔍 بحث بالاسم أو الهاتف أو التخصص..." oninput="filterDirectorySearch(this.value)">
                    <button class="btn btn-primary rounded-pill fw-bold" onclick="openQuickAddPersonModal()">
                        <i class="fa fa-user-plus me-1"></i>إضافة متعامل جديد
                    </button>
                </div>
            </div>

            <!-- Sub Pills for Directory Types -->
            <ul class="nav nav-pills mb-3" id="directorySubTabs">
                <li class="nav-item"><button class="nav-link active" onclick="filterDirectory('all', this)">الكل ({{ $directory->count() }})</button></li>
                <li class="nav-item"><button class="nav-link" onclick="filterDirectory('supplier', this)">الموردين ({{ $suppliers->count() }})</button></li>
                <li class="nav-item"><button class="nav-link" onclick="filterDirectory('technician', this)">الفنيين والصنايعية ({{ $technicians->count() }})</button></li>
                <li class="nav-item"><button class="nav-link" onclick="filterDirectory('marketer', this)">المسوقين ({{ $marketers->count() }})</button></li>
                <li class="nav-item"><button class="nav-link" onclick="filterDirectory('client', this)">العملاء ({{ $clientsList->count() }})</button></li>
            </ul>

            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <div class="table-responsive limit-5-rows">
                    <table class="table table-bordered table-striped text-center align-middle" id="table-directory">
                        <thead class="table-light">
                            <tr>
                                <th>النوع</th>
                                <th class="text-start">الاسم</th>
                                <th>الهاتف</th>
                                <th>التخصص / البيان</th>
                                <th>الرصيد المتبقي (له أو عليه)</th>
                                <th>ملاحظات</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($directory as $person)
                                <tr data-type="{{ $person->type }}">
                                    <td>
                                        @if($person->type == 'supplier') <span class="badge bg-primary">مورد</span>
                                        @elseif($person->type == 'technician') <span class="badge bg-warning text-dark">فني / صنايعي</span>
                                        @elseif($person->type == 'marketer') <span class="badge bg-info text-dark">مسوق</span>
                                        @else <span class="badge bg-success">عميل</span> @endif
                                    </td>
                                    <td class="text-start fw-bold">{{ $person->name }}</td>
                                    <td>
                                        @if($person->phone)
                                            <a href="tel:{{ $person->phone }}" class="text-decoration-none fw-bold"><i class="fa fa-phone me-1"></i>{{ $person->phone }}</a>
                                        @else - @endif
                                    </td>
                                    <td>{{ $person->specialty ?: '-' }}</td>
                                    <td class="fw-bold {{ $person->balance_debt > 0 ? ($person->type == 'client' ? 'text-primary' : 'text-danger') : 'text-muted' }}">
                                        {{ number_format($person->balance_debt, 2) }} ج
                                        @if($person->type == 'client' && $person->balance_debt > 0)
                                            <small class="d-block text-muted" style="font-size: 0.72rem;">(مستحق عليه للشركة)</small>
                                        @elseif($person->balance_debt > 0)
                                            <small class="d-block text-muted" style="font-size: 0.72rem;">(دين له على الشركة)</small>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $person->notes ?: '-' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                            @if(in_array($person->type, ['supplier', 'technician']))
                                                <a href="{{ url('/contracting/person-statement') }}?type={{ $person->type }}&id={{ $person->id }}&name={{ urlencode($person->name) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-2 fw-bold" title="عرض وطباعة كشف الحساب">
                                                    <i class="fa fa-file-invoice me-1"></i>كشف حساب
                                                </a>
                                            @endif
                                            @if($person->balance_debt > 0)
                                                <span class="badge bg-light text-muted border px-2 py-1" title="لا يمكن الحذف لوجود رصيد أو معاملات مالية قائمة"><i class="fa fa-lock text-warning me-1"></i>محمي ماليًا</span>
                                            @else
                                                <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteDirectoryPerson({{ $person->id }})" title="حذف جهة الاتصال (رصيدها صفر)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-4 text-muted">لا توجد جهات اتصال مسجلة</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 8: التقارير والأرباح الشاملة
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-reports">
            <!-- ══════════════════════════════════════════════════════════════
                 1. شريط الفلاتر الزمنية للتقارير (الافتراضي 3 شهور)
            ══════════════════════════════════════════════════════════════ -->
            <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white border">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; font-size: 20px; background: #eff6ff; color: #2563eb;">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">لوحة التقارير والتحليل المالي للمقاولات</h5>
                            <p class="text-muted small mb-0">تحليل الإيرادات، التكاليف، أرباح المشاريع ومؤشرات الأداء المالي</p>
                        </div>
                    </div>

                    <!-- Date Filter Bar -->
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="btn-group btn-group-sm bg-light p-1 rounded-pill border">
                            <a href="{{ url('/contracting?period=3months#tab-reports') }}" class="btn rounded-pill px-3 fw-bold {{ $filterPreset == '3months' ? 'btn-dark text-white' : 'btn-light text-secondary' }}">
                                آخر 3 شهور (الافتراضي)
                            </a>
                            <a href="{{ url('/contracting?period=month#tab-reports') }}" class="btn rounded-pill px-3 fw-bold {{ $filterPreset == 'month' ? 'btn-dark text-white' : 'btn-light text-secondary' }}">
                                هذا الشهر
                            </a>
                            <a href="{{ url('/contracting?period=6months#tab-reports') }}" class="btn rounded-pill px-3 fw-bold {{ $filterPreset == '6months' ? 'btn-dark text-white' : 'btn-light text-secondary' }}">
                                آخر 6 شهور
                            </a>
                            <a href="{{ url('/contracting?period=year#tab-reports') }}" class="btn rounded-pill px-3 fw-bold {{ $filterPreset == 'year' ? 'btn-dark text-white' : 'btn-light text-secondary' }}">
                                هذا العام
                            </a>
                            <a href="{{ url('/contracting?period=all#tab-reports') }}" class="btn rounded-pill px-3 fw-bold {{ $filterPreset == 'all' ? 'btn-dark text-white' : 'btn-light text-secondary' }}">
                                كل الفترات
                            </a>
                        </div>

                        <!-- Custom Date Range Form -->
                        <form method="GET" action="{{ url('/contracting') }}" class="d-flex align-items-center gap-1 m-0" onsubmit="this.action = this.action + '#tab-reports'">
                            <input type="hidden" name="period" value="custom">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border text-muted small">من</span>
                                <input type="date" name="from_date" value="{{ $filterFromStr }}" class="form-control form-control-sm border" style="width: 135px;" required>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border text-muted small">إلى</span>
                                <input type="date" name="to_date" value="{{ $filterToStr }}" class="form-control form-control-sm border" style="width: 135px;" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm">
                                <i class="fa fa-filter me-1"></i>تطبيق
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted small">
                    <div>
                        <i class="fa fa-calendar-check text-primary me-1"></i>
                        الفترة الزمنية النشطة: <strong>{{ date('d/m/Y', strtotime($filterFromStr)) }}</strong> إلى <strong>{{ date('d/m/Y', strtotime($filterToStr)) }}</strong>
                        @if($filterPreset == '3months')
                            <span class="badge bg-light text-dark border ms-2 fw-bold">افتراضي 3 شهور</span>
                        @endif
                    </div>
                    <div>
                        <span class="badge bg-light text-secondary border"><i class="fa fa-city text-primary me-1"></i>{{ count($reportProjectMetrics) }} مشروع مسجل</span>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 2. بلوك إجمالي ربح المقاولات التنفيذي (Executive Grand Profit Card)
            ══════════════════════════════════════════════════════════════ -->
            <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white border">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-5 col-md-12 border-start-lg">
                            <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill fw-bold fs-7 mb-2">
                                <i class="fa fa-chart-pie text-primary me-1"></i>صافي أرباح المقاولات المحققة
                            </span>
                            <h2 class="fw-black text-dark display-5 mb-1" style="letter-spacing: -1px;">
                                {{ number_format($reportHighlights['kpis']['grand_profit'], 2) }}
                                <span class="fs-4 text-muted fw-bold">ج.م</span>
                            </h2>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold fs-7">
                                    متوسط هامش الربح: {{ $reportHighlights['kpis']['avg_margin'] }}%
                                </span>
                            </div>
                            <p class="text-muted small mt-2 mb-0">صافي الأرباح المحققة بعد استقطاع كافة تكاليف المواد، أجور الفنيين، مصروفات المواقع والخصومات الممنوحة.</p>
                        </div>

                        <div class="col-lg-7 col-md-12">
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted fw-bold">حجم الأعمال (بيع):</span>
                                            <i class="fa fa-arrow-trend-up text-primary"></i>
                                        </div>
                                        <h4 class="fw-black text-primary mb-0">{{ number_format($reportHighlights['kpis']['grand_selling'], 2) }} ج</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted fw-bold">إجمالي التكاليف المنصرفة:</span>
                                            <i class="fa fa-arrow-trend-down text-danger"></i>
                                        </div>
                                        <h4 class="fw-black text-danger mb-0">{{ number_format($reportHighlights['kpis']['grand_cost'], 2) }} ج</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted fw-bold">المحصل نقدياً بالخزينة:</span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-2 small">{{ $reportHighlights['kpis']['collection_rate'] }}% تحصيل</span>
                                        </div>
                                        <h4 class="fw-black text-success mb-0">{{ number_format($reportHighlights['kpis']['grand_paid'], 2) }} ج</h4>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 rounded-3 bg-light border h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted fw-bold">مستحقات العملاء المتبقية:</span>
                                            <i class="fa fa-clock text-secondary"></i>
                                        </div>
                                        <h4 class="fw-black text-dark mb-0">{{ number_format($reportHighlights['kpis']['grand_remaining'], 2) }} ج</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight: أرباح نسبة الإشراف فقط -->
                    <div class="mt-4 p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-award text-primary fs-5"></i>
                            <div>
                                <strong class="text-dark">أرباح نسبة الإشراف فقط (Supervision Profit):</strong>
                                <span class="text-muted small ms-2">صافي الأرباح المحققة حصرياً من إضافة نسبة الإشراف التعاقدية على بنود العملاء الخاضعة للفوترة</span>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-primary text-white fs-5 fw-black px-4 py-2 rounded-pill shadow-sm">
                                {{ number_format($reportHighlights['supervision_profit_total'], 2) }} ج.م
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 3. بطاقات استخبارات الأعمال وصنع القرار (9 Smart Decision Cards)
            ══════════════════════════════════════════════════════════════ -->
            <div class="row g-3 mb-4">
                
                <!-- 1. أكثر مشروع كسبت منه -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-trophy text-primary me-1"></i>أكثر مشروع كسبت منه
                            </span>
                            @if($reportHighlights['top_profit_project'])
                                <span class="badge bg-light text-secondary border rounded-pill">هامش: {{ $reportHighlights['top_profit_project']['margin_pct'] }}%</span>
                            @endif
                        </div>
                        @if($reportHighlights['top_profit_project'])
                            <h5 class="fw-bold text-dark mb-1">
                                <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $reportHighlights['top_profit_project']['project_id'] }})" class="text-dark text-decoration-none">
                                    {{ $reportHighlights['top_profit_project']['project_name'] }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-2"><i class="fa fa-user me-1 text-secondary"></i>العميل: {{ $reportHighlights['top_profit_project']['client_name'] }}</p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">صافي ربح المشروع:</span>
                                <h5 class="fw-black text-success mb-0">+{{ number_format($reportHighlights['top_profit_project']['net_profit'], 2) }} ج</h5>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span>بيع: {{ number_format($reportHighlights['top_profit_project']['net_selling'], 2) }} ج</span>
                                <span>تكلفة: {{ number_format($reportHighlights['top_profit_project']['total_cost'], 2) }} ج</span>
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لا توجد مشاريع محققة لأرباح خلال هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 2. أكثر مشروع صرفت فيه -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-arrow-trend-down text-danger me-1"></i>أكثر مشروع صرفت فيه
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">أعلى تكلفة</span>
                        </div>
                        @if($reportHighlights['top_spending_project'])
                            <h5 class="fw-bold text-dark mb-1">
                                <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $reportHighlights['top_spending_project']['project_id'] }})" class="text-dark text-decoration-none">
                                    {{ $reportHighlights['top_spending_project']['project_name'] }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-2"><i class="fa fa-user me-1 text-secondary"></i>العميل: {{ $reportHighlights['top_spending_project']['client_name'] }}</p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">إجمالي التكلفة المنصرفة:</span>
                                <h5 class="fw-black text-danger mb-0">{{ number_format($reportHighlights['top_spending_project']['total_cost'], 2) }} ج</h5>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span>قيمة الأعمال: {{ number_format($reportHighlights['top_spending_project']['net_selling'], 2) }} ج</span>
                                <span>العمليات: {{ $reportHighlights['top_spending_project']['ops_count'] }} بند</span>
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لا توجد تكاليف مسجلة خلال هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 3. أكثر مشروع أخد خصم -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-tag text-primary me-1"></i>أكثر مشروع أخد خصم
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">خصومات</span>
                        </div>
                        @if($reportHighlights['top_discount_project'])
                            <h5 class="fw-bold text-dark mb-1">
                                <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $reportHighlights['top_discount_project']['project_id'] }})" class="text-dark text-decoration-none">
                                    {{ $reportHighlights['top_discount_project']['project_name'] }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-2"><i class="fa fa-user me-1 text-secondary"></i>العميل: {{ $reportHighlights['top_discount_project']['client_name'] }}</p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">إجمالي الخصم الممنوح:</span>
                                <h5 class="fw-black text-dark mb-0">{{ number_format($reportHighlights['top_discount_project']['discounts'], 2) }} ج</h5>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span>البيع قبل الخصم: {{ number_format($reportHighlights['top_discount_project']['billable_selling'], 2) }} ج</span>
                                <span>الصافي: {{ number_format($reportHighlights['top_discount_project']['net_selling'], 2) }} ج</span>
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لم يتم منح أي خصومات للمشاريع في هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 4. أكثر مسوق خد فلوس -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-bullhorn text-primary me-1"></i>أكثر مسوق خد فلوس
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">عمولات تسويق</span>
                        </div>
                        @if($reportHighlights['top_marketer'])
                            <h5 class="fw-bold text-dark mb-1">{{ $reportHighlights['top_marketer']['name'] }}</h5>
                            <p class="text-muted small mb-2"><i class="fa fa-handshake me-1 text-secondary"></i>مسوق معتمد للمشاريع</p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">إجمالي العمولات:</span>
                                <h5 class="fw-black text-primary mb-0">{{ number_format($reportHighlights['top_marketer']['total_commission'], 2) }} ج</h5>
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="fa fa-circle-check text-success me-1"></i>عدد العمليات: {{ $reportHighlights['top_marketer']['ops_count'] }} عملية
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لا توجد عمولات تسويق مسجلة في هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 5. أكثر بند صرفت فيه وكان تبع مشروع إيه -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-receipt text-danger me-1"></i>أكثر بند صرفت فيه
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">أعلى بند تكلفة</span>
                        </div>
                        @if($reportHighlights['top_cost_item'])
                            <h5 class="fw-bold text-dark mb-1 text-truncate" title="{{ $reportHighlights['top_cost_item']['title'] }}">
                                {{ $reportHighlights['top_cost_item']['title'] }}
                            </h5>
                            <p class="text-muted small mb-2 text-truncate">
                                <i class="fa fa-building text-secondary me-1"></i>تبع مشروع:
                                <strong class="text-dark">{{ $reportHighlights['top_cost_item']['project_name'] }}</strong>
                                ({{ $reportHighlights['top_cost_item']['client_name'] }})
                            </p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">تكلفة البند:</span>
                                <h5 class="fw-black text-danger mb-0">{{ number_format($reportHighlights['top_cost_item']['cost'], 2) }} ج</h5>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span>الكمية: {{ $reportHighlights['top_cost_item']['qty'] }} {{ $reportHighlights['top_cost_item']['unit'] }}</span>
                                <span class="badge bg-light text-dark border">النوع: {{ $reportHighlights['top_cost_item']['op_type'] }}</span>
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لا توجد بنود مصروفات مسجلة في هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 6. أكثر بند كسبت منه وكان تبع مشروع إيه -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-coins text-success me-1"></i>أكثر بند كسبت منه
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">أعلى عائد</span>
                        </div>
                        @if($reportHighlights['top_profit_item'])
                            <h5 class="fw-bold text-dark mb-1 text-truncate" title="{{ $reportHighlights['top_profit_item']['title'] }}">
                                {{ $reportHighlights['top_profit_item']['title'] }}
                            </h5>
                            <p class="text-muted small mb-2 text-truncate">
                                <i class="fa fa-building text-secondary me-1"></i>تبع مشروع:
                                <strong class="text-dark">{{ $reportHighlights['top_profit_item']['project_name'] }}</strong>
                                ({{ $reportHighlights['top_profit_item']['client_name'] }})
                            </p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">صافي ربح البند:</span>
                                <h5 class="fw-black text-success mb-0">+{{ number_format($reportHighlights['top_profit_item']['profit'], 2) }} ج</h5>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span>البيع: {{ number_format($reportHighlights['top_profit_item']['selling'], 2) }} ج</span>
                                <span>التكلفة: {{ number_format($reportHighlights['top_profit_item']['cost'], 2) }} ج</span>
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لا توجد بنود محققة لأرباح في هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 7. أكثر فني اتعاملت معاه -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-user-gear text-primary me-1"></i>أكثر فني اتعاملت معاه
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">مصنعيات</span>
                        </div>
                        @if($reportHighlights['top_tech'])
                            <h5 class="fw-bold text-dark mb-1">{{ $reportHighlights['top_tech']['name'] }}</h5>
                            <p class="text-muted small mb-2"><i class="fa fa-hammer me-1 text-secondary"></i>إجمالي أعمال المصنعيات</p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">إجمالي الأعمال:</span>
                                <h5 class="fw-black text-dark mb-0">{{ number_format($reportHighlights['top_tech']['total_cost'], 2) }} ج</h5>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span class="text-success"><i class="fa fa-check me-1"></i>مسدد: {{ number_format($reportHighlights['top_tech']['paid_amount'], 2) }} ج</span>
                                <span class="text-danger"><i class="fa fa-clock me-1"></i>متبقي له: {{ number_format($reportHighlights['top_tech']['remaining_debt'], 2) }} ج</span>
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لا توجد مصنعيات فنيين مسجلة في هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 8. أكثر خامة جبتها -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-boxes-stacked text-primary me-1"></i>أكثر خامة جبتها
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">توريدات</span>
                        </div>
                        @if($reportHighlights['top_material'])
                            <h5 class="fw-bold text-dark mb-1 text-truncate" title="{{ $reportHighlights['top_material']['title'] }}">
                                {{ $reportHighlights['top_material']['title'] }}
                            </h5>
                            <p class="text-muted small mb-2"><i class="fa fa-dolly me-1 text-secondary"></i>أعلى كمية توريد مشتراة</p>
                            <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <span class="text-muted small">إجمالي الكمية:</span>
                                <h5 class="fw-black text-primary mb-0">{{ $reportHighlights['top_material']['total_qty'] }} {{ $reportHighlights['top_material']['unit'] }}</h5>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-2">
                                <span>التكلفة: {{ number_format($reportHighlights['top_material']['total_cost'], 2) }} ج</span>
                                <span>عدد الفواتير: {{ $reportHighlights['top_material']['count'] }} مرة</span>
                            </div>
                        @else
                            <div class="py-4 text-center text-muted">
                                <p class="mb-0 small">لا توجد خامات مشتراة في هذه الفترة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 9. مؤشرات السيولة والديون لصنع القرار -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa fa-scale-balanced text-primary me-1"></i>مؤشرات السيولة والديون
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill">صنع القرار</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">المركز المالي للتعاقدات</h5>
                        <p class="text-muted small mb-2">موقف التدفق النقدي والتزامات الشركة</p>
                        <div class="p-2 rounded-3 bg-light border mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">معدل التحصيل الفعلي:</span>
                                <strong class="text-success">{{ $reportHighlights['kpis']['collection_rate'] }}%</strong>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ min(100, $reportHighlights['kpis']['collection_rate']) }}%"></div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>ديون الموردين المعلقة:</span>
                            <strong class="text-danger">{{ number_format($reportHighlights['kpis']['supplier_debts'], 2) }} ج</strong>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 4. الرسوم البيانية والشارتات التفاعلية (Chart.js Interactive)
            ══════════════════════════════════════════════════════════════ -->
            <div class="row g-4 mb-4">
                <!-- شارت 1: توزيع المصروفات -->
                <div class="col-lg-4 col-md-12">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fa fa-chart-pie text-primary me-2"></i>توزيع وهيكل المصروفات
                            </h6>
                            <span class="badge bg-light text-secondary border">
                                الإجمالي: {{ number_format($reportHighlights['expenses_breakdown']['total'], 2) }} ج
                            </span>
                        </div>
                        <div style="position: relative; height: 260px;">
                            <canvas id="chartExpensesBreakdown"></canvas>
                        </div>
                        <div class="mt-3 pt-3 border-top small">
                            <div class="d-flex justify-content-between mb-1">
                                <span><i class="fa fa-square me-1" style="color: #2563eb;"></i>خامات:</span>
                                <strong>{{ number_format($reportHighlights['expenses_breakdown']['material'], 2) }} ج</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span><i class="fa fa-square me-1" style="color: #0d9488;"></i>مصنعيات:</span>
                                <strong>{{ number_format($reportHighlights['expenses_breakdown']['technician'], 2) }} ج</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span><i class="fa fa-square me-1" style="color: #6366f1;"></i>نثريات موقع:</span>
                                <strong>{{ number_format($reportHighlights['expenses_breakdown']['petty'], 2) }} ج</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span><i class="fa fa-square me-1" style="color: #64748b;"></i>مصروفات داخلية:</span>
                                <strong>{{ number_format($reportHighlights['expenses_breakdown']['expense'], 2) }} ج</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fa fa-square me-1" style="color: #f59e0b;"></i>عمولات مسوقين:</span>
                                <strong>{{ number_format($reportHighlights['expenses_breakdown']['commission'], 2) }} ج</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- شارت 2: مقارنة المشاريع مالياً -->
                <div class="col-lg-4 col-md-12">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fa fa-chart-column text-primary me-2"></i>مقارنة المشاريع مالياً
                            </h6>
                            <span class="badge bg-light text-secondary border small">تكلفة vs بيع vs ربح</span>
                        </div>
                        <div style="position: relative; height: 320px;">
                            <canvas id="chartProjectsComparison"></canvas>
                        </div>
                    </div>
                </div>

                <!-- شارت 3: التدفق والمسار المالي الشهري -->
                <div class="col-lg-4 col-md-12">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white h-100 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fa fa-chart-line text-success me-2"></i>المسار والتدفق المالي (6 شهور)
                            </h6>
                            <span class="badge bg-light text-secondary border small">التطور الزمني</span>
                        </div>
                        <div style="position: relative; height: 320px;">
                            <canvas id="chartMonthlyTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 5. جدول الأداء المالي التفصيلي للمشاريع في الفترة المحددة
            ══════════════════════════════════════════════════════════════ -->
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-0"><i class="fa fa-table-list text-primary me-2"></i>جدول أرباح ومصروفات المشاريع للفترة المحددة</h5>
                        <p class="text-muted small mb-0">كشف شامل بتكلفة وبيع وتحصيلات وأرباح كل مشروع على حدة</p>
                    </div>
                    <div style="min-width: 250px;">
                        <input type="text" class="form-control form-control-sm rounded-pill px-3" placeholder="🔍 بحث في جدول المشاريع..." onkeyup="filterReportProjectsTable(this.value)">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle" id="reportProjectsTable">
                        <thead class="table-light text-muted small fw-bold">
                            <tr>
                                <th>#</th>
                                <th class="text-start">المشروع</th>
                                <th>العميل</th>
                                <th>نسبة الإشراف</th>
                                <th>إجمالي التكلفة</th>
                                <th>قيمة البيع</th>
                                <th>المحصل نقداً</th>
                                <th>المتبقي طرف العميل</th>
                                <th>صافي الربح</th>
                                <th>هامش الربح</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportProjectMetrics as $idx => $m)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-start fw-bold">
                                        <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $m['project_id'] }})" class="text-primary text-decoration-none" title="عرض في سجل المشاريع">
                                            <i class="fa fa-folder-open me-1"></i>{{ $m['project_name'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0)" onclick="openProjectFromAnywhere({{ $m['project_id'] }})" class="text-dark text-decoration-none" title="عرض في سجل المشاريع">
                                            {{ $m['client_name'] }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $m['supervision_pct'] }}%</span></td>
                                    <td class="text-danger fw-bold">{{ number_format($m['total_cost'], 2) }} ج</td>
                                    <td class="text-primary fw-bold">{{ number_format($m['net_selling'], 2) }} ج</td>
                                    <td class="text-success fw-bold">{{ number_format($m['client_paid'], 2) }} ج</td>
                                    <td class="text-warning fw-bold">{{ number_format($m['client_remaining'], 2) }} ج</td>
                                    <td class="fw-black {{ $m['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $m['net_profit'] >= 0 ? '+' : '' }}{{ number_format($m['net_profit'], 2) }} ج
                                    </td>
                                    <td>
                                        <span class="badge {{ $m['margin_pct'] >= 20 ? 'bg-success' : ($m['margin_pct'] > 0 ? 'bg-primary' : 'bg-danger') }}">
                                            {{ $m['margin_pct'] }}%
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" onclick="openProjectFromAnywhere({{ $m['project_id'] }})" title="فتح ملف المشروع">
                                            <i class="fa fa-external-link"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-4 text-muted">لا توجد مشاريع مسجلة في النظام</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-black">
                            <tr>
                                <td colspan="4" class="text-end">الإجمالي العام للفترة:</td>
                                <td class="text-danger">{{ number_format($reportHighlights['kpis']['grand_cost'], 2) }} ج</td>
                                <td class="text-primary">{{ number_format($reportHighlights['kpis']['grand_selling'], 2) }} ج</td>
                                <td class="text-success">{{ number_format($reportHighlights['kpis']['grand_paid'], 2) }} ج</td>
                                <td class="text-warning">{{ number_format($reportHighlights['kpis']['grand_remaining'], 2) }} ج</td>
                                <td class="{{ $reportHighlights['kpis']['grand_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($reportHighlights['kpis']['grand_profit'], 2) }} ج
                                </td>
                                <td>
                                    <span class="badge bg-dark">{{ $reportHighlights['kpis']['avg_margin'] }}%</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             TAB 9: الإعدادات (المشاريع، البنود العامة، والنثريات)
        ════════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-settings">
            <h4 class="fw-black mb-4 border-bottom pb-2 text-dark"><i class="fa fa-cogs text-warning me-2"></i>إعدادات نظام المقاولات</h4>

            <div class="row g-4">
                <!-- 1. إدارة وتعديل المشاريع -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa fa-building text-warning me-2"></i>إدارة المشاريع والعملاء</h6>
                            <span class="badge bg-secondary">{{ $projects->count() }} مشروع</span>
                        </div>
                        <div class="card-body p-3">
                            <form action="{{ route('contracting.project.store') }}" method="POST" class="mb-3 p-3 bg-light rounded-3 border">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="name" class="form-control form-control-sm rounded-3" placeholder="اسم المشروع/الموقع *" required>
                                </div>
                                <div class="mb-2">
                                    <input type="text" name="client_name" class="form-control form-control-sm rounded-3" placeholder="اسم العميل *" required>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="number" name="area_sqm" class="form-control form-control-sm rounded-3 text-center text-success fw-bold" placeholder="المساحة م²" step="any">
                                    </div>
                                    <div class="col-4">
                                        <input type="number" name="supervision_pct" class="form-control form-control-sm rounded-3 text-center" value="15" placeholder="إشراف %" required>
                                    </div>
                                    <div class="col-4">
                                        <select name="status" class="form-select form-select-sm rounded-3">
                                            <option value="active">نشط</option>
                                            <option value="completed">منتهي</option>
                                            <option value="suspended">معلق</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="add_default_items" id="checkDefItemsSettings" checked>
                                    <label class="form-check-label small fw-bold" for="checkDefItemsSettings">إضافة البنود الأساسية</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-dark w-100 rounded-pill fw-bold"><i class="fa fa-plus me-1"></i>إضافة مشروع جديد</button>
                            </form>

                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                @foreach($projects as $proj)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-2 mb-2 rounded-3 bg-light border">
                                        <div style="cursor: pointer;" onclick="openProjectFromAnywhere({{ $proj->id }})" title="اضغط لعرض تفاصيل المشروع">
                                            <div class="fw-bold text-dark"><i class="fa fa-folder-open text-warning me-1"></i>{{ $proj->name }}</div>
                                            <small class="text-muted">{{ $proj->client_name }} | إشراف: {{ $proj->supervision_pct }}% @if(!empty($proj->area_sqm)) | <strong class="text-success">{{ number_format($proj->area_sqm, 0) }} م²</strong> @endif</small>
                                            <div>
                                                @if($proj->status == 'active') <span class="badge bg-success">نشط</span>
                                                @elseif($proj->status == 'completed') <span class="badge bg-secondary">منتهي</span>
                                                @else <span class="badge bg-warning text-dark">معلق</span> @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0" onclick="openProjectFromAnywhere({{ $proj->id }})" title="عرض في سجل المشاريع"><i class="fa fa-eye"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="deleteProject({{ $proj->id }}, '{{ addslashes($proj->name) }}')" title="حذف المشروع"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. بنود الأعمال التي تظهر (مكتبة البنود العامة) -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa fa-layer-group me-2"></i>بنود الأعمال التي تظهر (المكتبة)</h6>
                            <span class="badge bg-light text-dark">{{ $globalItems->count() }} بند</span>
                        </div>
                        <div class="card-body p-3">
                            <form onsubmit="submitGlobalItem(event)" class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="mb-2">
                                    <input type="text" id="setting-item-title" class="form-control form-control-sm rounded-3" placeholder="اسم البند (مثلاً: بند أعمال العزل...) *" required>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="setting-item-apply" checked>
                                    <label class="form-check-label small fw-bold" for="setting-item-apply">إضافته فوراً لكل المشاريع الحالية النشطة</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill fw-bold"><i class="fa fa-plus me-1"></i>إضافة بند جديد للمكتبة</button>
                            </form>

                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;" id="global-items-list">
                                @foreach($globalItems as $gItem)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-2 mb-2 rounded-3 bg-light border">
                                        <span class="fw-bold text-dark"><i class="fa fa-check-circle text-primary me-2"></i>{{ $gItem->title }}</span>
                                        <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteGlobalItem({{ $gItem->id }})"><i class="fa fa-trash"></i></button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. أسماء النثريات المقترحة (بدون سعر - محملة على العميل) -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-info text-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa fa-mug-hot me-2"></i>أسماء النثريات (لتحميلها على العميل)</h6>
                            <span class="badge bg-light text-dark" id="petty-titles-count">{{ $pettyTitlesList->count() }} اسم</span>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">تُعرض كأسماء مقترحة وأوتوكومبليت بدون سعر مع إمكانية كتابة أي بيان يدويًا بحرية.</p>
                            <div class="input-group input-group-sm mb-3">
                                <input type="text" id="new-petty-title-input" class="form-control rounded-start-pill" placeholder="اكتب اسم بند نثريات جديد...">
                                <button class="btn btn-info text-white rounded-end-pill px-3 fw-bold" type="button" onclick="addNewPettyTitle()">
                                    <i class="fa fa-plus me-1"></i>إضافة
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2 p-2 bg-light rounded-3 border" id="petty-titles-tags" style="max-height: 320px; overflow-y: auto;">
                                @foreach($pettyTitlesList as $pt)
                                    <span class="badge bg-white text-dark border p-2 rounded-pill d-inline-flex align-items-center gap-2 shadow-sm" id="badge-petty-title-{{ $pt->id }}">
                                        <i class="fa fa-tag text-info"></i>{{ $pt->title }}
                                        <i class="fa fa-times text-danger" style="cursor: pointer;" onclick="deletePettyTitle({{ $pt->id }})" title="حذف"></i>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. بنود المصروفات العامة والداخلية (بدون سعر) -->
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa fa-money-bill-wave me-2"></i>بنود المصروفات العامة / الداخلية</h6>
                            <span class="badge bg-light text-dark" id="expense-titles-count">{{ $expenseTitlesList->count() }} بند</span>
                        </div>
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">تُعرض كبنود مقترحة وأوتوكومبليت بدون سعر لمصروفات الموقع والإدارة مع إمكانية الكتابة الحرة.</p>
                            <div class="input-group input-group-sm mb-3">
                                <input type="text" id="new-expense-title-input" class="form-control rounded-start-pill" placeholder="اكتب بند مصروف جديد...">
                                <button class="btn btn-danger rounded-end-pill px-3 fw-bold" type="button" onclick="addNewExpenseTitle()">
                                    <i class="fa fa-plus me-1"></i>إضافة
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2 p-2 bg-light rounded-3 border" id="expense-titles-tags" style="max-height: 320px; overflow-y: auto;">
                                @foreach($expenseTitlesList as $et)
                                    <span class="badge bg-white text-dark border p-2 rounded-pill d-inline-flex align-items-center gap-2 shadow-sm" id="badge-expense-title-{{ $et->id }}">
                                        <i class="fa fa-receipt text-danger"></i>{{ $et->title }}
                                        <i class="fa fa-times text-danger" style="cursor: pointer;" onclick="deleteExpenseTitle({{ $et->id }})" title="حذف"></i>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. تسجيل حركات النثريات السريعة -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fa fa-coins me-2"></i>تسجيل حركات النثريات السريعة وتصنيفاتها</h6>
                            <span class="badge bg-dark">{{ $pettyCategories->count() }} تصنيف</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <!-- Quick Record Petty Expense Form -->
                                    <form onsubmit="submitPettyExpense(event)" class="p-3 bg-light rounded-3 border">
                                        <div class="fw-bold small text-muted mb-2">تسجيل وصرف نثريات مباشرة:</div>
                                        <div class="mb-2">
                                            <input type="text" id="setting-petty-record-title" class="form-control form-control-sm rounded-3" list="list-petty-titles" placeholder="بيان المصروف (مثال: شاي وسكر للموقع) *" required>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" id="petty-amount" class="form-control rounded-start-3" placeholder="المبلغ *" step="any" required>
                                                    <span class="input-group-text">ج</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <select id="petty-category" class="form-select form-select-sm rounded-3">
                                                    <option value="">بدون تصنيف</option>
                                                    @foreach($pettyCategories as $pc)
                                                        <option value="{{ $pc->id }}">{{ $pc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <select id="petty-account" class="form-select form-select-sm rounded-3">
                                                <option value="">بدون خصم من الخزينة</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ $acc->id == $defaultSafeId ? 'selected' : '' }}>{{ $acc->account_name ?? $acc->name }} (رصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-warning text-dark w-100 rounded-pill fw-bold"><i class="fa fa-coins me-1"></i>تسجيل وصرف النثريات</button>
                                    </form>

                                    <!-- Manage Petty Categories Section -->
                                    <div class="p-3 mt-3 bg-light rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold small">تصنيفات النثريات:</span>
                                            <button class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" onclick="showAddPettyCategory()"><i class="fa fa-plus"></i> تصنيف جديد</button>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1" id="petty-cat-tags">
                                            @foreach($pettyCategories as $pc)
                                                <span class="badge bg-secondary d-inline-flex align-items-center gap-1">
                                                    {{ $pc->name }}
                                                    <i class="fa fa-times text-danger" style="cursor: pointer;" onclick="deletePettyCategory({{ $pc->id }})" title="حذف التصنيف"></i>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <!-- Recent Petty Log -->
                                    <div class="fw-bold small text-muted mb-2"><i class="fa fa-history me-1 text-primary"></i>آخر حركات النثريات المصروفة:</div>
                                    <div class="list-group list-group-flush border rounded-3 overflow-hidden" style="max-height: 320px; overflow-y: auto;">
                                        @forelse($recentPettyExpenses as $re)
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-white">
                                                <div>
                                                    <span class="fw-bold small text-dark">{{ $re->title }}</span>
                                                    <small class="text-muted d-block" style="font-size: 11px;">{{ substr($re->created_at, 0, 10) }} | {{ $re->category_name ?: 'عام' }} @if(!empty($re->account_name)) | من: {{ $re->account_name }} @endif</small>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-danger small">{{ number_format($re->amount, 2) }} ج</span>
                                                    <button class="btn btn-sm btn-link text-danger p-0" onclick="deletePettyExpense({{ $re->id }})"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted small">لا توجد حركات نثريات مسجلة بعد</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     MODALS SECTION
════════════════════════════════════════════════════════════════ -->

<!-- Modal: New Project -->
<div class="modal fade" id="modalNewProject" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa fa-building text-warning me-2"></i>إنشاء مشروع مقاولات جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('contracting.project.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المشروع أو الموقع <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" required placeholder="مثال: فيلا التجمع الخامس، شقة الشيخ زايد...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم العميل <span class="text-danger">*</span></label>
                        <input type="text" name="client_name" class="form-control rounded-3" required placeholder="اسم العميل الكامل...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم هاتف العميل</label>
                        <input type="text" name="client_phone" class="form-control rounded-3" placeholder="01xxxxxxxxx">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-7 col-12">
                            <label class="form-label fw-bold">الموقع / العنوان</label>
                            <input type="text" name="location" class="form-control rounded-3" placeholder="العنوان بالتفصيل...">
                        </div>
                        <div class="col-md-5 col-12">
                            <label class="form-label fw-bold text-success"><i class="fa fa-ruler-combined me-1"></i>مساحة المشروع</label>
                            <div class="input-group">
                                <input type="number" name="area_sqm" class="form-control rounded-start-3 text-center fw-bold text-success" step="any" placeholder="المساحة بالمتر">
                                <span class="input-group-text">م²</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">نسبة الإشراف المعتمدة (%)</label>
                            <input type="number" name="supervision_pct" class="form-control rounded-3 text-center" value="15.00" step="any" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">نوع التعاقد</label>
                            <select name="contract_type" class="form-select rounded-3">
                                <option value="cost_plus">إشراف وتكلفة (Cost-Plus)</option>
                                <option value="fixed_price">مقاولة بسعر ثابت</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="add_default_items" id="checkDefaultItems" checked>
                        <label class="form-check-label fw-bold" for="checkDefaultItems">إضافة بنود الأعمال الأساسية تلقائياً (كهرباء، سباكة، محارة، جبس، سيراميك، دهانات)</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات إضافية</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="أي شروط أو اتفاقات خاصة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">حفظ وبدء المشروع</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Receive Payment -->
<div class="modal fade" id="modalReceivePayment" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa fa-hand-holding-dollar me-2"></i>استلام دفعة / مستخلص من العميل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitReceivePayment(event)">
                <input type="hidden" id="pay-project-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">المبلغ المستلم <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="pay-amount" class="form-control rounded-start-3 fs-5 fw-bold" required step="any" placeholder="0.00">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">إيداع في الخزينة / الحساب <span class="text-danger">*</span></label>
                        <select id="pay-account" class="form-select rounded-3" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ $acc->id == $defaultSafeId ? 'selected' : '' }}>{{ $acc->account_name ?? $acc->name }} (رصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">البيان / ملاحظات الإيصال</label>
                        <input type="text" id="pay-notes" class="form-control rounded-3" placeholder="مثال: دفعة تحت حساب مرحلة المحارة...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">تأكيد الاستلام والإيداع</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Apply Discount -->
<div class="modal fade" id="modalApplyDiscount" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa fa-tags me-2"></i>إضافة خصم للعميل على المشروع</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitDiscount(event)">
                <input type="hidden" id="disc-project-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">قيمة الخصم <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="disc-amount" class="form-control rounded-start-3 fs-5 fw-bold" required step="any" placeholder="0.00">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">سبب الخصم</label>
                        <input type="text" id="disc-reason" class="form-control rounded-3" placeholder="مثال: خصم تجاري، تسوية بند...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">تطبيق الخصم</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Convert to Installment Plan -->
<div class="modal fade" id="modalConvertToInstallments" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa fa-credit-card me-2"></i>تحويل باقي حساب المشروع لمنظومة الأقساط</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitInstallmentPlan(event)">
                <input type="hidden" id="inst-project-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">المبلغ المراد تقسيطه <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="inst-amount" class="form-control rounded-start-3 fs-5 fw-bold" required step="any">
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">عدد الشهور / الأقساط <span class="text-danger">*</span></label>
                            <input type="number" id="inst-months" class="form-control rounded-3 text-center fw-bold" value="6" min="1" required oninput="calcMonthlyInstallment()">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">قيمة القسط الشهري</label>
                            <input type="number" id="inst-monthly" class="form-control rounded-3 text-center bg-light fw-bold text-primary" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ استحقاق أول قسط</label>
                        <input type="date" id="inst-start-date" class="form-control rounded-3" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fa fa-info-circle me-1"></i>سيتم إدراج هذا العقد تلقائياً في <strong>منظومة الأقساط العامة</strong> بالنظام لمتابعته وتحصيله كأي قسط عادي.
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">إنشاء جدول الأقساط وترحيله</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: New Directory Person -->
<div class="modal fade" id="modalNewPerson" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa fa-user-plus text-warning me-2"></i>إضافة متعامل جديد إلى سجل المتعاملين</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitDirectoryPerson(event)">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">التصنيف <span class="text-danger">*</span></label>
                        <select id="dp-type" class="form-select rounded-3" required>
                            <option value="supplier">مورد خامات ومواد</option>
                            <option value="technician">فني / صنايعي</option>
                            <option value="marketer">مسوق / وسيط</option>
                            <option value="client">عميل</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" id="dp-name" class="form-control rounded-3" required placeholder="الاسم...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم الهاتف</label>
                        <input type="text" id="dp-phone" class="form-control rounded-3" placeholder="01xxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">التخصص / نوع المواد</label>
                        <input type="text" id="dp-specialty" class="form-control rounded-3" list="list-specialties" placeholder="مثال: سباكة، كهرباء، أسمنت، دهانات...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات</label>
                        <input type="text" id="dp-notes" class="form-control rounded-3" placeholder="اختياري...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">حفظ في سجل المتعاملين</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Project Commission (عمولة مسوق على المشروع ككل) -->
<div class="modal fade" id="modalProjectCommission" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa fa-bullhorn me-2"></i>تسجيل عمولة مسوق للمشروع</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="submitProjectCommission(event)">
                <input type="hidden" id="modal-comm-project-id">
                <div class="modal-body p-4">
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="fa fa-info-circle text-primary me-1"></i>يتم تسجيل هذه العمولة على <strong>المشروع ككل</strong> وتخصم من صافي أرباح المشروع الإجمالية، ولا ترتبط ببند محدد.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المسوق / الوسيط <span class="text-danger">*</span> (يحفظ في السجل)</label>
                        <input type="text" id="modal-comm-name" class="form-control rounded-3" list="list-marketers" placeholder="اكتب أو اختر اسم المسوق..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">مبلغ العمولة المستحقة <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="modal-comm-val" class="form-control rounded-start-3 fs-5 fw-bold" placeholder="0.00" step="any" required>
                            <span class="input-group-text">ج</span>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">طريقة السداد</label>
                            <select id="modal-comm-method" class="form-select form-select-sm rounded-3">
                                <option value="cash">نقداً بالكامل (كاش)</option>
                                <option value="later">آجل (دين مسوق في شاشة الديون)</option>
                                <option value="bank">تحويل بنكي / محفظة</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">الخزينة (إذا تم السداد فوراً)</label>
                            <select id="modal-comm-acc" class="form-select form-select-sm rounded-3">
                                <option value="">بدون خصم من الخزينة</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name ?? $acc->name }} (رصيد: {{ number_format($acc->balance, 2) }} ج)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">ملاحظات / اتفاق العمولة</label>
                        <input type="text" id="modal-comm-note" class="form-control form-control-sm rounded-3" placeholder="اختياري...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">حفظ وصرف العمولة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: View Invoice Details (عرض تفاصيل وبنود فاتورة الخامات) -->
<div class="modal fade" id="modalInvoiceDetails" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="inv-modal-title"><i class="fa fa-boxes-stacked text-warning me-2"></i>تفاصيل فاتورة الخامات</h5>
                    <small class="text-white-50" id="inv-modal-subtitle"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3 p-3 bg-light rounded-3 border">
                    <div class="col-md-3">
                        <small class="text-muted d-block">المورد</small>
                        <span class="fw-bold text-primary" id="inv-modal-supplier">-</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">المشروع</small>
                        <span class="fw-bold text-dark" id="inv-modal-project">-</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">إجمالي الشراء (عليك)</small>
                        <span class="fw-bold text-danger" id="inv-modal-cost">0 ج</span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">المتبقي (آجل دين)</small>
                        <span class="fw-bold text-danger" id="inv-modal-debt">0 ج</span>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-bordered table-sm text-center align-middle mb-0" id="table-inv-modal-items">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>#</th>
                                <th class="text-start">اسم الخامة</th>
                                <th>الكمية</th>
                                <th>سعر الشراء</th>
                                <th>سعر البيع</th>
                                <th>إجمالي الشراء</th>
                                <th>إجمالي البيع</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                <a id="btn-modal-print-invoice" href="#" target="_blank" class="btn btn-dark rounded-pill px-4 fw-bold">
                    <i class="fa fa-print me-1"></i>طباعة الفاتورة بالهوية الرسمية
                </a>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Review & Confirm Operations Before Posting (مراجعة وتعديل عمليات الصرف قبل الترحيل) -->
<div class="modal fade" id="modalReviewOperations" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-clipboard-check text-warning fs-3"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">مراجعة وتعديل عمليات صندوق العمليات قبل الترحيل النهائي</h5>
                        <small class="text-white-50" id="review-modal-subtitle">المشروع: - | العميل: -</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Summary KPI Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-md-2 col-6">
                        <div class="p-2 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block">إجمالي التكلفة (عليك)</small>
                            <span class="fw-black text-danger fs-6" id="review-total-cost">0.00 ج</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="p-2 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block">سعر البيع الأساسي</small>
                            <span class="fw-black text-secondary fs-6" id="review-base-sell">0.00 ج</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="p-2 bg-light rounded-3 text-center border">
                            <small class="text-muted d-block" id="review-sup-label">نسبة الإشراف (15%)</small>
                            <span class="fw-black text-warning fs-6" id="review-sup-amount">+ 0.00 ج</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-2 bg-light rounded-3 text-center border border-primary">
                            <small class="text-primary fw-bold d-block">إجمالي المطالبة (على العميل)</small>
                            <span class="fw-black text-primary fs-5" id="review-customer-total">0.00 ج</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-12">
                        <div class="p-2 bg-light rounded-3 text-center border border-success">
                            <small class="text-success fw-bold d-block">صافي المكسب المتوقع</small>
                            <span class="fw-black text-success fs-5" id="review-net-profit">0.00 ج</span>
                        </div>
                    </div>
                </div>

                <!-- Payment & Account Settlement Controls -->
                <div class="alert alert-warning border rounded-3 mb-3 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="small fw-bold text-dark">
                        <i class="fa fa-info-circle text-primary me-1 fs-6 align-middle"></i>
                        الخامات والمصنعيات والعمولات تُرحل <strong>آجل (مديونية موردين وصنايعية)</strong>، بينما المصروفات والنثريات تُصرف مباشرة من <strong>الخزينة المختارة</strong>.
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-danger rounded-pill px-2 py-1">إجمالي الآجل (الديون):</span>
                            <span class="fw-black text-danger fs-6" id="review-debt-badge-display">0.00 ج</span>
                        </div>
                        <div class="d-flex align-items-center gap-1" id="review-cash-badge-wrapper" style="display: none !important;">
                            <span class="badge bg-dark rounded-pill px-2 py-1">صرف مباشر من الخزينة:</span>
                            <span class="fw-black text-dark fs-6" id="review-cash-badge-display">0.00 ج</span>
                        </div>
                    </div>
                    <input type="hidden" id="review-edit-pay-method" value="later">
                    <input type="hidden" id="review-edit-paid" value="0">
                    <input type="hidden" id="review-edit-debt" value="0.00 ج">
                    <input type="hidden" id="review-edit-account" value="">
                </div>

                <!-- Operations Table -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fa fa-edit text-primary me-2"></i>جدول بنود الصندوق (يمكنك تعديل أي بيان، سعر، كمية مباشرة هنا):</h6>
                    <small class="text-muted"><i class="fa fa-info-circle text-info me-1"></i>التعديلات تحدث المجاميع والحسابات لحظياً</small>
                </div>
                <div class="table-responsive border rounded-3 limit-5-rows" style="max-height: 290px; overflow-y: auto;">
                    <table class="table table-bordered table-hover text-center align-middle mb-0" id="table-review-items">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="width: 35px;">#</th>
                                <th style="width: 85px;">النوع</th>
                                <th style="min-width: 200px;">البيان / اسم الخامة</th>
                                <th style="min-width: 170px;">المورد / الفني / الخزينة</th>
                                <th style="width: 85px;">الكمية</th>
                                <th style="width: 105px;">سعر التكلفة</th>
                                <th style="width: 105px;">إجمالي التكلفة</th>
                                <th style="width: 105px;">سعر البيع</th>
                                <th style="width: 125px;">المطالبة بالإشراف</th>
                                <th style="width: 45px;">حذف</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">
                    <i class="fa fa-arrow-right me-1"></i>رجوع للتعديل في النموذج
                </button>
                <button type="button" class="btn btn-success rounded-pill px-5 fw-black py-2 fs-6 shadow" id="btn-confirm-post-review" onclick="confirmAndPostFromReview()">
                    <i class="fa fa-check-double me-2"></i>تأكيد وترحيل الحسابات نهائياً
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: طباعة وعرض المقايسة التفصيلية -->
<div class="modal fade" id="modalPrintSurvey" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-black text-dark" id="modal-survey-print-title">
                    <i class="fa fa-calculator text-primary me-2"></i>كشف مقايسة مساحات تشطيبات
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modal-survey-print-content">
                <!-- Content generated dynamically -->
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="executeSurveyModalPrint()">
                    <i class="fa fa-print me-1"></i> طباعة الكشف
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: طباعة وعرض تقرير تقدير تكلفة المشروع -->
<div class="modal fade" id="modalPrintEstimation" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-black text-dark" id="modal-est-print-title">
                    <i class="fa fa-wand-magic-sparkles text-warning me-2"></i>دراسة جدوى وتقدير تكلفة مشروع
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modal-est-print-content">
                <!-- Content generated dynamically -->
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="executeEstimationModalPrint()">
                    <i class="fa fa-print me-1"></i> طباعة التقرير التقديري
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: السجل الكامل لشراء الخامة عبر الفواتير والمشاريع (مطابقة لتصميم المستخدم) -->
<div class="modal fade" id="modalMaterialHistory" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-black text-dark" id="modal-mat-history-title">
                    <i class="fa fa-history text-primary me-2"></i>سجل الشراء الكامل للصنف
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>م</th>
                                <th>تاريخ الشراء</th>
                                <th class="text-start">المشروع</th>
                                <th>المورد</th>
                                <th>سعر الوحدة (شراء)</th>
                                <th>الكمية</th>
                                <th>الوحدة</th>
                                <th>إجمالي القيمة</th>
                                <th>رقم الفاتورة</th>
                            </tr>
                        </thead>
                        <tbody id="modal-mat-history-tbody">
                            <!-- Injected dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Material Invoice / Returns (تعديل فاتورة الخامات والمرتجعات) -->
<div class="modal fade" id="modalEditMaterialInvoice" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">تعديل فاتورة خامات / إثبات مرتجع (<span id="edit-minv-ref-title"></span>)</h5>
                        <small class="text-white-50" id="edit-minv-project-subtitle"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="edit-minv-ref">
                <input type="hidden" id="edit-minv-project-id">

                <div class="row g-3 mb-3 p-3 bg-light rounded-3 border align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">اسم المورد / المحل (من قاعدة البيانات)</label>
                        <select id="edit-minv-supplier" class="form-select rounded-3 fw-bold">
                            <option value="">-- اختر المورد --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->name }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">المشروع</label>
                        <input type="text" id="edit-minv-project-name" class="form-control rounded-3 bg-white" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">العميل</label>
                        <input type="text" id="edit-minv-client-name" class="form-control rounded-3 bg-white" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold"><i class="fa fa-cubes text-primary me-1"></i>بنود وخامات الفاتورة (لعمل مرتجع قلل الكمية أو ضع 0 أو احذف البند):</span>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-bold" onclick="addEditMinvItemRow()">
                        <i class="fa fa-plus me-1"></i>+ إضافة خامة للفاتورة
                    </button>
                </div>

                <div class="table-responsive border rounded-3 mb-3" style="max-height: 340px; overflow-y: auto;">
                    <table class="table table-bordered table-sm text-center align-middle mb-0" id="table-edit-minv-items">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 35px;">#</th>
                                <th class="text-start" style="min-width: 220px;">اسم الخامة <span class="text-danger">*</span></th>
                                <th style="width: 90px;">الكمية</th>
                                <th style="width: 115px;">سعر التكلفة (عليك)</th>
                                <th style="width: 90px;">نسبة الإشراف %</th>
                                <th style="width: 115px;">سعر البيع (للعميل)</th>
                                <th style="width: 105px;">إجمالي الشراء</th>
                                <th style="width: 105px;">إجمالي البيع</th>
                                <th style="width: 40px;">حذف</th>
                            </tr>
                        </thead>
                        <tbody id="edit-minv-items-tbody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Financial Reconciliations -->
                <div class="p-3 bg-light rounded-3 border">
                    <div class="row g-3 align-items-center mb-3 text-center">
                        <div class="col-md-3 col-6">
                            <span class="text-muted small d-block">إجمالي تكلفة الفاتورة (عليك):</span>
                            <span class="fs-5 fw-black text-danger" id="edit-minv-total-cost">0.00 ج</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted small d-block">إجمالي المطالبة (على العميل):</span>
                            <span class="fs-5 fw-black text-primary" id="edit-minv-total-selling">0.00 ج</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted small d-block">صافي الربح المتوقع:</span>
                            <span class="fs-5 fw-black text-success" id="edit-minv-total-profit">0.00 ج</span>
                        </div>
                        <div class="col-md-3 col-6">
                            <span class="text-muted small d-block">المتبقي (دين على الشركة):</span>
                            <span class="fs-5 fw-black text-danger" id="edit-minv-debt-badge">0.00 ج</span>
                        </div>
                    </div>

                    <div class="row g-3 pt-3 border-top align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">المسدد نقداً للمورد حالياً</label>
                            <div class="input-group">
                                <input type="number" step="any" min="0" id="edit-minv-paid" class="form-control rounded-start-3 text-center fw-bold text-success" oninput="calcEditMinvTotals()">
                                <span class="input-group-text">ج</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">الخزينة / الحساب البنكي</label>
                            <select id="edit-minv-account" class="form-select rounded-3">
                                <option value="">-- اختر الخزينة للتسوية --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance, 2) }} ج)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info py-2 px-3 small mb-0">
                                <i class="fa fa-info-circle me-1"></i>
                                في حالة تقليل الكميات (مرتجع) أو تعديل المسدد، يُرد الفارق إلى الخزينة المختارة تلقائياً وتُسوى ديون المورد ومستحقات العميل.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm" onclick="saveEditMaterialInvoice()" id="btn-save-edit-minv">
                    <i class="fa fa-floppy-disk me-1"></i>حفظ التعديلات والمرتجعات
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Single Operation (تعديل حركة منفردة - مصنعية فني / مصروف داخلي) -->
<div class="modal fade" id="modalEditSingleOperation" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="fa fa-pen me-2 text-warning"></i>تعديل حركة بمشروع
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="edit-op-id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">بيان الحركة / اسم البند <span class="text-danger">*</span></label>
                    <input type="text" id="edit-op-name" class="form-control rounded-3 fw-bold" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">الكمية</label>
                    <input type="number" step="any" min="0.01" id="edit-op-qty" class="form-control rounded-3 text-center fw-bold" oninput="calcEditOpLive()">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-danger">سعر التكلفة <span class="text-danger">*</span></label>
                        <input type="number" step="any" min="0" id="edit-op-cost" class="form-control rounded-3 text-center fw-bold text-danger" oninput="calcEditOpLive()">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-warning">نسبة الإشراف %</label>
                        <input type="number" step="any" min="0" id="edit-op-sup" class="form-control rounded-3 text-center fw-bold text-warning" oninput="onEditOpSupChange()">
                    </div>
                </div>
                <div class="mb-3" id="edit-op-sell-row">
                    <label class="form-label small fw-bold text-primary">سعر البيع للعميل (الأساسي للوحدة)</label>
                    <input type="number" step="any" min="0" id="edit-op-sell" class="form-control rounded-3 text-center fw-bold text-primary" oninput="onEditOpSellChange()">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">المستفيد / الفني / الطرف الآخر (من قاعدة البيانات)</label>
                    <select id="edit-op-person" class="form-select rounded-3 fw-bold">
                        <option value="">-- اختر المستفيد --</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">ملاحظات</label>
                    <textarea id="edit-op-notes" class="form-control rounded-3" rows="2"></textarea>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="fa fa-info-circle me-1"></i>
                    تعديل هذه الحركة يقوم بإعادة احتساب مستحقات العميل وتحديث شاشة المستحقات (/debts) تلقائياً.
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="saveEditSingleOp()">
                    <i class="fa fa-floppy-disk me-1"></i>حفظ التعديل والمزامنة
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     FRONTEND JAVASCRIPT LOGIC
════════════════════════════════════════════════════════════════ -->
<script>
    // System Data & State
    let currentProjectId = null;
    let currentProjectName = '';
    let currentClientName = '';
    let currentSupervisionPct = 15.0;
    let currentItemId = null;
    let currentItemTitle = '';
    let currentOpType = 'material'; // material, technician, overhead, commission
    let cart = [];

    // All Project Items Data & Directory Lists from DB
    const allProjectItems = @json($items);
    const dbSuppliersList = @json($suppliers->pluck('name')->values());
    const dbTechsList = @json($technicians->pluck('name')->values());
    const dbAccountsList = @json($accounts->map(fn($a) => ['id' => $a->id, 'name' => $a->account_name ?? $a->name ?? 'خزينة', 'balance' => $a->balance])->values());

    window.allowSafeReload = false;
    function safeContractingReload() {
        window.allowSafeReload = true;
        window.location.reload();
    }

    // ── 1. POS Selection Handlers ──
    function selectProject(id, name, clientName, supervisionPct, element) {
        currentProjectId = Number(id);
        currentProjectName = name;
        currentClientName = clientName;
        currentSupervisionPct = parseFloat(supervisionPct) || 15.0;

        // UI active classes
        document.querySelectorAll('#projects-grid .pos-btn').forEach(btn => btn.classList.remove('active'));
        if (element) {
            element.classList.add('active');
        } else {
            const allBtns = document.querySelectorAll('#projects-grid .pos-btn');
            allBtns.forEach(btn => {
                const clickAttr = btn.getAttribute('onclick') || '';
                if (clickAttr.includes(`selectProject(${id},`)) {
                    btn.classList.add('active');
                }
            });
        }

        // Update Cart project info
        const cartProjInfo = document.getElementById('cart-project-info');
        if (cartProjInfo) {
            cartProjInfo.innerHTML = `<strong>المشروع:</strong> ${name} <span class="badge bg-warning text-dark ms-1">إشراف ${currentSupervisionPct}%</span>`;
        }

        // Synchronize with Project 360 select
        const sel360 = document.getElementById('select-project-360');
        if (sel360) sel360.value = id;

        // Populate Items for this project
        const itemsContainer = document.getElementById('items-grid');
        itemsContainer.innerHTML = '';
        const projectItems = allProjectItems.filter(i => Number(i.project_id) === Number(id));

        document.getElementById('step-items').style.display = 'block';

        // DO NOT pre-select any item! User explicitly requested everything starts empty until they choose
        currentItemId = null;
        currentItemTitle = null;
        const opForm = document.getElementById('step-op-form');
        if (opForm) opForm.style.display = 'none';

        const badge = document.getElementById('selected-item-badge');
        if (badge) badge.innerText = 'يرجى اختيار البند أولاً للبدء';

        if (projectItems.length > 0) {
            projectItems.forEach((item) => {
                const itemBtn = document.createElement('div');
                itemBtn.className = 'pos-btn';
                itemBtn.onclick = function() { selectItem(item.id, item.title, this); };
                itemBtn.innerHTML = `<i class="fa fa-layer-group mb-2"></i><span class="fw-bold fs-6 w-100 px-2" style="line-height: 1.35; word-break: break-word;">${item.title}</span>`;
                itemsContainer.appendChild(itemBtn);
            });
        } else {
            // No items configured yet for this project - provide general option spanning full grid width
            itemsContainer.innerHTML = `
                <div class="py-4 px-4 bg-light rounded-4 text-center border" style="grid-column: 1 / -1;">
                    <div class="mb-3 text-muted fw-bold">
                        <i class="fa fa-layer-group text-primary me-2 fs-5 align-middle"></i>لا توجد بنود مسجلة لهذا المشروع حتى الآن
                    </div>
                    <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                        <button type="button" class="btn btn-primary rounded-3 px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="selectItem(null, 'عام (توريد مباشر للمشروع)', this)">
                            <i class="fa fa-check-circle"></i>
                            <span>المتابعة بدون بند محدد</span>
                        </button>
                        <button type="button" class="btn btn-success rounded-3 px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="showQuickAddItem()">
                            <i class="fa fa-plus-circle"></i>
                            <span>إضافة بند جديد للمشروع</span>
                        </button>
                    </div>
                </div>
            `;
        }

        // Auto calculate hint & modals
        const supHint = document.getElementById('sup-calc-hint');
        if (supHint) supHint.innerText = `(+${currentSupervisionPct}% إشراف)`;
        const modalComm = document.getElementById('modal-comm-project-id');
        if (modalComm) modalComm.value = id;

        // Reset inputs silently
        resetOpFormForNewEntry(true);

        // Smoothly scroll to step 2 (Items) so the user chooses their item
        setTimeout(() => {
            const stepItems = document.getElementById('step-items');
            if (stepItems) {
                stepItems.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 100);
    }

    function hideAllOpSections() {
        const secMat = document.getElementById('section-material-invoice');
        const secTech = document.getElementById('section-technician');
        const secPetty = document.getElementById('section-petty');
        const secOvh = document.getElementById('section-overhead');
        const secComm = document.getElementById('section-commission');

        if (secMat) secMat.style.display = 'none';
        if (secTech) secTech.style.display = 'none';
        if (secPetty) secPetty.style.display = 'none';
        if (secOvh) secOvh.style.display = 'none';
        if (secComm) secComm.style.display = 'none';
    }

    function selectItem(id, title, element) {
        currentItemId = id;
        currentItemTitle = title || 'عام';
        currentSelectedItem = { id: id, title: currentItemTitle };

        document.querySelectorAll('#items-grid .pos-btn').forEach(btn => btn.classList.remove('active'));
        if (element && element.classList) element.classList.add('active');

        const badge = document.getElementById('selected-item-badge');
        if (badge) badge.innerText = currentItemTitle;

        const form = document.getElementById('step-op-form');
        if (form) form.style.display = 'block';

        // Clear active on operation type squares
        document.querySelectorAll('#op-types-grid .op-type-card').forEach(btn => btn.classList.remove('active'));

        // Hide all sub-sections initially until user chooses a square
        hideAllOpSections();

        // Show prompt asking user to pick a square
        const prompt = document.getElementById('op-prompt-select');
        if (prompt) {
            prompt.style.display = 'block';
            prompt.innerHTML = `
                <div class="mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                        <i class="fa fa-hand-pointer me-1 animate__animated animate__pulse animate__infinite"></i> الخطوة التالية: اختر نوع العملية
                    </span>
                </div>
                <p class="text-muted mb-0 fw-semibold">اضغط على أي من المربعات أعلاه (خامات، مصنعية، مصروف...) لفتح النموذج والتسجيل مباشرة على بند: <span class="text-primary fw-bold">"${currentItemTitle}"</span></p>
            `;
        }

        setTimeout(() => {
            if (form) {
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }, 100);
    }

    function showQuickAddItem() {
        if (!currentProjectId) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً قبل إضافة بند', 'warning');
            return;
        }

        Swal.fire({
            title: 'إضافة بند جديد للمشروع',
            input: 'text',
            inputLabel: 'اسم البند',
            inputPlaceholder: 'مثال: بند أعمال السباكة والصرف...',
            showCancelButton: true,
            confirmButtonText: 'إضافة',
            cancelButtonText: 'إلغاء',
            inputValidator: (value) => {
                if (!value) return 'اسم البند مطلوب!';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("contracting.item.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ project_id: currentProjectId, title: result.value })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        allProjectItems.push({ id: data.item_id, project_id: currentProjectId, title: data.title });
                        selectProject(currentProjectId, currentProjectName, currentClientName, currentSupervisionPct, null);
                        Swal.fire('تم!', 'تمت إضافة البند بنجاح', 'success');
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                });
            }
        });
    }

    // ── 2. Operation Type Switcher ──
    function setOpType(type, button) {
        currentOpType = type;
        document.querySelectorAll('#op-types-grid .op-type-card').forEach(btn => btn.classList.remove('active'));
        if (button && button.classList) {
            button.classList.add('active');
        } else {
            const card = document.querySelector(`#op-types-grid .op-type-card[data-op="${type}"]`);
            if (card) card.classList.add('active');
        }

        // Hide prompt
        const prompt = document.getElementById('op-prompt-select');
        if (prompt) prompt.style.display = 'none';

        // Hide all 5 sub-sections
        hideAllOpSections();

        // Show chosen section & focus first input
        let targetSec = null;
        let targetInput = null;
        if (type === 'material') {
            targetSec = document.getElementById('section-material-invoice');
            targetInput = document.getElementById('inv-supplier');
        } else if (type === 'technician') {
            targetSec = document.getElementById('section-technician');
            targetInput = document.getElementById('tech-person');
        } else if (type === 'petty') {
            targetSec = document.getElementById('section-petty');
            targetInput = document.getElementById('petty-title');
        } else if (type === 'overhead') {
            targetSec = document.getElementById('section-overhead');
            targetInput = document.getElementById('ovh-title');
        } else if (type === 'commission') {
            targetSec = document.getElementById('section-commission');
            targetInput = document.getElementById('comm-name');
        }

        if (targetSec) {
            targetSec.style.display = 'block';
            setTimeout(() => {
                targetSec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                if (targetInput) targetInput.focus();
            }, 100);
        }
    }

    // ── 3. Multi-Item Material Invoice Logic (فاتورة خامات مجمعة) ──
    let materialRowIndex = 0;

    function addMaterialRow(data = {}) {
        materialRowIndex++;
        const rowId = materialRowIndex;
        const tbody = document.getElementById('material-invoice-tbody');
        const tr = document.createElement('tr');
        tr.id = `mat-row-${rowId}`;
        tr.className = 'mat-invoice-row';

        const title = data.title || '';
        const qty = data.qty || 1;
        const unit = data.unit || 'عدد';
        const cost = data.cost || '';
        const defaultSup = (typeof currentSupervisionPct !== 'undefined' && currentSupervisionPct !== null) ? currentSupervisionPct : 15.0;
        const sup = data.sup !== undefined ? data.sup : defaultSup;
        let sell = data.sell || ''; // Leave selling price blank by default - user types it manually

        tr.innerHTML = `
            <td class="text-muted fw-bold row-num"></td>
            <td>
                <input type="text" class="form-control form-control-sm rounded-3 mat-title fw-bold" style="min-width: 220px;" list="list-materials" placeholder="اكتب اسم الخامة كاملة..." value="${title}" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rounded-3 mat-qty text-center" value="${qty}" min="0.01" step="any" oninput="calcMatRow(${rowId})">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rounded-3 mat-cost text-center" placeholder="0.00" step="any" value="${cost}" oninput="onMatCostChange(${rowId})">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rounded-3 mat-sup text-center fw-bold text-warning" placeholder="%" step="any" value="${sup}" oninput="onMatSupChange(${rowId})">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rounded-3 mat-sell text-center fw-bold text-primary" placeholder="0.00" step="any" value="${sell}" oninput="onMatSellChange(${rowId})">
            </td>
            <td class="fw-bold text-danger mat-total-cost">0.00 ج</td>
            <td class="fw-bold text-primary mat-total-sell">0.00 ج</td>
            <td>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeMaterialRow(${rowId})"><i class="fa fa-times"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        renumberMaterialRows();
        if (cost) calcMatRow(rowId);
    }

    function renumberMaterialRows() {
        const rows = document.querySelectorAll('#material-invoice-tbody tr');
        rows.forEach((row, idx) => {
            const numCell = row.querySelector('.row-num');
            if (numCell) numCell.innerText = idx + 1;
        });
    }

    function addMultipleMaterialRows(count) {
        for (let i = 0; i < count; i++) {
            addMaterialRow();
        }
    }

    function removeMaterialRow(rowId) {
        const row = document.getElementById(`mat-row-${rowId}`);
        if (row) {
            row.remove();
            renumberMaterialRows();
            calcInvoiceTotals();
        }
        const remaining = document.querySelectorAll('#material-invoice-tbody tr');
        if (remaining.length === 0) {
            addMaterialRow();
        }
    }

    function onMatCostChange(rowId) {
        calcMatRow(rowId);
    }

    function onMatSupChange(rowId) {
        calcMatRow(rowId);
    }

    function onMatSellChange(rowId) {
        const row = document.getElementById(`mat-row-${rowId}`);
        if (!row) return;
        const sellInput = row.querySelector('.mat-sell');
        if (sellInput) sellInput.dataset.userEdited = 'true';
        calcMatRow(rowId);
    }

    function calcMatRow(rowId) {
        const row = document.getElementById(`mat-row-${rowId}`);
        if (!row) return;
        const qty = parseFloat(row.querySelector('.mat-qty').value) || 0;
        const cost = parseFloat(row.querySelector('.mat-cost').value) || 0;
        const sup = parseFloat(row.querySelector('.mat-sup').value) || 0;
        const sell = parseFloat(row.querySelector('.mat-sell').value) || 0;

        const baseSell = sell > 0 ? sell : cost;
        const rowTotalCost = qty * cost;
        const rowBaseSell = qty * baseSell;
        const rowSupAmount = Math.round(rowBaseSell * (sup / 100) * 100) / 100;
        const rowTotalSell = rowBaseSell + rowSupAmount;

        row.querySelector('.mat-total-cost').innerText = rowTotalCost.toFixed(2) + ' ج';
        row.querySelector('.mat-total-sell').innerText = (baseSell > 0 ? rowTotalSell.toFixed(2) + ' ج' : '0.00 ج');

        calcInvoiceTotals();
    }

    function calcInvoiceTotals() {
        let grandCost = 0;
        let grandBaseSell = 0;
        let grandSupAmount = 0;
        let grandCustomerTotal = 0;

        document.querySelectorAll('#material-invoice-tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.mat-qty').value) || 0;
            const cost = parseFloat(row.querySelector('.mat-cost').value) || 0;
            const sup = parseFloat(row.querySelector('.mat-sup').value) || 0;
            const sell = parseFloat(row.querySelector('.mat-sell').value) || 0;

            const baseSell = sell > 0 ? sell : cost;
            const rowCost = qty * cost;
            const rowBaseSell = qty * baseSell;
            const rowSup = Math.round(rowBaseSell * (sup / 100) * 100) / 100;
            const rowCustomer = rowBaseSell + rowSup;

            grandCost += rowCost;
            grandBaseSell += rowBaseSell;
            grandSupAmount += rowSup;
            grandCustomerTotal += rowCustomer;
        });

        const grandProfit = grandCustomerTotal - grandCost;

        const costEl = document.getElementById('inv-total-cost');
        if (costEl) {
            costEl.innerText = grandCost.toFixed(2) + ' ج';
            costEl.dataset.val = grandCost;
        }

        const baseSellEl = document.getElementById('inv-base-selling');
        if (baseSellEl) baseSellEl.innerText = grandBaseSell.toFixed(2) + ' ج';

        const supValEl = document.getElementById('inv-supervision-val');
        if (supValEl) {
            supValEl.innerText = `+ ${grandSupAmount.toFixed(2)} ج`;
        }

        const sellEl = document.getElementById('inv-total-selling');
        if (sellEl) {
            sellEl.innerText = grandCustomerTotal.toFixed(2) + ' ج';
            sellEl.dataset.val = grandCustomerTotal;
        }

        const profitEl = document.getElementById('inv-total-profit');
        if (profitEl) profitEl.innerText = grandProfit.toFixed(2) + ' ج';

        // Invoice paid & debt defaults
        const payEl = document.getElementById('inv-paid');
        if (payEl) payEl.value = grandCost > 0 ? grandCost.toFixed(2) : '0.00';
        const debtEl = document.getElementById('inv-debt');
        if (debtEl) debtEl.value = '0.00';
    }

    function onInvoicePaymentMethodChange(method) {
        calcInvoiceTotals();
    }

    function calcInvoiceDebt() {
        const totalCost = parseFloat(document.getElementById('inv-total-cost')?.dataset?.val) || 0;
        const paid = parseFloat(document.getElementById('inv-paid')?.value) || 0;
        const debt = Math.max(0, totalCost - paid);
        const debtEl = document.getElementById('inv-debt');
        if (debtEl) debtEl.value = debt.toFixed(2);
    }

    function onSupplierChange(val) {
        const invRef = document.getElementById('inv-ref');
        const v = val.trim();
        if (!invRef.dataset.manualEdited || invRef.value.trim() === '') {
            const d = new Date();
            const dateStr = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            invRef.value = v ? `فاتورة خامات - ${v} - ${dateStr}` : `فاتورة خامات ${dateStr}`;
        }
    }

    function appendToDatalist(listId, val) {
        if (!val) return;
        const dl = document.getElementById(listId);
        if (!dl) return;
        const exists = Array.from(dl.options).some(o => o.value === val);
        if (!exists) {
            const opt = document.createElement('option');
            opt.value = val;
            dl.appendChild(opt);
        }
    }

    function validateCashAccount(paymentMethod, paidAmount, accountId, selectElementId) {
        if ((paymentMethod === 'cash' || paymentMethod === 'bank' || paidAmount > 0) && !accountId) {
            Swal.fire({
                title: 'تنبيه: تحديد الخزينة إلزامي',
                text: 'طالما يوجد سداد نقدي أو بنكي، يجب اختيار الحساب البنكي أو الخزينة للسحب منها!',
                icon: 'warning',
                confirmButtonText: 'حسناً، سأختار الخزينة'
            });
            const sel = document.getElementById(selectElementId);
            if (sel) {
                sel.classList.add('is-invalid');
                sel.focus();
            }
            return false;
        }
        return true;
    }

    function resetOpFormForNewEntry(silent = false) {
        if (!currentProjectId && !silent) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً من الخطوة 1', 'warning');
            return;
        }
        // Reset Material Invoice fields
        const invSup = document.getElementById('inv-supplier');
        if (invSup) invSup.value = '';
        const invRef = document.getElementById('inv-ref');
        if (invRef) {
            delete invRef.dataset.manualEdited;
            invRef.value = '';
            if (typeof onSupplierChange === 'function') onSupplierChange('');
        }
        const tbody = document.getElementById('material-invoice-tbody');
        if (tbody) {
            tbody.innerHTML = '';
            addMaterialRow();
        }
        const invPay = document.getElementById('inv-payment-method'); if (invPay) invPay.value = 'cash';
        const invPaid = document.getElementById('inv-paid'); if (invPaid) invPaid.value = '';
        const invDebt = document.getElementById('inv-debt'); if (invDebt) invDebt.value = '0.00';
        if (typeof calcInvoiceTotals === 'function') calcInvoiceTotals();

        // Reset Tech fields
        const tp = document.getElementById('tech-person'); if (tp) tp.value = '';
        const tt = document.getElementById('tech-title'); if (tt) tt.value = '';
        const tc = document.getElementById('tech-cost'); if (tc) tc.value = '';
        const tsup = document.getElementById('tech-sup'); if (tsup) tsup.value = (typeof currentSupervisionPct !== 'undefined' && currentSupervisionPct !== null) ? currentSupervisionPct : 15.0;
        const ts = document.getElementById('tech-sell'); if (ts) ts.value = '';
        const tpaid = document.getElementById('tech-paid'); if (tpaid) tpaid.value = '';
        const tdebt = document.getElementById('tech-debt'); if (tdebt) tdebt.value = '0.00';
        if (typeof calcTechLive === 'function') calcTechLive();

        // Reset Petty fields
        const ptitle = document.getElementById('petty-title'); if (ptitle) ptitle.value = '';
        const pcost = document.getElementById('petty-cost'); if (pcost) pcost.value = '';
        const psup = document.getElementById('petty-sup'); if (psup) psup.value = (typeof currentSupervisionPct !== 'undefined' && currentSupervisionPct !== null) ? currentSupervisionPct : 15.0;
        const psell = document.getElementById('petty-sell'); if (psell) psell.value = '';
        if (typeof calcPettyLive === 'function') calcPettyLive();

        // Reset Overhead fields
        const ot = document.getElementById('ovh-title'); if (ot) ot.value = '';
        const oa = document.getElementById('ovh-amount'); if (oa) oa.value = '';

        // Reset Commission fields
        const cn = document.getElementById('comm-name'); if (cn) cn.value = '';
        const cv = document.getElementById('comm-val'); if (cv) cv.value = '';

        // Reset squares active state
        document.querySelectorAll('#op-types-grid .op-type-card').forEach(btn => btn.classList.remove('active'));
        hideAllOpSections();

        // Show prompt to pick operation type
        const prompt = document.getElementById('op-prompt-select');
        if (prompt) {
            prompt.style.display = 'block';
            prompt.innerHTML = `
                <div class="mb-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                        <i class="fa fa-rotate-left me-1"></i> جاهز لحركة جديدة على: ${currentItemTitle || 'المشروع'}
                    </span>
                </div>
                <p class="text-muted mb-0 fw-semibold">اختر نوع العملية من المربعات أعلاه للبدء في التسجيل</p>
            `;
        }

        if (!silent) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `جاهز لحركة جديدة على (${currentItemTitle || 'المشروع'})`,
                showConfirmButton: false,
                timer: 2000
            });

            const grid = document.getElementById('op-types-grid');
            if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // ── 4. Adding to Operations Box (صندوق العمليات) Logic ──
    function addInvoiceToCart() {
        if (!currentProjectId) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً من الخطوة 1', 'warning');
            return;
        }
        const supplierName = document.getElementById('inv-supplier').value.trim();
        if (!supplierName) {
            Swal.fire('تنبيه', 'يرجى إدخال اسم المورد لحفظ الخامات في صندوق العمليات', 'warning');
            document.getElementById('inv-supplier').focus();
            return;
        }

        const rows = document.querySelectorAll('#material-invoice-tbody tr');
        const items = [];
        let grandCost = 0;
        let grandBaseSell = 0;

        rows.forEach(row => {
            const title = row.querySelector('.mat-title').value.trim();
            const qty = parseFloat(row.querySelector('.mat-qty').value) || 1;
            const unit = 'عدد';
            const cost = parseFloat(row.querySelector('.mat-cost').value) || 0;
            const sup = parseFloat(row.querySelector('.mat-sup').value) || 0;
            const rawSell = parseFloat(row.querySelector('.mat-sell').value) || 0;
            const sell = rawSell > 0 ? rawSell : cost;
            const totalCost = qty * cost;
            const totalBaseSell = qty * sell;
            const supAmount = Math.round(totalBaseSell * (sup / 100) * 100) / 100;
            const totalCustomer = totalBaseSell + supAmount;
            if (title) {
                items.push({
                    title, qty, unit, cost, sup, sell,
                    total_cost: totalCost,
                    total_selling_base: totalBaseSell,
                    supervision_amount: supAmount,
                    total_customer: totalCustomer
                });
                grandCost += totalCost;
                grandBaseSell += totalBaseSell;
            }
        });

        if (items.length === 0) {
            Swal.fire('تنبيه', 'يرجى كتابة اسم خامة واحدة على الأقل لإضافتها لصندوق العمليات', 'warning');
            return;
        }

        const invoiceRef = document.getElementById('inv-ref').value.trim() || ('فاتورة خامات ' + new Date().toISOString().slice(0, 10));
        const paymentMethod = 'later';
        const paidAmount = 0;
        const remainingDebt = grandCost;
        const accountId = null;
        const accountName = '-';

        items.forEach(it => {
            const itPaid = 0;
            const itDebt = it.total_cost;

            cart.push({
                cart_id: 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
                project_id: currentProjectId,
                project_name: currentProjectName,
                client_name: currentClientName,
                item_id: currentItemId,
                item_title: currentItemTitle || 'عام',
                op_type: 'material',
                name_or_title: it.title,
                person_name: supplierName,
                quantity: it.qty,
                unit: it.unit,
                cost_price: it.cost,
                supervision_pct: it.sup,
                selling_price: it.sell,
                total_cost: it.total_cost,
                total_selling_base: it.total_selling_base,
                supervision_amount: it.supervision_amount,
                total_customer: it.total_customer,
                profit_amount: it.total_customer - it.total_cost,
                payment_method: paymentMethod,
                paid_amount: itPaid,
                remaining_debt: itDebt,
                account_id: accountId,
                account_name: accountName,
                invoice_ref: invoiceRef,
                is_billable: 1,
                notes: ''
            });
        });

        items.forEach(it => {
            appendToDatalist('list-materials', it.title);
        });

        renderCart();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `تمت إضافة (${items.length}) خامة إلى صندوق العمليات`,
            showConfirmButton: false,
            timer: 2500
        });

        // Reset rows
        const tbody = document.getElementById('material-invoice-tbody');
        if (tbody) {
            tbody.innerHTML = '';
            addMaterialRow();
            addMaterialRow();
            calcInvoiceTotals();
        }
    }

    // ── 5. Technician Operations (مصنعية فني / صنايعي) ──
    function onTechCostChange(val) {
        calcTechLive();
    }

    function onTechSupChange(val) {
        calcTechLive();
    }

    function onTechSellChange(val) {
        calcTechLive();
    }

    function onTechWorkTypeChange(type) {
        const qtyInput = document.getElementById('tech-qty');
        const lblQty = document.getElementById('lbl-tech-qty');
        const lblCost = document.getElementById('lbl-tech-cost');
        const lblSell = document.getElementById('lbl-tech-sell');

        if (type === 'meter') {
            if (lblQty) lblQty.innerText = 'عدد الأمتار';
            if (lblCost) lblCost.innerHTML = 'سعر المتر (تكلفة) <span class="text-danger">*</span>';
            if (lblSell) lblSell.innerText = 'سعر بيع المتر للعميل';
        } else if (type === 'day') {
            if (lblQty) lblQty.innerText = 'عدد الأيام';
            if (lblCost) lblCost.innerHTML = 'أجر اليومية (تكلفة) <span class="text-danger">*</span>';
            if (lblSell) lblSell.innerText = 'يومية الفني للعميل';
        } else if (type === 'piece') {
            if (lblQty) lblQty.innerText = 'عدد القطع';
            if (lblCost) lblCost.innerHTML = 'سعر القطعة (تكلفة) <span class="text-danger">*</span>';
            if (lblSell) lblSell.innerText = 'سعر بيع القطعة للعميل';
        } else {
            // lump_sum
            if (lblQty) lblQty.innerText = 'الكمية';
            if (qtyInput) qtyInput.value = 1;
            if (lblCost) lblCost.innerHTML = 'أجر المقاولة (تكلفة) <span class="text-danger">*</span>';
            if (lblSell) lblSell.innerText = 'سعر المقاولة للعميل';
        }
        calcTechLive();
    }

    function calcTechLive() {
        const qty = parseFloat(document.getElementById('tech-qty').value) || 1;
        const cost = parseFloat(document.getElementById('tech-cost').value) || 0;
        const supEl = document.getElementById('tech-sup');
        const sup = (supEl && supEl.value !== '') ? (parseFloat(supEl.value) || 0) : 0;
        const sellVal = document.getElementById('tech-sell').value.trim();
        const baseSell = sellVal !== '' ? (parseFloat(sellVal) || 0) : (cost > 0 ? cost : 0);

        const totalCost = qty * cost;
        const totalBaseSell = qty * baseSell;
        const supAmount = Math.round(totalBaseSell * (sup / 100) * 100) / 100;
        const totalCustomer = totalBaseSell + supAmount;
        const profit = totalCustomer - totalCost;

        const costEl = document.getElementById('tech-live-cost');
        if (costEl) costEl.innerText = totalCost.toFixed(2) + ' ج';
        const sellEl = document.getElementById('tech-live-sell');
        if (sellEl) sellEl.innerText = totalBaseSell.toFixed(2) + ' ج';
        const liveSupEl = document.getElementById('tech-live-sup');
        if (liveSupEl) liveSupEl.innerText = `+ ${supAmount.toFixed(2)} ج`;
        const custEl = document.getElementById('tech-live-customer');
        if (custEl) custEl.innerText = totalCustomer.toFixed(2) + ' ج';
        const profitEl = document.getElementById('tech-live-profit');
        if (profitEl) profitEl.innerText = profit.toFixed(2) + ' ج';
    }

    function addTechToCart() {
        if (!currentProjectId) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً', 'warning');
            return;
        }
        const person = document.getElementById('tech-person').value.trim();
        let title = document.getElementById('tech-title').value.trim();
        const cost = parseFloat(document.getElementById('tech-cost').value) || 0;
        const qty = parseFloat(document.getElementById('tech-qty').value) || 1;
        const unit = 'مقطوع';
        const supEl = document.getElementById('tech-sup');
        const sup = (supEl && supEl.value !== '') ? (parseFloat(supEl.value) || 0) : (currentSupervisionPct || 15.0);
        const sellVal = document.getElementById('tech-sell').value.trim();
        const sell = sellVal !== '' ? (parseFloat(sellVal) || 0) : (cost > 0 ? cost : 0);

        if (!person) {
            Swal.fire('تنبيه', 'يرجى اختيار اسم الفني / الصنايعي من القائمة أولاً', 'warning');
            document.getElementById('tech-person')?.focus();
            return;
        }
        if (cost <= 0) {
            Swal.fire('تنبيه', 'يرجى إدخال أجر الفني (التكلفة عليك)', 'warning');
            document.getElementById('tech-cost')?.focus();
            return;
        }
        if (!title) {
            title = `مصنعية ${currentItemTitle || 'أعمال'}`;
        }

        const totalCost = qty * cost;
        const totalBaseSell = qty * sell;
        const supAmount = Math.round(totalBaseSell * (sup / 100) * 100) / 100;
        const totalCust = totalBaseSell + supAmount;

        cart.push({
            cart_id: 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            project_id: currentProjectId,
            project_name: currentProjectName,
            client_name: currentClientName,
            item_id: currentItemId,
            item_title: currentItemTitle || 'عام',
            op_type: 'technician',
            name_or_title: title,
            person_name: person,
            quantity: qty,
            unit: unit,
            cost_price: cost,
            supervision_pct: sup,
            selling_price: sell,
            total_cost: totalCost,
            total_selling_base: totalBaseSell,
            supervision_amount: supAmount,
            total_customer: totalCust,
            profit_amount: totalCust - totalCost,
            payment_method: 'later',
            paid_amount: 0,
            remaining_debt: totalCost,
            account_id: null,
            account_name: '-',
            invoice_ref: `مصنعية - ${person}`,
            is_billable: 1,
            notes: ''
        });

        renderCart();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `تمت إضافة مصنعية (${person}) إلى صندوق العمليات (آجل)`,
            showConfirmButton: false,
            timer: 2500
        });

        document.getElementById('tech-person').value = '';
        document.getElementById('tech-title').value = '';
        document.getElementById('tech-cost').value = '';
        document.getElementById('tech-sell').value = '';
        document.getElementById('tech-qty').value = '1';
        calcTechLive();
    }

    // ── 6. Internal Overhead (مصروف داخلي) ──
    function onOvhPayMethodChange(method) {
        const badge = document.getElementById('ovh-acc-required');
        if (badge) {
            badge.style.display = (method === 'cash' || method === 'bank') ? 'inline' : 'none';
        }
        const acc = document.getElementById('ovh-account');
        if ((method === 'cash' || method === 'bank') && acc && !acc.value && acc.options.length > 1) {
            acc.selectedIndex = 1;
        }
    }

    function addOverheadToCart() {
        if (!currentProjectId) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً', 'warning');
            return;
        }
        const title = document.getElementById('ovh-title').value.trim();
        const amount = parseFloat(document.getElementById('ovh-amount').value) || 0;
        const accEl = document.getElementById('ovh-account');
        const account = accEl ? accEl.value : '';
        const accountName = (accEl && accEl.selectedIndex >= 0 && account) ? accEl.options[accEl.selectedIndex].text.split('(')[0].trim() : '-';
        const method = 'cash';

        if (!title || amount <= 0) {
            Swal.fire('تنبيه', 'يرجى إدخال بيان وقيمة المصروف الداخلي', 'warning');
            return;
        }
        if (!account) {
            Swal.fire('اختيار الخزينة مطلوب', 'يرجى اختيار الخزينة أو الحساب المالي الذي سيتم صرف المصروف منه', 'warning');
            if (accEl) accEl.focus();
            return;
        }

        cart.push({
            cart_id: 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            project_id: currentProjectId,
            project_name: currentProjectName,
            client_name: currentClientName,
            item_id: currentItemId,
            item_title: currentItemTitle || 'عام',
            op_type: 'overhead',
            name_or_title: title,
            person_name: 'مصروف موقع / إدارة',
            quantity: 1,
            unit: 'عدد',
            cost_price: amount,
            selling_price: 0,
            total_cost: amount,
            total_selling_base: 0,
            supervision_pct: 0,
            supervision_amount: 0,
            total_customer: 0,
            profit_amount: -amount,
            payment_method: method,
            paid_amount: amount,
            remaining_debt: 0,
            account_id: account,
            account_name: accountName,
            invoice_ref: `مصروف داخلي`,
            is_billable: 0,
            notes: ''
        });

        appendToDatalist('list-expense-titles', title);
        renderCart();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `تمت إضافة المصروف الداخلي (يُصرف من: ${accountName})`,
            showConfirmButton: false,
            timer: 2500
        });

        document.getElementById('ovh-title').value = '';
        document.getElementById('ovh-amount').value = '';
    }

    // ── 6.5. Petty Incidental Expenses Billable to Client (مصروف نثري يتحمله العميل) ──
    function onPettyCostChange(val) {
        // Do not touch petty-sell - user types selling price manually
        calcPettyLive();
    }

    function onPettySupChange(val) {
        // Do not overwrite petty-sell - user types selling price manually
        calcPettyLive();
    }

    function onPettySellChange(val) {
        calcPettyLive();
    }

    function calcPettyLive() {
        const cost = parseFloat(document.getElementById('petty-cost').value) || 0;
        const sup = parseFloat(document.getElementById('petty-sup')?.value) || 0;
        const rawSell = parseFloat(document.getElementById('petty-sell').value) || 0;
        const baseSell = rawSell > 0 ? rawSell : cost;
        const supAmount = Math.round(baseSell * (sup / 100) * 100) / 100;
        const totalCustomer = baseSell + supAmount;
        const profit = totalCustomer - cost;
        const profitEl = document.getElementById('petty-live-profit');
        if (profitEl) profitEl.innerText = profit.toFixed(2) + ' ج';
    }

    function onPettyPayMethodChange(method) {
        const reqBadge = document.getElementById('petty-acc-required');
        if (reqBadge) reqBadge.style.display = (method === 'cash' || method === 'bank') ? 'inline' : 'none';
    }

    function addPettyToCart() {
        if (!currentProjectId) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً من الخطوة 1', 'warning');
            return;
        }
        if (!currentItemId && !currentSelectedItem) {
            Swal.fire('تنبيه', 'يرجى اختيار بند العمل من الخطوة 2 قبل إضافة المصروف النثري', 'warning');
            return;
        }
        const title = document.getElementById('petty-title').value.trim();
        const cost = parseFloat(document.getElementById('petty-cost').value) || 0;
        const supPct = parseFloat(document.getElementById('petty-sup').value) || 0;
        const sellVal = parseFloat(document.getElementById('petty-sell').value);
        const baseSell = !isNaN(sellVal) && sellVal > 0 ? sellVal : cost;
        const supAmount = Math.round(baseSell * (supPct / 100) * 100) / 100;
        const totalCustomer = baseSell + supAmount;
        const accEl = document.getElementById('petty-account');
        const accountId = accEl ? accEl.value : '';
        const accountName = (accEl && accEl.selectedIndex >= 0 && accountId) ? accEl.options[accEl.selectedIndex].text.split('(')[0].trim() : '-';
        const payMethod = 'cash';

        if (!title) {
            Swal.fire('بيان مطلوب', 'يرجى كتابة بيان المصروف النثري (مثل: ونش، مشال، إكراميات...)', 'warning');
            return;
        }
        if (cost <= 0) {
            Swal.fire('مبلغ مطلوب', 'يرجى كتابة تكلفة المصروف النثري', 'warning');
            return;
        }
        if (!accountId) {
            Swal.fire('اختيار الخزينة مطلوب', 'يرجى اختيار الخزينة أو الحساب المالي الذي سيتم صرف النثريات منه', 'warning');
            if (accEl) accEl.focus();
            return;
        }

        const paid = cost;
        const debt = 0;

        cart.push({
            cart_id: 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            project_id: currentProjectId,
            project_name: currentProjectName,
            client_name: currentClientName,
            item_id: currentItemId,
            item_title: currentItemTitle || 'عام',
            op_type: 'petty',
            name_or_title: title,
            person_name: 'نثريات موقع',
            quantity: 1,
            unit: 'مقطوع',
            cost_price: cost,
            supervision_pct: supPct,
            selling_price: baseSell,
            total_cost: cost,
            total_selling_base: baseSell,
            supervision_amount: supAmount,
            total_customer: totalCustomer,
            profit_amount: totalCustomer - cost,
            payment_method: payMethod,
            paid_amount: paid,
            remaining_debt: debt,
            account_id: accountId,
            account_name: accountName,
            is_billable: 1,
            notes: 'مصروف نثري يتحمله العميل'
        });

        appendToDatalist('list-petty-titles', title);
        renderCart();

        // Reset form
        document.getElementById('petty-title').value = '';
        document.getElementById('petty-cost').value = '';
        document.getElementById('petty-sup').value = currentSupervisionPct || 15.0;
        document.getElementById('petty-sell').value = '';
        calcPettyLive();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `تمت إضافة "${title}" (يُصرف من: ${accountName} - ومحمل على العميل)`,
            showConfirmButton: false,
            timer: 2500
        });
    }

    // ── 7. Marketer Commission (عمولة مسوق على المشروع ككل) ──
    function onCommPayMethodChange(method) {
        const badge = document.getElementById('comm-acc-required');
        if (badge) {
            badge.style.display = (method === 'cash' || method === 'bank') ? 'inline' : 'none';
        }
    }

    function addCommissionToCart() {
        if (!currentProjectId) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً', 'warning');
            return;
        }
        const name = document.getElementById('comm-name').value.trim();
        const val = parseFloat(document.getElementById('comm-val').value) || 0;
        const method = 'later';
        const acc = null;
        const accountName = '-';
        const note = document.getElementById('comm-note').value.trim();

        if (!name || val <= 0) {
            Swal.fire('تنبيه', 'يرجى إدخال اسم المسوق ومبلغ العمولة', 'warning');
            return;
        }

        const paid = 0;
        const debt = val;

        cart.push({
            cart_id: 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            project_id: currentProjectId,
            project_name: currentProjectName,
            client_name: currentClientName,
            item_id: null,
            item_title: 'المشروع ككل',
            op_type: 'commission',
            name_or_title: `عمولة مسوق - ${name}`,
            person_name: name,
            quantity: 1,
            unit: 'عدد',
            cost_price: val,
            selling_price: 0,
            total_cost: val,
            total_selling_base: 0,
            supervision_pct: 0,
            supervision_amount: 0,
            total_customer: 0,
            profit_amount: -val,
            payment_method: method,
            paid_amount: paid,
            remaining_debt: debt,
            account_id: acc,
            account_name: accountName,
            invoice_ref: `عمولة مسوق - ${name}`,
            is_billable: 0,
            notes: note
        });

        appendToDatalist('list-marketers', name);
        renderCart();

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `تمت إضافة عمولة المسوق (${name}) إلى صندوق العمليات`,
            showConfirmButton: false,
            timer: 2500
        });

        document.getElementById('comm-name').value = '';
        document.getElementById('comm-val').value = '';
        document.getElementById('comm-note').value = '';
    }

    function openCommissionModal() {
        new bootstrap.Modal(document.getElementById('modalProjectCommission')).show();
    }

    function submitProjectCommission(e) {
        e.preventDefault();
        const projectId = document.getElementById('modal-comm-project-id').value;
        if (!projectId) {
            Swal.fire('تنبيه', 'يرجى اختيار المشروع أولاً', 'warning');
            return;
        }
        const name = document.getElementById('modal-comm-name').value.trim();
        const val = parseFloat(document.getElementById('modal-comm-val').value) || 0;
        const method = document.getElementById('modal-comm-method').value;
        const acc = document.getElementById('modal-comm-acc').value || null;
        const note = document.getElementById('modal-comm-note').value.trim();

        if (!name || val <= 0) {
            Swal.fire('تنبيه', 'يرجى إدخال اسم المسوق وقيمة العمولة', 'warning');
            return;
        }

        const paid = (method === 'cash' || method === 'bank') ? val : 0;
        const debt = (method === 'later') ? val : 0;

        if (!validateCashAccount(method, paid, acc, 'modal-comm-acc')) {
            return;
        }

        fetch('{{ route("contracting.operation.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                project_id: projectId,
                items: [{
                    item_id: null,
                    op_type: 'commission',
                    is_billable: 0,
                    name_or_title: 'عمولة تسويقية - ' + name,
                    person_name: name,
                    cost_price: val,
                    quantity: 1,
                    selling_price: 0,
                    payment_method: method,
                    paid_amount: paid,
                    remaining_debt: debt,
                    account_id: acc,
                    notes: note
                }]
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                appendToDatalist('list-marketers', name);
                bootstrap.Modal.getInstance(document.getElementById('modalProjectCommission')).hide();
                Swal.fire('تم بنجاح', data.message, 'success').then(() => loadProject360(projectId));
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    // ── 8. Operations Box (صندوق العمليات) Management Logic ──
    function clearCart() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'تفريغ صندوق العمليات',
            text: 'هل أنت متأكد من تفريغ كافة بنود صندوق العمليات الحالي؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، تفريغ',
            cancelButtonText: 'إلغاء'
        }).then(res => {
            if (res.isConfirmed) {
                cart = [];
                renderCart();
                if (typeof window.clearPosDraftStorage === 'function') window.clearPosDraftStorage();
            }
        });
    }

    function removeCartItem(cartId) {
        cart = cart.filter(c => c.cart_id !== cartId);
        renderCart();
    }

    function renderCart() {
        if (typeof window.savePosDraftToStorage === 'function') window.savePosDraftToStorage();
        const container = document.getElementById('cart-items-container');
        const badge = document.getElementById('cart-count-badge');
        if (badge) badge.innerText = `${cart.length} عمليات`;

        if (!container) return;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fa fa-box-open fs-2 mb-2 text-secondary"></i>
                    <div class="fw-bold">صندوق العمليات فارغ حالياً</div>
                    <small class="text-muted">أضف خامات أو مصنعيات أو نثريات لتتجمع هنا وتراجعها معاً قبل الحفظ</small>
                </div>
            `;
            updateCartTotals(0, 0, 0, 0, 0);
            return;
        }

        let html = '';
        let totalCost = 0;
        let totalBaseSell = 0;
        let totalSup = 0;
        let totalCustomer = 0;

        cart.forEach(it => {
            totalCost += (it.total_cost || 0);
            totalBaseSell += (it.total_selling_base || 0);
            totalSup += (it.supervision_amount || 0);
            totalCustomer += (it.total_customer || 0);

            let typeBadge = '<span class="badge bg-primary">خامة</span>';
            if (it.op_type === 'technician') typeBadge = '<span class="badge bg-warning text-dark">مصنعية فني</span>';
            else if (it.op_type === 'petty') typeBadge = '<span class="badge bg-info text-white">نثريات للعميل</span>';
            else if (it.op_type === 'overhead') typeBadge = '<span class="badge bg-danger">مصروف داخلي</span>';
            else if (it.op_type === 'commission') typeBadge = '<span class="badge bg-success">عمولة مسوق</span>';

            let payBadge = '<span class="badge bg-light text-dark border">نقداً</span>';
            if (it.payment_method === 'later') payBadge = '<span class="badge bg-danger">آجل (دين)</span>';
            else if (it.payment_method === 'bank') payBadge = '<span class="badge bg-info text-dark">بنك / محفظة</span>';
            else if (it.payment_method === 'partial') payBadge = '<span class="badge bg-warning text-dark">جزئي</span>';

            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            ${typeBadge}
                            <strong class="text-dark ms-1">${it.name_or_title}</strong>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 border-0" onclick="removeCartItem('${it.cart_id}')" title="حذف من صندوق العمليات">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span><i class="fa fa-layer-group me-1"></i>${it.item_title}</span>
                        <span><i class="fa fa-user me-1"></i>${it.person_name || '-'}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small pt-1 border-top">
                        <span class="text-danger fw-bold">تكلفة: ${Number(it.total_cost).toLocaleString()} ج</span>
                        <span class="text-primary fw-bold">${it.is_billable ? 'للعميل: ' + Number(it.total_customer).toLocaleString() + ' ج' : '<span class="badge bg-secondary">غير محمل للعميل</span>'}</span>
                    </div>
                    <div class="small mt-1 text-end">
                        ${payBadge}
                        ${it.paid_amount > 0 && it.account_name && it.account_name !== '-' ? `<small class="text-muted ms-1">(${it.account_name})</small>` : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        const netProfit = totalCustomer - totalCost;
        updateCartTotals(totalCost, totalBaseSell, totalSup, totalCustomer, netProfit);
    }

    function updateCartTotals(cost, baseSell, sup, customer, profit) {
        const costEl = document.getElementById('cart-total-cost');
        if (costEl) costEl.innerText = Number(cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const baseSellEl = document.getElementById('cart-base-selling');
        if (baseSellEl) baseSellEl.innerText = Number(baseSell).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const supEl = document.getElementById('cart-supervision-val');
        if (supEl) supEl.innerText = '+ ' + Number(sup).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const sellEl = document.getElementById('cart-total-selling');
        if (sellEl) sellEl.innerText = Number(customer).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const profitEl = document.getElementById('cart-total-profit');
        if (profitEl) profitEl.innerText = Number(profit).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';
    }

    // ── 9. Operations Box (صندوق العمليات) Review & Confirmation Modal Logic ──
    let reviewStagingItems = [];

    function openReviewModal() {
        if (cart.length === 0) {
            Swal.fire('تنبيه', 'صندوق العمليات فارغ حالياً. قم بإضافة خامات أو مصنعيات للصندوق أولاً للمراجعة والاعتماد.', 'info');
            return;
        }
        reviewStagingItems = JSON.parse(JSON.stringify(cart));
        renderReviewModalContent();
        new bootstrap.Modal(document.getElementById('modalReviewOperations')).show();
    }

    function renderReviewModalContent() {
        const subtitleEl = document.getElementById('review-modal-subtitle');
        if (subtitleEl) {
            subtitleEl.innerText = `المشروع: ${currentProjectName || '-'} | العميل: ${currentClientName || '-'}`;
        }

        const tbody = document.querySelector('#table-review-items tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        let totalCost = 0;
        let totalBaseSell = 0;
        let totalSup = 0;
        let totalCustomer = 0;
        let initialPaid = 0;
        let initialDebt = 0;
        let initialMethod = 'cash';
        let initialAccountId = '';

        if (reviewStagingItems.length > 0) {
            initialMethod = reviewStagingItems[0].payment_method || 'cash';
            initialAccountId = reviewStagingItems[0].account_id || '';
            initialPaid = reviewStagingItems.reduce((acc, it) => acc + (parseFloat(it.paid_amount) || 0), 0);
            initialDebt = reviewStagingItems.reduce((acc, it) => acc + (parseFloat(it.remaining_debt) || 0), 0);
        }

        reviewStagingItems.forEach((it, idx) => {
            const qty = parseFloat(it.quantity) || 1;
            const cost = parseFloat(it.cost_price) || 0;
            const sell = parseFloat(it.selling_price) || 0;
            const isBillable = it.is_billable !== 0 && it.op_type !== 'overhead' && it.op_type !== 'commission';
            const supPct = it.supervision_pct || currentSupervisionPct || 15.0;

            it.total_cost = qty * cost;
            it.total_selling_base = isBillable ? (qty * sell) : 0;
            it.supervision_amount = isBillable ? Math.round(it.total_selling_base * (supPct / 100) * 100) / 100 : 0;
            it.total_customer = isBillable ? (it.total_selling_base + it.supervision_amount) : 0;
            it.profit_amount = it.total_customer - it.total_cost;

            totalCost += it.total_cost;
            totalBaseSell += it.total_selling_base;
            totalSup += it.supervision_amount;
            totalCustomer += it.total_customer;

            let typeBadge = '<span class="badge bg-primary">خامة</span>';
            if (it.op_type === 'technician') typeBadge = '<span class="badge bg-warning text-dark">مصنعية فني</span>';
            else if (it.op_type === 'petty') typeBadge = '<span class="badge bg-info text-white">نثريات للعميل</span>';
            else if (it.op_type === 'overhead') typeBadge = '<span class="badge bg-danger">مصروف داخلي</span>';
            else if (it.op_type === 'commission') typeBadge = '<span class="badge bg-success">عمولة مسوق</span>';

            const sellInputHtml = isBillable
                ? `<input type="number" step="any" class="form-control form-control-sm rounded-2 text-center text-primary fw-bold" style="width: 100px; display: inline-block;" value="${sell}" oninput="onReviewItemFieldChange(${idx}, 'selling_price', this.value, this)">`
                : `<span class="badge bg-secondary">غير محمل</span>`;

            let personInputHtml = '';
            if (it.op_type === 'material') {
                const opts = dbSuppliersList.map(s => `<option value="${s.replace(/"/g, '&quot;')}" ${it.person_name === s ? 'selected' : ''}>${s}</option>`).join('');
                personInputHtml = `<select class="form-select form-select-sm rounded-2 fw-bold" onchange="onReviewItemFieldChange(${idx}, 'person_name', this.value, this)">${opts}</select>`;
            } else if (it.op_type === 'technician') {
                const opts = dbTechsList.map(t => `<option value="${t.replace(/"/g, '&quot;')}" ${it.person_name === t ? 'selected' : ''}>${t}</option>`).join('');
                personInputHtml = `<select class="form-select form-select-sm rounded-2 fw-bold" onchange="onReviewItemFieldChange(${idx}, 'person_name', this.value, this)">${opts}</select>`;
            } else if (it.op_type === 'petty' || it.op_type === 'overhead') {
                if (!it.account_id && dbAccountsList.length > 0) {
                    it.account_id = dbAccountsList[0].id;
                    it.account_name = dbAccountsList[0].name;
                }
                it.payment_method = 'cash';
                it.paid_amount = it.total_cost;
                it.remaining_debt = 0;
                const accOpts = dbAccountsList.map(a => `<option value="${a.id}" ${String(it.account_id) === String(a.id) ? 'selected' : ''}>🏦 ${a.name}</option>`).join('');
                personInputHtml = `<select class="form-select form-select-sm rounded-2 fw-bold border-dark text-dark" onchange="onReviewItemFieldChange(${idx}, 'account_id', this.value, this)" title="الخزينة التي يُصرف منها المصروف">${accOpts}</select>`;
            } else {
                personInputHtml = `<span class="small fw-bold text-muted">${it.person_name || '-'}</span>`;
            }

            tbody.innerHTML += `
                <tr id="rev-row-${idx}">
                    <td class="small fw-bold text-muted">${idx + 1}</td>
                    <td>${typeBadge}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm rounded-2" value="${(it.name_or_title || '').replace(/"/g, '&quot;')}" oninput="onReviewItemFieldChange(${idx}, 'name_or_title', this.value, this)">
                    </td>
                    <td>
                        ${personInputHtml}
                    </td>
                    <td>
                        <input type="number" step="any" class="form-control form-control-sm rounded-2 text-center" style="width: 75px; display: inline-block;" value="${qty}" oninput="onReviewItemFieldChange(${idx}, 'quantity', this.value, this)">
                    </td>
                    <td>
                        <input type="number" step="any" class="form-control form-control-sm rounded-2 text-center text-danger fw-bold" style="width: 95px; display: inline-block;" value="${cost}" oninput="onReviewItemFieldChange(${idx}, 'cost_price', this.value, this)">
                    </td>
                    <td>
                        <span class="fw-bold text-danger rev-row-cost-${idx}">${Number(it.total_cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج</span>
                    </td>
                    <td>
                        ${sellInputHtml}
                    </td>
                    <td>
                        <span class="fw-black text-primary rev-row-cust-${idx}">${isBillable ? Number(it.total_customer).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج' : '0.00 ج'}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeReviewItem(${idx})" title="حذف هذا البند من الصندوق">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        const payMethodEl = document.getElementById('review-edit-pay-method');
        if (payMethodEl) payMethodEl.value = 'later';

        const paidInputEl = document.getElementById('review-edit-paid');
        if (paidInputEl) paidInputEl.value = '0.00';

        const accSelectEl = document.getElementById('review-edit-account');
        if (accSelectEl) accSelectEl.value = '';

        recalcReviewModalSummary();
    }

    function onReviewItemFieldChange(idx, field, value, el) {
        if (!reviewStagingItems[idx]) return;

        if (['quantity', 'cost_price', 'selling_price'].includes(field)) {
            reviewStagingItems[idx][field] = parseFloat(value) || 0;
        } else if (field === 'account_id') {
            reviewStagingItems[idx].account_id = value;
            const foundAcc = dbAccountsList.find(a => String(a.id) === String(value));
            reviewStagingItems[idx].account_name = foundAcc ? foundAcc.name : '-';
        } else {
            reviewStagingItems[idx][field] = value;
        }

        const it = reviewStagingItems[idx];
        const qty = parseFloat(it.quantity) || 0;
        const cost = parseFloat(it.cost_price) || 0;
        const sell = parseFloat(it.selling_price) || 0;
        const isBillable = it.is_billable !== 0 && it.op_type !== 'overhead' && it.op_type !== 'commission';
        const supPct = it.supervision_pct || currentSupervisionPct || 15.0;

        it.total_cost = qty * cost;
        it.total_selling_base = isBillable ? (qty * sell) : 0;
        it.supervision_amount = isBillable ? Math.round(it.total_selling_base * (supPct / 100) * 100) / 100 : 0;
        it.total_customer = isBillable ? (it.total_selling_base + it.supervision_amount) : 0;
        it.profit_amount = it.total_customer - it.total_cost;

        if (it.op_type === 'petty' || it.op_type === 'overhead') {
            it.payment_method = 'cash';
            it.paid_amount = it.total_cost;
            it.remaining_debt = 0;
        } else {
            it.payment_method = 'later';
            it.paid_amount = 0;
            it.remaining_debt = it.total_cost;
        }

        const costSpan = document.querySelector(`.rev-row-cost-${idx}`);
        if (costSpan) costSpan.innerText = Number(it.total_cost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const custSpan = document.querySelector(`.rev-row-cust-${idx}`);
        if (custSpan) custSpan.innerText = isBillable ? (Number(it.total_customer).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج') : '0.00 ج';

        recalcReviewModalSummary();

        if (cart[idx]) {
            Object.assign(cart[idx], it);
            renderCart();
        }
    }

    function recalcReviewModalSummary() {
        let totalCost = 0;
        let totalBaseSell = 0;
        let totalSup = 0;
        let totalCustomer = 0;

        reviewStagingItems.forEach(it => {
            totalCost += (it.total_cost || 0);
            totalBaseSell += (it.total_selling_base || 0);
            totalSup += (it.supervision_amount || 0);
            totalCustomer += (it.total_customer || 0);
        });

        const netProfit = totalCustomer - totalCost;

        const costEl = document.getElementById('review-total-cost');
        if (costEl) costEl.innerText = Number(totalCost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const baseSellEl = document.getElementById('review-base-sell');
        if (baseSellEl) baseSellEl.innerText = Number(totalBaseSell).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const supLabelEl = document.getElementById('review-sup-label');
        if (supLabelEl) supLabelEl.innerText = `نسبة الإشراف (${currentSupervisionPct || 15}%)`;

        const supAmountEl = document.getElementById('review-sup-amount');
        if (supAmountEl) supAmountEl.innerText = '+ ' + Number(totalSup).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const custEl = document.getElementById('review-customer-total');
        if (custEl) custEl.innerText = Number(totalCustomer).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        const profitEl = document.getElementById('review-net-profit');
        if (profitEl) profitEl.innerText = Number(netProfit).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';

        syncReviewPaymentWithCost(totalCost);
    }

    function syncReviewPaymentWithCost(totalCost) {
        let debtTotal = 0;
        let cashTotal = 0;

        reviewStagingItems.forEach(it => {
            if (it.op_type === 'petty' || it.op_type === 'overhead') {
                cashTotal += (it.total_cost || 0);
            } else {
                debtTotal += (it.total_cost || 0);
            }
        });

        const paidInput = document.getElementById('review-edit-paid');
        const debtInput = document.getElementById('review-edit-debt');
        const debtBadgeDisplay = document.getElementById('review-debt-badge-display');
        const cashBadgeWrapper = document.getElementById('review-cash-badge-wrapper');
        const cashBadgeDisplay = document.getElementById('review-cash-badge-display');

        if (paidInput) paidInput.value = cashTotal.toFixed(2);
        if (debtInput) debtInput.value = debtTotal.toFixed(2) + ' ج';
        if (debtBadgeDisplay) debtBadgeDisplay.innerText = Number(debtTotal).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';
        if (cashBadgeDisplay) cashBadgeDisplay.innerText = Number(cashTotal).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ج';
        if (cashBadgeWrapper) {
            cashBadgeWrapper.style.setProperty('display', cashTotal > 0 ? 'flex' : 'none', 'important');
        }
    }

    function onReviewPayMethodChange(method) {
        const totalCost = reviewStagingItems.reduce((acc, it) => acc + (it.total_cost || 0), 0);
        syncReviewPaymentWithCost(totalCost);
    }

    function onReviewPaidChange(val) {
        const totalCost = reviewStagingItems.reduce((acc, it) => acc + (it.total_cost || 0), 0);
        syncReviewPaymentWithCost(totalCost);
    }

    function onReviewAccountChange(val) {}

    function removeReviewItem(index) {
        if (reviewStagingItems[index]) {
            const removed = reviewStagingItems.splice(index, 1)[0];
            cart = cart.filter(c => c.cart_id !== removed.cart_id);
            renderCart();

            if (reviewStagingItems.length === 0) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalReviewOperations'));
                if (modal) modal.hide();
                Swal.fire('تنبيه', 'تم تفريغ كافة بنود صندوق العمليات', 'info');
            } else {
                renderReviewModalContent();
            }
        }
    }

    function confirmAndPostFromReview() {
        if (!reviewStagingItems || reviewStagingItems.length === 0) {
            Swal.fire('تنبيه', 'لا توجد بنود في صندوق العمليات للاعتماد والترحيل', 'warning');
            return;
        }

        // التحقق من اختيار خزينة لأي مصروف نثري أو داخلي
        for (const it of reviewStagingItems) {
            if ((it.op_type === 'petty' || it.op_type === 'overhead') && !it.account_id) {
                Swal.fire('اختيار الخزينة مطلوب', `يرجى تحديد الخزينة لصرف المصروف (${it.name_or_title})`, 'warning');
                return;
            }
        }

        const totalCost = reviewStagingItems.reduce((acc, it) => acc + (it.total_cost || 0), 0);
        const method = 'later';
        const paidAmount = 0;
        const debtAmount = totalCost;
        const accountId = null;

        const btn = document.getElementById('btn-confirm-post-review');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>جاري ترحيل وتأكيد صندوق العمليات...';

        const isAllMaterial = reviewStagingItems.every(i => i.op_type === 'material');

        let payload;
        if (isAllMaterial) {
            const first = reviewStagingItems[0];
            payload = {
                is_invoice: true,
                project_id: currentProjectId,
                item_id: currentItemId,
                supplier_name: first.person_name || 'مورد عام',
                invoice_ref: first.invoice_ref || ('فاتورة خامات ' + new Date().toISOString().slice(0, 10)),
                payment_method: method,
                paid_amount: paidAmount,
                remaining_debt: debtAmount,
                account_id: accountId,
                items: reviewStagingItems.map(it => ({
                    item_id: it.item_id,
                    name_or_title: it.name_or_title,
                    person_name: it.person_name,
                    quantity: it.quantity,
                    unit: it.unit,
                    cost_price: it.cost_price,
                    supervision_pct: it.supervision_pct,
                    selling_price: it.selling_price
                }))
            };
        } else {
            payload = {
                project_id: currentProjectId,
                items: reviewStagingItems.map(it => {
                    const isExpense = (it.op_type === 'petty' || it.op_type === 'overhead');
                    return {
                        item_id: it.item_id,
                        op_type: it.op_type,
                        is_billable: it.is_billable,
                        name_or_title: it.name_or_title,
                        person_name: it.person_name,
                        quantity: it.quantity,
                        unit: it.unit,
                        cost_price: it.cost_price,
                        supervision_pct: it.supervision_pct,
                        selling_price: it.selling_price,
                        payment_method: isExpense ? 'cash' : 'later',
                        paid_amount: isExpense ? (it.total_cost || 0) : 0,
                        remaining_debt: isExpense ? 0 : (it.total_cost || 0),
                        account_id: isExpense ? it.account_id : null,
                        invoice_ref: it.invoice_ref,
                        notes: it.notes
                    };
                })
            };
        }

        fetch('{{ route("contracting.operation.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check-double me-2"></i>تأكيد وترحيل الحسابات نهائياً';
            if (data.success) {
                cart = [];
                if (typeof window.clearPosDraftStorage === 'function') window.clearPosDraftStorage();
                renderCart();
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalReviewOperations'));
                if (modal) modal.hide();

                Swal.fire({
                    title: 'تم الترحيل بنجاح!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'ممتاز'
                }).then(() => safeContractingReload());
            } else {
                Swal.fire('خطأ أثناء الحفظ', data.message, 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check-double me-2"></i>تأكيد وترحيل الحسابات نهائياً';
            Swal.fire('خطأ', 'حدث خطأ أثناء الاتصال بالخادم', 'error');
        });
    }

    function submitCart() {
        openReviewModal();
    }

    // ── 5. Project 360 Dashboard AJAX ──
    function loadProject360(projectId) {
        if (!projectId) {
            document.getElementById('project-360-container').style.display = 'none';
            document.getElementById('project-360-empty').style.display = 'block';
            document.getElementById('project-quick-actions').style.display = 'none';
            return;
        }

        fetch(`{{ url('/contracting/project') }}/${projectId}/summary`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;

                // Update Project Header Info
                if (data.project) {
                    const pName = document.getElementById('p360-name');
                    if (pName) pName.innerText = data.project.name;
                    const pClient = document.getElementById('p360-client');
                    if (pClient) pClient.innerText = data.project.client_name;
                    const pLoc = document.getElementById('p360-location');
                    if (pLoc) pLoc.innerText = data.project.location || 'غير محدد';
                    const pSup = document.getElementById('p360-sup');
                    if (pSup) pSup.innerText = data.project.supervision_pct;
                    const pArea = document.getElementById('p360-area');
                    if (pArea) pArea.innerText = data.project.area_sqm ? (Number(data.project.area_sqm).toLocaleString() + ' م²') : 'غير محددة';
                }

                // Update KPIs safely
                const kCost = document.getElementById('kpi-cost'); if (kCost) kCost.innerText = Number(data.totals.total_cost).toLocaleString() + ' ج';
                const kSell = document.getElementById('kpi-selling'); if (kSell) kSell.innerText = Number(data.totals.billable_selling).toLocaleString() + ' ج';
                const kDisc = document.getElementById('kpi-discount'); if (kDisc) kDisc.innerText = Number(data.totals.total_discounts).toLocaleString() + ' ج';
                const kPaid = document.getElementById('kpi-paid'); if (kPaid) kPaid.innerText = Number(data.totals.client_paid).toLocaleString() + ' ج';
                const kRem = document.getElementById('kpi-remaining'); if (kRem) kRem.innerText = Number(data.totals.client_remaining).toLocaleString() + ' ج';
                const kProf = document.getElementById('kpi-profit'); if (kProf) kProf.innerText = Number(data.totals.net_profit).toLocaleString() + ' ج';

                // Print links
                const lSum = document.getElementById('link-print-summary'); if (lSum) lSum.href = `{{ url('/contracting/project') }}/${projectId}/statement?type=summary`;
                const lDet = document.getElementById('link-print-detailed'); if (lDet) lDet.href = `{{ url('/contracting/project') }}/${projectId}/statement?type=detailed`;
                const lInt = document.getElementById('link-print-internal'); if (lInt) lInt.href = `{{ url('/contracting/project') }}/${projectId}/statement?type=internal`;

                // Set IDs in Modals
                const payP = document.getElementById('pay-project-id'); if (payP) payP.value = projectId;
                const discP = document.getElementById('disc-project-id'); if (discP) discP.value = projectId;
                const instP = document.getElementById('inst-project-id'); if (instP) instP.value = projectId;
                const modalCommProj = document.getElementById('modal-comm-project-id'); if (modalCommProj) modalCommProj.value = projectId;
                const instAmt = document.getElementById('inst-amount'); if (instAmt) instAmt.value = data.totals.client_remaining;
                if (typeof calcMonthlyInstallment === 'function') calcMonthlyInstallment();

                // Populate Materials Table
                const matTbody = document.querySelector('#table-project-materials tbody');
                matTbody.innerHTML = '';
                data.materials.forEach(m => {
                    matTbody.innerHTML += `
                    <tr>
                        <td class="text-start fw-bold">${m.name}</td>
                        <td>${m.total_qty} ${m.unit}</td>
                        <td>${m.avg_price} ج</td>
                        <td class="fw-bold text-danger">${Number(m.total_cost).toLocaleString()} ج</td>
                    </tr>`;
                });
                if (data.materials.length === 0) matTbody.innerHTML = '<tr><td colspan="4" class="text-muted">لا توجد خامات مسجلة</td></tr>';

                // Populate Techs Table
                const techTbody = document.querySelector('#table-project-techs tbody');
                techTbody.innerHTML = '';
                data.techs.forEach(t => {
                    const techStmtUrl = `{{ url('/contracting/person-statement') }}?type=technician&name=${encodeURIComponent(t.name)}&project_id=${projectId}`;
                    techTbody.innerHTML += `
                    <tr>
                        <td class="text-start fw-bold">${t.name}</td>
                        <td>${t.jobs_count}</td>
                        <td>${Number(t.total_cost).toLocaleString()} ج</td>
                        <td class="text-success">${Number(t.paid).toLocaleString()} ج</td>
                        <td class="text-danger fw-bold">${Number(t.debt).toLocaleString()} ج</td>
                        <td>
                            <a href="${techStmtUrl}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-2 py-0 fw-bold" style="font-size: 0.75rem;" title="كشف حساب الفني">
                                <i class="fa fa-file-invoice me-1"></i>كشف حساب
                            </a>
                        </td>
                    </tr>`;
                });
                if (data.techs.length === 0) techTbody.innerHTML = '<tr><td colspan="6" class="text-muted">لا يوجد صنايعية مسجلين</td></tr>';

                // Populate Top Suppliers Table
                const supTbody = document.querySelector('#table-project-suppliers tbody');
                supTbody.innerHTML = '';
                data.suppliers.forEach(s => {
                    const supStmtUrl = `{{ url('/contracting/person-statement') }}?type=supplier&name=${encodeURIComponent(s.name)}&project_id=${projectId}`;
                    const paidVal = s.paid !== undefined ? s.paid : Math.max(0, Number(s.total_amount) - Number(s.debt));
                    supTbody.innerHTML += `
                    <tr>
                        <td class="text-start fw-bold">${s.name}</td>
                        <td>${s.orders_count}</td>
                        <td class="fw-bold text-dark">${Number(s.total_amount).toLocaleString()} ج</td>
                        <td class="text-success fw-bold">${Number(paidVal).toLocaleString()} ج</td>
                        <td class="fw-bold text-danger">${Number(s.debt).toLocaleString()} ج</td>
                        <td>
                            <a href="${supStmtUrl}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0 fw-bold" style="font-size: 0.75rem;" title="كشف حساب المورد">
                                <i class="fa fa-file-invoice me-1"></i>كشف حساب
                            </a>
                        </td>
                    </tr>`;
                });
                if (data.suppliers.length === 0) supTbody.innerHTML = '<tr><td colspan="6" class="text-muted">لا توجد تعاملات مع موردين</td></tr>';

                // Populate Project Items (مراحل المشروع) & Dropdown Item Statement Links
                const itemsTbody = document.querySelector('#table-project-items tbody');
                const dropItemsContainer = document.getElementById('dropdown-print-items-list');
                if (dropItemsContainer) dropItemsContainer.innerHTML = '';

                if (itemsTbody) {
                    itemsTbody.innerHTML = '';
                    (data.items || []).forEach((it, idx) => {
                        const profit = (it.total_sell || 0) - (it.total_cost || 0);
                        const itemStmtUrl = `{{ url('/contracting/project') }}/${projectId}/statement?type=detailed&item_id=${it.id}`;
                        itemsTbody.innerHTML += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td class="text-start fw-bold">${it.title}</td>
                            <td><span class="badge bg-secondary">${it.ops_count || 0} حركة</span></td>
                            <td class="fw-bold text-danger">${Number(it.total_cost || 0).toLocaleString()} ج</td>
                            <td class="fw-bold text-primary">${Number(it.total_sell || 0).toLocaleString()} ج</td>
                            <td class="fw-black ${profit >= 0 ? 'text-success' : 'text-danger'}">${Number(profit).toLocaleString()} ج</td>
                            <td>
                                <a href="${itemStmtUrl}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill fw-bold px-3 py-1">
                                    <i class="fa fa-print text-primary me-1"></i>كشف حساب البند
                                </a>
                            </td>
                        </tr>`;

                        if (dropItemsContainer) {
                            dropItemsContainer.innerHTML += `
                                <li>
                                    <a class="dropdown-item small fw-bold" href="${itemStmtUrl}" target="_blank">
                                        <i class="fa fa-file-invoice text-primary me-2"></i>كشف بند: ${it.title}
                                    </a>
                                </li>
                            `;
                        }
                    });
                    if (!data.items || data.items.length === 0) {
                        itemsTbody.innerHTML = '<tr><td colspan="7" class="text-muted py-3">لا توجد بنود مضافة لهذا المشروع</td></tr>';
                        if (dropItemsContainer) {
                            dropItemsContainer.innerHTML = '<li><span class="dropdown-item-text small text-muted">لا توجد بنود مضافة</span></li>';
                        }
                    }
                }

                // Populate Project Full Operations Log
                const opsTbody = document.querySelector('#table-project-operations tbody');
                const opsBadge = document.getElementById('project-ops-badge');
                if (opsBadge) opsBadge.innerText = (data.operations || []).length + ' حركة';
                if (opsTbody) {
                    opsTbody.innerHTML = '';
                    (data.operations || []).forEach((op, idx) => {
                        let opBadge = '<span class="badge bg-primary">خامة</span>';
                        if (op.op_type === 'technician') opBadge = '<span class="badge bg-warning text-dark">مصنعية فني</span>';
                        else if (op.op_type === 'petty') opBadge = '<span class="badge bg-info text-white">نثريات للعميل</span>';
                        else if (op.op_type === 'overhead') opBadge = '<span class="badge bg-danger">مصروف داخلي</span>';
                        else if (op.op_type === 'commission') opBadge = '<span class="badge bg-success">عمولة مسوق</span>';

                        let payBadge = '<span class="badge bg-success">نقداً</span>';
                        if (op.payment_method === 'later') payBadge = '<span class="badge bg-danger">آجل (دين)</span>';
                        else if (op.payment_method === 'partial') payBadge = '<span class="badge bg-warning text-dark">جزئي</span>';
                        else if (op.payment_method === 'bank') payBadge = '<span class="badge bg-info text-dark">بنك / محفظة</span>';

                        opsTbody.innerHTML += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td class="small">${(op.created_at || '').substring(0, 10)}</td>
                            <td>${opBadge}</td>
                            <td class="text-start fw-bold">${op.name_or_title}</td>
                            <td class="small text-muted">${op.item_title || (op.op_type === 'commission' ? 'المشروع ككل' : '-')}</td>
                            <td class="fw-bold text-dark">${op.person_name || '-'}</td>
                            <td>${op.quantity} ${op.unit || ''}</td>
                            <td class="fw-bold text-danger">${Number(op.total_cost).toLocaleString()} ج</td>
                            <td class="fw-bold text-primary">${op.is_billable ? Number(op.total_selling).toLocaleString() + ' ج' : '<span class="text-muted">-</span>'}</td>
                            <td>${payBadge}</td>
                            <td class="text-success">${Number(op.paid_amount || 0).toLocaleString()} ج</td>
                            <td class="fw-bold ${op.remaining_debt > 0 ? 'text-danger' : 'text-muted'}">${Number(op.remaining_debt || 0).toLocaleString()} ج</td>
                            <td class="small font-monospace">${op.invoice_ref || '-'}</td>
                            <td>
                                ${op.op_type === 'material' && op.invoice_ref ? `
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1 small fw-bold" onclick="goToInvoiceFromOp('${(op.invoice_ref || '').replace(/'/g, "\\'")}', ${op.project_id})" title="تعديل أو مرتجع أو حذف الفاتورة من تابة الفواتير">
                                        <i class="fa fa-receipt me-1"></i>إدارة الفاتورة
                                    </button>
                                ` : `
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0" onclick="openEditSingleOp(${JSON.stringify(op).replace(/"/g, '&quot;')})" title="تعديل الحركة">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="deleteOpFromRow(${op.id}, this)" data-title="${(op.name_or_title || '').replace(/"/g, '&quot;')}" title="حذف الحركة">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                `}
                            </td>
                        </tr>`;
                    });
                    if (!data.operations || data.operations.length === 0) {
                        opsTbody.innerHTML = '<tr><td colspan="14" class="text-muted py-4">لا توجد حركات أو فواتير مسجلة لهذا المشروع بعد</td></tr>';
                    }
                }

                // Highlight active 360 chip
                document.querySelectorAll('.btn-360-chip').forEach(c => {
                    const isMatch = (c.id === `chip-360-${projectId}`);
                    c.classList.toggle('btn-dark', isMatch);
                    c.classList.toggle('text-white', isMatch);
                    c.classList.toggle('btn-outline-dark', !isMatch);
                });

                const pCont = document.getElementById('project-360-container');
                if (pCont) pCont.style.display = 'block';
                const pEmp = document.getElementById('project-360-empty');
                if (pEmp) pEmp.style.display = 'none';
                const pAct = document.getElementById('project-quick-actions');
                if (pAct) pAct.style.display = 'flex';
            });
    }

    // ── Search & Autocomplete in Project Dossier (سجل المشاريع) ──
    function showClientSearchDropdown360(resetFilter = false) {
        const dd = document.getElementById('client-search-dropdown-360');
        if (dd) dd.style.display = 'block';
        if (resetFilter) {
            document.querySelectorAll('.client-search-item-360').forEach(el => el.style.display = 'flex');
            const noRes = document.getElementById('client-search-no-results-360');
            if (noRes) noRes.style.display = 'none';
        }
    }

    function toggleClientSearchDropdown360() {
        const dd = document.getElementById('client-search-dropdown-360');
        if (!dd) return;
        if (dd.style.display === 'none' || dd.style.display === '') {
            showClientSearchDropdown360(true);
        } else {
            dd.style.display = 'none';
        }
    }

    function filterClientSearchDropdown360(query) {
        showClientSearchDropdown360(false);
        const q = (query || '').trim().toLowerCase();
        const items = document.querySelectorAll('.client-search-item-360');
        let visibleCount = 0;
        items.forEach(el => {
            const client = (el.getAttribute('data-client') || '').toLowerCase();
            const text = (el.textContent || '').toLowerCase();
            if (!q || client === 'all' || client.includes(q) || text.includes(q)) {
                el.style.display = 'flex';
                visibleCount++;
            } else {
                el.style.display = 'none';
            }
        });
        const noRes = document.getElementById('client-search-no-results-360');
        if (noRes) noRes.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function chooseClientFromSearch360(clientName, labelText) {
        const input = document.getElementById('input-client-search-360');
        if (input) input.value = labelText || clientName;
        const sel = document.getElementById('select-360-client-filter');
        if (sel) sel.value = clientName;
        const dd = document.getElementById('client-search-dropdown-360');
        if (dd) dd.style.display = 'none';
        filterProjectsByClient(clientName);
    }

    function clearClientSearch360() {
        const sel = document.getElementById('select-360-client-filter');
        const allLabel = sel?.querySelector('option[value="all"]')?.textContent || '-- جميع العملاء --';
        if (sel) sel.value = 'all';
        const input = document.getElementById('input-client-search-360');
        if (input) {
            input.value = '';
            input.placeholder = allLabel;
            input.focus();
        }
        filterProjectsByClient('all');
        showClientSearchDropdown360(true);
    }

    function showProjectSearchDropdown(resetToClientFilter = false) {
        const dd = document.getElementById('project-search-dropdown');
        if (dd) dd.style.display = 'block';
        if (resetToClientFilter) {
            const activeClient = document.getElementById('select-360-client-filter')?.value || 'all';
            let visibleCount = 0;
            document.querySelectorAll('.project-search-item').forEach(el => {
                const client = el.getAttribute('data-client') || '';
                const match = (activeClient === 'all' || !activeClient || client === activeClient);
                el.style.display = match ? 'flex' : 'none';
                if (match) visibleCount++;
            });
            const noRes = document.getElementById('project-search-no-results');
            if (noRes) noRes.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    function hideProjectSearchDropdown() {
        setTimeout(() => {
            const dd = document.getElementById('project-search-dropdown');
            if (dd) dd.style.display = 'none';
        }, 250);
    }

    function toggleProjectSearchDropdown() {
        const dd = document.getElementById('project-search-dropdown');
        if (!dd) return;
        if (dd.style.display === 'none' || dd.style.display === '') {
            showProjectSearchDropdown(true);
        } else {
            dd.style.display = 'none';
        }
    }

    function clearProjectSearch360() {
        const input = document.getElementById('input-project-search');
        if (input) {
            input.value = '';
            input.focus();
        }
        showProjectSearchDropdown(true);
    }

    // Close client & project search dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const projWrapper = document.getElementById('project-search-wrapper');
        const projDd = document.getElementById('project-search-dropdown');
        if (projWrapper && projDd && !projWrapper.contains(e.target)) {
            projDd.style.display = 'none';
        }
        const clientWrapper = document.getElementById('client-search-wrapper-360');
        const clientDd = document.getElementById('client-search-dropdown-360');
        if (clientWrapper && clientDd && !clientWrapper.contains(e.target)) {
            clientDd.style.display = 'none';
        }
    });

    function filterProjectSearchDropdown(query) {
        showProjectSearchDropdown(false);
        const q = (query || '').trim().toLowerCase();
        const activeClient = document.getElementById('select-360-client-filter')?.value || 'all';
        const items = document.querySelectorAll('.project-search-item');
        let visibleCount = 0;
        items.forEach(el => {
            const name = (el.getAttribute('data-name') || '').toLowerCase();
            const client = (el.getAttribute('data-client') || '');
            const clientLower = client.toLowerCase();
            const id = (el.getAttribute('data-id') || '').toLowerCase();
            const combinedLabel = `${name} - (العميل: ${clientLower})`;
            const matchClient = (activeClient === 'all' || !activeClient || client === activeClient);
            const matchQuery = (!q || name.includes(q) || clientLower.includes(q) || id.includes(q) || combinedLabel.includes(q));
            if (matchClient && matchQuery) {
                el.style.display = 'flex';
                visibleCount++;
            } else {
                el.style.display = 'none';
            }
        });

        const noRes = document.getElementById('project-search-no-results');
        if (noRes) noRes.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function chooseProjectFromSearch(id, name, client) {
        const input = document.getElementById('input-project-search');
        if (input) input.value = `${name} - (العميل: ${client})`;
        const dd = document.getElementById('project-search-dropdown');
        if (dd) dd.style.display = 'none';
        select360Project(id);
    }

    function select360Project(id) {
        if (!id) return;
        const sel = document.getElementById('select-project-360');
        if (sel) {
            sel.value = id;
            const opt = sel.querySelector(`option[value="${id}"]`);
            const input = document.getElementById('input-project-search');
            if (input && opt) {
                input.value = opt.textContent.replace('-- اضغط لاختيار المشروع --', '').trim();
            }
        }

        document.querySelectorAll('.btn-360-chip').forEach(c => {
            const isMatch = (c.id === `chip-360-${id}`);
            c.classList.toggle('btn-dark', isMatch);
            c.classList.toggle('text-white', isMatch);
            c.classList.toggle('btn-outline-dark', !isMatch);
        });

        loadProject360(id);
    }

    // Close project search dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('project-search-dropdown');
        const input = document.getElementById('input-project-search');
        const toggleBtn = input ? input.nextElementSibling : null;
        if (dd && dd.style.display !== 'none') {
            if (!dd.contains(e.target) && e.target !== input && e.target !== toggleBtn) {
                dd.style.display = 'none';
            }
        }
    });

    // ── Invoice Pagination & Filter System (7 per page) ──
    const invoicePaginationState = {
        materials: { page: 1, pageSize: 7 },
        clients:   { page: 1, pageSize: 7 },
        cancelled: { page: 1, pageSize: 7 }
    };

    const cancelledInvoicesData = @json($cancelledInvoices->values());

    function paginateInvoices(type, targetPage) {
        if (!invoicePaginationState[type]) {
            invoicePaginationState[type] = { page: 1, pageSize: 7 };
        }
        if (targetPage !== undefined) {
            invoicePaginationState[type].page = targetPage;
        }

        const isMat = (type === 'materials');
        const isCan = (type === 'cancelled');
        const tableId = isMat ? 'table-invoices-materials' : (isCan ? 'table-invoices-cancelled' : 'table-invoices-payments');
        const navId = isMat ? 'pagination-nav-materials' : (isCan ? 'pagination-nav-cancelled' : 'pagination-nav-clients');
        const infoId = isMat ? 'page-info-materials' : (isCan ? 'page-info-cancelled' : 'page-info-clients');
        const dateColIdx = isMat ? 11 : (isCan ? 8 : 5);
        const itemLabel = isMat ? 'فاتورة خامات' : (isCan ? 'فاتورة ملغاة' : 'سند / مستخلص');

        const table = document.getElementById(tableId);
        if (!table) return 0;

        const q = (document.getElementById('input-search-invoices')?.value || '').trim().toLowerCase();
        const fromStr = document.getElementById('df-from-invoices')?.value || '';
        const toStr = document.getElementById('df-to-invoices')?.value || '';
        const fromDate = fromStr ? new Date(fromStr + 'T00:00:00') : null;
        const toDate = toStr ? new Date(toStr + 'T23:59:59') : null;

        const allRows = Array.from(table.querySelectorAll('tbody tr'));
        const dataRows = allRows.filter(tr => !tr.querySelector('td[colspan]'));
        const emptyRow = allRows.find(tr => tr.querySelector('td[colspan]'));

        // 1. Filter rows matching both search text & date range
        const matchingRows = [];
        dataRows.forEach(tr => {
            let matches = true;

            if (q) {
                const txt = tr.textContent.toLowerCase();
                if (!txt.includes(q)) matches = false;
            }

            if (matches && (fromDate || toDate)) {
                const cell = tr.cells[dateColIdx];
                const text = cell ? cell.textContent : '';
                const m = text.match(/\d{4}-\d{2}-\d{2}/);
                if (m) {
                    const rowDate = new Date(m[0] + 'T12:00:00');
                    if (fromDate && rowDate < fromDate) matches = false;
                    if (toDate && rowDate > toDate) matches = false;
                }
            }

            if (matches) {
                matchingRows.push(tr);
            } else {
                tr.style.display = 'none';
            }
        });

        const totalCount = matchingRows.length;
        const pageSize = invoicePaginationState[type].pageSize || 7;
        const totalPages = Math.max(1, Math.ceil(totalCount / pageSize));

        if (invoicePaginationState[type].page > totalPages) {
            invoicePaginationState[type].page = totalPages;
        }
        if (invoicePaginationState[type].page < 1) {
            invoicePaginationState[type].page = 1;
        }
        const currentPage = invoicePaginationState[type].page;

        // 2. Display 7 rows for current page
        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = startIdx + pageSize;

        matchingRows.forEach((tr, idx) => {
            if (idx >= startIdx && idx < endIdx) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });

        if (emptyRow) {
            emptyRow.style.display = totalCount === 0 ? '' : 'none';
        }

        // 3. Update info text
        const infoEl = document.getElementById(infoId);
        if (infoEl) {
            if (totalCount === 0) {
                infoEl.innerHTML = `<span class="text-danger small fw-bold">لا توجد نتائج مطابقة للبحث أو الفترة المحددة</span>`;
            } else {
                const shownStart = startIdx + 1;
                const shownEnd = Math.min(endIdx, totalCount);
                infoEl.innerText = `عرض ${shownStart} - ${shownEnd} من إجمالي ${totalCount} ${itemLabel}`;
            }
        }

        // 4. Render pagination navigation bar
        const navEl = document.getElementById(navId);
        if (navEl) {
            navEl.innerHTML = '';
            if (totalPages > 1) {
                // Prev button
                const prevDisabled = currentPage === 1 ? ' disabled' : '';
                navEl.innerHTML += `
                    <li class="page-item${prevDisabled}">
                        <a class="page-link py-1 px-2 small text-dark" href="javascript:void(0)" onclick="paginateInvoices('${type}', ${currentPage - 1})" aria-label="السابق" title="الصفحة السابقة">
                            <i class="fa fa-chevron-right small"></i>
                        </a>
                    </li>
                `;

                let startP = Math.max(1, currentPage - 2);
                let endP = Math.min(totalPages, startP + 4);
                if (endP - startP < 4) {
                    startP = Math.max(1, endP - 4);
                }

                if (startP > 1) {
                    navEl.innerHTML += `
                        <li class="page-item">
                            <a class="page-link py-1 px-2 small" href="javascript:void(0)" onclick="paginateInvoices('${type}', 1)">1</a>
                        </li>
                    `;
                    if (startP > 2) {
                        navEl.innerHTML += `<li class="page-item disabled"><span class="page-link py-1 px-2 small">...</span></li>`;
                    }
                }

                for (let p = startP; p <= endP; p++) {
                    const activeCls = p === currentPage ? ' active fw-bold' : '';
                    navEl.innerHTML += `
                        <li class="page-item${activeCls}">
                            <a class="page-link py-1 px-2 small" href="javascript:void(0)" onclick="paginateInvoices('${type}', ${p})">${p}</a>
                        </li>
                    `;
                }

                if (endP < totalPages) {
                    if (endP < totalPages - 1) {
                        navEl.innerHTML += `<li class="page-item disabled"><span class="page-link py-1 px-2 small">...</span></li>`;
                    }
                    navEl.innerHTML += `
                        <li class="page-item">
                            <a class="page-link py-1 px-2 small" href="javascript:void(0)" onclick="paginateInvoices('${type}', ${totalPages})">${totalPages}</a>
                        </li>
                    `;
                }

                // Next button
                const nextDisabled = currentPage === totalPages ? ' disabled' : '';
                navEl.innerHTML += `
                    <li class="page-item${nextDisabled}">
                        <a class="page-link py-1 px-2 small text-dark" href="javascript:void(0)" onclick="paginateInvoices('${type}', ${currentPage + 1})" aria-label="التالي" title="الصفحة التالية">
                            <i class="fa fa-chevron-left small"></i>
                        </a>
                    </li>
                `;
            }
        }

        return totalCount;
    }

    function refreshAllInvoicesPagination(resetPage = false) {
        if (resetPage) {
            invoicePaginationState.materials.page = 1;
            invoicePaginationState.clients.page = 1;
            invoicePaginationState.cancelled.page = 1;
        }
        const matCount = paginateInvoices('materials');
        const cliCount = paginateInvoices('clients');
        const canCount = paginateInvoices('cancelled');

        const q = (document.getElementById('input-search-invoices')?.value || '').trim();
        const badge = document.getElementById('invoices-search-count');
        if (badge) {
            badge.innerHTML = q 
                ? `<i class="fa fa-filter text-warning me-1"></i>نتائج المطابقة: ${matCount + cliCount + canCount} فاتورة / مستخلص` 
                : `<i class="fa fa-layer-group text-primary me-1"></i>إجمالي الفواتير النشطة: ${matCount + cliCount} | الملغاة: ${canCount}`;
        }
    }

    function filterInvoicesTable(query) {
        refreshAllInvoicesPagination(true);
    }

    function filterQuotationsTable(query) {
        const q = (query || '').trim().toLowerCase();
        document.querySelectorAll('#tab-quotations tbody tr').forEach(tr => {
            if (tr.querySelector('td[colspan]')) return;
            const txt = tr.textContent.toLowerCase();
            tr.style.display = txt.includes(q) ? '' : 'none';
        });
    }

    function filterProjectOpsTable(query) {
        const q = (query || '').trim().toLowerCase();
        let count = 0;
        document.querySelectorAll('#table-project-operations tbody tr').forEach(tr => {
            if (tr.querySelector('td[colspan]')) return;
            const txt = tr.textContent.toLowerCase();
            const match = txt.includes(q);
            tr.style.display = match ? '' : 'none';
            if (match) count++;
        });
        const badge = document.getElementById('project-ops-badge');
        if (badge && q) {
            badge.innerText = `${count} مطابقة`;
        }
    }

    function filterSurveyLogTable(query) {
        const q = (query || '').trim().toLowerCase();
        document.querySelectorAll('#table-survey-log tbody tr').forEach(tr => {
            if (tr.querySelector('td[colspan]')) return;
            const txt = tr.textContent.toLowerCase();
            tr.style.display = txt.includes(q) ? '' : 'none';
        });
    }

    function filterDirectorySearch(query) {
        const q = (query || '').trim().toLowerCase();
        document.querySelectorAll('#table-directory tbody tr').forEach(tr => {
            if (tr.querySelector('td[colspan]')) return;
            const txt = tr.textContent.toLowerCase();
            tr.style.display = txt.includes(q) ? '' : 'none';
        });
    }

    // ── Table Date & Time Range Filtering Engine ──
    function setQuickDateFilter(tabKey, preset, tableId, dateColIdx) {
        const today = new Date();
        const pad = n => String(n).padStart(2, '0');
        const toDateStr = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

        let fromStr = '';
        let toStr = toDateStr(today);

        if (preset === 'today') {
            fromStr = toDateStr(today);
        } else if (preset === 'week') {
            const d = new Date(today);
            d.setDate(d.getDate() - 7);
            fromStr = toDateStr(d);
        } else if (preset === 'month') {
            const d = new Date(today.getFullYear(), today.getMonth(), 1);
            fromStr = toDateStr(d);
        } else if (preset === 'all') {
            fromStr = '';
            toStr = '';
        }

        const fromInput = document.getElementById(`df-from-${tabKey}`);
        const toInput = document.getElementById(`df-to-${tabKey}`);
        if (fromInput) fromInput.value = fromStr;
        if (toInput) toInput.value = toStr;

        const bar = document.getElementById(`filter-bar-${tabKey}`);
        if (bar) {
            bar.querySelectorAll('.btn-filter-preset').forEach(btn => {
                btn.classList.remove('active');
                if (preset === 'all' && btn.innerText.includes('الكل')) btn.classList.add('active');
                if (preset === 'today' && btn.innerText.includes('اليوم')) btn.classList.add('active');
                if (preset === 'week' && btn.innerText.includes('الأسبوع')) btn.classList.add('active');
                if (preset === 'month' && btn.innerText.includes('الشهر')) btn.classList.add('active');
            });
        }

        if (tabKey === 'invoices') {
            refreshAllInvoicesPagination(true);
            return;
        }

        applyTableDateFilter(tableId, dateColIdx, fromStr, toStr);
    }

    function runCustomDateFilter(tabKey, tableId, dateColIdx) {
        const fromStr = document.getElementById(`df-from-${tabKey}`)?.value || '';
        const toStr = document.getElementById(`df-to-${tabKey}`)?.value || '';

        const bar = document.getElementById(`filter-bar-${tabKey}`);
        if (bar) {
            bar.querySelectorAll('.btn-filter-preset').forEach(btn => btn.classList.remove('active'));
        }

        if (tabKey === 'invoices') {
            refreshAllInvoicesPagination(true);
            return;
        }

        applyTableDateFilter(tableId, dateColIdx, fromStr, toStr);
    }

    function clearDateFilter(tabKey, tableId, dateColIdx) {
        const fromInput = document.getElementById(`df-from-${tabKey}`);
        const toInput = document.getElementById(`df-to-${tabKey}`);
        if (fromInput) fromInput.value = '';
        if (toInput) toInput.value = '';

        const bar = document.getElementById(`filter-bar-${tabKey}`);
        if (bar) {
            bar.querySelectorAll('.btn-filter-preset').forEach(btn => {
                btn.classList.remove('active');
                if (btn.innerText.includes('الكل')) btn.classList.add('active');
            });
        }

        if (tabKey === 'invoices') {
            refreshAllInvoicesPagination(true);
            return;
        }

        applyTableDateFilter(tableId, dateColIdx, '', '');
    }

    function applyTableDateFilter(tableId, dateColIdx, fromStr, toStr) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = table.querySelectorAll('tbody tr');
        const from = fromStr ? new Date(fromStr + 'T00:00:00') : null;
        const to = toStr ? new Date(toStr + 'T23:59:59') : null;

        rows.forEach(tr => {
            if (tr.querySelector('td[colspan]')) return;
            const cell = tr.cells[dateColIdx];
            if (!cell) {
                tr.style.display = '';
                return;
            }
            const text = cell.textContent || '';
            const match = text.match(/\d{4}-\d{2}-\d{2}/);
            if (!match) {
                tr.style.display = '';
                return;
            }
            const rowDate = new Date(match[0] + 'T12:00:00');
            let visible = true;
            if (from && rowDate < from) visible = false;
            if (to && rowDate > to) visible = false;
            tr.style.display = visible ? '' : 'none';
        });
    }

    function filterItemCards(query) {
        const q = (query || '').trim().toLowerCase();
        document.querySelectorAll('#items-grid .pos-btn').forEach(btn => {
            const txt = btn.textContent.toLowerCase();
            btn.style.display = txt.includes(q) ? 'flex' : 'none';
        });
    }

    function openProjectFromAnywhere(id) {
        if (!id) return;
        const tabBtn = document.querySelector('[data-bs-target="#tab-projects"]');
        if (tabBtn) {
            tabBtn.click();
        }
        select360Project(id);
    }

    function deleteOpFromRow(id, btn) {
        const title = btn ? btn.getAttribute('data-title') : '';
        deleteOperation(id, title);
    }

    // ── Invoice Details & Subtabs ──
    function switchInvoicesSubTab(type, btn) {
        document.querySelectorAll('#invoicesSubTabs .nav-link').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const matPane = document.getElementById('subtab-invoices-materials');
        const cliPane = document.getElementById('subtab-invoices-clients');
        const canPane = document.getElementById('subtab-invoices-cancelled');

        if (matPane) matPane.style.display = (type === 'materials') ? 'block' : 'none';
        if (cliPane) cliPane.style.display = (type === 'clients') ? 'block' : 'none';
        if (canPane) canPane.style.display = (type === 'cancelled') ? 'block' : 'none';

        paginateInvoices(type);
    }

    function viewCancelledInvoiceDetails(idx) {
        const cinv = cancelledInvoicesData[idx];
        if (!cinv) return;
        let items = [];
        try {
            items = typeof cinv.items_json === 'string' ? JSON.parse(cinv.items_json) : (cinv.items_json || []);
        } catch (e) {}

        document.getElementById('inv-modal-title').innerHTML = `<i class="fa fa-ban text-danger me-2"></i>${cinv.invoice_ref} <span class="badge bg-danger ms-2">فاتورة ملغاة</span>`;
        document.getElementById('inv-modal-subtitle').innerText = `المشروع: ${cinv.project_name || '-'} | العميل: ${cinv.client_name || '-'} | تاريخ الإلغاء: ${(cinv.cancelled_at || '').substring(0, 16)}`;
        document.getElementById('inv-modal-supplier').innerText = cinv.supplier_name || '-';
        document.getElementById('inv-modal-project').innerText = cinv.project_name || '-';
        document.getElementById('inv-modal-cost').innerText = Number(cinv.total_cost || 0).toLocaleString() + ' ج (ملغى)';
        document.getElementById('inv-modal-debt').innerText = '0.00 ج (تم إلغاء الدين)';

        const printBtn = document.getElementById('btn-modal-print-invoice');
        if (printBtn) {
            printBtn.style.display = 'none';
        }

        const tbody = document.querySelector('#table-inv-modal-items tbody');
        tbody.innerHTML = '';
        items.forEach((it, i) => {
            tbody.innerHTML += `
            <tr class="table-danger">
                <td>${i + 1}</td>
                <td class="text-start fw-bold text-decoration-line-through">${it.name_or_title || '-'}</td>
                <td>${it.quantity || 1}</td>
                <td class="text-danger fw-bold">${Number(it.cost_price || 0).toLocaleString()} ج</td>
                <td class="text-primary fw-bold">${Number(it.selling_price || 0).toLocaleString()} ج</td>
                <td class="text-danger fw-black">${Number(it.total_cost || 0).toLocaleString()} ج</td>
                <td class="text-primary fw-black">${Number(it.total_selling || 0).toLocaleString()} ج</td>
            </tr>`;
        });

        new bootstrap.Modal(document.getElementById('modalInvoiceDetails')).show();
    }

    function viewInvoiceDetails(invoiceRef, projectId) {
        fetch(`{{ route("contracting.invoice.details") }}?invoice_ref=${encodeURIComponent(invoiceRef)}&project_id=${projectId || ''}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                document.getElementById('inv-modal-title').innerHTML = `<i class="fa fa-boxes-stacked text-warning me-2"></i>${data.invoice_ref}`;
                document.getElementById('inv-modal-subtitle').innerText = `المشروع: ${data.project} | العميل: ${data.client} | التاريخ: ${data.created_at.substring(0, 10)}`;
                document.getElementById('inv-modal-supplier').innerText = data.supplier;
                document.getElementById('inv-modal-project').innerText = data.project;
                document.getElementById('inv-modal-cost').innerText = Number(data.total_cost).toLocaleString() + ' ج';
                document.getElementById('inv-modal-debt').innerText = Number(data.total_debt).toLocaleString() + ' ج';

                const printBtn = document.getElementById('btn-modal-print-invoice');
                if (printBtn) {
                    printBtn.style.display = '';
                    printBtn.href = `{{ url('/contracting/material-invoice') }}/${encodeURIComponent(data.invoice_ref)}?project_id=${projectId || ''}`;
                }

                const tbody = document.querySelector('#table-inv-modal-items tbody');
                tbody.innerHTML = '';
                (data.items || []).forEach((it, idx) => {
                    tbody.innerHTML += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td class="text-start fw-bold">${it.name_or_title}</td>
                        <td>${it.quantity}</td>
                        <td class="text-danger fw-bold">${Number(it.cost_price).toLocaleString()} ج</td>
                        <td class="text-primary fw-bold">${Number(it.selling_price).toLocaleString()} ج</td>
                        <td class="text-danger fw-black">${Number(it.total_cost).toLocaleString()} ج</td>
                        <td class="text-primary fw-black">${Number(it.total_selling).toLocaleString()} ج</td>
                    </tr>`;
                });

                new bootstrap.Modal(document.getElementById('modalInvoiceDetails')).show();
            });
    }

    function deleteOperation(id, name) {
        Swal.fire({
            title: 'حذف العملية',
            text: `هل تريد بالتأكيد حذف حركة "${name}"؟ سيتم استعادة الرصيد والديون تلقائياً.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف',
            cancelButtonText: 'إلغاء'
        }).then(res => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/operation') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الحذف', data.message, 'success').then(() => {
                            const sel = document.getElementById('select-project-360');
                            if (sel && sel.value) loadProject360(sel.value);
                            else safeContractingReload();
                        });
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                });
            }
        });
    }

    function goToInvoiceFromOp(invoiceRef, projectId) {
        const tabBtn = document.querySelector('[data-bs-target="#tab-invoices"]');
        if (tabBtn) tabBtn.click();
        const matBtn = document.getElementById('btn-subtab-materials');
        if (matBtn) switchInvoicesSubTab('materials', matBtn);
        openEditMaterialInvoice(invoiceRef, projectId);
    }

    function deleteMaterialInvoice(invoiceRef, projectId) {
        Swal.fire({
            title: 'حذف فاتورة الخامات بالكامل',
            html: `<div class="text-end">
                    <p>هل تريد بالتأكيد إلغاء وحذف فاتورة الخامات <b>${invoiceRef}</b>؟</p>
                    <ul class="text-danger small mb-0 pe-3">
                        <li>سيتم حذف جميع بنود وخامات الفاتورة.</li>
                        <li>سيتم إلغاء متبقي ديون المورد في سجل ديون الشركات.</li>
                        <li>سيتم <b>استرداد المبالغ المسددة نقداً إلى الخزينة فوراً</b>.</li>
                        <li>سيتم تحديث رصيد مطالبة ومستحقات العميل في شاشة المستحقات.</li>
                    </ul>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف واسترداد',
            cancelButtonText: 'تراجع',
            confirmButtonColor: '#dc2626'
        }).then(res => {
            if (res.isConfirmed) {
                Swal.fire({ title: 'جاري حذف الفاتورة وعكس الأثر المالي...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                fetch('{{ route("contracting.material_invoice.delete") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ invoice_ref: invoiceRef, project_id: projectId })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الإلغاء بنجاح', data.message, 'success').then(() => safeContractingReload());
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                })
                .catch(err => Swal.fire('خطأ', 'تعذر الاتصال بالخادم', 'error'));
            }
        });
    }

    let editMinvRowCounter = 0;

    function openEditMaterialInvoice(invoiceRef, projectId) {
        Swal.fire({ title: 'جاري تحميل بيانات الفاتورة...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        fetch(`{{ route("contracting.material_invoice.details") }}?invoice_ref=${encodeURIComponent(invoiceRef)}&project_id=${projectId}`)
            .then(r => r.json())
            .then(data => {
                Swal.close();
                if (!data.success) {
                    Swal.fire('خطأ', data.message || 'تعذر تحميل بيانات الفاتورة', 'error');
                    return;
                }

                document.getElementById('edit-minv-ref').value = data.invoice_ref;
                document.getElementById('edit-minv-project-id').value = data.project_id;
                document.getElementById('edit-minv-ref-title').innerText = data.invoice_ref;
                document.getElementById('edit-minv-project-subtitle').innerText = `المشروع: ${data.project_name} | العميل: ${data.client_name}`;
                document.getElementById('edit-minv-supplier').value = data.supplier_name || '';
                document.getElementById('edit-minv-project-name').value = data.project_name || '';
                document.getElementById('edit-minv-client-name').value = data.client_name || '';
                document.getElementById('edit-minv-paid').value = data.paid_amount || 0;
                document.getElementById('edit-minv-account').value = data.account_id || '';

                const tbody = document.getElementById('edit-minv-items-tbody');
                tbody.innerHTML = '';
                editMinvRowCounter = 0;

                (data.items || []).forEach(it => {
                    addEditMinvItemRow(it);
                });

                calcEditMinvTotals();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditMaterialInvoice')).show();
            })
            .catch(err => {
                Swal.fire('خطأ', 'تعذر الاتصال بالخادم', 'error');
            });
    }

    function addEditMinvItemRow(item = {}) {
        editMinvRowCounter++;
        const idx = editMinvRowCounter;
        const tbody = document.getElementById('edit-minv-items-tbody');
        const tr = document.createElement('tr');
        tr.id = `edit-minv-row-${idx}`;
        tr.className = 'edit-minv-row';

        const name = item.name_or_title || '';
        const qty = item.quantity !== undefined ? item.quantity : 1;
        const cost = item.cost_price !== undefined ? item.cost_price : 0;
        const sup = item.supervision_pct !== undefined ? item.supervision_pct : 15;
        let sell = item.selling_price !== undefined ? item.selling_price : 0;
        if (!sell && cost) {
            sell = cost;
        }

        tr.innerHTML = `
            <td class="text-muted fw-bold edit-minv-num"></td>
            <td>
                <input type="text" class="form-control form-control-sm rounded-3 em-title fw-bold" style="min-width: 200px;" placeholder="اسم الخامة..." value="${name}" required>
            </td>
            <td>
                <input type="number" step="any" min="0" class="form-control form-control-sm rounded-3 em-qty text-center" value="${qty}" oninput="calcEditMinvRow(${idx})">
            </td>
            <td>
                <input type="number" step="any" min="0" class="form-control form-control-sm rounded-3 em-cost text-center text-danger fw-bold" value="${cost}" oninput="onEditMinvCostChange(${idx})">
            </td>
            <td>
                <input type="number" step="any" min="0" class="form-control form-control-sm rounded-3 em-sup text-center text-warning fw-bold" value="${sup}" oninput="onEditMinvSupChange(${idx})">
            </td>
            <td>
                <input type="number" step="any" min="0" class="form-control form-control-sm rounded-3 em-sell text-center text-primary fw-bold" value="${sell}" oninput="onEditMinvSellChange(${idx})">
            </td>
            <td class="fw-bold text-danger em-total-cost">0.00 ج</td>
            <td class="fw-bold text-primary em-total-sell">0.00 ج</td>
            <td>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeEditMinvItemRow(${idx})"><i class="fa fa-times"></i></button>
            </td>
        `;

        tbody.appendChild(tr);
        renumberEditMinvRows();
        calcEditMinvRow(idx);
    }

    function renumberEditMinvRows() {
        document.querySelectorAll('#edit-minv-items-tbody tr').forEach((row, i) => {
            const num = row.querySelector('.edit-minv-num');
            if (num) num.innerText = i + 1;
        });
    }

    function removeEditMinvItemRow(idx) {
        const row = document.getElementById(`edit-minv-row-${idx}`);
        if (row) {
            row.remove();
            renumberEditMinvRows();
            calcEditMinvTotals();
        }
        if (document.querySelectorAll('#edit-minv-items-tbody tr').length === 0) {
            addEditMinvItemRow();
        }
    }

    function onEditMinvCostChange(idx) {
        calcEditMinvRow(idx);
    }

    function onEditMinvSupChange(idx) {
        calcEditMinvRow(idx);
    }

    function onEditMinvSellChange(idx) {
        calcEditMinvRow(idx);
    }

    function calcEditMinvRow(idx) {
        const row = document.getElementById(`edit-minv-row-${idx}`);
        if (!row) return;
        const qty = parseFloat(row.querySelector('.em-qty').value) || 0;
        const cost = parseFloat(row.querySelector('.em-cost').value) || 0;
        const sup = parseFloat(row.querySelector('.em-sup').value) || 0;
        const sell = parseFloat(row.querySelector('.em-sell').value) || 0;

        const baseSell = sell > 0 ? sell : cost;
        const totCost = qty * cost;
        const totBaseSell = qty * baseSell;
        const totSup = Math.round(totBaseSell * (sup / 100) * 100) / 100;
        const totSell = totBaseSell + totSup;

        row.querySelector('.em-total-cost').innerText = totCost.toFixed(2) + ' ج';
        row.querySelector('.em-total-sell').innerText = totSell.toFixed(2) + ' ج';
        calcEditMinvTotals();
    }

    function calcEditMinvTotals() {
        let grandCost = 0;
        let grandSelling = 0;

        document.querySelectorAll('#edit-minv-items-tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.em-qty').value) || 0;
            const cost = parseFloat(row.querySelector('.em-cost').value) || 0;
            const sup = parseFloat(row.querySelector('.em-sup').value) || 0;
            const sell = parseFloat(row.querySelector('.em-sell').value) || 0;
            const baseSell = sell > 0 ? sell : cost;
            const rowBaseSell = qty * baseSell;
            const rowSup = Math.round(rowBaseSell * (sup / 100) * 100) / 100;
            grandCost += (qty * cost);
            grandSelling += (rowBaseSell + rowSup);
        });

        const paid = parseFloat(document.getElementById('edit-minv-paid').value) || 0;
        const debt = Math.max(0, grandCost - paid);
        const profit = grandSelling - grandCost;

        document.getElementById('edit-minv-total-cost').innerText = grandCost.toFixed(2) + ' ج';
        document.getElementById('edit-minv-total-selling').innerText = grandSelling.toFixed(2) + ' ج';
        document.getElementById('edit-minv-total-profit').innerText = profit.toFixed(2) + ' ج';
        document.getElementById('edit-minv-debt-badge').innerText = debt.toFixed(2) + ' ج';
    }

    function saveEditMaterialInvoice() {
        const invoiceRef = document.getElementById('edit-minv-ref').value;
        const projectId = document.getElementById('edit-minv-project-id').value;
        const supplierName = document.getElementById('edit-minv-supplier').value.trim();
        const paidAmount = parseFloat(document.getElementById('edit-minv-paid').value) || 0;
        const accountId = document.getElementById('edit-minv-account').value || null;

        const items = [];
        document.querySelectorAll('#edit-minv-items-tbody tr').forEach(row => {
            const name = row.querySelector('.em-title').value.trim();
            const qty = parseFloat(row.querySelector('.em-qty').value) || 0;
            const unit = 'عدد';
            const cost = parseFloat(row.querySelector('.em-cost').value) || 0;
            const sup = parseFloat(row.querySelector('.em-sup').value) || 0;
            const sell = parseFloat(row.querySelector('.em-sell').value) || 0;

            if (name && qty > 0) {
                items.push({
                    name_or_title: name,
                    quantity: qty,
                    unit: unit,
                    cost_price: cost,
                    supervision_pct: sup,
                    selling_price: sell > 0 ? sell : cost
                });
            }
        });

        if (items.length === 0) {
            Swal.fire('تنبيه', 'يجب أن تحتوي الفاتورة على بند واحد على الأقل بكمية أكبر من صفر، أو استخدم زر حذف الفاتورة.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-save-edit-minv');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>جاري حفظ التعديل وتسوية الحسابات...';

        fetch('{{ route("contracting.material_invoice.update") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                invoice_ref: invoiceRef,
                project_id: projectId,
                supplier_name: supplierName,
                paid_amount: paidAmount,
                account_id: accountId,
                items: items
            })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-floppy-disk me-1"></i>حفظ التعديلات والمرتجعات';
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEditMaterialInvoice')).hide();
                Swal.fire('تم بنجاح', data.message, 'success').then(() => safeContractingReload());
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-floppy-disk me-1"></i>حفظ التعديلات والمرتجعات';
            Swal.fire('خطأ', 'تعذر الاتصال بالخادم', 'error');
        });
    }

    // ── Single Operation Edit Logic ──
    function openEditSingleOp(op) {
        document.getElementById('edit-op-id').value = op.id;
        document.getElementById('edit-op-name').value = op.name_or_title || '';
        document.getElementById('edit-op-qty').value = op.quantity || 1;
        document.getElementById('edit-op-cost').value = op.cost_price || 0;
        document.getElementById('edit-op-sup').value = op.supervision_pct || 0;
        document.getElementById('edit-op-sell').value = op.selling_price || op.cost_price || 0;
        document.getElementById('edit-op-notes').value = op.notes || '';

        const personSel = document.getElementById('edit-op-person');
        if (personSel) {
            let list = [];
            if (op.op_type === 'material') list = dbSuppliersList;
            else if (op.op_type === 'technician' || op.op_type === 'labor') list = dbTechsList;
            else list = [...new Set([...dbSuppliersList, ...dbTechsList])];

            personSel.innerHTML = '<option value="">-- اختر من قاعدة البيانات --</option>';
            list.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p;
                opt.textContent = p;
                if (op.person_name === p) opt.selected = true;
                personSel.appendChild(opt);
            });
            if (op.person_name && !list.includes(op.person_name)) {
                const opt = document.createElement('option');
                opt.value = op.person_name;
                opt.textContent = op.person_name;
                opt.selected = true;
                personSel.appendChild(opt);
            }
        }

        const sellRow = document.getElementById('edit-op-sell-row');
        if (sellRow) {
            sellRow.style.display = op.is_billable ? 'block' : 'none';
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditSingleOperation')).show();
    }

    function calcEditOpLive() {
        // Keep base selling price independent of supervision %
    }

    function onEditOpSupChange() {
        // Keep base selling price independent of supervision %
    }

    function onEditOpSellChange() {
        const sellInput = document.getElementById('edit-op-sell');
        if (sellInput) sellInput.dataset.userEdited = 'true';
    }

    function saveEditSingleOp() {
        const id = document.getElementById('edit-op-id').value;
        const payload = {
            name_or_title: document.getElementById('edit-op-name').value.trim(),
            quantity: parseFloat(document.getElementById('edit-op-qty').value) || 1,
            unit: 'عدد',
            cost_price: parseFloat(document.getElementById('edit-op-cost').value) || 0,
            supervision_pct: parseFloat(document.getElementById('edit-op-sup').value) || 0,
            selling_price: parseFloat(document.getElementById('edit-op-sell').value) || 0,
            person_name: document.getElementById('edit-op-person').value.trim(),
            notes: document.getElementById('edit-op-notes').value.trim(),
            selling_price_manual: true
        };

        if (!payload.name_or_title) {
            Swal.fire('تنبيه', 'يرجى إدخال اسم البند أو الحركة', 'warning');
            return;
        }

        fetch(`{{ url('/contracting/operation') }}/${id}/update`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEditSingleOperation')).hide();
                Swal.fire('تم بنجاح', data.message, 'success').then(() => {
                    const sel = document.getElementById('select-project-360');
                    if (sel && sel.value) loadProject360(sel.value);
                    else safeContractingReload();
                });
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        })
        .catch(err => Swal.fire('خطأ', 'تعذر الاتصال بالخادم', 'error'));
    }

    // ── 6. Modals Submissions ──
    function openReceivePaymentModal() {
        new bootstrap.Modal(document.getElementById('modalReceivePayment')).show();
    }
    function submitReceivePayment(e) {
        e.preventDefault();
        const projectId = document.getElementById('pay-project-id').value;
        const amount = document.getElementById('pay-amount').value;
        const accountId = document.getElementById('pay-account').value;
        const notes = document.getElementById('pay-notes').value;

        fetch('{{ route("contracting.payment.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ project_id: projectId, paid_amount: amount, account_id: accountId, notes: notes })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalReceivePayment')).hide();
                Swal.fire('تم بنجاح', data.message, 'success').then(() => loadProject360(projectId));
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    function openDiscountModal() {
        new bootstrap.Modal(document.getElementById('modalApplyDiscount')).show();
    }
    function submitDiscount(e) {
        e.preventDefault();
        const projectId = document.getElementById('disc-project-id').value;
        const amount = document.getElementById('disc-amount').value;
        const reason = document.getElementById('disc-reason').value;

        fetch('{{ route("contracting.discount.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ project_id: projectId, amount: amount, reason: reason })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalApplyDiscount')).hide();
                Swal.fire('تم بنجاح', data.message, 'success').then(() => loadProject360(projectId));
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    function openInstallmentModal() {
        new bootstrap.Modal(document.getElementById('modalConvertToInstallments')).show();
    }
    function calcMonthlyInstallment() {
        const total = parseFloat(document.getElementById('inst-amount').value) || 0;
        const months = parseInt(document.getElementById('inst-months').value) || 1;
        document.getElementById('inst-monthly').value = (total / months).toFixed(2);
    }
    function submitInstallmentPlan(e) {
        e.preventDefault();
        const projectId = document.getElementById('inst-project-id').value;
        const amount = document.getElementById('inst-amount').value;
        const months = document.getElementById('inst-months').value;
        const startDate = document.getElementById('inst-start-date').value;

        fetch('{{ route("contracting.installment.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ project_id: projectId, amount: amount, installment_months: months, start_date: startDate })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalConvertToInstallments')).hide();
                Swal.fire('تم الترحيل للأقساط', data.message, 'success').then(() => loadProject360(projectId));
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    // ── 7. Price Tracking & Supplier Comparison ──
    function filterMaterialsTable(query) {
        const q = query.trim().toLowerCase();
        const rows = document.querySelectorAll('#materials-prices-tbody .mat-price-row');
        let visibleCount = 0;

        rows.forEach(r => {
            const name = r.getAttribute('data-name') || '';
            if (!q || name.includes(q)) {
                r.style.display = '';
                visibleCount++;
            } else {
                r.style.display = 'none';
            }
        });

        const emptyRow = document.getElementById('empty-materials-row');
        if (emptyRow) {
            emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
        }
    }

    function showMaterialFullHistory(materialName) {
        document.getElementById('modal-mat-history-title').innerHTML = `
            <i class="fa fa-history text-primary me-2"></i>السجل الكامل لشراء: <span class="text-primary">${materialName}</span>
        `;
        const tbody = document.getElementById('modal-mat-history-tbody');
        tbody.innerHTML = `<tr><td colspan="9" class="py-4 text-center text-muted"><i class="fa fa-spinner fa-spin me-2"></i>جارٍ تحميل سجل العمليات والفواتير...</td></tr>`;

        new bootstrap.Modal(document.getElementById('modalMaterialHistory')).show();

        fetch(`{{ route("contracting.prices.history") }}?material=${encodeURIComponent(materialName)}&action=details`)
            .then(r => r.json())
            .then(data => {
                tbody.innerHTML = '';
                const history = data.history || [];

                if (history.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="9" class="py-4 text-center text-muted">لا توجد فواتير أو حركات شراء مسجلة لهذه الخامة</td></tr>`;
                    return;
                }

                history.forEach((h, idx) => {
                    const dateStr = h.created_at ? h.created_at.substring(0, 10) : '-';
                    const unitPrice = parseFloat(h.cost_price) || 0;
                    const qty = parseFloat(h.quantity) || 0;
                    const tot = parseFloat(h.total_cost) || (unitPrice * qty);

                    tbody.innerHTML += `
                        <tr>
                            <td class="text-muted fw-bold">${idx + 1}</td>
                            <td>${dateStr}</td>
                            <td class="text-start fw-bold text-dark">${h.project_name || 'عام / غير محدد'}</td>
                            <td class="text-secondary fw-bold">${h.supplier_name || 'مورد عام'}</td>
                            <td class="fw-black text-danger" dir="ltr">${unitPrice.toFixed(2)} ج</td>
                            <td class="fw-bold">${qty}</td>
                            <td><span class="badge bg-light text-secondary border">${h.unit || 'عدد'}</span></td>
                            <td class="fw-black text-dark" dir="ltr">${tot.toFixed(2)} ج</td>
                            <td><span class="badge bg-light text-dark border font-monospace">${h.invoice_ref || '-'}</span></td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="9" class="py-4 text-center text-danger">حدث خطأ أثناء تحميل السجل من الخادم</td></tr>`;
            });
    }

    function searchPriceHistory(material) {
        filterMaterialsTable(material || '');
    }

    function filterSupplierCompareCards() {
        const inp = document.getElementById('supplier-compare-search');
        const q = inp ? inp.value.trim().toLowerCase() : '';
        const cols = document.querySelectorAll('#supplier-cards-grid .supplier-comp-col');
        let visibleCount = 0;

        cols.forEach(col => {
            const matName = col.getAttribute('data-name') || '';
            const supNames = col.getAttribute('data-suppliers') || '';

            if (!q || matName.includes(q) || supNames.includes(q)) {
                col.style.display = '';
                visibleCount++;
            } else {
                col.style.display = 'none';
            }
        });

        const noRes = document.getElementById('no-search-results');
        if (noRes) {
            noRes.style.display = (visibleCount === 0 && cols.length > 0) ? 'block' : 'none';
        }
    }

    function compareSuppliers(material) {
        const inp = document.getElementById('supplier-compare-search');
        if (inp) inp.value = material || '';
        filterSupplierCompareCards();
    }

    // ── 8. Directory Handlers ──
    function filterDirectory(type, btn) {
        document.querySelectorAll('#directorySubTabs .nav-link').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        document.querySelectorAll('#table-directory tbody tr').forEach(row => {
            if (type === 'all' || row.getAttribute('data-type') === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function openQuickAddPersonModal(defaultType = 'supplier') {
        const typeEl = document.getElementById('dp-type');
        if (typeEl) typeEl.value = defaultType;
        const nameEl = document.getElementById('dp-name');
        if (nameEl) nameEl.value = '';
        const phoneEl = document.getElementById('dp-phone');
        if (phoneEl) phoneEl.value = '';
        const specEl = document.getElementById('dp-specialty');
        if (specEl) specEl.value = '';
        const notesEl = document.getElementById('dp-notes');
        if (notesEl) notesEl.value = '';

        const modalEl = document.getElementById('modalNewPerson');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            setTimeout(() => { if (nameEl) nameEl.focus(); }, 250);
        }
    }

    function submitDirectoryPerson(e) {
        e.preventDefault();
        const type = document.getElementById('dp-type').value;
        const name = document.getElementById('dp-name').value.trim();
        const phone = document.getElementById('dp-phone').value.trim();
        const specialty = document.getElementById('dp-specialty').value.trim();
        const notes = document.getElementById('dp-notes').value.trim();

        const payload = { type, name, phone, specialty, notes };

        fetch('{{ route("contracting.directory.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const modalInst = bootstrap.Modal.getInstance(document.getElementById('modalNewPerson'));
                if (modalInst) modalInst.hide();

                // تحديث القوائم المنسدلة والمصفوفات لحظياً بدون عمل Reload للصفحة حتى لا يضيع صندوق العمليات أو فاتورة الخامات!
                if (type === 'supplier') {
                    if (!dbSuppliersList.includes(name)) dbSuppliersList.push(name);
                    ['inv-supplier', 'edit-minv-supplier'].forEach(selId => {
                        const sel = document.getElementById(selId);
                        if (sel && !Array.from(sel.options).some(o => o.value === name)) {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name + (specialty ? ` (${specialty})` : '');
                            sel.appendChild(opt);
                        }
                    });
                    appendToDatalist('list-suppliers', name);
                    const invSup = document.getElementById('inv-supplier');
                    if (invSup && (!invSup.value || currentOpType === 'material')) {
                        invSup.value = name;
                        onSupplierChange(name);
                    }
                } else if (type === 'technician') {
                    if (!dbTechsList.includes(name)) dbTechsList.push(name);
                    const techSel = document.getElementById('tech-person');
                    if (techSel && !Array.from(techSel.options).some(o => o.value === name)) {
                        const opt = document.createElement('option');
                        opt.value = name;
                        opt.textContent = name + (specialty ? ` (${specialty})` : '');
                        techSel.appendChild(opt);
                    }
                    appendToDatalist('list-techs', name);
                    if (techSel && (!techSel.value || currentOpType === 'technician')) {
                        techSel.value = name;
                    }
                } else if (type === 'marketer') {
                    appendToDatalist('list-marketers', name);
                    const commInput = document.getElementById('comm-name');
                    if (commInput && (!commInput.value || currentOpType === 'commission')) {
                        commInput.value = name;
                    }
                } else if (type === 'client') {
                    appendToDatalist('list-clients', name);
                }

                // إضافة الصف الجديد في جدول سجل المتعاملين مباشرة
                const dirTbody = document.querySelector('#table-directory tbody');
                if (dirTbody) {
                    const badgeMap = {
                        supplier: '<span class="badge bg-primary">مورد خامات</span>',
                        technician: '<span class="badge bg-warning text-dark">صنايعي / مقاول</span>',
                        marketer: '<span class="badge bg-success">مسوق / وسيط</span>',
                        client: '<span class="badge bg-info text-dark">عميل مشروع</span>'
                    };
                    const stmtBtn = (type === 'supplier' || type === 'technician')
                        ? `<a href="{{ url('/contracting/person-statement') }}?type=${type}&name=${encodeURIComponent(name)}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-2 fw-bold me-1"><i class="fa fa-file-invoice me-1"></i>كشف حساب</a>`
                        : '';
                    const tr = document.createElement('tr');
                    tr.setAttribute('data-type', type);
                    tr.innerHTML = `
                        <td>${badgeMap[type] || type}</td>
                        <td class="fw-bold">${name}</td>
                        <td>${phone || '-'}</td>
                        <td>${specialty || '-'}</td>
                        <td class="fw-bold text-success">0.00 ج</td>
                        <td class="text-muted small">${notes || '-'}</td>
                        <td>${stmtBtn}<span class="badge bg-success-subtle text-success">مضاف حديثاً</span></td>
                    `;
                    dirTbody.prepend(tr);
                }

                // مسح حقول المودال
                document.getElementById('dp-name').value = '';
                document.getElementById('dp-phone').value = '';
                document.getElementById('dp-specialty').value = '';
                document.getElementById('dp-notes').value = '';

                if (typeof savePosDraftToStorage === 'function') savePosDraftToStorage();

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `تم تسجيل "${name}" في سجل المتعاملين بنجاح دون مسح بياناتك الحالية`,
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    function deleteDirectoryPerson(id) {
        Swal.fire({
            title: 'تأكيد الحذف',
            text: 'هل أنت متأكد من حذف هذه البيانات من الدليل؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف',
            cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/directory') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الحذف', data.message, 'success').then(() => safeContractingReload());
                    }
                });
            }
        });
    }

    // ── 9. Quotations Logic ──
    function addQuotationRow() {
        const container = document.getElementById('q-items-container');
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 q-item-row';
        row.innerHTML = `
            <div class="col-5"><input type="text" class="form-control form-control-sm rounded-3 q-item-title" list="list-items" placeholder="اسم البند..." required></div>
            <div class="col-3"><input type="number" class="form-control form-control-sm rounded-3 q-item-cost text-center" placeholder="التكلفة" step="any" oninput="calcQuotationLiveTotal()"></div>
            <div class="col-3"><input type="number" class="form-control form-control-sm rounded-3 q-item-price text-center" placeholder="سعر البيع" step="any" oninput="calcQuotationLiveTotal()"></div>
            <div class="col-1 text-center"><button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeQuotationRow(this)"><i class="fa fa-times"></i></button></div>
        `;
        container.appendChild(row);
    }

    function removeQuotationRow(btn) {
        const rows = document.querySelectorAll('.q-item-row');
        if (rows.length > 1) {
            btn.closest('.q-item-row').remove();
            calcQuotationLiveTotal();
        }
    }

    function calcQuotationLiveTotal() {
        let totalCost = 0;
        let totalSelling = 0;
        document.querySelectorAll('.q-item-row').forEach(row => {
            const cost = parseFloat(row.querySelector('.q-item-cost').value) || 0;
            const price = parseFloat(row.querySelector('.q-item-price').value) || 0;
            totalCost += cost;
            totalSelling += price;
        });
        document.getElementById('q-live-cost').innerText = totalCost.toFixed(2) + ' ج';
        document.getElementById('q-live-selling').innerText = totalSelling.toFixed(2) + ' ج';
    }

    function submitQuotation(e) {
        e.preventDefault();
        const items = [];
        document.querySelectorAll('.q-item-row').forEach(row => {
            items.push({
                item_title: row.querySelector('.q-item-title').value,
                cost: parseFloat(row.querySelector('.q-item-cost').value) || 0,
                price: parseFloat(row.querySelector('.q-item-price').value) || 0,
            });
        });

        const payload = {
            client_name: document.getElementById('q-client').value,
            client_phone: document.getElementById('q-phone').value,
            project_title: document.getElementById('q-title').value,
            supervision_pct: document.getElementById('q-supervision').value,
            items: items,
        };

        fetch('{{ route("contracting.quotation.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('تم الحفظ', data.message, 'success').then(() => safeContractingReload());
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    function convertQuotationToProject(id) {
        Swal.fire({
            title: 'تحويل عرض السعر لمشروع فعلي',
            text: 'سيتم إنشاء المشروع وتجهيز كافة بنوده في النظام بضغطة واحدة!',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، تحويل للمشاريع',
            cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/quotation') }}/${id}/convert`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم التحويل!', data.message, 'success').then(() => safeContractingReload());
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                });
            }
        });
    }

    function filterProjectCards(query) {
        const q = (query || '').trim().toLowerCase();
        document.querySelectorAll('#projects-grid .pos-btn').forEach(card => {
            const text = (card.getAttribute('data-name') || '').toLowerCase();
            card.style.display = (!q || text.includes(q)) ? 'flex' : 'none';
        });
    }

    // ── 10. Settings Tab Operations (المشاريع، البنود، والنثريات) ──
    function deleteProject(id, name) {
        Swal.fire({
            title: 'تأكيد حذف المشروع',
            text: `هل أنت متأكد من حذف مشروع "${name}" وكافة بنوده؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف المشروع',
            cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/project') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الحذف', data.message, 'success').then(() => safeContractingReload());
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                });
            }
        });
    }

    function submitGlobalItem(e) {
        e.preventDefault();
        const title = document.getElementById('setting-item-title').value.trim();
        const applyToAll = document.getElementById('setting-item-apply').checked;

        fetch('{{ route("contracting.global_items.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ title: title, apply_to_all_projects: applyToAll })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('تمت الإضافة', data.message, 'success').then(() => safeContractingReload());
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    function deleteGlobalItem(id) {
        Swal.fire({
            title: 'حذف البند من المكتبة',
            text: 'هل تريد حذف هذا البند من قائمة البنود المقترحة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف',
            cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/global-items') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الحذف', data.message, 'success').then(() => safeContractingReload());
                    }
                });
            }
        });
    }

    function showAddPettyCategory() {
        Swal.fire({
            title: 'إضافة تصنيف نثريات جديد',
            input: 'text',
            inputPlaceholder: 'مثال: شاي وضيافة، انتقالات...',
            showCancelButton: true,
            confirmButtonText: 'حفظ',
            cancelButtonText: 'إلغاء',
            inputValidator: (v) => !v && 'اسم التصنيف مطلوب'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch('{{ route("contracting.petty_categories.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: res.value })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الحفظ', data.message, 'success').then(() => safeContractingReload());
                    }
                });
            }
        });
    }

    function deletePettyCategory(id) {
        Swal.fire({
            title: 'حذف تصنيف النثريات',
            text: 'هل أنت متأكد من حذف هذا التصنيف؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف',
            cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/petty-categories') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الحذف', data.message, 'success').then(() => safeContractingReload());
                    }
                });
            }
        });
    }

    function submitPettyExpense(e) {
        e.preventDefault();
        const titleEl = document.getElementById('setting-petty-record-title') || document.getElementById('petty-title');
        const payload = {
            title: titleEl ? titleEl.value : '',
            amount: document.getElementById('petty-amount').value,
            category_id: document.getElementById('petty-category').value,
            account_id: document.getElementById('petty-account').value,
        };

        fetch('{{ route("contracting.petty_expenses.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('تم الصرف', data.message, 'success').then(() => safeContractingReload());
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        });
    }

    function deletePettyExpense(id) {
        Swal.fire({
            title: 'حذف حركة النثريات',
            text: 'هل تريد حذف هذا السجل واستعادة الرصيد بالخزينة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف واستعادة',
            cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/petty-expenses') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('تم الحذف', data.message, 'success').then(() => safeContractingReload());
                    }
                });
            }
        });
    }

    // ══ Auxiliary Settings Management: Petty Titles, Expense Titles, and Units Library ══
    function addNewPettyTitle() {
        const input = document.getElementById('new-petty-title-input');
        const val = (input ? input.value : '').trim();
        if (!val) {
            Swal.fire('تنبيه', 'برجاء كتابة اسم بند النثريات أولاً', 'warning');
            return;
        }
        fetch('{{ route("contracting.petty_titles.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ title: val })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                input.value = '';
                appendToDatalist('list-petty-titles', data.title);
                const container = document.getElementById('petty-titles-tags');
                if (container) {
                    container.innerHTML += `
                        <span class="badge bg-white text-dark border p-2 rounded-pill d-inline-flex align-items-center gap-2 shadow-sm" id="badge-petty-title-${data.id}">
                            <i class="fa fa-tag text-info"></i>${data.title}
                            <i class="fa fa-times text-danger" style="cursor: pointer;" onclick="deletePettyTitle(${data.id})" title="حذف"></i>
                        </span>`;
                }
                const countBadge = document.getElementById('petty-titles-count');
                if (countBadge) {
                    const currentCount = parseInt(countBadge.innerText) || 0;
                    countBadge.innerText = (currentCount + 1) + ' اسم';
                }
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            } else {
                Swal.fire('خطأ', data.message || 'تعذر إضافة الاسم', 'error');
            }
        });
    }

    function deletePettyTitle(id) {
        Swal.fire({
            title: 'حذف اسم النثريات',
            text: 'هل أنت متأكد من حذف اسم بند النثريات من القائمة المقترحة؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`{{ url('/contracting/petty-titles') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    const el = document.getElementById(`badge-petty-title-${id}`);
                    if (el) el.remove();
                    const countBadge = document.getElementById('petty-titles-count');
                    if (countBadge) {
                        const currentCount = Math.max(0, (parseInt(countBadge.innerText) || 1) - 1);
                        countBadge.innerText = currentCount + ' اسم';
                    }
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                }
            });
        });
    }

    function addNewExpenseTitle() {
        const input = document.getElementById('new-expense-title-input');
        const val = (input ? input.value : '').trim();
        if (!val) {
            Swal.fire('تنبيه', 'برجاء كتابة بند المصروف أولاً', 'warning');
            return;
        }
        fetch('{{ route("contracting.expense_titles.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ title: val })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                input.value = '';
                appendToDatalist('list-expense-titles', data.title);
                const container = document.getElementById('expense-titles-tags');
                if (container) {
                    container.innerHTML += `
                        <span class="badge bg-white text-dark border p-2 rounded-pill d-inline-flex align-items-center gap-2 shadow-sm" id="badge-expense-title-${data.id}">
                            <i class="fa fa-receipt text-danger"></i>${data.title}
                            <i class="fa fa-times text-danger" style="cursor: pointer;" onclick="deleteExpenseTitle(${data.id})" title="حذف"></i>
                        </span>`;
                }
                const countBadge = document.getElementById('expense-titles-count');
                if (countBadge) {
                    const currentCount = parseInt(countBadge.innerText) || 0;
                    countBadge.innerText = (currentCount + 1) + ' بند';
                }
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            } else {
                Swal.fire('خطأ', data.message || 'تعذر إضافة البند', 'error');
            }
        });
    }

    function deleteExpenseTitle(id) {
        Swal.fire({
            title: 'حذف بند المصروفات',
            text: 'هل أنت متأكد من حذف هذا البند من القائمة المقترحة؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`{{ url('/contracting/expense-titles') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    const el = document.getElementById(`badge-expense-title-${id}`);
                    if (el) el.remove();
                    const countBadge = document.getElementById('expense-titles-count');
                    if (countBadge) {
                        const currentCount = Math.max(0, (parseInt(countBadge.innerText) || 1) - 1);
                        countBadge.innerText = currentCount + ' بند';
                    }
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                }
            });
        });
    }

    function addNewUnit() {
        const input = document.getElementById('new-unit-input');
        const val = (input ? input.value : '').trim();
        if (!val) {
            Swal.fire('تنبيه', 'برجاء كتابة اسم وحدة القياس أولاً', 'warning');
            return;
        }
        fetch('{{ route("contracting.units.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name: val })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                input.value = '';
                appendToDatalist('list-units', data.name);
                const container = document.getElementById('units-tags');
                if (container) {
                    container.innerHTML += `
                        <span class="badge bg-white text-dark border p-2 rounded-pill d-inline-flex align-items-center gap-2 shadow-sm" id="badge-unit-${data.id}">
                            <i class="fa fa-ruler text-success"></i>${data.name}
                            <i class="fa fa-times text-danger" style="cursor: pointer;" onclick="deleteUnit(${data.id})" title="حذف"></i>
                        </span>`;
                }
                const countBadge = document.getElementById('units-count');
                if (countBadge) {
                    const currentCount = parseInt(countBadge.innerText) || 0;
                    countBadge.innerText = (currentCount + 1) + ' وحدة';
                }
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
            } else {
                Swal.fire('خطأ', data.message || 'تعذر حفظ الوحدة', 'error');
            }
        });
    }

    function deleteUnit(id) {
        Swal.fire({
            title: 'حذف وحدة القياس',
            text: 'هل أنت متأكد من حذف هذه الوحدة من القائمة؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`{{ url('/contracting/units') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    const el = document.getElementById(`badge-unit-${id}`);
                    if (el) el.remove();
                    const countBadge = document.getElementById('units-count');
                    if (countBadge) {
                        const currentCount = Math.max(0, (parseInt(countBadge.innerText) || 1) - 1);
                        countBadge.innerText = currentCount + ' وحدة';
                    }
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                }
            });
        });
    }

    // ══ Filter Projects By Client in Tab-Projects ══
    function filterProjectsByClient(clientName) {
        const wrapper = document.getElementById('chips-projects-wrapper');
        let firstMatchedProjectId = null;

        // Filter dropdown items as well
        let visibleCount = 0;
        document.querySelectorAll('.project-search-item').forEach(el => {
            const c = el.getAttribute('data-client') || '';
            const match = (clientName === 'all' || !clientName || c === clientName);
            el.style.display = match ? 'flex' : 'none';
            if (match) visibleCount++;
        });
        const noRes = document.getElementById('project-search-no-results');
        if (noRes) noRes.style.display = visibleCount === 0 ? 'block' : 'none';

        // Show project chips ONLY when a specific client is chosen (keep hidden when 'all' to preserve clean layout)
        if (!clientName || clientName === 'all') {
            if (wrapper) wrapper.style.display = 'none';
            return;
        }

        let matchCount = 0;
        document.querySelectorAll('.btn-360-chip').forEach(chip => {
            const c = chip.getAttribute('data-client');
            if (c === clientName) {
                chip.style.display = 'inline-flex';
                matchCount++;
                if (!firstMatchedProjectId) {
                    firstMatchedProjectId = chip.id.replace('chip-360-', '');
                }
            } else {
                chip.style.display = 'none';
            }
        });

        if (wrapper) {
            wrapper.style.display = matchCount > 0 ? 'block' : 'none';
        }

        if (firstMatchedProjectId) {
            select360Project(firstMatchedProjectId);
        }
    }

    // ════════════════════════════════════════════════════════════════
    // 11. SURVEY & AREA CALCULATOR (حاسبة ومقايسة المساحات والسجل)
    // ════════════════════════════════════════════════════════════════
    function switchSurveySubtab(sub) {
        const viewCalc = document.getElementById('survey-view-calc');
        const viewLog = document.getElementById('survey-view-log');
        const btnCalc = document.getElementById('survey-pill-calc');
        const btnLog = document.getElementById('survey-pill-log');

        if (sub === 'calc') {
            if (viewCalc) viewCalc.style.display = 'block';
            if (viewLog) viewLog.style.display = 'none';
            if (btnCalc) { btnCalc.classList.add('btn-primary', 'active'); btnCalc.classList.remove('btn-light', 'text-dark'); }
            if (btnLog) { btnLog.classList.remove('btn-primary', 'active'); btnLog.classList.add('btn-light', 'text-dark'); }
        } else {
            if (viewCalc) viewCalc.style.display = 'none';
            if (viewLog) viewLog.style.display = 'block';
            if (btnLog) { btnLog.classList.add('btn-primary', 'active'); btnLog.classList.remove('btn-light', 'text-dark'); }
            if (btnCalc) { btnCalc.classList.remove('btn-primary', 'active'); btnCalc.classList.add('btn-light', 'text-dark'); }
        }
    }

    let surveyRoomCounter = 0;

    function addSurveyRoom(name = '', type = 'room', len = '', wid = '', openings = '') {
        surveyRoomCounter++;
        const defaultName = name || `اوضه ${surveyRoomCounter}`;
        const tbody = document.getElementById('survey-rooms-body');
        if (!tbody) return;

        const tr = document.createElement('tr');
        tr.className = 'survey-room-row';
        tr.innerHTML = `
            <td class="text-start">
                <input type="text" class="form-control rounded-3 bg-light border-0 py-2 fw-bold room-name" value="${defaultName}" placeholder="اسم المساحة...">
            </td>
            <td>
                <select class="form-select rounded-3 bg-light border-0 py-2 room-type" onchange="calcSurveyRow(this.closest('tr'))">
                    <option value="room" ${type === 'room' ? 'selected' : ''}>حجرة / صالة</option>
                    <option value="bath" ${type === 'bath' ? 'selected' : ''}>حمام / مطبخ</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control rounded-3 bg-light border-0 py-2 text-center fw-bold room-len" value="${len}" placeholder="0" step="0.05" oninput="calcSurveyRow(this.closest('tr'))">
            </td>
            <td>
                <input type="number" class="form-control rounded-3 bg-light border-0 py-2 text-center fw-bold room-wid" value="${wid}" placeholder="0" step="0.05" oninput="calcSurveyRow(this.closest('tr'))">
            </td>
            <td>
                <input type="number" class="form-control rounded-3 bg-light border-0 py-2 text-center room-openings" value="${openings}" placeholder="خصم م²" step="0.05" oninput="calcSurveyRow(this.closest('tr'))">
            </td>
            <td class="text-center">
                <div class="room-results p-2 rounded-3 bg-light fw-bold font-monospace text-center small text-nowrap">--</div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" onclick="removeSurveyRoom(this)">
                    حذف
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        calcSurveyRow(tr);
    }

    function removeSurveyRoom(btn) {
        const tr = btn.closest('tr');
        if (tr) {
            tr.remove();
            recalcSurveyTotals();
        }
    }

    function calcSurveyRow(tr) {
        if (!tr) return;
        const hInp = document.getElementById('survey-height');
        const H = parseFloat(hInp ? hInp.value : 2.8) || 2.8;

        const lenInp = tr.querySelector('.room-len');
        const widInp = tr.querySelector('.room-wid');
        const opInp = tr.querySelector('.room-openings');
        const typeSel = tr.querySelector('.room-type');
        const resDiv = tr.querySelector('.room-results');

        const L = parseFloat(lenInp ? lenInp.value : 0) || 0;
        const W = parseFloat(widInp ? widInp.value : 0) || 0;
        const O = parseFloat(opInp ? opInp.value : 0) || 0;
        const type = typeSel ? typeSel.value : 'room';

        const floorArea = L * W;
        const ceilingArea = floorArea;
        const perimeter = 2 * (L + W);
        const rawWallArea = perimeter * H;
        const netWallArea = Math.max(0, rawWallArea - O);

        tr.dataset.floor = floorArea;
        tr.dataset.ceiling = ceilingArea;
        tr.dataset.wall = netWallArea;
        tr.dataset.openings = O;
        tr.dataset.type = type;

        if (resDiv) {
            if (type === 'room') {
                resDiv.innerHTML = `<span class="text-primary">حوائط: <strong>${netWallArea.toFixed(2)} م²</strong></span> | <span class="text-secondary">سقف: <strong>${ceilingArea.toFixed(2)} م²</strong></span> | <span class="text-success">أرضية: <strong>${floorArea.toFixed(2)} م²</strong></span>`;
            } else {
                resDiv.innerHTML = `<span class="text-success">أرضية: <strong>${floorArea.toFixed(2)} م²</strong></span> | <span class="text-primary">حوائط: <strong>${netWallArea.toFixed(2)} م²</strong></span>`;
            }
        }

        recalcSurveyTotals();
    }

    function recalcSurveyAll() {
        document.querySelectorAll('#survey-rooms-body .survey-room-row').forEach(tr => calcSurveyRow(tr));
    }

    function recalcSurveyTotals() {
        let totalRoomWalls = 0;
        let totalCeramicWalls = 0;
        let totalFloors = 0;
        let totalDeductions = 0;

        document.querySelectorAll('#survey-rooms-body .survey-room-row').forEach(tr => {
            const floor = parseFloat(tr.dataset.floor) || 0;
            const wall = parseFloat(tr.dataset.wall) || 0;
            const openings = parseFloat(tr.dataset.openings) || 0;
            const type = tr.dataset.type || 'room';

            totalFloors += floor;
            totalDeductions += openings;

            if (type === 'room') {
                totalRoomWalls += wall;
            } else {
                totalCeramicWalls += wall;
            }
        });

        const elRoomWalls = document.getElementById('survey-tot-room-walls');
        const elFloors = document.getElementById('survey-tot-floors');
        const elCeramic = document.getElementById('survey-tot-ceramic-walls');
        const elDeduct = document.getElementById('survey-tot-deductions');

        if (elRoomWalls) elRoomWalls.innerHTML = `${totalRoomWalls.toFixed(2)} <span class="fs-6 text-muted">م²</span>`;
        if (elFloors) elFloors.innerHTML = `${totalFloors.toFixed(2)} <span class="fs-6 text-muted">م²</span>`;
        if (elCeramic) elCeramic.innerHTML = `${totalCeramicWalls.toFixed(2)} <span class="fs-6 text-muted">م²</span>`;
        if (elDeduct) elDeduct.innerHTML = `${totalDeductions.toFixed(2)} <span class="fs-6 text-muted">م²</span>`;

        // Ceilings = floors of rooms only (حمامات ومطابخ غالباً أسقف جبس/معلقة)
        let totalRoomCeilings = 0;
        document.querySelectorAll('#survey-rooms-body .survey-room-row').forEach(tr => {
            if ((tr.dataset.type || 'room') === 'room') totalRoomCeilings += parseFloat(tr.dataset.ceiling) || 0;
        });

        window.surveyAreas = {
            room_walls: totalRoomWalls,
            ceilings: totalRoomCeilings,
            floors: totalFloors,
            ceramic_walls: totalCeramicWalls
        };
        recalcSurveyPricing();
    }

    function fmtMoney(v) {
        return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcSurveyPricing() {
        const areas = window.surveyAreas || {};
        let totLabor = 0, totMaterial = 0;
        document.querySelectorAll('#table-survey-pricing .survey-price-row').forEach(tr => {
            const key = tr.dataset.key;
            const area = parseFloat(areas[key]) || 0;
            const labor = parseFloat(tr.querySelector('.price-labor').value) || 0;
            const material = parseFloat(tr.querySelector('.price-material').value) || 0;
            const lt = area * labor, mt = area * material;
            totLabor += lt; totMaterial += mt;
            tr.querySelector('.price-area').innerText = area.toFixed(2);
            tr.querySelector('.price-labor-total').innerText = fmtMoney(lt);
            tr.querySelector('.price-material-total').innerText = fmtMoney(mt);
            tr.querySelector('.price-row-total').innerText = fmtMoney(lt + mt);
        });
        const elL = document.getElementById('survey-total-labor');
        const elM = document.getElementById('survey-total-material');
        const elT = document.getElementById('survey-estimated-total');
        if (elL) elL.innerHTML = `${fmtMoney(totLabor)} <span class="fs-6 text-muted">ج</span>`;
        if (elM) elM.innerHTML = `${fmtMoney(totMaterial)} <span class="fs-6 text-muted">ج</span>`;
        if (elT) elT.innerHTML = `${fmtMoney(totLabor + totMaterial)} <span class="fs-6">ج</span>`;
        window.surveyPricingTotals = { labor: totLabor, material: totMaterial, total: totLabor + totMaterial };
    }

    function collectSurveyPricing() {
        const areas = window.surveyAreas || {};
        const out = [];
        document.querySelectorAll('#table-survey-pricing .survey-price-row').forEach(tr => {
            out.push({
                key: tr.dataset.key,
                label: tr.dataset.label,
                area: parseFloat(areas[tr.dataset.key]) || 0,
                labor: parseFloat(tr.querySelector('.price-labor').value) || 0,
                material: parseFloat(tr.querySelector('.price-material').value) || 0
            });
        });
        return out;
    }

    function fillSurveyPricing(pricing) {
        const map = {};
        (pricing || []).forEach(p => { if (p && p.key) map[p.key] = p; });
        document.querySelectorAll('#table-survey-pricing .survey-price-row').forEach(tr => {
            const p = map[tr.dataset.key];
            tr.querySelector('.price-labor').value = p && p.labor ? p.labor : '';
            tr.querySelector('.price-material').value = p && p.material ? p.material : '';
        });
        recalcSurveyPricing();
    }

    function setSurveyEditMode(on) {
        const b = document.getElementById('survey-edit-badge');
        if (b) b.style.display = on ? 'block' : 'none';
    }

    function resetSurveyCalc() {
        const editIdInp = document.getElementById('survey-edit-id');
        if (editIdInp) editIdInp.value = '';
        const titleInp = document.getElementById('survey-title');
        if (titleInp) titleInp.value = '';
        const prjSel = document.getElementById('survey-project-id');
        if (prjSel) prjSel.value = '';
        const hInp = document.getElementById('survey-height');
        if (hInp) hInp.value = '2.8';

        const tbody = document.getElementById('survey-rooms-body');
        if (tbody) tbody.innerHTML = '';
        surveyRoomCounter = 0;

        // Add 2 default starter rows exactly like in the user's reference image
        addSurveyRoom('اوضه 1', 'room', 5, 5, 0);
        addSurveyRoom('اوضه 5', 'bath', 8, 9, 0);
        fillSurveyPricing([]);
        setSurveyEditMode(false);
    }

    function saveSurveyToLog() {
        const rows = document.querySelectorAll('#survey-rooms-body .survey-room-row');
        if (rows.length === 0) {
            Swal.fire('تنبيه', 'برجاء إضافة مساحة واحدة على الأقل قبل الحفظ', 'warning');
            return;
        }

        const roomsData = [];
        let totalRoomWalls = 0;
        let totalCeramicWalls = 0;
        let totalFloors = 0;
        let totalDeductions = 0;

        rows.forEach(tr => {
            const name = tr.querySelector('.room-name')?.value || '';
            const type = tr.querySelector('.room-type')?.value || 'room';
            const len = parseFloat(tr.querySelector('.room-len')?.value) || 0;
            const wid = parseFloat(tr.querySelector('.room-wid')?.value) || 0;
            const openings = parseFloat(tr.querySelector('.room-openings')?.value) || 0;
            const wall = parseFloat(tr.dataset.wall) || 0;
            const floor = parseFloat(tr.dataset.floor) || 0;
            const ceiling = parseFloat(tr.dataset.ceiling) || 0;

            roomsData.push({
                name, type, len, wid, openings, wall, floor, ceiling
            });

            totalFloors += floor;
            totalDeductions += openings;
            if (type === 'room') totalRoomWalls += wall;
            else totalCeramicWalls += wall;
        });

        const id = document.getElementById('survey-edit-id')?.value || null;
        const title = document.getElementById('survey-title')?.value.trim() || 'مقايسة مساحات';
        const project_id = document.getElementById('survey-project-id')?.value || null;
        const ceiling_height = parseFloat(document.getElementById('survey-height')?.value) || 2.8;

        const payload = {
            id: id || null,
            title: title,
            project_id: project_id || null,
            ceiling_height: ceiling_height,
            total_room_walls: totalRoomWalls,
            total_floors: totalFloors,
            total_ceramic_walls: totalCeramicWalls,
            total_deductions: totalDeductions,
            rooms_data: JSON.stringify(roomsData),
            pricing_data: JSON.stringify(collectSurveyPricing()),
            total_labor_cost: (window.surveyPricingTotals || {}).labor || 0,
            total_material_cost: (window.surveyPricingTotals || {}).material || 0,
            estimated_total: (window.surveyPricingTotals || {}).total || 0
        };

        fetch('{{ route("contracting.survey.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('survey-edit-id').value = data.survey.id;
                setSurveyEditMode(true);
                Swal.fire({
                    icon: 'success',
                    title: 'تم الحفظ في السجل!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Update or prepend row in log table
                updateSurveyLogRow(data.survey);

                // Switch to log subtab so the user sees their saved survey immediately
                setTimeout(() => {
                    switchSurveySubtab('log');
                }, 800);
            } else {
                Swal.fire('خطأ', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('خطأ', 'تعذر الاتصال بالخادم لحفظ المقايسة', 'error');
        });
    }

    function updateSurveyLogRow(survey) {
        const tbody = document.getElementById('survey-log-body');
        if (!tbody) return;

        const emptyRow = document.getElementById('survey-empty-log-row');
        if (emptyRow) emptyRow.remove();

        const existingRow = document.getElementById(`survey-row-${survey.id}`);
        const dateStr = survey.created_at ? new Date(survey.created_at).toISOString().slice(0, 16).replace('T', ' ') : 'الآن';

        const rowHtml = `
            <td class="text-start fw-bold text-dark fs-6">${survey.title}</td>
            <td class="fw-bold text-warning-emphasis" dir="ltr">${Number(survey.total_room_walls).toFixed(2)} م²</td>
            <td class="fw-bold text-primary" dir="ltr">${Number(survey.total_floors).toFixed(2)} م²</td>
            <td class="fw-bold text-success" dir="ltr">${Number(survey.total_ceramic_walls).toFixed(2)} م²</td>
            <td class="text-muted" dir="ltr">${Number(survey.total_deductions).toFixed(2)} م²</td>
            <td class="fw-black text-dark">${Number(survey.estimated_total || 0) > 0 ? fmtMoney(survey.estimated_total) + ' ج' : '—'}</td>
            <td class="small text-muted">${dateStr}</td>
            <td>
                <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 fw-bold" onclick="loadSurveyInCalc(${survey.id})" title="استرجاع وتعديل في الحاسبة">
                        <i class="fa fa-pen-to-square"></i> تعديل / عرض
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2" onclick="printSavedSurvey(${survey.id})" title="طباعة المقايسة">
                        <i class="fa fa-print"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-2" onclick="deleteSurvey(${survey.id})" title="حذف">
                        حذف
                    </button>
                </div>
            </td>
        `;

        if (existingRow) {
            existingRow.innerHTML = rowHtml;
        } else {
            const tr = document.createElement('tr');
            tr.id = `survey-row-${survey.id}`;
            tr.innerHTML = rowHtml;
            tbody.prepend(tr);

            const countEl = document.getElementById('survey-log-count');
            if (countEl) countEl.innerText = parseInt(countEl.innerText || 0) + 1;
        }
    }

    function loadSurveyInCalc(id) {
        fetch(`{{ url('/contracting/survey') }}/${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const s = data.survey;
                    const rooms = data.rooms || [];

                    document.getElementById('survey-edit-id').value = s.id;
                    document.getElementById('survey-title').value = s.title;
                    const prjSel = document.getElementById('survey-project-id');
                    if (prjSel) prjSel.value = s.project_id || '';
                    document.getElementById('survey-height').value = s.ceiling_height;

                    const tbody = document.getElementById('survey-rooms-body');
                    if (tbody) tbody.innerHTML = '';
                    surveyRoomCounter = 0;

                    if (rooms.length > 0) {
                        rooms.forEach(r => {
                            addSurveyRoom(r.name, r.type, r.len, r.wid, r.openings);
                        });
                    } else {
                        addSurveyRoom('اوضه 1', 'room', 0, 0, 0);
                    }
                    fillSurveyPricing(data.pricing || []);
                    setSurveyEditMode(true);

                    switchSurveySubtab('calc');
                    Swal.fire({
                        icon: 'info',
                        title: 'تم استرجاع المقايسة',
                        text: `تم تحميل "${s.title}" في الحاسبة بنجاح للتحرير والتعديل`,
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('خطأ', 'تعذر استرجاع المقايسة من السيرفر', 'error');
            });
    }

    function deleteSurvey(id) {
        Swal.fire({
            title: 'تأكيد الحذف',
            text: 'هل أنت متأكد من حذف هذه المقايسة من السجل نهائياً؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، حذف',
            cancelButtonText: 'إلغاء'
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`{{ url('/contracting/survey') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tr = document.getElementById(`survey-row-${id}`);
                        if (tr) tr.remove();
                        const countEl = document.getElementById('survey-log-count');
                        if (countEl) countEl.innerText = Math.max(0, parseInt(countEl.innerText || 1) - 1);
                        Swal.fire('تم الحذف', data.message, 'success');
                    } else {
                        Swal.fire('خطأ', data.message, 'error');
                    }
                });
            }
        });
    }

    function printCurrentSurvey() {
        const title = document.getElementById('survey-title')?.value.trim() || 'مقايسة مساحات';
        const height = document.getElementById('survey-height')?.value || 2.8;
        const rows = document.querySelectorAll('#survey-rooms-body .survey-room-row');

        let roomsHtml = '';
        rows.forEach((tr, i) => {
            const name = tr.querySelector('.room-name')?.value || `مساحة ${i+1}`;
            const type = tr.querySelector('.room-type')?.value === 'room' ? 'حجرة / صالة' : 'حمام / مطبخ';
            const len = tr.querySelector('.room-len')?.value || 0;
            const wid = tr.querySelector('.room-wid')?.value || 0;
            const openings = tr.querySelector('.room-openings')?.value || 0;
            const wall = tr.dataset.wall || 0;
            const floor = tr.dataset.floor || 0;

            roomsHtml += `
                <tr>
                    <td>${i+1}</td>
                    <td class="text-start fw-bold">${name}</td>
                    <td>${type}</td>
                    <td>${len} × ${wid} م</td>
                    <td>${openings} م²</td>
                    <td class="fw-bold">${parseFloat(wall).toFixed(2)} م²</td>
                    <td class="fw-bold">${parseFloat(floor).toFixed(2)} م²</td>
                </tr>
            `;
        });

        const totWalls = document.getElementById('survey-tot-room-walls')?.innerText || '0.00 م²';
        const totFloors = document.getElementById('survey-tot-floors')?.innerText || '0.00 م²';
        const totCeramic = document.getElementById('survey-tot-ceramic-walls')?.innerText || '0.00 م²';
        const totDeduct = document.getElementById('survey-tot-deductions')?.innerText || '0.00 م²';

        const pricingItems = collectSurveyPricing();
        const pricingTotals = window.surveyPricingTotals || { labor: 0, material: 0, total: 0 };
        let pricingSectionHtml = '';
        if (pricingTotals.total > 0) {
            let pRowsHtml = '';
            pricingItems.forEach((p, idx) => {
                if (p.area <= 0 || (p.labor + p.material) <= 0) return;
                const lCost = p.area * p.labor;
                const mCost = p.area * p.material;
                const subTot = lCost + mCost;
                pRowsHtml += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td class="text-start fw-bold">${p.label}</td>
                        <td class="fw-bold">${Number(p.area).toFixed(2)} م²</td>
                        <td>${fmtMoney(p.labor)} ج</td>
                        <td>${fmtMoney(p.material)} ج</td>
                        <td class="fw-bold">${fmtMoney(lCost)} ج</td>
                        <td class="fw-bold">${fmtMoney(mCost)} ج</td>
                        <td class="fw-black text-primary">${fmtMoney(subTot)} ج</td>
                    </tr>
                `;
            });
            pricingSectionHtml = `
                <h6 class="fw-black text-dark mt-4 mb-2"><i class="fa fa-coins text-warning me-1"></i>حساب التكلفة التقريبية للمقايسة (مصنعية + خامات)</h6>
                <table class="table table-bordered text-center align-middle mb-3">
                    <thead class="table-dark">
                        <tr>
                            <th>م</th>
                            <th class="text-start">بند الأعمال</th>
                            <th>المساحة الصافية</th>
                            <th>سعر م² مصنعية</th>
                            <th>تكلفة م² خامات</th>
                            <th>إجمالي المصنعية</th>
                            <th>إجمالي الخامات</th>
                            <th>الإجمالي التقريبي</th>
                        </tr>
                    </thead>
                    <tbody>${pRowsHtml}</tbody>
                    <tfoot class="table-light fw-black">
                        <tr>
                            <td colspan="5" class="text-start">السعر التقريبي الإجمالي للمقايسة</td>
                            <td class="text-primary">${fmtMoney(pricingTotals.labor)} ج</td>
                            <td class="text-warning-emphasis">${fmtMoney(pricingTotals.material)} ج</td>
                            <td class="text-success fs-6">${fmtMoney(pricingTotals.total)} ج.م</td>
                        </tr>
                    </tfoot>
                </table>
            `;
        }

        const content = `
            <div id="survey-printable-area" class="p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div>
                        <h4 class="fw-black text-dark m-0">${title}</h4>
                        <div class="text-muted small">تاريخ الكشف: ${new Date().toLocaleDateString('ar-EG')} | ارتفاع السقف: ${height} متر</div>
                    </div>
                    <div class="badge bg-primary fs-6">حاسبة المقايسة والمساحات</div>
                </div>

                <table class="table table-bordered text-center align-middle mb-4">
                    <thead class="table-dark">
                        <tr>
                            <th>م</th>
                            <th class="text-start">اسم المساحة</th>
                            <th>النوع</th>
                            <th>الأبعاد</th>
                            <th>الفتحات (خصم)</th>
                            <th>الحوائط الصافية</th>
                            <th>الأرضيات / الأسقف</th>
                        </tr>
                    </thead>
                    <tbody>${roomsHtml}</tbody>
                </table>

                <div class="row g-2 text-center">
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>حوائط الغرف:</strong> ${totWalls}</div></div>
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>الأرضيات = الأسقف:</strong> ${totFloors}</div></div>
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>حوائط سيراميك:</strong> ${totCeramic}</div></div>
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>إجمالي الخصومات:</strong> ${totDeduct}</div></div>
                </div>
                ${pricingSectionHtml}
            </div>
        `;

        document.getElementById('modal-survey-print-title').innerHTML = `<i class="fa fa-calculator text-primary me-2"></i>${title}`;
        document.getElementById('modal-survey-print-content').innerHTML = content;
        new bootstrap.Modal(document.getElementById('modalPrintSurvey')).show();
    }

    function printSavedSurvey(id) {
        fetch(`{{ url('/contracting/survey') }}/${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const s = data.survey;
                    const rooms = data.rooms || [];
                    const pricing = data.pricing || [];

                    let roomsHtml = '';
                    rooms.forEach((r, i) => {
                        const type = r.type === 'room' ? 'حجرة / صالة' : 'حمام / مطبخ';
                        roomsHtml += `
                            <tr>
                                <td>${i+1}</td>
                                <td class="text-start fw-bold">${r.name}</td>
                                <td>${type}</td>
                                <td>${r.len} × ${r.wid} م</td>
                                <td>${r.openings} م²</td>
                                <td class="fw-bold">${parseFloat(r.wall).toFixed(2)} م²</td>
                                <td class="fw-bold">${parseFloat(r.floor).toFixed(2)} م²</td>
                            </tr>
                        `;
                    });

                    let savedPricingHtml = '';
                    const estTot = parseFloat(s.estimated_total) || 0;
                    if (estTot > 0 && Array.isArray(pricing) && pricing.length > 0) {
                        let pRows = '';
                        let totLabor = 0;
                        let totMat = 0;
                        pricing.forEach((p, idx) => {
                            const area = parseFloat(p.area) || 0;
                            const labor = parseFloat(p.labor) || 0;
                            const mat = parseFloat(p.material) || 0;
                            if (area <= 0 || (labor + mat) <= 0) return;
                            const lCost = area * labor;
                            const mCost = area * mat;
                            totLabor += lCost;
                            totMat += mCost;
                            pRows += `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td class="text-start fw-bold">${p.label || 'بند'}</td>
                                    <td class="fw-bold">${area.toFixed(2)} م²</td>
                                    <td>${fmtMoney(labor)} ج</td>
                                    <td>${fmtMoney(mat)} ج</td>
                                    <td class="fw-bold">${fmtMoney(lCost)} ج</td>
                                    <td class="fw-bold">${fmtMoney(mCost)} ج</td>
                                    <td class="fw-black text-primary">${fmtMoney(lCost + mCost)} ج</td>
                                </tr>
                            `;
                        });
                        savedPricingHtml = `
                            <h6 class="fw-black text-dark mt-4 mb-2"><i class="fa fa-coins text-warning me-1"></i>حساب التكلفة التقريبية للمقايسة (مصنعية + خامات)</h6>
                            <table class="table table-bordered text-center align-middle mb-3">
                                <thead class="table-dark">
                                    <tr>
                                        <th>م</th>
                                        <th class="text-start">بند الأعمال</th>
                                        <th>المساحة الصافية</th>
                                        <th>سعر م² مصنعية</th>
                                        <th>تكلفة م² خامات</th>
                                        <th>إجمالي المصنعية</th>
                                        <th>إجمالي الخامات</th>
                                        <th>الإجمالي التقريبي</th>
                                    </tr>
                                </thead>
                                <tbody>${pRows}</tbody>
                                <tfoot class="table-light fw-black">
                                    <tr>
                                        <td colspan="5" class="text-start">السعر التقريبي الإجمالي للمقايسة</td>
                                        <td class="text-primary">${fmtMoney(totLabor)} ج</td>
                                        <td class="text-warning-emphasis">${fmtMoney(totMat)} ج</td>
                                        <td class="text-success fs-6">${fmtMoney(estTot)} ج.م</td>
                                    </tr>
                                </tfoot>
                            </table>
                        `;
                    }

                    const content = `
                        <div id="survey-printable-area" class="p-3">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                <div>
                                    <h4 class="fw-black text-dark m-0">${s.title}</h4>
                                    <div class="text-muted small">تاريخ الحفظ: ${new Date(s.created_at).toLocaleDateString('ar-EG')} | ارتفاع السقف: ${s.ceiling_height} متر</div>
                                </div>
                                <div class="badge bg-primary fs-6">كشف مقايسة مسجل</div>
                            </div>

                            <table class="table table-bordered text-center align-middle mb-4">
                                <thead class="table-dark">
                                    <tr>
                                        <th>م</th>
                                        <th class="text-start">اسم المساحة</th>
                                        <th>النوع</th>
                                        <th>الأبعاد</th>
                                        <th>الفتحات (خصم)</th>
                                        <th>الحوائط الصافية</th>
                                        <th>الأرضيات / الأسقف</th>
                                    </tr>
                                </thead>
                                <tbody>${roomsHtml}</tbody>
                            </table>

                            <div class="row g-2 text-center">
                                <div class="col-3"><div class="p-2 border rounded bg-light"><strong>حوائط الغرف:</strong> ${Number(s.total_room_walls).toFixed(2)} م²</div></div>
                                <div class="col-3"><div class="p-2 border rounded bg-light"><strong>الأرضيات = الأسقف:</strong> ${Number(s.total_floors).toFixed(2)} م²</div></div>
                                <div class="col-3"><div class="p-2 border rounded bg-light"><strong>حوائط سيراميك:</strong> ${Number(s.total_ceramic_walls).toFixed(2)} م²</div></div>
                                <div class="col-3"><div class="p-2 border rounded bg-light"><strong>إجمالي الخصومات:</strong> ${Number(s.total_deductions).toFixed(2)} م²</div></div>
                            </div>
                            ${savedPricingHtml}
                        </div>
                    `;

                    document.getElementById('modal-survey-print-title').innerHTML = `<i class="fa fa-calculator text-primary me-2"></i>${s.title}`;
                    document.getElementById('modal-survey-print-content').innerHTML = content;
                    new bootstrap.Modal(document.getElementById('modalPrintSurvey')).show();
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            });
    }

    function executeSurveyModalPrint() {
        const area = document.getElementById('survey-printable-area');
        if (!area) return;
        const printWin = window.open('', '_blank', 'width=900,height=700');
        printWin.document.write(`
            <html dir="rtl" lang="ar">
            <head>
                <title>طباعة المقايسة</title>
                <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
                <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
                <style>
                    body { font-family: 'Cairo', sans-serif; padding: 25px; }
                    .table th { background: #0f172a !important; color: #fff !important; }
                </style>
            </head>
            <body>
                ${area.innerHTML}
                <script>
                    window.onload = function() { window.print(); window.close(); }
                <\/script>
            </body>
            </html>
        `);
        printWin.document.close();
    }

    // ════════════════════════════════════════════════════════════════
    // 12. PROJECT COST ESTIMATION & FORECASTING (تقدير تكلفة المشروع الذكي)
    // ════════════════════════════════════════════════════════════════
    let currentEstimationData = null;

    function onBenchmarkProjectChange() {
        const sel = document.getElementById('est-benchmark-project');
        if (!sel) return;
        const val = sel.value;
        const opt = sel.options[sel.selectedIndex];

        if (val !== 'average' && opt) {
            const sup = opt.getAttribute('data-sup');
            if (sup) document.getElementById('est-supervision-pct').value = sup;
        }

        loadBenchmarkData(val);
    }

    // مساحة المرجع تُقرأ من قاعدة البيانات — ولا يُسمح بالإدخال اليدوي إلا لو المساحة غير مسجلة
    function applyBenchmarkAreaState(areaFromDb, area) {
        const inp = document.getElementById('est-benchmark-area');
        const missing = document.getElementById('est-area-missing');
        const badge = document.getElementById('est-area-src-badge');
        if (!inp) return;
        if (areaFromDb && area > 0) {
            inp.value = area;
            inp.readOnly = true;
            inp.classList.remove('border', 'border-danger');
            if (missing) missing.style.display = 'none';
            if (badge) badge.style.display = '';
        } else {
            inp.value = '';
            inp.readOnly = false;
            inp.placeholder = 'اكتب المساحة';
            inp.classList.add('border', 'border-danger');
            if (missing) missing.style.display = 'block';
            if (badge) badge.style.display = 'none';
        }
    }

    function saveBenchmarkAreaToProject() {
        const sel = document.getElementById('est-benchmark-project');
        const projectId = sel ? sel.value : null;
        const area = parseFloat(document.getElementById('est-benchmark-area')?.value) || 0;
        if (!projectId || projectId === 'average') return;
        if (area <= 0) {
            Swal.fire('تنبيه', 'اكتب مساحة المشروع (م²) الأول', 'warning');
            return;
        }
        fetch(`{{ url('/contracting/project') }}/${projectId}/update`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ area_sqm: area })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const opt = sel.options[sel.selectedIndex];
                if (opt) opt.setAttribute('data-area', area);
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'تم حفظ مساحة المشروع في قاعدة البيانات', showConfirmButton: false, timer: 2000 });
                loadBenchmarkData(projectId);
            } else {
                Swal.fire('خطأ', data.message || 'تعذر حفظ المساحة', 'error');
            }
        })
        .catch(() => Swal.fire('خطأ', 'تعذر حفظ المساحة', 'error'));
    }

    function loadBenchmarkData(projectId) {
        fetch(`{{ url('/contracting/estimation/benchmark') }}?project_id=${projectId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    currentEstimationData = data;
                    applyBenchmarkAreaState(data.area_from_db !== false, parseFloat(data.benchmark.area_sqm) || 0);
                    if (data.benchmark.supervision_pct) {
                        document.getElementById('est-supervision-pct').value = data.benchmark.supervision_pct;
                    }
                    calculateEstimationLive();
                } else {
                    currentEstimationData = null;
                    applyBenchmarkAreaState(false, 0);
                    Swal.fire('تنبيه', data.message, 'warning');
                }
            })
            .catch(err => {
                console.error('Error loading estimation benchmark data:', err);
            });
    }

    function calculateEstimationLive() {
        if (!currentEstimationData || !currentEstimationData.benchmark) return;

        const newArea = parseFloat(document.getElementById('est-new-area')?.value) || 0;
        const benchmarkArea = parseFloat(document.getElementById('est-benchmark-area')?.value) || 0;
        const inflationPct = parseFloat(document.getElementById('est-inflation-pct')?.value) || 0;
        const supervisionPct = parseFloat(document.getElementById('est-supervision-pct')?.value) || 15;

        if (newArea <= 0 || benchmarkArea <= 0) return;

        const inflationFactor = 1 + (inflationPct / 100);
        const areaRatio = newArea / benchmarkArea;
        const scaleFactor = areaRatio * inflationFactor;

        const b = currentEstimationData.benchmark;
        const bCost = parseFloat(b.total_cost) || 0;
        const bMat = parseFloat(b.materials_cost) || 0;
        const bLabor = parseFloat(b.labor_cost) || 0;
        const bOverhead = parseFloat(b.overhead_cost) || 0;

        // Estimated Totals
        const estCost = bCost * scaleFactor;
        const sqmRate = newArea > 0 ? (estCost / newArea) : 0;
        const supervisionAmount = estCost * (supervisionPct / 100);
        const sellingPrice = estCost + supervisionAmount;
        const netProfit = supervisionAmount;

        const estMat = bMat * scaleFactor;
        const estLabor = bLabor * scaleFactor;
        const estOverhead = bOverhead * scaleFactor;

        // Update KPI Cards
        document.getElementById('est-tot-cost').innerHTML = `${Number(estCost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted">ج</span>`;
        document.getElementById('est-sqm-rate').innerHTML = `${Number(sqmRate).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted">ج/م²</span>`;
        document.getElementById('est-selling-price').innerHTML = `${Number(sellingPrice).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted">ج</span>`;
        document.getElementById('est-net-profit').innerHTML = `${Number(netProfit).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted">ج</span>`;

        // Update Components
        document.getElementById('est-mat-cost').innerHTML = `${Number(estMat).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted">ج</span>`;
        document.getElementById('est-mat-pct').innerText = (estCost > 0 ? ((estMat / estCost) * 100).toFixed(1) : 0) + '%';
        document.getElementById('est-mat-rate').innerText = (newArea > 0 ? (estMat / newArea).toFixed(2) : '0.00') + ' ج/م²';

        document.getElementById('est-labor-cost').innerHTML = `${Number(estLabor).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted">ج</span>`;
        document.getElementById('est-labor-pct').innerText = (estCost > 0 ? ((estLabor / estCost) * 100).toFixed(1) : 0) + '%';
        document.getElementById('est-labor-rate').innerText = (newArea > 0 ? (estLabor / newArea).toFixed(2) : '0.00') + ' ج/م²';

        document.getElementById('est-overhead-cost').innerHTML = `${Number(estOverhead).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted">ج</span>`;
        document.getElementById('est-overhead-pct').innerText = (estCost > 0 ? ((estOverhead / estCost) * 100).toFixed(1) : 0) + '%';
        document.getElementById('est-overhead-rate').innerText = (newArea > 0 ? (estOverhead / newArea).toFixed(2) : '0.00') + ' ج/م²';

        // 1. Render Work Packages Breakdown Table
        const items = currentEstimationData.items || [];
        const itemsTbody = document.getElementById('est-items-body');
        document.getElementById('est-items-count').innerText = `${items.length} بند`;

        if (items.length === 0) {
            itemsTbody.innerHTML = `<tr><td colspan="5" class="py-3 text-muted">لا توجد بنود تفصيلية مسجلة في هذا المرجع</td></tr>`;
        } else {
            let itemsHtml = '';
            items.forEach((item, idx) => {
                const itemOldCost = parseFloat(item.total_cost) || 0;
                const itemEstCost = itemOldCost * scaleFactor;
                const itemRate = newArea > 0 ? (itemEstCost / newArea) : 0;
                const itemPct = estCost > 0 ? ((itemEstCost / estCost) * 100).toFixed(1) : 0;

                itemsHtml += `
                    <tr>
                        <td class="text-muted fw-bold">${idx + 1}</td>
                        <td class="text-start fw-bold text-dark">${item.title}</td>
                        <td class="text-secondary fw-bold" dir="ltr">${itemRate.toFixed(2)} ج/م²</td>
                        <td class="text-primary fw-black" dir="ltr">${Number(itemEstCost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج</td>
                        <td><span class="badge bg-light text-dark border">${itemPct}%</span></td>
                    </tr>
                `;
            });
            itemsTbody.innerHTML = itemsHtml;
        }

        // 2. Render Materials Breakdown Table (User requested feature!)
        const materials = currentEstimationData.materials || [];
        const materialsTbody = document.getElementById('est-materials-body');
        document.getElementById('est-materials-count').innerText = `${materials.length} صنف`;

        if (materials.length === 0) {
            materialsTbody.innerHTML = `<tr><td colspan="6" class="py-3 text-muted">لا توجد خامات تفصيلية مسجلة في هذا المرجع</td></tr>`;
        } else {
            let matHtml = '';
            materials.forEach((mat, idx) => {
                const oldQty = parseFloat(mat.total_qty) || 0;
                const oldAvgPrice = parseFloat(mat.avg_price) || 0;
                const expQty = oldQty * areaRatio;
                const unitPrice = oldAvgPrice * inflationFactor;
                const expTotalCost = expQty * unitPrice;

                matHtml += `
                    <tr>
                        <td class="text-muted fw-bold">${idx + 1}</td>
                        <td class="text-start fw-bold text-dark">${mat.name}</td>
                        <td><span class="badge bg-light text-secondary border">${mat.unit}</span></td>
                        <td class="fw-black text-success" dir="ltr">${Number(expQty).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 2})}</td>
                        <td class="text-muted fw-bold" dir="ltr">${Number(unitPrice).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج</td>
                        <td class="fw-black text-dark" dir="ltr">${Number(expTotalCost).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ج</td>
                    </tr>
                `;
            });
            materialsTbody.innerHTML = matHtml;
        }
    }

    function convertEstimationToQuotation() {
        if (!currentEstimationData) {
            Swal.fire('تنبيه', 'برجاء احتساب التقدير أولاً', 'warning');
            return;
        }

        const newArea = parseFloat(document.getElementById('est-new-area')?.value) || 0;
        const benchmarkArea = parseFloat(document.getElementById('est-benchmark-area')?.value) || 0;
        const inflationPct = parseFloat(document.getElementById('est-inflation-pct')?.value) || 0;
        const supervisionPct = parseFloat(document.getElementById('est-supervision-pct')?.value) || 15;
        const scaleFactor = (newArea / benchmarkArea) * (1 + inflationPct / 100);

        const projectName = document.getElementById('est-project-name')?.value.trim() || `مشروع تشطيب مساحة ${newArea} م²`;
        const clientName = document.getElementById('est-client-name')?.value.trim() || 'عميل محتمل';

        // 1. Switch to Quotation Tab
        const qTabBtn = document.querySelector('button[data-bs-target="#tab-quotations"]');
        if (qTabBtn) qTabBtn.click();

        // 2. Pre-fill Quotation basic info
        document.getElementById('q-client').value = clientName;
        document.getElementById('q-title').value = projectName;
        document.getElementById('q-supervision').value = supervisionPct;

        // 3. Clear container and fill items
        const container = document.getElementById('q-items-container');
        container.innerHTML = '';

        const items = currentEstimationData.items || [];
        if (items.length > 0) {
            items.forEach(item => {
                const itemOldCost = parseFloat(item.total_cost) || 0;
                const itemEstCost = Math.round(itemOldCost * scaleFactor);
                const itemSelling = Math.round(itemEstCost * (1 + supervisionPct / 100));

                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 q-item-row';
                row.innerHTML = `
                    <div class="col-5">
                        <input type="text" class="form-control form-control-sm rounded-3 q-item-title" value="${item.title}" placeholder="اسم البند..." required>
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm rounded-3 q-item-cost text-center" value="${itemEstCost}" placeholder="التكلفة" step="any" oninput="calcQuotationLiveTotal()">
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm rounded-3 q-item-price text-center" value="" placeholder="سعر البيع" step="any" oninput="calcQuotationLiveTotal()">
                    </div>
                    <div class="col-1 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('.q-item-row').remove(); calcQuotationLiveTotal();"><i class="fa fa-trash"></i></button>
                    </div>
                `;
                container.appendChild(row);
            });
        } else {
            const b = currentEstimationData.benchmark;
            const matCost = Math.round((parseFloat(b.materials_cost) || 0) * scaleFactor);
            const laborCost = Math.round((parseFloat(b.labor_cost) || 0) * scaleFactor);

            [
                { title: 'بند توريد الخامات والمواد الأساسية', cost: matCost },
                { title: 'بند المصنعيات وأجور الفنيين والتنفيذ', cost: laborCost }
            ].forEach(cat => {
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 q-item-row';
                row.innerHTML = `
                    <div class="col-5">
                        <input type="text" class="form-control form-control-sm rounded-3 q-item-title" value="${cat.title}" placeholder="اسم البند..." required>
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm rounded-3 q-item-cost text-center" value="${cat.cost}" placeholder="التكلفة" step="any" oninput="calcQuotationLiveTotal()">
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm rounded-3 q-item-price text-center" value="" placeholder="سعر البيع" step="any" oninput="calcQuotationLiveTotal()">
                    </div>
                    <div class="col-1 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('.q-item-row').remove(); calcQuotationLiveTotal();"><i class="fa fa-trash"></i></button>
                    </div>
                `;
                container.appendChild(row);
            });
        }

        calcQuotationLiveTotal();

        Swal.fire({
            icon: 'success',
            title: 'تم نقل بنود التقدير بنجاح!',
            text: 'تم إنشاء مسودة عرض السعر بجميع البنود والتكاليف التقديرية المحتسبة، يمكنك مراجعتها أو حفظها الآن.',
            timer: 2500,
            showConfirmButton: false
        });
    }

    function printEstimationReport() {
        if (!currentEstimationData) {
            Swal.fire('تنبيه', 'برجاء احتساب التقدير أولاً', 'warning');
            return;
        }

        const newArea = parseFloat(document.getElementById('est-new-area')?.value) || 0;
        const benchmarkArea = parseFloat(document.getElementById('est-benchmark-area')?.value) || 0;
        const inflationPct = parseFloat(document.getElementById('est-inflation-pct')?.value) || 0;
        const supervisionPct = parseFloat(document.getElementById('est-supervision-pct')?.value) || 15;
        const projectName = document.getElementById('est-project-name')?.value.trim() || `مشروع مساحة ${newArea} م²`;
        const clientName = document.getElementById('est-client-name')?.value.trim() || 'عميل مقترح';

        const totCost = document.getElementById('est-tot-cost')?.innerText || '0.00 ج';
        const sqmRate = document.getElementById('est-sqm-rate')?.innerText || '0.00 ج/م²';
        const sellingPrice = document.getElementById('est-selling-price')?.innerText || '0.00 ج';
        const netProfit = document.getElementById('est-net-profit')?.innerText || '0.00 ج';

        const matCost = document.getElementById('est-mat-cost')?.innerText || '0.00 ج';
        const matPct = document.getElementById('est-mat-pct')?.innerText || '0%';
        const laborCost = document.getElementById('est-labor-cost')?.innerText || '0.00 ج';
        const laborPct = document.getElementById('est-labor-pct')?.innerText || '0%';
        const overheadCost = document.getElementById('est-overhead-cost')?.innerText || '0.00 ج';
        const overheadPct = document.getElementById('est-overhead-pct')?.innerText || '0%';

        const itemsTableHtml = document.getElementById('table-est-items')?.outerHTML || '';
        const materialsTableHtml = document.getElementById('table-est-materials')?.outerHTML || '';

        const content = `
            <div id="est-printable-area" class="p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div>
                        <h3 class="fw-black text-dark m-0">دراسة جدوى وتقدير تكلفة مشروع هندسي</h3>
                        <div class="text-muted small mt-1">تاريخ الدراسة: ${new Date().toLocaleDateString('ar-EG')} | المقدر: المكتب الهندسي لإدارة المشروعات</div>
                    </div>
                    <div class="text-start">
                        <span class="badge bg-warning text-dark fs-6">تقدير مالي تناسبي معتمد</span>
                    </div>
                </div>

                <!-- Project & Benchmark Dossier -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 border rounded bg-light">
                            <strong>المشروع المقترح:</strong> ${projectName}<br>
                            <strong>العميل المستهدف:</strong> ${clientName}<br>
                            <strong>المساحة المستهدفة:</strong> <span class="badge bg-primary fs-6">${newArea} م²</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-light">
                            <strong>المشروع المرجعي:</strong> ${currentEstimationData.benchmark.name}<br>
                            <strong>مساحة المرجع:</strong> ${benchmarkArea} م²<br>
                            <strong>معامل تغير الأسعار:</strong> ${inflationPct}% | <strong>نسبة الإشراف:</strong> ${supervisionPct}%
                        </div>
                    </div>
                </div>

                <!-- KPI Tiles -->
                <div class="row g-2 text-center mb-3">
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>إجمالي التكلفة المتوقعة:</strong><br><span class="fs-5 fw-black text-primary">${totCost}</span></div></div>
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>معدل تكلفة المتر (م²):</strong><br><span class="fs-5 fw-black text-warning-emphasis">${sqmRate}</span></div></div>
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>سعر البيع المقترح (+ الإشراف):</strong><br><span class="fs-5 fw-black text-success">${sellingPrice}</span></div></div>
                    <div class="col-3"><div class="p-2 border rounded bg-light"><strong>صافي أرباح الشركة المتوقعة:</strong><br><span class="fs-5 fw-black text-dark">${netProfit}</span></div></div>
                </div>

                <!-- Distribution -->
                <div class="row g-2 text-center mb-3">
                    <div class="col-4"><div class="p-2 border rounded bg-light"><strong>الخامات والمواد:</strong> ${matCost} (${matPct})</div></div>
                    <div class="col-4"><div class="p-2 border rounded bg-light"><strong>المصنعيات وأجور الفنيين:</strong> ${laborCost} (${laborPct})</div></div>
                    <div class="col-4"><div class="p-2 border rounded bg-light"><strong>المصروفات والنثريات:</strong> ${overheadCost} (${overheadPct})</div></div>
                </div>

                <!-- Work Packages -->
                <h5 class="fw-bold text-dark mt-4 mb-2"><i class="fa fa-layer-group me-1"></i> توزيع بنود الأعمال المقدرة</h5>
                ${itemsTableHtml}

                <!-- Expected Materials -->
                <h5 class="fw-bold text-dark mt-4 mb-2"><i class="fa fa-boxes-stacked me-1"></i> جدول الخامات والمواد المتوقعة وكمياتها التناسبية</h5>
                ${materialsTableHtml}
            </div>
        `;

        document.getElementById('modal-est-print-title').innerHTML = `<i class="fa fa-wand-magic-sparkles text-warning me-2"></i>دراسة تقدير تكلفة: ${projectName} (${newArea} م²)`;
        document.getElementById('modal-est-print-content').innerHTML = content;
        new bootstrap.Modal(document.getElementById('modalPrintEstimation')).show();
    }

    function executeEstimationModalPrint() {
        const area = document.getElementById('est-printable-area');
        if (!area) return;
        const printWin = window.open('', '_blank', 'width=950,height=750');
        printWin.document.write(`
            <html dir="rtl" lang="ar">
            <head>
                <title>طباعة دراسة تقدير تكلفة المشروع</title>
                <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
                <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
                <style>
                    body { font-family: 'Cairo', sans-serif; padding: 25px; }
                    .table th { background: #0f172a !important; color: #fff !important; }
                </style>
            </head>
            <body>
                ${area.innerHTML}
                <script>
                    window.onload = function() { window.print(); window.close(); }
                <\/script>
            </body>
            </html>
        `);
        printWin.document.close();
    }

    // Robust Tab Switching Initializer (Matches AC Screen Behavior)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#contractingTabs .nav-link').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetSelector = this.getAttribute('data-bs-target');
                if (!targetSelector) return;

                // 1. Update button states
                document.querySelectorAll('#contractingTabs .nav-link').forEach(function(b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                // 2. Update tab panes
                document.querySelectorAll('#contractingTabContent .tab-pane').forEach(function(pane) {
                    pane.classList.remove('show', 'active');
                });
                const targetPane = document.querySelector(targetSelector);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }

                // 3. Optional triggers per tab
                if (targetSelector === '#tab-projects') {
                    const sel = document.getElementById('select-project-360');
                    if (sel) {
                        if (!sel.value && currentProjectId) {
                            sel.value = currentProjectId;
                        }
                        if (sel.value) {
                            select360Project(sel.value);
                        } else if (sel.options.length > 1) {
                            sel.selectedIndex = 1;
                            select360Project(sel.value);
                        }
                    }
                } else if (targetSelector === '#tab-prices') {
                    const priceInp = document.getElementById('filter-materials-input');
                    filterMaterialsTable(priceInp ? priceInp.value : '');
                } else if (targetSelector === '#tab-estimation') {
                    if (!currentEstimationData) {
                        loadBenchmarkData('average');
                    }
                } else if (targetSelector === '#tab-reports') {
                    setTimeout(initReportsCharts, 80);
                } else if (targetSelector === '#tab-invoices') {
                    refreshAllInvoicesPagination();
                }
            });
        });

        // Filter Report Projects Table in Real-time
        window.filterReportProjectsTable = function(searchTerm) {
            searchTerm = (searchTerm || '').toLowerCase().trim();
            const rows = document.querySelectorAll('#reportProjectsTable tbody tr');
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        };

        // Reports Data & Chart.js Instances
        const reportsData = @json($reportHighlights ?? []);
        let chartExpensesInstance = null;
        let chartProjectsInstance = null;
        let chartTrendInstance = null;

        window.initReportsCharts = function() {
            if (!reportsData || typeof Chart === 'undefined') return;

            // 1. Chart: Expenses Breakdown (Doughnut)
            const expCanvas = document.getElementById('chartExpensesBreakdown');
            if (expCanvas) {
                if (chartExpensesInstance) chartExpensesInstance.destroy();
                const exp = reportsData.expenses_breakdown || {};
                const expData = [
                    exp.material || 0,
                    exp.technician || 0,
                    exp.petty || 0,
                    exp.expense || 0,
                    exp.commission || 0
                ];
                const hasData = expData.some(v => v > 0);

                chartExpensesInstance = new Chart(expCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['خامات توريد', 'مصنعيات فنيين', 'نثريات موقع', 'مصروفات داخلية', 'عمولات مسوقين'],
                        datasets: [{
                            data: hasData ? expData : [1],
                            backgroundColor: hasData ? [
                                '#2563eb',
                                '#0d9488',
                                '#6366f1',
                                '#64748b',
                                '#f59e0b'
                            ] : ['#e2e8f0'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                rtl: true,
                                labels: {
                                    font: { family: 'Cairo', size: 11, weight: 'bold' },
                                    boxWidth: 12,
                                    padding: 12
                                }
                            },
                            tooltip: {
                                rtl: true,
                                callbacks: {
                                    label: function(ctx) {
                                        if (!hasData) return 'لا توجد بيانات مسجلة';
                                        const val = Number(ctx.raw || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
                                        return ' ' + ctx.label + ': ' + val + ' ج.م';
                                    }
                                }
                            }
                        },
                        cutout: '62%'
                    }
                });
            }

            // 2. Chart: Projects Comparison (Bar Chart)
            const projCanvas = document.getElementById('chartProjectsComparison');
            if (projCanvas) {
                if (chartProjectsInstance) chartProjectsInstance.destroy();
                const projList = reportsData.projects_comparison || [];
                const labels = projList.map(p => p.project_name || ('مشروع ' + p.project_id));
                const costs = projList.map(p => p.total_cost || 0);
                const revenues = projList.map(p => p.net_selling || 0);
                const profits = projList.map(p => p.net_profit || 0);

                chartProjectsInstance = new Chart(projCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels.length ? labels : ['لا توجد مشاريع نشطة'],
                        datasets: [
                            {
                                label: 'إجمالي التكلفة',
                                data: costs.length ? costs : [0],
                                backgroundColor: '#ef4444',
                                borderRadius: 6
                            },
                            {
                                label: 'قيمة البيع',
                                data: revenues.length ? revenues : [0],
                                backgroundColor: '#2563eb',
                                borderRadius: 6
                            },
                            {
                                label: 'صافي الربح',
                                data: profits.length ? profits : [0],
                                backgroundColor: '#10b981',
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: { font: { family: 'Cairo', size: 10, weight: 'bold' } },
                                grid: { display: false }
                            },
                            y: {
                                ticks: {
                                    font: { family: 'Cairo', size: 10 },
                                    callback: val => Number(val).toLocaleString() + ' ج'
                                },
                                grid: { color: '#f1f5f9' }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                rtl: true,
                                labels: { font: { family: 'Cairo', size: 11, weight: 'bold' }, boxWidth: 12 }
                            },
                            tooltip: {
                                rtl: true,
                                callbacks: {
                                    label: ctx => ' ' + ctx.dataset.label + ': ' + Number(ctx.raw || 0).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج.م'
                                }
                            }
                        }
                    }
                });
            }

            // 3. Chart: Monthly Trend (Line / Area Chart)
            const trendCanvas = document.getElementById('chartMonthlyTrend');
            if (trendCanvas) {
                if (chartTrendInstance) chartTrendInstance.destroy();
                const trend = reportsData.monthly_trend || [];
                const labels = trend.map(t => t.month);
                const costs = trend.map(t => t.cost || 0);
                const revenues = trend.map(t => t.selling || 0);
                const profits = trend.map(t => t.profit || 0);

                chartTrendInstance = new Chart(trendCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'صافي الربح',
                                data: profits,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: '#10b981'
                            },
                            {
                                label: 'الإيرادات (بيع)',
                                data: revenues,
                                borderColor: '#2563eb',
                                backgroundColor: 'transparent',
                                borderDash: [5, 5],
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 3
                            },
                            {
                                label: 'التكاليف',
                                data: costs,
                                borderColor: '#ef4444',
                                backgroundColor: 'transparent',
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: { font: { family: 'Cairo', size: 10, weight: 'bold' } },
                                grid: { display: false }
                            },
                            y: {
                                ticks: {
                                    font: { family: 'Cairo', size: 10 },
                                    callback: val => Number(val).toLocaleString() + ' ج'
                                },
                                grid: { color: '#f1f5f9' }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                rtl: true,
                                labels: { font: { family: 'Cairo', size: 11, weight: 'bold' }, boxWidth: 12 }
                            },
                            tooltip: {
                                rtl: true,
                                callbacks: {
                                    label: ctx => ' ' + ctx.dataset.label + ': ' + Number(ctx.raw || 0).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج.م'
                                }
                            }
                        }
                    }
                });
            }
        };

        // If the URL specifies the reports tab or filters, activate it immediately
        if (window.location.hash === '#tab-reports' || window.location.search.includes('period=') || window.location.search.includes('from_date=')) {
            const repTabBtn = document.querySelector('#contractingTabs button[data-bs-target="#tab-reports"]');
            if (repTabBtn) {
                repTabBtn.click();
            }
        }

        // ── حفظ واسترجاع مسودة صندوق العمليات وفاتورة الخامات تلقائياً (لمنع ضياع 40 صنف أو أي عملية أبداً) ──
        const POS_DRAFT_STORAGE_KEY = 'cont_pos_draft_v1';

        window.savePosDraftToStorage = function() {
            try {
                const matRows = [];
                document.querySelectorAll('#material-invoice-tbody tr.mat-invoice-row').forEach(tr => {
                    const title = tr.querySelector('.mat-title')?.value || '';
                    const qty = tr.querySelector('.mat-qty')?.value || 1;
                    const unit = tr.querySelector('.mat-unit')?.value || 'عدد';
                    const cost = tr.querySelector('.mat-cost')?.value || '';
                    const sup = tr.querySelector('.mat-sup')?.value || '';
                    const sell = tr.querySelector('.mat-sell')?.value || '';
                    if (title.trim() !== '' || (cost !== '' && parseFloat(cost) > 0)) {
                        matRows.push({ title, qty, unit, cost, sup, sell });
                    }
                });
                const draft = {
                    cart: Array.isArray(cart) ? cart : [],
                    currentProjectId,
                    currentProjectName,
                    currentClientName,
                    currentSupervisionPct,
                    currentItemId,
                    currentItemTitle,
                    currentOpType,
                    invSupplier: document.getElementById('inv-supplier')?.value || '',
                    invRef: document.getElementById('inv-ref')?.value || '',
                    matRows
                };
                localStorage.setItem(POS_DRAFT_STORAGE_KEY, JSON.stringify(draft));
            } catch (e) {}
        };

        window.clearPosDraftStorage = function() {
            try {
                localStorage.removeItem(POS_DRAFT_STORAGE_KEY);
            } catch (e) {}
        };

        window.restorePosDraftFromStorage = function() {
            try {
                const raw = localStorage.getItem(POS_DRAFT_STORAGE_KEY);
                if (!raw) return false;
                const draft = JSON.parse(raw);
                if (!draft) return false;

                const hasSavedCart = Array.isArray(draft.cart) && draft.cart.length > 0;
                const hasSavedMatRows = Array.isArray(draft.matRows) && draft.matRows.length > 0;
                if (!hasSavedCart && !hasSavedMatRows) return false;

                if (draft.currentProjectId) {
                    selectProject(draft.currentProjectId, draft.currentProjectName || '', draft.currentClientName || '', draft.currentSupervisionPct || 15.0, null);
                    if (draft.currentItemId !== undefined && draft.currentItemTitle) {
                        selectItem(draft.currentItemId, draft.currentItemTitle, null);
                        if (draft.currentOpType) {
                            setOpType(draft.currentOpType, null);
                        }
                    }
                }

                if (hasSavedCart) {
                    cart = draft.cart;
                    renderCart();
                }

                if (draft.invSupplier) {
                    const invSup = document.getElementById('inv-supplier');
                    if (invSup) {
                        invSup.value = draft.invSupplier;
                        onSupplierChange(draft.invSupplier);
                    }
                }
                if (draft.invRef) {
                    const invRefEl = document.getElementById('inv-ref');
                    if (invRefEl) invRefEl.value = draft.invRef;
                }

                if (hasSavedMatRows) {
                    const tbody = document.getElementById('material-invoice-tbody');
                    if (tbody) {
                        tbody.innerHTML = '';
                        draft.matRows.forEach(r => addMaterialRow(r));
                        calcInvoiceTotals();
                    }
                    return true;
                }
            } catch (e) {}
            return false;
        };

        // Initialize Material Invoice table with 2 starter rows (unless restored from draft)
        const restoredMatRows = window.restorePosDraftFromStorage();
        if (!restoredMatRows) {
            addMaterialRow();
            addMaterialRow();
            onSupplierChange('');
        }

        // If there's an active project in 360 select, preload it
        const init360Sel = document.getElementById('select-project-360');
        if (init360Sel && init360Sel.options.length > 1) {
            init360Sel.selectedIndex = 1;
            select360Project(init360Sel.value);
        }

        // Initialize Survey Calculator with starter rows matching reference image
        resetSurveyCalc();

        // Initialize Estimation Benchmark pre-load
        loadBenchmarkData('average');

        searchPriceHistory('');

        // Initialize Invoices pagination (7 per page)
        refreshAllInvoicesPagination();

        // ── حماية البيانات من الضياع عند عمل Refresh للصفحة فقط (وليس عند عمل Submit لأي فورم) ──
        // السماح الفوري لأي عملية Submit لأي فورم (مثل إضافة مشروع جديد أو إعدادات) بدون إظهار رسالة التحذير
        document.addEventListener('submit', function(e) {
            if (!e.defaultPrevented) {
                window.allowSafeReload = true;
            }
        }, false);

        if (!HTMLFormElement.prototype._contractingSafeSubmitPatched) {
            const origFormSubmit = HTMLFormElement.prototype.submit;
            HTMLFormElement.prototype.submit = function() {
                window.allowSafeReload = true;
                return origFormSubmit.apply(this, arguments);
            };
            HTMLFormElement.prototype._contractingSafeSubmitPatched = true;
        }

        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                window.allowSafeReload = false;
            }
        });

        // حفظ تلقائي للمسودة عند أي تعديل في شاشة نقطة البيع
        ['input', 'change'].forEach(function(evtName) {
            document.addEventListener(evtName, function(e) {
                if (e.target && e.target.closest && e.target.closest('#tab-pos')) {
                    window.savePosDraftToStorage();
                }
            }, { passive: true });
        });

        function hasUnsavedPosWork() {
            if (Array.isArray(cart) && cart.length > 0) return true;

            const hasMatRows = Array.from(document.querySelectorAll('#material-invoice-tbody tr.mat-invoice-row')).some(function(tr) {
                const title = (tr.querySelector('.mat-title')?.value || '').trim();
                const cost = parseFloat(tr.querySelector('.mat-cost')?.value || '0') || 0;
                const sell = parseFloat(tr.querySelector('.mat-sell')?.value || '0') || 0;
                return title !== '' || cost > 0 || sell > 0;
            });
            if (hasMatRows) return true;

            const posEntryFieldIds = [
                'tech-title', 'tech-cost', 'tech-sell',
                'ovh-title', 'ovh-amount',
                'petty-title', 'petty-cost', 'petty-sell',
                'comm-name', 'comm-val', 'comm-note'
            ];
            return posEntryFieldIds.some(function(id) {
                const el = document.getElementById(id);
                return el && el.value && el.value.trim() !== '' && el.value !== '0' && el.value !== '0.00';
            });
        }

        // 1. اعتراض أزرار الكيبورد (F5 و Ctrl+R) وإظهار رسالة تأكيد فقط لو فيه بيانات مدخلة غير مرحلة
        window.addEventListener('keydown', function(e) {
            const isF5 = (e.key === 'F5' || e.keyCode === 116);
            const isCtrlR = ((e.ctrlKey || e.metaKey) && (e.key === 'r' || e.key === 'R' || e.keyCode === 82));

            if ((isF5 || isCtrlR) && hasUnsavedPosWork()) {
                e.preventDefault();
                e.stopPropagation();

                Swal.fire({
                    title: 'انتبه! لديك عمليات غير مرحلة',
                    text: 'يوجد بنود في صندوق العمليات أو أصناف مدخلة لم يتم اعتمادها وترحيلها بعد.. هل تريد تحديث الصفحة؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#0f172a',
                    confirmButtonText: 'نعم، اعمل تحديث',
                    cancelButtonText: 'لا، البقاء في الصفحة',
                    reverseButtons: true,
                    focusCancel: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        safeContractingReload();
                    }
                });
            }
        });

        // 2. اعتراض زر التحديث الخاص بالمتصفح أو إغلاق التاب (beforeunload) — لا يظهر أبداً عند عمل Submit
        window.addEventListener('beforeunload', function(e) {
            if (window.allowSafeReload) return;

            if (hasUnsavedPosWork()) {
                window.savePosDraftToStorage();
                const msg = 'البيانات المدخلة لم يتم ترحيلها بعد، هل أنت متأكد من مغادرة الصفحة؟';
                e.preventDefault();
                e.returnValue = msg;
                return msg;
            }
        });
    });
</script>
</body>
</html>
