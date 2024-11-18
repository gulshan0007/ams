<?php
include 'connections.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipment_dept = $_POST['equipment_dept'] ?? '';

    if (!empty($equipment_dept)) {
        // Query to fetch asset IDs and equipment names based on selected equipment department
        $query = "SELECT id, equipment_name FROM civil WHERE equipment_dept = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $equipment_dept);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<option value="">Select Asset ID</option>';  // Default option
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . $row['id'] . ' - ' . $row['equipment_name'] . '</option>';
        }
    }
}
?>
