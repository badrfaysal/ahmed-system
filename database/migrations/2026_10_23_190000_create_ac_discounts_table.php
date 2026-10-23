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
        Schema::create('ac_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ac_client_id')->constrained('ac_clients')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ac_discounts');
    }
};
