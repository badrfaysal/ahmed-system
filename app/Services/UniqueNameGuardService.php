<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * الحارس الموحّد لمنع تكرار الأسماء بالضبط عبر كل شاشات النظام
 * (محطات البنزين، شركات النقل، الموردين، دليل المقاولات، التكييف، العملاء، الموظفين، الشركاء، الاستقطاعات، الخزائن)
 */
class UniqueNameGuardService
{
    /**
     * أسماء افتراضية عامة لا تُعامل كأسماء جهات خاصة
     */
    private const IGNORED_GENERIC_NAMES = [
        'عام',
        'معرض عام',
        'صنايعي عام',
        'نقدي',
        'عميل نقدي',
        'عميل عام',
    ];

    /**
     * تطبيع الاسم للمقارنة الدقيقة: إزالة المسافات الزائدة وتوحيد حالة الأحرف
     */
    public static function normalize(?string $name): string
    {
        $trimmed = trim((string) $name);
        if ($trimmed === '') {
            return '';
        }
        $collapsed = preg_replace('/\s+/u', ' ', $trimmed);
        return mb_strtolower($collapsed ?? $trimmed, 'UTF-8');
    }

    /**
     * هل الاسمان متطابقان بالضبط بعد إزالة المسافات الزائدة؟
     */
    public static function isSameName(?string $a, ?string $b): bool
    {
        return self::normalize($a) === self::normalize($b);
    }

    /**
     * سجل الشاشات / الجداول المراقَبة في النظام
     */
    public static function registry(): array
    {
        return [
            'gas_station' => [
                'table' => 'gas_stations',
                'col'   => 'station_name',
                'label' => 'محطات البنزين',
                'hint'  => 'محطة بنزين',
            ],
            'transport_company' => [
                'table' => 'transport_companies',
                'col'   => 'company_name',
                'label' => 'شركات النقل',
                'hint'  => 'شركة نقل',
            ],
            'supplier' => [
                'table' => 'suppliers',
                'col'   => 'name',
                'label' => 'موردين المخزون',
                'hint'  => 'مورد مخزون',
            ],
            'cont_supplier' => [
                'table' => 'cont_directory',
                'col'   => 'name',
                'where' => ['type' => 'supplier'],
                'label' => 'موردين المقاولات',
                'hint'  => 'مورد مقاولات',
            ],
            'cont_technician' => [
                'table' => 'cont_directory',
                'col'   => 'name',
                'where' => ['type' => 'technician'],
                'label' => 'صنايعية المقاولات',
                'hint'  => 'صنايعي مقاولات',
            ],
            'cont_marketer' => [
                'table' => 'cont_directory',
                'col'   => 'name',
                'where' => ['type' => 'marketer'],
                'label' => 'مسوقين المقاولات',
                'hint'  => 'مسوق مقاولات',
            ],
            'cont_client' => [
                'table' => 'cont_directory',
                'col'   => 'name',
                'where' => ['type' => 'client'],
                'label' => 'عملاء المقاولات',
                'hint'  => 'عميل مقاولات',
            ],
            'ac_technician' => [
                'table' => 'ac_technicians',
                'col'   => 'name',
                'label' => 'فنيين التكييف',
                'hint'  => 'فني تكييف',
            ],
            'ac_client' => [
                'table' => 'ac_clients',
                'col'   => 'name',
                'label' => 'عملاء التكييف',
                'hint'  => 'عميل تكييف',
            ],
            'customer' => [
                'table' => 'customers',
                'col'   => 'name',
                'label' => 'أرشيف العملاء',
                'hint'  => 'عميل',
            ],
            'employee' => [
                'table' => 'employees',
                'col'   => 'name',
                'where' => ['status' => 'active'],
                'label' => 'الموظفين',
                'hint'  => 'موظف',
            ],
            'partner' => [
                'table' => 'partners',
                'col'   => 'name',
                'label' => 'الشركاء',
                'hint'  => 'شريك',
            ],
            'fuel_deduction' => [
                'table' => 'fuel_deductions',
                'col'   => 'name',
                'label' => 'استقطاعات البنزينة',
                'hint'  => 'استقطاع',
            ],
            'account' => [
                'table' => 'accounts',
                'col'   => 'account_name',
                'label' => 'الخزائن والمحافظ',
                'hint'  => 'خزنة/محفظة',
            ],
        ];
    }

