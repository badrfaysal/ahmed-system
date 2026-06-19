<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سجل العمليات - شركة الضبع</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('partials.theme', ['accent' => 'rose'])

    <style>
        .op-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-right: 4px solid var(--c-text-soft);
            border-radius: var(--r-md);
            padding: 14px 18px;
            margin-bottom: 10px;
            transition: var(--t-fast);
        }
        .op-card:hover { box-shadow: var(--shadow-sm); transform: translateY(-1px); }
        .op-card.t-fuel    { border-right-color: var(--c-warning); }
        .op-card.t-invpur  { border-right-color: var(--c-primary); }
        .op-card.t-invmov  { border-right-color: var(--c-danger); }
        .op-card.t-sale    { border-right-color: var(--c-success); }
        .op-card.t-service { border-right-color: var(--c-info); }
        .op-card.op-locked { opacity: 0.72; background: linear-gradient(135deg, var(--c-surface) 0%, var(--c-navy-50, #f1f5f9) 100%); }
        .op-card.op-locked .op-title { color: var(--c-text-soft); }

        .op-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
        .op-title { font-weight: 700; color: var(--c-text); font-size: 0.98rem; margin-bottom: 4px; }
        .op-desc  { color: var(--c-text-soft); font-size: 0.85rem; line-height: 1.55; }
        .op-meta  { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .op-time  { color: var(--c-text-soft); font-size: 0.75rem; direction: ltr; }

        .op-icon-box {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .op-icon-fuel    { background: var(--c-warning-bg); color: var(--c-warning); }
        .op-icon-invpur  { background: var(--c-primary-bg); color: var(--c-primary); }
        .op-icon-invmov  { background: var(--c-danger-bg);  color: var(--c-danger); }
        .op-icon-sale    { background: var(--c-success-bg); color: var(--c-success); }
        .op-icon-service { background: var(--c-info-bg);    color: var(--c-info); }

        .op-amount {
            font-weight: 700; font-size: 1.05rem; color: var(--c-text);
            text-align: end;
        }
        .op-profit { color: var(--c-success); font-size: 0.8rem; font-weight: 600; }

        .btn-edit-op {
            background: var(--c-warning-bg); color: var(--c-warning);
            border: 1px solid var(--c-warning); border-radius: 8px;
            padding: 5px 12px; font-size: 0.82rem; font-weight: 600;
            transition: var(--t-fast); cursor: pointer;
        }
        .btn-edit-op:hover { background: var(--c-warning); color: #fff; }
        .btn-cancel-op {
            background: var(--c-danger-bg) !important; color: var(--c-danger) !important;
            border-color: var(--c-danger) !important;
        }
        .btn-cancel-op:hover { background: var(--c-danger) !important; color: #fff !important; }
        .btn-return-op {
            background: var(--c-info-bg) !important; color: var(--c-info) !important;
            border-color: var(--c-info) !important;
        }
        .btn-return-op:hover { background: var(--c-info) !important; color: #fff !important; }

        .pay-card {
            border: 1px solid var(--c-border); border-radius: 8px;
            padding: 8px 12px; cursor: pointer; flex: 1;
            text-align: center; transition: var(--t-fast);
        }
        .pay-card input { display: none; }
        .pay-card.active { background: var(--c-primary-bg); border-color: var(--c-primary); color: var(--c-primary); font-weight: 600; }

        .settle-card {
            border: 2px solid var(--c-border); border-radius: 10px;
            padding: 12px 14px; cursor: pointer; flex: 1;
            text-align: center; transition: var(--t-fast);
            font-weight: 600; color: var(--c-text-soft);
            user-select: none;
        }
        .settle-card input { display: none; }
        .settle-card:hover { border-color: var(--c-primary); }
        .settle-card.active {
            background: var(--c-primary-bg); border-color: var(--c-primary);
            color: var(--c-primary); box-shadow: 0 0 0 3px var(--c-primary-bg);
        }
    </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    <div class="page-header">
        <div>
            <h2><i class="fa-solid fa-pen-to-square"></i> سجل العمليات</h2>
            <div class="subtitle">عدّل أي إدخال غلط: لتر بنزينة، عهدة، توريد مخزن، إهلاك، أو بيع — وكل القيود المرتبطة تتسوّى تلقائياً</div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-md col-sm-6">
            <div class="stat-card-pro">
                <div class="label">إجمالي العمليات (الفلتر)</div>
                <div class="value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md col-sm-6">
            <div class="stat-card-pro warning">
                <div class="label">بنزينة</div>
                <div class="value">{{ $stats['fuel'] }}</div>
            </div>
        </div>
        <div class="col-md col-sm-6">
            <div class="stat-card-pro" style="border-color: var(--c-primary)">
                <div class="label">عمليات المخزن</div>
                <div class="value">{{ $stats['inv'] }}</div>
            </div>
        </div>
        <div class="col-md col-sm-6">
            <div class="stat-card-pro success">
                <div class="label">مبيعات</div>
                <div class="value">{{ $stats['sales'] }}</div>
            </div>
        </div>
        <div class="col-md col-sm-6">
            <div class="stat-card-pro" style="border-color: #831843">
                <div class="label">حركات مالية</div>
                <div class="value">{{ $stats['financial'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-3 mb-3" style="background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--r-md);">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">نوع العملية</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" {{ $type=='all'?'selected':'' }}>كل العمليات</option>
                    <option value="fuel" {{ $type=='fuel'?'selected':'' }}>بنزينة</option>
                    <option value="inventory_purchase" {{ $type=='inventory_purchase'?'selected':'' }}>توريد مخزن</option>
                    <option value="inventory_movement" {{ $type=='inventory_movement'?'selected':'' }}>حركات المخزن (إهلاك/مرتجع)</option>
                    <option value="sale_cash" {{ $type=='sale_cash'?'selected':'' }}>مبيعات (مباشر/مخزن/خدمات)</option>
                    <option value="expense" {{ $type=='expense'?'selected':'' }}>مصروفات</option>
                    <option value="financial" {{ $type=='financial'?'selected':'' }}>حركات مالية (إيداع/تحويل/تحصيل)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">الفترة</label>
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="today"     {{ $period=='today'?'selected':'' }}>اليوم</option>
                    <option value="yesterday" {{ $period=='yesterday'?'selected':'' }}>أمس</option>
                    <option value="week"      {{ $period=='week'?'selected':'' }}>آخر أسبوع</option>
                    <option value="month"     {{ $period=='month'?'selected':'' }}>آخر شهر</option>
                    <option value="custom"    {{ $period=='custom'?'selected':'' }}>تحديد</option>
                    <option value="all"       {{ $period=='all'?'selected':'' }}>كل الفترات</option>
                </select>
            </div>
            @if($period == 'custom')
                <div class="col-md-2">
                    <label class="form-label small fw-bold">من</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">إلى</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm">
                </div>
            @endif
            <div class="col-md-3">
                <label class="form-label small fw-bold">بحث</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="اسم محطة، مورد، عميل، منتج..." class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100"><i class="fa fa-filter me-1"></i>فلتر</button>
            </div>
        </div>
    </form>

    {{-- Operations List --}}
    <div>
        @forelse($operations as $op)
            @php
                $tclass = match($op['type']) {
                    'fuel' => 't-fuel',
                    'inventory_purchase' => 't-invpur',
                    'inventory_movement' => 't-invmov',
                    'sale_cash' => $op['editor'] === 'service' ? 't-service' : 't-sale',
                    default => '',
                };
                $iclass = match($op['type']) {
                    'fuel' => 'op-icon-fuel',
                    'inventory_purchase' => 'op-icon-invpur',
                    'inventory_movement' => 'op-icon-invmov',
                    'sale_cash' => $op['editor'] === 'service' ? 'op-icon-service' : 'op-icon-sale',
                    default => '',
                };
            @endphp
            @php
                $isSuperseded = !empty($op['superseded_by']);
                $isEditEntry  = !empty($op['is_edit']);
                $isCancelled  = !empty($op['is_cancelled']);
                $isLocked     = $isSuperseded || $isCancelled || (!$op['editable']);
            @endphp
            <div class="op-card {{ $tclass }} @if($isLocked) op-locked @endif">
                <div class="op-top">
                    <div class="d-flex gap-3 flex-grow-1" style="min-width: 0;">
                        <div class="op-icon-box {{ $iclass }}">
                            <i class="fa-solid {{ $op['icon'] }}"></i>
                        </div>
                        <div style="min-width: 0; flex-grow: 1;">
                            <div class="op-meta mb-1">
                                <span class="badge bg-{{ $op['color'] }}-subtle text-{{ $op['color'] }}-emphasis fw-bold">{{ $op['type_label'] }}</span>
                                <span class="op-time">{{ \Carbon\Carbon::parse($op['date'])->format('Y-m-d h:i A') }}</span>
                                <span class="op-time">#{{ $op['id'] }}</span>

                                @if($isEditEntry)
                                    <span class="badge bg-info-subtle text-info-emphasis fw-bold" title="هذه نسخة معدّلة">
                                        <i class="fa fa-pen-to-square me-1"></i>تعديل لعملية #{{ $op['supersedes'] }}
                                    </span>
                                @endif
                                @if($isSuperseded)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis fw-bold" title="تم تعديلها وأنشأت عملية جديدة">
                                        <i class="fa fa-clock-rotate-left me-1"></i>
                                        تم التعديل
                                        @if($op['superseded_by'])
                                            (راجع #{{ $op['superseded_by'] }})
                                        @endif
                                        @if($op['superseded_at'])
                                            — {{ \Carbon\Carbon::parse($op['superseded_at'])->format('Y-m-d h:i A') }}
                                        @endif
                                    </span>
                                @endif
                                @if($isCancelled)
                                    <span class="badge bg-danger-subtle text-danger-emphasis fw-bold">
                                        <i class="fa fa-ban me-1"></i>
                                        ملغاة
                                        @if($op['superseded_at'])
                                            — {{ \Carbon\Carbon::parse($op['superseded_at'])->format('Y-m-d h:i A') }}
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="op-title">{{ $op['title'] }}</div>
                            <div class="op-desc">{{ $op['description'] }}</div>
                        </div>
                    </div>
                    <div class="text-end d-flex flex-column align-items-end gap-2" style="flex-shrink: 0;">
                        @if($op['amount'])
                            <div class="op-amount">{{ number_format((float)$op['amount'], 0) }} ج.م</div>
                        @endif
                        @if($op['profit'] !== null)
                            <div class="op-profit">ربح: {{ number_format((float)$op['profit'], 0) }}</div>
                        @endif
                        @if($op['editable'])
                            @php
                                $isServiceOp   = $op['editor'] === 'service';
                                $isFinancialOp = $op['editor'] === 'financial';
                                $isCancelOp    = in_array($op['editor'], ['sale_direct', 'sale_inventory']);
                                $isDeletable   = in_array($op['editor'], ['fuel', 'expense', 'financial']);
                            @endphp
                            <div class="d-flex gap-2 flex-wrap">
                                @if($isServiceOp)
                                    {{-- الخدمات: حذف فقط (بدون مرتجع أو تعديل) --}}
                                    <button type="button"
                                            class="btn-edit-op btn-cancel-op"
                                            onclick="openEdit('service', {{ $op['id'] }})">
                                        <i class="fa fa-trash me-1"></i>حذف الخدمة
                                    </button>
                                @elseif($isFinancialOp)
                                    {{-- الحركات المالية: إلغاء فقط (بدون تعديل) — الزر يظهر بعد --}}
                                @else
                                @if($isCancelOp)
                                    <button type="button"
                                            class="btn-edit-op btn-return-op"
                                            onclick="openEdit('sale_return', {{ $op['id'] }})">
                                        <i class="fa fa-undo me-1"></i>مرتجع
                                    </button>
                                @endif
                                <button type="button"
                                        class="btn-edit-op {{ $isCancelOp ? 'btn-cancel-op' : '' }}"
                                        onclick="openEdit('{{ $op['editor'] }}', {{ $op['id'] }})">
                                    @if($isCancelOp)
                                        <i class="fa fa-trash me-1"></i>حذف العملية
                                    @else
                                        <i class="fa fa-pen me-1"></i>تعديل
                                    @endif
                                </button>
                                @endif
                                @if($isDeletable)
                                    <button type="button"
                                            class="btn-edit-op btn-cancel-op"
                                            onclick="openDelete('{{ $op['editor'] }}', {{ $op['id'] }})">
                                        <i class="fa fa-trash me-1"></i>حذف
                                    </button>
                                @endif
                            </div>
                        @else
                            <button type="button" class="btn btn-sm btn-light text-muted" disabled style="cursor: not-allowed;">
                                <i class="fa fa-lock me-1"></i>غير قابل للتعديل
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5" style="color: var(--c-text-soft);">
                <i class="fa fa-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">لا توجد عمليات في الفترة المحددة.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal تعديل توريد المخزن --}}
<div class="modal fade" id="editInvPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="editInvPurchaseForm" method="POST" class="modal-content" style="background: var(--c-surface);">
            @csrf
            <input type="hidden" name="admin_pin" id="ip_pin">

            <div class="modal-header" style="background: var(--c-primary-bg); border-bottom: 1px solid var(--c-primary);">
                <h5 class="modal-title fw-bold text-primary-emphasis">
                    <i class="fa fa-truck-ramp-box me-2"></i> تعديل توريد مخزن
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fa fa-circle-info me-1"></i>
                    لو غيّرت الكمية أو سعر الشراء، فرق التكلفة هيتم تسويته إما <b>على دين المورد</b> أو <b>من/إلى خزنة</b>.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">اسم الصنف</label>
                        <input type="text" name="product_name" id="ip_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">الفئة</label>
                        <input type="text" name="category" id="ip_cat" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">المورد</label>
                        <input type="text" name="supplier_name" id="ip_sup" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">الكمية <span id="ip_sold_info" class="text-muted small"></span></label>
                        <input type="number" step="1" name="quantity" id="ip_qty" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">سعر الشراء (ج)</label>
                        <input type="number" step="1" name="purchase_price" id="ip_pp" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">سعر البيع (ج)</label>
                        <input type="number" step="1" name="selling_price" id="ip_sp" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">طريقة تسوية فرق التكلفة (لو في فرق)</label>
                        <div class="d-flex gap-2">
                            <label class="settle-card" id="settle_card_supplier" onclick="onSettleClick('supplier_debt')">
                                <input type="radio" name="settle_method" value="supplier_debt" checked>
                                <i class="fa fa-user-tag me-1"></i> على دين المورد
                            </label>
                            <label class="settle-card" id="settle_card_account" onclick="onSettleClick('account')">
                                <input type="radio" name="settle_method" value="account">
                                <i class="fa fa-wallet me-1"></i> من/إلى خزنة
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12" id="ip_acc_div" style="display:none;">
                        <label class="form-label small fw-bold">الخزنة</label>
                        <select name="settle_account" id="ip_settle_acc" class="form-select"></select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="fa fa-floppy-disk me-1"></i> حفظ التعديل
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal مرتجع بيعة --}}
<div class="modal fade" id="returnSaleModal" tabindex="-1" data-bs-focus="false" aria-labelledby="returnSaleModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <form id="returnSaleForm" method="POST" class="modal-content" style="background: var(--c-surface);">
            @csrf
            <input type="hidden" name="admin_pin" id="rs_pin">

            <div class="modal-header" style="background: var(--c-info-bg); border-bottom: 1px solid var(--c-info);">
                <h5 class="modal-title fw-bold text-info-emphasis">
                    <i class="fa fa-undo me-2"></i> مرتجع بيعة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-info small mb-3">
                    <i class="fa fa-circle-info me-1"></i>
                    المرتجع هيرجع البضاعة للمخزن (تاب <b>مرتجعات العملاء</b>)، يرد <b>كل الفلوس اللي العميل دفعها</b> من الخزنة، ويحذف كل الديون والمستحقات المرتبطة بالبيعة دي.
                </div>

                <div class="mb-3 p-3 rounded" style="background: var(--c-info-bg); border: 1px solid var(--c-info);">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">العميل:</span>
                        <span id="rs_customer"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">المنتج:</span>
                        <span id="rs_product"></span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5">
                        <span class="fw-bold">إجمالي اللي العميل دفعه:</span>
                        <span class="fw-black text-info-emphasis"><span id="rs_total_paid">0</span> ج</span>
                    </div>
                </div>

                <div class="mb-3" id="rs_account_div">
                    <label class="form-label small fw-bold">خزنة الصرف (هيتسحب منها مبلغ الرد)</label>
                    <select name="refund_account_id" id="rs_account" class="form-select" required
                            onchange="validateReturnAccount(parseFloat(this.dataset.totalPaid || 0))">
                        <option value="" disabled selected>اختر الخزنة...</option>
                    </select>
                </div>

                <div class="alert alert-warning small mb-0" id="rs_old_warning" style="display:none;">
                    <i class="fa fa-triangle-exclamation me-1"></i>
                    دي بيعة قديمة (مفيش ربط بالأصناف الأصلية في المخزن) — هيتعمل <b>دفعة جديدة</b> في المخزن باسم المنتج بكامل الكمية.
                </div>

                <div class="alert alert-danger small mt-3 mb-0" id="rs_error" style="display:none;">
                    <i class="fa fa-triangle-exclamation me-1"></i>
                    <span id="rs_error_msg"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-info text-white fw-bold">
                    <i class="fa fa-undo me-1"></i> تأكيد المرتجع
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal تعديل البنزينة --}}
<div class="modal fade" id="editFuelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="editFuelForm" class="modal-content" style="background: var(--c-surface);">
            @csrf
            <input type="hidden" name="admin_pin" id="ef_pin">
            <input type="hidden" name="_fuel_id"  id="ef_id">

            <div class="modal-header" style="background: var(--c-warning-bg); border-bottom: 1px solid var(--c-warning);">
                <h5 class="modal-title fw-bold text-warning-emphasis">
                    <i class="fa fa-gas-pump me-2"></i> تعديل عملية بنزينة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning small mb-3">
                    <i class="fa fa-triangle-exclamation me-1"></i>
                    التعديل هيلغي القيود المرتبطة بالعملية (دين المحطة، الاستقطاعات، عهدة الخزنة، دين شركة النقل) ويعيد إنشاءها بالقيم الجديدة.
                    <br>
                    <b>لو في سداد جزئي أو كلي للمستحقات → التعديل هيتمنع، الغِ السداد أولاً.</b>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">شركة النقل</label>
                        <select name="company_id" id="ef_company" class="form-select" required></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">المحطة</label>
                        <select name="station_id" id="ef_station" class="form-select" required></select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">السائق / السيارة</label>
                        <input type="text" name="driver_car" id="ef_driver" class="form-control" placeholder="مثال: علي - 1234">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">نوع الوقود</label>
                        <select name="item_id" id="ef_item" class="form-select" required>
                            <option value="0">بدون وقود (عهدة فقط)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">عدد اللترات</label>
                        <input type="number" step="0.01" name="quantity" id="ef_qty" class="form-control" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">العهدة النقدية (ج)</label>
                        <input type="number" step="1" name="cash_advance" id="ef_adv" class="form-control" value="0">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">مصدر العهدة</label>
                        <div class="d-flex gap-2">
                            <label class="pay-card">
                                <input type="radio" name="advance_source" value="none" onchange="onAdvanceSource()">
                                بدون عهدة
                            </label>
                            <label class="pay-card">
                                <input type="radio" name="advance_source" value="safe" onchange="onAdvanceSource()">
                                خزنة الشركة
                            </label>
                            <label class="pay-card">
                                <input type="radio" name="advance_source" value="station" onchange="onAdvanceSource()">
                                المحطة (تضاف على الديْن)
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6" id="ef_acc_div" style="display:none;">
                        <label class="form-label small fw-bold">الخزنة (لو من خزنة الشركة)</label>
                        <select name="account_id" id="ef_acc" class="form-select"></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">خصم يدوي (مصاريف) — اختياري</label>
                        <input type="number" step="1" name="manual_discount" class="form-control" value="0">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-warning fw-bold">
                    <i class="fa fa-floppy-disk me-1"></i> حفظ التعديل وتسوية القيود
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function openEdit(editor, id) {
    const isSaleDelete = ['sale_direct', 'sale_inventory', 'service'].includes(editor);
    const isSaleReturn = editor === 'sale_return';
    let title = 'تعديل عملية محمي';
    let btnColor = '#f59e0b';
    if (editor === 'service') { title = 'حذف خدمة — محمي'; btnColor = '#dc2626'; }
    else if (isSaleDelete)   { title = 'حذف بيعة محمي';    btnColor = '#dc2626'; }
    else if (isSaleReturn)   { title = 'مرتجع بيعة محمي';  btnColor = '#0ea5e9'; }

    const { value: pin } = await Swal.fire({
        title: title,
        text: 'أدخل كلمة مرور حسابك (أدمن) للتأكيد:',
        input: 'password',
        showCancelButton: true,
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: btnColor,
        inputAttributes: { autocomplete: 'current-password' },
    });
    if (!pin) return;

    const supported = ['fuel', 'inventory_purchase', 'sale_inventory', 'sale_direct', 'service', 'sale_return', 'expense'];
    if (!supported.includes(editor)) {
        Swal.fire({ icon: 'info', title: 'لسه مش متاح', html: 'العملية <b>' + editor + '</b> هتتفعل قريباً.' });
        return;
    }

    try {
        const res = await fetch(`/operations-log/${editor}/${id}/show`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ admin_pin: pin })
        });
        const data = await res.json();
        if (!res.ok) {
            Swal.fire('خطأ', data.error || 'فشل جلب البيانات', 'error');
            return;
        }

        // ننتظر تنظيف الـ Swal بالكامل قبل ما نفتح bootstrap modal
        // (عشان نتجنب تعارض الـ focus بينهم)
        await new Promise(resolve => setTimeout(resolve, 80));

        const openModal = (elId) => {
            const el = document.getElementById(elId);
            return bootstrap.Modal.getOrCreateInstance(el).show();
        };

        if (editor === 'fuel') {
            fillFuelModal(data, id, pin);
            openModal('editFuelModal');
        } else if (editor === 'inventory_purchase') {
            fillInvPurchaseModal(data, id, pin);
            openModal('editInvPurchaseModal');
        } else if (editor === 'expense') {
            fillExpenseModal(data, id, pin);
            openModal('editExpenseModal');
        } else if (editor === 'sale_return') {
            if (fillReturnModal(data, id, pin)) {
                openModal('returnSaleModal');
            }
        } else {
            confirmCancelSale(data, id, pin, editor);
        }
    } catch (e) {
        Swal.fire('خطأ', 'فشل الاتصال بالسيرفر', 'error');
    }
}

// ── توريد المخزن ───────────────────────────────────────────────
function fillInvPurchaseModal(d, id, pin) {
    const r = d.row;
    document.getElementById('ip_pin').value = pin;
    document.getElementById('editInvPurchaseForm').action = `/operations-log/inventory_purchase/${id}`;

    document.getElementById('ip_name').value     = r.product_name;
    document.getElementById('ip_cat').value      = r.category;
    document.getElementById('ip_sup').value      = r.supplier_name;
    document.getElementById('ip_pp').value       = r.purchase_price;
    document.getElementById('ip_sp').value       = r.selling_price;
    document.getElementById('ip_qty').value      = r.quantity;
    document.getElementById('ip_qty').min        = d.sold_qty;
    document.getElementById('ip_sold_info').textContent = `(المباع منها فعلاً: ${d.sold_qty}, الكمية الجديدة لازم تكون ≥ ${d.sold_qty})`;

    // ملء قائمة الخزن
    const accSel = document.getElementById('ip_settle_acc');
    accSel.innerHTML = '<option value="">اختر الخزنة...</option>';
    d.accounts.forEach(a => {
        const o = document.createElement('option');
        o.value = a.id;
        o.textContent = `${a.account_name} (متاح: ${Math.round(a.balance).toLocaleString()} ج)`;
        accSel.appendChild(o);
    });
    toggleSettleAccount();
}

// تحديث المظهر فقط (تلوين الكارت + إظهار/إخفاء الخزنة) — بدون popup. تُستدعى وقت الفتح.
function toggleSettleAccount() {
    const m = document.querySelector('input[name="settle_method"]:checked')?.value;
    document.getElementById('ip_acc_div').style.display = (m === 'account') ? 'block' : 'none';
    document.getElementById('settle_card_supplier').classList.toggle('active', m === 'supplier_debt');
    document.getElementById('settle_card_account').classList.toggle('active', m === 'account');
}

// عند ضغط المستخدم على كارت: نفعّل الاختيار، نلوّنه، ونعرض شرح مبسّط.
function onSettleClick(method) {
    const radio = document.querySelector(`input[name="settle_method"][value="${method}"]`);
    if (radio) radio.checked = true;
    toggleSettleAccount();

    if (method === 'supplier_debt') {
        Swal.fire({
            icon: 'info',
            title: 'على دين المورد',
            html: `
                <div class="text-end" style="line-height:1.9">
                    فرق التكلفة هيتحاسب على <b>حساب المورد</b> — مش هيتحرّك أي كاش من الخزنة.
                    <hr>
                    <ul class="text-end" style="padding-right:1rem">
                        <li>لو التكلفة <b>زادت</b> → بيتضاف <b>دين جديد</b> عليك للمورد.</li>
                        <li>لو التكلفة <b>نقصت</b> → بيتخصم الفرق من <b>ديون المورد</b> القائمة.</li>
                    </ul>
                    <div class="small text-muted mt-2">اختاره لو <b>لسه مدفعتش</b> للمورد والفرق هيتسوّى في الحساب بينكم.</div>
                </div>`,
            confirmButtonText: 'تمام، فهمت',
            confirmButtonColor: '#3b82f6',
        });
    } else {
        Swal.fire({
            icon: 'info',
            title: 'من / إلى خزنة',
            html: `
                <div class="text-end" style="line-height:1.9">
                    فرق التكلفة هيتحرّك <b>كاش فعلي</b> من/إلى خزنة بتختارها.
                    <hr>
                    <ul class="text-end" style="padding-right:1rem">
                        <li>لو التكلفة <b>زادت</b> → بيتسحب الفرق من الخزنة (مصروف).</li>
                        <li>لو التكلفة <b>نقصت</b> → بيترجّع الفرق للخزنة (استرداد).</li>
                    </ul>
                    <div class="small text-muted mt-2">اختاره لو <b>دفعت للمورد كاش</b> بالفعل ولازم الفرق يتحرّك من الخزنة. هتحدد الخزنة تحت.</div>
                </div>`,
            confirmButtonText: 'تمام، فهمت',
            confirmButtonColor: '#3b82f6',
        });
    }
}

// ── إلغاء بيعة نقدية ───────────────────────────────────────────
async function confirmCancelSale(d, id, pin, editor) {
    const r = d.row;
    // الحالة الوحيدة للمنع: في دفعات إضافية بعد المقدم (سداد فعلي على المستحقات)
    // المقدم نفسه مسجل كـ payment واحد، فلازم نقارن.
    const downPayment = parseFloat(r.down_payment || 0);
    const extraPayments = d.has_payments && downPayment > 0
        ? false   // مينفعش نعرف من غير backend check — السيرفر هيرفض لو في دفعات إضافية فعلاً
        : false;

    const ftAccountInfo = d.income_ft
        ? `<br><small class="text-muted">سيتم إرجاع <b>${Math.round(d.income_ft.amount).toLocaleString()} ج</b> من الخزنة المودع فيها (المقدم)</small>`
        : '<br><small class="text-muted">لم يتم العثور على إيداع كاش مرتبط — البيعة كانت آجلة بالكامل.</small>';

    const ok = await Swal.fire({
        icon: 'warning',
        title: 'تأكيد حذف البيعة بالكامل',
        html: `
            <div class="text-end">
                <p><b>العميل:</b> ${r.customer_name}</p>
                <p><b>المنتج:</b> ${r.product_name}</p>
                <p><b>السعر:</b> ${Math.round(r.cash_price).toLocaleString()} ج | <b>الربح:</b> ${Math.round(r.profit).toLocaleString()} ج</p>
                <hr>
                <p class="small">هتنحذف البيعة <b>كأنها لم تحدث أبداً</b>:</p>
                <ul class="small text-end" style="padding-right: 1rem;">
                    <li>حذف سطر البيعة من المبيعات/الأقساط نهائياً</li>
                    <li>إرجاع المقدم المدفوع من الخزنة وحذف الإيداع</li>
                    <li>حذف العمولات والمصاريف والخصومات المرتبطة</li>
                    <li>حذف ديون المورد المرتبطة (لو مش متسددة)</li>
                    <li>إرجاع البضاعة للمخزن (لبيعات المخزن)</li>
                </ul>
                ${ftAccountInfo}
                <p class="text-danger small mt-2"><b>تنبيه:</b> الحذف هيتمنع لو في تسديد فعلي على ديون المورد أو دفعات إضافية بعد المقدم. لو البيعة قديمة من المخزن من غير ربط أصناف، البضاعة <u>مش هترجع تلقائياً</u>.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'نعم، احذفها نهائياً',
        cancelButtonText: 'تراجع',
    });
    if (!ok.isConfirmed) return;

    // POST cancel
    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('admin_pin', pin);

    Swal.fire({ title: 'جاري الإلغاء...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const res = await fetch(`/operations-log/${editor}/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'text/html' },
            body: fd,
        });
        Swal.close();
        if (res.redirected) window.location.href = res.url;
        else location.reload();
    } catch (e) {
        Swal.fire('خطأ', e.message, 'error');
    }
}

