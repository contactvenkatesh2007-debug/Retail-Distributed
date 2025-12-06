#!/bin/bash

# Distributed Retail Engine - Setup Script

set -e

echo "🚀 Setting up Distributed Retail Engine..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Copy .env.example if .env doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "⚠️  Please edit .env file with your configuration before continuing."
    read -p "Press Enter to continue after editing .env..."
fi

# Build Docker images
echo "🔨 Building Docker images..."
docker-compose build

# Start services
echo "🚀 Starting services..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
docker-compose exec -T api-gateway composer install --no-interaction

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec -T api-gateway php artisan key:generate

# Run migrations
echo "🗄️  Running database migrations..."
docker-compose exec -T api-gateway php artisan migrate --force

# Clear and cache config
echo "🧹 Clearing and caching configuration..."
docker-compose exec -T api-gateway php artisan config:clear
docker-compose exec -T api-gateway php artisan config:cache
docker-compose exec -T api-gateway php artisan route:cache

echo "✅ Setup complete!"
echo ""
echo "📋 Service URLs:"
echo "   API Gateway: http://localhost:8000"
echo "   Health Check: http://localhost:8000/api/v1/health"
echo "   Horizon Dashboard: http://localhost:8000/horizon"
echo ""
echo "📊 Check service status:"
echo "   docker-compose ps"
echo ""
echo "📝 View logs:"
echo "   docker-compose logs -f"

