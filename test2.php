<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = Illuminate\Support\Facades\DB::table('sales')->where('inventory_status', 'to_inventory')->where('remaining_quantity', '>', 0)->where('category', 'تكييفات')->get();
echo "Count: " . count($items) . "\n";
foreach($items as $i) echo $i->product_name . "\n";
