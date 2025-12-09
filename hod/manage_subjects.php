<?php
// session_start(); // Removed to avoid notice, handled in session_check.php
include('../includes/db_connect.php');
include('../includes/session_check.php');

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM subject_details WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Subject deleted successfully.";
        $msg_type = "success";
    } else {
        $msg = "Error deleting subject: " . $conn->error;
        $msg_type = "error";
    }
    $stmt->close();
}

// Fetch all subjects
$sql = "SELECT * FROM subject_details ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects - HOD Dashboard</title>
    <link rel="stylesheet" href="../assets/css/hod_dashboard.css">
    <!-- Inline CSS for this page specific styles if needed, or add to hod_dashboard.css -->
    <style>
        .container { padding: 20px; margin-left: 250px; } /* Adjust based on sidebar width */
        .table-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: 600; }
        .btn-action { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.9em; margin-right: 5px; }
        .btn-edit { background-color: #ffc107; color: #000; }
        .btn-delete { background-color: #dc3545; color: #fff; }
        .btn-add { background-color: #28a745; color: #fff; padding: 10px 15px; text-decoration: none; display: inline-block; margin-bottom: 15px; border-radius: 5px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<!-- SIDEBAR (Copy from hod_dashboard.php or include a common sidebar file) -->
<div class="sidebar">
    <h2>Akal University</h2>
    <ul>
        <li><a href="hod_dashboard.php" style="color: inherit; text-decoration: none;">Dashboard</a></li>
        <li class="active">Manage Subjects</li>
        <li>Detailed Reports</li>
        <li>Defaulter List</li>
        <li>Manage Users</li>
        <li>Settings</li>
    </ul>
    
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Manage Subjects</h1>

    <?php if (isset($msg)): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <a href="#" class="btn-add" onclick="alert('Feature coming soon: Add Single Subject'); return false;">+ Add New Subject</a>
        
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject Name</th>
                    <th>Type</th>
                    <th>Credits (T/P)</th>
                    <th>Marks (Int/Ext)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= htmlspecialchars($row['course_type']) ?></td>
                            <td><?= $row['theory_credits'] ?> / <?= $row['practical_credits'] ?></td>
                            <td><?= $row['internal_marks'] ?> / <?= $row['theory_marks'] ?></td>
                            <td>
                                <a href="#" class="btn-action btn-edit" onclick="alert('Edit feature coming soon for ID: <?= $row['id'] ?>'); return false;">Edit</a>
                                <a href="manage_subjects.php?delete_id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No subjects found. Please import subjects from the Dashboard.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
