# Documentation Index

Quick navigation guide for the Distributed Retail Engine project.

## 🚀 Getting Started

1. **[PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)** - Overview of the entire project
2. **[QUICKSTART.md](QUICKSTART.md)** - Get started in 5 minutes
3. **[README.md](README.md)** - Complete documentation and setup guide
4. **[SETUP_CHECKLIST.md](SETUP_CHECKLIST.md)** - Setup verification checklist

## 📚 Documentation

### Architecture & Design
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - System architecture and design patterns
- **[MICROSERVICES.md](MICROSERVICES.md)** - Microservices implementation guide

### Deployment
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment guide
- **Docker Files**: `Dockerfile`, `docker-compose.yml`
- **CI/CD**: `.github/workflows/ci-cd.yml`, `.gitlab-ci.yml`

## 🛠️ Setup Scripts

- **Linux/Mac**: `scripts/setup.sh`
- **Windows**: `scripts/setup.bat`

## 📁 Key Directories

- `app/` - Application code
  - `Http/Controllers/` - API controllers
  - `Services/` - Business logic services
  - `Jobs/` - Queue jobs
  - `Models/` - Eloquent models
  - `Events/` - Event classes
  - `Listeners/` - Event listeners

- `config/` - Configuration files
  - `services.php` - Microservices configuration
  - `queue.php` - Queue configuration
  - `horizon.php` - Horizon configuration

- `routes/` - Route definitions
  - `api.php` - API routes

- `database/migrations/` - Database migrations

- `services/` - Microservice route definitions

## 🔑 Configuration Files

- `.env.example` - Environment variables template
- `composer.json` - PHP dependencies
- `package.json` - Node.js dependencies (if any)

## 🐳 Docker Files

- `Dockerfile` - Main container definition
- `docker-compose.yml` - Multi-container orchestration
- `supervisord.conf` - Process management
- `.dockerignore` - Docker build exclusions

## 🔄 CI/CD Files

- `.github/workflows/ci-cd.yml` - GitHub Actions pipeline
- `.gitlab-ci.yml` - GitLab CI pipeline

## 📋 Quick Reference

### Common Commands

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f

# Run migrations
docker-compose exec api-gateway php artisan migrate

# Access services
# API: http://localhost:8000
# Horizon: http://localhost:8000/horizon
```

### API Endpoints

- Health: `GET /api/v1/health`
- Products: `/api/v1/products`
- Orders: `/api/v1/orders`
- Storage: `/api/v1/storage/*`

## 🎯 Feature Guides

- **API Gateway**: See `app/Http/Controllers/ApiGatewayController.php`
- **AWS Integration**: See `app/Services/AwsStorageService.php`
- **Queue Jobs**: See `app/Jobs/`
- **Service Communication**: See `app/Services/ServiceClient.php`

## 🔍 Troubleshooting

1. Check logs: `docker-compose logs [service-name]`
2. Verify health: `curl http://localhost:8000/api/v1/health`
3. Review [README.md](README.md) troubleshooting section
4. Check [SETUP_CHECKLIST.md](SETUP_CHECKLIST.md)

## 📖 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Documentation](https://docs.docker.com/)
- [AWS SDK for PHP](https://aws.amazon.com/sdk-for-php/)
- [Laravel Horizon](https://laravel.com/docs/horizon)

---

**Start here**: [QUICKSTART.md](QUICKSTART.md) for fast setup, or [README.md](README.md) for complete guide.

