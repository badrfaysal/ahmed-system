<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;

trait AppliesAccountCommission
{
    public static function applyAccountCommission(
        int    $account_id,
        float  $amount,
        string $notes,
        string $direction = 'out'
    ): float {
        if ($direction === 'in') {
            return 0;
        }

        if ($amount <= 0) {
            return 0;
        }

        $account = DB::table('accounts')->where('id', $account_id)->first();

        if (!$account || !$account->has_commission) {
            return 0;
        }

        $commission_value = floatval($account->commission_value ?? 0);
        $flat_fee_below   = floatval($account->flat_fee_below  ?? 0);
        $flat_fee_amount  = floatval($account->flat_fee_amount  ?? 0);
        $min_commission   = floatval($account->min_commission   ?? 0);
        $max_commission   = floatval($account->max_commission   ?? 0);

        $fee = 0;

        // لو المبلغ أقل من الحد الأدنى للمبلغ → عمولة ثابتة صغيرة
        if ($flat_fee_below > 0 && $flat_fee_amount > 0 && $amount < $flat_fee_below) {
            $fee = $flat_fee_amount;
        } else {
            if ($account->commission_type === 'percentage') {
                $fee = $amount * ($commission_value / 100);
            } elseif ($account->commission_type === 'fixed_per_1000') {
                $fee = ($amount / 1000) * $commission_value;
            }

            // الحد الأدنى للعمولة
            if ($min_commission > 0 && $fee < $min_commission) {
                $fee = $min_commission;
            }

            // الحد الأقصى للعمولة
            if ($max_commission > 0 && $fee > $max_commission) {
                $fee = $max_commission;
            }
        }

        $fee = round($fee, 2);

        if ($fee > 0) {
            DB::table('accounts')
                ->where('id', $account_id)
                ->decrement('balance', $fee);

            DB::table('financial_transactions')->insert([
                'type'            => 'general_expense',
                'amount'          => $fee,
                'from_account_id' => $account_id,
                'to_account_id'   => null,
                'notes'           => '[عمولة تلقائية - خروج] ' . $notes
                                    . ' | معدل: ' . $commission_value
                                    . ($account->commission_type === 'percentage' ? '%' : ' ج/1000'),
                'status'          => 'active',
                'created_at'      => now(),
            ]);
        }

        return $fee;
    }
}
