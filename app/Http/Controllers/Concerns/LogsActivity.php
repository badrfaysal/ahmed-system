<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait LogsActivity
{
    private const ALLOWED_ACTIVITY_ACTIONS = [
        'create', 'update', 'delete', 'login', 'logout', 'view', 'cancel', 'payment', 'transfer',
    ];

    private const ACTION_ENUM_MAP = [
        'collect'         => 'payment',
        'payout'          => 'payment',
        'recover'         => 'payment',
        'pay'             => 'payment',
        'pay_bulk'        => 'payment',
        'cancel_payout'   => 'cancel',
        'cancel_recovery' => 'cancel',
        'undo_transfer'   => 'cancel',
        'reverse'         => 'cancel',
        'split_share'     => 'update',
        'unsplit_share'   => 'update',
    ];

    protected function logActivity(
        string $action,
        string $module,
        string $description,
        array  $extraData = []
    ): void {
        try {
            $user = session('auth_user');
            $dbAction = in_array($action, self::ALLOWED_ACTIVITY_ACTIONS, true)
                ? $action
                : (self::ACTION_ENUM_MAP[$action] ?? 'update');

            DB::table('activity_logs')->insert([
                'user_id'     => $user?->id    ?? 0,
                'user_name'   => $user?->name  ?? 'النظام',
                'user_role'   => $user?->role  ?? 'system',
                'action'      => $dbAction,
                'module'      => $module,
                'description' => $description,
                'extra_data'  => !empty($extraData) ? json_encode($extraData, JSON_UNESCAPED_UNICODE) : null,
                'ip_address'  => request()->ip(),
                'user_agent'  => substr(request()->userAgent() ?? '', 0, 200),
                'is_read'     => 0,
                'created_at'  => now(),
            ]);

            $token  = '8838781113:AAFzKHPB_jU3L8dzeJ8rgclW4MdmDmogMOE';
            $chatId = '-1004438939705';

            if ($token && $chatId) {
                $icon = '🔹';
                if ($dbAction === 'create') $icon = '✅';
                elseif ($dbAction === 'update') $icon = '✏️';
                elseif ($dbAction === 'delete') $icon = '🗑️';
                elseif ($dbAction === 'login') $icon = '🔐';
                elseif ($dbAction === 'logout') $icon = '🚪';
                elseif ($dbAction === 'cancel') $icon = '❌';
                elseif ($dbAction === 'payment') $icon = '💰';

                $userName = $user?->name ?? 'النظام الآلي';
                $msg = "{$icon} <b>إشعار نظام جديد</b>\n";
                $msg .= "👤 <b>بواسطة:</b> {$userName}\n";
                $msg .= "📝 <b>البيان:</b> {$description}\n";
                $msg .= "🕒 <b>الوقت:</b> " . now()->format('h:i A');

                $url = "https://api.telegram.org/bot{$token}/sendMessage";
                $payload = [
                    'chat_id'    => $chatId,
                    'text'       => $msg,
                    'parse_mode' => 'HTML',
                ];

                // إرسال إشعار تيليجرام بعد إنهاء الرد/المعاملة حتى لا يؤخر استجابة الشاشة أو يحجز قفل قاعدة البيانات
                app()->terminating(function () use ($url, $payload) {
                    try {
                        Http::connectTimeout(1)->timeout(2)->post($url, $payload);
                    } catch (\Throwable $e) {
                        Log::warning('Telegram activity notification failed: ' . $e->getMessage());
                    }
                });
            }
        } catch (\Exception $e) {
            Log::error('ActivityLog failed: ' . $e->getMessage());
        }
    }

    public static function getUnreadCount(): int
    {
        try {
            return (int) DB::table('activity_logs')->where('is_read', 0)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
