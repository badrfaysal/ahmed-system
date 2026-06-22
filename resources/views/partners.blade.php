<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الشركاء وتوزيع الأرباح - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --vip-dark: #0f172a; --vip-gold: #d4af37; --bg-light: #f1f5f9; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--bg-light); color: var(--vip-dark); padding-bottom: 50px; }
        .capital-card { background: linear-gradient(135deg, #1e293b, #0f172a); color: white; border-radius: 20px; border-bottom: 5px solid var(--vip-gold); }
        .partner-row { background: #ffffff; border-radius: 15px; border-right: 5px solid #10b981; transition: 0.3s; }
        .partner-row:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.08); }
        .partner-icon { width: 55px; height: 55px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        @media(max-width:991px){ .main-content{ margin-right:0!important; width:100%!important; padding:70px 16px 30px!important; } }
    </style>
</head>
<body>
    @include('sidebar') 
    <div class="container-fluid py-4 main-content" style="margin-right: 260px; width: calc(100% - 260px);">
        
        @if(session('success')) <div class="alert alert-success animate__animated animate__fadeInDown fw-bold"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger animate__animated animate__fadeInDown fw-bold"><i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}</div> @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fa-solid fa-users-gear text-primary me-2"></i> إدارة الشركاء وتوزيع الأرباح</h3>
            <div>
                <button class="btn btn-dark fw-bold me-2" data-bs-toggle="modal" data-bs-target="#addPartnerModal">
                    <i class="fa-solid fa-plus me-1"></i> إضافة شريك
                </button>
                <button class="btn btn-success fw-bold px-4 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#distributeModal">
                    <i class="fa-solid fa-hand-holding-dollar me-2"></i> توزيع أرباح للكل
                </button>
            </div>
        </div>

        <div class="row mb-5 animate__animated animate__zoomIn">
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-4 shadow-sm border border-primary text-center">
                    <span class="text-muted fw-bold small d-block">الربح الحقيقي للشركة (ما تم تحصيله)</span>
                    <h3 class="fw-black text-primary m-0 mt-2">{{ fmtMoney($real_collected_profit) }} ج</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-white rounded-4 shadow-sm border border-danger text-center">
                    <span class="text-muted fw-bold small d-block">ما تم توزيعه مسبقاً (في محافظ الشركاء)</span>
                    <h3 class="fw-black text-danger m-0 mt-2">{{ fmtMoney($total_distributed_so_far) }} ج</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="capital-card p-3 text-center h-100 d-flex flex-column justify-content-center">
                    <span class="text-white-50 fw-bold small d-block">الربح المتبقي (المتاح للتوزيع الآن)</span>
                    <h3 class="fw-black text-warning m-0 mt-2">{{ number_format($estimated_net_profit, 2) }} ج</h3>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @foreach($partners as $partner)
                @php $percentage = $total_partners_capital > 0 ? ($partner->capital / $total_partners_capital) * 100 : 0; @endphp
                <div class="col-12 animate__animated animate__fadeInUp">
                    <div class="partner-row p-3 d-flex flex-column flex-xl-row align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center flex-grow-1" style="min-width: 250px;">
                            <div class="partner-icon me-3 shadow-sm"><i class="fa-solid fa-user-tie"></i></div>
                            <div>
                                <h5 class="mb-1 fw-bold text-dark">{{ $partner->name }}</h5>
                                <div class="text-muted small fw-bold">رأس المال: {{ fmtMoney($partner->capital) }} ج.م</div>
                            </div>
                        </div>

                        <div class="text-center bg-light p-2 rounded border" style="min-width: 130px;">
                            <small class="d-block text-muted mb-1 fw-bold">النسبة</small>
                            <i class="fa-solid fa-chart-pie text-primary me-1"></i> <strong>{{ number_format($percentage, 2) }}%</strong>
                        </div>

                        <div class="text-center bg-success bg-opacity-10 border border-success p-2 rounded" style="min-width: 150px;">
                            <small class="d-block text-success mb-1 fw-bold">أرباح متاحة للسحب</small>
                            <span class="fs-5 fw-bold text-success">{{ number_format($partner->profit_wallet, 2) }} ج</span>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <form action="{{ route('partners.withdrawProfit') }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="partner_id" value="{{ $partner->id }}">
                                <div class="input-group input-group-sm mb-1 shadow-sm">
                                    <select name="account_id" class="form-select border-danger text-danger fw-bold" required>
                                        <option value="" disabled selected>من خزنة...</option>
                                        @foreach($accounts as $acc) <option value="{{ $acc->id }}">{{ $acc->account_name }}</option> @endforeach
                                    </select>
                                </div>
                                <div class="input-group input-group-sm shadow-sm">
                                    <input type="number" step="0.01" name="amount" class="form-control text-center fw-bold" placeholder="مبلغ السحب" max="{{ $partner->profit_wallet }}" required>
                                    <button class="btn btn-danger fw-bold" type="submit" title="سحب نقدي">سحب كاش</button>
                                </div>
                            </form>

                            <form action="{{ route('partners.reinvest') }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="partner_id" value="{{ $partner->id }}">
                                <div class="input-group input-group-sm shadow-sm mt-4">
                                    <input type="number" step="0.01" name="amount" class="form-control text-center fw-bold" placeholder="مبلغ الرسملة" max="{{ $partner->profit_wallet }}" required>
                                    <button class="btn btn-primary fw-bold" type="submit" title="إضافة لرأس المال">رسملة</button>
                                </div>
                            </form>

                            <button type="button" class="btn btn-outline-dark btn-sm shadow-sm mt-4 fw-bold" onclick="openExitModal({{ $partner->id }}, '{{ $partner->name }}', {{ $partner->capital }}, {{ $partner->profit_wallet }})">
                                تخارج الشريك
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 💡 مودال توزيع الأرباح المطور 💡 --}}
    <div class="modal fade" id="distributeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('partners.distributeProfit') }}" method="POST" class="modal-content border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-hand-holding-dollar text-warning me-2"></i> توزيع أرباح على المساهمين</h5>
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-4 text-center">
                        <span class="small fw-bold">الحد الأقصى للأرباح القابلة للتوزيع حالياً:</span>
                        <h4 class="fw-black text-success mt-1 mb-0">{{ number_format($estimated_net_profit, 2) }} ج</h4>
                    </div>
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold text-muted mb-2">أدخل إجمالي المبلغ المراد توزيعه</label>
                        <input type="number" step="0.01" name="amount" id="dist_amount_input" max="{{ $estimated_net_profit }}" class="form-control form-control-lg text-center fw-bold text-success border-success rounded-4 mb-2" placeholder="مثال: 50000" required>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill fw-bold" onclick="document.getElementById('dist_amount_input').value = {{ $estimated_net_profit }}">توزيع كامل المبلغ المتاح</button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-dark px-4 fw-bold">تأكيد الاعتماد والجدولة</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مودال إضافة شريك --}}
    <div class="modal fade" id="addPartnerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('partners.store') }}" method="POST" class="modal-content border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2"></i>إضافة شريك جديد</h5>
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الشريك</label>
                        <input type="text" name="name" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رأس المال المدفوع (ج.م)</label>
                        <input type="number" step="0.01" name="capital" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">أودع في الخزنة/الحساب</label>
                        <select name="account_id" class="form-select border-dark" required>
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc) <option value="{{ $acc->id }}">{{ $acc->account_name }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">تسجيل الشريك</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مودال التخارج --}}
    <div class="modal fade" id="exitModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('partners.exitPartner') }}" method="POST" class="modal-content border-0 shadow-lg" id="exitForm">
                @csrf
                <input type="hidden" name="partner_id" id="exit_partner_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="exit_partner_name_display"></h5>
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning fw-bold mb-4">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> سيتم تصفية حساب الشريك وخروج كافة مستحقاته من النظام نهائياً.
                    </div>
                    <ul class="list-group list-group-flush mb-3 fw-bold">
                        <li class="list-group-item d-flex justify-content-between align-items-center">رأس المال المسترد: <span id="exit_capital_display" class="text-primary fs-5"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">الأرباح المستحقة: <span id="exit_profit_display" class="text-success fs-5"></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light mt-2 rounded">
                            المبلغ الإجمالي للتصفية: <span id="exit_total_display" class="text-danger fs-4 fw-black"></span>
                        </li>
                    </ul>
                    <div class="mb-3 mt-3">
                        <label class="form-label fw-bold">صرف مستحقات التخارج من خزنة:</label>
                        <select name="account_id" class="form-select border-danger" required>
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc) <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ fmtMoney($acc->balance) }} ج)</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold">تأكيد التخارج وصرف المبلغ</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openExitModal(id, name, capital, profit) {
            document.getElementById('exit_partner_id').value = id;
            document.getElementById('exit_partner_name_display').innerText = "تصفية حساب: " + name;
            document.getElementById('exit_capital_display').innerText = capital.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
            document.getElementById('exit_profit_display').innerText = profit.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
            document.getElementById('exit_total_display').innerText = (capital + profit).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
            new bootstrap.Modal(document.getElementById('exitModal')).show();
        }
    </script>
</body>
</html>