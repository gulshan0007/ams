<?php
$host = "localhost";
$dbname = "ugacademics_lab_assets";
$username = "root";
$password = "";

// Create a new MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
