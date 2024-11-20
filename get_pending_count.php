<?php
function getPendingCount($mysqli, $department) {
    try {
        // Check if mysqli connection is valid
        if (!$mysqli || $mysqli->connect_errno) {
            error_log("Database connection error in get_pending_count.php");
            return 0;
        }

        // Prepare and execute query
        $pending_query = "SELECT COUNT(*) as pending_count FROM bookings WHERE department = ? AND status = 'pending'";
        $stmt = $mysqli->prepare($pending_query);
        
        if (!$stmt) {
            error_log("Failed to prepare statement in get_pending_count.php: " . $mysqli->error);
            return 0;
        }

        $stmt->bind_param("s", $department);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $pending_count = $result->fetch_assoc()['pending_count'];
        
        $stmt->close();
        
        return $pending_count;
    } catch (Exception $e) {
        error_log("Error in get_pending_count.php: " . $e->getMessage());
        return 0;
    }
}

// Usage example:
// include 'get_pending_count.php';
// $pending_count = getPendingCount($mysqli, $department);
?>