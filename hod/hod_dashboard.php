<?php
// session_start();
include('../includes/db_connect.php');
include('../includes/session_check.php'); // je HOD login alag aa ta

// TODO: baad ch login ton HOD da department leyo
// e.g. $hod_department = $_SESSION['department'];
$hod_department = 'Physics';   // TEMP: static rakheya

/* ---------------------------------------------------
   1) SUMMARY CARDS (REAL DATA FROM admission TABLE)
   --------------------------------------------------- */

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
$totalClasses = (int)($res['total_classes'] ?? 0);
$stmt->close();

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
$totalStudents = (int)($res['total_students'] ?? 0);
$stmt->close();

// 3) Department Attendance % (abhi 0 rakheya – baad ch attendance table ton)
$deptAttendancePercent = 0;

// 4) Defaulters Count (abhi 0 – baad ch attendance summary ton)
$defaultersCount = 0;

// Summary array
$summary = [
    ["title" => "Total Classes",            "value" => $totalClasses],
    ["title" => "Total Students",           "value" => $totalStudents],
    ["title" => "Department Attendance %",  "value" => $deptAttendancePercent . "%"],
    ["title" => "Defaulters (<75%)",        "value" => $defaultersCount]
];

/* ---------------------------------------------------
   2) CLASS-WISE SUMMARY (FROM admission TABLE)
   --------------------------------------------------- */

$sqlClassSummary = "
    SELECT 
        current_class AS class_name,
        COUNT(*)      AS total_students
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

    // Abhi attendance link nahi, is karke sab 0
    $classSummary[] = [
        "class_name" => $row['class_name'],
        "total"      => $total,
        "present"    => 0,          // TODO: attendance table ton fill
        "absent"     => 0,
        "attendance" => "0%"
    ];

    $classFilterOptions[] = $row['class_name'];
}

$stmt->close();

// filter dropdown lai unique list
$classFilterOptions = array_unique($classFilterOptions);

/* ---------------------------------------------------
   3) DEFAULTER SUMMARY (PLACEHOLDER)
   --------------------------------------------------- */
// Later: attendance_summary table ya view ton data
$defaulters = [];   // hune empty rakheya

// Optional: import subject success message
$status  = $_GET['status'] ?? '';
$count   = isset($_GET['count']) ? (int)$_GET['count'] : 0;
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

<!-- SIDEBAR -->
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

<!-- MAIN CONTENT -->
<div class="main">
    <h1>HOD Dashboard (<?= htmlspecialchars($hod_department) ?>)</h1>

    <!-- OPTIONAL IMPORT SUCCESS -->
    <?php if ($status === 'subject_imported'): ?>
        <div class="alert-success">
            ✅ <?= $count ?> subject(s) imported successfully.
        </div>
    <?php endif; ?>

    <!-- SUMMARY CARDS -->
    <div class="summary-box">
        <?php foreach ($summary as $item): ?>
            <div class="card">
                <p><?= htmlspecialchars($item['title']) ?></p>
                <h3><?= htmlspecialchars($item['value']) ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- FILTERS ROW (Future use) -->
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
                        <td><?= (int)$row['total'] ?></td>
                        <td><?= (int)$row['present'] ?></td>
                        <td><?= (int)$row['absent'] ?></td>
                        <td><?= htmlspecialchars($row['attendance']) ?></td>
                        <td>
                            <button class="view-btn">View Details</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No classes found for this department.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- DEFAULTER TABLE (DEPT-WIDE) -->
    <div class="table-card">
        <h2>Defaulter Summary (Department-wide)</h2>
        <table>
            <tr>
                <th>Course</th>
                <th>Students &lt;50%</th>
                <th>50–60%</th>
                <th>70–75%</th>
                <th>Total Below 75%</th>
            </tr>

            <?php if (!empty($defaulters)): ?>
                <?php foreach ($defaulters as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                        <td><?= (int)$row['b50'] ?></td>
                        <td><?= (int)$row['b5060'] ?></td>
                        <td><?= (int)$row['b7075'] ?></td>
                        <td><?= (int)$row['total75'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No defaulter data available yet.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- (OPTIONAL) SUBJECT IMPORT CARD – je tu HOD se subject_details import karna -->
    
    <div class="table-card">
        <h2>Import Subject Details</h2>
        <p>Upload subject_details CSV file.</p>

        <form method="POST" enctype="multipart/form-data" action="import_subjects.php" id="importForm">
            <label class="upload-box">
                <input type="file" name="csv_file" accept=".csv, .xlsx, .xls" required id="fileInput">
                <span class="upload-text">Click to Upload CSV or Excel</span>
            </label>

            <!-- hidden or button name = import -->
            <button type="submit" name="import" value="1" class="btn-primary">
                Upload &amp; Import
            </button>
        </form>
    </div>
</div>

<!-- SheetJS for Excel to CSV conversion -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    
    if (!file) return;

    const fileName = file.name.toLowerCase();
    if (fileName.endsWith('.xlsx') || fileName.endsWith('.xls')) {
        e.preventDefault(); // Stop form submission to convert file
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            
            // Get first sheet
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            
            // Convert to CSV
            const csvOutput = XLSX.utils.sheet_to_csv(worksheet);
            
            // Create a new Blob and File
            const blob = new Blob([csvOutput], {type: 'text/csv'});
            const newFile = new File([blob], "converted_courses.csv", {type: "text/csv"});
            
            // Create a DataTransfer to update the file input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(newFile);
            fileInput.files = dataTransfer.files;
            
            // Now submit the form programmatically
            // We need to remove the submit listener or use a flag to prevent infinite loop, 
            // but since we replaced the file, the check (endsWith .xlsx) might fail if we checked the new file.
            // However, the file input value is now a .csv file (in memory at least for submission), 
            // but the file input value visible to user might not change easily or we just submit.
            // Actually, simply submitting the form now that the input has a CSV file is enough.
            // But wait, 'submit()' on form element doesn't trigger 'submit' event listeners.
            document.getElementById('importForm').submit();
        };
        reader.readAsArrayBuffer(file);
    }
});
</script>

    </div>
   

</div>

</body>
</html>
