<?php
/**
 * Authentication Controller for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

use App\Auth;

header('Content-Type: application/json');

$auth = new Auth();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            handleLogin($auth);
            break;
            
        case 'logout':
            handleLogout($auth);
            break;
            
        case 'register':
            handleRegister($auth);
            break;
            
        case 'change-password':
            handleChangePassword($auth);
            break;
            
        case 'forgot-password':
            handleForgotPassword($auth);
            break;
            
        case 'reset-password':
            handleResetPassword($auth);
            break;
            
        case 'verify-email':
            handleVerifyEmail($auth);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}

function handleLogin($auth) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }
    
    // Validate CSRF token
    $token = $_POST['_token'] ?? '';
    if (!validateCSRFToken($token)) {
        throw new Exception('Invalid security token');
    }
    
    if ($auth->login($username, $password, $remember)) {
        logActivity("User logged in: {$username}", 'info', $auth->getUserId());
        
        jsonResponse([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/views/dashboard/'
        ]);
    } else {
        throw new Exception('Invalid credentials');
    }
}

function handleLogout($auth) {
    if ($auth->isAuthenticated()) {
        $userId = $auth->getUserId();
        $auth->logout();
        logActivity("User logged out", 'info', $userId);
    }
    
    // Handle both AJAX and regular requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        jsonResponse([
            'success' => true,
            'message' => 'Logout successful',
            'redirect' => '/views/auth/login.php'
        ]);
    } else {
        header('Location: /views/auth/login.php');
        exit;
    }
}

function handleRegister($auth) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Check if user is admin (only admins can register new users)
    if (!$auth->isAuthenticated() || !$auth->hasRole('admin')) {
        throw new Exception('Only administrators can register new users');
    }
    
    $userData = [
        'username' => sanitize($_POST['username'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'role' => sanitize($_POST['role'] ?? 'user')
    ];
    
    // Validate required fields
    $required = ['username', 'email', 'password', 'first_name', 'last_name'];
    foreach ($required as $field) {
        if (empty($userData[$field])) {
            throw new Exception("Field {$field} is required");
        }
    }
    
    // Validate email
    if (!isValidEmail($userData['email'])) {
        throw new Exception('Invalid email address');
    }
    
    // Validate password strength
    if (strlen($userData['password']) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }
    
    // Validate CSRF token
    $token = $_POST['_token'] ?? '';
    if (!validateCSRFToken($token)) {
        throw new Exception('Invalid security token');
    }
    
    $userId = $auth->register($userData);
    
    logActivity("New user registered: {$userData['username']}", 'info', $auth->getUserId());
    
    jsonResponse([
        'success' => true,
        'message' => 'User registered successfully',
        'user_id' => $userId
    ]);
}

function handleChangePassword($auth) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    if (!$auth->isAuthenticated()) {
        throw new Exception('Authentication required');
    }
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        throw new Exception('All password fields are required');
    }
    
    if ($newPassword !== $confirmPassword) {
        throw new Exception('New passwords do not match');
    }
    
    if (strlen($newPassword) < 6) {
        throw new Exception('New password must be at least 6 characters long');
    }
    
    // Validate CSRF token
    $token = $_POST['_token'] ?? '';
    if (!validateCSRFToken($token)) {
        throw new Exception('Invalid security token');
    }
    
    $userId = $auth->getUserId();
    $auth->changePassword($userId, $currentPassword, $newPassword);
    
    logActivity("Password changed", 'info', $userId);
    
    jsonResponse([
        'success' => true,
        'message' => 'Password changed successfully'
    ]);
}

function handleForgotPassword($auth) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        throw new Exception('Email address is required');
    }
    
    if (!isValidEmail($email)) {
        throw new Exception('Invalid email address');
    }
    
    // Validate CSRF token
    $token = $_POST['_token'] ?? '';
    if (!validateCSRFToken($token)) {
        throw new Exception('Invalid security token');
    }
    
    // TODO: Implement password reset logic
    // For now, just log the attempt
    logActivity("Password reset requested for: {$email}", 'info');
    
    // Always return success for security reasons (don't reveal if email exists)
    jsonResponse([
        'success' => true,
        'message' => 'Password reset instructions have been sent to your email if the account exists'
    ]);
}

function handleResetPassword($auth) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $token = $_POST['reset_token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($token) || empty($password) || empty($confirmPassword)) {
        throw new Exception('All fields are required');
    }
    
    if ($password !== $confirmPassword) {
        throw new Exception('Passwords do not match');
    }
    
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }
    
    // TODO: Implement password reset verification and update
    logActivity("Password reset completed", 'info');
    
    jsonResponse([
        'success' => true,
        'message' => 'Password has been reset successfully'
    ]);
}

function handleVerifyEmail($auth) {
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        throw new Exception('Verification token is required');
    }
    
    // TODO: Implement email verification logic
    logActivity("Email verification attempted", 'info');
    
    jsonResponse([
        'success' => true,
        'message' => 'Email verified successfully'
    ]);
}