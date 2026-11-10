<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $screenTitle ?? 'الشاشة قيد التنفيذ' }} - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('partials.theme')

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            overflow-x: hidden;
        }
        .main-content {
            margin-right: 250px;
            padding: 36px 28px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 991px) {
            .main-content {
                margin-right: 0 !important;
                width: 100% !important;
                padding: 80px 16px 40px !important;
            }
        }
        .coming-soon-card {
            max-width: 620px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: 5px solid #f59e0b;
            border-radius: 22px;
            padding: 48px 36px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.07);
            position: relative;
            overflow: hidden;
        }
        .coming-soon-icon-wrap {
            width: 96px;
            height: 96px;
            margin: 0 auto 24px;
            border-radius: 26px;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 2px solid #fde68a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.6rem;
            color: #d97706;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.18);
        }
        .coming-soon-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 800;
            font-size: 0.85rem;
            padding: 6px 16px;
            border-radius: 999px;
            margin-bottom: 16px;
        }
        .coming-soon-title {
            font-weight: 900;
            font-size: 1.65rem;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .coming-soon-msg {
            font-weight: 700;
            font-size: 1.15rem;
            color: #b45309;
            background: #fffbeb;
            border: 1px dashed #fcd34d;
            border-radius: 14px;
            padding: 16px 20px;
            margin: 18px 0;
        }
        .coming-soon-sub {
            color: #64748b;
            font-size: 0.94rem;
            line-height: 1.7;
            margin-bottom: 28px;
        }
    </style>
</head>
<body>

@include('sidebar')

<div class="main-content">
    <div class="coming-soon-card">
        <div class="coming-soon-icon-wrap">
            <i class="fa {{ $screenIcon ?? 'fa-screwdriver-wrench' }}"></i>
        </div>

        <div class="coming-soon-badge">
            <i class="fa fa-clock"></i>
            <span>{{ $screenTitle ?? 'قسم قيد التطوير' }}</span>
        </div>

        <h3 class="coming-soon-title">الشاشة قيد التنفيذ سوف تتاح قريباً</h3>

        <div class="coming-soon-msg">
            <i class="fa fa-gears me-2"></i>
            جاري العمل حالياً على استكمال ومراجعة هذه الشاشة، وسوف تتاح للاستخدام قريباً بإذن الله.
        </div>

        <p class="coming-soon-sub">
            يمكنك متابعة العمل على باقي شاشات النظام بشكل طبيعي من خلال القائمة الجانبية.
        </p>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="{{ route('treasury.index') }}" class="btn btn-dark fw-bold px-4 py-2 rounded-pill shadow-sm">
                <i class="fa fa-wallet me-1"></i> المركز المالي (الخزائن)
            </a>
            <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary fw-bold px-4 py-2 rounded-pill">
                <i class="fa fa-home me-1"></i> لوحة التحكم
            </a>
        </div>
    </div>
</div>

</body>
</html>
