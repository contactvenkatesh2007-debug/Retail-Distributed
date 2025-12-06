# Distributed Retail Engine – Scalable Microservices System

A comprehensive, production-ready distributed retail engine built with Laravel, featuring microservices architecture, AWS EC2/S3 integration, queue processing, and CI/CD pipelines.

## 🏗️ Architecture Overview

This system implements a microservices architecture with the following components:

### Core Services

1. **API Gateway** (Port 8000) - Main entry point for all client requests
2. **Product Service** (Port 8001) - Product catalog management
3. **Order Service** (Port 8002) - Order processing and management
4. **User Service** (Port 8003) - User authentication and management
5. **Payment Service** (Port 8004) - Payment processing
6. **Inventory Service** (Port 8005) - Inventory management
7. **Notification Service** (Port 8006) - Email, SMS, and push notifications

### Infrastructure Components

- **MySQL Database** - Primary data storage
- **Redis** - Caching and queue management
- **Laravel Horizon** - Queue monitoring dashboard
- **Docker & Docker Compose** - Containerization

## 🚀 Features

- ✅ Microservices Architecture with API Gateway
- ✅ AWS S3 Integration for File Storage
- ✅ AWS EC2 Instance Management
- ✅ Redis-based Queue System
- ✅ Laravel Horizon for Queue Monitoring
- ✅ Event-Driven Architecture
- ✅ Docker Containerization
- ✅ CI/CD Pipelines (GitHub Actions & GitLab CI)
- ✅ RESTful API Design
- ✅ Database Migrations
- ✅ Comprehensive Error Handling
- ✅ Service Discovery & Routing
- ✅ Inter-Service Communication

## 📋 Prerequisites

- PHP 8.2+
- Composer
- Docker & Docker Compose
- MySQL 8.0+
- Redis 7+
- AWS Account (for S3 and EC2)
- Node.js & NPM (for frontend assets)

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Retail
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file and configure:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=retail
DB_USERNAME=root
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# AWS Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# Microservices URLs (for local development)
PRODUCT_SERVICE_URL=http://localhost:8001
ORDER_SERVICE_URL=http://localhost:8002
USER_SERVICE_URL=http://localhost:8003
PAYMENT_SERVICE_URL=http://localhost:8004
INVENTORY_SERVICE_URL=http://localhost:8005
NOTIFICATION_SERVICE_URL=http://localhost:8006
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Services with Docker

```bash
docker-compose up -d
```

This will start:
- All microservices
- MySQL database
- Redis
- Queue workers
- Laravel Horizon

### 6. Start Queue Worker (if not using Docker)

```bash
php artisan queue:work redis --tries=3
```

### 7. Access the Application

- API Gateway: http://localhost:8000
- Laravel Horizon: http://localhost:8000/horizon
- Health Check: http://localhost:8000/api/v1/health

## 📚 API Documentation

### Base URL

All API requests should be made to: `http://localhost:8000/api/v1`

### Authentication

Currently, the API uses basic authentication. Include headers as needed:

```http
Authorization: Bearer {token}
```

### Endpoints

#### Products

- `GET /products` - List all products
- `GET /products/{id}` - Get product details
- `POST /products` - Create new product
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product

#### Orders

- `GET /orders` - List all orders
- `GET /orders/{id}` - Get order details
- `POST /orders` - Create new order
- `PUT /orders/{id}` - Update order status

#### Storage (AWS S3)

- `POST /storage/upload` - Upload file to S3
- `GET /storage/download/{path}` - Download file from S3
- `GET /storage/url/{path}` - Get presigned URL
- `DELETE /storage/delete/{path}` - Delete file from S3
- `GET /storage/list` - List files in bucket

### Example Requests

#### Create Product

```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Product Name",
    "description": "Product Description",
    "price": 99.99,
    "category": "Electronics",
    "sku": "PROD-001",
    "stock_quantity": 100
  }'
```

#### Create Order

```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "items": [
      {
        "product_id": 1,
        "quantity": 2,
        "price": 99.99
      }
    ],
    "shipping_address": "123 Main St, City, State 12345",
    "payment_method": "credit_card"
  }'
```

## 🔄 Queue System

The system uses Redis for queue processing. Jobs are automatically processed in the background.

### Available Queues

- `default` - General purpose jobs
- `orders` - Order processing jobs
- `images` - Image processing jobs

### Monitor Queues

Access Laravel Horizon at: http://localhost:8000/horizon

## 🐳 Docker Deployment

### Build Images

```bash
docker-compose build
```

### Start Services

```bash
docker-compose up -d
```

### View Logs

```bash
docker-compose logs -f api-gateway
docker-compose logs -f queue-worker
```

### Stop Services

```bash
docker-compose down
```

### Run Migrations in Docker

```bash
docker-compose exec api-gateway php artisan migrate
```

## 🔧 AWS Configuration

### S3 Setup

1. Create an S3 bucket in AWS Console
2. Configure bucket permissions
3. Add credentials to `.env`:

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_BUCKET=your-bucket-name
AWS_DEFAULT_REGION=us-east-1
```

### EC2 Setup

1. Launch an EC2 instance
2. Configure security groups
3. Add instance details to `.env`:

```env
AWS_EC2_INSTANCE_ID=i-xxxxxxxxxxxxx
AWS_EC2_KEY_PAIR=your-key-pair
AWS_EC2_SECURITY_GROUP=sg-xxxxxxxxxxxxx
```

## 🚦 CI/CD Pipeline

### GitHub Actions

The pipeline automatically:
1. Runs tests on push/PR
2. Builds Docker images on merge to main
3. Deploys to AWS EC2

Configure secrets in GitHub:
- `DOCKER_USERNAME`
- `DOCKER_PASSWORD`
- `AWS_EC2_HOST`
- `AWS_EC2_USER`
- `AWS_EC2_SSH_KEY`

### GitLab CI

Similar pipeline configured for GitLab CI/CD. Add variables in GitLab:
- `SSH_PRIVATE_KEY`
- `SSH_USER`
- `SSH_HOST`

## 📊 Monitoring

### Laravel Horizon

Monitor queues, jobs, and workers:
- URL: http://localhost:8000/horizon
- Requires authentication (configure in `config/horizon.php`)

### Health Check

```bash
curl http://localhost:8000/api/v1/health
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter ProductTest
```

## 📝 Development Workflow

1. Create feature branch
2. Make changes
3. Write tests
4. Run tests locally
5. Push to repository
6. CI/CD pipeline runs automatically
7. Merge after review

## 🔐 Security Best Practices

- Use environment variables for sensitive data
- Enable HTTPS in production
- Implement rate limiting
- Use API authentication tokens
- Regular security updates
- Database encryption at rest
- Secure AWS credentials

## 📈 Scaling

### Horizontal Scaling

- Scale individual microservices independently
- Use load balancers for API Gateway
- Configure auto-scaling groups in AWS

### Vertical Scaling

- Increase container resources
- Optimize database queries
- Implement caching strategies

## 🐛 Troubleshooting

### Queue Not Processing

```bash
# Check queue worker status
docker-compose ps queue-worker

# Restart queue worker
docker-compose restart queue-worker

# View queue logs
docker-compose logs -f queue-worker
```

### Service Connection Issues

Check service URLs in `.env` and ensure all services are running:

```bash
docker-compose ps
```

### Database Connection

```bash
# Test database connection
docker-compose exec api-gateway php artisan db:show
```

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For issues and questions, please open an issue on the GitHub repository.

## 🙏 Acknowledgments

- Laravel Framework
- AWS SDK for PHP
- Docker Community
- All contributors

---

**Built with ❤️ using Laravel**
