<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jamia_shares', function (Blueprint $table) {
            if (!Schema::hasColumn('jamia_shares', 'share_fraction')) {
                $table->decimal('share_fraction', 5, 2)->default(1.00)->after('share_number');
            }
            if (!Schema::hasColumn('jamia_shares', 'split_part')) {
                $table->unsignedTinyInteger('split_part')->default(1)->after('share_fraction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jamia_shares', function (Blueprint $table) {
            if (Schema::hasColumn('jamia_shares', 'split_part')) {
                $table->dropColumn('split_part');
            }
            if (Schema::hasColumn('jamia_shares', 'share_fraction')) {
                $table->dropColumn('share_fraction');
            }
        });
    }
};
