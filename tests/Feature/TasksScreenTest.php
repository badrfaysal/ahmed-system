<?php

namespace Tests\Feature;

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

        // الموظف العادي يرى مهامه فقط
        $staffRes = $this->withSession(['auth_user' => $this->staffUser])
            ->get(route('inquiries.index'));
        $staffRes->assertStatus(200);
        $staffRes->assertSee('مراجعة عقود الشهر');
        $staffRes->assertDontSee('توصيل شحنة المعادي');
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

        // الموظف العادي ممنوع من التعديل
        $empRes = $this->withSession(['auth_user' => $this->staffUser])
            ->post(route('inquiries.bulk_update'), [
                'original_ids'  => [$id1],
                'assigned_name' => 'أحمد جمال',
                'items'         => [['id' => $id1, 'details' => 'محاولة اختراق', 'deleted' => '0']],
            ]);
        $empRes->assertStatus(302); // RestrictEmployeeActions أو 403
        $this->assertSame('مهمة معدلة 1', DB::table('customer_inquiries')->where('id', $id1)->value('inquiry'));
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

        // التحقق من تواجد أزرار الإدخال السريع وبادجات الصورة الملونة في الشاشة
        $viewRes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('inquiries.index'));
        $viewRes->assertStatus(200);
        $html = $viewRes->getContent();

        $this->assertStringContainsString('id="quickBlocksContainer"', $html);
        $this->assertStringContainsString('+ إضافة مهمة / موظف آخر', $html);
        $this->assertStringContainsString('+ إضافة مهمة لنفس الموظف', $html);
        $this->assertStringContainsString('المكلّف:', $html);
        $this->assertStringContainsString('palette', $html);
    }
}
