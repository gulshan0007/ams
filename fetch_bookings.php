<?php
// Prevent error output from corrupting JSON response
ini_set('display_errors', 0);

session_start();
include 'connections.php';

// Create an empty array for the response
$response = [];

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode([]);
    exit;
}

try {
    // Get parameters
    $instrument_id = isset($_POST['instrument_id']) ? intval($_POST['instrument_id']) : 0;
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $start_date = isset($_POST['start']) ? $_POST['start'] : '';
    $end_date = isset($_POST['end']) ? $_POST['end'] : '';

    // Log the received parameters for debugging
    error_log("Fetch bookings - ID: $instrument_id, Dept: $department, Start: $start_date, End: $end_date");

    if (!$instrument_id) {
        echo json_encode([]);
        exit;
    }

    // Prepare query to fetch bookings for this instrument
    // Note: We're only filtering by instrument_id to ensure we get results
    $query = "SELECT id, instrument_id, username, name, start_datetime, end_datetime, department, status, purpose 
              FROM bookings 
              WHERE instrument_id = ?";

    if (!empty($department)) {
        $query .= " AND department = ?";
    }

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $mysqli->error);
    }

    if (!empty($department)) {
        $stmt->bind_param("is", $instrument_id, $department);
    } else {
        $stmt->bind_param("i", $instrument_id);
    }

    $stmt->execute();
    if ($stmt->error) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    $events = [];

    // Define color palette for different users
    $colorPalette = [
        '#3498db', // Blue
        '#e74c3c', // Red
        '#2ecc71', // Green
        '#f39c12', // Orange
        '#9b59b6', // Purple
        '#1abc9c', // Turquoise
        '#d35400', // Pumpkin
        '#34495e', // Dark Blue
        '#27ae60', // Emerald
        '#e67e22', // Carrot
        '#8e44ad', // Wisteria
        '#16a085', // Green Sea
    ];

    // Keep track of user colors
    $userColors = [];
    $colorIndex = 0;

    if ($result && $result->num_rows > 0) {
        error_log("Found " . $result->num_rows . " booking(s)");
        
        while ($row = $result->fetch_assoc()) {
            // Debug: Print raw date values for troubleshooting
            error_log("Raw start date: " . $row['start_datetime']);
            error_log("Raw end date: " . $row['end_datetime']);
            
            try {
                // First attempt with MySQL standard format
                $start_datetime = new DateTime($row['start_datetime']);
                $end_datetime = new DateTime($row['end_datetime']);
                
                // Format dates for calendar
                $start_formatted = $start_datetime->format('Y-m-d H:i:s');
                $end_formatted = $end_datetime->format('Y-m-d H:i:s');
            }
            catch (Exception $e) {
                // If standard format failed, try the DD-MM-YYYY format
                try {
                    $start_datetime = DateTime::createFromFormat('d-m-Y H:i', $row['start_datetime']);
                    $end_datetime = DateTime::createFromFormat('d-m-Y H:i', $row['end_datetime']);
                    
                    if (!$start_datetime || !$end_datetime) {
                        error_log("Date parsing failed for booking ID: " . $row['id']);
                        continue;
                    }
                    
                    // Format dates for calendar
                    $start_formatted = $start_datetime->format('Y-m-d H:i:s');
                    $end_formatted = $end_datetime->format('Y-m-d H:i:s');
                }
                catch (Exception $ex) {
                    error_log("All date parsing attempts failed for booking ID: " . $row['id']);
                    continue;
                }
            }
            
            // Assign consistent color for each user
            $username = $row['username'];
            if (!isset($userColors[$username])) {
                $userColors[$username] = $colorPalette[$colorIndex % count($colorPalette)];
                $colorIndex++;
            }
            
            $backgroundColor = $userColors[$username];
            $borderColor = adjustBrightness($backgroundColor, -20); // Slightly darker border
            
            // Add booking as calendar event
            $events[] = [
                'id' => $row['id'],
                'title' => $row['name'] . ' (' . $row['username'] . ')',
                'start' => $start_formatted,
                'end' => $end_formatted,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'textColor' => '#ffffff',
                'description' => 'Booked by: ' . $row['name'] . 
                                //  '<br>User ID: ' . $row['username'] .
                                 '<br>Purpose: ' . $row['purpose'] .
                                 '<br>Status: ' . ucfirst($row['status']) .
                                 '<br>From: ' . $row['start_datetime'] . 
                                 '<br>To: ' . $row['end_datetime'],
                'allDay' => false,
                'status' => $row['status']
            ];
        }
    } else {
        error_log("No bookings found for instrument ID: $instrument_id" . 
                 (!empty($department) ? " in department: $department" : ""));
        
        // Add a demo booking for testing if no records found
        $now = new DateTime();
        $tomorrow = (clone $now)->modify('+2 hours');
        
        $events[] = [
            'id' => 999,
            'title' => 'Test Booking',
            'start' => $now->format('Y-m-d H:i:s'),
            'end' => $tomorrow->format('Y-m-d H:i:s'),
            'backgroundColor' => '#3498db',
            'borderColor' => '#2980b9',
            'textColor' => '#ffffff',
            'description' => 'This is a test booking to verify the calendar is working.',
            'allDay' => false,
            'status' => 'approved'
        ];
    }

    echo json_encode($events);
    
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log("Calendar error: " . $e->getMessage(), 0);
    // Return empty array instead of error
    echo json_encode([]);
}

// Function to adjust color brightness
function adjustBrightness($hex, $steps) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
?>