<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditService;

/**
 * يلتقط كل عمليات الكتابة (POST/PUT/PATCH/DELETE) ويسجلها أوتوماتيك في audit_logs.
 * يستخرج اسم القسم والعملية من اسم الـ route (route name).
 * ميسجلش طلبات GET أو الـ redirects الفاشلة (4xx/5xx).
 */
class AuditRequestMiddleware
{
    /**
     * Map من route name → [module, action, summary template].
     * لو الـ route مش موجود هنا، بياخد الـ route name كما هو.
     */
    private const ROUTE_LABELS = [
        // ── أقساط ──
        'installments.store'             => ['installments', 'create',      'إنشاء عقد تقسيط جديد', 'info'],
        'installments.pay'               => ['installments', 'pay',         'تحصيل قسط من عميل', 'info'],
        'installments.update'            => ['installments', 'update',      'تعديل بيانات عقد تقسيط', 'warning'],
        'installments.delete'            => ['installments', 'cancel',      'فسخ عقد تقسيط', 'critical'],
        'installments.pay_bulk'          => ['installments', 'pay_bulk',    'تحصيل أقساط متعددة', 'info'],
        'installments.reverse_pay'       => ['installments', 'reverse',     'عكس دفعة قسط', 'critical'],
        'installments.deleteDefaulted'   => ['installments', 'delete_late', 'حذف قسط متأخر', 'critical'],
        'installments.writeoff'          => ['installments', 'writeoff',    'إعدام دين عقد', 'critical'],

        // ── مالية ──
        'financial.store'                => ['finance',      'create',      'إنشاء عملية مالية جديدة', 'info'],
        'financial.cancel'               => ['finance',      'cancel',      'إلغاء عملية مالية', 'critical'],
        'financial.pay_installment'      => ['finance',      'pay',         'تحصيل قسط من شاشة المالية', 'info'],
        'financial.pay_bulk_installments'=> ['finance',      'pay_bulk',    'تحصيل أقساط متعددة', 'info'],
        'shift.close'                    => ['finance',      'shift_close', 'تقفيل وردية الكاشير', 'warning'],
        'treasury.snapshot'              => ['finance',      'snapshot',    'أخذ snapshot لرأس المال', 'info'],
        'treasury.updateManualBalance'   => ['finance',      'update_bal',  'تعديل يدوي لرصيد خزنة', 'critical'],

        // ── مبيعات ──
        'sales.store'                    => ['sales',        'create',      'تسجيل بيعة مباشرة', 'info'],

        // ── مخزون ──
        'inventory.store'                => ['inventory',    'create',      'توريد بضاعة جديدة للمخزن', 'info'],
        'inventory.sell'                 => ['inventory',    'sell',        'بيع من المخزن', 'info'],
        'inventory.return'               => ['inventory',    'return',      'مرتجع للمخزن', 'info'],
        'inventory.restock'              => ['inventory',    'restock',     'إعادة تموين صنف', 'info'],
        'inventory.update'               => ['inventory',    'update',      'تعديل بيانات صنف', 'warning'],
        'inventory.delete'               => ['inventory',    'delete',      'حذف صنف من المخزن', 'critical'],
        'inventory.return_supplier'      => ['inventory',    'ret_supp',    'إرجاع للمورد', 'warning'],
        'inventory.adjust'               => ['inventory',    'adjust',      'تسوية جرد المخزن', 'warning'],
        'inventory.transfer'             => ['inventory',    'transfer',    'نقل بضاعة بين مخازن', 'info'],

        // ── بنزينة ──
        'gas.store'                      => ['gas_station',  'create',      'تسجيل عملية بنزينة', 'info'],

        // ── ديون ──
        'debts.discount'                 => ['debts',        'discount',    'منح خصم لعميل', 'warning'],
        'debts.writeoff'                 => ['debts',        'writeoff',    'إعدام دين عميل', 'critical'],
        'company_debts.store'            => ['company_debts','create',      'تسجيل دين على الشركة', 'info'],
        'company_debts.pay'              => ['company_debts','pay',         'سداد دين على الشركة', 'info'],
        'company_debts.pay_bulk'         => ['company_debts','pay_bulk',    'سداد جماعي لديون', 'info'],
        'company_debts.delete'           => ['company_debts','delete',      'حذف دين على الشركة', 'critical'],

        // ── مصروفات ──
        'expenses.store'                 => ['expenses',     'create',      'تسجيل مصروف جديد', 'info'],
        'settings.storeExpenseCategory'  => ['settings',     'create',      'إضافة فئة مصروف', 'info'],
        'settings.updateExpenseCategory' => ['settings',     'update',      'تعديل فئة مصروف', 'info'],
        'settings.destroyExpenseCategory'=> ['settings',     'delete',      'حذف فئة مصروف', 'warning'],

        // ── شركاء ──
        'partners.store'                 => ['partners',     'create',      'إضافة شريك جديد', 'critical'],
        'partners.distributeProfit'      => ['partners',     'distribute',  'توزيع أرباح على الشركاء', 'critical'],
        'partners.withdrawProfit'        => ['partners',     'withdraw',    'سحب شريك من حسابه', 'critical'],
        'partners.reinvest'              => ['partners',     'reinvest',    'إعادة استثمار أرباح شريك', 'warning'],
        'partners.exitPartner'           => ['partners',     'exit',        'إخراج شريك من الشراكة', 'critical'],

        // ── HR ──
        'hr.store'                       => ['hr',           'create',      'إضافة موظف جديد', 'info'],
        'hr.transaction'                 => ['hr',           'tx',          'حركة على حساب موظف (سلفة/خصم)', 'info'],
        'hr.pay'                         => ['hr',           'pay',         'صرف راتب موظف', 'warning'],
        'hr.update'                      => ['hr',           'update',      'تعديل بيانات موظف', 'info'],
        'hr.delete'                      => ['hr',           'delete',      'حذف موظف', 'critical'],

        // ── أصول ──
        'assets.store'                   => ['assets',       'create',      'تسجيل أصل ثابت جديد', 'info'],
        'assets.update'                  => ['assets',       'update',      'تعديل بيانات أصل', 'info'],
        'assets.depreciate'              => ['assets',       'depreciate',  'إهلاك أصل', 'info'],
        'assets.manual_depreciate'       => ['assets',       'depreciate',  'إهلاك يدوي لأصل', 'warning'],
        'assets.sell'                    => ['assets',       'sell',        'بيع أصل ثابت', 'warning'],
        'assets.destroy'                 => ['assets',       'delete',      'حذف أصل من السجلات', 'critical'],

        // ── أهداف ──
        'goals.store'                    => ['goals',        'create',      'إنشاء هدف جديد', 'info'],
        'goals.close'                    => ['goals',        'close',       'إغلاق هدف', 'info'],
        'goals.destroy'                  => ['goals',        'delete',      'حذف هدف', 'warning'],

        // ── مستخدمين ──
        'users.store'                    => ['users',        'create',      'إنشاء حساب مستخدم جديد', 'critical'],
        'users.resetPassword'            => ['users',        'reset_pwd',   'إعادة تعيين كلمة سر مستخدم', 'critical'],
        'users.destroy'                  => ['users',        'delete',      'حذف مستخدم', 'critical'],
        'profile.updateName'             => ['profile',      'update',      'تعديل اسم البروفايل', 'info'],

        // ── إعدادات ──
        'settings.storePaymentMethod'    => ['settings',     'create',      'إضافة محفظة/خزنة', 'info'],
        'settings.renameAccount'         => ['settings',     'rename',      'تعديل اسم محفظة', 'info'],
        'settings.updateCommission'      => ['settings',     'commission',  'تعديل إعدادات العمولات', 'warning'],
        'settings.storeSupplier'         => ['settings',     'create',      'إضافة مورد', 'info'],
        'settings.destroySupplier'       => ['settings',     'delete',      'حذف مورد', 'warning'],
        'settings.storeCompany'          => ['settings',     'create',      'إضافة شركة نقل', 'info'],
        'settings.destroyCompany'        => ['settings',     'delete',      'حذف شركة نقل', 'warning'],
        'settings.storeStation'          => ['settings',     'create',      'إضافة محطة بنزين', 'info'],
        'settings.destroyStation'        => ['settings',     'delete',      'حذف محطة بنزين', 'warning'],
        'settings.storeItem'             => ['settings',     'create',      'إضافة صنف', 'info'],
        'settings.destroyItem'           => ['settings',     'delete',      'حذف صنف', 'warning'],

        // ── عملاء / استفسارات ──
        'customers.store'                => ['customers',    'create',      'إضافة عميل للأرشيف', 'info'],
        'inquiries.store'                => ['inquiries',    'create',      'تسجيل استفسار جديد', 'info'],
        'inquiries.toggle'               => ['inquiries',    'toggle',      'تأكيد/إرجاع تواصل مع عميل', 'info'],
        'inquiries.update'               => ['inquiries',    'update',      'تعديل استفسار', 'info'],
        'inquiries.destroy'              => ['inquiries',    'delete',      'حذف استفسار', 'warning'],
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // نسجل بس عمليات الكتابة الناجحة
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $response;
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            return $response;
        }

