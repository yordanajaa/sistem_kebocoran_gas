<?php
// Suppress all errors and warnings from being displayed
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header first
header('Content-Type: application/json');

// Try to load dependencies
try {
    require_once 'OTPService.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading dependencies: ' . $e->getMessage()
    ]);
    exit;
}

try {
    $otpService = new OTPService();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error initializing OTP service: ' . $e->getMessage()
    ]);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get action from URL parameter
$action = $_GET['action'] ?? '';

// Handle POST requests
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'send-otp':
            $email = $input['email'] ?? '';
            
            if (empty($email)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email harus diisi'
                ]);
                exit;
            }
            
            $result = $otpService->sendOTP($email);
            echo json_encode($result);
            break;
            
        case 'verify-otp':
            $email = $input['email'] ?? '';
            $otp = $input['otp'] ?? '';
            
            if (empty($email) || empty($otp)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email dan OTP harus diisi'
                ]);
                exit;
            }
            
            $result = $otpService->verifyOTP($email, $otp);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action tidak valid'
            ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ]);
}
?>
