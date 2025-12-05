<?php
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

if (!isset($_FILES['csv_file']['name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    die("No CSV uploaded or upload error.");
}

$file = $_FILES['csv_file']['tmp_name'];
if (!file_exists($file)) {
    die("CSV file not found.");
}

$handle = fopen($file, "r");
if (!$handle) {
    die("Cannot open CSV.");
}

/* ----------------------------------------------------
   1) Helper Functions for UUID and Relational Data
   ---------------------------------------------------- */
function generateUuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function getOrCreateDepartment($conn, $name) {
    if (empty($name)) return null;
    
    // Check exist
    $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['id'];
    }
    $stmt->close();
    
    // Create
    $newId = generateUuid();
    $stmt = $conn->prepare("INSERT INTO departments (id, name) VALUES (?, ?)");
    $stmt->bind_param("ss", $newId, $name);
    $stmt->execute();
    $stmt->close();
    
    return $newId;
}

function getOrCreateProgram($conn, $name, $deptId, $batch) {
    if (empty($name) || empty($deptId)) return null;
    
    // Check exist
    $stmt = $conn->prepare("SELECT programId FROM programs WHERE name = ? AND departmentId = ?");
    $stmt->bind_param("ss", $name, $deptId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['programId'];
    }
    $stmt->close();
    
    // Create
    $newId = generateUuid();
    $batchVal = $batch ?: date('Y'); // Default batch if empty
    $stmt = $conn->prepare("INSERT INTO programs (programId, name, batch, departmentId) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $newId, $name, $batchVal, $deptId);
    $stmt->execute();
    $stmt->close();
    
    return $newId;
}

function getOrCreateStudent($conn, $email, $name, $programId, $startYear) {
    if (empty($email)) return null;
    
    // Check exist
    $stmt = $conn->prepare("SELECT studentUid FROM students WHERE studentEmail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['studentUid'];
    }
    $stmt->close();
    
    // Create
    // Schema: studentUid (PK), id (Unique), studentName, studentEmail, studentRole, studentStartingYear, studentProgramId
    $uid = generateUuid();
    $id = generateUuid(); 
    $role = 'Student';
    $year = $startYear ?: date('Y');
    
    if (!$programId) return null;

    $stmt = $conn->prepare("INSERT INTO students (studentUid, id, studentName, studentEmail, studentRole, studentStartingYear, studentProgramId) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $uid, $id, $name, $email, $role, $year, $programId);
    if (!$stmt->execute()) {
        return null;
    }
    $stmt->close();
    
    return $uid;
}

/* ----------------------------------------------------
   2) Read actual columns from DB (skip auto_increment)
   ---------------------------------------------------- */
$dbCols = [];
$res = $conn->query("SHOW COLUMNS FROM admission");
while ($col = $res->fetch_assoc()) {
    if (stripos($col['Extra'], 'auto_increment') !== false) {
        continue; // skip id
    }
    $dbCols[] = $col['Field'];
}
$res->free();

$expectedCols = count($dbCols);

$columns = "`" . implode("`, `", $dbCols) . "`";
$placeholders = rtrim(str_repeat("?,", $expectedCols), ",");
$sqlTemplate = "INSERT INTO admission ($columns) VALUES ($placeholders)";

/* ----------------------------------------------------
   3) Identify Column Indexes for Logic
   ---------------------------------------------------- */
$idxDepartment = array_search('department', $dbCols);
$idxProgram    = array_search('program', $dbCols);
$idxStudentName= array_search('student_name', $dbCols);
$idxEmail      = array_search('stu_email', $dbCols);
$idxSess       = array_search('admission_session', $dbCols);
$idxUid        = array_search('studentUid', $dbCols);

$isHeader = true;
$rowNumber = 0;
$inserted = 0;

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;

    // skip header row
    if ($isHeader) {
        $isHeader = false;
        continue;
    }

    // skip completely empty lines
    if (count(array_filter($row)) == 0) continue;

    // trim / pad
    if (count($row) > $expectedCols) {
        $row = array_slice($row, 0, $expectedCols);
    } elseif (count($row) < $expectedCols) {
        $row = array_pad($row, $expectedCols, "");
    }

    if (count($row) != $expectedCols) {
        die("Row $rowNumber mismatch: expected $expectedCols, got " . count($row));
    }
    
    /* ------------------------------------------------
       4) Relational Logic
       ------------------------------------------------ */
    // Extract values
    $deptName = ($idxDepartment !== false) ? trim($row[$idxDepartment]) : '';
    $progName = ($idxProgram !== false) ? trim($row[$idxProgram]) : '';
    $stuName  = ($idxStudentName !== false) ? trim($row[$idxStudentName]) : '';
    $stuEmail = ($idxEmail !== false) ? trim($row[$idxEmail]) : '';
    $session  = ($idxSess !== false) ? trim($row[$idxSess]) : '';
    
    // 1. Department
    $deptId = getOrCreateDepartment($conn, $deptName);
    
    // 2. Program
    $progId = getOrCreateProgram($conn, $progName, $deptId, $session);
    
    // 3. Student
    // Extract Year from session (e.g., "2024-2025" -> "2024")
    $parts = explode('-', $session);
    $startYear = isset($parts[0]) && is_numeric(trim($parts[0])) ? trim($parts[0]) : date('Y');
    
    $stuUid = getOrCreateStudent($conn, $stuEmail, $stuName, $progId, $startYear);
    
    // 4. Update row with Student UID
    if ($idxUid !== false && $stuUid) {
        $row[$idxUid] = $stuUid;
    } elseif ($idxUid !== false && !$stuUid) {
        // Log basic info for debugging
         die("Create Student Error on row $rowNumber. UID creation failed. Check Email: $stuEmail");
    }

    /* ------------------------------------------------
       5) Insert into Admission
       ------------------------------------------------ */
    $stmt = $conn->prepare($sqlTemplate);
    if (!$stmt) die("SQL PREPARE ERROR: " . $conn->error);

    $types = str_repeat("s", $expectedCols);
    $bind = [$types];
    for ($i = 0; $i < $expectedCols; $i++) {
        $bind[] = &$row[$i];
    }

    call_user_func_array([$stmt, "bind_param"], $bind);

    if (!$stmt->execute()) {
        die("Insert Error on row $rowNumber → " . $stmt->error);
    }

    $inserted++;
    $stmt->close();
}

fclose($handle);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Status</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <style>
        .success-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: fadeIn 0.5s ease-out;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: #e8f5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .check-icon {
            color: #4caf50;
            font-size: 40px;
        }
        h3 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Akal University</h2>
    <ul>
        <li onclick="window.location.href='ad_dashboard.php'">Dashboard</li>
        <li>Detailed Reports</li>
        <li>Defaulter List</li>
        <li>Manage Users</li>
        <li onclick="window.location.href='manage_students.php'">Manage Students</li>
        <li onclick="window.location.href='manage_departments.php'">Manage Departments</li>
        <li onclick="window.location.href='settings.php'">Settings</li>
    </ul>
    
    <!-- Logout Button -->
    <div class="logout-box">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <div class="success-wrapper">
        <div class="card">
            <div class="icon-circle">
                <span class="check-icon">✓</span>
            </div>
            <h3>Success!</h3>
            <p><?php echo $inserted; ?> student records imported successfully.</p>
            <a href="ad_dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
