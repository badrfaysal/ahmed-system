<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictEmployeeActions
{
    // 🔒 إجراءات (حذف / فسخ-إلغاء-شطب-استرجاع / تعديل تشغيلي) ممنوعة على دور "موظف" المقيّد —
    // متاحة فقط لـ admin و manager. لو موظف حاول ينفذ أي راوت من دول يترجع برسالة بدل التنفيذ.
    protected array $blocked = [
        // حذف
        'inquiries.destroy',
        'inventory.delete',
        'installments.delete',
        'installments.deleteDefaulted',
        'company_debts.delete',
        'goals.destroy',
        'assets.destroy',
        'notifications.destroy',
        'notifications.clear',
        'settings.destroyAccount',
        'settings.destroySupplier',
        'settings.destroyCompany',
        'settings.destroyStation',
        'settings.destroyItem',
        'settings.destroyExpenseCategory',

        // فسخ / إلغاء / شطب / استرجاع
        'installments.reverse_pay',
        'installments.writeoff',
        'installments.terminate',
        'debts.writeoff',
        'financial.cancel',
        'financial.capitalAdjustCancel',

        // تعديل تشغيلي (بيانات بيع/مخزون/أقساط/عملاء/أصول)
        'inventory.update',
        'installments.update',
        'customers.update',
        'assets.update',
        'inquiries.update',
        'treasury.updateManualBalance',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = session('auth_user');
        $routeName = $request->route()?->getName();

        if ($user && $user->role === 'employee' && $routeName && in_array($routeName, $this->blocked, true)) {
            return back()->with('error', '⛔ هذا الإجراء يحتاج صلاحية أعلى (مدير أو أدمن). برجاء التواصل مع المسؤول.');
        }

        return $next($request);
    }
}
