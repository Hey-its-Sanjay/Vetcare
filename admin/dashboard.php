<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['message'] = 'You must be logged in as admin to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php');
}

// Get count of pending doctor applications
$query = "SELECT COUNT(*) as pending_count FROM doctors WHERE status = 'pending'";
$result = $conn->query($query);
$pending_doctors = $result->fetch_assoc()['pending_count'];

// Get total number of approved doctors
$query = "SELECT COUNT(*) as doctor_count FROM doctors WHERE status = 'approved'";
$result = $conn->query($query);
$total_doctors = $result->fetch_assoc()['doctor_count'];

// Get total number of patients
$query = "SELECT COUNT(*) as patient_count FROM patients";
$result = $conn->query($query);
$total_patients = $result->fetch_assoc()['patient_count'];

// Get total number of appointments
$query = "SELECT COUNT(*) as appointment_count FROM appointments";
$result = $conn->query($query);
$total_appointments = $result->fetch_assoc()['appointment_count'];

// Get latest 5 doctors
$query = "SELECT * FROM doctors ORDER BY created_at DESC LIMIT 5";
$latest_doctors = $conn->query($query);

// Get latest 5 patients
$query = "SELECT * FROM patients ORDER BY created_at DESC LIMIT 5";
$latest_patients = $conn->query($query);

$page_title = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Admin Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>
    
    <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 200px;">
            <div class="card">
                <div class="card-header">
                    <h3>Pending Doctor Applications</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <h2 style="font-size: 3rem;"><?php echo $pending_doctors; ?></h2>
                    <a href="doctor-applications.php" class="btn">View Applications</a>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 200px;">
            <div class="card">
                <div class="card-header">
                    <h3>Total Doctors</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <h2 style="font-size: 3rem;"><?php echo $total_doctors; ?></h2>
                    <a href="doctors.php" class="btn">Manage Doctors</a>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 200px;">
            <div class="card">
                <div class="card-header">
                    <h3>Total Patients</h3>
                </div>
                <div class="card-body" style="text-align: center;">
                    <h2 style="font-size: 3rem;"><?php echo $total_patients; ?></h2>
                    <a href="patients.php" class="btn">Manage Patients</a>
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
                    <a href="appointments.php" class="btn">View Appointments</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row" style="display: flex; flex-wrap: wrap; margin: 20px -10px;">
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 300px;">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Doctor Registrations</h3>
                </div>
                <div class="card-body">
                    <?php if ($latest_doctors->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($doctor = $latest_doctors->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                        <td>
                                            <?php
                                                $status_class = '';
                                                switch ($doctor['status']) {
                                                    case 'pending':
                                                        $status_class = 'warning';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($doctor['status'])); ?></span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($doctor['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No doctor registrations yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="column" style="flex: 1; padding: 0 10px; min-width: 300px;">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Patient Registrations</h3>
                </div>
                <div class="card-body">
                    <?php if ($latest_patients->num_rows > 0): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Verified</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($patient = $latest_patients->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                        <td>
                                            <?php if ($patient['email_verified']): ?>
                                                <span class="status-badge success">Yes</span>
                                            <?php else: ?>
                                                <span class="status-badge warning">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($patient['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No patient registrations yet.</p>
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
</style>

<?php include '../includes/footer.php'; ?> 