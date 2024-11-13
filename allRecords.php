<?php
header("Content-Type: application/json");
include 'connections.php';

// Check for department parameter
if (isset($_GET['department'])) {
    $department = $_GET['department'];
    
    // Prevent SQL injection by validating department name
    $valid_departments = ["civil", "cse", "mechanical"]; // Add other valid departments here
    if (!in_array($department, $valid_departments)) {
        echo json_encode(["error" => "Invalid department"]);
        exit();
    }

    // Check if department table exists
    $table_exists_query = "SHOW TABLES LIKE '$department'";
    $result = $mysqli->query($table_exists_query);

    if ($result->num_rows == 0) {
        echo json_encode(["error" => "Department table not found"]);
        exit();
    }

    // Fetch all records from the department table
    $query = "SELECT * FROM `$department`";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        echo json_encode($records);
    } else {
        echo json_encode(["error" => "No records found in this department"]);
    }
} else {
    echo json_encode(["error" => "Department parameter is required"]);
}
?>
