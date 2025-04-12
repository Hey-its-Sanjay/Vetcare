<?php
require_once 'config.php';
require_once 'simple_mail.php'; // Use the simpler mail system

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in($user_type = '') {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    if (!empty($user_type) && isset($_SESSION['user_type'])) {
        return $_SESSION['user_type'] === $user_type;
    }
    
    return true;
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to send email
function send_email($to, $subject, $message) {
    // Use PHPMailer for sending emails
    if (function_exists('send_email_phpmailer')) {
        return send_email_phpmailer($to, $subject, $message);
    } else {
        // Fallback to simple email if PHPMailer function is not available
        return send_simple_email($to, $subject, $message);
    }
}

// Get user data by ID
function get_user_by_id($user_id, $user_type) {
    global $conn;
    
    $table = '';
    switch($user_type) {
        case 'admin':
            $table = 'admins';
            break;
        case 'doctor':
            $table = 'doctors';
            break;
        case 'patient':
            $table = 'patients';
            break;
        default:
            return false;
    }
    
    $user_id = (int)$user_id;
    $query = "SELECT * FROM $table WHERE id = $user_id LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

// Generate random string for verification codes, etc.
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $random_string;
}
?> 