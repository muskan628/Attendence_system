<?php
include('../includes/session_check.php');
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$auid          = $_POST['auid'] ?? '';
$student_name  = $_POST['student_name'] ?? '';
$father_name   = $_POST['father_name'] ?? '';
$mother_name   = $_POST['mother_name'] ?? '';
$department    = $_POST['department'] ?? '';
$program       = $_POST['program'] ?? '';
$current_class = $_POST['current_class'] ?? '';
$gender        = $_POST['gender'] ?? '';
$category      = $_POST['category'] ?? '';

if ($id <= 0 || empty($auid) || empty($student_name)) {
    die("Required fields missing.");
}

$sql = "UPDATE admission
        SET auid = ?, 
            student_name = ?, 
            father_name = ?, 
            mother_name = ?, 
            department = ?, 
            program = ?, 
            current_class = ?, 
            gender = ?, 
            category = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param(
    "sssssssssi",
    $auid,
    $student_name,
    $father_name,
    $mother_name,
    $department,
    $program,
    $current_class,
    $gender,
    $category,
    $id
);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_students.php?msg=" . urlencode("Student updated successfully."));
    exit();
} else {
    die("Update failed: " . $stmt->error);
}
