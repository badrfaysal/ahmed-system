<?php
$p = 'd:/Projects/ahmed-system/ahmed-system/resources/views/sidebar.blade.php';
$c = file_get_contents($p);
$c = str_replace("<script>if(typeof playSuccessSound === 'function') playSuccessSound();</script>", "if(typeof playSuccessSound === 'function') playSuccessSound();", $c);
file_put_contents($p, $c);
echo "Fixed sidebar!\n";
