@if($inst->remaining_balance > 0)
{{-- Pay Modal --}}
<div class="modal fade" id="payModal_{{ $inst->id }}" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="fw-bold m-0"><i class="fa fa-cash-register me-2"></i> سداد قسط: {{ $inst->customer_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('installments.pay') }}" method="POST" novalidate onsubmit="return checkPayForm(event, this, '{{ $inst->id }}', {{ $inst->remaining_balance }})">
                @csrf <input type="hidden" name="inst_id" value="{{ $inst->id }}">
                {{-- مفتاح العميل محسوب بنفس طريقة التجميع بالظبط (مش $groupKey لأنه stale هنا) --}}
                <input type="hidden" name="group_key" value="grp_{{ md5(filled($inst->customer_phone) ? $inst->customer_phone : 'n:'.$inst->customer_name) }}">
                <div class="modal-body p-4 bg-light">
                    <div class="d-flex justify-content-between align-items-center bg-danger bg-opacity-10 border border-danger rounded p-3 mb-4 shadow-sm">
                        <span class="fw-bold text-danger fs-5">المتبقي المطلوب:</span>
                        <h3 class="m-0 fw-bold text-danger"><span id="disp_rem_{{ $inst->id }}">{{ fmtMoney($inst->remaining_balance) }}</span> ج</h3>
                    </div>
                    <div class="mb-4 bg-white p-3 rounded border border-warning shadow-sm">
                        <label class="fw-bold text-warning mb-2"><i class="fa fa-tag me-1"></i> خصم / تسوية (يُطرح تلقائياً من المتبقي)</label>
                        <input type="number" step="0.01" min="0" name="discount" id="disc_{{ $inst->id }}" class="form-control border-warning fw-bold text-center fs-4 text-warning" placeholder="0" value="0" autocomplete="on" oninput="updatePay('{{ $inst->id }}', {{ $inst->monthly_installment }}, {{ $inst->remaining_balance }})">
                    </div>
                    <label class="fw-bold text-dark mb-2">نظام السداد للمبلغ (بعد الخصم):</label>
                    <div class="pay-radio-group mb-4 shadow-sm">
                        <label><input type="radio" name="pay_type_{{ $inst->id }}" id="pt_m_{{ $inst->id }}" value="monthly" checked onchange="updatePay('{{ $inst->id }}', {{ $inst->monthly_installment }}, {{ $inst->remaining_balance }})"> قسط شهري ثابت</label>
                        <label><input type="radio" name="pay_type_{{ $inst->id }}" id="pt_f_{{ $inst->id }}" value="full" onchange="updatePay('{{ $inst->id }}', {{ $inst->monthly_installment }}, {{ $inst->remaining_balance }})"> سداد كامل المتبقي</label>
                        <label class="text-primary"><input type="radio" name="pay_type_{{ $inst->id }}" id="pt_p_{{ $inst->id }}" value="partial" onchange="updatePay('{{ $inst->id }}', {{ $inst->monthly_installment }}, {{ $inst->remaining_balance }})"> مبلغ مخصص</label>
                        <label class="text-danger"><input type="radio" name="pay_type_{{ $inst->id }}" id="pt_d_{{ $inst->id }}" value="defaulted" onchange="updatePay('{{ $inst->id }}', {{ $inst->monthly_installment }}, {{ $inst->remaining_balance }})"> <i class="fa fa-exclamation-triangle me-1"></i> تعثر (بدون دفع)</label>
                    </div>
                    <div id="defaulted_alert_{{ $inst->id }}" class="alert alert-danger fw-bold text-center mb-3 d-none" style="font-size:14px; border-radius:8px;">
                        <i class="fa fa-exclamation-circle me-2"></i> سيتم تسجيل هذا القسط بقيمة <strong>صفر</strong> ولن يؤثر على المتبقي
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold text-success mb-2" id="amtLabel_{{ $inst->id }}">المبلغ المطلوب سداده كاش الآن <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" id="amt_{{ $inst->id }}" data-pay-type="monthly" class="form-control border-success fw-bold text-center fs-3 text-success input-locked" value="{{ number_format(min($inst->monthly_installment, $inst->remaining_balance), 2, '.', '') }}" autocomplete="on" oninput="clampPayAmount('{{ $inst->id }}', {{ $inst->monthly_installment }}, {{ $inst->remaining_balance }})">
                    </div>
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="fw-bold text-dark mb-1">تاريخ العملية / التعثر</label>
                            <input type="date" name="payment_date" id="pay_date_{{ $inst->id }}" class="form-control fw-bold" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6" id="vault_section_{{ $inst->id }}">
                            <label class="fw-bold text-primary mb-1">إيداع في خزنة <span class="text-danger">*</span></label>
                            <select name="method_id" id="vault_sel_{{ $inst->id }}" class="form-select border-primary fw-bold" onchange="showVaultBalance('vault_sel_{{ $inst->id }}', 'vault_bal_{{ $inst->id }}')">
                                <option value="" disabled selected>اختر الخزنة...</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" data-balance="{{ $acc->balance }}">
                                        {{ $acc->account_name }} — {{ number_format($acc->balance, 2) }} ج
                                    </option>
                                @endforeach
                            </select>
                            <div id="vault_bal_{{ $inst->id }}" class="vault-balance-display"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white"><button type="submit" class="btn btn-success w-100 fw-bold fs-4 rounded-pill">تأكيد التحصيل</button></div>
            </form>
        </div>
    </div>
