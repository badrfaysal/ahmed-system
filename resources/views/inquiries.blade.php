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
            border-bottom: 1px solid var(--c-border);
            padding: 10px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-tabs-pro .nav-item {
            flex: 1 1 220px;
        }
        .nav-tabs-pro .nav-link {
            width: 100%;
            border: 1.5px solid var(--c-border);
            border-radius: var(--r-md);
            font-weight: 700;
            font-size: 0.96rem;
            color: var(--c-text-muted);
            padding: 13px 18px;
            background: var(--c-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--t-fast);
        }
        .nav-tabs-pro .nav-link:hover {
            border-color: var(--c-accent);
            color: var(--c-navy);
            background: var(--c-accent-bg);
        }
        .nav-tabs-pro .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, var(--c-navy) 0%, var(--c-accent) 100%);
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(13, 115, 115, 0.22);
        }
        .nav-tabs-pro .nav-link.tab-prev.active {
            background: linear-gradient(135deg, #334155 0%, #0f766e 100%);
            box-shadow: 0 4px 12px rgba(15, 42, 71, 0.18);
        }
        .count-badge {
            background: var(--c-navy-50); color: var(--c-navy);
            border-radius: 999px; padding: 2px 10px;
            font-size: 0.78rem; font-weight: 700;
            margin-right: 4px;
        }
        .nav-tabs-pro .nav-link.active .count-badge {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

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

        .btn-pro.active {
            background: var(--c-navy);
            color: #fff;
            border-color: var(--c-navy);
        }

        /* ── الكارت اليومي الموحّد: كل يوم قائمة مستقلة تجمع كل موظفيه ومهامهم معاً ── */
        .day-sheets-stack {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .day-sheet-card {
            background: var(--c-surface);
            border: 1.5px solid var(--c-border-2);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .day-sheet-head {
            background: linear-gradient(135deg, var(--c-navy) 0%, var(--c-navy-soft) 100%);
            color: #fff;
            padding: 14px 18px;
            border-bottom: 3px solid var(--c-accent);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .day-rel-badge {
            background: var(--c-accent);
            color: #fff;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
        }
        .day-meta-pill {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 500;
        }
        .day-sheet-body {
            padding: 16px;
            background: var(--c-bg);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .day-employee-section {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-right: 4px solid var(--c-navy);
            border-radius: var(--r-md);
            overflow: hidden;
        }
        .day-employee-head {
            background: var(--c-navy-50);
            padding: 9px 14px;
            border-bottom: 1px solid var(--c-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .employee-avatar-sm {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--c-navy); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.92rem; flex-shrink: 0;
        }
        .day-employee-tasks {
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .grouped-task-item {
            background: var(--c-surface-2);
            border: 1px solid var(--c-border);
            border-radius: var(--r-sm);
            padding: 9px 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .task-num-badge {
            width: 24px; height: 24px; border-radius: 50%;
            background: var(--c-accent); color: #fff;
            font-size: 0.78rem; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .task-mini-actions {
            display: flex; gap: 4px; flex-shrink: 0; align-items: center;
        }
        .btn-mini-icon {
            border: 1px solid var(--c-border-2);
            background: var(--c-surface);
            color: var(--c-text-muted);
            width: 28px; height: 28px;
            border-radius: var(--r-sm);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.78rem; cursor: pointer; transition: var(--t-fast);
        }
        .btn-mini-icon:hover { background: var(--c-navy-50); color: var(--c-navy); }
        .btn-mini-icon.done-btn { background: var(--c-success-bg); color: var(--c-success); border-color: #b7e4ca; }
        .btn-mini-icon.done-btn:hover { background: var(--c-success); color: #fff; }
        .btn-mini-icon.del-btn:hover { background: var(--c-danger-bg); color: var(--c-danger); border-color: #f5c2c2; }
        .btn-mini-icon.wa-btn { background: #e8fbf0; color: #16a34a; border-color: #86efac; }
        .btn-mini-icon.wa-btn:hover { background: #25d366; color: #fff; border-color: #25d366; }
        .btn-wa-pro {
            background: #25d366 !important;
            color: #fff !important;
            border: 1px solid #25d366 !important;
            font-weight: 700;
        }
        .btn-wa-pro:hover {
            background: #1ebe5d !important;
            border-color: #1ebe5d !important;
            color: #fff !important;
        }

        /* ── قائمة الاختيار الذكية (كتابة حرة + اقتراحات) ── */
        .assignee-combo { position: relative; }
        .assignee-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            right: 0; left: 0;
            z-index: 1065;
            background: #fff;
            border: 1px solid var(--c-border-2);
            border-radius: var(--r-md);
            box-shadow: var(--shadow-lg);
            max-height: 220px;
            overflow-y: auto;
            display: none;
        }
        .assignee-dropdown.show { display: block; }
        .assignee-dropdown-item {
            padding: 8px 12px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
            border-bottom: 1px solid var(--c-border);
            font-size: 0.9rem;
        }
        .assignee-dropdown-item:last-child { border-bottom: none; }
        .assignee-dropdown-item:hover { background: var(--c-navy-50); }
        .assignee-source-tag {
            font-size: 0.72rem;
            padding: 2px 7px;
            border-radius: 999px;
            background: var(--c-bg);
            color: var(--c-text-muted);
            border: 1px solid var(--c-border);
            white-space: nowrap;
        }
        .assignee-source-tag.user { background: var(--c-info-bg); color: var(--c-info); border-color: transparent; }
        .assignee-quick-chips {
            display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px;
        }
        .assignee-chip {
            border: 1px solid var(--c-border-2);
            background: var(--c-bg);
            color: var(--c-text);
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.78rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--t-fast);
        }
        .assignee-chip:hover { background: var(--c-accent-bg); border-color: var(--c-accent); color: var(--c-accent); }
        .assignee-chip.selected { background: var(--c-accent); border-color: var(--c-accent); color: #fff; }

        /* ── شارة اختيار اليوم والموظفين داخل معاينة الصورة ── */
        .poster-filter-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 999px;
            border: 1px solid var(--c-border-2);
            background: var(--c-surface);
            font-size: 0.82rem; font-weight: 600;
            cursor: pointer; user-select: none;
            transition: var(--t-fast);
        }
        .poster-filter-chip.active {
            background: var(--c-navy); color: #fff; border-color: var(--c-navy);
        }
        .poster-day-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: var(--r-md);
            border: 1px solid var(--c-border-2);
            background: var(--c-surface);
            font-size: 0.84rem; font-weight: 600;
            cursor: pointer;
            transition: var(--t-fast);
        }
        .poster-day-chip.active {
            background: var(--c-accent); color: #fff; border-color: var(--c-accent);
        }
        .poster-preview-scroll {
            background: #e9eef5;
            padding: 18px;
            border-radius: var(--r-md);
            max-height: 62vh;
            overflow-y: auto;
            display: flex;
            justify-content: center;
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
        window.__lastActionSavedSuccessfully = {{ session('success') ? 'true' : 'false' }};
    </script>

    <div class="page-header">
        <div>
            <h2><i class="fa-solid fa-list-check"></i> المهام</h2>
            <div class="subtitle">كل يوم له قائمة موحّدة تجمع جميع الموظفين ومهامهم معاً حتى لا تتداخل الأيام</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($pendingDays->count() > 0)
                <button type="button" class="btn-pro btn-pro-primary" onclick="openTasksPosterModal()">
                    <i class="fa fa-images me-1"></i> صورة المهام المجمّعة
                </button>
                <button type="button" class="btn-pro btn-wa-pro" onclick="shareTasksPosterWhatsApp()">
                    <i class="fa-brands fa-whatsapp me-1"></i> إرسال واتساب
                </button>
            @endif
            @if($canManageTasks ?? $isAdmin)
                <button class="btn-pro btn-pro-accent" data-bs-toggle="modal" data-bs-target="#addInquiryModal" onclick="setAddTaskDate('{{ today()->toDateString() }}')">
                    <i class="fa fa-plus-circle me-1"></i> إضافة مهام جديدة
                </button>
            @endif
        </div>
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
                <div class="label">مهام اليوم (جارية)</div>
                <div class="value">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro success">
                <div class="label">مهام سابقة</div>
                <div class="value">{{ $stats['done'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro info">
                <div class="label">مهام تاريخ اليوم</div>
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
                <div class="col-md-{{ ($canManageTasks ?? $isAdmin) ? '6' : '9' }}">
                    <input type="text" name="search" class="form-control form-pro-control" placeholder="ابحث بنص المهمة أو اسم الموظف..." value="{{ $search }}">
                </div>
                @if($canManageTasks ?? $isAdmin)
                <div class="col-md-3">
                    <select name="assignee" class="form-select form-pro-control" onchange="document.getElementById('filterForm').submit()">
                        <option value="">— تصفية بالموظف (الكل) —</option>
                        @foreach($filterAssignees as $fa)
                            <option value="{{ $fa->value }}" {{ $selectedAssignee === $fa->value ? 'selected' : '' }}>{{ $fa->label }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-pro btn-pro-primary flex-grow-1"><i class="fa fa-magnifying-glass"></i> بحث</button>
                    @if($search || $period !== 'all' || $selectedAssignee !== '')
                        <a href="{{ route('inquiries.index') }}" class="btn-pro btn-pro-outline" title="إعادة تعيين"><i class="fa fa-xmark"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if($period !== 'all' || $selectedAssignee !== '')
        <div class="active-filter-notice">
            <i class="fa fa-filter me-1"></i>
            <span>عرض النتائج لـ: <strong>{{ $chips[$period][0] ?? 'فترة مخصصة' }}</strong></span>
            @if($selectedAssignee !== '')
                @php
                    $fltItem = collect($filterAssignees)->firstWhere('value', $selectedAssignee);
                    $fltLabel = $fltItem ? $fltItem->label : (str_starts_with($selectedAssignee, 'n:') ? substr($selectedAssignee, 2) : $selectedAssignee);
                @endphp
                <span class="mx-2 muted-pro">·</span>
                <span>الموظف: <strong>{{ $fltLabel }}</strong></span>
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
                <i class="fa-solid fa-calendar-day"></i>
                <span>مهام اليوم</span>
                <span class="count-badge">{{ count($pending) }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link tab-prev" data-bs-toggle="tab" data-bs-target="#tabDone" type="button">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>مهام سابقة</span>
                <span class="count-badge">{{ count($done) }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content tab-content-pro">
        <div class="tab-pane fade show active" id="tabPending">
            @if(count($pending) > 0)
                {{-- شريط التحكم في طريقة العرض --}}
                <div class="p-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: var(--c-bg); border: 1px solid var(--c-border); border-radius: var(--r-md);">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <span class="fw-bold small text-navy"><i class="fa fa-layer-group me-1"></i> طريقة العرض:</span>
                        <div class="d-flex gap-1" role="group">
                            <button type="button" class="btn-pro btn-pro-sm active" id="btnViewGrouped" onclick="switchPendingView('grouped')">
                                <i class="fa fa-calendar-check me-1"></i> مجمّعة حسب اليوم
                            </button>
                            <button type="button" class="btn-pro btn-pro-sm btn-pro-outline" id="btnViewList" onclick="switchPendingView('list')">
                                <i class="fa fa-list me-1"></i> بطاقات منفردة
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn-pro btn-pro-sm btn-pro-primary" onclick="openTasksPosterModal()">
                            <i class="fa fa-images me-1"></i> معاينة الصورة
                        </button>
                        <button type="button" class="btn-pro btn-pro-sm" style="background: var(--c-info); color: #fff;" onclick="downloadTasksPosterImage()">
                            <i class="fa fa-download me-1"></i> تنزيل صورة
                        </button>
                        <button type="button" class="btn-pro btn-pro-sm btn-wa-pro" onclick="shareTasksPosterWhatsApp()">
                            <i class="fa-brands fa-whatsapp me-1"></i> إرسال واتساب
                        </button>
                    </div>
                </div>

                {{-- العرض 1: كارت واحد موحّد لكل يوم (كل موظفي اليوم ومهامهم معاً) --}}
                <div id="pendingGroupedContainer">
                    <div class="day-sheets-stack">
                        @foreach($pendingDays as $day)
                            <div class="day-sheet-card" id="dayCard_{{ $day->date }}">
                                <div class="day-sheet-head">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold" style="font-size: 1.05rem;">
                                            <i class="fa-regular fa-calendar-days me-1"></i> مهام {{ $day->date_label }}
                                        </span>
                                        @if($day->relative_tag)
                                            <span class="day-rel-badge">{{ $day->relative_tag }}</span>
                                        @endif
                                        <span class="day-meta-pill">
                                            {{ $day->total_assignees }} {{ $day->total_assignees === 1 ? 'موظف' : 'موظفين' }} · {{ $day->total_tasks }} {{ $day->total_tasks === 1 ? 'مهمة' : ($day->total_tasks === 2 ? 'مهمتان' : 'مهام') }}
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        @if($canManageTasks ?? $isAdmin)
                                            <button type="button" class="btn-pro btn-pro-sm" style="background: var(--c-accent); color: #fff; font-weight: 700;" onclick="openAddTaskForDate('{{ $day->date }}')">
                                                <i class="fa fa-plus me-1"></i> إضافة مهمة
                                            </button>
                                        @endif
                                        <button type="button" class="btn-pro btn-pro-sm" style="background: rgba(255,255,255,0.16); color: #fff; border: 1px solid rgba(255,255,255,0.28);" onclick="openTasksPosterModal('{{ $day->date }}')">
                                            <i class="fa fa-eye me-1"></i> معاينة الصورة
                                        </button>
                                        <button type="button" class="btn-pro btn-pro-sm" style="background: #fff; color: var(--c-navy); font-weight: 700;" onclick="downloadTasksPosterImage('{{ $day->date }}')">
                                            <i class="fa fa-download me-1"></i> تنزيل صورة اليوم
                                        </button>
                                        <button type="button" class="btn-pro btn-pro-sm btn-wa-pro" onclick="shareTasksPosterWhatsApp('{{ $day->date }}')">
                                            <i class="fa-brands fa-whatsapp me-1"></i> إرسال واتساب
                                        </button>
                                    </div>
                                </div>

                                <div class="day-sheet-body">
                                    @foreach($day->assignees as $group)
                                        <div class="day-employee-section">
                                            <div class="day-employee-head">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="employee-avatar-sm">{{ mb_substr($group->assignee_name, 0, 1, 'UTF-8') }}</div>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="fw-bold text-navy" style="font-size: 1rem;">{{ $group->assignee_name }}</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn-mini-icon" title="تنزيل صورة مهام {{ $group->assignee_name }}" onclick="downloadTasksPosterImage('{{ $day->date }}', '{{ $group->key }}')">
                                                        <i class="fa fa-download"></i>
                                                    </button>
                                                    <button type="button" class="btn-mini-icon wa-btn" title="إرسال مهام {{ $group->assignee_name }} على واتساب" onclick="shareTasksPosterWhatsApp('{{ $day->date }}', '{{ $group->key }}')">
                                                        <i class="fa-brands fa-whatsapp"></i>
                                                    </button>
                                                    @if($canManageTasks ?? $isAdmin)
                                                        <button type="button" class="btn-pro btn-pro-sm btn-pro-outline" style="padding: 3px 10px; font-size: 0.78rem;" onclick="openBulkEditGroup('{{ $day->date }}', '{{ $group->key }}')">
                                                            <i class="fa fa-pen-to-square me-1 text-primary"></i> تعديل المهام
                                                        </button>
                                                    @endif
                                                    <span class="count-badge" style="margin-right: 0;">
                                                        {{ count($group->tasks) }} {{ count($group->tasks) === 1 ? 'مهمة' : (count($group->tasks) === 2 ? 'مهمتان' : 'مهام') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="day-employee-tasks">
                                                @foreach($group->tasks as $tIndex => $row)
                                                    @php
                                                        $hasTitle = filled($row->customer_name) && trim((string) $row->customer_name) !== trim((string) $row->inquiry);
                                                        $taskDateVal = !empty($row->task_date) ? (string) $row->task_date : \Carbon\Carbon::parse($row->created_at)->toDateString();
                                                        $rowJs = json_encode([
                                                            'id'               => $row->id,
                                                            'title'            => $hasTitle ? $row->customer_name : null,
                                                            'raw_title'        => $row->customer_name,
                                                            'details'          => $row->inquiry,
                                                            'assigned_to'      => $row->assigned_to,
                                                            'assigned_to_name' => $row->assigned_to_name,
                                                            'task_date'        => $taskDateVal,
                                                            'contact_notes'    => $row->contact_notes,
                                                        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                                                    @endphp
                                                    <div class="grouped-task-item">
                                                        <span class="task-num-badge">{{ $tIndex + 1 }}</span>
                                                        <div class="flex-grow-1" style="min-width: 0;">
                                                            @if($hasTitle)
                                                                <div class="fw-bold text-navy mb-1" style="font-size: 0.92rem;">{{ $row->customer_name }}</div>
                                                            @endif
                                                            <div style="font-size: 0.96rem; line-height: 1.6; color: var(--c-text); white-space: pre-wrap; word-break: break-word;">{{ $row->inquiry }}</div>
                                                        </div>
                                                        <div class="task-mini-actions">
                                                            <button type="button" class="btn-mini-icon done-btn" title="تم إنجاز المهمة" onclick="openConfirmContact({{ $row->id }})">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                            @if($canManageTasks ?? $isAdmin)
                                                                <button type="button" class="btn-pro btn-pro-primary btn-pro-sm" style="padding: 4px 10px; font-size: 0.78rem;" title="تعديل المهمة" onclick='editInquiry({!! $rowJs !!})'>
                                                                    <i class="fa fa-pen"></i> تعديل
                                                                </button>
                                                                <button type="button" class="btn-mini-icon del-btn" title="حذف" onclick="deleteInquiry({{ $row->id }})">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- العرض 2: البطاقات المنفردة --}}
                <div id="pendingListContainer" style="display: none;">
                    @foreach($pending as $row)
                        @include('partials.inquiry_card', ['row' => $row, 'isDone' => false])
                    @endforeach
                </div>
            @else
                <div class="empty-pro">
                    <i class="fa-regular fa-calendar-check"></i>
                    <h5>لا توجد مهام حالياً في مهام اليوم</h5>
                    <p>كل المهام تم نقلها إلى المهام السابقة أو يمكنك إضافة مهام جديدة</p>
                </div>
            @endif
        </div>

        <div class="tab-pane fade" id="tabDone">
            @forelse($done as $row)
                @include('partials.inquiry_card', ['row' => $row, 'isDone' => true])
            @empty
                <div class="empty-pro">
                    <i class="fa-regular fa-folder-open"></i>
                    <h5>لا توجد مهام سابقة بعد</h5>
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
                        <small style="opacity: 0.85; font-size: 0.82rem;">اكتب المهمة مباشرة بدون عنوان، ثم اكتب أو اختر اسم الموظف</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addInquiryForm" action="{{ route('inquiries.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- تاريخ القائمة (حتى لا تتداخل مهام الأيام) + تبويبات نمط الإدخال --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 p-2 px-3" style="background: var(--c-bg); border: 1px solid var(--c-border); border-radius: var(--r-md);">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label for="add_task_date" class="fw-bold small text-navy mb-0">
                                <i class="fa-regular fa-calendar me-1 text-primary"></i> مهام يوم:
                            </label>
                            <input type="date" name="task_date" id="add_task_date" class="form-control form-control-sm form-pro-control" style="width: auto;" value="{{ today()->toDateString() }}" required>
                            <button type="button" class="btn-pro btn-pro-sm btn-pro-outline" onclick="setAddTaskDate('{{ today()->toDateString() }}')">اليوم</button>
                            <button type="button" class="btn-pro btn-pro-sm btn-pro-outline" onclick="setAddTaskDate('{{ today()->addDay()->toDateString() }}')">غداً</button>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn-pro btn-pro-sm active" id="btnModeQuick" onclick="switchTaskEntryMode('quick')">
                                <i class="fa fa-bolt me-1 text-warning"></i> إدخال سريع
                            </button>
                            <button type="button" class="btn-pro btn-pro-sm btn-pro-outline" id="btnModeDetailed" onclick="switchTaskEntryMode('detailed')">
                                <i class="fa fa-tasks me-1"></i> بند ببند
                            </button>
                        </div>
                    </div>

                    {{-- قسم 1: مهام مفصلة (المهمة أولاً ثم المهمة لمين) --}}
                    <div id="sectionDetailed" style="display: none;">
                        <div id="taskRowsContainer">
                            <div class="task-entry-card mb-3 p-3" id="taskRow_0" style="background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-md); position: relative;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed var(--c-border);">
                                    <span class="badge bg-secondary task-index-badge"><i class="fa fa-thumbtack me-1"></i> المهمة رقم 1</span>
                                    <button type="button" class="btn btn-sm text-danger btn-remove-task" onclick="removeTaskRow(0)" style="display: none; border: none; background: transparent;" title="حذف هذه المهمة">
                                        <i class="fa fa-trash-can me-1"></i> حذف المهمة
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-pro-label fw-bold">
                                            <i class="fa fa-pen-to-square me-1 text-primary"></i> المهمة <span style="color:var(--c-danger);">*</span>
                                        </label>
                                        <textarea name="tasks[0][details]" class="form-control form-pro-control task-details-input" rows="2" placeholder="اكتب المهمة المطلوب تنفيذها..." required disabled onkeydown="onTaskDetailsKeyDown(event)"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-pro-label fw-bold">
                                            <i class="fa fa-user-check me-1 text-primary"></i> المهمة لمين؟ <span style="color:var(--c-danger);">*</span>
                                            <small class="text-muted fw-normal">(اكتب أي اسم أو اختر)</small>
                                        </label>
                                        <div class="assignee-combo">
                                            <input type="hidden" name="tasks[0][assigned_to]" class="task-assignee-id" value="" disabled>
                                            <div class="input-group">
                                                <input type="text" name="tasks[0][assigned_name]" class="form-control form-pro-control task-assignee-name" placeholder="اكتب اسم الموظف..." autocomplete="off" required disabled>
                                                <button type="button" class="btn btn-outline-secondary btn-toggle-suggestions" tabindex="-1" title="اختيار من القائمة">
                                                    <i class="fa fa-chevron-down"></i>
                                                </button>
                                            </div>
                                            <div class="assignee-dropdown"></div>
                                            <div class="assignee-quick-chips"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 flex-wrap gap-2">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn-pro btn-pro-outline" onclick="addTaskRow(false)" style="border-style: dashed; border-width: 2px;">
                                    <i class="fa fa-plus-circle me-1 text-success"></i> إضافة مهمة أخرى
                                </button>
                                <button type="button" class="btn-pro btn-pro-outline" onclick="addTaskRow(true)" title="إضافة مهمة جديدة مع تكرار اسم نفس الموظف السابق">
                                    <i class="fa fa-user-plus me-1 text-primary"></i> + مهمة لنفس الموظف
                                </button>
                            </div>
                            <span class="text-muted small fw-bold" id="taskRowsCountText">إجمالي المهام المكتوبة: 1</span>
                        </div>
                    </div>

                    {{-- قسم 2: إدخال سريع (الافتراضي) - كتابة مهام لجميع الموظفين دفعة واحدة --}}
                    <div id="sectionQuick">
                        <div class="alert-pro info mb-3">
                            <i class="fa fa-bolt text-warning"></i>
                            <div>
                                <strong>إدخال سريع لمهام جميع الموظفين دفعة واحدة:</strong><br>
                                اكتب مهام كل موظف (سطر لكل مهمة)، واضغط <strong>"+ إضافة مهمة / موظف آخر"</strong> لتسجيل مهام باقي الموظفين في نفس الوقت.
                            </div>
                        </div>

                        <div id="quickBlocksContainer">
                            <div class="quick-block-card mb-3 p-3" id="quickBlock_0" style="background: var(--c-surface); border: 1.5px solid var(--c-border-2); border-radius: var(--r-md); position: relative;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed var(--c-border);">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold quick-block-badge">
                                        <i class="fa fa-user-check me-1"></i> موظف #1
                                    </span>
                                    <button type="button" class="btn btn-sm text-danger btn-remove-quick-block" onclick="removeQuickBlock(0)" style="display: none; border: none; background: transparent;" title="حذف هذا الموظف">
                                        <i class="fa fa-trash-can me-1"></i> حذف
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12 mb-2">
                                        <label class="form-pro-label fw-bold small">
                                            <i class="fa fa-user-check me-1 text-primary"></i> المهمة لمين؟ <span style="color:var(--c-danger);">*</span>
                                            <small class="text-muted fw-normal">(اختر من الأسماء أو اكتب أي اسم)</small>
                                        </label>
                                        <div class="assignee-combo">
                                            <input type="hidden" name="quick_blocks[0][assigned_to]" class="task-assignee-id">
                                            <div class="input-group">
                                                <input type="text" name="quick_blocks[0][assigned_name]" class="form-control form-pro-control task-assignee-name" placeholder="اكتب اسم الموظف..." autocomplete="off" required>
                                                <button type="button" class="btn btn-outline-secondary btn-toggle-suggestions" tabindex="-1">
                                                    <i class="fa fa-chevron-down"></i>
                                                </button>
                                            </div>
                                            <div class="assignee-dropdown"></div>
                                            <div class="assignee-quick-chips"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-pro-label fw-bold small">
                                            <i class="fa fa-list-check me-1 text-primary"></i> قائمة المهام لهذا الموظف <span style="color:var(--c-danger);">*</span>
                                            <small class="text-muted fw-normal">(اكتب مهمة واحدة أو عدة مهام — سطر لكل مهمة)</small>
                                        </label>
                                        <textarea name="quick_blocks[0][text]" class="form-control form-pro-control quick-block-text" rows="3" placeholder="الاتصال بالعميل بخصوص القسط&#10;استلام الفاتورة من المخزن&#10;تأكيد المعاينة" required oninput="updateQuickTasksStats()"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-1 flex-wrap gap-2">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn-pro btn-pro-outline" onclick="addQuickBlock(false)" style="border-style: dashed; border-width: 2px;">
                                    <i class="fa fa-plus-circle me-1 text-success"></i> + إضافة مهمة / موظف آخر
                                </button>
                                <button type="button" class="btn-pro btn-pro-outline" onclick="addQuickBlock(true)" title="إضافة مهمة جديدة لنفس الموظف السابق">
                                    <i class="fa fa-user-plus me-1 text-primary"></i> + إضافة مهمة لنفس الموظف
                                </button>
                            </div>
                            <span class="text-muted small fw-bold" id="quickTasksStatsText">إجمالي المهام: 0 مهمة لـ 1 موظف</span>
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

{{-- Modal: معاينة وتنزيل صورة المهام المنظّمة (يوم واحد في كل صورة) --}}
<div class="modal fade" id="tasksPosterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:var(--r-lg);border:none;">
            <div class="modal-header" style="background:var(--c-navy);color:#fff;border:none;border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fa fa-images me-2"></i> صورة المهام المنظّمة</h5>
                    <small style="opacity:0.85;">كل يوم في صورة مستقلة — يمكنك تنزيلها أو إرسالها مباشرة على واتساب</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="posterDaysSelectorWrap" class="mb-3" style="display: none;">
                    <div class="small fw-bold text-muted mb-2">
                        <i class="fa-regular fa-calendar me-1"></i> اختر اليوم:
                    </div>
                    <div id="posterDaysSelector" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="mb-3">
                    <div class="small fw-bold text-muted mb-2">
                        <i class="fa fa-filter me-1"></i> الموظفون الظاهرون في الصورة (اضغط للإظهار/الإخفاء):
                    </div>
                    <div id="posterEmployeesFilter" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="mb-3 p-2 px-3 d-flex align-items-center gap-2 flex-wrap" style="background: var(--c-bg); border: 1px solid var(--c-border); border-radius: var(--r-md);">
                    <label for="posterWaPhone" class="small fw-bold mb-0 text-success" style="white-space: nowrap;">
                        <i class="fa-brands fa-whatsapp me-1"></i> رقم واتساب:
                    </label>
                    <input type="text" id="posterWaPhone" class="form-control form-control-sm form-pro-control" style="max-width: 200px;" dir="ltr" value="01094494782" placeholder="01094494782" oninput="this.dataset.userEdited = '1'; updatePosterDirectChatLink(this.value)">
                    <a id="posterDirectChatLink" href="https://wa.me/201094494782" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success fw-bold">
                        <i class="fa-brands fa-whatsapp me-1"></i> فتح شات الرقم
                    </a>
                </div>

                <div class="poster-preview-scroll">
                    <div id="tasksPosterPreviewHost" style="width: 100%; max-width: 680px;"></div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between flex-wrap gap-2" style="background:var(--c-bg);border-radius:0 0 var(--r-lg) var(--r-lg);">
                <button type="button" class="btn-pro btn-pro-outline" data-bs-dismiss="modal">إغلاق</button>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn-pro btn-pro-primary px-4" onclick="downloadTasksPosterImage()">
                        <i class="fa fa-download me-1"></i> تنزيل الصورة (PNG)
                    </button>
                    <button type="button" class="btn-pro btn-wa-pro px-4" onclick="shareTasksPosterWhatsApp()">
                        <i class="fa-brands fa-whatsapp me-1"></i> إرسال واتساب
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: تأكيد الإنجاز --}}
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
                        <textarea name="contact_notes" class="form-control form-pro-control" rows="3" placeholder="مثال: تم التنفيذ والتسليم بنجاح..."></textarea>
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

{{-- Modal: تعديل المهمة --}}
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
                        <div class="col-12">
                            <label class="form-pro-label fw-bold">المهمة <span style="color:var(--c-danger);">*</span></label>
                            <textarea name="inquiry" id="edit_inquiry" class="form-control form-pro-control" rows="3" required></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-pro-label fw-bold">المهمة لمين؟ <span style="color:var(--c-danger);">*</span></label>
                            <div class="assignee-combo">
                                <input type="hidden" name="assigned_to" id="edit_assigned_to" class="task-assignee-id">
                                <div class="input-group">
                                    <input type="text" name="assigned_to_name" id="edit_assigned_name" class="form-control form-pro-control task-assignee-name" autocomplete="off" required>
                                    <button type="button" class="btn btn-outline-secondary btn-toggle-suggestions" tabindex="-1">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="assignee-dropdown"></div>
                                <div class="assignee-quick-chips"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-pro-label fw-bold">تاريخ القائمة</label>
                            <input type="date" name="task_date" id="edit_task_date" class="form-control form-pro-control">
                        </div>
                        <div class="col-12" id="editTitleGroup" style="display: none;">
                            <label class="form-pro-label">عنوان إضافي (اختياري)</label>
                            <input type="text" name="customer_name" id="edit_title" class="form-control form-pro-control">
                        </div>
                        <div class="col-12">
                            <label class="form-pro-label">ملاحظات الإنجاز</label>
                            <textarea name="contact_notes" id="edit_contact_notes" class="form-control form-pro-control" rows="2"></textarea>
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

{{-- Modal: تعديل مهام موظف في يوم (تعديل جماعي) --}}
<div class="modal fade" id="bulkEditTasksModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:var(--r-lg);border:none;">
            <div class="modal-header" style="background:var(--c-navy);color:#fff;border:none;border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fa fa-pen-to-square me-2"></i> تعديل مهام: <span id="bulkEditHeaderEmployee"></span></h5>
                    <small style="opacity: 0.85;" id="bulkEditHeaderDate"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkEditTasksForm" action="{{ route('inquiries.bulk_update') }}" method="POST">
                @csrf
                <div id="bulkEditOriginalIdsHost"></div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3 p-3" style="background: var(--c-bg); border: 1px solid var(--c-border); border-radius: var(--r-md);">
                        <div class="col-md-8">
                            <label class="form-pro-label fw-bold">المهمة لمين؟ <span style="color:var(--c-danger);">*</span></label>
                            <div class="assignee-combo">
                                <input type="hidden" name="assigned_to" id="bulk_edit_assigned_to" class="task-assignee-id">
                                <div class="input-group">
                                    <input type="text" name="assigned_name" id="bulk_edit_assigned_name" class="form-control form-pro-control task-assignee-name" autocomplete="off" required>
                                    <button type="button" class="btn btn-outline-secondary btn-toggle-suggestions" tabindex="-1">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="assignee-dropdown"></div>
                                <div class="assignee-quick-chips"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-pro-label fw-bold">تاريخ القائمة</label>
                            <input type="date" name="task_date" id="bulk_edit_task_date" class="form-control form-pro-control">
                        </div>
                    </div>

                    <div id="bulkEditRowsContainer" class="d-flex flex-column gap-2"></div>

                    <div class="mt-3">
                        <button type="button" class="btn-pro btn-pro-outline btn-pro-sm" onclick="addBulkEditNewRow()" style="border-style: dashed; border-width: 2px;">
                            <i class="fa fa-plus-circle me-1 text-success"></i> إضافة مهمة جديدة لهذه القائمة
                        </button>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between" style="background:var(--c-bg);border-radius:0 0 var(--r-lg) var(--r-lg);">
                    <button type="button" class="btn-pro btn-pro-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn-pro btn-pro-primary px-4"><i class="fa fa-save me-1"></i> حفظ كل التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteInquiryForm" method="POST" style="display:none;">@csrf</form>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
    const addInquiryFormEl = document.getElementById('addInquiryForm');
    if (addInquiryFormEl) {
        addInquiryFormEl.addEventListener('submit', function () {
            sessionStorage.setItem('newInquiryNotify', '1');
        });
    }

    const assigneeSuggestions = {!! json_encode($assigneeSuggestions ?? [], JSON_UNESCAPED_UNICODE) !!};
    const posterDataEndpoint  = {!! json_encode(route('inquiries.poster_data'), JSON_UNESCAPED_UNICODE) !!};
    let pendingDaysData       = {!! json_encode($tasksPosterDaysJson ?? [], JSON_UNESCAPED_UNICODE) !!};
    let currentTaskEntryMode  = 'quick';
    let taskRowCounter        = 0;
    let bulkEditRowCounter    = 0;
    let activePosterDate      = pendingDaysData.length > 0 ? pendingDaysData[0].date : null;
    let selectedPosterKeys    = new Set(
        pendingDaysData.length > 0 ? pendingDaysData[0].assignees.map(a => a.key) : []
    );
    const posterKnownKeysByDate = new Map(
        pendingDaysData.map(d => [d.date, new Set(d.assignees.map(a => a.key))])
    );

    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    function normAr(s) {
        return String(s ?? '').trim().replace(/\s+/g, ' ').toLowerCase()
            .replace(/[أإآٱ]/g, 'ا')
            .replace(/ى/g, 'ي')
            .replace(/ة/g, 'ه');
    }

    function setDateInputValue(el, dateStr) {
        if (!el) return;
        const val = dateStr || '';
        el.value = val;
        if (el._flatpickr) {
            el._flatpickr.setDate(val || null, false);
        }
    }

    function setAddTaskDate(dateStr) {
        setDateInputValue(document.getElementById('add_task_date'), dateStr);
    }

    function openAddTaskForDate(dateStr) {
        setAddTaskDate(dateStr || '{{ today()->toDateString() }}');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addInquiryModal')).show();
    }

    function getAllSuggestions() {
        const list = [...assigneeSuggestions];
        const seen = new Set(list.map(item => normAr(item.name)));
        document.querySelectorAll('#sectionDetailed .task-assignee-name').forEach(inp => {
            const val = inp.value.trim();
            if (!val) return;
            const k = normAr(val);
            if (!seen.has(k)) {
                seen.add(k);
                list.push({ user_id: null, name: val, source: 'draft' });
            }
        });
        return list;
    }

    function findMatchingSuggestion(nameVal) {
        const clean = String(nameVal ?? '').trim().toLowerCase();
        if (!clean) return null;
        const all = getAllSuggestions();
        return all.find(s => s.name.trim().toLowerCase() === clean)
            || all.find(s => normAr(s.name) === normAr(clean))
            || null;
    }

    function applySuggestionToCombo(comboEl, item) {
        const nameInput = comboEl.querySelector('.task-assignee-name');
        const idInput   = comboEl.querySelector('.task-assignee-id');
        if (nameInput) nameInput.value = item.name;
        if (idInput) idInput.value = item.user_id || '';
        renderQuickChipsForCombo(comboEl);
    }

    function renderDropdownForCombo(comboEl, query) {
        const dropdown = comboEl.querySelector('.assignee-dropdown');
        if (!dropdown) return;
        const normQ = normAr(query);
        const all = getAllSuggestions();
        const filtered = normQ
            ? all.filter(s => normAr(s.name).includes(normQ))
            : all;

        if (filtered.length === 0) {
            dropdown.classList.remove('show');
            dropdown.innerHTML = '';
            return;
        }

        dropdown.innerHTML = '';
        filtered.slice(0, 15).forEach(item => {
            const div = document.createElement('div');
            div.className = 'assignee-dropdown-item';
            const tagLabel = item.source === 'user' ? 'حساب سيستم' : (item.source === 'hr' ? 'موظف' : (item.source === 'draft' ? 'مكتوب الآن' : 'سابق'));
            const tagClass = item.source === 'user' ? 'user' : '';
            div.innerHTML = `
                <span class="fw-semibold">${escHtml(item.name)}</span>
                <span class="assignee-source-tag ${tagClass}">${tagLabel}</span>
            `;
            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                applySuggestionToCombo(comboEl, item);
                dropdown.classList.remove('show');
            });
            dropdown.appendChild(div);
        });
        dropdown.classList.add('show');
    }

    function renderQuickChipsForCombo(comboEl) {
        const chipsBox  = comboEl.querySelector('.assignee-quick-chips');
        const nameInput = comboEl.querySelector('.task-assignee-name');
        if (!chipsBox || !nameInput) return;

        const currentNorm = normAr(nameInput.value);
        const all = getAllSuggestions().slice(0, 10);
        chipsBox.innerHTML = '';

        all.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'assignee-chip' + (currentNorm && normAr(item.name) === currentNorm ? ' selected' : '');
            btn.textContent = item.name;
            btn.addEventListener('mousedown', function (e) {
                e.preventDefault();
                applySuggestionToCombo(comboEl, item);
                const dd = comboEl.querySelector('.assignee-dropdown');
                if (dd) dd.classList.remove('show');
            });
            chipsBox.appendChild(btn);
        });
    }

    function refreshAllQuickChips() {
        document.querySelectorAll('.assignee-combo').forEach(combo => renderQuickChipsForCombo(combo));
    }

    function initAssigneeCombo(comboEl) {
        if (!comboEl || comboEl.dataset.initialized === '1') return;
        comboEl.dataset.initialized = '1';

        const nameInput = comboEl.querySelector('.task-assignee-name');
        const idInput   = comboEl.querySelector('.task-assignee-id');
        const toggleBtn = comboEl.querySelector('.btn-toggle-suggestions');
        const dropdown  = comboEl.querySelector('.assignee-dropdown');

        nameInput.addEventListener('focus', function () {
            renderDropdownForCombo(comboEl, '');
        });

        nameInput.addEventListener('input', function () {
            const match = findMatchingSuggestion(this.value);
            if (idInput) idInput.value = match && match.user_id ? match.user_id : '';
            renderDropdownForCombo(comboEl, this.value);
            renderQuickChipsForCombo(comboEl);
        });

        nameInput.addEventListener('blur', function () {
            setTimeout(() => {
                if (dropdown) dropdown.classList.remove('show');
                refreshAllQuickChips();
            }, 120);
        });

        if (toggleBtn) {
            toggleBtn.addEventListener('mousedown', function (e) {
                e.preventDefault();
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                } else {
                    renderDropdownForCombo(comboEl, '');
                    nameInput.focus();
                }
            });
        }

        renderQuickChipsForCombo(comboEl);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.assignee-combo').forEach(initAssigneeCombo);

        const addModalEl = document.getElementById('addInquiryModal');
        if (addModalEl) {
            addModalEl.addEventListener('shown.bs.modal', function () {
                refreshAllQuickChips();
                if (currentTaskEntryMode === 'quick') {
                    updateQuickTasksStats();
                    const firstText = document.querySelector('#quickBlocksContainer .quick-block-text');
                    if (firstText) firstText.focus();
                } else {
                    document.querySelector('#taskRowsContainer .task-details-input')?.focus();
                }
            });
        }

        const justSubmittedNewInquiry = sessionStorage.getItem('newInquiryNotify') === '1';
        sessionStorage.removeItem('newInquiryNotify');
        if (justSubmittedNewInquiry && window.__lastActionSavedSuccessfully) {
            playInquiryAlertSound();
        }
    });

    function switchPendingView(mode) {
        const groupedEl = document.getElementById('pendingGroupedContainer');
        const listEl    = document.getElementById('pendingListContainer');
        const btnG      = document.getElementById('btnViewGrouped');
        const btnL      = document.getElementById('btnViewList');
        if (!groupedEl || !listEl) return;

        if (mode === 'list') {
            groupedEl.style.display = 'none';
            listEl.style.display    = 'block';
            btnG.classList.remove('active');
            btnG.classList.add('btn-pro-outline');
            btnL.classList.add('active');
            btnL.classList.remove('btn-pro-outline');
        } else {
            listEl.style.display    = 'none';
            groupedEl.style.display = 'block';
            btnL.classList.remove('active');
            btnL.classList.add('btn-pro-outline');
            btnG.classList.add('active');
            btnG.classList.remove('btn-pro-outline');
        }
    }

    let quickBlockCounter = 0;

    function addQuickBlock(copyLastAssignee) {
        let preName = '', preId = '';
        if (copyLastAssignee) {
            const blocks = document.querySelectorAll('#quickBlocksContainer .quick-block-card');
            const lastBlock = blocks[blocks.length - 1];
            if (lastBlock) {
                preName = lastBlock.querySelector('.task-assignee-name')?.value || '';
                preId   = lastBlock.querySelector('.task-assignee-id')?.value || '';
            }
        }

        quickBlockCounter++;
        const idx = quickBlockCounter;
        const container = document.getElementById('quickBlocksContainer');
        const newBlock = document.createElement('div');
        newBlock.className = 'quick-block-card mb-3 p-3';
        newBlock.id = `quickBlock_${idx}`;
        newBlock.style.cssText = 'background: var(--c-surface); border: 1.5px solid var(--c-border-2); border-radius: var(--r-md); position: relative;';

        newBlock.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed var(--c-border);">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold quick-block-badge">
                    <i class="fa fa-user-check me-1"></i> موظف #${idx + 1}
                </span>
                <button type="button" class="btn btn-sm text-danger btn-remove-quick-block" onclick="removeQuickBlock(${idx})" style="border: none; background: transparent;" title="حذف هذا الموظف">
                    <i class="fa fa-trash-can me-1"></i> حذف
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12 mb-2">
                    <label class="form-pro-label fw-bold small">
                        <i class="fa fa-user-check me-1 text-primary"></i> المهمة لمين؟ <span style="color:var(--c-danger);">*</span>
                        <small class="text-muted fw-normal">(اختر من الأسماء أو اكتب أي اسم)</small>
                    </label>
                    <div class="assignee-combo">
                        <input type="hidden" name="quick_blocks[${idx}][assigned_to]" class="task-assignee-id" value="${escHtml(preId)}">
                        <div class="input-group">
                            <input type="text" name="quick_blocks[${idx}][assigned_name]" class="form-control form-pro-control task-assignee-name" placeholder="اكتب اسم الموظف..." autocomplete="off" value="${escHtml(preName)}" required>
                            <button type="button" class="btn btn-outline-secondary btn-toggle-suggestions" tabindex="-1">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="assignee-dropdown"></div>
                        <div class="assignee-quick-chips"></div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-pro-label fw-bold small">
                        <i class="fa fa-list-check me-1 text-primary"></i> قائمة المهام لهذا الموظف <span style="color:var(--c-danger);">*</span>
                        <small class="text-muted fw-normal">(اكتب مهمة واحدة أو عدة مهام — سطر لكل مهمة)</small>
                    </label>
                    <textarea name="quick_blocks[${idx}][text]" class="form-control form-pro-control quick-block-text" rows="3" placeholder="اكتب المهمة هنا... (سطر لكل مهمة)" required oninput="updateQuickTasksStats()"></textarea>
                </div>
            </div>
        `;
        container.appendChild(newBlock);

        initAssigneeCombo(newBlock.querySelector('.assignee-combo'));
        updateQuickTasksStats();

        if (copyLastAssignee && preName) {
            newBlock.querySelector('.quick-block-text')?.focus();
        } else {
            newBlock.querySelector('.task-assignee-name')?.focus();
        }
    }

    function removeQuickBlock(idx) {
        const block = document.getElementById(`quickBlock_${idx}`);
        if (block) {
            block.remove();
            updateQuickTasksStats();
            refreshAllQuickChips();
        }
    }

    function updateQuickTasksStats() {
        const blocks = document.querySelectorAll('#quickBlocksContainer .quick-block-card');
        const removeBtns = document.querySelectorAll('.btn-remove-quick-block');
        removeBtns.forEach(btn => {
            btn.style.display = blocks.length > 1 ? 'inline-block' : 'none';
        });

        blocks.forEach((b, i) => {
            const badge = b.querySelector('.quick-block-badge');
            if (badge) {
                badge.innerHTML = `<i class="fa fa-user-check me-1"></i> موظف #${i + 1}`;
            }
        });

        let totalTasks = 0;
        blocks.forEach(b => {
            const txt = b.querySelector('.quick-block-text')?.value || '';
            const lines = txt.split('\n').map(l => l.trim()).filter(Boolean);
            totalTasks += lines.length;
        });

        const statsEl = document.getElementById('quickTasksStatsText');
        if (statsEl) {
            const tText = totalTasks === 1 ? 'مهمة واحدة' : (totalTasks === 2 ? 'مهمتان' : `${totalTasks} مهام`);
            const eText = blocks.length === 1 ? 'موظف واحد' : `${blocks.length} موظفين`;
            statsEl.textContent = `إجمالي المهام: ${tText} لـ ${eText}`;
        }

        if (currentTaskEntryMode === 'quick') {
            updateSubmitButtonLabel(totalTasks);
        }
    }

    function switchTaskEntryMode(mode) {
        currentTaskEntryMode = mode;
        const btnDetailed = document.getElementById('btnModeDetailed');
        const btnQuick    = document.getElementById('btnModeQuick');
        const secDetailed = document.getElementById('sectionDetailed');
        const secQuick    = document.getElementById('sectionQuick');

        if (mode === 'quick') {
            btnDetailed.classList.remove('active');
            btnDetailed.classList.add('btn-pro-outline');
            btnQuick.classList.add('active');
            btnQuick.classList.remove('btn-pro-outline');

            secDetailed.style.display = 'none';
            secQuick.style.display = 'block';

            secDetailed.querySelectorAll('input, textarea, select').forEach(el => { el.disabled = true; });
            secQuick.querySelectorAll('input, textarea, select').forEach(el => { el.disabled = false; });
            updateQuickTasksStats();
            refreshAllQuickChips();
            const firstQuickText = secQuick.querySelector('.quick-block-text');
            if (firstQuickText) firstQuickText.focus();
        } else {
            btnQuick.classList.remove('active');
            btnQuick.classList.add('btn-pro-outline');
            btnDetailed.classList.add('active');
            btnDetailed.classList.remove('btn-pro-outline');

            secQuick.style.display = 'none';
            secDetailed.style.display = 'block';

            secDetailed.querySelectorAll('input, textarea, select').forEach(el => { el.disabled = false; });
            secQuick.querySelectorAll('input, textarea, select').forEach(el => { el.disabled = true; });
            updateDetailedTasksStats();
            refreshAllQuickChips();
            const firstDetails = secDetailed.querySelector('.task-details-input');
            if (firstDetails) firstDetails.focus();
        }
    }

    function addTaskRow(copyLastAssignee) {
        let preName = '', preId = '';
        if (copyLastAssignee) {
            const cards = document.querySelectorAll('#taskRowsContainer .task-entry-card');
            const lastCard = cards[cards.length - 1];
            if (lastCard) {
                preName = lastCard.querySelector('.task-assignee-name')?.value || '';
                preId   = lastCard.querySelector('.task-assignee-id')?.value || '';
            }
        }

        taskRowCounter++;
        const idx = taskRowCounter;
        const container = document.getElementById('taskRowsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'task-entry-card mb-3 p-3';
        newRow.id = `taskRow_${idx}`;
        newRow.style.cssText = 'background: var(--c-surface); border: 1px solid var(--c-border-2); border-radius: var(--r-md); position: relative;';

        newRow.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed var(--c-border);">
                <span class="badge bg-secondary task-index-badge"><i class="fa fa-thumbtack me-1"></i> المهمة رقم </span>
                <button type="button" class="btn btn-sm text-danger btn-remove-task" onclick="removeTaskRow(${idx})" style="border: none; background: transparent;" title="حذف هذه المهمة">
                    <i class="fa fa-trash-can me-1"></i> حذف المهمة
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-pro-label fw-bold">
                        <i class="fa fa-pen-to-square me-1 text-primary"></i> المهمة <span style="color:var(--c-danger);">*</span>
                    </label>
                    <textarea name="tasks[${idx}][details]" class="form-control form-pro-control task-details-input" rows="2" placeholder="اكتب المهمة المطلوب تنفيذها..." required onkeydown="onTaskDetailsKeyDown(event)"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-pro-label fw-bold">
                        <i class="fa fa-user-check me-1 text-primary"></i> المهمة لمين؟ <span style="color:var(--c-danger);">*</span>
                        <small class="text-muted fw-normal">(اكتب أي اسم أو اختر)</small>
                    </label>
                    <div class="assignee-combo">
                        <input type="hidden" name="tasks[${idx}][assigned_to]" class="task-assignee-id" value="${escHtml(preId)}">
                        <div class="input-group">
                            <input type="text" name="tasks[${idx}][assigned_name]" class="form-control form-pro-control task-assignee-name" placeholder="اكتب اسم الموظف..." autocomplete="off" value="${escHtml(preName)}" required>
                            <button type="button" class="btn btn-outline-secondary btn-toggle-suggestions" tabindex="-1" title="اختيار من القائمة">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="assignee-dropdown"></div>
                        <div class="assignee-quick-chips"></div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newRow);

        initAssigneeCombo(newRow.querySelector('.assignee-combo'));
        updateDetailedTasksStats();

        const detailsInput = newRow.querySelector('.task-details-input');
        if (detailsInput) detailsInput.focus();
    }

    function removeTaskRow(idx) {
        const row = document.getElementById(`taskRow_${idx}`);
        if (row) {
            row.remove();
            updateDetailedTasksStats();
            refreshAllQuickChips();
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

    function onTaskDetailsKeyDown(event) {
        if (event.key === 'Enter' && event.ctrlKey) {
            event.preventDefault();
            addTaskRow(false);
        }
    }

    function playInquiryAlertSound() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const notes = [880, 660, 880, 660, 880, 660];
            const noteDuration = 0.32;
            const gap = 0.12;
            let t = ctx.currentTime + 0.05;

            notes.forEach((freq) => {
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, t);
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

            setTimeout(() => { try { ctx.close(); } catch (e) {} }, (t + 0.5) * 1000);
        } catch (e) {
            console.warn('تعذر تشغيل صوت التنبيه:', e);
        }
    }

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
            title: 'إرجاع المهمة لمهام اليوم؟',
            text: 'هيتم إرجاعها لقائمة مهام اليوم ومسح ملاحظات الإنجاز.',
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
        document.getElementById('edit_inquiry').value = data.details || '';
        document.getElementById('edit_assigned_to').value = data.assigned_to || '';
        document.getElementById('edit_assigned_name').value = data.assigned_to_name || '';
        setDateInputValue(document.getElementById('edit_task_date'), data.task_date || '');
        document.getElementById('edit_contact_notes').value = data.contact_notes || '';

        const titleGroup = document.getElementById('editTitleGroup');
        const titleInput = document.getElementById('edit_title');
        const hasLegacyTitle = Boolean(data.title && String(data.title).trim() !== '' && String(data.title).trim() !== String(data.details || '').trim());
        titleInput.value = hasLegacyTitle ? data.title : '';
        titleGroup.style.display = hasLegacyTitle ? 'block' : 'none';

        refreshAllQuickChips();
        new bootstrap.Modal(document.getElementById('editInquiryModal')).show();
    }

    function appendBulkEditRow(itemData) {
        const container = document.getElementById('bulkEditRowsContainer');
        if (!container) return;
        const idx = bulkEditRowCounter++;
        const isExisting = Boolean(itemData && itemData.id);

        const rowEl = document.createElement('div');
        rowEl.className = 'p-3 border rounded-3 bulk-edit-row';
        rowEl.style.cssText = 'background: var(--c-surface); border-color: var(--c-border-2) !important;';
        rowEl.dataset.deleted = '0';

        rowEl.innerHTML = `
            <input type="hidden" name="items[${idx}][id]" value="${isExisting ? escHtml(itemData.id) : ''}">
            <input type="hidden" name="items[${idx}][title]" value="${itemData && itemData.title ? escHtml(itemData.title) : ''}">
            <input type="hidden" name="items[${idx}][deleted]" class="bulk-row-deleted-input" value="0">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-secondary bulk-row-num">مهمة</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 bulk-row-del-btn" onclick="toggleBulkEditRowDelete(this)">
                    <i class="fa fa-trash me-1"></i> حذف
                </button>
            </div>
            <textarea name="items[${idx}][details]" class="form-control form-pro-control bulk-row-textarea" rows="2" placeholder="اكتب نص المهمة..." required>${escHtml(itemData ? itemData.details : '')}</textarea>
        `;
        container.appendChild(rowEl);
        renumberBulkEditRows();
    }

    function toggleBulkEditRowDelete(btn) {
        const rowEl = btn.closest('.bulk-edit-row');
        if (!rowEl) return;
        const idInput = rowEl.querySelector('input[name$="[id]"]');
        if (!idInput || !idInput.value) {
            rowEl.remove();
            renumberBulkEditRows();
            return;
        }
        const delInput = rowEl.querySelector('.bulk-row-deleted-input');
        const textarea = rowEl.querySelector('.bulk-row-textarea');
        const isDel = delInput.value === '1';
        if (isDel) {
            delInput.value = '0';
            rowEl.style.opacity = '1';
            textarea.disabled = false;
            textarea.required = true;
            btn.className = 'btn btn-sm btn-outline-danger py-0 px-2 bulk-row-del-btn';
            btn.innerHTML = '<i class="fa fa-trash me-1"></i> حذف';
        } else {
            delInput.value = '1';
            rowEl.style.opacity = '0.45';
            textarea.disabled = true;
            textarea.required = false;
            btn.className = 'btn btn-sm btn-outline-secondary py-0 px-2 bulk-row-del-btn';
            btn.innerHTML = '<i class="fa fa-rotate-left me-1"></i> تراجع عن الحذف';
        }
        renumberBulkEditRows();
    }

    function renumberBulkEditRows() {
        const rows = document.querySelectorAll('#bulkEditRowsContainer .bulk-edit-row');
        let n = 1;
        rows.forEach(r => {
            const badge = r.querySelector('.bulk-row-num');
            const isDel = r.querySelector('.bulk-row-deleted-input')?.value === '1';
            if (badge) {
                badge.textContent = isDel ? 'سيتم حذفها' : `مهمة ${n++}`;
                badge.className = isDel ? 'badge bg-danger bulk-row-num' : 'badge bg-secondary bulk-row-num';
            }
        });
    }

    function addBulkEditNewRow() {
        appendBulkEditRow(null);
        const textareas = document.querySelectorAll('#bulkEditRowsContainer .bulk-row-textarea:not(:disabled)');
        textareas[textareas.length - 1]?.focus();
    }

    async function openBulkEditGroup(dateStr, groupKey) {
        await refreshPosterData(true);
        const day = pendingDaysData.find(d => d.date === dateStr);
        if (!day) return;
        const group = day.assignees.find(g => g.key === groupKey);
        if (!group) return;

        document.getElementById('bulkEditHeaderEmployee').textContent = group.assignee_name || '';
        document.getElementById('bulkEditHeaderDate').textContent = day.date_label || dateStr;
        document.getElementById('bulk_edit_assigned_to').value = group.assignee_id || '';
        document.getElementById('bulk_edit_assigned_name').value = group.assignee_name || '';
        setDateInputValue(document.getElementById('bulk_edit_task_date'), day.date || '');

        const origHost = document.getElementById('bulkEditOriginalIdsHost');
        const rowsHost = document.getElementById('bulkEditRowsContainer');
        origHost.innerHTML = '';
        rowsHost.innerHTML = '';
        bulkEditRowCounter = 0;

        (group.tasks || []).forEach(t => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'original_ids[]';
            hidden.value = t.id;
            origHost.appendChild(hidden);
            appendBulkEditRow(t);
        });

        refreshAllQuickChips();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkEditTasksModal')).show();
    }

    // ═══ أدوات مشتركة لتنزيل الصور والمشاركة المباشرة على واتساب ═══
    const DEFAULT_TASKS_WA_PHONE = '01094494782';

    function waNormalizePhone(raw) {
        let digits = String(raw || '').replace(/[^\d]/g, '');
        if (!digits) return '';
        if (digits.startsWith('00')) digits = digits.slice(2);
        if (digits.startsWith('01') && digits.length === 11) {
            digits = '2' + digits;
        } else if (digits.startsWith('1') && digits.length === 10) {
            digits = '20' + digits;
        }
        return digits.length >= 10 ? digits : '';
    }

    function updatePosterDirectChatLink(rawPhone) {
        const link = document.getElementById('posterDirectChatLink');
        if (!link) return;
        const clean = waNormalizePhone(rawPhone || DEFAULT_TASKS_WA_PHONE) || waNormalizePhone(DEFAULT_TASKS_WA_PHONE);
        link.href = `https://wa.me/${clean}`;
    }

    function sanitizeFilenamePart(str) {
        return String(str || '').trim().replace(/[\\/:*?"<>|\s]+/g, '_').replace(/^_+|_+$/g, '').slice(0, 40);
    }

    function triggerBlobDownload(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2500);
    }

    async function captureHtmlToBlob(htmlContent, widthPx, mimeType = 'image/png', quality = 0.95) {
        const box = document.createElement('div');
        box.style.cssText = `position:fixed;left:-9999px;top:0;width:${widthPx}px;background:#ffffff;direction:rtl;font-family:"IBM Plex Sans Arabic",Tahoma,Arial,sans-serif;letter-spacing:normal;word-spacing:normal;`;
        box.innerHTML = htmlContent;
        document.body.appendChild(box);
        try {
            if (document.fonts && document.fonts.ready) await document.fonts.ready;
            const canvas = await html2canvas(box, {
                scale: 2,
                backgroundColor: '#ffffff',
                useCORS: true,
                logging: false,
                onclone: function (doc) {
                    const st = doc.createElement('style');
                    st.textContent = '* { letter-spacing: 0 !important; word-spacing: normal !important; text-rendering: auto !important; }';
                    doc.head.appendChild(st);
                }
            });
            const blob = await new Promise(resolve => canvas.toBlob(resolve, mimeType, quality));
            if (!blob) throw new Error('تعذر توليد ملف الصورة');
            return blob;
        } finally {
            box.remove();
        }
    }

    async function captureHtmlToPngBlob(htmlContent, widthPx) {
        return captureHtmlToBlob(htmlContent, widthPx, 'image/png');
    }

    async function shareImageToWhatsApp({ blob, filename, title, caption, phone }) {
        const cleanPhone = waNormalizePhone(phone || DEFAULT_TASKS_WA_PHONE) || waNormalizePhone(DEFAULT_TASKS_WA_PHONE);
        const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        const fileType = blob.type || 'image/jpeg';
        const shareFilename = fileType === 'image/jpeg'
            ? filename.replace(/\.png$/i, '.jpg')
            : filename;
        const file = new File([blob], shareFilename, { type: fileType });

        // 1) Web Share API (يعمل على ويندوز ديسكتوب وعلى الجوال — يفتح قائمة المشاركة ثم واتساب والصورة مرفقة جاهزة بدون كوبي بيست)
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            try {
                await navigator.share({
                    files: [file],
                    title: title || 'مهام اليوم',
                    text: caption || ''
                });
                return;
            } catch (shareErr) {
                if (shareErr && shareErr.name === 'AbortError') return;
                if (shareErr && shareErr.name === 'NotAllowedError') {
                    Swal.fire({
                        icon: 'success',
                        title: 'الصورة جاهزة للإرسال',
                        text: 'اضغط على الزر بالأسفل لاختيار واتساب وإرسال الصورة مباشرة.',
                        confirmButtonText: '<i class="fa-brands fa-whatsapp me-1"></i> إرسال لواتساب الآن',
                        confirmButtonColor: '#25d366',
                        showCancelButton: true,
                        cancelButtonText: 'إغلاق'
                    }).then(r => {
                        if (r.isConfirmed) {
                            navigator.share({
                                files: [file],
                                title: title || 'مهام اليوم',
                                text: caption || ''
                            }).catch(() => {});
                        }
                    });
                    return;
                }
            }
        }

        // 2) بديل للمتصفحات التي لا تدعم المشاركة المباشرة للملفات: فتح شات الرقم 01094494782 مباشرة
        const waWebUrl = `https://web.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(caption || '')}`;
        const waAppUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(caption || '')}`;
        const primaryUrl = isMobile ? waAppUrl : waWebUrl;

        triggerBlobDownload(blob, shareFilename);
        const waWin = window.open(primaryUrl, '_blank');
        if (!waWin) {
            window.location.href = primaryUrl;
        }
    }

    // ═══ صورة المهمة المفردة (تنزيل + واتساب) ═══
    function buildSingleTaskHtml(t) {
        const hasTitle = Boolean(t.title && String(t.title).trim() !== '' && String(t.title).trim() !== String(t.details || '').trim());
        const empName = escHtml(t.assigned_to_name || 'غير محدد');
        const initial = escHtml((t.assigned_to_name || 'م').trim().charAt(0));
        const dateText = escHtml(t.task_date || t.created_at || '');
        return `
            <div style="background:#ffffff;border:2.5px solid #0f2a47;border-radius:16px;overflow:hidden;direction:rtl;font-family:'IBM Plex Sans Arabic',Tahoma,Arial,sans-serif;letter-spacing:normal;word-spacing:normal;">
                <div style="background:linear-gradient(135deg,#0f2a47 0%,#1a3a5f 100%);color:#fff;padding:15px 20px;border-bottom:4px solid #0d7373;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                    <div style="font-size:19px;font-weight:800;">مهام العمل — شركة الضبع</div>
                    ${dateText ? `<div style="background:rgba(255,255,255,0.16);color:#fff;font-size:13px;font-weight:600;padding:3px 12px;border-radius:999px;">${dateText}</div>` : ''}
                </div>
                <div style="padding:18px 20px;background:#f8fafc;">
                    <div style="border:1.5px solid #cbd5e1;border-right:5px solid #0f766e;border-radius:13px;overflow:hidden;background:#ffffff;">
                        <div style="background:#f1f5f9;padding:11px 16px;display:flex;align-items:center;gap:11px;border-bottom:1px solid #cbd5e1;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#0f2a47;color:#fff;font-size:16.5px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${initial}</div>
                            <div style="font-size:20px;font-weight:800;color:#0f2a47;font-family:'IBM Plex Sans Arabic',Tahoma,Arial,sans-serif;line-height:1.3;">${empName}</div>
                        </div>
                        <div style="padding:13px 14px;">
                            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:#ffffff;border-radius:10px;border:1px solid #e2e8f0;">
                                <div style="width:28px;height:28px;border-radius:50%;background:#0f766e;color:#fff;font-size:13.5px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">1</div>
                                <div style="flex:1;min-width:0;">
                                    ${hasTitle ? `<div style="font-size:14.5px;font-weight:700;color:#0f2a47;margin-bottom:4px;">${escHtml(t.title)}</div>` : ''}
                                    <div style="font-size:16.5px;line-height:1.7;color:#0f172a;white-space:pre-wrap;word-break:break-word;font-weight:500;">${escHtml(t.details)}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    async function downloadTaskImage(t) {
        try {
            const blob = await captureHtmlToPngBlob(buildSingleTaskHtml(t), 580);
            triggerBlobDownload(blob, `task-${t.id}.png`);
        } catch (e) {
            if (e.name !== 'AbortError') Swal.fire({ icon: 'error', title: 'تعذر إنشاء الصورة' });
        }
    }

    async function shareTaskImageWhatsApp(t) {
        Swal.fire({ title: 'جاري تجهيز صورة المهمة...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        try {
            const blob = await captureHtmlToBlob(buildSingleTaskHtml(t), 580, 'image/jpeg', 0.95);
            Swal.close();
            const empName = t.assigned_to_name || '';
            const safeEmp = sanitizeFilenamePart(empName);
            const filename = safeEmp ? `مهمة-${safeEmp}-${t.id}.jpg` : `task-${t.id}.jpg`;
            const caption = empName ? `مهمة ${empName}` : 'مهمة عمل';
            await shareImageToWhatsApp({
                blob,
                filename,
                title: caption,
                caption,
                phone: t.assigned_phone || DEFAULT_TASKS_WA_PHONE
            });
        } catch (e) {
            Swal.close();
            if (e.name !== 'AbortError') Swal.fire({ icon: 'error', title: 'تعذر تجهيز الصورة لواتساب' });
        }
    }

    // ═══ صورة اليوم المجمّعة (كل موظفي يوم محدد معاً في صورة واحدة مستقلة) ═══
    function getActiveDayObj(overrideDate) {
        if (pendingDaysData.length === 0) return null;
        const targetDate = overrideDate || activePosterDate || pendingDaysData[0].date;
        return pendingDaysData.find(d => d.date === targetDate) || pendingDaysData[0];
    }

    function selectPosterDay(dateStr) {
        activePosterDate = dateStr;
        const day = getActiveDayObj(dateStr);
        selectedPosterKeys = new Set(day ? day.assignees.map(a => a.key) : []);
        const phoneInput = document.getElementById('posterWaPhone');
        if (phoneInput && phoneInput.dataset.userEdited !== '1') {
            phoneInput.value = DEFAULT_TASKS_WA_PHONE;
            updatePosterDirectChatLink(phoneInput.value);
        }
        renderTasksPosterPreview();
    }

    function getFilteredDayAssignees(day) {
        if (!day) return [];
        return day.assignees.filter(g => selectedPosterKeys.has(g.key));
    }

    function syncPosterPhoneInput(assignees) {
        const phoneInput = document.getElementById('posterWaPhone');
        if (!phoneInput) return;
        if (phoneInput.dataset.userEdited !== '1') {
            phoneInput.value = (assignees.length === 1 && assignees[0].assignee_phone)
                ? assignees[0].assignee_phone
                : DEFAULT_TASKS_WA_PHONE;
        }
        updatePosterDirectChatLink(phoneInput.value);
    }

    function buildTasksPosterHtml(day, assignees) {
        const totalTasks = assignees.reduce((acc, g) => acc + g.tasks.length, 0);
        const palette = [
            { main: '#0f766e', dark: '#0f2a47', light: '#f1f5f9', border: '#cbd5e1' }, // Navy & Teal
            { main: '#0284c7', dark: '#0c4a6e', light: '#f0f9ff', border: '#bae6fd' }, // Sky Blue
            { main: '#059669', dark: '#065f46', light: '#f0fdf4', border: '#bbf7d0' }, // Emerald
            { main: '#d97706', dark: '#92400e', light: '#fffbeb', border: '#fde68a' }, // Amber
            { main: '#7c3aed', dark: '#5b21b6', light: '#faf5ff', border: '#ddd6fe' }, // Purple
            { main: '#e11d48', dark: '#9f1239', light: '#fff1f2', border: '#fecdd3' }, // Rose
            { main: '#4f46e5', dark: '#3730a3', light: '#eef2ff', border: '#c7d2fe' }, // Indigo
            { main: '#0891b2', dark: '#155e75', light: '#ecfeff', border: '#a5f3fc' }, // Cyan
        ];

        let sectionsHtml = '';
        assignees.forEach((g, idx) => {
            const theme = palette[idx % palette.length];
            const initial = escHtml((g.assignee_name || 'م').trim().charAt(0));
            const empName = escHtml(g.assignee_name);
            const countLabel = g.tasks.length === 1 ? 'مهمة واحدة' : (g.tasks.length === 2 ? 'مهمتان' : `${g.tasks.length} مهام`);

            let itemsHtml = '';
            g.tasks.forEach((t, tIdx) => {
                const hasTitle = Boolean(t.title && String(t.title).trim() !== '' && String(t.title).trim() !== String(t.details || '').trim());
                itemsHtml += `
                    <div style="display:flex;align-items:center;gap:12px;padding:11px 14px;background:${tIdx % 2 === 0 ? '#ffffff' : '#f8fafc'};border-radius:10px;border:1px solid #e2e8f0;margin-bottom:${tIdx === g.tasks.length - 1 ? '0' : '8px'};">
                        <div style="width:28px;height:28px;border-radius:50%;background:${theme.main};color:#fff;font-size:13.5px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${tIdx + 1}</div>
                        <div style="flex:1;min-width:0;">
                            ${hasTitle ? `<div style="font-size:14px;font-weight:700;color:${theme.dark};margin-bottom:3px;">${escHtml(t.title)}</div>` : ''}
                            <div style="font-size:16px;line-height:1.65;color:#0f172a;white-space:pre-wrap;word-break:break-word;font-weight:500;">${escHtml(t.details)}</div>
                        </div>
                    </div>
                `;
            });

            sectionsHtml += `
                <div style="border:1.5px solid #cbd5e1;border-right:5px solid ${theme.main};border-radius:13px;overflow:hidden;margin-bottom:${idx === assignees.length - 1 ? '0' : '14px'};background:#ffffff;box-shadow:0 2px 6px rgba(0,0,0,0.04);">
                    <div style="background:${theme.light};padding:11px 16px;display:flex;align-items:center;justify-content:space-between;gap:10px;border-bottom:1px solid ${theme.border};">
                        <div style="display:flex;align-items:center;gap:11px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:${theme.dark};color:#fff;font-size:16.5px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${initial}</div>
                            <div style="font-size:19.5px;font-weight:800;color:${theme.dark};font-family:'IBM Plex Sans Arabic',Tahoma,Arial,sans-serif;line-height:1.3;">${empName}</div>
                        </div>
                        <div style="background:${theme.main};color:#fff;font-size:12.5px;font-weight:700;padding:3px 12px;border-radius:999px;flex-shrink:0;">
                            ${countLabel}
                        </div>
                    </div>
                    <div style="padding:11px 13px;">
                        ${itemsHtml}
                    </div>
                </div>
            `;
        });

        if (assignees.length === 0) {
            sectionsHtml = `<div style="text-align:center;padding:32px;color:#64748b;font-size:16px;">اختر موظفاً واحداً على الأقل لعرض مهامه في الصورة</div>`;
        }

        const empCountText = assignees.length === 1 ? 'موظف واحد' : `${assignees.length} موظفين`;
        const taskCountText = totalTasks === 1 ? 'مهمة واحدة' : (totalTasks === 2 ? 'مهمتان' : `${totalTasks} مهام`);

        return `
            <div style="background:#ffffff;border:2.5px solid #0f2a47;border-radius:16px;overflow:hidden;direction:rtl;font-family:'IBM Plex Sans Arabic',Tahoma,Arial,sans-serif;letter-spacing:normal;word-spacing:normal;box-shadow:0 4px 18px rgba(15,42,71,0.12);">
                <div style="background:linear-gradient(135deg,#0f2a47 0%,#1a3a5f 100%);color:#ffffff;padding:16px 20px;border-bottom:4px solid #0d7373;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <div style="font-size:19.5px;font-weight:800;">مهام ${escHtml(day.date_label)}</div>
                            ${day.relative_tag ? `<span style="background:#0d9488;color:#fff;padding:2px 11px;border-radius:999px;font-size:12.5px;font-weight:700;">${escHtml(day.relative_tag)}</span>` : ''}
                        </div>
                        <div style="background:rgba(255,255,255,0.16);padding:4px 13px;border-radius:999px;font-size:13px;font-weight:600;">
                            ${empCountText} · ${taskCountText}
                        </div>
                    </div>
                </div>
                <div style="padding:16px 18px;background:#f8fafc;">
                    ${sectionsHtml}
                </div>
            </div>
        `;
    }

    function renderTasksPosterPreview() {
        const daysWrap = document.getElementById('posterDaysSelectorWrap');
        const daysEl   = document.getElementById('posterDaysSelector');
        const filterEl = document.getElementById('posterEmployeesFilter');
        const hostEl   = document.getElementById('tasksPosterPreviewHost');
        if (!filterEl || !hostEl) return;

        const activeDay = getActiveDayObj();
        if (!activeDay) return;

        if (daysWrap && daysEl) {
            if (pendingDaysData.length > 1) {
                daysWrap.style.display = 'block';
                daysEl.innerHTML = '';
                pendingDaysData.forEach(d => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'poster-day-chip' + (d.date === activeDay.date ? ' active' : '');
                    btn.innerHTML = `<span>${escHtml(d.date_label)}</span>${d.relative_tag ? `<span style="opacity:0.85;">(${escHtml(d.relative_tag)})</span>` : ''}`;
                    btn.addEventListener('click', () => selectPosterDay(d.date));
                    daysEl.appendChild(btn);
                });
            } else {
                daysWrap.style.display = 'none';
            }
        }

        filterEl.innerHTML = '';
        activeDay.assignees.forEach(g => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'poster-filter-chip' + (selectedPosterKeys.has(g.key) ? ' active' : '');
            chip.innerHTML = `<span>${escHtml(g.assignee_name)}</span> <span style="opacity:0.8;">(${g.tasks.length})</span>`;
            chip.addEventListener('click', function () {
                if (selectedPosterKeys.has(g.key)) {
                    selectedPosterKeys.delete(g.key);
                } else {
                    selectedPosterKeys.add(g.key);
                }
                renderTasksPosterPreview();
            });
            filterEl.appendChild(chip);
        });

        const filteredAssignees = getFilteredDayAssignees(activeDay);
        syncPosterPhoneInput(filteredAssignees);
        hostEl.innerHTML = buildTasksPosterHtml(activeDay, filteredAssignees);
    }

    async function refreshPosterData(preserveSelection = true, timeoutMs = 1500) {
        const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        const timer = controller ? setTimeout(() => controller.abort(), timeoutMs) : null;
        try {
            const params = new URLSearchParams(window.location.search);
            params.set('_t', String(Date.now()));
            const res = await fetch(`${posterDataEndpoint}?${params.toString()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
                signal: controller ? controller.signal : undefined
            });
            if (!res.ok) return pendingDaysData;
            const payload = await res.json();
            if (!payload || !Array.isArray(payload.days)) return pendingDaysData;

            const prevDate = activePosterDate;
            const prevKnownKeys = posterKnownKeysByDate.get(prevDate) || new Set();
            pendingDaysData = payload.days;

            if (pendingDaysData.length === 0) {
                activePosterDate = null;
                selectedPosterKeys = new Set();
                posterKnownKeysByDate.clear();
                return pendingDaysData;
            }

            const matchedDay = pendingDaysData.find(d => d.date === prevDate) || pendingDaysData[0];
            const newKeys = new Set(matchedDay.assignees.map(a => a.key));

            if (!preserveSelection || matchedDay.date !== prevDate) {
                activePosterDate = matchedDay.date;
                selectedPosterKeys = new Set(newKeys);
            } else {
                const nextSelected = new Set();
                matchedDay.assignees.forEach(a => {
                    if (selectedPosterKeys.has(a.key) || !prevKnownKeys.has(a.key)) {
                        nextSelected.add(a.key);
                    }
                });
                selectedPosterKeys = nextSelected;
            }

            posterKnownKeysByDate.clear();
            pendingDaysData.forEach(d => {
                posterKnownKeysByDate.set(d.date, new Set(d.assignees.map(a => a.key)));
            });

            if (document.getElementById('tasksPosterModal')?.classList.contains('show')) {
                renderTasksPosterPreview();
            }
            return pendingDaysData;
        } catch (e) {
            return pendingDaysData;
        } finally {
            if (timer) clearTimeout(timer);
        }
    }

    async function openTasksPosterModal(dateStr) {
        await refreshPosterData(false);
        if (pendingDaysData.length === 0) {
            Swal.fire({ icon: 'info', title: 'لا توجد مهام حالياً' });
            return;
        }
        const phoneInput = document.getElementById('posterWaPhone');
        if (phoneInput) {
            phoneInput.dataset.userEdited = '';
            phoneInput.value = DEFAULT_TASKS_WA_PHONE;
            updatePosterDirectChatLink(phoneInput.value);
        }
        selectPosterDay(dateStr || activePosterDate || pendingDaysData[0].date);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('tasksPosterModal')).show();
    }

    function resolvePosterSelection(overrideDate, overrideGroupKey) {
        const day = getActiveDayObj(overrideDate);
        if (!day) {
            Swal.fire({ icon: 'info', title: 'لا توجد مهام حالياً' });
            return null;
        }

        let assignees;
        if (overrideGroupKey) {
            assignees = day.assignees.filter(g => g.key === overrideGroupKey);
        } else if (overrideDate) {
            assignees = day.assignees;
        } else {
            assignees = getFilteredDayAssignees(day);
        }

        if (assignees.length === 0) {
            Swal.fire({ icon: 'warning', title: 'اختر موظفاً واحداً على الأقل' });
            return null;
        }

        let phone = DEFAULT_TASKS_WA_PHONE;
        if (overrideGroupKey) {
            phone = assignees[0]?.assignee_phone || DEFAULT_TASKS_WA_PHONE;
        } else if (!overrideDate) {
            const modalOpen = document.getElementById('tasksPosterModal')?.classList.contains('show');
            if (modalOpen) {
                phone = document.getElementById('posterWaPhone')?.value?.trim() || DEFAULT_TASKS_WA_PHONE;
            }
        }

        const singleName = assignees.length === 1 ? sanitizeFilenamePart(assignees[0].assignee_name) : '';
        const filename = singleName ? `مهام-${day.date}-${singleName}.jpg` : `مهام-${day.date}.jpg`;
        const pngFilename = singleName ? `tasks-${day.date}-${singleName}.png` : `tasks-${day.date}.png`;
        const caption = assignees.length === 1
            ? `مهام ${assignees[0].assignee_name} — ${day.date_label}`
            : `مهام ${day.date_label}`;

        return { day, assignees, phone, filename, pngFilename, caption };
    }

    async function downloadTasksPosterImage(overrideDate, overrideGroupKey) {
        await refreshPosterData(!overrideDate && !overrideGroupKey);
        const target = resolvePosterSelection(overrideDate, overrideGroupKey);
        if (!target) return;

        try {
            const blob = await captureHtmlToPngBlob(buildTasksPosterHtml(target.day, target.assignees), 680);
            triggerBlobDownload(blob, target.pngFilename);
        } catch (e) {
            if (e.name !== 'AbortError') {
                Swal.fire({ icon: 'error', title: 'تعذر إنشاء الصورة' });
            }
        }
    }

    async function shareTasksPosterWhatsApp(overrideDate, overrideGroupKey) {
        Swal.fire({ title: 'جاري تجهيز صورة المهام...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        await refreshPosterData(!overrideDate && !overrideGroupKey, 1200);
        const target = resolvePosterSelection(overrideDate, overrideGroupKey);
        if (!target) return;

        try {
            const blob = await captureHtmlToBlob(buildTasksPosterHtml(target.day, target.assignees), 680, 'image/jpeg', 0.95);
            Swal.close();
            await shareImageToWhatsApp({
                blob,
                filename: target.filename,
                title: target.caption,
                caption: target.caption,
                phone: target.phone
            });
        } catch (e) {
            Swal.close();
            if (e.name !== 'AbortError') {
                Swal.fire({ icon: 'error', title: 'تعذر تجهيز الصورة لواتساب' });
            }
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
