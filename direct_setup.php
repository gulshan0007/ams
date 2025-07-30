<?php
// Direct database setup script for Lab Asset Management System
// This script will create all necessary tables and insert sample data

include 'connections.php';

echo "<h2>Setting up Lab Asset Management System Database</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>";

$success_count = 0;
$error_count = 0;

// Step 1: Create tables directly
echo "<h3>Step 1: Creating Tables...</h3>";

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($mysqli->query($sql)) {
    $success_count++;
    echo "<p style='color: green;'>✓ Success: Created users table</p>";
} else {
    $error_count++;
    echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
}

// Create userdetails table
$sql = "CREATE TABLE IF NOT EXISTS userdetails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($mysqli->query($sql)) {
    $success_count++;
    echo "<p style='color: green;'>✓ Success: Created userdetails table</p>";
} else {
    $error_count++;
    echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
}

// Create bookings table
$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instrument_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    department VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    purpose TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($mysqli->query($sql)) {
    $success_count++;
    echo "<p style='color: green;'>✓ Success: Created bookings table</p>";
} else {
    $error_count++;
    echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
}

// Create department tables
$departments = ['civil', 'mechanical', 'electrical', 'computer_science', 'chemistry', 'physics', 'mathematics', 'biology'];

foreach ($departments as $dept) {
    $sql = "CREATE TABLE IF NOT EXISTS $dept (
        id INT AUTO_INCREMENT PRIMARY KEY,
        equipment_name VARCHAR(255) NOT NULL,
        equipment_dept VARCHAR(255) NOT NULL,
        photo VARCHAR(255),
        specification TEXT,
        description TEXT,
        purpose TEXT,
        users TEXT,
        availability VARCHAR(50) DEFAULT 'Available',
        currently_used_by VARCHAR(50),
        last_used_by VARCHAR(50),
        year_of_purchase VARCHAR(50),
        mmd_no INT,
        supplier VARCHAR(255),
        amount DECIMAL(10,2),
        fund VARCHAR(50),
        incharge VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($mysqli->query($sql)) {
        $success_count++;
        echo "<p style='color: green;'>✓ Success: Created $dept table</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
    }
}

// Step 2: Insert sample data
echo "<h3>Step 2: Inserting Sample Data...</h3>";

// Insert admin users
$admin_users = [
    ['admin', 'admin123', 'civil'],
    ['admin_mech', 'admin123', 'mechanical'],
    ['admin_elec', 'admin123', 'electrical'],
    ['admin_cs', 'admin123', 'computer_science'],
    ['admin_chem', 'admin123', 'chemistry'],
    ['admin_phy', 'admin123', 'physics'],
    ['admin_math', 'admin123', 'mathematics'],
    ['admin_bio', 'admin123', 'biology']
];

foreach ($admin_users as $user) {
    $sql = "INSERT INTO users (username, password, department) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sss", $user[0], $user[1], $user[2]);
    
    if ($stmt->execute()) {
        $success_count++;
        echo "<p style='color: green;'>✓ Success: Added user {$user[0]}</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Insert user details
$user_details = [
    ['admin', 'Administrator', 'admin@iitb.ac.in', 'civil'],
    ['admin_mech', 'Mechanical Admin', 'admin_mech@iitb.ac.in', 'mechanical'],
    ['admin_elec', 'Electrical Admin', 'admin_elec@iitb.ac.in', 'electrical'],
    ['admin_cs', 'CS Admin', 'admin_cs@iitb.ac.in', 'computer_science'],
    ['admin_chem', 'Chemistry Admin', 'admin_chem@iitb.ac.in', 'chemistry'],
    ['admin_phy', 'Physics Admin', 'admin_phy@iitb.ac.in', 'physics'],
    ['admin_math', 'Mathematics Admin', 'admin_math@iitb.ac.in', 'mathematics'],
    ['admin_bio', 'Biology Admin', 'admin_bio@iitb.ac.in', 'biology']
];

foreach ($user_details as $user) {
    $hashed_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $sql = "INSERT INTO userdetails (username, name, email, department, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssss", $user[0], $user[1], $user[2], $user[3], $hashed_password);
    
    if ($stmt->execute()) {
        $success_count++;
        echo "<p style='color: green;'>✓ Success: Added user details for {$user[0]}</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Insert sample equipment for civil department
$civil_equipment = [
    ['Universal Testing Machine', 'civil', 'Capacity: 2000kN, Accuracy: ±1%', 'High precision testing machine for material strength testing', 'Material testing, structural analysis', 'Students, Researchers, Faculty', '2020', 12345, 'ABC Suppliers', 500000.00, 'Department Fund', 'Dr. Smith'],
    ['Concrete Mixer', 'civil', 'Capacity: 50L, Motor: 2HP', 'Portable concrete mixer for laboratory use', 'Concrete preparation, mixing experiments', 'Students, Lab Technicians', '2019', 12346, 'XYZ Equipment', 25000.00, 'Lab Equipment Fund', 'Prof. Johnson'],
    ['Survey Equipment Set', 'civil', 'Total Station, Level, Theodolite', 'Complete surveying equipment package', 'Land surveying, construction layout', 'Students, Surveyors', '2021', 12347, 'Survey Solutions', 150000.00, 'Infrastructure Fund', 'Dr. Williams']
];

foreach ($civil_equipment as $equipment) {
    $sql = "INSERT INTO civil (equipment_name, equipment_dept, specification, description, purpose, users, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssssssssdss", $equipment[0], $equipment[1], $equipment[2], $equipment[3], $equipment[4], $equipment[5], $equipment[6], $equipment[7], $equipment[8], $equipment[9], $equipment[10], $equipment[11]);
    
    if ($stmt->execute()) {
        $success_count++;
        echo "<p style='color: green;'>✓ Success: Added equipment {$equipment[0]}</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Step 3: Create indexes
echo "<h3>Step 3: Creating Indexes...</h3>";

$indexes = [
    "CREATE INDEX idx_bookings_instrument ON bookings(instrument_id)",
    "CREATE INDEX idx_bookings_username ON bookings(username)",
    "CREATE INDEX idx_bookings_datetime ON bookings(start_datetime, end_datetime)",
    "CREATE INDEX idx_userdetails_username ON userdetails(username)",
    "CREATE INDEX idx_users_username ON users(username)"
];

foreach ($indexes as $index_sql) {
    if ($mysqli->query($index_sql)) {
        $success_count++;
        echo "<p style='color: green;'>✓ Success: Created index</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Error: " . $mysqli->error . "</p>";
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