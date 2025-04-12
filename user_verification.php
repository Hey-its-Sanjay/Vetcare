<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $user_type = isset($_POST['user_type']) ? sanitize_input($_POST['user_type']) : 'patient';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
        $message_type = 'danger';
    } else {
        // Determine which table to check based on user type
        $table = ($user_type === 'doctor') ? 'doctors' : 'patients';
        
        // Check if email exists
        $query = "SELECT id, full_name, email_verified FROM $table WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['email_verified'] == 1) {
                $message = 'Your email is already verified. You can login to your account.';
                $message_type = 'info';
            } else {
                // Generate verification code
                $verification_code = generate_random_string(32);
                
                // Update user with verification code
                $update_query = "UPDATE $table SET verification_code = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param('si', $verification_code, $user['id']);
                
                if ($stmt->execute()) {
                    // Generate link and email content
                    $verification_link = generate_verification_link($user_type, $verification_code);
                    $email_content = create_verification_email($user['full_name'], $verification_link, $user_type === 'doctor');
                    
                    // Send verification email
                    if (send_simple_email($email, "VetCare - Email Verification", $email_content)) {
                        $message = 'Verification email has been sent. Please check your inbox and spam folder.';
                        $message_type = 'success';
                    } else {
                        $message = 'Failed to send verification email. Please try again later.';
                        $message_type = 'danger';
                    }
                } else {
                    $message = 'Error updating verification code. Please try again.';
                    $message_type = 'danger';
                }
            }
        } else {
            $message = 'Email not found. Please make sure you registered with this email address.';
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - VetCare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .radio-group {
            margin: 10px 0;
        }
        .radio-group label {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Your Email Address</h1>
        <p>Enter your email address below to receive a verification link.</p>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Account Type:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="user_type" value="patient" checked> 
                            Patient/Pet Owner
                        </label>
                        <label>
                            <input type="radio" name="user_type" value="doctor"> 
                            Doctor/Veterinarian
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn">Send Verification Email</button>
            </form>
        </div>
        
        <p>
            <a href="login.php">Back to Login</a> | 
            <a href="index.php">Back to Home</a>
        </p>
        
        <div style="margin-top: 30px; font-size: 0.9em; color: #666;">
            <p><strong>Note:</strong> If you're having trouble receiving the verification email:</p>
            <ul>
                <li>Check your spam or junk folder</li>
                <li>Make sure you entered the correct email address</li>
                <li>Add <strong>Shresthasanjay087@gmail.com</strong> to your contacts</li>
                <li>Try again in a few minutes</li>
            </ul>
        </div>
    </div>
</body>
</html> 