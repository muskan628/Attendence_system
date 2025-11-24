<?php
session_start();
include('../includes/db_connect.php');

// Optional: later get teacher info from login
// $teacher_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$subject_code = $_POST['subject_code'] ?? '';
$date         = $_POST['date'] ?? '';
$statuses     = $_POST['status'] ?? [];   // status[roll_no] => P/A/L

if ($subject_code === '' || $date === '') {
    die("Subject or date missing.");
}

if (empty($statuses)) {
    die("No attendance data received.");
}

// Ensure table exists
$checkTable = "CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(50) NOT NULL,
    roll_no VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    status CHAR(1) NOT NULL,
    UNIQUE KEY unique_attendance (subject_code, roll_no, date)
)";
if (!$conn->query($checkTable)) {
    die("Table creation failed: " . $conn->error);
}

// Prepare INSERT ... ON DUPLICATE KEY UPDATE
$sql = "
    INSERT INTO attendance (subject_code, roll_no, date, status)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE status = VALUES(status)
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

// Initialize variables for bind_param
$roll_no = '';
$status_val = '';
$date_db = $date; 

// Bind parameters
$stmt->bind_param("ssss", $subject_code, $roll_no, $date_db, $status_val);

foreach ($statuses as $r_no => $stat) {
    $status_val = strtoupper(trim($stat));
    if (!in_array($status_val, ['P','A','L'])) {
        $status_val = 'P';
    }

    $roll_no = trim($r_no);

    $stmt->execute();
}

$stmt->close();

// Redirect back to staff dashboard with message + same subject/date
// Redirect back
$redirect_to = $_POST['redirect_to'] ?? 'staff_dashboard.php';
header(
    "Location: " . $redirect_to . "?msg=success&subject=" .
    urlencode($subject_code) .
    "&date=" . urlencode($date)
);
exit;
