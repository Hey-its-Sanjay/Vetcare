<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in as doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    $_SESSION['message'] = 'You must be logged in as a doctor to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('login.php');
}

// Check if doctor is approved
$doctor_id = $_SESSION['user_id'];
$query = "SELECT * FROM doctors WHERE id = ? AND status = 'approved'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Your account is not approved yet. Please wait for admin approval.';
    $_SESSION['message_type'] = 'warning';
    
    // Logout
    session_destroy();
    redirect('login.php');
}

// Get doctor information
$doctor = $result->fetch_assoc();

// Get pending appointment requests
$query = "SELECT a.*, p.full_name AS patient_name, p.email AS patient_email, p.phone AS patient_phone 
          FROM appointments a 
          INNER JOIN patients p ON a.patient_id = p.id 
          WHERE a.doctor_id = ? AND a.status = 'pending' 
          ORDER BY a.appointment_date, a.appointment_time";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$pending_appointments = $stmt->get_result();

// Get upcoming appointments
$query = "SELECT a.*, p.full_name AS patient_name, p.email AS patient_email, p.phone AS patient_phone 
          FROM appointments a 
          INNER JOIN patients p ON a.patient_id = p.id 
          WHERE a.doctor_id = ? AND a.status = 'approved' AND a.appointment_date >= CURDATE() 
          ORDER BY a.appointment_date, a.appointment_time";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$upcoming_appointments = $stmt->get_result();

// Get total appointments count
$query = "SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$total_appointments = $stmt->get_result()->fetch_assoc()['total'];

// Get average rating
$query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$avg_rating = $stmt->get_result()->fetch_assoc()['avg_rating'];
$avg_rating = $avg_rating ? number_format($avg_rating, 1) : 'N/A';

$page_title = 'Doctor Dashboard';
include '../includes/header.php';
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Doctor Dashboard</h1>
        <p>Welcome, Dr. <?php echo $_SESSION['full_name']; ?>!</p>
    </div>
    
    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 200px;">
            <div class="card">
                <div class="card-header">
                    <h3>Pending Requests</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <h2 style="font-size: 3rem;"><?php echo $pending_appointments->num_rows; ?></h2>
                    <a href="appointments.php?status=pending" class="btn">View Requests</a>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 200px;">
            <div class="card">
                <div class="card-header">
                    <h3>Upcoming Appointments</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <h2 style="font-size: 3rem;"><?php echo $upcoming_appointments->num_rows; ?></h2>
                    <a href="appointments.php?status=approved" class="btn">View Appointments</a>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 200px;">
            <div class="card">
                <div class="card-header">
                    <h3>Total Appointments</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <h2 style="font-size: 3rem;"><?php echo $total_appointments; ?></h2>
                    <a href="appointments.php" class="btn">View All</a>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 200px;">
            <div class="card">
                <div class="card-header">
                    <h3>Your Rating</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <h2 style="font-size: 3rem;">
                        <?php echo $avg_rating; ?>
                        <?php if ($avg_rating !== 'N/A'): ?>
                            <span style="color: #ffc107; font-size: 0.8em;">★</span>
                        <?php endif; ?>
                    </h2>
                    <a href="reviews.php" class="btn">View Reviews</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row" style="display: flex; flex-wrap: wrap; margin: 20px -10px;">
        <div class="column" style="flex: 3; padding: 0 10px; min-width: 300px;">
            <!-- Pending Appointment Requests -->
            <div class="card">
                <div class="card-header">
                    <h3>Pending Appointment Requests</h3>
                </div>
                <div class="card-body">
                    <?php if ($pending_appointments->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Contact</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($appointment = $pending_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                        <td>
                                            <span title="<?php echo htmlspecialchars($appointment['patient_email']); ?>">
                                                <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td>
                                            <a href="appointment-action.php?id=<?php echo $appointment['id']; ?>&action=approve" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this appointment?');">Approve</a>
                                            <a href="appointment-action.php?id=<?php echo $appointment['id']; ?>&action=reject" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this appointment?');">Reject</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No pending appointment requests at this time.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Upcoming Appointments -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3>Upcoming Appointments</h3>
                </div>
                <div class="card-body">
                    <?php if ($upcoming_appointments->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Contact</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                        <td>
                                            <span title="<?php echo htmlspecialchars($appointment['patient_email']); ?>">
                                                <?php echo htmlspecialchars($appointment['patient_phone']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td>
                                            <span class="status-badge success">Approved</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="appointments.php" class="btn">View All Appointments</a>
                        </div>
                    <?php else: ?>
                        <p>No upcoming appointments at this time.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 250px;">
            <!-- Doctor Profile Summary -->
            <div class="card">
                <div class="card-header">
                    <h3>Profile Summary</h3>
                </div>
                <div class="card-body">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="../assets/images/<?php echo $doctor['profile_image']; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                        <h3 style="margin-top: 10px;">Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                        <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        <p><?php echo htmlspecialchars($doctor['email']); ?></p>
                        <a href="profile.php" class="btn" style="margin-top: 10px;">Edit Profile</a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;"><a href="appointments.php" class="btn btn-block">Manage Appointments</a></li>
                        <li style="margin-bottom: 10px;"><a href="schedule.php" class="btn btn-block">Set Availability</a></li>
                        <li><a href="profile.php" class="btn btn-block">Edit Profile</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    margin: 2px;
}
.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
}
.status-badge.success {
    background-color: #d4edda;
    color: #155724;
}
.status-badge.warning {
    background-color: #fff3cd;
    color: #856404;
}
.status-badge.danger {
    background-color: #f8d7da;
    color: #721c24;
}
</style>

<?php include '../includes/footer.php'; ?> 