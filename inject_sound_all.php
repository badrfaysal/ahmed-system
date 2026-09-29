<?php
$views_dir = 'd:/Projects/ahmed-system/ahmed-system/resources/views/';
$files = glob($views_dir . '*.blade.php');

$search = "@if(session('success'))";
$replace = "@if(session('success'))\n<script>if(typeof playSuccessSound === 'function') playSuccessSound();</script>\n";

$count = 0;
foreach ($files as $p) {
    if (file_exists($p)) {
        $c = file_get_contents($p);
        $c = str_replace("\r\n", "\n", $c);
        
        if (strpos($c, $search) !== false && strpos($c, "playSuccessSound();</script>") === false) {
            $c = preg_replace("/" . preg_quote($search, "/") . "/", $replace, $c, 1);
            file_put_contents($p, $c);
            $count++;
            echo "Injected into " . basename($p) . "\n";
        }
    }
}
echo "Total injected: $count\n";
