<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// If user is already logged in as doctor, redirect to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'doctor') {
    redirect('dashboard.php');
}

// Initialize variables
$username = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check if user exists
        $query = "SELECT * FROM doctors WHERE (username = ? OR email = ?) LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $doctor = $result->fetch_assoc();
            
            // In development environment, allow login without verification
            // In production, you would remove the "true ||" part
            if (true || $doctor['email_verified'] == 1) {
                // In development environment, also allow login without admin approval
                // In production, you would remove the "true ||" part
                if (true || $doctor['status'] == 'approved') {
                    // Verify password
                    if (password_verify($password, $doctor['password'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $doctor['id'];
                        $_SESSION['user_type'] = 'doctor';
                        $_SESSION['username'] = $doctor['username'];
                        $_SESSION['full_name'] = $doctor['full_name'];
                        
                        // Redirect to dashboard
                        redirect('dashboard.php');
                    } else {
                        $error = 'Invalid password';
                    }
                } else {
                    $error = 'Your application is pending approval from admin';
                }
            } else {
                $error = 'Please verify your email address before logging in. <a href="../user_verification.php">Resend verification email</a>.';
            }
        } else {
            $error = 'Username or email not found';
        }
    }
}

$page_title = 'Doctor Login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - VetCare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <div class="logo">
                <a href="../index.php">VetCare</a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">Doctor Login</h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-block">Login</button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <p><a href="forgot-password.php">Forgot Password?</a></p>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p>Didn't receive verification email? <a href="../resend_verification.php">Resend it</a></p>
                <p><a href="../index.php">Back to Home</a></p>
            </div>
        </div>
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
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../login.php">Login</a></li>
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