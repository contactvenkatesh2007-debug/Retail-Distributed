# 🚀 Complete Deployment Steps - Build & Deploy to AWS

## Overview

This guide walks you through setting up and using the CI/CD pipeline to automatically build and deploy your Laravel application to AWS EC2.

## 📋 Prerequisites Checklist

- [ ] AWS EC2 Instance running (Instance ID: `i-0ed07d391b8a885e9`)
- [ ] GitHub repository set up
- [ ] AWS credentials available
- [ ] SSH key pair for EC2 instance

## 🔧 Step 1: Configure GitHub Secrets

### 1.1 Access GitHub Repository Settings

1. Go to your GitHub repository
2. Click **Settings** (top navigation)
3. In the left sidebar, click **Secrets and variables** → **Actions**
4. Click **New repository secret**

### 1.2 Add Required Secrets

Add each secret one by one:

#### Secret 1: AWS_ACCESS_KEY_ID
- **Name**: `AWS_ACCESS_KEY_ID`
- **Value**: Your AWS Access Key ID
- **How to get**: AWS Console → IAM → Users → Your User → Security Credentials → Create Access Key

#### Secret 2: AWS_SECRET_ACCESS_KEY
- **Name**: `AWS_SECRET_ACCESS_KEY`
- **Value**: Your AWS Secret Access Key
- **Important**: Copy this when creating the access key (you can only see it once)

#### Secret 3: AWS_EC2_USER
- **Name**: `AWS_EC2_USER`
- **Value**: 
  - `ubuntu` (for Ubuntu/Debian instances)
  - `ec2-user` (for Amazon Linux instances)
  - `admin` (for Debian instances)

#### Secret 4: AWS_SSH_PRIVATE_KEY
- **Name**: `AWS_SSH_PRIVATE_KEY`
- **Value**: Complete contents of your EC2 SSH private key file (.pem)
- **How to get**: 
  ```bash
  cat /path/to/your-key.pem
  # Copy everything including:
  # -----BEGIN RSA PRIVATE KEY-----
  # ... (all lines) ...
  # -----END RSA PRIVATE KEY-----
  ```

#### Optional Secret: REPO_URL (if repository is private)
- **Name**: `REPO_URL`
- **Value**: Your repository clone URL with authentication

### 1.3 Verify Secrets

After adding, you should see:
- ✅ AWS_ACCESS_KEY_ID
- ✅ AWS_SECRET_ACCESS_KEY
- ✅ AWS_EC2_USER
- ✅ AWS_SSH_PRIVATE_KEY

## 🖥️ Step 2: Initial EC2 Setup (One-Time)

### 2.1 Connect to EC2 Instance

```bash
ssh -i your-key.pem ubuntu@<your-ec2-ip>
```

### 2.2 Run Setup Script

```bash
# Download and run setup script
curl -fsSL https://raw.githubusercontent.com/your-username/your-repo/main/scripts/setup-ec2.sh | bash
```

Or manually:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-redis php8.2-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Git
sudo apt install -y git

# Install Supervisor
sudo apt install -y supervisor
```

### 2.3 Create Application Directory

```bash
sudo mkdir -p /var/www/retail
sudo chown -R $USER:$USER /var/www/retail
cd /var/www/retail
```

### 2.4 Clone Repository (First Time)

```bash
git clone <your-repository-url> .
```

### 2.5 Configure Environment

```bash
cp .env.example .env
nano .env
```

Update these critical settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://<your-ec2-ip>

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=retail
DB_USERNAME=root
DB_PASSWORD=your_secure_password

REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=redis

AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

### 2.6 Initial Application Setup

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Build frontend assets
npm install
npm run build
```

