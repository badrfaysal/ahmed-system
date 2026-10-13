<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('installment_todos', function (Blueprint $table) {
            $table->id();
            $table->date('todo_date')->index();
            $table->unsignedInteger('due_day')->nullable()->index();
            $table->unsignedBigInteger('installment_id')->nullable();
            $table->text('installment_ids')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable()->index();
            $table->text('product_name')->nullable();
            $table->decimal('monthly_installment', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2)->default(0);
            $table->unsignedInteger('contracts_count')->default(1);
            $table->boolean('is_collected')->default(false)->index();
            $table->timestamp('collected_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['todo_date', 'is_collected']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_todos');
    }
};
