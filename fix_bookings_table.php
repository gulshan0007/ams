<?php
// Fix bookings table by adding missing name column
include 'connections.php';

echo "<h2>Fixing Bookings Table</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

// Add name column to bookings table if it doesn't exist
$check_column_query = "SHOW COLUMNS FROM bookings LIKE 'name'";
$result = $mysqli->query($check_column_query);

if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $add_column_sql = "ALTER TABLE bookings ADD COLUMN name VARCHAR(255) AFTER username";
    
    if ($mysqli->query($add_column_sql)) {
        echo "<p style='color: green;'>✓ Success: Added 'name' column to bookings table</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ Info: 'name' column already exists in bookings table</p>";
}

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✅ Bookings table fix completed!</p>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";

echo "</div>";

$mysqli->close();
?> 