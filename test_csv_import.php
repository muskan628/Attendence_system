<?php
// test_csv_import.php
include('includes/db_connect.php');

echo "Testing CSV Import Logic...\n";

// 1. Create Dummy CSV
$csvFile = 'test_teachers.csv';
$fp = fopen($csvFile, 'w');
fputcsv($fp, ['S.No', 'Name', 'Email', 'Department', 'Designation']); // Header
fputcsv($fp, ['1', 'ImportTest1', 'imp1@test.com', 'CS', 'Prof']);
fputcsv($fp, ['2', 'ImportTest2', 'imp2@test.com', 'Math', 'Lecturer']);
fclose($fp);

echo "Created CSV: $csvFile\n";

// 2. Simulate Import Logic (Copied from import_teachers.php)
// Detect Columns
$dbCols = [];
$res = $conn->query("SHOW COLUMNS FROM teachers");
while ($col = $res->fetch_assoc()) {
    if (stripos($col['Extra'], 'auto_increment') !== false) continue;
    if ($col['Field'] === 'created_at') continue;
    $dbCols[] = $col['Field'];
}

// Prepare
$csvTargetCols = [];
$primaryKeyCol = 'id';
foreach ($dbCols as $col) {
    if ($col === 'id') {
        $primaryKeyCol = $col;
        continue;
    }
    $csvTargetCols[] = $col;
}

$finalDbCols = array_merge([$primaryKeyCol], $csvTargetCols);
$columnsSql  = "`" . implode("`, `", $finalDbCols) . "`";
$placeholders = rtrim(str_repeat("?,", count($finalDbCols)), ",");
$sqlTemplate = "INSERT INTO teachers ($columnsSql) VALUES ($placeholders)";

echo "SQL: $sqlTemplate\n";

$handle = fopen($csvFile, "r");
$year = date('y');
$isHeader = true;
$stmt = $conn->prepare($sqlTemplate);

if (!$stmt) die("Prepare Failed: " . $conn->error . "\n");

while (($row = fgetcsv($handle)) !== false) {
    if ($isHeader) {
        $isHeader = false;
        continue;
    }
    if (count($row) == 0) continue;

    // Simulate "Ignore S.No"
    array_shift($row);

    // Generate ID
    $rand = rand(1000, 9999);
    $newId = "T-" . $year . $rand;

    // Bind
    $bindData = array_merge([$newId], $row);
    // Bind logic simplified for test (assuming all strings)
    $types = str_repeat("s", count($bindData));
    $bindParams = [$types];
    foreach ($bindData as $k => $v) {
        $bindData[$k] = trim($v);
        $bindParams[] = &$bindData[$k];
    }
    call_user_func_array([$stmt, "bind_param"], $bindParams);

    if ($stmt->execute()) {
        echo "Inserted Row: $newId\n";
    } else {
        echo "Insert Failed: " . $stmt->error . "\n";
    }
}
fclose($handle);

// 3. Clean up
unlink($csvFile);
echo "Test Complete.\n";
