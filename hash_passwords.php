<?php
include('includes/db_connect.php'); // ✅ use your existing DB connection file

$sql = "SELECT id, password FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Hash only if it's not already hashed
        if (substr($row['password'], 0, 4) !== '$2y$') {
            $hashed = password_hash($row['password'], PASSWORD_DEFAULT);
            $update = "UPDATE users SET password='$hashed' WHERE id=" . $row['id'];
            $conn->query($update);
        }
    }
    echo "<h2>✅ Passwords converted to hashed format successfully!</h2>";
} else {
    echo "<h2>⚠️ No users found in the table.</h2>";
}
?>
