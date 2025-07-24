# 🚀 FileManager Pro - Installation Guide

<div align="center">
  <img src="https://img.shields.io/badge/Installation-Easy_Setup-28a745?style=for-the-badge" alt="Easy Installation">
  <img src="https://img.shields.io/badge/Deploy_Time-5_Minutes-ff6b6b?style=for-the-badge" alt="Quick Deploy">
  <img src="https://img.shields.io/badge/Support-Multiple_Platforms-4f46e5?style=for-the-badge" alt="Multi Platform">
</div>

## 📋 Table of Contents

- [System Requirements](#system-requirements)
- [Quick Installation](#quick-installation)
- [Detailed Installation](#detailed-installation)
- [Deployment Options](#deployment-options)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)
- [Post-Installation](#post-installation)

## 🖥️ System Requirements

### Minimum Requirements

| Component | Version | Notes |
|-----------|---------|-------|
| **PHP** | 8.0+ | Recommended: PHP 8.1+ |
| **MySQL** | 5.7+ | Or MariaDB 10.3+ |
| **Web Server** | Apache 2.4+ | Or Nginx 1.18+ |
| **Memory** | 512MB RAM | Recommended: 1GB+ |
| **Storage** | 1GB | For application + uploads |

### Required PHP Extensions

```bash
# Check if extensions are installed
php -m | grep -E "(pdo|pdo_mysql|gd|fileinfo|mbstring|curl|zip|json)"
```

Required extensions:
- `pdo` - Database abstraction
- `pdo_mysql` - MySQL driver
- `gd` - Image processing
- `fileinfo` - MIME type detection
- `mbstring` - String handling
- `curl` - HTTP requests
- `zip` - Archive handling
- `json` - JSON processing

## ⚡ Quick Installation

### One-Command Installation

```bash
# Download and setup in one command
curl -fsSL https://raw.githubusercontent.com/whympxx/SystemManagementFile/main/scripts/install.sh | bash
```

### Manual Quick Setup

```bash
# 1. Clone repository
git clone https://github.com/whympxx/SystemManagementFile.git
cd SystemManagementFile

# 2. Run setup script
chmod +x setup.sh
./setup.sh
```

## 📖 Detailed Installation

### Step 1: Download Source Code

#### Option A: Git Clone (Recommended)
```bash
git clone https://github.com/whympxx/SystemManagementFile.git filemanager-pro
cd filemanager-pro
```

#### Option B: Download ZIP
```bash
wget https://github.com/whympxx/SystemManagementFile/archive/main.zip
unzip main.zip
mv SystemManagementFile-main filemanager-pro
cd filemanager-pro
```

### Step 2: Database Setup

#### Create Database
```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE filemanager_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (recommended)
CREATE USER 'filemanager'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON filemanager_pro.* TO 'filemanager'@'localhost';
FLUSH PRIVILEGES;

-- Use the database
USE filemanager_pro;
```

#### Import Database Schema
```bash
# Import from SQL file
mysql -u filemanager -p filemanager_pro < config/database_schema.sql

# Or run the initialization script
php init_database.php
```

### Step 3: Configuration

#### Database Configuration
```bash
# Copy example config
cp config/database.php.example config/database.php

# Edit configuration
nano config/database.php
```

```php
<?php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'filemanager_pro');
define('DB_USER', 'filemanager');
define('DB_PASS', 'your_secure_password');
define('DB_CHARSET', 'utf8mb4');

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'zip']);

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
?>
```

#### Application Configuration
```bash
# Copy application config
cp config/app.php.example config/app.php
```

```php
<?php
// config/app.php
define('APP_NAME', 'FileManager Pro');
define('APP_VERSION', '1.2.0');
define('APP_URL', 'http://localhost/filemanager-pro');
define('DEBUG_MODE', false); // Set to true for development
define('TIMEZONE', 'Asia/Jakarta');

// Email configuration (for password reset)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@yoursite.com');
define('SMTP_FROM_NAME', 'FileManager Pro');
?>
```

### Step 4: Directory Setup

```bash
# Create upload directories
mkdir -p uploads/{files,thumbnails,temp}

# Set permissions (Linux/Unix)
chmod 755 uploads/
chmod 755 uploads/files/
chmod 755 uploads/thumbnails/
chmod 755 uploads/temp/

# Set ownership (adjust user/group as needed)
chown -R www-data:www-data uploads/
chown -R www-data:www-data logs/

# For development on local machine
chmod 777 uploads/ # Less secure, only for local development
```

### Step 5: Web Server Configuration

#### Apache Configuration

```apache
# .htaccess (already included)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options SAMEORIGIN
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Hide sensitive files
<FilesMatch "\.(sql|log|env|config)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# File upload security
<Directory "uploads/">
    Options -ExecCGI
    AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi
    php_flag engine off
</Directory>
```

#### Virtual Host Example
```apache
<VirtualHost *:80>
    ServerName filemanager.local
    DocumentRoot /var/www/filemanager-pro
    
    <Directory /var/www/filemanager-pro>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/filemanager_error.log
    CustomLog ${APACHE_LOG_DIR}/filemanager_access.log combined
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name filemanager.local;
    root /var/www/filemanager-pro;
    index index.php index.html;

    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";

    # File upload limits
    client_max_body_size 50M;

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security
    location ~ /\.(ht|git|env) {
        deny all;
    }

    # Hide sensitive files
    location ~* \.(sql|log|config)$ {
        deny all;
    }

    # Uploads security
    location /uploads/ {
        location ~ \.php$ {
            deny all;
        }
    }

    # Pretty URLs
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

## 🐳 Deployment Options

### Docker Deployment

#### Docker Compose Setup
```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./uploads:/var/www/html/uploads
      - ./logs:/var/www/html/logs
    environment:
      - DB_HOST=db
      - DB_NAME=filemanager_pro
      - DB_USER=filemanager
      - DB_PASS=secure_password
    depends_on:
      - db

  db:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      - MYSQL_ROOT_PASSWORD=root_password
      - MYSQL_DATABASE=filemanager_pro
      - MYSQL_USER=filemanager
      - MYSQL_PASSWORD=secure_password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./config/database_schema.sql:/docker-entrypoint-initdb.d/init.sql

volumes:
  mysql_data:
```

#### Dockerfile
```dockerfile
FROM php:8.1-apache

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql gd fileinfo mbstring

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs
RUN chmod -R 755 /var/www/html/uploads /var/www/html/logs

EXPOSE 80
```

#### Deploy with Docker
```bash
# Build and run
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f app
```

### Shared Hosting Deployment

#### File Upload via FTP/SFTP
```bash
# Using SFTP
sftp user@yoursite.com
put -r filemanager-pro/ public_html/

# Using rsync
rsync -avz --exclude '.git' filemanager-pro/ user@yoursite.com:public_html/
```

#### Shared Hosting Considerations
- Check PHP version and extensions
- Verify file permissions (usually 644 for files, 755 for directories)
- Upload database via phpMyAdmin or similar
- Modify config for shared hosting environment

### VPS/Dedicated Server

#### Ubuntu/Debian Setup
```bash
# Update system
apt update && apt upgrade -y

# Install LAMP stack
apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-gd php8.1-mbstring php8.1-curl php8.1-zip php8.1-fileinfo -y

# Enable Apache modules
a2enmod rewrite headers

# Secure MySQL
mysql_secure_installation

# Clone and setup application
cd /var/www/html
git clone https://github.com/whympxx/SystemManagementFile.git filemanager-pro
cd filemanager-pro

# Set permissions
chown -R www-data:www-data .
chmod -R 755 uploads logs
```

#### CentOS/RHEL Setup
```bash
# Install EPEL repository
yum install epel-release -y

# Install LAMP stack
yum install httpd mysql-server php php-mysql php-gd php-mbstring php-curl php-zip -y

# Start services
systemctl start httpd mysql
systemctl enable httpd mysql

# Continue with application setup...
```

## ⚙️ Configuration

### Environment-Specific Configs

#### Development Environment
```php
// config/development.php
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', true);
define('ERROR_REPORTING', E_ALL);
```

#### Production Environment
```php
// config/production.php
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', false);
define('ERROR_REPORTING', E_ERROR | E_WARNING | E_PARSE);
```

#### Staging Environment
```php
// config/staging.php
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', false);
define('ERROR_REPORTING', E_ALL & ~E_NOTICE);
```

### Security Hardening

```php
// config/security.php
define('SECURE_COOKIES', true);
define('HTTPONLY_COOKIES', true);
define('SAMESITE_COOKIES', 'Strict');
define('SESSION_REGENERATE_ID', true);
define('CSRF_PROTECTION', true);
define('XSS_PROTECTION', true);
define('CONTENT_TYPE_NOSNIFF', true);
define('FRAME_OPTIONS', 'SAMEORIGIN');
```

## 🔧 Troubleshooting

### Common Issues

#### File Upload Errors
```bash
# Check PHP configuration
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"

# Fix common upload issues
echo "upload_max_filesize = 50M" >> /etc/php/8.1/apache2/php.ini
echo "post_max_size = 50M" >> /etc/php/8.1/apache2/php.ini
echo "max_execution_time = 300" >> /etc/php/8.1/apache2/php.ini

# Restart Apache
systemctl restart apache2
```

#### Permission Issues
```bash
# Fix file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Fix upload permissions
chmod 755 uploads/
chmod 644 uploads/*
chown -R www-data:www-data uploads/
```

#### Database Connection Issues
```bash
# Test database connection
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=filemanager_pro', 'filemanager', 'your_password');
    echo 'Database connection successful\n';
} catch(PDOException \$e) {
    echo 'Connection failed: ' . \$e->getMessage() . '\n';
}
"
```

#### Apache/Nginx Issues
```bash
# Check Apache status
systemctl status apache2

# Check error logs
tail -f /var/log/apache2/error.log

# Test Apache configuration
apache2ctl configtest

# For Nginx
nginx -t
systemctl status nginx
tail -f /var/log/nginx/error.log
```

### Debug Mode

Enable debug mode for troubleshooting:

```php
// In config/database.php
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
```

Check logs:
```bash
tail -f logs/error.log
tail -f logs/upload.log
tail -f logs/security.log
```

## ✅ Post-Installation

### Verification Steps

1. **Check System Health**
```bash
# Run system check script
php scripts/system_check.php
```

2. **Test File Upload**
```bash
# Test upload functionality
curl -X POST -F "file=@test.txt" http://localhost/filemanager-pro/includes/upload.php
```

3. **Security Audit**
```bash
# Run security check
php scripts/security_audit.php
```

### Initial Setup

1. **Create Admin Account**
   - Navigate to `/register.php`
   - Create your admin account
   - Login and test functionality

2. **Configure Settings**
   - Update upload limits
   - Set file type restrictions
   - Configure email settings

3. **Test All Features**
   - File upload/download
   - File management operations
   - User authentication
   - Security features

### Backup Setup

```bash
# Database backup script
#!/bin/bash
mysqldump -u filemanager -p filemanager_pro > backups/db_$(date +%Y%m%d_%H%M%S).sql

# File backup
tar -czf backups/uploads_$(date +%Y%m%d_%H%M%S).tar.gz uploads/

# Add to crontab for automated backups
crontab -e
# Add: 0 2 * * * /path/to/backup_script.sh
```

### Monitoring Setup

```bash
# Setup log rotation
cat > /etc/logrotate.d/filemanager << EOF
/var/www/html/filemanager-pro/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
EOF
```

## 🎉 Installation Complete!

Your FileManager Pro installation is now complete! 

### Next Steps:
1. 🔐 **Review security settings** in `SECURITY.md`
2. 📚 **Read the user guide** in `README.md`
3. 🛠️ **Check API documentation** in `API.md`
4. 🤝 **Contributing guidelines** in `CONTRIBUTING.md`

### Support:
- 📋 [GitHub Issues](https://github.com/whympxx/SystemManagementFile/issues)
- 💬 [Discussions](https://github.com/whympxx/SystemManagementFile/discussions)
- 📖 [Documentation](https://github.com/whympxx/SystemManagementFile/wiki)

---

**Built with ❤️ by [whympxx](https://github.com/whympxx)**
