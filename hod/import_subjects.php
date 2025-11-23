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

    // Optional: debug header
    // echo "<pre>"; print_r($header); exit;

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
            // echo "Row skipped (only " . count($row) . " columns): " . implode(" | ", $row) . "<br>";
            $skipped++;
            continue;
        }

        // Map columns (0-based index) – matches your Excel:
        // 0: Sr. No.
        // 1: Department
        // 2: Program
        // 3: Course Name
        // 4: Course Code
        // 5: Course Type
        // 6: Theory Credits
        // 7: Practical Credits
        // 8: Internal
        // 9: MCQ
        // 10: Theory
        // 11: Created At

        $sr_no             = trim($row[0] ?? '');
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
        $sr_no             = (int)$sr_no;
        $theory_credits    = (float)$theory_credits;
        $practical_credits = (float)$practical_credits;
        $internal          = (int)$internal;
        $mcq               = (int)$mcq;
        $theory            = (int)$theory;

        // Optional: if created_at is empty, you can set current date
        if ($created_at === '' || $created_at === null) {
            $created_at = date('Y-m-d H:i:s');
        }

        // Prepare INSERT (ensure these column names exist in your table)
        $stmt = $conn->prepare("
            INSERT INTO subjects_details 
            (sr_no, department, program, course_name, course_code, course_type, 
             theory_credits, practical_credits, internal_marks, mcq_marks, theory_marks, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Types: i = int, d = double(float), s = string
        $stmt->bind_param(
            "isssssddiiis",
            $sr_no,
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

    // Optional: show summary during testing
    // echo "Imported: $inserted rows, Skipped: $skipped rows";
    // exit;

    header("Location: hod_dashboard.php?import=success&inserted=$inserted&skipped=$skipped");
    exit;

} else {
    echo "No file uploaded!";
}
?>
