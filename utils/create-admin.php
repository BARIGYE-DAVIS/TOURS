<?php
/**
 * Create Admin User Utility for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../utils/helpers.php';

use App\Database;
use App\Auth;

echo "NSSF Uganda Dashboard - Create Admin User\n";
echo "=========================================\n\n";

try {
    $db = Database::getInstance();
    $auth = new Auth();
    
    // Check if users table exists, if not create it
    if (!$db->tableExists('users')) {
        echo "Creating users table...\n";
        
        $sql = "
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                role ENUM('super_admin', 'admin', 'manager', 'officer', 'user') DEFAULT 'user',
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                email_verified_at TIMESTAMP NULL,
                password_changed_at TIMESTAMP NULL,
                last_login TIMESTAMP NULL,
                login_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_role (role),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $db->query($sql);
        echo "Users table created successfully.\n\n";
    }
    
    // Check if admin user already exists
    $existingAdmin = $db->selectOne("SELECT id FROM users WHERE role = 'super_admin' LIMIT 1");
    
    if ($existingAdmin) {
        echo "Admin user already exists. Do you want to create another admin? (y/n): ";
        $input = trim(fgets(STDIN));
        if (strtolower($input) !== 'y') {
            echo "Exiting...\n";
            exit(0);
        }
    }
    
    // Collect user information
    echo "Enter admin user details:\n";
    echo "-------------------------\n";
    
    echo "Username: ";
    $username = trim(fgets(STDIN));
    
    echo "Email: ";
    $email = trim(fgets(STDIN));
    
    echo "First Name: ";
    $firstName = trim(fgets(STDIN));
    
    echo "Last Name: ";
    $lastName = trim(fgets(STDIN));
    
    echo "Password: ";
    $password = trim(fgets(STDIN));
    
    echo "Confirm Password: ";
    $confirmPassword = trim(fgets(STDIN));
    
    // Validate input
    if (empty($username) || empty($email) || empty($firstName) || empty($lastName) || empty($password)) {
        throw new Exception("All fields are required");
    }
    
    if (!isValidEmail($email)) {
        throw new Exception("Invalid email address");
    }
    
    if ($password !== $confirmPassword) {
        throw new Exception("Passwords do not match");
    }
    
    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters long");
    }
    
    // Check if username or email already exists
    $existing = $db->selectOne(
        "SELECT id FROM users WHERE username = :username OR email = :email",
        ['username' => $username, 'email' => $email]
    );
    
    if ($existing) {
        throw new Exception("Username or email already exists");
    }
    
    // Create admin user
    $userData = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'role' => 'super_admin'
    ];
    
    $userId = $auth->register($userData);
    
    // Mark email as verified for admin
    $db->update('users', [
        'email_verified_at' => date('Y-m-d H:i:s'),
        'status' => 'active'
    ], 'id = :id', ['id' => $userId]);
    
    echo "\n✓ Admin user created successfully!\n";
    echo "User ID: {$userId}\n";
    echo "Username: {$username}\n";
    echo "Email: {$email}\n";
    echo "Role: super_admin\n\n";
    
    echo "You can now login to the dashboard using these credentials.\n";
    echo "Login URL: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/views/auth/login.php\n\n";
    
    // Create other necessary tables for demo
    echo "Creating additional demo tables...\n";
    
    // Members table
    if (!$db->tableExists('members')) {
        $sql = "
            CREATE TABLE members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_number VARCHAR(20) UNIQUE NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100),
                phone VARCHAR(20),
                national_id VARCHAR(20) UNIQUE,
                date_of_birth DATE,
                gender ENUM('male', 'female', 'other'),
                address TEXT,
                employment_status ENUM('employed', 'self_employed', 'unemployed'),
                employer_id INT,
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_member_number (member_number),
                INDEX idx_email (email),
                INDEX idx_phone (phone),
                INDEX idx_national_id (national_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $db->query($sql);
        echo "Members table created.\n";
    }
    
    // Employers table
    if (!$db->tableExists('employers')) {
        $sql = "
            CREATE TABLE employers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employer_code VARCHAR(20) UNIQUE NOT NULL,
                company_name VARCHAR(100) NOT NULL,
                contact_person VARCHAR(100),
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                industry VARCHAR(50),
                employee_count INT DEFAULT 0,
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_employer_code (employer_code),
                INDEX idx_company_name (company_name),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $db->query($sql);
        echo "Employers table created.\n";
    }
    
    // Contributions table
    if (!$db->tableExists('contributions')) {
        $sql = "
            CREATE TABLE contributions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                employer_id INT NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                contribution_date DATE NOT NULL,
                period_month INT NOT NULL,
                period_year INT NOT NULL,
                employee_contribution DECIMAL(15,2) NOT NULL,
                employer_contribution DECIMAL(15,2) NOT NULL,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_member_id (member_id),
                INDEX idx_employer_id (employer_id),
                INDEX idx_contribution_date (contribution_date),
                INDEX idx_period (period_year, period_month),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $db->query($sql);
        echo "Contributions table created.\n";
    }
    
    // Claims table
    if (!$db->tableExists('claims')) {
        $sql = "
            CREATE TABLE claims (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                claim_type ENUM('retirement', 'early_retirement', 'emigration', 'death', 'invalidity') NOT NULL,
                amount_claimed DECIMAL(15,2),
                status ENUM('pending', 'under_review', 'approved', 'rejected', 'paid') DEFAULT 'pending',
                submission_date DATE NOT NULL,
                documents TEXT,
                remarks TEXT,
                processed_by INT,
                processed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_member_id (member_id),
                INDEX idx_claim_type (claim_type),
                INDEX idx_status (status),
                INDEX idx_submission_date (submission_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $db->query($sql);
        echo "Claims table created.\n";
    }
    
    echo "\n✓ All demo tables created successfully!\n";
    echo "Your NSSF Uganda Dashboard is ready to use.\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}