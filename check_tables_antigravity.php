<?php
include('includes/db_connect.php');

$tables = ['teachers', 'staff', 'users'];
foreach ($tables as $t) {
    if ($conn->query("SELECT 1 FROM $t LIMIT 1")) {
        echo "Table '$t' EXISTS.\n";
        $res = $conn->query("SHOW COLUMNS FROM $t");
        while($r = $res->fetch_assoc()) echo "  - " . $r['Field'] . "\n";
    } else {
        echo "Table '$t' DOES NOT EXIST.\n";
    }
}
?>
