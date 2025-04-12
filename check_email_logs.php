<?php
// Script to check email logs directory and permissions

// Set content type to plain text for better visibility
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Email Logs Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .success { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .info { background-color: #e2f3fd; color: #0c5460; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 4px; font-family: monospace; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Email Logs Directory Check</h1>";

// Define directories to check
$required_dirs = [
    __DIR__ . '/email_logs'
];

// Required files to include
$required_files = [
    __DIR__ . '/includes/email_config.php',
    __DIR__ . '/includes/functions.php',
    __DIR__ . '/includes/simple_mail.php'
];

// Check required directories
echo "<h2>Checking Required Directories</h2>";

foreach ($required_dirs as $dir) {
    echo "<h3>Checking: " . htmlspecialchars($dir) . "</h3>";
    
    if (!file_exists($dir)) {
        // Directory doesn't exist, try to create it
        echo "<div class='error'>Directory does not exist.</div>";
        
        if (mkdir($dir, 0777, true)) {
            echo "<div class='success'>Created directory successfully with permissions 0777.</div>";
        } else {
            echo "<div class='error'>Failed to create directory. Please create it manually and set permissions to 0777.</div>";
            echo "<code>mkdir -p " . htmlspecialchars($dir) . "</code>";
        }
    } else {
        echo "<div class='success'>Directory exists.</div>";
        
        // Check if directory is writable
        if (is_writable($dir)) {
            echo "<div class='success'>Directory is writable.</div>";
        } else {
            echo "<div class='error'>Directory is not writable. Please change permissions:</div>";
            echo "<code>chmod 777 " . htmlspecialchars($dir) . "</code>";
        }
        
        // List contents
        $files = scandir($dir);
        if (count($files) > 2) { // More than . and ..
            echo "<div class='info'>Directory contains " . (count($files) - 2) . " files/directories.</div>";
            echo "<pre>";
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo htmlspecialchars($file) . "\n";
                }
            }
            echo "</pre>";
            
            // Check log files
            $error_log = $dir . '/error_log.txt';
            if (file_exists($error_log)) {
                echo "<h4>Last 5 lines of error_log.txt:</h4>";
                $lines = file($error_log);
                $last_lines = array_slice($lines, -5);
                echo "<pre>" . htmlspecialchars(implode('', $last_lines)) . "</pre>";
            }
            
            $success_log = $dir . '/success_log.txt';
            if (file_exists($success_log)) {
                echo "<h4>Last 5 lines of success_log.txt:</h4>";
                $lines = file($success_log);
                $last_lines = array_slice($lines, -5);
                echo "<pre>" . htmlspecialchars(implode('', $last_lines)) . "</pre>";
            }
        } else {
            echo "<div class='info'>Directory is empty.</div>";
        }
    }
}

// Check required files
echo "<h2>Checking Required Files</h2>";

foreach ($required_files as $file) {
    echo "<h3>Checking: " . htmlspecialchars($file) . "</h3>";
    
    if (file_exists($file)) {
        echo "<div class='success'>File exists.</div>";
        
        // For email_config.php, check if it has the correct password
        if (basename($file) == 'email_config.php') {
            $content = file_get_contents($file);
            if (strpos($content, 'raqi qvpx wmke vded') !== false) {
                echo "<div class='success'>App password is set correctly.</div>";
            } else {
                echo "<div class='error'>App password may not be set correctly in this file.</div>";
            }
        }
        
        // For functions.php, check if it includes email_config.php
        if (basename($file) == 'functions.php') {
            $content = file_get_contents($file);
            if (strpos($content, 'require_once') !== false && strpos($content, 'email_config.php') !== false) {
                echo "<div class='success'>File appears to require email_config.php.</div>";
            } else {
                echo "<div class='error'>File may not be including email_config.php properly.</div>";
            }
        }
    } else {
        echo "<div class='error'>File does not exist: " . htmlspecialchars($file) . "</div>";
    }
}

// Check PHP Mail Configuration
echo "<h2>PHP Mail Configuration</h2>";

$sendmail_path = ini_get('sendmail_path');
echo "<p>sendmail_path: " . ($sendmail_path ? htmlspecialchars($sendmail_path) : "Not set") . "</p>";

$smtp = ini_get('SMTP');
echo "<p>SMTP host: " . ($smtp ? htmlspecialchars($smtp) : "Not set") . "</p>";

$smtp_port = ini_get('smtp_port');
echo "<p>SMTP port: " . ($smtp_port ? htmlspecialchars($smtp_port) : "Not set") . "</p>";

// Check Gmail App Password
echo "<h2>Gmail App Password Check</h2>";

