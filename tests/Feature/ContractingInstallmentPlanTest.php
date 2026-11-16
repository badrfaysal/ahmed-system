<?php

namespace Tests\Feature;

use App\Services\InstallmentFinanceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContractingInstallmentPlanTest extends TestCase
{
    protected int $accountId;
    protected object $devUser;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        $tables = [
            'cont_material_returns', 'cont_invoices', 'cont_quotations', 'cont_discounts',
            'cont_operations', 'cont_directory', 'cont_project_items', 'cont_projects',
            'installment_payments', 'installment_expenses', 'installments', 'company_debts',
            'financial_transactions', 'accounts', 'activity_logs', 'audit_logs',
            'sales', 'assets', 'fuel_transactions', 'sy2_installment_contracts',
            'suppliers', 'customer_inquiries', 'users',
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

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('customer_inquiries', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_contacted')->default(0);
            $table->timestamps();
        });

        $m1 = require database_path('migrations/2026_10_29_230000_create_contracting_system_tables.php');
        $m1->up();
        $m2 = require database_path('migrations/2026_10_30_033000_add_area_sqm_to_cont_projects_table.php');
        $m2->up();
        $m3 = require database_path('migrations/2026_10_30_160000_add_supervision_pct_to_cont_operations_table.php');
        $m3->up();

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
    }

    protected function actingAsDeveloper()
    {
        return $this->withSession([
            'auth_user'       => $this->devUser,
            'auth_last_check' => time(),
        ]);
    }

    public function test_prior_payments_are_summed_into_down_payment_and_client_is_removed_from_debts(): void
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'تشطيب فيلا النخيل',
            'client_name'     => 'عميل تجريبي',
            'client_phone'    => '01012345678',
            'supervision_pct' => 15,
            'status'          => 'completed',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'technician',
            'is_billable'   => 1,
            'name_or_title' => 'بند سباكة',
            'quantity'      => 1,
            'unit'          => 'مقطوع',
            'cost_price'    => 600,
            'selling_price' => 1000,
            'total_cost'    => 600,
            'total_selling' => 1000,
            'profit_amount' => 400,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Initial sync creates receivable in /debts (installment_months = 0)
        $instId = app(\App\Http\Controllers\ContractingController::class)->syncProjectCustomerReceivable($projectId);
        $this->assertNotNull($instId);

        // Payment 1 via Contracting screen: 100 EGP
        $this->actingAsDeveloper()
            ->postJson(route('contracting.payment.store'), [
                'project_id'  => $projectId,
                'paid_amount' => 100,
                'account_id'  => $this->accountId,
                'notes'       => 'دفعة أولى',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // Payment 2 via /debts screen: 120 EGP
        $this->actingAsDeveloper()
            ->post(route('installments.pay'), [
                'inst_id'   => $instId,
                'amount'    => 120,
                'method_id' => $this->accountId,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Verify client is currently in /debts with remaining 780
        $debtsBefore = DB::table('installments')
            ->where('installment_months', '<=', 0)
            ->where('status', '!=', 'cancelled')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->get();
        $this->assertCount(1, $debtsBefore);
        $this->assertEquals(780.0, (float) $debtsBefore->first()->remaining_balance);

        // Convert project to 6-month installment contract for remaining 780
        $this->actingAsDeveloper()
            ->postJson(route('contracting.installment.store'), [
                'project_id'         => $projectId,
                'amount'             => 780,
                'installment_months' => 6,
                'start_date'         => '2026-11-16',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // 1. Client MUST be completely removed from /debts (installment_months <= 0)
        $debtsAfter = DB::table('installments')
            ->where('installment_months', '<=', 0)
            ->where('status', '!=', 'cancelled')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->count();
        $this->assertEquals(0, $debtsAfter);

        // 2. Single installment contract row with down_payment = 220 (100 + 120)
        $contracts = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->get();
        $this->assertCount(1, $contracts);

        $contract = $contracts->first();
        $this->assertEquals(6, (int) $contract->installment_months);
        $this->assertEquals(220.0, (float) $contract->down_payment);
        $this->assertEquals(780.0, (float) $contract->remaining_after_down);
        $this->assertEquals(1000.0, (float) $contract->total_after_interest);
        $this->assertEquals(1000.0, (float) $contract->cash_price);
        $this->assertEquals(780.0, (float) $contract->remaining_balance);
        $this->assertEquals(130.0, (float) $contract->monthly_installment);
        $this->assertEquals(400.0, (float) $contract->profit);

        // 3. Pre-conversion payments absorbed out of installment_payments (0 monthly installments paid yet)
        $this->assertEquals(0, DB::table('installment_payments')->where('installment_id', $contract->id)->count());

        // 4. Both payment receipts remain in cont_invoices (total 220), and installment_contract has paid_amount = 0
        $receipts = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'payment_receipt')
            ->get();
        $this->assertCount(2, $receipts);
        $this->assertEquals(220.0, (float) $receipts->sum('paid_amount'));

        $planInv = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'installment_contract')
            ->first();
        $this->assertNotNull($planInv);
        $this->assertEquals(0.0, (float) $planInv->paid_amount);

        // 5. Treasury summary does NOT double-count receivables or collected money
        $summary = InstallmentFinanceService::treasurySummary();
        $this->assertEquals(780.0, (float) $summary['installmentsSystemDebts']);
        $this->assertEquals(0.0, (float) $summary['otherDebtsForUs']);
        $this->assertEquals(220.0, InstallmentFinanceService::totalCollectedAmount([$contract->id]));

        // 6. Calling syncProjectCustomerReceivable on page load preserves down_payment, receipts, and balance
        app(\App\Http\Controllers\ContractingController::class)->syncProjectCustomerReceivable($projectId);
        $this->assertEquals(2, DB::table('cont_invoices')->where('project_id', $projectId)->where('invoice_type', 'payment_receipt')->count());
        $this->assertEquals(220.0, (float) DB::table('installments')->where('id', $contract->id)->value('down_payment'));
        $this->assertEquals(780.0, (float) DB::table('installments')->where('id', $contract->id)->value('remaining_balance'));

        // 7. Subsequent monthly installment payment of 130 in /installments works cleanly and keeps down_payment = 220
        $this->actingAsDeveloper()
            ->post(route('installments.pay'), [
                'inst_id'      => $contract->id,
                'amount'       => 130,
                'method_id'    => $this->accountId,
                'payment_date' => now()->addMinute()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $afterPay = DB::table('installments')->where('id', $contract->id)->first();
        $this->assertEquals(220.0, (float) $afterPay->down_payment);
        $this->assertEquals(650.0, (float) $afterPay->remaining_balance);
        $this->assertEquals(1, DB::table('installment_payments')->where('installment_id', $contract->id)->count());
        $this->assertEquals(3, DB::table('cont_invoices')->where('project_id', $projectId)->where('invoice_type', 'payment_receipt')->count());

        $summaryJson = $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->json();
        $this->assertEquals(350.0, (float) $summaryJson['totals']['client_paid']);
        $this->assertEquals(650.0, (float) $summaryJson['totals']['client_remaining']);
        $this->assertEquals(220.0, (float) $summaryJson['installment_plan']['down_payment']);
        $this->assertEquals(130.0, (float) $summaryJson['installment_plan']['post_installments_paid']);

        // 8. Re-editing the installment plan keeps down_payment = 220 AND keeps the post-conversion payment of 130
        // Set plan created_at earlier than post-conversion payment_date
        DB::table('cont_invoices')->where('id', $planInv->id)->update(['created_at' => now()->subMinute()->format('Y-m-d H:i:s')]);
        DB::table('installment_payments')->where('installment_id', $contract->id)->update(['payment_date' => now()->addMinute()->format('Y-m-d H:i:s')]);

        $this->actingAsDeveloper()
            ->postJson(route('contracting.installment.store'), [
                'project_id'         => $projectId,
                'amount'             => 650,
                'installment_months' => 5,
                'start_date'         => '2026-11-16',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $afterEdit = DB::table('installments')->where('id', $contract->id)->first();
        $this->assertEquals(220.0, (float) $afterEdit->down_payment);
        $this->assertEquals(650.0, (float) $afterEdit->remaining_balance);
        $this->assertEquals(1000.0, (float) $afterEdit->total_after_interest);
        $this->assertEquals(1, DB::table('installment_payments')->where('installment_id', $contract->id)->count());

        // 9. Reversing the monthly installment payment removes only the monthly receipt, keeping pre-conversion receipts
        $monthlyPayId = DB::table('installment_payments')->where('installment_id', $contract->id)->value('id');
        $this->actingAsDeveloper()
            ->post(route('installments.reverse_pay'), ['payment_id' => $monthlyPayId])
            ->assertRedirect();

        $this->assertEquals(780.0, (float) DB::table('installments')->where('id', $contract->id)->value('remaining_balance'));
        $this->assertEquals(2, DB::table('cont_invoices')->where('project_id', $projectId)->where('invoice_type', 'payment_receipt')->count());
    }

    public function test_unlinked_payment_before_operations_is_counted_as_down_payment_on_conversion(): void
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'مشروع بدون بنود مبدئية',
            'client_name'     => 'عميل مقدم مبكر',
            'client_phone'    => '01099998888',
            'supervision_pct' => 10,
            'status'          => 'completed',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Pay 300 BEFORE any billable operations exist (so installment_id starts NULL)
        $this->actingAsDeveloper()
            ->postJson(route('contracting.payment.store'), [
                'project_id'  => $projectId,
                'paid_amount' => 300,
                'account_id'  => $this->accountId,
                'notes'       => 'عربون مقدم',
            ])
            ->assertOk();

        $this->assertNull(DB::table('cont_invoices')->where('project_id', $projectId)->value('installment_id'));

        // Convert with custom amount 500
        $this->actingAsDeveloper()
            ->postJson(route('contracting.installment.store'), [
                'project_id'         => $projectId,
                'amount'             => 500,
                'installment_months' => 5,
                'start_date'         => '2026-11-16',
            ])
            ->assertOk();

        $inst = DB::table('installments')->where('source_type', 'cont_project')->where('source_id', $projectId)->first();
        $this->assertNotNull($inst);
        $this->assertEquals(300.0, (float) $inst->down_payment);
        $this->assertEquals(500.0, (float) $inst->remaining_balance);
        $this->assertEquals(800.0, (float) $inst->total_after_interest);
        $this->assertEquals($inst->id, DB::table('cont_invoices')->where('project_id', $projectId)->where('invoice_type', 'payment_receipt')->value('installment_id'));
    }

    public function test_reconciliation_migration_fixes_split_rows_and_unabsorbed_prior_payments(): void
    {
        // Project A: split legacy rows (one in /debts with months=0, one in /installments with months=6)
        $projA = DB::table('cont_projects')->insertGetId([
            'name' => 'مشروع أ', 'client_name' => 'عميل أ', 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('cont_operations')->insert([
            'project_id' => $projA, 'op_type' => 'overhead', 'is_billable' => 1,
            'name_or_title' => 'بند', 'quantity' => 1, 'cost_price' => 100, 'selling_price' => 230,
            'total_cost' => 100, 'total_selling' => 230, 'profit_amount' => 130,
        ]);
        $debtRowA = DB::table('installments')->insertGetId([
            'sale_type' => 'contracting', 'source_type' => 'cont_project', 'source_id' => $projA,
            'customer_name' => 'عميل أ', 'product_name' => 'مشروع مقاولات: مشروع أ',
            'cash_price' => 230, 'total_after_interest' => 230, 'remaining_balance' => 230,
            'installment_months' => 0, 'status' => 'active', 'profit' => 130,
        ]);
        $planRowA = DB::table('installments')->insertGetId([
            'sale_type' => 'direct', 'source_type' => null, 'source_id' => null,
            'customer_name' => 'عميل أ', 'product_name' => 'أقساط مشروع مقاولة: مشروع أ',
            'cash_price' => 230, 'total_after_interest' => 230, 'remaining_balance' => 230,
            'installment_months' => 6, 'monthly_installment' => 38.33, 'status' => 'active', 'profit' => 0,
        ]);
        DB::table('cont_invoices')->insert([
            'project_id' => $projA, 'invoice_number' => 'INST-PLAN-' . $planRowA,
            'invoice_type' => 'installment_contract', 'total_billed' => 230, 'paid_amount' => 0,
            'remaining_balance' => 230, 'installment_id' => $planRowA, 'created_at' => '2026-11-15 00:16:07',
        ]);

        // Project B: converted with down_payment = 0 while pre-conversion payment of 30 sits in installment_payments
        $projB = DB::table('cont_projects')->insertGetId([
            'name' => 'مشروع ب', 'client_name' => 'عميل ب', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('cont_operations')->insert([
            'project_id' => $projB, 'op_type' => 'technician', 'is_billable' => 1,
            'name_or_title' => 'بند', 'quantity' => 1, 'cost_price' => 100, 'selling_price' => 230,
            'total_cost' => 100, 'total_selling' => 230, 'profit_amount' => 130,
        ]);
        $planRowB = DB::table('installments')->insertGetId([
            'sale_type' => 'contracting', 'source_type' => 'cont_project', 'source_id' => $projB,
            'customer_name' => 'عميل ب', 'product_name' => 'مشروع مقاولات: مشروع ب',
            'cash_price' => 230, 'down_payment' => 0, 'remaining_after_down' => 230,
            'total_after_interest' => 230, 'remaining_balance' => 200,
            'installment_months' => 6, 'monthly_installment' => 33.33, 'status' => 'active', 'profit' => 130,
        ]);
        $payIdB = DB::table('installment_payments')->insertGetId([
            'installment_id' => $planRowB, 'amount_paid' => 30, 'discount_applied' => 0,
            'payment_date' => '2026-11-16 11:53:23', 'payment_method_id' => $this->accountId,
        ]);
        DB::table('cont_invoices')->insert([
            'project_id' => $projB, 'invoice_number' => 'CONT-INV-B1',
            'invoice_type' => 'payment_receipt', 'total_billed' => 30, 'paid_amount' => 30,
            'remaining_balance' => 0, 'installment_id' => $planRowB,
            'notes' => "تحصيل دفعة نقدية من العميل (إيصال #{$payIdB})", 'created_at' => '2026-11-16 11:53:23',
        ]);
        DB::table('cont_invoices')->insert([
            'project_id' => $projB, 'invoice_number' => 'INST-PLAN-' . $planRowB,
            'invoice_type' => 'installment_contract', 'total_billed' => 200, 'paid_amount' => 0,
            'remaining_balance' => 200, 'installment_id' => $planRowB, 'created_at' => '2026-11-16 12:14:53',
        ]);

        // Run the reconciliation migration
        $mig = require database_path('migrations/2026_11_16_130000_reconcile_contracting_installment_plans.php');
        $mig->up();

        // Assert Project A no longer has a /debts row and $planRowA is properly linked
        $this->assertNull(DB::table('installments')->where('id', $debtRowA)->first());
        $fixedA = DB::table('installments')->where('id', $planRowA)->first();
        $this->assertEquals('cont_project', $fixedA->source_type);
        $this->assertEquals($projA, (int) $fixedA->source_id);
        $this->assertEquals(130.0, (float) $fixedA->profit);

        // Assert Project B's 30 payment moved into down_payment and receipt survived sync
        $fixedB = DB::table('installments')->where('id', $planRowB)->first();
        $this->assertEquals(30.0, (float) $fixedB->down_payment);
        $this->assertEquals(200.0, (float) $fixedB->remaining_after_down);
        $this->assertEquals(200.0, (float) $fixedB->remaining_balance);
        $this->assertEquals(0, DB::table('installment_payments')->where('installment_id', $planRowB)->count());

        app(\App\Http\Controllers\ContractingController::class)->syncProjectCustomerReceivable($projB);
        $this->assertEquals(1, DB::table('cont_invoices')->where('project_id', $projB)->where('invoice_type', 'payment_receipt')->count());
    }

    public function test_interest_rate_percentage_calculates_interest_total_and_monthly_installment(): void
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'مشروع بفائدة تقسيط',
            'client_name'     => 'عميل فائدة',
            'client_phone'    => '01055556666',
            'supervision_pct' => 15,
            'status'          => 'completed',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'technician',
            'is_billable'   => 1,
            'name_or_title' => 'أعمال ديكور',
            'quantity'      => 1,
            'unit'          => 'مقطوع',
            'cost_price'    => 600,
            'selling_price' => 1000,
            'total_cost'    => 600,
            'total_selling' => 1000,
            'profit_amount' => 400,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        app(\App\Http\Controllers\ContractingController::class)->syncProjectCustomerReceivable($projectId);

        // Prior payment of 200 EGP -> remaining principal before interest = 800 EGP
        $this->actingAsDeveloper()
            ->postJson(route('contracting.payment.store'), [
                'project_id'  => $projectId,
                'paid_amount' => 200,
                'account_id'  => $this->accountId,
                'notes'       => 'دفعة مقدم',
            ])
            ->assertOk();

        // User enters ONLY the interest percentage (10%) on base amount 800 over 4 months
        $this->actingAsDeveloper()
            ->postJson(route('contracting.installment.store'), [
                'project_id'         => $projectId,
                'amount'             => 800,
                'interest_rate'      => 10,
                'installment_months' => 4,
                'start_date'         => '2026-11-16',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->first();
        $this->assertNotNull($inst);
        $this->assertEquals(10.0, (float) $inst->interest_rate);
        $this->assertEquals(200.0, (float) $inst->down_payment);
        $this->assertEquals(1000.0, (float) $inst->cash_price);
        $this->assertEquals(1080.0, (float) $inst->total_after_interest);
        $this->assertEquals(880.0, (float) $inst->remaining_after_down);
        $this->assertEquals(880.0, (float) $inst->remaining_balance);
        $this->assertEquals(220.0, (float) $inst->monthly_installment);
        $this->assertEquals(480.0, (float) $inst->profit);

        // Treasury summary counts 80 EGP interest profit + 400 EGP contracting profit = 480 EGP (zero double count)
        $summary = InstallmentFinanceService::treasurySummary();
        $this->assertEquals(80.0, (float) $summary['profitInstallments']);
        $this->assertEquals(400.0, (float) $summary['profitContracting']);
        $this->assertEquals(880.0, (float) $summary['installmentsSystemDebts']);
        $this->assertEquals(0.0, (float) $summary['otherDebtsForUs']);

        // Project 360 summary returns interest rate, interest amount, base remaining, and remaining with interest
        $summaryJson = $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->json();
        $this->assertEquals(10.0, (float) $summaryJson['installment_plan']['interest_rate']);
        $this->assertEquals(80.0, (float) $summaryJson['installment_plan']['interest_amount']);
        $this->assertEquals(800.0, (float) $summaryJson['installment_plan']['base_remaining']);
        $this->assertEquals(880.0, (float) $summaryJson['totals']['client_remaining']);

        // Re-editing interest rate to 15% on base 800 updates cleanly (NOT compounding onto 880)
        $this->actingAsDeveloper()
            ->postJson(route('contracting.installment.store'), [
                'project_id'         => $projectId,
                'amount'             => 800,
                'interest_rate'      => 15,
                'installment_months' => 4,
                'start_date'         => '2026-11-16',
            ])
            ->assertOk();

        $edited = DB::table('installments')->where('id', $inst->id)->first();
        $this->assertEquals(15.0, (float) $edited->interest_rate);
        $this->assertEquals(1000.0, (float) $edited->cash_price);
        $this->assertEquals(1120.0, (float) $edited->total_after_interest);
        $this->assertEquals(920.0, (float) $edited->remaining_balance);
        $this->assertEquals(230.0, (float) $edited->monthly_installment);
        $this->assertEquals(520.0, (float) $edited->profit);
    }
}
