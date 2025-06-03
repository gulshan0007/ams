<?php
session_start();
include 'connections.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: user_login.php");
    exit;
}

// Fetch unique equipment departments
$equipment_departments = [];
$query = "SELECT DISTINCT equipment_dept FROM civil";
$result = $mysqli->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $equipment_departments[] = $row['equipment_dept'];
    }
}

// Handle the form submission
$data = [];
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipment_dept = $_POST['equipment_dept'] ?? '';
    $id = $_POST['id'] ?? '';

    $query = "SELECT * FROM civil WHERE equipment_dept = ?";
    if (!empty($id)) {
        $query .= " AND id = ?";
    }

    $stmt = $mysqli->prepare($query);
    if (!empty($id)) {
        $stmt->bind_param("si", $equipment_dept, $id);
    } else {
        $stmt->bind_param("s", $equipment_dept);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        $error = "No data found for the selected department and asset ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Asset Management - View Details</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JavaScript and Popper.js (required for popovers) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.min.js"></script>
    <!-- FullCalendar Core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet" />
    <!-- FullCalendar dependencies first -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <!-- FullCalendar JS file after moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <!-- Rest of your existing styles -->
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
        padding: 20px;
        color: #1a202c;
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

    .logout-btn {
        background: #ef4444;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
    }

    .logout-btn:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .header {
        text-align: center;
        margin-bottom: 2rem;
    }

    h2 {
        color: #1a202c;
        font-size: 2.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .form-container {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        color: #4a5568;
        font-weight: 500;
    }

    .select-field,
    .input-field {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s;
        background: #f8fafc;
    }

    .select-field:focus,
    .input-field:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .submit-btn {
        width: 20%;
        padding: 0.75rem;
        background: #2a5298;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .submit-btn:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .search-container {
        position: relative;
        margin-bottom: 1.5rem;
        width: 100%;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s;
        background: #f8fafc;
    }

    .search-input:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 2px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        z-index: 10;
        display: none;
    }

    .search-dropdown.active {
        display: block;
    }

    .search-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .search-item:hover {
        background-color: #f8fafc;
    }

    .search-item .item-id {
        font-weight: bold;
        margin-right: 0.5rem;
    }

    .search-item .item-name {
        color: #718096;
    }

    .details-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .details-header {
        background: linear-gradient(135deg, #2a5298 0%, #2563eb 100%);
        color: white;
        padding: 1.5rem 2rem;
    }

    .result-table {
        width: 100%;
        border-collapse: collapse;
    }

    .result-table th,
    .result-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .result-table th {
        background: #f8fafc;
        font-weight: 600;
        text-align: left;
        width: 200px;
    }

    .availability-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 500;
    }

    .available {
        background: #dcfce7;
        color: #166534;
    }

    .unavailable {
        background: #fee2e2;
        color: #991b1b;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        text-align: center;
        font-weight: 500;
    }

    .book-btn {
        margin-top: 1.5rem;
        background: #10b981;
    }

    .book-btn:hover {
        background: #059669;
    }

    

/* Calendar Styling */
.fc {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    max-width: 100%;
    margin-bottom: 0;
}

#availability-calendar {
    width: 100% !important;
    max-width: 100%;
    overflow-x: auto;
    min-height: 500px;
}

.fc-time-grid-container {
    min-width: 800px; /* Ensure minimum width for content */
}

.fc-view-container, .fc-view {
    width: 100% !important;
}

.fc-view-container {
    min-width: 800px;
}

.fc-scroller {
    height: auto !important; /* Allow auto height - IMPORTANT CHANGE */
    overflow-y: visible !important; /* Prevent scroll - IMPORTANT CHANGE */
}



