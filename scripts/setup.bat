@echo off
REM Distributed Retail Engine - Windows Setup Script

echo 🚀 Setting up Distributed Retail Engine...

REM Check if Docker is installed
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker is not installed. Please install Docker Desktop first.
    exit /b 1
)

REM Check if Docker Compose is installed
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Compose is not installed. Please install Docker Compose first.
    exit /b 1
)

REM Copy .env.example if .env doesn't exist
if not exist .env (
    echo 📝 Creating .env file from .env.example...
    copy .env.example .env
    echo ⚠️  Please edit .env file with your configuration before continuing.
    pause
)

REM Build Docker images
echo 🔨 Building Docker images...
docker-compose build

REM Start services
echo 🚀 Starting services...
docker-compose up -d

REM Wait for MySQL to be ready
echo ⏳ Waiting for MySQL to be ready...
timeout /t 10 /nobreak >nul

REM Install Composer dependencies
echo 📦 Installing Composer dependencies...
docker-compose exec -T api-gateway composer install --no-interaction

REM Generate application key
echo 🔑 Generating application key...
docker-compose exec -T api-gateway php artisan key:generate

REM Run migrations
echo 🗄️  Running database migrations...
docker-compose exec -T api-gateway php artisan migrate --force

REM Clear and cache config
echo 🧹 Clearing and caching configuration...
docker-compose exec -T api-gateway php artisan config:clear
docker-compose exec -T api-gateway php artisan config:cache
docker-compose exec -T api-gateway php artisan route:cache

echo ✅ Setup complete!
echo.
echo 📋 Service URLs:
echo    API Gateway: http://localhost:8000
echo    Health Check: http://localhost:8000/api/v1/health
echo    Horizon Dashboard: http://localhost:8000/horizon
echo.
echo 📊 Check service status:
echo    docker-compose ps
echo.
echo 📝 View logs:
echo    docker-compose logs -f

pause

