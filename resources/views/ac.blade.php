<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تركيب وصيانة تكييفات</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --main-blue: #0f172a; --main-gold: #d4af37; --bg-light: #e0f2fe; }
        body { 
            font-family: 'Cairo', sans-serif; 
            background-color: var(--bg-light); 
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%230284c7" opacity="0.03"><path d="M21 14v-7c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zm-16-7h14v4H5V7zm14 7H5v-2h14v2zm-3-6h2v2h-2V8zm-3 0h2v2h-2V8z"/></svg>');
            background-size: 100px;
            background-attachment: fixed;
            color: var(--main-blue); 
            overflow-x: hidden; 
        }

        /* ❄️ Splash Screen */
        #ac-splash {
            position: fixed; inset: 0; z-index: 99999;
            background: linear-gradient(135deg, #0c4a6e 0%, #0f172a 50%, #1e3a5f 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        #ac-splash.hide { opacity: 0; visibility: hidden; pointer-events: none; }
        #ac-splash .splash-icon {
            font-size: 5rem; color: #38bdf8;
            animation: splashPulse 0.5s ease-in-out infinite alternate;
            filter: drop-shadow(0 0 30px rgba(56,189,248,0.5));
        }
        #ac-splash .splash-title {
            color: white; font-size: 1.6rem; font-weight: 900; margin-top: 15px;
            text-shadow: 0 2px 15px rgba(56,189,248,0.4);
        }
        #ac-splash .splash-sub {
            color: rgba(255,255,255,0.6); font-size: 0.95rem; margin-top: 5px;
        }
        @keyframes splashPulse { from { transform: scale(1) rotate(0deg); } to { transform: scale(1.15) rotate(10deg); } }
        
        .snowflake {
            position: fixed; top: -20px; z-index: 100000;
            color: rgba(255,255,255,0.7); font-size: 1.2rem;
            animation: snowFall linear forwards;
            pointer-events: none;
        }
        @keyframes snowFall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
        }
        .main-content { margin-right: 260px; padding: 40px 30px; }
        .pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .pos-btn { position: relative; height: 120px; border-radius: 16px; border: 2px solid transparent; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: all 0.5s; color: var(--main-blue); padding: 10px; text-align: center; }
        .pos-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.1); border-color: var(--main-blue); }
        .pos-btn.active { background: var(--main-blue); color: white; border-color: var(--main-blue); }
        .pos-btn i { font-size: 2rem; margin-bottom: 8px; color: var(--main-gold); }
        .pos-btn.active i { color: white; }
        
        .section-title { font-weight: 900; margin-bottom: 20px; border-bottom: 3px solid var(--main-gold); display: inline-block; padding-bottom: 5px; }
        
        /* Sidebar layout for POS cart */
        .pos-layout { display: flex; gap: 20px; }
        .pos-items-area { flex: 1; }
        .pos-cart-area { width: 350px; background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); align-self: flex-start; position: sticky; top: 20px;}
        
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed #ccc; }
        .cart-total { font-size: 1.5rem; font-weight: bold; color: #dc3545; margin-top: 15px; text-align: left; }
        
        .nav-pills .nav-link { color: #64748b; font-weight: 700; border-radius: 50px; padding: 10px 24px; margin-left: 8px; transition: 0.3s; }
        .nav-pills .nav-link.active { background-color: var(--main-blue); color: white; }
        @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;} .pos-layout{flex-direction:column;} .pos-cart-area{width:100%;}}

        @keyframes popClick {
            0% { transform: scale(1); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
            50% { transform: scale(0.94); box-shadow: inset 0 2px 6px rgba(0,0,0,0.1); background-color: #f1f5f9; border-color: #cbd5e1; }
            100% { transform: scale(1); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        }
        .clicked-anim { animation: popClick 0.3s ease-out; }
        
        .profile-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; border: 1px solid var(--main-blue) !important; }

        @keyframes floatUp {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-40px) scale(1.5); }
        }
        .plus-one {
            position: absolute;
            top: 20%;
            color: #10b981;
            font-weight: 900;
            font-size: 2rem;
            pointer-events: none;
            animation: floatUp 0.6s ease-out forwards;
            z-index: 10;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
@include('sidebar')

<!-- ❄️ Splash Screen -->
<div id="ac-splash">
    <div class="splash-icon">❄️</div>
    <div class="splash-title">تكييفات وصيانة</div>
    <div class="splash-sub">جاري تحميل نقطة البيع...</div>
</div>

<div class="main-content">
    @if(session('success'))
 <div class="alert alert-success fw-bold rounded-4"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger fw-bold rounded-4"><i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger fw-bold rounded-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <ul class="nav nav-pills mb-4" id="acTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-pos" type="button"><i class="fa fa-desktop me-2"></i>تسجيل عملية</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-schools" type="button"><i class="fa fa-school me-2"></i>ملفات المدارس</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-expenses" type="button"><i class="fa fa-money-bill-wave me-2"></i>المصروفات</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-reports" type="button"><i class="fa fa-chart-line me-2"></i>التقارير</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button"><i class="fa fa-history me-2"></i>السجل</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button"><i class="fa fa-cogs me-2"></i>الإعدادات</button></li>
    </ul>

    <div class="tab-content">
        <!-- POS TAB -->
        <div class="tab-pane fade show active" id="tab-pos">
            <div class="pos-layout">
                <!-- Selection Area -->
                <div class="pos-items-area">
                    
                    <!-- 1. Clients -->
                    <div id="step-clients">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h4 class="section-title mb-0">1. اختر العميل</h4>
                            <div>
                                <button class="btn btn-sm btn-outline-primary fw-bold rounded-pill" onclick="showQuickAddClient()"><i class="fa fa-plus me-1"></i> عميل جديد</button>
                                <button class="btn btn-sm btn-outline-danger fw-bold rounded-pill ms-2" onclick="switchToExpensesTab()"><i class="fa fa-money-bill-wave me-1"></i> إضافة مصروف</button>
                            </div>
                        </div>
                        <div class="pos-grid mb-4" id="clients-grid">
                            @foreach($clients as $c)
                            <div class="pos-btn" onclick="selectClient({{ $c->id }}, '{{ addslashes($c->name) }}', this)">
                                <i class="fa fa-building"></i>
                                <span>{{ $c->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 2. Floors -->
                    <div id="step-floors" style="display:none;">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h4 class="section-title mb-0">2. اختر الدور (اختياري)</h4>
                            <div>
                                <button class="btn btn-sm btn-outline-success fw-bold rounded-pill me-2" onclick="selectAllFloors()"><i class="fa fa-check-double me-1"></i> تحديد الكل</button>
                                <button class="btn btn-sm btn-outline-primary fw-bold rounded-pill me-2" onclick="showQuickAddSetting('floor')"><i class="fa fa-plus me-1"></i> دور جديد</button>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="skipToClasses()">تخطي الدور</button>
                            </div>
                        </div>
                        <div class="pos-grid mb-4" id="floors-grid">
                            @foreach($floors as $f)
                            <div class="pos-btn" data-client-id="{{ $f->ac_client_id }}" onclick="selectFloor({{ $f->id }}, '{{ addslashes($f->name) }}', this)" style="display:none;">
                                <i class="fa fa-layer-group"></i>
                                <span>{{ $f->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. Classes -->
                    <div id="step-classes" style="display:none;">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h4 class="section-title mb-0">3. اختر الفصل (اختياري)</h4>
                            <div>
                                <button class="btn btn-sm btn-outline-success fw-bold rounded-pill me-2" onclick="selectAllClasses()"><i class="fa fa-check-double me-1"></i> تحديد الكل</button>
                                <button class="btn btn-sm btn-outline-primary fw-bold rounded-pill me-2" onclick="showQuickAddSetting('class')"><i class="fa fa-plus me-1"></i> فصل جديد</button>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="skipToType()">تخطي الفصل</button>
                            </div>
                        </div>
                        <div class="pos-grid mb-4" id="classes-grid">
                            @foreach($classes as $c)
                            <div class="pos-btn" data-client-id="{{ $c->ac_client_id }}" data-floor-id="{{ $c->ac_floor_id }}" onclick="selectClass({{ $c->id }}, '{{ addslashes($c->name) }}', this)" style="display:none;">
                                <i class="fa fa-door-open"></i>
                                <span>{{ $c->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 4. Type (Sale vs Maintenance) -->
                    <div id="step-type" style="display:none;">
                        <h4 class="section-title">4. نوع العملية</h4>
                        <div class="pos-grid mb-4">
                            <div class="pos-btn" id="btn-type-sale" onclick="selectType('sale', this)">
                                <i class="fa fa-fan"></i>
                                <span>بيع وتركيب تكييفات</span>
                            </div>
                            <div class="pos-btn" onclick="selectType('maintenance', this)">
                                <i class="fa fa-tools"></i>
                                <span>صيانة</span>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Items -->
                    <div id="step-items" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="section-title mb-0" id="items-title">5. اختر الأصناف</h4>
                            <div style="width: 300px; position: relative;">
                                <i class="fa fa-search text-muted" style="position: absolute; right: 12px; top: 10px;"></i>
                                <input type="text" id="item-search" class="form-control border-primary" style="padding-right: 35px; border-radius: 20px;" placeholder="بحث عن صنف أو باركود..." onkeyup="filterItems()">
                            </div>
                        </div>
                        
                        <!-- Manual Maintenance / Free Item Input -->
                        <div id="manual-maintenance-div" style="display:none;" class="mb-4 bg-white p-3 rounded-4 shadow-sm border">
                            <h5 class="fw-bold">إضافة بند حر (مصنعية أو غيرها)</h5>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" id="manual_maint_name" placeholder="الاسم (مثال: مصنعية تركيب، شحن فريون)">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="manual_maint_cost" placeholder="التكلفة (سعر الشراء)">
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="manual_maint_price" placeholder="سعر البيع للعميل">
                                        <button class="btn btn-primary" onclick="setManualMaint()">اعتماد</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sale Items Grid -->
                        <div class="pos-grid mb-4" id="grid-sale-items" style="display:none;">
                            @foreach($acItems as $item)
                            <div class="pos-btn" onclick="addToCart({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $item->selling_price }}, this)">
                                <i class="fa fa-box"></i>
                                <span style="font-size:14px;">{{ $item->product_name }}</span>
                                <small class="text-danger mt-1">{{ number_format($item->selling_price, 2) }} ج</small>
                            </div>
                            @endforeach
                        </div>

                        <!-- Maintenance Items Grid -->
                        <div class="pos-grid mb-4" id="grid-maint-items" style="display:none;">
                            @foreach($acServices ?? [] as $srv)
                            <div class="pos-btn" onclick="addToCart('srv_{{ $srv->id }}', '{{ addslashes($srv->name) }}', {{ $srv->selling_price }}, this, true, {{ $srv->cost_price }})">
                                <i class="fa fa-cogs text-warning"></i>
                                <span style="font-size:14px;">{{ $srv->name }}</span>
                                <small class="text-danger mt-1">{{ number_format($srv->selling_price, 2) }} ج</small>
                            </div>
                            @endforeach

                            @foreach($maintenanceItems as $item)
                            <div class="pos-btn" onclick="addToCart({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $item->selling_price }}, this)">
                                <i class="fa fa-wrench"></i>
                                <span style="font-size:14px;">{{ $item->product_name }}</span>
                                <small class="text-danger mt-1">{{ number_format($item->selling_price, 2) }} ج</small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                </div>

                <!-- Cart Area -->
                <div class="pos-cart-area">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h4 class="fw-bold mb-0"><i class="fa fa-shopping-cart text-warning me-2"></i>الفاتورة</h4>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="showRecentInvoices()"><i class="fa fa-history me-1"></i> أحدث الفواتير</button>
                    </div>
                    <form action="{{ route('ac.store') }}" method="POST" id="checkout-form">
                        @csrf
                        <input type="hidden" name="ac_client_id" id="form_client_id">
                        <input type="hidden" name="ac_floor_id" id="form_floor_id">
                        <input type="hidden" name="ac_class_id" id="form_class_id">
                        <input type="hidden" name="type" id="form_type">
                        <input type="hidden" name="items" id="form_items">
                        <input type="hidden" name="multi_floors_text" id="form_multi_floors_text">
                        <input type="hidden" name="multi_classes_text" id="form_multi_classes_text">
                        
                        <div class="mb-2"><small>العميل:</small> <strong id="lbl_client" class="text-primary">-</strong></div>
                        <div class="mb-2"><small>الدور:</small> <strong id="lbl_floor" class="text-primary">-</strong></div>
                        <div class="mb-2"><small>الفصل:</small> <strong id="lbl_class" class="text-primary">-</strong></div>
                        <hr>
                        
                        <div id="cart-items-container" style="min-height: 100px;">
                            <div class="text-muted text-center mt-4">الفاتورة فارغة</div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label mb-0 fw-bold">الخصم (إن وجد)</label>
                            <input type="number" name="discount_amount" id="form_discount" class="form-control" value="0" min="0" oninput="updateTotal()">
                        </div>

                        <div class="cart-total">
                            إجمالي: <span id="cart-total-val">0</span> ج
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">طريقة الدفع</label>
                            <select name="payment_method" id="payment_method" class="form-select mb-3" onchange="togglePaymentAmount()">
                                <option value="cash">كله كاش</option>
                                <option value="later">كله آجل</option>
                                <option value="partial">جزئي (مقدم)</option>
                            </select>
                        </div>

                        <div class="mt-3" id="paid_amount_div" style="display: none;">
                            <label class="form-label fw-bold">المبلغ المدفوع (المقدم)</label>
                            <input type="number" name="paid_amount" id="paid_amount" class="form-control mb-3" step="0.01" min="0">
                        </div>

                        <div class="mt-3" id="treasury_div">
                            <label class="form-label fw-bold">خزينة الدفع والإيداع</label>
                            <select name="deposit_account_id" class="form-select mb-3" required>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance,2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        
                        <script>
                            function togglePaymentAmount() {
                                const method = document.getElementById("payment_method").value;
                                const amountDiv = document.getElementById("paid_amount_div");
                                const treasuryDiv = document.getElementById("treasury_div");
                                const treasurySelect = document.querySelector("select[name='deposit_account_id']");
                                const paidAmount = document.getElementById("paid_amount");
                                
                                if (method === "partial") {
                                    amountDiv.style.display = "block";
                                    paidAmount.required = true;
                                } else {
                                    amountDiv.style.display = "none";
                                    paidAmount.required = false;
                                }

                                if (method === "later") {
                                    treasuryDiv.style.display = "none";
                                    if(treasurySelect) treasurySelect.required = false;
                                } else {
                                    treasuryDiv.style.display = "block";
                                    if(treasurySelect) treasurySelect.required = true;
                                }
                            }
                            document.addEventListener("DOMContentLoaded", togglePaymentAmount);
                        </script>
                        <button type="button" class="btn btn-success w-100 fw-bold py-2 rounded-4" onclick="submitForm()"><i class="fa fa-check me-2"></i>حفظ العملية</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- SCHOOLS PROFILES TAB -->
        <div class="tab-pane fade" id="tab-schools">
            <h3 class="fw-bold mb-4 border-bottom pb-2 text-primary"><i class="fa fa-school me-2"></i>ملفات المدارس</h3>
            
            <div class="row" id="schools-profile-grid">
                @foreach($clients as $c)
                <div class="col-md-3 col-sm-4 col-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 text-center cursor-pointer profile-card" onclick="openSchoolProfile({{ $c->id }})" style="cursor: pointer; transition: 0.3s;">
                        <div class="card-body p-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                                <i class="fa fa-building"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-0">{{ $c->name }}</h5>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- EXPENSES TAB -->
        <div class="tab-pane fade" id="tab-expenses">
            <div class="row">
                <!-- Add Expense Form -->
                <div class="col-md-8 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 text-primary"><i class="fa fa-money-bill-wave me-2"></i>تسجيل مصروف جديد</h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="expenseForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">المدرسة / العميل</label>
                                        <select class="form-select select2-clients" name="ac_client_id" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($clients as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">بند المصروف</label>
                                        <select class="form-select" name="ac_expense_category_id" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($expenseCategories as $ec)
                                                <option value="{{ $ec->id }}">{{ $ec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">الخزينة / المحفظة</label>
                                        <select class="form-select" name="account_id" required>
                                            <option value="">-- اختر --</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">المبلغ</label>
                                        <input type="number" step="0.01" class="form-control" name="amount" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">التاريخ</label>
                                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-bold">ملاحظات / بيان</label>
                                        <input type="text" class="form-control" name="notes">
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-2"></i>حفظ المصروف</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pie Chart -->
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 text-secondary"><i class="fa fa-chart-pie me-2"></i>تفنيد بنود المصروفات</h5>
                        </div>
                        <div class="card-body p-3 d-flex flex-column justify-content-center align-items-center">
                            @if(count($expensesByCategory ?? []) > 0)
                                <div style="position: relative; width: 100%; height: 220px;">
                                    <canvas id="expensesPieChart"></canvas>
                                </div>
                            @else
                                <div class="text-muted text-center"><i class="fa fa-info-circle fa-2x mb-2"></i><br>لا توجد بيانات</div>
                            @endif
                            <div class="w-100 mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center bg-danger bg-opacity-10 rounded-3 p-3">
                                    <span class="fw-bold text-danger"><i class="fa fa-money-bill-wave me-2"></i>إجمالي المصروفات</span>
                                    <span class="fw-bold text-danger fs-4">{{ number_format(collect($recentExpenses ?? [])->sum('amount'), 2) }} ج</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 text-secondary"><i class="fa fa-history me-2"></i>أحدث المصروفات المسجلة</h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="expensesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">التاريخ</th>
                                    <th class="text-center">المدرسة / العميل</th>
                                    <th class="text-center">البند</th>
                                    <th class="text-center">المبلغ</th>
                                    <th class="text-center">ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentExpenses ?? [] as $exp)
                                <tr>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($exp->date)->format('Y-m-d') }}</td>
                                    <td class="text-center fw-bold">{{ $exp->client_name }}</td>
                                    <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">{{ $exp->category_name }}</span></td>
                                    <td class="text-center fw-bold text-danger">{{ number_format($exp->amount, 2) }} ج</td>
                                    <td class="text-center text-muted small">{{ $exp->notes ?: '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- Close tab-expenses -->

        <!-- REPORTS TAB -->
        <div class="tab-pane fade" id="tab-reports">
            <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="fa fa-chart-line text-gold me-2"></i>تقارير وإحصائيات التكييفات</h3>
            
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted"><i class="fa fa-calendar-alt me-1"></i> من تاريخ</label>
                            <input type="text" id="report_start_date" class="form-control datepicker" placeholder="اختر البداية...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted"><i class="fa fa-calendar-alt me-1"></i> إلى تاريخ</label>
                            <input type="text" id="report_end_date" class="form-control datepicker" placeholder="اختر النهاية...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted"><i class="fa fa-search me-1"></i> بحث شامل</label>
                            <input type="text" id="report_search" class="form-control" placeholder="اسم العميل، الدور، الفصل، نوع الصيانة...">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary w-100 fw-bold" onclick="loadReports()"><i class="fa fa-filter me-1"></i> تصفية</button>
                            <button class="btn btn-outline-secondary" onclick="printReports()" title="طباعة"><i class="fa fa-print"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="reports-container">
                <div class="text-center py-5 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i> جاري تحميل التقارير...</div>
            </div>
        </div>

        <!-- LOGS TAB -->
        <div class="tab-pane fade" id="tab-logs">
            <h3 class="fw-bold mb-4 border-bottom pb-2">سجل العمليات والمدارس</h3>
            <div class="card p-4 shadow-sm border-0 mb-4 bg-white rounded-4">
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted"><i class="fa fa-school me-1"></i> المدرسة / العميل</label>
                        <select id="log_client_id" class="form-select" onchange="filterLogClasses()">
                            <option value="">الكل</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted"><i class="fa fa-door-open me-1"></i> الفصل / الغرفة</label>
                        <select id="log_class_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" data-client="{{ $c->ac_client_id }}" style="display:none;">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted"><i class="fa fa-search me-1"></i> بحث نصي</label>
                        <input type="text" id="log_search" class="form-control" placeholder="بحث إضافي...">
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-muted"><i class="fa fa-calendar-alt me-1"></i> من تاريخ</label>
                        <input type="text" id="log_start_date" class="form-control datepicker" placeholder="اختر البداية...">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-muted"><i class="fa fa-calendar-check me-1"></i> إلى تاريخ</label>
                        <input type="text" id="log_end_date" class="form-control datepicker" placeholder="اختر النهاية...">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100 fw-bold" onclick="loadLogs()"><i class="fa fa-filter me-1"></i> عرض السجل</button>
                    </div>
                </div>
            </div>
            
            <div id="logs-container">
                <div class="text-center py-5 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i> جاري تحميل السجل...</div>
            </div>
        </div>

        <!-- SETTINGS TAB -->
        <div class="tab-pane fade" id="tab-settings">
            <h3 class="fw-bold mb-4 border-bottom pb-2">إعدادات التكييفات</h3>
            <div class="row g-4">
                <!-- Clients Settings -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white border-0 py-3">
                            <h5 class="fw-bold mb-0"><i class="fa fa-building me-2"></i>العملاء والجهات</h5>
                        </div>
                        <div class="card-body bg-white">
                            <form action="{{ route('ac.settings.add') }}" method="POST" class="d-flex gap-2 mb-4">
                                @csrf <input type="hidden" name="type" value="client">
                                <input type="text" name="name" class="form-control bg-light border-0" placeholder="اسم العميل/الجهة" required>
                                <button class="btn btn-primary px-3 rounded-3"><i class="fa fa-plus"></i></button>
                            </form>
                            <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                                @foreach($clients as $c)
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 mb-1 rounded-3 bg-light">
                                    <span class="fw-bold text-dark">{{ $c->name }}</span>
                                    <a href="{{ route('ac.settings.delete', ['type'=>'client', 'id'=>$c->id]) }}" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('تأكيد الحذف؟')"><i class="fa fa-trash"></i></a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Floors Settings -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-success text-white border-0 py-3">
                            <h5 class="fw-bold mb-0"><i class="fa fa-layer-group me-2"></i>الأدوار</h5>
                        </div>
                        <div class="card-body bg-white">
                            <form action="{{ route('ac.settings.add') }}" method="POST" class="d-flex flex-column gap-2 mb-4">
                                @csrf <input type="hidden" name="type" value="floor">
                                <select name="ac_client_id" class="form-select bg-light border-0" required>
                                    <option value="" disabled selected>اختر العميل التابع له...</option>
                                    @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <textarea name="name" class="form-control bg-light border-0" rows="2" placeholder="يمكنك إدخال أكثر من سطر (سطر لكل دور)" required></textarea>
                                <button class="btn btn-success fw-bold rounded-3"><i class="fa fa-plus me-1"></i> إضافة الأدوار</button>
                            </form>
                            <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                                @foreach($floors as $f)
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 mb-1 rounded-3 bg-light">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $f->name }}</div>
                                        <div class="text-muted" style="font-size:11px;"><i class="fa fa-building me-1"></i>{{ collect($clients)->firstWhere('id', $f->ac_client_id)?->name ?? 'غير مرتبط' }}</div>
                                    </div>
                                    <a href="{{ route('ac.settings.delete', ['type'=>'floor', 'id'=>$f->id]) }}" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('تأكيد الحذف؟')"><i class="fa fa-trash"></i></a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classes Settings -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-info text-white border-0 py-3">
                            <h5 class="fw-bold mb-0"><i class="fa fa-door-open me-2"></i>الفصول والغرف</h5>
                        </div>
                        <div class="card-body bg-white">
                            <form action="{{ route('ac.settings.add') }}" method="POST" class="d-flex flex-column gap-2 mb-4">
                                @csrf <input type="hidden" name="type" value="class">
                                <select name="ac_client_id" class="form-select bg-light border-0" onchange="filterSettingsFloors(this.value)" required>
                                    <option value="" disabled selected>اختر العميل التابع له...</option>
                                    @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <select name="ac_floor_id" id="settings_class_floor_id" class="form-select bg-light border-0" required>
                                    <option value="" disabled selected>اختر الدور التابع له...</option>
                                    @foreach($floors as $f)
                                    <option value="{{ $f->id }}" data-client="{{ $f->ac_client_id }}" style="display:none;">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                                <textarea name="name" class="form-control bg-light border-0" rows="2" placeholder="يمكنك إدخال أكثر من سطر (سطر لكل فصل)" required></textarea>
                                <button class="btn btn-info text-white fw-bold rounded-3"><i class="fa fa-plus me-1"></i> إضافة الفصول</button>
                            </form>
                            <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                                @foreach($classes as $c)
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 mb-1 rounded-3 bg-light">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $c->name }}</div>
                                        <div class="text-muted" style="font-size:11px;">
                                            <i class="fa fa-building me-1"></i>{{ collect($clients)->firstWhere('id', $c->ac_client_id)?->name ?? 'غير مرتبط' }} 
                                            <span class="mx-1">|</span>
                                            <i class="fa fa-layer-group me-1"></i>{{ collect($floors)->firstWhere('id', $c->ac_floor_id)?->name ?? 'بدون دور' }}
                                        </div>
                                    </div>
                                    <a href="{{ route('ac.settings.delete', ['type'=>'class', 'id'=>$c->id]) }}" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('تأكيد الحذف؟')"><i class="fa fa-trash"></i></a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Services Settings -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-warning text-dark border-0 py-3">
                            <h5 class="fw-bold mb-0"><i class="fa fa-cogs me-2"></i>خدمات الصيانة الجاهزة (مصنعية)</h5>
                        </div>
                        <div class="card-body bg-white">
                            <form action="{{ route('ac.settings.add') }}" method="POST" class="d-flex flex-column gap-3 mb-4">
                                @csrf <input type="hidden" name="type" value="service">
                                <input type="text" name="name" class="form-control bg-light border-0" placeholder="اسم الخدمة (مثال: شحن فريون)" required>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="cost_price" class="form-control bg-light border-0" placeholder="سعر التكلفة" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="selling_price" class="form-control bg-light border-0" placeholder="سعر البيع" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <button class="btn btn-warning fw-bold text-dark rounded-3"><i class="fa fa-plus me-1"></i> إضافة خدمة</button>
                            </form>
                            <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                                @foreach($acServices ?? [] as $srv)
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 mb-1 rounded-3 bg-light">
                                    <div>
                                        <strong class="d-block text-dark">{{ $srv->name }}</strong>
                                        <small class="text-muted"><i class="fa fa-tag me-1"></i>بيع: {{ number_format($srv->selling_price, 2) }} ج <span class="mx-1">|</span> <i class="fa fa-wallet me-1"></i>تكلفة: {{ number_format($srv->cost_price, 2) }} ج</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary border-0" onclick="editService({{ $srv->id }}, '{{ addslashes($srv->name) }}', {{ $srv->selling_price ?? 0 }}, {{ $srv->cost_price ?? 0 }})" title="تعديل"><i class="fa fa-edit"></i></button>
                                        <a href="{{ route('ac.settings.delete', ['type'=>'service', 'id'=>$srv->id]) }}" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('تأكيد الحذف؟')" title="حذف"><i class="fa fa-trash"></i></a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Expense Categories Settings -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-danger text-white border-0 py-3">
                            <h5 class="fw-bold mb-0"><i class="fa fa-list me-2"></i>بنود المصروفات</h5>
                        </div>
                        <div class="card-body bg-white">
                            <form action="{{ route('ac.settings.add') }}" method="POST" class="d-flex flex-column gap-3 mb-4">
                                @csrf <input type="hidden" name="type" value="expense_category">
                                <input type="text" name="name" class="form-control bg-light border-0" placeholder="اسم البند (مثال: مواصلات، وجبات)" required>
                                <button class="btn btn-danger fw-bold rounded-3"><i class="fa fa-plus me-1"></i> إضافة بند مصروف</button>
                            </form>
                            <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                                @foreach($expenseCategories ?? [] as $cat)
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 mb-1 rounded-3 bg-light">
                                    <strong class="d-block text-dark">{{ $cat->name }}</strong>
                                    <a href="{{ route('ac.settings.delete', ['type'=>'expense_category', 'id'=>$cat->id]) }}" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('تأكيد الحذف؟')" title="حذف"><i class="fa fa-trash"></i></a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

      // Remember active tab
      document.addEventListener("DOMContentLoaded", function() {
          let activeTab = localStorage.getItem('ac_active_tab');
          if(activeTab) {
              let tabBtn = document.querySelector('button[data-bs-target="' + activeTab + '"]');
              if(tabBtn) {
                  let bsTab = new bootstrap.Tab(tabBtn);
                  bsTab.show();
              }
          }
          
          document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(btn => {
              btn.addEventListener('shown.bs.tab', function (e) {
                  localStorage.setItem('ac_active_tab', e.target.getAttribute('data-bs-target'));
              });
          });
      });

    let cart = [];
    
    let currentSchoolNetProfit = 0;
    let currentSchoolIdForProfile = null;

    function openSchoolProfile(id) {
        currentSchoolIdForProfile = id;
        document.getElementById('sp_filter_start').value = '';
        document.getElementById('sp_filter_end').value = '';
        loadSchoolProfileData(id, '', '');
    }

    function applySchoolProfileFilter() {
        if (!currentSchoolIdForProfile) return;
        let start = document.getElementById('sp_filter_start').value;
        let end = document.getElementById('sp_filter_end').value;
        loadSchoolProfileData(currentSchoolIdForProfile, start, end);
    }

    function resetSchoolProfileFilter() {
        if (!currentSchoolIdForProfile) return;
        document.getElementById('sp_filter_start').value = '';
        document.getElementById('sp_filter_end').value = '';
        loadSchoolProfileData(currentSchoolIdForProfile, '', '');
    }

    function loadSchoolProfileData(id, start = '', end = '') {
        Swal.fire({ title: 'جاري تحميل الملف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        let url = `/ac/clients/${id}/profile`;
        if (start || end) {
            url += `?start_date=${start}&end_date=${end}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if(data.success) {
                    const p = data.data;
                    document.getElementById('sp_client_name').innerText = p.client_name;
                    document.getElementById('sp_floors').innerText = p.floors_count;
                    document.getElementById('sp_classes').innerText = p.classes_count;
                    document.getElementById('sp_total_ops').innerText = p.sales_count + p.maint_count;

                    document.getElementById('sp_sales_rev').innerText = Number(p.sales_revenue).toFixed(2);
                    document.getElementById('sp_sales_cost').innerText = Number(p.sales_cost).toFixed(2);
                    document.getElementById('sp_sales_profit').innerText = Number(p.sales_profit).toFixed(2);

                    document.getElementById('sp_maint_rev').innerText = Number(p.maint_revenue).toFixed(2);
                    document.getElementById('sp_maint_cost').innerText = Number(p.maint_cost).toFixed(2);
                    document.getElementById('sp_maint_profit').innerText = Number(p.maint_profit).toFixed(2);

                    document.getElementById('sp_discounts').innerText = Number(p.total_discounts).toFixed(2);
                    document.getElementById('sp_expenses').innerText = Number(p.total_expenses).toFixed(2);

                    currentSchoolNetProfit = Number(p.net_profit);
                    document.getElementById('sp_net_profit').innerText = currentSchoolNetProfit.toFixed(2) + ' ج';
                    
                    let netCard = document.getElementById('sp_net_card');
                    let netStatus = document.getElementById('sp_net_status');
                    
                    if(currentSchoolNetProfit > 0) {
                        netCard.className = "card border-0 shadow-sm rounded-4 h-100 bg-success bg-opacity-10 border border-success-subtle";
                        document.getElementById('sp_net_profit').className = "display-4 fw-bold mb-0 text-success";
                        netStatus.innerHTML = '<span class="text-success fw-bold"><i class="fa fa-smile me-1"></i>المدرسة تحقق ربحاً</span>';
                    } else if (currentSchoolNetProfit < 0) {
                        netCard.className = "card border-0 shadow-sm rounded-4 h-100 bg-danger bg-opacity-10 border border-danger-subtle";
                        document.getElementById('sp_net_profit').className = "display-4 fw-bold mb-0 text-danger";
                        netStatus.innerHTML = '<span class="text-danger fw-bold"><i class="fa fa-frown me-1"></i>المدرسة تتسبب في خسارة</span>';
                    } else {
                        netCard.className = "card border-0 shadow-sm rounded-4 h-100 bg-warning bg-opacity-10 border border-warning-subtle";
                        document.getElementById('sp_net_profit').className = "display-4 fw-bold mb-0 text-warning";
                        netStatus.innerHTML = '<span class="text-warning fw-bold"><i class="fa fa-meh me-1"></i>نقطة التعادل (لا ربح ولا خسارة)</span>';
                    }

                    document.getElementById('sp_sim_discount').value = '';
                    document.getElementById('sp_sim_result').innerText = '';

                    let m = bootstrap.Modal.getInstance(document.getElementById('schoolProfileModal'));
                    if (!m) {
                        m = new bootstrap.Modal(document.getElementById('schoolProfileModal'));
                    }
                    m.show();
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            }).catch(err => {
                Swal.fire('خطأ', 'فشل الاتصال بالسيرفر.', 'error');
            });
    }

    function simulateDiscount() {
        let val = parseFloat(document.getElementById('sp_sim_discount').value) || 0;
        let resEl = document.getElementById('sp_sim_result');
        let newNet = currentSchoolNetProfit - val;
        
        if(val === 0) {
            resEl.innerText = '';
            return;
        }

        if(newNet > 0) {
            resEl.innerHTML = `<span class="text-success"><i class="fa fa-check-circle me-1"></i>ستظل كسبان: ${newNet.toFixed(2)} ج</span>`;
        } else if (newNet < 0) {
            resEl.innerHTML = `<span class="text-danger"><i class="fa fa-exclamation-triangle me-1"></i>إحذر! ستتحول لخسارة: ${Math.abs(newNet).toFixed(2)} ج</span>`;
        } else {
            resEl.innerHTML = `<span class="text-warning-emphasis"><i class="fa fa-balance-scale me-1"></i>المحصلة ستكون صفر (رأس برأس)</span>`;
        }
    }
    
    function animateBtn(btnElement) {
        if(!btnElement) return;
        btnElement.classList.remove('clicked-anim');
        void btnElement.offsetWidth;
        btnElement.classList.add('clicked-anim');
        setTimeout(() => btnElement.classList.remove('clicked-anim'), 300);
    }

    function showPlusOne(btnElement) {
        if(!btnElement) return;
        let span = document.createElement('span');
        span.className = 'plus-one';
        span.innerText = '+1';
        btnElement.appendChild(span);
        setTimeout(() => span.remove(), 600);
    }
    
    function resetActive(containerId) {
        document.querySelectorAll(`#${containerId} .pos-btn`).forEach(b => b.classList.remove('active'));
    }

    function switchToExpensesTab() {
        let selectedClientInput = document.getElementById('form_client_id');
        let select = document.getElementById('quick_exp_client');
        if (select) {
            if (selectedClientInput && selectedClientInput.value) {
                select.value = selectedClientInput.value;
            } else {
                select.value = "";
            }
        }
        
        let quickModal = new bootstrap.Modal(document.getElementById('quickExpenseModal'));
        quickModal.show();
    }

    function selectClient(id, name, btn) {
        animateBtn(btn);
        resetActive('step-clients'); btn.classList.add('active');
        document.getElementById('form_client_id').value = id;
        document.getElementById('lbl_client').innerText = name;
        document.getElementById('step-floors').style.display = 'block';

        document.querySelectorAll('#floors-grid .pos-btn').forEach(el => {
            if (el.getAttribute('data-client-id') == id) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
        
        document.querySelectorAll('#classes-grid .pos-btn').forEach(el => {
            if (el.getAttribute('data-client-id') == id) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
        
        document.getElementById('step-classes').style.display = 'none';
        document.getElementById('step-type').style.display = 'none';
        document.getElementById('step-items').style.display = 'none';
        
        document.getElementById('form_floor_id').value = '';
        document.getElementById('lbl_floor').innerText = '-';
        resetActive('step-floors');
        
        document.getElementById('form_class_id').value = '';
        document.getElementById('lbl_class').innerText = '-';
        resetActive('step-classes');
    }

    let selectedFloors = [];
    let selectedClasses = [];

    function checkMultiSelection() {
        let isMulti = selectedFloors.length > 1 || selectedClasses.length > 1;
        let btnSale = document.getElementById('btn-type-sale');
        if (btnSale) {
            if (isMulti) {
                btnSale.style.display = 'none';
                if (document.getElementById('form_type').value === 'sale') {
                    document.getElementById('step-items').style.display = 'none';
                    document.getElementById('form_type').value = '';
                    resetActive('step-type');
                }
            } else {
                btnSale.style.display = 'flex';
            }
        }
    }

    function selectFloor(id, name, btn) {
        animateBtn(btn);
        
        if (btn.classList.contains('active')) {
            btn.classList.remove('active');
            selectedFloors = selectedFloors.filter(f => f.id != id);
        } else {
            btn.classList.add('active');
            selectedFloors.push({id: id, name: name});
        }
        
        document.getElementById('form_floor_id').value = selectedFloors.length > 0 ? selectedFloors[0].id : '';
        document.getElementById('form_multi_floors_text').value = selectedFloors.map(f => f.name).join('، ');
        document.getElementById('lbl_floor').innerText = selectedFloors.length > 0 ? selectedFloors.map(f => f.name).join('، ') : '-';
        
        let clientId = document.getElementById('form_client_id').value;
        let floorIds = selectedFloors.map(f => f.id.toString());
        document.querySelectorAll('#classes-grid .pos-btn').forEach(el => {
            let elClientId = el.getAttribute('data-client-id');
            let elFloorId = el.getAttribute('data-floor-id');
            
            if (elClientId == clientId) {
                if (selectedFloors.length === 0) {
                    el.style.display = 'none';
                } else if (!elFloorId || floorIds.includes(elFloorId)) {
                    el.style.display = 'flex';
                } else {
                    el.style.display = 'none';
                }
            } else {
                el.style.display = 'none';
            }
        });

        document.getElementById('step-classes').style.display = 'block';
        checkMultiSelection();
    }

    function selectAllFloors() {
        let buttons = document.querySelectorAll('#floors-grid .pos-btn');
        let visibleButtons = Array.from(buttons).filter(b => window.getComputedStyle(b).display !== 'none');
        let shouldSelect = visibleButtons.some(b => !b.classList.contains('active'));
        
        visibleButtons.forEach(btn => {
            if (shouldSelect && !btn.classList.contains('active')) {
                btn.click();
            } else if (!shouldSelect && btn.classList.contains('active')) {
                btn.click();
            }
        });
    }
    
    function skipToClasses() {
        selectedFloors = [];
        document.getElementById('form_floor_id').value = '';
        document.getElementById('form_multi_floors_text').value = '';
        document.getElementById('lbl_floor').innerText = 'بدون دور';
        resetActive('step-floors');
        
        let clientId = document.getElementById('form_client_id').value;
        document.querySelectorAll('#classes-grid .pos-btn').forEach(el => {
            if (el.getAttribute('data-client-id') == clientId) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
        
        document.getElementById('step-classes').style.display = 'block';
        checkMultiSelection();
    }

    function selectClass(id, name, btn) {
        animateBtn(btn);
        
        if (btn.classList.contains('active')) {
            btn.classList.remove('active');
            selectedClasses = selectedClasses.filter(c => c.id != id);
        } else {
            btn.classList.add('active');
            selectedClasses.push({id: id, name: name});
        }
        
        document.getElementById('form_class_id').value = selectedClasses.length > 0 ? selectedClasses[0].id : '';
        document.getElementById('form_multi_classes_text').value = selectedClasses.map(c => c.name).join('، ');
        document.getElementById('lbl_class').innerText = selectedClasses.length > 0 ? selectedClasses.map(c => c.name).join('، ') : '-';
        
        document.getElementById('step-type').style.display = 'block';
        checkMultiSelection();
    }

    function selectAllClasses() {
        let buttons = document.querySelectorAll('#classes-grid .pos-btn');
        let visibleButtons = Array.from(buttons).filter(b => window.getComputedStyle(b).display !== 'none');
        let shouldSelect = visibleButtons.some(b => !b.classList.contains('active'));
        
        visibleButtons.forEach(btn => {
            if (shouldSelect && !btn.classList.contains('active')) {
                btn.click();
            } else if (!shouldSelect && btn.classList.contains('active')) {
                btn.click();
            }
        });
    }

    function skipToType() {
        selectedClasses = [];
        document.getElementById('form_class_id').value = '';
        document.getElementById('form_multi_classes_text').value = '';
        document.getElementById('lbl_class').innerText = 'بدون فصل';
        resetActive('step-classes');
        document.getElementById('step-type').style.display = 'block';
        checkMultiSelection();
    }

    function selectType(type, btn) {
        animateBtn(btn);
        resetActive('step-type'); btn.classList.add('active');
        document.getElementById('form_type').value = type;
        document.getElementById('step-items').style.display = 'block';
        
        document.getElementById('grid-sale-items').style.display = 'none';
        document.getElementById('grid-maint-items').style.display = 'none';
        document.getElementById('manual-maintenance-div').style.display = 'block';

        if(type === 'sale') {
            document.getElementById('items-title').innerText = '5. اختر تكييف للبيع والتركيب';
            document.getElementById('grid-sale-items').style.display = 'grid';
        } else {
            document.getElementById('items-title').innerText = '5. اختر قطع الصيانة أو أدخلها يدوياً';
            document.getElementById('grid-maint-items').style.display = 'grid';
        }
    }

    const barcodeSound = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg');
    const cashRegisterSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2003/2003-preview.mp3');
    
    function playCashierSound() {
        try {
            barcodeSound.currentTime = 0;
            barcodeSound.play().catch(e => console.log('Audio play blocked:', e));
        } catch(e) {}
    }

    function playCheckoutSuccessSound() {
        try {
            cashRegisterSound.currentTime = 0;
            cashRegisterSound.play().catch(e => console.log('Audio play blocked:', e));
        } catch(e) {}
    }

    function setManualMaint() {
        let name = document.getElementById('manual_maint_name').value;
        let sellPrice = parseFloat(document.getElementById('manual_maint_price').value);
        let costPrice = parseFloat(document.getElementById('manual_maint_cost').value) || 0;
        
        if(!name || !sellPrice || sellPrice <= 0) return alert('الرجاء إدخال نوع الصيانة وسعر البيع');
        
        playCashierSound();
        let manualId = 'manual_' + Date.now();
        cart.push({ id: manualId, name: name, selling_price: sellPrice, cost_price: costPrice, quantity: 1, is_manual: true });
        
        document.getElementById('manual_maint_name').value = '';
        document.getElementById('manual_maint_price').value = '';
        document.getElementById('manual_maint_cost').value = '';
        
        renderCart();
    }

    function addToCart(id, name, price, btnElement = null, isManual = false, costPrice = 0) {
        if(btnElement) {
            animateBtn(btnElement);
            showPlusOne(btnElement);
        }
        playCashierSound();
        let existing = cart.find(i => i.id === id);
        if(existing) {
            existing.quantity++;
        } else {
            cart.push({ id: id, name: name, selling_price: price, quantity: 1, is_manual: isManual, cost_price: costPrice });
        }
        renderCart();
    }

    function updateQty(id, delta) {
        let item = cart.find(i => i.id === id);
        if(!item) return;
        item.quantity += delta;
        if(item.quantity <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        renderCart();
    }

    function updatePrice(id, newPrice) {
        let item = cart.find(i => i.id === id);
        if(item) {
            item.selling_price = parseFloat(newPrice) || 0;
            renderCart();
        }
    }

    function renderCart() {
        let c = document.getElementById('cart-items-container');
        if(cart.length === 0) {
            c.innerHTML = '<div class="text-muted text-center mt-4">الفاتورة فارغة</div>';
        } else {
            c.innerHTML = '';
            cart.forEach(item => {
                if (item.is_expense) {
                    c.innerHTML += `
                        <div class="cart-item bg-danger bg-opacity-10 border-danger border-start border-4">
                            <div style="flex:1">
                                <div class="fw-bold text-danger"><i class="fa fa-money-bill-wave me-1"></i>مصروف: ${item.name}</div>
                                <div class="text-muted small">هذا المصروف لن يظهر للعميل في الطباعة ولن يحسب عليه</div>
                            </div>
                            <div class="fw-bold text-danger">${item.amount} ج</div>
                        </div>
                    `;
                } else {
                    c.innerHTML += `
                        <div class="cart-item">
                            <div style="flex:1">
                                <div class="fw-bold">${item.name}</div>
                                <div>
                                    <input type="number" value="${item.selling_price}" class="form-control form-control-sm d-inline-block" style="width:80px" onchange="updatePrice('${item.id}', this.value)"> ج
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="updateQty('${item.id}', -1)">-</button>
                                <span>${item.quantity}</span>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="updateQty('${item.id}', 1)">+</button>
                            </div>
                        </div>
                    `;
                }
            });
        }
        updateTotal();
    }

    function updateTotal() {
        let realCart = cart.filter(i => !i.is_expense);
        let t = realCart.reduce((sum, item) => sum + (item.quantity * item.selling_price), 0);
        let d = parseFloat(document.getElementById('form_discount').value) || 0;
        t -= d;
        if(t < 0) t = 0;
        document.getElementById('cart-total-val').innerText = t.toFixed(2);
        document.getElementById('form_items').value = JSON.stringify(realCart);
    }

    async function submitForm() {
        if(!document.getElementById('form_client_id').value) return alert('يجب اختيار العميل');
        if(!document.getElementById('form_type').value) return alert('يجب اختيار نوع العملية');
        if(cart.length === 0) return alert('يجب اختيار أصناف أو إدخال صيانة يدوية');
        
        let form = document.getElementById('checkout-form');
        let formData = new FormData(form);
        let btn = form.querySelector('button[onclick="submitForm()"]');
        let origText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>جاري الحفظ...';
        btn.disabled = true;

        try {
            let response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            btn.innerHTML = origText;
            btn.disabled = false;

            let result = await response.json();
            
            if (response.ok && result.success) {
                
                playCheckoutSuccessSound();

                Swal.fire({
                    title: 'تمت العملية بنجاح!',
                    text: 'هل ترغب في طباعة الفاتورة الآن؟',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'نعم، اطبع الفاتورة',
                    cancelButtonText: 'لا، شكراً',
                    backdrop: `rgba(0,0,123,0.4)`
                }).then((r) => {
                    if (r.isConfirmed) {
                        let opId = result.id || result.op_id;
                        window.open('/ac/invoice/' + opId, '_blank');
                    }
                    window.location.reload();
                });

            } else {
                alert(result.message || 'حدث خطأ غير معروف');
            }
        } catch (error) {
            btn.innerHTML = origText;
            btn.disabled = false;
            alert('حدث خطأ في الاتصال بالخادم. يرجى المحاولة مرة أخرى.');
            console.error(error);
        }
    }

    async function loadReports() {
        let start = document.getElementById('report_start_date').value;
        let end = document.getElementById('report_end_date').value;
        let search = document.getElementById('report_search').value;
        let container = document.getElementById('reports-container');
        
        container.innerHTML = '<div class="text-center py-5 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i> جاري تحميل التقارير...</div>';
        
        try {
            let res = await fetch(`{{ route('ac.reports.ajax') }}?start_date=${start}&end_date=${end}&search=${encodeURIComponent(search)}`);
            let html = await res.text();
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<div class="alert alert-danger">حدث خطأ أثناء تحميل التقارير</div>';
        }
    }

    function filterLogClasses() {
        let clientId = document.getElementById('log_client_id').value;
        let classSelect = document.getElementById('log_class_id');
        classSelect.value = '';
        Array.from(classSelect.options).forEach(opt => {
            if (opt.value === '') {
                opt.style.display = 'block';
            } else if (opt.getAttribute('data-client') == clientId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    async function loadLogs() {
        let start = document.getElementById('log_start_date').value;
        let end = document.getElementById('log_end_date').value;
        let search = document.getElementById('log_search').value;
        let clientId = document.getElementById('log_client_id').value;
        let classId = document.getElementById('log_class_id').value;
        let container = document.getElementById('logs-container');
        
        container.innerHTML = '<div class="text-center py-5 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i> جاري تحميل السجل...</div>';
        
        try {
            let res = await fetch(`{{ route('ac.reports.ajax') }}?view=logs&start_date=${start}&end_date=${end}&search=${encodeURIComponent(search)}&client_id=${clientId}&class_id=${classId}`);
            let html = await res.text();
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<div class="alert alert-danger">حدث خطأ أثناء تحميل السجل</div>';
        }
    }

    function filterSettingsFloors(clientId) {
        let floorSelect = document.getElementById('settings_class_floor_id');
        if (!floorSelect) return;
        floorSelect.value = '';
        Array.from(floorSelect.options).forEach(opt => {
            if (opt.value === '') {
                opt.style.display = 'block';
            } else if (opt.getAttribute('data-client') == clientId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function printReports() {
        let originalContent = document.body.innerHTML;
        let printContent = document.getElementById('tab-reports').innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload(); // To restore event listeners
    }

    // Load reports and initialize datepicker automatically on page load
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof flatpickr !== 'undefined') {
            flatpickr(".datepicker", { dateFormat: "Y-m-d" });
        }
        
        // Add enter key listener for search
        document.getElementById('report_search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') loadReports();
        });
        document.getElementById('log_search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') loadLogs();
        });

        loadReports();
        loadLogs();
    });
</script>
<!-- Quick Add Client Modal -->
<div class="modal fade" id="quickAddClientModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fa fa-user-plus text-primary me-2"></i>إضافة عميل سريع</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-bold">اسم العميل <span class="text-danger">*</span></label>
            <input type="text" id="quick-client-name" class="form-control" placeholder="أدخل اسم العميل">
        </div>
        <button class="btn btn-primary w-100 fw-bold rounded-pill" onclick="saveQuickClient()">حفظ واختيار</button>
      </div>
    </div>
  </div>
</div>

<!-- Quick Add Setting Modal (Floor/Class) -->
<div class="modal fade" id="quickAddSettingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="quickAddSettingTitle">إضافة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-bold" id="quickAddSettingLabel">الاسم <span class="text-danger">*</span></label>
            <textarea id="quick-setting-name" class="form-control" rows="3" placeholder="يمكنك إدخال أكثر من سطر..."></textarea>
            <input type="hidden" id="quick-setting-type">
        </div>
        <button class="btn btn-primary w-100 fw-bold rounded-pill" onclick="saveQuickSetting()">حفظ واختيار</button>
      </div>
    </div>
  </div>
</div>

<!-- Recent Invoices Modal -->
<div class="modal fade" id="recentInvoicesModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fa fa-history text-primary me-2"></i>أحدث الفواتير المسجلة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>رقم</th>
                        <th>الوقت</th>
                        <th>العميل</th>
                        <th>النوع</th>
                        <th>الإجمالي</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody id="recent-invoices-body">
                    <tr><td colspan="6" class="text-center">جاري التحميل...</td></tr>
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    function showRecentInvoices() {
        new bootstrap.Modal(document.getElementById('recentInvoicesModal')).show();
        let tbody = document.getElementById('recent-invoices-body');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> جاري التحميل...</td></tr>';
        
        fetch('{{ route("ac.recent.invoices") }}')
            .then(res => res.json())
            .then(data => {
                if(data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">لا توجد فواتير حديثة</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map(op => {
                    let statusBadge = '';
                    let rowClass = '';
                    if (op.status === 'cancelled') {
                        statusBadge = '<span class="badge bg-danger ms-1">ملغي</span>';
                        rowClass = 'text-decoration-line-through text-muted opacity-50';
                    } else if (op.status === 'returned') {
                        statusBadge = '<span class="badge bg-warning ms-1">مرتجع</span>';
                        rowClass = 'text-muted';
                    }
                    
                    return `
                    <tr class="${rowClass}">
                        <td class="fw-bold">#${op.id} ${statusBadge}</td>
                        <td class="text-muted"><small>${op.time}</small></td>
                        <td class="fw-bold">${op.client_name}</td>
                        <td>
                            ${op.type === 'sale' ? '<span class="badge bg-primary">بيع</span>' : '<span class="badge bg-info">صيانة</span>'}
                        </td>
                        <td class="fw-bold text-dark">${parseFloat(op.total_amount).toLocaleString()} ج</td>
                        <td>
                            <a href="/ac/invoice/${op.id}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill ${op.status !== 'active' ? 'disabled' : ''}"><i class="fa fa-print me-1"></i> طباعة</a>
                        </td>
                    </tr>
                    `;
                }).join('');
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">حدث خطأ أثناء تحميل الفواتير</td></tr>';
            });
    }

    function showQuickAddClient() {
        document.getElementById('quick-client-name').value = '';
        new bootstrap.Modal(document.getElementById('quickAddClientModal')).show();
    }

    function showQuickAddSetting(type) {
        document.getElementById('quick-setting-type').value = type;
        document.getElementById('quick-setting-name').value = '';
        let title = type === 'floor' ? 'إضافة دور جديد' : 'إضافة فصل جديد';
        let label = type === 'floor' ? 'اسم الدور (مثل: الأرضي)' : 'اسم الفصل (مثل: 1/A)';
        let icon = type === 'floor' ? 'fa-layer-group' : 'fa-door-open';
        document.getElementById('quickAddSettingTitle').innerHTML = `<i class="fa ${icon} text-primary me-2"></i>${title}`;
        document.getElementById('quickAddSettingLabel').innerHTML = `${label} <span class="text-danger">*</span>`;
        new bootstrap.Modal(document.getElementById('quickAddSettingModal')).show();
    }

    function saveQuickSetting() {
        let name = document.getElementById('quick-setting-name').value.trim();
        let type = document.getElementById('quick-setting-type').value;
        if(!name) return alert('يرجى إدخال الاسم');
        
        let clientId = document.getElementById('form_client_id').value;
        if (!clientId) return alert('يرجى اختيار العميل أولاً من الخطوة 1');
        
        let floorId = null;
        if (type === 'class' && selectedFloors.length > 0) {
            floorId = selectedFloors[0].id;
        }
        
        let btn = document.querySelector('#quickAddSettingModal .btn-primary');
        let oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> جاري الحفظ...';
        btn.disabled = true;

        let reqBody = { name: name, type: type, ac_client_id: clientId };
        if (floorId) reqBody.ac_floor_id = floorId;

        fetch('{{ route("ac.settings.ajax") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(reqBody)
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            
            let gridId = type === 'floor' ? 'floors-grid' : 'classes-grid';
            let iconClass = type === 'floor' ? 'fa-layer-group' : 'fa-door-open';
            let clickFunc = type === 'floor' ? 'selectFloor' : 'selectClass';
            let grid = document.getElementById(gridId);
            
            let lastDiv = null;
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    let div = document.createElement('div');
                    div.className = 'pos-btn';
                    div.setAttribute('data-client-id', clientId);
                    if (type === 'class' && floorId) {
                        div.setAttribute('data-floor-id', floorId);
                    }
                    div.style.display = 'flex';
                    div.innerHTML = `<i class="fa ${iconClass}"></i><span>${item.name}</span>`;
                    div.onclick = function() { window[clickFunc](item.id, item.name, this); };
                    grid.insertBefore(div, grid.firstChild);
                    lastDiv = div;
                });
            }
            
            bootstrap.Modal.getInstance(document.getElementById('quickAddSettingModal')).hide();
            
            if (lastDiv) lastDiv.click();
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'تمت الإضافة بنجاح', showConfirmButton: false, timer: 1500 });
        })
        .catch(err => {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            alert('حدث خطأ أثناء الحفظ');
        });
    }

    function saveQuickClient() {
        let name = document.getElementById('quick-client-name').value.trim();
        if(!name) return alert('يرجى إدخال اسم العميل');
        
        let btn = document.querySelector('#quickAddClientModal .btn-primary');
        let oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> جاري الحفظ...';
        btn.disabled = true;

        fetch('{{ route("ac.client.ajax") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            
            // Add to grid
            let grid = document.getElementById('clients-grid');
            let div = document.createElement('div');
            div.className = 'pos-btn';
            div.innerHTML = `<i class="fa fa-building"></i><span>${data.name}</span>`;
            div.onclick = function() { selectClient(data.id, data.name, this); };
            grid.insertBefore(div, grid.firstChild);
            
            bootstrap.Modal.getInstance(document.getElementById('quickAddClientModal')).hide();
            
            // Auto select
            div.click();
            
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'تمت الإضافة بنجاح', showConfirmButton: false, timer: 1500 });
        })
        .catch(err => {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            alert('حدث خطأ أثناء حفظ العميل');
        });
    }

    function normalizeArabic(text) {
        if(!text) return '';
        return text.replace(/ة/g, 'ه')
                   .replace(/ى/g, 'ي')
                   .replace(/يي/g, 'ي')
                   .replace(/أ|إ|آ/g, 'ا');
    }

    function filterItems() {
        let query = normalizeArabic(document.getElementById('item-search').value.toLowerCase().trim());
        // Filter Sale Items
        document.querySelectorAll('#grid-sale-items .pos-btn').forEach(btn => {
            let text = normalizeArabic(btn.innerText.toLowerCase());
            btn.style.display = text.includes(query) ? 'flex' : 'none';
        });
        // Filter Maint Items
        document.querySelectorAll('#grid-maint-items .pos-btn').forEach(btn => {
            let text = normalizeArabic(btn.innerText.toLowerCase());
            btn.style.display = text.includes(query) ? 'flex' : 'none';
        });
    }
</script>

<!-- Quick Expense Modal -->
<div class="modal fade" id="quickExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fa fa-money-bill-wave me-2"></i>تسجيل مصروف سريع</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickExpenseForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">اختر المدرسة / العميل</label>
                            <select class="form-select" name="ac_client_id" id="quick_exp_client" required>
                                <option value="">-- اختر --</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">بند المصروف</label>
                            <select class="form-select" name="ac_expense_category_id" required>
                                <option value="">-- اختر --</option>
                                @foreach($expenseCategories as $ec)
                                    <option value="{{ $ec->id }}">{{ $ec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الخزينة / المحفظة</label>
                            <select class="form-select" name="account_id" required>
                                <option value="">-- اختر --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">المبلغ</label>
                            <input type="number" step="0.01" class="form-control" name="amount" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">التاريخ</label>
                            <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">ملاحظات / بيان</label>
                            <input type="text" class="form-control" name="notes">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-3"><i class="fa fa-save me-1"></i>حفظ المصروف</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- School Profile Modal -->
<div class="modal fade" id="schoolProfileModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow" style="overflow: hidden;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h4 class="modal-title fw-bold mb-0"><i class="fa fa-school me-2"></i>ملف المدرسة: <span id="sp_client_name"></span></h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-4">
                <!-- Date Filter -->
                <div class="row g-3 align-items-end mb-4 bg-white p-3 rounded-4 shadow-sm mx-0">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted"><i class="fa fa-calendar-alt me-1"></i> من تاريخ</label>
                        <input type="date" id="sp_filter_start" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted"><i class="fa fa-calendar-alt me-1"></i> إلى تاريخ</label>
                        <input type="date" id="sp_filter_end" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100 fw-bold" onclick="applySchoolProfileFilter()"><i class="fa fa-filter me-1"></i> تصفية</button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary w-100 fw-bold" onclick="resetSchoolProfileFilter()"><i class="fa fa-times me-1"></i> إعادة</button>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Basic Info -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-secondary mb-4 border-bottom pb-2">نظرة عامة</h5>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fs-5"><i class="fa fa-layer-group text-primary me-2"></i>عدد الأدوار</span>
                                    <span class="fs-4 fw-bold" id="sp_floors">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fs-5"><i class="fa fa-door-open text-primary me-2"></i>عدد الفصول</span>
                                    <span class="fs-4 fw-bold" id="sp_classes">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fs-5"><i class="fa fa-receipt text-primary me-2"></i>إجمالي العمليات</span>
                                    <span class="fs-4 fw-bold" id="sp_total_ops">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sales & Maintenance Details -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-secondary mb-4 border-bottom pb-2">تفاصيل التعاملات</h5>
                                <div class="row text-center g-3">
                                    <div class="col-sm-6">
                                        <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success-subtle">
                                            <h6 class="fw-bold text-success mb-3"><i class="fa fa-shopping-cart me-2"></i>المبيعات</h6>
                                            <div class="d-flex justify-content-between small mb-1"><span>الإيرادات:</span><strong id="sp_sales_rev">0</strong></div>
                                            <div class="d-flex justify-content-between small mb-1"><span>التكلفة:</span><strong class="text-danger" id="sp_sales_cost">0</strong></div>
                                            <div class="d-flex justify-content-between border-top pt-1 mt-2"><span>المكسب:</span><strong class="text-success fs-6" id="sp_sales_profit">0</strong></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning-subtle">
                                            <h6 class="fw-bold text-warning-emphasis mb-3"><i class="fa fa-wrench me-2"></i>الصيانة</h6>
                                            <div class="d-flex justify-content-between small mb-1"><span>الإيرادات:</span><strong id="sp_maint_rev">0</strong></div>
                                            <div class="d-flex justify-content-between small mb-1"><span>التكلفة:</span><strong class="text-danger" id="sp_maint_cost">0</strong></div>
                                            <div class="d-flex justify-content-between border-top pt-1 mt-2"><span>المكسب:</span><strong class="text-success fs-6" id="sp_maint_profit">0</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger bg-opacity-10 border-danger-subtle border">
                            <div class="card-body">
                                <h5 class="fw-bold text-danger mb-4 border-bottom border-danger-subtle pb-2">الخصومات والمصروفات</h5>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fs-5 text-danger">إجمالي الخصومات</span>
                                    <span class="fs-4 fw-bold text-danger" id="sp_discounts">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 text-danger">إجمالي المصروفات</span>
                                    <span class="fs-4 fw-bold text-danger" id="sp_expenses">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Profit & Simulator -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100" id="sp_net_card" style="transition: all 0.4s;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                <h4 class="mb-3">المحصلة النهائية من المدرسة</h4>
                                <h1 class="display-4 fw-bold mb-0" id="sp_net_profit" style="transition: color 0.3s;">0 ج</h1>
                                <p class="fs-5 mt-2 mb-4" id="sp_net_status"></p>
                                
                                <div class="w-100 bg-light p-3 rounded-4 border">
                                    <h5 class="fw-bold mb-3"><i class="fa fa-calculator text-primary me-2"></i>محاكي الخصومات الإضافية</h5>
                                    <div class="input-group input-group-lg" style="max-width: 400px; margin: 0 auto;">
                                        <span class="input-group-text bg-white">لو عملنا خصم بـ</span>
                                        <input type="number" class="form-control fw-bold text-center text-danger" id="sp_sim_discount" placeholder="0" onkeyup="simulateDiscount()">
                                        <span class="input-group-text bg-white">ج</span>
                                    </div>
                                    <div class="mt-3 fs-5 fw-bold" id="sp_sim_result" style="height: 30px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="fa fa-edit me-2"></i>تعديل خدمة صيانة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ac.settings.update') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="service">
                <input type="hidden" name="id" id="edit_srv_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الخدمة</label>
                        <input type="text" name="name" id="edit_srv_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">التكلفة (الشراء)</label>
                            <input type="number" name="cost_price" id="edit_srv_cost" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">سعر البيع للعميل</label>
                            <input type="number" name="selling_price" id="edit_srv_sell" class="form-control" step="0.01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark rounded-3"><i class="fa fa-save me-1"></i>حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editService(id, name, sellPrice, costPrice) {
        document.getElementById('edit_srv_id').value = id;
        document.getElementById('edit_srv_name').value = name;
        document.getElementById('edit_srv_sell').value = sellPrice;
        document.getElementById('edit_srv_cost').value = costPrice;
        
        let editModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
        editModal.show();
    }

    document.getElementById('expenseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>جاري الحفظ...';

        fetch('{{ route("ac.expenses.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                playCheckoutSuccessSound();
                Swal.fire({
                    icon: 'success', title: 'تم بنجاح', text: data.message, timer: 2000, showConfirmButton: false
                }).then(() => {
                    document.getElementById('expenseForm').reset();
                    window.location.reload();
                });
            } else {
                Swal.fire('خطأ', data.message || 'حدث خطأ غير متوقع', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('خطأ', 'تفاصيل الخطأ: ' + err.message, 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save me-2"></i>حفظ المصروف';
        });
    });

    let quickExpForm = document.getElementById('quickExpenseForm');
    if (quickExpForm) {
        quickExpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');
            
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>جاري الحفظ...';

            fetch('{{ route("ac.expenses.store") }}', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    playCheckoutSuccessSound();
                    Swal.fire({
                        icon: 'success', title: 'تم بنجاح', text: data.message, timer: 1500, showConfirmButton: false
                    }).then(() => {
                        let catSelect = quickExpForm.querySelector('select[name="ac_expense_category_id"]');
                        let catName = catSelect.options[catSelect.selectedIndex].text;
                        let amount = quickExpForm.querySelector('input[name="amount"]').value;
                        
                        cart.push({
                            id: 'exp_' + Date.now(),
                            name: catName,
                            amount: amount,
                            selling_price: 0,
                            quantity: 1,
                            is_expense: true
                        });
                        renderCart();
                        
                        let quickModal = bootstrap.Modal.getInstance(document.getElementById('quickExpenseModal'));
                        if (quickModal) quickModal.hide();
                        quickExpForm.reset();
                    });
                } else {
                    Swal.fire('خطأ', data.message || 'حدث خطأ غير متوقع', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('خطأ', 'تفاصيل الخطأ: ' + err.message, 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-1"></i>حفظ المصروف';
            });
        });
    }
</script>

<script>
// ❄️ Splash Screen Animation
(function() {
    const splash = document.getElementById('ac-splash');
    if (!splash) return;
    
    // Generate random snowflakes
    const snowChars = ['❄', '❅', '❆', '✻', '✼'];
    for (let i = 0; i < 25; i++) {
        const flake = document.createElement('div');
        flake.className = 'snowflake';
        flake.textContent = snowChars[Math.floor(Math.random() * snowChars.length)];
        flake.style.left = Math.random() * 100 + 'vw';
        flake.style.fontSize = (0.8 + Math.random() * 1.2) + 'rem';
        flake.style.animationDuration = (0.3 + Math.random() * 0.4) + 's';
        flake.style.animationDelay = (Math.random() * 0.2) + 's';
        flake.style.opacity = 0.3 + Math.random() * 0.7;
        document.body.appendChild(flake);
    }
    
    setTimeout(() => {
        splash.classList.add('hide');
        setTimeout(() => {
            document.querySelectorAll('.snowflake').forEach(f => f.remove());
        }, 300); // wait for fade out
    }, 500);
})();
</script>
<!-- DataTables & Chart.js -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        if($.fn.DataTable && $('#expensesTable').length > 0) {
            $('#expensesTable').DataTable({
                "pageLength": 15,
                "lengthChange": false,
                "language": {
                    "search": "بحث:",
                    "paginate": {
                        "first": "الأول",
                        "last": "الأخير",
                        "next": "التالي",
                        "previous": "السابق"
                    },
                    "info": "عرض _START_ إلى _END_ من أصل _TOTAL_ مصروف",
                    "infoEmpty": "لا توجد مصروفات مسجلة",
                    "zeroRecords": "لم يتم العثور على نتائج",
                },
                "order": [[0, "desc"]]
            });
        }

        // Initialize Chart
        @if(count($expensesByCategory ?? []) > 0)
        if(document.getElementById('expensesPieChart')) {
            const ctx = document.getElementById('expensesPieChart').getContext('2d');
            const data = @json($expensesByCategory);
            const labels = Object.keys(data);
            const values = Object.values(data);
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: ['#e11d48', '#2563eb', '#16a34a', '#d97706', '#9333ea', '#0891b2', '#475569', '#f43f5e', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { font: { family: "'Cairo', sans-serif" } } }
                    }
                }
            });
        }
        @endif
    });
</script>
</body>
</html>

