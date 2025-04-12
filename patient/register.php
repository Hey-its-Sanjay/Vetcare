<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/email_config.php'; // Include email configuration

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'patient') {
    redirect('dashboard.php');
}

// Initialize variables
$username = '';
$email = '';
$full_name = '';
$phone = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    $full_name = sanitize_input($_POST['full_name']);
    $phone = sanitize_input($_POST['phone']);
    
    // Validate username
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 4) {
        $errors[] = 'Username must be at least 4 characters';
    } else {
        // Check if username already exists
        $query = "SELECT id FROM patients WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Username already exists';
        }
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email already exists
        $query = "SELECT id FROM patients WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Email already registered';
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Validate full name
    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }
    
    // Validate phone
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    // If no errors, process registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate verification code
        $verification_code = generate_random_string(32);
        
        // Insert user into database
        $query = "INSERT INTO patients (username, password, email, full_name, phone, verification_code) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssssss', $username, $hashed_password, $email, $full_name, $phone, $verification_code);
        
        if ($stmt->execute()) {
            $patient_id = $stmt->insert_id;
            
            // Send verification email with improved template
            $subject = "VetCare - Email Verification";
            
            // Create proper base URL that works on both localhost and production
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
            $dir_path = dirname(dirname($_SERVER['PHP_SELF']));
            if ($dir_path != '/' && $dir_path != '\\') {
                $base_url .= $dir_path;
            }
            
            // Ensure trailing slash
            if (substr($base_url, -1) != '/') {
                $base_url .= '/';
            }
            
            $verification_link = $base_url . "patient/verify.php?code=" . urlencode($verification_code);
            
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
                        <h2>Welcome to VetCare, " . htmlspecialchars($full_name) . "!</h2>
                        <p>Thank you for registering. Please verify your email by clicking the button below:</p>
                        <p style='text-align: center;'><a href='$verification_link' class='button'>Verify Email Address</a></p>
                        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                        <p style='word-break: break-all;'>$verification_link</p>
                        <p>If you didn't create this account, you can ignore this email.</p>
                        <p>Regards,<br>The VetCare Team</p>
                    </div>
                </body>
                </html>
            ";
            
            // Use the improved email sending function
            send_email($email, $subject, $message);
            
            // Set session message
            $_SESSION['message'] = 'Registration successful! Please check your email to verify your account. If you don\'t see the email, check your spam folder or <a href="../resend_verification.php">click here</a> to resend it.';
            $_SESSION['message_type'] = 'success';
            
            // Redirect to login page
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed: ' . $conn->error;
        }
    }
}

$page_title = 'Patient Registration';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - VetCare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <div class="logo">
                <a href="../index.php">VetCare</a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div style="max-width: 700px; margin: 50px auto;">
            <h2 class="text-center">Register as Pet Owner</h2>
            <p class="text-center" style="margin-bottom: 30px;">Create your account to book appointments with veterinarians</p>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                            <div style="flex: 1; min-width: 200px; padding: 0 10px;">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                                </div>
                            </div>
                            
                            <div style="flex: 1; min-width: 200px; padding: 0 10px;">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                            <div style="flex: 1; min-width: 200px; padding: 0 10px;">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            
                            <div style="flex: 1; min-width: 200px; padding: 0 10px;">
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                            <div style="flex: 1; min-width: 200px; padding: 0 10px;">
                                <div class="form-group">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($full_name); ?>" required>
                                </div>
                            </div>
                            
                            <div style="flex: 1; min-width: 200px; padding: 0 10px;">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-success btn-block">Register</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <p>Already have an account? <a href="login.php">Login here</a></p>
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
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../login.php">Login</a></li>
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