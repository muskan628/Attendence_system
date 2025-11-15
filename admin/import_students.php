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
            s_no, date_of_admission, tid, department, program, current_class, auid,
            student_name, father_name, mother_name, gender, date_of_birth, category,
            sub_category, religion, facility_availed, hosteller, transport, address,
            city, district, state, pin_code, nationality, mobile_number, email,
            aadhar_number, father_mobile, mother_mobile, guardian_mobile, blood_group,
            quota, matric_percentage, senior_secondary_percentage, ug_percentage,
            pg_percentage, fee_concession, scholarship, remarks, matriculation_certificate,
            migration_certificate, plus2_certificate, copy_all_dmcs, character_certificate,
            aadhar_self, photographs, study_gap_certificate, undertakings, caste_certificate,
            bank_account_copy, income_certificate, aadhar_father, aadhar_mother,
            aadhar_guardian, residence_proof
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
