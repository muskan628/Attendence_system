<?php
session_start();
include('../includes/db_connect.php');

// TODO: baad ch login ton teacher da naam / id leyo
$teacher_name = "Demo Teacher";

// ---------- GET SELECTED SUBJECT & DATE (from GET) ----------
$selected_subject_code = $_GET['subject'] ?? '';
$selected_date         = $_GET['date'] ?? date('Y-m-d');

// ---------- SUBJECTS DROPDOWN DATA ----------
$subjects = [];
$subSql = "SELECT code, name FROM subjects ORDER BY code";
$subRes = $conn->query($subSql);
if ($subRes && $subRes->num_rows > 0) {
    while ($row = $subRes->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// ---------- LOAD STUDENTS FOR SELECTED SUBJECT ----------
$students = [];

if (!empty($selected_subject_code)) {
    /*
        enrollments: id, student_id, subject_code
        student: id, roll_no, name, department, class_id
    */
    $sqlStudents = "
        SELECT 
            st.id          AS student_id,
            st.roll_no     AS roll_no,
            st.name        AS student_name
        FROM enrollments e
        JOIN student st ON st.id = e.student_id
        WHERE e.subject_code = ?
        ORDER BY st.roll_no
    ";

    $stmt = $conn->prepare($sqlStudents);
    if ($stmt) {
        $stmt->bind_param("s", $selected_subject_code);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $students[] = [
                'id'   => $row['student_id'],
                'roll' => $row['roll_no'],
                'name' => $row['student_name'],
            ];
        }
        $stmt->close();
    }
}

// ---------- SUMMARY CARDS (simple dynamic) ----------
$totalClasses    = count($subjects);       // Total subjects as classes
$totalStudents   = count($students);       // Students in selected subject
$todaysPresent   = 0;                      // TODO: link with attendance
$overallPercent  = "0%";                   // TODO: link with attendance

$summary = [
    ["title" => "Total Classes",          "value" => $totalClasses],
    ["title" => "My Students",            "value" => $totalStudents],
    ["title" => "Today’s Present",        "value" => $todaysPresent],
    ["title" => "My Overall Attendance",  "value" => $overallPercent]
];

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/css/staff_dashboard.css">

    <!-- 🟦 Chart.js CDN (pehle) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- 🟩 Tuhada custom JS (baad ch) -->
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

    <!-- Optional success message -->
    <?php if ($msg === 'success'): ?>
        <div class="alert success">
            Attendance saved successfully!
        </div>
    <?php endif; ?>

    <!-- SUMMARY BOXES -->
    <div class="summary-row">
        <?php foreach ($summary as $row): ?>
            <div class="summary-card">
                <h3><?= htmlspecialchars($row['title']) ?></h3>
                <p><?= htmlspecialchars($row['value']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <h1>Welcome, <?= htmlspecialchars($teacher_name) ?>!</h1>

    <!-- SUBJECT & DATE SELECTION -->
    <div class="class-row">
        <form method="GET" action="">
            <label>Select Subject & Date</label>

            <select name="subject" required>
                <option value="">-- Select Subject --</option>
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?= htmlspecialchars($sub['code']) ?>"
                        <?= ($sub['code'] == $selected_subject_code) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sub['code'] . ' - ' . $sub['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>">
            <button type="submit" class="go">Go</button>
        </form>
    </div>

    <!-- ATTENDANCE TABLE -->
    <div class="table-card">
        <h2>
            <?php if ($selected_subject_code): ?>
                Subject: <?= htmlspecialchars($selected_subject_code) ?> – <?= htmlspecialchars($selected_date) ?>
            <?php else: ?>
                Please select a subject to view students
            <?php endif; ?>
        </h2>

        <?php if (!empty($students)): ?>
            <form method="POST" action="save_attendance.php">
                <input type="hidden" name="subject_code" value="<?= htmlspecialchars($selected_subject_code) ?>">
                <input type="hidden" name="date" value="<?= htmlspecialchars($selected_date) ?>">

                <table>
                    <tr>
                        <th>S. No.</th>
                        <th>Roll No.</th>
                        <th>Student Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Leave</th>
                    </tr>

                    <?php
                    $count = 1;
                    foreach ($students as $s): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($s['roll']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>

                            <!-- Radio buttons: one status per student -->
                            <td>
                                <input type="radio" name="status[<?= htmlspecialchars($s['roll']) ?>]" value="P" checked>
                            </td>
                            <td>
                                <input type="radio" name="status[<?= htmlspecialchars($s['roll']) ?>]" value="A">
                            </td>
                            <td>
                                <input type="radio" name="status[<?= htmlspecialchars($s['roll']) ?>]" value="L">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <button type="submit" class="submit-btn">Submit Attendance</button>
            </form>
        <?php else: ?>
            <?php if ($selected_subject_code): ?>
                <p>No students found for this subject.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- QUICK REPORT (static for now) -->
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
                    <th>Roll No.</th>
                    <th>Absent</th>
                    <th>Attendance %</th>
                </tr>
                <!-- TODO: future – calculate from attendance table -->
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
        © 2025 Akal University | All Rights Reserved
    </footer>

</div>

</body>
</html>
