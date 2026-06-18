<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * تسجيل الـ Audit Log بشكل موحد وذكي.
 * استخدامه في أي مكان:
 *   AuditService::log('delete', 'installments', "فسخ عقد العميل أحمد", $oldData);
 *   AuditService::critical('delete', 'installments', "حذف عقد", null, ['id' => 12]);
 */
class AuditService
{
    /**
     * يسجل عملية في الـ audit log.
     */
    public static function log(
        string $action,
        string $module,
        string $summary,
        $oldValues = null,
        $newValues = null,
        ?string $entityType = null,
        $entityId = null,
        string $severity = 'info'
    ): void {
        $user    = session('auth_user');
        $request = request();

        try {
            DB::table('audit_logs')->insert([
                'user_id'     => $user->id ?? null,
                'user_name'   => $user->name ?? 'النظام',
                'user_role'   => $user->role ?? null,
                'action'      => $action,
                'module'      => $module,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'summary'     => mb_substr($summary, 0, 500),
                'old_values'  => $oldValues ? json_encode(self::sanitize($oldValues), JSON_UNESCAPED_UNICODE) : null,
                'new_values'  => $newValues ? json_encode(self::sanitize($newValues), JSON_UNESCAPED_UNICODE) : null,
                'ip_address'  => $request ? $request->ip() : null,
                'user_agent'  => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
                'severity'    => $severity,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail-safe: لا نوقف العملية الأساسية لو فشل التسجيل
            logger()->error('AuditService failed: ' . $e->getMessage());
        }
    }

    public static function critical(string $action, string $module, string $summary, $old = null, $new = null, ?string $entity = null, $id = null): void
    {
        self::log($action, $module, $summary, $old, $new, $entity, $id, 'critical');
    }

    public static function warning(string $action, string $module, string $summary, $old = null, $new = null, ?string $entity = null, $id = null): void
    {
        self::log($action, $module, $summary, $old, $new, $entity, $id, 'warning');
    }

    /**
     * يحول object/Model لـ array قابل للـ json — مع إخفاء الحقول الحساسة.
     */
    private static function sanitize($values): array
    {
        if (is_object($values)) {
            $values = (array) $values;
        }
        if (!is_array($values)) {
            return ['value' => $values];
        }

        $hidden = ['password', 'remember_token', 'api_token'];
        foreach ($hidden as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '***';
            }
        }
        return $values;
    }
}
