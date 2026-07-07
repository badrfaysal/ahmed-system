<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('system_settings')) return;

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'low_balance_alert_enabled'],
            [
                'value'      => '1',
                'type'       => 'boolean',
                'group'      => 'general',
                'label'      => 'تفعيل تنبيه الرصيد المنخفض',
                'description'=> 'إظهار إشعار لما رصيد أي خزنة يقل عن الحد المحدد',
                'user_added' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('system_settings')->where('key', 'low_balance_alert_enabled')->delete();
    }
};
