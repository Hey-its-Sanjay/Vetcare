<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// This is a development-only tool to bypass email verification
// Would be removed in production

$patient_count = 0;
$doctor_count = 0;
$error = '';

// Process verification if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verify patients
    $patient_query = "UPDATE patients SET email_verified = 1 WHERE email_verified = 0";
    if ($conn->query($patient_query)) {
        $patient_count = $conn->affected_rows;
    } else {
        $error = "Error updating patients: " . $conn->error;
    }
    
    // Verify doctors
    $doctor_query = "UPDATE doctors SET email_verified = 1, status = 'approved' WHERE email_verified = 0 OR status = 'pending'";
    if ($conn->query($doctor_query)) {
        $doctor_count = $conn->affected_rows;
    } else {
        $error = "Error updating doctors: " . $conn->error;
    }
}

// Count unverified users
$unverified_patients = 0;
$query = "SELECT COUNT(*) as count FROM patients WHERE email_verified = 0";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $unverified_patients = $row['count'];
}

$unverified_doctors = 0;
$query = "SELECT COUNT(*) as count FROM doctors WHERE email_verified = 0 OR status = 'pending'";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $unverified_doctors = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify All Users - VetCare Development</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .stats {
            margin: 20px 0;
            padding: 15px;
            background-color: #e9f7fe;
            border-radius: 4px;
            border: 1px solid #b8daff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Verify All Users</h1>
        <div>
            <a href="index.php">Back to Home</a>
        </div>
    </div>
    
    <div class="alert alert-warning">
        <strong>Development Tool Only!</strong> This tool automatically verifies all users without requiring email verification.
        This should <strong>not</strong> be used in production.
    </div>
    
    <div class="container">
        <h2>User Verification Status</h2>
        
        <div class="stats">
            <p><strong>Unverified Pet Owners:</strong> <?php echo $unverified_patients; ?></p>
            <p><strong>Unverified/Pending Veterinarians:</strong> <?php echo $unverified_doctors; ?></p>
        </div>
        
        <?php if ($patient_count > 0 || $doctor_count > 0): ?>
            <div class="alert alert-success">
                <p><strong>Success!</strong> The following changes were made:</p>
                <ul>
                    <?php if ($patient_count > 0): ?>
                        <li><?php echo $patient_count; ?> pet owner(s) verified</li>
                    <?php endif; ?>
                    <?php if ($doctor_count > 0): ?>
                        <li><?php echo $doctor_count; ?> veterinarian(s) verified and approved</li>
                    <?php endif; ?>
                </ul>
                <p>These users can now log in without needing email verification.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($unverified_patients > 0 || $unverified_doctors > 0): ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <p>Click the button below to verify all users in the database:</p>
                <button type="submit" class="btn">Verify All Users</button>
            </form>
        <?php else: ?>
            <p>All users are already verified.</p>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <p>After verification, users can log in with their credentials.</p>
            <ul>
                <li><a href="patient/login.php">Pet Owner Login</a></li>
                <li><a href="doctor/login.php">Veterinarian Login</a></li>
            </ul>
        </div>
    </div>
    
    <div style="margin-top: 20px; text-align: center;">
        <p>
            <a href="view_emails.php">View Stored Emails</a> | 
            <a href="user_verification.php">Email Verification</a> | 
            <a href="test_email.php">Email Test</a>
        </p>
    </div>
</body>
</html> 