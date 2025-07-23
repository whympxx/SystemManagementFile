# File Management System

A secure, modern web-based file management system built with PHP and JavaScript that provides comprehensive file operations with robust security features.

## 🚀 Features

### Core Functionality
- **File Upload**: Secure file uploading with validation and thumbnail generation
- **File Management**: Complete CRUD operations (Create, Read, Update, Delete)
- **File Operations**: Rename, copy, and organize files with ease
- **File Preview**: Built-in preview for images, documents, and other supported formats
- **Search & Filter**: Advanced search capabilities with file type filtering
- **Download Management**: Secure file download with access control

### Security Features
- **User Authentication**: Secure login and registration system
- **CSRF Protection**: Comprehensive Cross-Site Request Forgery protection
- **Input Validation**: Strict validation for all user inputs
- **File Type Validation**: Both extension and MIME type validation
- **Access Control**: User-based file access restrictions
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Input sanitization and output encoding

### Technical Features
- **Responsive Design**: Mobile-friendly interface
- **AJAX Operations**: Seamless user experience without page reloads
- **Error Handling**: Comprehensive error management and logging
- **Database Integration**: MySQL/MariaDB with PDO
- **Thumbnail Generation**: Automatic image thumbnail creation
- **File Size Management**: Size validation and formatting utilities

## 🛠 Technology Stack

### Backend
- **PHP 8.0+**: Core server-side logic
- **MySQL/MariaDB**: Database management
- **PDO**: Database abstraction layer
- **Session Management**: Secure user session handling

### Frontend
- **HTML5**: Modern markup structure
- **CSS3**: Responsive styling with Flexbox/Grid
- **JavaScript (ES6+)**: Interactive functionality
- **AJAX**: Asynchronous operations
- **Bootstrap 5**: UI framework components

### Development Tools
- **XAMPP/WAMP**: Local development environment
- **Git**: Version control
- **Composer**: Dependency management (future implementation)

## 📋 Prerequisites

Before installing this project, ensure you have:

- **Web Server**: Apache 2.4+ or Nginx
- **PHP**: Version 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Extensions**: 
  - PDO MySQL
  - GD Library (for image processing)
  - fileinfo (for MIME type detection)
  - mbstring (for string handling)

## ⚙️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/whympxx/SystemManagementFile.git
cd SystemManagementFile
```

### 2. Database Setup
```sql
-- Create database
CREATE DATABASE file_management_system;
USE file_management_system;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create files table
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    user_id INT DEFAULT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_files_user_id ON files(user_id);
CREATE INDEX idx_files_filename ON files(filename);
CREATE INDEX idx_files_upload_date ON files(upload_date);
```

### 3. Configuration
1. Copy `config/database.php.example` to `config/database.php`
2. Update database credentials:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'file_management_system');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
?>
```

### 4. Directory Setup
```bash
# Create necessary directories
mkdir uploads
mkdir uploads/thumbnails
chmod 755 uploads
chmod 755 uploads/thumbnails

# Set appropriate permissions
chown -R www-data:www-data uploads/ # For Linux/Ubuntu
# or
chown -R apache:apache uploads/     # For CentOS/RHEL
```

### 5. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 🎯 Usage Guide

### Getting Started
1. **Access the Application**: Navigate to `http://localhost/SystemManagementFile`
2. **Register Account**: Create a new user account or login with existing credentials
3. **Upload Files**: Use the upload interface to add files to your collection
4. **Manage Files**: Organize, rename, copy, or delete files as needed

### Main Features

#### File Upload
- Drag and drop files or click to browse
- Automatic file validation and security checks
- Progress indicators for large files
- Thumbnail generation for images

#### File Management
- **View**: Browse files in grid or list view
- **Search**: Find files by name or type
- **Filter**: Sort by date, size, or file type
- **Preview**: Quick preview for supported file types

#### File Operations
- **Rename**: Change file names with validation
- **Copy**: Create duplicates of existing files
- **Delete**: Remove files with confirmation
- **Download**: Secure file download

### API Endpoints

#### Authentication
- `POST /login.php` - User login
- `POST /register.php` - User registration
- `GET /logout.php` - User logout

#### File Operations
- `POST /includes/upload.php` - Upload new files
- `GET /includes/get_files.php` - Retrieve file list
- `POST /includes/delete.php` - Delete files
- `POST /includes/rename.php` - Rename files
- `POST /includes/copy.php` - Copy files
- `GET /includes/download.php` - Download files

## 🏗 Project Structure

