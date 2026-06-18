<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            // السيستم هيشيك لو العمود مش موجود هيضيفه، لو موجود هيتجاهله ويكمل
            if (!Schema::hasColumn('items', 'original_price')) {
                $table->decimal('original_price', 10, 2)->default(0);
            }
        });
    }
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'selling_price']);
        });
    }
};