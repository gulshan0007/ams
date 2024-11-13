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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];

    // Insert booking information into instrument_availability
    $query = "INSERT INTO instrument_availability (instrument_id, username, start_datetime, end_datetime, department) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("issss", $instrument_id, $username, $start_datetime, $end_datetime, $department);

    if ($stmt->execute()) {
        echo "<script>alert('Booking confirmed!'); window.close();</script>";
        exit();
    } else {
        echo "Error: Could not book instrument.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Instrument</title>
</head>
<body>
    <h2>Book Instrument</h2>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly><br>

        <label>Instrument ID:</label>
        <input type="text" name="instrument_id" value="<?php echo htmlspecialchars($instrument_id); ?>" readonly><br>

        <label>Department:</label>
        <input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>" readonly><br>

        <label>Start Date & Time:</label>
        <input type="datetime-local" name="start_datetime" required><br>

        <label>End Date & Time:</label>
        <input type="datetime-local" name="end_datetime" required><br>

        <button type="submit">Submit Booking</button>
    </form>
</body>
</html>
