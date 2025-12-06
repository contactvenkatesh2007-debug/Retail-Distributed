#!/bin/bash

# Initial EC2 Setup Script
# Run this on your EC2 instance to set up the environment

set -e

echo "🚀 Setting up EC2 instance for Laravel deployment..."

# Update system
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
echo "🐘 Installing PHP 8.2..."
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-redis php8.2-bcmath

# Install Composer
echo "📦 Installing Composer..."
if [ ! -f /usr/local/bin/composer ]; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

# Install Nginx
echo "🌐 Installing Nginx..."
sudo apt install -y nginx

# Install MySQL
echo "🗄️ Installing MySQL..."
sudo apt install -y mysql-server

# Install Redis
echo "📮 Installing Redis..."
sudo apt install -y redis-server

# Install Git
echo "📥 Installing Git..."
sudo apt install -y git

# Install Node.js and NPM
echo "📦 Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Supervisor
echo "👷 Installing Supervisor..."
sudo apt install -y supervisor

# Install AWS CLI
echo "☁️ Installing AWS CLI..."
if [ ! -f /usr/local/bin/aws ]; then
    curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
    unzip awscliv2.zip
    sudo ./aws/install
    rm -rf aws awscliv2.zip
fi

# Create application directory
echo "📁 Creating application directory..."
sudo mkdir -p /var/www/retail
sudo chown -R $USER:$USER /var/www/retail

# Setup Nginx configuration
echo "⚙️ Configuring Nginx..."
sudo tee /etc/nginx/sites-available/retail > /dev/null <<EOF
server {
    listen 80;
    server_name _;
    root /var/www/retail/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
sudo ln -sf /etc/nginx/sites-available/retail /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t

# Setup Supervisor configuration for queue worker
echo "👷 Configuring Supervisor..."
sudo tee /etc/supervisor/conf.d/retail-worker.conf > /dev/null <<EOF
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
EOF

# Setup Supervisor configuration for Horizon
sudo tee /etc/supervisor/conf.d/retail-horizon.conf > /dev/null <<EOF
[program:retail-horizon]
process_name=%(program_name)s
command=php /var/www/retail/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/retail/storage/logs/horizon.log
stopwaitsecs=3600
EOF

# Setup firewall
echo "🔥 Configuring firewall..."
sudo ufw --force enable
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Start services
echo "🔄 Starting services..."
sudo systemctl enable nginx
sudo systemctl enable php8.2-fpm
sudo systemctl enable mysql
sudo systemctl enable redis-server
sudo systemctl enable supervisor

sudo systemctl start nginx
sudo systemctl start php8.2-fpm
sudo systemctl start mysql
sudo systemctl start redis-server
sudo systemctl start supervisor

echo "✅ EC2 setup complete!"
echo ""
echo "📝 Next steps:"
echo "1. Clone your repository: cd /var/www && git clone <repo-url> retail"
echo "2. Configure .env file: cd /var/www/retail && cp .env.example .env"
echo "3. Install dependencies: composer install"
echo "4. Run migrations: php artisan migrate"
echo "5. Set permissions: sudo chown -R www-data:www-data /var/www/retail"

