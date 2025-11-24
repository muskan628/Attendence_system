<?php
session_start();
include('../includes/db_connect.php');

$teacher_name = "Demo Teacher";

// ---------- GET SELECTED SUBJECT & DATE (from GET) ----------
$selected_subject_code = $_GET['subject'] ?? '';
$selected_date         = $_GET['date'] ?? date('Y-m-d');

// ---------- SUBJECTS DROPDOWN DATA ----------
$subjects = [];
$subSql = "SELECT course_code AS code, subject_name AS name FROM subject_details ORDER BY course_code";
$subRes = $conn->query($subSql);
if ($subRes && $subRes->num_rows > 0) {
    while ($row = $subRes->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// ---------- LOAD STUDENTS FOR SELECTED SUBJECT ----------
$students = [];

if (!empty($selected_subject_code)) {
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

// ---------- LOAD ATTENDANCE FOR SELECTED DATE ----------
$attendanceMap = [];

if (!empty($selected_subject_code) && !empty($students)) {
    $sqlAtt = "SELECT roll_no, status FROM attendance WHERE subject_code = ? AND date = ?";
    $stmt = $conn->prepare($sqlAtt);
    if ($stmt) {
        $stmt->bind_param("ss", $selected_subject_code, $selected_date);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $attendanceMap[$row['roll_no']] = $row['status'];
        }
        $stmt->close();
    }
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance - Staff</title>
    <link rel="stylesheet" href="../assets/css/staff_dashboard.css">
    <script src="../assets/js/staff_dashboard.js" defer></script>
    <style>
        /* Minimal override for sidebar links if not present in css */
        .sidebar ul li a {
            text-decoration: none;
            color: inherit;
            display: block;
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Akal University</h2>
    <ul>
        <li><a href="staff_dashboard.php">Dashboard</a></li>
        <li class="active"><a href="mark_attendance.php">Mark Attendance</a></li>
        <li><a href="#">Defaulter List</a></li>
        <li><a href="#">Manage Users</a></li>
        <li><a href="#">Settings</a></li>
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

    <h1>Mark Attendance</h1>

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
                <!-- Redirect back to this page -->
                <input type="hidden" name="redirect_to" value="mark_attendance.php">

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
                        <?php 
                            $currentStatus = $attendanceMap[$s['roll']] ?? 'P';
                        ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($s['roll']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>

                            <td>
                                <input type="radio" name="status[<?= htmlspecialchars($s['roll']) ?>]" value="P" <?= ($currentStatus === 'P') ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <input type="radio" name="status[<?= htmlspecialchars($s['roll']) ?>]" value="A" <?= ($currentStatus === 'A') ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <input type="radio" name="status[<?= htmlspecialchars($s['roll']) ?>]" value="L" <?= ($currentStatus === 'L') ? 'checked' : '' ?>>
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

    <footer>
        © 2025 Akal University | All Rights Reserved
    </footer>

</div>

</body>
</html>
