<?php
session_start();
require_once 'includes/functions.php';

// Only admins can view emails
if (!is_logged_in('admin')) {
    $_SESSION['message'] = 'You must be logged in as admin to view emails';
    $_SESSION['message_type'] = 'danger';
    redirect('login.php');
}

// Email logs directory
$email_logs_dir = __DIR__ . '/email_logs';

// Get list of email files
$email_files = glob($email_logs_dir . '/email_*.html');
rsort($email_files); // Sort by newest first
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Viewer - VetCare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .email-list {
            list-style: none;
            padding: 0;
        }
        .email-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .email-content {
            margin-top: 10px;
            padding: 15px;
            border: 1px solid #eee;
            background-color: #f9f9f9;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <div class="logo">
                <a href="index.php">VetCare</a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="admin/dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container" style="padding: 30px 0;">
        <h2>Email Logs Viewer</h2>
        <p>This page shows all emails that would have been sent in a development environment.</p>
        
        <?php if (empty($email_files)): ?>
            <div class="alert alert-info">No emails have been sent yet.</div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h3>Recent Emails (<?php echo count($email_files); ?>)</h3>
                    
                    <ul class="email-list">
                        <?php foreach ($email_files as $file): ?>
                            <li class="email-item">
                                <strong>File:</strong> <?php echo basename($file); ?><br>
                                <strong>Date:</strong> <?php echo date('Y-m-d H:i:s', filemtime($file)); ?>
                                
                                <div class="email-content">
                                    <?php echo nl2br(htmlspecialchars(file_get_contents($file))); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <h3>VetCare</h3>
                    <p>Connecting pet owners with qualified veterinarians</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> VetCare. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html> 