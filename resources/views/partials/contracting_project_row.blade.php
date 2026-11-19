@php
    $pm = $projectMetrics[$p->id] ?? [
        'ops_count' => 0, 'total_cost' => 0, 'net_selling' => 0,
        'client_paid' => 0, 'client_remaining' => 0, 'net_profit' => 0,
        'is_installment_plan' => false, 'installment_months' => 0,
    ];
    $isInstPlan = !empty($pm['is_installment_plan']);
    $isArchiveRow = ($context ?? 'master') === 'archive';
    $idPrefix = $isArchiveRow ? 'archive' : 'master';
    $rowClass = $isArchiveRow ? 'project-archive-row' : 'project-master-row';
    $openFnCall = $isArchiveRow ? "openProjectFromAnywhere({$p->id}, true)" : "select360Project({$p->id})";
    $isCompletedProj = ($p->status ?? 'active') === 'completed';
    $isDeleteLocked = $isInstPlan || $isCompletedProj;
@endphp
<tr id="{{ $idPrefix }}-project-row-{{ $p->id }}"
    class="{{ $rowClass }} {{ $isInstPlan ? 'project-row-installment' : '' }}"
    data-name="{{ mb_strtolower($p->name) }}"
    data-client="{{ $p->client_name }}"
    data-phone="{{ $p->client_phone ?? '' }}"
    data-location="{{ mb_strtolower($p->location ?? '') }}"
    data-status="{{ $p->status ?? 'active' }}"
    data-installment="{{ $isInstPlan ? '1' : '0' }}">
    <td><span class="badge bg-indigo-subtle text-primary border rounded-pill">#PRJ-{{ $p->id }}</span></td>
    <td class="text-start">
        <a href="javascript:void(0)" onclick="{{ $openFnCall }}" class="fw-black text-primary text-decoration-none d-block">
            <i class="fa fa-building text-warning me-1"></i>{{ $p->name }}
        </a>
        <small class="text-muted"><i class="fa fa-map-marker-alt text-danger me-1"></i>{{ $p->location ?: 'غير محدد' }}</small>
    </td>
    <td class="text-start">
        <div class="fw-bold text-dark">{{ $p->client_name }}</div>
        @if(!empty($p->client_phone))
            <small class="text-muted"><i class="fa fa-phone text-success me-1"></i>{{ $p->client_phone }}</small>
        @endif
    </td>
    <td>
        <div class="fw-bold text-success">{{ $p->area_sqm ? number_format($p->area_sqm, 0) . ' م²' : '-' }}</div>
        <small class="badge bg-light text-dark border">إشراف {{ $p->supervision_pct ?? 0 }}%</small>
    </td>
    <td class="project-status-cell">
        @if(($p->status ?? 'active') === 'active')
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">نشط</span>
        @elseif($p->status === 'completed')
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2">مكتمل</span>
        @else
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2">متوقف</span>
        @endif
        @if($isInstPlan)
            <div class="mt-1 inst-plan-badge-wrap">
                <span class="badge rounded-pill px-2 fw-bold" style="background: linear-gradient(135deg, #7c3aed, #6d28d9); color: #fff; font-size: 11px;">
                    <i class="fa fa-calendar-check me-1"></i>منظومة التقسيط@if(($pm['installment_months'] ?? 0) > 0) ({{ $pm['installment_months'] }} شهر)@endif
                </span>
            </div>
        @endif
    </td>
    <td><span class="badge bg-secondary-subtle text-dark rounded-pill">{{ $pm['ops_count'] }} حركة</span></td>
    <td class="fw-bold text-danger">{{ number_format($pm['total_cost'], 0) }} ج</td>
    <td class="fw-bold text-primary">{{ number_format($pm['net_selling'], 0) }} ج</td>
    <td class="fw-bold text-success" id="{{ $idPrefix }}-paid-{{ $p->id }}">{{ number_format($pm['client_paid'], 0) }} ج</td>
    <td class="fw-bold" id="{{ $idPrefix }}-rem-{{ $p->id }}" style="color: {{ ($pm['client_credit'] ?? 0) > 0 ? '#1d4ed8' : '#b45309' }};">
        @if(($pm['client_credit'] ?? 0) > 0)
            <span>0 ج</span>
            <div class="mt-1">
                <span class="badge bg-info-subtle text-primary border border-info-subtle rounded-pill fw-bold" style="font-size: 10.5px;">
                    رصيد مقدم: {{ number_format($pm['client_credit'], 0) }} ج
                </span>
            </div>
        @else
            {{ number_format($pm['client_remaining'], 0) }} ج
        @endif
    </td>
    <td class="fw-black {{ $pm['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($pm['net_profit'], 0) }} ج</td>
    <td>
        <div class="d-flex justify-content-center align-items-center gap-1 flex-nowrap">
            <button type="button" class="btn btn-sm btn-primary rounded-pill fw-bold px-2" onclick="{{ $openFnCall }}" title="فتح لوحة تفاصيل المشروع">
                <i class="fa fa-eye me-1"></i>عرض
            </button>
            @if($isArchiveRow)
                <a href="{{ route('contracting.statement', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-2" title="طباعة كشف حساب المشروع">
                    <i class="fa fa-print"></i>
                </a>
            @endif
            <button type="button" id="{{ $idPrefix }}-edit-btn-{{ $p->id }}"
                    class="btn btn-sm {{ $isInstPlan ? 'btn-secondary opacity-75' : 'btn-warning text-dark' }} rounded-pill fw-bold px-2"
                    onclick="openEditProjectModal({{ $p->id }}, {{ json_encode($p->name) }}, {{ json_encode($p->client_name) }}, {{ json_encode($p->client_phone ?? '') }}, {{ json_encode($p->location ?? '') }}, {{ json_encode($p->area_sqm ?? '') }}, {{ json_encode($p->supervision_pct ?? 15) }}, {{ json_encode($p->contract_type ?? 'cost_plus') }}, {{ json_encode($p->status ?? 'active') }}, {{ json_encode($p->notes ?? '') }}, {{ $isInstPlan ? 'true' : 'false' }})"
                    title="{{ $isInstPlan ? 'محول لعقد تقسيط — الحالة والتعديل والحذف مقفولان' : ($isCompletedProj ? 'تعديل الحالة / إعادة تنشيط المشروع' : 'تعديل بيانات المشروع') }}">
                <i class="fa {{ $isInstPlan ? 'fa-lock' : 'fa-pen' }}"></i>
            </button>
            <button type="button" id="{{ $idPrefix }}-delete-btn-{{ $p->id }}"
                    class="btn btn-sm {{ $isDeleteLocked ? 'btn-secondary opacity-50' : 'btn-danger' }} rounded-pill px-2"
                    onclick="deleteProject({{ $p->id }}, {{ json_encode($p->name) }}, {{ json_encode($p->status ?? 'active') }}, {{ $isInstPlan ? 'true' : 'false' }})"
                    title="{{ $isInstPlan ? 'لا يمكن حذف مشروع محوّل لعقد تقسيط' : ($isCompletedProj ? 'المشروع مكتمل — قم بتغيير حالته إلى نشط أولاً للحذف' : 'حذف المشروع') }}">
                <i class="fa {{ $isDeleteLocked ? 'fa-lock' : 'fa-trash' }}"></i>
            </button>
        </div>
    </td>
</tr>