$email_config_file = __DIR__ . '/includes/email_config.php';
if (file_exists($email_config_file)) {
    $email_config_content = file_get_contents($email_config_file);
    
    // Check if the password is set to the default template value
    if (strpos($email_config_content, 'your-16-digit-app-password') !== false) {
        echo "<div class='error'>Gmail App Password is not properly configured. You need to replace the placeholder with a valid App Password.</div>";
        echo "<p>Follow these steps to generate an App Password:</p>";
        echo "<ol>";
        echo "<li>Go to <a href='https://myaccount.google.com/security' target='_blank'>Google Account Security</a></li>";
        echo "<li>Enable 2-Step Verification if not already enabled</li>";
        echo "<li>Go to App Passwords section</li>";
        echo "<li>Create a new App Password for 'Mail' and 'Other (VetCare)'</li>";
        echo "<li>Copy the 16-character password generated</li>";
        echo "<li>Update the password in includes/email_config.php</li>";
        echo "</ol>";
    } 
    elseif (strpos($email_config_content, 'raqi qvpx wmke vded') !== false) {
        // Check for authentication errors in logs 
        $error_log_file = __DIR__ . '/email_logs/error_log.txt';
        $has_auth_errors = false;
        
        if (file_exists($error_log_file)) {
            $error_content = file_get_contents($error_log_file);
            if (strpos($error_content, 'authenticate') !== false || 
                strpos($error_content, 'authentication') !== false) {
                $has_auth_errors = true;
            }
        }
        
        if ($has_auth_errors) {
            echo "<div class='error'>Gmail App Password appears to be invalid or expired. Authentication errors were found in the logs.</div>";
            echo "<p>The current password 'raqi qvpx wmke vded' may have expired or been revoked.</p>";
            echo "<p>Please generate a new App Password following these steps:</p>";
            echo "<ol>";
            echo "<li>Go to <a href='https://myaccount.google.com/security' target='_blank'>Google Account Security</a></li>";
            echo "<li>Go to App Passwords section</li>";
            echo "<li>Create a new App Password for 'Mail' and 'Other (VetCare)'</li>";
            echo "<li>Copy the 16-character password generated</li>";
            echo "<li>Update the password in includes/email_config.php</li>";
            echo "</ol>";
        } else {
            echo "<div class='success'>Gmail App Password is configured (raqi qvpx wmke vded).</div>";
            echo "<p>If you're still experiencing email issues, you may need to:</p>";
            echo "<ul>";
            echo "<li>Check if the Gmail account has 2-Step Verification enabled</li>";
            echo "<li>Verify if the App Password is still valid</li>";
            echo "<li>Check if Less Secure Apps access is enabled (though this is being deprecated by Google)</li>";
            echo "<li>Ensure the Gmail account doesn't have any security restrictions</li>";
            echo "</ul>";
        }
    } else {
        echo "<div class='info'>Gmail App Password appears to be configured with a custom value.</div>";
        echo "<p>If you're experiencing email issues, verify that the password is correct and that the Gmail account is properly set up for SMTP access.</p>";
    }
} else {
    echo "<div class='error'>Email configuration file not found at: " . htmlspecialchars($email_config_file) . "</div>";
}

// Check for PHPMailer installation
echo "<h2>PHPMailer Installation Check</h2>";

$phpmailer_files = [
    __DIR__ . '/includes/PHPMailer/src/Exception.php',
    __DIR__ . '/includes/PHPMailer/src/PHPMailer.php',
    __DIR__ . '/includes/PHPMailer/src/SMTP.php'
];

$all_phpmailer_files_exist = true;
foreach ($phpmailer_files as $file) {
    if (!file_exists($file)) {
        $all_phpmailer_files_exist = false;
        echo "<div class='error'>Missing PHPMailer file: " . htmlspecialchars($file) . "</div>";
    }
}

if ($all_phpmailer_files_exist) {
    echo "<div class='success'>PHPMailer files are correctly installed.</div>";
    echo "<p>If you're still having issues, try running the test email tool again:</p>";
    echo "<p><a href='test_email.php'>Test Email Tool</a></p>";
} else {
    echo "<div class='error'>PHPMailer is not properly installed. Run the installer:</div>";
    echo "<p><a href='install_phpmailer.php'>Install PHPMailer</a></p>";
}

// Fix for doctor/register.php
echo "<h2>Check Registration Files</h2>";

$reg_files = [
    __DIR__ . '/doctor/register.php',
    __DIR__ . '/patient/register.php'
];

foreach ($reg_files as $file) {
    if (file_exists($file)) {
        echo "<h3>Checking: " . htmlspecialchars(basename($file)) . "</h3>";
        
        $content = file_get_contents($file);
        $has_email_config = strpos($content, 'email_config.php') !== false || strpos($content, 'send_email') !== false;
        
        if ($has_email_config) {
            echo "<div class='success'>File appears to be using the email functions correctly.</div>";
        } else {
            echo "<div class='error'>File may not be including email functions properly.</div>";
            echo "<p>To fix, consider adding this line near the top of the file:</p>";
            echo "<pre>require_once '../includes/email_config.php';</pre>";
        }
    } else {
        echo "<div class='error'>Registration file does not exist: " . htmlspecialchars($file) . "</div>";
    }
}

// Add footer
echo "
        <div style='margin-top: 30px; text-align: center;'>
            <p><a href='index.php'>&larr; Back to Home</a> | <a href='test_email.php'>Test Email System</a></p>
        </div>
    </div>
</body>
</html>";
?> 