```
SystemManagementFile/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   ├── main.js           # Core JavaScript functionality
│   │   ├── upload.js         # File upload handling
│   │   └── file-operations.js # File management operations
│   └── images/               # Static images and icons
├── config/
│   ├── database.php          # Database configuration
│   └── constants.php         # Application constants
├── includes/
│   ├── upload.php           # File upload processing
│   ├── delete.php           # File deletion handling
│   ├── rename.php           # File renaming logic
│   ├── copy.php             # File copying functionality
│   ├── get_files.php        # File retrieval API
│   ├── download.php         # File download handler
│   └── functions.php        # Utility functions
├── uploads/                 # File storage directory
│   └── thumbnails/         # Generated thumbnails
├── index.php               # Main application entry point
├── login.php              # User authentication
├── register.php           # User registration
├── logout.php             # Session termination
└── README.md              # Project documentation
```

## 🔧 Frontend Architecture

### JavaScript Modules

#### CSRF Token Management
```javascript
// Automatic CSRF token handling
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Include in all AJAX requests
fetch('/includes/upload.php', {
    method: 'POST',
    headers: {
        'X-CSRF-Token': csrfToken
    },
    body: formData
});
```

#### File Upload Component
- Drag and drop interface
- Progress tracking
- File validation
- Error handling

#### File Management Grid
- Dynamic file loading
- Sorting and filtering
- Context menus
- Bulk operations

## 🔒 Security Implementation

### CSRF Protection
Every form and AJAX request includes CSRF token validation:
```php
// Token generation
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Token validation
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    throw new Exception('Invalid CSRF token');
}
```

### File Upload Security
- File type validation (extension + MIME type)
- File size limits
- Malicious file detection
- Secure file naming
- Directory traversal prevention

### Database Security
- Prepared statements for all queries
- Input sanitization
- SQL injection prevention
- Error message sanitization

## 🚧 Development Guidelines

### Code Standards
- **PHP**: Follow PSR-12 coding standards
- **JavaScript**: Use ES6+ features with backward compatibility
- **CSS**: BEM methodology for class naming
- **Database**: Use meaningful table and column names

### Security Best Practices
- Always validate user input
- Use prepared statements for database queries
- Implement proper error handling
- Log security-relevant events
- Regular security audits

### Testing
```bash
# Run PHP syntax checks
find . -name "*.php" -exec php -l {} \;

# Test database connections
php -f config/test_connection.php

# Validate file permissions
ls -la uploads/
```

## 🐛 Troubleshooting

### Common Issues

#### File Upload Fails
- Check directory permissions (755 for uploads/)
- Verify PHP upload_max_filesize setting
- Ensure post_max_size is adequate
- Check available disk space

#### Database Connection Errors
- Verify database credentials in config/database.php
- Ensure MySQL/MariaDB service is running
- Check database user permissions
- Validate database exists

#### CSRF Token Errors
- Clear browser cache and cookies
- Check session configuration
- Verify token generation in forms
- Ensure AJAX requests include tokens

#### Permission Denied Errors
```bash
# Fix file permissions
chmod 755 uploads/
chmod 644 uploads/*
chown -R www-data:www-data uploads/
```

### Debug Mode
Enable debug mode in development:
```php
// config/constants.php
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
```

## 📚 API Documentation

### Response Format
All API endpoints return JSON responses:
```json
{
    "success": true|false,
    "message": "Human readable message",
    "data": {}, 
    "errors": []
}
```

### Error Codes
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found (resource doesn't exist)
- `413` - Payload Too Large (file size exceeded)
- `500` - Internal Server Error

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Contribution Guidelines
- Follow existing code style
- Add tests for new features
- Update documentation
- Ensure security best practices
- Test thoroughly before submitting

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- PHP community for excellent documentation
- Bootstrap team for the UI framework
- Contributors and testers
- Open source security community

## 📞 Support

For support and questions:
- **Issues**: [GitHub Issues](https://github.com/whympxx/SystemManagementFile/issues)
- **Discussions**: [GitHub Discussions](https://github.com/whympxx/SystemManagementFile/discussions)
- **Documentation**: Check this README and inline code comments

## 🚀 Future Enhancements

- [ ] File versioning system
- [ ] Advanced user roles and permissions
- [ ] File sharing capabilities
- [ ] REST API expansion
- [ ] Docker containerization
- [ ] Cloud storage integration
- [ ] Real-time notifications
- [ ] Advanced search with indexing
- [ ] Audit logging
- [ ] Multi-language support

---

**Built with ❤️ by [whympxx](https://github.com/whympxx)**
