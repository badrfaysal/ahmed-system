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
        // 1. Projects & Clients table
        Schema::create('cont_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('client_name')->index();
            $table->string('client_phone')->nullable()->index();
            $table->string('location')->nullable()->index();
            $table->decimal('supervision_pct', 5, 2)->default(15.00);
            $table->string('contract_type', 50)->default('cost_plus');
            $table->decimal('total_contract_value', 12, 2)->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });

        // 2. Project Items (BoQ / Work Packages)
        Schema::create('cont_project_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cont_projects')->onDelete('cascade');
            $table->string('title')->index();
            $table->string('status', 30)->default('open')->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Directory (Suppliers, Technicians, Marketers, Clients)
        Schema::create('cont_directory', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index(); // supplier, technician, marketer, client
            $table->string('name')->index();
            $table->string('phone', 50)->nullable()->index();
            $table->string('specialty', 100)->nullable()->index();
            $table->decimal('balance_debt', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Operations & Expenditures
        Schema::create('cont_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cont_projects')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('cont_project_items')->onDelete('set null');
            $table->string('op_type', 30)->index(); // material, technician, overhead, commission
            $table->boolean('is_billable')->default(true)->index(); // 1 = billable to client, 0 = company overhead
            $table->string('name_or_title')->index();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 50)->default('عدد')->index();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('total_selling', 12, 2)->default(0);
            $table->decimal('profit_amount', 12, 2)->default(0);
            $table->unsignedBigInteger('person_id')->nullable()->index();
            $table->string('person_name')->nullable()->index();
            $table->string('payment_method', 50)->default('cash')->index();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining_debt', 12, 2)->default(0);
            $table->unsignedBigInteger('debt_id')->nullable()->index();
            $table->string('invoice_ref', 100)->nullable()->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });

        // 5. Discounts
        Schema::create('cont_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cont_projects')->onDelete('cascade');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });

        // 6. Quotations (المقايسات وعروض الأسعار)
        Schema::create('cont_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('client_name')->index();
            $table->string('client_phone', 50)->nullable()->index();
            $table->string('project_title')->index();
            $table->decimal('supervision_pct', 5, 2)->default(15.00);
            $table->longText('items_data');
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('total_selling', 12, 2)->default(0);
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedBigInteger('converted_project_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });

        // 7. Invoices & Milestones (الفواتير والمستخلصات)
        Schema::create('cont_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cont_projects')->onDelete('cascade');
            $table->string('invoice_number', 50)->unique();
            $table->string('invoice_type', 30)->default('detailed')->index();
            $table->boolean('is_tax')->default(false)->index();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->boolean('show_supervision_fee')->default(true);
            $table->decimal('supervision_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_billed', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2)->default(0);
            $table->unsignedBigInteger('installment_id')->nullable()->index();
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
        Schema::dropIfExists('cont_invoices');
        Schema::dropIfExists('cont_quotations');
        Schema::dropIfExists('cont_discounts');
        Schema::dropIfExists('cont_operations');
        Schema::dropIfExists('cont_directory');
        Schema::dropIfExists('cont_project_items');
        Schema::dropIfExists('cont_projects');
    }
};
