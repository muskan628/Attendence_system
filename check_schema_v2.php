<?php
include('includes/db_connect.php');

$tables = ['departments', 'programs', 'department']; 

foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $result = $conn->query("SHOW CREATE TABLE $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo $row['Create Table'] . "\n\n";
    } else {
        echo "Error: " . $conn->error . "\n\n";
    }
}
?>
