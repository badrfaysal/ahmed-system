<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الشركاء وحساب الإدارة - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;
            --brand-primary: #1e40af;
            --brand-success: #047857;
            --brand-danger:  #b91c1c;
            --brand-amber:   #b45309;
        }
        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--slate-50);
            color: var(--slate-900);
            font-size: 0.92rem;
            overflow-x: hidden;
        }
        .main-content {
            padding: 24px 20px;
        }
        .card-formal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .card-header-formal {
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .card-header-formal h5 {
            margin: 0;
            font-weight: 800;
            font-size: 1.02rem;
            color: var(--slate-900);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .kpi-formal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            padding: 16px 18px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            height: 100%;
            position: relative;
            border-top: 3px solid var(--slate-700);
        }
        .kpi-formal.accent-primary { border-top-color: var(--brand-primary); }
        .kpi-formal.accent-success { border-top-color: var(--brand-success); }
        .kpi-formal.accent-amber   { border-top-color: var(--brand-amber); }
        .kpi-formal.accent-slate   { border-top-color: var(--slate-700); }

        .kpi-formal .title {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--slate-600);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .kpi-formal .value {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--slate-900);
            font-variant-numeric: tabular-nums;
        }
        .kpi-formal .sub {
            font-size: 0.76rem;
            color: var(--slate-600);
            margin-top: 6px;
        }

        /* ── جداول رسمية متناسقة ── */
        .table-responsive {
            overflow-x: auto;
            margin: 0;
        }
        .table-formal {
            margin: 0;
            width: 100%;
            vertical-align: middle;
            font-size: 0.88rem;
        }
        .table-responsive {
            overflow-x: visible !important;
        }
        .table-formal thead th {
            background: var(--slate-800);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.78rem;
            padding: 10px 8px;
            border: none;
            white-space: normal;
            vertical-align: middle;
        }
        .table-formal tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--slate-200);
            font-variant-numeric: tabular-nums;
            vertical-align: middle;
        }
        .table-formal tbody tr.row-exited td {
            background-color: #fef2f2 !important;
            border-bottom-color: #fee2e2 !important;
        }
        .table-formal tbody tr.row-exited:hover td {
            background-color: #fee2e2 !important;
        }
        .table-formal tbody tr.row-exited td:first-child {
            border-right: 4px solid #ef4444 !important;
        }

        .btn-action-icon {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 0.78rem;
            transition: 0.15s ease;
        }

        .pagination {
            margin: 0;
            font-size: 0.84rem;
        }
        .pagination .page-link {
            color: var(--slate-700);
            border-color: var(--slate-200);
            padding: 5px 11px;
            font-weight: 600;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: #ffffff;
            font-weight: 700;
        }
        .pagination .page-link:hover {
            background-color: var(--slate-100);
            color: var(--brand-primary);
        }

        .print-only { display: none; }
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm 6mm 10mm 6mm;
            }
            *, *::before, *::after {
                overflow: visible !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
                box-shadow: none !important;
            }
            ::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'Cairo', sans-serif !important;
                font-size: 8pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
                width: 100% !important;
            }
            .app-sidebar, .sb-mobile-launcher, .sb-mobile-backdrop, .no-print, button, .btn, .card-footer, .modal {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                right: 0 !important;
            }
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .print-only {
                display: block !important;
            }
            .card-formal {
                border: 1px solid #94a3b8 !important;
                box-shadow: none !important;
                margin-bottom: 12px !important;
                border-radius: 4px !important;
                page-break-inside: avoid !important;
            }
            .table-responsive {
                overflow: visible !important;
                display: block !important;
                width: 100% !important;
            }
            .table-formal {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 7.8pt !important;
                table-layout: auto !important;
            }
            .table-formal thead th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
                padding: 5px 4px !important;
                font-size: 7.2pt !important;
                font-weight: 700 !important;
                text-align: center !important;
                white-space: normal !important;
            }
            .table-formal tbody td {
                border: 1px solid #cbd5e1 !important;
                padding: 5px 4px !important;
                font-size: 7.8pt !important;
                color: #000000 !important;
            }
            .table-formal tr {
                page-break-inside: avoid !important;
            }
            .badge {
                border: 1px solid #475569 !important;
                color: #000000 !important;
                background: #f1f5f9 !important;
                font-weight: 700 !important;
                padding: 2px 4px !important;
                font-size: 6.8pt !important;
            }
            .print-sig-col {
                border: 1px dashed #94a3b8 !important;
                padding: 8px !important;
                text-align: center !important;
                background: #ffffff !important;
                border-radius: 4px !important;
            }
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="container-fluid">

        {{-- الترويسة المطبوعة الرسمية (تظهر فقط عند الطباعة) --}}
        <div class="print-only mb-3 pb-2 border-bottom border-dark border-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-end">
                    <h5 class="fw-bold m-0 text-dark">شركة الضبع للتجارة والتوزيع</h5>
                    <small class="text-muted d-block">سجل الشركاء وحساب الإدارة والدورات الشهرية</small>
                    <span class="badge bg-dark text-white mt-1">كود الوثيقة: PRT-REG-{{ date('Ymd') }}</span>
                </div>
                <div class="text-center">
                    <h4 class="fw-bold m-0 text-dark">سجل الشركاء والحصص المعتمدة</h4>
                    <div class="fw-bold fs-6 text-primary mt-1">حساب الإدارة: {{ $headName }}</div>
                    <small class="text-muted">إجمالي الحصص المعتمدة: <strong>{{ number_format($totalApprovedCapital, 2) }} ج</strong> · رأس مال الشركة الحالي: <strong>{{ number_format($totalCompanyCapital, 2) }} ج</strong></small>
                </div>
                <div class="text-start">
                    <div class="badge bg-dark text-white px-2 py-1 mb-1">وثيقة مالية رسمية</div>
                    <div class="small text-muted" style="font-size:0.75rem;">تاريخ الطباعة: {{ date('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- ترويسة الصفحة والتحكم للشاشة --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 no-print">
            <div>
                <h4 class="fw-bold mb-1 d-flex align-items-center gap-2 text-dark">
                    <i class="fa fa-briefcase text-primary"></i> إدارة الشركاء وحساب الإدارة
                </h4>
                <p class="text-muted small m-0">نسب الشركاء وحساب الإدارة ثابتة ومعتمدة من إجمالي الحصص، ولا تتغير إلا عند إقفال الدورة الشهرية أو الإيداع/السحب المباشر.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-dark fw-bold shadow-sm px-3 py-2" onclick="window.print()">
                    <i class="fa fa-print me-1"></i> طباعة السجل الرسمي
                </button>
                <a href="{{ route('partners.closing') }}" class="btn btn-primary fw-bold shadow-sm px-3 py-2">
                    <i class="fa fa-calendar-check me-1"></i> إقفال الدورة الشهرية
                </a>
                <button type="button" class="btn btn-outline-dark fw-bold shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalAddPartner">
                    <i class="fa fa-user-plus me-1"></i> إضافة شريك
                </button>
                <a href="{{ route('partners.ahmed') }}" class="btn btn-outline-secondary fw-bold shadow-sm px-3 py-2">
                    <i class="fa fa-chart-pie me-1"></i> حساب الإدارة ({{ $headName }})
                </a>
            </div>
        </div>

        {{-- كروت المؤشرات المالية الكلية --}}
        <div class="row g-3 mb-4 no-print">
            <div class="col-md-3">
                <div class="kpi-formal accent-primary">
                    <div class="title"><i class="fa fa-building text-primary"></i> إجمالي الحصص المعتمدة بالدورة</div>
                    <div class="value">{{ number_format($totalApprovedCapital, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                    <div class="sub d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <span>رأس مال الشركة اللحظي: <strong>{{ number_format($totalCompanyCapital, 2) }} ج</strong></span>
                        @if(abs($unclosedPeriodProfit) >= 0.01)
                            <span class="badge {{ $unclosedPeriodProfit >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                                {{ $unclosedPeriodProfit >= 0 ? 'ربح تحت التوزيع: +' : 'عجز حالي: ' }}{{ number_format($unclosedPeriodProfit, 2) }} ج
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-formal accent-amber">
                    <div class="title"><i class="fa fa-user-tie text-warning"></i> حساب الإدارة ({{ $headName }})</div>
                    <div class="value">{{ number_format($ahmedCapital, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                    <div class="sub d-flex justify-content-between">
                        <span>نسبته في الدورة الحالية: <strong>{{ number_format($ahmedSharePct, 2) }}%</strong></span>
                        <span class="text-muted">(أرباح نسب الشغل المدورة)</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-formal accent-slate">
                    <div class="title"><i class="fa fa-users text-secondary"></i> إجمالي رؤوس أموال الشركاء</div>
                    <div class="value">{{ number_format($totalPartnersCapital, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                    <div class="sub">
                        <span>نسبة الشركاء مجتمعين: <strong>{{ number_format($partnersSharePct, 2) }}%</strong> ({{ $activePartners->count() }} شركاء نشطين)</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-formal accent-success">
                    <div class="title"><i class="fa fa-hand-holding-dollar text-success"></i> إجمالي أرباح حساب الإدارة</div>
                    <div class="value">{{ number_format($totalAhmedTotalEarned, 2) }} <span class="fs-6 fw-normal text-muted">ج</span></div>
                    <div class="sub">
                        <span>نسب الشغل: <strong>{{ number_format($totalAhmedCommissionsEarned, 2) }} ج</strong> · حصة الرصيد: <strong>{{ number_format($totalAhmedOwnProfitEarned, 2) }} ج</strong></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- جدول الشركاء النشطين والنسب المعتمدة --}}
        <div class="card-formal">
            <div class="card-header-formal">
                <h5>
                    <i class="fa fa-users text-primary"></i> سجل الشركاء وحساب الإدارة (النسب المعتمدة للدورة الحالية)
                    <span class="badge bg-light text-dark border ms-2">{{ $partners->count() }} شريك + حساب الإدارة</span>
                </h5>
                <div class="text-muted small no-print">
                    <i class="fa fa-shield-alt text-success me-1"></i> النسب ثابتة طوال الشهر ولا تتأثر بأي ربح جديد إلا عند اعتماد الإقفال.
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-formal table-hover">
                    <thead>
                        <tr>
                            <th style="width: 38px;" class="text-center">#</th>
                            <th style="width: 22%;">الشريك / الحساب</th>
                            <th style="width: 14%;">الرصيد المعتمد بالدورة</th>
                            <th style="width: 10%;" class="text-center">نسبة المساهمة</th>
                            <th style="width: 10%;" class="text-center">نسبة الإدارة</th>
                            <th style="width: 10%;" class="text-center">صافي الحصة</th>
                            <th style="width: 12%;">إجمالي الأرباح</th>
                            <th style="width: 10%;">المسحوبات</th>
                            <th style="width: 12%;" class="text-center no-print">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- صف حساب الإدارة --}}
                        <tr style="background-color:#f8fafc; border-bottom:2px solid #cbd5e1;">
                            <td class="text-center fw-bold text-muted">—</td>
                            <td>
                                <a href="{{ route('partners.ahmed') }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $headName }}
                                </a>
                                <small class="text-muted d-block">
                                    <i class="fa fa-briefcase fa-xs me-1"></i>حساب أرباح الإدارة من نسب الشغل (يُدوّر ما لا يُسحب ليشارك في الدورة القادمة)
                                </small>
                                <span class="badge bg-dark mt-1" style="font-size:0.65rem;">حساب الإدارة</span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark text-nowrap fs-6">{{ number_format($ahmedCapital, 2) }} <small class="text-muted">ج</small></span>
                                <div style="font-size:0.7rem;" class="text-muted">رصيد أرباح الإدارة المدورة</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-primary border fw-bold">
                                    {{ number_format($ahmedSharePct, 2) }}%
                                </span>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border">يحصّل نسب الشغل</span></td>
                            <td class="text-center">
                                <span class="badge bg-light text-success border fw-bold">
                                    {{ number_format($ahmedSharePct, 2) }}% + نسب الشغل
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary text-nowrap">+{{ number_format($totalAhmedTotalEarned, 2) }} <small>ج</small></span>
                                <div style="font-size:0.7rem;" class="text-muted">نسب الشغل + ربح الرصيد</div>
                            </td>
                            <td>
                                <span class="fw-bold text-danger text-nowrap">{{ $totalAhmedWithdrawn > 0 ? '-' . number_format($totalAhmedWithdrawn, 2) . ' ج' : '—' }}</span>
                            </td>
                            <td class="text-center no-print">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('partners.ahmed') }}" class="btn btn-outline-primary btn-action-icon" title="تقرير حساب الإدارة">
                                        <i class="fa fa-chart-line"></i>
                                    </a>
                                    @if($headPartner)
                                    <button type="button" class="btn btn-outline-success btn-action-icon" title="إيداع في حساب الإدارة" onclick="openAddCapitalModal({{ $headPartner->id }}, '{{ addslashes($headPartner->name) }}')">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-action-icon" title="سحب نقدي من رصيد حساب الإدارة" onclick="openWithdrawCapitalModal({{ $headPartner->id }}, '{{ addslashes($headPartner->name) }}', {{ $headPartner->capital }})">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-action-icon" title="تعديل اسم وبيانات الحساب" onclick='openEditPartnerModal({!! json_encode($headPartner) !!})'>
                                        <i class="fa fa-pen"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- قائمة الشركاء --}}
                        @forelse($partners as $p)
                        <tr class="{{ $p->status === 'exited' ? 'row-exited' : '' }}">
                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('partners.show', $p->id) }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $p->name }}
                                </a>
                                <small class="text-muted">
                                    <i class="fa fa-phone fa-xs me-1"></i>{{ $p->phone ?: 'بدون هاتف' }}
                                    · انضمام: {{ $p->joined_at ? date('Y-m-d', strtotime($p->joined_at)) : '—' }}
                                </small>
                                @if($p->status === 'exited')
                                    <span class="badge bg-danger ms-1" style="font-size:0.65rem;">متخارج</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-dark text-nowrap fs-6">{{ number_format($p->capital, 2) }} <small class="text-muted">ج</small></span>
                                @if($p->initial_capital > 0 && $p->capital != $p->initial_capital)
                                    <div style="font-size:0.7rem;" class="{{ $p->capital > $p->initial_capital ? 'text-success' : 'text-danger' }}">
                                        @if($p->capital > $p->initial_capital)
                                            <i class="fa fa-arrow-up me-1"></i> نما +{{ number_format($p->capital - $p->initial_capital, 2) }} ج
                                        @else
                                            <i class="fa fa-arrow-down me-1"></i> انخفض -{{ number_format($p->initial_capital - $p->capital, 2) }} ج
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- نسبة المساهمة المعتمدة --}}
                            <td class="text-center">
                                <span class="badge bg-light text-primary border fw-bold">
                                    {{ number_format($p->capital_share_pct, 2) }}%
                                </span>
                            </td>

                            {{-- نسبة الإدارة من ربح الشريك --}}
                            <td class="text-center">
                                <span class="badge bg-light text-dark border fw-bold" title="نسبة الإدارة من ربح الشريك {{ number_format($p->ahmed_ratio, 1) }}%">
                                    {{ number_format($p->ahmed_ratio, 1) }}%
                                </span>
                            </td>

                            {{-- النسبة الفعلية في أرباح الشركة --}}
                            <td class="text-center">
                                <span class="badge bg-light text-success border fw-bold">
                                    {{ number_format($p->effective_profit_pct, 2) }}%
                                </span>
                            </td>

                            <td>
                                <span class="fw-bold text-success text-nowrap">+{{ number_format($p->total_earned, 2) }} <small>ج</small></span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger text-nowrap">{{ $p->total_withdrawn_profits > 0 ? '-' . number_format($p->total_withdrawn_profits, 2) . ' ج' : '—' }}</span>
                            </td>

                            <td class="text-center no-print">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('partners.show', $p->id) }}" class="btn btn-outline-primary btn-action-icon" title="عرض السجل والتحليلات">
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
                            <td colspan="9" class="text-center py-4 text-muted">
                                لا يوجد شركاء مسجلين حتى الآن. ابدأ بإضافة شريك جديد عبر الزر المتاح بالأعلى.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                <div class="small text-muted">
                    إجمالي الحصص المعتمدة (شركاء + حساب الإدارة): <strong>{{ number_format($totalApprovedCapital, 2) }} ج</strong> (مجموع النسب = 100%)
                </div>
            </div>
        </div>

        {{-- سجل تاريخ تغير النسب والحصص (النسب كانت كام وبقت كام) --}}
        <div class="card-formal no-print">
            <div class="card-header-formal">
                <h5>
                    <i class="fa fa-history text-warning"></i> سجل تاريخ تغير النسب والحصص (النسب كانت كام وبقت كام)
                    <span class="badge bg-light text-dark border ms-2">{{ $shareHistories->total() }} عملية تغيير نسب مسجلة</span>
                </h5>
                <div class="text-muted small">
                    يُوثق تلقائياً كيف كانت نسبة ورصيد كل شريك وحساب الإدارة قبل الإقفال (أو السحب/الإيداع) وكيف أصبحت بعده.
                </div>
            </div>

            <div class="p-3">
                @forelse($shareHistories as $idx => $hist)
                    @php
                        $hLines = $historyLinesGrouped->get($hist->id, collect());
                        $isFirst = ($idx === 0);
                    @endphp
                    <div class="border rounded mb-3 overflow-hidden shadow-sm">
                        <div class="bg-light px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if($hist->event_type === 'period_closing')
                                    <span class="badge bg-primary"><i class="fa fa-calendar-check me-1"></i> إقفال دورة شهرية</span>
                                @elseif($hist->event_type === 'capital_withdrawal')
                                    <span class="badge bg-danger"><i class="fa fa-arrow-down me-1"></i> سحب من الرصيد / رأس المال</span>
                                @elseif($hist->event_type === 'capital_increase' || $hist->event_type === 'initial_deposit')
                                    <span class="badge bg-success"><i class="fa fa-arrow-up me-1"></i> إيداع / زيادة رأس مال</span>
                                @elseif($hist->event_type === 'partner_exit')
                                    <span class="badge bg-dark"><i class="fa fa-right-from-bracket me-1"></i> تخارج شريك</span>
                                @else
                                    <span class="badge bg-secondary">{{ $hist->event_type }}</span>
                                @endif
                                <strong class="text-dark">{{ $hist->title }}</strong>
                                <small class="text-muted">· بتاريخ: {{ date('Y-m-d H:i', strtotime($hist->created_at)) }}</small>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="small text-muted">
                                    إجمالي الحصص قبل: <strong>{{ number_format($hist->total_capital_before, 2) }} ج</strong>
                                    ← بعد: <strong class="text-dark">{{ number_format($hist->total_capital_after, 2) }} ج</strong>
                                </span>
                                <button class="btn btn-sm btn-outline-dark fw-bold py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHist_{{ $hist->id }}" aria-expanded="{{ $isFirst ? 'true' : 'false' }}">
                                    <i class="fa fa-table me-1"></i> عرض النسب (كانت كام وبقت كام)
                                </button>
                            </div>
                        </div>

                        <div class="collapse {{ $isFirst ? 'show' : '' }}" id="collapseHist_{{ $hist->id }}">
                            <div class="table-responsive">
                                <table class="table table-formal table-sm table-hover m-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 40px;">#</th>
                                            <th>الشريك / الحساب</th>
                                            <th class="text-center">الرصيد قبل</th>
                                            <th class="text-center" style="background:#334155;">النسبة كانت كام (%)</th>
                                            <th class="text-center">التغير في الرصيد</th>
                                            <th class="text-center">الرصيد بعد</th>
                                            <th class="text-center" style="background:#1e40af;">النسبة بقت كام (%)</th>
                                            <th class="text-center">فرق النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hLines as $hl)
                                        <tr style="{{ $hl->is_head ? 'background-color:#f8fafc; font-weight:700;' : '' }}">
                                            <td class="text-center">{{ $hl->is_head ? '—' : $loop->iteration }}</td>
                                            <td>
                                                <span class="fw-bold text-dark">{{ $hl->partner_name }}</span>
                                                @if($hl->is_head)
                                                    <span class="badge bg-dark ms-1" style="font-size:0.65rem;">حساب الإدارة</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($hl->capital_before, 2) }} ج</td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-secondary border fw-bold fs-6">
                                                    {{ number_format($hl->share_pct_before, 2) }}%
                                                </span>
                                            </td>
                                            <td class="text-center fw-bold {{ $hl->capital_change > 0 ? 'text-success' : ($hl->capital_change < 0 ? 'text-danger' : 'text-muted') }}">
                                                @if(abs($hl->capital_change) >= 0.01)
                                                    {{ $hl->capital_change > 0 ? '+' : '' }}{{ number_format($hl->capital_change, 2) }} ج
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-dark">{{ number_format($hl->capital_after, 2) }} ج</td>
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold fs-6">
                                                    {{ number_format($hl->share_pct_after, 2) }}%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($hl->share_pct_diff > 0.0001)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">
                                                        <i class="fa fa-arrow-up me-1"></i>+{{ number_format($hl->share_pct_diff, 2) }}%
                                                    </span>
                                                @elseif($hl->share_pct_diff < -0.0001)
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">
                                                        <i class="fa fa-arrow-down me-1"></i>{{ number_format($hl->share_pct_diff, 2) }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted small">ثابتة (0%)</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        لا توجد سجلات تغير نسب حتى الآن. سيتم تسجيل النسب تلقائياً عند كل إقفال دورة أو إيداع/سحب رأس مال.
                    </div>
                @endforelse
            </div>

            @if($shareHistories->hasPages())
            <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    عرض <strong>{{ $shareHistories->firstItem() ?? 0 }}</strong> إلى <strong>{{ $shareHistories->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $shareHistories->total() }}</strong> سجل
                </div>
                <div>
                    {{ $shareHistories->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>

        {{-- أرشيف التقفيلات الشهرية السابقة --}}
        <div class="card-formal">
            <div class="card-header-formal">
                <h5>
                    <i class="fa fa-calendar-alt text-success"></i> سجل التقفيلات الشهرية المعتمدة
                    <span class="badge bg-light text-dark border ms-2">{{ $periods->total() }} دورة مسجلة</span>
                </h5>
                <a href="{{ route('partners.closing') }}" class="btn btn-sm btn-outline-primary fw-bold no-print">
                    <i class="fa fa-file-invoice-dollar me-1"></i> فتح إقفال دورة جديدة
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-formal table-hover">
                    <thead>
                        <tr>
                            <th style="width: 38px;" class="text-center">#</th>
                            <th>اسم الدورة</th>
                            <th>الفترة المحاسبية</th>
                            <th>رأس مال البداية</th>
                            <th>رأس مال الإقفال</th>
                            <th>الربح التشغيلي (R)</th>
                            <th>صافي ربح الشركاء</th>
                            <th>ربح حساب الإدارة</th>
                            <th>المسحوب نقداً</th>
                            <th>المدوّر في الحصص</th>
                            <th class="text-center no-print">التقرير</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $per)
                        <tr>
                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                            <td class="fw-bold text-dark">{{ $per->period_name }}</td>
                            <td class="text-muted small text-nowrap">{{ $per->start_date }} → {{ $per->end_date }}</td>
                            <td class="text-nowrap">{{ number_format($per->opening_capital, 2) }} ج</td>
                            <td class="fw-bold text-dark text-nowrap">{{ number_format($per->closing_capital, 2) }} ج</td>
                            <td class="fw-bold text-nowrap {{ $per->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $per->gross_profit >= 0 ? '+' : '' }}{{ number_format($per->gross_profit, 2) }} ج
                            </td>
                            <td class="fw-bold text-dark text-nowrap">{{ number_format($per->total_partners_net_profit, 2) }} ج</td>
                            <td class="fw-bold text-primary text-nowrap">
                                +{{ number_format($per->ahmed_total_profit ?: $per->total_ahmed_commission, 2) }} ج
                                @if(!empty($per->ahmed_opening_capital) && $per->ahmed_opening_capital > 0)
                                    <div style="font-size:0.7rem;" class="text-muted">نسبته: {{ number_format($per->ahmed_capital_share_pct, 2) }}% ← {{ number_format($per->ahmed_closing_share_pct, 2) }}%</div>
                                @endif
                            </td>
                            <td class="fw-bold text-danger text-nowrap">{{ $per->total_withdrawn > 0 ? '-' . number_format($per->total_withdrawn, 2) . ' ج' : '—' }}</td>
                            <td class="fw-bold text-success text-nowrap">{{ $per->total_reinvested > 0 ? '+' . number_format($per->total_reinvested, 2) . ' ج' : '—' }}</td>
                            <td class="text-center no-print">
                                <a href="{{ route('partners.period.show', $per->id) }}" class="btn btn-sm btn-outline-dark fw-bold px-2 py-1">
                                    <i class="fa fa-eye me-1"></i> التقرير والنسب
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                لم يتم إقفال أي دورة شهرية حتى الآن. اضغط على «إقفال الدورة الشهرية» لحساب أرباح الدورة الأولى.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                <div class="small text-muted">
                    عرض <strong>{{ $periods->firstItem() ?? 0 }}</strong> إلى <strong>{{ $periods->lastItem() ?? 0 }}</strong> من إجمالي <strong>{{ $periods->total() }}</strong> دورة معتمدة
                </div>
                <div>
                    {{ $periods->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        {{-- خانات التوقيع والاعتماد الرسمية للطباعة --}}
        <div class="print-only" style="margin-top: 20px; page-break-inside: avoid;">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">إعداد وتدقيق الحسابات</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">وحدة الحسابات والرقابة المالية</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">المدير المالي التنفيذي</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">الشؤون المالية والاستثمار</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">التوقيع: ............................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="print-sig-col">
                        <div class="fw-bold mb-1" style="font-size:8.5pt;">اعتماد الإدارة</div>
                        <div class="text-muted small mb-4" style="font-size:7pt;">{{ $headName }}</div>
                        <div class="border-bottom border-dark border-1 mx-4 mb-2"></div>
                        <div class="small text-muted" style="font-size:7pt;">الاعتماد: ............................</div>
                    </div>
                </div>
            </div>
            <div class="text-center text-muted mt-2 pt-2 border-top" style="font-size:7pt;">
                سجل استثماري رسمي صادر عن النظام المالي لشركة الضبع · مستخرج بتاريخ {{ date('Y-m-d H:i') }}
            </div>
        </div>

    </div>
</div>

{{-- 1. MODAL: إضافة شريك جديد --}}
<div class="modal fade" id="modalAddPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('partners.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-user-plus me-2"></i> إضافة شريك جديد</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-secondary py-2 px-3 small rounded mb-3">
                    <i class="fa fa-info-circle me-1"></i> عند إضافة شريك ومعه رأس مال، يتم إيداعه في الخزينة المحددة وتحديث نسب المساهمة مع حفظ التغيير في سجل الهيستوري (النسب كانت كام وبقت كام).
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_head" value="1" id="addIsHead" onchange="toggleHeadFields('add', this.checked)">
                                <label class="form-check-label fw-bold" for="addIsHead">تعيين هذا الحساب كحساب الإدارة (أرباح نسب الشغل)</label>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                                حساب الإدارة تدخل فيه الأرباح من نسب الشغل مع الشركاء، وفي حالة عدم سحبها تزيد أرقامه وتأخذ حصتها من أرباح الدورة التالية.
                                @if($headPartner)
                                    <br><strong>حساب الإدارة الحالي: {{ $headPartner->name }}</strong>
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">اسم الشريك / الحساب <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="الاسم الكامل..." required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">رقم الهاتف للتواصل</label>
                        <input type="text" name="phone" class="form-control form-control-sm" placeholder="مثال: 010xxxxxxxx">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-success">مبلغ رأس المال المودع (ج) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="capital" id="addCapitalInput" class="form-control form-control-sm fw-bold text-success" placeholder="100000" required>
                        <small class="text-muted d-none" id="addCapitalHeadHint" style="font-size:0.72rem;">اختياري لحساب الإدارة: يبدأ رصيده من الأرباح المحصلة من نسب الشغل أو أي رصيد افتتاحي.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-primary">الخزينة المستلمة لرأس المال <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select form-select-sm fw-bold">
                            <option value="">-- تحديد الخزينة المستلمة --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6" id="addRatioWrap">
                        <label class="form-label fw-bold small">
                            نسبة الإدارة من أرباح الشريك (%) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" id="addRatioInput" class="form-control fw-bold" value="30.00" required>
                            <span class="input-group-text bg-white fw-bold">%</span>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size:0.72rem;">مثال: إذا كانت 30%، يحصل الشريك على 70% من ربح حصته وتدخل 30% في حساب الإدارة نظير الشغل والتشغيل.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">تاريخ الانضمام وبداية الشراكة</label>
                        <input type="date" name="joined_at" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small">ملاحظات أو بنود الاتفاق</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="أي تفاصيل أو شروط خاصة بالاتفاق...">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark btn-sm fw-bold px-4">
                    <i class="fa fa-check me-1"></i> حفظ وتوريد رأس المال
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 2. MODAL: زيادة رأس مال شريك حالي --}}
<div class="modal fade" id="modalAddCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formAddCapital" action="" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i> زيادة الرصيد / رأس المال</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 small">إضافة مبلغ لحساب: <strong class="text-dark fs-6" id="addCapPartnerName"></strong></p>

                <div class="mb-3">
                    <label class="form-label fw-bold small">المبلغ الإضافي المراد إيداعه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control form-control-sm fw-bold text-success" placeholder="المبلغ..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">الخزينة المستلمة للمبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-select-sm fw-bold" required>
                        <option value="">-- تحديد الخزينة --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">ملاحظات الإيداع</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="مثال: زيادة استثمارية...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark btn-sm fw-bold px-4">
                    <i class="fa fa-check me-1"></i> تأكيد الإيداع وتحديث النسب
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 3. MODAL: سحب من رأس مال الشريك --}}
<div class="modal fade" id="modalWithdrawCapital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formWithdrawCapital" action="" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-minus-circle me-2"></i> سحب من الرصيد / رأس المال</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger border-2 border-danger d-flex align-items-start gap-2 py-2 px-3 mb-3">
                    <i class="fa fa-triangle-exclamation text-danger fs-4 mt-1 flex-shrink-0"></i>
                    <div class="small">
                        <strong class="text-danger d-block mb-1">تنبيه هام:</strong>
                        هذا السحب يُخصم مباشرة من <u>الرصيد المعتمد في الحصص</u>، وسيتم تحديث النسب فوراً وتسجيل النسب قبل وبعد السحب في سجل الهيستوري.
                    </div>
                </div>

                <p class="mb-2 small">الحساب / الشريك: <strong class="fs-6 text-dark" id="withCapPartnerName"></strong></p>
                <div class="alert alert-secondary py-2 px-3 small rounded mb-3">
                    الرصيد الحالي المتاح: <strong class="fs-6" id="withCapCurrentVal">0.00</strong> ج.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">المبلغ المراد سحبه (ج) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" id="withCapAmountInput" class="form-control form-control-sm fw-bold text-danger" placeholder="المبلغ..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">الخزينة المصروف منها المبلغ <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-select-sm fw-bold" required>
                        <option value="">-- تحديد الخزينة للصرف --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">ملاحظات السحب</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="سبب السحب...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-danger btn-sm fw-bold px-4" onclick="return confirm('تأكيد نهائي: سيتم خصم المبلغ وتحديث نسب المساهمة وتسجيلها في الهيستوري. هل تريد المتابعة؟')">
                    <i class="fa fa-check me-1"></i> تأكيد وصرف المبلغ
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 4. MODAL: تعديل بيانات الشريك ونسبته --}}
<div class="modal fade" id="modalEditPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditPartner" action="" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-pen me-2"></i> تعديل البيانات والنسبة</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">الاسم <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editPartnerName" class="form-control form-control-sm" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">رقم الهاتف</label>
                    <input type="text" name="phone" id="editPartnerPhone" class="form-control form-control-sm">
                </div>

                <div class="mb-3" id="editRatioWrap">
                    <label class="form-label fw-bold small">نسبة الإدارة من الربح (%) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="ahmed_ratio" id="editPartnerAhmedRatio" class="form-control fw-bold" required>
                        <span class="input-group-text bg-white fw-bold">%</span>
                    </div>
                </div>

                <div class="mb-3 border rounded p-3 bg-light">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_head" value="1" id="editIsHead" onchange="toggleHeadFields('edit', this.checked)">
                        <label class="form-check-label fw-bold" for="editIsHead">تعيين هذا الحساب كحساب الإدارة الرئيسي</label>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:0.72rem;">
                        حساب الإدارة تدخل فيه الأرباح من نسب الشغل مع الشركاء، وما لا يُسحب يُضاف لرصيده ويأخذ حصته من الدورة القادمة.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">ملاحظات</label>
                    <input type="text" name="notes" id="editPartnerNotes" class="form-control form-control-sm">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-dark btn-sm fw-bold px-4">
                    <i class="fa fa-check me-1"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 5. MODAL: تصفية وتخارج نهائي للشريك --}}