/* Responsive calendar adjustments */
@media (max-width: 768px) {
    .fc-toolbar {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .fc-toolbar .fc-left,
    .fc-toolbar .fc-right,
    .fc-toolbar .fc-center {
        float: none !important;
        margin-bottom: 10px;
    }
    
    .fc-time-grid-container {
        height: 300px !important;
    }
}

/* Time slots */
.fc-time-grid .fc-slats td {
    height: 1.5em !important; /* Reduced from 2.5em */
    padding: 0 !important;
    border-bottom: 1px solid #f0f0f0;
}
/* Today column highlighting */
.fc-today {
    background-color: rgba(255, 248, 225, 0.4) !important;
}

/* Event styling */
.fc-event {
    border-radius: 3px;
    font-size: 0.75em !important; /* Smaller font */
    border-left: 4px solid;
    padding: 1px 2px !important; /* Reduced padding */
    margin: 0 !important; /* Remove margins */
    line-height: 1.2 !important; /* Tighter line height */
}

.fc-event:hover {
    transform: scale(1.02);
    z-index: 10;
}

/* Today's events special styling */
.today-booking {
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* Calendar header styling */
.fc-toolbar {
    margin-bottom: 5px !important;
    padding: 5px !important; /* Reduced from 10px */
    background-color: #f9f9f9;
    border-radius: 8px 8px 0 0;
}

.fc-toolbar h2 {
    font-size: 1.3em;
    font-weight: 600;
    color: #333;
}

/* Button styling */
.fc-button {
    background-color: #2a5298 !important;
    color: white !important;
    border: none !important;
    box-shadow: none !important;
    text-shadow: none !important;
    border-radius: 4px !important;
    padding: 6px 12px !important;
    margin: 0 2px !important;
    transition: background-color 0.2s;
}

.fc-button:hover {
    background-color: #1a3a6c !important;
}

.fc-button.fc-state-active {
    background-color: #1a3a6c !important;
}

/* Custom navigation buttons */
.calendar-control-buttons, .calendar-view-toggles {
    display: flex;
    justify-content: center;
    margin-bottom: 15px;
    gap: 10px;
}

.calendar-btn, .view-toggle {
    background-color: #f1f1f1;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
    transition: all 0.2s;
}

.calendar-btn:hover, .view-toggle:hover {
    background-color: #e1e1e1;
}

.view-toggle.active {
    background-color: #2a5298;
    color: white;
}

/* Popover styling for event details */
.popover {
    max-width: 300px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}

.popover-title {
    background-color: #2a5298;
    color: white;
    font-weight: 600;
    padding: 8px 12px;
}

.popover-content {
    padding: 10px;
    line-height: 1.5;
}

/* Loading indicator */
#calendar-loading {
    margin: 20px auto;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 4px;
    text-align: center;
    color: #666;
}

/* Error message */
.calendar-error {
    background-color: #fee;
    color: #c33;
    padding: 15px;
    text-align: center;
    border-radius: 4px;
    margin: 10px 0;
}

/* Additional hour marker styling */
.fc-time-grid-event .fc-time {
    font-weight: bold;
}

/* Currently happening event */
.fc-event.fc-now {
    border-width: 2px;
    box-shadow: 0 0 0 2px rgba(255,255,255,0.5) inset;
}

/* Status indicator */
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #fff;
    display: inline-block;
    margin-right: 5px;
    vertical-align: middle;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.2);
}



    .fc-toolbar {
        margin-bottom: 15px !important;
        padding: 0 10px;
    }

    .fc-toolbar h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2a5298;
    }

    .fc-toolbar button {
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        color: #4a5568 !important;
        text-shadow: none !important;
        box-shadow: none !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
        transition: all 0.2s;
    }

    .fc-toolbar button:hover {
        background-color: #edf2f7 !important;
        border-color: #cbd5e0 !important;
        color: #2a5298 !important;
    }

    .fc-toolbar button:active {
        background-color: #e2e8f0 !important;
    }

    .fc-toolbar button.fc-state-active {
        background-color: #edf2f7 !important;
        border-color: #cbd5e0 !important;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        color: #2a5298 !important;
    }

    .fc-day-header {
    padding: 3px !important;
    font-size: 0.75rem !important;
    max-width: 55px !important; /* Limit width of each column header */
    width: 14.28% !important; /* Each day takes equal width */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Make sure the day grid expands properly */
.fc-time-grid-container, .fc-time-grid {
    width: 100% !important;
}

/* Make scrollable on mobile */
@media (max-width: 768px) {
    .fc-view-container {
        overflow-x: auto;
    }
    
    .fc-time-grid {
        min-width: 600px; /* Ensure minimum width for content */
    }
    
    .fc-day-header {
        max-width: 60px !important;
        font-size: 0.7rem !important;
    }
}

    .fc-day {
        border-color: #e2e8f0 !important;
    }

    .fc-time-grid-container {
        border-color: #e2e8f0 !important;
        min-width: 800px; /* Ensure minimum width for content */
    height: auto !important; /* Allow auto height - IMPORTANT CHANGE */
    max-height: 400px;
    }

    .fc-slats td {
        border-color: #f1f5f9 !important;
        height: 30px !important;
    }

    .fc-slats .fc-axis {
        font-size: 0.8rem;
        color: #94a3b8;
        font-weight: 500;
        padding-right: 8px !important;
    }

    .fc-event {
        border-radius: 6px !important;
        border: none !important;
        padding: 3px 6px !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        margin: 1px 0 !important;
        transition: all 0.2s;
    }

    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }

    .fc-event-container .fc-content {
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background-color: #ffffff;
        border-radius: 50%;
        margin-right: 6px;
        display: inline-block;
    }

    .fc-day-grid-event .fc-content {
        white-space: normal;
        overflow: hidden;
    }

    .fc-today {
        background-color: rgba(42, 82, 152, 0.05) !important;
    }

    .fc-unthemed .fc-divider,
    .fc-unthemed .fc-popover .fc-header,
    .fc-unthemed .fc-list-heading td {
        background: #f8fafc;
    }

    .fc-unthemed .fc-popover {
        border-color: #e2e8f0;
    }

    .fc-now-indicator {
        border-color: #ef4444;
    }

    .fc-now-indicator-line {
        border-top-width: 2px;
    }

    .fc-now-indicator-arrow {
        border-width: 5px;
    }

    .fc-time-grid .fc-slats .fc-minor td {
        border-top-style: dotted;
        border-top-color: #f1f5f9;
    }

    .fc-nonbusiness {
        background: #f8fafc;
    }

    /* Custom Calendar Controls */
    .calendar-control-buttons,
    .calendar-view-toggles {
        display: flex;
        justify-content: center;
        margin-bottom: 15px;
        gap: 10px;
    }

    .calendar-btn,
    .view-toggle {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #4a5568;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .calendar-btn:hover,
    .view-toggle:hover {
        background: #edf2f7;
        color: #2a5298;
        border-color: #cbd5e0;
    }

    .view-toggle.active {
        background: #2a5298;
        color: white;
        border-color: #1a3a6c;
    }

    /* Popover Styling */
    .popover {
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .popover-title {
        background-color: #2a5298;
        color: white;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        border-bottom: none;
    }

    .popover-content {
        padding: 12px;
        line-height: 1.6;
    }

    .calendar-error {
        color: #ef4444;
        background-color: #fee2e2;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
    }

    #calendar-loading {
        padding: 10px;
        text-align: center;
        color: #4a5568;
        font-style: italic;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }

        .fc-toolbar h2 {
            font-size: 1.2rem;
        }

        .fc-toolbar .fc-left,
        .fc-toolbar .fc-right {
            float: none;
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .calendar-control-buttons,
        .calendar-view-toggles {
            flex-wrap: wrap;
        }

        .fc-event {
            font-size: 0.75rem !important;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 0.5rem;
        }

        .form-container,
        .details-container {
            padding: 1rem;
        }

        .result-table th,
        .result-table td {
            padding: 0.75rem;
        }

        .result-table th {
            width: 120px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <nav class="navbar">
            <div class="nav-brand">Lab Asset Management</div>
            <a href="logout1.php" class="logout-btn">Logout</a>
        </nav>

        <div class="header">
            <h2>Asset Details Lookup</h2>
        </div>

        <div class="form-container">

            <div class="search-container">
                <input type="text" id="global-search" class="search-input" placeholder="Search by ID or Name...">
                <div class="search-dropdown"></div>
            </div>
            <form method="POST" action="view_details.php">
                <div class="form-group">
                    <label for="equipment_dept">Equipment Department:</label>
                    <select name="equipment_dept" id="equipment_dept" class="select-field" required>
                        <option value="">Select Equipment Department</option>
                        <?php foreach ($equipment_departments as $dept): ?>
                        <option value="<?php echo $dept; ?>"
                            <?php echo (isset($_POST['equipment_dept']) && $_POST['equipment_dept'] === $dept) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($dept); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id">Asset ID:</label>
                    <select name="id" id="id" class="select-field" required
                        <?php echo !isset($data['equipment_dept']) ? 'disabled' : ''; ?>>
                        <option value="">Select Asset ID</option>
                        <?php
    // Populate Asset ID dropdown based on the selected equipment_dept
    if (isset($_POST['equipment_dept']) && !empty($_POST['equipment_dept'])) {
        $equipment_dept = $_POST['equipment_dept'];

        // Query to fetch id and equipment_name
        $query = "SELECT id, equipment_name FROM civil WHERE equipment_dept = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $equipment_dept);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Loop through each result and display id and equipment_name
            while ($row = $result->fetch_assoc()) {
                $selected = (isset($_POST['id']) && $_POST['id'] == $row['id']) ? 'selected' : '';
                echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['id']) . ' - ' . htmlspecialchars($row['equipment_name']) . '</option>';
            }
        } else {
            // If no results found, show a placeholder
            echo '<option value="">No assets found</option>';
        }
    }
    ?>
                    </select>


                </div>
                <div style="display: flex; justify-content: center; align-items: center; height: 5vh;">

                    <button type="submit" class="submit-btn">Search Asset</button>
                </div>
            </form>
        </div>

        <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($data)): ?>
        <div class="details-container">
            <div class="details-header">
                <h3>Asset Information</h3>
            </div>
            <table class="result-table">
                <tr>
                    <th>ID</th>
                    <td><?php echo htmlspecialchars($data['id']); ?></td>
                </tr>
                <tr>
                    <th>Equipment Name</th>
                    <td><?php echo htmlspecialchars($data['equipment_name']); ?></td>
                </tr>
                <tr>
                    <th>Equipment Department</th>
                    <td><?php echo htmlspecialchars($data['equipment_dept']); ?></td>
                </tr>
                <tr>
                    <th>Photo</th>
                    <td>
                        <?php if ($data['photo']): ?>
                        <img src="<?php echo htmlspecialchars($data['photo']); ?>" alt="Equipment Image"
                            style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <?php else: ?>
                        No image available
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Specification</th>
                    <td><?php echo htmlspecialchars($data['specification']); ?></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><?php echo htmlspecialchars($data['description']); ?></td>
                </tr>
                <tr>
                    <th>Purpose</th>
                    <td><?php echo htmlspecialchars($data['purpose']); ?></td>
                </tr>
                <tr>
                    <th>Users</th>
                    <td><?php echo htmlspecialchars($data['users']); ?></td>
                </tr>
                <!-- <tr><th>Status</th> -->
                <!-- <td>
                            <span class="availability-badge <?php echo strtolower($data['availability']) === 'available' ? 'available' : 'unavailable'; ?>">
                                <?php echo htmlspecialchars($data['availability']); ?>
                            </span>
                        </td> -->
                <!-- </tr> -->
                <tr>
    <th>Next Availability</th>
    <td style="padding: 0; vertical-align: top;">
        <div style="width: 100%; overflow: hidden; border-radius: 8px;">
            <div id="availability-calendar" style="width: 100%;"></div>
            <div id="calendar-loading" style="text-align: center; margin: 10px 0; display: none;">
                Loading booking information...
            </div>
        </div>
    </td>
