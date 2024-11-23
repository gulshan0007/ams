<?php
session_start();
include 'connections.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Set timezone to Asia/Kolkata
date_default_timezone_set('Asia/Kolkata');

// Function to send booking status email
function sendBookingStatusEmail($email, $status, $equipment_name, $start_datetime, $end_datetime) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gulshankumar060102@gmail.com';
        $mail->Password   = 'bmmz zjnm zbnk kyrb'; // Use App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('gulshan.iitb@gmail.com', 'Lab Assets Management');
        $mail->addAddress($email);
        $mail->isHTML(true);

        $statusText = ucfirst($status);
        $mail->Subject = "Lab Asset Booking {$statusText}";

        $bodyMessage = "
        <html>
        <body>
            <h2>Booking {$statusText}</h2>
            <p>Your booking request for <strong>{$equipment_name}</strong> has been {$status}.</p>
            <p><strong>Start Time:</strong> " . date('M j, Y g:i A', strtotime($start_datetime)) . "</p>
            <p><strong>End Time:</strong> " . date('M j, Y g:i A', strtotime($end_datetime)) . "</p>
            <p>Please contact your department administrator for any questions.</p>
        </body>
        </html>
        ";

        $mail->Body = $bodyMessage;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

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
        
        // First, get booking details to send email
        $booking_query = "SELECT instrument_id, start_datetime, end_datetime, username, equipment_name 
                          FROM bookings b 
                          JOIN `$department` e ON b.instrument_id = e.id 
                          WHERE b.id = ?";
        $stmt = $mysqli->prepare($booking_query);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();

        $email = $booking['username'] . '@iitb.ac.in';
        
        if ($action == 'approve') {
            // Update booking status to approved
            $update_query = "UPDATE bookings SET status = 'approved' WHERE id = ? AND department = ?";
            $stmt = $mysqli->prepare($update_query);
            $stmt->bind_param("is", $booking_id, $department);
            
            if ($stmt->execute()) {
                // Send approval email
                sendBookingStatusEmail(
                    $email, 
                    'approved', 
                    $booking['equipment_name'], 
                    $booking['start_datetime'], 
                    $booking['end_datetime']
                );

                // Get booking details
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
                // Send rejection email
                sendBookingStatusEmail(
                    $email, 
                    'rejected', 
                    $booking['equipment_name'], 
                    $booking['start_datetime'], 
                    $booking['end_datetime']
                );
                
                echo "<script>alert('Booking Rejected and Removed!');</script>";
            } else {
                echo "<script>alert('Error rejecting booking.');</script>";
            }
        }
    }
}

// Fetch pending bookings
$pending_query = "SELECT b.*, e.equipment_name, u.username as user_name 
                  FROM bookings b 
                  JOIN `$department` e ON b.instrument_id = e.id 
                  LEFT JOIN users u ON b.username = u.username
                  WHERE b.department = ? AND b.status = 'pending' 
                  ORDER BY b.start_datetime ASC";

$stmt = $mysqli->prepare($pending_query);
$stmt->bind_param("s", $department);
$stmt->execute();
$pending_result = $stmt->get_result();

// Fetch previously approved bookings
$approved_query = "SELECT b.*, e.equipment_name, u.username as user_name 
                   FROM bookings b 
                   JOIN `$department` e ON b.instrument_id = e.id 
                   LEFT JOIN users u ON b.username = u.username
                   WHERE b.department = ? AND b.status = 'approved' 
                   ORDER BY b.start_datetime DESC";

$stmt = $mysqli->prepare($approved_query);
$stmt->bind_param("s", $department);
$stmt->execute();
$approved_result = $stmt->get_result();
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
            background: rgba(255, 255, 255, 0.1);
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
            background-color: #2a5298; 
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
        .bookings-section {
            margin-top: 30px;
        }
        .section-header {
            background-color: #f1f1f1;
            padding: 10px 15px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .past-booking {
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
            <h1>Booking Management</h1>
            <a href="dashboard.php" class="btn btn-back">Back to Dashboard</a>
        </div>

        <!-- Pending Bookings Section -->
        <div class="bookings-section">
            <div class="section-header">Pending Bookings</div>
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
                    <?php if ($pending_result->num_rows > 0): ?>
                        <?php while ($booking = $pending_result->fetch_assoc()): ?>
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

        <!-- Approved Bookings Section -->
        <div class="bookings-section">
            <div class="section-header">Approved Bookings</div>
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Booked By</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($approved_result->num_rows > 0): ?>
                        <?php while ($booking = $approved_result->fetch_assoc()): 
                            $current_time = new DateTime();
                            $end_time = new DateTime($booking['end_datetime']);
                            $is_past_booking = $end_time < $current_time;
                        ?>
                            <tr class="<?php echo $is_past_booking ? 'past-booking' : ''; ?>">
                                <td><?php echo htmlspecialchars($booking['equipment_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($booking['start_datetime'])); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($booking['end_datetime'])); ?></td>
                                <td>
                                    <?php if (!$is_past_booking): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                                    </form>
                                    <?php else: ?>
                                        <span class="past-booking">Booking completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-bookings">No approved bookings available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>