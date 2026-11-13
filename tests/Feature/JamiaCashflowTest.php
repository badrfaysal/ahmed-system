<?php

namespace Tests\Feature;

use App\Http\Controllers\JamiaController;
use App\Models\Jamia;
use App\Models\JamiaInstallment;
use App\Models\JamiaShare;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JamiaCashflowTest extends TestCase
{
    protected int $accountId;
    protected object $devUser;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        Schema::dropIfExists('jamia_installments');
        Schema::dropIfExists('jamia_shares');
        Schema::dropIfExists('jamias');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('customer_inquiries');
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

        // Run the actual Jamia migrations
        $m1 = require database_path('migrations/2026_11_04_220000_create_jamias_system_tables.php');
        $m1->up();
        $m2 = require database_path('migrations/2026_11_13_000000_convert_jamias_to_organizer_model.php');
        $m2->up();

        $userId = DB::table('users')->insertGetId([
            'name'      => 'Developer',
            'email'     => 'dev@example.com',
            'password'  => \Illuminate\Support\Facades\Hash::make('secret123'),
            'role'      => 'admin',
            'is_active' => 1,
        ]);
        $this->devUser = DB::table('users')->where('id', $userId)->first();

        $this->accountId = DB::table('accounts')->insertGetId([
            'account_name' => 'الخزنة الرئيسية',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 50000,
        ]);
    }

    private function asDev()
    {
        return $this->withSession([
            'auth_user'       => $this->devUser,
            'auth_last_check' => time(),
        ]);
    }

    public function test_create_jamia_validates_max_collection_day_10(): void
    {
        $res = $this->asDev()->post(route('cashflows.store'), [
            'name'                 => 'جمعية اختبار',
            'total_amount'         => 30000,
            'duration_months'      => 3,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 15, // exceeds max 10
            'members'              => [
                ['turn' => 1, 'name' => 'شركة الضبع', 'is_company' => 1],
                ['turn' => 2, 'name' => 'أحمد علي'],
                ['turn' => 3, 'name' => 'محمود حسن'],
            ],
        ]);

        $res->assertSessionHasErrors('collection_end_day');
        $this->assertDatabaseCount('jamias', 0);
    }

    public function test_full_organizer_lifecycle_collection_payout_member_swap_and_views(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-13 12:00:00'));

        // 1. Create a 3-month jamia with 1 company share (defaults to شركة الضبع) and 2 external members
        $res = $this->asDev()->post(route('cashflows.store'), [
            'name'                 => 'جمعية 30 ألف - نوفمبر',
            'total_amount'         => 30000,
            'duration_months'      => 3,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 10,
            'members'              => [
                ['turn' => 1, 'name' => '', 'is_company' => 1], // should default to شركة الضبع
                ['turn' => 2, 'name' => 'أحمد علي', 'phone' => '01001112223'],
                ['turn' => 3, 'name' => 'محمود حسن', 'phone' => '01004445556'],
            ],
        ]);
        $res->assertSessionHasNoErrors();
        $res->assertSessionHas('success');

        $jamia = Jamia::firstOrFail();
        $this->assertSame(JamiaController::COMPANY_NAME, $jamia->organizer_name);
        $this->assertSame(1, (int) $jamia->company_shares_count);
        $this->assertSame(1, (int) $jamia->collection_start_day);
        $this->assertSame(10, (int) $jamia->collection_end_day);
        $this->assertEquals(10000.0, (float) $jamia->monthly_installment);

        $companyShare = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 1)->firstOrFail();
        $this->assertTrue($companyShare->is_company);
        $this->assertSame(JamiaController::COMPANY_NAME, $companyShare->share_label);

        $ahmedShare = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 2)->firstOrFail();
        $mahmoudShare = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 3)->firstOrFail();
        $this->assertFalse($ahmedShare->is_company);

        // 2. Render index for 2026-11 (day 13 > end_day 10 => members are late!)
        $indexRes = $this->asDev()->get(route('cashflows.index', ['month' => '2026-11']));
        $indexRes->assertOk();
        $indexRes->assertSee('جمعية 30 ألف - نوفمبر');
        $indexRes->assertSee('أحمد علي');
        $indexRes->assertSee('محمود حسن');
        $indexRes->assertSee('متأخر 3 يوم');

        // 3. Collect Ahmed's Nov installment on time (paid_at = 2026-11-05) and Mahmoud's late (paid_at = 2026-11-13)
        $ahmedNov = JamiaInstallment::where('share_id', $ahmedShare->id)->whereDate('month_date', '2026-11-01')->firstOrFail();
        $mahmoudNov = JamiaInstallment::where('share_id', $mahmoudShare->id)->whereDate('month_date', '2026-11-01')->firstOrFail();

        $this->asDev()->post(route('cashflows.collect'), [
            'installment_ids' => [$ahmedNov->id],
            'account_id'      => $this->accountId,
            'paid_at'         => '2026-11-05',
        ])->assertSessionHas('success');

        $this->asDev()->post(route('cashflows.collect'), [
            'installment_ids' => [$mahmoudNov->id],
            'account_id'      => $this->accountId,
            'paid_at'         => '2026-11-13',
        ])->assertSessionHas('success');

        // Account started at 50,000 + 10,000 + 10,000 = 70,000
        $this->assertEquals(70000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
        $this->assertDatabaseHas('financial_transactions', [
            'type'     => 'income',
            'ref_type' => 'jamia_collection',
            'ref_id'   => $ahmedNov->id,
            'status'   => 'active',
        ]);

        // 4. Company receipt REQUIRES mandatory account_id and credits that safe
        $this->asDev()->post(route('cashflows.payout'), [
            'share_id' => $companyShare->id,
        ])->assertSessionHasErrors(['account_id', 'amount']);
        $this->assertFalse($companyShare->fresh()->is_collected);

        $this->asDev()->post(route('cashflows.payout'), [
            'share_id'   => $companyShare->id,
            'account_id' => $this->accountId,
            'amount'     => 30000,
            'paid_at'    => '2026-11-10',
        ])->assertSessionHas('success');
        $this->assertTrue($companyShare->fresh()->is_collected);
        $this->assertEquals(100000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // 5. Payout Ahmed's turn 2 (30,000 deducted from account -> 70,000)
        $this->asDev()->post(route('cashflows.payout'), [
            'share_id'   => $ahmedShare->id,
            'account_id' => $this->accountId,
            'amount'     => 30000,
            'paid_at'    => '2026-12-10',
        ])->assertSessionHas('success');
        $this->assertEquals(70000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // 6. Cancel Ahmed's payout => balance returns to 100,000
        $this->asDev()->post(route('cashflows.cancel_payout'), [
            'share_id' => $ahmedShare->id,
        ])->assertSessionHas('success');
        $this->assertFalse($ahmedShare->fresh()->is_collected);
        $this->assertEquals(100000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // 7. Update member: swap Ahmed (turn 2) and Mahmoud (turn 3)
        $this->asDev()->post(route('cashflows.update_member'), [
            'share_id' => $ahmedShare->id,
            'name'     => 'أحمد علي المعدل',
            'phone'    => '01099998888',
            'turn'     => 3,
        ])->assertSessionHas('success');
        $this->assertSame(3, (int) $ahmedShare->fresh()->share_number);
        $this->assertSame('2027-01-01', $ahmedShare->fresh()->payout_month->toDateString());
        $this->assertSame(2, (int) $mahmoudShare->fresh()->share_number);
        $this->assertSame('2026-12-01', $mahmoudShare->fresh()->payout_month->toDateString());

        // 8. Cancel Mahmoud's Nov collection => balance returns to 90,000
        $this->asDev()->post(route('cashflows.cancel_payment'), [
            'installment_id' => $mahmoudNov->id,
        ])->assertSessionHas('success');
        $this->assertFalse($mahmoudNov->fresh()->is_paid);
        $this->assertEquals(90000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // 9. Update Jamia collection window (1..8)
        $this->asDev()->post(route('cashflows.update_jamia'), [
            'jamia_id'             => $jamia->id,
            'name'                 => 'جمعية 30 ألف - معدلة',
            'collection_start_day' => 1,
            'collection_end_day'   => 8,
            'status'               => 'active',
        ])->assertSessionHas('success');
        $this->assertSame(8, (int) $jamia->fresh()->collection_end_day);

        // 10. Render both print views
        $this->asDev()->get(route('cashflows.print_month', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('جمعية 30 ألف - معدلة')
            ->assertSee('أحمد علي المعدل');

        $this->asDev()->get(route('cashflows.print_jamia', $jamia->id))
            ->assertOk()
            ->assertSee('جمعية 30 ألف - معدلة')
            ->assertSee('جدول متابعة التحصيل الشهري');

        Carbon::setTestNow();
    }

    public function test_bulk_collection_and_operations_log_reversal(): void
    {
        $this->asDev()->post(route('cashflows.store'), [
            'name'                 => 'جمعية تحصيل جماعي',
            'total_amount'         => 20000,
            'duration_months'      => 2,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 10,
            'members'              => [
                ['turn' => 1, 'name' => 'عميل أول'],
                ['turn' => 2, 'name' => 'عميل ثاني'],
            ],
        ])->assertSessionHas('success');

        $jamia = Jamia::firstOrFail();
        $share1 = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 1)->firstOrFail();
        $novInsts = JamiaInstallment::where('jamia_id', $jamia->id)
            ->whereDate('month_date', '2026-11-01')
            ->orderBy('id')
            ->get();
        $this->assertCount(2, $novInsts);

        // Bulk collect both installments at once (10,000 + 10,000 = +20,000 -> 70,000)
        $this->asDev()->post(route('cashflows.collect'), [
            'installment_ids' => $novInsts->pluck('id')->all(),
            'account_id'      => $this->accountId,
            'paid_at'         => '2026-11-05',
        ])->assertSessionHas('success');

        $this->assertEquals(70000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
        $firstInst = $novInsts->first()->fresh();
        $this->assertTrue($firstInst->is_paid);
        $this->assertNotNull($firstInst->tx_id);

        // Payout share 1 (-20,000 -> 50,000)
        $this->asDev()->post(route('cashflows.payout'), [
            'share_id'   => $share1->id,
            'account_id' => $this->accountId,
            'amount'     => 20000,
            'paid_at'    => '2026-11-06',
        ])->assertSessionHas('success');

        $this->assertEquals(50000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
        $payoutTxId = $share1->fresh()->collected_tx_id;
        $this->assertNotNull($payoutTxId);

        // Reverse one collection tx via OperationsLogController (-10,000 -> 40,000)
        $this->asDev()->post(route('operations.destroy', ['editor' => 'financial', 'id' => $firstInst->tx_id]), [
            'admin_pin' => 'secret123',
        ])->assertSessionHas('success');

        $this->assertFalse($firstInst->fresh()->is_paid);
        $this->assertEquals(40000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // Reverse payout expense tx via OperationsLogController (+20,000 -> 60,000)
        $this->asDev()->post(route('operations.destroy', ['editor' => 'expense', 'id' => $payoutTxId]), [
            'admin_pin' => 'secret123',
        ])->assertSessionHas('success');

        $this->assertFalse($share1->fresh()->is_collected);
        $this->assertEquals(60000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
    }

    public function test_company_receipt_requires_safe_and_transfer_13_of_15_with_claim_recovery_validations(): void
    {
        $secondAccId = DB::table('accounts')->insertGetId([
            'account_name' => 'خزنة المعرض',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 10000,
        ]);

        // Create 15-member Jamia (15,000 payout, 1,000/month), company owns turn 1 and turn 13
        $members = [];
        for ($i = 1; $i <= 15; $i++) {
            $members[] = ($i === 1 || $i === 13)
                ? ['turn' => $i, 'name' => 'شركة الضبع', 'is_company' => 1]
                : ['turn' => $i, 'name' => "عضو رقم {$i}"];
        }

        $this->asDev()->post(route('cashflows.store'), [
            'name'                 => 'جمعية 15 اسم - الضبع',
            'total_amount'         => 15000,
            'duration_months'      => 15,
            'start_date'           => '2026-11-01',
            'collection_start_day' => 1,
            'collection_end_day'   => 10,
            'members'              => $members,
        ])->assertSessionHas('success');

        $jamia = Jamia::where('name', 'جمعية 15 اسم - الضبع')->firstOrFail();
        $companyTurn1  = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 1)->firstOrFail();
        $companyTurn13 = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 13)->firstOrFail();
        $memberTurn2   = JamiaShare::where('jamia_id', $jamia->id)->where('share_number', 2)->firstOrFail();

        // 1. Company receipt validation: amount > payout_amount is blocked
        $this->asDev()->post(route('cashflows.payout'), [
            'share_id'   => $companyTurn1->id,
            'account_id' => $this->accountId,
            'amount'     => 25000, // exceeds 15,000
        ])->assertSessionHas('error');
        $this->assertFalse($companyTurn1->fresh()->is_collected);

        // 2. Valid company receipt deposits 15,000 into selected safe (50,000 -> 65,000)
        $this->asDev()->post(route('cashflows.payout'), [
            'share_id'   => $companyTurn1->id,
            'account_id' => $this->accountId,
            'amount'     => 15000,
            'paid_at'    => '2026-11-10',
        ])->assertSessionHas('success');

        $companyTurn1->refresh();
        $this->assertTrue($companyTurn1->is_collected);
        $this->assertEquals($this->accountId, $companyTurn1->collected_account_id);
        $this->assertEquals(65000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));
        $this->assertDatabaseHas('financial_transactions', [
            'id'       => $companyTurn1->collected_tx_id,
            'type'     => 'income',
            'ref_type' => 'jamia_share',
            'status'   => 'active',
        ]);

        // Reversing company receipt via OperationsLogController deducts back from safe (65,000 -> 50,000)
        $this->asDev()->post(route('operations.destroy', ['editor' => 'financial', 'id' => $companyTurn1->collected_tx_id]), [
            'admin_pin' => 'secret123',
        ])->assertSessionHas('success');
        $this->assertFalse($companyTurn1->fresh()->is_collected);
        $this->assertEquals(50000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance'));

        // 3. Transfer validations
        // 3a) Non-company share cannot be transferred
        $this->asDev()->post(route('cashflows.transfer'), [
            'share_id'                    => $memberTurn2->id,
            'transferred_to_name'         => 'شخص خارجي',
            'transferred_from_month'      => '2027-02-01',
            'paid_months_before_transfer' => 3,
        ])->assertSessionHas('error');

        // 3b) Over-counting months (13 paid + 15 remaining from month 1 > 15) must fail
        $this->asDev()->post(route('cashflows.transfer'), [
            'share_id'                    => $companyTurn13->id,
            'transferred_to_name'         => 'الحاج سيد',
            'transferred_from_month'      => '2026-11-01', // month 1 => 15 remaining
            'paid_months_before_transfer' => 13,
        ])->assertSessionHas('error');
        $this->assertFalse($companyTurn13->fresh()->is_transferred);

        // 3c) Valid 13-of-15 transfer: replace company turn 13 starting month 14 (2027-12-01), company covered 13 names
        $this->asDev()->post(route('cashflows.transfer'), [
            'share_id'                    => $companyTurn13->id,
            'transferred_to_name'         => 'الحاج سيد',
            'member_phone'                => '01012345678',
            'transferred_from_month'      => '2027-12-01',
            'paid_months_before_transfer' => 13,
            'transferred_notes'           => 'ياخد القبض ويدفع ال 13 اسم',
        ])->assertSessionHas('success');

        $companyTurn13->refresh();
        $this->assertTrue($companyTurn13->is_transferred);
        $this->assertFalse($companyTurn13->is_company);
        $this->assertSame('الحاج سيد', $companyTurn13->share_label);
        $this->assertSame(13, (int) $companyTurn13->paid_months_before_transfer);
        $this->assertEquals(13000.0, (float) $companyTurn13->company_claim_amount);

        $waivedCount = JamiaInstallment::where('share_id', $companyTurn13->id)->where('is_waived', true)->count();
        $payableCount = JamiaInstallment::where('share_id', $companyTurn13->id)->where('is_waived', false)->count();
        $this->assertSame(13, $waivedCount);
        $this->assertSame(2, $payableCount);

        // 4. Collect month 14 installment from replacement member (+1,000 -> secondAcc = 11,000)
        $inst14 = JamiaInstallment::where('share_id', $companyTurn13->id)->whereDate('month_date', '2027-12-01')->firstOrFail();
        $this->asDev()->post(route('cashflows.collect'), [
            'installment_ids' => [$inst14->id],
            'account_id'      => $secondAccId,
        ])->assertSessionHas('success');
        $this->assertEquals(11000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));

        // undoTransfer blocked once replacement member paid an installment
        $this->asDev()->post(route('cashflows.undo_transfer'), [
            'share_id' => $companyTurn13->id,
        ])->assertSessionHas('error');

        // 5. Claim recovery validations
        // 5a) Recover without account_id fails validation
        $this->asDev()->post(route('cashflows.recover'), [
            'share_id' => $companyTurn13->id,
            'amount'   => 13000,
        ])->assertSessionHasErrors('account_id');

        // 5b) Recover exceeding claim amount fails validation
        $this->asDev()->post(route('cashflows.recover'), [
            'share_id'   => $companyTurn13->id,
            'account_id' => $secondAccId,
            'amount'     => 14500, // exceeds 13,000
        ])->assertSessionHas('error');

        // 6. Payout replacement member (15,000 out from main safe) + recover 13,000 directly into secondAcc!
        $this->asDev()->post(route('cashflows.payout'), [
            'share_id'           => $companyTurn13->id,
            'account_id'         => $this->accountId,
            'amount'             => 15000,
            'also_recover_claim' => 1,
            'recover_account_id' => $secondAccId,
            'recover_amount'     => 13000,
        ])->assertSessionHas('success');

        $companyTurn13->refresh();
        $this->assertTrue($companyTurn13->is_collected);
        $this->assertTrue($companyTurn13->is_claim_recovered);
        $this->assertEquals(13000.0, (float) $companyTurn13->recovered_amount);
        $this->assertEquals(35000.0, (float) DB::table('accounts')->where('id', $this->accountId)->value('balance')); // 50,000 - 15,000
        $this->assertEquals(24000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance')); // 11,000 + 13,000

        // 7. Cancel claim recovery deducts from secondAcc (24,000 -> 11,000) and allows re-recovering standalone
        $this->asDev()->post(route('cashflows.cancel_recovery'), [
            'share_id' => $companyTurn13->id,
        ])->assertSessionHas('success');
        $this->assertFalse($companyTurn13->fresh()->is_claim_recovered);
        $this->assertEquals(11000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));

        // Re-recover via standalone route into secondAcc (11,000 -> 24,000)
        $this->asDev()->post(route('cashflows.recover'), [
            'share_id'   => $companyTurn13->id,
            'account_id' => $secondAccId,
            'amount'     => 13000,
        ])->assertSessionHas('success');
        $this->assertTrue($companyTurn13->fresh()->is_claim_recovered);
        $this->assertEquals(24000.0, (float) DB::table('accounts')->where('id', $secondAccId)->value('balance'));

        // 8. Verify index + both print views render tiles, popups & transferred info cleanly
        $this->asDev()->get(route('cashflows.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('monthJamiaModal-' . $jamia->id)
            ->assertSee('dirJamiaModal-' . $jamia->id)
            ->assertSee('transferModal')
            ->assertSee('recoverModal')
            ->assertSee('الحاج سيد');

        $this->asDev()->get(route('cashflows.print_month', ['month' => '2027-11']))
            ->assertOk()
            ->assertSee('الحاج سيد')
            ->assertSee('بديل الشركة');

        $this->asDev()->get(route('cashflows.print_jamia', $jamia->id))
            ->assertOk()
            ->assertSee('الحاج سيد')
            ->assertSee('تمت للشركة');

        // 9. Transfer & undoTransfer cleanly when no payments exist
        $this->asDev()->post(route('cashflows.transfer'), [
            'share_id'                    => $companyTurn1->id,
            'transferred_to_name'         => 'مؤقت للتجربة',
            'transferred_from_month'      => '2026-12-01',
            'paid_months_before_transfer' => 1,
        ])->assertSessionHas('success');
        $this->assertTrue($companyTurn1->fresh()->is_transferred);

        $this->asDev()->post(route('cashflows.undo_transfer'), [
            'share_id' => $companyTurn1->id,
        ])->assertSessionHas('success');
        $companyTurn1->refresh();
        $this->assertFalse($companyTurn1->is_transferred);
        $this->assertTrue($companyTurn1->is_company);
        $this->assertSame('شركة الضبع', $companyTurn1->share_label);
    }
}
