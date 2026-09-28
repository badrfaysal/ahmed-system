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
        Schema::create('ac_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('ac_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ac_client_id')->constrained('ac_clients')->onDelete('cascade');
            $table->foreignId('ac_expense_category_id')->constrained('ac_expense_categories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('notes')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ac_expenses');
        Schema::dropIfExists('ac_expense_categories');
    }
};
