# Quick Start Guide

Get the Distributed Retail Engine up and running in 5 minutes!

## Prerequisites Check

- ✅ Docker and Docker Compose installed
- ✅ Git installed
- ✅ AWS credentials (for S3/EC2 features)

## Installation Steps

### 1. Clone and Navigate

```bash
git clone <repository-url>
cd Retail
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and set at minimum:

```env
DB_DATABASE=retail
DB_USERNAME=root
DB_PASSWORD=root
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=your-bucket
```

### 3. Start with Docker

```bash
docker-compose up -d
```

### 4. Install Dependencies & Migrate

```bash
docker-compose exec api-gateway composer install
docker-compose exec api-gateway php artisan key:generate
docker-compose exec api-gateway php artisan migrate
```

### 5. Access the Application

- **API**: http://localhost:8000/api/v1/health
- **Horizon Dashboard**: http://localhost:8000/horizon

## Test the API

### Health Check

```bash
curl http://localhost:8000/api/v1/health
```

### Create a Product

```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "price": 29.99,
    "category": "Electronics",
    "sku": "TEST-001",
    "stock_quantity": 100
  }'
```

### Create an Order

```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "items": [{
      "product_id": 1,
      "quantity": 2,
      "price": 29.99
    }],
    "shipping_address": "123 Main St",
    "payment_method": "credit_card"
  }'
```

## Common Commands

### View Logs

```bash
docker-compose logs -f api-gateway
docker-compose logs -f queue-worker
```

### Restart Services

```bash
docker-compose restart
```

### Stop Services

```bash
docker-compose down
```

### Run Migrations

```bash
docker-compose exec api-gateway php artisan migrate
```

### Clear Cache

```bash
docker-compose exec api-gateway php artisan config:clear
docker-compose exec api-gateway php artisan cache:clear
```

## Next Steps

1. Review [README.md](README.md) for detailed documentation
2. Check [ARCHITECTURE.md](ARCHITECTURE.md) for system design
3. See [DEPLOYMENT.md](DEPLOYMENT.md) for production deployment

## Troubleshooting

### Port Already in Use

If port 8000 is busy, change it in `docker-compose.yml`:

```yaml
ports:
  - "8001:8000"  # Change 8001 to any available port
```

### Database Connection Error

Ensure MySQL container is running:

```bash
docker-compose ps mysql
docker-compose logs mysql
```

### Queue Not Processing

Check queue worker:

```bash
docker-compose ps queue-worker
docker-compose logs queue-worker
```

## Support

For issues, check:
- Logs: `docker-compose logs`
- Health endpoint: `http://localhost:8000/api/v1/health`
- GitHub Issues

Happy coding! 🚀

