# 🐳 FileManager Pro - Docker Deployment

<div align="center">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Ready">
  <img src="https://img.shields.io/badge/Compose-Supported-FCC624?style=for-the-badge&logo=docker&logoColor=black" alt="Docker Compose">
  <img src="https://img.shields.io/badge/Multi--Stage-Build-success?style=for-the-badge" alt="Multi-stage Build">
</div>

## 🚀 Quick Start with Docker

### Prerequisites
- Docker 20.10+
- Docker Compose 2.0+
- 2GB+ available RAM
- 5GB+ available disk space

### 1. Clone Repository
```bash
git clone https://github.com/whympxx/SystemManagementFile.git
cd SystemManagementFile
```

### 2. Start Development Environment
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f app

# Check status
docker-compose ps
```

### 3. Access Application
- **FileManager Pro**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Health Check**: http://localhost:8080/health.php

### 4. Default Credentials
- **Database**: 
  - Host: `localhost:3306`
  - Database: `filemanager_dev`
  - Username: `filemanager`
  - Password: `dev_password`

## 📋 Available Commands

### Basic Operations
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Rebuild containers
docker-compose build --no-cache

# View logs
docker-compose logs -f [service_name]

# Execute commands in container
docker-compose exec app bash
```

### Development Helpers
```bash
# Install dependencies
docker-compose exec app composer install

# Clear logs
docker-compose exec app rm -rf logs/*.log

# Check PHP configuration
docker-compose exec app php -i

# Database operations
docker-compose exec db mysql -u root -p
```

## 🏭 Production Deployment

### 1. Build Production Image
```bash
# Build production image
docker build --target production -t filemanager-pro:latest .

# Or use multi-stage build
docker build -t filemanager-pro:prod --target production .
```

### 2. Production Docker Compose
Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    image: filemanager-pro:latest
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - uploads_data:/var/www/html/uploads
      - logs_data:/var/www/html/logs
    environment:
      - ENVIRONMENT=production
      - DB_HOST=db
      - DB_NAME=filemanager_prod
      - DB_USER=filemanager
      - DB_PASS=your_secure_password
    depends_on:
      - db
    restart: unless-stopped

  db:
    image: mysql:8.0
    volumes:
      - mysql_data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD=your_root_password
      - MYSQL_DATABASE=filemanager_prod
      - MYSQL_USER=filemanager
      - MYSQL_PASSWORD=your_secure_password
    restart: unless-stopped

volumes:
  mysql_data:
  uploads_data:
  logs_data:
```

### 3. Deploy Production
```bash
# Deploy with production compose
docker-compose -f docker-compose.prod.yml up -d

# Scale application
docker-compose -f docker-compose.prod.yml up -d --scale app=3
```

## 🔧 Configuration

### Environment Variables
| Variable | Description | Default |
|----------|-------------|---------|
| `ENVIRONMENT` | Environment mode | `development` |
| `DEBUG_MODE` | Enable debug mode | `true` |
| `DB_HOST` | Database host | `db` |
| `DB_NAME` | Database name | `filemanager_dev` |
| `DB_USER` | Database user | `filemanager` |
| `DB_PASS` | Database password | `dev_password` |

### Volume Mappings
| Host Path | Container Path | Purpose |
|-----------|----------------|---------|
| `./uploads` | `/var/www/html/uploads` | File storage |
| `./logs` | `/var/www/html/logs` | Application logs |
| `mysql_data` | `/var/lib/mysql` | Database files |

### Port Mappings
| Host Port | Container Port | Service |
|-----------|----------------|---------|
| `8080` | `80` | FileManager Pro |
| `8081` | `80` | phpMyAdmin |
| `3306` | `3306` | MySQL Database |
| `6379` | `6379` | Redis Cache |

## 🐛 Troubleshooting

### Common Issues

#### Container Won't Start
```bash
# Check container logs
docker-compose logs app

# Check system resources
docker system df
docker system prune

# Rebuild without cache
docker-compose build --no-cache
```

#### Database Connection Error
```bash
# Check database status
docker-compose exec db mysqladmin ping

# Reset database
docker-compose down
docker volume rm systemmanagementfile_mysql_data
docker-compose up -d
```

#### Permission Issues
```bash
# Fix file permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/uploads
docker-compose exec app chmod -R 755 /var/www/html/uploads
```

#### Out of Space
```bash
# Clean up Docker
docker system prune -a
docker volume prune

# Check disk usage
df -h
```

### Health Checks
```bash
# Application health
curl http://localhost:8080/health.php

# Database health
docker-compose exec db mysqladmin ping

# Container health
docker-compose ps
```

## 📊 Monitoring

### View Container Stats
```bash
# Real-time stats
docker stats

# Container inspection
docker-compose exec app ps aux
docker-compose exec app df -h
```

### Log Management
```bash
# Application logs
docker-compose logs -f app

# Database logs
docker-compose logs -f db

# Follow specific log
docker-compose exec app tail -f logs/error.log
```

## 🔒 Security Considerations

### Production Security
1. **Change default passwords**
2. **Use secrets management**
3. **Enable HTTPS**
4. **Restrict network access**
5. **Regular updates**

### Secure Configuration
```yaml
# Use Docker secrets
secrets:
  db_password:
    file: ./secrets/db_password.txt
  mysql_root_password:
    file: ./secrets/mysql_root_password.txt

services:
  app:
    secrets:
      - db_password
    environment:
      - DB_PASS_FILE=/run/secrets/db_password
```

## 📈 Performance Optimization

### Resource Limits
```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 1G
        reservations:
          cpus: '1.0'
          memory: 512M
```

### Caching
```yaml
services:
  redis:
    image: redis:7-alpine
    command: redis-server --maxmemory 256mb --maxmemory-policy allkeys-lru
```

## 🚀 Advanced Usage

### Multi-Environment Setup
```bash
# Development
docker-compose -f docker-compose.yml up -d

# Staging
docker-compose -f docker-compose.staging.yml up -d

# Production
docker-compose -f docker-compose.prod.yml up -d
```

### Container Orchestration
```bash
# Docker Swarm
docker swarm init
docker stack deploy -c docker-compose.prod.yml filemanager

# Kubernetes (requires conversion)
kompose convert -f docker-compose.prod.yml
kubectl apply -f .
```

## 📞 Support

### Docker-specific Issues
- 🐳 [Docker Hub](https://hub.docker.com/)
- 📖 [Docker Documentation](https://docs.docker.com/)
- 💬 [Docker Community](https://www.docker.com/community)

### FileManager Pro Support
- 📋 [GitHub Issues](https://github.com/whympxx/SystemManagementFile/issues)
- 💬 [Discussions](https://github.com/whympxx/SystemManagementFile/discussions)
- 📖 [Full Documentation](README.md)

---

**Built with ❤️ by [whympxx](https://github.com/whympxx)**

*Docker deployment made simple and secure*
