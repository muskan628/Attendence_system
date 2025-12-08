<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid Request");
}

$type = isset($_POST['export_type']) ? $_POST['export_type'] : '';

if ($type === 'students') {
    $filename = "students_export_" . date('Y-m-d') . ".csv";
    $query = "SELECT * FROM admission";
} elseif ($type === 'departments') {
    $filename = "departments_export_" . date('Y-m-d') . ".csv";
    // Check if table is 'department' or 'departments'
    // Based on settings.php it's 'department'
    $query = "SELECT * FROM department";
} else {
    die("Invalid export type selected.");
}

// Fetch Data
$result = $conn->query($query);
if (!$result) {
    die("Error fetching data: " . $conn->error);
}

// Headers for Download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Column Headers
$fields = $result->fetch_fields();
$headers = [];
foreach ($fields as $field) {
    $headers[] = $field->name;
}
fputcsv($output, $headers);

// Rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
