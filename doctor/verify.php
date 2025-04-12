<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if verification code is provided
if (!isset($_GET['code']) || empty($_GET['code'])) {
    $_SESSION['message'] = 'Invalid verification link';
    $_SESSION['message_type'] = 'danger';
    redirect('login.php');
}

// Make sure to decode the verification code
$verification_code = urldecode($_GET['code']);

// Look for user with this verification code
$query = "SELECT id, email, full_name FROM doctors WHERE verification_code = ? AND email_verified = 0 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $verification_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $doctor = $result->fetch_assoc();
    
    // Update user as verified
    $query = "UPDATE doctors SET email_verified = 1, verification_code = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $doctor['id']);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Email verified successfully! Your application is now pending admin approval. You will receive an email when your application has been reviewed.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Email verification failed: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
} else {
    $_SESSION['message'] = 'Invalid or expired verification link';
    $_SESSION['message_type'] = 'danger';
}

redirect('login.php');
?> 