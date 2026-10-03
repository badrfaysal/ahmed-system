<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$results = []; foreach(DB::select("SHOW TABLES") as $table) { $tName = array_values((array)$table)[0]; foreach(DB::select("SHOW COLUMNS FROM ".$tName) as $col) { if(in_array($col->Field, ["notes", "reason", "description", "details", "item_name", "product_name"]) && strpos($col->Type, "varchar") !== false) { $results[] = $tName . "." . $col->Field; } } } echo json_encode($results);
