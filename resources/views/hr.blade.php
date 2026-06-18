<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شؤون الموظفين والرواتب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8fafc; overflow-x: hidden; }
        .main-content { margin-right: 260px; padding: 30px; }
        .hr-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #eef2f6; }
        .stat-box { padding: 10px 15px; border-radius: 12px; font-weight: bold; text-align: center; }
        .btn-action { border-radius: 50px; font-weight: bold; padding: 6px 15px; font-size: 0.85rem; }
        .employee-row { transition: 0.2s; cursor: pointer; }
        .employee-row:hover { background-color: #f8fafc; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .modal-content { border-radius: 20px; border: none; }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    @if(session('success')) 
        <div class="alert alert-success fw-bold rounded-4 mb-4"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> 
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fa fa-users-cog text-primary me-2"></i>إدارة شؤون الموظفين</h2>
            <p class="text-muted fw-bold mb-0">متابعة السلف، الخصومات، المكافآت، وصرف الرواتب</p>
        </div>
        <button class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="fa fa-user-plus me-1"></i> تسجيل موظف جديد
        </button>
    </div>

    <div class="row g-4">
        @forelse($employees as $emp)
            <div class="col-md-6 col-lg-4">
                <div class="hr-card employee-row" 
                     onclick="showTransactions(this)" 
                     title="اضغط لعرض تفاصيل الحركات"
                     data-name="{{ $emp->name }}"
                     data-basic="{{ number_format($emp->basic_salary, 0) }}"
                     data-transactions="{{ json_encode($emp->transactions_details) }}">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa fa-user fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $emp->name }}</h5>
                                <small class="text-muted fw-bold">{{ $emp->phone ?? 'لا يوجد رقم' }}</small>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm rounded-circle" onclick="event.stopPropagation()" data-bs-toggle="modal" data-bs-target="#editEmployeeModal{{ $emp->id }}"><i class="fa fa-pen text-secondary"></i></button>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="stat-box" style="font-size:0.78rem; padding: 8px 6px; background:#d1fae5; color:#065f46; border-radius:10px;">
                                <i class="fa fa-gift fa-xs d-block mb-1"></i>
                                مكافآت
                                <br><strong>{{ number_format($emp->bonuses, 0) }} ج</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box" style="font-size:0.78rem; padding: 8px 6px; background:#fef3c7; color:#92400e; border-radius:10px;">
                                <i class="fa fa-hand-holding-dollar fa-xs d-block mb-1"></i>
                                سلف
                                <br><strong>{{ number_format($emp->advances, 0) }} ج</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box" style="font-size:0.78rem; padding: 8px 6px; background:#fee2e2; color:#991b1b; border-radius:10px;">
                                <i class="fa fa-scissors fa-xs d-block mb-1"></i>
                                خصومات
                                <br><strong>{{ number_format($emp->deductions, 0) }} ج</strong>
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark text-white rounded-3 p-3 d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold opacity-75">الصافي للدفع:</span>
                        <h4 class="mb-0 fw-bold text-warning">{{ number_format($emp->net_salary, 0) }} ج</h4>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-action flex-fill" onclick="event.stopPropagation()" data-bs-toggle="modal" data-bs-target="#actionModal{{ $emp->id }}"><i class="fa fa-plus-minus me-1"></i> سلفة / مكافأة</button>
                        <button class="btn btn-success btn-action flex-fill" onclick="event.stopPropagation()" data-bs-toggle="modal" data-bs-target="#paySalaryModal{{ $emp->id }}"><i class="fa fa-money-bill-wave me-1"></i> صرف الراتب</button>
                    </div>
                </div>
            </div>

            {{-- مودال صرف الراتب (تمت إضافة تاريخ آخر راتب) --}}
            <div class="modal fade" id="paySalaryModal{{ $emp->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius: 20px;">
                        <div class="modal-header bg-dark text-white border-0">
                            <h5 class="modal-title fw-bold"><i class="fa fa-money-check-alt me-2 text-success"></i>صرف راتب: {{ $emp->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('hr.pay') }}" method="POST">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                            <div class="modal-body p-4 text-start">
                                
                                {{-- ✅ الرسالة الجديدة لتاريخ آخر راتب --}}
                                <div class="alert alert-warning fw-bold py-2 mb-4 border-warning border-2">
                                    <i class="fa fa-clock me-2"></i> آخر مرة تم صرف راتب لهذا الموظف: 
                                    <span class="text-danger ms-1">{{ $emp->last_salary_date }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2"><span class="text-muted fw-bold">الراتب الأساسي:</span><span class="fw-bold">{{ number_format($emp->basic_salary, 2) }} ج</span></div>
                                <div class="d-flex justify-content-between mb-2"><span class="text-muted fw-bold">إجمالي المكافآت:</span><span class="fw-bold text-success">+ {{ number_format($emp->bonuses, 2) }} ج</span></div>
<div class="d-flex justify-content-between mb-2">
    <span class="fw-bold text-dark">
        إجمالي السلف:
    </span>

    <span class="fw-bold text-danger">
        - {{ number_format($emp->advances, 2) }} ج
    </span>
</div>                                <div class="d-flex justify-content-between mb-3 border-bottom pb-3"><span class="text-muted fw-bold">إجمالي الخصومات:</span><span class="fw-bold text-danger">- {{ number_format($emp->deductions, 2) }} ج</span></div>
                                <div class="d-flex justify-content-between mb-4"><span class="text-dark fw-bold fs-5">الصافي للدفع:</span><span class="fw-bold fs-4 text-primary">{{ number_format($emp->net_salary, 2) }} ج</span></div>
                                
                                <div class="mb-3">
                                    <label class="fw-bold small text-primary mb-1">المبلغ المصروف فعلياً (ج)</label>
                                    <input type="number" step="1" name="amount" value="{{ $emp->net_salary }}"
                                        class="form-control fw-bold border-primary text-center fs-4 text-primary" required min="0" readonly
                                        style="background-color:#f0f4ff; cursor:not-allowed;">
                                    <small class="text-muted fw-bold">المبلغ محسوب تلقائياً ولا يمكن تعديله</small>
                                </div>
                                <div class="mb-2">
                                    <label class="fw-bold small text-success mb-1">صرف المبلغ من خزنة:</label>
                                    <select name="account_id" class="form-select fw-bold border-success" required>
                                        <option value="" disabled selected>اختر الخزنة...</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance, 0) }} ج)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-3 pt-0">
                                <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill">تأكيد الصرف وتصفية الحساب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- مودال إضافة حركة (سلفة / مكافأة) --}}
            <div class="modal fade" id="actionModal{{ $emp->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0">
                        <div class="modal-header bg-primary text-white border-0">
                            <h5 class="modal-title fw-bold"><i class="fa fa-plus-minus me-2"></i>تسجيل حركة لـ {{ $emp->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('hr.transaction') }}" method="POST">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                            <div class="modal-body p-4 text-start">
                                <div class="mb-3">
                                    <label class="fw-bold small mb-1">نوع الحركة</label>
                                    <select name="type" class="form-select fw-bold border-primary" onchange="toggleAccountSelect(this, 'acc_div_{{ $emp->id }}')" required>
                                        <option value="" disabled selected>اختر نوع الحركة...</option>
                                        <option value="advance">سلفة مالية (تخصم من الخزنة الآن)</option>
                                        <option value="deduction">خصم (تأخير / جزاء)</option>
                                        <option value="bonus">مكافأة / حافز</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold small mb-1">المبلغ (ج)</label>
                                    <input type="number" step="1" name="amount" class="form-control fw-bold border-dark text-center fs-5" required>
                                </div>
                                <div class="mb-3" id="acc_div_{{ $emp->id }}" style="display: none;">
                                    <label class="fw-bold small text-danger mb-1">سحب السلفة من خزنة:</label>
                                    <select name="account_id" class="form-select fw-bold border-danger">
                                        <option value="" disabled selected>اختر الخزنة...</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (متاح: {{ number_format($acc->balance, 0) }} ج)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="fw-bold small mb-1">ملاحظات (السبب)</label>
                                    <input type="text" name="notes" class="form-control fw-bold" placeholder="مثال: سلفة طوارئ، خصم تأخير...">
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-3 pt-0">
                                <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">تسجيل الحركة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- مودال التعديل --}}
            <div class="modal fade" id="editEmployeeModal{{ $emp->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title fw-bold text-dark"><i class="fa fa-pen me-2 text-primary"></i>تعديل بيانات: {{ $emp->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form action="{{ route('hr.update') }}" method="POST" class="mb-4">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                <div class="mb-3"><label class="fw-bold small mb-1">الاسم</label><input type="text" name="name" value="{{ $emp->name }}" class="form-control fw-bold border-dark" required></div>
                                <div class="mb-3"><label class="fw-bold small mb-1">الهاتف</label><input type="text" name="phone" value="{{ $emp->phone }}" class="form-control fw-bold border-dark"></div>
                                <div class="mb-3"><label class="fw-bold small mb-1 text-success">الراتب الأساسي</label><input type="number" step="1" name="basic_salary" value="{{ $emp->basic_salary }}" class="form-control fw-bold border-success" required></div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill mt-2">حفظ التعديلات</button>
                            </form>

                            <hr>
                            <form action="{{ route('hr.delete') }}" method="POST">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                <button type="submit" class="btn btn-outline-danger w-100 fw-bold rounded-pill" onclick="return confirm('هل أنت متأكد من حذف الموظف نهائياً؟')"><i class="fa fa-trash me-1"></i> حذف الموظف</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="col-12">
                <div class="alert alert-secondary text-center fw-bold py-5 rounded-4">
                    <i class="fa fa-users-slash fa-3x mb-3 d-block opacity-50"></i>
                    لا يوجد موظفين مسجلين حالياً.
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- مودال إضافة موظف جديد --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fa fa-user-plus me-2 text-warning"></i>تسجيل موظف جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hr.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="fw-bold small mb-1 text-muted">اسم الموظف بالكامل</label>
                        <input type="text" name="name" class="form-control fw-bold border-dark" placeholder="اكتب اسم الموظف" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-1 text-muted">رقم الهاتف (اختياري)</label>
                        <input type="text" name="phone" class="form-control fw-bold border-dark" placeholder="مثال: 01xxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small text-success mb-1">الراتب الأساسي (ج)</label>
                        <input type="number" step="1" name="basic_salary" class="form-control fw-bold border-success text-center fs-5" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0">
                    <button type="submit" class="btn btn-dark text-warning w-100 fw-bold rounded-pill">اعتماد وتسجيل الموظف</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- مودال تفاصيل الحركات --}}
