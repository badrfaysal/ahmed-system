<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المهام - شركة الضبع</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/html2canvas/html2canvas.min.js') }}"></script>

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

    <script>
        // هل آخر عملية اتحفظت بنجاح فعلاً على السيرفر؟ بيُستخدم مع علامة الفورم عشان نتأكد إن صوت التنبيه
        // بيشتغل بس لما "إضافة مهمة جديدة" يتحفظ بنجاح، مش مع أي تعديل أو حذف أو تأكيد تواصل.
        window.__lastActionSavedSuccessfully = {{ session('success') ? 'true' : 'false' }};
    </script>

    <div class="page-header">
        <div>
            <h2><i class="fa-solid fa-list-check"></i> المهام</h2>
            <div class="subtitle">كتابة المهام وتحديد الموظف المكلّف بها وإرسالها له كصورة أو رسالة</div>
        </div>
        @if($isAdmin)<button class="btn-pro btn-pro-accent" data-bs-toggle="modal" data-bs-target="#addInquiryModal">
            <i class="fa fa-plus-circle me-1"></i> إضافة مهام جديدة
        </button>@endif
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro">
                <div class="label">إجمالي المهام</div>
                <div class="value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro warning">
                <div class="label">مهام معلقة</div>
                <div class="value">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro success">
                <div class="label">مهام منجزة</div>
                <div class="value">{{ $stats['done'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro info">
                <div class="label">مهام اليوم</div>
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
                <div class="col-md-{{ $isAdmin ? '6' : '9' }}">
                    <input type="text" name="search" class="form-control form-pro-control" placeholder="ابحث بعنوان المهمة أو اسم الموظف أو التفاصيل..." value="{{ $search }}">
                </div>
                @if($isAdmin)
                <div class="col-md-3">
                    <select name="user_id" class="form-select form-pro-control" onchange="document.getElementById('filterForm').submit()">
                        <option value="">— تصفية بالموظف (الكل) —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (string)($selectedUserId ?? '') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-pro btn-pro-primary flex-grow-1"><i class="fa fa-magnifying-glass"></i> بحث</button>
                    @if($search || $period !== 'all' || !empty($selectedUserId))
                        <a href="{{ route('inquiries.index') }}" class="btn-pro btn-pro-outline" title="إعادة تعيين"><i class="fa fa-xmark"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if($period !== 'all' || !empty($selectedUserId))
        <div class="active-filter-notice">
            <i class="fa fa-filter me-1"></i>
            <span>عرض النتائج لـ: <strong>{{ $chips[$period][0] ?? 'فترة مخصصة' }}</strong></span>
            @if(!empty($selectedUserId))
                @php $fltUser = $users->firstWhere('id', $selectedUserId); @endphp
                @if($fltUser)
                    <span class="mx-2 muted-pro">·</span>
                    <span>الموظف: <strong>{{ $fltUser->name }}</strong></span>
                @endif
            @endif
            @if($period === 'custom' && ($fromDate || $toDate))
                <span class="mx-2 muted-pro">·</span>
                <span class="small" dir="ltr">{{ $fromDate ?: '...' }} → {{ $toDate ?: 'اليوم' }}</span>
            @endif
            <a href="{{ route('inquiries.index') }}" class="ms-2 fw-bold small" style="color: var(--c-danger);"><i class="fa fa-xmark"></i> إزالة الفلاتر</a>
        </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs-pro" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPending" type="button">
                <i class="fa fa-hourglass-half me-1"></i> معلقة
                <span class="count-badge">{{ count($pending) }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDone" type="button">
                <i class="fa fa-circle-check me-1"></i> منجزة
                <span class="count-badge">{{ count($done) }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content tab-content-pro">
        <div class="tab-pane fade show active" id="tabPending">
            @if($isAdmin && count($pending) > 0)
                @php
                    $pendingByAssignee = $pending->groupBy('assigned_to');
                @endphp
                @if($pendingByAssignee->count() > 0)
                    <div class="p-3 mb-3" style="background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--r-md); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="fw-bold text-navy small">
                                <i class="fa-brands fa-whatsapp text-success me-1 fa-lg"></i> إرسال المهام المعلقة دفعة واحدة عبر واتساب:
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach($pendingByAssignee as $assigneeId => $group)
                                    @php
                                        $firstTask = $group->first();
                                        $assigneeName = $firstTask->assigned_to_name ?: 'غير محدد';
                                        $wa = preg_replace('/\D/', '', (string) ($firstTask->assigned_phone ?? ''));
                                        if ($wa && str_starts_with($wa, '0')) {
                                            $wa = '20' . substr($wa, 1);
                                        }
                                        $groupPayload = json_encode([
                                            'assignee_name'  => $assigneeName,
                                            'assignee_phone' => $firstTask->assigned_phone,
                                            'wa_phone'       => $wa,
                                            'tasks'          => $group->map(function($t) {
                                                return [
                                                    'id'      => $t->id,
                                                    'title'   => $t->customer_name,
                                                    'details' => $t->inquiry,
                                                    'date'    => \Carbon\Carbon::parse($t->created_at)->format('Y/m/d h:i A'),
                                                ];
                                            })->values(),
                                        ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                                    @endphp
                                    <button type="button" class="btn-pro btn-pro-sm" style="background: #25D366; color: #fff; font-size: 0.82rem;" onclick='sendGroupedTasksToWhatsApp({!! $groupPayload !!})'>
                                        <i class="fa-brands fa-whatsapp me-1"></i> مهام {{ $assigneeName }} ({{ count($group) }})
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            @forelse($pending as $row)
                @include('partials.inquiry_card', ['row' => $row, 'isDone' => false])
            @empty
                <div class="empty-pro">
                    <i class="fa-regular fa-bell-slash"></i>
                    <h5>لا توجد مهام معلقة</h5>
                    <p>كل المهام منجزة</p>
                </div>
            @endforelse
        </div>

        <div class="tab-pane fade" id="tabDone">
            @forelse($done as $row)
                @include('partials.inquiry_card', ['row' => $row, 'isDone' => true])
            @empty
                <div class="empty-pro">
                    <i class="fa-regular fa-folder-open"></i>
                    <h5>لا توجد مهام منجزة بعد</h5>
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Modal: إضافة مهام جديدة --}}
<div class="modal fade" id="addInquiryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:var(--r-lg);border:none;box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background:var(--c-navy);color:#fff;border:none;border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check" style="font-size: 1.25rem;"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">إضافة مهام جديدة</h5>
                        <small style="opacity: 0.82; font-size: 0.82rem;">يمكنك كتابة مهمة واحدة أو عدة مهام دفعة واحدة وتكليف الموظف بها</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addInquiryForm" action="{{ route('inquiries.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- الموظف المكلّف الافتراضي ورقم الواتساب --}}
                    <div class="p-3 mb-3" style="background: var(--c-bg); border-radius: var(--r-md); border: 1px solid var(--c-border);">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-pro-label fw-bold">
                                    <i class="fa fa-user-check me-1 text-primary"></i> الموظف المكلّف بالمهام <span style="color:var(--c-danger);">*</span>
                                </label>
                                <select name="assigned_to" id="task_assigned_to" class="form-select form-pro-control" required onchange="onAssigneeChanged(this)">
                                    <option value="">— اختر الموظف —</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" data-phone="{{ $latestPhones[$u->id] ?? '' }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-pro-label">
                                    <i class="fa-brands fa-whatsapp me-1 text-success"></i> رقم واتساب الموظف (لإرسال المهام)
                                </label>
                                <input type="text" name="assigned_phone" id="task_assigned_phone" class="form-control form-pro-control" placeholder="01012345678" dir="ltr">
                            </div>
                        </div>
                    </div>

                    {{-- تبويبات نمط الإدخال: مفصل أو سريع --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn-pro btn-pro-sm active" id="btnModeDetailed" onclick="switchTaskEntryMode('detailed')">
                                <i class="fa fa-tasks me-1"></i> مهام مفصلة (قائمة بنود)
                            </button>
                            <button type="button" class="btn-pro btn-pro-sm btn-pro-outline" id="btnModeQuick" onclick="switchTaskEntryMode('quick')">
                                <i class="fa fa-bolt me-1 text-warning"></i> إدخال سريع (كل سطر مهمة)
                            </button>
                        </div>
                    </div>

                    {{-- قسم 1: مهام مفصلة --}}
                    <div id="sectionDetailed">
                        <div id="taskRowsContainer">
                            {{-- سطر المهمة الأولى --}}
                            <div class="task-entry-card mb-3 p-3" id="taskRow_0" style="background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--r-md); position: relative;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed var(--c-border);">
                                    <span class="badge bg-secondary task-index-badge"><i class="fa fa-thumbtack me-1"></i> المهمة رقم 1</span>
                                    <button type="button" class="btn btn-sm text-danger btn-remove-task" onclick="removeTaskRow(0)" style="display: none; border: none; background: transparent;" title="حذف هذه المهمة">
                                        <i class="fa fa-trash-can me-1"></i> حذف المهمة
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-pro-label">عنوان المهمة <span style="color:var(--c-danger);">*</span></label>
                                        <input type="text" name="tasks[0][title]" class="form-control form-pro-control task-title-input" placeholder="مثال: متابعة تحصيل قسط العميل أحمد" required onkeydown="onTaskTitleKeyDown(event, 0)">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-pro-label">تفاصيل المهمة <small class="text-muted">(اختياري - إن تركت فارغة سيُعتمد العنوان)</small></label>
                                        <textarea name="tasks[0][details]" class="form-control form-pro-control task-details-input" rows="2" placeholder="اكتب المطلوب تنفيذه بالتفصيل..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <button type="button" class="btn-pro btn-pro-outline" onclick="addTaskRow()" style="border-style: dashed; border-width: 2px;">
                                <i class="fa fa-plus-circle me-1 text-success"></i> إضافة مهمة أخرى (+)
                            </button>
                            <span class="text-muted small fw-bold" id="taskRowsCountText">إجمالي المهام المكتوبة: 1</span>
                        </div>
                    </div>

                    {{-- قسم 2: إدخال سريع --}}
                    <div id="sectionQuick" style="display: none;">
                        <div class="alert-pro info mb-3">
                            <i class="fa fa-circle-info"></i>
                            <div>
                                <strong>طريقة سريعة لكتابة عدة مهام معاً:</strong><br>
                                اكتب أو الصق مهامك هنا، كل سطر هيتحول لمهمة مستقلة للموظف المكلّف.<br>
                                <small class="text-muted">ملاحظة: يمكنك فصل العنوان والتفاصيل بنقطتين (:) مثل: <code>تحصيل قسط : الاتصال بالعميل واستلام المبلغ</code></small>
                            </div>
                        </div>
                        <label class="form-pro-label fw-bold">قائمة المهام (سطر لكل مهمة):</label>
                        <textarea name="bulk_text" id="bulk_text_input" class="form-control form-pro-control" rows="8" placeholder="مهمة 1: الاتصال بالعميل محمد علي بخصوص قسط التكييف
مهمة 2: استلام الفاتورة من المخزن
مهمة 3: تأكيد التحويل في البنك" disabled oninput="onQuickTextInput(this)"></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-muted small" id="quickLinesCountText">عدد المهام: 0 مهمة</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between" style="background:var(--c-bg);border-radius:0 0 var(--r-lg) var(--r-lg);">
                    <button type="button" class="btn-pro btn-pro-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-pro btn-pro-primary px-4" id="btnSubmitTasks">
                        <i class="fa fa-floppy-disk me-1"></i> <span id="submitBtnText">حفظ المهمة</span>
                    </button>
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
                <h5 class="modal-title fw-bold"><i class="fa fa-circle-check me-2"></i> تأكيد إنجاز المهمة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="confirmContactForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert-pro success"><i class="fa fa-info-circle"></i>اكتب ملاحظة قصيرة عن نتيجة التنفيذ (اختياري).</div>
                    <div class="mb-2">
                        <label class="form-pro-label">ملاحظات الإنجاز</label>
                        <textarea name="contact_notes" class="form-control form-pro-control" rows="3" placeholder="مثال: العميل اقتنع وهيمر لاستلام الجهاز يوم الجمعة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:var(--c-bg);border-radius:0 0 var(--r-lg) var(--r-lg);">
                    <button type="button" class="btn-pro btn-pro-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-pro btn-pro-success"><i class="fa fa-check"></i> تأكيد الإنجاز</button>
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
                <h5 class="modal-title fw-bold"><i class="fa fa-pen-to-square me-2"></i> تعديل المهمة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInquiryForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-pro-label">المهمة (عنوان)</label>
                            <input type="text" name="customer_name" id="edit_title" class="form-control form-pro-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-pro-label">المهمة لـ</label>
                            <select name="assigned_to" id="edit_assigned_to" class="form-select form-pro-control" required>
                                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-pro-label">رقم واتساب الموظف</label>
                            <input type="text" name="assigned_phone" id="edit_assigned_phone" class="form-control form-pro-control" dir="ltr">
                        </div>
                        <div class="col-12">
                            <label class="form-pro-label">تفاصيل المهمة</label>
                            <textarea name="inquiry" id="edit_inquiry" class="form-control form-pro-control" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-pro-label">ملاحظات الإنجاز</label>
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

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
    // ═══ صوت تنبيه طويل لما يتسجل استفسار جديد ═══
    // بنحط علامة قبل ما الفورم يتبعت (sessionStorage بتفضل موجودة بعد إعادة تحميل الصفحة)،
    // وبعد ما الصفحة تتحمل تاني بعد الحفظ، لو العلامة موجودة + السيرفر أكد إن الحفظ نجح، نشغّل الصوت.
    const addInquiryFormEl = document.getElementById('addInquiryForm');
    if (addInquiryFormEl) {
        addInquiryFormEl.addEventListener('submit', function () {
            sessionStorage.setItem('newInquiryNotify', '1');
        });
    }

    // بيانات الهواتف المسجلة مسبقاً للموظفين
    const latestPhonesData = {!! json_encode($latestPhones ?? [], JSON_UNESCAPED_UNICODE) !!};
    let currentTaskEntryMode = 'detailed';
    let taskRowCounter = 0;

    function onAssigneeChanged(selectEl) {
        const userId = selectEl.value;
        const phone = selectEl.options[selectEl.selectedIndex]?.dataset?.phone || latestPhonesData[userId] || '';
        const phoneInput = document.getElementById('task_assigned_phone');
        if (phoneInput && (!phoneInput.value || phoneInput.dataset.autoFilled === '1')) {
            phoneInput.value = phone;
            phoneInput.dataset.autoFilled = phone ? '1' : '0';
        }
    }

    document.getElementById('task_assigned_phone')?.addEventListener('input', function() {
        this.dataset.autoFilled = '0';
    });

    document.getElementById('edit_assigned_to')?.addEventListener('change', function() {
        const phone = latestPhonesData[this.value] || '';
        if (phone) {
            document.getElementById('edit_assigned_phone').value = phone;
        }
    });

    function switchTaskEntryMode(mode) {
        currentTaskEntryMode = mode;
        const btnDetailed = document.getElementById('btnModeDetailed');
        const btnQuick    = document.getElementById('btnModeQuick');
        const secDetailed = document.getElementById('sectionDetailed');
        const secQuick    = document.getElementById('sectionQuick');
        const bulkInput   = document.getElementById('bulk_text_input');

        if (mode === 'quick') {
            btnDetailed.classList.remove('active');
            btnDetailed.classList.add('btn-pro-outline');
            btnQuick.classList.add('active');
            btnQuick.classList.remove('btn-pro-outline');

            secDetailed.style.display = 'none';
            secQuick.style.display = 'block';

            document.querySelectorAll('#taskRowsContainer input, #taskRowsContainer textarea').forEach(el => {
                el.disabled = true;
            });
            bulkInput.disabled = false;
            bulkInput.required = true;
            onQuickTextInput(bulkInput);
            bulkInput.focus();
        } else {
            btnQuick.classList.remove('active');
            btnQuick.classList.add('btn-pro-outline');
            btnDetailed.classList.add('active');
            btnDetailed.classList.remove('btn-pro-outline');

            secQuick.style.display = 'none';
            secDetailed.style.display = 'block';

            document.querySelectorAll('#taskRowsContainer input, #taskRowsContainer textarea').forEach(el => {
                el.disabled = false;
            });
            bulkInput.disabled = true;
            bulkInput.required = false;
            updateDetailedTasksStats();
        }
    }

    function onQuickTextInput(textarea) {
        const lines = textarea.value.split('\n').map(l => l.trim()).filter(Boolean);
        const count = lines.length;
        const countEl = document.getElementById('quickLinesCountText');
        if (countEl) countEl.textContent = `عدد المهام: ${count} مهمة`;
        updateSubmitButtonLabel(count);
    }

    function addTaskRow() {
        taskRowCounter++;
        const container = document.getElementById('taskRowsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'task-entry-card mb-3 p-3';
        newRow.id = `taskRow_${taskRowCounter}`;
        newRow.style.cssText = 'background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--r-md); position: relative; animation: fadeIn 0.25s ease-in-out;';

        newRow.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed var(--c-border);">
                <span class="badge bg-secondary task-index-badge"><i class="fa fa-thumbtack me-1"></i> المهمة رقم </span>
                <button type="button" class="btn btn-sm text-danger btn-remove-task" onclick="removeTaskRow(${taskRowCounter})" style="border: none; background: transparent;" title="حذف هذه المهمة">
                    <i class="fa fa-trash-can me-1"></i> حذف المهمة
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-pro-label">عنوان المهمة <span style="color:var(--c-danger);">*</span></label>
                    <input type="text" name="tasks[${taskRowCounter}][title]" class="form-control form-pro-control task-title-input" placeholder="اكتب عنوان المهمة..." required onkeydown="onTaskTitleKeyDown(event, ${taskRowCounter})">
                </div>
                <div class="col-12">
                    <label class="form-pro-label">تفاصيل المهمة <small class="text-muted">(اختياري - إن تركت فارغة سيُعتمد العنوان)</small></label>
                    <textarea name="tasks[${taskRowCounter}][details]" class="form-control form-pro-control task-details-input" rows="2" placeholder="اكتب تفاصيل المطلوب تنفيذه..."></textarea>
                </div>
            </div>
        `;
        container.appendChild(newRow);

        updateDetailedTasksStats();

        const titleInput = newRow.querySelector('.task-title-input');
        if (titleInput) titleInput.focus();
    }

    function removeTaskRow(idx) {
        const row = document.getElementById(`taskRow_${idx}`);
        if (row) {
            row.remove();
            updateDetailedTasksStats();
        }
    }

    function updateDetailedTasksStats() {
        const rows = document.querySelectorAll('#taskRowsContainer .task-entry-card');
        const count = rows.length;

        rows.forEach((r, i) => {
            const badge = r.querySelector('.task-index-badge');
            if (badge) badge.innerHTML = `<i class="fa fa-thumbtack me-1"></i> المهمة رقم ${i + 1}`;
            const delBtn = r.querySelector('.btn-remove-task');
            if (delBtn) delBtn.style.display = count > 1 ? 'inline-block' : 'none';
        });

        const countTextEl = document.getElementById('taskRowsCountText');
        if (countTextEl) countTextEl.textContent = `إجمالي المهام المكتوبة: ${count}`;
        updateSubmitButtonLabel(count);
    }

    function updateSubmitButtonLabel(count) {
        const lbl = document.getElementById('submitBtnText');
        if (!lbl) return;
        if (count <= 1) {
            lbl.textContent = 'حفظ المهمة';
        } else if (count === 2) {
            lbl.textContent = 'حفظ مهمتين';
        } else {
            lbl.textContent = `حفظ ${count} مهام`;
        }
    }

    function onTaskTitleKeyDown(event, idx) {
        if (event.key === 'Enter' && event.ctrlKey) {
            event.preventDefault();
            addTaskRow();
        }
    }

    function playInquiryAlertSound() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();

            // نغمتين متبادلتين (شبه جرس) بتتكرر كذا مرة عشان الصوت يبقى طويل وملحوظ
            const notes = [880, 660, 880, 660, 880, 660];
            const noteDuration = 0.32; // مدة كل نغمة بالثانية
            const gap = 0.12;          // فاصل بسيط بين النغمات
            let t = ctx.currentTime + 0.05;

            notes.forEach((freq) => {
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, t);

                // envelope ناعم عشان الصوت ميطلعش "طقطقة" في بداية/نهاية كل نغمة
                gain.gain.setValueAtTime(0, t);
                gain.gain.linearRampToValueAtTime(0.55, t + 0.03);
                gain.gain.linearRampToValueAtTime(0.55, t + noteDuration - 0.05);
                gain.gain.linearRampToValueAtTime(0, t + noteDuration);

                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(t);
                osc.stop(t + noteDuration);

                t += noteDuration + gap;
            });

            // قفل الـ AudioContext بعد ما الصوت يخلص لتوفير موارد المتصفح
            setTimeout(() => { try { ctx.close(); } catch (e) {} }, (t + 0.5) * 1000);
        } catch (e) {
            console.warn('تعذر تشغيل صوت التنبيه:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const justSubmittedNewInquiry = sessionStorage.getItem('newInquiryNotify') === '1';
        sessionStorage.removeItem('newInquiryNotify');
        if (justSubmittedNewInquiry && window.__lastActionSavedSuccessfully) {
            playInquiryAlertSound();
        }
    });

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
            title: 'إرجاع المهمة للمعلقة؟',
            text: 'هيتم مسح بيانات الإنجاز والملاحظات.',
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
        document.getElementById('edit_title').value = data.title || '';
        document.getElementById('edit_assigned_to').value = data.assigned_to || '';
        document.getElementById('edit_assigned_phone').value = data.assigned_phone || '';
        document.getElementById('edit_inquiry').value = data.details || '';
        document.getElementById('edit_contact_notes').value = data.contact_notes || '';
        new bootstrap.Modal(document.getElementById('editInquiryModal')).show();
    }

    // ═══ تنزيل المهمة كصورة / إرسالها للموظف ═══
    function escHtml(s) { return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

    async function downloadTaskImage(t, send) {
        const box = document.createElement('div');
        box.style.cssText = 'position:fixed;left:-9999px;top:0;width:560px;background:#fff;direction:rtl;font-family:IBM Plex Sans Arabic,Tahoma,Arial,sans-serif;letter-spacing:normal;word-spacing:normal;border:3px solid #0f2a47;border-radius:16px;overflow:hidden;';
        box.innerHTML = `
            <div style="background:#0f2a47;color:#fff;padding:18px 24px;font-size:22px;font-weight:700;">📋 مهمة جديدة — شركة الضبع</div>
            <div style="padding:22px 24px;color:#1f2937;">
                <div style="font-size:15px;color:#6b7280;">المهمة لـ</div>
                <div style="font-size:26px;font-weight:700;color:#0f2a47;margin-bottom:16px;">${escHtml(t.assigned_to_name || 'غير محدد')}</div>
                <div style="font-size:15px;color:#6b7280;">عنوان المهمة</div>
                <div style="font-size:20px;font-weight:700;margin-bottom:14px;">${escHtml(t.title)}</div>
                <div style="font-size:15px;color:#6b7280;">التفاصيل</div>
                <div style="font-size:18px;line-height:1.8;background:#f3f4f6;border-radius:10px;padding:12px 14px;white-space:pre-wrap;">${escHtml(t.details)}</div>
                <div style="margin-top:16px;font-size:14px;color:#6b7280;display:flex;justify-content:space-between;">
                    <span>من: ${escHtml(t.created_by_name || '')}</span><span>${escHtml(t.created_at)}</span>
                </div>
            </div>`;
        document.body.appendChild(box);
        try {
            if (document.fonts && document.fonts.ready) await document.fonts.ready;
            const canvas = await html2canvas(box, {
                scale: 2, backgroundColor: '#ffffff', logging: false,
                onclone: function (doc) {
                    // html2canvas بيرسم كل حرف لوحده لو فيه letter-spacing، وده بيفصّل/يلخبط الحروف العربية
                    const st = doc.createElement('style');
                    st.textContent = '* { letter-spacing: 0 !important; word-spacing: normal !important; text-rendering: auto !important; }';
                    doc.head.appendChild(st);
                }
            });
            const blob = await new Promise(r => canvas.toBlob(r, 'image/png'));
            const file = new File([blob], `task-${t.id}.png`, {type: 'image/png'});

            if (send && navigator.canShare && navigator.canShare({files: [file]})) {
                await navigator.share({files: [file], title: 'مهمة', text: `مهمة لـ ${t.assigned_to_name || ''}`});
                return;
            }
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = file.name;
            a.click();
            if (send) {
                const text = `📋 *مهمة جديدة — شركة الضبع*\n👤 *المهمة لـ:* ${t.assigned_to_name || ''}\n📌 *عنوان المهمة:* ${t.title}\n📝 *التفاصيل:*\n${t.details}\n\nمن: ${t.created_by_name || ''} (${t.created_at || ''})`;
                const waUrl = t.wa_phone 
                    ? `https://wa.me/${t.wa_phone}?text=${encodeURIComponent(text)}`
                    : `https://web.whatsapp.com/send?text=${encodeURIComponent(text)}`;

                Swal.fire({
                    icon: 'info',
                    title: 'تم تنزيل صورة المهمة',
                    text: 'هيتم فتح واتساب مع نص المهمة جاهز، وتقدر ترفق الصورة المنزّلة أيضاً.',
                    confirmButtonText: '<i class="fa-brands fa-whatsapp me-1"></i> فتح واتساب',
                    confirmButtonColor: '#25D366'
                }).then(() => window.open(waUrl, '_blank'));
            }
        } catch (e) {
            if (e.name !== 'AbortError') Swal.fire({icon: 'error', title: 'تعذر إنشاء الصورة'});
        } finally {
            box.remove();
        }
    }

    // ═══ إرسال وتنزيل مجموعة مهام الموظف معاً ═══
    async function sendGroupedTasksToWhatsApp(group) {
        if (!group || !group.tasks || group.tasks.length === 0) return;

        let text = `📋 *مهامك المعلقة — شركة الضبع*\n👤 *المكلّف:* ${group.assignee_name}\n📅 *التاريخ:* ${new Date().toLocaleDateString('ar-EG')}\n🔢 *إجمالي المهام:* ${group.tasks.length}\n──────────────────\n`;

        group.tasks.forEach((t, i) => {
            text += `\n${i + 1}️⃣ *${t.title}*\n📝 ${t.details}\n`;
        });
        text += `\n──────────────────\n✨ يُرجى البدء في التنفيذ وتأكيد الإنجاز.`;

        const waUrl = group.wa_phone 
            ? `https://wa.me/${group.wa_phone}?text=${encodeURIComponent(text)}`
            : `https://web.whatsapp.com/send?text=${encodeURIComponent(text)}`;

        const result = await Swal.fire({
            title: `إرسال مهام ${group.assignee_name}`,
            html: `يوجد <b>${group.tasks.length} مهام معلقة</b> للموظف <b>${escHtml(group.assignee_name)}</b>.<br><br>هل تريد تنزيل صورة مجمعة لكل المهام وفتح واتساب؟`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-brands fa-whatsapp me-1"></i> فتح واتساب وتنزيل الصورة',
            cancelButtonText: 'إرسال النص فقط على واتساب',
            showDenyButton: true,
            denyButtonText: 'إلغاء',
            confirmButtonColor: '#25D366',
            cancelButtonColor: '#0f2a47',
        });

        if (result.isConfirmed) {
            await downloadGroupedTasksImage(group, true, waUrl);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.open(waUrl, '_blank');
        }
    }

    async function downloadGroupedTasksImage(group, openWa, waUrl) {
        const box = document.createElement('div');
        box.style.cssText = 'position:fixed;left:-9999px;top:0;width:600px;background:#fff;direction:rtl;font-family:IBM Plex Sans Arabic,Tahoma,Arial,sans-serif;letter-spacing:normal;word-spacing:normal;border:3px solid #0f2a47;border-radius:16px;overflow:hidden;';
        
        let tasksHtml = '';
        group.tasks.forEach((t, i) => {
            tasksHtml += `
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-right:4px solid #0f2a47;border-radius:10px;padding:12px 14px;margin-bottom:10px;">
                    <div style="font-size:16px;font-weight:700;color:#0f2a47;margin-bottom:4px;">${i + 1}. ${escHtml(t.title)}</div>
                    <div style="font-size:14px;line-height:1.6;color:#334155;white-space:pre-wrap;">${escHtml(t.details)}</div>
                </div>
            `;
        });

        box.innerHTML = `
            <div style="background:#0f2a47;color:#fff;padding:18px 24px;">
                <div style="font-size:20px;font-weight:700;">📋 قائمة المهام — شركة الضبع</div>
                <div style="font-size:14px;opacity:0.85;margin-top:4px;">الموظف المكلّف: <b>${escHtml(group.assignee_name)}</b> — عدد المهام: <b>${group.tasks.length}</b></div>
            </div>
            <div style="padding:20px 24px;color:#1f2937;">
                ${tasksHtml}
                <div style="margin-top:14px;font-size:13px;color:#6b7280;text-align:left;">
                    ${new Date().toLocaleString('ar-EG')}
                </div>
            </div>`;
        document.body.appendChild(box);

        try {
            if (document.fonts && document.fonts.ready) await document.fonts.ready;
            const canvas = await html2canvas(box, {
                scale: 2, backgroundColor: '#ffffff', logging: false,
                onclone: function (doc) {
                    const st = doc.createElement('style');
                    st.textContent = '* { letter-spacing: 0 !important; word-spacing: normal !important; text-rendering: auto !important; }';
                    doc.head.appendChild(st);
                }
            });
            const blob = await new Promise(r => canvas.toBlob(r, 'image/png'));
            const file = new File([blob], `tasks-${group.assignee_name}.png`, {type: 'image/png'});

            if (openWa && navigator.canShare && navigator.canShare({files: [file]})) {
                await navigator.share({files: [file], title: `مهام ${group.assignee_name}`, text: `قائمة مهام لـ ${group.assignee_name}`});
                return;
            }

            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = file.name;
            a.click();

            if (openWa && waUrl) {
                window.open(waUrl, '_blank');
            }
        } catch (e) {
            if (e.name !== 'AbortError') Swal.fire({icon: 'error', title: 'تعذر إنشاء الصورة المجمعة'});
        } finally {
            box.remove();
        }
    }
    function deleteInquiry(id) {
        Swal.fire({
            title: 'حذف المهمة؟',
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