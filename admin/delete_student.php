<?php
include('../includes/session_check.php');
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid student ID.");
}

$id = (int)$_POST['id'];

$sql = "DELETE FROM admission WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_students.php?msg=" . urlencode("Student deleted successfully."));
    exit();
} else {
    die("Delete failed: " . $stmt->error);
}
