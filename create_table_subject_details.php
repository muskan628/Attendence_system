<?php
include('includes/db_connect.php');

$sql = "CREATE TABLE IF NOT EXISTS subject_details (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(255) NULL,
    program VARCHAR(255) NULL,
    subject_name VARCHAR(255) NOT NULL,
    course_name VARCHAR(255) NULL,
    course_code VARCHAR(50) NOT NULL,
    course_type VARCHAR(100) NULL,
    theory_credits INT(11) DEFAULT 0,
    practical_credits INT(11) DEFAULT 0,
    internal_marks INT(11) DEFAULT 0,
    mcq_marks INT(11) DEFAULT 0,
    theory_marks INT(11) DEFAULT 0,
    credit INT(11) DEFAULT 0,
    lecture_weekly INT(11) DEFAULT 0,
    hours_weekly INT(11) DEFAULT 0,
    hours_monthly INT(11) DEFAULT 0,
    hours_semester INT(11) DEFAULT 0,
    nature VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'subject_details' created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
