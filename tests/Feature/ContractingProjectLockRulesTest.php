<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContractingProjectLockRulesTest extends TestCase
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

        DB::table('cont_directory')->insert([
            'type'         => 'supplier',
            'name'         => 'مورد خامات البناء',
            'balance_debt' => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    protected function actingAsDeveloper()
    {
        return $this->withSession([
            'auth_user'       => $this->devUser,
            'auth_last_check' => time(),
        ]);
    }

    protected function seedProjectWithOperations(string $status = 'active'): array
    {
        $projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'مشروع اختبار الحالات',
            'client_name'     => 'عميل الاختبار',
            'client_phone'    => '01012345678',
            'supervision_pct' => 10,
            'status'          => $status,
            'created_by'      => 10,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Seed via active status if needed, or insert operations directly
        $matOpId = DB::table('cont_operations')->insertGetId([
            'project_id'        => $projectId,
            'op_type'           => 'material',
            'is_billable'       => 1,
            'name_or_title'     => 'أسمنت ممتاز',
            'quantity'          => 10,
            'original_quantity' => 10,
            'returned_quantity' => 0,
            'unit'              => '',
            'cost_price'        => 100,
            'supervision_pct'   => 10,
            'selling_price'     => 100,
            'total_cost'        => 1000,
            'total_selling'     => 1100,
            'profit_amount'     => 100,
            'person_name'       => 'مورد خامات البناء',
            'payment_method'    => 'later',
            'paid_amount'       => 0,
            'remaining_debt'    => 1000,
            'invoice_ref'       => 'MAT-LOCK-1',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $techOpId = DB::table('cont_operations')->insertGetId([
            'project_id'        => $projectId,
            'op_type'           => 'technician',
            'is_billable'       => 1,
            'name_or_title'     => 'مصنعية محارة',
            'quantity'          => 5,
            'original_quantity' => 5,
            'returned_quantity' => 0,
            'unit'              => 'متر',
            'cost_price'        => 80,
            'supervision_pct'   => 10,
            'selling_price'     => 100,
            'total_cost'        => 400,
            'total_selling'     => 550,
            'profit_amount'     => 150,
            'person_name'       => 'فني محارة',
            'payment_method'    => 'later',
            'paid_amount'       => 0,
            'remaining_debt'    => 400,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return [
            'project_id'  => $projectId,
            'mat_op_id'   => $matOpId,
            'tech_op_id'  => $techOpId,
            'invoice_ref' => 'MAT-LOCK-1',
        ];
    }

    public function test_completed_project_blocks_delete_edit_and_returns_and_unlocks_when_reactivated(): void
    {
        $seeded = $this->seedProjectWithOperations('active');
        $projectId = $seeded['project_id'];
        $matOpId = $seeded['mat_op_id'];
        $techOpId = $seeded['tech_op_id'];
        $invoiceRef = $seeded['invoice_ref'];

        // 1. Mark project as completed
        $this->actingAsDeveloper()
            ->postJson(route('contracting.project.update', $projectId), [
                'status' => 'completed',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('completed', DB::table('cont_projects')->where('id', $projectId)->value('status'));

        // 2. While completed, all deletes, edits, and returns MUST be blocked (422)
        $this->actingAsDeveloper()
            ->deleteJson(route('contracting.operation.destroy', $techOpId))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->actingAsDeveloper()
            ->postJson(route('contracting.operation.update', $techOpId), [
                'name_or_title' => 'تعديل مرفوض',
                'quantity'      => 6,
                'cost_price'    => 90,
                'selling_price' => 110,
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->actingAsDeveloper()
            ->postJson(route('contracting.material_invoice.update'), [
                'invoice_ref'   => $invoiceRef,
                'project_id'    => $projectId,
                'supplier_name' => 'مورد خامات البناء',
                'items'         => [[
                    'name_or_title' => 'أسمنت معدل',
                    'quantity'      => 10,
                    'cost_price'    => 110,
                    'selling_price' => 110,
                ]],
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->actingAsDeveloper()
            ->postJson(route('contracting.material_invoice.return'), [
                'project_id'        => $projectId,
                'invoice_ref'       => $invoiceRef,
                'excess_settlement' => 'supplier_credit',
                'items'             => [[
                    'id'         => $matOpId,
                    'return_qty' => 2,
                ]],
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->actingAsDeveloper()
            ->deleteJson(route('contracting.project.destroy', $projectId))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // Editing project data while keeping status = completed is also blocked
        $this->actingAsDeveloper()
            ->postJson(route('contracting.project.update', $projectId), [
                'name'   => 'اسم معدل مرفوض',
                'status' => 'completed',
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // AJAX endpoints report is_locked = true
        $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->assertJsonPath('locks.is_completed', true)
            ->assertJsonPath('locks.is_installment', false)
            ->assertJsonPath('locks.is_locked', true);

        $this->actingAsDeveloper()
            ->getJson(route('contracting.material_invoice.details', ['invoice_ref' => $invoiceRef, 'project_id' => $projectId]))
            ->assertOk()
            ->assertJsonPath('is_locked', true);

        $this->actingAsDeveloper()
            ->getJson(route('contracting.invoice.details', ['invoice_ref' => $invoiceRef, 'project_id' => $projectId]))
            ->assertOk()
            ->assertJsonPath('is_locked', true);

        // 3. Reactivate project back to active -> edit, return, and delete MUST work again
        $this->actingAsDeveloper()
            ->postJson(route('contracting.project.update', $projectId), [
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->assertJsonPath('locks.is_locked', false);

        // Edit operation succeeds now
        $this->actingAsDeveloper()
            ->postJson(route('contracting.operation.update', $techOpId), [
                'name_or_title' => 'مصنعية محارة معدلة',
                'quantity'      => 6,
                'cost_price'    => 80,
                'selling_price' => 100,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // Edit material invoice succeeds now
        $this->actingAsDeveloper()
            ->postJson(route('contracting.material_invoice.update'), [
                'invoice_ref'   => $invoiceRef,
                'project_id'    => $projectId,
                'supplier_name' => 'مورد خامات البناء',
                'items'         => [[
                    'name_or_title' => 'أسمنت ممتاز معدل',
                    'quantity'      => 10,
                    'cost_price'    => 100,
                    'selling_price' => 100,
                ]],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $freshMatOpId = DB::table('cont_operations')
            ->where('project_id', $projectId)
            ->where('invoice_ref', $invoiceRef)
            ->value('id');

        // Material return succeeds now
        $this->actingAsDeveloper()
            ->postJson(route('contracting.material_invoice.return'), [
                'project_id'        => $projectId,
                'invoice_ref'       => $invoiceRef,
                'excess_settlement' => 'supplier_credit',
                'items'             => [[
                    'id'         => $freshMatOpId,
                    'return_qty' => 2,
                ]],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // Delete operation succeeds now
        $this->actingAsDeveloper()
            ->deleteJson(route('contracting.operation.destroy', $techOpId))
            ->assertOk()
            ->assertJson(['success' => true]);

        // Delete project succeeds now
        $this->actingAsDeveloper()
            ->deleteJson(route('contracting.project.destroy', $projectId))
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_installment_converted_project_locks_status_change_and_permanently_blocks_delete_edit_and_returns(): void
    {
        $seeded = $this->seedProjectWithOperations('completed');
        $projectId = $seeded['project_id'];
        $matOpId = $seeded['mat_op_id'];
        $techOpId = $seeded['tech_op_id'];
        $invoiceRef = $seeded['invoice_ref'];

        // Convert completed project to installment contract
        $this->actingAsDeveloper()
            ->postJson(route('contracting.installment.store'), [
                'project_id'         => $projectId,
                'amount'             => 1650,
                'installment_months' => 6,
                'start_date'         => '2026-11-16',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // 1. Cannot change status to active or suspended or anything else
        foreach (['active', 'suspended', 'completed'] as $targetStatus) {
            $this->actingAsDeveloper()
                ->postJson(route('contracting.project.update', $projectId), [
                    'name'   => 'اسم آخر',
                    'status' => $targetStatus,
                ])
                ->assertStatus(422)
                ->assertJson(['success' => false]);
        }
        $this->assertEquals('completed', DB::table('cont_projects')->where('id', $projectId)->value('status'));

        // 2. Delete project is blocked
        $this->actingAsDeveloper()
            ->deleteJson(route('contracting.project.destroy', $projectId))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // 3. Edit operation is blocked
        $this->actingAsDeveloper()
            ->postJson(route('contracting.operation.update', $techOpId), [
                'name_or_title' => 'تعديل غير مسموح',
                'quantity'      => 5,
                'cost_price'    => 90,
                'selling_price' => 110,
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // 4. Delete operation is blocked
        $this->actingAsDeveloper()
            ->deleteJson(route('contracting.operation.destroy', $techOpId))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // 5. Edit material invoice is blocked
        $this->actingAsDeveloper()
            ->postJson(route('contracting.material_invoice.update'), [
                'invoice_ref'   => $invoiceRef,
                'project_id'    => $projectId,
                'supplier_name' => 'مورد خامات البناء',
                'items'         => [[
                    'name_or_title' => 'أسمنت معدل',
                    'quantity'      => 10,
                    'cost_price'    => 100,
                    'selling_price' => 100,
                ]],
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // 6. Material return is blocked
        $this->actingAsDeveloper()
            ->postJson(route('contracting.material_invoice.return'), [
                'project_id'        => $projectId,
                'invoice_ref'       => $invoiceRef,
                'excess_settlement' => 'supplier_credit',
                'items'             => [[
                    'id'         => $matOpId,
                    'return_qty' => 1,
                ]],
            ])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // 7. Summary and details AJAX reflect installment lock
        $this->actingAsDeveloper()
            ->getJson(route('contracting.project.summary', $projectId))
            ->assertOk()
            ->assertJsonPath('locks.is_installment', true)
            ->assertJsonPath('locks.is_locked', true);
    }

    public function test_contracting_blade_renders_lock_states_cleanly(): void
    {
        $completedProj = (object) [
            'id' => 101, 'name' => 'مشروع مكتمل', 'client_name' => 'عميل 1',
            'client_phone' => '010', 'location' => 'القاهرة', 'area_sqm' => 120,
            'supervision_pct' => 15, 'contract_type' => 'cost_plus', 'status' => 'completed', 'notes' => '',
        ];
        $instProj = (object) [
            'id' => 102, 'name' => 'مشروع تقسيط', 'client_name' => 'عميل 2',
            'client_phone' => '011', 'location' => 'الجيزة', 'area_sqm' => 150,
            'supervision_pct' => 15, 'contract_type' => 'cost_plus', 'status' => 'completed', 'notes' => '',
        ];
        $activeProj = (object) [
            'id' => 103, 'name' => 'مشروع نشط', 'client_name' => 'عميل 3',
            'client_phone' => '012', 'location' => 'أكتوبر', 'area_sqm' => 100,
            'supervision_pct' => 15, 'contract_type' => 'cost_plus', 'status' => 'active', 'notes' => '',
        ];

        $projects = collect([$completedProj, $instProj, $activeProj]);
        $projectMetrics = [
            101 => ['ops_count' => 1, 'total_cost' => 100, 'net_selling' => 115, 'client_paid' => 0, 'client_remaining' => 115, 'client_credit' => 0, 'net_profit' => 15, 'is_installment_plan' => false, 'installment_months' => 0],
            102 => ['ops_count' => 1, 'total_cost' => 100, 'net_selling' => 115, 'client_paid' => 0, 'client_remaining' => 115, 'client_credit' => 0, 'net_profit' => 15, 'is_installment_plan' => true, 'installment_months' => 6],
            103 => ['ops_count' => 1, 'total_cost' => 100, 'net_selling' => 115, 'client_paid' => 0, 'client_remaining' => 115, 'client_credit' => 0, 'net_profit' => 15, 'is_installment_plan' => false, 'installment_months' => 0],
        ];

        $materialInvoices = collect([
            (object) [
                'invoice_ref' => 'INV-COMP', 'project_id' => 101, 'project_name' => 'مشروع مكتمل', 'client_name' => 'عميل 1',
                'supplier_name' => 'مورد 1', 'items_count' => 1, 'materials_names' => 'أسمنت', 'total_cost' => 100,
                'total_selling' => 115, 'payment_method' => 'cash', 'total_paid' => 100, 'total_debt' => 0,
                'total_net_qty' => 5, 'total_returned_qty' => 0, 'created_at' => '2026-11-16 10:00:00',
                'project_status' => 'completed', 'is_installment_plan' => false,
            ],
            (object) [
                'invoice_ref' => 'INV-INST', 'project_id' => 102, 'project_name' => 'مشروع تقسيط', 'client_name' => 'عميل 2',
                'supplier_name' => 'مورد 1', 'items_count' => 1, 'materials_names' => 'حديد', 'total_cost' => 200,
                'total_selling' => 230, 'payment_method' => 'cash', 'total_paid' => 200, 'total_debt' => 0,
                'total_net_qty' => 5, 'total_returned_qty' => 0, 'created_at' => '2026-11-16 10:00:00',
                'project_status' => 'completed', 'is_installment_plan' => true,
            ],
            (object) [
                'invoice_ref' => 'INV-ACT', 'project_id' => 103, 'project_name' => 'مشروع نشط', 'client_name' => 'عميل 3',
                'supplier_name' => 'مورد 1', 'items_count' => 1, 'materials_names' => 'رمل', 'total_cost' => 50,
                'total_selling' => 57.5, 'payment_method' => 'cash', 'total_paid' => 50, 'total_debt' => 0,
                'total_net_qty' => 5, 'total_returned_qty' => 0, 'created_at' => '2026-11-16 10:00:00',
                'project_status' => 'active', 'is_installment_plan' => false,
            ],
        ]);

        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        $html = view('contracting', [
            'projects'                => $projects,
            'projectMetrics'          => $projectMetrics,
            'items'                   => collect(),
            'globalItems'             => collect(),
            'pettyCategories'         => collect(),
            'recentPettyExpenses'     => collect(),
            'directory'               => collect(),
            'suppliers'               => collect(),
            'technicians'             => collect(),
            'marketers'               => collect(),
            'clientsList'             => collect(),
            'accounts'                => collect(),
            'recentOperations'        => collect(),
            'quotations'              => collect(),
            'invoices'                => collect(),
            'materialInvoices'        => $materialInvoices,
            'cancelledInvoices'       => collect(),
            'materialReturns'         => collect(),
            'returnsStats'            => [],
            'surveys'                 => collect(),
            'materialPricesSummary'   => collect(),
            'supplierComparisonCards' => collect(),
            'autocompleteMaterials'   => collect(),
            'autocompleteUnits'       => collect(),
            'autocompleteItemTitles'  => collect(),
            'autocompleteSuppliers'   => collect(),
            'autocompleteTechs'       => collect(),
            'autocompleteMarketers'   => collect(),
            'unitsList'               => collect(),
            'pettyTitlesList'         => collect(),
            'expenseTitlesList'       => collect(),
            'reportProjectMetrics'    => [],
            'reportHighlights'        => [
                'kpis' => [
                    'grand_selling' => 0, 'grand_cost' => 0, 'grand_profit' => 0,
                    'avg_margin' => 0, 'grand_paid' => 0, 'grand_remaining' => 0,
                    'collection_rate' => 0, 'supplier_debts' => 0,
                ],
                'overview' => [
                    'projects_total_count' => 3, 'projects_active_count' => 1,
                    'gross_selling' => 0, 'total_discounts' => 0, 'net_selling' => 0,
                    'client_paid' => 0, 'client_remaining' => 0,
                    'materials_ops_count' => 0, 'materials_invoices_cnt' => 0,
                    'materials_cost' => 0, 'materials_selling' => 0, 'materials_profit' => 0,
                    'suppliers_debt_now' => 0, 'suppliers_credit_now' => 0,
                    'returns_count' => 0, 'returns_cost_total' => 0, 'returns_sell_total' => 0,
                    'tech_ops_count' => 0, 'tech_cost' => 0, 'tech_selling' => 0,
                    'tech_profit' => 0, 'tech_debt_now' => 0,
                    'petty_count' => 0, 'petty_cost' => 0, 'petty_selling' => 0, 'petty_profit' => 0,
                    'overhead_count' => 0, 'overhead_cost' => 0,
                    'general_petty_count' => 0, 'general_petty_cost' => 0,
                    'commission_count' => 0, 'commission_cost' => 0,
                    'net_profit_projects' => 0, 'net_profit_after_gen' => 0,
                ],
                'petty_ops_list' => collect(),
                'petty_by_title' => collect(),
                'overhead_ops_list' => collect(),
                'overhead_by_title' => collect(),
                'general_petty_list' => collect(),
                'general_petty_by_title' => collect(),
                'all_expenses_petty_log' => collect(),
                'top_profit_project' => null,
                'top_spending_project' => null,
                'top_discount_project' => null,
                'top_marketer' => null,
                'top_cost_item' => null,
                'top_profit_item' => null,
                'top_tech' => null,
                'top_material' => null,
                'supervision_profit_total' => 0,
                'expenses_breakdown' => [
                    'material' => 0, 'technician' => 0, 'petty' => 0,
                    'expense' => 0, 'general_petty' => 0, 'commission' => 0, 'total' => 0,
                ],
                'monthly_trend' => [],
                'projects_comparison' => collect(),
            ],
            'filterFromStr'           => '2026-11-01',
            'filterToStr'             => '2026-11-16',
            'filterPreset'            => 'month',
            'techLaborRows'           => [],
            'laborProjectsAgg'        => [],
            'techLaborSummary'        => ['techs_count' => 0, 'techs_owed_count' => 0, 'total' => 0, 'paid' => 0, 'remaining' => 0, 'ops_count' => 0],
            'supplierRows'            => [],
            'supProjectsAgg'          => [],
            'supplierSummary'         => ['count' => 0, 'owed_count' => 0, 'total' => 0, 'paid' => 0, 'remaining' => 0, 'credit' => 0, 'invoices_count' => 0],
        ])->render();

        // Installment project edit & delete buttons locked
        $this->assertStringContainsString('id="master-edit-btn-102"', $html);
        $this->assertStringContainsString('محول لعقد تقسيط — الحالة والتعديل والحذف مقفولان', $html);
        $this->assertStringContainsString('id="master-delete-btn-102"', $html);

        // Completed project allows opening edit modal for status reactivation, but locks delete
        $this->assertStringContainsString('تعديل الحالة / إعادة تنشيط المشروع', $html);
        $this->assertStringContainsString('id="master-delete-btn-101"', $html);

        // Settings tab also locks delete for completed and installment projects, keeps active unlocked
        $this->assertStringContainsString('id="settings-delete-btn-101"', $html);
        $this->assertStringContainsString('id="settings-delete-btn-102"', $html);
        $this->assertStringContainsString('id="settings-delete-btn-103"', $html);

        // Material invoices table locks edit & return for completed and installment projects, keeps active unlocked
        $this->assertStringContainsString("openEditMaterialInvoice('INV-ACT', 103)", $html);
        $this->assertStringContainsString("openReturnMaterialInvoice('INV-ACT', 103)", $html);
        $this->assertStringNotContainsString("openEditMaterialInvoice('INV-COMP', 101)", $html);
        $this->assertStringNotContainsString("openReturnMaterialInvoice('INV-COMP', 101)", $html);
        $this->assertStringNotContainsString("openEditMaterialInvoice('INV-INST', 102)", $html);
        $this->assertStringNotContainsString("openReturnMaterialInvoice('INV-INST', 102)", $html);

        // Return picker disables locked invoices and enables active invoice
        $this->assertStringContainsString('value="INV-COMP||101" disabled', $html);
        $this->assertStringContainsString('value="INV-INST||102" disabled', $html);
        $this->assertStringContainsString('value="INV-ACT||103" >', $html);
    }
}
