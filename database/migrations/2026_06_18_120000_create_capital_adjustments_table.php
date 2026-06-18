<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capital_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');           // الخزنة المتأثرة
            $table->string('type', 20);                          // 'increase' | 'decrease'
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('reason')->nullable();                  // السبب
            $table->string('status', 20)->default('active');     // 'active' | 'cancelled'
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capital_adjustments');
    }
};
