# Installation Guide

This guide will help you install and configure the NSSF Uganda Dashboard.

## System Requirements

### Minimum Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum, 1GB recommended
- **Storage**: 1GB free disk space

### Recommended Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache 2.4+ with mod_rewrite or Nginx 1.20+
- **Memory**: 2GB RAM or higher
- **Storage**: 5GB free disk space for data and logs

### PHP Extensions Required
- PDO and PDO_MySQL
- OpenSSL
- GD or Imagick
- cURL
- Mbstring
- XML
- JSON
- Zip

## Installation Steps

### 1. Download and Extract

```bash
# Clone from repository
git clone https://github.com/BARIGYE-DAVIS/TOURS.git nssf-dashboard
cd nssf-dashboard

# Or download and extract ZIP
wget https://github.com/BARIGYE-DAVIS/TOURS/archive/main.zip
unzip main.zip
mv TOURS-main nssf-dashboard
cd nssf-dashboard
```

### 2. Install Dependencies

```bash
# Install Composer if not already installed
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install PHP dependencies
composer install --no-dev --optimize-autoloader
```

### 3. Database Setup

#### Create Database
```sql
CREATE DATABASE nssf_uganda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nssf_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON nssf_uganda.* TO 'nssf_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Configure Database Connection
```bash
cp config/database.example.php config/database.php
```

Edit `config/database.php`:
```php
'mysql' => [
    'host' => 'localhost',
    'database' => 'nssf_uganda',
    'username' => 'nssf_user',
    'password' => 'secure_password',
    // ... keep other settings
]
```

### 4. Application Configuration

```bash
cp config/app.example.php config/app.php
```

Edit `config/app.php`:
```php
'environment' => 'production',
'debug' => false,
'url' => 'https://your-domain.com',
'key' => 'your-32-character-secret-key',
```

### 5. Web Server Configuration

#### Apache Configuration
Create/edit `.htaccess` in the root directory:
```apache
RewriteEngine On

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# Redirect Trailing Slashes If Not A Folder...
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} (.+)/$
RewriteRule ^ %1 [L,R=301]

# Send Requests To Front Controller...
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src 'self'"

# Deny access to sensitive files
<Files "*.php.bak">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>
```

#### Nginx Configuration
Add to your server block:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/nssf-dashboard;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(log|sql|bak)$ {
        deny all;
    }
}
```

### 6. Run Database Migrations

```bash
php migrations/install.php
```

### 7. Create Admin User

```bash
php utils/create-admin.php
```

Follow the prompts to create an administrator account.

### 8. Set Permissions

```bash
# Set proper ownership
chown -R www-data:www-data /path/to/nssf-dashboard

# Set directory permissions
find /path/to/nssf-dashboard -type d -exec chmod 755 {} \;

# Set file permissions
find /path/to/nssf-dashboard -type f -exec chmod 644 {} \;

# Set writable directories
chmod 755 logs/
chmod 755 assets/uploads/
```

### 9. Configure Cron Jobs

Add to crontab for automated tasks:
```bash
crontab -e
```

Add these lines:
```bash
# Daily backup at 2 AM
0 2 * * * php /path/to/nssf-dashboard/cron/backup-database.php

# Send reminders every hour during business hours
0 9-17 * * 1-5 php /path/to/nssf-dashboard/cron/send-reminders.php

# Generate monthly reports on 1st of each month
0 6 1 * * php /path/to/nssf-dashboard/cron/generate-reports.php

# Process analytics daily at midnight
0 0 * * * php /path/to/nssf-dashboard/cron/analytics-processing.php
```

## SSL Certificate Setup

### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d your-domain.com

# Auto-renewal (add to crontab)
0 12 * * * /usr/bin/certbot renew --quiet
```

## Email Configuration

### SMTP Configuration
Edit `config/email.php`:
```php
'smtp' => [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
]
```

### Test Email Configuration
```bash
php utils/test-email.php your-email@example.com
```

## Performance Optimization

### PHP Configuration
Edit `php.ini`:
```ini
memory_limit = 256M
max_execution_time = 300
max_input_vars = 5000
upload_max_filesize = 10M
post_max_size = 10M

# OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### MySQL Configuration
Edit `my.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
max_connections = 200
query_cache_size = 32M
query_cache_type = 1
```

## Security Checklist

- [ ] Change default admin credentials
- [ ] Configure SSL/HTTPS
- [ ] Set proper file permissions
- [ ] Configure firewall rules
- [ ] Enable security headers
- [ ] Configure backup strategy
- [ ] Set up monitoring
- [ ] Update system packages
- [ ] Configure log rotation

## Troubleshooting

### Common Issues

#### Database Connection Error
```bash
# Check MySQL service
sudo systemctl status mysql

# Check connection
mysql -u nssf_user -p nssf_uganda
```

#### Permission Errors
```bash
# Fix ownership
sudo chown -R www-data:www-data /path/to/nssf-dashboard

# Fix permissions
sudo chmod -R 755 /path/to/nssf-dashboard
sudo chmod -R 775 logs/ assets/uploads/
```

#### PHP Errors
```bash
# Check PHP error log
tail -f /var/log/php_errors.log

# Check Apache error log
tail -f /var/log/apache2/error.log
```

### Getting Help

If you encounter issues:
1. Check the error logs
2. Verify system requirements
3. Review configuration files
4. Contact support at support@nssf-uganda.com

## Next Steps

After successful installation:
1. Log in to the dashboard
2. Configure system settings
3. Import initial data
4. Set up user accounts
5. Configure automated tasks
6. Train users

---

**Installation complete! Your NSSF Uganda Dashboard is ready to use.**