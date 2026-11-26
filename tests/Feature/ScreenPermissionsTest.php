<?php

namespace Tests\Feature;

use App\Services\ScreenAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScreenPermissionsTest extends TestCase
{
    protected object $adminUser;
    protected object $employeeUser;
    protected object $managerUser;

    protected function setUp(): void
    {
        parent::setUp();

        ScreenAccessService::clearCache();

        foreach ([
            'customer_inquiries', 'system_settings', 'activity_logs', 'audit_logs',
            'accounts', 'suppliers', 'transport_companies', 'gas_stations', 'items',
            'ac_technicians', 'expense_categories', 'fuel_deductions', 'employees',
            'employee_transactions', 'financial_transactions', 'cont_directory',
            'cont_supplier_credit_logs', 'users',
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
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('user_added')->default(false);
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

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('category')->default('safe_cash');
            $table->string('status')->default('active');
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('has_commission')->default(false);
            $table->string('commission_type')->nullable();
            $table->decimal('commission_value', 10, 2)->default(0);
            $table->decimal('max_commission', 10, 2)->default(0);
            $table->decimal('min_commission', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('transport_companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('gas_stations', function (Blueprint $table) {
            $table->id();
            $table->string('station_name');
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->decimal('original_price', 12, 2)->default(0);
            $table->decimal('discount_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
        });
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('ac_technicians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->timestamps();
        });
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('fuel_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('percentage', 5, 2)->default(0);
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('employee_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->string('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->string('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        (require database_path('migrations/2026_06_13_140000_create_customer_inquiries_table.php'))->up();
        (require database_path('migrations/2026_11_16_100000_add_task_fields_to_customer_inquiries.php'))->up();
        (require database_path('migrations/2026_11_16_190000_make_customer_name_nullable_on_customer_inquiries.php'))->up();
        (require database_path('migrations/2026_11_16_200000_add_task_date_to_customer_inquiries.php'))->up();

        $adminId = DB::table('users')->insertGetId([
            'name'      => 'مدير النظام',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'admin',
            'is_active' => 1,
        ]);
        $empId = DB::table('users')->insertGetId([
            'name'      => 'آية موظفة',
            'email'     => 'aya@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'employee',
            'is_active' => 1,
        ]);
        $mgrId = DB::table('users')->insertGetId([
            'name'      => 'كريم مدير',
            'email'     => 'karim@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'manager',
            'is_active' => 1,
        ]);

        $this->adminUser    = DB::table('users')->where('id', $adminId)->first();
        $this->employeeUser = DB::table('users')->where('id', $empId)->first();
        $this->managerUser  = DB::table('users')->where('id', $mgrId)->first();
    }

    public function test_admin_can_configure_allowed_screens_in_settings_and_sidebar_and_routes_are_enforced(): void
    {
        // الإعدادات تعرض جدول اليوزرز والموظفين وزر تحديد الشاشات
        $settingsRes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('settings.index'));
        $settingsRes->assertStatus(200);
        $settingsRes->assertSee('الشاشات المتاحة');
        $settingsRes->assertSee('openScreenPermissionsModal');

        // الأدمن يحدد لـ آية شاشة المهام فقط (ويحاول تمرير settings في الطلب لنتأكد أن النظام يرفضها لغير الأدمن)
        $saveRes = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('users.updateScreens', $this->employeeUser->id), [
                'screen_mode'     => 'custom',
                'allowed_screens' => ['inquiries', 'settings', 'partners'],
            ]);
        $saveRes->assertRedirect();
        $saveRes->assertSessionHas('success');

        $savedRaw = DB::table('users')->where('id', $this->employeeUser->id)->value('allowed_screens');
        $savedArr = json_decode($savedRaw, true);
        $this->assertSame(['inquiries'], $savedArr);

        // عند تسجيل دخول الموظفة يتم تحويلها مباشرة لشاشة المهام
        $loginRes = $this->post(route('login.submit'), [
            'email'    => 'aya@example.com',
            'password' => 'secret123',
        ]);
        $loginRes->assertRedirect(route('inquiries.index'));

        // الصفحة الرئيسية "/" تحولها مباشرة لشاشة المهام أيضاً
        $rootRes = $this->get('/');
        $rootRes->assertRedirect(route('inquiries.index'));

        // السايدبار في شاشة المهام يظهر لها "المهام" فقط ويخفي باقي الشاشات
        $inqRes = $this->get(route('inquiries.index'));
        $inqRes->assertStatus(200);
        $inqRes->assertSee('data-label="المهام"', false);
        $inqRes->assertDontSee('data-label="المركز المالي"', false);
        $inqRes->assertDontSee('data-label="لوحة التحكم"', false);
        $inqRes->assertDontSee('data-label="بيع الخدمات"', false);
        $inqRes->assertDontSee('data-label="الإعدادات العامة"', false);
        $inqRes->assertDontSee('data-label="رأس مال المقاولات"', false);

        // محاولة الدخول المباشر على أي شاشة غير مسموحة يتم رفضها وتحويلها لشاشة المهام
        $treasuryRes = $this->get(route('treasury.index'));
        $treasuryRes->assertRedirect(route('inquiries.index'));
        $treasuryRes->assertSessionHas('error');

        $salesRes = $this->get(route('sales.index'));
        $salesRes->assertRedirect(route('inquiries.index'));

        $dashRes = $this->get(route('dashboard.index'));
        $dashRes->assertRedirect(route('inquiries.index'));

        // طلب JSON غير مصرح يعيد 403
        $jsonRes = $this->getJson(route('treasury.index'));
        $jsonRes->assertStatus(403);
    }

    public function test_admin_can_grant_hr_and_notifications_explicitly_and_reset_to_default(): void
    {
        // منح المدير شاشات: شؤون الموظفين + الإشعارات فقط
        $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('users.updateScreens', $this->managerUser->id), [
                'screen_mode'     => 'custom',
                'allowed_screens' => ['hr', 'notifications'],
            ])->assertSessionHas('success');

        // حتى لو سيشن المدير قديمة، التغيير يطبّق فوراً من الداتا بيز
        $hrRes = $this->withSession(['auth_user' => $this->managerUser])
            ->get(route('hr.index'));
        $hrRes->assertStatus(200);
        $hrRes->assertSee('data-label="شؤون الموظفين"', false);
        $hrRes->assertSee('data-label="الإشعارات وسجل النشاط"', false);
        $hrRes->assertDontSee('data-label="المركز المالي"', false);

        $notifRes = $this->get(route('notifications.index'));
        $notifRes->assertStatus(200);

        // محاولة دخول الإعدادات تظل مرفوضة لغير الأدمن
        $settingsAttempt = $this->get(route('settings.index'));
        $settingsAttempt->assertRedirect(route('hr.index'));

        // إرجاع المدير للوضع الافتراضي حسب الدور
        $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('users.updateScreens', $this->managerUser->id), [
                'screen_mode' => 'default',
            ])->assertSessionHas('success');

        $this->assertNull(DB::table('users')->where('id', $this->managerUser->id)->value('allowed_screens'));

        // الآن لا يمكنه دخول HR الافتراضي للأدمن، ويحول للخزينة الافتراضية
        $hrAfterReset = $this->withSession(['auth_user' => $this->managerUser])
            ->get(route('hr.index'));
        $hrAfterReset->assertRedirect(route('treasury.index'));
    }

    public function test_store_user_accepts_custom_screens_and_prevents_empty_custom_list_and_self_lockout(): void
    {
        // رفض حفظ قائمة فارغة عند اختيار التحديد المخصص
        $emptyRes = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('users.updateScreens', $this->employeeUser->id), [
                'screen_mode'     => 'custom',
                'allowed_screens' => [],
            ]);
        $emptyRes->assertSessionHas('error');
        $this->assertNull(DB::table('users')->where('id', $this->employeeUser->id)->value('allowed_screens'));

        // إنشاء موظف جديد بشاشة المهام فقط من فورم الإنشاء
        $createRes = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('users.store'), [
                'name'            => 'موظف مهام جديد',
                'email'           => 'tasks.only@example.com',
                'password'        => '123456',
                'role'            => 'employee',
                'screen_mode'     => 'custom',
                'allowed_screens' => ['inquiries'],
            ]);
        $createRes->assertSessionHas('success');

        $newUser = DB::table('users')->where('email', 'tasks.only@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertSame(['inquiries'], json_decode($newUser->allowed_screens, true));

        // الأدمن لا يمكنه إزالة شاشة الإعدادات عن نفسه بالخطأ
        $selfRes = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('users.updateScreens', $this->adminUser->id), [
                'screen_mode'     => 'custom',
                'allowed_screens' => ['inquiries'],
            ]);
        $selfRes->assertSessionHas('success');
        $adminScreens = json_decode(DB::table('users')->where('id', $this->adminUser->id)->value('allowed_screens'), true);
        $this->assertContains('settings', $adminScreens);
        $this->assertContains('inquiries', $adminScreens);
    }

    public function test_cashflows_and_contracting_are_open_to_non_developer_users_and_visible_in_settings(): void
    {
        // تأكد أن الأدمن غير المطور يرى التدفقات والمقاولات ضمن شاشات الإعدادات بدون مجموعة "أقسام المطور"
        $this->assertFalse(isDeveloperUser($this->adminUser));
        $this->assertFalse(isDeveloperUser($this->employeeUser));
        $this->assertFalse(isDeveloperUser($this->managerUser));

        $settingsRes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('settings.index'));
        $settingsRes->assertStatus(200);
        $settingsRes->assertSee('sp_cb_cashflows', false);
        $settingsRes->assertSee('sp_cb_contracting', false);
        $settingsRes->assertDontSee('أقسام المطور');

        // السايدبار لغير المطور لا يعرض شارة "قريباً" على التدفقات أو المقاولات
        $settingsRes->assertSee('data-label="التدفقات"', false);
        $settingsRes->assertSee('data-label="المقاولات والتشطيبات"', false);
        $settingsRes->assertDontSee('>قريباً<', false);

        // افتراضياً الموظف والمدير والأدمن يمتلكون صلاحية الدخول على الشاشتين
        foreach ([$this->adminUser, $this->managerUser, $this->employeeUser] as $u) {
            $this->assertTrue(ScreenAccessService::canAccessScreen($u, 'cashflows'));
            $this->assertTrue(ScreenAccessService::canAccessScreen($u, 'contracting'));
        }

        // الأدمن غير المطور يمكنه تخصيص التدفقات والمقاولات لموظف وتُفتح له مباشرة كصفحة رئيسية
        $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('users.updateScreens', $this->employeeUser->id), [
                'screen_mode'     => 'custom',
                'allowed_screens' => ['cashflows', 'contracting'],
            ])->assertSessionHas('success');

        $this->assertSame('cashflows.index', ScreenAccessService::homeRouteForUser($this->employeeUser));
    }

    public function test_safe_and_wallet_deletion_is_disabled_in_settings_ui_and_backend(): void
    {
        $safeId = DB::table('accounts')->insertGetId([
            'account_name' => 'خزنة الشركة',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 0,
        ]);
        $walletId = DB::table('accounts')->insertGetId([
            'account_name' => 'انستاباي احمد',
            'category'     => 'bank_wallet',
            'status'       => 'active',
            'balance'      => 0,
        ]);

        // لا يظهر زر أو مودال حذف للخزن والمحافظ في شاشة الإعدادات
        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('settings.index'));
        $res->assertStatus(200);
        $res->assertSee('خزنة الشركة');
        $res->assertSee('انستاباي احمد');
        $res->assertDontSee('deleteWalletModal' . $safeId, false);
        $res->assertDontSee('deleteWalletModal' . $walletId, false);

        // حتى مع طلب POST مباشر وبرصيد صفر وكلمة مرور صحيحة، يتم منع الحذف من الباك إند
        foreach ([$safeId, $walletId] as $accId) {
            $this->withSession(['auth_user' => $this->adminUser])
                ->post(route('settings.destroyAccount', $accId), [
                    'admin_pin' => 'secret123',
                ])
                ->assertSessionHas('error');

            $this->assertDatabaseHas('accounts', ['id' => $accId]);
        }
    }
}
