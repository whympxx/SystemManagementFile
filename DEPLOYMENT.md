# 🚀 FileManager Pro - Deployment Guide

<div align="center">
  <img src="https://img.shields.io/badge/Deployment-Production_Ready-28a745?style=for-the-badge" alt="Production Ready">
  <img src="https://img.shields.io/badge/Platform-Multi_Cloud-4f46e5?style=for-the-badge" alt="Multi Cloud">
  <img src="https://img.shields.io/badge/Scalability-High-ff6b6b?style=for-the-badge" alt="High Scalability">
</div>

## 📋 Table of Contents

- [Production Deployment](#production-deployment)
- [Cloud Platforms](#cloud-platforms)
- [Container Deployment](#container-deployment)
- [CI/CD Pipeline](#cicd-pipeline)
- [Load Balancing](#load-balancing)
- [Monitoring & Logging](#monitoring--logging)
- [Backup Strategies](#backup-strategies)
- [Performance Optimization](#performance-optimization)

## 🏭 Production Deployment

### Pre-Deployment Checklist

```bash
# ✅ System Requirements Check
- [ ] PHP 8.0+ with required extensions
- [ ] MySQL/MariaDB 5.7+
- [ ] Web server (Apache/Nginx)
- [ ] SSL certificate configured
- [ ] Firewall rules configured
- [ ] Backup strategy in place
- [ ] Monitoring tools setup
- [ ] Domain name configured
```

### Production Environment Setup

#### 1. Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apache2 mysql-server php8.1 php8.1-mysql \
    php8.1-gd php8.1-mbstring php8.1-curl php8.1-zip \
    php8.1-fileinfo php8.1-xml php8.1-bcmath

# Install additional tools
sudo apt install -y git curl wget unzip certbot python3-certbot-apache
```

#### 2. Database Configuration

```sql
-- Create production database
CREATE DATABASE filemanager_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user with minimal privileges
CREATE USER 'fm_prod_user'@'localhost' IDENTIFIED BY 'STRONG_RANDOM_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON filemanager_prod.* TO 'fm_prod_user'@'localhost';
FLUSH PRIVILEGES;

-- Import database schema
USE filemanager_prod;
SOURCE /path/to/database_schema.sql;
```

#### 3. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/filemanager-pro
cd /var/www/filemanager-pro

# Clone from production branch
git clone -b main https://github.com/whympxx/SystemManagementFile.git .

# Set proper ownership
sudo chown -R www-data:www-data /var/www/filemanager-pro

# Set secure permissions
find /var/www/filemanager-pro -type d -exec chmod 755 {} \;
find /var/www/filemanager-pro -type f -exec chmod 644 {} \;

# Secure sensitive directories
chmod 700 /var/www/filemanager-pro/config
chmod 600 /var/www/filemanager-pro/config/*.php

# Create and secure upload directories
mkdir -p /var/www/filemanager-pro/uploads/{files,thumbnails,temp}
chmod 755 /var/www/filemanager-pro/uploads
chown -R www-data:www-data /var/www/filemanager-pro/uploads
```

#### 4. Configuration Files

```php
<?php
// config/production.php
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', false);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'filemanager_prod');
define('DB_USER', 'fm_prod_user');
define('DB_PASS', 'STRONG_RANDOM_PASSWORD');

// Security settings
define('CSRF_TOKEN_EXPIRE', 3600);
define('SESSION_TIMEOUT', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOGIN_LOCKOUT_TIME', 1800); // 30 minutes

// File upload limits
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('MAX_UPLOAD_FILES', 10);

// Email configuration (for production notifications)
define('SMTP_HOST', 'smtp.yourdomain.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'EMAIL_PASSWORD');
define('SMTP_ENCRYPTION', 'tls');
?>
```

#### 5. Apache Virtual Host

```apache
# /etc/apache2/sites-available/filemanager-pro.conf
<VirtualHost *:443>
    ServerName filemanager.yourdomain.com
    DocumentRoot /var/www/filemanager-pro
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/filemanager.yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/filemanager.yourdomain.com/privkey.pem
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
    
    # Directory Configuration
    <Directory /var/www/filemanager-pro>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Uploads Security
    <Directory /var/www/filemanager-pro/uploads>
        Options -ExecCGI -Indexes
        AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi
        php_flag engine off
    </Directory>
    
    # Hide sensitive files
    <FilesMatch "\.(sql|log|env|config|ini)$">
        Require all denied
    </FilesMatch>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/filemanager-error.log
    CustomLog ${APACHE_LOG_DIR}/filemanager-access.log combined
    LogLevel warn
</VirtualHost>

# HTTP to HTTPS redirect
<VirtualHost *:80>
    ServerName filemanager.yourdomain.com
    Redirect permanent / https://filemanager.yourdomain.com/
</VirtualHost>
```

#### 6. Enable Site and SSL

```bash
# Enable site
sudo a2ensite filemanager-pro.conf

# Enable required modules
sudo a2enmod rewrite ssl headers

# Get SSL certificate
sudo certbot --apache -d filemanager.yourdomain.com

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

## ☁️ Cloud Platforms

### AWS Deployment

#### EC2 Instance Setup

```bash
# Launch EC2 instance (Ubuntu 20.04 LTS)
# Recommended: t3.medium or larger for production

# Connect to instance
ssh -i your-key.pem ubuntu@your-ec2-ip

# Update and install packages
sudo apt update && sudo apt upgrade -y
sudo apt install -y apache2 mysql-server php8.1 php8.1-mysql php8.1-gd

# Configure RDS (optional)
# Create RDS MySQL instance for better scalability
```

#### RDS Database Configuration

```bash
# Create RDS MySQL instance
aws rds create-db-instance \
    --db-instance-identifier filemanager-prod \
    --db-instance-class db.t3.micro \
    --engine mysql \
    --engine-version 8.0.28 \
    --allocated-storage 20 \
    --db-name filemanager_prod \
    --master-username admin \
    --master-user-password YourSecurePassword \
    --vpc-security-group-ids sg-xxxxxxxxx \
    --backup-retention-period 7 \
    --multi-az
```

#### S3 Storage Integration

```php
<?php
// config/aws.php
define('AWS_REGION', 'us-east-1');
define('AWS_BUCKET', 'filemanager-uploads');
define('AWS_ACCESS_KEY_ID', 'YOUR_ACCESS_KEY');
define('AWS_SECRET_ACCESS_KEY', 'YOUR_SECRET_KEY');

// Enable S3 storage for uploads
define('USE_S3_STORAGE', true);
define('S3_UPLOAD_PATH', 'uploads/');
define('S3_CDN_URL', 'https://cdn.yourdomain.com/');
?>
```

### DigitalOcean Deployment

#### Droplet Setup

```bash
# Create Droplet (Ubuntu 20.04, 2GB RAM minimum)
# Use DigitalOcean One-Click LAMP Stack

# Access droplet
ssh root@your-droplet-ip

# Deploy application
cd /var/www/html
git clone https://github.com/whympxx/SystemManagementFile.git filemanager
cd filemanager

# Configure database
mysql -u root -p
# Follow database setup steps from above
```

#### Spaces Object Storage

```bash
# Install s3cmd for Spaces integration
sudo apt install s3cmd

# Configure Spaces
s3cmd --configure
# Endpoint: nyc3.digitaloceanspaces.com
# Access Key: Your Spaces key
# Secret Key: Your Spaces secret
```

### Google Cloud Platform

#### Compute Engine Setup

```bash
# Create VM instance
gcloud compute instances create filemanager-prod \
    --image-family ubuntu-2004-lts \
    --image-project ubuntu-os-cloud \
    --machine-type e2-medium \
    --zone us-central1-a \
    --tags http-server,https-server

# SSH to instance
gcloud compute ssh filemanager-prod --zone us-central1-a
```

#### Cloud SQL Integration

```bash
# Create Cloud SQL instance
gcloud sql instances create filemanager-db \
    --database-version MYSQL_8_0 \
    --tier db-n1-standard-1 \
    --region us-central1 \
    --backup-start-time 03:00

# Create database
gcloud sql databases create filemanager_prod --instance filemanager-db
```

## 🐳 Container Deployment

### Docker Production Setup

#### Multi-stage Dockerfile

```dockerfile
# Production Dockerfile
FROM php:8.1-apache as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    curl

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        gd \
        fileinfo \
        mbstring \
        zip \
        xml \
        bcmath

# Enable Apache modules
RUN a2enmod rewrite headers ssl

# Production stage
FROM base as production

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Set permissions
RUN chmod -R 755 /var/www/html \
    && chmod -R 700 /var/www/html/config \
    && mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads

# Copy production Apache config
COPY docker/apache-prod.conf /etc/apache2/sites-available/000-default.conf

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80 443
```

#### Docker Compose Production

```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  app:
    build:
      context: .
      target: production
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - uploads_data:/var/www/html/uploads
      - logs_data:/var/www/html/logs
      - ssl_certs:/etc/ssl/certs
    environment:
      - ENVIRONMENT=production
      - DB_HOST=db
      - DB_NAME=filemanager_prod
      - DB_USER=filemanager
      - DB_PASS_FILE=/run/secrets/db_password
    secrets:
      - db_password
      - app_secret_key
    depends_on:
      db:
        condition: service_healthy
    networks:
      - filemanager_network

  db:
    image: mysql:8.0
    restart: unless-stopped
    command: --default-authentication-plugin=mysql_native_password
    environment:
      - MYSQL_ROOT_PASSWORD_FILE=/run/secrets/mysql_root_password
      - MYSQL_DATABASE=filemanager_prod
      - MYSQL_USER=filemanager
      - MYSQL_PASSWORD_FILE=/run/secrets/db_password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./config/mysql-prod.cnf:/etc/mysql/conf.d/custom.cnf
      - ./backup:/backup
    secrets:
      - mysql_root_password
      - db_password
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 20s
      retries: 10
    networks:
      - filemanager_network

  redis:
    image: redis:7-alpine
    restart: unless-stopped
    command: redis-server --requirepass /run/secrets/redis_password
    volumes:
      - redis_data:/data
    secrets:
      - redis_password
    networks:
      - filemanager_network

  nginx:
    image: nginx:alpine
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/nginx.conf
      - ssl_certs:/etc/ssl/certs
      - uploads_data:/var/www/html/uploads:ro
    depends_on:
      - app
    networks:
      - filemanager_network

secrets:
  db_password:
    file: ./secrets/db_password.txt
  mysql_root_password:
    file: ./secrets/mysql_root_password.txt
  redis_password:
    file: ./secrets/redis_password.txt
  app_secret_key:
    file: ./secrets/app_secret_key.txt

volumes:
  mysql_data:
  redis_data:
  uploads_data:
  logs_data:
  ssl_certs:

networks:
  filemanager_network:
    driver: bridge
```

### Kubernetes Deployment

#### Deployment Manifest

```yaml
# k8s/deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: filemanager-app
  labels:
    app: filemanager
spec:
  replicas: 3
  selector:
    matchLabels:
      app: filemanager
  template:
    metadata:
      labels:
        app: filemanager
    spec:
      containers:
      - name: filemanager
        image: filemanager:latest
        ports:
        - containerPort: 80
        env:
        - name: DB_HOST
          value: "mysql-service"
        - name: DB_NAME
          value: "filemanager_prod"
        - name: DB_USER
          valueFrom:
            secretKeyRef:
              name: db-credentials
              key: username
        - name: DB_PASS
          valueFrom:
            secretKeyRef:
              name: db-credentials
              key: password
        volumeMounts:
        - name: uploads-storage
          mountPath: /var/www/html/uploads
        livenessProbe:
          httpGet:
            path: /health
            port: 80
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /health
            port: 80
          initialDelaySeconds: 5
          periodSeconds: 5
      volumes:
      - name: uploads-storage
        persistentVolumeClaim:
          claimName: uploads-pvc
```

## 🔄 CI/CD Pipeline

### GitHub Actions Workflow

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: filemanager_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: pdo, pdo_mysql, gd, fileinfo, mbstring
    
    - name: Install dependencies
      run: |
        composer install --no-dev --optimize-autoloader
    
    - name: Run tests
      run: |
        php artisan test
        php scripts/security_check.php
    
    - name: Build assets
      run: |
        npm install
        npm run build
  
  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to production
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.PROD_HOST }}
        username: ${{ secrets.PROD_USER }}
        key: ${{ secrets.PROD_SSH_KEY }}
        script: |
          cd /var/www/filemanager-pro
          git pull origin main
          composer install --no-dev --optimize-autoloader
          php scripts/migrate.php
          sudo systemctl reload apache2
```

### GitLab CI/CD

```yaml
# .gitlab-ci.yml
stages:
  - test
  - build
  - deploy

variables:
  MYSQL_ROOT_PASSWORD: root
  MYSQL_DATABASE: filemanager_test

test:
  stage: test
  image: php:8.1
  services:
    - mysql:8.0
  before_script:
    - apt-get update -qq && apt-get install -y -qq git
    - docker-php-ext-install pdo_mysql
  script:
    - php scripts/test.php
    - php scripts/security_check.php

build:
  stage: build
  script:
    - docker build -t $CI_REGISTRY_IMAGE:$CI_COMMIT_SHA .
    - docker push $CI_REGISTRY_IMAGE:$CI_COMMIT_SHA

deploy:
  stage: deploy
  script:
    - kubectl set image deployment/filemanager-app filemanager=$CI_REGISTRY_IMAGE:$CI_COMMIT_SHA
  only:
    - main
```

## ⚖️ Load Balancing

### Nginx Load Balancer

```nginx
# /etc/nginx/sites-available/filemanager-lb
upstream filemanager_backend {
    least_conn;
    server 10.0.1.10:80 weight=3 max_fails=3 fail_timeout=30s;
    server 10.0.1.11:80 weight=3 max_fails=3 fail_timeout=30s;
    server 10.0.1.12:80 weight=2 max_fails=3 fail_timeout=30s;
}

server {
    listen 80;
    server_name filemanager.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name filemanager.yourdomain.com;
    
    ssl_certificate /etc/ssl/certs/filemanager.crt;
    ssl_certificate_key /etc/ssl/private/filemanager.key;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=5r/s;
    limit_req_zone $binary_remote_addr zone=upload:10m rate=2r/s;
    
    location / {
        proxy_pass http://filemanager_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
    
    location /api/ {
        limit_req zone=api burst=10 nodelay;
        proxy_pass http://filemanager_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    location /upload {
        limit_req zone=upload burst=5 nodelay;
        client_max_body_size 100M;
        proxy_pass http://filemanager_backend;
        proxy_request_buffering off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        proxy_pass http://filemanager_backend;
    }
}
```

### HAProxy Configuration

```bash
# /etc/haproxy/haproxy.cfg
global
    maxconn 4096
    ssl-default-bind-ciphers ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-CHACHA20-POLY1305
    ssl-default-bind-options ssl-min-ver TLSv1.2 no-tls-tickets

defaults
    mode http
    timeout connect 5000ms
    timeout client 50000ms
    timeout server 50000ms
    option httplog

frontend filemanager_frontend
    bind *:443 ssl crt /etc/ssl/certs/filemanager.pem
    redirect scheme https if !{ ssl_fc }
    
    # Rate limiting
    stick-table type ip size 100k expire 30s store http_req_rate(10s)
    http-request track-sc0 src
    http-request reject if { sc_http_req_rate(0) gt 20 }
    
    default_backend filemanager_backend

backend filemanager_backend
    balance roundrobin
    option httpchk GET /health
    
    server web1 10.0.1.10:80 check
    server web2 10.0.1.11:80 check
    server web3 10.0.1.12:80 check backup
```

## 📊 Monitoring & Logging

### Application Monitoring

#### Health Check Endpoint

```php
<?php
// health.php
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'version' => '1.2.0',
    'checks' => []
];

try {
    // Database check
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $health['checks']['database'] = 'ok';
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['checks']['database'] = 'error';
}

// Storage check
if (is_writable(UPLOAD_PATH)) {
    $health['checks']['storage'] = 'ok';
} else {
    $health['status'] = 'error';
    $health['checks']['storage'] = 'error';
}

// Memory check
$memory_usage = memory_get_usage(true);
$memory_limit = ini_get('memory_limit');
$health['checks']['memory'] = [
    'usage' => $memory_usage,
    'limit' => $memory_limit,
    'status' => 'ok'
];

http_response_code($health['status'] === 'ok' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
?>
```

### Logging Configuration

```php
<?php
// includes/logger.php
class Logger {
    private static $logDir = __DIR__ . '/../logs/';
    
    public static function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logFile = self::$logDir . $level . '_' . date('Y-m-d') . '.log';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    public static function error($message, $context = []) {
        self::log('error', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('warning', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::log('info', $message, $context);
    }
    
    public static function security($message, $context = []) {
        self::log('security', $message, $context);
    }
}
?>
```

### Monitoring with Prometheus

```yaml
# docker-compose.monitoring.yml
version: '3.8'

services:
  prometheus:
    image: prom/prometheus:latest
    ports:
      - "9090:9090"
    volumes:
      - ./monitoring/prometheus.yml:/etc/prometheus/prometheus.yml
      - prometheus_data:/prometheus

  grafana:
    image: grafana/grafana:latest
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
    volumes:
      - grafana_data:/var/lib/grafana
      - ./monitoring/grafana:/etc/grafana/provisioning

  node-exporter:
    image: prom/node-exporter:latest
    ports:
      - "9100:9100"

volumes:
  prometheus_data:
  grafana_data:
```

## 💾 Backup Strategies

### Automated Backup Script

```bash
#!/bin/bash
# scripts/backup.sh

# Configuration
BACKUP_DIR="/backup"
DB_NAME="filemanager_prod"
DB_USER="backup_user"
DB_PASS="backup_password"
UPLOAD_DIR="/var/www/filemanager-pro/uploads"
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR/{database,files}

# Database backup
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DB_BACKUP="$BACKUP_DIR/database/db_backup_$TIMESTAMP.sql.gz"

mysqldump -u$DB_USER -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    $DB_NAME | gzip > $DB_BACKUP

echo "Database backup completed: $DB_BACKUP"

# Files backup
FILES_BACKUP="$BACKUP_DIR/files/files_backup_$TIMESTAMP.tar.gz"
tar -czf $FILES_BACKUP -C $UPLOAD_DIR .

echo "Files backup completed: $FILES_BACKUP"

# Upload to S3 (optional)
if command -v aws &> /dev/null; then
    aws s3 cp $DB_BACKUP s3://filemanager-backups/database/
    aws s3 cp $FILES_BACKUP s3://filemanager-backups/files/
    echo "Backups uploaded to S3"
fi

# Cleanup old backups
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup cleanup completed"
```

### Backup Cron Job

```bash
# Add to crontab
# Run backup daily at 2 AM
0 2 * * * /var/www/filemanager-pro/scripts/backup.sh >> /var/log/backup.log 2>&1

# Run weekly full backup on Sundays at 1 AM
0 1 * * 0 /var/www/filemanager-pro/scripts/full_backup.sh >> /var/log/backup.log 2>&1
```

### Restore Procedures

```bash
#!/bin/bash
# scripts/restore.sh

BACKUP_FILE=$1
RESTORE_TYPE=$2

if [ "$RESTORE_TYPE" = "database" ]; then
    echo "Restoring database from $BACKUP_FILE"
    gunzip < $BACKUP_FILE | mysql -u$DB_USER -p$DB_PASS $DB_NAME
    echo "Database restore completed"
    
elif [ "$RESTORE_TYPE" = "files" ]; then
    echo "Restoring files from $BACKUP_FILE"
    tar -xzf $BACKUP_FILE -C $UPLOAD_DIR
    chown -R www-data:www-data $UPLOAD_DIR
    echo "Files restore completed"
    
else
    echo "Usage: $0 <backup_file> <database|files>"
    exit 1
fi
```

## ⚡ Performance Optimization

### Database Optimization

```sql
-- MySQL configuration for production
-- /etc/mysql/mysql.conf.d/filemanager.cnf

[mysqld]
# Memory settings
innodb_buffer_pool_size = 1G
innodb_buffer_pool_instances = 4
query_cache_size = 128M
query_cache_limit = 2M

# Connection settings
max_connections = 200
max_connect_errors = 10000
wait_timeout = 300
interactive_timeout = 300

# Performance settings
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
innodb_file_per_table = 1
innodb_log_file_size = 256M

# Binary logging
log_bin = /var/log/mysql/mysql-bin.log
binlog_format = MIXED
expire_logs_days = 7
```

### PHP Optimization

```ini
; /etc/php/8.1/apache2/conf.d/99-filemanager.ini

; Memory limits
memory_limit = 256M
max_execution_time = 300
max_input_time = 300

; File uploads
upload_max_filesize = 100M
post_max_size = 105M
max_file_uploads = 20

; OPcache settings
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1

; Session settings
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"
session.gc_maxlifetime = 7200
```

### CDN Integration

```php
<?php
// config/cdn.php
define('CDN_ENABLED', true);
define('CDN_URL', 'https://cdn.yourdomain.com');
define('CDN_STATIC_URL', 'https://static.yourdomain.com');

function getCDNUrl($path) {
    if (CDN_ENABLED) {
        return CDN_URL . '/' . ltrim($path, '/');
    }
    return $path;
}

function getStaticUrl($path) {
    if (CDN_ENABLED) {
        return CDN_STATIC_URL . '/' . ltrim($path, '/');
    }
    return $path;
}
?>
```

---

## 📞 Support & Resources

### Deployment Support
- 📋 [GitHub Issues](https://github.com/whympxx/SystemManagementFile/issues)
- 💬 [Deployment Discussions](https://github.com/whympxx/SystemManagementFile/discussions)
- 📖 [Wiki Documentation](https://github.com/whympxx/SystemManagementFile/wiki)

### Professional Services
- 🏢 **Enterprise Support** - Available for large deployments
- 🛠️ **Custom Integration** - Tailored solutions
- 📊 **Performance Consulting** - Optimization services

---

**Built with ❤️ by [whympxx](https://github.com/whympxx)**

*Last updated: January 24, 2025*
