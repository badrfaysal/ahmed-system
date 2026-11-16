<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContractingStatementTypesTest extends TestCase
{
    protected int $projectId;
    protected int $itemPlumbingId;
    protected int $itemElectricId;
    protected int $emptyItemId;
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
            'financial_transactions', 'accounts', 'system_settings', 'users',
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

        $this->projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'تشطيب فيلا التجمع',
            'client_name'     => 'أحمد محمود',
            'client_phone'    => '01001234567',
            'location'        => 'التجمع الخامس',
            'supervision_pct' => 10,
            'status'          => 'active',
            'created_by'      => $userId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->itemPlumbingId = DB::table('cont_project_items')->insertGetId([
            'project_id' => $this->projectId,
            'title'      => 'بند السباكة',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->itemElectricId = DB::table('cont_project_items')->insertGetId([
            'project_id' => $this->projectId,
            'title'      => 'بند الكهرباء',
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->emptyItemId = DB::table('cont_project_items')->insertGetId([
            'project_id' => $this->projectId,
            'title'      => 'بند بدون حركات',
            'sort_order' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Plumbing: Materials = 5500, Labor = 2200, Petty = 330 => Total = 8030
        DB::table('cont_operations')->insert([
            [
                'project_id'      => $this->projectId,
                'item_id'         => $this->itemPlumbingId,
                'op_type'         => 'material',
                'name_or_title'   => 'مواسير PVC 2 بوصة',
                'person_name'     => 'مورد السباكة',
                'quantity'        => 10,
                'unit'            => '',
                'cost_price'      => 500,
                'selling_price'   => 500,
                'supervision_pct' => 10,
                'total_cost'      => 5000,
                'total_selling'   => 5500,
                'is_billable'     => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'project_id'      => $this->projectId,
                'item_id'         => $this->itemPlumbingId,
                'op_type'         => 'technician',
                'name_or_title'   => 'تأسيس مواسير الحمام - الأسطى سعيد',
                'person_name'     => 'الأسطى سعيد',
                'quantity'        => 1,
                'unit'            => 'مقطوع',
                'cost_price'      => 2000,
                'selling_price'   => 2000,
                'supervision_pct' => 10,
                'total_cost'      => 2000,
                'total_selling'   => 2200,
                'is_billable'     => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'project_id'      => $this->projectId,
                'item_id'         => $this->itemPlumbingId,
                'op_type'         => 'petty',
                'name_or_title'   => 'ونش رفع خامات السباكة',
                'person_name'     => 'نثريات المقاولات',
                'quantity'        => 1,
                'unit'            => '',
                'cost_price'      => 300,
                'selling_price'   => 300,
                'supervision_pct' => 10,
                'total_cost'      => 300,
                'total_selling'   => 330,
                'is_billable'     => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            // Electricity: Materials = 4400, Labor = 1500, Petty = 0 => Total = 5900
            [
                'project_id'      => $this->projectId,
                'item_id'         => $this->itemElectricId,
                'op_type'         => 'material',
                'name_or_title'   => 'أسلاك السويدي 3مم',
                'person_name'     => 'مورد الكهرباء',
                'quantity'        => 4,
                'unit'            => '',
                'cost_price'      => 1000,
                'selling_price'   => 1000,
                'supervision_pct' => 10,
                'total_cost'      => 4000,
                'total_selling'   => 4400,
                'is_billable'     => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'project_id'      => $this->projectId,
                'item_id'         => $this->itemElectricId,
                'op_type'         => 'technician',
                'name_or_title'   => 'مد خراطيم الكهرباء',
                'person_name'     => 'الأسطى كريم',
                'quantity'        => 1,
                'unit'            => 'مقطوع',
                'cost_price'      => 1500,
                'selling_price'   => 1500,
                'supervision_pct' => 0,
                'total_cost'      => 1500,
                'total_selling'   => 1500,
                'is_billable'     => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            // Unassigned general billable petty = 250
            [
                'project_id'      => $this->projectId,
                'item_id'         => null,
                'op_type'         => 'petty',
                'name_or_title'   => 'نظافة وتشوين عام للموقع',
                'person_name'     => 'نثريات المقاولات',
                'quantity'        => 1,
                'unit'            => '',
                'cost_price'      => 250,
                'selling_price'   => 250,
                'supervision_pct' => 0,
                'total_cost'      => 250,
                'total_selling'   => 250,
                'is_billable'     => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            // Non-billable overhead (must NOT appear in customer statements)
            [
                'project_id'      => $this->projectId,
                'item_id'         => $this->itemPlumbingId,
                'op_type'         => 'overhead',
                'name_or_title'   => 'مصروف إداري سري للشركة',
                'person_name'     => 'مصروفات داخلية - المقاولات',
                'quantity'        => 1,
                'unit'            => '',
                'cost_price'      => 999,
                'selling_price'   => 0,
                'supervision_pct' => 0,
                'total_cost'      => 999,
                'total_selling'   => 0,
                'is_billable'     => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);

        // Totals across project: Materials = 9,900 | Labor = 3,700 | Expenses = 580 | Subtotal = 14,180
        // Discount = 180 => Net = 14,000 | Paid = 5,000 | Remaining = 9,000
        DB::table('cont_discounts')->insert([
            'project_id' => $this->projectId,
            'amount'     => 180,
            'reason'     => 'خصم تعاقد',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cont_invoices')->insert([
            'project_id'        => $this->projectId,
            'invoice_number'    => 'PAY-0001',
            'invoice_type'      => 'payment',
            'total_billed'      => 0,
            'paid_amount'       => 5000,
            'remaining_balance' => 9000,
            'notes'             => 'دفعة أولى',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    protected function actingAsDeveloper()
    {
        return $this->withSession([
            'auth_user'       => $this->devUser,
            'auth_last_check' => time(),
        ]);
    }

    protected function extractMainTable(string $html): string
    {
        if (preg_match('/<table class="statement-table">.*?<\/table>/s', $html, $matches)) {
            return $matches[0];
        }
        $this->fail('Could not locate statement-table in response HTML.');
    }

    public function test_summary_statement_hides_material_and_op_details_and_shows_item_totals(): void
    {
        $response = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $this->projectId, 'type' => 'summary']));

        $response->assertOk();
        $tableHtml = $this->extractMainTable($response->getContent());

        // Summary headers and title
        $response->assertSee('كشف حساب مختصر (بنود)', false);
        $this->assertStringContainsString('إجمالي الخامات', $tableHtml);
        $this->assertStringContainsString('إجمالي المصنعية', $tableHtml);
        $this->assertStringContainsString('إجمالي المصاريف', $tableHtml);
        $this->assertStringContainsString('إجمالي البند', $tableHtml);

        // Item titles appear (non-empty items + general unassigned row), empty item does not
        $this->assertStringContainsString('بند السباكة', $tableHtml);
        $this->assertStringContainsString('بند الكهرباء', $tableHtml);
        $this->assertStringContainsString('أعمال ومصروفات عامة على المشروع', $tableHtml);
        $this->assertStringNotContainsString('بند بدون حركات', $tableHtml);

        // Individual raw material, labor, and petty operation lines MUST NOT appear anywhere
        $response->assertDontSee('مواسير PVC 2 بوصة', false);
        $response->assertDontSee('أسلاك السويدي 3مم', false);
        $response->assertDontSee('تأسيس مواسير الحمام', false);
        $response->assertDontSee('ونش رفع خامات السباكة', false);
        $response->assertDontSee('مد خراطيم الكهرباء', false);
        $response->assertDontSee('نظافة وتشوين عام للموقع', false);
        $response->assertDontSee('مصروف إداري سري للشركة', false);
        $response->assertDontSee('الأسطى سعيد', false);

        // Detailed column header "سعر الخامة" should not appear in summary table
        $this->assertStringNotContainsString('سعر الخامة', $tableHtml);

        // Item-level totals: Plumbing (5,500.00 | 2,200.00 | 330.00 | 8,030.00)
        $this->assertStringContainsString('5,500.00', $tableHtml);
        $this->assertStringContainsString('2,200.00', $tableHtml);
        $this->assertStringContainsString('330.00', $tableHtml);
        $this->assertStringContainsString('8,030.00', $tableHtml);

        // Item-level totals: Electric (4,400.00 | 1,500.00 | 5,900.00)
        $this->assertStringContainsString('4,400.00', $tableHtml);
        $this->assertStringContainsString('1,500.00', $tableHtml);
        $this->assertStringContainsString('5,900.00', $tableHtml);

        // Project-level column sums & full reconciliation (9,900.00 | 3,700.00 | 580.00 | 14,180.00 | net 14,000.00 | paid 5,000.00 | rem 9,000.00)
        $this->assertStringContainsString('9,900.00', $tableHtml);
        $this->assertStringContainsString('3,700.00', $tableHtml);
        $this->assertStringContainsString('580.00', $tableHtml);
        $this->assertStringContainsString('14,180.00', $tableHtml);
        $response->assertSee('14,000.00', false);
        $response->assertSee('5,000.00', false);
        $response->assertSee('9,000.00', false);
        $response->assertSee('دفعة أولى', false);
    }

    public function test_detailed_and_internal_statements_still_render_operation_details(): void
    {
        $response = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $this->projectId, 'type' => 'detailed']));

        $response->assertOk();
        $response->assertSee('تفاصيل المصروفات لكل بند', false);
        $response->assertSee('سعر الخامة', false);
        $response->assertSee('مواسير PVC 2 بوصة', false);
        $response->assertSee('أسلاك السويدي 3مم', false);
        $response->assertSee('تأسيس مواسير الحمام', false);
        $response->assertSee('ونش رفع خامات السباكة', false);
        $response->assertSee('نظافة وتشوين عام للموقع', false);
        $response->assertDontSee('الأسطى سعيد', false);
        $response->assertDontSee('مصروف إداري سري للشركة', false);

        $internalResp = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $this->projectId, 'type' => 'internal']));

        $internalResp->assertOk();
        $internalResp->assertSee('كشف حساب داخلي (للشركة)', false);
        $internalResp->assertSee('الأسطى سعيد', false);
        $internalResp->assertSee('مواسير PVC 2 بوصة', false);
    }

    public function test_summary_statement_supports_tax_and_item_filter(): void
    {
        $taxResp = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $this->projectId, 'type' => 'summary', 'is_tax' => 1]));

        $taxResp->assertOk();
        // 14% of 14,000 = 1,960.00 -> final = 15,960.00
        $taxResp->assertSee('ضريبة القيمة المضافة (14%)', false);
        $taxResp->assertSee('1,960.00', false);
        $taxResp->assertSee('15,960.00', false);

        $itemResp = $this->actingAsDeveloper()
            ->get(route('contracting.statement', ['id' => $this->projectId, 'type' => 'summary', 'item_id' => $this->itemPlumbingId]));

        $itemResp->assertOk();
        $itemTable = $this->extractMainTable($itemResp->getContent());
        $itemResp->assertSee('كشف حساب مختصر (بند: بند السباكة)', false);
        $this->assertStringContainsString('بند السباكة', $itemTable);
        $this->assertStringContainsString('8,030.00', $itemTable);
        $this->assertStringNotContainsString('بند الكهرباء', $itemTable);
        $itemResp->assertDontSee('مواسير PVC 2 بوصة', false);
    }
}
