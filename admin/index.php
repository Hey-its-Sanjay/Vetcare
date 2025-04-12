<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
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
        $query = "SELECT * FROM admins WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['username'] = $admin['username'];
                $_SESSION['full_name'] = $admin['full_name'];
                
                // Redirect to dashboard
                redirect('dashboard.php');
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Invalid username';
        }
    }
}

$page_title = 'Admin Login';
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
    <div class="container">
        <div class="login-container">
            <h2 class="login-title">Admin Login</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
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
                <a href="../index.php">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html> 