function fillFuelModal(d, id, pin) {
    // فحص مسبق: لو في مدفوعات مرتبطة، نمنع التعديل ونعرض popup
    if (d.has_paid_debts) {
        Swal.fire({
            icon: 'error',
            title: 'لا يمكن التعديل',
            html: `
                <p>لا يمكن تعديل هذه العملية لارتباطها بعمليات مالية أخرى.</p>
                <p class="small text-muted mt-2">تم سداد جزء أو كل المستحقات المرتبطة بهذه العملية.<br>الغِ السداد أولاً ثم أعد التعديل.</p>
            `,
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#dc2626',
        });
        return;
    }

    document.getElementById('ef_id').value  = id;
    document.getElementById('ef_pin').value = pin;
    document.getElementById('editFuelForm').action = `/operations-log/fuel/${id}`;

    const setOptions = (sel, items, idKey, labelKey, selectedVal) => {
        sel.innerHTML = '';
        items.forEach(it => {
            const o = document.createElement('option');
            o.value = it[idKey];
            o.textContent = it[labelKey] || it.name || it.company_name || it.station_name || it.item_name || it.account_name || '—';
            if (String(it[idKey]) === String(selectedVal)) o.selected = true;
            sel.appendChild(o);
        });
    };

    setOptions(document.getElementById('ef_company'), d.companies, 'id', 'company_name', d.tx.company_id);
    setOptions(document.getElementById('ef_station'), d.stations,  'id', 'station_name', d.tx.station_id);

    const itemSel = document.getElementById('ef_item');
    itemSel.innerHTML = '<option value="0">بدون وقود (عهدة فقط)</option>';
    d.items.forEach(it => {
        const o = document.createElement('option');
        o.value = it.id;
        o.textContent = it.item_name || it.name;
        if ((it.item_name || it.name) === d.tx.fuel_type) o.selected = true;
        itemSel.appendChild(o);
    });
    if (d.tx.fuel_type === 'وقود فقط (بدون عهدة)' || !d.tx.liters || +d.tx.liters === 0) {
        itemSel.value = '0';
    }

    document.getElementById('ef_driver').value = d.tx.driver_name === 'غير محدد' ? '' : d.tx.driver_name;
    document.getElementById('ef_qty').value    = d.tx.liters || 0;
    document.getElementById('ef_adv').value    = d.tx.cash_advance || 0;

    document.querySelectorAll('input[name="advance_source"]').forEach(r => {
        r.checked = (r.value === d.advance_source);
        r.closest('.pay-card').classList.toggle('active', r.checked);
    });

    // عرض رصيد كل خزنة في القائمة
    const accSel = document.getElementById('ef_acc');
    accSel.innerHTML = '';
    d.accounts.forEach(a => {
        const o = document.createElement('option');
        o.value = a.id;
        o.textContent = `${a.account_name} (رصيد: ${Math.round(a.balance).toLocaleString()} ج)`;
        if (String(a.id) === String(d.account_id)) o.selected = true;
        accSel.appendChild(o);
    });

    onAdvanceSource();
}

