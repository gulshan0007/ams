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

        .select-field, .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .select-field:focus, .input-field:focus {
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

        .result-table th, .result-table td {
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

        @media (max-width: 768px) {
            .container {
                padding: 0.5rem;
            }

            .form-container, .details-container {
                padding: 1rem;
            }

            .result-table th, .result-table td {
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
        <form method="POST" action="view_details.php">
                <div class="form-group">
                    <label for="equipment_dept">Equipment Department:</label>
                    <select name="equipment_dept" id="equipment_dept" class="select-field" required>
                        <option value="">Select Equipment Department</option>
                        <?php foreach ($equipment_departments as $dept): ?>
                            <option value="<?php echo $dept; ?>" <?php echo (isset($_POST['equipment_dept']) && $_POST['equipment_dept'] === $dept) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                <label for="id">Asset ID:</label>
                <select name="id" id="id" class="select-field" required <?php echo !isset($data['equipment_dept']) ? 'disabled' : ''; ?>>
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
                    <tr><th>ID</th><td><?php echo htmlspecialchars($data['id']); ?></td></tr>
                    <tr><th>Equipment Name</th><td><?php echo htmlspecialchars($data['equipment_name']); ?></td></tr>
                    <tr><th>Equipment Department</th><td><?php echo htmlspecialchars($data['equipment_dept']); ?></td></tr>
                    <tr><th>Photo</th>
                        <td>
                            <?php if ($data['photo']): ?>
                                <img src="<?php echo htmlspecialchars($data['photo']); ?>" alt="Equipment Image" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <?php else: ?>
                                No image available
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th>Specification</th><td><?php echo htmlspecialchars($data['specification']); ?></td></tr>
                    <tr><th>Description</th><td><?php echo htmlspecialchars($data['description']); ?></td></tr>
                    <tr><th>Purpose</th><td><?php echo htmlspecialchars($data['purpose']); ?></td></tr>
                    <tr><th>Users</th><td><?php echo htmlspecialchars($data['users']); ?></td></tr>
                    <!-- <tr><th>Status</th> -->
                        <!-- <td>
                            <span class="availability-badge <?php echo strtolower($data['availability']) === 'available' ? 'available' : 'unavailable'; ?>">
                                <?php echo htmlspecialchars($data['availability']); ?>
                            </span>
                        </td> -->
                    <!-- </tr> -->
                    <tr><th>Next Availability</th>
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
                <button class="submit-btn book-btn" onclick="window.location.href='book_instrument.php?id=<?php echo $data['id']; ?>&department=civil'">Book Now</button>
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
                        data: { equipment_dept: equipment_dept },
                        success: function(response) {
                            // Update the asset ID dropdown with the fetched options
                            $('#id').html(response);
                            $('#id').prop('disabled', false);  // Enable the asset ID dropdown
                        }
                    });
                } else {
                    $('#id').html('<option value="">Select Asset ID</option>');
                    $('#id').prop('disabled', true);  // Disable the asset ID dropdown
                }
            });
        });
    </script>
</body>
</html>



<!-- <?php
session_start();
include 'connections.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: user_login.php");
    exit;
}

$departments = ["civil", "mechanical", "cse"];
$data = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department = $_POST['department'];
    $id = $_POST['id'];

    $query = "SELECT * FROM $department WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        $error = "No data found for ID $id in the $department department.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Asset Management - View Details</title>
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
            <form method="POST" action="view_details.php">
                <div class="form-group">
                    <label for="department">Department:</label>
                    <select name="department" id="department" class="select-field" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept; ?>"><?php echo ucfirst($dept); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id">Asset ID:</label>
                    <input type="number" name="id" id="id" class="input-field" required>
                </div>

                <button type="submit" class="submit-btn">Search Asset</button>
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
                    <tr><th>ID</th><td><?php echo htmlspecialchars($data['id']); ?></td></tr>
                    <tr><th>Equipment Name</th><td><?php echo htmlspecialchars($data['equipment_name']); ?></td></tr>
                    <tr><th>Equipment Department</th><td><?php echo htmlspecialchars($data['equipment_dept']); ?></td></tr>
                    <tr><th>Photo</th><td><?php if ($data['photo']): ?><img src="<?php echo htmlspecialchars($data['photo']); ?>" alt="Equipment Image" style="max-width: 200px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><?php else: ?>No image available<?php endif; ?></td></tr>
                    <tr><th>Specification</th><td><?php echo htmlspecialchars($data['specification']); ?></td></tr>
                    <tr><th>Description</th><td><?php echo htmlspecialchars($data['description']); ?></td></tr>
                    <tr><th>Purpose</th><td><?php echo htmlspecialchars($data['purpose']); ?></td></tr>
                    <tr><th>Users</th><td><?php echo htmlspecialchars($data['users']); ?></td></tr>
                    <tr><th>Availability</th>
                        <td>
                            <span class="availability-badge <?php echo strtolower($data['availability']) === 'available' ? 'available' : 'unavailable'; ?>">
                                <?php echo htmlspecialchars($data['availability']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr><th>Currently Used By</th><td><?php echo htmlspecialchars($data['currently_used_by']); ?></td></tr>
                    <tr><th>Last Used By</th><td><?php echo htmlspecialchars($data['last_used_by']); ?></td></tr>
                </table>
            </div>

            <!-- <?php if (strtolower($data['availability']) === 'available'): ?>
                <div style="text-align: center;">
                    <button class="submit-btn book-btn" onclick="window.location.href='book_instrument.php?id=<?php echo $data['id']; ?>&department=<?php echo $department; ?>'">Book Now</button>
                </div>
            <?php endif; ?> -->
            <div style="text-align: center;">
                    <button class="submit-btn book-btn" onclick="window.location.href='book_instrument.php?id=<?php echo $data['id']; ?>&department=<?php echo $department; ?>'">Book Now</button>
                </div>
        <?php endif; ?>
    </div>
</body>
</html> -->
