<?php
// includes/id_helper.php

/**
 * Generates a custom ID with a prefix (e.g., D-0025, P-034).
 * 
 * @param mysqli $conn Database connection object
 * @param string $prefix The prefix for the ID (e.g., 'D', 'P')
 * @param string $table The table name
 * @param string $column The ID column name
 * @param int $padding The number of digits to pad (default 4)
 * @return string The new custom ID
 */
function generateCustomId($conn, $prefix, $table, $column, $padding = 4) {
    // Escape for safety (though usage should be controlled)
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $prefixEscaped = $conn->real_escape_string($prefix);

    // Find the latest ID with this prefix
    // We order by length first to handle D-9 vs D-10 correctly, then by value
    $sql = "SELECT $column FROM $table 
            WHERE $column LIKE '$prefixEscaped-%' 
            ORDER BY LENGTH($column) DESC, $column DESC 
            LIMIT 1";
            
    $result = $conn->query($sql);
    
    $lastId = null;
    if ($result && $row = $result->fetch_assoc()) {
        $lastId = $row[$column];
    }

    if ($lastId) {
        // Extract number: D-0025 -> 0025
        // Assuming format is PREFIX-NUMBER
        $parts = explode('-', $lastId);
        if (count($parts) >= 2) {
            $number = (int)end($parts);
            $newNumber = $number + 1;
        } else {
            // Fallback if format is weird
            $newNumber = 1;
        }
    } else {
        // Start from 1 if no existing IDs with this prefix
        $newNumber = 1;
    }

    // Format new ID: D-0012
    return $prefix . '-' . str_pad($newNumber, $padding, '0', STR_PAD_LEFT);
}
?>
