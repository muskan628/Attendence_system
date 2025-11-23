<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: settings.php");
    exit();
}

$department_id = $_POST['department_id'] ?? '';
$action        = $_POST['dept_action'] ?? '';

if (!is_numeric($department_id) || ($action !== 'temp_trash' && $action !== 'permanent_delete')) {
    header("Location: settings.php");
    exit();
}

$department_id = (int)$department_id;

// 1) Check department exists
$stmtDept = $conn->prepare("SELECT ID, DEPARTMENT_NAME FROM department WHERE ID = ?");
$stmtDept->bind_param("i", $department_id);
$stmtDept->execute();
$resDept = $stmtDept->get_result();
$dept    = $resDept->fetch_assoc();
$stmtDept->close();

if (!$dept) {
    header("Location: settings.php?dept_cleanup=none");
    exit();
}

$deptName = $dept['DEPARTMENT_NAME'];

if ($action === 'temp_trash') {
    // Move to trash_department
    $stmtTrash = $conn->prepare("
        INSERT INTO trash_department (department_id, department_name)
        VALUES (?, ?)
    ");
    if (!$stmtTrash) {
        die("Prepare failed (trash dept): " . $conn->error);
    }
    $stmtTrash->bind_param("is", $department_id, $deptName);
    $stmtTrash->execute();
    $stmtTrash->close();

    // Optionally: also move its students to trash_admission before deleting?
    // (optional step, comment/uncomment as per requirement)
    /*
    $sqlMoveStudents = "
        INSERT INTO trash_admission
        (s_no, date_of_admission, tid, department, program, current_class, auid, student_name, father_name, mother_name, gender, date_of_birth, category, contact_no, email, address)
        SELECT s_no, date_of_admission, tid, department, program, current_class, auid, student_name, father_name, mother_name, gender, date_of_birth, category, contact_no, email, address
        FROM admission
        WHERE department = ?
    ";
    $stmtMoveStu = $conn->prepare($sqlMoveStudents);
    $stmtMoveStu->bind_param("s", $deptName);
    $stmtMoveStu->execute();
    $stmtMoveStu->close();

    $stmtDelStu = $conn->prepare("DELETE FROM admission WHERE department = ?");
    $stmtDelStu->bind_param("s", $deptName);
    $stmtDelStu->execute();
    $stmtDelStu->close();
    */

    // Now delete department from main table
    $stmtDelDept = $conn->prepare("DELETE FROM department WHERE ID = ?");
    $stmtDelDept->bind_param("i", $department_id);
    $stmtDelDept->execute();
    $stmtDelDept->close();

    header("Location: settings.php?dept_cleanup=temp_trash&dept=" . urlencode($deptName));
    exit();
}

if ($action === 'permanent_delete') {

    // First (optional) delete students linked with this department
    // agar students vi delete karne ne:
    /*
    $stmtDelStu = $conn->prepare("DELETE FROM admission WHERE department = ?");
    $stmtDelStu->bind_param("s", $deptName);
    $stmtDelStu->execute();
    $stmtDelStu->close();
    */

    // Now permanent delete department
    $stmtDelDept = $conn->prepare("DELETE FROM department WHERE ID = ?");
    $stmtDelDept->bind_param("i", $department_id);
    $stmtDelDept->execute();
    $stmtDelDept->close();

    header("Location: settings.php?dept_cleanup=permanent&dept=" . urlencode($deptName));
    exit();
}
