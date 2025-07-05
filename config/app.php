<?php
/**
 * Application Configuration for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

return [
    // Application Settings
    'name' => 'NSSF Uganda Dashboard',
    'version' => '1.0.0',
    'environment' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'url' => $_ENV['APP_URL'] ?? 'https://nssf-uganda.com',
    'timezone' => 'Africa/Kampala',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'key' => $_ENV['APP_KEY'] ?? 'base64:' . base64_encode(random_bytes(32)),
    
    // Security Settings
    'cipher' => 'AES-256-CBC',
    'session_lifetime' => 120, // minutes
    'session_secure' => true,
    'session_httponly' => true,
    'csrf_token_name' => '_token',
    'password_salt' => $_ENV['PASSWORD_SALT'] ?? 'nssf_uganda_2024',
    
    // File Upload Settings
    'max_upload_size' => 10485760, // 10MB in bytes
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
    'upload_path' => 'assets/uploads/',
    
    // API Settings
    'api_rate_limit' => 1000, // requests per hour
    'api_version' => 'v1',
    'api_timeout' => 30, // seconds
    
    // Email Settings
    'mail_from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@nssf-uganda.com',
    'mail_from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'NSSF Uganda',
    
    // Pagination
    'pagination_per_page' => 25,
    'pagination_max_per_page' => 100,
    
    // Cache Settings
    'cache_driver' => 'file',
    'cache_prefix' => 'nssf_',
    'cache_lifetime' => 3600, // 1 hour in seconds
    
    // Logging
    'log_channel' => 'single',
    'log_level' => $_ENV['LOG_LEVEL'] ?? 'error',
    'log_max_files' => 5,
    
    // Database Settings
    'db_log_queries' => $_ENV['DB_LOG_QUERIES'] ?? false,
    'db_slow_query_time' => 2.0, // seconds
    
    // NSSF Specific Settings
    'nssf' => [
        'contribution_rate' => 0.15, // 15% total (10% employer + 5% employee)
        'employer_rate' => 0.10,
        'employee_rate' => 0.05,
        'minimum_contribution' => 4000, // UGX
        'maximum_contribution' => 150000, // UGX
        'interest_rate' => 0.12, // 12% per annum
        'retirement_age' => 60,
        'early_retirement_age' => 55,
        'minimum_service_years' => 15,
        'grace_period_days' => 90,
    ],
    
    // Supported Languages
    'supported_locales' => [
        'en' => 'English',
        'lg' => 'Luganda',
        'sw' => 'Swahili',
    ],
    
    // Feature Flags
    'features' => [
        'sms_notifications' => $_ENV['FEATURE_SMS'] ?? true,
        'email_campaigns' => $_ENV['FEATURE_EMAIL_CAMPAIGNS'] ?? true,
        'advanced_analytics' => $_ENV['FEATURE_ANALYTICS'] ?? true,
        'ai_segmentation' => $_ENV['FEATURE_AI_SEGMENTATION'] ?? true,
        'fraud_detection' => $_ENV['FEATURE_FRAUD_DETECTION'] ?? true,
        'mobile_app_api' => $_ENV['FEATURE_MOBILE_API'] ?? true,
    ],
];