<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/email_config.php'; // Make sure email configuration is loaded

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['message'] = 'You must be logged in as admin to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php');
}

// Process application approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['doctor_id'])) {
    $doctor_id = (int)$_POST['doctor_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        // Update doctor status
        $query = "UPDATE doctors SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $status, $doctor_id);
        
        if ($stmt->execute()) {
            // Get doctor's email
            $query = "SELECT email, full_name FROM doctors WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $doctor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctor = $result->fetch_assoc();
            
            // Send email notification - try PHPMailer first
            $subject = "VetCare - Doctor Application " . ucfirst($status);
            
            // Create proper base URL that works on both localhost and production
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            
            // Use network IP instead of localhost for mobile compatibility
            $host = $_SERVER['HTTP_HOST'];
            if ($host == 'localhost' || $host == '127.0.0.1' || strpos($host, 'localhost:') !== false) {
                // Try to get the server's IP address on the local network
                $possible_ip = getHostByName(getHostName());
                if ($possible_ip && $possible_ip != '127.0.0.1') {
                    // Extract port if exists
                    $port = '';
                    if (strpos($host, ':') !== false) {
                        $parts = explode(':', $host);
                        if (isset($parts[1])) {
                            $port = ':' . $parts[1];
                        }
                    }
                    $host = $possible_ip . $port;
                }
            }
            
            // Create absolute base URL
            $base_url = $protocol . $host;
            
            // Get the directory path
            $dir_path = dirname(dirname($_SERVER['PHP_SELF']));
            if ($dir_path != '/' && $dir_path != '\\') {
                $base_url .= $dir_path;
            }
            
            // Ensure trailing slash
            if (substr($base_url, -1) != '/') {
                $base_url .= '/';
            }
            
            $login_link = $base_url . "doctor/login.php";
            
            if ($status === 'approved') {
                $message = "
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Application Approved</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                            .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                            .content { padding: 20px; }
                            .button { display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; margin: 20px 0; }
                            .footer { background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
                            @media only screen and (max-width: 480px) {
                                .container { width: 100%; padding: 10px; }
                                .button { display: block; text-align: center; margin: 20px auto; width: 80%; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Application Approved</h2>
                            </div>
                            <div class='content'>
                                <h2>Congratulations, Dr. " . htmlspecialchars($doctor['full_name']) . "!</h2>
                                <p>Your application to become a veterinarian on VetCare has been <strong>approved</strong>.</p>
                                <p>You can now log in to your account and:</p>
                                <ul>
                                    <li>Complete your professional profile</li>
                                    <li>Set your availability schedule</li>
                                    <li>View and manage appointments</li>
                                    <li>Communicate with pet owners</li>
                                </ul>
                                <p style='text-align: center;'><a href='$login_link' class='button'>Login to Your Account</a></p>
                                <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                                <p>Thank you for joining VetCare!</p>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " VetCare. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
            } else {
                $message = "
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>Application Status Update</title>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                            .header { background-color: #f44336; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                            .content { padding: 20px; }
                            .button { display: inline-block; padding: 12px 24px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; margin: 20px 0; }
                            .footer { background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
                            @media only screen and (max-width: 480px) {
                                .container { width: 100%; padding: 10px; }
                                .button { display: block; text-align: center; margin: 20px auto; width: 80%; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Application Status Update</h2>
                            </div>
                            <div class='content'>
                                <h2>Dear Dr. " . htmlspecialchars($doctor['full_name']) . ",</h2>
                                <p>We appreciate your interest in joining VetCare as a professional veterinarian.</p>
                                <p>After careful review of your application, we regret to inform you that we are unable to approve your application at this time.</p>
                                <p>This decision may be due to one or more of the following reasons:</p>
                                <ul>
                                    <li>Incomplete or insufficient professional credentials</li>
                                    <li>Unable to verify license information</li>
                                    <li>Your specialization may not match our current platform needs</li>
                                </ul>
                                <p>If you believe there has been an error in our evaluation or would like to provide additional information, please contact our support team at <a href='mailto:support@vetcare.com'>support@vetcare.com</a>.</p>
                                <p>You may submit a new application after 30 days with updated information.</p>
                                <p>Thank you for your understanding.</p>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " VetCare. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
            }
            
            // Send the email directly with PHPMailer for reliability
            // Log the attempt first
            $email_logs_dir = __DIR__ . '/../email_logs';
            if (!file_exists($email_logs_dir)) {
                mkdir($email_logs_dir, 0777, true);
            }
            
            // Log timestamp of approval/rejection notification
            file_put_contents($email_logs_dir . '/status_change_log.txt', 
                date('Y-m-d H:i:s') . " - Doctor ID: $doctor_id - Status: $status - Email: {$doctor['email']}\n", 
                FILE_APPEND);
            
            // Try to send using the main send_email function that prioritizes PHPMailer
            $email_sent = send_email($doctor['email'], $subject, $message);
            
            if ($email_sent) {
                file_put_contents($email_logs_dir . '/success_log.txt', 
                    date('Y-m-d H:i:s') . " - Status email ($status) sent to: " . $doctor['email'] . "\n", 
                    FILE_APPEND);
            } else {
                file_put_contents($email_logs_dir . '/error_log.txt', 
                    date('Y-m-d H:i:s') . " - Failed to send status email ($status) to: " . $doctor['email'] . "\n", 
                    FILE_APPEND);
                
                // Try to force send with direct email_phpmailer call as backup
                if (function_exists('send_email_phpmailer')) {
                    $backup_sent = send_email_phpmailer($doctor['email'], $subject, $message);
                    if ($backup_sent) {
                        file_put_contents($email_logs_dir . '/success_log.txt', 
                            date('Y-m-d H:i:s') . " - Backup status email ($status) sent to: " . $doctor['email'] . "\n", 
                            FILE_APPEND);
                    }
                }
            }
            
            $_SESSION['message'] = "Doctor application has been " . ucfirst($status) . " successfully!";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Error updating doctor application: " . $conn->error;
            $_SESSION['message_type'] = 'danger';
        }
        
        redirect('doctor-applications.php');
    }
}

// Get all pending doctor applications
$query = "SELECT * FROM doctors WHERE status = 'pending' ORDER BY created_at DESC";
$pending_applications = $conn->query($query);

$page_title = 'Doctor Applications';
include '../includes/header.php';
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Doctor Applications</h1>
        <p>Review and manage pending doctor applications</p>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Pending Applications</h3>
        </div>
        <div class="card-body">
            <?php if ($pending_applications->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Email</th>
                            <th>Specialization</th>
                            <th>Experience</th>
                            <th>License #</th>
                            <th>Application Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($doctor = $pending_applications->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['experience_years']); ?> years</td>
                                <td><?php echo htmlspecialchars($doctor['license_number']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($doctor['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="viewDetails(<?php echo $doctor['id']; ?>)">View Details</button>
                                    
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to approve this application?');">
                                        <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to reject this application?');">
                                        <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending doctor applications at this time.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Doctor Details Modal (simple implementation - in a real application you might want to use a JavaScript modal) -->
<div id="doctorDetailsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 700px; border-radius: 8px;">
        <span class="close" onclick="closeModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <div id="doctorDetailsContent"></div>
    </div>
</div>

<style>
.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    margin: 2px;
}
</style>

<script>
function viewDetails(doctorId) {
    // In a real application, you would fetch the details via AJAX
    // For this example, we'll use a simple approach
    fetch(`get_doctor_details.php?id=${doctorId}`)
        .then(response => response.json())
        .then(doctor => {
            let content = `
                <h2>${doctor.full_name}</h2>
                <div style="display: flex; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <p><strong>Email:</strong> ${doctor.email}</p>
                        <p><strong>Phone:</strong> ${doctor.phone}</p>
                        <p><strong>Specialization:</strong> ${doctor.specialization}</p>
                        <p><strong>Experience:</strong> ${doctor.experience_years} years</p>
                        <p><strong>License Number:</strong> ${doctor.license_number}</p>
                    </div>
                    <div style="flex: 1;">
                        <p><strong>Address:</strong> ${doctor.address}</p>
                        <p><strong>City:</strong> ${doctor.city}</p>
                        <p><strong>State:</strong> ${doctor.state}</p>
                        <p><strong>Zip Code:</strong> ${doctor.zip_code}</p>
                    </div>
                </div>
                <div>
                    <h3>Bio</h3>
                    <p>${doctor.bio || 'No bio provided'}</p>
                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to approve this application?');">
                        <input type="hidden" name="doctor_id" value="${doctor.id}">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success">Approve Application</button>
                    </form>
                    
                    <form method="post" style="display: inline-block; margin-left: 10px;" onsubmit="return confirm('Are you sure you want to reject this application?');">
                        <input type="hidden" name="doctor_id" value="${doctor.id}">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-danger">Reject Application</button>
                    </form>
                </div>
            `;
            document.getElementById('doctorDetailsContent').innerHTML = content;
            document.getElementById('doctorDetailsModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error fetching doctor details');
        });
}

function closeModal() {
    document.getElementById('doctorDetailsModal').style.display = 'none';
}

// Close the modal if the user clicks outside of it
window.onclick = function(event) {
    let modal = document.getElementById('doctorDetailsModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?> 