<?php
session_start();
include 'connections.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: login.php");
    exit();
}

$department = $_SESSION['department'];

// Handle booking approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['booking_id']) && isset($_POST['action'])) {
        $booking_id = $_POST['booking_id'];
        $action = $_POST['action'];
        
        if ($action == 'approve') {
            // Update booking status to approved
            $update_query = "UPDATE bookings SET status = 'approved' WHERE id = ? AND department = ?";
            $stmt = $mysqli->prepare($update_query);
            $stmt->bind_param("is", $booking_id, $department);
            
            if ($stmt->execute()) {
                // Get booking details
                $booking_query = "SELECT instrument_id, start_datetime FROM bookings WHERE id = ?";
                $stmt = $mysqli->prepare($booking_query);
                $stmt->bind_param("i", $booking_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $booking = $result->fetch_assoc();
                
                // Update instrument status if booking starts immediately
                $current_time = date("Y-m-d H:i:s");
                if (strtotime($booking['start_datetime']) <= strtotime($current_time)) {
                    $update_instrument = "UPDATE `$department` SET availability = 'Booked' WHERE id = ?";
                    $stmt = $mysqli->prepare($update_instrument);
                    $stmt->bind_param("i", $booking['instrument_id']);
                    $stmt->execute();
                }
                echo "<script>alert('Booking Approved!');</script>";
            } else {
                echo "<script>alert('Error updating booking status.');</script>";
            }
        } elseif ($action == 'reject') {
            // Delete the rejected booking
            $delete_query = "DELETE FROM bookings WHERE id = ? AND department = ?";
            $stmt = $mysqli->prepare($delete_query);
            $stmt->bind_param("is", $booking_id, $department);
            
            if ($stmt->execute()) {
                echo "<script>alert('Booking Rejected and Removed!');</script>";
            } else {
                echo "<script>alert('Error rejecting booking.');</script>";
            }
        }
    }
}

// Fetch pending bookings
$query = "SELECT b.*, e.equipment_name, u.username as user_name 
          FROM bookings b 
          JOIN `$department` e ON b.instrument_id = e.id 
          LEFT JOIN users u ON b.username = u.username
          WHERE b.department = ? AND b.status = 'pending' 
          ORDER BY b.start_datetime ASC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Bookings - Lab Asset Management</title>
    <style>
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .booking-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .booking-table th, 
        .booking-table td { 
            padding: 12px 15px; 
            border: 1px solid #ddd; 
        }
        .booking-table th { 
            background-color: #f5f5f5; 
            font-weight: bold;
            text-align: left;
        }
        .booking-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .booking-table tr:hover {
            background-color: #f2f2f2;
        }
        .btn { 
            padding: 8px 16px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin: 0 5px; 
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-approve { 
            background-color: #4CAF50; 
            color: white; 
        }
        .btn-reject { 
            background-color: #f44336; 
            color: white; 
        }
        .btn-back { 
            background-color: #2196F3; 
            color: white; 
            text-decoration: none; 
            display: inline-block;
        }
        .no-bookings {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            .booking-table {
                font-size: 14px;
            }
            .btn {
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pending Bookings</h1>
            <a href="dashboard.php" class="btn btn-back">Back to Dashboard</a>
        </div>

        <table class="booking-table">
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Requested By</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($booking = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['equipment_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($booking['start_datetime'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($booking['end_datetime'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-bookings">No pending bookings available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>