### 2.7 Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/retail
```

Paste this configuration:

```nginx
server {
    listen 80;
    server_name _;
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

Enable site:
```bash
sudo ln -sf /etc/nginx/sites-available/retail /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

### 2.8 Setup Queue Workers (Supervisor)

```bash
sudo nano /etc/supervisor/conf.d/retail-worker.conf
```

Add:
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

Enable:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start retail-worker:*
```

## 🚀 Step 3: Using CI/CD Pipeline

### 3.1 Automatic Deployment

Once everything is set up, **every push to `main` branch** will:

1. ✅ Run tests
2. ✅ Build the application
3. ✅ Deploy to EC2
4. ✅ Run migrations
5. ✅ Restart services
6. ✅ Verify deployment

### 3.2 Manual Deployment Trigger

You can also trigger deployment manually:

1. Go to GitHub repository
2. Click **Actions** tab
3. Select **CI/CD Pipeline - Build & Deploy to AWS**
4. Click **Run workflow**
5. Select branch: `main`
6. Click **Run workflow**

### 3.3 Monitor Deployment

1. Go to **Actions** tab in GitHub
2. Click on the latest workflow run
3. Watch real-time deployment progress
4. Check for any errors

## 📊 Step 4: Verify Deployment

### 4.1 Check Application

```bash
# Health check
curl http://<your-ec2-ip>/api/v1/health

# Should return JSON with service status
```

### 4.2 Check Services

```bash
# SSH into EC2
ssh -i your-key.pem ubuntu@<your-ec2-ip>

# Check services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis
sudo supervisorctl status
```

### 4.3 Check Logs

```bash
# Application logs
tail -f /var/www/retail/storage/logs/laravel.log

# Queue worker logs
tail -f /var/www/retail/storage/logs/worker.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

## 🔄 Step 5: Deployment Workflow

### What Happens During Deployment

1. **Test Phase**
   - Runs PHPUnit tests
   - Checks code quality
   - Verifies database migrations

2. **Build Phase**
   - Installs production dependencies
   - Builds frontend assets
   - Optimizes Laravel caches
   - Creates deployment artifact

3. **Deploy Phase**
   - Connects to EC2 instance
   - Pulls latest code from repository
   - Installs dependencies
   - Runs migrations
   - Clears and rebuilds caches
   - Restarts services
   - Verifies deployment

## 🐛 Troubleshooting

### Deployment Fails: SSH Connection Error

**Solution**:
- Verify `AWS_SSH_PRIVATE_KEY` contains complete key
- Check `AWS_EC2_USER` is correct for your OS
- Ensure EC2 security group allows SSH (port 22)

### Deployment Fails: Permission Denied

**Solution**:
```bash
# On EC2 instance
sudo chown -R $USER:$USER /var/www/retail
sudo chmod -R 775 /var/www/retail/storage
```

### Application Not Loading

**Solution**:
```bash
# Check Nginx configuration
sudo nginx -t

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# Check logs
sudo tail -f /var/log/nginx/error.log
```

### Database Connection Error

**Solution**:
- Verify `.env` file has correct database credentials
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `mysql -u root -p`

## 📝 Quick Reference

### Files Modified
- `.github/workflows/ci-cd.yml` - CI/CD pipeline configuration

### Environment Variables
- `EC2_INSTANCE_ID`: i-0ed07d391b8a885e9
- `AWS_REGION`: us-east-1
- `APP_DIR`: /var/www/retail

### Key URLs
- Application: `http://<ec2-ip>`
- Health Check: `http://<ec2-ip>/api/v1/health`
- API: `http://<ec2-ip>/api/v1`

## ✅ Success Checklist

After deployment, verify:
- [ ] Health endpoint returns 200 OK
- [ ] Application loads in browser
- [ ] Database migrations completed
- [ ] Queue workers running
- [ ] Nginx serving requests
- [ ] Logs show no errors

## 🔒 Security Notes

⚠️ **Important**:
- Never commit `.env` file
- Never commit SSH keys
- Use IAM users with minimal permissions (not root)
- Regularly rotate access keys
- Enable AWS MFA

---

**Your EC2 Instance**: `i-0ed07d391b8a885e9`  
**Region**: `us-east-1`  
**Ready to deploy!** 🚀

