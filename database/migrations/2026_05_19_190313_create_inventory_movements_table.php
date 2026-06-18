<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id'); // رقم عملية الشراء الأصلية
            $table->string('product_name'); // اسم الصنف
            $table->decimal('quantity_sold', 10, 2); // الكمية المباعة
            $table->decimal('selling_price', 10, 2); // سعر البيع للوحدة
            $table->decimal('total_revenue', 10, 2); // إجمالي الفاتورة
            $table->unsignedBigInteger('deposit_account_id')->nullable(); // حساب الإيداع (ممكن يكون فارغ لو قسط كله)
            $table->timestamps(); // بينشئ created_at و updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_movements');
    }
};