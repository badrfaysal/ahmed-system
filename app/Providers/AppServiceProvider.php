<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // دوال مساعدة عامة (تنسيق المبالغ بالكسور ...)
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // استخدام Bootstrap 5 لتنسيق روابط الترقيم (links)
        Paginator::useBootstrapFive();

        // مشاركة بيانات رئيس مجلس الإدارة مع جميع شاشات موديول الشركاء
        \Illuminate\Support\Facades\View::composer('partners.*', function ($view) {
            $head = \App\Services\PartnerProfitService::headPartner();
            $view->with('headPartner', $head);
            $view->with('headName', \App\Services\PartnerProfitService::headName());
        });
    }
}
