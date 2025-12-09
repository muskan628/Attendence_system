<?php
// session_start();
include('../includes/db_connect.php');      // DB connection
include('../includes/session_check.php');   // check login

// --------- 1) SUMMARY CARDS (BASIC REAL DATA) ---------

// Total Students (all departments)
$sqlTotalStudents = "SELECT COUNT(*) AS total_students FROM admission";
$res = $conn->query($sqlTotalStudents);
$row = $res ? $res->fetch_assoc() : ['total_students' => 0];
$totalStudents = (int)$row['total_students'];

// For now, Present (Today), Total Absent, Overall % static rakhde aa
// baad ch attendance table ton calculate kar lawange
$presentToday   = 950;
$totalAbsent    = 250;
$overallPercent = "79.17%";

$summary = [
    ["title" => "Total Students",        "value" => $totalStudents],
    ["title" => "Present (Today)",       "value" => $presentToday],
    ["title" => "Total Absent",          "value" => $totalAbsent],
    ["title" => "Overall Attendance %",  "value" => $overallPercent]
];

// --------- 2) ATTENDANCE SUMMARY (ABHI STATIC ARRAY) ---------
// Later: group by department from attendance table
$attendance = [
    ["dept" => "Physics",     "strength" => 300, "absent" => 40, "leave" => 10],
    ["dept" => "Chemistry",   "strength" => 280, "absent" => 70, "leave" => 10],
    ["dept" => "Mathematics", "strength" => 260, "absent" => 60, "leave" => 10]
];