function onAdvanceSource() {
    document.querySelectorAll('input[name="advance_source"]').forEach(r => {
        r.closest('.pay-card').classList.toggle('active', r.checked);
    });
    const src = document.querySelector('input[name="advance_source"]:checked')?.value;
    document.getElementById('ef_acc_div').style.display = (src === 'safe') ? 'block' : 'none';
    if (src === 'none') document.getElementById('ef_adv').value = 0;
}

async function submitOpForm(form) {
    const fd = new FormData(form);
    Swal.fire({ title: 'جاري الحفظ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'text/html' },
            body: fd,
        });
        Swal.close();
        if (res.redirected) window.location.href = res.url;
        else location.reload();
    } catch (err) {
        Swal.fire('خطأ', err.message, 'error');
    }
}

document.getElementById('editFuelForm').addEventListener('submit', e => { e.preventDefault(); submitOpForm(e.target); });
document.getElementById('editInvPurchaseForm').addEventListener('submit', e => { e.preventDefault(); submitOpForm(e.target); });

// ── مرتجع بيعة ───────────────────────────────────────────────
function fillReturnModal(d, id, pin) {
    if (d.has_paid_supplier_debts) {
        Swal.fire({
            icon: 'error',
            title: 'لا يمكن عمل المرتجع',
            html: `<p>في تسديد فعلي على ديون مورد مرتبطة بالبيعة دي.</p>
                   <p class="small text-muted mt-2">الغِ التسديد من شاشة الديون أولاً ثم أعد المرتجع.</p>`,
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#dc2626',
        });
        return false;
    }

    const r = d.row;
    document.getElementById('rs_pin').value = pin;
    document.getElementById('returnSaleForm').action = `/operations-log/sale_return/${id}`;
    document.getElementById('rs_customer').textContent = r.customer_name || '-';
    document.getElementById('rs_product').textContent  = r.product_name || '-';
    document.getElementById('rs_total_paid').textContent = Math.round(d.total_paid).toLocaleString('en-US');

    const accDiv = document.getElementById('rs_account_div');
    const accSel = document.getElementById('rs_account');
    accSel.innerHTML = '<option value="" disabled selected>اختر الخزنة...</option>';
    if (d.total_paid > 0) {
        accDiv.style.display = 'block';
        accSel.required = true;
        d.accounts.forEach(a => {
            const o = document.createElement('option');
            o.value = a.id;
            o.textContent = `${a.account_name} (رصيد: ${Math.round(a.balance).toLocaleString()} ج)`;
            accSel.appendChild(o);
        });
    } else {
        accDiv.style.display = 'none';
        accSel.required = false;
    }

    // تحذير البيعة القديمة (سيكون فيه ربط لو inventory_items فيها sale_id > 0)
    const isInventory = r.sale_type === 'inventory' || r.category === 'مبيعات مخزن';
    let hasLinkedItems = false;
    try {
        const items = r.inventory_items ? JSON.parse(r.inventory_items) : null;
        hasLinkedItems = Array.isArray(items) && items.some(it => parseInt(it.sale_id || 0) > 0);
    } catch (e) {}
    document.getElementById('rs_old_warning').style.display =
        (isInventory && !hasLinkedItems) ? 'block' : 'none';

    // ابدأ نظيف من أي خطأ قديم
    document.getElementById('rs_error').style.display = 'none';
    document.getElementById('rs_error_msg').textContent = '';

    // فحص client-side: نخزن المبلغ المطلوب على الـ select نفسه (يتقرى في onchange)
    accSel.dataset.totalPaid = d.total_paid;

    return true;
}

