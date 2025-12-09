<div class="sidebar">
    <h2>Akal University</h2>

    <a href="ad_dashboard.php">Dashboard</a>
    <a href="detailed_reports.php">Detailed Reports</a>
    <a href="defaulter_list.php">Defaulter List</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_students.php">Manage Students</a>
    <a href="manage_departments.php">Manage Departments</a>
    <a href="manage_teachers.php">Manage Teachers</a>
    <a href="settings.php">Settings</a>

    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100%;
    background: #f2f2f2;
    padding: 20px;
    position: fixed;
    left: 0;
    top: 0;
}
.sidebar a {
    display: block;
    padding: 12px;
    margin-bottom: 10px;
    text-decoration: none;
    color: #333;
    font-size: 16px;
}
.sidebar a:hover {
    background: #ddd;
    border-radius: 5px;
}
.logout-btn {
    background: red;
    color: white;
    padding: 10px;
    width: 100%;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
</style>
