@extends('portal.layout')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-6 col-lg-5">

        <div class="text-center mb-4">
            <div style="
                width: 88px; height: 88px;
                background: var(--c-navy);
                border-radius: var(--r-lg);
                display: inline-flex; align-items: center; justify-content: center;
                font-size: 2.4rem; color: var(--c-accent);
                box-shadow: var(--shadow-md);
            ">
                <i class="fa fa-building-columns"></i>
            </div>
            <h2 class="fw-bold mt-3 mb-1" style="color: var(--c-navy);">شركة الضبع</h2>
            <p class="muted-pro">بوابة العميل - تابع أقساطك وفواتيرك</p>
        </div>

        <div class="portal-card">

            @if($errors->any())
                <div class="alert-pro danger"><i class="fa fa-circle-exclamation"></i>{{ $errors->first() }}</div>
            @endif

            @if(session('msg'))
                <div class="alert-pro success"><i class="fa fa-circle-check"></i>{{ session('msg') }}</div>
            @endif

            <h5 class="fw-bold mb-3" style="color: var(--c-navy);">
                <i class="fa fa-key me-2" style="color: var(--c-accent);"></i> ادخل بكود البوابة   
            </h5>
            <p class="muted-pro small mb-3">
                هتقدر تشوف كل عقودك وأقساطك المتبقية وميعاد كل استحقاق.
            </p>

            <form action="{{ route('portal.login.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-pro-label">
                        <i class="fa fa-id-card me-1" style="color: var(--c-accent);"></i> كودك الشخصي
                    </label>
                    <input type="text"
                           name="identifier"
                           class="form-control"
                           style="font-size: 1.2rem; letter-spacing: 1.5px; direction: ltr;
                                  border-radius: var(--r-md); border: 2px solid var(--c-border-2); padding: 14px;
                                  text-align: center; font-weight: 700; font-family: monospace;
                                  text-transform: uppercase;"
                           placeholder=" XXXXXXXXX"
                           autofocus
                           autocomplete="off"
                           value="{{ old('identifier') }}"
                           required>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <i class="fa fa-circle-info"></i> اطلب كود البوابة من المحاسب لو مش موجود معاك.
                    </small>
                </div>

                <button type="submit" class="btn-pro btn-pro-primary btn-pro-lg w-100">
                    <i class="fa fa-arrow-left-long"></i> دخول
                </button>
            </form>

            <div class="divider-pro"></div>

            <div class="text-center small muted-pro">
                <i class="fa fa-shield-halved" style="color: var(--c-success);"></i>
                بياناتك محمية ولا يمكن لأي شخص آخر الدخول لحسابك.
            </div>
        </div>

        <div class="text-center mt-3 small muted-pro">
            <i class="fa fa-circle-info"></i>
            عندك مشكلة؟ تواصل مع الشركة على
            <a href="tel:+201000000000" style="color: var(--c-accent); text-decoration: none;">01000000000</a>
        </div>
    </div>
</div>
@endsection
