@php
    $cName        = $customerInsts->first()->customer_name;
    $cPhone       = $customerInsts->first()->customer_phone ?? '—';
    $activeConts  = $customerInsts->filter(fn($i) => $i->remaining_balance > 0);
    $doneConts    = $customerInsts->filter(fn($i) => $i->remaining_balance <= 0);
    $totalRemAll  = $customerInsts->sum('remaining_balance');
    $totalContAll = $customerInsts->sum('total_after_interest');
    $totalDownAll = $customerInsts->sum('down_payment');
    $totalMonthly = $activeConts->sum('monthly_installment');
    $totalPaidAll = $customerInsts->sum(fn($i) => collect($i->payments)->sum('amount_paid'));
    $totalProfAll = $customerInsts->sum('profit');
    $countAll     = $customerInsts->count();
    $groupKey     = 'grp_' . md5($phone ?? $cName);
@endphp

<div class="modal fade" id="customerModal_{{ $groupKey }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 700px;">
        <div class="modal-content border-0" style="border-radius:6px; overflow:hidden;">

            <div style="background:var(--text-main); color:#fff; padding:16px 22px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:38px; height:38px; border-radius:9px; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                        <i class="fa fa-file-lines" style="font-size:16px; color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:15px; font-weight:600; letter-spacing:-0.01em;">كشف حساب — {{ $cName }}</div>
                        <div style="font-size:11px; opacity:0.65; font-weight:400; margin-top:2px;" dir="ltr">{{ $cPhone }}</div>
                    </div>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button onclick="printCustomerStatement('{{ $groupKey }}')" class="btn btn-sm" style="background:var(--accent); color:#fff; font-size:12px; padding:6px 14px; border-radius:var(--r-sm); border:none; font-weight:500;"><i class="fa fa-print me-1"></i>طباعة</button>
                    <button onclick="downloadCustomerSheet('{{ $groupKey }}')" class="btn btn-sm" style="background:rgba(255,255,255,0.12); color:#fff; font-size:12px; padding:6px 14px; border-radius:var(--r-sm); border:1px solid rgba(255,255,255,0.18); font-weight:500;"><i class="fa fa-download me-1"></i>تحميل</button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div id="tabs_{{ $groupKey }}" class="cst-tabs-strip">
                @if($countAll > 1)
                <div class="cst-tab cst-tab-summary active-tab" data-group="{{ $groupKey }}" data-pane="summary" onclick="switchTab('{{ $groupKey }}','summary')">
                    <i class="fa fa-chart-pie" style="font-size:10px;"></i>
                    ملخص
                    <span class="cst-num">{{ $countAll }}</span>
                </div>
                @endif
                
                @if($activeConts->count() > 0)
                <div style="font-size: 11px; font-weight: bold; color: var(--danger); align-self: center; margin-left: 2px;">عقود نشطة:</div>
                @foreach($activeConts as $tIdx => $tInst)
                <div class="cst-tab {{ $countAll == 1 ? 'active-tab' : '' }}" data-group="{{ $groupKey }}" data-pane="contract_{{ $tInst->id }}" onclick="switchTab('{{ $groupKey }}','contract_{{ $tInst->id }}')">
                    <span style="width:7px;height:7px;border-radius:50%;background:var(--danger);display:inline-block;flex-shrink:0;"></span>
                    {{ Str::limit($tInst->product_name, 14) }}
                    <span class="cst-num">{{ $loop->iteration }}</span>
                </div>
                @endforeach
                @endif

                @if($doneConts->count() > 0)
                <div style="font-size: 11px; font-weight: bold; color: var(--success); align-self: center; margin-left: 2px; margin-right: 6px;">تم سدادها:</div>
                @foreach($doneConts as $tIdx => $tInst)
                <div class="cst-tab cst-tab-done {{ ($countAll == 1 && $activeConts->count() == 0) ? 'active-tab' : '' }}" data-group="{{ $groupKey }}" data-pane="contract_{{ $tInst->id }}" onclick="switchTab('{{ $groupKey }}','contract_{{ $tInst->id }}')">
                    <i class="fa fa-check" style="font-size:13px; font-weight:900;"></i>
                    {{ Str::limit($tInst->product_name, 14) }}
                    <span class="cst-num">{{ $loop->iteration }}</span>
                </div>
                @endforeach
                @endif
            </div>

            <div class="modal-body p-0" style="background:var(--surface);" id="captureCustomer_{{ $groupKey }}" data-active-pane="{{ $countAll == 1 ? 'contract_'.$customerInsts->first()->id : 'summary' }}" data-customer-name="{{ $cName }}">
                {{-- Header يظهر في الصورة فقط --}}
                <div style="background:var(--text-main);color:#fff;padding:12px 18px;text-align:center;display:none;" class="print-header-{{ $groupKey }}">
                    <div style="font-size:15px;font-weight:600;">شركة الضبع — كشف حساب تقسيط</div>
                    <div style="font-size:12px;opacity:0.7;margin-top:3px;">العميل: {{ $cName }} | تاريخ الطباعة: {{ date('Y-m-d') }}</div>
                </div>
                @if($countAll > 1)
                <div id="pane_{{ $groupKey }}_summary" class="cst-pane" style="display:block;">
                    <table class="paper-xls">
                        <tr class="pxls-title-row"><td class="pxls-label">اسم العميل</td><td class="pxls-value name-val">{{ $cName }}</td></tr>
                        <tr><td class="pxls-label">رقم الموبايل</td><td class="pxls-value" dir="ltr">{{ $cPhone }}</td></tr>
                        <tr><td class="pxls-label">إجمالي العقود</td><td class="pxls-value">{{ $countAll }} عقد ({{ $activeConts->count() }} نشط — {{ $doneConts->count() }} مكتمل)</td></tr>
                        <tr><td class="pxls-label">إجمالي قيمة العقود</td><td class="pxls-value">{{ fmtMoney($totalContAll) }}</td></tr>
                        <tr><td class="pxls-label">إجمالي المقدمات</td><td class="pxls-value">{{ fmtMoney($totalDownAll) }}</td></tr>
                        <tr><td class="pxls-label">إجمالي المسدد</td><td class="pxls-value paid-val">{{ fmtMoney($totalPaidAll) }}</td></tr>
                        <tr><td class="pxls-label">إجمالي الأقساط الشهرية</td><td class="pxls-value">{{ fmtMoney($totalMonthly) }}</td></tr>
                        <tr><td class="pxls-label profit-label">إجمالي أرباح العقود</td><td class="pxls-value profit-val">{{ fmtMoney($totalProfAll) }}</td></tr>
                        <tr><td class="pxls-label remaining-label">إجمالي المتبقي بالخارج</td><td class="pxls-value remaining-val">{{ fmtMoney($totalRemAll) }}</td></tr>
                    </table>
                </div>
                @endif

                @foreach($customerInsts as $inst)
                @php
                    $instPaidOnly  = collect($inst->payments)->sum('amount_paid');
                    // إجمالي المدفوع = الأقساط المحصّلة فقط (بدون المقدم)
                    $instTotalPaid = $instPaidOnly;
                    $instRemain    = $inst->remaining_balance;
                    $instProfit    = $inst->profit ?? 0;
                    $cashPrice     = isset($inst->cash_price) ? $inst->cash_price : round($inst->total_after_interest / (1 + ($inst->interest_rate ?? 0)/100));
                    $instExtras    = (float) ($inst->extras_total ?? 0);
                    $instDevice    = isset($inst->device_price) ? (float) $inst->device_price : max(0, $cashPrice - $instExtras);
                @endphp
                <div id="pane_{{ $groupKey }}_contract_{{ $inst->id }}" class="cst-pane" data-product="{{ $inst->product_name }}" style="display:{{ ($countAll == 1) ? 'block' : 'none' }};">
                    
                    @if($inst->remaining_balance > 0)
                    <div class="sheet-no-export" style="background:#fff8e1; border-bottom:1px solid #ffe082; padding:8px 12px; display:flex; gap:8px; flex-wrap:wrap; justify-content:center;">
                        <button class="btn btn-success btn-sm fw-bold px-3" data-bs-dismiss="modal" onclick="openActionModal('payModal', {{ $inst->id }})"><i class="fa fa-cash-register me-1"></i>سداد قسط</button>
                        <button class="btn btn-primary btn-sm fw-bold px-3" data-bs-dismiss="modal" onclick="openActionModal('editModal', {{ $inst->id }})"><i class="fa fa-pen me-1"></i>تعديل</button>
                        <button class="btn btn-warning btn-sm fw-bold px-3 text-dark" data-bs-dismiss="modal" onclick="openActionModal('writeoffModal', {{ $inst->id }})"><i class="fa fa-skull-crossbones me-1"></i>إعدام الدين</button>
                        <button class="btn btn-danger btn-sm fw-bold px-3" data-bs-dismiss="modal" onclick="openActionModal('terminateModal', {{ $inst->id }})"><i class="fa fa-file-circle-xmark me-1"></i>فسخ العقد</button>
                    </div>
                    @endif

                    <table class="paper-xls">
                        <tr class="pxls-title-row"><td class="pxls-label">اسم العميل</td><td class="pxls-value name-val">{{ $inst->customer_name }}</td></tr>
                        <tr><td class="pxls-label">نوع المنتج</td><td class="pxls-value">{{ $inst->product_name }}</td></tr>
                        <tr><td class="pxls-label">تاريخ التعاقد</td><td class="pxls-value" dir="ltr">{{ \Carbon\Carbon::parse($inst->start_date ?? $inst->created_at)->format('Y-m-d') }}</td></tr>
                        <tr><td class="pxls-label">سعر الجهاز كاش</td><td class="pxls-value">{{ fmtMoney($instDevice) }}</td></tr>
                        @if($instExtras > 0)
                        @if(($inst->transport_cost ?? 0) > 0)    <tr><td class="pxls-label" style="color:#b45309;">— نقل</td><td class="pxls-value" style="color:#b45309;">{{ fmtMoney($inst->transport_cost) }}</td></tr> @endif
                        @if(($inst->installation_cost ?? 0) > 0) <tr><td class="pxls-label" style="color:#b45309;">— تركيب</td><td class="pxls-value" style="color:#b45309;">{{ fmtMoney($inst->installation_cost) }}</td></tr> @endif
                        @if(($inst->materials_cost ?? 0) > 0)    <tr><td class="pxls-label" style="color:#b45309;">— خامات</td><td class="pxls-value" style="color:#b45309;">{{ fmtMoney($inst->materials_cost) }}</td></tr> @endif
                        <tr><td class="pxls-label" style="color:#d97706;font-weight:800;">إجمالي بنود التركيب</td><td class="pxls-value" style="color:#d97706;font-weight:800;">{{ fmtMoney($instExtras) }}</td></tr>
                        <tr><td class="pxls-label" style="font-weight:800;">الإجمالي (جهاز + تركيب)</td><td class="pxls-value" style="font-weight:800;">{{ fmtMoney($cashPrice) }}</td></tr>
                        @endif
                        <tr><td class="pxls-label">المقدم</td><td class="pxls-value">{{ fmtMoney($inst->down_payment) }}</td></tr>
                        <tr><td class="pxls-label">متبقي بعد دفع المقدم (قبل النسبة)</td><td class="pxls-value">{{ fmtMoney(max(0, $cashPrice - $inst->down_payment)) }}</td></tr>
                        <tr><td class="pxls-label">عدد الأشهر</td><td class="pxls-value">{{ $inst->installment_months }}</td></tr>
                        @if(($inst->interest_rate ?? 0) > 0) <tr><td class="pxls-label">نسبة مئوية</td><td class="pxls-value">{{ $inst->interest_rate }}%</td></tr> @endif
                        <tr><td class="pxls-label">إجمالي المتبقي بعد النسبة</td><td class="pxls-value">{{ fmtMoney($inst->total_after_interest - $inst->down_payment) }}</td></tr>
                        <tr><td class="pxls-label">القسط الشهري</td><td class="pxls-value">{{ fmtMoney($inst->monthly_installment) }}</td></tr>
                        <tr><td class="pxls-label">موعد سداد القسط</td><td class="pxls-value">{{ $inst->due_day }}</td></tr>
                        <tr><td class="pxls-label">رقم الموبايل</td><td class="pxls-value" dir="ltr">{{ $inst->customer_phone ?? '—' }}</td></tr>
                        @if(($inst->discount ?? 0) > 0) <tr><td class="pxls-label" style="color:#0277bd;">خصم ممنوح</td><td class="pxls-value" style="color:#0277bd;">{{ fmtMoney($inst->discount) }}</td></tr> @endif

