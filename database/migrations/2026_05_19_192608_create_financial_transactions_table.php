<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // نوع العملية: expense (صرف), settlement (تسوية), transfer (تحويل)
            $table->decimal('amount', 15, 2); // المبلغ
            $table->unsignedBigInteger('from_account_id')->nullable(); // اتسحب منين
            $table->unsignedBigInteger('to_account_id')->nullable(); // راح فين
            $table->string('notes')->nullable(); // السبب (عهدة مندوب، تسوية محل، الخ)
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('financial_transactions');
    }
};