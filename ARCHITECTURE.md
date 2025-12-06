# Architecture Documentation

## System Overview

The Distributed Retail Engine is built using a microservices architecture pattern, providing scalability, maintainability, and independent deployment capabilities.

## Architecture Diagram

```
┌─────────────┐
│   Client    │
│  (Browser/  │
│   Mobile)   │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────────────┐
│            API Gateway (Port 8000)              │
│  - Request Routing                              │
│  - Service Discovery                            │
│  - Load Balancing                               │
│  - Authentication                               │
└──────┬──────────────────────────────────────────┘
       │
       ├──────────┬──────────┬──────────┬──────────┬──────────┐
       │          │          │          │          │          │
       ▼          ▼          ▼          ▼          ▼          ▼
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│ Product  │ │  Order   │ │   User   │ │ Payment  │ │Inventory │ │Notification│
│ Service  │ │ Service  │ │ Service  │ │ Service  │ │ Service  │ │ Service  │
│  :8001   │ │  :8002   │ │  :8003   │ │  :8004   │ │  :8005   │ │  :8006   │
└────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘
     │            │             │            │            │            │
     └────────────┴─────────────┴────────────┴────────────┴────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
                    ▼                   ▼
            ┌──────────────┐    ┌──────────────┐
            │    MySQL     │    │    Redis     │
            │   Database   │    │   Cache &    │
            │              │    │    Queue     │
            └──────────────┘    └──────────────┘
                                        │
                                        ▼
                              ┌──────────────────┐
                              │  Queue Workers   │
                              │  - Order Jobs    │
                              │  - Image Jobs    │
                              └──────────────────┘
                                        │
                                        ▼
                              ┌──────────────────┐
                              │   AWS Services   │
                              │  - S3 Storage    │
                              │  - EC2 Instances │
                              └──────────────────┘
```

## Component Details

### 1. API Gateway

**Purpose**: Single entry point for all client requests

**Responsibilities**:
- Route requests to appropriate microservices
- Handle authentication and authorization
- Request/response transformation
- Rate limiting
- Service discovery

**Technology**: Laravel with custom routing

### 2. Product Service

**Purpose**: Manage product catalog

**Responsibilities**:
- Product CRUD operations
- Product search and filtering
- Category management
- Image upload and processing

**Database Tables**: `products`

### 3. Order Service

**Purpose**: Handle order lifecycle

**Responsibilities**:
- Order creation and management
- Order status tracking
- Order history
- Integration with inventory and payment services

**Database Tables**: `orders`, `order_items`

### 4. User Service

**Purpose**: User management and authentication

**Responsibilities**:
- User registration and authentication
- Profile management
- Session management

**Database Tables**: `users`

### 5. Payment Service

**Purpose**: Payment processing

**Responsibilities**:
- Payment gateway integration
- Transaction processing
- Payment history
- Refund processing

**Database Tables**: `payments`, `transactions`

### 6. Inventory Service

**Purpose**: Inventory management

**Responsibilities**:
- Stock level tracking
- Inventory updates
- Low stock alerts
- Inventory reservations

**Database Tables**: `inventory`

### 7. Notification Service

**Purpose**: Send notifications

**Responsibilities**:
- Email notifications
- SMS notifications
- Push notifications
- Notification templates

**Database Tables**: `notifications`

## Data Flow

### Order Processing Flow

1. Client sends order request to API Gateway
2. API Gateway routes to Order Service
3. Order Service creates order record
4. Order Service publishes `OrderCreated` event
5. Event listener dispatches `ProcessOrderJob` to queue
6. Queue worker processes job:
   - Checks inventory via Inventory Service
   - Processes payment via Payment Service
   - Updates inventory
   - Sends notification via Notification Service
7. Order status updated to "processing"

### File Upload Flow

1. Client uploads file to API Gateway
2. API Gateway routes to Storage Controller
3. Storage Controller uploads to AWS S3
4. File metadata stored in database
5. If product image, dispatches `ProcessProductImageJob`
6. Queue worker processes image (resize, optimize)
7. Processed images stored back to S3

## Communication Patterns

### Synchronous Communication

- **HTTP/REST**: Used for most service-to-service communication
- **ServiceClient**: Abstraction layer for HTTP requests

### Asynchronous Communication

- **Queue System**: Redis-based queue for background jobs
- **Events**: Laravel events for decoupled communication
- **Job Processing**: Workers process jobs asynchronously

## Storage Architecture

### Database

- **MySQL**: Primary relational database
- **Tables**: Products, Orders, Users, etc.
- **Migrations**: Version-controlled schema

### File Storage

- **AWS S3**: Object storage for files and images
- **Local Storage**: Fallback for development

### Cache

- **Redis**: In-memory cache and session storage
- **Laravel Cache**: Abstraction layer

## Queue Architecture

### Queue Types

- `default`: General purpose jobs
- `orders`: Order processing jobs
- `images`: Image processing jobs

### Job Processing

- **Laravel Horizon**: Queue monitoring and management
- **Workers**: Multiple workers for parallel processing
- **Retry Logic**: Automatic retry on failure
- **Failure Handling**: Failed jobs stored for analysis

## Scalability

### Horizontal Scaling

- Each microservice can be scaled independently
- Load balancers distribute traffic
- Multiple queue workers for parallel processing

### Vertical Scaling

- Increase container resources
- Database read replicas
- Cache layer optimization

## Security

### Authentication

- API tokens for service-to-service communication
- User authentication via User Service
- JWT tokens for stateless authentication

### Authorization

- Role-based access control (RBAC)
- Permission checks at API Gateway
- Service-level authorization

### Data Protection

- Encryption at rest (AWS S3)
- Encryption in transit (HTTPS)
- Sensitive data in environment variables

## Monitoring & Observability

### Logging

- Laravel logging to files
- Centralized logging system (optional)
- Structured logging format

### Metrics

- Laravel Horizon dashboard
- Queue metrics
- Service health checks

### Tracing

- Request ID propagation
- Service correlation IDs
- Performance monitoring

## Deployment

### Containerization

- Docker containers for each service
- Docker Compose for local development
- Kubernetes-ready for production

### CI/CD

- Automated testing
- Docker image building
- Automated deployment to AWS EC2
- Blue-green deployment support

## Performance Optimization

### Caching Strategy

- Redis cache for frequently accessed data
- Query result caching
- HTTP response caching

### Database Optimization

- Indexed columns
- Query optimization
- Connection pooling
- Read replicas

### CDN Integration

- Static asset delivery
- Image optimization
- Geographic distribution

## Disaster Recovery

### Backup Strategy

- Database backups (automated)
- S3 versioning enabled
- Configuration backups

### High Availability

- Multiple service instances
- Database replication
- Failover mechanisms

## Future Enhancements

- Message broker (RabbitMQ/Kafka)
- GraphQL API
- WebSocket support
- Service mesh (Istio/Linkerd)
- Distributed tracing (Jaeger/Zipkin)
- API versioning strategy
- Circuit breakers for resilience
- Bulk operations support

