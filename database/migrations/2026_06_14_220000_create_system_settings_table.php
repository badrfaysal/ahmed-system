<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $t) {
                $t->id();
                $t->string('key', 100)->unique();
                $t->text('value')->nullable();
                $t->string('type', 20)->default('string');
                $t->string('group', 50)->default('general');
                $t->string('label', 200);
                $t->text('description')->nullable();
                $t->boolean('user_added')->default(false);
                $t->timestamps();
            });
        }

        $now = now();
        $defaults = [
            ['key' => 'fuel_profit_rate',        'value' => '0.01',  'type' => 'number',  'label' => 'نسبة ربح البنزينة',           'description' => 'نسبة عشرية، 0.01 = 1%'],
            ['key' => 'default_interest_rate',   'value' => '0',     'type' => 'number',  'label' => 'معدل الفائدة الافتراضي للأقساط (%)','description' => 'يُقترح عند إنشاء عقد جديد'],
            ['key' => 'max_installment_months',  'value' => '36',    'type' => 'number',  'label' => 'أقصى عدد شهور للتقسيط',        'description' => 'الحد الأعلى المسموح به'],
            ['key' => 'min_down_payment_pct',    'value' => '0',     'type' => 'number',  'label' => 'أقل نسبة مقدم (%)',           'description' => 'مثلاً 10 = المقدم لازم 10% على الأقل'],
            ['key' => 'invoice_header_text',     'value' => 'بسم الله الرحمن الرحيم', 'type' => 'text', 'label' => 'رأس الفاتورة',  'description' => 'يظهر أعلى كل فاتورة مطبوعة'],
            ['key' => 'invoice_footer_text',     'value' => 'شكراً لتعاملكم معنا',    'type' => 'text', 'label' => 'تذييل الفاتورة','description' => 'يظهر أسفل كل فاتورة مطبوعة'],
            ['key' => 'low_balance_threshold',   'value' => '500',   'type' => 'number',  'label' => 'حد التنبيه للرصيد المنخفض (ج)','description' => 'لما رصيد أي خزنة يقل عنه يظهر إشعار'],
            ['key' => 'theme_color',             'value' => 'navy',  'type' => 'string',  'label' => 'لون النظام',                  'description' => 'يتغير لون السيستم كله بناءً عليه'],
        ];

        foreach ($defaults as $row) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, [
                    'group'      => 'general',
                    'user_added' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
