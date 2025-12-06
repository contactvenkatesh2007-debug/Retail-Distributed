# Deployment Guide

## AWS EC2 Deployment

### Prerequisites

- AWS EC2 instance running Ubuntu 20.04+
- SSH access to EC2 instance
- AWS credentials configured
- Domain name (optional)

### Step 1: Prepare EC2 Instance

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER
```

### Step 2: Clone Repository

```bash
cd /var/www
sudo git clone <repository-url> retail
sudo chown -R $USER:$USER retail
cd retail
```

### Step 3: Configure Environment

```bash
cp .env.example .env
nano .env
```

Configure all environment variables, especially:
- Database credentials
- AWS credentials
- Service URLs (use internal Docker network names)

### Step 4: Build and Start Services

```bash
docker-compose build
docker-compose up -d
```

### Step 5: Run Migrations

```bash
docker-compose exec api-gateway php artisan migrate --force
```

### Step 6: Set Up Nginx (Optional)

If using Nginx as reverse proxy:

```nginx
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Step 7: Enable HTTPS (Optional)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## Docker Production Configuration

### Optimized docker-compose.prod.yml

```yaml
version: '3.8'

services:
  api-gateway:
    image: your-registry/retail-api-gateway:latest
    restart: always
    ports:
      - "80:8000"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    deploy:
      replicas: 2
      resources:
        limits:
          cpus: '1'
          memory: 512M

  # Similar for other services...
```

## Scaling Strategy

### Horizontal Scaling

```bash
# Scale API Gateway
docker-compose up -d --scale api-gateway=3

# Scale Queue Workers
docker-compose up -d --scale queue-worker=5
```

### Load Balancer Configuration

Use AWS Application Load Balancer (ALB) for production:

1. Create Target Group
2. Register EC2 instances
3. Configure Health Checks
4. Set up Listener Rules

## Monitoring Setup

### Install Monitoring Tools

```bash
# Install Prometheus (optional)
docker run -d -p 9090:9090 prom/prometheus

# Install Grafana (optional)
docker run -d -p 3000:3000 grafana/grafana
```

### Log Aggregation

Configure centralized logging:

```yaml
# docker-compose.yml
services:
  api-gateway:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```

## Backup Strategy

### Database Backups

```bash
# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec -T mysql mysqldump -u root -p$MYSQL_ROOT_PASSWORD retail > backup_$DATE.sql
aws s3 cp backup_$DATE.sql s3://your-backup-bucket/
EOF

chmod +x backup.sh

# Schedule with cron
crontab -e
# Add: 0 2 * * * /var/www/retail/backup.sh
```

### S3 Backup

Enable versioning on S3 bucket for automatic backups.

## Security Hardening

### Firewall Configuration

```bash
# Allow only necessary ports
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### SSL/TLS

- Use Let's Encrypt for free SSL certificates
- Configure automatic renewal
- Enforce HTTPS redirect

### Secrets Management

Use AWS Secrets Manager or environment variables:

```bash
# Store secrets in AWS Secrets Manager
aws secretsmanager create-secret \
  --name retail/database \
  --secret-string '{"username":"admin","password":"secret"}'
```

## Performance Optimization

### PHP-FPM Configuration

```ini
; php.ini
memory_limit = 256M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
```

### Redis Configuration

```conf
# redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### Database Optimization

- Enable query cache
- Optimize indexes
- Regular maintenance

## Troubleshooting

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f api-gateway

# Last 100 lines
docker-compose logs --tail=100 api-gateway
```

### Restart Services

```bash
docker-compose restart api-gateway
docker-compose restart queue-worker
```

### Check Service Health

```bash
curl http://localhost:8000/api/v1/health
```

## Rollback Procedure

```bash
# Stop current version
docker-compose down

# Checkout previous version
git checkout <previous-tag>

# Rebuild and start
docker-compose build
docker-compose up -d
docker-compose exec api-gateway php artisan migrate --force
```

## Maintenance Window

1. Notify users
2. Enable maintenance mode: `php artisan down`
3. Perform updates
4. Run migrations: `php artisan migrate --force`
5. Clear caches: `php artisan config:cache && php artisan route:cache`
6. Disable maintenance mode: `php artisan up`

