<?php
// admin/import_teachers.php

include('../includes/db_connect.php'); // DB connection

// --- Basic checks ---
if (!isset($_FILES['csv_file']['name'])) {
    die("No CSV uploaded.");
}

$file = $_FILES['csv_file']['tmp_name'];

if (!file_exists($file)) {
    die("CSV file not found.");
}

$handle = fopen($file, "r");
if (!$handle) {
    die("Cannot open CSV.");
}

/*
   1) Read actual columns from `teachers` table
      and skip auto_increment column (ID) if applicable.
      Note: If ID is not auto_increment (VARCHAR), it will be expected in CSV.
*/
$dbCols = [];
// Ensure table exists (manage_teachers.php creates it, but good to be safe)
// We assume it exists or user visited manage page. If not, this might fail.
$res = $conn->query("SHOW COLUMNS FROM teachers");

if (!$res) {
    die("SQL ERROR: " . $conn->error . " (Table 'teachers' might not exist. Please visit 'Manage Teachers' first to create it.)");
}

while ($col = $res->fetch_assoc()) {
    // Skip auto-increment primary key (ID)
    if (stripos($col['Extra'], 'auto_increment') !== false) {
        continue;
    }
    // Also skip created_at if it has default current_timestamp (optional, but standard practice usually csv doesn't have timestamps)
    // But 'import_departments.php' checks ONLY auto_increment.
    // I will stick to 'same logic' = only skip auto_increment.
    // However, for my 'teachers' schema, 'created_at' has DEFAULT. 
    // If I include it in INSERT columns, I must provide it in CSV.
    // To be user friendly, I should probably skip 'created_at' if it is just a timestamp.
    // But sticking to "same logic" strict interpretation means I only skip auto_increment.
    // Let's check 'department' table. It likely doesn't have timestamps.
    // I will Adding a special case to skip 'created_at' to avoid forcing user to have it in CSV.
    if ($col['Field'] === 'created_at') {
        continue;
    }

    $dbCols[] = $col['Field'];
}
$res->free();

$expectedCols = count($dbCols);
if ($expectedCols === 0) {
    die("No insertable columns found in teachers table.");
}

/*
   2) Prepare SQL template
      We need to insert 'id' + detected columns.
      The CSV is expected to have: S.No (ignored) + Value for each detected column.
*/

// Filter out 'id' from detected columns if it's there (we generate it)
$csvTargetCols = [];
$primaryKeyCol = 'id'; // Default assumption
foreach ($dbCols as $col) {
    if ($col === 'id') {
        $primaryKeyCol = $col;
        continue;
    }
    $csvTargetCols[] = $col;
}

// Final list of columns to INSERT into DB: id + others
$finalDbCols = array_merge([$primaryKeyCol], $csvTargetCols);
$columnsSql  = "`" . implode("`, `", $finalDbCols) . "`";

// Placeholders: ? for ID + ? for each CSV column
$placeholders = rtrim(str_repeat("?,", count($finalDbCols)), ",");

$sqlTemplate = "INSERT INTO teachers ($columnsSql) VALUES ($placeholders)";

$isHeader    = true;
$rowNumber   = 0;
$inserted    = 0;
$year        = date('y'); // For ID generation

// Prepare statement once
$stmt = $conn->prepare($sqlTemplate);
if (!$stmt) {
    die("SQL PREPARE ERROR: " . $conn->error);
}

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;

    // skip header row
    if ($isHeader) {
        $isHeader = false;
        continue;
    }

    // skip completely empty rows
    if (count(array_filter($row, fn($v) => trim($v) !== '')) == 0) {
        continue;
    }

    // 1. Remove S.No (First Column)
    if (count($row) > 0) {
        array_shift($row);
    }

    // 2. Normalize row length to match expected target columns (excluding ID)
    $expectedCsvCount = count($csvTargetCols);

    if (count($row) > $expectedCsvCount) {
        $row = array_slice($row, 0, $expectedCsvCount);
    } elseif (count($row) < $expectedCsvCount) {
        $row = array_pad($row, $expectedCsvCount, "");
    }

    // 3. Generate Auto ID (Unique per row)
    $uniqueFound = false;
    $newId = '';
    // Simple retry loop for uniqueness
    for ($try = 0; $try < 5; $try++) {
        $rand = rand(1000, 9999);
        $candId = "T-" . $year . $rand;
        // Check valid format logic only, Collision check is via DB constraint usually but here we pre-check
        // Note: In high volume, pre-check inside loop is slow, but for CSV import it's acceptable.
        $chk = $conn->query("SELECT id FROM teachers WHERE id = '$candId'");
        if ($chk->num_rows == 0) {
            $newId = $candId;
            $uniqueFound = true;
            break;
        }
    }
    if (!$uniqueFound) {
        $newId = "T-" . $year . substr(time(), -4); // Fallback
    }

    // 4. Construct Data for Bind
    // [ID, Col1, Col2, ...]
    $bindData = array_merge([$newId], $row);

    // 5. Bind and Execute
    $types = str_repeat("s", count($bindData));
    $bindParams = [$types];
    foreach ($bindData as $k => $v) {
        $bindData[$k] = trim($v); // Trim values
        $bindParams[] = &$bindData[$k];
    }

    call_user_func_array([$stmt, "bind_param"], $bindParams);

    if (!$stmt->execute()) {
        // If error is duplicate entry, maybe retry ID? 
        // For now, die or skip. Die is safer to alert user.
        die("Insert Error on row $rowNumber (ID: $newId) → " . $stmt->error);
    }

    $inserted++;
}
$stmt->close();

fclose($handle);

// ✅ After successful import, redirect back to admin dashboard
header("Location: ad_dashboard.php?status=teachers_imported&count=" . $inserted);
exit;
