<?php
session_start();
include('../includes/db_connect.php');

// DEBUG: check connection
if (!$conn) {
    die("DB connection error");
}

// TODO: Later: get this from session (hod login)
// e.g. $hod_department = $_SESSION['department'];
$hod_department = 'Physics';

// ---------- SUMMARY CARDS (REAL DATA) ----------

// 1) Total Classes (distinct current_class in this department)
$sqlTotalClasses = "
    SELECT COUNT(DISTINCT current_class) AS total_classes
    FROM admission
    WHERE department = ?
";
$stmt = $conn->prepare($sqlTotalClasses);
if (!$stmt) {
    die("SQL ERROR (TotalClasses): " . $conn->error);
}
$stmt->bind_param("s", $hod_department);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$totalClasses = $res['total_classes'] ?? 0;

// 2) Total Students in this department
$sqlTotalStudents = "
    SELECT COUNT(*) AS total_students
    FROM admission
    WHERE department = ?
";
$stmt = $conn->prepare($sqlTotalStudents);
if (!$stmt) {
    die("SQL ERROR (TotalStudents): " . $conn->error);
}
$stmt->bind_param("s", $hod_department);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$totalStudents = $res['total_students'] ?? 0;

// 3) For now, attendance not linked -> 0
$deptAttendancePercent = 0;
$defaultersCount = 0;

// Summary array for cards
$summary = [
    ["title" => "Total Classes", "value" => $totalClasses],
    ["title" => "Total Students", "value" => $totalStudents],
    ["title" => "Department Attendance %", "value" => $deptAttendancePercent . "%"],
    ["title" => "Defaulters (<75%)", "value" => $defaultersCount]
];


// ---------- CLASS SUMMARY (from admission table) ----------
// Each current_class in this department, and total students in that class

$sqlClassSummary = "
    SELECT 
        current_class AS class_name,
        COUNT(*) AS total_students
    FROM admission
    WHERE department = ?
    GROUP BY current_class
    ORDER BY current_class
";

$stmt = $conn->prepare($sqlClassSummary);
if (!$stmt) {
    die("SQL ERROR (ClassSummary): " . $conn->error);
}
$stmt->bind_param("s", $hod_department);
$stmt->execute();
$result = $stmt->get_result();

$classSummary = [];
$classFilterOptions = [];

while ($row = $result->fetch_assoc()) {
    $total = (int)$row['total_students'];

    $classSummary[] = [
        "class_name" => $row['class_name'],
        "total"      => $total,
        "present"    => 0,          // TODO: link with attendance table later
        "absent"     => 0,
        "attendance" => "0%"
    ];

    $classFilterOptions[] = $row['class_name'];
}

// remove duplicates just in case
$classFilterOptions = array_unique($classFilterOptions);


// ---------- DEFAULTER SUMMARY (temporary empty) ----------
// Later: fill from attendance summary table / view
$defaulters = []; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="../assets/css/hod_dashboard.css">
    <script src="../assets/js/hod_dashboard.js" defer></script>
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
    <h1>HOD Dashboard (<?= htmlspecialchars($hod_department) ?>)</h1>

    <!-- SUMMARY CARDS -->
    <div class="summary-box">
        <?php foreach ($summary as $item): ?>
            <div class="card">
                <p><?= htmlspecialchars($item['title']) ?></p>
                <h3><?= htmlspecialchars($item['value']) ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FILTERS & BUTTONS -->
    <div class="filter-row">
        <select>
            <option value="">Filter by Date</option>
            <option value="today">Today</option>
            <option value="7days">Last 7 Days</option>
            <option value="month">This Month</option>
        </select>

        <select>
            <option value="">Filter by Class</option>
            <?php foreach ($classFilterOptions as $cls): ?>
                <option value="<?= htmlspecialchars($cls) ?>">
                    <?= htmlspecialchars($cls) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn pdf">Download PDF</button>
        <button class="btn csv">CSV</button>
    </div>

    <!-- CLASS SUMMARY TABLE -->
    <div class="table-card">
        <h2>Class-Wise Summary</h2>
        <table>
            <tr>
                <th>Class Name</th>
                <th>Total Students</th>
                <th>Present Today</th>
                <th>Absent</th>
                <th>Attendance %</th>
                <th>Action</th>
            </tr>

            <?php if (!empty($classSummary)): ?>
                <?php foreach ($classSummary as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                        <td><?= $row['total'] ?></td>
                        <td><?= $row['present'] ?></td>
                        <td><?= $row['absent'] ?></td>
                        <td><?= $row['attendance'] ?></td>
                        <td><button class="view-btn">View Details</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No classes found for this department.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- DEFAULTER TABLE -->
    <div class="table-card">
        <h2>Defaulter Summary (Department-wide)</h2>
        <table>
            <tr>
                <th>Course</th>
                <th>Students &lt;50%</th>
                <th>50-60%</th>
                <th>70-75%</th>
                <th>Total Below 75%</th>
            </tr>

            <?php if (!empty($defaulters)): ?>
                <?php foreach ($defaulters as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                        <td><?= $row['b50'] ?></td>
                        <td><?= $row['b5060'] ?></td>
                        <td><?= $row['b7075'] ?></td>
                        <td><?= $row['total75'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No defaulter data available yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</body>
</html>
