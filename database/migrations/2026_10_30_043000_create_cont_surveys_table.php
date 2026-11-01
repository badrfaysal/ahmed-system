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
        Schema::create('cont_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('مقايسة مساحات')->index();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->decimal('ceiling_height', 5, 2)->default(2.80);
            $table->decimal('total_room_walls', 10, 2)->default(0);
            $table->decimal('total_floors', 10, 2)->default(0);
            $table->decimal('total_ceramic_walls', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->longText('rooms_data')->nullable(); // JSON data of areas
            $table->longText('pricing_data')->nullable(); // JSON data of pricing (labor & material per m2)
            $table->decimal('total_labor_cost', 15, 2)->default(0);
            $table->decimal('total_material_cost', 15, 2)->default(0);
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cont_surveys');
    }
};