function validateReturnAccount(totalPaid) {
    const accSel = document.getElementById('rs_account');
    const errBox = document.getElementById('rs_error');
    const errMsg = document.getElementById('rs_error_msg');
    const opt = accSel.options[accSel.selectedIndex];
    if (!opt || !opt.value) { errBox.style.display = 'none'; return true; }
    // الرصيد مكتوب في النص: ... (رصيد: NUM ج)
    const match = opt.textContent.match(/رصيد:\s*([0-9,]+)\s*ج/);
    const balance = match ? parseFloat(match[1].replace(/,/g, '')) : NaN;
    if (!isNaN(balance) && balance < totalPaid) {
        errBox.style.display = 'block';
        errMsg.textContent = `رصيد الخزنة (${balance.toLocaleString()} ج) أقل من المبلغ المطلوب رده (${totalPaid.toLocaleString()} ج). اختار خزنة تانية.`;
        return false;
    }
    errBox.style.display = 'none';
    return true;
}

async function submitReturnForm(form) {
    const errBox = document.getElementById('rs_error');
    const errMsg = document.getElementById('rs_error_msg');
    errBox.style.display = 'none';

    const submitBtn = form.querySelector('button[type=submit]');
    const oldHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> جاري تنفيذ المرتجع...';

    try {
        const fd = new FormData(form);
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        });
        const data = await res.json().catch(() => ({ error: 'رد السيرفر غير صالح.' }));

        if (!res.ok || data.error) {
            // الفورم بتفضل مفتوحة، نعرض الخطأ جواها
            errBox.style.display = 'block';
            errMsg.textContent = data.error || 'فشل تنفيذ المرتجع.';
            submitBtn.disabled = false;
            submitBtn.innerHTML = oldHtml;
            return;
        }

        // نجاح: قفل المودال + رسالة + ريلود
        const modalEl = document.getElementById('returnSaleModal');
        bootstrap.Modal.getInstance(modalEl)?.hide();
        await Swal.fire({ icon: 'success', title: 'تم بنجاح', text: data.message || 'تم المرتجع.', timer: 2500, showConfirmButton: false });
        location.reload();
    } catch (e) {
        errBox.style.display = 'block';
        errMsg.textContent = 'فشل الاتصال بالسيرفر: ' + (e.message || '');
        submitBtn.disabled = false;
        submitBtn.innerHTML = oldHtml;
    }
}

