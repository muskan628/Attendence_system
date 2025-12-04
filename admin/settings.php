<?php
include('../includes/db_connect.php');
include('../includes/session_check.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings</title>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
<style>
.settings-wrapper {
  max-width: 1000px;
  margin: 30px auto;
  background: #fefcfb;
  padding: 25px;
  border-radius: 14px;
}
.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.settings-header h1 {
  margin: 0;
}
.settings-section {
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}
.settings-section h2 {
  margin: 0 0 6px 0;
  font-size: 18px;
}
.settings-section p {
  margin: 0 0 10px 0;
  font-size: 14px;
  color: #4b5563;
}
.badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 11px;
  margin-left: 8px;
}
.badge-danger {
  background: #fee2e2;
  color: #b91c1c;
}
.form-row-inline {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: flex-end;
}
.form-group {
  display: flex;
  flex-direction: column;
  min-width: 180px;
}
.form-group label {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 3px;
}
.form-group select,
.form-group input[type="text"],
.form-group input[type="number"] {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 13px;
}
.btn-danger {
  display: inline-block;
  padding: 8px 14px;
  border-radius: 8px;
  border: none;
  background: #dc2626;
  color: #fff;
  font-size: 14px;
  cursor: pointer;
  font-weight: 600;
}
.btn-danger:hover {
  opacity: 0.9;
}
.cleanup-note {
  margin-top: 6px;
  font-size: 12px;
  color: #6b7280;
}
.trash-link {
  margin-top: 10px;
  font-size: 14px;
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
        <li class="active">Settings</li>
    </ul>
    
    <!-- Logout Button -->
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
  <div class="settings-wrapper">
    <div class="settings-header">
      <h1>Settings</h1>
      <a href="admin_dashboard.php" class="btn-outline">← Back to Dashboard</a>
    </div>

    <!-- ✅ Student Cleanup Alert -->
    <?php if (isset($_GET['cleanup'])):
        $msg = '';
        if ($_GET['cleanup'] === 'temp_trash') {
            $msg = "Selected students moved to Temporary Trash.";
        } elseif ($_GET['cleanup'] === 'permanent') {
            $msg = "Selected students permanently deleted.";
        } elseif ($_GET['cleanup'] === 'none') {
            $msg = "No student matched the selected criteria.";
        }
        if ($msg):
    ?>
      <div class="alert-success" id="cleanup-alert" style="margin-top:15px;">
        <div class="alert-icon">✓</div>
        <div class="alert-text">
          <strong>Student Cleanup</strong>
          <span><?= htmlspecialchars($msg); ?></span>
        </div>
        <button class="alert-close" onclick="document.getElementById('cleanup-alert').style.display='none';">×</button>
      </div>
    <?php endif; endif; ?>

    <!-- ✅ Department Cleanup Alert -->
    <?php if (isset($_GET['dept_cleanup'])):
        $msg = '';
        $dept = isset($_GET['dept']) ? $_GET['dept'] : '';

        if ($_GET['dept_cleanup'] === 'temp_trash') {
            $msg = "Department '$dept' moved to Temporary Trash.";
        } elseif ($_GET['dept_cleanup'] === 'permanent') {
            $msg = "Department '$dept' permanently deleted.";
        } elseif ($_GET['dept_cleanup'] === 'none') {
            $msg = "Department not found or already deleted.";
        }
        if ($msg):
    ?>
      <div class="alert-success" id="dept-cleanup-alert" style="margin-top:10px;">
        <div class="alert-icon">✓</div>
        <div class="alert-text">
          <strong>Department Cleanup</strong>
          <span><?= htmlspecialchars($msg); ?></span>
        </div>
        <button class="alert-close" onclick="document.getElementById('dept-cleanup-alert').style.display='none';">×</button>
      </div>
    <?php endif; endif; ?>

    <!-- Link to Trash Manager -->
    <div class="trash-link">
      <a href="manage_trash.php" class="btn-outline">🗑️ Open Trash Manager (Students & Departments)</a>
    </div>

    <!-- 1) Student Data Cleanup -->
    <div class="settings-section">
      <h2>
        Student Data Cleanup
        <span class="badge badge-danger">Danger Zone</span>
      </h2>
      <p>
        Delete <strong>Student</strong> records from <code>admission</code>. 
        You can move them to <strong>Temporary Trash</strong> (stored in <code>trash_admission</code>) 
        or <strong>Permanently Delete</strong> them.
      </p>

      <form method="POST" action="students_cleanup.php"
            onsubmit="return confirm('Are you sure you want to clean up student data as per selected options?');">
        <div class="form-row-inline">

          <!-- Department filter -->
          <div class="form-group">
            <label for="cleanup_department">Department (optional)</label>
            <select id="cleanup_department" name="department">
              <option value="">-- All Departments --</option>
              <?php
              $deptRes = $conn->query("SELECT DEPARTMENT_NAME FROM department ORDER BY DEPARTMENT_NAME ASC");
              while ($d = $deptRes->fetch_assoc()):
              ?>
                <option value="<?= htmlspecialchars($d['DEPARTMENT_NAME']); ?>">
                  <?= htmlspecialchars($d['DEPARTMENT_NAME']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Program filter (optional free text or later dropdown) -->
          <div class="form-group">
            <label for="cleanup_program">Program (optional)</label>
            <input type="text" id="cleanup_program" name="program"
                   placeholder="e.g. BCA, BSc (Hons)">
          </div>

          <!-- Action -->
          <div class="form-group">
            <label for="cleanup_action">Action</label>
            <select id="cleanup_action" name="action" required>
              <option value="">-- Select Action --</option>
              <option value="temp_trash">Move to Temporary Trash</option>
              <option value="permanent_delete">Permanent Delete</option>
            </select>
          </div>

          <!-- Submit -->
          <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn-danger">
              Cleanup Students
            </button>
          </div>
        </div>

        <p class="cleanup-note">
          <strong>Note:</strong> Temporary Trash will copy matching records into 
          <code>trash_admission</code> and then delete them from <code>admission</code>.
          Permanent Delete will directly remove matching records from <code>admission</code>.
        </p>
      </form>
    </div>

    <!-- 2) Department Data Cleanup -->
    <div class="settings-section">
      <h2>
        Department Data Cleanup
        <span class="badge badge-danger">Danger Zone</span>
      </h2>
      <p>
        Delete <strong>Department</strong> records. You can move them to <strong>Temporary Trash</strong> 
        (stored in <code>trash_department</code>) or <strong>Permanently Delete</strong> them.
      </p>

      <form method="POST" action="departments_cleanup.php"
            onsubmit="return confirm('Are you sure you want to delete this department?');">
        <div class="form-row-inline">
          <!-- Department dropdown by ID + Name -->
          <div class="form-group">
            <label for="department_id">Department</label>
            <select id="department_id" name="department_id" required>
              <option value="">-- Select Department --</option>
              <?php
              $deptRes2 = $conn->query("SELECT ID, DEPARTMENT_NAME FROM department ORDER BY DEPARTMENT_NAME ASC");
              while ($d2 = $deptRes2->fetch_assoc()):
              ?>
                <option value="<?= (int)$d2['ID']; ?>">
                  <?= htmlspecialchars($d2['DEPARTMENT_NAME']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Action dropdown -->
          <div class="form-group">
            <label for="dept_action">Action</label>
            <select id="dept_action" name="dept_action" required>
              <option value="">-- Select Action --</option>
              <option value="temp_trash">Move to Temporary Trash</option>
              <option value="permanent_delete">Permanent Delete</option>
            </select>
          </div>

          <!-- Submit -->
          <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn-danger">
              Delete Department
            </button>
          </div>
        </div>

        <p class="cleanup-note">
          <strong>Note:</strong> Temporary Trash will copy the department record to
          <code>trash_department</code> before deleting from <code>department</code>.
          Permanent Delete will directly remove the department record.
        </p>
      </form>
    </div>

  </div>
</div>

</body>
</html>
