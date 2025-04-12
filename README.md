# VetCare - Veterinary Care Platform

A web-based platform connecting pet owners with qualified veterinarians.

## Features

- **Admin Dashboard** - Manage doctors, patients, and appointments
- **Doctor Registration** - Application-based registration with admin approval
- **Patient Registration** - Easy patient registration with email verification
- **Appointment Booking** - Patients can book appointments with doctors
- **Appointment Management** - Doctors can approve or reject appointment requests
- **Email Notifications** - Email notifications for various events

## Setup Instructions

1. **Prerequisites**
   - PHP 7.0 or higher
   - MySQL 5.7 or higher
   - Apache/Nginx web server
   - XAMPP/WAMP/MAMP (recommended for local development)

2. **Installation**
   - Clone or download this repository to your web server directory (e.g., `htdocs` for XAMPP)
   - Make sure the directory is named `vetcare` or update all references accordingly
   - Create a MySQL database named `vetcare` (or update the database name in `includes/config.php`)
   - Set up your database credentials in `includes/config.php` (default is username: `root`, password: ``)
   - Run the setup script by visiting: `http://localhost/vetcare/setup.php`
   - After setup is complete, you can access the site at: `http://localhost/vetcare/`

3. **Default Admin Account**
   - Username: `admin`
   - Password: `admin123`
   - Login at: `http://localhost/vetcare/admin/`

## Project Structure

- `admin/` - Admin section files
- `doctor/` - Doctor section files
- `patient/` - Patient section files
- `includes/` - Shared PHP files, configuration
- `assets/` - CSS, JavaScript, and image files

## Email Configuration

The platform uses PHP's `mail()` function for sending emails. In a production environment, you may want to use a proper mail sending library like PHPMailer.

To enable email notifications:
- Configure your web server's mail settings
- For local testing, you can use tools like MailHog or configure a local SMTP server

## Default User

After setting up, you can:
1. Log in as admin (admin/admin123)
2. Create and approve doctor accounts
3. Register as a patient
4. Book appointments

## Future Enhancements

- Video calling feature for remote consultations
- Payment integration
- Mobile app for patients
- Medicine prescriptions and delivery

## License

This project is open-source and available for educational and commercial use. 