<?php
// session_start();
include('../includes/db_connect.php');      // DB connection
include('../includes/session_check.php');   // check login

// --------- 1) SUMMARY CARDS (BASIC REAL DATA) ---------

// Total Students (all departments)
$sqlTotalStudents = "SELECT COUNT(*) AS total_students FROM students";
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
        <li>Manage Users</li>
        <li>Settings</li>
    </ul>
    
    <!-- Logout Button -->
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Admin Dashboard</h1>

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

    <!-- CSV Import Form -->
    <form method="POST" enctype="multipart/form-data" action="import_students.php">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Import CSV</button>
    </form>

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
