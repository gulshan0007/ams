<?php
session_start();
include 'connections.php';
require_once 'get_pending_count.php';


if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: login.php");
    exit();
}
$department = $_SESSION['department'];
$pending_count = getPendingCount($mysqli, $department);


// Add this at the top of your dashboard.php after session checks
updateEquipmentStatus($department, $mysqli);

// For debugging one specific equipment (replace XX with the equipment ID you want to check)
// checkEquipmentStatus($department, $mysqli, 1);
// checkEquipmentStatus($department, $mysqli, 10);

// Force the page to not cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");



// Create department table if it doesn't exist
$table_exists_query = "SHOW TABLES LIKE '$department'";
$result = $mysqli->query($table_exists_query);

if ($result->num_rows == 0) {
    $create_table_query = "CREATE TABLE $department (
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
        incharge VARCHAR(50)
    )";
    $mysqli->query($create_table_query);
}

// Create bookings table if it doesn't exist
$booking_table_query = "SHOW TABLES LIKE 'bookings'";
$booking_result = $mysqli->query($booking_table_query);

if ($booking_result->num_rows == 0) {
    // $create_booking_table = "CREATE TABLE bookings (
    //     id INT AUTO_INCREMENT PRIMARY KEY,
    //     instrument_id INT,
    //     username VARCHAR(255),
    //     start_datetime DATETIME,
    //     end_datetime DATETIME,
    //     department VARCHAR(255),
    //     FOREIGN KEY (instrument_id) REFERENCES $department(id)
    // )";
    $create_booking_table = "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        instrument_id INT,
        username VARCHAR(255),
        start_datetime DATETIME,
        end_datetime DATETIME,
        department VARCHAR(255),
        status VARCHAR(20) DEFAULT 'pending', /* Add this line */
        FOREIGN KEY (instrument_id) REFERENCES $department(id) ON DELETE CASCADE
    )";
    $mysqli->query($create_booking_table);
}

