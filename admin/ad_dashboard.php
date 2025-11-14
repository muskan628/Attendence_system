<?php
// ADMIN SUMMARY CARDS
$summary = [
    ["title" => "Total Students", "value" => 1200],
    ["title" => "Present (Today)", "value" => 950],
    ["title" => "Total Absent", "value" => 250],
    ["title" => "Overall Attendance %", "value" => "79.17%"]
];

// Attendance Summary Table
$attendance = [
    ["dept" => "Physics", "strength" => 300, "absent" => 40, "leave" => 10],
    ["dept" => "Chemistry", "strength" => 280, "absent" => 70, "leave" => 10],
    ["dept" => "Mathematics", "strength" => 260, "absent" => 60, "leave" => 10]
];

// Defaulter Summary Table
$defaulters = [
    ["dept" => "Physics", "b50" => 15, "b60" => 20, "b70" => 25, "total" => 70],
    ["dept" => "Chemistry", "b50" => 30, "b60" => 25, "b70" => 40, "total" => 117],
    ["dept" => "Mathematics", "b50" => 20, "b60" => 15, "b70" => 35, "total" => 70]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <script src="/assets/js/admin_dashboard.js" defer></script>
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
</div>

<div class="main">
    <h1>Admin Dashboard</h1>

    <!-- Summary Cards -->
    <div class="summary-box">
        <?php foreach ($summary as $item): ?>
            <div class="card">
                <p><?= $item['title'] ?></p>
                <h3><?= $item['value'] ?></h3>
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
                $percent = round(($present / $row['strength']) * 100, 2);
            ?>
                <tr>
                    <td><?= $row['dept'] ?></td>
                    <td><?= $row['strength'] ?></td>
                    <td><?= $row['absent'] ?></td>
                    <td><?= $row['leave'] ?></td>
                    <td><?= $percent ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>
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
                    <td><?= $row['dept'] ?></td>
                    <td><?= $row['b50'] ?></td>
                    <td><?= $row['b60'] ?></td>
                    <td><?= $row['b70'] ?></td>
                    <td><?= $row['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <footer>
        © 2025 Akal University. All Rights Reserved.
    </footer>

</div>
</body>
</html>

