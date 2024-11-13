<?php
session_start();
include 'connections.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: login.php");
    exit();
}

$department = $_SESSION['department'];
$table_exists_query = "SHOW TABLES LIKE '$department'";
$result = $mysqli->query($table_exists_query);

if ($result->num_rows == 0) {
    // Create table if it doesn't exist
    $create_table_query = "CREATE TABLE `$department` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        equipment_name VARCHAR(255) NOT NULL,
        photo VARCHAR(255),
        specification TEXT,
        description TEXT,
        purpose TEXT,
        users TEXT,
        availability VARCHAR(50),
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

// Image upload directory
$upload_dir = 'uploads/';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $equipment_name = $_POST['equipment_name'];
        $photo = null;

        // Handle file upload if a file was submitted
        if (!empty($_FILES['photo']['name'])) {
            $photo = $upload_dir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        }

        $stmt = $mysqli->prepare("INSERT INTO `$department` (equipment_name, photo, specification, description, purpose, users, availability, currently_used_by, last_used_by, year_of_purchase, mmd_no, supplier, amount, fund, incharge) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssissss",
            $equipment_name, $photo, $_POST['specification'], $_POST['description'], $_POST['purpose'], $_POST['users'],
            $_POST['availability'], $_POST['currently_used_by'], $_POST['last_used_by'], $_POST['year_of_purchase'], $_POST['mmd_no'],
            $_POST['supplier'], $_POST['amount'], $_POST['fund'], $_POST['incharge']);
        $stmt->execute();
    } elseif (isset($_POST['delete_id'])) {
        $stmt = $mysqli->prepare("DELETE FROM `$department` WHERE id = ?");
        $stmt->bind_param("i", $_POST['delete_id']);
        $stmt->execute();
    }
}

$query = "SELECT * FROM `$department`";
$result = $mysqli->query($query);
?>

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
        }

        .header-info h2, .header-info h3 {
            margin: 0;
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
            <div class="header-info">
                <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
                <h3>Department: <?php echo strtoupper($_SESSION['department']); ?></h3>
            </div>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>

        <!-- Equipment List Table -->
        <table class="equipment-table">
            <tr>
                <th>ID</th>
                <th>Equipment Name</th>
                <th>Photo</th>
                <th>Specification</th>
                <th>Description</th>
                <th>Purpose</th>
                <th>Users</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['equipment_name']; ?></td>
                    <td>
                        <?php if ($row['photo']): ?>
                            <img src="<?php echo $row['photo']; ?>" alt="Equipment Photo" width="100">
                        <?php else: ?>
                            No photo
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row['specification']; ?></td>
                    <td><?php echo $row['description']; ?></td>
                    <td><?php echo $row['purpose']; ?></td>
                    <td><?php echo $row['users']; ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($row['availability']) === 'available' ? 'status-available' : 'status-unavailable'; ?>">
                            <?php echo $row['availability']; ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-delete">Delete</button>
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
                        <label>Equipment Name</label>
                        <input type="text" name="equipment_name" required>
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
                    <div class="form-group">
                        <label>Users</label>
                        <input type="text" name="users">
                    </div>
                    <div class="form-group">
                        <label>Availability</label>
                        <input type="text" name="availability">
                    </div>
                    <div class="form-group">
                        <label>Currently Used By</label>
                        <input type="text" name="currently_used_by">
                    </div>
                    <div class="form-group">
                        <label>Last Used By</label>
                        <input type="text" name="last_used_by">
                    </div>
                    <div class="form-group">
                        <label>Year of Purchase</label>
                        <input type="text" name="year_of_purchase">
                    </div>
                    <div class="form-group">
                        <label>MMD No</label>
                        <input type="number" name="mmd_no">
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" name="supplier">
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
                        <input type="text" name="incharge">
                    </div>
                </div>
                <button type="submit" name="add" class="btn btn-add">Add Equipment</button>
            </form>
        </div>
    </div>
</body>
</html>