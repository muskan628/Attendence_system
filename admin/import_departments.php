<?php
// admin/import_departments.php

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
   1) Read actual columns from `department` table
      and skip auto_increment column (ID)
*/
$dbCols = [];
$res = $conn->query("SHOW COLUMNS FROM department");

if (!$res) {
    die("SQL ERROR: " . $conn->error);
}

while ($col = $res->fetch_assoc()) {
    // Skip auto-increment primary key (ID)
    if (stripos($col['Extra'], 'auto_increment') !== false) {
        continue;
    }
    $dbCols[] = $col['Field'];
}
$res->free();

$expectedCols = count($dbCols);  // for your table = 2 (Hod_id, DEPARTMENT_NAME)
if ($expectedCols === 0) {
    die("No insertable columns found in department table.");
}

/*
   2) Prepare SQL template
*/
$columns      = "`" . implode("`, `", $dbCols) . "`";
$placeholders = rtrim(str_repeat("?,", $expectedCols), ",");

$sqlTemplate = "INSERT INTO department ($columns) VALUES ($placeholders)";

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
        die("Row $rowNumber mismatch: expected $expectedCols columns, got " . count($row));
    }

    $stmt = $conn->prepare($sqlTemplate);
    if (!$stmt) {
        die("SQL PREPARE ERROR on row $rowNumber: " . $conn->error);
    }

    // sab nu string treat kar lainde aa, simple rakhde
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
// so the green tick alert can show up
header("Location: admin_dashboard.php?status=departments_imported&count=" . $inserted);
exit;
