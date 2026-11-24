<?php

namespace Tests\Feature;

use App\Services\ScreenAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TasksScreenTest extends TestCase
{
    protected object $adminUser;
    protected object $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        ScreenAccessService::clearCache();

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('customer_inquiries');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('admin');
            $table->boolean('is_active')->default(true);
            $table->boolean('hide_financials')->default(false);
            $table->text('allowed_screens')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->string('status')->default('active');
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
            $table->timestamps();
        });

        $m1 = require database_path('migrations/2026_06_13_140000_create_customer_inquiries_table.php');
        $m1->up();
        $m2 = require database_path('migrations/2026_11_16_100000_add_task_fields_to_customer_inquiries.php');
        $m2->up();
        $m3 = require database_path('migrations/2026_11_16_190000_make_customer_name_nullable_on_customer_inquiries.php');
        $m3->up();
        $m4 = require database_path('migrations/2026_11_16_200000_add_task_date_to_customer_inquiries.php');
        $m4->up();
        $m5 = require database_path('migrations/2026_06_13_160000_create_audit_logs_table.php');
        $m5->up();

        $adminId = DB::table('users')->insertGetId([
            'name'      => 'مدير النظام',
            'email'     => 'admin@example.com',
            'role'      => 'admin',
            'is_active' => 1,
        ]);
        $staffId = DB::table('users')->insertGetId([
            'name'      => 'أحمد جمال',
            'email'     => 'ahmed@example.com',
            'role'      => 'employee',
            'is_active' => 1,
        ]);

        $this->adminUser = DB::table('users')->where('id', $adminId)->first();
        $this->staffUser = DB::table('users')->where('id', $staffId)->first();

        DB::table('employees')->insert([
            'name'   => 'حسن السواق',
            'phone'  => '01099887766',
            'status' => 'active',
        ]);
    }

    public function test_can_create_titleless_tasks_for_custom_and_system_employees(): void
    {
        $response = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'tasks' => [
                    [
                        'details'        => 'تسليم فاتورة مدرسة النهضة للمخزن',
                        'assigned_to'    => '',
                        'assigned_name'  => 'كريم فني التكييف',
                        'assigned_phone' => '01011223344',
                    ],
                    [
                        'details'        => 'متابعة تحصيل قسط العميل محمود',
                        'assigned_to'    => '',
                        'assigned_name'  => 'احمد جمال', // يطابق مستخدم السيستم (أحمد جمال) مع تطبيع الهمزة
                        'assigned_phone' => '',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rows = DB::table('customer_inquiries')->orderBy('id')->get();
        $this->assertCount(2, $rows);

        // المهمة الأولى: بدون عنوان ولموظف غير مسجل بالسيستم
        $this->assertNull($rows[0]->customer_name);
        $this->assertSame('تسليم فاتورة مدرسة النهضة للمخزن', $rows[0]->inquiry);
        $this->assertNull($rows[0]->assigned_to);
        $this->assertSame('كريم فني التكييف', $rows[0]->assigned_to_name);
        $this->assertSame('01011223344', $rows[0]->assigned_phone);

        // المهمة الثانية: بدون عنوان وربط تلقائي بمستخدم السيستم عبر مطابقة الاسم
        $this->assertNull($rows[1]->customer_name);
        $this->assertSame('متابعة تحصيل قسط العميل محمود', $rows[1]->inquiry);
        $this->assertEquals($this->staffUser->id, $rows[1]->assigned_to);
        $this->assertSame('أحمد جمال', $rows[1]->assigned_to_name);
    }

    public function test_quick_bulk_mode_preserves_colons_and_dashes_without_splitting_title(): void
    {
        $response = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'bulk_text'     => "1- صيانة التكييف: الدور الثاني - مكتب المدير\nمراجعة الحساب مع المورد",
                'assigned_name' => 'سعيد',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rows = DB::table('customer_inquiries')->orderBy('id')->get();
        $this->assertCount(2, $rows);

        $this->assertNull($rows[0]->customer_name);
        $this->assertSame('صيانة التكييف: الدور الثاني - مكتب المدير', $rows[0]->inquiry);
        $this->assertNull($rows[0]->assigned_to);
        $this->assertSame('سعيد', $rows[0]->assigned_to_name);

        $this->assertNull($rows[1]->customer_name);
        $this->assertSame('مراجعة الحساب مع المورد', $rows[1]->inquiry);
        $this->assertSame('سعيد', $rows[1]->assigned_to_name);
    }

    public function test_store_requires_task_text_and_assignee_name(): void
    {
        $resNoTask = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'tasks' => [
                    ['details' => '   ', 'assigned_name' => 'كريم'],
                ],
            ]);
        $resNoTask->assertSessionHas('error');

        $resNoAssignee = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'tasks' => [
                    ['details' => 'مهمة بدون اسم موظف', 'assigned_name' => '   '],
                ],
            ]);
        $resNoAssignee->assertSessionHas('error');
        $this->assertSame(0, DB::table('customer_inquiries')->count());
    }

    public function test_can_update_task_without_title_and_change_to_custom_assignee(): void
    {
        $taskId = DB::table('customer_inquiries')->insertGetId([
            'customer_name'    => null,
            'inquiry'          => 'مهمة قديمة',
            'assigned_to'      => $this->staffUser->id,
            'assigned_to_name' => $this->staffUser->name,
            'is_contacted'     => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.update', $taskId), [
                'inquiry'          => 'مهمة محدثة بدون عنوان',
                'assigned_to'      => '',
                'assigned_to_name' => 'طارق السائق',
                'assigned_phone'   => '01234567890',
            ]);

        $res->assertRedirect();
        $res->assertSessionHas('success');

        $updated = DB::table('customer_inquiries')->where('id', $taskId)->first();
        $this->assertNull($updated->customer_name);
        $this->assertSame('مهمة محدثة بدون عنوان', $updated->inquiry);
        $this->assertNull($updated->assigned_to);
        $this->assertSame('طارق السائق', $updated->assigned_to_name);
        $this->assertSame('01234567890', $updated->assigned_phone);
    }

    public function test_index_groups_custom_employees_separately_and_supports_filtering(): void
    {
        DB::table('customer_inquiries')->insert([
            [
                'customer_name'    => null,
                'inquiry'          => 'توصيل شحنة المعادي',
                'assigned_to'      => null,
                'assigned_to_name' => 'سائق خارجي 1',
                'is_contacted'     => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'customer_name'    => null,
                'inquiry'          => 'توريد قطع غيار',
                'assigned_to'      => null,
                'assigned_to_name' => 'سائق خارجي 2',
                'is_contacted'     => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'customer_name'    => null,
                'inquiry'          => 'مراجعة عقود الشهر',
                'assigned_to'      => $this->staffUser->id,
                'assigned_to_name' => $this->staffUser->name,
                'is_contacted'     => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);

        // الصفحة الرئيسية تعرض كل الموظفين في مجموعات منفصلة
        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('inquiries.index'));
        $res->assertStatus(200);
        $res->assertSee('سائق خارجي 1');
        $res->assertSee('سائق خارجي 2');
        $res->assertSee('أحمد جمال');
        $res->assertSee('توصيل شحنة المعادي');
        $res->assertSee('توريد قطع غيار');

        $pendingDays = $res->viewData('pendingDays');
        $this->assertCount(1, $pendingDays);
        $this->assertCount(3, $pendingDays[0]->assignees);

        // التصفية باسم مخصص (خارج السيستم)
        $filteredCustom = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('inquiries.index', ['assignee' => 'n:سائق خارجي 1']));
        $filteredCustom->assertStatus(200);
        $filteredCustom->assertSee('توصيل شحنة المعادي');
        $filteredCustom->assertDontSee('توريد قطع غيار');
        $filteredCustom->assertDontSee('مراجعة عقود الشهر');

        // الموظف العادي يرى كل المهام لجميع الموظفين ويقدر يصفّي بأي موظف
        $staffRes = $this->withSession(['auth_user' => $this->staffUser])
            ->get(route('inquiries.index'));
        $staffRes->assertStatus(200);
        $staffRes->assertSee('مراجعة عقود الشهر');
        $staffRes->assertSee('توصيل شحنة المعادي');
        $staffRes->assertSee('توريد قطع غيار');
        $staffRes->assertSee('إضافة مهام جديدة');
        $staffRes->assertSee('تعديل المهام');

        $staffFiltered = $this->withSession(['auth_user' => $this->staffUser])
            ->get(route('inquiries.index', ['assignee' => 'n:سائق خارجي 1']));
        $staffFiltered->assertStatus(200);
        $staffFiltered->assertSee('توصيل شحنة المعادي');
        $staffFiltered->assertDontSee('توريد قطع غيار');
        $staffFiltered->assertDontSee('مراجعة عقود الشهر');
    }

    public function test_tasks_are_grouped_together_per_day_and_never_mix_across_days(): void
    {
        $day1 = '2026-11-16';
        $day2 = '2026-11-17';

        $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'task_date' => $day1,
                'tasks'     => [
                    ['details' => 'اول مهمه يوم 16', 'assigned_name' => 'احمد يحيي'],
                    ['details' => 'تاني مهمه يوم 16', 'assigned_name' => 'صبري'],
                ],
            ])->assertSessionHas('success');

        $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'task_date' => $day2,
                'tasks'     => [
                    ['details' => 'مهمه يوم 17 لصبري', 'assigned_name' => 'صبري'],
                    ['details' => 'مهمه يوم 17 لكريم', 'assigned_name' => 'كريم'],
                ],
            ])->assertSessionHas('success');

        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('inquiries.index'));
        $res->assertStatus(200);

        $pendingDays = $res->viewData('pendingDays');
        $this->assertCount(2, $pendingDays);

        // أحدث يوم في الأعلى
        $this->assertSame($day2, $pendingDays[0]->date);
        $this->assertSame(2, $pendingDays[0]->total_tasks);
        $this->assertCount(2, $pendingDays[0]->assignees);
        $this->assertSame('صبري', $pendingDays[0]->assignees[0]->assignee_name);
        $this->assertSame('مهمه يوم 17 لصبري', $pendingDays[0]->assignees[0]->tasks[0]->inquiry);
        $this->assertSame('كريم', $pendingDays[0]->assignees[1]->assignee_name);

        // اليوم الأقدم منفصل تماماً ولا يتداخل مع اليوم التالي
        $this->assertSame($day1, $pendingDays[1]->date);
        $this->assertSame(2, $pendingDays[1]->total_tasks);
        $this->assertCount(2, $pendingDays[1]->assignees);
        $this->assertSame('احمد يحيي', $pendingDays[1]->assignees[0]->assignee_name);
        $this->assertSame('اول مهمه يوم 16', $pendingDays[1]->assignees[0]->tasks[0]->inquiry);
        $this->assertSame('صبري', $pendingDays[1]->assignees[1]->assignee_name);
        $this->assertSame('تاني مهمه يوم 16', $pendingDays[1]->assignees[1]->tasks[0]->inquiry);
    }

    public function test_quick_entry_is_default_and_edit_options_are_rendered(): void
    {
        DB::table('customer_inquiries')->insert([
            'customer_name'    => null,
            'inquiry'          => 'مهمة قابلة للتعديل',
            'assigned_to'      => null,
            'assigned_to_name' => 'صبري',
            'task_date'        => '2026-11-18',
            'is_contacted'     => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('inquiries.index'));
        $res->assertStatus(200);

        $html = $res->getContent();
        $this->assertStringContainsString("let currentTaskEntryMode  = 'quick';", $html);
        $this->assertStringContainsString('<button type="button" class="btn-pro btn-pro-sm active" id="btnModeQuick"', $html);
        $this->assertStringContainsString('<div id="sectionDetailed" style="display: none;">', $html);
        $this->assertStringContainsString('<div id="sectionQuick">', $html);
        $this->assertStringContainsString('تعديل المهام', $html);
        $this->assertStringContainsString('title="تعديل المهمة"', $html);
        $this->assertStringContainsString('id="bulkEditTasksModal"', $html);
    }

    public function test_can_bulk_edit_employee_tasks_for_day_and_manager_can_manage_tasks(): void
    {
        $managerId = DB::table('users')->insertGetId([
            'name'      => 'مدير التشغيل',
            'email'     => 'manager@example.com',
            'role'      => 'manager',
            'is_active' => 1,
        ]);
        $managerUser = DB::table('users')->where('id', $managerId)->first();

        $id1 = DB::table('customer_inquiries')->insertGetId([
            'customer_name'    => null,
            'inquiry'          => 'مهمة قديمة 1',
            'assigned_to'      => null,
            'assigned_to_name' => 'صبري',
            'task_date'        => '2026-11-18',
            'is_contacted'     => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $id2 = DB::table('customer_inquiries')->insertGetId([
            'customer_name'    => null,
            'inquiry'          => 'مهمة قديمة 2',
            'assigned_to'      => null,
            'assigned_to_name' => 'صبري',
            'task_date'        => '2026-11-18',
            'is_contacted'     => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // المدير يقدر يعدل جماعياً: يعدّل الأولى، يحذف الثانية، ويضيف مهمة جديدة، ويحولهم لـ أحمد جمال وتاريخ 2026-11-19
        $res = $this->withSession(['auth_user' => $managerUser])
            ->post(route('inquiries.bulk_update'), [
                'original_ids'  => [$id1, $id2],
                'assigned_name' => 'أحمد جمال',
                'task_date'     => '2026-11-19',
                'items'         => [
                    ['id' => $id1, 'details' => 'مهمة معدلة 1', 'deleted' => '0'],
                    ['id' => $id2, 'details' => 'مهمة قديمة 2', 'deleted' => '1'],
                    ['id' => '',   'details' => 'مهمة مضافة أثناء التعديل', 'deleted' => '0'],
                ],
            ]);

        $res->assertRedirect();
        $res->assertSessionHas('success');

        $this->assertNull(DB::table('customer_inquiries')->where('id', $id2)->first());

        $updated = DB::table('customer_inquiries')->where('id', $id1)->first();
        $this->assertSame('مهمة معدلة 1', $updated->inquiry);
        $this->assertEquals($this->staffUser->id, $updated->assigned_to);
        $this->assertSame('أحمد جمال', $updated->assigned_to_name);
        $this->assertSame('2026-11-19', $updated->task_date);

        $added = DB::table('customer_inquiries')->where('inquiry', 'مهمة مضافة أثناء التعديل')->first();
        $this->assertNotNull($added);
        $this->assertEquals($this->staffUser->id, $added->assigned_to);
        $this->assertSame('2026-11-19', $added->task_date);

        // الموظف العادي يقدر يعدل جماعياً أيضاً
        $empRes = $this->withSession(['auth_user' => $this->staffUser])
            ->post(route('inquiries.bulk_update'), [
                'original_ids'  => [$id1],
                'assigned_name' => 'أحمد جمال',
                'task_date'     => '2026-11-19',
                'items'         => [['id' => $id1, 'details' => 'مهمة معدلة بواسطة موظف عادي', 'deleted' => '0']],
            ]);
        $empRes->assertRedirect();
        $empRes->assertSessionHas('success');
        $this->assertSame('مهمة معدلة بواسطة موظف عادي', DB::table('customer_inquiries')->where('id', $id1)->value('inquiry'));
    }

    public function test_can_create_tasks_for_all_employees_at_once_using_quick_blocks(): void
    {
        $res = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'task_date'    => '2026-11-20',
                'quick_blocks' => [
                    [
                        'assigned_name' => 'أحمد جمال',
                        'text'          => "1- تحصيل قسط العميل سمير\n2- تسليم إيصال السداد",
                    ],
                    [
                        'assigned_name' => 'حسن السواق',
                        'text'          => "نقل أجهزة التكييف إلى موقع المعادي\nشحن البنزين لسيارة العمل",
                    ],
                    [
                        'assigned_name' => 'محمود فني تركيبات',
                        'text'          => "معاينة موقع الشيخ زايد وتحديد مسار المواسير",
                    ],
                ],
            ]);

        $res->assertRedirect();
        $res->assertSessionHas('success');

        // مهام أحمد جمال (موظف سيستم)
        $ahmedTasks = DB::table('customer_inquiries')
            ->where('assigned_to', $this->staffUser->id)
            ->where('task_date', '2026-11-20')
            ->pluck('inquiry');
        $this->assertCount(2, $ahmedTasks);
        $this->assertContains('تحصيل قسط العميل سمير', $ahmedTasks);
        $this->assertContains('تسليم إيصال السداد', $ahmedTasks);

        // مهام حسن السواق (موظف HR)
        $hassanTasks = DB::table('customer_inquiries')
            ->where('assigned_to_name', 'حسن السواق')
            ->where('task_date', '2026-11-20')
            ->pluck('inquiry');
        $this->assertCount(2, $hassanTasks);
        $this->assertContains('نقل أجهزة التكييف إلى موقع المعادي', $hassanTasks);
        $this->assertContains('شحن البنزين لسيارة العمل', $hassanTasks);

        // مهام محمود فني تركيبات (موظف مخصص حر)
        $mahmoudTasks = DB::table('customer_inquiries')
            ->where('assigned_to_name', 'محمود فني تركيبات')
            ->where('task_date', '2026-11-20')
            ->pluck('inquiry');
        $this->assertCount(1, $mahmoudTasks);
        $this->assertContains('معاينة موقع الشيخ زايد وتحديد مسار المواسير', $mahmoudTasks);

        // التحقق من تواجد أزرار الإدخال السريع وإزالة الكلمات غير المرغوب فيها وتوافر المشاركة على واتساب
        $viewRes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('inquiries.index'));
        $viewRes->assertStatus(200);
        $html = $viewRes->getContent();

        $this->assertStringContainsString('id="quickBlocksContainer"', $html);
        $this->assertStringContainsString('+ إضافة مهمة / موظف آخر', $html);
        $this->assertStringContainsString('+ إضافة مهمة لنفس الموظف', $html);
        $this->assertStringContainsString('palette', $html);
        $this->assertStringContainsString('shareTasksPosterWhatsApp', $html);
        $this->assertStringContainsString('shareTaskImageWhatsApp', $html);
        $this->assertStringContainsString('إرسال واتساب', $html);
        $this->assertStringContainsString('id="posterWaPhone"', $html);

        // التأكد من إزالة "خارج السيستم" و"الموظف المكلف" و"المكلّف:" نهائياً من الشاشة ومن تصميم الصورة
        $this->assertStringNotContainsString('خارج السيستم', $html);
        $this->assertStringNotContainsString('الموظف المكلف', $html);
        $this->assertStringNotContainsString('الموظف المكلّف', $html);
        $this->assertStringNotContainsString('المكلّف:', $html);
        $this->assertStringNotContainsString('اسم مضاف يدوياً', $html);

        // التأكد من ربط رقم هاتف موظف HR تلقائياً للمشاركة المباشرة على واتساب
        $pendingDays = $viewRes->viewData('pendingDays');
        $hassanGroup = $pendingDays[0]->assignees->firstWhere('assignee_name', 'حسن السواق');
        $this->assertNotNull($hassanGroup);
        $this->assertSame('01099887766', $hassanGroup->assignee_phone);
    }

    public function test_regular_employee_can_view_all_add_for_others_edit_delete_and_update_poster_image(): void
    {
        $day = '2026-11-24';

        // موظفة عادية (صلاحية employee وبشاشة المهام فقط)
        $ayaId = DB::table('users')->insertGetId([
            'name'            => 'آية موظفة',
            'email'           => 'aya@example.com',
            'role'            => 'employee',
            'is_active'       => 1,
            'allowed_screens' => json_encode(['inquiries']),
        ]);
        $ayaUser = DB::table('users')->where('id', $ayaId)->first();

        // موظف غير مسموح له بشاشة المهام (مسموح له sales فقط)
        $blockedEmpId = DB::table('users')->insertGetId([
            'name'            => 'موظف مبيعات فقط',
            'email'           => 'salesonly@example.com',
            'role'            => 'employee',
            'is_active'       => 1,
            'allowed_screens' => json_encode(['sales']),
        ]);
        $blockedEmp = DB::table('users')->where('id', $blockedEmpId)->first();

        // صاحب العمل (الأدمن) أضاف مهام مبدئية لليوم لـ صبري وفاطمة
        $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('inquiries.store'), [
                'task_date'    => $day,
                'quick_blocks' => [
                    ['assigned_name' => 'صبري', 'text' => 'استلام بضاعة المخزن'],
                    ['assigned_name' => 'فاطمة', 'text' => 'مراجعة فواتير اليوم'],
                ],
            ])->assertSessionHas('success');

        // آية (موظفة عادية) تدخل شاشة المهام: ترى كل مهام الجميع + شارة السايدبار بالعدد الكامل (2) + أزرار الإضافة والتعديل والحذف
        $ayaView = $this->withSession(['auth_user' => $ayaUser])
            ->get(route('inquiries.index'));
        $ayaView->assertStatus(200);
        $ayaView->assertSee('استلام بضاعة المخزن');
        $ayaView->assertSee('مراجعة فواتير اليوم');
        $ayaView->assertSee('إضافة مهام جديدة');
        $ayaView->assertSee('openAddTaskForDate', false);
        $ayaView->assertSee('تعديل المهام');
        $ayaView->assertSee('<span class="sb-badge">2</span>', false);

        // آية تضيف مهمة جديدة لـ فاطمه (بتاء مربوطة/هاء) ومهمة جديدة لموظف تاني (أحمد جمال) في نفس اليوم
        $addRes = $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.store'), [
                'task_date'    => $day,
                'quick_blocks' => [
                    ['assigned_name' => 'فاطمه', 'text' => 'الاتصال بالعميل كريم'],
                    ['assigned_name' => 'أحمد جمال', 'text' => 'توصيل العقد الجديد'],
                ],
            ]);
        $addRes->assertRedirect();
        $addRes->assertSessionHas('success');

        // الصورة المجمّعة (عبر endpoint التحديث اللحظي) تتحدث فوراً وتجمع مهام فاطمة/فاطمه تحت نفس الموظفة وتضيف أحمد جمال
        $posterRes = $this->withSession(['auth_user' => $this->adminUser])
            ->getJson(route('inquiries.poster_data'));
        $posterRes->assertStatus(200);
        $this->assertStringContainsString('no-store', (string) $posterRes->headers->get('Cache-Control'));

        $days = $posterRes->json('days');
        $this->assertCount(1, $days);
        $this->assertSame($day, $days[0]['date']);
        $this->assertSame(4, $days[0]['total_tasks']);
        $this->assertSame(3, $days[0]['total_assignees']);

        $fatmaGroup = collect($days[0]['assignees'])->firstWhere('assignee_name', 'فاطمة');
        $this->assertNotNull($fatmaGroup);
        $this->assertCount(2, $fatmaGroup['tasks']);
        $this->assertSame('مراجعة فواتير اليوم', $fatmaGroup['tasks'][0]['details']);
        $this->assertSame('الاتصال بالعميل كريم', $fatmaGroup['tasks'][1]['details']);

        $ahmedGroup = collect($days[0]['assignees'])->firstWhere('assignee_name', 'أحمد جمال');
        $this->assertNotNull($ahmedGroup);
        $this->assertCount(1, $ahmedGroup['tasks']);
        $this->assertSame('توصيل العقد الجديد', $ahmedGroup['tasks'][0]['details']);

        // آية تعدّل مهمة موظف آخر (صبري) مباشرة
        $sabryTaskId = DB::table('customer_inquiries')->where('assigned_to_name', 'صبري')->value('id');
        $editRes = $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.update', $sabryTaskId), [
                'inquiry'          => 'استلام بضاعة المخزن — تعديل آية',
                'assigned_to_name' => 'صبري',
                'task_date'        => $day,
            ]);
        $editRes->assertRedirect();
        $editRes->assertSessionHas('success');
        $this->assertSame('استلام بضاعة المخزن — تعديل آية', DB::table('customer_inquiries')->where('id', $sabryTaskId)->value('inquiry'));

        // آية تؤكد إنجاز مهمة موظف آخر (صبري) فتخرج من صورة المهام المعلقة
        $toggleRes = $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.toggle', $sabryTaskId), [
                'contact_notes' => 'تم الاستلام بنجاح',
            ]);
        $toggleRes->assertRedirect();
        $toggleRes->assertSessionHas('success');
        $this->assertEquals(1, DB::table('customer_inquiries')->where('id', $sabryTaskId)->value('is_contacted'));

        // آية تحذف مهمة لموظف آخر (أحمد جمال) فتختفي من صورة اليوم
        $ahmedTaskId = DB::table('customer_inquiries')->where('inquiry', 'توصيل العقد الجديد')->value('id');
        $delRes = $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.destroy', $ahmedTaskId));
        $delRes->assertRedirect();
        $delRes->assertSessionHas('success');
        $this->assertNull(DB::table('customer_inquiries')->where('id', $ahmedTaskId)->first());

        // التحقق من تحديث بيانات الصورة بعد التعديل والإنجاز والحذف (تبقى مهمتا فاطمة فقط)
        $posterAfter = $this->withSession(['auth_user' => $ayaUser])
            ->getJson(route('inquiries.poster_data'));
        $posterAfter->assertStatus(200);
        $this->assertSame(2, $posterAfter->json('days.0.total_tasks'));
        $this->assertSame(1, $posterAfter->json('days.0.total_assignees'));

        // الموظف المقيّد على شاشة غير المهام يظل ممنوعاً من دخول شاشة المهام أو API الصورة
        $this->withSession(['auth_user' => $blockedEmp])
            ->getJson(route('inquiries.poster_data'))
            ->assertStatus(403);
        $this->withSession(['auth_user' => $blockedEmp])
            ->post(route('inquiries.store'), [
                'task_date' => $day,
                'tasks'     => [['details' => 'مهمة غير مصرحة', 'assigned_name' => 'صبري']],
            ])
            ->assertRedirect(route('sales.index'));
    }

    public function test_tasks_tabs_show_today_and_previous_labels_and_audit_log_tracks_every_task_change(): void
    {
        $day1 = '2026-11-24';
        $day2 = '2026-11-25';

        $ayaId = DB::table('users')->insertGetId([
            'name'            => 'آية موظفة',
            'email'           => 'aya.audit@example.com',
            'role'            => 'employee',
            'is_active'       => 1,
            'allowed_screens' => json_encode(['inquiries']),
        ]);
        $ayaUser = DB::table('users')->where('id', $ayaId)->first();

        // 1) التحقق من أسماء التابات الجديدة في شاشة المهام (مهام اليوم / مهام سابقة)
        $viewRes = $this->withSession(['auth_user' => $ayaUser])
            ->get(route('inquiries.index'));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('<span>مهام اليوم</span>', false);
        $viewRes->assertSee('<span>مهام سابقة</span>', false);
        $viewRes->assertDontSee('<i class="fa fa-hourglass-half me-1"></i> معلقة', false);
        $viewRes->assertDontSee('<i class="fa fa-circle-check me-1"></i> منجزة', false);

        // 2) آية تضيف مهمتين (واحدة للتعديل المفرد وواحدة للتعديل الجماعي والحذف)
        $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.store'), [
                'task_date' => $day1,
                'tasks'     => [
                    ['details' => 'تسليم الفاتورة القديمة', 'assigned_name' => 'صبري'],
                    ['details' => 'مراجعة العهدة اليومية', 'assigned_name' => 'صبري'],
                ],
            ])->assertSessionHas('success');

        $task1Id = DB::table('customer_inquiries')->where('inquiry', 'تسليم الفاتورة القديمة')->value('id');
        $task2Id = DB::table('customer_inquiries')->where('inquiry', 'مراجعة العهدة اليومية')->value('id');

        // 3) آية تعدّل المهمة الأولى (من نص قديم لنص جديد، ومن صبري لأحمد جمال، ومن يوم 24 ليوم 25)
        $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.update', $task1Id), [
                'inquiry'          => 'تسليم الفاتورة المعدلة',
                'assigned_to_name' => 'أحمد جمال',
                'task_date'        => $day2,
            ])->assertSessionHas('success');

        // 4) آية تنقل المهمة الأولى لمهام سابقة ثم ترجعها لمهام اليوم
        $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.toggle', $task1Id), ['contact_notes' => 'تم التسليم'])
            ->assertSessionHas('success');
        $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.toggle', $task1Id))
            ->assertSessionHas('success');

        // 5) أحمد جمال يعدّل جماعياً: يحذف المهمة الثانية ويضيف مهمة جديدة
        $this->withSession(['auth_user' => $this->staffUser])
            ->post(route('inquiries.bulk_update'), [
                'original_ids'  => [$task2Id],
                'assigned_name' => 'صبري',
                'task_date'     => $day1,
                'items'         => [
                    ['id' => $task2Id, 'details' => 'مراجعة العهدة اليومية', 'deleted' => '1'],
                    ['id' => '',       'details' => 'توصيل أوراق البنك', 'deleted' => '0'],
                ],
            ])->assertSessionHas('success');

        // 6) آية تحذف المهمة الأولى مباشرة
        $this->withSession(['auth_user' => $ayaUser])
            ->post(route('inquiries.destroy', $task1Id))
            ->assertSessionHas('success');

        // التأكد من عدم وجود تكرار في audit_logs (كل عملية تسجل مرة واحدة فقط بتفاصيل قبل/بعد)
        $allTaskLogs = DB::table('audit_logs')->where('module', 'inquiries')->orderBy('id')->get();
        // 2 create + 1 update + 1 complete + 1 reopen + 1 bulk-create + 1 bulk-delete + 1 destroy = 8
        $this->assertCount(8, $allTaskLogs);

        // التحقق من سجل التعديل: من إيه لإيه
        $updateLog = $allTaskLogs->firstWhere('action', 'update');
        $this->assertNotNull($updateLog);
        $this->assertEquals($ayaUser->id, $updateLog->user_id);
        $this->assertSame('آية موظفة', $updateLog->user_name);
        $this->assertSame('warning', $updateLog->severity);
        $oldVals = json_decode($updateLog->old_values, true);
        $newVals = json_decode($updateLog->new_values, true);
        $this->assertSame('تسليم الفاتورة القديمة', $oldVals['المهمة']);
        $this->assertSame('تسليم الفاتورة المعدلة', $newVals['المهمة']);
        $this->assertSame('صبري', $oldVals['الموظف']);
        $this->assertSame('أحمد جمال', $newVals['الموظف']);
        $this->assertSame($day1, $oldVals['تاريخ القائمة']);
        $this->assertSame($day2, $newVals['تاريخ القائمة']);

        // التحقق من سجلي الحذف (حذف آية + حذف أحمد جمال داخل التعديل الجماعي)
        $deleteLogs = $allTaskLogs->where('action', 'delete')->values();
        $this->assertCount(2, $deleteLogs);
        foreach ($deleteLogs as $dl) {
            $this->assertSame('critical', $dl->severity);
            $this->assertNull($dl->new_values);
            $this->assertNotNull($dl->old_values);
        }
        $bulkDelLog = $deleteLogs->firstWhere('user_id', $this->staffUser->id);
        $this->assertNotNull($bulkDelLog);
        $this->assertSame('مراجعة العهدة اليومية', json_decode($bulkDelLog->old_values, true)['المهمة']);

        $singleDelLog = $deleteLogs->firstWhere('user_id', $ayaUser->id);
        $this->assertNotNull($singleDelLog);
        $this->assertSame('تسليم الفاتورة المعدلة', json_decode($singleDelLog->old_values, true)['المهمة']);

        // 7) شاشة سجل التدقيق: الفلترة بقسم المهام + الفلترة باليوزر + الفلترة بالتعديل/الحذف + البحث بالنص القديم
        $auditAllTasks = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('audit.index', ['module' => 'inquiries', 'period' => 'all']));
        $auditAllTasks->assertStatus(200);
        $auditAllTasks->assertSee('سجل المهام');
        $auditAllTasks->assertSee('تسليم الفاتورة القديمة');
        $auditAllTasks->assertSee('تسليم الفاتورة المعدلة');
        $auditAllTasks->assertSee('مراجعة العهدة اليومية');
        $auditAllTasks->assertSee('آية موظفة');
        $auditAllTasks->assertSee('أحمد جمال');

        // فلترة بالتعديل لآية فقط
        $auditAyaEdits = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('audit.index', ['module' => 'inquiries', 'action' => 'update', 'user_id' => $ayaUser->id, 'period' => 'all']));
        $auditAyaEdits->assertStatus(200);
        $this->assertCount(1, $auditAyaEdits->viewData('logs'));
        $auditAyaEdits->assertSee('تسليم الفاتورة القديمة');
        $auditAyaEdits->assertSee('تسليم الفاتورة المعدلة');

        // فلترة بالمحذوفات فقط
        $auditDeletes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('audit.index', ['module' => 'inquiries', 'action' => 'delete', 'period' => 'all']));
        $auditDeletes->assertStatus(200);
        $this->assertCount(2, $auditDeletes->viewData('logs'));
        $auditDeletes->assertSee('تفاصيل السجل المحذوف');

        // البحث بالنص القديم للمهمة المعدلة يجد السجل
        $auditSearch = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('audit.index', ['module' => 'inquiries', 'search' => 'الفاتورة القديمة', 'period' => 'all']));
        $auditSearch->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $auditSearch->viewData('logs')->count());

        // التحقق من API تفاصيل السجل (/audit-log/{id})
        $detailJson = $this->withSession(['auth_user' => $this->adminUser])
            ->getJson(route('audit.show', $updateLog->id));
        $detailJson->assertStatus(200)
            ->assertJsonPath('module_label', 'المهام')
            ->assertJsonPath('action_label', 'تعديل')
            ->assertJsonPath('role_label', 'موظف')
            ->assertJsonPath('old_values.المهمة', 'تسليم الفاتورة القديمة')
            ->assertJsonPath('new_values.المهمة', 'تسليم الفاتورة المعدلة');
    }
}
