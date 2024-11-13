<?php
session_start();
include 'connections.php';


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: user_login.php");
    exit;
}

// Departments list for dropdown selection (you can adjust this array as necessary)
$departments = ["civil", "mechanical", "cse"]; // Add other departments as needed
// $department = $_GET['department'] ?? 'civil'; // Replace 'default_department' with an actual default if needed.

// Check if form is submitted
$data = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department = $_POST['department'];
    $id = $_POST['id'];

    // Fetch details from the database based on the selected department and id
    $query = "SELECT * FROM $department WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id); // assuming `id` is an integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any data was returned
    if ($result->num_rows > 0) {
        // Fetch data
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #2c3e50;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        h2 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
            font-size: 1.1em;
        }

        .select-field, .input-field {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8f9fa;
            color: #2c3e50;
        }

        .select-field:focus, .input-field:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 14px 25px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(41, 128, 185, 0.3);
        }

        .error {
            background: #fee;
            color: #e74c3c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .details-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .details-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 20px 30px;
        }

        .details-header h3 {
            margin: 0;
            font-size: 1.8em;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .result-table th, .result-table td {
            padding: 15px 25px;
            border-bottom: 1px solid #eee;
        }

        .result-table th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            text-align: left;
            width: 200px;
        }

        .result-table td {
            color: #34495e;
        }

        .result-table tr:last-child th,
        .result-table tr:last-child td {
            border-bottom: none;
        }

        .result-table img {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 200px;
            height: auto;
        }

        .availability-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .available {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .unavailable {
            background: #ffebee;
            color: #c62828;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .form-container, .details-container {
                padding: 20px;
            }

            .result-table th, .result-table td {
                padding: 12px 15px;
            }

            .result-table th {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
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
                    <tr><th>Photo</th><td><?php if ($data['photo']): ?><img src="<?php echo htmlspecialchars($data['photo']); ?>" alt="Equipment Image"><?php else: ?>No image available<?php endif; ?></td></tr>
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
                    <!-- <tr><th>Year of Purchase</th><td><?php echo htmlspecialchars($data['year_of_purchase']); ?></td></tr>
                    <tr><th>MMD No</th><td><?php echo htmlspecialchars($data['mmd_no']); ?></td></tr>
                    <tr><th>Supplier</th><td><?php echo htmlspecialchars($data['supplier']); ?></td></tr>
                    <tr><th>Amount</th><td>₹<?php echo htmlspecialchars(number_format($data['amount'], 2)); ?></td></tr>
                    <tr><th>Fund</th><td><?php echo htmlspecialchars($data['fund']); ?></td></tr>
                    <tr><th>Incharge</th><td><?php echo htmlspecialchars($data['incharge']); ?></td></tr> -->
                </table>
            </div>
        <?php endif; ?>
        <div style="text-align: center; margin-top: 20px;">
    <button class="submit-btn" onclick="window.location.href='book_instrument.php?id=<?php echo $data['id']; ?>&department=<?php echo $department; ?>'">Book Now</button>
</div>

    </div>
</body>
</html>