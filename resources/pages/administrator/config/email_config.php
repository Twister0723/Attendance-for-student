<?php
// config/email_config.php

// Email Configuration for Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // ← You'll update this later
define('SMTP_PASSWORD', 'your-app-password');    // ← You'll update this later
define('SMTP_FROM_EMAIL', 'noreply@youruniversity.com');
define('SMTP_FROM_NAME', 'University Lecture System');
define('SMTP_SECURE', 'tls');
define('SMTP_DEBUG', 0);
define('EMAIL_CHARSET', 'UTF-8');
?>