<?php
/**
 * Main Index File for NSSF Uganda Dashboard
 * 
 * This file serves as the main entry point for the NSSF Uganda Dashboard application.
 * It handles routing and redirects users to appropriate sections based on authentication status.
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 * @since 2024-01-01
 */

// Start session
session_start();

// Set error reporting for development (should be disabled in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/config/app.php';

use App\Auth;

// Load application configuration
$config = require_once __DIR__ . '/config/app.php';

// Set timezone
date_default_timezone_set($config['timezone']);

// Initialize authentication
$auth = new Auth();

// Check if user is authenticated
if ($auth->isAuthenticated()) {
    // User is logged in, redirect to dashboard
    header('Location: /views/dashboard/');
    exit;
} else {
    // User is not logged in, redirect to login page
    header('Location: /views/auth/login.php');
    exit;
}
?>