<div class="modal fade" id="modalExitPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formExitPartner" action="" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold"><i class="fa fa-right-from-bracket me-2"></i> تصفية وتخارج الشريك من النظام</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger py-2 px-3 small rounded mb-3">
                    <i class="fa fa-exclamation-triangle me-1"></i> تنبيه: إجراء التخارج يترتب عليه تصفية كامل رأس مال الشريك وصرفه من الخزينة المختارة وتحويل حالته إلى «متخارج» وتحديث نسب باقي الشركاء في الهيستوري.
                </div>

                <p class="mb-3 small">الشريك: <strong class="text-danger fs-6" id="exitPartnerName"></strong> · الرصيد المستحق: <strong class="fs-6" id="exitCapVal">0.00</strong> ج</p>

                <div class="mb-3">
                    <label class="form-label fw-bold small">الخزينة المصروف منها مستحقات التصفية <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-select form-select-sm fw-bold" required>
                        <option value="">-- تحديد الخزينة لصرف التخارج --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (رصيد: {{ number_format($acc->balance) }} ج)</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">ملاحظات التصفية</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="تفاصيل اتفاق التخارج النهائي...">
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">تراجع</button>
                <button type="submit" class="btn btn-danger btn-sm fw-bold px-4" onclick="return confirm('تأكيد: هل أنت متأكد من تصفية وتخارج هذا الشريك وصرف مستحقاته من الخزينة؟')">
                    <i class="fa fa-check me-1"></i> اعتماد التصفية والتخارج
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function openAddCapitalModal(id, name) {
        document.getElementById('formAddCapital').action = '/partners/' + id + '/add-capital';
        document.getElementById('addCapPartnerName').innerText = name;
        new bootstrap.Modal(document.getElementById('modalAddCapital')).show();
    }

    function openWithdrawCapitalModal(id, name, capital) {
        const doOpen = () => {
            document.getElementById('formWithdrawCapital').action = '/partners/' + id + '/withdraw-capital';
            document.getElementById('withCapPartnerName').innerText = name;
            document.getElementById('withCapCurrentVal').innerText = Number(capital).toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('withCapAmountInput').max = capital;
            new bootstrap.Modal(document.getElementById('modalWithdrawCapital')).show();
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'تنبيه سحب من الرصيد',
                html: '<div style="font-size:1.05rem;line-height:1.7;color:#1e293b;" class="text-center">' +
                      'ده سحب من رصيد <strong class="text-danger">(' + name + ')</strong><br>' +
                      '<span class="fw-bold text-danger">هيتم تحديث نسب المساهمة وتسجيل النسب قبل وبعد السحب في الهيستوري.</span><br><br>' +
                      '<small class="text-muted">هل تريد المتابعة وفتح نموذج السحب؟</small>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa fa-arrow-left me-1"></i> متابعة السحب',
                cancelButtonText: 'إلغاء',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    doOpen();
                }
            });
        } else {
            if (confirm('تنبيه هام:\nده سحب من الرصيد (' + name + ') وهيأثر على النسب ويتسجل في الهيستوري.\n\nهل تريد المتابعة؟')) {
                doOpen();
            }
        }
    }

    function openEditPartnerModal(p) {
        document.getElementById('formEditPartner').action = '/partners/' + p.id + '/update';
        document.getElementById('editPartnerName').value = p.name || '';
        document.getElementById('editPartnerPhone').value = p.phone || '';
        document.getElementById('editPartnerAhmedRatio').value = p.ahmed_ratio || 30.00;
        document.getElementById('editPartnerNotes').value = p.notes || '';
        document.getElementById('editIsHead').checked = !!Number(p.is_head);
        toggleHeadFields('edit', !!Number(p.is_head));
        new bootstrap.Modal(document.getElementById('modalEditPartner')).show();
    }

    function toggleHeadFields(prefix, isHead) {
        const ratioWrap = document.getElementById(prefix + 'RatioWrap');
        const ratioInput = prefix === 'add' ? document.getElementById('addRatioInput') : document.getElementById('editPartnerAhmedRatio');
        if (ratioWrap) ratioWrap.style.display = isHead ? 'none' : '';
        if (ratioInput) ratioInput.required = !isHead;

        if (prefix === 'add') {
            const capInput = document.getElementById('addCapitalInput');
            const hint = document.getElementById('addCapitalHeadHint');
            if (capInput) capInput.required = !isHead;
            if (hint) hint.classList.toggle('d-none', !isHead);
        }
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
