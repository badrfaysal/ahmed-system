<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryAndOperationsReturnTest extends TestCase
{
    protected object $adminUser;
    protected int $safeAccountId;
    protected int $expenseAccountId;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'audit_logs', 'activity_logs', 'operation_revisions',
            'installment_expenses', 'installment_payments', 'sale_returns',
            'inventory_movements', 'financial_transactions', 'company_debts',
            'installments', 'sales', 'suppliers', 'customers', 'assets',
            'ac_operations', 'customer_inquiries', 'fuel_transactions',
            'transport_companies', 'gas_stations', 'cont_supplier_credit_logs',
            'cont_operations', 'cont_project_items', 'cont_projects',
            'cont_directory', 'messages', 'accounts', 'users',
        ] as $tbl) {
            Schema::dropIfExists($tbl);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('admin');
            $table->boolean('is_active')->default(true);
            $table->boolean('hide_financials')->default(false);
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('category')->default('safe_cash');
            $table->string('status')->default('active');
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('current_value', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id')->default(1);
            $table->text('product_name')->nullable();
            $table->string('category')->nullable();
            $table->string('supplier_name')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('remaining_quantity', 10, 2)->default(0);
            $table->string('inventory_status')->default('to_inventory');
            $table->tinyInteger('is_return')->default(0);
            $table->date('purchase_date')->nullable();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->text('product_name')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('product_category')->nullable();
            $table->string('sale_type')->default('inventory');
            $table->string('category')->nullable();
            $table->date('start_date')->nullable();
            $table->decimal('cash_price', 15, 2)->nullable();
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->integer('discount')->nullable()->default(0);
            $table->decimal('down_payment', 15, 2)->nullable()->default(0);
            $table->decimal('remaining_after_down', 15, 2)->nullable()->default(0);
            $table->integer('installment_months')->nullable()->default(0);
            $table->decimal('interest_rate', 8, 2)->nullable()->default(0);
            $table->decimal('total_after_interest', 15, 2)->nullable()->default(0);
            $table->decimal('monthly_installment', 15, 2)->nullable()->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->integer('due_day')->nullable()->default(1);
            $table->decimal('remaining_balance', 15, 2)->nullable()->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->longText('inventory_items')->nullable();
            $table->timestamp('cancelled_via_oplog_at')->nullable();
            $table->timestamps();
        });

        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installment_id');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('discount_applied', 15, 2)->default(0);
            $table->dateTime('payment_date')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('installment_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installment_id');
            $table->decimal('transport_cost', 15, 2)->default(0);
            $table->decimal('installation_cost', 15, 2)->default(0);
            $table->decimal('materials_cost', 15, 2)->nullable()->default(0);
            $table->timestamps();
        });

        Schema::create('company_debts', function (Blueprint $table) {
            $table->id();
            $table->string('creditor_name');
            $table->text('reason')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->string('category')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('subtype')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->unsignedBigInteger('to_account_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->string('person_name')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('ref_type')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->text('product_name')->nullable();
            $table->string('category')->nullable();
            $table->decimal('quantity_returned', 10, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('return_price', 15, 2)->default(0);
            $table->decimal('total_refunded', 15, 2)->default(0);
            $table->decimal('loss_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('refund_account_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->string('type');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->integer('from_store')->nullable();
            $table->integer('to_store')->nullable();
            $table->string('notes')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('operation_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('action');
            $table->longText('snapshot_before');
            $table->unsignedBigInteger('new_source_id')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->string('edited_by_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action');
            $table->string('module');
            $table->text('description')->nullable();
            $table->text('extra_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_read')->default(0);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('module')->nullable();
            $table->string('action')->nullable();
            $table->string('severity')->nullable();
            $table->text('summary')->nullable();
            $table->string('route_name')->nullable();
            $table->string('method')->nullable();
            $table->text('url')->nullable();
            $table->string('ip')->nullable();
            $table->string('target_table')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->longText('before_data')->nullable();
            $table->longText('after_data')->nullable();
            $table->longText('request_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('ac_operations', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('customer_inquiries', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_contacted')->default(0);
            $table->timestamps();
        });

        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('station_id')->nullable();
            $table->decimal('liters', 10, 2)->default(0);
            $table->decimal('cash_advance', 15, 2)->default(0);
            $table->string('fuel_type')->nullable();
            $table->decimal('market_price_at_time', 10, 2)->default(0);
            $table->decimal('discount_price_at_time', 10, 2)->default(0);
            $table->decimal('total_on_company', 15, 2)->default(0);
            $table->decimal('total_to_station', 15, 2)->default(0);
            $table->decimal('ahmed_profit', 15, 2)->default(0);
            $table->string('driver_name')->nullable();
            $table->string('car_number')->nullable();
            $table->decimal('odometer', 15, 2)->nullable();
            $table->boolean('is_full_tank')->default(1);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('supersedes')->nullable();
            $table->unsignedBigInteger('superseded_by')->nullable();
            $table->timestamp('superseded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->timestamps();
        });

        Schema::create('gas_stations', function (Blueprint $table) {
            $table->id();
            $table->string('station_name');
            $table->timestamps();
        });

        Schema::create('cont_directory', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('supplier');
            $table->string('name');
            $table->decimal('balance_debt', 15, 2)->default(0);
            $table->decimal('balance_credit', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('cont_supplier_credit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('person_name');
            $table->string('tx_type')->default('overpayment');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('cont_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_name')->nullable();
            $table->timestamps();
        });

        Schema::create('cont_project_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('cont_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('op_type')->nullable();
            $table->string('name_or_title')->nullable();
            $table->string('person_name')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('total_selling', 15, 2)->default(0);
            $table->decimal('supervision_pct', 8, 2)->default(0);
            $table->boolean('is_billable')->default(0);
            $table->string('invoice_ref')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $adminId = DB::table('users')->insertGetId([
            'name'      => 'مدير النظام',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'admin',
            'is_active' => 1,
        ]);
        $this->adminUser = DB::table('users')->where('id', $adminId)->first();

        $this->safeAccountId = DB::table('accounts')->insertGetId([
            'account_name' => 'الخزنة الرئيسية',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 10000,
        ]);

        $this->expenseAccountId = DB::table('accounts')->insertGetId([
            'account_name' => 'خزنة المصروفات',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 5000,
        ]);
    }

    protected function actingAsAdmin()
    {
        return $this->withSession([
            'auth_user'       => $this->adminUser,
            'auth_last_check' => time(),
        ]);
    }

    public function test_supplier_return_deducts_from_supplier_debt_first_without_touching_treasury(): void
    {
        $saleId = DB::table('sales')->insertGetId([
            'store_id'           => 1,
            'product_name'       => 'ثلاجة توشيبا',
            'category'           => 'ثلاجات',
            'supplier_name'      => 'شركة النصر للأجهزة',
            'purchase_price'     => 50,
            'selling_price'      => 70,
            'quantity'           => 2,
            'remaining_quantity' => 2,
            'inventory_status'   => 'to_inventory',
            'created_at'         => now(),
        ]);

        // استخدام صيغة عربية قريبة للتأكد من التطبيع (شركه / للاجهزه)
        $debtId = DB::table('company_debts')->insertGetId([
            'creditor_name'     => 'شركه النصر للاجهزه',
            'reason'            => 'مشتريات بضاعة',
            'total_amount'      => 100,
            'paid_amount'       => 0,
            'remaining_balance' => 100,
            'category'          => 'مورد',
            'created_at'        => now(),
        ]);

        $res = $this->actingAsAdmin()->post('/inventory/return-supplier', [
            'sale_id'           => $saleId,
            'quantity_returned' => 1,
            'refund_price'      => 50,
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('success');

        $debt = DB::table('company_debts')->where('id', $debtId)->first();
        $this->assertEquals(50.0, (float) $debt->total_amount);
        $this->assertEquals(0.0, (float) $debt->paid_amount);
        $this->assertEquals(50.0, (float) $debt->remaining_balance);

        // الخزنة والمستحقات لم تتأثر
        $this->assertEquals(10000.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $this->assertEquals(0, DB::table('installments')->count());
        $this->assertEquals(1.0, (float) DB::table('sales')->where('id', $saleId)->value('remaining_quantity'));
    }

    public function test_supplier_return_with_zero_debt_requires_treasury_and_deposits_cash(): void
    {
        $saleId = DB::table('sales')->insertGetId([
            'store_id'           => 1,
            'product_name'       => 'غسالة LG',
            'category'           => 'غسالات',
            'supplier_name'      => 'مورد كاش',
            'purchase_price'     => 50,
            'selling_price'      => 80,
            'quantity'           => 2,
            'remaining_quantity' => 2,
            'inventory_status'   => 'to_inventory',
            'created_at'         => now(),
        ]);

        // بدون اختيار خزنة -> يرفض
        $fail = $this->actingAsAdmin()->post('/inventory/return-supplier', [
            'sale_id'           => $saleId,
            'quantity_returned' => 1,
            'refund_price'      => 50,
        ]);
        $fail->assertRedirect();
        $fail->assertSessionHas('error');
        $this->assertEquals(2.0, (float) DB::table('sales')->where('id', $saleId)->value('remaining_quantity'));

        // باختيار خزنة -> ينزل المبلغ فيها
        $ok = $this->actingAsAdmin()->post('/inventory/return-supplier', [
            'sale_id'           => $saleId,
            'quantity_returned' => 1,
            'refund_price'      => 50,
            'refund_account'    => $this->safeAccountId,
        ]);
        $ok->assertRedirect();
        $ok->assertSessionHas('success');

        $this->assertEquals(10050.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $this->assertEquals(1.0, (float) DB::table('sales')->where('id', $saleId)->value('remaining_quantity'));
        $this->assertEquals(0, DB::table('installments')->count());

        $ft = DB::table('financial_transactions')->where('type', 'income')->first();
        $this->assertNotNull($ft);
        $this->assertEquals(50.0, (float) $ft->amount);
        $this->assertEquals($this->safeAccountId, (int) $ft->to_account_id);
    }

    public function test_supplier_return_splits_between_open_debt_and_treasury_excess(): void
    {
        $saleId = DB::table('sales')->insertGetId([
            'store_id'           => 1,
            'product_name'       => 'شاشة سامسونج',
            'category'           => 'شاشات وتلفزيونات',
            'supplier_name'      => 'المتحدة',
            'purchase_price'     => 50,
            'selling_price'      => 75,
            'quantity'           => 2,
            'remaining_quantity' => 2,
            'inventory_status'   => 'to_inventory',
            'created_at'         => now(),
        ]);

        // فاتورة بـ 100 اتدفع منها 70 وباقي 30 دين
        $debtId = DB::table('company_debts')->insertGetId([
            'creditor_name'     => 'المتحدة',
            'reason'            => 'فاتورة شاشات',
            'total_amount'      => 100,
            'paid_amount'       => 70,
            'remaining_balance' => 30,
            'category'          => 'مورد',
            'created_at'        => now(),
        ]);

        // مرتجع بـ 50 -> يصفّر 30 من الدين، والفائض 20 ينزل الخزنة
        $res = $this->actingAsAdmin()->post('/inventory/return-supplier', [
            'sale_id'           => $saleId,
            'quantity_returned' => 1,
            'refund_price'      => 50,
            'refund_account'    => $this->safeAccountId,
        ]);
        $res->assertRedirect();
        $res->assertSessionHas('success');

        $debt = DB::table('company_debts')->where('id', $debtId)->first();
        $this->assertEquals(70.0, (float) $debt->total_amount);
        $this->assertEquals(70.0, (float) $debt->paid_amount);
        $this->assertEquals(0.0, (float) $debt->remaining_balance);

        $this->assertEquals(10020.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
    }

    public function test_operations_log_partial_return_then_remaining_full_return_cash_sale(): void
    {
        $batchId = DB::table('sales')->insertGetId([
            'store_id'           => 1,
            'product_name'       => 'تكييف شارب 1.5',
            'category'           => 'تكييفات',
            'supplier_name'      => 'العربي',
            'purchase_price'     => 800,
            'selling_price'      => 1000,
            'quantity'           => 10,
            'remaining_quantity' => 5,
            'inventory_status'   => 'to_inventory',
            'created_at'         => now(),
        ]);

        // بيع 5 قطع كاش بـ 5000 (الخزنة فيها 10000 فعلاً بعد البيع)
        $now = now();
        $instId = DB::table('installments')->insertGetId([
            'customer_name'        => 'محمد علي',
            'product_name'         => 'فاتورة مبيعات: تكييف شارب 1.5 (x5)',
            'sale_type'            => 'inventory',
            'category'             => 'مبيعات مخزن',
            'purchase_cost'        => 4000,
            'quantity'             => 1,
            'cash_price'           => 5000,
            'discount'             => 0,
            'down_payment'         => 5000,
            'remaining_after_down' => 0,
            'installment_months'   => 0,
            'total_after_interest' => 5000,
            'monthly_installment'  => 0,
            'remaining_balance'    => 0,
            'profit'               => 1000,
            'status'               => 'paid',
            'inventory_items'      => json_encode([
                ['sale_id' => $batchId, 'qty' => 5, 'product_name' => 'تكييف شارب 1.5', 'selling_price' => 1000],
            ], JSON_UNESCAPED_UNICODE),
            'created_at'           => $now,
        ]);

        $incomeFtId = DB::table('financial_transactions')->insertGetId([
            'type'          => 'income',
            'amount'        => 5000,
            'to_account_id' => $this->safeAccountId,
            'notes'         => 'بيع نقدي',
            'status'        => 'active',
            'created_at'    => $now,
        ]);

        // 1) عرض بيانات المودال
        $showRes = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}/show", [
            'admin_pin' => 'secret123',
        ]);
        $showRes->assertOk();
        $showRes->assertJsonPath('items.0.qty', 5);
        $showRes->assertJsonPath('items.0.unit_net_price', 1000);

        // 2) إرجاع 3 قطع من 5
        $partRes = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}", [
            'admin_pin'         => 'secret123',
            'refund_account_id' => $this->safeAccountId,
            'return_qtys'       => [0 => 3],
        ]);
        $partRes->assertOk()->assertJson(['success' => true]);

        // الخزنة نقصت 3000 مرة واحدة فقط (10000 -> 7000) والإيراد الأصلي محفوظ
        $this->assertEquals(7000.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $this->assertNotNull(DB::table('financial_transactions')->where('id', $incomeFtId)->first());
        $this->assertEquals(3000.0, (float) DB::table('financial_transactions')->where('type', 'expense')->value('amount'));

        // المخزن زاد 3 (5 -> 8)
        $this->assertEquals(8.0, (float) DB::table('sales')->where('id', $batchId)->value('remaining_quantity'));

        // الفاتورة لسه مفتوحة بـ 2 قطعة
        $inst = DB::table('installments')->where('id', $instId)->first();
        $this->assertNotNull($inst);
        $this->assertEquals(2000.0, (float) $inst->total_after_interest);
        $this->assertEquals(1600.0, (float) $inst->purchase_cost);
        $this->assertEquals(400.0, (float) $inst->profit);
        $this->assertEquals(2000.0, (float) $inst->down_payment);
        $remJson = json_decode($inst->inventory_items, true);
        $this->assertEquals(2, $remJson[0]['qty']);

        // لا يمكن حذف العملية بالكامل بعد المرتجع الجزئي
        $delFail = $this->actingAsAdmin()->post("/operations-log/sale_inventory/{$instId}", [
            'admin_pin' => 'secret123',
        ]);
        $delFail->assertRedirect();
        $delFail->assertSessionHas('error');

        // 3) إرجاع الـ 2 قطعة المتبقية -> يكتمل المرتجع وتحذف الفاتورة
        $fullRes = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}", [
            'admin_pin'         => 'secret123',
            'refund_account_id' => $this->safeAccountId,
            'return_qtys'       => [0 => 2],
        ]);
        $fullRes->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(5000.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $this->assertEquals(10.0, (float) DB::table('sales')->where('id', $batchId)->value('remaining_quantity'));
        $this->assertNull(DB::table('installments')->where('id', $instId)->first());
    }

    public function test_operations_log_partial_return_uses_net_price_after_discount_and_preserves_extras(): void
    {
        $batchId = DB::table('sales')->insertGetId([
            'store_id'           => 1,
            'product_name'       => 'تكييف كاريير',
            'category'           => 'تكييفات',
            'supplier_name'      => 'ميراكو',
            'purchase_price'     => 800,
            'selling_price'      => 1000,
            'quantity'           => 10,
            'remaining_quantity' => 5,
            'inventory_status'   => 'to_inventory',
            'created_at'         => now(),
        ]);

        // 5 قطع × 1000 = 5000 + نقل 500 - خصم 500 => الصافي 5000 (القطعة صافي = 900)
        $now = now();
        $instId = DB::table('installments')->insertGetId([
            'customer_name'        => 'أحمد حسن',
            'product_name'         => 'فاتورة مبيعات: تكييف كاريير (x5)',
            'sale_type'            => 'inventory',
            'category'             => 'مبيعات مخزن',
            'purchase_cost'        => 4000,
            'quantity'             => 1,
            'cash_price'           => 5500,
            'discount'             => 500,
            'down_payment'         => 5000,
            'remaining_after_down' => 0,
            'installment_months'   => 0,
            'total_after_interest' => 5000,
            'monthly_installment'  => 0,
            'remaining_balance'    => 0,
            'profit'               => 500, // (5000 - 500 نقل) - 4000
            'status'               => 'paid',
            'inventory_items'      => json_encode([
                ['sale_id' => $batchId, 'qty' => 5, 'product_name' => 'تكييف كاريير', 'selling_price' => 1000],
            ], JSON_UNESCAPED_UNICODE),
            'created_at'           => $now,
        ]);

        DB::table('installment_expenses')->insert([
            'installment_id'    => $instId,
            'transport_cost'    => 500,
            'installation_cost' => 0,
            'materials_cost'    => 0,
            'created_at'        => $now,
        ]);

        $discFtId = DB::table('financial_transactions')->insertGetId([
            'type'       => 'discount',
            'amount'     => 500,
            'notes'      => 'خصم فاتورة مبيعات',
            'status'     => 'active',
            'created_at' => $now,
        ]);

        $showRes = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}/show", [
            'admin_pin' => 'secret123',
        ]);
        $showRes->assertOk();
        $showRes->assertJsonPath('items.0.unit_net_price', 900);

        // إرجاع 3 قطع => صافي المرتجع = 2700 (3 × 900)
        $partRes = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}", [
            'admin_pin'         => 'secret123',
            'refund_account_id' => $this->safeAccountId,
            'return_qtys'       => [0 => 3],
        ]);
        $partRes->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(7300.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $inst = DB::table('installments')->where('id', $instId)->first();
        $this->assertEquals(2500.0, (float) $inst->cash_price);          // 2000 + 500 نقل
        $this->assertEquals(200, (int) $inst->discount);                 // 500 × (2/5)
        $this->assertEquals(2300.0, (float) $inst->total_after_interest); // 2500 - 200
        $this->assertEquals(1600.0, (float) $inst->purchase_cost);
        $this->assertEquals(200.0, (float) $inst->profit);               // (2300 - 500) - 1600
        $this->assertEquals(2300.0, (float) $inst->down_payment);
        $this->assertEquals(200.0, (float) DB::table('financial_transactions')->where('id', $discFtId)->value('amount'));
        $this->assertNotNull(DB::table('installment_expenses')->where('installment_id', $instId)->first());
    }

    public function test_operations_log_partial_return_deducts_customer_debt_first_then_refunds_excess(): void
    {
        $batchId = DB::table('sales')->insertGetId([
            'store_id'           => 1,
            'product_name'       => 'بوتاجاز',
            'category'           => 'عام',
            'supplier_name'      => 'يونيون',
            'purchase_price'     => 800,
            'selling_price'      => 1000,
            'quantity'           => 10,
            'remaining_quantity' => 5,
            'inventory_status'   => 'to_inventory',
            'created_at'         => now(),
        ]);

        // فاتورة بـ 5000: دفع 2000 مقدم وباقي عليه 3000
        $instId = DB::table('installments')->insertGetId([
            'customer_name'        => 'سيد محمود',
            'product_name'         => 'فاتورة مبيعات: بوتاجاز (x5)',
            'sale_type'            => 'inventory',
            'category'             => 'مبيعات مخزن',
            'purchase_cost'        => 4000,
            'quantity'             => 1,
            'cash_price'           => 5000,
            'discount'             => 0,
            'down_payment'         => 2000,
            'remaining_after_down' => 3000,
            'installment_months'   => 0,
            'total_after_interest' => 5000,
            'monthly_installment'  => 3000,
            'remaining_balance'    => 3000,
            'profit'               => 1000,
            'status'               => 'active',
            'inventory_items'      => json_encode([
                ['sale_id' => $batchId, 'qty' => 5, 'product_name' => 'بوتاجاز', 'selling_price' => 1000],
            ], JSON_UNESCAPED_UNICODE),
            'created_at'           => now(),
        ]);

        // 1) رجع 2 قطعة (2000) -> تتخصم من الدين (3000 -> 1000) بدون خزنة
        $r1 = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}", [
            'admin_pin'   => 'secret123',
            'return_qtys' => [0 => 2],
        ]);
        $r1->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(10000.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $inst = DB::table('installments')->where('id', $instId)->first();
        $this->assertEquals(1000.0, (float) $inst->remaining_balance);
        $this->assertEquals(2000.0, (float) $inst->down_payment);
        $this->assertEquals('active', $inst->status);

        // 2) رجع 2 قطعة كمان (2000) -> تقفل باقي الدين (1000 -> 0) وترد 1000 كاش من الخزنة
        $r2 = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}", [
            'admin_pin'         => 'secret123',
            'refund_account_id' => $this->safeAccountId,
            'return_qtys'       => [0 => 2],
        ]);
        $r2->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(9000.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $inst = DB::table('installments')->where('id', $instId)->first();
        $this->assertEquals(0.0, (float) $inst->remaining_balance);
        $this->assertEquals(1000.0, (float) $inst->down_payment);
        $this->assertEquals('paid', $inst->status);
    }

    public function test_operations_log_full_return_does_not_double_decrement_treasury(): void
    {
        $batchId = DB::table('sales')->insertGetId([
            'store_id'           => 1,
            'product_name'       => 'ديب فريزر',
            'category'           => 'ثلاجات',
            'supplier_name'      => 'توشيبا',
            'purchase_price'     => 2000,
            'selling_price'      => 2500,
            'quantity'           => 5,
            'remaining_quantity' => 3,
            'inventory_status'   => 'to_inventory',
            'created_at'         => now(),
        ]);

        $now = now();
        $instId = DB::table('installments')->insertGetId([
            'customer_name'        => 'خالد',
            'product_name'         => 'فاتورة مبيعات: ديب فريزر (x2)',
            'sale_type'            => 'inventory',
            'category'             => 'مبيعات مخزن',
            'purchase_cost'        => 4000,
            'quantity'             => 1,
            'cash_price'           => 5000,
            'discount'             => 0,
            'down_payment'         => 5000,
            'remaining_after_down' => 0,
            'installment_months'   => 0,
            'total_after_interest' => 5000,
            'monthly_installment'  => 0,
            'remaining_balance'    => 0,
            'profit'               => 1000,
            'status'               => 'paid',
            'inventory_items'      => json_encode([
                ['sale_id' => $batchId, 'qty' => 2, 'product_name' => 'ديب فريزر', 'selling_price' => 2500],
            ], JSON_UNESCAPED_UNICODE),
            'created_at'           => $now,
        ]);

        DB::table('financial_transactions')->insert([
            'type'          => 'income',
            'amount'        => 5000,
            'to_account_id' => $this->safeAccountId,
            'notes'         => 'بيع نقدي',
            'status'        => 'active',
            'created_at'    => $now,
        ]);

        // الرصيد كان 10000 -> بعد رد 5000 لازم يبقى 5000 بالضبط (مش 0!)
        $res = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}", [
            'admin_pin'         => 'secret123',
            'refund_account_id' => $this->safeAccountId,
            'return_qtys'       => [0 => 2],
        ]);
        $res->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(5000.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $this->assertEquals(5.0, (float) DB::table('sales')->where('id', $batchId)->value('remaining_quantity'));
        $this->assertNull(DB::table('installments')->where('id', $instId)->first());
    }

    public function test_operations_log_multi_item_partial_return_only_returns_selected_item(): void
    {
        $batchA = DB::table('sales')->insertGetId([
            'store_id' => 1, 'product_name' => 'تكييف A', 'category' => 'تكييفات',
            'supplier_name' => 'العربي', 'purchase_price' => 800, 'selling_price' => 1000,
            'quantity' => 10, 'remaining_quantity' => 6, 'inventory_status' => 'to_inventory',
            'created_at' => now(),
        ]);
        $batchB = DB::table('sales')->insertGetId([
            'store_id' => 1, 'product_name' => 'شاشة B', 'category' => 'شاشات وتلفزيونات',
            'supplier_name' => 'العربي', 'purchase_price' => 1500, 'selling_price' => 2000,
            'quantity' => 10, 'remaining_quantity' => 8, 'inventory_status' => 'to_inventory',
            'created_at' => now(),
        ]);

        // 4×1000 + 2×2000 = 8000 كاش
        $instId = DB::table('installments')->insertGetId([
            'customer_name'        => 'كريم',
            'product_name'         => 'فاتورة مبيعات: تكييف A (x4) + شاشة B (x2)',
            'sale_type'            => 'inventory',
            'category'             => 'مبيعات مخزن',
            'purchase_cost'        => 6200,
            'quantity'             => 1,
            'cash_price'           => 8000,
            'discount'             => 0,
            'down_payment'         => 8000,
            'remaining_after_down' => 0,
            'installment_months'   => 0,
            'total_after_interest' => 8000,
            'monthly_installment'  => 0,
            'remaining_balance'    => 0,
            'profit'               => 1800,
            'status'               => 'paid',
            'inventory_items'      => json_encode([
                ['sale_id' => $batchA, 'qty' => 4, 'product_name' => 'تكييف A', 'selling_price' => 1000],
                ['sale_id' => $batchB, 'qty' => 2, 'product_name' => 'شاشة B', 'selling_price' => 2000],
            ], JSON_UNESCAPED_UNICODE),
            'created_at'           => now(),
        ]);

        // إرجاع 2 من تكييف A فقط، و0 من شاشة B
        $res = $this->actingAsAdmin()->postJson("/operations-log/sale_return/{$instId}", [
            'admin_pin'         => 'secret123',
            'refund_account_id' => $this->safeAccountId,
            'return_qtys'       => [0 => 2, 1 => 0],
        ]);
        $res->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(8000.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));
        $this->assertEquals(8.0, (float) DB::table('sales')->where('id', $batchA)->value('remaining_quantity'));
        $this->assertEquals(8.0, (float) DB::table('sales')->where('id', $batchB)->value('remaining_quantity'));

        $inst = DB::table('installments')->where('id', $instId)->first();
        $this->assertEquals('فاتورة مبيعات: تكييف A (x2) + شاشة B (x2)', $inst->product_name);
        $this->assertEquals(6000.0, (float) $inst->total_after_interest);
        $this->assertEquals(4600.0, (float) $inst->purchase_cost);
        $this->assertEquals(1400.0, (float) $inst->profit);
        $this->assertEquals(1, DB::table('sale_returns')->count());
        $this->assertEquals($batchA, (int) DB::table('sale_returns')->value('sale_id'));
    }

    public function test_sell_modal_groups_same_store_batches_and_sells_fifo_across_batches(): void
    {
        $b1 = DB::table('sales')->insertGetId([
            'store_id' => 1, 'product_name' => 'بوتوجاز', 'category' => 'غسالات',
            'supplier_name' => 'مورد 1', 'purchase_price' => 80, 'selling_price' => 150,
            'quantity' => 2, 'remaining_quantity' => 1, 'inventory_status' => 'to_inventory',
            'created_at' => now()->subMinutes(10),
        ]);
        $b2 = DB::table('sales')->insertGetId([
            'store_id' => 1, 'product_name' => 'بوتوجاز', 'category' => 'غسالات',
            'supplier_name' => 'مورد 1', 'purchase_price' => 90, 'selling_price' => 150,
            'quantity' => 1, 'remaining_quantity' => 1, 'inventory_status' => 'to_inventory',
            'created_at' => now()->subMinutes(5),
        ]);
        // دفعة في المخزن الفرعي تفضل منفصلة بمخزنها
        DB::table('sales')->insertGetId([
            'store_id' => 2, 'product_name' => 'بوتوجاز', 'category' => 'غسالات',
            'supplier_name' => 'مورد 1', 'purchase_price' => 100, 'selling_price' => 150,
            'quantity' => 1, 'remaining_quantity' => 1, 'inventory_status' => 'to_inventory',
            'created_at' => now(),
        ]);

        // فحص بيانات التجميع المرسلة لصفحة المخزن
        $invRes = $this->actingAsAdmin()->get('/inventory');
        $invRes->assertOk();
        $grouped = $invRes->viewData('sellGroupedItems');
        $this->assertCount(2, $grouped);
        $store1Group = collect($grouped)->firstWhere('store_id', 1);
        $this->assertEquals(2.0, $store1Group['qty']);
        $this->assertEquals(2, $store1Group['batches_count']);
        $this->assertEquals($b1, $store1Group['id']);

        // لو scope=batch على الدفعة الأولى فقط وطلب 2 -> يرفض
        $batchFail = $this->actingAsAdmin()->post('/inventory/sell', [
            'payment_type'     => 'cash',
            'deposit_account'  => $this->safeAccountId,
            'sale_id'          => [$b1],
            'sell_scope'       => ['batch'],
            'sell_quantity'    => [2],
            'selling_price'    => [150],
            'customer_name'    => 'عميل 1',
        ]);
        $batchFail->assertRedirect();
        $batchFail->assertSessionHas('error');

        // لو scope=group -> يسحب 1 من b1 و 1 من b2 تلقائياً بنظام FIFO
        $groupOk = $this->actingAsAdmin()->post('/inventory/sell', [
            'payment_type'     => 'cash',
            'deposit_account'  => $this->safeAccountId,
            'sale_id'          => [$b1],
            'sell_scope'       => ['group'],
            'sell_quantity'    => [2],
            'selling_price'    => [150],
            'customer_name'    => 'عميل 1',
        ]);
        $groupOk->assertRedirect();
        $groupOk->assertSessionHas('success');

        $this->assertEquals(0.0, (float) DB::table('sales')->where('id', $b1)->value('remaining_quantity'));
        $this->assertEquals('sold_from_inventory', DB::table('sales')->where('id', $b1)->value('inventory_status'));
        $this->assertEquals(0.0, (float) DB::table('sales')->where('id', $b2)->value('remaining_quantity'));
        $this->assertEquals('sold_from_inventory', DB::table('sales')->where('id', $b2)->value('inventory_status'));

        $inst = DB::table('installments')->latest('id')->first();
        $this->assertEquals('فاتورة مبيعات: بوتوجاز (x2)', $inst->product_name);
        $this->assertEquals(170.0, (float) $inst->purchase_cost); // 80 + 90
        $this->assertEquals(130.0, (float) $inst->profit);        // 300 - 170
        $this->assertEquals(10300.0, (float) DB::table('accounts')->where('id', $this->safeAccountId)->value('balance'));

        $itemsJson = json_decode($inst->inventory_items, true);
        $this->assertCount(2, $itemsJson);
        $this->assertEquals($b1, $itemsJson[0]['sale_id']);
        $this->assertEquals(1, $itemsJson[0]['qty']);
        $this->assertEquals($b2, $itemsJson[1]['sale_id']);
        $this->assertEquals(1, $itemsJson[1]['qty']);
    }

    public function test_inventory_and_operations_log_views_render_cleanly(): void
    {
        $invRes = $this->actingAsAdmin()->get('/inventory');
        $invRes->assertOk();
        $invRes->assertSee('supplierReturnModal');
        $invRes->assertSee('الدين المفتوح للمورد');

        $opRes = $this->actingAsAdmin()->get('/operations-log');
        $opRes->assertOk();
        $opRes->assertSee('returnSaleModal');
        $opRes->assertSee('تحديد الكل (مرتجع كامل)');
    }
}
