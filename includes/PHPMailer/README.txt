PHPMailer Instructions
=====================

To complete the email verification setup, you need to download and install PHPMailer:

1. Download PHPMailer from GitHub: https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip

2. Extract the archive and copy the following files into this directory structure:
   - src/Exception.php -> includes/PHPMailer/src/Exception.php
   - src/PHPMailer.php -> includes/PHPMailer/src/PHPMailer.php
   - src/SMTP.php -> includes/PHPMailer/src/SMTP.php

3. Once PHPMailer is installed, email verification will work properly.

Alternative Method (Using Composer):
------------------------------------
1. Navigate to the project root directory
2. Run: composer require phpmailer/phpmailer
3. Update includes/email_config.php to use the vendor autoloader 