        $routeName = $request->route()?->getName() ?? '';

        // تجاهل routes الـ audit نفسها + login/logout (متسجلين بالفعل)
        $skip = ['audit.', 'login', 'logout', 'portal.login', 'portal.logout'];
        foreach ($skip as $s) {
            if (str_starts_with($routeName, $s) || $routeName === $s) {
                return $response;
            }
        }

        // استخراج التفاصيل
        if (isset(self::ROUTE_LABELS[$routeName])) {
            [$module, $action, $summary, $severity] = self::ROUTE_LABELS[$routeName];
        } else {
            // fallback: نستنتج من route name
            $parts   = explode('.', $routeName);
            $module  = $parts[0] ?? 'unknown';
            $action  = $parts[1] ?? strtolower($request->method());
            $summary = $routeName ?: ($request->method() . ' ' . $request->path());
            $severity= 'info';
        }

        // إضافة سياق إضافي من الـ request (لو فيه id أو amount مثلاً)
        $context = $this->extractContext($request);
        if ($context) {
            $summary .= ' — ' . $context;
        }

        // البيانات اللي اتبعتت (بدون السرية)
        $safeInput = $request->except(['password', 'password_confirmation', '_token', 'old_password', 'new_password']);

        AuditService::log($action, $module, $summary, null, $safeInput, null, null, $severity);

        return $response;
    }

    /**
     * يحاول يستخرج سياق مفيد من الـ request (مبلغ، اسم عميل، ID).
     */
    private function extractContext(Request $request): ?string
    {
        $parts = [];

        $name = $request->input('customer_name') ?? $request->input('creditor_name') ?? $request->input('name');
        if ($name) $parts[] = $name;

        $amount = $request->input('amount') ?? $request->input('total_amount') ?? $request->input('actual_amount');
        if ($amount && is_numeric($amount)) $parts[] = number_format((float) $amount, 0) . ' ج';

        $id = $request->input('inst_id') ?? $request->input('id') ?? $request->route('id');
        if ($id && is_scalar($id)) $parts[] = "#{$id}";

        return empty($parts) ? null : implode(' | ', $parts);
    }
}
