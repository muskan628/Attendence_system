<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');

/* ------------ HANDLE RESTORE / DELETE (STUDENTS) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Restore Student
    if (isset($_POST['restore_student_id'])) {
        $tid = (int)$_POST['restore_student_id'];

        // Fetch from trash_admission
        $sql = "SELECT * FROM trash_admission WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $tid);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();
        }

        if (!empty($row)) {
            // Insert back into admission
            $insertCols = [];
            $insertVals = [];
            foreach ($row as $col => $val) {
                if ($col === 'id') continue; // trash table ka PK skip
                $insertCols[] = $col;
                $insertVals[] = $val;
            }

            if (!empty($insertCols)) {
                $colsStr = "`" . implode("`,`", $insertCols) . "`";
                $placeholders = rtrim(str_repeat("?,", count($insertCols)), ",");

                $sqlInsert = "INSERT INTO admission ($colsStr) VALUES ($placeholders)";
                $stmtIns = $conn->prepare($sqlInsert);
                if ($stmtIns) {
                    $types = str_repeat("s", count($insertVals));
                    $bind = [$types];
                    foreach ($insertVals as $k => $v) {
                        $bind[] = &$insertVals[$k];
                    }
                    call_user_func_array([$stmtIns, 'bind_param'], $bind);
                    $stmtIns->execute();
                    $stmtIns->close();
                }

                // Delete from trash_admission
                $stmtDel = $conn->prepare("DELETE FROM trash_admission WHERE id = ?");
                $stmtDel->bind_param("i", $tid);
                $stmtDel->execute();
                $stmtDel->close();
            }
        }

        header("Location: manage_trash.php?msg=student_restored");
        exit();
    }

    // Permanently Delete Student
    if (isset($_POST['delete_student_id'])) {
        $tid = (int)$_POST['delete_student_id'];
        $stmt = $conn->prepare("DELETE FROM trash_admission WHERE id = ?");
        $stmt->bind_param("i", $tid);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_trash.php?msg=student_deleted");
        exit();
    }

    // Restore Department
    if (isset($_POST['restore_dept_id'])) {
        $did = (int)$_POST['restore_dept_id'];

        $sql = "SELECT * FROM trash_department WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $did);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();
        }

        if (!empty($row)) {
            $deptName = $row['department_name'];

            // Insert back into department (nava ID milega AUTO_INCREMENT ton)
            $stmtIns = $conn->prepare("INSERT INTO department (DEPARTMENT_NAME) VALUES (?)");
            $stmtIns->bind_param("s", $deptName);
            $stmtIns->execute();
            $stmtIns->close();

            // Remove from trash table
            $stmtDel = $conn->prepare("DELETE FROM trash_department WHERE id = ?");
            $stmtDel->bind_param("i", $did);
            $stmtDel->execute();
            $stmtDel->close();
        }

        header("Location: manage_trash.php?msg=dept_restored");
        exit();
    }

    // Permanently Delete Department (from trash)
    if (isset($_POST['delete_dept_id'])) {
        $did = (int)$_POST['delete_dept_id'];
        $stmt = $conn->prepare("DELETE FROM trash_department WHERE id = ?");
        $stmt->bind_param("i", $did);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_trash.php?msg=dept_deleted");
        exit();
    }
}

/* ------------ FETCH TRASH DATA ---------- */

$trashStudents = $conn->query("SELECT * FROM trash_admission ORDER BY id DESC");
$trashDepts    = $conn->query("SELECT * FROM trash_department ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Trash</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <style>
        .trash-wrapper {
            max-width: 1100px;
            margin: 30px auto;
            background: #fefcfb;
            padding: 25px;
            border-radius: 14px;
        }
        .trash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .trash-header h2 {
            margin: 0;
        }
        .tag-soft {
            padding: 3px 8px;
            border-radius: 999px;
            background: #e5e7eb;
            font-size: 12px;
        }
        .trash-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .trash-table th,
        .trash-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 13px;
            text-align: left;
        }
        .trash-table th {
            background: #111827;
            color: #fff;
        }
        .trash-actions {
            display: flex;
            gap: 6px;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .btn-restore {
            background: #22c55e;
            color: #fff;
        }
        .btn-delete {
            background: #ef4444;
            color: #fff;
        }
        .section-title {
            margin-top: 25px;
            margin-bottom: 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>
<div class="main">
    <div class="trash-wrapper">
        <div class="trash-header">
            <h2>Trash Management</h2>
            <a href="settings.php" class="btn-outline">← Back to Settings</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert-success" style="margin-top:15px;">
                <div class="alert-icon">✓</div>
                <div class="alert-text">
                    <strong>Trash Operation</strong>
                    <span>
                        <?php
                        $m = $_GET['msg'];
                        if ($m === 'student_restored')  echo "Student record restored successfully.";
                        if ($m === 'student_deleted')   echo "Student record permanently deleted.";
                        if ($m === 'dept_restored')     echo "Department restored successfully.";
                        if ($m === 'dept_deleted')      echo "Department permanently deleted.";
                        ?>
                    </span>
                </div>
                <button class="alert-close" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        <?php endif; ?>

        <!-- Students Trash -->
        <h3 class="section-title">
            Students in Trash
            <span class="tag-soft">trash_admission</span>
        </h3>

        <table class="trash-table">
            <tr>
                <th>#</th>
                <th>AUID</th>
                <th>Student Name</th>
                <th>Department</th>
                <th>Program</th>
                <th>Deleted At</th>
                <th>Actions</th>
            </tr>
            <?php if ($trashStudents && $trashStudents->num_rows > 0): 
                $i = 1;
                while ($s = $trashStudents->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($s['auid'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($s['student_name'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($s['department'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($s['program'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($s['deleted_at'] ?? ''); ?></td>
                    <td>
                        <div class="trash-actions">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="restore_student_id" value="<?= (int)$s['id']; ?>">
                                <button class="btn-sm btn-restore" type="submit"
                                        onclick="return confirm('Restore this student?');">
                                    Restore
                                </button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_student_id" value="<?= (int)$s['id']; ?>">
                                <button class="btn-sm btn-delete" type="submit"
                                        onclick="return confirm('Permanently delete this student from trash?');">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr>
                    <td colspan="7">No student records in trash.</td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Departments Trash -->
        <h3 class="section-title">
            Departments in Trash
            <span class="tag-soft">trash_department</span>
        </h3>

        <table class="trash-table">
            <tr>
                <th>#</th>
                <th>Department Name</th>
                <th>Deleted At</th>
                <th>Actions</th>
            </tr>
            <?php if ($trashDepts && $trashDepts->num_rows > 0): 
                $j = 1;
                while ($d = $trashDepts->fetch_assoc()): ?>
                <tr>
                    <td><?= $j++; ?></td>
                    <td><?= htmlspecialchars($d['department_name']); ?></td>
                    <td><?= htmlspecialchars($d['deleted_at'] ?? ''); ?></td>
                    <td>
                        <div class="trash-actions">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="restore_dept_id" value="<?= (int)$d['id']; ?>">
                                <button class="btn-sm btn-restore" type="submit"
                                        onclick="return confirm('Restore this department?');">
                                    Restore
                                </button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_dept_id" value="<?= (int)$d['id']; ?>">
                                <button class="btn-sm btn-delete" type="submit"
                                        onclick="return confirm('Permanently delete this department from trash?');">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr>
                    <td colspan="4">No department records in trash.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</body>
</html>
