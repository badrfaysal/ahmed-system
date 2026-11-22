<?php

use App\Services\UniqueNameGuardService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * فصل مورد الأسمنت في المقاولات «رمضان» إلى «رمضان (اسمنت)»
     * عن محطة بنزين «رمضان» تلقائياً عند الرفع على السيرفر.
     */
    public function up(): void
    {
        UniqueNameGuardService::ensureRamadanSupplierSeparated(true);
    }

    public function down(): void
    {
        // لا نعيد دمج الاسمين لمنع اختلاط ديون الوقود مع المقاولات مجدداً.
    }
};
