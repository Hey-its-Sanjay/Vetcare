<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if doctor ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Doctor ID is required']);
    exit;
}

$doctor_id = (int)$_GET['id'];

// Get doctor details
$query = "SELECT * FROM doctors WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Doctor not found']);
    exit;
}

$doctor = $result->fetch_assoc();

// Return doctor details as JSON
header('Content-Type: application/json');
echo json_encode($doctor);
?> 