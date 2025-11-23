<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: settings.php");
    exit();
}

$department = $_POST['department'] ?? '';
$action     = $_POST['action'] ?? '';

$department = trim($department);

if ($department === '' || ($action !== 'temp_trash' && $action !== 'permanent_delete')) {
    header("Location: settings.php");
    exit();
}

// Count matching students
$stmtCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM admission WHERE department = ?");
$stmtCount->bind_param("s", $department);
$stmtCount->execute();
$resCount = $stmtCount->get_result();
$rowCount = $resCount->fetch_assoc();
$totalAffected = (int)$rowCount['cnt'];
$stmtCount->close();

if ($totalAffected === 0) {
    header("Location: settings.php?cleanup=none&dept=" . urlencode($department));
    exit();
}

if ($action === 'temp_trash') {
    // 1) Move into trash_admission
    $sqlMove = "
        INSERT INTO trash_admission 
        (s_no, date_of_admission, tid, department, program, current_class, auid, student_name, father_name, mother_name, gender, date_of_birth, category, contact_no, email, address)
        SELECT s_no, date_of_admission, tid, department, program, current_class, auid, student_name, father_name, mother_name, gender, date_of_birth, category, contact_no, email, address
        FROM admission
        WHERE department = ?
    ";

    $stmtMove = $conn->prepare($sqlMove);
    if (!$stmtMove) {
        die("Prepare failed (move): " . $conn->error);
    }
    $stmtMove->bind_param("s", $department);
    $stmtMove->execute();
    $stmtMove->close();

    // 2) Delete from main table
    $stmtDel = $conn->prepare("DELETE FROM admission WHERE department = ?");
    if (!$stmtDel) {
        die("Prepare failed (delete): " . $conn->error);
    }
    $stmtDel->bind_param("s", $department);
    $stmtDel->execute();
    $stmtDel->close();

    header("Location: settings.php?cleanup=temp_trash&dept=" . urlencode($department) . "&count=" . $totalAffected);
    exit();
}

if ($action === 'permanent_delete') {
    // Permanent delete directly
    $stmtDel = $conn->prepare("DELETE FROM admission WHERE department = ?");
    if (!$stmtDel) {
        die("Prepare failed (delete2): " . $conn->error);
    }
    $stmtDel->bind_param("s", $department);
    $stmtDel->execute();
    $stmtDel->close();

    header("Location: settings.php?cleanup=permanent&dept=" . urlencode($department) . "&count=" . $totalAffected);
    exit();
}