document.getElementById('returnSaleForm').addEventListener('submit', e => { e.preventDefault(); submitReturnForm(e.target); });

// click on pay-card → check radio
document.querySelectorAll('.pay-card').forEach(card => {
    card.addEventListener('click', e => {
        const r = card.querySelector('input[type=radio]');
        r.checked = true;
        onAdvanceSource();
    });
});

// ── حذف عملية (بنزينة / مصروف) ───────────────────────────────
async function openDelete(editor, id) {
    const { value: pin } = await Swal.fire({
        title: 'حذف العملية',
        text: 'أدخل كلمة مرور حسابك (أدمن) للتأكيد:',
        input: 'password',
        showCancelButton: true,
        confirmButtonText: 'متابعة',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#dc2626',
        inputAttributes: { autocomplete: 'current-password' },
    });
    if (!pin) return;

    const labels = {
        fuel: 'عملية البنزينة وكل القيود المرتبطة (دين المحطة، الاستقطاعات، عهدة الخزنة، دين شركة النقل)',
        expense: 'هذا المصروف وإرجاع المبلغ للخزنة',
        financial: 'هذه الحركة المالية وعكس تأثيرها على الخزنة (وإلغاء سداد/تحصيل القسط إن وُجد)',
    };
    const ok = await Swal.fire({
        icon: 'warning',
        title: 'تأكيد الحذف النهائي',
        html: `<p class="text-end">سيتم حذف <b>${labels[editor] || 'العملية'}</b> بشكل نهائي وكأنها لم تحدث.</p>
               <p class="small text-danger text-end mt-2"><b>تنبيه:</b> لو فيها مدفوعات/سداد جزئي، الحذف هيتمنع.</p>`,
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'تراجع',
    });
    if (!ok.isConfirmed) return;

    Swal.fire({ title: 'جاري الحذف...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    try {
        const fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('admin_pin', pin);
        const res = await fetch(`/operations-log/${editor}/${id}/delete`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'text/html' },
            body: fd,
        });
        Swal.close();
        if (res.redirected) window.location.href = res.url;
        else location.reload();
    } catch (e) {
        Swal.fire('خطأ', e.message, 'error');
    }
}

