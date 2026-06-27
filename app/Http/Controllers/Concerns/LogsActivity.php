<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait LogsActivity
{
    protected function logActivity(
        string $action,
        string $module,
        string $description,
        array  $extraData = []
    ): void {
        try {
            $user = session('auth_user');

            DB::table('activity_logs')->insert([
                'user_id'     => $user?->id    ?? 0,
                'user_name'   => $user?->name  ?? 'النظام',
                'user_role'   => $user?->role  ?? 'system',
                'action'      => $action,
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
                if ($action == 'create') $icon = '✅';
                elseif ($action == 'update') $icon = '✏️';
                elseif ($action == 'delete') $icon = '🗑️';
                elseif ($action == 'login') $icon = '🔐';
                elseif ($action == 'logout') $icon = '🚪';
                elseif ($action == 'cancel') $icon = '❌';
                elseif ($action == 'payment') $icon = '💰';

                $userName = $user?->name ?? 'النظام الآلي';
                $msg = "{$icon} <b>إشعار نظام جديد</b>\n";
                $msg .= "👤 <b>بواسطة:</b> {$userName}\n";
                $msg .= "📝 <b>البيان:</b> {$description}\n";
                $msg .= "🕒 <b>الوقت:</b> " . now()->format('h:i A');

                $url = "https://api.telegram.org/bot{$token}/sendMessage";
                Http::timeout(2)->post($url, [
                    'chat_id'    => $chatId,
                    'text'       => $msg,
                    'parse_mode' => 'HTML',
                ]);
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
