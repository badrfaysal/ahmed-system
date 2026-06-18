<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة indexes على الأعمدة اللي بتتفلتر كتير عشان تتحول من Full Table Scan
     * إلى Index Range Scan. مهم جداً مع كبر حجم الداتا.
     */
    public function up(): void
    {
        $this->addIndex('installments', 'idx_inst_category',                  ['category']);
        $this->addIndex('installments', 'idx_inst_sale_type',                 ['sale_type']);
        $this->addIndex('installments', 'idx_inst_status',                    ['status']);
        $this->addIndex('installments', 'idx_inst_months_remaining',          ['installment_months', 'remaining_balance']);
        $this->addIndex('installments', 'idx_inst_category_remaining',        ['category', 'remaining_balance']);
        $this->addIndex('installments', 'idx_inst_customer_name',             ['customer_name']);
        $this->addIndex('installments', 'idx_inst_customer_phone',            ['customer_phone']);
        $this->addIndex('installments', 'idx_inst_created_at',                ['created_at']);
        $this->addIndex('installments', 'idx_inst_status_months',             ['status', 'installment_months']);

        $this->addIndex('installment_payments', 'idx_pay_installment_id',     ['installment_id']);
        $this->addIndex('installment_payments', 'idx_pay_payment_date',       ['payment_date']);

        $this->addIndex('company_debts', 'idx_cd_category',                   ['category']);
        $this->addIndex('company_debts', 'idx_cd_remaining',                  ['remaining_balance']);
        $this->addIndex('company_debts', 'idx_cd_creditor',                   ['creditor_name']);
        $this->addIndex('company_debts', 'idx_cd_category_remaining',         ['category', 'remaining_balance']);
        $this->addIndex('company_debts', 'idx_cd_created_at',                 ['created_at']);

        $this->addIndex('financial_transactions', 'idx_ft_type_status',       ['type', 'status']);
        $this->addIndex('financial_transactions', 'idx_ft_subtype',           ['subtype']);  // عمود جديد - راجع migration الترقية
        $this->addIndex('financial_transactions', 'idx_ft_from_account',      ['from_account_id']);
        $this->addIndex('financial_transactions', 'idx_ft_to_account',        ['to_account_id']);
        $this->addIndex('financial_transactions', 'idx_ft_created_at',        ['created_at']);
        $this->addIndex('financial_transactions', 'idx_ft_person',            ['person_name']);

        $this->addIndex('accounts', 'idx_acc_category',                       ['category']);

        $this->addIndex('sales', 'idx_sales_inventory_status',                ['inventory_status', 'remaining_quantity']);
        $this->addIndex('sales', 'idx_sales_category',                        ['category']);
        $this->addIndex('sales', 'idx_sales_created_at',                      ['created_at']);

        $this->addIndex('fuel_transactions', 'idx_fuel_company',              ['company_id']);
        $this->addIndex('fuel_transactions', 'idx_fuel_station',              ['station_id']);
        $this->addIndex('fuel_transactions', 'idx_fuel_created_at',           ['created_at']);

        $this->addIndex('assets', 'idx_assets_status',                        ['status']);

        $this->addIndex('customer_inquiries', 'idx_inq_contacted_created',    ['is_contacted', 'created_at']);
        $this->addIndex('customer_inquiries', 'idx_inq_phone',                ['customer_phone']);

        $this->addIndex('activity_logs', 'idx_act_is_read',                   ['is_read']);
        $this->addIndex('activity_logs', 'idx_act_created_at',                ['created_at']);

        $this->addIndex('customers', 'idx_cust_phone',                        ['phone']);
        $this->addIndex('customers', 'idx_cust_name',                         ['name']);
    }

    public function down(): void
    {
        // dropping indexes (silent fail OK لو مش موجودة)
    }

    /**
     * يضيف index لو الجدول والأعمدة موجودة والـ index مش متعمل قبل كده.
     */
    private function addIndex(string $table, string $indexName, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        foreach ($columns as $col) {
            if (!Schema::hasColumn($table, $col)) {
                return;
            }
        }
        $exists = DB::select(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
            [$table, $indexName]
        );
        if (!empty($exists)) {
            return;
        }
        try {
            $colList = '`' . implode('`, `', $columns) . '`';
            DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` ({$colList})");
        } catch (\Throwable $e) {
            // عمود مش موجود → نتجاوزه
        }
    }
};
