<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jamia_shares', function (Blueprint $table) {
            if (Schema::hasColumn('jamia_shares', 'share_fraction')) {
                $table->decimal('share_fraction', 10, 6)->default(1.000000)->change();
            } else {
                $table->decimal('share_fraction', 10, 6)->default(1.000000)->after('share_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jamia_shares', function (Blueprint $table) {
            if (Schema::hasColumn('jamia_shares', 'share_fraction')) {
                $table->decimal('share_fraction', 5, 2)->default(1.00)->change();
            }
        });
    }
};
