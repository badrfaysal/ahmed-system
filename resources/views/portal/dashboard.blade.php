@extends('portal.layout')

@section('title', 'صفحتي')

@section('extra-head')
<style>
    /* تنسيق عمودي ضيّق — أسهل في القراءة */
    .portal-narrow { max-width: 640px; margin: 0 auto; }

    .stat-mini {
        background: var(--c-surface); border-radius: var(--r-md); padding: 14px 18px;
        border: 1px solid var(--c-border); box-shadow: var(--shadow-xs);
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .stat-mini .left { display: flex; align-items: center; gap: 12px; }
    .stat-mini .icon {
        width: 42px; height: 42px; border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; color: #fff; flex-shrink: 0;
    }
    .stat-mini .num { font-size: 1.4rem; font-weight: 700; color: var(--c-navy); line-height: 1; font-feature-settings: 'tnum'; }
    .stat-mini .lbl { font-size: 0.82rem; font-weight: 500; color: var(--c-text-muted); }
    .ic-blue { background: var(--c-info); }
    .ic-amber { background: var(--c-warning); }
    .ic-green { background: var(--c-success); }
    .ic-red { background: var(--c-danger); }

    .contract-card {
        background: var(--c-surface); border-radius: var(--r-md);
        border: 1px solid var(--c-border); border-right: 3px solid var(--c-info);
        padding: 18px; margin-bottom: 14px;
        transition: var(--t-fast); box-shadow: var(--shadow-xs);
    }
    .contract-card.paid { border-right-color: var(--c-success); background: var(--c-success-bg); }
    .contract-card.overdue { border-right-color: var(--c-danger); background: var(--c-danger-bg); }
    .contract-card:hover { box-shadow: var(--shadow-sm); }

    .ct-top { display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
    .ct-title { font-weight: 600; color: var(--c-navy); font-size: 1.02rem; }
    .ct-id { font-size: 0.74rem; color: var(--c-text-soft); font-weight: 500; direction: ltr; }

    .ct-progress {
        height: 7px; background: var(--c-border);
        border-radius: 999px; overflow: hidden; margin: 12px 0 6px;
    }
    .ct-progress-bar {
        height: 100%;
        background: var(--c-success);
        transition: width .5s;
    }

    /* صفوف عمودية مرصوصة بدل grid 3 أعمدة */
    .ct-stats { display: flex; flex-direction: column; gap: 6px; margin-top: 10px; }
    .ct-stats > div {
        background: var(--c-bg); border-radius: var(--r-sm);
        padding: 10px 14px;
        border: 1px solid var(--c-border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .ct-stats .l { font-size: 0.82rem; font-weight: 500; color: var(--c-text-muted); }
    .ct-stats .v { font-weight: 700; font-size: 1rem; color: var(--c-navy); font-feature-settings: 'tnum'; }

    .ct-actions { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }

    .next-due-banner {
        background: var(--c-warning-bg);
        border: 1px solid var(--c-warning);
        border-radius: var(--r-md);
        padding: 14px 18px; margin-bottom: 18px;
        display: flex; align-items: center; gap: 12px;
        color: var(--c-warning);
    }
    .next-due-banner .icon { font-size: 1.6rem; }
    .next-due-banner .text { font-weight: 500; }
    .next-due-banner .date { font-weight: 600; font-size: 1.05rem; }
</style>
@endsection

@section('content')
<div class="portal-narrow">
    <div class="mb-4">
        <h3 class="fw-bold mb-1" style="color: var(--c-navy);">أهلاً بيك، {{ $cust['name'] }}</h3>
        <p class="muted-pro mb-1 small">ده ملخص حسابك معانا</p>
        <div class="muted-pro small">
            <i class="fa-regular fa-clock me-1"></i>
            آخر دخول: {{ \Carbon\Carbon::parse($cust['login_at'])->format('Y/m/d h:i A') }}
        </div>
    </div>

    {{-- Next Due Banner --}}
    @if($stats['next_due'] && $stats['open_contracts'] > 0)
        @php
            $nextDue = \Carbon\Carbon::parse($stats['next_due']);
            $daysLeft = (int) now()->startOfDay()->diffInDays($nextDue, false);
        @endphp
        <div class="next-due-banner">
            <div class="icon"><i class="fa fa-calendar-day"></i></div>
            <div>
                <div class="text">القسط الجاي مستحق:</div>
                <div class="date">
                    {{ $nextDue->translatedFormat('l، j F Y') }}
                    @if($daysLeft > 0)
                        <small class="ms-2">(باقي {{ $daysLeft }} يوم)</small>
                    @elseif($daysLeft === 0)
                        <small class="ms-2">(اليوم)</small>
                    @else
                        <small class="ms-2" style="color: var(--c-danger);">(متأخر {{ abs($daysLeft) }} يوم)</small>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Stats — مرصوصة عمودياً --}}
    <div class="d-flex flex-column gap-2 mb-4">
        <div class="stat-mini">
            <div class="left">
                <div class="icon ic-blue"><i class="fa fa-file-contract"></i></div>
                <div class="lbl">إجمالي العقود</div>
            </div>
            <div class="num">{{ $stats['total_contracts'] }}</div>
        </div>
        <div class="stat-mini">
            <div class="left">
                <div class="icon ic-amber"><i class="fa fa-clock-rotate-left"></i></div>
                <div class="lbl">عقود نشطة</div>
            </div>
            <div class="num">{{ $stats['open_contracts'] }}</div>
        </div>
        <div class="stat-mini">
            <div class="left">
                <div class="icon ic-red"><i class="fa fa-money-bill-trend-up"></i></div>
                <div class="lbl">المتبقي عليك (ج)</div>
            </div>
            <div class="num">{{ number_format($stats['total_remaining'], 0) }}</div>
        </div>
        <div class="stat-mini">
            <div class="left">
                <div class="icon ic-green"><i class="fa fa-check-double"></i></div>
                <div class="lbl">إجمالي اللي دفعته (ج)</div>
            </div>
            <div class="num">{{ number_format($stats['total_paid'], 0) }}</div>
        </div>
    </div>

    <h5 class="fw-bold mb-3" style="color: var(--c-navy);">
        <i class="fa fa-list-check me-1" style="color: var(--c-accent);"></i> عقودك ({{ $contracts->count() }})
    </h5>

    @forelse($contracts as $contract)
        @php
            $isPaid = $contract->remaining_balance <= 0;
            $isInstallment = $contract->installment_months > 0;
            $totalContract = (float) $contract->total_after_interest;
            $totalPaid     = max(0, $totalContract - (float) $contract->remaining_balance);
            $progress      = $totalContract > 0 ? min(100, ($totalPaid / $totalContract) * 100) : 100;
            $isOverdue = false;
            if (!$isPaid && $isInstallment) {
                $lastPay = isset($payments[$contract->id]) ? $payments[$contract->id]->max('payment_date') : null;
                $reference = $lastPay ?: $contract->created_at;
                try {
                    if ($reference && \Carbon\Carbon::parse($reference)->diffInDays(now()) > 35) {
                        $isOverdue = true;
                    }
                } catch (\Throwable $e) {}
            }
        @endphp

        <div class="contract-card {{ $isPaid ? 'paid' : ($isOverdue ? 'overdue' : '') }}">
            <div class="ct-top">
                <div>
                    <div class="ct-title">{{ $contract->product_name }}</div>
                    <div class="ct-id">رقم العقد: #{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                    @if($isPaid)
                        <span class="pill-pro pill-success"><i class="fa fa-check"></i> مسدد بالكامل</span>
                    @else
                        <span class="pill-pro pill-warning"><i class="fa-regular fa-clock"></i> نشط</span>
                    @endif
                    @if($isInstallment)
                        <span class="pill-pro pill-neutral">{{ $contract->installment_months }} شهر</span>
                    @else
                        <span class="pill-pro pill-neutral">كاش</span>
                    @endif
                </div>
            </div>

            <div class="ct-progress">
                <div class="ct-progress-bar" style="width: {{ $progress }}%;"></div>
            </div>
            <div class="text-end muted-pro small">{{ round($progress) }}% مسدد</div>

            <div class="ct-stats">
                <div>
                    <div class="l">إجمالي العقد</div>
                    <div class="v">{{ number_format($contract->total_after_interest, 0) }} ج</div>
                </div>
                <div>
                    <div class="l">المدفوع</div>
                    <div class="v" style="color: var(--c-success);">{{ number_format($totalPaid, 0) }} ج</div>
                </div>
                <div>
                    <div class="l">المتبقي</div>
                    <div class="v" style="color: {{ $isPaid ? 'var(--c-success)' : 'var(--c-danger)' }};">
                        {{ number_format($contract->remaining_balance, 0) }} ج
                    </div>
                </div>
            </div>

            @if($isInstallment && !$isPaid)
                <div style="
                    margin-top: 10px; padding: 10px 12px;
                    background: var(--c-info-bg); border: 1px solid rgba(30,95,164,0.2);
                    border-radius: var(--r-sm); font-size: 0.86rem; font-weight: 500; color: var(--c-info);
                ">
                    <i class="fa fa-calendar-check me-1"></i>
                    القسط الشهري: <strong style="font-weight: 600;">{{ number_format($contract->monthly_installment, 0) }} ج</strong>
                    يستحق يوم <strong style="font-weight: 600;">{{ (int) $contract->due_day }}</strong> من كل شهر
                </div>
            @endif

            <div class="ct-actions">
                <a href="{{ route('portal.contract', $contract->id) }}" class="btn-pro btn-pro-primary btn-pro-sm">
                    <i class="fa fa-eye"></i> عرض التفاصيل الكاملة
                </a>
            </div>
        </div>
    @empty
        <div class="empty-pro">
            <i class="fa-regular fa-folder-open"></i>
            <h5>مفيش عقود في حسابك</h5>
            <p>تواصل مع الشركة لو حضرتك عميل بالفعل وفي مشكلة في الربط.</p>
        </div>
    @endforelse
</div>
@endsection
