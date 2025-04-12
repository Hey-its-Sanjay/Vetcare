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

// Get registration type from URL
$type = isset($_GET['type']) ? $_GET['type'] : '';
if ($type !== 'doctor' && $type !== 'patient') {
    $type = ''; // Reset if invalid type
}

$page_title = 'Registration';
include 'includes/header.php';
?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <?php if (empty($type)): ?>
    <div class="row" style="display: flex; flex-wrap: wrap; justify-content: center;">
        <div style="text-align: center; margin-bottom: 30px; width: 100%;">
            <h2>Sign Up for VetCare</h2>
            <p>Choose your account type to register</p>
        </div>
        
        <div style="width: 300px; margin: 0 15px 30px;">
            <div class="card">
                <div class="card-header">
                    <h3>Pet Owner</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <p>Register as a pet owner to book appointments with veterinarians</p>
                    <a href="register.php?type=patient" class="btn btn-success btn-block">Sign Up as Pet Owner</a>
                </div>
            </div>
        </div>
        
        <div style="width: 300px; margin: 0 15px 30px;">
            <div class="card">
                <div class="card-header">
                    <h3>Veterinarian</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <p>Register as a veterinarian to offer your services on our platform</p>
                    <a href="register.php?type=doctor" class="btn btn-block">Sign Up as Veterinarian</a>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($type === 'patient'): ?>
    <div style="max-width: 600px; margin: 0 auto;">
        <h2 class="text-center">Register as Pet Owner</h2>
        <p class="text-center mb-4">Create your account to book appointments with veterinarians</p>
        
        <div class="card">
            <div class="card-body">
                <p>Please complete your registration by clicking the button below:</p>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="patient/register.php" class="btn btn-success">Continue to Registration</a>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Already have an account? <a href="patient/login.php">Login here</a></p>
        </div>
    </div>
    <?php else: ?>
    <div style="max-width: 600px; margin: 0 auto;">
        <h2 class="text-center">Register as Veterinarian</h2>
        <p class="text-center mb-4">Create your account to offer your veterinary services on our platform</p>
        
        <div class="card">
            <div class="card-body">
                <p>Please complete your registration by clicking the button below:</p>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="doctor/register.php" class="btn btn-success">Continue to Registration</a>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                    <p><strong>Note:</strong> After registration, your application will be reviewed by our admin team. 
                    Once approved, you'll receive an email notification and can start using your account.</p>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <p>Already have an account? <a href="doctor/login.php">Login here</a></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 