</div>
{{-- Other Modals for this inst (Edit, Writeoff, Delete) --}}
<div class="modal fade" id="editModal_{{ $inst->id }}" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered"><form action="{{ route('installments.update') }}" method="POST" class="modal-content border-0 shadow-lg" onsubmit="return disableBtn(event, this)">@csrf <input type="hidden" name="inst_id" value="{{ $inst->id }}"><div class="modal-header bg-primary text-white border-0"><h5 class="fw-bold m-0"><i class="fa fa-pen me-2"></i>تعديل العقد</h5><button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal"></button></div><div class="modal-body p-4 bg-light"><div class="mb-3"><label class="fw-bold mb-1">العميل</label><input type="text" name="customer_name" class="form-control fw-bold border-primary" value="{{ $inst->customer_name }}" required></div><div class="mb-3"><label class="fw-bold mb-1">الموبايل</label><input type="text" name="customer_phone" class="form-control fw-bold border-primary" value="{{ $inst->customer_phone }}"></div><div class="mb-2"><label class="fw-bold mb-1 text-danger">يوم السداد (1-31)</label><select name="due_day" class="form-select fw-bold border-danger text-center" required><option value="">— اختر يوم السداد —</option>@for($dy=1;$dy<=30;$dy++)<option value="{{ $dy }}" {{ $inst->due_day==$dy?'selected':'' }}>يوم {{ $dy }}</option>@endfor</select></div></div><div class="modal-footer border-0 bg-white"><button type="submit" class="btn btn-primary w-100 fw-bold fs-5 rounded-pill">حفظ التعديلات</button></div></form></div>
</div>

{{-- ═══════════ Modal فسخ العقد ═══════════ --}}
@if($inst->remaining_balance > 0)
@php
    $instTotalPaidByCust = (float) collect($inst->payments)->sum('amount_paid');
    $instDownPay         = (float) ($inst->down_payment ?? 0);
    // المقدم لو مش مسجل ضمن الـ payments نضيفه
    $instDownInPayments = collect($inst->payments)->contains(function ($p) use ($inst, $instDownPay) {
        return (float) $p->amount_paid == $instDownPay
            && abs(\Carbon\Carbon::parse($p->payment_date)->diffInSeconds($inst->created_at)) <= 5;
    });
    $instTotalRefundable = $instDownInPayments ? $instTotalPaidByCust : ($instTotalPaidByCust + $instDownPay);
    $instIsService = ($inst->category ?? '') === 'خدمات';
