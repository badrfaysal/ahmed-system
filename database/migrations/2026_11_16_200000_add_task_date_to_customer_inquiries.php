<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_inquiries') && !Schema::hasColumn('customer_inquiries', 'task_date')) {
            Schema::table('customer_inquiries', function (Blueprint $table) {
                $table->date('task_date')->nullable()->after('inquiry');
                $table->index('task_date', 'idx_inq_task_date');
            });

            DB::table('customer_inquiries')
                ->whereNull('task_date')
                ->whereNotNull('created_at')
                ->update(['task_date' => DB::raw('DATE(created_at)')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_inquiries') && Schema::hasColumn('customer_inquiries', 'task_date')) {
            Schema::table('customer_inquiries', function (Blueprint $table) {
                $table->dropIndex('idx_inq_task_date');
                $table->dropColumn('task_date');
            });
        }
    }
};
