<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات العامة - النظام المالي</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --main-blue: #0f172a; --main-gold: #d4af37; --bg-light: #f8fafc; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--bg-light); color: var(--main-blue); overflow-x: hidden;}
        .main-content { margin-right: 260px; padding: 40px 30px; }
        .settings-header { background: linear-gradient(135deg, var(--main-blue), #1e293b); color: white; border-radius: 20px; padding: 30px; margin-bottom: 30px; }
        .nav-pills .nav-link { color: #64748b; font-weight: 700; border-radius: 50px; padding: 10px 24px; margin-left: 8px; transition: 0.3s; border: 1px solid transparent; }
        .nav-pills .nav-link.active { background-color: var(--main-blue); color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .card { border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .btn-delete { color: #dc2626; background: #fee2e2; padding: 5px 10px; border-radius: 8px; transition: 0.2s; border:none; text-decoration:none; }
        .btn-delete:hover { background: #fca5a5; color: #991b1b; }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>
@include('sidebar')

<div class="main-content">
       <a href="{{ route('db.backup') }}" class="btn btn-success">
    <i class="fas fa-download"></i> تصدير قاعدة البيانات
</a>
<hr>
    @if(session('success')) <div class="alert alert-success fw-bold rounded-4"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger fw-bold rounded-4"><i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger fw-bold rounded-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="settings-header shadow-sm">
        <h2 class="fw-bold mb-1"><i class="fa fa-cogs me-2 text-warning"></i>الإعدادات العامة للنظام</h2>
        <p class="mb-0 opacity-75">إدارة الحسابات، الموردين، البنزينة، والعمولات البنكية الخاصة بالمحافظ.</p>
    </div>

    <ul class="nav nav-pills mb-4 d-flex flex-wrap gap-2" id="settingsTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-general" type="button">الشركات والموردين</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-gas" type="button">البنزينة والاستقطاعات</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-items" type="button">أسعار الأصناف</button></li>
        <li class="nav-item"><button class="nav-link bg-success bg-opacity-10 text-success border-success" data-bs-toggle="pill" data-bs-target="#tab-wallets" type="button">💳 الخزن والمحافظ (والعمولات)</button></li>
        <li class="nav-item"><button class="nav-link bg-warning bg-opacity-10 text-warning border-warning" data-bs-toggle="pill" data-bs-target="#tab-expense-cats" type="button">📋 بنود المصروفات</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-users" type="button">إدارة الموظفين</button></li>
        <li class="nav-item"><button class="nav-link bg-info bg-opacity-10 text-info border-info" data-bs-toggle="pill" data-bs-target="#tab-system" type="button">⚙️ إعدادات النظام العامة</button></li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">
        
        {{-- 1. تابات الشركات والموردين --}}
        <div class="tab-pane fade show active" id="tab-general">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-primary"><i class="fa fa-truck-loading me-2"></i>الموردين</h5>
                        <form action="{{ route('settings.storeSupplier') }}" method="POST" class="d-flex gap-2 mb-3">
                            @csrf <input type="text" name="name" class="form-control fw-bold" placeholder="اسم المورد..." required>
                            <button type="submit" class="btn btn-primary fw-bold">إضافة</button>
                        </form>
                        <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                            @php $suppliers = DB::table('suppliers')->orderBy('id', 'desc')->get(); @endphp
                            @foreach($suppliers as $sup)
                                <li class="list-group-item d-flex justify-content-between align-items-center fw-bold px-1">
                                    {{ $sup->name }}
                                    <a href="{{ route('settings.destroySupplier', $sup->id) }}" class="btn-delete" onclick="return confirm('تأكيد الحذف؟')"><i class="fa fa-trash"></i></a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-primary"><i class="fa fa-truck me-2"></i>شركات النقل</h5>
                        <form action="{{ route('settings.storeCompany') }}" method="POST" class="d-flex gap-2 mb-3">
                            {{-- ✅ تم تعديل name="company_name" لـ name="name" ليتوافق مع الكنترولر --}}
                            @csrf <input type="text" name="name" class="form-control fw-bold" placeholder="اسم الشركة..." required>
                            <button type="submit" class="btn btn-primary fw-bold">إضافة</button>
                        </form>
                        <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                            @php $companies = DB::table('transport_companies')->orderBy('id', 'desc')->get(); @endphp
                            @foreach($companies as $comp)
                                <li class="list-group-item d-flex justify-content-between align-items-center fw-bold px-1">
                                    {{ $comp->company_name }}
                                    <a href="{{ route('settings.destroyCompany', $comp->id) }}" class="btn-delete" onclick="return confirm('تأكيد الحذف؟')"><i class="fa fa-trash"></i></a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-primary"><i class="fa fa-gas-pump me-2"></i>محطات الوقود</h5>
                        <form action="{{ route('settings.storeStation') }}" method="POST" class="d-flex gap-2 mb-3">
                            {{-- ✅ تم تعديل name="station_name" لـ name="name" ليتوافق مع الكنترولر --}}
                            @csrf <input type="text" name="name" class="form-control fw-bold" placeholder="اسم المحطة..." required>
                            <button type="submit" class="btn btn-primary fw-bold">إضافة</button>
                        </form>
                        <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                            @php $stations = DB::table('gas_stations')->orderBy('id', 'desc')->get(); @endphp
                            @foreach($stations as $st)
                                <li class="list-group-item d-flex justify-content-between align-items-center fw-bold px-1">
                                    {{ $st->station_name }}
                                    <a href="{{ route('settings.destroyStation', $st->id) }}" class="btn-delete" onclick="return confirm('تأكيد الحذف؟')"><i class="fa fa-trash"></i></a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. البنزينة والاستقطاعات --}}
        <div class="tab-pane fade" id="tab-gas">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card p-4 border-danger h-100">
                        <h5 class="fw-bold mb-3 text-danger"><i class="fa fa-percentage me-2"></i>إدارة بنود استقطاعات البنزينة</h5>
                        <form action="{{ url('/settings/store-deduction') }}" method="POST" class="d-flex gap-2 mb-4">
                            @csrf
                            <input type="text" name="name" class="form-control fw-bold border-danger" placeholder="اسم البند الجديد..." required>
                            <input type="number" step="0.01" name="percentage" class="form-control fw-bold text-center border-danger" placeholder="النسبة %" style="max-width:120px;" required>
                            <button type="submit" class="btn btn-danger fw-bold">إضافة</button>
                        </form>
                        <ul class="list-group list-group-flush">
                            @php $currentDeds = DB::table('fuel_deductions')->get(); @endphp
                            @forelse($currentDeds as $ded)
                                <li class="list-group-item d-flex justify-content-between align-items-center fw-bold px-2 py-3 bg-light rounded mb-2 border">
                                    <div>📌 {{ $ded->name }} <span class="badge bg-danger ms-2">{{ $ded->percentage }} %</span></div>
                                    <a href="{{ url('/settings/delete-deduction/'.$ded->id) }}" class="btn-delete" onclick="return confirm('حذف هذا البند؟')"><i class="fa fa-trash"></i></a>
                                </li>
                            @empty
                                <div class="text-center text-muted small py-3 fw-bold">لا توجد استقطاعات مسجلة.</div>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. أسعار الأصناف --}}
        <div class="tab-pane fade" id="tab-items">
            <div class="card p-4">
                <h5 class="fw-bold mb-3 text-primary"><i class="fa fa-box me-2"></i>إدارة الأصناف والأسعار الأساسية</h5>
                <form action="{{ route('settings.storeItem') }}" method="POST" class="row g-2 mb-4">
                    @csrf
                    <div class="col-md-4"><input type="text" name="item_name" class="form-control fw-bold" placeholder="اسم الصنف..." required></div>
                    <div class="col-md-3"><input type="number" step="0.01" name="original_price" class="form-control fw-bold text-center" placeholder="سعر الشراء" required></div>
                    <div class="col-md-3"><input type="number" step="0.01" name="selling_price" class="form-control fw-bold text-center" placeholder="سعر البيع" required></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary fw-bold w-100">حفظ</button></div>
                </form>
                <div class="table-responsive">
                    <table class="table text-center table-hover">
                        <thead class="table-light"><tr><th>#</th><th class="text-start">الصنف</th><th>الشراء</th><th>البيع</th><th>إجراء</th></tr></thead>
                        <tbody>
                            @php $itemsList = DB::table('items')->orderBy('id', 'desc')->get(); @endphp
                            @foreach($itemsList as $itm)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-start fw-bold">{{ $itm->item_name }}</td>
                                    <td class="text-danger fw-bold">{{ number_format($itm->original_price, 2) }} ج</td>
                                    <td class="text-success fw-bold">{{ number_format($itm->selling_price, 2) }} ج</td>
                                    <td><a href="{{ route('settings.destroyItem', $itm->id) }}" class="btn btn-sm text-danger" onclick="return confirm('تأكيد؟')"><i class="fa fa-trash"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. الخزن والمحافظ والعمولات (للأدمن فقط) --}}
        <div class="tab-pane fade" id="tab-wallets">
            @if(session('auth_user') && session('auth_user')->role == 'admin')
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card p-4 border-success h-100">
                        <h5 class="fw-bold mb-3 text-success"><i class="fa fa-wallet me-2"></i>إضافة محفظة / خزنة</h5>
                        <form action="{{ route('settings.storePaymentMethod') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="small fw-bold text-muted">اسم المحفظة / الخزنة</label>
                                <input type="text" name="account_name" class="form-control fw-bold border-success" placeholder="مثال: فودافون كاش أحمد" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted">النوع</label>
                                <select name="category" class="form-select fw-bold border-success" required>
                                    <option value="bank_wallet"> محفظة إلكترونية / بنك</option>
                                    <option value="safe_cash"> خزنة كاش (درج)</option>
                                    <option value="project_sector"> مشروع (يظهر في قسم المشاريع)</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="small fw-bold text-muted">الرصيد الافتتاحي (ج)</label>
                                <input type="number" step="0.01" name="initial_balance" class="form-control fw-bold border-success text-center" value="0">
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm"><i class="fa fa-plus me-1"></i>تسجيل المحفظة</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-dark"><i class="fa fa-percent me-2 text-warning"></i>قواعد العمولات (تُطبق عند خروج الفلوس)</h5>
                        <div class="table-responsive">
                            <table class="table text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">اسم المحفظة / الخزنة</th>
                                        <th>حالة العمولة</th>
                                        <th>قاعدة الخصم</th>
                                        <th>الحد الأقصى</th>
                                        <th>إعدادات</th>
                                        <th>حذف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $wallets = DB::table('accounts')->whereIn('category', ['bank_wallet', 'safe_cash'])->get(); @endphp
                                    @foreach($wallets as $w)
                                        <tr>
                                            {{-- اسم المحفظة مع زر تعديل الاسم --}}
                                            <td class="text-start fw-bold">
                                                <span class="wallet-name-text-{{ $w->id }}">{{ $w->account_name }}</span>
                                                <button onclick="startRename({{ $w->id }})" class="btn btn-sm btn-link text-muted p-0 ms-1" title="تعديل الاسم"><i class="fa fa-pen fa-xs"></i></button>
                                                {{-- فورم تعديل الاسم (مخفي افتراضياً) --}}
                                                <form action="{{ route('settings.renameAccount', $w->id) }}" method="POST" class="d-none wallet-rename-form-{{ $w->id }} mt-1">
                                                    @csrf
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <input type="text" name="account_name" class="form-control form-control-sm fw-bold border-success" value="{{ $w->account_name }}" required style="max-width:180px;">
                                                        <button type="submit" class="btn btn-sm btn-success fw-bold">حفظ</button>
                                                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelRename({{ $w->id }})">إلغاء</button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                @if($w->has_commission) <span class="badge bg-danger">مُفعلة</span>
                                                @else <span class="badge bg-secondary">غير مُفعلة</span> @endif
                                            </td>
                                            <td class="text-muted small fw-bold">
                                                @if($w->has_commission)
                                                    {{ $w->commission_value }} 
                                                    {{ $w->commission_type == 'percentage' ? '%' : 'جنيه لكل ألف' }}
                                                @else - @endif
                                            </td>
                                            <td class="text-danger fw-bold">{{ $w->has_commission && $w->max_commission > 0 ? $w->max_commission . ' ج' : 'مفتوح' }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editCommModal{{ $w->id }}"><i class="fa fa-cog me-1"></i>تعديل العمولة</button>
                                            </td>
                                            @if(session('auth_user') && session('auth_user')->role == 'admin')
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#deleteWalletModal{{ $w->id }}"><i class="fa fa-trash me-1"></i>حذف</button>
                                            </td>
                                            @else
                                            <td></td>
                                            @endif
                                        </tr>

                                        {{-- Modal حذف الخزنة --}}
                                        @if(session('auth_user') && session('auth_user')->role == 'admin')
                                        <div class="modal fade" id="deleteWalletModal{{ $w->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0" style="border-radius: 20px;">
                                                    <div class="modal-header bg-danger text-white border-0">
                                                        <h5 class="modal-title fw-bold"><i class="fa fa-trash me-2"></i>حذف الخزنة: {{ $w->account_name }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('settings.destroyAccount', $w->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4 text-start">
                                                            <div class="alert alert-danger border-danger fw-bold text-center mb-4">
                                                                <i class="fa fa-exclamation-triangle fa-2x d-block mb-2"></i>
                                                                تحذير: هذا الإجراء نهائي ولا يمكن التراجع عنه!<br>
                                                                <small class="fw-normal">شرط الحذف: الرصيد يجب أن يكون صفراً.</small>
                                                            </div>
                                                            <div class="mb-3 bg-light rounded p-3 text-center">
                                                                <div class="fw-bold text-muted small mb-1">الرصيد الحالي</div>
                                                                <div class="fw-bold fs-4 {{ $w->balance > 0 ? 'text-success' : ($w->balance < 0 ? 'text-danger' : 'text-secondary') }}">
                                                                    {{ number_format($w->balance, 2) }} ج
                                                                </div>
                                                                @if($w->balance != 0)
                                                                <small class="text-warning fw-bold"><i class="fa fa-warning me-1"></i>الرصيد غير صفر — تأكد من تصفية الرصيد أولاً.</small>
                                                                @endif
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="fw-bold small text-danger">كلمة مرور المدير للتأكيد</label>
                                                                <input type="password" name="admin_pin" class="form-control fw-bold border-danger text-center" placeholder="أدخل كلمة المرور" required autocomplete="off">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 p-3 pt-0">
                                                            <button type="button" class="btn btn-secondary fw-bold rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                                                            <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4"><i class="fa fa-trash me-1"></i>حذف نهائي</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="modal fade" id="editCommModal{{ $w->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0" style="border-radius: 20px;">
                                                    <div class="modal-header bg-dark text-white border-0">
                                                        <h5 class="modal-title fw-bold"><i class="fa fa-cog me-2 text-warning"></i>إعدادات عمولة سحب: {{ $w->account_name }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('settings.updateCommission') }}" method="POST">
                                                        @csrf <input type="hidden" name="account_id" value="{{ $w->id }}">
                                                        {{-- redirect بعد الحفظ لتاب الخزن --}}
                                                        <input type="hidden" name="redirect_tab" value="wallets">
                                                        <div class="modal-body p-4 text-start">
                                                            
                                                            <div class="form-check form-switch mb-4 bg-light p-3 rounded border border-warning text-end">
                                                                <input class="form-check-input ms-0 me-3 float-none" type="checkbox" name="has_commission" id="has_comm{{ $w->id }}" value="1" {{ $w->has_commission ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold text-danger ms-1" for="has_comm{{ $w->id }}">تفعيل خصم عمولة أوتوماتيك عند الدفع/السحب من هذه المحفظة</label>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="fw-bold small text-muted">طريقة حساب العمولة</label>
                                                                <select name="commission_type" class="form-select fw-bold border-dark">
                                                                    <option value="fixed_per_1000" {{ $w->commission_type == 'fixed_per_1000' ? 'selected' : '' }}>مبلغ ثابت (لكل 1000 جنيه تسحب)</option>
                                                                    <option value="percentage" {{ $w->commission_type == 'percentage' ? 'selected' : '' }}>نسبة مئوية (%) من إجمالي السحب</option>
                                                                </select>
                                                            </div>

                                                            <div class="row g-3">
                                                                <div class="col-6">
                                                                    <label class="fw-bold small text-primary">القيمة (جزء الألف أو النسبة)</label>
                                                                    <input type="number" step="0.01" name="commission_value" class="form-control fw-bold border-primary text-center" value="{{ $w->commission_value }}" placeholder="مثال: 1">
                                                                    <small class="text-muted" style="font-size:0.7rem;">لو كتبت 1 في الثابت = 1 جنيه خصم على كل ألف.</small>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="fw-bold small text-danger">الحد الأقصى للعمولة (ج)</label>
                                                                    <input type="number" step="0.01" name="max_commission" class="form-control fw-bold border-danger text-center" value="{{ $w->max_commission ?? 0 }}" placeholder="مثال: 20">
                                                                    <small class="text-muted" style="font-size:0.7rem;">اكتب 0 لجعله مفتوح بدون حد أقصى.</small>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="fw-bold small text-warning">الحد الأدنى للعمولة (ج)</label>
                                                                    <input type="number" step="0.01" name="min_commission" class="form-control fw-bold border-warning text-center" value="{{ $w->min_commission ?? 0 }}" placeholder="مثال: 20">
                                                                    <small class="text-muted" style="font-size:0.7rem;">لو العمولة المحسوبة أقل من هذا الرقم تتحوّل للحد الأدنى.</small>
                                                                </div>
                                                                {{-- <div class="col-6">
                                                                    <label class="fw-bold small text-secondary">عمولة ثابتة للمبالغ الصغيرة (ج)</label>
                                                                    <input type="number" step="0.01" name="flat_fee_amount" class="form-control fw-bold border-secondary text-center" value="{{ $w->flat_fee_amount ?? 0 }}" placeholder="مثال: 0.50">
                                                                    <small class="text-muted" style="font-size:0.7rem;">مثال: 0.50 جنيه لكل تحويل أقل من الحد الأدنى للمبلغ.</small>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="fw-bold small text-info">الحد الأدنى للمبلغ (ج) — تطبيق العمولة الثابتة</label>
                                                                    <input type="number" step="0.01" name="flat_fee_below" class="form-control fw-bold border-info text-center" value="{{ $w->flat_fee_below ?? 0 }}" placeholder="مثال: 500">
                                                                    <small class="text-muted" style="font-size:0.7rem;">أي تحويل أقل من هذا المبلغ يُطبَّق عليه العمولة الثابتة بدل الحساب العادي. اكتب 0 لتعطيل هذه الخاصية.</small>
                                                                </div>
                                                            </div> --}}
                                                        </div>
                                                        <div class="modal-footer border-0 p-3 pt-0"><button type="submit" class="btn btn-dark w-100 py-2 fw-bold rounded-pill text-warning">حفظ إعدادات العمولة</button></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ─── قسم المشاريع ─── --}}
            @php $projectAccounts = DB::table('accounts')->where('category', 'project_sector')->get(); @endphp
            @if($projectAccounts->count() > 0)
            <div class="card p-4 mt-4">
                <h5 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-diagram-project me-2"></i>حسابات المشاريع</h5>
                <div class="table-responsive">
                    <table class="table text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">اسم المشروع</th>
                                <th>الرصيد</th>
                                <th>حذف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projectAccounts as $pa)
                            <tr>
                                <td class="text-start fw-bold">
                                    <span class="wallet-name-text-{{ $pa->id }}">{{ $pa->account_name }}</span>
                                    <button onclick="startRename({{ $pa->id }})" class="btn btn-sm btn-link text-muted p-0 ms-1" title="تعديل الاسم"><i class="fa fa-pen fa-xs"></i></button>
                                    <form action="{{ route('settings.renameAccount', $pa->id) }}" method="POST" class="d-none wallet-rename-form-{{ $pa->id }} mt-1">
                                        @csrf
                                        <div class="d-flex gap-1 align-items-center">
                                            <input type="text" name="account_name" class="form-control form-control-sm fw-bold border-primary" value="{{ $pa->account_name }}" required style="max-width:180px;">
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold">حفظ</button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelRename({{ $pa->id }})">إلغاء</button>
                                        </div>
                                    </form>
                                </td>
                                <td class="fw-bold {{ $pa->balance > 0 ? 'text-success' : ($pa->balance < 0 ? 'text-danger' : 'text-muted') }}">
                                    {{ number_format($pa->balance, 2) }} ج
                                </td>
                                @if(session('auth_user') && session('auth_user')->role == 'admin')
                                <td>
                                    <button class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#deleteWalletModal{{ $pa->id }}"><i class="fa fa-trash me-1"></i>حذف</button>
                                </td>
                                @else
                                <td></td>
                                @endif
                            </tr>
                            {{-- Modal حذف المشروع --}}
                            @if(session('auth_user') && session('auth_user')->role == 'admin')
                            <div class="modal fade" id="deleteWalletModal{{ $pa->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0" style="border-radius:20px;">
                                        <div class="modal-header bg-danger text-white border-0">
                                            <h5 class="modal-title fw-bold"><i class="fa fa-trash me-2"></i>حذف المشروع: {{ $pa->account_name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('settings.destroyAccount', $pa->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body p-4 text-start">
                                                <div class="alert alert-danger border-danger fw-bold text-center mb-4">
                                                    <i class="fa fa-exclamation-triangle fa-2x d-block mb-2"></i>
                                                    تحذير: هذا الإجراء نهائي!<br>
                                                    <small class="fw-normal">شرط الحذف: الرصيد يجب أن يكون صفراً.</small>
                                                </div>
                                                <div class="mb-3 bg-light rounded p-3 text-center">
                                                    <div class="fw-bold text-muted small mb-1">الرصيد الحالي</div>
                                                    <div class="fw-bold fs-4 {{ $pa->balance != 0 ? 'text-danger' : 'text-secondary' }}">{{ number_format($pa->balance, 2) }} ج</div>
                                                    @if($pa->balance != 0)
                                                    <small class="text-warning fw-bold"><i class="fa fa-warning me-1"></i>الرصيد غير صفر — صفّره أولاً.</small>
                                                    @endif
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fw-bold small text-danger">كلمة مرور المدير</label>
                                                    <input type="password" name="admin_pin" class="form-control fw-bold border-danger text-center" placeholder="أدخل كلمة المرور" required autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 p-3 pt-0">
                                                <button type="button" class="btn btn-secondary fw-bold rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4"><i class="fa fa-trash me-1"></i>حذف نهائي</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @else
                <div class="alert alert-danger fw-bold text-center py-5 rounded-4">
                    <i class="fa fa-lock fa-3x mb-3 d-block"></i>
                    هذا القسم للأدمن فقط — لا يمكنك الوصول إليه.
                </div>
            @endif
        </div>

        {{-- 6. بنود المصروفات (للأدمن فقط) --}}
        <div class="tab-pane fade" id="tab-expense-cats">
            @if(session('auth_user') && session('auth_user')->role == 'admin')
            <div class="row g-4">

                {{-- فورم الإضافة --}}
                <div class="col-md-4">
                    <div class="card p-4 border-warning h-100">
                        <h5 class="fw-bold mb-3 text-warning"><i class="fa fa-tags me-2"></i>إضافة بند مصروف جديد</h5>
                        <form action="{{ route('settings.storeExpenseCategory') }}" method="POST">
                            @csrf
                            {{-- بعد الإضافة يروح لصفحة المصروفات مباشرة --}}
                            <input type="hidden" name="redirect_to" value="{{ route('expenses.index') }}">
                            <div class="mb-3">
                                <label class="small fw-bold text-muted">اسم البند</label>
                                <input type="text" name="name" class="form-control fw-bold border-warning" placeholder="مثال: إيجار، صيانة، مرتبات..." required>
                            </div>
                            <div class="mb-4">
                                <label class="small fw-bold text-muted">وصف مختصر (اختياري)</label>
                                <input type="text" name="description" class="form-control fw-bold" placeholder="توضيح إضافي...">
                            </div>
                            <button type="submit" class="btn btn-warning w-100 fw-bold text-dark shadow-sm">
                                <i class="fa fa-plus-circle me-1"></i> إضافة البند والانتقال للمصروفات
                            </button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-warning fw-bold w-100 rounded-pill">
                                <i class="fa fa-arrow-left me-1"></i> الذهاب للمصروفات بدون إضافة
                            </a>
                        </div>
                    </div>
                </div>

                {{-- جدول البنود الموجودة --}}
                <div class="col-md-8">
                    <div class="card p-4 h-100">
                        <h5 class="fw-bold mb-3 text-dark"><i class="fa fa-list-ul me-2 text-warning"></i>بنود المصروفات الحالية</h5>

                        @php $expenseCats = DB::table('expense_categories')->orderBy('id', 'desc')->get(); @endphp

                        @if($expenseCats->isEmpty())
                            <div class="text-center text-muted py-5 fw-bold">
                                <i class="fa fa-inbox fa-3x mb-3 opacity-25 d-block"></i>
                                لا توجد بنود مصروفات مسجلة بعد.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th class="text-start">اسم البند</th>
                                            <th class="text-start">الوصف</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expenseCats as $cat)
                                            <tr>
                                                <td class="text-muted small fw-bold">{{ $loop->iteration }}</td>
                                                <td class="text-start">
                                                    {{-- وضع عرض الاسم --}}
                                                    <span class="fw-bold text-dark cat-name-text-{{ $cat->id }}">
                                                        📌 {{ $cat->name }}
                                                    </span>
                                                    {{-- حقل تعديل الاسم (مخفي افتراضياً) --}}
                                                    <form action="{{ route('settings.updateExpenseCategory', $cat->id) }}" method="POST" class="d-none cat-edit-form-{{ $cat->id }}">
                                                        @csrf @method('PUT')
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <input type="text" name="name" class="form-control form-control-sm fw-bold border-warning" value="{{ $cat->name }}" required style="max-width:180px;">
                                                            <input type="text" name="description" class="form-control form-control-sm" value="{{ $cat->description }}" placeholder="الوصف..." style="max-width:150px;">
                                                            <button type="submit" class="btn btn-sm btn-warning fw-bold text-dark">حفظ</button>
                                                            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit({{ $cat->id }})">إلغاء</button>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td class="text-start text-muted small cat-desc-text-{{ $cat->id }}">
                                                    {{ $cat->description ?? '—' }}
                                                </td>
                                                <td>
                                                    <button onclick="startEdit({{ $cat->id }})" class="btn btn-sm btn-outline-warning fw-bold rounded-pill px-3 me-1">
                                                        <i class="fa fa-pen"></i>
                                                    </button>
                                                    <a href="{{ route('settings.destroyExpenseCategory', $cat->id) }}"
                                                       class="btn-delete btn btn-sm rounded-pill px-3 fw-bold"
                                                       onclick="return confirm('هل أنت متأكد من حذف بند [ {{ $cat->name }} ]؟')">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            @else
                <div class="alert alert-danger fw-bold text-center py-5 rounded-4">
                    <i class="fa fa-lock fa-3x mb-3 d-block"></i>
                    هذا القسم للأدمن فقط — لا يمكنك الوصول إليه.
                </div>
            @endif
        </div>

        {{-- 5. إدارة الموظفين --}}
        <div class="tab-pane fade" id="tab-users">
            @if(session('auth_user') && session('auth_user')->role == 'admin')
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card p-4 border-primary">
                            <h5 class="fw-bold mb-3 text-primary"><i class="fa fa-user-plus me-2"></i>تسجيل موظف جديد</h5>
                            <form action="{{ route('users.store') }}" method="POST">
                                @csrf
                                <div class="mb-2"><label class="small fw-bold text-muted">اسم الموظف</label><input type="text" name="name" class="form-control fw-bold" required></div>
                                <div class="mb-2"><label class="small fw-bold text-muted">الإيميل (الدخول)</label><input type="text" name="email" class="form-control fw-bold" required></div>
                                <div class="mb-2"><label class="small fw-bold text-muted">الباسورد</label><input type="password" name="password" class="form-control fw-bold" required minlength="4"></div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">الصلاحية</label>
                                    <select name="role" class="form-select fw-bold" required>
                                        <option value="employee">موظف (كاشير)</option>
                                        <option value="admin">مدير نظام (أدمن)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold">حفظ الحساب</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-3 text-dark"><i class="fa fa-users me-2"></i>الموظفين الحاليين</h5>
                            <div class="table-responsive">
                                <table class="table text-center align-middle">
                                    <thead class="table-light"><tr><th>الاسم</th><th>بيانات الدخول</th><th>الصلاحية</th><th>الإجراءات</th></tr></thead>
                                    <tbody>
                                        @php $usersList = DB::table('users')->get(); @endphp
                                        @foreach($usersList as $u)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $u->name }}</td>
                                                <td class="text-muted small">{{ $u->email }}</td>
                                                <td>@if($u->role == 'admin') <span class="badge bg-danger">أدمن</span> @else <span class="badge bg-primary">موظف</span> @endif</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-dark border-0 me-1" onclick="showResetForm('{{ $u->id }}', '{{ $u->name }}')" title="تغيير الباسورد"><i class="fa fa-key"></i></button>
                                                    @if(session('auth_user')->id != $u->id)
                                                        <a href="{{ route('users.destroy', $u->id) }}" class="btn btn-sm text-danger" onclick="return confirm('حذف نهائي؟')"><i class="fa fa-trash"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div id="resetPasswordBox" class="d-none mt-4 p-4 border border-warning rounded-4 bg-light">
                                <h6 class="fw-bold text-dark mb-3">تغيير كلمة المرور لـ: <span id="resetUserName" class="text-danger"></span></h6>
                                <form id="resetPasswordForm" method="POST">
                                    @csrf
                                    <div class="d-flex gap-2">
                                        <input type="password" name="new_password" class="form-control fw-bold border-warning" placeholder="الباسورد الجديد..." required minlength="4">
                                        <button type="submit" class="btn btn-warning fw-bold text-dark text-nowrap px-4">حفظ</button>
                                        <button type="button" class="btn btn-secondary fw-bold" onclick="hideResetForm()">إلغاء</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-danger fw-bold text-center py-4"><i class="fa fa-lock fa-2x mb-2 d-block"></i>للأدمن فقط.</div>
            @endif
        </div>

        {{-- ═══════════════ تاب: إعدادات النظام العامة ═══════════════ --}}
        <div class="tab-pane fade" id="tab-system">
            <form action="{{ route('settings.system.update') }}" method="POST">
                @csrf

                @php
                    $themes = [
                        'navy'    => ['اللون الكحلي',  '#1a3a5f'],
                        'emerald' => ['اللون الأخضر', '#2d8659'],
                        'gold'    => ['اللون الذهبي', '#b8842d'],
                        'rose'    => ['اللون الوردي', '#a13347'],
                        'indigo'  => ['اللون البنفسجي','#3a4fb8'],
                        'teal'    => ['الفيروزي',     '#0d7373'],
                        'slate'   => ['الرمادي',      '#475569'],
                    ];
                    $currentTheme = $system_settings['theme_color'] ?? 'navy';
                @endphp

                {{-- ── لون النظام ── --}}
                <div class="card p-4 mb-3 shadow-sm">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">
                        <i class="fa fa-palette me-2"></i> لون النظام
                    </h5>
                    <p class="text-muted small mb-3">اختر لون يتغيّر بناءً عليه شكل السيستم كله. اضغط على اللون ثم احفظ.</p>
                    <div class="d-flex flex-wrap gap-4">
                        @foreach($themes as $key => [$label, $hex])
                            <label class="theme-option d-flex flex-column align-items-center gap-2 {{ $currentTheme === $key ? 'is-active' : '' }}"
                                   data-theme="{{ $key }}" style="cursor:pointer;">
                                <input type="radio" name="theme_color" value="{{ $key }}"
                                       class="d-none theme-radio"
                                       {{ $currentTheme === $key ? 'checked' : '' }}>
                                <span class="theme-swatch d-flex align-items-center justify-content-center"
                                      style="--sw:{{ $hex }}; background:{{ $hex }};">
                                    <i class="fa fa-check theme-check"></i>
                                </span>
                                <small class="theme-label fw-bold">{{ $label }}</small>
                            </label>
                        @endforeach
                    </div>
                </div>

                <style>
                    .theme-swatch {
                        width: 72px; height: 72px; border-radius: 50%;
                        border: 4px solid #e2e8f0;
                        box-shadow: 0 3px 10px rgba(0,0,0,0.12);
                        transition: all .2s ease;
                        position: relative;
                    }
                    .theme-swatch .theme-check {
                        color: #fff; font-size: 1.6rem;
                        opacity: 0; transform: scale(0.4);
                        transition: all .2s ease;
                        text-shadow: 0 1px 4px rgba(0,0,0,0.4);
                    }
                    .theme-option:hover .theme-swatch { transform: scale(1.08); }
                    .theme-option.is-active .theme-swatch {
                        border-color: #0f172a;
                        transform: scale(1.12);
                        box-shadow: 0 0 0 4px #fff, 0 0 0 7px var(--sw), 0 6px 18px rgba(0,0,0,0.25);
                    }
                    .theme-option.is-active .theme-check { opacity: 1; transform: scale(1); }
                    .theme-label { color: #94a3b8; transition: color .2s; }
                    .theme-option.is-active .theme-label { color: #0f172a; }
                @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>

                <script>
                    document.querySelectorAll('.theme-option').forEach(function (opt) {
                        opt.addEventListener('click', function () {
                            document.querySelectorAll('.theme-option').forEach(function (o) { o.classList.remove('is-active'); });
                            opt.classList.add('is-active');
                            opt.querySelector('.theme-radio').checked = true;
                        });
                    });
                </script>

                {{-- ── البنزينة ── --}}
                <div class="card p-4 mb-3 shadow-sm">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">
                        <i class="fa fa-gas-pump me-2"></i> البنزينة
                    </h5>
                    <label class="form-label fw-bold">نسبة ربح البنزينة</label>
                    <div class="input-group" style="max-width:300px;">
                        <input type="number" step="0.001" min="0" name="fuel_profit_rate"
                               value="{{ $system_settings['fuel_profit_rate'] ?? 0.01 }}"
                               class="form-control fw-bold text-center">
                        <span class="input-group-text fw-bold">نسبة عشرية</span>
                    </div>
                    <small class="text-muted">مثلاً: 0.01 = 1٪ — السيستم بيستخدمها على شاشة البنزينة لحساب الربح تلقائي.</small>
                </div>

                {{-- ── الأقساط ── --}}
                <div class="card p-4 mb-3 shadow-sm">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">
                        <i class="fa fa-file-invoice me-2"></i> الأقساط
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">معدل الفائدة الافتراضي (%)</label>
                            <input type="number" step="0.01" min="0" name="default_interest_rate"
                                   value="{{ $system_settings['default_interest_rate'] ?? 0 }}"
                                   class="form-control fw-bold text-center">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">أقصى عدد شهور للتقسيط</label>
                            <input type="number" min="1" name="max_installment_months"
                                   value="{{ $system_settings['max_installment_months'] ?? 36 }}"
                                   class="form-control fw-bold text-center">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">أقل نسبة مقدم (%)</label>
                            <input type="number" step="0.1" min="0" max="100" name="min_down_payment_pct"
                                   value="{{ $system_settings['min_down_payment_pct'] ?? 0 }}"
                                   class="form-control fw-bold text-center">
                        </div>
                    </div>
                </div>

                {{-- ── الفاتورة ── --}}
                <div class="card p-4 mb-3 shadow-sm">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">
                        <i class="fa fa-receipt me-2"></i> الفاتورة المطبوعة
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">رأس الفاتورة</label>
                            <textarea name="invoice_header_text" rows="3"
                                      class="form-control fw-bold">{{ $system_settings['invoice_header_text'] ?? '' }}</textarea>
                            <small class="text-muted">نص يظهر أعلى كل فاتورة.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">تذييل الفاتورة</label>
                            <textarea name="invoice_footer_text" rows="3"
                                      class="form-control fw-bold">{{ $system_settings['invoice_footer_text'] ?? '' }}</textarea>
                            <small class="text-muted">نص يظهر أسفل كل فاتورة.</small>
                        </div>
                    </div>
                </div>

                {{-- ── التنبيه ── --}}
                <div class="card p-4 mb-3 shadow-sm">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">
                        <i class="fa fa-bell me-2"></i> التنبيهات
                    </h5>
                    <label class="form-label fw-bold">حد التنبيه للرصيد المنخفض (ج)</label>
                    <input type="number" min="0" name="low_balance_threshold"
                           value="{{ $system_settings['low_balance_threshold'] ?? 500 }}"
                           class="form-control fw-bold text-center" style="max-width:300px;">
                    <small class="text-muted">لما رصيد أي خزنة يقل عن الحد ده — يظهر إشعار بأعلى الشاشة.</small>
                </div>

                <button type="submit" class="btn btn-success fw-bold px-5 py-2 shadow-sm">
                    <i class="fa fa-floppy-disk me-1"></i> حفظ الإعدادات
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showResetForm(userId, userName) {
        document.getElementById('resetUserName').textContent = userName;
        document.getElementById('resetPasswordForm').action = '/users/' + userId + '/reset-password';
        document.getElementById('resetPasswordBox').classList.remove('d-none');
    }
    function hideResetForm() { document.getElementById('resetPasswordBox').classList.add('d-none'); }

    // تعديل اسم بند المصروف
    function startEdit(id) {
        document.querySelector('.cat-name-text-' + id).classList.add('d-none');
        document.querySelector('.cat-desc-text-' + id).classList.add('d-none');
        document.querySelector('.cat-edit-form-' + id).classList.remove('d-none');
    }
    function cancelEdit(id) {
        document.querySelector('.cat-name-text-' + id).classList.remove('d-none');
        document.querySelector('.cat-desc-text-' + id).classList.remove('d-none');
        document.querySelector('.cat-edit-form-' + id).classList.add('d-none');
    }

    // تعديل اسم المحفظة / الخزنة
    function startRename(id) {
        document.querySelector('.wallet-name-text-' + id).classList.add('d-none');
        document.querySelector('.wallet-rename-form-' + id).classList.remove('d-none');
    }
    function cancelRename(id) {
        document.querySelector('.wallet-name-text-' + id).classList.remove('d-none');
        document.querySelector('.wallet-rename-form-' + id).classList.add('d-none');
    }

    // فتح التاب الصحيح بعد الـ redirect (لو في tab param في الـ URL)
  // فتح التاب الصحيح وحفظه في الـ Local Storage عشان يفضل ثابت بعد أي تعديل
    document.addEventListener('DOMContentLoaded', function() {
        // 1. استرجاع التابة المحفوظة من المتصفح لو موجودة
        const savedTab = localStorage.getItem('activeSettingsTab');
        if(savedTab) {
            const tabBtn = document.querySelector(`button[data-bs-target="${savedTab}"]`);
            if(tabBtn) {
                const tabInstance = new bootstrap.Tab(tabBtn);
                tabInstance.show();
            }
        }

        // 2. تحديث التابة المحفوظة فوراً عند الضغط على أي تابة جديدة
        const triggerTabList = document.querySelectorAll('button[data-bs-toggle="pill"]');
        triggerTabList.forEach(tab => {
            tab.addEventListener('shown.bs.tab', event => {
                localStorage.setItem('activeSettingsTab', event.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>
</body>
</html>