<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckDeveloperAccess;
use App\Services\ScreenAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CashflowsAndContractingAccessTest extends TestCase
{
    protected object $adminUser;
    protected object $managerUser;
    protected object $employeeUser;
    protected int $projectId;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        ScreenAccessService::clearCache();

        foreach ([
            'jamia_installments', 'jamia_shares', 'jamias',
            'cont_units', 'cont_petty_titles', 'cont_expense_titles',
            'cont_petty_expenses', 'cont_petty_categories', 'cont_global_items', 'cont_surveys',
            'cont_material_returns', 'cont_worker_payments', 'cont_supplier_credit_logs',
            'cont_invoices', 'cont_quotations', 'cont_discounts',
            'cont_operations', 'cont_directory', 'cont_project_items', 'cont_projects',
            'installment_payments', 'installment_expenses', 'installments', 'company_debts',
            'financial_transactions', 'accounts', 'capital_adjustments', 'system_settings',
            'activity_logs', 'audit_logs', 'sales', 'assets', 'fuel_transactions',
            'sy2_installment_contracts', 'suppliers', 'customer_inquiries', 'users',
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
            $table->string('transaction_code')->nullable();
            $table->string('type');
            $table->string('subtype', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
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
            $table->decimal('monthly_installment', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->integer('due_day')->default(1);
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
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->text('notes')->nullable();
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

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_inquiries', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_contacted')->default(0);
            $table->boolean('is_task')->default(0);
            $table->string('task_status')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_11_04_220000_create_jamias_system_tables.php'))->up();
        (require database_path('migrations/2026_11_13_000000_convert_jamias_to_organizer_model.php'))->up();
        (require database_path('migrations/2026_11_18_193000_add_share_fraction_to_jamia_shares_table.php'))->up();
        (require database_path('migrations/2026_10_29_230000_create_contracting_system_tables.php'))->up();
        (require database_path('migrations/2026_10_29_233000_create_contracting_settings_tables.php'))->up();
        (require database_path('migrations/2026_10_30_033000_add_area_sqm_to_cont_projects_table.php'))->up();
        (require database_path('migrations/2026_10_30_043000_create_cont_surveys_table.php'))->up();
        (require database_path('migrations/2026_10_30_160000_add_supervision_pct_to_cont_operations_table.php'))->up();
        (require database_path('migrations/2026_10_30_190000_create_cont_auxiliary_settings_tables.php'))->up();
        (require database_path('migrations/2026_10_31_070000_add_pricing_to_cont_surveys_table.php'))->up();

        $adminId = DB::table('users')->insertGetId([
            'name'      => 'أحمد مدير عام',
            'email'     => 'ahmed@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'admin',
            'is_active' => 1,
        ]);
        $mgrId = DB::table('users')->insertGetId([
            'name'      => 'كريم مدير',
            'email'     => 'karim@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'manager',
            'is_active' => 1,
        ]);
        $empId = DB::table('users')->insertGetId([
            'name'      => 'سارة موظفة',
            'email'     => 'sara@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'employee',
            'is_active' => 1,
        ]);

        $this->adminUser    = DB::table('users')->where('id', $adminId)->first();
        $this->managerUser  = DB::table('users')->where('id', $mgrId)->first();
        $this->employeeUser = DB::table('users')->where('id', $empId)->first();

        $this->projectId = DB::table('cont_projects')->insertGetId([
            'name'            => 'مشروع اختبار الوصول',
            'client_name'     => 'عميل الاختبار',
            'client_phone'    => '01000000001',
            'supervision_pct' => 10,
            'status'          => 'active',
            'created_by'      => $adminId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function test_no_cashflows_or_contracting_routes_have_developer_only_middleware(): void
    {
        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();
            if (str_starts_with($name, 'cashflows.') || str_starts_with($name, 'contracting.')) {
                $middlewares = $route->gatherMiddleware();
                foreach ($middlewares as $mw) {
                    $this->assertStringNotContainsString(
                        CheckDeveloperAccess::class,
                        (string) $mw,
                        "Route [{$name}] still has CheckDeveloperAccess middleware."
                    );
                }
            }
        }
    }

    public function test_non_developer_users_can_open_cashflows_and_contracting_endpoints(): void
    {
        foreach ([$this->adminUser, $this->managerUser, $this->employeeUser] as $user) {
            $this->assertFalse(isDeveloperUser($user));

            // 1) شاشة التدفقات (الجمعيات) تفتح مباشرة بدون coming_soon
            $cfRes = $this->withSession(['auth_user' => $user])
                ->get(route('cashflows.index'));
            $cfRes->assertOk();
            $cfRes->assertDontSee('الشاشة قيد التنفيذ سوف تتاح قريباً');
            $cfRes->assertDontSee('>قريباً<', false);

            // 2) كشف حساب عضو الجمعيات لا يرفض غير المطور بـ 403
            $stmtRes = $this->withSession(['auth_user' => $user])
                ->getJson(route('cashflows.member_statement', ['name' => 'عضو غير موجود', 'format' => 'json']));
            $this->assertNotEquals(403, $stmtRes->status());

            // 3) نقاط المقاولات (JSON وطباعة الكشف) تفتح مباشرة لغير المطور بدون 403 أو coming_soon
            $summaryRes = $this->withSession(['auth_user' => $user])
                ->getJson(route('contracting.project.summary', ['id' => $this->projectId]));
            $summaryRes->assertOk();

            $contStmtRes = $this->withSession(['auth_user' => $user])
                ->get(route('contracting.statement', ['id' => $this->projectId]));
            $contStmtRes->assertOk();
            $contStmtRes->assertDontSee('الشاشة قيد التنفيذ سوف تتاح قريباً');
        }
    }

    public function test_custom_screen_restrictions_still_apply_to_cashflows_and_contracting(): void
    {
        // إذا قيّد الأدمن موظفاً على شاشة المهام فقط، يظل ممنوعاً من التدفقات والمقاولات
        DB::table('users')->where('id', $this->employeeUser->id)->update([
            'allowed_screens' => json_encode(['inquiries']),
        ]);
        ScreenAccessService::clearCache();

        $cfBlocked = $this->withSession(['auth_user' => $this->employeeUser])
            ->get(route('cashflows.index'));
        $cfBlocked->assertRedirect(route('inquiries.index'));

        $contBlocked = $this->withSession(['auth_user' => $this->employeeUser])
            ->getJson(route('contracting.project.summary', ['id' => $this->projectId]));
        $contBlocked->assertStatus(403);
    }
}
