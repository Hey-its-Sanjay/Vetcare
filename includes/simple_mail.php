<?php
// Simple email sending functionality without requiring PHPMailer

// Function to send emails using PHP's built-in mail function
function send_simple_email($to, $subject, $message, $from_name = 'VetCare', $from_email = 'Shresthasanjay087@gmail.com') {
    // Create email logs directory if it doesn't exist
    $email_logs_dir = __DIR__ . '/../email_logs';
    if (!file_exists($email_logs_dir)) {
        mkdir($email_logs_dir, 0777, true);
    }
    
    // Save email to log file
    $log_file = $email_logs_dir . '/email_' . time() . '_' . md5($to . $subject) . '.html';
    
    // Log email content to file
    $email_content = "To: $to\n";
    $email_content .= "Subject: $subject\n";
    $email_content .= "From: $from_name <$from_email>\n\n";
    $email_content .= $message;
    
    file_put_contents($log_file, $email_content);
    
    // Headers for mail function
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $from_name <$from_email>" . "\r\n";
    $headers .= "Reply-To: $from_email" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Attempt to send the email
    $result = @mail($to, $subject, $message, $headers);
    
    // Log the result
    if ($result) {
        file_put_contents($email_logs_dir . '/success_log.txt', 
            date('Y-m-d H:i:s') . " - Email sent to $to using PHP mail function\n", 
            FILE_APPEND);
    } else {
        file_put_contents($email_logs_dir . '/error_log.txt', 
            date('Y-m-d H:i:s') . " - Failed to send email to $to using PHP mail function\n", 
            FILE_APPEND);
    }
    
    return $result;
}

// Function to generate a verification link
function generate_verification_link($user_type, $verification_code) {
    // Create proper base URL based on server environment
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
    
    // Generate link based on user type
    if ($user_type == 'doctor') {
        return $base_url . "doctor/verify.php?code=" . urlencode($verification_code);
    } else {
        return $base_url . "patient/verify.php?code=" . urlencode($verification_code);
    }
}

// Function to create verification email content
function create_verification_email($full_name, $verification_link, $is_doctor = false) {
    $title = $is_doctor ? "Dr. " . $full_name : $full_name;
    $account_type = $is_doctor ? "doctor " : "";
    $extra_text = $is_doctor ? "<p>After verification, your application will be reviewed by our administrators. You will receive another email when your account has been approved.</p>" : "";
    
    return "
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
                <h2>Hello $title,</h2>
                <p>Your email verification is pending for your VetCare {$account_type}account. Please verify your email by clicking the button below:</p>
                <p style='text-align: center;'><a href='$verification_link' class='button'>Verify Email Address</a></p>
                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                <p style='word-break: break-all;'>$verification_link</p>
                $extra_text
                <p>If you didn't create this account, you can ignore this email.</p>
                <p>Regards,<br>The VetCare Team</p>
            </div>
        </body>
        </html>
    ";
}

// Function to send verification email to a user
function send_verification_email($user_id, $user_type) {
    global $conn;
    
    // Determine which table to use based on user type
    $table = ($user_type === 'doctor') ? 'doctors' : 'patients';
    
    // Get user information
    $query = "SELECT email, full_name, verification_code FROM $table WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Generate new verification code if needed
        $verification_code = $user['verification_code'];
        if (empty($verification_code)) {
            $verification_code = substr(md5(uniqid(rand(), true)), 0, 32);
            
            // Update the verification code
            $update_query = "UPDATE $table SET verification_code = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('si', $verification_code, $user_id);
            $update_stmt->execute();
        }
        
        // Generate verification link
        $verification_link = generate_verification_link($user_type, $verification_code);
        
        // Create email content
        $subject = "VetCare - Email Verification";
        $message = create_verification_email($user['full_name'], $verification_link, $user_type === 'doctor');
        
        // Send the email
        return send_simple_email($user['email'], $subject, $message);
    }
    
    return false;
}
?> 