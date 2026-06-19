<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\GasStationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\OperationsLogController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\SystemController;
// ══════════════════════════════════════════════════════
// مسار إنشاء المستخدمين
// ══════════════════════════════════════════════════════
Route::get('/setup-users', function () {
    \Illuminate\Support\Facades\DB::table('users')->truncate();
    \Illuminate\Support\Facades\DB::table('users')->insert([
        ['name'=>'مدير النظام (أدمن)', 'email'=>'admin@aldabaa.com', 'username'=>'admin',
         'password'=>\Illuminate\Support\Facades\Hash::make('G12@750a'),
         'role'=>'admin', 'is_active'=>1, 'created_at'=>now()],
        ['name'=>'موظف الكاشير', 'email'=>'emp@aldabaa.com', 'username'=>'cashier',
         'password'=>\Illuminate\Support\Facades\Hash::make('G13#78b'),
         'role'=>'employee', 'is_active'=>1, 'created_at'=>now()],
    ]);
    return 'تم إنشاء الحسابات بنجاح!';
});

// ══════════════════════════════════════════════════════
// تسجيل الدخول والخروج
// ══════════════════════════════════════════════════════
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ══════════════════════════════════════════════════════
// بوابة العميل (Public - بدون nav bar)
// ══════════════════════════════════════════════════════
Route::prefix('portal')->group(function () {
    Route::get('/login',  [CustomerPortalController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [CustomerPortalController::class, 'login'])->name('portal.login.submit');

    Route::middleware('portal')->group(function () {
        Route::get('/',              [CustomerPortalController::class, 'dashboard'])->name('portal.dashboard');
        Route::get('/contract/{id}', [CustomerPortalController::class, 'contractDetails'])->name('portal.contract');
        Route::post('/logout',       [CustomerPortalController::class, 'logout'])->name('portal.logout');
    });
});

// ══════════════════════════════════════════════════════
// المسارات المحمية بـ auth.custom
// ══════════════════════════════════════════════════════
Route::middleware('auth.custom')->group(function () {

    // الصفحة الرئيسية ← Dashboard
    Route::get('/',          fn() => redirect()->route('dashboard.index'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // سجل التدقيق (Audit Log) — أدمن فقط
    Route::get('/audit-log',      [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-log/{id}', [AuditLogController::class, 'show'])->name('audit.show');

    // سجل العمليات (Operations Log) — تعديل العمليات الخاطئة (أدمن + رمز سري)
    Route::get('/operations-log',                     [OperationsLogController::class, 'index'])->name('operations.index');
    Route::post('/operations-log/{editor}/{id}/show',   [OperationsLogController::class, 'show'])->name('operations.show');
    Route::post('/operations-log/{editor}/{id}/delete', [OperationsLogController::class, 'destroy'])->name('operations.destroy');
    Route::post('/operations-log/{editor}/{id}',        [OperationsLogController::class, 'update'])->name('operations.update');

    // استفسارات العملاء
    Route::get('/inquiries',                      [InquiryController::class, 'index'])->name('inquiries.index');
    Route::post('/inquiries/store',               [InquiryController::class, 'store'])->name('inquiries.store');
    Route::post('/inquiries/{id}/toggle-contact', [InquiryController::class, 'toggleContact'])->name('inquiries.toggle');
    Route::post('/inquiries/{id}/update',         [InquiryController::class, 'update'])->name('inquiries.update');
    Route::post('/inquiries/{id}/delete',         [InquiryController::class, 'destroy'])->name('inquiries.destroy');

    // Risk Score للعملاء
    Route::get('/customers/risk-score', [CustomerController::class, 'riskScore'])->name('customers.riskScore');

    Route::post('/profile/update-name', [AuthController::class, 'updateProfile'])->name('profile.updateName');

    // ── الإعدادات ──
    Route::get('/settings',                               [SettingsController::class, 'settings'])->name('settings.index');
    Route::post('/settings/payment-method',             [SettingsController::class, 'storePaymentMethod'])->name('settings.storePaymentMethod');
    Route::post('/settings/payment-method/{id}/rename',   [SettingsController::class, 'renameAccount'])->name('settings.renameAccount');
    Route::post('/settings/accounts/{id}/delete',         [SettingsController::class, 'destroyAccount'])->name('settings.destroyAccount');
    Route::post('/settings/commission',                   [SettingsController::class, 'updateCommissionSettings'])->name('settings.updateCommission');
    Route::post('/settings/suppliers',                    [SettingsController::class, 'storeSupplier'])->name('settings.storeSupplier');
    Route::get('/settings/suppliers/{id}/delete',         [SettingsController::class, 'destroySupplier'])->name('settings.destroySupplier');
    Route::post('/settings/companies',                    [SettingsController::class, 'storeCompany'])->name('settings.storeCompany');
    Route::get('/settings/companies/{id}/delete',         [SettingsController::class, 'destroyCompany'])->name('settings.destroyCompany');
    Route::post('/settings/stations',                     [SettingsController::class, 'storeStation'])->name('settings.storeStation');
    Route::get('/settings/stations/{id}/delete',          [SettingsController::class, 'destroyStation'])->name('settings.destroyStation');
    Route::post('/settings/items',                        [SettingsController::class, 'storeItem'])->name('settings.storeItem');
    Route::get('/settings/items/{id}/delete',             [SettingsController::class, 'destroyItem'])->name('settings.destroyItem');
    Route::post('/settings/expense-categories',           [FinanceController::class, 'storeExpenseCategory'])->name('settings.storeExpenseCategory');
    Route::put('/settings/expense-categories/{id}',       [FinanceController::class, 'updateExpenseCategory'])->name('settings.updateExpenseCategory');
    Route::get('/settings/expense-categories/{id}/delete', [FinanceController::class, 'destroyExpenseCategory'])->name('settings.destroyExpenseCategory');

    // إعدادات النظام العامة
    Route::post('/settings/system',                       [SettingsController::class, 'updateSystemSettings'])->name('settings.system.update');

    // ── المستخدمين ──
    Route::get('/users',                      [AuthController::class, 'users'])->name('users.index');
    Route::post('/users',                     [AuthController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/toggle',          [AuthController::class, 'toggleUser'])->name('users.toggle');
    Route::post('/users/{id}/reset-password', [AuthController::class, 'resetPassword'])->name('users.resetPassword');
    Route::get('/users/{id}/delete',          [AuthController::class, 'destroyUser'])->name('users.destroy');

Route::post('/treasury/snapshot', [App\Http\Controllers\FinanceController::class, 'takeCapitalSnapshot'])->name('treasury.snapshot');

    // ── البنزينة ──
    Route::get('/gas-station',  [GasStationController::class, 'gasStation'])->name('gas.index');
    Route::post('/gas-station', [GasStationController::class, 'storeGasStation'])->name('gas.store');


Route::post('/financial/pay-installment', [FinanceController::class, 'payInstallment'])->name('financial.pay_installment');
Route::post('/financial/pay-bulk-installments', [FinanceController::class, 'payBulkInstallments'])->name('financial.pay_bulk_installments');

    // ── المبيعات المباشرة ──
    Route::get('/sales',  [SalesController::class, 'sales'])->name('sales.index');
    Route::post('/sales', [SalesController::class, 'storeSales'])->name('sales.store');

    // ── المخزون ──
    Route::get('/inventory',                  [InventoryController::class, 'inventory'])->name('inventory.index');
    Route::post('/inventory',                 [InventoryController::class, 'inventoryStore'])->name('inventory.store');
    Route::post('/inventory/sell',            [InventoryController::class, 'sellFromInventory'])->name('inventory.sell');
    Route::post('/inventory/return',          [InventoryController::class, 'storeReturn'])->name('inventory.return');
    Route::post('/inventory/restock',         [InventoryController::class, 'restockItem'])->name('inventory.restock');
    Route::post('/inventory/update',          [InventoryController::class, 'updateInventory'])->name('inventory.update');
    Route::post('/inventory/delete',          [InventoryController::class, 'deleteInventory'])->name('inventory.delete');
    Route::post('/inventory/return-supplier', [InventoryController::class, 'returnToSupplier'])->name('inventory.return_supplier');
    Route::post('/inventory/adjust',          [InventoryController::class, 'adjustInventory'])->name('inventory.adjust');
    Route::post('/inventory/transfer',        [InventoryController::class, 'transferInventory'])->name('inventory.transfer');

    // ── المالية ──
    Route::post('/shift/close',                         [FinanceController::class, 'closeShift'])->name('shift.close');
    Route::post('/finance/reverse-payment',             [FinanceController::class, 'reversePayment']);
    Route::get('/treasury',                             [FinanceController::class, 'treasury'])->name('treasury.index');
    Route::get('/treasury/export',                      [\App\Http\Controllers\ExportController::class, 'treasury'])->name('treasury.export');
    Route::get('/reports/export',                       [\App\Http\Controllers\ExportController::class, 'reports'])->name('reports.export');
    Route::post('/treasury/update-balance',             [FinanceController::class, 'updateManualBalance'])->name('treasury.updateManualBalance');
    
Route::get('/backup-database', [SystemController::class, 'exportDatabase'])->name('db.backup');
    
    Route::get('/expenses',                             [FinanceController::class, 'expenses'])->name('expenses.index');
    Route::post('/expenses/store',                      [FinanceController::class, 'storeExpense'])->name('expenses.store');
    Route::get('/installments',                         [FinanceController::class, 'installments'])->name('installments.index');
    Route::post('/installments/store',                  [FinanceController::class, 'storeInstallment'])->name('installments.store');
    Route::post('/installments/pay',                    [FinanceController::class, 'payInstallment'])->name('installments.pay');
    Route::post('/installments/update',                 [FinanceController::class, 'updateInstallment'])->name('installments.update');
    Route::post('/installments/delete',                 [FinanceController::class, 'deleteInstallment'])->name('installments.delete');
    Route::post('/installments/pay-bulk',               [FinanceController::class, 'payBulkInstallments'])->name('installments.pay_bulk');
    Route::post('/installments/reverse-payment',        [FinanceController::class, 'reverseInstallmentPayment'])->name('installments.reverse_pay');
    Route::post('/installments/delete-defaulted',       [FinanceController::class, 'deleteDefaultedPayment'])->name('installments.deleteDefaulted');
    Route::post('/installments/writeoff',               [FinanceController::class, 'writeOffInstallment'])->name('installments.writeoff');
    Route::post('/installments/terminate',              [FinanceController::class, 'terminateInstallment'])->name('installments.terminate');
    Route::get('/debts',                                [FinanceController::class, 'debts'])->name('debts.index');
    Route::post('/debts/discount',                      [FinanceController::class, 'applyDiscount'])->name('debts.discount');
    Route::post('/debts/writeoff',                      [FinanceController::class, 'writeOffDebt'])->name('debts.writeoff');
    Route::get('/debts2',                               [FinanceController::class, 'companyDebts'])->name('company_debts.index');
    Route::post('/debts/company/store',                 [FinanceController::class, 'storeCompanyDebt'])->name('company_debts.store');
    Route::post('/debts/company/pay',                   [FinanceController::class, 'payCompanyDebtOnUs'])->name('company_debts.pay');
    Route::post('/company-debts/pay-bulk',                [FinanceController::class, 'payCompanyDebtBulk'])->name('company_debts.pay_bulk');
    Route::post('/company-debts/delete',                [FinanceController::class, 'deleteCompanyDebt'])->name('company_debts.delete');
    Route::get('/financial-ops',                        [FinanceController::class, 'financialOps'])->name('financial.index');
    Route::post('/financial-ops/store',                 [FinanceController::class, 'storeFinancialOp'])->name('financial.store');
    Route::post('/financial-ops/cancel',                [FinanceController::class, 'cancelFinancialOp'])->name('financial.cancel');
    Route::post('/financial-ops/capital-adjustment',        [FinanceController::class, 'storeCapitalAdjustment'])->name('financial.capitalAdjust');
    Route::post('/financial-ops/capital-adjustment/cancel', [FinanceController::class, 'cancelCapitalAdjustment'])->name('financial.capitalAdjustCancel');
    Route::get('/archive',                              [FinanceController::class, 'archive'])->name('archive.index');

    // ── التقارير ──
    Route::get('/reports',            [ReportController::class, 'reports'])->name('reports.index');
    Route::post('/send-daily-report', [ReportController::class, 'sendDailyReport'])->name('send.daily.report');



Route::get('/goals', [GoalsController::class, 'index'])->name('goals.index');
 
// ── إنشاء هدف جديد ──
Route::post('/goals/store', [GoalsController::class, 'store'])->name('goals.store');
 
// ── إغلاق هدف (يدوي) ──
Route::post('/goals/close', [GoalsController::class, 'close'])->name('goals.close');
 
// ── حذف هدف ──
Route::post('/goals/destroy', [GoalsController::class, 'destroy'])->name('goals.destroy');
 
// ── API: بيانات الشريط للـ sidebar / داشبورد ──
Route::get('/goals/progress-bar', [GoalsController::class, 'progressBar'])->name('goals.progress_bar');
 

// ── الأصول الثابتة ──
    Route::get('/assets',                         [AssetController::class, 'assets'])->name('assets.index');
    Route::post('/assets/store',                  [AssetController::class, 'storeAsset'])->name('assets.store');
    Route::post('/assets/update',                 [AssetController::class, 'updateAsset'])->name('assets.update');
    Route::post('/assets/depreciate',             [AssetController::class, 'depreciateAsset'])->name('assets.depreciate');
    Route::post('/assets/manual-depreciate',      [AssetController::class, 'manualDepreciate'])->name('assets.manual_depreciate'); // 👈 هذا هو السطر الجديد
    Route::post('/assets/sell',                   [AssetController::class, 'sellAsset'])->name('assets.sell');
    Route::post('/assets/destroy',                [AssetController::class, 'destroyAsset'])->name('assets.destroy');
    // ── الواتساب ──
    Route::get('/whatsapp-center', [WhatsappController::class, 'whatsappCenter'])->name('whatsapp.center');

    // ── أرشيف العملاء ──
    Route::get('/customers-archive',        [CustomerController::class, 'customersArchive'])->name('customers.archive');
    Route::post('/customers-archive/store', [CustomerController::class, 'storeCustomer'])->name('customers.store');

Route::post('/inventory/adjust', [App\Http\Controllers\InventoryController::class, 'adjustStock'])->name('inventory.adjust');

    // ── الإشعارات ──
    Route::get('/notifications',         [ActivityController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [ActivityController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/clear',  [ActivityController::class, 'clearOld'])->name('notifications.clear');
});

// ══════════════════════════════════════════════════════
// مسارات الأدمن فقط
// ══════════════════════════════════════════════════════
Route::middleware(['auth.custom', \App\Http\Middleware\CheckAdmin::class])->group(function () {
    Route::post('/dev/reset-database', [App\Http\Controllers\SystemController::class, 'resetDatabase'])->name('dev.reset');
    Route::get('/partners',             [PartnerController::class, 'partners'])->name('partners.index');
    Route::post('/partners/store',      [PartnerController::class, 'storePartner'])->name('partners.store');
    Route::post('/partners/distribute', [PartnerController::class, 'distributeProfit'])->name('partners.distributeProfit');
    Route::post('/partners/withdraw',   [PartnerController::class, 'withdrawProfit'])->name('partners.withdrawProfit');
    Route::post('/partners/reinvest',   [PartnerController::class, 'reinvestProfit'])->name('partners.reinvest');
    Route::post('/partners/exit',       [PartnerController::class, 'exitPartner'])->name('partners.exitPartner');

    Route::get('/hr',              [HrController::class, 'index'])->name('hr.index');
    Route::post('/hr/store',       [HrController::class, 'storeEmployee'])->name('hr.store');
    Route::post('/hr/transaction', [HrController::class, 'addTransaction'])->name('hr.transaction');
    Route::post('/hr/pay',         [HrController::class, 'paySalary'])->name('hr.pay');
    Route::post('/hr/update',      [HrController::class, 'updateEmployee'])->name('hr.update');
    Route::post('/hr/delete',      [HrController::class, 'deleteEmployee'])->name('hr.delete');
});

Route::post('/settings/store-deduction',      [SettingsController::class, 'storeDeduction']);
Route::get('/settings/delete-deduction/{id}', [SettingsController::class, 'destroyDeduction']);

Route::get('/seed-partners', function () {
    \Illuminate\Support\Facades\DB::table('partners')->truncate();
    \Illuminate\Support\Facades\DB::table('partners')->insert([
        ['name' => 'أحمد الضبع (مؤسس)', 'capital' => 400000, 'profit_wallet' => 0, 'created_at' => now()],
        ['name' => 'شريك مستثمر (أ)', 'capital' => 300000, 'profit_wallet' => 0, 'created_at' => now()],
        ['name' => 'شريك مستثمر (ب)', 'capital' => 200000, 'profit_wallet' => 0, 'created_at' => now()],
        ['name' => 'شريك مستثمر (ج)', 'capital' => 100000, 'profit_wallet' => 0, 'created_at' => now()],
    ]);
    return '✅ تم مسح القديم وإضافة 4 شركاء بنجاح بإجمالي رأس مال مليون جنيه مقسمة (40%، 30%، 20%، 10%)';
});