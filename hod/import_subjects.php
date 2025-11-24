<?php
include('../includes/db_connect.php');

if (isset($_POST['import']) && isset($_FILES['csv_file'])) {
    $fileName = $_FILES['csv_file']['tmp_name'];

    if (!is_uploaded_file($fileName) || $_FILES['csv_file']['size'] <= 0) {
        die("File is empty or not uploaded correctly.");
    }

    $file = fopen($fileName, "r");

    if (!$file) {
        die("Unable to open file.");
    }

    // Read header row once and ignore
    $header = fgetcsv($file);

    $inserted = 0;
    $skipped  = 0;

    while (($row = fgetcsv($file, 10000, ",")) !== FALSE) {

        // Skip completely empty rows
        if (count(array_filter($row)) == 0) {
            $skipped++;
            continue;
        }

        // Safety: ensure at least 12 columns
        if (count($row) < 12) {
            $skipped++;
            continue;
        }

        
        $department        = trim($row[1] ?? '');
        $program           = trim($row[2] ?? '');
        $course_name       = trim($row[3] ?? '');
        $course_code       = trim($row[4] ?? '');
        $course_type       = trim($row[5] ?? '');
        $theory_credits    = trim($row[6] ?? 0);
        $practical_credits = trim($row[7] ?? 0);
        $internal          = trim($row[8] ?? 0);
        $mcq               = trim($row[9] ?? 0);
        $theory            = trim($row[10] ?? 0);
        $created_at        = trim($row[11] ?? null);

        // Convert numeric fields safely
        
        $theory_credits    = (float)$theory_credits;
        $practical_credits = (float)$practical_credits;
        $internal          = (int)$internal;
        $mcq               = (int)$mcq;
        $theory            = (int)$theory;

        if ($created_at === '' || $created_at === null) {
            $created_at = date('Y-m-d H:i:s');
        }

        $stmt = $conn->prepare("
            INSERT INTO subject_details 
            ( department, program, course_name, course_code, course_type, 
             theory_credits, practical_credits, internal_marks, mcq_marks, theory_marks, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssddiiis",
            
            $department,
            $program,
            $course_name,
            $course_code,
            $course_type,
            $theory_credits,
            $practical_credits,
            $internal,
            $mcq,
            $theory,
            $created_at
        );

        if (!$stmt->execute()) {
            echo "Error inserting row (Course Code: {$course_code}): " . $stmt->error . "<br>";
            $skipped++;
        } else {
            $inserted++;
        }

        $stmt->close();
    }

    fclose($file);

    header("Location: hod_dashboard.php?status=subject_imported&count=$inserted");
    exit;

} else {
    echo "No file uploaded or wrong form field name!";
}
