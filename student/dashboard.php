<?php include('../includes/session_check.php'); ?>
<?php include('../includes/db_connect.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">

  <!-- Sidebar -->
  <aside class="w-64 bg-white h-screen shadow-lg p-5">
    <h1 class="text-blue-700 text-lg font-semibold mb-6">Punjab University</h1>
    <ul>
      <li class="mb-3"><a href="#" class="text-gray-800 hover:text-blue-600">Dashboard</a></li>
      <li class="mb-3"><a href="#" class="text-gray-800 hover:text-blue-600">Course Schedule</a></li>
      <li class="mb-3"><a href="#" class="text-gray-800 hover:text-blue-600">Profile</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8">
    <h2 class="text-2xl font-bold text-gray-700 mb-4">Overall Attendance</h2>

    <!-- Overall Progress Card -->
    <div class="bg-blue-100 p-6 rounded-lg shadow-md flex items-center justify-between">
      <h3 class="text-xl font-semibold">Current Semester</h3>
      <div class="text-center">
        <p class="text-4xl font-bold text-blue-600">78%</p>
        <p class="text-sm text-gray-600">Present</p>
      </div>
    </div>

    <!-- Course-wise Section -->
    <h3 class="text-xl font-semibold mt-8 mb-4">Course-wise Attendance</h3>
    <div class="grid grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h4 class="text-lg font-semibold mb-2">PHYS-101: Mechanics</h4>
        <p class="text-3xl text-blue-600 font-bold mb-1">62%</p>
        <p class="text-gray-500 text-sm">Good | Warning</p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md">
        <h4 class="text-lg font-semibold mb-2">MATH-205: Linear Chemistry</h4>
        <p class="text-3xl text-green-600 font-bold mb-1">91%</p>
        <p class="text-gray-500 text-sm">Excellent | Warning</p>
      </div>
    </div>
  </main>

</body>
</html>
