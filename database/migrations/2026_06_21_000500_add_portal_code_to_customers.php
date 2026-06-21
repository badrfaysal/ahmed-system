<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة عمود portal_code للعملاء — يستخدم لدخول العميل لبوابة الاستعلام عن أقساطه.
     * كود قصير (8 حروف/أرقام) فريد لكل عميل، يتم توليده يدوياً من شاشة أرشيف العملاء.
     */
    public function up(): void
    {
        if (!Schema::hasTable('customers')) return;

        if (!Schema::hasColumn('customers', 'portal_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('portal_code', 16)->nullable()->after('phone');
                $table->index('portal_code', 'idx_customers_portal_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'portal_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('idx_customers_portal_code');
                $table->dropColumn('portal_code');
            });
        }
    }
};
