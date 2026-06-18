<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>استفسارات العملاء - شركة الضبع</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('partials.theme', ['accent' => 'teal'])

    <style>
        .nav-tabs-pro {
            background: var(--c-surface);
            border-radius: var(--r-lg) var(--r-lg) 0 0;
            border: 1px solid var(--c-border);
            border-bottom: none;
            padding: 8px 8px 0 8px;
            display: flex; gap: 4px;
        }
        .nav-tabs-pro .nav-link {
            border: none;
            border-radius: var(--r-md) var(--r-md) 0 0;
            font-weight: 600; color: var(--c-text-muted);
            padding: 12px 22px;
            background: transparent;
        }
        .nav-tabs-pro .nav-link.active {
            color: var(--c-navy);
            background: var(--c-bg);
            box-shadow: inset 0 -3px 0 var(--c-accent);
        }
        .count-badge {
            background: var(--c-navy-50); color: var(--c-navy);
            border-radius: 999px; padding: 1px 9px;
            font-size: 0.76rem; font-weight: 600;
            margin-right: 6px;
        }
        .nav-tabs-pro .nav-link.active .count-badge { background: var(--c-accent); color: #fff; }

        .tab-content-pro {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 0 0 var(--r-lg) var(--r-lg);
            padding: 22px;
        }

        .time-chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .time-chip {
            background: var(--c-surface); border: 1px solid var(--c-border);
            color: var(--c-text-muted); padding: 6px 14px;
            border-radius: 999px; font-weight: 500; font-size: 0.84rem;
            cursor: pointer; transition: var(--t-fast);
            display: inline-flex; align-items: center; gap: 6px;
        }
        .time-chip:hover { background: var(--c-accent-bg); border-color: var(--c-accent); color: var(--c-accent); }
        .time-chip.active {
            background: var(--c-accent);
            color: #fff; border-color: transparent;
        }

        .active-filter-notice {
            background: var(--c-warning-bg); border: 1px solid var(--c-warning);
            color: var(--c-warning); border-radius: var(--r-md);
            padding: 10px 14px; margin-bottom: 14px;
            font-weight: 500; font-size: 0.88rem;
            display: flex; align-items: center; flex-wrap: wrap;
        }
    </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    @if(session('success'))
        <div class="alert-pro success"><i class="fa fa-circle-check"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-pro danger"><i class="fa fa-triangle-exclamation"></i>{{ session('error') }}</div>
    @endif

    <div class="page-header">
        <div>
            <h2><i class="fa-solid fa-phone-volume"></i> استفسارات العملاء</h2>
            <div class="subtitle">تسجيل مكالمات وطلبات العملاء ومتابعة التواصل معاهم</div>
        </div>
        <button class="btn-pro btn-pro-accent" data-bs-toggle="modal" data-bs-target="#addInquiryModal">
            <i class="fa fa-plus"></i> تسجيل استفسار جديد
        </button>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro">
                <div class="label">إجمالي الاستفسارات</div>
                <div class="value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro warning">
                <div class="label">في انتظار التواصل</div>
                <div class="value">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro success">
                <div class="label">تم التواصل معاهم</div>
                <div class="value">{{ $stats['done'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro info">
                <div class="label">استفسارات اليوم</div>
                <div class="value">{{ $stats['today'] }}</div>
            </div>
        </div>
    </div>

    {{-- Search + Time Filter --}}
    <div class="panel-pro">
        <form method="GET" action="{{ route('inquiries.index') }}" id="filterForm">

            <div class="time-chips mb-3">
                @php
                    $chips = [
                        'all'        => ['الكل', 'fa-infinity'],
                        'today'      => ['اليوم', 'fa-calendar-day'],
                        'yesterday'  => ['أمس', 'fa-clock-rotate-left'],
                        'week'       => ['آخر 7 أيام', 'fa-calendar-week'],
                        'month'      => ['آخر 30 يوم', 'fa-calendar'],
                        'this_month' => ['هذا الشهر', 'fa-calendar-days'],
                        'custom'     => ['فترة مخصصة', 'fa-calendar-plus'],
                    ];
                @endphp
                @foreach($chips as $key => $info)
                    <button type="button"
                            class="time-chip {{ $period === $key ? 'active' : '' }}"
                            data-period="{{ $key }}">
                        <i class="fa {{ $info[1] }}"></i> {{ $info[0] }}
                    </button>
                @endforeach
            </div>

            <div class="row g-2 mb-3" id="customRangeBox" style="display: {{ $period === 'custom' ? 'flex' : 'none' }};">
                <div class="col-md-5">
                    <label class="form-pro-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control form-pro-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-5">
                    <label class="form-pro-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control form-pro-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn-pro btn-pro-primary w-100"><i class="fa fa-check"></i> تطبيق</button>
                </div>
            </div>

            <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

            <div class="row g-2 align-items-center">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control form-pro-control" placeholder="ابحث باسم العميل، الرقم، نوع المنتج، أو نص الاستفسار..." value="{{ $search }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-pro btn-pro-primary flex-grow-1"><i class="fa fa-magnifying-glass"></i> بحث</button>
                    @if($search || $period !== 'all')
                        <a href="{{ route('inquiries.index') }}" class="btn-pro btn-pro-outline" title="إعادة تعيين"><i class="fa fa-xmark"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if($period !== 'all')
        <div class="active-filter-notice">
            <i class="fa fa-filter me-1"></i>
            <span>عرض النتائج لـ: <strong>{{ $chips[$period][0] ?? 'فترة مخصصة' }}</strong></span>
            @if($period === 'custom' && ($fromDate || $toDate))
                <span class="mx-2 muted-pro">·</span>
                <span class="small" dir="ltr">{{ $fromDate ?: '...' }} → {{ $toDate ?: 'اليوم' }}</span>
            @endif
            <a href="{{ route('inquiries.index') }}" class="ms-2 fw-bold small" style="color: var(--c-danger);"><i class="fa fa-xmark"></i> إزالة</a>
        </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs-pro" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPending" type="button">
                <i class="fa fa-hourglass-half me-1"></i> لم يتم التواصل
                <span class="count-badge">{{ count($pending) }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDone" type="button">
                <i class="fa fa-circle-check me-1"></i> تم التواصل
                <span class="count-badge">{{ count($done) }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content tab-content-pro">
        <div class="tab-pane fade show active" id="tabPending">
            @forelse($pending as $row)
                @include('partials.inquiry_card', ['row' => $row, 'isDone' => false])
            @empty
                <div class="empty-pro">
                    <i class="fa-regular fa-bell-slash"></i>
                    <h5>لا توجد استفسارات في انتظار التواصل</h5>
                    <p>كل العملاء تم التواصل معاهم</p>
                </div>
            @endforelse
        </div>

        <div class="tab-pane fade" id="tabDone">
            @forelse($done as $row)
                @include('partials.inquiry_card', ['row' => $row, 'isDone' => true])
            @empty
                <div class="empty-pro">
                    <i class="fa-regular fa-folder-open"></i>
                    <h5>لم يتم التواصل مع أي عميل بعد</h5>
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Modal: تسجيل استفسار جديد --}}
<div class="modal fade" id="addInquiryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:var(--r-lg);border:none;">
            <div class="modal-header" style="background:var(--c-navy);color:#fff;border:none;border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-headset me-2"></i> تسجيل استفسار جديد من عميل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inquiries.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-pro-label">اسم العميل <span style="color:var(--c-danger);">*</span></label>
                            <input type="text" name="customer_name" class="form-control form-pro-control" placeholder="مثال: أحمد محمد" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-pro-label">رقم الهاتف (اختياري)</label>
                            <input type="text" name="customer_phone" class="form-control form-pro-control" placeholder="مثال: 01012345678" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-pro-label">نوع المنتج / الاستفسار</label>
                            <input type="text" name="product_type" class="form-control form-pro-control" list="productTypes" placeholder="مثال: موبايل، تكييف، شاشة..." autocomplete="off">
                            <datalist id="productTypes">
                                <option value="موبايل"><option value="تكييف"><option value="شاشة">
                                <option value="لاب توب"><option value="جهاز كهربائي"><option value="خدمة">
                                <option value="استفسار عن قسط">
                            </datalist>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch fs-5 mb-2">
                                <input class="form-check-input" type="checkbox" id="isContactedNew" name="is_contacted" value="1">
                                <label class="form-check-label fw-bold" for="isContactedNew">تم التواصل بالفعل</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-pro-label">تفاصيل الطلب / الاستفسار <span style="color:var(--c-danger);">*</span></label>
                            <textarea name="inquiry" class="form-control form-pro-control" rows="4" placeholder="مثال: العميل بيسأل عن سعر iPhone 15 Pro Max وإن كان متاح بالتقسيط..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:var(--c-bg);border-radius:0 0 var(--r-lg) var(--r-lg);">
                    <button type="button" class="btn-pro btn-pro-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-pro btn-pro-primary"><i class="fa fa-save"></i> حفظ الاستفسار</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: تأكيد التواصل --}}
<div class="modal fade" id="confirmContactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--r-lg);border:none;">
            <div class="modal-header" style="background:var(--c-success);color:#fff;border:none;border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-circle-check me-2"></i> تأكيد التواصل مع العميل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="confirmContactForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert-pro success"><i class="fa fa-info-circle"></i>اكتب ملخص قصير لما قاله العميل أو نتيجة التواصل (اختياري).</div>
                    <div class="mb-2">
                        <label class="form-pro-label">ملاحظات التواصل</label>
                        <textarea name="contact_notes" class="form-control form-pro-control" rows="3" placeholder="مثال: العميل اقتنع وهيمر لاستلام الجهاز يوم الجمعة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:var(--c-bg);border-radius:0 0 var(--r-lg) var(--r-lg);">
                    <button type="button" class="btn-pro btn-pro-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-pro btn-pro-success"><i class="fa fa-check"></i> تأكيد التواصل</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: تعديل --}}
<div class="modal fade" id="editInquiryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:var(--r-lg);border:none;">
            <div class="modal-header" style="background:var(--c-navy);color:#fff;border:none;border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-pen-to-square me-2"></i> تعديل بيانات الاستفسار</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInquiryForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-pro-label">اسم العميل</label>
                            <input type="text" name="customer_name" id="edit_customer_name" class="form-control form-pro-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-pro-label">رقم الهاتف</label>
                            <input type="text" name="customer_phone" id="edit_customer_phone" class="form-control form-pro-control" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-pro-label">نوع المنتج</label>
                            <input type="text" name="product_type" id="edit_product_type" class="form-control form-pro-control">
                        </div>
                        <div class="col-12">
                            <label class="form-pro-label">تفاصيل الاستفسار</label>
                            <textarea name="inquiry" id="edit_inquiry" class="form-control form-pro-control" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-pro-label">ملاحظات التواصل</label>
                            <textarea name="contact_notes" id="edit_contact_notes" class="form-control form-pro-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:var(--c-bg);border-radius:0 0 var(--r-lg) var(--r-lg);">
                    <button type="button" class="btn-pro btn-pro-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-pro btn-pro-primary"><i class="fa fa-save"></i> حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteInquiryForm" method="POST" style="display:none;">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.time-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const period = this.dataset.period;
            document.getElementById('periodInput').value = period;
            if (period === 'custom') {
                document.querySelectorAll('.time-chip').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('customRangeBox').style.display = 'flex';
                return;
            }
            document.getElementById('filterForm').submit();
        });
    });

    function openConfirmContact(id) {
        document.getElementById('confirmContactForm').action = `/inquiries/${id}/toggle-contact`;
        new bootstrap.Modal(document.getElementById('confirmContactModal')).show();
    }

    function undoContact(id) {
        Swal.fire({
            title: 'إرجاع الاستفسار لقائمة الانتظار؟',
            text: 'هيتم مسح بيانات التواصل والملاحظات.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، أرجع',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#b67c1f'
        }).then(r => {
            if (r.isConfirmed) {
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = `/inquiries/${id}/toggle-contact`;
                f.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                document.body.appendChild(f);
                f.submit();
            }
        });
    }

    function editInquiry(data) {
        document.getElementById('editInquiryForm').action = `/inquiries/${data.id}/update`;
        document.getElementById('edit_customer_name').value = data.customer_name || '';
        document.getElementById('edit_customer_phone').value = data.customer_phone || '';
        document.getElementById('edit_product_type').value = data.product_type || '';
        document.getElementById('edit_inquiry').value = data.inquiry || '';
        document.getElementById('edit_contact_notes').value = data.contact_notes || '';
        new bootstrap.Modal(document.getElementById('editInquiryModal')).show();
    }

    function deleteInquiry(id) {
        Swal.fire({
            title: 'حذف الاستفسار؟',
            text: 'هيتم مسح السجل بشكل نهائي.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#b91c1c'
        }).then(r => {
            if (r.isConfirmed) {
                const f = document.getElementById('deleteInquiryForm');
                f.action = `/inquiries/${id}/delete`;
                f.submit();
            }
        });
    }
</script>
</body>
</html>
