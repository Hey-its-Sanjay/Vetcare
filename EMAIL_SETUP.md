# Email Verification Setup for VetCare

This guide will help you set up email verification for your VetCare platform to reduce spam registrations.

## Step 1: Install PHPMailer

1. Visit this URL in your browser:
   ```
   http://localhost/vetcare/install_phpmailer.php
   ```

2. This will automatically download and install the required PHPMailer files.

## Step 2: Gmail App Password Configuration

The email system is already configured with an app password for `Shresthasanjay087@gmail.com`. If you need to use a different email or update the password:

1. Go to your Google Account security settings:
   https://myaccount.google.com/security

2. Under "Signing in to Google", make sure "2-Step Verification" is enabled.
   - If not, enable it and follow the steps.

3. After enabling 2-Step Verification, go to "App passwords":
   https://myaccount.google.com/apppasswords

4. Select "Mail" as the app and "Other (Custom name)" as the device.
   - Enter "VetCare" as the name.

5. Click "Generate" and Google will show you a 16-character password.
   - Copy this password.

6. Then update the password in `includes/email_config.php`.

## Step 3: Test the Email System

1. Visit this URL in your browser:
   ```
   http://localhost/vetcare/test_email.php?email=YOUR_EMAIL_ADDRESS
   ```
   (Replace YOUR_EMAIL_ADDRESS with your actual email address)

2. If everything is configured correctly, you should receive a test email.

## Troubleshooting Email Verification Issues

If you're getting the error "Failed to send verification email. Please try again later.":

### 1. Check Email Logs

Run the diagnostic tool to check for common issues:
```
http://localhost/vetcare/check_email_logs.php
```

This will check:
- If the email_logs directory exists and is writable
- If PHPMailer files are installed correctly
- If registration files include the email configuration
- If there are authentication errors in the logs

### 2. Common Authentication Issues

The most common issue is Gmail authentication. If you see errors containing "authenticate" or "authentication":

1. The current app password `raqi qvpx wmke vded` may have expired or been revoked
2. Generate a new app password following Step 2 above
3. Update the password in `includes/email_config.php`

### 3. Email Configuration Issues

If registration files are not including the email configuration:

1. Open `doctor/register.php` and `patient/register.php`
2. Make sure they include this line near the top:
   ```php
   require_once '../includes/email_config.php';
   ```

### 4. PHPMailer Installation Issues

If PHPMailer files are missing:

1. Run the installer again:
   ```
   http://localhost/vetcare/install_phpmailer.php
   ```
2. Check if the PHPMailer directory exists in `includes/PHPMailer/`
3. Make sure the `src` directory contains these files:
   - Exception.php
   - PHPMailer.php
   - SMTP.php

### 5. Email Logs

Review the log files in the `email_logs` directory:
- `success_log.txt`: Contains information about successful email operations
- `error_log.txt`: Contains error messages that can help diagnose issues
- HTML files: Contains copies of actual emails sent

## Security Note

Your Gmail account is being used to send verification emails. Make sure to:

1. Use a dedicated Gmail account for your application
2. Keep your app password secure
3. Never share your app password or include it in public repositories

## Cleanup

After setting up email verification, you can safely delete these files to reduce project size:

1. install_phpmailer.php (after installing)
2. test_email.php (after testing)
3. EMAIL_SETUP.md (after configuration is complete)

## Need More Help?

If you're still experiencing issues after following these steps:

1. Try setting up logging in PHP to capture more detailed errors
2. Check your server's ability to make outbound connections on port 587
3. Consider using a different email provider if Gmail continues to cause issues
4. For local development, you might consider disabling email verification by modifying the login check in `patient/login.php` and `doctor/login.php` 