<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])){
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>

    <link rel="stylesheet" href="style.css"> 
    <!-- Include your existing sidebar CSS also -->

    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .card-box {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }

        .user-card {
            background: #fff;
            padding: 30px;
            width: 250px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0px 3px 10px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: .3s;
        }

        .user-card:hover {
            transform: translateY(-5px);
            background-color: #f5f7ff;
        }

        .user-card h3 {
            margin-bottom: 10px;
            font-size: 22px;
        }

        .user-card p {
            font-size: 15px;
            color: #555;
        }
    </style>
</head>

<body>

<!-- SIDEBAR (same as your dashboard) -->
<?php include("sidebar.php"); ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <h2>Manage Users</h2>
    <p>Select a user type to continue</p>

    <div class="card-box">

        <div class="user-card" onclick="window.location='manage_hod.php'">
            <h3>HOD</h3>
            <p>Upload data, manage subjects</p>
        </div>

        <div class="user-card" onclick="window.location='manage_staff.php'">
            <h3>Staff</h3>
            <p>Mark attendance for classes</p>
        </div>

        <div class="user-card" onclick="window.location='manage_student.php'">
            <h3>Student</h3>
            <p>View attendance only</p>
        </div>

    </div>

</div>

</body>
</html>
