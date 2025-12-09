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

    // Read header row
    $header = fgetcsv($file);
    
    // Detect format
    $is_new_format = false;
    // Check for "Sr. No." or "Department" in header
    if ($header && (in_array('Sr. No.', $header) || in_array('Department', $header))) {
        $is_new_format = true;
    }

    $inserted = 0;
    $skipped  = 0;

    while (($row = fgetcsv($file, 10000, ",")) !== false) {
        // Skip completely empty rows
        if (count(array_filter($row)) == 0) {
            $skipped++;
            continue;
        }

        $created_at = date('Y-m-d H:i:s');

        if ($is_new_format) {
            // New Format Mapping (based on courses_2024-CSE.csv)
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
            // 11: Created At (Optional, we use current time)
            
            $department      = trim($row[1] ?? '');
            $program         = trim($row[2] ?? '');
            $subject_name    = trim($row[3] ?? ''); // Mapping Course Name to subject_name
            $course_code     = trim($row[4] ?? '');
            $course_type     = trim($row[5] ?? '');
            $theory_credits  = (int)trim($row[6] ?? 0);
            $practical_credits = (int)trim($row[7] ?? 0);
            $internal_marks  = (int)trim($row[8] ?? 0);
            $mcq_marks       = (int)trim($row[9] ?? 0);
            $theory_marks    = (int)trim($row[10] ?? 0);
            
            // Also populate course_name same as subject_name
            $course_name = $subject_name;

            if ($subject_name === '' || $course_code === '') {
                $skipped++;
                continue;
            }

            $stmt = $conn->prepare("
                INSERT INTO subject_details 
                (department, program, subject_name, course_name, course_code, course_type, 
                 theory_credits, practical_credits, internal_marks, mcq_marks, theory_marks, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            if (!$stmt) die("Prepare failed: " . $conn->error);
            $stmt->bind_param("ssssssiiiiis", 
                $department, $program, $subject_name, $course_name, $course_code, $course_type,
                $theory_credits, $practical_credits, $internal_marks, $mcq_marks, $theory_marks, $created_at
            );

        } else {
            // Old Format Mapping
            // Need at least 9 columns: subject_name..nature
            if (count($row) < 9) {
                $skipped++;
                continue;
            }

            $subject_name   = trim($row[0] ?? '');
            $course_type    = trim($row[1] ?? '');
            $course_code    = trim($row[2] ?? '');
            $credit         = (int)trim($row[3] ?? 0);
            $lecture_weekly = (int)trim($row[4] ?? 0);
            $hours_weekly   = (int)trim($row[5] ?? 0);
            $hours_monthly  = (int)trim($row[6] ?? 0);
            $hours_semester = (int)trim($row[7] ?? 0);
            $nature         = trim($row[8] ?? '');

            if ($subject_name === '' || $course_code === '') {
                $skipped++;
                continue;
            }

            $stmt = $conn->prepare("
                INSERT INTO subject_details 
                (subject_name, course_type, course_code, credit,
                 lecture_weekly, hours_weekly, hours_monthly, hours_semester,
                 nature, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ");
            if (!$stmt) die("Prepare failed: " . $conn->error);
            $stmt->bind_param("sssiiiiiss",
                $subject_name, $course_type, $course_code, $credit,
                $lecture_weekly, $hours_weekly, $hours_monthly, $hours_semester,
                $nature, $created_at
            );
        }

        if (!$stmt->execute()) {
             // Log error but don't stop
             error_log("Error inserting row ($course_code): " . $stmt->error);
             $skipped++;
        } else {
            $inserted++;
        }
        $stmt->close();
    }

    fclose($file);
    header("Location: manage_subjects.php?status=subject_imported&count=$inserted");
    exit;

} else {
    echo "No file uploaded or wrong form field name!";
}
?>
