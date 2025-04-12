<?php
require_once 'config.php';

// Create admins table
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating admins table: " . $conn->error);
}

// Create doctors table
$sql = "CREATE TABLE IF NOT EXISTS doctors (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    experience_years INT(3) NOT NULL,
    bio TEXT,
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    email_verified TINYINT(1) DEFAULT 0,
    verification_code VARCHAR(100),
    rating DECIMAL(3,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating doctors table: " . $conn->error);
}

// Create patients table
$sql = "CREATE TABLE IF NOT EXISTS patients (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    zip_code VARCHAR(20),
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    email_verified TINYINT(1) DEFAULT 0,
    verification_code VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating patients table: " . $conn->error);
}

// Create appointments table
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT(11) UNSIGNED NOT NULL,
    patient_id INT(11) UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating appointments table: " . $conn->error);
}

// Create reviews table
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT(11) UNSIGNED NOT NULL,
    patient_id INT(11) UNSIGNED NOT NULL,
    rating INT(1) NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating reviews table: " . $conn->error);
}

// Create default admin account if it doesn't exist
$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_email = 'admin@vetcare.com';
$admin_name = 'Administrator';

$check_admin = "SELECT id FROM admins WHERE username = '$admin_username' LIMIT 1";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO admins (username, password, email, full_name) 
            VALUES ('$admin_username', '$admin_password', '$admin_email', '$admin_name')";
            
    if (!$conn->query($sql)) {
        die("Error creating default admin account: " . $conn->error);
    }
    
    echo "Default admin account created successfully!<br>";
}

echo "Database setup completed successfully!";
?> 