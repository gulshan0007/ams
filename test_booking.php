<?php
// Test script to verify booking functionality
include 'connections.php';

echo "<h2>Testing Booking Functionality</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

// Test 1: Check if bookings table has correct structure
echo "<h3>Test 1: Checking Bookings Table Structure</h3>";
$result = $mysqli->query("DESCRIBE bookings");
if ($result) {
    echo "<p style='color: green;'>✓ Bookings table structure:</p>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['Field']} - {$row['Type']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Error checking bookings table: " . $mysqli->error . "</p>";
}

// Test 2: Check if civil table has equipment
echo "<h3>Test 2: Checking Civil Equipment</h3>";
$result = $mysqli->query("SELECT id, equipment_name, availability FROM civil LIMIT 3");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Found equipment in civil department:</p>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: {$row['id']} - {$row['equipment_name']} ({$row['availability']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ No equipment found in civil department</p>";
}

// Test 3: Check if users exist
echo "<h3>Test 3: Checking Users</h3>";
$result = $mysqli->query("SELECT username, department FROM users LIMIT 3");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Found users:</p>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['username']} ({$row['department']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ No users found</p>";
}

// Test 4: Test booking insertion (without actually inserting)
echo "<h3>Test 4: Testing Booking Query</h3>";
$test_query = "INSERT INTO bookings (instrument_id, username, start_datetime, end_datetime, department, status, purpose) 
               VALUES (?, ?, ?, ?, ?, 'pending', ?)";
$stmt = $mysqli->prepare($test_query);
if ($stmt) {
    echo "<p style='color: green;'>✓ Booking query prepared successfully</p>";
    $stmt->close();
} else {
    echo "<p style='color: red;'>✗ Error preparing booking query: " . $mysqli->error . "</p>";
}

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✅ Booking functionality test completed!</p>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";

echo "</div>";

$mysqli->close();
?> 