<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = ['companies', 'users', 'projects', 'master_activities', 'schedules', 'weather_thresholds', 'progress_records', 'audit_logs'];
$sql = "-- CLSU PPSDS Construction System Database Dump\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    if (!Schema::hasTable($table)) continue;
    $create = DB::select("SHOW CREATE TABLE `{$table}`");
    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $sql .= $create[0]->{'Create Table'} . ";\n\n";
    
    $rows = DB::table($table)->get();
    foreach ($rows as $row) {
        $arr = (array)$row;
        $keys = array_map(fn($k) => "`{$k}`", array_keys($arr));
        $values = array_map(function($v) {
            if (is_null($v)) return 'NULL';
            return "'" . addslashes((string)$v) . "'";
        }, array_values($arr));
        $sql .= "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
    }
    $sql .= "\n";
}
$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
file_put_contents(__DIR__ . '/clsu_database_dump.sql', $sql);
echo "SUCCESS: clsu_database_dump.sql created (" . strlen($sql) . " bytes)\n";
