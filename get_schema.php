<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\Schema::getTables();
foreach($tables as $t) {
    $table = $t['name'];
    echo "Table: $table\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumns($table);
    foreach($columns as $c) {
        echo "- {$c['name']} ({$c['type_name']})\n";
    }
    echo "\n";
}
