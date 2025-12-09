<?php include("sidebar.php"); ?>
<div class="main-content">
    <h2>HOD Panel</h2>

    <form action="upload_subjects.php" method="POST" enctype="multipart/form-data">
        <label>Upload Subject CSV:</label><br><br>
        <input type="file" name="subjects" required>
        <br><br>
        <button type="submit">Upload Subjects</button>
    </form>

    <hr><br>

    <form action="upload_hod_data.php" method="POST" enctype="multipart/form-data">
        <label>Upload HOD Data:</label><br><br>
        <input type="file" name="hod_data" required>
        <br><br>
        <button type="submit">Upload Data</button>
    </form>
</div>
