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

$page_title = 'Home';
include 'includes/header.php';
?>

<div class="hero-section" style="text-align: center; padding: 80px 0; background-color: #f0f8ff;">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 20px;">Welcome to VetCare</h1>
        <p style="font-size: 1.2rem; margin-bottom: 30px; max-width: 800px; margin-left: auto; margin-right: auto;">
            Connecting pet owners with qualified veterinarians for the best care for your beloved pets.
        </p>
        
        <div class="button-group">
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn btn-success">Sign Up</a>
        </div>
    </div>
</div>

<div class="info-section" style="padding: 60px 0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 40px;">How It Works</h2>
        
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; text-align: center;">
            <div style="flex: 1; min-width: 250px; padding: 20px;">
                <div style="font-size: 3rem; color: #0066cc; margin-bottom: 20px;">1</div>
                <h3>Sign Up</h3>
                <p>Create your account as a pet owner or veterinarian.</p>
            </div>
            
            <div style="flex: 1; min-width: 250px; padding: 20px;">
                <div style="font-size: 3rem; color: #0066cc; margin-bottom: 20px;">2</div>
                <h3>Find or Be Found</h3>
                <p>Pet owners can find vets by location or specialty. Vets can manage their profile.</p>
            </div>
            
            <div style="flex: 1; min-width: 250px; padding: 20px;">
                <div style="font-size: 3rem; color: #0066cc; margin-bottom: 20px;">3</div>
                <h3>Schedule Appointments</h3>
                <p>Book appointments with your preferred veterinarian at your convenience.</p>
            </div>
        </div>
    </div>
</div>

<div style="background-color: #f8f9fa; padding: 60px 0; text-align: center;">
    <div class="container">
        <h2 style="margin-bottom: 30px;">Join VetCare Today</h2>
        <p style="margin-bottom: 30px; max-width: 800px; margin-left: auto; margin-right: auto;">
            Whether you're a pet owner looking for the best care for your furry friends or a veterinarian 
            looking to grow your practice, VetCare provides the platform you need.
        </p>
        
        <div class="button-group">
            <a href="register.php?type=patient" class="btn btn-success">Join as Pet Owner</a>
            <a href="register.php?type=doctor" class="btn">Join as Veterinarian</a>
        </div>
    </div>
</div>

<?php if (file_exists('email_logs')): ?>
    <div style="margin: 40px auto; max-width: 800px; padding: 20px; border: 1px solid #ddd; background-color: #f9f9fa;">
        <h3>Development Tools</h3>
        <p>Tools for setting up and testing your installation:</p>
        <ul>
            <li><a href="install_phpmailer.php">Install PHPMailer</a> - Set up email capabilities</li>
            <li><a href="test_email.php">Test Email System</a> - Check if email configuration works</li>
            <li><a href="EMAIL_SETUP.md">Email Setup Guide</a> - Step-by-step instructions</li>
        </ul>
        <p><strong>Note:</strong> After completing your setup, you can delete these tools and this section for security.</p>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 