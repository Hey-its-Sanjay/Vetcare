<?php
/**
 * PHPMailer Installer Script
 * 
 * This script will download PHPMailer files from GitHub and install them in the correct location.
 * Run this script from your browser or command line to install PHPMailer.
 */

// Determine if we're running in a browser or CLI
$is_browser = php_sapi_name() !== 'cli';

if ($is_browser) {
    // HTML header for browser output
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PHPMailer Installer</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
            .container { max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1 { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .success { color: #155724; }
            .error { color: #721c24; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
            .footer { margin-top: 30px; text-align: center; font-size: 0.9em; color: #6c757d; }
            .next-steps { background-color: #e2f3fd; padding: 15px; border-radius: 5px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>PHPMailer Installer</h1>';
}

// Log function that works in both browser and CLI
function log_message($message, $type = 'info') {
    global $is_browser;
    
    if ($is_browser) {
        $class = ($type == 'success') ? 'success' : (($type == 'error') ? 'error' : '');
        echo "<p" . ($class ? " class=\"$class\"" : "") . ">" . ($type == 'success' ? "✓ " : ($type == 'error' ? "✗ " : "")) . htmlspecialchars($message) . "</p>\n";
    } else {
        echo ($type == 'success' ? "✓ " : ($type == 'error' ? "✗ " : "")) . $message . "\n";
    }
}

log_message("Starting PHPMailer installation...");

// Define the target directory and files
$phpmailer_dir = __DIR__ . '/includes/PHPMailer';
$src_dir = $phpmailer_dir . '/src';

// Create directories if they don't exist
if (!file_exists($phpmailer_dir)) {
    if (mkdir($phpmailer_dir, 0777, true)) {
        log_message("Created directory: $phpmailer_dir", 'success');
    } else {
        log_message("Failed to create directory: $phpmailer_dir", 'error');
        exit(1);
    }
}

if (!file_exists($src_dir)) {
    if (mkdir($src_dir, 0777, true)) {
        log_message("Created directory: $src_dir", 'success');
    } else {
        log_message("Failed to create directory: $src_dir", 'error');
        exit(1);
    }
}

// Define the URLs for PHPMailer files
$files = [
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php'
];

// Download files
$all_success = true;
foreach ($files as $filename => $url) {
    $target_file = $src_dir . '/' . $filename;
    
    // Check if file already exists
    if (file_exists($target_file)) {
        log_message("File $filename already exists, skipping.", 'success');
        continue;
    }
    
    log_message("Downloading $filename...");
    
    // Get file contents
    $file_content = @file_get_contents($url);
    
    if ($file_content === false) {
        log_message("Failed to download $filename", 'error');
        $all_success = false;
        continue;
    }
    
    // Save file
    if (file_put_contents($target_file, $file_content)) {
        log_message("Downloaded $filename successfully", 'success');
    } else {
        log_message("Failed to save $filename", 'error');
        $all_success = false;
    }
}

if ($all_success) {
    log_message("All PHPMailer files were installed successfully!", 'success');
} else {
    log_message("Some files could not be installed. Please check the errors above.", 'error');
}

if ($is_browser) {
    // Next steps for browser users
    echo '<div class="next-steps">
        <h2>Next Steps</h2>
        <ol>
            <li>Configure your Gmail App Password in <code>includes/email_config.php</code></li>
            <li>Test the email system using <a href="test_email.php">test_email.php</a></li>
            <li>For detailed instructions, see <a href="EMAIL_SETUP.md">EMAIL_SETUP.md</a></li>
        </ol>
    </div>';
    
    // Add footer and close HTML tags
    echo '<div class="footer">
            <p><a href="index.php">&larr; Back to Home</a></p>
        </div>
    </div>
    </body>
    </html>';
} else {
    // CLI output
    echo "\nNext steps:\n";
    echo "1. Configure your Gmail App Password in includes/email_config.php\n";
    echo "2. Test the email system using test_email.php\n";
    echo "3. For detailed instructions, see EMAIL_SETUP.md\n";
}
?> 