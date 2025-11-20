<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');

/* ---------- ADD NEW DEPARTMENT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['department_name'])) {
    $name = trim($_POST['department_name']);
    $hod  = isset($_POST['hod_id']) && $_POST['hod_id'] !== '' ? (int)$_POST['hod_id'] : null;

    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO department (Hod_id, DEPARTMENT_NAME) VALUES (?, ?)");
        $stmt->bind_param("is", $hod, $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_departments.php");
    exit();
}

/* ---------- DELETE ---------- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM department WHERE ID = $id");
    header("Location: manage_departments.php");
    exit();
}

/* ---------- FETCH LIST ---------- */
$result = $conn->query("SELECT * FROM department ORDER BY ID ASC");
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
.manage-form input[type="text"],
.manage-form input[type="number"] {
  padding: 8px 10px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 14px;
}
.manage-form input[name="department_name"] {
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
</style>
</head>
<body>

<div class="main">
  <div class="manage-wrapper">
    <div class="manage-header">
      <h2>Manage Departments</h2>
    </div>

    <!-- Add Department Form (top bar like screenshot) -->
    <form class="manage-form" method="POST">
      <input type="text"   name="department_name" placeholder="Enter Department Name" required>
      <input type="number" name="hod_id" placeholder="HOD ID (optional)">
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
          <td><?= htmlspecialchars($row['DEPARTMENT_NAME']); ?></td>
          <td class="action-icons">
            <!-- Edit icon (open edit page / modal if you create it) -->
            <a href="edit_department.php?id=<?= $row['ID']; ?>" class="edit" title="Edit">&#9998;</a>
            <!-- Delete icon -->
            <a href="manage_departments.php?delete=<?= $row['ID']; ?>" class="delete"
               onclick="return confirm('Delete this department?');"
               title="Delete">
               &#128465;
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>

</body>
</html>
