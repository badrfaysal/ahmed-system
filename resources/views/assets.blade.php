<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الأصول الثابتة - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --main-color: #0f766e; --light-bg: #f0fdf4; --border-color: #bbf7d0; }
        body { font-family: 'Cairo', sans-serif; background: #f8f9fa; overflow-x: hidden; }
        .main-content { margin-right: 260px; padding: 35px 30px; }

        .stat-card { border-radius: 16px; padding: 18px 22px; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .sc-main   { background: linear-gradient(135deg, #0f766e, #14b8a6); }
        .sc-orange { background: linear-gradient(135deg, #c2410c, #f97316); }
        .stat-card h6 { opacity: .85; font-size: .78rem; font-weight: 700; margin-bottom: 5px; }
        .stat-card h3 { font-weight: 900; margin: 0; font-size: 1.5rem; }

        .main-table-container { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; }
        .table { margin-bottom: 0; font-size: 0.9rem; }
        .table thead th { background: #f1f5f9; color: #475569; font-weight: 800; border-bottom: 2px solid #e2e8f0; padding: 15px; }
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-weight: 600; }
        
        .clickable-row { cursor: pointer; transition: background 0.2s; }
        .clickable-row:hover { background: #f0fdf4 !important; }

        .modal-content { border-radius: 20px; border: none; }

        /* تابات */
        .nav-tabs-custom { border-bottom: 2px solid #e2e8f0; margin-bottom: 0; }
        .nav-tabs-custom .nav-link { border: none; border-bottom: 3px solid transparent; color: #64748b; font-weight: 800; font-size: 0.9rem; padding: 12px 20px; margin-bottom: -2px; border-radius: 0; }
        .nav-tabs-custom .nav-link:hover { color: var(--main-color); }
        .nav-tabs-custom .nav-link.active { color: var(--main-color); border-bottom-color: var(--main-color); background: transparent; }
        .nav-tabs-custom .nav-link .badge { font-size: 0.65rem; }

        /* تنسيقات الخط الزمني */
        .custom-timeline { position: relative; padding-right: 30px; margin-top: 20px; }
        .custom-timeline::before { content: ''; position: absolute; top: 0; right: 9px; height: 100%; width: 2px; background: #e2e8f0; }
        .timeline-item { position: relative; margin-bottom: 25px; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-icon { position: absolute; right: -30px; top: 0; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: white; border: 3px solid; z-index: 1; }
        .timeline-content { background: white; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .timeline-content h6 { margin: 0 0 5px; font-weight: 800; font-size: 1rem; }
        .timeline-content p { margin: 0; color: #64748b; font-size: 0.85rem; font-weight: 600; }
        .timeline-date { font-size: 0.75rem; color: #94a3b8; font-weight: 700; margin-top: 5px; display: block; }

        /* تنسيق صف الأصل المهلك */
        .row-scrapped td { color: #94a3b8 !important; }
        .row-scrapped .fw-bold { color: #94a3b8 !important; }
    @media(max-width:991px){.main-content{margin-right:0!important;width:100%!important;padding:70px 16px 30px!important;}}</style>
</head>
<body>
@include('sidebar')

<div class="main-content">
    
    @if(session('success')) <div class="alert alert-success fw-bold rounded-4 animate__animated animate__fadeInDown"><i class="fa fa-check-circle me-2"></i>{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger  fw-bold rounded-4 animate__animated animate__fadeInDown"><i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}</div> @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color:var(--main-color);"><i class="fa fa-car-side me-2"></i>الأصول الثابتة</h2>
            <p class="text-muted small mb-0">إدارة ممتلكات الشركة، سجل العمليات، تحديث القيمة وتسييلها</p>
        </div>
        <button class="btn btn-lg rounded-pill fw-bold shadow-sm px-4" style="background:var(--main-color);color:white;" data-bs-toggle="modal" data-bs-target="#addAssetModal">
            <i class="fa fa-plus-circle me-2"></i>تسجيل أصل جديد
        </button>
    </div>

    <div class="row g-3 mb-4 animate__animated animate__fadeInUp">
        <div class="col-md-6">
            <div class="stat-card sc-main">
                <h6><i class="fa fa-wallet me-1"></i> إجمالي القيمة الشرائية (للأصول النشطة)</h6>
                <h3>{{ number_format($total_purchase_value, 2) }} ج</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card sc-orange">
                <h6><i class="fa fa-chart-line me-1"></i> إجمالي القيمة الحالية (للأصول النشطة)</h6>
                <h3>{{ number_format($total_current_value, 2) }} ج</h3>
            </div>
        </div>
    </div>

    <div class="main-table-container animate__animated animate__fadeInUp">

        <ul class="nav nav-tabs-custom px-3 pt-3" id="assetTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-active">
                    <i class="fa fa-check-circle text-success me-1"></i> الأصول النشطة
                    <span class="badge bg-success ms-1">{{ $assets->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sold">
                    <i class="fa fa-hand-holding-dollar text-warning me-1"></i> أصول مباعة
                    <span class="badge bg-warning text-dark ms-1">{{ $sold_assets->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-scrapped">
                    <i class="fa fa-circle-xmark text-secondary me-1"></i> مهلكة بالكامل
                    <span class="badge bg-secondary ms-1">{{ $scrapped_assets->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ═══ تابة 1: الأصول النشطة ═══ --}}
            <div class="tab-pane fade show active" id="tab-active">
                <div class="alert alert-info fw-bold bg-white border-0 border-bottom border-info mb-0 rounded-0 py-2 px-3">
                    <i class="fa fa-hand-pointer me-2 text-info"></i> اضغط على أي صف لعرض السجل الزمني الكامل للأصل.
                </div>
                <div class="table-responsive">
                    <table class="table text-center table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="text-start">اسم الأصل</th>
                                <th>التكلفة الشرائية</th>
                                <th>القيمة الحالية</th>
                                <th>مقدار الإهلاك</th>
                                <th>ملاحظات</th>
                                <th>إجراءات سريعة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                                @php $depreciationAmount = $asset->purchase_price - $asset->current_value; @endphp
                                <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#historyAssetModal_{{ $asset->id }}">
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td class="text-start fw-bold text-dark">
                                        <i class="fa fa-cube me-2 text-muted"></i>{{ $asset->name }}
                                        @if(isset($asset->auto_depreciate_percent) && $asset->auto_depreciate_percent > 0)
                                            <div class="mt-1">
                                                <span class="badge bg-info bg-opacity-10 text-info fw-bold border border-info" style="font-size: 0.7rem; padding: 4px 8px;">
                                                    <i class="fa fa-robot me-1"></i> إهلاك آلي: {{ $asset->auto_depreciate_percent }}% / {{ ($asset->auto_depreciate_interval ?? 'month') == 'year' ? 'سنوياً' : 'شهرياً' }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="mt-1">
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold border" style="font-size: 0.7rem; padding: 4px 8px;">
                                                    <i class="fa fa-hand-pointer me-1"></i> إهلاك يدوي فقط
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-primary fw-bold">{{ number_format($asset->purchase_price, 0) }} ج</td>
                                    <td class="text-success fw-bold fs-6">{{ number_format($asset->current_value, 0) }} ج</td>
                                    <td class="text-danger fw-bold">{{ $depreciationAmount > 0 ? number_format($depreciationAmount, 0) . ' ج' : '—' }}</td>
                                    <td><span class="text-muted" style="font-size:0.85rem;white-space:pre-wrap;word-break:break-word;display:block;">{{ $asset->notes ?: '—' }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary fw-bold rounded-3 px-2" data-bs-toggle="modal" data-bs-target="#editAssetModal_{{ $asset->id }}" title="تعديل" onclick="event.stopPropagation();"><i class="fa fa-pen"></i></button>
                                        <button class="btn btn-sm btn-warning fw-bold rounded-3 px-2 text-dark" data-bs-toggle="modal" data-bs-target="#sellAssetModal_{{ $asset->id }}" title="بيع وتسييل" onclick="event.stopPropagation();"><i class="fa fa-hand-holding-dollar"></i></button>
                                        <button class="btn btn-sm btn-danger fw-bold rounded-3 px-2" data-bs-toggle="modal" data-bs-target="#addDepreciationModal" data-id="{{ $asset->id }}" data-val="{{ $asset->current_value }}" title="خصم إهلاك" onclick="event.stopPropagation();"><i class="fa fa-arrow-trend-down"></i></button>
                                        <form action="{{ route('assets.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الأصل نهائياً؟');">
                                            @csrf
                                            <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-3 px-2" title="حذف نهائي" onclick="event.stopPropagation();"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-5"><i class="fa fa-car-side fa-3x text-muted opacity-25 mb-3 d-block"></i><span class="text-muted fw-bold">لا توجد أصول نشطة حالياً!</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ═══ تابة 2: الأصول المباعة ═══ --}}
            <div class="tab-pane fade" id="tab-sold">
                <div class="alert alert-warning fw-bold bg-white border-0 border-bottom border-warning mb-0 rounded-0 py-2 px-3">
                    <i class="fa fa-hand-holding-dollar me-2 text-warning"></i> هذه الأصول تم بيعها وتسييلها — لعرض سجل حركاتها اضغط على الصف.
                </div>
                <div class="table-responsive">
                    <table class="table text-center table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="text-start">اسم الأصل</th>
                                <th>التكلفة الشرائية</th>
                                <th>إجمالي الإهلاك</th>
                                <th>ملاحظات</th>
                                <th>حذف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sold_assets as $asset)
                                @php $depreciationAmount = $asset->purchase_price - $asset->current_value; @endphp
                                <tr class="clickable-row opacity-75 bg-light" data-bs-toggle="modal" data-bs-target="#historyAssetModal_{{ $asset->id }}">
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td class="text-start fw-bold text-dark">
                                        <i class="fa fa-cube me-2 text-muted"></i>{{ $asset->name }}
                                        <span class="badge bg-warning text-dark ms-2" style="font-size:0.6rem;">مباع</span>
                                    </td>
                                    <td class="text-primary fw-bold">{{ number_format($asset->purchase_price, 0) }} ج</td>
                                    <td class="text-danger fw-bold">{{ $depreciationAmount > 0 ? number_format($depreciationAmount, 0) . ' ج' : '—' }}</td>
                                    <td><span class="text-muted" style="font-size:0.85rem;white-space:pre-wrap;word-break:break-word;display:block;">{{ $asset->notes ?: '—' }}</span></td>
                                    <td>
                                        <form action="{{ route('assets.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذا الأصل نهائياً من السجلات؟');">
                                            @csrf
                                            <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-3 px-2" onclick="event.stopPropagation();"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5"><i class="fa fa-hand-holding-dollar fa-3x text-muted opacity-25 mb-3 d-block"></i><span class="text-muted fw-bold">لا توجد أصول مباعة حتى الآن!</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ═══ تابة 3: مهلكة بالكامل ═══ --}}
            <div class="tab-pane fade" id="tab-scrapped">
                <div class="alert alert-secondary fw-bold bg-white border-0 border-bottom border-secondary mb-0 rounded-0 py-2 px-3">
                    <i class="fa fa-circle-xmark me-2 text-secondary"></i> هذه الأصول وصلت لصفر — مهلكة بالكامل دفترياً. اضغط على الصف لعرض سجل الإهلاكات.
                </div>
                <div class="table-responsive">
                    <table class="table text-center table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="text-start">اسم الأصل</th>
                                <th>التكلفة الشرائية</th>
                                <th>إجمالي الإهلاك</th>
                                <th>القيمة الحالية</th>
                                <th>ملاحظات</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($scrapped_assets as $asset)
                                <tr class="clickable-row row-scrapped" data-bs-toggle="modal" data-bs-target="#historyAssetModal_{{ $asset->id }}">
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td class="text-start fw-bold">
                                        <i class="fa fa-cube me-2 text-muted"></i>{{ $asset->name }}
                                        <span class="badge bg-secondary ms-2" style="font-size:0.6rem;">مهلك</span>
                                    </td>
                                    <td class="fw-bold">{{ number_format($asset->purchase_price, 0) }} ج</td>
                                    <td class="text-danger fw-bold">{{ number_format($asset->purchase_price, 0) }} ج</td>
                                    <td><span class="badge bg-secondary">0 ج</span></td>
                                    <td><span class="text-muted" style="font-size:0.85rem;white-space:pre-wrap;word-break:break-word;display:block;">{{ $asset->notes ?: '—' }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning fw-bold rounded-3 px-2 text-dark" data-bs-toggle="modal" data-bs-target="#sellAssetModal_{{ $asset->id }}" title="بيع كخردة" onclick="event.stopPropagation();" title="بيع خردة"><i class="fa fa-hand-holding-dollar"></i></button>
                                        <form action="{{ route('assets.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذا الأصل نهائياً من السجلات؟');">
                                            @csrf
                                            <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-3 px-2" onclick="event.stopPropagation();"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-5"><i class="fa fa-circle-xmark fa-3x text-muted opacity-25 mb-3 d-block"></i><span class="text-muted fw-bold">لا توجد أصول مهلكة بالكامل حتى الآن!</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- مودال إضافة أصل جديد --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addAssetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header text-white border-0" style="background:var(--main-color); border-radius:20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i>تسجيل أصل جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('assets.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <label class="fw-bold text-muted small mb-1">اسم/وصف الأصل</label>
                    <input type="text" name="name" class="form-control fw-bold mb-3" required>
                    <div class="row">
                        <div class="col-6">
                            <label class="fw-bold text-muted small mb-1">التكلفة (سعر الشراء)</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control border-primary fw-bold text-center mb-3" required>
                        </div>
                        <div class="col-6">
                            <label class="fw-bold text-muted small mb-1">تاريخ الشراء</label>
                            <input type="date" name="purchase_date" class="form-control text-center fw-bold mb-3" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- 💡 حقل الإعدادات الآلية للإهلاك 💡 --}}
                    <div class="row bg-info bg-opacity-10 p-3 rounded-3 mb-3 border border-info mx-0">
                        <h6 class="fw-bold text-info mb-3"><i class="fa fa-robot me-1"></i> إعدادات الإهلاك الآلي (اختياري)</h6>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">النسبة المئوية للإهلاك (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="auto_depreciate_percent" class="form-control fw-bold text-center border-info" placeholder="مثال: 5" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">تُخصم كل (المدة)</label>
                            <select name="auto_depreciate_interval" class="form-select fw-bold border-info text-center">
                                <option value="month">كل شهر</option>
                                <option value="year">كل سنة</option>
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <small class="text-muted fw-bold"><i class="fa fa-info-circle me-1"></i> اترك النسبة 0 إذا كنت ترغب في استخدام زر (الإهلاك اليدوي) فقط.</small>
                        </div>
                    </div>

                    <label class="fw-bold text-muted small mb-1">صرف المبلغ من خزنة (اختياري)</label>
                    <select name="account_id" class="form-select border-dark mb-3">
                        <option value="">بدون سحب من الخزنة (تسجيل دفتري فقط)</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance, 0) }} ج)</option>
                        @endforeach
                    </select>
                    <label class="fw-bold text-muted small mb-1">ملاحظات إضافية</label>
                    <input type="text" name="notes" class="form-control bg-light border-0">
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn w-100 rounded-pill fw-bold py-2 shadow-sm" style="background:var(--main-color); color:white;">اعتماد وحفظ الأصل</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- مودال الإهلاك اليدوي (مشترك لكل الأصول) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addDepreciationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-danger text-white border-0" style="border-radius:20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa fa-arrow-trend-down me-2"></i>تسجيل إهلاك يدوي إضافي</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('assets.depreciate') }}" method="POST">
                @csrf
                <input type="hidden" name="asset_id" id="modal_dep_asset_id">
                <div class="modal-body p-4">
                    <div class="alert alert-danger text-center mb-4">
                        <small class="fw-bold d-block mb-1">القيمة الحالية للأصل</small>
                        <h3 class="fw-bold mb-0"><span id="modal_dep_current_val">0</span> ج</h3>
                    </div>
                    <label class="fw-bold text-muted small mb-1">تاريخ الإهلاك <span class="text-danger">*</span></label>
                    <input type="date" name="depreciation_date" class="form-control border-danger fw-bold text-center mb-3" value="{{ date('Y-m-d') }}" required>

                    <label class="fw-bold text-muted small mb-1">طريقة الإهلاك</label>
                    <select name="depreciation_type" id="modal_dep_type" class="form-select border-danger fw-bold mb-3" onchange="updateDepreciationUI()">
                        <option value="fixed">مبلغ ثابت (جنية)</option>
                        <option value="percent">نسبة مئوية (%)</option>
                    </select>
                    <label class="fw-bold text-muted small mb-1" id="modal_dep_label">قيمة الإهلاك</label>
                    <input type="number" name="amount" id="modal_dep_amount" class="form-control border-danger fw-bold fs-4 text-center mb-1" step="0.01" required oninput="calculatePreview()">
                    <div id="preview_box" class="text-center mb-3" style="display: none;">
                        <small class="text-muted fw-bold">المبلغ المخصوم: <span id="preview_amount" class="text-danger fs-6">0</span> ج</small>
                    </div>
                    <label class="fw-bold text-muted small mb-1">ملاحظات</label>
                    <input type="text" name="notes" class="form-control bg-light border-0">
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold py-2 shadow-sm">تأكيد خصم الإهلاك</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- مودالات السجل الزمني + التعديل + البيع لكل أصل --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@php
    $all_assets_for_modals = $assets->merge($sold_assets)->merge($scrapped_assets);
@endphp

@foreach($all_assets_for_modals as $asset)
    @php $depreciationAmount = $asset->purchase_price - $asset->current_value; @endphp

    {{-- مودال السجل الزمني --}}
    <div class="modal fade" id="historyAssetModal_{{ $asset->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white border-0" style="border-radius:20px 20px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fa fa-history me-2 text-warning"></i>سجل حركات الأصل: {{ $asset->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row mb-4 text-center g-2">
                        <div class="col-4">
                            <div class="bg-white p-2 border rounded-3 shadow-sm">
                                <span class="d-block small text-muted fw-bold">تكلفة الشراء</span>
                                <span class="fw-black text-primary">{{ number_format($asset->purchase_price, 0) }} ج</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white p-2 border rounded-3 shadow-sm">
                                <span class="d-block small text-muted fw-bold">إجمالي الإهلاكات</span>
                                <span class="fw-black text-danger">{{ number_format($depreciationAmount, 0) }} ج</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white p-2 border rounded-3 shadow-sm">
                                <span class="d-block small text-muted fw-bold">القيمة الحالية</span>
                                <span class="fw-black {{ $asset->current_value > 0 ? 'text-success' : 'text-secondary' }}">{{ number_format($asset->current_value, 0) }} ج</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-black text-dark mb-3 border-bottom pb-2"><i class="fa fa-list-ul me-2 text-primary"></i>التسلسل الزمني:</h6>
                    <div class="custom-timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon border-primary text-primary"><i class="fa fa-plus fa-xs"></i></div>
                            <div class="timeline-content border-primary" style="border-right-width: 4px;">
                                <h6 class="text-primary">شراء وإضافة الأصل</h6>
                                <p>تم تسجيل الأصل بتكلفة مبدئية قدرها {{ number_format($asset->purchase_price, 0) }} ج.</p>
                                <span class="timeline-date" dir="ltr"><i class="fa fa-clock me-1"></i>{{ \Carbon\Carbon::parse($asset->created_at ?? now())->format('Y-m-d') }}</span>
                            </div>
                        </div>

                        @if($asset->depreciation_history->count() > 0)
                            @foreach($asset->depreciation_history as $dep)
                            <div class="timeline-item">
                                <div class="timeline-icon border-danger text-danger"><i class="fa fa-arrow-down fa-xs"></i></div>
                                <div class="timeline-content border-danger" style="border-right-width: 4px;">
                                    <h6 class="text-danger">إهلاك — خصم {{ number_format($dep->amount, 0) }} ج</h6>
                                    <p>{{ $dep->notes }}</p>
                                    <span class="timeline-date" dir="ltr"><i class="fa fa-clock me-1"></i>{{ \Carbon\Carbon::parse($dep->created_at)->format('Y-m-d H:i') }}</span>
                                </div>
                            </div>
                            @endforeach
                        @endif

                        @if($asset->status == 'sold')
                        <div class="timeline-item">
                            <div class="timeline-icon border-warning text-warning"><i class="fa fa-hand-holding-dollar fa-xs"></i></div>
                            <div class="timeline-content border-warning" style="border-right-width: 4px;">
                                <h6 class="text-dark">بيع وتسييل الأصل</h6>
                                <p>تم بيع هذا الأصل وإخراجه من الخدمة وتسجيل قيمته بالخزنة.</p>
                                <span class="timeline-date" dir="ltr"><i class="fa fa-clock me-1"></i>{{ \Carbon\Carbon::parse($asset->updated_at ?? now())->format('Y-m-d') }}</span>
                            </div>
                        </div>
                        @elseif($asset->current_value <= 0)
                        <div class="timeline-item">
                            <div class="timeline-icon border-secondary text-secondary"><i class="fa fa-circle-xmark fa-xs"></i></div>
                            <div class="timeline-content border-secondary" style="border-right-width: 4px;">
                                <h6 class="text-secondary">مهلك بالكامل</h6>
                                <p>وصلت قيمة الأصل إلى صفر — مهلك دفترياً بالكامل.</p>
                                <span class="timeline-date" dir="ltr"><i class="fa fa-clock me-1"></i>{{ \Carbon\Carbon::parse($asset->updated_at ?? now())->format('Y-m-d') }}</span>
                            </div>
                        </div>
                        @else
                        <div class="timeline-item">
                            <div class="timeline-icon border-success text-success"><i class="fa fa-check fa-xs"></i></div>
                            <div class="timeline-content border-success" style="border-right-width: 4px;">
                                <h6 class="text-success">في الخدمة (نشط)</h6>
                                <p>الأصل ما زال يعمل وقيمته الدفترية الحالية {{ number_format($asset->current_value, 0) }} ج.</p>
                                <span class="timeline-date" dir="ltr"><i class="fa fa-clock me-1"></i>حتى تاريخ اليوم</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer p-2 border-0 bg-white">
                    <button type="button" class="btn btn-dark fw-bold px-5 rounded-pill w-100" data-bs-dismiss="modal">إغلاق السجل</button>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال التعديل (للأصول النشطة فقط) --}}
    @if($asset->status !== 'sold')
    <div class="modal fade" id="editAssetModal_{{ $asset->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white border-0" style="border-radius:20px 20px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fa fa-pen me-2"></i>تعديل بيانات الأصل</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('assets.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    <div class="modal-body p-4">
                        <label class="fw-bold text-muted small mb-1">اسم/وصف الأصل</label>
                        <input type="text" name="name" class="form-control fw-bold mb-3" value="{{ $asset->name }}" required>
                        <label class="fw-bold text-muted small mb-1">القيمة الحالية الدفترية</label>
                        <input type="number" step="0.01" name="current_value" class="form-control border-primary fw-bold text-center mb-3" value="{{ $asset->current_value }}" required>
                        
                        {{-- 💡 تعديل إعدادات الإهلاك الآلي 💡 --}}
                        <div class="row bg-info bg-opacity-10 p-3 rounded-3 mb-3 border border-info mx-0">
                            <h6 class="fw-bold text-info mb-3"><i class="fa fa-robot me-1"></i> تعديل الإهلاك الآلي</h6>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-dark">النسبة المئوية (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="auto_depreciate_percent" class="form-control fw-bold text-center border-info" value="{{ $asset->auto_depreciate_percent ?? 0 }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-dark">تُخصم كل (المدة)</label>
                                <select name="auto_depreciate_interval" class="form-select fw-bold border-info text-center">
                                    <option value="month" {{ ($asset->auto_depreciate_interval ?? 'month') == 'month' ? 'selected' : '' }}>شهر</option>
                                    <option value="year"  {{ ($asset->auto_depreciate_interval ?? 'month') == 'year' ? 'selected' : '' }}>سنة</option>
                                </select>
                            </div>
                        </div>

                        <label class="fw-bold text-muted small mb-1">ملاحظات إضافية</label>
                        <input type="text" name="notes" class="form-control bg-light border-0" value="{{ $asset->notes }}">
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">حفظ التعديلات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- مودال البيع --}}
    @if($asset->status !== 'sold')
    <div class="modal fade" id="sellAssetModal_{{ $asset->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark border-0" style="border-radius:20px 20px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fa fa-hand-holding-dollar me-2"></i>بيع وتسييل الأصل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('assets.sell') }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    <div class="modal-body p-4">
                        <div class="alert alert-warning text-center mb-4">
                            <small class="fw-bold d-block mb-1">القيمة الدفترية المقدرة</small>
                            <h3 class="fw-bold mb-0">{{ number_format($asset->current_value, 2) }} ج</h3>
                        </div>
                        <label class="fw-bold text-muted small mb-1">سعر البيع الفعلي (جنية)</label>
                        <input type="number" step="0.01" name="sell_price" class="form-control border-warning fw-bold text-center fs-4 mb-3" placeholder="اكتب المبلغ اللي قبضته فعلاً..." required>
                        <label class="fw-bold text-muted small mb-1">إيداع مبلغ البيع في خزنة:</label>
                        <select name="account_id" class="form-select border-dark fw-bold" required>
                            <option value="" disabled selected>اختر الخزنة...</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ number_format($acc->balance, 0) }} ج)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold py-2 shadow-sm text-dark">اعتماد البيع والإيداع</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentAssetValueForDep = 0;
    document.getElementById('addDepreciationModal').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        if (!btn) return;
        currentAssetValueForDep = parseFloat(btn.getAttribute('data-val')) || 0;
        document.getElementById('modal_dep_asset_id').value = btn.getAttribute('data-id');
        document.getElementById('modal_dep_current_val').innerText = currentAssetValueForDep.toLocaleString('en-US');
        document.getElementById('modal_dep_type').value = 'fixed';
        document.getElementById('modal_dep_amount').value = '';
        updateDepreciationUI();
    });

    function updateDepreciationUI() {
        const type = document.getElementById('modal_dep_type').value;
        const input = document.getElementById('modal_dep_amount');
        const label = document.getElementById('modal_dep_label');
        const previewBox = document.getElementById('preview_box');
        if (type === 'percent') {
            label.innerText = 'نسبة الإهلاك (%)';
            input.max = 100;
            if(previewBox) previewBox.style.display = 'block';
        } else {
            label.innerText = 'قيمة الإهلاك (جنية)';
            input.max = currentAssetValueForDep;
            if(previewBox) previewBox.style.display = 'none';
        }
        calculatePreview();
    }

    function calculatePreview() {
        const type = document.getElementById('modal_dep_type').value;
        const val = parseFloat(document.getElementById('modal_dep_amount').value) || 0;
        const previewAmount = document.getElementById('preview_amount');
        if (type === 'percent' && previewAmount) {
            const deduction = (currentAssetValueForDep * (val / 100));
            previewAmount.innerText = deduction.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }
</script>
</body>
</html>