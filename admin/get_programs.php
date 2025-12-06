<?php
// get_programs.php
include('../includes/session_check.php');
include('../includes/db_connect.php');

header('Content-Type: application/json');

if (!isset($_GET['department'])) {
    echo json_encode([]);
    exit;
}

$deptName = trim($_GET['department']);
if ($deptName === '') {
    echo json_encode([]);
    exit;
}

// 1. Get Department ID
$stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed (dept)']);
    exit;
}
$stmt->bind_param("s", $deptName);
$stmt->execute();
$res = $stmt->get_result();
$deptRow = $res->fetch_assoc();
$stmt->close();

if (!$deptRow) {
    // Department not found by name
    echo json_encode([]);
    exit;
}

$deptId = $deptRow['id'];

// 2. Get Programs for this Department ID
$stmt = $conn->prepare("SELECT name FROM programs WHERE departmentId = ? ORDER BY name");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed (prog)']);
    exit;
}
$stmt->bind_param("s", $deptId);
$stmt->execute();
$res = $stmt->get_result();

$programs = [];
while ($row = $res->fetch_assoc()) {
    $programs[] = $row['name'];
}
$stmt->close();

echo json_encode($programs);
?>
