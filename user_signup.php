<?php
include 'connections.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
if (isset($_GET['check_username'])) {
    $username = $_GET['username'];
    
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM userdetails WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    echo json_encode(['exists' => $count > 0]);
    exit();
}

session_start();

function generateOTP($length = 6) {
    return str_pad(rand(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
}

function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gulshankumar060102@gmail.com';
        $mail->Password   = 'bmmz zjnm zbnk kyrb'; // Use App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('gulshankumar060102@gmail.com', 'Lab Assets Registration');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Lab Assets Registration';
        $mail->Body    = "Your OTP is: <b>$otp</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

$error_message = '';

// Step 1: Initial Registration Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION['otp_verified'])) {
    // Use isset() to prevent undefined index warnings
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';

    // Validate inputs
    if (empty($username) || empty($password) || empty($department)) {
        $error_message = "All fields are required.";
    } else {
        $email = $username . '@iitb.ac.in';

        // Generate and send OTP
        $otp = generateOTP();
        
        // Store registration details in session
        $_SESSION['reg_username'] = $username;
        $_SESSION['reg_name'] = $name;
        $_SESSION['reg_email'] = $email;
        $_SESSION['reg_password'] = password_hash($password, PASSWORD_DEFAULT);
        $_SESSION['reg_department'] = $department;
        $_SESSION['generated_otp'] = $otp;

        // Send OTP via email
        if (sendOTPEmail($email, $otp)) {
            $_SESSION['otp_sent'] = true;
        } else {
            $error_message = "Failed to send OTP. Please contact IT support.";
        }
    }
}

// Step 2: OTP Verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['otp_sent']) && !isset($_SESSION['otp_verified'])) {
    $user_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    if (empty($user_otp)) {
        $error_message = "Please enter the OTP.";
    } elseif ($user_otp == $_SESSION['generated_otp']) {
        // OTP Verified - Complete Registration
        $username = $_SESSION['reg_username'];
        $name = $_SESSION['reg_name'];
        $email = $_SESSION['reg_email'];
        $hashedPassword = $_SESSION['reg_password'];
        $department = $_SESSION['reg_department'];

        // Insert user data into userdetails
        $query = "INSERT INTO userdetails (username, name, password, department) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssss", $username, $name, $hashedPassword, $department);

        if ($stmt->execute()) {
            // Clear session data
            session_unset();
            session_destroy();

            // Redirect to login page
            header("Location: user_login.php");
            exit();
        } else {
            $error_message = "Error: Could not register user.";
        }
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <style>
        /* Previous CSS remains the same */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .username-error {
            color: red;
            font-size: 0.8em;
            margin-top: 5px;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 20px;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
     <script>
    document.addEventListener('DOMContentLoaded', function() {
        const usernameInput = document.getElementById('username');
        const usernameError = document.createElement('div');
        usernameError.classList.add('username-error');
        usernameInput.parentNode.appendChild(usernameError);

        usernameInput.addEventListener('input', function() {
            const username = this.value;
            
            if (username.length > 0) {
                fetch(`user_signup.php?check_username=1&username=${username}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            usernameError.textContent = 'Username already exists. Please choose another.';
                            this.setCustomValidity('Username exists');
                        } else {
                            usernameError.textContent = '';
                            this.setCustomValidity('');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            } else {
                usernameError.textContent = '';
                this.setCustomValidity('');
            }
        });
    });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create Account</h1>
            <p>
                <?php 
                if (!isset($_SESSION['otp_sent'])) {
                    echo "Please fill in your information to sign up";
                } else {
                    echo "Enter the OTP sent to your @iitb.ac.in email";
                }
                ?>
            </p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php if (!isset($_SESSION['otp_sent'])): ?>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username (without @iitb.ac.in)</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" required>
                </div>

                <button type="submit" class="submit-btn">Send OTP</button>
            <?php else: ?>
                <div class="form-group">
                    <label for="otp">Enter OTP</label>
                    <input type="text" id="otp" name="otp" required>
                </div>

                <button type="submit" class="submit-btn">Verify OTP</button>
            <?php endif; ?>
        </form>

        <div class="login-link">
            Already have an account? <a href="user_login.php">Login here</a>
        </div>
    </div>
</body>
</html>