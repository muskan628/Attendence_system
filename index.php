<?php
session_start();
include('includes/db_connect.php'); // Your DB connection file

$error = ""; // Initialize error variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ✅ Use isset() to avoid undefined key warnings
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $userid = isset($_POST['userid']) ? $_POST['userid'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($role) || empty($userid) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Query to match user + role
        $sql = "SELECT * FROM users WHERE userid = '$userid' AND role = '$role'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify password (using hash)
            if (password_verify($password, $row['password'])) {
                $_SESSION['userid'] = $row['userid'];
                $_SESSION['role'] = $row['role'];

                // Redirect by role
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
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-300 h-screen flex items-center justify-center">

  <div class="bg-white shadow-xl rounded-2xl p-8 w-[380px]">
    <h2 class="text-center text-2xl font-bold text-blue-700 mb-6">University Attendance System</h2>

    <?php if(!empty($error)): ?>
      <p class="text-red-600 text-sm mb-4 text-center"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <!-- Role Selection -->
      <div>
        <label class="block mb-2 text-gray-700 font-semibold">Select Role</label>
        <select name="role" required class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">-- Select Role --</option>
          <option value="admin">Admin</option>
          <option value="hod">HOD</option>
          <option value="staff">Staff</option>
          <option value="student">Student</option>
        </select>
      </div>

      <!-- User ID -->
      <div>
        <label class="block mb-2 text-gray-700 font-semibold">User ID</label>
        <input type="text" name="userid" required
               class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Password -->
      <div>
        <label class="block mb-2 text-gray-700 font-semibold">Password</label>
        <input type="password" name="password" required
               class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Login Button -->
      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
        Login
      </button>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">© 2025 Akal University</p>
  </div>

</body>
</html>
