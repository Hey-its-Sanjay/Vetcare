<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    $_SESSION['message'] = 'You must be logged in as a patient to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('login.php');
}

// Get patient information
$patient_id = $_SESSION['user_id'];
$query = "SELECT * FROM patients WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// Get upcoming appointments
$query = "SELECT a.*, d.full_name AS doctor_name, d.specialization 
          FROM appointments a 
          INNER JOIN doctors d ON a.doctor_id = d.id 
          WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() 
          ORDER BY a.appointment_date, a.appointment_time";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$upcoming_appointments = $stmt->get_result();

// Get past appointments
$query = "SELECT a.*, d.full_name AS doctor_name, d.specialization 
          FROM appointments a 
          INNER JOIN doctors d ON a.doctor_id = d.id 
          WHERE a.patient_id = ? AND a.appointment_date < CURDATE() 
          ORDER BY a.appointment_date DESC, a.appointment_time DESC
          LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$past_appointments = $stmt->get_result();

// Get available doctors
$query = "SELECT * FROM doctors WHERE status = 'approved' ORDER BY rating DESC LIMIT 5";
$available_doctors = $conn->query($query);

$page_title = 'Patient Dashboard';
include '../includes/header.php';
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Patient Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>
    
    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
        <div class="column" style="flex: 3; padding: 0 10px; min-width: 300px;">
            <!-- Upcoming Appointments -->
            <div class="card">
                <div class="card-header">
                    <h3>Upcoming Appointments</h3>
                </div>
                <div class="card-body">
                    <?php if ($upcoming_appointments->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td>
                                            <?php
                                                $status_class = '';
                                                switch ($appointment['status']) {
                                                    case 'pending':
                                                        $status_class = 'warning';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'info';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($appointment['status'])); ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>You have no upcoming appointments.</p>
                        <a href="find-doctors.php" class="btn">Book an Appointment</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Past Appointments -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3>Past Appointments</h3>
                </div>
                <div class="card-body">
                    <?php if ($past_appointments->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($appointment = $past_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td>
                                            <?php
                                                $status_class = '';
                                                switch ($appointment['status']) {
                                                    case 'pending':
                                                        $status_class = 'warning';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'info';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($appointment['status'])); ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="appointments.php" class="btn">View All Appointments</a>
                        </div>
                    <?php else: ?>
                        <p>You have no past appointments.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 250px;">
            <!-- Patient Profile Summary -->
            <div class="card">
                <div class="card-header">
                    <h3>Profile Summary</h3>
                </div>
                <div class="card-body">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="../assets/images/<?php echo $patient['profile_image']; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                        <h3 style="margin-top: 10px;"><?php echo htmlspecialchars($patient['full_name']); ?></h3>
                        <p><?php echo htmlspecialchars($patient['email']); ?></p>
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
                        <li style="margin-bottom: 10px;"><a href="find-doctors.php" class="btn btn-block">Find Doctors</a></li>
                        <li style="margin-bottom: 10px;"><a href="appointments.php" class="btn btn-block">My Appointments</a></li>
                        <li><a href="profile.php" class="btn btn-block">Edit Profile</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Top Rated Doctors -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3>Top Rated Doctors</h3>
                </div>
                <div class="card-body">
                    <?php if ($available_doctors->num_rows > 0): ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php while ($doctor = $available_doctors->fetch_assoc()): ?>
                                <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; align-items: center;">
                                        <img src="../assets/images/<?php echo $doctor['profile_image']; ?>" alt="Doctor" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                        <div>
                                            <h4 style="margin: 0;"><?php echo htmlspecialchars($doctor['full_name']); ?></h4>
                                            <p style="margin: 5px 0;"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                            <div style="color: #ffc107;">
                                                <?php 
                                                    $rating = round($doctor['rating']);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '★';
                                                        } else {
                                                            echo '☆';
                                                        }
                                                    }
                                                ?>
                                                (<?php echo number_format($doctor['rating'], 1); ?>)
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <div style="text-align: center;">
                            <a href="find-doctors.php" class="btn">View All Doctors</a>
                        </div>
                    <?php else: ?>
                        <p>No doctors available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
.status-badge.info {
    background-color: #d1ecf1;
    color: #0c5460;
}
</style>

<?php include '../includes/footer.php'; ?> 