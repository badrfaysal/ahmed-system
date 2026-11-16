<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// تحويل شاشة استفسارات العملاء إلى شاشة "المهام" (المهمة + المكلّف بها)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_inquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_inquiries', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('product_type');
            }
            if (!Schema::hasColumn('customer_inquiries', 'assigned_to_name')) {
                $table->string('assigned_to_name')->nullable()->after('assigned_to');
            }
            if (!Schema::hasColumn('customer_inquiries', 'assigned_phone')) {
                $table->string('assigned_phone', 30)->nullable()->after('assigned_to_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_inquiries', function (Blueprint $table) {
            $table->dropColumn(['assigned_to', 'assigned_to_name', 'assigned_phone']);
        });
    }
};
