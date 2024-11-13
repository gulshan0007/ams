<?php
include 'connections.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $department = $_POST['department'];

    // Insert user data into userdetails
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO userdetails (username, password, department) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sss", $username, $hashedPassword, $department);

    if ($stmt->execute()) {
        header("Location: user_login.php"); // Redirect to login page
        exit();
    } else {
        echo "Error: Could not register user.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
</head>
<body>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <label>Department:</label>
        <input type="text" name="department" required>
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>
