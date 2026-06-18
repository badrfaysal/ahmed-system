<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('installments', function (Blueprint $table) {
        $table->string('customer_address')->nullable()->after('customer_phone');
        $table->decimal('cost_price', 15, 2)->default(0)->after('product_name'); // التعديل هنا
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            //
        });
    }
};