// --------- 3) DEFAULTER SUMMARY (STATIC FOR NOW) ---------
$defaulters = [
    ["dept" => "Physics",     "b50" => 15, "b60" => 20, "b70" => 25, "total" => 70],
    ["dept" => "Chemistry",   "b50" => 30, "b60" => 25, "b70" => 40, "total" => 117],
    ["dept" => "Mathematics", "b50" => 20, "b60" => 15, "b70" => 35, "total" => 70]
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <script src="../assets/js/admin_dashboard.js" defer></script>
</head>

<body>

    <div class="sidebar">
        <h2>Akal University</h2>
        <ul>
            <li class="active">Dashboard</li>
            <li>Detailed Reports</li>
            <li>Defaulter List</li>
            <li onclick="window.location.href='manage_users.php'">Manage Users</li>
            <li onclick="window.location.href='manage_students.php'">Manage Students</li>
            <li onclick="window.location.href='manage_departments.php'">Manage Departments</li>
            <li onclick="window.location.href='manage_teachers.php'">Manage Teachers</li>
            <li onclick="window.location.href='settings.php'">Settings</li>

        </ul>

        <!-- Logout Button -->
        <div class="logout-box">
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="main">
        <h1>Admin Dashboard</h1>

        <!-- ✅ IMPORT SUCCESS ALERT (with tick) -->
        <?php if (isset($_GET['status'])):
            $msg = '';
            if ($_GET['status'] === 'students_imported') {
                $count = isset($_GET['count']) ? (int)$_GET['count'] : 0;
                $msg = $count . " student record(s) imported successfully.";
            } elseif ($_GET['status'] === 'departments_imported') {
                $count = isset($_GET['count']) ? (int)$_GET['count'] : 0;
                $msg = $count . " department(s) imported successfully.";
            } elseif ($_GET['status'] === 'teachers_imported') {
                $count = isset($_GET['count']) ? (int)$_GET['count'] : 0;
                $msg = $count . " teacher(s) imported successfully.";
            }
            if ($msg):
        ?>
                <div class="alert-success" id="import-alert">
                    <div class="alert-icon">✓</div>
                    <div class="alert-text">
                        <strong>Import Complete</strong>
                        <span><?= htmlspecialchars($msg) ?></span>
                    </div>
                    <button class="alert-close" onclick="document.getElementById('import-alert').style.display='none';">×</button>
                </div>
        <?php endif;
        endif; ?>

        <!-- Summary Cards -->
        <div class="summary-box">
            <?php foreach ($summary as $item): ?>
                <div class="card">
                    <p><?= htmlspecialchars($item['title']) ?></p>
                    <h3><?= htmlspecialchars($item['value']) ?></h3>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Attendance Summary -->
        <div class="table-card">
            <h2>University Attendance Summary (Today)</h2>
            <table>
                <tr>
                    <th>Department Name</th>
                    <th>Total Strength</th>
                    <th>Total Absent</th>
                    <th>Total On Leave</th>
                    <th>% of Present</th>
                </tr>

                <?php foreach ($attendance as $row):
                    $present = $row['strength'] - ($row['absent'] + $row['leave']);
                    $percent = $row['strength'] > 0
                        ? round(($present / $row['strength']) * 100, 2)
                        : 0;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['dept']) ?></td>
                        <td><?= (int)$row['strength'] ?></td>
                        <td><?= (int)$row['absent'] ?></td>
                        <td><?= (int)$row['leave'] ?></td>
                        <td><?= $percent ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- CSV IMPORT SECTION -->
        <div class="table-card">
            <h2>Import Data</h2>

            <div class="import-grid">

                <!-- Import Students -->
                <div class="import-card new-import">
                    <div class="import-icon">📄</div>
                    <h3>Import Students</h3>
                    <p>Upload admission CSV file and import into the admission table.</p>

                    <form method="POST" enctype="multipart/form-data" action="import_students.php">
                        <label class="upload-box">
                            <input type="file" name="csv_file" accept=".csv" required>
                            <span class="upload-text">Click to Upload CSV</span>
                        </label>

                        <button type="submit" class="btn-primary big-btn">Upload &amp; Import</button>
                    </form>

                    <div class="view-link-wrap">
                        <a href="manage_students.php" class="btn-outline">
                            View / Manage Students
                        </a>
                    </div>
                </div>

                <!-- Import Departments -->
                <div class="import-card new-import">
                    <div class="import-icon">🏛️</div>
                    <h3>Import Departments</h3>
                    <p>Upload department CSV file and import into the department table.</p>

                    <form method="POST" enctype="multipart/form-data" action="import_departments.php">
                        <label class="upload-box">
                            <input type="file" name="csv_file" accept=".csv" required>
                            <span class="upload-text">Click to Upload CSV</span>
                        </label>

                        <button type="submit" class="btn-primary big-btn">Upload &amp; Import</button>
                    </form>

                    <div class="view-link-wrap">
                        <a href="manage_departments.php" class="btn-outline">
                            View / Manage Departments
                        </a>
                    </div>
                </div>

                <!-- Import Teachers -->
                <div class="import-card new-import">
                    <div class="import-icon">👨‍🏫</div>
                    <h3>Import Teachers</h3>
                    <p>Upload teacher CSV file and import into the teachers table.</p>

                    <form method="POST" enctype="multipart/form-data" action="import_teachers.php">
                        <label class="upload-box">
                            <input type="file" name="csv_file" accept=".csv" required>
                            <span class="upload-text">Click to Upload CSV</span>
                        </label>

                        <button type="submit" class="btn-primary big-btn">Upload &amp; Import</button>
                    </form>

                    <div class="view-link-wrap">
                        <a href="manage_teachers.php" class="btn-outline">
                            View / Manage Teachers
                        </a>
                    </div>
                </div>

            </div>

            <p class="import-note">
                * CSV headers must match database column names.
            </p>
        </div>

        <!-- Defaulter Summary -->
        <div class="table-card">
            <h2>University Defaulter Summary (Overall)</h2>
            <table>
                <tr>
                    <th>Department Name</th>
                    <th>Students &lt;50%</th>
                    <th>60–70%</th>
                    <th>70–75%</th>
                    <th>Total Students &lt;75%</th>
                </tr>

                <?php foreach ($defaulters as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['dept']) ?></td>
                        <td><?= (int)$row['b50'] ?></td>
                        <td><?= (int)$row['b60'] ?></td>
                        <td><?= (int)$row['b70'] ?></td>
                        <td><?= (int)$row['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

</body>

</html>