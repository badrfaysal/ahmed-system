<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'بوابة العميل') - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('partials.theme', ['accent' => 'navy'])

    <style>
        body {
            background: linear-gradient(180deg, var(--c-bg) 0%, #e5edf7 100%);
            min-height: 100vh; margin: 0;
        }

        .portal-header {
            background: linear-gradient(135deg, var(--c-navy) 0%, var(--c-navy-soft) 100%);
            color: #fff; padding: 18px 0;
            box-shadow: var(--shadow-md);
            position: sticky; top: 0; z-index: 100;
        }
        .portal-header .container { display: flex; justify-content: space-between; align-items: center; }
        .brand { display: flex; align-items: center; gap: 12px; color: #fff; text-decoration: none; }
        .brand-icon {
            width: 44px; height: 44px; border-radius: var(--r-md);
            background: var(--c-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: var(--c-navy);
        }
        .brand h6 { margin: 0; font-weight: 600; font-size: 1.05rem; line-height: 1.2; color: #fff; }
        .brand span { font-size: 0.78rem; color: rgba(255,255,255,0.7); font-weight: 500; }

        .header-user { display: flex; align-items: center; gap: 12px; }
        .header-user .name { font-weight: 600; font-size: 0.92rem; }
        .header-user .sub { font-size: 0.74rem; color: rgba(255,255,255,0.6); }
        .header-logout {
            background: rgba(255,255,255,0.1); color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: var(--r-md); padding: 7px 14px;
            font-weight: 500; font-size: 0.84rem;
            text-decoration: none; transition: var(--t-fast);
        }
        .header-logout:hover { background: var(--c-danger); border-color: var(--c-danger); color: #fff; }

        .portal-main { padding: 30px 0 60px; }
        .portal-card {
            background: var(--c-surface);
            border-radius: var(--r-lg);
            padding: 22px;
            border: 1px solid var(--c-border);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .portal-footer {
            background: var(--c-surface); padding: 20px 0;
            border-top: 1px solid var(--c-border); text-align: center;
            color: var(--c-text-muted); font-size: 0.84rem;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .brand h6 { font-size: 0.95rem; }
            .header-user .name { display: none; }
        }
    </style>
    @yield('extra-head')
</head>
<body>

@if(session('portal_customer'))
    <header class="portal-header">
        <div class="container">
            <a href="{{ route('portal.dashboard') }}" class="brand">
                <div class="brand-icon"><i class="fa fa-building-columns"></i></div>
                <div>
                    <h6>شركة الضبع</h6>
                    <span>بوابة العميل</span>
                </div>
            </a>
            <div class="header-user">
                <div class="text-end">
                    <div class="name">{{ session('portal_customer')['name'] }}</div>
                    <div class="sub">عميل مسجل</div>
                </div>
                <form action="{{ route('portal.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="header-logout">
                        <i class="fa fa-right-from-bracket me-1"></i> خروج
                    </button>
                </form>
            </div>
        </div>
    </header>
@endif

<main class="portal-main">
    <div class="container">
        @yield('content')
    </div>
</main>

<footer class="portal-footer">
    <div class="container">
        © {{ date('Y') }} شركة الضبع - جميع الحقوق محفوظة
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('extra-scripts')
</body>
</html>
