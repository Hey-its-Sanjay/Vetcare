<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in as doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    $_SESSION['message'] = 'You must be logged in as a doctor to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('login.php');
}

// Check if appointment ID and action are provided
if (!isset($_GET['id']) || !isset($_GET['action']) || 
    ($_GET['action'] !== 'approve' && $_GET['action'] !== 'reject')) {
    $_SESSION['message'] = 'Invalid request';
    $_SESSION['message_type'] = 'danger';
    redirect('dashboard.php');
}

$appointment_id = (int)$_GET['id'];
$action = $_GET['action'];
$doctor_id = $_SESSION['user_id'];

// Verify the appointment belongs to this doctor and is pending
$query = "SELECT a.*, p.email AS patient_email, p.full_name AS patient_name 
          FROM appointments a 
          INNER JOIN patients p ON a.patient_id = p.id
          WHERE a.id = ? AND a.doctor_id = ? AND a.status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $appointment_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Invalid appointment or you do not have permission to perform this action';
    $_SESSION['message_type'] = 'danger';
    redirect('dashboard.php');
}

$appointment = $result->fetch_assoc();

// Update appointment status
$status = ($action === 'approve') ? 'approved' : 'rejected';
$query = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('si', $status, $appointment_id);

if ($stmt->execute()) {
    // Get doctor information for email
    $query = "SELECT full_name, email FROM doctors WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $doctor_id);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();
    
    // Send email notification to patient
    $subject = "VetCare - Appointment " . ucfirst($status);
    
    if ($status === 'approved') {
        $message = "
            <html>
            <head>
                <title>Appointment Approved</title>
            </head>
            <body>
                <h2>Hello " . htmlspecialchars($appointment['patient_name']) . ",</h2>
                <p>Your appointment request with Dr. " . htmlspecialchars($doctor['full_name']) . " has been approved.</p>
                <p><strong>Appointment Details:</strong></p>
                <p>Date: " . date('F d, Y', strtotime($appointment['appointment_date'])) . "</p>
                <p>Time: " . date('h:i A', strtotime($appointment['appointment_time'])) . "</p>
                <p>If you need to cancel or reschedule, please contact us as soon as possible.</p>
                <p>Thank you for choosing VetCare!</p>
            </body>
            </html>
        ";
    } else {
        $message = "
            <html>
            <head>
                <title>Appointment Rejected</title>
            </head>
            <body>
                <h2>Hello " . htmlspecialchars($appointment['patient_name']) . ",</h2>
                <p>We regret to inform you that your appointment request with Dr. " . htmlspecialchars($doctor['full_name']) . " has been rejected.</p>
                <p>This could be due to scheduling conflicts or other reasons.</p>
                <p>Please feel free to book another appointment at a different time or with another doctor.</p>
                <p>Thank you for your understanding.</p>
            </body>
            </html>
        ";
    }
    
    send_email($appointment['patient_email'], $subject, $message);
    
    $_SESSION['message'] = "Appointment has been " . ucfirst($status) . " successfully!";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = "Error updating appointment: " . $conn->error;
    $_SESSION['message_type'] = 'danger';
}

redirect('dashboard.php');
?> 