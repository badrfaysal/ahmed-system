<?php
// ══════════════════════════════════════════════════════
// bootstrap/app.php  —  Laravel 11
// ══════════════════════════════════════════════════════

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── تسجيل الـ aliases (الأسماء المختصرة) ──
        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\AuthMiddleware::class,
            'admin.only'  => \App\Http\Middleware\AdminMiddleware::class,
            'portal'      => \App\Http\Middleware\CustomerPortalAuth::class,
        ]);

        // 🔒 منع دور "موظف" المقيّد من الحذف/الفسخ/التعديل التشغيلي، وحصر دور "مشاهد" في التقارير فقط
        $middleware->web(append: [
            \App\Http\Middleware\RestrictEmployeeActions::class,
            \App\Http\Middleware\RestrictViewerToReports::class,
            \App\Http\Middleware\RestrictFinancialAccess::class,
            \App\Http\Middleware\AuditRequestMiddleware::class,
            \App\Http\Middleware\PreventDoubleSubmission::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();