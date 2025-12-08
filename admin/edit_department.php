<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');

// --------- 1) Get department by ID ----------
// --------- 1) Get department by ID ----------
if (!isset($_GET['id'])) {
    die("Invalid department ID.");
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT ID, DEPARTMENT_NAME FROM department WHERE ID = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$dept   = $result->fetch_assoc();
$stmt->close();

if (!$dept) {
    die("Department not found.");
}

// --------- 2) Handle form submit (update) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['department_name'] ?? '');

    if ($name === '') {
        $error = "Department name is required.";
    } else {
        // Old name store kar laye (message layi)
        $oldName = $dept['DEPARTMENT_NAME'];

        $stmt = $conn->prepare("UPDATE department SET DEPARTMENT_NAME = ? WHERE ID = ?");
        if (!$stmt) {
            die("Prepare failed (update): " . $conn->error);
        }

        $stmt->bind_param("ss", $name, $id);

        if ($stmt->execute()) {
            $stmt->close();

            // Redirect with success + old/new name
            $old = urlencode($oldName);
            $new = urlencode($name);
            header("Location: manage_departments.php?updated=1&old=$old&new=$new");
            exit();
        } else {
            $error = "Update failed: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Department</title>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<style>
.edit-wrapper {
  max-width: 600px;
  margin: 30px auto;
  background: #fefcfb;
  padding: 25px;
  border-radius: 14px;
}
.edit-wrapper h2 {
  margin-top: 0;
}
.edit-form .form-group {
  margin-bottom: 12px;
}
.edit-form label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 4px;
}
.edit-form input[type="text"] {
  width: 100%;
  padding: 8px 10px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 14px;
}
.edit-form .btn-row {
  margin-top: 16px;
  display: flex;
  gap: 10px;
}
.error-msg {
  color: #b91c1c;
  margin-bottom: 10px;
}
.old-name {
  font-size: 14px;
  margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="main">
  <div class="edit-wrapper">
    <h2>Edit Department</h2>

    <p class="old-name">
      <strong>Old Name:</strong>
      <?= htmlspecialchars($dept['DEPARTMENT_NAME']); ?>
    </p>

    <?php if (!empty($error)): ?>
      <p class="error-msg"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" class="edit-form">
      <div class="form-group">
        <label>New Department Name</label>
        <input type="text" name="department_name"
               value="<?= htmlspecialchars($dept['DEPARTMENT_NAME']); ?>"
               required>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn-primary">Save Changes</button>
        <a href="manage_departments.php" class="btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
