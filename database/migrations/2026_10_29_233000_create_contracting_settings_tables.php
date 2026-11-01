<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Global / Default BoQ Items Library
        Schema::create('cont_global_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Petty Cash / General Expense Categories (النثريات)
        Schema::create('cont_petty_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->timestamps();
        });

        // 3. Petty Cash Expense Records (حركات النثريات العامة)
        Schema::create('cont_petty_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('cont_petty_categories')->onDelete('set null');
            $table->string('title')->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });

        // Seed initial default items and petty categories
        DB::table('cont_global_items')->insert([
            ['title' => 'بند أعمال الكهرباء والإنارة', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال السباكة والصرف', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال المحارة والأسمنت', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال الجبس بورد والأسقف', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال السيراميك والبورسلين', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال الدهانات والتشطيبات', 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال النجارة والأبواب', 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال الألوميتال والشبابيك', 'is_active' => true, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بند أعمال العزل المائي والحراري', 'is_active' => true, 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('cont_petty_categories')->insert([
            ['name' => 'بوفيه وضيافة الموقع', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'إكراميات حراسة وعمال', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'انتقالات ومشاوير ومواصلات', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شراء وصيانة عدد وأدوات', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مصاريف إدارية وطباعة وتصوير', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'نثريات ومصاريف طارئة', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cont_petty_expenses');
        Schema::dropIfExists('cont_petty_categories');
        Schema::dropIfExists('cont_global_items');
    }
};
