<?php
header("Content-Type: application/json");
include 'connections.php';

// Check for department and uid parameters
if (isset($_GET['department']) && isset($_GET['uid'])) {
    $department = $_GET['department'];
    $uid = intval($_GET['uid']);
    
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

    // Fetch record for the specific uid from the department table
    $query = "SELECT * FROM `$department` WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "No record found with the given UID"]);
    }
} else {
    echo json_encode(["error" => "Department and UID parameters are required"]);
}
?>
