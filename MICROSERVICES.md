# Microservices Implementation Guide

## Overview

Each microservice in this architecture can be developed and deployed independently. This document provides implementation templates for each service.

## Service Structure

Each microservice should follow this structure:

```
service-name/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   ├── Services/
│   └── Jobs/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── Dockerfile
├── composer.json
└── .env.example
```

## Product Service

### Responsibilities
- Product catalog management
- Product search and filtering
- Product images
- Category management

### API Endpoints
- `GET /api/v1/products` - List products
- `GET /api/v1/products/{id}` - Get product
- `POST /api/v1/products` - Create product
- `PUT /api/v1/products/{id}` - Update product
- `DELETE /api/v1/products/{id}` - Delete product

### Database
- `products` table
- Indexes on: `category`, `sku`, `status`

## Order Service

### Responsibilities
- Order creation and management
- Order status tracking
- Order history
- Order processing workflow

### API Endpoints
- `GET /api/v1/orders` - List orders
- `GET /api/v1/orders/{id}` - Get order
- `POST /api/v1/orders` - Create order
- `PUT /api/v1/orders/{id}` - Update order
- `POST /api/v1/orders/{id}/cancel` - Cancel order

### Database
- `orders` table
- `order_items` table
- Indexes on: `user_id`, `status`, `created_at`

## User Service

### Responsibilities
- User registration and authentication
- User profile management
- Session management
- Password reset

### API Endpoints
- `POST /api/v1/auth/register` - Register user
- `POST /api/v1/auth/login` - Login user
- `GET /api/v1/users/{id}` - Get user
- `PUT /api/v1/users/{id}` - Update user

### Database
- `users` table
- `password_resets` table (if needed)

## Payment Service

### Responsibilities
- Payment processing
- Transaction management
- Payment gateway integration
- Refund processing

### API Endpoints
- `POST /api/v1/payments/process` - Process payment
- `POST /api/v1/payments/refund` - Process refund
- `GET /api/v1/payments/{id}` - Get payment details

### Integration
- Stripe / PayPal / Square integration
- Webhook handling

## Inventory Service

### Responsibilities
- Stock level tracking
- Inventory updates
- Low stock alerts
- Inventory reservations

### API Endpoints
- `POST /api/v1/inventory/check` - Check availability
- `POST /api/v1/inventory/deduct` - Deduct inventory
- `POST /api/v1/inventory/add` - Add inventory
- `GET /api/v1/inventory/{product_id}` - Get inventory

### Database
- `inventory` table
- `inventory_reservations` table (optional)

## Notification Service

### Responsibilities
- Email notifications
- SMS notifications
- Push notifications
- Notification templates

### API Endpoints
- `POST /api/v1/notifications/send` - Send notification
- `GET /api/v1/notifications/{id}` - Get notification
- `GET /api/v1/notifications/user/{user_id}` - Get user notifications

### Integration
- Mail service (SendGrid, SES, etc.)
- SMS service (Twilio, etc.)
- Push notification service (FCM, APNs)

## Service Communication

### HTTP Communication

Use the `ServiceClient` class for inter-service communication:

```php
$client = new ServiceClient('product');
$response = $client->get('products/1');
```

### Event-Driven Communication

Publish events for decoupled communication:

```php
event(new OrderCreated($orderData));
```

### Queue Jobs

Use queues for async processing:

```php
ProcessOrderJob::dispatch($orderData);
```

## Database Per Service

For true microservices, each service should have its own database:

1. **Product Service** - `products_db`
2. **Order Service** - `orders_db`
3. **User Service** - `users_db`
4. **Payment Service** - `payments_db`
5. **Inventory Service** - `inventory_db`
6. **Notification Service** - `notifications_db`

Update `docker-compose.yml` to include separate databases:

```yaml
product-db:
  image: mysql:8.0
  environment:
    MYSQL_DATABASE: products_db

order-db:
  image: mysql:8.0
  environment:
    MYSQL_DATABASE: orders_db
```

## Service Discovery

### Static Configuration

Services are configured in `config/services.php`:

```php
'microservices' => [
    'product' => [
        'base_url' => env('PRODUCT_SERVICE_URL'),
    ],
],
```

### Dynamic Discovery (Future)

Implement service registry using:
- Consul
- Eureka
- etcd
- Kubernetes Service Discovery

## API Gateway Routing

The API Gateway routes requests to services based on path:

- `/api/v1/products/*` → Product Service
- `/api/v1/orders/*` → Order Service
- `/api/v1/users/*` → User Service

## Error Handling

Each service should return consistent error responses:

```json
{
  "success": false,
  "error": "Error message",
  "code": "ERROR_CODE",
  "status": 400
}
```

## Authentication Between Services

### API Keys

Each service validates API keys:

```php
if ($request->header('X-Service-Key') !== config('services.api_key')) {
    return response()->json(['error' => 'Unauthorized'], 401);
}
```

### JWT Tokens

Use JWT for service-to-service authentication.

## Monitoring

Each service should expose:

- Health endpoint: `/health`
- Metrics endpoint: `/metrics`
- Status endpoint: `/status`

## Deployment

Each service can be deployed independently:

```bash
# Deploy Product Service
docker build -t product-service:latest ./services/product-service
docker push product-service:latest

# Deploy to Kubernetes
kubectl apply -f k8s/product-service.yaml
```

## Testing

Test each service independently:

```bash
# Test Product Service
cd services/product-service
php artisan test
```

## Versioning

Use API versioning:

- `/api/v1/products`
- `/api/v2/products`

## Documentation

Each service should have:
- API documentation (OpenAPI/Swagger)
- README with setup instructions
- Environment variable documentation