// Image upload directory
$upload_dir = 'uploads/';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Retrieve POST values
        $equipment_name = $_POST['equipment_name'] ?? '';
        $equipment_dept = $_POST['equipment_dept'] ?? '';
        $specification = $_POST['specification'] ?? '';
        $description = $_POST['description'] ?? '';
        $purpose = $_POST['purpose'] ?? '';
        $users = $_POST['users'] ?? '';
        $year_of_purchase = $_POST['year_of_purchase'] ?? '';
        $mmd_no = intval($_POST['mmd_no'] ?? 0);
        $supplier = $_POST['supplier'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        $fund = $_POST['fund'] ?? '';
        $incharge = $_POST['incharge'] ?? '';
        $photo = '';
        
        // Default values
        $availability = 'Available';
        $currently_used_by = null;
        $last_used_by = null;

        // Handle file upload
        if (!empty($_FILES['photo']['name'])) {
            $photo = $upload_dir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        }

        // Insert into database
        $query = "INSERT INTO $department 
            (equipment_name, equipment_dept, photo, specification, description, purpose, users, 
            availability, currently_used_by, last_used_by,
            year_of_purchase, mmd_no, supplier, amount, fund, incharge) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param(
                "sssssssssssissss",
                $equipment_name,
                $equipment_dept,
                $photo,
                $specification,
                $description,
                $purpose,
                $users,
                $availability,
                $currently_used_by,
                $last_used_by,
                $year_of_purchase,
                $mmd_no,
                $supplier,
                $amount,
                $fund,
                $incharge
            );

            if ($stmt->execute()) {
                echo "Record added successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error in preparing statement: " . $mysqli->error;
        }
        
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
    
        // Delete related bookings
        $stmt = $mysqli->prepare("DELETE FROM bookings WHERE instrument_id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    
        // Delete the equipment record
        $stmt = $mysqli->prepare("DELETE FROM $department WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    
        // if ($mysqli->affected_rows > 0) {
        //     echo "Record deleted successfully!";
        // } else {
        //     echo "No record found with that ID.";
        // }
    }
    
}



function updateEquipmentStatus($department, $mysqli) {
    date_default_timezone_set('Asia/Kolkata');
    $current_time = date("Y-m-d H:i:s");
    
    // First, get all equipment from the department
    $equipment_query = "SELECT id FROM `$department`";
    $equipment_result = $mysqli->query($equipment_query);
    
    while($equipment = $equipment_result->fetch_assoc()) {
        $equipment_id = $equipment['id'];
        
        // Check if there's any current or future booking for this equipment
        $check_booking = "SELECT COUNT(*) as active_bookings 
                         FROM bookings 
                         WHERE instrument_id = ? 
                         AND department = ? 
                         AND end_datetime > ?";
                         
        $stmt = $mysqli->prepare($check_booking);
        $stmt->bind_param("iss", $equipment_id, $department, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if($row['active_bookings'] == 0) {
            // No active or future bookings - set to Available
            $update = "UPDATE `$department` 
                      SET availability = 'Available',
                          currently_used_by = NULL 
                      WHERE id = ?";
            $update_stmt = $mysqli->prepare($update);
            $update_stmt->bind_param("i", $equipment_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        $stmt->close();
    }
}

// Debug function to check specific equipment status
function checkEquipmentStatus($department, $mysqli, $equipment_id) {
    date_default_timezone_set('Asia/Kolkata');
    $current_time = date("Y-m-d H:i:s");
    
    // Get equipment details
    $equipment_query = "SELECT * FROM `$department` WHERE id = ?";
    $stmt = $mysqli->prepare($equipment_query);
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    $equipment_result = $stmt->get_result();
    $equipment = $equipment_result->fetch_assoc();
    
    // Get latest booking
    $booking_query = "SELECT * FROM bookings 
                     WHERE instrument_id = ? 
                     AND department = ? 
                     ORDER BY end_datetime DESC 
                     LIMIT 1";
    $stmt = $mysqli->prepare($booking_query);
    $stmt->bind_param("is", $equipment_id, $department);
    $stmt->execute();
    $booking_result = $stmt->get_result();
    $booking = $booking_result->fetch_assoc();
    
    echo "Debug Info:<br>";
    echo "Current Time: " . $current_time . "<br>";
    echo "Equipment Status: " . $equipment['availability'] . "<br>";
    if($booking) {
        echo "Latest Booking End: " . $booking['end_datetime'] . "<br>";
    } else {
        echo "No bookings found<br>";
    }
}





// Update equipment status
updateEquipmentStatus($department, $mysqli);

// Fetch all equipment data
$query = "SELECT * FROM $department";
$result = $mysqli->query($query);

?>

<!-- // Function to update equipment status
// function updateEquipmentStatus($department, $mysqli) {
//     $query = "SELECT * FROM $department";
//     $result = $mysqli->query($query);

//     while ($row = $result->fetch_assoc()) {
//         $equipment_id = $row['id'];
//         $current_time = date("Y-m-d H:i:s");

//         // Check for bookings
//         $booking_query = "SELECT * FROM bookings WHERE instrument_id = ? AND department = ? 
//                          ORDER BY end_datetime DESC LIMIT 1";
//         $stmt = $mysqli->prepare($booking_query);
//         $stmt->bind_param("is", $equipment_id, $department);
//         $stmt->execute();
//         $booking_result = $stmt->get_result();

//         $availability = "Available";
//         $currently_used_by = null;
//         $last_used_by = $row['last_used_by'];

//         if ($booking = $booking_result->fetch_assoc()) {
//             if ($current_time >= $booking['start_datetime'] && $current_time <= $booking['end_datetime']) {
//                 $availability = "Booked";
//                 $currently_used_by = $booking['username'];
//             } elseif ($current_time > $booking['end_datetime'] && $booking['username'] != $last_used_by) {
//                 $last_used_by = $booking['username'];
//             }
//         }

//         // Update equipment status
//         $update_query = "UPDATE $department SET 
//                         availability = ?, 
//                         currently_used_by = ?, 
//                         last_used_by = ? 
//                         WHERE id = ?";
//         $stmt_update = $mysqli->prepare($update_query);
//         $stmt_update->bind_param("sssi", $availability, $currently_used_by, $last_used_by, $equipment_id);
//         $stmt_update->execute();
//         $stmt_update->close();
//     }
// } -->

<!DOCTYPE html>
<html>

<head>
    <title>Lab Asset Management - Dashboard</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: #f5f7fa;
        color: #333;
        line-height: 1.6;
        padding: 20px;
    }

    .header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    gap: 20px; /* Add some flexible spacing */
}

.header-left {
    display: flex;
    align-items: center;
    gap: 20px; /* Space between logo and header info */
}

.header-right {
    display: flex;
    align-items: center;
    gap: 15px; /* Small gap between pending and logout */
}

.logo-container {
    display: flex;
    align-items: center;
}

.header-logo {
    max-height: 80px;
    width: auto;
    object-fit: contain;
}

    .logout-link {
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .logout-link:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .pending-link {
        position: relative;
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }

    .pending-link:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .pending-count {
        background: #ff4757;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: bold;
        min-width: 20px;
        text-align: center;
        position: absolute;
        top: -10px;
        right: -10px;
    }

    .pending-text {
        font-size: 14px;
        font-weight: 500;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Table Styles */
    .equipment-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .equipment-table th {
        background: #2a5298;
        color: white;
        padding: 15px;
        text-align: left;
    }

    .equipment-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }

    .equipment-table tr:hover {
        background: #f8f9fa;
    }

    .equipment-table img {
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Form Styles */
    .add-equipment-form {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-title {
        color: #2a5298;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #555;
    }

    input[type="text"],
    input[type="number"],
    input[type="file"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="number"]:focus {
        border-color: #2a5298;
        outline: none;
    }
    .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        /* Popup container */
        .popup-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 350px;
            padding: 25px;
            text-align: center;
            position: relative;
        }

        /* Popup styling */
        .popup-title {
            font-size: 1.2em;
            margin-bottom: 15px;
            color: #333;
        }

        .popup-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-confirm {
            background-color: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        /* Animation */
        @keyframes popupAnimation {
            from { transform: scale(0.7); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .popup-container {
            animation: popupAnimation 0.3s ease-out;
        }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-delete {
        background: #ff4757;
        color: white;
    }

    .btn-delete:hover {
        background: #ff6b81;
    }

    .btn-edit {
        background: green;
        color: white;
    }

    .btn-edit:hover {
        background: green;
    }

    .btn-add {
        background: #2a5298;
        color: white;
        padding: 12px 25px;
        font-size: 16px;
    }

    .btn-add:hover {
        background: #1e3c72;
    }

    /* Status Badge */
    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-available {
        background: #c8e6c9;
        color: #2e7d32;
    }

    .status-unavailable {
        background: #ffcdd2;
        color: #c62828;
    }
    /* Form Dropdown and Input Styles */
select, 
input[type="date"],
input[type="text"][name="mmd_no"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    background-color: white;
    transition: border-color 0.3s ease;
}

select:focus, 
input[type="date"]:focus,
input[type="text"][name="mmd_no"]:focus {
    border-color: #2a5298;
    outline: none;
    box-shadow: 0 0 5px rgba(42, 82, 152, 0.3);
}

/* Style for dropdown arrow */
select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg fill='black' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/><path d='M0 0h24v24H0z' fill='none'/></svg>");
    background-repeat: no-repeat;
    background-position-x: 98%;
    background-position-y: center;
    border: 1px solid #dfdfdf;
    border-radius: 2px;
    margin-right: 2rem;
    padding: 0.6rem;
    padding-right: 2rem;
    
}

/* Hover and focus states for dropdowns */
select:hover, 
select:focus {
    border-color: #2a5298;
    cursor: pointer;
}

/* Placeholder style */
select option[value=""] {
    color: #888;
}

/* Date input style improvements */
input[type="date"]::-webkit-calendar-picker-indicator {
    background-color: #2a5298;
    padding: 5px;
    border-radius: 3px;
    color: white;
}
    /* Responsive Design */
    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }

        .equipment-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <div class="container">

    <div class="header">
    <div class="header-left">
        <div class="logo-container">
            <img src="images/iitblogo.png" alt="IITB Logo" class="header-logo">
        </div>
        <div class="header-info">
            <h2>Welcome <?php echo $_SESSION['username']; ?></h2>
            <h3>Department: <?php echo strtoupper($_SESSION['department']); ?></h3>
        </div>
    </div>
    <div class="header-right">
        <a href="pending_bookings.php" class="pending-link">
            <?php if ($pending_count > 0): ?>
            <span class="pending-count"><?php echo $pending_count; ?></span>
            <?php endif; ?>
            <span class="pending-text">Booking Management</span>
        </a>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>
    </div>

        <!-- Equipment List Table -->
        <table class="equipment-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Lab</th>
                <th>Photo</th>
                <th>Specification</th>
                <th>Description</th>
                <!-- <th>Purpose</th>
                <th>Users</th> -->
                <!-- <th>Status</th> -->
                <th>Next Availability</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['equipment_name']; ?></td>
                <td><?php echo $row['equipment_dept']; ?></td>
                <td>
                    <?php if ($row['photo']): ?>
                    <img src="<?php echo $row['photo']; ?>" alt="Equipment Photo" width="100">
                    <?php else: ?>
                    No photo
                    <?php endif; ?>
                </td>
                <td><?php echo $row['specification']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <!-- <td><?php echo $row['purpose']; ?></td>
        <td><?php echo $row['users']; ?></td> -->
                <!-- <td>
                    <span
                        class="status-badge <?php echo strtolower($row['availability']) === 'available' ? 'status-available' : 'status-unavailable'; ?>">
                        <?php echo $row['availability']; ?>
                    </span>
                </td> -->
                <td>
                    <?php
            // Fetch the farthest end_datetime for this instrument from the bookings table
            $booked_query = "SELECT MAX(end_datetime) AS booked_till FROM bookings WHERE instrument_id = ? AND department = ?";
            $booked_stmt = $mysqli->prepare($booked_query);
            $booked_stmt->bind_param("is", $row['id'], $department);
            $booked_stmt->execute();
            $booked_result = $booked_stmt->get_result();
            $booked_row = $booked_result->fetch_assoc();

            if ($booked_row['booked_till']) {
                $date = new DateTime($booked_row['booked_till']);
                    echo $date->format('M j, Y g:i A'); // Display the farthest date and time
            } else {
                echo 'Available for Booking'; // If no bookings exist
            }
            ?>
                </td>
                <td>
                <!-- <form id="deleteForm" method="POST" style="display:inline;">
        <input type="hidden" id="deleteIdInput" name="delete_id" value="">
        <button type="button" class="btn btn-delete" onclick="showDeletePopup(<?php echo $row['id']; ?>)">Delete</button>
    </form> -->

    <div id="deletePopup" class="popup-overlay">
        <div class="popup-container">
            <div class="popup-title">Are you sure?</div>
            <p>Do you really want to delete this equipment? This action cannot be undone.</p>
            <div class="popup-buttons">
                <button class="btn btn-cancel" onclick="hideDeletePopup()">Cancel</button>
                <button class="btn btn-confirm" onclick="confirmDelete()">Yes, Delete</button>
            </div>
        </div>
    </div>
                    <form action="update.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-edit">Edit</button>
                    </form>
                    <form action="details.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-detail">Detail</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

        <!-- Form to add new equipment -->
        <div class="add-equipment-form">
            <h3 class="form-title">Add New Equipment</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="equipment_name" required>
                    </div>
                    <div class="form-group">
    <label>Lab</label>
    <select name="equipment_dept" required>
        <option value="">Select Lab</option>
        <option value="Traffic Lab">Traffic Lab</option>
        <option value="Hydro Lab">Hydro Lab</option>
        <option value="Foundation Lab">Foundation Lab</option>
    </select>
</div>
                    <div class="form-group">
                        <label>Photo</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Specification</label>
                        <input type="text" name="specification">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description">
                    </div>
                    <div class="form-group">
                        <label>Purpose</label>
                        <input type="text" name="purpose">
                    </div>
                    
                    <!-- <div class="form-group">
                        <label>Availability</label>
                        <input type="text" name="availability">
                    </div> -->
                    <!-- <div class="form-group">
                        <label>Currently Used By</label>
                        <input type="text" name="currently_used_by">
                    </div>
                    <div class="form-group">
                        <label>Last Used By</label>
                        <input type="text" name="last_used_by">
                    </div> -->
                    <div class="form-group">
    <label>Year of Purchase</label>
    <input type="date" name="year_of_purchase">
</div>
<div class="form-group">
    <label>MMD No</label>
    <input type="text" name="mmd_no" pattern="[A-Za-z0-9]+" title="Alphanumeric characters only">
</div>
<div class="form-group">
    <label>Supplier</label>
    <select name="supplier">
        <option value="">Select Supplier</option>
        <option value="Gulshan Kumar Enterprises">Mohit Enterprises</option>
        <option value="Ram Lal Enterprises">Ram Lal Enterprises</option>
    </select>
</div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount">
                    </div>
                    <div class="form-group">
                        <label>Fund</label>
                        <input type="text" name="fund">
                    </div>
                    <div class="form-group">
    <label>Incharge</label>
    <select name="incharge">
        <option value="">Select Incharge</option>
        <option value="ram">Prof. Archak Mittal</option>
        <option value="sita">Prof. Subimal Ghosh</option>
    </select>
</div>
<div class="form-group">
                        <label>Contact Email</label>
                        <input type="text" name="users">
                    </div>
                </div>
                <button type="submit" name="add" class="btn btn-add">Add Equipment</button>
            </form>
        </div>
    </div>
    <script>
        function showDeletePopup(id) {
            document.getElementById('deleteIdInput').value = id;
            document.getElementById('deletePopup').style.display = 'flex';
        }

        function hideDeletePopup() {
            document.getElementById('deletePopup').style.display = 'none';
        }

        function confirmDelete() {
            // Submit the form programmatically
            document.getElementById('deleteForm').submit();
        }
    </script>
</body>

</html>