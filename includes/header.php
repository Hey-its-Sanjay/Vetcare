<?php
require_once 'config.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - VetCare' : 'VetCare - Veterinary Care Platform'; ?></title>
    <link rel="stylesheet" href="/vetcare/assets/css/style.css">
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <div class="logo">
                <a href="/vetcare/index.php">VetCare</a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="/vetcare/index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])): ?>
                        <?php if ($_SESSION['user_type'] === 'admin'): ?>
                            <li><a href="/vetcare/admin/dashboard.php">Dashboard</a></li>
                            <li><a href="/vetcare/admin/doctor-applications.php">Doctor Applications</a></li>
                            <li><a href="/vetcare/admin/logout.php">Logout</a></li>
                        <?php elseif ($_SESSION['user_type'] === 'doctor'): ?>
                            <li><a href="/vetcare/doctor/dashboard.php">Dashboard</a></li>
                            <li><a href="/vetcare/doctor/appointments.php">Appointments</a></li>
                            <li><a href="/vetcare/doctor/profile.php">Profile</a></li>
                            <li><a href="/vetcare/doctor/logout.php">Logout</a></li>
                        <?php elseif ($_SESSION['user_type'] === 'patient'): ?>
                            <li><a href="/vetcare/patient/dashboard.php">Dashboard</a></li>
                            <li><a href="/vetcare/patient/appointments.php">My Appointments</a></li>
                            <li><a href="/vetcare/patient/profile.php">Profile</a></li>
                            <li><a href="/vetcare/patient/logout.php">Logout</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="/vetcare/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?> 