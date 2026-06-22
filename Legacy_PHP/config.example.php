<?php
// ⚠️ RENAME FILE INI MENJADI config.php DAN ISI DENGAN DATA ANDA!

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'otp_system');

// Gmail Configuration (untuk reset password)
define('GMAIL_USER', 'your-email@gmail.com');
define('GMAIL_APP_PASSWORD', 'your-16-digit-app-password');
define('GMAIL_FROM_NAME', 'OTP System');

// OTP Configuration
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 5);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Database Connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
