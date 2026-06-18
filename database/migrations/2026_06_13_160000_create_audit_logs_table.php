<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name', 100)->nullable();
                $table->string('user_role', 50)->nullable();
                $table->string('action', 50);          // create, update, delete, login, logout, etc.
                $table->string('module', 50);          // installments, debts, finance, ...
                $table->string('entity_type', 50)->nullable();   // installments table name مثلاً
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('summary', 500);        // وصف بشري للحدث
                $table->json('old_values')->nullable(); // البيانات قبل التعديل
                $table->json('new_values')->nullable(); // البيانات بعد التعديل
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->string('severity', 20)->default('info'); // info, warning, critical
                $table->timestamp('created_at')->useCurrent();

                $table->index('user_id');
                $table->index('module');
                $table->index('action');
                $table->index('entity_type');
                $table->index('created_at');
                $table->index('severity');
                $table->index(['module', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
