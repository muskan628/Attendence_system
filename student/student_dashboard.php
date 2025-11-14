<?php
// STUDENT OVERALL ATTENDANCE
$overallAttendance = 78;

// COURSE-WISE ATTENDANCE
$courses = [
    [
        "name" => "PHYS-101: Mechanics",
        "attendance" => 62,
        "total" => "XX",
        "attended" => "YY",
        "status" => "Warning"
    ],
    [
        "name" => "MATH-205: Linear Chemistry",
        "attendance" => 91,
        "total" => "XX",
        "attended" => "YY",
        "status" => "Excellent"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/student_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/student_dashboard.js" defer></script>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <img src="https://upload.wikimedia.org/wikipedia/en/0/0b/Punjab_University_logo.png" alt="">
        <h3>Akal UNIVERSITY<br><span>ATTENDANCE MANAGEMENT PORTAL</span></h3>
    </div>

    <ul>
        <li class="active">Dashboard</li>
        <li>Course Schedule</li>
        <li>Profile</li>
    </ul>
</div>

<!-- MAIN AREA -->
<div class="main">
    
    <h1 class="big-title">Overall Attendance!</h1>

    <!-- OVERALL ATTENDANCE CARD -->
    <div class="overall-box">
        <canvas id="overallChart" width="200" height="200"></canvas>
        <p class="sem-title">Current Semester</p>
    </div>

    <!-- COURSE-WISE SECTION -->
    <h2 class="section-title">Course-wise Attendance</h2>

    <div class="course-grid">

        <?php foreach ($courses as $c): ?>
            <div class="course-card">
                <h3><?= $c['name'] ?></h3>
                <canvas class="courseChart" data-percent="<?= $c['attendance'] ?>"></canvas>

                <div class="status-box">
                    <span class="dot <?= strtolower($c['status']) ?>"></span> <?= $c['status'] ?>
                </div>

                <p>Total Classes: <?= $c['total'] ?></p>
                <p>Attended: <?= $c['attended'] ?></p>
            </div>
        <?php endforeach; ?>

    </div>

</div>

</body>
</html>
