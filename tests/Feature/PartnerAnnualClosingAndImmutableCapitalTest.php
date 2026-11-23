<?php

namespace Tests\Feature;

use App\Services\PartnerProfitService;
use App\Services\ScreenAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PartnerAnnualClosingAndImmutableCapitalTest extends TestCase
{
    protected object $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        ScreenAccessService::clearCache();

        foreach ([
            'partner_annual_closing_lines', 'partner_annual_closings',
            'partner_share_history_lines', 'partner_share_histories',
            'partner_period_lines', 'partner_periods', 'partner_movements', 'partners',
            'capital_snapshots', 'fuel_transactions', 'assets', 'sales',
            'company_debts', 'installment_payments', 'installments',
            'financial_transactions', 'accounts', 'system_settings', 'activity_logs',
            'audit_logs', 'customer_inquiries', 'users',
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
            $table->text('allowed_screens')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->string('label')->default('');
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
            $table->timestamps();
        });

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('subtype', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
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
            $table->string('customer_name')->default('');
            $table->string('sale_type')->nullable();
            $table->string('category')->nullable();
            $table->decimal('cash_price', 15, 2)->default(0);
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->integer('installment_months')->default(0);
            $table->decimal('total_after_interest', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->string('source_type', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_status')->nullable();
            $table->decimal('remaining_quantity', 15, 2)->default(0);
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

        Schema::create('company_debts', function (Blueprint $table) {
            $table->id();
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('capital_snapshots', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_capital', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('capital', 15, 2)->default(0);
            $table->decimal('initial_capital', 15, 2)->default(0);
            $table->decimal('ahmed_ratio', 5, 2)->default(20.00);
            $table->decimal('monthly_fixed_withdrawal', 15, 2)->nullable();
            $table->boolean('is_head')->default(false);
            $table->string('status')->default('active');
            $table->date('joined_at')->nullable();
            $table->integer('last_reinvest_year')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('partner_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('opening_capital', 15, 2)->default(0);
            $table->decimal('closing_capital', 15, 2)->default(0);
            $table->decimal('capital_adjustments', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('total_partners_gross_profit', 15, 2)->default(0);
            $table->decimal('total_ahmed_commission', 15, 2)->default(0);
            $table->decimal('total_partners_net_profit', 15, 2)->default(0);
            $table->decimal('ahmed_total_profit', 15, 2)->default(0);
            $table->decimal('ahmed_withdrawn_amount', 15, 2)->default(0);
            $table->decimal('ahmed_reinvested_amount', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->decimal('total_reinvested', 15, 2)->default(0);
            $table->string('status')->default('closed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('partner_period_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id');
            $table->unsignedBigInteger('partner_id');
            $table->decimal('capital_at_closing', 15, 2)->default(0);
            $table->decimal('capital_share_pct', 8, 4)->default(0);
            $table->decimal('gross_share', 15, 2)->default(0);
            $table->decimal('ahmed_ratio', 5, 2)->default(20.00);
            $table->decimal('ahmed_commission', 15, 2)->default(0);
            $table->decimal('net_share', 15, 2)->default(0);
            $table->string('decision')->default('reinvest');
            $table->decimal('withdrawn_amount', 15, 2)->default(0);
            $table->decimal('reinvested_amount', 15, 2)->default(0);
            $table->decimal('new_capital', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('partner_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('period_id')->nullable();
            $table->string('type');
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('partner_share_histories', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('title');
            $table->unsignedBigInteger('period_id')->nullable();
            $table->unsignedBigInteger('movement_id')->nullable();
            $table->unsignedBigInteger('actor_partner_id')->nullable();
            $table->decimal('total_capital_before', 15, 2)->default(0);
            $table->decimal('total_capital_after', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('partner_share_history_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('history_id');
            $table->unsignedBigInteger('partner_id');
            $table->string('partner_name');
            $table->boolean('is_head')->default(false);
            $table->decimal('capital_before', 15, 2)->default(0);
            $table->decimal('capital_after', 15, 2)->default(0);
            $table->decimal('capital_change', 15, 2)->default(0);
            $table->decimal('share_pct_before', 8, 4)->default(0);
            $table->decimal('share_pct_after', 8, 4)->default(0);
            $table->decimal('share_pct_diff', 8, 4)->default(0);
            $table->timestamps();
        });

        // تشغيل ميجريشن التقفيلات السنوية للتأكد من توافقه التام مع SQLite و MySQL
        $migration = require base_path('database/migrations/2026_11_23_140000_create_partner_annual_closings_tables.php');
        $migration->up();

        $adminId = DB::table('users')->insertGetId([
            'name'       => 'المدير العام',
            'email'      => 'admin@test.local',
            'password'   => Hash::make('secret'),
            'role'       => 'admin',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->adminUser = DB::table('users')->where('id', $adminId)->first();

        DB::table('accounts')->insert([
            'account_name' => 'خزنة الشركة',
            'category'     => 'safe_cash',
            'status'       => 'active',
            'balance'      => 2000000.00,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function test_update_partner_cannot_modify_initial_capital(): void
    {
        $partnerId = DB::table('partners')->insertGetId([
            'name'            => 'شريك اختبار',
            'phone'           => '01000000001',
            'capital'         => 1400000.00,
            'initial_capital' => 1100000.00,
            'ahmed_ratio'     => 20.00,
            'is_head'         => false,
            'status'          => 'active',
            'joined_at'       => '2026-01-01',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $response = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('partners.update', $partnerId), [
                'name'            => 'شريك اختبار معدّل',
                'phone'           => '01099999999',
                'ahmed_ratio'     => 25.00,
                'initial_capital' => 500.00, // محاولة تغيير رأس المال التأسيسي
            ]);

        $response->assertRedirect();

        $updated = DB::table('partners')->where('id', $partnerId)->first();
        $this->assertSame('شريك اختبار معدّل', $updated->name);
        $this->assertSame('01099999999', $updated->phone);
        $this->assertEquals(25.00, (float) $updated->ahmed_ratio);
        // رأس المال التأسيسي يجب أن يظل 1,100,000.00 بدون أي تعديل
        $this->assertEquals(1100000.00, (float) $updated->initial_capital);
    }

    public function test_annual_closings_preserve_initial_capital_and_track_multi_year_gains(): void
    {
        $headId = DB::table('partners')->insertGetId([
            'name'            => 'ربح أحمد',
            'capital'         => 100000.00,
            'initial_capital' => 0.00,
            'ahmed_ratio'     => 0.00,
            'is_head'         => true,
            'status'          => 'active',
            'joined_at'       => '2026-01-01',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $partnerId = DB::table('partners')->insertGetId([
            'name'            => 'محمود يحي زغلول',
            'phone'           => '01019956661',
            'capital'         => 1400000.00,
            'initial_capital' => 1100000.00,
            'ahmed_ratio'     => 20.00,
            'is_head'         => false,
            'status'          => 'active',
            'joined_at'       => '2026-01-01',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // إقفال سنة 2026
        $count2026 = PartnerProfitService::executeAnnualCapitalRollover(2026, $this->adminUser->id, null, 'إقفال نهاية 2026');
        $this->assertSame(2, $count2026);

        // التأكد من بقاء رأس المال التأسيسي ثابتاً 1,100,000.00
        $pAfter2026 = DB::table('partners')->where('id', $partnerId)->first();
        $this->assertEquals(1100000.00, (float) $pAfter2026->initial_capital);
        $this->assertSame(2026, (int) $pAfter2026->last_reinvest_year);

        $line2026 = DB::table('partner_annual_closing_lines')
            ->where('closing_year', 2026)
            ->where('partner_id', $partnerId)
            ->first();
        $this->assertNotNull($line2026);
        $this->assertEquals(1100000.00, (float) $line2026->initial_capital);
        $this->assertEquals(1100000.00, (float) $line2026->opening_capital);
        $this->assertEquals(1400000.00, (float) $line2026->closing_capital);
        $this->assertEquals(300000.00, (float) $line2026->annual_gain);
        $this->assertEquals(300000.00, (float) $line2026->cumulative_gain);

        // منع تكرار إقفال نفس السنة 2026
        $duplicateBlocked = false;
        try {
            PartnerProfitService::executeAnnualCapitalRollover(2026, $this->adminUser->id);
        } catch (\Exception $e) {
            $duplicateBlocked = true;
            $this->assertStringContainsString('2026', $e->getMessage());
        }
        $this->assertTrue($duplicateBlocked, 'Expected duplicate closing for year 2026 to throw an exception.');

        // نمو رأس المال خلال 2027 إلى 1,750,000.00 ثم إقفال 2027 عبر مسار HTTP
        DB::table('partners')->where('id', $partnerId)->update(['capital' => 1750000.00]);
        DB::table('partners')->where('id', $headId)->update(['capital' => 180000.00]);

        $rolloverRes = $this->withSession(['auth_user' => $this->adminUser])
            ->post(route('partners.annual.rollover'), [
                'closing_year' => 2027,
                'notes'        => 'إقفال نهاية 2027',
            ]);
        $rolloverRes->assertRedirect(route('partners.index', ['tab' => 'annual']));

        // ما زال رأس المال التأسيسي 1,100,000.00 بدون أي تعديل
        $pAfter2027 = DB::table('partners')->where('id', $partnerId)->first();
        $this->assertEquals(1100000.00, (float) $pAfter2027->initial_capital);

        $line2027 = DB::table('partner_annual_closing_lines')
            ->where('closing_year', 2027)
            ->where('partner_id', $partnerId)
            ->first();
        $this->assertNotNull($line2027);
        $this->assertEquals(1100000.00, (float) $line2027->initial_capital);
        $this->assertEquals(1400000.00, (float) $line2027->opening_capital);
        $this->assertEquals(1750000.00, (float) $line2027->closing_capital);
        $this->assertEquals(350000.00, (float) $line2027->annual_gain);      // 1,750,000 - 1,400,000
        $this->assertEquals(650000.00, (float) $line2027->cumulative_gain);  // 1,750,000 - 1,100,000
        $this->assertEquals(25.00, (float) $line2027->annual_gain_pct);      // 350,000 / 1,400,000 * 100

        // التحقق من عرض التابة الجديدة وتقرير الطباعة السنوي عبر HTTP
        $indexRes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('partners.index', ['tab' => 'annual']));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('التقفيلات السنوية');
        $indexRes->assertSee('1,100,000.00');
        $indexRes->assertSee('1,400,000.00');
        $indexRes->assertSee('1,750,000.00');
        $indexRes->assertSee('إقفال سنة 2026');
        $indexRes->assertSee('إقفال سنة 2027');

        $annual2027Res = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('partners.annual.show', 2027));
        $annual2027Res->assertStatus(200);
        $annual2027Res->assertSee('تقرير الإقفال السنوي لسنة 2027');
        $annual2027Res->assertSee('1,100,000.00');
        $annual2027Res->assertSee('1,750,000.00');

        // التحقق من كشف حساب الشريك الفردي (يعرض دخل بكام وبقا كام وكسب كام والتقفيلات السنوية مباشرة)
        $showRes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('partners.show', $partnerId));
        $showRes->assertStatus(200);
        $showRes->assertSee('annualStatementSection', false);
        $showRes->assertSee('stmt-timeline-ribbon', false);
        $showRes->assertSee('1,100,000.00');
        $showRes->assertSee('1,400,000.00');
        $showRes->assertSee('1,750,000.00');
        $showRes->assertSee('+650,000.00');
        $showRes->assertSee('إقفال سنة 2026');
        $showRes->assertSee('إقفال سنة 2027');
        $showRes->assertSee('سجل التقفيلات السنوية:');

        // التحقق من كشف حساب الإدارة
        $ahmedRes = $this->withSession(['auth_user' => $this->adminUser])
            ->get(route('partners.ahmed'));
        $ahmedRes->assertStatus(200);
        $ahmedRes->assertSee('ahmedAnnualSection', false);
        $ahmedRes->assertSee('إقفال سنة 2026');
        $ahmedRes->assertSee('إقفال سنة 2027');
    }

    public function test_migration_backfill_restores_initial_capital_from_legacy_rollover_movements(): void
    {
        // إسقاط جدولي التقفيلات السنوية وإعادة التشغيل بعد زرع بيانات قديمة تأثرت بالترحيل القديم
        Schema::dropIfExists('partner_annual_closing_lines');
        Schema::dropIfExists('partner_annual_closings');

        $pid = DB::table('partners')->insertGetId([
            'name'            => 'شريك قديم',
            'capital'         => 1402230.66,
            'initial_capital' => 1402230.66, // تم تغييره خطأً في النظام القديم
            'ahmed_ratio'     => 20.00,
            'is_head'         => false,
            'status'          => 'active',
            'joined_at'       => '2026-01-01',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('partner_movements')->insert([
            'partner_id' => $pid,
            'type'       => 'annual_rollover',
            'amount'     => 1402230.66,
            'notes'      => 'إقفال سنوي: تحويل رصيد 31 ديسمبر 2026 (1,402,230.66 ج) إلى أصل رأس المال في 1 يناير 2027 (أصل رأس المال السابق: 1,100,000.00 ج)',
            'created_by' => $this->adminUser->id,
            'created_at' => '2026-11-22 23:38:19',
            'updated_at' => '2026-11-22 23:38:19',
        ]);

        $migration = require base_path('database/migrations/2026_11_23_140000_create_partner_annual_closings_tables.php');
        $migration->up();

        $restored = DB::table('partners')->where('id', $pid)->first();
        $this->assertEquals(1100000.00, (float) $restored->initial_capital);

        $line = DB::table('partner_annual_closing_lines')
            ->where('closing_year', 2026)
            ->where('partner_id', $pid)
            ->first();
        $this->assertNotNull($line);
        $this->assertEquals(1100000.00, (float) $line->initial_capital);
        $this->assertEquals(1402230.66, (float) $line->closing_capital);
        $this->assertEquals(302230.66, (float) $line->annual_gain);
    }
}
