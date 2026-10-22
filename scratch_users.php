<?php
require 'd:/Projects/ahmed-system/ahmed-system/vendor/autoload.php';
$app = require_once 'd:/Projects/ahmed-system/ahmed-system/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$users = DB::table('users')->get();
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role} | Active: {$u->is_active}\n";
    // check common passwords
    $passwords = ['G12@750a', 'admin', '123456', '12345678', 'password', 'G13#78b'];
    foreach ($passwords as $p) {
        if (Hash::check($p, $u->password)) {
            echo "   --> Password matches: {$p}\n";
        }
    }
}
