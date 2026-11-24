<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل التدقيق - شركة الضبع</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    @include('partials.theme', ['accent' => 'rose'])

    <style>
        .audit-quick-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }
        .audit-quick-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 999px;
            border: 1.5px solid var(--c-border-2);
            background: var(--c-surface);
            color: var(--c-text);
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--t-fast);
        }
        .audit-quick-chip:hover {
            border-color: var(--c-accent);
            color: var(--c-accent);
            background: var(--c-accent-bg);
        }
        .audit-quick-chip.active {
            background: var(--c-navy);
            border-color: var(--c-navy);
            color: #fff;
        }
        .audit-quick-chip.tasks-chip.active {
            background: #0d7373;
            border-color: #0d7373;
            color: #fff;
        }
        .audit-sub-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 999px;
            border: 1px solid var(--c-border-2);
            background: var(--c-bg);
            color: var(--c-text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--t-fast);
        }
        .audit-sub-chip:hover {
            border-color: var(--c-navy);
            color: var(--c-navy);
        }
        .audit-sub-chip.active {
            background: var(--c-accent);
            border-color: var(--c-accent);
            color: #fff;
        }
        .audit-sub-chip.danger-chip.active {
            background: var(--c-danger);
            border-color: var(--c-danger);
            color: #fff;
        }
        .audit-sub-chip.warn-chip.active {
            background: var(--c-warning);
            border-color: var(--c-warning);
            color: #fff;
        }

        .log-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-right: 4px solid var(--c-text-soft);
            border-radius: var(--r-md);
            padding: 14px 16px;
            margin-bottom: 10px;
            transition: var(--t-fast);
            cursor: pointer;
        }
        .log-card:hover { box-shadow: var(--shadow-sm); border-color: var(--c-border-2); }
        .log-card.sev-critical { border-right-color: var(--c-danger); background: var(--c-danger-bg); }
        .log-card.sev-warning  { border-right-color: var(--c-warning); background: var(--c-warning-bg); }
        .log-card.sev-info     { border-right-color: var(--c-info); }

        .log-top { display: flex; justify-content: space-between; align-items: start; gap: 10px; flex-wrap: wrap; }
        .log-meta { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .log-summary {
            color: var(--c-text);
            font-weight: 600;
            line-height: 1.6;
            margin-top: 8px;
            font-size: 0.93rem;
        }
        .log-inline-diff {
            margin-top: 9px;
            padding: 10px 12px;
            border-radius: var(--r-sm);
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid var(--c-border);
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 0.86rem;
        }
        .log-inline-diff.deleted-box {
            border-color: #f5c2c2;
            background: rgba(255, 255, 255, 0.92);
        }
        .inline-diff-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            line-height: 1.5;
        }
        .inline-diff-field {
            font-weight: 700;
            color: var(--c-navy);
        }
        .inline-diff-old {
            background: var(--c-danger-bg);
            color: var(--c-danger);
            padding: 1px 8px;
            border-radius: 6px;
            text-decoration: line-through;
        }
        .inline-diff-new {
            background: var(--c-success-bg);
            color: var(--c-success);
            padding: 1px 8px;
            border-radius: 6px;
            font-weight: 700;
        }
        .log-footer-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            font-size: 0.76rem;
            color: var(--c-text-soft);
        }
        .log-ip { direction: ltr; font-weight: 500; }

        .diff-table { width: 100%; border-collapse: collapse; }
        .diff-table th { background: var(--c-navy-50); padding: 9px 10px; font-weight: 700; font-size: 0.83rem; color: var(--c-navy); border-bottom: 1px solid var(--c-border-2); }
        .diff-table td { padding: 8px 10px; border-bottom: 1px solid var(--c-border); font-size: 0.88rem; vertical-align: top; word-break: break-word; }
        .diff-old { background: var(--c-danger-bg); color: var(--c-danger); text-decoration: line-through; }
        .diff-new { background: var(--c-success-bg); color: var(--c-success); font-weight: 600; }
    </style>
</head>
<body>
@include('sidebar')

