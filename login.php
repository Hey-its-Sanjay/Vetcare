<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'admin':
            redirect('admin/dashboard.php');
            break;
        case 'doctor':
            redirect('doctor/dashboard.php');
            break;
        case 'patient':
            redirect('patient/dashboard.php');
            break;
    }
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <div class="row" style="display: flex; flex-wrap: wrap; justify-content: center;">
        <div style="text-align: center; margin-bottom: 30px; width: 100%;">
            <h2>Choose Login Type</h2>
            <p>Select the appropriate login option based on your account type</p>
        </div>
        
        <div style="width: 300px; margin: 0 15px 30px;">
            <div class="card">
                <div class="card-header">
                    <h3>Patient Login</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <p>Login to your patient account to book appointments with veterinarians</p>
                    <a href="patient/login.php" class="btn btn-block">Login as Patient</a>
                    <div style="margin-top: 15px;">
                        <a href="register.php?type=patient">Don't have an account? Sign up here</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="width: 300px; margin: 0 15px 30px;">
            <div class="card">
                <div class="card-header">
                    <h3>Doctor Login</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <p>Login to your doctor account to manage appointments and your profile</p>
                    <a href="doctor/login.php" class="btn btn-block">Login as Doctor</a>
                    <div style="margin-top: 15px;">
                        <a href="register.php?type=doctor">Don't have an account? Sign up here</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="width: 300px; margin: 0 15px 30px;">
            <div class="card">
                <div class="card-header">
                    <h3>Admin Login</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <p>Login to the admin panel to manage the VetCare platform</p>
                    <a href="admin/index.php" class="btn btn-block">Admin Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 