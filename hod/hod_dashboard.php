<?php
// -------- SUMMARY STAT DATA ----------
$summary = [
    ["title" => "Total Classes", "value" => 45],
    ["title" => "Total Students", "value" => 550],
    ["title" => "Department Attendance %", "value" => "88.5%"],
    ["title" => "Defaulters (<75%)", "value" => 75]
];

// -------- CLASS SUMMARY DATA ----------
$classSummary = [
    ["dept" => "Physics", "total" => 200, "present" => 180, "absent" => 20, "attendance" => "90%"],
    ["dept" => "Chemistry", "total" => 280, "present" => 210, "absent" => 70, "attendance" => "75%"],
    ["dept" => "Mathematics", "total" => 250, "present" => 225, "absent" => 25, "attendance" => "90%"]
];

// -------- DEFAULTER SUMMARY ----------
$defaulters = [
    ["course" => "PHY-101 Mechanics", "b50" => 5, "b5060" => 8, "b7075" => 12, "total75" => 35],
    ["course" => "CHE-102 Organic", "b50" => 3, "b5060" => 10, "b7075" => 9, "total75" => 22],
    ["course" => "MAT-103 Algebra", "b50" => 4, "b5060" => 6, "b7075" => 8, "total75" => 18]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="hod.css">
    <script src="hod.js" defer></script>
</head>

<body>

<div class="sidebar">
    <h2>Panjab University</h2>
    <ul>
        <li class="active">Dashboard</li>
        <li>Detailed Reports</li>
        <li>Defaulter List</li>
        <li>Manage Users</li>
        <li>Settings</li>
    </ul>
</div>

<div class="main">
    <h1>HOD Dashboard</h1>

    <!-- SUMMARY CARDS -->
    <div class="summary-box">
        <?php foreach ($summary as $item): ?>
            <div class="card">
                <p><?= $item['title'] ?></p>
                <h3><?= $item['value'] ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FILTERS & BUTTONS -->
    <div class="filter-row">
        <select>
            <option>Filter by Date</option>
            <option>Today</option>
            <option>Last 7 Days</option>
            <option>This Month</option>
        </select>

        <select>
            <option>Filter by Class</option>
            <option>Physics</option>
            <option>Chemistry</option>
            <option>Mathematics</option>
        </select>

        <button class="btn pdf">Download PDF</button>
        <button class="btn csv">CSV</button>
    </div>

    <!-- CLASS SUMMARY -->
    <div class="table-card">
        <h2>Class-Wise Summary</h2>
        <table>
            <tr>
                <th>Department Name</th>
                <th>Total Students</th>
                <th>Present Today</th>
                <th>Absent</th>
                <th>Attendance %</th>
                <th>Action</th>
            </tr>

            <?php foreach ($classSummary as $row): ?>
                <tr>
                    <td><?= $row['dept'] ?></td>
                    <td><?= $row['total'] ?></td>
                    <td><?= $row['present'] ?></td>
                    <td><?= $row['absent'] ?></td>
                    <td><?= $row['attendance'] ?></td>
                    <td><button class="view-btn">View Details</button></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- DEFAULTER TABLE -->
    <div class="table-card">
        <h2>Defaulter Summary (Department-wide)</h2>
        <table>
            <tr>
                <th>Course</th>
                <th>Students <50%</th>
                <th>50-60%</th>
                <th>70-75%</th>
                <th>Total Below 75%</th>
            </tr>

            <?php foreach ($defaulters as $row): ?>
                <tr>
                    <td><?= $row['course'] ?></td>
                    <td><?= $row['b50'] ?></td>
                    <td><?= $row['b5060'] ?></td>
                    <td><?= $row['b7075'] ?></td>
                    <td><?= $row['total75'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>

</body>
</html>