    /**
     * التحقق من وجود تعارض اسم متطابق بالضبط عبر كل شاشات النظام.
     *
     * @param string          $name                       الاسم المُدخل
     * @param string|null     $currentScope               مفتاح الشاشة الحالية (من registry أو 'manual_debt')
     * @param int|string|null $excludeId                  معرّف السجل الحالي عند التعديل لتجاهل نفسه
     * @param string|null     $excludeNameInCurrentScope  الاسم القديم عند التعديل بالاسم (مثل أرشيف العملاء)
     * @param bool            $allowExistingInCurrentScope true للعمليات/الفواتير التي تستخدم جهة مسجلة مسبقاً في نفس الشاشة
     * @return array|null     null إذا لم يوجد تعارض، أو مصفوفة بها ['name', 'matches', 'message'] عند وجود تعارض
     */
    public static function checkConflict(
        ?string $name,
        ?string $currentScope = null,
        int|string|null $excludeId = null,
        ?string $excludeNameInCurrentScope = null,
        bool $allowExistingInCurrentScope = false
    ): ?array {
        $cleanName = preg_replace('/\s+/u', ' ', trim((string) $name)) ?? trim((string) $name);
        $norm = self::normalize($cleanName);

        if ($norm === '' || in_array($norm, self::IGNORED_GENERIC_NAMES, true)) {
            return null;
        }

        $registry = self::registry();

        // 1) في شاشات تسجيل العمليات (فاتورة خامات، توريد مخزن، مشروع جديد لعميل حالي):
        // لو الاسم مسجل مسبقاً في نفس الشاشة فهو تعامل طبيعي مع جهة قائمة — يُسمح به فوراً.
        if ($allowExistingInCurrentScope && $currentScope !== null) {
            if (isset($registry[$currentScope]) && self::existsInScope($norm, $registry[$currentScope])) {
                return null;
            }
            // بالنسبة لموردين المخزون: قد يكون المورد استُخدم سابقاً في المخزن أو الديون بتصنيف "مورد" دون إضافته في جدول suppliers
            if ($currentScope === 'supplier' && self::existsAsInventorySupplier($norm)) {
                return null;
            }
        }

        $matchedLabels = [];
        $excludeNorm = $excludeNameInCurrentScope !== null ? self::normalize($excludeNameInCurrentScope) : null;

        // 2) فحص كل الجداول المسجلة في النظام
        foreach ($registry as $scopeKey => $cfg) {
            if (!Schema::hasTable($cfg['table'])) {
                continue;
            }

            // تجاهل نفس السجل عند التعديل في نفس الشاشة
            $skipId = ($scopeKey === $currentScope && $excludeId !== null && $excludeId !== '') ? $excludeId : null;
            $skipName = ($scopeKey === $currentScope && $excludeNorm !== null && $excludeNorm !== '') ? $excludeNorm : null;

            if (self::existsInScope($norm, $cfg, $skipId, $skipName)) {
                $matchedLabels[] = $cfg['label'];
            }
        }

        // 3) فحص أسماء عملاء الأقساط أيضاً (قد يكون العميل في installments فقط ولم يُضف في customers)
        if (Schema::hasTable('installments')) {
            $skipInstallmentCheck = ($currentScope === 'customer' && $excludeNorm !== null && $excludeNorm === $norm)
                || ($allowExistingInCurrentScope && $currentScope === 'customer');
            if (!$skipInstallmentCheck) {
                $instQuery = DB::table('installments')
                    ->whereNotNull('customer_name')
                    ->where('customer_name', '!=', '');
                if ($currentScope === 'financial_expense' && Schema::hasColumn('installments', 'sale_type')) {
                    $instQuery->where(function ($q) {
                        $q->whereNull('sale_type')->orWhere('sale_type', '!=', 'financial');
                    });
                }
                $instMatch = false;
                foreach ($instQuery->distinct()->pluck('customer_name') as $val) {
                    if (self::normalize($val) === $norm) {
                        $instMatch = true;
                        break;
                    }
                }
                if ($instMatch && !in_array('أرشيف العملاء', $matchedLabels, true)) {
                    $matchedLabels[] = 'عملاء الأقساط';
                }
            }
        }

        $matchedLabels = array_values(array_unique($matchedLabels));
        if (empty($matchedLabels)) {
            return null;
        }

        $placesStr = implode('، ', $matchedLabels);
        $extraHints = [
            'manual_debt'          => 'دين يدوي',
            'financial_expense'    => 'مستلم عهدة',
            'financial_settlement' => 'مودع/دائن',
            'financial_op'         => 'حركة مالية',
        ];
        $hint = $registry[$currentScope]['hint'] ?? ($extraHints[$currentScope] ?? 'اسم مميز');
        $message = "⚠️ تحذير: الاسم «{$cleanName}» مسجل بالفعل في ({$placesStr})! لا يمكن تكرار نفس الاسم بالضبط لمنع تداخل الحسابات — يرجى إضافة وصف مميز (مثل: {$cleanName} - {$hint}).";

        return [
            'name'    => $cleanName,
            'matches' => $matchedLabels,
            'message' => $message,
        ];
    }

