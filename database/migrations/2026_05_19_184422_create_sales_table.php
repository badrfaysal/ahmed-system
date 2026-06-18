<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('product_name'); // اسم المنتج
            $table->string('supplier_name')->nullable(); // اسم المورد
            $table->decimal('purchase_price', 10, 2)->default(0); // سعر الشراء
            $table->decimal('selling_price', 10, 2)->default(0); // سعر البيع
            $table->decimal('quantity', 10, 2); // الكمية الأساسية
            $table->decimal('remaining_quantity', 10, 2)->default(0); // الكمية المتبقية (للمخزن)
            $table->string('inventory_status')->default('direct_sale'); // حالة البضاعة (مباشر أو مخزن)
            $table->timestamps(); // بينشئ created_at و updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};