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

$stmt->bind_param("ssss", $subject_code, $roll_no, $date_db, $status);

$date_db = $date; // assuming input is YYYY-mm-dd

foreach ($statuses as $roll_no => $status) {
    $status = strtoupper(trim($status));
    if (!in_array($status, ['P','A','L'])) {
        $status = 'P';
    }

    $roll_no = trim($roll_no);

    $stmt->execute();
}

$stmt->close();

// Redirect back to staff dashboard with message + same subject/date
header(
    "Location: staff_dashboard.php?msg=success&subject=" .
    urlencode($subject_code) .
    "&date=" . urlencode($date)
);
exit;