    /**
     * اختصار يرجّع نص رسالة التحذير فقط أو null لو الاسم سليم
     */
    public static function conflictMessage(
        ?string $name,
        ?string $currentScope = null,
        int|string|null $excludeId = null,
        ?string $excludeNameInCurrentScope = null,
        bool $allowExistingInCurrentScope = false
    ): ?string {
        $conflict = self::checkConflict($name, $currentScope, $excludeId, $excludeNameInCurrentScope, $allowExistingInCurrentScope);
        return $conflict['message'] ?? null;
    }

    private static function existsInScope(
        string $normTarget,
        array $cfg,
        int|string|null $excludeId = null,
        ?string $excludeNormName = null
    ): bool {
        if (!Schema::hasTable($cfg['table'])) {
            return false;
        }

        $q = DB::table($cfg['table'])->select(['id', $cfg['col']]);
        if (!empty($cfg['where'])) {
            foreach ($cfg['where'] as $wc => $wv) {
                $q->where($wc, $wv);
            }
        }
        if ($excludeId !== null) {
            $q->where('id', '!=', $excludeId);
        }

        $rows = $q->get();
        foreach ($rows as $row) {
            $val = $row->{$cfg['col']} ?? '';
            $rowNorm = self::normalize($val);
            if ($rowNorm === '') {
                continue;
            }
            if ($excludeNormName !== null && $rowNorm === $excludeNormName) {
                continue;
            }
            if ($rowNorm === $normTarget) {
                return true;
            }
        }

        return false;
    }

    private static function existsExactNormalized(string $table, string $col, string $normTarget): bool
    {
        $vals = DB::table($table)
            ->whereNotNull($col)
            ->where($col, '!=', '')
            ->distinct()
            ->pluck($col);

        foreach ($vals as $val) {
            if (self::normalize($val) === $normTarget) {
                return true;
            }
        }
        return false;
    }

