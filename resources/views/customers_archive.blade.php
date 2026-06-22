<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أرشيف العملاء الشامل - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary-color: #0f172a; --accent-color: #0284c7; --success-color: #10b981; }
        body { font-family: 'Cairo', sans-serif; background: #f4f7fb; overflow-x: hidden; }
        .main-content { margin-right: 260px; padding: 35px 30px; }

        .vip-card { border-radius: 20px; padding: 25px; color: white; border: none; transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden; }
        .vip-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
        .vip-card .bg-icon { position: absolute; left: -15px; bottom: -15px; font-size: 7rem; opacity: 0.15; transform: rotate(-10deg); }
        .vip-blue { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        .vip-red { background: linear-gradient(135deg, #991b1b, #ef4444); }
        .vip-gold { background: linear-gradient(135deg, #b45309, #f59e0b); }

        .vip-title { font-size: 0.95rem; font-weight: 800; margin-bottom: 12px; color: rgba(255,255,255,0.9); }
        .vip-name { font-size: 1.6rem; font-weight: 900; margin-bottom: 5px; }
        .vip-val { font-size: 1.1rem; font-weight: 800; color: #fef08a; }

        .filter-wrapper { background: #fff; border-radius: 18px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2eaf4; }

        .table-card { background: #fff; border-radius: 20px; border: 1px solid #e2eaf4; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; }
        .table thead th { background: #1a3a5f; color: white; font-weight: 700; font-size: 0.85rem; padding: 15px; border: none; white-space: nowrap; text-align: center; }
        .table tbody tr { transition: all 0.2s; border-bottom: 1px solid #f1f5f9; cursor: pointer; }
        .table tbody tr:hover { background: #f0f7ff; transform: scale(1.002); }
        .table tbody td { padding: 15px; color: #334155; font-weight: 600; vertical-align: middle; font-size: 0.95rem; text-align: center; }

        .badge-count { background: #eff6ff; color: #2563eb; padding: 6px 12px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; display: inline-block;}
        .debt-historical { color: #64748b; font-weight: 800; }
        .debt-current { color: #dc2626; font-weight: 900; background: #fef2f2; padding: 6px 12px; border-radius: 12px; display: inline-block;}
        .debt-clear { color: #16a34a; font-weight: 800; background: #f0fdf4; padding: 6px 12px; border-radius: 12px; display: inline-block;}
        .profit-text { color: #059669; font-weight: 900; font-size: 1.1rem; }
        
        .btn-whatsapp { background-color: #25D366; color: white; border: none; font-weight: 700; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.2); transition: 0.2s; }
        .btn-whatsapp:hover { background-color: #128C7E; color: white; transform: scale(1.05); }

        .profile-avatar { width: 60px; height: 60px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 900; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); flex-shrink: 0; }
        .profile-stat { border-right: 2px solid #e2eaf4; padding-right: 15px; }
        .profile-stat:last-child { border-right: none; }
        .trans-table th { background: #f8fafc !important; color: #475569 !important; font-weight: 800 !important; }
        .trans-table td { font-size: 0.9rem; font-weight: 700; }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    @if(session('success')) <div class="alert alert-success fw-bold rounded-4 animate__animated animate__fadeInDown"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error') && !session('open_modal')) <div class="alert alert-danger fw-bold rounded-4 animate__animated animate__fadeInDown"><i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}</div> @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1a3a5f;"><i class="fa fa-users-viewfinder me-2 text-primary"></i>إدارة وعلاقات العملاء (CRM)</h2>
            <p class="text-muted small mb-0">تحليل الأرباح، المديونيات التاريخية، ومتابعة التواصل المباشر مع العملاء</p>
        </div>
        <button class="btn btn-primary rounded-pill fw-bold shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fa fa-user-plus me-1"></i> تسجيل عميل يدوياً
        </button>
    </div>

    <div class="row g-3 mb-4 animate__animated animate__fadeInUp">
        <div class="col-md-4">
            <div class="vip-card vip-blue">
                <i class="fa fa-cart-shopping bg-icon"></i>
                <div class="vip-title"><i class="fa fa-medal text-warning me-1"></i> أكثر عميل تعاملاً معنا (ولاء)</div>
                <div class="vip-name">{{ $topPurchaser->name ?? 'لا يوجد عملاء' }}</div>
                <div class="vip-val">نفذ {{ $topPurchaser->total_purchases ?? 0 }} عملية شراء</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="vip-card vip-red">
                <i class="fa fa-file-invoice-dollar bg-icon"></i>
                <div class="vip-title"><i class="fa fa-fire text-warning me-1"></i> أعلى عميل سحب مديونيات تاريخياً</div>
                <div class="vip-name">{{ $topDebtor->name ?? 'لا يوجد عملاء' }}</div>
                <div class="vip-val">إجمالي سحوباته: {{ fmtMoney($topDebtor->total_historical_debts ?? 0) }} ج</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="vip-card vip-gold">
                <i class="fa fa-crown bg-icon text-white"></i>
                <div class="vip-title"><i class="fa fa-star text-white me-1"></i> العميل الذهبي (الأكثر ربحية)</div>
                <div class="vip-name">{{ $topProfitable->name ?? 'لا يوجد عملاء' }}</div>
                <div class="vip-val text-white">أدخل مكسب: {{ fmtMoney($topProfitable->total_profit ?? 0) }} ج</div>
            </div>
        </div>
    </div>

    <div class="filter-wrapper mb-4 animate__animated animate__fadeIn">
        <form method="GET" action="{{ route('customers.archive') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 fw-bold" placeholder="ابحث باسم العميل أو رقم الهاتف..." value="{{ $search }}" autocomplete="on">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa fa-location-dot text-danger"></i></span>
                    <input type="text" name="city" class="form-control bg-light border-start-0 fw-bold" placeholder="تصفية حسب المنطقة/العنوان..." value="{{ $cityFilter }}" autocomplete="on">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 fw-bold rounded-3">بحث وتصفية</button>
                @if($search || $cityFilter)
                    <a href="{{ route('customers.archive') }}" class="btn btn-outline-danger rounded-3 px-3"><i class="fa fa-times"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-card table-responsive animate__animated animate__fadeInUp">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-start">بيانات العميل</th>
                    <th>عدد العمليات</th>
                    <th>إجمالي ديونه السابقة (ككل)</th>
                    <th>المديونية الحالية (المتبقية)</th>
                    <th>مجموع المكسب الصافي</th>
                    <th>تواصل مباشر</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $idx => $customer)
                    @php
                        // اقرأ كود البورتال من جدول customers (إن وُجد)
                        $portalCode = \Illuminate\Support\Facades\DB::table('customers')
                            ->where('name', $customer->name)
                            ->value('portal_code');
                    @endphp
                    <tr data-bs-toggle="modal" data-bs-target="#customerProfileModal_{{ $idx }}">
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="fw-bold text-dark fs-5">{{ $customer->name }}</div>
                                @if($portalCode)
                                    <span class="badge bg-dark text-warning fw-bold px-2 py-1"
                                          style="font-family: monospace; letter-spacing: 1px; cursor:pointer;"
                                          onclick="event.stopPropagation(); copyPortalCode('{{ $portalCode }}', '{{ $customer->name }}');"
                                          title="انسخ كود البوابة">
                                        <i class="fa fa-key me-1"></i>{{ $portalCode }}
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('customers.generate_portal_code') }}" class="m-0 d-inline" onclick="event.stopPropagation();">
                                        @csrf
                                        <input type="hidden" name="customer_name" value="{{ $customer->name }}">
                                        <input type="hidden" name="customer_phone" value="{{ $customer->phone ?? '' }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning fw-bold rounded-pill px-2 py-1" style="font-size: 0.7rem;" title="ولّد كود بوابة للعميل">
                                            <i class="fa fa-key me-1"></i>توليد كود
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="text-muted small"><i class="fa fa-location-dot text-danger me-1"></i>{{ $customer->address }}</div>
                            
                            {{-- 💡 النوتة (تم ضبط البحث باستخدام TRiM و LIKE لتجاهل المسافات واستثناء كلمة تعثر) --}}
                            @php
                                $latestNote = \Illuminate\Support\Facades\DB::table('installments')
                                    ->where('customer_name', 'LIKE', '%' . trim($customer->name) . '%')
                                    ->whereNotNull('notes')
                                    ->whereRaw("TRIM(notes) != ''")
                                    ->where('notes', '!=', 'تعثر')
                                    ->orderBy('id', 'desc')
                                    ->value('notes');
                            @endphp
                            
                            @if(!empty($latestNote))
                                <div class="mt-2 p-2 rounded-3 shadow-sm" style="background:#fffbeb; border:1px dashed #f59e0b; font-size:0.85rem; color:#b45309; max-width: 300px; line-height: 1.4;">
                                    <i class="fa fa-comment-dots me-1"></i>
                                    <strong>ملاحظة:</strong> {{ $latestNote }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge-count"><i class="fa fa-shopping-bag me-1"></i> {{ $customer->total_purchases ?? 0 }} عملية</span>
                        </td>
                        <td>
                            <div class="debt-historical">{{ fmtMoney($customer->total_historical_debts ?? 0) }} ج</div>
                            <small class="text-muted" style="font-size: 0.7rem;">ما سحبه طوال فترته</small>
                        </td>
                        <td>
                            @if(($customer->total_current_debts ?? 0) > 0)
                                <span class="debt-current">{{ fmtMoney($customer->total_current_debts) }} ج</span>
                            @else
                                <span class="debt-clear"><i class="fa fa-check-circle me-1"></i>خالص</span>
                            @endif
                        </td>
                        <td>
                            @if(($customer->total_profit ?? 0) > 0)
                                <div class="profit-text">+ {{ fmtMoney($customer->total_profit) }} ج</div>
                            @else
                                <span class="text-muted fw-bold">—</span>
                            @endif
                        </td>
                        <td>
                            @if($customer->phone && $customer->phone != '-')
                                @php
                                    $waPhone = preg_replace('/[^0-9]/', '', $customer->phone);
                                    if (str_starts_with($waPhone, '0')) { $waPhone = '2' . $waPhone; }
                                @endphp
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="btn btn-sm btn-whatsapp rounded-pill px-3" onclick="event.stopPropagation();">
                                    <i class="fa-brands fa-whatsapp fs-5"></i>
                                </a>
                                <div class="small fw-bold text-muted mt-1" style="font-family: monospace; font-size: 0.75rem;">{{ $customer->phone }}</div>
                            @else
                                <span class="badge bg-light text-muted border">لا يوجد رقم</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa fa-users-slash fa-4x text-muted opacity-25 mb-3 d-block"></i>
                            <h5 class="text-muted fw-bold">لا يوجد عملاء مسجلين يطابقون شروط البحث!</h5>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($customers as $idx => $customer)
    @php
        $transactions = \Illuminate\Support\Facades\DB::table('installments')
                        ->where('customer_name', 'LIKE', '%' . trim($customer->name) . '%')
                        ->orderBy('created_at', 'desc')
                        ->get();
    @endphp
    
    <div class="modal fade" id="customerProfileModal_{{ $idx }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header bg-dark text-white border-0 p-3">
                    <h5 class="fw-bold m-0"><i class="fa fa-id-card me-2 text-warning"></i> الملف الشامل للعميل</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="bg-white p-4 rounded-4 shadow-sm border mb-4 d-flex align-items-center gap-4 flex-wrap">
                        <div class="profile-avatar">{{ mb_substr($customer->name, 0, 1) }}</div>
                        <div class="flex-grow-1">
                            <h3 class="fw-bold text-dark m-0">{{ $customer->name }}</h3>
                            <div class="text-muted fw-bold mt-2 d-flex gap-4 flex-wrap">
                                <span><i class="fa fa-phone text-success me-1"></i> {{ $customer->phone ?? 'غير مسجل' }}</span>
                                <span><i class="fa fa-location-dot text-danger me-1"></i> {{ $customer->address }}</span>
                                <span><i class="fa fa-calendar-alt text-primary me-1"></i> أضيف في: {{ date('Y-m-d', strtotime($customer->created_at)) }}</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary fw-bold rounded-pill px-3" onclick="toggleEditCustomer({{ $idx }})">
                            <i class="fa fa-pen me-1"></i> تعديل البيانات
                        </button>
                    </div>

                    {{-- نموذج تعديل بيانات العميل (مخفي افتراضياً) --}}
                    <div id="editCustomerBox_{{ $idx }}" class="bg-white p-4 rounded-4 shadow-sm border mb-4" style="display:none; border-color:#3b82f6 !important;">
                        <h6 class="fw-bold text-primary mb-3"><i class="fa fa-user-pen me-2"></i> تعديل بيانات العميل</h6>
                        <form action="{{ route('customers.update') }}" method="POST" class="row g-3">
                            @csrf
                            <input type="hidden" name="original_name" value="{{ $customer->name }}">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-primary">الاسم <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ $customer->name }}" class="form-control fw-bold border-primary" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-success">رقم الهاتف</label>
                                <input type="text" name="phone" value="{{ $customer->phone }}" class="form-control fw-bold border-success" placeholder="01xxxxxxxxx">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">العنوان / المنطقة <span class="text-danger">*</span></label>
                                <input type="text" name="address" value="{{ $customer->address }}" class="form-control fw-bold" required placeholder="مثال: الجيزة - الهرم">
                            </div>
                            <div class="col-12 d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-light fw-bold rounded-pill px-4" onclick="toggleEditCustomer({{ $idx }})">إلغاء</button>
                                <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4"><i class="fa fa-save me-1"></i> حفظ التعديلات</button>
                            </div>
                        </form>
                        <div class="small text-muted mt-2"><i class="fa fa-info-circle me-1"></i> تغيير الاسم بيتنقل تلقائياً على كل عمليات العميل وأقساطه في الأرشيف.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="bg-white p-3 rounded-3 shadow-sm border text-center profile-stat">
                                <small class="text-muted fw-bold d-block mb-1">العمليات المنفذة</small>
                                <h4 class="text-dark fw-bold m-0">{{ $customer->total_purchases ?? 0 }} <i class="fa fa-shopping-cart text-primary fs-6"></i></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white p-3 rounded-3 shadow-sm border text-center profile-stat">
                                <small class="text-muted fw-bold d-block mb-1">إجمالي ما تم سحبه</small>
                                <h4 class="text-dark fw-bold m-0">{{ fmtMoney($customer->total_historical_debts ?? 0) }} ج</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white p-3 rounded-3 shadow-sm border text-center profile-stat">
                                <small class="text-danger fw-bold d-block mb-1">المديونية المتبقية حالياً</small>
                                <h4 class="text-danger fw-bold m-0">{{ fmtMoney($customer->total_current_debts ?? 0) }} ج</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white p-3 rounded-3 shadow-sm border text-center profile-stat">
                                <small class="text-success fw-bold d-block mb-1">الربح المحقق للشركة</small>
                                <h4 class="text-success fw-bold m-0">+{{ fmtMoney($customer->total_profit ?? 0) }} ج</h4>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-3"><i class="fa fa-history text-warning me-2"></i> سجل العمليات والتعاقدات</h5>
                    <div class="table-responsive bg-white rounded-3 shadow-sm border">
                        <table class="table table-hover text-center align-middle trans-table m-0">
                            <thead>
                                <tr>
                                    <th>تاريخ العملية</th>
                                    <th>المنتج / الملاحظات</th>
                                    <th>إجمالي الفاتورة</th>
                                    <th>المقدم المدفوع</th>
                                    <th>المتبقي بالخارج</th>
                                    <th>حالة العملية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $trans)
                                    <tr>
                                        <td dir="ltr" class="text-muted">{{ date('Y-m-d', strtotime($trans->created_at)) }}</td>
                                        <td class="text-primary text-start">
                                            <div class="fw-bold">{{ Str::limit($trans->product_name, 35) }}</div>
                                            
                                            @if(!empty($trans->notes) && trim($trans->notes) != '' && $trans->notes != 'تعثر')
                                                <div class="text-muted mt-2 px-2 py-1 rounded shadow-sm" style="background:#f8fafc; font-size: 0.8rem; border: 1px dashed #cbd5e1; white-space: pre-wrap;">
                                                    <i class="fa fa-sticky-note text-warning me-1"></i> {{ $trans->notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ fmtMoney($trans->total_after_interest) }} ج</td>
                                        <td class="text-success">{{ fmtMoney($trans->down_payment) }} ج</td>
                                        <td class="text-danger">{{ fmtMoney($trans->remaining_balance) }} ج</td>
                                        <td>
                                            @if($trans->remaining_balance <= 0)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-1">مكتمل ومسدد</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-1">قسط نشط</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-muted fw-bold">لا توجد عمليات مسجلة لهذا العميل في الأرشيف.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer bg-white border-0">
                    <button type="button" class="btn btn-dark fw-bold px-5 rounded-pill" data-bs-dismiss="modal">إغلاق الملف</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-primary text-white p-3 border-0">
                <h5 class="modal-title fw-bold m-0"><i class="fa fa-user-plus me-2"></i> إضافة عميل جديد للأرشيف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info fw-bold small border-info">
                        <i class="fa fa-info-circle me-1"></i> العملاء يُسجلون تلقائياً عند أي عملية بيع.. استخدم هذه الشاشة للإدخال اليدوي المسبق فقط.
                    </div>
                    
                    @if($errors->any() && session('open_modal') == 'addCustomerModal')
                        <div class="alert alert-danger fw-bold small rounded-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-primary">اسم العميل بالكامل (ثلاثي على الأقل) <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control fw-bold border-primary fs-5" placeholder="الاسم ثلاثي..." required autocomplete="on">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-success">رقم الواتساب / الهاتف <span class="text-danger">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control fw-bold border-success fs-5" placeholder="01xxxxxxxxx" required autocomplete="on">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">العنوان / المنطقة <span class="text-danger">*</span></label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control fw-bold fs-5" placeholder="مثال: الجيزة - الهرم" required autocomplete="on">
                    </div>
                </div>
                <div class="modal-footer bg-white border-0 p-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold fs-5 shadow-sm">حفظ بيانات العميل</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('open_modal') == 'addCustomerModal')
            var customerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
            customerModal.show();
        @endif
    });

    // إظهار/إخفاء نموذج تعديل بيانات العميل داخل الملف
    function toggleEditCustomer(idx) {
        var box = document.getElementById('editCustomerBox_' + idx);
        if (!box) return;
        box.style.display = (box.style.display === 'none' || !box.style.display) ? 'block' : 'none';
        if (box.style.display === 'block') box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // نسخ كود البوابة بضغطة واحدة
    function copyPortalCode(code, customerName) {
        navigator.clipboard.writeText(code).then(() => {
            const tip = document.createElement('div');
            tip.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#0f172a;color:#fff;padding:12px 22px;border-radius:10px;z-index:9999;box-shadow:0 4px 18px rgba(0,0,0,0.25);font-weight:700;direction:rtl;';
            tip.innerHTML = `<i class="fa fa-check-circle text-success me-1"></i> تم نسخ كود البوابة (${code}) للعميل: ${customerName}`;
            document.body.appendChild(tip);
            setTimeout(() => tip.remove(), 2200);
        }).catch(() => alert('كود البوابة: ' + code));
    }
</script>
</body>
</html>