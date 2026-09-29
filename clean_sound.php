<?php
$views_dir = 'd:/Projects/ahmed-system/ahmed-system/resources/views/';
$files = glob($views_dir . '*.blade.php');

$search1 = "<script>if(typeof playSuccessSound === 'function') playSuccessSound();</script>\n";
$search2 = "<script>if(typeof playSuccessSound === 'function') playSuccessSound();</script>";
$search3 = "if(typeof playSuccessSound === 'function') playSuccessSound();\n";
$search4 = "if(typeof playSuccessSound === 'function') playSuccessSound();";

foreach ($files as $p) {
    if (basename($p) == 'global_form_handler.blade.php') continue; // keep it here!
    
    $c = file_get_contents($p);
    $orig = $c;
    $c = str_replace($search1, "", $c);
    $c = str_replace($search2, "", $c);
    if (basename($p) == 'sidebar.blade.php') {
        $c = str_replace($search3, "", $c);
        $c = str_replace($search4, "", $c);
    }
    
    if ($c !== $orig) {
        file_put_contents($p, $c);
        echo "Cleaned " . basename($p) . "\n";
    }
}
echo "Cleanup done.\n";
