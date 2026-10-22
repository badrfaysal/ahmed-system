<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الشركاء وحصص رأس المال - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #0f172a;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
            --card-border: rgba(226, 232, 240, 0.8);
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
        }
        .main-content {
            padding: 24px 20px;
        }
        .security-badge {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fbbf24;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid rgba(251, 191, 36, 0.3);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .kpi-card {
            border-radius: 14px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        }
        .kpi-card i.watermark {
            position: absolute;
            left: -15px;
            bottom: -20px;
            font-size: 5.5rem;
            opacity: 0.12;
            transform: rotate(-10deg);
        }
        .kpi-card.c-blue   { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
        .kpi-card.c-gold   { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%); }
        .kpi-card.c-green  { background: linear-gradient(135deg, #065f46 0%, #10b981 100%); }
        .kpi-card.c-purple { background: linear-gradient(135deg, #581c87 0%, #8b5cf6 100%); }
        .kpi-card .kpi-title { font-size: 0.88rem; font-weight: 700; opacity: 0.9; margin-bottom: 8px; }
        .kpi-card .kpi-val   { font-size: 1.7rem; font-weight: 900; line-height: 1.2; }
        .kpi-card .kpi-sub   { font-size: 0.78rem; opacity: 0.85; margin-top: 6px; }

        .section-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .section-header {
            padding: 16px 20px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .section-header h5 {
            margin: 0;
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .table-custom {
            margin: 0;
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .table-custom thead th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.82rem;
            padding: 12px 14px;
            border: none;
            white-space: nowrap;
        }
        .table-custom tbody td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .ratio-pill {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.82rem;
            line-height: 1.2;
        }
        .ratio-pill.blue  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .ratio-pill.gold  { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .ratio-pill.green { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .ratio-pill small { font-size: 0.65rem; font-weight: 600; opacity: 0.85; }

        .btn-action-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.82rem;
            transition: 0.15s ease;
        }
        .btn-action-icon:hover { transform: scale(1.08); }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- 🔒 ترويسة الصفحة والتنبيه الأمني --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="fa fa-handshake text-warning"></i> موديول الشركاء وتوزيع الأرباح المركبة
                </h3>
                <p class="text-muted small m-0">نظام تقفيل دورة رأس المال الشهرية (يوم 25)، وحساب نسب الشركاء المتغيرة والرسملة وتدوير الأرباح.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="security-badge small fw-bold">
                    <i class="fa fa-shield-halved me-1"></i> سري للغاية ومخصص حصرياً للمدير العام (أحمد)
                </span>
                <a href="{{ route('partners.closing') }}" class="btn btn-warning fw-bold shadow-sm px-3 py-2 text-dark">
                    <i class="fa fa-bolt me-1"></i> تقفيل شهر جديد (25) ⚡
                </a>
                <button type="button" class="btn btn-primary fw-bold shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalAddPartner">
                    <i class="fa fa-user-plus me-1"></i> إضافة شريك جديد
                </button>
                <a href="{{ route('partners.ahmed') }}" class="btn btn-dark fw-bold shadow-sm px-3 py-2">
                    <i class="fa fa-crown text-warning me-1"></i> بروفايل أحمد (المؤسس)
                </a>
            </div>
        </div>

        {{-- 📊 كروت المؤشرات المالية الكلية --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="kpi-card c-blue">
                    <i class="fa fa-vault watermark"></i>
                    <div class="kpi-title"><i class="fa fa-building me-1"></i> رأس مال الشركة الكلي</div>
                    <div class="kpi-val">{{ number_format($totalCompanyCapital, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                    <div class="kpi-sub">
                        <span>السيولة بالخزائن: <strong>{{ number_format($totalLiquidity, 2) }} ج</strong></span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card c-gold">
                    <i class="fa fa-crown watermark"></i>
                    <div class="kpi-title"><i class="fa fa-user-tie me-1"></i> رأس مال أحمد (المؤسس)</div>
                    <div class="kpi-val">{{ number_format($ahmedCapital, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                    <div class="kpi-sub d-flex justify-content-between">
                        <span>نسبته في الشركة: <strong>{{ number_format($ahmedSharePct, 2) }}%</strong></span>
                        <span>(الباقي بعد الشركاء)</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card c-purple">
                    <i class="fa fa-users watermark"></i>
                    <div class="kpi-title"><i class="fa fa-handshake me-1"></i> إجمالي رؤوس أموال الشركاء</div>
                    <div class="kpi-val">{{ number_format($totalPartnersCapital, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                    <div class="kpi-sub">
                        <span>نسبة الشركاء مجتمعين: <strong>{{ number_format(100 - $ahmedSharePct, 2) }}%</strong> ({{ $activePartners->count() }} شركاء)</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card c-green">
                    <i class="fa fa-sack-dollar watermark"></i>
                    <div class="kpi-title"><i class="fa fa-chart-line-up me-1"></i> عوائد أحمد من إدارة الشركاء</div>
                    <div class="kpi-val">{{ number_format($totalAhmedCommissionsEarned, 2) }} <span class="fs-6 fw-normal">ج</span></div>
                    <div class="kpi-sub">
                        <span>إجمالي العمولات المحصلة من أرباح الشركاء</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 👥 جدول الشركاء النشطين والنسب المركبة --}}
        <div class="section-card">
            <div class="section-header">
                <h5>
                    <i class="fa fa-users text-primary"></i> قائمة الشركاء والحصص الحالية
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">{{ $partners->count() }} شريك</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">💡 النسبة تتحدث تلقائياً مع كل تقفيلة بناءً على السحب أو التدوير في رأس المال.</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الشريك</th>
                            <th>رأس المال الحالي</th>
                            <th class="text-center">1️⃣ نسبة رأس المال<br><small class="text-muted fw-normal">(حصتي من الشركة)</small></th>
                            <th class="text-center">2️⃣ نسبة الاتفاق مع أحمد<br><small class="text-muted fw-normal">(عمولة أحمد من الربح)</small></th>
                            <th class="text-center">3️⃣ النسبة الفعلية في الربح<br><small class="text-muted fw-normal">(1 × صافي حصتي)</small></th>
                            <th>أرباحه السابقة</th>
                            <th>مسحوباته السابقة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $p)
                        <tr class="{{ $p->status === 'exited' ? 'table-secondary opacity-75' : '' }}">
                            <td class="fw-bold">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('partners.show', $p->id) }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $p->name }}
                                </a>
                                <small class="text-muted"><i class="fa fa-phone fa-xs me-1"></i>{{ $p->phone ?: 'بدون هاتف' }} · انضم: {{ $p->joined_at ? date('Y-m-d', strtotime($p->joined_at)) : '—' }}</small>
                                @if($p->status === 'exited')
                                    <span class="badge bg-danger ms-1" style="font-size:0.65rem;">متخارج</span>
                                @endif
                            </td>
                            <td class="fw-bold text-primary fs-6">
                                {{ number_format($p->capital, 2) }} ج
                                @if($p->initial_capital > 0 && $p->capital != $p->initial_capital)
                                    <div style="font-size:0.7rem;" class="{{ $p->capital > $p->initial_capital ? 'text-success' : 'text-danger' }}">
                                        {{ $p->capital > $p->initial_capital ? '▲ نما بمقدار' : '▼ نقص بمقدار' }} {{ number_format(abs($p->capital - $p->initial_capital), 2) }} ج
                                    </div>
                                @endif
                            </td>

                            {{-- 1️⃣ نسبة رأس المال --}}
                            <td class="text-center">
                                <span class="ratio-pill blue">
                                    {{ number_format($p->capital_share_pct, 2) }}%
                                    <small>من رأس مال الشركة</small>
                                </span>
                            </td>

                            {{-- 2️⃣ نسبة الاتفاق مع أحمد --}}
                            <td class="text-center">
                                <span class="ratio-pill gold">
                                    {{ number_format(100 - $p->ahmed_ratio, 1) }}% للشريك
                                    <small>{{ number_format($p->ahmed_ratio, 1) }}% لأحمد</small>
                                </span>
                            </td>

                            {{-- 3️⃣ النسبة الفعلية في الربح --}}
                            <td class="text-center">
                                <span class="ratio-pill green">
                                    {{ number_format($p->effective_profit_pct, 2) }}%
                                    <small>من صافي أرباح الشركة</small>
                                </span>
                            </td>

                            <td class="fw-bold text-success">{{ number_format($p->total_earned, 2) }} ج</td>
                            <td class="fw-bold text-danger">{{ number_format($p->total_withdrawn_profits, 2) }} ج</td>

                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('partners.show', $p->id) }}" class="btn btn-outline-primary btn-action-icon" title="عرض البروفايل والشارتات">
                                        <i class="fa fa-chart-line"></i>
                                    </a>
                                    @if($p->status === 'active')
                                    <button type="button" class="btn btn-outline-success btn-action-icon" title="إيداع / زيادة رأس مال" onclick="openAddCapitalModal({{ $p->id }}, '{{ addslashes($p->name) }}')">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-action-icon" title="سحب من رأس المال" onclick="openWithdrawCapitalModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->capital }})">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-action-icon" title="تعديل الإعدادات والنسبة" onclick="openEditPartnerModal({{ json_encode($p) }})">
                                        <i class="fa fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-action-icon" title="تصفية وتخارج نهائي" onclick="openExitPartnerModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->capital }})">
                                        <i class="fa fa-right-from-bracket"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fa fa-users-slash fs-2 mb-2 d-block text-secondary"></i>
                                لا يوجد شركاء مسجلين حتى الآن. ابدأ بإضافة شريكك الأول (مثل بدر أو محمود) من الزر أعلاه!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 📅 أرشيف التقفيلات الشهرية السابقة (دورات 25) --}}
        <div class="section-card">
            <div class="section-header">
                <h5>
                    <i class="fa fa-calendar-check text-success"></i> سجل التقفيلات الشهرية المعتمدة
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-2">{{ $periods->count() }} تقفيلة</span>
                </h5>
                <a href="{{ route('partners.closing') }}" class="btn btn-sm btn-outline-warning fw-bold text-dark">
                    <i class="fa fa-bolt me-1"></i> فتح تقفيل دورة جديدة
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الفترة</th>
                            <th>تاريخ البداية</th>
                            <th>تاريخ الإقفال</th>
                            <th>رأس مال الافتتاح</th>
                            <th>رأس مال الإقفال</th>
                            <th>الربح التشغيلي القابل للتوزيع (R)</th>
                            <th>أرباح الشركاء</th>
                            <th>عمولة أحمد المحصلة</th>
                            <th>المسحوب نقداً</th>
                            <th>المدوّر في رأس المال</th>
                            <th class="text-center">التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $per)
                        <tr>
                            <td class="fw-bold">{{ $loop->iteration }}</td>
                            <td class="fw-bold text-dark">{{ $per->period_name }}</td>
                            <td>{{ $per->start_date }}</td>
                            <td>{{ $per->end_date }}</td>
                            <td class="fw-bold">{{ number_format($per->opening_capital, 2) }} ج</td>
                            <td class="fw-bold text-primary">{{ number_format($per->closing_capital, 2) }} ج</td>
                            <td class="fw-bold {{ $per->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $per->gross_profit >= 0 ? '+' : '' }}{{ number_format($per->gross_profit, 2) }} ج
                            </td>
                            <td class="fw-bold text-info">{{ number_format($per->total_partners_net_profit, 2) }} ج</td>
                            <td class="fw-bold text-warning-emphasis">+{{ number_format($per->total_ahmed_commission, 2) }} ج</td>
                            <td class="fw-bold text-danger">{{ number_format($per->total_withdrawn, 2) }} ج</td>
                            <td class="fw-bold text-success">{{ number_format($per->total_reinvested, 2) }} ج</td>
                            <td class="text-center">
                                <a href="{{ route('partners.period.show', $per->id) }}" class="btn btn-sm btn-outline-dark fw-bold px-2 py-1">
                                    <i class="fa fa-eye me-1"></i> عرض التقرير
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                لم يتم إقفال أي دورة شهرية حتى الآن. اضغط على «تقفيل شهر جديد (25) ⚡» لحساب أرباح الدورة الأولى.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 1. MODAL: إضافة شريك جديد --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAddPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('partners.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-user-plus me-2"></i> إضافة شريك استثماري جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 small border-0 rounded-3 mb-3">
                    <i class="fa fa-info-circle me-1"></i> عند إضافة شريك ومعه رأس مال، سيتم توريد المبلغ فوراً في الخزنة المحددة ليدخل في سيولة ورأس مال الشركة، وتبدأ حسابات الشريك من دورة التقفيل التالية.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">اسم الشريك <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: بدر أو محمود..." required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">رقم الهاتف للتواصل</label>
                        <input type="text" name="phone" class="form-control" placeholder="مثال: 010xxxxxxxx">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-success">مبلغ رأس المال المودع (ج) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="capital" class="form-control fw-bold fs-5 text-success" placeholder="مثال: 100000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-primary">الخزنة / المحفظة التي استلمت المبلغ <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select fw-bold">
                            <option value="">-- اختر الخزنة لتوريد المبلغ --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيدها: {{ number_format($acc->balance) }} ج)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-warning-emphasis">
                            نسبة أحمد من أرباح الشريك (%) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" class="form-control fw-bold text-warning-emphasis" value="30.00" required>
                            <span class="input-group-text bg-white fw-bold">%</span>
                        </div>
                        <small class="text-muted d-block mt-1">مثال: لو 30%، يعني الشريك هياخد 70% من ربحه وأحمد هياخد 30% نظير الإدارة والشغل.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">تاريخ الانضمام وبداية الشراكة</label>
                        <input type="date" name="joined_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">ملاحظات إضافية أو شروط الاتفاق</label>
                        <input type="text" name="notes" class="form-control" placeholder="أي تفاصيل خاصة بالشريك...">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary fw-bold px-4">
                    <i class="fa fa-check me-1"></i> حفظ وإيداع رأس المال
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 2. MODAL: زيادة رأس مال شريك حالي --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAddCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formAddCapital" action="" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-money-bill-trend-up me-2"></i> زيادة رأس مال الشريك</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3">أنت على وشك إضافة رأس مال إضافي للشريك: <strong class="text-success fs-5" id="addCapPartnerName"></strong></p>

                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ الإضافي المطلوب إيداعه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control fw-bold fs-5 text-success" placeholder="المبلغ..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">الخزنة / المحفظة المستلمة للمبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select fw-bold" required>
                        <option value="">-- اختر الخزنة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيدها: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات / سبب الزيادة</label>
                    <input type="text" name="notes" class="form-control" placeholder="مثال: زيادة حصة في التوسعات الجديدة...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-success fw-bold px-4">
                    <i class="fa fa-plus me-1"></i> تأكيد وإيداع في الخزنة
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 3. MODAL: سحب من رأس مال الشريك --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalWithdrawCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formWithdrawCapital" action="" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fa fa-money-bill-wave me-2"></i> سحب من رأس مال الشريك</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-2">الشريك: <strong class="fs-5 text-dark" id="withCapPartnerName"></strong></p>
                <div class="alert alert-warning py-2 px-3 small border-0 rounded-3 mb-3">
                    رأس ماله الحالي: <strong class="fs-6" id="withCapCurrentVal">0.00</strong> ج. لا يمكن سحب مبلغ أكبر من رصيده. سيتم خصم المبلغ من الخزنة ومن رأس مال الشريك.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">المبلغ المراد سحبه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" id="withCapAmountInput" class="form-control fw-bold fs-5 text-danger" placeholder="المبلغ..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">الخزنة المصروف منها المبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select fw-bold" required>
                        <option value="">-- اختر الخزنة للصرف --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيدها: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات / سبب السحب</label>
                    <input type="text" name="notes" class="form-control" placeholder="سبب استرداد جزء من رأس المال...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-warning fw-bold px-4 text-dark">
                    <i class="fa fa-minus me-1"></i> تأكيد وصرف من الخزنة
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 4. MODAL: تعديل بيانات الشريك ونسبته --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditPartner" action="" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-pen-to-square me-2"></i> تعديل بيانات الشريك والنسبة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">اسم الشريك <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editPartnerName" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">رقم الهاتف</label>
                    <input type="text" name="phone" id="editPartnerPhone" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-warning-emphasis">نسبة أحمد من أرباح الشريك (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" id="editPartnerAhmedRatio" class="form-control fw-bold" required>
                        <span class="input-group-text bg-white fw-bold">%</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <input type="text" name="notes" id="editPartnerNotes" class="form-control">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark fw-bold px-4">
                    <i class="fa fa-check me-1"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 5. MODAL: تصفية وتخارج نهائي للشريك --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalExitPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formExitPartner" action="" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-right-from-bracket me-2"></i> تصفية وتخارج نهائي للشريك</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger py-2 px-3 small border-0 rounded-3 mb-3">
                    ⚠️ تحذير: التخارج يعني تصفية كامل رأس مال الشريك وخروجه من الشركة نهائياً. سيتم صرف كامل رصيده البالغ <strong class="fs-6" id="exitCapVal">0.00</strong> ج من الخزنة المختارة وتحويل حالته إلى «متخارج».
                </div>

                <p class="mb-3">الشريك المراد تصفيته: <strong class="text-danger fs-5" id="exitPartnerName"></strong></p>

                <div class="mb-3">
                    <label class="form-label fw-bold">الخزنة المصروف منها مستحقات التصفية <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select fw-bold" required>
                        <option value="">-- اختر الخزنة لصرف التخارج --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيدها: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات التخارج</label>
                    <input type="text" name="notes" class="form-control" placeholder="تفاصيل تصفية الشراكة والاتفاق النهائي...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">تراجع</button>
                <button type="submit" class="btn btn-danger fw-bold px-4" onclick="return confirm('هل أنت متأكد تماماً من تصفية وتخارج هذا الشريك وصرف كامل رصيده من الخزنة؟')">
                    <i class="fa fa-check me-1"></i> تأكيد التصفية والتخارج
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openAddCapitalModal(id, name) {
        document.getElementById('formAddCapital').action = '/partners/' + id + '/add-capital';
        document.getElementById('addCapPartnerName').innerText = name;
        new bootstrap.Modal(document.getElementById('modalAddCapital')).show();
    }

    function openWithdrawCapitalModal(id, name, capital) {
        document.getElementById('formWithdrawCapital').action = '/partners/' + id + '/withdraw-capital';
        document.getElementById('withCapPartnerName').innerText = name;
        document.getElementById('withCapCurrentVal').innerText = Number(capital).toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('withCapAmountInput').max = capital;
        new bootstrap.Modal(document.getElementById('modalWithdrawCapital')).show();
    }

    function openEditPartnerModal(p) {
        document.getElementById('formEditPartner').action = '/partners/' + p.id + '/update';
        document.getElementById('editPartnerName').value = p.name || '';
        document.getElementById('editPartnerPhone').value = p.phone || '';
        document.getElementById('editPartnerAhmedRatio').value = p.ahmed_ratio || 30.00;
        document.getElementById('editPartnerNotes').value = p.notes || '';
        new bootstrap.Modal(document.getElementById('modalEditPartner')).show();
    }

    function openExitPartnerModal(id, name, capital) {
        document.getElementById('formExitPartner').action = '/partners/' + id + '/exit';
        document.getElementById('exitPartnerName').innerText = name;
        document.getElementById('exitCapVal').innerText = Number(capital).toLocaleString('en-US', {minimumFractionDigits: 2});
        new bootstrap.Modal(document.getElementById('modalExitPartner')).show();
    }
</script>

</body>
</html>
