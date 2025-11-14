<?php
// SUMMARY CARDS
$summary = [
    ["title" => "Total Classes", "value" => 180],
    ["title" => "My Students", "value" => 180],
    ["title" => "Today’s Present", "value" => 155],
    ["title" => "My Overall Attendance", "value" => "86%"]
];

// STUDENT ATTENDANCE TABLE
$students = [
    ["roll" => "A023PH101", "name" => "Amit Sharma", "att" => 88, "abs" => 20, "leave" => 10],
    ["roll" => "AMIT", "name" => "Amit Sharma", "att" => 72, "abs" => 25, "leave" => 5],
    ["roll" => "DIVYA", "name" => "Divya Singh", "att" => 60, "abs" => 35, "leave" => 10],
    ["roll" => "RAJESH", "name" => "Rajesh Kumar", "att" => 7, "abs" => 35, "leave" => 12],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/css/staff_dashboard.css">
    <script src="../assets/js/staff_dashboard.js" defer></script>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Akal University</h2>
    <ul>
        <li class="active">Dashboard</li>
        <li>Mark Attendance</li>
        <li>Defaulter List</li>
        <li>Manage Users</li>
        <li>Settings</li>
    </ul>
</div>

<!-- MAIN AREA -->
<div class="main">
    <!-- SUMMARY BOXES -->
    <div class="summary-row">
        <?php foreach ($summary as $row): ?>
            <div class="summary-card">
                <h3><?= $row['title'] ?></h3>
                <p><?= $row['value'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <h1>Welcome, [Teacher’s Name]!</h1>

    <!-- CLASS SELECTION -->
    <div class="class-row">
        <label>Select Class & Date</label>
        <input type="text" placeholder="Select Class (e.g., JP Physics)">
        <button class="go">Go</button>
    </div>

    <!-- ATTENDANCE TABLE -->
    <div class="table-card">
        <h2>UG-1 Physics – 01/11/2024</h2>
        <table>
            <tr>
                <th>S. No.</th>
                <th>Student Name</th>
                <th>Absent</th>
                <th>Leave</th>
                <th>Attendance %</th>
            </tr>

            <?php
            $count = 1;
            foreach ($students as $s): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= $s['name'] ?></td>
                    <td><?= $s['abs'] ?></td>
                    <td><?= $s['leave'] ?></td>
                    <td class="<?= $s['att'] >= 75 ? 'green' : ($s['att'] >= 50 ? 'yellow' : 'red') ?>">
                        <?= $s['att'] ?>%
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button class="submit-btn">Submit Attendance</button>
    </div>

    <!-- QUICK REPORT -->
    <div class="report-section">
        <div class="left-box">
            <h2>Quick Class Report</h2>
            <select>
                <option>Select Class</option>
                <option>UG-I Physics</option>
                <option>UG-II Math</option>
            </select>
            <button class="view-report">View Full Report</button>

            <canvas id="chart" width="350" height="150"></canvas>
        </div>

        <div class="right-box">
            <h2>Students Below 75% Attendance</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Absent</th>
                    <th>View %</th>
                </tr>

                <tr>
                    <td>AUID</td>
                    <td>30</td>
                    <td>65%</td>
                </tr>
                <tr>
                    <td>AUIIID</td>
                    <td>35</td>
                    <td>68%</td>
                </tr>
            </table>
        </div>
    </div>

    <footer>
        © 2025 Panjab University | All Rights Reserved
    </footer>

</div>

</body>
</html>
