<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بيع الخدمات - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --main-green: #00ab67; --bg-light: #f4f9f7; --text-dark: #1a2e24; }
        body { font-family: 'Cairo', sans-serif; background-color: var(--bg-light); color: var(--text-dark); overflow-x: hidden; }
        .main-content { margin-right: 260px; padding: 40px 30px; }
        .form-card { background: white; border-radius: 20px; border: 1px solid #e1eee7; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .section-title { font-size: 1.05rem; color: #12231b; border-right: 4px solid var(--main-green); padding-right: 10px; margin-bottom: 18px; font-weight: 700; }
        .calc-display-box { border-radius: 15px; padding: 20px; color: white; height: 100%; }
        .bg-cost-box    { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .bg-revenue-box { background: linear-gradient(135deg, #1e3a8a, #3b82f6); }
        .bg-profit-box  { background: linear-gradient(135deg, #059669, #10b981); }
        .calc-display-box h6 { opacity: .82; font-size: .83rem; font-weight: 700; margin-bottom: 8px; }
        .calc-display-box h2 { font-weight: 900; margin: 0; direction: ltr; text-align: right; }
        .form-control, .form-select { transition: 0.3s; }
        .suggestion-item:hover { background: #f0fdf4; color: var(--main-green); }
        .page-header { background: linear-gradient(135deg, #0d4427, #00ab67); border-radius: 18px; padding: 24px 28px; margin-bottom: 28px; color: white; display: flex; align-items: center; gap: 16px; }
        .page-header .icon { width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; }
        .page-header h2 { margin: 0; font-size: 1.45rem; font-weight: 900; }
        .page-header p { margin: 4px 0 0; font-size: 0.88rem; opacity: 0.85; }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">

    <div class="page-header">
        <div class="icon"><i class="fa fa-wrench"></i></div>
        <div>
            <h2>بيع الخدمات</h2>
            <p>تسجيل خدمات الصيانة والتركيب وكل الخدمات المقدمة للعملاء</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert bg-success bg-opacity-10 border border-success text-success fw-bold p-3 rounded-4 mb-4">
            <i class="fa fa-circle-check me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any() || session('error'))
        <div class="alert bg-danger bg-opacity-10 border border-danger text-danger fw-bold p-3 rounded-4 mb-4 shadow-sm">
            <h5 class="fw-black mb-2"><i class="fa fa-exclamation-triangle me-2"></i>توجد أخطاء في البيانات:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                @if(session('error')) <li>{{ session('error') }}</li> @endif
            </ul>
        </div>
    @endif

    <div class="card form-card p-4 mx-auto" style="max-width: 960px;">
        <form method="POST" action="{{ route('sales.store') }}" id="salesForm">
            @csrf

            <div class="section-title"><i class="fa fa-file-invoice me-1"></i> تفاصيل الخدمة</div>
            <div class="row mb-4 g-3 bg-light p-3 rounded-4 border">

                <div class="col-md-6">
                    <label class="fw-bold small text-primary">اسم الصنايعي / الفني (المنفذ للخدمة)</label>
                    <input type="text" name="technician_name" value="{{ old('technician_name') }}"
                           class="form-control border-primary fw-bold" placeholder="مثال: محمد الكهربائي">
                </div>

                <div class="col-md-6">
                    <label class="fw-bold small">نوع الخدمة (صيانة، تركيب...)</label>
                    <input type="text" name="product_name" value="{{ old('product_name') }}" class="form-control fw-bold" required placeholder="مثال: صيانة ثلاجة، تركيب تكييف...">
                </div>

                <div class="col-md-6">
                    <label class="fw-bold small text-danger">أجرة الصنايعي أو التكلفة (ج)</label>
                    <input type="number" step="1" name="purchase_price" value="{{ old('purchase_price') }}" id="purchase_price" class="form-control calc-trigger border-danger text-center fw-bold fs-5" required placeholder="0">
                </div>

                <div class="col-md-6">
                    <label class="fw-bold small text-success">قيمة الخدمة المطلوبة من العميل (ج)</label>
                    <input type="number" step="1" name="selling_price" value="{{ old('selling_price') }}" id="selling_price" class="form-control calc-trigger border-success text-center fw-bold fs-5" required placeholder="0">
                </div>

                <div class="col-md-12 mt-3 pt-3 border-top border-secondary-subtle">
                    <h6 class="fw-bold text-danger mb-3"><i class="fa fa-hand-holding-dollar me-1"></i> سداد التكلفة (للصنايعي أو الفني)</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="fw-bold small text-muted">طريقة سداد أجرة الصنايعي</label>
                            <select name="supplier_pay_type" id="supplier_pay_type" class="form-select border-danger fw-bold" onchange="toggleSupplierPay()">
                                <option value="cash" {{ old('supplier_pay_type') == 'cash' ? 'selected' : '' }}>سداد كامل (كاش)</option>
                                <option value="partial" {{ old('supplier_pay_type') == 'partial' ? 'selected' : '' }}>دفع جزء وآجل</option>
                                <option value="ajel" {{ old('supplier_pay_type') == 'ajel' ? 'selected' : '' }}>آجل بالكامل (تسجيل كدين علينا)</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="div_supplier_paid" style="display: none;">
                            <label class="fw-bold small text-danger">المبلغ المدفوع الآن للصنايعي (ج)</label>
                            <input type="number" step="1" name="supplier_paid_amount" id="supplier_paid_amount" value="{{ old('supplier_paid_amount') }}" class="form-control border-danger text-center fw-bold fs-5 text-danger" placeholder="0">
                        </div>
                        <div class="col-md-4" id="div_supplier_account">
                            <label class="fw-bold small text-danger">صرف أجرة الصنايعي من خزنة:</label>
                            <select class="form-select border-danger fw-bold" name="withdrawal_account" id="withdrawal_account">
                                <option value="" disabled selected>اختر الخزنة للسحب...</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('withdrawal_account') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->account_name }} ({{ number_format($acc->balance, 0) }} ج)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title"><i class="fa fa-hand-holding-dollar me-1"></i> التحصيل وبيانات العميل</div>
            <div class="mb-4 rounded-4 border border-success p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold small text-muted">رقم التليفون (11 رقم)</label>
                        <div class="position-relative">
                            <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}"
                                   class="form-control fw-bold" placeholder="01xxxxxxxxx"
                                   oninput="lookupCustomer(this.value)" autocomplete="off">
                            <div id="phone_suggestions" class="position-absolute bg-white border rounded-3 shadow w-100 mt-1" style="display:none; z-index:9999; max-height:200px; overflow-y:auto;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold small text-muted">
                            اسم العميل (اختياري للآجل)
                            <span id="found_badge" class="badge bg-success ms-1" style="display:none;"><i class="fa fa-lock me-1"></i>عميل مسجل - الاسم محفوظ</span>
                        </label>
                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" class="form-control fw-bold" placeholder="سيُكمَل تلقائياً...">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="fw-bold small text-warning">
                            <i class="fa fa-comment-dots me-1"></i> ملاحظات عن العميل
                            <small class="text-muted fw-normal">(تظهر في ملفه بالأرشيف)</small>
                        </label>
                        <textarea name="customer_notes" id="customer_notes" rows="2"
                                  class="form-control border-warning fw-bold"
                                  style="background:#fffbeb;"
                                  placeholder="مثال: عميل ممتاز، يفضل التواصل صباحاً...">{{ old('customer_notes') }}</textarea>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="fw-bold small text-danger"><i class="fa fa-tag me-1"></i>خصم للعميل (ج)</label>
                        <input type="number" step="1" min="0" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', 0) }}" class="form-control calc-trigger border-danger fs-4 text-center fw-bold text-danger" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small text-success">المدفوع نقداً للشركة الآن</label>
                        <input type="number" step="1" min="0" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', 0) }}" class="form-control calc-trigger border-success fs-4 text-center fw-bold text-success" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small text-danger">المتبقي (سيُسجل كدين آجل)</label>
                        <input type="text" id="remaining_display" class="form-control border-danger text-danger fw-bold fs-4 text-center bg-light" readonly value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small text-success">إيداع الكاش في خزنة:</label>
                        <select class="form-select border-success fw-bold" name="deposit_account" id="deposit_account">
                            <option value="">بدون إيداع (لم يتم الدفع)</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('deposit_account') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} ({{ number_format($acc->balance, 0) }} ج)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="calc-display-box bg-cost-box shadow-sm"><h6>إجمالي التكلفة</h6><h2 id="total_cost_display">0</h2></div></div>
                <div class="col-md-4"><div class="calc-display-box bg-revenue-box shadow-sm"><h6>الإيراد المطلوب</h6><h2 id="sales_revenue_display">0</h2></div></div>
                <div class="col-md-4"><div class="calc-display-box bg-profit-box shadow-sm"><h6>صافي الربح المُحقق</h6><h2 id="expected_profit_display">0</h2></div></div>
            </div>

            <div class="section-title mb-3"><i class="fa fa-percent me-1 text-warning"></i> عمولة المبيعات <small class="text-muted fw-normal">(اختياري)</small></div>
            <div class="mb-4 rounded-4 border border-warning p-4" style="background:#fffbeb;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="fw-bold small text-warning">نوع العمولة</label>
                        <div class="d-flex gap-2 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="commission_type" id="comm_type_fixed_s" value="fixed" {{ old('commission_type', 'fixed') == 'fixed' ? 'checked' : '' }} onchange="calcCommissionSales()">
                                <label class="form-check-label fw-bold" for="comm_type_fixed_s"><i class="fa fa-hashtag me-1"></i>رقم ثابت</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="commission_type" id="comm_type_pct_s" value="percentage" {{ old('commission_type') == 'percentage' ? 'checked' : '' }} onchange="calcCommissionSales()">
                                <label class="form-check-label fw-bold" for="comm_type_pct_s"><i class="fa fa-percent me-1"></i>نسبة %</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small text-warning" id="comm_lbl_s">قيمة العمولة (ج)</label>
                        <input type="number" step="1" min="0" name="commission_value" id="commission_value_s" value="{{ old('commission_value') }}" class="form-control border-warning fw-bold text-center fs-5 text-warning" placeholder="0" oninput="calcCommissionSales()">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small">خصم من الربح</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" id="deduct_from_profit_s" name="deduct_from_profit" value="1" {{ old('deduct_from_profit') ? 'checked' : '' }} onchange="calcCommissionSales()">
                            <label class="form-check-label fw-bold text-danger" for="deduct_from_profit_s">خصم من صافي الربح</label>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <label class="fw-bold small text-danger d-block">إجمالي العمولة</label>
                        <div class="fw-bold fs-4 text-warning border border-warning rounded-3 py-2 px-3" id="commission_total_s">0 ج</div>
                    </div>
                </div>
                <input type="hidden" name="commission_amount" id="commission_amount_s" value="0">
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                    <button type="submit" id="btn_submit" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm fw-bold p-3">
                        <i class="fa fa-check-circle me-2"></i> اعتماد الخدمة وتحديث الأرصدة
                    </button>
                </div>
                <div class="col-md-4">
                    <button type="button" onclick="printInvoice()" class="btn btn-outline-dark btn-lg w-100 rounded-pill shadow-sm fw-bold p-3">
                        <i class="fa fa-print me-2"></i> طباعة فاتورة للعميل
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSupplierPay() {
        const type = document.getElementById('supplier_pay_type').value;
        const paidDiv = document.getElementById('div_supplier_paid');
        const accDiv  = document.getElementById('div_supplier_account');
        const accSel  = document.getElementById('withdrawal_account');
        const paidIn  = document.getElementById('supplier_paid_amount');

        if (type === 'ajel') {
            paidDiv.style.display = 'none';
            accDiv.style.display  = 'none';
            accSel.removeAttribute('required');
            paidIn.removeAttribute('required');
            paidIn.value = '';
        } else if (type === 'partial') {
            paidDiv.style.display = 'block';
            accDiv.style.display  = 'block';
            accSel.setAttribute('required', 'required');
            paidIn.setAttribute('required', 'required');
        } else {
            paidDiv.style.display = 'none';
            accDiv.style.display  = 'block';
            accSel.setAttribute('required', 'required');
            paidIn.removeAttribute('required');
            paidIn.value = '';
        }
    }

    function calculateMetrics() {
        const purchase = parseFloat(document.getElementById('purchase_price').value) || 0;
        const selling  = parseFloat(document.getElementById('selling_price').value) || 0;
        const paid     = parseFloat(document.getElementById('paid_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;

        const afterDisc = Math.max(selling - discount, 0);
        const remaining = Math.max(afterDisc - paid, 0);

        document.getElementById('total_cost_display').innerText    = purchase.toLocaleString('en-US');
        document.getElementById('sales_revenue_display').innerText = selling.toLocaleString('en-US');
        document.getElementById('expected_profit_display').innerText = (afterDisc - purchase).toLocaleString('en-US');
        document.getElementById('remaining_display').value = remaining.toFixed(0);

        const depAcc = document.getElementById('deposit_account');
        if (paid > 0) depAcc.setAttribute('required', 'required');
        else depAcc.removeAttribute('required');

        calcCommissionSales();
    }

    function calcCommissionSales() {
        const purchase  = parseFloat(document.getElementById('purchase_price').value) || 0;
        const selling   = parseFloat(document.getElementById('selling_price').value) || 0;
        const discount  = parseFloat(document.getElementById('discount_amount').value) || 0;
        const afterDisc = Math.max(selling - discount, 0);
        const profit    = afterDisc - purchase;
        const isPct     = document.getElementById('comm_type_pct_s').checked;
        const commVal   = parseFloat(document.getElementById('commission_value_s').value) || 0;

        document.getElementById('comm_lbl_s').innerText = isPct ? 'نسبة العمولة (%)' : 'قيمة العمولة (ج)';

        let total = isPct ? (commVal / 100) * (document.getElementById('deduct_from_profit_s').checked ? profit : afterDisc) : commVal;
        total = Math.max(total, 0);

        document.getElementById('commission_total_s').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 0}) + ' ج';
        document.getElementById('commission_amount_s').value = total.toFixed(0);

        const deductOn  = document.getElementById('deduct_from_profit_s').checked;
        const netProfit = deductOn ? (profit - total) : profit;
        document.getElementById('expected_profit_display').innerText = netProfit.toLocaleString('en-US', {minimumFractionDigits: 0});
    }

    document.getElementById('salesForm').addEventListener('submit', function(e) {
        const paid     = parseFloat(document.getElementById('paid_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const selling  = parseFloat(document.getElementById('selling_price').value) || 0;
        const afterDisc = Math.max(selling - discount, 0);

        if (paid > afterDisc) {
            e.preventDefault();
            Swal.fire('خطأ!', 'المبلغ المدفوع (' + paid + ') لا يمكن أن يكون أكبر من الإجمالي المطلوب (' + afterDisc + ').', 'error');
            return false;
        }
        document.getElementById('btn_submit').disabled = true;
        document.getElementById('btn_submit').innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> جاري الاعتماد...';
        return true;
    });

    const dbCustomers = {!! json_encode(
        \Illuminate\Support\Facades\DB::table('installments')
            ->select('customer_name', 'customer_phone')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '-')
            ->orderByDesc('created_at')
            ->get()
            ->merge(
                \Illuminate\Support\Facades\DB::table('customers')->select('name as customer_name', 'phone as customer_phone')->whereNotNull('phone')->get()
            )
            ->unique('customer_phone')
            ->values()
    ) !!};

    const suggestionsBox = document.getElementById('phone_suggestions');
    const nameInput      = document.getElementById('customer_name');
    const phoneInput     = document.getElementById('customer_phone');
    const foundBadge     = document.getElementById('found_badge');

    function lockNameField(locked) {
        if (locked) {
            nameInput.setAttribute('readonly', 'readonly');
            nameInput.classList.add('border-success', 'bg-light');
            nameInput.style.cursor = 'not-allowed';
            foundBadge.style.display = 'inline-block';
        } else {
            nameInput.removeAttribute('readonly');
            nameInput.classList.remove('border-success', 'bg-light');
            nameInput.style.cursor = '';
            foundBadge.style.display = 'none';
        }
    }

    function lookupCustomer(val) {
        if (val.length < 3) { suggestionsBox.style.display = 'none'; lockNameField(false); return; }
        const matches = dbCustomers.filter(c => c.customer_phone && c.customer_phone.startsWith(val)).slice(0, 6);
        const exact = dbCustomers.find(c => c.customer_phone === val);
        if (exact) {
            nameInput.value = exact.customer_name;
            lockNameField(true);
            suggestionsBox.style.display = 'none';
            return;
        }
        lockNameField(false);
        if (matches.length === 0) { suggestionsBox.style.display = 'none'; return; }
        suggestionsBox.innerHTML = matches.map(c => `
            <div class="px-3 py-2 fw-bold border-bottom suggestion-item" style="cursor:pointer; font-size:.9rem;"
                 onmousedown="selectCustomer('${c.customer_phone}', '${c.customer_name.replace(/'/g, "\\'")}')">
                <i class="fa fa-user text-primary me-2"></i>${c.customer_name}
                <small class="text-muted ms-2" style="direction:ltr;">${c.customer_phone}</small>
            </div>
        `).join('');
        suggestionsBox.style.display = 'block';
    }

    function selectCustomer(phone, name) {
        phoneInput.value = phone;
        nameInput.value  = name;
        lockNameField(true);
        suggestionsBox.style.display = 'none';
    }

    document.addEventListener('click', e => {
        if (!suggestionsBox.contains(e.target) && e.target !== phoneInput) suggestionsBox.style.display = 'none';
    });

    document.querySelectorAll('.calc-trigger').forEach(el => el.addEventListener('input', calculateMetrics));
    window.onload = function () { toggleSupplierPay(); };

    function printInvoice() {
        const productName  = (document.getElementsByName('product_name')[0].value || '').trim();
        const customerName = (document.getElementById('customer_name').value || 'عميل نقدي').trim();
        const customerPhone = (document.getElementById('customer_phone').value || '').trim();
        const customerNotes = (document.getElementById('customer_notes').value || '').trim();

        if (!productName) {
            Swal.fire('تنبيه', 'من فضلك ادخل نوع الخدمة قبل الطباعة', 'warning');
            return;
        }

        const unitPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        const discount  = parseFloat(document.getElementById('discount_amount').value) || 0;
        const paid      = parseFloat(document.getElementById('paid_amount').value) || 0;
        const afterDisc = Math.max(unitPrice - discount, 0);
        const remaining = Math.max(afterDisc - paid, 0);

        const fmt = (n) => Math.round(n).toLocaleString('en-US');
        const today = new Date();
        const dateStr = today.toLocaleDateString('ar-EG-u-nu-latn', { year:'numeric', month:'long', day:'numeric' });
        const invoiceNo = 'SRV-' + today.getFullYear() + ('0'+(today.getMonth()+1)).slice(-2) + ('0'+today.getDate()).slice(-2) + '-' + Math.floor(Math.random()*9000+1000);

        const html = `<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>فاتورة خدمة ${invoiceNo}</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Cairo', sans-serif; color: #1a2e24; background: #f4f4f4; padding: 20px; }
  .invoice { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 2px 20px rgba(0,0,0,.08); }
  .header { display: flex; justify-content: space-between; align-items: start; border-bottom: 3px solid #00ab67; padding-bottom: 20px; margin-bottom: 25px; }
  .company h1 { font-size: 1.8rem; color: #00ab67; font-weight: 900; margin-bottom: 4px; }
  .company p { font-size: .85rem; color: #666; }
  .meta { text-align: left; }
  .meta .badge { display: inline-block; background: #00ab67; color: #fff; padding: 6px 18px; border-radius: 8px; font-weight: 800; font-size: 1rem; margin-bottom: 8px; }
  .meta .no, .meta .date { font-size: .85rem; color: #666; margin-top: 4px; direction: ltr; }
  .info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
  .info-box { background: #f9fafb; border-right: 4px solid #00ab67; padding: 12px 16px; border-radius: 6px; }
  .info-box .label { font-size: .75rem; color: #888; font-weight: 700; margin-bottom: 4px; }
  .info-box .val { font-size: 1rem; font-weight: 700; color: #1a2e24; }
  .info-box .val.phone { direction: ltr; text-align: right; font-family: monospace; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
  thead th { background: #1a2e24; color: #fff; padding: 12px 10px; font-weight: 700; font-size: .9rem; text-align: center; }
  thead th:first-child { text-align: right; padding-right: 16px; }
  tbody td { padding: 14px 10px; border-bottom: 1px solid #eee; text-align: center; font-weight: 600; }
  tbody td:first-child { text-align: right; padding-right: 16px; }
  .totals { display: flex; justify-content: flex-start; }
  .totals table { width: 50%; }
  .totals td { padding: 8px 12px; border: none; font-size: .95rem; }
  .totals td:first-child { color: #666; font-weight: 600; }
  .totals td:last-child { text-align: left; font-weight: 800; direction: ltr; }
  .totals .grand td { border-top: 2px solid #00ab67; padding-top: 12px; font-size: 1.15rem; color: #00ab67; font-weight: 900; }
  .totals .paid td { color: #059669; }
  .totals .remain td { color: #dc2626; }
  .notes { margin-top: 20px; background: #fffbeb; border: 1px dashed #f59e0b; padding: 12px 16px; border-radius: 8px; }
  .notes .label { font-size: .75rem; color: #b45309; font-weight: 700; margin-bottom: 4px; }
  .notes .val { font-size: .9rem; color: #92400e; font-weight: 600; line-height: 1.5; }
  .footer { margin-top: 35px; padding-top: 20px; border-top: 1px dashed #ccc; text-align: center; color: #666; font-size: .8rem; }
  .footer .thanks { font-size: 1rem; font-weight: 700; color: #00ab67; margin-bottom: 8px; }
  .sig-row { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px; }
  .sig { text-align: center; }
  .sig .line { border-top: 1px solid #333; margin: 40px 20px 6px; }
  .sig .label { font-size: .8rem; font-weight: 700; }
  @media print { body { padding: 0; background: #fff; } .invoice { box-shadow: none; padding: 25px; } .no-print { display: none; } }
  .actions { max-width: 800px; margin: 0 auto 15px; display: flex; gap: 10px; justify-content: center; }
  .actions button { padding: 10px 30px; border: none; border-radius: 8px; font-family: 'Cairo'; font-weight: 700; cursor: pointer; font-size: .95rem; }
  .actions .b1 { background: #00ab67; color: #fff; }
  .actions .b2 { background: #e5e7eb; color: #333; }
</style>
</head>
<body>
<div class="actions no-print">
  <button class="b1" onclick="window.print()">🖨️ طباعة</button>
  <button class="b2" onclick="window.close()">إغلاق</button>
</div>
<div class="invoice">
  <div class="header">
    <div class="company">
      <h1>شركة الضبع</h1>
      <p>للخدمات والصيانة</p>
    </div>
    <div class="meta">
      <div class="badge">فاتورة خدمة</div>
      <div class="no">رقم: ${invoiceNo}</div>
      <div class="date">التاريخ: ${dateStr}</div>
    </div>
  </div>
  <div class="info-row">
    <div class="info-box">
      <div class="label">العميل</div>
      <div class="val">${customerName || '—'}</div>
    </div>
    <div class="info-box">
      <div class="label">رقم التواصل</div>
      <div class="val phone">${customerPhone || '—'}</div>
    </div>
  </div>
  <table>
    <thead><tr><th>الخدمة / الوصف</th><th>السعر</th></tr></thead>
    <tbody><tr><td>${productName}</td><td>${fmt(unitPrice)} ج</td></tr></tbody>
  </table>
  <div class="totals">
    <table>
      <tr><td>قيمة الخدمة:</td><td>${fmt(unitPrice)} ج</td></tr>
      ${discount > 0 ? `<tr><td>الخصم:</td><td>− ${fmt(discount)} ج</td></tr>` : ''}
      <tr class="grand"><td>الإجمالي المستحق:</td><td>${fmt(afterDisc)} ج</td></tr>
      ${paid > 0 ? `<tr class="paid"><td>المدفوع:</td><td>${fmt(paid)} ج</td></tr>` : ''}
      ${remaining > 0 ? `<tr class="remain"><td>المتبقي:</td><td>${fmt(remaining)} ج</td></tr>` : ''}
    </table>
  </div>
  ${customerNotes ? `<div class="notes"><div class="label">ملاحظات:</div><div class="val">${customerNotes.replace(/</g, '&lt;')}</div></div>` : ''}
  <div class="sig-row">
    <div class="sig"><div class="line"></div><div class="label">توقيع العميل</div></div>
    <div class="sig"><div class="line"></div><div class="label">ختم الشركة</div></div>
  </div>
  <div class="footer">
    <div class="thanks">شكراً لتعاملكم معنا 🌿</div>
    <div>هذه الفاتورة وثيقة رسمية معتمدة من شركة الضبع</div>
  </div>
</div>
<scr${''}ipt>window.onload = () => setTimeout(() => window.print(), 350);</scr${''}ipt>
</body>
</html>`;

        const w = window.open('', '_blank', 'width=900,height=700');
        if (!w) { Swal.fire('تنبيه', 'الرجاء السماح بفتح النوافذ المنبثقة للطباعة', 'warning'); return; }
        w.document.write(html);
        w.document.close();
    }
</script>
</body>
</html>
