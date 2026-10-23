<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (!Schema::hasColumn('partners', 'is_head')) {
                // رئيس مجلس الإدارة: رأس ماله = المتبقي من رأس مال الشركة بعد حصص الشركاء، ويحصّل أتعاب الإدارة
                $table->boolean('is_head')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'is_head')) {
                $table->dropColumn('is_head');
            }
        });
    }
};
