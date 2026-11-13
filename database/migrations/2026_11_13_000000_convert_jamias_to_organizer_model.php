<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تحويل منظومة الجمعيات إلى منظور "المنظم":
 * الشركة هي اللي بتلم الجمعية من الأعضاء (تحصيل شهري من يوم 1 لـ 10)
 * وبتصرف القبض لصاحب الدور كل شهر. ممكن يكون للشركة أسهم داخل الجمعية (شركة الضبع).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jamias', function (Blueprint $table) {
            if (!Schema::hasColumn('jamias', 'collection_start_day')) {
                $table->unsignedTinyInteger('collection_start_day')->default(1)->after('due_day'); // بداية فترة التحصيل
            }
            if (!Schema::hasColumn('jamias', 'collection_end_day')) {
                $table->unsignedTinyInteger('collection_end_day')->default(10)->after('collection_start_day'); // آخر يوم للتحصيل (بحد أقصى 10)
            }
        });

        Schema::table('jamia_shares', function (Blueprint $table) {
            if (!Schema::hasColumn('jamia_shares', 'member_phone')) {
                $table->string('member_phone', 50)->nullable()->after('share_label'); // هاتف العضو
            }
            if (!Schema::hasColumn('jamia_shares', 'is_company')) {
                $table->boolean('is_company')->default(false)->after('member_phone'); // سهم مملوك للشركة (شركة الضبع)
                $table->index('is_company');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jamia_shares', function (Blueprint $table) {
            if (Schema::hasColumn('jamia_shares', 'is_company')) {
                $table->dropIndex(['is_company']);
                $table->dropColumn('is_company');
            }
            if (Schema::hasColumn('jamia_shares', 'member_phone')) {
                $table->dropColumn('member_phone');
            }
        });

        Schema::table('jamias', function (Blueprint $table) {
            foreach (['collection_end_day', 'collection_start_day'] as $col) {
                if (Schema::hasColumn('jamias', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
