<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tables = array_map(function($t) { return array_values((array)$t)[0]; }, DB::select("SHOW TABLES"));
echo json_encode($tables);
