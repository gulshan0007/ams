<?php
session_start();
include 'connections.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

// Get instrument_id and department from query parameters
$instrument_id = $_GET['id'] ?? '';
$department = $_GET['department'] ?? '';
$username = $_SESSION['username'];
$current_time = date("Y-m-d H:i:s");



$user_query = "SELECT name FROM userdetails WHERE username = ?";
$user_stmt = $mysqli->prepare($user_query);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_row = $user_result->fetch_assoc();
$name = $user_row['name'];

// Check and update availability based on end_datetime
$update_availability_query = "
    UPDATE `$department` i
    SET i.availability = 'Available', 
        i.currently_used_by = NULL
    WHERE i.id = ? AND NOT EXISTS (
        SELECT 1 
        FROM bookings b
        WHERE b.instrument_id = i.id 
        AND b.department = ?
        AND b.end_datetime > ?
    )";
$update_stmt = $mysqli->prepare($update_availability_query);
$update_stmt->bind_param("iss", $instrument_id, $department, $current_time);
$update_stmt->execute();

// First, check if there are any active bookings and update instrument status
$check_bookings_query = "SELECT b.*, i.currently_used_by, i.last_used_by 
                        FROM bookings b
                        JOIN `$department` i ON b.instrument_id = i.id
                        WHERE b.instrument_id = ? 
                        AND b.department = ?
                        ORDER BY b.end_datetime DESC";

$check_stmt = $mysqli->prepare($check_bookings_query);
$check_stmt->bind_param("is", $instrument_id, $department);
$check_stmt->execute();
$bookings_result = $check_stmt->get_result();

$availability = 'Available';
$currently_used_by = null;
$last_used_by = null;

// Check all bookings to determine current status
while ($booking = $bookings_result->fetch_assoc()) {
    if (strtotime($current_time) >= strtotime($booking['start_datetime']) && 
        strtotime($current_time) <= strtotime($booking['end_datetime'])) {
        // Current time is within a booking period
        $availability = 'Booked';
        $currently_used_by = $booking['username'];
        if ($booking['currently_used_by'] !== null) {
            $last_used_by = $booking['currently_used_by'];
        }
        break;
    } else if (strtotime($current_time) < strtotime($booking['end_datetime'])) {
        // There's a future booking
        $availability = 'Booked';
        break;
    }
}

// Update instrument status based on current bookings
$update_status_query = "UPDATE `$department` 
                       SET availability = ?,
                           currently_used_by = ?,
                           last_used_by = ?
                       WHERE id = ?";
$update_status_stmt = $mysqli->prepare($update_status_query);
$update_status_stmt->bind_param("sssi", $availability, $currently_used_by, $last_used_by, $instrument_id);
$update_status_stmt->execute();

