#!/bin/bash

# AWS EC2 Deployment Script
# Usage: ./scripts/deploy-aws.sh

set -e

EC2_INSTANCE_ID="i-0ed07d391b8a885e9"
EC2_USER="ubuntu"  # Change to 'ec2-user' for Amazon Linux
EC2_REGION="us-east-1"
APP_DIR="/var/www/retail"

echo "🚀 Starting deployment to AWS EC2..."

# Get EC2 instance IP
echo "📍 Getting EC2 instance IP..."
INSTANCE_IP=$(aws ec2 describe-instances \
    --instance-ids $EC2_INSTANCE_ID \
    --region $EC2_REGION \
    --query 'Reservations[0].Instances[0].PublicIpAddress' \
    --output text)

if [ -z "$INSTANCE_IP" ] || [ "$INSTANCE_IP" == "None" ]; then
    echo "❌ Failed to get instance IP. Is the instance running?"
    exit 1
fi

echo "✅ Instance IP: $INSTANCE_IP"

# Deploy via SSH
echo "📦 Deploying application..."
ssh -o StrictHostKeyChecking=no $EC2_USER@$INSTANCE_IP << EOF
    set -e
    cd $APP_DIR || exit 1
    
    echo "📥 Pulling latest code..."
    git fetch origin
    git reset --hard origin/main
    
    echo "📦 Installing dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    
    echo "🗄️ Running migrations..."
    php artisan migrate --force
    
    echo "🧹 Clearing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    echo "⚡ Optimizing..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo "🔄 Restarting services..."
    sudo systemctl restart php8.2-fpm 2>/dev/null || sudo systemctl restart php-fpm || true
    sudo systemctl restart nginx || true
    sudo supervisorctl restart all 2>/dev/null || true
    
    echo "✅ Deployment complete!"
EOF

echo "🎉 Deployment successful!"
echo "🌐 Application URL: http://$INSTANCE_IP"

