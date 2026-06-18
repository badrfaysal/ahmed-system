<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_inquiries')) {
            Schema::create('customer_inquiries', function (Blueprint $table) {
                $table->id();
                $table->string('customer_name');
                $table->string('customer_phone')->nullable();
                $table->text('inquiry');                  // الطلب / السؤال
                $table->string('product_type')->nullable(); // نوع المنتج (موبايل، تكييف، جهاز، ...)
                $table->boolean('is_contacted')->default(false);
                $table->text('contact_notes')->nullable(); // ملاحظات بعد التواصل
                $table->timestamp('contacted_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable(); // الموظف اللي سجل
                $table->string('created_by_name')->nullable();        // اسم الموظف (للأمان)
                $table->unsignedBigInteger('contacted_by')->nullable(); // الموظف اللي تواصل
                $table->string('contacted_by_name')->nullable();
                $table->timestamps();

                $table->index('is_contacted');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_inquiries');
    }
};
