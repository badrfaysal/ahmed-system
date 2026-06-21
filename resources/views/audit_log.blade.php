<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل التدقيق - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('partials.theme', ['accent' => 'rose'])

    <style>
        .log-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-right: 3px solid var(--c-text-soft);
            border-radius: var(--r-md);
            padding: 13px 16px;
            margin-bottom: 8px;
            transition: var(--t-fast);
            cursor: pointer;
        }
        .log-card:hover { box-shadow: var(--shadow-sm); }
        .log-card.sev-critical { border-right-color: var(--c-danger); background: var(--c-danger-bg); }
        .log-card.sev-warning  { border-right-color: var(--c-warning); background: var(--c-warning-bg); }
        .log-card.sev-info     { border-right-color: var(--c-info); }

        .log-top { display: flex; justify-content: space-between; align-items: start; gap: 10px; flex-wrap: wrap; }
        .log-meta { display: flex; gap: 5px; flex-wrap: wrap; }
        .log-summary {
            color: var(--c-text);
            font-weight: 500;
            line-height: 1.55;
            margin-top: 6px;
            font-size: 0.92rem;
        }
        .log-ip { color: var(--c-text-soft); font-size: 0.72rem; font-weight: 500; direction: ltr; text-align: end; margin-top: 4px; }

        .diff-table { width: 100%; }
        .diff-table th { background: var(--c-navy-50); padding: 8px 10px; font-weight: 600; font-size: 0.82rem; color: var(--c-navy); }
        .diff-table td { padding: 7px 10px; border-bottom: 1px solid var(--c-border); font-size: 0.88rem; }
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
            <div class="subtitle">كل تغيير حصل في النظام: مين، إمتى، وإيه اللي اتغير</div>
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
                <div class="label">عمليات حرجة</div>
                <div class="value">{{ $stats['critical'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro warning">
                <div class="label">تحذيرات</div>
                <div class="value">{{ $stats['warning'] }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-pro info">
                <div class="label">سجلات اليوم</div>
                <div class="value">{{ $stats['today'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="panel-pro">
        <form method="GET" action="{{ route('audit.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-pro-label">الفترة</label>
                <select name="period" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="today"     {{ $period=='today' ? 'selected' : '' }}>اليوم</option>
                    <option value="yesterday" {{ $period=='yesterday' ? 'selected' : '' }}>أمس</option>
                    <option value="week"      {{ $period=='week' ? 'selected' : '' }}>آخر 7 أيام</option>
                    <option value="month"     {{ $period=='month' ? 'selected' : '' }}>آخر 30 يوم</option>
                    <option value="all"       {{ $period=='all' ? 'selected' : '' }}>كل الفترات</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">القسم</label>
                <select name="module" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">كل الأقسام</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ $module==$m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">العملية</label>
                <select name="action" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">كل العمليات</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ $action==$a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">الأهمية</label>
                <select name="severity" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">الكل</option>
                    <option value="info"     {{ $severity=='info' ? 'selected' : '' }}>عادي</option>
                    <option value="warning"  {{ $severity=='warning' ? 'selected' : '' }}>تحذير</option>
                    <option value="critical" {{ $severity=='critical' ? 'selected' : '' }}>حرج</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">الموظف</label>
                <select name="user_id" class="form-select form-pro-control" onchange="this.form.submit()">
                    <option value="all">الكل</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (string)$userId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-pro-label">بحث</label>
                <input type="text" name="search" class="form-control form-pro-control" placeholder="بحث في الوصف..." value="{{ $search }}">
            </div>
        </form>
    </div>

    {{-- Logs --}}
    @forelse($logs as $log)
        <div class="log-card sev-{{ $log->severity }}" onclick="showDiff({{ $log->id }})">
            <div class="log-top">
                <div class="log-meta">
                    <span class="pill-pro pill-info">{{ $log->module }}</span>
                    <span class="pill-pro pill-neutral">{{ $log->action }}</span>
                    <span class="pill-pro pill-accent"><i class="fa fa-user"></i> {{ $log->user_name }}</span>
                    @if($log->severity === 'critical')
                        <span class="pill-pro pill-danger"><i class="fa fa-triangle-exclamation"></i> حرج</span>
                    @endif
                </div>
                <span class="pill-pro pill-neutral"><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($log->created_at)->format('Y/m/d h:i:s A') }}</span>
            </div>
            <div class="log-summary">{{ $log->summary }}</div>
            @if($log->ip_address)
                <div class="log-ip">IP: {{ $log->ip_address }}</div>
            @endif
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--r-lg); border:none;">
            <div class="modal-header" style="background:var(--c-navy); color:#fff; border:none; border-radius:var(--r-lg) var(--r-lg) 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-magnifying-glass me-2"></i> تفاصيل السجل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="diffBody">جاري التحميل...</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showDiff(id) {
    const modal = new bootstrap.Modal(document.getElementById('diffModal'));
    document.getElementById('diffBody').innerHTML = 'جاري التحميل...';
    modal.show();

    fetch(`/audit-log/${id}`)
        .then(r => r.json())
        .then(data => {
            let html = `<div class="mb-3"><strong>الموظف:</strong> ${data.user_name || 'غير معروف'} (${data.user_role || '-'})</div>`;
            html += `<div class="mb-3"><strong>الوقت:</strong> ${data.created_at}</div>`;
            html += `<div class="mb-3"><strong>القسم:</strong> ${data.module} | <strong>العملية:</strong> ${data.action}</div>`;
            html += `<div class="mb-3"><strong>IP:</strong> ${data.ip_address || '-'}</div>`;
            html += `<div class="mb-3 p-3 rounded" style="background: var(--c-bg);"><strong>الوصف:</strong><br>${data.summary}</div>`;

            const old = data.old_values, neu = data.new_values;
            if (old || neu) {
                html += '<h6 class="fw-bold mt-3">الفرق:</h6>';
                html += '<table class="diff-table"><thead><tr><th>الحقل</th><th>قبل</th><th>بعد</th></tr></thead><tbody>';
                const keys = new Set([...Object.keys(old || {}), ...Object.keys(neu || {})]);
                keys.forEach(k => {
                    const ov = old ? (old[k] ?? '-') : '-';
                    const nv = neu ? (neu[k] ?? '-') : '-';
                    if (JSON.stringify(ov) !== JSON.stringify(nv)) {
                        html += `<tr><td><strong>${k}</strong></td><td class="diff-old">${JSON.stringify(ov)}</td><td class="diff-new">${JSON.stringify(nv)}</td></tr>`;
                    }
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
