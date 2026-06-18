@echo off
chcp 65001 > nul
echo.
echo ========================================
echo        تجهيز نظام أحمد
echo ========================================
echo.

echo [1/3] تنزيل مكتبات PHP...
call composer install --no-dev --optimize-autoloader
if %errorlevel% neq 0 (
    echo.
    echo خطأ: تأكد أن Composer مثبت على جهازك
    echo رابط التحميل: https://getcomposer.org/Composer-Setup.exe
    pause
    exit /b 1
)

echo.
echo [2/3] توليد مفتاح التطبيق...
php artisan key:generate

echo.
echo [3/3] تجهيز الجلسات والكاش...
php artisan config:clear
php artisan cache:clear

echo.
echo ========================================
echo   تم التجهيز! الآن:
echo   1- افتح phpMyAdmin وأنشئ قاعدة بيانات اسمها: ahmed
echo   2- استورد ملف الداتا بيز .sql اللي بعتهولك
echo   3- شغّل السيرفر بالأمر:  php artisan serve
echo   4- افتح المتصفح على:     http://localhost:8000
echo ========================================
echo.
pause
