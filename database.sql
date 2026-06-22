-- ============================================
-- OTP SYSTEM DATABASE
-- ============================================
-- Database untuk sistem OTP (One Time Password)
-- Versi: 1.0
-- Tanggal: 2026-05-12
-- ============================================

-- Hapus database jika sudah ada (HATI-HATI: Ini akan menghapus semua data!)
-- DROP DATABASE IF EXISTS otp_system;

-- Buat database baru
CREATE DATABASE IF NOT EXISTS otp_system 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Gunakan database
USE otp_system;

-- ============================================
-- TABLE: users
-- ============================================
-- Menyimpan data pengguna yang terdaftar
-- ============================================

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) DEFAULT NULL COMMENT 'Hashed password',
    name VARCHAR(100) DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0 COMMENT '0=belum verifikasi, 1=sudah verifikasi',
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_is_verified (is_verified),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: otp_codes
-- ============================================
-- Menyimpan kode OTP yang digenerate
-- ============================================

DROP TABLE IF EXISTS otp_codes;

CREATE TABLE otp_codes (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_used TINYINT(1) DEFAULT 0 COMMENT '0=belum digunakan, 1=sudah digunakan',
    used_at TIMESTAMP NULL DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    
    INDEX idx_email (email),
    INDEX idx_otp_code (otp_code),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_used (is_used),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: otp_logs
-- ============================================
-- Menyimpan log aktivitas OTP
-- ============================================

DROP TABLE IF EXISTS otp_logs;

CREATE TABLE otp_logs (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'send_otp, verify_otp, expired, failed',
    status VARCHAR(20) NOT NULL COMMENT 'success, failed, error',
    message TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_action (action),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA (Optional - Uncomment jika perlu)
-- ============================================

-- INSERT INTO users (email, name, is_verified) VALUES
-- ('test@gmail.com', 'Test User', 0),
-- ('demo@gmail.com', 'Demo User', 0);

-- ============================================
-- VIEWS (Optional - untuk reporting)
-- ============================================

-- View untuk melihat OTP yang masih aktif
CREATE OR REPLACE VIEW active_otps AS
SELECT 
    id,
    email,
    otp_code,
    created_at,
    expires_at,
    TIMESTAMPDIFF(SECOND, NOW(), expires_at) AS seconds_remaining,
    ip_address
FROM otp_codes
WHERE is_used = 0 
  AND expires_at > NOW()
ORDER BY created_at DESC;

-- View untuk statistik OTP per email
CREATE OR REPLACE VIEW otp_statistics AS
SELECT 
    email,
    COUNT(*) AS total_otp_sent,
    SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) AS total_verified,
    SUM(CASE WHEN is_used = 0 AND expires_at < NOW() THEN 1 ELSE 0 END) AS total_expired,
    MAX(created_at) AS last_otp_sent
FROM otp_codes
GROUP BY email
ORDER BY total_otp_sent DESC;

-- ============================================
-- STORED PROCEDURES (Optional)
-- ============================================

-- Procedure untuk cleanup OTP yang sudah expired
DELIMITER $$

DROP PROCEDURE IF EXISTS cleanup_expired_otp$$

CREATE PROCEDURE cleanup_expired_otp()
BEGIN
    DELETE FROM otp_codes 
    WHERE expires_at < NOW() 
      AND is_used = 0;
    
    SELECT ROW_COUNT() AS deleted_rows;
END$$

DELIMITER ;

-- ============================================
-- EVENTS (Optional - Auto cleanup setiap hari)
-- ============================================

-- Aktifkan event scheduler
SET GLOBAL event_scheduler = ON;

-- Event untuk auto cleanup OTP expired setiap hari jam 00:00
DROP EVENT IF EXISTS auto_cleanup_otp;

CREATE EVENT auto_cleanup_otp
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
DO
    CALL cleanup_expired_otp();

-- ============================================
-- SELESAI
-- ============================================

SELECT 'Database otp_system berhasil dibuat!' AS status;
SELECT 'Total tables: 3 (users, otp_codes, otp_logs)' AS info;
SELECT 'Total views: 2 (active_otps, otp_statistics)' AS info;
SELECT 'Total procedures: 1 (cleanup_expired_otp)' AS info;
