<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// This file can only be accessed by admins
if (!is_logged_in('admin')) {
    $_SESSION['message'] = 'You must be logged in as admin to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('login.php');
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_email = sanitize_input($_POST['test_email']);
    
    if (empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Send a test email
        $subject = 'VetCare - Test Email';
        $message = "
            <html>
            <head>
                <title>Test Email</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>VetCare Email System Test</h2>
                    <p>This is a test email to confirm that your email configuration is working correctly.</p>
                    <p>If you received this email, it means your email system is properly configured.</p>
                    <p>Time sent: " . date('Y-m-d H:i:s') . "</p>
                    <p>Regards,<br>The VetCare System</p>
                </div>
            </body>
            </html>
        ";
        
        if (send_email($test_email, $subject, $message)) {
            $success = true;
            // For development, show link to email logs
            $email_logs_link = '<br><a href="email_viewer.php">View email logs</a>';
        } else {
            $error = 'Failed to send email. Please check your server configuration.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - VetCare</title>
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
        <div style="max-width: 700px; margin: 50px auto;">
            <h2 class="text-center">Email System Test</h2>
            <p class="text-center" style="margin-bottom: 30px;">Use this page to test if your email system is working properly</p>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Test email has been sent to <?php echo htmlspecialchars($test_email); ?>!
                    <?php if (file_exists('email_logs')): ?>
                        <?php echo $email_logs_link; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="form-group">
                            <label for="test_email">Email Address to Test</label>
                            <input type="email" id="test_email" name="test_email" class="form-control" required>
                            <small class="form-text text-muted">Enter the email address where you want to receive the test email</small>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary">Send Test Email</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card" style="margin-top: 30px;">
                <div class="card-body">
                    <h4>Email Configuration Tips</h4>
                    <ul>
                        <li>Make sure your server is configured to send emails (PHP mail() function or SMTP)</li>
                        <li>For local development, you might need to set up a mail server like MailHog or Mailtrap</li>
                        <li>Check the includes/email_config.php file to configure SMTP settings</li>
                        <li>For production, consider using a transactional email service like SendGrid or Mailgun</li>
                    </ul>
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