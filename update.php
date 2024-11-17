<?php
session_start();
include 'connections.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['department'])) {
    header("Location: login.php");
    exit();
}

$department = $_SESSION['department'];

// Check if an ID is provided for editing
if (!isset($_GET['id'])) {
    echo "No equipment ID provided!";
    exit();
}

$equipment_id = intval($_GET['id']);

// Fetch the equipment details
$query = "SELECT * FROM $department WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No equipment found with the provided ID.";
    exit();
}

$equipment = $result->fetch_assoc();
$stmt->close();

// Handle form submission for updating equipment details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipment_name = $_POST['equipment_name'] ?? $equipment['equipment_name'];
    $specification = $_POST['specification'] ?? $equipment['specification'];
    $description = $_POST['description'] ?? $equipment['description'];
    $purpose = $_POST['purpose'] ?? $equipment['purpose'];
    $users = $_POST['users'] ?? $equipment['users'];
    $year_of_purchase = $_POST['year_of_purchase'] ?? $equipment['year_of_purchase'];
    $mmd_no = intval($_POST['mmd_no'] ?? $equipment['mmd_no']);
    $supplier = $_POST['supplier'] ?? $equipment['supplier'];
    $amount = floatval($_POST['amount'] ?? $equipment['amount']);
    $fund = $_POST['fund'] ?? $equipment['fund'];
    $incharge = $_POST['incharge'] ?? $equipment['incharge'];
    $photo = $equipment['photo'];

    // Handle file upload for photo
    $upload_dir = 'uploads/';
    if (!empty($_FILES['photo']['name'])) {
        $photo = $upload_dir . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    // Update query
    $update_query = "UPDATE $department SET 
        equipment_name = ?, photo = ?, specification = ?, description = ?, purpose = ?, 
        users = ?, year_of_purchase = ?, mmd_no = ?, supplier = ?, amount = ?, 
        fund = ?, incharge = ? WHERE id = ?";

    if ($stmt = $mysqli->prepare($update_query)) {
        $stmt->bind_param(
            "sssssssisdsii",
            $equipment_name,
            $photo,
            $specification,
            $description,
            $purpose,
            $users,
            $year_of_purchase,
            $mmd_no,
            $supplier,
            $amount,
            $fund,
            $incharge,
            $equipment_id
        );

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error in preparing statement: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment - Lab Asset Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #D1D5DB;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out;
        }
        .form-group input:focus {
            outline: none;
            border-color: #60A5FA;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
        }
        .btn-update {
            background-color: #2563EB;
            color: white;
        }
        .btn-update:hover {
            background-color: #1D4ED8;
        }
        .btn-cancel {
            background-color: #9CA3AF;
            color: white;
            margin-left: 0.75rem;
        }
        .btn-cancel:hover {
            background-color: #6B7280;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="container max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Edit Equipment Details</h2>
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 text-sm">Back to Dashboard</a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label>Equipment Name</label>
                            <input type="text" name="equipment_name" value="<?php echo htmlspecialchars($equipment['equipment_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Photo</label>
                            <div class="flex items-center space-x-4">
                                <input type="file" name="photo" accept="image/*" class="flex-1">
                                <?php if ($equipment['photo']): ?>
                                    <img src="<?php echo htmlspecialchars($equipment['photo']); ?>" alt="Current Photo" class="w-16 h-16 object-cover rounded">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Specification</label>
                            <input type="text" name="specification" value="<?php echo htmlspecialchars($equipment['specification']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description" value="<?php echo htmlspecialchars($equipment['description']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Purpose</label>
                            <input type="text" name="purpose" value="<?php echo htmlspecialchars($equipment['purpose']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Users</label>
                            <input type="text" name="users" value="<?php echo htmlspecialchars($equipment['users']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Year of Purchase</label>
                            <input type="text" name="year_of_purchase" value="<?php echo htmlspecialchars($equipment['year_of_purchase']); ?>">
                        </div>

                        <div class="form-group">
                            <label>MMD No</label>
                            <input type="number" name="mmd_no" value="<?php echo htmlspecialchars($equipment['mmd_no']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Supplier</label>
                            <input type="text" name="supplier" value="<?php echo htmlspecialchars($equipment['supplier']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($equipment['amount']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Fund</label>
                            <input type="text" name="fund" value="<?php echo htmlspecialchars($equipment['fund']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Incharge</label>
                            <input type="text" name="incharge" value="<?php echo htmlspecialchars($equipment['incharge']); ?>">
                        </div>
                    </div>

                    <div class="flex justify-end mt-8">
                        <button type="submit" class="btn btn-update">Update Equipment</button>
                        <a href="dashboard.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>