// Handle new booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $purpose = $_POST['purpose']; 

    // Validate datetime
    if (strtotime($end_datetime) <= strtotime($start_datetime)) {
        echo "<script>alert('End time must be after the start time.'); window.history.back();</script>";
        exit();
    }

    // Check for any existing bookings that would overlap
    $overlap_query = "SELECT * FROM bookings 
                     WHERE instrument_id = ? 
                     AND department = ? 
                     AND ((start_datetime <= ? AND end_datetime >= ?) 
                     OR (start_datetime <= ? AND end_datetime >= ?)
                     OR (start_datetime >= ? AND end_datetime <= ?))";

    $stmt = $mysqli->prepare($overlap_query);
    $stmt->bind_param("isssssss", 
        $instrument_id, 
        $department, 
        $start_datetime, 
        $start_datetime, 
        $end_datetime, 
        $end_datetime,
        $start_datetime,
        $end_datetime
    );
    $stmt->execute();
    $overlap_result = $stmt->get_result();

    if ($overlap_result->num_rows > 0) {
        echo "<script>alert('This instrument is already booked during the selected time period.'); window.history.back();</script>";
        exit();
    }

    // Start transaction for new booking
    $mysqli->begin_transaction();
    try {
        // Insert new booking
        $insert_query = "INSERT INTO bookings (instrument_id, username, start_datetime, end_datetime, department, status, purpose) 
                        VALUES (?, ?, ?, ?, ?, 'pending', ?)";
        $insert_stmt = $mysqli->prepare($insert_query);
        $insert_stmt->bind_param("isssss", $instrument_id, $username, $start_datetime, $end_datetime, $department, $purpose);
        $insert_stmt->execute();

        // Update instrument status if booking starts immediately
        if (strtotime($start_datetime) <= strtotime($current_time)) {
            $new_status_query = "UPDATE `$department` 
                               SET availability = 'Booked',
                                   currently_used_by = ?,
                                   last_used_by = ?
                               WHERE id = ?";
            $new_status_stmt = $mysqli->prepare($new_status_query);
            $new_status_stmt->bind_param("ssi", $username, $username, $instrument_id);
            $new_status_stmt->execute();
        } else {
            $new_status_query = "UPDATE `$department` 
                               SET availability = 'Booked'
                               WHERE id = ?";
            $new_status_stmt = $mysqli->prepare($new_status_query);
            $new_status_stmt->bind_param("i", $instrument_id);
            $new_status_stmt->execute();
        }

        $mysqli->commit();
        // updateEquipmentStatus($department, $mysqli);
        echo "<script>
        alert('Booking request submitted! Waiting for admin approval.');
        window.location.href = 'view_details.php';
    </script>";
        exit();
    } catch (Exception $e) {
        $mysqli->rollback();
        echo "<script>alert('Error: Could not book instrument. Please try again.'); window.history.back();</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Instrument - Lab Asset Management</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f6f9fc 0%, #edf2f7 100%);
        min-height: 100vh;
        padding: 2rem;
        color: #1a202c;
        display: flex;
        justify-content: center;
        align-items: start;
    }

    .container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
    }

    .navbar {
        background: white;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nav-brand {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2d3748;
    }

    .header {
        text-align: center;
        margin-bottom: 2rem;
    }

    h2 {
        color: #1a202c;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .booking-form {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        color: #4a5568;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .input-field {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s;
        background: #f8fafc;
    }

    .input-field:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .input-field:disabled,
    .input-field[readonly] {
        background-color: #f1f5f9;
        cursor: not-allowed;
        color: #64748b;
    }

    .datetime-field {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s;
        background: #f8fafc;
        color: #1a202c;
    }

    .datetime-field:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .submit-btn {
        width: 100%;
        padding: 0.75rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 1rem;
    }

    .submit-btn:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .info-text {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    textarea.input-field {
        resize: vertical;
        min-height: 100px;
        line-height: 1.5;
    }

    @media (max-width: 640px) {
        body {
            padding: 1rem;
        }

        .booking-form {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }
    }
    </style>
</head>

<body>
    <div class="container">
    <nav class="navbar">
    <div class="nav-brand">Lab Asset Management</div>
    <div style="display: flex; align-items: center; gap: 15px;">
        <a href="view_details.php" class="btn btn-back" style="
            background-color: #2a5298; 
            color: white; 
            text-decoration: none; 
            padding: 8px 16px; 
            border-radius: 6px; 
            font-size: 0.9rem;
        ">← Go Back</a>
        
    </div>
</nav>

        <div class="header">
            <h2>Book Instrument</h2>
        </div>

        <div class="booking-form">
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" class="input-field" name="username"
                        value="<?php echo htmlspecialchars($username); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="instrument_id">Instrument ID:</label>
                    <input type="text" id="instrument_id" class="input-field" name="instrument_id"
                        value="<?php echo htmlspecialchars($instrument_id); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="department">Department:</label>
                    <input type="text" id="department" class="input-field" name="department"
                        value="<?php echo htmlspecialchars($department); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" class="input-field" name="name"
                        value="<?php echo htmlspecialchars($name); ?>" readonly>
                </div>
                <?php
                    // Before the form, add a query to get the next availability
                    date_default_timezone_set('Asia/Kolkata');

$next_availability_query = "
SELECT MAX(end_datetime) AS next_available 
FROM bookings 
WHERE instrument_id = ? AND department = ?
";
$next_stmt = $mysqli->prepare($next_availability_query);
$next_stmt->bind_param("is", $instrument_id, $department);
$next_stmt->execute();
$next_result = $next_stmt->get_result();
$next_row = $next_result->fetch_assoc();

// Use this in the min attribute and as a JS variable
$next_available = $next_row['next_available'] 
? date('Y-m-d\TH:i', strtotime($next_row['next_available'])) 
: date('Y-m-d\TH:i');
                ?>

                <div class="form-group">
                    <label for="start_datetime">Start Date & Time:</label>
                    <input type="datetime-local" id="start_datetime" class="datetime-field" name="start_datetime"
                        required min="<?php echo $next_available; ?>" value="<?php echo $next_available; ?>">
                    <div class="info-text">Select when you want to start using the instrument</div>
                </div>

                <div class="form-group">
                    <label for="end_datetime">End Date & Time:</label>
                    <input type="datetime-local" id="end_datetime" class="datetime-field" name="end_datetime" required
                        min="<?php echo date('Y-m-d\TH:i'); ?>">
                    <div class="info-text">Select when you plan to finish using the instrument</div>
                </div>

                <div class="form-group">
    <label for="purpose">Purpose of Booking:</label>
    <textarea id="purpose" class="input-field" name="purpose" rows="3" required 
              placeholder="Briefly describe why you need this instrument"></textarea>
    <div class="info-text">Explain the research, experiment, or task you'll be using the instrument for</div>
</div>

                <button type="submit" class="submit-btn">Confirm Booking</button>
            </form>
        </div>
    </div>

    <script>
    // Set minimum datetime for end_datetime based on start_datetime
    document.getElementById('start_datetime').addEventListener('change', function() {
        document.getElementById('end_datetime').min = this.value;
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDatetime = document.getElementById('start_datetime');
        const endDatetime = document.getElementById('end_datetime');

        // Set initial min for end datetime
        endDatetime.min = startDatetime.value;

        // Update restrictions when start datetime changes
        startDatetime.addEventListener('change', function() {
            endDatetime.min = this.value;
        });

        // Prevent selecting dates before next availability
        const nextAvailable = new Date(startDatetime.min);
        startDatetime.addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            if (selectedDate < nextAvailable) {
                this.value = startDatetime.min;
            }
        });
    });
    </script>
</body>

</html>