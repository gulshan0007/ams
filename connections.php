<?php
$host = "10.198.49.5";
$dbname = "ugacademics_lab_assets";
$username = "ugacademics";
$password = "zsVgOLEGSxewJbgk";

// Create a new MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