// ── المصروف ─────────────────────────────────────────────────
function fillExpenseModal(d, id, pin) {
    document.getElementById('ee_pin').value = pin;
    document.getElementById('editExpenseForm').action = `/operations-log/expense/${id}`;

    document.getElementById('ee_amount').value = d.row.amount;
    document.getElementById('ee_notes').value  = d.actual_note || '';

    const catSel = document.getElementById('ee_category');
    catSel.innerHTML = '';
    d.categories.forEach(c => {
        const o = document.createElement('option');
        o.value = c; o.textContent = c;
        if (c === d.category) o.selected = true;
        catSel.appendChild(o);
    });

    const accSel = document.getElementById('ee_account');
    accSel.innerHTML = '<option value="">اختر الخزنة...</option>';
    d.accounts.forEach(a => {
        const o = document.createElement('option');
        o.value = a.id;
        o.textContent = `${a.account_name} (رصيد: ${Math.round(a.balance).toLocaleString()} ج)`;
        if (String(a.id) === String(d.row.from_account_id)) o.selected = true;
        accSel.appendChild(o);
    });
}

@if(session('success'))
    Swal.fire({ icon: 'success', title: 'تم بنجاح', text: @json(session('success')), timer: 3000 });
@endif
@if(session('error'))
    Swal.fire({ icon: 'error', title: 'خطأ', text: @json(session('error')) });
@endif
</script>

{{-- Modal تعديل مصروف --}}
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editExpenseForm" method="POST" class="modal-content" style="background: var(--c-surface);">
            @csrf
            <input type="hidden" name="admin_pin" id="ee_pin">
            <div class="modal-header" style="background: linear-gradient(135deg, #b91c1c, #ef4444); color: #fff;">
                <h5 class="modal-title fw-bold">
                    <i class="fa fa-receipt me-2"></i> تعديل مصروف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">التصنيف</label>
                    <select name="category" id="ee_category" class="form-select" required></select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">البيان / الملاحظات</label>
                    <input type="text" name="notes" id="ee_notes" class="form-control" placeholder="مثال: فاتورة كهرباء...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="ee_amount" class="form-control text-center fw-bold" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الخزنة</label>
                    <select name="account_id" id="ee_account" class="form-select" required></select>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="fa fa-circle-info me-1"></i>
                    التعديل هيرجع المبلغ القديم للخزنة الأصلية ويخصم المبلغ الجديد من الخزنة المختارة.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-danger fw-bold">
                    <i class="fa fa-floppy-disk me-1"></i> حفظ التعديل
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
