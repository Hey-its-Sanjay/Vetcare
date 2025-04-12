<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// This script should only be accessible to admins
if (!is_logged_in('admin')) {
    $_SESSION['message'] = 'You must be logged in as admin to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('login.php');
}

$sent_count = 0;
$error_count = 0;
$patient_count = 0;
$doctor_count = 0;

// Send verification emails to unverified patients
$query = "SELECT id, email, full_name, verification_code FROM patients WHERE email_verified = 0";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($patient = $result->fetch_assoc()) {
        $patient_count++;
        
        // Generate a new verification code if one doesn't exist
        $verification_code = $patient['verification_code'];
        if (empty($verification_code)) {
            $verification_code = generate_random_string(32);
            
            // Update the patient record with the new code
            $update_query = "UPDATE patients SET verification_code = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('si', $verification_code, $patient['id']);
            $stmt->execute();
        }
        
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
        
        $verification_link = $base_url . "patient/verify.php?code=" . urlencode($verification_code);
        
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
                    <h2>Hello " . htmlspecialchars($patient['full_name']) . ",</h2>
                    <p>Your email verification is pending for your VetCare account. Please verify your email by clicking the button below:</p>
                    <p style='text-align: center;'><a href='$verification_link' class='button'>Verify Email Address</a></p>
                    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                    <p style='word-break: break-all;'>$verification_link</p>
                    <p>If you didn't create this account, you can ignore this email.</p>
                    <p>Regards,<br>The VetCare Team</p>
                </div>
            </body>
            </html>
        ";
        
        // Send the email
        if (send_email($patient['email'], $subject, $message)) {
            $sent_count++;
        } else {
            $error_count++;
        }
    }
}

// Send verification emails to unverified doctors
$query = "SELECT id, email, full_name, verification_code FROM doctors WHERE email_verified = 0";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($doctor = $result->fetch_assoc()) {
        $doctor_count++;
        
        // Generate a new verification code if one doesn't exist
        $verification_code = $doctor['verification_code'];
        if (empty($verification_code)) {
            $verification_code = generate_random_string(32);
            
            // Update the doctor record with the new code
            $update_query = "UPDATE doctors SET verification_code = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('si', $verification_code, $doctor['id']);
            $stmt->execute();
        }
        
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
        
        $verification_link = $base_url . "doctor/verify.php?code=" . urlencode($verification_code);
        
        // Email content
        $subject = "VetCare - Doctor Email Verification";
        $message = "
            <html>
            <head>
                <title>Email Verification</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Hello Dr. " . htmlspecialchars($doctor['full_name']) . ",</h2>
                    <p>Your email verification is pending for your VetCare doctor account. Please verify your email by clicking the button below:</p>
                    <p style='text-align: center;'><a href='$verification_link' class='button'>Verify Email Address</a></p>
                    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                    <p>$verification_link</p>
                    <p>After verification, your application will be reviewed by our administrators. You will receive another email when your account has been approved.</p>
                    <p>If you didn't create this account, you can ignore this email.</p>
                    <p>Regards,<br>The VetCare Team</p>
                </div>
            </body>
            </html>
        ";
        
        // Send the email
        if (send_email($doctor['email'], $subject, $message)) {
            $sent_count++;
        } else {
            $error_count++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Verifications - VetCare</title>
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
                    <li><a href="admin/dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div style="max-width: 800px; margin: 50px auto;">
            <h2 class="text-center">Verification Email Status</h2>
            
            <div class="card">
                <div class="card-body">
                    <h3>Summary</h3>
                    <p>Processed <strong><?php echo $patient_count; ?></strong> unverified patient(s) and <strong><?php echo $doctor_count; ?></strong> unverified doctor(s).</p>
                    <p>Successfully sent <strong><?php echo $sent_count; ?></strong> verification emails.</p>
                    <?php if ($error_count > 0): ?>
                        <p class="text-danger">Failed to send <strong><?php echo $error_count; ?></strong> emails. Check the error logs for details.</p>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <p>All emails have been sent from <strong>Shresthasanjay087@gmail.com</strong></p>
                        <p>Emails are also logged to the <code>email_logs</code> directory.</p>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="admin/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                        <a href="email_viewer.php" class="btn btn-secondary">View Email Logs</a>
                    </div>
                </div>
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
                        <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
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