<div class="main-content">

    <div class="page-header">
        <div>
            <h2><i class="fa-solid fa-shield-halved"></i> سجل التدقيق</h2>
            <div class="subtitle">كل تغيير حصل في النظام: مين، إمتى، عدّل من إيه لإيه، ومين مسح إيه</div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro">
                <div class="label">إجمالي السجلات (الفلتر)</div>
                <div class="value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro danger">
                <div class="label">عمليات حرجة / حذف</div>
                <div class="value">{{ $stats['critical'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro warning">
                <div class="label">تعديلات وتحذيرات</div>
                <div class="value">{{ $stats['warning'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro info">
                <div class="label">{{ $module === 'inquiries' ? 'سجلات المهام اليوم' : 'سجلات اليوم' }}</div>
                <div class="value">{{ $stats['today'] }}</div>
            </div>
        </div>
    </div>

    {{-- Quick module shortcuts --}}
    @php
        $baseQuery = array_filter([
            'period'    => $period,
            'from_date' => $period === 'custom' ? $fromDate : null,
            'to_date'   => $period === 'custom' ? $toDate : null,
            'user_id'   => $userId !== 'all' ? $userId : null,
            'search'    => $search !== '' ? $search : null,
        ], fn ($v) => $v !== null && $v !== '');
    @endphp
    <div class="audit-quick-bar">
        <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'all', 'action' => 'all'])) }}"
           class="audit-quick-chip {{ $module === 'all' ? 'active' : '' }}">
            <i class="fa fa-layer-group"></i> كل الأقسام
        </a>
        <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'inquiries', 'action' => 'all'])) }}"
           class="audit-quick-chip tasks-chip {{ $module === 'inquiries' ? 'active' : '' }}">
            <i class="fa-solid fa-list-check"></i> سجل المهام
        </a>

        @if($module === 'inquiries')
            <span class="text-muted mx-1">|</span>
            <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'inquiries', 'action' => 'all'])) }}"
               class="audit-sub-chip {{ $action === 'all' ? 'active' : '' }}">
                الكل ({{ $taskActionCounts['all'] ?? 0 }})
            </a>
            <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'inquiries', 'action' => 'update'])) }}"
               class="audit-sub-chip warn-chip {{ $action === 'update' ? 'active' : '' }}">
                <i class="fa fa-pen"></i> تعديلات ({{ $taskActionCounts['update'] ?? 0 }})
            </a>
            <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'inquiries', 'action' => 'delete'])) }}"
               class="audit-sub-chip danger-chip {{ $action === 'delete' ? 'active' : '' }}">
                <i class="fa fa-trash"></i> محذوفة ({{ $taskActionCounts['delete'] ?? 0 }})
            </a>
            <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'inquiries', 'action' => 'create'])) }}"
               class="audit-sub-chip {{ $action === 'create' ? 'active' : '' }}">
                <i class="fa fa-plus"></i> مضافة ({{ $taskActionCounts['create'] ?? 0 }})
            </a>
            <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'inquiries', 'action' => 'complete'])) }}"
               class="audit-sub-chip {{ $action === 'complete' ? 'active' : '' }}">
                <i class="fa fa-check"></i> منجزة ({{ $taskActionCounts['complete'] ?? 0 }})
            </a>
            <a href="{{ route('audit.index', array_merge($baseQuery, ['module' => 'inquiries', 'action' => 'reopen'])) }}"
               class="audit-sub-chip warn-chip {{ $action === 'reopen' ? 'active' : '' }}">
                <i class="fa fa-rotate-left"></i> مُعادة لليوم ({{ $taskActionCounts['reopen'] ?? 0 }})
            </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="panel-pro">
        <form method="GET" action="{{ route('audit.index') }}" id="auditFilterForm" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-pro-label">الفترة</label>
                <select name="period" id="auditPeriodSelect" class="form-select form-pro-control" onchange="onAuditPeriodChange(this.value)">
                    <option value="today"     {{ $period=='today' ? 'selected' : '' }}>اليوم</option>
                    <option value="yesterday" {{ $period=='yesterday' ? 'selected' : '' }}>أمس</option>
                    <option value="week"      {{ $period=='week' ? 'selected' : '' }}>آخر 7 أيام</option>
                    <option value="month"     {{ $period=='month' ? 'selected' : '' }}>آخر 30 يوم</option>
                    <option value="custom"    {{ $period=='custom' ? 'selected' : '' }}>فترة مخصصة</option>
                    <option value="all"       {{ $period=='all' ? 'selected' : '' }}>كل الفترات</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">القسم</label>
                <select name="module" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">كل الأقسام</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ $module==$m ? 'selected' : '' }}>{{ $moduleLabels[$m] ?? $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">العملية</label>
                <select name="action" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">كل العمليات</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ $action==$a ? 'selected' : '' }}>{{ $actionLabels[$a] ?? $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">الأهمية</label>
                <select name="severity" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">الكل</option>
                    <option value="info"     {{ $severity=='info' ? 'selected' : '' }}>عادي</option>
                    <option value="warning"  {{ $severity=='warning' ? 'selected' : '' }}>تحذير / تعديل</option>
                    <option value="critical" {{ $severity=='critical' ? 'selected' : '' }}>حرج / حذف</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">الموظف / اليوزر</label>
                <select name="user_id" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">كل المستخدمين</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (string)$userId === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->name }}{{ !empty($u->role) && isset($roleLabels[$u->role]) ? ' (' . $roleLabels[$u->role] . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">بحث</label>
                <div class="d-flex gap-1">
                    <input type="text" name="search" class="form-control form-pro-control" placeholder="نص مهمة، موظف..." value="{{ $search }}">
                    <button type="submit" class="btn-pro btn-pro-primary px-3" title="بحث"><i class="fa fa-magnifying-glass"></i></button>
                </div>
            </div>

            <div class="col-12" id="auditCustomDateRow" style="display: {{ $period === 'custom' ? 'block' : 'none' }};">
                <div class="row g-2 mt-1">
                    <div class="col-md-3">
                        <label class="form-pro-label">من تاريخ</label>
                        <input type="date" name="from_date" class="form-control form-pro-control" value="{{ $fromDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-pro-label">إلى تاريخ</label>
                        <input type="date" name="to_date" class="form-control form-pro-control" value="{{ $toDate }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn-pro btn-pro-primary w-100"><i class="fa fa-check"></i> تطبيق</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Logs --}}
    @forelse($logs as $log)
        @php
            $actionPillClass = match ($log->action) {
                'delete', 'cancel', 'writeoff' => 'pill-danger',
                'update', 'reopen', 'bulk_update' => 'pill-warning',
                'complete', 'create' => 'pill-success',
                default => 'pill-neutral',
            };
        @endphp
        <div class="log-card sev-{{ $log->severity }}" onclick="showDiff({{ $log->id }})">
            <div class="log-top">
                <div class="log-meta">
                    <span class="pill-pro pill-info">{{ $log->module_label }}</span>
                    <span class="pill-pro {{ $actionPillClass }}">{{ $log->action_label }}</span>
                    <span class="pill-pro pill-accent">
                        <i class="fa fa-user"></i> {{ $log->user_name }}
                        @if($log->role_label && $log->role_label !== '-')
                            <small style="opacity:0.8;">({{ $log->role_label }})</small>
                        @endif
                    </span>
                    @if(!empty($log->entity_id))
                        <span class="pill-pro pill-neutral">#{{ $log->entity_id }}</span>
                    @endif
                    @if($log->severity === 'critical')
                        <span class="pill-pro pill-danger"><i class="fa fa-triangle-exclamation"></i> حرج</span>
                    @endif
                </div>
                <span class="pill-pro pill-neutral"><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($log->created_at)->format('Y/m/d - h:i:s A') }}</span>
            </div>

            <div class="log-summary">{{ $log->summary }}</div>

            {{-- عرض مباشر للتفاصيل: عدّل من إيه لإيه / أو إيه اللي اتمسح --}}
            @if(!empty($log->changes))
                <div class="log-inline-diff">
                    @foreach($log->changes as $chg)
                        <div class="inline-diff-row">
                            <span class="inline-diff-field">{{ $chg['field'] }}:</span>
                            <span class="inline-diff-old">{{ $chg['old'] }}</span>
                            <i class="fa fa-arrow-left text-muted" style="font-size:0.75rem;"></i>
                            <span class="inline-diff-new">{{ $chg['new'] }}</span>
                        </div>
                    @endforeach
                </div>
            @elseif($log->action === 'delete' && !empty($log->old_array))
                <div class="log-inline-diff deleted-box">
                    <div class="fw-bold text-danger small"><i class="fa fa-trash-can me-1"></i> تفاصيل السجل المحذوف:</div>
                    @foreach($log->old_array as $fk => $fv)
                        <div class="inline-diff-row">
                            <span class="inline-diff-field">{{ $fk }}:</span>
                            <span class="inline-diff-old" style="text-decoration:none;">{{ is_scalar($fv) ? $fv : json_encode($fv, JSON_UNESCAPED_UNICODE) }}</span>
                        </div>
                    @endforeach
                </div>
            @elseif($log->module === 'inquiries' && $log->action === 'create' && !empty($log->new_array))
                <div class="log-inline-diff">
                    @foreach($log->new_array as $fk => $fv)
                        <div class="inline-diff-row">
                            <span class="inline-diff-field">{{ $fk }}:</span>
                            <span>{{ is_scalar($fv) ? $fv : json_encode($fv, JSON_UNESCAPED_UNICODE) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="log-footer-line">
                <span><i class="fa fa-circle-info me-1"></i> اضغط لعرض كامل التفاصيل</span>
                @if($log->ip_address)
                    <span class="log-ip">IP: {{ $log->ip_address }}</span>
                @endif
            </div>
        </div>
    @empty
        <div class="empty-pro">
            <i class="fa-regular fa-folder-open"></i>
            <h5>لا توجد سجلات بالفلاتر الحالية</h5>
            <p>غير الفترة أو الفلاتر لرؤية نتائج</p>
        </div>
    @endforelse

</div>

{{-- Modal للتفاصيل --}}
<div class="modal fade" id="diffModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:var(--r-lg); border:none;">
            <div class="modal-header" style="background:var(--c-navy); color:#fff; border:none; border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-magnifying-glass me-2"></i> تفاصيل السجل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="diffBody">جاري التحميل...</div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
function escHtml(val) {
    return String(val ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function formatAuditVal(val) {
    if (val === null || val === undefined || val === '') return '—';
    if (typeof val === 'boolean') return val ? 'نعم' : 'لا';
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
}

function onAuditPeriodChange(val) {
    const customRow = document.getElementById('auditCustomDateRow');
    if (val === 'custom') {
        if (customRow) customRow.style.display = 'block';
        return;
    }
    if (customRow) customRow.style.display = 'none';
    document.getElementById('auditFilterForm').submit();
}

function showDiff(id) {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('diffModal'));
    document.getElementById('diffBody').innerHTML = 'جاري التحميل...';
    modal.show();

    fetch(`/audit-log/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            let html = `
                <div class="row g-2 mb-3 p-3 rounded" style="background: var(--c-bg); border: 1px solid var(--c-border);">
                    <div class="col-md-6"><strong>المستخدم:</strong> ${escHtml(data.user_name || 'غير معروف')} <span class="text-muted">(${escHtml(data.role_label || data.user_role || '-')})</span></div>
                    <div class="col-md-6"><strong>الوقت:</strong> ${escHtml(data.formatted_created_at || data.created_at || '-')}</div>
                    <div class="col-md-6"><strong>القسم:</strong> ${escHtml(data.module_label || data.module || '-')}</div>
                    <div class="col-md-6"><strong>العملية:</strong> ${escHtml(data.action_label || data.action || '-')}</div>
                    ${data.entity_id ? `<div class="col-md-6"><strong>رقم السجل:</strong> #${escHtml(data.entity_id)}</div>` : ''}
                    <div class="col-md-6"><strong>IP:</strong> <span dir="ltr">${escHtml(data.ip_address || '-')}</span></div>
                </div>
                <div class="mb-3 p-3 rounded" style="background: var(--c-surface-2, #f8fafc); border: 1px solid var(--c-border);">
                    <strong>الملخص:</strong><br>${escHtml(data.summary || '')}
                </div>
            `;

            const oldObj = data.old_values && typeof data.old_values === 'object' ? data.old_values : null;
            const newObj = data.new_values && typeof data.new_values === 'object' ? data.new_values : null;

            if (oldObj && newObj) {
                const keys = Array.from(new Set([...Object.keys(oldObj), ...Object.keys(newObj)]));
                const changedRows = [];
                const unchangedRows = [];

                keys.forEach(k => {
                    const ov = oldObj[k];
                    const nv = newObj[k];
                    if (JSON.stringify(ov ?? null) !== JSON.stringify(nv ?? null)) {
                        changedRows.push({ k, ov: formatAuditVal(ov), nv: formatAuditVal(nv) });
                    } else {
                        unchangedRows.push({ k, v: formatAuditVal(nv) });
                    }
                });

                if (changedRows.length > 0) {
                    html += '<h6 class="fw-bold mt-3 mb-2 text-navy"><i class="fa fa-code-compare me-1"></i> التعديلات (من إيه ← لإيه):</h6>';
                    html += '<table class="diff-table mb-3"><thead><tr><th style="width:26%;">الحقل</th><th style="width:37%;">قبل التعديل</th><th style="width:37%;">بعد التعديل</th></tr></thead><tbody>';
                    changedRows.forEach(r => {
                        html += `<tr><td><strong>${escHtml(r.k)}</strong></td><td class="diff-old">${escHtml(r.ov)}</td><td class="diff-new">${escHtml(r.nv)}</td></tr>`;
                    });
                    html += '</tbody></table>';
                }

                if (unchangedRows.length > 0) {
                    html += '<h6 class="fw-bold mt-3 mb-2 text-muted small">باقي بيانات السجل (بدون تغيير):</h6>';
                    html += '<table class="diff-table"><tbody>';
                    unchangedRows.forEach(r => {
                        html += `<tr><td style="width:26%;"><strong>${escHtml(r.k)}</strong></td><td>${escHtml(r.v)}</td></tr>`;
                    });
                    html += '</tbody></table>';
                }
            } else if (oldObj && !newObj) {
                html += '<h6 class="fw-bold mt-3 mb-2 text-danger"><i class="fa fa-trash-can me-1"></i> البيانات المحذوفة:</h6>';
                html += '<table class="diff-table"><thead><tr><th style="width:30%;">الحقل</th><th>القيمة قبل الحذف</th></tr></thead><tbody>';
                Object.keys(oldObj).forEach(k => {
                    html += `<tr><td><strong>${escHtml(k)}</strong></td><td class="diff-old" style="text-decoration:none;">${escHtml(formatAuditVal(oldObj[k]))}</td></tr>`;
                });
                html += '</tbody></table>';
            } else if (!oldObj && newObj) {
                html += '<h6 class="fw-bold mt-3 mb-2 text-success"><i class="fa fa-plus-circle me-1"></i> البيانات المسجّلة:</h6>';
                html += '<table class="diff-table"><thead><tr><th style="width:30%;">الحقل</th><th>القيمة</th></tr></thead><tbody>';
                Object.keys(newObj).forEach(k => {
                    html += `<tr><td><strong>${escHtml(k)}</strong></td><td class="diff-new">${escHtml(formatAuditVal(newObj[k]))}</td></tr>`;
                });
                html += '</tbody></table>';
            }

            document.getElementById('diffBody').innerHTML = html;
        })
        .catch(() => { document.getElementById('diffBody').innerHTML = '<div class="alert alert-danger">خطأ في تحميل البيانات</div>'; });
}
</script>
</body>
</html>
