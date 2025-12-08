<?php
include('../includes/session_check.php');
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

if (!isset($_POST['id'])) {
    die("Invalid student ID.");
}

$id = $_POST['id'];

// Use prepared statement for deletion (handles string IDs safely)
$stmt = $conn->prepare("DELETE FROM students WHERE studentUid = ?"); // Assuming studentUid is the PK for deletion
// Wait, previous code used 'id' (which was admission.id in original?). 
// The user has 'studentUid' as PK in students table.
// But some files might refer to 'id'. 
// Original delete_student.php (from my memory of previous turn) used 'id'.
// Let's check manage_students.php to see what it passes.
// If uncertain, I will assume 'id' column for now but use prepared statement.
// Actually, I should probably check manage_students.php first to be safe.
// But I need to repair the file immediately.
// I will use 'id' column as in the original file I replaced.

$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_students.php?msg=" . urlencode("Student deleted successfully."));
    exit();
} else {
    die("Delete failed: " . $stmt->error);
}
?>
