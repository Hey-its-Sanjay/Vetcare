<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// This can be used for resending verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $user_type = sanitize_input($_POST['user_type']); // 'patient' or 'doctor'
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = 'Please enter a valid email address';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Determine which table to check based on user type
        $table = ($user_type === 'doctor') ? 'doctors' : 'patients';
        
        // Check if user exists
        $query = "SELECT id, full_name, email_verified FROM $table WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate new verification code
            $verification_code = generate_random_string(32);
            
            // Update user with new verification code
            $query = "UPDATE $table SET verification_code = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $verification_code, $user['id']);
            
            if ($stmt->execute()) {
                // Create verification link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                
                // Use network IP instead of localhost for mobile compatibility
                $host = $_SERVER['HTTP_HOST'];
                if ($host == 'localhost' || $host == '127.0.0.1' || strpos($host, 'localhost:') !== false) {
                    // Try to get the server's IP address on the local network
                    $possible_ip = getHostByName(getHostName());
                    if ($possible_ip && $possible_ip != '127.0.0.1') {
                        // Extract port if exists
                        $port = '';
                        if (strpos($host, ':') !== false) {
                            $parts = explode(':', $host);
                            if (isset($parts[1])) {
                                $port = ':' . $parts[1];
                            }
                        }
                        $host = $possible_ip . $port;
                    }
                }
                
                // Create absolute base URL
                $base_url = $protocol . $host;
                
                // Get the directory path
                $dir_path = dirname($_SERVER['PHP_SELF']);
                if ($dir_path != '/' && $dir_path != '\\') {
                    $base_url .= $dir_path;
                }
                
                // Ensure trailing slash
                if (substr($base_url, -1) != '/') {
                    $base_url .= '/';
                }
                
                $verification_link = $base_url . $user_type . "/verify.php?code=" . urlencode($verification_code);
                
                // Email content
                $subject = "VetCare - Email Verification";
                $message = "
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Email Verification</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                            .button { display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; }
                            @media only screen and (max-width: 480px) {
                                .container { width: 100%; padding: 10px; }
                                .button { display: block; text-align: center; margin: 20px auto; width: 80%; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h2>Hello " . htmlspecialchars($user['full_name']) . ",</h2>
                            <p>Thank you for using VetCare. Please verify your email by clicking the button below:</p>
                            <p style='text-align: center;'><a href='$verification_link' class='button'>Verify Email Address</a></p>
                            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                            <p style='word-break: break-all;'>$verification_link</p>
                            <p>If you didn't request this verification, you can ignore this email.</p>
                            <p>Regards,<br>The VetCare Team</p>
                        </div>
                    </body>
                    </html>
                ";
                
                // Send the email
                if (send_email($email, $subject, $message)) {
                    $_SESSION['message'] = 'Verification link has been sent to your email';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Failed to send verification email. Please try again later.';
                    $_SESSION['message_type'] = 'danger';
                }
            } else {
                $_SESSION['message'] = 'Error: ' . $conn->error;
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            // Don't reveal that the email doesn't exist for security
            $_SESSION['message'] = 'If your email is registered, you will receive a verification link shortly';
            $_SESSION['message_type'] = 'info';
        }
    }
}

$page_title = 'Resend Verification Email';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - VetCare</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <div class="logo">
                <a href="index.php">VetCare</a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div style="max-width: 500px; margin: 50px auto;">
            <h2 class="text-center"><?php echo $page_title; ?></h2>
            <p class="text-center" style="margin-bottom: 30px;">Didn't receive your verification email? We'll send it again.</p>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Type</label>
                            <div>
                                <label style="display: inline-block; margin-right: 15px;">
                                    <input type="radio" name="user_type" value="patient" checked> Pet Owner
                                </label>
                                <label style="display: inline-block;">
                                    <input type="radio" name="user_type" value="doctor"> Veterinarian
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary btn-block">Send Verification Link</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <p><a href="login.php">Back to Login</a></p>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <h3>VetCare</h3>
                    <p>Connecting pet owners with qualified veterinarians</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> VetCare. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html> 