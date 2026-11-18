<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'allowed_screens')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('allowed_screens')->nullable()->after('hide_financials');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'allowed_screens')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('allowed_screens');
            });
        }
    }
};
