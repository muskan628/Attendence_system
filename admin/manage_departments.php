<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');

/* ---------- ADD NEW DEPARTMENT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department_name'])) {
    $name = trim($_POST['department_name']);

    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO department (name) VALUES (?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_departments.php");
    exit();
}

/* ---------- DELETE ---------- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM department WHERE id = $id");
    header("Location: manage_departments.php");
    exit();
}

/* ---------- FETCH LIST ---------- */
$result = $conn->query("SELECT * FROM department ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Departments</title>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<style>
.manage-wrapper {
  max-width: 900px;
  margin: 30px auto;
  background: #fefcfb;
  padding: 25px;
  border-radius: 14px;
}
.manage-header {
  text-align: center;
  margin-bottom: 20px;
}
.manage-header h2 {
  margin: 0;
}
.manage-form {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}
.manage-form input[type="text"] {
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
.manage-form button:hover {
  opacity: .9;
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
.dept-table th:nth-child(1) { width: 60px; }
.dept-table th:nth-child(3) { width: 120px; }

.action-icons a {
  margin-right: 8px;
  text-decoration: none;
  font-size: 16px;
}
.action-icons .delete {
  color: #e11d48;
}
.action-icons .edit {
  color: #374151;
}

/* reuse alert style if not already in CSS */
.alert-success {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #ecfdf3;
  border: 1px solid #4ade80;
  color: #166534;
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 15px;
  box-shadow: 0 2px 5px #00000010;
}
.alert-icon {
  width: 26px;
  height: 26px;
  border-radius: 999px;
  border: 2px solid #22c55e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
}
.alert-text {
  display: flex;
  flex-direction: column;
  font-size: 14px;
}
.alert-text strong {
  font-size: 15px;
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
        <li class="active">Manage Departments</li>
        <li onclick="window.location.href='settings.php'">Settings</li>
    </ul>
    
    <!-- Logout Button -->
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
  <div class="manage-wrapper">
    <div class="manage-header">
      <h2>Manage Departments</h2>
    </div>

    <!-- ✅ Update success message (old → new) -->
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): 
        $old = isset($_GET['old']) ? $_GET['old'] : '';
        $new = isset($_GET['new']) ? $_GET['new'] : '';
    ?>
      <div class="alert-success">
        <div class="alert-icon">✓</div>
        <div class="alert-text">
          <strong>Department Updated</strong>
          <span>
            Name changed from <b><?= htmlspecialchars($old); ?></b>
            to <b><?= htmlspecialchars($new); ?></b>.
          </span>
        </div>
      </div>
    <?php endif; ?>

    <!-- Add Department Form -->
    <form class="manage-form" method="POST">
      <input type="text" name="department_name" placeholder="Enter Department Name" required>
      <button type="submit">Add</button>
    </form>

    <!-- Department List Table -->
    <table class="dept-table">
      <tr>
        <th>#</th>
        <th>Department Name</th>
        <th>Actions</th>
      </tr>

      <?php
      $i = 1;
      while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++; ?></td>
          <td><?= htmlspecialchars($row['name']); ?></td>
          <td class="action-icons">
            <a href="edit_department.php?id=<?= $row['id']; ?>" class="edit" title="Edit">Edit</a>
            <a href="manage_departments.php?delete=<?= $row['id']; ?>" class="delete"
               onclick="return confirm('Delete this department?');"
               title="Delete">
               Delete
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>

</body>
</html>
