# AWS EC2 Deployment Guide

## Prerequisites

1. AWS EC2 Instance running (Instance ID: `i-0ed07d391b8a885e9`)
2. AWS CLI installed and configured
3. SSH access to EC2 instance
4. Git installed on EC2 instance

## 🔐 Setting Up AWS Credentials

### Option 1: Using AWS CLI (Recommended for Local)

```bash
aws configure
```

Enter your credentials:
- AWS Access Key ID: `[Your Access Key]`
- AWS Secret Access Key: `[Your Secret Key]`
- Default region: `us-east-1`
- Default output format: `json`

### Option 2: Environment Variables

```bash
export AWS_ACCESS_KEY_ID="your-access-key-id"
export AWS_SECRET_ACCESS_KEY="your-secret-access-key"
export AWS_DEFAULT_REGION="us-east-1"
```

## 🔑 Setting Up GitHub Secrets (For CI/CD)

Go to your GitHub repository → Settings → Secrets and variables → Actions → New repository secret

Add these secrets:

1. **AWS_ACCESS_KEY_ID**: Your AWS access key
2. **AWS_SECRET_ACCESS_KEY**: Your AWS secret key
3. **AWS_EC2_USER**: `ubuntu` (or `ec2-user` for Amazon Linux)
4. **AWS_SSH_PRIVATE_KEY**: Your EC2 SSH private key (contents of your .pem file)

### Getting SSH Private Key

If you don't have the SSH key:

1. Generate a new key pair in AWS EC2 Console
2. Download the `.pem` file
3. Copy the entire contents to the `AWS_SSH_PRIVATE_KEY` secret

## 📦 Initial EC2 Setup

### 1. Connect to EC2 Instance

```bash
ssh -i your-key.pem ubuntu@<your-ec2-ip>
```

### 2. Install Required Software

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-redis

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Git
sudo apt install -y git

# Install Docker (Optional, for containerized deployment)
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Install Node.js and NPM (for frontend assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Supervisor (for queue workers)
sudo apt install -y supervisor
```

### 3. Setup Application Directory

```bash
# Create application directory
sudo mkdir -p /var/www/retail
sudo chown -R $USER:$USER /var/www/retail

# Clone repository
cd /var/www
git clone <your-repository-url> retail
cd retail
```

### 4. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

Update these values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://<your-ec2-ip>

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=retail
DB_USERNAME=root
DB_PASSWORD=your_secure_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

QUEUE_CONNECTION=redis
```

### 5. Setup Database

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE retail;
CREATE USER 'retail_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON retail.* TO 'retail_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 6. Install Application

```bash
cd /var/www/retail

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Build frontend assets
npm install
npm run build
```

### 7. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/retail
```

Add this configuration:

```nginx
server {
    listen 80;
    server_name <your-ec2-ip> <your-domain>;
    root /var/www/retail/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/retail /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

### 8. Setup Queue Worker (Supervisor)

```bash
sudo nano /etc/supervisor/conf.d/retail-worker.conf
```

Add this configuration:

```ini
[program:retail-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/retail/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/retail/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start retail-worker:*
```

### 9. Setup Laravel Horizon (Optional)

```bash
sudo nano /etc/supervisor/conf.d/retail-horizon.conf
```

```ini
[program:retail-horizon]
process_name=%(program_name)s
command=php /var/www/retail/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/retail/storage/logs/horizon.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start retail-horizon
```

## 🚀 Deployment Methods

### Method 1: Manual Deployment

```bash
cd /var/www/retail
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart all
```

### Method 2: Using Deployment Script

```bash
# On your local machine
chmod +x scripts/deploy-aws.sh
./scripts/deploy-aws.sh
```

### Method 3: CI/CD Pipeline (Automatic)

1. Push code to `main` branch
2. GitHub Actions will automatically:
   - Run tests
   - Deploy to EC2
   - Run migrations
   - Restart services

## 🔒 Security Setup

### 1. Configure Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 2. Setup SSL (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### 3. Secure MySQL

Update MySQL configuration:
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add:
```ini
bind-address = 127.0.0.1
```

## 📊 Monitoring

### Check Application Logs

```bash
tail -f /var/www/retail/storage/logs/laravel.log
```

### Check Queue Worker Logs

```bash
tail -f /var/www/retail/storage/logs/worker.log
```

### Check Nginx Logs

```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

### Monitor Services

```bash
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis
sudo supervisorctl status
```

## 🔄 Troubleshooting

### Application Not Loading

```bash
# Check Nginx configuration
sudo nginx -t

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check permissions
sudo chown -R www-data:www-data /var/www/retail
sudo chmod -R 775 /var/www/retail/storage
```

### Queue Not Processing

```bash
# Check supervisor status
sudo supervisorctl status retail-worker:*

# Restart workers
sudo supervisorctl restart retail-worker:*
```

### Database Connection Issues

```bash
# Test MySQL connection
mysql -u retail_user -p retail

# Check MySQL is running
sudo systemctl status mysql
```

## 📝 Important Notes

⚠️ **Security Warning**: Never commit AWS credentials or SSH keys to version control. Always use secrets/environment variables.

✅ **Best Practices**:
- Use IAM users with minimal required permissions (not root)
- Enable MFA for AWS accounts
- Regularly update system packages
- Monitor application logs
- Set up automated backups

## 🆘 Support

If you encounter issues:
1. Check application logs: `/var/www/retail/storage/logs/`
2. Check system logs: `/var/log/nginx/`, `/var/log/syslog`
3. Verify environment variables in `.env`
4. Test database connectivity
5. Check service statuses

---

**Your EC2 Instance**: `i-0ed07d391b8a885e9`
**Region**: `us-east-1`

