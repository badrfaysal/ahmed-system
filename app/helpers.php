<?php

if (!function_exists('fmtMoney')) {
    /**
     * تنسيق المبالغ المالية مع إظهار الكسور (القروش) فقط عند وجودها.
     * 5000  => "5,000"      |  5000.5 => "5,000.50"
     * بدون تقريب للجنيه — بيحافظ على القروش الحقيقية في العرض.
     */
    function fmtMoney($val): string
    {
        $v = (float) $val;
        // round(.,2) بيتعامل مع أخطاء الفاصلة العائمة (مثلاً 100.00000001)
        $v = round($v, 2);
        return fmod($v, 1.0) === 0.0
            ? number_format($v, 0, '.', ',')
            : number_format($v, 2, '.', ',');
    }
}

if (!function_exists('finMask')) {
    /**
     * يغلّف رقم مالي حساس بميزة الإخفاء الاختياري (users.hide_financials).
     * لو الموظف الحالي مقيّد: الرقم بيتعرض متنقّط افتراضيًا مع أيقونة عين لإظهاره/إخفائه لحظيًا
     * (بدون إعادة تحميل الصفحة) — راجع window.toggleFinMask في sidebar.blade.php.
     */
    function finMask(string $displayValue): string
    {
        $user = session('auth_user');
        $isHidden = $user && (int) ($user->hide_financials ?? 0) === 1;

        if (!$isHidden) {
            return e($displayValue);
        }

        $real = e($displayValue);
        return '<span class="fin-mask">'
             . '<span class="fin-text" data-real="' . $real . '" data-masked="••••••">••••••</span>'
             . '<i class="fa fa-eye fin-eye-toggle" onclick="toggleFinMask(this, event)" title="إظهار/إخفاء الرقم"></i>'
             . '</span>';
    }
}
