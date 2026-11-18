<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScreenAccessService
{
    /**
     * Request-level memoization for fresh user permission rows to avoid redundant DB queries
     * while still reflecting changes made by an admin immediately on the next request.
     */
    private static array $userCache = [];

    /**
     * Clear in-memory cache (helpful across multiple requests in PHPUnit tests).
     */
    public static function clearCache(): void
    {
        self::$userCache = [];
    }

    /**
     * Canonical catalog of all screens in the system.
     * Order defines display order in Settings and priority fallback for home redirect.
     */
    public static function screens(): array
    {
        return [
            'dashboard' => [
                'key'            => 'dashboard',
                'label'          => 'لوحة التحكم',
                'subtitle'       => 'ملخص كل النظام',
                'route'          => 'dashboard.index',
                'group'          => 'المالية والخزينة',
                'icon'           => 'fa-gauge-high',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'treasury' => [
                'key'            => 'treasury',
                'label'          => 'المركز المالي (الخزائن)',
                'subtitle'       => 'أرصدة محافظ ورأس المال',
                'route'          => 'treasury.index',
                'group'          => 'المالية والخزينة',
                'icon'           => 'fa-wallet',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'financial_ops' => [
                'key'            => 'financial_ops',
                'label'          => 'العمليات المالية',
                'subtitle'       => 'تحويلات، عهد، إيداعات',
                'route'          => 'financial.index',
                'group'          => 'المالية والخزينة',
                'icon'           => 'fa-file-invoice-dollar',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'expenses' => [
                'key'            => 'expenses',
                'label'          => 'دفتر المصروفات',
                'subtitle'       => 'إيجار، كهرباء، صيانة',
                'route'          => 'expenses.index',
                'group'          => 'المالية والخزينة',
                'icon'           => 'fa-receipt',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'goals' => [
                'key'            => 'goals',
                'label'          => 'الأهداف',
                'subtitle'       => 'تتبع الأهداف المالية',
                'route'          => 'goals.index',
                'group'          => 'المالية والخزينة',
                'icon'           => 'fa-bullseye',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'reports' => [
                'key'            => 'reports',
                'label'          => 'التقارير والأرباح',
                'subtitle'       => 'تدفقات نقدية، أداء المبيعات',
                'route'          => 'reports.index',
                'group'          => 'المالية والخزينة',
                'icon'           => 'fa-chart-column',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'sales' => [
                'key'            => 'sales',
                'label'          => 'بيع الخدمات',
                'subtitle'       => 'صيانة، تركيب، خدمات الفنيين',
                'route'          => 'sales.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-credit-card',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'ac' => [
                'key'            => 'ac',
                'label'          => 'تكييفات وصيانة',
                'subtitle'       => 'إدارة التكييفات',
                'route'          => 'ac.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-snowflake',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'manual_invoices' => [
                'key'            => 'manual_invoices',
                'label'          => 'الفواتير اليدوية',
                'subtitle'       => 'إصدار وطباعة ومشاركة واتساب',
                'route'          => 'manual_invoices.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-file-invoice',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'inventory' => [
                'key'            => 'inventory',
                'label'          => 'إدارة المخزن',
                'subtitle'       => 'جرد، توريد، مرتجعات',
                'route'          => 'inventory.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-boxes-stacked',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'gas' => [
                'key'            => 'gas',
                'label'          => 'محطة الوقود',
                'subtitle'       => 'مسحوبات سولار واستقطاعات',
                'route'          => 'gas.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-gas-pump',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'installments' => [
                'key'            => 'installments',
                'label'          => 'منظومة الأقساط',
                'subtitle'       => 'عقود، نسبة، تحصيل',
                'route'          => 'installments.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-calendar-check',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'debts' => [
                'key'            => 'debts',
                'label'          => 'مستحقات العملاء (لنا)',
                'subtitle'       => 'ديون متأخرة، خصومات',
                'route'          => 'debts.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-arrow-trend-up',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'company_debts' => [
                'key'            => 'company_debts',
                'label'          => 'ديون الشركة (علينا)',
                'subtitle'       => 'فواتير موردين، عمولات',
                'route'          => 'company_debts.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-arrow-trend-down',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'customers' => [
                'key'            => 'customers',
                'label'          => 'أرشيف العملاء',
                'subtitle'       => 'قاعدة بيانات، ائتمان',
                'route'          => 'customers.archive',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-address-book',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'inquiries' => [
                'key'            => 'inquiries',
                'label'          => 'المهام',
                'subtitle'       => 'تكليف وإرسال المهام',
                'route'          => 'inquiries.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-list-check',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'whatsapp' => [
                'key'            => 'whatsapp',
                'label'          => 'مركز واتساب',
                'subtitle'       => 'رسائل تذكير وكشوفات',
                'route'          => 'whatsapp.center',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-comments',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'assets' => [
                'key'            => 'assets',
                'label'          => 'الأصول الثابتة',
                'subtitle'       => 'سيارات، معدات، إهلاك',
                'route'          => 'assets.index',
                'group'          => 'المبيعات والعملاء',
                'icon'           => 'fa-truck-pickup',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'notifications' => [
                'key'            => 'notifications',
                'label'          => 'الإشعارات وسجل النشاط',
                'subtitle'       => 'مراقبة حركات النظام',
                'route'          => 'notifications.index',
                'group'          => 'الإدارة والسجلات',
                'icon'           => 'fa-bell',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'operations' => [
                'key'            => 'operations',
                'label'          => 'سجل العمليات',
                'subtitle'       => 'تعديل الإدخالات الخاطئة',
                'route'          => 'operations.index',
                'group'          => 'الإدارة والسجلات',
                'icon'           => 'fa-shield-halved',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'audit' => [
                'key'            => 'audit',
                'label'          => 'سجل التدقيق',
                'subtitle'       => 'كل تغيير في النظام',
                'route'          => 'audit.index',
                'group'          => 'الإدارة والسجلات',
                'icon'           => 'fa-clipboard-check',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'hr' => [
                'key'            => 'hr',
                'label'          => 'شؤون الموظفين (HR)',
                'subtitle'       => 'رواتب، سلف، تقفيل الشهر',
                'route'          => 'hr.index',
                'group'          => 'الإدارة والسجلات',
                'icon'           => 'fa-briefcase',
                'admin_only'     => false,
                'developer_only' => false,
            ],
            'partners' => [
                'key'            => 'partners',
                'label'          => 'حسابات الشركاء',
                'subtitle'       => 'أرباح شهرية، تقفيل، حصص',
                'route'          => 'partners.index',
                'group'          => 'الإدارة والسجلات',
                'icon'           => 'fa-handshake',
                'admin_only'     => true,
                'developer_only' => false,
            ],
            'settings' => [
                'key'            => 'settings',
                'label'          => 'الإعدادات العامة',
                'subtitle'       => 'صلاحيات، موردين، خصومات',
                'route'          => 'settings.index',
                'group'          => 'الإدارة والسجلات',
                'icon'           => 'fa-gears',
                'admin_only'     => true,
                'developer_only' => false,
            ],
            'cashflows' => [
                'key'            => 'cashflows',
                'label'          => 'التدفقات (الجمعيات)',
                'subtitle'       => 'الجمعيات والمداخل والمخارج',
                'route'          => 'cashflows.index',
                'group'          => 'أقسام المطور',
                'icon'           => 'fa-money-bill-transfer',
                'admin_only'     => false,
                'developer_only' => true,
            ],
            'contracting' => [
                'key'            => 'contracting',
                'label'          => 'المقاولات والتشطيبات',
                'subtitle'       => 'إدارة المشاريع',
                'route'          => 'contracting.index',
                'group'          => 'أقسام المطور',
                'icon'           => 'fa-hard-hat',
                'admin_only'     => false,
                'developer_only' => true,
            ],
        ];
    }

    public static function allKeys(): array
    {
        return array_keys(self::screens());
    }

    /**
     * Screens grouped for the Settings UI.
     */
    public static function groupedForSettings(bool $includeDeveloper = false): array
    {
        $grouped = [];
        foreach (self::screens() as $key => $meta) {
            if (!empty($meta['developer_only']) && !$includeDeveloper) {
                continue;
            }
            $grouped[$meta['group']][$key] = $meta;
        }
        return $grouped;
    }

    /**
     * Fetch the fresh user row (or fallback to session object).
     */
    private static function resolveFreshUser($user): ?object
    {
        if (!$user) {
            return null;
        }

        $id = (int) ($user->id ?? 0);
        if ($id <= 0) {
            return $user;
        }

        if (!app()->runningUnitTests() && isset(self::$userCache[$id])) {
            return self::$userCache[$id];
        }

        try {
            if (Schema::hasTable('users')) {
                $fresh = DB::table('users')->where('id', $id)->first();
                if ($fresh) {
                    self::$userCache[$id] = $fresh;
                    return $fresh;
                }
            }
        } catch (\Throwable $e) {
            // Fall back to provided object
        }

        return $user;
    }

    /**
     * Returns null when the user uses default role-based visibility/access,
     * or an array of screen keys when custom screen access is configured.
     */
    public static function getCustomAllowedScreens($user): ?array
    {
        $fresh = self::resolveFreshUser($user);
        if (!$fresh) {
            return null;
        }

        $raw = $fresh->allowed_screens ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_array($raw)) {
            $decoded = $raw;
        } else {
            $decoded = json_decode((string) $raw, true);
            if (!is_array($decoded)) {
                return null;
            }
        }

        $valid = self::allKeys();
        return array_values(array_intersect($decoded, $valid));
    }

    public static function hasExplicitScreenAccess($user, string $screenKey): bool
    {
        $custom = self::getCustomAllowedScreens($user);
        if ($custom === null) {
            return false;
        }
        return in_array($screenKey, $custom, true);
    }

    public static function hasExplicitAccessForRequest($user, Request $request): bool
    {
        $allowedForRoute = self::allowedScreensForRequest($request);
        if (empty($allowedForRoute)) {
            return false;
        }

        foreach ($allowedForRoute as $screenKey) {
            if (self::hasExplicitScreenAccess($user, $screenKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether $user can see & access the given screen key.
     */
    public static function canAccessScreen($user, string $screenKey): bool
    {
        $fresh = self::resolveFreshUser($user);
        if (!$fresh) {
            return false;
        }

        $screens = self::screens();
        if (!isset($screens[$screenKey])) {
            return false;
        }

        $def             = $screens[$screenKey];
        $role            = $fresh->role ?? 'employee';
        $isFinRestricted = (int) ($fresh->hide_financials ?? 0) === 1;
        $custom          = self::getCustomAllowedScreens($fresh);

        if ($custom !== null) {
            if (!empty($def['admin_only']) && $role !== 'admin') {
                return false;
            }
            return in_array($screenKey, $custom, true);
        }

        // Default (unconfigured) behavior: preserves legacy role rules exactly
        if ($role === 'viewer') {
            return $screenKey === 'reports';
        }

        if ($role === 'admin') {
            return true;
        }

        if (in_array($screenKey, ['settings', 'partners', 'hr', 'operations', 'audit', 'notifications'], true)) {
            return false;
        }

        if ($isFinRestricted && in_array($screenKey, ['dashboard', 'ac', 'contracting', 'reports', 'goals'], true)) {
            return false;
        }

        return true;
    }

    /**
     * All screen keys the user can access (ordered).
     */
    public static function allowedScreenKeysForUser($user, bool $excludeDeveloperUnlessDev = false): array
    {
        $fresh = self::resolveFreshUser($user);
        if (!$fresh) {
            return [];
        }

        $isDev = function_exists('isDeveloperUser') && isDeveloperUser($fresh);
        $allowed = [];

        foreach (self::screens() as $key => $meta) {
            if ($excludeDeveloperUnlessDev && !empty($meta['developer_only']) && !$isDev) {
                continue;
            }
            if (self::canAccessScreen($fresh, $key)) {
                $allowed[] = $key;
            }
        }

        return $allowed;
    }

    /**
     * Default entry route name for the user after login or when blocked from another screen.
     */
    public static function homeRouteForUser($user): ?string
    {
        $fresh = self::resolveFreshUser($user);
        if (!$fresh) {
            return 'login';
        }

        $custom  = self::getCustomAllowedScreens($fresh);
        $role    = $fresh->role ?? 'employee';
        $screens = self::screens();

        if ($custom === null) {
            if ($role === 'viewer') {
                return 'reports.index';
            }
            return 'treasury.index';
        }

        // Prefer non-developer screens first in the user's configured list (preserving user/catalog order)
        $isDev = function_exists('isDeveloperUser') && isDeveloperUser($fresh);
        $candidates = [];

        foreach ($custom as $key) {
            if (!isset($screens[$key])) continue;
            if (!self::canAccessScreen($fresh, $key)) continue;
            if (!empty($screens[$key]['developer_only']) && !$isDev) continue;
            $candidates[] = $screens[$key]['route'];
        }

        if (!empty($candidates)) {
            // If treasury or dashboard is among custom screens, or simply first allowed screen:
            // Keeping the first in catalog/selected order feels most intuitive when restricted to e.g. ['inquiries'].
            return $candidates[0];
        }

        // Fallback if only developer_only screens were assigned
        foreach ($custom as $key) {
            if (isset($screens[$key]) && self::canAccessScreen($fresh, $key)) {
                return $screens[$key]['route'];
            }
        }

        return null;
    }

    /**
     * Map an incoming HTTP Request to the screen key(s) that grant access to it.
     * Returns null if the route is public / personal / bypassable (e.g. logout, profile.updateName, root /).
     */
    public static function allowedScreensForRequest(Request $request): ?array
    {
        $routeName = $request->route()?->getName();
        $path      = ltrim($request->path(), '/');

        // Always allowed once authenticated
        if (
            $path === '' ||
            in_array($routeName, ['login', 'login.submit', 'logout', 'profile.updateName'], true) ||
            str_starts_with($path, 'portal') ||
            in_array($path, ['setup-users', 'seed-partners', 'up'], true)
        ) {
            return null;
        }

        // Cross-screen endpoints
        if ($routeName === 'customers.riskScore') {
            return ['customers', 'installments'];
        }
        if ($routeName === 'goals.progress_bar') {
            return ['goals', 'dashboard'];
        }
        if (in_array($routeName, ['settings.storeTechnician', 'settings.destroyTechnician'], true)) {
            return ['settings', 'ac'];
        }
        if (in_array($routeName, [
            'installments.pay',
            'installments.pay_bulk',
            'installments.pay_partial',
            'installments.action_modals',
            'installments.customer_statement',
        ], true)) {
            return ['installments', 'debts'];
        }
        if (in_array($routeName, ['financial.pay_installment', 'financial.pay_bulk_installments'], true)) {
            return ['installments', 'debts', 'treasury', 'financial_ops'];
        }
        if ($path === 'finance/reverse-payment') {
            return ['treasury', 'installments', 'financial_ops', 'debts'];
        }

        // Exact & prefix matching on route name
        if ($routeName) {
            $prefixMap = [
                'dashboard.'       => ['dashboard'],
                'treasury.'        => ['treasury'],
                'financial.'       => ['financial_ops'],
                'expenses.'        => ['expenses'],
                'goals.'           => ['goals'],
                'reports.'         => ['reports'],
                'sales.'           => ['sales'],
                'ac.'              => ['ac'],
                'manual_invoices.' => ['manual_invoices'],
                'inventory.'       => ['inventory'],
                'gas.'             => ['gas'],
                'installments.'    => ['installments'],
                'debts.'           => ['debts'],
                'company_debts.'   => ['company_debts'],
                'customers.'       => ['customers'],
                'inquiries.'       => ['inquiries'],
                'whatsapp.'        => ['whatsapp'],
                'assets.'          => ['assets'],
                'notifications.'   => ['notifications'],
                'operations.'      => ['operations'],
                'audit.'           => ['audit'],
                'hr.'              => ['hr'],
                'partners.'        => ['partners'],
                'settings.'        => ['settings'],
                'users.'           => ['settings'],
                'cashflows.'       => ['cashflows'],
                'contracting.'     => ['contracting'],
            ];

            foreach ($prefixMap as $prefix => $screens) {
                if (str_starts_with($routeName, $prefix)) {
                    return $screens;
                }
            }

            $exactMap = [
                'shift.close'     => ['treasury'],
                'archive.index'   => ['treasury'],
                'send.daily.report' => ['reports'],
                'db.backup'       => ['settings'],
                'dev.reset'       => ['settings'],
            ];

            if (isset($exactMap[$routeName])) {
                return $exactMap[$routeName];
            }
        }

        // Path-based fallback for unnamed routes (e.g. /settings/store-deduction)
        if (str_starts_with($path, 'settings')) {
            return ['settings'];
        }
        if (str_starts_with($path, 'users')) {
            return ['settings'];
        }
        if (str_starts_with($path, 'inquiries')) {
            return ['inquiries'];
        }
        if (str_starts_with($path, 'hr')) {
            return ['hr'];
        }
        if (str_starts_with($path, 'partners')) {
            return ['partners'];
        }

        return null;
    }

    public static function canAccessRequest($user, Request $request): bool
    {
        $allowedForRoute = self::allowedScreensForRequest($request);
        if ($allowedForRoute === null) {
            return true;
        }

        foreach ($allowedForRoute as $screenKey) {
            if (self::canAccessScreen($user, $screenKey)) {
                return true;
            }
        }

        return false;
    }
}
