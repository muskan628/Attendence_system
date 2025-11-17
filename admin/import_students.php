<?php
include('../includes/db_connect.php');

if (isset($_FILES['csv_file']['name'])) {

    $file = $_FILES['csv_file']['tmp_name'];

    if (!file_exists($file)) {
        die("CSV file not found.");
    }

    $handle = fopen($file, "r");

    if (!$handle) {
        die("Cannot open uploaded CSV file.");
    }

    $isHeader = true;

    // Total columns expected in DB (excluding auto id)
    $expectedCols = 55;

    while (($row = fgetcsv($handle)) !== false) {

        // Skip header
        if ($isHeader) {
            $isHeader = false;
            continue;
        }

        // Skip empty rows
        if (count(array_filter($row)) == 0) {
            continue;
        }

        // If CSV row has more than 55 columns → trim
        if (count($row) > $expectedCols) {
            $row = array_slice($row, 0, $expectedCols);
        }

        // If CSV row has less → pad empty
        if (count($row) < $expectedCols) {
            $row = array_pad($row, $expectedCols, "");
        }

        // Debug check (optional)
        if (count($row) != $expectedCols) {
            die("Error: Row does not contain exactly 55 values. Found: " . count($row));
        }

        // Build query
        $sql = "INSERT INTO students (
           s_no ,
    date_of_admission,tid,department ,program ,current_class ,auid,student_name ,father_name ,mother_name ,gender ,date_of_birth ,category ,sub_category ,religion ,facility_availed ,village ,student_contact ,parents_contact ,stu_email ,father_occupation ,annual_income ,city ,district ,state ,pincode ,student_aadhar ,father_aadhar ,mother_aadhar ,guardian_relation ,guardian_name ,school_12th_name ,board_12th ,pass_year_12th ,marks_12th ,graduation_college ,graduation_university ,graduation_year ,graduation_marks ,status ,future_student ,admission_session ,nationality ,startup_class ,rural_urban ,permanent_address ,reference ,matriculation_certificate ,migration_certificate ,certificate_12th ,copy_of_all_dmcs ,character_certificate ,copy_aadhar_card_self ,photographs, study_gap_certificate ,undertakings ,caste_certificate ,bank_account_copy ,income_certificate ,aadhar_card_father ,aadhar_card_mother ,aadhar_card_guardian ,residence_proof
        ) VALUES (" . str_repeat("?,", 55);
        
        $sql = rtrim($sql, ",") . ")";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL ERROR: " . $conn->error . "<br>QUERY: " . $sql);
        }

        $types = str_repeat("s", 55); // 55 parameters as string

        $stmt->bind_param($types, ...$row);

        if (!$stmt->execute()) {
            die("Insert Error: " . $stmt->error);
        }
    }

    fclose($handle);

    echo "<b>CSV Imported Successfully!</b>";
}
?>