@forelse(collect($inst->payments)->sortBy('payment_date')->values() as $pIdx => $p)                        <tr class="pxls-pay-row">
                            <td class="pxls-pay-date" dir="ltr">{{ date('Y-m-d', strtotime($p->payment_date)) }}</td>
                            <td class="pxls-pay-num" style="position:relative;">
                                <span class="pxls-pay-amount" style="{{ $p->amount_paid == 0 ? 'color:#c62828;' : '' }}">
                                    {{ fmtMoney($p->amount_paid) }}
                                </span>
                                
                                {{-- زر الإلغاء لأي دفعة مع الرقم السري --}}
                                <button type="button"
                                        onclick="confirmDeletePayment('{{ $p->id }}', '{{ $inst->customer_name }}', {{ $p->amount_paid }})"
                                        title="إلغاء الدفعة"
                                        style="background:none;border:none;padding:0 4px;cursor:pointer;vertical-align:middle;opacity:.7;"
                                        onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.7">
                                    <i class="fa fa-trash-alt" style="color:#c62828;font-size:13px;"></i>
                                </button>
                                <form id="del_payment_form_{{ $p->id }}" action="{{ url('/installments/delete-defaulted') }}" method="POST" style="display:none;">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $p->id }}">
                                </form>
                                
                                <span class="pxls-row-badge">{{ $pIdx + 1 }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr class="pxls-pay-row"><td colspan="2" style="text-align:center; color:#9e9e9e; padding:10px; font-size:13px;">لم يتم سداد أي دفعات حتى الآن</td></tr>
                        @endforelse

                        @for($emptyRow = count($inst->payments) + 1; $emptyRow <= $inst->installment_months; $emptyRow++)
                        <tr class="pxls-empty-row"><td class="pxls-pay-date" style="color:#bdbdbd;">—</td><td class="pxls-pay-num"><span class="pxls-row-badge" style="background:#e0e0e0; color:#9e9e9e;">{{ $emptyRow }}</span></td></tr>
                        @endfor

                        <tr class="pxls-summary-row paid-summary"><td class="pxls-sum-label">إجمالي المدفوع</td><td class="pxls-sum-value paid-val">{{ fmtMoney($instTotalPaid) }}</td></tr>
                        {{-- <tr class="pxls-summary-row profit-summary"><td class="pxls-sum-label profit-label">ربح العقد</td><td class="pxls-sum-value profit-val">{{ fmtMoney($instProfit) }}</td></tr> --}}
                        <tr class="pxls-summary-row remaining-summary"><td class="pxls-sum-label remaining-label">إجمالي المتبقي</td><td class="pxls-sum-value remaining-val">{{ fmtMoney($instRemain) }}</td></tr>
                    </table>
                </div>
                @endforeach
            </div>

            <div style="background:var(--surface-2); border-top:1px solid var(--border); padding:12px 18px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                <button onclick="printCustomerStatement('{{ $groupKey }}')" class="btn" style="background:var(--accent); color:#fff; font-size:13px; padding:9px 22px; border-radius:var(--r-sm); border:none; font-weight:500;"><i class="fa fa-print me-2"></i> طباعة الكشف</button>
                <button onclick="downloadCustomerSheet('{{ $groupKey }}')" class="btn" style="background:var(--surface); color:var(--text-muted); font-size:13px; padding:9px 22px; border-radius:var(--r-sm); border:1px solid var(--border); font-weight:500;"><i class="fa fa-download me-2"></i> تحميل كصورة</button>
                @if($cPhone && $cPhone !== '—')
                <button onclick="sendCustomerSheetWhatsApp('{{ $groupKey }}', '{{ $cPhone }}')" class="btn" style="background:#25d366; color:#fff; font-size:13px; padding:9px 22px; border-radius:var(--r-sm); border:none; font-weight:600;"><i class="fab fa-whatsapp me-2"></i> إرسال العقد الحالي</button>
                @if($countAll > 1)
                <button onclick="sendAllContractsWhatsApp('{{ $groupKey }}', '{{ $cPhone }}')" class="btn" style="background:#128c7e; color:#fff; font-size:13px; padding:9px 22px; border-radius:var(--r-sm); border:none; font-weight:600;"><i class="fab fa-whatsapp me-2"></i> إرسال كل العقود ({{ $countAll }})</button>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
