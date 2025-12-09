<?php
include('../includes/session_check.php');
if($_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}
include("sidebar.php");
?>
<div class="main-content">

    <h2>Staff Panel</h2>

    <form action="mark_attendance.php" method="POST">

        <label>Select Student</label><br>
        <select name="student_id">
            <option value="101">Student 1</option>
            <option value="102">Student 2</option>
        </select>

        <br><br>

        <label>Attendance</label><br>
        <select name="status">
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
        </select>

        <br><br>

        <button type="submit">Mark Attendance</button>

    </form>

</div>