<div class="modal fade" id="transactionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 20px;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #1e293b, #334155);">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fa fa-list-alt me-2 text-warning"></i>
                    سجل حركات: <span id="txModalName" class="text-warning"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="txModalBody">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showTransactions(cardEl) {
        const name         = cardEl.dataset.name;
        const basic        = cardEl.dataset.basic;
        const transactions = JSON.parse(cardEl.dataset.transactions || '[]');

        document.getElementById('txModalName').textContent = name;

        const typeMap = {
            'advance'   : { label: 'سلفة مالية',    icon: 'fa-hand-holding-dollar', color: '#92400e', bg: '#fef3c7' },
            'deduction' : { label: 'خصم',            icon: 'fa-scissors',            color: '#991b1b', bg: '#fee2e2' },
            'bonus'     : { label: 'مكافأة / حافز', icon: 'fa-gift',                color: '#065f46', bg: '#d1fae5' },
        };

        let html = '';

        if (transactions.length === 0) {
            html = `<div class="text-center py-5 text-muted fw-bold">
                        <i class="fa fa-inbox fa-3x mb-3 d-block opacity-40"></i>
                        لا توجد حركات معلقة لهذا الموظف
                    </div>`;
        } else {
            html += `<div class="alert alert-light border fw-bold mb-3 py-2">
                        <i class="fa fa-circle-info text-primary me-1"></i>
                        الراتب الأساسي: <strong class="text-dark">${basic} ج</strong>
                        &nbsp;|&nbsp; إجمالي الحركات المعلقة: <strong class="text-primary">${transactions.length}</strong>
                    </div>`;
            html += '<div class="d-flex flex-column gap-2">';

            transactions.forEach(tx => {
                const t     = typeMap[tx.type] || { label: tx.type, icon: 'fa-circle', color: '#555', bg: '#eee' };
                const date  = tx.created_at ? tx.created_at.substring(0, 10) : '—';
                const time  = tx.created_at ? tx.created_at.substring(11, 16) : '';
                const notes = tx.notes ? tx.notes : '<span class="text-muted fst-italic">بدون سبب</span>';

                html += `
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 border"
                     style="background:${t.bg}20; border-color:${t.bg}!important;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:42px;height:42px;background:${t.bg};color:${t.color}">
                        <i class="fa ${t.icon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="fw-bold" style="color:${t.color}">${t.label}</span>
                            <span class="fw-bold fs-5" style="color:${t.color}">${parseFloat(tx.amount).toLocaleString('ar-EG')} ج</span>
                        </div>
                        <div class="small text-muted fw-bold mt-1">
                            <i class="fa fa-calendar-alt me-1"></i>${date}
                            ${time ? `<span class="ms-2"><i class="fa fa-clock me-1"></i>${time}</span>` : ''}
                        </div>
                        <div class="small mt-1" style="color:#374151">${notes}</div>
                    </div>
                </div>`;
            });

            html += '</div>';
        }

        document.getElementById('txModalBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('transactionsModal')).show();
    }

    // إظهار وإخفاء الخزنة بناءً على نوع الحركة (السلفة فقط تحتاج خزنة)
    function toggleAccountSelect(selectElement, divId) {
        const div = document.getElementById(divId);
        const accountSelect = div.querySelector('select');
        if (selectElement.value === 'advance') {
            div.style.display = 'block';
            accountSelect.setAttribute('required', 'required');
        } else {
            div.style.display = 'none';
            accountSelect.removeAttribute('required');
        }
    }

    // عرض الأخطاء بـ SweetAlert لو الفاليديشن ضرب
    @if($errors->any() || session('error'))
        document.addEventListener('DOMContentLoaded', function() {
            let errHtml = '<div class="text-start mt-3" dir="rtl"><ul class="text-danger fw-bold lh-lg">';
            @foreach($errors->all() as $err) errHtml += '<li>{!! addslashes($err) !!}</li>'; @endforeach
            @if(session('error')) errHtml += '<li>{!! addslashes(session("error")) !!}</li>'; @endif
            errHtml += '</ul></div>';
            
            Swal.fire({
                icon: 'error',
                title: 'توجد أخطاء في البيانات!',
                html: errHtml,
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#dc2626'
            });
        });
    @endif
</script>
</body>
</html>