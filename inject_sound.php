<?php
$path = 'd:/Projects/ahmed-system/ahmed-system/resources/views/partials/global_form_handler.blade.php';
$content = file_get_contents($path);

// 1. In global_form_handler.blade.php, find the session('success') block and inject playSuccessSound() directly
$target_success = <<<EOT
        // (O) session success
        @if(session('success'))
EOT;

// Actually wait, let's just use string replace on the exact text.
$content_lf = str_replace("\r\n", "\n", $content);

$target_success_search = "        @if(session('success'))\n            if (isSwal()) {";
$replacement_success = "        @if(session('success'))\n            if (typeof playSuccessSound === 'function') playSuccessSound();\n            if (isSwal()) {";

if (strpos($content_lf, $target_success_search) !== false) {
    $content_lf = str_replace($target_success_search, $replacement_success, $content_lf);
    file_put_contents($path, $content_lf);
    echo "Injected playSuccessSound() into session('success') block.\n";
} else {
    echo "Could not find session('success') block in global_form_handler.\n";
}

// 2. Also inject into debts, installments, expenses views where they have their own session('success') just in case
$views = ['debts.blade.php', 'installments.blade.php', 'expenses.blade.php', 'ac.blade.php', 'debts2.blade.php'];
foreach ($views as $view) {
    $p = "d:/Projects/ahmed-system/ahmed-system/resources/views/" . $view;
    if (file_exists($p)) {
        $c = file_get_contents($p);
        $c = str_replace("\r\n", "\n", $c);
        
        $search = "@if(session('success'))";
        $replace = "@if(session('success'))\n<script>if(typeof playSuccessSound === 'function') playSuccessSound();</script>\n";
        
        // Ensure not already replaced
        if (strpos($c, "playSuccessSound();</script>") === false) {
            // Only replace the first occurrence to avoid duplicates if they have multiple
            $c = preg_replace("/" . preg_quote($search, "/") . "/", $replace, $c, 1);
            file_put_contents($p, $c);
            echo "Injected into $view\n";
        }
    }
}
