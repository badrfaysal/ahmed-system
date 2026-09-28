<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categories = Illuminate\Support\Facades\DB::table('sales')->select('category')->distinct()->pluck('category')->toArray();
print_r($categories);
