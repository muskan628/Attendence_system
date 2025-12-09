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
*/
$columns      = "`" . implode("`, `", $dbCols) . "`";
$placeholders = rtrim(str_repeat("?,", $expectedCols), ",");

$sqlTemplate = "INSERT INTO teachers ($columns) VALUES ($placeholders)";

$isHeader    = true;
$rowNumber   = 0;
$inserted    = 0;

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

    // trim or pad row to exact column count
    if (count($row) > $expectedCols) {
        $row = array_slice($row, 0, $expectedCols);
    } elseif (count($row) < $expectedCols) {
        $row = array_pad($row, $expectedCols, "");
    }

    if (count($row) != $expectedCols) {
        die("Row $rowNumber mismatch: expected $expectedCols columns (" . implode(", ", $dbCols) . "), got " . count($row));
    }

    $stmt = $conn->prepare($sqlTemplate);
    if (!$stmt) {
        die("SQL PREPARE ERROR on row $rowNumber: " . $conn->error);
    }

    // Type definition (all strings 's')
    $types = str_repeat("s", $expectedCols);
    $bind  = [$types];

    // convert each value to variable reference
    for ($i = 0; $i < $expectedCols; $i++) {
        $row[$i] = trim($row[$i]);
        $bind[]  = &$row[$i];
    }

    // dynamic bind_param
    call_user_func_array([$stmt, "bind_param"], $bind);

    if (!$stmt->execute()) {
        $stmt->close();
        die("Insert Error on row $rowNumber → " . $stmt->error);
    }

    $inserted++;
    $stmt->close();
}

fclose($handle);

// ✅ After successful import, redirect back to admin dashboard
header("Location: ad_dashboard.php?status=teachers_imported&count=" . $inserted);
exit;
