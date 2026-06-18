<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // جدول مراجعات العمليات
        if (!Schema::hasTable('operation_revisions')) {
            Schema::create('operation_revisions', function (Blueprint $t) {
                $t->id();
                $t->string('source_type', 40)->index();    // fuel_transaction | sales | installments
                $t->unsignedBigInteger('source_id')->index();
                $t->string('action', 20);                   // edit | cancel
                $t->json('snapshot_before');                // الحالة قبل التعديل
                $t->unsignedBigInteger('new_source_id')->nullable(); // لما تعديل ينتج record جديد (بنزينة)
                $t->unsignedBigInteger('edited_by')->nullable();
                $t->string('edited_by_name', 120)->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }

        // ربط البيعة بأصناف المخزن المُباعة (للإرجاع التلقائي)
        Schema::table('installments', function (Blueprint $t) {
            if (!Schema::hasColumn('installments', 'inventory_items'))      $t->json('inventory_items')->nullable();
            if (!Schema::hasColumn('installments', 'cancelled_via_oplog_at')) $t->timestamp('cancelled_via_oplog_at')->nullable();
        });

        // ربط النسخة القديمة بالجديدة في عملية البنزينة (delete-recreate replaced by supersedes)
        Schema::table('fuel_transactions', function (Blueprint $t) {
            if (!Schema::hasColumn('fuel_transactions', 'supersedes'))     $t->unsignedBigInteger('supersedes')->nullable()->index();
            if (!Schema::hasColumn('fuel_transactions', 'superseded_by'))  $t->unsignedBigInteger('superseded_by')->nullable()->index();
            if (!Schema::hasColumn('fuel_transactions', 'superseded_at'))  $t->timestamp('superseded_at')->nullable();
        });

        // علامة "تم تعديله" للتوريد (sales)
        Schema::table('sales', function (Blueprint $t) {
            if (!Schema::hasColumn('sales', 'last_edited_at')) $t->timestamp('last_edited_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_revisions');

        Schema::table('installments', function (Blueprint $t) {
            if (Schema::hasColumn('installments', 'inventory_items'))         $t->dropColumn('inventory_items');
            if (Schema::hasColumn('installments', 'cancelled_via_oplog_at'))  $t->dropColumn('cancelled_via_oplog_at');
        });

        Schema::table('fuel_transactions', function (Blueprint $t) {
            foreach (['supersedes','superseded_by','superseded_at'] as $c) {
                if (Schema::hasColumn('fuel_transactions', $c)) $t->dropColumn($c);
            }
        });

        Schema::table('sales', function (Blueprint $t) {
            if (Schema::hasColumn('sales', 'last_edited_at')) $t->dropColumn('last_edited_at');
        });
    }
};
