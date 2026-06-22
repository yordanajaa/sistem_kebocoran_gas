<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'register':
            $name = $input['name'] ?? '';
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            if (empty($name) || empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'All fields required']);
                exit;
            }
            
            // Check if email already exists
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $checkStmt->execute([':email' => $email]);
            
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already registered']);
                exit;
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, is_verified) 
                VALUES (:name, :email, :password, 1)
            ");
            
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful'
            ]);
            break;
            
        case 'login':
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Email and password required']);
                exit;
            }
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
                exit;
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
                exit;
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful'
            ]);
            break;
            
        case 'logout':
            session_destroy();
            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
            break;
            
        case 'reset-password':
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Email and password required']);
                exit;
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Update or insert user with new password
            $stmt = $conn->prepare("
                INSERT INTO users (email, password, is_verified) 
                VALUES (:email, :password, 1)
                ON DUPLICATE KEY UPDATE password = :password, is_verified = 1
            ");
            
            $stmt->execute([
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password reset successful'
            ]);
            break;
            
        case 'check-session':
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'email' => $_SESSION['user_email'] ?? ''
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'logged_in' => false
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
