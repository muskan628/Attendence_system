<?php
include('../includes/db_connect.php');

$tables = [];
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    $tables[] = $row[0];
}

foreach($tables as $table) {
    echo "TABLE: $table\n";
    $cols = $conn->query("SHOW COLUMNS FROM $table");
    while($col = $cols->fetch_assoc()) {
        echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "\n";
}
?>