    private static function existsAsInventorySupplier(string $normTarget): bool
    {
        if (Schema::hasTable('company_debts')) {
            $vals = DB::table('company_debts')
                ->where('category', 'مورد')
                ->whereNotNull('creditor_name')
                ->distinct()
                ->pluck('creditor_name');
            foreach ($vals as $v) {
                if (self::normalize($v) === $normTarget) {
                    return true;
                }
            }
        }
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'supplier_name')) {
            $vals = DB::table('sales')
                ->whereNotNull('supplier_name')
                ->where('supplier_name', '!=', '')
                ->distinct()
                ->pluck('supplier_name');
            foreach ($vals as $v) {
                if (self::normalize($v) === $normTarget) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * فصل مورد الأسمنت في المقاولات «رمضان» إلى «رمضان (اسمنت)» عن محطة بنزين «رمضان» تلقائياً
     * (تعمل مرة واحدة بشكل آمن ومتكرر Idempotent — عبر الـ Migration أو تلقائياً عند أول فتح لشاشة الديون/المقاولات بعد الرفع للسيرفر).
     */
    public static function ensureRamadanSupplierSeparated(bool $force = false): bool
    {
        $cacheKey = 'ramadan_supplier_separated_v1';
        if (!$force && Cache::get($cacheKey)) {
            return false;
        }

        if (!Schema::hasTable('gas_stations') || !Schema::hasTable('cont_directory') || !Schema::hasTable('company_debts')) {
            return false;
        }

        $oldName = 'رمضان';
        $newName = 'رمضان (اسمنت)';

        // نتأكد إن محطة البنزين «رمضان» موجودة فعلاً عشان ما نعدلش حاجة لو مش موجودة
        $hasGasStation = DB::table('gas_stations')->where('station_name', $oldName)->exists();
        if (!$hasGasStation) {
            Cache::forever($cacheKey, true);
            return false;
        }

        $dirRow = DB::table('cont_directory')
            ->where('type', 'supplier')
            ->where('name', $oldName)
            ->first();

        $hasContDebts = DB::table('company_debts')
            ->where('creditor_name', $oldName)
            ->where(function ($q) {
                $q->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
                  ->orWhere('category', 'مقاولات')
                  ->orWhere('reason', 'like', '%مشروع:%')
                  ->orWhere('reason', 'like', '%فاتورة خامات%');
            })
            ->exists();

        $hasContOps = Schema::hasTable('cont_operations')
            && DB::table('cont_operations')
                ->where('op_type', 'material')
                ->where('person_name', $oldName)
                ->exists();

        if (!$dirRow && !$hasContDebts && !$hasContOps) {
            Cache::forever($cacheKey, true);
            return false;
        }

        DB::transaction(function () use ($oldName, $newName, $dirRow) {
            $existingTarget = DB::table('cont_directory')
                ->where('type', 'supplier')
                ->where('name', $newName)
                ->first();

            $targetDirId = $existingTarget?->id ?? $dirRow?->id;

            // 1) تحديث فواتير/عمليات المقاولات
            if (Schema::hasTable('cont_operations')) {
                $opQuery = DB::table('cont_operations')->where(function ($q) use ($oldName, $dirRow) {
                    $q->where(function ($sub) use ($oldName) {
                        $sub->where('op_type', 'material')->where('person_name', $oldName);
                    });
                    if ($dirRow) {
                        $q->orWhere('person_id', $dirRow->id);
                    }
                });
                $upd = ['person_name' => $newName];
                if ($targetDirId) {
                    $upd['person_id'] = $targetDirId;
                }
                $opQuery->update($upd);
            }

            // 2) تحديث سجل دفعات المورد في المقاولات
            if (Schema::hasTable('cont_worker_payments')) {
                $wpQuery = DB::table('cont_worker_payments')->where(function ($q) use ($oldName, $dirRow) {
                    $q->where(function ($sub) use ($oldName) {
                        $sub->where('person_type', 'supplier')->where('person_name', $oldName);
                    });
                    if ($dirRow) {
                        $q->orWhere('person_id', $dirRow->id);
                    }
                });
                $upd = ['person_name' => $newName];
                if ($targetDirId) {
                    $upd['person_id'] = $targetDirId;
                }
                $wpQuery->update($upd);
            }

            // 3) تحديث مرتجعات الخامات وسجل الرصيد الدائن إن وجدا
            if (Schema::hasTable('cont_material_returns')) {
                DB::table('cont_material_returns')
                    ->where('supplier_name', $oldName)
                    ->update(['supplier_name' => $newName]);
            }
            if (Schema::hasTable('cont_supplier_credit_logs')) {
                DB::table('cont_supplier_credit_logs')
                    ->where('person_type', 'supplier')
                    ->where('person_name', $oldName)
                    ->update(['person_name' => $newName]);
            }

            // 4) تحديث ديون المقاولات فقط في company_debts (بدون المساس بديون الوقود إطلاقاً)
            DB::table('company_debts')
                ->where('creditor_name', $oldName)
                ->where(function ($q) {
                    $q->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
                      ->orWhere('category', 'مقاولات')
                      ->orWhere('reason', 'like', '%مشروع:%')
                      ->orWhere('reason', 'like', '%فاتورة خامات%');
                })
                ->update(['creditor_name' => $newName]);

            // 5) إعادة حساب دين المقاولات الفعلي وتحديث/دمج سجل cont_directory
            $liveContDebt = (float) DB::table('company_debts')
                ->where('creditor_name', $newName)
                ->where(function ($q) {
                    $q->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
                      ->orWhere('category', 'مقاولات')
                      ->orWhere('reason', 'like', '%مشروع:%')
                      ->orWhere('reason', 'like', '%فاتورة خامات%');
                })
                ->sum('remaining_balance');

            if ($dirRow && $existingTarget && $dirRow->id !== $existingTarget->id) {
                DB::table('cont_directory')->where('id', $existingTarget->id)->update([
                    'balance_debt' => max(0, $liveContDebt),
                    'updated_at'   => now(),
                ]);
                DB::table('cont_directory')->where('id', $dirRow->id)->delete();
            } elseif ($dirRow) {
                DB::table('cont_directory')->where('id', $dirRow->id)->update([
                    'name'         => $newName,
                    'balance_debt' => max(0, $liveContDebt),
                    'updated_at'   => now(),
                ]);
            }
        });

        Cache::forever($cacheKey, true);
        return true;
    }
}
