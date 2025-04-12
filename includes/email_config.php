<?php
// Email Configuration for VetCare
// Using file-based email logging with SMTP email sending

// Email configuration using PHPMailer
require_once __DIR__ . '/functions.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Function to send emails with SMTP configuration
function send_email_phpmailer($to, $subject, $message, $from_name = "VetCare", $from_email = "Shresthasanjay087@gmail.com") {
    // Create logs directory if it does not exist
    $log_dir = __DIR__ . "/../email_logs";
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Log email content for reference
    $log_filename = $log_dir . "/email_" . time() . "_" . md5($to . $subject) . ".html";
    
    // Log email content to file
    $email_content = "To: $to\n";
    $email_content .= "Subject: $subject\n";
    $email_content .= "From: $from_name <$from_email>\n\n";
    $email_content .= $message;
    
    file_put_contents($log_filename, $email_content);
    
    // Log attempt
    file_put_contents($log_dir . "/success_log.txt", 
        date("Y-m-d H:i:s") . " - Email logged to file for: $to\n", 
        FILE_APPEND);
    
    // Check if PHPMailer files exist
    $phpmailer_dir = __DIR__ . "/PHPMailer";
    $phpmailer_src = $phpmailer_dir . "/src";
    
    if (!file_exists($phpmailer_src . "/PHPMailer.php")) {
        // Try to download PHPMailer files
        if (downloadPHPMailer()) {
            file_put_contents($log_dir . "/success_log.txt", 
                date("Y-m-d H:i:s") . " - PHPMailer was downloaded automatically\n", 
                FILE_APPEND);
        } else {
            file_put_contents($log_dir . "/error_log.txt", 
                date("Y-m-d H:i:s") . " - Cannot send email: PHPMailer files missing. Run install_phpmailer.php first.\n", 
                FILE_APPEND);
            return true; // Still return true since we logged the email
        }
    }
    
    // PHPMailer is available, try to use it
    try {
        require_once $phpmailer_dir . "/src/Exception.php";
        require_once $phpmailer_dir . "/src/PHPMailer.php";
        require_once $phpmailer_dir . "/src/SMTP.php";
        
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->SMTPDebug = 0;                     // Set to 0 for production, 2 for debugging
        $mail->isSMTP();                          // Send using SMTP
        $mail->Host       = "smtp.gmail.com";     // SMTP server
        $mail->SMTPAuth   = true;                 // Enable SMTP authentication
        
        // ============================================
        // IMPORTANT: EMAIL CONFIGURATION
        // ============================================
        // This Gmail account is already configured for the system
        $mail->Username   = "Shresthasanjay087@gmail.com";
        
        // CRITICAL: This is the App Password that was previously configured
        // DO NOT change this value unless you are creating a new App Password
        $mail->Password   = "dywu yyzk plfb ldbt"; 
        // ============================================
        
        $mail->SMTPSecure = "tls";                // Enable TLS encryption
        $mail->Port       = 587;                  // TCP port to connect to (use 587 for TLS)
        
        // Additional Gmail settings to work around SSL issues
        $mail->SMTPOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        );
        
        // Recipients
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);                      // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);    // Plain text version
        
        $mail->send();
        // Log success
        file_put_contents($log_dir . "/success_log.txt", 
            date("Y-m-d H:i:s") . " - Email sent to $to via PHPMailer SMTP\n", 
            FILE_APPEND);
        return true;
    } catch (Exception $e) {
        // Log error with more details
        $error_message = date("Y-m-d H:i:s") . " - Failed to send email to: $to. Error: " . $mail->ErrorInfo;
        
        // Add helpful troubleshooting tips
        if (strpos($mail->ErrorInfo, "authenticate") !== false) {
            $error_message .= "\nTroubleshooting: The Gmail app password may need to be updated. Current password may have expired.";
            $error_message .= "\n1. Go to https://myaccount.google.com/security";
            $error_message .= "\n2. Go to 'App Passwords'";
            $error_message .= "\n3. Generate a new password and update it in includes/email_config.php";
        }
        
        file_put_contents($log_dir . "/error_log.txt", $error_message . "\n", FILE_APPEND);
        return false;
    }
}

// Function to download PHPMailer files automatically
function downloadPHPMailer() {
    $phpmailer_dir = __DIR__ . "/PHPMailer";
    $src_dir = $phpmailer_dir . "/src";
    
    // Create directories if they do not exist
    if (!file_exists($phpmailer_dir)) {
        if (!mkdir($phpmailer_dir, 0777, true)) {
            return false;
        }
    }
    
    if (!file_exists($src_dir)) {
        if (!mkdir($src_dir, 0777, true)) {
            return false;
        }
    }
    
    // Define the URLs for PHPMailer files
    $files = [
        "Exception.php" => "https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php",
        "PHPMailer.php" => "https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php",
        "SMTP.php" => "https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php"
    ];
    
    // Download files
    foreach ($files as $filename => $url) {
        $target_file = $src_dir . "/" . $filename;
        
        // Skip if file already exists
        if (file_exists($target_file)) {
            continue;
        }
        
        // Get file contents
        $file_content = @file_get_contents($url);
        
        if ($file_content === false) {
            return false;
        }
        
        // Save file
        if (!file_put_contents($target_file, $file_content)) {
            return false;
        }
    }
    
    return true;
}
?>
