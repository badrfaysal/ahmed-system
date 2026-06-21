<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز رسائل العملاء - شركة الضبع</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('partials.theme', ['accent' => 'emerald'])

    <style>
        body { font-family: 'Cairo', sans-serif; }

        /* ─── Hero Header ─── */
        .wa-hero {
            background: linear-gradient(135deg, #075E54 0%, #128C7E 50%, #25D366 100%);
            border-radius: 24px;
            padding: 32px 36px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(37, 211, 102, 0.25);
        }
        .wa-hero::before {
            content: "";
            position: absolute;
            inset: -50% -20% auto auto;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%);
            border-radius: 50%;
        }
        .wa-hero-icon {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
            width: 72px; height: 72px; border-radius: 20px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 2.4rem;
        }

        /* ─── Stat Pills ─── */
        .stat-pill {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 16px;
            padding: 16px 20px;
            transition: var(--t-fast, 0.2s);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .stat-pill::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--accent-color, #25D366);
        }
        .stat-pill:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .stat-pill.active {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.15);
        }
        .stat-pill .label { font-size: 0.78rem; color: var(--c-text-soft); font-weight: 600; }
        .stat-pill .value { font-size: 1.45rem; font-weight: 800; color: var(--c-text); }
        .stat-pill.warning::before { background: #f59e0b; }
        .stat-pill.danger::before  { background: #ef4444; }
        .stat-pill.success::before { background: #10b981; }
        .stat-pill.info::before    { background: #3b82f6; }
        .stat-pill.purple::before  { background: #8b5cf6; }

        /* ─── Filter Bar ─── */
        .filter-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 20px;
            padding: 22px 26px;
        }
        .chip {
            background: var(--c-surface);
            border: 1.5px solid var(--c-border);
            border-radius: 999px;
            padding: 7px 16px;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--c-text-soft);
            cursor: pointer;
            transition: var(--t-fast, 0.2s);
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .chip:hover { border-color: #128C7E; color: #128C7E; }
        .chip.active {
            background: linear-gradient(135deg, #128C7E, #25D366);
            border-color: #128C7E;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }
        .chip-count {
            background: rgba(255,255,255,0.25);
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 0.7rem;
        }
        .chip:not(.active) .chip-count {
            background: var(--c-border);
            color: var(--c-text);
        }

        /* ─── Message Composer ─── */
        .composer {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 20px;
            padding: 22px 26px;
        }
        .tpl-pill {
            background: var(--c-surface);
            border: 1.5px solid var(--c-border);
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 0.82rem;
            color: var(--c-text);
            cursor: pointer;
            transition: var(--t-fast, 0.2s);
            display: flex; align-items: center; gap: 8px;
        }
        .tpl-pill:hover { border-color: #128C7E; transform: translateY(-1px); }
        .tpl-pill.active { background: rgba(37, 211, 102, 0.1); border-color: #25D366; }
        .tpl-pill i { color: #25D366; }

        /* ─── Customer Cards Grid ─── */
        .customers-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 14px; }
        .cust-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 18px;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: var(--t-fast, 0.2s);
            position: relative;
            overflow: hidden;
        }
        .cust-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 5px;
            background: var(--c-border);
        }
        .cust-card.s-defaulter::before       { background: #ef4444; }
        .cust-card.s-on_installments::before { background: #f59e0b; }
        .cust-card.s-has_balance::before     { background: #3b82f6; }
        .cust-card.s-completed::before       { background: #10b981; }
        .cust-card.s-no_transactions::before { background: #94a3b8; }
        .cust-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .cust-card.checked {
            background: linear-gradient(135deg, rgba(37, 211, 102, 0.06), var(--c-surface));
            border-color: #25D366;
        }

        .cust-avatar {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, #128C7E, #25D366);
            color: white;
            border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.1rem;
        }
        .cust-name { font-weight: 800; color: var(--c-text); font-size: 1rem; line-height: 1.3; }
        .cust-phone { font-size: 0.82rem; color: var(--c-text-soft); direction: ltr; text-align: end; font-family: 'Courier New', monospace; }

        .badge-status {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 9px;
            border-radius: 999px;
        }
        .b-defaulter       { background: #fee2e2; color: #991b1b; }
        .b-on_installments { background: #fef3c7; color: #92400e; }
        .b-has_balance     { background: #dbeafe; color: #1e40af; }
        .b-completed       { background: #d1fae5; color: #065f46; }
        .b-no_transactions { background: #e2e8f0; color: #475569; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.78rem; }
        .info-grid > div { background: var(--c-bg, #f8fafc); padding: 6px 10px; border-radius: 8px; }
        .info-grid .lbl { color: var(--c-text-soft); font-weight: 600; }
        .info-grid .val { color: var(--c-text); font-weight: 800; display: block; }

        .btn-wa { background: linear-gradient(135deg, #128C7E, #25D366); color: white; border: none; border-radius: 12px; padding: 8px 14px; font-weight: 700; font-size: 0.85rem; }
        .btn-wa:hover { color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35); }

        /* sticky bulk action bar */
        .bulk-bar {
            position: sticky; bottom: 16px; z-index: 100;
            background: linear-gradient(135deg, #075E54, #128C7E);
            color: white;
            border-radius: 18px;
            padding: 14px 22px;
            margin-top: 16px;
            box-shadow: 0 12px 32px rgba(7, 94, 84, 0.4);
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .bulk-bar.show { display: flex; }

        .form-check-input.cust-cb { width: 22px; height: 22px; cursor: pointer; }
        .form-check-input.cust-cb:checked { background-color: #25D366; border-color: #25D366; }

        .empty {
            text-align: center; padding: 60px 20px;
            background: var(--c-surface);
            border: 2px dashed var(--c-border);
            border-radius: 20px;
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">

    {{-- ═══════════ Hero ═══════════ --}}
    <div class="wa-hero mb-4">
        <div class="d-flex align-items-center gap-3 position-relative" style="z-index:2;">
            <div class="wa-hero-icon"><i class="fa-brands fa-whatsapp"></i></div>
            <div>
                <h2 class="fw-bold mb-1">مركز رسائل العملاء</h2>
                <div class="opacity-90">إدارة التواصل وإرسال المطالبات والتذكيرات الإسلامية للعملاء عبر الواتساب</div>
            </div>
        </div>
    </div>

    {{-- ═══════════ Stats ═══════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <a href="?filter=all" class="text-decoration-none">
                <div class="stat-pill info {{ $filter=='all' ? 'active' : '' }}">
                    <div class="label">إجمالي العملاء</div>
                    <div class="value">{{ $stats['total'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <a href="?filter=defaulters" class="text-decoration-none">
                <div class="stat-pill danger {{ $filter=='defaulters' ? 'active' : '' }}">
                    <div class="label">متعثرين</div>
                    <div class="value">{{ $stats['defaulters'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <a href="?filter=on_installments" class="text-decoration-none">
                <div class="stat-pill warning {{ $filter=='on_installments' ? 'active' : '' }}">
                    <div class="label">عليهم أقساط</div>
                    <div class="value">{{ $stats['on_installments'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <a href="?filter=completed" class="text-decoration-none">
                <div class="stat-pill success {{ $filter=='completed' ? 'active' : '' }}">
                    <div class="label">سددوا الكل</div>
                    <div class="value">{{ $stats['completed'] }}</div>
                </div>
            </a>
        </div>
        <div class="col-lg-4 col-md-8 col-12">
            <div class="stat-pill purple">
                <div class="label">إجمالي المستحقات</div>
                <div class="value">{{ number_format($stats['total_due'], 0) }} <span style="font-size:1rem;">ج.م</span></div>
            </div>
        </div>
    </div>

    {{-- ═══════════ Filter Card ═══════════ --}}
    <form method="GET" id="filterForm" class="filter-card mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3" style="gap: 8px !important;">
            <span class="fw-bold text-muted small me-2"><i class="fa fa-filter me-1"></i>فلتر سريع:</span>

            @php
                $chips = [
                    'all'             => ['كل العملاء', 'fa-users', $stats['total']],
                    'defaulters'      => ['متعثرين', 'fa-triangle-exclamation', $stats['defaulters']],
                    'on_installments' => ['عليهم أقساط', 'fa-calendar-day', $stats['on_installments']],
                    'has_balance'     => ['عليهم مستحقات', 'fa-money-bill-trend-up', '—'],
                    'completed'       => ['خالصين الحساب', 'fa-circle-check', $stats['completed']],
                    'due_this_week'   => ['أقساط الأسبوع', 'fa-calendar-week', '—'],
                    'late_over_30'    => ['متأخر +30 يوم', 'fa-hourglass-half', '—'],
                    'late_over_60'    => ['متأخر +60 يوم', 'fa-skull-crossbones', '—'],
                    'no_transactions' => ['بدون معاملات', 'fa-user', '—'],
                ];
            @endphp

            @foreach($chips as $key => [$lbl, $ico, $cnt])
                <button type="button" class="chip {{ $filter === $key ? 'active' : '' }}"
                        onclick="setFilter('{{ $key }}')">
                    <i class="fa {{ $ico }}"></i> {{ $lbl }}
                    @if($cnt !== '—')
                        <span class="chip-count">{{ $cnt }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        <input type="hidden" name="filter" id="f_filter" value="{{ $filter }}">

        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">🔎 بحث بالاسم أو التليفون</label>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="أحمد، 010...">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">📅 يوم استحقاق محدد</label>
                <input type="number" name="due_day" value="{{ $dueDay }}" class="form-control text-center" min="1" max="28" placeholder="1-28" onchange="setFilter('due_day')">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">↕️ ترتيب القائمة</label>
                <select name="sort_by" class="form-select" onchange="this.form.submit()">
                    <option value="balance_desc"   {{ $sortBy=='balance_desc' ? 'selected' : '' }}>الأكبر مستحقاً أولاً</option>
                    <option value="balance_asc"    {{ $sortBy=='balance_asc'  ? 'selected' : '' }}>الأقل مستحقاً أولاً</option>
                    <option value="days_late_desc" {{ $sortBy=='days_late_desc'? 'selected' : '' }}>الأكثر تأخراً أولاً</option>
                    <option value="monthly_desc"   {{ $sortBy=='monthly_desc' ? 'selected' : '' }}>الأكبر قسطاً شهرياً</option>
                    <option value="name_asc"       {{ $sortBy=='name_asc'     ? 'selected' : '' }}>أبجدي بالاسم</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success w-100 fw-bold"><i class="fa fa-magnifying-glass me-1"></i>تطبيق الفلتر</button>
            </div>
        </div>
    </form>

    {{-- ═══════════ Message Composer ═══════════ --}}
    <div class="composer mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold mb-0" style="color: var(--c-text);">
                <i class="fa fa-comment-dots text-success me-1"></i> صياغة الرسالة
            </h5>
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllCustomers(true)">
                    <i class="fa fa-check-double me-1"></i>تحديد الكل
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllCustomers(false)">
                    <i class="fa fa-square me-1"></i>إلغاء التحديد
                </button>
            </div>
        </div>

        @php
            $templates = [
                'tazkir'   => ['🌙 وذكِّر', 'fa-mosque', "وذكِّر فإن الذكرى تنفع المؤمنين"],
                'reminder' => ['تذكير قسط', 'fa-bell', "السلام عليكم {اسم_العميل}،\nنود تذكيركم بأن لديكم قسطاً مستحقاً بقيمة {المبلغ_الشهري} جنيه بتاريخ يوم {يوم_الاستحقاق} من الشهر.\nشكراً لالتزامكم.\nشركة الضبع"],
                'overdue'  => ['مطالبة متأخرات', 'fa-triangle-exclamation', "أستاذ {اسم_العميل}،\nنود إفادتكم بوجود متأخرات على حضرتكم بقيمة {المبلغ} جنيه.\nنرجو السداد في أقرب وقت لتجنب أي إجراءات.\nشركة الضبع"],
                'thanks'   => ['شكر على السداد', 'fa-heart', "أستاذ {اسم_العميل}،\nشكراً لالتزامكم وحرصكم على السداد. نتمنى لكم التوفيق والسعادة.\nشركة الضبع 🌹"],
                'eid'      => ['تهنئة بعيد', 'fa-star', "أستاذ {اسم_العميل}،\nكل عام وحضرتكم وأسرتكم بألف خير.\nأعاد الله عليكم الأيام المباركة بالصحة والبركة.\nشركة الضبع 🌙"],
                'custom'   => ['رسالة مخصصة', 'fa-pen', ''],
            ];
        @endphp

        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($templates as $key => [$lbl, $ico, $body])
                <div class="tpl-pill {{ $key === 'tazkir' ? 'active' : '' }}"
                     data-tpl="{{ $key }}" data-body="{{ $body }}"
                     onclick="pickTemplate(this)">
                    <i class="fa {{ $ico }}"></i> {{ $lbl }}
                </div>
            @endforeach
        </div>

        <div>
            <label class="form-label small fw-bold text-muted mb-1">معاينة وتعديل قبل الإرسال</label>
            <textarea id="msg_body" class="form-control" rows="4" style="font-weight: 600; line-height: 1.8; border-color: var(--c-border);">وذكِّر فإن الذكرى تنفع المؤمنين</textarea>
            <small class="text-muted">يمكنك استخدام: <code>{اسم_العميل}</code> · <code>{المبلغ}</code> · <code>{المبلغ_الشهري}</code> · <code>{يوم_الاستحقاق}</code></small>
        </div>
    </div>

    {{-- ═══════════ Customers Grid ═══════════ --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold m-0" style="color: var(--c-text);">
            القائمة الحالية
            <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">{{ $stats['showing'] }} عميل</span>
        </h6>
        <span class="small text-muted">المحدد للإرسال: <b id="checkedCount" style="color: #25D366;">0</b></span>
    </div>

    @if($customers->count() > 0)
        <div class="customers-grid">
            @foreach($customers as $c)
                <label class="cust-card s-{{ $c->status }}" data-phone="{{ $c->phone }}" data-name="{{ $c->name }}"
                       data-amount="{{ $c->total_remaining }}" data-monthly="{{ $c->monthly_total }}"
                       data-due="{{ $c->due_days[0] ?? '' }}" data-days-late="{{ $c->days_late }}">

                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex gap-2 align-items-center" style="min-width: 0; flex: 1;">
                            <input type="checkbox" class="form-check-input cust-cb" onchange="updateChecked(this)">
                            <div class="cust-avatar">{{ mb_substr($c->name ?? '?', 0, 1) }}</div>
                            <div style="min-width: 0;">
                                <div class="cust-name text-truncate">{{ $c->name }}</div>
                                <div class="cust-phone">{{ $c->phone }}</div>
                            </div>
                        </div>
                        <span class="badge-status b-{{ $c->status }}">
                            @switch($c->status)
                                @case('defaulter')       متعثر @break
                                @case('on_installments') على أقساط @break
                                @case('has_balance')     عليه مستحقات @break
                                @case('completed')       خالص الحساب @break
                                @case('no_transactions') عميل @break
                            @endswitch
                        </span>
                    </div>

                    @if($c->status !== 'no_transactions')
                        <div class="info-grid">
                            <div>
                                <span class="lbl">المستحق</span>
                                <span class="val" style="color: {{ $c->total_remaining > 0 ? '#ef4444' : '#10b981' }};">
                                    {{ number_format($c->total_remaining, 0) }} ج
                                </span>
                            </div>
                            @if($c->monthly_total > 0)
                                <div>
                                    <span class="lbl">القسط الشهري</span>
                                    <span class="val">{{ number_format($c->monthly_total, 0) }} ج</span>
                                </div>
                            @endif
                            @if(!empty($c->due_days))
                                <div>
                                    <span class="lbl">يوم الاستحقاق</span>
                                    <span class="val">{{ implode(', ', $c->due_days) }} من الشهر</span>
                                </div>
                            @endif
                            @if($c->days_late > 0)
                                <div>
                                    <span class="lbl">متأخر</span>
                                    <span class="val" style="color: #ef4444;">{{ $c->days_late }} يوم</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-1">
                        <button type="button" class="btn btn-wa flex-grow-1" onclick="sendSingle(event, this.closest('.cust-card'))">
                            <i class="fa-brands fa-whatsapp me-1"></i> إرسال للعميل
                        </button>
                    </div>
                </label>
            @endforeach
        </div>
    @else
        <div class="empty">
            <i class="fa-brands fa-whatsapp fa-4x text-muted opacity-25 mb-3 d-block"></i>
            <h5 class="fw-bold text-muted">مفيش عملاء يطابقوا الفلتر الحالي</h5>
            <p class="text-secondary small mb-0">جرّب فلتر تاني أو ابحث باسم/تليفون مختلف</p>
        </div>
    @endif

    {{-- ═══════════ Bulk Action Bar (sticky) ═══════════ --}}
    <div class="bulk-bar" id="bulkBar">
        <div>
            <i class="fa fa-circle-check me-1"></i>
            تم تحديد <b id="bulkCount">0</b> عميل للإرسال
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light fw-bold" onclick="openBulkModal()">
                <i class="fa-brands fa-whatsapp me-1"></i> إرسال للمحددين
            </button>
            <button class="btn btn-outline-light fw-bold" onclick="selectAllCustomers(false)">
                إلغاء
            </button>
        </div>
    </div>
</div>

{{-- ═══════════ Bulk Send Modal ═══════════ --}}
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #075E54, #25D366); border: none;">
                <div>
                    <h5 class="modal-title fw-bold mb-1">
                        <i class="fa-brands fa-whatsapp me-2"></i> إرسال للمحددين
                    </h5>
                    <div class="small opacity-90">دوس على كل عميل وافتح الواتس بسرعة (الرسالة جاهزة)</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="px-4 py-3" style="background: #f0fdf4; border-bottom: 1px solid #d1fae5;">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <span class="fw-bold text-success">
                                <i class="fa fa-circle-info me-1"></i>
                                تم <b id="bulkSentCount">0</b> من أصل <b id="bulkTotalCount">0</b>
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success fw-bold" onclick="openNextPending()" id="btnOpenNext">
                                <i class="fa fa-forward me-1"></i> افتح التالي
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="markAllSent()">
                                <i class="fa fa-check-double me-1"></i> علم الكل كمُرسل
                            </button>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 8px; border-radius: 8px;">
                        <div class="progress-bar bg-success" id="bulkProgress" style="width: 0%"></div>
                    </div>
                </div>

                <div id="bulkList" style="max-height: 55vh; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<style>
    .bulk-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 20px; border-bottom: 1px solid var(--c-border);
        transition: var(--t-fast, 0.2s);
    }
    .bulk-item:hover { background: var(--c-bg, #f8fafc); }
    .bulk-item.done {
        background: linear-gradient(135deg, rgba(37,211,102,0.08), transparent);
        opacity: 0.7;
    }
    .bulk-item.done .bulk-name { text-decoration: line-through; }
    .bulk-item .num {
        background: var(--c-border); color: var(--c-text);
        width: 30px; height: 30px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
    }
    .bulk-item.done .num { background: #25D366; color: white; }
    .bulk-item .name-block { flex-grow: 1; min-width: 0; }
    .bulk-name { font-weight: 700; color: var(--c-text); }
    .bulk-phone { font-size: 0.78rem; color: var(--c-text-soft); direction: ltr; }
    .btn-open-wa {
        background: linear-gradient(135deg, #128C7E, #25D366);
        color: white; border: none; border-radius: 10px;
        padding: 6px 14px; font-weight: 700; font-size: 0.82rem;
        white-space: nowrap;
    }
    .btn-open-wa:hover { color: white; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(37,211,102,0.3); }
    .bulk-item.done .btn-open-wa { background: #94a3b8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const f = document.getElementById('filterForm');

    function setFilter(val) {
        document.getElementById('f_filter').value = val;
        f.submit();
    }

    // ── Template picker ──
    function pickTemplate(el) {
        document.querySelectorAll('.tpl-pill').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        const body = el.dataset.body || '';
        document.getElementById('msg_body').value = body;
    }

    // ── Replace placeholders ──
    function buildMessage(c) {
        const tpl = document.getElementById('msg_body').value || '';
        return tpl
            .replace(/{اسم_العميل}/g, c.name || '')
            .replace(/{المبلغ}/g, Number(c.amount || 0).toLocaleString('en-US'))
            .replace(/{المبلغ_الشهري}/g, Number(c.monthly || 0).toLocaleString('en-US'))
            .replace(/{يوم_الاستحقاق}/g, c.due || '—');
    }

    function normalizePhone(phone) {
        let p = (phone || '').replace(/[^0-9]/g, '');
        if (p.startsWith('00')) p = p.slice(2);
        if (p.startsWith('20'))  return p;
        if (p.startsWith('0'))   return '2' + p;
        return p;
    }

    function openWA(phone, msg) {
        const url = `https://wa.me/${normalizePhone(phone)}?text=${encodeURIComponent(msg)}`;
        window.open(url, '_blank');
    }

    function cardData(card) {
        return {
            phone:   card.dataset.phone,
            name:    card.dataset.name,
            amount:  card.dataset.amount,
            monthly: card.dataset.monthly,
            due:     card.dataset.due,
        };
    }

    // ── Single send ──
    function sendSingle(e, card) {
        e.preventDefault(); e.stopPropagation();
        const c = cardData(card);
        if (!c.phone || c.phone === '-') {
            Swal.fire('بدون رقم', 'العميل ده مفيش له رقم تليفون مسجّل.', 'warning');
            return;
        }
        const msg = buildMessage(c);
        if (!msg.trim()) {
            Swal.fire('رسالة فاضية', 'اكتب نص الرسالة الأول.', 'info');
            return;
        }
        openWA(c.phone, msg);
    }

    // ── Checkbox state ──
    function updateChecked(cb) {
        const card = cb.closest('.cust-card');
        card.classList.toggle('checked', cb.checked);
        refreshBulkBar();
    }

    function refreshBulkBar() {
        const checked = document.querySelectorAll('.cust-cb:checked').length;
        document.getElementById('checkedCount').textContent = checked;
        document.getElementById('bulkCount').textContent = checked;
        document.getElementById('bulkBar').classList.toggle('show', checked > 0);
    }

    function selectAllCustomers(state) {
        document.querySelectorAll('.cust-cb').forEach(cb => {
            cb.checked = state;
            cb.closest('.cust-card').classList.toggle('checked', state);
        });
        refreshBulkBar();
    }

    // ── Bulk send modal ──
    let bulkRecipients = [];

    function openBulkModal() {
        const cards = Array.from(document.querySelectorAll('.cust-cb:checked'))
            .map(cb => cb.closest('.cust-card'));
        if (cards.length === 0) return;

        const msg = document.getElementById('msg_body').value;
        if (!msg.trim()) {
            Swal.fire('رسالة فاضية', 'اكتب نص الرسالة الأول قبل الإرسال.', 'info');
            return;
        }

        // جهز قائمة المستقبلين
        bulkRecipients = cards.map((card, i) => ({
            ...cardData(card),
            sent: false,
            idx: i,
        })).filter(r => r.phone);

        renderBulkList();
        new bootstrap.Modal(document.getElementById('bulkModal')).show();
    }

    function renderBulkList() {
        const list = document.getElementById('bulkList');
        list.innerHTML = '';
        bulkRecipients.forEach((r, i) => {
            const div = document.createElement('div');
            div.className = 'bulk-item' + (r.sent ? ' done' : '');
            div.id = `bulk_${i}`;
            div.innerHTML = `
                <div class="num">${r.sent ? '<i class="fa fa-check"></i>' : (i + 1)}</div>
                <div class="name-block">
                    <div class="bulk-name">${escapeHtml(r.name || '')}</div>
                    <div class="bulk-phone">${escapeHtml(r.phone || '')}</div>
                </div>
                <button class="btn-open-wa" onclick="openBulkItem(${i})">
                    <i class="fa-brands fa-whatsapp me-1"></i>
                    ${r.sent ? 'فتح تاني' : 'افتح واتس'}
                </button>
            `;
            list.appendChild(div);
        });
        updateBulkProgress();
    }

    function openBulkItem(i) {
        const r = bulkRecipients[i];
        if (!r) return;
        const msg = buildMessage(r);
        openWA(r.phone, msg);
        r.sent = true;
        const el = document.getElementById(`bulk_${i}`);
        if (el) {
            el.classList.add('done');
            el.querySelector('.num').innerHTML = '<i class="fa fa-check"></i>';
            el.querySelector('.btn-open-wa').innerHTML = '<i class="fa-brands fa-whatsapp me-1"></i> فتح تاني';
        }
        updateBulkProgress();
        // auto-scroll للعميل التالي
        setTimeout(() => {
            const next = bulkRecipients.findIndex((x, k) => k > i && !x.sent);
            if (next >= 0) {
                document.getElementById(`bulk_${next}`)?.scrollIntoView({behavior:'smooth', block:'center'});
            }
        }, 100);
    }

    function openNextPending() {
        const next = bulkRecipients.findIndex(r => !r.sent);
        if (next < 0) {
            Swal.fire({ icon: 'success', title: 'خلصت!', text: 'كل العملاء اتبعتلهم.', timer: 2500, showConfirmButton: false });
            return;
        }
        openBulkItem(next);
    }

    function markAllSent() {
        bulkRecipients.forEach(r => r.sent = true);
        renderBulkList();
    }

    function updateBulkProgress() {
        const total = bulkRecipients.length;
        const done = bulkRecipients.filter(r => r.sent).length;
        document.getElementById('bulkTotalCount').textContent = total;
        document.getElementById('bulkSentCount').textContent = done;
        const pct = total ? Math.round((done / total) * 100) : 0;
        document.getElementById('bulkProgress').style.width = pct + '%';

        const btnNext = document.getElementById('btnOpenNext');
        if (done >= total) {
            btnNext.innerHTML = '<i class="fa fa-check me-1"></i> خلصنا';
            btnNext.classList.replace('btn-success', 'btn-outline-success');
        } else {
            btnNext.innerHTML = '<i class="fa fa-forward me-1"></i> افتح التالي';
            btnNext.classList.add('btn-success');
            btnNext.classList.remove('btn-outline-success');
        }
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // اختصارات كيبورد داخل الـ modal: Space أو Enter = افتح التالي
    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('bulkModal');
        if (!modal.classList.contains('show')) return;
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.code === 'Space' || e.code === 'Enter') {
            e.preventDefault();
            openNextPending();
        }
    });
</script>

</body>
</html>
