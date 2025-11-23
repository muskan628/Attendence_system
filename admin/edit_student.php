<?php
// edit_student.php
include('../includes/session_check.php');
include('../includes/db_connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student ID.");
}

$id = (int)$_GET['id'];

/* ----------------------- Load student with prepared statement ----------------------- */
$sql = "SELECT * FROM admission WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed (student): " . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    die("Student not found.");
}

$student = $result->fetch_assoc();
$stmt->close();

/* ----------------------- Load department for dropdown ----------------------- */
/* Using simple query (no prepare) and escaping output for safety */
$deptOptions = [];
$resDept = $conn->query("SELECT DISTINCT department_name FROM department ORDER BY department_name");
if ($resDept) {
    while ($row = $resDept->fetch_assoc()) {
        $deptOptions[] = $row['department_name'];
    }
} else {
    // If this fails, we'll still show the page and display an error below
    $dept_load_error = "Failed to load department: " . $conn->error;
}

/* ----------------------- Load programs for the student's current department ----------------------- */
$currentDept = $student['department'] ?? '';
$programOptions = [];
if (!empty($currentDept)) {
    // Use real_escape_string to avoid SQL injection and avoid prepare problems
    $deptEscaped = $conn->real_escape_string($currentDept);
    $resProg = $conn->query("SELECT program_name FROM department WHERE department_name = '{$deptEscaped}' ORDER BY program_name");
    if ($resProg) {
        while ($r = $resProg->fetch_assoc()) {
            $programOptions[] = $r['program_name'];
        }
    } else {
        $prog_load_error = "Failed to load programs: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <style>
        .form-row { margin-bottom: 12px; }
        .form-row label { display:block; font-weight:600; margin-bottom:6px; }
        .form-row input, .form-row select { width: 100%; padding:8px; box-sizing:border-box; }
        .form-actions { margin-top:16px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Akal University</h2>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li class="active"><a href="manage_students.php">Manage Students</a></li>
    </ul>
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Edit Student</h1>

    <?php if (!empty($dept_load_error)): ?>
        <div class="alert-error"><?= htmlspecialchars($dept_load_error) ?></div>
    <?php endif; ?>
    <?php if (!empty($prog_load_error)): ?>
        <div class="alert-error"><?= htmlspecialchars($prog_load_error) ?></div>
    <?php endif; ?>

    <div class="table-card">
        <form action="update_student.php" method="POST" class="edit-form">
            <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">

            <div class="form-row">
                <label>AUID</label>
                <input type="text" name="auid" value="<?= htmlspecialchars($student['auid']) ?>" required>
            </div>

            <div class="form-row">
                <label>Student Name</label>
                <input type="text" name="student_name" value="<?= htmlspecialchars($student['student_name']) ?>" required>
            </div>

            <div class="form-row">
                <label>Father Name</label>
                <input type="text" name="father_name" value="<?= htmlspecialchars($student['father_name']) ?>">
            </div>

            <div class="form-row">
                <label>Mother Name</label>
                <input type="text" name="mother_name" value="<?= htmlspecialchars($student['mother_name']) ?>">
            </div>

            <div class="form-row">
                <label>Department</label>
                <select name="department" id="department-select" required>
                    <option value="">Select Department</option>
                    <?php foreach ($deptOptions as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>" <?= ($dept === $student['department']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Program</label>
                <select name="program" id="program-select">
                    <option value="">Select Program</option>
                    <?php foreach ($programOptions as $prog): ?>
                        <option value="<?= htmlspecialchars($prog) ?>" <?= ($prog === $student['program']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prog) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Current Class</label>
                <input type="text" name="current_class" value="<?= htmlspecialchars($student['current_class']) ?>">
            </div>

            <div class="form-row">
                <label>Gender</label>
                <input type="text" name="gender" value="<?= htmlspecialchars($student['gender']) ?>">
            </div>

            <div class="form-row">
                <label>Category</label>
                <input type="text" name="category" value="<?= htmlspecialchars($student['category']) ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="manage_students.php" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('department-select').addEventListener('change', function(){
    const dept = this.value;
    const progSelect = document.getElementById('program-select');

    // Clear current programs
    progSelect.innerHTML = '<option value="">Loading...</option>';

    if (!dept) {
        progSelect.innerHTML = '<option value="">Select Program</option>';
        return;
    }

    // Fetch programs via AJAX
    fetch('get_programs.php?department=' + encodeURIComponent(dept))
        .then(resp => resp.json())
        .then(data => {
            if (!Array.isArray(data)) {
                progSelect.innerHTML = '<option value="">No programs</option>';
                return;
            }
            let html = '<option value="">Select Program</option>';
            data.forEach(p => {
                // escape for safety (basic)
                const escaped = p.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                html += `<option value="${escaped}">${escaped}</option>`;
            });
            progSelect.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            progSelect.innerHTML = '<option value="">Error loading programs</option>';
        });
});
</script>

</body>
</html>
