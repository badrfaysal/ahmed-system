<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف تحصيل وصرف الجمعيات - شهر {{ $monthDate->translatedFormat('F Y') }}</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 6mm 10mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Cairo', sans-serif;
            background: #fff;
            color: #0f172a;
            padding: 0;
            margin: 0;
            font-size: 12px;
            line-height: 1.3;
        }

        .print-container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* ── Compact Header ── */
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-title {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f766e;
            margin: 0;
            line-height: 1.2;
        }
        .report-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            text-align: center;
        }
        .meta-info {
            font-size: 0.75rem;
            color: #64748b;
            text-align: left;
            line-height: 1.3;
        }

        /* ── Summary Cards ── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        .sum-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
            text-align: center;
            background: #fff;
        }
        .sum-box.red { background: #fef2f2; border-color: #fca5a5; }
        .sum-box.green { background: #f0fdf4; border-color: #86efac; }
        .sum-box.teal { background: #f0fdfa; border-color: #99f6e4; }
        .sum-box.blue { background: #eff6ff; border-color: #bfdbfe; }
        .sum-box .lbl { font-size: 0.72rem; font-weight: 700; color: #475569; margin-bottom: 2px; }
        .sum-box .val { font-size: 1.1rem; font-weight: 900; margin-bottom: 1px; }
        .sum-box .sub { font-size: 0.68rem; color: #64748b; }

        /* ── Section Titles ── */
        .sec-title {
            font-size: 0.88rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* ── Super Compact Tables ── */
        .table-compact {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 0.78rem;
        }
        .table-compact th, .table-compact td {
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            text-align: right;
            vertical-align: middle;
        }
        .table-compact th {
            background-color: #f1f5f9;
            font-weight: 800;
            color: #1e293b;
        }
        .table-compact tr:nth-child(even) { background-color: #f8fafc; }
        .table-compact tfoot td {
            font-weight: 800;
            background-color: #f1f5f9;
        }

        /* ── Signatures ── */
        .sign-area {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 18px;
            text-align: center;
            font-weight: 700;
            font-size: 0.78rem;
            page-break-inside: avoid;
        }
        .sign-line {
            border-top: 1px dashed #64748b;
            padding-top: 4px;
            margin-top: 25px;
        }

        @media print {
            body { padding: 0 !important; margin: 0 !important; }
            .no-print { display: none !important; }
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body>

<div class="print-container">

    {{-- Controls --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print pt-2">
        <a href="{{ route('cashflows.index', ['month' => $selectedMonth]) }}" class="btn btn-sm btn-outline-secondary px-3">
            <i class="fa fa-arrow-right me-1"></i> رجوع للشاشة
        </a>
        <button onclick="window.print()" class="btn btn-sm btn-success px-4 fw-bold">
            <i class="fa fa-print me-1"></i> طباعة الكشف (A4)
        </button>
    </div>

    {{-- Header --}}
    <div class="print-header">
        <div>
            <h1 class="company-title">{{ $companyName }}</h1>
            <div style="font-size:0.75rem; color:#64748b; font-weight:600;">إدارة الجمعيات — التحصيل الشهري وصرف الأدوار</div>
        </div>
        <div>
            <h2 class="report-title">كشف تحصيل وصرف الجمعيات</h2>
            <div style="font-size:0.8rem; font-weight:800; color:#0f766e; text-align:center;">شهر: {{ $monthDate->locale('ar')->isoFormat('MMMM YYYY') }}</div>
        </div>
        <div class="meta-info">
            <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y/m/d') }}</div>
            <div><strong>المستخدم:</strong> {{ session('auth_user')->name ?? 'الإدارة' }}</div>
        </div>
    </div>

    {{-- Summary KPIs --}}
    @php
        $paidCnt   = $monthInstallments->where('is_paid', true)->count();
        $unpaidCnt = $monthInstallments->where('is_paid', false)->count();
        $lateCnt   = $monthInstallments->filter(fn ($i) => $i->status['key'] === 'late')->count();
        $netExpected = $totalDue - $totalPayout;
        $netActual   = $totalCollected - $totalPaidOut;
    @endphp
    <div class="summary-grid">
        <div class="sum-box green">
            <div class="lbl">المطلوب تحصيله (الداخل)</div>
            <div class="val text-success">{{ number_format($totalDue, 0) }} ج</div>
            <div class="sub">اتحصل: <b>{{ number_format($totalCollected, 0) }}</b> | متبقي: <b>{{ number_format(max(0, $totalDue - $totalCollected), 0) }}</b></div>
        </div>
        <div class="sum-box red">
            <div class="lbl">القبض المستحق صرفه (الخارج)</div>
            <div class="val text-danger">{{ number_format($totalPayout, 0) }} ج</div>
            <div class="sub">اتصرف: <b>{{ number_format($totalPaidOut, 0) }}</b> | متبقي: <b>{{ number_format(max(0, $totalPayout - $totalPaidOut), 0) }}</b></div>
        </div>
        <div class="sum-box blue">
            <div class="lbl">موقف الأعضاء (دفعوا / لسه)</div>
            <div class="val text-primary">{{ $paidCnt }} / {{ $monthInstallments->count() }}</div>
            <div class="sub">لسه: <b>{{ $unpaidCnt }}</b> @if($lateCnt > 0) | متأخر: <b class="text-danger">{{ $lateCnt }}</b> @endif</div>
        </div>
        <div class="sum-box teal">
            <div class="lbl">صافي الشهر (التحصيل - الصرف)</div>
            <div class="val {{ $netExpected >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $netExpected >= 0 ? '+' : '' }}{{ number_format($netExpected, 0) }} ج
            </div>
            <div class="sub">الفعلي: <b>{{ $netActual >= 0 ? '+' : '' }}{{ number_format($netActual, 0) }} ج</b></div>
        </div>
    </div>

    {{-- Section 1: Inflow / Member Collections --}}
    <div class="sec-title">
        <span><i class="fa-solid fa-hand-holding-dollar text-success me-1"></i> أولاً: تحصيل أقساط الأعضاء لهذا الشهر (مين دفع ومين لسه)</span>
        <span class="text-success">المطلوب: {{ number_format($totalDue, 2) }} ج | المحصل: {{ number_format($totalCollected, 2) }} ج</span>
    </div>
    <table class="table-compact">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 22%;">الجمعية</th>
                <th style="width: 6%;">الدور</th>
                <th style="width: 22%;">العضو</th>
                <th style="width: 13%;">الهاتف</th>
                <th style="width: 11%;">القسط</th>
                <th style="width: 22%;">الحالة والخزنة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthInstallments as $idx => $inst)
                @php $st = $inst->status; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $inst->jamia_name }}</strong></td>
                    <td class="text-center fw-bold">{{ $inst->share_number }}</td>
                    <td class="fw-bold">{{ $inst->share_label }}</td>
                    <td dir="ltr" style="text-align:right;">{{ $inst->member_phone ?: '—' }}</td>
                    <td class="text-success fw-bold">{{ number_format($inst->amount, 2) }} ج</td>
                    <td>
                        @if($st['key'] === 'paid' || $st['key'] === 'paid_late')
                            <span class="text-success fw-bold">✔ دفع ({{ \Carbon\Carbon::parse($inst->paid_at)->format('d/m') }}) — {{ $inst->paid_account_name ?? 'الخزنة' }}</span>
                            @if($st['key'] === 'paid_late') <span class="text-danger">(بعد الميعاد)</span> @endif
                        @elseif($st['key'] === 'late')
                            <span class="text-danger fw-bold">✖ متأخر ({{ $st['days'] }} يوم)</span>
                        @elseif($st['key'] === 'open')
                            <span class="text-warning fw-bold">⏳ لسه ما دفعش</span>
                        @else
                            <span class="text-muted">لم يبدأ التحصيل</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-2">لا توجد أقساط مطلوبة من الأعضاء لهذا الشهر.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end">الإجمالي المطلوب تحصيله:</td>
                <td class="text-success">{{ number_format($totalDue, 2) }} ج</td>
                <td>محصل فعلياً: {{ number_format($totalCollected, 2) }} ج</td>
            </tr>
        </tfoot>
    </table>

    {{-- Section 2: Outflow / Payouts --}}
    <div class="sec-title">
        <span><i class="fa-solid fa-sack-dollar text-danger me-1"></i> ثانياً: أدوار القبض لهذا الشهر (صرف الجمعية)</span>
        <span class="text-danger">إجمالي الصرف للأعضاء: {{ number_format($totalPayout, 2) }} ج</span>
    </div>
    <table class="table-compact">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 24%;">الجمعية</th>
                <th style="width: 6%;">الدور</th>
                <th style="width: 24%;">صاحب الدور</th>
                <th style="width: 14%;">قيمة القبض</th>
                <th style="width: 28%;">حالة الصرف</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthShares as $idx => $s)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $s->jamia_name }}</strong></td>
                    <td class="text-center fw-bold">{{ $s->share_number }}</td>
                    <td class="fw-bold">
                        {{ $s->share_label }}
                        @if($s->is_company) <span class="text-primary">(سهم الشركة)</span> @endif
                        @if($s->is_transferred)
                            <div style="font-size:0.68rem; color:#4338ca;">
                                بديل الشركة — حق مسترد: {{ number_format($s->company_claim_amount, 0) }} ج
                                @if($s->is_claim_recovered)
                                    (استُرد في {{ $s->recovered_account_name ?? 'الخزنة' }})
                                @else
                                    (لم يُسترد)
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="text-danger fw-bold">{{ number_format($s->payout_amount, 2) }} ج</td>
                    <td>
                        @if($s->is_collected)
                            <span class="text-success fw-bold">
                                ✔ {{ $s->is_company ? 'استلام الشركة' : 'تم الصرف' }}
                                ({{ $s->collected_at ? \Carbon\Carbon::parse($s->collected_at)->format('d/m') : '' }})
                                @if($s->collected_account_name) — {{ $s->is_company ? 'في:' : 'من:' }} {{ $s->collected_account_name }} @endif
                            </span>
                        @else
                            <span class="text-warning fw-bold">⏳ {{ $s->is_company ? 'دور الشركة (لم يُسجّل)' : 'لم يصرف بعد' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-2">لا توجد أدوار قبض مسجلة لهذا الشهر.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end">إجمالي المنصرف للأعضاء:</td>
                <td class="text-danger">{{ number_format($totalPayout, 2) }} ج</td>
                <td>اتصرف فعلياً: {{ number_format($totalPaidOut, 2) }} ج</td>
            </tr>
        </tfoot>
    </table>

    {{-- Signatures --}}
    <div class="sign-area">
        <div>
            <div>مسؤول التحصيل</div>
            <div class="sign-line">التوقيع: .....................</div>
        </div>
        <div>
            <div>المراجعة والحسابات</div>
            <div class="sign-line">التوقيع: .....................</div>
        </div>
        <div>
            <div>اعتماد الإدارة</div>
            <div class="sign-line">التوقيع: .....................</div>
        </div>
    </div>

</div>

</body>
</html>
