<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 📸 لقطة تلقائية للمركز المالي كل يوم الساعة 12 منتصف الليل (بتوقيت مصر)
Schedule::command('snapshot:capital')
    ->dailyAt('00:00')
    ->timezone('Africa/Cairo')
    ->withoutOverlapping();
