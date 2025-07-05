# NSSF Uganda Dashboard

Professional dashboard system for managing NSSF Uganda's social security contributions, claims, and member services efficiently.

## Features

- **Member Management**: Complete member lifecycle management with registration, updates, and status tracking
- **Employer Management**: Comprehensive employer database with contribution tracking
- **Contribution Processing**: Automated contribution processing with validation and reporting
- **Claims Management**: Streamlined claim processing workflow with approval management
- **Advanced Analytics**: AI-powered member segmentation and behavioral analytics
- **Email Campaigns**: Targeted email campaigns with template management
- **Reporting System**: Dynamic report generation with PDF/Excel export capabilities
- **Security Features**: Role-based access control, CSRF protection, and audit trails

## Technology Stack

### Frontend
- **HTML5**: Semantic markup structure
- **CSS3**: Custom styling with modern design
- **Bootstrap 5**: Responsive framework
- **JavaScript (ES6+)**: Interactive functionality
- **jQuery**: DOM manipulation and AJAX
- **Chart.js**: Data visualization and charts
- **DataTables**: Advanced table functionality

### Backend
- **PHP 8+**: Core backend language
- **MySQL 8+**: Database management
- **PDO**: Database abstraction layer
- **PHPMailer**: Email functionality
- **TCPDF**: PDF generation
- **PhpSpreadsheet**: Excel file handling

## Installation

### Requirements
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (for dependency management)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/BARIGYE-DAVIS/TOURS.git
   cd TOURS
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   - Create a MySQL database for the application
   - Update database credentials in `config/database.php`

4. **Configure application**
   - Copy `config/app.example.php` to `config/app.php`
   - Update configuration settings as needed

5. **Set up database tables**
   ```bash
   php migrations/install.php
   ```

6. **Configure web server**
   - Set document root to the project directory
   - Ensure mod_rewrite is enabled (for Apache)

7. **Create admin user**
   ```bash
   php utils/create-admin.php
   ```

## Configuration

### Database Configuration
Edit `config/database.php` to set your database credentials:

```php
'mysql' => [
    'host' => 'localhost',
    'database' => 'nssf_uganda',
    'username' => 'your_username',
    'password' => 'your_password',
    // ... other settings
]
```

### Email Configuration
Edit `config/email.php` to configure email settings:

```php
'smtp' => [
    'host' => 'smtp.gmail.com',
    'username' => 'your_email@gmail.com',
    'password' => 'your_password',
    // ... other settings
]
```

### Application Settings
Edit `config/app.php` for general application settings:

```php
'name' => 'NSSF Uganda Dashboard',
'environment' => 'production', // or 'development'
'debug' => false,
'url' => 'https://your-domain.com',
// ... other settings
```

## Usage

### Admin Panel
Access the admin panel at `/views/dashboard/` after logging in with administrator credentials.

### Default Credentials
For demo purposes, you can use:
- **Username**: admin
- **Password**: admin123

> **Important**: Change default credentials in production!

### Key Features

#### Dashboard Overview
- Real-time statistics and KPIs
- Interactive charts and graphs
- Recent activity feed
- Quick action buttons

#### Member Management
- Add/edit/delete members
- Member search and filtering
- Contribution history tracking
- Statement generation

#### Contribution Processing
- Bulk contribution uploads
- Validation and error handling
- Monthly contribution reports
- Payment reconciliation

#### Claims Management
- Claim submission and tracking
- Approval workflow
- Document management
- Automated notifications

## API Documentation

The dashboard provides RESTful API endpoints for integration:

### Authentication
All API endpoints require authentication via session or API token.

### Endpoints
- `GET /api/members.php` - List members
- `POST /api/members.php` - Create member
- `GET /api/contributions.php` - List contributions
- `POST /api/contributions.php` - Add contribution
- `GET /api/dashboard-stats.php` - Dashboard statistics

## Security

### Security Features
- **CSRF Protection**: All forms include CSRF tokens
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: Secure session configuration
- **Password Hashing**: Strong password hashing with salt
- **Role-Based Access**: Granular permission system

### Security Recommendations
1. Use HTTPS in production
2. Regular security updates
3. Strong password policies
4. Regular security audits
5. Backup encryption

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- **Email**: support@nssf-uganda.com
- **Documentation**: `/docs/`
- **GitHub Issues**: [Create an issue](https://github.com/BARIGYE-DAVIS/TOURS/issues)

## Changelog

### Version 1.0.0 (2024-01-01)
- Initial release
- Core dashboard functionality
- Member and employer management
- Contribution processing
- Basic reporting system
- Authentication and security features

## Roadmap

### Future Enhancements
- Mobile application API
- Advanced AI analytics
- Real-time notifications
- Enhanced reporting
- Integration with external systems
- Multi-language support

---

**Built with ❤️ by BARIGYE-DAVIS**