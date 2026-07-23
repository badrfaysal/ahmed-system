<?php
foreach(DB::select('SHOW TABLES') as $t) {
    $table = array_values((array)$t)[0];
    try {
        foreach(Schema::getColumnListing($table) as $col) {
            $sum = DB::table($table)->whereRaw('cast(' . $col . ' as char) regexp \'^[0-9]+(\.[0-9]+)?$\'')->sum($col);
            if (abs($sum - 907.5) < 0.1) echo "Found 907.5 in $table . $col \n";
        }
    } catch (\Exception $e) {}
}
