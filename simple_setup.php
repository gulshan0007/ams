<?php
// Simple database setup script for Lab Asset Management System
// This script will create all necessary tables and insert sample data

include 'connections.php';

echo "<h2>Setting up Lab Asset Management System Database</h2>";

echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

$success_count = 0;
$error_count = 0;

// Step 1: Create tables
echo "<h3>Step 1: Creating Tables...</h3>";
$tables_sql = file_get_contents('create_tables_simple.sql');
$table_statements = array_filter(array_map('trim', explode(';', $tables_sql)));

foreach ($table_statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    try {
        if ($mysqli->query($statement)) {
            $success_count++;
            echo "<p style='color: green;'>✓ Success: " . substr($statement, 0, 50) . "...</p>";
        } else {
            $error_count++;
            echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
    }
}

// Step 2: Insert sample data
echo "<h3>Step 2: Inserting Sample Data...</h3>";
$data_sql = file_get_contents('insert_sample_data.sql');
$data_statements = array_filter(array_map('trim', explode(';', $data_sql)));

foreach ($data_statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    try {
        if ($mysqli->query($statement)) {
            $success_count++;
            echo "<p style='color: green;'>✓ Success: " . substr($statement, 0, 50) . "...</p>";
        } else {
            $error_count++;
            echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
    }
}

// Step 3: Create indexes
echo "<h3>Step 3: Creating Indexes...</h3>";
$index_statements = [
    "CREATE INDEX idx_bookings_instrument ON bookings(instrument_id)",
    "CREATE INDEX idx_bookings_username ON bookings(username)",
    "CREATE INDEX idx_bookings_datetime ON bookings(start_datetime, end_datetime)",
    "CREATE INDEX idx_userdetails_username ON userdetails(username)",
    "CREATE INDEX idx_users_username ON users(username)"
];

foreach ($index_statements as $statement) {
    try {
        if ($mysqli->query($statement)) {
            $success_count++;
            echo "<p style='color: green;'>✓ Success: " . substr($statement, 0, 50) . "...</p>";
        } else {
            $error_count++;
            echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<h3>Setup Summary:</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<p style='color: green; font-weight: bold;'>✅ Database setup completed successfully!</p>";
    echo "<h3>Sample Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Civil Department:</strong> Username: admin, Password: admin123</li>";
    echo "<li><strong>Mechanical Department:</strong> Username: admin_mech, Password: admin123</li>";
    echo "<li><strong>Electrical Department:</strong> Username: admin_elec, Password: admin123</li>";
    echo "<li><strong>Computer Science Department:</strong> Username: admin_cs, Password: admin123</li>";
    echo "<li><strong>Chemistry Department:</strong> Username: admin_chem, Password: admin123</li>";
    echo "<li><strong>Physics Department:</strong> Username: admin_phy, Password: admin123</li>";
    echo "<li><strong>Mathematics Department:</strong> Username: admin_math, Password: admin123</li>";
    echo "<li><strong>Biology Department:</strong> Username: admin_bio, Password: admin123</li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Database setup completed with errors. Please check the error messages above.</p>";
}

echo "</div>";

$mysqli->close();
?> 