<?php
include('../includes/session_check.php');
if($_SESSION['role'] !== 'admin'){
    header("Location: ../index.php");
    exit();
}
include("sidebar.php");
?>
<div class="main-content">

    <h2>Student Attendance</h2>

    <table border="1" cellpadding="10">
        <tr>
            <th>Date</th>
            <th>Status</th>
        </tr>

        <tr>
            <td>2025-01-01</td>
            <td>Present</td>
        </tr>

        <tr>
            <td>2025-01-02</td>
            <td>Absent</td>
        </tr>

    </table>

</div>
