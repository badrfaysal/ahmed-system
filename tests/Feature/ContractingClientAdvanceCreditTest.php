<?php

namespace Tests\Feature;

use App\Services\InstallmentFinanceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContractingClientAdvanceCreditTest extends TestCase
{
    protected int $accountId;
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
            'financial_transactions', 'accounts', 'system_settings', 'activity_logs',
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

        (require database_path('migrations/2026_10_29_230000_create_contracting_system_tables.php'))->up();
        (require database_path('migrations/2026_10_30_033000_add_area_sqm_to_cont_projects_table.php'))->up();
        (require database_path('migrations/2026_10_30_160000_add_supervision_pct_to_cont_operations_table.php'))->up();

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
            'balance'      => 1000,
        ]);
    }

    protected function actingAsDeveloper()
    {
        return $this->withSession([
            'auth_user'       => $this->devUser,
            'auth_last_check' => time(),
        ]);
    }

    public function test_zero_bill_upfront_deposit_shows_client_credit_and_keeps_capital_in_liquidity(): void
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'مشروع تحت الحساب',
            'client_name'     => 'عميل مقدم',
            'client_phone'    => '01011112222',
            'supervision_pct' => 10,
            'status'          => 'active',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $payResp = $this->actingAsDeveloper()
            ->postJson(route('contracting.payment.store'), [
                'project_id'  => $projectId,
                'paid_amount' => 100,
                'account_id'  => $this->accountId,
                'notes'       => 'دفعة مقدمة قبل بدء الشغل',
            ])
            ->assertOk()
            ->json();

        $this->assertTrue($payResp['success']);
        $this->assertEquals(100.0, (float) $payResp['client_paid']);
        $this->assertEquals(0.0, (float) $payResp['client_remaining']);
        $this->assertEquals(100.0, (float) $payResp['client_credit']);

        $summary = $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->json();
        $this->assertEquals(100.0, (float) $summary['totals']['client_credit']);
        $this->assertEquals(0.0, (float) $summary['totals']['client_remaining']);

        // Statement displays client advance credit
        $stmt = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $projectId, 'type' => 'summary']))
            ->assertOk();
        $stmt->assertSee('رصيد مقدم للعميل', false);
        $stmt->assertSee('100.00 ج.م', false);

        // Treasury liquidity & capital include the 100 without deducting any client liability
        $this->assertEquals(1100.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
        $treasury = InstallmentFinanceService::treasurySummary();
        $this->assertEquals(1100.0, (float) $treasury['liquidity']);
        $this->assertEquals(1100.0, (float) $treasury['capital']);
    }

    public function test_overpayment_creates_credit_and_automatically_deducts_when_new_work_is_added(): void
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'تشطيب شقة المعادي',
            'client_name'     => 'خالد علي',
            'client_phone'    => '01022223333',
            'supervision_pct' => 10,
            'status'          => 'active',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Initial bill = 40
        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'technician',
            'is_billable'   => 1,
            'name_or_title' => 'بند أول',
            'quantity'      => 1,
            'cost_price'    => 30,
            'selling_price' => 40,
            'total_cost'    => 30,
            'total_selling' => 40,
            'profit_amount' => 10,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Client pays 100 -> excess credit = 60
        $payResp = $this->actingAsDeveloper()
            ->postJson(route('contracting.payment.store'), [
                'project_id'  => $projectId,
                'paid_amount' => 100,
                'account_id'  => $this->accountId,
                'notes'       => 'دفعة زيادة 60',
            ])
            ->assertOk()
            ->json();

        $this->assertEquals(100.0, (float) $payResp['client_paid']);
        $this->assertEquals(0.0, (float) $payResp['client_remaining']);
        $this->assertEquals(60.0, (float) $payResp['client_credit']);

        $stmt = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $projectId, 'type' => 'summary']))
            ->assertOk();
        $stmt->assertSee('رصيد مقدم للعميل', false);
        $stmt->assertSee('60.00 ج.م', false);

        // Add new work of 30 -> total bill = 70, remaining = 0, credit drops to 30
        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'petty',
            'is_billable'   => 1,
            'name_or_title' => 'بند ثاني',
            'quantity'      => 1,
            'cost_price'    => 25,
            'selling_price' => 30,
            'total_cost'    => 25,
            'total_selling' => 30,
            'profit_amount' => 5,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $summary2 = $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->json();
        $this->assertEquals(70.0, (float) $summary2['totals']['net_selling']);
        $this->assertEquals(100.0, (float) $summary2['totals']['client_paid']);
        $this->assertEquals(0.0, (float) $summary2['totals']['client_remaining']);
        $this->assertEquals(30.0, (float) $summary2['totals']['client_credit']);

        // Add new work of 50 -> total bill = 120, credit consumed = 0, remaining = 20
        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'technician',
            'is_billable'   => 1,
            'name_or_title' => 'بند ثالث',
            'quantity'      => 1,
            'cost_price'    => 40,
            'selling_price' => 50,
            'total_cost'    => 40,
            'total_selling' => 50,
            'profit_amount' => 10,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $summary3 = $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->json();
        $this->assertEquals(120.0, (float) $summary3['totals']['net_selling']);
        $this->assertEquals(100.0, (float) $summary3['totals']['client_paid']);
        $this->assertEquals(20.0, (float) $summary3['totals']['client_remaining']);
        $this->assertEquals(0.0, (float) $summary3['totals']['client_credit']);

        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->first();
        $this->assertNotNull($inst);
        $this->assertEquals(20.0, (float) $inst->remaining_balance);
        $this->assertEquals('active', $inst->status);
    }

    public function test_refund_client_credit_validates_bounds_updates_treasury_and_reconciles_future_work(): void
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'تشطيب مكتب',
            'client_name'     => 'منى سالم',
            'client_phone'    => '01033334444',
            'supervision_pct' => 10,
            'status'          => 'active',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'technician',
            'is_billable'   => 1,
            'name_or_title' => 'أعمال تأسيس',
            'quantity'      => 1,
            'cost_price'    => 30,
            'selling_price' => 40,
            'total_cost'    => 30,
            'total_selling' => 40,
            'profit_amount' => 10,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->actingAsDeveloper()
            ->postJson(route('contracting.payment.store'), [
                'project_id'  => $projectId,
                'paid_amount' => 100,
                'account_id'  => $this->accountId,
                'notes'       => 'دفعة تحت الحساب',
            ])
            ->assertOk();

        // Treasury balance is now 1100; available credit is 60
        // Over-refund > 60 must be rejected
        $this->actingAsDeveloper()
            ->postJson(route('contracting.client_credit.refund'), [
                'project_id' => $projectId,
                'amount'     => 70,
                'account_id' => $this->accountId,
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertEquals(1100.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // Partial refund of 25 succeeds
        $refResp = $this->actingAsDeveloper()
            ->postJson(route('contracting.client_credit.refund'), [
                'project_id' => $projectId,
                'amount'     => 25,
                'account_id' => $this->accountId,
                'notes'      => 'رد جزء من الفائض',
            ])
            ->assertOk()
            ->json();

        $this->assertTrue($refResp['success']);
        $this->assertEquals(75.0, (float) $refResp['client_paid']);
        $this->assertEquals(0.0, (float) $refResp['client_remaining']);
        $this->assertEquals(35.0, (float) $refResp['client_credit']);
        $this->assertEquals(1075.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // Financial breakdown must NOT count client refund as company general expense
        $fb = InstallmentFinanceService::financialBreakdown();
        $this->assertEquals(0.0, (float) $fb['expensesGeneral']);

        // Refund invoice voucher prints cleanly
        $refundInv = DB::table('cont_invoices')
            ->where('project_id', $projectId)
            ->where('invoice_type', 'client_refund')
            ->first();
        $this->assertNotNull($refundInv);
        $this->assertEquals(-25.0, (float) $refundInv->paid_amount);

        $this->actingAsDeveloper()
            ->get(route('contracting.invoice.print', $refundInv->id))
            ->assertOk()
            ->assertSee('سند رد فائض نقدي', false);

        // Statement displays refund row and updated 35.00 credit
        $stmt = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $projectId, 'type' => 'summary']))
            ->assertOk();
        $stmt->assertSee('رد فائض للعميل', false);
        $stmt->assertSee('35.00 ج.م', false);

        // Add work of 50 -> total bill = 90, net paid = 75 -> remaining = 15, credit = 0
        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'petty',
            'is_billable'   => 1,
            'name_or_title' => 'بند إضافي',
            'quantity'      => 1,
            'cost_price'    => 40,
            'selling_price' => 50,
            'total_cost'    => 40,
            'total_selling' => 50,
            'profit_amount' => 10,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $summaryAfter = $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->json();
        $this->assertEquals(75.0, (float) $summaryAfter['totals']['client_paid']);
        $this->assertEquals(15.0, (float) $summaryAfter['totals']['client_remaining']);
        $this->assertEquals(0.0, (float) $summaryAfter['totals']['client_credit']);

        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->first();
        $this->assertEquals(15.0, (float) $inst->remaining_balance);
        $this->assertEquals('active', $inst->status);
    }

    public function test_refund_before_operations_nets_cleanly_upon_installment_conversion(): void
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'مشروع عربون ثم رد ثم تقسيط',
            'client_name'     => 'طارق حسن',
            'client_phone'    => '01044445555',
            'supervision_pct' => 10,
            'status'          => 'completed',
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Pay 100 before operations exist, then refund 40 -> net advance = 60
        $this->actingAsDeveloper()
            ->postJson(route('contracting.payment.store'), [
                'project_id'  => $projectId,
                'paid_amount' => 100,
                'account_id'  => $this->accountId,
                'notes'       => 'عربون أولي',
            ])
            ->assertOk();

        $this->actingAsDeveloper()
            ->postJson(route('contracting.client_credit.refund'), [
                'project_id' => $projectId,
                'amount'     => 40,
                'account_id' => $this->accountId,
                'notes'      => 'رد جزء من العربون',
            ])
            ->assertOk();

        // Add work of 90 -> net paid = 60, remaining = 30
        DB::table('cont_operations')->insert([
            'project_id'    => $projectId,
            'op_type'       => 'technician',
            'is_billable'   => 1,
            'name_or_title' => 'بند تشطيب',
            'quantity'      => 1,
            'cost_price'    => 70,
            'selling_price' => 90,
            'total_cost'    => 70,
            'total_selling' => 90,
            'profit_amount' => 20,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Convert remaining 30 to 3 monthly installments
        $this->actingAsDeveloper()
            ->postJson(route('contracting.installment.store'), [
                'project_id'         => $projectId,
                'amount'             => 30,
                'installment_months' => 3,
                'start_date'         => '2026-11-16',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $inst = DB::table('installments')
            ->where('source_type', 'cont_project')
            ->where('source_id', $projectId)
            ->first();
        $this->assertNotNull($inst);
        $this->assertEquals(60.0, (float) $inst->down_payment);
        $this->assertEquals(30.0, (float) $inst->remaining_balance);
        $this->assertEquals(90.0, (float) $inst->cash_price);

        // Subsequent sync must NOT double-subtract the pre-conversion refund
        app(\App\Http\Controllers\ContractingController::class)->syncProjectCustomerReceivable($projectId);
        $instAfterSync = DB::table('installments')->where('id', $inst->id)->first();
        $this->assertEquals(60.0, (float) $instAfterSync->down_payment);
        $this->assertEquals(30.0, (float) $instAfterSync->remaining_balance);
    }
}
