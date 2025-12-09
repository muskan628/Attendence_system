<?php
// test_auto_id.php
include('includes/db_connect.php');

echo "Testing Auto ID Generation...\n";

$year = date('y');
$uniqueFound = false;
$newId = '';

for ($i = 0; $i < 10; $i++) {
    $rand = rand(1000, 9999);
    $newId = "T-" . $year . $rand;
    $chk = $conn->query("SELECT id FROM teachers WHERE id = '$newId'");
    if ($chk->num_rows == 0) {
        $uniqueFound = true;
        break;
    }
}

if ($uniqueFound) {
    echo "Generated ID: " . $newId . "\n";
    if (preg_match('/^T-\d{2}\d{4}$/', $newId)) {
        echo "Format Check: PASS\n";
    } else {
        echo "Format Check: FAIL\n";
    }
} else {
    echo "Failed to generate unique ID\n";
}