</tr>
                <td>
                    <?php
        // Ensure $data['id'] and $data['equipment_dept'] are used correctly
        $instrument_id = $data['id'];
        $department = "civil";;

        // Fetch the farthest end_datetime for this instrument
        $booked_query = "SELECT MAX(end_datetime) AS booked_till FROM bookings WHERE instrument_id = ? AND department = ?";
        $booked_stmt = $mysqli->prepare($booked_query);
        
        // Check if preparation was successful
        if ($booked_stmt) {
            $booked_stmt->bind_param("is", $instrument_id, $department);
            $booked_stmt->execute();
            $booked_result = $booked_stmt->get_result();

            if ($booked_result) {
                $booked_row = $booked_result->fetch_assoc();
                if ($booked_row['booked_till']) {
                    $date = new DateTime($booked_row['booked_till']);
                    echo $date->format('M j, Y g:i A');
                } else {
                    echo 'Available for Booking'; // If no bookings exist
                }
            } else {
                echo 'Error fetching bookings.';
            }
            $booked_stmt->close();
        } else {
            echo 'Query preparation failed.';
        }
    ?>
                </td>
                </tr>

                </tr>
                <!-- <tr><th>Currently Used By</th><td><?php echo htmlspecialchars($data['currently_used_by']); ?></td></tr>
                    <tr><th>Last Used By</th><td><?php echo htmlspecialchars($data['last_used_by']); ?></td></tr> -->
            </table>
        </div>
        <div style="text-align: center;">
            <button class="submit-btn book-btn"
                onclick="window.location.href='book_instrument.php?id=<?php echo $data['id']; ?>&department=civil'">Book
                Now</button>
        </div>
        <?php endif; ?>
    </div>

    <script>
    $(document).ready(function() {
        // Trigger AJAX call when equipment_dept changes
        $('#equipment_dept').change(function() {
            var equipment_dept = $(this).val();

            // If an equipment department is selected, fetch the asset IDs
            if (equipment_dept) {
                $.ajax({
                    url: 'fetch_asset_ids.php',
                    type: 'POST',
                    data: {
                        equipment_dept: equipment_dept
                    },
                    success: function(response) {
                        // Update the asset ID dropdown with the fetched options
                        $('#id').html(response);
                        $('#id').prop('disabled', false); // Enable the asset ID dropdown
                    }
                });
            } else {
                $('#id').html('<option value="">Select Asset ID</option>');
                $('#id').prop('disabled', true); // Disable the asset ID dropdown
            }
        });
    });
    </script>
    <script>
    $(document).ready(function() {
        const searchInput = $('#global-search');
        const searchDropdown = $('.search-dropdown');
        let allInstruments = [];

        // Fetch all instruments from all departments
        function fetchAllInstruments() {
            $.ajax({
                url: 'fetch_all_instruments.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    allInstruments = response;
                }
            });
        }

        // Filter and display instruments
        function filterInstruments(query) {
            const filteredInstruments = allInstruments.filter(instrument =>
                instrument.id.toString().includes(query) ||
                instrument.equipment_name.toLowerCase().includes(query.toLowerCase())
            );

            const dropdownContent = filteredInstruments.map(instrument =>
                `<div class="search-item" data-id="${instrument.id}" data-dept="${instrument.equipment_dept}">
                <span class="item-id">${instrument.id}</span>
                <span class="item-name">${instrument.equipment_name}</span>
                <span class="item-dept">(${instrument.equipment_dept})</span>
            </div>`
            ).join('');

            searchDropdown.html(dropdownContent);
            searchDropdown.toggleClass('active', filteredInstruments.length > 0);
        }

        // Search input event
        searchInput.on('input', function() {
            const query = $(this).val();
            if (query.length >= 2) {
                filterInstruments(query);
            } else {
                searchDropdown.removeClass('active');
            }
        });

        // Select instrument from dropdown
        $(document).on('click', '.search-item', function() {
            const id = $(this).data('id');
            const dept = $(this).data('dept');

            // Set department and ID in the form
            $('#equipment_dept').val(dept);

            // Trigger department change to populate asset IDs
            $('#equipment_dept').trigger('change');

            // Wait for the asset ID dropdown to populate, then select the right option
            setTimeout(() => {
                $('#id').val(id);
                searchInput.val('');
                searchDropdown.removeClass('active');

                // Optionally, submit the form
                $('form').submit();
            }, 300);
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-container').length) {
                searchDropdown.removeClass('active');
            }
        });

        // Initial fetch of all instruments
        fetchAllInstruments();
    });
    </script>
    <script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Store the instrument ID and department for AJAX call
    var instrumentId = <?php echo isset($data['id']) ? $data['id'] : 0; ?>;
    var instrumentDept = 'civil';
    
    // Check if we have valid instrument ID before proceeding
    if (!instrumentId) {
        console.log("No instrument ID found. Calendar will not be loaded.");
        $('#availability-calendar').html('<div class="calendar-error">Please select an instrument to view booking calendar.</div>');
        return;
    }
    
    // Check if FullCalendar is available
    if (typeof jQuery.fn.fullCalendar !== 'function') {
        console.error("FullCalendar library not loaded correctly");
        $('#availability-calendar').html('<div class="calendar-error">Calendar could not be loaded. Please check your internet connection and refresh the page.</div>');
        return;
    }
    
    console.log("Initializing calendar for instrument ID: " + instrumentId + " in department: " + instrumentDept);
    
    // Initialize the calendar
    $('#availability-calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'agendaWeek,agendaDay'
        },
        defaultView: 'agendaWeek',
        themeSystem: 'standard',
        height: 500,
        contentHeight: 'auto',
        aspectRatio: 1.5,
        editable: false,
        eventLimit: true,
        timeFormat: 'h:mm a',
        slotLabelFormat: 'h:mm a',
        allDaySlot: false,
        minTime: '08:00:00',
        maxTime: '20:00:00',
        slotDuration: '00:30:00',
        slotLabelInterval: '01:00:00',
        contentWidth: '100%',
        nowIndicator: true,
        views: {
            agendaWeek: {
            columnHeaderFormat: 'ddd D',
            timeFormat: 'h:mm a',
            duration: { days: 7 },  // Ensure 7 days are displayed
            columnWidth: 14.28     // Let columns adjust to available width
            },
            month: {
                columnHeaderFormat: 'ddd D'
            },
            week: {
                columnHeaderFormat: 'ddd D/M',
                timeFormat: 'h:mm a'
            },
            day: {
                columnHeaderFormat: 'dddd D MMMM',
                timeFormat: 'h:mm a'
            }
        },
        businessHours: {
            start: '09:00',
            end: '17:00',
            dow: [1, 2, 3, 4, 5] // Monday - Friday
        },
        loading: function(isLoading) {
            // Show/hide loading indicator
            if (isLoading) {
                $('#calendar-loading').show();
            } else {
                $('#calendar-loading').hide();
            }
        },
        events: function(start, end, timezone, callback) {
            console.log("Fetching events for date range:", start.format(), "to", end.format());
            
            // Fetch events via AJAX
            $.ajax({
                url: 'fetch_bookings.php',
                type: 'POST',
                data: {
                    instrument_id: instrumentId,
                    department: instrumentDept,
                    start: start.format(),
                    end: end.format()
                },
                dataType: 'json', // Explicitly expect JSON response
                success: function(response) {
                    console.log("Received response:", response);
                    // Check if response is already an object
                    if (response && typeof response === 'object') {
                        callback(response);
                    } else {
                        console.error("Unexpected response format");
                        callback([]);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    callback([]);
                }
            });
        },
        eventRender: function(event, element) {
            // Add tooltip with booking information
            element.popover({
                title: 'Booking Details',
                content: event.description || 'No details available',
                trigger: 'hover',
                placement: 'top',
                container: 'body',
                html: true
            });

            // Add status indicator
            var now = moment();
            var isHappeningNow = event.start <= now && event.end >= now;
            
            if (isHappeningNow) {
                element.addClass('fc-now');
                element.find('.fc-content').prepend('<span class="status-dot" style="background-color: #2ecc71;"></span>');
            } else {
                element.find('.fc-content').prepend('<span class="status-dot"></span>');
            }
        },
        dayRender: function(date, cell) {
            // Highlight today
            if (date.isSame(moment(), 'day')) {
                cell.css('background-color', 'rgba(255, 248, 225, 0.4)');
            }
            
            // Weekend styling
            if (date.day() === 0 || date.day() === 6) {
                cell.css('background-color', 'rgba(0, 0, 0, 0.02)');
            }
        }
    });

    // // Add calendar navigation buttons
    // if ($('.calendar-control-buttons').length === 0) {
    //     $('<div class="calendar-control-buttons">' +
    //         '<button id="prev-week" class="calendar-btn">Previous</button>' +
    //         '<button id="today-btn" class="calendar-btn">Today</button>' +
    //         '<button id="next-week" class="calendar-btn">Next</button>' +
    //     '</div>').insertBefore('#availability-calendar');
    // }

    // Button event handlers
    $(document).on('click', '#prev-week', function() {
        $('#availability-calendar').fullCalendar('prev');
    });

    $(document).on('click', '#today-btn', function() {
        $('#availability-calendar').fullCalendar('today');
    });

    $(document).on('click', '#next-week', function() {
        $('#availability-calendar').fullCalendar('next');
    });

    // Add view toggles
    // if ($('.calendar-view-toggles').length === 0) {
    //     $('<div class="calendar-view-toggles">' +
    //         '<button class="view-toggle" data-view="month">Month</button>' +
    //         '<button class="view-toggle active" data-view="agendaWeek">Week</button>' +
    //         '<button class="view-toggle" data-view="agendaDay">Day</button>' +
    //     '</div>').insertBefore('#availability-calendar');
    // }

    // View toggle event handlers
    $(document).on('click', '.view-toggle', function() {
        $('.view-toggle').removeClass('active');
        $(this).addClass('active');
        $('#availability-calendar').fullCalendar('changeView', $(this).data('view'));
    });
});
</script>
</body>

</html>



