<?php
/**
 * Login Page for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/../../classes/Auth.php';

use App\Auth;

$auth = new Auth();

// Redirect if already authenticated
if ($auth->isAuthenticated()) {
    header('Location: /views/dashboard/');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            throw new Exception('Please enter both username and password');
        }
        
        if ($auth->login($username, $password, $remember)) {
            header('Location: /views/dashboard/');
            exit;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NSSF Uganda Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --nssf-primary: #1e3a8a;
            --nssf-secondary: #3b82f6;
            --nssf-accent: #fbbf24;
        }
        
        body {
            background: linear-gradient(135deg, var(--nssf-primary) 0%, var(--nssf-secondary) 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }
        
        .login-form {
            padding: 60px 40px;
        }
        
        .login-image {
            background: linear-gradient(135deg, var(--nssf-primary) 0%, var(--nssf-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            padding: 60px 40px;
            text-align: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: var(--nssf-primary);
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control:focus {
            border-color: var(--nssf-primary);
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
        }
        
        .btn-primary {
            background-color: var(--nssf-primary);
            border-color: var(--nssf-primary);
            padding: 15px;
            font-weight: 600;
            border-radius: 10px;
        }
        
        .btn-primary:hover {
            background-color: var(--nssf-secondary);
            border-color: var(--nssf-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .form-check-input:checked {
            background-color: var(--nssf-primary);
            border-color: var(--nssf-primary);
        }
        
        .text-primary {
            color: var(--nssf-primary) !important;
        }
        
        .link-primary {
            color: var(--nssf-primary);
            text-decoration: none;
        }
        
        .link-primary:hover {
            color: var(--nssf-secondary);
        }
        
        @media (max-width: 768px) {
            .login-image {
                display: none;
            }
            
            .login-form {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="row g-0">
            <!-- Login Form -->
            <div class="col-md-6">
                <div class="login-form">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-primary">Welcome Back</h2>
                        <p class="text-muted">Sign in to your NSSF Uganda Dashboard</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username or Email" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            <label for="username">
                                <i class="fas fa-user me-2"></i>Username or Email
                            </label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="forgot-password.php" class="link-primary">Forgot password?</a>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="text-muted">
                            Don't have an account? 
                            <a href="register.php" class="link-primary fw-semibold">Contact Administrator</a>
                        </p>
                    </div>
                    
                    <!-- Demo Credentials -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted mb-2">Demo Credentials:</h6>
                        <div class="row">
                            <div class="col-6">
                                <small>
                                    <strong>Admin:</strong><br>
                                    admin / admin123
                                </small>
                            </div>
                            <div class="col-6">
                                <small>
                                    <strong>User:</strong><br>
                                    user / user123
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login Image -->
            <div class="col-md-6">
                <div class="login-image">
                    <div class="logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="fw-bold mb-4">NSSF Uganda</h3>
                    <p class="mb-4 opacity-75">Professional Dashboard for managing social security contributions, claims, and member services efficiently.</p>
                    
                    <div class="row text-center mt-5">
                        <div class="col-4">
                            <div class="mb-2">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <small>Member Management</small>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <small>Analytics & Reports</small>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <i class="fas fa-shield-check fa-2x"></i>
                            </div>
                            <small>Secure & Reliable</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation and enhancement
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please enter both username and password');
                return;
            }
            
            // Show loading state
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            button.disabled = true;
            
            // Re-enable button after 5 seconds if form doesn't submit
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 5000);
        });
        
        // Auto-fill demo credentials
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for demo credentials
            const demoSection = document.querySelector('.bg-light');
            if (demoSection) {
                demoSection.style.cursor = 'pointer';
                demoSection.addEventListener('click', function() {
                    document.getElementById('username').value = 'admin';
                    document.getElementById('password').value = 'admin123';
                });
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>