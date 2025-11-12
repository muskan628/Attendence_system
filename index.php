<?php
session_start();
include('includes/db_connect.php');

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $userid = isset($_POST['userid']) ? $_POST['userid'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($role) || empty($userid) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $sql = "SELECT * FROM users WHERE userid = '$userid' AND role = '$role'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION['userid'] = $row['userid'];
                $_SESSION['role'] = $row['role'];

                switch ($row['role']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        exit;
                    case 'hod':
                        header("Location: hod/dashboard.php");
                        exit;
                    case 'staff':
                        header("Location: staff/dashboard.php");
                        exit;
                    case 'student':
                        header("Location: student/dashboard.php");
                        exit;
                }
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "User not found for selected role!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Attendance System</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <div class="login-box">
    <h2>University Attendance System</h2>

    <?php if(!empty($error)): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Select Role</label>
        <select name="role" required>
          <option value="">-- Select Role --</option>
          <option value="admin">Admin</option>
          <option value="hod">HOD</option>
          <option value="staff">Staff</option>
          <option value="student">Student</option>
        </select>
      </div>

      <div class="form-group">
        <label>User ID</label>
        <input type="text" name="userid" required placeholder="Enter your User ID">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Enter your Password">
      </div>

      <button type="submit">Login</button>
    </form>

    <p class="footer">© 2025 Akal University</p>
  </div>

</body>
</html>
