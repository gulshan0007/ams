<?php
session_start();
include 'connections.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // $department = $_POST['department'];
    $department = "civil";

    $stmt = $mysqli->prepare("SELECT * FROM users WHERE username=? AND password=? AND department=?");
    $stmt->bind_param("sss", $username, $password, $department);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['username'] = $username;
        $_SESSION['department'] = $department;
        header("Location: dashboard.php");
    } else {
        $error = "Invalid username, password, or department.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lab Asset Management - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('https://akm-img-a-in.tosshub.com/indiatoday/images/story/202410/iit-bombay-and-iit-delhi-are-among-the-worlds-top-150-in-the-qs-world-university-ranking-2025-050625583-16x9_1.jpg?VersionId=ZxoHasJDJYvzGGvYoIDNd2hvWCoqviDe&size=690:388'); /* Replace with your image URL */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5); /* Dark overlay for better readability */
            z-index: 1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.15); /* Transparent white background */
            backdrop-filter: blur(10px); /* Blur effect for glass morphism */
            -webkit-backdrop-filter: blur(10px);
            padding: 2.5em;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            position: relative;
            z-index: 2;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        h2 {
            color: #ffffff;
            margin-bottom: 1.5em;
            font-size: 2em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 1.5em;
            text-align: left;
        }

        label {
            font-size: 0.95em;
            color: #ffffff;
            display: block;
            margin-bottom: 0.5em;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        input[type="text"], 
        input[type="password"], 
        select {
            width: 100%;
            padding: 0.8em;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1em;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        input[type="text"]:focus, 
        input[type="password"]:focus, 
        select:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.2);
        }

        select option {
            background: #333;
            color: #ffffff;
        }

        button {
            width: 100%;
            padding: 1em;
            background: rgba(76, 175, 80, 0.8);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            background: rgba(76, 175, 80, 1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .error {
            color: #ff6b6b;
            font-size: 0.9em;
            margin-bottom: 1em;
            background: rgba(255, 0, 0, 0.1);
            padding: 0.5em;
            border-radius: 4px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* Placeholder color */
        ::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Dark mode selection color */
        ::selection {
            background: rgba(76, 175, 80, 0.3);
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required placeholder="Enter your username">
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>

        <!-- <div class="form-group">
            <label>Department:</label>
            <select name="department">
                <option value="cse">CSE</option>
                <option value="civil">Civil</option>
                <option value="mechanical">Mechanical</option>
            </select>
        </div> -->

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>