<?php
session_start();
include 'connections.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$departments = ['civil', 'mechanical', 'cse'];
$allInstruments = [];

foreach ($departments as $department) {
    $query = "SELECT id, equipment_name, equipment_dept FROM civil";
    $result = $mysqli->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $allInstruments[] = $row;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($allInstruments);
?>