@php
    $initial = mb_substr($row->customer_name, 0, 1, 'UTF-8');
    $phone   = trim((string) $row->customer_phone);
    $waPhone = preg_replace('/\D/', '', $phone);
    if ($waPhone && str_starts_with($waPhone, '0')) {
        $waPhone = '20' . substr($waPhone, 1);
    }
    $jsData = json_encode([
        'id'             => $row->id,
        'customer_name'  => $row->customer_name,
        'customer_phone' => $row->customer_phone,
        'product_type'   => $row->product_type,
        'inquiry'        => $row->inquiry,
        'contact_notes'  => $row->contact_notes,
    ], JSON_UNESCAPED_UNICODE);
@endphp

<div class="inquiry-card {{ $isDone ? 'done' : '' }}" style="
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 1px solid var(--c-border);
    border-right: 3px solid {{ $isDone ? 'var(--c-success)' : 'var(--c-warning)' }};
    padding: 16px 18px; margin-bottom: 12px;
    transition: var(--t-fast);
">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="
                width: 44px; height: 44px; border-radius: 50%;
                background: {{ $isDone ? 'var(--c-success)' : 'var(--c-navy)' }};
                color: #fff; display: flex; align-items: center; justify-content: center;
                font-size: 1.1rem; font-weight: 600; flex-shrink: 0;
            ">{{ $initial }}</div>
            <div>
                <div style="font-size: 1rem; font-weight: 600; color: var(--c-navy); line-height: 1.3;">{{ $row->customer_name }}</div>
                <div style="font-size: 0.84rem; color: var(--c-text-muted); direction: ltr; text-align: start;">
                    @if($phone)
                        <a href="tel:{{ $phone }}" style="color: var(--c-info); text-decoration: none;">
                            <i class="fa fa-phone fa-rotate-90 me-1"></i>{{ $phone }}
                        </a>
                    @else
                        <span class="muted-pro"><i class="fa fa-phone-slash me-1"></i>لا يوجد رقم</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            @if($row->product_type)
                <span class="pill-pro pill-info"><i class="fa fa-tag"></i>{{ $row->product_type }}</span>
            @endif
            <span class="pill-pro pill-neutral"><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($row->created_at)->format('Y/m/d - h:i A') }}</span>
            <span class="pill-pro pill-warning"><i class="fa fa-user-pen"></i> سجّل: {{ $row->created_by_name ?? 'غير معروف' }}</span>
            @if($isDone && $row->contacted_by_name)
                <span class="pill-pro pill-success">
                    <i class="fa fa-headset"></i> تواصل: {{ $row->contacted_by_name }}
                    @if($row->contacted_at)
                        · {{ \Carbon\Carbon::parse($row->contacted_at)->format('Y/m/d h:i A') }}
                    @endif
                </span>
            @endif
        </div>
    </div>

    <div style="background: var(--c-bg); border-radius: var(--r-md); padding: 12px 14px; margin: 10px 0; color: var(--c-text); font-weight: 400; line-height: 1.7; border: 1px solid var(--c-border);">
        <strong>الطلب:</strong> {{ $row->inquiry }}
    </div>

    @if($isDone && $row->contact_notes)
        <div style="background: var(--c-success-bg); border: 1px solid rgba(45,134,89,0.2); border-radius: var(--r-md); padding: 10px 14px; margin-top: 8px; color: var(--c-success); font-weight: 500; font-size: 0.9rem;">
            <strong style="font-weight: 600;">ملاحظة التواصل:</strong> {{ $row->contact_notes }}
        </div>
    @endif

    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px;">
        @if(!$isDone)
            <button class="btn-pro btn-pro-success btn-pro-sm" onclick="openConfirmContact({{ $row->id }})">
                <i class="fa fa-check"></i> تأكيد التواصل
            </button>
        @else
            <button class="btn-pro btn-pro-sm" style="background: var(--c-warning); color: #fff;" onclick="undoContact({{ $row->id }})">
                <i class="fa fa-rotate-left"></i> إرجاع لقائمة الانتظار
            </button>
        @endif

        @if($waPhone)
            <a class="btn-pro btn-pro-sm" style="background: #25D366; color: #fff;" href="https://wa.me/{{ $waPhone }}" target="_blank">
                <i class="fa-brands fa-whatsapp"></i> واتساب
            </a>
        @endif

        <button class="btn-pro btn-pro-primary btn-pro-sm" onclick='editInquiry({!! $jsData !!})'>
            <i class="fa fa-pen"></i> تعديل
        </button>

        <button class="btn-pro btn-pro-danger btn-pro-sm" onclick="deleteInquiry({{ $row->id }})">
            <i class="fa fa-trash"></i> حذف
        </button>
    </div>
</div>
