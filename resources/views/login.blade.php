<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - شركة الضبع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #0d1f35, #1a3a5f, #0f172a);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 16px;
        }
        .login-card {
            background: #fff; border-radius: 28px;
            box-shadow: 0 24px 64px rgba(0,0,0,.35);
            width: 100%; max-width: 460px; padding: 44px 40px;
            animation: cardIn .5s cubic-bezier(.34,1.56,.64,1) both;
        }
        @media (max-width: 480px) {
            .login-card { padding: 32px 22px; border-radius: 22px; }
            .role-picker { margin-bottom: 22px; }
        }
        @keyframes cardIn {
            from { opacity:0; transform: scale(.88) translateY(30px); }
            to   { opacity:1; transform: scale(1) translateY(0); }
        }
        .logo-circle {
            width: 76px; height: 76px;
            background: linear-gradient(135deg, #1a3a5f, #2563eb);
            color: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 2rem;
            margin: 0 auto 18px;
            box-shadow: 0 10px 28px rgba(37,99,235,.35);
            animation: logoPulse 3s ease-in-out infinite;
        }
        @keyframes logoPulse {
            0%,100% { box-shadow: 0 10px 28px rgba(37,99,235,.35); }
            50%      { box-shadow: 0 10px 40px rgba(37,99,235,.55); }
        }
        .role-picker { display:flex; gap:10px; margin-bottom:28px; }
        .role-btn {
            flex:1; padding:12px 8px; border-radius:14px;
            border:2px solid #e2e8f0; background:#f8fafc; color:#64748b;
            font-family:'Cairo',sans-serif; font-weight:700; font-size:.88rem;
            cursor:pointer; transition:all .25s cubic-bezier(.34,1.56,.64,1);
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .role-btn:hover { transform:translateY(-2px); border-color:#93c5fd; background:#eff6ff; color:#1d4ed8; }
        .role-btn.active {
            border-color:#2563eb;
            background:linear-gradient(135deg,#dbeafe,#eff6ff);
            color:#1d4ed8; box-shadow:0 4px 16px rgba(37,99,235,.2);
            transform:translateY(-2px);
        }
        .form-label { font-weight:700; font-size:.82rem; color:#64748b; margin-bottom:6px; }
        .input-wrap { position:relative; }
        .input-wrap .form-control {
            background:#f8fafc; border:2px solid #e2e8f0; border-radius:14px;
            padding:13px 18px 13px 48px;
            font-family:'Cairo',sans-serif; font-weight:700; font-size:.95rem;
            transition:.25s; width:100%;
        }
        .input-wrap .form-control:focus {
            border-color:#2563eb; background:#fff;
            box-shadow:0 0 0 4px rgba(37,99,235,.1); outline:none;
        }
        .input-icon {
            position:absolute; left:16px; top:50%;
            transform:translateY(-50%); color:#94a3b8; font-size:.9rem;
        }
        .field-hint { font-size:.72rem; color:#94a3b8; font-weight:600; margin-top:5px; padding-right:4px; }
        .btn-login {
            background:linear-gradient(135deg,#1a3a5f,#2563eb); border:none;
            border-radius:14px; padding:14px;
            font-family:'Cairo',sans-serif; font-weight:800; font-size:1rem;
            color:#fff; width:100%; cursor:pointer; transition:.25s;
            box-shadow:0 6px 20px rgba(37,99,235,.3);
        }
        .btn-login:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(37,99,235,.4); }
        .btn-login:active { transform:translateY(0); }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="logo-circle"><i class="fa fa-building-shield"></i></div>
        <h3 class="fw-bold text-dark mb-1" style="font-size:1.4rem;">شركة الضبع</h3>
        <p class="text-muted fw-bold" style="font-size:.8rem; margin:0;">النظام الإداري والمالي الموحد</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger fw-bold rounded-3 text-center mb-4" style="font-size:.85rem;">
            <i class="fa fa-triangle-exclamation me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Role picker — تلوين فقط بدون كتابة تلقائية --}}
    <div class="role-picker">
        <button type="button" class="role-btn" id="btn_admin" onclick="selectRole('admin')">
            <i class="fa fa-user-tie"></i> مدير
        </button>
        <button type="button" class="role-btn" id="btn_employee" onclick="selectRole('employee')">
            <i class="fa fa-user"></i> موظف
        </button>
    </div>

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="mb-4">
            <label class="form-label">الاسم الثنائي أو البريد الإلكتروني</label>
            <div class="input-wrap">
                <input type="text" name="email" id="name_input"
                       class="form-control"
                       placeholder="مثال: أحمد محمد"
                       value=""
                       required autocomplete="" dir="auto">
                <i class="fa fa-user input-icon"></i>
            </div>
            <div class="field-hint"><i class="fa fa-info-circle me-1"></i>اكتب اسمك كما هو مسجل في النظام</div>
        </div>

        <div class="mb-4">
            <label class="form-label">كلمة المرور</label>
            <div class="input-wrap">
                <input type="password" name="password"
                       class="form-control" placeholder=""
                       required autocomplete="">
                <i class="fa fa-lock input-icon"></i>
            </div>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
            <label class="form-check-label fw-bold text-muted" for="remember" style="font-size:.82rem;">
                تذكر بيانات الدخول
            </label>
        </div>

        <button type="submit" class="btn-login">
            <i class="fa fa-right-to-bracket me-2"></i> دخول للنظام
        </button>
    </form>
</div>
<script>
    function selectRole(role) {
        document.getElementById('btn_admin').classList.toggle('active', role === 'admin');
        document.getElementById('btn_employee').classList.toggle('active', role === 'employee');

        var input = document.getElementById('name_input');
        if (role === 'admin') {
            input.value = 'admin@aldabaa.com';
            input.focus();
            // تحديد النص عشان يسهل الكتابة فوقه لو حاب يغيره
            input.select();
        } else {
            // لو اختار موظف، امسح الإيميل وخليه يكتب اسمه
            if (input.value === 'admin@aldabaa.com') {
                input.value = '';
            }
            input.focus();
        }
    }
</script>
</body>
</html>