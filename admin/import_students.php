<?php
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

if (!isset($_FILES['csv_file']['name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    die("No CSV uploaded or upload error.");
}

$file = $_FILES['csv_file']['tmp_name'];
if (!file_exists($file)) {
    die("CSV file not found.");
}

$handle = fopen($file, "r");
if (!$handle) {
    die("Cannot open CSV.");
}

/* 1) Read actual columns from DB (skip auto_increment column) */
$dbCols = [];
$res = $conn->query("SHOW COLUMNS FROM admission");
while ($col = $res->fetch_assoc()) {
    if (stripos($col['Extra'], 'auto_increment') !== false) {
        continue; // skip id
    }
    $dbCols[] = $col['Field'];
}
$res->free();

$expectedCols = count($dbCols);

$columns = "`" . implode("`, `", $dbCols) . "`";
$placeholders = rtrim(str_repeat("?,", $expectedCols), ",");

$sqlTemplate = "INSERT INTO admission ($columns) VALUES ($placeholders)";

$isHeader = true;
$rowNumber = 0;
$inserted = 0;

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;

    // skip header row
    if ($isHeader) {
        $isHeader = false;
        continue;
    }

    // skip completely empty lines
    if (count(array_filter($row)) == 0) continue;

    // trim / pad
    if (count($row) > $expectedCols) {
        $row = array_slice($row, 0, $expectedCols);
    } elseif (count($row) < $expectedCols) {
        $row = array_pad($row, $expectedCols, "");
    }

    if (count($row) != $expectedCols) {
        die("Row $rowNumber mismatch: expected $expectedCols, got " . count($row));
    }

    $stmt = $conn->prepare($sqlTemplate);
    if (!$stmt) die("SQL PREPARE ERROR: " . $conn->error);

    $types = str_repeat("s", $expectedCols);
    $bind = [$types];
    for ($i = 0; $i < $expectedCols; $i++) {
        $bind[] = &$row[$i];
    }

    call_user_func_array([$stmt, "bind_param"], $bind);

    if (!$stmt->execute()) {
        die("Insert Error on row $rowNumber → " . $stmt->error);
    }

    $inserted++;
    $stmt->close();
}

fclose($handle);

echo "<h3>$inserted student records imported successfully!</h3>";
echo '<p><a href="admin_dashboard.php">Back to Dashboard</a></p>';
?>
