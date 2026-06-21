<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أرشيف العملاء - الإصدار المجمع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --main-green: #00ab67; --excel-yellow: #ffff00; --excel-green: #c6efce; --excel-pink: #ffc7ce; --excel-orange: #ffcc99; }
        body { font-family: 'Cairo', sans-serif; background-color: #f0f7f4; color: #1a2e24; }
        .main-content { margin-right: 260px; padding: 30px; }
        
        .summary-card { background: white; border-radius: 20px; padding: 20px; border-top: 5px solid var(--main-green); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .customer-card { background: white; border-radius: 20px; border: 1px solid #e1eee7; padding: 20px; transition: 0.3s; cursor: pointer; position: relative; overflow: hidden;}
        .customer-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,171,103,0.1); border-color: var(--main-green); }
        
        /* ─── تصميم الـ Excel المعتمد والمدمج ─── */
        .excel-table { width: 100%; border: 2px solid #000; border-collapse: collapse; margin-bottom: 15px; }
        .excel-table td { border: 1px solid #000; padding: 8px; font-weight: bold; text-align: center; font-size: 0.9rem; vertical-align: middle; }
        .ex-header { background: var(--excel-yellow); font-size: 1rem; color: #000; }
        .ex-th-pft { background: #cbd5e1; font-size: 0.9rem; color: #1a2e24; }
        .ex-label { background: var(--excel-orange); width: 40%; }
        .ex-val-green { background: var(--excel-green); }
        .ex-val-pink { background: var(--excel-pink); }
        .ex-val-yellow { background: #ffeb9c; }

        .accordion-button:not(.collapsed) { background-color: #f0fdf4; color: #166534; box-shadow: inset 0 -1px 0 rgba(0,0,0,.125); }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>

    @include('sidebar')

    <div class="main-content">
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="summary-card">
                    <small class="text-muted fw-bold">إجمالي المتبقي (المديونية الكلية)</small>
                    <h2 class="fw-bold text-danger mt-1">{{ number_format($global_rem, 2) }} ج</h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="summary-card" style="border-top-color: #10b981;">
                    <small class="text-muted fw-bold">إجمالي ما تم تحصيله (السيولة)</small>
                    <h2 class="fw-bold text-success mt-1">{{ number_format($global_paid, 2) }} ج</h2>
                </div>
            </div>
        </div>

        <div class="card p-4 border-0 shadow-sm mb-5 rounded-4">
            <form method="GET" action="{{ route('archive.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">البحث (الاسم أو الموبايل)</label>
                    <input type="text" name="search" class="form-control" placeholder="أدخل بيانات البحث.." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">فلترة بالحالة</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ $status_filter == 'all' ? 'selected' : '' }}>كل العملاء</option>
                        <option value="active" {{ $status_filter == 'active' ? 'selected' : '' }}>عليهم مديونية</option>
                        <option value="completed" {{ $status_filter == 'completed' ? 'selected' : '' }}>خالص الحساب</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success w-100 fw-bold rounded-3">تطبيق الفلتر</button>
                    @if($search) <a href="{{ route('archive.index') }}" class="btn btn-outline-danger rounded-3"><i class="fa fa-times"></i></a> @endif
                </div>
            </form>
        </div>

        <div class="row g-4">
            @foreach ($data as $client)
                <div class="col-lg-4 col-md-6">
                    <div class="customer-card" data-bs-toggle="modal" data-bs-target="#modal{{ $client->key }}">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle"><i class="fa fa-user fs-4"></i></div>
                            <span class="badge {{ $client->status == 'completed' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3 py-2">
                                {{ $client->status == 'completed' ? 'مكتمل ✅' : 'عليه مديونية ⚠️' }}
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $client->customer_name }}</h5>
                        <p class="text-muted small mb-3"><i class="fa fa-phone me-1"></i> {{ $client->customer_phone ?? 'بدون رقم' }}</p>
                        
                        <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">إجمالي المتبقي عليه</small>
                                <span class="fw-bold text-danger fs-5">{{ number_format($client->total_remaining, 2) }} ج</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">سجل العمليات</small>
                                <span class="badge bg-dark rounded-pill">{{ $client->purchase_count }} عملية</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- مودال العميل الشامل المجمع --}}
                <div class="modal fade" id="modal{{ $client->key }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 rounded-4 shadow-lg bg-light">
                            <div class="modal-header border-0 pb-0 p-4 bg-white rounded-top-4">
                                <div>
                                    <h4 class="fw-bold text-primary mb-1"><i class="fa fa-folder-open me-2"></i> أرشيف: {{ $client->customer_name }}</h4>
                                    <p class="text-muted small m-0"><i class="fa fa-phone me-1"></i> {{ $client->customer_phone ?? 'لا يوجد' }}</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                
                                <div class="row g-2 mb-4 text-center">
                                    <div class="col-6 bg-white p-3 border rounded-start-4 shadow-sm">
                                        <div class="small text-muted fw-bold">إجمالي ما سدده العميل</div>
                                        <strong class="text-success fs-4">{{ number_format($client->total_paid, 2) }} ج</strong>
                                    </div>
                                    <div class="col-6 bg-white p-3 border rounded-end-4 shadow-sm">
                                        <div class="small text-muted fw-bold">إجمالي المتبقي عليه للشركة</div>
                                        <strong class="text-danger fs-4">{{ number_format($client->total_remaining, 2) }} ج</strong>
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-3 text-secondary"><i class="fa fa-list me-1"></i> تفاصيل العمليات ({{ $client->purchase_count }}):</h6>
                                
                                {{-- عرض العمليات المجمعة كأكورديون --}}
                                <div class="accordion shadow-sm" id="accordion{{ $client->key }}">
                                    @foreach($client->installments as $idx => $inst)
                                        <div class="accordion-item border-0 mb-2 rounded-3 overflow-hidden">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button {{ $idx == 0 ? '' : 'collapsed' }} fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $inst->id }}">
                                                    <div class="d-flex justify-content-between w-100 pe-3 align-items-center">
                                                        <span>{{ $inst->product_name }}</span>
                                                        <span class="badge {{ $inst->remaining_balance > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill">متبقي: {{ number_format($inst->remaining_balance, 0) }} ج</span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $inst->id }}" class="accordion-collapse collapse {{ $idx == 0 ? 'show' : '' }}" data-bs-parent="#accordion{{ $client->key }}">
                                                <div class="accordion-body bg-white p-4">
                                                    
                                                    {{-- 💡 الجدول المدمج (التفاصيل + المدفوعات في إكسيل واحد) --}}
                                                    <table class="excel-table">
                                                        {{-- رأس تفاصيل العملية --}}
                                                        <tr class="ex-header"><td colspan="3">تفاصيل العملية رقم: #{{ $inst->id }}</td></tr>
                                                        <tr><td class="ex-label" colspan="2">تاريخ العملية</td><td class="ex-val-green">{{ date('Y-m-d', strtotime($inst->start_date)) }}</td></tr>
                                                        
                                                        @if($inst->installment_months > 0)
                                                            {{-- حالة التقسيط --}}
                                                            <tr><td class="ex-label" colspan="2">سعر الكاش الأساسي</td><td class="ex-val-green">{{ number_format($inst->cash_price, 2) }} ج</td></tr>
                                                            <tr><td class="ex-label" colspan="2">المقدم المدفوع</td><td class="ex-val-green">{{ number_format($inst->down_payment, 2) }} ج</td></tr>
                                                            <tr><td class="ex-label" colspan="2">عدد شهور التقسيط</td><td class="ex-val-green">{{ $inst->installment_months }} شهور</td></tr>
                                                            <tr><td class="ex-label" colspan="2">الإجمالي بعد النسبه</td><td class="ex-val-pink">{{ number_format($inst->total_after_interest, 2) }} ج</td></tr>
                                                            <tr><td class="ex-label" colspan="2">القسط الشهري</td><td class="ex-val-yellow text-danger">{{ number_format($inst->monthly_installment, 2) }} ج</td></tr>
                                                        @else
                                                            {{-- حالة البيع المباشر (الدين العادي) --}}
                                                            <tr><td class="ex-label" colspan="2">نوع العملية</td><td class="ex-val-green text-primary">بيع آجل (دين مباشر)</td></tr>
                                                            <tr><td class="ex-label" colspan="2">إجمالي المطلوب</td><td class="ex-val-pink">{{ number_format($inst->total_after_interest, 2) }} ج</td></tr>
                                                            <tr><td class="ex-label" colspan="2">المدفوع كاش</td><td class="ex-val-green">{{ number_format($inst->down_payment, 2) }} ج</td></tr>
                                                            <tr><td class="ex-label" colspan="2">المتبقي للدفع</td><td class="ex-val-yellow text-danger">{{ number_format($inst->remaining_balance, 2) }} ج</td></tr>
                                                        @endif

                                                        {{-- رأس تسلسل المدفوعات المدمج --}}
                                                        <tr class="ex-header" style="background: #e2e8f0;"><td colspan="3"><i class="fa fa-history me-1 text-success"></i> تسلسل مدفوعات هذه العملية</td></tr>
                                                        <tr class="ex-th-pft">
                                                            <td style="width: 20%;">رقم الدفعة</td>
                                                            <td style="width: 50%;">التاريخ</td>
                                                            <td style="width: 30%;">المبلغ</td>
                                                        </tr>
                                                        
                                                        {{-- بيانات الدفعات --}}
                                                        @forelse($inst->payments as $p)
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td class="text-muted small" style="direction: ltr;">{{ date('Y-m-d h:i A', strtotime($p->payment_date)) }}</td>
                                                                <td class="ex-val-green text-success">{{ number_format($p->amount_paid, 2) }} ج</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-muted py-3" style="font-weight: normal; background: #fafafa;">لم يتم سداد أي دفعات لهذه العملية حتى الآن.</td>
                                                            </tr>
                                                        @endforelse
                                                    </table> @if($client->customer_phone && $client->customer_phone !== '-')
                                                        <div class="mt-4">
                                                           <button onclick="sendFullStatement('{{ $client->customer_phone }}', '{{ $client->customer_name }}', '{{ $inst->product_name }}', '{{ date('Y-m-d', strtotime($inst->start_date)) }}', '{{ $inst->cash_price }}', '{{ $inst->down_payment }}', '{{ $inst->installment_months }}', '{{ $inst->monthly_installment }}', '{{ collect($inst->payments)->sum('amount_paid') }}', '{{ $inst->remaining_balance }}')" class="btn btn-success w-100 fw-bold rounded-pill p-2 mt-3">
                                                                <i class="fa-brands fa-whatsapp me-2"></i> إرسال كشف حساب مفصل
                                                            </button>
                                                        </div>
                                                    @endif
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function sendFullStatement(phone, name, prod, date, cash, down, months, monthly, paid, rem) {
    if(!phone || phone === '-') {
        Swal.fire('خطأ!', 'رقم هاتف العميل غير مسجل.', 'error');
        return;
    }
    let cPhone = phone.replace(/[^0-9]/g, '');
    if(cPhone.startsWith('0')) cPhone = '2' + cPhone;

    let msg = `*كشف حساب من شركة الضبع*\n\n`;
    msg += `👤 العميل: ${name}\n`;
    msg += `📦 العملية: ${prod}\n`;
    msg += `📅 التاريخ: ${date}\n`;
    msg += `💰 سعر الكاش: ${parseFloat(cash).toLocaleString('en-US')} ج\n`;
    msg += `💵 المقدم: ${parseFloat(down).toLocaleString('en-US')} ج\n`;
    if(months > 0) {
        msg += `🗓️ شهور التقسيط: ${months} شهور\n`;
        msg += `💳 القسط الشهري: ${parseFloat(monthly).toLocaleString('en-US')} ج\n`;
    } else {
        msg += `📌 نوع العملية: ديون آجلة (بدون تقسيط)\n`;
    }
    msg += `✅ إجمالي المسدد: ${parseFloat(paid).toLocaleString('en-US')} ج\n`;
    msg += `⚠️ المتبقي عليه: ${parseFloat(rem).toLocaleString('en-US')} ج\n\n`;
    msg += `نسعد دائماً بخدمتكم!`;

    window.open(`https://wa.me/${cPhone}?text=${encodeURIComponent(msg)}`, '_blank');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>