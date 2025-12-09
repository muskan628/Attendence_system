<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');
include('../includes/id_helper.php');

// ✅ Self-Healing: Create teachers table if not exists, or add missing columns
$checkTable = $conn->query("SHOW TABLES LIKE 'teachers'");
if ($checkTable->num_rows == 0) {
    $sql = "CREATE TABLE teachers (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        department VARCHAR(100),
        designation VARCHAR(100),
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql)) {
        die("Error creating teachers table: " . $conn->error);
    }
} else {
    // Table exists, check if 'created_at' exists
    $colCheck = $conn->query("SHOW COLUMNS FROM teachers LIKE 'created_at'");
    if ($colCheck->num_rows == 0) {
        // Add the column if missing
        $conn->query("ALTER TABLE teachers ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
}

/* ---------- DETECT SCHEMA ---------- */
$colName = 'name';
$colDept = 'department';
$colPhone = 'phone';
$resCols = $conn->query("SHOW COLUMNS FROM teachers");
if ($resCols) {
    while ($r = $resCols->fetch_assoc()) {
        if ($r['Field'] === 'name_of_employee') $colName = 'name_of_employee';
        if ($r['Field'] === 'dn') $colDept = 'dn';
        if ($r['Field'] === 'mobile_number') $colPhone = 'mobile_number';
    }
}

/* ---------- ADD NEW TEACHER ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $dept  = trim($_POST['department']);
    $desig = trim($_POST['designation']);

    if ($name !== '') {
        // Generate Custom ID (T-XXXX)
        // Note: For existing table with integer ID, this might fail if ID is auto-increment int
        // The user's table has `id` as int(11). My `generateCustomId` makes strings T-XXXX.
        // This will likely fail or cast to 0.
        // We should check if ID is auto-increment.

        $isAutoInc = false;
        $resCols = $conn->query("SHOW COLUMNS FROM teachers LIKE 'id'");
        if ($r = $resCols->fetch_assoc()) {
            if (stripos($r['Extra'], 'auto_increment') !== false) $isAutoInc = true;
        }

        if ($isAutoInc) {
            // Let DB handle ID
            $stmt = $conn->prepare("INSERT INTO teachers ($colName, email, $colDept, designation) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $dept, $desig);
        } else {
            // Use custom ID
            $newId = generateCustomId($conn, 'T', 'teachers', 'id', 4);
            $stmt = $conn->prepare("INSERT INTO teachers (id, $colName, email, $colDept, designation) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $newId, $name, $email, $dept, $desig);
        }

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_teachers.php?added=1");
    exit();
}

/* ---------- DELETE ---------- */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_teachers.php?deleted=1");
    exit();
}

/* ---------- FETCH DEPARTMENTS (For Dropdown) ---------- */
// Try plural 'departments' first, then singular 'department'
$depts = [];
$deptTable = 'departments';
$res = $conn->query("SHOW TABLES LIKE 'departments'");
if ($res->num_rows == 0) $deptTable = 'department';

$dRes = $conn->query("SELECT * FROM $deptTable");
if ($dRes) {
    while ($row = $dRes->fetch_assoc()) {
        // Handle different column names (name vs DEPARTMENT_NAME)
        $dName = isset($row['name']) ? $row['name'] : (isset($row['DEPARTMENT_NAME']) ? $row['DEPARTMENT_NAME'] : '');
        if ($dName) $depts[] = $dName;
    }
}

/* ---------- FETCH LIST ---------- */
$result = $conn->query("SELECT * FROM teachers ORDER BY id DESC");

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <style>
        .manage-wrapper {
            max-width: 1000px;
            margin: 30px auto;
            background: #fefcfb;
            padding: 25px;
            border-radius: 14px;
        }

        .manage-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .manage-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            background: #f9fafb;
            padding: 15px;
            border-radius: 10px;
        }

        .manage-form input,
        .manage-form select {
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            flex: 1;
        }

        .manage-form button {
            padding: 8px 22px;
            border-radius: 6px;
            border: none;
            background: #e53935;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .dept-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .dept-table th,
        .dept-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 14px;
            text-align: left;
        }

        .dept-table th {
            background: #111827;
            color: #fff;
        }

        .action-icons a {
            margin-right: 8px;
            text-decoration: none;
        }

        .action-icons .delete {
            color: #e11d48;
        }

        .action-icons .edit {
            color: #374151;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Akal University</h2>
        <ul>
            <li onclick="window.location.href='ad_dashboard.php'">Dashboard</li>
            <li>Detailed Reports</li>
            <li>Defaulter List</li>
            <li>Manage Users</li>
            <li onclick="window.location.href='manage_students.php'">Manage Students</li>
            <li onclick="window.location.href='manage_departments.php'">Manage Departments</li>
            <li class="active">Manage Teachers</li>
            <li onclick="window.location.href='settings.php'">Settings</li>
        </ul>

        <div class="logout-box">
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="manage-wrapper">
            <div class="manage-header">
                <h2>Manage Teachers</h2>
            </div>

            <!-- Add Teacher Form -->
            <form class="manage-form" method="POST">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="department" required>
                    <option value="">Select Department</option>
                    <?php foreach ($depts as $d): ?>
                        <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="designation" placeholder="Designation (e.g. Assistant Professor)">
                <button type="submit">Add Teacher</button>
            </form>

            <!-- List -->
            <table class="dept-table">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Actions</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()):
                    // MAP DB Columns to UI
                    $tName  = isset($row['name']) ? $row['name'] : $row['name_of_employee'];
                    $tEmail = $row['email'];
                    $tDept  = isset($row['department']) ? $row['department'] : $row['dn'];
                    $tDesig = $row['designation'];
                    $tId    = $row['id'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($tId); ?></td>
                        <td><?= htmlspecialchars($tName); ?></td>
                        <td><?= htmlspecialchars($tEmail); ?></td>
                        <td><?= htmlspecialchars($tDept); ?></td>
                        <td><?= htmlspecialchars($tDesig); ?></td>
                        <td class="action-icons">
                            <a href="manage_teachers.php?delete=<?= $tId; ?>" class="delete"
                                onclick="return confirm('Delete this teacher?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

</body>

</html>