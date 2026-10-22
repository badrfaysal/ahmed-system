<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقفيل الدورة الشهرية وتوزيع الأرباح - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #0f172a;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }
        .main-content {
            padding: 24px 20px;
        }
        .formula-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 16px;
            color: #ffffff;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            margin-bottom: 24px;
        }
        .formula-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            text-align: center;
        }
        .formula-item .label {
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .formula-item .val {
            font-size: 1.35rem;
            font-weight: 900;
        }
        .table-closing thead th {
            background: #1e293b;
            color: #ffffff;
            font-size: 0.82rem;
            padding: 12px;
            white-space: nowrap;
        }
        .table-closing tbody td {
            vertical-align: middle;
            padding: 12px;
            font-size: 0.88rem;
        }
        .btn-decision {
            transition: all .2s ease-in-out;
            font-size: 0.8rem;
        }
        .btn-decision.active {
            box-shadow: 0 4px 12px rgba(0,0,0,0.18) !important;
            transform: translateY(-1px);
        }
        .btn-outline-success.active {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
        }
        .btn-outline-primary.active {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
        }
        .btn-outline-warning.active {
            background-color: #f59e0b !important;
            border-color: #d97706 !important;
            color: #0f172a !important;
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <a href="{{ route('partners.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fa fa-arrow-right me-1"></i> العودة للوحة الشركاء
                </a>
                <h3 class="fw-bold m-0 d-flex align-items-center gap-2">
                    <i class="fa fa-bolt text-warning"></i> تقفيل الدورة الشهرية للشركاء (حساب لقطة 25)
                </h3>
            </div>
        </div>

        {{-- ⚙️ شريط تحديد تواريخ الفترة ومصدر اللقطة --}}
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="{{ route('partners.closing') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small"><i class="fa fa-calendar-day me-1 text-primary"></i> تاريخ بداية الدورة (اللقطة السابقة)</label>
                    <input type="date" name="start_date" class="form-control fw-bold" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small"><i class="fa fa-calendar-check me-1 text-success"></i> تاريخ نهاية الدورة (لقطة الإقفال)</label>
                    <input type="date" name="end_date" class="form-control fw-bold" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" role="switch" name="use_live" value="1" id="switchLive" {{ $useLiveCapital ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label fw-bold small" for="switchLive">
                            أخذ لقطة لحظية حية لرأس المال الآن ⚡
                        </label>
                    </div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">يحسب رأس المال بدقة من الخزائن والديون والسيولة في هذه الثانية</small>
                </div>
                <div class="col-md-3 text-start">
                    <button type="submit" class="btn btn-primary fw-bold w-100 py-2">
                        <i class="fa fa-rotate me-1"></i> إعادة حساب المعاينة الآن
                    </button>
                </div>
            </form>
        </div>

        {{-- 🧮 بطاقة المعادلة الحسابية وتفكيك الربح التشغيلي --}}
        <div class="formula-card">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary">
                <span class="fw-bold fs-6 text-warning">
                    <i class="fa fa-calculator me-1"></i> معادلة احتساب الربح التشغيلي القابل للتوزيع (R)
                </span>
                <span class="badge bg-secondary">
                    من {{ $preview['start_date'] }} إلى {{ $preview['end_date'] }}
                </span>
            </div>

            <div class="row g-3">
                <div class="col-md-2">
                    <div class="formula-item">
                        <div class="label">1. رأس مال الافتتاح</div>
                        <div class="val text-info">{{ number_format($preview['opening_capital'], 2) }}</div>
                        <small class="text-muted" style="font-size:0.68rem;">لقطة {{ $preview['start_date'] }}</small>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="formula-item">
                        <div class="label">2. رأس مال الإقفال</div>
                        <div class="val text-primary-emphasis" style="color: #60a5fa !important;">{{ number_format($preview['closing_capital'], 2) }}</div>
                        <small class="text-muted" style="font-size:0.68rem;">لقطة {{ $preview['end_date'] }}</small>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="formula-item">
                        <div class="label">3. الفارق الإجمالي</div>
                        <div class="val {{ $preview['raw_delta'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $preview['raw_delta'] >= 0 ? '+' : '' }}{{ number_format($preview['raw_delta'], 2) }}
                        </div>
                        <small class="text-muted" style="font-size:0.68rem;">(2) ناقص (1)</small>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="formula-item">
                        <div class="label">4. تعديلات يدوية مستبعدة</div>
                        <div class="val text-warning">{{ number_format($preview['capital_adjustments_net'], 2) }}</div>
                        <small class="text-muted" style="font-size:0.68rem;">زيادات/سحوبات يدوية</small>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="formula-item">
                        <div class="label">5. تدفقات شركاء مستبعدة</div>
                        <div class="val text-secondary">{{ number_format($preview['partner_deposits_net'], 2) }}</div>
                        <small class="text-muted" style="font-size:0.68rem;">إيداعات/سحوبات رأس مال</small>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="formula-item" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.4);">
                        <div class="label text-success fw-bold">الربح الصافي للتوزيع (R)</div>
                        <div class="val {{ $preview['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $preview['gross_profit'] >= 0 ? '+' : '' }}{{ number_format($preview['gross_profit'], 2) }}
                        </div>
                        <small class="text-muted" style="font-size:0.68rem;">قابل للتوزيع على الشركاء</small>
                    </div>
                </div>
            </div>

            @if($preview['gross_profit'] < 0)
            <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-white mt-3 mb-0 py-2 px-3 small rounded-3">
                <i class="fa fa-triangle-exclamation me-1"></i> تنبيه: الدورة حققت خسارة تشغيلية بمقدار {{ number_format(abs($preview['gross_profit']), 2) }} ج. بناءً على شروط النظام، يتحمل كل شريك حصته من الخسارة كاملة من رأس ماله، ولا تُحتسب أي عمولة لأحمد من الخسارة.
            </div>
            @endif
        </div>

        {{-- 📋 نموذج التقفيل واعتماد الحصص --}}
        <form action="{{ route('partners.closing.store') }}" method="POST" id="formExecuteClosing">
            @csrf
            <input type="hidden" name="start_date" value="{{ $preview['start_date'] }}">
            <input type="hidden" name="end_date" value="{{ $preview['end_date'] }}">
            <input type="hidden" name="closing_capital" value="{{ $preview['closing_capital'] }}">

            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3 border-bottom">
                    <div>
                        <h5 class="fw-bold m-0 text-dark">
                            <i class="fa fa-users text-primary me-1"></i> جدول توزيع أرباح الشركاء للدورة
                        </h5>
                        <small class="text-muted">أنت من يحدد الآن لكل شريك: (🔄 هيكمل ويدور أرباحه | 💵 سحب كلي | ⚖️ سحب جزئي وتدوير الباقي)</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-success fw-bold" onclick="setAllDecisions('reinvest')">
                                <i class="fa fa-arrows-rotate me-1"></i> تحديد الكل: هيكمل
                            </button>
                            <button type="button" class="btn btn-outline-primary fw-bold" onclick="setAllDecisions('withdraw_all')">
                                <i class="fa fa-money-bill-wave me-1"></i> تحديد الكل: سحب كلي
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2 border-start ps-2">
                            <label class="form-label m-0 fw-bold small text-nowrap">اسم التقفيلة:</label>
                            <input type="text" name="period_name" class="form-control form-control-sm fw-bold" style="width: 240px;" value="تقفيل شهر {{ \Carbon\Carbon::parse($preview['end_date'])->translatedFormat('F Y') }} (25)">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-closing table-hover m-0 align-middle">
                        <thead>
                            <tr>
                                <th>الشريك</th>
                                <th>رأس المال الحالي</th>
                                <th class="text-center">حصته (s)</th>
                                <th>نصيبه من الأرباح (g)</th>
                                <th>نسبة أحمد</th>
                                <th>عمولة أحمد (a)</th>
                                <th>صافي ربح الشريك (n)</th>
                                <th style="min-width: 230px;">
                                    قرار هذا الشهر للشريك
                                    <small class="d-block text-warning fw-normal" style="font-size:0.7rem;">(هيكمل أو يسحب كلي أو جزئي)</small>
                                </th>
                                <th style="min-width: 240px;">تفاصيل الصرف والخزنة</th>
                                <th style="min-width: 170px;">رأس المال الجديد المتوقع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($preview['partner_lines'] as $line)
                            @php $pid = $line['partner_id']; @endphp
                            <tr id="row_partner_{{ $pid }}" data-pid="{{ $pid }}" data-opencap="{{ $line['opening_capital'] }}" data-net="{{ $line['net_share'] }}">
                                <td>
                                    <strong class="text-dark d-block">{{ $line['name'] }}</strong>
                                    <small class="text-muted">{{ $line['phone'] ?: '—' }}</small>
                                </td>
                                <td class="fw-bold">{{ number_format($line['opening_capital'], 2) }} ج</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        {{ number_format($line['capital_share_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="fw-bold {{ $line['gross_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['gross_share'] >= 0 ? '+' : '' }}{{ number_format($line['gross_share'], 2) }} ج
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                        {{ number_format($line['ahmed_ratio_pct'], 1) }}%
                                    </span>
                                </td>
                                <td class="fw-bold text-warning-emphasis">
                                    +{{ number_format($line['ahmed_commission'], 2) }} ج
                                </td>
                                <td class="fw-bold fs-6 {{ $line['net_share'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $line['net_share'] >= 0 ? '+' : '' }}{{ number_format($line['net_share'], 2) }} ج
                                </td>

                                {{-- عمود القرار المباشر --}}
                                <td>
                                    @if($line['net_share'] > 0)
                                    <div class="btn-group btn-group-sm w-100 shadow-sm" role="group">
                                        <button type="button" class="btn btn-outline-success fw-bold py-2 btn-decision btn-decision-{{ $pid }} active" id="btn_opt_reinvest_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'reinvest', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                            <i class="fa fa-arrows-rotate me-1"></i> هيكمل
                                        </button>
                                        <button type="button" class="btn btn-outline-primary fw-bold py-2 btn-decision btn-decision-{{ $pid }}" id="btn_opt_withdraw_all_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_all', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                            <i class="fa fa-money-bill-wave me-1"></i> سحب كلي
                                        </button>
                                        <button type="button" class="btn btn-outline-warning text-dark fw-bold py-2 btn-decision btn-decision-{{ $pid }}" id="btn_opt_withdraw_partial_{{ $pid }}" onclick="setPartnerDecision({{ $pid }}, 'withdraw_partial', {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                            <i class="fa fa-scale-balanced me-1"></i> سحب جزئي
                                        </button>
                                    </div>
                                    <input type="hidden" name="decisions[{{ $pid }}][payout_action]" id="input_action_{{ $pid }}" value="reinvest">
                                    <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" id="input_withdrawn_{{ $pid }}" value="0">
                                    @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle p-2 text-wrap d-block">
                                        <i class="fa fa-triangle-exclamation me-1"></i> يتحمل الخسارة من رأس ماله (-{{ number_format(abs($line['net_share']), 2) }} ج)
                                    </span>
                                    <input type="hidden" name="decisions[{{ $pid }}][payout_action]" value="reinvest">
                                    <input type="hidden" name="decisions[{{ $pid }}][custom_withdrawn]" value="0">
                                    @endif
                                </td>

                                {{-- تفاصيل الصرف والخزنة --}}
                                <td>
                                    @if($line['net_share'] > 0)
                                        {{-- 1. في حالة هيكمل --}}
                                        <div id="box_reinvest_{{ $pid }}" class="payout-box-{{ $pid }} p-2 rounded-3 bg-success-subtle text-success border border-success-subtle text-center small fw-bold">
                                            <i class="fa fa-circle-check me-1"></i> تدوير بالكامل (+{{ number_format($line['net_share'], 2) }} ج) دون صرف نقدي
                                        </div>

                                        {{-- 2. في حالة سحب كلي --}}
                                        <div id="box_withdraw_all_{{ $pid }}" class="payout-box-{{ $pid }}" style="display:none;">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="small fw-bold text-muted">المسحوب كاش:</span>
                                                <span class="badge bg-danger fs-6">{{ number_format($line['net_share'], 2) }} ج</span>
                                            </div>
                                            <select name="decisions[{{ $pid }}][account_id]" id="acc_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-danger treasury-select" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}">
                                                <option value="">-- اختر خزنة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 3. في حالة سحب جزئي --}}
                                        <div id="box_withdraw_partial_{{ $pid }}" class="payout-box-{{ $pid }}" style="display:none;">
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold m-0 text-muted">المبلغ المراد سحبه كاش:</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="1" max="{{ $line['net_share'] }}" id="inp_partial_val_{{ $pid }}" class="form-control fw-bold text-danger border-warning" value="{{ round($line['net_share'] / 2, 2) }}" placeholder="المبلغ المسحوب" oninput="onPartialInput({{ $pid }}, {{ $line['opening_capital'] }}, {{ $line['net_share'] }})">
                                                    <span class="input-group-text bg-white fw-bold">ج</span>
                                                </div>
                                                <div class="small text-success fw-bold mt-1" id="partial_rollover_badge_{{ $pid }}">
                                                    <i class="fa fa-rotate me-1"></i> المتبقي للتدوير: {{ number_format($line['net_share'] - round($line['net_share'] / 2, 2), 2) }} ج
                                                </div>
                                            </div>
                                            <select id="acc_partial_sel_{{ $pid }}" class="form-select form-select-sm fw-bold border-warning treasury-select-partial" data-pid="{{ $pid }}" data-name="{{ $line['name'] }}" onchange="syncPartialAccount({{ $pid }})">
                                                <option value="">-- اختر خزنة الصرف --</option>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance) }} ج)</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="text-center text-muted small py-2">
                                            — لا يوجد صرف نقدي —
                                        </div>
                                    @endif
                                </td>

                                {{-- رأس المال الجديد المتوقع --}}
                                <td class="fw-bold fs-6" id="cell_cap_{{ $pid }}">
                                    <div class="text-primary fw-bold" id="new_cap_display_{{ $pid }}">
                                        {{ number_format($line['closing_capital'], 2) }} ج
                                    </div>
                                    <small id="new_cap_sub_{{ $pid }}" class="d-block {{ $line['net_share'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:0.72rem;">
                                        @if($line['net_share'] >= 0)
                                            ▲ ينمو بإضافة صافي الربح (+{{ number_format($line['net_share'], 2) }} ج)
                                        @else
                                            ▼ ينقص بمقدار الخسارة (-{{ number_format(abs($line['net_share']), 2) }} ج)
                                        @endif
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">لا يوجد شركاء نشطين لتوزيع الأرباح عليهم.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light p-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small m-0 me-2"><i class="fa fa-note-sticky text-secondary me-1"></i> ملاحظات التقفيل:</label>
                            <input type="text" name="notes" class="form-control form-control-sm mt-1" placeholder="مثال: تم إغلاق دورة 25 أكتوبر بعد جرد المحافظ وحساب مبيعات الشهر...">
                        </div>
                        <div class="col-md-4 text-start">
                            <button type="submit" class="btn btn-success fw-bold px-4 py-2 shadow-sm w-100">
                                <i class="fa fa-lock me-1"></i> اعتماد التقفيل وتحديث الخزائن 🔒
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- 👑 كارت دخل أحمد المؤسس الإجمالي من الدورة --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle fs-3">
                            <i class="fa fa-crown"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold m-0 text-dark">دخل أحمد (المؤسس)</h5>
                            <small class="text-muted">المحصل من رأس ماله + عمولات الإدارة</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">أرباح رأس مال أحمد الشخصي</small>
                    <strong class="fs-5 text-dark">{{ number_format($preview['ahmed']['own_profit'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">نسبة رأس ماله: {{ number_format($preview['ahmed']['capital_share_pct'], 2) }}%</div>
                </div>

                <div class="col-md-3 text-center border-start">
                    <small class="text-muted d-block mb-1">عمولات الإدارة المحصلة من الشركاء</small>
                    <strong class="fs-5 text-warning-emphasis">+{{ number_format($preview['ahmed']['commissions'], 2) }} ج</strong>
                    <div style="font-size:0.75rem;" class="text-muted">مجموع نسب أحمد المتفق عليها</div>
                </div>

                <div class="col-md-3 text-center border-start bg-success bg-opacity-10 py-3 rounded-3">
                    <small class="text-success fw-bold d-block mb-1">إجمالي صافي دخل أحمد من الدورة</small>
                    <strong class="fs-4 text-success">{{ number_format($preview['ahmed']['total_profit'], 2) }} ج</strong>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /**
     * تغيير قرار الشريك يدوياً (هيكمل / سحب كلي / سحب جزئي)
     */
    function setPartnerDecision(pid, action, openCap, netShare) {
        // 1. تفعيل الزر المختار وإلغاء تفعيل البقية
        document.querySelectorAll('.btn-decision-' + pid).forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('btn_opt_' + action + '_' + pid);
        if (activeBtn) activeBtn.classList.add('active');

        // 2. تحديث الحقل المخفي للقرار
        const inputAction = document.getElementById('input_action_' + pid);
        if (inputAction) inputAction.value = action;

        const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
        const accSel = document.getElementById('acc_sel_' + pid);
        const accPartialSel = document.getElementById('acc_partial_sel_' + pid);

        // 3. إخفاء كافة مربعات تفاصيل الصرف وإظهار المطلوب
        document.querySelectorAll('.payout-box-' + pid).forEach(box => box.style.display = 'none');

        let withdrawn = 0;
        let reinvested = netShare;

        if (action === 'withdraw_all') {
            const box = document.getElementById('box_withdraw_all_' + pid);
            if (box) box.style.display = 'block';
            withdrawn = netShare;
            reinvested = 0;
            if (inputWithdrawn) inputWithdrawn.value = withdrawn.toFixed(2);
            if (accSel) accSel.required = true;
        } else if (action === 'withdraw_partial') {
            const box = document.getElementById('box_withdraw_partial_' + pid);
            if (box) box.style.display = 'block';
            const inp = document.getElementById('inp_partial_val_' + pid);
            let val = parseFloat(inp ? inp.value : 0) || (netShare / 2);
            if (val > netShare) val = netShare;
            if (val < 0) val = 0;
            withdrawn = val;
            reinvested = netShare - withdrawn;
            if (inputWithdrawn) inputWithdrawn.value = withdrawn.toFixed(2);
            if (accPartialSel) accPartialSel.required = true;
            if (accSel && accPartialSel) accSel.value = accPartialSel.value;
        } else {
            // reinvest (هيكمل)
            const box = document.getElementById('box_reinvest_' + pid);
            if (box) box.style.display = 'block';
            withdrawn = 0;
            reinvested = netShare;
            if (inputWithdrawn) inputWithdrawn.value = '0';
            if (accSel) {
                accSel.required = false;
                accSel.value = '';
            }
        }

        // 4. تحديث عرض رأس المال الجديد المتوقع فورياً
        updateNewCapDisplay(pid, openCap, reinvested, action);
    }

    /**
     * تحديث فوري عند كتابة مبلغ السحب الجزئي
     */
    function onPartialInput(pid, openCap, netShare) {
        const inp = document.getElementById('inp_partial_val_' + pid);
        let val = parseFloat(inp ? inp.value : 0) || 0;
        if (val > netShare) val = netShare;
        if (val < 0) val = 0;

        const inputWithdrawn = document.getElementById('input_withdrawn_' + pid);
        if (inputWithdrawn) inputWithdrawn.value = val.toFixed(2);

        const rem = netShare - val;
        const badge = document.getElementById('partial_rollover_badge_' + pid);
        if (badge) {
            badge.innerHTML = '<i class="fa fa-rotate me-1"></i> المتبقي للتدوير: ' + Number(rem).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }

        updateNewCapDisplay(pid, openCap, rem, 'withdraw_partial');
    }

    /**
     * مزامنة الخزنة المختارة في السحب الجزئي مع الحقل الرئيسي
     */
    function syncPartialAccount(pid) {
        const accPartialSel = document.getElementById('acc_partial_sel_' + pid);
        const accSel = document.getElementById('acc_sel_' + pid);
        if (accSel && accPartialSel) {
            accSel.value = accPartialSel.value;
        }
    }

    /**
     * تعيين قرار واحد لجميع الشركاء دفعة واحدة (اختصار سريع)
     */
    function setAllDecisions(action) {
        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const openCap = parseFloat(tr.getAttribute('data-opencap')) || 0;
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            if (netShare > 0) {
                setPartnerDecision(pid, action, openCap, netShare);
            }
        });
    }

    /**
     * دالة مساعدة لتحديث خانة رأس المال والوصف الملون
     */
    function updateNewCapDisplay(pid, openCap, reinvested, action) {
        const newCap = Math.max(0, openCap + reinvested);
        const capDisplay = document.getElementById('new_cap_display_' + pid);
        const capSub = document.getElementById('new_cap_sub_' + pid);

        if (capDisplay) {
            capDisplay.innerText = Number(newCap).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج';
        }
        if (capSub) {
            if (action === 'reinvest') {
                capSub.className = 'd-block text-success';
                capSub.innerText = '▲ ينمو بإضافة كامل الربح (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            } else if (action === 'withdraw_all') {
                capSub.className = 'd-block text-secondary';
                capSub.innerText = '◼ ثابت (تم سحب كامل الربح نقداً)';
            } else {
                capSub.className = 'd-block text-primary';
                capSub.innerText = '▲ ينمو بالمتبقي المدور (+' + Number(reinvested).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ج)';
            }
        }
    }

    /**
     * التحقق عند إرسال النموذج واعتماد التقفيل
     */
    document.getElementById('formExecuteClosing').addEventListener('submit', function (e) {
        let hasError = false;
        let errorMsg = '';

        document.querySelectorAll('tr[data-pid]').forEach(tr => {
            const pid = tr.getAttribute('data-pid');
            const netShare = parseFloat(tr.getAttribute('data-net')) || 0;
            if (netShare <= 0) return;

            const actionInp = document.getElementById('input_action_' + pid);
            const action = actionInp ? actionInp.value : 'reinvest';

            if (action === 'withdraw_all') {
                const accSel = document.getElementById('acc_sel_' + pid);
                if (!accSel || !accSel.value) {
                    const partnerName = tr.querySelector('strong').innerText;
                    errorMsg = 'يرجى تحديد الخزنة المصروف منها أرباح الشريك «' + partnerName + '» (سحب كلي).';
                    hasError = true;
                    if (accSel) accSel.focus();
                }
            } else if (action === 'withdraw_partial') {
                const accPartialSel = document.getElementById('acc_partial_sel_' + pid);
                const accSel = document.getElementById('acc_sel_' + pid);
                const selectedVal = (accPartialSel && accPartialSel.value) || (accSel && accSel.value);
                if (!selectedVal) {
                    const partnerName = tr.querySelector('strong').innerText;
                    errorMsg = 'يرجى تحديد الخزنة المصروف منها أرباح الشريك «' + partnerName + '» (سحب جزئي).';
                    hasError = true;
                    if (accPartialSel) accPartialSel.focus();
                }
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('⚠️ ' + errorMsg);
            return false;
        }

        if (!confirm('هل راجعت قرارات كل الشركاء وتريد اعتماد وإغلاق دورة الشهر رسمياً وتحديث الأرصدة والخزائن؟')) {
            e.preventDefault();
            return false;
        }
    });
</script>

</body>
</html>
