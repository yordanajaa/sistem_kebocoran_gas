<?php
// Suppress warnings
error_reporting(E_ERROR | E_PARSE);

require_once 'config.php';

// Check if vendor/autoload.php exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception('PHPMailer not installed. Run: composer require phpmailer/phpmailer');
}

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OTPService {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    // Generate OTP
    public function generateOTP($length = OTP_LENGTH) {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9);
        }
        return $otp;
    }
    
    // Send OTP via Email
    public function sendOTP($email) {
        try {
            // Validasi email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Format email tidak valid'
                ];
            }
            
            // Hapus OTP lama yang belum digunakan
            $this->deleteOldOTP($email);
            
            // Generate OTP baru
            $otp = $this->generateOTP();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
            
            // Simpan OTP ke database
            $stmt = $this->conn->prepare("
                INSERT INTO otp_codes (email, otp_code, expires_at, ip_address, user_agent)
                VALUES (:email, :otp, :expires_at, :ip, :user_agent)
            ");
            
            $stmt->execute([
                ':email' => $email,
                ':otp' => $otp,
                ':expires_at' => $expiresAt,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
            // Kirim email
            $emailSent = $this->sendEmail($email, $otp);
            
            if ($emailSent) {
                // Log success
                $this->logAction($email, 'send_otp', 'success', 'OTP berhasil dikirim');
                
                return [
                    'success' => true,
                    'message' => 'OTP berhasil dikirim ke email Anda',
                    'expires_in' => OTP_EXPIRY_MINUTES . ' menit'
                ];
            } else {
                // Log failure
                $this->logAction($email, 'send_otp', 'failed', 'Gagal mengirim email');
                
                return [
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.'
                ];
            }
            
        } catch (Exception $e) {
            $this->logAction($email, 'send_otp', 'error', $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    // Send Email using PHPMailer
    private function sendEmail($email, $otp) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = GMAIL_USER;
            $mail->Password = GMAIL_APP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom(GMAIL_USER, GMAIL_FROM_NAME);
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Kode OTP Verifikasi Anda';
            $mail->Body = $this->getEmailTemplate($otp);
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email Error: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    // Email Template
    private function getEmailTemplate($otp) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-box { background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; border: 2px dashed #667eea; }
                .otp-code { font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #667eea; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Kode Verifikasi OTP</h1>
                </div>
                <div class="content">
                    <p>Halo,</p>
                    <p>Gunakan kode OTP berikut untuk verifikasi akun Anda:</p>
                    
                    <div class="otp-box">
                        <div class="otp-code">' . $otp . '</div>
                    </div>
                    
                    <p><strong>⏰ Kode ini akan kadaluarsa dalam ' . OTP_EXPIRY_MINUTES . ' menit.</strong></p>
                    
                    <p style="color: #666; font-size: 14px;">
                        Jika Anda tidak meminta kode ini, abaikan email ini.
                    </p>
                </div>
                <div class="footer">
                    <p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
                    <p>&copy; ' . date('Y') . ' OTP System. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
    
    // Verify OTP
    public function verifyOTP($email, $otp) {
        try {
            // Cari OTP yang valid
            $stmt = $this->conn->prepare("
                SELECT * FROM otp_codes 
                WHERE email = :email 
                AND otp_code = :otp 
                AND is_used = 0 
                AND expires_at > NOW()
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            $stmt->execute([
                ':email' => $email,
                ':otp' => $otp
            ]);
            
            $otpData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($otpData) {
                // Mark OTP as used
                $updateStmt = $this->conn->prepare("
                    UPDATE otp_codes 
                    SET is_used = 1, used_at = NOW() 
                    WHERE id = :id
                ");
                $updateStmt->execute([':id' => $otpData['id']]);
                
                // Update user verification status
                $this->updateUserVerification($email);
                
                // Log success
                $this->logAction($email, 'verify_otp', 'success', 'OTP berhasil diverifikasi');
                
                return [
                    'success' => true,
                    'message' => 'OTP valid! Verifikasi berhasil.'
                ];
            } else {
                // Check if OTP exists but expired
                $expiredStmt = $this->conn->prepare("
                    SELECT * FROM otp_codes 
                    WHERE email = :email 
                    AND otp_code = :otp 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ");
                
                $expiredStmt->execute([
                    ':email' => $email,
                    ':otp' => $otp
                ]);
                
                $expiredOTP = $expiredStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($expiredOTP) {
                    if ($expiredOTP['is_used'] == 1) {
                        $message = 'OTP sudah pernah digunakan';
                    } else {
                        $message = 'OTP sudah kadaluarsa';
                    }
                } else {
                    $message = 'OTP tidak valid';
                }
                
                // Log failure
                $this->logAction($email, 'verify_otp', 'failed', $message);
                
                return [
                    'success' => false,
                    'message' => $message
                ];
            }
            
        } catch (Exception $e) {
            $this->logAction($email, 'verify_otp', 'error', $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    // Delete old OTP
    private function deleteOldOTP($email) {
        $stmt = $this->conn->prepare("
            DELETE FROM otp_codes 
            WHERE email = :email 
            AND is_used = 0
        ");
        $stmt->execute([':email' => $email]);
    }
    
    // Update user verification
    private function updateUserVerification($email) {
        $stmt = $this->conn->prepare("
            INSERT INTO users (email, is_verified) 
            VALUES (:email, 1)
            ON DUPLICATE KEY UPDATE is_verified = 1
        ");
        $stmt->execute([':email' => $email]);
    }
    
    // Log action
    private function logAction($email, $action, $status, $message) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO otp_logs (email, action, status, message, ip_address)
                VALUES (:email, :action, :status, :message, :ip)
            ");
            
            $stmt->execute([
                ':email' => $email,
                ':action' => $action,
                ':status' => $status,
                ':message' => $message,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Log Error: " . $e->getMessage());
        }
    }
    
    // Get OTP history
    public function getOTPHistory($email, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT * FROM otp_codes 
            WHERE email = :email 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
