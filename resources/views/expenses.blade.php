<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المصروفات - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @include('partials.theme', ['accent' => 'rose'])

    <style>
        .main-content { margin-right: 260px; padding: 28px 30px; }

        /* ── Hero ── */
        .exp-hero {
            background: linear-gradient(135deg, #6b1230 0%, #8a1a3d 50%, var(--c-accent) 100%);
            color: #fff;
            border-radius: var(--r-xl);
            padding: 22px 28px;
            margin-bottom: 22px;
            box-shadow: var(--shadow-md);
            position: relative; overflow: hidden;
            display: flex; justify-content: space-between; align-items: center;
            gap: 16px; flex-wrap: wrap;
        }
        .exp-hero::after {
            content: ""; position: absolute; top: -50%; left: -10%;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(255,200,210,0.18), transparent 65%);
            border-radius: 50%;
        }
        .exp-hero .hero-block { position: relative; z-index: 2; display: flex; gap: 14px; align-items: center; }
        .exp-hero .hero-icon {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(6px);
            width: 56px; height: 56px; border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.55rem;
        }
        .exp-hero h2 { color: #fff; font-weight: 600; font-size: 1.3rem; margin: 0; }
        .exp-hero .sub { opacity: 0.85; font-size: 0.88rem; margin-top: 3px; }

        .btn-add-expense {
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(6px);
            color: #fff; border: 1px solid rgba(255,255,255,0.3);
            border-radius: var(--r-md);
            padding: 10px 20px; font-weight: 500;
            transition: var(--t-fast); position: relative; z-index: 2;
        }
        .btn-add-expense:hover {
            background: rgba(255,255,255,0.3); color: #fff;
            transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* ── Chart card ── */
        .chart-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--r-lg);
            padding: 20px;
            box-shadow: var(--shadow-xs);
            height: 100%;
            display: flex; flex-direction: column;
        }
        .chart-card h6 {
            color: var(--c-navy); font-weight: 600;
            font-size: 0.95rem; margin-bottom: 14px;
            text-align: center;
            padding-bottom: 12px; border-bottom: 1px solid var(--c-border);
        }

        /* ── Filter ── */
        .filter-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--r-lg);
            padding: 14px 18px;
            margin-bottom: 16px;
        }

        /* ── Table ── */
        .table-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--r-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xs);
        }
        .table-card table { width: 100%; margin: 0; }
        .table-card thead th {
            background: var(--c-navy-50);
            color: var(--c-navy);
            font-weight: 600; font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 0.03em;
            padding: 12px 14px; text-align: start;
            border-bottom: 2px solid var(--c-border-2);
        }
        .table-card tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--c-border);
            color: var(--c-text); font-weight: 400;
            vertical-align: middle;
        }
        .table-card tbody tr { cursor: pointer; transition: var(--t-fast); }
        .table-card tbody tr:hover { background: var(--c-navy-50); }
        .table-card tbody tr:last-child td { border-bottom: none; }
        .table-card tfoot td {
            background: var(--c-navy-50);
            font-weight: 600; padding: 12px 14px;
            border-top: 2px solid var(--c-border-2);
        }
        .amount-cell { color: var(--c-danger); font-weight: 600; font-feature-settings: 'tnum'; }
        .badge-cat {
            display: inline-block;
            padding: 4px 10px; font-size: 0.74rem;
            font-weight: 500; border-radius: 999px;
            white-space: nowrap;
        }
        .b-salary   { background: #e0e7ff; color: #4338ca; }
        .b-discount { background: #fef3c7; color: #d97706; }
        .b-expense  { background: var(--c-danger-bg); color: var(--c-danger); }

        /* ── Detail Modal ── */
        .detail-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0; border-bottom: 1px solid var(--c-border);
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: var(--c-text-muted); font-weight: 500; font-size: 0.88rem; }
        .detail-value { color: var(--c-text); font-weight: 600; font-size: 0.95rem; max-width: 60%; text-align: end; word-break: break-word; }

        .modal-content { border: 1px solid var(--c-border); border-radius: var(--r-lg); }
        .modal-header.expenses-modal-header {
            background: linear-gradient(135deg, var(--c-accent), var(--c-accent-2));
            color: #fff; border: none;
            padding: 18px 24px;
        }
        .modal-header.expenses-modal-header .btn-close { filter: invert(1) brightness(2); }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>

@include('sidebar')

<div class="main-content">

    {{-- ═══════════ Hero ═══════════ --}}
    <div class="exp-hero">
        <div class="hero-block">
            <div class="hero-icon"><i class="fa fa-wallet"></i></div>
            <div>
                <h2>إدارة المصروفات والرواتب</h2>
                <div class="sub">تتبع كل النفقات، الرواتب، الخصومات، والمصاريف التشغيلية</div>
            </div>
        </div>
        <button class="btn-add-expense" data-bs-toggle="modal" data-bs-target="#expenseModal">
            <i class="fa fa-plus-circle me-1"></i> تسجيل مصروف جديد
        </button>
    </div>

    {{-- ═══════════ Stats ═══════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card-pro danger">
                <div class="label">إجمالي المصروفات</div>
                <div class="value">{{ fmtMoney($total_expenses) }}</div>
                <div class="unit">ج.م</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-pro warning">
                <div class="label">الأعلى إنفاقاً</div>
                <div class="value">{{ isset($topCategoryData) ? fmtMoney($topCategoryData['total']) : 0 }}</div>
                <div class="unit">{{ $topCategory ?? '—' }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-pro info">
                <div class="label">عدد العمليات</div>
                <div class="value">{{ $expenses->count() }}</div>
                <div class="unit">حركة</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-pro">
                <div class="label">متوسط العملية</div>
                <div class="value">{{ $expenses->count() > 0 ? fmtMoney($total_expenses / $expenses->count()) : 0 }}</div>
                <div class="unit">ج.م / حركة</div>
            </div>
        </div>
    </div>

    {{-- ═══════════ Chart + Table ═══════════ --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="chart-card">
                <h6><i class="fa fa-chart-pie me-1" style="color: var(--c-accent);"></i> توزيع النفقات</h6>
                @if($total_expenses > 0)
                    <div style="position: relative; height: 280px;">
                        <canvas id="expensesChart"></canvas>
                    </div>
                @else
                    <div class="empty-pro">
                        <i class="fa fa-chart-pie"></i>
                        <h5>لا توجد بيانات</h5>
                        <p>سجّل أول مصروف عشان نعرضلك التوزيع</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-8">
            <div class="filter-card">
                <form method="GET" action="{{ route('expenses.index') }}" id="filterForm" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-pro-label" style="font-size: 0.74rem;">الفترة</label>
                        <select name="date_filter" id="dateFilter" class="form-pro-control" onchange="toggleCustom(this.value)">
                            <option value="all"        {{ $dateFilter=='all'        ?'selected':'' }}>كل الفترات</option>
                            <option value="today"      {{ $dateFilter=='today'      ?'selected':'' }}>اليوم</option>
                            <option value="yesterday"  {{ $dateFilter=='yesterday'  ?'selected':'' }}>أمس</option>
                            <option value="this_week"  {{ $dateFilter=='this_week'  ?'selected':'' }}>الأسبوع</option>
                            <option value="this_month" {{ $dateFilter=='this_month' ?'selected':'' }}>الشهر الحالي</option>
                            <option value="custom"     {{ $dateFilter=='custom'     ?'selected':'' }}>تاريخ محدد...</option>
                            <option value="range"      {{ $dateFilter=='range'      ?'selected':'' }}>نطاق (من - إلى)...</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="customDateWrap" style="display:{{ $dateFilter=='custom'?'block':'none' }}">
                        <label class="form-pro-label" style="font-size: 0.74rem;">التاريخ</label>
                        <input type="date" name="custom_date" id="customDateInput" class="form-pro-control"
                               value="{{ $customDate }}" onchange="document.getElementById('filterForm').submit()">
                    </div>
                    <div class="col-md-3" id="rangeWrap" style="display:{{ $dateFilter=='range'?'block':'none' }}">
                        <label class="form-pro-label" style="font-size: 0.74rem;">من - إلى</label>
                        <div class="d-flex gap-1">
                            <input type="date" name="custom_from" id="rangeFrom" class="form-pro-control"
                                   value="{{ $customFrom ?? '' }}" onchange="if(document.getElementById('rangeTo').value) document.getElementById('filterForm').submit()">
                            <input type="date" name="custom_to" id="rangeTo" class="form-pro-control"
                                   value="{{ $customTo ?? '' }}" onchange="if(document.getElementById('rangeFrom').value) document.getElementById('filterForm').submit()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-pro-label" style="font-size: 0.74rem;">التصنيف</label>
                        <select name="category_filter" class="form-pro-control" onchange="document.getElementById('filterForm').submit()">
                            <option value="">جميع التصنيفات</option>
                            @foreach($allAvailableCategories as $cat)
                                <option value="{{ $cat }}" {{ ($categoryFilter ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <a href="{{ route('expenses.index') }}" class="btn-pro btn-pro-outline w-100" title="إعادة تعيين">
                            <i class="fa fa-rotate"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>التصنيف</th>
                                <th>البيان</th>
                                <th>جهة الصرف</th>
                                <th class="text-end">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $exp)
                                @php
                                    $category = 'مصروفات تشغيلية';
                                    $actualNote = $exp->notes;
                                    $badgeClass = 'b-expense';

                                    if ($exp->type === 'salary_expense') {
                                        $category = 'رواتب وأجور';
                                        $badgeClass = 'b-salary';
                                    } elseif ($exp->type === 'discount') {
                                        $category = 'خصومات للعملاء';
                                        $badgeClass = 'b-discount';
                                    } elseif (preg_match('/^\[(.*?)\] (.*)/', $exp->notes, $m)) {
                                        $category = $m[1];
                                        $actualNote = $m[2];
                                    }

                                    $dateStr = \Carbon\Carbon::parse($exp->created_at)->format('Y/m/d h:i A');
                                    $amountStr = number_format($exp->amount, 2) . ' ج.م';
                                    $accName = $exp->account_name ?? 'غير محدد';
                                @endphp
                                <tr onclick="showExpenseDetails('{{ $exp->id }}', '{{ $category }}', '{{ addslashes($actualNote) }}', '{{ $amountStr }}', '{{ $accName }}', '{{ $dateStr }}', '{{ $badgeClass }}')">
                                    <td class="muted-pro">{{ $loop->iteration }}</td>
                                    <td class="muted-pro">{{ \Carbon\Carbon::parse($exp->created_at)->format('Y/m/d') }}</td>
                                    <td><span class="badge-cat {{ $badgeClass }}">{{ $category }}</span></td>
                                    <td style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $actualNote }}">{{ $actualNote }}</td>
                                    <td class="muted-pro">{{ $accName }}</td>
                                    <td class="text-end amount-cell">{{ fmtMoney($exp->amount) }} ج</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-pro">
                                            <i class="fa fa-folder-open"></i>
                                            <h5>لا توجد حركات في الفترة المحددة</h5>
                                            <p>غيّر فلتر الفترة أو التصنيف</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($expenses->count() > 0)
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end">إجمالي القائمة المعروضة</td>
                                <td class="text-end amount-cell" style="font-size: 1.05rem;">{{ fmtMoney($expenses->sum('amount')) }} ج</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ Add Expense Modal ═══════════ --}}
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header expenses-modal-header">
                <h5 class="modal-title fw-bold m-0"><i class="fa fa-plus-circle me-2"></i>تسجيل مصروف جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-pro-label">تصنيف البند</label>
                        <select name="category" class="form-pro-control" required>
                            <option value="" disabled {{ old('category') ? '' : 'selected' }}>-- حدد نوع المصروف --</option>
                            @foreach($allAvailableCategories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-pro-label">البيان / الملاحظات</label>
                        <input type="text" name="notes" class="form-pro-control"
                               placeholder="مثال: فاتورة كهرباء الفرع الرئيسي..." value="{{ old('notes') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-pro-label" style="color: var(--c-danger);">المبلغ</label>
                        <input type="number" step="0.01" name="amount" required
                               class="form-pro-control" value="{{ old('amount') }}"
                               style="border-color: var(--c-danger); color: var(--c-danger); font-weight: 600; font-size: 1.25rem; text-align: center;"
                               placeholder="0.00">
                    </div>

                    <div class="mb-3">
                        <label class="form-pro-label">الخزنة / جهة الصرف</label>
                        <select class="form-pro-control" name="account_id" required>
                            <option value="" disabled {{ old('account_id') ? '' : 'selected' }}>من أين سيتم الدفع؟</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} (متاح: {{ fmtMoney($acc->balance) }} ج)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-pro-label">تاريخ المصروف</label>
                        <input type="date" name="expense_date" class="form-pro-control"
                               value="{{ old('expense_date', date('Y-m-d')) }}">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn-pro btn-pro-accent btn-pro-lg w-100">
                        <i class="fa fa-check me-2"></i> تأكيد واعتماد المصروف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════ Detail Modal ═══════════ --}}
<div class="modal fade" id="expenseDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--c-navy-50); border-bottom: 1px solid var(--c-border);">
                <h5 class="modal-title fw-bold m-0" style="color: var(--c-navy);">
                    <i class="fa fa-receipt me-2" style="color: var(--c-accent);"></i>تفاصيل الحركة المالية
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div id="detailAmount" style="color: var(--c-danger); font-weight: 700; font-size: 2rem; margin-bottom: 8px; font-feature-settings: 'tnum';">0 ج.م</div>
                    <span id="detailCategory" class="badge-cat b-expense" style="font-size: 0.85rem; padding: 6px 14px;">تصنيف المصروف</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label"><i class="fa fa-hashtag me-2"></i>رقم الحركة</span>
                    <span class="detail-value" id="detailId">#123</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fa fa-calendar me-2"></i>التاريخ والوقت</span>
                    <span class="detail-value muted-pro" id="detailDate" style="direction: ltr;">—</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="fa fa-wallet me-2"></i>جهة الصرف</span>
                    <span class="detail-value" id="detailAccount" style="color: var(--c-accent);">—</span>
                </div>
                <div class="mt-3">
                    <div class="detail-label mb-2"><i class="fa fa-align-right me-2"></i>البيان / الملاحظات</div>
                    <div id="detailNotes" style="background: var(--c-navy-50); padding: 12px 14px; border-radius: var(--r-md); font-size: 0.92rem; line-height: 1.6; color: var(--c-text);">—</div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 pt-0">
                <button type="button" class="btn-pro btn-pro-outline w-100" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleCustom(val) {
        const wrap = document.getElementById('customDateWrap');
        const rangeWrap = document.getElementById('rangeWrap');
        wrap.style.display = (val === 'custom') ? 'block' : 'none';
        rangeWrap.style.display = (val === 'range') ? 'block' : 'none';

        if (val === 'custom') {
            setTimeout(() => document.getElementById('customDateInput').focus(), 50);
        } else if (val === 'range') {
            setTimeout(() => document.getElementById('rangeFrom').focus(), 50);
        } else {
            document.getElementById('filterForm').submit();
        }
    }

    function showExpenseDetails(id, category, notes, amount, account, date, badgeClass) {
        document.getElementById('detailId').innerText = '#' + id;
        document.getElementById('detailCategory').innerText = category;
        document.getElementById('detailCategory').className = 'badge-cat ' + badgeClass;
        document.getElementById('detailCategory').style.fontSize = '0.85rem';
        document.getElementById('detailCategory').style.padding = '6px 14px';
        document.getElementById('detailNotes').innerText = notes;
        document.getElementById('detailAmount').innerText = amount;
        document.getElementById('detailAccount').innerText = account;
        document.getElementById('detailDate').innerText = date;
        new bootstrap.Modal(document.getElementById('expenseDetailsModal')).show();
    }
</script>

@if($total_expenses > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = {!! json_encode(isset($expensesByCategory) ? $expensesByCategory->keys() : []) !!};
    const data   = {!! json_encode(isset($expensesByCategory) ? $expensesByCategory->pluck('total') : []) !!};

    const colors = [
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
        '#8b5cf6', '#06b6d4', '#f97316', '#ec4899',
        '#84cc16', '#14b8a6', '#6366f1', '#a855f7'
    ];

    new Chart(document.getElementById('expensesChart').getContext('2d'), {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0, hoverOffset: 8 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom', rtl: true,
                    labels: {
                        font: { family: 'IBM Plex Sans Arabic', size: 11, weight: '500' },
                        color: '#5a6478', padding: 12,
                        usePointStyle: true, pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(13,31,53,0.92)',
                    titleFont: { family: 'IBM Plex Sans Arabic', size: 12 },
                    bodyFont:  { family: 'IBM Plex Sans Arabic', size: 13, weight: 'bold' },
                    padding: 10, cornerRadius: 8,
                    callbacks: {
                        label: c => {
                            const t = c.dataset.data.reduce((a,b)=>a+b,0);
                            return ' ' + c.raw.toLocaleString() + ' ج (' + ((c.raw/t)*100).toFixed(1) + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif

@if(session('open_modal') || $errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        new bootstrap.Modal(document.getElementById('expenseModal')).show();
    });
</script>
@endif
</body>
</html>
