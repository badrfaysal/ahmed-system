<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ac_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('ac_floors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('ac_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('ac_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ac_client_id');
            $table->unsignedBigInteger('ac_floor_id')->nullable();
            $table->unsignedBigInteger('ac_class_id')->nullable();
            $table->enum('type', ['sale', 'maintenance']); // sale = بيع وتركيب, maintenance = صيانة
            $table->string('maintenance_type_name')->nullable(); // In case of manual maintenance without stock items
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('cost_amount', 15, 2)->default(0);
            $table->decimal('profit_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->date('date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('ac_operation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ac_operation_id');
            $table->unsignedBigInteger('item_id'); // refers to inventory table (id)
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->decimal('cost_price', 15, 2);
            $table->decimal('profit', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ac_operation_items');
        Schema::dropIfExists('ac_operations');
        Schema::dropIfExists('ac_classes');
        Schema::dropIfExists('ac_floors');
        Schema::dropIfExists('ac_clients');
    }
};
