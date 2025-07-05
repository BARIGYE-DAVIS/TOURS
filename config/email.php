<?php
/**
 * Email Configuration for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

return [
    // Default Mailer
    'default' => $_ENV['MAIL_MAILER'] ?? 'smtp',
    
    // Mailer Configurations
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
            'port' => $_ENV['MAIL_PORT'] ?? 587,
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'username' => $_ENV['MAIL_USERNAME'] ?? '',
            'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'timeout' => null,
            'auth_mode' => null,
        ],
        
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => $_ENV['MAIL_SENDMAIL_PATH'] ?? '/usr/sbin/sendmail -bs -i',
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => $_ENV['MAIL_LOG_CHANNEL'] ?? null,
        ],
    ],
    
    // Global "From" Address
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@nssf-uganda.com',
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'NSSF Uganda',
    ],
    
    // Email Templates
    'templates' => [
        'welcome' => [
            'subject' => 'Welcome to NSSF Uganda',
            'template' => 'emails/welcome.php',
        ],
        'password_reset' => [
            'subject' => 'Reset Your Password',
            'template' => 'emails/password-reset.php',
        ],
        'contribution_reminder' => [
            'subject' => 'Contribution Reminder',
            'template' => 'emails/contribution-reminder.php',
        ],
        'statement_ready' => [
            'subject' => 'Your Statement is Ready',
            'template' => 'emails/statement-ready.php',
        ],
        'claim_approved' => [
            'subject' => 'Claim Approved',
            'template' => 'emails/claim-approved.php',
        ],
        'monthly_newsletter' => [
            'subject' => 'NSSF Monthly Newsletter',
            'template' => 'emails/newsletter.php',
        ],
    ],
    
    // Campaign Settings
    'campaigns' => [
        'batch_size' => 100, // Number of emails to send per batch
        'delay_between_batches' => 5, // Seconds between batches
        'max_retries' => 3,
        'retry_delay' => 300, // 5 minutes in seconds
        'track_opens' => true,
        'track_clicks' => true,
        'unsubscribe_url' => $_ENV['APP_URL'] . '/unsubscribe/{token}',
    ],
    
    // Email Queue Settings
    'queue' => [
        'enabled' => $_ENV['MAIL_QUEUE_ENABLED'] ?? false,
        'connection' => $_ENV['MAIL_QUEUE_CONNECTION'] ?? 'database',
        'queue' => $_ENV['MAIL_QUEUE'] ?? 'emails',
        'timeout' => 60,
        'max_tries' => 3,
    ],
    
    // SMS Integration (for notifications)
    'sms' => [
        'provider' => $_ENV['SMS_PROVIDER'] ?? 'twilio',
        'twilio' => [
            'sid' => $_ENV['TWILIO_SID'] ?? '',
            'token' => $_ENV['TWILIO_TOKEN'] ?? '',
            'from' => $_ENV['TWILIO_FROM'] ?? '',
        ],
        'africas_talking' => [
            'username' => $_ENV['AFRICAS_TALKING_USERNAME'] ?? '',
            'api_key' => $_ENV['AFRICAS_TALKING_API_KEY'] ?? '',
            'from' => $_ENV['AFRICAS_TALKING_FROM'] ?? 'NSSF',
        ],
    ],
    
    // Email Validation
    'validation' => [
        'verify_dns' => true,
        'check_disposable' => true,
        'blocked_domains' => [
            '10minutemail.com',
            'tempmail.org',
            'guerrillamail.com',
        ],
    ],
    
    // Bounce Handling
    'bounce_handling' => [
        'enabled' => true,
        'max_bounces' => 3,
        'soft_bounce_threshold' => 5,
        'webhook_secret' => $_ENV['MAIL_WEBHOOK_SECRET'] ?? '',
    ],
];