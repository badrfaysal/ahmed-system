<?php

if (!function_exists('fmtMoney')) {
    /**
     * تنسيق المبالغ المالية مع إظهار الكسور (القروش) فقط عند وجودها.
     * 5000  => "5,000"      |  5000.5 => "5,000.50"
     * بدون تقريب للجنيه — بيحافظ على القروش الحقيقية في العرض.
     */
    function fmtMoney($val): string
    {
        $v = (float) $val;
        // round(.,2) بيتعامل مع أخطاء الفاصلة العائمة (مثلاً 100.00000001)
        $v = round($v, 2);
        return fmod($v, 1.0) === 0.0
            ? number_format($v, 0, '.', ',')
            : number_format($v, 2, '.', ',');
    }
}

if (!function_exists('finMask')) {
    /**
     * يغلّف رقم مالي حساس بميزة الإخفاء الاختياري (users.hide_financials).
     * لو الموظف الحالي مقيّد: الرقم بيتعرض متنقّط افتراضيًا مع أيقونة عين لإظهاره/إخفائه لحظيًا
     * (بدون إعادة تحميل الصفحة) — راجع window.toggleFinMask في sidebar.blade.php.
     */
    function finMask(string $displayValue): string
    {
        $user = session('auth_user');
        $isHidden = $user && (int) ($user->hide_financials ?? 0) === 1;

        if (!$isHidden) {
            return e($displayValue);
        }

        $real = e($displayValue);
        return '<span class="fin-mask">'
             . '<span class="fin-text" data-real="' . $real . '" data-masked="••••••">••••••</span>'
             . '<i class="fa fa-eye fin-eye-toggle" onclick="toggleFinMask(this, event)" title="إظهار/إخفاء الرقم"></i>'
             . '</span>';
    }
}

if (!function_exists('isDeveloperUser')) {
    /**
     * التحقق مما إذا كان المستخدم المسجل حالياً هو حساب المطور (Developer).
     */
    function isDeveloperUser($user = null): bool
    {
        $user = $user ?: session('auth_user');
        if (!$user) {
            return false;
        }
        $id    = (int) ($user->id ?? 0);
        $name  = strtolower(trim((string) ($user->name ?? '')));
        $email = strtolower(trim((string) ($user->email ?? '')));
        $role  = strtolower(trim((string) ($user->role ?? '')));

        return $id === 10
            || $name === 'developer'
            || str_contains($name, 'developer')
            || str_contains($name, 'ديفولبر')
            || str_contains($name, 'مطور')
            || str_contains($email, 'dev')
            || $role === 'developer';
    }
}

