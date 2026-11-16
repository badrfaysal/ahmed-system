<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContractingExpensesAndUnitsTest extends TestCase
{
    protected int $accountId;
    protected int $projectId;
    protected object $devUser;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        $tables = [
            'cont_units', 'cont_petty_titles', 'cont_expense_titles',
            'cont_material_returns', 'cont_invoices', 'cont_quotations', 'cont_discounts',
            'cont_operations', 'cont_directory', 'cont_project_items', 'cont_projects',
            'installment_payments', 'installment_expenses', 'installments', 'company_debts',
            'financial_transactions', 'accounts', 'capital_adjustments', 'system_settings',
            'activity_logs', 'audit_logs', 'sales', 'assets', 'fuel_transactions',
            'sy2_installment_contracts', 'suppliers', 'customer_inquiries', 'users',
        ];
        foreach ($tables as $t) {
            Schema::dropIfExists($t);
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
            $table->boolean('has_commission')->default(false);
            $table->decimal('commission_pct', 8, 4)->default(0);
            $table->decimal('max_commission', 12, 2)->default(0);
            $table->decimal('min_commission', 10, 2)->default(0);
            $table->decimal('flat_fee_below', 12, 2)->default(0);
            $table->decimal('flat_fee_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->nullable();
            $table->string('type');
            $table->string('subtype', 50)->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('total_before', 15, 2)->nullable();
            $table->decimal('total_after', 15, 2)->nullable();
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->unsignedBigInteger('to_account_id')->nullable();
            $table->string('person_name')->nullable();
            $table->string('ref_type')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
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
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('product_name')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('product_category')->nullable();
            $table->string('sale_type')->nullable();
            $table->string('category')->nullable();
            $table->date('start_date')->nullable();
            $table->decimal('cash_price', 15, 2)->default(0);
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('discount', 15, 2)->nullable()->default(0);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('remaining_after_down', 15, 2)->default(0);
            $table->integer('installment_months')->default(0);
            $table->decimal('interest_rate', 8, 2)->nullable()->default(0);
            $table->decimal('total_after_interest', 15, 2)->default(0);
            $table->decimal('monthly_installment', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->integer('due_day')->default(1);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->string('close_reason', 32)->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
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
            $table->decimal('materials_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('capital_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
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
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_status')->nullable();
            $table->decimal('remaining_quantity', 10, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('active');
            $table->decimal('current_value', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('superseded_by')->nullable();
            $table->decimal('ahmed_profit', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sy2_installment_contracts', function (Blueprint $table) {
            $table->id();
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('customer_inquiries', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_contacted')->default(0);
            $table->boolean('is_task')->default(0);
            $table->string('task_status')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_10_29_230000_create_contracting_system_tables.php'))->up();
        (require database_path('migrations/2026_10_30_033000_add_area_sqm_to_cont_projects_table.php'))->up();
        (require database_path('migrations/2026_10_30_160000_add_supervision_pct_to_cont_operations_table.php'))->up();
        (require database_path('migrations/2026_10_30_190000_create_cont_auxiliary_settings_tables.php'))->up();

        if (!Schema::hasColumn('cont_operations', 'original_quantity')) {
            Schema::table('cont_operations', function (Blueprint $table) {
                $table->decimal('original_quantity', 15, 2)->nullable()->after('quantity');
                $table->decimal('returned_quantity', 15, 2)->default(0)->after('original_quantity');
            });
        }
        if (function_exists('ensureSupplierCreditSchema')) {
            ensureSupplierCreditSchema();
        }

        $userId = DB::table('users')->insertGetId([
            'id'        => 10,
            'name'      => 'Developer',
            'email'     => 'dev@example.com',
            'role'      => 'admin',
            'is_active' => 1,
        ]);
        $this->devUser = DB::table('users')->where('id', $userId)->first();

        $this->accountId = DB::table('accounts')->insertGetId([
            'account_name' => 'الخزنة الرئيسية',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 10000,
        ]);

        $this->projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'مشروع اختبار التشطيب',
            'client_name'     => 'عميل الاختبار',
            'client_phone'    => '01000000001',
            'supervision_pct' => 10,
            'status'          => 'active',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('cont_directory')->insert([
            [
                'type'         => 'supplier',
                'name'         => 'مورد خامات الأمل',
                'balance_debt' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'type'         => 'technician',
                'name'         => 'فني سباكة معتمد',
                'balance_debt' => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    protected function actingAsDeveloper()
    {
        return $this->withSession([
            'auth_user'       => $this->devUser,
            'auth_last_check' => time(),
        ]);
    }

    public function test_materials_omit_fake_unit_while_technicians_keep_unit(): void
    {
        $resp1 = $this->actingAsDeveloper()
            ->postJson(route('contracting.operation.store'), [
                'project_id' => $this->projectId,
                'items'      => [
                    [
                        'op_type'         => 'material',
                        'is_billable'     => 1,
                        'name_or_title'   => 'سلك كهرباء 3 مم',
                        'quantity'        => 4,
                        'cost_price'      => 200,
                        'selling_price'   => 250,
                        'person_name'     => 'مورد خامات الأمل',
                        'payment_method'  => 'later',
                        'paid_amount'     => 0,
                        'invoice_ref'     => 'INV-UNIT-1',
                    ],
                    [
                        'op_type'         => 'technician',
                        'is_billable'     => 1,
                        'name_or_title'   => 'تأسيس سباكة حمام',
                        'quantity'        => 12,
                        'unit'            => 'متر',
                        'cost_price'      => 100,
                        'selling_price'   => 120,
                        'person_name'     => 'فني سباكة معتمد',
                        'payment_method'  => 'later',
                        'paid_amount'     => 0,
                    ],
                ],
            ]);
        $this->assertTrue($resp1->isOk(), 'storeOperation failed: ' . $resp1->status() . ' ' . $resp1->content());
        $resp1->assertJson(['success' => true]);

        $matOp = DB::table('cont_operations')->where('op_type', 'material')->first();
        $techOp = DB::table('cont_operations')->where('op_type', 'technician')->first();

        $this->assertSame('', (string) $matOp->unit);
        $this->assertSame('متر', (string) $techOp->unit);

        // Edit material invoice without specifying unit -> remains empty string (not 'عدد')
        $this->actingAsDeveloper()
            ->postJson(route('contracting.material_invoice.update'), [
                'project_id'    => $this->projectId,
                'supplier_name' => 'مورد خامات الأمل',
                'invoice_ref'   => 'INV-UNIT-1',
                'items'         => [
                    [
                        'name_or_title' => 'سلك كهرباء 3 مم محدث',
                        'quantity'      => 5,
                        'cost_price'    => 200,
                        'selling_price' => 250,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $updatedMat = DB::table('cont_operations')
            ->where('op_type', 'material')
            ->where('invoice_ref', 'INV-UNIT-1')
            ->first();
        $this->assertSame('', (string) $updatedMat->unit);
    }

    public function test_petty_and_overhead_defer_to_company_debts_with_distinct_rows_and_no_supplier_credit(): void
    {
        $resp2 = $this->actingAsDeveloper()
            ->postJson(route('contracting.operation.store'), [
                'project_id' => $this->projectId,
                'items'      => [
                    [
                        'op_type'        => 'petty',
                        'is_billable'    => 1,
                        'name_or_title'  => 'مشال خامات للدور الرابع',
                        'quantity'       => 1,
                        'cost_price'     => 350,
                        'selling_price'  => 350,
                        // Even if cash/account were submitted, controller must force deferred debt
                        'payment_method' => 'cash',
                        'paid_amount'    => 350,
                        'account_id'     => $this->accountId,
                    ],
                    [
                        'op_type'        => 'overhead',
                        'is_billable'    => 0,
                        'name_or_title'  => 'حراسة ليلية للموقع',
                        'quantity'       => 1,
                        'cost_price'     => 500,
                        'selling_price'  => 0,
                        'payment_method' => 'cash',
                        'paid_amount'    => 500,
                        'account_id'     => $this->accountId,
                    ],
                ],
            ]);
        $this->assertTrue($resp2->isOk(), 'storeOperation expenses failed: ' . $resp2->status() . ' ' . $resp2->content());
        $resp2->assertJson(['success' => true]);

        // Treasury must NOT be deducted immediately
        $accBalance = (float) DB::table('accounts')->where('id', $this->accountId)->value('balance');
        $this->assertEquals(10000.0, $accBalance);

        // Operations recorded as 'later' with empty unit and exact person names
        $pettyOp = DB::table('cont_operations')->where('op_type', 'petty')->first();
        $overheadOp = DB::table('cont_operations')->where('op_type', 'overhead')->first();

        $this->assertSame('later', $pettyOp->payment_method);
        $this->assertEquals(0.0, (float) $pettyOp->paid_amount);
        $this->assertEquals(350.0, (float) $pettyOp->remaining_debt);
        $this->assertSame('نثريات المقاولات', $pettyOp->person_name);
        $this->assertSame('', (string) $pettyOp->unit);

        $this->assertSame('later', $overheadOp->payment_method);
        $this->assertEquals(0.0, (float) $overheadOp->paid_amount);
        $this->assertEquals(500.0, (float) $overheadOp->remaining_debt);
        $this->assertSame('مصروفات داخلية - المقاولات', $overheadOp->person_name);
        $this->assertSame('', (string) $overheadOp->unit);

        // company_debts rows must carry distinct categories & no cont_directory entry
        $pettyDebt = DB::table('company_debts')->where('id', $pettyOp->debt_id)->first();
        $overheadDebt = DB::table('company_debts')->where('id', $overheadOp->debt_id)->first();

        $this->assertSame('مصروف نثري', $pettyDebt->category);
        $this->assertSame('نثريات المقاولات', $pettyDebt->creditor_name);
        $this->assertEquals(350.0, (float) $pettyDebt->remaining_balance);

        $this->assertSame('مصروف داخلي', $overheadDebt->category);
        $this->assertSame('مصروفات داخلية - المقاولات', $overheadDebt->creditor_name);
        $this->assertEquals(500.0, (float) $overheadDebt->remaining_balance);

        $this->assertFalse(DB::table('cont_directory')->whereIn('name', ['نثريات المقاولات', 'مصروفات داخلية - المقاولات'])->exists());

        // /debts2 screen renders distinct row classes and badges, and disables contracting overpayment flag
        $response = $this->actingAsDeveloper()->get('/debts2');
        $response->assertOk();
        $response->assertSee('cont-petty-debt-row', false);
        $response->assertSee('cont-overhead-debt-row', false);
        $response->assertSee('نثريات مقاولات', false);
        $response->assertSee('مصروفات داخلية', false);

        // Overpayment on petty debt via single pay must NOT be treated as contracting supplier excess credit
        $this->actingAsDeveloper()
            ->post(route('company_debts.pay'), [
                'debt_id'    => $pettyDebt->id,
                'amount'     => 400, // exceeds 350
                'account_id' => $this->accountId,
            ])
            ->assertSessionHas('error');

        // Overpayment on overhead creditor via partial pay must also be rejected
        $this->actingAsDeveloper()
            ->post(route('company_debts.pay_partial'), [
                'creditor_name'   => 'مصروفات داخلية - المقاولات',
                'amount'          => 600, // exceeds 500
                'earned_discount' => 0,
                'account_id'      => $this->accountId,
            ])
            ->assertSessionHas('error');

        // Treasury untouched after rejected overpayments
        $this->assertEquals(10000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // Exact settlement on petty debt succeeds cleanly
        $payResp = $this->actingAsDeveloper()
            ->post(route('company_debts.pay'), [
                'debt_id'    => $pettyDebt->id,
                'amount'     => 350,
                'account_id' => $this->accountId,
            ]);
        $this->assertTrue(session()->has('success'), 'Expected success, status=' . $payResp->status() . ' error=' . session('error'));

        $this->assertEquals(0.0, (float) DB::table('company_debts')->where('id', $pettyDebt->id)->value('remaining_balance'));
        $this->assertEquals(9650.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
    }
}