@endphp
<div class="modal fade" id="terminateModal_{{ $inst->id }}" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('installments.terminate') }}" method="POST" class="modal-content border-0 shadow-lg" onsubmit="return validateTerminate(event, this, {{ $inst->id }}, {{ $instTotalRefundable }})">
            @csrf
            <input type="hidden" name="inst_id" value="{{ $inst->id }}">

            <div class="modal-header border-0 py-3" style="background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;">
                <h5 class="fw-bold m-0"><i class="fa fa-file-circle-xmark me-2"></i>فسخ عقد — {{ Str::limit($inst->customer_name,30) }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4" style="background:#fef2f2;">

                {{-- شرح --}}
                <div class="alert mb-3 fw-bold small" style="background:#fef3c7;border:1.5px solid #fbbf24;color:#7c2d12;border-radius:10px;">
                    <i class="fa fa-triangle-exclamation me-1"></i>
                    الفسخ هيرد فلوس العميل من الخزنة، يرجّع البضاعة للمخزن (لو وافقت)، يحذف العقد + الأقساط + الديون + العمولات المرتبطة + أي ربح اتسجل. <b>غير قابل للتراجع.</b>
                </div>

                {{-- ملخص --}}
                <div class="p-3 rounded-3 mb-3" style="background:#fff;border:1.5px solid #fca5a5;">
                    <div class="row g-2 small">
                        <div class="col-md-6"><b>العميل:</b> {{ $inst->customer_name }}</div>
                        <div class="col-md-6"><b>المنتج:</b> {{ $inst->product_name }}</div>
                        <div class="col-md-6"><b>قيمة العقد:</b> {{ fmtMoney($inst->total_after_interest) }} ج</div>
                        <div class="col-md-6"><b>متبقي:</b> {{ fmtMoney($inst->remaining_balance) }} ج</div>
                    </div>
                    <hr class="my-2">
                    <div class="text-center fs-5 fw-bold">
                        إجمالي اللي العميل دفعه: <span class="text-success">{{ fmtMoney($instTotalRefundable) }} ج</span>
                    </div>
                </div>

                {{-- مبلغ الرد --}}
                <div class="mb-3">
                    <label class="fw-bold mb-1 text-dark">المبلغ المراد رده للعميل (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="{{ $instTotalRefundable }}" name="refund_amount" id="term_refund_{{ $inst->id }}" value="{{ $instTotalRefundable }}" class="form-control text-center fs-3 fw-bold border-danger" required oninput="updateDiffPreview({{ $inst->id }}, {{ $instTotalRefundable }})">
                    <div id="diff_preview_{{ $inst->id }}" class="small fw-bold mt-1 text-muted text-center"></div>
                    <div class="small text-muted mt-1"><i class="fa fa-circle-info me-1"></i>تقدر تنزل الرقم لو هتاخد خصم — الفرق هيتسجل كـ خصم فسخ للشركة في حركة منفصلة.</div>
                </div>

                {{-- خزنة الصرف --}}
                <div class="mb-3">
                    <label class="fw-bold mb-1 text-dark">خزنة الصرف (هيتسحب منها مبلغ الرد) <span class="text-danger">*</span></label>
                    <select name="refund_account_id" class="form-select fw-bold border-danger" required>
                        <option value="" disabled selected>اختر الخزنة...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" data-balance="{{ $acc->balance }}">{{ $acc->account_name }} — متاح: {{ fmtMoney($acc->balance) }} ج</option>
                        @endforeach
                    </select>
                </div>

                {{-- إرجاع البضاعة --}}
                @if(!$instIsService)
                    <div class="mb-3 p-3 rounded-3" style="background:#fff;border:1.5px solid #cbd5e1;">
                        <label class="fw-bold mb-2 d-block text-dark"><i class="fa fa-boxes-stacked me-1"></i>هل هترجع البضاعة للمخزن؟</label>
                        <div class="d-flex gap-2">
                            <label class="flex-fill text-center p-3 rounded border" style="cursor:pointer;background:#dcfce7;border-color:#16a34a !important;">
                                <input type="radio" name="return_to_stock" value="yes" checked class="form-check-input ms-1">
                                <b class="text-success">أيوه، ترجع للمخزن</b>
                                <div class="small text-muted">البضاعة سليمة وهترجع لتاب المرتجعات</div>
                            </label>
                            <label class="flex-fill text-center p-3 rounded border" style="cursor:pointer;">
                                <input type="radio" name="return_to_stock" value="no" class="form-check-input ms-1">
                                <b class="text-danger">لا، تتسجل خسارة</b>
                                <div class="small text-muted">البضاعة تالفة أو ضاعت — تكلفتها تتحسب خسارة</div>
                            </label>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="return_to_stock" value="no">
                @endif

                {{-- السبب --}}
                <div class="mb-2">
                    <label class="fw-bold mb-1 text-dark">سبب الفسخ <span class="text-danger">*</span></label>
                    <select class="form-select fw-bold mb-2" onchange="document.getElementById('term_reason_{{ $inst->id }}').value = this.value !== '_other_' ? this.value : '';">
                        <option value="" disabled selected>— اختر السبب أو اكتب —</option>
                        <option value="رغبة العميل في الفسخ">رغبة العميل في الفسخ</option>
                        <option value="عيب في المنتج">عيب في المنتج</option>
                        <option value="تعذر تكملة السداد">تعذر تكملة السداد</option>
                        <option value="تسوية ودية مع العميل">تسوية ودية مع العميل</option>
                        <option value="_other_">سبب آخر (اكتبه يدوياً)</option>
                    </select>
                    <textarea name="reason" id="term_reason_{{ $inst->id }}" class="form-control fw-bold" rows="2" maxlength="500" placeholder="اكتب أو اختر من القائمة فوق..." required></textarea>
                </div>

            </div>

            <div class="modal-footer border-0 bg-white p-3">
                <button type="button" class="btn btn-light fw-bold px-4 rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-danger fw-bold flex-grow-1 rounded-pill py-2 fs-6">
                    <i class="fa fa-check-circle me-2"></i>تأكيد فسخ العقد
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Writeoff --}}
<div class="modal fade" id="writeoffModal_{{ $inst->id }}" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('installments.writeoff') }}" method="POST" class="modal-content border-0 shadow-lg" onsubmit="return disableBtn(event, this)">
            @csrf
            <input type="hidden" name="inst_id" value="{{ $inst->id }}">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;">
                <h5 class="fw-bold m-0"><i class="fa fa-skull-crossbones me-2"></i>إعدام الدين — {{ Str::limit($inst->customer_name,25) }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="background:#faf5ff;">
                <div class="alert mb-3 fw-bold" style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;color:#7f1d1d;font-size:.9rem;">
                    <i class="fa fa-triangle-exclamation me-2 text-danger"></i>
                    سيتم <strong>إعدام المتبقي ({{ fmtMoney($inst->remaining_balance) }} ج)</strong> وتسجيله كـ <strong>خسارة في المصروفات</strong> بدون سداد من أي خزنة. هذا الإجراء لا يمكن التراجع عنه.
                </div>
                <div class="mb-3">
                    <label class="fw-bold mb-2 d-block" style="color:#7c3aed;font-size:.85rem;">سبب الإعدام <span class="text-danger">*</span></label>
                    <select name="writeoff_reason" class="form-select fw-bold border-2" style="border-color:#c4b5fd !important;" required>
                        <option value="">— اختر السبب —</option>
                        <option value="إعسار ثابت للعميل">إعسار ثابت للعميل</option>
                        <option value="وفاة العميل">وفاة العميل</option>
                        <option value="تعذر التحصيل نهائياً">تعذر التحصيل نهائياً</option>
                        <option value="تسوية ودية صفر">تسوية ودية (صفر)</option>
                        <option value="أخرى">أخرى</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="fw-bold mb-2 d-block" style="color:#7c3aed;font-size:.85rem;">ملاحظات إضافية</label>
                    <textarea name="writeoff_notes" class="form-control fw-bold" rows="2" style="border-color:#c4b5fd;border-radius:10px;" placeholder="اكتب أي تفاصيل إضافية..."></textarea>
                </div>
                <div class="p-3 rounded-3 text-center" style="background:#f5f3ff;border:1.5px dashed #c4b5fd;">
                    <span class="fw-black" style="color:#7c3aed;font-size:1.5rem;">{{ fmtMoney($inst->remaining_balance) }} ج</span>
                    <p class="mb-0 fw-bold" style="color:#6b21a8;font-size:.82rem;">المبلغ الذي سيُعدم ويُسجَّل كخسارة</p>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white p-3">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn fw-bold px-5 text-white" style="background:linear-gradient(135deg,#7c3aed,#a855f7);border-radius:10px;">
                    <i class="fa fa-skull-crossbones me-2"></i>تأكيد الإعدام
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endif
