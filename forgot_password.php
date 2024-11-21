<?php
include 'connections.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

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

        $mail->setFrom('gulshan.iitb@gmail.com', 'Lab Assets Registration');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body    = "Your password reset OTP is: <b>$otp</b>. This OTP will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

$error_message = '';
$success_message = '';

// Step 1: Username Verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION['reset_otp_sent'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';

    if (empty($username)) {
        $error_message = "Please enter your username.";
    } else {
        // Check if username exists
        $email = $username . '@iitb.ac.in';
        
        $stmt = $mysqli->prepare("SELECT * FROM userdetails WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generate and send OTP
            $otp = generateOTP();
            
            // Store reset details in session
            $_SESSION['reset_username'] = $username;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_otp_time'] = time();
            
            // Send OTP via email
            if (sendOTPEmail($email, $otp)) {
                $_SESSION['reset_otp_sent'] = true;
                $success_message = "OTP has been sent to your email.";
            } else {
                $error_message = "Failed to send OTP. Please contact IT support.";
            }
        } else {
            $error_message = "Username not found.";
        }
        $stmt->close();
    }
}

// Step 2: OTP Verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_otp_sent']) && !isset($_SESSION['reset_otp_verified'])) {
    $user_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    // Check OTP expiration (10 minutes)
    $current_time = time();
    $otp_expiration = 10 * 60; // 10 minutes

    if (empty($user_otp)) {
        $error_message = "Please enter the OTP.";
    } elseif (($current_time - $_SESSION['reset_otp_time']) > $otp_expiration) {
        $error_message = "OTP has expired. Please request a new one.";
        unset($_SESSION['reset_otp_sent']);
        unset($_SESSION['reset_otp']);
    } elseif ($user_otp == $_SESSION['reset_otp']) {
        // OTP Verified - Allow Password Reset
        $_SESSION['reset_otp_verified'] = true;
        $success_message = "OTP verified. You can now reset your password.";
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}

// Step 3: Password Reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_otp_verified'])) {
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please enter both new password and confirm password.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in database
        $username = $_SESSION['reset_username'];
        $stmt = $mysqli->prepare("UPDATE userdetails SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $username);

        if ($stmt->execute()) {
            // Clear all session data
            session_unset();
            session_destroy();

            // Redirect to login page with success message
            header("Location: user_login.php?reset=success");
            exit();
        } else {
            $error_message = "Error updating password. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .success-message {
            background-color: #d1fae5;
            color: #065f46;
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
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Forgot Password</h1>
            <p>
                <?php 
                if (!isset($_SESSION['reset_otp_sent'])) {
                    echo "Enter your username to reset your password";
                } elseif (!isset($_SESSION['reset_otp_verified'])) {
                    echo "Enter the OTP sent to your @iitb.ac.in email";
                } else {
                    echo "Enter a new password for your account";
                }
                ?>
            </p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php if (!isset($_SESSION['reset_otp_sent'])): ?>
                <div class="form-group">
                    <label for="username">Username (without @iitb.ac.in)</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <button type="submit" class="submit-btn">Send OTP</button>

            <?php elseif (!isset($_SESSION['reset_otp_verified'])): ?>
                <div class="form-group">
                    <label for="otp">Enter OTP</label>
                    <input type="text" id="otp" name="otp" required>
                </div>

                <button type="submit" class="submit-btn">Verify OTP</button>

            <?php else: ?>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="submit-btn">Reset Password</button>
            <?php endif; ?>
        </form>

        <div class="login-link">
            Remembered your password? <a href="user_login.php">Login here</a>
        </div>
    </div>
</body>
</html>