<?php
$path = 'd:/Projects/ahmed-system/ahmed-system/app/Http/Controllers/AcController.php';
$content = file_get_contents($path);

// 1. Exclude project_sector from accounts
$content = str_replace(
    '$accounts = DB::table(\'accounts\')->get();',
    '$accounts = DB::table(\'accounts\')->where(\'category\', \'!=\', \'project_sector\')->get();',
    $content
);

// 2. Change installment_months to 0
$content = str_replace(
    "'installment_months'  => (\$installmentStatus == 'active') ? 1 : 0,",
    "'installment_months'  => 0,",
    $content
);

file_put_contents($path, $content);
echo "AcController modifications successful.\n";
