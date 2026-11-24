@php
    $assigneeName = trim((string) ($row->assigned_to_name ?? ''));
    $hasTitle = filled($row->customer_name) && trim((string) $row->customer_name) !== trim((string) $row->inquiry);
    $initialSource = $assigneeName !== '' ? $assigneeName : ($row->customer_name ?: 'م');
    $initial = mb_substr($initialSource, 0, 1, 'UTF-8');

    $taskDateValue = !empty($row->task_date)
        ? (string) $row->task_date
        : (\Carbon\Carbon::parse($row->created_at)->toDateString());

    $normKey = strtr(mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $assigneeName)), 'UTF-8'), ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ى' => 'ي', 'ة' => 'ه']);
    $normKey = (string) preg_replace('/ي{2,}/u', 'ي', $normKey);
    $resolvedPhone = trim((string) ($row->assigned_phone ?? ''));
    if ($resolvedPhone === '' && isset($employeePhones) && $normKey !== '' && !empty($employeePhones[$normKey])) {
        $resolvedPhone = (string) $employeePhones[$normKey];
    }

    $jsData = json_encode([
        'id'               => $row->id,
        'title'            => $hasTitle ? $row->customer_name : null,
        'raw_title'        => $row->customer_name,
        'details'          => $row->inquiry,
        'assigned_to'      => $row->assigned_to,
        'assigned_to_name' => $assigneeName,
        'assigned_phone'   => $resolvedPhone !== '' ? $resolvedPhone : null,
        'task_date'        => $taskDateValue,
        'contact_notes'    => $row->contact_notes,
        'created_at'       => \Carbon\Carbon::parse($row->created_at)->format('Y/m/d - h:i A'),
        'created_by_name'  => $row->created_by_name,
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
@endphp

<div class="inquiry-card {{ $isDone ? 'done' : '' }}" style="
    background: var(--c-surface);
    border-radius: var(--r-md);
    border: 1px solid var(--c-border);
    border-right: 3px solid {{ $isDone ? 'var(--c-success)' : 'var(--c-warning)' }};
    padding: 16px 18px; margin-bottom: 12px;
">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 50%;
                background: {{ $isDone ? 'var(--c-success)' : 'var(--c-navy)' }};
                color: #fff; display: flex; align-items: center; justify-content: center;
                font-size: 1.1rem; font-weight: 600; flex-shrink: 0;">{{ $initial }}</div>
            <div>
                @if($hasTitle)
                    <div style="font-size: 1rem; font-weight: 600; color: var(--c-navy); line-height: 1.3;">{{ $row->customer_name }}</div>
                    <div style="font-size: 0.84rem; color: var(--c-text-muted);">
                        <i class="fa fa-user-check me-1"></i>المهمة لـ: <strong>{{ $assigneeName !== '' ? $assigneeName : 'غير محدد' }}</strong>
                    </div>
                @else
                    <div style="font-size: 1rem; font-weight: 700; color: var(--c-navy); line-height: 1.3;">
                        <i class="fa fa-user-check me-1" style="color: var(--c-accent);"></i>{{ $assigneeName !== '' ? $assigneeName : 'غير محدد' }}
                    </div>
                @endif
            </div>
        </div>
        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            <span class="pill-pro pill-neutral"><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($row->created_at)->format('Y/m/d - h:i A') }}</span>
            <span class="pill-pro pill-warning"><i class="fa fa-user-pen"></i> من: {{ $row->created_by_name ?? 'غير معروف' }}</span>
            @if($isDone && $row->contacted_by_name)
                <span class="pill-pro pill-success">
                    <i class="fa fa-circle-check"></i> أُنجزت بواسطة: {{ $row->contacted_by_name }}
                    @if($row->contacted_at)
                        · {{ \Carbon\Carbon::parse($row->contacted_at)->format('Y/m/d h:i A') }}
                    @endif
                </span>
            @endif
        </div>
    </div>

    <div style="background: var(--c-bg); border-radius: var(--r-md); padding: 12px 14px; margin: 10px 0; line-height: 1.7; border: 1px solid var(--c-border); white-space: pre-wrap;">{{ $row->inquiry }}</div>

    @if($isDone && $row->contact_notes)
        <div style="background: var(--c-success-bg); border-radius: var(--r-md); padding: 10px 14px; margin-top: 8px; color: var(--c-success); font-size: 0.9rem;">
            <strong>ملاحظات الإنجاز:</strong> {{ $row->contact_notes }}
        </div>
    @endif

    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px;">
        @if(!$isDone)
            <button class="btn-pro btn-pro-success btn-pro-sm" onclick="openConfirmContact({{ $row->id }})">
                <i class="fa fa-check"></i> تم إنجاز المهمة
            </button>
        @else
            <button class="btn-pro btn-pro-sm" style="background: var(--c-warning); color: #fff;" onclick="undoContact({{ $row->id }})">
                <i class="fa fa-rotate-left"></i> إرجاع لمهام اليوم
            </button>
        @endif

        <button class="btn-pro btn-pro-sm" style="background: var(--c-info); color: #fff;" onclick='downloadTaskImage({!! $jsData !!})'>
            <i class="fa fa-image"></i> تنزيل كصورة
        </button>

        <button class="btn-pro btn-pro-sm btn-wa-pro" onclick='shareTaskImageWhatsApp({!! $jsData !!})'>
            <i class="fa-brands fa-whatsapp"></i> إرسال واتساب
        </button>

        @if($canManageTasks ?? $isAdmin ?? false)
            <button class="btn-pro btn-pro-primary btn-pro-sm" onclick='editInquiry({!! $jsData !!})'>
                <i class="fa fa-pen"></i> تعديل
            </button>
            <button class="btn-pro btn-pro-danger btn-pro-sm" onclick="deleteInquiry({{ $row->id }})">
                <i class="fa fa-trash"></i> حذف
            </button>
        @endif
    </div>
</div>
