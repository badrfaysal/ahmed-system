<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_inquiries') && Schema::hasColumn('customer_inquiries', 'customer_name')) {
            Schema::table('customer_inquiries', function (Blueprint $table) {
                $table->string('customer_name')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_inquiries') && Schema::hasColumn('customer_inquiries', 'customer_name')) {
            Schema::table('customer_inquiries', function (Blueprint $table) {
                $table->string('customer_name')->nullable(false)->change();
            });
        }
    }
};
