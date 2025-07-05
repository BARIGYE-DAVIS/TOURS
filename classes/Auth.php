<?php
/**
 * Authentication Class for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

namespace App;

use App\Database;
use Exception;

class Auth
{
    private $db;
    private $config;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = require_once __DIR__ . '/../config/app.php';
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            $this->startSecureSession();
        }
    }
    
    private function startSecureSession()
    {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    public function login($username, $password, $remember = false)
    {
        try {
            // Find user by username or email
            $user = $this->db->selectOne(
                "SELECT * FROM users WHERE (username = :username OR email = :username) AND status = 'active'",
                ['username' => $username]
            );
            
            if (!$user) {
                throw new Exception("Invalid credentials");
            }
            
            // Verify password
            if (!$this->verifyPassword($password, $user['password'])) {
                // Log failed login attempt
                $this->logLoginAttempt($username, false, $_SERVER['REMOTE_ADDR']);
                throw new Exception("Invalid credentials");
            }
            
            // Check if account is locked
            if ($this->isAccountLocked($user['id'])) {
                throw new Exception("Account is temporarily locked due to multiple failed login attempts");
            }
            
            // Update last login
            $this->db->update('users', [
                'last_login' => date('Y-m-d H:i:s'),
                'login_count' => $user['login_count'] + 1
            ], 'id = :id', ['id' => $user['id']]);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['is_authenticated'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['csrf_token'] = $this->generateCSRFToken();
            
            // Handle "Remember Me" functionality
            if ($remember) {
                $this->setRememberMeCookie($user['id']);
            }
            
            // Log successful login
            $this->logLoginAttempt($username, true, $_SERVER['REMOTE_ADDR']);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function logout()
    {
        // Clear remember me cookie if exists
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/', '', true, true);
            
            // Remove from database
            if (isset($_SESSION['user_id'])) {
                $this->db->delete('user_sessions', 'user_id = :user_id AND type = "remember"', [
                    'user_id' => $_SESSION['user_id']
                ]);
            }
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Start new session for security
        session_start();
        session_regenerate_id(true);
    }
    
    public function isAuthenticated()
    {
        if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated']) {
            return false;
        }
        
        // Check session timeout
        $sessionLifetime = $this->config['session_lifetime'] * 60; // Convert to seconds
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $sessionLifetime) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    public function requireAuth($requiredRole = null)
    {
        if (!$this->isAuthenticated()) {
            header('Location: /views/auth/login.php');
            exit;
        }
        
        if ($requiredRole && !$this->hasRole($requiredRole)) {
            header('HTTP/1.1 403 Forbidden');
            header('Location: /views/errors/403.php');
            exit;
        }
    }
    
    public function hasRole($role)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $userRole = $_SESSION['role'] ?? '';
        
        // Define role hierarchy
        $roleHierarchy = [
            'super_admin' => 5,
            'admin' => 4,
            'manager' => 3,
            'officer' => 2,
            'user' => 1
        ];
        
        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$role] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
    
    public function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->db->selectOne(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        );
    }
    
    public function register($userData)
    {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'first_name', 'last_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new Exception("Field {$field} is required");
                }
            }
            
            // Check if username or email already exists
            $existing = $this->db->selectOne(
                "SELECT id FROM users WHERE username = :username OR email = :email",
                ['username' => $userData['username'], 'email' => $userData['email']]
            );
            
            if ($existing) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashedPassword = $this->hashPassword($userData['password']);
            
            // Prepare user data
            $insertData = [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $hashedPassword,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'role' => $userData['role'] ?? 'user',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'email_verified_at' => null
            ];
            
            // Insert user
            $userId = $this->db->insert('users', $insertData);
            
            return $userId;
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            $user = $this->db->selectOne("SELECT password FROM users WHERE id = :id", ['id' => $userId]);
            
            if (!$user || !$this->verifyPassword($currentPassword, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            $hashedPassword = $this->hashPassword($newPassword);
            
            $this->db->update('users', [
                'password' => $hashedPassword,
                'password_changed_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $userId]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function generateCSRFToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    public function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    private function hashPassword($password)
    {
        return password_hash($password . $this->config['password_salt'], PASSWORD_ARGON2ID);
    }
    
    private function verifyPassword($password, $hash)
    {
        return password_verify($password . $this->config['password_salt'], $hash);
    }
    
    private function setRememberMeCookie($userId)
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        
        // Store in database
        $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'token' => $hashedToken,
            'type' => 'remember',
            'expires_at' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)), // 30 days
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Set cookie
        setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }
    
    private function logLoginAttempt($username, $success, $ipAddress)
    {
        $this->db->insert('login_attempts', [
            'username' => $username,
            'success' => $success ? 1 : 0,
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function isAccountLocked($userId)
    {
        // Check failed attempts in last 15 minutes
        $attempts = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM login_attempts 
             WHERE username = (SELECT username FROM users WHERE id = :user_id) 
             AND success = 0 
             AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            ['user_id' => $userId]
        );
        
        return ($attempts['count'] ?? 0) >= 5; // Lock after 5 failed attempts
    }
}