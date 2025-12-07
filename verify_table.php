<?php
include('includes/db_connect.php');

$sql = "SELECT * FROM subject_details LIMIT 1";
if ($conn->query($sql)) {
    echo "Verification successful: Table exists and is queryable.";
} else {
    echo "Verification failed: " . $conn->error;
}
$conn->close();
?>
