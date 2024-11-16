<?php
include 'connections.php';

// Get all departments
$dept_query = "SHOW TABLES LIKE '%department%'";
$dept_result = $mysqli->query($dept_query);

while ($dept_row = $dept_result->fetch_array()) {
    $department = $dept_row[0];
    
    // Get all instruments in department
    $instrument_query = "SELECT id FROM `$department`";
    $instrument_result = $mysqli->query($instrument_query);
    
    while ($instrument = $instrument_result->fetch_assoc()) {
        updateInstrumentStatus($mysqli, $instrument['id'], $department);
    }
}
?>