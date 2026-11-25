<?php

namespace Tests\Feature;

use App\Models\Jamia;
use App\Models\JamiaInstallment;
use App\Models\JamiaShare;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JamiaCollectionFreezeAndSpeedTest extends TestCase
{
    protected object $adminUser;
    protected int $accountId;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        foreach ([
            'installment_payments', 'installments', 'partners', 'company_debts',
            'jamia_installments', 'jamia_shares', 'jamias',
            'financial_transactions', 'accounts', 'activity_logs',
            'customer_inquiries', 'users',
        ] as $tbl) {
            Schema::dropIfExists($tbl);
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
            $table->string('type');
            $table->string('subtype', 50)->nullable();
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->unsignedBigInteger('to_account_id')->nullable();
            $table->string('ref_type')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
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

        Schema::create('customer_inquiries', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_contacted')->default(0);
            $table->timestamps();
        });

        (require database_path('migrations/2026_11_04_220000_create_jamias_system_tables.php'))->up();
        (require database_path('migrations/2026_11_13_000000_convert_jamias_to_organizer_model.php'))->up();
        (require database_path('migrations/2026_11_18_193000_add_share_fraction_to_jamia_shares_table.php'))->up();

        $userId = DB::table('users')->insertGetId([
            'name'      => 'مدير النظام',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'admin',
            'is_active' => 1,
        ]);
        $this->adminUser = DB::table('users')->where('id', $userId)->first();

        $this->accountId = DB::table('accounts')->insertGetId([
            'account_name' => 'الخزنة الرئيسية',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 100000,
        ]);
    }

    private function asAdmin()
    {
        return $this->withSession([
            'auth_user'       => $this->adminUser,
            'auth_last_check' => time(),
        ]);
    }

    public function test_bulk_collection_batches_queries_logs_valid_enum_action_and_syncs_cancellations_without_n_plus_one(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00:00'));

        $members = [];
        for ($i = 1; $i <= 10; $i++) {
            $members[] = $i === 1
                ? ['turn' => 1, 'name' => 'شركة الضبع', 'is_company' => 1]
                : ['turn' => $i, 'name' => "عضو رقم {$i}", 'phone' => "0100000000{$i}"];
        }

        $this->asAdmin()->post(route('cashflows.store'), [
            'name'                 => 'جمعية سرعة التحصيل',
            'total_amount'         => 10000,
            'duration_months'      => 10,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 10,
            'members'              => $members,
        ])->assertSessionHas('success');

        $jamia = Jamia::firstOrFail();

        // تحصيل جماعي لـ 9 أعضاء في شهر نوفمبر
        $novMemberInsts = JamiaInstallment::where('jamia_id', $jamia->id)
            ->whereDate('month_date', '2026-11-01')
            ->whereHas('share', fn ($q) => $q->where('is_company', false))
            ->get();
        $this->assertCount(9, $novMemberInsts);

        $this->asAdmin()->post(route('cashflows.collect'), [
            'installment_ids' => $novMemberInsts->pluck('id')->all(),
            'account_id'      => $this->accountId,
            'paid_at'         => '2026-11-05',
        ])->assertSessionHas('success');

        // الرصيد زاد 9 × 1,000 = 9,000 (100,000 -> 109,000)
        $this->assertEquals(109000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // نشاط التحصيل سُجل بقيمة ENUM صالحة (payment وليس collect)
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'jamia',
            'action' => 'payment',
        ]);

        // إلغاء حركة واحدة مباشرة في financial_transactions والتحقق من مزامنتها بـ JOIN واحد بدون N+1
        $firstPaid = $novMemberInsts->first()->fresh();
        DB::table('financial_transactions')
            ->where('id', $firstPaid->tx_id)
            ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $jamiaQueryCount = 0;
        DB::listen(function ($query) use (&$jamiaQueryCount) {
            if (preg_match('/jamia_|jamias|financial_transactions/i', $query->sql)) {
                $jamiaQueryCount++;
            }
        });

        $res = $this->asAdmin()->get(route('cashflows.index', ['month' => '2026-11']));
        $res->assertOk();

        // القسط الملغي رجع تلقائياً لغير مدفوع
        $this->assertFalse($firstPaid->fresh()->is_paid);
        $this->assertNull($firstPaid->fresh()->tx_id);

        // عدد استعلامات الجمعيات يظل محدوداً وثابتاً ولا يتضاعف مع عدد الأقساط المدفوعة (بدون N+1)
        $this->assertLessThanOrEqual(18, $jamiaQueryCount);

        Carbon::setTestNow();
    }

    public function test_jamia_modals_render_outside_hidden_tabs_and_avoid_duplicate_blocking_swal(): void
    {
        $this->asAdmin()->post(route('cashflows.store'), [
            'name'                 => 'جمعية فحص المودال',
            'total_amount'         => 6000,
            'duration_months'      => 2,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 10,
            'members'              => [
                ['turn' => 1, 'name' => 'أحمد'],
                ['turn' => 2, 'name' => 'محمود'],
            ],
        ])->assertSessionHas('success');

        $jamia = Jamia::firstOrFail();

        $res = $this->asAdmin()
            ->withSession(['success' => 'تم تحصيل 1 قسط بنجاح'])
            ->get(route('cashflows.index', ['month' => '2026-11']));

        $res->assertOk();
        $html = $res->getContent();

        $tabMonthPos   = strpos($html, 'id="tab-month"');
        $tabOverduePos = strpos($html, 'id="tab-overdue"');
        $monthModalPos = strpos($html, 'id="monthJamiaModal-' . $jamia->id . '"');
        $dirModalPos   = strpos($html, 'id="dirJamiaModal-' . $jamia->id . '"');

        $this->assertNotFalse($tabMonthPos);
        $this->assertNotFalse($tabOverduePos);
        $this->assertNotFalse($monthModalPos);
        $this->assertNotFalse($dirModalPos);

        // المودالات لازم تكون خارج التابات المخفية (بعد tab-overdue في قسم MODALS) لمنع تجمّد الشاشة خلف backdrop
        $this->assertGreaterThan($tabOverduePos, $monthModalPos, 'monthJamiaModal must be placed outside hidden tab panes');
        $this->assertGreaterThan($tabOverduePos, $dirModalPos, 'dirJamiaModal must be placed outside hidden tab panes');

        // لا يوجد استدعاء Swal.fire المكرر الحاجب داخل cashflows.blade.php لـ session('success')
        $this->assertStringNotContainsString("Swal.fire({ icon: 'success', title: 'تمت العملية بنجاح'", $html);

        // وجود حماية تنظيف الـ backdrop وإعادة فتح التاب الصحيح
        $this->assertStringContainsString('cleanupOrphanModalBackdrops', $html);
        $this->assertStringContainsString("reopenId.startsWith('monthJamiaModal-')", $html);
    }

    public function test_partial_payouts_enforce_share_cap_allow_installments_and_support_single_tranche_cancellation(): void
    {
        // رصيد الخزنة يكفي صرف 300 ألف على دفعات
        DB::table('accounts')->where('id', $this->accountId)->update(['balance' => 400000]);

        $secondAccId = DB::table('accounts')->insertGetId([
            'account_name' => 'محفظة المعرض',
            'category'     => 'bank_wallet',
            'status'       => 'active',
            'balance'      => 300000,
        ]);

        $members = [];
        for ($i = 1; $i <= 3; $i++) {
            $members[] = ['turn' => $i, 'name' => "مشترك {$i}"];
        }

        $this->asAdmin()->post(route('cashflows.store'), [
            'name'                 => 'جمعية 300 ألف',
            'total_amount'         => 300000,
            'duration_months'      => 3,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 10,
            'members'              => $members,
        ])->assertSessionHas('success');

        $jamia  = Jamia::firstOrFail();
        $share1 = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 1)->firstOrFail();

        // 1) رفض صرف مبلغ أكبر من نصيب صاحب الدور (300,001 > 300,000)
        $this->asAdmin()->post(route('cashflows.payout'), [
            'share_id'   => $share1->id,
            'account_id' => $this->accountId,
            'amount'     => 300001,
        ])->assertSessionHas('error');
        $this->assertEquals(0.0, (float) $share1->fresh()->collected_amount);
        $this->assertFalse($share1->fresh()->is_collected);

        // 2) صرف دفعة أولى 50,000 من الخزنة الرئيسية -> الدور يظل مفتوحاً (باقي 250,000)
        $this->asAdmin()->post(route('cashflows.payout'), [
            'share_id'   => $share1->id,
            'account_id' => $this->accountId,
            'amount'     => 50000,
            'paid_at'    => '2026-11-05',
        ])->assertSessionHas('success');

        $share1->refresh();
        $this->assertFalse($share1->is_collected, 'Partial payout (50k/300k) must NOT close the turn');
        $this->assertEquals(50000.0, (float) $share1->collected_amount);
        $this->assertEquals(350000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
        $tx1Id = $share1->collected_tx_id;
        $this->assertNotNull($tx1Id);

        // موقف الجمعيات في الخزانة يحسب الدفعة الجزئية (50,000) فوراً
        $pos = \App\Services\InstallmentFinanceService::jamiaPositionSummary();
        $this->assertEquals(50000.0, $pos['payouts_to_members']);

        // الشاشة تعرض "صرف جزئي" وزر استكمال الصرف مع المتبقي 250,000
        $res = $this->asAdmin()->get(route('cashflows.index', ['month' => '2026-11']));
        $res->assertOk();
        $res->assertSee('صرف دفعة / المتبقي');
        $res->assertSee('250,000.00');
        $res->assertSee('50,000.00');

        // 3) فاليديشن: رفض صرف 260,000 لأن المتبقي 250,000 فقط
        $this->asAdmin()->post(route('cashflows.payout'), [
            'share_id'   => $share1->id,
            'account_id' => $secondAccId,
            'amount'     => 260000,
        ])->assertSessionHas('error');
        $this->assertEquals(50000.0, (float) $share1->fresh()->collected_amount);
        $this->assertEquals(300000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));

        // 4) صرف دفعة ثانية 100,000 من محفظة المعرض -> الإجمالي 150,000 والمتبقي 150,000
        $this->asAdmin()->post(route('cashflows.payout'), [
            'share_id'   => $share1->id,
            'account_id' => $secondAccId,
            'amount'     => 100000,
            'paid_at'    => '2026-11-08',
        ])->assertSessionHas('success');

        $share1->refresh();
        $this->assertFalse($share1->is_collected);
        $this->assertEquals(150000.0, (float) $share1->collected_amount);
        $this->assertEquals(200000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));
        $tx2Id = $share1->collected_tx_id;
        $this->assertNotEquals($tx1Id, $tx2Id);

        // 5) إلغاء الدفعة الثانية فقط (tx2 = 100,000) -> ترجع لمحفظة المعرض وتفضل الدفعة الأولى 50,000 قائمة
        $this->asAdmin()->post(route('cashflows.cancel_payout'), [
            'share_id' => $share1->id,
            'tx_id'    => $tx2Id,
        ])->assertSessionHas('success');

        $share1->refresh();
        $this->assertFalse($share1->is_collected);
        $this->assertEquals(50000.0, (float) $share1->collected_amount);
        $this->assertEquals($tx1Id, $share1->collected_tx_id);
        $this->assertEquals(300000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));

        // 6) صرف المتبقي بالكامل (250,000) -> يكتمل الدور ويصبح is_collected = true
        $this->asAdmin()->post(route('cashflows.payout'), [
            'share_id'   => $share1->id,
            'account_id' => $secondAccId,
            'amount'     => 250000,
            'paid_at'    => '2026-11-10',
        ])->assertSessionHas('success');

        $share1->refresh();
        $this->assertTrue($share1->is_collected);
        $this->assertEquals(300000.0, (float) $share1->collected_amount);
        $this->assertEquals(50000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));

        // 7) رفض أي صرف إضافي بعد اكتمال نصيب الدور
        $this->asAdmin()->post(route('cashflows.payout'), [
            'share_id'   => $share1->id,
            'account_id' => $this->accountId,
            'amount'     => 100,
        ])->assertSessionHas('error');

        // 8) إلغاء دفعة واحدة من سجل العمليات (OperationsLogController) يحافظ على باقي الدفعات ولا يصفر الدور بالكامل
        $txFinalId = $share1->collected_tx_id;
        $this->asAdmin()->post(route('operations.destroy', ['editor' => 'expense', 'id' => $txFinalId]), [
            'admin_pin' => 'secret123',
        ])->assertSessionHas('success');

        $share1->refresh();
        $this->assertFalse($share1->is_collected);
        $this->assertEquals(50000.0, (float) $share1->collected_amount);
        $this->assertEquals($tx1Id, $share1->collected_tx_id);
        $this->assertEquals(300000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));

        // 9) إلغاء كل دفعات الدور دفعة واحدة يعيد الدفعة المتبقية (50,000) للخزنة الرئيسية
        $this->asAdmin()->post(route('cashflows.cancel_payout'), [
            'share_id' => $share1->id,
        ])->assertSessionHas('success');

        $share1->refresh();
        $this->assertFalse($share1->is_collected);
        $this->assertEquals(0.0, (float) $share1->collected_amount);
        $this->assertNull($share1->collected_tx_id);
        $this->assertEquals(400000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
        $this->assertEquals(300000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));
    }

    public function test_legacy_closed_partial_payout_is_automatically_unlocked_for_remaining_balance(): void
    {
        $this->asAdmin()->post(route('cashflows.store'), [
            'name'                 => 'جمعية تصحيح قديم',
            'total_amount'         => 300000,
            'duration_months'      => 2,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 10,
            'members'              => [
                ['turn' => 1, 'name' => 'أحمد أبو سريع'],
                ['turn' => 2, 'name' => 'محمود'],
            ],
        ])->assertSessionHas('success');

        $share = JamiaShare::where('share_number', 1)->firstOrFail();

        // محاكاة الحالة القديمة: اتصرف 50,000 فقط واتقفل الدور بالخطأ بـ is_collected = true
        $txId = DB::table('financial_transactions')->insertGetId([
            'type'            => 'expense',
            'subtype'         => 'jamia_payout_out',
            'amount'          => 50000,
            'from_account_id' => $this->accountId,
            'ref_type'        => 'jamia_payout_out',
            'ref_id'          => $share->id,
            'status'          => 'active',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        DB::table('jamia_shares')->where('id', $share->id)->update([
            'is_collected'         => true,
            'collected_amount'     => 50000,
            'collected_tx_id'      => $txId,
            'collected_account_id' => $this->accountId,
            'collected_at'         => now(),
        ]);

        // مجرد فتح الشاشة يصحّح الحالة تلقائياً ويفتح إمكانية صرف المتبقي (250,000)
        $res = $this->asAdmin()->get(route('cashflows.index', ['month' => '2026-11']));
        $res->assertOk();
        $res->assertSee('صرف دفعة / المتبقي');
        $res->assertSee('250,000.00');

        $share->refresh();
        $this->assertFalse($share->is_collected);
        $this->assertEquals(50000.0, (float) $share->collected_amount);
    }
}
