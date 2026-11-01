<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Petty Titles Library (أسماء النثريات المحملة على العميل - بدون سعر)
        if (!Schema::hasTable('cont_petty_titles')) {
            Schema::create('cont_petty_titles', function (Blueprint $table) {
                $table->id();
                $table->string('title')->unique();
                $table->timestamps();
            });

            DB::table('cont_petty_titles')->insert([
                ['title' => 'مشال وتعتيق خامات', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'شاي وضيافة عمال', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'إكرامية ونش ورفع خامات', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'أدوات نظافة ومستهلكات موقع', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'مواصلات طارئة ونقل عينات', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'بوفيه عمال الموقع', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'حراسة طارئة وتأمين تشوينات', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. General Expense Titles Library (بنود المصروفات العامة والداخلية - بدون سعر)
        if (!Schema::hasTable('cont_expense_titles')) {
            Schema::create('cont_expense_titles', function (Blueprint $table) {
                $table->id();
                $table->string('title')->unique();
                $table->timestamps();
            });

            DB::table('cont_expense_titles')->insert([
                ['title' => 'إيجار سقالات ومعدات موقع', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'حارس موقع وأمن دوري', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'كهرباء ومياه موقع', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'صيانة وشراء عدد وأدوات عمل', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'رسوم وتراخيص وإشغالات', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'طباعة مخططات ورسومات هندسية', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'نثريات إدارة وضيافة مكتب', 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'نقل ومأموريات إدارية', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 3. Units Library (الوحدات المحفوظة للأوتوكومبليت)
        if (!Schema::hasTable('cont_units')) {
            Schema::create('cont_units', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });

            DB::table('cont_units')->insert([
                ['name' => 'متر', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'م2', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'م3', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'عدد', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'قطعة', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'كيس', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'شيكارة', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'طن', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'لفة', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مقطوعية', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'يومية', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'نقلة', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'ساعة', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'لوح', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'كيلو', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مجموعة', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cont_units');
        Schema::dropIfExists('cont_expense_titles');
        Schema::dropIfExists('cont_petty_titles');
    }
};