if (!function_exists('ensureSupplierCreditSchema')) {
    /**
     * التأكد من وجود عمود balance_credit في cont_directory وجدول سجل حركات الرصيد الدائن للموردين.
     */
    function ensureSupplierCreditSchema(): void
    {
        static $checked = false;
        if ($checked && !app()->runningUnitTests()) {
            return;
        }
        $checked = true;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('cont_directory') && !\Illuminate\Support\Facades\Schema::hasColumn('cont_directory', 'balance_credit')) {
                \Illuminate\Support\Facades\Schema::table('cont_directory', function ($table) {
                    $table->decimal('balance_credit', 15, 2)->default(0)->after('balance_debt');
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('cont_supplier_credit_logs')) {
                \Illuminate\Support\Facades\Schema::create('cont_supplier_credit_logs', function ($table) {
                    $table->id();
                    $table->string('person_name')->index();
                    $table->string('person_type')->default('supplier');
                    $table->string('tx_type')->default('overpayment'); // overpayment | return_credit | auto_deduct | cash_refund
                    $table->decimal('amount', 15, 2)->default(0);
                    $table->decimal('balance_after', 15, 2)->default(0);
                    $table->string('invoice_ref')->nullable();
                    $table->unsignedBigInteger('project_id')->nullable();
                    $table->unsignedBigInteger('debt_id')->nullable();
                    $table->unsignedBigInteger('account_id')->nullable();
                    $table->text('notes')->nullable();
                    $table->string('created_by')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignore schema check errors if DB not ready
        }
    }
}

if (!function_exists('addSupplierCredit')) {
    /**
     * إضافة رصيد دائن لنا عند المورد/الفني (نتيجة دفع مبلغ أكبر من المتبقي في الديون أو مرتجع خامات).
     */
    function addSupplierCredit(string $personName, float $amount, string $txType = 'overpayment', string $notes = '', array $meta = []): float
    {
        ensureSupplierCreditSchema();
        $personName = trim($personName);
        $amount = round($amount, 2);
        if ($personName === '' || $amount <= 0.001) {
            return 0.0;
        }

        $db = \Illuminate\Support\Facades\DB::class;
        $dirRow = $db::table('cont_directory')->where('name', $personName)->lockForUpdate()->first();
        if (!$dirRow) {
            $personType = $meta['person_type'] ?? 'supplier';
            $dirId = $db::table('cont_directory')->insertGetId([
                'type'           => $personType,
                'name'           => $personName,
                'balance_debt'   => 0,
                'balance_credit' => 0,
                'notes'          => 'أضيف تلقائياً من تسويات الديون',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $dirRow = $db::table('cont_directory')->where('id', $dirId)->first();
        }

        // إذا طُلب صراحةً خصم ديون مقاولات أخرى مفتوحة أولاً
        $remainingCredit = $amount;
        if (!empty($meta['deduct_open_cont_debts'])) {
            $openDebts = $db::table('company_debts')
                ->where('creditor_name', $personName)
                ->where('remaining_balance', '>', 0)
                ->where(function ($q) {
                    $q->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
                      ->orWhere('category', 'مقاولات')
                      ->orWhere('reason', 'like', '%مشروع:%')
                      ->orWhere('reason', 'like', '%فاتورة خامات%');
                })
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($openDebts as $od) {
                if ($remainingCredit <= 0.001) break;
                $odRem = (float) $od->remaining_balance;
                $deduct = min($remainingCredit, $odRem);
                $db::table('company_debts')->where('id', $od->id)->update([
                    'paid_amount'       => round((float) $od->paid_amount + $deduct, 2),
                    'remaining_balance' => max(0, round($odRem - $deduct, 2)),
                    'updated_at'        => now(),
                ]);
                $remainingCredit = round($remainingCredit - $deduct, 2);
            }
        }

        $liveContDebt = (float) $db::table('company_debts')
            ->where('creditor_name', $personName)
            ->where(function ($q) {
                $q->whereIn('source_type', ['cont_operation', 'cont_operation_invoice'])
                  ->orWhere('category', 'مقاولات')
                  ->orWhere('reason', 'like', '%مشروع:%')
                  ->orWhere('reason', 'like', '%فاتورة خامات%');
            })
            ->sum('remaining_balance');
        $newBalanceCredit = round((float) ($dirRow->balance_credit ?? 0) + $remainingCredit, 2);

        $db::table('cont_directory')->where('id', $dirRow->id)->update([
            'balance_debt'   => max(0, $liveContDebt),
            'balance_credit' => $newBalanceCredit,
            'updated_at'     => now(),
        ]);

        if ($remainingCredit > 0.001) {
            $user = session('auth_user');
            $db::table('cont_supplier_credit_logs')->insert([
                'person_name'   => $personName,
                'person_type'   => $dirRow->type ?? 'supplier',
                'tx_type'       => $txType,
                'amount'        => $remainingCredit,
                'balance_after' => $newBalanceCredit,
                'invoice_ref'   => $meta['invoice_ref'] ?? null,
                'project_id'    => $meta['project_id'] ?? null,
                'debt_id'       => $meta['debt_id'] ?? null,
                'account_id'    => $meta['account_id'] ?? null,
                'notes'         => $notes ?: 'تسجيل رصيد دائن لنا عند المورد',
                'created_by'    => $user?->name ?? 'الإدارة',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        return $remainingCredit;
    }
}

if (!function_exists('consumeSupplierCredit')) {
    /**
     * خصم تلقائي من رصيدنا الدائن عند المورد/الفني عند تسجيل فاتورة أو عملية جديدة له.
     */
    function consumeSupplierCredit(string $personName, float $maxNeeded, string $notes = '', array $meta = []): float
    {
        ensureSupplierCreditSchema();
        $personName = trim($personName);
        $maxNeeded = round($maxNeeded, 2);
        if ($personName === '' || $maxNeeded <= 0.001) {
            return 0.0;
        }

        $db = \Illuminate\Support\Facades\DB::class;
        $dirRow = $db::table('cont_directory')
            ->where('name', $personName)
            ->where('balance_credit', '>', 0)
            ->lockForUpdate()
            ->first();

        if (!$dirRow) {
            return 0.0;
        }

        $avail = (float) $dirRow->balance_credit;
        if ($avail <= 0.001) {
            return 0.0;
        }

        $used = round(min($avail, $maxNeeded), 2);
        $newCredit = max(0, round($avail - $used, 2));

        $db::table('cont_directory')->where('id', $dirRow->id)->update([
            'balance_credit' => $newCredit,
            'updated_at'     => now(),
        ]);

        $user = session('auth_user');
        $db::table('cont_supplier_credit_logs')->insert([
            'person_name'   => $personName,
            'person_type'   => $dirRow->type ?? 'supplier',
            'tx_type'       => 'auto_deduct',
            'amount'        => $used,
            'balance_after' => $newCredit,
            'invoice_ref'   => $meta['invoice_ref'] ?? null,
            'project_id'    => $meta['project_id'] ?? null,
            'debt_id'       => $meta['debt_id'] ?? null,
            'account_id'    => $meta['account_id'] ?? null,
            'notes'         => $notes ?: "خصم تلقائي من رصيدنا لدى المورد ({$personName})",
            'created_by'    => $user?->name ?? 'الإدارة',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return $used;
    }
}
