<?php
// Email Test Script
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/email_config.php';

// Get recipient email from URL parameter or use a default
$recipient = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : '';

// Start HTML output
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>VetCare Email Test</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .success { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .info { background-color: #e2f3fd; color: #0c5460; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        form { margin-bottom: 20px; }
        input[type='email'] { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>VetCare Email Test Tool</h1>";

// Check if we have a recipient email to test
if (!empty($recipient)) {
    // Validate email format
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='error'>Invalid email address format: $recipient</div>";
    } else {
        echo "<div class='info'>Sending test email to: <strong>$recipient</strong></div>";
        
        // Create test email content
        $subject = 'VetCare Email Test - ' . date('Y-m-d H:i:s');
        $message = "
            <html>
            <head>
                <title>VetCare Email Test</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    .highlight { background-color: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 4px solid #4CAF50; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>VetCare Email Test</h2>
                    <p>This is a test email to verify that your email configuration is working correctly.</p>
                    <div class='highlight'>
                        <p><strong>Good news!</strong> If you received this email, it means your email system is properly configured!</p>
                    </div>
                    <p>Time sent: " . date('Y-m-d H:i:s') . "</p>
                    <p>Regards,<br>The VetCare Team</p>
                </div>
            </body>
            </html>
        ";
        
        // Try to send the email
        $result = send_email($recipient, $subject, $message);
        
        if ($result) {
            echo "<div class='success'>
                <h3>✓ Email Sent Successfully!</h3>
                <p>A test email has been sent to <strong>$recipient</strong>.</p>
                <p>Please check your inbox (and spam folder) to confirm receipt.</p>
            </div>";
        } else {
            echo "<div class='error'>
                <h3>✗ Email Sending Failed</h3>
                <p>There was a problem sending the email. Please check your configuration.</p>
            </div>";
        }
        
        // Show email logs
        $email_logs_dir = __DIR__ . '/email_logs';
        if (file_exists($email_logs_dir)) {
            echo "<h3>Email Logs</h3>";
            
            // Check success log
            $success_log = $email_logs_dir . '/success_log.txt';
            if (file_exists($success_log)) {
                echo "<h4>Success Log (Last 5 entries)</h4>";
                $lines = file($success_log);
                $last_lines = array_slice($lines, -5);
                echo "<pre>" . htmlspecialchars(implode('', $last_lines)) . "</pre>";
            }
            
            // Check error log
            $error_log = $email_logs_dir . '/error_log.txt';
            if (file_exists($error_log)) {
                echo "<h4>Error Log (Last 5 entries)</h4>";
                $lines = file($error_log);
                $last_lines = array_slice($lines, -5);
                echo "<pre>" . htmlspecialchars(implode('', $last_lines)) . "</pre>";
                
                // Check for common errors and provide solutions
                $error_content = file_get_contents($error_log);
                echo "<h4>Troubleshooting Guide</h4>";
                
                if (strpos($error_content, 'authenticate') !== false || 
                    strpos($error_content, 'authentication') !== false) {
                    echo "<div class='error'>
                        <strong>Authentication Error Detected</strong>
                        <p>The system cannot authenticate with Gmail's SMTP server. This is usually due to an invalid app password.</p>
                        <p><strong>Solution:</strong></p>
                        <ol>
                            <li>Go to <a href='https://myaccount.google.com/security' target='_blank'>Google Account Security</a></li>
                            <li>Make sure 2-Step Verification is enabled</li>
                            <li>Go to 'App Passwords' and generate a new password</li>
                            <li>Copy the new password and update it in <code>includes/email_config.php</code></li>
                            <li>Update the line: \$mail->Password = 'new-password-here';</li>
                        </ol>
                    </div>";
                }
                
                if (strpos($error_content, 'connect') !== false || 
                    strpos($error_content, 'connection') !== false) {
                    echo "<div class='error'>
                        <strong>Connection Error Detected</strong>
                        <p>The system cannot connect to Gmail's SMTP server. This could be due to network issues or firewall restrictions.</p>
                        <p><strong>Solution:</strong></p>
                        <ol>
                            <li>Check your internet connection</li>
                            <li>Ensure port 587 is not blocked by your firewall or ISP</li>
                            <li>Try using a different network or server</li>
                        </ol>
                    </div>";
                }
                
                if (strpos($error_content, 'PHPMailer') !== false && 
                    strpos($error_content, 'missing') !== false) {
                    echo "<div class='error'>
                        <strong>PHPMailer Missing</strong>
                        <p>The PHPMailer library is not properly installed.</p>
                        <p><strong>Solution:</strong></p>
                        <ol>
                            <li>Run the <a href='install_phpmailer.php'>PHPMailer Installer</a></li>
                            <li>If that doesn't work, manually download PHPMailer and place it in includes/PHPMailer/</li>
                        </ol>
                    </div>";
                }
            }
        }
    }
}

// Check email configuration
echo "<h3>Email Configuration Check</h3>";
$email_config_file = __DIR__ . '/includes/email_config.php';
if (file_exists($email_config_file)) {
    $email_config_content = file_get_contents($email_config_file);
    
    // Check if the main functions exist
    if (function_exists('send_email_phpmailer')) {
        echo "<div class='success'>✓ PHPMailer email function is available</div>";
    } else {
        echo "<div class='error'>✗ PHPMailer email function is not available</div>";
    }
    
    if (function_exists('send_simple_email')) {
        echo "<div class='success'>✓ Simple mail function is available</div>";
    } else {
        echo "<div class='error'>✗ Simple mail function is not available</div>";
    }
    
    // Check if the Gmail credentials are properly set
    if (strpos($email_config_content, 'Username') !== false && 
        strpos($email_config_content, 'Password') !== false) {
        if (strpos($email_config_content, 'your-16-digit-app-password') !== false ||
            strpos($email_config_content, 'your.email@gmail.com') !== false) {
            echo "<div class='error'>✗ Default Gmail credentials detected. Update with real values.</div>";
        } else {
            echo "<div class='success'>✓ Gmail credentials appear to be configured</div>";
        }
    } else {
        echo "<div class='error'>✗ Gmail credentials not found in configuration</div>";
    }
}

// Check registration files
$reg_files = [
    __DIR__ . '/doctor/register.php',
    __DIR__ . '/patient/register.php'
];

$reg_issues = false;
foreach ($reg_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'email_config.php') === false) {
            $reg_issues = true;
        }
    }
}

if ($reg_issues) {
    echo "<div class='error'>
        <strong>Registration Files Issue</strong>
        <p>One or more registration files may not be including the email configuration correctly.</p>
        <p>Click <a href='check_email_logs.php'>here</a> to run a more detailed check.</p>
    </div>";
}

// Show form to test another email
echo "
        <h3>Test Another Email</h3>
        <form method='get' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>
            <input type='email' name='email' placeholder='Enter email address' required>
            <button type='submit'>Send Test Email</button>
        </form>
        
        <h3>Next Steps</h3>
        <ol>
            <li>Make sure you've set up your Gmail App Password in <code>includes/email_config.php</code></li>
            <li>After confirming emails work, patient and doctor registration emails will be sent automatically</li>
            <li>For more details, see the <a href='EMAIL_SETUP.md'>EMAIL_SETUP.md</a> file</li>
        </ol>
        
        <p><a href='index.php'>&larr; Back to Home</a></p>
    </div>
</body>
</html>";
?> 