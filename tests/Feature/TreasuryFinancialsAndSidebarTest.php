<?php

namespace Tests\Feature;

use App\Services\ScreenAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TreasuryFinancialsAndSidebarTest extends TestCase
{
    protected object $adminUser;
    protected object $employeeVisibleFin;
    protected object $employeeHiddenFin;

    protected function setUp(): void
    {
        parent::setUp();

        ScreenAccessService::clearCache();

        foreach ([
            'sy2_worker_payments', 'sy2_band_workers', 'sy2_supplier_debts',
            'sy2_client_payments', 'sy2_installment_contracts', 'sy2_projects', 'sy2_accounts',
            'capital_snapshots', 'customer_inquiries', 'system_settings', 'activity_logs',
            'audit_logs', 'fuel_transactions', 'assets', 'sales', 'company_debts',
            'installment_payments', 'installments', 'financial_transactions', 'accounts',
            'cont_directory', 'cont_operations', 'cont_discounts', 'expense_categories', 'goals', 'users',
        ] as $tbl) {
            Schema::dropIfExists($tbl);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('employee');
            $table->boolean('is_active')->default(true);
            $table->boolean('hide_financials')->default(false);
            $table->text('allowed_screens')->nullable();
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
            $table->string('type');
            $table->string('subtype', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('total_before', 15, 2)->nullable();
            $table->decimal('total_after', 15, 2)->nullable();
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->unsignedBigInteger('to_account_id')->nullable();
            $table->string('person_name')->nullable();
            $table->string('ref_type')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('product_name')->nullable();
            $table->string('sale_type')->nullable();
            $table->string('category')->nullable();
            $table->decimal('cash_price', 15, 2)->default(0);
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->integer('installment_months')->default(0);
            $table->decimal('total_after_interest', 15, 2)->default(0);
            $table->decimal('monthly_installment', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->string('status')->default('active');
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
            $table->timestamps();
        });

        Schema::create('company_debts', function (Blueprint $table) {
            $table->id();
            $table->string('creditor_name');
            $table->string('category')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_status')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('remaining_quantity', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('status')->default('active');
            $table->decimal('current_value', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('ahmed_profit', 15, 2)->default(0);
            $table->unsignedBigInteger('superseded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('capital_snapshots', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_capital', 15, 2)->default(0);
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
            $table->boolean('is_read')->default(0);
            $table->timestamps();
        });

        Schema::create('customer_inquiries', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_contacted')->default(0);
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('هدف');
            $table->string('type')->default('profit');
            $table->string('period')->default('month');
            $table->decimal('target_amount', 15, 2)->default(1000);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->decimal('achieved_amount', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sy2_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('active');
            $table->decimal('balance', 15, 2)->default(0);
        });
        Schema::create('sy2_projects', function (Blueprint $table) {
            $table->id();
            $table->decimal('cached_actual_total', 15, 2)->default(0);
            $table->decimal('cached_collected', 15, 2)->default(0);
            $table->decimal('cached_discount', 15, 2)->default(0);
        });
        Schema::create('sy2_installment_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('status')->default('active');
            $table->decimal('total_after_interest', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
        });
        Schema::create('sy2_supplier_debts', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('open');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
        });
        Schema::create('sy2_band_workers', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2)->default(0);
        });
        Schema::create('sy2_worker_payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
        });

        // Seed financial data: 50,000 liquidity + 25,000 installments = 75,000 capital (installment profit = 5,000)
        DB::table('accounts')->insert([
            'account_name' => 'الخزنة الرئيسية',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 50000,
        ]);
        DB::table('installments')->insert([
            'customer_name'        => 'عميل تجريبي',
            'installment_months'   => 12,
            'cash_price'           => 20000,
            'purchase_cost'        => 18000,
            'total_after_interest' => 25000,
            'profit'               => 5000,
            'remaining_balance'    => 25000,
            'status'               => 'active',
        ]);

        $adminId = DB::table('users')->insertGetId([
            'name'            => 'مدير النظام',
            'email'           => 'admin@example.com',
            'password'        => Hash::make('secret123'),
            'role'            => 'admin',
            'is_active'       => 1,
            'hide_financials' => 0,
        ]);
        $empVisibleId = DB::table('users')->insertGetId([
            'name'            => 'موظف أرقام ظاهرة',
            'email'           => 'emp.visible@example.com',
            'password'        => Hash::make('secret123'),
            'role'            => 'employee',
            'is_active'       => 1,
            'hide_financials' => 0,
        ]);
        $empHiddenId = DB::table('users')->insertGetId([
            'name'            => 'موظف أرقام مخفية',
            'email'           => 'emp.hidden@example.com',
            'password'        => Hash::make('secret123'),
            'role'            => 'employee',
            'is_active'       => 1,
            'hide_financials' => 1,
        ]);

        $this->adminUser          = DB::table('users')->where('id', $adminId)->first();
        $this->employeeVisibleFin = DB::table('users')->where('id', $empVisibleId)->first();
        $this->employeeHiddenFin  = DB::table('users')->where('id', $empHiddenId)->first();
    }

    public function test_employee_with_visible_financials_sees_capital_and_installments_on_treasury(): void
    {
        $res = $this->withSession(['auth_user' => $this->employeeVisibleFin])
            ->get(route('treasury.index'));

        $res->assertStatus(200);
        $res->assertSee('75,000');
        $res->assertSee('25,000');
        $res->assertSee('href="' . url('/installments') . '"', false);
        $res->assertDontSee('صلاحية الأدمن فقط');
        $res->assertDontSee('الأرقام المالية مخفية');
    }

    public function test_employee_with_hidden_financials_has_capital_and_installments_locked_on_treasury(): void
    {
        $res = $this->withSession(['auth_user' => $this->employeeHiddenFin])
            ->get(route('treasury.index'));

        $res->assertStatus(200);
        $res->assertSee('الأرقام المالية مخفية');
        $res->assertDontSee('صلاحية الأدمن فقط');
        $res->assertDontSee('75,000');
        $res->assertDontSee('25,000');
    }

    public function test_toggling_financials_takes_effect_immediately_for_stale_employee_session(): void
    {
        // Admin toggles hide_financials off (1 -> 0) for employeeHiddenFin
        $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('users.toggleFinancials', $this->employeeHiddenFin->id))
            ->assertRedirect();

        $this->assertEquals(0, (int) DB::table('users')->where('id', $this->employeeHiddenFin->id)->value('hide_financials'));

        // Even with the old stale session object (hide_financials = 1), RestrictFinancialAccess syncs from DB
        $goalsRes = $this->withSession(['auth_user' => $this->employeeHiddenFin])
            ->get(route('goals.index'));
        $goalsRes->assertStatus(200);

        $treasuryRes = $this->withSession(['auth_user' => $this->employeeHiddenFin])
            ->get(route('treasury.index'));
        $treasuryRes->assertStatus(200);
        $treasuryRes->assertSee('75,000');
        $treasuryRes->assertDontSee('صلاحية الأدمن فقط');
    }

    public function test_sidebar_removes_supplier_credit_bell_and_places_logout_at_top(): void
    {
        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('treasury.index'));

        $res->assertStatus(200);
        $html = $res->getContent();

        // Supplier credit floating pill button is completely removed
        $res->assertDontSee('sbSupplierCreditBellWrap', false);
        $res->assertDontSee('sbSupplierCreditBellBtn', false);
        $res->assertDontSee('sbSupplierCreditDropdown', false);
        $res->assertDontSee('أرصدة زيادة لينا عند الموردين');

        // Refund modal & JS helper remain available for contracting/debts2 pages
        $res->assertSee('id="modalRefundSupplierCredit"', false);
        $res->assertSee('window.openRefundSupplierCreditModal', false);

        // Logout button is at the top of the sidebar (before sb-body) and not in a bottom sb-footer
        $res->assertDontSee('<div class="sb-footer">', false);
        $logoutPos = strpos($html, 'sb-btn-logout');
        $bodyPos   = strpos($html, '<div class="sb-body">');

        $this->assertNotFalse($logoutPos, 'Logout button must exist in sidebar');
        $this->assertNotFalse($bodyPos, 'Sidebar navigation body must exist');
        $this->assertLessThan($bodyPos, $logoutPos, 'Logout button must appear above the sidebar navigation body');
    }

    public function test_sidebar_is_vertically_scrollable_for_all_screen_sizes(): void
    {
        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('treasury.index'));

        $res->assertStatus(200);
        $html = $res->getContent();

        // Sidebar itself scrolls vertically with dynamic viewport height & touch support
        $this->assertStringContainsString('height: 100dvh;', $html);
        $this->assertStringContainsString('overflow-y: auto;', $html);
        $this->assertStringContainsString('overscroll-behavior: contain;', $html);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch;', $html);
        $this->assertStringContainsString('env(safe-area-inset-bottom', $html);
        $this->assertStringContainsString('@media (max-height: 480px)', $html);
        $this->assertStringContainsString('sb_scroll_top', $html);
    }

    public function test_employee_with_treasury_only_screen_and_visible_financials_sees_numbers_without_broken_installments_link(): void
    {
        DB::table('users')
            ->where('id', $this->employeeVisibleFin->id)
            ->update(['allowed_screens' => json_encode(['treasury'])]);
        ScreenAccessService::clearCache();

        $res = $this->withSession(['auth_user' => $this->employeeVisibleFin])
            ->get(route('treasury.index'));

        $res->assertStatus(200);
        $res->assertSee('75,000');
        $res->assertSee('25,000');
        $res->assertDontSee('صلاحية الأدمن فقط');
        $res->assertDontSee('href="' . url('/installments') . '"', false);
    }
}
