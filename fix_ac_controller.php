<?php

$path = 'd:/Projects/ahmed-system/ahmed-system/app/Http/Controllers/AcController.php';
$content = file_get_contents($path);

$target1 = "                    'cash_price'          => \$totalAmount,
                    'down_payment'        => \$paidAmount,
                    'installment_months'  => (\$installmentStatus == 'active') ? 1 : 0,
                    'monthly_installment' => \$remainingAmount,
                    'due_day'             => 1,
                    'status'              => \$installmentStatus,";

$replacement1 = "                    'start_date'          => now()->toDateString(),
                    'cash_price'          => \$totalAmount,
                    'down_payment'        => \$paidAmount,
                    'remaining_after_down'=> \$remainingAmount,
                    'installment_months'  => (\$installmentStatus == 'active') ? 1 : 0,
                    'total_after_interest'=> \$totalAmount,
                    'monthly_installment' => \$remainingAmount,
                    'remaining_balance'   => \$remainingAmount,
                    'due_day'             => 1,
                    'status'              => \$installmentStatus,";

$content_lf = str_replace("\r\n", "\n", $content);
$target1_lf = str_replace("\r\n", "\n", $target1);
$replacement1_lf = str_replace("\r\n", "\n", $replacement1);

if (strpos($content_lf, $target1_lf) !== false) {
    $content_lf = str_replace($target1_lf, $replacement1_lf, $content_lf);
    file_put_contents($path, $content_lf);
    echo "Fixed AcController successfully!\n";
} else {
    echo "Target not found in AcController!\n";
}
