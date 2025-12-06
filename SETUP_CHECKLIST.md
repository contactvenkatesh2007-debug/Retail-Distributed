# Setup Checklist

Use this checklist to ensure proper setup of the Distributed Retail Engine.

## Pre-Installation

- [ ] Docker installed (version 20.10+)
- [ ] Docker Compose installed (version 2.0+)
- [ ] Git installed
- [ ] AWS account created
- [ ] AWS credentials obtained

## Environment Setup

- [ ] Repository cloned
- [ ] `.env` file created from `.env.example`
- [ ] Database credentials configured
- [ ] Redis configuration set
- [ ] AWS credentials added
- [ ] AWS S3 bucket created
- [ ] AWS EC2 instance launched (optional)
- [ ] Microservice URLs configured

## Docker Setup

- [ ] Docker images built successfully
- [ ] All containers started
- [ ] Containers running without errors
- [ ] Ports accessible (8000, 8001-8006, 3306, 6379)

## Application Setup

- [ ] Composer dependencies installed
- [ ] Application key generated
- [ ] Database migrations run
- [ ] Database seeders run (if applicable)
- [ ] Configuration cached
- [ ] Routes cached

## Queue Setup

- [ ] Redis running and accessible
- [ ] Queue worker started
- [ ] Laravel Horizon accessible
- [ ] Test job processed successfully

## AWS Integration

- [ ] S3 bucket accessible
- [ ] File upload test successful
- [ ] File download test successful
- [ ] EC2 instance accessible (if using)
- [ ] EC2 instance info retrievable

## API Testing

- [ ] Health check endpoint works
- [ ] Product creation works
- [ ] Product retrieval works
- [ ] Order creation works
- [ ] Storage upload works
- [ ] All microservices reachable

## Security

- [ ] `.env` file not committed to git
- [ ] Strong database passwords set
- [ ] AWS credentials secured
- [ ] Firewall configured (production)
- [ ] HTTPS configured (production)

## Monitoring

- [ ] Laravel Horizon dashboard accessible
- [ ] Logs accessible
- [ ] Error tracking configured (optional)
- [ ] Performance monitoring set up (optional)

## Documentation

- [ ] README.md reviewed
- [ ] API documentation reviewed
- [ ] Architecture documentation understood
- [ ] Deployment guide reviewed

## Production Checklist

- [ ] Environment set to `production`
- [ ] Debug mode disabled
- [ ] HTTPS enabled
- [ ] Database backups configured
- [ ] Monitoring and alerting set up
- [ ] Load balancer configured
- [ ] Auto-scaling configured
- [ ] CDN configured (optional)
- [ ] SSL certificates installed
- [ ] Domain name configured

## Troubleshooting

If any item fails:

1. Check logs: `docker-compose logs [service-name]`
2. Verify environment variables in `.env`
3. Check service connectivity: `docker-compose ps`
4. Review health endpoint: `curl http://localhost:8000/api/v1/health`
5. Check database connection: `docker-compose exec api-gateway php artisan db:show`

## Support

- Review [README.md](README.md) for detailed setup
- Check [QUICKSTART.md](QUICKSTART.md) for quick setup
- See [DEPLOYMENT.md](DEPLOYMENT.md) for production deployment
- Review [ARCHITECTURE.md](ARCHITECTURE.md) for system understanding

---

**Last Updated**: 2024

