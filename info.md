# 🗺️ خريطة نظام شركة الضبع — Blade ↔ Controller ↔ Route

ملف مرجعي يربط كل شاشة (Blade) بالـ Controller والـ Route والـ Methods اللي بتخدمها.
استخدمه لما تلاقي خطأ أو عايز تعدل شاشة معينة — تعرف على طول تروح فين.

---

## 📑 المحتويات

1. [الصفحات العامة (Auth + Portal)](#1-الصفحات-العامة)
2. [الداش بورد والإدارة العامة](#2-الداش-بورد-والإدارة-العامة)
3. [المبيعات والمخزن والبنزينة](#3-المبيعات-والمخزن-والبنزينة)
4. [الماليات (خزائن / أقساط / ديون / مصروفات)](#4-الماليات)
5. [التقارير والتصدير](#5-التقارير-والتصدير)
6. [الموارد البشرية والشركاء (أدمن فقط)](#6-الموارد-البشرية-والشركاء-أدمن-فقط)
7. [أصول / خدمات / إشعارات / إعدادات](#7-أصول--خدمات--إشعارات--إعدادات)
8. [سجل العمليات والتدقيق](#8-سجل-العمليات-والتدقيق)
9. [مساعدات شائعة (Partials + Services + Middlewares)](#9-مساعدات-شائعة)

---

## 1) الصفحات العامة

| الشاشة (Blade) | URL | Route name | Controller@method |
|---|---|---|---|
| [login.blade.php](resources/views/login.blade.php) | `GET /login` · `POST /login` | `login` · `login.submit` | [Authcontroller](app/Http/Controllers/Authcontroller.php)@`showLogin` · @`login` |
| [welcome.blade.php](resources/views/welcome.blade.php) | (Laravel default — غير مستخدمة فعلياً) | — | — |
| [portal/login.blade.php](resources/views/portal/login.blade.php) | `GET /portal/login` · `POST /portal/login` | `portal.login` · `portal.login.submit` | [CustomerPortalController](app/Http/Controllers/CustomerPortalController.php)@`showLogin` · @`login` |
| [portal/layout.blade.php](resources/views/portal/layout.blade.php) | (Layout مشترك لصفحات الـ portal) | — | — |
| [portal/dashboard.blade.php](resources/views/portal/dashboard.blade.php) | `GET /portal/` | `portal.dashboard` | [CustomerPortalController](app/Http/Controllers/CustomerPortalController.php)@`dashboard` |
| [portal/contract.blade.php](resources/views/portal/contract.blade.php) | `GET /portal/contract/{id}` | `portal.contract` | [CustomerPortalController](app/Http/Controllers/CustomerPortalController.php)@`contractDetails` |

**ملاحظات:**
- صفحات الـ portal محمية بـ middleware: [CustomerPortalAuth](app/Http/Middleware/CustomerPortalAuth.php)
- باقي الصفحات (الإدارية) محمية بـ middleware: `auth.custom` → [AuthMiddleware](app/Http/Middleware/AuthMiddleware.php)

---

## 2) الداش بورد والإدارة العامة

| الشاشة (Blade) | URL | Route name | Controller@method |
|---|---|---|---|
| [dashboard.blade.php](resources/views/dashboard.blade.php) | `GET /dashboard` | `dashboard.index` | [DashboardController](app/Http/Controllers/DashboardController.php)@`index` |
| [sidebar.blade.php](resources/views/sidebar.blade.php) | (Layout مشترك — مُضمَّن في كل شاشة) | — | — |
| [Notifications.blade.php](resources/views/Notifications.blade.php) | `GET /notifications` · `DELETE /notifications/{id}` · `POST /notifications/clear` | `notifications.index` · `notifications.destroy` · `notifications.clear` | [Activitycontroller](app/Http/Controllers/Activitycontroller.php)@`index` · @`destroy` · @`clearOld` |
| [inquiries.blade.php](resources/views/inquiries.blade.php) | `GET /inquiries` · `POST /inquiries/store` · `POST /inquiries/{id}/toggle-contact` · `POST /inquiries/{id}/update` · `POST /inquiries/{id}/delete` | `inquiries.index` · `inquiries.store` · `inquiries.toggle` · `inquiries.update` · `inquiries.destroy` | [InquiryController](app/Http/Controllers/InquiryController.php)@`index` · @`store` · @`toggleContact` · @`update` · @`destroy` |
| [customers_archive.blade.php](resources/views/customers_archive.blade.php) | `GET /customers-archive` · `POST /customers-archive/store` · `GET /customers/risk-score` | `customers.archive` · `customers.store` · `customers.riskScore` | [CustomerController](app/Http/Controllers/CustomerController.php)@`customersArchive` · @`storeCustomer` · @`riskScore` |
| [Goals.blade.php](resources/views/Goals.blade.php) | `GET /goals` · `POST /goals/store` · `POST /goals/close` · `POST /goals/destroy` · `GET /goals/progress-bar` | `goals.index` · `goals.store` · `goals.close` · `goals.destroy` · `goals.progress_bar` | [Goalscontroller](app/Http/Controllers/Goalscontroller.php)@`index` · @`store` · @`close` · @`destroy` · @`progressBar` |

---

## 3) المبيعات والمخزن والبنزينة

### 🛒 المبيعات المباشرة
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [sales.blade.php](resources/views/sales.blade.php) | `GET /sales` · `POST /sales` | `sales.index` · `sales.store` | [SalesController](app/Http/Controllers/SalesController.php)@`sales` · @`storeSales` |

### 📦 المخزن
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [inventory.blade.php](resources/views/inventory.blade.php) | `GET /inventory` | `inventory.index` | [InventoryController](app/Http/Controllers/InventoryController.php)@`inventory` |
| (نفس الشاشة) | `POST /inventory` | `inventory.store` | @`inventoryStore` |
| (نفس الشاشة) | `POST /inventory/sell` | `inventory.sell` | @`sellFromInventory` |
| (نفس الشاشة) | `POST /inventory/return` | `inventory.return` | @`storeReturn` |
| (نفس الشاشة) | `POST /inventory/restock` | `inventory.restock` | @`restockItem` |
| (نفس الشاشة) | `POST /inventory/update` | `inventory.update` | @`updateInventory` *(سعر الشراء بقى للقراءة فقط — يتعدل من سجل العمليات)* |
| (نفس الشاشة) | `POST /inventory/delete` | `inventory.delete` | @`deleteInventory` |
| (نفس الشاشة) | `POST /inventory/return-supplier` | `inventory.return_supplier` | @`returnToSupplier` |
| (نفس الشاشة) | `POST /inventory/adjust` | `inventory.adjust` | @`adjustInventory` / @`adjustStock` |
| (نفس الشاشة) | `POST /inventory/transfer` | `inventory.transfer` | @`transferInventory` |

### ⛽ البنزينة
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [gas_station.blade.php](resources/views/gas_station.blade.php) | `GET /gas-station` · `POST /gas-station` | `gas.index` · `gas.store` | [GasStationController](app/Http/Controllers/GasStationController.php)@`gasStation` · @`storeGasStation` |

---

## 4) الماليات

### 🏦 الخزينة والتقفيل
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [treasury.blade.php](resources/views/treasury.blade.php) | `GET /treasury` | `treasury.index` | [FinanceController](app/Http/Controllers/FinanceController.php)@`treasury` |
| (نفس الشاشة) | `POST /treasury/snapshot` | `treasury.snapshot` | @`takeCapitalSnapshot` |
| (نفس الشاشة) | `POST /treasury/update-balance` | `treasury.updateManualBalance` | @`updateManualBalance` |
| (نفس الشاشة) | `POST /shift/close` | `shift.close` | @`closeShift` |

### 💸 المصروفات
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [expenses.blade.php](resources/views/expenses.blade.php) | `GET /expenses` · `POST /expenses/store` | `expenses.index` · `expenses.store` | [FinanceController](app/Http/Controllers/FinanceController.php)@`expenses` · @`storeExpense` |

### 📝 الأقساط (Installments)
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [installments.blade.php](resources/views/installments.blade.php) | `GET /installments` | `installments.index` | [FinanceController](app/Http/Controllers/FinanceController.php)@`installments` |
| (نفس الشاشة) | `POST /installments/store` | `installments.store` | @`storeInstallment` |
| (نفس الشاشة) | `POST /installments/pay` · `POST /financial/pay-installment` | `installments.pay` · `financial.pay_installment` | @`payInstallment` |
| (نفس الشاشة) | `POST /installments/update` | `installments.update` | @`updateInstallment` |
| (نفس الشاشة) | `POST /installments/restructure` | `installments.restructure` | @`restructureInstallment` |
| (نفس الشاشة) | `POST /installments/delete` | `installments.delete` | @`deleteInstallment` *(زرار الفسخ اتشال من الواجهة — الـ route فاضل للأرشيف)* |
| (نفس الشاشة) | `POST /installments/pay-bulk` · `POST /financial/pay-bulk-installments` | `installments.pay_bulk` · `financial.pay_bulk_installments` | @`payBulkInstallments` |
| (نفس الشاشة) | `POST /installments/reverse-payment` · `POST /finance/reverse-payment` | `installments.reverse_pay` | @`reverseInstallmentPayment` · @`reversePayment` |
| (نفس الشاشة) | `POST /installments/delete-defaulted` | `installments.deleteDefaulted` | @`deleteDefaultedPayment` |
| (نفس الشاشة) | `POST /installments/writeoff` | `installments.writeoff` | @`writeOffInstallment` |

### 📃 المستحقات / الديون
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [debts.blade.php](resources/views/debts.blade.php) (ديون العملاء) | `GET /debts` | `debts.index` | [FinanceController](app/Http/Controllers/FinanceController.php)@`debts` |
| (نفس الشاشة) | `POST /debts/discount` | `debts.discount` | @`applyDiscount` |
| (نفس الشاشة) | `POST /debts/writeoff` | `debts.writeoff` | @`writeOffDebt` |
| [debts2.blade.php](resources/views/debts2.blade.php) (ديون الشركة) | `GET /debts2` | `company_debts.index` | @`companyDebts` |
| (نفس الشاشة) | `POST /debts/company/store` | `company_debts.store` | @`storeCompanyDebt` |
| (نفس الشاشة) | `POST /debts/company/pay` | `company_debts.pay` | @`payCompanyDebtOnUs` |
| (نفس الشاشة) | `POST /company-debts/pay-bulk` | `company_debts.pay_bulk` | @`payCompanyDebtBulk` |
| (نفس الشاشة) | `POST /company-debts/delete` | `company_debts.delete` | @`deleteCompanyDebt` |

### 💰 العمليات المالية (سجل الخزائن + رادار النظام)
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [financial_ops.blade.php](resources/views/financial_ops.blade.php) | `GET /financial-ops` | `financial.index` | [FinanceController](app/Http/Controllers/FinanceController.php)@`financialOps` |
| (نفس الشاشة) | `POST /financial-ops/store` | `financial.store` | @`storeFinancialOp` *(تنفيذ حركة يدوية — إيداع/صرف/تحويل)* |
| (نفس الشاشة) | `POST /financial-ops/cancel` | `financial.cancel` | @`cancelFinancialOp` *(إلغاء حركة يدوية بكلمة مرور الأدمن — يعكس الأثر تلقائياً)* |

### 📂 الأرشيف
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [archive.blade.php](resources/views/archive.blade.php) | `GET /archive` | `archive.index` | [FinanceController](app/Http/Controllers/FinanceController.php)@`archive` |

---

## 5) التقارير والتصدير

| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [reports.blade.php](resources/views/reports.blade.php) | `GET /reports` | `reports.index` | [ReportController](app/Http/Controllers/ReportController.php)@`reports` |
| (نفس الشاشة) | `POST /send-daily-report` | `send.daily.report` | @`sendDailyReport` |
| **تصدير PDF — التقارير** | `GET /reports/export` | `reports.export` | [ExportController](app/Http/Controllers/ExportController.php)@`reports` |
| **تصدير PDF — الخزنة** | `GET /treasury/export` | `treasury.export` | [ExportController](app/Http/Controllers/ExportController.php)@`treasury` |

> ⚠️ التصدير اتحوّل من Excel لـ PDF (طباعة المتصفح). الـ method اسمها لسه `xlsResponse`/`xlsHeader` لكنها بترجّع HTML بيفتح dialog طباعة تلقائي.

---

## 6) الموارد البشرية والشركاء (أدمن فقط)

> محمية بـ middleware إضافي: [CheckAdmin](app/Http/Middleware/CheckAdmin.php)

### 👥 الموارد البشرية
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [hr.blade.php](resources/views/hr.blade.php) | `GET /hr` | `hr.index` | [HrController](app/Http/Controllers/HrController.php)@`index` |
| (نفس الشاشة) | `POST /hr/store` | `hr.store` | @`storeEmployee` |
| (نفس الشاشة) | `POST /hr/transaction` | `hr.transaction` | @`addTransaction` |
| (نفس الشاشة) | `POST /hr/pay` | `hr.pay` | @`paySalary` |
| (نفس الشاشة) | `POST /hr/update` | `hr.update` | @`updateEmployee` |
| (نفس الشاشة) | `POST /hr/delete` | `hr.delete` | @`deleteEmployee` |

### 🤝 الشركاء
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [partners.blade.php](resources/views/partners.blade.php) | `GET /partners` | `partners.index` | [PartnerController](app/Http/Controllers/PartnerController.php)@`partners` |
| (نفس الشاشة) | `POST /partners/store` | `partners.store` | @`storePartner` |
| (نفس الشاشة) | `POST /partners/distribute` | `partners.distributeProfit` | @`distributeProfit` |
| (نفس الشاشة) | `POST /partners/withdraw` | `partners.withdrawProfit` | @`withdrawProfit` |
| (نفس الشاشة) | `POST /partners/reinvest` | `partners.reinvest` | @`reinvestProfit` |
| (نفس الشاشة) | `POST /partners/exit` | `partners.exitPartner` | @`exitPartner` |

---

## 7) أصول / خدمات / إشعارات / إعدادات

### 🏢 الأصول الثابتة
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [assets.blade.php](resources/views/assets.blade.php) | `GET /assets` | `assets.index` | [AssetController](app/Http/Controllers/AssetController.php)@`assets` |
| (نفس الشاشة) | `POST /assets/store` | `assets.store` | @`storeAsset` |
| (نفس الشاشة) | `POST /assets/update` | `assets.update` | @`updateAsset` |
| (نفس الشاشة) | `POST /assets/depreciate` | `assets.depreciate` | @`depreciateAsset` |
| (نفس الشاشة) | `POST /assets/manual-depreciate` | `assets.manual_depreciate` | @`manualDepreciate` |
| (نفس الشاشة) | `POST /assets/sell` | `assets.sell` | @`sellAsset` |
| (نفس الشاشة) | `POST /assets/destroy` | `assets.destroy` | @`destroyAsset` |

### 📱 الواتساب
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [whatsapp.blade.php](resources/views/whatsapp.blade.php) | `GET /whatsapp-center` | `whatsapp.center` | [WhatsappController](app/Http/Controllers/WhatsappController.php)@`whatsappCenter` |

### ⚙️ الإعدادات
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [settings.blade.php](resources/views/settings.blade.php) | `GET /settings` | `settings.index` | [SettingsController](app/Http/Controllers/SettingsController.php)@`settings` |
| (نفس الشاشة) | `POST /settings/payment-method` | `settings.storePaymentMethod` | @`storePaymentMethod` |
| (نفس الشاشة) | `POST /settings/payment-method/{id}/rename` | `settings.renameAccount` | @`renameAccount` |
| (نفس الشاشة) | `POST /settings/commission` | `settings.updateCommission` | @`updateCommissionSettings` |
| (نفس الشاشة) | `POST /settings/suppliers` | `settings.storeSupplier` | @`storeSupplier` |
| (نفس الشاشة) | `GET  /settings/suppliers/{id}/delete` | `settings.destroySupplier` | @`destroySupplier` |
| (نفس الشاشة) | `POST /settings/companies` | `settings.storeCompany` | @`storeCompany` |
| (نفس الشاشة) | `GET  /settings/companies/{id}/delete` | `settings.destroyCompany` | @`destroyCompany` |
| (نفس الشاشة) | `POST /settings/stations` | `settings.storeStation` | @`storeStation` |
| (نفس الشاشة) | `GET  /settings/stations/{id}/delete` | `settings.destroyStation` | @`destroyStation` |
| (نفس الشاشة) | `POST /settings/items` | `settings.storeItem` | @`storeItem` |
| (نفس الشاشة) | `GET  /settings/items/{id}/delete` | `settings.destroyItem` | @`destroyItem` |
| (نفس الشاشة) | `POST /settings/expense-categories` | `settings.storeExpenseCategory` | [FinanceController](app/Http/Controllers/FinanceController.php)@`storeExpenseCategory` |
| (نفس الشاشة) | `PUT  /settings/expense-categories/{id}` | `settings.updateExpenseCategory` | [FinanceController](app/Http/Controllers/FinanceController.php)@`updateExpenseCategory` |
| (نفس الشاشة) | `GET  /settings/expense-categories/{id}/delete` | `settings.destroyExpenseCategory` | [FinanceController](app/Http/Controllers/FinanceController.php)@`destroyExpenseCategory` |
| (نفس الشاشة) | `POST /settings/system` | `settings.system.update` | @`updateSystemSettings` |
| (نفس الشاشة) | `POST /settings/store-deduction` · `GET /settings/delete-deduction/{id}` | — | @`storeDeduction` · @`destroyDeduction` |

### 👤 المستخدمين والبروفايل
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| (داخل [settings.blade.php](resources/views/settings.blade.php) أو شاشة منفصلة) | `GET /users` | `users.index` | [Authcontroller](app/Http/Controllers/Authcontroller.php)@`users` |
| — | `POST /users` | `users.store` | @`storeUser` |
| — | `GET /users/{id}/toggle` | `users.toggle` | @`toggleUser` |
| — | `POST /users/{id}/reset-password` | `users.resetPassword` | @`resetPassword` |
| — | `GET /users/{id}/delete` | `users.destroy` | @`destroyUser` |
| — | `POST /profile/update-name` | `profile.updateName` | @`updateProfile` |

---

## 8) سجل العمليات والتدقيق

### 🔍 سجل التدقيق (Audit Log) — أدمن فقط
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [audit_log.blade.php](resources/views/audit_log.blade.php) | `GET /audit-log` · `GET /audit-log/{id}` | `audit.index` · `audit.show` | [AuditLogController](app/Http/Controllers/AuditLogController.php)@`index` · @`show` |

### 🧾 سجل العمليات (Operations Log) — تعديل/حذف/مرتجع
| الشاشة | URL | Route name | Controller@method |
|---|---|---|---|
| [operations_log.blade.php](resources/views/operations_log.blade.php) | `GET /operations-log` | `operations.index` | [OperationsLogController](app/Http/Controllers/OperationsLogController.php)@`index` |
| (نفس الشاشة) | `POST /operations-log/{editor}/{id}/show` | `operations.show` | @`show` *(يدعم: fuel, inventory_purchase, sale_inventory, sale_direct, service, **sale_return**, expense)* |
| (نفس الشاشة) | `POST /operations-log/{editor}/{id}/delete` | `operations.destroy` | @`destroy` *(للبنزينة والمصروف فقط — حذف نهائي)* |
| (نفس الشاشة) | `POST /operations-log/{editor}/{id}` | `operations.update` | @`update` *(يدعم تعديل البنزينة/التوريد/المصروف، حذف بيعة، ومرتجع `sale_return`)* |

---

## 9) مساعدات شائعة

### 🧩 Partials (مكونات Blade مُضمَّنة في الشاشات)
| الملف | المهمة |
|---|---|
| [partials/theme.blade.php](resources/views/partials/theme.blade.php) | متغيرات الـ CSS العامة (ألوان، خطوط، أحجام) — مُضمَّن في كل شاشة |
| [partials/debt_row.blade.php](resources/views/partials/debt_row.blade.php) | صف ديْن (يستخدم في `debts.blade.php` و `debts2.blade.php`) |
| [partials/inquiry_card.blade.php](resources/views/partials/inquiry_card.blade.php) | كارت استفسار (يستخدم في `inquiries.blade.php`) |
| [partials/global_form_handler.blade.php](resources/views/partials/global_form_handler.blade.php) | معالج فورمات عام (loading + double submit prevention) |
| [partials/print_helper.blade.php](resources/views/partials/print_helper.blade.php) | دالة طباعة عامة (للفواتير والتفاصيل) |
| [sidebar.blade.php](resources/views/sidebar.blade.php) | القائمة الجانبية + تنبيه الرصيد المنخفض |

### ⚙️ Services (منطق مشترك)
| الـ Service | الملف | المسؤولية |
|---|---|---|
| **InstallmentFinanceService** | [app/Services/InstallmentFinanceService.php](app/Services/InstallmentFinanceService.php) | حساب رأس المال، الأرباح، المصاريف، المرتجعات، الإهلاكات. يستدعى من: `DashboardController`, `ReportController`, `FinanceController`, `ExportController` |
| **AuditService** | [app/Services/AuditService.php](app/Services/AuditService.php) | تسجيل عمليات الـ audit log (warning/critical). يستدعى من: `OperationsLogController` وغيره |
| **SystemSetting** | [app/Services/SystemSetting.php](app/Services/SystemSetting.php) | جلب/حفظ إعدادات النظام (`fuel_profit_rate`, `low_balance_threshold` ...) |
| **CustomerRiskService** | [app/Services/CustomerRiskService.php](app/Services/CustomerRiskService.php) | حساب درجة المخاطر للعملاء |

### 🛡️ Middlewares
| الـ Middleware | الملف | متى يُستخدم |
|---|---|---|
| **AuthMiddleware** (`auth.custom`) | [app/Http/Middleware/AuthMiddleware.php](app/Http/Middleware/AuthMiddleware.php) | حماية كل الصفحات الإدارية |
| **CheckAdmin** | [app/Http/Middleware/CheckAdmin.php](app/Http/Middleware/CheckAdmin.php) | حماية صفحات الأدمن فقط (HR, Partners, Dev) |
| **AdminMiddleware** | [app/Http/Middleware/AdminMiddleware.php](app/Http/Middleware/AdminMiddleware.php) | (بديل/قديم) |
| **CheckRole** | [app/Http/Middleware/CheckRole.php](app/Http/Middleware/CheckRole.php) | فحص دور المستخدم |
| **CustomerPortalAuth** (`portal`) | [app/Http/Middleware/CustomerPortalAuth.php](app/Http/Middleware/CustomerPortalAuth.php) | حماية صفحات portal العميل |
| **AuditRequestMiddleware** | [app/Http/Middleware/AuditRequestMiddleware.php](app/Http/Middleware/AuditRequestMiddleware.php) | تسجيل كل POST مهم في الـ audit log تلقائياً |

### 🎛️ Base Controllers
| الـ Controller | الملف | الدور |
|---|---|---|
| **SystemController** | [app/Http/Controllers/SystemController.php](app/Http/Controllers/SystemController.php) | الأب المشترك — فيه helpers زي `verifyAdminPin`, `applyAccountCommission`, `logActivity` |
| **Controller** | [app/Http/Controllers/Controller.php](app/Http/Controllers/Controller.php) | الأب الأساسي (Laravel) |
| **Concerns/** | [app/Http/Controllers/Concerns/](app/Http/Controllers/Concerns) | traits مشتركة: `AppliesAccountCommission`, `LogsActivity` |

---

## 🔧 إزاي أتعامل مع الخطأ

### لو الخطأ ظهر في شاشة:
1. **افتح الـ blade** (مثلاً `inventory.blade.php`)
2. **ابص في الجدول فوق** — هتلاقي اسم الـ Controller والـ method
3. **افتح الـ Controller** (مثلاً `InventoryController.php`) وروح للـ method
4. **شوف الـ logs** في `storage/logs/laravel.log` — أحدث error هيكون في الآخر

### لو الخطأ في POST (مثلاً Submit فورم):
1. **شوف الـ URL في الـ network tab** (F12)
2. **دور على الـ URL في الـ Route table فوق** هتعرف الـ Controller@method
3. **اقرأ الـ method وشوف الـ validation rules** والـ logic

### لو شاشة مش بتفتح أصلاً:
1. **اتأكد إن الـ route موجود** بـ `php artisan route:list | grep <اسم_الـ route>`
2. **اتأكد إن الـ controller method موجود** ومش `private`
3. **مسح الـ caches**:
   ```bash
   php artisan view:clear
   php artisan route:clear
   php artisan config:clear
   ```

### لو الـ blade compiled قديم:
```bash
php artisan view:clear
```

### لو غيّرت route:
```bash
php artisan route:clear
```

---

## 📂 خريطة الجداول الأساسية (Database)

| الجدول | الاستخدام الأساسي |
|---|---|
| `users` | المستخدمين (admin/employee) |
| `customers` | العملاء (للـ portal والأرشيف) |
| `installments` | **مهم جداً** — كل عقد بيع (نقدي أو تقسيط)، فيه `category` = مبيعات مباشرة/مبيعات مخزن/خدمات/بنزينة/عهد ومصروفات |
| `installment_payments` | دفعات الأقساط (والمقدم بيتسجل هنا) |
| `installment_expenses` | مصاريف إضافية على عقد (نقل/تركيب/خامات) |
| `sales` | باتشات المخزن (`inventory_status='to_inventory'`) + سجل البيع المباشر (`inventory_status='sold'`) |
| `sale_returns` | مرتجعات العملاء (تاب "مرتجعات العملاء" في المخزن) |
| `inventory_movements` | حركات المخزن: `customer_return`, `supplier_return`, `transfer`, `adjustment_up/down`, `damage` |
| `financial_transactions` | كل حركة فلوس (income/settlement/expense/general_expense/salary_expense/discount/commission/transfer) — `status` = active/cancelled |
| `accounts` | الخزائن والمحافظ (`category` = bank_wallet/safe_cash/project_sector) |
| `company_debts` | الديون على الشركة (موردين، عمولات، استقطاعات، وقود) + الديون اللي قائمة |
| `fuel_transactions` | عمليات البنزينة (مع `supersedes`/`superseded_by` للتعديل من سجل العمليات) |
| `fuel_deductions` | نسب الاستقطاعات على عمليات البنزينة |
| `transport_companies` · `gas_stations` · `items` · `suppliers` | مرجعيات (lookup tables) |
| `expense_categories` | فئات المصروفات |
| `assets` | الأصول الثابتة + الإهلاكات |
| `partners` | الشركاء ورؤوس أموالهم |
| `employees` · `hr_transactions` | الموارد البشرية |
| `goals` | الأهداف |
| `customer_inquiries` | استفسارات العملاء |
| `activity_logs` | رادار النشاطات (يظهر في شاشة العمليات المالية → تاب الرادار) |
| `audit_logs` | سجل التدقيق التفصيلي (شاشة Audit Log) |
| `operation_revisions` | snapshot قبل أي تعديل/حذف من سجل العمليات |

---

## ✍️ كيفية تحديث هذا الملف

لما تضيف أو تعدّل:
- **route جديد** → ضيف صف في القسم المناسب
- **شاشة جديدة** → ضيف صف + اربطها بالـ controller
- **controller method جديدة** → حدّث الجدول
- **جدول database جديد** → ضيفه في خريطة الجداول

ابدأ بمسح الكاش بعد أي تعديل: `php artisan view:clear && php artisan route:clear`

---

> آخر تحديث: 2026-06-14 · شركة الضبع ERP
